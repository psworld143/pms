/**
 * PMS Shared JavaScript
 * Unified JavaScript for all PMS modules
 */

class PMSSystem {
    constructor() {
        this.currentModule = 'dashboard';
        this.isLoading = false;
        this.notifications = [];
        this.init();
    }
    
    /**
     * Initialize PMS system
     */
    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.startPeriodicUpdates();
        console.log('PMS System initialized');
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => this.toggleSidebar());
        }
        
        // Form submissions
        document.addEventListener('submit', (e) => this.handleFormSubmit(e));
        
        // Button clicks
        document.addEventListener('click', (e) => this.handleButtonClick(e));
        
        // Window resize
        window.addEventListener('resize', () => this.handleResize());
        
        // Before unload
        window.addEventListener('beforeunload', () => this.handleBeforeUnload());
    }
    
    /**
     * Initialize components
     */
    initializeComponents() {
        this.initializeDatePickers();
        this.initializeDataTables();
        this.initializeModals();
        this.initializeTooltips();
        this.updateDateTime();
    }
    
    /**
     * Start periodic updates
     */
    startPeriodicUpdates() {
        // Update date/time every minute
        setInterval(() => this.updateDateTime(), 60000);
        
        // Check for notifications every 30 seconds
        setInterval(() => this.checkNotifications(), 30000);
        
        // Update dashboard stats every 5 minutes
        setInterval(() => this.updateDashboardStats(), 300000);
    }
    
    /**
     * Toggle sidebar on mobile
     */
    toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('open');
        }
    }
    
    /**
     * Handle form submissions
     */
    handleFormSubmit(e) {
        const form = e.target;
        if (form.classList.contains('pms-form')) {
            e.preventDefault();
            this.submitForm(form);
        }
    }
    
    /**
     * Handle button clicks
     */
    handleButtonClick(e) {
        const button = e.target.closest('button, .btn');
        if (!button) return;
        
        const action = button.dataset.action;
        if (action) {
            e.preventDefault();
            this.handleAction(action, button);
        }
    }
    
    /**
     * Handle window resize
     */
    handleResize() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        
        if (window.innerWidth <= 768) {
            sidebar?.classList.remove('open');
        } else {
            sidebar?.classList.add('open');
        }
    }
    
    /**
     * Handle before unload
     */
    handleBeforeUnload() {
        // Save any pending data
        this.savePendingData();
    }
    
    /**
     * Submit form via AJAX
     */
    async submitForm(form) {
        const formData = new FormData(form);
        const url = form.action || window.location.href;
        const method = form.method || 'POST';
        
        this.setLoading(true);
        
        try {
            const response = await fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Success', result.message || 'Operation completed successfully', 'success');
                if (result.redirect) {
                    window.location.href = result.redirect;
                }
            } else {
                this.showNotification('Error', result.message || 'An error occurred', 'error');
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.showNotification('Error', 'Network error occurred', 'error');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Handle action buttons
     */
    async handleAction(action, button) {
        const url = button.dataset.url || window.location.href;
        const data = button.dataset.data ? JSON.parse(button.dataset.data) : {};
        
        this.setLoading(true);
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ action, ...data })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Success', result.message || 'Action completed successfully', 'success');
                if (result.redirect) {
                    window.location.href = result.redirect;
                } else if (result.reload) {
                    window.location.reload();
                }
            } else {
                this.showNotification('Error', result.message || 'Action failed', 'error');
            }
        } catch (error) {
            console.error('Action error:', error);
            this.showNotification('Error', 'Network error occurred', 'error');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Set loading state
     */
    setLoading(loading) {
        this.isLoading = loading;
        const buttons = document.querySelectorAll('button, .btn');
        buttons.forEach(button => {
            if (loading) {
                button.disabled = true;
                button.classList.add('loading');
            } else {
                button.disabled = false;
                button.classList.remove('loading');
            }
        });
    }
    
    /**
     * Show notification
     */
    showNotification(title, message, type = 'info') {
        const notification = {
            id: Date.now(),
            title,
            message,
            type,
            timestamp: new Date()
        };
        
        this.notifications.push(notification);
        this.renderNotification(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            this.removeNotification(notification.id);
        }, 5000);
    }
    
    /**
     * Render notification
     */
    renderNotification(notification) {
        const container = document.getElementById('notifications') || this.createNotificationContainer();
        
        const notificationEl = document.createElement('div');
        notificationEl.className = `alert alert-${notification.type}`;
        notificationEl.dataset.id = notification.id;
        
        notificationEl.innerHTML = `
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-semibold">${notification.title}</h4>
                    <p class="text-sm">${notification.message}</p>
                </div>
                <button class="btn btn-sm btn-outline" onclick="pmsSystem.removeNotification(${notification.id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        container.appendChild(notificationEl);
        
        // Animate in
        setTimeout(() => {
            notificationEl.classList.add('animate-in');
        }, 100);
    }
    
    /**
     * Create notification container
     */
    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'notifications';
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
        return container;
    }
    
    /**
     * Remove notification
     */
    removeNotification(id) {
        const notificationEl = document.querySelector(`[data-id="${id}"]`);
        if (notificationEl) {
            notificationEl.classList.add('animate-out');
            setTimeout(() => {
                notificationEl.remove();
            }, 300);
        }
        
        this.notifications = this.notifications.filter(n => n.id !== id);
    }
    
    /**
     * Initialize date pickers
     */
    initializeDatePickers() {
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            if (!input.value) {
                input.value = new Date().toISOString().split('T')[0];
            }
        });
    }
    
    /**
     * Initialize data tables
     */
    initializeDataTables() {
        const tables = document.querySelectorAll('.data-table');
        tables.forEach(table => {
            this.enhanceTable(table);
        });
    }
    
    /**
     * Enhance table functionality
     */
    enhanceTable(table) {
        // Add sorting
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => this.sortTable(table, header.dataset.sort));
        });
        
        // Add search
        const searchInput = table.querySelector('.table-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.filterTable(table, e.target.value));
        }
    }
    
    /**
     * Sort table
     */
    sortTable(table, column) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aVal = a.querySelector(`td[data-sort="${column}"]`)?.textContent || '';
            const bVal = b.querySelector(`td[data-sort="${column}"]`)?.textContent || '';
            
            return aVal.localeCompare(bVal);
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }
    
    /**
     * Filter table
     */
    filterTable(table, searchTerm) {
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(searchTerm.toLowerCase());
            row.style.display = matches ? '' : 'none';
        });
    }
    
    /**
     * Initialize modals
     */
    initializeModals() {
        const modalTriggers = document.querySelectorAll('[data-modal]');
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = trigger.dataset.modal;
                this.openModal(modalId);
            });
        });
        
        const modalCloses = document.querySelectorAll('.modal-close');
        modalCloses.forEach(close => {
            close.addEventListener('click', () => this.closeModal());
        });
    }
    
    /**
     * Open modal
     */
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('open');
            document.body.classList.add('modal-open');
        }
    }
    
    /**
     * Close modal
     */
    closeModal() {
        const modals = document.querySelectorAll('.modal.open');
        modals.forEach(modal => {
            modal.classList.remove('open');
        });
        document.body.classList.remove('modal-open');
    }
    
    /**
     * Initialize tooltips
     */
    initializeTooltips() {
        const tooltips = document.querySelectorAll('[data-tooltip]');
        tooltips.forEach(element => {
            element.addEventListener('mouseenter', (e) => this.showTooltip(e));
            element.addEventListener('mouseleave', () => this.hideTooltip());
        });
    }
    
    /**
     * Show tooltip
     */
    showTooltip(e) {
        const text = e.target.dataset.tooltip;
        if (!text) return;
        
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: #333;
            color: white;
            padding: 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            z-index: 1000;
            pointer-events: none;
        `;
        
        document.body.appendChild(tooltip);
        
        const rect = e.target.getBoundingClientRect();
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    }
    
    /**
     * Hide tooltip
     */
    hideTooltip() {
        const tooltip = document.querySelector('.tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }
    
    /**
     * Update date and time
     */
    updateDateTime() {
        const now = new Date();
        const dateStr = now.toLocaleDateString();
        const timeStr = now.toLocaleTimeString();
        
        const dateEl = document.getElementById('current-date');
        const timeEl = document.getElementById('current-time');
        
        if (dateEl) dateEl.textContent = dateStr;
        if (timeEl) timeEl.textContent = timeStr;
    }
    
    /**
     * Check for notifications
     */
    async checkNotifications() {
        try {
            const response = await fetch('/seait/pms/api/notifications.php');
            const notifications = await response.json();
            
            notifications.forEach(notification => {
                if (!this.notifications.find(n => n.id === notification.id)) {
                    this.showNotification(
                        notification.title,
                        notification.message,
                        notification.type
                    );
                }
            });
        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }
    
    /**
     * Update dashboard stats
     */
    async updateDashboardStats() {
        const statsElements = document.querySelectorAll('[data-stat]');
        if (statsElements.length === 0) return;
        
        try {
            const response = await fetch('/seait/pms/api/dashboard-stats.php');
            const stats = await response.json();
            
            statsElements.forEach(element => {
                const statName = element.dataset.stat;
                if (stats[statName] !== undefined) {
                    element.textContent = stats[statName];
                }
            });
        } catch (error) {
            console.error('Error updating dashboard stats:', error);
        }
    }
    
    /**
     * Save pending data
     */
    savePendingData() {
        // Save form data to localStorage
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            localStorage.setItem(`form_${form.id || 'default'}`, JSON.stringify(data));
        });
    }
    
    /**
     * Restore pending data
     */
    restorePendingData() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const data = localStorage.getItem(`form_${form.id || 'default'}`);
            if (data) {
                try {
                    const parsedData = JSON.parse(data);
                    Object.entries(parsedData).forEach(([key, value]) => {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.value = value;
                        }
                    });
                } catch (error) {
                    console.error('Error restoring form data:', error);
                }
            }
        });
    }
}

// Initialize PMS system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.pmsSystem = new PMSSystem();
});

// Export for use in other scripts
window.PMSSystem = PMSSystem;
