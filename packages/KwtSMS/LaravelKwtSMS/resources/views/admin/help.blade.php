@extends('kwtsms::admin.layout')

@section('title', __('kwtsms::kwtsms.help'))

@section('content')
<h1 class="kwt-page-title">{{ __('kwtsms::kwtsms.help') }}</h1>

<div class="kwt-card">
    <div class="kwt-card-title">Quick Start Guide</div>
    <ol style="padding-left:20px;line-height:2;font-size:14px;">
        <li>Add your API credentials to <code>.env</code>: <code>KWTSMS_USERNAME</code> and <code>KWTSMS_PASSWORD</code></li>
        <li>Set your sender ID: <code>KWTSMS_SENDER=YourName</code></li>
        <li>Go to <a href="{{ route('kwtsms.settings') }}">Settings</a> and click "Test Connection"</li>
        <li>Create SMS <a href="{{ route('kwtsms.templates.index') }}">Templates</a> for your events</li>
        <li>Enable events in <a href="{{ route('kwtsms.integrations') }}">Integrations</a></li>
        <li>Use the notification channel in your Laravel notifiables or dispatch jobs directly</li>
    </ol>
</div>

<div class="kwt-card">
    <div class="kwt-card-title">Sending SMS in Code</div>

    <p style="font-size:13px;color:#6B7280;margin-bottom:12px;">Using the notification channel:</p>
    <pre class="kwt-code">// In your Notification class:
public function via($notifiable): array
{
    return ['kwtsms'];
}

public function toKwtSms($notifiable): KwtSmsMessage
{
    return (new KwtSmsMessage)
        ->content('Your message text here')
        ->sender('MYSENDER');
}</pre>

    <p style="font-size:13px;color:#6B7280;margin:16px 0 12px;">Using SmsSender directly:</p>
    <pre class="kwt-code">use KwtSMS\Laravel\Services\SmsSender;

$sender = app(SmsSender::class);
$sender->send('96500000000', 'Hello from kwtSMS!');</pre>
</div>

<div class="kwt-card">
    <div class="kwt-card-title">Template Variables</div>
    <p style="font-size:13px;color:#6B7280;margin-bottom:12px;">
        Use <code>{variable_name}</code> syntax in template bodies. Pass data when sending:
    </p>
    <pre class="kwt-code">// Template body: "Dear {name}, your order {order_id} is confirmed."

$sender->sendForEvent('order_placed', '96500000000', [
    'name' => 'Ahmed',
    'order_id' => '#1234',
]);</pre>
</div>

<div class="kwt-card">
    <div class="kwt-card-title">Environment Variables</div>
    <div class="kwt-table-wrap">
        <table class="kwt-table">
            <thead>
                <tr>
                    <th>Variable</th>
                    <th>Default</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>KWTSMS_USERNAME</code></td>
                    <td>-</td>
                    <td>Your kwtSMS API username</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_PASSWORD</code></td>
                    <td>-</td>
                    <td>Your kwtSMS API password</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_SENDER</code></td>
                    <td>KWT-SMS</td>
                    <td>Default sender ID</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_ENABLED</code></td>
                    <td>true</td>
                    <td>Enable/disable all SMS sending</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_TEST_MODE</code></td>
                    <td>false</td>
                    <td>Test mode: messages queued but not delivered</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_API_URL</code></td>
                    <td>https://www.kwtsms.com/API/</td>
                    <td>API base URL</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_TIMEOUT</code></td>
                    <td>30</td>
                    <td>HTTP timeout in seconds</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_ADMIN_PREFIX</code></td>
                    <td>kwtsms</td>
                    <td>Admin panel URL prefix</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_OTP_EXPIRY</code></td>
                    <td>5</td>
                    <td>OTP expiry in minutes</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_OTP_COOLDOWN</code></td>
                    <td>3</td>
                    <td>OTP resend cooldown in minutes</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_LOG_RETENTION</code></td>
                    <td>90</td>
                    <td>Log retention period in days</td>
                </tr>
                <tr>
                    <td><code>KWTSMS_DEBUG</code></td>
                    <td>false</td>
                    <td>Enable debug logging</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="kwt-card">
    <div class="kwt-card-title">API Error Codes</div>
    <div class="kwt-table-wrap">
        <table class="kwt-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Meaning</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>1</code></td>
                    <td>Message sent successfully</td>
                    <td>No action needed</td>
                </tr>
                <tr>
                    <td><code>-1</code></td>
                    <td>Invalid credentials</td>
                    <td>Check KWTSMS_USERNAME and KWTSMS_PASSWORD</td>
                </tr>
                <tr>
                    <td><code>-2</code></td>
                    <td>Insufficient balance</td>
                    <td>Top up your kwtSMS account</td>
                </tr>
                <tr>
                    <td><code>-3</code></td>
                    <td>Invalid sender ID</td>
                    <td>Use a registered sender ID</td>
                </tr>
                <tr>
                    <td><code>-4</code></td>
                    <td>Invalid phone number</td>
                    <td>Check phone number format (international format)</td>
                </tr>
                <tr>
                    <td><code>-5</code></td>
                    <td>Empty message</td>
                    <td>Message body cannot be empty</td>
                </tr>
                <tr>
                    <td><code>-6</code></td>
                    <td>Message too long</td>
                    <td>Reduce message length</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="kwt-card">
    <div class="kwt-card-title">Support</div>
    <div style="font-size:14px;line-height:1.8;">
        <p>For kwtSMS API support, visit the official kwtSMS website.</p>
        <p>For package issues, check the package documentation or submit a bug report.</p>
        <p>Current package version: <code>kwtsms/laravel-kwtsms</code></p>
    </div>
</div>
@endsection
