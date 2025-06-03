// Global theme management system
window.ThemeManager = (() => {
  let isInitialized = false
  let currentTheme = "light-theme"

  // Debug logging
  function log(message) {
    if (window.location.search.includes("debug=theme")) {
      console.log("[ThemeManager]", message)
    }
  }

  // Get theme from storage
  function getStoredTheme() {
    // Priority: localStorage > cookie > default
    let theme = localStorage.getItem("theme")
    if (!theme) {
      const cookieMatch = document.cookie.match(/theme=([^;]+)/)
      theme = cookieMatch ? cookieMatch[1] : "light-theme"
    }
    return ["light-theme", "dark-theme"].includes(theme) ? theme : "light-theme"
  }

  // Save theme to storage
  function saveTheme(theme) {
    localStorage.setItem("theme", theme)
    document.cookie = `theme=${theme}; path=/; max-age=31536000; SameSite=Lax`
    log(`Theme saved: ${theme}`)
  }

  // Apply theme to DOM
  function applyTheme(theme) {
    log(`Applying theme: ${theme}`)

    // Remove existing theme classes
    document.body.classList.remove("light-theme", "dark-theme")
    document.documentElement.classList.remove("light-theme", "dark-theme")

    // Add new theme class
    document.body.classList.add(theme)
    document.documentElement.classList.add(theme)
    document.documentElement.setAttribute("data-theme", theme)

    currentTheme = theme
    updateToggleButton()

    // Dispatch event for other components
    window.dispatchEvent(
      new CustomEvent("themeChanged", {
        detail: { theme: theme },
      }),
    )
<<<<<<< HEAD
=======

    // Call observers
    notifyObservers("change", theme)
>>>>>>> feature-update
  }

  // Update toggle button icons
  function updateToggleButton() {
    const toggleBtn = document.querySelector("[data-theme-toggle]")
    if (!toggleBtn) {
      log("Toggle button not found")
      return
    }

    const sunIcon = toggleBtn.querySelector(".fa-sun")
    const moonIcon = toggleBtn.querySelector(".fa-moon")

    if (sunIcon && moonIcon) {
      if (currentTheme === "dark-theme") {
        sunIcon.style.display = "inline-block"
        moonIcon.style.display = "none"
      } else {
        sunIcon.style.display = "none"
        moonIcon.style.display = "inline-block"
      }
      log(`Icons updated for ${currentTheme}`)
    }
  }

  // Toggle theme
  function toggleTheme() {
    const newTheme = currentTheme === "light-theme" ? "dark-theme" : "light-theme"
    log(`Toggling from ${currentTheme} to ${newTheme}`)
    applyTheme(newTheme)
    saveTheme(newTheme)
  }

  // Initialize theme system
  function init() {
    if (isInitialized) {
      log("Already initialized")
      return
    }

    log("Initializing theme system...")

    // Get and apply stored theme
    currentTheme = getStoredTheme()
    applyTheme(currentTheme)

    // Set up toggle button
    const toggleBtn = document.querySelector("[data-theme-toggle]")
    if (toggleBtn) {
      // Remove existing listeners
      const newToggleBtn = toggleBtn.cloneNode(true)
      toggleBtn.parentNode.replaceChild(newToggleBtn, toggleBtn)

      // Add new listener
      newToggleBtn.addEventListener("click", (e) => {
        e.preventDefault()
        e.stopPropagation()
        toggleTheme()
      })

      log("Toggle button initialized")
    } else {
      log("ERROR: Toggle button not found!")
    }

    // Listen for storage changes from other tabs
    window.addEventListener("storage", (e) => {
      if (e.key === "theme" && e.newValue) {
        log(`Storage change detected: ${e.newValue}`)
        applyTheme(e.newValue)
      }
    })

    isInitialized = true
    log("Theme system initialized successfully")
  }

<<<<<<< HEAD
=======
  // Observer pattern
  let observers = []

  function addObserver(observer) {
    observers.push(observer)
  }

  function removeObserver(observer) {
    observers = observers.filter((obs) => obs !== observer)
  }

  function notifyObservers(event, theme) {
    observers.forEach((observer) => observer(event, theme))
  }

>>>>>>> feature-update
  // Public API
  return {
    init: init,
    toggle: toggleTheme,
    apply: applyTheme,
    getCurrent: () => currentTheme,
    isInitialized: () => isInitialized,
<<<<<<< HEAD
=======
    addObserver: addObserver,
    removeObserver: removeObserver,
    setTheme: applyTheme, // Added setTheme alias for applyTheme
>>>>>>> feature-update
  }
})()

// Initialize immediately when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM loaded, initializing theme...")
  window.ThemeManager.init()
})

// Fallback initialization for pages that might load scripts differently
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    if (!window.ThemeManager.isInitialized()) {
      console.log("Fallback theme initialization")
      window.ThemeManager.init()
    }
  })
} else {
  // DOM already loaded
  if (!window.ThemeManager.isInitialized()) {
    console.log("Immediate theme initialization")
    window.ThemeManager.init()
  }
}

// Rest of main.js functionality
document.addEventListener("DOMContentLoaded", () => {
<<<<<<< HEAD
  // Simple theme toggle functionality
  const themeToggleBtn = document.querySelector(".theme-toggle")

  if (themeToggleBtn) {
    // Get current theme from body class or default to light
    let currentTheme = document.body.classList.contains("dark-theme") ? "dark-theme" : "light-theme"

    // Update icons based on current theme
    updateThemeIcons(currentTheme)

    themeToggleBtn.addEventListener("click", () => {
      // Toggle theme
      if (currentTheme === "light-theme") {
        currentTheme = "dark-theme"
        document.body.className = "dark-theme"
      } else {
        currentTheme = "light-theme"
        document.body.className = "light-theme"
      }

      // Update icons
      updateThemeIcons(currentTheme)

      // Save to cookie
      document.cookie = `theme=${currentTheme}; path=/; max-age=31536000`

      console.log("Theme changed to:", currentTheme)
    })
  }

  function updateThemeIcons(theme) {
    const sunIcon = document.querySelector(".theme-toggle .fa-sun")
    const moonIcon = document.querySelector(".theme-toggle .fa-moon")

    if (sunIcon && moonIcon) {
      if (theme === "dark-theme") {
        sunIcon.style.display = "inline-block"
        moonIcon.style.display = "none"
      } else {
        sunIcon.style.display = "none"
        moonIcon.style.display = "inline-block"
      }
    }
=======
  // Ensure theme manager is initialized
  if (window.ThemeManager && !window.ThemeManager.isInitialized()) {
    window.ThemeManager.init()
  }

  // Add theme change observer for custom functionality
  if (window.ThemeManager) {
    window.ThemeManager.addObserver((event, theme) => {
      console.log(`Theme ${event}:`, theme)

      // Custom theme change handling can go here
      if (event === "change") {
        // Update any theme-specific elements
        updateThemeSpecificElements(theme)
      }
    })
>>>>>>> feature-update
  }

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

<<<<<<< HEAD
=======
/**
 * Update theme-specific elements when theme changes
 */
function updateThemeSpecificElements(theme) {
  // Update meta theme-color
  const themeColorMeta = document.querySelector('meta[name="theme-color"]')
  if (themeColorMeta) {
    themeColorMeta.content = theme === "dark-theme" ? "#121212" : "#ffffff"
  }

  // Update any charts, maps, or third-party components that need theme updates
  // Example: Update chart colors, map styles, etc.

  // Dispatch event for other components to listen to
  window.dispatchEvent(
    new CustomEvent("themeElementsUpdate", {
      detail: { theme },
    }),
  )
}

>>>>>>> feature-update
// Legacy function support for backward compatibility
function initializeTheme() {
  console.log("Legacy initializeTheme called, delegating to ThemeManager")
  window.ThemeManager.init()
}

function toggleTheme() {
  console.log("Legacy toggleTheme called, delegating to ThemeManager")
  window.ThemeManager.toggle()
}

function applyTheme(theme) {
  console.log("Legacy applyTheme called, delegating to ThemeManager")
  window.ThemeManager.apply(theme)
}
<<<<<<< HEAD
=======

// Global theme utilities for backward compatibility
window.toggleTheme = () => window.ThemeManager?.toggle()
window.setTheme = (theme) => window.ThemeManager?.setTheme(theme)
window.getCurrentTheme = () => window.ThemeManager?.getCurrent()

// Add this to the end of your main.js file to ensure search functionality is initialized

// Ensure search functionality is initialized
document.addEventListener("DOMContentLoaded", () => {
  // Initialize search functionality if not already done
  if (window.searchManager && !window.searchManager.initialized) {
    window.searchManager.init()
  } else if (!window.searchManager) {
    // Fallback if search.js hasn't loaded yet
    const searchToggleBtn = document.querySelector(".search-toggle")
    const searchCloseBtn = document.querySelector(".search-close")
    const searchOverlay = document.querySelector(".search-overlay")

    if (searchToggleBtn && searchOverlay && searchCloseBtn) {
      // Open search overlay
      searchToggleBtn.addEventListener("click", (e) => {
        e.preventDefault()
        searchOverlay.classList.add("active")
        setTimeout(() => {
          const searchInput = searchOverlay.querySelector('input[name="q"]')
          if (searchInput) searchInput.focus()
        }, 100)
      })

      // Close search overlay
      searchCloseBtn.addEventListener("click", (e) => {
        e.preventDefault()
        searchOverlay.classList.remove("active")
      })

      // Close search with Escape key
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && searchOverlay.classList.contains("active")) {
          searchOverlay.classList.remove("active")
        }
      })

      console.log("Search functionality initialized via fallback")
    }
  }
})
>>>>>>> feature-update
