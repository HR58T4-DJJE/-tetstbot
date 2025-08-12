(function () {
  "use strict";

  function log(message) {
    // eslint-disable-next-line no-console
    console.log("[app]", message);
  }

  async function pingHealth() {
    const resultEl = document.getElementById("pingResult");
    if (!resultEl) return;

    resultEl.textContent = "Запрос...";
    try {
      const response = await fetch("/health.php", { headers: { "Accept": "application/json" } });
      const data = await response.json();
      resultEl.textContent = JSON.stringify(data, null, 2);
    } catch (error) {
      resultEl.textContent = String(error);
    }
  }

  function wireUi() {
    const pingButton = document.getElementById("pingButton");
    if (pingButton) {
      pingButton.addEventListener("click", pingHealth);
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    log("Frontend initialized");
    wireUi();
  });
})();