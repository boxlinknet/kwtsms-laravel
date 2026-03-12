@extends('kwtsms::admin.layout')

@section('title', __('kwtsms::kwtsms.templates'))

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="kwt-page-title" style="margin-bottom:0;">{{ __('kwtsms::kwtsms.templates') }}</h1>
    <a href="{{ route('kwtsms.templates.create') }}" class="kwt-btn kwt-btn-primary">
        + {{ __('kwtsms::kwtsms.create') }} Template
    </a>
</div>

<div class="kwt-card">
    @if($templates->isEmpty())
        <div class="kwt-empty">
            <div class="kwt-empty-icon">&#128196;</div>
            <div>No templates yet.</div>
            <a href="{{ route('kwtsms.templates.create') }}" class="kwt-btn kwt-btn-primary" style="margin-top:12px;display:inline-flex;">
                Create your first template
            </a>
        </div>
    @else
        <div class="kwt-table-wrap">
            <table class="kwt-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Event Type</th>
                        <th>Locale</th>
                        <th>Status</th>
                        <th>Preview</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                        <tr>
                            <td><strong>{{ $template->name }}</strong></td>
                            <td><code style="font-size:12px;">{{ $template->event_type }}</code></td>
                            <td>
                                <span class="kwt-badge {{ $template->locale === 'ar' ? 'kwt-badge-orange' : 'kwt-badge-blue' }}">
                                    {{ strtoupper($template->locale) }}
                                </span>
                            </td>
                            <td>
                                @if($template->is_active)
                                    <span class="kwt-badge kwt-badge-green">Active</span>
                                @else
                                    <span class="kwt-badge kwt-badge-red">Inactive</span>
                                @endif
                            </td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:#6B7280;">
                                {{ $template->body }}
                            </td>
                            <td>
                                <div class="kwt-actions">
                                    <a href="{{ route('kwtsms.templates.edit', $template) }}" class="kwt-btn kwt-btn-sm kwt-btn-secondary">
                                        {{ __('kwtsms::kwtsms.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('kwtsms.templates.destroy', $template) }}" style="display:inline;" onsubmit="return confirmAction('Delete this template?', this)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="kwt-btn kwt-btn-sm kwt-btn-danger">
                                            {{ __('kwtsms::kwtsms.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
