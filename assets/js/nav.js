// JavaScript Document
function toggleMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');
    
    menuToggle.classList.toggle('active'); // Toggle class for animation
    mainNav.classList.toggle('active'); // Toggle class to show/hide menu
}

