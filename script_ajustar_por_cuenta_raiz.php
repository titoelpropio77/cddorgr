<?php
ini_set('display_errors', 'On');
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once('config/zona_horaria.php');
include_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/busqueda.class.php';
require_once 'clases/formulario.class.php';
require_once 'clases/eventos.class.php';

function ajustar_por_cuenta_raiz() {
    $sql = "select cmp_id,cmp_mon_id,cmp_ges_id,ven_id,urb_id,urb_ing_diferido,urb_cta_por_cobrar_usd,urb_cta_por_cobrar_bs,count(cde_cmp_id) as cant from con_comprobante
inner join con_comprobante_detalle on(cmp_id=cde_cmp_id)
inner join venta on(cmp_tabla_id=ven_id)
inner join urbanizacion on(ven_urb_id=urb_id)
where urb_tipo='Externo'
and cmp_id in(56128,
58748,
62824,
71782,
71783,
72404,
72737,
72789,
74039,
74536,
74724,
74725,
77188,
81762,
83376,
84744,
86647,
88460,
88461,
89082,
89083,
89085,
89087,
89088,
89089,
89092,
89093,
89094,
89096,
89097,
89098,
89100,
89101,
89102,
89103,
89104,
90495,
90496,
90497,
90498,
91273,
91598,
92526,
93592,
93593,
95566,
95573,
95575,
95802,
97643,
97666,
98049,
98591,
98752,
98753,
98754,
98755,
101078,
101079,
101745,
101747,
102124,
102125,
103796,
103799,
103800,
103801,
103804,
103805,
103810,
103813,
103822,
103823,
103824,
103826,
103828,
103829,
103830,
103832,
103833,
103834,
103836,
103840,
103841,
103846,
103849,
103850,
103851,
103852,
103853,
103854,
103856,
103857,
103860,
103866,
103868,
106799,
110542,
111492,
111493,
111496,
111501,
111506,
111510,
111516,
111521,
111525,
111528,
111533,
111534,
111536,
111537,
111542,
111545,
111653,
111654,
111657,
111658,
111670,
111791,
111792,
111794,
114275,
115256,
115544,
116453,
117301,
117303,
117306,
117311,
117313,
117315,
117317,
117322,
117327,
118230,
118231,
118232,
118233,
118234,
118235,
118247,
118249,
118260,
118262,
118263,
118264,
118270,
118272,
118273,
118275,
118277,
118278,
118287,
118298,
118303,
118306,
118311,
118312,
118323,
118325,
118397,
118406,
118411,
118415,
118418,
118420,
118421,
118424,
118426,
118429,
118432,
118437,
118452,
118457,
118465,
118466,
118470,
118474,
118476,
118479,
118483,
118499,
118501,
118509,
118511,
125261,
126433,
126952,
127148,
131877,
132943,
138023,
139519,
141022,
141308,
141320,
143074,
143075,
145557,
146713,
149408,
150091,
160183,
160377,
167779,
172317,
172319,
172320,
173515,
173516,
173518,
173519,
173520,
173522,
173524,
173525,
173527,
173528,
173530,
173533,
173534,
173536,
173537,
173540,
173541,
173542,
173543,
173544,
173545,
173546,
173548,
173550,
173551,
173553,
173554,
173556,
173564,
173565,
173566,
173567,
173569,
173571,
173572,
173573,
173574,
173575,
173890,
174217,
174219,
174221,
174222,
174223,
174225,
174227,
174228,
174230,
174231,
174319,
174321,
174322,
174323,
174345,
174346,
174347,
174349,
174350,
174351,
174352,
174354) and cmp_tabla='venta_retencion' and cmp_eliminado='No'
and urb_ing_diferido is not null and urb_cta_por_cobrar_usd is not null
group by cmp_id 
having cant = 8";
    
    // echo "<p>$sql;</p>";

    $cmps = FUNCIONES::lista_bd_sql($sql);
    $arr_cues_raiz = array(5831,3654,2177,997,2);
    foreach ($cmps as $c) {
        $sql_det = "select * from con_comprobante_detalle where cde_cmp_id='$c->cmp_id'"
                . " and cde_cue_id in(5831,3654,2177,997,2) and cde_secuencia in (1,2) and cde_mon_id='2'"
                . "order by cde_secuencia";
        // echo "<p>$sql_det;</p>";
        $dets = FUNCIONES::lista_bd_sql($sql_det);
        
        foreach ($dets as $d) {
            $sql_upd = '';
            if ($d->cde_secuencia == '1') {
                $cue_ing_dif_id = FUNCIONES::get_cuenta($c->cmp_ges_id, $c->urb_ing_diferido);
                if ($cue_ing_dif_id > 0 && !in_array($cue_ing_dif_id, $arr_cues_raiz)) {
                    $sql_upd = "update con_comprobante_detalle set cde_cue_id='$cue_ing_dif_id' "
                            . "where cde_cmp_id='$c->cmp_id' and cde_secuencia='$d->cde_secuencia'";
                } else {
                    echo "<p>-- cue_ing_dif_id:$cue_ing_dif_id - urb_ing_diferido:$c->urb_ing_diferido</p>";
                }
            } else if ($d->cde_secuencia == '2') {
                $cue_cta_cobrar_id = FUNCIONES::get_cuenta($c->cmp_ges_id, $c->urb_cta_por_cobrar_usd);
                if ($cue_cta_cobrar_id > 0 && !in_array($cue_cta_cobrar_id, $arr_cues_raiz)) {
                    $sql_upd = "update con_comprobante_detalle set cde_cue_id='$cue_cta_cobrar_id' "
                            . "where cde_cmp_id='$c->cmp_id' and cde_secuencia='$d->cde_secuencia'";
                } else {
                    echo "<p>-- cue_cta_cobrar_id:$cue_cta_cobrar_id - urb_cta_por_cobrar_usd:$c->urb_cta_por_cobrar_usd</p>";
                }
            }
            
            if ($sql_upd != '') {
                echo "<p>$sql_upd;</p>";
            }
        }
    }
}

function ajustar_venta_pagos(){
    $sql = "select * from con_comprobante where cmp_id in(115113,115120,115957,120685,
    120916,121756,121758,126300,127262,127552,129957)";
//    $sql = "select * from con_comprobante where cmp_id in(121756)";
    
    $cmps = FUNCIONES::lista_bd_sql($sql);
    
    foreach ($cmps as $c) {
        
        unset($_POST[a_fpag_monto]);
        unset($_POST[a_fpag_mon_id]);
        unset($_POST[a_fpag_forma_pago]);
        unset($_POST[a_fpag_ban_nombre]);
        unset($_POST[a_fpag_ban_nro]);
        unset($_POST[a_fpag_cue_id]);
        unset($_POST[a_fpag_descripcion]);
                
        
        $sql_vpag = "select * from venta_pago inner join venta on(vpag_ven_id=ven_id)"
                . " inner join urbanizacion on(ven_urb_id=urb_id)"
                . " where vpag_id='$c->cmp_tabla_id'";
        $venta_pago = FUNCIONES::objeto_bd_sql($sql_vpag);
        
        $sql_fpags = "select * from con_pago where fpag_tabla='$c->cmp_tabla' and fpag_tabla_id='$c->cmp_tabla_id'"
                . " and fpag_estado='Activo' order by fpag_id";
        $sql_fpags = "select * from con_pago where fpag_tabla='$c->cmp_tabla' and fpag_tabla_id='$c->cmp_tabla_id'"
                . "  order by fpag_id";
        $dets = FUNCIONES::lista_bd_sql($sql_fpags);
        
        if (count($dets) == 0) {
            echo "<p style='color:red;'>-- No existen detalles de pagos(cmp_id:$c->cmp_id).</p>";
            continue;
        }
        
        $i = 0;
        
        foreach ($dets as $d) {
            $_POST[a_fpag_monto][$i] = $d->fpag_monto;
            $_POST[a_fpag_mon_id][$i] = $d->fpag_mon_id;
            $_POST[a_fpag_forma_pago][$i] = $d->fpag_forma_pago;
            $_POST[a_fpag_ban_nombre][$i] = $d->fpag_ban_nombre;
            $_POST[a_fpag_ban_nro][$i] = $d->fpag_ban_nro;
            $_POST[a_fpag_cue_id][$i] = $d->fpag_cue_id;
            $_POST[a_fpag_descripcion][$i] = $d->fpag_descripcion;
            $i++;
        }
        
        $params=array(
                'tabla'=>'venta_pago',
                'tabla_id'=>$c->cmp_tabla_id,
                'fecha'=>$c->cmp_fecha,
                'moneda'=>$c->cmp_mon_id,
                'ingreso'=>true,
                'guardar_pago'=>false,
                'une_id'=>$venta_pago->urb_une_id,
                'glosa'=>$c->cmp_glosa,'ca'=>'0','cf'=>0,'cc'=>0
            );
        $detalles = FORMULARIO::insertar_pagos($params);    
        
        $data = array(
            'moneda' => $c->cmp_mon_id,
            'ges_id' => $c->cmp_ges_id,
            'fecha' => $c->cmp_fecha,
            'glosa' => $c->cmp_glosa,
            'interno' => $c->cmp_referido,
            'tabla_id' => $c->cmp_tabla_id,
            'urb' => $venta_pago,
            'interes' => $venta_pago->vpag_interes,
            'capital' => $venta_pago->vpag_capital,
            'capital_producto' => 0,
            'costo_producto_separado' => 0,
            'form' => $venta_pago->vpag_form,
            'envio' => $venta_pago->vpag_envio,
            'mora' => $venta_pago->vpag_mora,
            'detalles' => $detalles,
            'costo' => $venta_pago->vpag_costo,
            'costo_producto' => 0,
            'prorat_lote' => 1,
            'prorat_producto' => 0,
        );
        
        if ($venta_pago->urb_tipo == 'Interno') {
            $comprobante = MODELO_COMPROBANTE::pago_cuota($data);
        } elseif ($venta_pago->urb_tipo == 'Externo') {
            $comprobante = MODELO_COMPROBANTE::pago_cuota_ext($data);
        }

        $comprobante->cmp_id = $c->cmp_id;
        $comprobante->usu_per_id = $c->cmp_usu_id;
        $comprobante->usu_id = $c->cmp_usu_cre;
        
        $conec = NULL;
        COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
        
//        FUNCIONES::print_pre($comprobante);
    }
}

function ajustar_venta_fusion(){
   $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_fusion' and "
           . "cmp_id in(57527,62009,62372,63699,72984,92366,91129,91207,93874,95544,122144,122145,158070,169187,171931)";
    
    // $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_fusion' and "
            // . "cmp_id in(62009)";
    
    $cmps = FUNCIONES::lista_bd_sql($sql);
    
    foreach ($cmps as $c) {
        
        $vneg = FUNCIONES::objeto_bd_sql("select * from venta_negocio where vneg_ven_ori='$c->cmp_tabla_id' "
                . "and vneg_estado='Activado'");
        
        if ($vneg == NULL) {
            echo "<p>No existe el objeto venta_negocio de $c->cmp_tabla_id.</p>";
            continue;
        }
        
        $par = json_decode($vneg->vneg_parametros);
        
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$vneg->vneg_ven_id'");
        $venta_ori = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$vneg->vneg_ven_ori'");
        
        $urb_ori = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta_ori->ven_urb_id'");
        $urb_des = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

        $pagado_ori = EVENTOS::total_pagado($venta_ori->ven_id);
        $tot_pagado_ori = $par->ori_tot_capital;
        $ret_ingreso = $par->ret_ingreso;
        $capital_efectivo = $par->tot_capital;
        $capital_pag = $par->tot_capital;
        
        $saldo_final = $par->ven_monto_efectivo;
        $monto_pagar = 0;
        if ($saldo_final < 0) {
            $capital_pag = $par->tot_capital + $par->ven_monto_efectivo;
            $monto_pagar = $par->ven_monto_efectivo * (-1);
            $saldo_final = 0;
        }

        $saldo_ori = $venta_ori->ven_monto_efectivo - $pagado_ori->capital - $pagado_ori->descuento + $pagado_ori->incremento;

        $monto_intercambio = $venta->ven_monto_intercambio;
        $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
        $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");
        
        $sql_ind_fus = "select * from interno_deuda where ind_tabla='venta' and ind_num_correlativo='-3' "
                . "and ind_tabla_id='$venta->ven_id' and ind_venta_id='$venta_ori->ven_id' and ind_estado='Pagado' and ind_fecha_programada='$vneg->vneg_fecha'";
        $deuda_fusion = FUNCIONES::objeto_bd_sql($sql_ind_fus);
        
        $costo_pagado = $deuda_fusion->ind_costo_pagado;

        $data = array(
            'moneda' => $venta_ori->ven_moneda,
            'moneda_des' => $venta->ven_moneda,
            'ges_id' => $c->cmp_ges_id,
            'fecha' => $vneg->vneg_fecha,
            'glosa' => $c->cmp_glosa,
            'interno' => $c->cmp_referido,
            'tabla_id' => $venta_ori->ven_id,
            'urb_ori' => $urb_ori,
            'urb_des' => $urb_des,
            'costo' => $venta_ori->ven_costo,
            'costo_pagado_ori' => $venta_ori->ven_costo_cub + $pagado_ori->costo,
            'saldo_ori' => $saldo_ori,
            'tot_pagado_ori' => $tot_pagado_ori,
            'ret_ingreso' => $ret_ingreso,
            'capital_efectivo' => $capital_pag,
            'intercambio' => $monto_intercambio,
            'inter_montos' => $amontos,
            'inter_montos_pag' => $amontos_pag,
            'costo_pagado' => $costo_pagado,
            'monto_pagar' => $monto_pagar
        );
        
        $comprobante = MODELO_COMPROBANTE::venta_fusion($data);
//        FUNCIONES::print_pre($comprobante);
        
        $comprobante->cmp_id = $c->cmp_id;
        $comprobante->usu_per_id = $c->cmp_usu_id;
        $comprobante->usu_id = $c->cmp_usu_cre;
        
        $conec = NULL;
        COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);        
    }
}

function ajustar_venta_retencion(){
   $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_retencion' and "
           . "cmp_id in(71782,71783,72404,92526,98591,116453,146713,172317,172319,172320,173515,173516,173518,173519,173520,173522,173524,173525,173527,173528,173530,173533,173534,173536,173537,173540,173541,173542,173543,173544,173545,173546,173548,173550,173551,173553,173554,173556,173564,173565,173566,173567,173569,173890,174217,174219,174221,174222,174223,174225,174227,174228,174230,174231,174319,174321,174322,174323,174345,174346,174347,174349,174350,174351,174352,174354)";
    // $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_retencion' and "
            // . "cmp_id in(89087)";
    
    $cmps = FUNCIONES::lista_bd_sql($sql);
    include_once 'modulos/venta/venta.class.php';
    $obj_ven = new VENTA();    
    
    foreach ($cmps as $c) {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$c->cmp_tabla_id'");
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        $pagado = $obj_ven->total_pagado($venta->ven_id);
        $saldo = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;
        $tot_pagado = $venta->ven_res_anticipo + $venta->ven_venta_pagado + $pagado->capital;

        $monto_intercambio = $venta->ven_monto_intercambio;

        $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
        $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");
        
        $data = array(
            'moneda' => $venta->ven_moneda,
            'ges_id' => $c->cmp_ges_id,
            'fecha' => $c->cmp_fecha,
            'glosa' => $c->cmp_glosa,
            'interno' => $c->cmp_referido,
            'tabla_id' => $c->cmp_tabla_id,
            'tarea_id' => $c->cmp_tarea_id,
            'urb' => $urb,
            'costo' => $venta->ven_costo,
            'costo_pagado' => $venta->ven_costo_cub + $pagado->costo,
            'saldo_efectivo' => $saldo,
            'total_pagado' => $tot_pagado,
            'intercambio' => $monto_intercambio,
            'inter_montos' => $amontos,
            'inter_montos_pag' => $amontos_pag,
        );
        
        $comprobante = MODELO_COMPROBANTE::venta_retencion($data);
        $comprobante->cmp_id = $c->cmp_id;
        $comprobante->usu_per_id = $c->cmp_usu_id;
        $comprobante->usu_id = $c->cmp_usu_cre;

        $conec = NULL;
        COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
    }
}

function ajustar_venta_devolucion(){
   $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_devolucion' and "
           . "cmp_id in(74750,81768,88607,91532,95863,98763,98764,98766,98767,103110,103111,126910,121205,125262,127149,129679)";
    
    // $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_devolucion' and "
            // . "cmp_id in(103110)";
	
	$sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_devolucion' and "
            . "cmp_id in(81768)";
    
    $cmps = FUNCIONES::lista_bd_sql($sql);
    include_once 'modulos/venta/venta.class.php';
    $obj_ven = new VENTA();    
    
    foreach ($cmps as $c) {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$c->cmp_tabla_id'");
        
        if ($venta == NULL) {
            echo "<p>No existe el objeto venta $c->cmp_tabla_id.</p>";
            continue;
        }
        
        $pagado = $obj_ven->total_pagado($venta->ven_id);
        $tpagdo = $pagado->capital + $venta->ven_venta_pagado + $venta->ven_res_anticipo;
        $dev_monto = $venta->ven_dev_monto;
        $fecha = $c->cmp_fecha;
        $dev_ingreso = $tpagdo - $dev_monto;
        
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        $moneda = $c->cmp_mon_id;
        $detalles = array();
        
        if ($dev_monto > 0) {
            
            $sql_fpags = "select * from con_pago where fpag_tabla='$c->cmp_tabla' and fpag_tabla_id='$c->cmp_tabla_id'"
                    . " and fpag_estado='Activo' order by fpag_id";
            $dets = FUNCIONES::lista_bd_sql($sql_fpags);
            $i = 0;            
            
            if (count($dets) > 0) {
                foreach ($dets as $d) {
                    $_POST[a_fpag_monto][$i] = $d->fpag_monto;
                    $_POST[a_fpag_mon_id][$i] = $d->fpag_mon_id;
                    $_POST[a_fpag_forma_pago][$i] = $d->fpag_forma_pago;
                    $_POST[a_fpag_ban_nombre][$i] = $d->fpag_ban_nombre;
                    $_POST[a_fpag_ban_nro][$i] = $d->fpag_ban_nro;
                    $_POST[a_fpag_cue_id][$i] = $d->fpag_cue_id;
                    $_POST[a_fpag_descripcion][$i] = $d->fpag_descripcion;
                    $i++;
                }

                $params = array(
                    'tabla' => 'venta_devolucion',
                    'tabla_id' => $c->cmp_tabla_id,
                    'fecha' => $fecha,
                    'moneda' => $moneda,
                    'ingreso' => false,
                    'guardar_pago' => FALSE,
                    'une_id' => $urb->urb_une_id,
                    'glosa' => $c->cmp_glosa, 'ca' => '0', 'cf' => 0, 'cc' => 0
                );

                $detalles = FORMULARIO::insertar_pagos($params);
            }
        }
        
        $data = array(
            'moneda' => $moneda,
            'ges_id' => $c->cmp_ges_id,
            'fecha' => $fecha,
            'glosa' => $c->cmp_glosa,
            'interno' => $c->cmp_referido,
            'tabla_id' => $c->cmp_tabla_id,
            'urb' => $urb,
            'dev_monto' => $dev_monto,
            'dev_ingreso' => $dev_ingreso,
            'monto_pagado' => $tpagdo,
            'detalles' => $detalles,
        );

        $comprobante = MODELO_COMPROBANTE::devolucion_venta($data);
        $comprobante->cmp_id = $c->cmp_id;
        $comprobante->usu_per_id = $c->cmp_usu_id;
        $comprobante->usu_id = $c->cmp_usu_cre;

        $conec = NULL;
        COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
    }
}

function ajustar_venta_cambio_lote(){
   $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_cambio_lote' and "
           . "cmp_id in(77960,84633,84637,85595,93798,93800,97134,117063,147085)";
    
    // $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_cambio_lote' and "
            // . "cmp_id in(84633)";
    
    $cmps = FUNCIONES::lista_bd_sql($sql);
    
    foreach ($cmps as $c) {
        
        $objeto = FUNCIONES::objeto_bd_sql("select * from venta_negocio where "
                        . "vneg_tipo='cambio_lote' and vneg_ven_id='$c->cmp_tabla_id'"
                        . " and vneg_estado='Activado'");
        $par = json_decode($objeto->vneg_parametros);
        $monto_efectivo = $par->ven_monto_efectivo;
        $fecha = $c->cmp_fecha;

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$objeto->vneg_ven_id'");
        $venta_ori = $venta;

        $urb_ori = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta_ori->ven_urb_id'");
        $urb_des = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$par->new_urb_id'");

        $pagado_ori = EVENTOS::total_pagado($venta_ori->ven_id);
        $saldo_ori = $venta_ori->ven_monto_efectivo - $pagado_ori->capital - $pagado_ori->descuento + $pagado_ori->incremento;
        $monto_intercambio = $venta->ven_monto_intercambio;

        $monto_pagar = 0; //devolver
        if ($monto_efectivo <= 0) {            
            $monto_pagar = $monto_efectivo * (-1);
            $monto_efectivo = 0;
        }
                

        $moneda = $venta->ven_moneda;
        $cambio_usd = 1;
        if ($moneda == '1') {
            $cambio_usd = FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$fecha' and tca_mon_id=2");
        }
        
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$par->new_urb_id");
        $costo = $par->new_lot_sup * ($urb->urb_val_costo * $cambio_usd);
        $costo_cub = $par->ven_pagado;
        $costo_pag = 0;
        if ($costo_cub > $costo) {
            $costo_cub = $costo;
        }

        $amontos = array();
        $amontos_pag = array();
        if ($monto_intercambio > 0) {
            $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
            $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");
        }

        $data = array(
            'moneda' => $venta_ori->ven_moneda,
            'moneda_des' => $venta->ven_moneda,
            'ges_id' => $c->cmp_ges_id,
            'fecha' => $fecha,
            'glosa' => $c->cmp_glosa,
            'interno' => $c->cmp_referido,
            'tabla_id' => $venta_ori->ven_id,
            'urb_ori' => $urb_ori,
            'urb_des' => $urb_des,
            'costo' => $venta_ori->ven_costo,
            'costo_pagado' => $venta_ori->ven_costo_cub + $pagado_ori->costo,
            'pagado_ori' => $par->ven_pagado,
            'saldo_ori' => $saldo_ori,
            'saldo' => $par->ven_monto_efectivo,
            'monto_pagar' => $monto_pagar,
            'intercambio' => $monto_intercambio,
            'inter_montos' => $amontos,
            'inter_montos_pag' => $amontos_pag,
            'costo_des' => $costo,
            'costo_cub_des' => $costo_cub,
        );
        
        $comprobante = MODELO_COMPROBANTE::venta_cambio_lote($data);
        $comprobante->cmp_id = $c->cmp_id;
        $comprobante->usu_per_id = $c->cmp_usu_id;
        $comprobante->usu_id = $c->cmp_usu_cre;

        $conec = NULL;
        COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
    }
}

function ajustar_venta_activacion(){
   $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_activacion' and "
           . "cmp_id in(89803,90210,103876,103878,103880,103881,103882,103883,103887,103888,103889,103890,103891,103892,103893,103894,103895,103896,103897,103899,103901,103902,103903,103904,103906,103907,103908,103909,114102,114103,115543,147083,178417,178418,183010,183011,183870)";
    
    // $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta_activacion' and "
            // . "cmp_id in(90210)";
    
    $cmps = FUNCIONES::lista_bd_sql($sql);
    
    foreach ($cmps as $c) {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$c->cmp_tabla_id'");
        $vnreversion = FUNCIONES::objeto_bd_sql("select * from venta_negocio where "
                . "vneg_tipo='reversion' and vneg_ven_id=$venta->ven_id "
                . "order by vneg_id desc limit 1");
        $vneg_id = 0;
        if ($vnreversion) {
            $vneg_id = $vnreversion->vneg_id;
        }
        
        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_retencion' "
                . "and cmp_tabla_id='$venta->ven_id' and cmp_tarea_id='$vneg_id'");
        
//        FUNCIONES::print_pre($cmp);
        
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

        if ($cmp) {
            $rdetalles = FUNCIONES::lista_bd_sql("select * from con_comprobante_detalle where cde_cmp_id=$cmp->cmp_id and cde_mon_id=$cmp->cmp_mon_id");
            $data = array(
                'moneda' => $c->cmp_mon_id,
                'ges_id' => $c->cmp_ges_id,
                'fecha' => $c->cmp_fecha,
                'glosa' => $c->cmp_glosa,
                'interno' => $c->cmp_referido,
                'tabla_id' => $c->cmp_tabla_id,
                'tarea_id' => $c->cmp_tarea_id,
                'urb' => $urb,
                'rdetalles' => $rdetalles
            );

            $comprobante = MODELO_COMPROBANTE::venta_reactivacion($data);
            $comprobante->cmp_id = $c->cmp_id;
            $comprobante->usu_per_id = $c->cmp_usu_id;
            $comprobante->usu_id = $c->cmp_usu_cre;

            $conec = NULL;
            COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
        }
    }
}

function ajustar_venta(){
    $sql = "select * from con_comprobante where cmp_eliminado='No' and cmp_tabla='venta' and "
           . "cmp_id in(169166,169853)";
}

// ajustar_venta_fusion();
// ajustar_venta_retencion();
//ajustar_venta_devolucion();
// ajustar_venta_cambio_lote();
// ajustar_venta_activacion();

ajustar_venta_pagos();
