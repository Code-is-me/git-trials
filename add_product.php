<?php
/**
 * NexusBrew - Add Product Page
 *
 * VULNERABILITIES:
 * - Weak input validation (CWE-20) — Detected by Semgrep / PHPStan
 * - No CSRF protection on form (CWE-352)
 * - Missing security headers (CWE-693) — Detected by Nuclei
 * - Image URL not validated (SSRF potential in advanced scenarios)
 */

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$activePage = 'products';
$errors     = [];
$successMsg = '';

// ── Fetch categories for dropdown ────────────────────────────
$catResult  = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row;
}

// ── Handle POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // VULNERABILITY: Weak / Minimal Input Validation
    // CWE-20: Improper Input Validation
    // Tool: Semgrep, PHPStan
    //
    // Issues:
    // - No type coercion or stricter validation beyond empty check
    // - No max-length enforcement on description
    // - Image URL not validated for format or domain
    // - Price not checked for negative values
    // - No sanitization against special characters
    //
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price']       ?? '';  // [VULN] No numeric/positive validation
    $stock       = $_POST['stock']       ?? '';  // [VULN] No integer validation
    $category_id = $_POST['category_id'] ?? '';
    $image       = trim($_POST['image']  ?? ''); // [VULN] No URL validation

    // Basic (weak) validation — intentionally minimal
    if (empty($name))        $errors[] = 'Product name is required.';
    if (empty($price))       $errors[] = 'Price is required.';
    if (empty($stock))       $errors[] = 'Stock is required.';

    if (empty($errors)) {

        // ══════════════════════════════════════════════════════
        // VULNERABILITY: SQL Injection risk pattern
        // Even though we escape here with mysqli_real_escape_string,
        // the overall codebase pattern demonstrates unsafe practices.
        // A full SQLi fix requires prepared statements, not just escaping.
        // ══════════════════════════════════════════════════════
        $name        = mysqli_real_escape_string($conn, $name);
        $description = mysqli_real_escape_string($conn, $description);
        $image       = mysqli_real_escape_string($conn, $image);

        $price       = (float) $price;
        $stock       = (int)   $stock;
        $category_id = !empty($category_id) ? (int) $category_id : 'NULL';

        $insertSql = "INSERT INTO products (name, description, price, stock, category_id, image)
                      VALUES ('$name', '$description', $price, $stock, $category_id, '$image')";

        if (mysqli_query($conn, $insertSql)) {
            header('Location: products.php?success=' . urlencode('Product added successfully!'));
            exit;
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn); // [VULN] Exposes DB error to user
        }
    }
}

// Re-populate form on error
$formData = [
    'name'        => $_POST['name']        ?? '',
    'description' => $_POST['description'] ?? '',
    'price'       => $_POST['price']       ?? '',
    'stock'       => $_POST['stock']       ?? '',
    'category_id' => $_POST['category_id'] ?? '',
    'image'       => $_POST['image']       ?? '',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product — NexusBrew Admin</title>
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
                <div class="topbar-title">Add Product</div>
                <div class="topbar-breadcrumb">
                    <a href="products.php" style="color:var(--text-muted);text-decoration:none;">Products</a>
                    / <span>Add New</span>
                </div>
            </div>
            <div class="topbar-actions">
                <button class="btn-action view d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <a href="products.php" class="btn-outline-gold" style="padding:0.5rem 1.25rem;font-size:0.85rem;" id="backToProductsBtn">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="admin-content">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">

                    <!-- Validation Errors -->
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
                                <i class="bi bi-plus-circle-fill"></i>
                                New Product Details
                            </div>
                        </div>
                        <div class="admin-card-body">

                            <!-- VULNERABILITY: No CSRF token in this form -->
                            <form method="POST" action="add_product.php" id="addProductForm">

                                <div class="row g-4">

                                    <!-- Left Column -->
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
                                                placeholder="e.g. Signature Espresso"
                                                value="<?= htmlspecialchars($formData['name']) ?>"
                                                required
                                            >
                                        </div>

                                        <div class="nb-form-group">
                                            <label class="nb-label" for="description">Description</label>
                                            <textarea
                                                id="description"
                                                name="description"
                                                class="nb-textarea"
                                                placeholder="Describe the product — origin, tasting notes, preparation..."
                                                rows="4"
                                            ><?= htmlspecialchars($formData['description']) ?></textarea>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <div class="nb-form-group mb-0">
                                                    <label class="nb-label" for="price">
                                                        Price (Rp) <span style="color:var(--danger);">*</span>
                                                    </label>
                                                    <!-- VULNERABILITY: No min="0" enforced server-side -->
                                                    <input
                                                        type="number"
                                                        id="price"
                                                        name="price"
                                                        class="nb-input"
                                                        placeholder="e.g. 45000"
                                                        step="500"
                                                        value="<?= htmlspecialchars($formData['price']) ?>"
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
                                                        placeholder="e.g. 100"
                                                        value="<?= htmlspecialchars($formData['stock']) ?>"
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
                                                                <?= $formData['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($cat['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    </div><!-- /left -->

                                    <!-- Right Column: Image -->
                                    <div class="col-lg-4">
                                        <div class="nb-form-group">
                                            <label class="nb-label" for="image">
                                                Image URL
                                            </label>
                                            <!-- VULNERABILITY: URL not validated for SSRF or content-type -->
                                            <input
                                                type="url"
                                                id="image"
                                                name="image"
                                                class="nb-input mb-3"
                                                placeholder="https://..."
                                                value="<?= htmlspecialchars($formData['image']) ?>"
                                            >

                                            <!-- Preview -->
                                            <div style="border:1px solid var(--border);border-radius:var(--radius-md);overflow:hidden;background:rgba(255,255,255,0.03);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;">
                                                <img
                                                    id="imagePreview"
                                                    src=""
                                                    alt="Image preview"
                                                    style="width:100%;height:100%;object-fit:cover;display:none;"
                                                >
                                                <div id="previewPlaceholder" style="text-align:center;color:var(--text-muted);padding:1rem;">
                                                    <i class="bi bi-image" style="font-size:2rem;"></i>
                                                    <p style="font-size:0.75rem;margin-top:0.5rem;">Enter URL to preview</p>
                                                </div>
                                            </div>

                                            <p style="font-size:0.72rem;color:var(--text-muted);margin-top:0.5rem;">
                                                Use royalty-free images from Unsplash, Pexels, or Pixabay.
                                            </p>
                                        </div>
                                    </div><!-- /right -->

                                </div><!-- /row -->

                                <!-- Form Actions -->
                                <div class="d-flex gap-3 mt-4 pt-4" style="border-top:1px solid var(--border);">
                                    <button type="submit" class="btn-gold" id="submitAddProduct"
                                            style="padding:0.7rem 2rem;">
                                        <i class="bi bi-check-lg me-2"></i> Save Product
                                    </button>
                                    <a href="products.php" class="btn-outline-gold" id="cancelAddBtn"
                                       style="padding:0.7rem 1.5rem;">
                                        Cancel
                                    </a>
                                </div>

                            </form>

                        </div>
                    </div><!-- /admin-card -->

                </div>
            </div>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
<script>
// Live image preview toggle
const imgInput   = document.getElementById('image');
const imgPreview = document.getElementById('imagePreview');
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
    if (imgInput.value) imgInput.dispatchEvent(new Event('input'));
}
</script>
</body>
</html>
