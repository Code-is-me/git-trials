<?php
/**
 * NexusBrew - Categories CRUD Page
 *
 * VULNERABILITIES ON THIS PAGE:
 *
 * 1. SQL Injection (CWE-89) — Semgrep
 *    GET ?delete_id= and ?edit_id= concatenated directly into SQL
 *
 * 2. XSS (CWE-79) — Semgrep
 *    Category name echoed back from POST without full sanitization
 *    on inline edit form
 *
 * 3. No CSRF Protection (CWE-352)
 *    All forms lack CSRF tokens
 *
 * 4. Missing Security Headers (CWE-693) — Nuclei
 */

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$activePage = 'categories';
$successMsg = '';
$errorMsg   = '';
$editCat    = null;

// ── DELETE ───────────────────────────────────────────────────
if (isset($_GET['delete_id'])) {
    // [VULN] SQL Injection — delete_id from GET
    $delId     = $_GET['delete_id'];
    $checkSql  = "SELECT id, name FROM categories WHERE id = " . $delId; // <-- SQLi
    $checkRes  = mysqli_query($conn, $checkSql);

    if ($checkRes && mysqli_num_rows($checkRes) > 0) {
        $cat = mysqli_fetch_assoc($checkRes);
        $deleteSql = "DELETE FROM categories WHERE id = " . $delId; // <-- SQLi
        if (mysqli_query($conn, $deleteSql)) {
            $successMsg = 'Category "' . htmlspecialchars($cat['name']) . '" deleted.';
        } else {
            $errorMsg = 'Error: ' . mysqli_error($conn);
        }
    } else {
        $errorMsg = 'Category not found.';
    }
}

// ── LOAD FOR EDIT ────────────────────────────────────────────
if (isset($_GET['edit_id'])) {
    // [VULN] SQL Injection — edit_id from GET
    $editId    = $_GET['edit_id'];
    $editQuery = "SELECT * FROM categories WHERE id = " . $editId; // <-- SQLi
    $editRes   = mysqli_query($conn, $editQuery);
    if ($editRes && mysqli_num_rows($editRes) > 0) {
        $editCat = mysqli_fetch_assoc($editRes);
    }
}

// ── ADD / UPDATE ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $catName = trim($_POST['cat_name'] ?? '');
    $catId   = $_POST['cat_id'] ?? '';

    if (empty($catName)) {
        $errorMsg = 'Category name cannot be empty.';
    } else {

        if (!empty($catId)) {
            // UPDATE
            $escapedName = mysqli_real_escape_string($conn, $catName);
            // [VULN] SQL Injection — $catId from POST not validated
            $updateSql = "UPDATE categories SET name='$escapedName' WHERE id=" . $catId;
            if (mysqli_query($conn, $updateSql)) {
                $successMsg = 'Category updated successfully.';
                $editCat    = null;
                header('Location: categories.php?success=' . urlencode($successMsg));
                exit;
            } else {
                $errorMsg = 'Update failed: ' . mysqli_error($conn);
            }
        } else {
            // INSERT
            $escapedName = mysqli_real_escape_string($conn, $catName);
            $insertSql   = "INSERT INTO categories (name) VALUES ('$escapedName')";
            if (mysqli_query($conn, $insertSql)) {
                header('Location: categories.php?success=' . urlencode('Category added successfully.'));
                exit;
            } else {
                $errorMsg = 'Insert failed: ' . mysqli_error($conn);
            }
        }
    }
}

// ── GET success from redirect ────────────────────────────────
if (isset($_GET['success'])) $successMsg = $_GET['success'];

// ── Fetch all categories ─────────────────────────────────────
$allResult = mysqli_query($conn, "SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id DESC");
$allCats   = [];
while ($row = mysqli_fetch_assoc($allResult)) {
    $allCats[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories — NexusBrew Admin</title>
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
                <div class="topbar-title">Categories</div>
                <div class="topbar-breadcrumb">
                    <span>Catalog</span> / Categories
                </div>
            </div>
            <div class="topbar-actions">
                <button class="btn-action view d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>

        <div class="admin-content">

            <!-- Alerts -->
            <?php if ($successMsg): ?>
                <div class="alert-success auto-dismiss mb-4">
                    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($successMsg) ?>
                </div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="alert-error auto-dismiss mb-4">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <div class="row g-4">

                <!-- ── Category Form ─────────────────────── -->
                <div class="col-lg-4">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div class="admin-card-title">
                                <i class="bi bi-<?= $editCat ? 'pencil-fill' : 'plus-circle-fill' ?>"></i>
                                <?= $editCat ? 'Edit Category' : 'Add New Category' ?>
                            </div>
                            <?php if ($editCat): ?>
                                <a href="categories.php" class="btn-action delete" title="Cancel edit">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="admin-card-body">

                            <!-- VULNERABILITY: No CSRF token -->
                            <form method="POST" action="categories.php" id="categoryForm">

                                <?php if ($editCat): ?>
                                    <input type="hidden" name="cat_id" value="<?= (int)$editCat['id'] ?>">
                                    <div class="nb-badge nb-badge-info mb-3">
                                        Editing ID #<?= (int)$editCat['id'] ?>
                                    </div>
                                <?php endif; ?>

                                <div class="nb-form-group">
                                    <label class="nb-label" for="cat_name">
                                        Category Name <span style="color:var(--danger);">*</span>
                                    </label>
                                    <!-- VULNERABILITY: XSS - cat_name echoed back without sanitization on re-display -->
                                    <input
                                        type="text"
                                        id="cat_name"
                                        name="cat_name"
                                        class="nb-input"
                                        placeholder="e.g. Hot Coffee"
                                        value="<?= $editCat ? htmlspecialchars($editCat['name']) : (isset($_POST['cat_name']) ? $_POST['cat_name'] /* [VULN] XSS: no htmlspecialchars */ : '') ?>"
                                        required
                                        autofocus
                                    >
                                    <p style="font-size:0.72rem;color:var(--text-muted);margin-top:0.5rem;">
                                        Use descriptive names like "Cold Brew", "Espresso Drinks", etc.
                                    </p>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn-gold" style="flex:1;justify-content:center;" id="saveCategoryBtn">
                                        <i class="bi bi-<?= $editCat ? 'check-lg' : 'plus-lg' ?> me-1"></i>
                                        <?= $editCat ? 'Update' : 'Add Category' ?>
                                    </button>
                                    <?php if ($editCat): ?>
                                        <a href="categories.php" class="btn-outline-gold" style="padding:0.7rem 1rem;" id="cancelCatEditBtn">
                                            Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>

                            </form>

                        </div>
                    </div>
                </div><!-- /form col -->

                <!-- ── Category List ─────────────────────── -->
                <div class="col-lg-8">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div class="admin-card-title">
                                <i class="bi bi-tags-fill"></i>
                                All Categories
                                <span class="nb-badge nb-badge-gold ms-2"><?= count($allCats) ?></span>
                            </div>
                        </div>

                        <div style="overflow-x:auto;">
                            <table class="nb-table">
                                <thead>
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>Category Name</th>
                                        <th>Products</th>
                                        <th>Created</th>
                                        <th style="width:100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($allCats)): ?>
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state py-4">
                                                    <div class="empty-state-icon"><i class="bi bi-tags"></i></div>
                                                    <div class="empty-state-title">No categories yet</div>
                                                    <p style="font-size:0.82rem;margin-top:0.5rem;">
                                                        Add your first category using the form.
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($allCats as $i => $cat): ?>
                                            <tr <?= $editCat && $editCat['id'] == $cat['id'] ? 'style="background:rgba(200,145,79,0.06);"' : '' ?>>
                                                <td style="color:var(--text-muted);font-size:0.8rem;"><?= $i + 1 ?></td>
                                                <td>
                                                    <div style="font-weight:500;display:flex;align-items:center;gap:8px;">
                                                        <i class="bi bi-tag-fill" style="color:var(--gold);font-size:0.75rem;"></i>
                                                        <?= htmlspecialchars($cat['name']) ?>
                                                        <?php if ($editCat && $editCat['id'] == $cat['id']): ?>
                                                            <span class="nb-badge nb-badge-info" style="font-size:0.65rem;">Editing</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="products.php" style="text-decoration:none;">
                                                        <span class="nb-badge <?= $cat['product_count'] > 0 ? 'nb-badge-success' : 'nb-badge-danger' ?>">
                                                            <?= $cat['product_count'] ?> product<?= $cat['product_count'] != 1 ? 's' : '' ?>
                                                        </span>
                                                    </a>
                                                </td>
                                                <td style="font-size:0.8rem;color:var(--text-muted);">
                                                    <?= date('M j, Y', strtotime($cat['created_at'])) ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="categories.php?edit_id=<?= $cat['id'] ?>"
                                                           class="btn-action edit"
                                                           title="Edit category"
                                                           id="editCat<?= $cat['id'] ?>">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </a>
                                                        <a href="categories.php?delete_id=<?= $cat['id'] ?>"
                                                           class="btn-action delete"
                                                           data-confirm="Delete category '<?= htmlspecialchars($cat['name']) ?>'? Products in this category will become uncategorized."
                                                           title="Delete category"
                                                           id="deleteCat<?= $cat['id'] ?>">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div><!-- /list col -->

            </div><!-- /row -->

        </div><!-- /admin-content -->

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
