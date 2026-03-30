/***************************************************************
 * js/settings.js
 * - lädt Track-Daten und zeigt sie in einer Tabelle an
 * - Ermöglicht das Selektieren/Abwählen von Tracks per Checkbox
 * - Aktualisiert Track-Auswahl auf dem Server
 * - vorausgesetzt: Benutzer-Authentifizierung gegeben / Session ist aktiv(Prüfung zu Beginn)
 *
 * Client-seitiger Code: wird dem Client vom Server bereitgestellt und auf dem Client ausgeführt
 * eingebunden in: settings.html
 * Server-Interaktion mit:
 * - api/auth/auth.php (prüft, ob ein Benutzer eingeloggt ist (Session))
 * - api/tracks/read_tracks.php (Profilinfos und Infos zu verbundenen Geräten anzeigen)
 * - api/tracks/update_selected_tracks.php (Track-Auswahl des verbundenen Geräts ändern)
 ***************************************************************/

// First check if user is authorized
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

///////////////////////////////////// Checkliste anzeigen mit Infos aus der Datenbank

async function loadTracks() {
  const isAuthorized = await checkAuth();
  if (!isAuthorized) return;

  try {
    const response = await fetch("api/tracks/read_tracks.php");
    // response: z. B. [{"id": 1,"title": "Another brick in the wall","selected": 0},{"id": 2,"title": "Back in black","selected": 1},{...}]

    const tracks = await response.json();

    if (!tracks || tracks.error) {
      console.error("Error loading tracks:", tracks.error);
      document.querySelector(".scoreboard-container").style.display = "none";
      document.getElementById("noDeviceState").style.display = "";
      return;
    }

    const tbody = document.getElementById("tracks-body");
    tbody.innerHTML = "";

    tracks.forEach((track) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${track.title}</td>
        <td>
          <input
            type="checkbox"
            class="delete-checkbox"
            ${Number(track.selected) === 1 ? "checked" : ""}
            onchange="updateTrackSelection(${track.id}, this.checked)"
          />
        </td>
      `;
      tbody.appendChild(row);
    });
  } catch (error) {
    console.error("Error loading tracks:", error);
  }
}

///////////////////////////////////// Checkliste bearbeiten und Änderungen in DBspeichern

async function updateTrackSelection(trackId, selected) {
  try {
    const data_to_transmit = JSON.stringify({
      track_id: trackId,
      selected: selected ? 1 : 0,
    });

    const response = await fetch("api/tracks/update_selected_tracks.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: data_to_transmit, // z. B.  {"track_id":10,"selected":1}
    });

    const result = await response.json();
    if (result.error) {
      alert(result.error);
    }
  } catch (error) {
    console.error("Error updating track:", error);
    alert("Failed to update track");
  }
}

document.addEventListener("DOMContentLoaded", loadTracks);
