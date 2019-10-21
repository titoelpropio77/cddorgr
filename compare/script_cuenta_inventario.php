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

function determinar_nro_venta_lote($filtro = '') {

    $sql = "select ven_lot_id,urb_nombre,man_nro,lot_nro,count(ven_id) as cant,
	group_concat(ven_id order by ven_id asc) as ven_ids from venta 
	inner join lote on(ven_lot_id=lot_id) 
	inner join manzano on(lot_man_id=man_id) 
	inner join urbanizacion on(man_urb_id=urb_id) 
	where ven_estado!='Anulado' 
	GROUP by ven_lot_id";

    $regs = FUNCIONES::objetos_bd_sql($sql);

    $time = date('dmY_His');
    $nom_arch = "sqls/determinar_nro_venta_lote_$time.sql";
    $fp = fopen($nom_arch, "w");
    $buff = "";
    $lim = 100;
    $cont = 0;

    for ($i = 0; $i < $regs->get_num_registros(); $i++) {
        $reg = $regs->get_objeto();

        $arr_ven_ids = explode(',', $reg->ven_ids);

        $orden = 1;
        foreach ($arr_ven_ids as $ven_id) {
            $sql_upd = "update venta set ven_orden='$orden' where ven_id='$ven_id' and ven_lot_id='$reg->ven_lot_id';\n";
            $orden++;
            $cont++;

            $buff .= $sql_upd;

            if (($cont % $lim) == 0) {
                fputs($fp, $buff);
                $buff = "";
            }
        }

        $regs->siguiente();
    }

    fputs($fp, $buff);

    fclose($fp);
}

function actualizar_cuenta_inventario($filtro = '') {
    $sql = "select ven_id,cmp_ges_id,cmp_id,
	cinv.cue_id as cue_id_inv,
	cinv.cue_codigo,
	cadj.cue_id as cue_id_inv_adj,
	cadj.cue_codigo 
	from venta 
	inner join urbanizacion on(ven_urb_id=urb_id)
	inner join con_comprobante on(
		cmp_tabla in ('venta_ajuste_saldos','venta_migracion','venta') 
		and cmp_tabla_id=ven_id and cmp_eliminado='No'
	)
	inner join con_gestion on(cmp_ges_id=ges_id)
	inner join con_cuenta cinv on(cinv.cue_ges_id=ges_id)
	inner join con_cuenta cadj on(cadj.cue_ges_id=ges_id)
	where ven_orden > 1 and ven_estado!='Anulado'
    and cinv.cue_codigo=urb_inv_terrenos 
	and cadj.cue_codigo=urb_inv_terrenos_adj";

    $cmps = FUNCIONES::objetos_bd_sql($sql);

    $time = date('dmY_His');
    $nom_arch = "sqls/actualizar_cuenta_inventario_$time.sql";
    $nom_arch_resp = "sqls/actualizar_cuenta_inventario_respaldo_$time.sql";
    $fp = fopen($nom_arch, "w");
    $fr = fopen($nom_arch_resp, "w");
    $buff = "";
    $buff_resp = "";
    $lim = 100;
    $cont = 0;

    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {
        $cmp = $cmps->get_objeto();
        $sql_upd_dets = "update con_comprobante_detalle set cde_cue_id='$cmp->cue_id_inv_adj' where cde_cue_id='$cmp->cue_id_inv' and cde_cmp_id='$cmp->cmp_id';\n";
        $sql_upd_dets_resp = "update con_comprobante_detalle set cde_cue_id='$cmp->cue_id_inv' where cde_cue_id='$cmp->cue_id_inv_adj' and cde_cmp_id='$cmp->cmp_id';\n";

        $buff .= $sql_upd_dets;
        $buff_resp .= $sql_upd_dets_resp;

        if (($i % $lim) == 0) {
            fputs($fp, $buff);
            fputs($fr, $buff_resp);
            $buff = "";
            $buff_resp = "";
        }

        $cmps->siguiente();
    }

    fputs($fp, $buff);
    fputs($fr, $buff_resp);

    fclose($fp);
    fclose($fr);
}

function actualizar_cuenta_inventario_cambio_lote($filtro = '') {
    $sql = "SELECT ven_id,cmp_ges_id,cmp_id,
		cinv.cue_id as cue_id_inv,
		cinv.cue_codigo as cue_codigo_inv,
		cadj.cue_id as cue_id_inv_adj,
		cadj.cue_codigo as cue_codigo_inv_adj,
		urb_inv_terrenos,urb_inv_terrenos_adj		
	FROM `venta` 
	inner join urbanizacion on(ven_urb_id=urb_id)
	inner join (
			select cmp_id,cmp_tabla,cmp_tabla_id,cmp_ges_id from con_comprobante 
        	where cmp_tabla='venta_cambio_lote' 		
			and cmp_eliminado='No'
	)cmps on(cmp_tabla_id=ven_venta_id)
	
	inner join con_cuenta cinv on(cinv.cue_ges_id=cmp_ges_id)
	inner join con_cuenta cadj on(cadj.cue_ges_id=cmp_ges_id)
	WHERE ven_venta_id>0 and ven_orden>1 
	and ven_estado!='Anulado'
    and cinv.cue_codigo=urb_inv_terrenos 
	and cadj.cue_codigo=urb_inv_terrenos_adj;";

    echo "<p>$sql</p>";

    $cmps = FUNCIONES::objetos_bd_sql($sql);

    $time = date('dmY_His');
    $nom_arch = "sqls/actualizar_cuenta_inventario_cambio_lote_$time.sql";
    $nom_arch_resp = "sqls/actualizar_cuenta_inventario_cambio_lote_respaldo_$time.sql";

    $fp = fopen($nom_arch, "w");
    $fr = fopen($nom_arch_resp, "w");

    $buff = "";
    $buff_resp = "";
    $cont = 0;
    $lim = 100;

    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {
        $cmp = $cmps->get_objeto();

        $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id) 
		where cde_cmp_id='$cmp->cmp_id' and cue_codigo='$cmp->urb_inv_terrenos' and cde_valor<0";

        echo "<p style='color:red;'>$sql_dets</p>";

        $detalles = FUNCIONES::lista_bd_sql($sql_dets);

        foreach ($detalles as $det) {

            $cont++;
            $sql_upd_det = "update con_comprobante_detalle set cde_cue_id='$cmp->cue_id_inv_adj' where cde_cue_id='$cmp->cue_id_inv' and cde_cmp_id='$det->cde_cmp_id' and cde_secuencia='$det->cde_secuencia' and cde_mon_id='$det->cde_mon_id';\n";
            $sql_upd_det_resp = "update con_comprobante_detalle set cde_cue_id='$cmp->cue_id_inv' where cde_cue_id='$cmp->cue_id_inv_adj' and cde_cmp_id='$det->cde_cmp_id' and cde_secuencia='$det->cde_secuencia' and cde_mon_id='$det->cde_mon_id';\n";

            $buff .= $sql_upd_det;
            $buff_resp .= $sql_upd_det_resp;

            if ($cont % $lim) {

                fputs($fp, $buff);
                fputs($fr, $buff_resp);
                $buff = "";
                $buff_resp = "";
            }
        }

        $cmps->siguiente();
    }

    fputs($fp, $buff);
    fputs($fr, $buff_resp);

    fclose($fp);
    fclose($fr);
}

// determinar_nro_venta_lote();
// actualizar_cuenta_inventario();
actualizar_cuenta_inventario_cambio_lote();
