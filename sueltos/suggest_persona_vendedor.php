<?php

require_once('../config/database.conf.php');
mysql_connect(_SERVIDOR_BASE_DE_DATOS, _USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or die("Could not connect: " . mysql_error());
mysql_select_db(_BASE_DE_DATOS);

$input = $_REQUEST['input'];
$len = strlen($input);

$sql = "select vdo_id as id,
        CONCAT(vdo_codigo,'(',int_nombre, ' ', int_apellido,')') as nombre, 
        int_usu_id as usuario from interno
	inner join vendedor on (vdo_int_id=int_id)
        inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
        where (CONCAT(int_nombre, ' ', int_apellido) like '%$input%' 
        or vdo_codigo like '%$input%') 
        and vdo_estado='Habilitado'
        and vgru_nombre='AFILIADOS'";

$result = mysql_query($sql);
$num = mysql_num_rows($result);

$aResults = array();
$aResults[] = array("id" => ('0'), "value" => htmlspecialchars(utf8_encode('Raiz')), "info" => htmlspecialchars(utf8_encode('Raiz')));

for ($j = 0; $j < $num; $j++) {
    $objeto = mysql_fetch_object($result);

    $aResults[] = array("id" => ($objeto->id), "value" => htmlspecialchars(utf8_encode($objeto->nombre . ' (' . $objeto->usuario . ')')), "info" => htmlspecialchars(utf8_encode($objeto->nombre)));
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Pragma: no-cache"); // HTTP/1.0



if (isset($_REQUEST['json'])) {
    header("Content-Type: application/json");

    echo "{\"results\": [";
    $arr = array();
    for ($i = 0; $i < count($aResults); $i++) {
        $arr[] = "{\"id\": \"" . $aResults[$i]['id'] . "\", \"value\": \"" . $aResults[$i]['value'] . "\", \"info\": \"\"}";
    }
    echo implode(", ", $arr);
    echo "]}";
} else {
    header("Content-Type: text/xml");

    echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?><results>";
    for ($i = 0; $i < count($aResults); $i++) {
        echo "<rs id=\"" . $aResults[$i]['id'] . "\" info=\"" . $aResults[$i]['info'] . "\">" . $aResults[$i]['value'] . "</rs>";
    }
    echo "</results>";
}

function nombre_persona($id_interno) {
    $sql2 = "select int_nombre,int_apellido from interno where int_id=$id_interno";

    $result2 = mysql_query($sql2);

    $objeto2 = mysql_fetch_object($result2);

    return $objeto2->int_nombre . ' ' . $objeto2->int_apellido;
}

function obtener_id_interno_tbl_usuario($usu_id) {

    $sql = "SELECT usu_per_id FROM ad_usuario WHERE usu_id='$usu_id'";

    $result = mysql_query($sql);

    $num = mysql_num_rows($result);

    $objeto = mysql_fetch_object($result);

    return $objeto->usu_per_id;
}

?>