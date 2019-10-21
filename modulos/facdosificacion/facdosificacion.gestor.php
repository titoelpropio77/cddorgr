<?php
require_once('facdosificacion.class.php');

$factura = new FACDOSIFICACION();

	if ($_GET['tarea'] != "") {
		if (!($factura->verificar_permisos($_GET['tarea']))) {
		?>
	        <script>
	            location.href = "log_out.php";
	        </script>
	        <?php
	
	    }
	}
	
	switch ($_GET['tarea']) {
		case 'AGREGAR': {
			$factura->formulario_dosificacion($_GET['tarea']);
			break;
		}
		
		case 'VER': {
			$factura->formulario_dosificacion($_GET['tarea']);
			break;
		}
		
		case 'MODIFICAR': {
			$factura->formulario_dosificacion($_GET['tarea']);
			break;
		}
	
		case 'ELIMINAR': {
			$factura->eliminar_dosificacion();
			break;
		}
	
		default: {
			$factura->dibujar_busqueda();
			break;
		}
	
	}