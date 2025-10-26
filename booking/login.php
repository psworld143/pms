<?php
// VPS Session Fix - Robust session configuration
//Test testet tetstststs
require_once '../vps_session_fix.php';

require_once '../includes/database.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, name, username, password, role FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            logActivity($user['id'], 'login', 'User logged in successfully');
            
            header('Location: ' . booking_dashboard_url($user['role']));
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $error = 'System error. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking System Login - Hotel PMS Training</title>
    <meta name="description" content="Access the Hotel Booking & Reservations Management System - Professional Training Platform">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2',
                        accent: '#f093fb',
                        success: '#4ade80',
                        warning: '#fbbf24',
                        error: '#f87171'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-in-out',
                        'slide-up': 'slideUp 0.8s ease-out',
                        'bounce-in': 'bounceIn 0.8s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                        'float': 'float 3s ease-in-out infinite'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        bounceIn: {
                            '0%': { transform: 'scale(0.3)', opacity: '0' },
                            '50%': { transform: 'scale(1.05)' },
                            '70%': { transform: 'scale(0.9)' },
                            '100%': { transform: 'scale(1)', opacity: '1' }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .input-focus:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="min-h-screen bg-white relative overflow-hidden" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-primary/10 to-secondary/10 rounded-full blur-3xl animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-tr from-accent/10 to-primary/10 rounded-full blur-3xl animate-float" style="animation-delay: 1.5s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-r from-secondary/5 to-accent/5 rounded-full blur-3xl animate-pulse-slow"></div>
    </div>

    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md animate-fade-in">
            <!-- Main Login Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 animate-slide-up card-hover transition-all duration-500 border border-gray-100">
                <!-- Header Section -->
                <div class="text-center mb-8 animate-bounce-in">
                    <div class="relative">
                        <div class="w-20 h-20 bg-gradient-to-r from-primary via-secondary to-accent rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg animate-pulse-slow">
                            <i class="fas fa-hotel text-white text-3xl"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-success rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                    </div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                        Booking & Reservations
                    </h1>
                    <p class="text-gray-600 mt-2 text-sm font-medium">Complete Hotel Management System for Students</p>
                    <div class="flex items-center justify-center mt-3 space-x-2">
                        <div class="w-2 h-2 bg-success rounded-full animate-pulse"></div>
                        <span class="text-success text-xs font-medium">System Online</span>
                    </div>
                </div>
                
                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center animate-bounce-in">
                    <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
                    <span class="text-sm font-medium"><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center animate-bounce-in">
                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                    <span class="text-sm font-medium">You have been successfully logged out.</span>
                </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form method="POST" class="space-y-6 animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="space-y-2">
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-user mr-2 text-primary"></i>Username
                        </label>
                        <div class="relative">
                            <input type="text" id="username" name="username" required 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   class="w-full px-4 py-4 bg-gray-50 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300 text-gray-900 placeholder-gray-500 input-focus"
                                   placeholder="Enter your username">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-lock mr-2 text-primary"></i>Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                   class="w-full px-4 py-4 bg-gray-50 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300 text-gray-900 placeholder-gray-500 input-focus"
                                   placeholder="Enter your password">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-white border-2 border-green-500 text-green-600 py-4 px-6 rounded-xl font-semibold hover:bg-green-50 hover:border-green-600 transition-all duration-300 shadow-lg">
                        <span class="flex items-center justify-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                        </span>
                    </button>
                </form>
            </div>
            
            <!-- Floating Credentials Icon -->
            <button onclick="toggleCredentialsModal()" class="fixed bottom-6 right-6 w-14 h-14 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center hover:scale-110 animate-bounce-in z-50" title="View Demo Credentials">
                <i class="fas fa-key text-xl"></i>
            </button>
            
            <!-- Credentials Modal -->
            <div id="credentialsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 animate-fade-in">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 animate-slide-up">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent flex items-center">
                            <i class="fas fa-key mr-2 text-blue-600"></i>
                            Demo Credentials
                        </h3>
                        <button onclick="toggleCredentialsModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200">
                        <p class="text-blue-800 font-medium mb-3 text-sm">Use these credentials for testing:</p>
                        <div class="space-y-2 text-sm">
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <strong class="text-blue-700">Manager:</strong>
                                <span class="text-gray-700 ml-2">manager1 / password</span>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <strong class="text-blue-700">Front Desk:</strong>
                                <span class="text-gray-700 ml-2">frontdesk1 / password</span>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <strong class="text-blue-700">Housekeeping:</strong>
                                <span class="text-gray-700 ml-2">housekeeping1 / password</span>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-blue-100 rounded-lg">
                            <p class="text-blue-700 text-xs font-medium"><i class="fas fa-info-circle mr-1"></i>All roles have full access to booking system</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-6 animate-fade-in" style="animation-delay: 0.6s;">
                <p class="text-gray-500 text-sm mb-2">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Secure login system with encrypted authentication
                </p>
                <a href="../" class="text-primary hover:text-primary-dark transition-colors text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back to PMS System
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle credentials modal
        function toggleCredentialsModal() {
            const modal = document.getElementById('credentialsModal');
            modal.classList.toggle('hidden');
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('credentialsModal');
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.getElementById('credentialsModal').classList.add('hidden');
            }
        });
        
        // Add interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation feedback
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.outline = 'none';
                    this.style.borderColor = '#667eea';
                });
                
                input.addEventListener('blur', function() {
                    this.style.borderColor = '#d1d5db';
                });
            });
        });
    </script>
</body>
</html>
