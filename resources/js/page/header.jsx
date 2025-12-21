import {Link} from "react-router-dom";
import React,{useState,useEffect,useRef} from "react";

export default function Header({user, onLogout}){
  const [open, setOpen] = useState(false);
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const menuRef = useRef(null);

  // Fonction de déconnexion avec appel API
  const deconnexion = async () => {
    setIsLoggingOut(true);

    try {
      const token = localStorage.getItem('auth_token');

      const response = await fetch('http://localhost:8000/api/deconnexion', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        // Supprimer les données d'authentification
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');

        // Appeler le callback parent si fourni
        if (typeof onLogout === 'function') {
          onLogout();
        } else {
          // Rediriger vers la page de connexion
          window.location.href = '/connexion';
        }
      } else {
        const errorData = await response.json().catch(() => ({}));
        console.error('Erreur lors de la déconnexion:', errorData);
        alert('Erreur lors de la déconnexion');
      }
    } catch (error) {
      console.error('Erreur réseau:', error);
      alert('Erreur de réseau lors de la déconnexion');
    } finally {
      setIsLoggingOut(false);
      setOpen(false);
    }
  };

  const toggleMenu = () => {
    setOpen(prev => !prev);
  };

  // Fermer le menu en cliquant à l'extérieur
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  return(
    <header className="header" ref={menuRef}>
      <h1 className="logo" ></h1>

      <div className="profil" onClick={toggleMenu} role="button" tabIndex={0}>
        <img
          src={user?.photo || '/default-avatar.png'}
          alt="photo de profil"
          className="photo"
        />
        <span className="nom">{(user?.nom || '') + ' ' + (user?.prenom || '')}</span>
      </div>

      {open && (
        <div className="profil1">
          <Link to="/utilisateur/modifier" className="link-profil">Modifier</Link>
          <button
            className="button-deconnexion"
            onClick={deconnexion}
            disabled={isLoggingOut}
          >
            {isLoggingOut ? 'Déconnexion...' : 'Déconnexion'}
          </button>
          
        </div>
      )}
    </header> 
  );
}