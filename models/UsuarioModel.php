<?php

class UsuarioModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos(?string $rol = null): array
    {
        if ($rol !== null && $rol !== '') {
            $sql = 'SELECT id_usuario, nombre, correo, rol, edad, inicio_contrato, estado, fecha_registro
                    FROM usuarios
                    WHERE rol = :rol
                    ORDER BY id_usuario ASC';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':rol' => $rol]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = 'SELECT id_usuario, nombre, correo, rol, edad, inicio_contrato, estado, fecha_registro
                FROM usuarios
                ORDER BY id_usuario ASC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT id_usuario, nombre, correo, rol, edad, inicio_contrato, estado, fecha_registro
                FROM usuarios
                WHERE id_usuario = :id
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ?: null;
    }

    public function correoExiste(string $correo, ?int $excluirId = null): bool
    {
        if ($excluirId !== null) {
            $sql = 'SELECT id_usuario FROM usuarios WHERE correo = :correo AND id_usuario <> :id LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':correo' => $correo,
                ':id' => $excluirId,
            ]);
        } else {
            $sql = 'SELECT id_usuario FROM usuarios WHERE correo = :correo LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':correo' => $correo]);
        }

        return (bool) $stmt->fetchColumn();
    }

    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO usuarios (nombre, correo, rol, edad, inicio_contrato, estado)
                VALUES (:nombre, :correo, :rol, :edad, :inicio_contrato, :estado)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':correo' => $datos['correo'],
            ':rol' => $datos['rol'],
            ':edad' => $datos['edad'],
            ':inicio_contrato' => $datos['inicio_contrato'],
            ':estado' => $datos['estado'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = 'UPDATE usuarios
                SET nombre = :nombre,
                    correo = :correo,
                    rol = :rol,
                    edad = :edad,
                    inicio_contrato = :inicio_contrato,
                    estado = :estado
                WHERE id_usuario = :id';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $datos['nombre'],
            ':correo' => $datos['correo'],
            ':rol' => $datos['rol'],
            ':edad' => $datos['edad'],
            ':inicio_contrato' => $datos['inicio_contrato'],
            ':estado' => $datos['estado'],
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = 'DELETE FROM usuarios WHERE id_usuario = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}