# Configuración del contenedor

Esta carpeta guarda los ajustes de Apache y PHP que usa la imagen de Docker. El `Dockerfile` y el `docker-compose.yml` están en la raíz del repositorio; la guía completa de despliegue está en el [README principal](../README.md#9-despliegue-en-un-servidor-linux-con-docker).

## Archivos

| Archivo             | Qué hace                                                                                                                       |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| `apache/sigsm.conf` | Oculta la versión de Apache, desactiva el listado de directorios y bloquea el acceso directo a `Modulo Documentacion/uploads`. |
| `php/sigsm.ini`     | Zona horaria de Montevideo, límites de subida de archivos, errores solo al log y cookies de sesión más seguras.                |

## Por qué están así

- **Subida de archivos.** La aplicación acepta PDF de hasta 10 MB. PHP está configurado con 12 MB (`upload_max_filesize`) y 14 MB (`post_max_size`) para que el rechazo lo haga la aplicación, con un mensaje claro, y no PHP con un error genérico.
- **Errores.** `display_errors` está apagado: un paciente que provoque un error no debe ver rutas ni consultas. Los errores van al log del contenedor (`docker compose logs app`).
- **Carpeta de uploads.** Los PDF solo se entregan a través de `servir-documento.php`, que controla sesión o token. Apache bloquea el acceso directo por URL.
- **Cookies de sesión.** Llevan `HttpOnly`, `SameSite=Lax` y modo estricto. Falta activar `session.cookie_secure = 1` cuando el sistema funcione solo bajo HTTPS.

## Qué no va acá

Las contraseñas y el token del QR no se escriben en estos archivos. Se definen en el `.env` de la raíz, que no se sube al repositorio.

Los archivos de esta carpeta se copian a la imagen al construirla. Si se modifican, hay que reconstruir:

```bash
docker compose up -d --build
```
