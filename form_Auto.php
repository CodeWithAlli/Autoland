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

$mensaje = '';
$tipo    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'insertar') {
        $data->insertAuto(
            trim($_POST['marca']),
            trim($_POST['modelo']),
            intval($_POST['anio']),
            trim($_POST['color']),
            floatval($_POST['precio']),
            intval($_POST['kilometraje']),
            $_POST['combustible'],
            intval($_POST['idIndividuo']),
            $idUsuario
        );
        $mensaje = '✓ Auto registrado correctamente.';
        $tipo    = 'ok';

    } elseif ($accion === 'actualizar') {
        $data->actualizarAuto(
            intval($_POST['id']),
            trim($_POST['marca']),
            trim($_POST['modelo']),
            intval($_POST['anio']),
            trim($_POST['color']),
            floatval($_POST['precio']),
            intval($_POST['kilometraje']),
            $_POST['combustible'],
            intval($_POST['idIndividuo']),
            $idUsuario, $rol
        );
        $mensaje = '✓ Auto actualizado.';
        $tipo    = 'ok';

    } elseif ($accion === 'eliminar') {
        $data->eliminarAuto(intval($_POST['id']), $idUsuario, $rol);
        $mensaje = '⚠ Auto eliminado.';
        $tipo    = 'warn';
    }
}

$busqueda = trim($_GET['q'] ?? '');
$autos    = $busqueda
    ? $data->buscarAutoPorTexto($busqueda, $idUsuario, $rol)->fetchAll()
    : $data->listarAuto($idUsuario, $rol)->fetchAll();

$editar   = null;
if (isset($_GET['editar'])) {
    $row    = $data->buscarAuto(intval($_GET['editar']))->fetch();
    $editar = $row ?: null;
}

$clientes = $data->listarIndividuoSelect($idUsuario, $rol);

$combustibles = ['Gasolina', 'Diésel', 'Híbrido', 'Eléctrico'];
$anioActual   = (int) date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUTOLAND — Autos</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --negro:#0a0a0a; --rojo:#c0392b; --rojo2:#e74c3c; --gris:#111; --gris2:#1a1a1a; --gris3:#222; --claro:#f5f0eb; --texto:#888; }
        body { font-family: 'DM Sans', sans-serif; background: var(--negro); color: var(--claro); min-height: 100vh; }

        .navbar { display:flex; align-items:center; justify-content:space-between; padding:0 48px; height:64px; background:var(--gris); border-bottom:1px solid #1f1f1f; position:sticky; top:0; z-index:100; }
        .nav-logo { font-family:'Bebas Neue',sans-serif; font-size:24px; letter-spacing:3px; }
        .nav-logo span { color:var(--rojo); }
        .nav-links { display:flex; gap:4px; }
        .nav-links a { text-decoration:none; color:var(--texto); font-size:13px; padding:8px 16px; border-radius:6px; transition:all 0.2s; }
        .nav-links a:hover, .nav-links a.active { color:var(--claro); background:var(--gris2); }
        .nav-links a.active { color:var(--rojo); }
        .btn-logout { background:transparent; border:1px solid #2a2a2a; color:#555; padding:7px 14px; border-radius:6px; font-size:12px; cursor:pointer; font-family:'DM Sans',sans-serif; text-decoration:none; transition:all 0.2s; }
        .btn-logout:hover { border-color:var(--rojo); color:var(--rojo); }

        .page-header { padding:48px 48px 32px; border-bottom:1px solid #141414; }
        .page-eyebrow { font-size:11px; letter-spacing:4px; text-transform:uppercase; color:var(--rojo); font-weight:600; margin-bottom:8px; }
        .page-titulo { font-family:'Bebas Neue',sans-serif; font-size:56px; line-height:1; }

        .layout { display:grid; grid-template-columns:380px 1fr; gap:24px; padding:32px 48px; align-items:start; }

        .form-card { background:var(--gris2); border:1px solid #1f1f1f; border-radius:12px; padding:28px; position:sticky; top:80px; }
        .form-card.modo-editar { border-color:var(--rojo); }
        .form-titulo { font-size:13px; font-weight:700; letter-spacing:2px; text-transform:uppercase; margin-bottom:4px; }
        .form-sub { font-size:12px; color:var(--texto); margin-bottom:24px; }

        .campo { margin-bottom:14px; }
        .campo label { display:block; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#555; font-weight:600; margin-bottom:6px; }
        .campo input, .campo select { width:100%; background:var(--gris3); border:1px solid #2a2a2a; border-radius:6px; padding:11px 14px; font-size:13px; color:var(--claro); font-family:'DM Sans',sans-serif; outline:none; transition:border-color 0.2s; }
        .campo input:focus, .campo select:focus { border-color:var(--rojo); }
        .campo select option { background:var(--gris3); }
        .campos-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

        .btn-primary { width:100%; background:var(--rojo); color:#fff; border:none; border-radius:6px; padding:13px; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase; cursor:pointer; font-family:'DM Sans',sans-serif; margin-top:8px; transition:background 0.2s; }
        .btn-primary:hover { background:var(--rojo2); }
        .btn-secondary { width:100%; background:transparent; color:var(--texto); border:1px solid #2a2a2a; border-radius:6px; padding:11px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:1px; cursor:pointer; font-family:'DM Sans',sans-serif; margin-top:8px; transition:all 0.2s; text-decoration:none; display:block; text-align:center; }
        .btn-secondary:hover { border-color:#444; color:var(--claro); }

        .tabla-section { min-width:0; }
        .tabla-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; gap:12px; flex-wrap:wrap; }
        .tabla-info { font-size:12px; color:var(--texto); }
        .tabla-info strong { color:var(--claro); }
        .search-wrap { position:relative; }
        .search-wrap input { background:var(--gris2); border:1px solid #1f1f1f; border-radius:6px; padding:10px 14px 10px 36px; font-size:13px; color:var(--claro); font-family:'DM Sans',sans-serif; width:240px; outline:none; transition:border-color 0.2s; }
        .search-wrap input:focus { border-color:var(--rojo); }
        .search-wrap::before { content:'🔍'; position:absolute; left:11px; top:50%; transform:translateY(-50%); font-size:13px; }

        .tabla-wrap { overflow-x:auto; border-radius:10px; border:1px solid #1f1f1f; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        thead tr { background:var(--gris2); border-bottom:1px solid #1f1f1f; }
        thead th { padding:14px 16px; text-align:left; font-size:10px; letter-spacing:2px; text-transform:uppercase; color:#555; font-weight:600; white-space:nowrap; }
        tbody tr { border-bottom:1px solid #141414; transition:background 0.15s; }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:hover { background:var(--gris2); }
        td { padding:13px 16px; vertical-align:middle; }
        td.muted { color:var(--texto); }

        .precio-tag { font-family:'Bebas Neue',sans-serif; font-size:16px; color:var(--claro); letter-spacing:1px; }
        .badge-comb { display:inline-block; padding:3px 9px; border-radius:20px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
        .comb-Gasolina  { background:rgba(251,191,36,0.15);  color:#fbbf24; }
        .comb-Diésel    { background:rgba(156,163,175,0.15); color:#9ca3af; }
        .comb-Híbrido   { background:rgba(52,211,153,0.15);  color:#34d399; }
        .comb-Eléctrico { background:rgba(96,165,250,0.15);  color:#60a5fa; }

        .acciones { display:flex; gap:6px; }
        .btn-edit, .btn-del { padding:6px 12px; border-radius:5px; font-size:11px; font-weight:600; cursor:pointer; text-decoration:none; border:none; font-family:'DM Sans',sans-serif; transition:all 0.2s; }
        .btn-edit { background:rgba(192,57,43,0.15); color:var(--rojo); }
        .btn-edit:hover { background:var(--rojo); color:#fff; }
        .btn-del { background:rgba(255,255,255,0.04); color:#555; }
        .btn-del:hover { background:rgba(255,80,80,0.15); color:#f87171; }

        .empty-row td { text-align:center; padding:48px; color:#333; }

        .toast { position:fixed; bottom:24px; right:24px; z-index:999; padding:14px 20px; border-radius:8px; font-size:13px; font-weight:600; animation:toastIn 0.3s ease both; }
        .toast.ok   { background:rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.3); color:#4ade80; }
        .toast.warn { background:rgba(234,179,8,0.15); border:1px solid rgba(234,179,8,0.3); color:#fbbf24; }
        @keyframes toastIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

        @media(max-width:900px){.layout{grid-template-columns:1fr}.form-card{position:static}.navbar,.page-header,.layout{padding-left:20px;padding-right:20px}.nav-links{display:none}}
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">AUTO<span>LAND</span></div>
    <div class="nav-links">
        <a href="index.php">Inicio</a>
        <a href="form_Individuo.php">Clientes</a>
        <a href="form_Auto.php" class="active">Autos</a>
        <?php if ($esAdmin): ?><a href="usuarios.php">Usuarios</a><?php endif; ?>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
        <?php if ($esAdmin): ?><span style="background:rgba(192,57,43,.2);color:#c0392b;padding:3px 8px;border-radius:4px;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase">Admin</span><?php endif; ?>
        <span style="font-size:12px;color:#444"><strong style="color:#666"><?= $nombreReal ?></strong></span>
        <a href="logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</nav>

<div class="page-header">
    <div class="page-eyebrow">Gestión de inventario</div>
    <h1 class="page-titulo">Autos</h1>
</div>

<div class="layout">

    <!-- FORMULARIO -->
    <div class="form-card <?= $editar ? 'modo-editar' : '' ?>">
        <?php if ($editar): ?>
            <div class="form-titulo">Editar auto</div>
            <div class="form-sub">Modificando ID #<?= $editar['idAuto'] ?></div>
        <?php else: ?>
            <div class="form-titulo">Nuevo auto</div>
            <div class="form-sub">Completa los datos del vehículo</div>
        <?php endif; ?>

        <form method="POST" action="form_Auto.php">
            <input type="hidden" name="accion" value="<?= $editar ? 'actualizar' : 'insertar' ?>">
            <?php if ($editar): ?>
                <input type="hidden" name="id" value="<?= $editar['idAuto'] ?>">
            <?php endif; ?>

            <div class="campos-grid">
                <div class="campo">
                    <label>Marca</label>
                    <input type="text" name="marca" placeholder="Toyota, Kia…" required
                           value="<?= htmlspecialchars($editar['marca'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label>Año</label>
                    <input type="number" name="anio" min="1990" max="<?= $anioActual + 1 ?>"
                           placeholder="<?= $anioActual ?>"
                           value="<?= htmlspecialchars($editar['anio'] ?? $anioActual) ?>">
                </div>
            </div>
            <div class="campo">
                <label>Modelo</label>
                <input type="text" name="modelo" placeholder="Corolla 2.0 XEI" required
                       value="<?= htmlspecialchars($editar['modelo'] ?? '') ?>">
            </div>
            <div class="campos-grid">
                <div class="campo">
                    <label>Color</label>
                    <input type="text" name="color" placeholder="Blanco Perla"
                           value="<?= htmlspecialchars($editar['color'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label>Combustible</label>
                    <select name="combustible">
                        <?php foreach ($combustibles as $c): ?>
                            <option value="<?= $c ?>" <?= ($editar['combustible'] ?? 'Gasolina') === $c ? 'selected' : '' ?>>
                                <?= $c ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="campos-grid">
                <div class="campo">
                    <label>Precio (S/.)</label>
                    <input type="number" name="precio" min="0" step="0.01" placeholder="0.00"
                           value="<?= htmlspecialchars($editar['precio'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label>Kilometraje</label>
                    <input type="number" name="kilometraje" min="0" placeholder="0"
                           value="<?= htmlspecialchars($editar['kilometraje'] ?? '0') ?>">
                </div>
            </div>
            <div class="campo">
                <label>Propietario / Cliente</label>
                <select name="idIndividuo" required>
                    <option value="">— Selecciona un cliente —</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['idIndividuo'] ?>"
                            <?= ($editar['idIndividuo'] ?? '') == $c['idIndividuo'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-primary">
                <?= $editar ? '↑ Actualizar auto' : '+ Registrar auto' ?>
            </button>
        </form>

        <?php if ($editar): ?>
            <a href="form_Auto.php" class="btn-secondary">✕ Cancelar edición</a>
        <?php endif; ?>
    </div>

    <!-- TABLA -->
    <div class="tabla-section">
        <div class="tabla-top">
            <div class="tabla-info">
                <strong><?= count($autos) ?></strong> vehículo<?= count($autos) !== 1 ? 's' : '' ?>
                <?= $busqueda ? "para <strong>\"$busqueda\"</strong>" : 'en inventario' ?>
            </div>
            <form method="GET" action="form_Auto.php" class="search-wrap">
                <input type="text" name="q" placeholder="Buscar por marca o modelo…"
                       value="<?= htmlspecialchars($busqueda) ?>">
            </form>
        </div>

        <div class="tabla-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vehículo</th>
                        <th>Año</th>
                        <th>Color</th>
                        <th>Combustible</th>
                        <th>Km</th>
                        <th>Precio</th>
                        <th>Propietario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($autos)): ?>
                        <tr class="empty-row"><td colspan="9">No hay autos registrados aún.</td></tr>
                    <?php else: ?>
                        <?php foreach ($autos as $a): ?>
                        <tr>
                            <td class="muted"><?= $a['idAuto'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($a['marca']) ?></strong><br>
                                <span style="font-size:12px;color:var(--texto);"><?= htmlspecialchars($a['modelo']) ?></span>
                            </td>
                            <td class="muted"><?= $a['anio'] ?></td>
                            <td class="muted" style="font-size:12px;"><?= htmlspecialchars($a['color']) ?></td>
                            <td>
                                <span class="badge-comb comb-<?= htmlspecialchars($a['combustible']) ?>">
                                    <?= htmlspecialchars($a['combustible']) ?>
                                </span>
                            </td>
                            <td class="muted" style="font-size:12px;"><?= number_format($a['kilometraje']) ?> km</td>
                            <td><span class="precio-tag">S/ <?= number_format($a['precio'], 2) ?></span></td>
                            <td class="muted" style="font-size:12px;"><?= htmlspecialchars($a['propietario'] ?? '—') ?></td>
                            <td>
                                <div class="acciones">
                                    <a href="form_Auto.php?editar=<?= $a['idAuto'] ?>" class="btn-edit">Editar</a>
                                    <form method="POST" action="form_Auto.php"
                                          onsubmit="return confirm('¿Eliminar este auto?')">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $a['idAuto'] ?>">
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
    <div class="toast <?= $tipo ?>" id="toast"><?= $mensaje ?></div>
    <script>setTimeout(() => document.getElementById('toast').remove(), 3500);</script>
<?php endif; ?>

</body>
</html>
