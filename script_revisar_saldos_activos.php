<?php
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
//require_once 'clases/registrar_comprobantes.class.php';
require_once('clases/script_reporte.class.php');

//saldos(2);
//saldos(3);
saldos(4);

//monto_pagado();

function saldos($urb_id) {
    
//    $ventas = FUNCIONES::objetos_bd_sql("select * from venta where ven_urb_id='$urb_id' limit 50");
    $ventas = FUNCIONES::objetos_bd_sql("select * from venta where ven_urb_id='$urb_id' and ven_fecha <='2014-12-31' and ven_urb_id in (2,3,4) and ven_fecha_cre<='2015-11-26 23:59:59'");
    $result = array();
    $total=new stdClass();
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();

        
        $tpagado = pagado_venta($venta->ven_id);//FUNCIONES::total_pagado_venta($venta->ven_id);
        $pagado1 = pagado_venta($venta->ven_id,'2000-01-01','2014-12-31');
//        $pagado2 = pagado_venta($venta->ven_id,'2015-01-01','2015-11-25');
        $lote = FUNCIONES::objeto_bd_sql("select * from lote,manzano,uv,urbanizacion where lot_id='$venta->ven_lot_id' and lot_man_id=man_id and lot_uv_id=uv_id and man_urb_id=urb_id");
        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_id='$venta->ven_int_id'");
        if($venta->ven_fecha<='2014-12-31'){
            $saldo_2014=$venta->ven_monto_efectivo-$pagado1->capital-$pagado1->descuento;
        }else{
            $saldo_2014=0;
        }
        
//        $saldo_final=$venta->ven_monto_efectivo-$pagado1->capital-$pagado2->capital;
        $moneda=$venta->ven_moneda==2?'Dolares':'Bolivianos';
        $costo_pag_2014=$pagado1->costo*1;
        if($venta->ven_fecha<='2014-12-31'){
            $costo_pag_2014+=$venta->ven_costo_cub;
        }
//        $costo_pag_2015=$pagado2->costo*1;
//        if($venta->ven_fecha>='2015-01-01'){
//            $costo_pag_2015+=$venta->ven_costo_cub;
//        }
        $saldo_costo_2014=$venta->ven_costo-$costo_pag_2014;
//        $saldo_costo_final=$venta->ven_costo-$costo_pag_2014-$costo_pag_2015;

        $fecha=  FUNCIONES::get_fecha_latina($venta->ven_fecha);
        $result[] = array(
            $venta->ven_id,
            $fecha,
            $lote->urb_nombre,
            $lote->uv_nombre,
            $lote->man_nro,
            $lote->lot_nro,
            $venta->ven_superficie,
            "$interno->int_nombre $interno->int_apellido",
            $moneda,
            $venta->ven_monto,
            $venta->ven_costo,
            $venta->ven_monto_efectivo,
            $pagado1->descuento,
            $pagado1->capital,
            $saldo_2014,
            $costo_pag_2014,
            $saldo_costo_2014,
            $tpagado->descuento,
            $tpagado->incremento,
//            $pagado2->descuento,
//            $pagado2->capital, 
//            $pagado2->interes,
//            $pagado2->form,
//            $pagado2->mora,
//            $pagado2->monto,
//            $tpagado->descuento,
//            $tpagado->capital,
//            $saldo_final,
//            $costo_pag_2015,
//            $saldo_costo_final,
        );
        $mon=$venta->ven_moneda;
        $total->{"monto_$mon"}+=$venta->ven_monto;
        $total->{"costo_$mon"}+=$venta->ven_costo;
        $total->{"saldo_efectivo_$mon"}+=$venta->ven_monto_efectivo;
        $total->{"descuento_2014_$mon"}+=$pagado1->descuento;
        $total->{"capital_pag_2014_$mon"}+=$pagado1->capital;
        $total->{"saldo_2014_$mon"}+=$saldo_2014;
        $total->{"costo_pag_2014_$mon"}+=$costo_pag_2014;
        $total->{"saldo_costo_2014_$mon"}+=$saldo_costo_2014;
        $total->{"descuento_$mon"}+=$tpagado->descuento;
        $total->{"incremento_$mon"}+=$tpagado->incremento;
//        $total->{"descuento_2015_$mon"}+=$pagado2->descuento;
//        $total->{"capital_pag_2015_$mon"}+=$pagado2->capital;
//        $total->{"interes_pag_2015_$mon"}+=$pagado2->interes;
//        $total->{"form_pag_2015_$mon"}+=$pagado2->form;
//        $total->{"mora_pag_2015_$mon"}+=$pagado2->mora;
//        $total->{"monto_pag_2015_$mon"}+=$pagado2->monto;
//        $total->{"descuento_total_$mon"}+=$tpagado->descuento;
//        $total->{"capital_total_$mon"}+=$tpagado->capital;
//        $total->{"saldo_final_$mon"}+=$saldo_final;
//        $total->{"costo_pag_2015_$mon"}+=$costo_pag_2015;
//        $total->{"saldo_costo_final_$mon"}+=$saldo_costo_final;

        $ventas->siguiente();
    }
    $head = array(
        'VENTA ID',
        'FECHA',
        'PROYECTO',
        'UV',
        'MZ',
        'LOTE',
        'SUP.',
        'CLIENTE',
        'MONEDA',
        'MONTO',
        'COSTO',
        'SALDO FINANCIAR',
        'DESC. 31/12/2014',
        'CAP. PAG. 31/12/2014',
        'SALDO 2014',
        'COSTO. PAG. 31/12/2014',
        'SALDO COSTO 2014',
        'DESC. TOTAL',
        'INC. TOTAL',
        
//        'DESC. 2015',
//        'CAP. PAG. 2015',
//        'INTERES 2015',
//        'FORM 2015',
//        'MORA 2015',
//        'TOTAL PAGADO 2015',
//        'DESCUENTO TOTAL',
//        'CAPITAL PAGADO',
//        'SALDO CAP. FINAL',        
//        'COSTO PAG. 2015',
//        'SALDO COSTO. FINAL',
    );
    $data = array(
        'type' => 'success',
        'titulo' => 'SALDO VENTAS ACTIVAS',
        'info' => array(),
        'modulo' => '',
        'head' => $head,
        'result' => $result,
        'foot' => array(
                    array(
                        array('texto'=>'Total Bs.','attr'=>'colspan="9"'),
                        $total->monto_1,
                        $total->costo_1,
                        $total->saldo_efectivo_1,
                        $total->descuento_2014_1,
                        $total->capital_pag_2014_1,
                        $total->saldo_2014_1,
                        $total->costo_pag_2014_1,
                        $total->saldo_costo_2014_1,
                        $total->descuento_1,
                        $total->incremento_1,
//                        $total->descuento_2015_1,
//                        $total->capital_pag_2015_1,
//                        $total->interes_pag_2015_1,
//                        $total->form_pag_2015_1,
//                        $total->mora_pag_2015_1,
//                        $total->monto_pag_2015_1,
//                        $total->descuento_total_1,
//                        $total->capital_total_1,
//                        $total->saldo_final_1,
//                        $total->costo_pag_2015_1,
//                        $total->saldo_costo_final_1
                    ),
                    array(
                        array('texto'=>'Total $us','attr'=>'colspan="9"'),
                        $total->monto_2,
                        $total->costo_2,
                        $total->saldo_efectivo_2,
                        $total->descuento_2014_2,
                        $total->capital_pag_2014_2,
                        $total->saldo_2014_2,
                        $total->costo_pag_2014_2,
                        $total->saldo_costo_2014_2,
                        $total->descuento_2,
                        $total->incremento_2,
//                        $total->descuento_2015_2,
//                        $total->capital_pag_2015_2,
//                        $total->interes_pag_2015_2,
//                        $total->form_pag_2015_2,
//                        $total->mora_pag_2015_2,
//                        $total->monto_pag_2015_2,
//                        $total->descuento_total_2,
//                        $total->capital_total_2,
//                        $total->saldo_final_2,
//                        $total->costo_pag_2015_2,
//                        $total->saldo_costo_final_2
                    )
        ),
        'show_header' => false,
    );

//    SCRIPT_REPORTE::html($data);
    SCRIPT_REPORTE::excel($data);
}

function monto_pagado(){
    $vpagos=  FUNCIONES::objetos_bd_sql("select * from venta_pago where vpag_fecha_pago>='2015-01-01' and vpag_monto>0 order by vpag_ven_id asc, vpag_id asc");
    $result = array();
    $total=new stdClass();
    for ($i = 0; $i < $vpagos->get_num_registros(); $i++) {
        $pago=$vpagos->get_objeto();
        $fecha=  FUNCIONES::get_fecha_latina($pago->vpag_fecha_pago);
        $str_moneda=$pago->vpag_moneda==2?'Dolares':'Bolivianos';
        $result[]=array(
            $pago->vpag_id,
            $pago->vpag_ven_id,
            $fecha,
            $pago->vpag_usu_import,
            $str_moneda,
            $pago->vpag_costo,
            $pago->vpag_capital_desc,
            $pago->vpag_capital,
            $pago->vpag_interes,
            $pago->vpag_form,
            $pago->vpag_mora,
            $pago->vpag_monto,
        );
        $mon=$pago->vpag_moneda;
        $total->{"costo_$mon"}+=$pago->vpag_costo;
        $total->{"capital_desc_$mon"}+=$pago->vpag_capital_desc;
        $total->{"capital_$mon"}+=$pago->vpag_capital;
        $total->{"interes_$mon"}+=$pago->vpag_interes;
        $total->{"form_$mon"}+=$pago->vpag_form;
        $total->{"mora_$mon"}+=$pago->vpag_mora;
        $total->{"monto_$mon"}+=$pago->vpag_monto;
        $vpagos->siguiente();
    }
    $head = array(
        'ID',
        'VENTA ID',
        'FECHA',
        'USUARIO',
        'MONEDA',
        'COSTO',
        'DESCUENTO',
        'CAPITAL',
        'INTERES',
        'FORM',
        'MORA',
        'MONTO',
    );
    $data = array(
        'type' => 'success',
        'titulo' => 'PAGOS 2015',
        'info' => array(),
        'modulo' => '',
        'head' => $head,
        'result' => $result,
        'foot' => array(
                    array(
                        array('texto'=>'Total Bs.','attr'=>'colspan="5"'),
                        $total->costo_1,
                        $total->capital_desc_1,
                        $total->capital_1,
                        $total->interes_1,
                        $total->form_1,
                        $total->mora_1,
                        $total->monto_1
                    ),
                    array(
                        array('texto'=>'Total $us','attr'=>'colspan="5"'),
                        $total->costo_2,
                        $total->capital_desc_2,
                        $total->capital_2,
                        $total->interes_2,
                        $total->form_2,
                        $total->mora_2,
                        $total->monto_2
                    )
        ),
        'show_header' => false,
    );

//    SCRIPT_REPORTE::html($data);
    SCRIPT_REPORTE::excel($data);
}

function pagado_venta($ven_id,$fecha_ini='',$fecha_fin='') {
    $and_filtro='';
    if($fecha_ini){
        $and_filtro .= "and vpag_fecha_pago>='$fecha_ini'";
    }
    if($fecha_fin){
        $and_filtro .= "and vpag_fecha_pago<='$fecha_fin'";
    }
    
    $sql_pag = "select sum(vpag_interes) as interes, sum(vpag_form ) as form, sum(vpag_mora) as mora, sum(vpag_capital) as capital, sum(vpag_monto) as monto, 
                    sum(vpag_capital_desc) as descuento, sum(vpag_capital_inc) as incremento, sum(vpag_costo ) as costo
                    from venta_pago 
                    where vpag_estado='Activo' and vpag_ven_id=$ven_id $and_filtro
                    ";
    $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
    return $pagado;
}
