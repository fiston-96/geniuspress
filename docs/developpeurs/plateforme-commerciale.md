# Plateforme commerciale de GeniusPress

La plateforme commerciale est la partie de GeniusPress qui tourne **chez nous** (l'exploitant) : comptes clients, offres payantes, modèles payants, noms de domaine, crédits IA et licences. Le code est dans `app/Platform/`. Il est livré avec le cœur (AGPL) mais reste **inactif** tant que `GP_PLATFORM=true` n'est pas dans le `.env` : une installation ordinaire de GeniusPress n'expose aucune de ces pages.

Une installation ordinaire (locale ou sur le serveur du client) peut, elle, **utiliser** la plateforme grâce à une **clé de licence** : l'IA passe par notre relais, et les modèles payants s'achètent dans l'espace client.

```
Site GeniusPress du client ──(clé de licence)──▶ Plateforme ──(clé Anthropic)──▶ Claude
   (local, serveur du client,                    geniuspress.site
    ou site hébergé chez nous)                   comptes, crédits, journal
```

## 1. Qui est qui

| Rôle | Où | Connexion |
|---|---|---|
| **Exploitant** (nous) | `/admin/platform` sur le site de la plateforme | administrateur GeniusPress (`users`, rôle `admin`) |
| **Client** de la plateforme | `/compte` (espace client) | table `platform_customers`, garde `customer`, distinct des utilisateurs d'un site |
| **Site** d'un client | n'importe où | clé de licence `gpl_…` (en-tête `Authorization: Bearer`) |

Les comptes clients sont volontairement séparés des utilisateurs (`users`) : un client peut avoir plusieurs sites, et chaque site a ses propres utilisateurs.

## 2. Modèle de données

Toutes les tables commencent par `platform_`. Les montants d'argent sont des **entiers en plus petite unité** de la devise (centimes ; francs pour XOF, XAF, RWF… qui n'ont pas de décimales, voir `Money`). Les crédits IA sont des **entiers en micro-dollars** (1 µ$ = 0,000001 USD), l'unité dans laquelle Anthropic publie ses tarifs par million de jetons : `jetons × prix par million (USD) = coût en µ$`, sans arrondi intermédiaire.

| Table | Rôle | Colonnes principales |
|---|---|---|
| `platform_customers` | comptes clients | nom, e-mail, mot de passe (haché), téléphone, pays, langue, `status` (active/suspended), `trial_used_at`, `ai_balance` (µ$, solde mis en cache) |
| `platform_plans` | offres : hébergement, options, packs de crédits IA | `slug`, `kind` (hosting/option/ai_credits), nom et description FR/EN, `price`, `currency`, `period` (month/year/once), `trial_days`, `ai_credits` (µ$ inclus), `features`, `active` |
| `platform_subscriptions` | abonnements | client, offre, `status` (trialing/active/canceled/expired), `trial_ends_at`, `current_period_end`, `site_reference` (site hébergé, rempli par `app/Hosting`) |
| `platform_template_prices` | modèles payants | `template_id` (identifiant du catalogue), `price`, `currency`, `active`. Un modèle absent de la table est gratuit. |
| `platform_entitlements` | accès achetés | client, `kind` (template), `reference`, commande d'origine, `expires_at` |
| `platform_orders` / `platform_order_items` | commandes et lignes | numéro, `status` (pending/paid/failed/canceled), total, devise ; ligne : `kind` (plan/template/ai_credits/domain_register/domain_renew), `reference`, montant, `fulfilled_at` |
| `platform_payments` | tentatives de paiement | commande, prestataire, moyen (card/mobile_money), opérateur, `reference` du prestataire, `status`, montant |
| `platform_tld_prices` | prix de vente des extensions de domaine | `tld`, prix d'achat 1 an, prix de renouvellement, devise, `active` |
| `platform_domains` | domaines des clients | nom, client, `status`, registraire, `registered_at`, `expires_at` |
| `platform_ai_rates` | **tarifs Claude par modèle**, renseignés par l'exploitant | `model`, libellé, `input_price` et `output_price` (USD par million de jetons), `active` |
| `platform_credit_transactions` | grand livre des crédits IA | client, `amount` (µ$, signé), `balance_after`, `kind` (purchase/usage/grant/adjustment), référence, description |
| `platform_licenses` | clés de licence | client, nom du site, `key_prefix` (public), `key_hash` (SHA-256), `site_url`, `status`, `monthly_cap` (µ$, facultatif), `last_used_at`, `last_ip` |
| `platform_ai_usages` | journal du relais IA | client, licence, action, modèle, jetons d'entrée/sortie, `cost` (coût réel µ$), `billed` (µ$ débités), `status`, IP, durée |

Le multiplicateur de prix de l'IA est le réglage privé `platform_ai_multiplier` (2 par défaut), modifiable dans l'administration. Aucun prix n'est codé en dur : la table des tarifs est vide à l'installation, et un modèle sans tarif actif est **refusé** par le relais.

## 3. Parcours d'achat

1. Le client choisit une offre, un pack de crédits, un modèle payant ou un domaine dans `/compte`.
2. `Checkout` crée une **commande** `pending` (lignes et prix lus en base, jamais dans le formulaire).
3. Le client choisit son moyen de paiement : **Mobile Money** (numéro + opérateur : Orange Money, MTN MoMo, Airtel Money, M-Pesa, Moov, Wave) ou **carte**. `PaymentGateway::start()` renvoie soit une adresse de paiement (page du prestataire), soit « en attente de confirmation sur le téléphone ».
4. Le prestataire prévient la plateforme (webhook `POST /api/platform/v1/payments/{gateway}/webhook`, signature vérifiée par la passerelle) ; le client revient aussi sur `/compte/commandes/{numéro}`. Dans les deux cas, la plateforme **redemande l'état au prestataire** (`verify()`) : un paramètre d'adresse ou un webhook seul ne valide jamais un paiement.
5. Paiement confirmé et montant/devise identiques à la commande → `OrderFulfiller` passe la commande à `paid` **une seule fois** (verrou sur la ligne de commande) et livre chaque ligne :
   - offre → abonnement créé ou prolongé d'une période, crédits IA inclus ajoutés ;
   - pack de crédits → crédit dans le grand livre ;
   - modèle payant → accès (`platform_entitlements`) ;
   - domaine → enregistrement ou renouvellement chez le registraire. En cas d'échec du registraire, la ligne reste non livrée et apparaît dans l'administration (remboursement ou nouvel essai manuel).

Interfaces :

- `App\Platform\Payments\PaymentGateway` — `name()`, `methods()`, `start(Payment, array $details)`, `verify(Payment)`, `webhookReference(Request)`. Implémentation de test : `SandboxPaymentGateway` (aucun appel réseau : une page `/compte/paiement-test/{référence}` permet de simuler la réussite ou l'échec). Choix par `GP_PAYMENT_GATEWAY` (défaut `sandbox`).
- `App\Platform\Domains\DomainRegistrar` — `name()`, `check(array $domains)`, `register(string $domain, int $years, array $contact)`, `renew(string $domain, int $years)`. Implémentation de test : `SandboxRegistrar` (règles déterministes, aucun appel réseau). Choix par `GP_DOMAIN_REGISTRAR` (défaut `sandbox`).

**Prestataire de paiement retenu : Monerails** (`MonerailsGateway`, `GP_PAYMENT_GATEWAY=monerails`, documentation : https://docs.monerails.com). Une seule API pour le Mobile Money dans 22 pays africains, dont la RDC (Airtel Money, M-Pesa, Orange Money, Afrimoney, Vodacom).

- Encaissement : `POST /v1/collections` avec `Idempotency-Key` et `client_reference` = notre référence de paiement ; l'identifiant de la collecte est gardé dans `platform_payments.gateway_reference`. État relu par `GET /v1/collections/{id}` (montant et devise comparés à la commande).
- Notifications : `POST /api/platform/v1/payments/monerails/webhook`, à déclarer dans le tableau de bord Monerails (événements `collection.*`). Signature `Monerail-Signature: t=…,v1=…` (HMAC-SHA256 de `t.corps` avec le secret `whsec_…`), horodatage à ±5 minutes.
- Clés dans le `.env` seulement : `GP_MONERAILS_KEY` (`mr_test_…` ou `mr_live_…`) et `GP_MONERAILS_WEBHOOK_SECRET`. Une clé de test est refusée en production (elle validerait des paiements sans argent réel), sauf `GP_PLATFORM_SANDBOX=true`.
- **Limites à connaître** : Monerails n'encaisse **pas les cartes** de nos clients (ses cartes Visa virtuelles sont des cartes que nous détenons, rechargées en cryptomonnaie depuis son tableau de bord) ; en RDC, seul le **CDF** est ouvert (l'USD est « en ouverture ») : les offres vendues en RDC doivent donc avoir un prix en CDF, en francs entiers. Les opérateurs qui valident par code SMS (`requires_otp`) ne sont pas encore pris en charge. Les codes d'opérateurs sont à confirmer avec `GET /v1/operators?currency=CDF&country=CD` avant la mise en service. Monerails n'a pas de SDK PHP : l'intégration utilise le client HTTP de Laravel.
- Pour les cartes, il faudra un second prestataire (CinetPay, Flutterwave…) ; `PaymentManager` ne gère aujourd'hui qu'un prestataire à la fois : le choix du prestataire selon le moyen de paiement est à ajouter à ce moment-là.

**À décider par l'exploitant** : le prestataire des paiements par carte, et le registraire (LWS, OVH, Namecheap, ResellerClub… : API revendeur, extensions africaines comme `.cd`, `.ci`, `.sn`, `.cm`). Chaque implémentation réelle est une classe de plus derrière l'interface ; ses clés vont dans le `.env` uniquement.

## 4. Crédits IA et règle de prix

Pour chaque appel : `coût réel (µ$) = jetons d'entrée × prix d'entrée + jetons de sortie × prix de sortie` (prix du modèle qui a réellement répondu, sinon du modèle demandé), puis **débit = arrondi supérieur (coût réel × multiplicateur)**. Le multiplicateur vaut 2 par défaut : le client paie deux fois le coût réel. Les deux montants sont gardés dans le journal (`cost` et `billed`) pour le suivi de la marge.

- Les packs de crédits sont des offres `kind = ai_credits` : l'exploitant fixe le prix de vente (dans n'importe quelle devise) et le montant de crédits (en USD). Les offres d'hébergement peuvent inclure des crédits.
- `CreditAccount` crédite et débite dans une transaction avec verrou sur le compte client : le solde (`ai_balance`) et le grand livre restent cohérents même avec des appels simultanés.
- **Blocage** : un appel est refusé avant d'atteindre Claude si le solde est nul ou négatif, si la licence a dépassé son plafond mensuel, ou si le client est suspendu. Le site reçoit une réponse 402 et l'affiche en français (`AiException` : « Vos crédits IA sont épuisés… »). Le coût n'étant connu qu'après la réponse, le dernier appel peut rendre le solde légèrement négatif ; le dépassement est repris au prochain rechargement.
- Une réponse refusée par l'IA (`refusal`) est facturée selon les jetons consommés, comme Anthropic la facture ; une erreur du service n'est pas facturée.

## 5. Licences et relais IA

**Clé** : `gpl_` + 12 caractères publics + `_` + 40 caractères aléatoires (alphabet base62, ≈ 238 bits). La partie publique (`key_prefix`) sert à retrouver la licence ; seule l'empreinte SHA-256 de la clé complète est en base, comparée avec `hash_equals`. La clé n'est affichée **qu'une fois**, à la création ; le client peut la révoquer et en créer une autre. Côté site, elle est chiffrée dans les réglages (`gp_license_key`, réglage privé) ou fournie par `GP_LICENSE_KEY`.

**API de la plateforme** (`/api/platform/v1`, sans session ni cookie, JSON) :

| Méthode | Adresse | Rôle |
|---|---|---|
| `GET` | `/account` | client, offre active, solde, modèles proposés (prix facturés), modèles achetés |
| `POST` | `/ai/messages` | relais IA : `{model, system, content, schema, effort, max_tokens, action}` → `{data, usage, model, billed, balance}` |
| `GET` | `/templates/{id}/download` | modèle payant (accès vérifié) |
| `GET` | `/catalog/prices` | prix publics des modèles payants (sans clé) |
| `POST` | `/payments/{gateway}/webhook` | notifications des prestataires de paiement |

**Côté site** : quand une clé de licence est configurée, `LlmGateway` est lié à `App\Platform\Client\PlatformAiGateway`, qui envoie la demande au relais au lieu d'appeler Anthropic (le reste de l'assistant ne change pas). Sinon, `AnthropicGateway` reste utilisé avec la clé Anthropic du site : GeniusPress reste utilisable sans nous.

**Sur la plateforme**, `RelayController` : vérifie la clé, le client, la taille de la demande (`max_tokens` plafonné, contenu limité), le modèle (tarif actif obligatoire) et le solde ; appelle Claude par `LlmGateway` (clé Anthropic de la plateforme, dans le `.env`) ; débite ; écrit le journal ; répond.

Les sites hébergés chez nous (`app/Hosting`) reçoivent une licence comme les autres : un seul chemin pour la facturation.

## 6. Sécurité

- **Clés** : clés de licence hachées, affichées une fois, révocables ; clés des prestataires (Anthropic, paiement, registraire) uniquement dans le `.env`, jamais en base, jamais affichées, jamais dans les journaux ni dans `Setting::publicValues()`.
- **Limites** : relais limité par licence (`GP_RELAY_PER_MINUTE`, 30/min par défaut) et par IP ; `max_tokens` ≤ 32 000 ; demande ≤ 8 Mo (images comprises) ; plafond mensuel facultatif par licence ; connexion et inscription des clients limitées (5/min) ; mots de passe de 10 caractères minimum.
- **Journaux** : chaque appel relayé (licence, modèle, jetons, coût, débit, IP, durée, statut) ; chaque mouvement de crédits ; chaque paiement. Le **contenu** des demandes IA n'est jamais enregistré.
- **Argent** : prix toujours relus en base ; paiement toujours revérifié auprès du prestataire ; livraison idempotente ; ajustements manuels de crédits par l'exploitant avec motif obligatoire, inscrits au grand livre.
- **Isolement** : routes de la plateforme répondant 404 sans `GP_PLATFORM=true` ; administration réservée aux administrateurs ; garde `customer` séparée de celle des utilisateurs du site.

## 7. Interface pour l'essai hébergé (`app/Hosting`)

La session « Essai hébergé » n'utilise que le service `App\Platform\PlatformAccounts` (injection par le conteneur) :

```php
$accounts = app(\App\Platform\PlatformAccounts::class);

$accounts->register(name: 'Amani', email: 'amani@example.org', password: '…'): Customer; // compte client
$accounts->findByEmail('amani@example.org'): ?Customer;
$accounts->trialPlan(): ?Plan;                       // offre d'hébergement proposée à l'essai (réglage exploitant)
$accounts->canStartTrial($customer): bool;           // compte actif, essai jamais utilisé, offre d'essai disponible
$accounts->startTrial($customer, siteReference: 'site-42'): Subscription; // abonnement « trialing », essai marqué utilisé
$accounts->activeSubscription($customer, kind: 'hosting'): ?Subscription; // offre en cours (essai compris)
$accounts->attachSite($subscription, 'site-42'): void;
$accounts->issueLicense($customer, name: 'Site d’essai', siteUrl: 'https://…'): array; // ['license' => License, 'key' => 'gpl_…'] — clé en clair une seule fois
```

Côté facturation, `activatePlan($customer, $plan)` (appelé à la livraison d'une commande payée) transforme un essai en abonnement payé sans en créer un second, la période payée commençant à la fin de l'essai ; `cancel($subscription)` résilie à l'échéance.

`Subscription` expose `isActive()`, `onTrial()`, `endsAt()` (fin de l'essai ou de la période) et `plan` (avec `features`, `kind`). Une `PlatformException` (message en français) signale un refus (essai déjà utilisé, compte suspendu…).

## 8. Configuration (`.env`)

```
GP_PLATFORM=true                 # active la plateforme sur ce serveur
GP_PLATFORM_CURRENCY=USD         # devise par défaut des offres
GP_PAYMENT_GATEWAY=sandbox       # prestataire par défaut : sandbox | monerails (la console → Paiements l'emporte)
GP_MONERAILS_KEY=…               # facultatif : de préférence dans la console (Paiements), chiffrée
GP_MONERAILS_WEBHOOK_SECRET=…    # idem (whsec_…)
GP_DOMAIN_REGISTRAR=sandbox      # registraire : sandbox | lws
GP_SERVER_IP=78.24.248.103       # IP des sites hébergés : un domaine branché pointe vers elle
GP_LWS_LOGIN=… / GP_LWS_KEY=…    # facultatif : de préférence dans la console (Fournisseurs), clé chiffrée
GP_RELAY_PER_MINUTE=30
ANTHROPIC_API_KEY=…              # clé de la plateforme pour le relais (jamais affichée)

# Côté site d'un client
GP_LICENSE_KEY=…                 # ou dans Réglages → Assistant IA
GP_PLATFORM_URL=https://geniuspress.site
```

## 9. Fournisseurs (LWS) : domaines, hébergement web, mail pro, stockage

GeniusPress revend des services LWS avec sa marge, sans que LWS apparaisse dans l'espace client
(`app/Platform/Suppliers/`). API : https://docs-api.lws.net/fr/api-client/ (en-têtes `X-Auth-Login`,
`X-Auth-Pass`, `X-Test-Mode` ; achats débités sur le compte prépayé, traités en différé).

- **Connexion** : console → Fournisseurs. Identifiant LWS, clé chiffrée (`Crypt`, jamais réaffichée,
  réglages privés `platform_lws_*`), **mode test activé par défaut** (rien n'est créé ni facturé).
  L'IP du serveur doit être autorisée dans l'espace LWS (onglet « Api LWS »).
- **Domaines** (`GP_DOMAIN_REGISTRAR=lws`, `LwsRegistrar`) : achat au nom du client (contact LWS créé
  une fois avec ses vraies coordonnées, exigées avant le paiement, retenu dans `provider_ids`),
  `POST /hosting` offre « domaine ». Renouvellement un an à la fois (`maxRenewYears`). Code de transfert
  envoyé au titulaire par LWS. Zone DNS : `pointTo()` pose les A `@` et `www` vers `GP_SERVER_IP`.
- **Branchement sur un site hébergé** (espace client → Domaines → Brancher) : zone DNS, puis
  `SiteProvisioner::addHost()` (index `storage/hosting/hosts/`). Le certificat et le fichier Nginx du
  domaine sont faits par `scripts/hosting/certificats-essais.sh` (voir essai-heberge.md).
- **Offres partenaires** : offres de type `web_hosting`, `mail`, `storage` avec un fournisseur
  (`lws` ou `manual`), un nom chez LWS (`provider_ref`) et un coût. L'hébergement web LWS est acheté par
  l'API à la livraison ; le mail pro et le stockage (absents de l'API) et tous les renouvellements vont
  dans la file « À livrer » de la console (`platform_services.pending_action`), les exploitants sont
  prévenus, puis le client quand l'équipe marque le service comme livré (`ServiceDesk`).
- **Marges** : coûts synchronisés chaque nuit (`geniuspress:suppliers sync` : extensions et offres
  « lws » ; prix LWS mensuel × 12 pour une offre annuelle), taux de change (1 € = x devise) et marge
  conseillée réglés dans la console (`SupplierPricing`). Les prix de vente restent ceux de l'exploitant ;
  « Appliquer les prix conseillés » les recalcule pour les extensions.
- **Solde** : `geniuspress:suppliers balance` chaque heure, alerte aux exploitants sous le seuil (une fois par jour).
- **Vitrine** : widget `platform-offers` (déclaré seulement avec `GP_PLATFORM=true`), page Hébergement de
  `website/showcase.php`. La politique de confidentialité (`website/site.php`) nomme LWS comme prestataire.
- Tests : `tests/Feature/Platform/SuppliersTest.php`, API simulée par `Http::fake`, jamais d'appel réel.

## 10. Paiements Mobile Money (Monerails)

`App\Platform\Payments\MonerailsGateway` (https://docs.monerails.com) : encaissement Mobile Money dans les
pays ouverts par l'API (`MonerailsGateway::COUNTRIES`, chacun dans sa devise : CDF, XAF, XOF, GNF, GMD,
UGX, TZS, RWF). Monerails n'encaisse ni les cartes ni l'USD.

- **Console → Paiements** : prestataire actif, clé API et secret de notification (chiffrés, jamais
  réaffichés, refus d'une clé de test en production), adresse de notification à déclarer chez Monerails
  (`/api/platform/v1/payments/monerails/webhook`), essai de connexion (soldes).
- **Devise locale** : le client choisit le pays de son compte Mobile Money ; une commande dans une autre
  devise (USD) est convertie au taux de la console (Fournisseurs → taux de change, 1 € = x) par
  `Checkout::localAmount()`, arrondie à l'unité entière supérieure. Le paiement garde le montant d'origine
  (`original_amount`, `original_currency`) ; `OrderFulfiller` n'accepte que le total exact de la commande,
  ou sa conversion. Un pays sans taux n'est pas proposé.
- **Opérateurs** : liste en direct (`GET /v1/operators` par devise, gardée 10 minutes), liste de repli
  si l'API ne répond pas. Opérateurs à code (OTP, ou code obtenu par USSD) : le paiement attend le code
  (`awaiting = otp`), saisi sur la page de la commande et transmis par `authorize`.
- **Dollars** : la console active l'USD pays par pays (`platform_monerails_usd`), seulement quand Monerails
  l'ouvre (en cours en RD Congo). Le client choisit alors « pays (devise locale) » ou « pays (USD) ». Un
  message rappelle que l'opérateur prélève le solde de la devise choisie, jamais un solde dans une autre devise.
- **Pays du client** (`App\Platform\Countries`) : choisi à l'inscription (suggéré d'après la langue du
  navigateur) ou dans l'espace client ; les prix y sont aussi affichés dans sa devise, à titre indicatif
  (« ≈ 28 704 CDF », composant `x-platform.local-price`), et son pays est présélectionné au paiement.
- Le contenu d'une notification ne suffit jamais : l'état est redemandé à Monerails (montant et devise
  comparés au paiement demandé).
- Tests : `tests/Feature/Platform/MonerailsGatewayTest.php` (Http::fake, aucune clé réelle).

## 11. Reste à faire

- `npm run build` au déploiement : les vues de la plateforme utilisent quelques classes Tailwind nouvelles.

- Prestataire des paiements par carte et registraire, après choix de l'exploitant ; validation des paiements Monerails par code SMS.
- Retirer des fichiers publics du catalogue les zips des modèles payants (servis seulement par `/templates/{id}/download`).
- Factures PDF, e-mails de confirmation et rappels d'échéance, vérification de l'adresse e-mail des clients.
