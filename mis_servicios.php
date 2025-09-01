<?php
session_start();
require_once "modelo/conexion.php";
require_once "helpers.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
if (isset($_GET['eliminar'])) {
    $servicio_id = intval($_GET['eliminar']);

    $sql_check = "SELECT imagen FROM servicios WHERE id = ? AND usuario_id = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("ii", $servicio_id, $usuario_id);
    $stmt_check->execute();
    $res = $stmt_check->get_result();

    if ($res->num_rows > 0) {
        $servicio = $res->fetch_assoc();
        if (!empty($servicio['imagen']) && file_exists($servicio['imagen'])) {
            unlink($servicio['imagen']);
        }
        $sql_del = "DELETE FROM servicios WHERE id = ? AND usuario_id = ?";
        $stmt_del = $conexion->prepare($sql_del);
        $stmt_del->bind_param("ii", $servicio_id, $usuario_id);
        $stmt_del->execute();
    }
    header("Location: mis_servicios.php");
    exit();
}

$sql = "SELECT * FROM servicios WHERE usuario_id = ? ORDER BY id DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$servicios = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Servicios - SkillBarrio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img-top {
            width: 100%;
            height: 220px; 
            object-fit: contain;
            border-top-left-radius: .5rem;
            border-top-right-radius: .5rem;
        }
        .card {
            border: none;
            border-radius: .8rem;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        .card-title {
            font-size: 1.0rem;
            font-weight: 600;
        }
        .card-text {
            font-size: 0.8rem;
            color: #555;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <?php include "menu.php"; ?>

        <h2 class="text-center mb-4 fw-bold">📋 Mis Servicios</h2>

        <div class="d-flex justify-content-end mb-4">
            <a href="publicar.php" class="btn btn-success">➕ Publicar nuevo servicio</a>
        </div>

        <div class="row g-4">
            <?php if (count($servicios) > 0): ?>
                <?php foreach ($servicios as $s): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <?php if (!empty($s['imagen'])): ?>
                                <img src="<?= e($s['imagen']) ?>" class="card-img-top" alt="Imagen">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x200?text=Sin+Imagen" class="card-img-top" alt="Sin imagen">
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title"><?= e($s['titulo']) ?></h5>
                                <p class="card-text mb-1"><strong>Categoría:</strong> <?= e($s['categoria']) ?></p>
                                <p class="card-text"><?= e($s['descripcion']) ?></p>
                                <p class="card-text"><strong>Ubicación:</strong> <?= e($s['ubicacion']) ?></p>
                                <p class="card-text"><strong>Teléfono:</strong> <?= e($s['telefono']) ?></p>
                            </div>
                            <div class="card-footer text-center bg-white border-0">
                                <a href="editar_servicio.php?id=<?= $s['id'] ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
                                <a href="mis_servicios.php?eliminar=<?= $s['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este servicio?')">🗑 Eliminar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">❌ Aún no has publicado ningún servicio.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
