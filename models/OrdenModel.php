<?php

class OrdenModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos(?int $idCliente = null): array
    {
        $sql = 'SELECT
                    o.id_orden,
                    o.id_cliente,
                    u.nombre AS nombre_cliente,
                    u.correo AS correo_cliente,
                    o.fecha_compra,
                    o.total
                FROM ordenes o
                INNER JOIN usuarios u ON o.id_cliente = u.id_usuario';

        $parametros = [];
        if ($idCliente !== null && $idCliente > 0) {
            $sql .= ' WHERE o.id_cliente = :id_cliente';
            $parametros[':id_cliente'] = $idCliente;
        }

        $sql .= ' ORDER BY o.id_orden ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ordenes as &$orden) {
            $orden['detalles'] = $this->obtenerDetallesPorOrden((int) $orden['id_orden']);
        }
        unset($orden);

        return $ordenes;
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT
                    o.id_orden,
                    o.id_cliente,
                    u.nombre AS nombre_cliente,
                    u.correo AS correo_cliente,
                    o.fecha_compra,
                    o.total
                FROM ordenes o
                INNER JOIN usuarios u ON o.id_cliente = u.id_usuario
                WHERE o.id_orden = :id
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $orden = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orden) {
            return null;
        }

        $orden['detalles'] = $this->obtenerDetallesPorOrden((int) $orden['id_orden']);
        return $orden;
    }

    public function obtenerDetallesPorOrden(int $idOrden): array
    {
        $sql = 'SELECT
                    d.id_detalle,
                    d.id_orden,
                    d.id_producto,
                    p.nombre AS nombre_producto,
                    d.cantidad,
                    d.precio_unitario,
                    d.sub_total
                FROM detalle_orden d
                INNER JOIN productos p ON d.id_producto = p.id_producto
                WHERE d.id_orden = :id_orden
                ORDER BY d.id_detalle ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_orden' => $idOrden]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function usuarioExiste(int $idUsuario): bool
    {
        $sql = 'SELECT id_usuario FROM usuarios WHERE id_usuario = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        return (bool) $stmt->fetchColumn();
    }

    public function obtenerProductoActivo(int $idProducto): ?array
    {
        $sql = 'SELECT id_producto, nombre, precio, estado
                FROM productos
                WHERE id_producto = :id
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idProducto]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    public function crearConDetalles(array $datosOrden, array $itemsResueltos, bool $totalEsNull): int
    {
        $this->db->beginTransaction();

        try {
            $totalCalculado = 0.0;
            foreach ($itemsResueltos as $item) {
                $totalCalculado += $item['sub_total'];
            }

            $totalFinal = $totalEsNull ? null : round($totalCalculado, 2);

            $sqlOrden = 'INSERT INTO ordenes (id_cliente, fecha_compra, total)
                         VALUES (:id_cliente, :fecha_compra, :total)';
            $stmtOrden = $this->db->prepare($sqlOrden);
            $stmtOrden->execute([
                ':id_cliente' => $datosOrden['id_cliente'],
                ':fecha_compra' => $datosOrden['fecha_compra'],
                ':total' => $totalFinal,
            ]);

            $idOrden = (int) $this->db->lastInsertId();

            if (!empty($itemsResueltos)) {
                $sqlDetalle = 'INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario, sub_total)
                               VALUES (:id_orden, :id_producto, :cantidad, :precio_unitario, :sub_total)';
                $stmtDetalle = $this->db->prepare($sqlDetalle);

                foreach ($itemsResueltos as $item) {
                    $stmtDetalle->execute([
                        ':id_orden' => $idOrden,
                        ':id_producto' => $item['id_producto'],
                        ':cantidad' => $item['cantidad'],
                        ':precio_unitario' => $item['precio_unitario'],
                        ':sub_total' => $item['sub_total'],
                    ]);
                }
            }

            $this->db->commit();
            return $idOrden;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = 'UPDATE ordenes
                SET id_cliente = :id_cliente,
                    fecha_compra = :fecha_compra,
                    total = :total
                WHERE id_orden = :id';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':id_cliente' => $datos['id_cliente'],
            ':fecha_compra' => $datos['fecha_compra'],
            ':total' => $datos['total'],
        ]);
    }

    public function eliminar(int $id): bool
    {
        $this->db->beginTransaction();

        try {
            $stmtDetalles = $this->db->prepare('DELETE FROM detalle_orden WHERE id_orden = :id_orden');
            $stmtDetalles->execute([':id_orden' => $id]);

            $stmtOrden = $this->db->prepare('DELETE FROM ordenes WHERE id_orden = :id');
            $stmtOrden->execute([':id' => $id]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}