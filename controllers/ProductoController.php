<?php

require_once __DIR__ . '/../models/ProductoModel.php';

class ProductoController
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

    private static function validarEnteroPositivo($valor): ?int
    {
        if ($valor === null || $valor === '' || filter_var($valor, FILTER_VALIDATE_INT) === false) {
            return null;
        }
        $entero = (int) $valor;
        return $entero > 0 ? $entero : null;
    }

    private static function validarPrecio($precio): ?string
    {
        if ($precio === null || $precio === '' || !is_numeric($precio)) {
            return null;
        }
        $num = (float) $precio;
        if ($num < 0 || !is_finite($num)) {
            return null;
        }
        return number_format($num, 2, '.', '');
    }

    private static function normalizarEstado($estado, bool $defecto = true): ?bool
    {
        if ($estado === null) {
            return $defecto;
        }
        if (is_bool($estado)) {
            return $estado;
        }
        if ($estado === 1 || $estado === 0 || $estado === '1' || $estado === '0') {
            return (int) $estado === 1;
        }
        $filtrar = filter_var($estado, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $filtrar;
    }

    private static function validarFecha(?string $fecha): ?string
    {
        if ($fecha === null || trim($fecha) === '') {
            return null;
        }
        $fecha = trim($fecha);
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$dt || $dt->format('Y-m-d') !== $fecha) {
            return false; // señal de fecha inválida
        }
        return $fecha;
    }

    public static function listar(PDO $pdo): void
    {
        $idCategoria = null;
        if (isset($_GET['categoria']) && $_GET['categoria'] !== '') {
            $idCategoria = self::validarEnteroPositivo($_GET['categoria']);
            if ($idCategoria === null) {
                self::responder(400, [
                    'ok' => false,
                    'mensaje' => 'El parámetro categoria debe ser un entero positivo.',
                ]);
                return;
            }
        }

        $productos = ProductoModel::obtenerTodos($pdo, $idCategoria);
        self::responder(200, [
            'ok' => true,
            'mensaje' => 'Productos obtenidos correctamente.',
            'data' => $productos,
        ]);
    }

    public static function mostrar(PDO $pdo, $idParam): void
    {
        $id = self::validarEnteroPositivo($idParam);
        if ($id === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El ID de producto debe ser un entero positivo.',
            ]);
            return;
        }

        $producto = ProductoModel::obtenerPorId($pdo, $id);
        if ($producto === null) {
            self::responder(404, [
                'ok' => false,
                'mensaje' => 'Producto no encontrado.',
            ]);
            return;
        }

        self::responder(200, [
            'ok' => true,
            'mensaje' => 'Producto obtenido correctamente.',
            'data' => $producto,
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

        $nombre = isset($input['nombre']) ? trim((string) $input['nombre']) : '';
        $descripcion = isset($input['descripcion']) ? trim((string) $input['descripcion']) : null;
        $imagen = isset($input['imagen_prod']) ? trim((string) $input['imagen_prod']) : null;
        $precio = self::validarPrecio($input['precio'] ?? null);
        $idCategoria = self::validarEnteroPositivo($input['id_categoria'] ?? null);
        $estado = self::normalizarEstado($input['estado'] ?? null, true);
        $fechaIngreso = self::validarFecha(isset($input['fecha_ingreso']) ? (string) $input['fecha_ingreso'] : null);

        if ($nombre === '') {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo nombre es obligatorio.',
            ]);
            return;
        }

        if ($precio === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo precio es obligatorio y debe ser un número válido mayor o igual a 0.',
            ]);
            return;
        }

        if ($idCategoria === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo id_categoria es obligatorio y debe ser un entero positivo.',
            ]);
            return;
        }

        if ($estado === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo estado debe ser booleano (true/false o 1/0).',
            ]);
            return;
        }

        if ($fechaIngreso === false) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo fecha_ingreso debe tener formato YYYY-MM-DD.',
            ]);
            return;
        }

        if (!ProductoModel::existeCategoria($pdo, $idCategoria)) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'La categoría indicada en id_categoria no existe.',
            ]);
            return;
        }

        $idCreado = ProductoModel::crear($pdo, [
            'nombre' => $nombre,
            'descripcion' => $descripcion === '' ? null : $descripcion,
            'precio' => $precio,
            'id_categoria' => $idCategoria,
            'estado' => $estado,
            'imagen_prod' => $imagen === '' ? null : $imagen,
            'fecha_ingreso' => $fechaIngreso,
        ]);

        $creado = ProductoModel::obtenerPorId($pdo, $idCreado);

        self::responder(201, [
            'ok' => true,
            'mensaje' => 'Producto creado correctamente.',
            'data' => $creado,
        ]);
    }

    public static function actualizar(PDO $pdo, $idParam): void
    {
        $id = self::validarEnteroPositivo($idParam);
        if ($id === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El ID de producto debe ser un entero positivo.',
            ]);
            return;
        }

        $existente = ProductoModel::obtenerPorId($pdo, $id);
        if ($existente === null) {
            self::responder(404, [
                'ok' => false,
                'mensaje' => 'Producto no encontrado.',
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
            : (string) $existente['nombre'];

        if ($nombre === '') {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo nombre no puede estar vacío.',
            ]);
            return;
        }

        $precio = array_key_exists('precio', $input)
            ? self::validarPrecio($input['precio'])
            : self::validarPrecio($existente['precio']);

        if ($precio === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo precio debe ser un número válido mayor o igual a 0.',
            ]);
            return;
        }

        $idCategoria = array_key_exists('id_categoria', $input)
            ? self::validarEnteroPositivo($input['id_categoria'])
            : (int) $existente['id_categoria'];

        if ($idCategoria === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo id_categoria debe ser un entero positivo.',
            ]);
            return;
        }

        if (!ProductoModel::existeCategoria($pdo, $idCategoria)) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'La categoría indicada en id_categoria no existe.',
            ]);
            return;
        }

        $estado = array_key_exists('estado', $input)
            ? self::normalizarEstado($input['estado'], true)
            : (bool) $existente['estado'];

        if ($estado === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo estado debe ser booleano (true/false o 1/0).',
            ]);
            return;
        }

        $fechaIngreso = array_key_exists('fecha_ingreso', $input)
            ? self::validarFecha($input['fecha_ingreso'] !== null ? (string) $input['fecha_ingreso'] : null)
            : $existente['fecha_ingreso'];

        if ($fechaIngreso === false) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El campo fecha_ingreso debe tener formato YYYY-MM-DD.',
            ]);
            return;
        }

        if ($fechaIngreso === null) {
            $fechaIngreso = $existente['fecha_ingreso'];
        }

        $descripcion = array_key_exists('descripcion', $input)
            ? (trim((string) $input['descripcion']) === '' ? null : trim((string) $input['descripcion']))
            : $existente['descripcion'];

        $imagen = array_key_exists('imagen_prod', $input)
            ? (trim((string) $input['imagen_prod']) === '' ? null : trim((string) $input['imagen_prod']))
            : $existente['imagen_prod'];

        ProductoModel::actualizar($pdo, $id, [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'id_categoria' => $idCategoria,
            'estado' => $estado,
            'imagen_prod' => $imagen,
            'fecha_ingreso' => $fechaIngreso,
        ]);

        $actualizado = ProductoModel::obtenerPorId($pdo, $id);

        self::responder(200, [
            'ok' => true,
            'mensaje' => 'Producto actualizado correctamente.',
            'data' => $actualizado,
        ]);
    }

    public static function eliminar(PDO $pdo, $idParam): void
    {
        $id = self::validarEnteroPositivo($idParam);
        if ($id === null) {
            self::responder(400, [
                'ok' => false,
                'mensaje' => 'El ID de producto debe ser un entero positivo.',
            ]);
            return;
        }

        $existente = ProductoModel::obtenerPorId($pdo, $id);
        if ($existente === null) {
            self::responder(404, [
                'ok' => false,
                'mensaje' => 'Producto no encontrado.',
            ]);
            return;
        }

        try {
            ProductoModel::eliminar($pdo, $id);
            self::responder(200, [
                'ok' => true,
                'mensaje' => 'Producto eliminado correctamente.',
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                self::responder(400, [
                    'ok' => false,
                    'mensaje' => 'No se puede eliminar el producto porque tiene registros asociados.',
                ]);
                return;
            }
            throw $e;
        }
    }
}