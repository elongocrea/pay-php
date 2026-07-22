<?php

declare(strict_types=1);

namespace Elongo\Pay\Resource;

use Elongo\Pay\Client;

/**
 * Paiements — charge directe (l'application collecte le moyen de paiement).
 *
 * Pour déléguer entièrement le paiement, préférez `checkout->create()`.
 */
final class Payments
{
    public function __construct(private Client $client) {}

    /**
     * Charge un paiement mobile money.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function createMobile(array $params, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', 'v1/payments/mobile', $params, $this->idem($idempotencyKey));
    }

    /**
     * Charge un paiement par carte.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function createCard(array $params, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', 'v1/payments/card', $params, $this->idem($idempotencyKey));
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieve(string $id): array
    {
        return $this->client->request('GET', 'v1/payments/'.rawurlencode($id));
    }

    /**
     * @return array<string, mixed>
     */
    public function status(string $id): array
    {
        return $this->client->request('GET', 'v1/payments/'.rawurlencode($id).'/status');
    }

    /**
     * @return array<string, string>
     */
    private function idem(?string $key): array
    {
        return $key !== null ? ['Idempotency-Key' => $key] : [];
    }
}
