import React ,{useState} from 'react';
import axios from 'axios';
export default function connexion(){
    const [email, setEmail] = useState('');
    const [mot_de_passe, setMot_de_passe] = useState('');
    const [message, setMessage] = useState('');

    const handleSubmit = async (e) => {
    e.preventDefault(); // empêche le rechargement de la page

    try {
      const response = await axios.post("/connexion", {
        email_utilisateur: email,
        mot_de_passe: motDePasse,
      });

      setMessage(response.data.message);
      console.log("Réponse du serveur :", response.data);
    } catch (error) {
      if (error.response) {
        setMessage(error.response.data.message);
      } else {
        setMessage("Erreur de connexion au serveur");
      }
    }
  };
   
  return(
    <div>
        <form>
            <input type="email" value={email} onChange={e=> setEmail(e.target.value)} /> <br />
            <input type="password" valu={mot_de_passe} onChange={e=> setMot_de_passe(e.target.value)}/> <br />
            <button type="submit"> se connecter</button>
            {message && <div>{message}</div>}
        </form>
    </div>
  )

}