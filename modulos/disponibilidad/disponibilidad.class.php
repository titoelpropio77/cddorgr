<?php

class DISPONIBILIDAD extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function DISPONIBILIDAD()
	{
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='disponibilidad';
		
		$this->formulario = new FORMULARIO();
		
                $this->usu = new USUARIO();
                
		$this->formulario->set_titulo('PLANOS Y DISPONIBILIDAD');
	}
	
	function dibujar_busqueda()
	{
			$this->formulario();
	}
	
	function formulario()
	{
		if($_POST['oculto']=='ok')
		{
			$this->mostrar_reporte();
		}
		else
		{
		//$this->formulario->dibujar_cabecera();
		$this->formulario->dibujar_cabecera();
		?>
        <script>
        function enviar_formulario()
		{
			var urbanizacion=document.frm_sentencia.urb_id.options[document.frm_sentencia.urb_id.selectedIndex].value;
			
			if(urbanizacion!='')
			{
				document.frm_sentencia.submit();
			}
			else
			{
				$.prompt('Para Mostar el Mapa debe seleccionar la Urbanización',{ opacity: 0.8 });			   
			}
		} 
		</script>
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=disponibilidad" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
					<div class="Subtitulo">Urbanizaci&oacute;nes </div>
					<div id="ContenedorSeleccion">
						<div id="ContenedorDiv">
							<table class="tablaReporte" cellpadding="0" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>Urbanizaci&oacute;n</th>
										<th>Acceso p&uacute;blico</th>
										<th>Acceso privado</th>
										<th>Descargar Plano</th>
									</tr>
								</thead>
								<tbody>
									<?php
									//urbanizacion_plano_config
									// Generar url actual 
									$encode = base64_encode($_SESSION['id']);
									$encodeLimpio = str_replace(array('+','/','='),array('-','_',''),$encode);
									
									
									$conec = new ADO();
									$sql="select urb_id, urb_nombre from urbanizacion where 1 and urb_eliminado='No'";
									$conec->ejecutar($sql);
									$num = $conec->get_num_registros();
									for( $i=0; $i<$num;  $i++)
									{
									
										$objeto = $conec->get_objeto();
										$url_publico = "http://" . $_SERVER["SERVER_NAME"]."/modulos/uv/?mapa=&u=".$objeto->urb_id;
										$url_privado = "http://" . $_SERVER["SERVER_NAME"]."/modulos/uv/?mapa=".$encodeLimpio."&u=".$objeto->urb_id;
										
										$result = $this->urbanizacion_fases($objeto->urb_id);
										if($result->estado){
											for ($f = 0; $f < $result->num; $f++) {
											if($f == 0){
												?> 
												<tr>
													<td rowspan="<?php echo $result->num; ?>"><?php echo $objeto->urb_nombre; ?></td>
													<td><b>Fase: <?php echo $result->datos[$f]->uv_nombre; ?></b> <a href="<?php echo $url_publico; ?>&uv=<?php echo $result->datos[$f]->uv_id; ?>" target="_blank">Plano p&uacute;blico </a></td>
													<td><a href="<?php echo $url_privado; ?>&uv=<?php echo $result->datos[$f]->uv_id; ?>" target="_blank">Plano privado </a></td>
													<td><a href="<?php echo "gen_mapa.php?urb_id={$objeto->urb_id}&uv={$result->datos[$f]->uv_id}"; ?>" target="_blank"><img src="images/vermapa.png"></a></td>
												</tr>
												<?php
											} else {
												?>
												<tr>
													<td><b>Fase: <?php echo $result->datos[$f]->uv_nombre; ?></b> <a href="<?php echo $url_publico; ?>&uv=<?php echo $result->datos[$f]->uv_id; ?>" target="_blank"> Plano p&uacute;blico </a></td>
													<td><a href="<?php echo $url_privado; ?>&uv=<?php echo $result->datos[$f]->uv_id; ?>" target="_blank">Plano privado </a></td>
													<td><a href="<?php echo "gen_mapa.php?urb_id=$objeto->urb_id&uv={$result->datos[$f]->uv_id}"; ?>" target="_blank"><img src="images/vermapa.png"></a> </td>
												</tr>
												<?php 
												}
											}
											
										} else {
											?>
											<tr>
												<td><?php echo $objeto->urb_nombre; ?></td>
												<td><a href="<?php echo $url_publico; ?>" target="_blank">Plano p&uacute;blico </a></td>
												<td><a href="<?php echo $url_privado; ?>" target="_blank">Plano privado </a></td>
												<td><a href="<?php echo "gen_mapa.php?urb_id=$objeto->urb_id"; ?>" target="_blank"><img src="images/vermapa.png"></a></td>
											</tr>
											<?php
										}
										$conec->siguiente();
									}
									//rowspan="4"
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</form>	
		</div>
         <?php
		 }
	}
	
	function urbanizacion_fases($urb_id){
		$respuesta = new stdClass();
		$arr = array();
		$sql="SELECT uv_id,uv_nombre,pla_uv_id FROM urbanizacion_plano_config LEFT JOIN uv ON (pla_uv_id = uv_id) WHERE pla_fases='".$urb_id."'";  
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		if($num > 0) {
		
			for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass; 
                $o->uv_id = $objeto->pla_uv_id;
				$o->uv_nombre = $objeto->uv_nombre;
                array_push($arr, $o);
            }
			$respuesta->num = $num;
			$respuesta->datos = $arr;
			$respuesta->estado = true; 
		} else {
			$respuesta->num = 0;
			$respuesta->estado = false;
		}
		return $respuesta;
	}
	
}
?>