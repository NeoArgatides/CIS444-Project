document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("login-form");
    const usernameInput = document.getElementById("login-username");
    const passwordInput = document.getElementById("login-password");
    const usernameError = document.getElementById("login-username-error");
    const passwordError = document.getElementById("login-password-error");
    const resetButton = document.getElementById("reset-button");

    loginForm.addEventListener("submit", function (event) {
        let isValid = true;

        if (usernameInput.value.includes(" ")) {
            usernameError.style.display = "block";
            usernameInput.classList.add("invalid");
            isValid = false;
        } else {
            usernameError.style.display = "none";
            usernameInput.classList.remove("invalid");
        }

        if (passwordInput.value.trim() === "") {
            passwordError.style.display = "block";
            passwordInput.classList.add("invalid");
            isValid = false;
        } else {
            passwordError.style.display = "none";
            passwordInput.classList.remove("invalid");
        }

        if (!isValid) {
            event.preventDefault();
        }
    });

    resetButton.addEventListener("click", function () {
        usernameInput.value = "";
        passwordInput.value = "";
        usernameError.style.display = "none";
        passwordError.style.display = "none";
        usernameInput.classList.remove("invalid");
        passwordInput.classList.remove("invalid");
    });
});
