<?php

ini_set('display_errors', 'On');
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
define('_urbs', '2,3,4,6');
_db::open();

function resetear_venta($ven_id = 0) {

    $venta = FUNCIONES::objeto_bd_sql("select * from venta 
        where ven_multinivel='si' 
        and ven_numero like 'NET%'
        and ven_id='$ven_id'");

    if ($venta === NULL) {
        echo "Esta venta no es multinivel...<br/>";
        return FALSE;
    }

    $conec = new ADO();
    $sql_up = "update venta set  ven_costo_pag=0, ven_estado='Pendiente' where ven_id=$ven_id";
    $conec->ejecutar($sql_up);

    $sql_up = "delete from interno_deuda_pago where idp_vpag_id in (select vpag_id from venta_pago where vpag_ven_id=$ven_id);";
    $conec->ejecutar($sql_up);

    $sql_up = "delete from venta_pago where vpag_ven_id=$ven_id";
    $conec->ejecutar($sql_up);

    $sql_up = "delete from interno_deuda where ind_tabla_id=$ven_id and ind_num_correlativo=0";
    $conec->ejecutar($sql_up);

    $sql_up = "update interno_deuda set  ind_estado='Pendiente', ind_capital_pagado=0,ind_interes_pagado=0, ind_monto_pagado=0,ind_monto_parcial=0, ind_saldo_final=0,ind_costo_pagado=0  where ind_tabla_id=$ven_id";
    $conec->ejecutar($sql_up);

    $sql_up = "delete from venta_cobro where vcob_ven_id=$ven_id";
    $conec->ejecutar($sql_up);
}

function actualizar_saldos_netzen($ven_id = 0) {
    $conec = new ADO();
//    $sql_sel="select ven_id,ven_numero,ven_monto,ven_res_anticipo,ven_monto_efectivo, ven_monto-ven_res_anticipo as diferencia from venta where ven_numero like 'NET%' and ven_monto_efectivo!=ven_monto-ven_res_anticipo";
//    $filtro="and ven_id='8612'";
    $filtro = "";
    if ($ven_id > 0) {
        $filtro = " and ven_id=$ven_id";
    }

//    $sql_sel = "select * from venta 
//        where ven_numero like 'NET%' 
//        and ven_monto_efectivo!=ven_monto-ven_res_anticipo $filtro";

    $sql_sel = "select * from venta 
        where ven_numero like 'NET%' 
        $filtro";

    $ventas = FUNCIONES::lista_bd_sql($sql_sel);

    foreach ($ventas as $venta) {
        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_tabla_id='$venta->ven_id' and ind_estado in ('Pendiente','Pagado') order by ind_id asc");
        $saldo_financiar = $venta->ven_monto - $venta->ven_res_anticipo;
        $_saldo_financiar = $saldo_financiar;
        $saldo = $saldo_financiar;
        foreach ($cuotas as $cuota) {
            $saldo = $saldo - $cuota->ind_capital;
            if ($cuota->ind_estado == 'Pagado') {
                $saldo_final = $saldo;
            } else {
                $saldo_final = 0;
            }

            $sql_up = "update interno_deuda set ind_saldo='$saldo', ind_saldo_final='$saldo_final' where ind_id='$cuota->ind_id'";
            $conec->ejecutar($sql_up, false, false);
        }

        $vpagos = FUNCIONES::lista_bd_sql("select * from venta_pago where vpag_ven_id='$venta->ven_id' and vpag_estado='Activo' order by vpag_id asc");
        $saldo_financiar = $venta->ven_monto - $venta->ven_res_anticipo;
        $_saldo_financiar = $saldo_financiar;
        $saldo = $saldo_financiar;
        foreach ($vpagos as $pago) {
            $saldo = $saldo - $pago->vpag_capital;


            $sql_up = "update venta_pago set vpag_saldo_final='$saldo' where vpag_id='$pago->vpag_id'";
            $conec->ejecutar($sql_up, false, false);
        }



        $sql_up_venta = "update venta set ven_monto_efectivo='$_saldo_financiar' where ven_id='$venta->ven_id'";
        $conec->ejecutar($sql_up_venta, false, false);
    }
    echo count($ventas) . ";<br>";
}

$sql_ventas = "select distinct(ven_id),ven_numero from
venta inner join interno_deuda on (ven_id=ind_tabla_id and ind_tabla='venta')
where ven_multinivel='si'
and ind_num_correlativo=0
and ind_estado in ('Pendiente','Pagado')
limit 0,20";

//$sql_ventas = "select ven_id from venta where ven_id in (8743,8758,8778,8936)";

$ventas = FUNCIONES::lista_bd_sql($sql_ventas);

$s_ventas = array();
foreach ($ventas as $venta) {
    resetear_venta($venta->ven_id);
    actualizar_saldos_netzen($venta->ven_id);
    $s_ventas[] = $venta->ven_id;
}
echo implode(',', $s_ventas);
?>