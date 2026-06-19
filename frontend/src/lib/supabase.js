import { createClient } from '@supabase/supabase-js'

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL
const supabaseAnonKey = import.meta.env.VITE_SUPABASE_ANON_KEY

if (!supabaseUrl || !supabaseAnonKey) {
  console.warn(
    'Faltan las variables VITE_SUPABASE_URL / VITE_SUPABASE_ANON_KEY. ' +
    'Copia .env.example a .env y completa tus credenciales de Supabase.'
  )
}

export const supabase = createClient(supabaseUrl, supabaseAnonKey)

// El sistema le muestra al usuario un login de "usuario + contraseña",
// pero Supabase Auth funciona internamente con email. Esta función
// traduce "admin" -> "admin@autoland.local" de forma transparente.
export function usuarioAEmail(usuario) {
  return `${String(usuario).trim().toLowerCase()}@autoland.local`
}
