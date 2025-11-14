<?php
require_once "../library/conexion.php";

class TokenModel {
    private $conexion;
    
    function __construct() {
        $this->conexion = new Conexion();
        $this->conexion = $this->conexion->connect();
    }

    public function obtenerToken() {
        $sql = "SELECT token FROM token_api LIMIT 1";
        $result = $this->conexion->query($sql);
        return $result->fetch_object();
    }

    public function actualizarToken($token) {
        // Sin encriptación - se guarda exactamente como se recibe
        $stmt = $this->conexion->prepare("UPDATE token_api SET token = ?");
        $stmt->bind_param("s", $token);
        return $stmt->execute();
    }
}
?>  