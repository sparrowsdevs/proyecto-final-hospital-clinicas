<?php
/**
 * Creador de contraseña hash!
 * Vas a copiar el hash que te aparezca en este sitio
 * http://localhost/proyecto-final-hospital-clinicas/generar-hash.php
 * Luego ponerlo en la inserción de datos de la tabla usuario || 
 *  EJEMPLO
 * 
 * INSERT INTO usuario (cedula, contrasena, nombre, apellido, email, activo) VALUES
('12345678', 'ACÁ PEGARIAS EL HASH QUE TE APARECE EN EL NAVEGADOR', 'admin', 'apellido', 'ricardo@sparrowsdevs.com', TRUE);

 */


echo password_hash('admin', PASSWORD_DEFAULT);