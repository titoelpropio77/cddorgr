<link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/datetimepicker/jquery.datetimepicker.css" />
<script src="<?php echo _base_url; ?>recursos/librerias/datetimepicker/jquery.datetimepicker.js"></script>

<h3>AGREGAR ACCIÓN</h3>
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
			<div class="panel-body">
				<div class="tTablaEsp">
					<table class="tTabla1" cellspacing="0">
						<tbody>
							<tr>
								<th class="text-right" style="width:220px">Cliente:</th> 
								<td class="text-left"><?php echo $datos->cliente; ?></td>
							</tr>
							<tr> 
								<th class="text-right">Fecha inicio del seguimiento:</th> 
								<td class="text-left"><?php echo $datos->seg_fecha; ?></td>
							</tr>
							<tr>
								<th class="text-right">Hora:</th>
								<td class="text-left"><?php echo $datos->seg_hora; ?></td>
							</tr>
							<tr>
								<th class="text-right">Tipo de Contacto:</th>
								<td class="text-left"><?php echo $datos->stc_tipo; ?></td>
							</tr>
							<tr>
								<th class="text-right">Situaci&oacute;n:</th>
								<td class="text-left"><?php echo $datos->seg_situacion; ?></td>
							</tr>
						</tbody>
					</table>
				</div>		
				
				<div style="clear:both;"></div> 
				
				<div class="tTablaEsp"> 
					<form method="post" class="form-horizontal" id="frm_reserva" name="frm_reserva">
						<input type="hidden" class="form-control" id="sac_id" name="sac_id" value="<?php echo $_POST['sac_id']; ?>"/>
						<input type="hidden" class="form-control" id="sac_seg_id" name="sac_seg_id" value="<?php echo $_GET['uri4']; ?>"/>
						
						<table class="tTabla1" cellspacing="0">
							<thead> 
								<tr>
									<th>Nro.</th>
									<th>Fecha</th>
									<th>Hora</th>
									<th>Accion</th> 
									
									<th>Recordar por correo</th>
									<th class="tOpciones" width="100px">Opciones</th>
								</tr>
							</thead>
							<tbody>
								<?php
									if(count($opciones) >0 ) { 
										foreach ($opciones as $key => $value) {
											$encryp = new Encryption();
											$encrypId = $encryp->encode($value->sac_id);
											?>
											<tr> 
												<td><?php echo ($key+1); ?></td>
												<td><?php echo $value->sac_fecha; ?></td>
												<td><?php echo $value->sac_hora; ?></td>
												<td><?php echo $value->sac_accion; ?></td>
												<td>
													<?php 
														if($value->sac_alerta < 60){
															echo $value->sac_alerta." min";
														} else {
															echo (($value->sac_alerta)/(60))." hora";
														}
													?>
												</td>
												<td style="text-align:right">
													<div class="btn-group">
														<a href="<?php echo _base_url; ?>crm/seguimiento/acciones/<?php echo $_GET['uri4']; ?>/modificar/<?php echo $encrypId; ?>" class="btn btn-default" title="Modificar"><i class="fa fa-pencil"></i></a>
														<a href="<?php echo _base_url; ?>crm/seguimiento/acciones/<?php echo $_GET['uri4']; ?>/eliminar/<?php echo $encrypId; ?>" class="btn btn-default linkEliminar" title="Eliminar"><i class="fa fa-trash-o"></i> </a>
													</div>
												</td>
											</tr>
											<?php 
										}
									} else {
										?>
										<tr>
											<td colspan="6">
												<div class="msMensaje bg-warning">
													No se encontro ninguna acci&oacute;n registrada
												</div>
											</td>
										</tr>
										<?php
									}
								?>
							</tbody>
						</table> 
						
						<div style="clear:both;"></div><br>
						
						<div class="form-group">
							<label class="col-sm-2 control-label">Fecha <span class="requerido">*</span>:</label>
							<div class="col-sm-10">
								<?php
									if(isset($_POST['sac_fecha'])){
										$sac_fecha = $_POST['sac_fecha'];
									} else {
										$sac_fecha = date('d/m/Y');
									}
								?>
								<input type="text" class="form-control" id="sac_fecha" name="sac_fecha" value="<?php echo $sac_fecha; ?>"/>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label">Hora <span class="requerido">*</span>:</label>
							<div class="col-sm-10">
								<?php 
									if(isset($_POST['sac_hora'])){
										$sac_hora = $_POST['sac_hora'];
									} else {
										$sac_hora = date('H:i');
									}
								?>
								<input type="text" class="form-control" id="sac_hora" name="sac_hora"  value="<?php echo $sac_hora; ?>"/>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label">Acci&oacute;n <span class="requerido">*</span>:</label>
							<div class="col-sm-10">
								<textarea class="form-control" id="sac_accion" name="sac_accion"><?php echo $_POST['sac_accion']; ?></textarea>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label">Recordar</label>
							<div class="col-sm-10">
								<select name="sac_alerta" id="sac_alerta" class="caja_texto">
									<option value="0">Ninguno</option>
									<option value="5" <?php if($_POST['sac_alerta']==5){ echo "selected";  } ?>>5 minutos antes</option>
									<option value="10" <?php if($_POST['sac_alerta']==10){ echo "selected";  } ?>>10 minutos antes</option>
									<option value="15" <?php if($_POST['sac_alerta']==15){ echo "selected";  } ?>>15 minutos antes</option>
									<option value="20" <?php if($_POST['sac_alerta']==20){ echo "selected";  } ?>>20 minutos antes</option>
									<option value="25" <?php if($_POST['sac_alerta']==25){ echo "selected";  } ?>>25 minutos antes</option>
									<option value="30" <?php if($_POST['sac_alerta']==30){ echo "selected";  } ?>>30 minutos antes</option>
									<option value="45" <?php if($_POST['sac_alerta']==45){ echo "selected";  } ?>>45 minutos antes</option>
									<option value="60" <?php if($_POST['sac_alerta']==60){ echo "selected";  } ?>>1 hora antes</option>
									<option value="120" <?php if($_POST['sac_alerta']==120){ echo "selected";  } ?>>2 hora antes</option>
									<option value="180" <?php if($_POST['sac_alerta']==180){ echo "selected";  } ?>>3 hora antes</option>
									<option value="240" <?php if($_POST['sac_alerta']==240){ echo "selected";  } ?>>4 hora antes</option>
									<option value="300" <?php if($_POST['sac_alerta']==300){ echo "selected";  } ?>>5 hora antes</option>
								</select>
								<br><small>Recordatorio por correo</small> 
							</div>
						</div>
						
						<?php if($_GET['uri6'] =="modificar") { ?>
						<div class="form-group"> 
							<label class="col-sm-2 control-label">Estado</label>
							<div class="col-sm-10">
								<select name="sac_estado" id="sac_estado" class="caja_texto">
									<option value="Pendiente" <?php if($_POST['sac_estado']=="Pendiente"){ echo "selected";  } ?>>Pendiente</option>
									<option value="Confirmado" <?php if($_POST['sac_estado']=="Confirmado"){ echo "selected";  } ?>>Confirmado</option>
								</select>
							</div>
						</div>
						<?php } ?>
						
						<?php if($_GET['uri3'] !="ver") { ?>
							<div class="form-group">
								<label class="col-sm-2">&nbsp; </label>
								<div class="col-sm-10">
									<button id="btn_guardar" name="btn_guardar" type="button" class="btn btn-primary" onclick="javascript:enviar_formulario();">Guardar</button>
								</div>
							</div>
						<?php } ?>
							
					</form>
				</div> 
				
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
						$('#sac_fecha').mask('99/99/9999');
						$('#sac_hora').mask('99:99');
						$.datetimepicker.setLocale('es');
						$('#sac_fecha').datetimepicker({
							timepicker:false,
							format:'d/m/Y',
							lang:'es',
							mask:false,
						});
					});
				</script>
				</form> 
			</div> 
		</div> 
	</div> 
</div> 

