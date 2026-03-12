@extends('kwtsms::admin.layout')

@section('title', __('kwtsms::kwtsms.dashboard'))

@section('content')
<h1 class="kwt-page-title">{{ __('kwtsms::kwtsms.dashboard') }}</h1>

<div class="kwt-grid-3" style="margin-bottom:20px;">
    <div class="kwt-stat-card">
        <div class="kwt-stat-value">
            {{ $balance !== null ? number_format((float)$balance, 1) : '-' }}
        </div>
        <div class="kwt-stat-label">{{ __('kwtsms::kwtsms.balance') }} ({{ __('kwtsms::kwtsms.credits') }})</div>
    </div>
    <div class="kwt-stat-card">
        <div class="kwt-stat-value">{{ number_format($totalSent7Days) }}</div>
        <div class="kwt-stat-label">Sent (last 7 days)</div>
    </div>
    <div class="kwt-stat-card">
        <div class="kwt-stat-value">{{ number_format($totalSent30Days) }}</div>
        <div class="kwt-stat-label">Sent (last 30 days)</div>
    </div>
</div>

<div class="kwt-grid-2" style="margin-bottom:20px;">
    <div class="kwt-card">
        <div class="kwt-card-title">Gateway Status</div>
        <div class="kwt-detail-row">
            <span class="kwt-detail-key">Status</span>
            <span class="kwt-detail-value">
                @if($isConfigured)
                    <span class="kwt-badge kwt-badge-green">{{ __('kwtsms::kwtsms.connected') }}</span>
                @else
                    <span class="kwt-badge kwt-badge-red">{{ __('kwtsms::kwtsms.not_connected') }}</span>
                @endif
            </span>
        </div>
        <div class="kwt-detail-row">
            <span class="kwt-detail-key">SMS Sending</span>
            <span class="kwt-detail-value">
                @if($isEnabled)
                    <span class="kwt-badge kwt-badge-green">{{ __('kwtsms::kwtsms.enabled') }}</span>
                @else
                    <span class="kwt-badge kwt-badge-red">{{ __('kwtsms::kwtsms.disabled') }}</span>
                @endif
            </span>
        </div>
        <div class="kwt-detail-row">
            <span class="kwt-detail-key">Mode</span>
            <span class="kwt-detail-value">
                @if($isTestMode)
                    <span class="kwt-badge kwt-badge-yellow">{{ __('kwtsms::kwtsms.test_mode') }}</span>
                @else
                    <span class="kwt-badge kwt-badge-green">Live</span>
                @endif
            </span>
        </div>
        <div class="kwt-detail-row">
            <span class="kwt-detail-key">Sender ID</span>
            <span class="kwt-detail-value">{{ config('kwtsms.sender', '-') }}</span>
        </div>
        @if($lastSync)
        <div class="kwt-detail-row">
            <span class="kwt-detail-key">Last Sync</span>
            <span class="kwt-detail-value">{{ $lastSync }}</span>
        </div>
        @endif
        @if(!empty($senderids) && is_array($senderids))
        <div class="kwt-detail-row">
            <span class="kwt-detail-key">Sender IDs</span>
            <span class="kwt-detail-value">
                @foreach($senderids as $sid)
                    <span class="kwt-badge kwt-badge-blue" style="margin-right:4px;">{{ $sid }}</span>
                @endforeach
            </span>
        </div>
        @endif
    </div>

    <div class="kwt-card">
        <div class="kwt-card-title">Quick Actions</div>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="{{ route('kwtsms.settings') }}" class="kwt-btn kwt-btn-secondary">
                Configure API Credentials
            </a>
            <a href="{{ route('kwtsms.templates.create') }}" class="kwt-btn kwt-btn-secondary">
                Create SMS Template
            </a>
            <a href="{{ route('kwtsms.logs.index') }}" class="kwt-btn kwt-btn-secondary">
                View Logs
            </a>
            @if(!$isConfigured)
                <div class="kwt-flash kwt-flash-error" style="margin:0;">
                    API credentials not configured. <a href="{{ route('kwtsms.settings') }}">Go to Settings</a>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="kwt-card">
    <div class="kwt-card-title" style="display:flex;align-items:center;justify-content:space-between;">
        <span>Recent Logs</span>
        <a href="{{ route('kwtsms.logs.index') }}" class="kwt-btn kwt-btn-sm kwt-btn-secondary">View All</a>
    </div>
    @if($recentLogs->isEmpty())
        <div class="kwt-empty">
            <div class="kwt-empty-icon">&#128203;</div>
            <div>No messages sent yet.</div>
        </div>
    @else
        <div class="kwt-table-wrap">
            <table class="kwt-table">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Sender</th>
                        <th>Status</th>
                        <th>Event</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLogs as $log)
                        <tr>
                            <td>{{ $log->recipient }}</td>
                            <td>{{ $log->sender_id ?? '-' }}</td>
                            <td>
                                <span class="kwt-status kwt-status-{{ $log->status }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                                @if($log->is_test)
                                    <span class="kwt-status kwt-status-test" style="margin-left:4px;">Test</span>
                                @endif
                            </td>
                            <td>{{ $log->event_type ?? '-' }}</td>
                            <td>{{ $log->created_at?->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
