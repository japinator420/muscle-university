<?php

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';
$pdo = obtenerConexion();

$modelo = new UsuarioModel($pdo);
$controlador = new UsuarioController($modelo);

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$rol = isset($_GET['rol']) ? (string) $_GET['rol'] : null;

$cuerpoRaw = file_get_contents('php://input');
$cuerpo = [];
if ($cuerpoRaw !== false && trim($cuerpoRaw) !== '') {
    $decodificado = json_decode($cuerpoRaw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodificado)) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'mensaje' => 'JSON mal formado en el cuerpo de la petición.',
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    $cuerpo = $decodificado;
}

switch ($metodo) {
    case 'GET':
        if ($id !== null) {
            $controlador->mostrar($id);
        } else {
            $controlador->listar($rol);
        }
        break;

    case 'POST':
        $controlador->crear($cuerpo);
        break;

    case 'PUT':
    case 'PATCH':
        if ($id === null) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Debe indicar el parámetro id para actualizar.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        $controlador->actualizar($id, $cuerpo);
        break;

    case 'DELETE':
        if ($id === null) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Debe indicar el parámetro id para eliminar.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        $controlador->eliminar($id);
        break;

    default:
        http_response_code(405);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'mensaje' => 'Método HTTP no permitido.',
        ], JSON_UNESCAPED_UNICODE);
        break;
}