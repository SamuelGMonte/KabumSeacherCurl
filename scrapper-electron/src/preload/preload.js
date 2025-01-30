const { contextBridge, ipcRenderer } = require("electron");

console.log("Preload carregado!"); // Isso deve aparecer no DevTools do Electron

contextBridge.exposeInMainWorld("electron", {
  sendScraperRequest: async (form) => ipcRenderer.invoke("scraper-request", form),
});
