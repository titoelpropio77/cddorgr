<?php

class FACCONFIGURACION
{
	var $mensaje;
	
	function FACCONFIGURACION()
	{
		
		$this->link='gestor.php';
		
		$this->modulo='facconfiguracion';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('CONFIGURACI&Oacute;N FACTURA');
		
	}

	function datos()
	{
		if($_POST)
			return true;
		else
			return false;
	}
	
	function actualizar()
	{
	
		$this->mensaje="La configuracion fue actualizada correctamente";
		
		$conec= new ADO();

		$sql="update fac_factura_configuracion set 
		fcon_nit='".$_POST['fcon_nit']."',
		fcon_razon_social='".$_POST['fcon_razon_social']."',
		fcon_actividad_economica='".$_POST['fcon_actividad_economica']."'";

		$conec->ejecutar($sql);

	}

	function formulario_tcp()
	{
		if($this->datos())
		{	
			$this->actualizar();
		}
		
		$conec= new ADO();
		
		$sql="select * from fac_factura_configuracion";
		 
		$conec->ejecutar($sql);
		
		$objeto = $conec->get_objeto();
		
		// Cargado de datos para el formulario
		$nit = $objeto->fcon_nit;

		$razon_social = $objeto->fcon_razon_social;

		$actividad_economica = $objeto->fcon_actividad_economica;

		
		$url=$this->link.'?mod='.$this->modulo;
		
		$this->formulario->dibujar_tarea();
		
		if($this->mensaje != "")
		{
			
			$this->formulario->ventana_volver($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tare=ACCEDER');
			exit;
		}
		
		?>
			<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
<!-- ==============================================================================================-->
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
							
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>NIT</div>
							   <div id="CajaInput">
								<input type="text" class="caja_texto" name="fcon_nit" id="fcon_nit" size="35" value="<?php echo $nit;?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Raz&oacute;n Social</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="fcon_razon_social" id="fcon_razon_social" size="35" value="<?php echo $razon_social;?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Actividad Econ&oacute;mica</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="fcon_actividad_economica" id="fcon_actividad_economica" size="35" value="<?php echo $actividad_economica;?>">
							   </div>
							</div>
						</div>
<!-- ==============================================================================================-->
				</div>
				<div id="ContenedorDiv">
					<div id="CajaBotones" align="center">
							<input type="submit" class="boton" name="" value="Guardar">
							<input type="reset" class="boton" name="" value="Cancelar">
							<input type="button" class="boton" name="" value="Volver" onclick="javascript:history.back();">
					</div>
				</div>
			</form>
		</div>

		<?php
	}
}
?>