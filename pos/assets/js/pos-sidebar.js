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

// User dropdown functionality
function toggleUserDropdown() {
    const dropdown = document.getElementById('user-dropdown');
    dropdown.classList.toggle('hidden');
}

// Notifications functionality
function toggleNotifications() {
    const dropdown = document.getElementById('notifications-dropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
function setupDropdownHandlers() {
    document.addEventListener('click', function(event) {
        const userDropdown = document.getElementById('user-dropdown');
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        
        // Close user dropdown if clicking outside
        if (!event.target.closest('#user-menu-toggle') && !event.target.closest('#user-dropdown')) {
            if (userDropdown) userDropdown.classList.add('hidden');
        }
        
        // Close notifications dropdown if clicking outside
        if (!event.target.closest('#notifications-toggle') && !event.target.closest('#notifications-dropdown')) {
            if (notificationsDropdown) notificationsDropdown.classList.add('hidden');
        }
    });
}

// Initialize sidebar functionality
function initializePOSSidebar() {
    // Sidebar toggle event listener
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // User menu dropdown event listener
    const userMenuToggle = document.getElementById('user-menu-toggle');
    if (userMenuToggle) {
        userMenuToggle.addEventListener('click', toggleUserDropdown);
    }
    
    // Notifications toggle event listener
    const notificationsToggle = document.getElementById('notifications-toggle');
    if (notificationsToggle) {
        notificationsToggle.addEventListener('click', toggleNotifications);
    }
    
    // Setup dropdown handlers
    setupDropdownHandlers();
    
    // Initialize any other sidebar functionality
    console.log('POS Sidebar initialized successfully');
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
window.toggleUserDropdown = toggleUserDropdown;
window.toggleNotifications = toggleNotifications;
