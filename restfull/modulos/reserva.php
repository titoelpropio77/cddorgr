<?php

include_once("reserva.class.php");

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) {
        case "agregar": {
                agregar($mensaje, $accion, $datos, $opciones);
                break;
            }
        case "modificar": {
                modificar($mensaje, $accion, $datos, $opciones);
                break;
            }
        case "eliminar": {
                eliminar($mensaje, $accion, $datos, $opciones);
                break;
            }
        case "listar": {
                listar($mensaje, $accion, $datos, $opciones);
                break;
            }
    }
}

function eliminar(&$mensaje, &$accion, &$datos, &$opciones) {
    $datosForm = preg_replace('/\\\\\"/', "\"", $_POST['datosForm']);
    $jsonDatos = json_decode($datosForm);

    $seguimiento = new RESERVA();
    if (is_numeric($seguimiento->usuDatos[2])) {

        $resEliminar = $seguimiento->eliminar($jsonDatos);
        if ($resEliminar->estado) {

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
    $datosForm = preg_replace('/\\\\\"/', "\"", $_POST['datosForm']);
    $jsonDatos = json_decode($datosForm);

    $seguimiento = new RESERVA();
    if (is_numeric($seguimiento->usuDatos[2])) {
        $resAgregar = $seguimiento->agregar($jsonDatos);

        if ($resAgregar->estado) {
            $datos = $resAgregar->id;
            $mensaje = $resAgregar->mensaje;
            $accion = $resAgregar->accion;
            $opciones = "";
        } else {
            $mensaje = $resAgregar->mensaje;
            $accion = $resAgregar->accion;
            $opciones = "";
        }
    } else {
        $mensaje = "No has iniciado sesion";
        $accion = "error";
        $opciones = "";
    }
}

function modificar(&$mensaje, &$accion, &$datos, &$opciones) {
    $datosForm = preg_replace('/\\\\\"/', "\"", $_POST['datosForm']);
    $jsonDatos = json_decode($datosForm);

    $seguimiento = new RESERVA();
    if (is_numeric($seguimiento->usuDatos[2])) {

        $resModificar = $seguimiento->modificar($jsonDatos);
        $mensaje = $resModificar->mensaje;
        $accion = $resModificar->accion;
    } else {
        $mensaje = "No has iniciado sesion";
        $accion = "error";
    }
}

function listar(&$mensaje, &$accion, &$datos, &$opciones) {
//    $datosForm = preg_replace('/\\\\\"/', "\"", $_POST['datosForm']);
//    $jsonDatos = json_decode($datosForm);
//    echo "listando desde restfull...";
    $reserva = new RESERVA();

    if (is_numeric($reserva->usuDatos[2])) {

        $opcionesDatos = array();

        $opcionesDatos['PEND'] = $reserva->listar("Pendiente");
        $opcionesDatos['HAB'] = $reserva->listar("Habilitado");
        $opcionesDatos['DES'] = $reserva->listar("Deshabilitado");
        $opcionesDatos['CONC'] = $reserva->listar("Concretado");
        $opcionesDatos['EXP'] = $reserva->listar("Expirado");
        $opcionesDatos['DEV'] = $reserva->listar("Devuelto");

        $datos = json_encode($opcionesDatos);
        $opciones = "";
        $mensaje = "";
        $accion = "correcto";
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