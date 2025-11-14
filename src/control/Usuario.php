<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once('../model/admin-sesionModel.php');
require_once('../model/admin-usuarioModel.php');
require_once('../model/adminModel.php');

require '../../vendor/autoload.php';

// Validar que existe el parámetro tipo
if (!isset($_GET['tipo'])) {
    echo json_encode(array('status' => false, 'msg' => 'Parámetro tipo requerido'));
    exit;
}

$tipo = $_GET['tipo'];

// Instanciar las clases
$objSesion = new SessionModel();
$objUsuario = new UsuarioModel();
$objAdmin = new AdminModel();

// Función para validar sesión
function validarSesion($objSesion) {
    if (!isset($_POST['sesion']) || !isset($_POST['token'])) {
        return false;
    }
    return $objSesion->verificar_sesion_si_activa($_POST['sesion'], $_POST['token']);
}

// Función para enviir respuesta JSON
function enviarRespuesta($datos) {
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit;
}

switch ($tipo) {
    case "validar_datos_reset_password":
        if (!isset($_POST['id']) || !isset($_POST['token'])) {
            enviarRespuesta(array('status' => false, 'msg' => 'Datos incompletos'));
        }
        
        $id_email = $_POST['id'];
        $token_email = $_POST['token'];
        $arr_Respuesta = array('status' => false, 'msg' => 'Link Caducado');
        
        $datos_usuario = $objUsuario->buscarUsuarioById($id_email);
        if ($datos_usuario && $datos_usuario->reset_password == 1 && password_verify($datos_usuario->token_password, $token_email)) {
            $arr_Respuesta = array('status' => true, 'msg' => 'Ok');
        }
        enviarRespuesta($arr_Respuesta);
        break;

    case "actualizar_password_reset":
        if (!isset($_POST['id']) || !isset($_POST['password'])) {
            enviarRespuesta(array('status' => false, 'msg' => 'Datos incompletos'));
        }
        
        $id = $_POST['id'];
        $password = $_POST['password'];
        $pass_secure = password_hash($password, PASSWORD_DEFAULT);
        $arr_Respuesta = array('status' => false, 'msg' => 'Error');
        
        $update = $objUsuario->updateResetPassword($id, "", 0);
        $update = $objUsuario->actualizarPassword($id, $pass_secure);
        
        if ($update) {
            $arr_Respuesta = array('status' => true, 'msg' => 'Contraseña actualizada correctamente');
        }
        enviarRespuesta($arr_Respuesta);
        break;

    case "listar_usuarios_ordenados_tabla":
        $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
        
        if (!validarSesion($objSesion)) {
            enviarRespuesta($arr_Respuesta);
        }
        
        // Validar parámetros requeridos
        if (!isset($_POST['pagina']) || !isset($_POST['cantidad_mostrar'])) {
            enviarRespuesta(array('status' => false, 'msg' => 'Parámetros de paginación requeridos'));
        }
        
        $pagina = intval($_POST['pagina']);
        $cantidad_mostrar = intval($_POST['cantidad_mostrar']);
        $busqueda_tabla_dni = isset($_POST['busqueda_tabla_dni']) ? $_POST['busqueda_tabla_dni'] : '';
        $busqueda_tabla_nomap = isset($_POST['busqueda_tabla_nomap']) ? $_POST['busqueda_tabla_nomap'] : '';
        $busqueda_tabla_estado = isset($_POST['busqueda_tabla_estado']) ? $_POST['busqueda_tabla_estado'] : '';
        
        // Validar valores
        if ($pagina < 1) $pagina = 1;
        if ($cantidad_mostrar < 1) $cantidad_mostrar = 10;
        
        $arr_Respuesta = array('status' => false, 'contenido' => '');
        
        $busqueda_filtro = $objUsuario->buscarUsuariosOrderByApellidosNombres_tabla_filtro(
            $busqueda_tabla_dni, 
            $busqueda_tabla_nomap, 
            $busqueda_tabla_estado
        );
        
        $arr_Usuario = $objUsuario->buscarUsuariosOrderByApellidosNombres_tabla(
            $pagina, 
            $cantidad_mostrar, 
            $busqueda_tabla_dni, 
            $busqueda_tabla_nomap, 
            $busqueda_tabla_estado
        );
        
        $arr_contenido = [];
        if (!empty($arr_Usuario)) {
            foreach ($arr_Usuario as $index => $usuario) {
                $arr_contenido[$index] = (object) [];
                $arr_contenido[$index]->id = $usuario->id;
                $arr_contenido[$index]->dni = $usuario->dni;
                $arr_contenido[$index]->nombres_apellidos = $usuario->nombres_apellidos;
                $arr_contenido[$index]->correo = $usuario->correo;
                $arr_contenido[$index]->telefono = $usuario->telefono;
                $arr_contenido[$index]->estado = $usuario->estado;
                
                $opciones = '<button type="button" title="Editar" class="btn btn-primary btn-sm waves-effect waves-light" data-toggle="modal" data-target=".modal_editar' . $usuario->id . '"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-info btn-sm" title="Resetear Contraseña" onclick="reset_password(' . $usuario->id . ')"><i class="fa fa-key"></i></button>';
                $arr_contenido[$index]->options = $opciones;
            }
            
            $arr_Respuesta['total'] = count($busqueda_filtro);
            $arr_Respuesta['status'] = true;
            $arr_Respuesta['contenido'] = $arr_contenido;
        }
        enviarRespuesta($arr_Respuesta);
        break;

    case "registrar":
        $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
        
        if (!validarSesion($objSesion)) {
            enviarRespuesta($arr_Respuesta);
        }
        
        if (!isset($_POST['dni']) || !isset($_POST['apellidos_nombres']) || !isset($_POST['correo']) || !isset($_POST['telefono'])) {
            enviarRespuesta(array('status' => false, 'mensaje' => 'Datos incompletos'));
        }
        
        $dni = trim($_POST['dni']);
        $apellidos_nombres = trim($_POST['apellidos_nombres']);
        $correo = trim($_POST['correo']);
        $telefono = trim($_POST['telefono']);

        if (empty($dni) || empty($apellidos_nombres) || empty($correo) || empty($telefono)) {
            enviarRespuesta(array('status' => false, 'mensaje' => 'Error, campos vacíos'));
        }
        
        // Validar formato de email
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            enviarRespuesta(array('status' => false, 'mensaje' => 'Formato de correo inválido'));
        }

        $arr_Usuario = $objUsuario->buscarUsuarioByDni($dni);
        if ($arr_Usuario) {
            enviarRespuesta(array('status' => false, 'mensaje' => 'Registro Fallido, Usuario ya se encuentra registrado'));
        }
        
        $id_usuario = $objUsuario->registrarUsuario($dni, $apellidos_nombres, $correo, $telefono);
        if ($id_usuario > 0) {
            enviarRespuesta(array('status' => true, 'mensaje' => 'Registro Exitoso'));
        } else {
            enviarRespuesta(array('status' => false, 'mensaje' => 'Error al registrar usuario'));
        }
        break;

    case "actualizar":
        $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
        
        if (!validarSesion($objSesion)) {
            enviarRespuesta($arr_Respuesta);
        }
        
        if (!isset($_POST['data']) || !isset($_POST['dni']) || !isset($_POST['nombres_apellidos']) || 
            !isset($_POST['correo']) || !isset($_POST['telefono']) || !isset($_POST['estado'])) {
            enviarRespuesta(array('status' => false, 'mensaje' => 'Datos incompletos'));
        }
        
        $id = $_POST['data'];
        $dni = trim($_POST['dni']);
        $nombres_apellidos = trim($_POST['nombres_apellidos']);
        $correo = trim($_POST['correo']);
        $telefono = trim($_POST['telefono']);
        $estado = $_POST['estado'];

        if (empty($id) || empty($dni) || empty($nombres_apellidos) || empty($correo) || empty($telefono) || $estado === "") {
            enviarRespuesta(array('status' => false, 'mensaje' => 'Error, campos vacíos'));
        }
        
        // Validar formato de email
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            enviarRespuesta(array('status' => false, 'mensaje' => 'Formato de correo inválido'));
        }

        $arr_Usuario = $objUsuario->buscarUsuarioByDni($dni);
        if ($arr_Usuario && $arr_Usuario->id != $id) {
            enviarRespuesta(array('status' => false, 'mensaje' => 'DNI ya está registrado por otro usuario'));
        }
        
        $consulta = $objUsuario->actualizarUsuario($id, $dni, $nombres_apellidos, $correo, $telefono, $estado);
        if ($consulta) {
            enviarRespuesta(array('status' => true, 'mensaje' => 'Actualizado Correctamente'));
        } else {
            enviarRespuesta(array('status' => false, 'mensaje' => 'Error al actualizar registro'));
        }
        break;

    case "reiniciar_password":
        $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
        
        if (!validarSesion($objSesion)) {
            enviarRespuesta($arr_Respuesta);
        }
        
        if (!isset($_POST['id'])) {
            enviarRespuesta(array('status' => false, 'mensaje' => 'ID de usuario requerido'));
        }
        
        $id_usuario = $_POST['id'];
        $password = $objAdmin->generar_llave(10);
        $pass_secure = password_hash($password, PASSWORD_DEFAULT);
        $actualizar = $objUsuario->actualizarPassword($id_usuario, $pass_secure);
        
        if ($actualizar) {
            enviarRespuesta(array('status' => true, 'mensaje' => 'Contraseña actualizada correctamente a: ' . $password));
        } else {
            enviarRespuesta(array('status' => false, 'mensaje' => 'Hubo un problema al actualizar la contraseña, intente nuevamente'));
        }
        break;

    case "sent_email_password":
        $arr_Respuesta = array('status' => false, 'msg' => 'Error_Sesion');
        
        if (!validarSesion($objSesion)) {
            enviarRespuesta($arr_Respuesta);
        }
        
        $datos_sesion = $objSesion->buscarSesionLoginById($_POST['sesion']);
        $datos_usuario = $objUsuario->buscarUsuarioById($datos_sesion->id_usuario);
        $llave = $objAdmin->generar_llave(30);
        $token = password_hash($llave, PASSWORD_DEFAULT);
        $update = $objUsuario->updateResetPassword($datos_sesion->id_usuario, $llave, 1);
        
        if ($update) {
            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug = SMTP::DEBUG_OFF;
                $mail->isSMTP();
                $mail->Host       = 'mail.limon-cito.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'inventario_diner@limon-cito.com';
                $mail->Password   = 'diner@2025';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('inventario_diner@limon-cito.com', 'Cambio de Contraseña');
                $mail->addAddress($datos_usuario->correo, $datos_usuario->nombres_apellidos);
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'Cambio de Contraseña - Sistema de Inventario';
                $mail->Body    = '
                    <!DOCTYPE html>
                    <html lang="es">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Correo Empresarial</title>
                        <style>
                            body { margin: 0; padding: 0; background-color: #f4f4f4; }
                            .container { max-width: 600px; margin: auto; background-color: #ffffff; font-family: Arial, sans-serif; color: #333333; border: 1px solid #dddddd; }
                            .header { background-color: #004aad; color: white; padding: 20px; text-align: center; }
                            .content { padding: 30px; }
                            .content h1 { font-size: 22px; margin-bottom: 20px; }
                            .content p { font-size: 16px; line-height: 1.5; }
                            .button { display: inline-block; background-color: #004aad; color: #ffffff !important; padding: 12px 25px; margin: 20px 0; text-decoration: none; border-radius: 4px; }
                            .footer { background-color: #eeeeee; text-align: center; padding: 15px; font-size: 12px; color: #666666; }
                            @media screen and (max-width: 600px) { .content, .header, .footer { padding: 15px !important; } .button { padding: 10px 20px !important; } }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="header"><h2>Sistema de Inventario</h2></div>
                            <div class="content">
                                <h1>Hola ' . $datos_usuario->nombres_apellidos . ',</h1>
                                <p>Te saludamos cordialmente. Para informar sobre la solicitud de cambio de contraseña.</p>
                                <p>¡Si usted no solicito el cambio de contraseña, Contactese con el administrador!</p>
                                <a href="' . BASE_URL . 'reset-password/?data=' . $datos_usuario->id . '&data2=' . urlencode($token) . '" class="button">Cambiar mi Contraseña</a>
                                <p>Gracias por confiar en nosotros.</p>
                            </div>
                            <div class="footer">
                                © 2025 Sistema de Inventario. Todos los derechos reservados.<br>
                                <a href="' . BASE_URL . '">Cancelar suscripción</a>
                            </div>
                        </div>
                    </body>
                    </html>';

                $mail->send();
                enviarRespuesta(array('status' => true, 'msg' => 'Correo enviado correctamente'));
            } catch (Exception $e) {
                enviarRespuesta(array('status' => false, 'msg' => 'Error al enviar correo: ' . $mail->ErrorInfo));
            }
        } else {
            enviarRespuesta(array('status' => false, 'msg' => 'Error al actualizar datos para reset de password'));
        }
        break;

    default:
        enviarRespuesta(array('status' => false, 'msg' => 'Tipo de acción no válido'));
        break;
}
?>