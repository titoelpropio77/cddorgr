<?php

require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
include_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/formulario.class.php';

//gestionar_reversiones();
gestionar_fusiones();
function gestionar_reversiones() {

    $ele_info = new stdClass();
    $ele_info->origen = "reversiones_externas";

    $conec = new ADO();
    $limit_ins = 100;
    $filtro_vpag_id = '';

    $sql_revs = "select * from venta_negocio inner join venta on(vneg_ven_id=ven_id)
    and vneg_tipo='reversion'
    where ven_urb_id in(5,6) and vneg_estado='Activado'";

    echo "<p>$sql_revs</p>";
//    return false;

    $reversiones = FUNCIONES::objetos_bd_sql($sql_revs);

    $cont = 0;
    $urb_id_actual = 0;
    $arr_cmps_ids = array();

    $tiempo = date('dmY_His_') . rand();

    $arc_upd = "actualizar_cmps_reversiones_externas_" . $tiempo . ".sql";

    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    ob_start();
    for ($i = 0; $i < $reversiones->get_num_registros(); $i++) {

        $rev = $reversiones->get_objeto();
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$rev->vneg_ven_id'");
        if ($venta->ven_urb_id != $urb_id_actual) {
            $urb_id_actual = $venta->ven_urb_id;
            $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        }

        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante "
                        . "where cmp_tabla = 'venta_retencion' "
                        . "and cmp_tabla_id='$rev->vneg_ven_id'");

        if ($cmp == NULL) {
            echo "<p>-- NO EXISTE EL COMPROBANTE SUPUESTAMENTE GENERADO ANTERIORMENTE</p>";
            $s_res = ob_get_contents();
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            ob_start();

            $reversiones->siguiente();
            continue;
        }

        $arr_cmps_ids[] = $cmp->cmp_id;

        $pagado = total_pagado($venta->ven_id);
        $tot_pagado = $venta->ven_res_anticipo + $venta->ven_venta_pagado + $pagado->capital;
        $saldo = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;

        $monto_intercambio = $venta->ven_monto_intercambio;

        $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto "
                        . "from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
        $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto "
                        . "from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");

        $data = array(
            'moneda' => $cmp->cmp_mon_id,
            'ges_id' => $cmp->cmp_ges_id,
            'fecha' => $cmp->cmp_fecha,
            'glosa' => $cmp->cmp_glosa,
            'interno' => $cmp->cmp_referido,
            'tabla_id' => $cmp->cmp_tabla_id,
            'tarea_id' => $cmp->cmp_tarea_id,
            'urb' => $urb,
            'costo' => $venta->ven_costo,
            'costo_pagado' => $venta->ven_costo_cub + $pagado->costo,
            'saldo_efectivo' => $saldo,
            'total_pagado' => $tot_pagado,
            'intercambio' => $monto_intercambio,
            'inter_montos' => $amontos,
            'inter_montos_pag' => $amontos_pag,
        );

        $comprobante = MODELO_COMPROBANTE::venta_retencion_ext($data);
        $comprobante->cmp_id = $cmp->cmp_id;
        $comprobante->usu_per_id = $cmp->cmp_usu_id;
        $comprobante->usu_id = $cmp->cmp_usu_cre;
        COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
        $cont++;

        if ($cont == $limit_ins) {
            $cont = 0;
            $s_res = ob_get_contents();
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            ob_start();
        }

        $reversiones->siguiente();
    }

    if ($cont > 0 && $cont < $limit_ins) {
        $cont = 0;
        $s_res = ob_get_contents();
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        fputs($fp, $s_res);
    }
    fclose($fp);

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

function gestionar_fusiones() {

    $ele_info = new stdClass();
    $ele_info->origen = "fusiones_externas";

    $conec = new ADO();
    $limit_ins = 100;
    $filtro_vpag_id = '';

    $sql_fus = "select * from venta_negocio inner join venta on(vneg_ven_ori=ven_id)
    and vneg_tipo='fusion'
    where ven_urb_id in(5,6) and vneg_estado='Activado'";

    echo "<p>$sql_fus</p>";
//    return false;

    $fusiones = FUNCIONES::objetos_bd_sql($sql_fus);

    $cont = 0;
    $urb_id_actual = 0;
    $arr_cmps_ids = array();

    $tiempo = date('dmY_His_') . rand();

    $arc_upd = "actualizar_cmps_fusiones_externas_" . $tiempo . ".sql";

    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    ob_start();
    for ($i = 0; $i < $fusiones->get_num_registros(); $i++) {

        $fus = $fusiones->get_objeto();

        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante "
                        . "where cmp_tabla = 'venta_fusion' "
                        . "and cmp_tabla_id='$fus->vneg_ven_ori' and cmp_eliminado='No'");

        if ($cmp == NULL) {
            echo "<p>-- NO EXISTE EL COMPROBANTE SUPUESTAMENTE GENERADO ANTERIORMENTE</p>";
            $s_res = ob_get_contents();
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
            'monto_pagar' => $monto_pagar
        );

        $comprobante = MODELO_COMPROBANTE::venta_fusion_ext($data);
        $comprobante->cmp_id = $cmp->cmp_id;
        $comprobante->usu_per_id = $cmp->cmp_usu_id;
        $comprobante->usu_id = $cmp->cmp_usu_cre;
        COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
        $cont++;

        if ($cont == $limit_ins) {
            $cont = 0;
            $s_res = ob_get_contents();
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
        ob_end_clean();
        $s_res = str_replace('<p>', '', $s_res);
        $s_res = str_replace('</p>', PHP_EOL, $s_res);
        fputs($fp, $s_res);
    }
    fclose($fp);

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

function respaldar_cmp($s_cmps_ids, $ele_info = NULL, $id_dump = 0) {

    $limite_ins = 10;
    $tiempo = date('dmY_His_') . rand();

    $ademas = "_";
    if ($ele_info->min) {
        $ademas .= $ele_info->min . "_" . $ele_info->max . "_";
    }

    $arc_bck = "detalles_cmps_" . $ele_info->origen . $ademas . $tiempo . ".sql";
    FUNCIONES::bd_query("update tab_aux_dump set nombre_archivo_bck='$arc_bck' where id='$id_dump'");
    $fp = fopen("imp_exp/" . $arc_bck, 'w');

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
}

function total_pagado($ven_id) {
    $sql_pag = "select sum(ind_interes_pagado)as interes, sum(ind_capital_pagado) as capital, sum(ind_monto_pagado) as monto, 
                            sum(ind_capital_desc) as descuento, sum(ind_capital_inc) as incremento ,
                            sum(ind_costo_pagado) as costo
                            from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id 
                    ";
    $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
    return $pagado;
}

?>