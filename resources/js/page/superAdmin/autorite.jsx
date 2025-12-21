import React , {useState, useEffect} from "react";
import axios from "axios";
import Header from "../header";

export default function Autorite(){
    const [Autorite, setAutorite] = useState([]);
    const [message,setMessage] = useState("");

    const fetchautorite = async()=>{
        try{
            const res = await axios.get("/superadmin/autorite");
            setAutorite(res.data.autorite||[]);
        }
        catch(err){
            setMessage("Erreur Lors du chargement des administrateurs");
        }
    }

    useEffect(() => {
        fetchautorite();
    }, []);

    const promouvoirAutorite = async(id)=>{
        if(!window.confirm("Vouler vous vraiment promouvoir cet administrateur?"));
        try{
            await axios.get(`/superadmin/promouvoirAutorite/${id}`);
            nouveauRole:"administrateur",
            unite_id=null;
            setMessage("Administrateur promu avec succes");
        }
        catch(err){
            setMessage(err.response?.data?.message||"Erreur Lors de La retrogradation");
        }
    }

    return(
     <div className="p-6">
       <Header />
           <h1 className="text-2xl font-bold mb-4">Gestion des Autorite</h1>
           {message && <div className="mb-4 p-2 bg-green-100 text-green-700">{message}</div>}
            <table className="w-full border-collapse border">
        <thead>
          <tr className="bg-gray-200">
            <th className="border p-2">ID</th>
            <th className="border p-2">Nom </th>
            <th className="border p-2">Nom </th>
            <th className="border p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          {Autorite.map((autorite) => (
            <tr key={autorite.id}>
              <td className="border p-2">{autorite.id}</td>
              <td className="border p-2">{autorite.nom}</td>
               <td className="border p-2">{autorite.prenom}</td>
              <td className="border p-2 flex gap-2">
                <button
                  onClick={() => promouvoirAutorite(autorite.id)}
                  className="bg-yellow-500 text-white p-1 rounded"
                >
                  Promouvoir
                </button>
                <button
                  onClick={() => detailAutorite(autorite.id)}
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
    );
}