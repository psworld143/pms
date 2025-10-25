<?php
require_once dirname(__DIR__, 3) . '/vps_session_fix.php';
// Error handling for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);

/**
 * Invoice Management
 * Hotel PMS Training System for Students
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Load dynamic data
$invoiceMetrics = getInvoiceMetrics();
$invoices = getBills('', '', null);

// Set page title
$page_title = 'Invoice Management';

// Include header
include '../../includes/header-unified.php';
// Include sidebar
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 lg:mb-8 gap-4">
                <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Invoice Management</h2>
                <div class="flex items-center space-x-4">
                    <button onclick="openCreateInvoiceModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Create Invoice
                    </button>
                    <button onclick="exportInvoices()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-download mr-2"></i>Export Invoices
                    </button>
                </div>
            </div>

            <!-- Invoice Statistics (Dynamic) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-file-invoice text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Invoices</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($invoiceMetrics['total_invoices']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Paid Invoices</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($invoiceMetrics['paid_count']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($invoiceMetrics['pending_count']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Overdue</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($invoiceMetrics['overdue_count']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Invoices</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Statuses</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="overdue">Overdue</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <select id="date-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">Last 3 Months</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" id="search-filter" placeholder="Search invoices..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button onclick="filterInvoices()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button onclick="openCreateInvoiceModal()" class="w-full flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-plus text-blue-600 mr-3"></i>
                                <span class="text-blue-800 font-medium">Create New Invoice</span>
                            </div>
                            <i class="fas fa-arrow-right text-blue-600"></i>
                        </button>
                        <button onclick="exportInvoices()" class="w-full flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-download text-green-600 mr-3"></i>
                                <span class="text-green-800 font-medium">Export All Invoices</span>
                            </div>
                            <i class="fas fa-arrow-right text-green-600"></i>
                        </button>
                        <button onclick="sendReminders()" class="w-full flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition-colors">
                            <div class="flex items-center">
                                <i class="fas fa-bell text-yellow-600 mr-3"></i>
                                <span class="text-yellow-800 font-medium">Send Reminders</span>
                            </div>
                            <i class="fas fa-arrow-right text-yellow-600"></i>
                        </button>
                    </div>
                </div>

            <!-- Invoice Summary (Dynamic) -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Invoice Summary</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Revenue:</span>
                        <span class="font-semibold text-gray-900">₱<?php echo number_format($invoiceMetrics['total_revenue'], 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Outstanding Amount:</span>
                        <span class="font-semibold text-red-600">₱<?php echo number_format($invoiceMetrics['outstanding_amount'], 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Average Invoice:</span>
                        <span class="font-semibold text-gray-900">₱<?php echo number_format($invoiceMetrics['average_invoice'], 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Payment Rate:</span>
                        <span class="font-semibold text-green-600"><?php
                            $paid = max(0, (int)$invoiceMetrics['paid_count']);
                            $total = max(1, (int)$invoiceMetrics['total_invoices']);
                            echo number_format(($paid / $total) * 100, 1); ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices Table (Dynamic) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">All Invoices</h3>
                </div>
                <div class="overflow-x-auto">
                    <?php if (!empty($invoices)): ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo htmlspecialchars($inv['bill_number']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                                    <span class="text-white text-xs font-medium"><?php echo strtoupper(substr($inv['guest_name'],0,1)); ?></span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($inv['guest_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Room <?php echo htmlspecialchars($inv['room_number']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱<?php echo number_format($inv['total_amount'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($inv['bill_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($inv['due_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                            $cls = 'bg-gray-100 text-gray-800';
                                            if ($inv['status'] === 'paid') $cls = 'bg-green-100 text-green-800';
                                            elseif ($inv['status'] === 'pending') $cls = 'bg-yellow-100 text-yellow-800';
                                            elseif ($inv['status'] === 'overdue') $cls = 'bg-red-100 text-red-800';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $cls; ?>">
                                            <?php echo htmlspecialchars(ucfirst($inv['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewInvoice(<?php echo $inv['id']; ?>)" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                        <button onclick="downloadInvoice(<?php echo $inv['id']; ?>)" class="text-green-600 hover:text-green-900">Download</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-500">No invoices found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Create Invoice Modal -->
        <div id="create-invoice-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Create New Invoice</h3>
                    <button onclick="closeCreateInvoiceModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="create-invoice-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Guest *</label>
                            <select name="guest_id" id="invoice_guest_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Guest</option>
                                <?php
                                $guests = getAllGuests();
                                foreach ($guests as $guest): ?>
                                    <option value="<?php echo $guest['id']; ?>"><?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reservation *</label>
                            <select name="reservation_id" id="invoice_reservation_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Reservation</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
                            <input type="number" name="total_amount" id="invoice_amount" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter amount">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Due Date *</label>
                            <input type="date" name="due_date" id="invoice_due_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" id="invoice_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <input type="text" name="description" id="invoice_description" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" id="invoice_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeCreateInvoiceModal()" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Create Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Invoice Modal -->
        <div id="view-invoice-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Invoice Details</h3>
                    <button onclick="closeViewInvoiceModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="invoice-details-content">
                    <!-- Invoice details will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Include footer -->
        <?php include '../../includes/footer.php'; ?>
        
        <script>
        // Suppress classifier.js shader errors
        window.addEventListener('error', function(e) {
            if (e.message && e.message.includes('Failed to link vertex and fragment shaders')) {
                e.preventDefault();
                return false;
            }
        });

        // Suppress unhandled promise rejections from classifier.js
        window.addEventListener('unhandledrejection', function(e) {
            if (e.reason && e.reason.message && e.reason.message.includes('Failed to link vertex and fragment shaders')) {
                e.preventDefault();
                return false;
            }
        });

        // Invoice Management JavaScript
        function openCreateInvoiceModal() {
            document.getElementById('create-invoice-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCreateInvoiceModal() {
            document.getElementById('create-invoice-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('create-invoice-form').reset();
        }

        function openViewInvoiceModal() {
            document.getElementById('view-invoice-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeViewInvoiceModal() {
            document.getElementById('view-invoice-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function viewInvoice(invoiceId) {
            // Load invoice details and show modal
            fetch(`../../api/get-bill-details.php?id=${invoiceId}`, {
                credentials: 'include',
                headers: {
                    'X-API-Key': 'pms_users_api_2024',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayInvoiceDetails(data.bill);
                    openViewInvoiceModal();
                } else {
                    alert('Error loading invoice details: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading invoice details');
            });
        }

        function displayInvoiceDetails(bill) {
            const content = document.getElementById('invoice-details-content');
            content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-4">Invoice Information</h4>
                        <div class="space-y-2">
                            <p><span class="font-medium">Invoice #:</span> ${bill.bill_number || 'N/A'}</p>
                            <p><span class="font-medium">Guest:</span> ${bill.guest_name || 'N/A'}</p>
                            <p><span class="font-medium">Room:</span> ${bill.room_number || 'N/A'}</p>
                            <p><span class="font-medium">Amount:</span> ₱${parseFloat(bill.total_amount || 0).toFixed(2)}</p>
                            <p><span class="font-medium">Status:</span> <span class="px-2 py-1 rounded-full text-xs font-semibold ${getStatusClass(bill.status)}">${bill.status || 'N/A'}</span></p>
                            <p><span class="font-medium">Date:</span> ${bill.bill_date || 'N/A'}</p>
                            <p><span class="font-medium">Due Date:</span> ${bill.due_date || 'N/A'}</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-4">Actions</h4>
                        <div class="space-y-2">
                            <button onclick="downloadInvoice(${bill.id})" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i>Download PDF
                            </button>
                            <button onclick="markAsPaid(${bill.id})" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                <i class="fas fa-check mr-2"></i>Mark as Paid
                            </button>
                            <button onclick="sendReminder(${bill.id})" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                                <i class="fas fa-bell mr-2"></i>Send Reminder
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function getStatusClass(status) {
            switch(status) {
                case 'paid': return 'bg-green-100 text-green-800';
                case 'pending': return 'bg-yellow-100 text-yellow-800';
                case 'overdue': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function downloadInvoice(invoiceId) {
            window.open(`../../api/generate-bill-pdf.php?id=${invoiceId}`, '_blank');
        }

        function exportInvoices() {
            const status = document.getElementById('status-filter').value;
            const date = document.getElementById('date-filter').value;
            
            let url = '../../api/export-invoices-csv.php?';
            if (status) url += `status=${status}&`;
            if (date) url += `date=${date}&`;
            
            window.open(url, '_blank');
        }

        function sendReminders() {
            if (confirm('Send payment reminders to all pending invoices?')) {
                fetch('../../api/send-invoice-reminders.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'X-API-Key': 'pms_users_api_2024',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reminders sent successfully!');
                    } else {
                        alert('Error sending reminders: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error sending reminders');
                });
            }
        }

        function filterInvoices() {
            const status = document.getElementById('status-filter').value;
            const date = document.getElementById('date-filter').value;
            const search = document.getElementById('search-filter').value;
            
            // Reload page with filters
            let url = window.location.pathname + '?';
            if (status) url += `status=${status}&`;
            if (date) url += `date=${date}&`;
            if (search) url += `search=${encodeURIComponent(search)}&`;
            
            window.location.href = url;
        }

        function markAsPaid(invoiceId) {
            if (confirm('Mark this invoice as paid?')) {
                fetch('../../api/update-bill-status.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'X-API-Key': 'pms_users_api_2024',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        bill_id: invoiceId,
                        status: 'paid'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Invoice marked as paid!');
                        location.reload();
                    } else {
                        alert('Error updating invoice: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating invoice');
                });
            }
        }

        function sendReminder(invoiceId) {
            if (confirm('Send payment reminder for this invoice?')) {
                fetch('../../api/send-invoice-reminders.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'X-API-Key': 'pms_users_api_2024',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        bill_id: invoiceId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reminder sent successfully!');
                    } else {
                        alert('Error sending reminder: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error sending reminder');
                });
            }
        }

        // Handle form submission
        document.getElementById('create-invoice-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Validate that a reservation is selected
            if (!data.reservation_id || data.reservation_id === '') {
                alert('Please select a reservation for this guest.');
                return;
            }
            
            // Validate that guest is selected
            if (!data.guest_id || data.guest_id === '') {
                alert('Please select a guest.');
                return;
            }
            
            // Validate amount
            if (!data.total_amount || parseFloat(data.total_amount) <= 0) {
                alert('Please enter a valid amount.');
                return;
            }
            
            // Create bill items from the single amount field
            const amount = parseFloat(data.total_amount);
            const description = data.description || 'Hotel Services';
            
            // Validate amount is a valid number
            if (isNaN(amount) || amount <= 0) {
                alert('Please enter a valid amount greater than 0.');
                return;
            }
            
            const billData = {
                reservation_id: parseInt(data.reservation_id),
                bill_date: new Date().toISOString().split('T')[0],
                due_date: data.due_date,
                status: data.status || 'pending',
                notes: data.notes || null,
                items: [{
                    description: description,
                    quantity: 1,
                    unit_price: amount,
                    total_amount: amount
                }]
            };
            
            fetch('../../api/create-bill.php', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'X-API-Key': 'pms_users_api_2024',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(billData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Invoice created successfully!');
                    closeCreateInvoiceModal();
                    location.reload();
                } else {
                    alert('Error creating invoice: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating invoice: ' + error.message);
            });
        });

        // Load reservations when guest is selected
        document.getElementById('invoice_guest_id').addEventListener('change', function() {
            const guestId = this.value;
            const reservationSelect = document.getElementById('invoice_reservation_id');
            
            console.log('Guest selected:', guestId); // Debug log
            
            if (guestId) {
                // Clear existing options first
                reservationSelect.innerHTML = '<option value="">Loading reservations...</option>';
                
                fetch(`../../api/get-guest-reservations.php?guest_id=${guestId}`, {
                    credentials: 'include',
                    headers: {
                        'X-API-Key': 'pms_users_api_2024',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status); // Debug log
                    return response.json();
                })
                .then(data => {
                    console.log('Reservations data:', data); // Debug log
                    reservationSelect.innerHTML = '<option value="">Select Reservation</option>';
                    
                    if (data.success && data.reservations && data.reservations.length > 0) {
                        data.reservations.forEach(reservation => {
                            const option = document.createElement('option');
                            option.value = reservation.id;
                            option.textContent = `Reservation #${reservation.id} - Room ${reservation.room_number} (${reservation.status})`;
                            reservationSelect.appendChild(option);
                        });
                        console.log(`Loaded ${data.reservations.length} reservations for guest ${guestId}`);
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No reservations found for this guest';
                        option.disabled = true;
                        reservationSelect.appendChild(option);
                        console.log('No reservations found for guest:', guestId);
                        
                        // Show a helpful message
                        alert('This guest has no reservations. Please select a different guest or create a reservation first.');
                    }
                })
                .catch(error => {
                    console.error('Error loading reservations:', error);
                    reservationSelect.innerHTML = '<option value="">Error loading reservations</option>';
                });
            } else {
                reservationSelect.innerHTML = '<option value="">Select Reservation</option>';
            }
        });

        // Set default due date to 7 days from now
        document.addEventListener('DOMContentLoaded', function() {
            const dueDateInput = document.getElementById('invoice_due_date');
            if (dueDateInput) {
                const futureDate = new Date();
                futureDate.setDate(futureDate.getDate() + 7);
                dueDateInput.value = futureDate.toISOString().split('T')[0];
            }
        });
        </script>
    </body>
</html>
