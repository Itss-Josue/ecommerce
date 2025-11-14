<?php
/**
 * apiestudiante.php - Vista TOKEN que consume SIRE2 - SISTEMA AUTOMÁTICO
 */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>API Estudiantes - TOKEN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="<?php echo BASE_URL ?>src/view/pp/assets/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo BASE_URL ?>src/view/pp/plugins/sweetalert2/sweetalert2.min.css" rel="stylesheet" />
    
    <style>
        body { background-color: #f8f9fa; padding: 20px 0; }
        .card { margin-bottom: 20px; border-radius: 8px; }
        .loading-spinner { display: none; text-align: center; padding: 20px; }
        .status-card { border-left: 4px solid #007bff; }
        .status-automatico { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-api { background: linear-gradient(45deg, #28a745, #20c997); border: none; color: white; }
    </style>

    <script>
        const base_url = '<?php echo BASE_URL; ?>';
        const base_url_server = '<?php echo BASE_URL_SERVER; ?>';
        const RUTA_API_SIRE2 = '<?php echo RUTA_API; ?>';
        
        // URL del API de SIRE2 que vamos a consumir
        const API_SIRE2 = RUTA_API_SIRE2 + 'src/control/ApiController.php';
    </script>
</head>

<body>
    <div class="container-fluid">
        <!-- Card de Información -->
        <div class="card status-automatico">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="card-title mb-2 text-white">
                            <i class="fas fa-rocket mr-2"></i>API de Estudiantes - SISTEMA AUTOMÁTICO
                        </h4>
                        <p class="card-text text-white-50 mb-0">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Validación automática de tokens - Consumiendo datos de SIRE2
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="button" class="btn btn-light btn-sm mr-2" onclick="verificarEstadoToken()">
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
                    <i class="fas fa-info-circle mr-2"></i>Estado del Sistema
                </h5>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-key fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Token de Acceso</h6>
                                <span class="badge badge-success" id="statusToken">AUTOMÁTICO</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-database fa-2x text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Conexión SIRE2</h6>
                                <span class="badge badge-success" id="statusConexion">ACTIVA</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-bolt fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Modo de Operación</h6>
                                <span class="badge badge-info">SIN INTERVENCIÓN</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-2">
                    <i class="fas fa-robot mr-2"></i>
                    <strong>Sistema Automático:</strong> El token se obtiene y valida automáticamente. 
                    No es necesario ingresarlo manualmente.
                </div>
            </div>
        </div>

        <!-- Card de Búsqueda -->
        <div class="card" id="busquedaSection">
            <div class="card-body">
                <h4 class="card-title mb-3">
                    <i class="fas fa-search mr-2"></i>Búsqueda de Estudiantes
                </h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="dni" class="form-label">
                                <strong>DNI del Estudiante:</strong>
                            </label>
                            <input type="text" class="form-control" name="dni" id="dni" 
                                   placeholder="Ej: 41664487" maxlength="8"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <small class="form-text text-muted">Solo números, 8 dígitos - Búsqueda exacta</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="nombres" class="form-label">
                                <strong>Nombre del Estudiante:</strong>
                            </label>
                            <input type="text" class="form-control" name="nombres" id="nombres" 
                                   placeholder="Ej: Jesus Ordoñez">
                            <small class="form-text text-muted">Nombre, apellido paterno o materno - Búsqueda general</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="limite" class="form-label">
                                <strong>Resultados por página:</strong>
                            </label>
                            <select class="form-control" name="limite" id="limite">
                                <option value="10">10 resultados</option>
                                <option value="20" selected>20 resultados</option>
                                <option value="50">50 resultados</option>
                                <option value="100">100 resultados</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-8 mb-3 d-flex align-items-end">
                        <div class="alert alert-light border w-100">
                            <i class="fas fa-lightbulb mr-2 text-warning"></i>
                            <strong>Sugerencia:</strong> Use DNI para búsqueda exacta o Nombre para búsqueda general. 
                            El sistema validará automáticamente su acceso.
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0 text-center">
                    <button type="button" class="btn btn-api btn-lg waves-effect waves-light mr-3" onclick="ejecutarBusqueda()">
                        <i class="fas fa-search mr-2"></i> Buscar Estudiantes
                    </button>
                    <button type="button" class="btn btn-success btn-lg waves-effect waves-light mr-3" onclick="buscarPorDNI()">
                        <i class="fas fa-id-card mr-2"></i> Buscar por DNI
                    </button>
                    <button type="button" class="btn btn-outline-secondary waves-effect waves-light" onclick="limpiarFiltros()">
                        <i class="fas fa-refresh mr-2"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Card de Resultados -->
        <div class="card" id="resultadosSection">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-list mr-2"></i>Resultados de Búsqueda
                    </h4>
                    <div class="badge badge-secondary" id="contadorResultados">
                        Sistema listo - Ingrese criterios de búsqueda
                    </div>
                </div>
                
                <!-- Controles de paginación superior -->
                <div id="filtros_tabla_header" class="form-group row align-items-center justify-content-between m-0 mb-3 p-0">
                    <input type="hidden" id="pagina" value="1">
                    
                    <div class="d-flex align-items-center">
                        <label for="limite" class="mb-0 mr-2">Mostrar</label>
                        <select name="limite" id="limite" class="form-control form-control-sm" style="width: auto;" 
                                onchange="document.getElementById('pagina').value=1; ejecutarBusqueda();">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <label for="limite" class="mb-0 ml-2">registros</label>
                    </div>
                </div>

                <!-- Loading -->
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Obteniendo token y buscando estudiantes en SIRE2...</p>
                </div>

                <!-- Controles de exportación -->
                <div class="d-flex justify-content-between align-items-center mb-3" id="controlesResultados" style="display: none;">
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm mr-2" onclick="exportarJSON()" id="btnExportar">
                            <i class="fas fa-download mr-1"></i> Exportar JSON
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="mostrarJSON()" id="btnVerJSON">
                            <i class="fas fa-code mr-1"></i> Ver JSON
                        </button>
                    </div>
                    <div class="text-muted small" id="infoPaginacion">-</div>
                </div>

                <!-- Tabla de resultados -->
                <div class="table-responsive">
                    <table class="table table-hover" width="100%" id="tablaResultados">
                        <thead>
                            <tr>
                                <th>DNI</th>
                                <th>Nombre Completo</th>
                                <th>Programa</th>
                                <th>Semestre</th>
                                <th>Estado</th>
                                <th>Matrícula</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTabla">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <h5>Realice una búsqueda para ver los resultados</h5>
                                    <p class="text-muted">
                                        <i class="fas fa-key mr-1"></i>
                                        El sistema obtendrá automáticamente el token y consultará SIRE2
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación inferior -->
                <div id="filtros_tabla_footer" class="form-group row align-items-center justify-content-between m-0 mt-3 p-0">
                    <div id="texto_paginacion_tabla" class="text-muted">
                        Ingrese criterios de búsqueda
                    </div>
                    <div id="paginacion_tabla">
                        <ul class="pagination justify-content-end mb-0" id="lista_paginacion_tabla">
                            <li class="page-item disabled">
                                <span class="page-link">-</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL ?>src/view/pp/assets/js/jquery.min.js"></script>
    <script src="<?php echo BASE_URL ?>src/view/pp/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?>src/view/pp/plugins/sweetalert2/sweetalert2.min.js"></script>
    
    <!-- JS modificado para sistema automático -->
    <script src="<?php echo BASE_URL ?>src/view/js/functions_api.js"></script>

    <script>
    // Inicialización del sistema automático
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 API Estudiantes - Sistema Automático Iniciado');
        console.log('🔐 Validación automática de tokens activada');
        console.log('📡 Consumiendo de SIRE2:', RUTA_API_SIRE2);
        
        // Verificar estado inicial del token
        setTimeout(verificarEstadoToken, 1000);
    });

    // Función para verificar estado del token
    async function verificarEstadoToken() {
        try {
            const token = await obtenerToken();
            const validacion = await validarTokenConSIRE2(token);
            
            if (validacion.valido) {
                document.getElementById('statusToken').className = 'badge badge-success';
                document.getElementById('statusToken').textContent = 'VÁLIDO';
                
                // Mostrar notificación de estado
                Swal.fire({
                    icon: 'success',
                    title: 'Sistema Listo',
                    html: `<div class="text-left">
                            <p class="mb-2"><strong>Token verificado correctamente</strong></p>
                            <p class="mb-1"><strong>Cliente:</strong> ${validacion.cliente.razon_social}</p>
                            <p class="mb-0"><strong>Estado:</strong> Sistema automático activado</p>
                           </div>`,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                document.getElementById('statusToken').className = 'badge badge-danger';
                document.getElementById('statusToken').textContent = 'INVÁLIDO';
            }
        } catch (error) {
            console.error('Error verificando estado:', error);
            document.getElementById('statusToken').className = 'badge badge-warning';
            document.getElementById('statusToken').textContent = 'ERROR';
        }
    }

    // Función para ir al módulo de tokens
    function irAModuloTokens() {
        window.location.href = base_url + 'src/view/token.php';
    }

    // Sobrescribir función de mostrarConfirmacionTokenValido para mejor UX
    const originalMostrarConfirmacionTokenValido = window.mostrarConfirmacionTokenValido;
    window.mostrarConfirmacionTokenValido = async function(validacion) {
        // Solo mostrar la primera vez por sesión
        const confirmacionMostrada = sessionStorage.getItem('confirmacion_token_mostrada');
        if (!confirmacionMostrada) {
            await Swal.fire({
                icon: 'success',
                title: 'Acceso Verificado',
                html: `<div class="text-left">
                        <p class="mb-2"><strong>Token validado automáticamente</strong></p>
                        <p class="mb-1"><strong>Cliente:</strong> ${validacion.cliente.razon_social}</p>
                        <p class="mb-0"><strong>Procediendo con la búsqueda...</strong></p>
                       </div>`,
                timer: 1500,
                showConfirmButton: false
            });
            sessionStorage.setItem('confirmacion_token_mostrada', 'true');
        }
    }

    // Mejorar mensaje de loading durante búsqueda
    const originalEjecutarBusqueda = window.ejecutarBusqueda;
    window.ejecutarBusqueda = async function() {
        const loadingAlert = Swal.fire({
            title: 'Procesando Búsqueda...',
            html: `<div class="text-left">
                    <p class="mb-2">🔐 <strong>Obteniendo token automáticamente</strong></p>
                    <p class="mb-2">✅ <strong>Validando acceso con SIRE2</strong></p>
                    <p class="mb-0">🔍 <strong>Buscando estudiantes...</strong></p>
                   </div>`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        await originalEjecutarBusqueda();
        await loadingAlert.close();
    }
    </script>
</body>
</html>