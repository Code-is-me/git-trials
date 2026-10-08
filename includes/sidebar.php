<?php
/**
 * NexusBrew - Admin Sidebar Include
 * Shared across all admin pages.
 *
 * @param string $activePage  Pass the current page slug, e.g. 'dashboard', 'products', 'categories'
 */
if (!isset($activePage)) $activePage = '';

// Determine base path dynamically
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
// Navigate up if in a subfolder
$root = $base;
?>

<!-- ─── Sidebar Overlay (mobile) ──────────────────────────── -->
<div id="sidebarOverlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none d-lg-none" style="z-index:99;display:none!important;"></div>

<!-- ─── Sidebar ──────────────────────────────────────────── -->
<aside id="adminSidebar" class="admin-sidebar">

    <!-- Brand -->
    <a href="<?= $root ?>/dashboard.php" class="sidebar-brand text-decoration-none">
        <div class="sidebar-brand-icon">☕</div>
        <div>
            <div class="sidebar-brand-text">NexusBrew</div>
            <div class="sidebar-brand-sub">Admin Panel</div>
        </div>
    </a>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <div class="sidebar-nav-label">Main</div>

        <a href="<?= $root ?>/dashboard.php"
           class="sidebar-nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <div class="nav-icon">
                <i class="bi bi-grid-1x2-fill"></i>
            </div>
            Dashboard
        </a>

        <div class="sidebar-nav-label">Catalog</div>

        <a href="<?= $root ?>/products.php"
           class="sidebar-nav-link <?= $activePage === 'products' ? 'active' : '' ?>">
            <div class="nav-icon">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            Products
        </a>

        <a href="<?= $root ?>/categories.php"
           class="sidebar-nav-link <?= $activePage === 'categories' ? 'active' : '' ?>">
            <div class="nav-icon">
                <i class="bi bi-tags-fill"></i>
            </div>
            Categories
        </a>

        <div class="sidebar-nav-label">Application</div>

        <a href="<?= $root ?>/index.php" target="_blank"
           class="sidebar-nav-link">
            <div class="nav-icon">
                <i class="bi bi-globe2"></i>
            </div>
            View Storefront
        </a>

    </nav>

    <!-- User Info & Logout -->
    <div class="sidebar-footer">
        <div class="sidebar-user-info">
            <div class="sidebar-user-avatar">A</div>
            <div>
                <div class="sidebar-user-name">Administrator</div>
                <div class="sidebar-user-email">admin@nexusbrew.local</div>
            </div>
        </div>
        <a href="<?= $root ?>/login.php?logout=1"
           class="sidebar-nav-link text-danger"
           style="color: var(--danger) !important; border: 1px solid rgba(231,76,60,0.2); background: rgba(231,76,60,0.06);">
            <div class="nav-icon" style="background:rgba(231,76,60,0.1); color:var(--danger);">
                <i class="bi bi-box-arrow-left"></i>
            </div>
            Logout
        </a>
    </div>

</aside>
