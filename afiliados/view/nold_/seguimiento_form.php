<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/datetimepicker/jquery.datetimepicker.css" />
<script src="<?php echo _base_url; ?>recursos/librerias/datetimepicker/jquery.datetimepicker.js"></script>

<!--AutoSuggest-->
<script type="text/javascript" src="<?php echo _base_url; ?>recursos/js/bsn.AutoSuggest_c_2.0.js"></script>
<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

<h3>SEGUIMIENTO</h3>
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
		
			<ul class="nav nav-tabs">
				<li class="" style="float:right;"><a href="<?php echo _base_url; ?>crm/seguimiento/lista"> Listar</a></li>
                <li class="active" style="float:right;"><a href="<?php echo _base_url; ?>crm/seguimiento/agregar"> Agregar</a></li>
            </ul>
			
            <div class="tab-content">
                <div class="tab-pane active">
					<div class="panel-body">
						<?php if($form_visible == "Si") { ?> 
						<form method="post" class="form-horizontal" id="frm_reserva" name="frm_reserva">
							<input type="hidden" id="seg_id" name="seg_id" value="<?php echo $_POST['seg_id']; ?>" /> 
							<div class="form-group">
								<label class="col-sm-2 control-label">Tipo Contacto<span class="requerido">*</span>:</label>
								<div class="col-sm-10">
									<select id="seg_stc_id" name="seg_stc_id" class="form-control">
										<option value="">Seleccione</option>
										<?php
										foreach ($datos as $key => $value) {
											$selected = "";
											if($value->stc_id==$_POST['seg_stc_id']){
												$selected = "selected";
											} 
											?>
											<option <?php echo $selected; ?> value="<?php echo $value->stc_id; ?>"><?php echo $value->stc_tipo; ?></option>
											<?php
										}
										?>
									</select>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-sm-2 control-label">Cliente <span class="requerido">*</span>:</label>
								<div class="col-sm-5">                        
									<input type="hidden" id="seg_int_id" name="seg_int_id" value="<?php echo $_POST['seg_int_id']; ?>"/> 
									<input type="text" class="form-control" id="cliente" name="cliente" value="<?php echo $_POST['cliente']; ?>" placeholder="Buscar cliente o persona"/>
								</div>
								<script>
									var options1 = {
										script: _base_url + 'ajax?tarea=personas&',
										varname: "input",
										minchars: 2,
										timeout: 10000,
										noresults: "No se encontro ninguna persona",
										json: true,
										callback: function(obj) {
											document.getElementById('seg_int_id').value = obj.id;
										}
									};
									var as_json1 = new _bsn.AutoSuggest('cliente', options1);
								</script>
								<div class="col-sm-5">                        
									<a class="btn btn-default col-sm-2" data-toggle="modal" data-target="#modalCliente">
										<i class="fa fa-user-plus"></i>
									</a>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-sm-2 control-label">Fecha <span class="requerido">*</span>:</label>
								<div class="col-sm-10">
									<?php
										if(isset($_POST['seg_fecha'])){
											$seg_fecha = $_POST['seg_fecha'];
										} else {
											$seg_fecha = date('d/m/Y');
										}
									?>
									<input type="text" class="form-control" id="seg_fecha" name="seg_fecha" value="<?php echo $seg_fecha; ?>"/>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-sm-2 control-label">Hora <span class="requerido">*</span>:</label>
								<div class="col-sm-10">
									<?php 
										if(isset($_POST['seg_hora'])){
											$seg_hora = $_POST['seg_hora'];
										} else {
											$seg_hora = date('H:i');
										}
									?>
									<input type="text" class="form-control" id="seg_hora" name="seg_hora"  value="<?php echo $seg_hora; ?>"/>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-sm-2 control-label">Situación <span class="requerido">*</span>:</label>

								<div class="col-sm-10">
									<textarea class="form-control" id="seg_situacion" name="seg_situacion"><?php echo $_POST['seg_situacion']; ?></textarea>
								</div>
							</div>
							
							<?php if($_GET['uri3'] !="ver") { ?>
							<div class="form-group">
								<label class="col-sm-2">&nbsp; </label>
								<div class="col-sm-10">
									<button id="btn_guardar" name="btn_guardar" type="button" class="btn btn-primary" onclick="javascript:enviar_formulario();">Guardar</button>
								</div>
							</div>
							<?php } ?>
							
						</form>
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
							
							$(document).ready(function() {
								$('#seg_fecha').mask('99/99/9999');
								$('#seg_hora').mask('99:99');
								
								$.datetimepicker.setLocale('es');
								$('#seg_fecha').datetimepicker({
									timepicker:false,
									format:'d/m/Y',
									lang:'es',
									mask:false,
								});
							});
						</script>
						<?php } ?>
						
					</div>
				</div>
            </div>
        </div>
    </div>
</div>


<?php
	require("clientes_form.php");
?>

