<?php

ini_set('display_errors', 'On');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('config/constantes.php');
require_once('clases/log.class.php');
require_once('clases/mlm.class.php');

//$sql = "update vendedor set vdo_nivel=0 where vdo_vgru_id=14";
//FUNCIONES::bd_query($sql);
//MLM::establecer_niveles($_GET[padre], $_GET[nivel]);

//public static function obtener_red($vdo_id, &$nivel, $self = false, $profundidad = 0, $this_not = '', $tabla = 'vendedor')

$marcus = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='86'");
$bauer = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='85'");
$nivel = 1000;
$red_marcus = MLM::obtener_red($marcus->vdo_id, $nivel, FALSE, $marcus->vdo_nivel + $nivel);
$red_bauer = MLM::obtener_red($bauer->vdo_id, $nivel, FALSE, $bauer->vdo_nivel + $nivel);

$s_m = implode(',', $red_marcus);
$s_b = implode(',', $red_bauer);

echo "$s_m<br/>";
echo "$s_b<br/>";

//echo "<pre>Red Marcus";
//print_r($red_marcus);
//echo "</pre>";
//
//echo "<br/><br/>";
//
//echo "<pre>Red Bauer";
//print_r($red_bauer);
//echo "</pre>";

return;
////
//FUNCIONES::bd_query("truncate table vendedor_tmp");
//$sql_clon = "insert into vendedor_tmp select vdo.* from vendedor vdo
//        inner join vendedor_grupo vg on(vdo.vdo_vgru_id=vg.vgru_id)
//        where vg.vgru_nombre='AFILIADOS'";
//FUNCIONES::bd_query($sql_clon);
//
//$hoy = date("Y-m-d");
//$vdo_id = 0;
//MLM::compresion_dinamica($hoy, $vdo_id);
//$hoy = date("Y-m-d");
//echo $s_periodo = substr($hoy, 0, 7);

//$internos = FUNCIONES::lista_bd_sql("select * from vendedor where vdo_vgru_id=14 limit 5");
//$s_json = FUNCIONES::json_list($internos);
//echo $s_json;

?>