<?php
/**
 * NexusBrew - Public Landing Page (Storefront)
 * A clean, modern coffee shop e-commerce homepage.
 *
 * VULNERABILITY NOTE:
 * - No security headers set (CSP, X-Frame-Options, X-Content-Type-Options)
 * - CWE-693 | Detected by: Nuclei
 */

require_once __DIR__ . '/db.php';

// Fetch featured products (latest 6)
// VULNERABILITY: While this specific query is safe (no user input),
// the codebase pattern uses raw mysqli queries throughout.
$featuredQuery  = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 6";
$featuredResult = mysqli_query($conn, $featuredQuery);
$featured       = [];
while ($row = mysqli_fetch_assoc($featuredResult)) {
    $featured[] = $row;
}

// Count totals
$totalProducts   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM products"))['cnt'];
$totalCategories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM categories"))['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBrew — Premium Coffee Experience</title>
    <meta name="description" content="NexusBrew offers handcrafted premium coffee beverages and specialty beans sourced from the world's finest origins. Order now and experience the difference.">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════
     NAVBAR
     ═══════════════════════════════════════════════════════════ -->
<nav class="nb-navbar navbar navbar-expand-lg">
    <div class="container">

        <!-- Brand -->
        <a href="index.php" class="nb-brand" id="navBrand">
            <div class="brand-icon">☕</div>
            NexusBrew
        </a>

        <!-- Toggle -->
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                style="color:var(--gold);">
            <i class="bi bi-list" style="font-size:1.5rem;"></i>
        </button>

        <!-- Links -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <li class="nav-item">
                    <a href="#featured" class="nb-nav-link nav-link">Menu</a>
                </li>
                <li class="nav-item">
                    <a href="#features" class="nb-nav-link nav-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="#contact" class="nb-nav-link nav-link">Contact</a>
                </li>
                <li class="nav-item ms-lg-3">
                    <a href="login.php" class="btn-gold" id="adminLoginBtn">
                        <i class="bi bi-shield-lock-fill"></i> Admin
                    </a>
                </li>
            </ul>
        </div>

    </div>
</nav><!-- /navbar -->


<!-- ═══════════════════════════════════════════════════════════
     HERO SECTION
     ═══════════════════════════════════════════════════════════ -->
<section class="hero-section" id="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>

    <div class="container">
        <div class="row align-items-center">

            <!-- Left: Copy -->
            <div class="col-lg-6 hero-content">
                <div class="hero-tag">
                    <span class="tag-dot"></span>
                    Now Serving — Premium Blends
                </div>

                <h1 class="hero-title">
                    Every Sip Tells<br>
                    a <span class="highlight">Story</span>
                </h1>

                <p class="hero-subtitle">
                    From the highlands of Ethiopia to the volcanic slopes of Sumatra —
                    NexusBrew brings you handcrafted coffee experiences that go beyond
                    the ordinary cup.
                </p>

                <div class="hero-actions">
                    <a href="#featured" class="btn-gold" id="exploreMenuBtn">
                        <i class="bi bi-cup-hot-fill"></i>
                        Explore Menu
                    </a>
                    <a href="#features" class="btn-outline-gold" id="ourStoryBtn">
                        <i class="bi bi-play-circle"></i>
                        Our Story
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat-item">
                        <div class="hero-stat-number"><?= $totalProducts ?>+</div>
                        <div class="hero-stat-label">Menu Items</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-number"><?= $totalCategories ?>+</div>
                        <div class="hero-stat-label">Categories</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-number">100%</div>
                        <div class="hero-stat-label">Arabica Beans</div>
                    </div>
                </div>
            </div><!-- /col -->

            <!-- Right: Image -->
            <div class="col-lg-6 d-none d-lg-flex justify-content-end">
                <div class="hero-image-card">
                    <img
                        src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=700&q=85"
                        alt="Premium coffee at NexusBrew"
                        class="hero-coffee-img"
                    >

                    <!-- Float Card 1 -->
                    <div class="hero-float-card card-top">
                        <div class="float-card-label">Today's Special</div>
                        <div class="float-card-value">Caramel Macchiato</div>
                        <div class="float-card-sub">⭐ 4.9 · 200+ orders today</div>
                    </div>

                    <!-- Float Card 2 -->
                    <div class="hero-float-card card-bottom">
                        <div class="float-card-label">Fresh Roast</div>
                        <div class="float-card-value">Ethiopia Single Origin</div>
                        <div class="float-card-sub">🌿 Light · Fruity · Floral</div>
                    </div>

                </div>
            </div><!-- /col -->

        </div><!-- /row -->
    </div><!-- /container -->
</section><!-- /hero -->


<!-- ═══════════════════════════════════════════════════════════
     FEATURES STRIP
     ═══════════════════════════════════════════════════════════ -->
<section class="features-section py-4" id="features">
    <div class="container">
        <div class="row g-4">

            <div class="col-sm-6 col-lg-3">
                <div class="feature-item">
                    <div class="feature-icon">🌍</div>
                    <div>
                        <div class="feature-title">Single Origin</div>
                        <div class="feature-desc">Direct sourced from Ethiopia, Sumatra & Colombia</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="feature-item">
                    <div class="feature-icon">🔥</div>
                    <div>
                        <div class="feature-title">Small Batch Roasted</div>
                        <div class="feature-desc">Roasted fresh weekly in our micro-roastery</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="feature-item">
                    <div class="feature-icon">🚴</div>
                    <div>
                        <div class="feature-title">Fast Delivery</div>
                        <div class="feature-desc">Same-day delivery within city limits</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="feature-item">
                    <div class="feature-icon">♻️</div>
                    <div>
                        <div class="feature-title">Eco Packaging</div>
                        <div class="feature-desc">100% compostable and recyclable materials</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section><!-- /features -->


<!-- ═══════════════════════════════════════════════════════════
     FEATURED PRODUCTS
     ═══════════════════════════════════════════════════════════ -->
<section class="py-5" id="featured" style="background: var(--bg-primary);">
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <span class="section-tag">Our Menu</span>
            <h2 class="section-title">Handcrafted <span class="gradient-text">Favorites</span></h2>
            <div class="section-divider mx-auto"></div>
            <p style="color:var(--cream-muted);max-width:520px;margin:0 auto;font-size:0.9rem;line-height:1.8;">
                Each drink is crafted with precision, passion, and the finest ingredients
                from around the world.
            </p>
        </div>

        <!-- Product Grid -->
        <div class="row g-4">
            <?php if (empty($featured)): ?>
                <div class="col-12 text-center" style="color:var(--text-muted);padding:3rem 0;">
                    <i class="bi bi-cup" style="font-size:3rem;color:var(--border-hover);"></i>
                    <p class="mt-3">No products available yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($featured as $product): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="product-card">
                            <div class="product-card-img">
                                <img
                                    src="<?= htmlspecialchars($product['image'] ?? 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600') ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    loading="lazy"
                                >
                                <?php if (!empty($product['category_name'])): ?>
                                    <span class="product-badge">
                                        <?= htmlspecialchars($product['category_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="product-card-body">
                                <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                                <div class="product-footer">
                                    <div class="product-price">
                                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                    </div>
                                    <button class="btn-add-cart" title="Add to Cart" id="addCart<?= $product['id'] ?>">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div><!-- /row -->

    </div>
</section><!-- /featured -->


<!-- ═══════════════════════════════════════════════════════════
     CTA SECTION
     ═══════════════════════════════════════════════════════════ -->
<section class="cta-section py-5" id="contact">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-7 mb-4 mb-lg-0">
                <span class="section-tag">Get in Touch</span>
                <h2 class="section-title">Visit Us or Order <span class="gradient-text">Online</span></h2>
                <div class="section-divider"></div>
                <p style="color:var(--cream-muted);font-size:0.9rem;line-height:1.8;max-width:480px;">
                    Step into NexusBrew and let us craft the perfect cup for you. Or order online
                    and we'll deliver freshness right to your door.
                </p>

                <div class="row mt-4 g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3">
                            <div class="feature-icon">📍</div>
                            <div>
                                <div style="font-weight:600;color:var(--cream);font-size:0.9rem;">Location</div>
                                <div style="font-size:0.82rem;color:var(--cream-muted);margin-top:3px;">Jl. Sudirman No. 42<br>Jakarta Pusat, 10220</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3">
                            <div class="feature-icon">🕐</div>
                            <div>
                                <div style="font-weight:600;color:var(--cream);font-size:0.9rem;">Hours</div>
                                <div style="font-size:0.82rem;color:var(--cream-muted);margin-top:3px;">Mon–Fri: 7am – 9pm<br>Sat–Sun: 8am – 10pm</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="admin-card">
                    <div class="admin-card-body">
                        <h5 style="color:var(--cream);font-family:'Playfair Display',serif;margin-bottom:1.25rem;">
                            Send Us a Message
                        </h5>

                        <!-- VULNERABILITY: XSS - Output not sanitized in message display  -->
                        <!-- CWE-79 | Detected by: Semgrep                                 -->
                        <!-- This form has no XSS protection on the server-side response    -->
                        <form id="contactForm" onsubmit="return false;">
                            <div class="nb-form-group">
                                <label class="nb-label">Your Name</label>
                                <input type="text" class="nb-input" id="contactName" placeholder="John Doe">
                            </div>
                            <div class="nb-form-group">
                                <label class="nb-label">Email</label>
                                <input type="email" class="nb-input" id="contactEmail" placeholder="you@example.com">
                            </div>
                            <div class="nb-form-group">
                                <label class="nb-label">Message</label>
                                <textarea class="nb-textarea" id="contactMessage" placeholder="Tell us what you love about coffee..."></textarea>
                            </div>
                            <button type="submit" class="btn-gold w-100" style="justify-content:center;">
                                <i class="bi bi-send-fill me-2"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section><!-- /cta -->


<!-- ═══════════════════════════════════════════════════════════
     FOOTER
     ═══════════════════════════════════════════════════════════ -->
<footer class="nb-footer" id="footer">
    <div class="container">
        <div class="row g-4">

            <div class="col-lg-4">
                <div class="footer-brand">☕ NexusBrew</div>
                <p class="footer-desc">
                    Crafting extraordinary coffee experiences since 2024.
                    Sourced with care, roasted with passion, served with love.
                </p>
            </div>

            <div class="col-6 col-lg-2">
                <div style="font-size:0.8rem;font-weight:600;color:var(--cream-muted);letter-spacing:0.5px;text-transform:uppercase;margin-bottom:1rem;">Menu</div>
                <a href="#" class="footer-link">Hot Coffee</a>
                <a href="#" class="footer-link">Cold Brew</a>
                <a href="#" class="footer-link">Espresso</a>
                <a href="#" class="footer-link">Specialty</a>
            </div>

            <div class="col-6 col-lg-2">
                <div style="font-size:0.8rem;font-weight:600;color:var(--cream-muted);letter-spacing:0.5px;text-transform:uppercase;margin-bottom:1rem;">Company</div>
                <a href="#" class="footer-link">About Us</a>
                <a href="#" class="footer-link">Careers</a>
                <a href="#" class="footer-link">Blog</a>
                <a href="login.php" class="footer-link">Admin</a>
            </div>

            <div class="col-lg-4">
                <div style="font-size:0.8rem;font-weight:600;color:var(--cream-muted);letter-spacing:0.5px;text-transform:uppercase;margin-bottom:1rem;">Stay Connected</div>
                <p class="footer-desc">Subscribe to our newsletter for brewing tips and exclusive offers.</p>
                <div class="d-flex gap-2 mt-3">
                    <input type="email" class="nb-input" placeholder="your@email.com" style="flex:1;">
                    <button class="btn-gold" style="white-space:nowrap;padding:0.7rem 1.2rem;">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>

        </div><!-- /row -->

        <div class="footer-copy d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>© 2024 NexusBrew Coffee Shop. All rights reserved.</span>
            <span style="color:var(--text-muted);">
                ⚠ DevSecOps Demo Application — Intentionally Vulnerable
            </span>
        </div>

    </div>
</footer><!-- /footer -->


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
