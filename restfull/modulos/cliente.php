<?php

include_once("cliente.class.php");

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
        case "clientes": {
                obtener_clientes($mensaje, $accion, $datos, $opciones, $_GET[criterio]);
                break;
            }
    }
}

function obtener_clientes(&$mensaje, &$accion, &$datos, &$opciones, $criterio) {
    $Cliente = new CLIENTE();
    $datos = $Cliente->obtener_clientes($criterio);
    echo $datos;
    exit;
    $opciones = "";
    $mensaje = "";
    $accion = "correcto";
}

function agregar(&$mensaje, &$accion, &$datos, &$opciones) {

     //echo $_POST['datosForm']; 
	// exit;
    //$datosForm = preg_replace('/\\\\\"/', "\"", $_POST['datosForm']);            
	//$datosForm = preg_replace('/\\\\\"/', "\"", $datosForm);            
	// $datosForm = $_POST['datosForm'];
	//echo $datosForm;
	// exit;

	 $datosForm = stripslashes($_POST["datosForm"]);
	 //$datosForm = $datosForm."";
	//echo $datosForm;
	
   //$jsonDatos = json_decode('{"int_nombre":"xcvxcvxc","int_apellido":"xcv","int_ci":"xcv","int_fecha_nacimiento":"","int_email":"info@cvcc.com","int_telefono":"cvb","int_celular":"cvb","int_direccion":"cvb"}'); 
   $jsonDatos = json_decode($datosForm); 
   
   //var_dump($jsonDatos);
   
   //exit;

    $Cliente = new CLIENTE();
    if (is_numeric($Cliente->usuDatos[2])) {

        $resAgregar = $Cliente->agregar($jsonDatos);
        if ($resAgregar->estado) {

            $mensaje = $resAgregar->mensaje;
            $accion = "correcto";
			//$datos = $datosForm;
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
    $datosForm = preg_replace('/\\\\\"/', "\"", $_POST['datosForm']);
    $jsonDatos = json_decode($datosForm);

    $Cliente = new CLIENTE();
    if (is_numeric($Cliente->usuDatos[2])) {

        $resModificar = $Cliente->modificar($jsonDatos);
        if ($resModificar->estado) {

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
if (isset($_POST)) {
    echo '[' . $json . ']';
} else {
    echo 'iqCallback([' . $json . '])';
}
?>

