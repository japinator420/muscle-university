<?php

class OrdenController
{
    private OrdenModel $modelo;

    public function __construct(OrdenModel $modelo)
    {
        $this->modelo = $modelo;
    }

    public function listar(?int $idCliente = null): void
    {
        $ordenes = $this->modelo->obtenerTodos($idCliente);
        $this->responderJson(200, [
            'ok' => true,
            'data' => $ordenes,
        ]);
    }

    public function mostrar(int $id): void
    {
        if ($id <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'ID de orden inválido.',
            ]);
            return;
        }

        $orden = $this->modelo->obtenerPorId($id);
        if (!$orden) {
            $this->responderJson(404, [
                'ok' => false,
                'mensaje' => 'Orden no encontrada.',
            ]);
            return;
        }

        $this->responderJson(200, [
            'ok' => true,
            'data' => $orden,
        ]);
    }

    public function crear(array $entrada): void
    {
        $idClienteRaw = $entrada['id_cliente'] ?? null;
        if (!is_numeric($idClienteRaw) || (int) $idClienteRaw <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'id_cliente es obligatorio y debe ser un entero positivo.',
            ]);
            return;
        }
        $idCliente = (int) $idClienteRaw;

        if (!$this->modelo->usuarioExiste($idCliente)) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'El id_cliente indicado no existe en usuarios.',
            ]);
            return;
        }

        $fechaCompra = date('Y-m-d');
        if (array_key_exists('fecha_compra', $entrada) && $entrada['fecha_compra'] !== null && $entrada['fecha_compra'] !== '') {
            $fecha = trim((string) $entrada['fecha_compra']);
            if (!$this->esFechaValida($fecha)) {
                $this->responderJson(400, [
                    'ok' => false,
                    'mensaje' => 'fecha_compra debe tener formato válido YYYY-MM-DD.',
                ]);
                return;
            }
            $fechaCompra = $fecha;
        }

        $itemsRaw = $entrada['items'] ?? [];
        $itemsResueltos = $this->resolverItems($itemsRaw);
        if ($itemsResueltos === null) {
            return;
        }

        // Si envían total explícitamente como null, se respeta (útil para carrito inicial)
        $totalEsNull = array_key_exists('total', $entrada) && $entrada['total'] === null;

        try {
            $nuevoId = $this->modelo->crearConDetalles([
                'id_cliente' => $idCliente,
                'fecha_compra' => $fechaCompra,
            ], $itemsResueltos, $totalEsNull);

            $creada = $this->modelo->obtenerPorId($nuevoId);

            $this->responderJson(201, [
                'ok' => true,
                'mensaje' => 'Orden creada correctamente.',
                'data' => $creada,
            ]);
        } catch (PDOException $e) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'No se pudo crear la orden.',
            ]);
        }
    }

    public function agregarDetalle(int $idOrden, array $entrada): void
    {
        if ($idOrden <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'ID de orden inválido.',
            ]);
            return;
        }

        $orden = $this->modelo->obtenerPorId($idOrden);
        if (!$orden) {
            $this->responderJson(404, [
                'ok' => false,
                'mensaje' => 'Orden no encontrada.',
            ]);
            return;
        }

        // Acepta un solo ítem en raíz ("id_producto" + "cantidad") o un arreglo en "items"
        $itemsRaw = isset($entrada['items']) ? $entrada['items'] : [$entrada];
        $itemsResueltos = $this->resolverItems($itemsRaw);
        if ($itemsResueltos === null) {
            return;
        }

        if (empty($itemsResueltos)) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'Debes enviar id_producto y cantidad (o un arreglo items no vacío).',
            ]);
            return;
        }

        try {
            $idsInsertados = [];
            foreach ($itemsResueltos as $item) {
                $idsInsertados[] = $this->modelo->agregarDetalle($idOrden, $item);
            }

            $actualizada = $this->modelo->obtenerPorId($idOrden);
            $this->responderJson(200, [
                'ok' => true,
                'mensaje' => 'Detalle(s) agregado(s) y total actualizado correctamente.',
                'ids_detalle' => $idsInsertados,
                'data' => $actualizada,
            ]);
        } catch (Throwable $e) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'No se pudo agregar el detalle a la orden.',
            ]);
        }
    }

    public function actualizar(int $id, array $entrada): void
    {
        if ($id <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'ID de orden inválido.',
            ]);
            return;
        }

        $actual = $this->modelo->obtenerPorId($id);
        if (!$actual) {
            $this->responderJson(404, [
                'ok' => false,
                'mensaje' => 'Orden no encontrada.',
            ]);
            return;
        }

        // Si el PATCH trae "items" o "id_producto", agrega detalles y recalcula el total
        if (array_key_exists('items', $entrada) || array_key_exists('id_producto', $entrada)) {
            $this->agregarDetalle($id, $entrada);
            return;
        }

        $idCliente = array_key_exists('id_cliente', $entrada) ? (int) $entrada['id_cliente'] : (int) $actual['id_cliente'];
        if ($idCliente <= 0 || !$this->modelo->usuarioExiste($idCliente)) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'id_cliente inválido o inexistente.',
            ]);
            return;
        }

        $fechaCompra = $actual['fecha_compra'];
        if (array_key_exists('fecha_compra', $entrada) && $entrada['fecha_compra'] !== null && $entrada['fecha_compra'] !== '') {
            $fecha = trim((string) $entrada['fecha_compra']);
            if (!$this->esFechaValida($fecha)) {
                $this->responderJson(400, [
                    'ok' => false,
                    'mensaje' => 'fecha_compra debe tener formato válido YYYY-MM-DD.',
                ]);
                return;
            }
            $fechaCompra = $fecha;
        }

        $total = $actual['total'];
        if (array_key_exists('total', $entrada)) {
            if ($entrada['total'] === null || $entrada['total'] === '') {
                $total = null;
            } elseif (!is_numeric($entrada['total']) || (float) $entrada['total'] < 0) {
                $this->responderJson(400, [
                    'ok' => false,
                    'mensaje' => 'total debe ser null o un número mayor o igual a 0.',
                ]);
                return;
            } else {
                $total = round((float) $entrada['total'], 2);
            }
        }

        try {
            $this->modelo->actualizar($id, [
                'id_cliente' => $idCliente,
                'fecha_compra' => $fechaCompra,
                'total' => $total,
            ]);

            $actualizada = $this->modelo->obtenerPorId($id);
            $this->responderJson(200, [
                'ok' => true,
                'mensaje' => 'Orden actualizada correctamente.',
                'data' => $actualizada,
            ]);
        } catch (PDOException $e) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'No se pudo actualizar la orden.',
            ]);
        }
    }

    public function eliminar(int $id): void
    {
        if ($id <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'ID de orden inválido.',
            ]);
            return;
        }

        $orden = $this->modelo->obtenerPorId($id);
        if (!$orden) {
            $this->responderJson(404, [
                'ok' => false,
                'mensaje' => 'Orden no encontrada.',
            ]);
            return;
        }

        try {
            $this->modelo->eliminar($id);
            $this->responderJson(200, [
                'ok' => true,
                'mensaje' => 'Orden eliminada correctamente.',
            ]);
        } catch (PDOException $e) {
            $this->responderJson(409, [
                'ok' => false,
                'mensaje' => 'No se pudo eliminar la orden.',
            ]);
        }
    }

    /**
     * Valida y resuelve precios/subtotales desde la base de datos para uno o varios ítems.
     * Devuelve null si respondió con error HTTP 400.
     */
    private function resolverItems(mixed $itemsRaw): ?array
    {
        if (!is_array($itemsRaw)) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'items debe ser un arreglo.',
            ]);
            return null;
        }

        $itemsResueltos = [];
        foreach ($itemsRaw as $indice => $item) {
            if (!is_array($item)) {
                $this->responderJson(400, [
                    'ok' => false,
                    'mensaje' => "El elemento en items[$indice] no es válido.",
                ]);
                return null;
            }

            $idProdRaw = $item['id_producto'] ?? null;
            $cantRaw = $item['cantidad'] ?? null;

            if (!is_numeric($idProdRaw) || (int) $idProdRaw <= 0) {
                $this->responderJson(400, [
                    'ok' => false,
                    'mensaje' => "id_producto inválido en items[$indice].",
                ]);
                return null;
            }
            if (!is_numeric($cantRaw) || (int) $cantRaw <= 0 || (int) $cantRaw != $cantRaw) {
                $this->responderJson(400, [
                    'ok' => false,
                    'mensaje' => "cantidad debe ser un entero mayor que 0 en items[$indice].",
                ]);
                return null;
            }

            $idProducto = (int) $idProdRaw;
            $cantidad = (int) $cantRaw;

            $producto = $this->modelo->obtenerProductoActivo($idProducto);
            if (!$producto) {
                $this->responderJson(400, [
                    'ok' => false,
                    'mensaje' => "El producto con id_producto $idProducto no existe.",
                ]);
                return null;
            }

            $precioUnitario = round((float) $producto['precio'], 2);
            $subTotal = round($precioUnitario * $cantidad, 2);

            $itemsResueltos[] = [
                'id_producto' => $idProducto,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'sub_total' => $subTotal,
            ];
        }

        return $itemsResueltos;
    }

    private function esFechaValida(string $fecha): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return false;
        }
        [$anio, $mes, $dia] = array_map('intval', explode('-', $fecha));
        return checkdate($mes, $dia, $anio);
    }

    private function responderJson(int $codigoHttp, array $payload): void
    {
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}