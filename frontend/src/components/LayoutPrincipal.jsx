import { NavLink, Outlet, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import {
  IconoPanel, IconoAuto, IconoPersonas, IconoUsuarios, IconoSalir, IconoVentas,
} from './Iconos'

const TITULOS = {
  '/': { titulo: 'Panel principal', sub: 'Resumen general de operaciones' },
  '/clientes': { titulo: 'Clientes', sub: 'Gestión de la cartera de clientes' },
  '/autos': { titulo: 'Inventario de autos', sub: 'Vehículos registrados en el sistema' },
  '/ventas': { titulo: 'Ventas', sub: 'Seguimiento de consultas y negociaciones' },
  '/usuarios': { titulo: 'Usuarios del sistema', sub: 'Administración de cuentas de vendedores' },
}

export default function LayoutPrincipal() {
  const { perfil, cerrarSesion, esAdmin } = useAuth()
  const location = useLocation()
  const info = TITULOS[location.pathname] || { titulo: 'AUTOLAND', sub: '' }

  const iniciales = (perfil?.nombre_real || '?')
    .split(' ')
    .map((p) => p[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()

  return (
    <div className="layout">
      <aside className="sidebar">
        <div className="sidebar-marca">
          <div className="sidebar-marca-icono">A</div>
          <div>
            <div className="sidebar-marca-texto">AUTOLAND</div>
            <div className="sidebar-marca-sub">Panel interno</div>
          </div>
        </div>

        <nav className="sidebar-nav">
          <div className="sidebar-seccion">General</div>
          <NavLink to="/" end className={({ isActive }) => `sidebar-link ${isActive ? 'activo' : ''}`}>
            <IconoPanel /> Panel principal
          </NavLink>

          <div className="sidebar-seccion">Gestión</div>
          <NavLink to="/clientes" className={({ isActive }) => `sidebar-link ${isActive ? 'activo' : ''}`}>
            <IconoPersonas /> Clientes
          </NavLink>
          <NavLink to="/autos" className={({ isActive }) => `sidebar-link ${isActive ? 'activo' : ''}`}>
            <IconoAuto /> Autos
          </NavLink>
          <NavLink to="/ventas" className={({ isActive }) => `sidebar-link ${isActive ? 'activo' : ''}`}>
            <IconoVentas /> Ventas
          </NavLink>

          {esAdmin && (
            <>
              <div className="sidebar-seccion">Administración</div>
              <NavLink to="/usuarios" className={({ isActive }) => `sidebar-link ${isActive ? 'activo' : ''}`}>
                <IconoUsuarios /> Usuarios
              </NavLink>
            </>
          )}
        </nav>

        <div className="sidebar-pie">
          <div className="sidebar-usuario">
            <div className="sidebar-avatar">{iniciales}</div>
            <div className="sidebar-usuario-info">
              <div className="sidebar-usuario-nombre">{perfil?.nombre_real}</div>
              <div className="sidebar-usuario-rol">{perfil?.rol === 'admin' ? 'Administrador' : 'Vendedor'}</div>
            </div>
            <button className="sidebar-logout" onClick={cerrarSesion} aria-label="Cerrar sesión" title="Cerrar sesión">
              <IconoSalir />
            </button>
          </div>
        </div>
      </aside>

      <div className="contenido">
        <header className="topbar">
          <div>
            <div className="topbar-titulo">{info.titulo}</div>
            <div className="topbar-sub">{info.sub}</div>
          </div>
        </header>

        <main className="pagina">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
