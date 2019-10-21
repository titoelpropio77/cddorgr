<?php

class REPVENDIDOS extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function REPVENDIDOS()
	{
		//permisos
		$this->ele_id=137;
		
		$this->busqueda();
	
		//fin permisos
		
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='repvendidos';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('LOTES VENDIDOS');
		
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
						<script>
						function cargar_manzano(id)
						{
							var	valores="tarea=manzanos_reporte&urb="+id;			

							ejecutar_ajax('ajax.php','manzano',valores,'POST');									
						}
						
						function enviar_formulario()
						{
							var urbanizacion=document.frm_sentencia.ven_urb_id.options[document.frm_sentencia.ven_urb_id.selectedIndex].value;
							
							if(urbanizacion!='')
							{
								document.frm_sentencia.submit();
							}
							else
							{
								$.prompt('Para generar el reporte debe seleccionar la Urbanización',{ opacity: 0.8 });			   
							}
						}
						</script>	
						<div id="Contenedor_NuevaSentencia">
						<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=repvendidos" method="POST" enctype="multipart/form-data">  
									<div id="FormSent">
										<div class="Subtitulo">Filtro del Reporte</div>
					                    <div id="ContenedorSeleccion">
										<!--Inicio-->
										<div id="ContenedorDiv">
										   <div class="Etiqueta" ><span class="flechas1">*</span>Urbanización</div>
										   <div id="CajaInput">
												<select style="width:200px;" name="ven_urb_id" class="caja_texto" onchange="cargar_manzano(this.value);">
													   <option value="">Seleccione</option>
													   <?php 		
														$fun=NEW FUNCIONES;		
														$fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion",$_POST['ven_urb_id']);				
														?>
											   </select>
										   </div>
										</div>
										<!--Fin-->
										<!--Inicio-->
										<div id="ContenedorDiv">
										   <div class="Etiqueta" >Manzano</div>
										   <div id="CajaInput">
										   <div id="manzano">
										   <select style="width:200px;" name="ven_man_id" class="caja_texto">
										   <option value="">Seleccione</option>
										   <?php 		
											if($_POST['ven_urb_id']<>"")
											{
												$fun=NEW FUNCIONES;		
												$fun->combo("select man_id as id,man_nro as nombre from manzano where man_urb_id='".$_POST['ven_urb_id']."' ",$_POST['ven_man_id']);				
											}
											?>
										   </select>
										   </div>
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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=repvendidos\';"></td></tr></table><br><br>
				';?>
						
				 

			<div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="30%" >
				    </br><strong><?php echo _nombre_empresa; ?></strong></br>
                    <?php echo _datos_empresa; ?></br></br>
					</td>
				    <td  width="40%" ><p align="center" ><strong><h3><center>REPORTE DE LOTES VENDIDOS</br><?php echo strtoupper($this->nombre_uv()); ?></center></h3></strong></p></td>
				    <td  width="30%" ><div align="right"><br/><img src="imagenes/micro.png" /><br/></div><br/><br/></td>
				  </tr>
			</table>
			<?php
				$conec= new ADO();
				
				$cad="";
				
				if($_POST['ven_urb_id']<>"")
					$cad=" and urb_id='".$_POST['ven_urb_id']."' ";
				
				if($_POST['ven_man_id']<>"")
					$cad=" and man_id='".$_POST['ven_man_id']."' ";
				
				
					$sql="SELECT 
					ven_id,ven_fecha,ven_superficie,ven_promotor,ven_monto,ven_cuota_inicial,ven_plazo,ven_cuota,ven_moneda,ven_tipo,ven_co_propietario,int_nombre,int_apellido,man_nro,lot_nro,cast(man_nro as SIGNED) as manzano ,cast(lot_nro as SIGNED) as lote,lot_tipo,lot_sup_vivienda 
					FROM 
					venta
					inner join interno on (ven_int_id=int_id)
					inner join lote on (ven_lot_id=lot_id)
					inner join manzano on (lot_man_id=man_id)
					inner join urbanizacion on (man_urb_id=urb_id)
					where
					ven_estado in ('Pendiente','Pagado')
					$cad
					order by manzano,lote asc";
				
				
				$conec->ejecutar($sql);
		
				$num=$conec->get_num_registros();
			?>
			<table class="tablaLista" style="width:900px;" cellpadding="0" cellspacing="0" >
			<thead>
			<tr>
				<th>Nro</th>
                <th>Tipo</th>
				<th>Nro Manzano</th>
				<th>Nro Lote</th>
				<th>Superficie</th>
				<th>Cuota Mensual</th>
				<th>Nro de Cuotas</th>
                <th>Fecha de Venta</th>
				<th>Vendedor</th>
				<th>Promotor</th>
                <th>Comprador</th>
			</tr>
			</thead>
			<tbody>
				<?php
				$suma=0;
				$cuotat=0;
				$spla=0;
				for($i=0;$i<$num;$i++)
				{
					$objeto=$conec->get_objeto();
					
					if($objeto->ven_plazo<>0)
						//$cuota=round(($objeto->ven_monto - $objeto->ven_cuota_inicial)/$objeto->ven_meses_plazo,2);
						$cuota=$objeto->ven_cuota;
					else
						$cuota=0;
					?>
						<tr>
							<td><?php echo $objeto->ven_id; ?></td>
                            <td><?php echo $objeto->lot_tipo.' - '.$objeto->ven_tipo; ?></td>
							<td><?php echo $objeto->man_nro; ?></td>
							<td><?php echo $objeto->lot_nro; ?></td>
							<td>
							<?php 
							if($objeto->lot_tipo=='Lote')
							{
								echo $objeto->ven_superficie; $suma+=$objeto->ven_superficie;
							}
							else
							{
								echo $objeto->lot_sup_vivienda; $suma+=$objeto->lot_sup_vivienda;
							}
							?>
                            </td>
                            
							<td><?php echo $cuota; $cuotat+=$cuota; ?></td>
							<td><?php echo $objeto->ven_plazo; $spla+=$objeto->ven_plazo; ?></td>
                            <td><?php echo $conversor->get_fecha_latina($objeto->ven_fecha);?></td>
                            <td><?php echo $this->nombre_persona($this->obtener_id_interno_tbl_vendedor($this->obtener_id_vendedor_tbl_comision($objeto->ven_id)));?>&nbsp;</td>
							<td><?php echo $objeto->ven_promotor;?>&nbsp;</td>
                            <td><?php echo $objeto->int_nombre.' '.$objeto->int_apellido; if($objeto->ven_co_propietario<>0) echo ' - '.$this->nombre_persona($objeto->ven_co_propietario);?></td>
						</tr>
							
					<?php
					$conec->siguiente();
				}
				?>
				</tbody>
			<tfoot>
			<?php
			if($num>0)
			{
			?>
            <tr>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
                <th>&nbsp;</th>
                <th><?php echo $suma; ?> m2</th>
				<th><?php echo $cuotat; ?> $us</th>
				<th><?php echo round($spla/$num,0); ?> Prom. Cuotas</th>
				<th>&nbsp;</th>
                <th>&nbsp;</th>
				<th>&nbsp;</th>
                <th>&nbsp;</th>
			</tr>
            <?php
			}
			?>
			</tfoot>	
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
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		if($objeto->com_vdo_id=='')
			return 0;
		else
			return $objeto->com_vdo_id;
	}
	
	function obtener_id_interno_tbl_vendedor($id)
	{
		$conec= new ADO();
		
		$sql="SELECT vdo_int_id from vendedor
		WHERE vdo_id=$id";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		if($objeto->vdo_int_id=='')
			return 0;
		else
			return $objeto->vdo_int_id;
	}
	
	function nombre_persona($id_interno)
	{
		$conec= new ADO();
		
		$sql="select int_nombre,int_apellido from interno where int_id=$id_interno";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		if($objeto->int_nombre =='' && $objeto->int_apellido =='')
			return 'Sin Vendedor';
		else
			return $objeto->int_nombre.' '.$objeto->int_apellido; 
			
	}
}
?>