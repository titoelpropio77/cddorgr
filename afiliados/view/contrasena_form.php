<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
            <div class="panel-heading">
                <h3>Cambiar Contrase&ntilde;a</h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo _base_url . $_GET['uri1']; ?>" method="POST" class="form-horizontal" enctype="multipart/form-data">

                    <div class="hr-line-dashed"></div>
                    <div class="form-group"><label class="col-lg-2 control-label">Usuario</label>
                        <div class="col-lg-10"><input type="text" disabled="" placeholder="<?php echo $_SESSION['afil_id']; ?>" class="form-control"></div>
                    </div>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group"><label class="col-sm-2 control-label">Contrase&ntilde;a Antigua</label>
                        <div class="col-sm-10"><input type="password" class="form-control" name="usu_password_antiguo"></div>
                    </div>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group"><label class="col-sm-2 control-label">Contrase&ntilde;a Nueva</label>
                        <div class="col-sm-10"><input type="password" class="form-control" name="usu_password"></div>
                    </div>
                    
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Subir Foto...</label>
                        <div class="col-sm-10">
                            <input id="cargar_archivo" name="cargar_archivo" type="file" class="file" data-show-upload="false" data-show-caption="true">
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-2">
                            <button class="btn btn-default" type="reset">Cancelar</button>
                            <button class="btn btn-primary" type="submit">Guardar</button>
                        </div>
                    </div>
                </form> 
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function(){
    
    $('#cargar_archivo').fileValidator({
        onValidation: function(files) { /* Called once before validating files */
            console.log('onValidation...');
            $('.file-preview').show();
            $('.fileinput-remove-button').show();
        },
        onInvalid: function(validationType, file) { /* Called once for each invalid file */
            console.log('onInvalid...validationType => ' + validationType + ' - file => ' + JSON.stringify(file));

            if (validationType === 'maxSize') {
    //                    Command: toastr["error"]("El tama&ntilde;o de archivo excede los 4 MB.", "Error");

                var datos = {};
                datos.mensaje = "El tama&ntilde;o de archivo excede los 4 MB.";
                datos.tipo = "error";
                datos.titulo = "ERROR";
                mensaje(datos);
            } else if (validationType === 'type') {
    //                    Command: toastr["error"]("El tipo de archivo no es una imagen.", "Error");

                var datos = {};
                datos.mensaje = "El tipo de archivo no es una imagen.";
                datos.tipo = "error";
                datos.titulo = "ERROR";
                mensaje(datos);

            }
            $('.fileinput-remove-button').trigger('click');

        },
        maxSize: '4m', //optional
        type: 'image' //optional
    });

    $('.fileinput-remove-button').on('click', function() {
        console.log('removeeeeee....');
        $('.file-preview').hide();
        $('.fileinput-remove-button').hide();
    });
    
});
</script>
