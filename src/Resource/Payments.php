<?php

declare(strict_types=1);

namespace Elongo\Pay\Resource;

use Elongo\Pay\Client;

/**
 * Paiements.
 */
final class Payments
{
    public function __construct(private Client $client) {}

    /**
     * @return array<string, mixed>
     */
    public function retrieve(string $id): array
    {
        return $this->client->request('GET', 'v1/payments/'.rawurlencode($id));
    }
}
