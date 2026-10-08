# ☕ NexusBrew — Intentionally Vulnerable PHP E-Commerce

> **DevSecOps Final Project Demo Application**
>
> **Title:** Implementasi DevSecOps pada CI/CD Pipeline untuk Mendeteksi Kerentanan Aplikasi Web Secara Otomatis

---

> [!CAUTION]
> This application is **intentionally vulnerable** and is designed **ONLY for educational/research purposes** in a DevSecOps CI/CD pipeline demonstration. **DO NOT deploy this in a production environment.**

---

## 📋 Project Overview

NexusBrew is a simple PHP CRUD e-commerce website for a fictional coffee shop. It contains deliberately introduced security vulnerabilities that will be detected by the DevSecOps security scanning pipeline.

### Tech Stack

| Layer      | Technology                  |
|------------|----------------------------|
| Backend    | Native PHP 8+              |
| Database   | MySQL 8+                   |
| Frontend   | HTML5, CSS3, Bootstrap 5   |
| Web Server | Apache2                    |
| OS Target  | Ubuntu Server 22.04 LTS    |
| CI/CD      | GitLab + GitLab Runner     |

---

## 🔴 Intentional Vulnerabilities

| # | Vulnerability | CWE | File | Detection Tool |
|---|--------------|-----|------|----------------|
| 1 | Hard-coded Admin Credentials | CWE-798 | `config.php`, `login.php` | **Gitleaks** |
| 2 | Hard-coded DB Credentials | CWE-798 | `config.php` | **Gitleaks** |
| 3 | SQL Injection (Search) | CWE-89 | `products.php` | **Semgrep** |
| 4 | SQL Injection (Edit/Delete) | CWE-89 | `edit_product.php`, `delete_product.php`, `categories.php` | **Semgrep** |
| 5 | Cross-Site Scripting (XSS) | CWE-79 | `products.php`, `categories.php` | **Semgrep** |
| 6 | Weak Input Validation | CWE-20 | `add_product.php` | **Semgrep / PHPStan** |
| 7 | Missing Security Headers | CWE-693 | All pages | **Nuclei** |
| 8 | No CSRF Protection | CWE-352 | All forms | **Semgrep** |
| 9 | Plain-text Password in DB | CWE-256 | `database.sql` | **Semgrep** |
| 10 | Outdated Dependency (PHPMailer 5.2.0) | CWE-1104 | `composer.json` | **Trivy** |
| 11 | Sensitive Error Disclosure | CWE-209 | `db.php`, forms | **Semgrep** |

---

## 📁 Folder Structure

```
nexusbrew/
├── index.php              # Public storefront
├── login.php              # Admin login (hard-coded creds)
├── dashboard.php          # Admin dashboard
├── products.php           # Product list + search (SQLi + XSS)
├── add_product.php        # Add product form
├── edit_product.php       # Edit product (SQLi)
├── delete_product.php     # Delete handler (SQLi)
├── categories.php         # Categories CRUD (SQLi)
├── config.php             # App config (hard-coded credentials)
├── db.php                 # Database connection
├── database.sql           # Schema + sample data
├── composer.json          # Outdated PHPMailer 5.2.0 (Trivy)
├── includes/
│   └── sidebar.php        # Admin sidebar component
└── assets/
    ├── css/
    │   └── style.css      # Main stylesheet
    └── js/
        └── app.js         # Frontend JS
```

---

## 🚀 Installation Guide (Ubuntu Server + Apache2 + MySQL)

### Step 1 — Install Prerequisites

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y apache2 mysql-server php8.1 php8.1-mysql php8.1-mbstring libapache2-mod-php
```

### Step 2 — Enable Apache Modules

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Step 3 — Clone Repository

```bash
cd /var/www/html
sudo git clone <your-gitlab-repo-url> nexusbrew
sudo chown -R www-data:www-data /var/www/html/nexusbrew
sudo chmod -R 755 /var/www/html/nexusbrew
```

### Step 4 — Create MySQL Database

```bash
sudo mysql -u root -p
```

Inside MySQL shell:
```sql
CREATE DATABASE nexusbrew CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nexusbrew'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON nexusbrew.* TO 'nexusbrew'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 5 — Import Database Schema

```bash
sudo mysql -u root nexusbrew < /var/www/html/nexusbrew/database.sql
```

### Step 6 — Configure Apache Virtual Host

```bash
sudo nano /etc/apache2/sites-available/nexusbrew.conf
```

Paste:
```apache
<VirtualHost *:80>
    ServerName nexusbrew.local
    DocumentRoot /var/www/html/nexusbrew

    <Directory /var/www/html/nexusbrew>
        AllowOverride All
        Require all granted
    </Directory>

    # NOTE: Security headers intentionally NOT configured
    # This allows Nuclei to detect missing headers:
    # - Content-Security-Policy
    # - X-Frame-Options
    # - X-Content-Type-Options
    # - Strict-Transport-Security

    ErrorLog ${APACHE_LOG_DIR}/nexusbrew_error.log
    CustomLog ${APACHE_LOG_DIR}/nexusbrew_access.log combined
</VirtualHost>
```

Enable it:
```bash
sudo a2ensite nexusbrew.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

### Step 7 — Test the Application

Open your browser and visit:

| URL | Page |
|-----|------|
| `http://<server-ip>/` | Public Storefront |
| `http://<server-ip>/login.php` | Admin Login |
| `http://<server-ip>/dashboard.php` | Dashboard |
| `http://<server-ip>/products.php` | Products |
| `http://<server-ip>/categories.php` | Categories |

### Admin Credentials (Hard-coded — Intentional Vulnerability)

```
Email:    admin@nexusbrew.local
Password: Admin123!
```

---

## 🔬 DevSecOps Pipeline — Tool Configuration

### Gitleaks (Secret Scanning)
Detects:
- `ADMIN_EMAIL = 'admin@nexusbrew.local'` in `config.php`
- `ADMIN_PASSWORD = 'Admin123!'` in `config.php`

Sample `.gitleaks.toml`:
```toml
[allowlist]
  description = "Allow test files"
  files = ['^\.gitleaks\.toml$']
```

### Semgrep (SAST)
Detects:
- SQL Injection patterns in `products.php`, `edit_product.php`, `delete_product.php`, `categories.php`
- XSS patterns in `products.php`

Run locally:
```bash
semgrep --config=p/php-security-audit .
```

### Trivy (SCA — Dependency Scanning)
Detects:
- PHPMailer 5.2.0 (CVE-2016-10033, CVE-2016-10045) in `composer.json`

Run locally:
```bash
trivy fs --scanners vuln .
```

### Nuclei (DAST — HTTP Security Headers)
Detects:
- Missing `Content-Security-Policy`
- Missing `X-Frame-Options`
- Missing `X-Content-Type-Options`

Run:
```bash
nuclei -u http://<server-ip>/ -t http/misconfiguration/
```

### PHPStan (Static Analysis)
Run:
```bash
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse --level=5 .
```

---

## 📄 GitLab CI/CD Pipeline Sample (`.gitlab-ci.yml`)

```yaml
stages:
  - lint
  - sast
  - sca
  - dast

phpstan:
  stage: lint
  image: php:8.1-cli
  script:
    - composer install
    - vendor/bin/phpstan analyse --level=5 .

gitleaks:
  stage: sast
  image: zricethezav/gitleaks:latest
  script:
    - gitleaks detect --source . --verbose

semgrep:
  stage: sast
  image: returntocorp/semgrep:latest
  script:
    - semgrep --config=p/php-security-audit --output=semgrep-report.json --json .
  artifacts:
    paths:
      - semgrep-report.json

trivy:
  stage: sca
  image: aquasec/trivy:latest
  script:
    - trivy fs --scanners vuln --format json --output trivy-report.json .
  artifacts:
    paths:
      - trivy-report.json

nuclei:
  stage: dast
  image: projectdiscovery/nuclei:latest
  script:
    - nuclei -u http://$APP_URL -t http/misconfiguration/ -json -o nuclei-report.json
  artifacts:
    paths:
      - nuclei-report.json
```

---

## 🔑 Hard-coded Credentials Reference

> These are intentional and exist to be detected by **Gitleaks**:

```php
// config.php
define('ADMIN_EMAIL',    'admin@nexusbrew.local');  // [GITLEAKS]
define('ADMIN_PASSWORD', 'Admin123!');               // [GITLEAKS]

// login.php
if ($inputEmail === 'admin@nexusbrew.local' && $inputPassword === 'Admin123!') {
```

---

## 📚 References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CWE List](https://cwe.mitre.org/data/definitions/)
- [Gitleaks Documentation](https://github.com/gitleaks/gitleaks)
- [Semgrep Rules for PHP](https://semgrep.dev/p/php-security-audit)
- [Trivy Documentation](https://aquasecurity.github.io/trivy/)
- [Nuclei Templates](https://github.com/projectdiscovery/nuclei-templates)
- [PHPMailer CVE-2016-10033](https://nvd.nist.gov/vuln/detail/CVE-2016-10033)

---

*NexusBrew v1.0.0 — DevSecOps Demo Application*
*For educational purposes only.*
