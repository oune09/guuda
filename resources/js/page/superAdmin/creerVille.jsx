import React, { useState } from "react";
import axios from "axios";
import Header from "../header";

export default function Ville() {
  const [nom, setNom] = useState("");
  const [message, setMessage] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const res = await axios.post("/superadmin/creerville", { nom });
      setMessage("Ville créée avec succès !");
      setNom("");
    } catch (err) {
      setMessage("Erreur lors de la création de la ville.");
    }
  };

  return (
    <div className="p-6 shadow rounded bg-white w-full max-w-md mx-auto mt-6">
       <Header />
      <h2 className="text-xl font-semibold mb-4">Créer une Ville</h2>
      {message && <div className="mb-3 p-2 bg-gray-100 rounded">{message}</div>}
      <form onSubmit={handleSubmit} className="space-y-3">
        <input
          type="text"
          placeholder="Nom de la ville"
          className="border p-2 w-full rounded"
          value={nom}
          onChange={(e) => setNom(e.target.value)}
          required
        />
        <button className="w-full bg-blue-600 text-white p-2 rounded">Enregistrer</button>
      </form>
    </div>
  );
}
