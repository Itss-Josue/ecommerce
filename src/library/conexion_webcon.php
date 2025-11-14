<?php
/**
 * Conexión a la base de datos del sistema WEBCON
 */

class ConexionWebcon {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = 'root'; // Contraseña de MAMP
    private $db   = 'inventario'; // Base de datos de WEBCON
    private $conn;

    public function conectar() {
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
            
            if ($this->conn->connect_error) {
                throw new Exception("Error conectando a Webcon DB: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8");
            return $this->conn;
            
        } catch (Exception $e) {
            error_log("Error ConexionWebcon: " . $e->getMessage());
            throw $e;
        }
    }

    public function desconectar() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // Método para verificar la conexión
    public function verificarConexion() {
        try {
            $conn = $this->conectar();
            $result = $conn->query("SELECT 1 as test");
            $this->desconectar();
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>