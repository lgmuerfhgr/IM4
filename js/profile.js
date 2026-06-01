/***************************************************************
 * js/profile.js
 * - Authentifizierung prüfen
 * - Profil laden (Name, Boxen)
 * - Box verbinden mit Inline-Feedback
 * - Box trennen
 * - Name aktualisieren
 * - Logout
 ***************************************************************/

// Prüft ob der User eingeloggt ist. Bei Fehler → Redirect zu login.html
async function checkAuth() {
  try {
    const res = await fetch("api/auth/auth.php", { credentials: "include" });
    if (res.status === 401) { window.location.href = "login.html"; return false; }
    const result = await res.json();
    if (result.error) { window.location.href = "login.html"; return false; }
    return true;
  } catch (e) {
    window.location.href = "login.html";
    return false;
  }
}

// Lädt Profildaten (Name, Boxen) vom Server und rendert sie
async function loadProfile() {
  if (!await checkAuth()) return;

  try {
    const res = await fetch("api/profile/read_profile.php", { credentials: "include" });
    const data = await res.json();
    if (data.error) { console.error("Error loading profile:", data.error); return; }

    document.getElementById("userName").value = data.user.name || "";
    renderDevices(data.devices || []);
  } catch (e) {
    console.error("Error loading profile:", e);
  }
}

// Rendert die verknüpften Boxen in #deviceStatus
// Jede Box zeigt serial_id und einen Trennen-Button
function renderDevices(devices) {
  const el = document.getElementById("deviceStatus");
  if (!el) return;

  if (devices.length === 0) {
    el.innerHTML = '<span class="device-badge device-badge-none">Keine Box verbunden</span>';
    return;
  }

  el.innerHTML = devices.map(d => `
    <div class="device-badge">
      Box: ${d.serial_id}
      <button class="disconnect-btn" onclick="disconnectDevice(${d.id})" title="Trennen">&times;</button>
    </div>
  `).join("");
}

function getOrCreateFeedbackEl() {
  let el = document.getElementById("connectFeedback");
  if (!el) {
    el = document.createElement("div");
    el.id = "connectFeedback";
    el.className = "connect-feedback";
    const row = document.querySelector(".device-input-row");
    if (row) row.insertAdjacentElement("afterend", el);
    else document.getElementById("connectForm")?.appendChild(el);
  }
  return el;
}

// Verbindet eine Box anhand des eingegebenen Box-Codes (serial_id)
// Zeigt Erfolg oder Fehler inline unter dem Eingabefeld (#connectFeedback)
async function connectDevice() {
  const input = document.getElementById("deviceCode");
  const feedback = getOrCreateFeedbackEl();
  const code = input.value.trim();

  // Feedback zurücksetzen
  feedback.textContent = "";
  feedback.className = "connect-feedback";

  if (!code) {
    feedback.textContent = "Bitte einen Box-Code eingeben.";
    feedback.classList.add("connect-feedback--error");
    return;
  }

  try {
    const res = await fetch("api/device/connect_device.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ serial_id: code }),
    });

    const result = await res.json();

    if (result.error) {
      // Fehlermeldung vom Server inline anzeigen
      feedback.textContent = result.error;
      feedback.classList.add("connect-feedback--error");
      return;
    }

    // Erfolg: Eingabefeld leeren, Box-Name mit Häkchen anzeigen, Profil neu laden
    input.value = "";
    feedback.innerHTML = `&#10003; Box <strong>${result.serial_id}</strong> erfolgreich verbunden.`;
    feedback.classList.add("connect-feedback--success");
    loadProfile();
  } catch (e) {
    feedback.textContent = "Fehler beim Verbinden der Box.";
    feedback.classList.add("connect-feedback--error");
  }
}

// Trennt eine Box vom User-Profil (setzt user_id in boxes auf NULL)
// Bestätigung per confirm() da destruktive Aktion
async function disconnectDevice(deviceId) {
  if (!confirm("Box wirklich trennen?")) return;

  try {
    const res = await fetch("api/device/disconnect_device.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ device_id: deviceId }),
    });

    const result = await res.json();
    if (result.error) { alert(result.error); return; }
    loadProfile();
  } catch (e) {
    alert("Fehler beim Trennen der Box.");
  }
}

// Loggt den User aus und leitet zu login.html weiter
async function logout() {
  try {
    await fetch("api/auth/logout.php", { credentials: "include" });
    window.location.href = "login.html";
  } catch (e) {
    alert("Logout fehlgeschlagen.");
  }
}

// Speichert den geänderten Namen des Users in der Datenbank
// Wird beim change-Event des Namensfelds ausgelöst
async function updateName() {
  const newName = document.getElementById("userName").value.trim();
  if (!newName) { alert("Name darf nicht leer sein."); return; }

  try {
    const res = await fetch("api/profile/update_profile.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ name: newName }),
    });

    const result = await res.json();
    if (result.error) alert(result.error);
  } catch (e) {
    alert("Name konnte nicht gespeichert werden.");
  }
}

// Initialisierung: Name-Feld auf Änderungen überwachen, Profil laden
document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("userName").addEventListener("change", updateName);
  loadProfile();
});

