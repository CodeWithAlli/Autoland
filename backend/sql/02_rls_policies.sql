-- ============================================================
--  AUTOLAND — Row Level Security (RLS)
--  Ejecutar DESPUÉS de 01_schema.sql
-- ============================================================

-- ============================================================
-- FUNCIÓN HELPER: ¿el usuario autenticado es admin?
--  security definer para poder leer "perfiles" sin recursión
--  de políticas al evaluarse a sí misma.
-- ============================================================
create or replace function es_admin()
returns boolean
language sql
security definer
set search_path = public
as $$
  select coalesce(
    (select rol = 'admin' from perfiles where id = auth.uid()),
    false
  );
$$;

-- ============================================================
-- ACTIVAR RLS
-- ============================================================
alter table perfiles   enable row level security;
alter table individuos enable row level security;
alter table autos      enable row level security;

-- ============================================================
-- POLÍTICAS: perfiles
--  - Cualquier usuario autenticado puede leer su propio perfil
--  - El admin puede leer y administrar todos los perfiles
-- ============================================================
drop policy if exists perfiles_select_propio on perfiles;
create policy perfiles_select_propio on perfiles
  for select using ( id = auth.uid() or es_admin() );

drop policy if exists perfiles_admin_insert on perfiles;
create policy perfiles_admin_insert on perfiles
  for insert with check ( es_admin() );

drop policy if exists perfiles_admin_update on perfiles;
create policy perfiles_admin_update on perfiles
  for update using ( es_admin() or id = auth.uid() );

drop policy if exists perfiles_admin_delete on perfiles;
create policy perfiles_admin_delete on perfiles
  for delete using ( es_admin() );

-- ============================================================
-- POLÍTICAS: individuos
--  - admin: ve y gestiona todo
--  - vendedor: solo lo que él creó (creado_por = auth.uid())
-- ============================================================
drop policy if exists individuos_select on individuos;
create policy individuos_select on individuos
  for select using ( es_admin() or creado_por = auth.uid() );

drop policy if exists individuos_insert on individuos;
create policy individuos_insert on individuos
  for insert with check ( creado_por = auth.uid() );

drop policy if exists individuos_update on individuos;
create policy individuos_update on individuos
  for update using ( es_admin() or creado_por = auth.uid() );

drop policy if exists individuos_delete on individuos;
create policy individuos_delete on individuos
  for delete using ( es_admin() or creado_por = auth.uid() );

-- ============================================================
-- POLÍTICAS: autos (mismo criterio que individuos)
-- ============================================================
drop policy if exists autos_select on autos;
create policy autos_select on autos
  for select using ( es_admin() or creado_por = auth.uid() );

drop policy if exists autos_insert on autos;
create policy autos_insert on autos
  for insert with check ( creado_por = auth.uid() );

drop policy if exists autos_update on autos;
create policy autos_update on autos
  for update using ( es_admin() or creado_por = auth.uid() );

drop policy if exists autos_delete on autos;
create policy autos_delete on autos
  for delete using ( es_admin() or creado_por = auth.uid() );
