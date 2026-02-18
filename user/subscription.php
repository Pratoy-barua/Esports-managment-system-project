<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];


if ($_SESSION['profession'] !== 'Student') {
    header("Location: dashboard.php");
    exit();
}

//  student profile
$sql = "SELECT sp.*, u.university_name FROM student_profiles sp 
        LEFT JOIN universities u ON sp.university_id = u.university_id 
        WHERE sp.user_id = $user_id";
$student_profile = getSingleRow($conn, $sql);

// existing subscription
$sql = "SELECT * FROM subscriptions WHERE user_id = $user_id AND is_active = 1 ORDER BY end_date DESC LIMIT 1";
$active_subscription = getSingleRow($conn, $sql);

// Handle subscription form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_duration = sanitize($conn, $_POST['plan_duration']);
    $student_id_number = sanitize($conn, $_POST['student_id_number']);
    $payment_method = sanitize($conn, $_POST['payment_method']);
    $transaction_id = sanitize($conn, $_POST['transaction_id']);
    
    
    $amounts = [
        '1_month' => 200,
        '3_months' => 550,
        '6_months' => 1000,
        '1_year' => 1800
    ];
    
    $amount = $amounts[$plan_duration];
    
    // Calculate dates
    $start_date = date('Y-m-d');
    $months = [
        '1_month' => 1,
        '3_months' => 3,
        '6_months' => 6,
        '1_year' => 12
    ];
    
    $end_date = date('Y-m-d', strtotime("+{$months[$plan_duration]} months"));
    
    // Insert subscription
    $sql = "INSERT INTO subscriptions (user_id, plan_duration, amount, payment_method, transaction_id, start_date, end_date) 
            VALUES ($user_id, '$plan_duration', $amount, '$payment_method', '$transaction_id', '$start_date', '$end_date')";
    
    if (executeQuery($conn, $sql)) {
        
        if (!empty($student_id_number)) {
            $sql = "UPDATE student_profiles SET student_id_number = '$student_id_number' WHERE user_id = $user_id";
            executeQuery($conn, $sql);
        }
        
       
        $sql = "INSERT INTO notifications (user_id, title, message, notification_type) 
                VALUES ($user_id, 'Subscription Activated!', 'Your {$months[$plan_duration]} month subscription has been activated successfully.', 'Subscription')";
        executeQuery($conn, $sql);
        
        header("Location: subscription.php?success=Subscription activated successfully!");
        exit();
    }
}

$user = getSingleRow($conn, "SELECT * FROM users WHERE user_id = $user_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription - ESportsHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="top-bar">
                <h1>Student Subscription</h1>
            </div>

            <div class="dashboard-content">
                <?php if ($active_subscription): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Active Subscription</strong><br>
                        Valid until: <?php echo date('F d, Y', strtotime($active_subscription['end_date'])); ?>
                    </div>
                </div>
                <?php endif; ?>

               
                <div class="section">
                    <h2>Choose Your Plan</h2>
                    <div class="pricing-grid">
                        <div class="pricing-card">
                            <div class="pricing-header">
                                <h3>1 Month</h3>
                                <p class="price">৳200</p>
                            </div>
                            <ul class="pricing-features">
                                <li><i class="fas fa-check"></i> Access to all events</li>
                                <li><i class="fas fa-check"></i> Host events</li>
                                <li><i class="fas fa-check"></i> Priority support</li>
                            </ul>
                        </div>
                        <div class="pricing-card popular">
                            <div class="badge-popular">Most Popular</div>
                            <div class="pricing-header">
                                <h3>3 Months</h3>
                                <p class="price">৳550</p>
                                <small>Save ৳50</small>
                            </div>
                            <ul class="pricing-features">
                                <li><i class="fas fa-check"></i> All 1-month features</li>
                                <li><i class="fas fa-check"></i> Exclusive tournaments</li>
                                <li><i class="fas fa-check"></i> Special badges</li>
                            </ul>
                        </div>
                        <div class="pricing-card">
                            <div class="pricing-header">
                                <h3>6 Months</h3>
                                <p class="price">৳1000</p>
                                <small>Save ৳200</small>
                            </div>
                            <ul class="pricing-features">
                                <li><i class="fas fa-check"></i> All 3-month features</li>
                                <li><i class="fas fa-check"></i> Premium support</li>
                                <li><i class="fas fa-check"></i> Profile boost</li>
                            </ul>
                        </div>
                        <div class="pricing-card">
                            <div class="pricing-header">
                                <h3>1 Year</h3>
                                <p class="price">৳1800</p>
                                <small>Save ৳600</small>
                            </div>
                            <ul class="pricing-features">
                                <li><i class="fas fa-check"></i> All 6-month features</li>
                                <li><i class="fas fa-check"></i> VIP status</li>
                                <li><i class="fas fa-check"></i> Free merchandise</li>
                            </ul>
                        </div>
                    </div>
                </div>

               
                <div class="section">
                    <h2>Subscribe Now</h2>
                    <form method="POST" class="subscription-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>University</label>
                                <input type="text" value="<?php echo htmlspecialchars($student_profile['university_name']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Student ID</label>
                                <input type="text" name="student_id_number" placeholder="Enter your student ID" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Select Plan *</label>
                                <select name="plan_duration" required>
                                    <option value="">Choose a plan</option>
                                    <option value="1_month">1 Month - ৳200</option>
                                    <option value="3_months">3 Months - ৳550</option>
                                    <option value="6_months">6 Months - ৳1000</option>
                                    <option value="1_year">1 Year - ৳1800</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Payment Method *</label>
                                <select name="payment_method" required>
                                    <option value="">Select method</option>
                                    <option value="bKash">bKash</option>
                                    <option value="Nagad">Nagad</option>
                                    <option value="Card">Credit/Debit Card</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Transaction ID *</label>
                            <input type="text" name="transaction_id" placeholder="Enter transaction/reference ID" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full">Activate Subscription</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php closeConnection($conn); ?>
