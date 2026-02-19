document.addEventListener('DOMContentLoaded', () => {
    const mobileMenus = document.querySelectorAll('.navbar-menu');
    const navToggle = document.querySelector('.navbar-toggle');
    const navLinks = document.querySelectorAll('.navbar-menu li a');

    if (navToggle) {
        navToggle.addEventListener('click', () => {
            mobileMenus.forEach(menu => menu.classList.toggle('active'));
            navToggle.classList.toggle('active');
        });
    }


    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (mobileMenus[0].classList.contains('active')) { // Check first one is enough
                mobileMenus.forEach(menu => menu.classList.remove('active'));
                navToggle.classList.remove('active');
            }
        });
    });


    const currentLocation = location.href;
    const menuItem = document.querySelectorAll('.navbar-menu li a');
    const menuLength = menuItem.length;
    for (let i = 0; i < menuLength; i++) {
        if (menuItem[i].href === currentLocation) {
            menuItem[i].classList.add("active");
        }
    }
});
