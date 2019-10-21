<?php

class REPDEUDASINTERNOS extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function REPDEUDASINTERNOS()
	{
		//permisos
		$this->ele_id=150;
		
		$this->busqueda();
	
		//fin permisos
		
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='repcuentasx';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('CUENTAS X PAGAR / X COBRAR');
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
        <script>
			function enviar_formulario()
			{
					
					var tipo=document.frm_sentencia.tipo.value;
					var estado=document.frm_sentencia.estado.value;
					
					if(tipo!="" && estado!="")
					{
						document.frm_sentencia.submit();
					}
					else
					{
						$.prompt('Seleccione Tipo y Estado',{ opacity: 0.8 });
					}
			}
			</script>
		
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
                        <script>
                            jQuery(function($){
                               $("#fecha").mask("99/99/9999");  
                            });
                        </script>						
						<div id="Contenedor_NuevaSentencia">
						<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=repcuentasx" method="POST" enctype="multipart/form-data">  
									<div id="FormSent">
										<div class="Subtitulo">Filtro del Reporte</div>
					                    <div id="ContenedorSeleccion">
										
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" ><span class="flechas1">*</span>Tipo</div>
                                           <div id="CajaInput">
                                           <select name="tipo" id="tipo" class="caja_texto">
                                               <option value="">Seleccione</option>
                                               <option value="1">X Cobrar</option>
                                               <option value="2">X Pagar</option>
                                           </select>
                                           </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" ><span class="flechas1">*</span>Estado</div>
                                           <div id="CajaInput">
                                           <select name="estado" id="estado" class="caja_texto">
                                               <option value="">Seleccione</option>
                                               <option value="Todos">Todos</option>
                                               <option value="Pendiente">Pendiente</option>
                                               <option value="Pagado">Pagado</option>
                                           </select>
                                           </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" ><span class="flechas1">* </span>Pagos/Cobros hasta</div>
                                             <div id="CajaInput">
                                                <input class="caja_texto" name="fecha" id="fecha" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                                            </div>		
                                        </div>
												
										</div>
										
										<div id="ContenedorDiv">
					                           <div id="CajaBotones">
													<center>
													<input type="hidden" class="boton" name="formu" value="ok">
													<input type="button" class="boton" name="" value="Generar Reporte" onclick="enviar_formulario();">
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
	
	function mostrar_reporte()
	{		
		$conversor = new convertir();
		
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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=repcuentax\';"></td></tr></table><br><br>
				';?>
						
				 

			<div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="40%" >
				    <strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong></td>
				    <td  width="20%" ><p align="center" >
                    	<strong>
                    		<h3>
                            	<center>
                                <?php 
								if($_POST['tipo']==1)
								{
									$titulo="CUENTAS x COBRAR";
								}
								else
								{
									$titulo="CUENTAS x PAGAR";
								}
								if($_POST['estado']=="Todos")
								{
									$estado="Estado: Todos";
								}
								else
								{
									if($_POST['estado']=="Pendiente")
									{
										$estado="Estado: Pendientes";
									}
									else
									{
										if($_POST['estado']=="Pagado")
										{
											$estado="Estado: Pagados";
										}
									}
								}
								echo $titulo.'<br>';
								echo $estado;
								?>
 								                   
                    </center></h3></strong></p></td>
				    <td  width="40%" ><div align="right"><img src="imagenes/micro.png" /></div></td>
				  </tr>
			</table>
			<table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
		        	<th>Persona</th>
                    <th>Tipo</th>
					<th>Centro de Costo</th>
                    <th>Concepto</th>
					<th>Fecha</th>
					<th>Estado</th>
                    <th class="tOpciones">Monto</th>
                    <th class="tOpciones">Pagado</th>
                    <th class="tOpciones">Interes</th>
                    <th class="tOpciones">Saldo</th>
					<!--<th class="tOpciones">Bs</th>
					<th class="tOpciones">$us</th>-->
				</tr>	
				</thead>
				<tbody>
		<?php
		$conec= new ADO();
		
		if($_POST['tipo']<>"")
		{
			$cad=" WHERE cux_tipo='".$_POST['tipo']."'";	
		}
		
		if($_POST['estado']<>"")
		{
			if($_POST['estado']!="Todos")
			{
				$cad = $cad." AND cux_estado='".$_POST['estado']."'";	
			}
			
		}
		
		//$fecha=date('Y-m-d');
		
		/*if($_POST['fecha']!="")
		{
			if(($_POST['tipo']=="") && ($_POST['estado']==""))
			{
				$cad = $cad." WHERE cux_fecha <= '".$_POST['fecha']."'";
			}
			else
			{
				$cad = $cad." AND cux_fecha <= '".$_POST['fecha']."'";	
			}
		}
		else
		{
			
		}*/
		
		$fecha=$conversor->get_fecha_mysql($_POST['fecha']);
		
		/*$sql="SELECT ind_monto,ind_moneda,ind_concepto,ind_fecha_programada,int_id,int_nombre,int_apellido,int_email,int_telefono,int_celular
		FROM 
		interno_deuda inner join interno on ($cad ind_int_id=int_id)
		where ind_estado='Pendiente' $cad1
		order by int_apellido,int_nombre asc
			 ";*/
	
		$sql="SELECT * FROM 
		cuentasx".$cad;

		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$conversor = new convertir();
		
		$tipocambio=$this->tc;
		
		$tbol_monto=0;
		
		$tsus_monto=0;
		
		$tbol_monto_pagado=0;
		
		$tsus_monto_pagado=0;
		
		$tbol_monto_saldo=0;
		
		$tsus_monto_saldo=0;
		
		
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();

			echo '<tr>';
									
					echo "<td>";
						echo $this->nombre_interno($objeto->cux_int_id);
					echo "&nbsp;</td>";
					
					echo "<td>";
						if($objeto->cux_tipo==1)
							echo "X Cobrar";
						else
							echo "X Pagar";
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $this->obtener_centro_costo($objeto->cux_cco_id );
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->cux_concepto;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->cux_fecha);
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->cux_estado;
					echo "&nbsp;</td>";
					
					echo "<td>";
						if($objeto->cux_moneda=='1')
						{
							$tbol_monto+=$objeto->cux_monto;
						}
						else
						{
							$tsus_monto+=$objeto->cux_monto;
						}
						echo $objeto->cux_monto;
					echo "&nbsp;</td>";
					
					if($fecha!="")
					{
						$this->datos_cuenta($objeto->cux_id,$monto,$pagado,$moneda);
						
						$sql_monto_fecha="select sum(cup_monto) as pagado from cuentax_pago where cup_cux_id='".$objeto->cux_id."' AND cup_fecha <= '$fecha' AND cup_estado='Pagado'";
				
						$conec2= new ADO();
				
						$conec2->ejecutar($sql_monto_fecha);
				
						$objeto2=$conec2->get_objeto();
				
						$pagado=$objeto2->pagado;
					}
					else
					{
					
						$this->datos_cuenta($objeto->cux_id,$monto,$pagado,$moneda);
					}
					
					
					echo "<td>";
						if($objeto->cux_moneda=='1')
						{
							$tbol_monto_pagado+=$pagado;
						}
						else
						{
							$tsus_monto_pagado+=$pagado;
						}
						echo $pagado; if($objeto->cux_moneda=='1') echo ' Bs'; else echo ' $us';
					echo "&nbsp;</td>";
					
					
					if($fecha!="")
					{
						$sql_interes_fecha="select sum(cupi_monto_interes) as total_interes from cuentax_pago_interes where cupi_cux_id='".$objeto->cux_id."' AND cupi_fecha <= '$fecha' AND cupi_estado='Pagado'";
				
						$conec2= new ADO();
				
						$conec2->ejecutar($sql_interes_fecha);
				
						$objeto2=$conec2->get_objeto();
				
						$total_interes=$objeto2->total_interes;
					}
					else
					{
					
						$sql_interes="SELECT sum(cupi_monto_interes) as total_interes FROM cuentax_pago_interes WHERE cupi_cux_id='".$objeto->cux_id."' AND cupi_estado='Pagado' ORDER BY cupi_id ASC";
				
						$conec2= new ADO();
				
						$conec2->ejecutar($sql_interes);
				
						$objeto2=$conec2->get_objeto();
				
						$total_interes=$objeto2->total_interes;
					}
					
					
					
					echo "<td>";
						echo $total_interes; if($objeto->cux_moneda=='1') echo ' Bs'; else echo ' $us';
					echo "&nbsp;</td>";
					
					echo "<td>";
						if($objeto->cux_moneda=='1')
						{
							$tbol_monto_saldo+=($monto-$pagado);
						}
						else
						{
							$tsus_monto_saldo+=($monto-$pagado);
						}
						echo $monto-$pagado; if($objeto->cux_moneda=='1') echo ' Bs'; else echo ' $us';
					echo "&nbsp;</td>";
					
			$conec->siguiente();
		}
		?>
		</tbody>
		</table>
        <br /><br /><br />
		
       	<table   width="50%"  class="tablaReporte" cellpadding="0" cellspacing="0">
        	<thead>
            	
            	<tr>
                	<th class="tOpciones">Moneda</th>
                    <th class="tOpciones">Monto</th>
                    <th class="tOpciones">Pagado</th>
                    <th class="tOpciones">Saldo</th>
                    <!--<th class="tOpciones">Bs</th>
                    <th class="tOpciones">$us</th>-->
             	</tr>	
           	</thead>
            <tbody>
            	<tr>
                	<td>Totales Bolivianos</td>
                    <td><?php echo $tbol_monto.' Bs'; ?></td>
                    <td><?php echo $tbol_monto_pagado.' Bs'; ?></td>
                    <td><?php echo $tbol_monto_saldo.' Bs'; ?></td>
                </tr>
                <tr>
                	<td>Totales Dolares</td>
                    <td><?php echo $tsus_monto.' $us'; ?></td>
                    <td><?php echo $tsus_monto_pagado.' $us'; ?></td>
                    <td><?php echo $tsus_monto_saldo.' $us'; ?></td>
                </tr>
            </tbody>
    	</table>
		</center>
		<br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
		</div>
		<br>	
		<?php
		echo "";
		
	}
	
	function nombre_interno($id)
	{
		$conec= new ADO();
		
		$sql="select int_id,int_nombre,int_apellido from interno where int_id='".$id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->int_nombre.' '.$objeto->int_apellido;
	}
	
	function obtener_centro_costo($id)
	{
		$conec= new ADO();
		
		$sql="select cco_id,cco_descripcion from centrocosto where cco_id='".$id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->cco_descripcion;
	}
	
	function datos_cuenta($cuenta,&$monto,&$pagado,&$moneda,&$des="")
	{
		$conec= new ADO();
		
		$sql = "select cux_monto,cux_moneda,cux_concepto from cuentasx where cux_id='$cuenta'"; 
		
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();

		$moneda=$objeto->cux_moneda;
		
		$monto=$objeto->cux_monto;
		
		$des=$objeto->cux_concepto;
		
		$sql = "select sum(cup_monto) as pagado from cuentax_pago where cup_cux_id='$cuenta'"; 
		
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();
			
		$pagado=$objeto->pagado;
	}
	
	
}
?>