/* ─── app.js : Global utilities, Service Worker, Toast system ─── */

// ─── Service Worker Registration ───────────────────────────────
function registerSW() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/SSRCMS/sw.js')
            .then(reg  => console.log('[SW] Registered:', reg.scope))
            .catch(err => console.warn('[SW] Registration failed:', err));
    }
}

// ─── Toast Notification ────────────────────────────────────────
/**
 * @param {string} message  - Text to display
 * @param {'success'|'error'|'info'} type
 * @param {number} duration - Auto-hide ms (default 4000)
 */
function showToast(message, type = 'info', duration = 4000) {
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
    const id = 'toast_' + Date.now();

    const html = `
        <div id="${id}" class="toast toast-${type} align-items-center show mb-2"
             role="alert" aria-live="assertive">
            <div class="d-flex align-items-center gap-2 p-3">
                <i class="fas ${icons[type] || icons.info}" style="font-size:1rem;
                    color:${type==='success'?'var(--success)':type==='error'?'var(--danger)':'var(--info)'}"></i>
                <span class="flex-grow-1">${message}</span>
                <button type="button" class="btn-close btn-close-white btn-sm ms-2"
                        onclick="document.getElementById('${id}').remove()"></button>
            </div>
        </div>`;

    $('#toastContainer').append(html);
    setTimeout(() => $(`#${id}`).fadeOut(300, function(){ $(this).remove(); }), duration);
}

// ─── Format date string ────────────────────────────────────────
function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'numeric' })
        + ' ' + d.toLocaleTimeString('en-IN', { hour:'2-digit', minute:'2-digit' });
}

// ─── Status badge HTML ─────────────────────────────────────────
function getStatusBadge(status) {
    const map = {
        'Pending':     ['badge-pending',    'fas fa-clock',        'Pending'],
        'In-Progress': ['badge-inprogress', 'fas fa-spinner fa-spin', 'In-Progress'],
        'Resolved':    ['badge-resolved',   'fas fa-check-circle', 'Resolved'],
        'Closed':      ['badge-closed',     'fas fa-times-circle', 'Closed'],
    };
    const [cls, icon, label] = map[status] || ['badge-closed', 'fas fa-circle', status];
    return `<span class="badge-status ${cls}"><i class="${icon}"></i>${label}</span>`;
}

// ─── Priority badge HTML ───────────────────────────────────────
function getPriorityBadge(priority) {
    const map = {
        'Low':      ['badge-low',      '🟢'],
        'Medium':   ['badge-medium',   '🟡'],
        'High':     ['badge-high',     '🟠'],
        'Critical': ['badge-critical', '🔴'],
    };
    const [cls, dot] = map[priority] || ['badge-low', '⚪'];
    return `<span class="badge-priority ${cls}">${dot} ${priority}</span>`;
}

// ─── Category icon ─────────────────────────────────────────────
function getCategoryIcon(category) {
    const map = {
        'Electrical':  'fas fa-bolt',
        'Network':     'fas fa-wifi',
        'Maintenance': 'fas fa-tools',
        'Plumbing':    'fas fa-tint',
        'Other':       'fas fa-box',
    };
    return `<i class="${map[category] || 'fas fa-tag'}" style="color:var(--primary);margin-right:4px;"></i>${category}`;
}

// ─── Session check (redirect to login if 401) ──────────────────
function checkSession(onSuccess) {
    $.ajax({
        url: '/SSRCMS/api/me.php',
        method: 'GET',
        success: function(res) {
            if (res.success && typeof onSuccess === 'function') onSuccess(res);
        },
        error: function(xhr) {
            if (xhr.status === 401) {
                window.location.href = 'index.php';
            }
        }
    });
}
