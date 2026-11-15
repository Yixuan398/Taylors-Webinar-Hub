document.addEventListener("DOMContentLoaded", function () {

    // Show or hide password
    document.querySelectorAll(".toggle-password").forEach(function (button) {
        button.addEventListener("click", function () {
            const input = document.getElementById(this.dataset.target);

            if (input) {
                const isHidden = input.type === "password";

                input.type = isHidden ? "text" : "password";

                button.setAttribute(
                    "aria-label",
                    isHidden ? "Hide password" : "Show password"
                );

                button.setAttribute(
                    "aria-pressed",
                    String(isHidden)
                );
            }
        });
    });

});
