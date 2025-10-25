document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('bills-container');
    const statusFilter = document.getElementById('bill-status-filter');
    const dateFilter = document.getElementById('bill-date-filter');
    const btnFilter = document.getElementById('btn-filter-bills');
    const btnCreate = document.getElementById('btn-create-invoice');
    const btnExport = document.getElementById('btn-export-invoices');
    const qaCreate = document.getElementById('qa-create-invoice');
    const qaExport = document.getElementById('qa-export-invoices');
    const qaReminders = document.getElementById('qa-send-reminders');
    const modal = document.getElementById('create-invoice-modal');
    const closeModalBtn = document.getElementById('close-create-invoice');
    const cancelCreateBtn = document.getElementById('btn-cancel-create');
    const addItemBtn = document.getElementById('btn-add-item');
    const itemsContainer = document.getElementById('ci-items');
    const createForm = document.getElementById('create-invoice-form');

    function formatDate(dateString) {
        const d = new Date(dateString);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function statusClass(status) {
        switch (status) {
            case 'paid': return 'bg-green-100 text-green-800';
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'overdue': return 'bg-red-100 text-red-800';
            case 'cancelled': return 'bg-gray-100 text-gray-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    function renderTable(bills) {
        if (!container) return;
        if (!Array.isArray(bills) || bills.length === 0) {
            container.innerHTML = '<div class="p-6 text-center text-gray-500">No invoices found.</div>';
            return;
        }

        const rows = bills.map(bill => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${bill.bill_number}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8">
                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                <span class="text-white text-xs font-medium">${(bill.guest_name || '?').toString().charAt(0).toUpperCase()}</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">${bill.guest_name || 'Unknown'}</div>
                            <div class="text-xs text-gray-500">Room ${bill.room_number || '-'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${parseFloat(bill.total_amount || 0).toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatDate(bill.bill_date)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatDate(bill.due_date)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass(bill.status)}">${(bill.status || '').toString().charAt(0).toUpperCase() + (bill.status || '').toString().slice(1)}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                        <button class="text-blue-600 hover:text-blue-900" data-action="view" data-id="${bill.id}"><i class="fas fa-eye"></i></button>
                        <button class="text-green-600 hover:text-green-900" data-action="download" data-id="${bill.id}"><i class="fas fa-download"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');

        container.innerHTML = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">${rows}</tbody>
            </table>
        `;

        container.querySelectorAll('button[data-action]').forEach(btn => {
            btn.addEventListener('click', e => {
                const action = btn.getAttribute('data-action');
                const id = btn.getAttribute('data-id');
                if (action === 'view') {
                    window.location.href = `view-bill.php?id=${id}`;
                } else if (action === 'download') {
                    window.location.href = `../../api/export-bill.php?id=${id}`;
                }
            });
        });
    }

    async function loadBills() {
        try {
            const resp = await fetch('../../api/get-bills.php');
            const data = await resp.json();
            if (data.success) {
                renderTable(data.bills || []);
            } else {
                renderTable([]);
            }
        } catch (e) {
            renderTable([]);
        }
    }

    async function applyFilters() {
        const params = new URLSearchParams();
        if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);
        if (dateFilter && dateFilter.value) params.append('date', dateFilter.value);
        try {
            const resp = await fetch(`../../api/get-bills.php?${params.toString()}`);
            const data = await resp.json();
            renderTable(data.success ? (data.bills || []) : []);
        } catch (e) {
            renderTable([]);
        }
    }

    if (btnFilter) btnFilter.addEventListener('click', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    if (dateFilter) dateFilter.addEventListener('change', applyFilters);
    function openCreateModal() { if (modal) modal.classList.remove('hidden'); }
    function closeCreateModal() { if (modal) modal.classList.add('hidden'); if (createForm) createForm.reset(); if (itemsContainer) itemsContainer.innerHTML=''; }
    async function addItemRow() {
        if (!itemsContainer) return;
        const row = document.createElement('div');
        row.className = 'bill-item flex space-x-4 items-end';
        // Fetch inventory items for selection
        let options = '<option value="">Custom (no inventory)</option>';
        try {
            const resp = await fetch('../../api/get-inventory-items.php');
            const data = await resp.json();
            const items = data.items || data.inventory_items || [];
            options += items.map(it => `<option value="${it.id}" data-price="${it.unit_price || 0}">${it.item_name} (₱${parseFloat(it.unit_price || 0).toFixed(2)}) — In stock: ${it.current_stock}</option>`).join('');
        } catch(e) {}
        row.innerHTML = `
            <div class="w-1/3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Inventory Item</label>
                <select class="item-inventory w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">${options}</select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="text" class="item-description w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Item description">
            </div>
            <div class="w-24">
                <label class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
                <input type="number" class="item-quantity w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" min="1" value="1">
            </div>
            <div class="w-32">
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                <input type="number" class="item-price w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" step="0.01" min="0">
            </div>
            <button type="button" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 btn-remove-item"><i class="fas fa-trash"></i></button>
        `;
        itemsContainer.appendChild(row);
        row.querySelector('.btn-remove-item').addEventListener('click', () => row.remove());
        const invSelect = row.querySelector('.item-inventory');
        const descInput = row.querySelector('.item-description');
        const priceInput = row.querySelector('.item-price');
        invSelect.addEventListener('change', () => {
            const opt = invSelect.selectedOptions[0];
            if (opt && opt.value) {
                const price = parseFloat(opt.getAttribute('data-price') || '0');
                if (!descInput.value) descInput.value = opt.textContent.split(' (₱')[0];
                if (!priceInput.value || parseFloat(priceInput.value) <= 0) priceInput.value = price.toFixed(2);
            }
        });
    }
    async function submitCreate(e) {
        e.preventDefault();
        const formData = new FormData(createForm);
        const payload = {
            reservation_id: formData.get('reservation_id'),
            bill_date: formData.get('bill_date') || undefined,
            due_date: formData.get('due_date') || undefined,
            tax_rate: formData.get('tax_rate') ? parseFloat(formData.get('tax_rate')) : undefined,
            discount_amount: formData.get('discount_amount') ? parseFloat(formData.get('discount_amount')) : undefined,
            notes: formData.get('notes') || undefined,
            items: []
        };
        if (itemsContainer) {
            itemsContainer.querySelectorAll('.bill-item').forEach(el => {
                const description = el.querySelector('.item-description').value;
                const quantity = parseInt(el.querySelector('.item-quantity').value || '0', 10);
                const unit_price = parseFloat(el.querySelector('.item-price').value || '0');
                const inventory_item_id = el.querySelector('.item-inventory')?.value || '';
                if (description && quantity > 0 && unit_price >= 0) {
                    const item = { description, quantity, unit_price };
                    if (inventory_item_id) item.inventory_item_id = parseInt(inventory_item_id, 10);
                    payload.items.push(item);
                }
            });
        }
        try {
            const resp = await fetch('../../api/create-bill.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const result = await resp.json();
            if (result.success) {
                closeCreateModal();
                await applyFilters();
                alert('Invoice created successfully.');
            } else {
                alert(result.message || 'Failed to create invoice');
            }
        } catch (e) {
            alert('Error creating invoice');
        }
    }
    async function sendReminders() {
        try {
            const resp = await fetch('../../api/send-invoice-reminders.php', { method: 'POST' });
            const result = await resp.json();
            if (result.success) {
                alert(`Reminders sent: ${result.reminders_sent}`);
            } else {
                alert(result.message || 'Failed to send reminders');
            }
        } catch (e) {
            alert('Error sending reminders');
        }
    }
    if (qaCreate) qaCreate.addEventListener('click', openCreateModal);
    if (qaExport) qaExport.addEventListener('click', () => { window.location.href='../../api/export-invoices-csv.php'; });
    if (qaReminders) qaReminders.addEventListener('click', sendReminders);
    if (btnCreate) btnCreate.addEventListener('click', openCreateModal);
    if (btnExport) btnExport.addEventListener('click', () => { window.location.href='../../api/export-invoices-csv.php'; });
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeCreateModal);
    if (cancelCreateBtn) cancelCreateBtn.addEventListener('click', closeCreateModal);
    if (addItemBtn) addItemBtn.addEventListener('click', addItemRow);
    if (createForm) createForm.addEventListener('submit', submitCreate);
    // Start with one item row
    if (itemsContainer) addItemRow();

    loadBills();
});


