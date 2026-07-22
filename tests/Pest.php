<?php

declare(strict_types=1);

namespace Elongo\Pay\Tests;

use Elongo\Pay\Transport;

/**
 * Transport factice : enregistre la derniere requete et renvoie une reponse
 * programmee. Permet de tester le client sans reseau.
 */
final class FakeTransport implements Transport
{
    /** @var array<string, mixed>|null */
    public ?array $lastRequest = null;

    public function __construct(
        private int $status = 200,
        private string $body = '{}',
    ) {}

    public function send(string $method, string $url, array $headers, ?string $body): array
    {
        $this->lastRequest = compact('method', 'url', 'headers', 'body');

        return ['status' => $this->status, 'body' => $this->body];
    }
}
