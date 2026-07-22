<?php

declare(strict_types=1);

namespace Elongo\Pay\Resource;

use Elongo\Pay\Client;

/**
 * Remboursements.
 */
final class Refunds
{
    public function __construct(private Client $client) {}

    /**
     * Rembourse une transaction (total ou partiel).
     *
     * @param  array<string, mixed>  $params  amount, reason, metadata
     * @return array<string, mixed>
     */
    public function create(string $transactionId, array $params, ?string $idempotencyKey = null): array
    {
        $headers = $idempotencyKey !== null ? ['Idempotency-Key' => $idempotencyKey] : [];

        return $this->client->request(
            'POST',
            'v1/transactions/'.rawurlencode($transactionId).'/refunds',
            $params,
            $headers,
        );
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array<string, mixed>
     */
    public function all(array $query = []): array
    {
        $suffix = $query === [] ? '' : '?'.http_build_query($query);

        return $this->client->request('GET', 'v1/refunds'.$suffix);
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieve(string $id): array
    {
        return $this->client->request('GET', 'v1/refunds/'.rawurlencode($id));
    }
}
