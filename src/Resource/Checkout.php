<?php

declare(strict_types=1);

namespace Elongo\Pay\Resource;

use Elongo\Pay\Client;

/**
 * Sessions de checkout.
 */
final class Checkout
{
    public function __construct(private Client $client) {}

    /**
     * Cree une session et renvoie sa representation, dont l'URL hebergee.
     *
     * @param  array<string, mixed>  $params
     * @param  string|null  $idempotencyKey  Rejoue la meme reponse si repete.
     * @return array<string, mixed>
     */
    public function create(array $params, ?string $idempotencyKey = null): array
    {
        $headers = $idempotencyKey !== null
            ? ['Idempotency-Key' => $idempotencyKey]
            : [];

        return $this->client->request('POST', 'v1/checkout/sessions', $params, $headers);
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieve(string $id): array
    {
        return $this->client->request('GET', 'v1/checkout/sessions/'.rawurlencode($id));
    }
}
