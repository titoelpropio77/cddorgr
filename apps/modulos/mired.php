 <?php

include_once("mired.class.php");

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) {
        case "listar":
            listar($mensaje, $accion, $datos, $opciones);
            break;
    }
}

function listar(&$mensaje, &$accion, &$datos, &$opciones) {
	$mired = new MIRED();
    $datos = json_encode($mired->listar());
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

