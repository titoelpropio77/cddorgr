<!--<link rel="stylesheet" href="vendor/bootstrap-datepicker-master/dist/css/bootstrap-datepicker3.min.css" />-->
<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/bootstrap-datepicker-master/dist/css/bootstrap-datepicker3.min.css" />
<script src="<?php echo _base_url; ?>recursos/librerias/bootstrap-datepicker-master/dist/js/bootstrap-datepicker.min.js"></script>
<!--AutoSuggest-->
<script type="text/javascript" src="<?php echo _base_url; ?>recursos/js/bsn.AutoSuggest_c_2.0.js"></script>
<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
<!--AutoSuggest-->

<script>
    function enviar_formulario() {

        if ($('#int_id').val() === '') {
            Command: toastr["error"]("Ingrese el cliente.", "Error");
            return false;
        }

        if ($('#lot_id').val() === '') {
            Command: toastr["error"]("Elija el lote.", "Error");
            return false;
        }

        $('#frm_reserva').submit();
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
            <div class="panel-heading">
                <h3>Nueva Reserva</h3>
            </div>
            <div class="panel-body">

                <form method="post" class="form-horizontal" action="<?php echo _base_url . "reserva"; ?>" id="frm_reserva" name="frm_reserva" enctype="multipart/form-data">

                    <div class="form-group">

                        <label class="col-sm-2 control-label">Cliente:</label>
                        <div class="col-sm-5">                        
                            <input type="hidden" id="vdo_id" name="vdo_id" value="<?php echo $vdo_id; ?>"/>
                            <input type="hidden" id="res_moneda" name="res_moneda" value=""/>
                            <input type="hidden" id="int_id" name="int_id"/>
                            <input type="text" class="form-control" id="int_nombre_persona" name="int_nombre_persona" style=""/>
                        </div>
                        <script>
                            var options1 = {
//                            script: _base_url + 'suggest_persona?',
                                script: _base_url + 'ajax?tarea=personas&',
                                varname: "input",
                                minchars: 1,
                                timeout: 10000,
                                noresults: "No se encontro ninguna persona",
                                json: true,
                                callback: function(obj) {
                                    document.getElementById('int_id').value = obj.id;
                                }
                            };
                            var as_json1 = new _bsn.AutoSuggest('int_nombre_persona', options1);
                        </script>
                        <div class="col-sm-5">                        
                            <a class="btn btn-default col-sm-2" data-toggle="modal" data-target="#modalCliente">
                                <i class="fa fa-user-plus"></i>
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Urbanizacion:</label>
                        <div class="col-sm-10">
                            <select id="urb_id" name="urb_id" class="form-control">
                                <option value="">Seleccione</option>
                                <?php
                                for ($i = 0; $i < count($datos->urbanizaciones); $i++) {
                                    $urbanizacion = $datos->urbanizaciones[$i];
                                    ?>
                                    <option value="<?php echo $urbanizacion->urb_id; ?>"><?php echo $urbanizacion->urb_nombre; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">UV:</label>
                        <div class="col-sm-10">

                            <select id="uv_id" name="uv_id" class="form-control">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">

                        <label class="col-sm-2 control-label">Manzano:</label>

                        <div class="col-sm-10">
                            <select id="man_id" name="man_id" class="form-control">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Lote:</label>

                        <div class="col-sm-10">
                            <select id="lot_id" name="lot_id" class="form-control">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-sm-2 control-label">Nota:</label>

                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="res_nota" name="res_nota"/>
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-sm-2 control-label">Superficie:</label>

                        <div class="col-sm-10">
                            <input type="text" readonly="" class="form-control" id="superficie" name="superficie"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Valor m2: <span id="simb_moneda_vm2"></span></label>

                        <div class="col-sm-10">
                            <input type="text" readonly="" class="form-control" id="m2" name="m2"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Valor del Terreno: <span id="simb_moneda_vt"></span></label>

                        <div class="col-sm-10">
                            <input type="text" readonly="" class="form-control" id="valor_terreno" name="valor_terreno"/>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Subir Foto...</label>
                        <div class="col-sm-10">
                            <input id="cargar_archivo" name="cargar_archivo" type="file" class="file" data-show-upload="false" data-show-caption="true">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-10">
                            <button id="btn_guardar" name="btn_guardar" type="button" class="btn btn-primary" onclick="javascript:enviar_formulario();">Guardar</button>
                        </div>
                    </div>
                </form>


            </div>
        </div>
    </div>
</div>

<?php
require("clientes_form.php");
?>

<script>
                            $(document).ready(function() {
                                $('#urb_id').change(function() {
                                    var urb_id = $('#urb_id option:selected').val();
                                    var texto = $('#urb_id option:selected').text();
                                    console.log('urb_id => ' + urb_id + '; texto => ' + texto);
                                    $.post(_base_url + 'ajax', {tarea: 'listar', que: 'uv', urb_id: urb_id}, function(respuesta) {
                                        var res = eval("(" + respuesta + ")");
                                        res = res[0].respuesta;
                                        console.log(res.accion);
                                        var uvs = res.datos.uvs;
                                        console.log('uvs.legth => ' + uvs.length);
                                        var opciones = "<option value=''>Seleccione</option>";
                                        for (i = 0; i < uvs.length; i++) {
                                            var uv = uvs[i];
                                            var option = "<option value='" + uv.uv_id + "'>" + uv.uv_nombre + "</option>";
                                            opciones += option;
                                        }
                                        $('#uv_id').html(opciones);

                                    });
                                });

                                $('#uv_id').change(function() {
                                    var urb_id = $('#urb_id option:selected').val();
                                    var uv_id = $('#uv_id option:selected').val();
                                    $.post(_base_url + 'ajax', {tarea: 'listar', que: 'manzano', urb_id: urb_id, uv_id: uv_id}, function(respuesta) {
                                        var res = eval("(" + respuesta + ")");
                                        res = res[0].respuesta;
                                        console.log(res.accion);
                                        var manzanos = res.datos.manzanos;
                                        console.log('manzanos.legth => ' + manzanos.length);
                                        var opciones = "<option value=''>Seleccione</option>";
                                        for (i = 0; i < manzanos.length; i++) {
                                            var man = manzanos[i];
                                            var option = "<option value='" + man.man_id + "'>" + man.man_nro + "</option>";
                                            opciones += option;
                                        }
                                        $('#man_id').html(opciones);

                                    });
                                });

                                $('#man_id').change(function() {

                                    var urb_id = $('#urb_id option:selected').val();
                                    var uv_id = $('#uv_id option:selected').val();
                                    var man_id = $('#man_id option:selected').val();

                                    $.post(_base_url + 'ajax', {tarea: 'listar', que: 'lote', urb_id: urb_id, uv_id: uv_id, man_id: man_id}, function(respuesta) {
                                        var res = eval("(" + respuesta + ")");

                                        console.log(res);

                                        res = res[0].respuesta;
                                        var lotes = res.datos.lotes;
                                        var opciones = "<option value=''>Seleccione</option>";
                                        for (i = 0; i < lotes.length; i++) {
                                            var lot = lotes[i];
                                            var option = "<option value='" + lot.id + "'>" + lot.nombre + "</option>";
                                            opciones += option;
                                        }
                                        $('#lot_id').html(opciones);

                                    });
                                });

                                $('#lot_id').change(function() {

                                    console.log($(this).val());
                                    var datos = $(this).val();
                                    var val = datos.split('-');

                                    $('#valor_terreno').val(parseFloat(val[1]) * parseFloat(val[2]));
                                    $('#superficie').val(val[1]);
                                    $('#m2').val(val[2]);
                                    $('#res_moneda').val(val[4]);

                                    var simbolo_moneda_vm2 = '';
                                    var simbolo_moneda_vt = '';
                                    if (val[4] === 1) {
                                        simbolo_moneda_vm2 = '(Bs)'
                                        simbolo_moneda_vt = '(Bs)'

                                    } else {
                                        simbolo_moneda_vm2 = '($us)';
                                        simbolo_moneda_vt = '($us)';

                                    }
                                    $('#simb_moneda_vm2').html(simbolo_moneda_vm2);
                                    $('#simb_moneda_vt').html(simbolo_moneda_vt);

                                });

                                $('#btn_guardar').click(function() {
                                    console.log('enviando....');
                                });

                                $('.modal_reset').click(function() {
                                    console.log('Reseteando el formulario......');
                                    document.getElementById("form_cliente").reset();
                                    // $('#form_cliente').reset();
                                });

//        $('#int_fecha_nacimiento').datepicker();

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