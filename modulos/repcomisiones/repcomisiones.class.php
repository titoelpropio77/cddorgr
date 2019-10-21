<?php

class REPCOMISIONES extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function REPCOMISIONES()
	{
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='repcomisiones';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('COMISIONES A VENDEDORES');
		
		$this->usu=new USUARIO;
	}
	
	
	function dibujar_busqueda()
	{
			$this->formulario();
	}
	
	function formulario()
	{
	
		$this->formulario->dibujar_cabecera();
		
		if(!($_POST['formu']=='ok'))
		{
		?>
		
						<?php
						if($this->mensaje<>"")
						{
						?>
							<table width="100%" cellpadding="0" cellspacing="1" style="border:1px solid #DD3C10; color:#DD3C10;">
							<tr bgcolor="#FFEBE8">
								<td align="center">
									<?php
										echo $this->mensaje;
									?>
								</td>
							</tr>
							</table>
						<?php
						}
						?>						
						<div id="Contenedor_NuevaSentencia">
						<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=repcomisiones" method="POST" enctype="multipart/form-data">  
									<div id="FormSent">
										<div class="Subtitulo">Filtro del Reporte</div>
					                    <div id="ContenedorSeleccion">
											<!--Inicio-->
											<div id="ContenedorDiv">
											   <div class="Etiqueta" >Vendedor</div>
												<div id="CajaInput">
                                                <?php
											    if($this->obtener_grupo_id($this->usu->get_id())=="Promotores")
											    {
												    $id_interno=$this->obtener_id_interno_tbl_usuario($this->usu->get_id());
											    ?>
													<select style="width:200px;" name="vendedor" class="caja_texto">
															
														   <?php 		
															$fun=NEW FUNCIONES;		
															$fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado' and vdo_int_id=$id_interno",$_POST['vendedor']);				
															?>
												   </select>
                                                <?php
											    }
											    else
											    {
											    ?>
                                                <select style="width:200px;" name="vendedor" class="caja_texto">
															<option value="">Todos los vendedores</option>
														   <?php 		
															$fun=NEW FUNCIONES;		
															$fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado'",$_POST['vendedor']);				
															?>
												   </select>
                                                <?php
												}
												?>
											   </div>
											</div>
											<!--Fin-->
											<!--Inicio-->
											<div id="ContenedorDiv">
											   <div class="Etiqueta" >Estado</div>
												<div id="CajaInput">
													<select style="width:200px;" name="estado" class="caja_texto">
														<option value="">Ambos</option>
														<option value="Pendiente">Pendiente</option>
														<option value="Pagado">Pagado</option>
							   					   </select>
												 
											   </div>
											</div>
											<!--Fin-->
										</div>
										
										<div id="ContenedorDiv">
					                           <div id="CajaBotones">
													<center>
													<input type="hidden" class="boton" name="formu" value="ok">
													<input type="submit" class="boton" name="" value="Generar Reporte" >
													</center>
											   </div>
					                    </div>
									</div>
						</form>	
						<div>
		<?php
		}
		
		if($_POST['formu']=='ok')
			$this->mostrar_reporte();
	}
	
	function obtener_grupo_id($usu_id)
	{
		$conec= new ADO();
		
		$sql="SELECT usu_gru_id FROM ad_usuario WHERE usu_id='$usu_id'";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->usu_gru_id; 
	}
	
	function obtener_id_interno_tbl_usuario($usu_id)
	{
		$conec= new ADO();
		
		$sql="SELECT usu_per_id FROM ad_usuario WHERE usu_id='$usu_id'";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->usu_per_id;
	}
	
	function mostrar_reporte()
	{		
		////
		$pagina="'contenido_reporte'";
		
		$page="'about:blank'";
		
		$extpage="'reportes'";
		
		$features="'left=100,width=800,height=500,top=0,scrollbars=yes'";
		
		$extra1="'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
			  </head>
			  <body>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
		$extra1.=" <a href=javascript:window.print();>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
		$extra2="'</center></body></html>'"; 
		
		$myday = setear_fecha(strtotime(date('Y-m-d')));
		////
				
		?>		
				<?php echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=repcomisiones\';"></td></tr></table><br><br>
				';?>
						
				 

			<div id="contenido_reporte" style="clear:both;";>
				<center>
					<table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
						<tr>
							<td width="35%" >
							 </br><strong><?php echo _nombre_empresa; ?></strong></br>
							<?php echo _datos_empresa; ?></br></br>
							</td>
							<td  width="30%" ><p align="center" ><strong><h3><center>COMISIONENES A VENDEDORES</center></h3></strong></p></td>
							<td  width="35%" ><div align="right"><br/><img src="imagenes/micro.png" /></div></td>
						  </tr>
					</table>
					<?php
					if($_POST['estado']=='Pendiente' || $_POST['estado']=='')
					{
						?>
						<h2>COMISIONES PENDIENTES</h2><table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
						<thead>
						<tr>
							<th>Vendedor</th>
							<th>Venta Nro</th>
							<th>Fecha</th>
							<th>Urbanización</th>
							<th>Manzano</th>					
							<th>Lote</th>
							<th>Monto</th>
							<th>Moneda</th>
							
						</tr>	
						</thead>
						<tbody>
							<?php
							$conec= new ADO();
							
							$cp="";
							
							if($_POST['vendedor']<>"")
							{
								$cp.=" and com_vdo_id='".$_POST['vendedor']."' ";
							}
							
							$sql="SELECT distinct com_id,com_monto,com_moneda,com_estado,com_ven_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,ven_fecha 
							FROM 
							comision inner join venta on (com_ven_id=ven_id)
							inner join lote on (ven_lot_id=lot_id) 
							inner join manzano on (lot_man_id=man_id)
							inner join urbanizacion on (man_urb_id=urb_id)
							inner join vendedor on (com_vdo_id=vdo_id)
							inner join interno on (vdo_int_id=int_id)
							where
							com_estado='Pendiente' and ven_estado!='Anulado'
							$cp							
							order by int_nombre,int_apellido,ven_fecha asc	
							";
							//echo $sql;
							$conec->ejecutar($sql);
							
							$num=$conec->get_num_registros();
							
							$conversor = new convertir();
							$tot=0;
							for($i=0;$i<$num;$i++)
							{
								$objeto=$conec->get_objeto();

								echo '<tr>';
														
										echo "<td>";
											echo $objeto->int_nombre.' '.$objeto->int_apellido;
										echo "&nbsp;</td>";
										echo "<td>";
											echo $objeto->com_ven_id;
										echo "&nbsp;</td>";
										echo "<td>";
											echo $conversor->get_fecha_latina($objeto->ven_fecha);
										echo "&nbsp;</td>";
										echo "<td>";
											echo $objeto->urb_nombre;
										echo "&nbsp;</td>";
										
										echo "<td>";
											echo $objeto->man_nro;
										echo "&nbsp;</td>";
										echo "<td>";
											echo $objeto->lot_nro;
										echo "&nbsp;</td>";
										
										echo "<td>";
											echo $objeto->com_monto;
										echo "&nbsp;</td>";
										$tot+=$objeto->com_monto;
										echo "<td>";
											if($objeto->ven_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';
										echo "&nbsp;</td>";
										
										
									echo "</tr>";
								
								$conec->siguiente();
							}
							?>
							</tbody>
                            <?php if($num>0){ ?>
							<tfoot>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>					
									<td>&nbsp;</td>
									<td><?php echo $tot; ?></td>
									<td>&nbsp;</td>
									
								</tr>	
							</tfoot>
                            <?php } ?>
							</table>
                            <?php 
							if($num==0)
								echo "<center>No se encontraron registros</center>"; 
							?>
						<?php
					}
					
				if($_POST['estado']=='Pagado' || $_POST['estado']=='')
				{
					?>
					<br><br><h2>COMISIONES PAGADAS</h2><table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
					<thead>
					<tr>
						<th>Vendedor</th>
						<th>Venta Nro</th>
						<th>Urbanización</th>
						<th>Manzano</th>					
						<th>Lote</th>
						<th>Monto</th>
						<th>Moneda</th>
					</tr>	
					</thead>
					<tbody>
						<?php
						$conec= new ADO();
						
						$cpa="";
							
						if($_POST['vendedor']<>"")
						{
							$cpa.=" and com_vdo_id='".$_POST['vendedor']."' ";
						}
						
						
						$sql="SELECT com_id,com_monto,com_moneda,com_estado,com_ven_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro 
						FROM 
						comision inner join venta on (com_ven_id=ven_id)
						inner join lote on (ven_lot_id=lot_id) 
						inner join manzano on (lot_man_id=man_id)
						inner join urbanizacion on (man_urb_id=urb_id)
						inner join vendedor on (com_vdo_id=vdo_id)
						inner join interno on (vdo_int_id=int_id)
						where 
						com_estado='Pagado'
						$cpa
						";
						
						$conec->ejecutar($sql);
						
						$num=$conec->get_num_registros();
						
						$conversor = new convertir();
						
						$tot=0;
						
						for($i=0;$i<$num;$i++)
						{
							$objeto=$conec->get_objeto();

							echo '<tr>';
													
									echo "<td>";
											echo $objeto->int_nombre.' '.$objeto->int_apellido;
										echo "&nbsp;</td>";
									echo "<td>";
										echo $objeto->com_ven_id;
									echo "&nbsp;</td>";
									
									echo "<td>";
										echo $objeto->urb_nombre;
									echo "&nbsp;</td>";
									echo "<td>";
										echo $objeto->man_nro;
									echo "&nbsp;</td>";
									echo "<td>";
										echo $objeto->lot_nro;
									echo "&nbsp;</td>";
									
									echo "<td>";
										echo $objeto->com_monto;
									echo "&nbsp;</td>";
									$tot+=$objeto->com_monto;
									echo "<td>";
										if($objeto->ven_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';
									echo "&nbsp;</td>";
								
								echo "</tr>";
							
							$conec->siguiente();
						}
						?>
						</tbody>
                        <?php if($num>0){ ?>
						<tfoot>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>					
								<td>&nbsp;</td>
								<td><?php echo $tot; ?></td>
								<td>&nbsp;</td>
							</tr>	
						</tfoot>
                        <?php } ?>
						</table>
                        <?php 
						if($num==0)
							echo "<center>No se encontraron registros</center>"; 
						?>
					<?php
				}				
					?>
					
					


					
				
				</center>
				<br/>
				<table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
			</div>
			<br>	
		<?php
	}
	
	
}
?>