/***************************************************************
 * js/profile.js
 * - Laden und Aktualisieren des Benutzerprofils (profile.html)
 * - Anzeige, Verbindung und Trennung von Geräten
 *
 * Client-seitiger Code: wird dem Client vom Server bereitgestellt und auf dem Client ausgeführt
 * eingebunden in: profile.html
 * Server-Interaktion mit: api/auth/auth.php
 ***************************************************************/

async function checkAuth() {
  try {
    const response = await fetch("api/auth/auth.php", {
      credentials: "include",
    });

    if (response.status === 401) {
      window.location.href = "login.html";
      return false;
    }

    const result = await response.json();
    if (result.error || !result.email) {
      window.location.href = "login.html";
      return false;
    }
    return true;
  } catch (error) {
    console.error("Auth check failed:", error);
    window.location.href = "login.html";
    return false;
  }
}

// Load profile data
async function loadProfile() {
  const isAuthorized = await checkAuth();
  if (!isAuthorized) return;

  try {
    const response = await fetch("api/profile/read_profile.php");
    const data = await response.json();

    if (data.error) {
      console.error("Error loading profile:", data.error);
      return;
    }

    // Update user info
    document.getElementById("userName").value = data.user.name;

    // Render connected devices
    renderDevices(data.devices || []);
  } catch (error) {
    console.error("Error loading profile:", error);
  }
}

// Render connected devices in the device status area
function renderDevices(devices) {
  const statusEl = document.getElementById("deviceStatus");

  if (devices.length === 0) {
    statusEl.innerHTML =
      '<span class="device-badge device-badge-none">Kein Gerät verbunden</span>';
    return;
  }

  statusEl.innerHTML = devices
    .map(
      (d) =>
        `<div class="device-badge">
          Gerät: ${d.device_code}
          <button class="disconnect-btn" onclick="disconnectDevice(${d.id})" title="Trennen">&times;</button>
        </div>`,
    )
    .join("");
}

// Connect a device by code
async function connectDevice() {
  const input = document.getElementById("deviceCode");
  const code = input.value.trim();

  if (!code) {
    alert("Bitte einen Geräte-Code eingeben");
    return;
  }

  try {
    const response = await fetch("api/device/connect_device.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ device_code: code }),
    });

    const result = await response.json();

    if (result.error) {
      alert(result.error);
      return;
    }

    input.value = "";
    loadProfile();
  } catch (error) {
    console.error("Error connecting device:", error);
    alert("Fehler beim Verbinden des Geräts");
  }
}

// Disconnect a device
async function disconnectDevice(deviceId) {
  if (!confirm("Gerät wirklich trennen?")) return;

  try {
    const response = await fetch("api/device/disconnect_device.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ device_id: deviceId }),
    });

    const result = await response.json();

    if (result.error) {
      alert(result.error);
      return;
    }

    loadProfile();
  } catch (error) {
    console.error("Error disconnecting device:", error);
    alert("Fehler beim Trennen des Geräts");
  }
}

// Reuse logout function
async function logout() {
  try {
    await fetch("api/auth/logout.php");
    window.location.href = "login.html";
  } catch (error) {
    console.error("Logout failed:", error);
    alert("Logout failed");
  }
}

// Update user name
async function updateName() {
  const nameInput = document.getElementById("userName");
  const newName = nameInput.value.trim();

  if (!newName) {
    alert("Name cannot be empty");
    return;
  }

  try {
    const response = await fetch("api/profile/update_profile.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ name: newName }),
    });

    const result = await response.json();

    if (result.error) {
      alert(result.error);
      return;
    }

    alert("Name updated successfully!");
  } catch (error) {
    console.error("Error updating name:", error);
    alert("Failed to update name");
  }
}

// Add event listener for name input
document.getElementById("userName").addEventListener("change", updateName);

// Load profile when page loads
document.addEventListener("DOMContentLoaded", loadProfile);
