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


function gestionar_cmps_pagos() {

    $ele_info = new stdClass();
    $ele_info->origen = "pagos_externos";
    
    $conec = new ADO();
    $limit_ins = 100;
    $filtro_vpag_id = '';
        
    $sql_pags = "select * from venta_pago     
    inner join venta on (vpag_ven_id=ven_id)     
    inner join con_comprobante on(cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id and cmp_eliminado='No')    
    where vpag_estado='Activo'     
    and vpag_monto>0
    and vpag_venta_id is null 
    and vpag_capital_inc=0 
    and vpag_capital_desc=0
    and ven_urb_id in (5,6)
    and ven_estado != 'Anulado' and ven_prod_id=0
    $filtro_vpag_id;";

    echo "<p>$sql_pags</p>";
//    return false;

    $pagos = FUNCIONES::objetos_bd_sql($sql_pags);

    $cont = 0;
    $urb_id_actual = 0;
    $arr_cmps_ids = array();

    $tiempo = date('dmY_His_') . rand();

    $arc_upd = "actualizar_cmps_pagos_externos" . $tiempo . ".sql";

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
        
        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante "
                        . "where cmp_tabla = 'venta_pago' "
                        . "and cmp_tabla_id='$pag->vpag_id' "
                        . "and cmp_eliminado='No'");
        
        if ($cmp == NULL) {
            echo "<p>-- NO EXISTE EL COMPROBANTE SUPUESTAMENTE GENERADO ANTERIORMENTE</p>";
            $s_res = ob_get_contents();
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            ob_start();
            
            $pagos->siguiente();            
            continue;
        }

        $sql_fpags = "select * from con_pago where fpag_tabla='$cmp->cmp_tabla' "
                . "and fpag_tabla_id='$cmp->cmp_tabla_id' "
                . "and fpag_estado='Activo' order by fpag_id";

        $dets = FUNCIONES::lista_bd_sql($sql_fpags);

        if (count($dets) == 0) {
            echo "<p>-- NO EXISTEN LOS DETALLES DE PAGOS SUPUESTAMENTE GENERADOS ANTERIORMENTE</p>";
            $s_res = ob_get_contents();
            ob_end_clean();
            $s_res = str_replace('<p>', '', $s_res);
            $s_res = str_replace('</p>', PHP_EOL, $s_res);
            fputs($fp, $s_res);
            ob_start();
            
            $pagos->siguiente();            
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
            'form' => $pag->vpag_form,
            'envio' => $pag->vpag_envio,
            'mora' => $pag->vpag_mora,
            'detalles' => $detalles,            
        );

        $comprobante = MODELO_COMPROBANTE::pago_cuota_ext($data);
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
?>