// functions_webcon.js - Sistema de conexión con WEBCON

// ========== FUNCIONES PRINCIPALES ==========
async function cargarClientes() {
    const loading = document.getElementById('loadingClientes');
    const cuerpoTabla = document.getElementById('cuerpoTablaClientes');
    const contador = document.getElementById('contadorClientes');
    
    loading.style.display = 'block';
    cuerpoTabla.innerHTML = '';

    try {
        const formData = new FormData();
        const busqueda = document.getElementById('buscarCliente').value;
        if (busqueda) {
            formData.append('busqueda', busqueda);
        }

        console.log('🔍 Buscando clientes...');
        const response = await fetch(API_WEBCON + '?tipo=obtener_clientes', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📊 Clientes recibidos:', data);
        
        loading.style.display = 'none';
        
        if (data.status && data.data) {
            mostrarClientesEnTabla(data.data);
            contador.textContent = `${data.total} cliente(s) encontrado(s)`;
            
            Swal.fire({
                title: 'Clientes Cargados',
                text: `Se encontraron ${data.total} clientes`,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            mostrarClientesEnTabla([]);
            contador.textContent = '0 clientes encontrados';
            Swal.fire('Información', data.mensaje || 'No se encontraron clientes', 'info');
        }
    } catch (error) {
        loading.style.display = 'none';
        console.error('❌ Error cargando clientes:', error);
        mostrarClientesEnTabla([]);
        contador.textContent = 'Error de conexión';
        Swal.fire('Error', 'No se pudo conectar con WEBCON: ' + error.message, 'error');
    }
}

async function cargarProyectos() {
    const cuerpoTabla = document.getElementById('cuerpoTablaProyectos');
    
    // Mostrar loading en la tabla
    cuerpoTabla.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Cargando proyectos desde WEBCON...</p>
            </td>
        </tr>
    `;

    try {
        console.log('🔍 Cargando proyectos...');
        const response = await fetch(API_WEBCON + '?tipo=obtener_proyectos', {
            method: 'POST'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📊 Proyectos recibidos:', data);
        
        if (data.status && data.data) {
            mostrarProyectosEnTabla(data.data);
            Swal.fire({
                title: 'Proyectos Cargados',
                text: `Se cargaron ${data.total} proyectos`,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            mostrarProyectosEnTabla([]);
            Swal.fire('Información', data.mensaje || 'No se encontraron proyectos', 'info');
        }
    } catch (error) {
        console.error('❌ Error cargando proyectos:', error);
        mostrarProyectosEnTabla([]);
        Swal.fire('Error', 'No se pudo conectar con WEBCON: ' + error.message, 'error');
    }
}

// ========== FUNCIONES DE UI MEJORADAS ==========
function mostrarClientesEnTabla(clientes) {
    const cuerpoTabla = document.getElementById('cuerpoTablaClientes');
    
    if (!clientes || clientes.length === 0) {
        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="fas fa-users fa-3x mb-3"></i>
                    <h5>No se encontraron clientes</h5>
                    <p class="text-muted">Intente con otros criterios de búsqueda</p>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    clientes.forEach(cliente => {
        // Determinar nombre a mostrar
        const nombre = cliente.razon_social || cliente.nombre || cliente.nombres || 'N/A';
        const email = cliente.email || 'No disponible';
        const telefono = cliente.telefono || 'No disponible';
        const contacto = cliente.contacto || 'No disponible';
        const estado = cliente.estado || 'activo';
        const estadoBadge = estado === 'activo' ? 'success' : 
                           estado === 'inactivo' ? 'secondary' : 'warning';
        
        html += `
            <tr>
                <td><strong>#${cliente.id}</strong></td>
                <td>
                    <div class="font-weight-bold">${nombre}</div>
                    ${cliente.ruc ? `<small class="text-muted">RUC: ${cliente.ruc}</small>` : ''}
                    ${cliente.dni ? `<small class="text-muted d-block">DNI: ${cliente.dni}</small>` : ''}
                </td>
                <td>${contacto}</td>
                <td>${email}</td>
                <td>${telefono}</td>
                <td>
                    <span class="badge badge-${estadoBadge}">${estado}</span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalleCliente(${cliente.id})" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info ml-1" onclick="verProyectosCliente(${cliente.id})" title="Proyectos">
                        <i class="fas fa-project-diagram"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    cuerpoTabla.innerHTML = html;
}

function mostrarProyectosEnTabla(proyectos) {
    const cuerpoTabla = document.getElementById('cuerpoTablaProyectos');
    
    if (!proyectos || proyectos.length === 0) {
        cuerpoTabla.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="fas fa-project-diagram fa-3x mb-3"></i>
                    <h5>No se encontraron proyectos</h5>
                    <p class="text-muted">No hay proyectos registrados en el sistema</p>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    proyectos.forEach(proyecto => {
        const nombre = proyecto.nombre || proyecto.titulo || 'Proyecto sin nombre';
        const descripcion = proyecto.descripcion || '';
        const clienteNombre = proyecto.cliente_nombre || proyecto.razon_social || 'Cliente no asignado';
        const estado = proyecto.estado || 'activo';
        const estadoBadge = estado === 'completado' ? 'success' : 
                           estado === 'en_progreso' ? 'primary' : 
                           estado === 'pendiente' ? 'warning' : 'secondary';
        
        const fechaInicio = proyecto.fecha_inicio || proyecto.created_at || 'No definida';
        const fechaFin = proyecto.fecha_fin || proyecto.fecha_limite || 'No definida';
        
        html += `
            <tr>
                <td><strong>#${proyecto.id}</strong></td>
                <td>
                    <div class="font-weight-bold">${nombre}</div>
                    <small class="text-muted">${descripcion}</small>
                </td>
                <td>${clienteNombre}</td>
                <td>
                    <span class="badge badge-${estadoBadge}">${estado}</span>
                </td>
                <td><small>${fechaInicio}</small></td>
                <td><small>${fechaFin}</small></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalleProyecto(${proyecto.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    cuerpoTabla.innerHTML = html;
}

// ========== FUNCIONES AUXILIARES ==========
function verDetalleCliente(id) {
    Swal.fire({
        title: 'Cargando...',
        text: 'Obteniendo información del cliente',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = new FormData();
    formData.append('id', id);

    fetch(API_WEBCON + '?tipo=buscar_cliente', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if (data.status && data.data) {
            const cliente = data.data;
            const nombre = cliente.razon_social || cliente.nombre || cliente.nombres || 'N/A';
            
            let detallesHTML = `
                <div class="text-left">
                    <p><strong>ID:</strong> ${cliente.id}</p>
                    <p><strong>Nombre/Razón Social:</strong> ${nombre}</p>
            `;
            
            if (cliente.ruc) detallesHTML += `<p><strong>RUC:</strong> ${cliente.ruc}</p>`;
            if (cliente.dni) detallesHTML += `<p><strong>DNI:</strong> ${cliente.dni}</p>`;
            if (cliente.contacto) detallesHTML += `<p><strong>Contacto:</strong> ${cliente.contacto}</p>`;
            if (cliente.email) detallesHTML += `<p><strong>Email:</strong> ${cliente.email}</p>`;
            if (cliente.telefono) detallesHTML += `<p><strong>Teléfono:</strong> ${cliente.telefono}</p>`;
            if (cliente.direccion) detallesHTML += `<p><strong>Dirección:</strong> ${cliente.direccion}</p>`;
            
            detallesHTML += `
                    <p><strong>Estado:</strong> <span class="badge badge-${cliente.estado === 'activo' ? 'success' : 'secondary'}">${cliente.estado || 'activo'}</span></p>
                </div>
            `;
            
            Swal.fire({
                title: `Cliente #${cliente.id}`,
                html: detallesHTML,
                confirmButtonText: 'Cerrar',
                width: '600px'
            });
        } else {
            Swal.fire('Error', data.mensaje || 'No se pudo cargar el cliente', 'error');
        }
    })
    .catch(error => {
        Swal.close();
        Swal.fire('Error', 'Error de conexión: ' + error.message, 'error');
    });
}

function verProyectosCliente(clienteId) {
    // Por ahora, mostrar mensaje informativo
    Swal.fire({
        title: 'Proyectos del Cliente',
        html: `<div class="text-left">
                <p>Esta funcionalidad mostrará todos los proyectos asociados al cliente #${clienteId}.</p>
                <p class="text-muted"><small>Función en desarrollo - Próximamente</small></p>
               </div>`,
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}

function verDetalleProyecto(proyectoId) {
    Swal.fire({
        title: 'Detalle de Proyecto',
        html: `<div class="text-left">
                <p>Visualizando detalles del proyecto #${proyectoId}</p>
                <p class="text-muted"><small>Función en desarrollo - Próximamente podrá ver todos los detalles del proyecto</small></p>
               </div>`,
        icon: 'info',
        confirmButtonText: 'Cerrar'
    });
}

// ========== EVENT LISTENERS ==========
document.addEventListener('DOMContentLoaded', function() {
    // Buscar clientes al presionar Enter en el campo de búsqueda
    const buscarClienteInput = document.getElementById('buscarCliente');
    if (buscarClienteInput) {
        buscarClienteInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                cargarClientes();
            }
        });
    }
    
    console.log('✅ Functions Webcon cargadas correctamente');
    console.log('🔗 API WEBCON:', API_WEBCON);
});