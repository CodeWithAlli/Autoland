import { useEffect, useState } from 'react'
import { supabase } from '../lib/supabase'
import { useAuth } from '../context/AuthContext'
import Modal from '../components/Modal'
import Mensaje from '../components/Mensaje'
import { IconoBuscar, IconoMas, IconoLapiz, IconoBasura } from '../components/Iconos'

const FORM_VACIO = {
  nombre: '', apellido_paterno: '', apellido_materno: '',
  dni: '', telefono: '', direccion: '', edad: '', sexo: 'M',
}

export default function Clientes() {
  const { perfil, esAdmin } = useAuth()
  const [clientes, setClientes] = useState([])
  const [busqueda, setBusqueda] = useState('')
  const [cargando, setCargando] = useState(true)
  const [mensaje, setMensaje] = useState(null)

  const [modalAbierto, setModalAbierto] = useState(false)
  const [editandoId, setEditandoId] = useState(null)
  const [form, setForm] = useState(FORM_VACIO)
  const [guardando, setGuardando] = useState(false)

  const [confirmarEliminar, setConfirmarEliminar] = useState(null)

  useEffect(() => { cargarClientes() }, [perfil])

  async function cargarClientes() {
    if (!perfil) return
    setCargando(true)
    let query = supabase
      .from('individuos')
      .select('*, perfiles(nombre_real)')
      .order('apellido_paterno', { ascending: true })

    if (!esAdmin) query = query.eq('creado_por', perfil.id)

    const { data, error } = await query
    if (error) {
      setMensaje({ tipo: 'err', texto: 'No se pudieron cargar los clientes: ' + error.message })
    } else {
      setClientes(data || [])
    }
    setCargando(false)
  }

  const filtrados = clientes.filter((c) => {
    if (!busqueda.trim()) return true
    const t = busqueda.toLowerCase()
    return (
      c.nombre?.toLowerCase().includes(t) ||
      c.apellido_paterno?.toLowerCase().includes(t) ||
      c.apellido_materno?.toLowerCase().includes(t) ||
      c.dni?.toLowerCase().includes(t)
    )
  })

  function abrirNuevo() {
    setEditandoId(null)
    setForm(FORM_VACIO)
    setModalAbierto(true)
  }

  function abrirEditar(c) {
    setEditandoId(c.id)
    setForm({
      nombre: c.nombre || '',
      apellido_paterno: c.apellido_paterno || '',
      apellido_materno: c.apellido_materno || '',
      dni: c.dni || '',
      telefono: c.telefono || '',
      direccion: c.direccion || '',
      edad: c.edad || '',
      sexo: c.sexo || 'M',
    })
    setModalAbierto(true)
  }

  async function guardar(e) {
    e.preventDefault()
    setGuardando(true)
    setMensaje(null)

    const payload = {
      ...form,
      edad: form.edad ? parseInt(form.edad, 10) : null,
    }

    let error
    if (editandoId) {
      const res = await supabase.from('individuos').update(payload).eq('id', editandoId)
      error = res.error
    } else {
      const res = await supabase.from('individuos').insert({ ...payload, creado_por: perfil.id })
      error = res.error
    }

    setGuardando(false)

    if (error) {
      setMensaje({ tipo: 'err', texto: error.code === '23505' ? 'Ese DNI ya está registrado.' : error.message })
      return
    }

    setMensaje({ tipo: 'ok', texto: editandoId ? 'Cliente actualizado correctamente.' : 'Cliente registrado correctamente.' })
    setModalAbierto(false)
    cargarClientes()
  }

  async function eliminar(id) {
    const { error } = await supabase.from('individuos').delete().eq('id', id)
    setConfirmarEliminar(null)
    if (error) {
      setMensaje({ tipo: 'err', texto: 'No se pudo eliminar: ' + error.message })
    } else {
      setMensaje({ tipo: 'warn', texto: 'Cliente eliminado.' })
      cargarClientes()
    }
  }

  return (
    <div>
      <Mensaje tipo={mensaje?.tipo}>{mensaje?.texto}</Mensaje>

      <div className="card">
        <div className="card-header">
          <div className="barra-busqueda" style={{ width: 320 }}>
            <IconoBuscar />
            <input
              type="text"
              placeholder="Buscar por nombre, apellido o DNI…"
              value={busqueda}
              onChange={(e) => setBusqueda(e.target.value)}
            />
          </div>
          <button className="btn btn-acento" onClick={abrirNuevo}>
            <IconoMas /> Nuevo cliente
          </button>
        </div>

        <div className="tabla-wrap">
          <table className="tabla">
            <thead>
              <tr>
                <th>Nombre completo</th>
                <th>DNI</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Edad</th>
                {esAdmin && <th>Registrado por</th>}
                <th></th>
              </tr>
            </thead>
            <tbody>
              {cargando ? (
                <tr><td colSpan={7} className="tabla-vacia">Cargando…</td></tr>
              ) : filtrados.length === 0 ? (
                <tr><td colSpan={7} className="tabla-vacia">
                  {busqueda ? 'No se encontraron clientes con esa búsqueda.' : 'Aún no hay clientes registrados.'}
                </td></tr>
              ) : (
                filtrados.map((c) => (
                  <tr key={c.id}>
                    <td>{c.nombre} {c.apellido_paterno} {c.apellido_materno}</td>
                    <td className="celda-num">{c.dni || '—'}</td>
                    <td>{c.telefono || '—'}</td>
                    <td className="celda-suave">{c.direccion || '—'}</td>
                    <td className="celda-num">{c.edad || '—'}</td>
                    {esAdmin && <td className="celda-suave">{c.perfiles?.nombre_real || '—'}</td>}
                    <td>
                      <div className="celda-acciones">
                        <button className="btn btn-secundario btn-sm btn-icono" onClick={() => abrirEditar(c)} title="Editar">
                          <IconoLapiz />
                        </button>
                        <button className="btn btn-peligro btn-sm btn-icono" onClick={() => setConfirmarEliminar(c)} title="Eliminar">
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

      {modalAbierto && (
        <Modal
          titulo={editandoId ? 'Editar cliente' : 'Nuevo cliente'}
          onCerrar={() => setModalAbierto(false)}
          ancho={560}
          footer={
            <>
              <button className="btn btn-secundario" onClick={() => setModalAbierto(false)}>Cancelar</button>
              <button className="btn btn-acento" onClick={guardar} disabled={guardando}>
                {guardando ? 'Guardando…' : 'Guardar cliente'}
              </button>
            </>
          }
        >
          <form onSubmit={guardar} id="form-cliente">
            <div className="campo">
              <label>Nombres</label>
              <input value={form.nombre} onChange={(e) => setForm({ ...form, nombre: e.target.value })} required />
            </div>
            <div className="campo-fila">
              <div className="campo">
                <label>Apellido paterno</label>
                <input value={form.apellido_paterno} onChange={(e) => setForm({ ...form, apellido_paterno: e.target.value })} />
              </div>
              <div className="campo">
                <label>Apellido materno</label>
                <input value={form.apellido_materno} onChange={(e) => setForm({ ...form, apellido_materno: e.target.value })} />
              </div>
            </div>
            <div className="campo-fila">
              <div className="campo">
                <label>DNI</label>
                <input value={form.dni} onChange={(e) => setForm({ ...form, dni: e.target.value })} />
              </div>
              <div className="campo">
                <label>Teléfono</label>
                <input value={form.telefono} onChange={(e) => setForm({ ...form, telefono: e.target.value })} />
              </div>
            </div>
            <div className="campo">
              <label>Dirección</label>
              <input value={form.direccion} onChange={(e) => setForm({ ...form, direccion: e.target.value })} />
            </div>
            <div className="campo-fila">
              <div className="campo">
                <label>Edad</label>
                <input type="number" min="0" value={form.edad} onChange={(e) => setForm({ ...form, edad: e.target.value })} />
              </div>
              <div className="campo">
                <label>Sexo</label>
                <select value={form.sexo} onChange={(e) => setForm({ ...form, sexo: e.target.value })}>
                  <option value="M">Masculino</option>
                  <option value="F">Femenino</option>
                </select>
              </div>
            </div>
          </form>
        </Modal>
      )}

      {confirmarEliminar && (
        <Modal
          titulo="Eliminar cliente"
          onCerrar={() => setConfirmarEliminar(null)}
          footer={
            <>
              <button className="btn btn-secundario" onClick={() => setConfirmarEliminar(null)}>Cancelar</button>
              <button className="btn btn-peligro" onClick={() => eliminar(confirmarEliminar.id)}>Sí, eliminar</button>
            </>
          }
        >
          ¿Confirmas que quieres eliminar a <strong>{confirmarEliminar.nombre} {confirmarEliminar.apellido_paterno}</strong>?
          Esta acción no se puede deshacer.
        </Modal>
      )}
    </div>
  )
}
