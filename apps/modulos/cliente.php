<?php

include_once("cliente.class.php");

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
    }
}

function agregar(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
		
	$Cliente = new CLIENTE();
    if (is_numeric($Cliente->usuDatos[2])) {
	
		$resAgregar = $Cliente->agregar($jsonDatos);
		if($resAgregar->estado){
		
			$mensaje = $resAgregar->mensaje; 
            $accion = "correcto";
			
		} else {
			$mensaje = $resAgregar->mensaje; 
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
	
	$Cliente = new CLIENTE();
    if (is_numeric($Cliente->usuDatos[2])) {
	
		$resModificar = $Cliente->modificar($jsonDatos);
		if($resModificar->estado){
		
			$mensaje = $resModificar->mensaje; 
            $accion = "correcto";
			
		} else {
			$mensaje = $resModificar->mensaje; 
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

