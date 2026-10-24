<?php
/*
 * Provee una única instancia de conexión PDO a la base de datos MySQL.
 * reutilizable por todos los módulos del sistema (Documentación, Ambulancias, Encuestas).
 *
 * Patrón: Singleton
 */

class Conexion
{
    // --- Datos de conexión ---
    private static string $host = 'localhost';
    private static string $puerto = '3306';
    private static string $baseDatos = 'database_clinicas';
    private static string $usuario = 'root';
    private static string $contrasena = '';
    private static string $charset = 'utf8mb4';

    private static ?PDO $instancia = null;

    private function __construct() {}
    private function __clone() {}

    public static function obtenerConexion(): PDO
    {
        if (self::$instancia === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                self::$host, self::$puerto, self::$baseDatos, self::$charset
            );

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instancia = new PDO($dsn, self::$usuario, self::$contrasena, $opciones);
            } catch (PDOException $error) {
                error_log('Error de conexión a la base de datos: ' . $error->getMessage());
                throw new PDOException('No fue posible conectar con la base de datos.');
            }
        }

        return self::$instancia;
    }
}