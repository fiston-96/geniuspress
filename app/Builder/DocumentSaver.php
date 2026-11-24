<?php

namespace App\Builder;

use App\Models\ContentRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Enregistre le contenu du builder d'un article ou d'un gabarit (modèles qui
 * utilisent HasBuilderContent), comme pour les pages :
 *   - publier : le contenu devient visible, le brouillon disparaît ;
 *   - document en ligne : la version publiée reste intacte, le brouillon est gardé à part ;
 *   - brouillon : le contenu est remplacé.
 * Chaque enregistrement crée une version ; seules les 30 dernières sont gardées.
 */
final class DocumentSaver
{
    public const MAX_REVISIONS = 30;

    public function __construct(private ContentSanitizer $sanitizer) {}

    public function save(Model $doc, mixed $input, bool $publish, User $user): array
    {
        $content = $this->sanitizer->sanitize($input);

        DB::transaction(function () use ($doc, $content, $publish, $user) {
            if ($publish) {
                $doc->content = $content;
                $doc->draft_content = null;
                $doc->status = 'published';
                if ($doc->isFillable('published_at')) {
                    $doc->published_at ??= now();
                }
            } elseif ($doc->status === 'published') {
                $doc->draft_content = $content;
            } else {
                $doc->content = $content;
            }
            $doc->save();

            $doc->revisions()->create(['user_id' => $user->id, 'content' => $content, 'note' => $publish ? 'Publication' : 'Enregistrement']);
            $keep = $doc->revisions()->limit(self::MAX_REVISIONS)->pluck('id');
            ContentRevision::query()->whereMorphedTo('revisable', $doc)->whereNotIn('id', $keep)->delete();
        });

        return [
            'content' => $content,
            'status' => $doc->status,
            'has_draft' => $doc->hasUnpublishedChanges(),
            'saved_at' => $doc->updated_at->toIso8601String(),
            'revisions' => self::revisions($doc),
        ];
    }

    public static function revisions(Model $doc)
    {
        return $doc->revisions()->with('user:id,name')->limit(self::MAX_REVISIONS)->get(['id', 'user_id', 'note', 'created_at'])
            ->map->only(['id', 'note', 'created_at', 'user']);
    }

    /** Version restaurée (convertie au format actuel), si elle appartient bien au document. */
    public function restore(Model $doc, ContentRevision $revision): array
    {
        abort_unless($revision->revisable_type === $doc->getMorphClass() && (int) $revision->revisable_id === (int) $doc->getKey(), 404);

        return ['content' => $this->sanitizer->sanitize($revision->content)];
    }
}
