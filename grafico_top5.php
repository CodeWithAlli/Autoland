<?php
session_start();
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php"); exit();
}
require_once __DIR__ . '/config/data.php';
$data      = new Data();
$idUsuario = $_SESSION['idUsuario'];
$rol       = $_SESSION['rol'];
$nombreReal = htmlspecialchars($_SESSION['nombreReal']);
$esAdmin   = $rol === 'admin';
$filas     = $data->top5AutosMasCaros($idUsuario, $rol);

$labels  = array_map(fn($f) => $f['nombre'], $filas);
$precios = array_map(fn($f) => floatval($f['precio']), $filas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUTOLAND — Top 5 Autos</title>
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
        .chart-card{background:var(--gris2);border:1px solid #1f1f1f;border-radius:12px;padding:32px}
        .chart-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px}
        .chart-titulo{font-size:15px;font-weight:700;color:var(--claro)}
        .chart-badge{font-size:10px;letter-spacing:1px;text-transform:uppercase;background:rgba(192,57,43,.15);color:var(--rojo);padding:4px 10px;border-radius:20px;font-weight:600}
        .chart-wrap{position:relative;height:380px}
        .empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;height:300px;color:#333;gap:12px}
        .empty-state span{font-size:40px}
        .empty-state p{font-size:13px;letter-spacing:1px;text-transform:uppercase}
        @media(max-width:768px){.navbar,.page-header,.chart-section{padding-left:20px;padding-right:20px}.nav-links{display:none}}
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
    <h1 class="page-titulo">Top 5 — Más caros</h1>
    <p class="page-sub"><?= $esAdmin ? 'Todo el inventario' : 'Tu inventario' ?></p>
</div>

<div class="chart-section">
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-titulo">Los 5 vehículos de mayor valor</span>
            <span class="chart-badge">Barras</span>
        </div>
        <?php if (empty($filas)): ?>
            <div class="empty-state">
                <span>📊</span>
                <p>Sin datos aún</p>
            </div>
        <?php else: ?>
            <div class="chart-wrap">
                <canvas id="barChart"></canvas>
            </div>
            <script>
                const ctx = document.getElementById('barChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($labels) ?>,
                        datasets: [{
                            label: 'Precio (S/)',
                            data: <?= json_encode($precios) ?>,
                            backgroundColor: [
                                'rgba(192,57,43,0.85)',
                                'rgba(231,76,60,0.85)',
                                'rgba(146,43,33,0.85)',
                                'rgba(241,148,138,0.85)',
                                'rgba(100,30,22,0.85)'
                            ],
                            borderColor: [
                                '#c0392b','#e74c3c','#922b21','#f1948a','#641e16'
                            ],
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
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
                        },
                        scales: {
                            x: {
                                ticks: { color: '#666', font: { family: 'DM Sans', size: 12 } },
                                grid: { color: '#1a1a1a' }
                            },
                            y: {
                                ticks: {
                                    color: '#666',
                                    font: { family: 'DM Sans', size: 11 },
                                    callback: val => 'S/ ' + val.toLocaleString('es-PE')
                                },
                                grid: { color: '#222' }
                            }
                        }
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
