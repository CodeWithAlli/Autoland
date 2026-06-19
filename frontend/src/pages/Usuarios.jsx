import { useEffect, useState } from 'react'
import { supabase } from '../lib/supabase'
import Modal from '../components/Modal'
import Mensaje from '../components/Mensaje'
import { IconoMas, IconoBasura, IconoCandado } from '../components/Iconos'

export default function Usuarios() {
  const [vendedores, setVendedores] = useState([])
  const [cargando, setCargando] = useState(true)
  const [mensaje, setMensaje] = useState(null)

  const [modalNuevo, setModalNuevo] = useState(false)
  const [form, setForm] = useState({ nombreReal: '', usuario: '', password: '' })
  const [guardando, setGuardando] = useState(false)

  const [confirmarEliminar, setConfirmarEliminar] = useState(null)
  const [modalPassword, setModalPassword] = useState(false)
  const [nuevaPass, setNuevaPass] = useState('')

  useEffect(() => { cargarVendedores() }, [])

  async function cargarVendedores() {
    setCargando(true)
    const { data, error } = await supabase
      .from('perfiles')
      .select('*')
      .eq('rol', 'vendedor')
      .order('fecha_creado', { ascending: false })

    if (error) setMensaje({ tipo: 'err', texto: error.message })
    setVendedores(data || [])
    setCargando(false)
  }

  async function crearVendedor(e) {
    e.preventDefault()
    setGuardando(true)
    setMensaje(null)

    if (form.password.length < 6) {
      setMensaje({ tipo: 'err', texto: 'La contraseña debe tener al menos 6 caracteres.' })
      setGuardando(false)
      return
    }
    console.log('Form que se va a enviar:', JSON.stringify(form))
    
    const { data: sesionData } = await supabase.auth.getSession()
    const { data, error } = await supabase.functions.invoke('crear-vendedor', {
      body: form,
      headers: { Authorization: `Bearer ${sesionData.session.access_token}` },
    })

    setGuardando(false)

    if (error || data?.error) {
      setMensaje({ tipo: 'err', texto: data?.error || error.message })
      return
    }

    setMensaje({ tipo: 'ok', texto: `Vendedor "${form.usuario}" creado correctamente.` })
    setModalNuevo(false)
    setForm({ nombreReal: '', usuario: '', password: '' })
    cargarVendedores()
  }

  async function toggleActivo(v) {
    const { error } = await supabase.from('perfiles').update({ activo: !v.activo }).eq('id', v.id)
    if (error) {
      setMensaje({ tipo: 'err', texto: error.message })
    } else {
      setMensaje({ tipo: v.activo ? 'warn' : 'ok', texto: `Vendedor ${v.activo ? 'desactivado' : 'activado'}.` })
      cargarVendedores()
    }
  }

  async function eliminarVendedor(v) {
    const { data: sesionData } = await supabase.auth.getSession()
    const { data, error } = await supabase.functions.invoke('eliminar-vendedor', {
      body: { idVendedor: v.id },
      headers: { Authorization: `Bearer ${sesionData.session.access_token}` },
    })
    setConfirmarEliminar(null)

    if (error || data?.error) {
      setMensaje({ tipo: 'err', texto: data?.error || error.message })
      return
    }
    setMensaje({ tipo: 'warn', texto: 'Vendedor eliminado permanentemente.' })
    cargarVendedores()
  }

  async function cambiarPasswordPropia(e) {
    e.preventDefault()
    if (nuevaPass.length < 6) {
      setMensaje({ tipo: 'err', texto: 'La contraseña debe tener al menos 6 caracteres.' })
      return
    }
    const { error } = await supabase.auth.updateUser({ password: nuevaPass })
    if (error) {
      setMensaje({ tipo: 'err', texto: error.message })
    } else {
      setMensaje({ tipo: 'ok', texto: 'Tu contraseña fue actualizada.' })
      setModalPassword(false)
      setNuevaPass('')
    }
  }

  return (
    <div>
      <Mensaje tipo={mensaje?.tipo}>{mensaje?.texto}</Mensaje>

      <div className="pagina-header">
        <div />
        <div style={{ display: 'flex', gap: 10 }}>
          <button className="btn btn-secundario" onClick={() => setModalPassword(true)}>
            <IconoCandado size={15} /> Cambiar mi contraseña
          </button>
          <button className="btn btn-acento" onClick={() => setModalNuevo(true)}>
            <IconoMas /> Nuevo vendedor
          </button>
        </div>
      </div>

      <div className="card">
        <div className="card-header">
          <div>
            <div className="card-titulo">Vendedores</div>
            <div className="card-subtitulo">Cuentas con acceso restringido a sus propios registros</div>
          </div>
        </div>
        <div className="tabla-wrap">
          <table className="tabla">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th>Creado</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {cargando ? (
                <tr><td colSpan={5} className="tabla-vacia">Cargando…</td></tr>
              ) : vendedores.length === 0 ? (
                <tr><td colSpan={5} className="tabla-vacia">Aún no has creado ningún vendedor.</td></tr>
              ) : (
                vendedores.map((v) => (
                  <tr key={v.id}>
                    <td>{v.nombre_real}</td>
                    <td className="celda-num">{v.usuario}</td>
                    <td><span className={`badge ${v.activo ? 'badge-activo' : 'badge-inactivo'}`}>{v.activo ? 'Activo' : 'Inactivo'}</span></td>
                    <td className="celda-suave">{new Date(v.fecha_creado).toLocaleDateString('es-PE')}</td>
                    <td>
                      <div className="celda-acciones">
                        <button className="btn btn-secundario btn-sm" onClick={() => toggleActivo(v)}>
                          {v.activo ? 'Desactivar' : 'Activar'}
                        </button>
                        <button className="btn btn-peligro btn-sm btn-icono" onClick={() => setConfirmarEliminar(v)} title="Eliminar">
                          <IconoBasura />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {modalNuevo && (
        <Modal
          titulo="Nuevo vendedor"
          onCerrar={() => setModalNuevo(false)}
          footer={
            <>
              <button className="btn btn-secundario" onClick={() => setModalNuevo(false)}>Cancelar</button>
              <button className="btn btn-acento" onClick={crearVendedor} disabled={guardando}>
                {guardando ? 'Creando…' : 'Crear vendedor'}
              </button>
            </>
          }
        >
          <form onSubmit={crearVendedor}>
            <div className="campo">
              <label>Nombre completo</label>
              <input value={form.nombreReal} onChange={(e) => setForm({ ...form, nombreReal: e.target.value })} required />
            </div>
            <div className="campo">
              <label>Usuario</label>
              <input
                value={form.usuario}
                onChange={(e) => setForm({ ...form, usuario: e.target.value.toLowerCase() })}
                placeholder="ej. jperez"
                required
              />
            </div>
            <div className="campo">
              <label>Contraseña</label>
              <input
                type="password"
                value={form.password}
                onChange={(e) => setForm({ ...form, password: e.target.value })}
                placeholder="Mínimo 6 caracteres"
                required
              />
            </div>
          </form>
        </Modal>
      )}

      {confirmarEliminar && (
        <Modal
          titulo="Eliminar vendedor"
          onCerrar={() => setConfirmarEliminar(null)}
          footer={
            <>
              <button className="btn btn-secundario" onClick={() => setConfirmarEliminar(null)}>Cancelar</button>
              <button className="btn btn-peligro" onClick={() => eliminarVendedor(confirmarEliminar)}>Sí, eliminar</button>
            </>
          }
        >
          ¿Confirmas que quieres eliminar permanentemente a <strong>{confirmarEliminar.nombre_real}</strong>?
          Sus registros de clientes y autos no se borrarán, pero quedarán sin vendedor asociado.
        </Modal>
      )}

      {modalPassword && (
        <Modal
          titulo="Cambiar mi contraseña"
          onCerrar={() => setModalPassword(false)}
          footer={
            <>
              <button className="btn btn-secundario" onClick={() => setModalPassword(false)}>Cancelar</button>
              <button className="btn btn-acento" onClick={cambiarPasswordPropia}>Actualizar contraseña</button>
            </>
          }
        >
          <form onSubmit={cambiarPasswordPropia}>
            <div className="campo">
              <label>Nueva contraseña</label>
              <input
                type="password"
                value={nuevaPass}
                onChange={(e) => setNuevaPass(e.target.value)}
                placeholder="Mínimo 6 caracteres"
                required
              />
            </div>
          </form>
        </Modal>
      )}
    </div>
  )
}
