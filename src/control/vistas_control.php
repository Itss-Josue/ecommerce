<?php
class vistasControlador{
    
    public function obtenerPlantillaControlador(){
        require_once "./src/view/plantilla.php";
    }
    
    public function obtenerVistaControlador(){
        if(isset($_GET['views'])){
            $ruta = explode("/", $_GET['views']);
            $respuesta = $this->obtenerVista($ruta[0]);
        }else{
            // Vista por defecto
            if(isset($_SESSION['sesion_id']) && isset($_SESSION['sesion_token'])){
                $respuesta = "inicio";
            }else{
                $respuesta = "login";
            }
        }
        return $respuesta;
    }
    
    private function obtenerVista($vista){
        // Lista de vistas permitidas
        $vistasPermitidas = [
            "inicio", "login", "token", "usuarios", "nuevo-usuario", 
            "reset-password", "404", "apiestudiante", "apiwebcon"
        ];
        
        if(in_array($vista, $vistasPermitidas)){
            if($vista == "inicio" || $vista == "token" || $vista == "usuarios" || $vista == "nuevo-usuario"){
                return "./src/view/" . $vista . ".php";
            }else if($vista == "apiestudiante" || $vista == "apiwebcon"){
                return "./src/view/" . $vista . ".php";
            }else{
                return "./src/view/" . $vista . ".php";
            }
        }else{
            return "./src/view/404.php";
        }
    }
}