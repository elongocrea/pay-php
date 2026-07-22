<?php

declare(strict_types=1);

namespace Elongo\Pay;

use Elongo\Pay\Exception\ElongoPayException;

/**
 * Verification des webhooks Elongo Pay.
 *
 * En-tete recu :
 *   Elongo-Signature: t=<timestamp>,v1=<hmac_sha256(secret, "<t>.<payload>")>
 *
 * Recompose « <t>.<payload> », calcule le HMAC avec le secret du marchand,
 * compare en temps constant, et rejette au-dela d'une tolerance (anti-rejeu).
 * Doit rester identique au serveur (App\Services\WebhookSignature).
 */
final class Webhook
{
    public const TOLERANCE = 300;

    /**
     * Verifie la signature et renvoie l'evenement decode.
     *
     * @return array<string, mixed>
     *
     * @throws ElongoPayException si la signature est invalide ou trop ancienne
     */
    public static function constructEvent(string $payload, string $header, string $secret, int $tolerance = self::TOLERANCE): array
    {
        if (! self::verify($payload, $header, $secret, $tolerance)) {
            throw new ElongoPayException('Signature de webhook invalide.');
        }

        /** @var array<string, mixed> $event */
        $event = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);

        return $event;
    }

    public static function verify(string $payload, string $header, string $secret, int $tolerance = self::TOLERANCE, ?int $now = null): bool
    {
        $parts = self::parse($header);
        if (! isset($parts['t'], $parts['v1'])) {
            return false;
        }

        $timestamp = (int) $parts['t'];
        $now ??= time();

        if (abs($now - $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return hash_equals($expected, $parts['v1']);
    }

    /**
     * @return array<string, string>
     */
    private static function parse(string $header): array
    {
        $out = [];
        foreach (explode(',', $header) as $segment) {
            $pair = array_pad(explode('=', trim($segment), 2), 2, null);
            if ($pair[0] !== null && $pair[1] !== null) {
                $out[$pair[0]] = $pair[1];
            }
        }

        return $out;
    }
}
