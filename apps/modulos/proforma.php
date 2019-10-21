 <?php

include_once("proforma.class.php");

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) {
        case "agregar":
            agregar($mensaje, $accion, $datos, $opciones);
            break;
		case "modificar":
            modificar($mensaje, $accion, $datos, $opciones);
            break;
		case "eliminar":
            eliminar($mensaje, $accion, $datos, $opciones);
            break;
		case "enviaremail":
            enviaremail($mensaje, $accion, $datos, $opciones);
            break;
    }
}

function enviaremail(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
	$proforma = new PROFORMA();
    if (is_numeric($proforma->usuDatos[2])) {
	
		$resEliminar = $proforma->reenviar_correo($jsonDatos);
		if($resEliminar->estado){
			
			$mensaje = $resEliminar->mensaje;  
            $accion = $resEliminar->accion;
			
		} else {
			$mensaje = $resEliminar->mensaje; 
			$accion = $resEliminar->accion;
		}
		
    } else {
        $mensaje = "No has iniciado sesion";
		$accion = "error";
    }
}

function eliminar(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
		
	$proforma = new PROFORMA();
    if (is_numeric($proforma->usuDatos[2])) {
	
		$resEliminar = $proforma->eliminar($jsonDatos);
		if($resEliminar->estado){
		
			$mensaje = $resEliminar->mensaje;  
            $accion = "correcto";
			
		} else {
			$mensaje = "Error al eliminar en el servidor"; 
			$accion = "error";
		}
		
    } else {
        $mensaje = "No has iniciado sesion";
		$accion = "error";
    }
}

function agregar(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
		
	$proforma = new PROFORMA();
    if (is_numeric($proforma->usuDatos[2])) {
	
		$resAgregar = $proforma->agregar($jsonDatos);
		if($resAgregar->estado){
			$datos = $resAgregar->id;
			$mensaje = $resAgregar->mensaje; 
            $accion = "correcto";
		} else {
			$mensaje = "Error al guardar la base de datos"; 
			$accion = "error";
		}
		
    } else {
        $mensaje = "No has iniciado sesion";
		$accion = "error";
    }
}

function modificar(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
	
	$proforma = new PROFORMA();
    if (is_numeric($proforma->usuDatos[2])) {
	
		$resModificar = $proforma->modificar($jsonDatos);
		if($resModificar->estado){
		
			$mensaje = $resModificar->mensaje; 
            $accion = "correcto";
			
		} else {
			$mensaje = "Error al guardar la base de datos"; 
			$accion = "error";
		}
		
    } else {
        $mensaje = "No has iniciado sesion";
		$accion = "error";
    }
}

$json = '{
	"respuesta":
	{
		"mensaje": "' . $mensaje . '",
		"accion": "' . $accion . '",
		"datos":' . $datos . ',
		"opciones":' . $opciones . '
	}
}';

// Responder json
if(isset($_POST)){
	echo '[' . $json . ']';
} else {
	echo 'iqCallback([' . $json . '])';
}

?>

