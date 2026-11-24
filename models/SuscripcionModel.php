<?php

class SuscripcionModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos(?int $idCliente = null, ?int $idAsesor = null): array
    {
        $sql = 'SELECT
                    s.id_suscripcion,
                    s.id_cliente,
                    u_cli.nombre AS nombre_cliente,
                    u_cli.correo AS correo_cliente,
                    s.id_asesor,
                    u_ase.nombre AS nombre_asesor,
                    u_ase.correo AS correo_asesor,
                    s.estado,
                    s.inicio_suscripcion,
                    s.fin_suscripcion
                FROM suscripciones s
                INNER JOIN usuarios u_cli ON s.id_cliente = u_cli.id_usuario
                INNER JOIN usuarios u_ase ON s.id_asesor = u_ase.id_usuario';

        $condiciones = [];
        $parametros = [];

        if ($idCliente !== null && $idCliente > 0) {
            $condiciones[] = 's.id_cliente = :id_cliente';
            $parametros[':id_cliente'] = $idCliente;
        }

        if ($idAsesor !== null && $idAsesor > 0) {
            $condiciones[] = 's.id_asesor = :id_asesor';
            $parametros[':id_asesor'] = $idAsesor;
        }

        if (!empty($condiciones)) {
            $sql .= ' WHERE ' . implode(' AND ', $condiciones);
        }

        $sql .= ' ORDER BY s.id_suscripcion ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT
                    s.id_suscripcion,
                    s.id_cliente,
                    u_cli.nombre AS nombre_cliente,
                    u_cli.correo AS correo_cliente,
                    s.id_asesor,
                    u_ase.nombre AS nombre_asesor,
                    u_ase.correo AS correo_asesor,
                    s.estado,
                    s.inicio_suscripcion,
                    s.fin_suscripcion
                FROM suscripciones s
                INNER JOIN usuarios u_cli ON s.id_cliente = u_cli.id_usuario
                INNER JOIN usuarios u_ase ON s.id_asesor = u_ase.id_usuario
                WHERE s.id_suscripcion = :id
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ?: null;
    }

    public function obtenerUsuarioConRol(int $idUsuario): ?array
    {
        $sql = 'SELECT id_usuario, rol, estado FROM usuarios WHERE id_usuario = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ?: null;
    }

    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO suscripciones (id_cliente, id_asesor, estado, inicio_suscripcion, fin_suscripcion)
                VALUES (:id_cliente, :id_asesor, :estado, :inicio_suscripcion, :fin_suscripcion)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_cliente' => $datos['id_cliente'],
            ':id_asesor' => $datos['id_asesor'],
            ':estado' => $datos['estado'],
            ':inicio_suscripcion' => $datos['inicio_suscripcion'],
            ':fin_suscripcion' => $datos['fin_suscripcion'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = 'UPDATE suscripciones
                SET id_cliente = :id_cliente,
                    id_asesor = :id_asesor,
                    estado = :estado,
                    inicio_suscripcion = :inicio_suscripcion,
                    fin_suscripcion = :fin_suscripcion
                WHERE id_suscripcion = :id';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':id_cliente' => $datos['id_cliente'],
            ':id_asesor' => $datos['id_asesor'],
            ':estado' => $datos['estado'],
            ':inicio_suscripcion' => $datos['inicio_suscripcion'],
            ':fin_suscripcion' => $datos['fin_suscripcion'],
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = 'DELETE FROM suscripciones WHERE id_suscripcion = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}