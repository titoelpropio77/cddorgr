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
		case "buscar":
            buscar($mensaje, $accion, $datos, $opciones);
            break;
		case "lista":
            lista($mensaje, $accion, $datos, $opciones);
            break;
		case "tipoContacto":
            tipo_contacto($mensaje, $accion, $datos, $opciones);
            break;
		case "ver":
            ver($mensaje, $accion, $datos, $opciones);
            break;
    }
}
function tipo_contacto(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
	$Seguimiento = new SEGUIMIENTO();
    if (is_numeric($Seguimiento->usuDatos[2])) {
		$buscarObj = $Seguimiento->tipo_contacto_lista();
		$datos = json_encode($buscarObj);
    } else {
        $mensaje = "No has iniciado sesion";
		$accion = "error";
    }
}

function lista(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
	
	$Seguimiento = new SEGUIMIENTO();
    if (is_numeric($Seguimiento->usuDatos[2])) {
	
		$respLista = $Seguimiento->lista();
		if($respLista->estado){ 
			$opciones = json_encode($respLista->opciones);
			$datos = json_encode($respLista->datos);
			$mensaje = "Datos cargados lista";
			$accion = "correcto";
		} else {
			$mensaje = "No se encontro registros";
			$accion = "error";
		}
    } else {
        $mensaje = "No has iniciado sesion";
		$accion = "error";
    }
}

function ver(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
	$Seguimiento = new SEGUIMIENTO();
    if (is_numeric($Seguimiento->usuDatos[2])) {
		$buscarObj = $Seguimiento->ver($jsonDatos);
		if($buscarObj->estado){
			$datos = json_encode($buscarObj->datos);
			$mensaje = utf8_encode($buscarObj->mensaje);
			$accion = $buscarObj->accion; 
		} else {
			$mensaje = utf8_encode($buscarObj->mensaje);
			$accion = "error";
		} 
    } else {
        $mensaje = "No has iniciado sesion";
		$accion = "error";
    }
}

function buscar(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
	
	$Seguimiento = new SEGUIMIENTO();
    if (is_numeric($Seguimiento->usuDatos[2])) {
	
		$buscarObj = $Seguimiento->buscar($jsonDatos);
		
		$datos = json_encode($buscarObj->datos);
		$mensaje = utf8_encode($buscarObj->mensaje);
		$accion = $buscarObj->accion;
		
    } else {
        $mensaje = "No has iniciado sesion";
		$accion = "error";
    }
}

function eliminar(&$mensaje, &$accion, &$datos, &$opciones) {
	$datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);	
	$seguimiento = new SEGUIMIENTO();
    if (is_numeric($seguimiento->usuDatos[2])) {
		$resEliminar = $seguimiento->eliminar($jsonDatos);
		if($resEliminar->estado){
			$mensaje = utf8_encode($resEliminar->mensaje);  
            $accion = "correcto";
		} else { 
			$mensaje = utf8_encode($resEliminar->mensaje); 
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
			$mensaje = utf8_encode($resAgregar->mensaje); 
            $accion = "correcto";
		} else {
			$mensaje = utf8_encode($resAgregar->mensaje);
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
			$mensaje = utf8_encode($resModificar->mensaje); 
            $accion = "correcto"; 
		} else {
			$mensaje = utf8_encode($resModificar->mensaje); 
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

