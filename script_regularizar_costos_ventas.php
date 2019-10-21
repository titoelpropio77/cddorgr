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


$filtro = "";
$filtro = (isset($_GET[ventas])) ? " and ven_id in ($_GET[ventas])" : "";

//ajustar_costos($filtro);
ajustar_costos_en_fusiones($filtro);

// ajustar_cuenta_ingreso_fusion();
exit();

function ajustar_costos($filtro = '') {

    $sql = "select * from venta 
	inner join urbanizacion on(ven_urb_id=urb_id)	
	left join venta_producto on(vprod_ven_id=ven_id and vprod_estado!='Anulado')
	where 1 $filtro and ven_estado!='Anulado' and urb_id not in(5,6,12)";

    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $time = date('Ymd_His');
    $nom_arch = "imp_exp/ajustar_costos_gral_$time.sql";

    echo $sql;


    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();

        if (in_array($venta->ven_urb_id, array(2, 3, 4))) {

            if ($venta->ven_fecha <= '2014-12-31') {
                // ajustar_cmp_venta_ajuste_saldos($venta, $nom_arch);
            } else {
                // ajustar_cmp_venta($venta, $nom_arch);
            }
        } else {
            // ajustar_cmp_venta($venta, $nom_arch);
        }

        ajustar_cmp_pagos($venta, $nom_arch);
        // ajustar_cmp_pagos_migracion($venta, $nom_arch);
        // ajustar_cmp_pagos_desc_inc($venta, $nom_arch);

        ajustar_cmp_retencion($venta, $nom_arch);
        // ajustar_cmp_activacion($venta, $nom_arch);	--	HACER LUEGO DE ACTUALIZAR LOS CMPS DE RETENCION

        $ventas->siguiente();
    }
}

function ajustar_costos_en_fusiones($filtro) {

    $time = date('Ymd_His');
    $nom_arch = "imp_exp/ajustar_costos_gral_$time.sql";

    $sql = "select * from venta 
	inner join urbanizacion on(ven_urb_id=urb_id)
	where ven_estado='Fusionado' and urb_id not in(5,6,12) $filtro";
    $ventas = FUNCIONES::objetos_bd_sql($sql);

    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        ajustar_cmp_fusion($venta, $nom_arch);
        $ventas->siguiente();
    }
}

function ajustar_cmp_pagos_old_version($venta, $nom_arch) {

    $saldo_costo = ($venta->ven_costo > $venta->ven_costo_cub) ? $venta->ven_costo - $venta->ven_costo_cub : 0;
    $saldo_efectivo = $venta->ven_monto_efectivo;

    if ($venta->ven_prod_id > 0) {
        // echo "<p>entrando por ven_prod_id > 0</p>";
        $saldo_costo_producto = ($venta->ven_costo_producto > $venta->ven_costo_producto_cub) ? $venta->ven_costo_producto - $venta->ven_costo_producto_cub : 0;

        $saldo_producto = $venta->ven_monto_producto;

        $saldo_total = $venta->ven_monto_efectivo + $venta->ven_monto_producto;

        $prorat_lote = $venta->ven_monto_efectivo / $saldo_total;
        $prorat_casa = $venta->ven_monto_producto / $saldo_total;
    } else {
        if ($venta->vprod_id != null) {

            // echo "<p>entrando por vprod_id != null</p>";
            $saldo_costo_producto = ($venta->vprod_costo > $venta->vprod_costo_cub) ? $venta->vprod_costo - $venta->vprod_costo_cub : 0;

            $saldo_producto = $venta->vprod_monto_efectivo;
        }
    }

    if (($saldo_costo + $saldo_costo_producto) == 0) {
        return false;
    }

    $dif_cambio_egr = "6.3.1.01.1.04";
    $dif_cambio_ing = "4.2.1.01.1.01";

    $urb = clone $venta;
    $cue_cod_gasto_dif = $urb->urb_costo_diferido;
    $cue_cod_costo = $urb->urb_costo;

    $cue_cod_gasto_dif_prod = $urb->urb_cue_costo_diferido_producto;
    $cue_cod_costo_prod = $urb->urb_cue_costo_producto;

    $sql_ins_plan = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values";

    $sql_pagos = "select * from venta_pago 
	inner join con_comprobante on(cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id)
	where cmp_eliminado='No' 
	and vpag_estado='Activo' 
	and (vpag_capital>0 or vpag_capital_prod>0)
	and vpag_ven_id=$venta->ven_id
	order by vpag_fecha_pago asc";

    $cmps = FUNCIONES::objetos_bd_sql($sql_pagos);

    $arr_sent_sql = array();

    // FUNCIONES::print_pre($venta);
    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {

        $arr_cuentas_cubrir = array();

        $cmp = $cmps->get_objeto();
        $pago = clone $cmp;

        $costo_pagado = 0;
        $costo_pagado_producto = 0;


        if ($venta->ven_prod_id > 0) {
            // echo "<p>entrando por ven_prod_id > 0</p>";

            $costo_pagado = $pago->vpag_capital * $prorat_lote * ($saldo_costo / $saldo_efectivo);
            $costo_pagado_producto = $pago->vpag_capital * $prorat_casa * ($saldo_costo_producto / $saldo_producto);
        } else {
            // echo "<p>entrando por NO ven_prod_id > 0</p>";
            if ($venta->vprod_id != null) {

                $costo_pagado = $pago->vpag_capital * ($saldo_costo / $saldo_efectivo);
                $costo_pagado_producto = ($pago->vpag_vprod_id != null) ? $pago->vpag_capital_prod * ($saldo_costo_producto / $saldo_producto) : 0;
            } else {
                // echo "<p>entrando por vprod_id == null</p>";
                $costo_pagado = $pago->vpag_capital * ($saldo_costo / $saldo_efectivo);
            }
        }

        $valor = $costo_pagado;
        $valor2 = $costo_pagado_producto;

        $cmp_mon_id = $cmp->cmp_mon_id;
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp->cmp_fecha'");

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }

        // $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
        // where cue_codigo in(
        // '$cue_cod_costo','$cue_cod_gasto_dif','$cue_cod_costo_prod','$cue_cod_gasto_dif_prod'
        // ) 		
        // and	cde_cmp_id='$cmp->cmp_id'
        // order by cde_mon_id asc, cde_secuencia asc;";
        // $dets = FUNCIONES::lista_bd_sql($sql_dets);
        // if (count($dets) > 0) {
        // foreach ($dets as $d) {
        // $sql_del = "delete from con_comprobante_detalle where cde_cmp_id='$d->cde_cmp_id' and cde_secuencia='$d->cde_secuencia' and cde_mon_id='$d->cde_mon_id' and cde_cue_id='$d->cde_cue_id';";
        // $arr_sent_sql[] = $sql_del . "\n";
        // }
        // }
        // $sql_dets_dif_cambio = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
        // where cue_codigo in('$dif_cambio_egr','$dif_cambio_ing') and cde_cmp_id='$cmp->cmp_id'";
        // $dets_dif = FUNCIONES::lista_bd_sql($sql_dets_dif_cambio);
        // if (count($dets_dif) > 0) {
        // foreach ($dets_dif as $det_dif) {
        // $sql_del = "delete from con_comprobante_detalle where cde_cmp_id='$det_dif->cde_cmp_id' and cde_secuencia='$det_dif->cde_secuencia' and cde_mon_id='$det_dif->cde_mon_id' and cde_cue_id='$det_dif->cde_cue_id';";											
        // $arr_sent_sql[] = $sql_del . "\n";
        // }
        // }
        // $sql_max_index = "select max(cde_secuencia)as campo from con_comprobante_detalle 
        // inner join con_cuenta on(cde_cue_id=cue_id)
        // where cue_codigo not in ('$dif_cambio_egr','$dif_cambio_ing','$cue_cod_costo','$cue_cod_gasto_dif','$cue_cod_costo_prod','$cue_cod_gasto_dif_prod')
        // and	cde_cmp_id='$cmp->cmp_id'";
        // $max_index = FUNCIONES::atributo_bd_sql($sql_max_index) * 1;

        echo "<p>costo_pagado:$costo_pagado - costo_pagado_producto:$costo_pagado_producto - pag_id:$pago->vpag_id</p>";

        $arr_monedas_valor = array();
        $tipo_costo = "";
        $can_codigo_id = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta->ven_can_codigo);

        if ($valor > 0) {

            $arr_cuentas_cubrir[$cue_cod_costo] = $cue_cod_costo;
            $arr_cuentas_cubrir[$cue_cod_gasto_dif] = $cue_cod_gasto_dif;

            $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
			where cue_codigo in(
				'$cue_cod_costo','$cue_cod_gasto_dif'
			) 		
			and	cde_cmp_id='$cmp->cmp_id'
			and	cde_mon_id='$cmp->cmp_mon_id'
			order by cde_mon_id asc, cde_secuencia asc;";

            $dets = FUNCIONES::lista_bd_sql($sql_dets);

            foreach ($dets as $det) {

                if ($det->cue_codigo == $cue_cod_costo) {
                    $signo = 1;
                    $valor_calc = $valor;
                    $tipo_costo = "lote";
                    unset($arr_cuentas_cubrir[$cue_cod_costo]);
                } else if ($det->cue_codigo == $cue_cod_gasto_dif) {
                    $signo = (-1);
                    $valor_calc = $valor;
                    $tipo_costo = "lote";
                    unset($arr_cuentas_cubrir[$cue_cod_gasto_dif]);
                }

                $val_bol = round($valor_calc * $tc * $signo, 6);

                $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                $arr_sent_sql[] = $sql_upd_det . "\n";

                if ($signo == 1) {
                    $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                }

                $cambios->reset();
                for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                    $cambio = $cambios->get_objeto();
                    $cde_mon_id = $cambio->tca_mon_id;
                    if ($tcambios[$cde_mon_id] > 0) {
                        $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                    } else {
                        $cde_valor = $val_bol / $cambio->tca_valor;
                    }

                    $cde_valor = round($cde_valor, 6);
                    $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                    $arr_sent_sql[] = $sql_upd_det . "\n";

                    if ($signo == 1) {
                        $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                    }

                    $cambios->siguiente();
                }
            }


            // for ($ijk = 1; $ijk <=2; $ijk++) {
            // $max_index++;
            // if ($ijk == 1) {
            // $signo = 1;			
            // $valor_calc = $valor;
            // $tipo_costo = "lote";
            // $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_costo);					
            // } else {
            // $signo = (-1);
            // $valor_calc = $valor;
            // $tipo_costo = "lote";
            // $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_gasto_dif);
            // }
            // if ($signo == 1) {
            // $arr_monedas_valor[$tipo_costo][1] = $val_bol;
            // }
            // $val_bol = round($valor_calc * $tc * $signo, 6);
            // $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','1','$max_index','$can_codigo_id','0','0','$cue_id','$val_bol','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
            // $arr_sent_sql[] = $sql_ins . "\n";
            // $cambios->reset();
            // for ($k = 0; $k < $cambios->get_num_registros(); $k++) {
            // $cambio = $cambios->get_objeto();
            // $cde_mon_id = $cambio->tca_mon_id;
            // if($tcambios[$cde_mon_id]>0){
            // $cde_valor = $val_bol / $tcambios[$cde_mon_id];
            // }else{
            // $cde_valor = $val_bol / $cambio->tca_valor;
            // }
            // $cde_valor = round($cde_valor, 6);
            // $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','$cde_mon_id','$max_index','$can_codigo_id','0','0','$cue_id','$cde_valor','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";					
            // $arr_sent_sql[] = $sql_ins . "\n";                
            // if ($signo == 1) {
            // $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
            // }
            // $cambios->siguiente();
            // }
            // }
        }

        if ($valor2 > 0) {

            $arr_cuentas_cubrir[$cue_cod_costo_prod] = $cue_cod_costo_prod;
            $arr_cuentas_cubrir[$cue_cod_gasto_dif_prod] = $cue_cod_gasto_dif_prod;

            $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
			where cue_codigo in(
				'$cue_cod_costo_prod','$cue_cod_gasto_dif_prod'
			) 		
			and	cde_cmp_id='$cmp->cmp_id'
			and	cde_mon_id='$cmp->cmp_mon_id'
			order by cde_mon_id asc, cde_secuencia asc;";

            $dets = FUNCIONES::lista_bd_sql($sql_dets);

            foreach ($dets as $det) {

                if ($det->cue_codigo == $cue_cod_costo_prod) {
                    $signo = 1;
                    $valor_calc = $valor2;
                    $tipo_costo = "producto";
                    unset($arr_cuentas_cubrir[$cue_cod_costo_prod]);
                } else if ($det->cue_codigo == $cue_cod_gasto_dif_prod) {
                    $signo = (-1);
                    $valor_calc = $valor2;
                    $tipo_costo = "producto";
                    unset($arr_cuentas_cubrir[$cue_cod_gasto_dif_prod]);
                }

                $val_bol = round($valor_calc * $tc * $signo, 6);

                $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                $arr_sent_sql[] = $sql_upd_det . "\n";

                if ($signo == 1) {
                    $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                }

                $cambios->reset();
                for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                    $cambio = $cambios->get_objeto();
                    $cde_mon_id = $cambio->tca_mon_id;
                    if ($tcambios[$cde_mon_id] > 0) {
                        $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                    } else {
                        $cde_valor = $val_bol / $cambio->tca_valor;
                    }

                    $cde_valor = round($cde_valor, 6);
                    $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                    $arr_sent_sql[] = $sql_upd_det . "\n";

                    if ($signo == 1) {
                        $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                    }

                    $cambios->siguiente();
                }
            }

            // for ($ijk = 1; $ijk <=2; $ijk++) {
            // $max_index++;
            // if ($ijk == 1) {
            // $signo = 1;			
            // $valor_calc = $valor2;
            // $tipo_costo = "producto";
            // $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_costo_prod);					
            // } else {
            // $signo = (-1);
            // $valor_calc = $valor2;
            // $tipo_costo = "producto";
            // $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_gasto_dif_prod);
            // }
            // if ($signo == 1) {
            // $arr_monedas_valor[$tipo_costo][1] = $val_bol;
            // }
            // $val_bol = round($valor_calc * $tc * $signo, 6);				
            // $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','1','$max_index','$can_codigo_id','0','0','$cue_id','$val_bol','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
            // $arr_sent_sql[] = $sql_ins . "\n";
            // $cambios->reset();
            // for ($k = 0; $k < $cambios->get_num_registros(); $k++) {
            // $cambio = $cambios->get_objeto();
            // $cde_mon_id = $cambio->tca_mon_id;
            // if($tcambios[$cde_mon_id]>0){
            // $cde_valor = $val_bol / $tcambios[$cde_mon_id];
            // }else{
            // $cde_valor = $val_bol / $cambio->tca_valor;
            // }
            // $cde_valor = round($cde_valor, 6);
            // $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','$cde_mon_id','$max_index','$can_codigo_id','0','0','$cue_id','$cde_valor','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";					
            // $arr_sent_sql[] = $sql_ins . "\n";                
            // if ($signo == 1) {
            // $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
            // }
            // $cambios->siguiente();
            // }
            // }
        }

        /*
          foreach ($dets as $det) {

          if ($det->cue_codigo == $cue_cod_costo) {
          $signo = 1;
          $valor_calc = $valor;
          $tipo_costo = "lote";
          } else if ($det->cue_codigo == $cue_cod_gasto_dif) {
          $signo = (-1);
          $valor_calc = $valor;
          $tipo_costo = "lote";
          }

          if ($det->cue_codigo == $cue_cod_costo_prod) {
          $signo = 1;
          $valor_calc = $valor2;
          $tipo_costo = "producto";
          } else if ($det->cue_codigo == $cue_cod_gasto_dif_prod) {
          $signo = (-1);
          $valor_calc = $valor2;
          $tipo_costo = "producto";
          }

          $val_bol = round($valor_calc * $tc * $signo, 6);

          $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
          $arr_sent_sql[] = $sql_upd_det . "\n";

          if ($signo == 1) {
          $arr_monedas_valor[$tipo_costo][1] = $val_bol;
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
          $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
          $arr_sent_sql[] = $sql_upd_det . "\n";

          if ($signo == 1) {
          $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
          }

          $cambios->siguiente();
          }

          }
         */

        if (count($arr_cuentas_cubrir) > 0) {
            foreach ($arr_cuentas_cubrir as $cod_cuenta => $value) {
                echo "<p>NO EXISTEN DETALLES PARA $value...</p>";
            }
        } else {
            echo "<p>SE CUBRIERON TODAS LAS CUENTAS...</p>";
        }

        $val_costo = $arr_monedas_valor['lote'][$pago->vpag_moneda];
        $sql_upd_pago = "update venta_pago set vpag_costo='$val_costo' where vpag_id='$pago->vpag_id';\n";
        $arr_sent_sql[] = $sql_upd_pago;

        if (isset($arr_monedas_valor['producto'][$pago->vpag_moneda])) {

            $val_costo = $arr_monedas_valor['producto'][$pago->vpag_moneda];
            $sql_upd_pago = "update venta_pago set vpag_costo_prod='$val_costo' where vpag_id='$pago->vpag_id';\n";
            $arr_sent_sql[] = $sql_upd_pago;
        }

        $cmps->siguiente();
    }

    $fp = fopen($nom_arch, 'a');

    foreach ($arr_sent_sql as $s_sql) {
        fputs($fp, $s_sql);
    }

    fclose($fp);
}

function ajustar_cmp_pagos($venta, $nom_arch) {

    $saldo_costo = ($venta->ven_costo > $venta->ven_costo_cub) ? $venta->ven_costo - $venta->ven_costo_cub : 0;
    $saldo_efectivo = $venta->ven_monto_efectivo;

    if ($venta->ven_prod_id > 0) {
        // echo "<p>entrando por ven_prod_id > 0</p>";
        $saldo_costo_producto = ($venta->ven_costo_producto > $venta->ven_costo_producto_cub) ? $venta->ven_costo_producto - $venta->ven_costo_producto_cub : 0;

        $saldo_producto = $venta->ven_monto_producto;

        $saldo_total = $venta->ven_monto_efectivo + $venta->ven_monto_producto;

        $prorat_lote = $venta->ven_monto_efectivo / $saldo_total;
        $prorat_casa = $venta->ven_monto_producto / $saldo_total;
    } else {
        if ($venta->vprod_id != null) {

            // echo "<p>entrando por vprod_id != null</p>";
            $saldo_costo_producto = ($venta->vprod_costo > $venta->vprod_costo_cub) ? $venta->vprod_costo - $venta->vprod_costo_cub : 0;

            $saldo_producto = $venta->vprod_monto_efectivo;
        }
    }

    if (($saldo_costo + $saldo_costo_producto) == 0) {
        return false;
    }

    $dif_cambio_egr = "6.3.1.01.1.04";
    $dif_cambio_ing = "4.2.1.01.1.01";

    $urb = clone $venta;
    $cue_cod_gasto_dif = $urb->urb_costo_diferido;
    $cue_cod_costo = $urb->urb_costo;

    $cue_cod_gasto_dif_prod = $urb->urb_cue_costo_diferido_producto;
    $cue_cod_costo_prod = $urb->urb_cue_costo_producto;

    $sql_ins_plan = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values";

    $sql_pagos = "select * from venta_pago 
	inner join con_comprobante on(cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id)
	where cmp_eliminado='No' 
	and vpag_estado='Activo' 
	and (vpag_capital>0 or vpag_capital_prod>0)
	and vpag_ven_id=$venta->ven_id
	order by vpag_fecha_pago asc";

    $cmps = FUNCIONES::objetos_bd_sql($sql_pagos);

    $arr_sent_sql = array();

    // FUNCIONES::print_pre($venta);
    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {

        $arr_cuentas_cubrir = array();

        $cmp = $cmps->get_objeto();
        $pago = clone $cmp;

        $costo_pagado = 0;
        $costo_pagado_producto = 0;


        if ($venta->ven_prod_id > 0) {
            // echo "<p>entrando por ven_prod_id > 0</p>";

            $costo_pagado = $pago->vpag_capital * $prorat_lote * ($saldo_costo / $saldo_efectivo);
            $costo_pagado_producto = $pago->vpag_capital * $prorat_casa * ($saldo_costo_producto / $saldo_producto);
        } else {
            // echo "<p>entrando por NO ven_prod_id > 0</p>";
            if ($venta->vprod_id != null) {

                $costo_pagado = $pago->vpag_capital * ($saldo_costo / $saldo_efectivo);
                $costo_pagado_producto = ($pago->vpag_vprod_id != null) ? $pago->vpag_capital_prod * ($saldo_costo_producto / $saldo_producto) : 0;
            } else {
                // echo "<p>entrando por vprod_id == null</p>";
                $costo_pagado = $pago->vpag_capital * ($saldo_costo / $saldo_efectivo);
            }
        }

        $valor = $costo_pagado;
        $valor2 = $costo_pagado_producto;

        $cmp_mon_id = $cmp->cmp_mon_id;
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp->cmp_fecha'");

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }

        echo "<p>costo_pagado:$costo_pagado - costo_pagado_producto:$costo_pagado_producto - pag_id:$pago->vpag_id</p>";

        $arr_monedas_valor = array();
        $tipo_costo = "";
        $can_codigo_id = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta->ven_can_codigo);

        $dets_a_crear = array();

        if ($valor > 0) {
            $dets_a_crear['lote'] = 'lote';
        }

        if ($valor2 > 0) {
            $dets_a_crear['producto'] = 'producto';
        }

        if ($valor > 0) {

            $arr_cuentas_cubrir[$cue_cod_costo] = $cue_cod_costo;
            $arr_cuentas_cubrir[$cue_cod_gasto_dif] = $cue_cod_gasto_dif;

            $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
			where cue_codigo in(
				'$cue_cod_costo','$cue_cod_gasto_dif'
			) 		
			and	cde_cmp_id='$cmp->cmp_id'
			and	cde_mon_id='$cmp->cmp_mon_id'
			order by cde_mon_id asc, cde_secuencia asc;";

            $dets = FUNCIONES::lista_bd_sql($sql_dets);

            foreach ($dets as $det) {
                $proceder = false;
                if ($det->cue_codigo == $cue_cod_costo) {
                    $signo = 1;
                    $valor_calc = $valor;
                    $tipo_costo = "lote";
                    unset($arr_cuentas_cubrir[$cue_cod_costo]);
                    $proceder = true;
                } else if ($det->cue_codigo == $cue_cod_gasto_dif) {
                    $signo = (-1);
                    $valor_calc = $valor;
                    $tipo_costo = "lote";
                    unset($arr_cuentas_cubrir[$cue_cod_gasto_dif]);
                    $proceder = true;
                }

                if ($proceder) {
                    $val_bol = round($valor_calc * $tc * $signo, 6);

                    $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                    $arr_sent_sql[] = $sql_upd_det . "\n";
                    unset($dets_a_crear['lote']);

                    if ($signo == 1) {
                        $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                    }

                    $cambios->reset();
                    for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                        $cambio = $cambios->get_objeto();
                        $cde_mon_id = $cambio->tca_mon_id;
                        if ($tcambios[$cde_mon_id] > 0) {
                            $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                        } else {
                            $cde_valor = $val_bol / $cambio->tca_valor;
                        }

                        $cde_valor = round($cde_valor, 6);
                        $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                        $arr_sent_sql[] = $sql_upd_det . "\n";

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                        }

                        $cambios->siguiente();
                    }
                }
            }
        }

        if ($valor2 > 0) {

            $arr_cuentas_cubrir[$cue_cod_costo_prod] = $cue_cod_costo_prod;
            $arr_cuentas_cubrir[$cue_cod_gasto_dif_prod] = $cue_cod_gasto_dif_prod;

            $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
			where cue_codigo in(
				'$cue_cod_costo_prod','$cue_cod_gasto_dif_prod'
			) 		
			and	cde_cmp_id='$cmp->cmp_id'
			and	cde_mon_id='$cmp->cmp_mon_id'
			order by cde_mon_id asc, cde_secuencia asc;";

            $dets = FUNCIONES::lista_bd_sql($sql_dets);

            foreach ($dets as $det) {
                $proceder = false;
                if ($det->cue_codigo == $cue_cod_costo_prod) {
                    $signo = 1;
                    $valor_calc = $valor2;
                    $tipo_costo = "producto";
                    unset($arr_cuentas_cubrir[$cue_cod_costo_prod]);
                    $proceder = true;
                } else if ($det->cue_codigo == $cue_cod_gasto_dif_prod) {
                    $signo = (-1);
                    $valor_calc = $valor2;
                    $tipo_costo = "producto";
                    unset($arr_cuentas_cubrir[$cue_cod_gasto_dif_prod]);
                    $proceder = true;
                }

                if ($proceder) {
                    $val_bol = round($valor_calc * $tc * $signo, 6);

                    $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                    $arr_sent_sql[] = $sql_upd_det . "\n";
                    unset($dets_a_crear['producto']);

                    if ($signo == 1) {
                        $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                    }

                    $cambios->reset();
                    for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                        $cambio = $cambios->get_objeto();
                        $cde_mon_id = $cambio->tca_mon_id;
                        if ($tcambios[$cde_mon_id] > 0) {
                            $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                        } else {
                            $cde_valor = $val_bol / $cambio->tca_valor;
                        }

                        $cde_valor = round($cde_valor, 6);
                        $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                        $arr_sent_sql[] = $sql_upd_det . "\n";

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                        }

                        $cambios->siguiente();
                    }
                }
            }
        }

        // if (count($arr_cuentas_cubrir) > 0) {
        // foreach ($arr_cuentas_cubrir as $cod_cuenta => $value) {
        // echo "<p>NO EXISTEN DETALLES PARA $value...</p>";
        // }
        // } else {
        // echo "<p>SE CUBRIERON TODAS LAS CUENTAS...</p>";
        // }

        if (count($dets_a_crear) > 0) {
            foreach ($dets_a_crear as $key => $value) {
                echo "<p>NO EXISTEN DETALLES PARA $value...</p>";

                $sql_dets_dif_cambio = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
				where cue_codigo in('$dif_cambio_egr','$dif_cambio_ing') and cde_cmp_id='$cmp->cmp_id'";

                $dets_dif = FUNCIONES::lista_bd_sql($sql_dets_dif_cambio);

                if (count($dets_dif) > 0) {

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

                if ($value == 'lote') {
                    for ($ijk = 1; $ijk <= 2; $ijk++) {
                        $max_index++;
                        if ($ijk == 1) {
                            $signo = 1;
                            $valor_calc = $valor;
                            $tipo_costo = "lote";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_costo);
                        } else {
                            $signo = (-1);
                            $valor_calc = $valor;
                            $tipo_costo = "lote";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_gasto_dif);
                        }

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                        }

                        $val_bol = round($valor_calc * $tc * $signo, 6);

                        $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','1','$max_index','$can_codigo_id','0','0','$cue_id','$val_bol','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                        $arr_sent_sql[] = $sql_ins . "\n";

                        $cambios->reset();
                        for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                            $cambio = $cambios->get_objeto();
                            $cde_mon_id = $cambio->tca_mon_id;
                            if ($tcambios[$cde_mon_id] > 0) {
                                $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                            } else {
                                $cde_valor = $val_bol / $cambio->tca_valor;
                            }

                            $cde_valor = round($cde_valor, 6);
                            $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','$cde_mon_id','$max_index','$can_codigo_id','0','0','$cue_id','$cde_valor','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                            $arr_sent_sql[] = $sql_ins . "\n";

                            if ($signo == 1) {
                                $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                            }

                            $cambios->siguiente();
                        }
                    }
                }

                if ($value == 'producto') {
                    for ($ijk = 1; $ijk <= 2; $ijk++) {
                        $max_index++;
                        if ($ijk == 1) {
                            $signo = 1;
                            $valor_calc = $valor2;
                            $tipo_costo = "producto";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_costo_prod);
                        } else {
                            $signo = (-1);
                            $valor_calc = $valor2;
                            $tipo_costo = "producto";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_gasto_dif_prod);
                        }

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                        }

                        $val_bol = round($valor_calc * $tc * $signo, 6);

                        $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','1','$max_index','$can_codigo_id','0','0','$cue_id','$val_bol','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                        $arr_sent_sql[] = $sql_ins . "\n";

                        $cambios->reset();
                        for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                            $cambio = $cambios->get_objeto();
                            $cde_mon_id = $cambio->tca_mon_id;
                            if ($tcambios[$cde_mon_id] > 0) {
                                $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                            } else {
                                $cde_valor = $val_bol / $cambio->tca_valor;
                            }

                            $cde_valor = round($cde_valor, 6);
                            $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','$cde_mon_id','$max_index','$can_codigo_id','0','0','$cue_id','$cde_valor','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                            $arr_sent_sql[] = $sql_ins . "\n";

                            if ($signo == 1) {
                                $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                            }

                            $cambios->siguiente();
                        }
                    }
                }
            }
        } else {
            echo "<p>SE CUBRIERON TODAS LAS CUENTAS...</p>";
        }

        $val_costo = $arr_monedas_valor['lote'][$pago->vpag_moneda];
        $sql_upd_pago = "update venta_pago set vpag_costo='$val_costo' where vpag_id='$pago->vpag_id';\n";
        $arr_sent_sql[] = $sql_upd_pago;

        if (isset($arr_monedas_valor['producto'][$pago->vpag_moneda])) {

            $val_costo = $arr_monedas_valor['producto'][$pago->vpag_moneda];
            $sql_upd_pago = "update venta_pago set vpag_costo_prod='$val_costo' where vpag_id='$pago->vpag_id';\n";
            $arr_sent_sql[] = $sql_upd_pago;
        }

        $cmps->siguiente();
    }

    $fp = fopen($nom_arch, 'a');

    foreach ($arr_sent_sql as $s_sql) {
        fputs($fp, $s_sql);
    }

    fclose($fp);
}

function ajustar_cmp_venta_ajuste_saldos($venta, $nom_arch) {
    $sql = "select * from con_comprobante 
	where cmp_tabla='venta_ajuste_saldos' and cmp_tabla_id='$venta->ven_id'
	and cmp_eliminado='No'";

    $cmp = FUNCIONES::objeto_bd_sql($sql);

    if ($cmp) {
        $urb = clone $venta;
        $cue_cod_gasto_dif = $urb->urb_costo_diferido;
        $cue_cod_inv = $urb->urb_inv_terrenos;
        $cue_cod_cartera = ($venta->ven_moneda == 2) ? $urb->urb_cta_por_cobrar_usd : $urb->urb_cta_por_cobrar_bs;

        $sql_saldo = "select ifnull(cde_valor, $venta->ven_monto_efectivo) as campo from con_comprobante_detalle 
		inner join con_cuenta on(cde_cue_id=cue_id and cue_ges_id=$cmp->cmp_ges_id)
		where cde_cmp_id='$cmp->cmp_id' 
		and cue_codigo='$cue_cod_cartera' 
		and cde_mon_id='$cmp->cmp_mon_id'";

        $saldo_capital = FUNCIONES::atributo_bd_sql($sql_saldo) * 1;

        $capital_pagado = $venta->ven_monto_efectivo - $saldo_capital;
        $saldo_costo = ($venta->ven_costo - $venta->ven_costo_cub);
        $saldo_efectivo = $venta->ven_monto_efectivo;

        $costo_pagado = $capital_pagado * ($saldo_costo / $saldo_efectivo);

        $saldo_costo_actual = $saldo_costo - $costo_pagado;
        $valor = $saldo_costo_actual;

        $cmp_mon_id = $cmp->cmp_mon_id;
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp->cmp_fecha'");

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }

        $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
		where cue_codigo in('$cue_cod_inv','$cue_cod_gasto_dif') and cde_mon_id='$cmp->cmp_mon_id'
		and	cde_cmp_id='$cmp->cmp_id'
		order by cde_mon_id asc, cde_secuencia asc;";

        $dets = FUNCIONES::lista_bd_sql($sql_dets);

        $arr_sent_sql = array();

        foreach ($dets as $det) {

            if ($det->cue_codigo == $cue_cod_gasto_dif) {
                $signo = 1;
            } else {
                $signo = (-1);
            }

            $val_bol = round($valor * $tc * $signo, 6);

            $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
            $arr_sent_sql[] = $sql_upd_det . "\n";

            $cambios->reset();
            for ($k = 0; $k < $cambios->get_num_registros(); $k++) {
                $cambio = $cambios->get_objeto();
                $cde_mon_id = $cambio->tca_mon_id;
                if ($tcambios[$cde_mon_id] > 0) {
                    $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                } else {
                    $cde_valor = $val_bol / $cambio->tca_valor;
                }

                $cde_valor = round($cde_valor, 6);
                $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                $arr_sent_sql[] = $sql_upd_det . "\n";

                $cambios->siguiente();
            }
        }

        $fp = fopen($nom_arch, 'a');

        foreach ($arr_sent_sql as $s_sql) {
            fputs($fp, $s_sql);
        }

        fclose($fp);
    }
}

function ajustar_cmp_venta($venta, $nom_arch) {
    $sql = "select * from con_comprobante 
	where cmp_tabla='venta' and cmp_tabla_id='$venta->ven_id'
	and cmp_eliminado='No'";

    $cmp = FUNCIONES::objeto_bd_sql($sql);

    if ($cmp) {

        $referido = $cmp->cmp_referido;
        $glosa = $cmp->cmp_glosa;

        $ges_id = FUNCIONES::gestion_id($venta->ven_fecha);
        $ven_fecha = $venta->ven_fecha;
        $moneda = $venta->ven_moneda;
        $llave = $venta->ven_id;
        $urb = clone $venta;
        $ven_anticipo = $venta->ven_res_anticipo;
        $monto_efectivo = $venta->ven_monto_efectivo;
        $monto_intercambio = $venta->ven_monto_intercambio;
        $monto_pagar = $venta->ven_monto_pagar;
        $costo = $venta->ven_costo;
        $costo_cub = ($ven_anticipo > $costo) ? $costo : $ven_anticipo;
        $precio_prod = $venta->ven_monto_producto;
        $monto = $venta->ven_monto;

        $total_venta = $monto + $precio_prod;
        $prorat_lote = ($monto / $total_venta);

        if ($precio_prod > 0) {
            $prorat_prod = ($precio_prod / $total_venta);
            $costo_prod_cub = $venta->ven_costo_producto_cub;
        }

        $costo_producto = $venta->ven_costo_producto;
        $costo_prod_cub = $venta->ven_costo_producto_cub;
        $ven_can_codigo = $venta->ven_can_codigo;
        $descuento = $venta->ven_decuento;
        $is_fecha_mod = false;

        $data = array(
            'moneda' => $moneda,
            'ges_id' => $ges_id,
            'fecha' => $ven_fecha,
            'glosa' => $glosa,
            'interno' => $referido,
            'tabla_id' => $llave,
            'urb' => $urb,
            'anticipo' => $ven_anticipo,
            'saldo_efectivo' => $monto_efectivo,
            'monto_intercambio' => $monto_intercambio,
            'intercambio_ids' => explode($venta->ven_intercambio_ids),
            'intercambio_montos' => explode($venta->ven_intercambio_montos),
            'monto_pagar' => $monto_pagar,
            'costo' => $costo,
            'costo_cub' => $costo_cub,
            'monto_producto' => $precio_prod,
            'monto_venta' => $monto,
            'prorat_lote' => $prorat_lote,
            'prorat_producto' => $prorat_prod,
            'costo_producto' => $costo_producto,
            'costo_producto_cub' => $costo_prod_cub,
            'ven_can_codigo' => $ven_can_codigo,
            'descuento' => $descuento,
        );

        $comprobante = MODELO_COMPROBANTE::venta($data);
        $comprobante->usu_id = $cmp->cmp_usu_cre;
        $comprobante->usu_per_id = $cmp->cmp_usu_id;
        $comprobante->cmp_id = $cmp->cmp_id;
        $comprobante->is_fecha_mod = $is_fecha_mod;

        $conec = null;
        ob_start();

        COMPROBANTES::modificar_comprobante($comprobante, $conec, false);

        $contenido = ob_get_contents();
        $contenido = str_replace('<p>', '', $contenido);
        $contenido = str_replace('</p>', PHP_EOL, $contenido);

        ob_end_clean();

        $fp = fopen($nom_arch, 'a');
        fputs($fp, $contenido);

        $sql_upd_venta = "update venta set ven_costo_cub='$costo_cub' where ven_id='$venta->ven_id';\n";
        fputs($fp, $sql_upd_venta);

        fclose($fp);
    }
}

function ajustar_cmp_pagos_migracion_old_version($venta, $nom_arch) {
    $sql_pagos = "select * from venta_pago 
	inner join con_comprobante on(cmp_tabla='venta_pago_migracion' and cmp_tabla_id=vpag_id)
	where cmp_eliminado='No' 
	and vpag_estado='Activo' 
	and vpag_capital>0
	and vpag_ven_id=$venta->ven_id
	order by vpag_fecha_pago asc";

    $cmps = FUNCIONES::objetos_bd_sql($sql_pagos);

    $arr_sent_sql = array();

    FUNCIONES::print_pre($venta);
    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {

        $cmp = $cmps->get_objeto();
        $pago = clone $cmp;
        $urb = clone $venta;

        $cue_cod_gasto_dif = $urb->urb_costo_diferido;
        $cue_cod_costo = $urb->urb_costo;

        $cue_cod_gasto_dif_prod = $urb->urb_cue_costo_diferido_producto;
        $cue_cod_costo_prod = $urb->urb_cue_costo_producto;

        $saldo_costo = $venta->ven_costo - $venta->ven_costo_cub;
        $saldo_efectivo = $venta->ven_monto_efectivo;

        $costo_pagado = 0;
        $costo_pagado_producto = 0;

        if ($venta->ven_prod_id > 0) {
            // echo "<p>entrando por ven_prod_id > 0</p>";
            $saldo_costo_producto = $venta->ven_costo_producto - $venta->ven_costo_producto_cub;
            $saldo_producto = $venta->ven_monto_producto;

            $saldo_total = $venta->ven_monto_efectivo + $venta->ven_monto_producto;

            $prorat_lote = $venta->ven_monto_efectivo / $saldo_total;
            $prorat_casa = $venta->ven_monto_producto / $saldo_total;

            $costo_pagado = $pago->vpag_capital * $prorat_lote * ($saldo_costo / $saldo_efectivo);
            $costo_pagado_producto = $pago->vpag_capital * $prorat_casa * ($saldo_costo_producto / $saldo_producto);
        } else {
            // echo "<p>entrando por NO ven_prod_id > 0</p>";
            if ($venta->vprod_id != null) {

                // FUNCIONES::print_pre($pago);
                // echo "<p>entrando por vprod_id != null</p>";
                $saldo_costo_producto = $venta->vprod_costo - $venta->vprod_costo_cub;
                $saldo_producto = $venta->vprod_monto_efectivo;

                $costo_pagado = $pago->vpag_capital * ($saldo_costo / $saldo_efectivo);
                $costo_pagado_producto = ($pago->vpag_vprod_id != null) ? $pago->vpag_capital_prod * ($saldo_costo_producto / $saldo_producto) : 0;
            } else {
                // echo "<p>entrando por vprod_id == null</p>";
                $costo_pagado = $pago->vpag_capital * ($saldo_costo / $saldo_efectivo);
            }
        }

        // $saldo_costo = ($venta->ven_costo - $venta->ven_costo_cub);
        // $saldo_efectivo = $venta->ven_monto_efectivo;
        // $costo_pagado = $pago->vpag_capital * ($saldo_costo / $saldo_efectivo);

        $valor = $costo_pagado;
        $valor2 = $costo_pagado_producto;

        $cmp_mon_id = $cmp->cmp_mon_id;
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp->cmp_fecha'");

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }

        $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
		where cue_codigo in(
			'$cue_cod_costo','$cue_cod_gasto_dif','$cue_cod_costo_prod','$cue_cod_gasto_dif_prod'
		) 
		and cde_mon_id='$cmp->cmp_mon_id'
		and	cde_cmp_id='$cmp->cmp_id'
		order by cde_mon_id asc, cde_secuencia asc;";

        $dets = FUNCIONES::lista_bd_sql($sql_dets);

        echo "<p>costo_pagado:$costo_pagado - costo_pagado_producto:$costo_pagado_producto - pag_id:$pago->vpag_id</p>";

        $arr_monedas_valor = array();
        $tipo_costo = "";
        foreach ($dets as $det) {

            if ($det->cue_codigo == $cue_cod_costo) {
                $signo = 1;
                $valor_calc = $valor;
                $tipo_costo = "lote";
            } else if ($det->cue_codigo == $cue_cod_gasto_dif) {
                $signo = (-1);
                $valor_calc = $valor;
                $tipo_costo = "lote";
            }

            if ($det->cue_codigo == $cue_cod_costo_prod) {
                $signo = 1;
                $valor_calc = $valor2;
                $tipo_costo = "producto";
            } else if ($det->cue_codigo == $cue_cod_gasto_dif_prod) {
                $signo = (-1);
                $valor_calc = $valor2;
                $tipo_costo = "producto";
            }

            $val_bol = round($valor_calc * $tc * $signo, 6);

            $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
            $arr_sent_sql[] = $sql_upd_det . "\n";

            if ($signo == 1) {
                $arr_monedas_valor[$tipo_costo][1] = $val_bol;
            }

            $cambios->reset();
            for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                $cambio = $cambios->get_objeto();
                $cde_mon_id = $cambio->tca_mon_id;
                if ($tcambios[$cde_mon_id] > 0) {
                    $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                } else {
                    $cde_valor = $val_bol / $cambio->tca_valor;
                }

                $cde_valor = round($cde_valor, 6);
                $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                $arr_sent_sql[] = $sql_upd_det . "\n";

                if ($signo == 1) {
                    $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                }

                $cambios->siguiente();
            }
        }

        $val_costo = $arr_monedas_valor['lote'][$pago->vpag_moneda];
        $sql_upd_pago = "update venta_pago set vpag_costo='$val_costo' where vpag_id='$pago->vpag_id';\n";
        $arr_sent_sql[] = $sql_upd_pago;

        if (isset($arr_monedas_valor['producto'][$pago->vpag_moneda])) {

            $val_costo = $arr_monedas_valor['producto'][$pago->vpag_moneda];
            $sql_upd_pago = "update venta_pago set vpag_costo_prod='$val_costo' where vpag_id='$pago->vpag_id';\n";
            $arr_sent_sql[] = $sql_upd_pago;
        }

        $cmps->siguiente();
    }

    $fp = fopen($nom_arch, 'a');

    foreach ($arr_sent_sql as $s_sql) {
        fputs($fp, $s_sql);
    }

    fclose($fp);
}

function ajustar_cmp_pagos_migracion($venta, $nom_arch) {

    $saldo_costo = ($venta->ven_costo > $venta->ven_costo_cub) ? $venta->ven_costo - $venta->ven_costo_cub : 0;
    $saldo_efectivo = $venta->ven_monto_efectivo;

    if ($venta->ven_prod_id > 0) {
        // echo "<p>entrando por ven_prod_id > 0</p>";
        $saldo_costo_producto = ($venta->ven_costo_producto > $venta->ven_costo_producto_cub) ? $venta->ven_costo_producto - $venta->ven_costo_producto_cub : 0;

        $saldo_producto = $venta->ven_monto_producto;

        $saldo_total = $venta->ven_monto_efectivo + $venta->ven_monto_producto;

        $prorat_lote = $venta->ven_monto_efectivo / $saldo_total;
        $prorat_casa = $venta->ven_monto_producto / $saldo_total;
    } else {
        if ($venta->vprod_id != null) {

            // echo "<p>entrando por vprod_id != null</p>";
            $saldo_costo_producto = ($venta->vprod_costo > $venta->vprod_costo_cub) ? $venta->vprod_costo - $venta->vprod_costo_cub : 0;

            $saldo_producto = $venta->vprod_monto_efectivo;
        }
    }

    if (($saldo_costo + $saldo_costo_producto) == 0) {
        return false;
    }

    $dif_cambio_egr = "6.3.1.01.1.04";
    $dif_cambio_ing = "4.2.1.01.1.01";

    $urb = clone $venta;
    $cue_cod_gasto_dif = $urb->urb_costo_diferido;
    $cue_cod_costo = $urb->urb_costo;

    $cue_cod_gasto_dif_prod = $urb->urb_cue_costo_diferido_producto;
    $cue_cod_costo_prod = $urb->urb_cue_costo_producto;

    $sql_ins_plan = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values";

    $sql_pagos = "select * from venta_pago 
	inner join con_comprobante on(cmp_tabla='venta_pago_migracion' and cmp_tabla_id=vpag_id)
	where cmp_eliminado='No' 
	and vpag_estado='Activo' 
	and vpag_capital>0
	and vpag_ven_id=$venta->ven_id
	order by vpag_fecha_pago asc";

    $cmps = FUNCIONES::objetos_bd_sql($sql_pagos);

    $arr_sent_sql = array();

    // FUNCIONES::print_pre($venta);
    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {

        $arr_cuentas_cubrir = array();

        $cmp = $cmps->get_objeto();
        $pago = clone $cmp;

        $costo_pagado = 0;
        $costo_pagado_producto = 0;


        if ($venta->ven_prod_id > 0) {
            // echo "<p>entrando por ven_prod_id > 0</p>";

            $costo_pagado = $pago->vpag_capital * $prorat_lote * ($saldo_costo / $saldo_efectivo);
            $costo_pagado_producto = $pago->vpag_capital * $prorat_casa * ($saldo_costo_producto / $saldo_producto);
        } else {
            // echo "<p>entrando por NO ven_prod_id > 0</p>";
            if ($venta->vprod_id != null) {

                $costo_pagado = $pago->vpag_capital * ($saldo_costo / $saldo_efectivo);
                $costo_pagado_producto = ($pago->vpag_vprod_id != null) ? $pago->vpag_capital_prod * ($saldo_costo_producto / $saldo_producto) : 0;
            } else {
                // echo "<p>entrando por vprod_id == null</p>";
                $costo_pagado = $pago->vpag_capital * ($saldo_costo / $saldo_efectivo);
            }
        }

        $valor = $costo_pagado;
        $valor2 = $costo_pagado_producto;

        $cmp_mon_id = $cmp->cmp_mon_id;
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp->cmp_fecha'");

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }

        echo "<p>costo_pagado:$costo_pagado - costo_pagado_producto:$costo_pagado_producto - pag_id:$pago->vpag_id</p>";

        $arr_monedas_valor = array();
        $tipo_costo = "";
        $can_codigo_id = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta->ven_can_codigo);

        $dets_a_crear = array();

        if ($valor > 0) {
            $dets_a_crear['lote'] = 'lote';
        }

        if ($valor2 > 0) {
            $dets_a_crear['producto'] = 'producto';
        }

        if ($valor > 0) {

            $arr_cuentas_cubrir[$cue_cod_costo] = $cue_cod_costo;
            $arr_cuentas_cubrir[$cue_cod_gasto_dif] = $cue_cod_gasto_dif;

            $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
			where cue_codigo in(
				'$cue_cod_costo','$cue_cod_gasto_dif'
			) 		
			and	cde_cmp_id='$cmp->cmp_id'
			and	cde_mon_id='$cmp->cmp_mon_id'
			order by cde_mon_id asc, cde_secuencia asc;";

            $dets = FUNCIONES::lista_bd_sql($sql_dets);

            foreach ($dets as $det) {
                $proceder = false;
                if ($det->cue_codigo == $cue_cod_costo) {
                    $signo = 1;
                    $valor_calc = $valor;
                    $tipo_costo = "lote";
                    unset($arr_cuentas_cubrir[$cue_cod_costo]);
                    $proceder = true;
                } else if ($det->cue_codigo == $cue_cod_gasto_dif) {
                    $signo = (-1);
                    $valor_calc = $valor;
                    $tipo_costo = "lote";
                    unset($arr_cuentas_cubrir[$cue_cod_gasto_dif]);
                    $proceder = true;
                }

                if ($proceder) {
                    $val_bol = round($valor_calc * $tc * $signo, 6);

                    $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                    $arr_sent_sql[] = $sql_upd_det . "\n";
                    unset($dets_a_crear['lote']);

                    if ($signo == 1) {
                        $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                    }

                    $cambios->reset();
                    for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                        $cambio = $cambios->get_objeto();
                        $cde_mon_id = $cambio->tca_mon_id;
                        if ($tcambios[$cde_mon_id] > 0) {
                            $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                        } else {
                            $cde_valor = $val_bol / $cambio->tca_valor;
                        }

                        $cde_valor = round($cde_valor, 6);
                        $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                        $arr_sent_sql[] = $sql_upd_det . "\n";

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                        }

                        $cambios->siguiente();
                    }
                }
            }
        }

        if ($valor2 > 0) {

            $arr_cuentas_cubrir[$cue_cod_costo_prod] = $cue_cod_costo_prod;
            $arr_cuentas_cubrir[$cue_cod_gasto_dif_prod] = $cue_cod_gasto_dif_prod;

            $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
			where cue_codigo in(
				'$cue_cod_costo_prod','$cue_cod_gasto_dif_prod'
			) 		
			and	cde_cmp_id='$cmp->cmp_id'
			and	cde_mon_id='$cmp->cmp_mon_id'
			order by cde_mon_id asc, cde_secuencia asc;";

            $dets = FUNCIONES::lista_bd_sql($sql_dets);

            foreach ($dets as $det) {
                $proceder = false;
                if ($det->cue_codigo == $cue_cod_costo_prod) {
                    $signo = 1;
                    $valor_calc = $valor2;
                    $tipo_costo = "producto";
                    unset($arr_cuentas_cubrir[$cue_cod_costo_prod]);
                    $proceder = true;
                } else if ($det->cue_codigo == $cue_cod_gasto_dif_prod) {
                    $signo = (-1);
                    $valor_calc = $valor2;
                    $tipo_costo = "producto";
                    unset($arr_cuentas_cubrir[$cue_cod_gasto_dif_prod]);
                    $proceder = true;
                }

                if ($proceder) {
                    $val_bol = round($valor_calc * $tc * $signo, 6);

                    $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                    $arr_sent_sql[] = $sql_upd_det . "\n";
                    unset($dets_a_crear['producto']);

                    if ($signo == 1) {
                        $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                    }

                    $cambios->reset();
                    for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                        $cambio = $cambios->get_objeto();
                        $cde_mon_id = $cambio->tca_mon_id;
                        if ($tcambios[$cde_mon_id] > 0) {
                            $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                        } else {
                            $cde_valor = $val_bol / $cambio->tca_valor;
                        }

                        $cde_valor = round($cde_valor, 6);
                        $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                        $arr_sent_sql[] = $sql_upd_det . "\n";

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                        }

                        $cambios->siguiente();
                    }
                }
            }
        }

        // if (count($arr_cuentas_cubrir) > 0) {
        // foreach ($arr_cuentas_cubrir as $cod_cuenta => $value) {
        // echo "<p>NO EXISTEN DETALLES PARA $value...</p>";
        // }
        // } else {
        // echo "<p>SE CUBRIERON TODAS LAS CUENTAS...</p>";
        // }

        if (count($dets_a_crear) > 0) {
            foreach ($dets_a_crear as $key => $value) {
                echo "<p>NO EXISTEN DETALLES PARA $value...</p>";

                $sql_dets_dif_cambio = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
				where cue_codigo in('$dif_cambio_egr','$dif_cambio_ing') and cde_cmp_id='$cmp->cmp_id'";

                $dets_dif = FUNCIONES::lista_bd_sql($sql_dets_dif_cambio);

                if (count($dets_dif) > 0) {

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

                if ($value == 'lote') {
                    for ($ijk = 1; $ijk <= 2; $ijk++) {
                        $max_index++;
                        if ($ijk == 1) {
                            $signo = 1;
                            $valor_calc = $valor;
                            $tipo_costo = "lote";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_costo);
                        } else {
                            $signo = (-1);
                            $valor_calc = $valor;
                            $tipo_costo = "lote";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_gasto_dif);
                        }

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                        }

                        $val_bol = round($valor_calc * $tc * $signo, 6);

                        $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','1','$max_index','$can_codigo_id','0','0','$cue_id','$val_bol','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                        $arr_sent_sql[] = $sql_ins . "\n";

                        $cambios->reset();
                        for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                            $cambio = $cambios->get_objeto();
                            $cde_mon_id = $cambio->tca_mon_id;
                            if ($tcambios[$cde_mon_id] > 0) {
                                $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                            } else {
                                $cde_valor = $val_bol / $cambio->tca_valor;
                            }

                            $cde_valor = round($cde_valor, 6);
                            $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','$cde_mon_id','$max_index','$can_codigo_id','0','0','$cue_id','$cde_valor','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                            $arr_sent_sql[] = $sql_ins . "\n";

                            if ($signo == 1) {
                                $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                            }

                            $cambios->siguiente();
                        }
                    }
                }

                if ($value == 'producto') {
                    for ($ijk = 1; $ijk <= 2; $ijk++) {
                        $max_index++;
                        if ($ijk == 1) {
                            $signo = 1;
                            $valor_calc = $valor2;
                            $tipo_costo = "producto";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_costo_prod);
                        } else {
                            $signo = (-1);
                            $valor_calc = $valor2;
                            $tipo_costo = "producto";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_gasto_dif_prod);
                        }

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                        }

                        $val_bol = round($valor_calc * $tc * $signo, 6);

                        $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','1','$max_index','$can_codigo_id','0','0','$cue_id','$val_bol','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                        $arr_sent_sql[] = $sql_ins . "\n";

                        $cambios->reset();
                        for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                            $cambio = $cambios->get_objeto();
                            $cde_mon_id = $cambio->tca_mon_id;
                            if ($tcambios[$cde_mon_id] > 0) {
                                $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                            } else {
                                $cde_valor = $val_bol / $cambio->tca_valor;
                            }

                            $cde_valor = round($cde_valor, 6);
                            $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','$cde_mon_id','$max_index','$can_codigo_id','0','0','$cue_id','$cde_valor','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                            $arr_sent_sql[] = $sql_ins . "\n";

                            if ($signo == 1) {
                                $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                            }

                            $cambios->siguiente();
                        }
                    }
                }
            }
        } else {
            echo "<p>SE CUBRIERON TODAS LAS CUENTAS...</p>";
        }

        $val_costo = $arr_monedas_valor['lote'][$pago->vpag_moneda];
        $sql_upd_pago = "update venta_pago set vpag_costo='$val_costo' where vpag_id='$pago->vpag_id';\n";
        $arr_sent_sql[] = $sql_upd_pago;

        if (isset($arr_monedas_valor['producto'][$pago->vpag_moneda])) {

            $val_costo = $arr_monedas_valor['producto'][$pago->vpag_moneda];
            $sql_upd_pago = "update venta_pago set vpag_costo_prod='$val_costo' where vpag_id='$pago->vpag_id';\n";
            $arr_sent_sql[] = $sql_upd_pago;
        }

        $cmps->siguiente();
    }

    $fp = fopen($nom_arch, 'a');

    foreach ($arr_sent_sql as $s_sql) {
        fputs($fp, $s_sql);
    }

    fclose($fp);
}

function ajustar_cmp_pagos_desc_inc_old_version($venta, $nom_arch) {
    $sql_pagos = "select * from venta_pago 
	inner join con_comprobante on(cmp_tabla='venta_pago_desc_inc' and cmp_tabla_id=vpag_id)
	where cmp_eliminado='No' 
	and vpag_estado='Activo' 
	and vpag_capital_desc>0
	and vpag_ven_id=$venta->ven_id
	order by vpag_fecha_pago asc";

    $cmps = FUNCIONES::objetos_bd_sql($sql_pagos);

    $arr_sent_sql = array();

    FUNCIONES::print_pre($venta);
    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {

        $cmp = $cmps->get_objeto();
        $pago = clone $cmp;
        $urb = clone $venta;

        $cue_cod_gasto_dif = $urb->urb_costo_diferido;
        $cue_cod_costo = $urb->urb_costo;

        $cue_cod_gasto_dif_prod = $urb->urb_cue_costo_diferido_producto;
        $cue_cod_costo_prod = $urb->urb_cue_costo_producto;

        $saldo_costo = $venta->ven_costo - $venta->ven_costo_cub;
        $saldo_efectivo = $venta->ven_monto_efectivo;

        $costo_pagado = 0;
        $costo_pagado_producto = 0;

        if ($venta->ven_prod_id > 0) {
            // echo "<p>entrando por ven_prod_id > 0</p>";
            $saldo_costo_producto = $venta->ven_costo_producto - $venta->ven_costo_producto_cub;
            $saldo_producto = $venta->ven_monto_producto;

            $saldo_total = $venta->ven_monto_efectivo + $venta->ven_monto_producto;

            $prorat_lote = $venta->ven_monto_efectivo / $saldo_total;
            $prorat_casa = $venta->ven_monto_producto / $saldo_total;

            $costo_pagado = $pago->vpag_capital_desc * $prorat_lote * ($saldo_costo / $saldo_efectivo);
            $costo_pagado_producto = $pago->vpag_capital_desc * $prorat_casa * ($saldo_costo_producto / $saldo_producto);
        } else {
            $costo_pagado = $pago->vpag_capital_desc * ($saldo_costo / $saldo_efectivo);
        }

        $valor = $costo_pagado;
        $valor2 = $costo_pagado_producto;

        $cmp_mon_id = $cmp->cmp_mon_id;
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp->cmp_fecha'");

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }

        $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
		where cue_codigo in(
			'$cue_cod_costo','$cue_cod_gasto_dif','$cue_cod_costo_prod','$cue_cod_gasto_dif_prod'
		) 
		and cde_mon_id='$cmp->cmp_mon_id'
		and	cde_cmp_id='$cmp->cmp_id'
		order by cde_mon_id asc, cde_secuencia asc;";

        $dets = FUNCIONES::lista_bd_sql($sql_dets);

        echo "<p>costo_pagado:$costo_pagado - costo_pagado_producto:$costo_pagado_producto - pag_id:$pago->vpag_id</p>";

        $arr_monedas_valor = array();
        $tipo_costo = "";
        foreach ($dets as $det) {

            if ($det->cue_codigo == $cue_cod_costo) {
                $signo = 1;
                $valor_calc = $valor;
                $tipo_costo = "lote";
            } else if ($det->cue_codigo == $cue_cod_gasto_dif) {
                $signo = (-1);
                $valor_calc = $valor;
                $tipo_costo = "lote";
            }

            if ($det->cue_codigo == $cue_cod_costo_prod) {
                $signo = 1;
                $valor_calc = $valor2;
                $tipo_costo = "producto";
            } else if ($det->cue_codigo == $cue_cod_gasto_dif_prod) {
                $signo = (-1);
                $valor_calc = $valor2;
                $tipo_costo = "producto";
            }

            $val_bol = round($valor_calc * $tc * $signo, 6);

            $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
            $arr_sent_sql[] = $sql_upd_det . "\n";

            if ($signo == 1) {
                $arr_monedas_valor[$tipo_costo][1] = $val_bol;
            }

            $cambios->reset();
            for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                $cambio = $cambios->get_objeto();
                $cde_mon_id = $cambio->tca_mon_id;
                if ($tcambios[$cde_mon_id] > 0) {
                    $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                } else {
                    $cde_valor = $val_bol / $cambio->tca_valor;
                }

                $cde_valor = round($cde_valor, 6);
                $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                $arr_sent_sql[] = $sql_upd_det . "\n";

                if ($signo == 1) {
                    $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                }

                $cambios->siguiente();
            }
        }

        $val_costo = $arr_monedas_valor['lote'][$pago->vpag_moneda];
        $sql_upd_pago = "update venta_pago set vpag_costo='$val_costo' where vpag_id='$pago->vpag_id';\n";
        $arr_sent_sql[] = $sql_upd_pago;

        if (isset($arr_monedas_valor['producto'][$pago->vpag_moneda])) {

            $val_costo = $arr_monedas_valor['producto'][$pago->vpag_moneda];
            $sql_upd_pago = "update venta_pago set vpag_costo_prod='$val_costo' where vpag_id='$pago->vpag_id';\n";
            $arr_sent_sql[] = $sql_upd_pago;
        }

        $cmps->siguiente();
    }

    $fp = fopen($nom_arch, 'a');

    foreach ($arr_sent_sql as $s_sql) {
        fputs($fp, $s_sql);
    }

    fclose($fp);
}

function ajustar_cmp_pagos_desc_inc($venta, $nom_arch) {

    $saldo_costo = ($venta->ven_costo > $venta->ven_costo_cub) ? $venta->ven_costo - $venta->ven_costo_cub : 0;
    $saldo_efectivo = $venta->ven_monto_efectivo;

    if ($venta->ven_prod_id > 0) {
        // echo "<p>entrando por ven_prod_id > 0</p>";
        $saldo_costo_producto = ($venta->ven_costo_producto > $venta->ven_costo_producto_cub) ? $venta->ven_costo_producto - $venta->ven_costo_producto_cub : 0;

        $saldo_producto = $venta->ven_monto_producto;

        $saldo_total = $venta->ven_monto_efectivo + $venta->ven_monto_producto;

        $prorat_lote = $venta->ven_monto_efectivo / $saldo_total;
        $prorat_casa = $venta->ven_monto_producto / $saldo_total;
    } else {
        if ($venta->vprod_id != null) {

            // echo "<p>entrando por vprod_id != null</p>";
            $saldo_costo_producto = ($venta->vprod_costo > $venta->vprod_costo_cub) ? $venta->vprod_costo - $venta->vprod_costo_cub : 0;

            $saldo_producto = $venta->vprod_monto_efectivo;
        }
    }

    if (($saldo_costo + $saldo_costo_producto) == 0) {
        return false;
    }

    $dif_cambio_egr = "6.3.1.01.1.04";
    $dif_cambio_ing = "4.2.1.01.1.01";

    $urb = clone $venta;
    $cue_cod_gasto_dif = $urb->urb_costo_diferido;
    $cue_cod_costo = $urb->urb_costo;

    $cue_cod_gasto_dif_prod = $urb->urb_cue_costo_diferido_producto;
    $cue_cod_costo_prod = $urb->urb_cue_costo_producto;

    $sql_ins_plan = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values";

    $sql_pagos = "select * from venta_pago 
	inner join con_comprobante on(cmp_tabla='venta_pago_desc_inc' and cmp_tabla_id=vpag_id)
	where cmp_eliminado='No' 
	and vpag_estado='Activo' 
	and vpag_capital_desc>0
	and vpag_ven_id=$venta->ven_id
	order by vpag_fecha_pago asc";

    $cmps = FUNCIONES::objetos_bd_sql($sql_pagos);

    $arr_sent_sql = array();

    // FUNCIONES::print_pre($venta);
    for ($i = 0; $i < $cmps->get_num_registros(); $i++) {

        $arr_cuentas_cubrir = array();

        $cmp = $cmps->get_objeto();
        $pago = clone $cmp;

        $costo_pagado = 0;
        $costo_pagado_producto = 0;


        if ($venta->ven_prod_id > 0) {
            // echo "<p>entrando por ven_prod_id > 0</p>";

            $costo_pagado = $pago->vpag_capital_desc * $prorat_lote * ($saldo_costo / $saldo_efectivo);
            $costo_pagado_producto = $pago->vpag_capital_desc * $prorat_casa * ($saldo_costo_producto / $saldo_producto);
        } else {
            $costo_pagado = $pago->vpag_capital_desc * ($saldo_costo / $saldo_efectivo);
        }

        $valor = $costo_pagado;
        $valor2 = $costo_pagado_producto;

        $cmp_mon_id = $cmp->cmp_mon_id;
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp->cmp_fecha'");

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }

        echo "<p>costo_pagado:$costo_pagado - costo_pagado_producto:$costo_pagado_producto - pag_id:$pago->vpag_id</p>";

        $arr_monedas_valor = array();
        $tipo_costo = "";
        $can_codigo_id = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta->ven_can_codigo);

        $dets_a_crear = array();

        if ($valor > 0) {
            $dets_a_crear['lote'] = 'lote';
        }

        if ($valor2 > 0) {
            $dets_a_crear['producto'] = 'producto';
        }

        if ($valor > 0) {

            $arr_cuentas_cubrir[$cue_cod_costo] = $cue_cod_costo;
            $arr_cuentas_cubrir[$cue_cod_gasto_dif] = $cue_cod_gasto_dif;

            $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
			where cue_codigo in(
				'$cue_cod_costo','$cue_cod_gasto_dif'
			) 		
			and	cde_cmp_id='$cmp->cmp_id'
			and	cde_mon_id='$cmp->cmp_mon_id'
			order by cde_mon_id asc, cde_secuencia asc;";

            $dets = FUNCIONES::lista_bd_sql($sql_dets);

            foreach ($dets as $det) {
                $proceder = false;
                if ($det->cue_codigo == $cue_cod_costo) {
                    $signo = 1;
                    $valor_calc = $valor;
                    $tipo_costo = "lote";
                    unset($arr_cuentas_cubrir[$cue_cod_costo]);
                    $proceder = true;
                } else if ($det->cue_codigo == $cue_cod_gasto_dif) {
                    $signo = (-1);
                    $valor_calc = $valor;
                    $tipo_costo = "lote";
                    unset($arr_cuentas_cubrir[$cue_cod_gasto_dif]);
                    $proceder = true;
                }

                if ($proceder) {
                    $val_bol = round($valor_calc * $tc * $signo, 6);

                    $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                    $arr_sent_sql[] = $sql_upd_det . "\n";
                    unset($dets_a_crear['lote']);

                    if ($signo == 1) {
                        $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                    }

                    $cambios->reset();
                    for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                        $cambio = $cambios->get_objeto();
                        $cde_mon_id = $cambio->tca_mon_id;
                        if ($tcambios[$cde_mon_id] > 0) {
                            $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                        } else {
                            $cde_valor = $val_bol / $cambio->tca_valor;
                        }

                        $cde_valor = round($cde_valor, 6);
                        $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                        $arr_sent_sql[] = $sql_upd_det . "\n";

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                        }

                        $cambios->siguiente();
                    }
                }
            }
        }

        if ($valor2 > 0) {

            $arr_cuentas_cubrir[$cue_cod_costo_prod] = $cue_cod_costo_prod;
            $arr_cuentas_cubrir[$cue_cod_gasto_dif_prod] = $cue_cod_gasto_dif_prod;

            $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
			where cue_codigo in(
				'$cue_cod_costo_prod','$cue_cod_gasto_dif_prod'
			) 		
			and	cde_cmp_id='$cmp->cmp_id'
			and	cde_mon_id='$cmp->cmp_mon_id'
			order by cde_mon_id asc, cde_secuencia asc;";

            $dets = FUNCIONES::lista_bd_sql($sql_dets);

            foreach ($dets as $det) {
                $proceder = false;
                if ($det->cue_codigo == $cue_cod_costo_prod) {
                    $signo = 1;
                    $valor_calc = $valor2;
                    $tipo_costo = "producto";
                    unset($arr_cuentas_cubrir[$cue_cod_costo_prod]);
                    $proceder = true;
                } else if ($det->cue_codigo == $cue_cod_gasto_dif_prod) {
                    $signo = (-1);
                    $valor_calc = $valor2;
                    $tipo_costo = "producto";
                    unset($arr_cuentas_cubrir[$cue_cod_gasto_dif_prod]);
                    $proceder = true;
                }

                if ($proceder) {
                    $val_bol = round($valor_calc * $tc * $signo, 6);

                    $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                    $arr_sent_sql[] = $sql_upd_det . "\n";
                    unset($dets_a_crear['producto']);

                    if ($signo == 1) {
                        $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                    }

                    $cambios->reset();
                    for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                        $cambio = $cambios->get_objeto();
                        $cde_mon_id = $cambio->tca_mon_id;
                        if ($tcambios[$cde_mon_id] > 0) {
                            $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                        } else {
                            $cde_valor = $val_bol / $cambio->tca_valor;
                        }

                        $cde_valor = round($cde_valor, 6);
                        $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                        $arr_sent_sql[] = $sql_upd_det . "\n";

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                        }

                        $cambios->siguiente();
                    }
                }
            }
        }

        // if (count($arr_cuentas_cubrir) > 0) {
        // foreach ($arr_cuentas_cubrir as $cod_cuenta => $value) {
        // echo "<p>NO EXISTEN DETALLES PARA $value...</p>";
        // }
        // } else {
        // echo "<p>SE CUBRIERON TODAS LAS CUENTAS...</p>";
        // }

        if (count($dets_a_crear) > 0) {
            foreach ($dets_a_crear as $key => $value) {
                echo "<p>NO EXISTEN DETALLES PARA $value...</p>";

                $sql_dets_dif_cambio = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
				where cue_codigo in('$dif_cambio_egr','$dif_cambio_ing') and cde_cmp_id='$cmp->cmp_id'";

                $dets_dif = FUNCIONES::lista_bd_sql($sql_dets_dif_cambio);

                if (count($dets_dif) > 0) {

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

                if ($value == 'lote') {
                    for ($ijk = 1; $ijk <= 2; $ijk++) {
                        $max_index++;
                        if ($ijk == 1) {
                            $signo = 1;
                            $valor_calc = $valor;
                            $tipo_costo = "lote";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_costo);
                        } else {
                            $signo = (-1);
                            $valor_calc = $valor;
                            $tipo_costo = "lote";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_gasto_dif);
                        }

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                        }

                        $val_bol = round($valor_calc * $tc * $signo, 6);

                        $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','1','$max_index','$can_codigo_id','0','0','$cue_id','$val_bol','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                        $arr_sent_sql[] = $sql_ins . "\n";

                        $cambios->reset();
                        for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                            $cambio = $cambios->get_objeto();
                            $cde_mon_id = $cambio->tca_mon_id;
                            if ($tcambios[$cde_mon_id] > 0) {
                                $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                            } else {
                                $cde_valor = $val_bol / $cambio->tca_valor;
                            }

                            $cde_valor = round($cde_valor, 6);
                            $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','$cde_mon_id','$max_index','$can_codigo_id','0','0','$cue_id','$cde_valor','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                            $arr_sent_sql[] = $sql_ins . "\n";

                            if ($signo == 1) {
                                $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                            }

                            $cambios->siguiente();
                        }
                    }
                }

                if ($value == 'producto') {
                    for ($ijk = 1; $ijk <= 2; $ijk++) {
                        $max_index++;
                        if ($ijk == 1) {
                            $signo = 1;
                            $valor_calc = $valor2;
                            $tipo_costo = "producto";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_costo_prod);
                        } else {
                            $signo = (-1);
                            $valor_calc = $valor2;
                            $tipo_costo = "producto";
                            $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_cod_gasto_dif_prod);
                        }

                        if ($signo == 1) {
                            $arr_monedas_valor[$tipo_costo][1] = $val_bol;
                        }

                        $val_bol = round($valor_calc * $tc * $signo, 6);

                        $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','1','$max_index','$can_codigo_id','0','0','$cue_id','$val_bol','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                        $arr_sent_sql[] = $sql_ins . "\n";

                        $cambios->reset();
                        for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                            $cambio = $cambios->get_objeto();
                            $cde_mon_id = $cambio->tca_mon_id;
                            if ($tcambios[$cde_mon_id] > 0) {
                                $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                            } else {
                                $cde_valor = $val_bol / $cambio->tca_valor;
                            }

                            $cde_valor = round($cde_valor, 6);
                            $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','$cde_mon_id','$max_index','$can_codigo_id','0','0','$cue_id','$cde_valor','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                            $arr_sent_sql[] = $sql_ins . "\n";

                            if ($signo == 1) {
                                $arr_monedas_valor[$tipo_costo][$cde_mon_id] = $cde_valor;
                            }

                            $cambios->siguiente();
                        }
                    }
                }
            }
        } else {
            echo "<p>SE CUBRIERON TODAS LAS CUENTAS...</p>";
        }

        $val_costo = $arr_monedas_valor['lote'][$pago->vpag_moneda];
        $sql_upd_pago = "update venta_pago set vpag_costo='$val_costo' where vpag_id='$pago->vpag_id';\n";
        $arr_sent_sql[] = $sql_upd_pago;

        if (isset($arr_monedas_valor['producto'][$pago->vpag_moneda])) {

            $val_costo = $arr_monedas_valor['producto'][$pago->vpag_moneda];
            $sql_upd_pago = "update venta_pago set vpag_costo_prod='$val_costo' where vpag_id='$pago->vpag_id';\n";
            $arr_sent_sql[] = $sql_upd_pago;
        }

        $cmps->siguiente();
    }

    $fp = fopen($nom_arch, 'a');

    foreach ($arr_sent_sql as $s_sql) {
        fputs($fp, $s_sql);
    }

    fclose($fp);
}

function ajustar_cmp_retencion($venta, $nom_arch) {
    $sql = "select * from venta_negocio 	
	inner join con_comprobante on(
		cmp_tabla='venta_retencion' 
		and cmp_tabla_id=vneg_ven_id 
		and cmp_tarea_id=vneg_id
	)	
	where cmp_eliminado='No' and vneg_ven_id='$venta->ven_id'";

    $obj_vnegs = FUNCIONES::objetos_bd_sql($sql);

    $conec = new ADO();

    for ($i = 0; $i < $obj_vnegs->get_num_registros(); $i++) {

        $obj_vneg = $obj_vnegs->get_objeto();

        if ($obj_vneg) {
            // $venta = clone $obj_vneg;
            $vneg_id = $obj_vneg->vneg_id;

            $params = array();
            $fecha_ini = $venta->ven_fecha;
            $fecha_fin = $obj_vneg->vneg_fecha;

            if ($fecha_ini != '' || $fecha_fin != '') {
                if ($fecha_ini != '') {
                    $params[rango_fechas]->fecha_ini = $fecha_ini;
                }

                if ($fecha_fin != '') {
                    $params[rango_fechas]->fecha_fin = $fecha_fin;
                }
            }

            $pagado = FUNCIONES::total_pagado($venta->ven_id, (object) $params);
            $tot_pagado = $venta->ven_res_anticipo + ($venta->ven_venta_pagado * 1) + $pagado->capital;
            $saldo = ($venta->ven_monto_efectivo + $pagado->incremento) - ($pagado->capital + $pagado->descuento);

            $monto_intercambio = $venta->ven_monto_intercambio;

            $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto 
			from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");

            $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto 
			from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id 
			group by vipag_inter_id order by inter_id asc");

            $obj_json = json_decode($obj_vneg->vneg_parametros);

            $params = array(
                'costo_old' => $obj_json->costo,
                'costo_pagado_old' => $obj_json->costo_pagado,
                'saldo_efectivo_old' => $obj_json->saldo_efectivo,
                'total_pagado_old' => $obj_json->total_pagado,
                'intercambio_old' => $obj_json->intercambio,
                'inter_montos_old' => $obj_json->inter_montos,
                'inter_montos_pag_old' => $obj_json->inter_montos_pag,
                'costo' => $venta->ven_costo,
                'costo_pagado' => $venta->ven_costo_cub + $pagado->costo,
                'saldo_efectivo' => $saldo,
                'total_pagado' => $tot_pagado,
                'intercambio' => $monto_intercambio,
                'inter_montos' => $amontos,
                'inter_montos_pag' => $amontos_pag,
            );

            FUNCIONES::print_pre($params);

            $s_json = json_encode($params);

            $cmp = clone $obj_vneg;

            $referido = $cmp->cmp_referido;
            $glosa = $cmp->cmp_glosa;
            $urb = clone $venta;

            $fecha_cmp = $cmp->cmp_fecha;
            $is_fecha_mod = false;
            $ademas_fecha_vneg = "";

            if ($new_fecha_cmp != '') {
                if ($new_fecha_cmp != $cmp->cmp_fecha) {
                    $fecha_cmp = $new_fecha_cmp;
                    $is_fecha_mod = true;
                    $ademas_fecha_vneg = ", vneg_fecha='$fecha_cmp'";
                }
            }

            $ges_id = FUNCIONES::gestion_id($fecha_cmp);

            $data = array(
                'moneda' => $cmp->cmp_mon_id,
                'ges_id' => $ges_id,
                'fecha' => $fecha_cmp,
                'glosa' => $glosa,
                'interno' => $referido,
                'tabla_id' => $cmp->cmp_tabla_id,
                'tarea_id' => $vneg_id,
                'urb' => $urb,
                'costo' => $venta->ven_costo,
                'costo_pagado' => $venta->ven_costo_cub + $pagado->costo,
                'saldo_efectivo' => $saldo,
                'total_pagado' => $tot_pagado,
                'intercambio' => $monto_intercambio,
                'inter_montos' => $amontos,
                'inter_montos_pag' => $amontos_pag,
                'ven_can_codigo' => $venta->ven_can_codigo,
            );

            if ($urb->urb_tipo == 'Interno') {
                $comprobante = MODELO_COMPROBANTE::venta_retencion($data);
            } else if ($urb->urb_tipo == 'Externo') {
                $comprobante = MODELO_COMPROBANTE::venta_retencion_ext($data);
            }

            $comprobante->cmp_id = $cmp->cmp_id;
            $comprobante->usu_id = $cmp->cmp_usu_cre;
            $comprobante->usu_per_id = $cmp->cmp_usu_id;
            $comprobante->is_fecha_mod = $is_fecha_mod;

            ob_start();
            COMPROBANTES::modificar_comprobante($comprobante, $conec, false);

            $contenido = ob_get_contents();
            $contenido = str_replace('<p>', '', $contenido);
            $contenido = str_replace('</p>', PHP_EOL, $contenido);

            ob_end_clean();

            $fp = fopen($nom_arch, 'a');
            fputs($fp, $contenido);

            $sql_upd = "update venta_negocio set vneg_parametros='$s_json' $ademas_fecha_vneg where vneg_id='$vneg_id';\n";

            echo "<p>$sql_upd</p>";
            fputs($fp, $sql_upd);
            fclose($fp);
        }

        $obj_vnegs->siguiente();
    }
}

function ajustar_cmp_activacion($venta, $nom_arch) {
    $sql = "select * from venta_negocio 	
		inner join con_comprobante on(
			cmp_tabla='venta_activacion' 
			and cmp_tabla_id=vneg_ven_id 
			and cmp_tarea_id=vneg_id
		)	
		where cmp_eliminado='No' and vneg_ven_id='$venta->ven_id' order by vneg_fecha asc";

    $activaciones = FUNCIONES::objetos_bd_sql($sql);

    $urb = clone $venta;

    for ($i = 0; $i < $activaciones->get_num_registros(); $i++) {
        $act = $activaciones->get_objeto();
        $cmp = clone $act;

        $sql_ret = "select * from venta_negocio where vneg_tipo='reversion' 
			and vneg_ven_id=$venta->ven_id and vneg_id<'$act->vneg_id' 
			and vneg_estado='Activado'
			order by vneg_id desc limit 1";

        $vneg_reversion = FUNCIONES::objeto_bd_sql($sql_ret);

        if ($vneg_reversion) {
            $cmp_ret = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_retencion' 
				and cmp_tabla_id='$venta->ven_id' and cmp_tarea_id='$vneg_reversion->vneg_id'");

            if ($cmp_ret) {
                $rdetalles = FUNCIONES::lista_bd_sql("select * from con_comprobante_detalle where cde_cmp_id=$cmp_ret->cmp_id and cde_mon_id=$cmp_ret->cmp_mon_id");
                $data = array(
                    'moneda' => $venta->ven_moneda,
                    'ges_id' => $cmp->cmp_ges_id,
                    'fecha' => $cmp->cmp_fecha,
                    'glosa' => $cmp->cmp_glosa,
                    'interno' => $cmp->cmp_referido,
                    'tabla_id' => $venta->ven_id,
                    'tarea_id' => $cmp->cmp_tarea_id,
                    'urb' => $urb,
                    'rdetalles' => $rdetalles,
                    'ven_can_codigo' => $venta->ven_can_codigo,
                );

                $comprobante = MODELO_COMPROBANTE::venta_reactivacion($data);
                $comprobante->usu_id = $cmp->cmp_usu_cre;
                $comprobante->usu_per_id = $cmp->cmp_usu_id;
                $comprobante->cmp_id = $cmp->cmp_id;
                $comprobante->is_fecha_mod = false;

                $conec = null;

                ob_start();
                COMPROBANTES::modificar_comprobante($comprobante, $conec, false);
                $contenido = ob_get_contents();
                $contenido = str_replace('<p>', '', $contenido);
                $contenido = str_replace('</p>', PHP_EOL, $contenido);

                ob_end_clean();

                $fp = fopen($nom_arch, 'a');
                fputs($fp, $contenido);
            }
        }

        $activaciones->siguiente();
    }
}

function ajustar_cmp_fusion($venta, $nom_arch) {

    $sql = "select * from venta 
		inner join venta_negocio on(ven_id=vneg_ven_ori and vneg_estado='Activado')
		inner join con_comprobante on(cmp_tabla='venta_fusion' and cmp_tabla_id=vneg_ven_ori
		and vneg_id=cmp_tarea_id)
		where ven_estado='Fusionado' and cmp_eliminado='No' and ven_id='$venta->ven_id'";

    $fus = FUNCIONES::objeto_bd_sql($sql);

    $dif_cambio_egr = "6.3.1.01.1.04";
    $dif_cambio_ing = "4.2.1.01.1.01";

    $sql_ins_plan = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values";

    $arr_sent_sql = array();
    if ($fus) {

        $vneg_fusion = clone $fus;
        $venta_ori = clone $venta;
        $cmp = clone $fus;

        $urb_ori = clone $venta;
        $mon_ori = $venta->ven_moneda == '1' ? '_bs' : ($venta->ven_moneda == '2' ? '_usd' : '');

        $venta_des = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$vneg_fusion->vneg_ven_id'");
        $urb_des = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta_des->ven_urb_id'");

        $cue_ing_reversion_ori = $urb_ori->{"urb_ing_por_reversion$mon_ori"};
        $cue_costo_diferido_ori = $urb_ori->urb_costo_diferido;
        $cue_inv_diferido_adj_ori = $urb_ori->urb_inv_terrenos_adj;

        $cue_costo_des = $urb_des->urb_costo;
        $cue_costo_diferido_des = $urb_des->urb_costo_diferido;

        $can_id_ori = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta_ori->ven_can_codigo);
        $can_id_des = FUNCIONES::get_cuenta_ca($cmp->cmp_ges_id, $venta_des->ven_can_codigo);

        $totales_pagados_ori = FUNCIONES::total_pagado($venta_ori->ven_id);

        $costo_ori = $venta_ori->ven_costo;
        $costo_pagado_ori = $venta_ori->ven_costo_cub + $totales_pagados_ori->costo;
        $saldo_costo_ori = $costo_ori - $costo_pagado_ori;

        $params = json_decode($vneg_fusion->vneg_parametros);
        $capital_pagar_des = $params->tot_capital;

        $saldo_costo_des = $venta_des->ven_costo - $venta_des->ven_costo_cub;
        $costo_pagado_des = ($saldo_costo_des / $venta_des->ven_monto_efectivo) * $capital_pagar_des;

        echo "<p>costo_pagado_ori:$costo_pagado_ori - saldo_costo_ori:$saldo_costo_ori - costo_ori:$costo_ori - costo_pagado_des:$costo_pagado_des</p>";

        $valor1 = $costo_pagado_ori * (-1);
        $valor2 = $saldo_costo_ori * (-1);
        $valor3 = $costo_ori;

        $valor4 = $costo_pagado_des;
        $valor5 = $costo_pagado_des * (-1);

        $arr_cuentas_cubrir = array();

        if ($valor1 <> 0) {

            $ele = new stdClass();
            $ele->valor_calc = $valor1;
            $ele->cue_codigo = $cue_ing_reversion_ori;
            $ele->can_id = 0;
            $key = $ele->cue_codigo . "-" . $ele->can_id;
            $arr_cuentas_cubrir[$key] = $ele;
        }

        if ($valor2 <> 0) {

            $ele = new stdClass();
            $ele->valor_calc = $valor2;
            $ele->cue_codigo = $cue_costo_diferido_ori;
            $ele->can_id = $can_id_ori;
            $key = $ele->cue_codigo . "-" . $ele->can_id;
            $arr_cuentas_cubrir[$key] = $ele;
        }

        if ($valor3 <> 0) {

            $ele = new stdClass();
            $ele->valor_calc = $valor3;
            $ele->cue_codigo = $cue_inv_diferido_adj_ori;
            $ele->can_id = $can_id_ori;
            $key = $ele->cue_codigo . "-" . $ele->can_id;
            $arr_cuentas_cubrir[$key] = $ele;
        }

        if ($valor4 <> 0) {

            $ele = new stdClass();
            $ele->valor_calc = $valor4;
            $ele->cue_codigo = $cue_costo_des;
            $ele->can_id = $can_id_des;
            $key = $ele->cue_codigo . "-" . $ele->can_id;
            $arr_cuentas_cubrir[$key] = $ele;
        }

        if ($valor5 <> 0) {

            $ele = new stdClass();
            $ele->valor_calc = $valor5;
            $ele->cue_codigo = $cue_costo_diferido_des;
            $ele->can_id = $can_id_des;
            $key = $ele->cue_codigo . "-" . $ele->can_id;
            $arr_cuentas_cubrir[$key] = $ele;
        }

        FUNCIONES::print_pre($arr_cuentas_cubrir);

        $cmp_mon_id = $cmp->cmp_mon_id;
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp->cmp_fecha'");

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }

        $sql_dets = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
			where cue_codigo in(
				'$cue_ing_reversion_ori','$cue_costo_diferido_ori','$cue_inv_diferido_adj_ori',
				'$cue_costo_des','$cue_costo_diferido_des'
			) 		
			and	cde_cmp_id='$cmp->cmp_id'
			and	cde_mon_id='$cmp->cmp_mon_id'
			order by cde_mon_id asc, cde_secuencia asc;";

        echo "<p>sql_dets:$sql_dets</p>";

        $dets = FUNCIONES::lista_bd_sql($sql_dets);
        //	CONTINUAR AQUI CON LOS AJUSTES EN FUNCION DE LA CUENTE Y CUENTA ANALITICA

        $arr_valores_aceptados = array();
        foreach ($dets as $det) {
            $key_det = $det->cue_codigo . "-" . $det->cde_can_id;
            $ele = $arr_cuentas_cubrir[$key_det];
            $valor_calc = $ele->valor_calc;
            $arr_valores_aceptados[] = $ele;
            $key = $ele->cue_codigo . "-" . $ele->can_id;
            echo "<p>imprimiendo las claves:$key</p>";
            unset($arr_cuentas_cubrir[$key]);

            $val_bol = round($valor_calc * $tc, 6);

            $sql_upd_det = "update con_comprobante_detalle set cde_valor='$val_bol' where cde_mon_id='1' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
            $arr_sent_sql[] = $sql_upd_det . "\n";

            $cambios->reset();
            for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                $cambio = $cambios->get_objeto();
                $cde_mon_id = $cambio->tca_mon_id;
                if ($tcambios[$cde_mon_id] > 0) {
                    $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                } else {
                    $cde_valor = $val_bol / $cambio->tca_valor;
                }

                $cde_valor = round($cde_valor, 6);
                $sql_upd_det = "update con_comprobante_detalle set cde_valor='$cde_valor' where cde_mon_id='$cde_mon_id' and cde_secuencia='$det->cde_secuencia' and cde_cue_id='$det->cde_cue_id' and cde_cmp_id='$det->cde_cmp_id';";
                $arr_sent_sql[] = $sql_upd_det . "\n";

                $cambios->siguiente();
            }
        }

        $dim_cuentas_cubrir = count($arr_cuentas_cubrir);
        echo "<p>arr_cuentas_cubrir";
        FUNCIONES::print_pre($arr_cuentas_cubrir);
        echo "</p>";
        if ($dim_cuentas_cubrir > 0) {

            echo "<p>dim_cuentas_cubrir:$dim_cuentas_cubrir</p>";
            $sql_dets_dif_cambio = "select * from con_comprobante_detalle inner join con_cuenta on(cde_cue_id=cue_id)
				where cue_codigo in('$dif_cambio_egr','$dif_cambio_ing') and cde_cmp_id='$cmp->cmp_id'";

            $dets_dif = FUNCIONES::lista_bd_sql($sql_dets_dif_cambio);

            if (count($dets_dif) > 0) {

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

            foreach ($arr_cuentas_cubrir as $ele) {
                $max_index++;
                $cue_codigo = $ele->cue_codigo;
                $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_codigo);
                $can_codigo_id = $ele->can_id;
                $valor_calc = $ele->valor_calc;

                $val_bol = round($valor_calc * $tc, 6);

                $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','1','$max_index','$can_codigo_id','0','0','$cue_id','$val_bol','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                $arr_sent_sql[] = $sql_ins . "\n";

                $cambios->reset();
                for ($k = 0; $k < $cambios->get_num_registros(); $k++) {

                    $cambio = $cambios->get_objeto();
                    $cde_mon_id = $cambio->tca_mon_id;
                    if ($tcambios[$cde_mon_id] > 0) {
                        $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                    } else {
                        $cde_valor = $val_bol / $cambio->tca_valor;
                    }

                    $cde_valor = round($cde_valor, 6);
                    $sql_ins = $sql_ins_plan . "('$cmp->cmp_id','$cde_mon_id','$max_index','$can_codigo_id','0','0','$cue_id','$cde_valor','$cmp->cmp_glosa','No','0','Efectivo','','','','$cmp->cmp_une_id','0');";
                    $arr_sent_sql[] = $sql_ins . "\n";

                    $cambios->siguiente();
                }
            }
        }
    }

    if (count($arr_sent_sql) > 0) {
        $fp = fopen($nom_arch, 'a');

        foreach ($arr_sent_sql as $s_sql) {
            fputs($fp, $s_sql);
        }

        $rastro = "-- SE ACTUALIZO CMP FUSION VENTA $venta->ven_id..\n";
        fputs($fp, $rastro);
        fclose($fp);
    }

    FUNCIONES::print_pre($arr_valores_aceptados);
}

function ajustar_cuenta_ingreso_fusion() {
    $sql = "select * from venta 
		inner join urbanizacion on(ven_urb_id=urb_id)
		inner join con_comprobante on(ven_id=cmp_tabla_id and cmp_tabla='venta_fusion')
		inner join con_comprobante_detalle on(cde_cmp_id=cmp_id and cde_mon_id=ven_moneda)
		inner join con_cuenta on(cde_cue_id=cue_id)
		where urb_id not in(5,6,12) and cmp_eliminado='No' and cue_codigo='4.1.4.01.2.01'
		and cde_secuencia=1";

    $time = date('dmY_His');
    $nom_arch = "imp_exp/ajustar_cuenta_ingreso_fusion_$time.sql";

    $regs = FUNCIONES::objetos_bd_sql($sql);
    $arr_sent_sql = array();
    for ($i = 0; $i < $regs->get_num_registros(); $i++) {

        $reg = $regs->get_objeto();
        $urb = clone $reg;
        $venta = clone $reg;
        $cmp = clone $reg;

        $mon_ori = $venta->ven_moneda == '1' ? '_bs' : ($venta->ven_moneda == '2' ? '_usd' : '');
        $cue_codigo_ing = $urb->{"urb_ing_por_reversion$mon_ori"};
        $cue_id = FUNCIONES::get_cuenta($cmp->cmp_ges_id, $cue_codigo_ing);

        $sql_upd = "update con_comprobante_detalle set cde_cue_id='$cue_id' where cde_cmp_id='$reg->cde_cmp_id' and cde_secuencia='$reg->cde_secuencia';\n";
        $arr_sent_sql[] = $sql_upd;

        $regs->siguiente();
    }

    if (count($arr_sent_sql) > 0) {
        $fp = fopen($nom_arch, 'w');

        foreach ($arr_sent_sql as $s_sql) {
            fputs($fp, $s_sql);
        }

        fputs($fp, $rastro);
        fclose($fp);
    }
}
