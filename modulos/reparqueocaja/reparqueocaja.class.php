<?php

class REPARQUEOCAJA extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	function REPARQUEOCAJA()
	{
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='reparqueocaja';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('ARQUEO DE CAJA');
		
		$this->usu=new USUARIO;
	}
	
	
	function dibujar_busqueda()
	{
			$this->formulario();
	}
	
	function formulario()
	{
	 ?>
	    <script type="text/javascript">
		    function enviar(frm,n)
			{
			  if (n==0)
			  {
			   var caja = document.frm_sentencia.caja_id.options[document.frm_sentencia.caja_id.selectedIndex].value;
			   if (caja!="")
			   {			      
			      frm.submit();				  
			   }
			   else
			     $.prompt('Seleccione un Caja',{ opacity: 0.8 });
			  }
			  else
			     frm.submit();				  
			}
		    function cargar_cajero(id)
			{
				var	valores="tid="+id+"&t=4";				
				ejecutar_ajax('ajax.php','cajero',valores,'POST');					
				
			}
			
			function seleccionar(frm,op)
			{
			    for (var i=0;i<frm.elements.length;i++)
				{
				     var e=frm.elements[i];					 
					      if (op==1)					
					          e.checked = true;										
						  else					
						      e.checked = false;							 
			    }
			}
			function hay_seleccionados()
			{
			   var Hay = false;
			   for (var i=0;i<document.frm_sentencia.elements.length;i++)
				{
				     var e=document.frm_sentencia.elements[i];					 
					  if (e.checked)
					  {
						 Hay = true;
						 break;
					  }					
			    }
			  return Hay;
			}
		</script>
	 <?
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
						<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=reparqueocaja" method="POST" enctype="multipart/form-data">  
									<div id="FormSent" style="width:65%">
										<div class="Subtitulo">Filtro del Reporte</div>
					                    <div id="ContenedorSeleccion">
                                        	<!--Inicio
											<div id="ContenedorDiv">
					                           <div class="Etiqueta" >Feha Inicio</div>
					                             <div id="CajaInput">
													<input readonly="readonly" class="caja_texto" name="inicio" id="inicio" size="12" value="" type="text">
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
													<input readonly="readonly" class="caja_texto" name="fin" id="fin" size="12" value="" type="text">
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
                                     <?php
									    $conec= new ADO();	
									    $sql="select cja_cue_id from cajero where cja_usu_id = '".$this->usu->get_id()."' and cja_estado=1";			
										$conec->ejecutar($sql);	
										$nume=$conec->get_num_registros();
										//echo $nume;
										if ($nume == 0 )
									    {
									 
									 ?>
											
											<div id="ContenedorDiv">
											   <div class="Etiqueta" ><span class="flechas1">*</span>Caja</div>
											   <div id="CajaInput">
											   <select name="caja_id" class="caja_texto" onchange="cargar_cajero(this.value);">
											   <option value="">Seleccione</option>
											   <?php 		
												$fun=NEW FUNCIONES;		
												$fun->combo("select cue_id as id,cue_descripcion as nombre from cuenta  where cue_padre_id = 4 order by cue_id asc",$_POST['caja_id']);		
												?>
											   </select>
											   </div>
											</div>
											
											<!--Inicio-->
											<div id="ContenedorDiv">
											   <div class="Etiqueta" >Cajero</div>
											   <div id="CajaInput">
											       <div id='cajero'>
												   <select name="cajero_id" class="caja_texto">
												   <option value="">Seleccione</option>											   
												   </select>
												   </div>
											   </div>
											</div>										
										<?
                                          }										
										?>		
										<div id="ContenedorDiv">
										   <div class="Etiqueta" >Persona</div>
										   <div id="CajaInput">
										   <select style="width:300px;" name="int_id" id="int_id" class="caja_texto">
										   <option value="">Seleccione</option>
										   <?php 		
											$fun=NEW FUNCIONES;		
											$fun->combo(" select int_id as id,CONCAT(int_apellido,' ',int_nombre) as nombre from interno order by int_apellido,int_nombre asc ",$_POST['int_id']);		
											?>
										   </select>
										   </div>
										</div>
										<div id="ContenedorDiv">
											   <div class="Etiqueta" >Tipo Reporte</div>
											   <div id="CajaInput">											       
												   <select name="reporte_id" class="caja_texto">
												   <option value="resumido">Resumido</option>
												   <option value="detallado">Detallado</option>												   
												   </select>
												   
											   </div>
											</div>

								        <div id="ContenedorDiv">
											   <div class="Etiqueta" >Cuenta Traspasos</div>
											   <div id="CajaInput">											       
												   <select name="traspasos" class="caja_texto">
												   <option value="no">No</option>
												   <option value="si">Si</option>												   
												   </select>
												   
											   </div>
											</div>	
										</div>
										
										
										
										
										<div id="ContenedorDiv">
					                           <div id="CajaBotones">
													<center>
													<input type="hidden" class="boton" name="formu" value="ok">
													<input type="button" class="boton" name="" value="Generar Reporte" onClick="enviar(this.form,<? if ($nume == 0 ){ echo "0";}else{ echo "1";}?>);">
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
	
	
	
		
    function detalle($cajeros,$tipo,&$total_bs,&$total_sus)
	{
	    $conversor=new convertir();
		
        $fecha ="";
		$fecha_tras ="";
		$interno = "";
		
        if($_POST['inicio']<>"")
		{
			$fecha.=" and cmp_fecha >= '".$conversor->get_fecha_mysql($_POST['inicio'])."' ";
			$fecha_tras.=" and arq_fecha >= '".$conversor->get_fecha_mysql($_POST['inicio'])."' ";
			
			if($_POST['fin']<>"")
			{
				$fecha.=" and cmp_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
				$fecha_tras.=" and arq_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
			}
		}
		else
		{
			if($_POST['fin']<>"")
			{
				$fecha.=" and cmp_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
				$fecha_tras.=" and arq_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
			}
		}
		
		if (isset($_POST['int_id']) && !empty($_POST['int_id']))
		{
		     $interno = " and int_id =".$_POST['int_id'];
		}
		
			
			
			
	    $conec= new ADO();
		
	    $sql = "SELECT cmp_id,cmp_fecha, cmp_tc, cmp_usu_id, cmp_interesado, CONCAT(int_apellido,' ',int_nombre) as persona, cmp_mon_id, cde_monto, cue_descripcion,cco_descripcion,cde_glosa,cde_monto_sus
				FROM comprobante inner join comprobante_detalle on (cde_cmp_id = cmp_id)
							     left join interno on (cmp_int_id = int_id)
							     inner join cuenta on (cde_cue_id=cue_id) 
								 inner join centrocosto on (cde_cco_id=cco_id)
				WHERE
				     cmp_estado = 1 and cmp_tco_id=$tipo  and cmp_usu_id in ($cajeros) ".$fecha.$interno." 
				ORDER BY cue_numero"; 
	
	    $conec->ejecutar($sql);		
		$num=$conec->get_num_registros();
		
		if ($num > 0 || $_POST['traspasos']=='si')
		{
		    $cuenta=""; $monto_bs = 0; $monto_sus = 0; 
		  	for($i=0;$i<$num;$i++)
			{
				$objeto=$conec->get_objeto();
				
				if  (trim($objeto->cue_descripcion) != trim($cuenta))
				{
				   if ($i==0)
				   {
				      if ($_POST['reporte_id']=="detallado")
					  {
				         echo "<tr><td colspan='7' ><b>".$objeto->cue_descripcion."</b></td></tr>";
					  }
				      $cuenta = $objeto->cue_descripcion;
				   }
                   else				   
				   {
				      if ($_POST['reporte_id']=="detallado")
					  {
						echo "<tr><td colspan='5' align='right'><b>Subtotal :</b></td><td><b>".abs(round($monto_bs,2))."</b></td><td><b>".abs(round($monto_sus,2))."</b></td></tr>";
					    echo "<tr><td colspan='7' ><b>".$objeto->cue_descripcion."</b></td></tr>";
					  }
					  else
					  {
					     echo "<tr><td colspan='5' ><b>".$cuenta."</b></td><td><b>".abs(round($monto_bs,2))."</b></td><td><b>".abs(round($monto_sus,2))."</b></td></tr>";					     
					  }
					  
					   $cuenta = $objeto->cue_descripcion;
					   $monto_bs = 0;
					   $monto_sus = 0;
				   }			   
				}             
			   $mon_bs  =  $objeto->cde_monto;
			   $mon_sus	=  $objeto->cde_monto_sus;	   
			   $monto_bs+=$mon_bs;
			   $monto_sus+=$mon_sus;
			   $total_bs +=$mon_bs;
			   $total_sus+=$mon_sus;
			
		   if ($_POST['reporte_id']=="detallado")		   
		   {
			  echo '<tr>';			
					
					echo "<td>";					    
						echo $objeto->cmp_fecha;
					echo "</td>";
					echo "<td>";					    
						echo $objeto->cmp_usu_id;
					echo "</td>";
					echo "<td>";
						echo $objeto->cmp_id;
					echo "</td>";		
					echo "<td>";
						echo $objeto->cmp_tc;
					echo "</td>";
					echo "<td>";
					  if (trim($objeto->persona)!="")
						 echo $objeto->persona;
					  else
					     echo $objeto->cmp_interesado;
					  echo ",".$objeto->cde_glosa.",".$objeto->cco_descripcion;
					echo "</td>";
					echo "<td>";					
						echo abs($mon_bs);
					echo "</td>";
					echo "<td>";					
						echo abs($mon_sus);
					echo "</td>";
					
				echo "</tr>";		
              }  
				if (($i+1)==$num)
				{
				   
				    if ($_POST['reporte_id']=="detallado")
					  {
						echo "<tr><td colspan='5' align='right'><b>Subtotal :</b></td><td><b>".abs(round($monto_bs,2))."</b></td><td><b>".abs(round($monto_sus,2))."</b></td></tr>";
					  }
					  else
					  {
					     echo "<tr><td colspan='5' ><b>".$cuenta."</b></td><td><b>".abs(round($monto_bs,2))."</b></td><td><b>".abs(round($monto_sus,2))."</b></td></tr>";					     
					  }				   
				  // echo "<tr><td colspan='5' align='right'><b><font size='4'>Total :</font></b></td><td><b><font size='4'>".abs($total_bs)."</font></b></td><td><b><font size='4'>".abs($total_sus)."</font></b></td></tr>";
				}
		
				$conec->siguiente();
	     	}	 
		 
		   $caja_id = $this->cuenta_caja();
		   
		   
		   
		if ($_POST['traspasos']=='si')
        {		
		   
		   if ($tipo == 1)
		        $sql = "SELECT arq_id, arq_fecha, arq_usu_id, arq_cue_origen, arq_cue_destino, arq_monto, arq_estado, arq_observacion, arq_tc, arq_mon_id,arq_monto_sus
				     	FROM arqueo
					    WHERE arq_estado = 1 and arq_cue_destino = ".$caja_id.$fecha_tras;
		   else
		       $sql = "SELECT arq_id, arq_fecha, arq_usu_id, arq_cue_origen, arq_cue_destino, arq_monto, arq_estado, arq_observacion, arq_tc, arq_mon_id,arq_monto_sus
				     	FROM arqueo
					    WHERE arq_estado = 1 and arq_cue_origen = ".$caja_id.$fecha_tras;
		  
			
		   $conec->ejecutar($sql);		
		   $num=$conec->get_num_registros();
			
		   if ($num > 0)
           {
		      if ($_POST['reporte_id']=="detallado")
				 echo "<tr><td colspan='7' ><b>TRASPASOS</b></td></tr>";
			   $monto_bs=0;
			   $monto_sus=0;
		       for($i=0;$i<$num;$i++)
			   {
				  $objeto=$conec->get_objeto();
				     
					 if ($tipo == 1)
					  {  $objeto->arq_monto *=-1;
						$objeto->arq_monto_sus *=-1;
						
					 }
					 
					 
				    
					    $mon_bs  =  $objeto->arq_monto;
					    $mon_sus =  $objeto->arq_monto_sus;				    
					 
				        $monto_bs+=$mon_bs;
					    $monto_sus+=round($mon_sus,2);
					    $total_bs +=$mon_bs;
					    $total_sus+=round($mon_sus,2);  
				  
				  
				 if ($_POST['reporte_id']=="detallado")
				 {
				  echo '<tr>';			
					
					echo "<td>";					    
						echo $objeto->arq_fecha;
					echo "</td>";
					echo "<td>";					    
						echo $objeto->arq_usu_id;
					echo "</td>";
					echo "<td>";
						echo $objeto->arq_id;
					echo "</td>";		
					echo "<td>";
						echo $objeto->arq_tc;
					echo "</td>";
					echo "<td>";
						  echo $objeto->arq_observacion;
					echo "</td>";
					echo "<td>";					
						echo abs($mon_bs);
					echo "</td>";
					echo "<td>";					
						echo abs(round($mon_sus,2));
					echo "</td>";
					
				echo "</tr>";		
			    }
				$conec->siguiente();
			   }
			   if ($_POST['reporte_id']=="detallado")
			   {
		          echo "<tr><td colspan='5' align='right'><b>Subtotal :</b></td><td><b>".abs(round($monto_bs,2))."</b></td><td><b>".abs(round($monto_sus,2))."</b></td></tr>";
			   }
			   else
			   {
			     echo "<tr><td colspan='5' ><b>TRASPASOS</b></td><td><b>".abs(round($monto_bs,2))."</b></td><td><b>".abs(round($monto_sus,2))."</b></td></tr>";					     
			   }
			    
		   }		   
			
		 }
          
		echo "<tr><td colspan='5' align='right'><b><font size='4'>Total :</font></b></td><td><b><font size='4'>".abs($total_bs)."</font></b></td><td><b><font size='4'>".abs($total_sus)."</font></b></td></tr>";
			
	    }
	
	}
	
	 
	
	function interno()
	{
	    $conec= new ADO();		
	    $sql = "select int_nombre,int_apellido from interno where int_id =".$_POST['int_id'];   
		$conec->ejecutar($sql);
	    $objeto=$conec->get_objeto();
		return $objeto->int_apellido.' '.$objeto->int_nombre;
	}
	
	function caja()
	{
	   
	    $conec= new ADO();		
	  if(isset($_POST['caja_id']) && !empty($_POST['caja_id'])) 
	  {	     
		$sql = "SELECT cue_descripcion
		        FROM cuenta
				WHERE cue_id =".$_POST['caja_id'];   
	  }
	  else
	  {     
		   $sql="select cue_descripcion 
		   from cajero inner join cuenta on (cja_cue_id = cue_id) 
		   where cja_usu_id = '".$this->usu->get_id()."' and cja_estado=1";				
	  }
	  
	  $conec->ejecutar($sql);
	    $objeto=$conec->get_objeto();
		return $objeto->cue_descripcion;
	  
	}
	
	function cuenta_caja()
	{
	     $conec= new ADO();		
	  if(isset($_POST['caja_id']) && !empty($_POST['caja_id'])) 
	  {	     
		return   $_POST['caja_id'];
	  }
	  else
	  {     
		   $sql="select cue_id
		   from cajero inner join cuenta on (cja_cue_id = cue_id) 
		   where cja_usu_id = '".$this->usu->get_id()."' and cja_estado=1";	
		   
			$conec->ejecutar($sql);
				    $objeto=$conec->get_objeto();
					return $objeto->cue_id;		   
	  }
	  
	  
	}
	
	function mostrar_reporte()
	{		
		$conversor=new convertir();
		
		$conec= new ADO();			
		
		if (isset($_POST['cajero_id']) && !empty($_POST['cajero_id']))
		{
		    $cajeros = "'".$_POST['cajero_id']."'";
		}
		else
		{
		   if (isset($_POST['caja_id']) && !empty($_POST['caja_id']))
		   {
		
			    $sql =  "SELECT cja_usu_id
						FROM cajero"; 
	            $conec->ejecutar($sql);
			    $num=$conec->get_num_registros();	
				$vec = array();
	            for($i=0;$i<$num;$i++)
				{
					$objeto=$conec->get_objeto();			
	           		$vec[] = "'".$objeto->cja_usu_id."'"; 		
				    $conec->siguiente();
			    }
				$cajeros = implode(",",$vec);
           }
           else
           {		     
		        $cajeros = "'".$this->usu->get_id()."'";
		   }		   
		}
		
		
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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=reparqueocaja\';"></td></tr></table><br><br>
				';?>
						
				 

			<div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="40%">
					<strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong><BR><BR>
					<strong><?php echo $this->caja(); ?></strong><BR>
				   </td>
				    <td><p align="center" ><strong><h3><center>ARQUEO DE CAJA </center></h3></strong><BR><?php if ($_POST['inicio']<>"") echo '<strong>Del:</strong> '.date('d/m/Y',strtotime($conversor->get_fecha_mysql($_POST['inicio'])))?><?php if ($_POST['fin']<>"") echo ' <strong>Al:</strong> '.date('d/m/Y',strtotime($conversor->get_fecha_mysql($_POST['fin'])))?></p></td>
				    <td width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
		    <td colspan="2">
		    <strong>Usuario: </strong> <?php echo $this->nombre_persona($this->usu->get_id());?> <br>
            <? if (isset($_POST['int_id']) && !empty($_POST['int_id'])){ echo "<strong>Persona: </strong>".$this->interno();} ?>		
			<br></td>
			<td colspan="2"><strong> <?php if($_POST['cmp_mon_id']=="1") echo "COMPROBANTES EN BOLIVIANOS"; else if($_POST['cmp_mon_id']=="2")echo "COMPROBANTES EN DOLARES";?><strong></td>
		    <td></td>
		  </tr>
				 
			</table>
			<h1>INGRESOS</h1>
			<table   width="90%"  class="tablaReporte" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th>
					<b>Fecha</b>
				</th>
				<th>
					<b>Usuario</b>
				</th>
				<th>
					<b>No. Nota</b>
				</th>					
				<th>
					<b>TC</b>
				</th>
				<th>
					<b>Detalle</b>
				</th>
				<th>
					<b>Bs.</b>
				</th>
				<th>
					<b>$us.</b>
				</th>
			</tr>
			</thead>
			<tbody>
			
		<?php		
		
		$totalin_bs = 0;
		$totalin_sus = 0;
		
		$this->detalle($cajeros,1,$totalin_bs,$totalin_sus);
	
		?>
		
		
		</tbody>
		        </table>
        <br>
         <h1>EGRESOS</h1>
			<table   width="90%"  class="tablaReporte" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th>
					<b>Fecha</b>
				</th>
				<th>
					<b>Usuario</b>
				</th>
				<th>
					<b>No. Nota</b>
				</th>					
				<th>
					<b>TC</b>
				</th>
				<th>
					<b>Detalle</b>
				</th>
				<th>
					<b>Bs.</b>
				</th>
				<th>
					<b>$us.</b>
				</th>
			</tr>
			</thead>
			<tbody>
			
		<?php		
		
		$totaleg_bs = 0;
		$totaleg_sus = 0;
		
		$this->detalle($cajeros,2,$totaleg_bs,$totaleg_sus);		
		?>
		
		
		</tbody>
		        </table>		
        <br>
		<table   width="90%"  class="tablaReporte" cellpadding="0" cellspacing="0">		
		<tr>
			<td colspan='4'><b><font size="4">TOTAL INGRESOS/EGRESOS :</font></b></td>
			<td><b><font size="4"><? echo round((abs($totalin_bs)-abs($totaleg_bs)),2);?></font></b></td>
			<td><b><font size="4"><? echo round((abs($totalin_sus)-abs($totaleg_sus)),2);?></font></b></td>
		<tr>	
		</table>
		</center>
		<br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
		</div><br>
		<?php
	}
	
	function nombre_persona($usuario)
	{
		$conec= new ADO();
		
		$sql="select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->int_nombre.' '.$objeto->int_apellido; 
			
	}
	
}
?>