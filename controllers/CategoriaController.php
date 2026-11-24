<?php

require_once __DIR__ . '/../models/CategoriaModel.php';

class CategoriaController
{
    private static function responder(int $codigo, array $payload): void
    {
        http_response_code($codigo);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private static function leerJson(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ['__json_invalido' => true];
        }

        return $data;
    }

    private static function validarId($id): ?int
    {
        if ($id === null || filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id <= 0) {
            return null;
        }
        return (int) $id;
    }

    public static function listar(PDO $pdo): void
    {
        $categorias = CategoriaModel::obtenerTodos($pdo);
        self::responder(200, [
            'ok' => true,
            'mensaje' => 'Categorías obtenidas correctamente.',
            'data' => $categorias,
        ]);
    }

    public static function mostrar(PDO $pdo, $idParam): void
    {
        $id = self::validarId($idParam);
        if ($id === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El ID de categoría debe ser un entero positivo.',
            ]);
            return;
        }

        $categoria = CategoriaModel::obtenerPorId($pdo, $id);
        if ($categoria === null) {
            self::responder(404, [
                'ok' => false,
                'mensaje' => 'Categoría no encontrada.',
            ]);
            return;
        }

        self::responder(200, [
            'ok' => true,
            'mensaje' => 'Categoría obtenida correctamente.',
            'data' => $categoria,
        ]);
    }

    public static function crear(PDO $pdo): void
    {
        $input = self::leerJson();
        if (isset($input['__json_invalido'])) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El cuerpo de la petición no es un JSON válido.',
            ]);
            return;
        }

        $nombre = trim((string) ($input['nombre'] ?? ''));
        $descripcion = trim((string) ($input['descripcion'] ?? ''));
        $imagen = trim((string) ($input['imagen_referencial'] ?? ''));

        if ($nombre === '') {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo nombre es obligatorio.',
            ]);
            return;
        }

        $idCreado = CategoriaModel::crear($pdo, [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'imagen_referencial' => $imagen,
        ]);

        $categoriaCreada = CategoriaModel::obtenerPorId($pdo, $idCreado);

        self::responder(201, [
            'ok' => true,
            'mensaje' => 'Categoría creada correctamente.',
            'data' => $categoriaCreada,
        ]);
    }

    public static function actualizar(PDO $pdo, $idParam): void
    {
        $id = self::validarId($idParam);
        if ($id === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El ID de categoría debe ser un entero positivo.',
            ]);
            return;
        }

        $existente = CategoriaModel::obtenerPorId($pdo, $id);
        if ($existente === null) {
            self::responder(404, [
                'ok' => false,
                'mensaje' => 'Categoría no encontrada.',
            ]);
            return;
        }

        $input = self::leerJson();
        if (isset($input['__json_invalido'])) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El cuerpo de la petición no es un JSON válido.',
            ]);
            return;
        }

        $nombre = array_key_exists('nombre', $input)
            ? trim((string) $input['nombre'])
            : $existente['nombre'];
        $descripcion = array_key_exists('descripcion', $input)
            ? trim((string) $input['descripcion'])
            : $existente['descripcion'];
        $imagen = array_key_exists('imagen_referencial', $input)
            ? trim((string) $input['imagen_referencial'])
            : $existente['imagen_referencial'];

        if ($nombre === '') {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo nombre no puede estar vacío.',
            ]);
            return;
        }

        CategoriaModel::actualizar($pdo, $id, [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'imagen_referencial' => $imagen,
        ]);

        $actualizada = CategoriaModel::obtenerPorId($pdo, $id);

        self::responder(200, [
            'ok' => true,
            'mensaje' => 'Categoría actualizada correctamente.',
            'data' => $actualizada,
        ]);
    }

    public static function eliminar(PDO $pdo, $idParam): void
    {
        $id = self::validarId($idParam);
        if ($id === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El ID de categoría debe ser un entero positivo.',
            ]);
            return;
        }

        $existente = CategoriaModel::obtenerPorId($pdo, $id);
        if ($existente === null) {
            self::responder(404, [
                'ok' => false,
                'mensaje' => 'Categoría no encontrada.',
            ]);
            return;
        }

        try {
            CategoriaModel::eliminar($pdo, $id);
            self::responder(200, [
                'ok' => true,
                'mensaje' => 'Categoría eliminada correctamente.',
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                self::responder(400, [
                    'ok' => false,
                    'mensaje' => 'No se puede eliminar la categoría porque tiene registros asociados.',
                ]);
                return;
            }
            throw $e;
        }
    }
}