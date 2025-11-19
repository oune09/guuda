import React from "react";
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Connexion from "./page/connexion.jsx";
import Inscription from "./page/inscription.jsx"
import Superadmin from "./page/superadmin.jsx"
import Ville  from "./page/superAdmin/ville.jsx";
import Secteur from "./page/superAdmin/secteur.jsx";
import Unite  from "./page/superAdmin/unite.jsx";
import Admin  from "./page/superAdmin/admin.jsx";
import Autorite  from "./page/superAdmin/autorite.jsx";
import Organisation  from "./page/superAdmin/organisation.jsx";
import Menu from "./page/superAdmin/menu.jsx";
import Header from "./page/header.jsx";

ReactDOM.createRoot(document.getElementById('root')).render(
  <BrowserRouter>
    <Routes>
      <Route path="/" element={<Connexion />} />
      <Route path="/connexion" element={<Connexion />} />
      <Route path="/inscription" element={<Inscription />} />
      <Route path="/superadmin" element={<Superadmin />} />
      <Route path="/superAdmin/ville" element={<Ville/>} />
      <Route path="/superAdmin/secteur" element={<Secteur/>} />
      <Route path="/superAdmin/unite" element={<Unite/>} />
      <Route path="/superAdmin/admin" element={<Admin/>} />
      <Route path="/superAdmin/autorite" element={<Autorite/>} />
      <Route path="/superAdmin/organisation" element={<Organisation/>} />
      <Route path="/header" element={<Header />} />
      <Route path="/superAdmin/menu" element={<Menu/>} />
    </Routes>
  </BrowserRouter>
);
