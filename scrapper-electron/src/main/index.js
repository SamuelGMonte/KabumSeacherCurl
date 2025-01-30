const { app, BrowserWindow, ipcMain } = require("electron");
const path = require("path");
const fetch = require("node-fetch"); // Se necessário, instale com `npm install node-fetch`

app.whenReady().then(() => {
  const mainWindow = new BrowserWindow({
    width: 800,
    height: 600,
    webPreferences: {
      preload: path.join(__dirname, "..", "preload", "preload.js"),
      contextIsolation: true,
      nodeIntegration: false,
    },
  });

  mainWindow.loadFile(path.join(__dirname, "..", "renderer", "index.html"));
});

ipcMain.handle("scraper-request", async (_, form) => {
  try {
    const response = await fetch("http://localhost:8000/api/ApiScraper.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' },
      body: JSON.stringify(form),
    });

    const contentType = response.headers.get("Content-Type");

    if (contentType && contentType.includes("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")) {
      // Se a resposta for um arquivo (Excel)
      const blob = await response.blob();

      // Retorna o blob como um buffer ou como uma URL de objeto (para facilitar o download)
      const fileBuffer = await blob.arrayBuffer();  // Converte blob para um buffer
      return { file: fileBuffer };
    } else {
      // Se for JSON
      const data = await response.json();
      return data;
    }
  } catch (error) {
    console.error("Erro ao conectar à API:", error);
    return { error: "Erro ao conectar à API", details: error.message };
  }
});
