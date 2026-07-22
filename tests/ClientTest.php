<?php

declare(strict_types=1);

use Elongo\Pay\Client;
use Elongo\Pay\Exception\ApiException;
use Elongo\Pay\Tests\FakeTransport;

it('cree une session avec la bonne requete', function () {
    $transport = new FakeTransport(201, json_encode([
        'id' => 'cs_test_abc',
        'url' => 'https://checkout.elongopay.cd/cs_test_abc',
        'status' => 'open',
    ]));

    $pay = new Client('sk_test_key', 'https://api.elongopay.cd', $transport);

    $session = $pay->checkout->create([
        'amount' => 25000,
        'currency' => 'CDF',
        'success_url' => 'https://m.example/ok',
        'cancel_url' => 'https://m.example/ko',
    ], idempotencyKey: 'order-42');

    // Reponse exposee telle quelle.
    expect($session['id'])->toBe('cs_test_abc')
        ->and($session['url'])->toContain('checkout.elongopay.cd');

    // Requete construite correctement.
    $req = $transport->lastRequest;
    expect($req['method'])->toBe('POST')
        ->and($req['url'])->toBe('https://api.elongopay.cd/v1/checkout/sessions')
        ->and($req['headers']['Authorization'])->toBe('Bearer sk_test_key')
        ->and($req['headers']['Idempotency-Key'])->toBe('order-42')
        ->and(json_decode($req['body'], true)['amount'])->toBe(25000);
});

it('recupere une session', function () {
    $transport = new FakeTransport(200, json_encode(['id' => 'cs_test_abc', 'status' => 'completed']));
    $pay = new Client('sk_test_key', 'https://api.elongopay.cd', $transport);

    $session = $pay->checkout->retrieve('cs_test_abc');

    expect($session['status'])->toBe('completed')
        ->and($transport->lastRequest['url'])->toBe('https://api.elongopay.cd/v1/checkout/sessions/cs_test_abc')
        ->and($transport->lastRequest['method'])->toBe('GET');
});

it('leve une ApiException sur erreur', function () {
    $transport = new FakeTransport(422, json_encode(['message' => 'success_url manquant']));
    $pay = new Client('sk_test_key', 'https://api.elongopay.cd', $transport);

    expect(fn () => $pay->checkout->create([]))
        ->toThrow(ApiException::class, 'success_url manquant');
});
