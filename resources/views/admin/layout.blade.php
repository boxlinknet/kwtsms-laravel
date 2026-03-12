<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>kwtSMS Admin @hasSection('title') - @yield('title') @endif</title>
    <style>
        :root {
            --kwt-orange: #FFA200;
            --kwt-blue: #79CCF2;
            --kwt-text: #434345;
            --kwt-bg: #FFFFFF;
            --kwt-border: #E5E7EB;
            --kwt-muted: #6B7280;
            --kwt-light: #F9FAFB;
            --kwt-success: #10B981;
            --kwt-error: #EF4444;
            --kwt-warning: #F59E0B;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #F3F4F6;
            color: var(--kwt-text);
            font-size: 14px;
            line-height: 1.5;
        }

        a {
            color: var(--kwt-orange);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Top bar */
        .kwt-topbar {
            background: var(--kwt-bg);
            border-bottom: 3px solid var(--kwt-orange);
            padding: 0 24px;
            display: flex;
            align-items: center;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }

        .kwt-logo {
            font-family: 'Montserrat', Arial, sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: var(--kwt-orange);
            letter-spacing: -0.5px;
            margin-right: 8px;
        }

        .kwt-logo span {
            color: var(--kwt-blue);
        }

        .kwt-topbar-badges {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .kwt-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .kwt-badge-green {
            background: #D1FAE5;
            color: #065F46;
        }

        .kwt-badge-red {
            background: #FEE2E2;
            color: #991B1B;
        }

        .kwt-badge-yellow {
            background: #FEF3C7;
            color: #92400E;
        }

        .kwt-badge-blue {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .kwt-badge-orange {
            background: #FFF7ED;
            color: #C2410C;
        }

        /* Layout */
        .kwt-wrapper {
            display: flex;
            min-height: calc(100vh - 56px);
        }

        /* Sidebar */
        .kwt-sidebar {
            width: 220px;
            background: var(--kwt-bg);
            border-right: 1px solid var(--kwt-border);
            padding: 20px 0;
            flex-shrink: 0;
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
        }

        .kwt-nav {
            list-style: none;
        }

        .kwt-nav-item a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: var(--kwt-text);
            font-size: 14px;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }

        [dir="rtl"] .kwt-nav-item a {
            border-left: none;
            border-right: 3px solid transparent;
        }

        .kwt-nav-item a:hover {
            background: var(--kwt-light);
            color: var(--kwt-orange);
            text-decoration: none;
        }

        .kwt-nav-item a.active {
            background: #FFF7ED;
            color: var(--kwt-orange);
            border-left-color: var(--kwt-orange);
            font-weight: 600;
        }

        [dir="rtl"] .kwt-nav-item a.active {
            border-left-color: transparent;
            border-right-color: var(--kwt-orange);
        }

        .kwt-nav-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* Main content */
        .kwt-main {
            flex: 1;
            padding: 28px;
            max-width: 1100px;
        }

        .kwt-page-title {
            font-family: 'Montserrat', Arial, sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--kwt-text);
            margin-bottom: 20px;
        }

        /* Cards */
        .kwt-card {
            background: var(--kwt-bg);
            border: 1px solid var(--kwt-border);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .kwt-card-title {
            font-family: 'Montserrat', Arial, sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--kwt-text);
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--kwt-border);
        }

        /* Grid */
        .kwt-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .kwt-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 768px) {
            .kwt-grid-3,
            .kwt-grid-2 {
                grid-template-columns: 1fr;
            }

            .kwt-sidebar {
                display: none;
            }
        }

        /* Stat cards */
        .kwt-stat-card {
            background: var(--kwt-bg);
            border: 1px solid var(--kwt-border);
            border-radius: 8px;
            padding: 18px 20px;
            text-align: center;
        }

        .kwt-stat-value {
            font-family: 'Montserrat', Arial, sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: var(--kwt-orange);
            line-height: 1;
            margin-bottom: 6px;
        }

        .kwt-stat-label {
            font-size: 12px;
            color: var(--kwt-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Flash messages */
        .kwt-flash {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 500;
        }

        .kwt-flash-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #6EE7B7;
        }

        .kwt-flash-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }

        /* Forms */
        .kwt-form-group {
            margin-bottom: 16px;
        }

        .kwt-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--kwt-text);
            margin-bottom: 6px;
        }

        .kwt-input,
        .kwt-select,
        .kwt-textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--kwt-border);
            border-radius: 6px;
            font-size: 14px;
            color: var(--kwt-text);
            background: var(--kwt-bg);
            outline: none;
            transition: border-color 0.15s;
            font-family: inherit;
        }

        .kwt-input:focus,
        .kwt-select:focus,
        .kwt-textarea:focus {
            border-color: var(--kwt-orange);
            box-shadow: 0 0 0 3px rgba(255, 162, 0, 0.1);
        }

        .kwt-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .kwt-help-text {
            font-size: 12px;
            color: var(--kwt-muted);
            margin-top: 4px;
        }

        .kwt-error-text {
            font-size: 12px;
            color: var(--kwt-error);
            margin-top: 4px;
        }

        /* Buttons */
        .kwt-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.15s;
            font-family: inherit;
            text-decoration: none;
        }

        .kwt-btn:hover {
            text-decoration: none;
        }

        .kwt-btn-primary {
            background: var(--kwt-orange);
            color: #fff;
        }

        .kwt-btn-primary:hover {
            background: #E69100;
            color: #fff;
        }

        .kwt-btn-secondary {
            background: var(--kwt-light);
            color: var(--kwt-text);
            border: 1px solid var(--kwt-border);
        }

        .kwt-btn-secondary:hover {
            background: #E5E7EB;
        }

        .kwt-btn-danger {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }

        .kwt-btn-danger:hover {
            background: #FCA5A5;
            color: #7F1D1D;
        }

        .kwt-btn-blue {
            background: var(--kwt-blue);
            color: #fff;
        }

        .kwt-btn-blue:hover {
            background: #5BB8E0;
            color: #fff;
        }

        .kwt-btn-sm {
            padding: 5px 12px;
            font-size: 12px;
        }

        /* Tables */
        .kwt-table-wrap {
            overflow-x: auto;
        }

        .kwt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .kwt-table th {
            background: var(--kwt-light);
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--kwt-muted);
            border-bottom: 1px solid var(--kwt-border);
            white-space: nowrap;
        }

        [dir="rtl"] .kwt-table th {
            text-align: right;
        }

        .kwt-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--kwt-border);
            vertical-align: middle;
        }

        .kwt-table tr:last-child td {
            border-bottom: none;
        }

        .kwt-table tr:hover td {
            background: var(--kwt-light);
        }

        /* Status badges */
        .kwt-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }

        .kwt-status-sent {
            background: #D1FAE5;
            color: #065F46;
        }

        .kwt-status-failed {
            background: #FEE2E2;
            color: #991B1B;
        }

        .kwt-status-pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .kwt-status-test {
            background: #DBEAFE;
            color: #1E40AF;
        }

        /* Toggle switch */
        .kwt-toggle-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--kwt-border);
        }

        .kwt-toggle-wrap:last-child {
            border-bottom: none;
        }

        .kwt-toggle-info {
            flex: 1;
        }

        .kwt-toggle-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--kwt-text);
        }

        .kwt-toggle-desc {
            font-size: 12px;
            color: var(--kwt-muted);
            margin-top: 2px;
        }

        .kwt-toggle {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            flex-shrink: 0;
        }

        .kwt-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .kwt-toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #D1D5DB;
            border-radius: 24px;
            transition: 0.2s;
        }

        .kwt-toggle-slider:before {
            position: absolute;
            content: '';
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.2s;
        }

        .kwt-toggle input:checked + .kwt-toggle-slider {
            background: var(--kwt-orange);
        }

        .kwt-toggle input:checked + .kwt-toggle-slider:before {
            transform: translateX(20px);
        }

        /* Actions row */
        .kwt-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .kwt-actions-right {
            margin-left: auto;
        }

        /* Pagination */
        .kwt-pagination {
            display: flex;
            justify-content: center;
            gap: 4px;
            margin-top: 16px;
        }

        .kwt-pagination a,
        .kwt-pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            border-radius: 6px;
            font-size: 13px;
            border: 1px solid var(--kwt-border);
            color: var(--kwt-text);
            background: var(--kwt-bg);
        }

        .kwt-pagination a:hover {
            background: var(--kwt-light);
            text-decoration: none;
        }

        .kwt-pagination span.current {
            background: var(--kwt-orange);
            color: #fff;
            border-color: var(--kwt-orange);
            font-weight: 600;
        }

        /* Code/pre */
        .kwt-code {
            background: #1F2937;
            color: #E5E7EB;
            border-radius: 6px;
            padding: 14px 16px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            overflow-x: auto;
            line-height: 1.6;
        }

        /* Footer */
        .kwt-footer {
            text-align: center;
            padding: 16px;
            font-size: 12px;
            color: var(--kwt-muted);
            border-top: 1px solid var(--kwt-border);
            margin-top: 40px;
        }

        /* Detail row */
        .kwt-detail-row {
            display: flex;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--kwt-border);
            font-size: 13px;
        }

        .kwt-detail-row:last-child {
            border-bottom: none;
        }

        .kwt-detail-key {
            font-weight: 600;
            color: var(--kwt-muted);
            min-width: 160px;
            flex-shrink: 0;
        }

        .kwt-detail-value {
            color: var(--kwt-text);
            word-break: break-all;
        }

        /* Empty state */
        .kwt-empty {
            text-align: center;
            padding: 40px 20px;
            color: var(--kwt-muted);
        }

        .kwt-empty-icon {
            font-size: 36px;
            margin-bottom: 8px;
        }

        /* Char counter */
        .kwt-char-counter {
            font-size: 12px;
            color: var(--kwt-muted);
            text-align: right;
            margin-top: 4px;
        }

        .kwt-char-counter.warning {
            color: var(--kwt-warning);
        }

        .kwt-char-counter.danger {
            color: var(--kwt-error);
        }

        /* Inline form row */
        .kwt-form-row {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .kwt-form-row .kwt-form-group {
            flex: 1;
            margin-bottom: 0;
        }
    </style>
    <script>window.kwtSmsConfig = { connectUrl: '{{ route("kwtsms.settings.connect") }}' };</script>
</head>
<body>

    <div class="kwt-topbar">
        <span class="kwt-logo">kwt<span>SMS</span></span>
        <div class="kwt-topbar-badges">
            @if(config('kwtsms.enabled', true))
                <span class="kwt-badge kwt-badge-green">Enabled</span>
            @else
                <span class="kwt-badge kwt-badge-red">Disabled</span>
            @endif
            @if(config('kwtsms.test_mode', false))
                <span class="kwt-badge kwt-badge-yellow">Test Mode</span>
            @endif
            @if(!empty(config('kwtsms.username')) && !empty(config('kwtsms.password')))
                <span class="kwt-badge kwt-badge-green">Configured</span>
            @else
                <span class="kwt-badge kwt-badge-red">Not Configured</span>
            @endif
        </div>
    </div>

    <div class="kwt-wrapper">
        <nav class="kwt-sidebar">
            <ul class="kwt-nav">
                <li class="kwt-nav-item">
                    <a href="{{ route('kwtsms.dashboard') }}" class="{{ request()->routeIs('kwtsms.dashboard') ? 'active' : '' }}">
                        <svg class="kwt-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        {{ __('kwtsms::kwtsms.dashboard') }}
                    </a>
                </li>
                <li class="kwt-nav-item">
                    <a href="{{ route('kwtsms.settings') }}" class="{{ request()->routeIs('kwtsms.settings*') ? 'active' : '' }}">
                        <svg class="kwt-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ __('kwtsms::kwtsms.settings') }}
                    </a>
                </li>
                <li class="kwt-nav-item">
                    <a href="{{ route('kwtsms.templates.index') }}" class="{{ request()->routeIs('kwtsms.templates*') ? 'active' : '' }}">
                        <svg class="kwt-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('kwtsms::kwtsms.templates') }}
                    </a>
                </li>
                <li class="kwt-nav-item">
                    <a href="{{ route('kwtsms.integrations') }}" class="{{ request()->routeIs('kwtsms.integrations*') ? 'active' : '' }}">
                        <svg class="kwt-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        {{ __('kwtsms::kwtsms.integrations') }}
                    </a>
                </li>
                <li class="kwt-nav-item">
                    <a href="{{ route('kwtsms.logs.index') }}" class="{{ request()->routeIs('kwtsms.logs*') ? 'active' : '' }}">
                        <svg class="kwt-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ __('kwtsms::kwtsms.logs') }}
                    </a>
                </li>
                <li class="kwt-nav-item">
                    <a href="{{ route('kwtsms.alerts') }}" class="{{ request()->routeIs('kwtsms.alerts*') ? 'active' : '' }}">
                        <svg class="kwt-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        {{ __('kwtsms::kwtsms.admin_alerts') }}
                    </a>
                </li>
                <li class="kwt-nav-item">
                    <a href="{{ route('kwtsms.help') }}" class="{{ request()->routeIs('kwtsms.help*') ? 'active' : '' }}">
                        <svg class="kwt-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('kwtsms::kwtsms.help') }}
                    </a>
                </li>
            </ul>
        </nav>

        <main class="kwt-main">
            @if(session('success'))
                <div class="kwt-flash kwt-flash-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="kwt-flash kwt-flash-error">{{ session('error') }}</div>
            @endif

            @yield('content')

            <div class="kwt-footer">
                kwtSMS Laravel Package &middot; <a href="{{ route('kwtsms.help') }}">Help</a>
            </div>
        </main>
    </div>

    <script>
        // CSRF token helper for AJAX
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        // Confirm dialog helper
        function confirmAction(message, form) {
            if (confirm(message || 'Are you sure?')) {
                form.submit();
            }
            return false;
        }

        // Character counter for SMS body textarea
        function initCharCounter(textareaId, counterId) {
            var textarea = document.getElementById(textareaId);
            var counter = document.getElementById(counterId);
            if (!textarea || !counter) { return; }

            function update() {
                var text = textarea.value;
                var len = text.length;
                var hasArabic = /[\u0600-\u06FF]/.test(text);
                var pageSize = hasArabic ? 70 : 160;
                var pageLabel = hasArabic ? 'AR' : 'EN';
                var smsCount = len === 0 ? 1 : Math.ceil(len / pageSize);
                counter.textContent = len + ' chars / ' + smsCount + ' SMS (' + pageLabel + ', ' + pageSize + '/page)';
                counter.className = 'kwt-char-counter';
                if (len > pageSize * 2) { counter.className += ' danger'; }
                else if (len > pageSize) { counter.className += ' warning'; }
            }

            textarea.addEventListener('input', update);
            update();
        }

        // Connect/test connection button
        function testConnection(btn) {
            btn.disabled = true;
            btn.textContent = 'Connecting...';

            fetch('{{ route("kwtsms.settings.connect") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                if (data.success) {
                    btn.textContent = 'Connected';
                    btn.style.background = '#10B981';
                    var info = document.getElementById('connect-result');
                    if (info) {
                        info.textContent = 'Balance: ' + (data.balance !== undefined ? data.balance + ' credits' : 'N/A');
                        info.style.display = 'inline';
                    }
                } else {
                    btn.textContent = 'Failed';
                    btn.style.background = '#EF4444';
                    var info2 = document.getElementById('connect-result');
                    if (info2) {
                        info2.textContent = data.message || 'Connection failed';
                        info2.style.display = 'inline';
                        info2.style.color = '#EF4444';
                    }
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = 'Error';
                btn.style.background = '#EF4444';
            });
        }
    </script>

</body>
</html>
