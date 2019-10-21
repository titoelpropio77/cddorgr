<?php
ini_set('display_errors','On'); 

@session_start();
require_once('clases/session.class.php');
require_once('clases/usuario.class.php');
//url amigable 
require_once('config/constantes.php');
require_once('config/zona_horaria.php');
require_once('clases/mensaje.class.php');
require_once('clases/Encryption.php');
require_once('clases/conversiones.class.php');

require_once('clases/sistema.class.php');
require_once('clases/Funciones.php'); 
require_once('clases/URL.class.php');
require_once('clases/view.class.php');

$archivo = "controlador/" . $_GET['uri1'] . ".php";
//print_r($_SESSION);
if ($_SESSION['afil_id'] != '') {

	$data =  array();
	$resp = SISTEMA::send_post(_servicio_url . "gestor.php?mod=login&tarea=rango&usu_id=".$_SESSION['afil_id']."&vdo_id=".$_SESSION['vdo_id'], $data);
		
	$jsonResp = json_decode($resp);
	$jsonResp = $jsonResp[0]->respuesta;
	if ($jsonResp->accion == "correcto") {
		$_SESSION['afil_rango'] = $jsonResp->datos->r;
		$_SESSION['notificaciones'] = $jsonResp->datos->notificaciones;
	}
}

if ($_GET['uri1'] != 'miscomisiones') {
    if (file_exists($archivo)) {
        include_once($archivo);
    } else {
        echo "No Existe el modulo";
    //include_once("view/404.php");
    }
} else {
    echo "No Existe el modulo";
//include_once("view/404.php");
}



?>