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
// require_once 'clases/thread.php';

$arr_gestiones = array(14, 15, 16, 17, 18, 19);
$arr_roots = array();

foreach ($arr_gestiones as $ges_id) {
    $sql_tit = "select can_id as campo from con_cuenta_ca where can_ges_id='$ges_id' and can_tipo='Titulo'";
    $can_id = FUNCIONES::atributo_bd_sql($sql_tit);
    $arr_roots[$ges_id] = $can_id;
}

FUNCIONES::print_pre($arr_roots);

// $arr_roots = array(
// 14 => 7,
// 15 => 8,
// 16 => 9,
// 17 => 10,
// 18 => 11
// );

function crear_ca_analitica($ges_id) {

    $fs = fopen("sql_crear_analiticos_$ges_id.sql", 'w');

    $sql = "insert into con_cuenta_ca(can_ges_id,can_codigo,can_descripcion,can_tipo,can_padre_id,can_eliminado,can_tree_level)values";
    global $arr_roots;
    $padre_id = $arr_roots[$ges_id];
    $conec = new ADO();
    $long_max = 10;
    $char_pad = '0';
    $tipo = STR_PAD_LEFT;
    $prefijo = "01.";

    $sql_ventas = "select * from venta where ven_estado != 'Anulado'";
    $ventas = FUNCIONES::objetos_bd_sql($sql_ventas);
    ob_start();
    $limit_ins = 300;
    $cont = 0;
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $nro = $venta->ven_id;
        $codigo = $prefijo . str_pad($nro, $long_max, $char_pad, $tipo);
        $descripcion = "VENTA $nro";
//        echo "<p>codigo:$codigo</p>";

        $sql_ins = $sql . "('$ges_id','$codigo','$descripcion','Movimiento','$padre_id','No','2')";
        echo "<p>$sql_ins;</p>";
//        $conec->ejecutar($sql_ins);
//        if ($venta->ven_can_codigo == '') {
        if ($ges_id == 14) {
            $sql_upd_venta = "update venta set ven_can_codigo='$codigo' where ven_id='$venta->ven_id'";
            echo "<p>$sql_upd_venta;</p>";
//        $conec->ejecutar($sql_upd_venta);        
        }

        $cont++;
        if ($cont == $limit_ins) {
            $cont = 0;
            $contenido = ob_get_contents();
            ob_end_clean();

            $contenido = str_replace('<p>', '', $contenido);
            $contenido = str_replace('</p>', PHP_EOL, $contenido);
            fputs($fs, $contenido);
            ob_start();
        }

        $ventas->siguiente();
    }

    if ($cont > 0 && $cont < $limit_ins) {
        $contenido = ob_get_contents();
        $contenido = str_replace('<p>', '', $contenido);
        $contenido = str_replace('</p>', PHP_EOL, $contenido);
        ob_end_clean();
        fputs($fs, $contenido);
    }
    fclose($fs);
}

function actualizar_comprobantes($ven_id = 0, $limit = '') {

    $ahora = date('H:i:s');
    echo "<p style='color: green;'>Inicio:$ahora</p>";

    $tiempo = date('dmY_His_') . rand();
    $limit_arr = 100;

    $f_ven = '';
    if ($ven_id * 1 > 0) {
        $f_ven = " and ven_id='$ven_id'";
    }

    $sql_ven = "select * from venta 
        inner join urbanizacion on(ven_urb_id=urb_id) 
        inner join con_comprobante on(
            cmp_tabla='venta' and cmp_tabla_id=ven_id and cmp_eliminado='No'
        ) 
        where ven_estado!='Anulado' $f_ven $limit";

    echo $sql_ven;
//    return false;            

    $origen = "ventas";
    $arc_upd = "actualizar_cmps_" . $origen . "_" . $tiempo . ".sql";
    echo "<p>-- $arc_upd</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');
    fclose($fp);

    $ventas = FUNCIONES::objetos_bd_sql($sql_ven);

    $arr_contenido_ventas = array();

    $cont_arr = 0;
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $cont_arr++;
        $arr_contenido_ventas[] = actualizar_cmp_venta($venta);

        if ($cont_arr == $limit_arr) {
            $cont_arr = 0;
            escribir_en_archivo($arc_upd, $arr_contenido_ventas);
            $arr_contenido_ventas = array();
        }

        $ventas->siguiente();
    }

    if ($cont_arr > 0 && $cont_arr < $limit_arr) {
        $cont_arr = 0;
        escribir_en_archivo($arc_upd, $arr_contenido_ventas);
        $arr_contenido_ventas = array();
    }

    $ahora = date('H:i:s');
    echo "<p style='color: green;'>Fin:$ahora</p>";
}

function procesar_pagos($ven_id = 0, $limit = NULL, $urb_id = 0) {
    /*     * **********VENTA PAGOS */

    $ahora = date('H:i:s');
    echo "<p style='color: green;'>Inicio:$ahora</p>";

    $intervalo = '';
    $s_intervalo = '';
    if ($limit) {
        $intervalo .= " and vpag_id >= '$limit->min' and vpag_id <= '$limit->max'";
        $s_intervalo .= "_vpag_id_from_{$limit->min}_to_{$limit->max}";
    }

    $tiempo = date('dmY_His_') . rand();
    $limit_arr = 100;

    $f_ven = '';
    if ($ven_id * 1 > 0) {
        $f_ven = " and vpag_ven_id='$ven_id'";
    }

    $f_urb = '';
    $s_urb = "";
    if ($urb_id * 1 > 0) {
        $f_urb = " and urb_id='$urb_id'";
        $obj_urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$urb_id'");
        $s_urb = "_" . strtoupper($obj_urb->urb_nombre);
    }

    $sql_pagos = "select vpag_id,cmp_ges_id,cmp_id,cmp_tabla,cmp_tabla_id,ven_id,
    ven_moneda,ven_can_codigo,urb_cta_por_cobrar_bs,urb_ing_por_venta_bs,
    urb_cta_por_cobrar_usd,urb_ing_por_venta_usd,urb_ing_diferido,urb_costo,
    urb_costo_diferido,urb_inv_terrenos 
    from venta_pago
    inner join venta on(vpag_ven_id=ven_id)
    inner join urbanizacion on(ven_urb_id=urb_id)
    inner join con_comprobante on(
       cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id and cmp_eliminado='No'
    )
    where 1 $f_ven $intervalo $f_urb";

    FUNCIONES::print_pre($sql_pagos);

    $pagos = FUNCIONES::objetos_bd_sql($sql_pagos);

    $origen = "pagos";
    $arc_upd2 = "actualizar_cmps_" . $origen . $s_urb . "_" . $tiempo . $s_intervalo . ".sql";
    echo "<p>-- $arc_upd2</p>";

    $fp2 = fopen("imp_exp/" . $arc_upd2, 'w');
    fclose($fp2);
    $arr_contenido_pagos = array();

    $cont_arr = 0;
    $res_escr = true;
    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pago = $pagos->get_objeto();
        $cont_arr++;
//        echo "<p>$pago->vpag_id</p>";
        $arr_contenido_pagos[] = actualizar_cmps_pagos($pago);

        if ($cont_arr == $limit_arr) {
            $cont_arr = 0;
            $res_escr = escribir_en_archivo($arc_upd2, $arr_contenido_pagos);

            if (!$res_escr) {
                break;
            }

            $arr_contenido_pagos = array();
        }

        $pagos->siguiente();
    }

    if ($res_escr) {
        echo "<p>cont_arr:$cont_arr - limit_arr:$limit_arr</p>";

        if ($cont_arr > 0 && $cont_arr < $limit_arr) {
            $cont_arr = 0;
            escribir_en_archivo($arc_upd2, $arr_contenido_pagos);
            $arr_contenido_pagos = array();
        }
    } else {
        echo "<p>ERROR, NO SE PUDO ESCRIBIR EN EL ARCHIVO $arc_upd2.</p>";
    }

    $ahora = date('H:i:s');
    echo "<p style='color: green;'>Fin:$ahora</p>";
}

function procesar_pagos_anteriores_demazu($ven_id = 0, $limit = NULL, $urb_id = 0) {
    /*     * **********VENTA PAGOS */

    $ahora = date('H:i:s');
    echo "<p style='color: green;'>Inicio:$ahora</p>";

    $intervalo = '';
    $s_intervalo = '';
    if ($limit) {
        $intervalo .= " and vpag_id >= '$limit->min' and vpag_id <= '$limit->max'";
        $s_intervalo .= "vpag_id_from_{$limit->min}_to_{$limit->max}";
    }

    $tiempo = date('dmY_His_') . rand();
    $limit_arr = 100;

    $f_ven = '';
    if ($ven_id * 1 > 0) {
        $f_ven = " and vpag_ven_id='$ven_id'";
    }

    $f_urb = '';
    if ($urb_id * 1 > 0) {
        $f_urb = " and urb_id='$urb_id'";
    }

    $sql_pagos = "select vpag_id,cmp_ges_id,cmp_id,cmp_tabla,cmp_tabla_id,ven_id,
    ven_moneda,ven_can_codigo,urb_cta_por_cobrar_bs,urb_ing_por_venta_bs,
    urb_cta_por_cobrar_usd,urb_ing_por_venta_usd,urb_ing_diferido,urb_costo,
    urb_costo_diferido,urb_inv_terrenos 
    from venta_pago
    inner join venta on(vpag_ven_id=ven_id)
    inner join urbanizacion on(ven_urb_id=urb_id)
    inner join con_comprobante on(
       cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id and cmp_eliminado='No'
    )
    where 1 and vpag_estado='Activo' and vpag_fecha_pago < '2016-03-02' and vpag_monto>0 
	and vpag_venta_id is null and vpag_capital_inc=0 and vpag_capital_desc=0 and ven_estado != 'Anulado' 
	and ven_urb_id in(7,8,9,10) $f_ven $intervalo $f_urb";

    FUNCIONES::print_pre($sql_pagos);

    $pagos = FUNCIONES::objetos_bd_sql($sql_pagos);

    $origen = "pagos_anteriores_demazu";
    $arc_upd2 = "actualizar_cmps_" . $origen . "_" . $tiempo . "_" . $s_intervalo . ".sql";
    echo "<p>-- $arc_upd2</p>";
    $fp2 = fopen("imp_exp/" . $arc_upd2, 'w');

    fclose($fp2);
    $arr_contenido_pagos = array();

    $cont_arr = 0;
    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pago = $pagos->get_objeto();
        $cont_arr++;
//        echo "<p>$pago->vpag_id</p>";
        $arr_contenido_pagos[] = actualizar_cmps_pagos($pago);

        if ($cont_arr == $limit_arr) {
            $cont_arr = 0;
            escribir_en_archivo($arc_upd2, $arr_contenido_pagos);
            $arr_contenido_pagos = array();
        }

        $pagos->siguiente();
    }

    echo "<p>cont_arr:$cont_arr - limit_arr:$limit_arr</p>";

    if ($cont_arr > 0 && $cont_arr < $limit_arr) {
        $cont_arr = 0;
        escribir_en_archivo($arc_upd2, $arr_contenido_pagos);
        $arr_contenido_pagos = array();
    }

    $ahora = date('H:i:s');
    echo "<p style='color: green;'>Fin:$ahora</p>";
}

function escribir_en_archivo($nom_archivo, $arr_contenido) {

    $ruta = "imp_exp/" . $nom_archivo;

    if (!file_exists($ruta)) {
        return false;
    }

    $limit_ins = 200;
    $cont = 0;

    $fp = fopen($ruta, 'a');

    $buff = '';
    for ($i = 0; $i < count($arr_contenido); $i++) {
        $buff .= $arr_contenido[$i];
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

    return true;
}

function generar_script_sql($origen, $arr_contenido) {

    $limit_ins = 200;
    $cont = 0;

    $tiempo = date('dmY_His_') . rand();
    $arc_upd = "actualizar_cmps_" . $origen . "_" . $tiempo . ".sql";
    echo "<p>-- $arc_upd</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');


    $buff = '';
    for ($i = 0; $i < count($arr_contenido); $i++) {
        $buff .= $arr_contenido[$i];
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

    return $arc_upd;
}

function actualizar_cmp_venta($venta) {

    $salida_nav = "";

//    $filtro_venta = '';
//    if ($venta) {
//        $filtro_venta = "and cmp_tabla_id='$venta->ven_id'";
//    }
//
//    $sql_cmp = "select * from con_comprobante where cmp_tabla='venta'
//        $filtro_venta and cmp_eliminado='No'";
//    $cmps = FUNCIONES::objetos_bd_sql($sql_cmp);

    $cmp = clone $venta;

    ob_start();
    if ($cmp == NULL) {
        echo "<p>-- No existe comprobante de venta $venta->ven_id.</p>";
        $s_res = ob_get_contents();
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        $salida_nav .= $s_res;
        echo $salida_nav;
        return $salida_nav;
    }

//    $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion 
//        where urb_id='$venta->ven_urb_id'");

    $urbanizacion = clone $venta;

    if ($urbanizacion == NULL) {
        echo "<p>-- No existe la urbanizacion de la venta.</p>";
        $s_res = ob_get_contents();
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        $salida_nav .= $s_res;
        echo $salida_nav;
        return $salida_nav;
    }

    $codigo_cta_por_cobrar = "";
    $codigo_ingreso_por_venta = "";
    $codigo_ingreso_por_venta_diferido = "";
    $codigo_costo = "";
    $codigo_costo_diferido = "";
    $codigo_inv_terrenos = "";

    if ($venta->ven_moneda == 1) {
        $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_bs;
        $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_bs;
    } else {
        $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_usd;
        $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_usd;
    }

    $codigo_ingreso_por_venta_diferido = $urbanizacion->urb_ing_diferido;
    $codigo_costo = $urbanizacion->urb_costo;
    $codigo_costo_diferido = $urbanizacion->urb_costo_diferido;
    $codigo_inv_terrenos = $urbanizacion->urb_inv_terrenos;

    $cue_cta_por_cobrar_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_cta_por_cobrar) * 1;
    $cue_ingreso_por_venta = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_ingreso_por_venta) * 1;
    $cue_ingreso_por_venta_diferido = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_ingreso_por_venta_diferido) * 1;
    $cue_costo = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_costo) * 1;
    $cue_costo_diferido = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_costo_diferido) * 1;
    $cue_inv_terrenos = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_inv_terrenos) * 1;

    $arr_cue_ids = array(
        $cue_cta_por_cobrar_id,
        $cue_ingreso_por_venta,
        $cue_ingreso_por_venta_diferido,
        $cue_costo,
        $cue_costo_diferido,
        $cue_inv_terrenos
    );

    $arr_cuentas = array_filter($arr_cue_ids, 'purgar_ceros');

    if (count($arr_cuentas) == 0) {
        echo "<p>-- No existen cuentas para actualizar en los detalles.</p>";
        $s_res = ob_get_contents();
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        $salida_nav .= $s_res;
        return $salida_nav;
    }

    $s_cue_ids = implode(',', $arr_cuentas);

    $can_id = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta->ven_can_codigo) * 1;

    $sql_upd_cmp_dets = "update con_comprobante_detalle set cde_can_id='$can_id' where cde_cmp_id='$cmp->cmp_id' and cde_cue_id in ($s_cue_ids)";
    echo "<p>$sql_upd_cmp_dets;-- cmp_tabla='$cmp->cmp_tabla'</p>";

    $s_res = ob_get_contents();
    ob_end_clean();
    $s_res = str_replace('<p>', '', $s_res);
    $s_res = str_replace('</p>', PHP_EOL, $s_res);
    $salida_nav .= $s_res;

    // echo $salida_nav;
    return $salida_nav;
}

function actualizar_cmps_pagos_version_estable($venta) {

    $limit_ins = 100;
    $cont = 0;

    $salida_nav = "";

    $filtro_venta = '';
    if ($venta) {
        $filtro_venta = "and vpag_ven_id='$venta->ven_id'";
    }

    $sql_cmp = "select * from con_comprobante
    inner join venta_pago on(
        cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id
    )    
    where cmp_eliminado='No' and vpag_estado='Activo' $filtro_venta";

//    FUNCIONES::print_pre($sql_cmp);
//    return false;

    $cmps = FUNCIONES::objetos_bd_sql($sql_cmp);

    ob_start();
    if ($cmps->get_num_registros() == 0) {
        echo "<p>-- No existe el comprobantes de pagos de la venta $venta->ven_id.</p>";
        $s_res = ob_get_contents();
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        $salida_nav .= $s_res;
        return $salida_nav;
    }

    $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion 
        where urb_id='$venta->ven_urb_id'");

    if ($urbanizacion == NULL) {
        echo "<p>-- No existe la urbanizacion de la venta.</p>";
        $s_res = ob_get_contents();
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        $salida_nav .= $s_res;
        return $salida_nav;
    }

    $codigo_cta_por_cobrar = "";
    $codigo_ingreso_por_venta = "";
    $codigo_ingreso_por_venta_diferido = "";
    $codigo_costo = "";
    $codigo_costo_diferido = "";
    $codigo_inv_terrenos = "";

    if ($venta->ven_moneda == 1) {
        $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_bs;
        $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_bs;
    } else {
        $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_usd;
        $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_usd;
    }

    $codigo_ingreso_por_venta_diferido = $urbanizacion->urb_ing_diferido;
    $codigo_costo = $urbanizacion->urb_costo;
    $codigo_costo_diferido = $urbanizacion->urb_costo_diferido;
    $codigo_inv_terrenos = $urbanizacion->urb_inv_terrenos;

    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {
        $cmp = $cmps->get_objeto();
        $cue_cta_por_cobrar_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_cta_por_cobrar) * 1;
        $cue_ingreso_por_venta = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_ingreso_por_venta) * 1;
        $cue_ingreso_por_venta_diferido = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_ingreso_por_venta_diferido) * 1;
        $cue_costo = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_costo) * 1;
        $cue_costo_diferido = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_costo_diferido) * 1;
        $cue_inv_terrenos = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_inv_terrenos) * 1;

        $arr_cue_ids = array(
            $cue_cta_por_cobrar_id,
            $cue_ingreso_por_venta,
            $cue_ingreso_por_venta_diferido,
            $cue_costo,
            $cue_costo_diferido,
            $cue_inv_terrenos
        );

        $arr_cuentas = array_filter($arr_cue_ids, 'purgar_ceros');

        if (count($arr_cuentas) == 0) {
            echo "<p>-- No existen cuentas para actualizar en los detalles.</p>";
            $s_res = ob_get_contents();
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            $salida_nav .= $s_res;
            continue;
        }

        $s_cue_ids = implode(',', $arr_cuentas);

        $can_id = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta->ven_can_codigo) * 1;

        $sql_upd_cmp_dets = "update con_comprobante_detalle set cde_can_id='$can_id' where cde_cmp_id='$cmp->cmp_id' and cde_cue_id in ($s_cue_ids)";
        echo "<p>$sql_upd_cmp_dets;-- cmp_tabla='$cmp->cmp_tabla'</p>";

        $cont++;

        if ($cont == $limit_ins) {
            $cont = 0;
            $s_res = ob_get_contents();
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            $salida_nav .= $s_res;
            ob_start();
        }

        $cmps->siguiente();
    }

    if ($cont > 0 && $cont < $limit_ins) {
        $cont = 0;
        $s_res = ob_get_contents();
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        $salida_nav .= $s_res;
    }

    return $salida_nav;
}

function actualizar_cmps_pagos($venta) {

    $limit_ins = 100;
    $cont = 0;

    $salida_nav = "";

//    $filtro_venta = '';
//    if ($venta) {
//        $filtro_venta = "and vpag_ven_id='$venta->ven_id'";
//    }
//
//    $sql_cmp = "select * from con_comprobante
//    inner join venta_pago on(
//        cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id
//    )    
//    where cmp_eliminado='No' and vpag_estado='Activo' $filtro_venta";
//    FUNCIONES::print_pre($sql_cmp);
//    return false;
//    $cmps = FUNCIONES::objetos_bd_sql($sql_cmp);

    $cmp = clone $venta;

//    echo "<p>1.-</p>";

    ob_start();
    if ($cmp == NULL) {
        echo "<p>-- No existe el comprobantes de pagos de la venta $venta->ven_id.</p>";
        $s_res = ob_get_contents();
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        $salida_nav .= $s_res;
        return $salida_nav;
    }
//    $salida_nav .= "<p>2.-</p>";
//    $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion 
//        where urb_id='$venta->ven_urb_id'");

    $urbanizacion = clone $venta;

//    $salida_nav .= "<p>3.-</p>";
    if ($urbanizacion == NULL) {
        echo "<p>-- No existe la urbanizacion de la venta.</p>";
        $s_res = ob_get_contents();
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        $salida_nav .= $s_res;
        return $salida_nav;
    }
//    $salida_nav .= "<p>4.-</p>";

    $codigo_cta_por_cobrar = "";
    $codigo_ingreso_por_venta = "";
    $codigo_ingreso_por_venta_diferido = "";
    $codigo_costo = "";
    $codigo_costo_diferido = "";
    $codigo_inv_terrenos = "";

//    $salida_nav .= "<p>5.-</p>";
    if ($venta->ven_moneda == 1) {
        $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_bs;
        $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_bs;
    } else {
        $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_usd;
        $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_usd;
    }
//    $salida_nav .= "<p>6.-</p>";

    $codigo_ingreso_por_venta_diferido = $urbanizacion->urb_ing_diferido;
    $codigo_costo = $urbanizacion->urb_costo;
    $codigo_costo_diferido = $urbanizacion->urb_costo_diferido;
    $codigo_inv_terrenos = $urbanizacion->urb_inv_terrenos;

//    $salida_nav .= "<p>7.-</p>";
    if (TRUE) {
//        echo "<p>entrando a true</p>";
        $cue_cta_por_cobrar_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_cta_por_cobrar) * 1;
        $cue_ingreso_por_venta = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_ingreso_por_venta) * 1;
        $cue_ingreso_por_venta_diferido = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_ingreso_por_venta_diferido) * 1;
        $cue_costo = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_costo) * 1;
        $cue_costo_diferido = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_costo_diferido) * 1;
        $cue_inv_terrenos = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_inv_terrenos) * 1;

        $arr_cue_ids = array(
            $cue_cta_por_cobrar_id,
            $cue_ingreso_por_venta,
            $cue_ingreso_por_venta_diferido,
            $cue_costo,
            $cue_costo_diferido,
            $cue_inv_terrenos
        );

        $arr_cuentas = array_filter($arr_cue_ids, 'purgar_ceros');

        if (count($arr_cuentas) == 0) {
            echo "<p>-- No existen cuentas para actualizar en los detalles.</p>";
            $s_res = ob_get_contents();
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            $salida_nav .= $s_res;
            return $salida_nav;
        }

        $s_cue_ids = implode(',', $arr_cuentas);

        $can_id = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta->ven_can_codigo) * 1;

        $sql_upd_cmp_dets = "update con_comprobante_detalle set cde_can_id='$can_id' where cde_cmp_id='$cmp->cmp_id' and cde_cue_id in ($s_cue_ids)";
        // echo "<p>$sql_upd_cmp_dets; -- $cmp->cmp_tabla:$cmp->cmp_tabla_id.</p>";
        echo "<p>$sql_upd_cmp_dets;</p>";

        $cont++;

        if ($cont == $limit_ins) {
            $cont = 0;
            $s_res = ob_get_contents();
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            $salida_nav .= $s_res;
            ob_start();
        }
    }
//    $salida_nav .= "<p>8.-</p>";

    if ($cont > 0 && $cont < $limit_ins) {
        $cont = 0;
        $s_res = ob_get_contents();
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        $salida_nav .= $s_res;
    }

//    $salida_nav .= "<p>9.-</p>";
//    echo $salida_nav;
    return $salida_nav;
}

function actualizar_cmps_retenciones($filtro_venta = '') {

    $ele_info = new stdClass();
    $ele_info->origen = "retenciones";

    $conec = new ADO();
    $limit_ins = 100;

    $cont = 0;
    $arr_cmps_ids = array();

    $tiempo = date('dmY_His_') . rand();

    $arc_upd = "actualizar_cmps_" . $ele_info->origen . "_" . $tiempo . ".sql";

    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');


    // $filtro_venta = '';
    // if ($venta) {
    // $filtro_venta = "and cmp_tabla_id='$venta->ven_id'";
    // }

    $sql_cmp = "select * from con_comprobante
	inner join venta on(cmp_tabla='venta_retencion' and cmp_tabla_id=ven_id and cmp_eliminado='No')
    inner join urbanizacion on(ven_urb_id=urb_id)
	where 1 $filtro_venta";

    echo "<p>$sql_cmp;</p>";

    $cmps = FUNCIONES::objetos_bd_sql($sql_cmp);

    ob_start();
    $salida_nav = "";

    if ($cmps->get_num_registros() == 0) {
        echo "<p>-- No existen comprobantes de retenciones.</p>";
        $s_res = ob_get_contents();
        $salida_nav .= $s_res;
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        fputs($fp, $s_res);
        fclose($fp);
        echo $salida_nav;
        return FALSE;
    }

    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {
        $cmp = $cmps->get_objeto();

        $venta = clone $cmp;
        $urbanizacion = clone $cmp;

        // if ($urbanizacion == NULL) {
        // echo "<p>-- No existe la urbanizacion de la venta.</p>";
        // $s_res = ob_get_contents();
        // $salida_nav .= $s_res;
        // ob_end_clean();
        // $s_res = str_replace('<p>', '', $s_res);
        // $s_res = str_replace('</p>', PHP_EOL, $s_res);
        // fputs($fp, $s_res);
        // fclose($fp);
        // echo $salida_nav;
        // return FALSE;
        // }

        $codigo_cta_por_cobrar = "";
        $codigo_ingreso_por_venta = "";
        $codigo_ingreso_por_venta_diferido = "";
        $codigo_costo = "";
        $codigo_costo_diferido = "";
        $codigo_inv_terrenos = "";
        $codigo_inv_terrenos_adj = "";

        if ($venta->ven_moneda == 1) {
            $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_bs;
            $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_bs;
        } else {
            $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_usd;
            $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_usd;
        }

        $codigo_ingreso_por_venta_diferido = $urbanizacion->urb_ing_diferido;
        $codigo_costo = $urbanizacion->urb_costo;
        $codigo_costo_diferido = $urbanizacion->urb_costo_diferido;
        $codigo_inv_terrenos = $urbanizacion->urb_inv_terrenos;
        $codigo_inv_terrenos_adj = $urbanizacion->urb_inv_terrenos_adj;

        /**/
        $cue_cta_por_cobrar_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_cta_por_cobrar) * 1;
        $cue_ingreso_por_venta = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_ingreso_por_venta) * 1;
        $cue_ingreso_por_venta_diferido = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_ingreso_por_venta_diferido) * 1;
        $cue_costo = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_costo) * 1;
        $cue_costo_diferido = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_costo_diferido) * 1;
        $cue_inv_terrenos = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_inv_terrenos) * 1;
        $cue_inv_terrenos_adj = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_inv_terrenos_adj) * 1;

        $arr_cue_ids = array(
            $cue_cta_por_cobrar_id,
            $cue_ingreso_por_venta,
            $cue_ingreso_por_venta_diferido,
            $cue_costo,
            $cue_costo_diferido,
            $cue_inv_terrenos,
            $cue_inv_terrenos_adj
        );

        $arr_cuentas = array_filter($arr_cue_ids, 'purgar_ceros');

        if (count($arr_cuentas) == 0) {
            echo "<p>-- No existen cuentas para actualizar en los detalles.</p>";
            $s_res = ob_get_contents();
            $salida_nav .= $s_res;
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            continue;
        }

        $s_cue_ids = implode(',', $arr_cuentas);

        $can_id = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta->ven_can_codigo) * 1;

        $sql_upd_cmp_dets = "update con_comprobante_detalle set cde_can_id='$can_id' where cde_cmp_id='$cmp->cmp_id' and cde_cue_id in ($s_cue_ids)";
        // echo "<p>$sql_upd_cmp_dets;-- $cmp->cmp_tabla:$cmp->cmp_tabla_id</p>";
        echo "<p>$sql_upd_cmp_dets;</p>";
        //    $conec->ejecutar($sql_upd_cmp_dets);
        $arr_cmps_ids[] = $cmp->cmp_id;

        $cont++;

        if ($cont == $limit_ins) {
            $cont = 0;
            $s_res = ob_get_contents();
            $salida_nav .= $s_res;
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            ob_start();
        }

        $cmps->siguiente();
    }

    if ($cont > 0 && $cont < $limit_ins) {
        $cont = 0;
        $s_res = ob_get_contents();
        $salida_nav .= $s_res;
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        fputs($fp, $s_res);
    }
    fclose($fp);
    echo $salida_nav;
}

function actualizar_cmps_activaciones($filtro_venta = '') {

    $ele_info = new stdClass();
    $ele_info->origen = "activaciones";

    $conec = new ADO();
    $limit_ins = 100;

    $cont = 0;
    $arr_cmps_ids = array();

    $tiempo = date('dmY_His_') . rand();

    $arc_upd = "actualizar_cmps_" . $ele_info->origen . "_" . $tiempo . ".sql";

    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    // $filtro_venta = '';
    // if ($venta) {
    // $filtro_venta = " and cmp_tabla_id='$venta->ven_id'";
    // }

    $sql_cmp = "select * from con_comprobante
    inner join venta on(cmp_tabla='venta_activacion' and cmp_tabla_id=ven_id and cmp_eliminado='No')
    inner join urbanizacion on(ven_urb_id=urb_id)
    $filtro_venta";

    echo "<p>$sql_cmp;</p>";

    $cmps = FUNCIONES::objetos_bd_sql($sql_cmp);

    ob_start();
    $salida_nav = "";
    if ($cmps->get_num_registros() == 0) {
        echo "<p>-- No existen comprobantes de activaciones.</p>";
        $s_res = ob_get_contents();
        $salida_nav .= $s_res;
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        fputs($fp, $s_res);
        fclose($fp);
        echo $salida_nav;
        return FALSE;
    }

    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {
        $cmp = $cmps->get_objeto();

        $venta = clone $cmp;
        $urbanizacion = clone $cmp;

        if ($urbanizacion == NULL) {
            echo "<p>-- No existe la urbanizacion de la venta.</p>";
            $s_res = ob_get_contents();
            $salida_nav .= $s_res;
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            fclose($fp);
            echo $salida_nav;
            return FALSE;
        }

        $codigo_cta_por_cobrar = "";
        $codigo_ingreso_por_venta = "";
        $codigo_ingreso_por_venta_diferido = "";
        $codigo_costo = "";
        $codigo_costo_diferido = "";
        $codigo_inv_terrenos = "";
        $codigo_inv_terrenos_adj = "";

        if ($venta->ven_moneda == 1) {
            $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_bs;
            $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_bs;
        } else {
            $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_usd;
            $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_usd;
        }

        $codigo_ingreso_por_venta_diferido = $urbanizacion->urb_ing_diferido;
        $codigo_costo = $urbanizacion->urb_costo;
        $codigo_costo_diferido = $urbanizacion->urb_costo_diferido;
        $codigo_inv_terrenos = $urbanizacion->urb_inv_terrenos;
        $codigo_inv_terrenos_adj = $urbanizacion->urb_inv_terrenos_adj;

        $cue_cta_por_cobrar_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_cta_por_cobrar) * 1;
        $cue_ingreso_por_venta = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_ingreso_por_venta) * 1;
        $cue_ingreso_por_venta_diferido = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_ingreso_por_venta_diferido) * 1;
        $cue_costo = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_costo) * 1;
        $cue_costo_diferido = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_costo_diferido) * 1;
        $cue_inv_terrenos = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_inv_terrenos) * 1;
        $cue_inv_terrenos_adj = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $codigo_inv_terrenos_adj) * 1;

        $arr_cue_ids = array(
            $cue_cta_por_cobrar_id,
            $cue_ingreso_por_venta,
            $cue_ingreso_por_venta_diferido,
            $cue_costo,
            $cue_costo_diferido,
            $cue_inv_terrenos,
            $cue_inv_terrenos_adj
        );

        $arr_cuentas = array_filter($arr_cue_ids, 'purgar_ceros');

        if (count($arr_cuentas) == 0) {
            echo "<p>-- No existen cuentas para actualizar en los detalles.</p>";
            $s_res = ob_get_contents();
            $salida_nav .= $s_res;
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            continue;
        }

        $s_cue_ids = implode(',', $arr_cuentas);

        $can_id = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta->ven_can_codigo) * 1;

        $sql_upd_cmp_dets = "update con_comprobante_detalle set cde_can_id='$can_id' where cde_cmp_id='$cmp->cmp_id' and cde_cue_id in ($s_cue_ids)";
        echo "<p>$sql_upd_cmp_dets;</p>";
        //    $conec->ejecutar($sql_upd_cmp_dets);
        $arr_cmps_ids[] = $cmp->cmp_id;

        $cont++;

        if ($cont == $limit_ins) {
            $cont = 0;
            $s_res = ob_get_contents();
            $salida_nav .= $s_res;
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            ob_start();
        }

        $cmps->siguiente();
    }

    if ($cont > 0 && $cont < $limit_ins) {
        $cont = 0;
        $s_res = ob_get_contents();
        $salida_nav .= $s_res;
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        fputs($fp, $s_res);
    }
    fclose($fp);
    echo $salida_nav;
}

function actualizar_cmps_fusiones($venta = NULL) {

    $ele_info = new stdClass();
    $ele_info->origen = "fusiones";

    $conec = new ADO();
    $limit_ins = 100;

    $filtro_venta = '';
    if ($venta) {
        $filtro_venta = " and ven_id='$venta->ven_id'";
    }

    $sql_fus = "select * from venta_negocio inner join venta on(vneg_ven_ori=ven_id)    
    where vneg_estado='Activado' and vneg_tipo='fusion' $filtro_venta";

    echo "<p>$sql_fus</p>";
//    return false;

    $fusiones = FUNCIONES::objetos_bd_sql($sql_fus);

    $cont = 0;
    $arr_cmps_ids = array();

    $tiempo = date('dmY_His_') . rand();

    $arc_upd = "actualizar_cmps_fusiones_" . $tiempo . ".sql";

    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    ob_start();
    $salida_nav = "";
    for ($i = 0; $i < $fusiones->get_num_registros(); $i++) {

        $fus = $fusiones->get_objeto();

        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante "
                        . "where cmp_tabla = 'venta_fusion' "
                        . "and cmp_tabla_id='$fus->vneg_ven_ori' and cmp_eliminado='No'");

        if ($cmp == NULL) {
            echo "<p>-- NO EXISTE EL COMPROBANTE SUPUESTAMENTE GENERADO ANTERIORMENTE</p>";
            $s_res = ob_get_contents();
            $salida_nav .= $s_res;
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            ob_start();

            $fusiones->siguiente();
            continue;
        }

        $arr_cmps_ids[] = $cmp->cmp_id;

        $par = json_decode($fus->vneg_parametros);

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$fus->vneg_ven_id'");
        $venta_ori = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$fus->vneg_ven_ori");

        $capital_efectivo = $par->tot_capital;
        $capital_pag = $par->tot_capital;
        $capital_desc = 0; //$par->ori_descuento;
        $capital_inc = 0; //$par->ori_incremento;

        $saldo_final = $par->ven_monto_efectivo;

        $saldo_costo = $venta->ven_costo - $venta->ven_res_anticipo - $venta->ven_monto_intercambio - $venta->ven_cuota_inicial - $venta->ven_venta_pagado;
        $costo_pagado = 0;
        if ($saldo_costo > 0) {

            if ($saldo_final <= 0) {
                $costo_pagado = $venta->ven_costo - $venta->ven_costo_cub - $venta->ven_costo_pag;
            } else {
                $costo_pagado = round(($capital_pag * $saldo_costo) / $venta->ven_monto_efectivo, 2);
            }
        }

        $monto_pagar = 0;
        if ($saldo_final < 0) {
            $capital_pag = $par->tot_capital + $par->ven_monto_efectivo;
            $monto_pagar = $par->ven_monto_efectivo * (-1);
            $saldo_final = 0;
        }

        $urb_ori = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta_ori->ven_urb_id'");
        $urb_des = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

        $monto_intercambio = $venta->ven_monto_intercambio;

        $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
        $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");

        $pagado_ori = total_pagado($venta_ori->ven_id);
        $tot_pagado_ori = $par->ori_tot_capital;
        $ret_ingreso = $par->ret_ingreso;

        $saldo_ori = $venta_ori->ven_monto_efectivo - $pagado_ori->capital - $pagado_ori->descuento + $pagado_ori->incremento;

        $data = array(
            'moneda' => $venta_ori->ven_moneda,
            'moneda_des' => $venta->ven_moneda,
            'ges_id' => $cmp->cmp_ges_id,
            'fecha' => $cmp->cmp_fecha,
            'glosa' => $cmp->cmp_glosa,
            'interno' => $cmp->cmp_referido,
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
            'monto_pagar' => $monto_pagar,
            'ven_can_codigo_ori' => $venta_ori->ven_can_codigo,
            'ven_can_codigo_dest' => $venta->ven_can_codigo,
        );

        $comprobante = MODELO_COMPROBANTE::venta_fusion($data);
        $comprobante->cmp_id = $cmp->cmp_id;
        $comprobante->usu_per_id = $cmp->cmp_usu_id;
        $comprobante->usu_id = $cmp->cmp_usu_cre;
        COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
        $cont++;

        if ($cont == $limit_ins) {
            $cont = 0;
            $s_res = ob_get_contents();
            $salida_nav .= $s_res;
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            ob_start();
        }

        $fusiones->siguiente();
    }

    if ($cont > 0 && $cont < $limit_ins) {
        $cont = 0;
        $s_res = ob_get_contents();
        $salida_nav .= $s_res;
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        fputs($fp, $s_res);
    }
    fclose($fp);
    echo $salida_nav;

//    return FALSE;

    if (count($arr_cmps_ids) > 0) {
        $s_cmps_ids = implode(',', $arr_cmps_ids);
        respaldar_cmp($s_cmps_ids, $ele_info, $tab_dmp_id);
        $sql_sel_cmps_eq = "select * from con_comprobante where cmp_id in ($s_cmps_ids);";
        echo "<p>$sql_sel_cmps_eq</p>";
    } else {
        echo "<p>-- NADA PARA RESPALDAR.</p>";
        return false;
    }
}

function actualizar_cmps_cambio_lote($venta = NULL) {

    $ele_info = new stdClass();
    $ele_info->origen = "cambios_de_lote";

    $conec = new ADO();
    $limit_ins = 100;

    $filtro_venta = '';
    if ($venta) {
        $filtro_venta = " and ven_id='$venta->ven_id'";
    }

    $sql_fus = "select * from venta_negocio inner join venta on(vneg_ven_id=ven_id)    
    where vneg_estado='Activado' and vneg_tipo='cambio_lote' $filtro_venta";

    echo "<p>$sql_fus</p>";
//    return false;

    $fusiones = FUNCIONES::objetos_bd_sql($sql_fus);

    $cont = 0;
    $arr_cmps_ids = array();

    $tiempo = date('dmY_His_') . rand();

    $arc_upd = "actualizar_cmps_cambios_lote_" . $tiempo . ".sql";

    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    ob_start();
    $salida_nav = "";
    for ($i = 0; $i < $fusiones->get_num_registros(); $i++) {

        $fus = $fusiones->get_objeto();

        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante "
                        . "where cmp_tabla = 'venta_cambio_lote' "
                        . "and cmp_tabla_id='$fus->vneg_ven_id' and cmp_eliminado='No'");

        if ($cmp == NULL) {
            echo "<p>-- NO EXISTE EL COMPROBANTE SUPUESTAMENTE GENERADO ANTERIORMENTE</p>";
            $s_res = ob_get_contents();
            $salida_nav .= $s_res;
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            ob_start();

            $fusiones->siguiente();
            continue;
        }

        $arr_cmps_ids[] = $cmp->cmp_id;

        $par = json_decode($fus->vneg_parametros);
        $monto_efectivo = $par->ven_monto_efectivo;

        $monto_pagar = 0; //devolver
        if ($monto_efectivo <= 0) {
            $monto_pagar = $monto_efectivo * (-1);
            $monto_efectivo = 0;
        }

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$fus->vneg_ven_id'");
        $monto_intercambio = $venta->ven_monto_intercambio;

        $venta_ori = $venta; // FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$objeto->vneg_ven_ori");
        $venta_des = FUNCIONES::objeto_bd_sql("select * from venta where ven_venta_id='$venta->ven_id'");
        $referido = $cmp->cmp_referido;

        $urb_ori = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta_ori->ven_urb_id'");
        $urb_des = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta_des->ven_urb_id'");

        $pagado_ori = total_pagado($venta_ori->ven_id);
        $saldo_ori = $venta_ori->ven_monto_efectivo - $pagado_ori->capital - $pagado_ori->descuento + $pagado_ori->incremento;

        $amontos = array();
        $amontos_pag = array();
        if ($monto_intercambio > 0) {
            $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
            $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");
        }

        $costo = $venta_des->ven_costo;
        $costo_cub = $par->ven_pagado;
        $costo_pag = 0;
        if ($costo_cub > $costo) {
            $costo_cub = $costo;
        }

        $data = array(
            'moneda' => $venta_ori->ven_moneda,
            'moneda_des' => $venta->ven_moneda,
            'ges_id' => $cmp->cmp_ges_id,
            'fecha' => $cmp->cmp_fecha,
            'glosa' => $cmp->cmp_glosa,
            'interno' => $referido,
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
            'ven_can_codigo_ori' => $venta_ori->ven_can_codigo,
            'ven_can_codigo_dest' => $venta_des->ven_can_codigo,
        );
//'$costo','$costo_cub','$costo_pag'
        $comprobante = MODELO_COMPROBANTE::venta_cambio_lote($data);
        $comprobante->cmp_id = $cmp->cmp_id;
        $comprobante->usu_per_id = $cmp->cmp_usu_id;
        $comprobante->usu_id = $cmp->cmp_usu_cre;
        COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
        $cont++;

        if ($cont == $limit_ins) {
            $cont = 0;
            $s_res = ob_get_contents();
            $salida_nav .= $s_res;
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            ob_start();
        }

        $fusiones->siguiente();
    }

    if ($cont > 0 && $cont < $limit_ins) {
        $cont = 0;
        $s_res = ob_get_contents();
        $salida_nav .= $s_res;
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        fputs($fp, $s_res);
    }
    fclose($fp);
    echo $salida_nav;

//    return FALSE;

    if (count($arr_cmps_ids) > 0) {
        $s_cmps_ids = implode(',', $arr_cmps_ids);
        respaldar_cmp($s_cmps_ids, $ele_info, $tab_dmp_id);
        $sql_sel_cmps_eq = "select * from con_comprobante where cmp_id in ($s_cmps_ids);";
        echo "<p>$sql_sel_cmps_eq</p>";
    } else {
        echo "<p>-- NADA PARA RESPALDAR.</p>";
        return false;
    }
}

function total_pagado($ven_id) {
    // $sql_pag = "select sum(ind_interes_pagado)as interes, sum(ind_capital_pagado) as capital, sum(ind_monto_pagado) as monto, 
    // sum(ind_capital_desc) as descuento, sum(ind_capital_inc) as incremento ,
    // sum(ind_costo_pagado) as costo
    // from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id 
    // ";
    // $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
    $pagado = FUNCIONES::total_pagado($ven_id);
    return $pagado;
}

function respaldar_cmp($s_cmps_ids, $ele_info = NULL, $id_dump = 0, $dir = '') {
	
    $limite_ins = 10;
    $tiempo = ($ele_info->tiempo)? $ele_info->tiempo: date('dmY_His_') . rand();

    $ademas = "_";
    if ($ele_info->min) {
        $ademas .= $ele_info->min . "_" . $ele_info->max . "_";
    }
	
	$ademas_dir = '';
	if ($dir != '') {
		$ademas_dir = "$dir/";
	}
	
	if ($ele_info->accion == 'AGREGAR') {
	
		if (!file_exists("imp_exp/" . $ele_info->nom_arch_resp)) {
			return false;
		} else {	
			$fp = fopen("imp_exp/" . $ele_info->nom_arch_resp, 'a');
		}
		
	} else {
	
		$arc_bck = $ademas_dir . "detalles_cmps_" . $ele_info->origen . $ademas . $tiempo . ".sql";
		FUNCIONES::bd_query("update tab_aux_dump set nombre_archivo_bck='$arc_bck' where id='$id_dump'");
		$fp = fopen("imp_exp/" . $arc_bck, 'w');
	}    
	
	if ($s_cmps_ids == '') {
	
		if ($ele_info->accion == 'AGREGAR') {
			return false;
		} else {
			return $arc_bck;
		}
	}

    $sql_cmp = "select * from con_comprobante_detalle where cde_cmp_id in ($s_cmps_ids)";

    $dets = FUNCIONES::objetos_bd_sql($sql_cmp);

    $sql_ins_plantilla = "INSERT INTO con_comprobante_detalle(cde_cmp_id, cde_mon_id, cde_secuencia, cde_can_id, cde_cco_id, cde_cfl_id, cde_cue_id, cde_valor, cde_glosa, cde_eliminado, cde_libro, cde_cu, cde_idpadre, cde_int_id, cde_fpago, cde_fpago_ban_nombre, cde_fpago_ban_nro, cde_fpago_descripcion, cde_une_id, cde_doc_id)VALUES";

    $cont = 0;
    ob_start();
    for ($i = 0; $i < $dets->get_num_registros(); $i++) {
        $det = $dets->get_objeto();

        $coma = ($cont == 0) ? '' : ',';

        $sql_ins .= $coma . "('$det->cde_cmp_id', '$det->cde_mon_id', '$det->cde_secuencia', "
                . "'$det->cde_can_id', '$det->cde_cco_id', '$det->cde_cfl_id', '$det->cde_cue_id', "
                . "'$det->cde_valor', '$det->cde_glosa', '$det->cde_eliminado', '$det->cde_libro', "
                . "'$det->cde_cu', '$det->cde_idpadre', '$det->cde_int_id', '$det->cde_fpago', "
                . "'$det->cde_fpago_ban_nombre', '$det->cde_fpago_ban_nro', '$det->cde_fpago_descripcion', "
                . "'$det->cde_une_id', '$det->cde_doc_id')";

        $cont++;

        if ($cont == $limite_ins) {

            echo $sql_ins_plantilla . $sql_ins . ";";
            $sql_ins = '';
            $cont = 0;
            $s_res = ob_get_contents();
            ob_end_clean();
            $s_res = str_replace('VALUES', 'VALUES' . PHP_EOL, $s_res);
            $s_res = str_replace('),', '),' . PHP_EOL, $s_res);
            $s_res = str_replace(');', ');' . PHP_EOL, $s_res);

            fputs($fp, $s_res);
            ob_start();
        }

        $dets->siguiente();
    }

    if ($cont < $limite_ins && $cont > 0) {
        echo $sql_ins_plantilla . $sql_ins . ";";
    }

    $s_res = ob_get_contents();
    ob_end_clean();

    $s_res = str_replace('VALUES', 'VALUES' . PHP_EOL, $s_res);
    $s_res = str_replace('),', '),' . PHP_EOL, $s_res);

    fputs($fp, $s_res);
    fputs($fp, "-- " . $s_cmps_ids);
    fclose($fp);
	
	// return $arc_bck;
	return true;
}

function purgar_ceros($ele) {
    return $ele > 0;
}

function cabecera_comprobante($data) {

    $data = (object) $data;
    $venta = $data->venta;

    $cmp_ajuste = FUNCIONES::objeto_bd_sql("select * from con_comprobante 
        where cmp_tabla='venta_ajuste_saldos' and cmp_tabla_id='$venta->ven_id' 
            and cmp_eliminado='No'");

    $comprobante = new stdClass();
    if ($cmp_ajuste) {
        $ges_id = $data->ges_id;
        $glosa = $data->glosa . "(Venta $venta->ven_id)";

        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $cmp_ajuste->cmp_mon_id;
        $comprobante->nro_documento = $cmp_ajuste->cmp_nro_documento;
        $comprobante->fecha = $cmp_ajuste->cmp_fecha;
        $comprobante->ges_id = $cmp_ajuste->cmp_ges_id;
        $comprobante->peri_id = $cmp_ajuste->cmp_peri_id;
        $comprobante->forma_pago = "Efectivo";
        $comprobante->ban_id = $cmp_ajuste->cmp_ban_id;
        $comprobante->ban_char = $cmp_ajuste->cmp_ban_char;
        $comprobante->ban_nro = $cmp_ajuste->cmp_ban_nro;
        $comprobante->glosa = $cmp_ajuste->cmp_glosa;
        $comprobante->referido = $cmp_ajuste->cmp_referido;
        $comprobante->tabla = $cmp_ajuste->cmp_tabla;
        $comprobante->tabla_id = $cmp_ajuste->cmp_tabla_id;
        $comprobante->cmp_id = $cmp_ajuste->cmp_id;
        $comprobante->usu_per_id = $cmp_ajuste->cmp_usu_id;
        $comprobante->usu_id = $cmp_ajuste->cmp_usu_cre;
    } else {
        $ges_id = $data->ges_id;
        $glosa = $data->glosa . "(Venta $venta->ven_id)";

        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $venta->ven_moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = "Efectivo";
        $comprobante->ban_id = 0;
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_ajuste_saldos";
        $comprobante->tabla_id = $venta->ven_id;
    }

    return $comprobante;
}

function crear_cmp_ajuste_migracion_cdd($filtro = '') {

    $fecha_cmp = "2015-01-02";
    $ges_id = FUNCIONES::atributo_bd_sql("select ges_id as campo from con_gestion 
        where ges_fecha_ini<='$fecha_cmp' and ges_fecha_fin>='$fecha_cmp'
            and ges_eliminado='No'") * 1;
    $glosa = "Ajuste de Saldos de Venta y Saldos de Costos - Migracion Lujan, Norte y Bisito al 31/12/2014";

    $arr_cuentas_por_urb = array();

    $sql_urbs = "select * from urbanizacion where urb_id in(2,3,4)";
    $urbs = FUNCIONES::lista_bd_sql($sql_urbs);

    foreach ($urbs as $urb) {
        $arr_det = array(
            'cue_por_cobrar' => array(
                1 => $urb->urb_cta_por_cobrar_bs,
                2 => $urb->urb_cta_por_cobrar_usd
            ),
            'cue_costo_diferido' => $urb->urb_costo_diferido,
            'cue_ing_diferido' => $urb->urb_ing_diferido,
            'cue_inv_terrenos' => $urb->urb_inv_terrenos,
        );
        $arr_cuentas_por_urb[$urb->urb_id] = $arr_det;
    }

    // $filtro_venta = '';
    // if ($ven_id * 1 > 0) {
    // $filtro_venta = " and ven_id='$ven_id'";
    // }

    $sql_ventas = "select * from venta 
        inner join rep_saldos_venta_cdd rsvc on(ven_id=rsvc.venta)
        inner join urbanizacion on (ven_urb_id=urb_id)
        where 1 $filtro";
    $ventas = FUNCIONES::objetos_bd_sql($sql_ventas);
    $conec = new ADO();

    $arr_contenido = array();

    $arc_ins = generar_script_sql("migracion_cdd", $arr_contenido);
    $limit_arr = 500;
    $cont = 0;

    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();

        $data = array(
            'venta' => $venta,
            'ges_id' => $ges_id,
            'glosa' => $glosa,
            'fecha' => $fecha_cmp,
            'interno' => FUNCIONES::interno_nombre($venta->ven_int_id),
        );

        $comprobante = cabecera_comprobante($data);

        ob_start();
        if ($comprobante->cmp_id) {

            $datos_detalle = array(
                'cuentas' => $arr_cuentas_por_urb,
                'saldo_venta' => $venta->saldo_venta,
                'saldo_costo' => $venta->saldo_costo,
                'glosa' => $glosa,
                'urbanizacion' => $venta,
                'gestion' => $ges_id,
                'venta' => $venta
            );

            $detalles = detalles_comprobante($datos_detalle);
            $comprobante->detalles = $detalles;
            COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
        } else {

            $comprobante->usu_per_id = 1;
            $comprobante->usu_id = 'admin';
            COMPROBANTES::registrar_comprobante($comprobante, $conec, FALSE);
        }

        $contenido = ob_get_contents();
        ob_end_clean();
        $contenido = str_replace('<p>', '', $contenido);

        if ($comprobante->cmp_id) {
            $contenido = str_replace('</p>', PHP_EOL, $contenido);
        } else {
            $contenido = str_replace(');</p>', ");" . PHP_EOL, $contenido);
        }
        $arr_contenido[] = $contenido;
        $cont++;

        if ($cont == $limit_arr) {
            $cont = 0;
            escribir_en_archivo($arc_ins, $arr_contenido);
            $arr_contenido = array();
        }
        $ventas->siguiente();
    }

    if ($cont > 0 && $cont < $limit_arr) {
        $cont = 0;
        escribir_en_archivo($arc_ins, $arr_contenido);
        $arr_contenido = array();
    }
}

function crear_cmp_ajuste_migracion_demazu($filtro = '') {

    $fecha_cmp = "2016-03-01";
    $ges_id = FUNCIONES::atributo_bd_sql("select ges_id as campo from con_gestion 
        where ges_fecha_ini<='$fecha_cmp' and ges_fecha_fin>='$fecha_cmp'
            and ges_eliminado='No'") * 1;
    $glosa = "Ajuste de Saldos de Venta y Saldos de Costos - Migracion Demazu al 01/03/2016";

    $arr_cuentas_por_urb = array();

    $sql_urbs = "select * from urbanizacion where urb_id in(7,8,9,10)";
    $urbs = FUNCIONES::lista_bd_sql($sql_urbs);

    foreach ($urbs as $urb) {
        $arr_det = array(
            'cue_por_cobrar' => array(
                1 => $urb->urb_cta_por_cobrar_bs,
                2 => $urb->urb_cta_por_cobrar_usd
            ),
            'cue_costo_diferido' => $urb->urb_costo_diferido,
            'cue_ing_diferido' => $urb->urb_ing_diferido,
            'cue_inv_terrenos' => $urb->urb_inv_terrenos,
        );
        $arr_cuentas_por_urb[$urb->urb_id] = $arr_det;
    }

    // $filtro_venta = '';
    // if ($ven_id * 1 > 0) {
    // $filtro_venta = " and ven_id='$ven_id'";
    // }

    $sql_ventas = "select * from venta 
        inner join rep_saldos_venta_demazu rsvc on(ven_id=rsvc.venta)
        inner join urbanizacion on (ven_urb_id=urb_id)
        where 1 $filtro";
    $ventas = FUNCIONES::objetos_bd_sql($sql_ventas);
    $conec = new ADO();

    $arr_contenido = array();

    $arc_ins = generar_script_sql("migracion_demazu", $arr_contenido);
    $limit_arr = 500;
    $cont = 0;

    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();

        $data = array(
            'venta' => $venta,
            'ges_id' => $ges_id,
            'glosa' => $glosa,
            'fecha' => $fecha_cmp,
            'interno' => FUNCIONES::interno_nombre($venta->ven_int_id),
        );

        $comprobante = cabecera_comprobante($data);

        ob_start();
        if ($comprobante->cmp_id) {

            $datos_detalle = array(
                'cuentas' => $arr_cuentas_por_urb,
                'saldo_venta' => $venta->saldo_venta,
                'saldo_costo' => $venta->saldo_costo,
                'glosa' => $glosa,
                'urbanizacion' => $venta,
                'gestion' => $ges_id,
                'venta' => $venta
            );

            $detalles = detalles_comprobante($datos_detalle);
            $comprobante->detalles = $detalles;
            COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
        } else {

            $comprobante->usu_per_id = 1;
            $comprobante->usu_id = 'admin';
            COMPROBANTES::registrar_comprobante($comprobante, $conec, FALSE);
        }

        $contenido = ob_get_contents();
        ob_end_clean();
        $contenido = str_replace('<p>', '', $contenido);

        if ($comprobante->cmp_id) {
            $contenido = str_replace('</p>', PHP_EOL, $contenido);
        } else {
            $contenido = str_replace(');</p>', ");" . PHP_EOL, $contenido);
        }
        $arr_contenido[] = $contenido;
        $cont++;

        if ($cont == $limit_arr) {
            $cont = 0;
            escribir_en_archivo($arc_ins, $arr_contenido);
            $arr_contenido = array();
        }
        $ventas->siguiente();
    }

    if ($cont > 0 && $cont < $limit_arr) {
        $cont = 0;
        escribir_en_archivo($arc_ins, $arr_contenido);
        $arr_contenido = array();
    }
}

function crear_cmp_ajuste_migracion_vexsa($filtro = '') {

    $fecha_cmp = "2017-04-22";
    $ges_id = FUNCIONES::atributo_bd_sql("select ges_id as campo from con_gestion 
        where ges_fecha_ini<='$fecha_cmp' and ges_fecha_fin>='$fecha_cmp'
            and ges_eliminado='No'") * 1;
    $glosa = "Ajuste de Saldos de Venta y Saldos de Costos - Migracion Vexsa al 22/04/2017";

    $arr_cuentas_por_urb = array();

    $sql_urbs = "select * from urbanizacion where urb_id in(2,3,11,13)";
    $urbs = FUNCIONES::lista_bd_sql($sql_urbs);

    foreach ($urbs as $urb) {
        $arr_det = array(
            'cue_por_cobrar' => array(
                1 => $urb->urb_cta_por_cobrar_bs,
                2 => $urb->urb_cta_por_cobrar_usd
            ),
            'cue_costo_diferido' => $urb->urb_costo_diferido,
            'cue_ing_diferido' => $urb->urb_ing_diferido,
            'cue_inv_terrenos' => $urb->urb_inv_terrenos,
        );
        $arr_cuentas_por_urb[$urb->urb_id] = $arr_det;
    }

    // $filtro_venta = '';
    // if ($ven_id * 1 > 0) {
    // $filtro_venta = " and ven_id='$ven_id'";
    // }

    $sql_ventas = "select * from venta 
        inner join rep_saldos_venta_vexsa rsvc on(ven_id=rsvc.venta)
        inner join urbanizacion on (ven_urb_id=urb_id)
        where 1 $filtro";
    $ventas = FUNCIONES::objetos_bd_sql($sql_ventas);
    $conec = new ADO();

    $arr_contenido = array();

    $arc_ins = generar_script_sql("migracion_vexsa", $arr_contenido);
    $limit_arr = 500;
    $cont = 0;

    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();

        $data = array(
            'venta' => $venta,
            'ges_id' => $ges_id,
            'glosa' => $glosa,
            'fecha' => $fecha_cmp,
            'interno' => FUNCIONES::interno_nombre($venta->ven_int_id),
        );

        $comprobante = cabecera_comprobante($data);

        ob_start();
        if ($comprobante->cmp_id) {

            $datos_detalle = array(
                'cuentas' => $arr_cuentas_por_urb,
                'saldo_venta' => $venta->saldo_venta,
                'saldo_costo' => $venta->saldo_costo,
                'glosa' => $glosa,
                'urbanizacion' => $venta,
                'gestion' => $ges_id,
                'venta' => $venta
            );

            $detalles = detalles_comprobante($datos_detalle);
            $comprobante->detalles = $detalles;
            COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
        } else {

            $comprobante->usu_per_id = 1;
            $comprobante->usu_id = 'admin';
            COMPROBANTES::registrar_comprobante($comprobante, $conec, FALSE);
        }

        $contenido = ob_get_contents();
        ob_end_clean();
        $contenido = str_replace('<p>', '', $contenido);

        if ($comprobante->cmp_id) {
            $contenido = str_replace('</p>', PHP_EOL, $contenido);
        } else {
            $contenido = str_replace(');</p>', ");" . PHP_EOL, $contenido);
        }
        $arr_contenido[] = $contenido;
        $cont++;

        if ($cont == $limit_arr) {
            $cont = 0;
            escribir_en_archivo($arc_ins, $arr_contenido);
            $arr_contenido = array();
        }
        $ventas->siguiente();
    }

    if ($cont > 0 && $cont < $limit_arr) {
        $cont = 0;
        escribir_en_archivo($arc_ins, $arr_contenido);
        $arr_contenido = array();
    }
}

function detalles_comprobante($datos) {

    $datos = (object) $datos;
    $arr_cuentas = $datos->cuentas;
    $saldo_venta = $datos->saldo_venta;
    $saldo_costo = $datos->saldo_costo;
    $urb = $datos->urbanizacion;
    $ges_id = $datos->gestion;
    $venta = $datos->venta;
    $glosa = $datos->glosa . "(Venta $venta->ven_id)";

    $codigo_por_cobrar = $arr_cuentas[$urb->urb_id]['cue_por_cobrar'][$venta->ven_moneda];
    $codigo_ing_diferido = $arr_cuentas[$urb->urb_id]['cue_ing_diferido'];
    $codigo_costo_diferido = $arr_cuentas[$urb->urb_id]['cue_costo_diferido'];
    $codigo_inv_terrenos = $arr_cuentas[$urb->urb_id]['cue_inv_terrenos'];

    $detalles = array();

    if ($saldo_venta > 0) {
        $detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_por_cobrar),
            "debe" => $saldo_venta,
            "haber" => 0,
            "glosa" => $glosa,
            "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urb->une_id
        );

        $detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_ing_diferido),
            "debe" => 0,
            "haber" => $saldo_venta,
            "glosa" => $glosa,
            "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urb->une_id
        );
    }

    if ($saldo_costo > 0) {

        $detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_costo_diferido),
            "debe" => $saldo_costo,
            "haber" => 0,
            "glosa" => $glosa,
            "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urb->une_id
        );

        $detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_inv_terrenos),
            "debe" => 0,
            "haber" => $saldo_costo,
            "glosa" => $glosa,
            "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urb->une_id
        );
    }

    return $detalles;
}

function generar_script_venta_pagos() {
    echo "entrando generar_script_venta_pagos...";

    $sql_pagos = "select vpag_id,cmp_ges_id,cmp_id,cmp_tabla,cmp_tabla_id,ven_id,
    ven_moneda,ven_can_codigo,urb_cta_por_cobrar_bs,urb_ing_por_venta_bs,
    urb_cta_por_cobrar_usd,urb_ing_por_venta_usd,urb_ing_diferido,urb_costo,
    urb_costo_diferido,urb_inv_terrenos  
    from venta_pago
    inner join venta on(vpag_ven_id=ven_id)
    inner join urbanizacion on(ven_urb_id=urb_id)
    inner join con_comprobante on(
       cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id and cmp_eliminado='No'
    )
    where 1";

    $partes = 15;
    $l_pagos = FUNCIONES::objetos_bd_sql($sql_pagos);
    $cant_pagos = $l_pagos->get_num_registros();

    $cuantas = intval(ceil($cant_pagos / $partes));

    $li = 0;
    $arr_segmentos = array();
    for ($i = 1; $i <= $partes; $i++) {
        echo "<p>ITERANDO:$i.</p>";

        $sql_maestra = "select min(vpag_id)as minimo,max(vpag_id)as maximo from (select * from venta_pago
        inner join venta on(vpag_ven_id=ven_id)
        inner join urbanizacion on(ven_urb_id=urb_id)
        inner join con_comprobante on(
           cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id and cmp_eliminado='No'
        )
        where 1 order by vpag_id asc limit $li,$cuantas)tmp";

        $obj = FUNCIONES::objeto_bd_sql($sql_maestra);
        $ele = new stdClass();
        $ele->min = $obj->minimo;
        $ele->max = $obj->maximo;
        $ele->tab_tmp = "venta_pago_tmp_{$ele->min}_{$ele->max}";
        $arr_segmentos[] = $ele;
        $li = $li + $cuantas;

//        $sql_tmp = "create temporary table {$ele->tab_tmp} as " . $sql_pagos . 
//                " and vpag_id>='{$ele->min}' and vpag_id<='{$ele->max}'";
//                
//        echo "<p>sql_tmp:$sql_tmp</p>";
//                
//        FUNCIONES::bd_query($sql_tmp);
    }

    return $arr_segmentos;
}

function foo($conf) {

    FUNCIONES::print_pre($conf);

    $sql_tmp = "select * from $conf->tab_tmp";

    $regs = FUNCIONES::objetos_bd_sql($sql_tmp);

    echo "<p>{$regs->get_num_registros()}</p>";
}

function crear_cuenta_analitica($ven_id) {

    $sql = "select ifnull(group_concat(ges_id),'')as campo from con_gestion where ges_eliminado='No' 
	order by ges_id asc";
    $s_gestiones = FUNCIONES::atributo_bd_sql($sql);

    if ($s_gestiones == '') {
        return false;
    }

    $arr_gestiones = explode(',', $s_gestiones);
    $arr_roots = array();

    foreach ($arr_gestiones as $ges_id) {
        $sql_tit = "select can_id as campo from con_cuenta_ca where can_ges_id='$ges_id' 
		and can_tipo='Titulo'";
        $can_id = FUNCIONES::atributo_bd_sql($sql_tit);
        $arr_roots[$ges_id] = $can_id;
    }

    $sql_ins_plan = "insert into con_cuenta_ca(
		can_ges_id,can_codigo,can_descripcion,can_tipo,can_padre_id,can_eliminado,can_tree_level
	)values";

    $long_max = 10;
    $char_pad = '0';
    $tipo = STR_PAD_LEFT;
    $prefijo = "01.";

    $sql_ventas = "select * from venta where ven_id = '$ven_id'";
    $venta = FUNCIONES::objeto_bd_sql($sql_ventas);

    $nro = $venta->ven_id;
    $codigo = $prefijo . str_pad($nro, $long_max, $char_pad, $tipo);
    $descripcion = "VENTA $nro";

    foreach ($arr_gestiones as $ges_id) {
        $padre_id = $arr_roots[$ges_id];
        $sql_ins = $sql_ins_plan . "('$ges_id','$codigo','$descripcion','Movimiento','$padre_id','No','2')";
        echo "<p>$sql_ins;</p>";
    }

    $sql_upd_venta = "update venta set ven_can_codigo='$codigo' where ven_id='$venta->ven_id'";
    echo "<p>$sql_upd_venta;</p>";
}

function gestionar_descuentos_incrementos_cdd($filtro = '') {
    $fecha_limite = "2014-12-31";
    $sql = "select * from venta_pago 
        inner join venta on(vpag_ven_id=ven_id)
        where vpag_estado='Activo'
	and (vpag_capital_desc>0 or vpag_capital_inc>0)
        and ven_urb_id in(2, 3, 4)
        and vpag_fecha_pago > '2014-12-31'
        and ven_numero is not null and ven_numero != '' and ven_numero not like 'NET-%'
        and ven_estado != 'Anulado' $filtro";

    $pagos = FUNCIONES::objetos_bd_sql($sql);

    $arr_cmps_resp = array();
    $conec = new ADO();

	$tiempo = date('dmY_His');
    $arc_upd = "actualizacion_desc_inc_pagos_cdd_$tiempo.sql";
    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pag = $pagos->get_objeto();

        // $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_pago' 
		// and cmp_tabla_id='$pag->vpag_id' and cmp_eliminado='No'");

        // if ($pag->vpag_monto > 0) {
		if (false) {
            if ($cmp) {
                $s_updates = agregar_detalles_desc_inc($pag, $cmp);
                $arr_cmps_resp[] = $cmp->cmp_id;
                $s_updates = str_replace('<p>', '', $s_updates);
                $s_updates = str_replace('</p>', PHP_EOL, $s_updates);
                fputs($fp, $s_updates);
            }
        } else {
            $s_updates = gestionar_cmp_descuento_incremento($pag);
			$s_updates = str_replace('<p>', '', $s_updates);
			$s_updates = str_replace('</p>', PHP_EOL, $s_updates);
			fputs($fp, $s_updates);
        }

        $pagos->siguiente();
    }

    fclose($fp);

    $s_cmps_ids = implode(',', $arr_cmps_resp);
    $ele_info = new stdClass();
    $ele_info->origen = "desc_inc_cdd";
    respaldar_cmp($s_cmps_ids, $ele_info, $tab_dmp_id);
}

function gestionar_descuentos_incrementos_demazu($filtro = '') {
    $fecha_limite = "2016-03-01";
    $sql = "select * from venta_pago 
        inner join venta on(vpag_ven_id=ven_id)
        where vpag_estado='Activo'
	and (vpag_capital_desc>0 or vpag_capital_inc>0)
        and ven_urb_id in(7,8,9,10)
        and vpag_fecha_pago > '2016-03-01'
        and ven_numero is not null and ven_numero != ''
        and ven_estado != 'Anulado' $filtro";

    $pagos = FUNCIONES::objetos_bd_sql($sql);

    $arr_cmps_resp = array();
    $conec = new ADO();

	$tiempo = date('dmY_His');
    $arc_upd = "actualizacion_desc_inc_pagos_demazu_$tiempo.sql";
    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pag = $pagos->get_objeto();

        // $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_pago' 
		// and cmp_tabla_id='$pag->vpag_id' and cmp_eliminado='No'");

        // if ($pag->vpag_monto > 0) {
		if (false) {
            if ($cmp) {
                $s_updates = agregar_detalles_desc_inc($pag, $cmp);
                $arr_cmps_resp[] = $cmp->cmp_id;
                $s_updates = str_replace('<p>', '', $s_updates);
                $s_updates = str_replace('</p>', PHP_EOL, $s_updates);
                fputs($fp, $s_updates);
            }
        } else {
            $s_updates = gestionar_cmp_descuento_incremento($pag);
			$s_updates = str_replace('<p>', '', $s_updates);
			$s_updates = str_replace('</p>', PHP_EOL, $s_updates);
			fputs($fp, $s_updates);
        }

        $pagos->siguiente();
    }

    fclose($fp);

    $s_cmps_ids = implode(',', $arr_cmps_resp);
    $ele_info = new stdClass();
    $ele_info->origen = "desc_inc_demazu";
    respaldar_cmp($s_cmps_ids, $ele_info, $tab_dmp_id);
}

function gestionar_descuentos_incrementos_vexsa($filtro = '') {
    $fecha_limite = "2017-04-22";
    $sql = "select * from venta_pago 
        inner join venta on(vpag_ven_id=ven_id)
        where vpag_estado='Activo'
	and (vpag_capital_desc>0 or vpag_capital_inc>0)        
        and vpag_fecha_pago > '2017-04-22'
        and ven_numero like 'NET-%'
        and ven_estado != 'Anulado' $filtro";

    $pagos = FUNCIONES::objetos_bd_sql($sql);

    $arr_cmps_resp = array();
    $conec = new ADO();

	$tiempo = date('dmY_His');
    $arc_upd = "actualizacion_desc_inc_pagos_vexsa_$tiempo.sql";
    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pag = $pagos->get_objeto();

        // $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_pago' 
		// and cmp_tabla_id='$pag->vpag_id' and cmp_eliminado='No'");

        // if ($pag->vpag_monto > 0) {
		if (false) {
            if ($cmp) {
                $s_updates = agregar_detalles_desc_inc($pag, $cmp);
                $arr_cmps_resp[] = $cmp->cmp_id;
                $s_updates = str_replace('<p>', '', $s_updates);
                $s_updates = str_replace('</p>', PHP_EOL, $s_updates);
                fputs($fp, $s_updates);
            }
        } else {            
			$s_updates = gestionar_cmp_descuento_incremento($pag);
			$s_updates = str_replace('<p>', '', $s_updates);
			$s_updates = str_replace('</p>', PHP_EOL, $s_updates);
			fputs($fp, $s_updates);
        }

        $pagos->siguiente();
    }

    fclose($fp);

    $s_cmps_ids = implode(',', $arr_cmps_resp);
    $ele_info = new stdClass();
    $ele_info->origen = "desc_inc_vexsa";
    respaldar_cmp($s_cmps_ids, $ele_info, $tab_dmp_id);
}

function gestionar_descuentos_incrementos_demas($filtro = '') {
    $sql = "select * from venta_pago inner join venta on(vpag_ven_id=ven_id) 
        where vpag_estado='Activo' and (vpag_capital_desc>0 or vpag_capital_inc>0) 
        and (ven_numero is null or ven_numero = '') and ven_estado != 'Anulado' $filtro";
		
	// echo "<p>$sql;</p>";
		
    $pagos = FUNCIONES::objetos_bd_sql($sql);

    $arr_cmps_resp = array();
    $conec = new ADO();

	$tiempo = date('dmY_His');
    $arc_upd = "actualizacion_desc_inc_pagos_demas_ventas_$tiempo.sql";
    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pag = $pagos->get_objeto();

        // $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_pago' 
		// and cmp_tabla_id='$pag->vpag_id' and cmp_eliminado='No'");

        // if ($pag->vpag_monto > 0) {
		if (false) {
            if ($cmp) {
                $s_updates = agregar_detalles_desc_inc($pag, $cmp);
                $arr_cmps_resp[] = $cmp->cmp_id;
				$s_updates = str_replace('<p>', '', $s_updates);
                $s_updates = str_replace('</p>', PHP_EOL, $s_updates);
                fputs($fp, $s_updates);
            }
        } else {
            $s_updates = gestionar_cmp_descuento_incremento($pag);
			$s_updates = str_replace('<p>', '', $s_updates);
			$s_updates = str_replace('</p>', PHP_EOL, $s_updates);
			fputs($fp, $s_updates);
        }

        $pagos->siguiente();
    }

    fclose($fp);

    $s_cmps_ids = implode(',', $arr_cmps_resp);
    $ele_info = new stdClass();
    $ele_info->origen = "desc_inc_demas_ventas";
    respaldar_cmp($s_cmps_ids, $ele_info, $tab_dmp_id);
}

function gestionar_descuentos_incrementos_individual($filtro = '') {
    $sql = "select * from venta_pago inner join venta on(vpag_ven_id=ven_id) 
        where vpag_estado='Activo' and (vpag_capital_desc>0 or vpag_capital_inc>0) 
        $filtro";
		
	// echo "<p>$sql;</p>";
		
    $pagos = FUNCIONES::objetos_bd_sql($sql);

    $arr_cmps_resp = array();
    $conec = new ADO();

	// $tiempo = date('dmY_His');
    // $arc_upd = "actualizacion_desc_inc_pagos_demas_ventas_$tiempo.sql";
    // $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    // $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    // $tab_dmp_id = ADO::$insert_id;

    // echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    // $fp = fopen("imp_exp/" . $arc_upd, 'w');

    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pag = $pagos->get_objeto();

		if (false) {
            if ($cmp) {
                $s_updates = agregar_detalles_desc_inc($pag, $cmp);
                $arr_cmps_resp[] = $cmp->cmp_id;
				// $s_updates = str_replace('<p>', '', $s_updates);
                // $s_updates = str_replace('</p>', PHP_EOL, $s_updates);
                // fputs($fp, $s_updates);
				
				echo $s_updates;
            }
        } else {
            $s_updates = gestionar_cmp_descuento_incremento($pag);
			// $s_updates = str_replace('<p>', '', $s_updates);
			// $s_updates = str_replace('</p>', PHP_EOL, $s_updates);
			// fputs($fp, $s_updates);
			
			echo $s_updates;
        }

        $pagos->siguiente();
    }

    // fclose($fp);

    // $s_cmps_ids = implode(',', $arr_cmps_resp);
    // $ele_info = new stdClass();
    // $ele_info->origen = "desc_inc_demas_ventas";
    // respaldar_cmp($s_cmps_ids, $ele_info, $tab_dmp_id);
}

function agregar_detalles_desc_inc($pag, $cmp) {

    if ($cmp == NULL) {
        return "-- Nada para agregar a pago $pag->vpag_id.";
    }

    $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pag->vpag_ven_id'");

    $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

    $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp->cmp_fecha'");
    $cmp_id = $cmp->cmp_id;
    $cde_cmp_id = $cmp_id;
    $cde_eliminado = "No";

    $ges_id = $cmp->cmp_ges_id;

    $index = FUNCIONES::atributo_bd_sql("
    select (max(cde_secuencia)+1)as campo from con_comprobante_detalle
    where cde_cmp_id=$cmp_id
    ");

    $detalles = array();

    $tc = 1;
    for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
        $cambio = $cambios->get_objeto();
        if ($cmp->cmp_mon_id == $cambio->tca_mon_id) {
            $tc = $cambio->tca_valor;
        }
        $cambios->siguiente();
    }

    if ($venta->ven_moneda == 1) {
        $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_bs;
        $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_bs;
        $codigo_incremento_capital = $urbanizacion->urb_cue_incremento_capital_bs;
        $codigo_descuento_capital = $urbanizacion->urb_cue_descuento_capital_bs;
    } else {
        $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_usd;
        $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_usd;
        $codigo_incremento_capital = $urbanizacion->urb_cue_incremento_capital_usd;
        $codigo_descuento_capital = $urbanizacion->urb_cue_descuento_capital_usd;
    }

    $codigo_ingreso_diferido = $urbanizacion->urb_ing_diferido;
    $codigo_costo_diferido = $urbanizacion->urb_costo_diferido;

    if ($pag->vpag_capital_inc > 0) {

        $detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_cta_por_cobrar),
            "debe" => $pag->vpag_capital_inc,
            "haber" => 0,
            "glosa" => $cmp->cmp_glosa,
            "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urbanizacion->urb_une_id
        );

        $detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_ingreso_diferido),
            "debe" => 0,
            "haber" => $pag->vpag_capital_inc,
            "glosa" => $cmp->cmp_glosa,
            "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urbanizacion->urb_une_id
        );
    }

    if ($pag->vpag_capital_desc > 0) {

        $detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_ingreso_diferido),
            "debe" => $pag->vpag_capital_desc,
            "haber" => 0,
            "glosa" => $cmp->cmp_glosa,
            "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urbanizacion->urb_une_id
        );

        $detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_cta_por_cobrar),
            "debe" => 0,
            "haber" => $pag->vpag_capital_desc,
            "glosa" => $cmp->cmp_glosa,
            "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urbanizacion->urb_une_id
        );

        $detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_descuento_capital),
            "debe" => $pag->vpag_capital_desc,
            "haber" => 0,
            "glosa" => $cmp->cmp_glosa,
            "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urbanizacion->urb_une_id
        );

        $detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_ingreso_por_venta),
            "debe" => 0,
            "haber" => $pag->vpag_capital_desc,
            "glosa" => $cmp->cmp_glosa,
            "ca" => 0,
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urbanizacion->urb_une_id
        );
    }

    ob_start();
    foreach ($detalles as $cmp_detalle) {
        $detalle = (object) $cmp_detalle;
        $cde_secuencia = $index;
        $cde_can_id = (isset($detalle->ca) && $detalle->ca != "") ? $detalle->ca : 0;
        $cde_cco_id = (isset($detalle->cc) && $detalle->cc != "") ? $detalle->cc : 0;
        $cde_cfl_id = (isset($detalle->cf) && $detalle->cf != "") ? $detalle->cf : 0;
        $cde_cue_id = (isset($detalle->cuen) && $detalle->cuen != "") ? $detalle->cuen : 0;
        $cde_glosa = $detalle->glosa;
        $debe = $detalle->debe;
        $haber = $detalle->haber;
        $int_id = 0;
        $doc_id = 0;
        $une_id = 0;
        $fpago = 'Efectivo';
        $ban_nombre = '';
        $descripcion = '';
        $ban_nro = '';
        if ($detalle->int_id)
            $int_id = $detalle->int_id;
        if ($detalle->doc_id)
            $doc_id = $detalle->doc_id;
        if ($detalle->une_id)
            $une_id = $detalle->une_id;
        if ($detalle->fpago)
            $fpago = $detalle->fpago;
        if ($detalle->ban_nombre)
            $ban_nombre = $detalle->ban_nombre;
        if ($detalle->ban_nro)
            $ban_nro = $detalle->ban_nro;
        if ($detalle->descripcion)
            $descripcion = $detalle->descripcion;
        $valor = 0;
        if ($debe != "") {
            $valor = floatval($debe) * 1;
        }
        if ($haber != "") {
            $valor = floatval($haber) * (-1);
        }
        $val_bol = $valor * $tc;
        $sql = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values('$cde_cmp_id', 1, '$cde_secuencia', '$cde_can_id', '$cde_cco_id', '$cde_cfl_id', '$cde_cue_id', '$val_bol','$cde_glosa', '$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id')";
//            echo $sql."<br>";
        echo "<p>$sql;</p>";

        $cambios->reset();
        for ($i = 0; $i < $cambios->get_num_registros(); $i++) {

            $cambio = $cambios->get_objeto();
            $cde_mon_id = $cambio->tca_mon_id;

            $cde_valor = $val_bol / $cambio->tca_valor;

            $sql = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values('$cde_cmp_id', '$cde_mon_id', '$cde_secuencia', '$cde_can_id', '$cde_cco_id', '$cde_cfl_id', '$cde_cue_id', '$cde_valor','$cde_glosa', '$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id')";
//                echo $cde_mon_id." - ".$sql."<br>";
//            $conec->ejecutar($sql,false,false);
            echo "<p>$sql;</p>";
            $cambios->siguiente();
        }
        $index++;
    }

    $contents = ob_get_contents();
    ob_end_clean();

    return $contents;
}

function gestionar_cmp_descuento_incremento($pag) {

    $conec = NULL;

    $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pag->vpag_ven_id'");
    $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

    $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante 
        where cmp_tabla='venta_pago_desc_inc' and cmp_tabla_id='$pag->vpag_id' and cmp_eliminado='No'");

    $ges_id = FUNCIONES::atributo_bd_sql("select ges_id as campo from con_gestion 
    where ges_fecha_ini<='$pag->vpag_fecha_pago' 
    and ges_fecha_fin>='$pag->vpag_fecha_pago'
    and ges_eliminado='No'") * 1;
	
	$saldo_costo_por_pagar = ($venta->ven_costo - $venta->ven_costo_cub);
	$saldo_venta = $venta->ven_monto_efectivo;
	$costo_por_descuento = $pag->vpag_capital_desc * ($saldo_costo_por_pagar / $saldo_venta);
	
	

	ob_start();
    if ($cmp == null) {
        $interno = FUNCIONES::interno_nombre($venta->ven_int_id);
        $glosa = "Ajuste de Capital Adeudado en la Venta Nro. $venta->ven_id - $venta->ven_concepto - $interno";

        $datos = array(
            'urbanizacion' => $urbanizacion,
            'venta' => $venta,
            'pago' => $pag,
            'glosa' => $glosa,
            'ges_id' => $ges_id,
            'moneda' => $venta->ven_moneda,
            'fecha' => $pag->vpag_fecha_pago,
            'interno' => $interno,
            'tabla_id' => $pag->vpag_id,
            'origen' => 'ajustes',
            'con_detalles' => FALSE,
			'costo' => $costo_por_descuento,
        );

        $comprobante = MODELO_COMPROBANTE::pago_descuento_incremento($datos);
        COMPROBANTES::registrar_comprobante($comprobante);
    } else {
        $datos = array(
            'urbanizacion' => $urbanizacion,
            'venta' => $venta,
            'pago' => $pag,
            'glosa' => $cmp->cmp_glosa,
            'ges_id' => $cmp->cmp_ges_id,
            'moneda' => $cmp->cmp_mon_id,
            'fecha' => $cmp->cmp_fecha,
            'interno' => $cmp->cmp_referido,
            'tabla_id' => $cmp->cmp_tabla_id,
            'origen' => 'ajustes',
            'con_detalles' => TRUE,
			'costo' => $costo_por_descuento,
        );
        
        $comprobante = MODELO_COMPROBANTE::pago_descuento_incremento($datos);
		$comprobante->cmp_id = $cmp->cmp_id;
        $comprobante->usu_per_id = $cmp->cmp_usu_id;
        $comprobante->usu_id = $cmp->cmp_usu_cre;
        COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
    }
	
	$contenido = ob_get_contents();
	ob_end_clean();
	
	return $contenido;
}

function ajustar_cmps_venta_con_descuentos($filtro = ''){
	$conec = new ADO();

	$sql = "select * from venta 
	inner join con_comprobante on(cmp_tabla='venta' and cmp_tabla_id=ven_id and cmp_eliminado='No')
	where ven_estado!='Anulado' and ven_decuento > 0 $filtro";
	$ventas = FUNCIONES::objetos_bd_sql($sql);
	
	$tiempo = date('dmY_His');
    $arc_upd = "actualizacion_descuento_cmp_venta_$tiempo.sql";
    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;
	
	$arr_cmps_resp = array();

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');
	
	ob_start();
	for ($i = 0; $i < $ventas->get_num_registros(); $i++) {	
		
		$venta = $ventas->get_objeto();
		$arr_cmps_resp[] = $venta->cmp_id;
		$urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
		
		$costo_producto = $venta->ven_costo_producto;
		
		$monto = $venta->ven_monto;
		$precio_prod = $venta->ven_monto_producto;
		
		$total_venta = $monto + $precio_prod;
		$prorat_lote = ($monto / $total_venta);
		
		if ($precio_prod > 0) {
			$prorat_prod = ($precio_prod / $total_venta);
			$costo_prod_cub = $venta->ven_costo_producto_cub;
		}
		
		$data = array(
			'moneda' => $venta->ven_moneda,
			'ges_id' => $venta->cmp_ges_id,
			'fecha' => $venta->cmp_fecha,
			'glosa' => $venta->cmp_glosa,
			'interno' => $venta->cmp_referido,
			'tabla_id' => $venta->cmp_tabla_id,
			'urb' => $urb,
			'anticipo' => $venta->ven_res_anticipo,
			'saldo_efectivo' => $venta->ven_monto_efectivo,
			'monto_intercambio' => $venta->ven_monto_intercambio,
			'intercambio_ids' => $venta->ven_intercambio_ids,
			'intercambio_montos' => $venta->ven_intercambio_montos,
			'monto_pagar' => $venta->ven_monto_pagar,
			'costo' => $venta->ven_costo,
			'costo_cub' => $venta->ven_costo_cub,
			'monto_producto' => $precio_prod,
			'monto_venta' => $monto,
			'prorat_lote' => $prorat_lote,
			'prorat_producto' => $prorat_prod,
			'costo_producto' => $costo_producto,
			'costo_producto_cub' => $costo_prod_cub,
			'ven_can_codigo' => $venta->ven_can_codigo,
			'descuento' => $venta->ven_decuento,
		);	

		$comprobante = MODELO_COMPROBANTE::venta($data);
		$comprobante->usu_id = $venta->cmp_usu_cre;
		$comprobante->usu_per_id = $venta->cmp_usu_id;
		$comprobante->cmp_id = $venta->cmp_id;
		
		$conec = null;
		COMPROBANTES::modificar_comprobante($comprobante, $conec, false);
		
		$contenido = ob_get_contents();
		$contenido = str_replace('<p>', '', $contenido);
        $contenido = str_replace('</p>', PHP_EOL, $contenido);
		ob_end_clean();		
		fputs($fp, $contenido);
		
		ob_start();
		
		$ventas->siguiente();
	}
	
	$contenido = ob_get_contents();
	$contenido = str_replace('<p>', '', $contenido);
	$contenido = str_replace('</p>', PHP_EOL, $contenido);
	ob_end_clean();		
	fputs($fp, $contenido);

	$s_cmps_ids = implode(',', $arr_cmps_resp);
	$ele_info = new stdClass();
	$ele_info->origen = $arc_upd;	
	respaldar_cmp($s_cmps_ids, $ele_info, $tab_dmp_id);
}

function actualizar_costo_en_pagos_todos() {
	echo "(1)entrando a actualizar_costo_en_pagos_todos...<br/>";
	$partes = 5;
	
	$sql_pagos = "select count(vpag_id)as campo from venta_pago 
	inner join con_comprobante on(cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id and cmp_eliminado='No')
	inner join venta on(vpag_ven_id=ven_id)
	inner join urbanizacion on(ven_urb_id=urb_id)
	inner join pago_comprobante on(pcm_vpag_id=vpag_id and pcm_cmp_id=cmp_id and pcm_estado = 'Pendiente')
	where vpag_estado='Activo' 
	and ven_urb_id not in(5,6) 
	and vpag_capital>0 
	and (ven_costo - ven_costo_cub) > 0 ";
	
	echo "<p>$sql_pagos;</p>";
			
    $cant_pagos = FUNCIONES::atributo_bd_sql($sql_pagos) * 1;
	
	echo "(2)entrando a actualizar_costo_en_pagos_todos...<br/>";
	
	$cuantas = intval(ceil($cant_pagos / $partes));
	echo "<p>cuantas:$cuantas - cant_pagos:$cant_pagos - partes:$partes</p>";
	echo "(3)entrando a actualizar_costo_en_pagos_todos...<br/>";
	
	$li = 0;
    $arr_segmentos = array();
    for ($i = 1; $i <= $partes; $i++) {
        $sql_ven = "select min(vpag_id)as minima,max(vpag_id)as maxima from(select vpag_id from venta_pago 
		inner join con_comprobante on(cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id and cmp_eliminado='No')
		inner join venta on(vpag_ven_id=ven_id)
		inner join urbanizacion on(ven_urb_id=urb_id)
		inner join pago_comprobante on(pcm_vpag_id=vpag_id and pcm_cmp_id=cmp_id and pcm_estado = 'Pendiente')
		where vpag_estado='Activo' 
		and ven_urb_id not in(5,6) 
		and vpag_capital>0 
		and (ven_costo - ven_costo_cub) > 0      
        order by vpag_id asc limit $li,$cuantas)tmp";

        echo "<p>$sql_ven;</p>";

        $obj = FUNCIONES::objeto_bd_sql($sql_ven);
        $ele = new stdClass();
        $ele->min = $obj->minima;
        $ele->max = $obj->maxima;
        $arr_segmentos[] = $ele;
        $li = $li + $cuantas;
    }		
	echo "(4)entrando a actualizar_costo_en_pagos_todos...<br/>";

    FUNCIONES::print_pre($arr_segmentos);
	return false;
		
	foreach ($arr_segmentos as $ele) {
		$filtro = " and vpag_id >='$ele->min' and vpag_id <='$ele->max'";
		actualizar_costo_en_pagos($filtro);
	}
}

function actualizar_costo_en_pagos($filtro = '', $limit = '') {
	$lote_exec = "lote3";
	
	$dif_cambio_egr=  "6.3.1.01.1.04";
    $dif_cambio_ing=  "4.2.1.01.1.01";
	
	$sql = "select cmp_id,cmp_fecha,cmp_mon_id,cmp_ges_id,cmp_glosa,cmp_une_id,
	vpag_capital,vpag_id,
	ven_id,ven_costo,ven_costo_cub,ven_monto_efectivo,ven_can_codigo,
	urb_costo,urb_costo_diferido 
	from venta_pago 
	inner join con_comprobante on(cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id and cmp_eliminado='No')
	inner join venta on(vpag_ven_id=ven_id)
	inner join urbanizacion on(ven_urb_id=urb_id)
	inner join pago_comprobante on(pcm_vpag_id=vpag_id and pcm_cmp_id=cmp_id and pcm_estado = 'Pendiente' and pcm_lote_exec='$lote_exec')
	where vpag_estado='Activo' 
	and ven_urb_id not in(5,6) 
	and vpag_capital>0 
	and (ven_costo - ven_costo_cub) > 0		
	$filtro order by vpag_id asc $limit";
	
	echo "<p>$sql</p>";
	// return false;
	
	$pagos = FUNCIONES::objetos_bd_sql($sql);
		
	$time = date('dmY_His');
	$nom_archivo = "AJUSTE DE COSTOS EN PAGOS/actualizar_costo_en_pagos_$time.sql";
	$fp = fopen("imp_exp/" . $nom_archivo, 'w');
	fclose($fp);
	
	
	$ele_info = new stdClass();
	$ele_info->origen = "corregir_costos_en_pagos";
	$ele_info->tiempo = $time;
	$nom_archivo_resp = respaldar_cmp($s_cmps_ids, $ele_info, 0, 'AJUSTE DE COSTOS EN PAGOS');
	
	echo "<p>nom_archivo_resp:$nom_archivo_resp</p>";
	// return false;
	
	$arr_sent_sql = array();
	$arr_cmps_resp = array();
	$limit_ins = 100;
	
	$add = "";	
	for ($i = 0; $i < $pagos->get_num_registros(); $i++) {		
		
		// echo "<p>$i - $add</p>";
		$pago = $pagos->get_objeto();
		$venta = clone $pago;
		$urbanizacion = clone $pago;
		$cmp = clone $pago;
		$arr_cmps_resp[] = $cmp->cmp_id;
		
		if (count($arr_cmps_resp) >= $limit_ins) {
		
			$s_cmps_ids = implode(',' , $arr_cmps_resp);
			$ele_info = new stdClass();
			$ele_info->nom_arch_resp = $nom_archivo_resp;
			$ele_info->accion = "AGREGAR";
			$res_resp = respaldar_cmp($s_cmps_ids, $ele_info);
		
			$now = date("Y-m-d H:i:s");
			$sql_upd_pago_cmp = "update pago_comprobante set pcm_estado='Atendido',pcm_momento='$now' where pcm_lote_exec='$lote_exec' and pcm_cmp_id in($s_cmps_ids);";
			$arr_sent_sql[] = $sql_upd_pago_cmp . "\n";	
		
			$arr_cmps_resp = array();
			
			if (!$res_resp) {
				break 2;
			}
		}
				
		$cue_cod_gasto = $urbanizacion->urb_costo;
		$cue_cod_gasto_dif = $urbanizacion->urb_costo_diferido;
		$can_codigo_id = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta->ven_can_codigo);
							
		$saldo_costo = $venta->ven_costo - $venta->ven_costo_cub;
		$saldo_venta = $venta->ven_monto_efectivo;
		
		$valor = ($saldo_costo > 0) ? $pago->vpag_capital * ($saldo_costo / $saldo_venta) : 0;
		
		if ($valor == 0) {
			$msj = "-- el valor del costo para el pago es cero..(venta:$venta->ven_id - pago:$pago->vpag_id - capital:$pago->vpag_capital - saldo_costo:$saldo_costo - saldo_venta:$saldo_venta - valor:$valor)";
			echo "<p>$msj</p>";
			$arr_sent_sql[] = $msj . "\n";
			$pagos->siguiente();
			continue;
		} 
		// else {
			// echo "<p>(valor > 0)i.- $i</p>";
		// }
				
		$cmp_fecha = $cmp->cmp_fecha;
		$cmp_mon_id = $cmp->cmp_mon_id;
		
		// $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp_fecha'");
		$cambios = FUNCIONES::objetos_bd_sql("select * from con_tipo_cambio where tca_fecha = '$cmp_fecha'");
		
		$tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }
					
		$sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
		where cue_codigo in('$cue_cod_gasto','$cue_cod_gasto_dif') and cde_mon_id='$cmp->cmp_mon_id'
		and	cde_cmp_id='$cmp->cmp_id'
		order by cde_mon_id asc, cde_secuencia asc;";
		
		// echo "<p>$sql_dets -- (venta:" . $venta->ven_id . " - pago:" . $pago->vpag_id .")</p>";
				
		// $dets = FUNCIONES::lista_bd_sql($sql_dets);
		$dets = FUNCIONES::objetos_bd_sql($sql_dets);
					
		if ($dets->get_num_registros() != 2) {
			$msj = "-- No se encontraron detalles con las cuentas indicadas..(venta:" . $venta->ven_id . " - pago:" . $pago->vpag_id .")";
			echo "<p>$msj</p>";
			$arr_sent_sql[] = $msj . "\n";
			
			if ($valor > 0) {
				
				$sql_dets_dif_cambio = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
				where cue_codigo in('$dif_cambio_egr','$dif_cambio_ing') and cde_cmp_id='$cmp->cmp_id'";
				
				$dets_dif = FUNCIONES::lista_bd_sql($sql_dets_dif_cambio);
				
				if (count($dets_dif) > 0) {
				//	CONTINUAR AQUI ------	
					foreach ($dets_dif as $det_dif) {
						$sql_del = "delete from con_comprobante_detalle where cde_cmp_id='$det_dif->cde_cmp_id' and cde_secuencia='$det_dif->cde_secuencia' and cde_mon_id='$det_dif->cde_mon_id' and cde_cue_id='$det_dif->cde_cue_id';";											
						$arr_sent_sql[] = $sql_del . "\n";
					}
				}
				$sql_max_index = "select max(cde_secuencia)as campo from con_comprobante_detalle 
				inner join con_cuenta on(cde_cue_id=cue_id)
				where cue_codigo not in ('$dif_cambio_egr','$dif_cambio_ing')
				and	cde_cmp_id='$cmp->cmp_id'";
				
				$max_index = FUNCIONES::atributo_bd_sql($sql_max_index) * 1;
				
				$cue_gasto_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_gasto);
				$cue_gasto_dif_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_gasto_dif);				
								
				for ($iii = 1; $iii <= 2; $iii++) {
					
					if ($iii == 1) {
						$signo = 1;
						$cue_id = $cue_gasto_id;
					} else {
						$signo = (-1);
						$cue_id = $cue_gasto_dif_id;
					}
					
					$max_index++;
					$val_bol = round($valor * $tc * $signo, 6);
					
					$sql = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values('$cmp->cmp_id','1','$max_index','$can_codigo_id','0','0','$cue_id','$val_bol','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
					
					$arr_sent_sql[] = $sql . "\n";												
					
					if (count($arr_sent_sql) >= $limit_ins) {
						$resp = escribir_en_archivo($nom_archivo ,$arr_sent_sql);
						$arr_sent_sql = array();
						if (!$resp) {
							break 2;
						}
					}
															
					$cambios->reset();
					for ($k = 0; $k < $cambios->get_num_registros(); $k++) {
						$cambio = $cambios->get_objeto();
						$cde_mon_id = $cambio->tca_mon_id;
						if($tcambios[$cde_mon_id]>0){
							$cde_valor = $val_bol / $tcambios[$cde_mon_id];
						}else{
							$cde_valor = $val_bol / $cambio->tca_valor;
						}
						
						$cde_valor = round($cde_valor, 6);
						$sql = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values('$cmp->cmp_id','$cde_mon_id','$max_index','$can_codigo_id','0','0','$cue_id','$cde_valor','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
						$arr_sent_sql[] = $sql . "\n";
												
						if (count($arr_sent_sql) >= $limit_ins) {
							$resp = escribir_en_archivo($nom_archivo ,$arr_sent_sql);
							$arr_sent_sql = array();
							if (!$resp) {
								break 3;
							}
						}

						$cambios->siguiente();
					}
					
					$dets->siguiente();
				}
				
			} else {
				echo "<p>-- Valor igual a cero</p>";
			}
			
			$pagos->siguiente();			
			continue;
		} else {
				
			$msj = "-- ACTUALIZANDO venta:$venta->ven_id - pago:$pago->vpag_id - cmp_id:$cmp->cmp_id";
			$arr_sent_sql[] = $msj . "\n";
			
			for ($dd = 0; $dd < $dets->get_num_registros(); $dd++) {
				$det = $dets->get_objeto();
								
				$signo = ($det->cde_valor > 0) ? 1: (-1);
				$val_bol = round($valor * $tc * $signo, 6);
							
				$sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol',cde_can_id='$can_codigo_id' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
				$arr_sent_sql[] = $sql_upd_det . "\n";
				
				if (count($arr_sent_sql) >= $limit_ins) {
					$resp = escribir_en_archivo($nom_archivo ,$arr_sent_sql);
					$arr_sent_sql = array();
					if (!$resp) {
						break 2;
					}
				}
					
				$cambios->reset();
				for ($k = 0; $k < $cambios->get_num_registros(); $k++) {
					$cambio = $cambios->get_objeto();
					$cde_mon_id = $cambio->tca_mon_id;
					if($tcambios[$cde_mon_id]>0){
						$cde_valor = $val_bol / $tcambios[$cde_mon_id];
					}else{
						$cde_valor = $val_bol / $cambio->tca_valor;
					}

					$cde_valor = round($cde_valor, 6);
					$sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor',cde_can_id='$can_codigo_id' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
					$arr_sent_sql[] = $sql_upd_det . "\n";                
					
					if (count($arr_sent_sql) >= $limit_ins) {
						$resp = escribir_en_archivo($nom_archivo ,$arr_sent_sql);
						$arr_sent_sql = array();
						if (!$resp) {
							break 3;
						}
					}

					$cambios->siguiente();
				}
				
				$dets->siguiente();
			}
		}
					
		$pagos->siguiente();
	}
	
	
	if (count($arr_cmps_resp) > 0 && count($arr_cmps_resp) <= $limit_ins) {
		
		$s_cmps_ids = implode(',' , $arr_cmps_resp);
		$ele_info = new stdClass();
		$ele_info->nom_arch_resp = $nom_archivo_resp;
		$ele_info->accion = "AGREGAR";
		$res_resp = respaldar_cmp($s_cmps_ids, $ele_info);
	
		$now = date("Y-m-d H:i:s");
		$sql_upd_pago_cmp = "update pago_comprobante set pcm_estado='Atendido',pcm_momento='$now' where pcm_lote_exec='$lote_exec' and pcm_cmp_id in($s_cmps_ids);";
		$arr_sent_sql2 = array($sql_upd_pago_cmp . "\n");	
		escribir_en_archivo($nom_archivo, $arr_sent_sql2);
	
		$arr_cmps_resp = array();
	}
	
		
	// $s_cmps_ids = implode(',', $arr_cmps_resp);
	// $arr_sent_sql[] = "-- (" . $s_cmps_ids . ")\n";
	
	// $now = date("Y-m-d H:i:s");
	// $sql_upd_pago_cmp = "update pago_comprobante set pcm_estado='Atendido',pcm_momento='$now' where pcm_lote_exec='$lote_exec' and pcm_cmp_id in($s_cmps_ids);";
	// $arr_sent_sql[] = $sql_upd_pago_cmp . "\n";	
	// escribir_en_archivo($nom_archivo, $arr_sent_sql);
	
	// $ele_info = new stdClass();
	// $ele_info->origen = "corregir_costos_en_pagos";
	// $ele_info->tiempo = $time;
	// respaldar_cmp($s_cmps_ids, $ele_info, 0, 'AJUSTE DE COSTOS EN PAGOS');
		
}

function eliminar_costos_ventas_casas(){
	echo "<p>(1)entrando a eliminar_costos_ventas_casas...</p>";
	$sql = "select * from pago_comprobante 
	inner join venta_pago on(pcm_vpag_id=vpag_id) 
	inner join con_comprobante on(pcm_cmp_id=cmp_id) 
	inner join venta on(vpag_ven_id=ven_id and ven_prod_id>0 and ven_estado!='Anulado') 
	inner join urbanizacion on(ven_urb_id=urb_id) 
	where pcm_lote_exec='lote2'";
	
	$pagos = FUNCIONES::objetos_bd_sql($sql);
	echo "<p>(2)entrando a eliminar_costos_ventas_casas...</p>";
	for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
		
		$pago = $pagos->get_objeto();
		$urbanizacion = clone $pago;
		$venta = clone $pago;
		$cmp = clone $pago;
		
		$cue_cod_gasto = $urbanizacion->urb_costo;
		$cue_cod_gasto_dif = $urbanizacion->urb_costo_diferido;
		
		$sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
		where cue_codigo in('$cue_cod_gasto','$cue_cod_gasto_dif') and cde_cmp_id='$cmp->cmp_id'";
		
		// echo "<p>$sql_dets;</p>";
		
		$dets = FUNCIONES::lista_bd_sql($sql_dets);
		
		if (count($dets) > 0) {
		//	CONTINUAR AQUI ------	
			foreach ($dets as $det_dif) {
				$sql_del = "delete from con_comprobante_detalle where cde_cmp_id='$det_dif->cde_cmp_id' and cde_secuencia='$det_dif->cde_secuencia' and cde_mon_id='$det_dif->cde_mon_id' and cde_cue_id='$det_dif->cde_cue_id';";											
				echo "<p>$sql_del</p>";
			}
		} else {
			echo "<p>-- No tiene los detalles con las cuentas de costos.(venta:$venta->ven_id - pago:$pago->vpag_id - cmp:$cmp->cmp_id)</p>";
		}
		
		$pagos->siguiente();
	}
	echo "<p>(3)entrando a eliminar_costos_ventas_casas...</p>";
}

/**/

// $sql_ventas_sin_analitica = "select * from venta where (ven_can_codigo = '' or ven_can_codigo is null) and ven_estado ='Anulado' limit 0,5";
// $ventas = FUNCIONES::objetos_bd_sql($sql_ventas_sin_analitica);
// for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
// $venta = $ventas->get_objeto();
// crear_cuenta_analitica($venta->ven_id);
// $ventas->siguiente();
// }

/**/


/* INI: GENERAR LAS CUENTAS ANALITICAS DE LAS VENTAS */
if ($_GET[tarea] == 'crear_ca_analitica') {

    foreach ($arr_gestiones as $ges_id) {
        crear_ca_analitica($ges_id);
    }
    echo "shit";
}
// exit;

/* PARA LAS OPERACIONES DE NEGOCIO */

/* PARA ACTUALIZAR LA CUENTA ANALITICA EN LOS CMPS DE VENTAS */
if ($_GET[tarea] == 'act_cmps_venta') {

    $ven_id = $_GET[venta];
    $limit = $_GET[limit];
    actualizar_comprobantes($ven_id, $limit);
}

/* PARA ACTUALIZAR LA CUENTA ANALITICA EN LOS CMPS DE RETENCIONES */
if ($_GET[tarea] == 'act_cmps_retencion') {
    $filtro_ventas = "";
    if ($_GET[ventas] != '') {
        $filtro_ventas = " and ven_id in ($_GET[ventas])";
    }
    actualizar_cmps_retenciones();
}

/* PARA ACTUALIZAR LA CUENTA ANALITICA EN LOS CMPS DE ACTIVACIONES */
if ($_GET[tarea] == 'act_cmps_activacion') {
    actualizar_cmps_activaciones();
}

/* PARA ACTUALIZAR LA CUENTA ANALITICA EN LOS CMPS DE FUSIONES */
if ($_GET[tarea] == 'act_cmps_fusion') {
    actualizar_cmps_fusiones();
}

/* PARA ACTUALIZAR LA CUENTA ANALITICA EN LOS CMPS DE CAMBIOS DE LOTES */
if ($_GET[tarea] == 'act_cmps_cambio_lote') {
    actualizar_cmps_cambio_lote();
}

if ($_GET[tarea] == 'procesar_pagos') {
    procesar_pagos(0, null, $_GET[urb]);
}

if ($_GET[tarea] == 'migracion_cdd') {
    $filtro = "";

    if (isset($_GET[venta])) {
        $filtro = " and ven_id='$_GET[venta]'";
    }
    crear_cmp_ajuste_migracion_cdd($filtro);
}

if ($_GET[tarea] == 'migracion_demazu') {
    $filtro = "";

    if (isset($_GET[venta])) {
        $filtro = " and ven_id='$_GET[venta]'";
    }
    crear_cmp_ajuste_migracion_demazu($filtro);
}

if ($_GET[tarea] == 'migracion_vexsa') {
    $filtro = "";

    if (isset($_GET[venta])) {
        $filtro = " and ven_id='$_GET[venta]'";
    }
    crear_cmp_ajuste_migracion_vexsa($filtro);
}

if ($_GET[tarea] == 'desc_inc_cdd') {
	if (isset($_GET[filtro])) {
		$filtro = " and vpag_id in($_GET[filtro])";
	}
    gestionar_descuentos_incrementos_cdd($filtro);
}

if ($_GET[tarea] == 'desc_inc_demazu') {
	if (isset($_GET[filtro])) {
		$filtro = " and vpag_id in($_GET[filtro])";
	}
    gestionar_descuentos_incrementos_demazu($filtro);
}

if ($_GET[tarea] == 'desc_inc_vexsa') {
	if (isset($_GET[filtro])) {
		$filtro = " and vpag_id in($_GET[filtro])";
	}
    gestionar_descuentos_incrementos_vexsa($filtro);
}

if ($_GET[tarea] == 'desc_inc_demas') {
	if (isset($_GET[filtro])) {
		$filtro = " and vpag_id in($_GET[filtro])";
	}
    gestionar_descuentos_incrementos_demas($filtro);
}

if ($_GET[tarea] == 'desc_inc_indiv') {
	if (isset($_GET[filtro])) {
		$filtro = " and vpag_id in($_GET[filtro])";
	}
	gestionar_descuentos_incrementos_individual($filtro);
}

if ($_GET[tarea] == 'ajustar_cmps_venta_con_descuentos') {
	$filtro = '';
	if ($_GET[venta] != '') {
		$filtro = " and ven_id='$_GET[venta]'";
	}
	ajustar_cmps_venta_con_descuentos($filtro);
}

if ($_GET[tarea] == 'actualizar_costo_en_pagos') {
	$filtro = "";
	$limit = "";
	if ($_GET[pago] != '') {
		$filtro = " and vpag_id='$_GET[pago]'";		
	}
	
	if ($_GET[limit] != '') {
		$limit = " limit " . $_GET[limit];
	}
	
	actualizar_costo_en_pagos($filtro, $limit);
}

// eliminar_costos_ventas_casas();

// actualizar_costo_en_pagos_todos();
// $filtro = " and vpag_id>='31' and vpag_id<='88018'";
// $filtro = " and vpag_id>='88019' and vpag_id<='128624'";
// actualizar_costo_en_pagos($filtro);

//    $th1 = new Thread('procesar_pagos');
//    $th2 = new Thread('procesar_pagos');
//    $th3 = new Thread('procesar_pagos');
////
//    $lim1 = new stdClass();
//    $lim1->min = 100;
//    $lim1->max = 500;
////
//    $lim2 = new stdClass();
//    $lim2->min = 1000;
//    $lim2->max = 5000;
////
//    $lim3 = new stdClass();
//    $lim3->min = 10000;
//    $lim3->max = 50000;
////
//    $th1->start(0, $lim1);
//    $th2->start(0, $lim2);
//    $th3->start(0, $lim3);
//exit();

/*
  $arr_segmentos = generar_script_venta_pagos();
  foreach ($arr_segmentos as $lim) {
  procesar_pagos(0, $lim);
  }
 */

// actualizar_comprobantes();
// $filtro = " and ven_urb_id='4' and ven_moneda='2'";
// $filtro = " and ven_id='9373'";

// crear_cmp_ajuste_migracion_demazu($filtro);
// crear_cmp_ajuste_migracion_vexsa($filtro);



// procesar_pagos_anteriores_demazu();