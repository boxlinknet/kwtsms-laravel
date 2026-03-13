@extends('kwtsms::admin.layout')

@section('title', $template ? 'Edit Template' : 'Create Template')

@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="{{ route('kwtsms.templates.index') }}" class="kwt-btn kwt-btn-sm kwt-btn-secondary">&larr; Back</a>
    <h1 class="kwt-page-title" style="margin-bottom:0;">
        {{ $template ? 'Edit Template' : 'Create Template' }}
    </h1>
</div>

<div class="kwt-card">
    <form
        method="POST"
        action="{{ $template ? route('kwtsms.templates.update', $template) : route('kwtsms.templates.store') }}"
    >
        @csrf
        @if($template)
            @method('PUT')
        @endif

        <div class="kwt-form-group">
            <label class="kwt-label" for="name">Template Name</label>
            <input
                type="text"
                id="name"
                name="name"
                class="kwt-input"
                value="{{ old('name', $template?->name) }}"
                required
                maxlength="100"
                placeholder="e.g. Order Confirmation EN"
            >
            @error('name')
                <div class="kwt-error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="kwt-form-row">
            <div class="kwt-form-group">
                <label class="kwt-label" for="event_type">Event Type</label>
                <input
                    type="text"
                    id="event_type"
                    name="event_type"
                    class="kwt-input"
                    value="{{ old('event_type', $template?->event_type) }}"
                    required
                    maxlength="60"
                    placeholder="e.g. order_placed"
                >
                <div class="kwt-help-text">Use snake_case. e.g. order_placed, otp, password_reset</div>
                @error('event_type')
                    <div class="kwt-error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="kwt-form-group" style="max-width:160px;">
                <label class="kwt-label" for="locale">Locale</label>
                <select id="locale" name="locale" class="kwt-select">
                    <option value="en" {{ old('locale', $template?->locale) === 'en' ? 'selected' : '' }}>English (en)</option>
                    <option value="ar" {{ old('locale', $template?->locale) === 'ar' ? 'selected' : '' }}>Arabic (ar)</option>
                </select>
                @error('locale')
                    <div class="kwt-error-text">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="kwt-form-group">
            <label class="kwt-label" for="body">Message Body</label>
            <textarea
                id="body"
                name="body"
                class="kwt-textarea"
                rows="5"
                required
                placeholder="e.g. Your order @{{order_number}} has been placed."
            >{{ old('body', $template?->body) }}</textarea>
            <div id="body-counter" class="kwt-char-counter">0 chars / 1 SMS</div>
            <div class="kwt-help-text">Use <code>@{{variable_name}}</code> for placeholders. e.g. @{{order_number}}, @{{name}}, @{{code}}</div>
            @error('body')
                <div class="kwt-error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="kwt-form-group">
            <label class="kwt-toggle" style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input
                    type="hidden"
                    name="is_active"
                    value="0"
                >
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    style="width:auto;height:auto;opacity:1;position:static;"
                    {{ old('is_active', $template?->is_active ?? true) ? 'checked' : '' }}
                >
                <span class="kwt-label" style="margin-bottom:0;cursor:pointer;">Active</span>
            </label>
            <div class="kwt-help-text" style="margin-top:4px;">Inactive templates will not be used for automatic sends.</div>
        </div>

        <div class="kwt-actions">
            <button type="submit" class="kwt-btn kwt-btn-primary">
                {{ $template ? __('kwtsms::kwtsms.save') : __('kwtsms::kwtsms.create') }}
            </button>
            <a href="{{ route('kwtsms.templates.index') }}" class="kwt-btn kwt-btn-secondary">{{ __('kwtsms::kwtsms.cancel') }}</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initCharCounter('body', 'body-counter');
    });
</script>
@endsection
