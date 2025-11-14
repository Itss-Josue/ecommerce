<?php
session_start();
require_once('../model/admin-sesionModel.php');
require_once('../model/TokenModel.php');

class TokenController {
    private $objSesion;
    private $objToken;
    
    public function __construct() {
        $this->objSesion = new SessionModel();
        $this->objToken = new TokenModel();
    }
    
    private function validateSession($id_sesion, $token_sesion) {
        return $this->objSesion->verificar_sesion_si_activa($id_sesion, $token_sesion);
    }
    
    private function jsonResponse($data) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public function handleRequest() {
        $tipo = $_GET['tipo'] ?? '';
        
        // ========== NUEVO: Endpoint público para obtener token ==========
        if ($tipo === "obtener_token_publico") {
            $this->obtenerTokenPublico();
            return;
        }
        
        $id_sesion = $_POST['sesion'] ?? '';
        $token_sesion = $_POST['token_sesion'] ?? '';
        
        if (!$this->validateSession($id_sesion, $token_sesion)) {
            $this->jsonResponse(['status' => false, 'msg' => 'Error_Sesion']);
        }
        
        switch($tipo) {
            case "obtener":
                $this->obtenerToken();
                break;
            case "actualizar":
                $this->actualizarToken();
                break;
            default:
                $this->jsonResponse(['status' => false, 'msg' => 'Tipo no válido']);
        }
    }
    
    // ========== NUEVO MÉTODO: Obtener token sin autenticación ==========
    private function obtenerTokenPublico() {
        try {
            $token = $this->objToken->obtenerToken();
            
            if ($token && !empty($token->token)) {
                $this->jsonResponse([
                    'status' => true,
                    'token' => $token->token,
                    'mensaje' => 'Token obtenido correctamente'
                ]);
            } else {
                $this->jsonResponse([
                    'status' => false, 
                    'msg' => 'No se encontró token configurado'
                ]);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'status' => false, 
                'msg' => 'Error interno del servidor'
            ]);
        }
    }
    
    // ========== MÉTODOS EXISTENTES ==========
    private function obtenerToken() {
        try {
            $token = $this->objToken->obtenerToken();
            
            if ($token) {
                $this->jsonResponse([
                    'status' => true,
                    'token' => $token->token
                ]);
            } else {
                $this->jsonResponse(['status' => false, 'msg' => 'No se encontró token']);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['status' => false, 'msg' => 'Error interno']);
        }
    }
    
    private function actualizarToken() {
        try {
            $nuevo_token = trim($_POST['token_api'] ?? '');
            
            if (empty($nuevo_token)) {
                $this->jsonResponse(['status' => false, 'mensaje' => 'Token no puede estar vacío']);
            }
            
            $success = $this->objToken->actualizarToken($nuevo_token);
            if ($success) {
                $this->jsonResponse(['status' => true, 'mensaje' => 'Token actualizado correctamente']);
            } else {
                $this->jsonResponse(['status' => false, 'mensaje' => 'Error al actualizar token']);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['status' => false, 'mensaje' => 'Error interno']);
        }
    }
}

$controller = new TokenController();
$controller->handleRequest();
?>