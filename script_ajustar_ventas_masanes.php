<?php
session_start();
ini_set('display_errors', 'On');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/aux_reporte_saldos.class.php');
require_once('config/constantes.php');

$fecha_venta = "2018-07-31";
$hora = date('H:i:s');
$ges_id = 18;

$sql_ventas = "select * from venta where ven_int_id='15193' and ven_estado!='Anulado'";

$ventas = FUNCIONES::lista_bd_sql($sql_ventas);
$pdo_id = FUNCIONES::atributo_bd_sql("select ifnull(pdo_id,0) as campo from con_periodo 
    where pdo_eliminado='No' and pdo_fecha_inicio<='$fecha_venta' 
        and pdo_fecha_fin>='$fecha_venta' and pdo_ges_id='$ges_id'");


foreach ($ventas as $venta) {
    
    $sql_upd_ven = "update venta set ven_fecha='$fecha_venta' where ven_id='$venta->ven_id'";
    echo "<p>$sql_upd_ven;</p>";

    $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where 
        cmp_tabla='venta' and cmp_tabla_id='$venta->ven_id' and cmp_eliminado='No'");
    
    if ($cmp) {
//        $nro = FUNCIONES::obtener_per_tc_nro($pdo_id, $cmp->cmp_tco_id, $ges_id);
        $sql_upd_cmp = "update con_comprobante set cmp_fecha='$fecha_venta', 
            cmp_peri_id='$pdo_id', cmp_fecha_modi='$fecha_venta $hora' 
                where cmp_id='$cmp->cmp_id'";
        echo "<p>$sql_upd_cmp;</p>";
    }
}