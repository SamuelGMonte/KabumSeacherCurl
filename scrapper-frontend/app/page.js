"use client"; 

import { useState, useEffect } from "react";

export default function Home() {
  const [form, setForm] = useState({
    option: "1",
    product_name: "",
    pag: 1,
    max_price: 0,
    min_price: 0,
  });
  const [response, setResponse] = useState(null);
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };


  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
  
    setResponse(null);
    try {
      const res = await fetch("/api/scraper", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });
  
      if (res.ok && res.headers.get("Content-Type") === "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
        const blob = await res.blob();
        downloadFile(blob);
      } else {
        const data = await res.json();
        setResponse(data);
      }
    } catch (error) {
      console.error("Error fetching data:", error);
      setResponse({ error: "Failed to fetch data" });
    }
  
    setLoading(false);
  };

  const downloadFile = async (blob) => {
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'resultado.xlsx';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  };


  return (
    <div>
      <h1>Scraper</h1>
      <form onSubmit={handleSubmit}>
        <label>
          Escolha a opção:
          <select name="option" value={form.option} onChange={handleChange}>
            <option value="1">Kabum</option>
            <option value="2">Mercado Livre</option>
          </select>
        </label>
        <br />
        <label>
          Produto:
          <input type="text" name="product_name" value={form.product_name} onChange={handleChange} required />
        </label>
        <br />
        <label>
          Páginas:
          <input type="number" name="pag" value={form.pag} onChange={handleChange} required />
        </label>
        <br />
        <label>
          Preço Máximo:
          <input type="number" name="max_price" value={form.max_price} onChange={handleChange} />
        </label>
        <br />
        <label>
          Preço Mínimo:
          <input type="number" name="min_price" value={form.min_price} onChange={handleChange} />
        </label>
        <br />
        <button type="submit" disabled={loading}>{loading ? "Buscando..." : "Buscar"}</button>
      </form>

      {response && response.length > 0 && (
        <div>
          <h2>Resultado:</h2>
          <pre>{response[0].message}</pre> 
        </div>
      )}

    </div>
  );
}
