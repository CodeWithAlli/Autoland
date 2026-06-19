import { useState } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { IconoCandado, IconoAlerta } from '../components/Iconos'

export default function Login() {
  const { sesion, perfil, iniciarSesion } = useAuth()
  const navigate = useNavigate()
  const [usuario, setUsuario] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [cargando, setCargando] = useState(false)

  if (sesion && perfil) return <Navigate to="/" replace />

  async function manejarSubmit(e) {
    e.preventDefault()
    setError('')
    setCargando(true)
    const res = await iniciarSesion(usuario, password)
    setCargando(false)
    if (!res.ok) {
      setError(res.error)
      return
    }
    navigate('/')
  }

  return (
    <div className="pantalla-login">
      <div className="login-card">
        <div className="login-marca">
          <div className="login-marca-icono">A</div>
          <div>
            <div className="login-marca-texto">AUTOLAND</div>
            <div className="login-marca-sub">Panel de gestión interna</div>
          </div>
        </div>

        <div className="login-encabezado">
          <h1>Inicia sesión</h1>
          <p>Ingresa con tu usuario y contraseña asignados por el administrador.</p>
        </div>

        {error && (
          <div className="mensaje mensaje-err">
            <IconoAlerta size={15} />
            <span>{error}</span>
          </div>
        )}

        <form onSubmit={manejarSubmit}>
          <div className="campo">
            <label htmlFor="usuario">Usuario</label>
            <input
              id="usuario"
              type="text"
              placeholder="admin"
              value={usuario}
              onChange={(e) => setUsuario(e.target.value)}
              autoComplete="username"
              required
            />
          </div>
          <div className="campo">
            <label htmlFor="password">Contraseña</label>
            <input
              id="password"
              type="password"
              placeholder="••••••••"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              autoComplete="current-password"
              required
            />
          </div>
          <button type="submit" className="btn btn-acento login-btn" disabled={cargando}>
            <IconoCandado size={15} />
            {cargando ? 'Verificando…' : 'Ingresar'}
          </button>
        </form>

        <div className="login-pie">
          Acceso exclusivo para personal autorizado de AUTOLAND.
        </div>
      </div>
    </div>
  )
}
