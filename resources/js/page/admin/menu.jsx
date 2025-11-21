import React from "react";
import {Link} from "react-router-dom";

export default function Menu()
{
    return(
       <div className="menu">
         <aside className="menu1">
            <ul className="menu2">
              <li><Link to="/admin/autorite" className="menu3">Autorite</Link></li>
              <li><Link to="/admin/incident" className="menu3">Incident</Link></li>
              <li><Link to="/admin/alerte" className="menu3">Alerte</Link></li>
              <li><Link to="/admin/secteur" className="menu3">Secteur</Link></li>
            </ul>
         </aside>
       </div>
    )
}