@extends('kwtsms::admin.layout')

@section('title', __('kwtsms::kwtsms.settings'))

@section('content')
<h1 class="kwt-page-title">{{ __('kwtsms::kwtsms.settings') }}</h1>

<div class="kwt-card">
    <div class="kwt-card-title">API Credentials</div>
    <p style="font-size:13px;color:#6B7280;margin-bottom:16px;">
        Credentials are read from your <code>.env</code> file. Set <code>KWTSMS_USERNAME</code> and <code>KWTSMS_PASSWORD</code> there.
    </p>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Username</span>
        <span class="kwt-detail-value">
            @if(!empty(config('kwtsms.username')))
                <span class="kwt-badge kwt-badge-green">Set</span>
                <code style="margin-left:8px;font-size:12px;">{{ config('kwtsms.username') }}</code>
            @else
                <span class="kwt-badge kwt-badge-red">Not Set</span>
            @endif
        </span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Password</span>
        <span class="kwt-detail-value">
            @if(!empty(config('kwtsms.password')))
                <span class="kwt-badge kwt-badge-green">Set</span>
                <code style="margin-left:8px;font-size:12px;">********</code>
            @else
                <span class="kwt-badge kwt-badge-red">Not Set</span>
            @endif
        </span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Sender ID</span>
        <span class="kwt-detail-value">{{ config('kwtsms.sender', 'Not Set') }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">API URL</span>
        <span class="kwt-detail-value">{{ config('kwtsms.api_base_url') }}</span>
    </div>
    <div style="margin-top:16px;">
        <button type="button" onclick="testConnection(this)" class="kwt-btn kwt-btn-blue" id="connect-btn">
            {{ __('kwtsms::kwtsms.test_connection') }}
        </button>
        <span id="connect-result" style="display:none;margin-left:12px;font-size:13px;font-weight:600;"></span>
    </div>
</div>

<div class="kwt-card">
    <div class="kwt-card-title">Gateway Flags</div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">SMS Enabled</span>
        <span class="kwt-detail-value">
            @if(config('kwtsms.enabled', true))
                <span class="kwt-badge kwt-badge-green">{{ __('kwtsms::kwtsms.enabled') }}</span>
            @else
                <span class="kwt-badge kwt-badge-red">{{ __('kwtsms::kwtsms.disabled') }}</span>
            @endif
            <span class="kwt-help-text" style="display:inline;margin-left:8px;">Set via <code>KWTSMS_ENABLED</code></span>
        </span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Test Mode</span>
        <span class="kwt-detail-value">
            @if(config('kwtsms.test_mode', false))
                <span class="kwt-badge kwt-badge-yellow">On</span>
            @else
                <span class="kwt-badge kwt-badge-green">Off</span>
            @endif
            <span class="kwt-help-text" style="display:inline;margin-left:8px;">Set via <code>KWTSMS_TEST_MODE</code></span>
        </span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Timeout</span>
        <span class="kwt-detail-value">{{ config('kwtsms.timeout', 30) }}s</span>
    </div>
</div>

<div class="kwt-card">
    <div class="kwt-card-title">OTP Settings</div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">OTP Expiry</span>
        <span class="kwt-detail-value">{{ config('kwtsms.otp.expiry_minutes', 5) }} minutes</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Resend Cooldown</span>
        <span class="kwt-detail-value">{{ config('kwtsms.otp.resend_cooldown', 3) }} minutes</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Max Attempts/Hour</span>
        <span class="kwt-detail-value">{{ config('kwtsms.otp.max_attempts_hour', 3) }}</span>
    </div>
</div>

<form method="POST" action="{{ route('kwtsms.settings.update') }}">
    @csrf
    <div class="kwt-card">
        <div class="kwt-card-title">Notification Settings</div>

        <div class="kwt-form-group">
            <label class="kwt-label" for="admin_phone">Admin Phone Number</label>
            <input
                type="text"
                id="admin_phone"
                name="admin_phone"
                class="kwt-input"
                value="{{ old('admin_phone', $adminPhone) }}"
                placeholder="e.g. 96500000000"
                maxlength="30"
            >
            <div class="kwt-help-text">Used for low-balance alerts and admin notifications.</div>
            @error('admin_phone')
                <div class="kwt-error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="kwt-form-group">
            <label class="kwt-label" for="low_balance_threshold">Low Balance Threshold (credits)</label>
            <input
                type="number"
                id="low_balance_threshold"
                name="low_balance_threshold"
                class="kwt-input"
                style="max-width:180px;"
                value="{{ old('low_balance_threshold', $lowBalanceThreshold) }}"
                min="0"
                step="1"
            >
            <div class="kwt-help-text">Alert will be sent when balance falls below this amount.</div>
            @error('low_balance_threshold')
                <div class="kwt-error-text">{{ $message }}</div>
            @enderror
        </div>

        @if(!empty($senderids) && is_array($senderids))
        <div class="kwt-form-group">
            <label class="kwt-label">Registered Sender IDs</label>
            <div>
                @foreach($senderids as $sid)
                    <span class="kwt-badge kwt-badge-blue" style="margin-right:4px;">{{ $sid }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <button type="submit" class="kwt-btn kwt-btn-primary">{{ __('kwtsms::kwtsms.save') }}</button>
    </div>
</form>
@endsection
