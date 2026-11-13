document.addEventListener("DOMContentLoaded", function () {

    const params = new URLSearchParams(window.location.search);
    const isCreateMode = params.get("mode") === "create";

    const form = document.getElementById("eventForm");
    const formHeading = document.getElementById("form-heading");
    const formDescription = document.getElementById("form-description");
    const saveButton = document.getElementById("save-button");

    const publishCheckbox = document.getElementById("publish-event");
    const publishDescription = document.getElementById("publish-description");
    const statusBadge = document.getElementById("status-badge");
    const statusText = document.getElementById("status-text");

    // Set up create or edit mode
    if (isCreateMode) {
        form.reset();

        document.querySelectorAll(
            "#eventForm input:not([type='checkbox']), #eventForm textarea"
        ).forEach(function (field) {
            field.value = "";
        });

        document.getElementById("event-category").value = "";

        formHeading.textContent = "Add event";
        formDescription.textContent =
            "Enter the details students will see on your event page.";

        saveButton.textContent = "Create event";
    }

    // Update publish switch
    function updatePublishStatus() {
        const isPublished = publishCheckbox.checked;

        publishDescription.textContent = isPublished
            ? "On — this event will be visible to students."
            : "Off — saved as a draft and hidden from students.";

        statusText.textContent = isPublished ? "Published" : "Draft";

        statusBadge.classList.toggle("published", isPublished);
        statusBadge.classList.toggle("draft", !isPublished);
    }

    publishCheckbox.addEventListener("change", updatePublishStatus);
    updatePublishStatus();

});
