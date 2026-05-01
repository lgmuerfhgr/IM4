/*********************************************************
 * js/index.js
 * - prüft Benutzer-Authentifizierung und leitet ggf. auf Login-Seite um, falls User-Authentifizierung / Session nicht gegeben ist (Prüfung zu Beginn)
 * - Lädt und zeigt sensordata (Schreievents) aus der API
 * - Formatiert Datum, Uhrzeit und Dauer von Events
 * - Visualisiert Dauer am Weinen pro Tag und Häufigkeit pro Stunde mit Chart.js
 *
 * Client-seitiger Code: wird dem Client vom Server bereitgestellt und auf dem Client ausgeführt
 * eingebunden in: index.html
 * API-Endpunkte: api/sensordata/read_sensordata.php
 *********************************************************/

// First check if user is authorized
async function checkAuth() {
  try {
    const response = await fetch("api/auth/auth.php", {
      // eg. {email: 'jan.fiess@fhgr.ch', user_id: 4}
      credentials: "include",
    });

    // ^ IMPORTANT if you need cookies

    // If server returns 401:
    if (response.status === 401) {
      window.location.href = "login.html";
      return false;
    }

    // Otherwise parse the JSON
    const result = await response.json();
    // console.log(result);

    // Possibly check if result has an error:
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

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return `${date.getDate()}.${date.getMonth() + 1}.${String(
    date.getFullYear(),
  ).slice(2)} ${String(date.getHours()).padStart(2, "0")}:${String(
    date.getMinutes(),
  ).padStart(2, "0")}`;
}

function formatDurationMinutes(startTime, endTime) {
  const start = new Date(startTime).getTime();
  const end = new Date(endTime).getTime();
  const minutes = Math.max(0, Math.round((end - start) / 60000));
  return minutes;
}

// Function to load and display all crying events
async function loadSensordata() {
  // First check authorization
  const isAuthorized = await checkAuth();
  if (!isAuthorized) return;

  try {
    const response = await fetch("api/sensordata/read_sensordata.php");
    const history = await response.json();
    console.log("Loaded sensordata history:", history);
    // eg. [{id: 23, starttime: '2026-03-21 20:50:27', endtime: '2026-03-21 20:50:37'}{...}]

    if (!history || history.error) {
      console.error("Error loading sensordata:", history.error);
      return;
    }

    const tbody = document.getElementById("sensordata-body");
    tbody.innerHTML = ""; // Clear existing rows

    if (history.length === 0) {
      document.getElementById("emptyState").style.display = "";
      renderChart([]);
      renderTimeChart([]);
      return;
    }

    document.getElementById("emptyState").style.display = "none";

    history.forEach((entry) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${formatDateTime(entry.starttime)}</td>
        <td>${formatDateTime(entry.endtime)}</td>
        <td>${formatDurationMinutes(entry.starttime, entry.endtime)}</td>
      `;
      tbody.appendChild(row);
    });

    renderChart(history);
    renderTimeChart(history);
  } catch (error) {
    console.error("Error loading sensordata:", error);
  }
}

function renderChart(history) {
  // Aggregate total crying minutes per day
  const minutesPerDay = {};

  history.forEach((entry) => {
    const date = new Date(entry.starttime);
    const dayKey = `${date.getDate()}.${date.getMonth() + 1}.${String(
      date.getFullYear(),
    ).slice(2)}`;
    const mins = formatDurationMinutes(entry.starttime, entry.endtime);
    minutesPerDay[dayKey] = (minutesPerDay[dayKey] || 0) + mins;
  });

  // Sort by date (oldest first)
  const sorted = Object.entries(minutesPerDay).sort((a, b) => {
    const [dA, mA, yA] = a[0].split(".").map(Number);
    const [dB, mB, yB] = b[0].split(".").map(Number);
    return yA - yB || mA - mB || dA - dB;
  });

  // When empty, show placeholder day labels so axes are still visible
  const labels = sorted.length > 0 ? sorted.map((e) => e[0]) : ["Heute"];
  const data = sorted.length > 0 ? sorted.map((e) => e[1]) : [0];

  const ctx = document.getElementById("sensordata").getContext("2d");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Wie lange geweint (min)",
          data: data,
          backgroundColor: "#ff6724",
          borderRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: {
          display: true,
          text: "Wie lange geweint nach Tag",
          font: { size: 16 },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          suggestedMax: 20,
          title: { display: true, text: "Minuten" },
        },
        x: {
          title: { display: true, text: "Tag" },
        },
      },
    },
  });
}

function renderTimeChart(history) {
  // Count how many crying events started in each hour of the day (0–23)
  const countPerHour = new Array(24).fill(0);

  history.forEach((entry) => {
    const hour = new Date(entry.starttime).getHours();
    countPerHour[hour]++;
  });

  const labels = countPerHour.map((_, h) => `${String(h).padStart(2, "0")}:00`);

  const ctx = document.getElementById("sensordata-time").getContext("2d");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Wie oft geweint?",
          data: countPerHour,
          backgroundColor: "#ff6724",
          borderRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: {
          display: true,
          text: "Weinen nach Uhrzeit",
          font: { size: 16 },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 },
          title: { display: true, text: "Anzahl" },
        },
        x: {
          title: { display: true, text: "Uhrzeit" },
        },
      },
    },
  });
}

// Load history when page loads
document.addEventListener("DOMContentLoaded", loadSensordata);
