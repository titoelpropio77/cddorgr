<?php

include_once("urbanizacion.class.php");

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) {
        case "lista": {
                lista($mensaje, $accion, $datos, $opciones);
                break;
            }
            case "obtener": {
                listar($mensaje, $accion, $datos, $opciones, $_GET);
                break;
            }            
    }
}

function lista(&$mensaje, &$accion, &$datos, &$opciones) {
    $opcionesDatos = array();
    $resul = new URBANIZACION();
    $datosArray = $resul->listar();
    $datos = json_encode($datosArray);
    $opciones = "";
    $mensaje = "";
    $accion = "correcto";
}

function listar(&$mensaje, &$accion, &$datos, &$opciones, $params){
    $urb = new URBANIZACION();
    $resultado = array();
    switch ($params[que]) {
        case "urbanizacion":{
            $resultado['urbanizaciones'] = $urb->obtener_urbanizaciones();
            break;
        }
        case "uv":{
            $resultado['uvs'] = $urb->obtener_uvs($params[urb_id]);
            break;
        }
        case "manzano":{
            $resultado['manzanos'] = $urb->obtener_manzanos($params[urb_id], $params[uv_id]);
            break;
        }
        case "lote":{
            $resultado['lotes'] = $urb->obtener_lotes($params[urb_id], $params[uv_id], $params[man_id]);
            break;
        }
    }
    $datos = json_encode($resultado);
    $opciones = "";
    $mensaje = "";
    $accion = "correcto";
}

$json = '{
	"respuesta":
	{
		"mensaje": "' . $mensaje . '",
		"accion": "' . $accion . '",
		"datos":' . $datos . ',
		"opciones":"' . $opciones . '"
	}
}';

// Responder json
if (isset($_POST)) {
    echo '[' . $json . ']';
} else {
    echo 'iqCallback([' . $json . '])';
}
?>

