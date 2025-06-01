// Events Calendar Functionality
document.addEventListener("DOMContentLoaded", () => {
  const currentMonthElement = document.getElementById("current-month")
  const calendarDays = document.getElementById("calendar-days")
  const eventsContainer = document.getElementById("events-container")
  const eventTypeFilter = document.getElementById("event-type-filter")

  const currentDate = new Date()
  let selectedDate = new Date()

  // Sample events data (in a real app, this would come from the server)
  const sampleEvents = [
    {
      date: new Date().toISOString().split("T")[0],
      title: "Guided Tour: Modern Masterpieces",
      time: "2:00 PM - 3:30 PM",
      location: "Main Gallery",
      type: "tour",
      description: "Join our expert curator for a guided tour of our Modern Masterpieces exhibition.",
    },
    {
      date: new Date(Date.now() + 86400000).toISOString().split("T")[0],
      title: "Artist Talk: Contemporary Expressions",
      time: "6:30 PM - 8:00 PM",
      location: "Auditorium",
      type: "talk",
      description: "Meet the artist behind our latest contemporary exhibition.",
    },
    {
      date: new Date(Date.now() + 172800000).toISOString().split("T")[0],
      title: "Family Workshop: Art Exploration",
      time: "10:00 AM - 12:00 PM",
      location: "Education Center",
      type: "workshop",
      description: "A hands-on art workshop for families with children ages 5-12.",
    },
  ]

  function renderCalendar() {
    const year = currentDate.getFullYear()
    const month = currentDate.getMonth()

    // Update month display
    currentMonthElement.textContent = new Intl.DateTimeFormat("en-US", {
      month: "long",
      year: "numeric",
    }).format(currentDate)

    // Clear calendar
    calendarDays.innerHTML = ""

    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1)
    const lastDay = new Date(year, month + 1, 0)
    const startDate = new Date(firstDay)
    startDate.setDate(startDate.getDate() - firstDay.getDay())

    // Generate calendar days
    for (let i = 0; i < 42; i++) {
      const date = new Date(startDate)
      date.setDate(startDate.getDate() + i)

      const dayElement = document.createElement("div")
      dayElement.className = "calendar-day"
      dayElement.textContent = date.getDate()

      // Add classes for styling
      if (date.getMonth() !== month) {
        dayElement.classList.add("other-month")
      }

      if (date.toDateString() === new Date().toDateString()) {
        dayElement.classList.add("today")
      }

      if (date.toDateString() === selectedDate.toDateString()) {
        dayElement.classList.add("selected")
      }

      // Check if date has events
      const dateString = date.toISOString().split("T")[0]
      const hasEvents = sampleEvents.some((event) => event.date === dateString)
      if (hasEvents) {
        dayElement.classList.add("has-events")
      }

      // Add click handler
      dayElement.addEventListener("click", () => {
        selectedDate = new Date(date)
        renderCalendar()
        displayEventsForDate(selectedDate)
      })

      calendarDays.appendChild(dayElement)
    }
  }

  function displayEventsForDate(date) {
    const dateString = date.toISOString().split("T")[0]
    const eventsForDate = sampleEvents.filter((event) => event.date === dateString)

    // Update events list header
    const eventListHeader = document.querySelector(".event-list h3")
    eventListHeader.textContent = `Events for ${date.toLocaleDateString("en-US", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    })}`

    // Clear and populate events
    eventsContainer.innerHTML = ""

    if (eventsForDate.length > 0) {
      eventsForDate.forEach((event) => {
        const eventElement = document.createElement("div")
        eventElement.className = `event-item`
        eventElement.setAttribute("data-type", event.type)

        eventElement.innerHTML = `
                    <div class="event-time">${event.time}</div>
                    <div class="event-details">
                        <h4>${event.title}</h4>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> ${event.location}</p>
                        <p>${event.description}</p>
                        <a href="event-detail.php" class="btn btn-secondary">Learn More</a>
                    </div>
                `

        eventsContainer.appendChild(eventElement)
      })
    } else {
      eventsContainer.innerHTML = '<div class="no-events"><p>No events scheduled for this date.</p></div>'
    }
  }

  // Event listeners for month navigation
  document.querySelector(".prev-month").addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() - 1)
    renderCalendar()
  })

  document.querySelector(".next-month").addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() + 1)
    renderCalendar()
  })

  // Event type filter
  if (eventTypeFilter) {
    eventTypeFilter.addEventListener("change", (e) => {
      const filterValue = e.target.value
      const eventItems = document.querySelectorAll(".event-item")

      eventItems.forEach((item) => {
        if (filterValue === "all" || item.getAttribute("data-type") === filterValue) {
          item.style.display = "flex"
        } else {
          item.style.display = "none"
        }
      })
    })
  }

  // Initialize calendar
  renderCalendar()
  displayEventsForDate(selectedDate)
})
