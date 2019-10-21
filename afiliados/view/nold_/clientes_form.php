<div class="modal fade" id="modalCliente" tabindex="-1" role="dialog" aria-hidden="true" style="top: 0" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" class="form-horizontal" id="form_cliente" name="form_cliente">
                <div class="color-line"></div>
                <div class="modal-header text-center">
                    <h4 class="modal-title">Nuevo Cliente</h4>
                    <small class="font-bold">Agregue los datos del Cliente</small>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label class="col-sm-2 control-label">Nombres(*):</label>
                        <div class="col-sm-10"><input type="text" class="form-control" id="int_nombre" name="int_nombre"></div>
                    </div>
                    <!--<div class="hr-line-dashed"></div>-->
                    <div class="form-group"><label class="col-sm-2 control-label">Apellidos(*):</label>

                        <div class="col-sm-10"><input type="text" class="form-control" id="int_apellido" name="int_apellido"></div>
                    </div>
                    <!--<div class="hr-line-dashed"></div>-->
                    <div class="form-group"><label class="col-sm-2 control-label">C.I.(*)</label>

                        <div class="col-sm-6"><input type="text" class="form-control" id="int_ci" name="int_ci"></div>
                        <label class="col-sm-2 control-label">Exp(*).</label>
                        <div class="col-sm-2">
                            <select id="int_ci_exp" name="int_ci_exp" class="form-control">
                                <option value="">Seleccione</option>									
                                <option value="SC">SC</option>
                                <option value="BN">BN</option>
                                <option value="PA">PA</option>
                                <option value="LP">LP</option>
                                <option value="OR">OR</option>
                                <option value="PT">PT</option>
                                <option value="CB">CB</option>
                                <option value="TJ">TJ</option>
                                <option value="CH">CH</option>
                                <option value="EX">EX</option>
                            </select>
                        </div>
                    </div>
                    <!--<div class="hr-line-dashed"></div>-->
                    <div class="form-group"><label class="col-sm-2 control-label">Fecha de Nacimiento(*):</label>

                        <div class="col-sm-10"><input type="text" class="form-control" value="<?php echo date('d/m/Y'); ?>" id="int_fecha_nacimiento" name="int_fecha_nacimiento"></div>
                    </div>
                    <!--<div class="hr-line-dashed"></div>-->
                    <div class="form-group"><label class="col-lg-2 control-label">Email:</label>

                        <div class="col-lg-10"><input type="email" id="int_email" name="int_email" required="" class="form-control" placeholder="Ingrese email" aria-required="true"></div>
                    </div>
                    <!--<div class="hr-line-dashed"></div>-->
                    <div class="form-group"><label class="col-sm-2 control-label">Telefono:</label>

                        <div class="col-sm-10"><input type="text" class="form-control" id="int_telefono" name="int_telefono"></div>
                    </div>
                    <!--<div class="hr-line-dashed"></div>-->
                    <div class="form-group"><label class="col-sm-2 control-label">Celular(*):</label>

                        <div class="col-sm-10"><input type="text" class="form-control" id="int_celular" name="int_celular"></div>
                    </div>


                    <div class="form-group"><label class="col-sm-2 control-label">Direccion(*):</label>

                        <div class="col-sm-10"><input type="text" class="form-control" id="int_direccion" name="int_direccion"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default modal_reset" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="enviarForm">Guardar</button>
                </div>
        </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#int_fecha_nacimiento').mask('99/99/9999');
        $('#enviarForm').click(function() {
            if (($("#int_nombre").val() != "") && ($("#int_apellido").val() != "") &&
                    ($("#int_ci").val() != "") && ($("#int_ci_exp").val() != "") &&
                    ($('#int_celular').val() != "") && ($('#int_fecha_nacimiento').val() != "") &&
                    ($('#int_direccion').val() != ""))
            {
                var datosForm = JSON.stringify($('#form_cliente').serializeObject());
                var resul = ajax_cargar(_base_url + "ajax?tarea=add_cliente", "POST", "&datosForm=" + datosForm, "json");
                resul.success(function(jsondata)
                {
                    console.log('jsondata => ' + jsondata);
                    var accion = jsondata[0].respuesta.accion.toString();
                    if (accion == "correcto")
                    {
                        console.log(jsondata[0].respuesta.mensaje.toString());
                        Command: toastr["success"](jsondata[0].respuesta.mensaje.toString(), "Ok");
                        $('#modalCliente').modal('hide');
                    }
                    else
                    {
                        Command: toastr["error"](jsondata[0].respuesta.mensaje.toString(), "Error");
                    }
                });
                resul.fail(function(error)
                {
                    console.log("Eroor");
                });
            } else {
                Command: toastr["error"]("Por favor rellene los campos requeridos (*)", "Error");
            }
            return false;
        });
    });
</script>