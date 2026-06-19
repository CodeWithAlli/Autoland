-- ============================================================
--  AUTOLAND — Esquema para Supabase (PostgreSQL)
--  Ejecutar en: Supabase Dashboard → SQL Editor
-- ============================================================

-- ============================================================
-- TABLA: perfiles
--  Vive 1 a 1 con auth.users. Aquí guardamos el rol y el
--  nombre real de cada persona. El login de Supabase Auth
--  usa email, pero el frontend solo le pide al usuario un
--  "usuario" + contraseña y traduce eso a un email interno
--  (ej: admin -> admin@autoland.local).
-- ============================================================
create table if not exists perfiles (
  id           uuid primary key references auth.users(id) on delete cascade,
  usuario      varchar(50) not null unique,
  nombre_real  varchar(100) not null,
  rol          varchar(20) not null default 'vendedor' check (rol in ('admin','vendedor')),
  activo       boolean not null default true,
  fecha_creado timestamptz not null default now()
);

comment on table perfiles is 'Datos de negocio de cada usuario. id = auth.users.id';

-- ============================================================
-- TABLA: individuos (antes "individuo") — clientes
-- ============================================================
create table if not exists individuos (
  id               bigint generated always as identity primary key,
  nombre           varchar(100) not null,
  apellido_paterno varchar(50),
  apellido_materno varchar(50),
  dni              varchar(20) unique,
  telefono         varchar(20),
  direccion        varchar(200),
  edad             int,
  sexo             varchar(1),
  creado_por       uuid references perfiles(id) on delete set null,
  creado_en        timestamptz not null default now()
);

-- ============================================================
-- TABLA: autos
-- ============================================================
create table if not exists autos (
  id           bigint generated always as identity primary key,
  marca        varchar(50),
  modelo       varchar(100),
  anio         int,
  color        varchar(50),
  precio       numeric(10,2),
  kilometraje  int default 0,
  combustible  varchar(20) default 'Gasolina',
  stock        int not null default 1,
  individuo_id bigint references individuos(id) on delete set null,
  creado_por   uuid references perfiles(id) on delete set null,
  creado_en    timestamptz not null default now()
);

-- ============================================================
-- ÍNDICES útiles para búsquedas y filtros por dueño
-- ============================================================
create index if not exists idx_individuos_creado_por on individuos(creado_por);
create index if not exists idx_autos_creado_por       on autos(creado_por);
create index if not exists idx_autos_individuo_id     on autos(individuo_id);
