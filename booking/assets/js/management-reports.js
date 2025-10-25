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
    loadGuestDemographicsChart();
    loadInventoryAnalytics();
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
    
    fetch('../../api/get-occupancy-data.php', {
        headers: { 'X-API-Key': 'pms_users_api_2024' },
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.daily && data.data.daily.length > 0) {
                // Process the daily occupancy data
                const dailyData = data.data.daily || [];
                const labels = dailyData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                });
                const values = dailyData.map(item => parseFloat(item.occupancy_rate) || 0);
                
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
                // Show a message when no data is available
                const ctx = document.getElementById('occupancyChart').getContext('2d');
                chartInstances.occupancyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['No Data'],
                        datasets: [{
                            label: 'Occupancy Rate (%)',
                            data: [0],
                            borderColor: '#E5E7EB',
                            backgroundColor: 'rgba(229, 231, 235, 0.1)',
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
                        }
                    }
                });
                console.warn('No occupancy data available for chart');
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
    
    fetch('../../api/get-revenue-data.php', {
        headers: { 'X-API-Key': 'pms_users_api_2024' },
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.daily && data.data.daily.length > 0) {
                // Process the daily revenue data
                const dailyData = data.data.daily || [];
                const labels = dailyData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                });
                const values = dailyData.map(item => parseFloat(item.daily_revenue) || 0);
                
                const ctx = document.getElementById('revenueChart').getContext('2d');
                chartInstances.revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Daily Revenue (₱)',
                            data: values,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
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
                // Show a message when no data is available
                const ctx = document.getElementById('revenueChart').getContext('2d');
                chartInstances.revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['No Data'],
                        datasets: [{
                            label: 'Daily Revenue (₱)',
                            data: [0],
                            borderColor: '#E5E7EB',
                            backgroundColor: 'rgba(229, 231, 235, 0.1)',
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
                        }
                    }
                });
                console.warn('No revenue data available for chart');
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
    switch(type) {
        case 'revenue':
            generateRevenueReport();
            break;
        case 'occupancy':
            generateOccupancyReport();
            break;
        case 'demographics':
            generateDemographicsReport();
            break;
        case 'inventory':
            generateInventoryReport();
            break;
        default:
            HotelPMS.Utils.showNotification('Report type not supported', 'error');
    }
}

function generateRevenueReport() {
    fetch('../../api/get-revenue-data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create a simple text report
                let reportContent = 'REVENUE REPORT\n';
                reportContent += '================\n\n';
                
                if (data.data.daily && data.data.daily.length > 0) {
                    reportContent += 'Daily Revenue (Last 30 Days):\n';
                    data.data.daily.forEach(day => {
                        reportContent += `${day.date}: ₱${parseFloat(day.daily_revenue).toLocaleString()}\n`;
                    });
                }
                
                if (data.data.breakdown && data.data.breakdown.length > 0) {
                    reportContent += '\nRevenue Breakdown:\n';
                    data.data.breakdown.forEach(item => {
                        reportContent += `${item.source}: ₱${parseFloat(item.amount).toLocaleString()}\n`;
                    });
                }
                
                // Download the report
                downloadTextFile(reportContent, 'revenue-report.txt');
                HotelPMS.Utils.showNotification('Revenue report generated successfully!', 'success');
            } else {
                HotelPMS.Utils.showNotification('Error generating revenue report', 'error');
            }
        })
        .catch(error => {
            console.error('Error generating revenue report:', error);
            HotelPMS.Utils.showNotification('Error generating revenue report', 'error');
        });
}

function generateOccupancyReport() {
    fetch('../../api/get-occupancy-data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let reportContent = 'OCCUPANCY REPORT\n';
                reportContent += '==================\n\n';
                
                if (data.data.daily && data.data.daily.length > 0) {
                    reportContent += 'Daily Occupancy (Last 30 Days):\n';
                    data.data.daily.forEach(day => {
                        reportContent += `${day.date}: ${day.occupancy_rate}% (${day.occupied_rooms}/${day.total_rooms} rooms)\n`;
                    });
                }
                
                if (data.data.by_type && data.data.by_type.length > 0) {
                    reportContent += '\nOccupancy by Room Type:\n';
                    data.data.by_type.forEach(type => {
                        reportContent += `${type.room_type}: ${type.occupancy_rate}% (${type.occupied_rooms}/${type.total_rooms} rooms)\n`;
                    });
                }
                
                downloadTextFile(reportContent, 'occupancy-report.txt');
                HotelPMS.Utils.showNotification('Occupancy report generated successfully!', 'success');
            } else {
                HotelPMS.Utils.showNotification('Error generating occupancy report', 'error');
            }
        })
        .catch(error => {
            console.error('Error generating occupancy report:', error);
            HotelPMS.Utils.showNotification('Error generating occupancy report', 'error');
        });
}

function generateDemographicsReport() {
    fetch('../../api/get-guest-demographics.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let reportContent = 'GUEST DEMOGRAPHICS REPORT\n';
                reportContent += '==========================\n\n';
                
                if (data.data.age_groups && data.data.age_groups.length > 0) {
                    reportContent += 'Age Groups:\n';
                    data.data.age_groups.forEach(group => {
                        reportContent += `${group.age_group}: ${group.count} guests\n`;
                    });
                }
                
                if (data.data.nationalities && data.data.nationalities.length > 0) {
                    reportContent += '\nTop Nationalities:\n';
                    data.data.nationalities.forEach(nationality => {
                        reportContent += `${nationality.nationality}: ${nationality.count} guests\n`;
                    });
                }
                
                if (data.data.genders && data.data.genders.length > 0) {
                    reportContent += '\nGender Distribution:\n';
                    data.data.genders.forEach(gender => {
                        reportContent += `${gender.gender}: ${gender.count} guests\n`;
                    });
                }
                
                if (data.data.guest_types && data.data.guest_types.length > 0) {
                    reportContent += '\nGuest Types:\n';
                    data.data.guest_types.forEach(type => {
                        reportContent += `${type.guest_type}: ${type.count} guests\n`;
                    });
                }
                
                downloadTextFile(reportContent, 'demographics-report.txt');
                HotelPMS.Utils.showNotification('Demographics report generated successfully!', 'success');
            } else {
                HotelPMS.Utils.showNotification('Error generating demographics report', 'error');
            }
        })
        .catch(error => {
            console.error('Error generating demographics report:', error);
            HotelPMS.Utils.showNotification('Error generating demographics report', 'error');
        });
}

function generateInventoryReport() {
    fetch('../../api/get-inventory-reports.php', {
        headers: { 'X-API-Key': 'pms_users_api_2024' },
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let reportContent = 'INVENTORY REPORT\n';
                reportContent += '==================\n\n';
                
                if (data.data.items && data.data.items.length > 0) {
                    reportContent += 'Inventory Items:\n';
                    data.data.items.forEach(item => {
                        reportContent += `${item.item_name}: ${item.current_stock}/${item.minimum_stock} (${item.stock_status})\n`;
                    });
                }
                
                if (data.data.categories && data.data.categories.length > 0) {
                    reportContent += '\nCategories:\n';
                    data.data.categories.forEach(category => {
                        reportContent += `${category.category_name}: ${category.item_count} items\n`;
                    });
                }
                
                downloadTextFile(reportContent, 'inventory-report.txt');
                HotelPMS.Utils.showNotification('Inventory report generated successfully!', 'success');
            } else {
                HotelPMS.Utils.showNotification('Error generating inventory report', 'error');
            }
        })
        .catch(error => {
            console.error('Error generating inventory report:', error);
            HotelPMS.Utils.showNotification('Error generating inventory report', 'error');
        });
}

function downloadTextFile(content, filename) {
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
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

// Guest Demographics Functions
function loadGuestDemographicsChart() {
    fetch('../../api/get-guest-demographics.php', {
        headers: { 'X-API-Key': 'pms_users_api_2024' },
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Create pie charts
                createAgeGroupsPieChart(data.data.age_groups);
                createNationalitiesPieChart(data.data.nationalities);
                createGenderPieChart(data.data.genders);
                createGuestTypesPieChart(data.data.guest_types);
                
                // Create additional data displays
                createAgeGroupChart(data.data.age_groups);
                createNationalityChart(data.data.nationalities);
                createGenderChart(data.data.genders);
                createGuestTypeChart(data.data.guest_types);
            } else {
                console.error('Failed to load guest demographics:', data.message);
                if (window.HotelPMS && HotelPMS.Utils) {
                    HotelPMS.Utils.showNotification(data.message || 'Unable to load demographics', 'warning');
                }
            }
        })
        .catch(error => {
            console.error('Error loading guest demographics:', error);
            if (window.HotelPMS && HotelPMS.Utils) {
                HotelPMS.Utils.showNotification('Error loading demographics', 'error');
            }
        });
}

function createAgeGroupsPieChart(ageGroups) {
    const ctx = document.getElementById('ageGroupsChart').getContext('2d');
    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'];
    
    chartInstances.ageGroupsChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ageGroups.map(group => group.age_group),
            datasets: [{
                data: ageGroups.map(group => group.count),
                backgroundColor: colors.slice(0, ageGroups.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function createNationalitiesPieChart(nationalities) {
    const ctx = document.getElementById('nationalitiesChart').getContext('2d');
    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'];
    
    chartInstances.nationalitiesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: nationalities.map(nationality => nationality.nationality),
            datasets: [{
                data: nationalities.map(nationality => nationality.count),
                backgroundColor: colors.slice(0, nationalities.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function createGenderPieChart(genders) {
    const ctx = document.getElementById('genderChart').getContext('2d');
    const colors = ['#3B82F6', '#EC4899', '#10B981'];
    
    chartInstances.genderChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: genders.map(gender => gender.gender),
            datasets: [{
                data: genders.map(gender => gender.count),
                backgroundColor: colors.slice(0, genders.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function createGuestTypesPieChart(guestTypes) {
    const ctx = document.getElementById('guestTypesChart').getContext('2d');
    const colors = ['#F59E0B', '#6B7280'];
    
    chartInstances.guestTypesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: guestTypes.map(type => type.guest_type),
            datasets: [{
                data: guestTypes.map(type => type.count),
                backgroundColor: colors.slice(0, guestTypes.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function createAgeGroupChart(ageGroups) {
    const container = document.getElementById('guest-demographics-container');
    if (!container) return;
    
    const chartHtml = `
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Guest Age Groups</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                ${ageGroups.map(group => `
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">${group.count}</div>
                        <div class="text-sm text-gray-600">${group.age_group}</div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    container.innerHTML += chartHtml;
}

function createNationalityChart(nationalities) {
    const container = document.getElementById('guest-demographics-container');
    if (!container) return;
    
    const chartHtml = `
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Top Nationalities</h4>
            <div class="space-y-3">
                ${nationalities.map(nationality => `
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">${nationality.nationality}</span>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${(nationality.count / nationalities[0].count) * 100}%"></div>
                            </div>
                            <span class="text-sm text-gray-600">${nationality.count}</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    container.innerHTML += chartHtml;
}

function createGenderChart(genders) {
    const container = document.getElementById('guest-demographics-container');
    if (!container) return;
    
    const chartHtml = `
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Gender Distribution</h4>
            <div class="grid grid-cols-2 gap-4">
                ${genders.map(gender => `
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">${gender.count}</div>
                        <div class="text-sm text-gray-600">${gender.gender}</div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    container.innerHTML += chartHtml;
}

function createGuestTypeChart(guestTypes) {
    const container = document.getElementById('guest-demographics-container');
    if (!container) return;
    
    const chartHtml = `
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">VIP vs Regular Guests</h4>
            <div class="grid grid-cols-2 gap-4">
                ${guestTypes.map(type => `
                    <div class="text-center p-4 ${type.guest_type === 'VIP' ? 'bg-yellow-50' : 'bg-gray-50'} rounded-lg">
                        <div class="text-2xl font-bold ${type.guest_type === 'VIP' ? 'text-yellow-600' : 'text-gray-600'}">${type.count}</div>
                        <div class="text-sm text-gray-600">${type.guest_type}</div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    container.innerHTML += chartHtml;
}

// Inventory Analytics Functions
function loadInventoryAnalytics() {
    fetch('../../api/get-inventory-reports.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Create inventory charts
                createInventoryCategoryChart(data.data.categories || []);
                createStockStatusChart(data.data.items || []);
                createLowStockChart(data.data.items || []);
                createInventoryValueChart(data.data.categories || []);
            } else {
                console.warn('No inventory data available for analytics:', data.message || 'Unknown error');
                // Create empty charts when no data is available
                createEmptyInventoryCharts();
            }
        })
        .catch(error => {
            console.error('Error loading inventory analytics:', error);
            // Create empty charts when there's an error
            createEmptyInventoryCharts();
        });
}

function createEmptyInventoryCharts() {
    // Create empty charts for all inventory chart elements
    const chartIds = ['inventoryCategoryChart', 'stockStatusChart', 'lowStockChart', 'inventoryValueChart'];
    
    chartIds.forEach(chartId => {
        const ctx = document.getElementById(chartId);
        if (ctx) {
            const chartCtx = ctx.getContext('2d');
            chartInstances[chartId] = new Chart(chartCtx, {
                type: 'bar',
                data: {
                    labels: ['No Data'],
                    datasets: [{
                        label: 'No Data Available',
                        data: [0],
                        backgroundColor: '#E5E7EB'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
}

function createInventoryCategoryChart(categories) {
    const ctx = document.getElementById('inventoryCategoryChart').getContext('2d');
    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'];
    
    if (!categories || categories.length === 0) {
        chartInstances.inventoryCategoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['No Data'],
                datasets: [{
                    label: 'Items Count',
                    data: [0],
                    backgroundColor: '#E5E7EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        return;
    }
    
    chartInstances.inventoryCategoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categories.map(cat => cat.category_name || 'Unknown'),
            datasets: [{
                label: 'Items Count',
                data: categories.map(cat => cat.item_count || 0),
                backgroundColor: colors.slice(0, categories.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createStockStatusChart(items) {
    const ctx = document.getElementById('stockStatusChart').getContext('2d');
    
    if (!items || items.length === 0) {
        chartInstances.stockStatusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['No Data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#E5E7EB'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        return;
    }
    
    // Calculate stock status distribution
    const statusCounts = {
        'In Stock': 0,
        'Low Stock': 0,
        'Out of Stock': 0
    };
    
    items.forEach(item => {
        if (item.current_stock <= 0) {
            statusCounts['Out of Stock']++;
        } else if (item.current_stock <= item.minimum_stock) {
            statusCounts['Low Stock']++;
        } else {
            statusCounts['In Stock']++;
        }
    });
    
    chartInstances.stockStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusCounts),
            datasets: [{
                data: Object.values(statusCounts),
                backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function createLowStockChart(items) {
    const ctx = document.getElementById('lowStockChart').getContext('2d');
    
    if (!items || items.length === 0) {
        chartInstances.lowStockChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['No Data'],
                datasets: [{
                    label: 'Current Stock',
                    data: [0],
                    backgroundColor: '#E5E7EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
        return;
    }
    
    // Get low stock items
    const lowStockItems = items.filter(item => item.current_stock <= item.minimum_stock).slice(0, 10);
    
    if (lowStockItems.length === 0) {
        chartInstances.lowStockChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['No Low Stock Items'],
                datasets: [{
                    label: 'Current Stock',
                    data: [0],
                    backgroundColor: '#10B981',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
        return;
    }
    
    chartInstances.lowStockChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: lowStockItems.map(item => item.item_name || 'Unknown'),
            datasets: [{
                label: 'Current Stock',
                data: lowStockItems.map(item => item.current_stock || 0),
                backgroundColor: '#EF4444',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createInventoryValueChart(categories) {
    const ctx = document.getElementById('inventoryValueChart').getContext('2d');
    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'];
    
    if (!categories || categories.length === 0) {
        chartInstances.inventoryValueChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['No Data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#E5E7EB'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        return;
    }
    
    // Calculate total value for each category (simplified)
    const categoryValues = categories.map(cat => ({
        name: cat.category_name || 'Unknown',
        value: (cat.item_count || 0) * 100 // Simplified calculation
    }));
    
    chartInstances.inventoryValueChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: categoryValues.map(cat => cat.name),
            datasets: [{
                data: categoryValues.map(cat => cat.value),
                backgroundColor: colors.slice(0, categoryValues.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
