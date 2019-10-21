<?php
ini_set('display_errors', 'On');
require_once('clases/mytime_int.php');
require_once('clases/busqueda.class.php');
require_once 'clases/formulario.class.php';
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
require_once('config/constantes.php');
include_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/registrar_comprobantes.class.php';

$arr_sent_sql = array();
$sql_del = "delete from con_comprobante_detalle where cde_cmp_id='$det_dif->cde_cmp_id' 
and cde_secuencia='$det_dif->cde_secuencia' 
and cde_mon_id='$det_dif->cde_mon_id' 
and cde_cue_id='$det_dif->cde_cue_id'";
						
$sql_del = str_replace(PHP_EOL, "", $sql_del);
$arr_sent_sql[] = $sql_del;

FUNCIONES::print_pre($arr_sent_sql);
?>