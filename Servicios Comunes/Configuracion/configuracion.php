<?php
/*
 * IMPORTANTE (al desplegar):
 *  1. URL_BASE_PUBLICA: dirección con la que los celulares de los pacientes
 *     llegan al servidor, SIN barra final y hasta la carpeta del proyecto.
 *     Ejemplo: 'http://192.168.1.50/proyecto-final-hospital-clinicas'
 *     Si queda vacía, se usa el host desde el que el personal abre el panel
 *     (no sirve si se accede por "localhost": el celular no puede abrirlo).
 *  2. TOKEN_ACCESO_PUBLICO: clave secreta incluida en el QR. Quien no la tenga
 *     no puede ver el repositorio público. Para generar una nueva (y anular
 *     los QR impresos anteriormente):
 * Ambos valores pueden definirse como variables de entorno
 * (SIGSM_URL_BASE y SIGSM_TOKEN) para no dejarlos escritos en el código.
 */

define('URL_BASE_PUBLICA', getenv('SIGSM_URL_BASE') ?: '');

define('TOKEN_ACCESO_PUBLICO', getenv('SIGSM_TOKEN') ?: '6d1acfde25895d1b1b93acab13862a31a7ed6a2a38b18192');
