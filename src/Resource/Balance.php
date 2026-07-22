<?php

declare(strict_types=1);

namespace Elongo\Pay\Resource;

use Elongo\Pay\Client;

/**
 * Solde du marchand.
 */
final class Balance
{
    public function __construct(private Client $client) {}

    /**
     * @return array<string, mixed>
     */
    public function retrieve(): array
    {
        return $this->client->request('GET', 'v1/balance');
    }
}
