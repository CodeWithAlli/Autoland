import { useEffect, useMemo, useState } from 'react'
import { supabase } from '../lib/supabase'
import {
  calcularEstadoAuto,
  obtenerVendidosPorAuto,
  sincronizarEstadoAuto,
} from '../lib/estadoAuto'
import { useAuth } from '../context/AuthContext'
import Modal from '../components/Modal'
import Mensaje from '../components/Mensaje'
import { IconoBuscar, IconoMas } from '../components/Iconos'

// Definición de las columnas del tablero, en orden.
// "activas" son las que se ven en el tablero principal;
// el historial completo (incluye Vendido/Cancelado) se ve
// con el buscador y el filtro de abajo.
const ESTADOS = ['Consulta', 'En curso', 'Vendido', 'Cancelado']

const COLOR_ESTADO = {
  Consulta: 'badge-vendedor',
  'En curso': 'badge-admin',
  Vendido: 'badge-activo',
  Cancelado: 'badge-inactivo',
}

const FORM_VACIO = { individuo_id: '', auto_id: '', estado: 'Consulta', notas: '' }

function formatoMoneda(valor) {
  return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN', maximumFractionDigits: 0 }).format(valor || 0)
}

export default function Ventas() {
  const { perfil, esAdmin } = useAuth()
  const [negociaciones, setNegociaciones] = useState([])
  const [clientes, setClientes] = useState([])
  const [autos, setAutos] = useState([])
  const [cargando, setCargando] = useState(true)
  const [mensaje, setMensaje] = useState(null)
  const [busqueda, setBusqueda] = useState('')
  const [filtroEstado, setFiltroEstado] = useState('TODOS')

  const [modalAbierto, setModalAbierto] = useState(false)
  const [editandoId, setEditandoId] = useState(null)
  const [form, setForm] = useState(FORM_VACIO)
  const [guardando, setGuardando] = useState(false)
  const [vendidosPorAuto, setVendidosPorAuto] = useState({})

  useEffect(() => { cargarTodo() }, [perfil])

  async function cargarTodo() {
    if (!perfil) return
    setCargando(true)

    let queryNeg = supabase
      .from('negociaciones')
      .select('*, individuos(nombre, apellido_paterno, telefono), autos(marca, modelo, anio, color, precio, estado), perfiles(nombre_real)')
      .order('actualizado_en', { ascending: false })
    if (!esAdmin) queryNeg = queryNeg.eq('creado_por', perfil.id)

    let queryClientes = supabase.from('individuos').select('id, nombre, apellido_paterno')
    let queryAutos = supabase.from('autos').select('id, marca, modelo, anio, color, precio, estado, stock')
    if (!esAdmin) {
      queryClientes = queryClientes.eq('creado_por', perfil.id)
      queryAutos = queryAutos.eq('creado_por', perfil.id)
    }

    const [{ data: negData, error: errN }, { data: clientesData }, { data: autosData }] =
      await Promise.all([
        queryNeg,
        queryClientes,
        queryAutos,
      ])

    if (errN) setMensaje({ tipo: 'err', texto: 'No se pudieron cargar las ventas: ' + errN.message })

    const conteoVendidos = await obtenerVendidosPorAuto(supabase, negData || [])

    setNegociaciones(negData || [])
    setClientes(clientesData || [])
    setAutos(autosData || [])
    setVendidosPorAuto(conteoVendidos)
    setCargando(false)
  }

  const filtradas = useMemo(() => {
    return negociaciones.filter((n) => {
      if (filtroEstado !== 'TODOS' && n.estado !== filtroEstado) return false
      if (!busqueda.trim()) return true
      const t = busqueda.toLowerCase()
      const nombreCliente = `${n.individuos?.nombre || ''} ${n.individuos?.apellido_paterno || ''}`.toLowerCase()
      const nombreAuto = `${n.autos?.marca || ''} ${n.autos?.modelo || ''}`.toLowerCase()
      return nombreCliente.includes(t) || nombreAuto.includes(t)
    })
  }, [negociaciones, busqueda, filtroEstado])

  // Para el tablero, agrupamos solo cuando no hay búsqueda activa de filtro único.
  const columnas = useMemo(() => {
    const grupos = { Consulta: [], 'En curso': [], Vendido: [], Cancelado: [] }
    filtradas.forEach((n) => grupos[n.estado]?.push(n))
    return grupos
  }, [filtradas])

  const autoIdEditando = editandoId && form.auto_id ? parseInt(form.auto_id, 10) : null

  const autosParaSeleccion = useMemo(() => {
    return autos.filter((a) => {
      const vendidos = vendidosPorAuto[a.id] || 0
      const stock = a.stock ?? 1
      if (vendidos >= stock && a.id !== autoIdEditando) return false
      return true
    })
  }, [autos, vendidosPorAuto, autoIdEditando])

  // ============================================================
  // Helper central: ¿hay stock disponible para marcar ESTE auto
  // como "Vendido" en ESTA negociación específica?
  //
  // "vendidosPorAuto" cuenta TODAS las negociaciones en estado
  // Vendido de ese auto, incluyendo la propia negociación que
  // estamos editando (si ya estaba en Vendido). Por eso, si la
  // negociación que se está moviendo YA es una de las que cuentan
  // como vendida, no se le debe sumar de nuevo — se descuenta 1
  // para no bloquearse a sí misma al simplemente mantenerla o
  // moverla entre otros estados.
  // ============================================================
  function hayStockParaVender(autoId, negociacionIdActual, estadoActualDeEsaNegociacion) {
    const auto = autos.find((a) => a.id === autoId)
    const stock = auto?.stock ?? 1
    let vendidos = vendidosPorAuto[autoId] || 0

    // Si la negociación que estamos moviendo ya estaba contabilizada
    // como "Vendido" (es decir, ya ocupaba un cupo de stock), la
    // restamos del conteo antes de evaluar, porque no es una unidad
    // nueva que se esté vendiendo, es la misma que ya tenía reservada.
    if (negociacionIdActual && estadoActualDeEsaNegociacion === 'Vendido') {
      vendidos = Math.max(vendidos - 1, 0)
    }

    return vendidos < stock
  }

  function abrirNueva() {
    setEditandoId(null)
    setForm(FORM_VACIO)
    setModalAbierto(true)
  }

  function abrirEditar(n) {
    setEditandoId(n.id)
    setForm({
      individuo_id: n.individuo_id,
      auto_id: n.auto_id,
      estado: n.estado,
      notas: n.notas || '',
    })
    setModalAbierto(true)
  }

  async function guardar(e) {
    e.preventDefault()
    setGuardando(true)
    setMensaje(null)

    const payload = {
      individuo_id: parseInt(form.individuo_id, 10),
      auto_id: parseInt(form.auto_id, 10),
      estado: form.estado,
      notas: form.notas,
    }

    const negOriginal = editandoId ? negociaciones.find((n) => n.id === editandoId) : null
    const autoIdAnterior = negOriginal?.auto_id

    // ----------------------------------------------------------
    // VALIDACIÓN DE STOCK — aplica tanto a registros nuevos
    // como a ediciones. Antes solo se validaba en el caso "nuevo";
    // ahora también se bloquea si editas una negociación existente
    // y la quieres dejar (o pasar) en estado "Vendido" sin stock.
    // ----------------------------------------------------------
    if (payload.estado === 'Vendido') {
      const estadoPrevioDeEstaNegociacion = negOriginal?.estado || null
      const sePuedeVender = hayStockParaVender(payload.auto_id, editandoId, estadoPrevioDeEstaNegociacion)

      if (!sePuedeVender) {
        setGuardando(false)
        setMensaje({ tipo: 'err', texto: 'Ese auto ya no tiene stock disponible. No se puede marcar como Vendido.' })
        return
      }
    }

    let error
    if (editandoId) {
      const res = await supabase.from('negociaciones').update(payload).eq('id', editandoId)
      error = res.error
    } else {
      const res = await supabase.from('negociaciones').insert({ ...payload, creado_por: perfil.id })
      error = res.error
    }

    setGuardando(false)
    if (error) {
      setMensaje({ tipo: 'err', texto: error.message })
      return
    }

    setMensaje({ tipo: 'ok', texto: editandoId ? 'Registro actualizado.' : 'Negociación registrada.' })
    setModalAbierto(false)

    const vendidos = await obtenerVendidosPorAuto(supabase)
    await sincronizarEstadoAuto(supabase, payload.auto_id, vendidos)
    if (autoIdAnterior && autoIdAnterior !== payload.auto_id) {
      await sincronizarEstadoAuto(supabase, autoIdAnterior, vendidos)
    }

    cargarTodo()
  }

  // Cambio rápido de estado directamente desde la tarjeta del tablero
  async function cambiarEstadoRapido(n, nuevoEstado) {
    // ----------------------------------------------------------
    // Misma validación de stock, ahora también en el select rápido
    // de cada tarjeta del tablero — antes este camino no
    // verificaba nada y dejaba "vender" autos sin stock.
    // ----------------------------------------------------------
    if (nuevoEstado === 'Vendido') {
      const sePuedeVender = hayStockParaVender(n.auto_id, n.id, n.estado)
      if (!sePuedeVender) {
        setMensaje({ tipo: 'err', texto: 'Ese auto ya no tiene stock disponible. No se puede mover a Vendido.' })
        return
      }
    }

    const { error } = await supabase.from('negociaciones').update({ estado: nuevoEstado }).eq('id', n.id)
    if (error) {
      setMensaje({ tipo: 'err', texto: error.message })
    } else {
      setMensaje({ tipo: nuevoEstado === 'Cancelado' ? 'warn' : 'ok', texto: `Movido a "${nuevoEstado}".` })
      const vendidos = await obtenerVendidosPorAuto(supabase)
      await sincronizarEstadoAuto(supabase, n.auto_id, vendidos)
      cargarTodo()
    }
  }

  return (
    <div>
      <Mensaje tipo={mensaje?.tipo}>{mensaje?.texto}</Mensaje>

      <div className="card" style={{ marginBottom: 16 }}>
        <div className="card-header" style={{ flexWrap: 'wrap', gap: 10 }}>
          <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap' }}>
            <div className="barra-busqueda" style={{ width: 280 }}>
              <IconoBuscar />
              <input
                type="text"
                placeholder="Buscar cliente o auto…"
                value={busqueda}
                onChange={(e) => setBusqueda(e.target.value)}
              />
            </div>
            <select
              value={filtroEstado}
              onChange={(e) => setFiltroEstado(e.target.value)}
              style={{ width: 170, border: '1px solid var(--border-fuerte)', borderRadius: 6, padding: '0 10px', fontSize: 13.5 }}
            >
              <option value="TODOS">Todos los estados</option>
              {ESTADOS.map((s) => <option key={s} value={s}>{s}</option>)}
            </select>
          </div>
          <button className="btn btn-acento" onClick={abrirNueva}>
            <IconoMas /> Nueva consulta / venta
          </button>
        </div>
      </div>

      {cargando ? (
        <div className="card"><div className="card-body"><div className="vacio">Cargando…</div></div></div>
      ) : (
        <div className="tablero-ventas">
          {ESTADOS.map((estado) => (
            <div className="tablero-columna" key={estado}>
              <div className="tablero-columna-header">
                <span className={`badge ${COLOR_ESTADO[estado]}`}>{estado}</span>
                <span className="tablero-columna-contador">{columnas[estado].length}</span>
              </div>

              <div className="tablero-columna-body">
                {columnas[estado].length === 0 ? (
                  <div className="tablero-vacio">Sin registros aquí.</div>
                ) : (
                  columnas[estado].map((n) => (
                    <TarjetaNegociacion
                      key={n.id}
                      n={n}
                      onEditar={() => abrirEditar(n)}
                      onCambiarEstado={(nuevo) => cambiarEstadoRapido(n, nuevo)}
                    />
                  ))
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {modalAbierto && (
        <Modal
          titulo={editandoId ? 'Editar negociación' : 'Nueva consulta / venta'}
          onCerrar={() => setModalAbierto(false)}
          ancho={560}
          footer={
            <>
              <button className="btn btn-secundario" onClick={() => setModalAbierto(false)}>Cancelar</button>
              <button className="btn btn-acento" onClick={guardar} disabled={guardando}>
                {guardando ? 'Guardando…' : 'Guardar'}
              </button>
            </>
          }
        >
          <form onSubmit={guardar}>
            <div className="campo">
              <label>Cliente</label>
              <select value={form.individuo_id} onChange={(e) => setForm({ ...form, individuo_id: e.target.value })} required>
                <option value="">Selecciona un cliente…</option>
                {clientes.map((c) => (
                  <option key={c.id} value={c.id}>{c.nombre} {c.apellido_paterno}</option>
                ))}
              </select>
            </div>
            <div className="campo">
              <label>Auto</label>
              <select value={form.auto_id} onChange={(e) => setForm({ ...form, auto_id: e.target.value })} required>
                <option value="">Selecciona un auto…</option>
                {autosParaSeleccion.map((a) => {
                  const vendidos = vendidosPorAuto[a.id] || 0
                  const stock = a.stock ?? 1
                  const disponibles = Math.max(stock - vendidos, 0)
                  const estadoAuto = calcularEstadoAuto(stock, vendidos)
                  return (
                    <option key={a.id} value={a.id}>
                      {a.marca} {a.modelo} {a.anio || ''} — {a.color} — {formatoMoneda(a.precio)} ({estadoAuto}, {disponibles}/{stock} disp.)
                    </option>
                  )
                })}
              </select>
            </div>
            <div className="campo">
              <label>Estado</label>
              <select value={form.estado} onChange={(e) => setForm({ ...form, estado: e.target.value })}>
                {ESTADOS.map((s) => <option key={s} value={s}>{s}</option>)}
              </select>
              {form.estado === 'Vendido' && form.auto_id && (
                <SugerenciaStock
                  auto={autos.find((a) => a.id === parseInt(form.auto_id, 10))}
                  vendidosPorAuto={vendidosPorAuto}
                  negociacionIdActual={editandoId}
                  estadoOriginal={editandoId ? negociaciones.find((n) => n.id === editandoId)?.estado : null}
                />
              )}
            </div>
            <div className="campo">
              <label>Notas (opcional)</label>
              <input
                value={form.notas}
                onChange={(e) => setForm({ ...form, notas: e.target.value })}
                placeholder="Ej: pidió crédito, vuelve el sábado, etc."
              />
            </div>
          </form>
        </Modal>
      )}
    </div>
  )
}

// Pequeño aviso dentro del modal: si el auto elegido ya no tiene
// stock para venderse, se lo muestra de una vez antes de que el
// usuario intente guardar y se encuentre con el error.
function SugerenciaStock({ auto, vendidosPorAuto, negociacionIdActual, estadoOriginal }) {
  if (!auto) return null
  const stock = auto.stock ?? 1
  let vendidos = vendidosPorAuto[auto.id] || 0
  if (negociacionIdActual && estadoOriginal === 'Vendido') {
    vendidos = Math.max(vendidos - 1, 0)
  }
  if (vendidos < stock) return null

  return (
    <div style={{ marginTop: 6, fontSize: 12.5, color: 'var(--peligro)' }}>
      ⚠ Este auto ya no tiene unidades disponibles para vender ({vendidos}/{stock}).
    </div>
  )
}

function TarjetaNegociacion({ n, onEditar, onCambiarEstado }) {
  const cliente = n.individuos
  const auto = n.autos
  const fecha = new Date(n.actualizado_en).toLocaleDateString('es-PE', { day: '2-digit', month: 'short' })

  return (
    <div className="tarjeta-negociacion" onClick={onEditar}>
      <div className="tarjeta-negociacion-auto">{auto?.marca} {auto?.modelo}</div>
      <div className="tarjeta-negociacion-detalle">{auto?.color} · {auto?.anio} · {formatoMoneda(auto?.precio)}</div>
      <div className="tarjeta-negociacion-cliente">
        {cliente?.nombre} {cliente?.apellido_paterno}
        {cliente?.telefono && <span className="celda-suave"> · {cliente.telefono}</span>}
      </div>
      {n.notas && <div className="tarjeta-negociacion-notas">"{n.notas}"</div>}
      <div className="tarjeta-negociacion-pie">
        <span className="celda-suave">{fecha}</span>
        <select
          value={n.estado}
          onClick={(e) => e.stopPropagation()}
          onChange={(e) => onCambiarEstado(e.target.value)}
          className="tarjeta-negociacion-select"
        >
          {ESTADOS.map((s) => <option key={s} value={s}>{s}</option>)}
        </select>
      </div>
    </div>
  )
}