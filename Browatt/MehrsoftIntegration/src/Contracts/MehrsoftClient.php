<?php

namespace Browatt\MehrsoftIntegration\Contracts;

interface MehrsoftClient
{
    /**
     * Get MehrSoft product status by product serial.
     *
     * @return array<string, mixed>
     */
    public function getProductStatusBySerial(string $serial): array;

    /**
     * Get warranty period in months for a warranty type and product code.
     */
    public function getWarrantyMonths(string $warrantyType, string $goodFullCode): ?int;

    /**
     * Get MehrSoft warranty settings.
     *
     * @return array<int|string, mixed>
     */
    public function getWarrantySettings(): array;

    /**
     * Save an after-sales document.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function saveAfterSales(array $payload): array;

    /**
     * Close the current MehrSoft session when one has been opened.
     */
    public function logout(): void;
}
