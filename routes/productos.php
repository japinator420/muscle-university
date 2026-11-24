<?php

require_once __DIR__ . '/../controllers/ProductoController.php';
$pdo = obtenerConexion();

$id = $id ?? (isset($_GET['id']) && $_GET['id'] !== '' ? $_GET['id'] : null);
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($metodo) {
    case 'GET':
        if ($id !== null) {
            ProductoController::mostrar($pdo, $id);
        } else {
            ProductoController::listar($pdo);
        }
        break;

    case 'POST':
        ProductoController::crear($pdo);
        break;

    case 'PUT':
    case 'PATCH':
        ProductoController::actualizar($pdo, $id);
        break;

    case 'DELETE':
        ProductoController::eliminar($pdo, $id);
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'ok' => false,
            'mensaje' => 'Método HTTP no permitido para productos.',
        ], JSON_UNESCAPED_UNICODE);
        break;
}