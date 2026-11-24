<?php

class CategoriaModel
{
    private const COLUMNAS = 'id_categoria, nombre, descripcion, id_categoria_padre, estado, imagen_referencial';

    public static function obtenerTodos(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT ' . self::COLUMNAS . '
             FROM categorias
             ORDER BY id_categoria ASC'
        );
        return $stmt->fetchAll();
    }

    public static function obtenerPorId(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT ' . self::COLUMNAS . '
             FROM categorias
             WHERE id_categoria = :id'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }

    public static function existeCategoria(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare('SELECT 1 FROM categorias WHERE id_categoria = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public static function crear(PDO $pdo, array $datos): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO categorias (nombre, descripcion, id_categoria_padre, estado, imagen_referencial)
             VALUES (:nombre, :descripcion, :id_categoria_padre, :estado, :imagen_referencial)'
        );
        $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':id_categoria_padre' => $datos['id_categoria_padre'],
            ':estado' => $datos['estado'] ? 1 : 0,
            ':imagen_referencial' => $datos['imagen_referencial'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function actualizar(PDO $pdo, int $id, array $datos): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE categorias
             SET nombre = :nombre,
                 descripcion = :descripcion,
                 id_categoria_padre = :id_categoria_padre,
                 estado = :estado,
                 imagen_referencial = :imagen_referencial
             WHERE id_categoria = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':id_categoria_padre' => $datos['id_categoria_padre'],
            ':estado' => $datos['estado'] ? 1 : 0,
            ':imagen_referencial' => $datos['imagen_referencial'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function eliminar(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare('DELETE FROM categorias WHERE id_categoria = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}