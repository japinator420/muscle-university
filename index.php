<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/database.php';

$recurso = $_GET['recurso'] ?? '';
$id = isset($_GET['id']) && $_GET['id'] !== '' ? $_GET['id'] : null;

if (!empty($_SERVER['PATH_INFO'])) {
    $partes = explode('/', trim($_SERVER['PATH_INFO'], '/'));
    if ($recurso === '' && !empty($partes[0])) {
        $recurso = $partes[0];
    }
    if ($id === null && isset($partes[1]) && $partes[1] !== '') {
        $id = $partes[1];
    }
}

$recurso = strtolower(trim($recurso));

try {
    $pdo = obtenerConexion();

    switch ($recurso) {
        case 'categorias':
            require_once __DIR__ . '/routes/categorias.php';
            break;

        case 'productos':
            require_once __DIR__ . '/routes/productos.php';
            break;
        
        case 'usuarios':
            require __DIR__ . '/routes/usuarios.php';
            break;
        
        case 'suscripciones':
            require __DIR__ . '/routes/suscripciones.php';
            break;
        
        case 'ordenes':
            require __DIR__ . '/routes/ordenes.php';
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Recurso no encontrado.'
            ]);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error interno de base de datos.'
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error interno del servidor.'
    ]);
}