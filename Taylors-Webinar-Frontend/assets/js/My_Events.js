document.addEventListener("DOMContentLoaded", function () {

    // Event tabs
    const tabButtons = document.querySelectorAll(".tab-button");
    const upcomingEvents = document.getElementById("upcomingEvents");
    const pastEvents = document.getElementById("pastEvents");

    tabButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            tabButtons.forEach(function (tab) {
                tab.classList.remove("active");
            });

            button.classList.add("active");

            const showUpcoming = button.dataset.tab === "upcoming";

            upcomingEvents.hidden = !showUpcoming;
            pastEvents.hidden = showUpcoming;
        });
    });

    // Calendar
    const monthSelect = document.getElementById("calendarMonth");
    const yearSelect = document.getElementById("calendarYear");
    const previousButton = document.getElementById("previousMonth");
    const nextButton = document.getElementById("nextMonth");
    const calendarDays = document.getElementById("calendarDays");
    const selectedDayTitle = document.getElementById("selectedDayTitle");
    const selectedDayEvents = document.getElementById("selectedDayEvents");

    let currentMonth = 10;
    let currentYear = 2026;
    let selectedDate = new Date(2026, 10, 9);

    const calendarEvents = [
        {
            date: "2026-11-09",
            title: "Build Together: Campus Hackathon",
            time: "9:00 am",
            category: "technology"
        },
        {
            date: "2026-11-17",
            title: "Grow a Greener Campus",
            time: "8:30 am",
            category: "community"
        },
        {
            date: "2026-11-22",
            title: "Past Event",
            time: "",
            category: "past"
        }
    ];

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return year + "-" + month + "-" + day;
    }

    function updateSelectedDay() {
        const weekdays = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        selectedDayTitle.textContent =
            weekdays[selectedDate.getDay()] + ", " +
            months[selectedDate.getMonth()] + " " +
            selectedDate.getDate();

        selectedDayEvents.innerHTML = "";

        const matchingEvents = calendarEvents.filter(function (event) {
            return event.date === formatDate(selectedDate);
        });

        matchingEvents.forEach(function (event) {
            const item = document.createElement("div");
            item.className = "calendar-event";

            if (event.category === "community") {
                item.style.borderColor = "#4c8967";
            }

            const title = document.createElement("strong");
            title.textContent = event.title;

            const time = document.createElement("span");
            time.textContent = event.time ? "◷ " + event.time : "";

            item.appendChild(title);
            item.appendChild(time);
            selectedDayEvents.appendChild(item);
        });
    }

    function renderCalendar() {
        monthSelect.value = String(currentMonth);
        yearSelect.value = String(currentYear);

        calendarDays.innerHTML = "";

        const firstDay = new Date(currentYear, currentMonth, 1);
        const startOffset = firstDay.getDay();

        for (let i = 0; i < 35; i++) {
            const date = new Date(
                currentYear,
                currentMonth,
                i - startOffset + 1
            );

            const button = document.createElement("button");
            button.type = "button";
            button.className = "calendar-day";
            button.textContent = date.getDate();

            if (date.getMonth() !== currentMonth) {
                button.classList.add("other-month");
            }

            if (formatDate(date) === formatDate(selectedDate)) {
                button.classList.add("selected");
            }

            const event = calendarEvents.find(function (item) {
                return item.date === formatDate(date);
            });

            if (event) {
                button.classList.add("has-event");

                if (event.category === "community") {
                    button.classList.add("community-event");
                }

                if (event.category === "past") {
                    button.classList.add("past-event");
                }
            }

            button.addEventListener("click", function () {
                selectedDate = new Date(
                    date.getFullYear(),
                    date.getMonth(),
                    date.getDate()
                );

                currentMonth = selectedDate.getMonth();
                currentYear = selectedDate.getFullYear();

                renderCalendar();
            });

            calendarDays.appendChild(button);
        }

        updateSelectedDay();
    }

    function changeMonth(amount) {
        const nextDate = new Date(currentYear, currentMonth + amount, 1);

        currentMonth = nextDate.getMonth();
        currentYear = nextDate.getFullYear();

        selectedDate = new Date(currentYear, currentMonth, 1);

        renderCalendar();
    }

    previousButton.addEventListener("click", function () {
        changeMonth(-1);
    });

    nextButton.addEventListener("click", function () {
        changeMonth(1);
    });

    monthSelect.addEventListener("change", function () {
        currentMonth = Number(monthSelect.value);
        selectedDate = new Date(currentYear, currentMonth, 1);
        renderCalendar();
    });

    yearSelect.addEventListener("change", function () {
        currentYear = Number(yearSelect.value);
        selectedDate = new Date(currentYear, currentMonth, 1);
        renderCalendar();
    });

    renderCalendar();

});
