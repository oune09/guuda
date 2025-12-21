import React, { useEffect, useState } from "react";
import axios from "axios";
import Header from "../header";

export default function CreerIncident() {
    const [coordonnees, setCoordonnees] = useState({ latitude: null, longitude: null });
    const [villes, setVilles] = useState([]);
    const [secteurs, setSecteurs] = useState([]);
    const [preuves, setPreuves] = useState([]);
    const [chargement, setChargement] = useState(false);
    const [form, setForm] = useState({
        description: "",
        type: "",
        date_incident: "",
        priorite: "",
        secteur_id: "",
        utilisateur_id: "",
        ville_id: "",
        statut_incident: "",
    });

    useEffect(() => {
        const chargerVilles = async () => {
            try {
                setChargement(true);
                const res = await axios.get("/api/villes");
                
                // Gérer les différents formats de réponse
                let donneesVilles = [];
                
                if (Array.isArray(res.data)) {
                    donneesVilles = res.data;
                } else if (res.data && Array.isArray(res.data.data)) {
                    // Si utilisation de Laravel Resources avec wrapper
                    donneesVilles = res.data.data;
                } else if (res.data && res.data.villes) {
                    // Si la réponse a une propriété villes
                    donneesVilles = res.data.villes;
                }
                
                console.log("Données villes traitées:", donneesVilles);
                setVilles(donneesVilles);
            } catch (err) {
                console.error("Erreur lors du chargement des villes:", err);
                setVilles([]);
            } finally {
                setChargement(false);
            }
        };

        chargerVilles();
    }, []);

    const handleVilleChange = async (e) => {
        const ville_id = e.target.value;
        setForm({ ...form, ville_id, secteur_id: "" });

        try {
            const res = await axios.get(`/api/secteurs/${ville_id}`);
            let donneesSecteurs = [];
            
            if (Array.isArray(res.data)) {
                donneesSecteurs = res.data;
            } else if (res.data && Array.isArray(res.data.data)) {
                donneesSecteurs = res.data.data;
            }
            
            setSecteurs(donneesSecteurs);
        } catch (err) {
            console.error("Erreur lors du chargement des secteurs:", err);
            setSecteurs([]);
        }
    };

    const handlePreuves = (e) => {
        setPreuves(e.target.files);
    };

    const handleChange = (e) => {
        setForm({ ...form, [e.target.name]: e.target.value })
    };

    const geoLocalisation = () => {
        if (!navigator.geolocation) {
            alert("La géolocalisation n'est pas supportée !");
            return;
        }

        navigator.geolocation.getCurrentPosition(async (pos) => {
            const latitude = pos.coords.latitude;
            const longitude = pos.coords.longitude;

            setCoordonnees({ latitude, longitude });

            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
                );
                const data = await res.json();
                console.log("Adresse :", data.display_name);
            } catch (err) {
                console.error("Erreur adresse :", err);
            }
        });
    };

    const handSubmit = async (e) => {
        e.preventDefault();

        const payload = {
            ...form,
            longitude: coordonnees.longitude,
            latitude: coordonnees.latitude,
        };

        try {
            const res = await axios.post("/incident/creerIncident", payload);
            alert("Incident créé !");
            console.log(res.data);
        } catch (err) {
            console.log(err.response?.data);
            alert("Erreur lors de la création de l'incident.");
        }
    };

    return (
        <div>
             <Header />
            <h1>Signalez un Incident</h1>
            <button onClick={geoLocalisation}>Me Localiser</button>
            <form onSubmit={handSubmit}>
                <label>Description</label>
                <textarea name='description' onChange={handleChange} required></textarea>

                <label>Date</label>
                <input type="date" name="date_incident" onChange={handleChange} required></input>

                <label>Priorité</label>
                <select name="priorite" value={form.priorite} onChange={handleChange} required>
                    <option value="">Choisir une priorité</option>
                    <option value="faible">Faible</option>
                    <option value="moyenne">Moyenne</option>
                    <option value="elevee">Elevée</option>
                </select>

                <label>Ville</label>
                <select value={form.ville_id} onChange={handleVilleChange} required>
                    <option value="">Choisir une ville</option>
                    {chargement ? (
                        <option>Chargement des villes...</option>
                    ) : (
                        Array.isArray(villes) && villes.map(v => (
                            <option key={v.id} value={v.id}>{v.nom_ville}</option>
                        ))
                    )}
                </select>

                <label>Secteur</label>
                <select
                    name="secteur_id"
                    value={form.secteur_id}
                    onChange={handleChange}
                    required
                >
                    <option value="">Choisir un secteur</option>
                    {Array.isArray(secteurs) && secteurs.map(s => (
                        <option key={s.id} value={s.id}>{s.nom_secteur}</option>
                    ))}
                </select>

                <label>Preuves</label>
                <input type="file" multiple onChange={handlePreuves} />
                <button type="submit">Créer</button>
            </form>
        </div>
    );
}