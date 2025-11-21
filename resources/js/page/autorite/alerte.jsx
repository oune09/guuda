import React,{useState,useEffect} from "react";
import axios from "axios";

export default function Alerte()
{
    const [alerte,setAlerte] = useState([]);
    const [detail, setDetail] = useState(null);
    const [message,setMessage] = useState("");

    const fetchAlerte = async()=>{
        try{
            const res = await axios.get('/admin/alerte');
            setAutorite(res.data.alerte ||[]);
            fetchAlerte();
        }
        catch(err){
            setMessage("Erreur Lors du chargement des alertes");
        }
    }

   const detailAutorite = async (id) => {
    try {
        const res = await axios.get("/admin/detailAlerte/" + id);

        if(!res.data || !res.data.alerte){
            throw new Error("Format de réponse inattendu");
        }

        setDetail(res.data.admin);
        return res.data.admin;

       } catch (err) {
        console.error(err);
        setMessage("Erreur lors du chargement des détails de l'alerte");
        throw err;
      } 
    };

    return(
        <div>
            <h2>Alerte</h2>
            {message && <div className="mb-4 p-2 bg-green-100 text-green-700">{message}</div> }
            <table className="w-full border-collapse border">
                <thead>
                    <tr className="bg-gray-200">
                        <th className="border p-2">Date</th>
                        <th className="border p-2">Message</th>
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
                        onClick={() => detailAlerte(alerte.id)}
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