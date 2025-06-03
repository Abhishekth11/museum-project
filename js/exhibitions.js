document.addEventListener("DOMContentLoaded", () => {
  const filterTabs = document.querySelectorAll(".filter-tab")
  const categoryFilter = document.getElementById("category-filter")
  const exhibitionsContainers = document.querySelectorAll(".exhibitions-grid")

  // Tab filtering
  filterTabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      // Remove active class from all tabs
      filterTabs.forEach((t) => t.classList.remove("active"))
      this.classList.add("active")

      // Hide all containers
      exhibitionsContainers.forEach((container) => {
        container.classList.add("hidden")
      })

      // Show selected container
      const targetContainer = document.getElementById(this.dataset.filter + "-exhibitions")
      if (targetContainer) {
        targetContainer.classList.remove("hidden")
      }
    })
  })

  // Category filtering
  if (categoryFilter) {
    categoryFilter.addEventListener("change", function () {
      const selectedCategory = this.value
      const activeContainer = document.querySelector(".exhibitions-grid:not(.hidden)")

      if (activeContainer) {
        const cards = activeContainer.querySelectorAll(".exhibition-card")
        cards.forEach((card) => {
          if (selectedCategory === "all" || card.dataset.category === selectedCategory) {
            card.style.display = "block"
          } else {
            card.style.display = "none"
          }
        })
      }
    })
  }

  // AJAX loading for dynamic content
  function loadExhibitions(status, category = "all") {
    fetch(`api/get-exhibitions.php?status=${status}&category=${category}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          updateExhibitionsDisplay(data.data, status)
        }
      })
      .catch((error) => {
        console.error("Error loading exhibitions:", error)
      })
  }

  function updateExhibitionsDisplay(exhibitions, status) {
    const container = document.getElementById(status + "-exhibitions")
    if (!container) return

    if (exhibitions.length === 0) {
      container.innerHTML = '<div class="no-content"><p>No exhibitions found.</p></div>'
      return
    }

    container.innerHTML = exhibitions
      .map(
        (exhibition) => `
            <div class="exhibition-card" data-category="${exhibition.category}">
                <div class="exhibition-image">
                    <img src="${exhibition.image ? "uploads/exhibitions/" + exhibition.image : "https://source.unsplash.com/random/600x400/?art"}" 
                         alt="${exhibition.title}">
                    <div class="exhibition-date">
                        ${
                          status === "current"
                            ? "Until " + formatDate(exhibition.end_date)
                            : formatDate(exhibition.start_date) + " - " + formatDate(exhibition.end_date)
                        }
                    </div>
                </div>
                <div class="exhibition-details">
                    <h3>${exhibition.title}</h3>
                    <p>${exhibition.description.substring(0, 150)}...</p>
                    <div class="tags">
                        <span>${exhibition.category}</span>
                    </div>
                    <a href="exhibition-detail.php?id=${exhibition.id}" class="btn btn-secondary">View Details</a>
                </div>
            </div>
        `,
      )
      .join("")
  }

  function formatDate(dateString) {
    const date = new Date(dateString)
    return date.toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
    })
  }
})
