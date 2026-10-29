<?php

class Conexion
{
    // --- Datos de conexión ---
    private static string $host = 'localhost';
    private static string $puerto = '3306';
    private static string $baseDatos = 'database_clinicas';
    private static string $usuario = 'root';
    private static string $contrasena = 'mysql';
    private static string $charset = 'utf8mb4';

    private static ?PDO $instancia = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Datos de conexión. Si existen las variables de entorno SIGSM_DB_* (Docker /
     * servidor) tienen prioridad; si no, se usan los valores de arriba (XAMPP local).
     */
    private static function configuracion(): array
    {
        $leer = static function (string $nombre, string $porDefecto): string {
            $valor = getenv($nombre);
            return ($valor === false || $valor === '') ? $porDefecto : $valor;
        };
        // La contraseña puede ser legítimamente vacía: solo se ignora si la variable no existe
        $contrasena = getenv('SIGSM_DB_PASS');

        return [
            'host' => $leer('SIGSM_DB_HOST', self::$host),
            'puerto' => $leer('SIGSM_DB_PORT', self::$puerto),
            'baseDatos' => $leer('SIGSM_DB_NAME', self::$baseDatos),
            'usuario' => $leer('SIGSM_DB_USER', self::$usuario),
            'contrasena' => $contrasena === false ? self::$contrasena : $contrasena,
        ];
    }

    public static function obtenerConexion(): PDO
    {
        if (self::$instancia === null) {
            $config = self::configuracion();

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'], $config['puerto'], $config['baseDatos'], self::$charset
            );

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instancia = new PDO($dsn, $config['usuario'], $config['contrasena'], $opciones);
            } catch (PDOException $error) {
                error_log('Error de conexión a la base de datos: ' . $error->getMessage());
                throw new PDOException('No fue posible conectar con la base de datos.');
            }
        }

        return self::$instancia;
    }
}