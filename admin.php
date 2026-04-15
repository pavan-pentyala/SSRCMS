<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
$adminName = htmlspecialchars($_SESSION['name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Dashboard — Manage all service complaints">
    <title>Admin Dashboard — ServiceDesk Pro</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6c63ff">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;"></div>

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
        <div class="user-avatar admin-avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
        <div class="user-info">
            <span class="user-name"><?= $adminName ?></span>
            <span class="user-badge user-badge-admin">Admin</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="#" class="nav-item active" id="navOverview" onclick="showAdminTab('overview',this)">
            <i class="fas fa-tachometer-alt"></i> Overview
        </a>
        <a href="#" class="nav-item" id="navComplaints" onclick="showAdminTab('complaints',this)">
            <i class="fas fa-list-alt"></i> All Complaints
        </a>
        <a href="#" class="nav-item" id="navCritical" onclick="showAdminTab('critical',this)">
            <i class="fas fa-exclamation-triangle"></i> Critical Tickets
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="nav-item nav-logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>

<!-- ─── MAIN CONTENT ──────────────────────────────────────────── -->
<div class="main-wrapper">

    <header class="topbar">
        <button class="topbar-toggle d-lg-none" onclick="openSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="topbar-title" id="adminPageTitle">Overview</div>
        <div class="topbar-right">
            <span class="poll-indicator">
                <i class="fas fa-circle" style="color:#00d4aa; font-size:8px;"></i> Live
            </span>
            <button class="btn btn-sm btn-outline-light ms-3" onclick="refreshAll()">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </header>

    <main class="main-content">

        <!-- ─── TAB: OVERVIEW ─────────────────────────────────── -->
        <div id="tabOverview" class="tab-pane fade-in">

            <!-- Stats cards -->
            <div class="row g-4 mb-4" id="statsRow">
                <div class="col-6 col-xl-3">
                    <div class="stat-card stat-total">
                        <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                        <div class="stat-value" id="statTotal">—</div>
                        <div class="stat-label">Total Complaints</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="stat-card stat-pending">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-value" id="statPending">—</div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="stat-card stat-progress">
                        <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                        <div class="stat-value" id="statProgress">—</div>
                        <div class="stat-label">In-Progress</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="stat-card stat-resolved">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-value" id="statResolved">—</div>
                        <div class="stat-label">Resolved</div>
                    </div>
                </div>
            </div>

            <!-- Recent complaints snapshot (last 5) -->
            <div class="glass-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Recent Complaints</h5>
                    <button class="btn btn-sm btn-ghost" onclick="showAdminTab('complaints', document.getElementById('navComplaints'))">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
                <div id="recentComplaintsBody">
                    <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
                </div>
            </div>
        </div>

        <!-- ─── TAB: ALL COMPLAINTS ───────────────────────────── -->
        <div id="tabComplaints" class="tab-pane d-none">

            <!-- Filters -->
            <div class="filter-bar mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <input type="text" id="adminSearch" class="form-control custom-inp-sm"
                               placeholder="🔍 Search title or user…" oninput="filterTable()">
                    </div>
                    <div class="col-md-3">
                        <select id="adminFilterStatus" class="form-select custom-inp-sm" onchange="filterTable()">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="In-Progress">In-Progress</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="adminFilterCat" class="form-select custom-inp-sm" onchange="filterTable()">
                            <option value="">All Categories</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Network">Network</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Plumbing">Plumbing</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <button class="btn btn-sm btn-ghost" onclick="loadAllComplaints()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <div class="glass-card p-0">
                <div class="table-responsive">
                    <table class="table admin-table mb-0" id="adminTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>User</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Manage</th>
                            </tr>
                        </thead>
                        <tbody id="adminTableBody">
                            <tr><td colspan="8" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin"></i> Loading…
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ─── TAB: CRITICAL TICKETS ────────────────────────── -->
        <div id="tabCritical" class="tab-pane d-none">
            <div class="filter-bar mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-white fw-bold"><i class="fas fa-fire me-2 text-danger"></i>Critical Tickets</h5>
                        <p class="text-white-50 small mb-0">Priority: Critical | Status: Pending or In-Progress</p>
                    </div>
                    <button class="btn btn-sm btn-ghost" onclick="loadCriticalTickets()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="glass-card p-0">
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>User</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Manage</th>
                            </tr>
                        </thead>
                        <tbody id="criticalTableBody">
                            <tr><td colspan="7" class="text-center py-4 text-muted">No high priority tickets.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-edit me-2"></i>Update Complaint
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="updateModalBody"></div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-grad" id="saveUpdateBtn" onclick="saveUpdate()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="js/app.js"></script>
<script src="js/admin.js"></script>
<script>
    function showAdminTab(tab, el) {
        $('.tab-pane').addClass('d-none').removeClass('fade-in');
        const titles = {overview:'Overview', complaints:'All Complaints', critical:'Critical Tickets'};
        $('#tab' + tab.charAt(0).toUpperCase() + tab.slice(1))
            .removeClass('d-none').addClass('fade-in');
        $('.nav-item').removeClass('active');
        if (el) $(el).addClass('active');
        document.getElementById('adminPageTitle').textContent = titles[tab] || 'Dashboard';
        if (tab === 'overview')    { loadStats(); loadRecentComplaints(); }
        if (tab === 'complaints')  loadAllComplaints();
        if (tab === 'critical')    loadCriticalTickets();
        closeSidebar();
    }
    function refreshAll() {
        const activeTab = $('.tab-pane:not(.d-none)').attr('id').replace('tab','').toLowerCase();
        showAdminTab(activeTab, null);
    }
    function openSidebar()  { $('#sidebar').addClass('open'); $('#sidebarOverlay').addClass('visible'); }
    function closeSidebar() { $('#sidebar').removeClass('open'); $('#sidebarOverlay').removeClass('visible'); }

    $(document).ready(function () {
        registerSW();
        initAdminPage();
        setInterval(function () {
            refreshAll();
        }, 30000);
    });
</script>
</body>
</html>
