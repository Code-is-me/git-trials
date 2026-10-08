<?php
/**
 * NexusBrew Coffee Shop - Configuration File
 *
 * ┌──────────────────────────────────────────────────────────────┐
 * │          DEVSECOPS INTENTIONAL VULNERABILITY FILE            │
 * │  Project: Implementasi DevSecOps pada CI/CD Pipeline         │
 * │  untuk Mendeteksi Kerentanan Aplikasi Web Secara Otomatis    │
 * └──────────────────────────────────────────────────────────────┘
 *
 * This file intentionally contains security vulnerabilities
 * for the purpose of demonstrating automated detection via
 * a DevSecOps CI/CD pipeline.
 *
 * DO NOT use this in a production environment.
 */

// ==============================================================
// VULNERABILITY #1: Hard-coded Admin Credentials
// --------------------------------------------------------------
// CWE ID   : CWE-798 (Use of Hard-coded Credentials)
// Tool     : Gitleaks (Secret Scanning)
// Severity : CRITICAL
// Description:
//   Admin login credentials are hard-coded directly in the
//   source code. Gitleaks will scan the Git repository and
//   flag these secrets as exposed credentials in the codebase.
//   In a real-world scenario, these would be stored in
//   environment variables or a secrets manager (e.g., Vault).
// ==============================================================
define('ADMIN_EMAIL',    'admin@nexusbrew.local');  // [GITLEAKS] Hard-coded email
define('ADMIN_PASSWORD', 'Admin123!');               // [GITLEAKS] Hard-coded password

// ==============================================================
// VULNERABILITY #2: Hard-coded Database Credentials
// --------------------------------------------------------------
// CWE ID   : CWE-798 (Use of Hard-coded Credentials)
// Tool     : Gitleaks
// Severity : HIGH
// Description:
//   Database connection parameters are hard-coded. Gitleaks
//   will also flag the DB credentials if they were non-empty.
//   In production, use .env files and never commit them.
// ==============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');    // [GITLEAKS] Hard-coded DB username
define('DB_PASS', '');        // [GITLEAKS] Hard-coded DB password (empty = weak)
define('DB_NAME', 'nexusbrew');

// Application Meta
define('APP_NAME',    'NexusBrew');
define('APP_VERSION', '1.0.0');
define('APP_URL',     'http://localhost/phpweb/nexusbrew');
?>
