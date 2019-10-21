<?php

class REPESTADORESULTADO extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function REPESTADORESULTADO()
	{
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='repestadoresultado';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('ESTADO DE INGRESOS/EGRESOS');
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
                        <!--MaskedInput-->
						<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                        <!--MaskedInput-->						
						<div id="Contenedor_NuevaSentencia">
						<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=repestadoresultado" method="POST" enctype="multipart/form-data">  
									<div id="FormSent">
										<div class="Subtitulo">Filtro del Reporte</div>
					                    <div id="ContenedorSeleccion">
                                        	<!--Inicio
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
											Fin-->
                                            <!--Inicio
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
                                            Fin-->
                                            <!--Inicio-->
											<div id="ContenedorDiv">
					                           <div class="Etiqueta" >Fecha Inicio</div>
					                             <div id="CajaInput">
													<input class="caja_texto" name="inicio" id="inicio" size="12" value="<?php echo $_POST['inicio'];?>" type="text">
								<span class="flechas1">(DD/MM/AAAA)</span>
												</div>		
					                        </div>
											<!--Fin-->
                                            <!--Inicio-->
											<div id="ContenedorDiv">
					                           <div class="Etiqueta" >Fecha Fin</div>
					                             <div id="CajaInput">
													<input class="caja_texto" name="fin" id="fin" size="12" value="<?php echo $_POST['fin'];?>" type="text">
								<span class="flechas1">(DD/MM/AAAA)</span>
												</div>		
					                        </div>
											<!--Fin-->
                                            <!--Inicio-->
											<div id="ContenedorDiv">
										   <div class="Etiqueta" >Centro Costo</div>
										   <div id="CajaInput">
											   <select name="cco_id" class="caja_texto">
											   <option value="">Seleccione</option>
											   <?php 		
												$fun=NEW FUNCIONES;		
												$fun->combo("select cco_id as id,cco_descripcion as nombre from centrocosto where cco_padre_id = 0 order by cco_descripcion asc",$_POST['cco_id']);		
												?>
											   </select>
											</div>
											</div>
                                            <!--Fin-->
                                            <!--Inicio-->
											<div id="ContenedorDiv">
											   <div class="Etiqueta" >Tipo Comprobante</div>
											   <div id="CajaInput">
											   <select name="tco_id" class="caja_texto" onchange="cargar_cuenta(this.value);">
											   <option value="">Seleccione</option>
											   <?php 		
												$fun=NEW FUNCIONES;		
												$fun->combo("select tco_id as id,tco_descripcion as nombre from tipo_comprobante order by tco_id asc limit 0,2",$_POST['cmp_tco_id']);		
												?>
											   </select>
											   </div>
											</div>
											<!--Fin-->
											<!--Inicio
											<div id="ContenedorDiv">
											   <div class="Etiqueta" >Moneda</div>
											   <div id="CajaInput">
											   <select name="cmp_mon_id" class="caja_texto">
											   <option value="">Seleccione</option>
											   <?php 		
												$fun=NEW FUNCIONES;	
				                               //  if (!isset($_POST['cmp_mon_id']))
				                                 //   $_POST['cmp_mon_id'] = 1;      								 
												$fun->combo("select mon_id as id,mon_descripcion as nombre from moneda order by mon_id asc",$_POST['cmp_mon_id']);		
												?>
											   </select>
											   </div>
											</div>
											Fin-->
												
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
                        <script>
							jQuery(function($){
							   $("#inicio").mask("99/99/9999");
							   $("#fin").mask("99/99/9999");  
							});
						</script>
		<?php
		}
		
		if($_POST['formu']=='ok')
			$this->mostrar_reporte();
	}
	
	
	
	
	function monto($cue_id,$mon,&$mon_bs,&$mon_sus)
	{	    
	    $conversor=new convertir();
		
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
			   $this->monto($objeto->cue_id,$mon,$mon_bs,$mon_sus);
			   $conec->siguiente();
			  // echo "monto : ".$monto_total."<br>";
		   } 	    
           		   
		}
		else
		{
		  // $conec= new ADO();
		 
          $aux="";		 
		  if ($_POST['cmp_mon_id']!="")		  
		      $aux = " and cmp_mon_id =".$_POST['cmp_mon_id'];	
        $fecha ="";
        if($_POST['inicio']<>"")
		{
			$fecha.=" and cmp_fecha >= '".$conversor->get_fecha_mysql($_POST['inicio'])."' ";
			
			if($_POST['fin']<>"")
				$fecha.=" and cmp_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
		}
		else
		{
			if($_POST['fin']<>"")
				$fecha.=" and cmp_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
		}
			  
		  
		  if (isset($_POST['cco_id']) && !empty($_POST['cco_id']))
		  {
		       $sql = "SELECT SUM(cde_monto) as monto,cmp_tc,cmp_mon_id,cmp_id,SUM(cde_monto_sus) as montosus
                       FROM comprobante_detalle,comprobante
                       WHERE cmp_estado = 1 and cde_cmp_id= cmp_id and cde_cue_id =$cue_id and cde_cco_id =".$_POST['cco_id'].$aux.$fecha."
					   GROUP BY cmp_mon_id";
		  }
		  else
		  {
		    $sql = "SELECT SUM(cde_monto) as monto,cmp_tc,cmp_mon_id,cmp_id,SUM(cde_monto_sus) as montosus
                    FROM comprobante_detalle,comprobante
                    WHERE cmp_estado = 1 and cde_cmp_id= cmp_id and cde_cue_id =$cue_id ".$aux.$fecha."
					GROUP BY cmp_mon_id";	
		  
		  }		  
			//echo $sql;
           $conec->ejecutar($sql);							  
		   $num2=$conec->get_num_registros();	
           for($j=0;$j<$num2;$j++)
		   {
			   $objeto=$conec->get_objeto();
				
			   $mon_bs  +=  $objeto->monto;
			   $mon_sus	+=  $objeto->montosus;	   
			   
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
			$monto_bs = 0;
		    $monto_sus = 0;
			$this->monto($objeto->cue_id,$_POST['cmp_mon_id'],$monto_bs,$monto_sus);
			
			if (abs($monto_bs) > 0 || abs($monto_sus) > 0)
			{
			echo '<tr>';			
					
					echo "<td>";
					    
						echo $ne.$cad.$objeto->cue_numero.$nec;
					echo "</td>";
					
					echo "<td>";
						echo $ne.$cad.$objeto->cue_descripcion.$nec;
					echo "</td>";		
					echo "<td>";
						echo $ne.$cad.abs($monto_bs).$nec;
					echo "</td>";
					echo "<td>";
					
						echo $ne.$cad.abs($monto_sus).$nec;
					echo "</td>";
					
				echo "</tr>";		
				
              $this->hijos($objeto->cue_id,$cad."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");				
			}
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
		$conversor= new convertir();
		
		$conec= new ADO();		
		
		if (isset($_POST['tco_id']) && !empty($_POST['tco_id']))
		{
		    $sql = "SELECT cue_id, cue_numero, cue_descripcion, cue_padre_id,cue_tcu_id
					FROM cuenta
					WHERE cue_tcu_id = ".$_POST['tco_id']."  and cue_padre_id = 0 "; 
		}
		else
		{
		    $sql =  "SELECT cue_id, cue_numero, cue_descripcion, cue_padre_id,cue_tcu_id
					FROM cuenta
					WHERE cue_tcu_id <= 2 and cue_padre_id = 0"; 
					

		}
		
		//echo $sql;
		
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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=repptranse\';"></td></tr></table><br><br>
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
				    <td><p align="center" ><strong><h3><center>ESTADO DE INGRESOS/EGRESOS</center></h3></strong><BR><?php if ($_POST['inicio']<>"") echo '<strong>Del:</strong> '.date('d/m/Y',strtotime($conversor->get_fecha_mysql($_POST['inicio'])))?><?php if ($_POST['fin']<>"") echo ' <strong>Al:</strong> '.date('d/m/Y',strtotime($conversor->get_fecha_mysql($_POST['fin'])))?></p></td>
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
				</tr>
				</thead>
				<tbody>
		<?php				
		
		$monto_totalbs= 0;
		$monto_totalsus = 0;
		
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();
			$monto_bs = 0;
			$monto_sus = 0;
			$this->monto($objeto->cue_id,$_POST['cmp_mon_id'],$monto_bs,$monto_sus);
			$monto_totalbs+=$monto_bs;
			$monto_totalsus+=$monto_sus;
            if (abs($monto_bs) > 0 || abs($monto_sus) > 0)
			{
			echo '<tr>';			
					
					echo "<td>";
						echo "<b>".$objeto->cue_numero."</b>";
					echo "</td>";
					
					echo "<td>";
						echo "<b>".$objeto->cue_descripcion."</b>";
					echo "</td>";		
					
					echo "<td>";						
						echo "<font size='3'><b>".abs($monto_bs)."</b></font>";
					echo "</td>";
					
					echo "<td>";						
						echo "<font size='3'><b>".abs($monto_sus)."</b></font>";
					echo "</td>";
					
				echo "</tr>";		
				$this->hijos($objeto->cue_id,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");				
			}
			$conec->siguiente();
		}
		?>
		<tr>
					<td class="">&nbsp;
						
					</td>
					<td class="">&nbsp;
						
					</td>
					<td class="">&nbsp;
						
					</td>
					
					<td class="">
						<b><?php echo $mon;?></b>
					</td>
				</tr>
		<tr>
					<td colspan ="2"class="" align="right">
						<font size="4"><b>SALDO=></b></font>
					</td>
					
					<td class="">
						<font size="4"><b><?php echo ($monto_totalbs*-1);?></b></font>
					</td>
					
					<td class="">
						<font size="4"><b><?php echo ($monto_totalsus*-1);?></b></font>
					</td>
				</tr>
		</tbody>
		</table>
		</center>
		<br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
		</div><br>
		<?php
	}
	
	
}
?>