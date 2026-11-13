document.addEventListener("DOMContentLoaded", function () {

    // Edit profile
    const editButton = document.getElementById("editProfileButton");
    const cancelButton = document.getElementById("cancelEditButton");
    const profileForm = document.getElementById("profileForm");
    const formActions = document.getElementById("formActions");

    const editableFields = [
        "userName",
        "dateOfBirth",
        "firstName",
        "lastName",
        "email",
        "mobileNumber"
    ];

    let originalValues = {};

    function setEditing(editing) {
        editableFields.forEach(function (id) {
            document.getElementById(id).disabled = !editing;
        });

        formActions.hidden = !editing;
        editButton.hidden = editing;
    }

    editButton.addEventListener("click", function () {
        originalValues = {};

        editableFields.forEach(function (id) {
            originalValues[id] = document.getElementById(id).value;
        });

        setEditing(true);
    });

    cancelButton.addEventListener("click", function () {
        editableFields.forEach(function (id) {
            document.getElementById(id).value = originalValues[id];
        });

        setEditing(false);
    });

    profileForm.addEventListener("submit", function (event) {
        event.preventDefault();

        // Front-end preview only; PHP will save the data later.
        document.getElementById("profileDisplayName").textContent =
            document.getElementById("userName").value;

        document.getElementById("profileDisplayEmail").textContent =
            document.getElementById("email").value;

        document.querySelector(".user-name").textContent =
            document.getElementById("userName").value;

        setEditing(false);
    });

    // Profile photo
    const changePhotoButton = document.getElementById("changePhotoButton");
    const photoInput = document.getElementById("profilePhotoInput");
    const profileImage = document.getElementById("profileImage");
    const profileInitials = document.getElementById("profileInitials");

    changePhotoButton.addEventListener("click", function () {
        photoInput.click();
    });

    photoInput.addEventListener("change", function () {
        const file = photoInput.files[0];

        if (!file || !file.type.startsWith("image/")) {
            return;
        }

        const reader = new FileReader();

        reader.onload = function () {
            profileImage.src = reader.result;
            profileImage.hidden = false;
            profileInitials.hidden = true;
        };

        reader.readAsDataURL(file);
    });

    // Change password dialog
    const passwordDialog = document.getElementById("passwordDialog");
    const changePasswordButton = document.getElementById("changePasswordButton");
    const closePasswordButton = document.getElementById("closePasswordDialog");
    const cancelPasswordButton = document.getElementById("cancelPasswordButton");
    const passwordForm = document.getElementById("passwordForm");
    const passwordError = document.getElementById("passwordError");

    changePasswordButton.addEventListener("click", function () {
        passwordForm.reset();
        passwordError.hidden = true;
        passwordDialog.showModal();
    });

    function closePasswordDialog() {
        passwordDialog.close();
    }

    closePasswordButton.addEventListener("click", closePasswordDialog);
    cancelPasswordButton.addEventListener("click", closePasswordDialog);

    passwordForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const newPassword = document.getElementById("newPassword").value;
        const confirmPassword = document.getElementById("confirmPassword").value;

        if (newPassword !== confirmPassword) {
            passwordError.hidden = false;
            return;
        }

        passwordError.hidden = true;

        // Password update requires PHP backend verification.
    });

});
