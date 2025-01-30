document.getElementById("scraper-form").addEventListener("submit", async (e) => {
    e.preventDefault();

    const form = {
        option: document.getElementById("option").value.trim(),
        product_name: document.getElementById("product_name").value.trim(),
        pag: document.getElementById("pag").value.trim(),
        max_price: document.getElementById("max_price").value.trim(),
        min_price: document.getElementById("min_price").value.trim(),
    };

    // Verifica se o `window.electron` está disponível
    if (!window.electron) {
        console.error("window.electron não está definido!");
        document.getElementById("result").innerText = "Erro: integração com Electron não carregada!";
        return;
    }

    try {
        const response = await window.electron.sendScraperRequest(form);
        
        console.log("Resposta do Electron:", response);  // Log para depuração

        // Verifica se a resposta contém o arquivo
        if (response && response.file) {
            // Converte o arrayBuffer de volta para um Blob
            const fileBlob = new Blob([response.file], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
            
            // Cria um URL para o blob e inicia o download
            const url = URL.createObjectURL(fileBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'resultado.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        } else if (response) {
            // Exibe os dados JSON se não for um arquivo
            document.getElementById("result").innerText = JSON.stringify(response, null, 2);
        } else {
            document.getElementById("result").innerText = "Resposta inválida recebida!";
        }
    } catch (error) {
        console.error("Erro na requisição:", error);
        document.getElementById("result").innerText = "Erro na requisição: " + error.message;
    }
});
