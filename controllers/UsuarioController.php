<?php

class UsuarioController
{
    private UsuarioModel $modelo;
    private const ROLES_PERMITIDOS = ['cliente', 'asesor', 'admin'];

    public function __construct(UsuarioModel $modelo)
    {
        $this->modelo = $modelo;
    }

    public function listar(?string $rol = null): void
    {
        if ($rol !== null && $rol !== '' && !in_array($rol, self::ROLES_PERMITIDOS, true)) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'Rol no válido. Use cliente, asesor o admin.',
            ]);
            return;
        }

        $usuarios = $this->modelo->obtenerTodos($rol);
        $this->responderJson(200, [
            'ok' => true,
            'data' => $usuarios,
        ]);
    }

    public function mostrar(int $id): void
    {
        if ($id <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'ID de usuario inválido.',
            ]);
            return;
        }

        $usuario = $this->modelo->obtenerPorId($id);
        if (!$usuario) {
            $this->responderJson(404, [
                'ok' => false,
                'mensaje' => 'Usuario no encontrado.',
            ]);
            return;
        }

        $this->responderJson(200, [
            'ok' => true,
            'data' => $usuario,
        ]);
    }

    public function crear(array $entrada): void
    {
        $validacion = $this->validarDatos($entrada, true);
        if ($validacion['error'] !== null) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => $validacion['error'],
            ]);
            return;
        }

        $datos = $validacion['datos'];
        if ($this->modelo->correoExiste($datos['correo'])) {
            $this->responderJson(409, [
                'ok' => false,
                'mensaje' => 'Ya existe un usuario registrado con ese correo.',
            ]);
            return;
        }

        try {
            $nuevoId = $this->modelo->crear($datos);
            $creado = $this->modelo->obtenerPorId($nuevoId);

            $this->responderJson(201, [
                'ok' => true,
                'mensaje' => 'Usuario creado correctamente.',
                'data' => $creado,
            ]);
        } catch (PDOException $e) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'No se pudo crear el usuario. Revise los datos enviados.',
            ]);
        }
    }

    public function actualizar(int $id, array $entrada): void
    {
        if ($id <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'ID de usuario inválido.',
            ]);
            return;
        }

        $actual = $this->modelo->obtenerPorId($id);
        if (!$actual) {
            $this->responderJson(404, [
                'ok' => false,
                'mensaje' => 'Usuario no encontrado.',
            ]);
            return;
        }

        $fusionado = array_merge($actual, $entrada);
        $validacion = $this->validarDatos($fusionado, false);
        if ($validacion['error'] !== null) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => $validacion['error'],
            ]);
            return;
        }

        $datos = $validacion['datos'];
        if ($this->modelo->correoExiste($datos['correo'], $id)) {
            $this->responderJson(409, [
                'ok' => false,
                'mensaje' => 'Ese correo ya pertenece a otro usuario.',
            ]);
            return;
        }

        try {
            $this->modelo->actualizar($id, $datos);
            $actualizado = $this->modelo->obtenerPorId($id);

            $this->responderJson(200, [
                'ok' => true,
                'mensaje' => 'Usuario actualizado correctamente.',
                'data' => $actualizado,
            ]);
        } catch (PDOException $e) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'No se pudo actualizar el usuario.',
            ]);
        }
    }

    public function eliminar(int $id): void
    {
        if ($id <= 0) {
            $this->responderJson(400, [
                'ok' => false,
                'mensaje' => 'ID de usuario inválido.',
            ]);
            return;
        }

        $usuario = $this->modelo->obtenerPorId($id);
        if (!$usuario) {
            $this->responderJson(404, [
                'ok' => false,
                'mensaje' => 'Usuario no encontrado.',
            ]);
            return;
        }

        try {
            $this->modelo->eliminar($id);
            $this->responderJson(200, [
                'ok' => true,
                'mensaje' => 'Usuario eliminado correctamente.',
            ]);
        } catch (PDOException $e) {
            // 23000 suele indicar restricción de llave foránea (suscripciones u órdenes asociadas)
            $this->responderJson(409, [
                'ok' => false,
                'mensaje' => 'No se puede eliminar el usuario porque tiene suscripciones u órdenes asociadas.',
            ]);
        }
    }

    private function validarDatos(array $entrada, bool $esCreacion): array
    {
        $nombre = trim((string) ($entrada['nombre'] ?? ''));
        $correo = strtolower(trim((string) ($entrada['correo'] ?? '')));
        $rol = $entrada['rol'] ?? ($esCreacion ? 'cliente' : null);
        $rol = is_string($rol) ? strtolower(trim($rol)) : '';

        if ($nombre === '' || mb_strlen($nombre) > 50) {
            return ['error' => 'El nombre es obligatorio y debe tener máximo 50 caracteres.', 'datos' => null];
        }

        if ($correo === '' || mb_strlen($correo) > 100 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Debe proporcionar un correo electrónico válido.', 'datos' => null];
        }

        if (!in_array($rol, self::ROLES_PERMITIDOS, true)) {
            return ['error' => 'Rol no permitido (cliente, asesor, admin).', 'datos' => null];
        }

        $edad = null;
        if (array_key_exists('edad', $entrada) && $entrada['edad'] !== null && $entrada['edad'] !== '') {
            if (!is_numeric($entrada['edad']) || (int) $entrada['edad'] != $entrada['edad']) {
                return ['error' => 'La edad debe ser un número entero.', 'datos' => null];
            }
            $edad = (int) $entrada['edad'];
            if ($edad < 14 || $edad > 120) {
                return ['error' => 'La edad debe estar entre 14 y 120 años.', 'datos' => null];
            }
        }

        $inicioContrato = null;
        if (array_key_exists('inicio_contrato', $entrada) && $entrada['inicio_contrato'] !== null && $entrada['inicio_contrato'] !== '') {
            $fecha = trim((string) $entrada['inicio_contrato']);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                return ['error' => 'inicio_contrato debe tener formato YYYY-MM-DD.', 'datos' => null];
            }
            $inicioContrato = $fecha;
        }

        $estado = 1;
        if (array_key_exists('estado', $entrada) && $entrada['estado'] !== null) {
            $estado = filter_var($entrada['estado'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($estado === null) {
                return ['error' => 'El estado debe ser booleano (true/false o 1/0).', 'datos' => null];
            }
            $estado = $estado ? 1 : 0;
        }

        return [
            'error' => null,
            'datos' => [
                'nombre' => $nombre,
                'correo' => $correo,
                'rol' => $rol,
                'edad' => $edad,
                'inicio_contrato' => $inicioContrato,
                'estado' => $estado,
            ],
        ];
    }

    private function responderJson(int $codigoHttp, array $payload): void
    {
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}