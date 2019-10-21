<?php
	
	require_once('facsucursal.class.php');
	
	$sucursal = new FACSUCURSAL();
	if ($_GET['tarea'] != "") {
		if (!($sucursal->verificar_permisos($_GET['tarea']))) {
			?>
	        <script>
	            location.href = "log_out.php";
	        </script>
	        <?php
	
	    }
	}
	
	switch ($_GET['tarea']) {
		case 'SUCURSAL': {
			if ($_GET['accion'] == 'eliminar' && $_GET['id']) {
				$sucursal->eliminar_sucursal();
			} elseif ($_GET['accion'] == 'ver' && $_GET['id']) {
				$sucursal->formulario_sucursal($_GET['accion']);
				
			} elseif ($_GET['accion'] == 'editar' && $_GET['id']) {
				$sucursal->formulario_sucursal($_GET['accion']);
			} elseif ($_GET['accion'] == 'nuevo') {
				$sucursal->formulario_sucursal($_GET['accion']);
			} 
			break;
		}
	
		default: {
			$sucursal->formulario_tcp();
			break;
	    }

	}
		
?>