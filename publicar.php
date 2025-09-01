<?php
require_once "modelo/conexion.php";
require_once "helpers.php";
require_once "config_global.php";

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }
if (!empty($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) { die("❌ Los administradores no pueden publicar servicios."); }

$mensaje = "";
ensure_csrf_token();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
        $mensaje = "❌ Token inválido.";
    } else {
        
        $titulo = trim(filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS));
        $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS));
        $categoria = trim(filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS));
        $telefono = trim(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_SPECIAL_CHARS));
        $ubicacion = trim(filter_input(INPUT_POST, 'ubicacion', FILTER_SANITIZE_SPECIAL_CHARS));
        $latitud = trim($_POST['latitud'] ?? '');
        $longitud = trim($_POST['longitud'] ?? '');
        $usuario_id = (int)$_SESSION['usuario_id'];

        if (!$titulo || !$descripcion || !$categoria || !$telefono || !$ubicacion) {
            $mensaje = "❌ Completa todos los campos obligatorios.";
        } else {
            
            $imgCheck = validar_subida_imagen($_FILES['imagen'] ?? null);
            if (!$imgCheck['ok']) {
                $mensaje = "❌ " . $imgCheck['msg'];
            } else {
                $ruta_imagen = $imgCheck['path']; 

                
                $sql = "INSERT INTO servicios (usuario_id, titulo, descripcion, categoria, telefono, ubicacion, latitud, longitud, imagen)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conexion->prepare($sql);
                if (!$stmt) {
                    $mensaje = "❌ Error en la consulta: " . $conexion->error;
                } else {
                    $lat_bind = $latitud === '' ? null : $latitud;
                    $lon_bind = $longitud === '' ? null : $longitud;

                    $stmt->bind_param("issssssss", $usuario_id, $titulo, $descripcion, $categoria, $telefono, $ubicacion, $lat_bind, $lon_bind, $ruta_imagen);
                    if ($stmt->execute()) {
                        $mensaje = "✅ Servicio publicado correctamente.";
                    } else {
                        $mensaje = "❌ Error al publicar: " . $conexion->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Publicar servicio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
<?php include "menu.php"; ?>
<div class="container mt-4">
    <h2 class="text-center">Publicar un servicio</h2>
    <?php if($mensaje): ?><div class="alert alert-info text-center"><?= e($mensaje) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow mx-auto" style="max-width:800px;">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nombre del servicio</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-select" required>
                    <option value="">Seleccionar...</option>
                    <option value="Peluquería">Peluquería</option>
                    <option value="Mecánica">Mecánica</option>
                    <option value="Costura">Costura</option>
                    <option value="Comida">Comida</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3" required></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Teléfono o WhatsApp</label>
                <input type="text" name="telefono" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Ubicación (Barrio, Calle)</label>
                <input type="text" name="ubicacion" class="form-control" required>
            </div>    
            <div class="col-12">
                <label class="form-label">Imagen (jpg/png/webp, máx 3MB)</label>
                <input type="file" name="imagen" class="form-control" accept="image/*">
            </div>
        </div>

        <button class="btn btn-primary w-100 mt-3">Publicar Servicio</button>
    </form>
</div>
</body>
</html>
