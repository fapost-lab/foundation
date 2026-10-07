<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use InvalidArgumentException;

/**
 * Who asks to enter which tenant, as a {@see \Fapost\Foundation\Tenancy\Contracts\SupportAccessInterface} receives it.
 */
final readonly class SupportAccessRequest
{
    /**
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     * @param  string  $operatorRef  the caller's stable reference to the operator, such as "operator:<id>"
     * @param  string  $operatorName  shown to the tenant in its support access log and banner
     * @param  string  $operatorEmail  shown to the tenant in its support access log
     *
     * @throws InvalidArgumentException when a field is empty
     */
    public function __construct(
        public string $tenantId,
        public string $operatorRef,
        public string $operatorName,
        public string $operatorEmail,
    ) {
        foreach (['tenantId' => $tenantId, 'operatorRef' => $operatorRef, 'operatorName' => $operatorName, 'operatorEmail' => $operatorEmail] as $field => $value) {
            if ('' === trim($value)) {
                throw new InvalidArgumentException(sprintf('Support access needs a non-empty %s.', $field));
            }
        }
    }
}
