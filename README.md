# 🚗 AUTOLAND — Sistema de Gestión de Autos

Sistema web de gestión interna para concesionaria de autos, desarrollado en PHP + MySQL con diseño oscuro y sistema de roles.

---

## 📋 Requisitos

- XAMPP (PHP 8.x + MySQL)
- Navegador moderno

---

## ⚙️ Instalación

**1. Clonar el repositorio**
```bash
git clone https://github.com/TU_USUARIO/autoland.git
```

**2. Copiar la carpeta al servidor**
```
C:\xampp\htdocs\CRUD_AUTOLAND\
```

**3. Importar la base de datos**
- Abrir phpMyAdmin → `http://localhost/phpmyadmin`
- Crear base de datos llamada `autoland_bd`
- Importar el archivo `autoland_bd_v2.sql`

**4. Configurar la conexión**
```bash
# Copiar el archivo de ejemplo
cp config/conexion.example.php config/conexion.php
```
Editar `config/conexion.php` con tus credenciales de MySQL.

**5. Crear el administrador**
- Abrir: `http://localhost/CRUD_AUTOLAND/setup_admin.php`
- Seguir las instrucciones en pantalla
- ⚠️ **Eliminar `setup_admin.php` después de usarlo**

**6. Iniciar sesión**
```
http://localhost/CRUD_AUTOLAND/login.php
```

---

## 👥 Roles del sistema

| Funcionalidad | Admin | Vendedor |
|---|:---:|:---:|
| Ver todos los registros | ✅ | ❌ |
| Editar registros propios | ✅ | ✅ |
| Editar registros ajenos | ✅ | ❌ |
| Panel de usuarios | ✅ | ❌ |
| Estadísticas globales | ✅ | ❌ |
| Estadísticas propias | ✅ | ✅ |

---

## 🗂️ Estructura del proyecto

```
CRUD_AUTOLAND/
├── config/
│   ├── conexion.php          ← (crear desde conexion.example.php)
│   ├── conexion.example.php  ← plantilla de configuración
│   └── data.php              ← lógica de base de datos
├── img/
│   └── fondo.png             ← imagen de fondo del login
├── index.php                 ← dashboard principal
├── login.php                 ← página de ingreso
├── logout.php                ← cierre de sesión
├── form_Individuo.php        ← gestión de clientes
├── form_Auto.php             ← gestión de autos
├── usuarios.php              ← panel admin de vendedores
├── grafico_top5.php          ← estadísticas top 5
├── grafico_pastel_top5.php   ← distribución de precios
├── setup_admin.php           ← crear admin inicial (eliminar tras usar)
├── Individuo.php             ← clase modelo
└── autoland_bd_v2.sql        ← script de base de datos
```

---

## 🛠️ Tecnologías

- PHP 8.x
- MySQL / PDO
- HTML5 + CSS3
- Chart.js (gráficos)
- Google Fonts (Bebas Neue + DM Sans)

---

## ⚠️ Notas importantes

- El archivo `config/conexion.php` está en `.gitignore` — no se sube al repositorio por seguridad.
- Siempre eliminar `setup_admin.php` después de crear el primer administrador.
- El sistema está diseñado para uso en red local (XAMPP).
