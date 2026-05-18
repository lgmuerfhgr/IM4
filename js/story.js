/*********************************************************
 * js/story.js
 * - prüft Auth
 * - lädt einzelne Story anhand story.html?id=...
 * - setzt Titel und Audio-Datei
 *********************************************************/

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

async function loadStory() {
  const isAuthorized = await checkAuth();
  if (!isAuthorized) return;

  const params = new URLSearchParams(window.location.search);
  const storyId = params.get("id");

  if (!storyId) {
    document.getElementById("storyTitle").textContent = "Keine Geschichte ausgewählt";
    return;
  }

  try {
    const response = await fetch(`api/stories/read_story.php?id=${encodeURIComponent(storyId)}`, {
      credentials: "include",
    });

    const story = await response.json();

    if (story.error) {
      document.getElementById("storyTitle").textContent = story.error;
      return;
    }

    document.getElementById("storyTitle").textContent = story.title;
    document.getElementById("storyText").textContent = story.description || "";

    const audio = document.getElementById("storyAudio");
    audio.src = story.audio_path;
  } catch (error) {
    console.error("Error loading story:", error);
  }
}

document.addEventListener("DOMContentLoaded", loadStory);