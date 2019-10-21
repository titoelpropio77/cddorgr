<?php

ini_set('display_errors', 'On');

require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once('config/zona_horaria.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/formulario.class.php';

$sql_dump = "select * from tab_aux_dump where estado='Pendiente' order by id asc limit 0,1";
$dump = FUNCIONES::objeto_bd_sql($sql_dump);

if ($dump == NULL) {
    echo "<p>No existen archivos para procesar...</p>";
    exit();
}

$nom_arch = $dump->nombre_archivo_upd;

if (!file_exists('imp_exp/'.$nom_arch)) {
    echo "<p>No existe el archivo $nom_arch</p>";
    exit();
}

echo "<p>Si existe el archivo $nom_arch.</p>";
//exit();

if ($dump->respuesta != '') {
    $params = json_decode($dump->respuesta);
    $start = $params->start * 1;
    $foffset = $params->foffset * 1;
    $totalqueries = $params->totalqueries * 1;
    $porc = $params->elem24 * 1;
    $porc_alcanzado = $params->elem22 * 1;
} else {
    $start = 0;
    $foffset = 0;
    $totalqueries = 0;
    $porc = 0;
    $porc_alcanzado = 0;
}

$arr_porcs_alcanzados = array();
while ($i <= 150000 && $porc_alcanzado < 100) {
        
    $i++;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/cdd/imp_exp/importar.php?start=$start&fn=$nom_arch&foffset=$foffset&totalqueries=$totalqueries&delimiter=;&ajaxrequest=0");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $xxx = curl_exec($ch);
//    echo $xxx;

    try {
        $xml_resp = new SimpleXMLElement($xxx);
        $start = $xml_resp->linenumber * 1;
        $foffset = $xml_resp->foffset * 1;
        $totalqueries = $xml_resp->totalqueries * 1;
        $porc = $xml_resp->elem24 * 1;
        $porc_alcanzado = $xml_resp->elem22 * 1;
        
        $sql_upd_porc_alc = "update tab_aux_dump set porc_alcanzado='$porc_alcanzado' where id='$dump->id'";
        FUNCIONES::bd_query($sql_upd_porc_alc);
        
        $arr_porcs_alcanzados[] = $porc_alcanzado;
    } catch (Exception $exc) {
//        $i = 100;
        $exc->getMessage();
        curl_close($ch);
        break;
    }

    sleep(1);
    curl_close($ch);
    
}

echo "<p>porc:$porc - porc_alc:$porc_alcanzado - contador:$i</p>";

echo "<pre>";
print_r($arr_porcs_alcanzados);
echo "</pre>";

$resp = new stdClass();
$resp->start = $start;
$resp->foffset = $foffset;
$resp->totalqueries = $totalqueries;
$resp->elem22 = $porc_alcanzado;
$resp->elem24 = $porc;
$s_json = json_encode($resp);

$ad_estado = "";
if ($porc_alcanzado == 100) {
    $ad_estado = ",estado='Atendido'";
}
$sql_upd_dump = "update tab_aux_dump set respuesta='$s_json' $ad_estado "
        . "where id='$dump->id'";
FUNCIONES::bd_query($sql_upd_dump);
?>