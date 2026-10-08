<?php
/**
 * NexusBrew - Database Connection
 *
 * ┌──────────────────────────────────────────────────────────────┐
 * │              INTENTIONAL VULNERABILITY: SQL INJECTION        │
 * └──────────────────────────────────────────────────────────────┘
 *
 * VULNERABILITY #3: Raw SQL Queries Without Prepared Statements
 * CWE ID   : CWE-89 (Improper Neutralization of SQL Commands)
 * Tool     : Semgrep (SAST)
 * Severity : CRITICAL
 * Description:
 *   This application uses raw mysqli_query() calls with user
 *   input directly concatenated into SQL strings. Semgrep
 *   will detect this pattern as a SQL Injection vulnerability.
 *   Fix: Use PDO or mysqli prepared statements with
 *   parameterized queries.
 */

require_once __DIR__ . '/config.php';

// Raw mysqli connection — no abstraction layer or ORM
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    // VULNERABILITY: Detailed error message exposed to end users
    // CWE-209: Generation of Error Message Containing Sensitive Information
    // Detected by: Semgrep, Nuclei
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, 'utf8mb4');
?>
