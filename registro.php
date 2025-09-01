<?php
require_once "modelo/conexion.php";
require_once "helpers.php";

$mensaje = "";
ensure_csrf_token();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
        $mensaje = "❌ Token inválido.";
    } else {
        $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS));
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $contrasena_raw = $_POST['contrasena'] ?? '';

        if (!$nombre || !$correo || !$contrasena_raw) {
            $mensaje = "❌ Completa todos los campos.";
        } elseif (strlen($contrasena_raw) < 8 || !preg_match('/[0-9]/', $contrasena_raw)) {
            $mensaje = "❌ La contraseña debe tener al menos 8 caracteres y al menos un número.";
        } else {
            $hash = password_hash($contrasena_raw, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nombre, correo, contrasena, es_admin, activo) VALUES (?, ?, ?, 0, 1)";
            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                $mensaje = "❌ Error: " . $conexion->error;
            } else {
                $stmt->bind_param("sss", $nombre, $correo, $hash);
                if ($stmt->execute()) {
                    $mensaje = "✅ Cuenta creada. Ya puedes iniciar sesión.";
                } else {
                
                    $mensaje = "❌ Error al registrar: " . $conexion->error;
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Registro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
<?php include "menu.php"; ?>
<div class="container mt-4">
    <h2 class="text-center">Crear cuenta</h2>
    <?php if($mensaje): ?><div class="alert alert-info text-center"><?= e($mensaje) ?></div><?php endif; ?>
    <form method="POST" class="card p-4 shadow mx-auto" style="max-width:520px;">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña (mín 8 caracteres y 1 número)</label>
            <input type="password" name="contrasena" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Registrarme</button>
    </form>
</div>
</body>
</html>
