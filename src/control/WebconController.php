<?php
/**
 * WebconController - Controlador para conectar con sistema WEBCON
 */

header('Content-Type: application/json');

try {
    // Verificar parámetro tipo
    if (!isset($_GET['tipo'])) {
        throw new Exception('Parámetro "tipo" no especificado');
    }

    // Incluir modelos con rutas absolutas
    require_once __DIR__ . '/../model/WebconClienteModel.php';
    require_once __DIR__ . '/../model/WebconProyectoModel.php';
    
    class WebconController {
        private $clienteModel;
        private $proyectoModel;

        public function __construct() {
            $this->clienteModel = new WebconClienteModel();
            $this->proyectoModel = new WebconProyectoModel();
        }

        public function procesar($tipo) {
            switch($tipo) {
                case 'verificar_conexion':
                    return $this->verificarConexion();
                case 'obtener_clientes':
                    return $this->obtenerClientes();
                case 'obtener_proyectos':
                    return $this->obtenerProyectos();
                case 'buscar_cliente':
                    return $this->buscarCliente();
                default:
                    throw new Exception('Tipo de operación no válido: ' . $tipo);
            }
        }

        private function verificarConexion() {
            try {
                // Verificar conexión a la base de datos
                require_once __DIR__ . '/../library/conexion_webcon.php';
                $conexion = new ConexionWebcon();
                $conexionOk = $conexion->verificarConexion();
                
                if ($conexionOk) {
                    $totalClientes = $this->clienteModel->getTotalClientes();
                    $totalProyectos = $this->proyectoModel->getTotalProyectos();
                    
                    return [
                        'status' => true,
                        'mensaje' => 'Conexión establecida correctamente con WEBCON',
                        'total_clientes' => $totalClientes,
                        'total_proyectos' => $totalProyectos,
                        'debug' => 'Base de datos: inventario'
                    ];
                } else {
                    throw new Exception('No se pudo conectar a la base de datos de WEBCON');
                }
                
            } catch (Exception $e) {
                return [
                    'status' => false,
                    'mensaje' => 'Error de conexión: ' . $e->getMessage(),
                    'total_clientes' => 0,
                    'total_proyectos' => 0
                ];
            }
        }

        private function obtenerClientes() {
            try {
                $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';
                $clientes = $this->clienteModel->getClientes($busqueda);
                
                return [
                    'status' => true,
                    'data' => $clientes,
                    'total' => count($clientes)
                ];
            } catch (Exception $e) {
                return [
                    'status' => false,
                    'mensaje' => 'Error obteniendo clientes: ' . $e->getMessage(),
                    'data' => [],
                    'total' => 0
                ];
            }
        }

        private function obtenerProyectos() {
            try {
                $proyectos = $this->proyectoModel->getProyectos();
                
                return [
                    'status' => true,
                    'data' => $proyectos,
                    'total' => count($proyectos)
                ];
            } catch (Exception $e) {
                return [
                    'status' => false,
                    'mensaje' => 'Error obteniendo proyectos: ' . $e->getMessage(),
                    'data' => [],
                    'total' => 0
                ];
            }
        }

        private function buscarCliente() {
            try {
                $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                if ($id <= 0) {
                    throw new Exception('ID de cliente no válido');
                }
                
                $cliente = $this->clienteModel->getClienteById($id);
                
                if ($cliente) {
                    return [
                        'status' => true,
                        'data' => $cliente
                    ];
                } else {
                    return [
                        'status' => false,
                        'mensaje' => 'Cliente no encontrado'
                    ];
                }
            } catch (Exception $e) {
                return [
                    'status' => false,
                    'mensaje' => 'Error buscando cliente: ' . $e->getMessage()
                ];
            }
        }
    }

    // Ejecutar controlador
    $controller = new WebconController();
    $resultado = $controller->procesar($_GET['tipo']);
    echo json_encode($resultado);

} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'mensaje' => 'Error en el controlador: ' . $e->getMessage()
    ]);
}
?>