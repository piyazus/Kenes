document.addEventListener('DOMContentLoaded', () => {
    const mobileMenu = document.querySelector('.navbar-menu');
    const navToggle = document.querySelector('.navbar-toggle');
    const navLinks = document.querySelectorAll('.navbar-menu li a');

    if (navToggle) {
        navToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }

    // Close menu when clicking a link
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');
                navToggle.classList.remove('active');
            }
        });
    });

    // Highlight active link based on current URL
    const currentLocation = location.href;
    const menuItem = document.querySelectorAll('.navbar-menu li a');
    const menuLength = menuItem.length;
    for (let i = 0; i < menuLength; i++) {
        if (menuItem[i].href === currentLocation) {
            menuItem[i].classList.add("active");
        }
    }
});
