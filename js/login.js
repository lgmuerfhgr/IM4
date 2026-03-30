/***************************************************************
 * js/login.js
 * - Formular-Event-Handling für Login
 * - Login-Daten an das Backend senden (-> api/auth/login.php)
 * - Verarbeitung der Server-Antwort (Erfolg/Misserfolg)
 * - bei erfolgreichem Login index.html aufrufen
 *
 * Client-seitiger Code: wird dem Client vom Server bereitgestellt und auf dem Client ausgeführt
 * eingebunden in: login.html
 * Server-Interaktion mit: api/auth/login.php
 ***************************************************************/

document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();

  try {
    const response = await fetch("api/auth/login.php", {
      method: "POST",
      // credentials: 'include', // uncomment if front-end & back-end are on different domains
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ email, password }),
    });
    const result = await response.json();

    if (result.status === "success") {
      alert("Login successful!");
      window.location.href = "index.html";
    } else {
      alert(result.message || "Login failed.");
    }
  } catch (error) {
    console.error("Error:", error);
    alert("Something went wrong!");
  }
});
