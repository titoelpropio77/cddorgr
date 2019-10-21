 <?php
 
include_once("usuario.class.php");

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) {
        case "cambiar_contrasenia":
            cambiar_contrasenia($mensaje, $accion, $datos, $opciones);
            break;
    }
}

function cambiar_contrasenia(&$mensaje, &$accion, &$datos, &$opciones) {
    $datosForm = preg_replace('/\\\\\"/',"\"", $_POST['datosForm']);
	$jsonDatos = json_decode($datosForm);
		
	$usuario = new USUARIO();
    if (is_numeric($usuario->usuDatos[2])) {
		$resUsuario = $usuario->cambiar_contrasenia($jsonDatos); 
		//$datos = "";
		$mensaje = $resUsuario->mensaje;
		$accion = $resUsuario->accion;
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

