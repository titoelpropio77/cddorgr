<?php

class FACSUCURSAL extends BUSQUEDA {
	var $mensaje;
	var $formulario;
	
	function FACSUCURSAL()
	{
		
		//permisos
		$this->ele_id = 177;
		
		$this->busqueda();
		
		$this->link='gestor.php';
		
		$this->modulo='facsucursal';
		
		$this->formulario = new FORMULARIO();
		
		$this->coneccion = new ADO();
		
		$this->formulario->set_titulo('INFORMACI&Oacute;N DE LA EMPRESA');
		
	}

	function datos()
	{
		if($_POST)
			return true;
		else
			return false;
	}
	
	function datos_sucursal()
	{
		if($_POST) {

			if ($_POST['identificador'] == '' || $_POST['direccion'] == '') {

				$mensaje = 'Debe llenar todo los campos marcados con (*).';
				$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'error');
				exit;			
			}
			
			return true;
		} else {
			
			return false;
		}
	}
	
	function actualizar()
	{
	
		$this->mensaje="La informaci&oacute;n fue actualizada correctamente";
		
		$conec= new ADO();

		$sql="update fac_sucursal set 
		suc_direccion='".$_POST['direccion']."',
		suc_telefono='".$_POST['telefono']."',
		suc_celular='".$_POST['celular']."',
		suc_alcaldia='".$_POST['alcaldia']."'";

		$conec->ejecutar($sql);

	}
	
	function actualizar_sucursal()
	{
	
		$this->mensaje="La informaci&oacute;n fue actualizada correctamente";
		$sucursal_id = $_GET['id'];
		$conec= new ADO();

		$sql="update fac_sucursal set 
		suc_identificador='".$_POST['identificador']."',
		suc_direccion='".$_POST['direccion']."',
		suc_telefono='".$_POST['telefono']."',
		suc_celular='".$_POST['celular']."',
		suc_alcaldia='".$_POST['alcaldia']."'
		 where suc_id=$sucursal_id";

	}
	
	function guardar_sucursal()
	{
	
		$this->mensaje = "La sucursal fue agregada correctamente";
		
		$conec= new ADO();

		$sql="INSERT INTO 
			fac_sucursal 
			(`suc_identificador`, 
			`suc_direccion`, 
			`suc_telefono`, 
			`suc_celular`, 
			`suc_alcaldia`) 
			VALUES 
			('" . $_POST['identificador'] . "', 
			'" . $_POST['direccion'] . "', 
			'" . $_POST['telefono'] . "', 
			'" . $_POST['celular'] . "', 
			'" . $_POST['alcaldia'] . "')";

		$conec->ejecutar($sql);
		$this->formulario->ventana_volver($this->mensaje, $this->link . '?mod=' . $this->modulo);
		exit;

	}

	function formulario_tcp()
	{
		if($this->datos())
		{	
			$this->actualizar();
		}
		
		$conec= new ADO();
		
		$sql="select * from fac_sucursal where suc_identificador=0 and suc_id=1";
		 
		$conec->ejecutar($sql);
		
		$objeto = $conec->get_objeto();
		
		// Cargado de datos para el formulario
		$direccion = $objeto->suc_direccion;

		$telefonos = $objeto->suc_telefono;

		$celulares = $objeto->suc_celular;
		
		$alcaldia = $objeto->suc_alcaldia;

		
		$url=$this->link.'?mod='.$this->modulo;
		
		$this->formulario->dibujar_tarea();
		
		if($this->mensaje != "")
		{
			
			$this->formulario->ventana_volver($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tare=ACCEDER');
			exit;
		}
		
		?>
<div id="Contenedor_NuevaSentencia">
	<form id="frm_sentencia" name="frm_sentencia"
		action="<?php echo $url;?>" method="POST"
		enctype="multipart/form-data">
		<div id="FormSent">
			<!-- ==============================================================================================-->
			<div class="Subtitulo">Datos de la Central</div>
			<div id="ContenedorSeleccion">

				<div id="ContenedorDiv">
					<div class="Etiqueta">
						<span class="flechas1">* </span>Direccion
					</div>
					<div id="CajaInput">
						<input type="text" class="caja_texto" name="direccion" id="direccion" size="45" value="<?php echo $direccion;?>">
					</div>
				</div>
				<!--Fin-->
				<!--Inicio-->
				<div id="ContenedorDiv">
					<div class="Etiqueta">Telefono(s)</div>
					<div id="CajaInput">
						<input type="text" class="caja_texto" name="telefono" id="telefono" size="35" value="<?php echo $telefonos;?>">
					</div>
				</div>
				<!--Fin-->
				<!--Inicio-->
				<div id="ContenedorDiv">
					<div class="Etiqueta">Celular(es)</div>
					<div id="CajaInput">
						<input type="text" class="caja_texto" name="celular" id="celular" size="35" value="<?php echo $celulares;?>">
					</div>
				</div>
				<!--Inicio-->
				<div id="ContenedorDiv">
					<div class="Etiqueta">Alcaldia</div>
					<div id="CajaInput">
						<input type="text" class="caja_texto" name="alcaldia" id="alcaldia" size="35" value="<?php echo $alcaldia;?>">
					</div>
				</div>
				<!--Inicio-->
				<div id="ContenedorDiv">
					<label style="color: red;">NOTA:</label><br> 
					<label style="font-size: 9px;">
					La informaci&oacute;n
					que ingrese en el formulario tiene que ser llenado correctamente
					sin errores ortogr&aacute;ficos, ya que todas las facturas
					ir&aacute;n con esa informaci&oacute;n agregada que el cliente
					presentar&aacute; al SIN (Servicio de Impuestos Nacionales).<br>
					Si tiene m&aacute;s de un n&uacute;mero de telefono o celular separelo con "/".
					</label>
				</div>
			</div>
			<!-- ==============================================================================================-->
		</div>
		<div id="ContenedorDiv">
			<div id="CajaBotones" align="center">
				<input type="submit" class="boton" name="" value="Guardar"> <input
					type="reset" class="boton" name="" value="Cancelar"> <input
					type="button" class="boton" name="" value="Volver"
					onclick="javascript:history.back();">
			</div>
		</div>
			<div class="Subtitulo">Sucursales</div>
			<div id="ContenedorSeleccion">
				<!--Inicio-->
				<div id="ContenedorDiv">
					<input type="button" class="boton" name="" value="Agregar" onclick="javascript:location.href='<?php echo $url . '&tarea=SUCURSAL&accion=nuevo';?>';">
				</div>
				
				<?php
					$sql_sucursales = 'select * from fac_sucursal where suc_identificador != "0"';
					$this->coneccion->ejecutar($sql_sucursales);
					$nroRegistros = $this->coneccion->get_num_registros();

				?>
				<table class="tablaReporte">
					<thead>
						<tr>
							<th>Identificador</th>
							<th>Direcci&oacute;n</th>
							<th>Tel&eacute;fono(s)</th>
							<th>Celular(es)</th>
							<th>Alcaldia</th>
							<th class="tOpciones" width="100px">Opciones</th>
						</tr>
					</thead>
					<tbody>
						<?php 
							for($index = 0; $index < $nroRegistros; $index++) {
								$sucursal = $this->coneccion->get_objeto();
						?>
							<tr>
								<td><?php echo $sucursal->suc_identificador?></td>
								<td><?php echo $sucursal->suc_direccion?></td>
								<td><?php echo $sucursal->suc_telefono?></td>
								<td><?php echo $sucursal->suc_celular?></td>
								<td><?php echo $sucursal->suc_alcaldia?></td>
								<td align="center">
									<a class="linkOpciones" href="gestor.php?mod=<?php echo $this->modulo?>&tarea=SUCURSAL&accion=editar&id=<?php echo  $sucursal->suc_id; ?>"><img src="images/b_edit.png" alt="EDITAR SUCURSAL" title="EDITAR SUCURSAL" border="0"></a>
									<a class="linkOpciones" href="gestor.php?mod=<?php echo $this->modulo?>&tarea=SUCURSAL&accion=ver&id=<?php echo  $sucursal->suc_id; ?>"><img src="images/b_search.png" alt="VER SUCURSAL" title="VER SUCURSAL" border="0"></a>
									<a class="linkOpciones" href="gestor.php?mod=<?php echo $this->modulo?>&tarea=SUCURSAL&accion=eliminar&id=<?php echo  $sucursal->suc_id; ?>"><img src="images/b_drop.png" alt="ELIMINAR" title="ELIMINAR" border="0"></a>
								</td>
							</tr>
						<?php
								$this->coneccion->siguiente(); 
							}
						?>
					</tbody>
				</table>
			</div>
	</form>
</div>

<?php
	}
	
	function eliminar_sucursal() {
		$sucursal_id = $_GET['id'];
		$sql_verificar_facturas = 'select * from fac_factura where fac_estado="emitido" and fac_sucursal_id=' . $sucursal_id;
		$this->coneccion->ejecutar($sql_verificar_facturas);
		if ($this->coneccion->get_num_registros() > 0) {
			$mensaje = 'No puede procesar la eliminacion esta sucursal tiene factura(s) emitida(s).';
			$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'error');

		} else {
			$sql_eliminar_sucursal = "DELETE FROM fac_sucursal WHERE  `suc_id`=$sucursal_id";

			$this->coneccion->ejecutar($sql_eliminar_sucursal);
			
			$mensaje = 'Sucursal Eliminada correctamente!!';
			$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tare=ACCEDER');
		}
	}
	
	function formulario_sucursal($accion) {
		
		if($this->datos_sucursal())
		{
			if ($accion == 'nuevo') {
				$this->guardar_sucursal();
			} else {
				$this->actualizar_sucursal();
			}
		}

		$conec= new ADO();
		$id = 0;
		if ($_GET['id']) {
			$id = $_GET['id'];
		}
		$sql = "select * from fac_sucursal where suc_identificador=0 and suc_id=$id";
			
		$conec->ejecutar($sql);
		
		$objeto = $conec->get_objeto();
		
		// Cargado de datos para el formulario
		$identificador = $objeto->suc_identificador;
		
		$direccion = $objeto->suc_direccion;
		
		$telefonos = $objeto->suc_telefono;
		
		$celulares = $objeto->suc_celular;
		
		$alcaldia = $objeto->suc_alcaldia;


		$url = $this->link.'?mod='.$this->modulo . '&tarea=ACCEDER';
		
		if ($accion != 'ver') {
			$url_accion = $this->link.'?mod='.$this->modulo . '&tarea=' . $_GET['tarea'] . '&accion=' . $accion;
			if ($id > 0) {
				$url_accion .= '&id=' . $id;
			}
		}
		$this->formulario->dibujar_tarea();
		
		if($this->mensaje != "")
		{
				
			$this->formulario->mensaje('Correcto',$this->mensaje);
		}

?>
<div id="Contenedor_NuevaSentencia">
	<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url_accion;?>" method="POST" enctype="multipart/form-data">
		<div id="FormSent">
			<!-- ==============================================================================================-->
			<div class="Subtitulo">Datos Sucursal</div>
			<div id="ContenedorSeleccion">

				<div id="ContenedorDiv">
					<div class="Etiqueta">
						<span class="flechas1">* </span>Identificador
					</div>
					<div id="CajaInput">
						<input type="text" class="caja_texto" name="identificador" id="identificador" size="45" value="<?php echo $identificador;?>">
					</div>
				</div>
				
				<div id="ContenedorDiv">
					<div class="Etiqueta">
						<span class="flechas1">* </span>Direccion
					</div>
					<div id="CajaInput">
						<input type="text" class="caja_texto" name="direccion" id="direccion" size="45" value="<?php echo $direccion;?>">
					</div>
				</div>
				<!--Fin-->
				<!--Inicio-->
				<div id="ContenedorDiv">
					<div class="Etiqueta">Telefono(s)</div>
					<div id="CajaInput">
						<input type="text" class="caja_texto" name="telefono" id="telefono" size="35" value="<?php echo $telefonos;?>">
					</div>
				</div>
				<!--Fin-->
				<!--Inicio-->
				<div id="ContenedorDiv">
					<div class="Etiqueta">Celular(es)</div>
					<div id="CajaInput">
						<input type="text" class="caja_texto" name="celular" id="celular" size="35" value="<?php echo $celulares;?>">
					</div>
				</div>
				<!--Inicio-->
				<div id="ContenedorDiv">
					<div class="Etiqueta">Alcaldia</div>
					<div id="CajaInput">
						<input type="text" class="caja_texto" name="alcaldia" id="alcaldia" size="35" value="<?php echo $alcaldia;?>">
					</div>
				</div>
				<!--Inicio-->
				<div id="ContenedorDiv">
					<label style="color: red;">NOTA:</label><br> 
					<label style="font-size: 9px;">
					La informaci&oacute;n
					que ingrese en el formulario tiene que ser llenado correctamente
					sin errores ortogr&aacute;ficos, ya que todas las facturas
					ir&aacute;n con esa informaci&oacute;n agregada que el cliente
					presentar&aacute; al SIN (Servicio de Impuestos Nacionales).<br>
					Si tiene m&aacute;s de un n&uacute;mero de telefono o celular separelo con "/".
					</label>
				</div>
			</div>
			<!-- ==============================================================================================-->
		</div>
		<div id="ContenedorDiv">
			<div id="CajaBotones" align="center">
				<?php if ($accion != 'ver') {?>
					<input type="submit" class="boton" name="" value="Guardar">
				<?php }?> 
					<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $url;?>';">
			</div>
		</div>
	</form>
</div>

<?php
	
	}
}
?>