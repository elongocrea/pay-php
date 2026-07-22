<?php

declare(strict_types=1);

namespace Elongo\Pay\Resource;

use Elongo\Pay\Client;

/**
 * Transactions (paiements) du marchand.
 */
final class Transactions
{
    public function __construct(private Client $client) {}

    /**
     * Liste paginée. Paramètres pris en charge : `per_page`, `page`.
     *
     * @param  array<string, scalar>  $query
     * @return array<string, mixed>
     */
    public function all(array $query = []): array
    {
        $suffix = $query === [] ? '' : '?'.http_build_query($query);

        return $this->client->request('GET', 'v1/transactions'.$suffix);
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieve(string $id): array
    {
        return $this->client->request('GET', 'v1/transactions/'.rawurlencode($id));
    }
}
