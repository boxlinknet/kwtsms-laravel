<?php

namespace KwtSMS\Laravel\Services;

use KwtSMS\PhoneUtils;

/**
 * Phone number normalization and coverage verification service.
 *
 * Wraps KwtSMS\PhoneUtils and adds coverage prefix filtering
 * for the pre-send check in SmsSender.
 */
class PhoneNormalizer
{
    /**
     * Normalize a phone number to kwtSMS-accepted format (digits only, international format).
     *
     * Converts Arabic-Indic and Extended Arabic-Indic digits to Latin,
     * strips all non-digit characters, and removes leading zeros.
     *
     * Examples:
     *   normalize('+96598765432')   -> '96598765432'
     *   normalize('0096598765432')  -> '96598765432'
     *   normalize('965 9876 5432')  -> '96598765432'
     *   normalize('٩٦٥٩٨٧٦٥٤٣٢')   -> '96598765432'
     */
    public function normalize(string $phone): string
    {
        return PhoneUtils::normalize_phone($phone);
    }

    /**
     * Verify that a normalized phone number is valid for sending.
     *
     * Checks length (7-15 digits) and optionally verifies the number
     * starts with one of the provided coverage prefixes.
     *
     * @param  string  $normalizedPhone  Already-normalized phone (digits only).
     * @param  string[]  $coverage  Array of valid country prefixes (e.g. ['965', '966']).
     *                              Empty array skips coverage check.
     */
    public function verify(string $normalizedPhone, array $coverage = []): bool
    {
        [$valid] = PhoneUtils::validate_phone_input($normalizedPhone);

        if (! $valid) {
            return false;
        }

        if (empty($coverage)) {
            return true;
        }

        return $this->matchesCoverage($normalizedPhone, $coverage);
    }

    /**
     * Normalize and verify multiple phone numbers.
     *
     * @param  string[]  $phones  Raw phone numbers.
     * @param  string[]  $coverage  Coverage prefixes for filtering.
     * @return array{valid: string[], invalid: string[]}
     */
    public function normalizeMany(array $phones, array $coverage = []): array
    {
        $valid = [];
        $invalid = [];

        foreach ($phones as $phone) {
            $normalized = $this->normalize($phone);

            if ($this->verify($normalized, $coverage)) {
                $valid[] = $normalized;
            } else {
                $invalid[] = $phone;
            }
        }

        return ['valid' => $valid, 'invalid' => $invalid];
    }

    /**
     * Check if a normalized phone number starts with any of the given prefixes.
     *
     * Empty-string prefixes are skipped to prevent every number matching.
     *
     * @param  string[]  $coverage
     */
    private function matchesCoverage(string $normalizedPhone, array $coverage): bool
    {
        foreach ($coverage as $prefix) {
            $prefix = (string) $prefix;

            if ($prefix === '') {
                continue;
            }

            if (str_starts_with($normalizedPhone, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
