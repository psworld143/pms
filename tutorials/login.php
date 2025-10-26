<?php
// VPS Session Fix
require_once '../vps_session_fix.php';
require_once '../includes/database.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND is_active = 1");
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
    <title>Training System Login - Hotel PMS Education</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .bg-pattern {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.15) 0%, transparent 50%);
            animation: pulse 15s ease-in-out infinite;
        }
        @keyframes pulse { 0%, 100% { opacity: 0.5; } 50% { opacity: 1; } }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .login-container { position: relative; z-index: 1; width: 100%; max-width: 500px; animation: fadeInUp 0.6s ease-out; }
        .login-card { background: rgba(255, 255, 255, 0.98); border-radius: 32px; padding: 50px 45px; box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3); }
        .logo-icon {
            width: 100px; height: 100px; margin: 0 auto 25px;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            border-radius: 24px; display: flex; align-items: center; justify-content: center;
            font-size: 3rem; color: white; box-shadow: 0 15px 40px rgba(250, 112, 154, 0.4);
            animation: float 6s ease-in-out infinite;
        }
        .system-title {
            font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; text-align: center;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .system-subtitle { text-align: center; font-size: 1rem; color: #718096; font-weight: 500; margin-bottom: 15px; }
        .status-badge {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 20px;
            background: rgba(16, 185, 129, 0.1); border-radius: 25px;
            font-size: 0.85rem; color: #10b981; font-weight: 600;
            margin: 0 auto; display: flex; justify-content: center; width: fit-content;
        }
        .status-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: pulse-dot 2s infinite; }
        @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .alert { padding: 18px; border-radius: 16px; margin: 30px 0 25px; display: flex; align-items: center; gap: 12px; font-size: 0.95rem; font-weight: 500; }
        .alert-error { background: rgba(239, 68, 68, 0.1); border: 2px solid rgba(239, 68, 68, 0.3); color: #dc2626; }
        .form-group { margin-bottom: 25px; }
        .form-label { display: block; font-size: 0.95rem; font-weight: 600; color: #2d3748; margin-bottom: 10px; }
        .form-label i { color: #fa709a; margin-right: 8px; }
        .form-input {
            width: 100%; padding: 16px 20px; border: 2px solid #e2e8f0; border-radius: 14px;
            font-size: 1.05rem; font-family: 'Inter', sans-serif; background: #f7fafc; transition: all 0.3s ease;
        }
        .form-input:focus { outline: none; border-color: #fa709a; background: white; box-shadow: 0 0 0 5px rgba(250, 112, 154, 0.1); }
        .btn-primary {
            width: 100%; padding: 18px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white; border: none; border-radius: 14px; font-size: 1.1rem; font-weight: 700;
            cursor: pointer; box-shadow: 0 8px 25px rgba(250, 112, 154, 0.4);
            display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s ease;
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 12px 35px rgba(250, 112, 154, 0.5); }
        .footer-links { text-align: center; margin-top: 30px; padding-top: 25px; border-top: 2px solid #e2e8f0; }
        .footer-links a {
            color: #fa709a; text-decoration: none; font-size: 1rem; font-weight: 600;
            transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px;
        }
        .footer-links a:hover { color: #fee140; gap: 12px; }
        .credentials-btn {
            position: fixed; bottom: 35px; right: 35px; width: 65px; height: 65px;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            border: none; border-radius: 50%; color: white; font-size: 1.6rem;
            cursor: pointer; box-shadow: 0 10px 30px rgba(250, 112, 154, 0.5);
            z-index: 1000; animation: bounce 3s infinite; transition: all 0.3s ease;
        }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        .credentials-btn:hover { transform: scale(1.15); }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 2000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 24px; padding: 35px; max-width: 500px; width: 90%; box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4); animation: slideUp 0.4s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; }
        .modal-title { font-size: 1.6rem; font-weight: 800; color: #2d3748; display: flex; align-items: center; gap: 12px; }
        .modal-close { width: 40px; height: 40px; border: none; background: #f7fafc; border-radius: 10px; color: #718096; font-size: 1.3rem; cursor: pointer; transition: all 0.3s ease; }
        .modal-close:hover { background: #e2e8f0; color: #2d3748; transform: rotate(90deg); }
        .credential-item { background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 14px; padding: 18px; margin-bottom: 14px; border: 2px solid #e2e8f0; transition: all 0.3s ease; }
        .credential-item:hover { border-color: #fa709a; transform: translateX(5px); }
        .credential-role { font-weight: 700; color: #fa709a; margin-bottom: 8px; font-size: 1rem; }
        .credential-value { font-family: 'Monaco', monospace; color: #2d3748; font-size: 0.95rem; background: white; padding: 8px 12px; border-radius: 8px; }
        @media (max-width: 640px) {
            .login-card { padding: 35px 30px; }
            .system-title { font-size: 1.8rem; }
            .logo-icon { width: 80px; height: 80px; font-size: 2.4rem; }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
                <h1 class="system-title">Interactive Training</h1>
                <p class="system-subtitle">Step-by-Step Hotel Management Tutorials</p>
                <div class="status-badge"><div class="status-dot"></div> Training Portal Active</div>
            </div>
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle" style="font-size: 1.3rem;"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="user@hotel.com" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Training
                </button>
            </form>
            <div class="footer-links">
                <a href="../"><i class="fas fa-arrow-left"></i> Back to PMS Dashboard</a>
            </div>
        </div>
    </div>
    <button class="credentials-btn" onclick="toggleModal()" title="View Demo Credentials"><i class="fas fa-key"></i></button>
    <div class="modal" id="credentialsModal" onclick="closeModalOnOutside(event)">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-key" style="color: #fa709a;"></i> Demo Credentials</h3>
                <button class="modal-close" onclick="toggleModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="credential-item">
                <div class="credential-role"><i class="fas fa-user-tie"></i> Manager</div>
                <div class="credential-value">david@hotel.com / password</div>
            </div>
            <div class="credential-item">
                <div class="credential-role"><i class="fas fa-user"></i> Front Desk</div>
                <div class="credential-value">john@hotel.com / password</div>
            </div>
            <div class="credential-item">
                <div class="credential-role"><i class="fas fa-graduation-cap"></i> Student</div>
                <div class="credential-value">demo@student.com / demo123</div>
            </div>
            <div class="alert alert-error" style="margin-top: 20px; margin-bottom: 0; background: rgba(250, 112, 154, 0.1); border-color: rgba(250, 112, 154, 0.3); color: #fa709a;">
                <i class="fas fa-graduation-cap" style="font-size: 1.3rem;"></i>
                <span><strong>Learning Mode:</strong> All users welcome!</span>
            </div>
        </div>
    </div>
    <script>
        function toggleModal() { document.getElementById('credentialsModal').classList.toggle('active'); }
        function closeModalOnOutside(event) { if (event.target.id === 'credentialsModal') toggleModal(); }
        document.addEventListener('keydown', function(event) { if (event.key === 'Escape') document.getElementById('credentialsModal').classList.remove('active'); });
    </script>
</body>
</html>
