<?php
session_start();
if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php"); exit();
}
require_once 'config/data.php';
$data    = new Data();
$usuario = htmlspecialchars($_SESSION['usuario']);

$mensaje = ''; $tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $nombreReal = trim($_POST['nombreReal'] ?? '');
        $usr        = trim($_POST['usuario'] ?? '');
        $pass       = trim($_POST['password'] ?? '');

        if (!$nombreReal || !$usr || !$pass) {
            $mensaje = '✕ Completa todos los campos.'; $tipo = 'err';
        } elseif (strlen($pass) < 6) {
            $mensaje = '✕ La contraseña debe tener al menos 6 caracteres.'; $tipo = 'err';
        } elseif ($data->usuarioExiste($usr)) {
            $mensaje = "✕ El usuario \"$usr\" ya existe."; $tipo = 'err';
        } else {
            $data->crearVendedor($nombreReal, $usr, $pass);
            $mensaje = "✓ Vendedor \"$usr\" creado correctamente."; $tipo = 'ok';
        }

    } elseif ($accion === 'toggle') {
        $id     = intval($_POST['id']);
        $activo = intval($_POST['activo']);
        $data->toggleVendedor($id, $activo);
        $mensaje = $activo ? '✓ Vendedor activado.' : '⚠ Vendedor desactivado.';
        $tipo    = $activo ? 'ok' : 'warn';

    } elseif ($accion === 'eliminar') {
        $data->eliminarVendedor(intval($_POST['id']));
        $mensaje = '⚠ Vendedor eliminado permanentemente.'; $tipo = 'warn';

    } elseif ($accion === 'cambiar_pass') {
        $nuevaPass = trim($_POST['nuevaPass'] ?? '');
        if (strlen($nuevaPass) < 6) {
            $mensaje = '✕ Contraseña muy corta (mínimo 6 caracteres).'; $tipo = 'err';
        } else {
            $data->cambiarPasswordAdmin($_SESSION['idUsuario'], $nuevaPass);
            $mensaje = '✓ Contraseña de admin actualizada.'; $tipo = 'ok';
        }
    }
}

$vendedores = $data->listarVendedores()->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUTOLAND — Usuarios</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--negro:#0a0a0a;--rojo:#c0392b;--rojo2:#e74c3c;--gris:#111;--gris2:#1a1a1a;--gris3:#222;--claro:#f5f0eb;--texto:#888;--verde:#22c55e;--amarillo:#f59e0b}
        body{font-family:'DM Sans',sans-serif;background:var(--negro);color:var(--claro);min-height:100vh}

        .navbar{display:flex;align-items:center;justify-content:space-between;padding:0 48px;height:64px;background:var(--gris);border-bottom:1px solid #1f1f1f;position:sticky;top:0;z-index:100}
        .nav-logo{font-family:'Bebas Neue',sans-serif;font-size:24px;letter-spacing:3px}
        .nav-logo span{color:var(--rojo)}
        .nav-links{display:flex;gap:4px}
        .nav-links a{text-decoration:none;color:var(--texto);font-size:13px;padding:8px 16px;border-radius:6px;transition:all .2s}
        .nav-links a:hover,.nav-links a.active{color:var(--claro);background:var(--gris2)}
        .nav-links a.active{color:var(--rojo)}
        .badge-admin{background:rgba(192,57,43,.2);color:var(--rojo);padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase}
        .btn-logout{background:transparent;border:1px solid #2a2a2a;color:#555;padding:7px 14px;border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;transition:all .2s}
        .btn-logout:hover{border-color:var(--rojo);color:var(--rojo)}

        .page-header{padding:48px 48px 32px;border-bottom:1px solid #141414}
        .page-eyebrow{font-size:11px;letter-spacing:4px;text-transform:uppercase;color:var(--rojo);font-weight:600;margin-bottom:8px}
        .page-titulo{font-family:'Bebas Neue',sans-serif;font-size:56px;line-height:1}
        .page-sub{font-size:14px;color:var(--texto);margin-top:8px}

        .layout{display:grid;grid-template-columns:360px 1fr;gap:24px;padding:32px 48px;align-items:start}

        /* Columna izquierda */
        .col-izq{display:flex;flex-direction:column;gap:20px;position:sticky;top:80px}

        .card{background:var(--gris2);border:1px solid #1f1f1f;border-radius:12px;padding:24px}
        .card-titulo{font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:4px}
        .card-sub{font-size:12px;color:var(--texto);margin-bottom:20px}

        .campo{margin-bottom:14px}
        .campo label{display:block;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#555;font-weight:600;margin-bottom:6px}
        .campo input{width:100%;background:var(--gris3);border:1px solid #2a2a2a;border-radius:6px;padding:11px 14px;font-size:13px;color:var(--claro);font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s}
        .campo input:focus{border-color:var(--rojo)}
        .campo small{display:block;font-size:11px;color:#444;margin-top:4px}

        .btn-primary{width:100%;background:var(--rojo);color:#fff;border:none;border-radius:6px;padding:13px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:6px;transition:background .2s}
        .btn-primary:hover{background:var(--rojo2)}
        .btn-danger{width:100%;background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.2);border-radius:6px;padding:12px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:6px;transition:all .2s}
        .btn-danger:hover{background:rgba(239,68,68,.2)}

        /* Tabla vendedores */
        .tabla-section{min-width:0}
        .tabla-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
        .tabla-info{font-size:12px;color:var(--texto)}
        .tabla-info strong{color:var(--claro)}
        .tabla-wrap{overflow-x:auto;border-radius:10px;border:1px solid #1f1f1f}
        table{width:100%;border-collapse:collapse;font-size:13px}
        thead tr{background:var(--gris2);border-bottom:1px solid #1f1f1f}
        thead th{padding:14px 16px;text-align:left;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:#555;font-weight:600;white-space:nowrap}
        tbody tr{border-bottom:1px solid #141414;transition:background .15s}
        tbody tr:last-child{border-bottom:none}
        tbody tr:hover{background:var(--gris2)}
        td{padding:14px 16px;vertical-align:middle}
        td.muted{color:var(--texto)}

        .badge-activo{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px}
        .badge-activo.si{background:rgba(34,197,94,.12);color:#4ade80}
        .badge-activo.no{background:rgba(239,68,68,.12);color:#f87171}
        .badge-activo::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor}

        .acciones{display:flex;gap:6px;flex-wrap:wrap}
        .btn-sm{padding:5px 11px;border-radius:5px;font-size:11px;font-weight:600;cursor:pointer;border:none;font-family:'DM Sans',sans-serif;transition:all .2s}
        .btn-toggle-on {background:rgba(34,197,94,.12);color:#4ade80}
        .btn-toggle-on:hover{background:rgba(34,197,94,.25)}
        .btn-toggle-off{background:rgba(245,158,11,.12);color:#fbbf24}
        .btn-toggle-off:hover{background:rgba(245,158,11,.25)}
        .btn-del{background:rgba(255,255,255,.04);color:#555}
        .btn-del:hover{background:rgba(239,68,68,.15);color:#f87171}

        .empty-row td{text-align:center;padding:48px;color:#333}

        /* Info admin */
        .admin-info{background:rgba(192,57,43,.06);border:1px solid rgba(192,57,43,.15);border-radius:10px;padding:18px 20px;display:flex;align-items:center;gap:14px}
        .admin-avatar{width:44px;height:44px;background:var(--rojo);border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue',sans-serif;font-size:20px;color:#fff;flex-shrink:0}
        .admin-nombre{font-size:14px;font-weight:700;color:var(--claro)}
        .admin-detalle{font-size:12px;color:var(--texto)}

        /* Divider */
        .divider{border:none;border-top:1px solid #1f1f1f;margin:4px 0}

        .toast{position:fixed;bottom:24px;right:24px;z-index:999;padding:14px 20px;border-radius:8px;font-size:13px;font-weight:600;animation:toastIn .3s ease both;max-width:340px}
        .toast.ok  {background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:#4ade80}
        .toast.warn{background:rgba(234,179,8,.15);border:1px solid rgba(234,179,8,.3);color:#fbbf24}
        .toast.err {background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#f87171}
        @keyframes toastIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

        @media(max-width:900px){.layout{grid-template-columns:1fr}.col-izq{position:static}.navbar,.page-header,.layout{padding-left:20px;padding-right:20px}.nav-links{display:none}}
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">AUTO<span>LAND</span></div>
    <div class="nav-links">
        <a href="index.php">Inicio</a>
        <a href="form_Individuo.php">Clientes</a>
        <a href="form_Auto.php">Autos</a>
        <a href="usuarios.php" class="active">Usuarios</a>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
        <span class="badge-admin">Admin</span>
        <a href="logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</nav>

<div class="page-header">
    <div class="page-eyebrow">Solo administrador</div>
    <h1 class="page-titulo">Usuarios</h1>
    <p class="page-sub">Crea y gestiona las cuentas de vendedores. Solo el admin puede acceder aquí.</p>
</div>

<div class="layout">

    <!-- COLUMNA IZQUIERDA -->
    <div class="col-izq">

        <!-- Info admin actual -->
        <div class="admin-info">
            <div class="admin-avatar"><?= strtoupper(substr($_SESSION['usuario'],0,1)) ?></div>
            <div>
                <div class="admin-nombre"><?= htmlspecialchars($_SESSION['nombreReal']) ?></div>
                <div class="admin-detalle">@<?= $usuario ?> · Administrador</div>
            </div>
        </div>

        <!-- Crear vendedor -->
        <div class="card">
            <div class="card-titulo">Nuevo vendedor</div>
            <div class="card-sub">El vendedor podrá ingresar al sistema inmediatamente.</div>
            <form method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="campo">
                    <label>Nombre completo</label>
                    <input type="text" name="nombreReal" placeholder="Ej: María Torres" required
                           value="<?= htmlspecialchars($_POST['nombreReal'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label>Usuario (para login)</label>
                    <input type="text" name="usuario" placeholder="Ej: matorres" required
                           value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
                    <small>Sin espacios ni caracteres especiales</small>
                </div>
                <div class="campo">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                </div>
                <button type="submit" class="btn-primary">+ Crear vendedor</button>
            </form>
        </div>

        <!-- Cambiar contraseña admin -->
        <div class="card">
            <div class="card-titulo">Mi contraseña</div>
            <div class="card-sub">Cambia la contraseña del administrador.</div>
            <form method="POST">
                <input type="hidden" name="accion" value="cambiar_pass">
                <div class="campo">
                    <label>Nueva contraseña</label>
                    <input type="password" name="nuevaPass" placeholder="Mínimo 6 caracteres" required>
                </div>
                <button type="submit" class="btn-danger">Actualizar contraseña</button>
            </form>
        </div>

    </div>

    <!-- TABLA VENDEDORES -->
    <div class="tabla-section">
        <div class="tabla-top">
            <div class="tabla-info">
                <strong><?= count($vendedores) ?></strong>
                vendedor<?= count($vendedores) !== 1 ? 'es' : '' ?> registrado<?= count($vendedores) !== 1 ? 's' : '' ?>
            </div>
        </div>

        <div class="tabla-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Registrado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vendedores)): ?>
                        <tr class="empty-row">
                            <td colspan="6">No hay vendedores aún. Crea uno con el formulario.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vendedores as $v): ?>
                        <tr>
                            <td class="muted"><?= $v['idUsuario'] ?></td>
                            <td><strong><?= htmlspecialchars($v['nombreReal']) ?></strong></td>
                            <td class="muted">@<?= htmlspecialchars($v['usuario']) ?></td>
                            <td>
                                <span class="badge-activo <?= $v['activo'] ? 'si' : 'no' ?>">
                                    <?= $v['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="muted" style="font-size:12px">
                                <?= date('d/m/Y', strtotime($v['fechaCreado'])) ?>
                            </td>
                            <td>
                                <div class="acciones">
                                    <!-- Activar / Desactivar -->
                                    <form method="POST">
                                        <input type="hidden" name="accion" value="toggle">
                                        <input type="hidden" name="id" value="<?= $v['idUsuario'] ?>">
                                        <input type="hidden" name="activo" value="<?= $v['activo'] ? 0 : 1 ?>">
                                        <button type="submit"
                                            class="btn-sm <?= $v['activo'] ? 'btn-toggle-off' : 'btn-toggle-on' ?>">
                                            <?= $v['activo'] ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                    <!-- Eliminar -->
                                    <form method="POST"
                                          onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars($v['nombreReal']) ?>? Sus registros quedarán sin asignar.')">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $v['idUsuario'] ?>">
                                        <button type="submit" class="btn-sm btn-del">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Nota informativa -->
        <div style="margin-top:20px;padding:16px 20px;background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.15);border-radius:8px;font-size:12px;color:#78716c;line-height:1.7">
            <strong style="color:#fbbf24">¿Qué puede hacer un vendedor?</strong><br>
            Registrar, editar y eliminar clientes y autos que él mismo haya creado. No puede ver los datos de otros vendedores ni acceder a esta página. Si se desactiva, no puede iniciar sesión pero sus datos se conservan.
        </div>
    </div>

</div>

<?php if ($mensaje): ?>
    <div class="toast <?= $tipo ?>" id="toast"><?= htmlspecialchars($mensaje) ?></div>
    <script>setTimeout(()=>document.getElementById('toast').remove(),4000)</script>
<?php endif; ?>

</body>
</html>
