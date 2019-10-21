<?php

function logs($tipo_accion, $sql, $usuario){
	$sqlNew = str_replace("'","/",$sql);
	$consulta="insert into ad_logs (
										log_fecha,
										log_hora,
										log_tipo_accion,
										log_accion,
										log_usu_id,
										log_ip,
										log_pc
									)
									values
									('".date("Y-m-d")."',
										'".date("H:i:s")."',
										'".$tipo_accion."',
										'".$sqlNew."',
										'".$usuario."',
										'".$_SERVER['REMOTE_ADDR']."',
										'".gethostbyaddr($_SERVER['REMOTE_ADDR'])
									."')";
				
	$result = mysql_query($consulta);
}

function cortar_string($string, $largo) {
	$marca = "<!--corte-->";
	if (strlen($string) > $largo) {

		$string = wordwrap($string, $largo, $marca);
		$string = explode($marca, $string);
		$string = $string[0];
	}
	return $string;
}

function html_convertir_json($log_html){
	$json_html = htmlentities(stripslashes(utf8_encode($log_html)), ENT_QUOTES);
	return $json_html;
	/*
	$aux_html = array('html' => $log_html);
	$json_html = json_encode(array_map(utf8_encode, $aux_html));
	return $json_html;
	*/
}
function fecha_parser($log_fecha){
	$fecha = explode("/", $log_fecha);
	$auxFecha = $fecha[2]."-".$fecha[1]."-".$fecha[0];
	return $auxFecha;
}

function hora_parser($hora){
	$auxHora = explode(" ", $hora);
	$auxHora2 = explode(":", $auxHora[0]);
	
	$hora = "00:00:00";
	if($auxHora[1]=="PM"){
		$horas = (int)$auxHora2[0]+12;
		$hora = $horas.":".$auxHora2[1].":00";
	} else {
		$hora = $auxHora2[0].":".$auxHora2[1].":00";
	}
	return $hora;
}

function clean_input($string)
{
	$string = str_replace(" ", "-", $string);
	$string = preg_replace('/[^A-Za-z0-9_\-]/', '', $string);
	return preg_replace('/-+/', '-', $string);
}

//Verificar el interno
function _cliente_tempid($int_id)
{
	$intId;
	$sql = "SELECT int_id FROM interno WHERE int_app_tempid='".$int_id."'; ";
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	if ($num > 0) {
		$objeto = mysql_fetch_object($result);
		$intId = $objeto->int_id;
	} else {
		$intId = $int_id;
	}
	return $intId;
}

//Verificar el seguimiento
function _seguimiento_tempid($seg_id)
{
	$segId;
	$sql = "SELECT seg_id FROM seguimiento WHERE seg_app_tempid='".$seg_id."'; ";
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	if ($num > 0) {
		$objeto = mysql_fetch_object($result);
		$segId = $objeto->seg_id;
	} else {
		$segId = $seg_id;
	}
	return $segId;
}

function _proforma_tempid($pro_id)
{
	$segId;
	$sql = "SELECT pro_id FROM proforma WHERE pro_app_tempid='".$pro_id."'; ";
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	if ($num > 0) {
		$objeto = mysql_fetch_object($result);
		$segId = $objeto->pro_id;
	} else {
		$segId = $pro_id;
	}
	return $segId;
}

function _reserva_tempid($res_id)
{
	$segId;
	$sql = "SELECT res_id FROM reserva_terreno WHERE res_app_tempid='".$res_id."'; ";
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	if ($num > 0) {
		$objeto = mysql_fetch_object($result);
		$segId = $objeto->res_id;
	} else {
		$segId = $res_id;
	}
	return $segId;
}

?>