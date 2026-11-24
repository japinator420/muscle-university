# Muscle University

Aplicación web y API RESTful desarrollada bajo el patrón **MVC** en PHP con respaldo en MySQL para la gestión de usuarios, suscripciones de asesoría, órdenes, detalles de orden y catálogo de productos y categorías.

## Tecnologías utilizadas

* **Backend y API:** PHP (`PDO`), arquitectura MVC (`models/`, `controllers/`, `routes/`), `index.php` como enrutador central y carga de variables de entorno mediante `config/env.php` y `config/database.php`.
* **Base de datos:** MySQL / MariaDB con relaciones, restricciones, lógica de carrito/órdenes y el procedimiento almacenado `sp_obtener_clientes_por_asesor`.
* **Frontend:** HTML, CSS, JavaScript y Tailwind CSS (`dist/output.css` compilado, con `src/input.css` y `package.json`).
* **Servidor local:** XAMPP (Apache y MySQL).

## Estructura del proyecto

```text
muscle-university/
├── config/          # Carga de entorno (.env) y conexión PDO a MySQL
├── controllers/     # Lógica de controladores por recurso
├── database/        # Scripts SQL de estructura, datos y procedimientos
├── dist/            # Archivos CSS compilados
├── models/          # Acceso a datos y consultas SQL con PDO
├── routes/          # Definición de rutas y métodos HTTP por recurso
├── src/             # Archivos fuente de estilos (Tailwind CSS)
├── .env.example     # Plantilla de variables de entorno
├── .gitignore       # Reglas de exclusión para Git
├── index.php        # Punto de entrada y enrutador principal
└── README.md        # Documentación del proyecto
```

## Ejecución en local con XAMPP

1. **Ubicar el proyecto en XAMPP:** clona o copia el repositorio dentro de la carpeta `htdocs` de XAMPP (por ejemplo, `C:\xampp\htdocs\muscle-university`).
2. **Iniciar servicios:** abre el panel de control de **XAMPP** y arranca **Apache** y **MySQL**.
3. **Crear e importar la base de datos:** crea la base de datos en MySQL e importa el script `.sql` del proyecto (incluyendo tablas, datos iniciales y el procedimiento `sp_obtener_clientes_por_asesor`).
4. **Configurar las variables de entorno:** haz una copia de `.env.example`, renómbrala a `.env` en la raíz del proyecto y completa los valores correspondientes:
   * `DB_HOST` (por defecto `127.0.0.1`)
   * `DB_PORT` (por defecto `3306`)
   * `DB_NAME` (nombre de la base de datos creada en el paso anterior)
   * `DB_USER` (por ejemplo `root` en XAMPP)
   * `DB_PASS` (contraseña de MySQL; vacía por defecto en XAMPP)
   * `DB_CHARSET` (por defecto `utf8mb4`)
5. **Abrir en el navegador o probar la API:** accede a `http://localhost/muscle-university/` (o a la ruta correspondiente según el nombre de la carpeta en `htdocs`) o envía peticiones HTTP a los endpoints desde Postman.