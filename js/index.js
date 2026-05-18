/*********************************************************
 * js/index.js
 * - prüft Benutzer-Authentifizierung
 * - lädt freigeschaltete Geschichten des eingeloggten Users
 * - sortiert nach play_count absteigend
 * - zeigt Empty State, falls noch keine Geschichten vorhanden sind
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

async function loadStories() {
  const isAuthorized = await checkAuth();
  if (!isAuthorized) return;

  try {
    const response = await fetch("api/stories/read_user_stories.php", {
      credentials: "include",
    });

    const stories = await response.json();

    if (!stories || stories.error) {
      console.error("Error loading stories:", stories.error);
      return;
    }

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

document.addEventListener("DOMContentLoaded", loadStories);