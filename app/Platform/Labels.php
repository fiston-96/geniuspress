<?php

namespace App\Platform;

/** Libellés traduits des états et types de la plateforme. */
final class Labels
{
    public static function status(?string $status): string
    {
        return match ($status) {
            'pending' => __('En attente'),
            'paid' => __('Payée'),
            'failed' => __('Échec'),
            'canceled' => __('Résilié'),
            'succeeded' => __('Réussi'),
            'trialing' => __('Essai'),
            'active' => __('Actif'),
            'expired' => __('Expiré'),
            'suspended' => __('Suspendu'),
            'revoked' => __('Révoquée'),
            'ok' => __('Réussi'),
            'refused' => __('Refusé'),
            'error' => __('Erreur'),
            'blocked' => __('Bloqué'),
            default => (string) $status,
        };
    }

    public static function kind(?string $kind): string
    {
        return match ($kind) {
            'hosting' => __('Hébergement'),
            'option' => __('Option'),
            'ai_credits' => __('Crédits IA'),
            'plan' => __('Offre'),
            'template' => __('Modèle de site'),
            'domain_register' => __('Nom de domaine'),
            'domain_renew' => __('Renouvellement de domaine'),
            'purchase' => __('Achat'),
            'grant' => __('Offert'),
            'usage' => __('Consommation'),
            'adjustment' => __('Ajustement'),
            default => (string) $kind,
        };
    }

    public static function period(?string $period): string
    {
        return match ($period) {
            'month' => __('par mois'),
            'year' => __('par an'),
            'once' => __('paiement unique'),
            default => (string) $period,
        };
    }

    public static function method(?string $method): string
    {
        return match ($method) {
            'mobile_money' => __('Mobile Money'),
            'card' => __('Carte bancaire'),
            'wallet' => __('Solde de parrainage'),
            default => (string) $method,
        };
    }
}
