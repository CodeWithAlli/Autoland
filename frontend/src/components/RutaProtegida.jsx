import { Navigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function RutaProtegida({ children, soloAdmin = false }) {
  const { sesion, perfil, cargando } = useAuth()

  if (cargando) {
    return (
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100vh', color: 'var(--texto-suave)' }}>
        Cargando…
      </div>
    )
  }

  if (!sesion || !perfil) {
    return <Navigate to="/login" replace />
  }

  if (soloAdmin && perfil.rol !== 'admin') {
    return <Navigate to="/" replace />
  }

  return children
}
