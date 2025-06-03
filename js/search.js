/**
 * Enhanced Search Functionality
 * Ensures search works consistently across all pages
 */

class SearchManager {
  constructor() {
    this.initialized = false
    this.searchToggleBtn = null
    this.searchCloseBtn = null
    this.searchOverlay = null
    this.searchInput = null

    // Bind methods
    this.init = this.init.bind(this)
    this.openSearch = this.openSearch.bind(this)
    this.closeSearch = this.closeSearch.bind(this)
    this.handleKeyDown = this.handleKeyDown.bind(this)
  }

  /**
   * Initialize search functionality
   */
  init() {
    if (this.initialized) {
      console.warn("[SearchManager] Already initialized")
      return
    }

    console.log("[SearchManager] Initializing...")

    // Get elements
    this.searchToggleBtn = document.querySelector(".search-toggle")
    this.searchCloseBtn = document.querySelector(".search-close")
    this.searchOverlay = document.querySelector(".search-overlay")

    if (!this.searchToggleBtn || !this.searchCloseBtn || !this.searchOverlay) {
      console.error("[SearchManager] Required elements not found")
      return
    }

    this.searchInput = this.searchOverlay.querySelector('input[name="q"]')

    // Add event listeners
    this.searchToggleBtn.addEventListener("click", this.openSearch)
    this.searchCloseBtn.addEventListener("click", this.closeSearch)
    document.addEventListener("keydown", this.handleKeyDown)

    this.initialized = true
    console.log("[SearchManager] Initialized successfully")
  }

  /**
   * Open search overlay
   * @param {Event} e - Click event
   */
  openSearch(e) {
    if (e) e.preventDefault()

    console.log("[SearchManager] Opening search overlay")
    this.searchOverlay.classList.add("active")

    // Focus input after transition
    setTimeout(() => {
      if (this.searchInput) {
        this.searchInput.focus()
      }
    }, 100)
  }

  /**
   * Close search overlay
   * @param {Event} e - Click event
   */
  closeSearch(e) {
    if (e) e.preventDefault()

    console.log("[SearchManager] Closing search overlay")
    this.searchOverlay.classList.remove("active")
  }

  /**
   * Handle keyboard events
   * @param {KeyboardEvent} e - Keyboard event
   */
  handleKeyDown(e) {
    // Close on Escape key
    if (e.key === "Escape" && this.searchOverlay.classList.contains("active")) {
      this.closeSearch()
    }

    // Open on Ctrl+K or Cmd+K
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
      e.preventDefault()
      this.openSearch()
    }
  }
}

// Create global instance
window.searchManager = new SearchManager()

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  window.searchManager.init()
})

// Fallback initialization for pages that might load scripts differently
if (document.readyState !== "loading") {
  // DOM already loaded
  if (window.searchManager && !window.searchManager.initialized) {
    window.searchManager.init()
  }
}
