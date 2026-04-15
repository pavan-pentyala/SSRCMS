/* ─── complaints.js : User dashboard — submit & track complaints ─ */

// ─── Init ──────────────────────────────────────────────────────
function initComplaintsPage() {
    checkSession();
    initSubmitForm();
}

// ─── Submit Complaint Form ─────────────────────────────────────
function initSubmitForm() {

    // Real-time title validation
    $('#cTitle').on('blur', function() {
        const v = $(this).val().trim();
        if (!v) {
            $(this).addClass('is-invalid');
            $('#titleErr').text('Complaint title is required.');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $('#titleErr').text('');
        }
    });

    $('#cCategory').on('change', function() {
        if (!$(this).val()) {
            $(this).addClass('is-invalid');
            $('#catErr').text('Please select a category.');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $('#catErr').text('');
        }
    });

    // Form submit
    $('#complaintForm').on('submit', function(e) {
        e.preventDefault();

        const title       = $('#cTitle').val().trim();
        const category    = $('#cCategory').val();
        const priority    = $('#cPriority').val();
        const description = $('#cDesc').val().trim();

        let valid = true;
        if (!title) {
            $('#cTitle').addClass('is-invalid'); $('#titleErr').text('Title is required.'); valid = false;
        }
        if (!category) {
            $('#cCategory').addClass('is-invalid'); $('#catErr').text('Select a category.'); valid = false;
        }
        if (!valid) return;

        $('#submitBtn .btn-label').addClass('d-none');
        $('#submitBtn .btn-spin').removeClass('d-none');
        $('#submitBtn').prop('disabled', true);

        $.ajax({
            url:  '/SSRCMS/api/submit_complaint.php',
            type: 'POST',
            data: { title, category, priority, description },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast('Complaint #' + res.complaint_id + ' submitted successfully!', 'success');
                    $('#complaintForm')[0].reset();
                    $('#cTitle, #cCategory, #cPriority, #cDesc').removeClass('is-valid is-invalid');
                    // Switch to My Complaints and load
                    setTimeout(() => {
                        showTab('mycomplaints', document.getElementById('navMyComp'));
                    }, 1000);
                } else {
                    showToast(res.message || 'Failed to submit complaint.', 'error');
                }
                resetSubmitBtn();
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Server error.', 'error');
                resetSubmitBtn();
            }
        });
    });

    function resetSubmitBtn() {
        $('#submitBtn .btn-label').removeClass('d-none');
        $('#submitBtn .btn-spin').addClass('d-none');
        $('#submitBtn').prop('disabled', false);
    }
}

// ─── Load My Complaints ────────────────────────────────────────
function loadMyComplaints(showLoader) {
    const container = $('#myComplaintsContainer');
    const status    = $('#filterStatus').val();
    const category  = $('#filterCat').val();

    if (showLoader) {
        container.html('<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><br>Loading…</div>');
    }

    $.ajax({
        url:      '/SSRCMS/api/get_complaints.php',
        type:     'GET',
        data:     { status, category },
        dataType: 'json',
        success: function(res) {
            if (!res.success) {
                container.html('<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Failed to load complaints.</p></div>');
                return;
            }
            const data = res.data;
            $('#lastRefresh').text('Updated ' + new Date().toLocaleTimeString('en-IN'));

            if (!data || data.length === 0) {
                container.html(`
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No complaints found. Submit your first complaint!</p>
                    </div>`);
                $('#pendingBadge').hide();
                return;
            }

            // Update pending badge count
            const pendingCount = data.filter(c => c.status === 'Pending').length;
            if (pendingCount > 0) {
                $('#pendingBadge').text(pendingCount).show();
            } else {
                $('#pendingBadge').hide();
            }

            const html = data.map(c => buildComplaintCard(c)).join('');
            container.html(html);
        },
        error: function() {
            container.html('<div class="empty-state"><i class="fas fa-wifi"></i><p>Connection error. Retrying…</p></div>');
        }
    });
}

// ─── Build Complaint Card ──────────────────────────────────────
function buildComplaintCard(c) {
    const cardClass = 'card-' + c.status.toLowerCase().replace(' ', '-');
    const desc = c.description ? `<p class="complaint-card-desc mb-0">${escHtml(c.description)}</p>` : '';
    const adminNotes = c.admin_notes
        ? `<div class="mt-2 p-2 rounded" style="background:rgba(108,99,255,0.08); border:1px solid rgba(108,99,255,0.2);">
               <small><strong style="color:var(--primary);">Admin Notes:</strong> ${escHtml(c.admin_notes)}</small>
           </div>` : '';
    const assignedTo = c.assigned_to
        ? `<span class="complaint-card-meta"><i class="fas fa-user-cog me-1"></i>${escHtml(c.assigned_to)}</span>` : '';

    return `
    <div class="complaint-card ${cardClass}" onclick="toggleCard(this)">
        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
            <div>
                <div class="complaint-card-title">#${c.id} — ${escHtml(c.title)}</div>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    <span class="complaint-card-meta"><i class="fas fa-tag me-1"></i>${escHtml(c.category)}</span>
                    ${assignedTo}
                    <span class="complaint-card-meta"><i class="fas fa-clock me-1"></i>${formatDate(c.created_at)}</span>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                ${getPriorityBadge(c.priority)}
                ${getStatusBadge(c.status)}
            </div>
        </div>
        ${desc}
        ${adminNotes}
    </div>`;
}

// ─── Toggle card expand ────────────────────────────────────────
function toggleCard(el) {
    $(el).toggleClass('expanded');
}

// ─── HTML escape ───────────────────────────────────────────────
function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
