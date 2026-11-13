document.addEventListener("DOMContentLoaded", function () {

    // Show / hide password
    document.querySelectorAll(".toggle-password").forEach(function (button) {
        button.onclick = function () {
            const input = document.getElementById(this.dataset.target);

            if (input) {
                input.type = input.type === "password" ? "text" : "password";
            }
        };
    });

    // Check matching passwords
    const form = document.getElementById("signupForm");

    if (form) {
        form.addEventListener("submit", function (event) {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm-password").value;

            if (password !== confirmPassword) {
                event.preventDefault();
                alert("Passwords do not match.");
            }
        });
    }

});
