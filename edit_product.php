<?php
/**
 * NexusBrew - Edit Product Page
 *
 * ┌──────────────────────────────────────────────────────────────┐
 * │ INTENTIONAL VULNERABILITIES:                                 │
 * │                                                              │
 * │ 1. SQL Injection (CWE-89)                                    │
 * │    Tool: Semgrep                                             │
 * │    The product ID from $_GET['id'] is concatenated directly  │
 * │    into the SELECT and UPDATE SQL queries.                   │
 * │                                                              │
 * │ 2. Weak Input Validation (CWE-20)                           │
 * │    Tool: Semgrep / PHPStan                                   │
 * │    No server-side validation of data types beyond empty      │
 * │    check.                                                    │
 * │                                                              │
 * │ 3. No CSRF Protection (CWE-352)                             │
 * │    No token verification on POST form submission.            │
 * └──────────────────────────────────────────────────────────────┘
 */

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$activePage = 'products';
$errors     = [];

// ── Get product ID ───────────────────────────────────────────
// VULNERABILITY: SQL Injection
// $id is taken from user input ($_GET['id']) and used directly
// in the SQL query. An attacker could pass: ?id=1 OR 1=1
// ════════════════════════════════════════════════════════════
$id = $_GET['id'] ?? '';

if (empty($id)) {
    header('Location: products.php');
    exit;
}

// [VULN] SQL Injection — $id directly in query, no prepared statement
$productQuery  = "SELECT * FROM products WHERE id = " . $id; // <-- SQL INJECTION
$productResult = mysqli_query($conn, $productQuery);

if (!$productResult || mysqli_num_rows($productResult) === 0) {
    header('Location: products.php?error=' . urlencode('Product not found.'));
    exit;
}

$product = mysqli_fetch_assoc($productResult);

// ── Fetch categories ─────────────────────────────────────────
$catResult  = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row;
}

// ── Handle POST (Update) ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price']       ?? '';
    $stock       = $_POST['stock']       ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $image       = trim($_POST['image']  ?? '');

    if (empty($name))  $errors[] = 'Product name is required.';
    if (empty($price)) $errors[] = 'Price is required.';
    if (empty($stock)) $errors[] = 'Stock is required.';

    if (empty($errors)) {

        $name        = mysqli_real_escape_string($conn, $name);
        $description = mysqli_real_escape_string($conn, $description);
        $image       = mysqli_real_escape_string($conn, $image);
        $price       = (float) $price;
        $stock       = (int)   $stock;
        $category_id = !empty($category_id) ? (int) $category_id : 'NULL';

        // [VULN] SQL Injection — $id from GET not re-validated for UPDATE
        $updateSql = "UPDATE products
                      SET name='$name', description='$description',
                          price=$price, stock=$stock,
                          category_id=$category_id, image='$image'
                      WHERE id=" . $id; // <-- SQL INJECTION

        if (mysqli_query($conn, $updateSql)) {
            header('Location: products.php?success=' . urlencode('Product updated successfully!'));
            exit;
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn); // [VULN] Error disclosure
        }
    }

    // Repopulate with POST data on error
    $product = array_merge($product, [
        'name'        => $_POST['name']        ?? '',
        'description' => $_POST['description'] ?? '',
        'price'       => $_POST['price']       ?? '',
        'stock'       => $_POST['stock']       ?? '',
        'category_id' => $_POST['category_id'] ?? '',
        'image'       => $_POST['image']       ?? '',
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product — NexusBrew Admin</title>
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
                <div class="topbar-title">Edit Product</div>
                <div class="topbar-breadcrumb">
                    <a href="products.php" style="color:var(--text-muted);text-decoration:none;">Products</a>
                    / <span>Edit #<?= (int)$id ?></span>
                </div>
            </div>
            <div class="topbar-actions">
                <button class="btn-action view d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <a href="products.php" class="btn-outline-gold" style="padding:0.5rem 1.25rem;font-size:0.85rem;">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="admin-content">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">

                    <!-- Errors -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert-error mb-4">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>
                                <?php foreach ($errors as $err): ?>
                                    <div><?= htmlspecialchars($err) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Form Card -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div class="admin-card-title">
                                <i class="bi bi-pencil-fill"></i>
                                Editing: <?= htmlspecialchars($product['name']) ?>
                            </div>
                            <span class="nb-badge nb-badge-info">ID #<?= (int)$id ?></span>
                        </div>
                        <div class="admin-card-body">

                            <!-- VULNERABILITY: No CSRF token -->
                            <form method="POST" action="edit_product.php?id=<?= (int)$id ?>" id="editProductForm">

                                <div class="row g-4">

                                    <div class="col-lg-8">

                                        <div class="nb-form-group">
                                            <label class="nb-label" for="name">
                                                Product Name <span style="color:var(--danger);">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                id="name"
                                                name="name"
                                                class="nb-input"
                                                value="<?= htmlspecialchars($product['name']) ?>"
                                                required
                                            >
                                        </div>

                                        <div class="nb-form-group">
                                            <label class="nb-label" for="description">Description</label>
                                            <textarea
                                                id="description"
                                                name="description"
                                                class="nb-textarea"
                                                rows="4"
                                            ><?= htmlspecialchars($product['description']) ?></textarea>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <div class="nb-form-group mb-0">
                                                    <label class="nb-label" for="price">
                                                        Price (Rp) <span style="color:var(--danger);">*</span>
                                                    </label>
                                                    <input
                                                        type="number"
                                                        id="price"
                                                        name="price"
                                                        class="nb-input"
                                                        step="500"
                                                        value="<?= htmlspecialchars($product['price']) ?>"
                                                        required
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="nb-form-group mb-0">
                                                    <label class="nb-label" for="stock">
                                                        Stock <span style="color:var(--danger);">*</span>
                                                    </label>
                                                    <input
                                                        type="number"
                                                        id="stock"
                                                        name="stock"
                                                        class="nb-input"
                                                        value="<?= htmlspecialchars($product['stock']) ?>"
                                                        required
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="nb-form-group mb-0">
                                                    <label class="nb-label" for="category_id">Category</label>
                                                    <select id="category_id" name="category_id" class="nb-select">
                                                        <option value="">— None —</option>
                                                        <?php foreach ($categories as $cat): ?>
                                                            <option value="<?= $cat['id'] ?>"
                                                                <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($cat['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    </div><!-- /left -->

                                    <div class="col-lg-4">
                                        <div class="nb-form-group">
                                            <label class="nb-label" for="image">Image URL</label>
                                            <input
                                                type="url"
                                                id="image"
                                                name="image"
                                                class="nb-input mb-3"
                                                placeholder="https://..."
                                                value="<?= htmlspecialchars($product['image'] ?? '') ?>"
                                            >
                                            <div style="border:1px solid var(--border);border-radius:var(--radius-md);overflow:hidden;background:rgba(255,255,255,0.03);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;">
                                                <img
                                                    id="imagePreview"
                                                    src="<?= htmlspecialchars($product['image'] ?? '') ?>"
                                                    alt="Preview"
                                                    style="width:100%;height:100%;object-fit:cover;<?= !empty($product['image']) ? '' : 'display:none;' ?>"
                                                    onerror="this.style.display='none'"
                                                >
                                                <div id="previewPlaceholder" style="text-align:center;color:var(--text-muted);padding:1rem;<?= !empty($product['image']) ? 'display:none' : '' ?>">
                                                    <i class="bi bi-image" style="font-size:2rem;"></i>
                                                    <p style="font-size:0.75rem;margin-top:0.5rem;">No image</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div><!-- /row -->

                                <div class="d-flex gap-3 mt-4 pt-4" style="border-top:1px solid var(--border);">
                                    <button type="submit" class="btn-gold" style="padding:0.7rem 2rem;" id="saveEditBtn">
                                        <i class="bi bi-check-lg me-2"></i> Update Product
                                    </button>
                                    <a href="products.php" class="btn-outline-gold" style="padding:0.7rem 1.5rem;" id="cancelEditBtn">
                                        Cancel
                                    </a>
                                </div>

                            </form>

                            <div class="d-flex justify-content-end mt-3">
                                <form method="post"
                                      action="delete_product.php"
                                      class="m-0"
                                      data-confirm="Delete this product permanently?"
                                      id="deleteFromEditForm">
                                    <input type="hidden" name="id" value="<?= (int)$id ?>">
                                    <input type="hidden" name="return_to" value="products.php">
                                    <button type="submit"
                                            class="btn-action delete"
                                            style="width:auto;height:auto;padding:0.7rem 1.2rem;font-size:0.82rem;"
                                            id="deleteFromEditBtn">
                                        <i class="bi bi-trash-fill me-1"></i> Delete
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
<script>
const imgInput       = document.getElementById('image');
const imgPreview     = document.getElementById('imagePreview');
const imgPlaceholder = document.getElementById('previewPlaceholder');

if (imgInput) {
    imgInput.addEventListener('input', function () {
        if (this.value.trim()) {
            imgPreview.src = this.value.trim();
            imgPreview.style.display = 'block';
            imgPlaceholder.style.display = 'none';
            imgPreview.onerror = function () {
                imgPreview.style.display = 'none';
                imgPlaceholder.style.display = 'flex';
            };
        } else {
            imgPreview.style.display = 'none';
            imgPlaceholder.style.display = 'flex';
        }
    });
}
</script>
</body>
</html>
