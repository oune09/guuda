import React, { useState } from "react";
import axios from "axios";

export default function CreerSuperAdmin() {
    const [form, setForm] = useState({
        nom_utilisateur: "",
        prenom_utilisateur: "",
        email_utilisateur: "",
        cnib: "",
        mot_de_passe_utilisateur: "",
        confirmation_mot_de_passe: "",
        date_naissance_utilisateur: "",
        telephone_utilisateur: "",
        matricule: ""
    });
    const [message, setMessage] = useState("");
    const [erreur, setErreur] = useState("");
    const [chargement, setChargement] = useState(false);

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setMessage("");
        setErreur("");
        setChargement(true);

        // Validation côté client
        if (form.mot_de_passe_utilisateur !== form.confirmation_mot_de_passe) {
            setErreur("Les mots de passe ne correspondent pas");
            setChargement(false);
            return;
        }

        if (form.mot_de_passe_utilisateur.length < 6) {
            setErreur("Le mot de passe doit contenir au moins 6 caractères");
            setChargement(false);
            return;
        }

        try {
            const payload = {
                nom_utilisateur: form.nom_utilisateur,
                prenom_utilisateur: form.prenom_utilisateur,
                email_utilisateur: form.email_utilisateur,
                cnib: form.cnib,
                mot_de_passe_utilisateur: form.mot_de_passe_utilisateur,
                date_naissance_utilisateur: form.date_naissance_utilisateur,
                telephone_utilisateur: form.telephone_utilisateur,
                matricule: form.matricule
            };

            const res = await axios.post("/api/superadmin/creer", payload);
            
            setMessage("Super administrateur créé avec succès !");
            setForm({
                nom_utilisateur: "",
                prenom_utilisateur: "",
                email_utilisateur: "",
                cnib: "",
                mot_de_passe_utilisateur: "",
                confirmation_mot_de_passe: "",
                date_naissance_utilisateur: "",
                telephone_utilisateur: "",
                matricule: ""
            });

        } catch (err) {
            console.error("Erreur détaillée:", err.response);
            
            if (err.response?.data?.errors) {
                const errors = Object.values(err.response.data.errors).flat();
                setErreur(errors.join(", "));
            } else if (err.response?.data?.message) {
                setErreur(err.response.data.message);
            } else {
                setErreur("Erreur lors de la création du super administrateur");
            }
        } finally {
            setChargement(false);
        }
    };

    return (
        <div style={styles.container}>
            <h2>Créer un Super Administrateur</h2>
            
            {message && <div style={styles.success}>{message}</div>}
            {erreur && <div style={styles.error}>{erreur}</div>}

            <form onSubmit={handleSubmit} style={styles.form}>
                <div style={styles.formGroup}>
                    <label>Nom *</label>
                    <input
                        type="text"
                        name="nom_utilisateur"
                        value={form.nom_utilisateur}
                        onChange={handleChange}
                        required
                        style={styles.input}
                    />
                </div>

                <div style={styles.formGroup}>
                    <label>Prénom *</label>
                    <input
                        type="text"
                        name="prenom_utilisateur"
                        value={form.prenom_utilisateur}
                        onChange={handleChange}
                        required
                        style={styles.input}
                    />
                </div>

                <div style={styles.formGroup}>
                    <label>Email *</label>
                    <input
                        type="email"
                        name="email_utilisateur"
                        value={form.email_utilisateur}
                        onChange={handleChange}
                        required
                        style={styles.input}
                    />
                </div>

                <div style={styles.formGroup}>
                    <label>CNIB *</label>
                    <input
                        type="text"
                        name="cnib"
                        value={form.cnib}
                        onChange={handleChange}
                        required
                        style={styles.input}
                    />
                </div>

                <div style={styles.formGroup}>
                    <label>Date de naissance *</label>
                    <input
                        type="date"
                        name="date_naissance_utilisateur"
                        value={form.date_naissance_utilisateur}
                        onChange={handleChange}
                        required
                        style={styles.input}
                    />
                </div>

                <div style={styles.formGroup}>
                    <label>Téléphone *</label>
                    <input
                        type="tel"
                        name="telephone_utilisateur"
                        value={form.telephone_utilisateur}
                        onChange={handleChange}
                        required
                        style={styles.input}
                    />
                </div>

                <div style={styles.formGroup}>
                    <label>Matricule *</label>
                    <input
                        type="text"
                        name="matricule"
                        value={form.matricule}
                        onChange={handleChange}
                        required
                        style={styles.input}
                    />
                </div>

                <div style={styles.formGroup}>
                    <label>Mot de passe *</label>
                    <input
                        type="password"
                        name="mot_de_passe_utilisateur"
                        value={form.mot_de_passe_utilisateur}
                        onChange={handleChange}
                        required
                        style={styles.input}
                    />
                </div>

                <div style={styles.formGroup}>
                    <label>Confirmer le mot de passe *</label>
                    <input
                        type="password"
                        name="confirmation_mot_de_passe"
                        value={form.confirmation_mot_de_passe}
                        onChange={handleChange}
                        required
                        style={styles.input}
                    />
                </div>

                <button 
                    type="submit" 
                    style={styles.button}
                    disabled={chargement}
                >
                    {chargement ? "Création..." : "Créer le Super Admin"}
                </button>
            </form>
        </div>
    );
}

const styles = {
    container: {
        maxWidth: "500px",
        margin: "0 auto",
        padding: "20px"
    },
    form: {
        display: "flex",
        flexDirection: "column",
        gap: "15px"
    },
    formGroup: {
        display: "flex",
        flexDirection: "column",
        gap: "5px"
    },
    input: {
        padding: "10px",
        border: "1px solid #ddd",
        borderRadius: "4px",
        fontSize: "16px"
    },
    button: {
        padding: "12px",
        backgroundColor: "#007bff",
        color: "white",
        border: "none",
        borderRadius: "4px",
        fontSize: "16px",
        cursor: "pointer"
    },
    success: {
        padding: "10px",
        backgroundColor: "#d4edda",
        color: "#155724",
        border: "1px solid #c3e6cb",
        borderRadius: "4px",
        marginBottom: "15px"
    },
    error: {
        padding: "10px",
        backgroundColor: "#f8d7da",
        color: "#721c24",
        border: "1px solid #f5c6cb",
        borderRadius: "4px",
        marginBottom: "15px"
    }
};