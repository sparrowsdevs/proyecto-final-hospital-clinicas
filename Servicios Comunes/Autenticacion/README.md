# Autenticación

Todo lo relacionado con quién entra al sistema y qué puede hacer.

## Archivos

| Archivo                | Función                                                                                             |
| ---------------------- | --------------------------------------------------------------------------------------------------- |
| `AuthController.php`   | Inicia y cierra sesión, comprueba roles y protege las vistas.                                       |
| `usuarioModelo.php`    | Consultas sobre usuarios y roles (buscar, crear, actualizar, suspender, reactivar).                 |
| `procesar-login.php`   | Recibe cédula y contraseña desde `index.php` (AJAX) y responde en JSON.                             |
| `procesar-usuario.php` | Endpoint AJAX de Gestión de Usuarios: crear, actualizar, suspender y reactivar. Solo Administrador. |
| `logout.php`           | Cierra la sesión y vuelve al login.                                                                 |

## Flujo de ingreso

1. `index.php` valida el formato de la cédula en el navegador y envía los datos con `fetch` a `procesar-login.php`.
2. `AuthController::iniciarSesion()` busca el usuario por cédula. Si está suspendido o la contraseña no coincide (`password_verify`), rechaza el ingreso con un mensaje único («Cédula o contraseña incorrectas, o usuario inactivo»), para no revelar si la cédula existe.
3. Si es correcto, guarda en la sesión el identificador, la cédula, el nombre, el apellido y los roles del usuario. **La contraseña nunca se guarda en la sesión.**
4. El navegador es redirigido al Panel de Documentación.

## Cómo proteger una vista nueva

Al comienzo de cada vista o controlador que exija sesión:

```php
require_once __DIR__ . '/../../Servicios Comunes/Autenticacion/AuthController.php';

$auth = new AuthController();
$auth->protegerRuta();                 // cualquier usuario con sesión
// $auth->protegerRuta('Administrador'); // solo ese rol
```

`protegerRuta()` redirige al login si no hay sesión o si el rol no alcanza, y envía cabeceras anti-caché para que el botón «Atrás» del navegador no muestre un panel después de cerrar sesión.

## Reglas de negocio

- **Borrado lógico:** los usuarios no se eliminan, se suspenden (`activo = 0`) y se pueden reactivar.
- **Un Administrador no puede suspenderse a sí mismo ni quitarse su propio rol**, para no dejar el sistema sin administradores.
- Los roles vigentes son dos: Administrador y Usuario Básico (`rol.nombre_rol` es un ENUM en el script actual). Los choferes y acompañantes de los traslados se eligen entre los usuarios activos, sin un rol propio.
- La relación usuario-rol es de muchos a muchos (`usuario_rol`), aunque hoy cada usuario tiene un rol.

## Pendiente

Los botones «¿Olvidó su contraseña?» y «Solicitar acceso» del login son solo interfaz. Falta decidir si las solicitudes se guardan en la base, se envían por correo o ambas.
