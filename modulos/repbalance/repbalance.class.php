<?php

class REPBALANCE extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function REPBALANCE()
	{
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='repbalance';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('BALANCE GENERAL');
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
						<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=repbalance" method="POST" enctype="multipart/form-data">  
									<div id="FormSent">
										<div class="Subtitulo">Filtro del Reporte</div>
					                    <div id="ContenedorSeleccion">
											<div id="ContenedorDiv">
					                           <div class="Etiqueta" >Feha Inicio</div>
					                             <div id="CajaInput">
													<input readonly="readonly" class="caja_texto" name="inicio" id="inicio" size="12" value="<?php echo $_POST['inicio'];?>" type="text">
														<input name="but_fecha_pago2" id="but_fecha_pago2" class="boton_fecha" value="..." type="button">
														<script type="text/javascript">
																		Calendar.setup({inputField     : "inicio"
																						,ifFormat     :     "%Y-%m-%d",
																						button     :    "but_fecha_pago2"
																						});
														</script>
												</div>		
					                        </div>
											
											<div id="ContenedorDiv">
					                           <div class="Etiqueta" >Fecha Fin</div>
					                             <div id="CajaInput">
													<input readonly="readonly" class="caja_texto" name="fin" id="fin" size="12" value="<?php echo $_POST['fin'];?>" type="text">
														<input name="but_fecha_pago" id="but_fecha_pago" class="boton_fecha" value="..." type="button">
														<script type="text/javascript">
																		Calendar.setup({inputField     : "fin"
																						,ifFormat     :     "%Y-%m-%d",
																						button     :    "but_fecha_pago"
																						});
														</script>
												</div>		
					                        </div>									
											
											
												
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
	
	
	
	
	function monto($cue_id,$mon,&$monin_bs,&$monin_sus,&$moneg_bs,&$moneg_sus)
	{	    
	    $conec= new ADO();
		
		$sql = "SELECT cue_id,cue_tcu_id
		        FROM cuenta
				WHERE cue_padre_id = $cue_id";
		
		
		$conec->ejecutar($sql);
		
		$monto_total = 0;
		
		$num=$conec->get_num_registros();	
		
	    if ($num > 0)
		{
		   for($i=0;$i<$num;$i++)
		   {
			   $objeto=$conec->get_objeto();
			   $this->monto($objeto->cue_id,$mon,$monin_bs,$monin_sus,$moneg_bs,$moneg_sus);
			   $conec->siguiente();
			  // echo "monto : ".$monto_total."<br>";
		   } 	    
           		   
		}
		else
		{
		  // $conec= new ADO();
		 
        $fecha ="";
        if($_POST['inicio']<>"")
		{
			$fecha.=" and cmp_fecha >= '".$_POST['inicio']."' ";
			
			if($_POST['fin']<>"")
				$fecha.=" and cmp_fecha <='".$_POST['fin']."' ";
		}
		else
		{
			if($_POST['fin']<>"")
				$fecha.=" and cmp_fecha <='".$_POST['fin']."' ";
		}
			  
		  
		 
		$sql = "SELECT SUM(ABS(cde_monto)) as monto,SUM(ABS(cde_monto_sus)) as montosus
				FROM comprobante_detalle,comprobante
				WHERE cmp_estado = 1 and (cde_monto > 0 or cde_monto_sus > 0) and cde_cmp_id= cmp_id and cde_cue_id =$cue_id ".$aux.$fecha."
				GROUP BY cmp_mon_id";	
				
		$sql2 = "SELECT SUM(ABS(cde_monto)) as monto,SUM(ABS(cde_monto_sus)) as montosus
				FROM comprobante_detalle,comprobante
				WHERE cmp_estado = 1 and (cde_monto < 0 or cde_monto_sus < 0) and cde_cmp_id= cmp_id and cde_cue_id =$cue_id ".$aux.$fecha."
				GROUP BY cmp_mon_id";		
		  
		//echo $sql;	  
		//echo $sql2;
		
		  
           $conec->ejecutar($sql);							  
		   $num2=$conec->get_num_registros();	
           for($j=0;$j<$num2;$j++)
		   {
			   $objeto=$conec->get_objeto();
			   
			  
			       $monin_bs  +=  $objeto->monto;
			       $monin_sus	+=  $objeto->montosus;	   
			   
			   $conec->siguiente();			 
		   }   

           $conec->ejecutar($sql2);							  
		   $num2=$conec->get_num_registros();	
           for($j=0;$j<$num2;$j++)
		   {
			   $objeto=$conec->get_objeto();
			   
			   
			       $moneg_bs  +=  $objeto->monto;
			       $moneg_sus	+=  $objeto->montosus;	   
			  
			   $conec->siguiente();			 
		   }     
           		   
		}
	
	}
		

	
	function hijos($cue_id,$cad)
	{
	    $conec= new ADO();
	    $sql = "SELECT cue_id, cue_numero, cue_nivel,cue_descripcion, cue_padre_id,cue_tcu_id
				FROM cuenta
				WHERE cue_padre_id = $cue_id "; 
		
	    $conec->ejecutar($sql);		
		$num=$conec->get_num_registros();
	    
	    for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();
			
		
			$ne = "<b>";
			   $nec = "</b>";
			if ($objeto->cue_nivel == 3)
			{
			   	$ne = "";
			$nec = "";
            }
			$montoin_bs = 0;
		    $montoin_sus = 0;
			$montoeg_bs = 0;
		    $montoeg_sus = 0;
			$this->monto($objeto->cue_id,$_POST['cmp_mon_id'],$montoin_bs,$montoin_sus,$montoeg_bs,$montoeg_sus);
			
			//if ($monto_bs > 0)
			//{
			echo '<tr>';			
					
					echo "<td>";
					    
						echo $ne.$cad.$objeto->cue_numero.$nec;
					echo "</td>";
					
					echo "<td>";
						echo $ne.$cad.$objeto->cue_descripcion.$nec;
					echo "</td>";		
					echo "<td>";
						echo $ne.$cad.$montoin_bs.$nec;
					echo "</td>";
					echo "<td>";
					
						echo $ne.$cad.$montoin_sus.$nec;
					echo "</td>";
					echo "<td>";
						echo $ne.$cad.$montoeg_bs.$nec;
					echo "</td>";
					echo "<td>";
					
						echo $ne.$cad.$montoeg_sus.$nec;
					echo "</td>";
					echo "<td>";
						echo $ne.$cad.($montoin_bs-$montoeg_bs).$nec;
					echo "</td>";
					echo "<td>";
					
						echo $ne.$cad.($montoin_sus-$montoeg_sus).$nec;
					echo "</td>";
					
				echo "</tr>";		
				
              $this->hijos($objeto->cue_id,$cad."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");				
			//}
			$conec->siguiente();
		}
	}
	
	function centrocosto($id)
	{
	  $conec= new ADO();
		$sql = "SELECT cco_descripcion
		        FROM centrocosto
				WHERE cco_id = $id";
		$conec->ejecutar($sql);
	    $objeto=$conec->get_objeto();
		return $objeto->cco_descripcion;
	}
	
	function mostrar_reporte()
	{		
		$conec= new ADO();		
		
		
		$sql =  "SELECT cue_id, cue_numero, cue_descripcion, cue_padre_id,cue_tcu_id
				FROM cuenta
				WHERE cue_tcu_id = 4 and cue_padre_id = 0"; 
					
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$bol=0;
		$dol=0;
		
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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=repbalance\';"></td></tr></table><br><br>
				';?>
						
				 

			<div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="40%">
					<strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong><BR><BR>
					<strong><?php if(isset($_POST['cco_id']) && !empty($_POST['cco_id'])) echo $this->centrocosto($_POST['cco_id']); else echo "OFICINA CENTRAL";?></strong><BR>
				    
				   </td>
				    <td><p align="center" ><strong><h3>BALANCE GENERAL</h3></strong><BR><?if ($_POST['inicio']<>"") echo '<strong>Del:</strong> '.date('d/m/Y',strtotime($_POST['inicio']))?><?if ($_POST['fin']<>"") echo ' <strong>Al:</strong> '.date('d/m/Y',strtotime($_POST['fin']))?></p></td>
				    <td width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
		    <td colspan="2">
		    <strong>Impreso el: </strong> <?php echo $myday;?> <br><br></td>
			<td colspan="2"><strong> <?php if($_POST['cmp_mon_id']=="1") echo "COMPROBANTES EN BOLIVIANOS"; else if($_POST['cmp_mon_id']=="2")echo "COMPROBANTES EN DOLARES";?><strong></td>
		    <td></td>
		  </tr>
				 
			</table>
			<table   width="90%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>
						<b>&nbsp;</b>
					</th>
					<th>
						<b>&nbsp;</b>
					</th>					
					<th colspan="2">
						<b>INGRESOS</b>
					</th>
					<th colspan="2">
						<b>EGRESOS</b>
					</th>
					<th colspan="2">
						<b>SALDO</b>
					</th>					
				</tr>
				<tr>
					<th>
						<b>Codigo</b>
					</th>
					<th>
						<b>Cuenta</b>
					</th>					
					<th>
						<b>Monto Bs.</b>
					</th>
					<th>
						<b>Monto $us.</b>
					</th>
					<th>
						<b>Monto Bs.</b>
					</th>
					<th>
						<b>Monto $us.</b>
					</th>
					<th>
						<b>Monto Bs.</b>
					</th>
					<th>
						<b>Monto $us.</b>
					</th>
				</tr>
				</thead>
				<tbody>
		<?php				
		
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();
			$montoin_bs = 0;
			$montoin_sus = 0;
			$montoeg_bs = 0;
			$montoeg_sus = 0;
			$this->monto($objeto->cue_id,$_POST['cmp_mon_id'],$montoin_bs,$montoin_sus,$montoeg_bs,$montoeg_sus);
           // if ($monto_bs > 0)
		//	{
			echo '<tr>';			
					
					echo "<td>";
						echo "<b>".$objeto->cue_numero."</b>";
					echo "</td>";
					
					echo "<td>";
						echo "<b>".$objeto->cue_descripcion."</b>";
					echo "</td>";		
					
					echo "<td>";						
						echo "<b>".$montoin_bs."</b>";
					echo "</td>";
					
					echo "<td>";						
						echo "<b>".$montoin_sus."</b>";
					echo "</td>";
					
					echo "<td>";						
						echo "<b>".$montoeg_bs."</b>";
					echo "</td>";
					
					echo "<td>";						
						echo "<b>".$montoeg_sus."</b>";
					echo "</td>";
					echo "<td>";						
						echo "<b>".($montoin_bs-$montoeg_bs)."</b>";
					echo "</td>";
					
					echo "<td>";						
						echo "<b>".($montoin_sus-$montoeg_sus)."</b>";
					echo "</td>";
					
				echo "</tr>";		
            $this->hijos($objeto->cue_id,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");				
			//}
			$conec->siguiente();
		}
		?>
	
		
		</tbody>
		</table>
		</center>
		<br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
		</div><br>
		<?php
	}
	
	
}
?>