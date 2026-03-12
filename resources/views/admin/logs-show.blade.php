@extends('kwtsms::admin.layout')

@section('title', 'Log #' . $log->id)

@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="{{ route('kwtsms.logs.index') }}" class="kwt-btn kwt-btn-sm kwt-btn-secondary">&larr; Back</a>
    <h1 class="kwt-page-title" style="margin-bottom:0;">Log #{{ $log->id }}</h1>
    <span class="kwt-status kwt-status-{{ $log->status }}">{{ ucfirst($log->status) }}</span>
    @if($log->is_test)
        <span class="kwt-status kwt-status-test">Test</span>
    @endif
</div>

<div class="kwt-card">
    <div class="kwt-card-title">Message Details</div>

    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Recipient</span>
        <span class="kwt-detail-value">{{ $log->recipient }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Sender ID</span>
        <span class="kwt-detail-value">{{ $log->sender_id ?? '-' }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Message</span>
        <span class="kwt-detail-value" style="white-space:pre-wrap;">{{ $log->message }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Event Type</span>
        <span class="kwt-detail-value">{{ $log->event_type ?? '-' }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Message ID</span>
        <span class="kwt-detail-value">{{ $log->message_id ?? '-' }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Numbers Sent</span>
        <span class="kwt-detail-value">{{ $log->numbers_sent ?? 0 }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Points Charged</span>
        <span class="kwt-detail-value">{{ $log->points_charged > 0 ? number_format($log->points_charged, 4) : '0' }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Balance After</span>
        <span class="kwt-detail-value">{{ $log->balance_after !== null ? number_format($log->balance_after, 2) : '-' }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Is Test</span>
        <span class="kwt-detail-value">{{ $log->is_test ? 'Yes' : 'No' }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Sent At</span>
        <span class="kwt-detail-value">{{ $log->sent_at?->format('Y-m-d H:i:s') ?? '-' }}</span>
    </div>
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Created At</span>
        <span class="kwt-detail-value">{{ $log->created_at?->format('Y-m-d H:i:s') }}</span>
    </div>
    @if($log->error_code)
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Error Code</span>
        <span class="kwt-detail-value" style="color:#EF4444;">{{ $log->error_code }}</span>
    </div>
    @endif
    @if($log->error_message)
    <div class="kwt-detail-row">
        <span class="kwt-detail-key">Error Message</span>
        <span class="kwt-detail-value" style="color:#EF4444;">{{ $log->error_message }}</span>
    </div>
    @endif
</div>

@if(!empty($log->api_request))
<div class="kwt-card">
    <div class="kwt-card-title">API Request</div>
    @php
        $requestData = $log->api_request;
        // Mask sensitive fields
        if (is_array($requestData)) {
            if (isset($requestData['password'])) {
                $requestData['password'] = '***masked***';
            }
            if (isset($requestData['username'])) {
                $requestData['username'] = '***masked***';
            }
        }
    @endphp
    <pre class="kwt-code">{{ json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</div>
@endif

@if(!empty($log->api_response))
<div class="kwt-card">
    <div class="kwt-card-title">API Response</div>
    <pre class="kwt-code">{{ json_encode($log->api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</div>
@endif
@endsection
