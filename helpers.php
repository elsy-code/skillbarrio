<?php

if (session_status() === PHP_SESSION_NONE) session_start();

function ensure_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}


function validar_subida_imagen($file) {
    if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'msg' => 'Error subiendo el archivo.'];
    }

    if ($file['size'] > 3 * 1024 * 1024) {
        return ['ok' => false, 'msg' => 'La imagen es demasiado grande (máx 3MB).'];
    }

    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowed)) {
        return ['ok' => false, 'msg' => 'Tipo de imagen no permitido. Usa jpg, png o webp.'];
    }


    $dir = __DIR__ . '/uploads/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $ext = '';
    switch ($mime) {
        case 'image/jpeg': $ext = '.jpg'; break;
        case 'image/png':  $ext = '.png'; break; 
        case 'image/webp': $ext = '.webp'; break;
    }
    $nombre = time() . '_' . bin2hex(random_bytes(6)) . $ext;
    $ruta_rel = 'uploads/' . $nombre;
    $ruta_abs = $dir . $nombre;

    if (!move_uploaded_file($file['tmp_name'], $ruta_abs)) {
        return ['ok' => false, 'msg' => 'No se pudo guardar la imagen en el servidor.'];
    }

    return ['ok' => true, 'path' => $ruta_rel];
}
