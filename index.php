<?php
session_start();
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit();
}
require_once 'config/data.php';
$data       = new Data();
$usuario    = htmlspecialchars($_SESSION['usuario']);
$nombreReal = htmlspecialchars($_SESSION['nombreReal']);
$rol        = $_SESSION['rol'];
$idUsuario  = $_SESSION['idUsuario'];
$esAdmin    = $rol === 'admin';

// Estadísticas según rol
$totalClientes  = $data->contarIndividuos($idUsuario, $rol);
$totalAutos     = $data->contarAutos($idUsuario, $rol);
$valorInv       = $data->valorInventario($idUsuario, $rol);
$totalVendedores = $esAdmin ? $data->contarVendedores() : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUTOLAND — Panel Principal</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --negro: #0a0a0a;
            --rojo:  #c0392b;
            --rojo2: #e74c3c;
            --gris:  #111111;
            --gris2: #1a1a1a;
            --gris3: #222222;
            --claro: #f5f0eb;
            --texto: #888;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--negro);
            color: var(--claro);
            min-height: 100vh;
        }

        /* ── NAVBAR ── */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 48px;
            height: 64px;
            background: var(--gris);
            border-bottom: 1px solid #1f1f1f;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-logo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 24px;
            letter-spacing: 3px;
            color: var(--claro);
        }
        .nav-logo span { color: var(--rojo); }

        .nav-links {
            display: flex;
            gap: 4px;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--texto);
            font-size: 13px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            transition: color 0.2s, background 0.2s;
            letter-spacing: 0.5px;
        }
        .nav-links a:hover {
            color: var(--claro);
            background: var(--gris2);
        }

        .nav-usuario {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .nav-saludo {
            font-size: 12px;
            color: #444;
        }
        .nav-saludo strong { color: #888; }
        .btn-logout {
            background: transparent;
            border: 1px solid #2a2a2a;
            color: #555;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-logout:hover {
            border-color: var(--rojo);
            color: var(--rojo);
        }

        /* ── HERO ── */
        .hero {
            padding: 64px 48px 48px;
            border-bottom: 1px solid #141414;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(192,57,43,0.08) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-eyebrow {
            font-size: 11px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--rojo);
            font-weight: 600;
            margin-bottom: 12px;
        }
        .hero-titulo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 72px;
            line-height: 1;
            letter-spacing: 2px;
            color: var(--claro);
            margin-bottom: 16px;
        }
        .hero-titulo span { color: var(--rojo); }
        .hero-sub {
            font-size: 15px;
            color: var(--texto);
            font-weight: 300;
            max-width: 480px;
            line-height: 1.6;
        }

        /* ── CARDS ACCIONES ── */
        .section {
            padding: 48px;
        }
        .section-label {
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #444;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
            margin-bottom: 48px;
        }

        .card {
            background: var(--gris2);
            border: 1px solid #1f1f1f;
            border-radius: 12px;
            padding: 32px;
            text-decoration: none;
            color: var(--claro);
            transition: border-color 0.2s, background 0.2s, transform 0.2s;
            display: block;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 20%, rgba(192,57,43,0.06) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .card:hover {
            border-color: var(--rojo);
            background: #1e1e1e;
            transform: translateY(-2px);
        }
        .card:hover::before { opacity: 1; }

        .card-icono {
            font-size: 28px;
            margin-bottom: 20px;
            display: block;
        }
        .card-titulo {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--claro);
        }
        .card-desc {
            font-size: 13px;
            color: var(--texto);
            line-height: 1.6;
            font-weight: 300;
        }
        .card-arrow {
            position: absolute;
            bottom: 28px;
            right: 28px;
            font-size: 18px;
            color: #2a2a2a;
            transition: color 0.2s, transform 0.2s;
        }
        .card:hover .card-arrow {
            color: var(--rojo);
            transform: translate(3px, -3px);
        }

        /* Card destacada (roja) */
        .card-highlight {
            background: var(--rojo);
            border-color: var(--rojo);
        }
        .card-highlight::before { display: none; }
        .card-highlight:hover {
            background: var(--rojo2);
            border-color: var(--rojo2);
        }
        .card-highlight .card-desc { color: rgba(255,255,255,0.75); }
        .card-highlight .card-arrow { color: rgba(255,255,255,0.3); }
        .card-highlight:hover .card-arrow { color: #fff; }

        /* ── GRAFICOS ── */
        .graficos-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .grafico-card {
            background: var(--gris2);
            border: 1px solid #1f1f1f;
            border-radius: 12px;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .grafico-card:hover { border-color: #333; }
        .grafico-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .grafico-titulo {
            font-size: 14px;
            font-weight: 600;
            color: var(--claro);
        }
        .grafico-badge {
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            background: rgba(192,57,43,0.15);
            color: var(--rojo);
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        .grafico-body {
            padding: 20px 24px;
        }
        .grafico-body img {
            width: 100%;
            border-radius: 6px;
            display: block;
        }
        .grafico-placeholder {
            height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #2a2a2a;
            gap: 8px;
        }
        .grafico-placeholder span { font-size: 36px; }
        .grafico-placeholder p { font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
        .guia-body { padding: 8px 4px; }
        .guia-lista { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .guia-item { font-size: 13px; padding-left: 24px; position: relative; line-height: 1.5; }
        .guia-item::before { position: absolute; left: 0; font-size: 13px; }
        .guia-item.permitido { color: #aaa; }
        .guia-item.permitido::before { content: '✓'; color: #27ae60; font-weight: 700; }
        .guia-item.bloqueado { color: #444; }
        .guia-item.bloqueado::before { content: '✗'; color: #c0392b; font-weight: 700; }
        .guia-item strong { color: #f5f0eb; font-weight: 600; }

        /* ── FOOTER ── */
        .footer {
            padding: 24px 48px;
            border-top: 1px solid #141414;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 48px;
        }
        .footer-text {
            font-size: 12px;
            color: #2a2a2a;
        }
        .footer-logo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 16px;
            letter-spacing: 3px;
            color: #222;
        }
        .footer-logo span { color: #3a1a1a; }

        /* Animaciones de entrada */
        .fade-in {
            animation: fadeIn 0.5s ease both;
        }
        .fade-in:nth-child(1) { animation-delay: 0.05s; }
        .fade-in:nth-child(2) { animation-delay: 0.10s; }
        .fade-in:nth-child(3) { animation-delay: 0.15s; }
        .fade-in:nth-child(4) { animation-delay: 0.20s; }
        .fade-in:nth-child(5) { animation-delay: 0.25s; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .hero { padding: 40px 20px 32px; }
            .hero-titulo { font-size: 48px; }
            .section { padding: 32px 20px; }
            .graficos-grid { grid-template-columns: 1fr; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-logo">AUTO<span>LAND</span></div>
        <div class="nav-links">
            <a href="index.php">Inicio</a>
            <a href="form_Individuo.php">Clientes</a>
            <a href="form_Auto.php">Autos</a>
            <?php if ($esAdmin): ?>
                <a href="usuarios.php">Usuarios</a>
            <?php endif; ?>
        </div>
        <div class="nav-usuario">
            <span class="nav-saludo">
                <?php if ($esAdmin): ?>
                    <span style="background:rgba(192,57,43,.2);color:#c0392b;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-right:6px;">Admin</span>
                <?php endif; ?>
                <strong><?= $nombreReal ?></strong>
            </span>
            <a href="logout.php" class="btn-logout">Cerrar sesión</a>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-eyebrow">Panel de control — <?= date('d M Y') ?></div>
        <h1 class="hero-titulo">Bienvenido,<br><span><?= $nombreReal ?>.</span></h1>
        <p class="hero-sub">
            <?php if ($esAdmin): ?>
                Vista completa del sistema. Gestionas todos los clientes, autos y vendedores.
            <?php else: ?>
                Estás viendo solo tus registros. Tienes <strong><?= $totalClientes ?></strong> clientes y <strong><?= $totalAutos ?></strong> autos registrados.
            <?php endif; ?>
        </p>
    </section>

    <!-- Acciones principales -->
    <section class="section">
        <div class="section-label">Resumen</div>
        <div class="cards-grid">
            <a href="form_Individuo.php" class="card fade-in card-highlight">
                <span class="card-icono">👤</span>
                <div class="card-titulo">Clientes</div>
                <p class="card-desc"><?= $totalClientes ?> cliente<?= $totalClientes !== 1 ? 's' : '' ?> <?= $esAdmin ? 'en total' : 'registrados por ti' ?></p>
                <span class="card-arrow">↗</span>
            </a>

            <a href="form_Auto.php" class="card fade-in">
                <span class="card-icono">🚗</span>
                <div class="card-titulo">Autos</div>
                <p class="card-desc"><?= $totalAutos ?> vehículo<?= $totalAutos !== 1 ? 's' : '' ?> · S/ <?= number_format($valorInv, 0, '.', ',') ?> en inventario</p>
                <span class="card-arrow">↗</span>
            </a>

            <?php if ($esAdmin): ?>
            <a href="usuarios.php" class="card fade-in">
                <span class="card-icono">🔑</span>
                <div class="card-titulo">Vendedores</div>
                <p class="card-desc"><?= $totalVendedores ?> vendedor<?= $totalVendedores !== 1 ? 'es' : '' ?> activo<?= $totalVendedores !== 1 ? 's' : '' ?> en el sistema</p>
                <span class="card-arrow">↗</span>
            </a>
            <?php endif; ?>

            <a href="grafico_top5.php" class="card fade-in">
                <span class="card-icono">📊</span>
                <div class="card-titulo">Top 5 — Más caros</div>
                <p class="card-desc">Gráfico de barras con los vehículos de mayor valor.</p>
                <span class="card-arrow">↗</span>
            </a>
        </div>

        <!-- Sección gráficos embebidos -->
        <div class="section-label">Guía rápida del sistema</div>
        <div class="graficos-grid">

            <!-- Tarjeta Vendedor -->
            <div class="grafico-card">
                <div class="grafico-header">
                    <span class="grafico-titulo">🧑‍💼 Rol: Vendedor</span>
                    <span class="grafico-badge">Acceso limitado</span>
                </div>
                <div class="guia-body">
                    <ul class="guia-lista">
                        <li class="guia-item permitido">Registrar nuevos clientes</li>
                        <li class="guia-item permitido">Registrar autos al inventario</li>
                        <li class="guia-item permitido">Ver y editar <strong>solo sus propios</strong> registros</li>
                        <li class="guia-item permitido">Consultar estadísticas de su inventario</li>
                        <li class="guia-item bloqueado">Ver registros de otros vendedores</li>
                        <li class="guia-item bloqueado">Eliminar registros ajenos</li>
                        <li class="guia-item bloqueado">Acceder al panel de usuarios</li>
                    </ul>
                </div>
            </div>

            <!-- Tarjeta Administrador -->
            <div class="grafico-card">
                <div class="grafico-header">
                    <span class="grafico-titulo">🛡️ Rol: Administrador</span>
                    <span class="grafico-badge" style="background:rgba(39,174,96,.15);color:#27ae60">Acceso total</span>
                </div>
                <div class="guia-body">
                    <ul class="guia-lista">
                        <li class="guia-item permitido">Ver <strong>todos</strong> los clientes y autos del sistema</li>
                        <li class="guia-item permitido">Editar y eliminar cualquier registro</li>
                        <li class="guia-item permitido">Ver estadísticas de todo el inventario</li>
                        <li class="guia-item permitido">Crear y desactivar cuentas de vendedores</li>
                        <li class="guia-item permitido">Cambiar contraseñas de vendedores</li>
                        <li class="guia-item permitido">Gestionar usuarios desde el panel de administración</li>
                    </ul>
                </div>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <span class="footer-text">Sistema de uso interno — Solo personal autorizado</span>
        <span class="footer-logo">AUTO<span>LAND</span></span>
    </footer>

</body>
</html>
