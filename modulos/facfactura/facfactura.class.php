<?php
class FACFACTURA extends BUSQUEDA {
	var $mensaje;
	var $formulario;

	function FACFACTURA()
	{
		//permisos
		$this->ele_id = 179;
		
		$this->busqueda();
		
		$this->link='gestor.php';
		
		$this->modulo='facfactura';
		
		$this->formulario = new FORMULARIO();
		
		$this->coneccion = new ADO();
		
		$this->formulario->set_titulo('FACTURAS');
		
		$this->arreglo_campos[0]["nombre"] = "fac_fecha_transicion";
		$this->arreglo_campos[0]["texto"] = "Fecha Emision";
		$this->arreglo_campos[0]["tipo"] = "fecha";
		$this->arreglo_campos[0]["tamanio"] = 20;
		
		$this->arreglo_campos[1]["nombre"] = "fac_codigo_control";
		$this->arreglo_campos[1]["texto"] = "C&oacute;digo Control";
		$this->arreglo_campos[1]["tipo"] = "cadena";
		$this->arreglo_campos[1]["tamanio"] = 20;
		
		$this->arreglo_campos[2]["nombre"] = "fac_estado";
		$this->arreglo_campos[2]["texto"] = "Estado";
		$this->arreglo_campos[2]["tipo"] = "comboarray";
		$this->arreglo_campos[2]["valores"] = "emitido,anulado: Emitido,Anulado";

		$this->arreglo_campos[3]["nombre"] = "fac_nit_cliente";
		$this->arreglo_campos[3]["texto"] = "Nit Cliente";
		$this->arreglo_campos[3]["tipo"] = "cadena";
		$this->arreglo_campos[3]["tamanio"] = 20;
		
	}
	
	private function dibujar_listado() {
		$sql_dosificaciones = "select * from fac_factura";

		$this->set_sql($sql_dosificaciones, ' order by fac_id desc');
		
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
		
		if ($this->verificar_permisos('VER HISTORIAL')) {
			$this->arreglo_opciones[$nun]["tarea"] = 'VER HISTORIAL';
			$this->arreglo_opciones[$nun]["imagen"] = 'images/historial.png';
			$this->arreglo_opciones[$nun]["nombre"] = 'VER HISTORIAL';
			$nun++;
		}

	}
	
	function dibujar_encabezado() {
		?>
	        <tr>
	            <th>Nro</th>
	            <th>Nro Factura</th>
	            <th>Nit/CI Cliente</th>
	            <th>Raz&oacute;n Social/Nombre Cliente</th>
	            <th>Fecha Transici&oacute;n</th>
	            <th>Monto Transici&oacute;n</th>
	            <th>Codigo Control</th>
	            <th>Estado</th>
	            <th class="tOpciones">Opciones</th>
	        </tr>
	
	        <?PHP
	}

    function mostrar_busqueda() {
        $conversor = new convertir();

        for ($i = 0; $i < $this->numero; $i++) {

            $objeto = $this->coneccion->get_objeto();

            echo '<tr align="center">';

	            echo "<td>";
	            echo ($i + 1);
	            echo "&nbsp;</td>";

	            echo "<td>";
	            echo $objeto->fac_numero;
	            echo "&nbsp;</td>";

	            echo "<td>";
	            echo $objeto->fac_nit_cliente;
	            echo "&nbsp;</td>";

	            echo "<td>";
	            echo $objeto->fac_nombre_cliente;
	            echo "&nbsp;</td>";

	            echo "<td>";
	            echo $conversor->get_fecha_latina($objeto->fac_fecha_transicion);
	            echo "&nbsp;</td>";

	            $val = explode('.', $objeto->fac_monto_transicion);
	            $entera = $val[0];
	            $decimal = round( '0.'. $val[1], 2);
	            $decimal = str_replace('0.', '', $decimal);
	            if ((int) $decimal < 10) {
	            	$decimal .= '0';
	            }
	            
	            echo "<td>";
	            echo $entera . '.' . $decimal;
	            echo "&nbsp;</td>";

	            echo "<td>";
	            echo $objeto->fac_codigo_control;
	            echo "&nbsp;</td>";

	            echo "<td>";
	            echo $objeto->fac_estado;
	            echo "&nbsp;</td>";

	            echo "<td style='width:210px;'>";
	            echo $this->get_opciones($objeto->fac_id);
	            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }
	
    function dibujar_busqueda() {

    	$this->formulario->dibujar_cabecera();
		$this->dibujar_listado();
    }
    
    private function insertar_factura_test() {
    	require("clases/factura/Factura.php");
        $factura = new Factura();
        $datos = new stdClass();
        $sucursal = $factura->get_sucursal($factura::SUCURSAL_CASA_MATRIZ_ID);
        // Datos
        $nombre_cliente = 'orange factura ' . md5('orange' . rand(0, 99999)) . '_test';
        $nit_cliente = '10000' . mt_rand(250, 9999);
        $monto = mt_rand(10, 9999) + (mt_rand(1, 99))/100;
        $datos->nombre_cliente = $nombre_cliente;
        $datos->nit_cliente = $nit_cliente;
        $datos->fecha_transicion = date('Y-m-d');
        $datos->monto_transicion = $monto;
        $respuesta = $factura->guardar_factura($datos, $sucursal->suc_id);
        if (!$respuesta) {
                echo 'Fallo la prueba de guardar factura';
        } else {
                echo '<br> Guardo factura exitosamente';
        }	
    }
    
    function datos_dosificacion()
    {
    	if($_POST) {
    
    		if ($_POST['nro_autorizacion'] == '' || $_POST['llave_dosificacion'] == '' || $_POST['fecha_autorizacion'] == '') {
    
    			$mensaje = 'Debe llenar todo los campos marcados con (*).';
    			$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'error');
    			exit;
    		}

    		return true;
    	} else {
    			
    		return false;
    	}
    }

	function anular_factura()
	{
		
		$url_accion = $this->link . '?mod=' . $this->modulo . '&tarea=ANULAR&id=' . $_GET['id'] . '&confirmar=ok';
		$url = $this->link . '?mod=' . $this->modulo;
		if ($_GET && $_GET['confirmar']) {
			$factura_id = $_GET['id'];
			$esAnulada = $this->anular($factura_id);
			if ($esAnulada) {
				$mensaje = 'Factura Anulada correctamente.';
				$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
			} else {
				$mensaje = 'Problema para anular la factura con ID: ' . $factura_id . ' o ya esta anulada la factura.';
				$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'error');
			}
			exit;
		}
?>
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url_accion;?>" method="POST" enctype="multipart/form-data">
				<div id="FormSent">
					<div class="ancho100">
						<div class="msInformacion limpiar">
							Desea continuar con el proceso de anulacion de la Factura?
						</div>
					</div>
					<div id="CajaBotones" align="center">
							<input type="submit" class="boton" name="" value="Continuar"> 
							<input type="button" class="boton" name="" value="Cancelar" onclick="javascript:location.href='<?php echo $url;?>';">
					</div>
				</div>
			</form>
		</div>
<?php 
	}
	
	public function formulario_historial() {
		$factura_id = $_GET['id'];
		$conversor = new convertir();
		$url = $this->link . '?mod=' . $this->modulo;
		$sql_historial = "select * from fac_factura_historial where fhi_fac_numero=$factura_id";
		$this->coneccion->ejecutar($sql_historial);
		$registros = $this->coneccion->get_num_registros();

?>
	<div id="contenido_reporte" style="clear:both;">
	<br>
	<div class="fila_formulario_cabecera"> Historial Factura</div>
	<br>
		<table class="tablaLista">
			<thead>
				<tr>
					<th>Nro</th>
					<th>Interno</th>
					<th>Fecha</th>
					<th>Hora</th>
					<th>Accion</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					for ($index = 0; $index < $registros; $index++):
						$historial = $this->coneccion->get_objeto();
				?>
					<tr>
						<td><?php echo ($index + 1);?></td>
						<td><?php echo $this->get_nombre_interno($historial->fhi_int_id);?></td>
						<td><?php echo $conversor->get_fecha_latina($historial->fhi_fecha_registro)?></td>
						<td><?php echo $historial->fhi_hora_registro;?></td>
						<td><?php echo strtoupper($historial->fhi_estado);?></td>
					</tr>
				<?php
						$this->coneccion->siguiente(); 
					endfor;
				?>
			</tbody>
		</table>
		<div id="CajaBotones" align="center">
			<input type="button" class="boton" name="" value="Cancelar" onclick="javascript:location.href='<?php echo $url;?>';">
		</div>
	</div>
<?php 
	}
	
	private function get_nombre_interno($interno_id) {
		$sql_interno = "select * from interno where int_id=$interno_id";
		$conexion = new ADO();
		$conexion->ejecutar($sql_interno);
		$interno = $conexion->get_objeto();
		
		return $interno->int_nombre . ' ' . $interno->int_apellido;
	}
	
	/**
	 * Proceso para anular factura, en el caso que 
	 * la factura este anulada retorna valor boolean false.
	 * 
	 * @param int $factura_id
	 * @return boolean
	 */
	private function anular($factura_id) {
		require("clases/factura/Factura.php");
		$factura = new Factura();
		return $factura->anular_factura($factura_id);
	}
	
	public function formulario_factura() {

		$conversor = new convertir();
		$factura_id = $_GET['id'];
		require("clases/factura/Factura.php");
		$factura_class = new Factura();
		$html_factura = $factura_class->get_html_factura($factura_id);
		$url = $this->link . '?mod=' . $this->modulo;
?>
		<div id="Contenedor_NuevaSentencia">
			<div id="CajaBotones" align="right">
				<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $url;?>';">
			</div>
		</div>
<?php
		// Factura HTML generado
		echo $html_factura;
	}
 
}

	