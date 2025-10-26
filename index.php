<?php
/**
 * Hotel PMS Training System - Main Entry Point
 * Enhanced Student-Friendly Interface
 * Redirects to the appropriate module based on user session or shows module selection
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in, redirect to their appropriate module
    $user_role = $_SESSION['user_role'] ?? 'front_desk';
    
    switch ($user_role) {
        case 'manager':
            header('Location: booking/modules/manager/');
            break;
        case 'housekeeping':
            header('Location: booking/modules/housekeeping/');
            break;
        case 'front_desk':
        default:
            header('Location: booking/');
            break;
    }
    exit();
}

// User is not logged in, show module selection page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel PMS Training System - Learn Professional Hotel Management</title>
    <meta name="description" content="Comprehensive Hotel Property Management System for Students - Learn booking, POS, inventory management, and more">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            z-index: 0;
            animation: pulse 15s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            text-align: center;
            padding: 60px 20px 40px;
            animation: fadeInDown 1s ease-out;
        }

        .logo-wrapper {
            display: inline-block;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .main-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #2d3748;
            margin: 0;
            line-height: 1.2;
        }

        .main-title i {
            color: #667eea;
            margin-right: 15px;
        }

        .subtitle {
            font-size: 1.4rem;
            color: white;
            margin-top: 20px;
            font-weight: 500;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .student-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
        }

        /* Module Cards Grid */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin: 40px 0;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .module-card {
            background: white;
            border-radius: 24px;
            padding: 40px 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--color-1), var(--color-2));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .module-card:hover::before {
            transform: scaleX(1);
        }

        .module-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        }

        .module-card.booking { --color-1: #667eea; --color-2: #764ba2; }
        .module-card.pos { --color-1: #11998e; --color-2: #38ef7d; }
        .module-card.inventory { --color-1: #7f00ff; --color-2: #e100ff; }
        .module-card.tutorials { --color-1: #fa709a; --color-2: #fee140; }

        .module-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            font-size: 2.5rem;
            color: white;
            background: linear-gradient(135deg, var(--color-1), var(--color-2));
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .module-card:hover .module-icon {
            transform: rotateY(360deg);
        }

        .module-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 12px;
            text-align: center;
        }

        .module-description {
            color: #4a5568;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 20px;
            text-align: center;
        }

        .features-list {
            list-style: none;
            margin: 20px 0;
            padding: 0;
        }

        .features-list li {
            padding: 8px 0;
            color: #718096;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .features-list li i {
            color: var(--color-1);
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .module-stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            padding: 15px 0;
            border-top: 2px solid #f7fafc;
            border-bottom: 2px solid #f7fafc;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-1);
        }

        .stat-label {
            font-size: 0.75rem;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, var(--color-1), var(--color-2));
            color: white;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
        }

        .btn-primary i {
            margin-right: 8px;
        }

        /* Info Section */
        .info-section {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: 50px 40px;
            margin: 60px 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .info-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
            color: #2d3748;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .feature-item {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, #f6f8fb 0%, #ffffff 100%);
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .feature-item:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
        }

        .feature-item i {
            font-size: 3rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-item h4 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .feature-item p {
            color: #718096;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Quick Start Guide */
        .quick-start {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 24px;
            padding: 50px 40px;
            margin: 40px 0;
            animation: fadeInUp 1s ease-out 0.9s both;
        }

        .quick-start h2 {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .step-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .step-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: white;
            color: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 15px;
        }

        .step-item h4 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .step-item p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 40px 20px;
            color: white;
            margin-top: 60px;
        }

        .footer p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .footer-links {
            margin: 20px 0;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .footer-links a:hover {
            opacity: 1;
            text-decoration: underline;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-title {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1.1rem;
            }

            .modules-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .module-card {
                padding: 30px 20px;
            }

            .info-section, .quick-start {
                padding: 30px 20px;
            }

            .info-section h2, .quick-start h2 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .main-title {
                font-size: 1.5rem;
            }

            .logo-wrapper {
                padding: 15px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo-wrapper">
                <h1 class="main-title">
                    <i class="fas fa-hotel"></i>Hotel PMS Training System
                </h1>
            </div>
            <p class="subtitle">
                Master Professional Hotel Management - Built for Hospitality Students
            </p>
            <div class="student-badge">
                <i class="fas fa-graduation-cap"></i> Student Learning Platform
            </div>
        </header>

        <!-- Modules Grid -->
        <div class="modules-grid">
            <!-- Booking System Module -->
            <div class="module-card booking">
                <div class="module-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="module-title">Booking & Reservations</h3>
                <p class="module-description">
                    Complete front desk operations system for managing guest reservations, check-ins, check-outs, and room assignments.
                </p>
                
                <div class="module-stats">
                    <div class="stat">
                        <div class="stat-value">15+</div>
                        <div class="stat-label">Features</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">3</div>
                        <div class="stat-label">Roles</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">Live</div>
                        <div class="stat-label">Reports</div>
                    </div>
                </div>

                <ul class="features-list">
                    <li><i class="fas fa-check-circle"></i> Guest Check-in & Check-out</li>
                    <li><i class="fas fa-check-circle"></i> Room Management & Assignment</li>
                    <li><i class="fas fa-check-circle"></i> Housekeeping Task Management</li>
                    <li><i class="fas fa-check-circle"></i> Guest Services & Requests</li>
                    <li><i class="fas fa-check-circle"></i> Loyalty Program Management</li>
                    <li><i class="fas fa-check-circle"></i> Real-time Dashboard & Analytics</li>
                </ul>

                <a href="booking/login.php" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Access Booking System
                </a>
            </div>

            <!-- POS System Module -->
            <div class="module-card pos">
                <div class="module-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <h3 class="module-title">Point of Sale (POS)</h3>
                <p class="module-description">
                    Comprehensive POS system for hotel revenue centers including restaurant, spa, gift shop, and room service operations.
                </p>
                
                <div class="module-stats">
                    <div class="stat">
                        <div class="stat-value">4</div>
                        <div class="stat-label">Venues</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">100+</div>
                        <div class="stat-label">Items</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">Live</div>
                        <div class="stat-label">Sales</div>
                    </div>
                </div>

                <ul class="features-list">
                    <li><i class="fas fa-check-circle"></i> Restaurant Order Management</li>
                    <li><i class="fas fa-check-circle"></i> Spa Services & Bookings</li>
                    <li><i class="fas fa-check-circle"></i> Gift Shop Retail Sales</li>
                    <li><i class="fas fa-check-circle"></i> Room Service Orders</li>
                    <li><i class="fas fa-check-circle"></i> Quick Sales Processing</li>
                    <li><i class="fas fa-check-circle"></i> Sales Reports & Analytics</li>
                </ul>

                <a href="pos/login.php" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Access POS System
                </a>
            </div>

            <!-- Inventory Management Module -->
            <div class="module-card inventory">
                <div class="module-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3 class="module-title">Inventory Management</h3>
                <p class="module-description">
                    Professional inventory control system for tracking stock levels, managing suppliers, and handling procurement processes.
                </p>
                
                <div class="module-stats">
                    <div class="stat">
                        <div class="stat-value">200+</div>
                        <div class="stat-label">Items</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">Auto</div>
                        <div class="stat-label">Alerts</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">Full</div>
                        <div class="stat-label">Reports</div>
                    </div>
                </div>

                <ul class="features-list">
                    <li><i class="fas fa-check-circle"></i> Stock Level Monitoring</li>
                    <li><i class="fas fa-check-circle"></i> Purchase Order Management</li>
                    <li><i class="fas fa-check-circle"></i> Supplier Management</li>
                    <li><i class="fas fa-check-circle"></i> Inventory Transactions</li>
                    <li><i class="fas fa-check-circle"></i> Low Stock Alerts</li>
                    <li><i class="fas fa-check-circle"></i> Comprehensive Reports</li>
                </ul>

                <a href="inventory/login.php" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Access Inventory System
                </a>
            </div>

            <!-- Tutorial System Module -->
            <div class="module-card tutorials">
                <div class="module-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="module-title">Interactive Training</h3>
                <p class="module-description">
                    Guided learning system with interactive tutorials, real-world scenarios, assessments, and certification for hospitality students.
                </p>
                
                <div class="module-stats">
                    <div class="stat">
                        <div class="stat-value">20+</div>
                        <div class="stat-label">Tutorials</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">50+</div>
                        <div class="stat-label">Scenarios</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">Cert</div>
                        <div class="stat-label">Included</div>
                    </div>
                </div>

                <ul class="features-list">
                    <li><i class="fas fa-check-circle"></i> Step-by-Step Tutorials</li>
                    <li><i class="fas fa-check-circle"></i> Interactive Scenarios</li>
                    <li><i class="fas fa-check-circle"></i> Progress Tracking</li>
                    <li><i class="fas fa-check-circle"></i> Knowledge Assessments</li>
                    <li><i class="fas fa-check-circle"></i> Achievement Badges</li>
                    <li><i class="fas fa-check-circle"></i> Training Certificates</li>
                </ul>

                <a href="tutorials/index.php" class="btn-primary">
                    <i class="fas fa-play"></i> Start Learning Now
                </a>
            </div>
        </div>

        <!-- Quick Start Guide -->
        <section class="quick-start">
            <h2><i class="fas fa-rocket"></i> Quick Start Guide for Students</h2>
            <div class="steps-grid">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <h4>Choose Your Module</h4>
                    <p>Select from Booking, POS, Inventory, or Training based on your learning goals</p>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <h4>Login with Credentials</h4>
                    <p>Use provided student credentials to access the system (manager1 / password)</p>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <h4>Explore Features</h4>
                    <p>Navigate through the dashboard and explore all available features</p>
                </div>
                <div class="step-item">
                    <div class="step-number">4</div>
                    <h4>Practice Scenarios</h4>
                    <p>Complete real-world scenarios to master hotel operations</p>
                </div>
                <div class="step-item">
                    <div class="step-number">5</div>
                    <h4>Track Progress</h4>
                    <p>Monitor your learning progress and complete assessments</p>
                </div>
                <div class="step-item">
                    <div class="step-number">6</div>
                    <h4>Earn Certificate</h4>
                    <p>Complete all modules and receive your training certificate</p>
                </div>
            </div>
        </section>

        <!-- Features & Benefits -->
        <section class="info-section">
            <h2><i class="fas fa-star"></i> Why Choose Our Training System?</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <i class="fas fa-laptop-code"></i>
                    <h4>Real-World Experience</h4>
                    <p>Practice with actual hotel management software used in the industry</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-users"></i>
                    <h4>Multi-Role Learning</h4>
                    <p>Experience different roles: Manager, Front Desk, and Housekeeping</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-line"></i>
                    <h4>Live Analytics</h4>
                    <p>Real-time dashboards and reports for data-driven decision making</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-mobile-alt"></i>
                    <h4>Mobile Responsive</h4>
                    <p>Access from any device - desktop, tablet, or smartphone</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Safe Learning Environment</h4>
                    <p>Practice without consequences in a controlled training environment</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-clock"></i>
                    <h4>Learn at Your Pace</h4>
                    <p>24/7 access allows you to learn whenever and wherever you want</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-certificate"></i>
                    <h4>Earn Certificates</h4>
                    <p>Receive official certificates upon completing training modules</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-database"></i>
                    <h4>Comprehensive Data</h4>
                    <p>Work with realistic hotel data and scenarios</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-links">
                <a href="booking/login.php"><i class="fas fa-calendar"></i> Booking</a>
                <a href="pos/login.php"><i class="fas fa-cash-register"></i> POS</a>
                <a href="inventory/login.php"><i class="fas fa-boxes"></i> Inventory</a>
                <a href="tutorials/index.php"><i class="fas fa-graduation-cap"></i> Training</a>
            </div>
            <p>
                <i class="fas fa-university"></i> 
                &copy; <?php echo date('Y'); ?> Hotel PMS Training System - Professional Hospitality Education Platform
            </p>
            <p style="margin-top: 10px; font-size: 0.9rem; opacity: 0.8;">
                <i class="fas fa-code"></i> Built for Students | Learn Hotel Management the Modern Way
            </p>
        </footer>
    </div>

    <script>
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Add entrance animations on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.module-card, .feature-item, .step-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });
    </script>
</body>
</html>
