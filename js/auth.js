// Enhanced Authentication JavaScript
class AuthManager {
  constructor() {
    this.initializeEventListeners()
    this.initializeTabSwitching()
    this.initializeFormValidation()
    this.initializePasswordToggle()
  }

  initializeEventListeners() {
    document.addEventListener("DOMContentLoaded", () => {
      const loginForm = document.getElementById("login-form")
      const registerForm = document.getElementById("register-form")

      if (loginForm) {
        this.setupLoginForm(loginForm)
      }

      if (registerForm) {
        this.setupRegisterForm(registerForm)
      }

      // Initialize tab switching
      this.setupTabSwitching()
    })
  }

  setupLoginForm(form) {
    form.addEventListener("submit", async (e) => {
      // Don't prevent default - let the form submit normally for now
      // This ensures the PHP processing works
      const submitBtn = form.querySelector('button[type="submit"]')

      // Add loading state
      this.setLoadingState(submitBtn, true)

      // Reset loading state after a short delay to allow form submission
      setTimeout(() => {
        this.setLoadingState(submitBtn, false)
      }, 1000)
    })
  }

  setupRegisterForm(form) {
    form.addEventListener("submit", async (e) => {
      // Client-side validation
      if (!this.validateRegistrationForm(form)) {
        e.preventDefault()
        return
      }

      const submitBtn = form.querySelector('button[type="submit"]')

      // Add loading state
      this.setLoadingState(submitBtn, true)

      // Reset loading state after a short delay to allow form submission
      setTimeout(() => {
        this.setLoadingState(submitBtn, false)
      }, 1000)
    })
  }

  setupTabSwitching() {
    const tabs = document.querySelectorAll(".auth-tab")

    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const tabName = tab.getAttribute("data-tab")
        this.switchTab(tabName)
      })
    })

    // Handle URL hash for direct tab access
    const hash = window.location.hash.substring(1)
    if (hash === "register") {
      this.switchTab("register")
    }
  }

  switchTab(tabName) {
    // Update tabs
    document.querySelectorAll(".auth-tab").forEach((tab) => {
      tab.classList.remove("active")
    })
    const activeTab = document.querySelector(`[data-tab="${tabName}"]`)
    if (activeTab) {
      activeTab.classList.add("active")
    }

    // Update panels
    document.querySelectorAll(".auth-panel").forEach((panel) => {
      panel.classList.remove("active")
    })
    const activePanel = document.getElementById(`${tabName}-panel`)
    if (activePanel) {
      activePanel.classList.add("active")
    }

    // Clear messages when switching tabs
    this.clearMessages()

    // Update URL hash
    window.history.replaceState(null, null, `#${tabName}`)
  }

  initializeFormValidation() {
    // Real-time validation for email fields
    const emailInputs = document.querySelectorAll('input[type="email"]')
    emailInputs.forEach((input) => {
      input.addEventListener("blur", () => this.validateEmail(input))
      input.addEventListener("input", () => this.clearFieldError(input))
    })

    // Real-time validation for password fields
    const passwordInputs = document.querySelectorAll('input[type="password"]')
    passwordInputs.forEach((input) => {
      input.addEventListener("blur", () => this.validatePassword(input))
      input.addEventListener("input", () => this.clearFieldError(input))
    })

    // Password confirmation validation
    const confirmPasswordInput = document.getElementById("confirm-password")
    const passwordInput = document.getElementById("register-password")

    if (confirmPasswordInput && passwordInput) {
      confirmPasswordInput.addEventListener("blur", () => {
        this.validatePasswordConfirmation(passwordInput, confirmPasswordInput)
      })
      confirmPasswordInput.addEventListener("input", () => {
        this.clearFieldError(confirmPasswordInput)
      })
    }
  }

  initializePasswordToggle() {
    // Add password visibility toggle buttons
    const passwordFields = document.querySelectorAll('input[type="password"]')

    passwordFields.forEach((field) => {
      const wrapper = document.createElement("div")
      wrapper.style.position = "relative"

      field.parentNode.insertBefore(wrapper, field)
      wrapper.appendChild(field)

      const toggleBtn = document.createElement("button")
      toggleBtn.type = "button"
      toggleBtn.innerHTML = "👁️"
      toggleBtn.style.cssText = `
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        font-size: 18px;
        opacity: 0.6;
        transition: opacity 0.3s;
      `

      toggleBtn.addEventListener("click", () => {
        const type = field.getAttribute("type") === "password" ? "text" : "password"
        field.setAttribute("type", type)
        toggleBtn.innerHTML = type === "password" ? "👁️" : "🙈"
      })

      toggleBtn.addEventListener("mouseenter", () => {
        toggleBtn.style.opacity = "1"
      })

      toggleBtn.addEventListener("mouseleave", () => {
        toggleBtn.style.opacity = "0.6"
      })

      wrapper.appendChild(toggleBtn)
    })
  }

  validateRegistrationForm(form) {
    let isValid = true
    const formData = new FormData(form)

    // Validate required fields
    const requiredFields = ["first_name", "last_name", "email", "password", "confirm_password"]
    requiredFields.forEach((fieldName) => {
      const field = form.querySelector(`[name="${fieldName}"]`)
      if (!formData.get(fieldName).trim()) {
        this.showFieldError(field, "This field is required")
        isValid = false
      }
    })

    // Validate email
    const emailField = form.querySelector('[name="email"]')
    if (!this.validateEmail(emailField)) {
      isValid = false
    }

    // Validate password
    const passwordField = form.querySelector('[name="password"]')
    if (!this.validatePassword(passwordField)) {
      isValid = false
    }

    // Validate password confirmation
    const confirmPasswordField = form.querySelector('[name="confirm_password"]')
    if (!this.validatePasswordConfirmation(passwordField, confirmPasswordField)) {
      isValid = false
    }

    return isValid
  }

  validateEmail(input) {
    const email = input.value.trim()
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

    if (email && !emailRegex.test(email)) {
      this.showFieldError(input, "Please enter a valid email address")
      return false
    }

    this.showFieldSuccess(input)
    return true
  }

  validatePassword(input) {
    const password = input.value

    if (password && password.length < 8) {
      this.showFieldError(input, "Password must be at least 8 characters long")
      return false
    }

    if (password) {
      this.showFieldSuccess(input)
    }
    return true
  }

  validatePasswordConfirmation(passwordInput, confirmInput) {
    const password = passwordInput.value
    const confirmPassword = confirmInput.value

    if (confirmPassword && password !== confirmPassword) {
      this.showFieldError(confirmInput, "Passwords do not match")
      return false
    }

    if (confirmPassword) {
      this.showFieldSuccess(confirmInput)
    }
    return true
  }

  showFieldError(input, message) {
    const formGroup = input.closest(".form-group")
    if (!formGroup) return

    formGroup.classList.remove("success")
    formGroup.classList.add("error")

    // Remove existing error message
    const existingError = formGroup.querySelector(".error-message")
    if (existingError) {
      existingError.remove()
    }

    // Add new error message
    const errorDiv = document.createElement("div")
    errorDiv.className = "error-message"
    errorDiv.textContent = message
    formGroup.appendChild(errorDiv)
  }

  showFieldSuccess(input) {
    const formGroup = input.closest(".form-group")
    if (!formGroup) return

    formGroup.classList.remove("error")
    formGroup.classList.add("success")

    // Remove error message
    const existingError = formGroup.querySelector(".error-message")
    if (existingError) {
      existingError.remove()
    }
  }

  clearFieldError(input) {
    const formGroup = input.closest(".form-group")
    if (!formGroup) return

    formGroup.classList.remove("error", "success")

    const errorMessage = formGroup.querySelector(".error-message")
    if (errorMessage) {
      errorMessage.remove()
    }
  }

  showMessage(message, type) {
    this.clearMessages()

    const messageDiv = document.createElement("div")
    messageDiv.className = `alert alert-${type}`
    messageDiv.textContent = message

    const activePanel = document.querySelector(".auth-panel.active")
    const form = activePanel.querySelector("form")

    if (form) {
      form.insertBefore(messageDiv, form.firstChild)
    } else {
      activePanel.insertBefore(messageDiv, activePanel.firstChild)
    }

    // Auto-remove success messages
    if (type === "success") {
      setTimeout(() => {
        if (messageDiv.parentNode) {
          messageDiv.remove()
        }
      }, 5000)
    }
  }

  clearMessages() {
    const messages = document.querySelectorAll(".alert")
    messages.forEach((message) => message.remove())
  }

  setLoadingState(button, isLoading) {
    if (isLoading) {
      button.classList.add("loading")
      button.disabled = true
      button.dataset.originalText = button.textContent
      button.textContent = "Please wait..."
    } else {
      button.classList.remove("loading")
      button.disabled = false
      button.textContent = button.dataset.originalText || button.textContent
    }
  }

  initializeTabSwitching() {
    // Handle keyboard navigation for tabs
    const tabs = document.querySelectorAll(".auth-tab")

    tabs.forEach((tab, index) => {
      tab.setAttribute("tabindex", "0")
      tab.setAttribute("role", "tab")

      tab.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault()
          tab.click()
        } else if (e.key === "ArrowLeft" || e.key === "ArrowRight") {
          e.preventDefault()
          const direction = e.key === "ArrowLeft" ? -1 : 1
          const nextIndex = (index + direction + tabs.length) % tabs.length
          tabs[nextIndex].focus()
        }
      })
    })
  }
}

// Initialize the authentication manager
new AuthManager()

// Additional utility functions for enhanced UX
document.addEventListener("DOMContentLoaded", () => {
  // Add smooth scrolling for better mobile experience
  if (window.innerWidth <= 768) {
    const authSection = document.querySelector(".auth-section")
    if (authSection) {
      authSection.style.scrollBehavior = "smooth"
    }
  }

  // Handle browser back/forward navigation
  window.addEventListener("popstate", (e) => {
    const hash = window.location.hash.substring(1)
    if (hash === "register" || hash === "login") {
      const authManager = new AuthManager()
      authManager.switchTab(hash)
    }
  })

  // Prevent form submission on Enter in non-submit fields
  const inputs = document.querySelectorAll('.auth-form input:not([type="submit"])')
  inputs.forEach((input) => {
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && input.type !== "submit") {
        e.preventDefault()
        const form = input.closest("form")
        const submitBtn = form.querySelector('button[type="submit"]')
        if (submitBtn) {
          submitBtn.click()
        }
      }
    })
  })
})
