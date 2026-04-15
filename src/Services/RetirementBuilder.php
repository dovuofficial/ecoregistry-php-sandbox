<?php

declare(strict_types=1);

namespace Ecoregistry\Services;

/**
 * Fluent builder for constructing a credit retirement request.
 *
 * Usage:
 *   $eco->exchange()->auth()->retirement()
 *       ->serial('CDC_18_5_11_321_14_XX_XA_CO_1_1_2021')
 *       ->quantity(10)
 *       ->voluntaryCompensation()
 *       ->endUser('DOVU Market', countryId: 230, documentTypeId: 1, documentNumber: '267167674')
 *       ->execute();
 */
final class RetirementBuilder
{
    private ?string $serial = null;
    private ?int $quantity = null;
    private ?int $reasonId = null;
    private string $observation = '';
    private bool $isPublic = true;
    private ?array $endUser = null;
    private ?array $passiveSubject = null;
    private bool $inKg = false;

    /** @var callable(array, string, string): array */
    private $executor;

    public function __construct(callable $executor)
    {
        $this->executor = $executor;
    }

    /** Set the credit serial to retire. */
    public function serial(string $serial): self
    {
        $this->serial = $serial;
        return $this;
    }

    /** Set the quantity to retire. */
    public function quantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    /** Set retirement reason by ID. */
    public function reason(int $reasonId): self
    {
        $this->reasonId = $reasonId;
        return $this;
    }

    /** Shorthand: Voluntary compensation (reason ID 16). */
    public function voluntaryCompensation(): self
    {
        return $this->reason(16);
    }

    /** Shorthand: CORSIA (reason ID 1). */
    public function corsia(): self
    {
        return $this->reason(1);
    }

    /** Shorthand: Colombian Carbon Tax (reason ID 2). Requires passiveSubject. */
    public function colombianCarbonTax(): self
    {
        return $this->reason(2);
    }

    /** Set an observation/note for the retirement. */
    public function observation(string $observation): self
    {
        $this->observation = $observation;
        return $this;
    }

    /** Set whether the retirement is publicly visible. Default: true. */
    public function public(bool $isPublic = true): self
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    /** Retire in kilograms instead of tonnes. */
    public function inKg(): self
    {
        $this->inKg = true;
        return $this;
    }

    /**
     * Set the end user (beneficiary) of the retirement.
     *
     * @param string $name Company or person name
     * @param int $countryId Country ID (e.g. 230 for UK, 47 for Colombia)
     * @param int $documentTypeId Document type (1=ID, 2=NIT/Company ID, 4=Enterprise ID)
     * @param string $documentNumber Document number
     * @param int|null $industryId Industry ID (optional)
     * @param int|null $verificationDigit Verification digit for NIT (optional)
     */
    public function endUser(
        string $name,
        int $countryId,
        int $documentTypeId = 1,
        string $documentNumber = '',
        ?int $industryId = null,
        ?int $verificationDigit = null,
    ): self {
        $this->endUser = array_filter([
            'name' => $name,
            'countryId' => $countryId,
            'documentNumberTypeId' => $documentTypeId,
            'documentNumber' => $documentNumber,
            'verificationDigit' => $verificationDigit,
            'industry_id' => $industryId,
        ], fn($v) => $v !== null);

        return $this;
    }

    /**
     * Set the passive subject (required for Colombian Carbon Tax).
     */
    public function passiveSubject(
        string $name,
        int $countryId,
        int $documentTypeId = 1,
        string $documentNumber = '',
        ?int $verificationDigit = null,
    ): self {
        $this->passiveSubject = array_filter([
            'name' => $name,
            'countryId' => $countryId,
            'documentNumberTypeId' => $documentTypeId,
            'documentNumber' => $documentNumber,
            'verificationDigit' => $verificationDigit,
        ], fn($v) => $v !== null);

        return $this;
    }

    /**
     * Execute the retirement.
     *
     * @return array Response with 'data' (serial, quantity, date), 'urlPDF', 'transactionId'
     * @throws \RuntimeException if required fields are missing
     */
    public function execute(string $lang = 'en'): array
    {
        if (!$this->serial) {
            throw new \RuntimeException('Retirement requires a serial. Call ->serial() first.');
        }
        if (!$this->quantity || $this->quantity < 1) {
            throw new \RuntimeException('Retirement requires a positive quantity. Call ->quantity() first.');
        }
        if (!$this->reasonId) {
            throw new \RuntimeException('Retirement requires a reason. Call ->voluntaryCompensation() or ->reason() first.');
        }
        if (!$this->endUser) {
            throw new \RuntimeException('Retirement requires an end user. Call ->endUser() first.');
        }

        $body = [
            'reasonUsingCarbonOffsetsId' => $this->reasonId,
            'serialElegibleId' => $this->reasonId,
            'quantity' => $this->quantity,
            'serial' => $this->serial,
            'observation' => $this->observation,
            'isPublic' => $this->isPublic,
            'endUser' => $this->endUser,
        ];

        if ($this->passiveSubject) {
            $body['passiveSubject'] = $this->passiveSubject;
        }

        $path = $this->inKg
            ? '/api-exchange-v2/v2/retirement-kg'
            : '/api-exchange-v2/v2/retirement';

        return ($this->executor)($body, $lang, $path);
    }
}
