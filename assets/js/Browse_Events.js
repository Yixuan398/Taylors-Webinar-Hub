
document.addEventListener("DOMContentLoaded", function () {

    const searchForm = document.getElementById("searchForm");
    const searchInput = document.getElementById("searchInput");
    const categoryFilter = document.getElementById("categoryFilter");
    const sortSelect = document.getElementById("sortSelect");
    const eventsGrid = document.getElementById("eventsGrid");
    const eventsCount = document.getElementById("eventsCount");
    const noResults = document.getElementById("noResults");

    const eventCards = Array.from(
        eventsGrid.querySelectorAll(".event-card")
    );

    // Filter and sort events
    function updateEvents() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const selectedCategory = categoryFilter.value;
        const sortOption = sortSelect.value;

        const filteredEvents = eventCards.filter(function (card) {
            const title = card.dataset.title.toLowerCase();
            const description = card.dataset.description.toLowerCase();
            const category = card.dataset.category;

            const matchesSearch =
                title.includes(searchTerm) ||
                description.includes(searchTerm);

            const matchesCategory =
                selectedCategory === "all" ||
                category === selectedCategory;

            return matchesSearch && matchesCategory;
        });

        filteredEvents.sort(function (a, b) {
            if (sortOption === "latest") {
                return b.dataset.date.localeCompare(a.dataset.date);
            }

            if (sortOption === "az") {
                return a.dataset.title.localeCompare(b.dataset.title);
            }

            return a.dataset.date.localeCompare(b.dataset.date);
        });

        eventCards.forEach(function (card) {
            card.remove();
        });

        filteredEvents.forEach(function (card) {
            eventsGrid.appendChild(card);
        });

        eventsCount.textContent =
            filteredEvents.length +
            (filteredEvents.length === 1 ? " event" : " events");

        noResults.hidden = filteredEvents.length !== 0;
    }

    searchForm.addEventListener("submit", function (event) {
        event.preventDefault();
        updateEvents();
    });

    categoryFilter.addEventListener("change", updateEvents);
    sortSelect.addEventListener("change", updateEvents);

});
