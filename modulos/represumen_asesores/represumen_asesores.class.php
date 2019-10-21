<?php

class REPRESUMEN_ASESORES extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function REPRESUMEN_ASESORES()
	{
		//permisos
		$this->ele_id=187;
		
		$this->busqueda();
	
		//fin permisos
		
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='represumen_asesores';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('RESUMEN DE ASESORES');
		
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
			<script>
			function enviar_formulario()
			{
				
					document.frm_sentencia.submit();
				
			}
			function reset_fecha_inicio()
			{
				document.frm_sentencia.inicio.value='';
			}
			function reset_fecha_fin()
			{
				document.frm_sentencia.fin.value='';
			}
			</script>
			<!--MaskedInput-->
			<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
			<!--MaskedInput-->	
						<div id="Contenedor_NuevaSentencia">
						<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=represumen_asesores" method="POST" enctype="multipart/form-data">  
									<div id="FormSent">
										<div class="Subtitulo">Filtro del Reporte</div>
					                    <div id="ContenedorSeleccion">
										
                                        
										<!--Inicio
										<div id="ContenedorDiv">
											<div class="Etiqueta" ><span class="flechas1">* </span>Asesor</div>
											<div id="CajaInput">
												<select name="asesor" id="asesor" class="caja_texto">
													<option value="">Todos</option>
													<?php
													  // $fun = NEW FUNCIONES;
													  // $fun->combo("select usu_id as id, usu_id  as nombre  from ad_usuario order by usu_id asc ", $_POST['asesor']);
													?>
												</select>
											</div>
											
										</div>
										Fin-->
										
										<!--Inicio-->
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" >Fecha Inicio</div>
                                             <div id="CajaInput">
                                                <input class="caja_texto" name="inicio" id="inicio" size="12" value="<?php echo date('d-m-Y');?>" type="text">
                            					<a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_fecha_inicio();">
                                                	<img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                                </a>
                                                <span class="flechas1">(DD/MM/AAAA)</span>
                                            </div>		
                                        </div>
                                        <!--Fin-->
                                        <!--Inicio-->
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" >Fecha Fin</div>
                                             <div id="CajaInput">
                                                <input class="caja_texto" name="fin" id="fin" size="12" value="<?php echo date('d-m-Y'); ?>" type="text">
                            					<a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_fecha_fin();">
                                                	<img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                                </a>
                                                <span class="flechas1">(DD/MM/AAAA)</span>
                                            </div>		
                                        </div>
                                        <!--Fin-->
												
										</div>
										
										<div id="ContenedorDiv">
					                           <div id="CajaBotones">
													<center>
													<input type="hidden" class="boton" name="formu" value="ok">
													<input type="button" class="boton" name="" onclick="javascript:enviar_formulario();" value="Generar Reporte">
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
	
	function mostrar_reporte()
	{		
		$conversor = new convertir();
		////
		$pagina="'contenido_reporte'";
		
		$page="'about:blank'";
		
		$extpage="'reportes'";
		
		$features="'left=100,width=900,height=500,top=0,scrollbars=yes'";
		
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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=represumen_venta\';"></td></tr></table><br><br>
				';?>
						
				 

			<div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="30%" >
				    </br><strong><?php echo _nombre_empresa; ?></strong></br>
							
							<?php echo _datos_empresa; ?></br></br>
					</td>
				    <td  width="40%" >
                    	<p align="center" >
                        	<strong>
                            	<h3>
									<center>REPORTE SEGUIMIENTO</center>
                              	</h3>
                          	</strong>
                       	</p>
                    	<p align="center">
                        	<BR><?php if ($_POST['inicio']<>"") echo '<strong>Del:</strong> '.date('d/m/Y',strtotime($conversor->get_fecha_mysql($_POST['inicio'])))?><?php if ($_POST['fin']<>"") echo ' <strong>Al:</strong> '.date('d/m/Y',strtotime($conversor->get_fecha_mysql($_POST['fin'])))?>
                        </p>
						<p align="center">
							<strong>Asesor:</strong>
							<?php
							if($_POST['asesor']<>'')
							{
								echo $this->nombre_persona($this->obtener_id_interno_tbl_usuario($_POST['asesor']));
							}
							else
								echo 'Todos';
							?>
                        </p>
                        
                        
                        
                    </td>
				    <td  width="30%" ><div align="right"></br><img src="imagenes/micro.png" /></div><br/><br/></td>
				  </tr>
			</table>
			<table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="30%" >
					<?php 
					if($_POST['cliente']<>'')
					{
						echo $this->armar_datos_cliente($_POST['cliente']);
					}
					?>
					</td>
				    <td  width="40%" >
                    	
                    </td>
				    <td  width="30%" >
					
					</td>
				  </tr>
			</table>
			<br>
			<?php
				$conec= new ADO();
				
				$fecha_interno='';
				$fecha_seguimiento='';
				$fecha_seguimiento_accion='';
				$fecha_proforma='';
				$fecha_reserva='';
				$fecha_venta='';
				
				if($_POST['inicio']<>"")
				{
					$fecha_interno.=" and int_fecha_ingreso >= '".$conversor->get_fecha_mysql($_POST['inicio'])."' ";
					$fecha_seguimiento.=" and seg_fecha >= '".$conversor->get_fecha_mysql($_POST['inicio'])."' ";
					$fecha_seguimiento_accion.=" and sac_fecha >= '".$conversor->get_fecha_mysql($_POST['inicio'])."' ";
					$fecha_proforma.=" and pro_fecha >= '".$conversor->get_fecha_mysql($_POST['inicio'])."' ";
					$fecha_reserva.=" and res_fecha >= '".$conversor->get_fecha_mysql($_POST['inicio'])."' ";
					$fecha_venta.=" and ven_fecha >= '".$conversor->get_fecha_mysql($_POST['inicio'])."' ";
					
					if($_POST['fin']<>"")
					{
						$fecha_interno.=" and int_fecha_ingreso <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
						$fecha_seguimiento.=" and seg_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
						$fecha_seguimiento_accion.=" and sac_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
						$fecha_proforma.=" and pro_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
						$fecha_reserva.=" and res_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
						$fecha_venta.=" and ven_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
					}
				}
				else
				{
					if($_POST['fin']<>"")
					{
						$fecha_interno.=" and int_fecha_ingreso <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
						$fecha_seguimiento.=" and seg_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
						$fecha_seguimiento_accion.=" and sac_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
						$fecha_proforma.=" and pro_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
						$fecha_reserva.=" and res_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
						$fecha_venta.=" and ven_fecha <='".$conversor->get_fecha_mysql($_POST['fin'])."' ";
					}
				}
				
				$usuario='';
				if($_POST['asesor']<>"")
				{
					$usuario=" and usu_id='".$_POST['asesor']."'";
				}
				
					
				
				$sql="SELECT *
				FROM 
				ad_usuario
				inner join interno on (usu_per_id=int_id)
				inner join vendedor on (vdo_int_id=int_id)
				where 1=1 $usuario
				order by int_nombre,int_apellido asc";

				$conec->ejecutar($sql);
		
				$num=$conec->get_num_registros();
			?>
			<table class="tablaLista" style="100%" cellpadding="0" cellspacing="0" >
			<thead>
			<tr>
				<th>Asesor</th>
				<th>Registro Prospecto</th>
				<th>Seguimiento</th>
                <th>Acciones</th>
                <th>Proformas</th>
				<th>Reservas</th>
				<th>Ventas</th>
			</tr>
			</thead>
			<tbody>
				<?php
				
				for($i=0;$i<$num;$i++)
				{
					$objeto=$conec->get_objeto();
					
					$id_interno 	= $this->obtener_id_interno_tbl_usuario($objeto->usu_id);
					$id_vendedor 	= $this->obtener_id_vendedor($id_interno);
					
					$filtro_vendedor_reserva='';
					$filtro_vendedor_venta='';
					if($id_vendedor<>'')
					{
						$filtro_vendedor_reserva =" and res_vdo_id='$id_vendedor'";
						$filtro_vendedor_venta	 =" and ven_vdo_id='$id_vendedor'";
					}
					$fun = NEW FUNCIONES;
					
					
					?>
						<tr>
							<td><?php echo $objeto->int_nombre.' '.$objeto->int_apellido; ?></td>
							<td><?php echo $fun->numero_registros('interno',"1=1 $fecha_interno and int_usu_id='$objeto->usu_id'");?></td>
							<td><?php echo $fun->numero_registros('seguimiento',"1=1 $fecha_seguimiento and seg_usu_id='$objeto->usu_id'");?></td>
							<td><?php echo $fun->numero_registros('seguimiento_accion',"1=1 $fecha_seguimiento_accion and sac_usu_id='$objeto->usu_id'");?></td>
							<td><?php echo $fun->numero_registros('proforma',"1=1 $fecha_proforma and pro_usu_id='$objeto->usu_id'");?></td>
							<td><?php echo $fun->numero_registros('reserva_terreno',"1=1 $fecha_reserva $filtro_vendedor_reserva and res_estado in ('Pendiente','Habilitado','Concretado')");?></td>
							<td><?php echo $fun->numero_registros('venta',"1=1 $fecha_venta $filtro_vendedor_venta and ven_estado in ('Pendiente','Pagado')");?></td>
						</tr>
							
					<?php
					$conec->siguiente();
				}
				?>
				</tbody>
				
			</table>
            <?php
			if($num==0)
			{
				echo "<center>No se encontraron registros</center>";
			}
			?>
			</center>
			<br/><br><br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
			</div>
			<br>	
			<?php		
	}
	
	
	function armar_datos_cliente($int_id)
	{
		$conec = new ADO();

        $sql = "select int_nombre,int_apellido,int_telefono,int_celular,int_email from interno where int_id='$int_id'";

        $conec->ejecutar($sql);
		
		$objeto = $conec->get_objeto();
		
		$datos = '<b>Cliente:</b> '.$objeto->int_nombre.' '.$objeto->int_apellido.'<br><b>Telefono:</b> '.$objeto->int_telefono.'<br><b>Celular:</b> '.$objeto->int_celular.'<br><b>Email:</b> '.$objeto->int_email;
		
		return $datos;
	}
	
	function armar_detalle_seguimiento($id_seguimiento)
	{
		$conversor = new convertir();
		
		$conec = new ADO();

        $sql = "select * from seguimiento_accion where sac_seg_id='$id_seguimiento'";

        $conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		if($num>0)
		{
			?>
			<table class="tablaLista" style="100%" cellpadding="0" cellspacing="0" >
				<thead>
					<tr>
						<th>Nro</th>
						<th>Fecha</th>
						<th>Hora</th>
						<th>Accion</th>
					</tr>
				</thead>
				<tbody>
					<?php
					for($i=0;$i<$num;$i++)
					{
						$objeto = $conec->get_objeto();
						echo '<tr>';
							echo '<td>';
							echo $i+1;
							echo '</td>';
							
							echo '<td>';
							echo $conversor->get_fecha_latina($objeto->sac_fecha);
							echo '</td>';
							
							echo '<td>';
							echo $objeto->sac_hora;
							echo '</td>';
							
							echo '<td>';
							echo $objeto->sac_accion;
							echo '</td>';
							
						echo '</tr>';
						$conec->siguiente();
						
					}
					?>
				</tbody>
			</table>
			<?php
		}
		
		
		
		$datos = '<b>Cliente:</b> '.$objeto->int_nombre.' '.$objeto->int_apellido.'<br><b>Telefono:</b> '.$objeto->int_telefono.'<br><b>Celular:</b> '.$objeto->int_celular.'<br><b>Email:</b> '.$objeto->int_email;
		
		return $datos;
	}
	
	function nombre_vendedor($vdo_id) {
        $conec = new ADO();

        $sql = "select int_nombre,int_apellido from vendedor inner join interno on (vdo_int_id=int_id and vdo_id='$vdo_id')";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }
	
	function nombre_uv()
	{
		$conec= new ADO();
	
		$sql="select urb_nombre from urbanizacion where urb_id='".$_POST['ven_urb_id']."'";

		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->urb_nombre;
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
	
	function obtener_id_vendedor($id_interno)
	{
		$conec= new ADO();
		
		$sql="SELECT vdo_id,vdo_int_id FROM vendedor WHERE vdo_int_id=$id_interno";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->vdo_id;
	}
	
	function obtener_id_vendedor_tbl_comision($id_venta)
	{
		$conec= new ADO();
		
		$sql="SELECT * from comision
		WHERE com_ven_id=$id_venta";
		
		echo $sql;
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->vdo_id;
	}
	
	function nombre_persona($id_interno)
	{
		$conec= new ADO();
		
		$sql="select int_nombre,int_apellido from interno where int_id=$id_interno";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->int_nombre.' '.$objeto->int_apellido; 
			
	}
	
	function montopagado($id)
	{
		$conec=new ADO();
		$sql="select sum(ind_monto) as monto from interno_deuda where ind_tabla='venta' and ind_estado='Pagado' and ind_tabla_id='$id'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->monto;
	}
}
?>