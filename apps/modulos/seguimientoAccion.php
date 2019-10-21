<?php

include_once("seguimientoAccion.class.php");

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
		
	$accion = new ACCION();
    if (is_numeric($accion->usuDatos[2])) {
	
		$resEliminar = $accion->eliminar($jsonDatos);
		if($resEliminar->estado){
		
			$mensaje = $resEliminar->mensaje;  
            $accion = "correcto";
			
		} else {
			$mensaje = "Error al eliminar siguimiento accion"; 
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
		
	$accion = new ACCION();
    if (is_numeric($accion->usuDatos[2])) {
	
		$resAgregar = $accion->agregar($jsonDatos);
		if($resAgregar->estado){
		
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
	
	$accion = new ACCION();
    if (is_numeric($accion->usuDatos[2])) {
	
		$resModificar = $accion->modificar($jsonDatos);
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

