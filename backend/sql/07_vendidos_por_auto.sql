-- ============================================================
--  AUTOLAND — Conteo global de vendidos por auto (inventario)
--  Ejecutar DESPUÉS de 06_negociaciones.sql
--  Usado en Ventas para ocultar autos sin stock disponible.
-- ============================================================

create or replace function public.vendidos_por_auto()
returns table (auto_id bigint, cantidad int)
language sql
security definer
stable
set search_path = public
as $$
  select auto_id, count(*)::int as cantidad
  from negociaciones
  where estado = 'Vendido'
  group by auto_id;
$$;

grant execute on function public.vendidos_por_auto() to authenticated;
