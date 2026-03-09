<?php
/**
 * AUTOLAND — Generador de contraseña para el admin
 * 
 * INSTRUCCIONES:
 * 1. Coloca este archivo en C:\xampp\htdocs\CRUD_AUTOLAND\
 * 2. Abre http://localhost/CRUD_AUTOLAND/setup_admin.php
 * 3. Haz clic en "Configurar admin"
 * 4. ¡ELIMINA este archivo después de usarlo!
 */
require_once 'config/conexion.php';

$hecho   = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? 'admin');
    $password = trim($_POST['password'] ?? '');
    $nombre   = trim($_POST['nombre']   ?? 'Administrador');

    if (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        try {
            $db   = (new Conexion())->getConnection();
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Verificar si ya existe un admin
            $check = $db->prepare("SELECT COUNT(*) FROM usuario WHERE rol='admin'");
            $check->execute();

            if ($check->fetchColumn() > 0) {
                // Actualizar admin existente
                $stmt = $db->prepare("UPDATE usuario SET nombreReal=?, usuario=?, password=? WHERE rol='admin' LIMIT 1");
                $stmt->execute([$nombre, $usuario, $hash]);
            } else {
                // Crear admin nuevo
                $stmt = $db->prepare("INSERT INTO usuario (nombreReal, usuario, password, rol) VALUES (?, ?, ?, 'admin')");
                $stmt->execute([$nombre, $usuario, $hash]);
            }
            $hecho = true;
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AUTOLAND — Setup Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:#0a0a0a;color:#f5f0eb;min-height:100vh;display:flex;align-items:center;justify-content:center}
        .card{background:#1a1a1a;border:1px solid #2a2a2a;border-radius:14px;padding:40px;width:100%;max-width:420px}
        h1{font-size:22px;font-weight:700;margin-bottom:6px}
        .sub{font-size:13px;color:#666;margin-bottom:28px;line-height:1.6}
        .campo{margin-bottom:16px}
        label{display:block;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#555;font-weight:600;margin-bottom:6px}
        input{width:100%;background:#222;border:1px solid #333;border-radius:6px;padding:11px 14px;font-size:13px;color:#f5f0eb;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s}
        input:focus{border-color:#c0392b}
        button{width:100%;background:#c0392b;color:#fff;border:none;border-radius:6px;padding:13px;font-size:13px;font-weight:700;cursor:pointer;margin-top:8px;transition:background .2s}
        button:hover{background:#e74c3c}
        .ok{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#4ade80;padding:16px;border-radius:8px;font-size:13px;line-height:1.7;margin-bottom:20px}
        .err{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171;padding:14px;border-radius:8px;font-size:13px;margin-bottom:20px}
        .warn{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);color:#fbbf24;padding:14px;border-radius:8px;font-size:12px;margin-top:20px;line-height:1.6}
        a{color:#c0392b;text-decoration:none;font-weight:600}
    </style>
</head>
<body>
<div class="card">
    <h1>⚙️ Setup Admin</h1>
    <p class="sub">Configura las credenciales del administrador de AUTOLAND. Solo necesitas hacer esto una vez.</p>

    <?php if ($hecho): ?>
        <div class="ok">
            ✓ <strong>Listo.</strong> El administrador fue configurado correctamente.<br><br>
            Ahora puedes <a href="login.php">iniciar sesión</a> con las credenciales que definiste.<br><br>
            <strong>⚠ Elimina este archivo</strong> de tu servidor cuando termines.
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="err">✕ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$hecho): ?>
    <form method="POST">
        <div class="campo">
            <label>Nombre completo del admin</label>
            <input type="text" name="nombre" value="Administrador" required>
        </div>
        <div class="campo">
            <label>Usuario (para login)</label>
            <input type="text" name="usuario" value="admin" required>
        </div>
        <div class="campo">
            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
        </div>
        <button type="submit">Configurar admin →</button>
    </form>
    <?php endif; ?>

    <div class="warn">
        🔒 <strong>Seguridad:</strong> Este archivo permite crear/modificar el admin sin autenticación. 
        Elimínalo de tu servidor una vez que lo hayas usado.
    </div>
</div>
</body>
</html>
