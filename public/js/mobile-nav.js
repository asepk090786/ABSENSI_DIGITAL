/**
 * Mobile Navigation Enhancement Script
 * Improves mobile responsiveness and usability
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile navigation
    initMobileNavigation();
    
    // Handle sidebar toggle on mobile
    handleSidebarToggle();
    
    // Improve touch interactions
    improveTouch();
    
    // Handle orientation changes
    handleOrientationChange();
    
    // Close sidebar when clicking outside
    handleSidebarClickOutside();
});

/**
 * Initialize mobile navigation
 */
function initMobileNavigation() {
    const navbar = document.querySelector('.navbar-vertical');
    if (navbar && window.innerWidth <= 768) {
        navbar.classList.add('mobile-sidebar');
    }
}

/**
 * Handle sidebar toggle
 */
function handleSidebarToggle() {
    const toggler = document.getElementById('toggleSidebarBtn');
    const sidebar = document.querySelector('.navbar-vertical');
    const navbarCollapse = document.getElementById('sidebar-menu');
    const overlay = document.querySelector('.sidebar-overlay');
    const body = document.body;
    
    if (toggler && sidebar && navbarCollapse) {
        const closeSidebar = function () {
            sidebar.classList.remove('show');
            navbarCollapse.classList.remove('show');
            if (overlay) {
                overlay.classList.remove('show');
            }
            body.classList.remove('mobile-sidebar-open');
            document.body.style.overflow = 'auto';
        };

        toggler.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = sidebar.classList.toggle('show');
            navbarCollapse.classList.toggle('show', isOpen);
            if (overlay) {
                overlay.classList.toggle('show', isOpen);
            }
            body.classList.toggle('mobile-sidebar-open', isOpen);
            document.body.style.overflow = isOpen ? 'hidden' : 'auto';
        });

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        window.addEventListener('resize', function() {
            if (window.innerWidth > 992 && body.classList.contains('mobile-sidebar-open')) {
                closeSidebar();
            }
        });
    }
}

/**
 * Close sidebar when clicking outside
 */
function handleSidebarClickOutside() {
    const sidebar = document.querySelector('.navbar-vertical');
    const navbarCollapse = sidebar ? sidebar.querySelector('.navbar-collapse') : document.querySelector('#sidebar-menu');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar && navbarCollapse) {
        const closeSidebar = function() {
            navbarCollapse.classList.remove('show');
            sidebar.classList.remove('show');
            if (overlay) {
                overlay.classList.remove('show');
            }
            document.body.style.overflow = 'auto';
        };

        document.addEventListener('click', function(e) {
            const isClickInsideSidebar = sidebar.contains(e.target);
            const isClickOnToggler = e.target.closest('.navbar-toggler');

            if (!isClickInsideSidebar && !isClickOnToggler && navbarCollapse.classList.contains('show')) {
                closeSidebar();
            }
        });

        if (overlay) {
            overlay.addEventListener('click', function() {
                closeSidebar();
            });
        }
    }
}

/**
 * Improve touch interactions
 */
function improveTouch() {
    // Enhance dropdown menus for touch
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('touchend', function(e) {
            // Prevent the default behavior and manually trigger click
            e.preventDefault();
            this.click();
        });
    });
    
    // Make touch targets larger on mobile
    const links = document.querySelectorAll('a, button');
    links.forEach(link => {
        const rect = link.getBoundingClientRect();
        if (rect.height < 44) {
            link.style.padding = '0.5rem';
        }
    });
}

/**
 * Handle orientation changes
 */
function handleOrientationChange() {
    window.addEventListener('orientationchange', function() {
        // Close sidebar on orientation change
        const sidebar = document.querySelector('.navbar-vertical');
        const navbarCollapse = document.querySelector('.navbar-collapse');
        
        if (sidebar && navbarCollapse) {
            navbarCollapse.classList.remove('show');
            sidebar.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
        
        // Reflow tables
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            table.style.minWidth = 'auto';
        });
    });
}

/**
 * Utility: Check if device is mobile
 */
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) 
        || window.innerWidth <= 768;
}

/**
 * Utility: Close all modals on mobile
 */
function closeAllModals() {
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach(modal => {
        modal.classList.remove('show');
        modal.style.display = 'none';
    });
}

/**
 * Improve form inputs for mobile
 */
function improveFormInputs() {
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="number"], textarea');
    
    inputs.forEach(input => {
        // Increase font size to prevent zoom on iOS
        input.style.fontSize = '16px';
        
        // Add padding for better touch interaction
        input.style.padding = '0.75rem';
        
        // Add focus handlers
        input.addEventListener('focus', function() {
            // Scroll into view with padding
            this.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });
}

/**
 * Handle responsive table scrolling
 */
function handleTableResponsiveness() {
    const tables = document.querySelectorAll('table');
    
    tables.forEach(table => {
        if (window.innerWidth <= 768) {
            const wrapper = table.parentElement;
            if (wrapper && !wrapper.classList.contains('table-responsive')) {
                wrapper.classList.add('table-responsive');
                wrapper.style.overflowX = 'auto';
                wrapper.style.WebkitOverflowScrolling = 'touch';
            }
        }
    });
}

/**
 * Initialize all mobile enhancements
 */
window.addEventListener('load', function() {
    improveFormInputs();
    handleTableResponsiveness();
});

/**
 * Handle window resize
 */
window.addEventListener('resize', function() {
    // Re-check if device is mobile
    const navbar = document.querySelector('.navbar-vertical');
    const page = document.querySelector('.page');
    
    if (window.innerWidth <= 768 && navbar) {
        page.style.marginLeft = '0';
    } else if (window.innerWidth > 768 && navbar) {
        page.style.marginLeft = '280px';
    }
});

// Export functions for external use
window.MobileNav = {
    isMobileDevice,
    closeAllModals,
    improveFormInputs,
    handleTableResponsiveness
};
