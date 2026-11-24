<?php
function getEjercicios() {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://wger.de/api/v2/exerciseinfo/?format=json&language=4");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Solución habitual para XAMPP local (desactiva verificación SSL estricta)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // User-Agent por si el servidor bloquea peticiones sin cabecera de navegador
    curl_setopt($ch, CURLOPT_USERAGENT, 'MuscleUniversity/1.0');

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        // Imprime el error exacto de cURL en pantalla si falla
        echo 'Error en cURL: ' . curl_error($ch);
        curl_close($ch);
        return [];
    }

    curl_close($ch);

    $datos = json_decode($response, true);

    // Devuelve los datos o un array vacío si no hay resultados
    return $datos['results'] ?? [];
}
?>