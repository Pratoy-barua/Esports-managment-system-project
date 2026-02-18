<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_team') {
    $team_name = sanitize($conn, $_POST['team_name']);
    $game_category = sanitize($conn, $_POST['game_category']);
    $description = sanitize($conn, $_POST['description']);
    

    $sql = "INSERT INTO teams (team_name, captain_id, game_category, description) 
            VALUES ('$team_name', $user_id, '$game_category', '$description')";
    
    if (executeQuery($conn, $sql)) {
        $team_id = $conn->insert_id;
        
     
        $sql = "INSERT INTO team_members (team_id, user_id, role) VALUES ($team_id, $user_id, 'Captain')";
        executeQuery($conn, $sql);
        
        header("Location: teams.php?success=Team created successfully");
        exit();
    }
}


$sql = "SELECT t.*, tm.role, 
        (SELECT COUNT(*) FROM team_members WHERE team_id = t.team_id) as member_count
        FROM teams t 
        INNER JOIN team_members tm ON t.team_id = tm.team_id 
        WHERE tm.user_id = $user_id 
        ORDER BY t.created_at DESC";
$user_teams = getAllRows($conn, $sql);

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
    <title>Teams - ESportsHub</title>
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
                <h1>My Teams</h1>
                <button class="btn btn-primary" onclick="openCreateTeamModal()">
                    <i class="fas fa-plus"></i> Create Team
                </button>
            </div>

            <div class="dashboard-content">
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
                <?php endif; ?>

                <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="stat-box">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Teams</h3>
                            <p class="stat-value"><?php echo count($user_teams); ?></p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Leading</h3>
                            <p class="stat-value"><?php echo count(array_filter($user_teams, fn($t) => $t['role'] === 'Captain')); ?></p>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Member Of</h3>
                            <p class="stat-value"><?php echo count(array_filter($user_teams, fn($t) => $t['role'] !== 'Captain')); ?></p>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2>Your Teams</h2>
                    <?php if (empty($user_teams)): ?>
                    <p class="empty-state">You haven't joined any teams yet. Create one to get started!</p>
                    <?php else: ?>
                    <div class="tournament-grid">
                        <?php foreach ($user_teams as $team): ?>
                        <div class="tournament-card">
                            <div class="tournament-card-header">
                                <h3><?php echo htmlspecialchars($team['team_name']); ?></h3>
                                <div class="tournament-meta">
                                    <span class="tournament-badge">
                                        <i class="fas fa-gamepad"></i> <?php echo htmlspecialchars($team['game_category']); ?>
                                    </span>
                                    <span class="tournament-badge">
                                        <i class="fas fa-<?php echo $team['role'] === 'Captain' ? 'crown' : 'user'; ?>"></i> 
                                        <?php echo htmlspecialchars($team['role']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="tournament-card-body">
                                <div class="tournament-info">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo $team['member_count']; ?> Members</span>
                                </div>
                                <?php if ($team['description']): ?>
                                <p style="color: var(--text-muted); margin: 1rem 0;"><?php echo htmlspecialchars(substr($team['description'], 0, 100)); ?></p>
                                <?php endif; ?>
                                <button onclick="viewTeam(<?php echo $team['team_id']; ?>)" class="btn btn-secondary btn-full">
                                    <i class="fas fa-info-circle"></i> View Details
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

   
    <div id="createTeamModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCreateTeamModal()">&times;</span>
            <h2>Create New Team</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create_team">
                
                <div class="form-group">
                    <label>Team Name *</label>
                    <input type="text" name="team_name" required>
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
                        <option value="League of Legends">League of Legends</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Create Team</button>
            </form>
        </div>
    </div>

    <script>
        function openCreateTeamModal() {
            document.getElementById('createTeamModal').style.display = 'block';
        }
        
        function closeCreateTeamModal() {
            document.getElementById('createTeamModal').style.display = 'none';
        }
        
        function viewTeam(teamId) {
            window.location.href = `team_details.php?id=${teamId}`;
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('createTeamModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php closeConnection($conn); ?>
