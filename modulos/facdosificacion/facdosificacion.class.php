<?php
class FACDOSIFICACION extends BUSQUEDA {
	var $mensaje;
	var $formulario;

	function FACDOSIFICACION()
	{
		//permisos
		$this->ele_id = 178;
		
		$this->busqueda();
		
		$this->link='gestor.php';
		
		$this->modulo='facdosificacion';
		
		$this->formulario = new FORMULARIO();
		
		$this->coneccion = new ADO();
		
		$this->formulario->set_titulo('DOSIFICACION PARA FACTURAS');
		
		$this->arreglo_campos[0]["nombre"] = "dos_id";
		$this->arreglo_campos[0]["texto"] = "Nro";
		$this->arreglo_campos[0]["tipo"] = "cadena";
		$this->arreglo_campos[0]["tamanio"] = 40;
		
		$this->arreglo_campos[1]["nombre"] = "dos_numero_autorizacion";
		$this->arreglo_campos[1]["texto"] = "Nro Autorizaci&oacute;n";
		$this->arreglo_campos[1]["tipo"] = "cadena";
		$this->arreglo_campos[1]["tamanio"] = 40;
		
	}
	
	private function dibujar_listado() {
		$sql_dosificaciones = "select * from fac_dosificacion where dos_eliminado is null";

		$this->set_sql($sql_dosificaciones, ' order by dos_id desc');
		
		$this->set_opciones();
		
		$this->dibujar();
	}
	
	function set_opciones() {
	
		$nun = 0;
	
		if ($this->verificar_permisos('VER')) {
			$this->arreglo_opciones[$nun]["tarea"] = 'VER';
			$this->arreglo_opciones[$nun]["imagen"] = 'images/b_search.png';
			$this->arreglo_opciones[$nun]["nombre"] = 'VER';
			$nun++;
		}
		
		if ($this->verificar_permisos('MODIFICAR')) {
			$this->arreglo_opciones[$nun]["tarea"] = 'MODIFICAR';
			$this->arreglo_opciones[$nun]["imagen"] = 'images/b_edit.png';
			$this->arreglo_opciones[$nun]["nombre"] = 'MODIFICAR';
			$nun++;
		}
		
//		if ($this->verificar_permisos('ELIMINAR')) {
//			$this->arreglo_opciones[$nun]["tarea"] = 'ELIMINAR';
//			$this->arreglo_opciones[$nun]["imagen"] = 'images/b_drop.png';
//			$this->arreglo_opciones[$nun]["nombre"] = 'ELIMINAR';
//			$nun++;
//		}

	}
	
	function dibujar_encabezado() {
		?>
	        <tr>
	            <th>Nro</th>
	            <th>Nro Autorizaci&oacute;n</th>
	            <th>Fecha Autorizaci&oacute;n</th>
	            <th>Fecha Finalizaci&oacute;n</th>
	            <th>Fecha Modificaci&oacute;n</th>
	            <th class="tOpciones">Opciones</th>
	        </tr>
	
	        <?PHP
	}

    function mostrar_busqueda() {
        $conversor = new convertir();

        for ($i = 0; $i < $this->numero; $i++) {

            $objeto = $this->coneccion->get_objeto();

            echo '<tr>';

	            echo "<td>";
	            echo ($i + 1);
	            echo "&nbsp;</td>";

	            echo "<td>";
	            echo $objeto->dos_numero_autorizacion;
	            echo "&nbsp;</td>";

	            echo "<td>";
	            echo $conversor->get_fecha_latina($objeto->dos_fecha_autorizacion_inicial);
	            echo "&nbsp;</td>";

	            echo "<td>";
	            echo $conversor->get_fecha_latina($objeto->dos_fecha_autorizacion_final);
	            echo "&nbsp;</td>";

	            echo "<td>";
	            if ($objeto->dos_fecha_modificacion) {
	            	echo $conversor->get_fecha_latina($objeto->dos_fecha_modificacion);
	            }
	            echo "&nbsp;</td>";

	            echo "<td style='width:210px;'>";
	            echo $this->get_opciones($objeto->dos_id);
	            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }
	
    function dibujar_busqueda() {

    	$this->formulario->dibujar_cabecera();
		$this->dibujar_listado();
    }
    
    function datos_dosificacion()
    {
    	if($_POST) {
    
    		if (!empty($_POST['fecha_autorizacion']) && !empty($_POST['fecha_finalizacion'])) {
    			$convertor = new convertir();
    			$fecha_inicial = $convertor->get_fecha_mysql($_POST['fecha_autorizacion']);
    			$fecha_final = $convertor->get_fecha_mysql($_POST['fecha_finalizacion']);
    			$fecha_inicial = new \DateTime($fecha_inicial);
    			$fecha_final = new \DateTime($fecha_final);

    			 if ($fecha_inicial >= $fecha_final) {
    			 	$mensaje = 'La fecha de Autorizacion debe ser menor a la Fecha Limite de Emision';
    			 	$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'error');
    			 	exit;
    			 }
    		}
    		
    		if ($_POST['nro_autorizacion'] == '' || $_POST['llave_dosificacion'] == '' || $_POST['fecha_autorizacion'] == '' || $_POST['fecha_finalizacion'] == '') {
    
    			$mensaje = 'Debe llenar todo los campos marcados con (*).';
    			$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'error');
    			exit;
    		}
    			
    		return true;
    	} else {
    			
    		return false;
    	}
    }
    
    function eliminar_dosificacion() {
    	$dosificacion_id = $_GET['id'];
    	$sql_verificar_facturas = 'select * from fac_factura where fac_estado="emitido" and fac_dosificacion_id=' . $dosificacion_id;
    	$this->coneccion->ejecutar($sql_verificar_facturas);
    	if ($this->coneccion->get_num_registros() > 0) {
    		$mensaje = 'No puede procesar la eliminacion esta dosificacion tiene factura(s) emitida(s).';
    		$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'error');
    
    	} else {
    		$fecha_registro = new \DateTime('now');
    		$fecha_registro = $fecha_registro->format('Y-m-d H:i:s');
    		$sql_eliminar = "update fac_dosificacion set dos_eliminado='$fecha_registro' WHERE  `dos_id`=$dosificacion_id";

    		$this->coneccion->ejecutar($sql_eliminar);
    			
    		$mensaje = 'Dosificacion Eliminada correctamente!!';
    		$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tare=ACCEDER');
    	}
    }
    
	function guardar_dosificacion()
	{
	
		$this->mensaje = "La dosificacion fue agregada correctamente";
		
		$conec= new ADO();
		$convertir = new convertir;
		$dias_autorizacion = 180;
		$fecha_autorizacion = $_POST['fecha_autorizacion'];
		$fecha_autorizacion = $convertir->get_fecha_mysql($fecha_autorizacion);
		$fecha_finalizacion = $convertir->get_fecha_mysql($_POST['fecha_finalizacion']);
		$registro = date('Y-m-d');

		// Verificar que no exista Dosificacion para la fecha ingresada
		$sql_fecha_inicial = "select * from fac_dosificacion where dos_fecha_autorizacion_inicial BETWEEN '$fecha_autorizacion' and '$fecha_finalizacion'";
		$sql_fecha_final = "select * from fac_dosificacion where dos_fecha_autorizacion_final BETWEEN '$fecha_autorizacion' and '$fecha_finalizacion'";
		$this->coneccion->ejecutar($sql_fecha_inicial);
		$registros_inicial = $this->coneccion->get_num_registros();
		$this->coneccion->ejecutar($sql_fecha_final);
		$registros_final = $this->coneccion->get_num_registros();
		
		if ($registros_inicial > 0 || $registros_final > 0) {
			$mensaje = 'Ya existe registro de dosificacion para este periodo : ' . $fecha_autorizacion . ' , ' . $fecha_finalizacion;
			$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'error');
			exit;
		}

		$sql = "INSERT INTO 
			fac_dosificacion 
			(`dos_numero_autorizacion`, 
			`dos_llave_dosificacion`, 
			`dos_fecha_autorizacion_inicial`, 
			`dos_fecha_autorizacion_final`, 
			`dos_fecha_registro`) 
			VALUES 
			('" . $_POST['nro_autorizacion'] . "', 
			'" . $_POST['llave_dosificacion'] . "', 
			'" . $fecha_autorizacion . "', 
			'" . $fecha_finalizacion . "', 
			'" . $registro . "')";

		$conec->ejecutar($sql);
		$this->formulario->ventana_volver($this->mensaje, $this->link . '?mod=' . $this->modulo);
		exit;
	}

	function actualizar_dosificacion()
	{
	
		$this->mensaje = "La informaci&oacute;n fue actualizada correctamente";
		$dosificacion_id = $_GET['id'];
		$conec= new ADO();
		$conec= new ADO();
		$convertir = new convertir;
		$dias_autorizacion = 180;
		$fecha_autorizacion = $_POST['fecha_autorizacion'];
		$fecha_autorizacion = $convertir->get_fecha_mysql($fecha_autorizacion);
		$fecha_finalizacion = $convertir->get_fecha_mysql($_POST['fecha_finalizacion']);
		$registro = date('Y-m-d');
		$usuario = new USUARIO;
		$usuario_int_id = $usuario->get_usu_per_id();
		
		// Verificar que no exista para la dosificacion
		$sql_factura_dosificacion = "select * from fac_factura where fac_dosificacion_id=$dosificacion_id and fac_estado='emitido'";
		$this->coneccion->ejecutar($sql_factura_dosificacion); 
		if ($this->coneccion->get_num_registros() > 0) {
			$mensaje = 'No se puede modificar la informacion de la dosificacion, ya existen facturas emitidas para esta.';
			$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'error');
			exit;
		}
	
		// Verificar que no exista Dosificacion para la fecha ingresada
		$sql_fecha_inicial = "select * from fac_dosificacion where dos_fecha_autorizacion_inicial BETWEEN '$fecha_autorizacion' and '$fecha_finalizacion' and dos_id!=$dosificacion_id";
		$sql_fecha_final = "select * from fac_dosificacion where dos_fecha_autorizacion_final BETWEEN '$fecha_autorizacion' and '$fecha_finalizacion' and dos_id!=$dosificacion_id";
		$this->coneccion->ejecutar($sql_fecha_inicial);
		$registros_inicial = $this->coneccion->get_num_registros();
		$this->coneccion->ejecutar($sql_fecha_final);
		$registros_final = $this->coneccion->get_num_registros();

		if ($registros_inicial > 0 || $registros_final > 0) {
			$mensaje = 'Ya existe registro de dosificacion para este periodo : ' . $fecha_autorizacion . ' , ' . $fecha_finalizacion;
			$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'error');
			exit;
		} 

		$sql_update = "update fac_dosificacion set
		dos_numero_autorizacion='" . $_POST['nro_autorizacion'] . "',
		dos_llave_dosificacion='" . $_POST['llave_dosificacion'] . "',
		dos_fecha_autorizacion_inicial='" . $fecha_autorizacion . "',
		dos_fecha_autorizacion_final='" . $fecha_finalizacion . "',
		dos_fecha_modificacion='" . $registro . "',
		dos_modificacion_int_id='" . $usuario_int_id . "'
			where dos_id=$dosificacion_id";
		$this->coneccion->ejecutar($sql_update);
	
	}
	
	function formulario_dosificacion($tarea) {
		
		if($this->datos_dosificacion())
		{
			if ($tarea == 'AGREGAR') {
				$this->guardar_dosificacion();
			} else {
				$this->actualizar_dosificacion();
			}
		}

		$conec= new ADO();
		$id = 0;
		if ($_GET['id']) {
			$id = $_GET['id'];
		}
		$sql = "select * from fac_dosificacion where dos_id=$id";
			
		$conec->ejecutar($sql);
		
		$objeto = $conec->get_objeto();
		
		// Cargado de datos para el formulario
		$nro_autorizacion = $objeto->dos_numero_autorizacion;
		$llave_dosificacion = $objeto->dos_llave_dosificacion;
		$fecha_autorizacion = $objeto->dos_fecha_autorizacion_inicial;
		$fecha_finalizacion = $objeto->dos_fecha_autorizacion_final;
		$fecha_modificacion = $objeto->dos_fecha_modificacion;
		$nombre_usuario = '';
		if ($objeto->dos_modificacion_int_id) {
			$sql_usuario = "select * from interno where int_id=$objeto->dos_modificacion_int_id";
			$this->coneccion->ejecutar($sql_usuario);
			$usuario = $this->coneccion->get_objeto();
			$nombre_usuario = $usuario->int_nombre . ' ' . $usuario->int_apellido;
		}
		
		$convertir = new convertir;
		
		$url = $this->link.'?mod='.$this->modulo . '&tarea=ACCEDER';
		
		if ($tarea != 'VER') {
			$url_accion = $this->link.'?mod='.$this->modulo . '&tarea=' . $_GET['tarea'];
			if ($id > 0) {
				$url_accion .= '&id=' . $id;
			}
		}

		$this->formulario->dibujar_tarea();
		
		if($this->mensaje != "")
		{
			echo '<h3><strong style="color: green;">' . $this->mensaje . '</strong></h3>';
		}

?>
		<!--MaskedInput-->
			<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
		<!--MaskedInput-->
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url_accion;?>" method="POST" enctype="multipart/form-data">
				<div id="FormSent">
					<!-- ==============================================================================================-->
					<div class="Subtitulo">Datos</div>
					<div id="ContenedorSeleccion">
		
						<div id="ContenedorDiv">
							<div class="Etiqueta">
								<span class="flechas1">* </span>Nro Autorizaci&oacute;n
							</div>
							<div id="CajaInput">
								<input type="text" class="caja_texto" name="nro_autorizacion" id="nro_autorizacion" size="45" value="<?php echo $nro_autorizacion;?>">
							</div>
						</div>
						
						<div id="ContenedorDiv">
							<div class="Etiqueta">
								<span class="flechas1">* </span>Llave Dosificaci&oacute;n
							</div>
							<div id="CajaInput">
								<input type="text" class="caja_texto" name="llave_dosificacion" id="llave_dosificacion" size="45" value="<?php echo $llave_dosificacion;?>">
							</div>
						</div>
						<!--Fin-->
						<!--Inicio-->
						<div id="ContenedorDiv">
							<div class="Etiqueta"><span class="flechas1">* </span>Fecha Autorizaci&oacute;n</div>
							<div id="CajaInput">
								<input type="text" class="caja_texto" name="fecha_autorizacion" id="fecha_autorizacion" size="12" value="<?php echo ($fecha_autorizacion) ? $convertir->get_fecha_latina($fecha_autorizacion) : date('d/m/Y');?>">
							</div>
						</div>
						<!--Fin-->
						<!--Inicio-->
						<div id="ContenedorDiv">
							<div class="Etiqueta">Fecha Limite Emisi&oacute;n</div>
							<div id="CajaInput">
								<input type="text" class="caja_texto" name="fecha_finalizacion" id="fecha_finalizacion" size="12" value="<?php echo ($fecha_finalizacion) ? $convertir->get_fecha_latina($fecha_finalizacion) : '';?>">
							</div>
						</div>
						<!--Fin-->
						<?php if ($tarea == 'VER') :?>
						<!--Inicio-->
						<div id="ContenedorDiv">
							<div class="Etiqueta">Modificac&oacute;n Realizada</div>
							<div id="CajaInput">
								<input type="text" class="caja_texto" name="usuario_modificacion" id="usuario_modificacion" size="12" value="<?php echo ($fecha_modificacion) ? $convertir->get_fecha_latina($fecha_modificacion) : '';?>" readonly>
							</div>
						</div>
						<!--Fin-->
						<!--Inicio-->
						<div id="ContenedorDiv">
							<div class="Etiqueta">Fecha Modificac&oacute;n</div>
							<div id="CajaInput">
								<input type="text" class="caja_texto" name="fecha_modificacion" id="fecha_modificacion" size="12" value="<?php echo $nombre_usuario?>" readonly>
							</div>
						</div>
						<!--Fin-->
						<?php endif;?>
					</div>
					<!-- ==============================================================================================-->
				</div>
				<div id="ContenedorDiv">
					<div id="CajaBotones" align="center">
						<?php if ($tarea != 'VER') {?>
							<input type="submit" class="boton" name="" value="Guardar">
						<?php }?> 
							<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $url;?>';">
					</div>
				</div>
				<script>
					jQuery(function($) {
						$("#fecha_autorizacion").mask("99/99/9999");
						$("#fecha_finalizacion").mask("99/99/9999");
					});
				</script>
			</form>
		</div>
<?php
	
	}
}

	