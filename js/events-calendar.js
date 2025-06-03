document.addEventListener("DOMContentLoaded", () => {
  console.log("Events calendar loaded, theme handled by main.js")

  // Your existing events calendar code stays here
  // Just remove any theme-related functions
  // Calendar functionality only - no theme code
  const calendarContainer = document.querySelector(".calendar-container")
  if (!calendarContainer) return

  const currentMonthElement = document.getElementById("current-month")
  const calendarDays = document.getElementById("calendar-days")
  const prevMonthBtn = document.querySelector(".prev-month")
  const nextMonthBtn = document.querySelector(".next-month")

  const currentDate = new Date()
  let currentMonth = currentDate.getMonth()
  let currentYear = currentDate.getFullYear()

  function generateCalendar(year, month) {
    if (!calendarDays) return

    calendarDays.innerHTML = ""

    const firstDay = new Date(year, month, 1)
    const lastDay = new Date(year, month + 1, 0)
    const daysInMonth = lastDay.getDate()
    const startingDayOfWeek = firstDay.getDay()

    // Update month display
    if (currentMonthElement) {
      currentMonthElement.textContent = firstDay.toLocaleDateString("en-US", {
        month: "long",
        year: "numeric",
      })
    }

    // Add empty cells for days before the first day of the month
    for (let i = 0; i < startingDayOfWeek; i++) {
      const emptyDay = document.createElement("div")
      emptyDay.className = "calendar-day empty"
      calendarDays.appendChild(emptyDay)
    }

    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
      const dayElement = document.createElement("div")
      dayElement.className = "calendar-day"

      const dayNumber = document.createElement("div")
      dayNumber.className = "calendar-day-number"
      dayNumber.textContent = day
      dayElement.appendChild(dayNumber)

      // Highlight today
      const today = new Date()
      if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
        dayElement.classList.add("today")
      }

      // Add sample events (in a real app, this would come from a database)
      if (hasEventsOnDay(year, month, day)) {
        const eventIndicator = document.createElement("div")
        eventIndicator.className = "calendar-day-event"
        eventIndicator.textContent = "Event"
        dayElement.appendChild(eventIndicator)

        dayElement.addEventListener("click", function () {
          showEventsForDay(year, month, day)
          // Remove selected class from all days
          document.querySelectorAll(".calendar-day").forEach((d) => d.classList.remove("selected"))
          // Add selected class to clicked day
          this.classList.add("selected")
        })
      }

      calendarDays.appendChild(dayElement)
    }
  }

  function hasEventsOnDay(year, month, day) {
    // Sample logic - in a real app, this would check against a database
    return (month === 5 && (day === 15 || day === 22 || day === 28)) || (month === 6 && (day === 5 || day === 12))
  }

  function showEventsForDay(year, month, day) {
    const eventList = document.querySelector(".event-list")
    if (!eventList) return

    const eventTitle = eventList.querySelector("h3")
    if (eventTitle) {
      const date = new Date(year, month, day)
      eventTitle.textContent = `Events for ${date.toLocaleDateString("en-US", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
      })}`
    }

    // In a real app, you would fetch events for this specific day
    // For now, we'll just update the title
  }

  // Navigation event listeners
  if (prevMonthBtn) {
    prevMonthBtn.addEventListener("click", () => {
      currentMonth--
      if (currentMonth < 0) {
        currentMonth = 11
        currentYear--
      }
      generateCalendar(currentYear, currentMonth)
    })
  }

  if (nextMonthBtn) {
    nextMonthBtn.addEventListener("click", () => {
      currentMonth++
      if (currentMonth > 11) {
        currentMonth = 0
        currentYear++
      }
      generateCalendar(currentYear, currentMonth)
    })
  }

  // Event type filter
  const eventTypeFilter = document.getElementById("event-type-filter")
  if (eventTypeFilter) {
    eventTypeFilter.addEventListener("change", function () {
      const selectedType = this.value
      const eventItems = document.querySelectorAll(".event-item")

      eventItems.forEach((item) => {
        const eventType = item.getAttribute("data-type")
        if (selectedType === "all" || eventType === selectedType) {
          item.style.display = "block"
        } else {
          item.style.display = "none"
        }
      })
    })
  }

  // Initialize calendar
  generateCalendar(currentYear, currentMonth)
})
