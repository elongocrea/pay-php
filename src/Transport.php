<?php

declare(strict_types=1);

namespace Elongo\Pay;

/**
 * Transport HTTP minimal.
 *
 * Abstrait l'envoi reseau pour que le client soit testable sans reseau : les
 * tests injectent un faux transport. L'implementation par defaut est cURL, sans
 * dependance externe.
 */
interface Transport
{
    /**
     * @param  array<string, string>  $headers
     * @return array{status: int, body: string}
     */
    public function send(string $method, string $url, array $headers, ?string $body): array;
}
