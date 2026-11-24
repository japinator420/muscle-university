<?php 
$base_url = $base_url ?? "."; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link href="../dist/output.css" rel="stylesheet">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="bg-bg-card/70 sticky top-0 z-50 backdrop-blur-md">
    <nav class="flex flex-row border-2 border-solid rounded-xl">
        <a class="cursor-pointer p-2 pl-4 hover:text-brand-hover scale-100 hover:scale-110 transition-transform duration-300" href="<?= $base_url; ?>/home.php"><i class="fa-solid fa-house pr-2"></i>Inicio</a>
        <a class="cursor-pointer p-2 pl-4 hover:text-brand-hover scale-100 hover:scale-110 transition-transform duration-300" href="<?= $base_url; ?>/pages/rutinas.php"><i class="fa-solid fa-dumbbell pr-2"></i>Rutinas</a>
        <a class="cursor-pointer p-2 pl-4 hover:text-brand-hover scale-100 hover:scale-110 transition-transform duration-300" href="<?= $base_url; ?>/pages/alimentacion.php"><i class="fa-solid fa-utensils pr-2"></i>Alimentacion</a>
        <a class="cursor-pointer p-2 pl-4 hover:text-brand-hover scale-100 hover:scale-110 transition-transform duration-300" href="<?= $base_url; ?>/pages/calendario.php"><i class="fa-solid fa-calendar pr-2"></i>Calendario</a>
        <a class="cursor-pointer p-2 pl-4 hover:text-brand-hover scale-100 hover:scale-110 transition-transform duration-300" href="<?= $base_url; ?>/pages/tienda.php"><i class="fa-solid fa-cart-shopping pr-2"></i>Tienda</a>
        <a class="cursor-pointer p-2 pl-4 hover:text-brand-hover scale-100 hover:scale-110 transition-transform duration-300" href="<?= $base_url; ?>/pages/contactanos.php"><i class="fa-solid fa-circle-user pr-2"></i>Contactanos</a>
    </nav>
</div>
</body>
</html>