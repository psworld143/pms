// Management & Reports JavaScript
// Chart instances storage
let chartInstances = {};
let chartsLoading = {
    occupancy: false,
    revenue: false
};

document.addEventListener('DOMContentLoaded', function() {
    initializeManagementReports();
    loadCharts();
    loadDailyReports();
});

// Clean up chart instances when page is unloaded
window.addEventListener('beforeunload', function() {
    Object.values(chartInstances).forEach(chart => {
        if (chart && typeof chart.destroy === 'function') {
            chart.destroy();
        }
    });
    chartInstances = {};
});

function initializeManagementReports() {
    switchReportTab('daily');
    
    // Initialize filter change listeners
    document.getElementById('daily-date-filter').addEventListener('change', loadDailyReports);
    document.getElementById('weekly-date-filter').addEventListener('change', loadWeeklyReports);
    document.getElementById('monthly-date-filter').addEventListener('change', loadMonthlyReports);
    document.getElementById('inventory-category-filter').addEventListener('change', loadInventoryReports);
    
    // Initialize form handlers
    document.getElementById('add-item-form').addEventListener('submit', handleAddItemSubmit);
}

// Tab switching functionality
function switchReportTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('active');
    });
    
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
        button.classList.remove('border-primary', 'text-primary');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    const selectedContent = document.getElementById(`tab-content-${tabName}`);
    if (selectedContent) {
        selectedContent.classList.remove('hidden');
        selectedContent.classList.add('active');
    }
    
    const selectedButton = document.getElementById(`tab-${tabName}`);
    if (selectedButton) {
        selectedButton.classList.add('active', 'border-primary', 'text-primary');
        selectedButton.classList.remove('border-transparent', 'text-gray-500');
    }
    
    switch(tabName) {
        case 'daily': loadDailyReports(); break;
        case 'weekly': loadWeeklyReports(); break;
        case 'monthly': loadMonthlyReports(); break;
        case 'inventory': loadInventoryReports(); break;
    }
}

// Chart initialization
function loadCharts() {
    loadOccupancyChart();
    loadRevenueChart();
}

function loadOccupancyChart() {
    // Prevent multiple simultaneous loads
    if (chartsLoading.occupancy) {
        return;
    }
    
    chartsLoading.occupancy = true;
    
    // Destroy existing chart if it exists
    if (chartInstances.occupancyChart) {
        chartInstances.occupancyChart.destroy();
        chartInstances.occupancyChart = null;
    }
    
    fetch('../../api/get-occupancy-data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Process the data to extract labels and values
                const labels = data.data.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                });
                const values = data.data.map(item => item.occupancy_rate);
                
                const ctx = document.getElementById('occupancyChart').getContext('2d');
                chartInstances.occupancyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Occupancy Rate (%)',
                            data: values,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeInOutQuart'
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            } else {
                console.error('No data available for occupancy chart');
            }
        })
        .catch(error => {
            console.error('Error loading occupancy chart:', error);
        })
        .finally(() => {
            chartsLoading.occupancy = false;
        });
}

function loadRevenueChart() {
    // Prevent multiple simultaneous loads
    if (chartsLoading.revenue) {
        return;
    }
    
    chartsLoading.revenue = true;
    
    // Destroy existing chart if it exists
    if (chartInstances.revenueChart) {
        chartInstances.revenueChart.destroy();
        chartInstances.revenueChart = null;
    }
    
    fetch('../../api/get-revenue-data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Process the data to extract labels and values
                const labels = data.data.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                });
                const values = data.data.map(item => item.revenue);
                
                const ctx = document.getElementById('revenueChart').getContext('2d');
                chartInstances.revenueChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: values,
                            backgroundColor: '#10B981',
                            borderColor: '#059669',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeInOutQuart'
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            } else {
                console.error('No data available for revenue chart');
            }
        })
        .catch(error => {
            console.error('Error loading revenue chart:', error);
        })
        .finally(() => {
            chartsLoading.revenue = false;
        });
}

// Report loading functions
function loadDailyReports() {
    const dateFilter = document.getElementById('daily-date-filter').value;
    const params = new URLSearchParams({ date: dateFilter });
    
    fetch(`../../api/get-daily-reports.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayDailyReports(data);
            } else {
                HotelPMS.Utils.showNotification(data.message || 'Error loading daily reports', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading daily reports:', error);
            HotelPMS.Utils.showNotification('Error loading daily reports', 'error');
        });
}

function loadWeeklyReports() {
    const weekFilter = document.getElementById('weekly-date-filter').value;
    const params = new URLSearchParams({ week: weekFilter });
    
    fetch(`../../api/get-weekly-reports.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayWeeklyReports(data);
            } else {
                HotelPMS.Utils.showNotification(data.message || 'Error loading weekly reports', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading weekly reports:', error);
            HotelPMS.Utils.showNotification('Error loading weekly reports', 'error');
        });
}

function loadMonthlyReports() {
    const monthFilter = document.getElementById('monthly-date-filter').value;
    const params = new URLSearchParams({ month: monthFilter });
    
    fetch(`../../api/get-monthly-reports.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMonthlyReports(data);
            } else {
                HotelPMS.Utils.showNotification(data.message || 'Error loading monthly reports', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading monthly reports:', error);
            HotelPMS.Utils.showNotification('Error loading monthly reports', 'error');
        });
}

function loadInventoryReports() {
    const categoryFilter = document.getElementById('inventory-category-filter').value;
    const params = new URLSearchParams({ category: categoryFilter });
    
    // Use the new management-specific inventory reports API
    fetch(`../../api/get-inventory-reports.php?${params}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Load categories into the filter dropdown
            loadInventoryCategoriesFilter(data.categories || []);
            displayInventoryReports(data);
        } else {
            console.error('API Error:', data);
            HotelPMS.Utils.showNotification(data.message || 'Error loading inventory reports', 'error');
        }
    })
    .catch(error => {
        console.error('Error loading inventory reports:', error);
        HotelPMS.Utils.showNotification('Error loading inventory reports', 'error');
    });
}

function loadInventoryCategoriesFilter(categories) {
    const select = document.getElementById('inventory-category-filter');
    if (select && categories.length > 0) {
        // Keep the "All Categories" option and add dynamic categories
        const currentValue = select.value;
        select.innerHTML = '<option value="">All Categories</option>';
        categories.forEach(category => {
            select.innerHTML += `<option value="${category}">${category}</option>`;
        });
        // Restore the selected value
        select.value = currentValue;
    }
}

// Display functions
function displayDailyReports(data) {
    const container = document.getElementById('daily-reports-container');

    if (!data || !data.summary) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No daily reports found</h3>
                <p class="text-gray-500">No reports match your current filters.</p>
            </div>
        `;
        return;
    }

    const summary = data.summary;
    const reservations = data.reservations || [];

    const summaryCards = [
        { icon: 'fa-calendar-day', color: 'text-blue-600', bg: 'bg-blue-100', label: 'Total Reservations', value: formatNumber(summary.total_reservations) },
        { icon: 'fa-sign-in-alt', color: 'text-green-600', bg: 'bg-green-100', label: 'Check-ins', value: formatNumber(summary.check_ins) },
        { icon: 'fa-sign-out-alt', color: 'text-red-600', bg: 'bg-red-100', label: 'Check-outs', value: formatNumber(summary.check_outs) },
        { icon: 'fa-bed', color: 'text-yellow-600', bg: 'bg-yellow-100', label: 'Occupancy Rate', value: `${Number(summary.occupancy_rate || 0).toFixed(1)}%` },
        { icon: 'fa-peso-sign', color: 'text-emerald-600', bg: 'bg-emerald-100', label: 'Revenue', value: formatCurrency(summary.daily_revenue) },
        { icon: 'fa-coins', color: 'text-indigo-600', bg: 'bg-indigo-100', label: 'Taxes', value: formatCurrency(summary.daily_taxes) },
        { icon: 'fa-tags', color: 'text-purple-600', bg: 'bg-purple-100', label: 'Discounts', value: formatCurrency(summary.daily_discounts) },
        { icon: 'fa-receipt', color: 'text-gray-600', bg: 'bg-gray-100', label: 'Transactions', value: formatNumber(summary.total_transactions) }
    ];

    const summaryHtml = `
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            ${summaryCards.map(card => `
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-2 ${card.bg} rounded-lg">
                            <i class="fas ${card.icon} ${card.color}"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">${card.label}</p>
                            <p class="text-2xl font-bold text-gray-900">${card.value}</p>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    const tableHtml = `
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Reservations for ${formatDate(data.date)}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${reservations.map(reservation => `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">${reservation.guest_name}</div>
                                    <div class="text-sm text-gray-500">${reservation.guest_email || '—'}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${reservation.room_number} (${reservation.room_type})</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatDate(reservation.check_in_date)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatDate(reservation.check_out_date)}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusClass(reservation.status)}">
                                        ${reservation.status.replace('_', ' ').toUpperCase()}
                                    </span>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    container.innerHTML = summaryHtml + tableHtml;
}

function displayWeeklyReports(data) {
    const container = document.getElementById('weekly-reports-container');
    
    if (!data || !data.data || data.data.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No weekly reports found</h3>
                <p class="text-gray-500">No reports match your current filters.</p>
            </div>
        `;
        return;
    }
    
    const totals = data.totals || {};

    const summaryCards = [
        { icon: 'fa-peso-sign', color: 'text-emerald-600', bg: 'bg-emerald-100', label: 'Total Revenue', value: formatCurrency(totals.total_revenue) },
        { icon: 'fa-coins', color: 'text-indigo-600', bg: 'bg-indigo-100', label: 'Taxes', value: formatCurrency(totals.total_taxes) },
        { icon: 'fa-tags', color: 'text-purple-600', bg: 'bg-purple-100', label: 'Discounts', value: formatCurrency(totals.total_discounts) },
        { icon: 'fa-bed', color: 'text-blue-600', bg: 'bg-blue-100', label: 'Occupied Nights', value: formatNumber(totals.occupied_nights) }
    ];

    const summaryHtml = `
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            ${summaryCards.map(card => `
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-2 ${card.bg} rounded-lg">
                            <i class="fas ${card.icon} ${card.color}"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">${card.label}</p>
                            <p class="text-2xl font-bold text-gray-900">${card.value}</p>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    const tableHtml = `
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Weekly Report: ${data.start_date} to ${data.end_date}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taxes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discounts</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${data.data.map(report => `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${formatDate(report.date)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatNumber(report.transactions)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatCurrency(report.revenue)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatCurrency(report.taxes)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatCurrency(report.discounts)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <span class="text-sm font-medium text-gray-900">Weekly Summary</span>
                    <div class="text-sm text-gray-700 space-x-4">
                        <span>Revenue: ${formatCurrency(totals.total_revenue)}</span>
                        <span>Taxes: ${formatCurrency(totals.total_taxes)}</span>
                        <span>Discounts: ${formatCurrency(totals.total_discounts)}</span>
                        <span>Transactions: ${formatNumber(totals.total_reservations)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.innerHTML = summaryHtml + tableHtml;
}

function displayMonthlyReports(data) {
    const container = document.getElementById('monthly-reports-container');
    
    if (!data || !data.data || data.data.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No monthly reports found</h3>
                <p class="text-gray-500">No reports match your current filters.</p>
            </div>
        `;
        return;
    }
    
    const totals = data.totals || {};

    const summaryCards = [
        { icon: 'fa-peso-sign', color: 'text-emerald-600', bg: 'bg-emerald-100', label: 'Total Revenue', value: formatCurrency(totals.total_revenue) },
        { icon: 'fa-coins', color: 'text-indigo-600', bg: 'bg-indigo-100', label: 'Taxes', value: formatCurrency(totals.total_taxes) },
        { icon: 'fa-tags', color: 'text-purple-600', bg: 'bg-purple-100', label: 'Discounts', value: formatCurrency(totals.total_discounts) },
        { icon: 'fa-balance-scale', color: 'text-blue-600', bg: 'bg-blue-100', label: 'Avg. Reservation Value', value: formatCurrency(totals.average_reservation_value) }
    ];

    const summaryHtml = `
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            ${summaryCards.map(card => `
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-2 ${card.bg} rounded-lg">
                            <i class="fas ${card.icon} ${card.color}"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">${card.label}</p>
                            <p class="text-2xl font-bold text-gray-900">${card.value}</p>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    const tableHtml = `
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Monthly Report: ${data.month}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taxes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discounts</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${data.data.map(report => `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${formatDate(report.date)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatNumber(report.transactions)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatCurrency(report.revenue)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatCurrency(report.taxes)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatCurrency(report.discounts)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <span class="text-sm font-medium text-gray-900">Monthly Summary</span>
                    <div class="text-sm text-gray-700 space-x-4">
                        <span>Revenue: ${formatCurrency(totals.total_revenue)}</span>
                        <span>Taxes: ${formatCurrency(totals.total_taxes)}</span>
                        <span>Discounts: ${formatCurrency(totals.total_discounts)}</span>
                        <span>Avg Value: ${formatCurrency(totals.average_reservation_value)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.innerHTML = summaryHtml + tableHtml;
}

function formatCurrency(value) {
    const amount = Number(value || 0);
    return `₱${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatNumber(value) {
    return Number(value || 0).toLocaleString('en-US');
}

function displayInventoryReports(data) {
    const container = document.getElementById('inventory-reports-container');
    
    if (!data || !data.inventory_items || data.inventory_items.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-boxes text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No inventory reports found</h3>
                <p class="text-gray-500">No reports match your current filters.</p>
            </div>
        `;
        return;
    }
    
    const summary = data.stock_summary;
    const items = data.inventory_items;
    
    // Display summary cards
    const summaryHtml = `
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-boxes text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Items</p>
                        <p class="text-2xl font-bold text-gray-900">${summary.total_items}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Low Stock</p>
                        <p class="text-2xl font-bold text-gray-900">${summary.low_stock_items}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-exclamation-circle text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Warning</p>
                        <p class="text-2xl font-bold text-gray-900">${summary.medium_stock_items}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Good Stock</p>
                        <p class="text-2xl font-bold text-gray-900">${summary.good_stock_items}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Display inventory items table
    const tableHtml = `
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Inventory Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min. Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${items.map(item => `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">${item.name}</div>
                                    <div class="text-sm text-gray-500">₱${parseFloat(item.unit_price || 0).toFixed(2)}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.category_name || 'N/A'}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.current_stock}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.minimum_stock}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${getStockStatusClass(item.current_stock, item.minimum_stock)}">
                                        ${item.stock_status}
                                    </span>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    container.innerHTML = summaryHtml + tableHtml;
}

// Report generation functions
function generateReport(type) {
    const params = new URLSearchParams({ type: type });
    
    fetch(`../../api/generate-report.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                HotelPMS.Utils.showNotification(`${type.charAt(0).toUpperCase() + type.slice(1)} report generated successfully!`, 'success');
                if (data.download_url) {
                    window.open(data.download_url, '_blank');
                }
            } else {
                HotelPMS.Utils.showNotification(data.message || 'Error generating report', 'error');
            }
        })
        .catch(error => {
            console.error('Error generating report:', error);
            HotelPMS.Utils.showNotification('Error generating report', 'error');
        });
}

function exportReport(type) {
    let params = new URLSearchParams({ type: type });
    
    switch(type) {
        case 'daily':
            const dailyDate = document.getElementById('daily-date-filter').value;
            if (dailyDate) params.append('date', dailyDate);
            break;
        case 'weekly':
            const weeklyDate = document.getElementById('weekly-date-filter').value;
            if (weeklyDate) params.append('week', weeklyDate);
            break;
        case 'monthly':
            const monthlyDate = document.getElementById('monthly-date-filter').value;
            if (monthlyDate) params.append('month', monthlyDate);
            break;
        case 'inventory':
            const category = document.getElementById('inventory-category-filter').value;
            if (category) params.append('category', category);
            break;
    }
    
    fetch(`../../api/export-report.php?${params}`)
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${type}_report_${new Date().toISOString().split('T')[0]}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            HotelPMS.Utils.showNotification('Report exported successfully!', 'success');
        })
        .catch(error => {
            console.error('Error exporting report:', error);
            HotelPMS.Utils.showNotification('Error exporting report', 'error');
        });
}

// Inventory management functions
function openInventoryModal() {
    document.getElementById('inventory-modal').classList.remove('hidden');
    switchInventoryTab('items');
    loadInventoryItems();
}

function closeInventoryModal() {
    document.getElementById('inventory-modal').classList.add('hidden');
}

function switchInventoryTab(tabName) {
    document.querySelectorAll('.inventory-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('active');
    });
    
    document.querySelectorAll('.inventory-tab-button').forEach(button => {
        button.classList.remove('active');
        button.classList.remove('border-primary', 'text-primary');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    const selectedContent = document.getElementById(`inventory-content-${tabName}`);
    if (selectedContent) {
        selectedContent.classList.remove('hidden');
        selectedContent.classList.add('active');
    }
    
    const selectedButton = document.getElementById(`inventory-tab-${tabName}`);
    if (selectedButton) {
        selectedButton.classList.add('active', 'border-primary', 'text-primary');
        selectedButton.classList.remove('border-transparent', 'text-gray-500');
    }
    
    switch(tabName) {
        case 'items': loadInventoryItems(); break;
        case 'categories': loadInventoryCategories(); break;
        case 'transactions': loadInventoryTransactions(); break;
    }
}

function loadInventoryItems() {
    fetch('../../inventory/api/get-inventory-items.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayInventoryItems(data.inventory_items || data.items || []);
            } else {
                HotelPMS.Utils.showNotification(data.message || 'Error loading inventory items', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading inventory items:', error);
            HotelPMS.Utils.showNotification('Error loading inventory items', 'error');
        });
}

function displayInventoryItems(items) {
    const container = document.getElementById('inventory-items-container');
    
    if (!items || items.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-box text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No inventory items found</h3>
                <p class="text-gray-500">Add some items to get started.</p>
            </div>
        `;
        return;
    }
    
    const tableHtml = `
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${items.map(item => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">${item.item_name}</div>
                                <div class="text-sm text-gray-500">₱${parseFloat(item.unit_price).toFixed(2)}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.category_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${item.current_stock} / ${item.minimum_stock}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${getStockStatusClass(item.current_stock, item.minimum_stock)}">
                                    ${getStockStatusLabel(item.current_stock, item.minimum_stock)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="editInventoryItem(${item.id})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="adjustStock(${item.id})" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-plus-minus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = tableHtml;
}

// Modal functions
function openAddItemModal() {
    loadInventoryCategories('category_id');
    document.getElementById('add-item-modal').classList.remove('hidden');
}

function closeAddItemModal() {
    document.getElementById('add-item-modal').classList.add('hidden');
    document.getElementById('add-item-form').reset();
}

// Form handlers
function handleAddItemSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';
    
    fetch('../../inventory/api/create-inventory-item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            HotelPMS.Utils.showNotification('Inventory item added successfully!', 'success');
            closeAddItemModal();
            loadInventoryItems();
        } else {
            HotelPMS.Utils.showNotification(result.message || 'Error adding inventory item', 'error');
        }
    })
    .catch(error => {
        console.error('Error adding inventory item:', error);
        HotelPMS.Utils.showNotification('Error adding inventory item', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Utility functions
function loadInventoryCategories(selectId) {
    fetch('../../inventory/api/get-inventory-categories.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById(selectId);
                select.innerHTML = '<option value="">Select Category</option>';
                (data.categories || []).forEach(category => {
                    select.innerHTML += `<option value="${category.id}">${category.name}</option>`;
                });
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
        });
}

function loadInventoryCategories() {
    fetch('../../inventory/api/get-inventory-categories.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayInventoryCategories(data.categories || []);
            } else {
                HotelPMS.Utils.showNotification(data.message || 'Error loading categories', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            HotelPMS.Utils.showNotification('Error loading categories', 'error');
        });
}

function displayInventoryCategories(categories) {
    const container = document.getElementById('inventory-categories-container');
    
    if (!categories || categories.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-tags text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No categories found</h3>
                <p class="text-gray-500">Add some categories to get started.</p>
            </div>
        `;
        return;
    }
    
    const tableHtml = `
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${categories.map(category => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${category.name}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">${category.description || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${category.items_count}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="editCategory(${category.id})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteCategory(${category.id})" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = tableHtml;
}

function loadInventoryTransactions() {
    fetch('../../inventory/api/get-transaction-stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayInventoryTransactions(data.transactions || []);
            } else {
                HotelPMS.Utils.showNotification(data.message || 'Error loading transactions', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading transactions:', error);
            HotelPMS.Utils.showNotification('Error loading transactions', 'error');
        });
}

function displayInventoryTransactions(transactions) {
    const container = document.getElementById('inventory-transactions-container');
    
    if (!transactions || transactions.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exchange-alt text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No transactions found</h3>
                <p class="text-gray-500">No inventory transactions recorded.</p>
            </div>
        `;
        return;
    }
    
    const tableHtml = `
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${transactions.map(transaction => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatDate(transaction.transaction_date)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${transaction.item_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${getTransactionTypeClass(transaction.transaction_type)}">
                                    ${getTransactionTypeLabel(transaction.transaction_type)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${transaction.quantity}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">${transaction.reason || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${transaction.user_name}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = tableHtml;
}

// Helper functions for styling
function getOccupancyClass(rate) {
    if (rate >= 80) return 'bg-green-100 text-green-800';
    if (rate >= 60) return 'bg-yellow-100 text-yellow-800';
    return 'bg-red-100 text-red-800';
}

function getStatusClass(status) {
    switch (status) {
        case 'confirmed': return 'bg-blue-100 text-blue-800';
        case 'checked_in': return 'bg-green-100 text-green-800';
        case 'checked_out': return 'bg-gray-100 text-gray-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getStockStatusClass(current, minimum) {
    if (current <= minimum) return 'bg-red-100 text-red-800';
    if (current <= minimum * 1.5) return 'bg-yellow-100 text-yellow-800';
    return 'bg-green-100 text-green-800';
}

function getStockStatusLabel(current, minimum) {
    if (current <= minimum) return 'Low Stock';
    if (current <= minimum * 1.5) return 'Warning';
    return 'In Stock';
}

function getTransactionTypeClass(type) {
    switch (type) {
        case 'in': return 'bg-green-100 text-green-800';
        case 'out': return 'bg-red-100 text-red-800';
        case 'adjustment': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getTransactionTypeLabel(type) {
    switch (type) {
        case 'in': return 'Stock In';
        case 'out': return 'Stock Out';
        case 'adjustment': return 'Adjustment';
        default: return type;
    }
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Action functions (placeholder implementations)
function editInventoryItem(itemId) {
    HotelPMS.Utils.showNotification('Edit inventory item feature coming soon', 'info');
}

function adjustStock(itemId) {
    HotelPMS.Utils.showNotification('Adjust stock feature coming soon', 'info');
}

function editCategory(categoryId) {
    HotelPMS.Utils.showNotification('Edit category feature coming soon', 'info');
}

function deleteCategory(categoryId) {
    HotelPMS.Utils.showNotification('Delete category feature coming soon', 'info');
}

function openAddCategoryModal() {
    HotelPMS.Utils.showNotification('Add category feature coming soon', 'info');
}

function openAddTransactionModal() {
    HotelPMS.Utils.showNotification('Add transaction feature coming soon', 'info');
}
