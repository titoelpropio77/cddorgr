 <?php

include_once("comiciones.class.php");

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

    $opcionesDatos = array();
	$Comiciones = new COMICIONES();
	
	$opcionesDatos['pendientes'] = $Comiciones->pendientes();
	$opcionesDatos['pagadas'] = $Comiciones->pagadas();
	$opcionesDatos['inactivas'] = $Comiciones->inactivas(); 
	
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

