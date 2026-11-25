<?php

namespace App\Platform\Http\Controllers\Api;

use App\Ai\AiException;
use App\Ai\AiRefusedException;
use App\Ai\LlmGateway;
use App\Http\Controllers\Controller;
use App\Platform\Ai\CreditAccount;
use App\Platform\Ai\Pricing;
use App\Platform\Http\Middleware\AuthenticateLicense;
use App\Platform\Models\AiRate;
use App\Platform\Models\AiUsage;
use App\Platform\Models\Customer;
use App\Platform\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * Relais IA : un site GeniusPress (local, chez le client ou hébergé chez nous)
 * envoie sa demande avec sa clé de licence ; la plateforme vérifie le solde,
 * appelle Claude avec sa propre clé, débite le client (coût réel × multiplicateur)
 * et journalise l'appel, sans jamais enregistrer le contenu de la demande.
 */
class RelayController extends Controller
{
    public const EFFORTS = ['low', 'medium', 'high', 'xhigh', 'max'];
    public const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public function __construct(private Pricing $pricing, private CreditAccount $credits) {}

    public function messages(Request $request, LlmGateway $llm)
    {
        /** @var License $license */
        $license = $request->attributes->get('platform_license');
        /** @var Customer $customer */
        $customer = $request->attributes->get('platform_customer');

        if (strlen($request->getContent()) > (int) config('geniuspress.platform.relay_max_bytes')) {
            return AuthenticateLicense::error('too_large', __('Cette demande est trop volumineuse pour l’IA.'), 413);
        }
        $validator = Validator::make($request->all(), [
            'model' => ['required', 'string', 'max:80'],
            'system' => ['required', 'string', 'max:200000'],
            'content' => ['required', 'array', 'min:1', 'max:60'],
            'content.*.type' => ['required', 'in:text,image'],
            'schema' => ['required', 'array'],
            'effort' => ['nullable', 'in:'.implode(',', self::EFFORTS)],
            'max_tokens' => ['nullable', 'integer', 'min:1', 'max:'.(int) config('geniuspress.platform.relay_max_tokens')],
            'action' => ['nullable', 'string', 'max:40'],
        ]);
        if ($validator->fails()) {
            return AuthenticateLicense::error('invalid_request', $validator->errors()->first(), 422);
        }
        $data = $validator->validated();
        $content = $this->cleanContent($request->input('content'));
        if ($content === null) {
            return AuthenticateLicense::error('invalid_request', __('Le contenu de la demande est invalide.'), 422);
        }

        $log = ['customer_id' => $customer->id, 'license_id' => $license->id, 'action' => $data['action'] ?? null, 'model' => $data['model'], 'ip' => $request->ip()];

        $rate = $this->pricing->rate($data['model']);
        if (! $rate) {
            return AuthenticateLicense::error('model_unavailable', __('Ce modèle d’IA n’est pas proposé.'), 422, [
                'models' => AiRate::query()->where('active', true)->orderBy('label')->pluck('model')->all(),
            ]);
        }

        $balance = $this->credits->balance($customer);
        if ($balance <= 0) {
            AiUsage::create($log + ['status' => 'blocked', 'error' => 'credits_exhausted']);

            return AuthenticateLicense::error('credits_exhausted', __('Vos crédits IA sont épuisés. Rechargez-les dans votre espace client GeniusPress.'), 402, ['balance' => $balance]);
        }
        if ($license->monthly_cap !== null) {
            $spent = (int) AiUsage::query()->where('license_id', $license->id)->where('created_at', '>=', now()->startOfMonth())->sum('billed');
            if ($spent >= $license->monthly_cap) {
                AiUsage::create($log + ['status' => 'blocked', 'error' => 'monthly_cap']);

                return AuthenticateLicense::error('monthly_cap', __('Le plafond mensuel de dépenses IA de ce site est atteint.'), 402);
            }
        }

        // Une requête HTTP = une demande : le modèle choisi par le client s'applique à cet appel.
        config(['geniuspress.ai.model' => $rate->model]);
        $started = microtime(true);

        try {
            $response = $llm->json($data['system'], $content, $data['schema'], $data['effort'] ?? 'medium', (int) ($data['max_tokens'] ?? 16000));
        } catch (AiRefusedException $e) {
            // Un refus est facturé par Anthropic selon les jetons consommés.
            $billing = $this->bill($customer, $rate, $e->model ?: $rate->model, $e->usage, $log, 'refused', $started);

            return response()->json(['error' => 'refused', 'message' => $e->getMessage(), 'usage' => $e->usage, 'model' => $billing['model']] + $billing['amounts'], 422);
        } catch (Throwable $e) {
            if (! $e instanceof AiException) {
                report($e);
            }
            AiUsage::create($log + ['status' => 'error', 'error' => mb_substr($e->getMessage(), 0, 255), 'duration_ms' => $this->ms($started)]);

            return AuthenticateLicense::error('upstream', $e instanceof AiException ? $e->getMessage() : __('L’assistant IA a rencontré une erreur. Réessayez.'), 502);
        }

        $billing = $this->bill($customer, $rate, (string) ($response['model'] ?? ''), $response['usage'], $log, 'ok', $started);

        return response()->json([
            'data' => $response['data'],
            'usage' => $response['usage'],
            'model' => $billing['model'],
        ] + $billing['amounts']);
    }

    /** Débite le client selon le modèle qui a répondu (s'il a un tarif), sinon le modèle demandé. */
    private function bill(Customer $customer, AiRate $requested, string $answeredModel, array $usage, array $log, string $status, float $started): array
    {
        $rate = $answeredModel !== '' && $answeredModel !== $requested->model ? ($this->pricing->rate($answeredModel) ?? $requested) : $requested;
        $input = (int) ($usage['input'] ?? 0);
        $output = (int) ($usage['output'] ?? 0);
        $cost = $this->pricing->cost($rate, $input, $output);
        $billed = $this->pricing->billed($rate, $input, $output);

        $usageRow = AiUsage::create(array_merge($log, [
            'model' => $rate->model, 'input_tokens' => $input, 'output_tokens' => $output,
            'cost' => $cost, 'billed' => $billed, 'status' => $status, 'duration_ms' => $this->ms($started),
        ]));
        if ($billed > 0) {
            $this->credits->debit($customer, $billed, 'usage', __('IA : :model', ['model' => $rate->label]), 'usage-'.$usageRow->id);
        }

        return ['model' => $rate->model, 'amounts' => ['billed' => $billed, 'balance' => $this->credits->balance($customer)]];
    }

    /** Ne garde que les blocs « text » et « image » (base64) bien formés. */
    private function cleanContent(mixed $blocks): ?array
    {
        $out = [];
        foreach ((array) $blocks as $block) {
            if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                $out[] = ['type' => 'text', 'text' => $block['text']];
            } elseif (($block['type'] ?? null) === 'image'
                && ($block['source']['type'] ?? null) === 'base64'
                && in_array($block['source']['media_type'] ?? null, self::IMAGE_TYPES, true)
                && is_string($block['source']['data'] ?? null)) {
                $out[] = ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $block['source']['media_type'], 'data' => $block['source']['data']]];
            } else {
                return null;
            }
        }

        return $out;
    }

    private function ms(float $started): int
    {
        return (int) ((microtime(true) - $started) * 1000);
    }
}
