<?php
//ini_set('display_errors','On');

header('Access-Control-Allow-Origin: *');
header('Expires: ' . gmdate('r', 0));
date_default_timezone_set("America/La_Paz"); 

require_once('../../config/database.conf.php');
require_once('view.class.php');
require_once('view/Mobile_Detect.php');

mysql_connect(_SERVIDOR_BASE_DE_DATOS,_USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or die("Could not connect: " . mysql_error());
mysql_select_db(_BASE_DE_DATOS);

$obj = new stdClass();
$obj->code = 1;

$view = new VIEWPLANO();
if(isset($_GET['a']))
{
	$view = new VIEWPLANO();
	switch ($_GET['a'])
	{
		case "getMapView":
			$obj->data = $view->plano();
		break;
		case "searchMarkers": 
			$obj->data = $view->buscar();
		break;
		case "loteInfo": 
			$obj->data = $view->loteInfo();
		break;
		default:
		$obj->data = $view->iniciar();
	}
} else {
	$obj->data = $view->iniciar();
}

$obj = json_encode($obj);

echo $obj;

?>
