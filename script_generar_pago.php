<?php
ini_set('display_errors', 'On');

require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
require_once('config/constantes.php');

function generar_pago($datos){
    
    $datos = (object) $datos;
    $ven_id = $datos->ven_id;
    $capital_pagado = $datos->capital_pagado; 
    $monto = $datos->monto; 
    $fecha_pago = $datos->fecha_pago;
    $fecha_valor = $datos->fecha_valor;
    $saldo_inicial = $datos->saldo_inicial;
    $saldo_final = $saldo_inicial - $capital_pagado;
    $capital_ids = $datos->capital_ids;
    $capital_montos = $datos->capital_montos;
    $costo_ids = $datos->costo_ids;
    $ind_id = $datos->ind_id;
    $user = $datos->usuario;
    
    $recibo = $datos->recibo;
    $fecha_cre = date('Y-m-d H:i:s');
    
    $cu = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_id='$ind_id'");
    
    $sql_ins_plan = "INSERT INTO `venta_pago` (
    `vpag_ven_id`,`vpag_codigo`,`vpag_fecha_pago`, 
    `vpag_fecha_valor`,`vpag_int_id`,`vpag_moneda`,`vpag_saldo_inicial`, 
    `vpag_dias_interes`,`vpag_interes`,`vpag_capital`,`vpag_monto`,`vpag_saldo_final`,
    `vpag_estado`,`vpag_interes_ids`,`vpag_interes_montos`,`vpag_capital_ids`,
    `vpag_capital_montos`,`vpag_fecha_cre`,`vpag_usu_cre`,`vpag_cob_usu`,`vpag_cob_codigo`,
    `vpag_cob_aut`,`vpag_costo`,`vpag_costo_ids`,`vpag_costo_montos`,
    `vpag_recibo`,`vpag_importado`,`vpag_suc_id`) VALUES";
    
    $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$ven_id'");
    
    $saldo_costo = $venta->ven_costo-$venta->ven_cuota_inicial-$venta->ven_costo_cub;
    $saldo_venta = $venta->ven_monto_efectivo - $venta->ven_cuota_inicial;
    
    $costo_pag = ($saldo_costo / $saldo_venta) * $capital_pagado;
    
    if (($venta->ven_costo_cub + $venta->ven_costo_pag + $costo_pag) > $venta->ven_costo) {
        $costo_pag = $venta->ven_costo - ($venta->ven_costo_cub + $venta->ven_costo_pag);
    }
    
    $costo_montos = "$costo_pag";
    
    $pag_codigo = FUNCIONES::fecha_codigo();
    $sql_ins = $sql_ins_plan."('$venta->ven_id','$pag_codigo','$fecha_pago',
    '$fecha_valor','$venta->ven_int_id','$venta->ven_moneda','$saldo_inicial',
    '0','0','$capital_pagado','$monto','$saldo_final',
    'Activo','','','$capital_ids',
    '$capital_montos','$fecha_cre','$user','$user','',
    '','$costo_pag','$costo_ids','$costo_montos','$recibo','1','$venta->ven_suc_id')";
    
    echo "<p>$sql_ins;</p>";
    
    $sql_ins_idp = "insert into interno_deuda_pago(
        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,
        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
        idp_cob_usu,idp_cob_codigo,idp_cob_aut
    )values(
        '$cu->ind_id','$cu->ind_num_correlativo','0','$recibo','$pag_codigo','capital',
        '$fecha_pago','$capital_pagado','Activo','$fecha_cre','$user',
        '$user','',''
    )";

    echo "<p>$sql_ins_idp;</p>";
    
    $sql_ins_idp = "insert into interno_deuda_pago(
        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,
        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
        idp_cob_usu,idp_cob_codigo,idp_cob_aut
    )values(
        '$cu->ind_id','$cu->ind_num_correlativo','0','$recibo','$pag_codigo','costo',
        '$fecha_pago','$costo_pag','Activo','$fecha_cre','$user',
        '$user','',''
    )";
    
    echo "<p>$sql_ins_idp;</p>";
    
    $sql_ind = "update interno_deuda set 
    ind_estado='Pagado',
    ind_fecha_pago='$fecha_pago',
    ind_capital_pagado=ind_capital_pagado+$capital_pagado,
    ind_monto_pagado=ind_interes_pagado+ind_capital_pagado,
    ind_costo_pagado=ind_costo_pagado+$costo_pag,    
    ind_saldo_final='$saldo_final'
    where ind_id = '$cu->ind_id'";
    
    echo "<p>$sql_ind;</p>";
    
    $sql_up_ven_costo = "update venta 
    set     
    ven_costo_pag=ven_costo_pag+$costo_pag,     
    ven_estado='Pagado'
    where ven_id=$venta->ven_id";
    
    echo "<p>$sql_up_ven_costo;</p>";
}

$datos = array(
    'ven_id' => 3197,
    'capital_pagado' => 5360,
    'monto' => 5360,
    'fecha_pago' => '2015-09-04',
    'fecha_valor' => '2015-10-31',
    'saldo_inicial' => 5360,
    'capital_ids' => '269891',
    'ind_id' => '269891',
    'costo_ids' => '269891',
    'capital_montos' => '5360',    
    'recibo' => '86913',    
);

generar_pago($datos);