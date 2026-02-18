// Main JavaScript for ESportsHub
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    // Close mobile menu when clicking nav links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navMenu.classList.remove('active');
        });
    });
    
    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#') && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Active nav link on scroll
    window.addEventListener('scroll', function() {
        let current = '';
        const sections = document.querySelectorAll('section[id]');
        const pageYOffset = window.pageYOffset;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (pageYOffset >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
    
    // Load statistics
    loadStatistics();
    
    // Button event listeners
    document.getElementById('getStartedBtn')?.addEventListener('click', function() {
        const modal = document.getElementById('signUpModal');
        if (modal) modal.style.display = 'block';
    });
    
    document.getElementById('learnMoreBtn')?.addEventListener('click', function() {
        const tournamentSection = document.querySelector('#tournaments');
        if (tournamentSection) tournamentSection.scrollIntoView({ behavior: 'smooth' });
    });
    
    // Username validation (Adds @ automatically)
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function(e) {
            let value = e.target.value;
            if (value && !value.startsWith('@')) {
                e.target.value = '@' + value;
            }
        });
    }
    
    // Password confirmation
    const signUpForm = document.getElementById('signUpForm');
    if (signUpForm) {
        signUpForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    }
    
    // Profession change handler
    const professionSelect = document.getElementById('profession');
    if (professionSelect) {
        professionSelect.addEventListener('change', function() {
            handleProfessionChange(this.value);
        });
        
        // Load universities and departments
        loadUniversities();
        loadDepartments();
    }

    // --- REAL-TIME NOTIFICATION COUNT UPDATE ---
    // Protity 3 second por por unread count update korbe
    setInterval(() => {
        const notifBadge = document.getElementById('notifCount');
        if (notifBadge) {
            fetch('/api/get_unread_count.php')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.unread > 0) {
                        notifBadge.innerText = data.unread;
                        notifBadge.style.display = 'inline-block'; // Show if count > 0
                    } else {
                        notifBadge.innerText = '';
                        notifBadge.style.display = 'none'; // Hide if 0
                    }
                })
                .catch(err => console.log('Notification fetch failed (silently ignored)'));
        }
    }, 3000);
});

// Load statistics from API
function loadStatistics() {
    fetch('api/get_statistics.php')
        .then(response => response.json())
        .then(data => {
            if (data) {
                animateCounter('totalUsers', data.total_users || 0);
                animateCounter('activeTournaments', data.active_tournaments || 0);
                animateCounter('registeredTeams', data.registered_teams || 0);
                animateCounter('runningEvents', data.running_events || 0);
            }
        })
        .catch(error => console.error('Error loading statistics:', error));
}

// Animate counter
function animateCounter(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element || isNaN(targetValue)) return;
    
    let currentValue = 0;
    const increment = Math.ceil(targetValue / 50) || 1;
    const duration = 2000;
    const stepTime = duration / 50;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= targetValue) {
            element.textContent = targetValue;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(currentValue);
        }
    }, stepTime);
}

// Handle profession selection
function handleProfessionChange(profession) {
    const studentFields = document.getElementById('studentFields');
    const jobHolderFields = document.getElementById('jobHolderFields');
    
    if (!studentFields || !jobHolderFields) return;

    // Hide all conditional fields
    studentFields.style.display = 'none';
    jobHolderFields.style.display = 'none';
    
    // Remove required attributes
    document.getElementById('university')?.removeAttribute('required');
    document.getElementById('company')?.removeAttribute('required');
    
    // Show relevant fields
    if (profession === 'Student') {
        studentFields.style.display = 'block';
        document.getElementById('university')?.setAttribute('required', 'required');
    } else if (profession === 'Job Holder') {
        jobHolderFields.style.display = 'block';
        document.getElementById('company')?.setAttribute('required', 'required');
    }
}

// Load universities from database
function loadUniversities() {
    fetch('api/get_universities.php')
        .then(response => response.json())
        .then(data => {
            const universitySelect = document.getElementById('university');
            if (universitySelect && Array.isArray(data)) {
                universitySelect.innerHTML = '<option value="">Select University</option>';
                data.forEach(university => {
                    const option = document.createElement('option');
                    option.value = university.university_id;
                    option.textContent = university.university_name;
                    universitySelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading universities:', error));
}

// Load departments from database
function loadDepartments() {
    fetch('api/get_departments.php')
        .then(response => response.json())
        .then(data => {
            const departmentSelect = document.getElementById('department');
            if (departmentSelect && Array.isArray(data)) {
                departmentSelect.innerHTML = '<option value="">Select Department</option>';
                data.forEach(department => {
                    const option = document.createElement('option');
                    option.value = department.department_id;
                    option.textContent = department.department_name;
                    departmentSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading departments:', error));
}

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 5000);
}

// Check URL parameters for messages
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('success')) {
    showNotification(decodeURIComponent(urlParams.get('success')), 'success');
}
if (urlParams.get('error')) {
    showNotification(decodeURIComponent(urlParams.get('error')), 'error');
}