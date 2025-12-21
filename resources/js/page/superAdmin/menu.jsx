import React from "react";
import {Link} from "react-router-dom";

const classNames = (...classes) => classes.filter(Boolean).join(' ');
const Users = (props) => (<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></svg>);
const Shield = (props) => (<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>);
const Building = (props) => (<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}><rect x="3" y="2" width="18" height="20" rx="2" ry="2" /><path d="M7 10h.01" /><path d="M11 10h.01" /><path d="M15 10h.01" /><path d="M7 14h.01" /><path d="M11 14h.01" /><path d="M15 14h.01" /><path d="M7 18h.01" /><path d="M11 18h.01" /><path d="M15 18h.01" /></svg>);
const MapPin = (props) => (<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}><path d="M12 22s8-4 8-10c0-4.42-3.58-8-8-8s-8 3.58-8 8c0 6 8 10 8 10z" /><circle cx="12" cy="10" r="3" /></svg>);
const Home = (props) => (<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" /><polyline points="9 22 9 12 15 12 15 22" /></svg>);
const Settings = (props) => (<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.47a2 2 0 0 1-1 1.73l-.44.25a2 2 0 0 1-2 0l-.1-.06a2 2 0 0 0-2.73-2.73l-.06-.1a2 2 0 0 0 0-2l.25-.44a2 2 0 0 1 1.73-1L2 12.22v-.44a2 2 0 0 0 2 2h.47a2 2 0 0 1 1.73 1l.25.44a2 2 0 0 1 0 2l-.06.1a2 2 0 0 0-2.73 2.73l-.1.06a2 2 0 0 0-2 0l-.44-.25a2 2 0 0 1-1.73-1L2 11.78v.44a2 2 0 0 0 2 2h.47a2 2 0 0 1 1.73 1l.25.44a2 2 0 0 1 0 2l-.06.1a2 2 0 0 0 2.73 2.73l.1.06a2 2 0 0 0 2 0l.44-.25a2 2 0 0 1 1.73-1L12 21.78v.44a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.47a2 2 0 0 1 1-1.73l.44-.25a2 2 0 0 1 2 0l.1.06a2 2 0 0 0 2.73 2.73l.06.1a2 2 0 0 0 2 0l.44-.25a2 2 0 0 1 1.73-1L22 12.22v-.44a2 2 0 0 0-2-2h-.47a2 2 0 0 1-1.73-1l-.25-.44a2 2 0 0 1 0-2l.06-.1a2 2 0 0 0 2.73-2.73l.1-.06a2 2 0 0 0 2 0l.44.25a2 2 0 0 1 1.73 1L22 11.78z" /><circle cx="12" cy="12" r="3" /></svg>);
const ChevronRight = (props) => (<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}><polyline points="9 18 15 12 9 6" /></svg>);
const MenuIcon = (props) => (<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}><line x1="4" y1="12" x2="20" y2="12" /><line x1="4" y1="6" x2="20" y2="6" /><line x1="4" y1="18" x2="20" y2="18" /></svg>);
const X = (props) => (<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>);

const Button = ({ children, className, ...props }) => {
  return (
    <button 
      className={classNames(
        "inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:opacity-50",
        "h-10 w-10 border border-gray-300 hover:bg-gray-100 bg-white", // Classes de base pour "icon/outline"
        className
      )} 
      {...props}
    >
      {children}
    </button>
  );
};

const menuItems = [
    {
      path: "/superadmin/admin",
      label: "Administrateurs",
      icon: Users,
      description: "Gérer les administrateurs"
    },
    {
      path: "/superadmin/autorite",
      label: "Autorités",
      icon: Shield,
      description: "Gérer les autorités"
    },
    {
      path: "/superadmin/unite",
      label: "Unités",
      icon: Home,
      description: "Gérer les unités"
    },
    {
      path: "/superadmin/organisation",
      label: "Organisations",
      icon: Building,
      description: "Gérer les organisations"
    },
    {
      path: "/superadmin/ville",
      label: "Villes",
      icon: MapPin,
      description: "Gérer les villes"
    },
    {
      path: "/superadmin/secteur",
      label: "Secteurs",
      icon: Settings,
      description: "Gérer les secteurs"
    }
];
const Menu = ({ currentPath, handleNavigation }) => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const isActive = (path) => currentPath === path;

  return (
    <>
      {/* Bouton Menu Mobile */}
      <div className="lg:hidden fixed top-4 left-4 z-50">
        <Button
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          className="bg-white/80 backdrop-blur-sm shadow-lg"
        >
          {isMobileMenuOpen ? <X className="h-4 w-4" /> : <MenuIcon className="h-4 w-4" />}
        </Button>
      </div>

      {/* Overlay Mobile */}
      {isMobileMenuOpen && (
        <div 
          className="lg:hidden fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-40"
          onClick={() => setIsMobileMenuOpen(false)}
        />
      )}

      {/* Barre Latérale (Sidebar) */}
      <aside className={classNames(
        "fixed lg:sticky top-0 left-0 h-screen bg-white border-r border-gray-200 z-40 transition-transform duration-300",
        "w-80 flex flex-col shadow-xl lg:shadow-none",
        // Logique responsive
        isMobileMenuOpen ? "translate-x-0" : "-translate-x-full lg:translate-x-0"
      )}>
        {/* En-tête (Header) */}
        <div className="p-6 border-b border-gray-200">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-500 rounded-lg flex items-center justify-center shadow-lg">
              {/* Icône principale */}
              <Shield className="h-5 w-5 text-white" /> 
            </div>
            <div>
              <h1 className="font-bold text-xl text-gray-800">Super Admin</h1>
              <p className="text-sm text-gray-500">Panel de gestion</p>
            </div>
          </div>
        </div>

        {/* Navigation principale */}
        <nav className="flex-1 p-4 overflow-y-auto">
          <ul className="space-y-1">
            {menuItems.map((item) => {
              const IconComponent = item.icon; 
              const active = isActive(item.path);
              
              return (
                <li key={item.path}>
                  {/* Utilisation de <a> simple. À remplacer par <Link to={item.path}> de votre routeur */}
                  <a
                    href={item.path}
                    onClick={(e) => {
                      e.preventDefault();
                      handleNavigation(item.path); // Utilise la fonction passée en prop
                      setIsMobileMenuOpen(false);
                    }}
                    className={classNames(
                      "flex items-center gap-3 w-full p-3 rounded-xl text-sm font-medium transition-all duration-200 group",
                      "hover:bg-gray-100 hover:text-gray-800",
                      active 
                        ? "bg-blue-50 text-blue-700 font-semibold shadow-sm" 
                        : "text-gray-600"
                    )}
                  >
                    <div className={classNames(
                      "p-2 rounded-lg transition-colors flex items-center justify-center",
                      active 
                        ? "bg-blue-600 text-white shadow-md" 
                        : "bg-gray-200 text-gray-500 group-hover:bg-blue-100 group-hover:text-blue-600"
                    )}>
                      <IconComponent className="h-4 w-4" />
                    </div>
                    
                    <div className="flex-1 min-w-0">
                      <div className="font-medium text-base leading-snug">{item.label}</div>
                      <div className="text-xs truncate" style={{ color: active ? 'inherit' : '#9ca3af' }}>
                        {item.description}
                      </div>
                    </div>
                    
                    <ChevronRight className={classNames(
                      "h-4 w-4 transition-transform duration-200",
                      active && "text-blue-700",
                      "group-hover:translate-x-0.5"
                    )} />
                  </a>
                </li>
              );
            })}
          </ul>
        </nav>

        {/* Pied de page du menu */}
        <div className="p-6 border-t border-gray-200">
          <div className="text-xs text-gray-500 text-center">
            GUUDA Platform v1.0
          </div>
        </div>
      </aside>
    </>
  );
};

export default Menu;