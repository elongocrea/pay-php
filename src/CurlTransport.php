<?php

declare(strict_types=1);

namespace Elongo\Pay;

use Elongo\Pay\Exception\NetworkException;

/**
 * Transport HTTP par defaut, base sur cURL. Aucune dependance externe.
 */
final class CurlTransport implements Transport
{
    public function __construct(private int $timeout = 30) {}

    public function send(string $method, string $url, array $headers, ?string $body): array
    {
        $handle = curl_init($url);

        $curlHeaders = [];
        foreach ($headers as $name => $value) {
            $curlHeaders[] = $name.': '.$value;
        }

        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $curlHeaders,
        ]);

        if ($body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($handle);

        if ($response === false) {
            $error = curl_error($handle);
            throw new NetworkException($error !== '' ? $error : 'Requête réseau échouée.');
        }

        // Pas de curl_close() : sans effet depuis PHP 8.0, deprecie en 8.5. La
        // poignee est liberee a la sortie de portee.
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

        return ['status' => $status, 'body' => (string) $response];
    }
}
