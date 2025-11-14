// Cargar token al iniciar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarTokenActual();
    verificarTokenAlmacenado();
});

function cargarTokenActual() {
    // Primero validar sesión
    const sesion = localStorage.getItem('sesion_id');
    const tokenSesion = localStorage.getItem('sesion_token');
    
    if (!sesion || !tokenSesion) {
        window.location.href = BASE_URL + '?views=login';
        return;
    }
    
    // Obtener token de la API
    fetch(BASE_URL + 'src/control/TokenController.php?tipo=obtener', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `sesion=${sesion}&token_sesion=${tokenSesion}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            document.getElementById('tokenInput').value = data.token;


            tokenOriginal = data.token;
            
            // ========== NUEVO: Almacenar token automáticamente ==========
            almacenarTokenAutomatico(data.token);
            mostrarMensaje('Token cargado y almacenado automáticamente', 'success');
        } else {
            mostrarMensaje('Error al cargar token: ' + data.msg, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión', 'danger');
    });
}

function habilitarEdicion() {
    const tokenInput = document.getElementById('tokenInput');
    tokenInput.readOnly = false;
    tokenInput.focus();
    document.getElementById('botonesGuardar').style.display = 'block';
}

function cancelarEdicion() {
    const tokenInput = document.getElementById('tokenInput');
    tokenInput.readOnly = true;
    document.getElementById('botonesGuardar').style.display = 'none';
    // Recargar valor original
    cargarTokenActual();
}

function guardarToken() {
    const nuevoToken = document.getElementById('tokenInput').value.trim();
    const sesion = localStorage.getItem('sesion_id');
    const tokenSesion = localStorage.getItem('sesion_token');
    
    if (!nuevoToken) {
        mostrarMensaje('El token no puede estar vacío', 'warning');
        return;
    }
    
    fetch(BASE_URL + 'src/control/TokenController.php?tipo=actualizar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `sesion=${sesion}&token_sesion=${tokenSesion}&token_api=${encodeURIComponent(nuevoToken)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            mostrarMensaje(data.mensaje, 'success');
            tokenInput.readOnly = true;
            document.getElementById('botonesGuardar').style.display = 'none';
            
            // ========== NUEVO: Almacenar nuevo token automáticamente ==========
            almacenarTokenAutomatico(nuevoToken);
        } else {
            mostrarMensaje(data.mensaje, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión', 'danger');
    });
}

function copiarToken() {
    const tokenInput = document.getElementById('tokenInput');
    tokenInput.select();
    document.execCommand('copy');
    mostrarMensaje('Token copiado al portapapeles', 'success');
}


function mostrarMensaje(mensaje, tipo) {
    const mensajeDiv = document.getElementById('mensaje');
    mensajeDiv.innerHTML = `
        <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            ${mensaje}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
        const alert = mensajeDiv.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);

// ========== NUEVAS FUNCIONES PARA SISTEMA AUTOMÁTICO ==========

// Función para almacenar token automáticamente
function almacenarTokenAutomatico(token) {
    if (token && token.trim() !== '') {
        // Almacenar en localStorage (persistente)
        localStorage.setItem('api_token_sire2', token.trim());
        console.log('✅ Token almacenado automáticamente:', token.trim());
        
        // Actualizar indicador visual
        actualizarIndicadorToken();
    }
}

// Función para verificar token almacenado
function verificarTokenAlmacenado() {
    const tokenAlmacenado = localStorage.getItem('api_token_sire2');
    if (tokenAlmacenado) {
        console.log('🔐 Token almacenado encontrado:', tokenAlmacenado);
        actualizarIndicadorToken();
    }
}

// Función para actualizar indicador visual
function actualizarIndicadorToken() {
    const tokenAlmacenado = localStorage.getItem('api_token_sire2');
    let indicador = document.getElementById('indicadorToken');
    
    if (!indicador) {
        indicador = document.createElement('div');
        indicador.id = 'indicadorToken';
        indicador.className = 'alert alert-info mt-3';
        document.querySelector('.card-body').appendChild(indicador);
    }
    
    if (tokenAlmacenado) {
        indicador.innerHTML = `
            <i class="fas fa-key mr-2"></i>
            <strong>Token Activo:</strong> Almacenado para uso automático en API
            <button class="btn btn-outline-primary btn-sm ml-2" onclick="irAlAPI()">
                <i class="fas fa-external-link-alt mr-1"></i> Ir al API
            </button>
            <button class="btn btn-outline-danger btn-sm ml-1" onclick="eliminarTokenAlmacenado()">
                <i class="fas fa-trash mr-1"></i> Eliminar
            </button>
        `;
    } else {
        indicador.innerHTML = `
            <i class="fas fa-key mr-2"></i>
            <strong>Token No Almacenado:</strong> El token no está disponible para uso automático
        `;
    }
}

// Función para ir al API
function irAlAPI() {
    window.open(base_url + 'src/view/apiestudiante.php', '_blank');
}
// Función para eliminar token almacenado
function eliminarTokenAlmacenado() {
    localStorage.removeItem('api_token_sire2');
    console.log('🗑️ Token almacenado eliminado');
    actualizarIndicadorToken();
}
}