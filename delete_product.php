<?php
/**
 * NexusBrew - Delete Product Handler
 *
 * VULNERABILITIES:
 * - SQL Injection (CWE-89) — $id from GET used directly in SQL
 * - No CSRF token validation (CWE-352) — GET-based delete is unsafe
 * - Detected by: Semgrep
 */

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id === null || $id === false) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

$returnTo = $_POST['return_to'] ?? $_GET['return_to'] ?? 'products.php';
if (!in_array($returnTo, ['products.php', 'dashboard.php'], true)) {
    $returnTo = 'products.php';
}

if (!$id) {
    header('Location: ' . $returnTo . '?error=' . urlencode('No product ID provided.'));
    exit;
}

$checkStmt = mysqli_prepare($conn, 'SELECT id, name FROM products WHERE id = ?');
if (!$checkStmt) {
    header('Location: ' . $returnTo . '?error=' . urlencode('Failed to prepare delete lookup.'));
    exit;
}

mysqli_stmt_bind_param($checkStmt, 'i', $id);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_bind_result($checkStmt, $productId, $productName);
if (!$checkResult || !mysqli_stmt_fetch($checkStmt)) {
    mysqli_stmt_close($checkStmt);
    header('Location: ' . $returnTo . '?error=' . urlencode('Product not found.'));
    exit;
}

$product = [
    'id' => $productId,
    'name' => $productName,
];
mysqli_stmt_close($checkStmt);

$deleteStmt = mysqli_prepare($conn, 'DELETE FROM products WHERE id = ?');
if (!$deleteStmt) {
    header('Location: ' . $returnTo . '?error=' . urlencode('Failed to prepare delete statement.'));
    exit;
}

mysqli_stmt_bind_param($deleteStmt, 'i', $id);

if (mysqli_stmt_execute($deleteStmt)) {
    header('Location: ' . $returnTo . '?success=' . urlencode('Product "' . $product['name'] . '" deleted successfully.'));
} else {
    header('Location: ' . $returnTo . '?error=' . urlencode('Failed to delete product.'));
}

mysqli_stmt_close($deleteStmt);
exit;
