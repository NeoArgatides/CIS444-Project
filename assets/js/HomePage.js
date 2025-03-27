document.addEventListener("DOMContentLoaded", () => {
    // Get references to elements
    const modalOverlay = document.getElementById("modal-overlay");
    const loginModal = document.getElementById("login-modal");
    const signupModal = document.getElementById("signup-modal");
  
    const loginLink = document.getElementById("login-link");
    const signupLink = document.getElementById("signup-link");
  
    const closeLoginBtn = document.getElementById("close-login-btn");
    const closeSignupBtn = document.getElementById("close-signup-btn");
  
    const switchToSignup = document.getElementById("switch-to-signup");
    const switchToLogin = document.getElementById("switch-to-login");
  
    // Function to show a modal
    function showModal(modal) {
      modalOverlay.classList.remove("hidden");
      modal.classList.remove("hidden");
    }
  
    // Function to hide all modals
    function hideModals() {
      modalOverlay.classList.add("hidden");
      loginModal.classList.add("hidden");
      signupModal.classList.add("hidden");
    }
  
    // Open Log In modal
    loginLink.addEventListener("click", (e) => {
      e.preventDefault();
      showModal(loginModal);
    });
  
    // Open Sign Up modal
    signupLink.addEventListener("click", (e) => {
      e.preventDefault();
      showModal(signupModal);
    });
  
    // Close modals via close button or overlay click
    closeLoginBtn.addEventListener("click", hideModals);
    closeSignupBtn.addEventListener("click", hideModals);
    modalOverlay.addEventListener("click", hideModals);
  
    // Switch between modals
    if (switchToSignup) {
      switchToSignup.addEventListener("click", (e) => {
        e.preventDefault();
        loginModal.classList.add("hidden");
        signupModal.classList.remove("hidden");
      });
    }
    if (switchToLogin) {
      switchToLogin.addEventListener("click", (e) => {
        e.preventDefault();
        signupModal.classList.add("hidden");
        loginModal.classList.remove("hidden");
      });
    }
  });
  