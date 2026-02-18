<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];


$sql = "SELECT * FROM users WHERE user_id = $user_id";
$user = getSingleRow($conn, $sql);

// Get student profile if applicable
$student_profile = null;
if ($user['profession'] === 'Student') {
    $sql = "SELECT sp.*, u.university_name, d.department_name 
            FROM student_profiles sp 
            LEFT JOIN universities u ON sp.university_id = u.university_id 
            LEFT JOIN departments d ON sp.department_id = d.department_id 
            WHERE sp.user_id = $user_id";
    $student_profile = getSingleRow($conn, $sql);
    checkSubscriptionStatus($conn, $user_id);
}


$job_profile = null;
if ($user['profession'] === 'Job Holder') {
    $sql = "SELECT * FROM job_holder_profiles WHERE user_id = $user_id";
    $job_profile = getSingleRow($conn, $sql);
}


$sql = "SELECT COUNT(*) as count FROM participants WHERE user_id = $user_id AND tournament_id IS NOT NULL";
$tournament_count = getSingleRow($conn, $sql)['count'];

$sql = "SELECT COUNT(*) as count FROM team_members WHERE user_id = $user_id";
$team_count = getSingleRow($conn, $sql)['count'];

$sql = "SELECT COUNT(*) as count FROM participants WHERE user_id = $user_id AND event_id IS NOT NULL";
$event_count = getSingleRow($conn, $sql)['count'];


$sql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = $user_id AND is_read = 0";
$notification_count = getSingleRow($conn, $sql)['unread'];


$sql = "SELECT COUNT(*) as unread FROM messages WHERE receiver_id = $user_id AND is_read = 0";
$message_count = getSingleRow($conn, $sql)['unread'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-gamepad"></i>
                    <span>ESports<strong>Hub</strong></span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="profile.php" class="nav-item">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                <a href="tournaments.php" class="nav-item">
                    <i class="fas fa-trophy"></i>
                    <span>Tournaments</span>
                </a>
                <a href="teams.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Teams</span>
                </a>
                <a href="tickets.php" class="nav-item">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Tickets</span>
                </a>
                <a href="products.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Products</span>
                </a>
                <?php if ($user['profession'] === 'Student'): ?>
                <a href="subscription.php" class="nav-item">
                    <i class="fas fa-star"></i>
                    <span>Subscription</span>
                </a>
                <?php if (hasActiveSubscription()): ?>
                <a href="events.php" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events Hub</span>
                </a>
                <?php endif; ?>
                <?php endif; ?>
                <a href="notifications.php" class="nav-item">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <?php if ($notification_count > 0): ?>
                    <span class="badge"><?php echo $notification_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="messages.php" class="nav-item">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <?php if ($message_count > 0): ?>
                    <span class="badge"><?php echo $message_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="../auth/logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div>
                    <h1>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                    <p class="top-subtitle">Here's what's happening with your esports journey today</p>
                </div>
                <div class="user-info">
                    <div class="notification-icon" onclick="window.location.href='notifications.php'">
                        <i class="fas fa-bell"></i>
                        <?php if ($notification_count > 0): ?>
                        <span class="badge-dot"></span>
                        <?php endif; ?>
                    </div>
                    <div class="user-avatar">
                        <img src="../assets/images/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Tournaments Joined</h3>
                            <p class="stat-value"><?php echo $tournament_count; ?></p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>My Teams</h3>
                            <p class="stat-value"><?php echo $team_count; ?></p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Events Participated</h3>
                            <p class="stat-value"><?php echo $event_count; ?></p>
                        </div>
                    </div>
                    <?php if ($user['profession'] === 'Student'): ?>
                    <div class="stat-box">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Subscription</h3>
                            <p class="stat-value"><?php echo hasActiveSubscription() ? 'Active' : 'Inactive'; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="section">
                    <h2>Quick Actions</h2>
                    <div class="action-grid">
                        <a href="tournaments.php" class="action-card">
                            <i class="fas fa-trophy"></i>
                            <h3>Join Tournament</h3>
                            <p>Browse and join active tournaments</p>
                        </a>
                        <a href="teams.php?action=create" class="action-card">
                            <i class="fas fa-users"></i>
                            <h3>Create Team</h3>
                            <p>Build your dream esports team</p>
                        </a>
                        <a href="products.php" class="action-card">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Shop Products</h3>
                            <p>Browse gaming merchandise</p>
                        </a>
                        <?php if ($user['profession'] === 'Student' && hasActiveSubscription()): ?>
                        <a href="events.php?action=host" class="action-card">
                            <i class="fas fa-calendar-plus"></i>
                            <h3>Host Event</h3>
                            <p>Apply to host your event</p>
                        </a>
                        <?php else: ?>
                        <a href="messages.php?to=admin" class="action-card">
                            <i class="fas fa-envelope"></i>
                            <h3>Contact Admin</h3>
                            <p>Send message to support</p>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="section">
                    <h2>Recent Notifications</h2>
                    <div class="notification-list">
                        <?php
                        $sql = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
                        $notifications = getAllRows($conn, $sql);
                        
                        if (empty($notifications)):
                        ?>
                        <p class="empty-state">No notifications yet</p>
                        <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                            <i class="fas fa-bell"></i>
                            <div class="notification-content">
                                <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <small><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <a href="notifications.php" class="btn btn-secondary" style="margin-top: 1rem;">View All Notifications</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php closeConnection($conn); ?>
