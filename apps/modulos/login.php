<?php

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) { 
        case "login":
            login($mensaje, $accion, $datos, $opciones);
            break;
    }
}

function login(&$mensaje, &$accion, &$datos, &$opciones) {
	$usu_id = trim($_GET['usu_id']); 
	$usu_password = MD5(trim($_GET['usu_password']));
    if ((isset($usu_id)) && (isset($usu_password))) 
	{
		$sql="SELECT usu_id,usu_gru_id,usu_per_id,int_nombre,int_apellido,usu_password FROM ad_usuario INNER JOIN interno ON(int_id=usu_per_id) WHERE usu_id='".$usu_id."' AND usu_password='".$usu_password."' AND usu_estado='1'";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		if ($num > 0) 
		{
			//usu_per_id
			$Encript = new Encryption();
			$objeto = mysql_fetch_object($result);
			
			$o = new stdClass; 
			$o->i = $objeto->usu_id;
			$o->g = $objeto->usu_gru_id;
			$o->p = $objeto->usu_per_id;
			//$o->c = $objeto->usu_password;
			$o->c = ""; 
			$o->pa = utf8_encode($objeto->int_nombre." ".$objeto->int_apellido."|");
			$o->t = $Encript->encode($objeto->usu_id."|".$objeto->usu_gru_id."|".$objeto->usu_per_id); 
			
			//Registrar logs 
			logs("APLICACION", "Ingreso al Sistema desde la aplicacion", $objeto->usu_id);
			
			// Correocto 
			$datos = json_encode($o);
			$accion = "correcto";  
			$mensaje = "Sesi&oacute;n iniciada correctamente";
		}
		else
		{
			$mensaje = "Por favor escriba su usuario y contrase&ntilde;a correctamente server";
			$accion = "error";
		}
    } 
	else 
	{
        $mensaje = "Por favor escriba su usuario y contrase&ntilde;a"; 
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

echo 'iqCallback([' . $json . '])';

?>