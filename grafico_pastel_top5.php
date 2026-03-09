<?php
session_start();
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php"); exit();
}
require_once __DIR__ . '/config/data.php';
$data       = new Data();
$idUsuario  = $_SESSION['idUsuario'];
$rol        = $_SESSION['rol'];
$nombreReal = htmlspecialchars($_SESSION['nombreReal']);
$esAdmin    = $rol === 'admin';
$filas      = $data->top5AutosMasCaros($idUsuario, $rol);

$labels  = array_map(fn($f) => $f['nombre'], $filas);
$precios = array_map(fn($f) => floatval($f['precio']), $filas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUTOLAND — Distribución</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--negro:#0a0a0a;--rojo:#c0392b;--rojo2:#e74c3c;--gris:#111;--gris2:#1a1a1a;--claro:#f5f0eb;--texto:#888}
        body{font-family:'DM Sans',sans-serif;background:var(--negro);color:var(--claro);min-height:100vh}
        .navbar{display:flex;align-items:center;justify-content:space-between;padding:0 48px;height:64px;background:var(--gris);border-bottom:1px solid #1f1f1f;position:sticky;top:0;z-index:100}
        .nav-logo{font-family:'Bebas Neue',sans-serif;font-size:24px;letter-spacing:3px}
        .nav-logo span{color:var(--rojo)}
        .nav-links{display:flex;gap:4px}
        .nav-links a{text-decoration:none;color:var(--texto);font-size:13px;padding:8px 16px;border-radius:6px;transition:all .2s}
        .nav-links a:hover,.nav-links a.active{color:var(--claro);background:var(--gris2)}
        .nav-links a.active{color:var(--rojo)}
        .btn-logout{background:transparent;border:1px solid #2a2a2a;color:#555;padding:7px 14px;border-radius:6px;font-size:12px;text-decoration:none;transition:all .2s}
        .btn-logout:hover{border-color:var(--rojo);color:var(--rojo)}
        .page-header{padding:48px 48px 32px;border-bottom:1px solid #141414}
        .page-eyebrow{font-size:11px;letter-spacing:4px;text-transform:uppercase;color:var(--rojo);font-weight:600;margin-bottom:8px}
        .page-titulo{font-family:'Bebas Neue',sans-serif;font-size:56px;line-height:1}
        .page-sub{font-size:13px;color:var(--texto);margin-top:8px}
        .chart-section{padding:40px 48px}
        .chart-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}
        .chart-card{background:var(--gris2);border:1px solid #1f1f1f;border-radius:12px;padding:32px}
        .chart-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px}
        .chart-titulo{font-size:15px;font-weight:700;color:var(--claro)}
        .chart-badge{font-size:10px;letter-spacing:1px;text-transform:uppercase;background:rgba(192,57,43,.15);color:var(--rojo);padding:4px 10px;border-radius:20px;font-weight:600}
        .chart-wrap{position:relative;height:340px}
        .empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;height:300px;color:#333;gap:12px}
        .empty-state span{font-size:40px}
        .empty-state p{font-size:13px;letter-spacing:1px;text-transform:uppercase}
        .tabla-precios{width:100%;border-collapse:collapse;font-size:13px;margin-top:8px}
        .tabla-precios tr{border-bottom:1px solid #222}
        .tabla-precios tr:last-child{border:none}
        .tabla-precios td{padding:12px 8px;vertical-align:middle}
        .dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:8px}
        .precio-val{font-family:'Bebas Neue',sans-serif;font-size:16px;color:var(--claro);letter-spacing:1px}
        @media(max-width:900px){.chart-grid{grid-template-columns:1fr}.navbar,.page-header,.chart-section{padding-left:20px;padding-right:20px}.nav-links{display:none}}
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">AUTO<span>LAND</span></div>
    <div class="nav-links">
<a href="index.php">Inicio</a>
<a href="form_Individuo.php">Clientes</a>
<a href="form_Auto.php">Autos</a>
<?php if($esAdmin): ?>
    <a href="usuarios.php">Usuarios</a>
    <a href="grafico_top5.php" class="active">Top 5</a>
    <a href="grafico_pastel_top5.php">Distribución</a>
<?php endif; ?>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
        <span style="font-size:12px;color:#444"><strong style="color:#666"><?= $nombreReal ?></strong></span>
        <a href="logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</nav>

<div class="page-header">
    <div class="page-eyebrow">Estadísticas</div>
    <h1 class="page-titulo">Distribución de precios</h1>
    <p class="page-sub"><?= $esAdmin ? 'Todo el inventario' : 'Tu inventario' ?></p>
</div>

<div class="chart-section">
    <?php if (empty($filas)): ?>
        <div class="chart-card">
            <div class="empty-state"><span>🥧</span><p>Sin datos aún</p></div>
        </div>
    <?php else: ?>
    <div class="chart-grid">

        <!-- Gráfico pastel -->
        <div class="chart-card">
            <div class="chart-header">
                <span class="chart-titulo">Distribución por valor</span>
                <span class="chart-badge">Pastel</span>
            </div>
            <div class="chart-wrap">
                <canvas id="pieChart"></canvas>
            </div>
        </div>

        <!-- Tabla detalle -->
        <div class="chart-card">
            <div class="chart-header">
                <span class="chart-titulo">Detalle Top 5</span>
                <span class="chart-badge">Ranking</span>
            </div>
            <table class="tabla-precios">
                <?php
                $colores = ['#c0392b','#e74c3c','#922b21','#f1948a','#641e16'];
                $total   = array_sum($precios);
                foreach ($filas as $i => $f):
                    $pct = $total > 0 ? round(($f['precio'] / $total) * 100, 1) : 0;
                ?>
                <tr>
                    <td style="width:20px">
                        <span class="dot" style="background:<?= $colores[$i] ?>"></span>
                    </td>
                    <td style="color:#aaa"><?= htmlspecialchars($f['nombre']) ?></td>
                    <td style="text-align:right">
                        <span class="precio-val">S/ <?= number_format($f['precio'], 0, '.', ',') ?></span>
                    </td>
                    <td style="text-align:right;color:#555;font-size:12px"><?= $pct ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #222;display:flex;justify-content:space-between;font-size:12px;color:#555">
                <span>Valor total Top 5</span>
                <span style="color:var(--claro);font-family:'Bebas Neue',sans-serif;font-size:16px;letter-spacing:1px">
                    S/ <?= number_format($total, 0, '.', ',') ?>
                </span>
            </div>
        </div>

    </div>

    <script>
        const ctx = document.getElementById('pieChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    data: <?= json_encode($precios) ?>,
                    backgroundColor: ['#c0392b','#e74c3c','#922b21','#f1948a','#641e16'],
                    borderColor: '#1a1a1a',
                    borderWidth: 3,
                    hoverOffset: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#666',
                            font: { family: 'DM Sans', size: 11 },
                            padding: 16,
                            boxWidth: 10,
                            boxHeight: 10,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' S/ ' + ctx.raw.toLocaleString('es-PE', {minimumFractionDigits:2})
                        },
                        backgroundColor: '#1a1a1a',
                        borderColor: '#333',
                        borderWidth: 1,
                        titleColor: '#f5f0eb',
                        bodyColor: '#c0392b',
                        padding: 12
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</div>

</body>
</html>
