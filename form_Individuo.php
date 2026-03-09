<?php
session_start();
if (!isset($_SESSION['idUsuario'])) { header("Location: login.php"); exit(); }
require_once 'config/data.php';
$data       = new Data();
$usuario    = htmlspecialchars($_SESSION['usuario']);
$nombreReal = htmlspecialchars($_SESSION['nombreReal']);
$rol        = $_SESSION['rol'];
$idUsuario  = $_SESSION['idUsuario'];
$esAdmin    = $rol === 'admin';

$mensaje = ''; $tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'insertar') {
        $data->insertIndividuo(
            trim($_POST['nombres']), trim($_POST['apellidoP']), trim($_POST['apellidoM']),
            trim($_POST['dni']), trim($_POST['telefono']), trim($_POST['direccion']),
            intval($_POST['edad']), $_POST['sexo'], $idUsuario
        );
        $mensaje = '✓ Cliente registrado correctamente.'; $tipo = 'ok';

    } elseif ($accion === 'actualizar') {
        $data->actualizarIndividuo(
            intval($_POST['id']),
            trim($_POST['nombres']), trim($_POST['apellidoP']), trim($_POST['apellidoM']),
            trim($_POST['dni']), trim($_POST['telefono']), trim($_POST['direccion']),
            intval($_POST['edad']), $_POST['sexo'], $idUsuario, $rol
        );
        $mensaje = '✓ Cliente actualizado.'; $tipo = 'ok';

    } elseif ($accion === 'eliminar') {
        $data->eliminarIndividuo(intval($_POST['id']), $idUsuario, $rol);
        $mensaje = '⚠ Cliente eliminado.'; $tipo = 'warn';
    }
}

$busqueda   = trim($_GET['q'] ?? '');
$individuos = $busqueda
    ? $data->buscarIndividuoPorTexto($busqueda, $idUsuario, $rol)->fetchAll()
    : $data->listarIndividuo($idUsuario, $rol)->fetchAll();

$editar = null;
if (isset($_GET['editar'])) {
    $row = $data->buscarIndividuo(intval($_GET['editar']))->fetch();
    $editar = $row ?: null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUTOLAND — Clientes</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--negro:#0a0a0a;--rojo:#c0392b;--rojo2:#e74c3c;--gris:#111;--gris2:#1a1a1a;--gris3:#222;--claro:#f5f0eb;--texto:#888}
        body{font-family:'DM Sans',sans-serif;background:var(--negro);color:var(--claro);min-height:100vh}
        .navbar{display:flex;align-items:center;justify-content:space-between;padding:0 48px;height:64px;background:var(--gris);border-bottom:1px solid #1f1f1f;position:sticky;top:0;z-index:100}
        .nav-logo{font-family:'Bebas Neue',sans-serif;font-size:24px;letter-spacing:3px}
        .nav-logo span{color:var(--rojo)}
        .nav-links{display:flex;gap:4px}
        .nav-links a{text-decoration:none;color:var(--texto);font-size:13px;padding:8px 16px;border-radius:6px;transition:all .2s}
        .nav-links a:hover,.nav-links a.active{color:var(--claro);background:var(--gris2)}
        .nav-links a.active{color:var(--rojo)}
        .badge-admin{background:rgba(192,57,43,.2);color:var(--rojo);padding:3px 8px;border-radius:4px;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase}
        .btn-logout{background:transparent;border:1px solid #2a2a2a;color:#555;padding:7px 14px;border-radius:6px;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;transition:all .2s}
        .btn-logout:hover{border-color:var(--rojo);color:var(--rojo)}
        .page-header{padding:48px 48px 32px;border-bottom:1px solid #141414}
        .page-eyebrow{font-size:11px;letter-spacing:4px;text-transform:uppercase;color:var(--rojo);font-weight:600;margin-bottom:8px}
        .page-titulo{font-family:'Bebas Neue',sans-serif;font-size:56px;line-height:1}
        .page-sub{font-size:13px;color:var(--texto);margin-top:8px}
        .layout{display:grid;grid-template-columns:380px 1fr;gap:24px;padding:32px 48px;align-items:start}
        .form-card{background:var(--gris2);border:1px solid #1f1f1f;border-radius:12px;padding:28px;position:sticky;top:80px}
        .form-card.modo-editar{border-color:var(--rojo)}
        .form-titulo{font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:4px}
        .form-sub{font-size:12px;color:var(--texto);margin-bottom:20px}
        .campo{margin-bottom:14px}
        .campo label{display:block;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#555;font-weight:600;margin-bottom:6px}
        .campo input,.campo select{width:100%;background:var(--gris3);border:1px solid #2a2a2a;border-radius:6px;padding:11px 14px;font-size:13px;color:var(--claro);font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s}
        .campo input:focus,.campo select:focus{border-color:var(--rojo)}
        .campo select option{background:var(--gris3)}
        .campos-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .btn-primary{width:100%;background:var(--rojo);color:#fff;border:none;border-radius:6px;padding:13px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:8px;transition:background .2s}
        .btn-primary:hover{background:var(--rojo2)}
        .btn-secondary{width:100%;background:transparent;color:var(--texto);border:1px solid #2a2a2a;border-radius:6px;padding:11px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:1px;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:8px;transition:all .2s;text-decoration:none;display:block;text-align:center}
        .btn-secondary:hover{border-color:#444;color:var(--claro)}
        .tabla-section{min-width:0}
        .tabla-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap}
        .tabla-info{font-size:12px;color:var(--texto)}
        .tabla-info strong{color:var(--claro)}
        .search-wrap{position:relative}
        .search-wrap input{background:var(--gris2);border:1px solid #1f1f1f;border-radius:6px;padding:10px 14px 10px 36px;font-size:13px;color:var(--claro);font-family:'DM Sans',sans-serif;width:240px;outline:none;transition:border-color .2s}
        .search-wrap input:focus{border-color:var(--rojo)}
        .search-wrap::before{content:'🔍';position:absolute;left:11px;top:50%;transform:translateY(-50%);font-size:13px}
        .tabla-wrap{overflow-x:auto;border-radius:10px;border:1px solid #1f1f1f}
        table{width:100%;border-collapse:collapse;font-size:13px}
        thead tr{background:var(--gris2);border-bottom:1px solid #1f1f1f}
        thead th{padding:14px 16px;text-align:left;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:#555;font-weight:600;white-space:nowrap}
        tbody tr{border-bottom:1px solid #141414;transition:background .15s}
        tbody tr:last-child{border-bottom:none}
        tbody tr:hover{background:var(--gris2)}
        td{padding:13px 16px;vertical-align:middle}
        td.muted{color:var(--texto)}
        .badge-sexo{display:inline-block;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase}
        .badge-M{background:rgba(59,130,246,.15);color:#60a5fa}
        .badge-F{background:rgba(236,72,153,.15);color:#f472b6}
        .tag-vendedor{font-size:10px;background:rgba(255,255,255,.05);color:#555;padding:2px 7px;border-radius:4px}
        .acciones{display:flex;gap:6px}
        .btn-edit,.btn-del{padding:6px 12px;border-radius:5px;font-size:11px;font-weight:600;cursor:pointer;text-decoration:none;border:none;font-family:'DM Sans',sans-serif;transition:all .2s}
        .btn-edit{background:rgba(192,57,43,.15);color:var(--rojo)}
        .btn-edit:hover{background:var(--rojo);color:#fff}
        .btn-del{background:rgba(255,255,255,.04);color:#555}
        .btn-del:hover{background:rgba(255,80,80,.15);color:#f87171}
        .empty-row td{text-align:center;padding:48px;color:#333}
        .toast{position:fixed;bottom:24px;right:24px;z-index:999;padding:14px 20px;border-radius:8px;font-size:13px;font-weight:600;animation:toastIn .3s ease both}
        .toast.ok  {background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:#4ade80}
        .toast.warn{background:rgba(234,179,8,.15);border:1px solid rgba(234,179,8,.3);color:#fbbf24}
        @keyframes toastIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        @media(max-width:900px){.layout{grid-template-columns:1fr}.form-card{position:static}.navbar,.page-header,.layout{padding-left:20px;padding-right:20px}.nav-links{display:none}}
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">AUTO<span>LAND</span></div>
    <div class="nav-links">
        <a href="index.php">Inicio</a>
        <a href="form_Individuo.php" class="active">Clientes</a>
        <a href="form_Auto.php">Autos</a>
        <?php if ($esAdmin): ?><a href="usuarios.php">Usuarios</a><?php endif; ?>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
        <?php if ($esAdmin): ?><span class="badge-admin">Admin</span><?php endif; ?>
        <span style="font-size:12px;color:#444"><strong style="color:#666"><?= $nombreReal ?></strong></span>
        <a href="logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</nav>

<div class="page-header">
    <div class="page-eyebrow">Gestión de clientes</div>
    <h1 class="page-titulo">Clientes</h1>
    <?php if (!$esAdmin): ?>
        <p class="page-sub">Mostrando solo tus clientes registrados.</p>
    <?php endif; ?>
</div>

<div class="layout">

    <div class="form-card <?= $editar ? 'modo-editar' : '' ?>">
        <?php if ($editar): ?>
            <div class="form-titulo">Editar cliente</div>
            <div class="form-sub">Modificando ID #<?= $editar['idIndividuo'] ?></div>
        <?php else: ?>
            <div class="form-titulo">Nuevo cliente</div>
            <div class="form-sub">Completa los datos del cliente</div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="accion" value="<?= $editar ? 'actualizar' : 'insertar' ?>">
            <?php if ($editar): ?><input type="hidden" name="id" value="<?= $editar['idIndividuo'] ?>"><?php endif; ?>

            <div class="campo"><label>Nombres</label>
                <input type="text" name="nombres" placeholder="Ej: Carlos Alberto" required
                       value="<?= htmlspecialchars($editar['nombreIndividuo'] ?? '') ?>">
            </div>
            <div class="campos-grid">
                <div class="campo"><label>Apellido paterno</label>
                    <input type="text" name="apellidoP" required
                           value="<?= htmlspecialchars($editar['apellidoPaterno'] ?? '') ?>">
                </div>
                <div class="campo"><label>Apellido materno</label>
                    <input type="text" name="apellidoM"
                           value="<?= htmlspecialchars($editar['apellidoMaterno'] ?? '') ?>">
                </div>
            </div>
            <div class="campo"><label>DNI</label>
                <input type="text" name="dni" maxlength="8" placeholder="8 dígitos"
                       value="<?= htmlspecialchars($editar['dni'] ?? '') ?>">
            </div>
            <div class="campo"><label>Teléfono</label>
                <input type="text" name="telefono" placeholder="9XX XXX XXX"
                       value="<?= htmlspecialchars($editar['telefono'] ?? '') ?>">
            </div>
            <div class="campo"><label>Dirección</label>
                <input type="text" name="direccion"
                       value="<?= htmlspecialchars($editar['direccion'] ?? '') ?>">
            </div>
            <div class="campos-grid">
                <div class="campo"><label>Edad</label>
                    <input type="number" name="edad" min="18" max="99"
                           value="<?= htmlspecialchars($editar['edadIndividuo'] ?? '') ?>">
                </div>
                <div class="campo"><label>Sexo</label>
                    <select name="sexo">
                        <option value="M" <?= ($editar['sexoIndividuo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($editar['sexoIndividuo'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary">
                <?= $editar ? '↑ Actualizar cliente' : '+ Registrar cliente' ?>
            </button>
        </form>
        <?php if ($editar): ?>
            <a href="form_Individuo.php" class="btn-secondary">✕ Cancelar</a>
        <?php endif; ?>
    </div>

    <div class="tabla-section">
        <div class="tabla-top">
            <div class="tabla-info">
                <strong><?= count($individuos) ?></strong> cliente<?= count($individuos) !== 1 ? 's' : '' ?>
                <?= $busqueda ? "para <strong>\"".htmlspecialchars($busqueda)."\"</strong>" : ($esAdmin ? 'en total' : 'registrados por ti') ?>
            </div>
            <form method="GET" class="search-wrap">
                <input type="text" name="q" placeholder="Buscar por nombre o DNI…"
                       value="<?= htmlspecialchars($busqueda) ?>">
            </form>
        </div>

        <div class="tabla-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Nombre completo</th><th>DNI</th><th>Teléfono</th>
                        <th>Edad</th><th>Sexo</th>
                        <?php if ($esAdmin): ?><th>Registrado por</th><?php endif; ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($individuos)): ?>
                        <tr class="empty-row"><td colspan="<?= $esAdmin ? 8 : 7 ?>">No hay clientes aún.</td></tr>
                    <?php else: ?>
                        <?php foreach ($individuos as $i): ?>
                        <tr>
                            <td class="muted"><?= $i['idIndividuo'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($i['nombreIndividuo']) ?></strong><br>
                                <span style="font-size:12px;color:var(--texto)"><?= htmlspecialchars($i['apellidoPaterno'].' '.$i['apellidoMaterno']) ?></span>
                            </td>
                            <td class="muted"><?= htmlspecialchars($i['dni']) ?></td>
                            <td class="muted"><?= htmlspecialchars($i['telefono']) ?></td>
                            <td class="muted"><?= $i['edadIndividuo'] ?></td>
                            <td><span class="badge-sexo badge-<?= $i['sexoIndividuo'] ?>"><?= $i['sexoIndividuo'] === 'M' ? 'Masc.' : 'Fem.' ?></span></td>
                            <?php if ($esAdmin): ?>
                                <td><span class="tag-vendedor"><?= htmlspecialchars($i['registradoPor'] ?? '—') ?></span></td>
                            <?php endif; ?>
                            <td>
                                <div class="acciones">
                                    <a href="form_Individuo.php?editar=<?= $i['idIndividuo'] ?>" class="btn-edit">Editar</a>
                                    <form method="POST" onsubmit="return confirm('¿Eliminar este cliente?')">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $i['idIndividuo'] ?>">
                                        <button type="submit" class="btn-del">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="toast <?= $tipo ?>" id="toast"><?= htmlspecialchars($mensaje) ?></div>
    <script>setTimeout(()=>document.getElementById('toast').remove(),3500)</script>
<?php endif; ?>
</body>
</html>
