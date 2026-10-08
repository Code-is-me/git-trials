<?php
/**
 * NexusBrew - Admin Dashboard
 *
 * VULNERABILITIES ON THIS PAGE:
 * - No security headers (CSP, X-Frame-Options, X-Content-Type-Options)
 *   CWE-693 | Detected by: Nuclei
 * - Session not properly validated (no regeneration, no fingerprinting)
 *   CWE-384 | Session Fixation
 */

session_start();

// VULNERABILITY: Simple session check — no regeneration, no token, no fingerprinting
// A secure implementation would use session_regenerate_id() and store user agent
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$activePage = 'dashboard';
$successMsg = $_GET['success'] ?? '';
$errorMsg   = $_GET['error']   ?? '';

// ── Stats Queries ────────────────────────────────────────────
// These use direct queries with no user input — safe for this page
$totalProducts   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM products"))['cnt']   ?? 0;
$totalCategories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM categories"))['cnt'] ?? 0;
$totalStock      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stock) as cnt FROM products"))['cnt']  ?? 0;
$totalValue      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(price * stock) as val FROM products"))['val'] ?? 0;

// ── Recent Products ──────────────────────────────────────────
$recentResult = mysqli_query($conn, "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 5");
$recentProducts = [];
while ($row = mysqli_fetch_assoc($recentResult)) {
    $recentProducts[] = $row;
}

// ── Low Stock Products ───────────────────────────────────────
$lowStockResult = mysqli_query($conn, "SELECT * FROM products WHERE stock < 20 ORDER BY stock ASC LIMIT 5");
$lowStock = [];
while ($row = mysqli_fetch_assoc($lowStockResult)) {
    $lowStock[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — NexusBrew Admin</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="admin-layout">

    <!-- ── Sidebar ─────────────────────────────────────────── -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- ── Main Content ────────────────────────────────────── -->
    <main class="admin-main">

        <!-- Topbar -->
        <div class="admin-topbar">
            <div>
                <div class="topbar-title">Dashboard</div>
                <div class="topbar-breadcrumb">
                    <span>Home</span> / Overview
                </div>
            </div>
            <div class="topbar-actions">
                <button class="btn-action view d-lg-none" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <a href="products.php?action=add" class="btn-gold" id="addProductQuick"
                   style="padding:0.5rem 1.25rem;font-size:0.85rem;">
                    <i class="bi bi-plus-lg me-1"></i> Add Product
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="admin-content">

            <!-- Alerts -->
            <?php if ($successMsg): ?>
                <div class="alert-success auto-dismiss mb-4">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= htmlspecialchars($successMsg) ?>
                </div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="alert-error auto-dismiss mb-4">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <!-- Welcome Banner -->
            <div class="admin-card mb-4" style="border-color:rgba(200,145,79,0.25);background:linear-gradient(135deg,rgba(200,145,79,0.08),transparent);">
                <div class="admin-card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 style="font-family:'Playfair Display',serif;color:var(--cream);margin:0 0 4px;">
                            Good <?= (date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening')) ?>, Administrator ☕
                        </h4>
                        <p style="margin:0;font-size:0.85rem;color:var(--cream-muted);">
                            Here's what's happening with NexusBrew today — <?= date('l, F j, Y') ?>
                        </p>
                    </div>
                    <div class="vuln-notice" style="max-width:360px;font-size:0.72rem;margin-bottom:0;">
                        <strong>⚠ DevSecOps Demo — Active Vulnerabilities:</strong>
                        Hard-coded credentials · SQL Injection · XSS · Missing Headers · Outdated Deps
                    </div>
                </div>
            </div>

            <!-- ── Stat Cards ──────────────────────────────── -->
            <div class="row g-4 mb-4">

                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon gold"><i class="bi bi-box-seam-fill"></i></div>
                        <div>
                            <div class="stat-number" data-count="<?= $totalProducts ?>"><?= $totalProducts ?></div>
                            <div class="stat-label">Total Products</div>
                            <div class="stat-change up"><i class="bi bi-arrow-up me-1"></i>Active catalog items</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon success"><i class="bi bi-tags-fill"></i></div>
                        <div>
                            <div class="stat-number" data-count="<?= $totalCategories ?>"><?= $totalCategories ?></div>
                            <div class="stat-label">Categories</div>
                            <div class="stat-change up"><i class="bi bi-check2-circle me-1"></i>All active</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon info"><i class="bi bi-archive-fill"></i></div>
                        <div>
                            <div class="stat-number" data-count="<?= $totalStock ?>"><?= number_format($totalStock) ?></div>
                            <div class="stat-label">Total Inventory</div>
                            <div class="stat-change <?= count($lowStock) > 0 ? 'down' : 'up' ?>">
                                <?php if (count($lowStock) > 0): ?>
                                    <i class="bi bi-exclamation-triangle me-1"></i><?= count($lowStock) ?> low stock items
                                <?php else: ?>
                                    <i class="bi bi-check2-circle me-1"></i>All well-stocked
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon amber"><i class="bi bi-currency-dollar"></i></div>
                        <div>
                            <div class="stat-number" style="font-size:1.6rem;">
                                Rp <?= number_format($totalValue / 1000, 1) ?>K
                            </div>
                            <div class="stat-label">Inventory Value</div>
                            <div class="stat-change up"><i class="bi bi-graph-up me-1"></i>Total stock value</div>
                        </div>
                    </div>
                </div>

            </div><!-- /stat cards -->

            <!-- ── Recent Products & Low Stock ────────────── -->
            <div class="row g-4">

                <!-- Recent Products -->
                <div class="col-lg-7">
                    <div class="admin-card h-100">
                        <div class="admin-card-header">
                            <div class="admin-card-title">
                                <i class="bi bi-clock-history"></i>
                                Recent Products
                            </div>
                            <a href="products.php" class="btn-action view" id="viewAllProducts" title="View all">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="nb-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentProducts)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center" style="color:var(--text-muted);padding:2rem;">
                                                No products found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentProducts as $p): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img
                                                            src="<?= htmlspecialchars($p['image'] ?? '') ?>"
                                                            alt="<?= htmlspecialchars($p['name']) ?>"
                                                            class="product-thumb"
                                                            onerror="this.src='https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=100'"
                                                        >
                                                        <span style="font-weight:500;"><?= htmlspecialchars($p['name']) ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="nb-badge nb-badge-gold">
                                                        <?= htmlspecialchars($p['category_name'] ?? '—') ?>
                                                    </span>
                                                </td>
                                                <td style="color:var(--gold);font-weight:600;">
                                                    Rp <?= number_format($p['price'], 0, ',', '.') ?>
                                                </td>
                                                <td>
                                                    <span class="nb-badge <?= $p['stock'] < 20 ? 'nb-badge-danger' : 'nb-badge-success' ?>">
                                                        <?= $p['stock'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <a href="edit_product.php?id=<?= $p['id'] ?>"
                                                           class="btn-action edit" title="Edit"
                                                           id="dashEditProduct<?= $p['id'] ?>">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </a>
                                                        <form method="post"
                                                              action="delete_product.php"
                                                              class="m-0"
                                                              data-confirm="Delete '<?= htmlspecialchars($p['name']) ?>'?"
                                                              id="dashDeleteProductForm<?= $p['id'] ?>">
                                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                            <input type="hidden" name="return_to" value="dashboard.php">
                                                            <button type="submit"
                                                                    class="btn-action delete"
                                                                    title="Delete"
                                                                    id="dashDeleteProduct<?= $p['id'] ?>">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!-- /recent products -->

                <!-- Low Stock & Quick Actions -->
                <div class="col-lg-5">

                    <!-- Low Stock Alert -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <div class="admin-card-title">
                                <i class="bi bi-exclamation-triangle" style="color:var(--warning);"></i>
                                Low Stock Alerts
                            </div>
                        </div>
                        <div class="admin-card-body p-0">
                            <?php if (empty($lowStock)): ?>
                                <div class="empty-state py-4">
                                    <div class="empty-state-icon"><i class="bi bi-check-circle" style="color:var(--success);"></i></div>
                                    <div class="empty-state-title">All items well-stocked!</div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($lowStock as $item): ?>
                                    <div class="d-flex align-items-center justify-content-between px-4 py-3"
                                         style="border-bottom:1px solid var(--border);">
                                        <div style="font-size:0.85rem;font-weight:500;color:var(--cream);">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </div>
                                        <span class="nb-badge nb-badge-danger">
                                            <?= $item['stock'] ?> left
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div class="admin-card-title">
                                <i class="bi bi-lightning-charge-fill"></i>
                                Quick Actions
                            </div>
                        </div>
                        <div class="admin-card-body d-flex flex-column gap-2">
                            <a href="add_product.php" class="btn-gold w-100" style="justify-content:center;" id="quickAddProduct">
                                <i class="bi bi-plus-circle me-2"></i> Add New Product
                            </a>
                            <a href="categories.php" class="btn-outline-gold w-100" style="justify-content:center;" id="quickManageCats">
                                <i class="bi bi-tags me-2"></i> Manage Categories
                            </a>
                            <a href="products.php" class="btn-outline-gold w-100" style="justify-content:center;" id="quickViewProducts">
                                <i class="bi bi-list-ul me-2"></i> View All Products
                            </a>
                        </div>
                    </div>

                </div><!-- /right col -->

            </div><!-- /row -->

        </div><!-- /admin-content -->

    </main><!-- /admin-main -->
</div><!-- /admin-layout -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
