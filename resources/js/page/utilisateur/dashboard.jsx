import React, { useEffect, useState } from "react";
import axios from "axios";
import "leaflet/dist/leaflet.css";
import L from "leaflet";
import { useNavigate } from "react-router-dom";
import Header from "../header";
import Menu from "../superAdmin/menu";

export default function IncidentMap() {
  const [map, setMap] = useState(null);
  const navigate = useNavigate();
  useEffect(() => {
    // Initialisation de la carte
    const myMap = L.map("map").setView([12.37, -1.52], 13); // Position par défaut Ouaga
    setMap(myMap);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(myMap);
  }, []);

  useEffect(() => {
    if (!map) return;

    // Charger les incidents
    const fetchIncidents = async () => {
      const res = await axios.post("/citoyen/ListeIncident");
      const incidents = res.data;

      incidents.forEach((inc) => {
        L.marker([inc.latitude, inc.longitude])
          .addTo(map)
          .bindPopup(
            `<b>${inc.type_incident}</b><br>${inc.description_incident}<br>${inc.quartier}, ${inc.ville}`
          );
      });
    };

    fetchIncidents();
  }, [map]);
  const styles = {
    boutonsContainer: {
      marginTop: "20px",
      display: "flex",
      justifyContent: "center",
      gap: "15px"
    },
    boutonPrincipal: {
      padding: "10px 20px",
      background: "#e63946",
      color: "white",
      border: "none",
      borderRadius: "8px",
      cursor: "pointer",
      fontSize: "16px"
    },
    boutonSecondaire: {
      padding: "10px 20px",
      background: "#457b9d",
      color: "white",
      border: "none",
      borderRadius: "8px",
      cursor: "pointer",
      fontSize: "16px"
    }
  };
  return (
     
    <div>
       <Header />
      <h2>Carte des incidents</h2>
      <div id="map" style={{ height: "500px", width: "100%" }}></div>
      <div style={styles.boutonsContainer}>
        <button style={styles.boutonPrincipal} onClick={()=> navigate("/utilisateur/Creerincident")}>
          🚨 Signaler un Nouvel Incident
        </button>
        <button style={styles.boutonSecondaire} onClick={()=> navigate("/utilisateur/alerte")}>
          🔔 Voir les Alertes
        </button>
      </div>
    </div>
     
  );
  
}
