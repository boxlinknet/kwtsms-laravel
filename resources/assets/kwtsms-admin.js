/**
 * kwtSMS Admin Panel
 *
 * Standalone JavaScript for the kwtSMS admin panel.
 * When published (php artisan vendor:publish --tag=kwtsms-assets),
 * this file is loaded instead of the inline scripts in layout.blade.php.
 *
 * Requires window.kwtSmsConfig.connectUrl to be set by the Blade layout.
 */

'use strict';

/**
 * Get CSRF token from meta tag.
 *
 * @returns {string}
 */
function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

/**
 * Show a confirm dialog before submitting a form.
 *
 * @param {string} message
 * @param {HTMLFormElement} form
 * @returns {boolean}
 */
function confirmAction(message, form) {
    if (confirm(message || 'Are you sure?')) {
        form.submit();
    }
    return false;
}

/**
 * Initialise the SMS character counter on a textarea.
 *
 * Detects Arabic characters and uses the correct page size:
 * - English / Latin: 160 chars per page
 * - Arabic / Unicode: 70 chars per page
 *
 * @param {string} textareaId
 * @param {string} counterId
 */
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

/**
 * Test the kwtSMS API connection from the Settings page.
 * Reads the endpoint URL from window.kwtSmsConfig.connectUrl.
 *
 * @param {HTMLButtonElement} btn
 */
function testConnection(btn) {
    var url = (window.kwtSmsConfig && window.kwtSmsConfig.connectUrl) ? window.kwtSmsConfig.connectUrl : '';
    if (!url) { return; }

    btn.disabled = true;
    btn.textContent = 'Connecting...';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
        body: JSON.stringify({}),
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        btn.disabled = false;
        var info = document.getElementById('connect-result');
        if (data.success) {
            btn.textContent = 'Connected';
            btn.style.background = '#10B981';
            if (info) {
                info.textContent = 'Balance: ' + (data.balance !== undefined ? data.balance + ' credits' : 'N/A');
                info.style.display = 'inline';
                info.style.color = '';
            }
        } else {
            btn.textContent = 'Failed';
            btn.style.background = '#EF4444';
            if (info) {
                info.textContent = data.message || 'Connection failed';
                info.style.display = 'inline';
                info.style.color = '#EF4444';
            }
        }
    })
    .catch(function () {
        btn.disabled = false;
        btn.textContent = 'Error';
        btn.style.background = '#EF4444';
    });
}
