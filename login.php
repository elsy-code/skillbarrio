<?php
require_once "modelo/conexion.php";
require_once "helpers.php";

$mensaje = "";
ensure_csrf_token();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST['csrf_token'] ?? '';
    if (!check_csrf_token($token)) {
        $mensaje = "❌ Token inválido. Vuelve a intentarlo.";
    } else {
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $contrasena = $_POST['contrasena'] ?? '';

        if (!$correo || !$contrasena) {
            $mensaje = "❌ Completa correo y contraseña.";
        } else {
            $sql = "SELECT id, nombre, correo, contrasena, es_admin, activo FROM usuarios WHERE correo = ? LIMIT 1";
            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                $mensaje = "❌ Error en la consulta: " . $conexion->error;
            } else {
                $stmt->bind_param("s", $correo);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $res->num_rows === 1) {
                    $u = $res->fetch_assoc();
                    if ((int)$u['activo'] !== 1) {
                        $mensaje = "❌ Tu cuenta está desactivada. Contacta al administrador.";
                    } elseif (password_verify($contrasena, $u['contrasena'])) {
                        session_regenerate_id(true);
                        $_SESSION['usuario_id'] = $u['id'];
                        $_SESSION['usuario_nombre'] = $u['nombre'];
                        $_SESSION['es_admin'] = (int)$u['es_admin'];
                        header("Location: index.php");
                        exit();
                    } else {
                        $mensaje = "❌ Contraseña incorrecta.";
                    }
                } else {
                    $mensaje = "❌ No existe una cuenta con ese correo.";
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
<title>Iniciar sesión</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
<?php include "menu.php"; ?>
<div class="container mt-4">
    <h2 class="text-center">Iniciar sesión</h2>
    <?php if($mensaje): ?><div class="alert alert-danger text-center"><?= e($mensaje) ?></div><?php endif; ?>

    <form method="POST" class="card p-4 shadow mx-auto" style="max-width:480px;">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="contrasena" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Entrar</button>
    </form>
</div>
</body>
</html>
