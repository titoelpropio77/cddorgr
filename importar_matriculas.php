<?php

require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

$urb_id = "2";
$sql = "select * from temp_matriculas_lujan";

$matrs = FUNCIONES::lista_bd_sql($sql);

foreach ($matrs as $obj) {
    $s = $obj->cantidad;
    $parts = explode('al', $s);
//    echo "$obj->matricula";
//    echo "<pre>";
//    print_r($parts);
//    echo "</pre>";

    $man_nro = trim($obj->manzano);
    $matricula = trim($obj->matricula);
    $superficie = floatval(trim($obj->superficie));
    $lote_ini = trim($parts[0]);
    $lote_fin = (isset($parts[1])) ? trim($parts[1]) : $lote_ini;

    $sql_upd = "update manzano set man_matricula='$matricula',
    man_superficie='$superficie',man_lote_ini='$lote_ini',man_lote_fin='$lote_fin'
    where man_urb_id='$urb_id' and man_nro='$man_nro';";
    
    echo "<p>$sql_upd</p>";
}
?>