<h3>SEGUIMIENTO</h3> 
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
		
			<ul class="nav nav-tabs">
				<li class="active" style="float:right;"><a href="<?php echo _base_url; ?>crm/seguimiento/lista"> Listar</a></li>
                <li class="" style="float:right;"><a href="<?php echo _base_url; ?>crm/seguimiento/agregar"> Agregar</a></li>
            </ul>
			
            <div class="tab-content">
                <div class="tab-pane active">
					<div class="panel-body">
					
						<div class="row">
							<div class="col-lg-12">
								<form method="GET" class="form-horizontal" action="<?php echo _base_url; ?>crm/seguimiento/lista" id="frm_reserva" name="frm_reserva">
									<?php 
										$buscar = "";
										$buscarPaginacion = "";
										if(isset($_GET['buscar'])){
											$buscar = $_GET['buscar'];
											$buscarPaginacion ="&buscar=".$_GET['buscar'];
										} 
									?>
									<div class="form-group">
										<label class="col-sm-2 control-label">Cliente:</label>
										<div class="col-sm-5">  
											<input class="form-control" id="buscar" name="buscar" value="<?php echo $buscar; ?>" type="text"><br>
											<small>Por favor busque  por los siguientes criterios: <b>nombre, apellido, situaci&oacute;n</b></small>
										</div> 
										<div class="col-sm-5">
											<button class="btn btn-sm btn-primary m-t-n-xs" type="submit"><strong>Buscar</strong></button>
										</div>
									</div>
								</form>
							</div>
						</div>
						
						<div class="row">
							<div class="col-lg-12">
								<table id="tablaPend" class="footable table table-bordered table-hover tableSinpaginar" >
									<thead>
										<tr>
											<th>Cliente</th> 	
											<th  data-hide="phone,tablet">Vendedor</th> 	
											<th>Tipo de Contato</th> 	
											<th data-hide="phone,tablet">Situación</th> 	
											<th>Fecha</th> 	
											<th>Hora</th> 	
											<th>Acciones</th> 	
											<th class="cvbc">Opciones</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($datos as $key => $value) {
											$encryp = new Encryption();
											$encrypId = $encryp->encode($value->seg_id);
											?>
											<tr>
												<td><?php echo $value->cliente; ?></td>
												<td><?php echo $value->seg_usu_id; ?></td>
												<td><?php echo $value->stc_tipo; ?></td>
												<td><?php echo $value->seg_situacion; ?></td>
												<td><?php echo $value->seg_fecha; ?></td>
												<td><?php echo $value->seg_hora; ?></td>
												<td><?php echo $value->acciones; ?></td>
												<td style="text-align:right">
													<div class="btn-group">
														<a href="<?php echo _base_url; ?>crm/seguimiento/ver/<?php echo $encrypId; ?>" class="btn btn-default" title="Ver"><i class="fa fa-search"></i> </a>
														<a href="<?php echo _base_url; ?>crm/seguimiento/modificar/<?php echo $encrypId; ?>" class="btn btn-default" title="Modificar"><i class="fa fa-pencil"></i></a>
														<a href="<?php echo _base_url; ?>crm/seguimiento/acciones/<?php echo $encrypId; ?>" class="btn btn-default" title="Acciones"><i class="fa fa-commenting"></i></a> 
														<a href="<?php echo _base_url; ?>crm/seguimiento/eliminar/<?php echo $encrypId; ?>" class="btn btn-default linkEliminar" title="Eliminar"><i class="fa fa-trash-o"></i> </a>
													</div>
												</td>
											</tr>
											<?php
										}
										?>
									</tbody> 
									<tfoot>
										<tr>
											<td colspan="7" >&nbsp;</td>                                    
										</tr> 
									</tfoot>
								</table>
							</div>
						</div>
						
						<div class="row">
							<div class="col-lg-12">
								<center>
									<ul class="pagination">
										<?php 
											for ($i=0; $i<$opciones->pageTotal; $i++) {
												$active = "";
												if($opciones->pageActivo==$i){
													$active = "active";
												}
										?>
										<li class="<?php echo $active; ?>"><a href="<?php echo _base_url; ?>crm/seguimiento/lista?pagina=<?php echo $i.$buscarPaginacion; ?>"><?php echo ($i+1)?></a></li>
										<?php } ?>
									</ul>
								</center>
							</div>
						</div>
						
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>
