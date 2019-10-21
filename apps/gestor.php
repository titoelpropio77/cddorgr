<?php
	session_start();
	ini_set('display_errors','On'); 
	header('Access-Control-Allow-Origin: *');
	header('Expires: ' . gmdate('r', 0));
	date_default_timezone_set("America/La_Paz");
	
	require_once('config/constantes.php'); 
	require_once('config/database.conf.php'); 
	
	require_once('clases/Email.class.php');
	require_once('clases/funciones.php');
	require_once('clases/Encryption.php');
	require_once('clases/conversiones.class.php'); 
	require_once('clases/usuarios.class.php'); 
	mysql_connect(_SERVIDOR_BASE_DE_DATOS,_USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or die("Could not connect: " . mysql_error());
	mysql_select_db(_BASE_DE_DATOS); 

	$archivo = "./modulos/".$_GET["mod"].".php";
	if (file_exists($archivo))
	{
		// El modulo existe
		require_once($archivo);
	} else {
		// No existe modulo
		$json = '{
			"respuesta":
			{
				"mensaje": "Este modulo no existe",
				"accion": "error",
				"datos":[]
			}
		}';
		echo 'iqCallback(['.$json.'])';
	}
?>