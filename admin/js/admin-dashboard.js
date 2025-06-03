// Admin Dashboard JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  const sidebar = document.querySelector(".admin-sidebar")

  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", () => {
      sidebar.classList.toggle("open")
    })
  }

  // Activity tabs
  const tabButtons = document.querySelectorAll(".tab-btn")
  const tabPanels = document.querySelectorAll(".tab-panel")

  tabButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const targetTab = this.dataset.tab

      // Remove active class from all buttons and panels
      tabButtons.forEach((btn) => btn.classList.remove("active"))
      tabPanels.forEach((panel) => panel.classList.remove("active"))

      // Add active class to clicked button and corresponding panel
      this.classList.add("active")
      document.getElementById(targetTab + "-tab").classList.add("active")
    })
  })

  // Auto-refresh dashboard data every 5 minutes
  setInterval(() => {
    refreshDashboardStats()
  }, 300000)

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", (e) => {
    if (window.innerWidth <= 1024) {
      if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
        sidebar.classList.remove("open")
      }
    }
  })

  // Initialize tooltips
  initializeTooltips()

  // Initialize confirmation dialogs
  initializeConfirmDialogs()
})

// Refresh dashboard statistics
function refreshDashboardStats() {
  fetch("api/dashboard-stats.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateStatCards(data.stats)
      }
    })
    .catch((error) => {
      console.error("Error refreshing stats:", error)
    })
}

// Update stat cards with new data
function updateStatCards(stats) {
  const statCards = document.querySelectorAll(".stat-card")

  statCards.forEach((card) => {
    const statType = card.querySelector(".stat-icon").classList[1]
    const numberElement = card.querySelector(".stat-number")

    if (stats[statType] !== undefined) {
      animateNumber(numberElement, Number.parseInt(numberElement.textContent), stats[statType])
    }
  })
}

// Animate number changes
function animateNumber(element, start, end) {
  const duration = 1000
  const startTime = performance.now()

  function update(currentTime) {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)

    const current = Math.floor(start + (end - start) * progress)
    element.textContent = current

    if (progress < 1) {
      requestAnimationFrame(update)
    }
  }

  requestAnimationFrame(update)
}

// Initialize tooltips
function initializeTooltips() {
  const tooltipElements = document.querySelectorAll("[data-tooltip]")

  tooltipElements.forEach((element) => {
    element.addEventListener("mouseenter", showTooltip)
    element.addEventListener("mouseleave", hideTooltip)
  })
}

// Show tooltip
function showTooltip(e) {
  const tooltip = document.createElement("div")
  tooltip.className = "tooltip"
  tooltip.textContent = e.target.dataset.tooltip

  document.body.appendChild(tooltip)

  const rect = e.target.getBoundingClientRect()
  tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px"
  tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + "px"

  setTimeout(() => tooltip.classList.add("show"), 10)
}

// Hide tooltip
function hideTooltip() {
  const tooltip = document.querySelector(".tooltip")
  if (tooltip) {
    tooltip.remove()
  }
}

// Initialize confirmation dialogs
function initializeConfirmDialogs() {
  const deleteButtons = document.querySelectorAll("[data-confirm]")

  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()

      const message = this.dataset.confirm || "Are you sure you want to delete this item?"

      if (confirm(message)) {
        window.location.href = this.href
      }
    })
  })
}

// Show notification
function showNotification(message, type = "success") {
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.innerHTML = `
        <span>${message}</span>
        <button class="notification-close">&times;</button>
    `

  document.body.appendChild(notification)

  // Show notification
  setTimeout(() => notification.classList.add("show"), 10)

  // Auto hide after 5 seconds
  setTimeout(() => {
    notification.classList.remove("show")
    setTimeout(() => notification.remove(), 300)
  }, 5000)

  // Close button
  notification.querySelector(".notification-close").addEventListener("click", () => {
    notification.classList.remove("show")
    setTimeout(() => notification.remove(), 300)
  })
}

// Form validation
function validateForm(form) {
  const requiredFields = form.querySelectorAll("[required]")
  let isValid = true

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      showFieldError(field, "This field is required")
      isValid = false
    } else {
      clearFieldError(field)
    }
  })

  return isValid
}

// Show field error
function showFieldError(field, message) {
  clearFieldError(field)

  const error = document.createElement("div")
  error.className = "field-error"
  error.textContent = message

  field.parentNode.appendChild(error)
  field.classList.add("error")
}

// Clear field error
function clearFieldError(field) {
  const existingError = field.parentNode.querySelector(".field-error")
  if (existingError) {
    existingError.remove()
  }
  field.classList.remove("error")
}

// Export functions for global use
window.adminDashboard = {
  showNotification,
  validateForm,
  refreshDashboardStats,
}
