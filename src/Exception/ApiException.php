<?php

declare(strict_types=1);

namespace Elongo\Pay\Exception;

/**
 * Erreur renvoyee par l'API (statut HTTP >= 400).
 */
class ApiException extends ElongoPayException
{
    /**
     * @param  array<string, mixed>  $body
     */
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly array $body = [],
    ) {
        parent::__construct($message);
    }
}
