/*********************************************************
 * js/index.js
 * - prüft Benutzer-Authentifizierung
 * - lädt freigeschaltete Geschichten des eingeloggten Users
 * - sortiert nach play_count absteigend
 * - zeigt Empty State, falls noch keine Geschichten vorhanden sind
 * - pollt alle 3 Sekunden auf neue Sensor-Auslösungen
 * - blendet automatisch einen Audio-Player ein wenn eine Figur erkannt wird
 *********************************************************/

// ── Auth ────────────────────────────────────────────────
async function checkAuth() {
  try {
    const response = await fetch("api/auth/auth.php", { credentials: "include" });
    if (response.status === 401) { window.location.href = "login.html"; return false; }
    const result = await response.json();
    if (result.error || !result.email) { window.location.href = "login.html"; return false; }
    return true;
  } catch (error) {
    console.error("Auth check failed:", error);
    window.location.href = "login.html";
    return false;
  }
}

// ── Story-Liste laden ────────────────────────────────────
async function loadStories() {
  const isAuthorized = await checkAuth();
  if (!isAuthorized) return;

  try {
    const response = await fetch("api/stories/read_user_stories.php", { credentials: "include" });
    const stories = await response.json();

    if (!stories || stories.error) { console.error("Error loading stories:", stories?.error); return; }

    const storyList = document.getElementById("storyList");
    const emptyState = document.getElementById("emptyState");
    storyList.innerHTML = "";

    if (stories.length === 0) {
      emptyState.style.display = "";
      return;
    }

    emptyState.style.display = "none";

    stories.forEach((story) => {
      const link = document.createElement("a");
      link.href = `story.html?id=${encodeURIComponent(story.id)}`;
      const button = document.createElement("button");
      button.innerHTML = `<span>▷</span>${story.title}`;
      link.appendChild(button);
      storyList.appendChild(link);
    });
  } catch (error) {
    console.error("Error loading stories:", error);
  }
}

// ── Auto-Player Overlay ──────────────────────────────────

const animalImages = {
  1: "assets/illustrations/Tiere/Elefant.png",
  2: "assets/illustrations/Tiere/Löwe.png",
  3: "assets/illustrations/Tiere/Zebra.png"
};

// Erstellt den Overlay-Player (einmalig im DOM) und gibt Referenz zurück
function ensurePlayerOverlay() {
  let overlay = document.getElementById("autoPlayerOverlay");
  if (overlay) return overlay;

  overlay = document.createElement("div");
  overlay.id = "autoPlayerOverlay";
  overlay.innerHTML = `
    <div class="auto-player-card">
      <button class="auto-player-close" id="autoPlayerClose" aria-label="Schliessen">&times;</button>
      <img id="autoPlayerAnimal" src="" alt="Tier" />
      <h2 id="autoPlayerTitle"></h2>
      <p id="autoPlayerIntro"></p>
      <audio id="autoPlayerAudio" controls autoplay>
        Dein Browser unterstützt kein Audio-Element.
      </audio>
    </div>
  `;
  document.body.appendChild(overlay);

  document.getElementById("autoPlayerClose").addEventListener("click", () => {
    const audio = document.getElementById("autoPlayerAudio");
    audio.pause();
    overlay.classList.remove("auto-player-visible");
  });

  return overlay;
}

// Zeigt den Overlay-Player mit den Story-Daten an
function showAutoPlayer(story) {
  const overlay = ensurePlayerOverlay();

  document.getElementById("autoPlayerTitle").textContent = story.title;
  document.getElementById("autoPlayerIntro").textContent = story.intro ?? "";
  document.getElementById("autoPlayerAnimal").src = animalImages[story.animal_id] ?? "";

  const audio = document.getElementById("autoPlayerAudio");
  audio.src = encodeURI(story.audio_path);
  audio.load();
  audio.play().catch(() => {
    // Autoplay vom Browser blockiert → Audio sichtbar, User tippt selbst Play
    console.info("Autoplay blocked by browser – user can press play manually.");
  });

  overlay.classList.add("auto-player-visible");

  // Story-Liste neu laden damit play_count aktuell ist
  loadStories();
}

// ── Polling ──────────────────────────────────────────────
let pollingInterval = null;

async function pollForStory() {
  try {
    const res = await fetch("api/sensor/poll_story.php", { credentials: "include" });
    if (res.status === 401) { stopPolling(); window.location.href = "login.html"; return; }
    const data = await res.json();
    if (data.story) {
      showAutoPlayer(data.story);
    }
  } catch (e) {
    // Netzwerkfehler beim Polling → still ignorieren, nächster Versuch kommt
    console.warn("Polling error:", e);
  }
}

function startPolling() {
  if (pollingInterval) return;
  pollingInterval = setInterval(pollForStory, 3000); // alle 3 Sekunden
}

function stopPolling() {
  clearInterval(pollingInterval);
  pollingInterval = null;
}

// Polling pausieren wenn Tab nicht sichtbar (Akku/Server schonen)
document.addEventListener("visibilitychange", () => {
  if (document.hidden) { stopPolling(); } else { startPolling(); }
});

// ── Init ─────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", async () => {
  const ok = await checkAuth();
  if (!ok) return;
  loadStories();
  startPolling();
});