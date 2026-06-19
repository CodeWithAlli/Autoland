-- ============================================================
--  AUTOLAND — Crear el perfil del administrador
--  PASO PREVIO OBLIGATORIO (hacerlo en el Dashboard, no en SQL):
--
--  1. Ir a: Authentication → Users → "Add user" → "Create new user"
--  2. Email:    admin@autoland.local
--  3. Password: allisonautoland
--  4. Marcar "Auto Confirm User" (para que no pida verificar email)
--  5. Guardar y COPIAR el UUID que Supabase le asigna a ese usuario.
--
--  Luego reemplaza 'PEGA_AQUI_EL_UUID' abajo y ejecuta este script.
-- ============================================================

insert into perfiles (id, usuario, nombre_real, rol, activo)
values (
  'a5ab7262-b45c-49c1-af08-a84ee6a3b859',   -- UUID copiado de Authentication > Users
  'admin',               -- esto es lo que el admin escribirá en el campo "Usuario" del login
  'Administrador',
  'admin',
  true
)
on conflict (id) do update set
  usuario     = excluded.usuario,
  nombre_real = excluded.nombre_real,
  rol         = excluded.rol,
  activo      = excluded.activo;

-- Verificar que quedó bien:
-- select * from perfiles;
