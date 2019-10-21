<?php
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/aux_reporte_saldos.class.php');
require_once('config/constantes.php');

$sql_set_max_group_concat_len = "set group_concat_max_len=100000000;";
FUNCIONES::bd_query($sql_set_max_group_concat_len);
$sql_ventas = "select convert(group_concat(ven_id)using utf8)as campo from venta where ven_estado!='Anulado' order by ven_id asc";
$s_ven_ids = FUNCIONES::atributo_bd_sql($sql_ventas);

$arr_ventas = explode(',', $s_ven_ids);

//echo "<pre>";
//print_r($arr_ventas);
//echo "</pre>";
//return false;

AUX_REPORTE::cargar_historia_ventas($arr_ventas);

