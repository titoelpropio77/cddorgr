<?php
ini_set('display_errors', 'On');
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
require_once("clases/mytime_int.php");
require_once('config/constantes.php');
require_once("clases/Ticket.php");
require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');
include_once 'clases/oferta.class.php';
include_once 'modulos/bonos/bonos.class.php';

//$arr_ventas = array(9392, 9394, 9406, 9407, 9413);
$sql = "set group_concat_max_len=1000000;";
FUNCIONES::bd_query($sql);
$sql = "select group_concat(vof_ven_id)as campo from venta_oferta where vof_estado='Pendiente'";
$s_ventas = FUNCIONES::atributo_bd_sql($sql);
$arr_ventas = explode(',', $s_ventas);
$oferta = 1;

foreach ($arr_ventas as $llave) {
//    $sql_upd = "update venta set ven_of_id='$oferta' where ven_id='$llave'";
//    FUNCIONES::bd_query($sql_upd);
    echo $llave."<br/>";
    $data_oferta = array('venta' => $llave, 'oferta' => $oferta);
//    OFERTA::guardar_venta_oferta($data_oferta);
    OFERTA::actualizar_venta_oferta($data_oferta);
}


//$arr_ventas = array(1,2,3,4,8,6,4,2,1,3,9);
//$arr2 = array_unique($arr_ventas);
//
//foreach ($arr2 as $llave) {
//    echo "$llave<br>";
//}

//$s_json = '{"of_id":"1","of_nombre":"CERO INICIAL","of_descripcion":"Tu primer cuota, tu primer mes.","of_plazo":"59","of_periodos_sin_bra":"2","of_periodos_con_bir":"3","of_fecha_ini":"2017-04-01","of_fecha_fin":"2017-12-31","of_usu_id":"admin","of_fecha_cre":"2017-04-01","of_usu_mod":"admin","of_fecha_mod":"2017-05-18","of_forma_ci":"cuota_mensual","of_monto_fijo":"0.00","of_porc":"0.00","of_eliminado":"No","pdos_bra":["2016-11","2016-12"],"pdos_bir":["2016-10","2016-11","2016-12"]}';
//$obj = json_decode($s_json);
//
//print_r($obj);

//$pdo_id = $_GET[pdo];
$sql = "set group_concat_max_len=1000000;";
FUNCIONES::bd_query($sql);
$sql = "select convert(group_concat(pdo_id)using utf8)as campo from comision_periodo 
    where pdo_estado='Cerrado' order by pdo_id asc;";
$s_ventas = FUNCIONES::atributo_bd_sql($sql);
$arr_pdos = explode(',', $s_ventas);

$obj_bonos = new BONOS();
foreach ($arr_pdos as $pdo_id) {
    
    $obj_bonos->revisar_ventas_con_oferta($pdo_id);
}


?>