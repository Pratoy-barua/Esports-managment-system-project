<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESports Tournament & Team Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/modal.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-gamepad"></i>
                <span>ESports<strong>Hub</strong></span>
            </div>
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link active">Home</a></li>
                <li><a href="#tournaments" class="nav-link">Tournaments</a></li>
                <li><a href="#teams" class="nav-link">Teams</a></li>
                <li><a href="#products" class="nav-link">Products</a></li>
                <li><a href="#" class="btn-signin" id="openSignIn">Sign In</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to ESports<span>Hub</span></h1>
            <p class="hero-subtitle">The Ultimate Platform for Tournament Management, Team Building & Gaming Excellence</p>
            <div class="hero-buttons">
                <button class="btn btn-primary" id="getStartedBtn">Get Started</button>
                <button class="btn btn-secondary" id="learnMoreBtn">Learn More</button>
            </div>
        </div>
        <div class="hero-stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-content">
                    <h3 id="totalUsers">0</h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                <div class="stat-content">
                    <h3 id="activeTournaments">0</h3>
                    <p>Active Tournaments</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="stat-content">
                    <h3 id="registeredTeams">0</h3>
                    <p>Registered Teams</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-content">
                    <h3 id="runningEvents">0</h3>
                    <p>Running Events</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="tournaments">
        <div class="container">
            <h2 class="section-title">Why Choose ESportsHub?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-trophy feature-icon"></i>
                    <h3>Tournament Management</h3>
                    <p>Create, manage and participate in professional esports tournaments with ease</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users-cog feature-icon"></i>
                    <h3>Team Building</h3>
                    <p>Build your dream team and compete with players from universities nationwide</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-graduation-cap feature-icon"></i>
                    <h3>Student Benefits</h3>
                    <p>Exclusive subscription plans and university-based events for students</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-calendar-check feature-icon"></i>
                    <h3>Host Events</h3>
                    <p>Apply to host your own tournaments with admin approval system</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bell feature-icon"></i>
                    <h3>Real-time Notifications</h3>
                    <p>Stay updated with instant notifications about tournaments and events</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shopping-cart feature-icon"></i>
                    <h3>Gaming Products</h3>
                    <p>Browse and purchase gaming merchandise and accessories</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>ESportsHub</h4>
                    <p>Your ultimate esports management platform</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#tournaments">Tournaments</a></li>
                        <li><a href="#teams">Teams</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#" id="privacyLink">Privacy Policy</a></li>
                        <li><a href="#" id="termsLink">Terms & Conditions</a></li>
                        <li><a href="#" id="feedbackLink">Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Connect</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-discord"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 ESportsHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Sign In Modal -->
    <div id="signInModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeSignIn">&times;</span>
            <h2>Sign In</h2>
            <form id="signInForm" method="POST" action="auth/login.php">
                <div class="form-group">
                    <label for="loginUsername">Username or Email</label>
                    <input type="text" id="loginUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Sign In</button>
                <p class="form-footer">Don't have an account? <a href="#" id="openSignUpLink">Sign Up</a></p>
            </form>
        </div>
    </div>

    <!-- Sign Up Modal -->
    <div id="signUpModal" class="modal">
        <div class="modal-content modal-large">
            <span class="close" id="closeSignUp">&times;</span>
            <h2>Create Account</h2>
            <form id="signUpForm" method="POST" action="auth/register.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="fullName">Full Name *</label>
                        <input type="text" id="fullName" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" placeholder="@username" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender *</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dob">Date of Birth *</label>
                        <input type="date" id="dob" name="date_of_birth" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="profession">Profession *</label>
                    <select id="profession" name="profession" required>
                        <option value="">Select Profession</option>
                        <option value="Student">Student</option>
                        <option value="Job Holder">Job Holder</option>
                        <option value="Freelancer">Freelancer</option>
                        <option value="Entrepreneur">Entrepreneur</option>
                        <option value="Content Creator">Content Creator</option>
                        <option value="None">None of the Above</option>
                    </select>
                </div>

                <!-- Conditional Fields for Student -->
                <div id="studentFields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="university">University *</label>
                            <select id="university" name="university_id">
                                <option value="">Select University</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department">Department</label>
                            <select id="department" name="department_id">
                                <option value="">Select Department</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Conditional Fields for Job Holder -->
                <div id="jobHolderFields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company">Company Name *</label>
                            <input type="text" id="company" name="company_name">
                        </div>
                        <div class="form-group">
                            <label for="designation">Designation</label>
                            <input type="text" id="designation" name="designation">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password *</label>
                        <input type="password" id="confirmPassword" name="confirm_password" required>
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the Terms & Conditions and Privacy Policy</label>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Create Account</button>
                <p class="form-footer">Already have an account? <a href="#" id="openSignInLink">Sign In</a></p>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/modal.js"></script>
</body>
</html>
