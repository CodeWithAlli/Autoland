-- ============================================================
--  AUTOLAND — Agregar estado manual al inventario de autos
--  Ejecutar en el SQL Editor de Supabase (botón "Run query")
-- ============================================================

alter table autos
  add column if not exists estado varchar(20) not null default 'Disponible'
  check (estado in ('Disponible', 'Reservado', 'Vendido'));

create index if not exists idx_autos_estado on autos(estado);
