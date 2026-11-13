document.addEventListener("DOMContentLoaded", function () {

    const filterButtons = document.querySelectorAll(".filter-button");
    const searchInput = document.getElementById("search-events");
    const eventRows = document.querySelectorAll("#events-body tr");
    const eventsCount = document.getElementById("events-count");

    let activeFilter = "all";

    // Filter and search events
    function updateEvents() {
        const searchText = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        eventRows.forEach(function (row) {
            const status = row.dataset.status;
            const rowText = row.textContent.toLowerCase();

            const matchesFilter =
                activeFilter === "all" || status === activeFilter;

            const matchesSearch = rowText.includes(searchText);

            const shouldShow = matchesFilter && matchesSearch;

            row.hidden = !shouldShow;

            if (shouldShow) {
                visibleCount++;
            }
        });

        eventsCount.textContent =
            "Showing " + visibleCount + " of " + eventRows.length + " events";
    }

    filterButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            activeFilter = button.dataset.filter;

            filterButtons.forEach(function (filterButton) {
                const isActive = filterButton === button;

                filterButton.classList.toggle("active", isActive);
                filterButton.setAttribute("aria-pressed", String(isActive));
            });

            updateEvents();
        });
    });

    searchInput.addEventListener("input", updateEvents);

    // Confirm event actions
    document.querySelectorAll("[data-action='delete']").forEach(function (button) {
        button.addEventListener("click", function () {
            const row = button.closest("tr");
            const title = row.querySelector(".event-title").textContent.trim();

            window.confirm("Delete event: " + title + "?");
        });
    });

});
