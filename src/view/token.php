<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Token API</h4>
                <p class="card-title-desc">Gestiona el token de acceso a la API.</p>
                
                <div class="form-group">
                    <label><strong>Token Actual:</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="tokenInput" 
                               value="" readonly
                               style="font-family: monospace; font-size: 14px;">
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary" type="button" 
                                    onclick="habilitarEdicion()" title="Editar token">
                                <i class="fa fa-edit"></i> Actualizar
                            </button>
                            <button class="btn btn-outline-secondary" type="button" 
                                    onclick="copiarToken()" title="Copiar token">
                                <i class="fa fa-copy"></i> Copiar
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="botonesGuardar" style="display: none;" class="mt-3">
                    <button type="button" class="btn btn-success mr-2" onclick="guardarToken()">
                        <i class="fa fa-save"></i> Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="cancelarEdicion()">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                </div>
                
                <div id="mensaje" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>src/view/js/functions_token.js"></script>