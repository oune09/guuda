import React,{useState,useEffect}  from "react";
import axios from "axios";
import Header  from "../header";
import Menu from "./menu";

export default function organisation(){
    const [organisations, setOrganisations] = useState([]);
    const [nouvelleOrganisation, setNouvelleOrganisation] = useState("");
    const[message,setMessage] = useState("");

    const fetchOrganisations = async ()=>{
        try{
            const res = await axios.get("/superadmin/organisation");
            setOrganisations(res.data.organisation||[]);
        }
        catch(err){
            setMessage("Erreur Lors du chargemant des organisations");
        }
    }

    useEffect(()=>{
        fetchOrganisations();
    },[]);
    const creerOrganisation = async()=>{
        try{
            await axios.get("/superAdmin/creersecteur",{nom_secteur:nouveauSecteur});
            setNouveauSecteur("");
            fetchSecteurs();
            setMessage("Secteur creer avec succes");
        }
        catch(errr){
            setMessage(err.response?.data?.message ||"Erreur lors de la creation ");
        }
    };
    
    const supprimerOrganisation = async(id) =>{
        if(!window.confirm("vouler vous vraiment supprimer ce organisation?"));
        try{
            await axios.delete(`/superadmin/supprimerOrganisation/${id}`);
            fetchOrganisations();
            setMessage("organisation supprimer avec succes");
        }
        catch(err){
            setMessage(err.response?.data?.message||"Erreur Lors de la Suppression");
        }
    };
    const modifierOrganisation = async(organisation) =>{
        const nouveauNom = window.prompt("Modifier le nom de l'organisation:", organisation.nom_organisation);
        if(!nouveauNom) return;
        try{
            await axios.put(`/superadmin/modifierOrganisation/${organisation.id}`, {nom_organisation: nouveauNom});
            fetchOrganisations();
            setMessage("Organisation modifiée avec succès");
        }
        catch(err){
            setMessage(err.response?.data?.message||"Erreur lors de la modification");
        }
    };

    return(
        <div className="p-6">
      <h1 className="text-2xl font-bold mb-4">Gestion des Organisations</h1>

      {message && <div className="mb-4 p-2 bg-green-100 text-green-700">{message}</div>}

      <div className="mb-4 flex gap-2">
        <input
          type="text"
          placeholder="Nom de la nouvelle organisation"
          value={nouvelleOrganisation}
          onChange={(e) => setNouvelleOrganisation(e.target.value)}
          className="border p-2 rounded"
        />
        <button
          onClick={creerOrganisation}
          className="bg-blue-600 text-white p-2 rounded"
        >
          Nouveau
        </button>
      </div>

      <table className="w-full border-collapse border">
        <thead>
          <tr className="bg-gray-200">
            <th className="border p-2">ID</th>
            <th className="border p-2">Nom de l'organisation</th>
            <th className="border p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          {organisations.map((organisation) => (
            <tr key={organisation.id}>
              <td className="border p-2">{organisation.id}</td>
              <td className="border p-2">{organisation.nom_organisation}</td>
              <td className="border p-2 flex gap-2">
                <button
                  onClick={() => modifierOrganisation(organisation)}
                  className="bg-yellow-500 text-white p-1 rounded"
                >
                  Modifier
                </button>
                <button
                  onClick={() => supprimerOrganisation(organisation.id)}
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