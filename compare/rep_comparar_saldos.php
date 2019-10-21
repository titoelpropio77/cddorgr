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
require_once('clases/aux_reporte_saldos.class.php');
require_once('config/constantes.php');

$hoy = date('Y-m-d');
$_SESSION[id] = 'admin';

function calcular_saldo_venta_contable($venta, $fecha_limite = null) {
    $gestion_minima = FUNCIONES::objeto_bd_sql("select * from con_gestion where ges_id='14'");
    $codigo_saldo_venta = ($venta->ven_moneda == 1) ? $venta->urb_cta_por_cobrar_bs : $venta->urb_cta_por_cobrar_usd;
    $sql_saldo_venta = "select sum(cde_valor)as campo from con_comprobante 
        inner join con_comprobante_detalle on(cmp_id=cde_cmp_id)
        inner join con_cuenta on (cde_cue_id=cue_id)
        inner join con_cuenta_ca on(cde_can_id=can_id)
        where cue_codigo='$codigo_saldo_venta' 
        and can_codigo='$venta->ven_can_codigo'
        and cmp_fecha<='$fecha_limite' 
        and cde_mon_id='$venta->ven_moneda'
        and cmp_eliminado='No'";

    $saldo_venta = FUNCIONES::atributo_bd_sql($sql_saldo_venta) * 1;
    return $saldo_venta;
}

function calcular_saldo_venta_comercial($venta, $fecha_limite = null) {

    global $hoy;
    if ($fecha_limite < $hoy) {

        include_once 'clases/aux_reporte_saldos.class.php';
        $arr_ventas = array($venta->ven_id);
        $token_user = $_SESSION[id] . "_" . date('dmY_His');
        AUX_REPORTE::cargar_historia_ventas($arr_ventas, $fecha_limite, $token_user, $_SESSION[id]);

        $join_aux = " inner join aux_reporte_saldos on(ven_id=ars_ven_id and ars_token_user='$token_user')";
        $campos_aux = " if(ars_tipo='cambio_lote','Cambiado',
                                    if(ars_tipo='reversion','Retenido',
                                        if(ars_tipo='activacion','Pendiente',
                                            if(ars_tipo='fusion','Fusionado',if(ars_tipo='sin_operacion','Sin_Operacion','Pendiente'))
                                        )
                                    )
                                )as estado_calculado,";
    }

    $sql = "SELECT 
                    ven_id,ven_fecha,ven_superficie,ven_monto,ven_moneda,ven_lot_id,
                    ven_cuota_inicial,ven_plazo,ven_tipo,ven_estado,
                    ven_cuota,ven_comision,
                    ven_vdo_id,ven_urb_id,ven_valor,ven_metro,ven_res_anticipo,
                    ven_val_interes,ven_superficie,ven_decuento,ven_lot_ids,ven_monto_intercambio,
                    ven_monto_efectivo,
                    ven_ufecha_pago,ven_ufecha_valor,ven_cuota_pag,ven_capital_pag,
                    ven_capital_desc,ven_capital_inc,ven_usaldo,
                    ven_ubicacion,
                    $campos_aux
                    ifnull(sum(vpag_capital_inc),0) as incremento,
                    ifnull(sum(vpag_capital_desc),0) as descuento,
                    ifnull(sum(vpag_interes),0) as interes_pagado, 
                    ifnull(sum(vpag_capital),0) as capital_pagado
                FROM 
                    venta
                    left join venta_pago on (ven_id=vpag_ven_id and vpag_estado='Activo' and vpag_fecha_pago<='$fecha_limite')                    
                    $join_aux                                        
                    where ven_id='$venta->ven_id'
                    group by ven_id";

    $obj = FUNCIONES::objeto_bd_sql($sql);

    $arr_para_sumar_saldos = array('Pendiente', 'Sin_Operacion', 'Pagado');

    if ($fecha_limite >= $hoy) {
        $estado_calculado = $obj->ven_estado;
    } else if ($obj->estado_calculado == 'Sin_Operacion') {
        if ($obj->ven_tipo == 'Contado') {
            $estado_calculado = 'Pagado';
        } else {
            $estado_calculado = 'Pendiente';
        }
    } else {
        $estado_calculado = $obj->estado_calculado;
    }

    $capital_cuotas = $obj->capital_pagado;
    $interes_cuotas = $obj->interes_pagado;
    $cuota_ini = $obj->ven_monto_intercambio + $obj->ven_res_anticipo;
    $capital_pagado = $cuota_ini + $capital_cuotas;
    $monto_venta = $obj->ven_monto;

    $saldo_financiar = $obj->ven_monto_efectivo;

    if ($capital_cuotas < $saldo_financiar) {
        $saldo_capital = ($saldo_financiar + $obj->incremento) - ($capital_cuotas + $obj->descuento);
    } else {
        if ($obj->ven_tipo == 'Contado' &&
                ($cuota_ini >= $saldo_financiar) &&
                ($cuota_ini >= $obj->capital_pagado)) {

            $capital_pagado = $cuota_ini;
            $capital_cuotas = 0;
        }

        $saldo_capital = 0;
    }


    $saldo_capital = (in_array($estado_calculado, $arr_para_sumar_saldos)) ? $saldo_capital : 0;
    $saldo_capital = round($saldo_capital, 2);

    
    $resp = new stdClass();
    $resp->saldo = $saldo_capital;
    $resp->estado_calculado = $estado_calculado;
    return $resp;
}

function comparar_migracion_cdd($filtro_venta = '') {
    $fecha_limite = "2015-01-02";
    
    $sql_ventas = "select * from venta 
        inner join rep_saldos_venta_cdd rsvc on(ven_id=rsvc.venta)
        inner join urbanizacion on (ven_urb_id=urb_id)
        inner join con_moneda on(ven_moneda=mon_id)
        where 1 $filtro_venta";
    $ventas = FUNCIONES::objetos_bd_sql($sql_ventas);

    $head = (object) array(
        'VENTA','PROYECTO','MONEDA','ESTADO ACTUAL',
        'ESTADO CALCULADO','SALDO COMERCIAL','SALDO CONTABLE','DIF. SALDOS');
    $arr_ventas_compares = array();
    $arr_ventas_compares[] = $head;
    $nom_arch = 'rep_comparar_saldos_migracion_cdd.csv';
    $fp = fopen($nom_arch, 'w');
    fclose($fp);
    $cont = 0;
    $limit_ins = 100;
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {        
        $venta = $ventas->get_objeto();
        $saldo_venta_contable = calcular_saldo_venta_contable($venta, $fecha_limite);
        $resp_venta_comercial = calcular_saldo_venta_comercial($venta, $fecha_limite);
        $saldo_venta_comercial = $resp_venta_comercial->saldo;
        $estado_calculado = $resp_venta_comercial->estado_calculado;
        
        $reg = new stdClass();
        $reg->ven_id = $venta->ven_id;
        $reg->proyecto = $venta->urb_nombre;
        $reg->moneda = $venta->mon_Simbolo;
        $reg->estado_actual = $venta->ven_estado;        
        $reg->estado_calculado = $estado_calculado;        
        $reg->saldo_comercial = $saldo_venta_comercial;
        $reg->saldo_contable = $saldo_venta_contable;
        
        $dif = abs($saldo_venta_comercial - $saldo_venta_contable);
        $reg->obs_diferencia = ($dif >= 1) ? "ERROR": "OK";
        $reg->diferencia = ($dif >= 1) ? TRUE: FALSE;        
                
        $arr_ventas_compares[] = $reg;
        $cont++;
        if ($cont == $limit_ins) {
            escribir_en_archivo($nom_arch, $arr_ventas_compares);
            $cont = 0;
            $arr_ventas_compares = array();            
        }
                
        $ventas->siguiente();
    }
    
    if ($cont > 0 && $cont < $limit_ins) {
        escribir_en_archivo($nom_arch, $arr_ventas_compares);
        $cont = 0;
        $arr_ventas_compares = array();            
    }
    
//    return $arr_ventas_compares;
}

function comparar_migracion_demazu($filtro_venta = '') {
    $fecha_limite = "2016-03-01";
    
    $sql_ventas = "select * from venta 
        inner join rep_saldos_venta_demazu rsvc on(ven_id=rsvc.venta)
        inner join urbanizacion on (ven_urb_id=urb_id)
        inner join con_moneda on(ven_moneda=mon_id)
        where 1 $filtro_venta";
    $ventas = FUNCIONES::objetos_bd_sql($sql_ventas);

    $head = (object) array(
        'VENTA','PROYECTO','MONEDA','ESTADO ACTUAL',
        'ESTADO CALCULADO','SALDO COMERCIAL','SALDO CONTABLE','DIF. SALDOS');
    $arr_ventas_compares = array();
    $arr_ventas_compares[] = $head;
    $nom_arch = 'rep_comparar_saldos_migracion_demazu.csv';
    $fp = fopen($nom_arch, 'w');
    fclose($fp);
    $cont = 0;
    $limit_ins = 100;
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {        
        $venta = $ventas->get_objeto();
        $saldo_venta_contable = calcular_saldo_venta_contable($venta, $fecha_limite);
        $resp_venta_comercial = calcular_saldo_venta_comercial($venta, $fecha_limite);
        $saldo_venta_comercial = $resp_venta_comercial->saldo;
        $estado_calculado = $resp_venta_comercial->estado_calculado;
        
        $reg = new stdClass();
        $reg->ven_id = $venta->ven_id;
        $reg->proyecto = $venta->urb_nombre;
        $reg->moneda = $venta->mon_Simbolo;
        $reg->estado_actual = $venta->ven_estado;        
        $reg->estado_calculado = $estado_calculado;        
        $reg->saldo_comercial = $saldo_venta_comercial;
        $reg->saldo_contable = $saldo_venta_contable;
        
        $dif = abs($saldo_venta_comercial - $saldo_venta_contable);
        $reg->obs_diferencia = ($dif >= 1) ? "ERROR": "OK";
        $reg->diferencia = ($dif >= 1) ? TRUE: FALSE;
                
        $arr_ventas_compares[] = $reg;
        $cont++;
        if ($cont == $limit_ins) {
            escribir_en_archivo($nom_arch, $arr_ventas_compares);
            $cont = 0;
            $arr_ventas_compares = array();            
        }
                
        $ventas->siguiente();
    }
    
    if ($cont > 0 && $cont < $limit_ins) {
        escribir_en_archivo($nom_arch, $arr_ventas_compares);
        $cont = 0;
        $arr_ventas_compares = array();            
    }
    
//    return $arr_ventas_compares;
}

function comparar_migracion_vexsa($filtro_venta = '') {
    $fecha_limite = "2017-04-22";
    
    $sql_ventas = "select * from venta 
        inner join rep_saldos_venta_vexsa rsvc on(ven_id=rsvc.venta)
        inner join urbanizacion on (ven_urb_id=urb_id)
        inner join con_moneda on(ven_moneda=mon_id)
        where 1 $filtro_venta";
    $ventas = FUNCIONES::objetos_bd_sql($sql_ventas);

    $head = (object) array(
        'VENTA','PROYECTO','MONEDA','ESTADO ACTUAL',
        'ESTADO CALCULADO','SALDO COMERCIAL','SALDO CONTABLE','DIF. SALDOS');
    $arr_ventas_compares = array();
    $arr_ventas_compares[] = $head;
    $nom_arch = 'rep_comparar_saldos_migracion_vexsa.csv';
    $fp = fopen($nom_arch, 'w');
    fclose($fp);
    $cont = 0;
    $limit_ins = 100;
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {        
        $venta = $ventas->get_objeto();
        $saldo_venta_contable = calcular_saldo_venta_contable($venta, $fecha_limite);
        $resp_venta_comercial = calcular_saldo_venta_comercial($venta, $fecha_limite);
        $saldo_venta_comercial = $resp_venta_comercial->saldo;
        $estado_calculado = $resp_venta_comercial->estado_calculado;
        
        $reg = new stdClass();
        $reg->ven_id = $venta->ven_id;
        $reg->proyecto = $venta->urb_nombre;
        $reg->moneda = $venta->mon_Simbolo;
        $reg->estado_actual = $venta->ven_estado;        
        $reg->estado_calculado = $estado_calculado;        
        $reg->saldo_comercial = $saldo_venta_comercial;
        $reg->saldo_contable = $saldo_venta_contable;
        
        $dif = abs($saldo_venta_comercial - $saldo_venta_contable);
        $reg->obs_diferencia = ($dif >= 1) ? "ERROR": "OK";
        $reg->diferencia = ($dif >= 1) ? TRUE: FALSE;        
                
        $arr_ventas_compares[] = $reg;
        $cont++;
        if ($cont == $limit_ins) {
            escribir_en_archivo($nom_arch, $arr_ventas_compares);
            $cont = 0;
            $arr_ventas_compares = array();            
        }
                
        $ventas->siguiente();
    }
    
    if ($cont > 0 && $cont < $limit_ins) {
        escribir_en_archivo($nom_arch, $arr_ventas_compares);
        $cont = 0;
        $arr_ventas_compares = array();            
    }
    
//    return $arr_ventas_compares;
}

function obtener_linea($arr_datos) {
    $s = '"' . implode('";"', $arr_datos) . '"' . "\n";
    return $s;
}

function escribir_en_archivo($nom_archivo, $arr_contenido) {

    $limit_ins = 200;
    $cont = 0;

    $fp = fopen($nom_archivo, 'a');

    $buff = '';
    for ($i = 0; $i < count($arr_contenido); $i++) {
        $ele = (array) $arr_contenido[$i];        
        
        $s_csv = obtener_linea($ele);
        
        $buff .= $s_csv;
        $cont++;

        if ($cont == $limit_ins) {
            $cont = 0;
            fputs($fp, $buff);
            $buff = '';
        }
    }

    if ($cont > 0 && $cont < $limit_ins) {
        $cont = 0;
        fputs($fp, $buff);
        $buff = '';
    }

    fclose($fp);
}

function mostrar_tabla_comparacion($arr_ventas) {
    ?>
<style>
    .obs_dif{
        background-color: red;
    }
</style>
<table id="tabla" border="1">
    <thead>
        <tr>
            <th>#</th>
            <th>VENTA</th>
            <th>PROYECTO</th>
            <th>MONEDA</th>
            <th>ESTADO ACTUAL</th>
            <th>EST. CALC. AL CIERRE</th>
            <th>SALDO VENTA COMERCIAL</th>
            <th>SALDO VENTA CONTABLE</th>
        </tr>
    </thead>
    <tbody>                
        <?php
            $index = 0;
            foreach ($arr_ventas as $reg) {
                $index++;
                $class_dif = ($reg->diferencia) ? "obs_dif":"";
                ?>
        <tr class="<?php echo $class_dif;?>">
            <td><?php echo $index;?></td>
            <td><?php echo $reg->ven_id;?></td>
            <td><?php echo $reg->proyecto;?></td>
            <td><?php echo $reg->moneda;?></td>
            <td><?php echo $reg->estado_actual;?></td>
            <td><?php echo $reg->estado_calculado;?></td>
            <td><?php echo $reg->saldo_comercial;?></td>
            <td><?php echo $reg->saldo_contable;?></td>
        </tr>
                <?php
            }
        ?>
    </tbody>
</table>
    <?php
}

//$ven_id = $_GET[venta];
//$fecha_limite = "2015-01-02";
//$venta = FUNCIONES::objeto_bd_sql("select * from venta 
//    inner join urbanizacion on(ven_urb_id=urb_id) where ven_id='$ven_id'");
//
//$saldo_venta_contable = calcular_saldo_venta_contable($venta, $fecha_limite);
//$saldo_venta_comercial = calcular_saldo_venta_comercial($venta, $fecha_limite);
//
//echo "<p>saldo_venta_contable:$saldo_venta_contable - saldo_venta_comercial:$saldo_venta_comercial</p>";

// $filtro = " and ven_id='4652'";
//$arr_ventas1 = comparar_migracion_cdd($filtro);
$arr_ventas2 = comparar_migracion_demazu($filtro);
$arr_ventas3 = comparar_migracion_vexsa($filtro);
//mostrar_tabla_comparacion($arr_ventas);