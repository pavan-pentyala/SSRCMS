<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
if ($_SESSION['role'] === 'admin') {
    header('Location: admin.php');
    exit;
}
$userName = htmlspecialchars($_SESSION['name']);
$userId   = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="User Dashboard — Submit and track your service complaints">
    <title>My Dashboard — ServiceDesk Pro</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6c63ff">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;"></div>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ─── SIDEBAR ─────────────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-headset"></i>
            <span>ServiceDesk</span>
        </div>
        <button class="sidebar-close d-lg-none" onclick="closeSidebar()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
        <div class="user-info">
            <span class="user-name"><?= $userName ?></span>
            <span class="user-badge user-badge-user">User</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="#" class="nav-item active" id="navSubmit" onclick="showTab('submit',this)">
            <i class="fas fa-plus-circle"></i> Submit Complaint
        </a>
        <a href="#" class="nav-item" id="navMyComp" onclick="showTab('mycomplaints',this)">
            <i class="fas fa-list-alt"></i> My Complaints
            <span class="nav-badge" id="pendingBadge" style="display:none;"></span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="nav-item nav-logout" id="logoutBtn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>

<!-- ─── MAIN CONTENT ──────────────────────────────────────────── -->
<div class="main-wrapper">

    <!-- Top bar -->
    <header class="topbar">
        <button class="topbar-toggle d-lg-none" onclick="openSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="topbar-title" id="pageTitle">Submit a Complaint</div>
        <div class="topbar-right">
            <span class="poll-indicator" id="pollDot" title="Auto-refreshing every 30s">
                <i class="fas fa-circle" style="color:#00d4aa; font-size:8px;"></i> Live
            </span>
            <button class="btn btn-sm btn-outline-light ms-3" onclick="loadMyComplaints(true)">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </header>

    <main class="main-content">

        <!-- ─── TAB: SUBMIT COMPLAINT ──────────────────────────── -->
        <div id="tabSubmit" class="tab-pane fade-in">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="glass-card">
                        <div class="card-header-custom">
                            <h5><i class="fas fa-file-alt me-2"></i>New Service Request</h5>
                            <p class="card-subtext">Describe your issue and we'll assign it to the right team</p>
                        </div>
                        <form id="complaintForm" novalidate>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-medium" for="cTitle">Complaint Title *</label>
                                    <input type="text" id="cTitle" name="title"
                                           class="form-control custom-inp"
                                           placeholder="Brief summary of the issue" required>
                                    <div class="field-error" id="titleErr"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-medium" for="cCategory">Category *</label>
                                    <select id="cCategory" name="category" class="form-select custom-inp" required>
                                        <option value="">Select category…</option>
                                        <option value="Electrical">⚡ Electrical</option>
                                        <option value="Network">🌐 Network</option>
                                        <option value="Maintenance">🔧 Maintenance</option>
                                        <option value="Plumbing">🚰 Plumbing</option>
                                        <option value="Other">📦 Other</option>
                                    </select>
                                    <div class="field-error" id="catErr"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-medium" for="cPriority">Priority *</label>
                                    <select id="cPriority" name="priority" class="form-select custom-inp" required>
                                        <option value="Low">🟢 Low</option>
                                        <option value="Medium" selected>🟡 Medium</option>
                                        <option value="High">🟠 High</option>
                                        <option value="Critical">🔴 Critical</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-medium" for="cDesc">Description</label>
                                    <textarea id="cDesc" name="description" rows="4"
                                              class="form-control custom-inp"
                                              placeholder="Provide any additional details, location, time of issue…"></textarea>
                                </div>
                            </div>

                            <div class="d-flex gap-3 mt-4">
                                <button type="submit" class="btn btn-grad" id="submitBtn">
                                    <span class="btn-label"><i class="fas fa-paper-plane me-2"></i>Submit Complaint</span>
                                    <span class="btn-spin d-none"><i class="fas fa-spinner fa-spin"></i> Submitting…</span>
                                </button>
                                <button type="reset" class="btn btn-ghost">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- /#tabSubmit -->

        <!-- ─── TAB: MY COMPLAINTS ─────────────────────────────── -->
        <div id="tabMycomplaints" class="tab-pane d-none">

            <!-- Filter bar -->
            <div class="filter-bar mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <select id="filterStatus" class="form-select custom-inp-sm" onchange="loadMyComplaints()">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="In-Progress">In-Progress</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="filterCat" class="form-select custom-inp-sm" onchange="loadMyComplaints()">
                            <option value="">All Categories</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Network">Network</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Plumbing">Plumbing</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <small class="text-muted" id="lastRefresh"></small>
                    </div>
                </div>
            </div>

            <!-- Complaints table / cards -->
            <div id="myComplaintsContainer">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3"></i><br>Loading your complaints…
                </div>
            </div>

        </div><!-- /#tabMycomplaints -->

    </main>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-white">Complaint Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailModalBody"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
<script src="js/complaints.js"></script>
<script>
    function showTab(tab, el) {
        $('.tab-pane').addClass('d-none').removeClass('fade-in');
        $('#tab' + tab.charAt(0).toUpperCase() + tab.slice(1))
            .removeClass('d-none').addClass('fade-in');
        $('.nav-item').removeClass('active');
        if (el) $(el).addClass('active');
        document.getElementById('pageTitle').textContent =
            tab === 'submit' ? 'Submit a Complaint' : 'My Complaints';
        if (tab === 'mycomplaints') loadMyComplaints();
        closeSidebar();
    }
    function openSidebar()  { $('#sidebar').addClass('open'); $('#sidebarOverlay').addClass('visible'); }
    function closeSidebar() { $('#sidebar').removeClass('open'); $('#sidebarOverlay').removeClass('visible'); }

    $(document).ready(function () {
        registerSW();
        initComplaintsPage();
        // Poll every 30 seconds
        setInterval(function () {
            if (!$('#tabMycomplaints').hasClass('d-none')) loadMyComplaints();
        }, 30000);
    });
</script>
</body>
</html>
