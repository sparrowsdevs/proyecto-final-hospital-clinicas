# Uploads

Destino de los PDF que sube el Administrador. Los archivos van dentro de `documentos/`.

## Reglas de esta carpeta

- **No se suben PDF al repositorio.** `.gitignore` excluye todo el contenido de `documentos/` salvo el `.htaccess`. Cada instalación tiene sus propios archivos.
- **El acceso directo por URL está bloqueado.** `documentos/.htaccess` impide ejecutar scripts, y en Docker la configuración de Apache niega todo acceso a esta carpeta. Los archivos solo salen a través de `Controlador/servir-documento.php`, que verifica sesión o token y que el documento esté activo.
- **Los nombres los genera el sistema.** No se usa el nombre original del archivo, para evitar colisiones y nombres maliciosos.

## Con Docker

La carpeta `documentos/` es un volumen (`documentos_pdf`). Los PDF persisten aunque se recreen los contenedores y no forman parte de la imagen. Para respaldarlos, ver el [README principal](../../README.md#95-mantenimiento).

## Permisos en el servidor

Si el sistema se instala sin Docker, el usuario de Apache (`www-data`) necesita permiso de escritura sobre `documentos/`.
