<?php
session_start();
if (isset($_SESSION['idUsuario'])) {
    header("Location: index.php");
    exit();
}
require_once 'config/conexion.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userInput = trim($_POST['usuario'] ?? '');
    $passInput = trim($_POST['password'] ?? '');

    $db   = (new Conexion())->getConnection();
    $stmt = $db->prepare("SELECT * FROM usuario WHERE usuario = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$userInput]);
    $row  = $stmt->fetch();

    if ($row && password_verify($passInput, $row['password'])) {
        $_SESSION['idUsuario']  = $row['idUsuario'];
        $_SESSION['usuario']    = $row['usuario'];
        $_SESSION['nombreReal'] = $row['nombreReal'];
        $_SESSION['rol']        = $row['rol'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUTOLAND — Acceso</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --negro: #0a0a0a;
            --rojo: #c0392b;
            --rojo2: #e74c3c;
            --gris: #1a1a1a;
            --gris2: #2a2a2a;
            --claro: #f5f0eb;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--negro);
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            overflow: hidden;
        }

        /* ── PANEL IZQUIERDO ── */
        .panel-visual {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        .panel-visual .bg-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            z-index: 0;
        }

        .panel-visual::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 1;
            background:
                linear-gradient(to top, rgba(0, 0, 0, 0.92) 0%, rgba(0, 0, 0, 0.45) 50%, rgba(0, 0, 0, 0.25) 100%),
                radial-gradient(ellipse at 20% 80%, rgba(192, 57, 43, 0.30) 0%, transparent 60%);
        }

        .panel-visual::after {
            content: 'AUTOLAND';
            font-family: 'Bebas Neue', sans-serif;
            font-size: 140px;
            color: rgba(255, 255, 255, 0.04);
            position: absolute;
            bottom: -20px;
            left: -10px;
            line-height: 1;
            letter-spacing: 4px;
            pointer-events: none;
            z-index: 2;
        }

        .lineas {
            position: absolute;
            inset: 0;
            overflow: hidden;
            z-index: 2;
        }

        .linea {
            position: absolute;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(192, 57, 43, 0.5), transparent);
            animation: scan 4s ease-in-out infinite;
        }

        .linea:nth-child(1) {
            top: 20%;
            width: 60%;
            left: -60%;
            animation-delay: 0s;
        }

        .linea:nth-child(2) {
            top: 45%;
            width: 80%;
            left: -80%;
            animation-delay: 1.5s;
        }

        .linea:nth-child(3) {
            top: 70%;
            width: 50%;
            left: -50%;
            animation-delay: 3s;
        }

        @keyframes scan {
            0% {
                transform: translateX(0);
                opacity: 0;
            }

            20% {
                opacity: 1;
            }

            80% {
                opacity: 1;
            }

            100% {
                transform: translateX(300%);
                opacity: 0;
            }
        }

        .tagline {
            position: absolute;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -60%);
            z-index: 3;
            text-align: center;
        }

        .tagline-label {
            font-size: 11px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--rojo);
            font-weight: 600;
            margin-bottom: 16px;
        }

        .tagline-titulo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 64px;
            color: var(--claro);
            line-height: 1;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

        .tagline-titulo span {
            color: var(--rojo);
        }

        .tagline-sub {
            font-size: 14px;
            color: #bbb;
            line-height: 1.7;
            font-weight: 300;
            margin: 0 auto;
            max-width: 380px;
        }

        /* ── PANEL DERECHO ── */
        .panel-login {
            width: 440px;
            background: var(--negro);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 50px;
            border-left: 1px solid #1f1f1f;
        }

        .login-logo {
            margin-bottom: 48px;
        }

        .login-logo img {
            max-width: 200px;
            filter: brightness(0) invert(1);
            opacity: 0.9;
        }

        .login-logo-fallback {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 32px;
            color: var(--claro);
            letter-spacing: 3px;
        }

        .login-logo-fallback span {
            color: var(--rojo);
        }

        .login-titulo {
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .login-subtitulo {
            font-size: 26px;
            font-weight: 600;
            color: var(--claro);
            margin-bottom: 40px;
            line-height: 1.2;
        }

        .campo {
            margin-bottom: 20px;
        }

        .campo label {
            display: block;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #555;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .campo input {
            width: 100%;
            background: var(--gris2);
            border: 1px solid #2a2a2a;
            border-radius: 6px;
            padding: 14px 16px;
            font-size: 14px;
            color: var(--claro);
            font-family: 'DM Sans', sans-serif;
            transition: border-color 0.2s, background 0.2s;
            outline: none;
        }

        .campo input:focus {
            border-color: var(--rojo);
            background: #222;
        }

        .campo input::placeholder {
            color: #3a3a3a;
        }

        .error-msg {
            background: rgba(192, 57, 43, 0.15);
            border: 1px solid rgba(192, 57, 43, 0.4);
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 13px;
            color: #e74c3c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-msg::before {
            content: '⚠';
            font-size: 15px;
        }

        .btn-ingresar {
            width: 100%;
            background: var(--rojo);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 15px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            margin-top: 10px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-ingresar:hover {
            background: var(--rojo2);
        }

        .btn-ingresar:active {
            transform: scale(0.98);
        }

        .acceso-aviso {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #1a1a1a;
            font-size: 12px;
            color: #3a3a3a;
            line-height: 1.6;
            text-align: center;
        }

        .acceso-aviso strong {
            color: #555;
        }

        /* ── ANIMACIONES ── */
        .panel-login>* {
            animation: slideUp 0.5s ease both;
        }

        .panel-login>*:nth-child(1) {
            animation-delay: 0.10s;
        }

        .panel-login>*:nth-child(2) {
            animation-delay: 0.15s;
        }

        .panel-login>*:nth-child(3) {
            animation-delay: 0.20s;
        }

        .panel-login>*:nth-child(4) {
            animation-delay: 0.25s;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
                overflow: auto;
            }

            .panel-visual {
                display: none;
            }

            .panel-login {
                width: 100%;
                padding: 50px 30px;
            }
        }
    </style>
</head>

<body>

    <!-- Panel visual izquierdo -->
    <div class="panel-visual">
        <img class="bg-img"
            src="./img/fondo.png"
            alt="Autos AUTOLAND"
            onerror="this.style.display='none'">

        <div class="lineas">
            <div class="linea"></div>
            <div class="linea"></div>
            <div class="linea"></div>
        </div>

        <div class="tagline">
            <div class="tagline-label">Sistema de Gestión Interno</div>
            <div class="tagline-titulo">Gestiona tu <span>flota</span> con control.</div>
            <p class="tagline-sub">Plataforma exclusiva para personal autorizado de AUTOLAND. Acceso restringido.</p>
        </div>
    </div>

    <!-- Panel login derecho -->
    <div class="panel-login">
        <div class="login-logo">
            <img src="./img/foto1.jpg" alt="Autoland"
                onerror="this.style.display='none'; document.getElementById('logo-text').style.display='block'">
            <div class="login-logo-fallback" id="logo-text" style="display:none;">
                AUTO<span>LAND</span>
            </div>
        </div>

        <div class="login-titulo">Acceso al Sistema</div>
        <div class="login-subtitulo">Solo personal<br>autorizado.</div>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="campo">
                <label for="usuario">Usuario</label>
                <input type="text" name="usuario" id="usuario"
                    placeholder="Ingresa tu usuario"
                    value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                    autocomplete="username" required>
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password"
                    placeholder="••••••••"
                    autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn-ingresar">Ingresar →</button>
        </form>

        <div class="acceso-aviso">
            <strong>Acceso restringido.</strong><br>
            Este sistema es de uso exclusivo del personal de AUTOLAND.<br>
            Si necesitas acceso, contacta al administrador.
        </div>
    </div>

</body>

</html>