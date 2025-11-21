import React from "react";
import {Link} from "react-router-dom";

export default function Menu()
{
    return(
       <div className="menu">
         <aside className="menu1">
            <ul className="menu2">
              <li><Link to="/autorite/incident" className="menu3">Incident</Link></li>
              <li><Link to="/autorite/alerte" className="menu3">Alerte</Link></li>
              <li><Link to="/autorite/secteur" className="menu3">Secteur</Link></li>
            </ul>
         </aside>
       </div>
    )
}