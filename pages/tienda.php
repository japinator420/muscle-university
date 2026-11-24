<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muscle University - Tienda</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link href="../dist/output.css" rel="stylesheet">

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

    <p id="estado-tienda" class="text-center mt-4 text-sm opacity-80"></p>

    <section id="vista-categorias">
        <div id="contenedor-categorias" class="flex flex-row flex-wrap m-5 gap-6 justify-center"></div>
    </section>

    <section id="vista-productos" class="hidden">
        <div class="flex flex-col items-center gap-6 my-6 px-4">
            <div id="contenedor-productos" class="flex flex-row flex-wrap m-5 gap-6 justify-center"></div>
            <button
                type="button"
                id="btn-volver-categorias"
                class="inline-block w-60 text-center p-3 border rounded-xl border-white cursor-pointer"
            >
                Volver a categorias
            </button>
        </div>
    </section>

    <footer class="text-center mt-5">
        Muscle University®
    </footer>

    <script>
        const API_URL = '/muscle-university/index.php';
        const RUTA_IMG_CATEGORIAS = '/muscle-university/images/categorias/';
        const RUTA_IMG_PRODUCTOS = '/muscle-university/images/productos/';

        const vistaCategorias = document.getElementById('vista-categorias');
        const vistaProductos = document.getElementById('vista-productos');
        const contenedorCategorias = document.getElementById('contenedor-categorias');
        const contenedorProductos = document.getElementById('contenedor-productos');
        const btnVolver = document.getElementById('btn-volver-categorias');
        const estadoTienda = document.getElementById('estado-tienda');

        function escaparHtml(texto) {
            return String(texto ?? '')
                .replace(/&/g, '&')
                .replace(/</g, '<')
                .replace(/>/g, '>')
                .replace(/"/g, '"')
                .replace(/'/g, "'");
        }

        function setEstado(mensaje = '') {
            estadoTienda.textContent = mensaje;
        }

        function mostrarVistaProductos(activa) {
            vistaCategorias.classList.toggle('hidden', activa);
            vistaProductos.classList.toggle('hidden', !activa);
        }

        async function cargarCategorias() {
            mostrarVistaProductos(false);
            setEstado('Cargando categorías...');
            contenedorCategorias.innerHTML = '';

            try {
                const res = await fetch(`${API_URL}?recurso=categorias`);
                const json = await res.json();

                if (!res.ok || !json.ok) {
                    throw new Error(json.mensaje || 'No se pudieron cargar las categorías.');
                }

                if (!Array.isArray(json.data) || json.data.length === 0) {
                    setEstado('No hay categorías disponibles.');
                    return;
                }

                setEstado('');
                contenedorCategorias.innerHTML = json.data.map((cat) => {
                    const id = Number(cat.id_categoria);
                    const imagen = cat.imagen_referencial
                        ? `<img src="${RUTA_IMG_CATEGORIAS}${encodeURIComponent(cat.imagen_referencial)}" alt="${escaparHtml(cat.nombre)}" class="w-72 h-72 aspect-square object-cover rounded-lg mx-auto mt-4">`
                        : '';

                    return `
                        <button
                            type="button"
                            data-id-categoria="${id}"
                            class="btn-categoria text-center p-8 border rounded-xl border-white cursor-pointer"
                        >
                            <h3 class="text-3xl font-bold tracking-wide">${escaparHtml(cat.nombre)}</h3>
                            <p class="mt-2">${escaparHtml(cat.descripcion)}</p>
                            ${imagen}
                        </button>
                    `;
                }).join('');
            } catch (error) {
                setEstado(error.message || 'Error al conectar con la API de categorías.');
            }
        }

        async function cargarProductosPorCategoria(idCategoria) {
            mostrarVistaProductos(true);
            setEstado('Cargando productos...');
            contenedorProductos.innerHTML = '';

            try {
                const res = await fetch(`${API_URL}?recurso=productos&categoria=${encodeURIComponent(idCategoria)}`);
                const json = await res.json();

                if (!res.ok || !json.ok) {
                    throw new Error(json.mensaje || 'No se pudieron cargar los productos.');
                }

                if (!Array.isArray(json.data) || json.data.length === 0) {
                    setEstado('No hay productos registrados en esta categoría.');
                    return;
                }

                setEstado('');
                contenedorProductos.innerHTML = json.data.map((prod) => {
                    const imagen = prod.imagen_prod
                        ? `<img src="${RUTA_IMG_PRODUCTOS}${encodeURIComponent(prod.imagen_prod)}" alt="${escaparHtml(prod.nombre)}" class="w-48 h-48 object-cover rounded-lg mx-auto mb-3">`
                        : '';

                    return `
                        <div class="text-center p-8 border rounded-xl border-white">
                            ${imagen}
                            <h3 class="text-xl font-bold">${escaparHtml(prod.nombre)}</h3>
                            <p class="mt-1">${escaparHtml(prod.descripcion)}</p>
                            <p class="mt-2 font-semibold">$${escaparHtml(prod.precio)}</p>
                        </div>
                    `;
                }).join('');
            } catch (error) {
                setEstado(error.message || 'Error al conectar con la API de productos.');
            }
        }

        contenedorCategorias.addEventListener('click', (e) => {
            const tarjeta = e.target.closest('.btn-categoria');
            if (!tarjeta) return;

            const idCategoria = tarjeta.dataset.idCategoria;
            const url = new URL(window.location);
            url.searchParams.set('categoria', idCategoria);
            window.history.pushState({}, '', url);

            cargarProductosPorCategoria(idCategoria);
        });

        btnVolver.addEventListener('click', () => {
            const url = new URL(window.location);
            url.searchParams.delete('categoria');
            window.history.pushState({}, '', url);

            cargarCategorias();
        });

        window.addEventListener('popstate', () => {
            const catParam = new URLSearchParams(window.location.search).get('categoria');
            if (catParam) {
                cargarProductosPorCategoria(catParam);
            } else {
                cargarCategorias();
            }
        });

        // Carga inicial según query string actual, para recargar la pagina por ejemplo
        const categoriaInicial = new URLSearchParams(window.location.search).get('categoria');
        if (categoriaInicial) {
            cargarProductosPorCategoria(categoriaInicial);
        } else {
            cargarCategorias();
        }
    </script>
</body>
</html>