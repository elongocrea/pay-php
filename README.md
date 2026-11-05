# Elongo Pay — SDK PHP

SDK officiel pour [Elongo Pay](https://elongopay.cd) : crée des sessions de
checkout et vérifie les webhooks, sans écrire une seule ligne d'appel HTTP ni de
vérification de signature.

Aucune dépendance runtime (cURL + JSON, tous deux fournis par PHP).

## Installation

```bash
composer require elongo/pay-php
```

## Créer un paiement

Le client est redirigé vers une page hébergée par Elongo Pay. Votre application
ne voit jamais de numéro de carte ni de moyen de paiement.

```php
use Elongo\Pay\Client;

$pay = new Client('sk_live_votre_cle');

$session = $pay->checkout->create([
    'amount'              => 25000,
    'currency'            => 'CDF',
    'success_url'         => 'https://votre-app.cd/commande/ok',
    'cancel_url'          => 'https://votre-app.cd/commande/ko',
    'client_reference_id' => $commande->id,
    'metadata'            => ['commande_id' => $commande->id],
], idempotencyKey: "commande-{$commande->id}");

header('Location: '.$session['url']);
```

L'`idempotencyKey` (facultative mais recommandée) garantit qu'un rejeu réseau ne
crée pas deux sessions pour la même commande.

## Récupérer une session ou un paiement

```php
$session = $pay->checkout->retrieve('cs_live_…');
$payment = $pay->payments->retrieve('…');
```

## Vérifier un webhook

Elongo Pay notifie votre `callback_url` à chaque changement d'état. **Vérifiez
toujours la signature** avant de traiter l'événement : c'est ce qui prouve qu'il
vient bien d'Elongo Pay.

```php
use Elongo\Pay\Webhook;
use Elongo\Pay\Exception\ElongoPayException;

$payload = file_get_contents('php://input');
$header  = $_SERVER['HTTP_ELONGO_SIGNATURE'] ?? '';
$secret  = getenv('ELONGO_WEBHOOK_SECRET'); // whsec_…

try {
    $event = Webhook::constructEvent($payload, $header, $secret);
} catch (ElongoPayException) {
    http_response_code(400);
    exit;
}

match ($event['type']) {
    Webhook::CHECKOUT_SESSION_COMPLETED => marquerPayee($event['data']['client_reference_id']),
    Webhook::PAYMENT_SUCCEEDED => marquerPayee($event['data']['reference']), // charge directe
    Webhook::PAYMENT_FAILED => marquerEchouee($event['data']['reference']),
    default => null, // type inconnu : ignorer, répondre 200
};

http_response_code(200);
```

Le secret `whsec_…` est celui de l'**application** qui porte la clé API (une
application, donc une clé, par intégration). Un secret vide est toujours refusé.

| Événement | `data` |
|---|---|
| `checkout.session.completed` | la session (`id`, `client_reference_id`, `amount`, `currency`, `metadata`) |
| `payment.succeeded` / `payment.failed` | le paiement d'une charge directe (`id`, `reference`, `status`, `amount`, `currency`) |

### Le schéma de signature

Pour les intégrations hors PHP, l'en-tête est documenté et réimplémentable :

```
Elongo-Signature: t=<timestamp>,v1=<hmac_sha256(secret, "<t>.<payload>")>
```

Recomposez `"<t>.<payload>"`, calculez le HMAC-SHA256 avec votre secret,
comparez en temps constant, et rejetez au-delà de 5 minutes d'écart (anti-rejeu).

## Tests

```bash
composer install
./vendor/bin/pest
```

## Configuration avancée

```php
// URL de l'API (vide ou null = https://api.elongopay.cd), transport personnalisé.
$pay = new Client('sk_test_…', 'https://api.elongopay.cd', $transport);
```

### Mode test

Une clé `sk_test_…` n'appelle jamais d'agrégateur : le paiement est simulé
immédiatement. Un numéro se terminant par `0000` simule un échec, tout autre
numéro un succès. Aucun mouvement de fonds réel.
