# AUTOLAND — Sistema de gestión (v2: React + Supabase)

Sistema interno de gestión para AUTOLAND, una concesionaria de autos.
Construido con **React** en el frontend y **Supabase** como backend
(Postgres + Auth + Edge Functions + seguridad por fila).

## 🗂️ Estructura

```
autoland/
├── backend/       ← scripts SQL y Edge Functions de Supabase
│   ├── README.md  ← cómo crear el proyecto de Supabase paso a paso
│   ├── sql/        ← tablas, RLS, función de stock, datos de ejemplo
│   └── supabase/functions/  ← Edge Functions (crear-vendedor, eliminar-vendedor)
└── frontend/       ← aplicación React (Vite)
    └── README.md    ← cómo correrlo y conectarlo a Supabase
```

## 🚦 Orden recomendado para poner esto en marcha

1. Lee `backend/README.md` y sigue los pasos para crear tu proyecto de
   Supabase, las tablas, la seguridad (RLS) y el usuario `admin`.
2. Lee `frontend/README.md`, instala dependencias, configura `.env` con tus
   credenciales de Supabase, y corre `npm run dev`.
3. Inicia sesión con usuario `admin` y contraseña `allisonautoland`.
4. Desde **"Usuarios"** (solo visible para el admin), crea las cuentas de
   los vendedores que necesites.

> ⚠️ **Importante sobre el campo "Usuario" al crear vendedores:** ese campo
> debe ser un nombre corto sin `@` (ej. `jperez`), nunca un correo
> completo (ej. `jperez@gmail.com`). El sistema internamente lo convierte
> a `jperez@autoland.local` para Supabase Auth — si le pones un email real
> ahí, la conversión queda mal formada y la creación del vendedor falla.

## 🧩 Qué hace el sistema

### Clientes y Autos
- Cada vendedor solo ve los clientes y autos que él mismo registró. El
  admin ve todo, de todos los vendedores.
- Cada auto es una unidad individual (su propia fila), aunque haya varias
  unidades idénticas en marca/modelo/color — así cada una tiene su propio
  historial de negociaciones y estado.

### Ventas (tablero de negociaciones)
- Apartado dedicado en el sidebar, con un tablero de 4 columnas:
  **Consulta → En curso → Vendido → Cancelado**.
- **"Nueva consulta / venta"** registra un cliente + un auto + el estado
  inicial (normalmente "Consulta").
- El estado se puede mover desde el select pequeño de cada tarjeta, o
  abriendo la tarjeta (clic) para editar el registro completo.
- **Nada se borra al cancelar**: "Cancelado" es un estado más, queda en el
  historial completo. Solo el admin puede eliminar un registro por
  completo (por si se creó por error); los vendedores no pueden eliminar.
- Buscador y filtro por estado para revisar el historial completo de
  consultas, negociaciones en curso, ventas y cancelaciones.

### Control de stock (autos ↔ ventas)
- Cada auto tiene un campo `stock` (unidades disponibles de ese registro)
  y un `estado` (Disponible / Reservado / Vendido).
- Cuando una negociación pasa a **"Vendido"**, el sistema cuenta
  automáticamente cuántas unidades de ese auto ya están vendidas y
  sincroniza su `estado` en el inventario — sin que tengas que entrar a
  Autos a cambiarlo a mano.
- **Bloqueo de sobreventa:** si un auto ya no tiene stock disponible, el
  sistema no permite marcar otra negociación de ese mismo auto como
  "Vendido" — ni desde el formulario de nueva/edición de negociación, ni
  desde el cambio rápido de estado en la tarjeta del tablero. Mientras una
  negociación está en "Consulta" o "En curso" sí puede seguir existiendo
  aunque el stock se agote (para no perder el registro de quién preguntó),
  pero no puede "completarse" como venta si ya no hay unidades.
- Si una venta se cancela o un auto se reactiva, el cupo de stock se libera
  automáticamente para que otra negociación sí pueda marcarse como
  "Vendido".

### Usuarios (solo admin)
- El admin puede crear, activar/desactivar y eliminar cuentas de
  vendedores desde la sección "Usuarios", sin necesidad de tocar Supabase
  directamente.
- Cada usuario inicia sesión solo con **Usuario + Contraseña** (el sistema
  traduce eso a un email técnico de Supabase Auth por dentro, de forma
  transparente para quien lo usa).

## 🔐 Seguridad

- Todo el control de acceso (admin ve todo / vendedor ve lo suyo) lo aplica
  la base de datos mediante Row Level Security (RLS), no el frontend — así
  que aunque alguien manipule el código del navegador, no puede ver datos
  que no le correspondan.
- Crear y eliminar vendedores requiere privilegios elevados de Supabase
  (`service_role`), por eso esas dos acciones pasan por Edge Functions en
  el backend en vez de hacerse directo desde el navegador.