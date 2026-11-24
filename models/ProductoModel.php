<?php

class ProductoModel
{
    private const COLUMNAS = 'id_producto, nombre, descripcion, precio, id_categoria, estado, imagen_prod, fecha_ingreso';

    public static function obtenerTodos(PDO $pdo, ?int $idCategoria = null): array
    {
        if ($idCategoria !== null) {
            $stmt = $pdo->prepare(
                'SELECT ' . self::COLUMNAS . '
                 FROM productos
                 WHERE id_categoria = :id_categoria
                 ORDER BY id_producto ASC'
            );
            $stmt->execute([':id_categoria' => $idCategoria]);
            return $stmt->fetchAll();
        }

        $stmt = $pdo->query(
            'SELECT ' . self::COLUMNAS . '
             FROM productos
             ORDER BY id_producto ASC'
        );
        return $stmt->fetchAll();
    }

    public static function obtenerPorId(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT ' . self::COLUMNAS . '
             FROM productos
             WHERE id_producto = :id'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }

    public static function existeCategoria(PDO $pdo, int $idCategoria): bool
    {
        $stmt = $pdo->prepare('SELECT 1 FROM categorias WHERE id_categoria = :id_categoria LIMIT 1');
        $stmt->execute([':id_categoria' => $idCategoria]);
        return (bool) $stmt->fetchColumn();
    }

    public static function crear(PDO $pdo, array $datos): int
    {
        $sql = 'INSERT INTO productos (nombre, descripcion, precio, id_categoria, estado, imagen_prod';
        $placeholders = 'VALUES (:nombre, :descripcion, :precio, :id_categoria, :estado, :imagen_prod';
        $params = [
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':precio' => $datos['precio'],
            ':id_categoria' => $datos['id_categoria'],
            ':estado' => $datos['estado'] ? 1 : 0,
            ':imagen_prod' => $datos['imagen_prod'],
        ];

        if ($datos['fecha_ingreso'] !== null) {
            $sql .= ', fecha_ingreso';
            $placeholders .= ', :fecha_ingreso';
            $params[':fecha_ingreso'] = $datos['fecha_ingreso'];
        }

        $sql .= ') ' . $placeholders . ')';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $pdo->lastInsertId();
    }

    public static function actualizar(PDO $pdo, int $id, array $datos): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE productos
             SET nombre = :nombre,
                 descripcion = :descripcion,
                 precio = :precio,
                 id_categoria = :id_categoria,
                 estado = :estado,
                 imagen_prod = :imagen_prod,
                 fecha_ingreso = :fecha_ingreso
             WHERE id_producto = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':precio' => $datos['precio'],
            ':id_categoria' => $datos['id_categoria'],
            ':estado' => $datos['estado'] ? 1 : 0,
            ':imagen_prod' => $datos['imagen_prod'],
            ':fecha_ingreso' => $datos['fecha_ingreso'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function eliminar(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare('DELETE FROM productos WHERE id_producto = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}