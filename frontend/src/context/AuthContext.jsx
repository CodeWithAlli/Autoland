import { createContext, useContext, useEffect, useState } from 'react'
import { supabase, usuarioAEmail } from '../lib/supabase'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [sesion, setSesion] = useState(null)
  const [perfil, setPerfil] = useState(null)
  const [cargando, setCargando] = useState(true)

  async function cargarPerfil(userId) {
    const { data, error } = await supabase
      .from('perfiles')
      .select('*')
      .eq('id', userId)
      .single()
    if (error) {
      console.error('No se pudo cargar el perfil:', error.message)
      setPerfil(null)
      return
    }
    setPerfil(data)
  }

  useEffect(() => {
    supabase.auth.getSession().then(({ data }) => {
      setSesion(data.session)
      if (data.session?.user) cargarPerfil(data.session.user.id)
      setCargando(false)
    })

    const { data: listener } = supabase.auth.onAuthStateChange((_event, nuevaSesion) => {
      setSesion(nuevaSesion)
      if (nuevaSesion?.user) {
        cargarPerfil(nuevaSesion.user.id)
      } else {
        setPerfil(null)
      }
    })

    return () => listener.subscription.unsubscribe()
  }, [])

  async function iniciarSesion(usuario, password) {
    const email = usuarioAEmail(usuario)
    const { data, error } = await supabase.auth.signInWithPassword({ email, password })
    if (error) {
      return { ok: false, error: 'Usuario o contraseña incorrectos.' }
    }

    // Verificar que el perfil exista y esté activo
    const { data: perfilData, error: perfilError } = await supabase
      .from('perfiles')
      .select('*')
      .eq('id', data.user.id)
      .single()

    if (perfilError || !perfilData) {
      await supabase.auth.signOut()
      return { ok: false, error: 'No se encontró un perfil asociado a esta cuenta.' }
    }

    if (!perfilData.activo) {
      await supabase.auth.signOut()
      return { ok: false, error: 'Esta cuenta está desactivada. Contacta al administrador.' }
    }

    setPerfil(perfilData)
    return { ok: true }
  }

  async function cerrarSesion() {
    await supabase.auth.signOut()
    setPerfil(null)
  }

  const value = {
    sesion,
    perfil,
    cargando,
    esAdmin: perfil?.rol === 'admin',
    iniciarSesion,
    cerrarSesion,
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth debe usarse dentro de <AuthProvider>')
  return ctx
}
