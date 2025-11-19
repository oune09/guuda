import React,{useState,useEffect}  from "react";
import axios from "axios";

export default function Secteur(){
    const [secteurs, setSecteurs] = useState([]);
    const [nouveauSecteur, setNouveauSecteur] = useState("");
    const[message,setMessage] =useState("");

    const fetchSecteurs = async ()=>{

        try{
            const res = await axios.get("/superadmin/secteurs");
            setSecteurs (res.data.secteurs ||[]);
        }
        catch(err){
            setMessage("Erreur Lors du chargemant des secteurs");
        }
    }

    useEffect(()=>{
        fetchSecteurs();
    },[]);

    const creerSecteur = async()=>{
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
    
    const supperSecteur = async(id) =>{
        if(!window.confirm("vouler vous vraiment supprimer ce secteur?"));
        try{
            await axios.delete(`/superadmin/supprimerSecteur/${id}`);
            fetchSecteurs();
            setMessage("secteur supprimer avec succes");
        }
        catch(err){
            setMessage(err.response?.data?.message||"Erreur Lors de la Suppression");
        }
    };
    const modifierSecteur = async(Secteur) =>{
        const nouveauNom = window.prompt("Modifier le mon su secteur:",secteur.nom_secteur);
        if(!nouveauNom) return;
        try{
            await axios.put(`/superadmin/modifierSecteur/${secteur.id}`, {nom_secteur: nouveauNom});
            fetchSecteurs();
            setMessage("Secteur modifier avec succes");
        }
        catch(err){
            setMessage(err.response?.data?.message||"Errur Lors de la modification");
        }
    };

    return(
        <div className="p-6">
      <h1 className="text-2xl font-bold mb-4">Gestion des Secteur</h1>

      {message && <div className="mb-4 p-2 bg-green-100 text-green-700">{message}</div>}

      <div className="mb-4 flex gap-2">
        <input
          type="text"
          placeholder="Nom du nouveau secteur"
          value={nouveauSecteur}
          onChange={(e) => setNouveauSecteur(e.target.value)}
          className="border p-2 rounded"
        />
        <button
          onClick={creerSecteur}
          className="bg-blue-600 text-white p-2 rounded"
        >
          Nouveau
        </button>
      </div>

      <table className="w-full border-collapse border">
        <thead>
          <tr className="bg-gray-200">
            <th className="border p-2">ID</th>
            <th className="border p-2">Nom du secteur</th>
            <th className="border p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          {secteurs.map((secteur) => (
            <tr key={secteur.id}>
              <td className="border p-2">{secteur.id}</td>
              <td className="border p-2">{secteur.nom_secteur}</td>
              <td className="border p-2 flex gap-2">
                <button
                  onClick={() => modifierSecteur(secteur)}
                  className="bg-yellow-500 text-white p-1 rounded"
                >
                  Modifier
                </button>
                <button
                  onClick={() => supprimerSecteur(secteur.id)}
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