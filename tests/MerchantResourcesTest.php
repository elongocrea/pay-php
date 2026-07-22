<?php

declare(strict_types=1);

use Elongo\Pay\Client;
use Elongo\Pay\Tests\FakeTransport;

it('liste les transactions avec les paramètres de requête', function () {
    $transport = new FakeTransport(200, '{"object":"list","data":[]}');
    $pay = new Client('sk_test', 'https://api.elongopay.cd', $transport);

    $pay->transactions->all(['per_page' => 10]);

    expect($transport->lastRequest['method'])->toBe('GET')
        ->and($transport->lastRequest['url'])->toBe('https://api.elongopay.cd/v1/transactions?per_page=10');
});

it('récupère une transaction', function () {
    $transport = new FakeTransport(200, '{"object":"payment","id":"tx_1"}');
    $pay = new Client('sk_test', 'https://api.elongopay.cd', $transport);

    $tx = $pay->transactions->retrieve('tx_1');

    expect($tx['id'])->toBe('tx_1')
        ->and($transport->lastRequest['url'])->toBe('https://api.elongopay.cd/v1/transactions/tx_1');
});

it('crée un remboursement sur une transaction', function () {
    $transport = new FakeTransport(201, '{"object":"refund","amount":2000}');
    $pay = new Client('sk_test', 'https://api.elongopay.cd', $transport);

    $refund = $pay->refunds->create('tx_1', ['amount' => 2000], idempotencyKey: 'r-1');

    expect($refund['amount'])->toBe(2000)
        ->and($transport->lastRequest['method'])->toBe('POST')
        ->and($transport->lastRequest['url'])->toBe('https://api.elongopay.cd/v1/transactions/tx_1/refunds')
        ->and($transport->lastRequest['headers']['Idempotency-Key'])->toBe('r-1');
});

it('récupère le solde', function () {
    $transport = new FakeTransport(200, '{"object":"balance","available":12345.67}');
    $pay = new Client('sk_test', 'https://api.elongopay.cd', $transport);

    $balance = $pay->balance->retrieve();

    expect($balance['available'])->toBe(12345.67)
        ->and($transport->lastRequest['url'])->toBe('https://api.elongopay.cd/v1/balance');
});

it('charge un paiement mobile', function () {
    $transport = new FakeTransport(201, '{"object":"payment","status":"pending"}');
    $pay = new Client('sk_test', 'https://api.elongopay.cd', $transport);

    $pay->payments->createMobile(['amount' => 5000, 'phone' => '243810000000', 'channel_id' => 'ch_1']);

    expect($transport->lastRequest['url'])->toBe('https://api.elongopay.cd/v1/payments/mobile')
        ->and($transport->lastRequest['method'])->toBe('POST');
});
