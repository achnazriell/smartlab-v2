import "./bootstrap";

function togglePassword() {
    const passwordInput = document.getElementById("password");
    const eyeOpen = document.getElementById("eye-open");
    const eyeClosed = document.getElementById("eye-closed");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeOpen.classList.add("hidden");
        eyeClosed.classList.remove("hidden");
    } else {
        passwordInput.type = "password";
        eyeOpen.classList.remove("hidden");
        eyeClosed.classList.add("hidden");
    }
}

document.querySelector("form").addEventListener("submit", function () {
    const btn = document.getElementById("login-btn");
    const text = document.getElementById("login-text");
    const spinner = document.getElementById("login-spinner");

    btn.disabled = true;
    text.classList.add("hidden");
    spinner.classList.remove("hidden");
});
