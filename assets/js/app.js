/**
 * NexusBrew - Main JavaScript
 * DevSecOps Demo Application
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Navbar scroll effect ──────────────────────────────────
    const navbar = document.querySelector('.nb-navbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ── Mobile sidebar toggle ─────────────────────────────────
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
        });
    }

    // ── Delete confirmation ───────────────────────────────────
    const deleteLinks = document.querySelectorAll('a[data-confirm], button[data-confirm]');
    deleteLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    const confirmForms = document.querySelectorAll('form[data-confirm]');
    confirmForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // ── Auto-dismiss alerts ───────────────────────────────────
    const alerts = document.querySelectorAll('.auto-dismiss');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 4000);
    });

    // ── Image preview for product form ────────────────────────
    const imageUrlInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');

    if (imageUrlInput && imagePreview) {
        imageUrlInput.addEventListener('input', function () {
            const url = this.value.trim();
            if (url) {
                imagePreview.src = url;
                imagePreview.style.display = 'block';
                imagePreview.onerror = function () {
                    imagePreview.style.display = 'none';
                };
            } else {
                imagePreview.style.display = 'none';
            }
        });

        // Trigger on load if value already exists
        if (imageUrlInput.value) {
            imageUrlInput.dispatchEvent(new Event('input'));
        }
    }

    // ── Animate stat numbers ──────────────────────────────────
    const statNumbers = document.querySelectorAll('.stat-number[data-count]');
    statNumbers.forEach(function (el) {
        const target = parseInt(el.getAttribute('data-count'), 10);
        let current = 0;
        const duration = 1200;
        const step = target / (duration / 16);

        const timer = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = Math.floor(current).toLocaleString();
        }, 16);
    });

    // ── Price formatter ───────────────────────────────────────
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('blur', function () {
            const val = parseFloat(this.value);
            if (!isNaN(val)) {
                this.value = val.toFixed(2);
            }
        });
    }

    // ── Search form live submit ───────────────────────────────
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                searchInput.closest('form').submit();
            }, 500);
        });
    }

    // ── Tooltip init ──────────────────────────────────────────
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipEls.forEach(function (el) {
            new bootstrap.Tooltip(el, { trigger: 'hover' });
        });
    }

});
