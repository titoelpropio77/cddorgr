<?php

include_once("comisiones.class.php");

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) {
        case "listar": {
                listar($mensaje, $accion, $datos, $opciones, $_GET['estado']);
                break;
            }
        case "gestiones": {
                gestiones($mensaje, $accion, $datos, $opciones);
                break;
            }

        case "historial": {
                // print_r($_POST);
                historial($mensaje, $accion, $datos, $opciones, $_POST);
                break;
            }
            
        case "detallado": {
            detallado($mensaje, $accion, $datos, $opciones, $_POST);
            break;
        }
        case "periodos": {
                periodos($mensaje, $accion, $datos, $opciones, $_GET['ges_id']);
                break;
            }
        case "foo": {
                foo($mensaje, $accion, $datos, $opciones);
                break;
            }
    }
}

function gestiones(&$mensaje, &$accion, &$datos, &$opciones) {
    $opcionesDatos = array();
    $com = new COMISIONES();
    $opcionesDatos['gestiones'] = $com->gestiones();
    $datos = json_encode($opcionesDatos);
    $opciones = "";
    $mensaje = "";
    $accion = "correcto";
}

function periodos(&$mensaje, &$accion, &$datos, &$opciones, $ges_id) {
    $opcionesDatos = array();
    $com = new COMISIONES();
    $opcionesDatos['periodos'] = $com->periodos($ges_id);
    $datos = json_encode($opcionesDatos);
    $opciones = "";
    $mensaje = "";
    $accion = "correcto";
}

function historial(&$mensaje, &$accion, &$datos, &$opciones, $params) {
    $opcionesDatos = array();
    $com = new COMISIONES();
    $datosForm = preg_replace('/\\\\\"/', "\"", $_POST['datosForm']);
    $params = json_decode($datosForm);
    $opcionesDatos['historial'] = $com->historial($params);
    $datos = json_encode($opcionesDatos);
    $opciones = "";
    $mensaje = "";
    $accion = "correcto";
}

function detallado(&$mensaje, &$accion, &$datos, &$opciones, $params) {
    $opcionesDatos = array();
    $com = new COMISIONES();
    $datosForm = preg_replace('/\\\\\"/', "\"", $_POST['datosForm']);
    $params = json_decode($datosForm);
    $opcionesDatos['detalle'] = $com->detallado($params);
    $datos = json_encode($opcionesDatos);
    $opciones = "";
    $mensaje = "";
    $accion = "correcto";
}

function foo(&$mensaje, &$accion, &$datos, &$opciones) {
    $opcionesDatos = array();
    $objeto = new stdClass();
//    $d = FUNCIONES::objeto_bd_sql("select int_nombre,int_apellido from interno where int_id=1");

    $objeto->valor = "adfadf";
    $objeto->nombre = "dfdfdfdfdfdf";
    array_push($opcionesDatos, $objeto);
    $datos = json_encode($opcionesDatos);
//    $datos = json_encode($opcionesDatos);
    $opciones = "";
    $mensaje = "hola che";
    $accion = "correcto";
}

function listar(&$mensaje, &$accion, &$datos, &$opciones, $estado) {

    $opcionesDatos = array();
    $comisiones = new COMISIONES();

    $s = new stdClass();
    $s->lista = array();
    $opcionesDatos['BIR'] = $s;
    $opcionesDatos['BVI'] = $s;
    $opcionesDatos['BRA'] = $s;

//    if ($estado == 'Pendiente') {
//        $opcionesDatos['BIR'] = $comisiones->pendientes("BIR");
//        $opcionesDatos['BVI'] = $comisiones->pendientes("BVI");
//        $opcionesDatos['BRA'] = $comisiones->pendientes("BRA");
//    } else if ($estado == 'Pagado') {
//        $opcionesDatos['BIR'] = $comisiones->pagadas("BIR");
//        $opcionesDatos['BVI'] = $comisiones->pagadas("BVI");
//        $opcionesDatos['BRA'] = $comisiones->pagadas("BRA");
//    }


    $opcionesDatos['BIR'] = $comisiones->listar("BIR", "'Pendiente','Pagado'");
    $opcionesDatos['BVI'] = $comisiones->listar("BVI", "'Pendiente','Pagado'");
    $opcionesDatos['BRA'] = $comisiones->listar("BRA", "'Pendiente','Pagado'");


    $datos = json_encode($opcionesDatos);
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

