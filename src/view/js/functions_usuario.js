function numero_pagina(pagina) {
    document.getElementById('pagina').value = pagina;
    listar_usuariosOrdenados();
}

async function listar_usuariosOrdenados() {
    try {
        mostrarPopupCarga();
        
        console.log('=== INICIANDO CARGA DE USUARIOS ===');
        
        // Validar y obtener parámetros
        let pagina = parseInt(document.getElementById('pagina').value) || 1;
        let cantidad_mostrar = parseInt(document.getElementById('cantidad_mostrar').value) || 10;
        let busqueda_tabla_dni = document.getElementById('busqueda_tabla_dni').value.trim();
        let busqueda_tabla_nomap = document.getElementById('busqueda_tabla_nomap').value.trim();
        let busqueda_tabla_estado = document.getElementById('busqueda_tabla_estado').value;
        
        // Validaciones
        if (pagina < 1) pagina = 1;
        if (cantidad_mostrar < 1) cantidad_mostrar = 10;
        
        // Guardar filtros
        document.getElementById('filtro_dni').value = busqueda_tabla_dni;
        document.getElementById('filtro_nomap').value = busqueda_tabla_nomap;
        document.getElementById('filtro_estado').value = busqueda_tabla_estado;

        // Preparar datos
        const formData = new FormData();
        formData.append('pagina', pagina.toString());
        formData.append('cantidad_mostrar', cantidad_mostrar.toString());
        formData.append('busqueda_tabla_dni', busqueda_tabla_dni);
        formData.append('busqueda_tabla_nomap', busqueda_tabla_nomap);
        formData.append('busqueda_tabla_estado', busqueda_tabla_estado);
        formData.append('sesion', session_session);
        formData.append('token', token_token);
        
        console.log('Enviando petición con datos:', {
            pagina, cantidad_mostrar, busqueda_tabla_dni, busqueda_tabla_nomap, busqueda_tabla_estado
        });
        
        // Enviar petición
        let respuesta = await fetch(base_url_server + 'src/control/Usuario.php?tipo=listar_usuarios_ordenados_tabla', {
            method: 'POST',
            cache: 'no-cache',
            body: formData
        });

        if (!respuesta.ok) {
            throw new Error('Error en la respuesta del servidor: ' + respuesta.status);
        }

        let json = await respuesta.json();
        console.log('Respuesta del servidor:', json);
        
        // Limpiar contenido anterior
        document.getElementById('tablas').innerHTML = `
            <table class="table table-striped table-bordered dt-responsive" width="100%">
                <thead class="thead-dark">
                    <tr>
                        <th>Nro</th>
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="contenido_tabla">
                </tbody>
            </table>`;
        document.querySelector('#modals_editar').innerHTML = ``;
        
        if (json.status && json.contenido && json.contenido.length > 0) {
            let datos = json.contenido;
            console.log('Datos recibidos:', datos);
            
            // Limpiar tabla antes de agregar nuevos datos
            document.querySelector('#contenido_tabla').innerHTML = '';
            
            datos.forEach((item, index) => {
                generarfilastablaUsuarios(item, index);
            });
            
            // Actualizar paginación
            let paginacion = generar_paginacion(json.total, cantidad_mostrar, pagina);
            let texto_paginacion = generar_texto_paginacion(json.total, cantidad_mostrar, pagina);
            document.getElementById('texto_paginacion_tabla').innerHTML = texto_paginacion;
            document.getElementById('lista_paginacion_tabla').innerHTML = paginacion;
            
        } else if (json.msg == "Error_Sesion") {
            alerta_sesion();
        } else {
            document.getElementById('tablas').innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="fa fa-info-circle"></i> No se encontraron usuarios registrados
                </div>`;
            document.getElementById('texto_paginacion_tabla').innerHTML = '';
            document.getElementById('lista_paginacion_tabla').innerHTML = '';
        }
        
    } catch (error) {
        console.error("Error al cargar usuarios:", error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar los usuarios: ' + error.message,
            confirmButtonClass: 'btn btn-confirm mt-2'
        });
    } finally {
        ocultarPopupCarga();
    }
}

function generarfilastablaUsuarios(item, index) {
    let cont = index + 1;
    
    let nueva_fila = document.createElement("tr");
    nueva_fila.id = "fila" + item.id;
    nueva_fila.className = "filas_tabla";

    let activo_si = "";
    let activo_no = "";
    let estado_texto = "";
    let estado_badge = "";
    
    if (item.estado == 1) {
        estado_texto = "ACTIVO";
        estado_badge = "<span class='badge badge-success'>ACTIVO</span>";
        activo_si = "selected";
    } else {
        estado_texto = "INACTIVO";
        estado_badge = "<span class='badge badge-danger'>INACTIVO</span>";
        activo_no = "selected";
    }

    // Asegurar que los datos existan
    let dni = item.dni || 'No registrado';
    let nombres_apellidos = item.nombres_apellidos || 'No registrado';
    let correo = item.correo || 'No registrado';
    let telefono = item.telefono || 'No registrado';

    nueva_fila.innerHTML = `
        <td>${cont}</td>
        <td>${dni}</td>
        <td>${nombres_apellidos}</td>
        <td>${correo}</td>
        <td>${telefono}</td>
        <td>${estado_badge}</td>
        <td>
            <button type="button" title="Editar" class="btn btn-primary btn-sm waves-effect waves-light" data-toggle="modal" data-target=".modal_editar${item.id}">
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-info btn-sm" title="Resetear Contraseña" onclick="reset_password(${item.id})">
                <i class="fa fa-key"></i>
            </button>
        </td>
    `;
    
    // Generar modal de edición
    document.querySelector('#modals_editar').innerHTML += `
        <div class="modal fade modal_editar${item.id}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title h4">Actualizar datos de usuario</h5>
                        <button type="button" class="close waves-effect waves-light" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12">
                            <form class="form-horizontal" id="frmActualizar${item.id}">
                                <input type="hidden" name="data" value="${item.id}">
                                <div class="form-group row mb-2">
                                    <label for="dni${item.id}" class="col-3 col-form-label">DNI *</label>
                                    <div class="col-9">
                                        <input type="text" class="form-control" id="dni${item.id}" name="dni" value="${dni}" required maxlength="11">
                                    </div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label for="nombres_apellidos${item.id}" class="col-3 col-form-label">Apellidos y Nombres *</label>
                                    <div class="col-9">
                                        <input type="text" class="form-control" id="nombres_apellidos${item.id}" name="nombres_apellidos" value="${nombres_apellidos}" required>
                                    </div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label for="correo${item.id}" class="col-3 col-form-label">Correo Electrónico *</label>
                                    <div class="col-9">
                                        <input type="email" class="form-control" id="correo${item.id}" name="correo" value="${correo}" required>
                                    </div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label for="telefono${item.id}" class="col-3 col-form-label">Teléfono *</label>
                                    <div class="col-9">
                                        <input type="text" class="form-control" id="telefono${item.id}" name="telefono" value="${telefono}" required>
                                    </div>
                                </div>
                                <div class="form-group row mb-2">
                                    <label for="estado${item.id}" class="col-3 col-form-label">ESTADO *</label>
                                    <div class="col-9">
                                        <select name="estado" id="estado${item.id}" class="form-control" required>
                                            <option value="">Seleccione estado</option>
                                            <option value="1" ${activo_si}>ACTIVO</option>
                                            <option value="0" ${activo_no}>INACTIVO</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group mb-0 justify-content-end row text-center">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-light waves-effect waves-light" data-dismiss="modal">Cancelar</button>
                                        <button type="button" class="btn btn-success waves-effect waves-light" onclick="actualizarUsuario(${item.id})">Actualizar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    
    document.querySelector('#contenido_tabla').appendChild(nueva_fila);
}

// Funciones de paginación
function generar_paginacion(total, cantidad_mostrar, pagina_actual) {
    let total_paginas = Math.ceil(total / cantidad_mostrar);
    let paginacion = '';
    
    if (total_paginas <= 1) return '';
    
    // Botón Inicio y Anterior
    if (pagina_actual > 1) {
        paginacion += `<li class="page-item"><a class="page-link" href="javascript:numero_pagina(1)">Inicio</a></li>`;
        paginacion += `<li class="page-item"><a class="page-link" href="javascript:numero_pagina(${pagina_actual - 1})">Anterior</a></li>`;
    }
    
    // Números de página
    for (let i = 1; i <= total_paginas; i++) {
        if (i === pagina_actual) {
            paginacion += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            paginacion += `<li class="page-item"><a class="page-link" href="javascript:numero_pagina(${i})">${i}</a></li>`;
        }
    }
    
    // Botón Siguiente y Final
    if (pagina_actual < total_paginas) {
        paginacion += `<li class="page-item"><a class="page-link" href="javascript:numero_pagina(${pagina_actual + 1})">Siguiente</a></li>`;
        paginacion += `<li class="page-item"><a class="page-link" href="javascript:numero_pagina(${total_paginas})">Final</a></li>`;
    }
    
    return paginacion;
}

function generar_texto_paginacion(total, cantidad_mostrar, pagina_actual) {
    let inicio = ((pagina_actual - 1) * cantidad_mostrar) + 1;
    let fin = Math.min(pagina_actual * cantidad_mostrar, total);
    
    return `Mostrando del ${inicio} al ${fin} de un total de ${total} registros`;
}

async function registrar_usuario() {
    let dni = document.getElementById('dni').value.trim();
    let apellidos_nombres = document.querySelector('#apellidos_nombres').value.trim();
    let correo = document.querySelector('#correo').value.trim();
    let telefono = document.querySelector('#telefono').value.trim();
    
    if (!dni || !apellidos_nombres || !correo || !telefono) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Todos los campos son obligatorios',
            confirmButtonClass: 'btn btn-confirm mt-2'
        });
        return;
    }
    
    // Validar formato de DNI (solo números)
    if (!/^\d+$/.test(dni)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El DNI debe contener solo números',
            confirmButtonClass: 'btn btn-confirm mt-2'
        });
        return;
    }
    
    // Validar formato de email
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Formato de correo electrónico inválido',
            confirmButtonClass: 'btn btn-confirm mt-2'
        });
        return;
    }

    try {
        const datos = new FormData(document.getElementById('frmRegistrar'));
        datos.append('sesion', session_session);
        datos.append('token', token_token);
        
        let respuesta = await fetch(base_url_server + 'src/control/Usuario.php?tipo=registrar', {
            method: 'POST',
            body: datos
        });
        
        let json = await respuesta.json();
        
        if (json.status) {
            document.getElementById("frmRegistrar").reset();
            Swal.fire({
                icon: 'success',
                title: 'Registro Exitoso',
                text: json.mensaje,
                confirmButtonClass: 'btn btn-confirm mt-2',
                timer: 1500
            }).then(() => {
                // Recargar la lista después de registrar
                listar_usuariosOrdenados();
                // Cerrar modal si está en uno
                $('.modal').modal('hide');
            });
        } else if (json.msg == "Error_Sesion") {
            alerta_sesion();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: json.mensaje,
                confirmButtonClass: 'btn btn-confirm mt-2'
            });
        }
    } catch (error) {
        console.error("Error al registrar usuario:", error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión: ' + error.message,
            confirmButtonClass: 'btn btn-confirm mt-2'
        });
    }
}

async function actualizarUsuario(id) {
    let dni = document.getElementById('dni' + id).value.trim();
    let nombres_apellidos = document.querySelector('#nombres_apellidos' + id).value.trim();
    let correo = document.querySelector('#correo' + id).value.trim();
    let telefono = document.querySelector('#telefono' + id).value.trim();
    let estado = document.querySelector('#estado' + id).value;
    
    if (!dni || !nombres_apellidos || !correo || !telefono || !estado) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Todos los campos son obligatorios',
            confirmButtonClass: 'btn btn-confirm mt-2',
            timer: 1000
        });
        return;
    }
    
    // Validaciones
    if (!/^\d+$/.test(dni)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El DNI debe contener solo números',
            confirmButtonClass: 'btn btn-confirm mt-2'
        });
        return;
    }
    
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Formato de correo electrónico inválido',
            confirmButtonClass: 'btn btn-confirm mt-2'
        });
        return;
    }

    try {
        const formulario = document.getElementById('frmActualizar' + id);
        const datos = new FormData(formulario);
        datos.append('sesion', session_session);
        datos.append('token', token_token);
        
        let respuesta = await fetch(base_url_server + 'src/control/Usuario.php?tipo=actualizar', {
            method: 'POST',
            body: datos
        });
        
        let json = await respuesta.json();
        
        if (json.status) {
            $('.modal_editar' + id).modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Actualización Exitosa',
                text: json.mensaje,
                confirmButtonClass: 'btn btn-confirm mt-2',
                timer: 1500
            }).then(() => {
                // Recargar la lista después de actualizar
                listar_usuariosOrdenados();
            });
        } else if (json.msg == "Error_Sesion") {
            alerta_sesion();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: json.mensaje,
                confirmButtonClass: 'btn btn-confirm mt-2'
            });
        }
    } catch (error) {
        console.error("Error al actualizar usuario:", error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión: ' + error.message,
            confirmButtonClass: 'btn btn-confirm mt-2'
        });
    }
}

//-------------------------------------------------------- RESETEAR CONTRASEÑA -------------------------------------------------------------
function reset_password(id) {
    Swal.fire({
        title: "¿Estás seguro de generar nueva contraseña?",
        text: "Se generará una nueva contraseña para este usuario",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, generar",
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.value) {
            reniciar_password(id);
        }
    });
}

async function reniciar_password(id) {
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('sesion', session_session);
        formData.append('token', token_token);
        
        let respuesta = await fetch(base_url_server + 'src/control/Usuario.php?tipo=reiniciar_password', {
            method: 'POST',
            body: formData
        });
        
        let json = await respuesta.json();
        
        if (json.status) {
            Swal.fire({
                icon: 'success',
                title: 'Contraseña Actualizada',
                html: `<strong>${json.mensaje}</strong><br><br>Por favor, comparta esta contraseña con el usuario.`,
                confirmButtonClass: 'btn btn-confirm mt-2',
                confirmButtonText: "Aceptar"
            });
        } else if (json.msg == "Error_Sesion") {
            alerta_sesion();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: json.mensaje,
                confirmButtonClass: 'btn btn-confirm mt-2'
            });
        }
    } catch (error) {
        console.error("Error al resetear contraseña:", error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión: ' + error.message,
            confirmButtonClass: 'btn btn-confirm mt-2'
        });
    }
}

// Función para mostrar/ocultar popup de carga
function mostrarPopupCarga() {
    if (!document.getElementById('popupCarga')) {
        const popup = document.createElement('div');
        popup.id = 'popupCarga';
        popup.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
                <span class="ml-2 text-white">Cargando...</span>
            </div>
        `;
        document.body.appendChild(popup);
    } else {
        document.getElementById('popupCarga').style.display = 'flex';
    }
}

function ocultarPopupCarga() {
    const popup = document.getElementById('popupCarga');
    if (popup) {
        popup.style.display = 'none';
    }
}

// Función para alerta de sesión
function alerta_sesion() {
    Swal.fire({
        icon: 'warning',
        title: 'Sesión Expirada',
        text: 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.',
        confirmButtonClass: 'btn btn-confirm mt-2'
    }).then(() => {
        window.location.reload();
    });
}

// Inicializar la lista al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    listar_usuariosOrdenados();
});