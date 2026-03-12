@extends('kwtsms::admin.layout')

@section('title', __('kwtsms::kwtsms.admin_alerts'))

@section('content')
<h1 class="kwt-page-title">{{ __('kwtsms::kwtsms.admin_alerts') }}</h1>

<form method="POST" action="{{ route('kwtsms.alerts.update') }}">
    @csrf

    <div class="kwt-card">
        <div class="kwt-card-title">Alert Notifications</div>
        <p style="font-size:13px;color:#6B7280;margin-bottom:16px;">
            These alerts are sent to the admin phone number configured in Settings.
            @if(!empty($adminPhone))
                Currently sending to: <strong>{{ $adminPhone }}</strong>
            @else
                <strong style="color:#EF4444;">No admin phone number configured.</strong>
                <a href="{{ route('kwtsms.settings') }}">Configure in Settings</a>
            @endif
        </p>

        @php
            $alertOptions = [
                'low_balance' => [
                    'label' => 'Low Balance Alert',
                    'desc' => 'Alert when balance drops below the configured threshold.',
                ],
                'send_failure' => [
                    'label' => 'Send Failure Alert',
                    'desc' => 'Alert when an SMS fails to send.',
                ],
                'daily_summary' => [
                    'label' => 'Daily Summary',
                    'desc' => 'Daily report of SMS sent, failed, and current balance.',
                ],
                'api_error' => [
                    'label' => 'API Error Alert',
                    'desc' => 'Alert on API connection errors or unexpected responses.',
                ],
                'otp_flood' => [
                    'label' => 'OTP Flood Alert',
                    'desc' => 'Alert when OTP rate limits are triggered.',
                ],
            ];
        @endphp

        @foreach($alertOptions as $key => $option)
            <div class="kwt-toggle-wrap">
                <div class="kwt-toggle-info">
                    <div class="kwt-toggle-label">{{ $option['label'] }}</div>
                    <div class="kwt-toggle-desc">{{ $option['desc'] }}</div>
                </div>
                <label class="kwt-toggle">
                    <input
                        type="checkbox"
                        name="alerts[{{ $key }}]"
                        value="1"
                        {{ !empty($alerts[$key]) ? 'checked' : '' }}
                    >
                    <span class="kwt-toggle-slider"></span>
                </label>
            </div>
        @endforeach
    </div>

    <button type="submit" class="kwt-btn kwt-btn-primary">{{ __('kwtsms::kwtsms.save') }}</button>
    <a href="{{ route('kwtsms.settings') }}" class="kwt-btn kwt-btn-secondary" style="margin-left:8px;">Configure Phone</a>
</form>
@endsection
