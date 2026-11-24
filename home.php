<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muscle University</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link href="./dist/output.css" rel="stylesheet">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="flex flex-col">
    <header class="text-center p-8 hero-website">
        <h1 class="text-8xl p-4">Muscle University</h1> 
        <p>El cambio comienza contigo.</p>
        <div class="mt-4 flex justify-center gap-4">
            <a href="pages/rutinas.php" class="bg-brand-primary p-1.5 rounded-xl scale-100 hover:scale-110 transition-transform duration-300 border-2 border-white shadow-md hover:shadow-brand-primary">Explorar Rutinas</a>
            <a href="pages/contactanos.php" class="bg-brand-primary p-1.5 rounded-xl scale-100 hover:scale-110 transition-transform duration-300 border-2 border-white shadow-md hover:shadow-brand-primary">Unirme Ahora</a>
        </div>
    </header>
    <?php
    $base_url = ".";
    include 'components/navbar.php';
    ?>
    <section class="m-8 flex flex-col items-center">
        <h2 class="mb-8 text-center text-6xl p-6 bg-bg-card rounded-4xl max-w-150 shadow-xs shadow-gray-400 border-white border">¿Quienes somos?</h2>
        <p class="text-justify bg-bg-card rounded-2xl text-3xl p-10 max-w-200 shadow-xs shadow-gray-400 border-white border">Durante la pandemia, un grupo de expertos del fitness decidio juntarse y crear un servicio para todo el que quisiera dar 
        un cambio radical a su vida sin importar en donde este.<br>
        Es así que nace Muscle University, una web en la que encontraras todo la información y herramientas necesarias para que emprendas
        tu proceso de transformación sin problemas y formes parte de una hermosa comunidad con personas con el mismo objetivo. <br>
        No importa el punto de partida, solo necesitas de constancia, paciencia y disciplina para que cada día te encuentres mas cerca a
        la meta.
        </p>
    </section>
    <div class="bg-bg-card self-center flex flex-col items-center mt-5 rounded-4xl p-5 text-center shadow-xs shadow-gray-400 border-white borde text-6xl">
        <h2>Miembros Fundadores</h2>
    </div>
    <div class="grid grid-cols-2 gap-8">
        <div class="bg-bg-card flex flex-col items-center m-10 rounded-4xl p-5 text-center shadow-xs shadow-gray-400 border-white border min-h-250">
            <img src="images/home_images/carlos-coach.jpg" class="m-10 rounded-2xl">
            <h3 class="text-4xl mb-3 scale-100 transition-transform duration-300 hover:scale-110 cursor-pointer">Carlos Rodriguez</h3>
            <p class="mb-3 text-2xl">
                Especialista en biomecánica y fuerza. Con más de 10 años de experiencia entrenando atletas, su misión es llevar el conocimiento técnico de los gimnasios profesionales a cualquier persona en el mundo.
            </p>
            <div class="text-xl">
                <a href="javascript:void(0);" class="p-2 text-3xl"><i class="fa-brands fa-facebook scale-100 transition-transform duration-300 hover:scale-110"></i></a>
                <a href="javascript:void(0);" class="p-2 text-3xl"><i class="fa-brands fa-instagram scale-100 transition-transform duration-300 hover:scale-110"></i></a>
                <a href="javascript:void(0);" class="p-2 text-3xl"><i class="fa-brands fa-whatsapp scale-100 transition-transform duration-300 hover:scale-110"></i></a>
            </div>
        </div>
        <div class="bg-bg-card flex flex-col items-center m-10 rounded-4xl p-5 text-center shadow-xs shadow-gray-400 border-white border min-h-250">
            <img src="images/home_images/maeve-coach.jpg" class="m-10 rounded-2xl">
            <h3 class="text-4xl mb-3 scale-100 transition-transform duration-300 hover:scale-110 cursor-pointer">Maeve Dillon</h3>
            <p class="mb-3 text-2xl">
                Nutricionista deportiva y apasionada por los hábitos sostenibles. Cree firmemente que el cambio físico real comienza en la cocina sin prohibiciones extremas ni dietas imposibles.
            </p>
            <div class="text-xl">
                <a href="javascript:void(0);" class="p-2 text-3xl"><i class="fa-brands fa-facebook scale-100 transition-transform duration-300 hover:scale-110"></i></a>
                <a href="javascript:void(0);" class="p-2 text-3xl"><i class="fa-brands fa-instagram scale-100 transition-transform duration-300 hover:scale-110"></i></a>
                <a href="javascript:void(0);" class="p-2 text-3xl"><i class="fa-brands fa-whatsapp scale-100 transition-transform duration-300 hover:scale-110"></i></a>
            </div>
        </div>
    </div>
    <footer class="text-center mb-4">
        Mucle University&reg;
    </footer>
</body>
</html>