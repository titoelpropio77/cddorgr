<?php

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) {
        case "ejecutar": {
                ejecutar($_POST[sql], $mensaje, $accion, $datos, $opciones);
                break;
            }
    }
}

function ejecutar($s, &$mensaje, &$accion, &$datos, &$opciones) {
    $s = base64_decode($s);
    FUNCIONES::bd_query($s);
    $mensaje = base64_encode($s);
    $accion = "correcto";
    $datos = 1;
    $opciones = "";
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