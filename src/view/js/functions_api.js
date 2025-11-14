// functions_api.js - SISTEMA CON MANEJO COMPLETO DE CORS

// ========== CONSTANTES ==========
const API_URL = API_SIRE2;
const TOKEN_VALIDATION_URL = RUTA_API_SIRE2 + 'src/control/TokenController.php?tipo=validar_token_api';

// ========== FUNCIÓN MEJORADA: Obtener token automáticamente ==========
async function obtenerToken() {
    try {
        // 1. Intentar desde localStorage
        const tokenLocalStorage = localStorage.getItem('api_token_sire2');
        if (tokenLocalStorage && tokenLocalStorage.trim() !== '') {
            console.log('✅ Token obtenido de localStorage');
            return tokenLocalStorage.trim();
        }
        
        // 2. Intentar obtener del sistema TOKEN automáticamente
        console.log('🔄 Obteniendo token del sistema TOKEN...');
        const tokenObtenerURL = base_url + 'src/control/TokenController.php?tipo=obtener_token_publico';
        
        const response = await fetch(tokenObtenerURL, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.status && data.token) {
            console.log('✅ Token obtenido del sistema TOKEN:', data.token.substring(0, 10) + '...');
            // Almacenar para futuras consultas
            localStorage.setItem('api_token_sire2', data.token.trim());
            return data.token.trim();
        } else {
            throw new Error(data.msg || 'No se pudo obtener token');
        }
        
    } catch (error) {
        console.warn('⚠️ No se pudo obtener token automáticamente:', error);
        
        // 3. Token por defecto (fallback)
        const tokenDefault = 'd6ba9ab2704f1380-2';
        console.log('🔄 Usando token por defecto');
        return tokenDefault;
    }
}

// ========== FUNCIÓN MEJORADA: Validar token con manejo CORS robusto ==========
async function validarTokenConSIRE2(token) {
    if (!token || token.trim() === '') {
        return {
            valido: false,
            mensaje: 'No se encontró ningún token válido',
            errorCORS: false
        };
    }

    try {
        console.log('🔐 Enviando token a:', TOKEN_VALIDATION_URL);
        
        // Crear FormData para enviar como multipart/form-data
        const formData = new FormData();
        formData.append('token', token.trim());
        
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 segundos timeout
        
        const response = await fetch(TOKEN_VALIDATION_URL, {
            method: 'POST',
            body: formData,
            mode: 'cors',
            credentials: 'omit',
            signal: controller.signal,
            headers: {
                'Accept': 'application/json'
            }
        });
        
        clearTimeout(timeoutId);
        
        // Verificar si la respuesta es OK
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Error HTTP:', response.status, errorText);
            throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📨 Respuesta del servidor:', data);
        return data;
        
    } catch (error) {
        console.error('❌ Error validando token:', error);
        
        // Detectar específicamente errores de CORS
        if (error.name === 'TypeError' && (
            error.message.includes('Failed to fetch') || 
            error.message.includes('NetworkError') ||
            error.message.includes('Load failed')
        )) {
            return {
                valido: false,
                mensaje: 'Error de conexión CORS. El servidor no permite peticiones desde este origen.',
                errorCORS: true,
                errorDetalle: error.message
            };
        }
        
        // Detectar timeout
        if (error.name === 'AbortError') {
            return {
                valido: false,
                mensaje: 'Timeout: El servidor no respondió en 10 segundos',
                errorCORS: false,
                errorDetalle: 'Timeout'
            };
        }
        
        return {
            valido: false,
            mensaje: `Error de conexión: ${error.message}`,
            errorCORS: false,
            errorDetalle: error.message
        };
    }
}

// ========== FUNCIÓN MEJORADA: Mostrar error con diagnóstico ==========
function mostrarErrorToken(mensaje, errorCORS = false, errorDetalle = '') {
    if (errorCORS) {
        Swal.fire({
            icon: 'error',
            title: 'Error de Conexión CORS',
            html: `<div class="text-left">
                    <p class="mb-2"><strong>No se pudo conectar con el servidor SIRE2</strong></p>
                    <div class="alert alert-warning mt-2">
                        <strong>Problema de Configuración:</strong> 
                        <ul class="mb-0 pl-3">
                            <li>El servidor SIRE2 no permite peticiones desde localhost</li>
                            <li>Faltan headers CORS en el servidor</li>
                            <li>Posible bloqueo por el navegador</li>
                        </ul>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Detalle técnico:</strong> ${errorDetalle}
                        </small>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary btn-sm mr-2" onclick="probarConexionManual()">
                            <i class="fas fa-plug mr-1"></i> Probar Conexión Manual
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="irAModuloTokens()">
                            <i class="fas fa-cog mr-1"></i> Configurar Token Local
                        </button>
                    </div>
                  </div>`,
            confirmButtonText: 'Entendido',
            confirmButtonClass: 'btn btn-primary',
            width: '600px'
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error de Autenticación',
            html: `<div class="text-left">
                    <p class="mb-2">${mensaje}</p>
                    ${errorDetalle ? `<small class="text-muted"><strong>Detalle:</strong> ${errorDetalle}</small>` : ''}
                    <div class="mt-3">
                        <button class="btn btn-primary btn-sm" onclick="irAModuloTokens()">
                            <i class="fas fa-key mr-1"></i> Configurar Token
                        </button>
                    </div>
                  </div>`,
            confirmButtonText: 'Entendido',
            confirmButtonClass: 'btn btn-primary'
        });
    }
}

// ========== FUNCIONES AUXILIARES NUEVAS ==========
async function probarConexionManual() {
    const loadingAlert = Swal.fire({
        title: 'Probando Conexión...',
        html: `<div class="text-left">
                <p class="mb-1">🔍 Probando conexión con: ${TOKEN_VALIDATION_URL}</p>
                <p class="mb-0">⏳ Esto puede tomar unos segundos...</p>
               </div>`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        // Probamos una petición simple OPTIONS primero (preflight)
        const response = await fetch(TOKEN_VALIDATION_URL, {
            method: 'OPTIONS',
            mode: 'cors'
        });

        await loadingAlert.close();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Conexión OPTIONS Exitosa',
                html: `<div class="text-left">
                        <p class="mb-2">✅ El servidor acepta peticiones CORS</p>
                        <p class="mb-1"><strong>Headers CORS encontrados:</strong></p>
                        <ul class="mb-0">
                            <li>Access-Control-Allow-Origin: ${response.headers.get('Access-Control-Allow-Origin') || 'No'}</li>
                            <li>Access-Control-Allow-Methods: ${response.headers.get('Access-Control-Allow-Methods') || 'No'}</li>
                        </ul>
                       </div>`,
                confirmButtonText: 'Continuar'
            });
        } else {
            throw new Error(`OPTIONS failed: ${response.status}`);
        }
    } catch (error) {
        await loadingAlert.close();
        
        Swal.fire({
            icon: 'error',
            title: 'Error de Conexión',
            html: `<div class="text-left">
                    <p class="mb-2">❌ No se pudo establecer conexión con el servidor</p>
                    <p class="mb-1"><strong>Error:</strong> ${error.message}</p>
                    <div class="alert alert-danger mt-2">
                        <strong>Acción requerida:</strong> Contacte al administrador del SIRE2 para configurar los headers CORS.
                    </div>
                   </div>`,
            confirmButtonText: 'Entendido'
        });
    }
}

// ========== FUNCIONES PRINCIPALES CON MEJOR MANEJO ==========
async function ejecutarBusqueda() {
    try {
        const token = await obtenerToken();
        
        const loadingAlert = Swal.fire({
            title: 'Procesando Búsqueda...',
            html: `<div class="text-left">
                    <p class="mb-2">🔐 <strong>Obteniendo token automáticamente</strong></p>
                    <p class="mb-2">🌐 <strong>Conectando con SIRE2...</strong></p>
                    <p class="mb-0">✅ <strong>Validando acceso...</strong></p>
                   </div>`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const validacion = await validarTokenConSIRE2(token);
        
        await loadingAlert.close();
        
        if (!validacion.valido) {
            mostrarErrorToken(
                validacion.mensaje || validacion.error, 
                validacion.errorCORS, 
                validacion.errorDetalle
            );
            return;
        }

        await mostrarConfirmacionTokenValido(validacion);
        await ejecutarBusquedaOriginal(token);
        
    } catch (error) {
        console.error('Error en ejecutarBusqueda:', error);
        mostrarErrorToken('Error inesperado: ' + error.message, false, error.stack);
    }
}

async function buscarPorDNI() {
    try {
        const token = await obtenerToken();
        
        const loadingAlert = Swal.fire({
            title: 'Procesando Búsqueda...',
            html: `<div class="text-left">
                    <p class="mb-2">🔐 <strong>Obteniendo token automáticamente</strong></p>
                    <p class="mb-2">🌐 <strong>Conectando con SIRE2...</strong></p>
                    <p class="mb-0">✅ <strong>Validando acceso...</strong></p>
                   </div>`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const validacion = await validarTokenConSIRE2(token);
        
        await loadingAlert.close();
        
        if (!validacion.valido) {
            mostrarErrorToken(
                validacion.mensaje || validacion.error, 
                validacion.errorCORS, 
                validacion.errorDetalle
            );
            return;
        }

        await mostrarConfirmacionTokenValido(validacion);
        await buscarPorDNIOriginal(token);
        
    } catch (error) {
        console.error('Error en buscarPorDNI:', error);
        mostrarErrorToken('Error inesperado: ' + error.message, false, error.stack);
    }
}

// ========== RESTANTE DEL CÓDIGO (SIN CAMBIOS) ==========
async function mostrarConfirmacionTokenValido(validacion) {
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

function irAModuloTokens() {
    window.location.href = base_url + 'src/view/token.php';
}

// ... (las funciones ejecutarBusquedaOriginal, buscarPorDNIOriginal y demás se mantienen igual)

<<<<<<< HEAD
// ========== FUNCIONES PRINCIPALES MODIFICADAS ==========
async function ejecutarBusqueda() {
    const token = await obtenerToken();
    
    // Mostrar loading de validación
    const loadingAlert = Swal.fire({
        title: 'Validando Acceso...',
        text: 'Obteniendo y verificando token automáticamente',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Validar token antes de proceder
    const validacion = await validarTokenConSIRE2(token);
    
    await loadingAlert.close();
    
    if (!validacion.valido) {
        mostrarErrorToken(validacion.mensaje || validacion.error);
        return;
    }

    // Si token es válido, mostrar confirmación y proceder
    await mostrarConfirmacionTokenValido(validacion);
    await ejecutarBusquedaOriginal(token);
}

async function buscarPorDNI() {
    const token = await obtenerToken();
    
    const loadingAlert = Swal.fire({
        title: 'Validando Acceso...',
        text: 'Obteniendo y verificando token automáticamente',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const validacion = await validarTokenConSIRE2(token);
    
    await loadingAlert.close();
    
    if (!validacion.valido) {
        mostrarErrorToken(validacion.mensaje || validacion.error);
        return;
    }

    await mostrarConfirmacionTokenValido(validacion);
    await buscarPorDNIOriginal(token);
}

// ========== FUNCIONES AUXILIARES ==========
async function mostrarConfirmacionTokenValido(validacion) {
    // Mostrar confirmación rápida solo la primera vez
    const tokenUsado = sessionStorage.getItem('token_confirmado');
    if (!tokenUsado) {
        await Swal.fire({
            icon: 'success',
            title: 'Token Válido',
            html: `<div class="text-left">
                    <p class="mb-1"><strong>Cliente:</strong> ${validacion.cliente.razon_social}</p>
                    <p class="mb-0"><strong>Token verificado automáticamente</strong></p>
                   </div>`,
            timer: 1500,
            showConfirmButton: false
        });
        sessionStorage.setItem('token_confirmado', 'true');
    }
}

function irAModuloTokens() {
    window.location.href = base_url + 'src/view/token.php';
}

=======
>>>>>>> 26df3c031386474b0b67401a38a6c7b9cba7864d
// ========== FUNCIONES DE BÚSQUEDA ORIGINALES MODIFICADAS ==========
async function ejecutarBusquedaOriginal(token) {
    const dni = document.getElementById('dni').value;
    const nombres = document.getElementById('nombres').value;
    const pagina = document.getElementById('pagina').value;
    const limite = document.getElementById('limite').value;

    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('cuerpoTabla').innerHTML = '';

    const formData = new FormData();
    formData.append('dni', dni);
    formData.append('nombres', nombres);
    formData.append('pagina', pagina);
    formData.append('limite', limite);
    formData.append('token', token);

    try {
        const response = await fetch(API_URL + '?tipo=buscar_estudiantes', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        document.getElementById('loadingSpinner').style.display = 'none';
        
        if (data.status) {
            mostrarResultadosEnTabla(data);
            if (data.paginacion) {
                actualizarPaginacion(data.paginacion);
            }
        } else {
            mostrarResultadosEnTabla({status: false});
        }
    } catch (error) {
        document.getElementById('loadingSpinner').style.display = 'none';
        console.error('Error de conexión con SIRE2:', error);
        mostrarResultadosEnTabla({status: false});
    }
}

async function buscarPorDNIOriginal(token) {
    const dni = document.getElementById('dni').value;
    
    if (!dni) {
        mostrarResultadosEnTabla({status: false, data: []});
        return;
    }

    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('cuerpoTabla').innerHTML = '';

    const formData = new FormData();
    formData.append('dni', dni);
    formData.append('token', token);

    try {
        const response = await fetch(API_URL + '?tipo=buscar_estudiante_dni', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        document.getElementById('loadingSpinner').style.display = 'none';
        
        if (data.status) {
            mostrarResultadosEnTabla({
                status: true,
                data: [data.data]
            });
            document.getElementById('pagina').value = 1;
            actualizarPaginacion({
                pagina_actual: 1,
                limite: 1,
                total_estudiantes: 1,
                total_paginas: 1
            });
        } else {
            mostrarResultadosEnTabla({status: false});
        }
    } catch (error) {
        document.getElementById('loadingSpinner').style.display = 'none';
        console.error('Error de conexión con SIRE2:', error);
        mostrarResultadosEnTabla({status: false});
    }
}

// ========== FUNCIONES DE UI (SIN CAMBIOS) ==========
function mostrarResultadosEnTabla(data) {
    const cuerpoTabla = document.getElementById('cuerpoTabla');
    const contadorResultados = document.getElementById('contadorResultados');
    const controlesResultados = document.getElementById('controlesResultados');
    
    if (!data.status || !data.data || data.data.length === 0) {
        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-5">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <h5>No se encontraron estudiantes en SIRE2</h5>
                    <p class="text-muted">Intente con otros criterios de búsqueda</p>
                </td>
            </tr>
        `;
        contadorResultados.textContent = '0 estudiantes encontrados';
        controlesResultados.style.display = 'none';
        return;
    }

    let html = '';
    data.data.forEach(estudiante => {
        const programa = estudiante.programa_nombre || `Programa ${estudiante.programa_id}`;
        
        html += `
            <tr>
                <td><strong>${estudiante.dni}</strong></td>
                <td>
                    <div class="font-weight-bold">${estudiante.nombre_completo}</div>
                    <small class="text-muted">${estudiante.nombres}</small>
                </td>
                <td>
                    <span class="badge badge-info">${programa}</span>
                    <small class="d-block text-muted">ID: ${estudiante.programa_id}</small>
                </td>
                <td>
                    <span class="badge badge-primary">${estudiante.semestre}° Semestre</span>
                </td>
                <td>
                    <span class="badge ${estudiante.estado === 'activo' ? 'badge-success' : 'badge-warning'}">
                        ${estudiante.estado}
                    </span>
                </td>
                <td>
                    <small class="text-muted">${estudiante.fecha_matricula || 'No registrada'}</small>
                </td>
            </tr>
        `;
    });

    cuerpoTabla.innerHTML = html;
    contadorResultados.textContent = `${data.data.length} estudiante(s) encontrado(s) en SIRE2`;
    controlesResultados.style.display = 'flex';
}

// ... (las funciones restantes se mantienen igual que antes)
function actualizarPaginacion(paginacion) {
    const textoPaginacion = document.getElementById('texto_paginacion_tabla');
    const listaPaginacion = document.getElementById('lista_paginacion_tabla');
    
    if (!paginacion) {
        textoPaginacion.innerHTML = 'Ingrese criterios de búsqueda';
        listaPaginacion.innerHTML = '<li class="page-item disabled"><span class="page-link">-</span></li>';
        return;
    }
    
    const { pagina_actual, limite, total_estudiantes, total_paginas } = paginacion;
    
    const inicio = ((pagina_actual - 1) * limite) + 1;
    const fin = Math.min(pagina_actual * limite, total_estudiantes);
    
    textoPaginacion.innerHTML = total_estudiantes > 0 
        ? `Mostrando ${inicio} a ${fin} de ${total_estudiantes} registros`
        : 'No hay registros para mostrar';
    
    let html = '';
    
    if (pagina_actual > 1) {
        html += `<li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${pagina_actual - 1})">Anterior</a>
                 </li>`;
    }
    
    for (let i = 1; i <= total_paginas; i++) {
        if (i === pagina_actual) {
            html += `<li class="page-item active">
                        <span class="page-link">${i}</span>
                     </li>`;
        } else {
            html += `<li class="page-item">
                        <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${i})">${i}</a>
                     </li>`;
        }
    }
    
    if (pagina_actual < total_paginas) {
        html += `<li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="cambiarPagina(${pagina_actual + 1})">Siguiente</a>
                 </li>`;
    }
    
    listaPaginacion.innerHTML = html || '<li class="page-item disabled"><span class="page-link">-</span></li>';
}

function cambiarPagina(pagina) {
    document.getElementById('pagina').value = pagina;
    ejecutarBusqueda();
}

function limpiarFiltros() {
    document.getElementById('dni').value = '';
    document.getElementById('nombres').value = '';
    document.getElementById('pagina').value = 1;
    
    const cuerpoTabla = document.getElementById('cuerpoTabla');
    cuerpoTabla.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted py-5">
                <i class="fas fa-search fa-3x mb-3"></i>
                <h5>Realice una búsqueda para ver los resultados</h5>
                <p class="text-muted">Los datos se consumen directamente de SIRE2</p>
            </td>
        </tr>
    `;
    
    document.getElementById('contadorResultados').textContent = 'Esperando búsqueda...';
    document.getElementById('texto_paginacion_tabla').innerHTML = 'Ingrese criterios de búsqueda';
    document.getElementById('lista_paginacion_tabla').innerHTML = '<li class="page-item disabled"><span class="page-link">-</span></li>';
    document.getElementById('controlesResultados').style.display = 'none';
}

function exportarJSON() {
    console.log('Exportar JSON desde TOKEN');
}

function mostrarJSON() {
    console.log('Mostrar JSON desde TOKEN');
}