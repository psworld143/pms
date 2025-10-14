<?php
/**
 * Mobile Tutorial Interface
 * Hotel PMS Training System - Interactive Tutorials
 */

session_start();
require_once '../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // For testing
}

// Set page title
$page_title = 'Mobile Tutorials';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title><?php echo $page_title; ?> - Hotel PMS Training System</title>
    <link rel="icon" type="image/png" href="../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#3B82F6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PMS Tutorials">
    
    <style>
        /* Mobile-first responsive design */
        .mobile-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .mobile-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .mobile-content {
            flex: 1;
            padding: 1rem;
            padding-bottom: 5rem; /* Space for bottom navigation */
        }
        
        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 40;
            background: white;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .mobile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        
        .mobile-button {
            min-height: 44px; /* iOS touch target minimum */
            min-width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .mobile-button:active {
            transform: scale(0.98);
        }
        
        .mobile-input {
            min-height: 44px;
            padding: 12px 16px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            font-size: 16px; /* Prevents zoom on iOS */
        }
        
        .mobile-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .swipe-indicator {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 2px;
        }
        
        .swipe-indicator::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 20px;
            height: 4px;
            background: white;
            border-radius: 2px;
            animation: swipe 2s infinite;
        }
        
        @keyframes swipe {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(20px); }
        }
        
        .touch-feedback {
            position: relative;
            overflow: hidden;
        }
        
        .touch-feedback::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s ease, height 0.3s ease;
        }
        
        .touch-feedback:active::before {
            width: 200px;
            height: 200px;
        }
        
        /* Responsive breakpoints */
        @media (max-width: 640px) {
            .mobile-content {
                padding: 0.75rem;
            }
            
            .mobile-card {
                margin-bottom: 0.75rem;
            }
        }
        
        @media (min-width: 768px) {
            .mobile-content {
                max-width: 768px;
                margin: 0 auto;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .mobile-header {
                background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            }
            
            .mobile-bottom-nav {
                background: #1f2937;
                border-top-color: #374151;
            }
        }
        
        /* High contrast mode */
        @media (prefers-contrast: high) {
            .mobile-button {
                border: 2px solid currentColor;
            }
            
            .mobile-input {
                border-width: 3px;
            }
        }
        
        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .swipe-indicator::after {
                animation: none;
            }
            
            .touch-feedback::before {
                transition: none;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="mobile-container">
        <!-- Mobile Header -->
        <header class="mobile-header text-white">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center">
                    <button id="back-button" class="mr-4 p-2 rounded-lg hover:bg-white hover:bg-opacity-20">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </button>
                    <h1 class="text-lg font-semibold">Tutorials</h1>
                </div>
                <div class="flex items-center space-x-2">
                    <button id="search-button" class="p-2 rounded-lg hover:bg-white hover:bg-opacity-20">
                        <i class="fas fa-search text-xl"></i>
                    </button>
                    <button id="menu-button" class="p-2 rounded-lg hover:bg-white hover:bg-opacity-20">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Search Bar (Hidden by default) -->
            <div id="search-bar" class="hidden px-4 pb-4">
                <input type="text" id="search-input" placeholder="Search tutorials..." 
                       class="w-full mobile-input bg-white text-gray-900">
            </div>
        </header>

        <!-- Mobile Content -->
        <main class="mobile-content">
            <!-- Welcome Section -->
            <div class="mobile-card p-6 mb-4">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-graduation-cap text-2xl text-blue-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Welcome to Mobile Tutorials</h2>
                    <p class="text-gray-600 text-sm">Learn hotel operations on the go with interactive, hands-on practice.</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="mobile-card p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600" id="mobile-total-modules">0</div>
                    <div class="text-sm text-gray-600">Modules</div>
                </div>
                <div class="mobile-card p-4 text-center">
                    <div class="text-2xl font-bold text-green-600" id="mobile-completed">0</div>
                    <div class="text-sm text-gray-600">Completed</div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="mobile-card p-4 mb-4">
                <div class="flex space-x-2 overflow-x-auto">
                    <button class="filter-tab mobile-button bg-blue-600 text-white px-4 py-2 text-sm whitespace-nowrap" data-filter="all">
                        All
                    </button>
                    <button class="filter-tab mobile-button bg-gray-200 text-gray-700 px-4 py-2 text-sm whitespace-nowrap" data-filter="pos">
                        POS
                    </button>
                    <button class="filter-tab mobile-button bg-gray-200 text-gray-700 px-4 py-2 text-sm whitespace-nowrap" data-filter="inventory">
                        Inventory
                    </button>
                    <button class="filter-tab mobile-button bg-gray-200 text-gray-700 px-4 py-2 text-sm whitespace-nowrap" data-filter="booking">
                        Booking
                    </button>
                </div>
            </div>

            <!-- Tutorial Modules -->
            <div id="mobile-tutorials-container">
                <!-- Tutorial modules will be loaded dynamically -->
            </div>

            <!-- Loading State -->
            <div id="mobile-loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-gray-600">Loading tutorials...</p>
            </div>

            <!-- Empty State -->
            <div id="mobile-empty" class="hidden text-center py-12">
                <i class="fas fa-mobile-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tutorials found</h3>
                <p class="text-gray-600">Try adjusting your filters or check back later.</p>
            </div>
        </main>

        <!-- Mobile Bottom Navigation -->
        <nav class="mobile-bottom-nav">
            <div class="flex">
                <button class="nav-item flex-1 flex flex-col items-center py-3 text-blue-600" data-page="tutorials">
                    <i class="fas fa-graduation-cap text-xl mb-1"></i>
                    <span class="text-xs font-medium">Tutorials</span>
                </button>
                <button class="nav-item flex-1 flex flex-col items-center py-3 text-gray-500" data-page="progress">
                    <i class="fas fa-chart-line text-xl mb-1"></i>
                    <span class="text-xs font-medium">Progress</span>
                </button>
                <button class="nav-item flex-1 flex flex-col items-center py-3 text-gray-500" data-page="assessments">
                    <i class="fas fa-clipboard-check text-xl mb-1"></i>
                    <span class="text-xs font-medium">Tests</span>
                </button>
                <button class="nav-item flex-1 flex flex-col items-center py-3 text-gray-500" data-page="profile">
                    <i class="fas fa-user text-xl mb-1"></i>
                    <span class="text-xs font-medium">Profile</span>
                </button>
            </div>
        </nav>
    </div>

    <!-- Mobile Tutorial Modal -->
    <div id="mobile-tutorial-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50" id="modal-backdrop"></div>
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-6 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modal-title" class="text-lg font-semibold text-gray-900"></h3>
                <button id="close-modal" class="p-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modal-content" class="text-gray-600">
                <!-- Modal content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed');
                    });
            });
        }
    </script>

    <script>
    $(document).ready(function() {
        // Mobile tutorial state
        let currentFilter = 'all';
        let tutorialModules = [];
        
        // Initialize mobile interface
        loadMobileTutorials();
        setupMobileInteractions();
        
        // Event handlers
        $('#back-button').click(function() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '../index.php';
            }
        });
        
        $('#search-button').click(function() {
            toggleSearch();
        });
        
        $('#menu-button').click(function() {
            showMobileMenu();
        });
        
        $('#search-input').on('input', function() {
            filterTutorials($(this).val());
        });
        
        $('.filter-tab').click(function() {
            const filter = $(this).data('filter');
            setActiveFilter(filter);
            filterTutorialsByType(filter);
        });
        
        $('.nav-item').click(function() {
            const page = $(this).data('page');
            navigateToPage(page);
        });
        
        $('#close-modal').click(function() {
            closeMobileModal();
        });
        
        $('#modal-backdrop').click(function() {
            closeMobileModal();
        });
        
        function loadMobileTutorials() {
            $.ajax({
                url: '../api/tutorials/get-modules.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        tutorialModules = response.modules;
                        displayMobileTutorials(tutorialModules);
                        updateMobileStats();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading mobile tutorials:', error);
                    showMobileError('Failed to load tutorials');
                }
            });
        }
        
        function displayMobileTutorials(modules) {
            const container = $('#mobile-tutorials-container');
            container.empty();
            
            if (modules.length === 0) {
                $('#mobile-empty').removeClass('hidden');
                return;
            }
            
            $('#mobile-empty').addClass('hidden');
            
            modules.forEach(function(module) {
                const moduleCard = createMobileModuleCard(module);
                container.append(moduleCard);
            });
        }
        
        function createMobileModuleCard(module) {
            const statusClass = getMobileStatusClass(module.status);
            const progressWidth = module.completion_percentage || 0;
            
            return `
                <div class="mobile-card touch-feedback" data-module-id="${module.id}">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3" 
                                     style="background-color: ${getModuleColor(module.module_type)}">
                                    <i class="${getModuleIcon(module.module_type)} text-white text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-base">${module.name}</h3>
                                    <p class="text-sm text-gray-600">${module.module_type.toUpperCase()} • ${module.difficulty_level}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                                ${module.status.replace('_', ' ').toUpperCase()}
                            </span>
                        </div>
                        
                        <p class="text-gray-600 text-sm mb-3">${module.description || 'No description available'}</p>
                        
                        <div class="mb-3">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Progress</span>
                                <span>${progressWidth.toFixed(1)}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                     style="width: ${progressWidth}%"></div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center text-sm text-gray-600 mb-3">
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                <span>${module.estimated_duration} min</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-star mr-1"></i>
                                <span>${module.score ? module.score.toFixed(1) : '0.0'}</span>
                            </div>
                        </div>
                        
                        <button class="w-full mobile-button bg-blue-600 text-white py-3 px-4 rounded-lg text-sm font-medium" 
                                onclick="startMobileTutorial(${module.id}, '${module.name}')">
                            ${module.status === 'not_started' ? 'Start Tutorial' : 
                              module.status === 'completed' ? 'Review Tutorial' : 
                              'Continue Tutorial'}
                        </button>
                    </div>
                </div>
            `;
        }
        
        function getMobileStatusClass(status) {
            switch (status) {
                case 'completed': return 'bg-green-100 text-green-800';
                case 'in_progress': return 'bg-blue-100 text-blue-800';
                case 'paused': return 'bg-yellow-100 text-yellow-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }
        
        function getModuleColor(moduleType) {
            switch (moduleType) {
                case 'pos': return '#10B981';
                case 'inventory': return '#3B82F6';
                case 'booking': return '#8B5CF6';
                default: return '#6B7280';
            }
        }
        
        function getModuleIcon(moduleType) {
            switch (moduleType) {
                case 'pos': return 'fas fa-cash-register';
                case 'inventory': return 'fas fa-boxes';
                case 'booking': return 'fas fa-calendar-check';
                default: return 'fas fa-book';
            }
        }
        
        function updateMobileStats() {
            const total = tutorialModules.length;
            const completed = tutorialModules.filter(m => m.status === 'completed').length;
            
            $('#mobile-total-modules').text(total);
            $('#mobile-completed').text(completed);
        }
        
        function toggleSearch() {
            const searchBar = $('#search-bar');
            const searchInput = $('#search-input');
            
            if (searchBar.hasClass('hidden')) {
                searchBar.removeClass('hidden');
                searchInput.focus();
            } else {
                searchBar.addClass('hidden');
                searchInput.blur();
            }
        }
        
        function filterTutorials(searchTerm) {
            const filtered = tutorialModules.filter(module => 
                module.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                module.description.toLowerCase().includes(searchTerm.toLowerCase())
            );
            displayMobileTutorials(filtered);
        }
        
        function setActiveFilter(filter) {
            $('.filter-tab').removeClass('bg-blue-600 text-white').addClass('bg-gray-200 text-gray-700');
            $(`.filter-tab[data-filter="${filter}"]`).removeClass('bg-gray-200 text-gray-700').addClass('bg-blue-600 text-white');
            currentFilter = filter;
        }
        
        function filterTutorialsByType(filter) {
            if (filter === 'all') {
                displayMobileTutorials(tutorialModules);
            } else {
                const filtered = tutorialModules.filter(module => module.module_type === filter);
                displayMobileTutorials(filtered);
            }
        }
        
        function navigateToPage(page) {
            $('.nav-item').removeClass('text-blue-600').addClass('text-gray-500');
            $(`.nav-item[data-page="${page}"]`).removeClass('text-gray-500').addClass('text-blue-600');
            
            switch (page) {
                case 'tutorials':
                    // Already on tutorials page
                    break;
                case 'progress':
                    window.location.href = 'analytics.php';
                    break;
                case 'assessments':
                    window.location.href = 'assessment.php';
                    break;
                case 'profile':
                    window.location.href = '../profile.php';
                    break;
            }
        }
        
        function showMobileMenu() {
            // Implement mobile menu functionality
            showMobileModal('Menu', `
                <div class="space-y-4">
                    <a href="../index.php" class="block p-3 bg-gray-100 rounded-lg text-gray-900">
                        <i class="fas fa-home mr-3"></i>Dashboard
                    </a>
                    <a href="analytics.php" class="block p-3 bg-gray-100 rounded-lg text-gray-900">
                        <i class="fas fa-chart-line mr-3"></i>Analytics
                    </a>
                    <a href="../profile.php" class="block p-3 bg-gray-100 rounded-lg text-gray-900">
                        <i class="fas fa-user mr-3"></i>Profile
                    </a>
                    <a href="logout.php" class="block p-3 bg-red-100 rounded-lg text-red-900">
                        <i class="fas fa-sign-out-alt mr-3"></i>Logout
                    </a>
                </div>
            `);
        }
        
        function showMobileModal(title, content) {
            $('#modal-title').text(title);
            $('#modal-content').html(content);
            $('#mobile-tutorial-modal').removeClass('hidden');
        }
        
        function closeMobileModal() {
            $('#mobile-tutorial-modal').addClass('hidden');
        }
        
        function showMobileError(message) {
            $('#mobile-loading').hide();
            $('#mobile-tutorials-container').html(`
                <div class="mobile-card p-6 text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Error</h3>
                    <p class="text-gray-600 mb-4">${message}</p>
                    <button onclick="location.reload()" class="mobile-button bg-blue-600 text-white px-6 py-2 rounded-lg">
                        Try Again
                    </button>
                </div>
            `);
        }
        
        function setupMobileInteractions() {
            // Touch gesture support
            let startX, startY, endX, endY;
            
            $('body').on('touchstart', function(e) {
                startX = e.originalEvent.touches[0].clientX;
                startY = e.originalEvent.touches[0].clientY;
            });
            
            $('body').on('touchend', function(e) {
                endX = e.originalEvent.changedTouches[0].clientX;
                endY = e.originalEvent.changedTouches[0].clientY;
                
                const diffX = endX - startX;
                const diffY = endY - startY;
                
                // Swipe detection
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                    if (diffX > 0) {
                        // Swipe right - go back
                        if (window.history.length > 1) {
                            window.history.back();
                        }
                    } else {
                        // Swipe left - could implement next/previous functionality
                    }
                }
            });
            
            // Pull to refresh
            let pullStartY = 0;
            let pullCurrentY = 0;
            let isPulling = false;
            
            $('body').on('touchstart', function(e) {
                if (window.scrollY === 0) {
                    pullStartY = e.originalEvent.touches[0].clientY;
                    isPulling = true;
                }
            });
            
            $('body').on('touchmove', function(e) {
                if (isPulling) {
                    pullCurrentY = e.originalEvent.touches[0].clientY;
                    const pullDistance = pullCurrentY - pullStartY;
                    
                    if (pullDistance > 0 && pullDistance < 100) {
                        // Show pull to refresh indicator
                        e.preventDefault();
                    }
                }
            });
            
            $('body').on('touchend', function(e) {
                if (isPulling) {
                    const pullDistance = pullCurrentY - pullStartY;
                    
                    if (pullDistance > 80) {
                        // Trigger refresh
                        loadMobileTutorials();
                    }
                    
                    isPulling = false;
                }
            });
        }
        
        // Global functions for module cards
        window.startMobileTutorial = function(moduleId, moduleName) {
            window.location.href = `index.php?module_id=${moduleId}`;
        };
    });
    </script>
</body>
</html>
