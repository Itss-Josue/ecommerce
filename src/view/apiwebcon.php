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
    <title>API Webcon - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="<?php echo BASE_URL ?>src/view/pp/assets/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo BASE_URL ?>src/view/pp/plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1f2937;
            --light: #f8fafc;
            --gray: #6b7280;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 16px;
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .icon-primary { background: linear-gradient(135deg, var(--primary), #818cf8); }
        .icon-success { background: linear-gradient(135deg, var(--secondary), #34d399); }
        .icon-warning { background: linear-gradient(135deg, var(--warning), #fbbf24); }
        
        .btn-modern {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
            color: white;
        }
        
        .btn-success-modern {
            background: linear-gradient(135deg, var(--secondary), #059669);
        }
        
        .table-modern {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .table-modern thead {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }
        
        .table-modern th {
            border: none;
            padding: 1rem;
            font-weight: 500;
        }
        
        .table-modern td {
            padding: 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }
        
        .badge-modern {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .search-box {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 2px solid #f1f5f9;
            transition: all 0.3s ease;
        }
        
        .search-box:focus-within {
            border-color: var(--primary);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.15);
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 3rem;
        }
        
        .spinner-modern {
            width: 50px;
            height: 50px;
            border: 4px solid #f1f5f9;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .nav-tabs-modern {
            border-bottom: 2px solid #f1f5f9;
        }
        
        .nav-tabs-modern .nav-link {
            border: none;
            padding: 1rem 2rem;
            font-weight: 500;
            color: var(--gray);
            border-radius: 0;
            position: relative;
        }
        
        .nav-tabs-modern .nav-link.active {
            color: var(--primary);
            background: transparent;
        }
        
        .nav-tabs-modern .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 3px 3px 0 0;
        }
        
        .floating-action {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }
    </style>

    <script>
    const base_url = '<?php echo BASE_URL; ?>';
    const RUTA_WEBCON = '<?php echo RUTA_WEBCON; ?>';
    const API_WEBCON = base_url + 'src/control/WebconController.php';
    </script>
</head>

<body>
    <div class="container-fluid">
        <!-- Header del Dashboard -->
        <div class="dashboard-header glass-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 mb-2 fw-bold">
                        <i class="fas fa-rocket me-3"></i>API Webcon Dashboard
                    </h1>
                    <p class="lead mb-0 opacity-90">
                        Sistema de integración automática - Conexión directa con WEBCON
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <button class="btn btn-light btn-modern me-2" onclick="verificarEstadoSistema()">
                            <i class="fas fa-sync-alt me-2"></i>Verificar Estado
                        </button>
                        <button class="btn btn-outline-light" onclick="irAModuloTokens()">
                            <i class="fas fa-cog me-2"></i>Gestionar Tokens
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas del Sistema -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card glass-card">
                    <div class="stat-icon icon-primary">
                        <i class="fas fa-plug text-white"></i>
                    </div>
                    <h5 class="text-gray-600 mb-1">Estado Conexión</h5>
                    <div class="d-flex align-items-center">
                        <span class="badge-modern bg-warning text-dark" id="statusConexion">VERIFICANDO</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="stat-card glass-card">
                    <div class="stat-icon icon-success">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <h5 class="text-gray-600 mb-1">Total Clientes</h5>
                    <h3 class="mb-0 fw-bold text-dark" id="statusClientes">-</h3>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="stat-card glass-card">
                    <div class="stat-icon icon-warning">
                        <i class="fas fa-project-diagram text-white"></i>
                    </div>
                    <h5 class="text-gray-600 mb-1">Total Proyectos</h5>
                    <h3 class="mb-0 fw-bold text-dark" id="statusProyectos">-</h3>
                </div>
            </div>
        </div>

        <!-- Navegación por Pestañas -->
        <div class="glass-card mb-4">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-modern" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="clientes-tab" data-bs-toggle="tab" 
                                data-bs-target="#clientes" type="button" role="tab">
                            <i class="fas fa-users me-2"></i>Gestión de Clientes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="proyectos-tab" data-bs-toggle="tab" 
                                data-bs-target="#proyectos" type="button" role="tab">
                            <i class="fas fa-project-diagram me-2"></i>Gestión de Proyectos
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content mt-4" id="myTabContent">
                    <!-- Pestaña Clientes -->
                    <div class="tab-pane fade show active" id="clientes" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="search-box">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-0">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </span>
                                        <input type="text" class="form-control border-0" 
                                               id="buscarCliente" placeholder="Buscar cliente por nombre, razón social o email...">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-modern w-100 h-100" onclick="cargarClientes()">
                                    <i class="fas fa-sync-alt me-2"></i>Cargar Clientes
                                </button>
                            </div>
                        </div>

                        <!-- Loading -->
                        <div class="loading-spinner glass-card" id="loadingClientes">
                            <div class="spinner-modern"></div>
                            <h5 class="text-muted mt-3">Conectando con WEBCON</h5>
                            <p class="text-muted">Cargando información de clientes...</p>
                        </div>

                        <!-- Tabla de Clientes -->
                        <div class="table-responsive">
                            <table class="table table-modern" id="tablaClientes">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                        <th><i class="fas fa-building me-2"></i>Razón Social</th>
                                        <th><i class="fas fa-user me-2"></i>Contacto</th>
                                        <th><i class="fas fa-envelope me-2"></i>Email</th>
                                        <th><i class="fas fa-phone me-2"></i>Teléfono</th>
                                        <th><i class="fas fa-circle me-2"></i>Estado</th>
                                        <th><i class="fas fa-actions me-2"></i>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaClientes">
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                                                <h5>No hay datos cargados</h5>
                                                <p>Haga clic en "Cargar Clientes" para ver la información</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pestaña Proyectos -->
                    <div class="tab-pane fade" id="proyectos" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <button class="btn btn-success-modern btn-modern" onclick="cargarProyectos()">
                                    <i class="fas fa-sync-alt me-2"></i>Cargar Todos los Proyectos
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de Proyectos -->
                        <div class="table-responsive">
                            <table class="table table-modern" id="tablaProyectos">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                        <th><i class="fas fa-tasks me-2"></i>Proyecto</th>
                                        <th><i class="fas fa-building me-2"></i>Cliente</th>
                                        <th><i class="fas fa-circle me-2"></i>Estado</th>
                                        <th><i class="fas fa-play me-2"></i>Inicio</th>
                                        <th><i class="fas fa-flag-checkered me-2"></i>Fin</th>
                                        <th><i class="fas fa-actions me-2"></i>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaProyectos">
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-project-diagram fa-3x mb-3 opacity-50"></i>
                                                <h5>No hay datos cargados</h5>
                                                <p>Haga clic en "Cargar Proyectos" para ver la información</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón Flotante de Acción -->
    <div class="floating-action">
        <button class="btn btn-modern btn-lg rounded-circle shadow-lg" 
                onclick="verificarEstadoSistema()" 
                title="Verificar Estado del Sistema">
            <i class="fas fa-bolt"></i>
        </button>
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
        console.log('🚀 API Webcon - Dashboard Moderno Iniciado');
        console.log('📡 Conectando a WEBCON:', RUTA_WEBCON);
        
        // Verificar estado inicial del sistema
        setTimeout(verificarEstadoSistema, 1000);
        
        // Inicializar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

    // Función para verificar estado del sistema
    async function verificarEstadoSistema() {
        try {
            document.getElementById('statusConexion').className = 'badge-modern bg-warning text-dark';
            document.getElementById('statusConexion').textContent = 'VERIFICANDO...';
            
            console.log('🔍 Verificando conexión con:', API_WEBCON + '?tipo=verificar_conexion');
            
            const respuesta = await fetch(API_WEBCON + '?tipo=verificar_conexion');
            
            console.log('📨 Respuesta recibida, status:', respuesta.status);
            
            if (!respuesta.ok) {
                throw new Error(`HTTP ${respuesta.status}: ${respuesta.statusText}`);
            }
            
            const data = await respuesta.json();
            console.log('📊 Datos recibidos:', data);
            
            if (data.status) {
                document.getElementById('statusConexion').className = 'badge-modern bg-success';
                document.getElementById('statusConexion').textContent = 'CONECTADO';
                
                document.getElementById('statusClientes').textContent = data.total_clientes;
                document.getElementById('statusProyectos').textContent = data.total_proyectos;
                
                Swal.fire({
                    title: '✅ Conexión Exitosa',
                    html: `<div class="text-start">
                            <p class="mb-3"><strong>Sistema WEBCON conectado correctamente</strong></p>
                            <div class="row">
                                <div class="col-6">
                                    <strong>👥 Clientes:</strong><br>
                                    <span class="fs-4">${data.total_clientes}</span>
                                </div>
                                <div class="col-6">
                                    <strong>📁 Proyectos:</strong><br>
                                    <span class="fs-4">${data.total_proyectos}</span>
                                </div>
                            </div>
                           </div>`,
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false,
                    background: 'var(--light)',
                    customClass: {
                        popup: 'glass-card'
                    }
                });
            } else {
                throw new Error(data.mensaje || 'Error en la respuesta del servidor');
            }
        } catch (error) {
            console.error('❌ Error verificando conexión:', error);
            document.getElementById('statusConexion').className = 'badge-modern bg-danger';
            document.getElementById('statusConexion').textContent = 'ERROR';
            
            Swal.fire({
                title: '❌ Error de Conexión',
                html: `<div class="text-start">
                        <p class="mb-2"><strong>No se pudo conectar con WEBCON</strong></p>
                        <p class="mb-3"><strong>Error:</strong> ${error.message}</p>
                        <p class="text-muted small">Verifique que el controlador esté funcionando correctamente</p>
                       </div>`,
                icon: 'error',
                confirmButtonText: 'Reintentar',
                background: 'var(--light)',
                customClass: {
                    popup: 'glass-card'
                }
            }).then(() => {
                verificarEstadoSistema();
            });
        }
    }

    // Función para ir al módulo de tokens
    function irAModuloTokens() {
        window.location.href = base_url + 'token';
    }
    
    // Función para buscar en tiempo real
    document.getElementById('buscarCliente').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#cuerpoTablaClientes tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    </script>
</body>
</html>