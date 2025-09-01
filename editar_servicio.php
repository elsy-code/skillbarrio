<?php
session_start();
require_once "modelo/conexion.php";
require_once "helpers.php";

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";

// Obtener ID del servicio
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("❌ ID de servicio no válido.");
}
$servicio_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];

// Verificar que el servicio pertenezca al usuario logueado
$sql = "SELECT * FROM servicios WHERE id = ? AND usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $servicio_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("❌ No tienes permiso para editar este servicio.");
}

$servicio = $resultado->fetch_assoc();

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = e($_POST['titulo']);
    $descripcion = e($_POST['descripcion']);
    $categoria = e($_POST['categoria']);
    $telefono = e($_POST['telefono']);
    $ubicacion = e($_POST['ubicacion']);

    // Manejo de la nueva imagen
    $imagen = $servicio['imagen']; // Mantener la anterior si no suben nueva

    if (!empty($_FILES['imagen']['name'])) {
        $directorio = "uploads/";
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }
        $archivo = $directorio . time() . "_" . basename($_FILES['imagen']['name']);
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $archivo)) {
            // Eliminar la imagen anterior si existe
            if (!empty($servicio['imagen']) && file_exists($servicio['imagen'])) {
                unlink($servicio['imagen']);
            }
            $imagen = $archivo;
        }
    }

    $sql_update = "UPDATE servicios 
                   SET titulo = ?, descripcion = ?, categoria = ?, telefono = ?, ubicacion = ?, imagen = ?
                   WHERE id = ? AND usuario_id = ?";
    $stmt_update = $conexion->prepare($sql_update);
    if ($stmt_update) {
        $stmt_update->bind_param("ssssssii", $titulo, $descripcion, $categoria, $telefono, $ubicacion, $imagen, $servicio_id, $usuario_id);
        if ($stmt_update->execute()) {
            $mensaje = "✅ Servicio actualizado correctamente.";
            // Recargar datos actualizados
            $servicio['titulo'] = $titulo;
            $servicio['descripcion'] = $descripcion;
            $servicio['categoria'] = $categoria;
            $servicio['telefono'] = $telefono;
            $servicio['ubicacion'] = $ubicacion;
            $servicio['imagen'] = $imagen;
        } else {
            $mensaje = "❌ Error al actualizar: " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        $mensaje = "❌ Error en la preparación: " . $conexion->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Servicio - SkillBarrio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <?php include "menu.php"; ?>

        <h2 class="text-center mb-4">✏️ Editar Servicio</h2>

        <?php if (!empty($mensaje)) : ?>
            <div class="alert alert-info text-center">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="editar_servicio.php?id=<?= $servicio_id ?>" enctype="multipart/form-data" class="card p-4 shadow mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="titulo" class="form-label">Nombre del servicio</label>
                <input type="text" name="titulo" class="form-control" value="<?= e($servicio['titulo']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3" required><?= e($servicio['descripcion']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="categoria" class="form-label">Categoría</label>
                <select name="categoria" class="form-select" required>
                    <option value="Peluquería" <?= ($servicio['categoria'] == "Peluquería") ? "selected" : "" ?>>Peluquería</option>
                    <option value="Mecánica" <?= ($servicio['categoria'] == "Mecánica") ? "selected" : "" ?>>Mecánica</option>
                    <option value="Costura" <?= ($servicio['categoria'] == "Costura") ? "selected" : "" ?>>Costura</option>
                    <option value="Comida" <?= ($servicio['categoria'] == "Comida") ? "selected" : "" ?>>Comida</option>
                    <option value="Otros" <?= ($servicio['categoria'] == "Otros") ? "selected" : "" ?>>Otros</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono o WhatsApp</label>
                <input type="text" name="telefono" class="form-control" value="<?= e($servicio['telefono']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="ubicacion" class="form-label">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" value="<?= e($servicio['ubicacion']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="imagen" class="form-label">Imagen actual</label><br>
                <?php if (!empty($servicio['imagen'])): ?>
                    <img src="<?= e($servicio['imagen']) ?>" alt="Imagen actual" width="120" class="mb-2"><br>
                <?php else: ?>
                    <span class="text-muted">No hay imagen</span><br>
                <?php endif; ?>
                <input type="file" name="imagen" class="form-control mt-2" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
            <div class="mt-3 text-center">
                <a href="mis_servicios.php" class="btn btn-outline-secondary">Volver a Mis Servicios</a>
            </div>
        </form>
    </div>
</body>
</html>
