/**
 * Check-out Management JavaScript
 * Hotel PMS - Front Desk Module
 */

class CheckOutManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadCheckedInGuests();
        this.setupEventListeners();
    }

    // Load checked-in guests
    async loadCheckedInGuests() {
        try {
            const response = await fetch('../../api/get-checked-in-guests.php');
            const data = await response.json();
            
            if (data.success) {
                this.displayCheckedInGuests(data.guests);
            } else {
                console.error('Error loading checked-in guests:', data.message);
            }
        } catch (error) {
            console.error('Error loading checked-in guests:', error);
        }
    }

    // Display checked-in guests
    displayCheckedInGuests(guests) {
        const container = document.getElementById('checked-in-guests');
        if (!container) return;

        if (guests.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-bed text-4xl mb-4"></i>
                    <p>No checked-in guests found</p>
                </div>
            `;
            return;
        }

        const table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${guests.map(guest => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center">
                                            <span class="text-white font-medium">
                                                ${(guest.guest_name || 'G').charAt(0).toUpperCase()}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">${guest.guest_name || 'Unknown Guest'}</div>
                                        <div class="text-sm text-gray-500">${guest.email || ''}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${guest.reservation_number || 'N/A'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Room ${guest.room_number || 'N/A'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${this.formatDate(guest.check_out_date)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${this.getStatusClass(guest.checkout_status)}">
                                    ${this.getStatusLabel(guest.checkout_status, guest.days_remaining)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="startCheckOut('${guest.id ?? ''}','${guest.reservation_number ?? ''}')" class="text-red-600 hover:text-red-900 mr-3">
                                    <i class="fas fa-sign-out-alt mr-1"></i>Check Out
                                </button>
                                <button onclick="viewGuestDetails('${guest.id ?? ''}','${guest.reservation_number ?? ''}')" class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-eye mr-1"></i>View
                                </button>
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
        // Check-out form submission
        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', this.handleCheckOutSubmit.bind(this));
        }

        // Search functionality
        const searchInputs = ['search_reservation', 'search_guest', 'search_room', 'search_status'];
        searchInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', this.handleSearch.bind(this));
            }
        });
    }

    // Handle check-out form submission
    async handleCheckOutSubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = {
            reservation_id: formData.get('reservation_id'),
            room_key_returned: formData.get('room_key_returned'),
            payment_status: formData.get('payment_status'),
            checkout_notes: formData.get('checkout_notes')
        };

        try {
            const response = await fetch('../../api/process-checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Check-out completed successfully!', 'success');
                this.hideCheckOutForm();
                this.loadCheckedInGuests();
            } else {
                this.showNotification(result.message || 'Error processing check-out', 'error');
            }
        } catch (error) {
            console.error('Error processing check-out:', error);
            this.showNotification('Error processing check-out. Please try again.', 'error');
        }
    }

    // Handle search
    handleSearch() {
        const reservationNumber = document.getElementById('search_reservation').value;
        const guestName = document.getElementById('search_guest').value;
        const roomNumber = document.getElementById('search_room').value;
        const status = document.getElementById('search_status').value;

        this.searchCheckedInGuests({
            reservation_number: reservationNumber,
            guest_name: guestName,
            room_number: roomNumber,
            status: status
        });
    }

    // Search checked-in guests
    async searchCheckedInGuests(filters) {
        try {
            const params = new URLSearchParams();
            Object.keys(filters).forEach(key => {
                if (filters[key]) {
                    params.append(key, filters[key]);
                }
            });

            const response = await fetch(`../../api/search-checked-in-guests.php?${params.toString()}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayCheckedInGuests(data.guests);
            } else {
                console.error('Error searching checked-in guests:', data.message);
            }
        } catch (error) {
            console.error('Error searching checked-in guests:', error);
        }
    }

    // Start check-out process
    startCheckOut(reservationId, reservationNumber) {
        // Prefer numeric id; if missing, use reservation number
        const raw = reservationId && reservationId !== 'undefined' ? reservationId : reservationNumber;
        this.loadReservationDetails(raw);
    }

    // Load reservation details for check-out
    async loadReservationDetails(rawId) {
        // Attempt both ID and reservation_number to be safe
        const tryFetch = async (url) => {
            const res = await fetch(url);
            return await res.json();
        };
        try {
            let data;
            const isNumeric = rawId && /^\d+$/.test(String(rawId));
            const firstUrl = isNumeric
                ? `../../api/get-reservation-details.php?id=${rawId}`
                : `../../api/get-reservation-details.php?reservation_number=${encodeURIComponent(rawId)}`;
            data = await tryFetch(firstUrl);
            if (!data || !data.success) {
                const fallbackUrl = isNumeric
                    ? `../../api/get-reservation-details.php?reservation_number=${encodeURIComponent(rawId)}`
                    : `../../api/get-reservation-details.php?id=${rawId}`;
                data = await tryFetch(fallbackUrl);
            }
            if (data && data.success) {
                this.populateCheckOutForm(data.reservation);
                const billKey = data.reservation.id || rawId;
                this.loadBillingSummary(billKey);
                this.showCheckOutForm();
            } else {
                console.error('Reservation details API error:', data);
                this.showNotification(data && data.message ? data.message : 'Error loading reservation details', 'error');
            }
        } catch (error) {
            console.error('Error loading reservation details:', error);
            this.showNotification('Error loading reservation details', 'error');
        }
    }

    // Load billing summary
    async loadBillingSummary(reservationId) {
        try {
            // First finalize invoice to ensure a persisted billing row
            await fetch(`../../api/finalize-invoice.php?reservation_id=${reservationId}`);
            const response = await fetch(`../../api/get-billing-summary.php?reservation_id=${reservationId}`);
            const data = await response.json();
            if (data.success) {
                this.displayBillingSummary(data.billing);
            } else {
                console.error('Error loading billing summary:', data.message);
                // fallback: compute from reservation if available
                if (this.currentReservation) {
                    this.displayBillingSummary({
                        room_rate: 0,
                        nights: 1,
                        subtotal: 0,
                        tax: 0,
                        additional_charges: 0,
                        discounts: 0,
                        total_amount: 0
                    });
                }
            }
        } catch (error) {
            console.error('Error loading billing summary:', error);
        }
    }

    // Display billing summary
    displayBillingSummary(billing) {
        const container = document.getElementById('billing-summary');
        if (!container) return;

        // Ensure billing object has all required properties with defaults and convert to numbers
        const safeBilling = {
            room_rate: parseFloat(billing.room_rate || billing.room_charges || 0),
            nights: parseInt(billing.nights || 1),
            subtotal: parseFloat(billing.subtotal || billing.room_charges || 0),
            tax: parseFloat(billing.tax || billing.tax_amount || 0),
            additional_charges: parseFloat(billing.additional_charges || 0),
            discounts: parseFloat(billing.discounts || 0),
            total_amount: parseFloat(billing.total_amount || 0)
        };

        // Fallback compute if backend returned zeros
        if ((safeBilling.room_rate === 0 || safeBilling.subtotal === 0) && this.currentReservation) {
            const r = this.currentReservation;
            const typeKey = String(r.room_type || '').toLowerCase();
            const defaultRates = { standard: 500, deluxe: 250, suite: 400, presidential: 800 };
            let roomRate = defaultRates[typeKey] || 0;
            try {
                const inDate = new Date(r.check_in_date);
                const outDate = new Date(r.check_out_date);
                const nights = Math.max(1, Math.round((outDate - inDate) / (1000 * 60 * 60 * 24)));
                const subtotal = roomRate * nights;
                const tax = +(subtotal * 0.10).toFixed(2);
                const total = subtotal + tax;
                safeBilling.room_rate = roomRate;
                safeBilling.nights = nights;
                safeBilling.subtotal = subtotal;
                safeBilling.tax = tax;
                safeBilling.total_amount = total;
            } catch (e) {
                // leave zeros if parse failed
            }
        }

        const summary = `
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Room Rate:</span>
                    <span class="text-sm font-medium">₱${safeBilling.room_rate.toFixed(2)}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Nights:</span>
                    <span class="text-sm font-medium">${safeBilling.nights}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Subtotal:</span>
                    <span id="subtotal-amount" class="text-sm font-medium">₱${safeBilling.subtotal.toFixed(2)}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Tax (10%):</span>
                    <span id="tax-amount" class="text-sm font-medium">₱${safeBilling.tax.toFixed(2)}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Additional Charges:</span>
                    <span id="addl-amount" class="text-sm font-medium">₱${safeBilling.additional_charges.toFixed(2)}</span>
                </div>
                <div id="additional-services" class="mt-2 hidden">
                    <div class="text-sm text-gray-700 font-medium mb-1">Additional Services</div>
                    <ul id="additional-services-items" class="text-sm text-gray-600 list-disc pl-5 space-y-1"></ul>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Discounts:</span>
                    <span class="text-sm font-medium text-green-600">-₱${safeBilling.discounts.toFixed(2)}</span>
                </div>
                <div class="border-t pt-2">
                    <div class="flex justify-between">
                        <span class="text-base font-semibold text-gray-900">Total Amount:</span>
                        <span id="total-amount" class="text-base font-semibold text-gray-900">₱${safeBilling.total_amount.toFixed(2)}</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button id="btn-finalize" class="px-3 py-2 bg-blue-600 text-white rounded" onclick="(async()=>{await fetch('../../api/finalize-invoice.php?reservation_id='+(window.checkOutManager?.currentReservation?.id||'')); await window.checkOutManager.loadBillingSummary(window.checkOutManager?.currentReservation?.id||'')})()">Finalize Invoice</button>
                        <button id="btn-pay-full" class="px-3 py-2 bg-green-600 text-white rounded" onclick="(async()=>{const rid=window.checkOutManager?.currentReservation?.id||'';const amt=${safeBilling.total_amount.toFixed(2)};await fetch('../../api/post-payment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({reservation_id:rid,amount:amt,method:'cash'})}); await window.checkOutManager.loadBillingSummary(rid)})()">Record Full Payment</button>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML = summary;

        // Load detailed invoice items to list additional services
        const reservationId = this.currentReservation?.id;
        if (reservationId) {
            (async () => {
                try {
                    // Always load our additional services API first
                    let sum = 0;
                    try {
                        const res2 = await fetch(`../../api/get-additional-services.php?reservation_id=${reservationId}`, { credentials: 'include' });
                        const json2 = await res2.json();
                        if (json2 && json2.success && (json2.items||[]).length > 0) {
                            const ul = document.getElementById('additional-services-items');
                            const sec = document.getElementById('additional-services');
                            if (ul && sec) {
                                ul.innerHTML = json2.items.map(it => `<li>${(it.description||'')}: ₱${Number(it.total||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}</li>`).join('');
                                sec.classList.remove('hidden');
                            }
                            sum = (json2.items||[]).reduce((acc, it) => acc + Number(it.total||0), 0);
                        }
                    } catch (_) {}

                    // If invoice already has items (post-finalize), list those as well
                    try {
                        const res = await fetch(`../../api/get-invoice-details.php?reservation_id=${reservationId}`, { credentials: 'include' });
                        const json = await res.json();
                        if (json && json.success) {
                            const items = json.items || [];
                            const extra = items.filter(it => {
                                const d = String(it.description||'').toLowerCase();
                                return d && !d.includes('room charge') && !d.includes('tax');
                            });
                            if (extra.length > 0) {
                                const ul = document.getElementById('additional-services-items');
                                const sec = document.getElementById('additional-services');
                                if (ul && sec) {
                                    ul.innerHTML += extra.map(it => `<li>${(it.description||'')}: ₱${Number(it.total_amount||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}</li>`).join('');
                                    sec.classList.remove('hidden');
                                }
                            }
                        }
                    } catch (_) {}

                    // Update amounts if we have a sum
                    if (sum > 0) {
                        const addl = document.getElementById('addl-amount');
                        if (addl) addl.textContent = '₱' + Number(sum).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                        const subtotal = Number((document.getElementById('subtotal-amount')?.textContent||'0').replace(/[^0-9.]/g,'')) || 0;
                        const tax = Number((document.getElementById('tax-amount')?.textContent||'0').replace(/[^0-9.]/g,'')) || 0;
                        const discounts = 0;
                        const total = subtotal + tax + sum - discounts;
                        const totalEl = document.getElementById('total-amount');
                        if (totalEl) totalEl.textContent = '₱' + Number(total).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                    }
                } catch (_) {}
            })();
        }
    }

    // Populate check-out form
    populateCheckOutForm(reservation) {
        // Save for fallback billing computation
        this.currentReservation = reservation;
        document.getElementById('reservation_id').value = reservation.id;
        document.getElementById('guest_name').value = reservation.guest_name;
        document.getElementById('reservation_number').value = reservation.reservation_number;
        document.getElementById('room_number').value = reservation.room_number;
        document.getElementById('checkout_date').value = this.formatDate(reservation.check_out_date);
    }

    // Show check-out form
    showCheckOutForm() {
        const formContainer = document.getElementById('checkout-form-container');
        if (formContainer) {
            formContainer.classList.remove('hidden');
            formContainer.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Hide check-out form
    hideCheckOutForm() {
        const formContainer = document.getElementById('checkout-form-container');
        if (formContainer) {
            formContainer.classList.add('hidden');
        }
    }

    // Cancel check-out
    cancelCheckout() {
        this.hideCheckOutForm();
        document.getElementById('checkout-form').reset();
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
            'due_today': 'bg-yellow-100 text-yellow-800',
            'overdue': 'bg-red-100 text-red-800',
            'vip': 'bg-purple-100 text-purple-800',
            'normal': 'bg-green-100 text-green-800'
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    }

    getStatusLabel(status, daysRemaining) {
        if (status === 'overdue') {
            return 'Overdue';
        } else if (status === 'due_today') {
            return 'Due Today';
        } else if (status === 'vip') {
            return 'VIP Guest';
        } else if (daysRemaining !== undefined && daysRemaining > 0) {
            return `${daysRemaining} days left`;
        } else {
            return 'Normal';
        }
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
function searchCheckedInGuests() {
    if (window.checkOutManager) {
        window.checkOutManager.handleSearch();
    }
}

function clearFilters() {
    document.getElementById('search_reservation').value = '';
    document.getElementById('search_guest').value = '';
    document.getElementById('search_room').value = '';
    document.getElementById('search_status').value = '';
    
    if (window.checkOutManager) {
        window.checkOutManager.loadCheckedInGuests();
    }
}

function refreshData() {
    if (window.checkOutManager) {
        window.checkOutManager.loadCheckedInGuests();
    }
}

function startCheckOut(reservationId, reservationNumber) {
    if (window.checkOutManager) {
        window.checkOutManager.startCheckOut(reservationId, reservationNumber);
    }
}

function viewGuestDetails(reservationId) {
    window.location.href = `view-reservation.php?id=${reservationId}`;
}

function cancelCheckout() {
    if (window.checkOutManager) {
        window.checkOutManager.cancelCheckout();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.checkOutManager = new CheckOutManager();
});