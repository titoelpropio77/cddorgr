<?php

include_once("seguimiento.class.php");

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
    }
}

function eliminar(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
		
	$seguimiento = new SEGUIMIENTO();
    if (is_numeric($seguimiento->usuDatos[2])) {
	
		$resEliminar = $seguimiento->eliminar($jsonDatos);
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
		
	$seguimiento = new SEGUIMIENTO();
    if (is_numeric($seguimiento->usuDatos[2])) {
	
		$resAgregar = $seguimiento->agregar($jsonDatos);
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
	
	$seguimiento = new SEGUIMIENTO();
    if (is_numeric($seguimiento->usuDatos[2])) {
	
		$resModificar = $seguimiento->modificar($jsonDatos);
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

