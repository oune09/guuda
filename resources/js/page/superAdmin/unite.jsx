import React,{useState,useEffect} from "react";
import axios from "axios";
import Header from "../header";

export default function Unite(){
    const [unites, setUnites] = useState([]);
    const [nouvelleUnite, setNouvelleUnite] = useState("");
    const [message,setMessage] =useState("");

    const fetchUnites = async()=>{
        try{
            const res = await axios.get("/superadmin/unites");
            setUnites(res.data.unites || []);
        }
        catch(err){
            setMessage("Erreur Lors du chargemant des unites");
        }
    }

    useEffect(()=>{
        fetchUnites();
    },[]);

    const creerUnite = async()=>{
        try{
            await axios.post("/superadmin/creerunite",{nom_unite:nouvelleUnite});
            setNouvelleUnite("");
            fetchUnites();
            setMessage("Unite creer avec succes");
        }
        catch(err){
            setMessage(err.response?.data?.message ||"Erreur Lors de la creation");
        }
    };

    const supprimerUnite = async(id)=>{
        if(!window.confirm("vouler vous vraiment supprimer cette unite?"));
        try{
            await axios.delete(`/superadmin/supprimerUnite/${id}`);
            fetchUnites();
            setMessage("unite supprimer avec succes");
        }
        catch(err){
            setMessage(err.response?.data?.message || "Erreur Lors de la suppression");
        }
    };

    const modifierUnite = async(unites)=>{
        const nouveauNom = window.prompt("Modifier le nom de l'unite:", unites.nom_unite);
        if(!nouveauNom) return;
        try{
            await axios.put(`/superadmin/modifierUnite/${unites.id}`,{nom_unite:nouveauNom});
            fetchUnites();
            setMessage("unite modifiee avec succes");
        }
        catch(err){
            setMessage(err.response?.data?.message || "Erreur Lors de la modification");
        }
    };

    return(
        <div className="p-6">
           <Header />
      <h1 className="text-2xl font-bold mb-4">Gestion des Unites</h1>

      {message && <div className="mb-4 p-2 bg-green-100 text-green-700">{message}</div>}

      <div className="mb-4 flex gap-2">
        <input
          type="text"
          placeholder="Nom de la nouvelle unite"
          value={nouvelleUnite}
          onChange={(e) => setNouvelleUnite(e.target.value)}
          className="border p-2 rounded"
        />
        <button
          onClick={creerUnite}
          className="bg-blue-600 text-white p-2 rounded"
        >
          Nouveau
        </button>
      </div>

      <table className="w-full border-collapse border">
        <thead>
          <tr className="bg-gray-200">
            <th className="border p-2">ID</th>
            <th className="border p-2">Nom de l'unite</th>
            <th className="border p-2">ville</th>
            <th className="border p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          {unites.map((unite) => (
            <tr key={unite.id}>
              <td className="border p-2">{unite.id}</td>
              <td className="border p-2">{unite.nom_unite}</td>
              <td className="border p-2 flex gap-2">
                <button
                  onClick={() => modifierUnite(unite)}
                  className="bg-yellow-500 text-white p-1 rounded"
                >
                  Modifier
                </button>
                <button
                  onClick={() => modifierUnite(unite)}
                  className="bg-yellow-500 text-white p-1 rounded"
                >
                Details
                </button>
                <button
                  onClick={() => supprimerUnite(unite.id)}
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