/**
 * Simple Theme Switcher - Alternative CSS Approach
 * Switches between style.css (light) and alternative-style.css (dark)
 */

class SimpleThemeSwitcher {
  constructor() {
    this.currentTheme = "light"
    this.storageKey = "museum-theme-preference"
    this.cookieName = "theme"
    this.cookieExpiry = 365 // days
    this.isInitialized = false
    this.alternativeStyleLink = null
    this.observers = []

    // Bind methods
    this.init = this.init.bind(this)
    this.toggle = this.toggle.bind(this)
    this.setTheme = this.setTheme.bind(this)
  }

  /**
   * Initialize the theme switcher
   */
  init() {
    if (this.isInitialized) {
      console.warn("[SimpleThemeSwitcher] Already initialized")
      return
    }

    console.log("[SimpleThemeSwitcher] Initializing...")

    try {
      // Create alternative style link
      this.createAlternativeStyleLink()

      // Detect initial theme
      this.currentTheme = this.detectInitialTheme()

      // Apply initial theme
      this.applyTheme(this.currentTheme, false)

      // Set up event listeners
      this.setupEventListeners()

      this.isInitialized = true
      console.log("[SimpleThemeSwitcher] Initialized with theme:", this.currentTheme)

      // Notify observers
      this.notifyObservers("init", this.currentTheme)
    } catch (error) {
      console.error("[SimpleThemeSwitcher] Initialization error:", error)

      // Fallback to light theme in case of errors
      try {
        this.applyTheme("light", false)
      } catch (e) {
        console.error("[SimpleThemeSwitcher] Fallback error:", e)
      }
    }
  }

  /**
   * Create the alternative style link element
   */
  createAlternativeStyleLink() {
    // Remove any existing alternative style link
    const existing = document.getElementById("alternative-style-css")
    if (existing) {
      existing.remove()
    }

    // Create new alternative style link
    this.alternativeStyleLink = document.createElement("link")
    this.alternativeStyleLink.rel = "stylesheet"
    this.alternativeStyleLink.href = "css/alternative-style.css"
    this.alternativeStyleLink.id = "alternative-style-css"
    this.alternativeStyleLink.disabled = true // Initially disabled

    // Add to head
    document.head.appendChild(this.alternativeStyleLink)

    console.log("[SimpleThemeSwitcher] Alternative style link created")
  }

  /**
   * Detect the initial theme preference
   */
  detectInitialTheme() {
    // Priority: URL parameter > localStorage > cookie > PHP default > system preference

    // 1. Check URL parameter
    const urlParams = new URLSearchParams(window.location.search)
    const urlTheme = urlParams.get("theme")
    if (urlTheme && (urlTheme === "light" || urlTheme === "dark")) {
      console.log("[SimpleThemeSwitcher] Theme from URL:", urlTheme)
      return urlTheme
    }

    // 2. Check localStorage
    const storedTheme = localStorage.getItem(this.storageKey)
    if (storedTheme && (storedTheme === "light" || storedTheme === "dark")) {
      console.log("[SimpleThemeSwitcher] Theme from localStorage:", storedTheme)
      return storedTheme
    }

    // 3. Check cookie
    const cookieTheme = this.getCookie(this.cookieName)
    if (cookieTheme && (cookieTheme === "light" || cookieTheme === "dark")) {
      console.log("[SimpleThemeSwitcher] Theme from cookie:", cookieTheme)
      return cookieTheme
    }

    // 4. Check data-default-theme attribute on html element (set by PHP)
    const defaultTheme = document.documentElement.getAttribute("data-default-theme")
    if (defaultTheme && (defaultTheme === "light" || defaultTheme === "dark")) {
      console.log("[SimpleThemeSwitcher] Theme from PHP default:", defaultTheme)
      return defaultTheme
    }

    // 5. Check system preference
    if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
      console.log("[SimpleThemeSwitcher] Theme from system preference: dark")
      return "dark"
    }

    // 6. Default theme
    console.log("[SimpleThemeSwitcher] Using default theme: light")
    return "light"
  }

  /**
   * Apply a theme by enabling/disabling the alternative CSS
   */
  applyTheme(themeName, animate = true) {
    if (themeName !== "light" && themeName !== "dark") {
      console.error("[SimpleThemeSwitcher] Invalid theme:", themeName)
      return false
    }

    console.log("[SimpleThemeSwitcher] Applying theme:", themeName)

    // Show loading animation if requested
    if (animate) {
      this.showLoadingAnimation()
    }

    // Enable/disable alternative CSS
    if (this.alternativeStyleLink) {
      this.alternativeStyleLink.disabled = themeName === "light"
    }

    // Update body and html classes
    document.body.classList.remove("light-theme", "dark-theme")
    document.body.classList.add(themeName + "-theme")

    document.documentElement.classList.remove("light-theme", "dark-theme")
    document.documentElement.classList.add(themeName + "-theme")

    // Set data attribute
    document.documentElement.setAttribute("data-theme", themeName)

    // Update meta theme-color
    this.updateMetaThemeColor(themeName)

    // Update current theme
    this.currentTheme = themeName

    // Save preference
    this.saveThemePreference(themeName)

    // Update toggle button
    this.updateToggleButton()

    // Hide loading animation
    if (animate) {
      setTimeout(() => {
        this.hideLoadingAnimation()
      }, 200)
    }

    // Notify observers
    this.notifyObservers("change", themeName)

    // Dispatch custom event
    window.dispatchEvent(
      new CustomEvent("themeChanged", {
        detail: { theme: themeName },
      }),
    )

    return true
  }

  /**
   * Toggle between light and dark themes
   */
  toggle() {
    const nextTheme = this.currentTheme === "light" ? "dark" : "light"
    console.log("[SimpleThemeSwitcher] Toggling from", this.currentTheme, "to", nextTheme)
    this.applyTheme(nextTheme)
  }

  /**
   * Set a specific theme
   */
  setTheme(themeName) {
    if (themeName !== "light" && themeName !== "dark") {
      console.error("[SimpleThemeSwitcher] Invalid theme:", themeName)
      return false
    }

    if (themeName === this.currentTheme) {
      console.log("[SimpleThemeSwitcher] Theme already active:", themeName)
      return true
    }

    return this.applyTheme(themeName)
  }

  /**
   * Get current theme
   */
  getCurrentTheme() {
    return this.currentTheme
  }

  /**
   * Update meta theme-color
   */
  updateMetaThemeColor(themeName) {
    let metaThemeColor = document.querySelector('meta[name="theme-color"]')
    if (!metaThemeColor) {
      metaThemeColor = document.createElement("meta")
      metaThemeColor.name = "theme-color"
      document.head.appendChild(metaThemeColor)
    }

    const color = themeName === "dark" ? "#121212" : "#ffffff"
    metaThemeColor.content = color
  }

  /**
   * Save theme preference
   */
  saveThemePreference(themeName) {
    // Save to localStorage
    try {
      localStorage.setItem(this.storageKey, themeName)
    } catch (e) {
      console.warn("[SimpleThemeSwitcher] Could not save to localStorage:", e)
    }

    // Save to cookie
    this.setCookie(this.cookieName, themeName, this.cookieExpiry)
  }

  /**
   * Set up event listeners
   */
  setupEventListeners() {
    // Theme toggle buttons
    document.addEventListener("click", (e) => {
      if (e.target.matches(".theme-toggle, .theme-toggle *")) {
        e.preventDefault()
        this.toggle()
      }
    })

    // Keyboard shortcut (Ctrl/Cmd + Shift + T)
    document.addEventListener("keydown", (e) => {
      if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === "T") {
        e.preventDefault()
        this.toggle()
      }
    })

    // System theme change detection
    if (window.matchMedia) {
      const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)")
      mediaQuery.addEventListener("change", (e) => {
        // Only auto-switch if no manual preference is set
        if (!localStorage.getItem(this.storageKey)) {
          const systemTheme = e.matches ? "dark" : "light"
          console.log("[SimpleThemeSwitcher] System theme changed to:", systemTheme)
          this.applyTheme(systemTheme)
        }
      })
    }

    // Cross-tab synchronization
    window.addEventListener("storage", (e) => {
      if (e.key === this.storageKey && e.newValue && e.newValue !== this.currentTheme) {
        console.log("[SimpleThemeSwitcher] Theme changed in another tab:", e.newValue)
        this.applyTheme(e.newValue, false)
      }
    })

    // Page visibility change (sync on tab focus)
    document.addEventListener("visibilitychange", () => {
      if (!document.hidden) {
        const storedTheme = localStorage.getItem(this.storageKey)
        if (storedTheme && storedTheme !== this.currentTheme) {
          this.applyTheme(storedTheme, false)
        }
      }
    })
  }

  /**
   * Update toggle button appearance
   */
  updateToggleButton() {
    const toggleButtons = document.querySelectorAll(".theme-toggle")

    toggleButtons.forEach((button) => {
      const sunIcon = button.querySelector(".fa-sun")
      const moonIcon = button.querySelector(".fa-moon")

      if (sunIcon && moonIcon) {
        if (this.currentTheme === "dark") {
          sunIcon.style.display = "inline-block"
          moonIcon.style.display = "none"
        } else {
          sunIcon.style.display = "none"
          moonIcon.style.display = "inline-block"
        }
      }

      // Update aria-label
      const isDark = this.currentTheme === "dark"
      button.setAttribute("aria-label", isDark ? "Switch to light theme" : "Switch to dark theme")

      // Update title
      button.setAttribute("title", `Switch to ${isDark ? "light" : "dark"} theme (Ctrl+Shift+T)`)
    })
  }

  /**
   * Show loading animation
   */
  showLoadingAnimation() {
    // Add a simple transition class to body
    document.body.style.transition = "background-color 0.3s ease, color 0.3s ease"

    // Remove transition after animation
    setTimeout(() => {
      document.body.style.transition = ""
    }, 300)
  }

  /**
   * Hide loading animation
   */
  hideLoadingAnimation() {
    // Animation is handled by CSS transitions
    // This method exists for consistency with the interface
  }

  /**
   * Cookie utilities
   */
  setCookie(name, value, days) {
    const expires = new Date()
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000)

    // Ensure cookie is available across the entire site
    const path = "/"

    // Set SameSite attribute for security
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=${path};SameSite=Lax`
  }

  getCookie(name) {
    const nameEQ = name + "="
    const ca = document.cookie.split(";")
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i]
      while (c.charAt(0) === " ") c = c.substring(1, c.length)
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length)
    }
    return null
  }

  /**
   * Observer pattern
   */
  addObserver(callback) {
    this.observers.push(callback)
  }

  removeObserver(callback) {
    this.observers = this.observers.filter((obs) => obs !== callback)
  }

  notifyObservers(event, theme) {
    this.observers.forEach((callback) => {
      try {
        callback(event, theme)
      } catch (e) {
        console.error("[SimpleThemeSwitcher] Observer error:", e)
      }
    })
  }

  /**
   * Reset theme to default
   */
  reset() {
    localStorage.removeItem(this.storageKey)
    this.setCookie(this.cookieName, "", -1)

    const systemTheme =
      window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"

    this.applyTheme(systemTheme)
  }

  /**
   * Get theme statistics
   */
  getStats() {
    return {
      currentTheme: this.currentTheme,
      isInitialized: this.isInitialized,
      hasLocalStorage: !!localStorage.getItem(this.storageKey),
      hasCookie: !!this.getCookie(this.cookieName),
      systemPreference:
        window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light",
    }
  }
}

// Create global instance
window.simpleThemeSwitcher = new SimpleThemeSwitcher()

// Auto-initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    window.simpleThemeSwitcher.init()
  })
} else {
  window.simpleThemeSwitcher.init()
}

// Global utility functions for backward compatibility
window.toggleTheme = () => window.simpleThemeSwitcher?.toggle()
window.setTheme = (theme) => window.simpleThemeSwitcher?.setTheme(theme)
window.getCurrentTheme = () => window.simpleThemeSwitcher?.getCurrentTheme()

// Export for module systems
if (typeof module !== "undefined" && module.exports) {
  module.exports = SimpleThemeSwitcher
}
