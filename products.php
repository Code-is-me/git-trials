<?php
/**
 * NexusBrew - Products List Page
 *
 * ┌──────────────────────────────────────────────────────────────┐
 * │ INTENTIONAL VULNERABILITIES ON THIS PAGE:                    │
 * │                                                              │
 * │ 1. SQL Injection (CWE-89)                                    │
 * │    Tool: Semgrep                                             │
 * │    The search query directly concatenates $_GET['search']    │
 * │    into the SQL string without prepared statements.          │
 * │                                                              │
 * │ 2. Cross-Site Scripting (XSS) (CWE-79)                      │
 * │    Tool: Semgrep                                             │
 * │    The search term is echoed back without sanitization:      │
 * │    echo $_GET['search']                                      │
 * │                                                              │
 * │ 3. Missing Security Headers (CWE-693)                        │
 * │    Tool: Nuclei                                              │
 * │    No CSP, X-Frame-Options, X-Content-Type-Options set       │
 * └──────────────────────────────────────────────────────────────┘
 */

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$activePage = 'products';

// ── Flash messages ───────────────────────────────────────────
$successMsg = $_GET['success'] ?? '';
$errorMsg   = $_GET['error']   ?? '';

// ── Search ───────────────────────────────────────────────────
// VULNERABILITY: SQL Injection
// CWE-89: Improper Neutralization of Special Elements used in SQL Command
// Tool: Semgrep
//
// The $search variable is taken directly from user input ($_GET['search'])
// and concatenated into a raw SQL query string without:
//   - Prepared statements / parameterized queries
//   - Escaping via mysqli_real_escape_string()
//   - Any input sanitization or validation
//
// Example attack payload: ' OR '1'='1
// Attack URL: products.php?search=' OR '1'='1
//
// Fix (secure): Use prepared statements:
//   $stmt = $conn->prepare("SELECT ... WHERE name LIKE ?");
//   $stmt->bind_param("s", $searchParam);
//
$search = $_GET['search'] ?? '';

if (!empty($search)) {
    // ══════════════════════════════════════════════════════════
    // [VULN] SQL INJECTION — User input directly in SQL string
    // Semgrep rule: php.lang.security.injection.tainted-sql-string
    // ══════════════════════════════════════════════════════════
    $sql = "SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE '%" . $search . "%'
            ORDER BY p.id DESC";
} else {
    $sql = "SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.id DESC";
}

$result   = mysqli_query($conn, $sql);
$products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — NexusBrew Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="admin-layout">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="admin-main">

        <!-- Topbar -->
        <div class="admin-topbar">
            <div>
                <div class="topbar-title">Products</div>
                <div class="topbar-breadcrumb">
                    <span>Catalog</span> / All Products
                </div>
            </div>
            <div class="topbar-actions">
                <button class="btn-action view d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <a href="add_product.php" class="btn-gold" id="addProductBtn"
                   style="padding:0.5rem 1.25rem;font-size:0.85rem;">
                    <i class="bi bi-plus-lg me-1"></i> Add Product
                </a>
            </div>
        </div>

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

            <!-- Products Card -->
            <div class="admin-card">

                <!-- Card Header with Search -->
                <div class="admin-card-header flex-wrap gap-3">
                    <div class="admin-card-title">
                        <i class="bi bi-box-seam-fill"></i>
                        All Products
                        <span class="nb-badge nb-badge-gold ms-2"><?= count($products) ?></span>
                    </div>

                    <!-- Search Form -->
                    <!-- ══════════════════════════════════════════════════════ -->
                    <!-- VULNERABILITY: XSS — Unsanitized user input displayed -->
                    <!-- CWE-79: Improper Neutralization of Input During        -->
                    <!--         Web Page Generation                            -->
                    <!-- Tool: Semgrep                                          -->
                    <!-- The value of $_GET['search'] is echoed back to the    -->
                    <!-- user without htmlspecialchars() or any encoding.       -->
                    <!-- Fix: Use htmlspecialchars($_GET['search'])             -->
                    <!-- ══════════════════════════════════════════════════════ -->
                    <form method="GET" action="products.php" class="d-flex align-items-center gap-2">
                        <div class="search-container">
                            <i class="bi bi-search search-icon"></i>
                            <input
                                type="text"
                                id="searchInput"
                                name="search"
                                class="search-input"
                                placeholder="Search products..."
                                value="<?= $search /* [VULN] XSS: No htmlspecialchars() */ ?>"
                            >
                        </div>
                        <button type="submit" class="btn-action view" title="Search" id="searchBtn">
                            <i class="bi bi-search"></i>
                        </button>
                        <?php if ($search): ?>
                            <a href="products.php" class="btn-action delete" title="Clear search" id="clearSearchBtn">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </form>

                </div><!-- /card header -->

                <!-- Search Result Label -->
                <?php if ($search): ?>
                    <div style="padding:0.75rem 1.5rem;background:rgba(200,145,79,0.05);border-bottom:1px solid var(--border);font-size:0.82rem;color:var(--cream-muted);">
                        <!-- [VULN] XSS: $search printed without escaping below -->
                        Showing results for: <strong style="color:var(--gold);"><?= $search /* [VULN] XSS */ ?></strong>
                        &nbsp;·&nbsp; <?= count($products) ?> result(s) found
                    </div>
                <?php endif; ?>

                <!-- Table -->
                <div style="overflow-x:auto;">
                    <table class="nb-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th style="width:70px;">Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th style="width:100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="bi bi-search"></i>
                                            </div>
                                            <div class="empty-state-title">No products found</div>
                                            <p style="font-size:0.82rem;margin-top:0.5rem;">
                                                <?= $search ? 'Try a different search term.' : 'Add your first product to get started.' ?>
                                            </p>
                                            <?php if (!$search): ?>
                                                <a href="add_product.php" class="btn-gold mt-3" style="padding:0.6rem 1.5rem;font-size:0.85rem;">
                                                    <i class="bi bi-plus-lg me-1"></i> Add First Product
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $i => $p): ?>
                                    <tr>
                                        <td style="color:var(--text-muted);font-size:0.8rem;"><?= $i + 1 ?></td>
                                        <td>
                                            <img
                                                src="<?= htmlspecialchars($p['image'] ?? '') ?>"
                                                alt="<?= htmlspecialchars($p['name']) ?>"
                                                class="product-thumb"
                                                onerror="this.src='https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=100'"
                                            >
                                        </td>
                                        <td>
                                            <div style="font-weight:500;"><?= htmlspecialchars($p['name']) ?></div>
                                            <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                <?= htmlspecialchars(substr($p['description'] ?? '', 0, 60)) ?>...
                                            </div>
                                        </td>
                                        <td>
                                            <span class="nb-badge nb-badge-gold">
                                                <?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?>
                                            </span>
                                        </td>
                                        <td style="font-weight:600;color:var(--gold);">
                                            Rp <?= number_format($p['price'], 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <span class="nb-badge <?= $p['stock'] < 20 ? 'nb-badge-danger' : ($p['stock'] < 50 ? 'nb-badge-info' : 'nb-badge-success') ?>">
                                                <?= $p['stock'] ?> units
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 align-items-center">
                                                <a href="edit_product.php?id=<?= $p['id'] ?>"
                                                   class="btn-action edit"
                                                   title="Edit product"
                                                   data-bs-toggle="tooltip"
                                                   id="editProduct<?= $p['id'] ?>">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <form method="post"
                                                      action="delete_product.php"
                                                      class="m-0"
                                                      data-confirm="Are you sure you want to delete '<?= htmlspecialchars($p['name']) ?>'? This action cannot be undone."
                                                      id="deleteProductForm<?= $p['id'] ?>">
                                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                    <input type="hidden" name="return_to" value="products.php">
                                                    <button type="submit"
                                                            class="btn-action delete"
                                                            title="Delete product"
                                                            id="deleteProduct<?= $p['id'] ?>">
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
                </div><!-- /table -->

            </div><!-- /admin-card -->

        </div><!-- /admin-content -->

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
