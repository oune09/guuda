import React, {useState,useEffect} from "react";
import axios from "qxios";

export default function incident()
{
    const [incidents,setIncidents]= useState([]);
    const [nouvelleAlerte,setNouvelleAlerte]= useState("");
    const [message,setMessage]= useState("");

    const fetchIncidents = async()=>{
        try{
            const res = await axios.get('/admin/incident');
            setIncidents(res.data.incident || []);
        }
        catch(err){
            setMessage("Erreur Lors du chargement des incidents");
        }
    }

    useEffect(()=>{
        fetchIncidents();
    },[]);

    const confirmerSignalement = async(id)=>{
        try{
            await axios.post('/admin/confirmerSignalement/'+id);
            fetchIncidents();
            setMessage("Signaleùent confirme avec succes");
        }
        catch(err){
            setMessage(err.response?.data?.message || "Erreur Lors de la confirmation du signalement");
        }
    }

    const supprimerIncident = async(id)=>{
        if(!window.confirm("Vouler vous vraiment supprimer cet incident"))
            try{
                await axios.delete('/admin/supprimerIncident/'+id)
                fetchIncidents();
                setMessage("Incident supprimer avec succes");
             }
            catch(err){
                setMessage(err.response?.data?.message || "Erreur Lors de La suppression de l'incident");
            }
    }

    return(
        <div className="p-6">  
            <h2 className="text-2xl font-bold mb-4">Gestion des Incidents</h2>
            {message && <div className="mb-4 p-2 bg-green-100 text-green-700">{message}</div> }
            <table className="w-full border-collapse border">
                <thead>
                    <tr className="bg-gray-200">
                        <th className="border p-2">Date</th>
                        <th className="border p-2">Description</th>
                        <th className="border p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {incident.map((incident) => (
                      <tr key={incident.id}>
                      <td className="border p-2">{incident.id}</td>
                      <td className="border p-2">{incident.nom_secteur}</td>
                      <td className="border p-2 flex gap-2">
                     <button
                        onClick={() => confirmerSignalement(incident.id)}
                        className="bg-yellow-500 text-white p-1 rounded"
                     >
                       Modifier
                     </button>
                     <button
                       onClick={() => supprimerSecteur(incident.id)}
                       className="bg-red-600 text-white p-1 rounded"
                     >
                       Supprimer
                     </button>
                    </td>
                  </tr>
                 ))}
                </tbody>
            </table>
        </div>   )
}