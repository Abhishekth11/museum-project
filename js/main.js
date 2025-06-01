document.addEventListener("DOMContentLoaded", () => {
  // Theme toggle functionality
  const themeToggleBtn = document.querySelector(".theme-toggle")
  const htmlElement = document.documentElement
  const storedTheme = localStorage.getItem("theme") || "light-theme"

  // Set initial theme from localStorage
  document.body.className = storedTheme

  themeToggleBtn.addEventListener("click", () => {
    if (document.body.classList.contains("light-theme")) {
      document.body.classList.remove("light-theme")
      document.body.classList.add("dark-theme")
      localStorage.setItem("theme", "dark-theme")
      // Set cookie for server-side theme detection
      document.cookie = "theme=dark-theme; path=/; max-age=31536000"
    } else {
      document.body.classList.remove("dark-theme")
      document.body.classList.add("light-theme")
      localStorage.setItem("theme", "light-theme")
      // Set cookie for server-side theme detection
      document.cookie = "theme=light-theme; path=/; max-age=31536000"
    }
  })

  // Mobile menu functionality
  const menuToggleBtn = document.querySelector(".menu-toggle")

  if (menuToggleBtn) {
    menuToggleBtn.addEventListener("click", () => {
      document.body.classList.toggle("menu-open")
    })
  }

  // Search overlay functionality
  const searchToggleBtn = document.querySelector(".search-toggle")
  const searchCloseBtn = document.querySelector(".search-close")
  const searchOverlay = document.querySelector(".search-overlay")

  if (searchToggleBtn && searchOverlay && searchCloseBtn) {
    searchToggleBtn.addEventListener("click", () => {
      searchOverlay.classList.add("active")
      setTimeout(() => {
        searchOverlay.querySelector("input").focus()
      }, 100)
    })

    searchCloseBtn.addEventListener("click", () => {
      searchOverlay.classList.remove("active")
    })

    // Close search with Escape key
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && searchOverlay.classList.contains("active")) {
        searchOverlay.classList.remove("active")
      }
    })
  }

  // Collection slider functionality
  const collectionSlider = document.querySelector(".collection-slider")

  if (collectionSlider) {
    const prevBtn = document.querySelector(".prev-btn")
    const nextBtn = document.querySelector(".next-btn")
    const dots = document.querySelectorAll(".dot")
    let currentSlide = 0
    const totalSlides = document.querySelectorAll(".collection-item").length

    function showSlide(index) {
      // For simplicity, we're using CSS grid for the layout
      // In a real implementation, you'd adjust the slider position
      currentSlide = index

      // Update dots
      dots.forEach((dot, i) => {
        dot.classList.toggle("active", i === index)
      })
    }

    if (prevBtn && nextBtn) {
      prevBtn.addEventListener("click", () => {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides
        showSlide(currentSlide)
      })

      nextBtn.addEventListener("click", () => {
        currentSlide = (currentSlide + 1) % totalSlides
        showSlide(currentSlide)
      })
    }

    dots.forEach((dot, index) => {
      dot.addEventListener("click", () => {
        showSlide(index)
      })
    })
  }

  // Newsletter form submission with AJAX
  const newsletterForm = document.getElementById("newsletter-form")
  if (newsletterForm) {
    newsletterForm.addEventListener("submit", function (e) {
      e.preventDefault()

      const emailInput = this.querySelector('input[type="email"]')
      const messageContainer = document.getElementById("newsletter-message")
      const submitButton = this.querySelector('button[type="submit"]')

      // Disable button and show loading state
      submitButton.disabled = true
      submitButton.textContent = "Subscribing..."

      // Get form data
      const formData = new FormData(this)

      // Send AJAX request
      fetch("api/subscribe.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          // Show message
          messageContainer.textContent = data.message
          messageContainer.style.color = data.success ? "green" : "red"

          // Reset form if successful
          if (data.success) {
            emailInput.value = ""
          }

          // Reset button
          submitButton.disabled = false
          submitButton.textContent = "Subscribe"

          // Clear message after 5 seconds
          setTimeout(() => {
            messageContainer.textContent = ""
          }, 5000)
        })
        .catch((error) => {
          console.error("Error:", error)
          messageContainer.textContent = "An error occurred. Please try again."
          messageContainer.style.color = "red"

          // Reset button
          submitButton.disabled = false
          submitButton.textContent = "Subscribe"
        })
    })
  }

  // Exhibition filter functionality
  const filterTabs = document.querySelectorAll(".filter-tab")
  if (filterTabs.length > 0) {
    filterTabs.forEach((tab) => {
      tab.addEventListener("click", function () {
        // Remove active class from all tabs
        filterTabs.forEach((t) => t.classList.remove("active"))

        // Add active class to clicked tab
        this.classList.add("active")

        // Show corresponding exhibitions
        const filter = this.getAttribute("data-filter")
        const allGrids = document.querySelectorAll(".exhibitions-grid")

        allGrids.forEach((grid) => {
          grid.classList.add("hidden")
        })

        const targetGrid = document.getElementById(`${filter}-exhibitions`)
        if (targetGrid) {
          targetGrid.classList.remove("hidden")
        }
      })
    })

    // Category filter functionality
    const categoryFilter = document.getElementById("category-filter")
    if (categoryFilter) {
      categoryFilter.addEventListener("change", function () {
        const category = this.value
        const exhibitionCards = document.querySelectorAll(".exhibition-card")

        exhibitionCards.forEach((card) => {
          if (category === "all" || card.getAttribute("data-category").includes(category)) {
            card.style.display = "block"
          } else {
            card.style.display = "none"
          }
        })
      })
    }
  }

  // Events calendar functionality
  const calendarDays = document.getElementById("calendar-days")
  if (calendarDays) {
    // Simple calendar generation for demonstration
    // In a real app, you'd use a more sophisticated calendar system
    function generateCalendar(year, month) {
      calendarDays.innerHTML = ""

      const date = new Date(year, month, 1)
      const lastDay = new Date(year, month + 1, 0).getDate()
      const firstDayIndex = date.getDay()

      // Update month title
      const currentMonthElement = document.getElementById("current-month")
      if (currentMonthElement) {
        currentMonthElement.textContent = date.toLocaleDateString("en-US", {
          month: "long",
          year: "numeric",
        })
      }

      // Add empty cells for days before the 1st of the month
      for (let i = 0; i < firstDayIndex; i++) {
        const emptyDay = document.createElement("div")
        emptyDay.className = "calendar-day empty"
        calendarDays.appendChild(emptyDay)
      }

      // Add days of the month
      for (let i = 1; i <= lastDay; i++) {
        const dayElement = document.createElement("div")
        dayElement.className = "calendar-day"

        const dayNumber = document.createElement("div")
        dayNumber.className = "calendar-day-number"
        dayNumber.textContent = i

        dayElement.appendChild(dayNumber)

        // Mark today
        const today = new Date()
        if (year === today.getFullYear() && month === today.getMonth() && i === today.getDate()) {
          dayElement.classList.add("today")
        }

        // Add sample events - in a real app, these would come from a database
        if ((month === 5 && i === 15) || (month === 5 && i === 22)) {
          const eventIndicator = document.createElement("div")
          eventIndicator.className = "calendar-day-event"
          eventIndicator.textContent = "Events"
          dayElement.appendChild(eventIndicator)

          // Add click event to show events for this day
          dayElement.addEventListener("click", function () {
            document.querySelectorAll(".calendar-day").forEach((day) => {
              day.classList.remove("selected")
            })
            this.classList.add("selected")

            // Update event list title (hardcoded for demo)
            const eventListTitle = document.querySelector(".event-list h3")
            if (eventListTitle) {
              eventListTitle.textContent = `Events for June ${i}, 2024`
            }
          })
        }

        calendarDays.appendChild(dayElement)
      }
    }

    // Initial calendar setup (June 2024)
    generateCalendar(2024, 5) // Month is 0-based (5 = June)

    // Month navigation
    const prevMonthBtn = document.querySelector(".prev-month")
    const nextMonthBtn = document.querySelector(".next-month")
    let currentMonth = 5 // June
    let currentYear = 2024

    if (prevMonthBtn && nextMonthBtn) {
      prevMonthBtn.addEventListener("click", () => {
        currentMonth--
        if (currentMonth < 0) {
          currentMonth = 11
          currentYear--
        }
        generateCalendar(currentYear, currentMonth)
      })

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
        const eventType = this.value
        const eventItems = document.querySelectorAll(".event-item")

        eventItems.forEach((item) => {
          if (eventType === "all" || item.getAttribute("data-type") === eventType) {
            item.style.display = "grid"
          } else {
            item.style.display = "none"
          }
        })
      })
    }
  }
})
