/**
 * Navbar — mobile toggle, active link highlighting
 */
document.addEventListener('DOMContentLoaded', function() {
    // Mobile hamburger toggle
    var toggle = document.getElementById('navToggle');
    var menu = document.getElementById('navMenu');
    
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            menu.classList.toggle('open');
        });
    }

    // Highlight active nav link based on current page
    var currentPage = window.location.pathname.split('/').pop() || 'index.php';
    var navLinks = document.querySelectorAll('.navbar-menu a');
    navLinks.forEach(function(link) {
        var href = link.getAttribute('href');
        if (href && href.includes(currentPage)) {
            link.classList.add('active');
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (menu && menu.classList.contains('open') && !menu.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
            menu.classList.remove('open');
        }
    });

    // Language switcher — preserve current page URL params
    document.querySelectorAll('.lang-switcher a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var url = new URL(window.location.href);
            var lang = this.href.split('lang=')[1];
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        });
    });
});
