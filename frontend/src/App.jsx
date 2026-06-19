import { Routes, Route } from 'react-router-dom'
import { AuthProvider } from './context/AuthContext'
import RutaProtegida from './components/RutaProtegida'
import LayoutPrincipal from './components/LayoutPrincipal'
import Login from './pages/Login'
import Dashboard from './pages/Dashboard'
import Clientes from './pages/Clientes'
import Autos from './pages/Autos'
import Usuarios from './pages/Usuarios'
import Ventas from './pages/Ventas'


export default function App() {
  return (
    <AuthProvider>
      <Routes>
        <Route path="/login" element={<Login />} />

        <Route
          path="/"
          element={
            <RutaProtegida>
              <LayoutPrincipal />
            </RutaProtegida>
          }
        >
          <Route index element={<Dashboard />} />
          <Route path="clientes" element={<Clientes />} />
          <Route path="autos" element={<Autos />} />
          <Route path="ventas" element={<Ventas />} />

          <Route
            path="usuarios"
            element={
              <RutaProtegida soloAdmin>
                <Usuarios />
              </RutaProtegida>
            }
          />
        </Route>
      </Routes>
    </AuthProvider>
  )
}
