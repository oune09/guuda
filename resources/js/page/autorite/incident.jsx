import React,{useState,useEffect} from "react";
import axios from "axios";
import Header from "../header";

export default function Autorite()
{
    const [autorites,setAutorite] = useState([]);
    const [detail, setDetail] = useState(null);
    const [message,setMessage] = useState("");

    const fetchAutorites = async()=>{
        try{
            const res = await axios.get('/admin/adminAutorite');
            setAutorite(res.data.autorites ||[]);
        }
        catch(err){
            setMessage("Erreur Lors du chargement des autorites");
        }
    }

   const detailAutorite = async (id) => {
    try {
        const res = await axios.get("/autorite/detailAutorite/" + id);

        if(!res.data || !res.data.autorite){
            throw new Error("Format de réponse inattendu");
        }

        setDetail(res.data.autorite);
        return res.data.autorite;

       } catch (err) {
        console.error(err);
        setMessage("Erreur lors du chargement des détails de l'autorité");
        throw err;
      } 
    };

    return(
        <div>
             <Header />
            <h2>Autorite</h2>
            {message && <div className="mb-4 p-2 bg-green-100 text-green-700">{message}</div> }
            <table className="w-full border-collapse border">
                <thead>
                    <tr className="bg-gray-200">
                        <th className="border p-2">Nom</th>
                        <th className="border p-2">Prenom</th>
                        <th className="border p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {autorite.map((autorite) => (
                      <tr key={autorite.id}>
                      <td className="border p-2">{autorite.nom}</td>
                      <td className="border p-2">{autorite.prenom}</td>
                      <td className="border p-2 flex gap-2">
                     <button
                        onClick={() => detailAutorite(incident.id)}
                        className="bg-yellow-500 text-white p-1 rounded"
                     >
                       Detail
                     </button>
                    </td>
                  </tr>
                 ))}
                </tbody>
            </table>
        </div>
    )
}