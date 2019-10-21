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

function crear_cmps_pagos_faltante($vpag_id, $codigo_cuenta, $moneda = 2) {

    $conec = new ADO();

    $arc_upd = "crear_cmp_venta_pago_$vpag_id.sql";

    $fp = fopen("imp_exp/" . $arc_upd, 'w');

    $filtro_vpag_id = '';

    $sql_pags = "select * from venta_pago  
    inner join venta on (vpag_ven_id=ven_id)	
    where 1    
    and vpag_estado='Activo'     
    and vpag_id='$vpag_id';";

    echo "<p>$sql_pags</p>";
//    return false;

    $pag = FUNCIONES::objeto_bd_sql($sql_pags);

    $sql_ins_fpag_plan = "insert into con_pago(
	fpag_forma_pago,fpag_cue_id, fpag_monto,fpag_mon_id, fpag_descripcion, 
	fpag_tabla, fpag_tabla_id, fpag_fecha, fpag_estado,fpag_tipo,fpag_une_id,fpag_une_porc
	) values";

    if ($pag) {

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pag->vpag_ven_id'");
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

        $gestion = FUNCIONES::objeto_bd_sql("select * from con_gestion 
		where ges_fecha_ini<='$pag->vpag_fecha_pago' and ges_fecha_fin>='$pag->vpag_fecha_pago' and ges_eliminado='No'");

        if ($gestion == null) {
            echo "<p style='color:red;'>No existe la gestion del pago $vpag_id ...</p>";
            return false;
        }

        $ges_id = $gestion->ges_id;

        $sql_fpags = "select * from con_pago where fpag_tabla='venta_pago' "
                . "and fpag_tabla_id='$pag->vpag_id' "
                . "and fpag_estado='Activo' order by fpag_id";

        $dets = FUNCIONES::lista_bd_sql($sql_fpags);

        if (count($dets) > 0) {

            $sql_anul_pagos = "update con_pago set fpag_estado='Anulado', fpag_log='Detalle de Pagos Anulados por no haber generado el cmp contable' 
			where fpag_tabla='venta_pago' and fpag_tabla_id='$pag->vpag_id'";

            $conec->ejecutar($sql_anul_pagos, FALSE, FALSE);
        }

        $cue_id = FUNCIONES::get_cuenta($ges_id, $codigo_cuenta);

        // $sql_ins_fpag = $sql_ins_fpag_plan . "('Efectivo','$cue_id','$pag->vpag_monto','$pag->vpag_moneda',
        // '','venta_pago','$pag->vpag_id','$pag->vpag_fecha_pago','Activo','Ingreso','$urb->urb_une_id','100')";

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
        // echo "<p>$sql_upd_pago</p>";
        $conec->ejecutar($sql_upd_pago, FALSE, FALSE);

        unset($_POST[a_fpag_monto]);
        unset($_POST[a_fpag_mon_id]);
        unset($_POST[a_fpag_forma_pago]);
        unset($_POST[a_fpag_ban_nombre]);
        unset($_POST[a_fpag_ban_nro]);
        unset($_POST[a_fpag_cue_id]);
        unset($_POST[a_fpag_descripcion]);

        $k = 0;
        $_POST[a_fpag_monto][$k] = $pag->vpag_monto;
        $_POST[a_fpag_mon_id][$k] = $pag->vpag_moneda;
        $_POST[a_fpag_forma_pago][$k] = 'Efectivo';
        $_POST[a_fpag_ban_nombre][$k] = '';
        $_POST[a_fpag_ban_nro][$k] = '';
        $_POST[a_fpag_cue_id][$k] = $cue_id;
        $_POST[a_fpag_descripcion][$k] = '';

        //Pago de la Venta Nro. 3252 - Urb:CIUDAD DE DIOS LUJAN - Mza:111 - Lote:24 - Zona:Zona B - UV:2 - Ricardo Rafael Suarez Porcel - Rec. 201812001458
        $interno = FUNCIONES::interno_nombre($venta->ven_int_id);
        $glosa = "Pago de la Venta Nro. 3252 - " . $venta->ven_concepto . " - " . $interno . " - Rec. " . $pag->vpag_recibo;

        $params = array(
            'tabla' => 'venta_pago',
            'tabla_id' => $pag->vpag_id,
            'fecha' => $pag->vpag_fecha_pago,
            'moneda' => $pag->vpag_moneda,
            'ingreso' => true,
            'guardar_pago' => true,
            'une_id' => $urb->urb_une_id,
            'glosa' => $glosa, 'ca' => '0', 'cf' => 0, 'cc' => 0
        );
        $detalles = FORMULARIO::insertar_pagos($params);

        $data = array(
            'moneda' => $venta->ven_moneda,
            'ges_id' => $ges_id,
            'fecha' => $pag->vpag_fecha_pago,
            'glosa' => $glosa,
            'interno' => $interno,
            'tabla_id' => $pag->vpag_id,
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

        if ($urb->urb_tipo == 'Interno') {
            $comprobante = MODELO_COMPROBANTE::pago_cuota($data);
        } elseif ($urb->urb_tipo == 'Externo') {
            $comprobante = MODELO_COMPROBANTE::pago_cuota_ext($data);
        }

        $comprobante->usu_per_id = 1;
        $comprobante->usu_id = 'admin';
        COMPROBANTES::registrar_comprobante($comprobante, $conec);
    } else {
        echo "<p style='color:red;'>NO EXISTE EL PAGO...</p>";
    }
}

function verificar_cuentas_correctas($pago) {
    $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pago->vpag_ven_id'");
    $urbanizaciones = FUNCIONES::lista_bd_sql("select * from urbanizacion where urb_id != '$venta->ven_urb_id'");

    $gestiones = array(14, 15, 16, 17, 18, 19);
    foreach ($urbanizaciones as $urbanizacion) {

        $codigo_cta_por_cobrar_bs = $urbanizacion->urb_cta_por_cobrar_bs;
        $codigo_ingreso_por_venta_bs = $urbanizacion->urb_ing_por_venta_bs;
        $codigo_cta_por_cobrar_usd = $urbanizacion->urb_cta_por_cobrar_usd;
        $codigo_ingreso_por_venta_usd = $urbanizacion->urb_ing_por_venta_usd;

        $codigo_ingreso_por_venta_diferido = $urbanizacion->urb_ing_diferido;
        $codigo_costo = $urbanizacion->urb_costo;
        $codigo_costo_diferido = $urbanizacion->urb_costo_diferido;
        $codigo_inv_terrenos = $urbanizacion->urb_inv_terrenos;

        $codigo_urb_reserva = $urbanizacion->urb_reserva;
        $codigo_urb_inv_terrenos_adj = $urbanizacion->urb_inv_terrenos_adj;
        $codigo_urb_ing_reserva = $urbanizacion->urb_ing_reserva;
        $codigo_urb_ing_por_interes_usd = $urbanizacion->urb_ing_por_interes_usd;
        $codigo_urb_ing_por_interes_bs = $urbanizacion->urb_ing_por_interes_bs;

        foreach ($gestiones as $ges) {
            
        }
    }
}

// crear_cmps_pagos_faltante('221554','1.1.1.01.2.27');
// crear_cmps_pagos_faltante('222752','1.1.1.01.2.27');
// crear_cmps_pagos_faltante('222753','1.1.1.01.2.27');