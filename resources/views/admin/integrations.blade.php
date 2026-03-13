@extends('kwtsms::admin.layout')

@section('title', __('kwtsms::kwtsms.integrations'))

@section('content')
<h1 class="kwt-page-title">{{ __('kwtsms::kwtsms.integrations') }}</h1>

<form method="POST" action="{{ route('kwtsms.integrations.update') }}">
    @csrf

    <div class="kwt-card">
        <div class="kwt-card-title">E-commerce Events</div>

        @php
            $ecommerceEvents = [
                'order_placed' => ['label' => 'Order Placed', 'desc' => 'Send SMS when a new order is placed.'],
                'order_confirmed' => ['label' => 'Order Confirmed', 'desc' => 'Send SMS when an order is confirmed.'],
                'order_shipped' => ['label' => 'Order Shipped', 'desc' => 'Send SMS when an order is shipped.'],
                'order_delivered' => ['label' => 'Order Delivered', 'desc' => 'Send SMS when an order is delivered.'],
                'order_cancelled' => ['label' => 'Order Cancelled', 'desc' => 'Send SMS when an order is cancelled.'],
                'order_status_changed' => ['label' => 'Order Status Changed', 'desc' => 'Send SMS on any order status change.'],
            ];
        @endphp

        @foreach($ecommerceEvents as $key => $event)
            <div class="kwt-toggle-wrap">
                <div class="kwt-toggle-info">
                    <div class="kwt-toggle-label">{{ $event['label'] }}</div>
                    <div class="kwt-toggle-desc">{{ $event['desc'] }}</div>
                </div>
                <label class="kwt-toggle">
                    <input
                        type="checkbox"
                        name="integrations[{{ $key }}]"
                        value="1"
                        {{ !empty($integrations[$key]) ? 'checked' : '' }}
                    >
                    <span class="kwt-toggle-slider"></span>
                </label>
            </div>
        @endforeach
    </div>

    <div class="kwt-card">
        <div class="kwt-card-title">Authentication Events</div>

        @php
            $authEvents = [
                'otp' => ['label' => 'OTP / Verification Code', 'desc' => 'Send OTP codes via SMS.'],
                'password_reset' => ['label' => 'Password Reset', 'desc' => 'Send SMS when a password reset is requested.'],
                'login_alert' => ['label' => 'Login Alert', 'desc' => 'Notify users of new logins.'],
                'account_created' => ['label' => 'Account Created', 'desc' => 'Send welcome SMS on registration.'],
            ];
        @endphp

        @foreach($authEvents as $key => $event)
            <div class="kwt-toggle-wrap">
                <div class="kwt-toggle-info">
                    <div class="kwt-toggle-label">{{ $event['label'] }}</div>
                    <div class="kwt-toggle-desc">{{ $event['desc'] }}</div>
                </div>
                <label class="kwt-toggle">
                    <input
                        type="checkbox"
                        name="integrations[{{ $key }}]"
                        value="1"
                        {{ !empty($integrations[$key]) ? 'checked' : '' }}
                    >
                    <span class="kwt-toggle-slider"></span>
                </label>
            </div>
        @endforeach
    </div>

    <div class="kwt-card">
        <div class="kwt-card-title">Custom Events</div>
        <p style="font-size:13px;color:#6B7280;margin-bottom:12px;">
            Custom events can be triggered from your code using
            <code>app(SmsSender::class)->send($phone, $message, null, ['event_type' => 'your_event'])</code>.
        </p>
        @php
            $customEvents = [
                'custom_notification' => ['label' => 'Custom Notification', 'desc' => 'Generic notification event.'],
                'appointment_reminder' => ['label' => 'Appointment Reminder', 'desc' => 'Appointment/booking reminder.'],
                'payment_received' => ['label' => 'Payment Received', 'desc' => 'Notify on payment receipt.'],
            ];
        @endphp

        @foreach($customEvents as $key => $event)
            <div class="kwt-toggle-wrap">
                <div class="kwt-toggle-info">
                    <div class="kwt-toggle-label">{{ $event['label'] }}</div>
                    <div class="kwt-toggle-desc">{{ $event['desc'] }}</div>
                </div>
                <label class="kwt-toggle">
                    <input
                        type="checkbox"
                        name="integrations[{{ $key }}]"
                        value="1"
                        {{ !empty($integrations[$key]) ? 'checked' : '' }}
                    >
                    <span class="kwt-toggle-slider"></span>
                </label>
            </div>
        @endforeach
    </div>

    <button type="submit" class="kwt-btn kwt-btn-primary">{{ __('kwtsms::kwtsms.save') }}</button>
</form>
@endsection
