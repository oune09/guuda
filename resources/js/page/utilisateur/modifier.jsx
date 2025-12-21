import React, { useState } from "react";
import axios from "axios";
import Header from "../header";

export default function Modifier() {
  const [formData, setFormData] = useState({
    nom_utilisateur: "",
    prenom_utilisateur: "",
    email_utilisateur: "",
    mot_de_passe: "",
    mot_de_passe_confirmation: "",
    cnib: "",
    date_naissance_utilisateur: "",
    telephone_utilisateur: "",
    role_utilisateur: "citoyen",
    ville: "",
    secteur: "",
    quartier: "",
    organisation: "",
    matricule: "",
    zone_responsabilite: "",
    statut: "actif",
  });

  const [photo, setPhoto] = useState(null);
  const [message, setMessage] = useState("");

  // Fonction pour mettre à jour les champs du formulaire
  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handlePhoto = (e) => {
    setPhoto(e.target.files[0]);
  };

  // Soumission du formulaire
  const handleSubmit = async (e) => {
    e.preventDefault();

    const data = new FormData();

    for (let key in formData) {
      data.append(key, formData[key]);
    }

    if (photo) {
      data.append("photo", photo);
    }

    try {
      const response = await axios.post("/citoyen/modifierUtilisateur", data, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      setMessage(response.data.message);
      console.log(response.data);
    } catch (error) {
      if (error.response) {
        setMessage(error.response.data.message);
      } else {
        setMessage("Erreur serveur");
      }
    }
  };

  return (
    <div className="p-10 max-w-md mx-auto">
        <Header />
      <h1 className="text-2xl font-bold mb-4">Modification</h1>

      <form onSubmit={handleSubmit} encType="multipart/form-data">
        <input type="text" name="nom_utilisateur" placeholder="Nom" value={formData.nom_utilisateur} onChange={handleChange}/><br />
        <input type="text" name="prenom_utilisateur" placeholder="Prénom" value={formData.prenom_utilisateur} onChange={handleChange}/><br />
        <input type="email" name="email_utilisateur" placeholder="Email" value={formData.email_utilisateur} onChange={handleChange}/><br />
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" value={formData.mot_de_passe} onChange={handleChange} /><br />
        <input type="password" name="mot_de_passe_confirmation" placeholder="Confirmer mot de passe" value={formData.mot_de_passe_confirmation} onChange={handleChange}/><br />
        <input type="text" name="cnib" placeholder="CNIB" value={formData.cnib} onChange={handleChange}/><br />
        <input type="date" name="date_naissance_utilisateur" value={formData.date_naissance_utilisateur} onChange={handleChange} /><br />
        <input type="text" name="telephone_utilisateur" placeholder="Téléphone" value={formData.telephone_utilisateur} onChange={handleChange} /><br />
        <input type="text" name="quartier" placeholder="quartier" value={formData.quartier} onChange={handleChange}/><br />
        <input type="text" name="ville" placeholder="ville" value={formData.ville} onChange={handleChange}/><br />
        <input type="text" name="secteur" placeholder="secteur" value={formData.secteur} onChange={handleChange}/><br />
        <input type="file" name="photo" onChange={handlePhoto}/><br />
        <button type="submit" className="bg-blue-500 text-white px-4 py-2 rounded">Modifier</button>
      </form>
        {message && <p className="mt-4 text-center text-red-500">{message}</p>}
    </div>
  );
}
