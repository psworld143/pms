/**
 * POS Sidebar JavaScript Functions
 * Handles sidebar interactions, dropdowns, and mobile responsiveness
 */

// Sidebar functionality - matching booking system
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (window.innerWidth < 1024) {
        const isOpen = sidebar.getAttribute('data-mobile-open') === 'true';
        
        if (isOpen) {
            sidebar.setAttribute('data-mobile-open', 'false');
            sidebar.style.transform = 'translateX(-100%)';
            if (overlay) {
                overlay.classList.add('hidden');
            }
        } else {
            sidebar.setAttribute('data-mobile-open', 'true');
            sidebar.style.transform = 'translateX(0)';
            if (overlay) {
                overlay.classList.remove('hidden');
            }
        }
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    sidebar.setAttribute('data-mobile-open', 'false');
    sidebar.style.transform = 'translateX(-100%)';
    if (overlay) {
        overlay.classList.add('hidden');
    }
}

// Submenu functionality
function toggleSubmenu(menuKey) {
    console.log('toggleSubmenu called with key:', menuKey);
    const submenu = document.getElementById(`submenu-${menuKey}`);
    const chevron = document.getElementById(`chevron-${menuKey}`);
    
    console.log('Submenu element:', submenu);
    console.log('Chevron element:', chevron);
    
    if (submenu && chevron) {
        if (submenu.classList.contains('hidden')) {
            console.log('Showing submenu for:', menuKey);
            submenu.classList.remove('hidden');
            chevron.style.transform = 'rotate(180deg)';
        } else {
            console.log('Hiding submenu for:', menuKey);
            submenu.classList.add('hidden');
            chevron.style.transform = 'rotate(0deg)';
        }
    } else {
        console.error('Could not find submenu or chevron for:', menuKey);
    }
}

// Initialize sidebar functionality
function initializePOSSidebar() {
    // Sidebar toggle event listener
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Note: Dropdowns are handled by pos-header.php inline script
    // This keeps code simple and matches the booking system approach
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializePOSSidebar();
    
    // Close sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            const sidebar = document.getElementById('sidebar');
            sidebar.style.transform = 'translateX(0)';
        }
    });
});

// Export functions for global use
window.toggleSidebar = toggleSidebar;
window.closeSidebar = closeSidebar;
window.toggleSubmenu = toggleSubmenu;
