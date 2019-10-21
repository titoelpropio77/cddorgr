<?php

require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'excel/reader.php';
require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');
require_once("clases/log.class.php");
require_once("clases/sistema.class.php");
require_once("clases/formulario.class.php");
require_once("clases/busqueda.class.php");
require_once("clases/mytime_int.php");
require_once("modulos/afiliado/afiliado.class.php");
require_once("modulos/afiliado/afiliado_historial.class.php");
ini_set('display_errors', 'On');

//echo "shit";
////exit();
//
//$data_form = array(
//    'gestion' => 17,
//    'pdo_id' => 37,
//    'agrupado_por' => 'sin_agrupar'
//);
//var_dump($data_form);
////exit();
//$resp = SISTEMA::send_post('http://cdd.sistema.com.bo/gestor.php?mod=afiliado&tarea=VER HISTORIAL&id=86',$data_form);
//echo $resp;


$afiliados = FUNCIONES::lista_bd_sql("select * from vendedor 
    inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
    where vgru_nombre='AFILIADOS'
    order by vdo_id asc");

$af_hist = new AFILIADO_HISTORIAL();

foreach ($afiliados as $afil) {
    $_GET['id'] = $afil->vdo_id;
    $_POST['gestion'] = 17;
    $_POST['pdo_id'] = 37;
    $_POST['agrupado_por'] = 'sin_agrupar';

    $af_hist->generar_historial($_POST, $_GET, FALSE);
}
?>