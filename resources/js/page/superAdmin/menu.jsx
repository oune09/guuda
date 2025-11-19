import React from "react";
import {Link} from "react-router-dom";

export default function Menu()
{
    return(
       <div className="menu">
         <aside className="menu1">
            <ul className="menu2">
              <li><Link to="/superadmin/admin" className="menu3">Admin</Link></li>
              <li><Link to="/superadmin/autorite" className="menu3">Autorite</Link></li>
              <li><Link to="/superadmin/unite" className="menu3">Unite</Link></li>
              <li><Link to="/superadmin/organisation" className="menu3">Organisation</Link></li>
              <li><Link to="/superadmin/ville" className="menu3">Villes</Link></li>
              <li><Link to="/superadmin/secteur" className="menu3">Secteurs</Link></li>
            </ul>
         </aside>
       </div>
    )
}