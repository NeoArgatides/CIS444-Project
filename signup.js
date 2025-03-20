document.getElementById("signup-form").addEventListener("submit", function(event) {
    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirm-password").value;
    let errorMessage = document.getElementById("error-message");
    let usernameError = document.getElementById("username-error");
    let passwordLengthError = document.getElementById("password-length-error");

    document.getElementById("username").classList.remove("invalid");
    document.getElementById("password").classList.remove("invalid");
    document.getElementById("confirm-password").classList.remove("invalid");

    if (username.includes(" ")) {
        event.preventDefault(); 
        usernameError.style.display = "block";
        document.getElementById("username").classList.add("invalid");
    } else {
        usernameError.style.display = "none";
    }

    const passwordRegex = /^.{8,}$/;
    if (!passwordRegex.test(password)) {
        event.preventDefault(); 
        passwordLengthError.style.display = "block";
        document.getElementById("password").classList.add("invalid");
    } else {
        passwordLengthError.style.display = "none";
    }

    if (password !== confirmPassword) {
        event.preventDefault();
        errorMessage.style.display = "block";
        document.getElementById("password").classList.add("invalid");
        document.getElementById("confirm-password").classList.add("invalid");
    } else {
        errorMessage.style.display = "none";
    }
});

document.getElementById("reset-button").addEventListener("click", function() {
    document.getElementById("signup-form").reset();
    document.getElementById("error-message").style.display = "none";
    document.getElementById("username-error").style.display = "none";
    document.getElementById("password-length-error").style.display = "none";
    document.getElementById("username").classList.remove("invalid");
    document.getElementById("password").classList.remove("invalid");
    document.getElementById("confirm-password").classList.remove("invalid");
});