document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("login-form")
  const registerForm = document.getElementById("register-form")

  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault()

      const formData = new FormData(this)

      fetch("api/login.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            window.location.href = data.redirect || "index.php"
          } else {
            showMessage(data.message, "error")
          }
        })
        .catch((error) => {
          showMessage("An error occurred. Please try again.", "error")
        })
    })
  }

  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      e.preventDefault()

      const password = this.querySelector('[name="password"]').value
      const confirmPassword = this.querySelector('[name="confirm_password"]').value

      if (password !== confirmPassword) {
        showMessage("Passwords do not match.", "error")
        return
      }

      const formData = new FormData(this)

      fetch("api/register.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showMessage(data.message, "success")
            setTimeout(() => {
              window.location.href = "login.php"
            }, 2000)
          } else {
            showMessage(data.message, "error")
          }
        })
        .catch((error) => {
          showMessage("An error occurred. Please try again.", "error")
        })
    })
  }

  function showMessage(message, type) {
    const messageDiv = document.createElement("div")
    messageDiv.className = `message ${type}`
    messageDiv.textContent = message

    const form = document.querySelector("form")
    form.insertBefore(messageDiv, form.firstChild)

    setTimeout(() => {
      messageDiv.remove()
    }, 5000)
  }
})
