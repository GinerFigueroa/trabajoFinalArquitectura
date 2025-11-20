<?php

class Conexion
{
    // Almacena la única instancia de la clase
    private static $instancia = null;
    // Almacena la conexión mysqli
    private $connection;
    
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbName = 'opipitaltrabajo';

    // 1. Constructor privado: Inicializa la conexión solo una vez
    private function __construct()
    {
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->dbName);
        if ($this->connection->connect_error) {
            die("Conexión fallida: " . $this->connection->connect_error);
        }
        $this->connection->set_charset("utf8");
    }

    // 2. Método estático: Punto de acceso único a la instancia
    public static function getInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new Conexion();
        }
        return self::$instancia;
    }

    // 3. Método para obtener el objeto mysqli
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Cierra la conexión y libera la instancia Singleton.
     * Solo debe llamarse una vez al final de la ejecución del script.
     */
    public function cerrarConexion()
    {
        if ($this->connection) {
            $this->connection->close();
            self::$instancia = null; // Reinicia la instancia por si se necesita otra
        }
    }

    // Métodos para prevenir la clonación y deserialización
    private function __clone() {}
    public function __wakeup() {}
}
?>