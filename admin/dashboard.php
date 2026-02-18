<?php


session_start();
require_once '../config/database.php';


$conn = getConnection(); 

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['full_name'];

// Fetch dashboard statistics
$stats = [];

// Total Users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total Students
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE profession = 'Student' AND role = 'user'");
$stats['total_students'] = $result->fetch_assoc()['count'];

// Active Subscriptions
$result = $conn->query("SELECT COUNT(*) as count FROM subscriptions WHERE is_active = 1 AND expires_at > NOW()");
$stats['active_subscriptions'] = $result->fetch_assoc()['count'];

// Total Tournaments
$result = $conn->query("SELECT COUNT(*) as count FROM tournaments");
$stats['total_tournaments'] = $result->fetch_assoc()['count'];

// Running Events
$result = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'Running'");
$stats['running_events'] = $result->fetch_assoc()['count'];

// Total Revenue
$result = $conn->query("SELECT IFNULL(SUM(amount), 0) as revenue FROM payments WHERE payment_status = 'completed'");
$stats['total_revenue'] = $result->fetch_assoc()['revenue'];

// Pending Hosting Requests
$result = $conn->query("SELECT COUNT(*) as count FROM hosting_requests WHERE status = 'Pending'");
$stats['pending_requests'] = $result->fetch_assoc()['count'];

// Pending Orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'Processing'");
$stats['pending_orders'] = $result->fetch_assoc()['count'];

// Monthly User Growth (Last 6 Months)
$monthly_users = [];
$result = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $monthly_users[] = $row;
    }
}

// Recent Activities
$recent_logs = [];
$result = $conn->query("
    SELECT al.*, u.full_name, u.username 
    FROM admin_logs al 
    JOIN users u ON al.admin_id = u.user_id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_logs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 260px;
            background: #1e293b;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-logo {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #334155;
            margin-bottom: 20px;
        }
        
        .admin-logo h2 {
            color: #818cf8;
            font-size: 22px;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: #334155;
            color: #818cf8;
        }
        
        .admin-nav a i {
            margin-right: 12px;
            width: 20px;
        }
        
        .admin-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            font-size: 28px;
            color: #f1f5f9;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-user img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid #818cf8;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #1e293b;
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid;
        }
        
        .stat-card.users { border-left-color: #3b82f6; }
        .stat-card.students { border-left-color: #8b5cf6; }
        .stat-card.subscriptions { border-left-color: #10b981; }
        .stat-card.tournaments { border-left-color: #f59e0b; }
        .stat-card.events { border-left-color: #ef4444; }
        .stat-card.revenue { border-left-color: #14b8a6; }
        .stat-card.requests { border-left-color: #ec4899; }
        .stat-card.orders { border-left-color: #06b6d4; }
        
        .stat-card h3 {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 10px;
        }
        
        .stat-card .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #f1f5f9;
        }
        
        .stat-card .stat-icon {
            float: right;
            font-size: 36px;
            opacity: 0.3;
        }
        
        .chart-container {
            background: #1e293b;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .chart-container h2 {
            margin-bottom: 20px;
            color: #f1f5f9;
        }
        
        .activity-log {
            background: #1e293b;
            padding: 25px;
            border-radius: 12px;
        }
        
        .activity-log h2 {
            margin-bottom: 20px;
            color: #f1f5f9;
        }
        
        .log-item {
            padding: 15px;
            border-bottom: 1px solid #334155;
            display: flex;
            justify-content: space-between;
        }
        
        .log-item:last-child { border-bottom: none; }
        
        .log-item .log-text { color: #cbd5e1; }
        .log-item .log-time { color: #64748b; font-size: 13px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h2><i class="fas fa-gamepad"></i> ESportsHub</h2>
                <p style="font-size: 12px; color: #64748b;">Admin Panel</p>
            </div>
            
            <nav class="admin-nav">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="users.php">
                    <i class="fas fa-users"></i> User Management
                </a>
                <a href="tournaments.php">
                    <i class="fas fa-trophy"></i> Tournaments
                </a>
                <a href="hosting.php">
                    <i class="fas fa-calendar-check"></i> Hosting Requests
                </a>
                <a href="teams.php">
                    <i class="fas fa-users-gear"></i> Teams
                </a>
                <a href="products.php">
                    <i class="fas fa-box"></i> Products & Orders
                </a>
                <a href="subscriptions.php">
                    <i class="fas fa-crown"></i> Subscriptions
                </a>
                <a href="messages.php">
                    <i class="fas fa-envelope"></i> Messages
                </a>
                <a href="notifications.php">
                    <i class="fas fa-bell"></i> Notifications
                </a>
                <a href="logs.php">
                    <i class="fas fa-history"></i> Activity Logs
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Dashboard Overview</h1>
                <div class="admin-user">
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                        <div style="font-size: 13px; color: #64748b;">Administrator</div>
                    </div>
                    <img src="../assets/images/default-avatar.png" alt="Admin">
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card users">
                    <i class="fas fa-users stat-icon"></i>
                    <h3>Total Users</h3>
                    <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                </div>
                
                <div class="stat-card students">
                    <i class="fas fa-graduation-cap stat-icon"></i>
                    <h3>Total Students</h3>
                    <div class="stat-value"><?php echo number_format($stats['total_students']); ?></div>
                </div>
                
                <div class="stat-card subscriptions">
                    <i class="fas fa-crown stat-icon"></i>
                    <h3>Active Subscriptions</h3>
                    <div class="stat-value"><?php echo number_format($stats['active_subscriptions']); ?></div>
                </div>
                
                <div class="stat-card tournaments">
                    <i class="fas fa-trophy stat-icon"></i>
                    <h3>Total Tournaments</h3>
                    <div class="stat-value"><?php echo number_format($stats['total_tournaments']); ?></div>
                </div>
                
                <div class="stat-card events">
                    <i class="fas fa-calendar-day stat-icon"></i>
                    <h3>Running Events</h3>
                    <div class="stat-value"><?php echo number_format($stats['running_events']); ?></div>
                </div>
                
                <div class="stat-card revenue">
                    <i class="fas fa-dollar-sign stat-icon"></i>
                    <h3>Total Revenue</h3>
                    <div class="stat-value">৳<?php echo number_format($stats['total_revenue']); ?></div>
                </div>
                
                <div class="stat-card requests">
                    <i class="fas fa-clock stat-icon"></i>
                    <h3>Pending Requests</h3>
                    <div class="stat-value"><?php echo number_format($stats['pending_requests']); ?></div>
                </div>
                
                <div class="stat-card orders">
                    <i class="fas fa-shopping-cart stat-icon"></i>
                    <h3>Pending Orders</h3>
                    <div class="stat-value"><?php echo number_format($stats['pending_orders']); ?></div>
                </div>
            </div>
            
            <div class="chart-container">
                <h2><i class="fas fa-chart-bar"></i> User Growth (Last 6 Months)</h2>
                <canvas id="userGrowthChart" height="80"></canvas>
            </div>
            
            <div class="activity-log">
                <h2><i class="fas fa-history"></i> Recent Admin Activity</h2>
                <?php if (!empty($recent_logs)): ?>
                    <?php foreach ($recent_logs as $log): ?>
                        <div class="log-item">
                            <div class="log-text">
                                <strong><?php echo htmlspecialchars($log['full_name']); ?></strong>
                                <?php echo htmlspecialchars($log['action_type']); ?> 
                                <?php echo htmlspecialchars($log['affected_entity']); ?>
                            </div>
                            <div class="log-time">
                                <?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #64748b; padding: 15px;">No recent activities found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // User Growth Chart
        const ctx = document.getElementById('userGrowthChart').getContext('2d');
        const chartData = <?php echo json_encode($monthly_users); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.month),
                datasets: [{
                    label: 'New Users',
                    data: chartData.map(d => d.count),
                    borderColor: '#818cf8',
                    backgroundColor: 'rgba(129, 140, 248, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#cbd5e1' } }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: { color: '#cbd5e1' },
                        grid: { color: '#334155' }
                    },
                    x: { 
                        ticks: { color: '#cbd5e1' },
                        grid: { color: '#334155' }
                    }
                }
            }
        });
    </script>
</body>
</html>