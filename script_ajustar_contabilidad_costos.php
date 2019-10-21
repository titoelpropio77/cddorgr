<?php

ini_set('display_errors', 'On');
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once('config/zona_horaria.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/formulario.class.php';

$f_ven_id = (isset($_GET[venta])) ? " and ven_id='$_GET[venta]'" : "";
//gestionar_cmps_ventas();

gestionar_pagos();

function gestionar_pagos() {

    $partes = 10;

    // $f_fecha_fin = " and vpag_fecha_pago<='2016-03-05'";

    $sql_ventas = "select * from venta_pago     
    inner join venta on (vpag_ven_id=ven_id)     
    inner join urbanizacion on(ven_urb_id=urb_id)    
    where abs(ven_costo - ((urb_val_costo*ven_superficie)*if(ven_moneda = 1,6.96,1)))>=0.1
    and vpag_estado='Activo'     
    and vpag_monto>0
    and vpag_venta_id is null 
    and vpag_capital_inc=0 
    and vpag_capital_desc=0    
    and ven_estado != 'Anulado' order by vpag_id asc";

    echo "<p>$sql_ventas;</p>";

    $l_ventas = FUNCIONES::objetos_bd_sql($sql_ventas);
    $cant_ventas = $l_ventas->get_num_registros();

    $cuantas = intval(ceil($cant_ventas / $partes));
    echo "<p>-- cuantas:$cuantas - cant_ventas:$cant_ventas - partes:$partes</p>";
    // exit();

    $li = 0;
    $arr_segmentos = array();
    for ($i = 1; $i <= $partes; $i++) {
        $sql_ven = "select min(vpag_id)as minima,max(vpag_id)as maxima from(select vpag_id from venta_pago     
        inner join venta on (vpag_ven_id=ven_id)     
        inner join urbanizacion on(ven_urb_id=urb_id)    
        where abs(ven_costo - ((urb_val_costo*ven_superficie)*if(ven_moneda = 1,6.96,1)))>=0.1
        and vpag_estado='Activo'         
        and vpag_monto>0
        and vpag_venta_id is null 
        and vpag_capital_inc=0 
        and vpag_capital_desc=0    
        and ven_estado != 'Anulado'             
        order by vpag_id asc limit $li,$cuantas)tmp";

        echo "<p>$sql_ven;</p>";

        $obj = FUNCIONES::objeto_bd_sql($sql_ven);
        $ele = new stdClass();
        $ele->min = $obj->minima;
        $ele->max = $obj->maxima;
        $ele->origen = 'pagos';
        $arr_segmentos[] = $ele;
        $li = $li + $cuantas;
    }

    FUNCIONES::print_pre($arr_segmentos);

    foreach ($arr_segmentos as $ele) {
        gestionar_cmps_pagos($ele, $f_fecha_fin);
//        break;
    }
}

function gestionar_cmps_ventas() {

    $limit_ins = 100;
    $conec = new ADO();

    global $limit;
    global $f_ven_id;

    $sql = "select venta.*,urbanizacion.*
    from venta
    inner join urbanizacion on(ven_urb_id=urb_id)    
    where abs(ven_costo - ((urb_val_costo*ven_superficie)*if(ven_moneda = 1,6.96,1)))>=0.1
    and ven_estado != 'Anulado' and ven_prod_id=0 $f_ven_id $limit";

    echo "<p style='color:blue'>$sql;</p>";
//    exit();

    $ventas = FUNCIONES::objetos_bd_sql($sql);

    $tiempo = date('dmY_His_') . rand();
    $arc_upd = "actualizar_cmps_ventas_" . $tiempo . ".sql";

    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    $arr_cmps_ventas_ids = array();

    $cont = 0;
    ob_start();
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();

        $sql_cmp = "select * from con_comprobante where cmp_tabla='venta' "
                . "and cmp_tabla_id='$venta->ven_id' and cmp_eliminado='No'";

        $cmp = FUNCIONES::objeto_bd_sql($sql_cmp);

        $cambio_usd = 1;
        if ($venta->ven_moneda == '1') {
            $cambio_usd = 6.96;
        }

        $costo = $venta->ven_superficie * ($venta->urb_val_costo * $cambio_usd);
        $costo_cub = ($venta->ven_res_anticipo + $venta->ven_monto_intercambio);
        $costo_pag = 0;
        if ($costo_cub > $costo) {
            $costo_cub = $costo;
        }

        $sql_upd_ven = "update venta set ven_costo='$costo',ven_costo_cub='$costo_cub' where ven_id='$venta->ven_id'";
        echo "<p>$sql_upd_ven;</p>";

        if ($cmp) {

            $monto_efectivo = $venta->ven_monto - ($venta->ven_monto_intercambio + $venta->ven_res_anticipo);

            $data = array(
                'moneda' => $venta->ven_moneda,
                'ges_id' => $cmp->cmp_ges_id,
                'fecha' => $venta->ven_fecha,
                'glosa' => $cmp->cmp_glosa,
                'interno' => $cmp->cmp_referido,
                'tabla_id' => $venta->ven_id,
                'urb' => $venta,
                'anticipo' => $venta->ven_res_anticipo,
                'saldo_efectivo' => $monto_efectivo,
                'monto_intercambio' => $venta->ven_monto_intercambio,
                'intercambio_ids' => explode(",", $venta->ven_intercambio_ids),
                'intercambio_montos' => explode(",", $venta->ven_intercambio_montos),
                'monto_pagar' => $venta->ven_monto_pagar,
                'costo' => $costo,
                'costo_cub' => $costo_cub,
                'monto_venta' => $venta->ven_monto,
                'prorat_lote' => 1,
            );

            $comprobante = MODELO_COMPROBANTE::venta($data);

            $comprobante->cmp_id = $cmp->cmp_id;
            $comprobante->usu_per_id = $cmp->cmp_usu_id;
            $comprobante->usu_id = $cmp->cmp_usu_cre;
            COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);

            $cmp_id = $cmp->cmp_id;
            $arr_cmps_ventas_ids[] = $cmp_id;
        } else {
            echo "<p>-- NO EXISTE EL CMP DE LA VENTA $venta->ven_id - cmp_id:$cmp->cmp_id</p>";
        }

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

        $ventas->siguiente();
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


    if (count($arr_cmps_ventas_ids) > 0) {

        $ele_info = new stdClass();
        $ele_info->origen = 'ventas';
        $s_cmps_ids = implode(',', $arr_cmps_ventas_ids);
        respaldar_cmp($s_cmps_ids, $ele_info, $tab_dmp_id);
        $sql_sel_cmps_eq = "select * from con_comprobante where cmp_id in ($s_cmps_ids);";
        echo "<p>$sql_sel_cmps_eq</p>";
    } else {
        echo "<p>-- NADA PARA RESPALDAR.</p>";
        return false;
    }

    $s_cmps_ventas_ids = implode(',', $arr_cmps_ventas_ids);
    echo "<p style='color:green;'>CMPS VENTAS IDS:$s_cmps_ventas_ids</p>";
}

function gestionar_cmps_pagos($ele_info, $f_fecha_fin = '') {

    $conec = new ADO();

    $limit_ins = 100;


    $filtro_vpag_id = '';
    if ($ele_info) {
        $filtro_vpag_id = " and vpag_id>='$ele_info->min' and vpag_id<='$ele_info->max'";
    }

    $sql_pags = "select * from venta_pago     
    inner join venta on (vpag_ven_id=ven_id)     
    inner join urbanizacion on(ven_urb_id=urb_id)    
    where abs(ven_costo - ((urb_val_costo*ven_superficie)*if(ven_moneda = 1,6.96,1)))>=0.1
    and vpag_estado='Activo' 
    $f_fecha_fin
    and vpag_monto>0
    and vpag_venta_id is null 
    and vpag_capital_inc=0 
    and vpag_capital_desc=0    
    and ven_estado != 'Anulado'    
    $filtro_vpag_id;";

    echo "<p>$sql_pags</p>";
//    return false;

    $pagos = FUNCIONES::objetos_bd_sql($sql_pags);

    $cont = 0;
    $urb_id_actual = 0;
    $arr_cmps_ids = array();

    $tiempo = date('dmY_His_') . rand();
//    $arc_upd = "stage demazu/scripts updates/actualizar_cmps_pagos_demazu_" .$ele_info->min ."-". $ele_info->max. "_" . $tiempo . ".sql";
    $arc_upd = "actualizar_cmps_pagos_" . $ele_info->min . "-" . $ele_info->max . "_" . $tiempo . ".sql";

    $sql_ins_dump = "insert into tab_aux_dump(nombre_archivo_upd)values('$arc_upd')";
    $conec->ejecutar($sql_ins_dump, FALSE, TRUE);
    $tab_dmp_id = ADO::$insert_id;

    echo "<p>-- $arc_upd($tab_dmp_id)</p>";
    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    ob_start();
    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {

        $pag = $pagos->get_objeto();
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pag->vpag_ven_id'");
        if ($pag->ven_urb_id != $urb_id_actual) {
            $urb_id_actual = $pag->ven_urb_id;
            $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$pag->ven_urb_id'");
        }

        $saldo_costo = $venta->ven_costo - ($venta->ven_res_anticipo + $venta->ven_monto_intercambio + $venta->ven_cuota_inicial);
        $saldo_venta = $venta->ven_monto_efectivo;
        $costo_cuota = round(($pag->vpag_capital * $saldo_costo) / $saldo_venta, 2);
        $capital_ids = explode(',', $pag->vpag_capital_ids);
        $capital_montos = explode(',', $pag->vpag_capital_montos);

        $costo_ids = array();
        $costo_montos = array();
        for ($ii = 0; $ii < count($capital_ids); $ii++) {
            $a_costo = round(($capital_montos[$ii] * $saldo_costo) / $saldo_venta, 2);
            $costo_montos[] = $a_costo;
            $costo_ids[] = $capital_ids[$ii];
        }
        $txt_costo_ids = implode(',', $costo_ids);
        $txt_costo_montos = implode(',', $costo_montos);

        $sql_upd_pago = "update venta_pago set vpag_costo='$costo_cuota',vpag_costo_ids='$txt_costo_ids', vpag_costo_montos='$txt_costo_montos' where vpag_id='$pag->vpag_id';";
        echo "<p>$sql_upd_pago</p>";
//        $conec->ejecutar($sql_upd_pago, FALSE, FALSE);

        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante "
                        . "where cmp_tabla = 'venta_pago' "
                        . "and cmp_tabla_id='$pag->vpag_id' "
                        . "and cmp_eliminado='No'");
        if ($cmp == NULL) {
            $pagos->siguiente();
            echo "<p>-- NO EXISTE EL COMPROBANTE SUPUESTAMENTE GENERADO ANTERIORMENTE</p>";
            continue;
        }

        $sql_fpags = "select * from con_pago where fpag_tabla='$cmp->cmp_tabla' "
                . "and fpag_tabla_id='$cmp->cmp_tabla_id' "
                . "and fpag_estado='Activo' order by fpag_id";

        $dets = FUNCIONES::lista_bd_sql($sql_fpags);

        if (count($dets) == 0) {
            $pagos->siguiente();
            echo "<p>-- NO EXISTEN LOS DETALLES DE PAGOS SUPUESTAMENTE GENERADOS ANTERIORMENTE</p>";
            continue;
        }

        $arr_cmps_ids[] = $cmp->cmp_id;

        unset($_POST[a_fpag_monto]);
        unset($_POST[a_fpag_mon_id]);
        unset($_POST[a_fpag_forma_pago]);
        unset($_POST[a_fpag_ban_nombre]);
        unset($_POST[a_fpag_ban_nro]);
        unset($_POST[a_fpag_cue_id]);
        unset($_POST[a_fpag_descripcion]);

        $k = 0;
        foreach ($dets as $d) {
            $_POST[a_fpag_monto][$k] = $d->fpag_monto;
            $_POST[a_fpag_mon_id][$k] = $d->fpag_mon_id;
            $_POST[a_fpag_forma_pago][$k] = $d->fpag_forma_pago;
            $_POST[a_fpag_ban_nombre][$k] = $d->fpag_ban_nombre;
            $_POST[a_fpag_ban_nro][$k] = $d->fpag_ban_nro;
            $_POST[a_fpag_cue_id][$k] = $d->fpag_cue_id;
            $_POST[a_fpag_descripcion][$k] = $d->fpag_descripcion;
            $k++;
        }

        $params = array(
            'tabla' => 'venta_pago',
            'tabla_id' => $cmp->cmp_tabla_id,
            'fecha' => $cmp->cmp_fecha,
            'moneda' => $cmp->cmp_mon_id,
            'ingreso' => true,
            'guardar_pago' => false,
            'une_id' => $urb->urb_une_id,
            'glosa' => $cmp->cmp_glosa, 'ca' => '0', 'cf' => 0, 'cc' => 0
        );
        $detalles = FORMULARIO::insertar_pagos($params);

        $data = array(
            'moneda' => $venta->ven_moneda,
            'ges_id' => $cmp->cmp_ges_id,
            'fecha' => $cmp->cmp_fecha,
            'glosa' => $cmp->cmp_glosa,
            'interno' => $cmp->cmp_referido,
            'tabla_id' => $cmp->cmp_tabla_id,
            'urb' => $urb,
            'anticipo' => $venta->ven_res_anticipo,
            'interes' => $pag->vpag_interes,
            'capital' => $pag->vpag_capital,
            'capital_producto' => 0,
            'costo_producto_separado' => 0,
            'form' => $pag->vpag_form,
            'envio' => $pag->vpag_envio,
            'mora' => $pag->vpag_mora,
            'detalles' => $detalles,
            'costo' => $costo_cuota,
            'costo_producto' => 0,
            'prorat_lote' => 1,
            'prorat_producto' => 0,
        );

        $comprobante = MODELO_COMPROBANTE::pago_cuota($data);
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

        $pagos->siguiente();
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
    $tiempo = date('d_m_Y_H_i_s_') . rand();

    $ademas = "_";
    if ($ele_info->min) {
        $ademas .= $ele_info->min . "_" . $ele_info->max;
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
    fclose($fp);
}

function ajustar_registros_comerciales() {
    echo "<p>INICIO - " . date('Y-m-d H:i:s') . "</p>";
    $conec = null;
    $sql_ventas_costo_mal = "select venta.*,urbanizacion.*,    
    from venta
    inner join urbanizacion on(ven_urb_id=urb_id)    
    where abs(ven_costo - ((urb_val_costo*ven_superficie)*if(ven_moneda = 1,6.96,1)))>=0.1
    and ven_estado != 'Anulado' and ven_prod_id=0";

    $ventas = FUNCIONES::objetos_bd_sql($sql_ventas_costo_mal);

    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();

        $sql_cmp = "select * from con_comprobante where cmp_tabla='venta' "
                . "and cmp_tabla_id='$venta->ven_id' and cmp_eliminado='No'";

        $cmp = FUNCIONES::objeto_bd_sql($sql_cmp);

        $cambio_usd = 1;
        if ($venta->ven_moneda == '1') {
            $cambio_usd = 6.96;
        }

        $costo = $venta->ven_superficie * ($venta->urb_val_costo * $cambio_usd);
        $monto_efectivo = $venta->ven_monto - ($venta->ven_monto_intercambio + $venta->ven_res_anticipo);
        $costo_cub = ($venta->ven_res_anticipo + $venta->ven_monto_intercambio);
        $costo_pag = 0;

        if ($costo_cub > $costo) {
            $costo_cub = $costo;
        }

        $sql_upd_venta = "update venta set ven_costo='$costo',ven_costo_cub='$costo_cub' where ven_id='$venta->ven_id';";
        echo "<p>$sql_upd_venta</p>";

        if ($cmp == NULL) {
            echo "<p>No tiene comprobante</p>";
        } else {

            $data = array(
                'moneda' => $venta->ven_moneda,
                'ges_id' => $cmp->cmp_ges_id,
                'fecha' => $venta->ven_fecha,
                'glosa' => $cmp->cmp_glosa,
                'interno' => $cmp->cmp_referido,
                'tabla_id' => $venta->ven_id,
                'urb' => $venta,
                'anticipo' => $venta->ven_res_anticipo,
                'saldo_efectivo' => $monto_efectivo,
                'monto_intercambio' => $venta->ven_monto_intercambio,
                'intercambio_ids' => explode(",", $venta->ven_intercambio_ids),
                'intercambio_montos' => explode(",", $venta->ven_intercambio_montos),
                'monto_pagar' => $venta->ven_monto_pagar,
                'costo' => $costo,
                'costo_cub' => $costo_cub,
                'monto_venta' => $venta->ven_monto,
                'prorat_lote' => 1,
            );

            $comprobante = MODELO_COMPROBANTE::venta($data);

            $comprobante->cmp_id = $cmp->cmp_id;
            $comprobante->usu_per_id = $cmp->cmp_usu_id;
            $comprobante->usu_id = $cmp->cmp_usu_cre;
            COMPROBANTES::modificar_comprobante($comprobante, $conec, FALSE);
        }
        $ventas->siguiente();
    }
    echo "<p>FIN - " . date('Y-m-d H:i:s') . "</p>";
}
