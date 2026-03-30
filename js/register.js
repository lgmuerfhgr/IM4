/***************************************************************
 * js/register.js
 * - Registrierungsformular-Handling (register.html)
 * - Senden von Name, Email und Passwort an das Backend
 * - Anzeige von Erfolg/Misserfolg der Registrierung
 * - Weiterleitung zur Login-Seite (login.html) bei erfolgreicher Registrierung
 *
 * Client-seitiger Code: wird dem Client vom Server bereitgestellt und auf dem Client ausgeführt
 * eingebunden in: register.html
 * Server-Interaktion mit: api/auth/register.php
 ***************************************************************/

document
  .getElementById("registerForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    try {
      const response = await fetch("api/auth/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ name, email, password }),
      });
      const result = await response.json();

      if (result.status === "success") {
        alert("Registration successful! You can now log in.");
        window.location.href = "login.html";
      } else {
        alert(result.message || "Registration failed.");
      }
    } catch (error) {
      console.error("Error:", error);
      alert("Something went wrong!");
    }
  });
