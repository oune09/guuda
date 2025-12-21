import React, { useState, useEffect } from "react";
import axios from "axios";
import Header from "../header";

export default function Villes() {
  const [villes, setVilles] = useState([]);
  const [nouvelleVille, setNouvelleVille] = useState("");
  const [message, setMessage] = useState("");

  // Récupérer la liste des villes
  const fetchVilles = async () => {
    try {
      const res = await axios.get("/superadmin/villes");
      setVilles(res.data.villes || []);
    } catch (err) {
      setMessage("Erreur lors du chargement des villes");
    }
  };

  useEffect(() => {
    fetchVilles();
  }, []);

  // Créer une nouvelle ville
  const creerVille = async () => {
    try {
      await axios.post("/superadmin/creerville", { nom_ville: nouvelleVille });
      setNouvelleVille("");
      fetchVilles();
      setMessage("Ville créée avec succès");
    } catch (err) {
      setMessage(err.response?.data?.message || "Erreur lors de la création");
    }
  };

  // Supprimer une ville
  const supprimerVille = async (id) => {
    if (!window.confirm("Voulez-vous vraiment supprimer cette ville ?")) return;
    try {
      await axios.delete(`/superadmin/supprimerville/${id}`);
      fetchVilles();
      setMessage("Ville supprimée avec succès");
    } catch (err) {
      setMessage(err.response?.data?.message || "Erreur lors de la suppression");
    }
  };

  // Modifier une ville (simple prompt pour l'exemple)
  const modifierVille = async (ville) => {
    const nouveauNom = window.prompt("Modifier le nom de la ville :", ville.nom_ville);
    if (!nouveauNom) return;

    try {
      await axios.put(`/superadmin/modifierville/${ville.id}`, { nom_ville: nouveauNom });
      fetchVilles();
      setMessage("Ville modifiée avec succès");
    } catch (err) {
      setMessage(err.response?.data?.message || "Erreur lors de la modification");
    }
  };

  return (
    <div className="p-6">
       <Header />
      <h1 className="text-2xl font-bold mb-4">Gestion des Villes</h1>

      {message && <div className="mb-4 p-2 bg-green-100 text-green-700">{message}</div>}

      <div className="mb-4 flex gap-2">
        <input
          type="text"
          placeholder="Nom de la nouvelle ville"
          value={nouvelleVille}
          onChange={(e) => setNouvelleVille(e.target.value)}
          className="border p-2 rounded"
        />
        <button
          onClick={creerVille}
          className="bg-blue-600 text-white p-2 rounded"
        >
          Nouveau
        </button>
      </div>

      <table className="w-full border-collapse border">
        <thead>
          <tr className="bg-gray-200">
            <th className="border p-2">ID</th>
            <th className="border p-2">Nom de la ville</th>
            <th className="border p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          {villes.map((ville) => (
            <tr key={ville.id}>
              <td className="border p-2">{ville.id}</td>
              <td className="border p-2">{ville.nom_ville}</td>
              <td className="border p-2 flex gap-2">
                <button
                  onClick={() => modifierVille(ville)}
                  className="bg-yellow-500 text-white p-1 rounded"
                >
                  Modifier
                </button>
                <button
                  onClick={() => supprimerVille(ville.id)}
                  className="bg-red-600 text-white p-1 rounded"
                >
                  Supprimer
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
