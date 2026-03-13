<?php

namespace KwtSMS\Laravel\Services;

use KwtSMS\PhoneUtils;

/**
 * Phone number normalization, validation, and coverage verification service.
 *
 * Wraps KwtSMS\PhoneUtils for normalization and adds country-specific format
 * validation (local number length + mobile prefix checks) ported from the
 * kwtSMS Shopify integration phone.ts validation rules table.
 */
class PhoneNormalizer
{
    /**
     * Country-specific phone validation rules.
     *
     * Keys are E.164 country codes (digits only, no +).
     * localLengths: valid digit count(s) AFTER the country code.
     * mobileStartDigits: valid first character(s) of the local number.
     *   If absent, any starting digit is accepted (length check only).
     *
     * Countries not listed pass through with generic E.164 validation (7-15 digits).
     *
     * Sources: ITU-T E.164, Wikipedia national numbering plans, HowToCallAbroad.com
     *
     * @var array<string, array{localLengths: int[], mobileStartDigits?: string[]}>
     */
    private const PHONE_RULES = [
        // === GCC ===
        '965' => ['localLengths' => [8], 'mobileStartDigits' => ['4', '5', '6', '9']],        // Kuwait: 4x=Virgin/STC, 5x=STC/Zain, 6x=Ooredoo, 9x=Zain
        '966' => ['localLengths' => [9], 'mobileStartDigits' => ['5']],                        // Saudi Arabia: 50-59
        '971' => ['localLengths' => [9], 'mobileStartDigits' => ['5']],                        // UAE: 50,52-56,58
        '973' => ['localLengths' => [8], 'mobileStartDigits' => ['3', '6']],                   // Bahrain: 3x,6x
        '974' => ['localLengths' => [8], 'mobileStartDigits' => ['3', '5', '6', '7']],         // Qatar: 33,55,66,77
        '968' => ['localLengths' => [8], 'mobileStartDigits' => ['7', '9']],                   // Oman: 7x,9x
        // === Levant ===
        '962' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                        // Jordan: 75,77,78,79
        '961' => ['localLengths' => [7, 8], 'mobileStartDigits' => ['3', '7', '8']],           // Lebanon: 3x (7-digit legacy), 7x/81 (8-digit)
        '970' => ['localLengths' => [9], 'mobileStartDigits' => ['5']],                        // Palestine: 56=Jawwal, 59=Ooredoo
        '964' => ['localLengths' => [10], 'mobileStartDigits' => ['7']],                       // Iraq: 75-79
        '963' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                        // Syria: 93-96,98,99
        // === Other Arab ===
        '967' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                        // Yemen: 70,71,73,77
        '20' => ['localLengths' => [10], 'mobileStartDigits' => ['1']],                       // Egypt: 10,11,12,15
        '218' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                        // Libya: 91-95
        '216' => ['localLengths' => [8], 'mobileStartDigits' => ['2', '4', '5', '9']],         // Tunisia: 2x,4x=MVNO,5x,9x
        '212' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],                   // Morocco: 6x,7x
        '213' => ['localLengths' => [9], 'mobileStartDigits' => ['5', '6', '7']],              // Algeria: 5x,6x,7x
        '249' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                        // Sudan: 90,91,92,96,99
        // === Non-Arab Middle East ===
        '98' => ['localLengths' => [10], 'mobileStartDigits' => ['9']],                       // Iran: 9x
        '90' => ['localLengths' => [10], 'mobileStartDigits' => ['5']],                       // Turkey: 5x
        '972' => ['localLengths' => [9], 'mobileStartDigits' => ['5']],                        // Israel: 50,52-55,58
        // === South Asia ===
        '91' => ['localLengths' => [10], 'mobileStartDigits' => ['6', '7', '8', '9']],        // India: 6-9x
        '92' => ['localLengths' => [10], 'mobileStartDigits' => ['3']],                       // Pakistan: 3x
        '880' => ['localLengths' => [10], 'mobileStartDigits' => ['1']],                       // Bangladesh: 1x
        '94' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                        // Sri Lanka: 70-78
        '960' => ['localLengths' => [7], 'mobileStartDigits' => ['7', '9']],                   // Maldives: 7x,9x
        // === East Asia ===
        '86' => ['localLengths' => [11], 'mobileStartDigits' => ['1']],                       // China: 13-19x
        '81' => ['localLengths' => [10], 'mobileStartDigits' => ['7', '8', '9']],             // Japan: 70,80,90
        '82' => ['localLengths' => [10], 'mobileStartDigits' => ['1']],                       // South Korea: 010
        '886' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                        // Taiwan: 9x
        // === Southeast Asia ===
        '65' => ['localLengths' => [8], 'mobileStartDigits' => ['8', '9']],                   // Singapore: 8x,9x
        '60' => ['localLengths' => [9, 10], 'mobileStartDigits' => ['1']],                    // Malaysia: 1x (9 or 10 digits)
        '62' => ['localLengths' => [9, 10, 11, 12], 'mobileStartDigits' => ['8']],            // Indonesia: 8x (variable length)
        '63' => ['localLengths' => [10], 'mobileStartDigits' => ['9']],                       // Philippines: 9x
        '66' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '8', '9']],              // Thailand: 6x,8x,9x
        '84' => ['localLengths' => [9], 'mobileStartDigits' => ['3', '5', '7', '8', '9']],    // Vietnam: 3x,5x,7x,8x,9x
        '95' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                        // Myanmar: 9x
        '855' => ['localLengths' => [8, 9], 'mobileStartDigits' => ['1', '6', '7', '8', '9']], // Cambodia: mixed lengths
        '976' => ['localLengths' => [8], 'mobileStartDigits' => ['6', '8', '9']],              // Mongolia: 6x,8x,9x
        // === Europe ===
        '44' => ['localLengths' => [10], 'mobileStartDigits' => ['7']],                       // UK: 7x
        '33' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],                   // France: 6x,7x
        '49' => ['localLengths' => [10, 11], 'mobileStartDigits' => ['1']],                   // Germany: 15x,16x,17x
        '39' => ['localLengths' => [10], 'mobileStartDigits' => ['3']],                       // Italy: 3x
        '34' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],                   // Spain: 6x,7x
        '31' => ['localLengths' => [9], 'mobileStartDigits' => ['6']],                        // Netherlands: 6x
        '32' => ['localLengths' => [9]],                                                       // Belgium: length only (complex prefixes)
        '41' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                        // Switzerland: 74-79
        '43' => ['localLengths' => [10], 'mobileStartDigits' => ['6']],                       // Austria: 65x-69x
        '47' => ['localLengths' => [8], 'mobileStartDigits' => ['4', '9']],                   // Norway: 4x,9x
        '48' => ['localLengths' => [9]],                                                       // Poland: length only (complex prefixes)
        '30' => ['localLengths' => [10], 'mobileStartDigits' => ['6']],                       // Greece: 69x
        '420' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],                   // Czech Republic: 6x,7x
        '46' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                        // Sweden: 7x
        '45' => ['localLengths' => [8]],                                                       // Denmark: length only (complex prefixes)
        '40' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                        // Romania: 7x
        '36' => ['localLengths' => [9]],                                                       // Hungary: length only (complex prefixes)
        '380' => ['localLengths' => [9]],                                                       // Ukraine: length only (complex prefixes)
        // === Americas ===
        '1' => ['localLengths' => [10]],                                                      // USA/Canada: no mobile-specific prefix
        '52' => ['localLengths' => [10]],                                                      // Mexico: no mobile-specific prefix since 2019
        '55' => ['localLengths' => [11]],                                                      // Brazil: area code + 9 + subscriber
        '57' => ['localLengths' => [10], 'mobileStartDigits' => ['3']],                       // Colombia: 3x
        '54' => ['localLengths' => [10], 'mobileStartDigits' => ['9']],                       // Argentina: 9 + area + subscriber
        '56' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                        // Chile: 9x
        '58' => ['localLengths' => [10], 'mobileStartDigits' => ['4']],                       // Venezuela: 4x
        '51' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                        // Peru: 9x
        '593' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                        // Ecuador: 9x
        '53' => ['localLengths' => [8], 'mobileStartDigits' => ['5', '6']],                   // Cuba: 5x,6x
        // === Africa ===
        '27' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7', '8']],              // South Africa: 6x,7x,8x
        '234' => ['localLengths' => [10], 'mobileStartDigits' => ['7', '8', '9']],             // Nigeria: 70,71,80,81,90,91
        '254' => ['localLengths' => [9], 'mobileStartDigits' => ['1', '7']],                   // Kenya: 1x,7x
        '233' => ['localLengths' => [9], 'mobileStartDigits' => ['2', '5']],                   // Ghana: 2x,5x
        '251' => ['localLengths' => [9], 'mobileStartDigits' => ['7', '9']],                   // Ethiopia: 7x,9x
        '255' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],                   // Tanzania: 6x,7x
        '256' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                        // Uganda: 7x
        '237' => ['localLengths' => [9], 'mobileStartDigits' => ['6']],                        // Cameroon: 6x
        '225' => ['localLengths' => [10]],                                                      // Ivory Coast: length only (01,05,07 prefixes)
        '221' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                        // Senegal: 7x
        '252' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],                   // Somalia: 6x,7x
        '250' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                        // Rwanda: 7x
        // === Oceania ===
        '61' => ['localLengths' => [9], 'mobileStartDigits' => ['4']],                        // Australia: 4x
        '64' => ['localLengths' => [8, 9, 10], 'mobileStartDigits' => ['2']],                 // New Zealand: 21,22,27-29
    ];

    /**
     * @var array<string, string>
     */
    private const COUNTRY_NAMES = [
        '965' => 'Kuwait', '966' => 'Saudi Arabia', '971' => 'UAE', '973' => 'Bahrain',
        '974' => 'Qatar', '968' => 'Oman', '962' => 'Jordan', '961' => 'Lebanon',
        '970' => 'Palestine', '964' => 'Iraq', '963' => 'Syria', '967' => 'Yemen',
        '98' => 'Iran', '90' => 'Turkey', '972' => 'Israel',
        '20' => 'Egypt', '218' => 'Libya', '216' => 'Tunisia', '212' => 'Morocco',
        '213' => 'Algeria', '249' => 'Sudan',
        '91' => 'India', '92' => 'Pakistan', '880' => 'Bangladesh', '94' => 'Sri Lanka',
        '960' => 'Maldives', '86' => 'China', '81' => 'Japan', '82' => 'South Korea',
        '886' => 'Taiwan', '65' => 'Singapore', '60' => 'Malaysia', '62' => 'Indonesia',
        '63' => 'Philippines', '66' => 'Thailand', '84' => 'Vietnam', '95' => 'Myanmar',
        '855' => 'Cambodia', '976' => 'Mongolia',
        '44' => 'UK', '33' => 'France', '49' => 'Germany', '39' => 'Italy',
        '34' => 'Spain', '31' => 'Netherlands', '32' => 'Belgium', '41' => 'Switzerland',
        '43' => 'Austria', '47' => 'Norway', '48' => 'Poland', '30' => 'Greece',
        '420' => 'Czech Republic', '46' => 'Sweden', '45' => 'Denmark', '40' => 'Romania',
        '36' => 'Hungary', '380' => 'Ukraine',
        '1' => 'USA/Canada', '52' => 'Mexico', '55' => 'Brazil', '57' => 'Colombia',
        '54' => 'Argentina', '56' => 'Chile', '58' => 'Venezuela', '51' => 'Peru',
        '593' => 'Ecuador', '53' => 'Cuba',
        '27' => 'South Africa', '234' => 'Nigeria', '254' => 'Kenya', '233' => 'Ghana',
        '251' => 'Ethiopia', '255' => 'Tanzania', '256' => 'Uganda', '237' => 'Cameroon',
        '225' => 'Ivory Coast', '221' => 'Senegal', '252' => 'Somalia', '250' => 'Rwanda',
        '61' => 'Australia', '64' => 'New Zealand',
    ];

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
     * Find the country code prefix from a normalized phone number.
     * Tries 3-digit codes first, then 2-digit, then 1-digit (longest match wins).
     */
    public function findCountryCode(string $normalized): ?string
    {
        if (strlen($normalized) >= 3 && isset(self::PHONE_RULES[substr($normalized, 0, 3)])) {
            return substr($normalized, 0, 3);
        }

        if (strlen($normalized) >= 2 && isset(self::PHONE_RULES[substr($normalized, 0, 2)])) {
            return substr($normalized, 0, 2);
        }

        if (strlen($normalized) >= 1 && isset(self::PHONE_RULES[substr($normalized, 0, 1)])) {
            return substr($normalized, 0, 1);
        }

        return null;
    }

    /**
     * Validate a normalized phone number against country-specific format rules.
     * Checks local number length and mobile starting digits.
     * Numbers with no matching country rules pass through (generic E.164 validation only).
     *
     * @return array{valid: bool, error: ?string}
     */
    public function validatePhoneFormat(string $normalized): array
    {
        $cc = $this->findCountryCode($normalized);

        if ($cc === null) {
            return ['valid' => true, 'error' => null];
        }

        $rule = self::PHONE_RULES[$cc];
        $local = substr($normalized, strlen($cc));
        $country = self::COUNTRY_NAMES[$cc] ?? "+{$cc}";

        if (! in_array(strlen($local), $rule['localLengths'], true)) {
            $expected = implode(' or ', $rule['localLengths']);

            return [
                'valid' => false,
                'error' => "Invalid {$country} number: expected {$expected} digits after +{$cc}, got ".strlen($local),
            ];
        }

        if (! empty($rule['mobileStartDigits'])) {
            $hasValidPrefix = false;

            foreach ($rule['mobileStartDigits'] as $prefix) {
                if (str_starts_with($local, $prefix)) {
                    $hasValidPrefix = true;
                    break;
                }
            }

            if (! $hasValidPrefix) {
                $prefixes = implode(', ', $rule['mobileStartDigits']);

                return [
                    'valid' => false,
                    'error' => "Invalid {$country} mobile number: after +{$cc} must start with {$prefixes}",
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Verify that a normalized phone number is valid for sending.
     *
     * Checks E.164 length (7-15 digits), country-specific format rules
     * (local number length + mobile prefix), and optionally verifies the
     * number starts with one of the provided coverage prefixes.
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

        $formatCheck = $this->validatePhoneFormat($normalizedPhone);

        if (! $formatCheck['valid']) {
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
