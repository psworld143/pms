<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Get user certificates with enhanced data
$certificates = [];
try {
    $stmt = $pdo->prepare("
        SELECT tc.*,
               CASE 
                   WHEN ta.scenario_type = 'training' THEN ts.title
                   WHEN ta.scenario_type = 'customer_service' THEN css.title
                   WHEN ta.scenario_type = 'problem' THEN ps.title
                ELSE 'Unknown Scenario'
            END as scenario_title,
               ta.scenario_type,
            ta.score,
            ta.completed_at
        FROM training_certificates tc
        LEFT JOIN training_attempts ta ON tc.attempt_id = ta.id
        LEFT JOIN training_scenarios ts ON ta.scenario_id = ts.id AND ta.scenario_type = 'training'
        LEFT JOIN customer_service_scenarios css ON ta.scenario_id = css.id AND ta.scenario_type = 'customer_service'
        LEFT JOIN problem_scenarios ps ON ta.scenario_id = ps.id AND ta.scenario_type = 'problem'
        WHERE tc.user_id = ?
        ORDER BY tc.earned_at DESC
    ");
    $stmt->execute([$user_id]);
    $certificates = $stmt->fetchAll() ?: [];
} catch (Exception $e) {
    error_log("Error getting certificates: " . $e->getMessage());
    $certificates = [];
}

// Get potential certificates (completed attempts with high scores but no certificate yet)
$potential_certificates = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            ta.id as attempt_id,
            ta.scenario_id,
            ta.scenario_type,
            ta.score,
            ta.completed_at,
            CASE 
                WHEN ta.scenario_type = 'training' THEN ts.title
                WHEN ta.scenario_type = 'customer_service' THEN css.title
                WHEN ta.scenario_type = 'problem' THEN ps.title
                ELSE 'Unknown Scenario'
            END as scenario_title
        FROM training_attempts ta
        LEFT JOIN training_scenarios ts ON ta.scenario_id = ts.id AND ta.scenario_type = 'training'
        LEFT JOIN customer_service_scenarios css ON ta.scenario_id = css.id AND ta.scenario_type = 'customer_service'
        LEFT JOIN problem_scenarios ps ON ta.scenario_id = ps.id AND ta.scenario_type = 'problem'
        WHERE ta.user_id = ? 
        AND ta.status = 'completed' 
        AND ta.score >= 80
        AND NOT EXISTS (
            SELECT 1 FROM training_certificates tc 
            WHERE tc.attempt_id = ta.id
        )
        ORDER BY ta.completed_at DESC
    ");
    $stmt->execute([$user_id]);
    $potential_certificates = $stmt->fetchAll() ?: [];
} catch (Exception $e) {
    error_log("Error getting potential certificates: " . $e->getMessage());
    $potential_certificates = [];
}

// Get certificate statistics
$cert_stats = [
    'total' => count($certificates),
    'training' => count(array_filter($certificates, function($c) { return $c['scenario_type'] === 'training'; })),
    'customer_service' => count(array_filter($certificates, function($c) { return $c['scenario_type'] === 'customer_service'; })),
    'problem' => count(array_filter($certificates, function($c) { return $c['scenario_type'] === 'problem'; }))
];

$page_title = 'Certificates';
include '../../includes/header-unified.php';
include '../../includes/sidebar-unified.php';
?>

        <!-- Main Content -->
        <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1 transition-all duration-300">
            <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                <h1 class="text-2xl font-bold text-gray-900">Certificates</h1>
                <p class="text-gray-600 mt-1">Your training achievements and certificates</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm text-gray-500">Welcome back,</p>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user_name); ?></p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-certificate text-white text-xl"></i>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Certificate Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-certificate text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Certificates</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $cert_stats['total']; ?></p>
                        </div>
                    </div>
                </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-play-circle text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Training</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $cert_stats['training']; ?></p>
                        </div>
                    </div>
                </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-headset text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Customer Service</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $cert_stats['customer_service']; ?></p>
                        </div>
                    </div>
                </div>
        
        <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-lightbulb text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Problem Solving</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $cert_stats['problem']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

    <!-- Certificate Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Certificates</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Certificate Type</label>
                <select id="type-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    <option value="">All Types</option>
                    <option value="training">Training Scenarios</option>
                    <option value="customer_service">Customer Service</option>
                    <option value="problem">Problem Solving</option>
                </select>
                    </div>

            <!-- Date Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Earned Date</label>
                <select id="date-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    <option value="">All Time</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                </select>
                    </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" id="search-input" placeholder="Search certificates..." 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>
            </div>

            <!-- Certificates Grid -->
    <div id="certificates-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($certificates)): ?>
            <div class="col-span-full text-center py-12">
                <i class="fas fa-certificate text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No certificates yet</h3>
                <p class="text-gray-500 mb-4">Complete training scenarios with high scores to earn certificates</p>
                <a href="scenarios.php" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-yellow-600 to-orange-600 text-white rounded-md hover:from-yellow-700 hover:to-orange-700 transition-all duration-300">
                    <i class="fas fa-play mr-2"></i>
                    Start Training
                </a>
                            </div>
        <?php else: ?>
            <?php foreach ($certificates as $certificate): ?>
                <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-all duration-300 certificate-card" 
                     data-type="<?php echo htmlspecialchars($certificate['scenario_type']); ?>"
                     data-date="<?php echo date('Y-m', strtotime($certificate['earned_at'])); ?>"
                     data-title="<?php echo htmlspecialchars(strtolower($certificate['scenario_title'])); ?>">
                    
                    <!-- Certificate Header -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($certificate['scenario_title']); ?></h3>
                                <p class="text-sm text-gray-600 mb-3">
                                    <?php echo ucfirst(str_replace('_', ' ', $certificate['scenario_type'])); ?> Certificate
                                </p>
                                        </div>
                            <div class="flex flex-col items-end space-y-2">
                                <!-- Type Badge -->
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                            <?php
                                    switch($certificate['scenario_type']) {
                                        case 'training': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'customer_service': echo 'bg-green-100 text-green-800'; break;
                                        case 'problem': echo 'bg-purple-100 text-purple-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $certificate['scenario_type'])); ?>
                                        </span>
                                
                                <!-- Score Badge -->
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                            <?php
                                    if ($certificate['score'] >= 90) echo 'bg-green-100 text-green-800';
                                    elseif ($certificate['score'] >= 80) echo 'bg-blue-100 text-blue-800';
                                    elseif ($certificate['score'] >= 70) echo 'bg-yellow-100 text-yellow-800';
                                    else echo 'bg-gray-100 text-gray-800';
                                    ?>">
                                    <?php echo $certificate['score']; ?>%
                                </span>
                    </div>
                </div>

                        <!-- Certificate Details -->
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div>
                                <p class="text-2xl font-bold text-yellow-600"><?php echo $certificate['score']; ?>%</p>
                                <p class="text-xs text-gray-500">Score</p>
                                        </div>
                                        <div>
                                <p class="text-2xl font-bold text-blue-600"><?php echo date('M j', strtotime($certificate['earned_at'])); ?></p>
                                <p class="text-xs text-gray-500">Earned</p>
                    </div>
                </div>
            </div>

                    <!-- Certificate Actions -->
                    <div class="p-6">
                        <div class="flex space-x-3">
                            <button onclick="viewCertificate(<?php echo $certificate['id']; ?>)" 
                                    class="flex-1 bg-gradient-to-r from-yellow-600 to-orange-600 text-white px-4 py-2 rounded-md hover:from-yellow-700 hover:to-orange-700 transition-all duration-300 font-medium">
                                <i class="fas fa-eye mr-2"></i>
                                View
                            </button>
                            <button onclick="downloadCertificate(<?php echo $certificate['id']; ?>)" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                                <i class="fas fa-download"></i>
                            </button>
                            <button onclick="shareCertificate(<?php echo $certificate['id']; ?>)" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                                <i class="fas fa-share"></i>
                            </button>
                        </div>
                                </div>
                            </div>
            <?php endforeach; ?>
        <?php endif; ?>
                </div>
</main>

<!-- Certificate View Modal -->
<div id="certificate-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Certificate Details</h3>
            <button onclick="closeCertificateModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
            </div>

        <div id="certificate-content">
            <!-- Certificate content will be loaded here -->
                        </div>
        
        <div class="flex justify-end space-x-4 mt-6">
            <button onclick="closeCertificateModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                Close
                    </button>
            <button onclick="downloadCertificateFromModal()" class="px-4 py-2 bg-gradient-to-r from-yellow-600 to-orange-600 text-white rounded-md hover:from-yellow-700 hover:to-orange-700 transition-all duration-300">
                <i class="fas fa-download mr-2"></i>Download
                    </button>
                </div>
            </div>
    </div>

<script>
let currentCertificateId = null;

// Filter functionality - with null checks
document.addEventListener('DOMContentLoaded', function() {
    const typeFilter = document.getElementById('type-filter');
    const dateFilter = document.getElementById('date-filter');
    const searchInput = document.getElementById('search-input');
    
    if (typeFilter) {
        typeFilter.addEventListener('change', filterCertificates);
    }
    if (dateFilter) {
        dateFilter.addEventListener('change', filterCertificates);
    }
    if (searchInput) {
        searchInput.addEventListener('input', searchCertificates);
    }
});

function filterCertificates() {
    const typeFilter = document.getElementById('type-filter');
    const dateFilter = document.getElementById('date-filter');
    const cards = document.querySelectorAll('.certificate-card');
    
    if (!typeFilter || !dateFilter) return;
    
    const type = typeFilter.value;
    const date = dateFilter.value;
    
    cards.forEach(card => {
        const cardType = card.dataset.type;
        const cardDate = card.dataset.date;
        const currentDate = new Date();
        
        let showCard = true;
        
        // Type filter
        if (type && cardType !== type) {
            showCard = false;
        }
        
        // Date filter
        if (date) {
            const cardDateObj = new Date(cardDate + '-01');
            switch(date) {
                case 'this_month':
                    if (cardDateObj.getMonth() !== currentDate.getMonth() || 
                        cardDateObj.getFullYear() !== currentDate.getFullYear()) {
                        showCard = false;
                    }
                    break;
                case 'last_month':
                    const lastMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1);
                    if (cardDateObj.getMonth() !== lastMonth.getMonth() || 
                        cardDateObj.getFullYear() !== lastMonth.getFullYear()) {
                        showCard = false;
                    }
                    break;
                case 'this_year':
                    if (cardDateObj.getFullYear() !== currentDate.getFullYear()) {
                        showCard = false;
                    }
                    break;
            }
        }
        
        card.style.display = showCard ? 'block' : 'none';
    });
}

function searchCertificates() {
    const searchInput = document.getElementById('search-input');
    const cards = document.querySelectorAll('.certificate-card');
    
    if (!searchInput) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    
    cards.forEach(card => {
        const title = card.dataset.title;
        
        if (title.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function viewCertificate(certificateId) {
    currentCertificateId = certificateId;
    
    fetch(`../../api/training/get-certificate-details.php?id=${certificateId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openCertificateModal(data.certificate);
            } else {
                alert('Error loading certificate: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading certificate:', error);
            alert('Error loading certificate');
        });
}

function openCertificateModal(certificate) {
    document.getElementById('certificate-content').innerHTML = `
        <div class="space-y-6">
            <div class="text-center">
                <div class="w-24 h-24 bg-gradient-to-r from-yellow-400 to-orange-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-certificate text-white text-3xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Certificate of Completion</h2>
                <p class="text-gray-600">This certifies that</p>
                <p class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($user_name); ?></p>
                <p class="text-gray-600">has successfully completed</p>
                <p class="text-lg font-medium text-gray-900">${certificate.scenario_title}</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Certificate Type</p>
                        <p class="font-medium text-gray-900">${certificate.scenario_type}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Score Achieved</p>
                        <p class="font-medium text-gray-900">${certificate.score}%</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Date Earned</p>
                        <p class="font-medium text-gray-900">${new Date(certificate.earned_at).toLocaleDateString()}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Certificate ID</p>
                        <p class="font-medium text-gray-900">#${certificate.id}</p>
                    </div>
                </div>
            </div>
            
            <div class="text-center text-sm text-gray-500">
                <p>This certificate is digitally verified and can be shared or downloaded.</p>
            </div>
        </div>
    `;
    
    document.getElementById('certificate-modal').classList.remove('hidden');
}

function closeCertificateModal() {
    document.getElementById('certificate-modal').classList.add('hidden');
}

function downloadCertificate(certificateId) {
    window.open(`../../modules/training/download-certificate.php?id=${certificateId}`, '_blank');
}

function downloadCertificateFromModal() {
    if (currentCertificateId) {
        downloadCertificate(currentCertificateId);
    }
}

function shareCertificate(certificateId) {
    if (navigator.share) {
        navigator.share({
            title: 'Training Certificate',
            text: 'I earned a certificate in hotel management training!',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Certificate link copied to clipboard!');
        });
    }
}
    </script>

<?php include '../../includes/footer.php'; ?>