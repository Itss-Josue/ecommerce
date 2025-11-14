<?php
/**
 * apiwebcon.php - Vista TOKEN que consume WEBCON - SISTEMA AUTOMÁTICO
 */

// Cargar configuración directamente
require_once __DIR__ . "/../config/config.php";

// Verificar y definir constantes necesarias
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8888/token/');
}
if (!defined('RUTA_WEBCON')) {
    define('RUTA_WEBCON', 'http://localhost:8888/webcon/');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>API Webcon - TOKEN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="<?php echo BASE_URL ?>src/view/pp/assets/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo BASE_URL ?>src/view/pp/plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet" />
    
    <style>
        body { background-color: #f8f9fa; padding: 20px 0; }
        .card { margin-bottom: 20px; border-radius: 8px; }
        .loading-spinner { display: none; text-align: center; padding: 20px; }
        .status-card { border-left: 4px solid #007bff; }
        .status-webcon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-api { background: linear-gradient(45deg, #28a745, #20c997); border: none; color: white; }
        .badge-webcon { background: linear-gradient(45deg, #6f42c1, #e83e8c); }
    </style>

    <script>
    const base_url = '<?php echo BASE_URL; ?>';
    const RUTA_WEBCON = '<?php echo RUTA_WEBCON; ?>';
    
    // URL CORREGIDA - apunta al controlador dentro del sistema TOKEN
    const API_WEBCON = base_url + 'src/control/WebconController.php';
</script>
</head>

<body>
    <div class="container-fluid">
        <!-- Card de Información -->
        <div class="card status-webcon">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="card-title mb-2 text-white">
                            <i class="fas fa-database mr-2"></i>API de Webcon - SISTEMA AUTOMÁTICO
                        </h4>
                        <p class="card-text text-white-50 mb-0">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Acceso automático a datos de clientes y proyectos - Sistema WEBCON
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="button" class="btn btn-light btn-sm mr-2" onclick="verificarEstadoSistema()">
                            <i class="fas fa-sync-alt mr-1"></i> Verificar Estado
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="irAModuloTokens()">
                            <i class="fas fa-cog mr-1"></i> Gestionar Tokens
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de Estado del Sistema -->
        <div class="card status-card">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-info-circle mr-2"></i>Estado del Sistema WEBCON
                </h5>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-plug fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Conexión WEBCON</h6>
                                <span class="badge badge-success" id="statusConexion">VERIFICANDO...</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-users fa-2x text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Clientes</h6>
                                <span class="badge badge-webcon" id="statusClientes">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-project-diagram fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Proyectos</h6>
                                <span class="badge badge-webcon" id="statusProyectos">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-2">
                    <i class="fas fa-robot mr-2"></i>
                    <strong>Sistema Automático:</strong> Conexión directa a la base de datos de WEBCON. 
                    No requiere validación de tokens externa.
                </div>
            </div>
        </div>

        <!-- Card de Búsqueda de Clientes -->
        <div class="card" id="busquedaClientesSection">
            <div class="card-body">
                <h4 class="card-title mb-3">
                    <i class="fas fa-users mr-2"></i>Gestión de Clientes WEBCON
                </h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="buscarCliente" class="form-label">
                                <strong>Buscar Cliente:</strong>
                            </label>
                            <input type="text" class="form-control" name="buscarCliente" id="buscarCliente" 
                                   placeholder="Nombre, razón social o email...">
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="button" class="btn btn-primary btn-lg waves-effect waves-light w-100" 
                                    onclick="cargarClientes()">
                                <i class="fas fa-sync-alt mr-2"></i> Cargar Todos los Clientes
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-light border">
                            <i class="fas fa-info-circle mr-2 text-primary"></i>
                            <strong>Información:</strong> Los datos se obtienen directamente de la base de datos del sistema WEBCON.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de Resultados Clientes -->
        <div class="card" id="resultadosClientesSection">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-list mr-2"></i>Clientes de WEBCON
                    </h4>
                    <div class="badge badge-secondary" id="contadorClientes">
                        Sistema listo
                    </div>
                </div>

                <!-- Loading -->
                <div class="loading-spinner" id="loadingClientes">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Conectando con WEBCON y cargando clientes...</p>
                </div>

                <!-- Tabla de clientes -->
                <div class="table-responsive">
                    <table class="table table-hover" width="100%" id="tablaClientes">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Razón Social</th>
                                <th>Contacto</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaClientes">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <h5>Haga clic en "Cargar Clientes" para ver los datos</h5>
                                    <p class="text-muted">Los datos se obtienen del sistema WEBCON</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Card de Gestión de Proyectos -->
        <div class="card" id="gestionProyectosSection">
            <div class="card-body">
                <h4 class="card-title mb-3">
                    <i class="fas fa-project-diagram mr-2"></i>Gestión de Proyectos WEBCON
                </h4>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <button type="button" class="btn btn-success btn-lg waves-effect waves-light" 
                                onclick="cargarProyectos()">
                            <i class="fas fa-sync-alt mr-2"></i> Cargar Todos los Proyectos
                        </button>
                    </div>
                </div>

                <!-- Tabla de proyectos -->
                <div class="table-responsive">
                    <table class="table table-hover" width="100%" id="tablaProyectos">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Proyecto</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaProyectos">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-project-diagram fa-3x mb-3"></i>
                                    <h5>Haga clic en "Cargar Proyectos" para ver los datos</h5>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL ?>src/view/pp/assets/js/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>src/view/pp/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>src/view/pp/plugins/sweetalert2/sweetalert2.min.js"></script>
    
    <!-- JS para API WEBCON -->
    <script src="<?php echo BASE_URL ?>src/view/js/functions_webcon.js"></script>

    <script>
    // Inicialización del sistema WEBCON
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 API Webcon - Sistema Automático Iniciado');
        console.log('📡 Conectando a WEBCON:', RUTA_WEBCON);
        
        // Verificar estado inicial del sistema
        setTimeout(verificarEstadoSistema, 1000);
    });

    // Función para verificar estado del sistema
    // Función para verificar estado del sistema
async function verificarEstadoSistema() {
    try {
        document.getElementById('statusConexion').className = 'badge badge-warning';
        document.getElementById('statusConexion').textContent = 'VERIFICANDO...';
        
        console.log('🔍 Verificando conexión con:', API_WEBCON + '?tipo=verificar_conexion');
        
        // Verificar conexión con WEBCON
        const respuesta = await fetch(API_WEBCON + '?tipo=verificar_conexion');
        
        console.log('📨 Respuesta recibida, status:', respuesta.status);
        
        if (!respuesta.ok) {
            throw new Error(`HTTP ${respuesta.status}: ${respuesta.statusText}`);
        }
        
        const data = await respuesta.json();
        console.log('📊 Datos recibidos:', data);
        
        if (data.status) {
            document.getElementById('statusConexion').className = 'badge badge-success';
            document.getElementById('statusConexion').textContent = 'CONECTADO';
            
            document.getElementById('statusClientes').textContent = data.total_clientes + ' clientes';
            document.getElementById('statusProyectos').textContent = data.total_proyectos + ' proyectos';
            
            Swal.fire({
                title: 'Conexión Exitosa',
                html: `<div class="text-left">
                        <p class="mb-2"><strong>✅ Conexión establecida con WEBCON</strong></p>
                        <p class="mb-1"><strong>📊 Base de datos:</strong> ${data.debug || 'inventario'}</p>
                        <p class="mb-1"><strong>👥 Clientes:</strong> ${data.total_clientes} registrados</p>
                        <p class="mb-0"><strong>📁 Proyectos:</strong> ${data.total_proyectos} registrados</p>
                       </div>`,
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            throw new Error(data.mensaje || 'Error en la respuesta del servidor');
        }
    } catch (error) {
        console.error('❌ Error verificando conexión:', error);
        document.getElementById('statusConexion').className = 'badge badge-danger';
        document.getElementById('statusConexion').textContent = 'ERROR';
        
        Swal.fire({
            title: 'Error de Conexión',
            html: `<div class="text-left">
                    <p class="mb-2"><strong>No se pudo conectar con WEBCON</strong></p>
                    <p class="mb-1"><strong>Error:</strong> ${error.message}</p>
                    <p class="mb-0 text-muted">Verifique que el controlador esté funcionando</p>
                   </div>`,
            icon: 'error',
            confirmButtonText: 'Reintentar'
        }).then(() => {
            verificarEstadoSistema();
        });
    }
}

    // Función para ir al módulo de tokens
    function irAModuloTokens() {
        window.location.href = base_url + 'token';
    }
    </script>
</body>
</html>