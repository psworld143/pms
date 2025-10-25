<?php
/**
 * Server Status & Deployment Verification
 * Use this to verify your server is properly configured
 * Visit: https://pms.seait.edu.ph/server_status.php
 */

$checks = [];

// 1. Check PHP version
$phpVersion = phpversion();
$checks['PHP Version'] = [
    'value' => $phpVersion,
    'status' => version_compare($phpVersion, '7.4.0', '>=') ? 'success' : 'error',
    'message' => version_compare($phpVersion, '7.4.0', '>=') ? 'PHP version is compatible' : 'PHP 7.4+ required'
];

// 2. Check PDO MySQL
$checks['PDO MySQL'] = [
    'value' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not Installed',
    'status' => extension_loaded('pdo_mysql') ? 'success' : 'error',
    'message' => extension_loaded('pdo_mysql') ? 'PDO MySQL extension is available' : 'PDO MySQL extension is required'
];

// 3. Check required extensions
$required_extensions = ['mysqli', 'mbstring', 'json', 'session'];
$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}
$checks['PHP Extensions'] = [
    'value' => count($missing_extensions) === 0 ? 'All Required' : implode(', ', $missing_extensions) . ' missing',
    'status' => count($missing_extensions) === 0 ? 'success' : 'warning',
    'message' => count($missing_extensions) === 0 ? 'All required extensions are loaded' : 'Missing: ' . implode(', ', $missing_extensions)
];

// 4. Check if running on HTTPS
$checks['HTTPS'] = [
    'value' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Enabled' : 'Disabled',
    'status' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'success' : 'warning',
    'message' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Site is secure' : 'Consider enabling HTTPS'
];

// 5. Check file permissions
$writableDirs = ['tmp', 'tmp_sessions', 'booking/tmp', 'inventory/tmp'];
$permissionIssues = [];
foreach ($writableDirs as $dir) {
    if (file_exists($dir)) {
        if (!is_writable($dir)) {
            $permissionIssues[] = $dir;
        }
    }
}
$checks['Directory Permissions'] = [
    'value' => count($permissionIssues) === 0 ? 'OK' : count($permissionIssues) . ' directories not writable',
    'status' => count($permissionIssues) === 0 ? 'success' : 'warning',
    'message' => count($permissionIssues) === 0 ? 'All temp directories are writable' : 'Not writable: ' . implode(', ', $permissionIssues)
];

// 6. Check if database config exists
$configExists = file_exists(__DIR__ . '/includes/database.local.php');
$checks['Database Config'] = [
    'value' => $configExists ? 'Found' : 'Not Found',
    'status' => $configExists ? 'success' : 'error',
    'message' => $configExists ? 'Configuration file exists' : 'Create includes/database.local.php'
];

// 7. Check key files
$keyFiles = ['index.php', 'booking/index.php', 'inventory/index.php', 'pos/index.php'];
$missingFiles = [];
foreach ($keyFiles as $file) {
    if (!file_exists($file)) {
        $missingFiles[] = $file;
    }
}
$checks['Key Files'] = [
    'value' => count($missingFiles) === 0 ? 'All Present' : count($missingFiles) . ' missing',
    'status' => count($missingFiles) === 0 ? 'success' : 'error',
    'message' => count($missingFiles) === 0 ? 'All module files found' : 'Missing: ' . implode(', ', $missingFiles)
];

// 8. Server Info
$checks['Server Software'] = [
    'value' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'status' => 'info',
    'message' => 'Web server information'
];

$checks['Document Root'] = [
    'value' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'status' => 'info',
    'message' => 'Server document root directory'
];

$checks['Current Directory'] = [
    'value' => __DIR__,
    'status' => 'info',
    'message' => 'Current script directory'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Status - PMS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header p { opacity: 0.9; font-size: 14px; }
        .content { padding: 30px; }
        .check-item { padding: 20px; margin-bottom: 15px; border-radius: 8px; border-left: 4px solid #ddd; background: #f9fafb; }
        .check-item.success { border-left-color: #10b981; background: #f0fdf4; }
        .check-item.error { border-left-color: #ef4444; background: #fef2f2; }
        .check-item.warning { border-left-color: #f59e0b; background: #fffbeb; }
        .check-item.info { border-left-color: #3b82f6; background: #eff6ff; }
        .check-name { font-weight: 600; font-size: 16px; margin-bottom: 5px; color: #1f2937; }
        .check-value { font-family: 'Courier New', monospace; color: #6b7280; margin-bottom: 5px; font-size: 14px; }
        .check-message { color: #6b7280; font-size: 14px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; margin-left: 10px; }
        .status-badge.success { background: #d1fae5; color: #065f46; }
        .status-badge.error { background: #fee2e2; color: #991b1b; }
        .status-badge.warning { background: #fef3c7; color: #92400e; }
        .status-badge.info { background: #dbeafe; color: #1e40af; }
        .actions { margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb; }
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; margin-right: 10px; margin-bottom: 10px; font-weight: 600; transition: all 0.3s; }
        .btn:hover { background: #5568d3; transform: translateY(-2px); }
        .btn-secondary { background: #6b7280; }
        .btn-secondary:hover { background: #4b5563; }
        .summary { background: #f3f4f6; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .summary-item { display: inline-block; margin-right: 30px; margin-bottom: 10px; }
        .summary-count { font-size: 32px; font-weight: 700; }
        .summary-label { font-size: 12px; color: #6b7280; text-transform: uppercase; }
        .icon { width: 20px; height: 20px; display: inline-block; margin-right: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Server Status Check</h1>
            <p>PMS Deployment Verification - <?php echo $_SERVER['HTTP_HOST'] ?? 'Unknown Host'; ?></p>
        </div>
        
        <div class="content">
            <?php
            // Calculate summary
            $total = count($checks);
            $success = 0;
            $errors = 0;
            $warnings = 0;
            foreach ($checks as $check) {
                if ($check['status'] === 'success') $success++;
                if ($check['status'] === 'error') $errors++;
                if ($check['status'] === 'warning') $warnings++;
            }
            ?>
            
            <div class="summary">
                <div class="summary-item">
                    <div class="summary-count" style="color: #10b981;">✓ <?php echo $success; ?></div>
                    <div class="summary-label">Passed</div>
                </div>
                <div class="summary-item">
                    <div class="summary-count" style="color: #ef4444;">✗ <?php echo $errors; ?></div>
                    <div class="summary-label">Errors</div>
                </div>
                <div class="summary-item">
                    <div class="summary-count" style="color: #f59e0b;">⚠ <?php echo $warnings; ?></div>
                    <div class="summary-label">Warnings</div>
                </div>
            </div>

            <?php foreach ($checks as $name => $check): ?>
                <div class="check-item <?php echo $check['status']; ?>">
                    <div class="check-name">
                        <?php echo htmlspecialchars($name); ?>
                        <span class="status-badge <?php echo $check['status']; ?>">
                            <?php echo strtoupper($check['status']); ?>
                        </span>
                    </div>
                    <div class="check-value"><?php echo htmlspecialchars($check['value']); ?></div>
                    <div class="check-message"><?php echo htmlspecialchars($check['message']); ?></div>
                </div>
            <?php endforeach; ?>

            <div class="actions">
                <h3 style="margin-bottom: 15px; color: #1f2937;">Next Steps:</h3>
                
                <?php if ($errors === 0 && $warnings === 0): ?>
                    <p style="color: #10b981; font-weight: 600; margin-bottom: 20px;">
                        ✅ All checks passed! Your server is ready.
                    </p>
                    <a href="/" class="btn">Go to Home Page</a>
                    <a href="/test_db_connection.php" class="btn btn-secondary">Test Database</a>
                    <a href="/booking/" class="btn btn-secondary">Open Booking System</a>
                <?php else: ?>
                    <p style="color: #dc2626; font-weight: 600; margin-bottom: 20px;">
                        ⚠️ Please fix the issues above before proceeding.
                    </p>
                    <a href="DEPLOYMENT_GUIDE.md" class="btn">View Deployment Guide</a>
                    <a href="ONLINE_DATABASE_SETUP.md" class="btn btn-secondary">Database Setup Guide</a>
                <?php endif; ?>
                
                <p style="margin-top: 20px; color: #6b7280; font-size: 14px;">
                    <strong>Tip:</strong> After fixing all issues, delete this file (server_status.php) for security.
                </p>
            </div>
        </div>
    </div>
</body>
</html>

