/* ─── admin.js : Admin dashboard — complaints, charts, inline update ─ */

let allComplaints = [];
let updateModal = null;
let catChart = null, monthlyChart = null, statusChart = null;

// ─── Init ──────────────────────────────────────────────────────
function initAdminPage() {
    checkSession();
    updateModal = new bootstrap.Modal('#updateModal');
    loadStats();
    loadRecentComplaints();
}

// ─── Load Stats Cards ──────────────────────────────────────────
function loadStats() {
    $.ajax({
        url: '/SSRCMS/api/get_stats.php',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (!res.success) return;
            animateNumber('#statTotal', res.total);
            animateNumber('#statPending', res.byStatus['Pending'] || 0);
            animateNumber('#statProgress', res.byStatus['In-Progress'] || 0);
            animateNumber('#statResolved', res.byStatus['Resolved'] || 0);
        }
    });
}

function animateNumber(selector, target) {
    const el = $(selector);
    const cur = parseInt(el.text()) || 0;
    $({ n: cur }).animate({ n: target }, {
        duration: 600,
        step: function () { el.text(Math.round(this.n)); },
        complete: function () { el.text(target); }
    });
}

// ─── Recent Complaints (Overview tab, last 5) ──────────────────
function loadRecentComplaints() {
    $.ajax({
        url: '/SSRCMS/api/get_complaints.php',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (!res.success || !res.data.length) {
                $('#recentComplaintsBody').html('<div class="empty-state"><i class="fas fa-inbox"></i><p>No complaints yet.</p></div>');
                return;
            }
            const rows = res.data.slice(0, 5).map(c => `
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-3"
                     style="border-bottom:1px solid var(--border);">
                    <div>
                        <span class="fw-semibold" style="color:#fff;">#${c.id} ${escAdminHtml(c.title)}</span>
                        <div class="d-flex gap-2 mt-1 flex-wrap">
                            <span class="text-white-50" style="font-size: 0.85rem">${escAdminHtml(c.user_name || '—')}</span>
                            <span class="text-white" style="font-size: 0.85rem">${escAdminHtml(c.category)}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        ${getPriorityBadge(c.priority)}
                        ${getStatusBadge(c.status)}
                        <button class="action-btn" onclick="openUpdateModal(${c.id})">
                            <i class="fas fa-edit me-1"></i>Manage
                        </button>
                    </div>
                </div>`).join('');
            $('#recentComplaintsBody').html(`<div>${rows}</div>`);
        }
    });
}

// ─── Load All Complaints (Complaints tab) ──────────────────────
function loadAllComplaints() {
    $('#adminTableBody').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading…</td></tr>');

    $.ajax({
        url: '/SSRCMS/api/get_complaints.php',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (!res.success) {
                $('#adminTableBody').html('<tr><td colspan="8" class="text-center text-muted">Failed to load.</td></tr>');
                return;
            }
            allComplaints = res.data || [];
            renderAdminTable(allComplaints);
        },
        error: function () {
            $('#adminTableBody').html('<tr><td colspan="8" class="text-center text-muted">Connection error.</td></tr>');
        }
    });
}

function renderAdminTable(data) {
    if (!data.length) {
        $('#adminTableBody').html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-inbox me-2"></i>No complaints found.</td></tr>');
        return;
    }

    const rows = data.map(c => `
        <tr id="row_${c.id}">
            <td><span class="fw-semibold" style="color:var(--primary);">#${c.id}</span></td>
            <td style="max-width:200px;"><div class="text-truncate text-white fw-medium" title="${escAdminHtml(c.title)}">${escAdminHtml(c.title)}</div></td>
            <td>
                <div class="text-white">${escAdminHtml(c.user_name || '—')}</div>
                <span class="text-white-50" style="font-size: 0.85rem">${escAdminHtml(c.department || '')}</span>
            </td>
            <td><span class="text-white fw-medium">${getCategoryIcon(c.category)}</span></td>
            <td>${getPriorityBadge(c.priority)}</td>
            <td id="status_${c.id}">${getStatusBadge(c.status)}</td>
            <td><span class="text-white">${formatDate(c.created_at)}</span></td>
            <td><button class="action-btn" onclick="openUpdateModal(${c.id})"><i class="fas fa-edit me-1"></i>Manage</button></td>
        </tr>`).join('');

    $('#adminTableBody').html(rows);
}

// ─── Critical Tickets (Critical tab) ───────────────────────────
function loadCriticalTickets() {
    $('#criticalTableBody').html('<tr><td colspan="7" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading…</td></tr>');

    $.ajax({
        url: '/SSRCMS/api/get_complaints.php',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (!res.success) {
                $('#criticalTableBody').html('<tr><td colspan="7" class="text-center text-muted">Failed to load.</td></tr>');
                return;
            }
            allComplaints = res.data || [];
            // Filter: Priority=Critical AND (Status=Pending OR Status=In-Progress)
            const criticals = allComplaints.filter(c =>
                c.priority === 'Critical' && (c.status === 'Pending' || c.status === 'In-Progress')
            );
            renderCriticalTable(criticals);
        },
        error: function () {
            $('#criticalTableBody').html('<tr><td colspan="7" class="text-center text-muted">Connection error.</td></tr>');
        }
    });
}

function renderCriticalTable(data) {
    if (!data.length) {
        $('#criticalTableBody').html('<tr><td colspan="7" class="text-center py-5 text-white"><i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>No unresolved critical tickets. Good job!</td></tr>');
        return;
    }

    const rows = data.map(c => `
        <tr id="row_${c.id}">
            <td><span class="fw-semibold" style="color:var(--danger);">#${c.id}</span></td>
            <td style="max-width:200px;"><div class="text-truncate text-white fw-bold" title="${escAdminHtml(c.title)}">${escAdminHtml(c.title)}</div></td>
            <td>
                <div class="text-white">${escAdminHtml(c.user_name || '—')}</div>
                <span class="text-white-50" style="font-size: 0.82rem">${escAdminHtml(c.department || '')}</span>
            </td>
            <td><span class="text-white fw-medium">${getCategoryIcon(c.category)}</span></td>
            <td id="status_${c.id}">${getStatusBadge(c.status)}</td>
            <td><span class="text-white">${formatDate(c.created_at)}</span></td>
            <td><button class="action-btn" onclick="openUpdateModal(${c.id})"><i class="fas fa-edit me-1"></i>Manage</button></td>
        </tr>`).join('');

    $('#criticalTableBody').html(rows);
}

// ─── Client-side Filter ────────────────────────────────────────
function filterTable() {
    const search = $('#adminSearch').val().toLowerCase();
    const status = $('#adminFilterStatus').val();
    const cat = $('#adminFilterCat').val();

    const filtered = allComplaints.filter(c => {
        const matchText = !search ||
            c.title.toLowerCase().includes(search) ||
            (c.user_name && c.user_name.toLowerCase().includes(search));
        const matchStatus = !status || c.status === status;
        const matchCat = !cat || c.category === cat;
        return matchText && matchStatus && matchCat;
    });

    renderAdminTable(filtered);
}

// ─── Open Update Modal ─────────────────────────────────────────
function openUpdateModal(id) {
    const c = allComplaints.find(x => x.id == id);
    if (!c) { loadAllComplaints(); showToast('Complaint not found, refreshing…', 'info'); return; }

    const body = `
        <input type="hidden" id="editId" value="${c.id}">
        <div class="mb-3">
            <h6 class="text-white fw-bold mb-1" style="font-size:1.1rem;">#${c.id} — ${escAdminHtml(c.title)}</h6>
            <div class="d-flex gap-3 flex-wrap">
                ${getPriorityBadge(c.priority)}
                <span class="text-white" style="font-size: 0.88rem; opacity: 0.85;">Submitted by: <strong class="text-white">${escAdminHtml(c.user_name || '—')}</strong></span>
                <span class="text-white" style="font-size: 0.88rem; opacity: 0.85;">on ${formatDate(c.created_at)}</span>
            </div>
        </div>
        ${c.description ? `<div class="p-3 mb-3 rounded" style="background:rgba(255,255,255,0.04); border:1px solid var(--border);">
            <p class="text-white mb-0" style="font-size: 0.92rem; line-height: 1.5; opacity: 0.9;">${escAdminHtml(c.description)}</p></div>` : ''}
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-medium">Update Status</label>
                <select id="editStatus" class="form-select custom-inp">
                    ${['Pending', 'In-Progress', 'Resolved', 'Closed'].map(s =>
        `<option value="${s}" ${c.status === s ? 'selected' : ''}>${s}</option>`
    ).join('')}
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Assign To</label>
                <div class="custom-input-wrap">
                    <i class="fas fa-user-cog input-icon-left"></i>
                    <input type="text" id="editAssigned" class="form-control custom-inp"
                           value="${escAdminHtml(c.assigned_to || '')}" placeholder="Technician name">
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-medium">Admin Notes</label>
                <textarea id="editNotes" rows="3" class="form-control custom-inp"
                          placeholder="Add remarks, resolution steps…">${escAdminHtml(c.admin_notes || '')}</textarea>
            </div>
        </div>`;

    $('#updateModalBody').html(body);
    updateModal.show();
}

// ─── Save Update (AJAX — no page reload) ──────────────────────
function saveUpdate() {
    const id = $('#editId').val();
    const status = $('#editStatus').val();
    const assignedTo = $('#editAssigned').val().trim();
    const adminNotes = $('#editNotes').val().trim();

    $('#saveUpdateBtn').prop('disabled', true)
        .html('<i class="fas fa-spinner fa-spin me-1"></i> Saving…');

    $.ajax({
        url: '/SSRCMS/api/update_status.php',
        type: 'POST',
        data: { complaint_id: id, status, assigned_to: assignedTo, admin_notes: adminNotes },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                showToast('Complaint #' + id + ' updated to ' + status + '.', 'success');
                updateModal.hide();

                // Update local cache
                const idx = allComplaints.findIndex(c => c.id == id);
                if (idx !== -1) {
                    allComplaints[idx].status = status;
                    allComplaints[idx].assigned_to = assignedTo;
                    allComplaints[idx].admin_notes = adminNotes;
                }

                // Animate the updated row in place (no reload)
                const row = $('#row_' + id);
                if (row.length) {
                    $(`#status_${id}`).html(getStatusBadge(status));
                    row.addClass('row-updated');
                    setTimeout(() => row.removeClass('row-updated'), 1000);
                }

                // Refresh overview stats and critical list (if viewing it)
                loadStats();
                if ($('#tabCritical').is(':visible')) loadCriticalTickets();
                if ($('#tabComplaints').is(':visible')) loadAllComplaints();
            } else {
                showToast(res.message || 'Update failed.', 'error');
            }
            $('#saveUpdateBtn').prop('disabled', false)
                .html('<i class="fas fa-save me-1"></i> Save Changes');
        },
        error: function () {
            showToast('Server error. Try again.', 'error');
            $('#saveUpdateBtn').prop('disabled', false)
                .html('<i class="fas fa-save me-1"></i> Save Changes');
        }
    });
}

// Charts removed (Analytics tab deleted)
// loadCharts, renderCategoryChart, renderStatusChart, renderMonthlyChart functions deleted

// ─── HTML escape (admin context) ──────────────────────────────
function escAdminHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
