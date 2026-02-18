<?php
ob_start(); // বাফারিং শুরু

require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// --- POST Logic (Join Tournament) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'join_tournament') {
    $tournament_id = (int)$_POST['tournament_id'];
    $payment_method = isset($_POST['payment_method']) ? sanitize($conn, $_POST['payment_method']) : '';
    $team_id = isset($_POST['team_id']) && $_POST['team_id'] !== '' ? (int)$_POST['team_id'] : null;
      
    // 1. Check if already joined (First Layer Security)
    $sql = "SELECT * FROM participants WHERE tournament_id = $tournament_id AND user_id = $user_id";
    $existing = getSingleRow($conn, $sql);
    
    if ($existing) {
        header("Location: tournaments.php?error=You have already joined this tournament");
        exit();
    }

    // 2. Get Tournament Info
    $checkSql = "SELECT is_suspended, join_locked, registration_fee, max_slots FROM tournaments WHERE tournament_id = $tournament_id";
    $tournamentInfo = getSingleRow($conn, $checkSql);
    
    if (!$tournamentInfo) {
        header("Location: tournaments.php?error=Invalid tournament");
        exit();
    }
    
    if ($tournamentInfo['is_suspended'] == 1) {
        header("Location: tournaments.php?error=This tournament is suspended by admin");
        exit();
    }
    
    if ($tournamentInfo['join_locked'] == 1) {
        header("Location: tournaments.php?error=Joining is temporarily locked by admin");
        exit();
    }

    // --- Check Slot Limit BEFORE Join ---
    if ($tournamentInfo['max_slots'] > 0) {
        $countSql = "SELECT COUNT(*) as total FROM participants WHERE tournament_id = $tournament_id";
        $countRow = getSingleRow($conn, $countSql);
        $currentJoined = $countRow ? (int)$countRow['total'] : 0;
        
        if ($currentJoined >= $tournamentInfo['max_slots']) {
            header("Location: tournaments.php?error=Tournament is full!");
            exit();
        }
    }
    
    // 3. Payment Logic
    $fee = (float)$tournamentInfo['registration_fee'];
    $allowed_methods = ['bkash', 'nagad', 'Free'];

    if ($fee > 0) {
        if (empty($payment_method) || $payment_method === 'Free') {
            header("Location: tournaments.php?error=Valid payment method required for paid tournaments");
            exit();
        }
        $payment_status = 'Paid'; 
    } else {
        $payment_method = 'Free';
        $payment_status = 'Free';
    }

    if (!in_array($payment_method, $allowed_methods)) {
        header("Location: tournaments.php?error=Invalid payment method selected");
        exit();
    }

    // 4. Insert Participant (DB Unique Constraint will be the Final Layer Security)
    $sqlTeamVal = ($team_id === null) ? "NULL" : $team_id;
    
    $sql = "INSERT INTO participants (tournament_id, user_id, team_id, payment_status, payment_method, status) 
            VALUES ($tournament_id, $user_id, $sqlTeamVal, '$payment_status', '$payment_method', 'Registered')";
    
    if (executeQuery($conn, $sql)) {
        
        // 🔒 AUTO LOCK LOGIC
        if ($tournamentInfo['max_slots'] > 0) {
            $countSql = "SELECT COUNT(*) as total FROM participants WHERE tournament_id = $tournament_id";
            $countRow = getSingleRow($conn, $countSql);
            $currentJoined = $countRow ? (int)$countRow['total'] : 0;

            if ($currentJoined >= $tournamentInfo['max_slots']) {
                $lockSql = "UPDATE tournaments SET join_locked = 1 WHERE tournament_id = $tournament_id";
                executeQuery($conn, $lockSql);
            }
        }

        // Notification
        $notifMsg = "Success! Payment: $payment_method";
        $sql = "INSERT INTO notifications (user_id, title, message, notification_type) 
                VALUES ($user_id, 'Tournament Joined!', '$notifMsg', 'Tournament')";
        executeQuery($conn, $sql);
        
        header("Location: tournaments.php?success=Tournament joined successfully!");
        exit();
    } else {
        // If DB unique constraint fails, it falls here
        header("Location: tournaments.php?error=Could not join. Maybe already joined?");
        exit();
    }
}

// Fetch Tournaments
$sql = "SELECT t.*, u.full_name as organizer_name FROM tournaments t 
        INNER JOIN users u ON t.organizer_id = u.user_id 
        ORDER BY t.created_at DESC";
$tournaments = getAllRows($conn, $sql);

// Fetch User's Participations
$sql = "SELECT tournament_id FROM participants WHERE user_id = $user_id";
$participations = getAllRows($conn, $sql);

$participated_ids = [];
if ($participations) {
    $participated_ids = array_map('intval', array_column($participations, 'tournament_id'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tournaments | ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Modal Styles */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7); z-index: 1000; justify-content: center; align-items: center;
        }
        .modal-content {
            background: #1e293b; padding: 25px; border-radius: 12px;
            width: 90%; max-width: 450px; border: 1px solid #334155;
            color: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.5); animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .modal-title { font-size: 1.25rem; font-weight: bold; }
        .close-modal { background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; }
        
        .payment-options { display: flex; gap: 10px; margin: 20px 0; }
        .pay-btn {
            flex: 1; padding: 10px; border: 1px solid #334155;
            background: #0f172a; color: #fff; border-radius: 8px; cursor: pointer;
            transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 5px;
        }
        .pay-btn:hover { border-color: #818cf8; }
        .pay-btn.selected { background: #818cf8; color: #fff; border-color: #818cf8; }
        
        .error-msg {
            color: #ef4444; font-size: 0.85rem; margin-top: 5px; display: none;
            background: rgba(239, 68, 68, 0.1); padding: 8px; border-radius: 5px;
        }

        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; color: #cbd5e1; }
        .modal-footer { margin-top: 20px; display: flex; gap: 10px; }
        
        .tournament-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .tournament-card { background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="top-bar"><h1>Tournaments</h1></div>

            <div class="dashboard-content">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success" style="color: green; background: #d1fae5; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                        <?= htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error" style="color: red; background: #fee2e2; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                        <?= htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="tournament-grid">
                    <?php if ($tournaments): ?>
                        <?php foreach ($tournaments as $tournament): ?>
                        
                        <?php 
                            // SLOT COUNT LOGIC
                            $joinedCount = 0;
                            $slotPercent = 0;
                            $isFull = false;

                            if (isset($tournament['max_slots']) && $tournament['max_slots'] > 0) {
                                $countSql = "SELECT COUNT(*) as total FROM participants WHERE tournament_id = {$tournament['tournament_id']}";
                                $countRow = getSingleRow($conn, $countSql);
                                $joinedCount = $countRow ? (int)$countRow['total'] : 0;
                                
                                $slotPercent = min(100, round(($joinedCount / $tournament['max_slots']) * 100));
                                
                                if ($joinedCount >= $tournament['max_slots']) {
                                    $isFull = true;
                                }
                            }
                        ?>

                        <div class="tournament-card">
                            <h3><?= htmlspecialchars($tournament['tournament_name']); ?></h3>
                            <p style="color: #818cf8; font-size: 14px;"><?= htmlspecialchars($tournament['game_category']); ?></p>
                            
                            <div style="margin: 15px 0;">
                                <?php if ($tournament['is_suspended'] == 1): ?>
                                    <span style="color: #ef4444; font-weight: bold;">[SUSPENDED]</span>
                                
                                <?php elseif ($tournament['join_locked'] == 1): ?>
                                    <span style="color: #f59e0b; font-weight: bold;">[JOIN LOCKED]</span>
                                
                                <?php elseif ($isFull): ?>
                                    <span style="color: #ef4444; font-weight: bold;">[FULL]</span>
                                
                                <?php else: ?>
                                    <span style="color: #10b981; font-weight: bold;"><?= strtoupper($tournament['status']); ?></span>
                                <?php endif; ?>
                                <span style="float: right; color: #cbd5e1;">Fee: ৳<?= number_format($tournament['registration_fee'], 0) ?></span>
                            </div>

                            <?php if (isset($tournament['max_slots']) && $tournament['max_slots'] > 0): ?>
                                <div style="margin:10px 0;">
                                    <div style="display:flex; justify-content:space-between; font-size:12px; color:#cbd5e1;">
                                        <span>Slots</span>
                                        <span><?= $joinedCount ?> / <?= $tournament['max_slots'] ?></span>
                                    </div>
                                    <div style="background:#334155; border-radius:6px; height:8px; margin-top:5px;">
                                        <div style="
                                            width:<?= $slotPercent ?>%;
                                            height:8px;
                                            border-radius:6px;
                                            transition: width 0.5s ease;
                                            background:<?= ($slotPercent >= 100) ? '#ef4444' : '#22c55e' ?>;
                                        "></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <?php if ($tournament['is_suspended'] == 1 || strtolower($tournament['status']) === 'completed'): ?>
                                    <button class="btn btn-secondary" disabled style="flex: 1; padding:10px; cursor:not-allowed; background:#475569; color:#94a3b8; border:none; border-radius:5px;">Closed</button>
                                
                                <?php elseif (in_array((int)$tournament['tournament_id'], $participated_ids)): ?>
                                    <button class="btn btn-success" disabled style="flex: 1; padding:10px; cursor:not-allowed; background:#059669; color:white; border:none; border-radius:5px;">Joined</button>
                                
                                <?php elseif ($tournament['join_locked'] == 1): ?>
                                    <button class="btn btn-secondary" disabled style="flex: 1; padding:10px; cursor:not-allowed; background:#475569; color:#94a3b8; border:none; border-radius:5px;">Locked</button>
                                
                                <?php elseif ($isFull): ?>
                                    <button class="btn btn-secondary" disabled style="flex: 1; padding:10px; cursor:not-allowed; background:#ef4444; color:white; border:none; border-radius:5px;">Full</button>

                                <?php elseif (strtolower($tournament['status']) === 'upcoming'): ?>
                                    <button 
                                        class="btn btn-primary" 
                                        style="flex: 1; padding:10px; background:#2563eb; color:white; border:none; border-radius:5px; cursor:pointer;"
                                        onclick="openJoinModal(
                                            <?= $tournament['tournament_id'] ?>, 
                                            '<?= htmlspecialchars($tournament['tournament_name'], ENT_QUOTES) ?>', 
                                            <?= $tournament['registration_fee'] ?>
                                        )">
                                        Join
                                    </button>
                                    
                                    <a 
                                        href="tournament_details.php?id=<?= $tournament['tournament_id'] ?>" 
                                        class="btn btn-secondary" 
                                        style="flex: 1; text-align:center; text-decoration: none; display: flex; align-items: center; justify-content: center; background:#334155; color:white; border-radius:5px;">
                                        Details
                                    </a>
                                
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled style="flex: 1; padding:10px; cursor:not-allowed; background:#475569; color:#94a3b8;">Closed</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #94a3b8;">No tournaments available right now.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <div id="joinModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">Join Tournament</span>
                <button class="close-modal" onclick="closeJoinModal()">&times;</button>
            </div>
            
            <form method="POST" id="joinForm">
                <input type="hidden" name="action" value="join_tournament">
                <input type="hidden" name="tournament_id" id="modalTournamentId">
                <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="">

                <div class="info-row">
                    <span>Tournament:</span>
                    <strong id="modalTournamentName" style="color:#fff;">Loading...</strong>
                </div>
                <div class="info-row">
                    <span>Entry Fee:</span>
                    <strong id="modalFee" style="color:#10b981;">৳0</strong>
                </div>

                <div id="paymentSection">
                    <p style="margin-top: 15px; font-size: 0.9rem; color: #94a3b8;">Select Payment Method:</p>
                    <div class="payment-options">
                        <div class="pay-btn" onclick="selectPayment('bkash', this)">
                            <i class="fa-solid fa-mobile-screen"></i> Bkash
                        </div>
                        <div class="pay-btn" onclick="selectPayment('nagad', this)">
                            <i class="fa-solid fa-wallet"></i> Nagad
                        </div>
                    </div>
                    <div id="paymentError" class="error-msg">
                        <i class="fa-solid fa-circle-exclamation"></i> Please select a payment method first.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeJoinModal()" style="flex:1; padding:10px; background:#475569; color:white; border:none; border-radius:5px; cursor:pointer;">Cancel</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary" style="flex:1; padding:10px; background:#2563eb; color:white; border:none; border-radius:5px; cursor:pointer;">Confirm Join</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('joinModal');
        const paymentInput = document.getElementById('selectedPaymentMethod');
        const paymentSection = document.getElementById('paymentSection');
        const submitBtn = document.getElementById('submitBtn');
        const errorMsg = document.getElementById('paymentError');

        function openJoinModal(id, name, fee) {
            document.getElementById('modalTournamentId').value = id;
            document.getElementById('modalTournamentName').textContent = name;
            document.getElementById('modalFee').textContent = '৳' + fee;
            document.querySelectorAll('.pay-btn').forEach(btn => btn.classList.remove('selected'));
            paymentInput.value = '';
            errorMsg.style.display = 'none'; 

            if (fee > 0) {
                paymentSection.style.display = 'block';
                paymentInput.required = true;
                submitBtn.textContent = 'Confirm & Pay';
            } else {
                paymentSection.style.display = 'none';
                paymentInput.required = false;
                paymentInput.value = 'Free';
                submitBtn.textContent = 'Confirm Join';
            }
            modal.style.display = 'flex';
        }

        function closeJoinModal() { modal.style.display = 'none'; }

        function selectPayment(method, btnElement) {
            document.querySelectorAll('.pay-btn').forEach(btn => btn.classList.remove('selected'));
            btnElement.classList.add('selected');
            paymentInput.value = method;
            errorMsg.style.display = 'none';
        }

        window.onclick = function(event) { if (event.target == modal) closeJoinModal(); }
        
        document.getElementById('joinForm').onsubmit = function(e) {
            const feeText = document.getElementById('modalFee').textContent;
            if (feeText !== '৳0' && paymentInput.value === '') {
                e.preventDefault(); 
                errorMsg.style.display = 'block'; 
            }
        };
    </script>
</body>
</html>