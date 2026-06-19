import { useEffect, useState } from 'react'
import { supabase } from '../lib/supabase'
import {
  calcularEstadoAuto,
  obtenerVendidosPorAuto,
  sincronizarEstadosAutos,
} from '../lib/estadoAuto'
import { useAuth } from '../context/AuthContext'
import Modal from '../components/Modal'
import Mensaje from '../components/Mensaje'
import { IconoBuscar, IconoMas, IconoLapiz, IconoBasura } from '../components/Iconos'

const COLOR_ESTADO_AUTO = {
  Disponible: 'badge-activo',
  Vendido: 'badge-inactivo',
}

const FORM_VACIO = {
  marca: '', modelo: '', anio: '', color: '', precio: '',
  kilometraje: '', combustible: 'Gasolina', stock: '1',
}

function formatoMoneda(valor) {
  return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN', maximumFractionDigits: 0 }).format(valor || 0)
}

export default function Autos() {
  const { perfil, esAdmin } = useAuth()
  const [autos, setAutos] = useState([])
  const [clientes, setClientes] = useState([])
  const [vendidosPorAuto, setVendidosPorAuto] = useState({})
  const [busqueda, setBusqueda] = useState('')
  const [cargando, setCargando] = useState(true)
  const [mensaje, setMensaje] = useState(null)

  const [modalAbierto, setModalAbierto] = useState(false)
  const [editandoId, setEditandoId] = useState(null)
  const [form, setForm] = useState(FORM_VACIO)
  const [guardando, setGuardando] = useState(false)
  const [confirmarEliminar, setConfirmarEliminar] = useState(null)

  useEffect(() => { cargarTodo() }, [perfil])

  async function cargarTodo() {
    if (!perfil) return
    setCargando(true)

    let queryAutos = supabase
      .from('autos')
      .select('*,perfiles(nombre_real)')
      .order('marca', { ascending: true })
    if (!esAdmin) queryAutos = queryAutos.eq('creado_por', perfil.id)

    let queryClientes = supabase.from('individuos').select('id, nombre, apellido_paterno')
    if (!esAdmin) queryClientes = queryClientes.eq('creado_por', perfil.id)

    const [{ data: autosData, error: errA }, { data: clientesData }] = await Promise.all([queryAutos, queryClientes])

    if (errA) setMensaje({ tipo: 'err', texto: 'No se pudieron cargar los autos: ' + errA.message })

    const vendidos = await obtenerVendidosPorAuto(supabase)
    const autosConEstado = (autosData || []).map((a) => ({
      ...a,
      estado: calcularEstadoAuto(a.stock, vendidos[a.id] || 0),
    }))

    await sincronizarEstadosAutos(supabase, autosData || [], vendidos)

    setAutos(autosConEstado)
    setVendidosPorAuto(vendidos)
    setClientes(clientesData || [])
    setCargando(false)
  }

  const filtrados = autos.filter((a) => {
    if (!busqueda.trim()) return true
    const t = busqueda.toLowerCase()
    const estado = calcularEstadoAuto(a.stock, vendidosPorAuto[a.id] || 0)
    return a.marca?.toLowerCase().includes(t) || a.modelo?.toLowerCase().includes(t)
      || a.color?.toLowerCase().includes(t) || estado.toLowerCase().includes(t)
      || String(a.stock ?? '').includes(t)
  })

  function abrirNuevo() {
    setEditandoId(null)
    setForm(FORM_VACIO)
    setModalAbierto(true)
  }

  function abrirEditar(a) {
    setEditandoId(a.id)
    setForm({
      marca: a.marca || '', modelo: a.modelo || '', anio: a.anio || '',
      color: a.color || '', precio: a.precio || '', kilometraje: a.kilometraje || 0,
      combustible: a.combustible || 'Gasolina',
      stock: a.stock ?? 1,
      individuo_id: a.individuo_id || '',
    })
    setModalAbierto(true)
  }

  async function guardar(e) {
    e.preventDefault()
    setGuardando(true)
    setMensaje(null)

    const stock = form.stock !== '' ? parseInt(form.stock, 10) : 1
    const vendidos = editandoId ? (vendidosPorAuto[editandoId] || 0) : 0

    const payload = {
      marca: form.marca,
      modelo: form.modelo,
      anio: form.anio ? parseInt(form.anio, 10) : null,
      color: form.color,
      precio: form.precio ? parseFloat(form.precio) : null,
      kilometraje: form.kilometraje ? parseInt(form.kilometraje, 10) : 0,
      combustible: form.combustible,
      stock,
      estado: calcularEstadoAuto(stock, vendidos),
      individuo_id: form.individuo_id ? parseInt(form.individuo_id, 10) : null,
    }

    let error
    if (editandoId) {
      const res = await supabase.from('autos').update(payload).eq('id', editandoId)
      error = res.error
    } else {
      const res = await supabase.from('autos').insert({ ...payload, creado_por: perfil.id })
      error = res.error
    }

    setGuardando(false)
    if (error) {
      setMensaje({ tipo: 'err', texto: error.message })
      return
    }

    setMensaje({ tipo: 'ok', texto: editandoId ? 'Auto actualizado correctamente.' : 'Auto registrado correctamente.' })
    setModalAbierto(false)
    cargarTodo()
  }

  async function eliminar(id) {
    const { error } = await supabase.from('autos').delete().eq('id', id)
    setConfirmarEliminar(null)
    if (error) {
      setMensaje({ tipo: 'err', texto: 'No se pudo eliminar: ' + error.message })
    } else {
      setMensaje({ tipo: 'warn', texto: 'Auto eliminado.' })
      cargarTodo()
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
              placeholder="Buscar por marca, modelo, color, estado o stock…"
              value={busqueda}
              onChange={(e) => setBusqueda(e.target.value)}
            />
          </div>
          <button className="btn btn-acento" onClick={abrirNuevo}>
            <IconoMas /> Nuevo auto
          </button>
        </div>

        <div className="tabla-wrap">
          <table className="tabla">
            <thead>
              <tr>
                <th>Marca / Modelo</th>
                <th>Año</th>
                <th>Color</th>
                <th>Precio</th>
                <th>Km</th>
                <th>Combustible</th>
                <th>Estado</th>
                <th>Stock</th>
                {esAdmin && <th>Registrado por</th>}
                <th></th>
              </tr>
            </thead>
            <tbody>
              {cargando ? (
                <tr><td colSpan={esAdmin ? 10 : 9} className="tabla-vacia">Cargando…</td></tr>
              ) : filtrados.length === 0 ? (
                <tr><td colSpan={esAdmin ? 10 : 9} className="tabla-vacia">
                  {busqueda ? 'No se encontraron autos con esa búsqueda.' : 'Aún no hay autos registrados.'}
                </td></tr>
              ) : (
                filtrados.map((a) => {
                  const vendidos = vendidosPorAuto[a.id] || 0
                  const estado = calcularEstadoAuto(a.stock, vendidos)
                  return (
                    <tr key={a.id}>
                      <td><strong>{a.marca}</strong> {a.modelo}</td>
                      <td className="celda-num">{a.anio || '—'}</td>
                      <td>{a.color || '—'}</td>
                      <td className="celda-num">{formatoMoneda(a.precio)}</td>
                      <td className="celda-num">{a.kilometraje?.toLocaleString('es-PE') || 0}</td>
                      <td>{a.combustible || '—'}</td>
                      <td>
                        <span className={`badge ${COLOR_ESTADO_AUTO[estado]}`}>{estado}</span>
                        {vendidos > 0 && (
                          <span className="celda-suave" style={{ display: 'block', fontSize: 11, marginTop: 2 }}>
                            {vendidos}/{a.stock ?? 1} vendidos
                          </span>
                        )}
                      </td>
                      <td className="celda-num">{a.stock ?? 1}</td>
                      {esAdmin && <td className="celda-suave">{a.perfiles?.nombre_real || '—'}</td>}
                      <td>
                        <div className="celda-acciones">
                          <button className="btn btn-secundario btn-sm btn-icono" onClick={() => abrirEditar(a)} title="Editar">
                            <IconoLapiz />
                          </button>
                          <button className="btn btn-peligro btn-sm btn-icono" onClick={() => setConfirmarEliminar(a)} title="Eliminar">
                            <IconoBasura />
                          </button>
                        </div>
                      </td>
                    </tr>
                  )
                })
              )}
            </tbody>
          </table>
        </div>
      </div>

      {modalAbierto && (
        <Modal
          titulo={editandoId ? 'Editar auto' : 'Nuevo auto'}
          onCerrar={() => setModalAbierto(false)}
          ancho={580}
          footer={
            <>
              <button className="btn btn-secundario" onClick={() => setModalAbierto(false)}>Cancelar</button>
              <button className="btn btn-acento" onClick={guardar} disabled={guardando}>
                {guardando ? 'Guardando…' : 'Guardar auto'}
              </button>
            </>
          }
        >
          <form onSubmit={guardar} id="form-auto">
            <div className="campo-fila">
              <div className="campo">
                <label>Marca</label>
                <input value={form.marca} onChange={(e) => setForm({ ...form, marca: e.target.value })} required />
              </div>
              <div className="campo">
                <label>Modelo</label>
                <input value={form.modelo} onChange={(e) => setForm({ ...form, modelo: e.target.value })} required />
              </div>
            </div>
            <div className="campo-fila">
              <div className="campo">
                <label>Año</label>
                <input type="number" value={form.anio} onChange={(e) => setForm({ ...form, anio: e.target.value })} />
              </div>
              <div className="campo">
                <label>Color</label>
                <input value={form.color} onChange={(e) => setForm({ ...form, color: e.target.value })} />
              </div>
            </div>
            <div className="campo-fila">
              <div className="campo">
                <label>Precio (S/)</label>
                <input type="number" step="0.01" value={form.precio} onChange={(e) => setForm({ ...form, precio: e.target.value })} required />
              </div>
              <div className="campo">
                <label>Kilometraje</label>
                <input type="number" value={form.kilometraje} onChange={(e) => setForm({ ...form, kilometraje: e.target.value })} />
              </div>
            </div>
            <div className="campo-fila">
              <div className="campo">
                <label>Combustible</label>
                <select value={form.combustible} onChange={(e) => setForm({ ...form, combustible: e.target.value })}>
                  <option>Gasolina</option>
                  <option>Diésel</option>
                  <option>Híbrido</option>
                  <option>Eléctrico</option>
                  <option>GLP/GNV</option>
                </select>
              </div>
              <div className="campo">
                <label>Stock</label>
                <input
                  type="number"
                  min="0"
                  value={form.stock}
                  onChange={(e) => setForm({ ...form, stock: e.target.value })}
                  required
                />
              </div>
            </div>
            {editandoId && (
              <p className="celda-suave" style={{ fontSize: 13, margin: 0 }}>
                Estado actual:{' '}
                <strong>
                  {calcularEstadoAuto(
                    form.stock !== '' ? parseInt(form.stock, 10) : 1,
                    vendidosPorAuto[editandoId] || 0,
                  )}
                </strong>
                {' '}({vendidosPorAuto[editandoId] || 0} vendidos — se actualiza solo según las ventas)
              </p>
            )}
          </form>
        </Modal>
      )}

      {confirmarEliminar && (
        <Modal
          titulo="Eliminar auto"
          onCerrar={() => setConfirmarEliminar(null)}
          footer={
            <>
              <button className="btn btn-secundario" onClick={() => setConfirmarEliminar(null)}>Cancelar</button>
              <button className="btn btn-peligro" onClick={() => eliminar(confirmarEliminar.id)}>Sí, eliminar</button>
            </>
          }
        >
          ¿Confirmas que quieres eliminar <strong>{confirmarEliminar.marca} {confirmarEliminar.modelo}</strong>?
          Esta acción no se puede deshacer.
        </Modal>
      )}
    </div>
  )
}
