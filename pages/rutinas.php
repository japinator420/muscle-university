<?php
include '../api_ejercicios.php';
$ejercicios = getEjercicios();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muscle University</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link href="../dist/output.css" rel="stylesheet">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="flex flex-col">
    <header class="text-center p-8 hero-website">
        <h1 class="text-8xl p-4">Muscle University</h1>
        <p>El cambio comienza contigo.</p>
    </header>
    <?php
    $base_url = ".."; 
    include '../components/navbar.php';
    ?>
    <div class="grid grid-cols-3 gap-4">
        <?php foreach($ejercicios as $ejercicio): ?>
            <?php 
                // Buscamos la traducción en español (language = 4) o inglés (language = 2) por defecto
                $traduccion = null;
                if (!empty($ejercicio['translations'])) {
                    foreach ($ejercicio['translations'] as $t) {
                        if ($t['language'] == 4) { // 4 es español en Wger
                            $traduccion = $t;
                            break;
                        }
                    }
                    // Si no hay en español, agarramos la primera que encuentre por defecto
                    if (!$traduccion) {
                        $traduccion = $ejercicio['translations'][0];
                    }
                }
            ?>
            <div class="bg-white p-4 rounded-xl shadow">
                <h4 class="font-bold text-lg text-black">
                    <?= htmlspecialchars($traduccion['name'] ?? 'Sin nombre'); ?>
                </h4>
                <div class="text-gray-600 text-sm mt-2">
                    <?= nl2br($traduccion['description'] ?? 'Sin descripción disponible'); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <footer class="text-center">
        Mucle University&reg;
    </footer>
</body>
</html>