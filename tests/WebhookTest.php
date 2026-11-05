<?php

declare(strict_types=1);

use Elongo\Pay\Exception\ElongoPayException;
use Elongo\Pay\Webhook;

function sign(string $payload, string $secret, int $ts): string
{
    return 't='.$ts.',v1='.hash_hmac('sha256', $ts.'.'.$payload, $secret);
}

it('verifie une signature valide et decode l evenement', function () {
    $payload = '{"type":"checkout.session.completed","data":{"id":"cs_test_1"}}';
    $secret = 'whsec_test';
    $header = sign($payload, $secret, time());

    $event = Webhook::constructEvent($payload, $header, $secret);

    expect($event['type'])->toBe('checkout.session.completed')
        ->and($event['data']['id'])->toBe('cs_test_1');
});

it('rejette un corps altere', function () {
    $secret = 'whsec_test';
    $header = sign('{"a":1}', $secret, time());

    expect(Webhook::verify('{"a":2}', $header, $secret))->toBeFalse();
});

it('rejette une signature trop ancienne', function () {
    $secret = 'whsec_test';
    $payload = '{}';
    $header = sign($payload, $secret, time() - 10000);

    expect(Webhook::verify($payload, $header, $secret, tolerance: 300))->toBeFalse();
});

it('leve une exception sur signature invalide', function () {
    Webhook::constructEvent('{}', 't=1,v1=deadbeef', 'whsec_test');
})->throws(ElongoPayException::class);

/**
 * Regression: un secret vide (variable d'environnement absente) produisait un
 * HMAC que n'importe qui peut recalculer : un faux « paiement reussi »
 * passait la verification.
 */
it('refuse toute signature quand le secret est vide', function () {
    $payload = '{"type":"checkout.session.completed"}';
    $header = sign($payload, '', time());

    expect(Webhook::verify($payload, $header, ''))->toBeFalse()
        ->and(fn () => Webhook::constructEvent($payload, $header, ''))->toThrow(ElongoPayException::class);
});

it('verifie un evenement payment.succeeded de charge directe', function () {
    $payload = '{"id":"evt_1","type":"payment.succeeded","data":{"object":"payment","reference":"CMD-1","status":"success"}}';
    $event = Webhook::constructEvent($payload, sign($payload, 'whsec_test', time()), 'whsec_test');

    expect($event['type'])->toBe(Webhook::PAYMENT_SUCCEEDED)
        ->and($event['data']['reference'])->toBe('CMD-1');
});
