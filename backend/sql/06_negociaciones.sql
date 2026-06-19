-- ============================================================
--  AUTOLAND — Módulo de Ventas / Negociaciones
--  Ejecutar DESPUÉS de 05_agregar_estado_autos.sql
--  Te va a salir el aviso de "operación destructiva" por los
--  "drop policy if exists" — es normal, dale a "Run query".
-- ============================================================

-- ============================================================
-- TABLA: negociaciones
--  Registro de cada cliente que se interesa por un auto.
--  Nunca se borra: solo cambia de estado. Eso te da el
--  historial completo (quién consultó, quién compró, quién
--  se echó para atrás).
-- ============================================================
create table if not exists negociaciones (
  id            bigint generated always as identity primary key,
  individuo_id  bigint not null references individuos(id) on delete cascade,
  auto_id       bigint not null references autos(id) on delete cascade,
  estado        varchar(20) not null default 'Consulta'
                check (estado in ('Consulta', 'En curso', 'Vendido', 'Cancelado')),
  notas         text,
  creado_por    uuid references perfiles(id) on delete set null,
  creado_en     timestamptz not null default now(),
  actualizado_en timestamptz not null default now()
);

comment on table negociaciones is 'Historial de interés/negociación de un cliente por un auto específico. No se elimina, solo cambia de estado.';

-- Índices para que el tablero y los filtros sean rápidos
create index if not exists idx_negociaciones_estado     on negociaciones(estado);
create index if not exists idx_negociaciones_individuo  on negociaciones(individuo_id);
create index if not exists idx_negociaciones_auto       on negociaciones(auto_id);
create index if not exists idx_negociaciones_creado_por on negociaciones(creado_por);

-- ============================================================
-- FUNCIÓN: actualiza updated_at automáticamente
-- ============================================================
create or replace function actualizar_fecha_negociacion()
returns trigger
language plpgsql
as $$
begin
  new.actualizado_en = now();
  return new;
end;
$$;

-- ============================================================
-- FUNCIÓN: controla stock al cambiar a "Vendido"
-- ============================================================
create or replace function validar_stock_antes_vender()
returns trigger
language plpgsql
as $$
begin

  -- PASAR A VENDIDO
  if new.estado = 'Vendido' and old.estado is distinct from 'Vendido' then

    update autos
    set stock = stock - 1
    where id = new.auto_id
      and stock > 0;

    -- si no se actualizó ninguna fila → no hay stock
    if not found then
      raise exception 'No hay stock disponible para este auto';
    end if;

  end if;

  return new;
end;
$$;

-- ============================================================
-- TRIGGER: updated_at automático
-- ============================================================
drop trigger if exists trg_actualizar_fecha_negociacion on negociaciones;

create trigger trg_actualizar_fecha_negociacion
before update on negociaciones
for each row
execute function actualizar_fecha_negociacion();

-- ============================================================
-- TRIGGER: control de stock al vender
-- ============================================================
drop trigger if exists trg_validar_stock_vendido on negociaciones;

create trigger trg_validar_stock_vendido
before update on negociaciones
for each row
execute function validar_stock_antes_vender();

-- ============================================================
-- RLS — mismo criterio que individuos/autos:
--  admin ve y gestiona todo, vendedor solo lo que él creó.
-- ============================================================
alter table negociaciones enable row level security;

drop policy if exists negociaciones_select on negociaciones;
create policy negociaciones_select on negociaciones
  for select using ( es_admin() or creado_por = auth.uid() );

drop policy if exists negociaciones_insert on negociaciones;
create policy negociaciones_insert on negociaciones
  for insert with check ( creado_por = auth.uid() );

drop policy if exists negociaciones_update on negociaciones;
create policy negociaciones_update on negociaciones
  for update using ( es_admin() or creado_por = auth.uid() );

drop policy if exists negociaciones_delete on negociaciones;
create policy negociaciones_delete on negociaciones
  for delete using ( es_admin() );
-- Nota: a propósito NO dejamos que el vendedor elimine
-- negociaciones (ni las propias), porque pediste mantener
-- todo el historial. Solo el admin puede borrar en caso de
-- un registro hecho por error. Cancelar es distinto a eliminar:
-- "Cancelado" es un estado, sigue existiendo el registro.


