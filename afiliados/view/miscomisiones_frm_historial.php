<!--<link rel="stylesheet" href="vendor/bootstrap-datepicker-master/dist/css/bootstrap-datepicker3.min.css" />-->
<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/bootstrap-datepicker-master/dist/css/bootstrap-datepicker3.min.css" />
<script src="<?php echo _base_url; ?>recursos/librerias/bootstrap-datepicker-master/dist/js/bootstrap-datepicker.min.js"></script>
<!--AutoSuggest-->
<script type="text/javascript" src="<?php echo _base_url; ?>recursos/js/bsn.AutoSuggest_c_2.0.js"></script>
<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
<!--AutoSuggest-->

<script>
    function enviar_formulario(){
        console.log('entrando a enviar formulario...');
        if ($('#ges_id option:selected').val() === '') {
            Command: toastr["error"]("Seleccione la gesti&oacute;n.", "Error");
            return false;
        }
        
        if ($('#pdo_ini option:selected').val() === '') {
            Command: toastr["error"]("Seleccione el periodo inicial.", "Error");
            return false;
        }
        
        var forma_reporte = $('#forma_reporte option:selected').val();
        
        if (forma_reporte === 'resumido') {
            
            if ($('#pdo_fin option:selected').val() === '') {
                Command: toastr["error"]("Seleccione el periodo final.", "Error");
                return false;
            }
        }
        console.log('enviando el formulario...');
        $('#frm_reserva').submit();
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
            <div class="panel-heading">
                <h3>Historial de Comisiones</h3>
            </div>
            <div class="panel-body">

                <form method="post" class="form-horizontal" action="<?php echo _base_url."miscomisiones";?>" id="frm_reserva" name="frm_reserva">
                                        
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Forma de Visualizaci&oacute;n:</label>
                        <div class="col-sm-10">
                            <select id="forma_reporte" name="forma_reporte" class="form-control">
                                <!--<option value="">Seleccione</option>-->                                
                                <option value="resumido">Resumido</option>
                                <option value="detallado">Detallado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Gestion:</label>
                        <div class="col-sm-10">
                            <select id="ges_id" name="ges_id" class="form-control">
                                <option value="">Seleccione</option>
                                <?php
                                for ($i = 0; $i < count($datos->gestiones); $i++) {
                                    $gestion = $datos->gestiones[$i];
                                    ?>
                                    <option value="<?php echo $gestion->ges_id; ?>"><?php echo $gestion->ges_descripcion; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Periodo <span id="sp_pdo_ini">Inicial</span>:</label>
                        <div class="col-sm-10">

                            <select id="pdo_ini" name="pdo_ini" class="form-control">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group" id="div_pdo_final">
                        <label class="col-sm-2 control-label">Periodo Final:</label>
                        <div class="col-sm-10">

                            <select id="pdo_fin" name="pdo_fin" class="form-control">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-10">
                            <button id="btn_guardar" name="btn_guardar" type="button" class="btn btn-primary" onclick="javascript:enviar_formulario();">Generar</button>
                        </div>
                    </div>
                </form>


            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {
        $('#int_fecha_nacimiento').mask('99/99/9999');
        $('#enviarForm').click(function() {
            
            if (($("#int_nombre").val() != "") && ($("#int_apellido").val() != "") && 
                    ($("#int_ci").val() != ""))
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




//        $('a[data-target="#modalCliente"]').click(function() {
//
//            Command: toastr["success"]("mensaje texto", "titulo");
//
//        });

        $('#ges_id').change(function() {
            var ges_id = $('#ges_id option:selected').val();
            var texto = $('#ges_id option:selected').text();
            console.log('ges_id => ' + ges_id + '; texto => ' + texto);
            $.post(_base_url + 'ajax', {tarea: 'periodos', ges_id: ges_id}, function(respuesta) {
                var res = eval("(" + respuesta + ")");
                res = res[0].respuesta;
                console.log(res.accion);
                var periodos = res.datos.periodos;
                console.log('periodos.legth => ' + periodos.length);
                var opciones = "<option value=''>Seleccione</option>";
                for (i = 0; i < periodos.length; i++) {
                    var periodo = periodos[i];
                    var option = "<option value='" + periodo.pdo_id + "'>" + periodo.pdo_descripcion + "</option>";
                    opciones += option;
                }
                $('#pdo_ini').html(opciones);
                $('#pdo_fin').html(opciones);

            });
        });
        
        $('#forma_reporte').change(function() {
            var forma = $('#forma_reporte option:selected').val();
            if (forma === 'resumido') {
                $('#sp_pdo_ini').text('Inicial');
                $('#div_pdo_final').css('visibility','visible');
            } else {
//                $('#div_pdo_final').hide();
                $('#sp_pdo_ini').text('');
                $('#div_pdo_final').css('visibility','hidden');
            }
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
    });



</script>