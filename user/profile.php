<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $full_name = sanitize($conn, $_POST['full_name']);
        $phone = sanitize($conn, $_POST['phone']);
        $gender = sanitize($conn, $_POST['gender']);
        $date_of_birth = sanitize($conn, $_POST['date_of_birth']);
        
        $sql = "UPDATE users SET full_name = '$full_name', phone = '$phone', gender = '$gender', date_of_birth = '$date_of_birth' WHERE user_id = $user_id";
        
        if (executeQuery($conn, $sql)) {
            $_SESSION['full_name'] = $full_name;
            header("Location: profile.php?success=Profile updated successfully");
            exit();
        }
    }
    
    if ($_POST['action'] === 'update_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $sql = "SELECT password_hash FROM users WHERE user_id = $user_id";
        $user_data = getSingleRow($conn, $sql);
        
        if (md5($current_password) === $user_data['password_hash']) {
            if ($new_password === $confirm_password && strlen($new_password) >= 6) {
                $new_hash = md5($new_password);
                $sql = "UPDATE users SET password_hash = '$new_hash' WHERE user_id = $user_id";
                
                if (executeQuery($conn, $sql)) {
                    header("Location: profile.php?success=Password changed successfully");
                    exit();
                }
            } else {
                header("Location: profile.php?error=New passwords do not match or too short");
                exit();
            }
        } else {
            header("Location: profile.php?error=Current password is incorrect");
            exit();
        }
    }
}

// Get user details
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$user = getSingleRow($conn, $sql);

// Get student profile
$student_profile = null;
if ($user['profession'] === 'Student') {
    $sql = "SELECT sp.*, u.university_name, d.department_name 
            FROM student_profiles sp 
            LEFT JOIN universities u ON sp.university_id = u.university_id 
            LEFT JOIN departments d ON sp.department_id = d.department_id 
            WHERE sp.user_id = $user_id";
    $student_profile = getSingleRow($conn, $sql);
}

// Get job holder profile
$job_profile = null;
if ($user['profession'] === 'Job Holder') {
    $sql = "SELECT * FROM job_holder_profiles WHERE user_id = $user_id";
    $job_profile = getSingleRow($conn, $sql);
}

// Get activity stats
$sql = "SELECT COUNT(*) as count FROM participants WHERE user_id = $user_id";
$total_participations = getSingleRow($conn, $sql)['count'];

$sql = "SELECT COUNT(*) as count FROM subscriptions WHERE user_id = $user_id";
$total_subscriptions = getSingleRow($conn, $sql)['count'];

// Get notifications count
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
    <title>Profile - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 3rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid var(--white);
            overflow: hidden;
        }
        
        .profile-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-header-info h1 {
            color: var(--white);
            margin-bottom: 0.5rem;
        }
        
        .profile-badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            color: var(--white);
            font-size: 0.9rem;
            margin-right: 0.5rem;
        }
        
        .tabs {
            display: flex;
            gap: 1rem;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 2rem;
        }
        
        .tab {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            position: relative;
            transition: color 0.3s ease;
        }
        
        .tab.active {
            color: var(--primary-color);
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .info-item {
            background: rgba(30, 41, 59, 0.5);
            padding: 1.5rem;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }
        
        .info-item label {
            display: block;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .info-item .value {
            color: var(--white);
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .activity-item {
            background: rgba(30, 41, 59, 0.5);
            padding: 1rem;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="top-bar">
                <h1>My Profile</h1>
            </div>

            <div class="dashboard-content">
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
                <?php endif; ?>

                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-avatar-large">
                        <img src="../assets/images/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                    </div>
                    <div class="profile-header-info">
                        <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                        <p style="color: rgba(255,255,255,0.8); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($user['username']); ?></p>
                        <div>
                            <span class="profile-badge">
                                <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($user['profession']); ?>
                            </span>
                            <span class="profile-badge">
                                <i class="fas fa-<?php echo $user['is_active'] ? 'check-circle' : 'times-circle'; ?>"></i> 
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="section">
                    <div class="tabs">
                        <button class="tab active" data-tab="overview">Overview</button>
                        <button class="tab" data-tab="about">About</button>
                        <button class="tab" data-tab="activity">Activity</button>
                        <?php if ($user['profession'] === 'Student'): ?>
                        <button class="tab" data-tab="subscriptions">Subscriptions</button>
                        <?php endif; ?>
                        <button class="tab" data-tab="security">Security</button>
                    </div>

                    <!-- Overview Tab -->
                    <div class="tab-content active" id="overview">
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Full Name</label>
                                <div class="value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Username</label>
                                <div class="value"><?php echo htmlspecialchars($user['username']); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Email</label>
                                <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Phone</label>
                                <div class="value"><?php echo htmlspecialchars($user['phone']); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Member Since</label>
                                <div class="value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Total Participations</label>
                                <div class="value"><?php echo $total_participations; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- About Tab -->
                    <div class="tab-content" id="about">
                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                            <h3>Personal Information</h3>
                            <button class="btn btn-primary" onclick="openEditModal()">
                                <i class="fas fa-edit"></i> Edit Profile
                            </button>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Full Name</label>
                                <div class="value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Email (Read-only)</label>
                                <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Phone</label>
                                <div class="value"><?php echo htmlspecialchars($user['phone']); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Gender</label>
                                <div class="value"><?php echo htmlspecialchars($user['gender']); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Date of Birth</label>
                                <div class="value"><?php echo date('F d, Y', strtotime($user['date_of_birth'])); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Profession</label>
                                <div class="value"><?php echo htmlspecialchars($user['profession']); ?></div>
                            </div>
                            
                            <?php if ($student_profile): ?>
                            <div class="info-item">
                                <label>University</label>
                                <div class="value"><?php echo htmlspecialchars($student_profile['university_name']); ?></div>
                            </div>
                            <div class="info-item">
                                <label>Department</label>
                                <div class="value"><?php echo htmlspecialchars($student_profile['department_name'] ?? 'Not specified'); ?></div>
                            </div>
                            <?php if ($student_profile['student_id_number']): ?>
                            <div class="info-item">
                                <label>Student ID</label>
                                <div class="value"><?php echo htmlspecialchars($student_profile['student_id_number']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($job_profile): ?>
                            <div class="info-item">
                                <label>Company Name</label>
                                <div class="value"><?php echo htmlspecialchars($job_profile['company_name']); ?></div>
                            </div>
                            <?php if ($job_profile['designation']): ?>
                            <div class="info-item">
                                <label>Designation</label>
                                <div class="value"><?php echo htmlspecialchars($job_profile['designation']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Activity Tab -->
                    <div class="tab-content" id="activity">
                        <h3 style="margin-bottom: 1.5rem;">Recent Activity</h3>
                        <?php
                        $sql = "SELECT 'tournament' as type, t.tournament_name as name, p.registration_date as date 
                                FROM participants p 
                                INNER JOIN tournaments t ON p.tournament_id = t.tournament_id 
                                WHERE p.user_id = $user_id 
                                UNION ALL
                                SELECT 'event' as type, e.event_name as name, p.registration_date as date 
                                FROM participants p 
                                INNER JOIN events e ON p.event_id = e.event_id 
                                WHERE p.user_id = $user_id 
                                ORDER BY date DESC LIMIT 10";
                        $activities = getAllRows($conn, $sql);
                        
                        if (empty($activities)):
                        ?>
                        <p class="empty-state">No activity yet</p>
                        <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                        <div class="activity-item">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <i class="fas fa-<?php echo $activity['type'] === 'tournament' ? 'trophy' : 'calendar-alt'; ?>" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                                <div style="flex: 1;">
                                    <h4 style="color: var(--white); margin-bottom: 0.3rem;">
                                        Joined <?php echo ucfirst($activity['type']); ?>: <?php echo htmlspecialchars($activity['name']); ?>
                                    </h4>
                                    <small style="color: var(--text-muted);">
                                        <?php echo date('F d, Y', strtotime($activity['date'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Subscriptions Tab -->
                    <?php if ($user['profession'] === 'Student'): ?>
                    <div class="tab-content" id="subscriptions">
                        <h3 style="margin-bottom: 1.5rem;">Subscription History</h3>
                        <?php
                        $sql = "SELECT * FROM subscriptions WHERE user_id = $user_id ORDER BY created_at DESC";
                        $subscriptions = getAllRows($conn, $sql);
                        
                        if (empty($subscriptions)):
                        ?>
                        <p class="empty-state">No subscriptions yet. <a href="subscription.php">Subscribe now</a></p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Plan</th>
                                        <th>Amount</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subscriptions as $sub): ?>
                                    <tr>
                                        <td><?php echo str_replace('_', ' ', ucwords($sub['plan_duration'], '_')); ?></td>
                                        <td>৳<?php echo number_format($sub['amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($sub['start_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($sub['end_date'])); ?></td>
                                        <td>
                                            <span class="badge-status <?php echo $sub['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $sub['is_active'] ? 'Active' : 'Expired'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Security Tab -->
                    <div class="tab-content" id="security">
                        <h3 style="margin-bottom: 1.5rem;">Change Password</h3>
                        <form method="POST" style="max-width: 600px;">
                            <input type="hidden" name="action" value="update_password">
                            
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" minlength="6" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" minlength="6" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-lock"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Profile</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="Male" <?php echo $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" value="<?php echo $user['date_of_birth']; ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                
                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });
        });
        
        // Modal functions
        function openEditModal() {
            document.getElementById('editProfileModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('editProfileModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php closeConnection($conn); ?>
