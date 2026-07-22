<?php

declare(strict_types=1);

namespace Elongo\Pay;

use Elongo\Pay\Exception\ApiException;
use Elongo\Pay\Resource\Balance;
use Elongo\Pay\Resource\Checkout;
use Elongo\Pay\Resource\Payments;
use Elongo\Pay\Resource\Refunds;
use Elongo\Pay\Resource\Transactions;

/**
 * Client de l'API Elongo Pay.
 *
 *   $pay = new Client('sk_live_…');
 *   $session = $pay->checkout->create([...]);
 *   header('Location: '.$session['url']);
 */
final class Client
{
    public readonly Checkout $checkout;

    public readonly Payments $payments;

    public readonly Transactions $transactions;

    public readonly Refunds $refunds;

    public readonly Balance $balance;

    private Transport $transport;

    private string $baseUrl;

    public function __construct(
        private string $apiKey,
        ?string $baseUrl = null,
        ?Transport $transport = null,
    ) {
        $this->baseUrl = rtrim($baseUrl ?? 'https://api.elongopay.cd', '/');
        $this->transport = $transport ?? new CurlTransport;

        $this->checkout = new Checkout($this);
        $this->payments = new Payments($this);
        $this->transactions = new Transactions($this);
        $this->refunds = new Refunds($this);
        $this->balance = new Balance($this);
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $params = [], array $headers = []): array
    {
        $body = $params === [] ? null : (string) json_encode($params);

        $response = $this->transport->send(
            $method,
            $this->baseUrl.'/'.ltrim($path, '/'),
            [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                ...$headers,
            ],
            $body,
        );

        /** @var array<string, mixed> $decoded */
        $decoded = $response['body'] === ''
            ? []
            : (array) json_decode($response['body'], true);

        if ($response['status'] >= 400) {
            $message = is_string($decoded['message'] ?? null)
                ? $decoded['message']
                : 'Requête API échouée.';

            throw new ApiException($message, $response['status'], $decoded);
        }

        return $decoded;
    }
}
