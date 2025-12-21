import React,{useState,useEffect} from "react";
import axios from "axios";
import Header from "../header";

export default function Alerte()
{
    const [alertes,setAlerte] = useState([]);
    const [nouvelleAlerte,setNouvelleAlerte] = useState("");
    const [ùessage,setMessage] = useState("");

    const fetchAlertes = async()=>{
        try{
            const res = await axios.get('/admin/adminalerte');
            setAlerte(res.data.alertes || []);
        }
        catch(err){
            setMessage("Erreur Lors du chargement des alertes");
        }
    }

    useEffect(()=>{
        fetchAlertee();
    },[]);

    const creerAlerte = async()=>{
        try{
            await axios.post('/admin/creerAlerte',{description:nouvelleAlerte});
            fetchAlertes();
            setNouvelleAlerte("");
            setMessage('Alerte creer avec succes');
        }
        catch(err){
            setMessage(err.response?.data?.message || "Erreur Lors de la cretion  de l'alerte");
        }
    }
    const supprimerAlerte = async(id)=>{
        if(!window.confirm("Vouler vous vraiment supprimer cette alerte"))
         try {
              await axios.delete('/admin/supprimerAlerte/'+id);
              fetchAlertes();
              setMessage("Alerte supprimer avec succes");
            }
            catch(err){
                setMessage(err.response?.data?.message || "Erreur Lors de la suppression de l'alerte ");
            }
    }
    const modifierAlerte = async(id)=>{
        try{
            await axios.post('/admin/modifierAlerte/'+id,{message:nouvelleAlerte});
            fetchAlertes();
            setNouvelleAlerte("");
            setMessage('Alerte modifier avec succes');
        }
        catch(err){
            setMessage(err.response?.data?.message||"Erreur Lors de la modification de l'allerte")
        }
    }
    return(
        <div>
             <Header />
            <h1>Gestion des Alertes</h1>
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
                    {alerte.map((alerte) => (
                      <tr key={alerte.id}>
                      <td className="border p-2">{alerte.date_alerte}</td>
                      <td className="border p-2">{alerte.message}</td>
                      <td className="border p-2 flex gap-2">
                     <button
                        onClick={() => modifierAlerte(alerte.id)}
                        className="bg-yellow-500 text-white p-1 rounded"
                     >
                       Modifier
                     </button>
                     <button
                       onClick={() => supprimerAlerte(alerte.id)}
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
    )
}