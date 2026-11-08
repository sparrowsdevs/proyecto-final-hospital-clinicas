# Servicios Comunes

Código que usan todos los módulos. Está acá para no repetirlo en cada uno.

## Contenido

| Carpeta                       | Qué contiene                                                                                    |
| ----------------------------- | ----------------------------------------------------------------------------------------------- |
| `Conexion BD/`                | `conexion.php`: la conexión a MySQL con PDO.                                                    |
| `Configuracion/`              | `configuracion.php`: URL pública del sistema y token del QR.                                    |
| `Seguridad/`                  | `AccesoPublico.php`: validación del token que trae el paciente.                                 |
| `Autenticacion/`              | Login, logout, sesiones, roles y gestión de usuarios. Ver [su README](Autenticacion/README.md). |
| `Vista General/`              | `navbar.php` y sus estilos: el menú lateral y la barra superior de todos los paneles.           |
| `Permisos/` y `Validaciones/` | Reservadas para uso futuro.                                                                     |

## Conexión a la base

`Conexion` es un Singleton: todos los módulos comparten una única conexión PDO por petición. Está configurada con excepciones activadas y con `ATTR_EMULATE_PREPARES` en `false`, de modo que las consultas preparadas las resuelve realmente MySQL.

Los datos de conexión se toman de variables de entorno si existen:

| Variable        | Valor por defecto (XAMPP) |
| --------------- | ------------------------- |
| `SIGSM_DB_HOST` | `localhost`               |
| `SIGSM_DB_PORT` | `3306`                    |
| `SIGSM_DB_NAME` | `database_clinicas`       |
| `SIGSM_DB_USER` | `root`                    |
| `SIGSM_DB_PASS` | `mysql`                   |

Con Docker las define `docker-compose.yml`. En XAMPP, si el usuario `root` tiene otra contraseña, se ajusta en `conexion.php`.

## Configuración pública

`configuracion.php` define dos constantes:

- `URL_BASE_PUBLICA`: dirección con la que los celulares llegan al sistema (variable `SIGSM_URL_BASE`). Es la que se codifica dentro del QR.
- `TOKEN_ACCESO_PUBLICO`: clave secreta del QR (variable `SIGSM_TOKEN`). Quien no la tenga no entra al repositorio público.

Cambiar el token invalida todos los QR impresos. En producción el token siempre se define por variable de entorno; el valor de reemplazo que trae el archivo no debe usarse.

## Navbar

`Vista General/navbar.php` se incluye desde todos los paneles. Muestra el nombre y el rol reales del usuario con sesión y muestra solo las opciones que le corresponden: el Administrador ve además Carga de Archivos y Gestión de Usuarios. El estado «activo» del menú se calcula con la variable `$paginaActual` que define cada vista antes de incluirlo.
