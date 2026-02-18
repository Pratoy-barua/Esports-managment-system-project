<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-gamepad"></i>
            <span>ESports<strong>Hub</strong></span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item">
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

        <?php if ($_SESSION['profession'] === 'Student'): ?>
            <a href="subscription.php" class="nav-item">
                <i class="fas fa-star"></i>
                <span>Subscription</span>
            </a>

            <?php if (isset($_SESSION['has_subscription']) && $_SESSION['has_subscription']): ?>
                <a href="events.php" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events Hub</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <a href="tickets.php" class="nav-item">
            <i class="fas fa-ticket-alt"></i>
            <span>Support</span>
        </a>

        <a href="products.php" class="nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span>Products</span>
        </a>

        <a href="notifications.php" class="nav-item">
            <i class="fas fa-bell"></i>
            <span>Notifications</span>
        </a>

        <a href="messages.php" class="nav-item">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
        </a>

        <a href="../auth/logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>
