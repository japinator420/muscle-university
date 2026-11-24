<?php

function cargarEnv(string $rutaArchivo): void
{
    if (!file_exists($rutaArchivo) || !is_readable($rutaArchivo)) {
        throw new RuntimeException('No se encontró el archivo de configuración de entorno.');
    }

    $lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }

        [$clave, $valor] = array_pad(explode('=', $linea, 2), 2, '');
        $clave = trim($clave);
        $valor = trim($valor, " \t\n\r\0\x0B\"'");

        $_ENV[$clave] = $valor;
        putenv("$clave=$valor");
    }
}