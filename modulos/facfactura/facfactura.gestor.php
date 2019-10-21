<?php
require_once('facfactura.class.php');

$factura = new FACFACTURA();

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
		case 'VER': {
			$factura->formulario_factura();
			break;
		}

		case 'VER HISTORIAL': {
			if ($_GET['id']) {
				$factura->formulario_historial();
			}
			break;
		}
		
		case 'ANULAR': {
			$factura->anular_factura();
			break;
		}
	
		default: {
			$factura->dibujar_busqueda();
			break;
		}
	
	}