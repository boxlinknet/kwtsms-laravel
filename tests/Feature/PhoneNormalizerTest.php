<?php

namespace KwtSMS\Laravel\Tests\Feature;

use KwtSMS\Laravel\Services\PhoneNormalizer;
use KwtSMS\Laravel\Tests\TestCase;

class PhoneNormalizerTest extends TestCase
{
    private PhoneNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = app(PhoneNormalizer::class);
    }

    public function test_normalizes_plus_prefix(): void
    {
        $this->assertSame('96598765432', $this->normalizer->normalize('+96598765432'));
    }

    public function test_normalizes_double_zero_prefix(): void
    {
        $this->assertSame('96598765432', $this->normalizer->normalize('0096598765432'));
    }

    public function test_normalizes_spaces(): void
    {
        $this->assertSame('96598765432', $this->normalizer->normalize('965 9876 5432'));
    }

    public function test_normalizes_dashes(): void
    {
        $this->assertSame('96598765432', $this->normalizer->normalize('965-9876-5432'));
    }

    public function test_normalizes_arabic_digits(): void
    {
        // Arabic-Indic digits for 96598765432
        $this->assertSame('96598765432', $this->normalizer->normalize('٩٦٥٩٨٧٦٥٤٣٢'));
    }

    public function test_valid_kuwait_number_in_coverage(): void
    {
        // 96598765432 starts with '965', which is in coverage
        $result = $this->normalizer->normalizeMany(['96598765432'], ['965']);
        $this->assertContains('96598765432', $result['valid']);
        $this->assertEmpty($result['invalid']);
    }

    public function test_number_out_of_coverage(): void
    {
        // 97112345678 starts with '971', not in ['965'] coverage
        $result = $this->normalizer->normalizeMany(['97112345678'], ['965']);
        $this->assertEmpty($result['valid']);
        $this->assertNotEmpty($result['invalid']);
    }
}
