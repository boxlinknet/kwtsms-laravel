@extends('kwtsms::admin.layout')

@section('title', __('kwtsms::kwtsms.logs'))

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="kwt-page-title" style="margin-bottom:0;">{{ __('kwtsms::kwtsms.logs') }}</h1>
    <div class="kwt-actions">
        <a href="{{ route('kwtsms.logs.export') }}" class="kwt-btn kwt-btn-sm kwt-btn-secondary">
            {{ __('kwtsms::kwtsms.export') }}
        </a>
        <form method="POST" action="{{ route('kwtsms.logs.clear') }}" onsubmit="return confirmAction('Clear all logs? This cannot be undone.', this)">
            @csrf
            @method('DELETE')
            <button type="submit" class="kwt-btn kwt-btn-sm kwt-btn-danger">
                {{ __('kwtsms::kwtsms.clear_logs') }}
            </button>
        </form>
    </div>
</div>

<div class="kwt-card">
    @if($logs->isEmpty())
        <div class="kwt-empty">
            <div class="kwt-empty-icon">&#128203;</div>
            <div>No logs found.</div>
        </div>
    @else
        <div class="kwt-table-wrap">
            <table class="kwt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Recipient</th>
                        <th>Sender</th>
                        <th>Status</th>
                        <th>Event</th>
                        <th>Points</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td style="color:#9CA3AF;font-size:12px;">{{ $log->id }}</td>
                            <td>{{ $log->recipient }}</td>
                            <td>{{ $log->sender_id ?? '-' }}</td>
                            <td>
                                <span class="kwt-status kwt-status-{{ $log->status }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                                @if($log->is_test)
                                    <span class="kwt-status kwt-status-test" style="margin-left:3px;">Test</span>
                                @endif
                            </td>
                            <td>
                                @if($log->event_type)
                                    <code style="font-size:11px;">{{ $log->event_type }}</code>
                                @else
                                    <span style="color:#9CA3AF;">-</span>
                                @endif
                            </td>
                            <td>{{ $log->points_charged > 0 ? number_format($log->points_charged, 2) : '-' }}</td>
                            <td style="white-space:nowrap;font-size:12px;color:#6B7280;">
                                {{ $log->created_at?->format('Y-m-d H:i') }}
                            </td>
                            <td>
                                <a href="{{ route('kwtsms.logs.show', $log) }}" class="kwt-btn kwt-btn-sm kwt-btn-secondary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="kwt-pagination">
                @if($logs->onFirstPage())
                    <span>&laquo;</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}">&laquo;</a>
                @endif

                @foreach($logs->getUrlRange(max(1, $logs->currentPage()-3), min($logs->lastPage(), $logs->currentPage()+3)) as $page => $url)
                    @if($page == $logs->currentPage())
                        <span class="current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}">&raquo;</a>
                @else
                    <span>&raquo;</span>
                @endif
            </div>
        @endif
    @endif
</div>
@endsection
