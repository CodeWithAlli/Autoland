# AUTOLAND — Frontend (React + Supabase)

Panel de administración para AUTOLAND, una concesionaria de autos. Conectado
a Supabase (ver carpeta `../backend`). Hecho con React + Vite.

---

## 🚀 Cómo correrlo en local

```bash
npm install
cp .env.example .env
```

Edita `.env` con tu URL y `anon key` de Supabase (las encuentras en
**Project Settings → API** dentro de tu proyecto de Supabase):

```
VITE_SUPABASE_URL=https://tuproyecto.supabase.co
VITE_SUPABASE_ANON_KEY=tu_clave_anonima
```

Luego:

```bash
npm run dev
```

Abre `http://localhost:5173`.

---

## 🔑 Login del sistema

El login solo pide **Usuario** y **Contraseña** (como en la versión
anterior). Por dentro, esto se traduce a un correo técnico de Supabase
Auth (`usuario@autoland.local`), pero la persona que usa el sistema nunca
ve esa parte.

Usuario inicial (configurado en el backend):
- **Usuario:** `admin`
- **Contraseña:** `allisonautoland`

---

## 🧩 Roles

- **Admin**: ve y gestiona todos los clientes, autos y vendedores. Puede
  crear, activar/desactivar y eliminar vendedores desde "Usuarios".
- **Vendedor**: solo ve y gestiona los clientes/autos que él mismo registró.

Estas reglas las aplica directamente la base de datos (Row Level Security
en Supabase), no el frontend — así que aunque alguien manipule el código del
navegador, no puede ver datos que no le correspondan.

---

## ☁️ Funciones de servidor (Edge Functions)

Crear y eliminar vendedores requiere privilegios que **nunca** deben estar
en el navegador (la "service role key" de Supabase). Por eso esas dos
acciones llaman a funciones desplegadas en Supabase:

```bash
# Desde la carpeta backend/, con la CLI de Supabase instalada:
supabase functions deploy crear-vendedor
supabase functions deploy eliminar-vendedor
```

Si no las despliegas, todo el resto del sistema (login, clientes, autos,
dashboard) funciona igual — solo "Nuevo vendedor" y "Eliminar vendedor"
necesitan esas funciones activas.

---

## 📦 Compilar para producción

```bash
npm run build
```

Esto genera la carpeta `dist/`, lista para subir a **Vercel**, **Netlify**,
o cualquier hosting de archivos estáticos (recuerda configurar las mismas
variables de entorno `VITE_SUPABASE_URL` y `VITE_SUPABASE_ANON_KEY` en el
panel de ese hosting).

---

## 🗂️ Estructura

```
frontend/
├── src/
│   ├── components/      componentes (Modal, Mensaje, Sidebar, íconos, ruta protegida)
│   ├── context/          AuthContext (sesión y perfil del usuario)
│   ├── lib/               cliente de Supabase
│   ├── pages/             Login, Dashboard, Clientes, Autos, Usuarios
│   ├── styles/            tokens.css, componentes.css, layout.css, login.css
│   ├── App.jsx            rutas
│   └── main.jsx
├── .env.example
└── package.json
```


