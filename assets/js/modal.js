// Modal Management
document.addEventListener('DOMContentLoaded', function() {
    // Get modals
    const signInModal = document.getElementById('signInModal');
    const signUpModal = document.getElementById('signUpModal');
    
    // Get buttons that open modals
    const openSignInBtns = document.querySelectorAll('#openSignIn, #openSignInLink');
    const openSignUpBtns = document.querySelectorAll('#openSignUpLink');
    
    // Get close buttons
    const closeSignIn = document.getElementById('closeSignIn');
    const closeSignUp = document.getElementById('closeSignUp');
    
    // Open Sign In Modal
    openSignInBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            signUpModal.style.display = 'none';
            signInModal.style.display = 'block';
        });
    });
    
    // Open Sign Up Modal
    openSignUpBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            signInModal.style.display = 'none';
            signUpModal.style.display = 'block';
        });
    });
    
    // Close Sign In Modal
    if (closeSignIn) {
        closeSignIn.addEventListener('click', function() {
            signInModal.style.display = 'none';
        });
    }
    
    // Close Sign Up Modal
    if (closeSignUp) {
        closeSignUp.addEventListener('click', function() {
            signUpModal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === signInModal) {
            signInModal.style.display = 'none';
        }
        if (event.target === signUpModal) {
            signUpModal.style.display = 'none';
        }
    });
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            signInModal.style.display = 'none';
            signUpModal.style.display = 'none';
        }
    });
    
    // Check if should open modal on page load
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('login') === 'required') {
        signInModal.style.display = 'block';
    }
    if (urlParams.get('register') === 'true') {
        signUpModal.style.display = 'block';
    }
});

// Modal utility functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function closeAllModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
    });
}
