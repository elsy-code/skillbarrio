<?php
require_once "modelo/conexion.php";
require_once "helpers.php";
require_once "config_global.php";

if (session_status() === PHP_SESSION_NONE) session_start();

// Solo administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['es_admin'] != 1) {
    die("❌ No tienes permiso para acceder.");
}

ensure_csrf_token();
$mensaje = "";

if (!isset($_GET['id'])) {
    header("Location: admin_panel.php");
    exit();
}

$id = intval($_GET['id']);

// Buscar servicio por id
$sql = "SELECT * FROM servicios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$servicio = $res->fetch_assoc();
$stmt->close();

if (!$servicio) die("❌ Servicio no encontrado.");

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
        $mensaje = "❌ Token inválido.";
    } else {
        $titulo = trim(filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS));
        $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS));
        $categoria = trim(filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS));
        $telefono = trim(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_SPECIAL_CHARS));
        $ubicacion = trim(filter_input(INPUT_POST, 'ubicacion', FILTER_SANITIZE_SPECIAL_CHARS));

        if (!$titulo || !$descripcion || !$categoria) {
            $mensaje = "❌ Completa todos los campos obligatorios.";
        } else {
            $imgCheck = validar_subida_imagen($_FILES['imagen'] ?? null);
            if (!$imgCheck['ok']) {
                $mensaje = "❌ " . $imgCheck['msg'];
            } else {
                $ruta = $imgCheck['path'] ?? $servicio['imagen'];
                $sqlUp = "UPDATE servicios 
                          SET titulo=?, descripcion=?, categoria=?, telefono=?, ubicacion=?, imagen=? 
                          WHERE id=?";
                $st = $conexion->prepare($sqlUp);
                if ($st) {
                    $st->bind_param("ssssssi", $titulo, $descripcion, $categoria, $telefono, $ubicacion, $ruta, $id);
                    if ($st->execute()) {
                        $mensaje = "✅ Servicio actualizado.";
                        $servicio['titulo'] = $titulo;
                        $servicio['descripcion'] = $descripcion;
                        $servicio['categoria'] = $categoria;
                        $servicio['telefono'] = $telefono;
                        $servicio['ubicacion'] = $ubicacion;
                        $servicio['imagen'] = $ruta;
                    } else {
                        $mensaje = "❌ Error al actualizar.";
                    }
                    $st->close();
                } else {
                    $mensaje = "❌ Error en la consulta: " . $conexion->error;
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
<title>Editar servicio (Admin)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
<?php include "menu.php"; ?>
<div class="container mt-3">
  <h2 class="text-center">Editar Servicio (Administrador)</h2>
  <?php if($mensaje): ?><div class="alert alert-info text-center"><?= e($mensaje) ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="card p-4 shadow mx-auto" style="max-width:800px;">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Título</label>
        <input name="titulo" class="form-control" value="<?= e($servicio['titulo']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Categoría</label>
        <select name="categoria" class="form-select" required>
          <?php $cats = ["Peluquería","Mecánica","Costura","Comida","Otros"]; ?>
          <?php foreach($cats as $c): ?>
            <option value="<?= e($c) ?>" <?= ($servicio['categoria']==$c)?"selected":"" ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3" required><?= e($servicio['descripcion']) ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Teléfono</label>
        <input name="telefono" class="form-control" value="<?= e($servicio['telefono']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Ubicación</label>
        <input name="ubicacion" class="form-control" value="<?= e($servicio['ubicacion']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Imagen</label><br>
        <?php if(!empty($servicio['imagen'])): ?>
            <img src="<?= e($servicio['imagen']) ?>" width="120"><br>
        <?php endif; ?>
        <input type="file" name="imagen" class="form-control" accept="image/*">
      </div>
    </div>
    <button class="btn btn-primary w-100 mt-3">Guardar cambios</button>
  </form>
</div>
</body>
</html>
