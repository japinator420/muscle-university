<?php

require_once __DIR__ . '/../controllers/CategoriaController.php';

$id = $id ?? (isset($_GET['id']) && $_GET['id'] !== '' ? $_GET['id'] : null);
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($metodo) {
    case 'GET':
        if ($id !== null) {
            CategoriaController::mostrar($pdo, $id);
        } else {
            CategoriaController::listar($pdo);
        }
        break;

    case 'POST':
        CategoriaController::crear($pdo);
        break;

    case 'PUT':
    case 'PATCH':
        CategoriaController::actualizar($pdo, $id);
        break;

    case 'DELETE':
        CategoriaController::eliminar($pdo, $id);
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'ok' => false,
            'mensaje' => 'Método HTTP no permitido para categorías.',
        ], JSON_UNESCAPED_UNICODE);
        break;
}