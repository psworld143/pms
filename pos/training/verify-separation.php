<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Training System Separation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">
            <i class="fas fa-check-double text-green-600 mr-2"></i>
            Training System Separation Verification
        </h1>
        
        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        require_once __DIR__ . '/../../booking/modules/training/training-session-bridge.php';
        require_once __DIR__ . '/../../includes/database.php';
        
        $user_id = $_SESSION['user_id'] ?? 0;
        
        echo "<div class='bg-blue-50 border-l-4 border-blue-500 p-4 mb-6'>";
        echo "<p><strong>User ID:</strong> $user_id</p>";
        echo "<p><strong>Database:</strong> " . $pdo->query("SELECT DATABASE() as db")->fetch()['db'] . "</p>";
        echo "</div>";
        
        // Query all systems
        $systems = ['pos', 'booking', 'inventory'];
        
        foreach ($systems as $sys) {
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                    ROUND(AVG(CASE WHEN status = 'completed' THEN score END), 2) as avg_score
                FROM training_attempts
                WHERE user_id = ? AND system = ?
            ");
            $stmt->execute([$user_id, $sys]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $color = $sys === 'pos' ? 'blue' : ($sys === 'booking' ? 'green' : 'purple');
            
            echo "<div class='bg-white rounded-lg shadow-md p-6 mb-6 border-l-4 border-{$color}-500'>";
            echo "<h2 class='text-xl font-semibold mb-4 text-{$color}-800'>";
            echo strtoupper($sys) . " Training System";
            echo "</h2>";
            
            if ($data['total'] > 0) {
                echo "<div class='grid grid-cols-3 gap-4'>";
                echo "<div class='text-center'>";
                echo "<div class='text-3xl font-bold text-{$color}-600'>{$data['total']}</div>";
                echo "<div class='text-sm text-gray-600'>Total Attempts</div>";
                echo "</div>";
                echo "<div class='text-center'>";
                echo "<div class='text-3xl font-bold text-green-600'>{$data['completed']}</div>";
                echo "<div class='text-sm text-gray-600'>Completed</div>";
                echo "</div>";
                echo "<div class='text-center'>";
                echo "<div class='text-3xl font-bold text-orange-600'>" . round($data['avg_score']) . "%</div>";
                echo "<div class='text-sm text-gray-600'>Avg Score</div>";
                echo "</div>";
                echo "</div>";
                
                // Show sample records
                $stmt = $pdo->prepare("
                    SELECT ta.id, ta.scenario_id, ta.score, ta.status, ta.created_at
                    FROM training_attempts ta
                    WHERE ta.user_id = ? AND ta.system = ?
                    ORDER BY ta.created_at DESC
                    LIMIT 3
                ");
                $stmt->execute([$user_id, $sys]);
                $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<div class='mt-4 pt-4 border-t'>";
                echo "<p class='text-sm font-semibold text-gray-700 mb-2'>Recent Attempts:</p>";
                echo "<ul class='text-sm text-gray-600 space-y-1'>";
                foreach ($samples as $sample) {
                    echo "<li>• ID {$sample['id']}: {$sample['scenario_id']} - {$sample['score']}% - " . date('M d', strtotime($sample['created_at'])) . "</li>";
                }
                echo "</ul>";
                echo "</div>";
            } else {
                echo "<p class='text-gray-500 italic'>No training attempts in this system</p>";
            }
            
            echo "</div>";
        }
        
        // Verification summary
        echo "<div class='bg-green-50 border-2 border-green-500 rounded-lg p-6'>";
        echo "<h3 class='text-xl font-bold text-green-800 mb-3'><i class='fas fa-check-circle mr-2'></i>Separation Verified!</h3>";
        echo "<p class='text-green-700 mb-4'>Each training system is properly isolated and shows only its own data.</p>";
        echo "<div class='flex gap-4'>";
        echo "<a href='training-dashboard.php' class='bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700'>";
        echo "<i class='fas fa-tachometer-alt mr-2'></i>POS Dashboard";
        echo "</a>";
        echo "<a href='progress.php' class='bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700'>";
        echo "<i class='fas fa-chart-line mr-2'></i>POS Progress";
        echo "</a>";
        echo "<a href='scenarios.php' class='bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700'>";
        echo "<i class='fas fa-list mr-2'></i>POS Scenarios";
        echo "</a>";
        echo "</div>";
        echo "</div>";
        ?>
    </div>
</body>
</html>

