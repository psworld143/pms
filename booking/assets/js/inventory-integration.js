/**
 * Inventory Integration JavaScript
 * Handles integration between booking and inventory systems
 */

class InventoryIntegration {
    constructor() {
        this.apiBase = '../../api/inventory-integration.php';
        this.init();
    }

    init() {
        // Add inventory widgets to booking dashboards
        this.addInventoryWidgets();
        
        // Add inventory status to room management
        this.addRoomInventoryStatus();
        
        // Add low stock alerts
        this.addLowStockAlerts();
    }

    /**
     * Add inventory widgets to booking dashboards
     */
    addInventoryWidgets() {
        const dashboard = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4');
        if (!dashboard) return;

        // Create inventory status widget
        const inventoryWidget = document.createElement('div');
        inventoryWidget.className = 'bg-white rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow';
        inventoryWidget.innerHTML = `
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-r from-orange-400 to-orange-600 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-boxes text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-gray-800" id="inventory-status-count">-</h3>
                    <p class="text-gray-600">Low Stock Items</p>
                </div>
            </div>
        `;

        // Insert after the fourth widget
        dashboard.appendChild(inventoryWidget);

        // Load inventory status
        this.loadInventoryStatus();
    }

    /**
     * Load inventory status for dashboard
     */
    async loadInventoryStatus() {
        try {
            const response = await fetch(`${this.apiBase}?action=get_inventory_status`);
            const data = await response.json();

            if (data.success) {
                document.getElementById('inventory-status-count').textContent = data.low_stock_items;
                
                // Add click handler to show details
                const widget = document.querySelector('.bg-white.rounded-lg.p-6.shadow-md:last-child');
                widget.style.cursor = 'pointer';
                widget.addEventListener('click', () => this.showInventoryDetails());
            }
        } catch (error) {
            console.error('Error loading inventory status:', error);
        }
    }

    /**
     * Show inventory details modal
     */
    async showInventoryDetails() {
        try {
            const response = await fetch(`${this.apiBase}?action=get_low_stock_alerts`);
            const data = await response.json();

            if (data.success) {
                this.createInventoryModal(data.alerts);
            }
        } catch (error) {
            console.error('Error loading inventory details:', error);
        }
    }

    /**
     * Create inventory details modal
     */
    createInventoryModal(alerts) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Inventory Status</h3>
                    <button class="text-gray-500 hover:text-gray-700" onclick="this.closest('.fixed').remove()">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    ${alerts.length === 0 ? 
                        '<div class="text-center py-8 text-gray-500">No low stock alerts</div>' :
                        alerts.map(alert => `
                            <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-red-800">${alert.item_name}</h4>
                                        <p class="text-sm text-red-600">SKU: ${alert.sku}</p>
                                        <p class="text-sm text-red-600">
                                            Current: ${alert.current_stock} ${alert.unit} | 
                                            Minimum: ${alert.minimum_stock} ${alert.unit}
                                        </p>
                                        <p class="text-sm text-red-600">
                                            Affects ${alert.rooms_affected} room(s)
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Low Stock
                                        </span>
                                    </div>
                                </div>
                            </div>
                        `).join('')
                    }
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400" 
                            onclick="this.closest('.fixed').remove()">
                        Close
                    </button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                            onclick="window.open('../../inventory/index.php', '_blank')">
                        Manage Inventory
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    }

    /**
     * Add inventory status to room management
     */
    addRoomInventoryStatus() {
        // This will be called when room management pages are loaded
        const roomCards = document.querySelectorAll('[data-room-id]');
        roomCards.forEach(card => {
            const roomId = card.getAttribute('data-room-id');
            if (roomId) {
                this.addRoomInventoryInfo(card, roomId);
            }
        });
    }

    /**
     * Add inventory info to room card
     */
    async addRoomInventoryInfo(roomCard, roomId) {
        try {
            const response = await fetch(`${this.apiBase}?action=get_room_inventory&room_id=${roomId}`);
            const data = await response.json();

            if (data.success && data.items.length > 0) {
                const inventoryInfo = document.createElement('div');
                inventoryInfo.className = 'mt-2 p-2 bg-gray-50 rounded text-xs';
                
                const lowStockItems = data.items.filter(item => item.quantity_current < item.par_level);
                
                if (lowStockItems.length > 0) {
                    inventoryInfo.innerHTML = `
                        <div class="flex items-center text-orange-600">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <span>${lowStockItems.length} item(s) need restocking</span>
                        </div>
                    `;
                } else {
                    inventoryInfo.innerHTML = `
                        <div class="flex items-center text-green-600">
                            <i class="fas fa-check-circle mr-1"></i>
                            <span>Inventory levels OK</span>
                        </div>
                    `;
                }

                roomCard.appendChild(inventoryInfo);
            }
        } catch (error) {
            console.error('Error loading room inventory info:', error);
        }
    }

    /**
     * Add low stock alerts to header
     */
    addLowStockAlerts() {
        const header = document.querySelector('header');
        if (!header) return;

        // Create alerts container
        const alertsContainer = document.createElement('div');
        alertsContainer.id = 'inventory-alerts';
        alertsContainer.className = 'hidden';

        header.appendChild(alertsContainer);

        // Load alerts
        this.loadLowStockAlerts();
    }

    /**
     * Load low stock alerts
     */
    async loadLowStockAlerts() {
        try {
            const response = await fetch(`${this.apiBase}?action=get_low_stock_alerts`);
            const data = await response.json();

            if (data.success && data.alerts.length > 0) {
                const alertsContainer = document.getElementById('inventory-alerts');
                alertsContainer.className = 'bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4';
                alertsContainer.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <div>
                            <p class="font-semibold">Low Stock Alert</p>
                            <p class="text-sm">${data.alerts.length} item(s) are below minimum stock levels</p>
                        </div>
                        <button class="ml-auto text-red-500 hover:text-red-700" 
                                onclick="this.parentElement.parentElement.classList.add('hidden')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading low stock alerts:', error);
        }
    }

    /**
     * Record inventory usage (for housekeeping/frontdesk)
     */
    async recordUsage(roomId, itemId, quantity, reason = 'Room usage') {
        try {
            const formData = new FormData();
            formData.append('action', 'record_inventory_usage');
            formData.append('room_id', roomId);
            formData.append('item_id', itemId);
            formData.append('quantity_used', quantity);
            formData.append('reason', reason);

            const response = await fetch(this.apiBase, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Utils.showNotification('Inventory usage recorded successfully', 'success');
                return data.quantity_remaining;
            } else {
                Utils.showNotification(data.message, 'error');
                return null;
            }
        } catch (error) {
            console.error('Error recording inventory usage:', error);
            Utils.showNotification('Error recording usage', 'error');
            return null;
        }
    }

    /**
     * Update room inventory (for managers)
     */
    async updateRoomInventory(roomId, itemId, quantity, notes = '') {
        try {
            const formData = new FormData();
            formData.append('action', 'update_room_inventory');
            formData.append('room_id', roomId);
            formData.append('item_id', itemId);
            formData.append('quantity_current', quantity);
            formData.append('notes', notes);

            const response = await fetch(this.apiBase, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Utils.showNotification('Room inventory updated successfully', 'success');
                return true;
            } else {
                Utils.showNotification(data.message, 'error');
                return false;
            }
        } catch (error) {
            console.error('Error updating room inventory:', error);
            Utils.showNotification('Error updating inventory', 'error');
            return false;
        }
    }
}

// Initialize inventory integration when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if user has appropriate role
    const userRole = document.body.getAttribute('data-user-role') || 
                   (window.location.pathname.includes('manager') ? 'manager' : 
                    window.location.pathname.includes('front-desk') ? 'front_desk' : null);
    
    if (userRole && ['manager', 'front_desk'].includes(userRole)) {
        window.inventoryIntegration = new InventoryIntegration();
    }
});

// Export for use in other modules
window.InventoryIntegration = InventoryIntegration;
