# AUTOLAND — Backend (Supabase)

El "backend" utiliza [Supabase](https://supabase.com): una base de datos PostgreSQL con API REST
automática, autenticación y seguridad por fila (RLS) incluidas. No hay
servidor que tú tengas que programar o mantener: el frontend en React habla
directo con Supabase usando su SDK de JavaScript.

Esta carpeta solo contiene los scripts SQL que configuran esa base de datos.

---


## 🚀 Pasos para configurar Supabase desde cero

### 1. Crear el proyecto
1. Ve a [supabase.com](https://supabase.com) → crea una cuenta / inicia sesión.
2. Click en **"New Project"**.
3. Elige un nombre (ej. `autoland`), una contraseña de base de datos (guárdala
   en un lugar seguro, es distinta a la del login del sistema) y la región
   más cercana (ej. `South America (São Paulo)`).
4. Espera 1-2 minutos a que el proyecto se cree.

### 2. Ejecutar el esquema
Ve a **SQL Editor** (ícono de la izquierda) → **New query**, y ejecuta los
archivos **en este orden exacto**:

1. `sql/01_schema.sql` → crea las tablas `perfiles`, `individuos`, `autos`.
2. `sql/02_rls_policies.sql` → activa la seguridad: admin ve todo, vendedor
   solo ve lo suyo.

### 3. Crear el usuario administrador (admin / allison)
Supabase Auth funciona con email, así que internamente el admin tendrá un
email "técnico" (`admin@autoland.local`), pero en la pantalla de login solo
verá los campos **Usuario** y **Contraseña**, igual que antes.

1. Ve a **Authentication → Users → Add user → Create new user**.
2. Email: `admin@autoland.local`
3. Password: `allisonautoland`
4. Activa la opción **"Auto Confirm User"**.
5. Guarda y copia el **UUID** que aparece para ese usuario.
6. Abre `sql/03_crear_admin.sql`, pega ese UUID donde dice
   `PEGA_AQUI_EL_UUID`, y ejecútalo en el SQL Editor.

Con eso ya puedes iniciar sesión en el sistema con:
- **Usuario:** `admin`
- **Contraseña:** `allisonautoland`

> ⚠️ Cambia esta contraseña desde el panel de "Mi cuenta" del sistema en
> cuanto lo tengas funcionando en producción.

### 4. Cargar datos de ejemplo
`sql/04_datos_ejemplo.sql` trae registros de ejemplo comentados, útiles
para probar el sistema antes de cargar datos reales. Reemplaza el UUID del
admin y descomenta las líneas si quieres usarlos.
`sql/05_agregar_estado_autos.sql` → agrega el campo `estado` (Disponible /
   Reservado / Vendido) a la tabla `autos` que ya tienes.
`sql/06_negociaciones.sql` → crea la tabla nueva `negociaciones` con su
   seguridad (RLS). Te va a salir el aviso de "operación destructiva" por
   los `drop policy if exists` — es normal, dale a **"Run query"**.
`sql/07_vendidos_por_auto.sql` Usado en Ventas para ocultar autos sin stock disponible.

Verifica en **Table Editor** que ahora `autos` tenga la columna `estado`,
y que exista la tabla nueva `negociaciones`.

---

### 5. Conectar el frontend
Ve a **Project Settings → API**. Necesitas dos valores para el frontend:
- **Project URL**
- **anon public key**

Estos van en el archivo `frontend/.env` (ver README del frontend).

---

## 👥 Crear vendedores

Los vendedores (usuarios normales del día a día) ya **no se crean por SQL**.
Una vez que el admin inicia sesión en el sistema, tiene una sección
**"Usuarios"** desde donde puede crear, activar/desactivar y eliminar
vendedores directamente desde la interfaz. Esa pantalla usa la API de
administración de Supabase para crear la cuenta de Auth y su fila en
`perfiles` automáticamente.

---

## 🗂️ Estructura de esta carpeta

```
backend/
└── sql/
    ├── 01_schema.sql          ← tablas: perfiles, individuos, autos
    ├── 02_rls_policies.sql    ← seguridad por fila (admin vs vendedor)
    ├── 03_crear_admin.sql     ← vincula el primer admin (usuario: admin)
    └── 04_datos_ejemplo.sql   ← datos de prueba opcionales
```



