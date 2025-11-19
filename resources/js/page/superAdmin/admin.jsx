import React , {useState, useEffect} from "react";
import axios from "axios";

export default function Admin(){
    const [Admin, setAdmin] = useState([]);
    const [message,setMessage] = useState("");

    const fetchadmin = async()=>{
        try{
            const res = await axios.get("/superadmin/admin");
            setAdmin(res.data.admin||[]);
        }
        catch(err){
            setMessage("Erreur Lors du chargement des administrateurs");
        }
    }

    useEffect(() => {
        fetchadmin();
    }, []);

    const retrograderAdmin = async(id)=>{
        if(!window.confirm("Vouler vous vraiment retrograder cet adminisatrateur?"));
        try{
            await axios.get(`/superadmin/retrograderAdmin/${id}`);
            nouveauRole("autorite"),
            unite_id=null;
            setMessage("Administrateur retrograder avec succes");
        }
        catch(err){
            setMessage(err.response?.data?.message||"Erreur Lors de La retrogradation");
        }
    }

    return(
     <div className="p-6">
           <h1 className="text-2xl font-bold mb-4">Gestion des Administrateurs</h1>
           {message && <div className="mb-4 p-2 bg-green-100 text-green-700">{message}</div>}
            <table className="w-full border-collapse border">
        <thead>
          <tr className="bg-gray-200">
            <th className="border p-2">ID</th>
            <th className="border p-2">Nom de l'unite</th>
            <th className="border p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          {Admin.map((admin) => (
            <tr key={admin.id}>
              <td className="border p-2">{admin.id}</td>
              <td className="border p-2">{admin.unite_id}</td>
              <td className="border p-2 flex gap-2">
                <button
                  onClick={() => retrograderAdmin(admin.id)}
                  className="bg-yellow-500 text-white p-1 rounded"
                >
                  Retrograder
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
     </div>
    );
}