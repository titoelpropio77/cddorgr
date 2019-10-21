<?php
session_start();
ini_set('display_errors', 'On');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

$f_ven_id = "";
if (($_GET[venta] * 1) > 0) {
    $f_ven_id = " and ven_id='$_GET[venta]'";
}

/* * *************FILTROS Y CONSTANTES****************************************** */
$_SESSION[id] = 'admin';
$hoy = date('Y-m-d');
$fecha_limite = "2017-04-22";
$arr_para_sumar_saldos = array('Pendiente', 'Sin_Operacion', 'Pagado');
/* * *************************************************************************** */

FUNCIONES::bd_query("set group_concat_max_len=100000000;");
$sql_ventas = "SELECT 
     ifnull(group_concat(distinct(ven_id)),'')as ventas
 FROM 
     venta
     left join venta_pago on (ven_id=vpag_ven_id and vpag_estado='Activo' and vpag_fecha_pago<='$fecha_limite')
     inner join interno on (ven_int_id=int_id)
     inner join urbanizacion on (ven_urb_id=urb_id)
     left join lote on(ven_lot_id=lot_id)
     left join manzano on(lot_man_id=man_id)
     left join uv on(lot_uv_id=uv_id)
     where 1
	  and ven_numero like 'NET-%'
	  and ven_estado != 'Anulado' $f_ven_id";

//echo $sql_ventas;
//exit();

$obj_ventas = FUNCIONES::objeto_bd_sql($sql_ventas);

if ($obj_ventas->ventas == '') {
    return false;
}

include_once 'clases/aux_reporte_saldos.class.php';
$arr_ventas_ids = explode(',', $obj_ventas->ventas);
$token_user = $_SESSION[id] . "_" . date('dmY_His');
AUX_REPORTE::cargar_historia_ventas($arr_ventas_ids, $fecha_limite, $token_user, $_SESSION[id]);

$join_aux = " inner join aux_reporte_saldos on(ven_id=ars_ven_id and ars_token_user='$token_user')";
$campos_aux = " if(ars_tipo='cambio_lote','Cambiado',
                    if(ars_tipo='reversion','Retenido',
                        if(ars_tipo='activacion','Pendiente',
                            if(ars_tipo='fusion','Fusionado',if(ars_tipo='sin_operacion','Sin_Operacion','Pendiente'))
                        )
                    )
                )as estado_calculado,";


$sql_ventas_vexsa = "SELECT 
     venta.*,urbanizacion.*,interno.*,lot_nro,uv_nombre,man_nro,
     $campos_aux
     ifnull(sum(vpag_capital_inc),0) as incremento,ifnull(sum(vpag_capital_desc),0) as descuento,
     ifnull(sum(vpag_interes),0) as interes_pagado, ifnull(sum(vpag_capital),0) as capital_pagado, 
     if(max(vpag_fecha_pago) = '0000-00-00','No existe',ifnull(max(vpag_fecha_pago),'No existe')) as ufecha_pago,
     if(max(vpag_fecha_valor) = '0000-00-00','No existe',ifnull(max(vpag_fecha_valor),'No existe')) as ufecha_valor	  
 FROM 
     venta
     left join venta_pago on (ven_id=vpag_ven_id and vpag_estado='Activo' and vpag_fecha_pago<='$fecha_limite')
     inner join interno on (ven_int_id=int_id)
     inner join urbanizacion on (ven_urb_id=urb_id)
     left join lote on(ven_lot_id=lot_id)
     left join manzano on(lot_man_id=man_id)
     left join uv on(lot_uv_id=uv_id)
     $join_aux
     where 1
	  and ven_numero like 'NET-%'
	  and ven_estado != 'Anulado' $f_ven_id
     group by ven_id";

$ventas = funciones::objetos_bd_sql($sql_ventas_vexsa);

$arr_ventas = array();
$s_rastro = '0';
for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
    $s_rastro .= "=>1";
    $venta = $ventas->get_objeto();
    $monto_venta = ($venta->ven_monto_efectivo + $venta->ven_res_anticipo);
    $cuota_inicial = $venta->ven_res_anticipo;

    $costo_cub = ($venta->ven_res_anticipo + $venta->ven_monto_intercambio);

    if ($venta->ven_moneda == '2') {
        $costo_calculado = $venta->ven_superficie * $venta->urb_val_costo;
        $costo = $venta->ven_superficie * $venta->urb_val_costo;
    } else {
        $costo_calculado = $venta->ven_superficie * $venta->urb_val_costo * 6.96;
        $costo = $venta->ven_superficie * $venta->urb_val_costo * 6.96;
    }

    $saldo_venta = $venta->ven_monto_efectivo;
    $costo_cubierto = 0;

    if ($costo_cub >= $costo) {
        $s_rastro .= "=>2";
        $costo_cub = $costo;
        $costo_cubierto = $costo;
        $saldo_costo = 0;
        $costo_pagado = 0;
        $costo_pagado_descuento = 0;
    } else {
        $s_rastro .= "=>3";
        $saldo_costo_por_pagar = $costo - $costo_cub;
        $costo_pagado = $venta->capital_pagado * ($saldo_costo_por_pagar / $saldo_venta);
        $costo_pagado_descuento = $venta->descuento * ($saldo_costo_por_pagar / $saldo_venta);
        $costo_cubierto = $costo_pagado + $costo_cub + $costo_pagado_descuento;
        $saldo_costo = $costo - $costo_cubierto;

        if ($costo_cubierto >= $costo) {
            $saldo_costo = 0;
            $costo_cubierto = $costo;
            $costo_pagado = $costo - $costo_cub;
        }
        $s_3 = "<p>s_3 => saldo_costo_por_pagar:$saldo_costo_por_pagar - costo:$costo - costo_cub:$costo_cub - costo_pagado_cm:$costo_pagado - saldo_venta:$saldo_venta - capital_pagado_cm:$venta->capital_pagado</p>";
    }

//    $capital_pagado = ($venta->ven_tipo == 'Contado') 
//            ? ($venta->ven_monto_efectivo + $venta->ven_res_anticipo) 
//            : ($venta->ven_res_anticipo + $venta->capital_pagado);

    $capital_cuotas = $venta->capital_pagado;
    $capital_pagado = $cuota_inicial + $capital_cuotas;    
        
    if ($capital_pagado < $monto_venta) {
        $s_rastro .= "=>4";                
        
        $saldo = ($monto_venta + $venta->incremento) - ($capital_pagado + $venta->descuento);
        $s_4 = "<p>s_4 => capital_pagado:$capital_pagado - saldo:$saldo</p>";
    } else {
        
        if ($venta->ven_tipo == 'Contado' && 
                ($cuota_inicial >= $monto_venta) && 
                ($cuota_inicial >= $venta->capital_pagado)) {
            $capital_pagado = $cuota_inicial;
            $capital_cuotas = 0;
        }
        
        $s_rastro .= "=>5";
        $saldo = 0;
        $s_5 = "<p>s_5 => capital_pagado:$capital_pagado - saldo:$saldo</p>";
    }

    $costo_m2 = ($venta->ven_costo > 0) ? $venta->ven_costo / $venta->ven_superficie : 999;
    $costo_m2_actual = $venta->urb_val_costo;
    
    if ($fecha_limite >= $hoy) {
        $estado_calculado = $venta->ven_estado;
    } else if ($venta->estado_calculado == 'Sin_Operacion') {
        if ($venta->ven_tipo == 'Contado') {
            $estado_calculado = 'Pagado';
        } else {
            $estado_calculado = 'Pendiente';
        }
    } else {
        $estado_calculado = $venta->estado_calculado;
    }
    
    $saldo = (in_array($estado_calculado, $arr_para_sumar_saldos)) ? $saldo : 0;
    $saldo_costo = (in_array($estado_calculado, $arr_para_sumar_saldos)) ? $saldo_costo : 0;

    $reg = array(
        'ven_id' => $venta->ven_id,
        'proyecto' => $venta->urb_nombre,
        'uv' => $venta->uv_nombre,
        'manzano' => $venta->man_nro,
        'lote' => $venta->lot_nro,
        'monto_venta' => $monto_venta,
        'cuota_inicial' => $cuota_inicial,
        'capital_cuotas' => $capital_cuotas,
        'capital_pagado' => $capital_pagado,
        'saldo_venta' => $saldo,
        'saldo_financiar' => $venta->ven_monto_efectivo,
        'costo' => $costo,
        'costo_calculado' => $costo_calculado,
        'costo_ci' => $costo_cub,
        'costo_cm' => $costo_pagado,
        'costo_desc' => $costo_pagado_descuento,
        'costo_cubierto' => $costo_cubierto,
        'saldo_costo' => $saldo_costo,
        'costo_m2' => $costo_m2,
        'costo_m2_actual' => $costo_m2_actual,
        'moneda' => $venta->ven_moneda,
        'incremento' => $venta->incremento,
        'descuento' => $venta->descuento,
        'estado_actual' => $venta->ven_estado,
        'estado_calculado' => $estado_calculado,
    );

    $arr_ventas[] = $reg;

    $ventas->siguiente();
}

if ($f_ven_id != '') {
    echo "<p>$s_rastro</p>";
    echo $s_3;
    echo $s_4;
    echo $s_5;
}

if (isset($_GET[excel])) {
    $filename = "reporte.xls";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=" . $filename);
} else {
    ?>
    <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="js/jquery.thfloat-0.7.2.min.js"></script>
    <?php
}
?>
<table id="tabla" border="1">
    <thead>
        <tr>
            <th>#</th>
            <th>VENTA</th>
            <th>PROYECTO</th>
<!--            <th>A</th>
            <th>B</th>            -->
            <th>MONEDA</th>
            <th>ESTADO ACTUAL</th>
            <th>EST. CALC. AL CIERRE</th>
            <!--<th>MONTO VENTA</th>-->
            <th>SALDO A FINANCIAR</th>
            <th>CUOTA INICIAL</th>
            <th>CAPITAL CUOTAS</th>
            <th>CAPITAL PAGADO</th>
            <th>CAPITAL INCREMENTO</th>
            <th>CAPITAL DESCUENTO</th>
            <th>SALDO VENTA</th>
            <th>COSTO</th>
            <!--<th>COSTO CALCULADO</th>-->
            <th>COSTO CI</th>
            <th>COSTO CM</th>
            <th>COSTO POR DESCUENTO</th>
            <th>COSTO CUBIERTO</th>
            <th>SALDO COSTO</th>
<!--            <th>COSTO m2</th>
            <th>COSTO m2 actual</th>-->
        </tr>
    </thead>
    <tbody>
        <?php
        $cont = 0;
        foreach ($arr_ventas as $reg) {
            $reg = (object) $reg;
            $cont++;
            ?>
            <tr>
                <td><?php echo $cont; ?></td>                                
                <td><?php echo $reg->ven_id; ?></td>                                
                <td><?php echo $reg->proyecto; ?></td>                                
                <!--<td><?php // echo "a"; ?></td>-->                                
                <!--<td><?php // echo "b"; ?></td>-->                                                
                <td><?php echo ($reg->moneda == '1') ? "BS." : "USD."; ?></td>
                <td><?php echo $reg->estado_actual; ?></td>
                <td><?php echo $reg->estado_calculado; ?></td>
                <!--<td><?php // echo round($reg->monto_venta, 2); ?></td>-->                                
                <td><?php echo round($reg->saldo_financiar, 2); ?></td>
                <td><?php echo round($reg->cuota_inicial, 2); ?></td>
                <td><?php echo round($reg->capital_cuotas, 2); ?></td>
                <td><?php echo round($reg->capital_pagado, 2); ?></td>
                <td><?php echo round($reg->incremento, 2); ?></td>
                <td><?php echo round($reg->descuento, 2); ?></td>
                <td><?php echo round($reg->saldo_venta, 2); ?></td>
                <td><?php echo round($reg->costo, 2); ?></td>
                <!--<td><?php // echo round($reg->costo_calculado, 2); ?></td>-->
                <td><?php echo round($reg->costo_ci, 2); ?></td>
                <td><?php echo round($reg->costo_cm, 2); ?></td>
                <td><?php echo round($reg->costo_desc, 2); ?></td>
                <td><?php echo round($reg->costo_cubierto, 2); ?></td>
                <td><?php echo round($reg->saldo_costo, 2); ?></td>
                <!--<td><?php // echo round($reg->costo_m2, 2); ?></td>-->
                <!--<td><?php // echo round($reg->costo_m2_actual, 2); ?></td>-->
            </tr>
            <?php
        }
        ?>        
    </tbody>
</table>
<?php
if (!isset($_GET[excel])) {
    ?>
    <script>
        $("#tabla").thfloat();
    </script>
    <?php
}
?>