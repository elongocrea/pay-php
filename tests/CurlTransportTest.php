<?php

declare(strict_types=1);

use Elongo\Pay\CurlTransport;
use Elongo\Pay\Exception\NetworkException;

/**
 * Regression: curl_close() est deprecie depuis PHP 8.5 (sans effet depuis 8.0).
 * Chaque requete du SDK emettait un E_DEPRECATED dans les journaux des
 * applications.
 */
it('n emet aucune deprecation PHP lors d un appel', function () {
    $deprecations = [];
    set_error_handler(function (int $errno, string $message) use (&$deprecations): bool {
        $deprecations[] = $message;

        return true;
    }, E_DEPRECATED);

    try {
        (new CurlTransport(2))->send('GET', 'http://127.0.0.1:1/', [], null);
    } catch (NetworkException) {
        // Port ferme : seule l'absence de deprecation nous interesse.
    } finally {
        restore_error_handler();
    }

    expect($deprecations)->toBe([]);
});
