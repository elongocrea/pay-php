<?php

declare(strict_types=1);

namespace Elongo\Pay\Resource;

use Elongo\Pay\Client;

/**
 * Solde du marchand.
 *
 * @deprecated Fonction historique d'Elongo Pay, en cours de retrait : les
 *             paiements sont encaisses directement sur le compte de
 *             l'etablissement chez l'agregateur. L'endpoint v1/balance
 *             repondra 404 une fois la fonction coupee cote serveur.
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
