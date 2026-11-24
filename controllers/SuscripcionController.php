<?php

class SuscripcionController
{
    private SuscripcionModel $modelo;

    public function __construct(SuscripcionModel $modelo)
    {
        $this->modelo = $modelo;
    }

    public function listar(?int $idCliente = null, ?int $idAsesor = null): void
    {
        $suscripciones = $this->modelo->obtenerTodos($idCliente, $idAsesor);
        $this->responderJson(200, [
            'ok' => true,
            'data' => $suscripciones,
        ]);
    }

    public function mostrar(int $id): void
    {
        if ($id <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'ID de suscripción inválido.',
            ]);
            return;
        }

        $suscripcion = $this->modelo->obtenerPorId($id);
        if (!$suscripcion) {
            $this->responderJson(404, [
                'ok' => false,
                'mensaje' => 'Suscripción no encontrada.',
            ]);
            return;
        }

        $this->responderJson(200, [
            'ok' => true,
            'data' => $suscripcion,
        ]);
    }

    public function crear(array $entrada): void
    {
        $validacion = $this->validarDatos($entrada);
        if ($validacion['error'] !== null) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => $validacion['error'],
            ]);
            return;
        }

        $datos = $validacion['datos'];
        $errorRelacion = $this->validarParticipantes($datos['id_cliente'], $datos['id_asesor']);
        if ($errorRelacion !== null) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => $errorRelacion,
            ]);
            return;
        }

        try {
            $nuevoId = $this->modelo->crear($datos);
            $creada = $this->modelo->obtenerPorId($nuevoId);

            $this->responderJson(201, [
                'ok' => true,
                'mensaje' => 'Suscripción creada correctamente.',
                'data' => $creada,
            ]);
        } catch (PDOException $e) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'No se pudo crear la suscripción. Verifique cliente, asesor y fechas.',
            ]);
        }
    }

    public function actualizar(int $id, array $entrada): void
    {
        if ($id <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'ID de suscripción inválido.',
            ]);
            return;
        }

        $actual = $this->modelo->obtenerPorId($id);
        if (!$actual) {
            $this->responderJson(404, [
                'ok' => false,
                'mensaje' => 'Suscripción no encontrada.',
            ]);
            return;
        }

        $fusionado = array_merge($actual, $entrada);
        $validacion = $this->validarDatos($fusionado);
        if ($validacion['error'] !== null) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => $validacion['error'],
            ]);
            return;
        }

        $datos = $validacion['datos'];
        $errorRelacion = $this->validarParticipantes($datos['id_cliente'], $datos['id_asesor']);
        if ($errorRelacion !== null) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => $errorRelacion,
            ]);
            return;
        }

        try {
            $this->modelo->actualizar($id, $datos);
            $actualizada = $this->modelo->obtenerPorId($id);

            $this->responderJson(200, [
                'ok' => true,
                'mensaje' => 'Suscripción actualizada correctamente.',
                'data' => $actualizada,
            ]);
        } catch (PDOException $e) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'No se pudo actualizar la suscripción.',
            ]);
        }
    }

    public function eliminar(int $id): void
    {
        if ($id <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'ID de suscripción inválido.',
            ]);
            return;
        }

        $suscripcion = $this->modelo->obtenerPorId($id);
        if (!$suscripcion) {
            $this->responderJson(404, [
                'ok' => false,
                'mensaje' => 'Suscripción no encontrada.',
            ]);
            return;
        }

        try {
            $this->modelo->eliminar($id);
            $this->responderJson(200, [
                'ok' => true,
                'mensaje' => 'Suscripción eliminada correctamente.',
            ]);
        } catch (PDOException $e) {
            $this->responderJson(409, [
                'ok' => false,
                'mensaje' => 'No se pudo eliminar la suscripción.',
            ]);
        }
    }

    private function validarParticipantes(int $idCliente, int $idAsesor): ?string
    {
        if ($idCliente === $idAsesor) {
            return 'El cliente y el asesor no pueden ser el mismo usuario.';
        }

        $cliente = $this->modelo->obtenerUsuarioConRol($idCliente);
        if (!$cliente) {
            return 'El id_cliente indicado no existe en usuarios.';
        }

        $asesor = $this->modelo->obtenerUsuarioConRol($idAsesor);
        if (!$asesor) {
            return 'El id_asesor indicado no existe en usuarios.';
        }

        if ($asesor['rol'] !== 'asesor') {
            return 'El usuario asignado como id_asesor debe tener el rol asesor.';
        }

        return null;
    }

    private function validarDatos(array $entrada): array
    {
        $idClienteRaw = $entrada['id_cliente'] ?? null;
        $idAsesorRaw = $entrada['id_asesor'] ?? null;

        if (!is_numeric($idClienteRaw) || (int) $idClienteRaw <= 0) {
            return ['error' => 'id_cliente es obligatorio y debe ser un entero positivo.', 'datos' => null];
        }
        if (!is_numeric($idAsesorRaw) || (int) $idAsesorRaw <= 0) {
            return ['error' => 'id_asesor es obligatorio y debe ser un entero positivo.', 'datos' => null];
        }

        $idCliente = (int) $idClienteRaw;
        $idAsesor = (int) $idAsesorRaw;

        $estado = 1;
        if (array_key_exists('estado', $entrada) && $entrada['estado'] !== null) {
            $estadoBool = filter_var($entrada['estado'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($estadoBool === null) {
                return ['error' => 'El estado debe ser booleano (true/false o 1/0).', 'datos' => null];
            }
            $estado = $estadoBool ? 1 : 0;
        }

        $inicio = date('Y-m-d');
        if (array_key_exists('inicio_suscripcion', $entrada) && $entrada['inicio_suscripcion'] !== null && $entrada['inicio_suscripcion'] !== '') {
            $fechaInicio = trim((string) $entrada['inicio_suscripcion']);
            if (!$this->esFechaValida($fechaInicio)) {
                return ['error' => 'inicio_suscripcion debe tener formato válido YYYY-MM-DD.', 'datos' => null];
            }
            $inicio = $fechaInicio;
        }

        $fin = null;
        if (array_key_exists('fin_suscripcion', $entrada) && $entrada['fin_suscripcion'] !== null && $entrada['fin_suscripcion'] !== '') {
            $fechaFin = trim((string) $entrada['fin_suscripcion']);
            if (!$this->esFechaValida($fechaFin)) {
                return ['error' => 'fin_suscripcion debe tener formato válido YYYY-MM-DD.', 'datos' => null];
            }
            if ($fechaFin < $inicio) {
                return ['error' => 'fin_suscripcion no puede ser anterior a inicio_suscripcion.', 'datos' => null];
            }
            $fin = $fechaFin;
        }

        return [
            'error' => null,
            'datos' => [
                'id_cliente' => $idCliente,
                'id_asesor' => $idAsesor,
                'estado' => $estado,
                'inicio_suscripcion' => $inicio,
                'fin_suscripcion' => $fin,
            ],
        ];
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