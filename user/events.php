<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Check if student with subscription
if ($_SESSION['profession'] !== 'Student') {
    header("Location: dashboard.php");
    exit();
}

if (!hasActiveSubscription()) {
    header("Location: subscription.php?error=Active subscription required");
    exit();
}

// Handle hosting request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_hosting') {
    $event_name = sanitize($conn, $_POST['event_name']);
    $game_category = sanitize($conn, $_POST['game_category']);
    $event_type = sanitize($conn, $_POST['event_type']);
    $expected_participants = (int)$_POST['expected_participants'];
    $prize_pool = isset($_POST['prize_pool']) ? (float)$_POST['prize_pool'] : 0;
    $rules = sanitize($conn, $_POST['rules']);
    $start_date = sanitize($conn, $_POST['start_date']);
    $end_date = sanitize($conn, $_POST['end_date']);
    
    // Get student university
    $sql = "SELECT university_id FROM student_profiles WHERE user_id = $user_id";
    $student = getSingleRow($conn, $sql);
    $university_id = $student ? $student['university_id'] : 'NULL';
    
    $sql = "INSERT INTO hosting_requests (user_id, event_name, game_category, event_type, hosting_university_id, expected_participants, prize_pool, rules, start_date, end_date) 
            VALUES ($user_id, '$event_name', '$game_category', '$event_type', $university_id, $expected_participants, $prize_pool, '$rules', '$start_date', '$end_date')";
    
    if (executeQuery($conn, $sql)) {
        $sql = "INSERT INTO notifications (user_id, title, message, notification_type) 
                VALUES ($user_id, 'Hosting Request Submitted', 'Your event hosting request has been submitted for admin review.', 'Hosting')";
        executeQuery($conn, $sql);
        
        header("Location: events.php?success=Hosting request submitted successfully");
        exit();
    }
}

// Get events
$sql = "SELECT e.*, u.full_name as host_name, uni.university_name 
        FROM events e 
        LEFT JOIN users u ON e.host_id = u.user_id 
        LEFT JOIN universities uni ON e.hosting_university_id = uni.university_id 
        WHERE e.status IN ('Upcoming', 'Running') 
        ORDER BY e.start_date ASC";
$events = getAllRows($conn, $sql);

// Get user's hosting requests
$sql = "SELECT * FROM hosting_requests WHERE user_id = $user_id ORDER BY requested_at DESC";
$hosting_requests = getAllRows($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Hub - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="top-bar">
                <h1>Events Hub (Student Exclusive)</h1>
                <button class="btn btn-primary" onclick="openHostingModal()">
                    <i class="fas fa-calendar-plus"></i> Request to Host Event
                </button>
            </div>
            <div class="dashboard-content">
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
                <?php endif; ?>

                <!-- Running/Upcoming Events -->
                <div class="section">
                    <h2>Available Events</h2>
                    <?php if (empty($events)): ?>
                    <p class="empty-state">No events available at the moment</p>
                    <?php else: ?>
                    <div class="tournament-grid">
                        <?php foreach ($events as $event): ?>
                        <div class="tournament-card">
                            <div class="tournament-card-header">
                                <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                <div class="tournament-meta">
                                    <span class="tournament-badge">
                                        <i class="fas fa-gamepad"></i> <?php echo htmlspecialchars($event['game_category']); ?>
                                    </span>
                                    <span class="tournament-badge">
                                        <i class="fas fa-<?php echo $event['event_type'] === 'University Only' ? 'graduation-cap' : 'globe'; ?>"></i>
                                        <?php echo htmlspecialchars($event['event_type']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="tournament-card-body">
                                <div class="tournament-info">
                                    <i class="fas fa-user"></i>
                                    <span>Hosted by <?php echo htmlspecialchars($event['host_name']); ?></span>
                                </div>
                                <?php if ($event['university_name']): ?>
                                <div class="tournament-info">
                                    <i class="fas fa-university"></i>
                                    <span><?php echo htmlspecialchars($event['university_name']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="tournament-info">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('M d - M d, Y', strtotime($event['start_date']), strtotime($event['end_date'])); ?></span>
                                </div>
                                <div class="tournament-info">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo $event['current_participants']; ?> / <?php echo $event['max_participants'] ?? 'Unlimited'; ?> Participants</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- My Hosting Requests -->
                <div class="section">
                    <h2>My Hosting Requests</h2>
                    <?php if (empty($hosting_requests)): ?>
                    <p class="empty-state">You haven't requested to host any events yet</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Event Name</th>
                                    <th>Game</th>
                                    <th>Type</th>
                                    <th>Start Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hosting_requests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['event_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['game_category']); ?></td>
                                    <td><?php echo htmlspecialchars($request['event_type']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($request['start_date'])); ?></td>
                                    <td><span class="badge-status"><?php echo htmlspecialchars($request['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Host Event Modal -->
    <div id="hostingModal" class="modal">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeHostingModal()">&times;</span>
            <h2>Request to Host Event</h2>
            <form method="POST">
                <input type="hidden" name="action" value="request_hosting">
                <div class="form-row">
                    <div class="form-group">
                        <label>Event Name *</label>
                        <input type="text" name="event_name" required>
                    </div>
                    <div class="form-group">
                        <label>Game Category *</label>
                        <select name="game_category" required>
                            <option value="">Select Game</option>
                            <option value="PUBG Mobile">PUBG Mobile</option>
                            <option value="Free Fire">Free Fire</option>
                            <option value="Valorant">Valorant</option>
                            <option value="CS:GO">CS:GO</option>
                            <option value="Dota 2">Dota 2</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Event Type *</label>
                        <select name="event_type" required>
                            <option value="">Select Type</option>
                            <option value="University Only">University Only</option>
                            <option value="Open For All">Open For All</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Expected Participants *</label>
                        <input type="number" name="expected_participants" min="4" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Prize Pool (BDT)</label>
                        <input type="number" name="prize_pool" step="0.01" min="0">
                    </div>
                    <div class="form-group"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date *</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date *</label>
                        <input type="date" name="end_date" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Rules & Regulations *</label>
                    <textarea name="rules" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Submit Request</button>
            </form>
        </div>
    </div>

    <script>
        function openHostingModal() {
            document.getElementById('hostingModal').style.display = 'block';
        }
        
        function closeHostingModal() {
            document.getElementById('hostingModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('hostingModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php closeConnection($conn); ?>
