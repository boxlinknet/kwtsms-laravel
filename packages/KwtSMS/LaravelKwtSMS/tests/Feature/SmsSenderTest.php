<?php

namespace KwtSMS\Laravel\Tests\Feature;

use KwtSMS\Laravel\Models\KwtSmsSetting;
use KwtSMS\Laravel\Services\SmsSender;
use KwtSMS\Laravel\Tests\TestCase;

class SmsSenderTest extends TestCase
{
    private function kwtSmsCredentialsConfigured(): bool
    {
        return ! empty(config('kwtsms.username')) && ! empty(config('kwtsms.password'));
    }

    public function test_send_single_number(): void
    {
        if (! $this->kwtSmsCredentialsConfigured()) {
            $this->markTestSkipped('kwtSMS credentials not configured');
        }

        $sender = app(SmsSender::class);
        $result = $sender->send('96598765432', 'SmsSenderTest: single number. Test mode.', null, ['event_type' => 'test']);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message_id', $result);
    }

    public function test_send_normalizes_phone_format(): void
    {
        if (! $this->kwtSmsCredentialsConfigured()) {
            $this->markTestSkipped('kwtSMS credentials not configured');
        }

        $sender = app(SmsSender::class);
        // Pass with + prefix - should normalize and send
        $result = $sender->send('+96598765432', 'SmsSenderTest: normalized phone. Test mode.', null, ['event_type' => 'test']);

        $this->assertTrue($result['success']);
    }

    public function test_send_blocked_when_disabled(): void
    {
        config(['kwtsms.enabled' => false]);

        $sender = app(SmsSender::class);
        $result = $sender->send('96598765432', 'This should not send', null, ['event_type' => 'test']);

        $this->assertFalse($result['success']);
        $this->assertSame('disabled', $result['reason']);

        config(['kwtsms.enabled' => true]);
    }

    public function test_send_blocked_when_zero_balance(): void
    {
        // Set balance to 0 in the settings cache
        KwtSmsSetting::set('balance_available', 0);

        $sender = app(SmsSender::class);
        $result = $sender->send('96598765432', 'This should not send', null, ['event_type' => 'test']);

        $this->assertFalse($result['success']);
        $this->assertSame('no_balance', $result['reason']);

        // Clean up
        KwtSmsSetting::set('balance_available', null);
    }

    public function test_send_skips_uncovered_country(): void
    {
        // Set coverage to only Kuwait (965), then try to send to UAE (971)
        KwtSmsSetting::set('coverage', ['965']);

        $sender = app(SmsSender::class);
        $result = $sender->send('97112345678', 'This should not send - UAE not covered', null, ['event_type' => 'test']);

        $this->assertFalse($result['success']);
        $this->assertSame('no_valid_recipients', $result['reason']);

        // Clean up
        KwtSmsSetting::set('coverage', []);
    }

    public function test_arabic_message_sends_correctly(): void
    {
        if (! $this->kwtSmsCredentialsConfigured()) {
            $this->markTestSkipped('kwtSMS credentials not configured');
        }

        $sender = app(SmsSender::class);
        $result = $sender->send(
            '96598765432',
            'رسالة اختبار: هذه رسالة عربية من حزمة Laravel.',
            null,
            ['event_type' => 'test']
        );

        $this->assertTrue($result['success']);
    }
}
