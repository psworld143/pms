/**
 * Billing & Payment JavaScript
 * Hotel PMS - Front Desk Module
 */

class BillingManager {
    constructor() {
        this.currentTab = 'bills';
        this.init();
    }

    init() {
        this.loadBills();
        this.setupEventListeners();
    }

    // Load bills
    async loadBills() {
        try {
            const response = await fetch('../../api/get-bills.php');
            const data = await response.json();
            
            if (data.success) {
                this.displayBills(data.bills);
            } else {
                console.error('Error loading bills:', data.message);
            }
        } catch (error) {
            console.error('Error loading bills:', error);
        }
    }

    // Display bills
    displayBills(bills) {
        const container = document.getElementById('bills-container');
        if (!container) return;

        if (bills.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-file-invoice text-4xl mb-4"></i>
                    <p>No bills found</p>
                </div>
            `;
            return;
        }

        const table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${bills.map(bill => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #${bill.bill_number}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${bill.guest_name}</div>
                                <div class="text-sm text-gray-500">Room ${bill.room_number}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱${parseFloat(bill.total_amount).toFixed(2)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${this.getStatusClass(bill.status)}">
                                    ${this.getStatusLabel(bill.status)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.formatDate(bill.created_at)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="viewBill(${bill.id})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editBill(${bill.id})" class="text-yellow-600 hover:text-yellow-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    ${bill.status === 'pending' ? `
                                        <button onclick="processPayment(${bill.id})" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;

        container.innerHTML = table;
    }

    // Setup event listeners
    setupEventListeners() {
        // Billing form submission
        const billingForm = document.getElementById('billing-form');
        if (billingForm) {
            billingForm.addEventListener('submit', this.handleBillingSubmit.bind(this));
        }

        // Discount form submission
        const discountForm = document.getElementById('discount-form');
        if (discountForm) {
            discountForm.addEventListener('submit', this.handleDiscountSubmit.bind(this));
        }

        // Voucher form submission
        const voucherForm = document.getElementById('voucher-form');
        if (voucherForm) {
            voucherForm.addEventListener('submit', this.handleVoucherSubmit.bind(this));
        }

        // Loyalty form submission
        const loyaltyForm = document.getElementById('loyalty-form');
        if (loyaltyForm) {
            loyaltyForm.addEventListener('submit', this.handleLoyaltySubmit.bind(this));
        }

        // Filter functionality
        const billStatusFilter = document.getElementById('bill-status-filter');
        const billDateFilter = document.getElementById('bill-date-filter');
        
        if (billStatusFilter) {
            billStatusFilter.addEventListener('change', this.handleBillFilter.bind(this));
        }
        
        if (billDateFilter) {
            billDateFilter.addEventListener('change', this.handleBillFilter.bind(this));
        }
    }

    // Handle billing form submission
    async handleBillingSubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = {
            reservation_id: formData.get('reservation_id'),
            bill_date: formData.get('bill_date'),
            items: this.getBillItems()
        };

        try {
            const response = await fetch('../../api/create-bill.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Bill created successfully!', 'success');
                this.closeBillingModal();
                this.loadBills();
            } else {
                this.showNotification(result.message || 'Error creating bill', 'error');
            }
        } catch (error) {
            console.error('Error creating bill:', error);
            this.showNotification('Error creating bill. Please try again.', 'error');
        }
    }

    // Handle discount form submission
    async handleDiscountSubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = {
            bill_id: formData.get('bill_id'),
            discount_type: formData.get('discount_type'),
            discount_value: formData.get('discount_value'),
            discount_reason: formData.get('discount_reason'),
            discount_description: formData.get('discount_description')
        };

        try {
            const response = await fetch('../../api/apply-discount.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Discount applied successfully!', 'success');
                this.closeDiscountModal();
                this.loadBills();
            } else {
                this.showNotification(result.message || 'Error applying discount', 'error');
            }
        } catch (error) {
            console.error('Error applying discount:', error);
            this.showNotification('Error applying discount. Please try again.', 'error');
        }
    }

    // Handle voucher form submission
    async handleVoucherSubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = {
            voucher_code: formData.get('voucher_code'),
            voucher_type: formData.get('voucher_type'),
            voucher_value: formData.get('voucher_value'),
            usage_limit: formData.get('usage_limit'),
            valid_from: formData.get('valid_from'),
            valid_until: formData.get('valid_until'),
            voucher_description: formData.get('voucher_description')
        };

        try {
            const response = await fetch('../../api/create-voucher.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Voucher created successfully!', 'success');
                this.closeVoucherModal();
                this.loadVouchers();
            } else {
                this.showNotification(result.message || 'Error creating voucher', 'error');
            }
        } catch (error) {
            console.error('Error creating voucher:', error);
            this.showNotification('Error creating voucher. Please try again.', 'error');
        }
    }

    // Handle loyalty form submission
    async handleLoyaltySubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = {
            guest_id: formData.get('guest_id'),
            loyalty_action: formData.get('loyalty_action'),
            loyalty_points: formData.get('loyalty_points'),
            loyalty_reason: formData.get('loyalty_reason'),
            loyalty_description: formData.get('loyalty_description')
        };

        try {
            const response = await fetch('../../api/process-loyalty.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Loyalty points processed successfully!', 'success');
                this.closeLoyaltyModal();
                this.loadLoyalty();
            } else {
                this.showNotification(result.message || 'Error processing loyalty points', 'error');
            }
        } catch (error) {
            console.error('Error processing loyalty points:', error);
            this.showNotification('Error processing loyalty points. Please try again.', 'error');
        }
    }

    // Handle bill filter
    handleBillFilter() {
        const status = document.getElementById('bill-status-filter').value;
        const date = document.getElementById('bill-date-filter').value;
        
        this.filterBills({
            status: status,
            date: date
        });
    }

    // Filter bills
    async filterBills(filters) {
        try {
            const params = new URLSearchParams();
            Object.keys(filters).forEach(key => {
                if (filters[key]) {
                    params.append(key, filters[key]);
                }
            });

            const response = await fetch(`../../api/filter-bills.php?${params.toString()}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayBills(data.bills);
            } else {
                console.error('Error filtering bills:', data.message);
            }
        } catch (error) {
            console.error('Error filtering bills:', error);
        }
    }

    // Switch tab
    switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
            content.classList.remove('active');
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active', 'border-primary', 'text-primary');
            button.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab content
        const selectedContent = document.getElementById(`tab-content-${tabName}`);
        if (selectedContent) {
            selectedContent.classList.remove('hidden');
            selectedContent.classList.add('active');
        }

        // Add active class to selected tab button
        const selectedButton = document.getElementById(`tab-${tabName}`);
        if (selectedButton) {
            selectedButton.classList.add('active', 'border-primary', 'text-primary');
            selectedButton.classList.remove('border-transparent', 'text-gray-500');
        }

        this.currentTab = tabName;

        // Load tab-specific data
        switch(tabName) {
            case 'bills':
                this.loadBills();
                break;
            case 'payments':
                this.loadPayments();
                break;
            case 'discounts':
                this.loadDiscounts();
                break;
            case 'vouchers':
                this.loadVouchers();
                break;
            case 'loyalty':
                this.loadLoyalty();
                break;
        }
    }

    // Load payments
    async loadPayments() {
        try {
            const response = await fetch('../../api/get-payments.php');
            const data = await response.json();
            
            if (data.success) {
                this.displayPayments(data.payments);
            } else {
                console.error('Error loading payments:', data.message);
            }
        } catch (error) {
            console.error('Error loading payments:', error);
        }
    }

    // Display payments
    displayPayments(payments) {
        const container = document.getElementById('payments-container');
        if (!container) return;

        if (payments.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-credit-card text-4xl mb-4"></i>
                    <p>No payments found</p>
                </div>
            `;
            return;
        }

        const table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${payments.map(payment => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #${payment.payment_number}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                #${payment.bill_number}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱${parseFloat(payment.amount).toFixed(2)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.getPaymentMethodLabel(payment.payment_method)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.formatDate(payment.created_at)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${this.getStatusClass(payment.status)}">
                                    ${this.getStatusLabel(payment.status)}
                                </span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;

        container.innerHTML = table;
    }

    // Load discounts
    async loadDiscounts() {
        try {
            const response = await fetch('../../api/get-discounts.php');
            const data = await response.json();
            
            if (data.success) {
                this.displayDiscounts(data.discounts);
            } else {
                console.error('Error loading discounts:', data.message);
            }
        } catch (error) {
            console.error('Error loading discounts:', error);
        }
    }

    // Display discounts
    displayDiscounts(discounts) {
        const container = document.getElementById('discounts-container');
        if (!container) return;

        if (discounts.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-percentage text-4xl mb-4"></i>
                    <p>No discounts found</p>
                </div>
            `;
            return;
        }

        const table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${discounts.map(discount => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #${discount.id}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                #${discount.bill_number}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.getDiscountTypeLabel(discount.discount_type)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${discount.discount_type === 'percentage' ? `${discount.discount_value}%` : `₱${parseFloat(discount.discount_value).toFixed(2)}`}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${discount.discount_reason || 'N/A'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.formatDate(discount.created_at)}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;

        container.innerHTML = table;
    }

    // Load vouchers
    async loadVouchers() {
        try {
            const response = await fetch('../../api/get-vouchers.php');
            const data = await response.json();
            
            if (data.success) {
                this.displayVouchers(data.vouchers);
            } else {
                console.error('Error loading vouchers:', data.message);
            }
        } catch (error) {
            console.error('Error loading vouchers:', error);
        }
    }

    // Display vouchers
    displayVouchers(vouchers) {
        const container = document.getElementById('vouchers-container');
        if (!container) return;

        if (vouchers.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-ticket-alt text-4xl mb-4"></i>
                    <p>No vouchers found</p>
                </div>
            `;
            return;
        }

        const table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid Until</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${vouchers.map(voucher => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${voucher.voucher_code}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.getVoucherTypeLabel(voucher.voucher_type)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${voucher.voucher_type === 'percentage' ? `${voucher.voucher_value}%` : `₱${parseFloat(voucher.voucher_value).toFixed(2)}`}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${voucher.used_count || 0} / ${voucher.usage_limit || '∞'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.formatDate(voucher.valid_until)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${this.getVoucherStatusClass(voucher.status)}">
                                    ${this.getVoucherStatusLabel(voucher.status)}
                                </span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;

        container.innerHTML = table;
    }

    // Load loyalty
    async loadLoyalty() {
        try {
            const response = await fetch('../../api/get-loyalty-members.php');
            const data = await response.json();
            
            if (data.success) {
                this.displayLoyalty(data.members);
            } else {
                console.error('Error loading loyalty members:', data.message);
            }
        } catch (error) {
            console.error('Error loading loyalty members:', error);
        }
    }

    // Display loyalty
    displayLoyalty(members) {
        const container = document.getElementById('loyalty-container');
        if (!container) return;

        if (members.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-star text-4xl mb-4"></i>
                    <p>No loyalty members found</p>
                </div>
            `;
            return;
        }

        const table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${members.map(member => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-purple-500 flex items-center justify-center">
                                            <span class="text-white font-medium">
                                                ${member.first_name.charAt(0).toUpperCase()}${member.last_name.charAt(0).toUpperCase()}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${member.first_name} ${member.last_name}</div>
                                        <div class="text-sm text-gray-500">${member.email}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${this.getTierClass(member.tier)}">
                                    ${this.getTierLabel(member.tier)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${member.points || 0}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱${(member.total_spent || 0).toFixed(2)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${member.last_activity ? this.formatDate(member.last_activity) : 'Never'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="manageLoyaltyPoints(${member.id})" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-star"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;

        container.innerHTML = table;
    }

    // Get bill items
    getBillItems() {
        const items = [];
        const itemElements = document.querySelectorAll('.bill-item');
        
        itemElements.forEach(item => {
            const description = item.querySelector('.item-description').value;
            const quantity = item.querySelector('.item-quantity').value;
            const price = item.querySelector('.item-price').value;
            
            if (description && quantity && price) {
                items.push({
                    description: description,
                    quantity: parseInt(quantity),
                    price: parseFloat(price)
                });
            }
        });
        
        return items;
    }

    // Add bill item
    addBillItem() {
        const container = document.getElementById('bill-items-container');
        if (!container) return;

        const itemHtml = `
            <div class="bill-item flex space-x-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" class="item-description w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Item description">
                </div>
                <div class="w-24">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" class="item-quantity w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" min="1" value="1">
                </div>
                <div class="w-32">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                    <input type="number" class="item-price w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" step="0.01" min="0">
                </div>
                <button type="button" onclick="removeBillItem(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', itemHtml);
    }

    // Remove bill item
    removeBillItem(button) {
        button.closest('.bill-item').remove();
    }

    // Modal functions
    openBillingModal() {
        const modal = document.getElementById('billing-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    closeBillingModal() {
        const modal = document.getElementById('billing-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.getElementById('billing-form').reset();
        }
    }

    openDiscountModal() {
        const modal = document.getElementById('discount-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    closeDiscountModal() {
        const modal = document.getElementById('discount-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.getElementById('discount-form').reset();
        }
    }

    openVoucherModal() {
        const modal = document.getElementById('voucher-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    closeVoucherModal() {
        const modal = document.getElementById('voucher-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.getElementById('voucher-form').reset();
        }
    }

    openLoyaltyModal() {
        const modal = document.getElementById('loyalty-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    closeLoyaltyModal() {
        const modal = document.getElementById('loyalty-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.getElementById('loyalty-form').reset();
        }
    }

    // Helper methods
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    getStatusClass(status) {
        const statusClasses = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'paid': 'bg-green-100 text-green-800',
            'overdue': 'bg-red-100 text-red-800',
            'cancelled': 'bg-gray-100 text-gray-800'
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    }

    getStatusLabel(status) {
        const statusLabels = {
            'pending': 'Pending',
            'paid': 'Paid',
            'overdue': 'Overdue',
            'cancelled': 'Cancelled'
        };
        return statusLabels[status] || status;
    }

    getPaymentMethodLabel(method) {
        const methodLabels = {
            'cash': 'Cash',
            'credit_card': 'Credit Card',
            'debit_card': 'Debit Card',
            'bank_transfer': 'Bank Transfer'
        };
        return methodLabels[method] || method;
    }

    getDiscountTypeLabel(type) {
        const typeLabels = {
            'percentage': 'Percentage',
            'fixed': 'Fixed Amount',
            'loyalty': 'Loyalty Points',
            'promotional': 'Promotional'
        };
        return typeLabels[type] || type;
    }

    getVoucherTypeLabel(type) {
        const typeLabels = {
            'percentage': 'Percentage Discount',
            'fixed': 'Fixed Amount',
            'free_night': 'Free Night',
            'upgrade': 'Room Upgrade'
        };
        return typeLabels[type] || type;
    }

    getVoucherStatusClass(status) {
        const statusClasses = {
            'active': 'bg-green-100 text-green-800',
            'used': 'bg-blue-100 text-blue-800',
            'expired': 'bg-gray-100 text-gray-800'
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    }

    getVoucherStatusLabel(status) {
        const statusLabels = {
            'active': 'Active',
            'used': 'Used',
            'expired': 'Expired'
        };
        return statusLabels[status] || status;
    }

    getTierClass(tier) {
        const tierClasses = {
            'bronze': 'bg-orange-100 text-orange-800',
            'silver': 'bg-gray-100 text-gray-800',
            'gold': 'bg-yellow-100 text-yellow-800',
            'platinum': 'bg-purple-100 text-purple-800'
        };
        return tierClasses[tier] || 'bg-gray-100 text-gray-800';
    }

    getTierLabel(tier) {
        const tierLabels = {
            'bronze': 'Bronze',
            'silver': 'Silver',
            'gold': 'Gold',
            'platinum': 'Platinum'
        };
        return tierLabels[tier] || tier;
    }

    showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white max-w-sm transform transition-all duration-300 translate-x-full`;
        
        // Set background color based on type
        switch(type) {
            case 'success':
                notification.classList.add('bg-green-500');
                break;
            case 'error':
                notification.classList.add('bg-red-500');
                break;
            case 'warning':
                notification.classList.add('bg-yellow-500');
                break;
            default:
                notification.classList.add('bg-blue-500');
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
}

// Global functions
function switchTab(tabName) {
    if (window.billingManager) {
        window.billingManager.switchTab(tabName);
    }
}

function openBillingModal() {
    if (window.billingManager) {
        window.billingManager.openBillingModal();
    }
}

function closeBillingModal() {
    if (window.billingManager) {
        window.billingManager.closeBillingModal();
    }
}

function openDiscountModal() {
    if (window.billingManager) {
        window.billingManager.openDiscountModal();
    }
}

function closeDiscountModal() {
    if (window.billingManager) {
        window.billingManager.closeDiscountModal();
    }
}

function openVoucherModal() {
    if (window.billingManager) {
        window.billingManager.openVoucherModal();
    }
}

function closeVoucherModal() {
    if (window.billingManager) {
        window.billingManager.closeVoucherModal();
    }
}

function openLoyaltyModal() {
    if (window.billingManager) {
        window.billingManager.openLoyaltyModal();
    }
}

function closeLoyaltyModal() {
    if (window.billingManager) {
        window.billingManager.closeLoyaltyModal();
    }
}

function addBillItem() {
    if (window.billingManager) {
        window.billingManager.addBillItem();
    }
}

function removeBillItem(button) {
    if (window.billingManager) {
        window.billingManager.removeBillItem(button);
    }
}

function viewBill(billId) {
    window.location.href = `view-bill.php?id=${billId}`;
}

function editBill(billId) {
    window.location.href = `edit-bill.php?id=${billId}`;
}

function processPayment(billId) {
    window.location.href = `process-payment.php?bill_id=${billId}`;
}

function manageLoyaltyPoints(guestId) {
    if (window.billingManager) {
        window.billingManager.openLoyaltyModal();
        document.getElementById('loyalty_guest_id').value = guestId;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.billingManager = new BillingManager();
});