<?php
/**
 * Tutorial System Login
 * Student Authentication for Hotel PMS Training
 */

session_start();
require_once '../includes/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student') {
    header('Location: index.php');
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND role = 'student' AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Hotel PMS Training</title>
    <link rel="icon" type="image/png" href="../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
<body class="min-h-screen bg-white relative overflow-hidden">
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
                            <i class="fas fa-graduation-cap text-white text-3xl"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-success rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                    </div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                        Training System
                    </h1>
                    <p class="text-gray-600 mt-2 text-sm font-medium">Interactive Hotel PMS Learning</p>
                    <div class="flex items-center justify-center mt-3 space-x-2">
                        <div class="w-2 h-2 bg-success rounded-full animate-pulse"></div>
                        <span class="text-xs text-gray-500 font-medium">Student Portal Active</span>
                    </div>
                </div>

                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 animate-slide-up">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span class="text-sm font-medium"><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" class="space-y-6 animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">
                                <i class="fas fa-envelope mr-2 text-primary"></i>Email Address
                            </label>
                            <input type="email" name="email" required 
                                   class="input-focus w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary transition-all duration-300 bg-gray-50 focus:bg-white"
                                   placeholder="student@example.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">
                                <i class="fas fa-lock mr-2 text-primary"></i>Password
                            </label>
                            <input type="password" name="password" required 
                                   class="input-focus w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary transition-all duration-300 bg-gray-50 focus:bg-white"
                                   placeholder="Enter your password">
                        </div>
                    </div>

                    <button type="submit" 
                            class="btn-hover w-full bg-gradient-to-r from-primary to-secondary text-white py-3 px-6 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login to Training
                    </button>
                </form>

                <!-- Demo Account Info -->
                <div class="mt-8 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200 animate-slide-up" style="animation-delay: 0.4s;">
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                            <i class="fas fa-info-circle text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-blue-800 font-semibold text-sm mb-1">Demo Account Available</h3>
                            <p class="text-blue-600 text-xs mb-2">For testing purposes:</p>
                            <div class="text-blue-700 text-xs space-y-1">
                                <p><strong>Email:</strong> demo@student.com</p>
                                <p><strong>Password:</strong> demo123</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back to PMS -->
                <div class="mt-6 text-center animate-slide-up" style="animation-delay: 0.6s;">
                    <a href="../index.php" class="text-gray-500 hover:text-primary transition-colors text-sm font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Back to PMS Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus first input
        document.querySelector('input[type="email"]').focus();
        
        // Add loading state to form submission
        document.querySelector('form').addEventListener('submit', function() {
            const button = document.querySelector('button[type="submit"]');
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Logging in...';
            button.disabled = true;
        });
    </script>
</body>
</html>
