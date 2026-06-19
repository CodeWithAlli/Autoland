export function calcularEstadoAuto(stock, vendidos) {
  const unidades = stock ?? 1
  const vendidas = vendidos ?? 0
  return vendidas >= unidades ? 'Vendido' : 'Disponible'
}

export async function obtenerVendidosPorAuto(supabase, negociacionesFallback = []) {
  const conteo = {}
  const { data, error } = await supabase.rpc('vendidos_por_auto')

  if (!error && data) {
    data.forEach(({ auto_id, cantidad }) => { conteo[auto_id] = cantidad })
    return conteo
  }

  negociacionesFallback.forEach((n) => {
    if (n.estado === 'Vendido') {
      conteo[n.auto_id] = (conteo[n.auto_id] || 0) + 1
    }
  })
  return conteo
}

export async function sincronizarEstadoAuto(supabase, autoId, vendidosPorAuto, stockOverride) {
  let stock = stockOverride
  if (stock === undefined) {
    const { data } = await supabase.from('autos').select('stock').eq('id', autoId).single()
    stock = data?.stock ?? 1
  }

  const estado = calcularEstadoAuto(stock, vendidosPorAuto[autoId] || 0)
  await supabase.from('autos').update({ estado }).eq('id', autoId)
  return estado
}

export async function sincronizarEstadosAutos(supabase, autos, vendidosPorAuto) {
  const pendientes = autos
    .map((a) => {
      const estado = calcularEstadoAuto(a.stock, vendidosPorAuto[a.id] || 0)
      if (a.estado === estado) return null
      return supabase.from('autos').update({ estado }).eq('id', a.id)
    })
    .filter(Boolean)

  if (pendientes.length > 0) await Promise.all(pendientes)
}
