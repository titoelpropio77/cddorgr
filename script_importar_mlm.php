<!--<meta http-equiv="Content-Type" content="text/html; charset=utf-8">-->

<?php
require_once('conexion.php');

require_once('clases/coneccion.class.php');

require_once('clases/usuario.class.php');

require_once('clases/funciones.class.php');

require_once('clases/conversiones.class.php');

require_once('config/constantes.php');

require_once 'clases/registrar_comprobantes.class.php';



// internos(); //1
//ventas(); // 5
// insertar_plan_pagos(); //6
//insertar_pagos();
//importar_reservas();
//establecer_m2_reservas();
//establecer_m2_reservas_concretadas();
//establecer_ci_reservas();
//actualizar_venta();
//$array_ventas = array(8624,8640,8662,8665,8672);
//$array_ventas = array(8675,8689,8701,8707,8721);
//$array_ventas = array(8726,8732,8735,8739,8743,8743,8755,8758,8758,8778,8778,8796,8808,8863,8880,8915,8921,8936,8936,8942);
//$array_ventas = array(8743,8758,8778,8936);
//$array_ventas = array(8987,9057,9058,9066,9069,9078,9079,9080,9088,9098,9102,9103,9104,9107,9108,9110,9116,9127,9132,9146);
//$array_ventas = array(9156,9166,9167,9181,9190,9214,9228,9239,9248,9256,9290,9291,9293,9298,9299,9300,9306,9337,9360,9366);
//foreach ($array_ventas as $ven_id) {
//    insertar_pagos($ven_id);
//    actualizar_venta($ven_id);
//}
//resetear_venta("8831");
//resetear_venta("9200");
//resetear_venta("8782");
//resetear_venta("8684");
//resetear_venta("8794");
//resetear_venta("8912");
//resetear_venta("8862");
//resetear_venta("9210");
//resetear_venta("8832");
//resetear_venta("9228");
//resetear_venta("8794");
//resetear_venta("9135");
//resetear_venta("9144");
//resetear_venta("9069");
//resetear_venta("8679");
//resetear_venta("9080");
//resetear_venta("9174");
//resetear_venta("9185");
//resetear_venta("9134");
//resetear_venta("9168");
//resetear_venta("9158");
//resetear_venta("9189");
//resetear_venta("9152");
//resetear_venta("9199");
//resetear_venta("8730");
//resetear_venta("8732");
//resetear_venta("8752");
//resetear_venta("8724");
//resetear_venta("8617");
//resetear_venta("8614");
//resetear_venta("8936");
//resetear_venta("9151");
//resetear_venta("9198");
//resetear_venta("9223");
//resetear_venta("9224");
//resetear_venta("9225");
//resetear_venta("9042");
//resetear_venta("8794");
//resetear_venta("9069");
//resetear_venta("8789");
//resetear_venta("8790");
//resetear_venta("8777");
//resetear_venta("8767");
//resetear_venta("8980");
//resetear_venta("8744");
//resetear_venta("8959");
//resetear_venta("8882");
//resetear_venta("9278");
//resetear_venta("9052");
//resetear_venta("8696");
//resetear_venta("8754");
//resetear_venta("8722");
//resetear_venta("8704");
//resetear_venta("8677");
//resetear_venta("8662");
//resetear_venta("8666");
//resetear_venta("8670");
//resetear_venta("9085");
//resetear_venta("8685");
//resetear_venta("9334");
//resetear_venta("8709");
//resetear_venta("8762");
//resetear_venta("8723");
//resetear_venta("8731");
//resetear_venta("8787");
//resetear_venta("9247");
//resetear_venta("8748");
//resetear_venta("8726");
//resetear_venta("8623");
//resetear_venta("8734");
//resetear_venta("9266");
//resetear_venta("8625");
//resetear_venta("8762");

//resetear_venta("8612");
//resetear_venta("8775");

/*----RESETEO EN 25/08/2017----*/
resetear_venta("8758");
resetear_venta("8772");
resetear_venta("8884");
resetear_venta("9209");
/*----RESETEO EN 25/08/2017----*/

function resetear_venta($ven_id) {
    $conec = new ADO();
    $sql_up = "update venta set  ven_costo_pag=0, ven_estado='Pendiente' where ven_id=$ven_id";
    $conec->ejecutar($sql_up);
    $sql_up = "delete from interno_deuda_pago where idp_vpag_id in (select vpag_id from venta_pago where vpag_ven_id=$ven_id);";
    $conec->ejecutar($sql_up);
    $sql_up = "update venta_pago set vpag_estado='Anulado' where vpag_ven_id=$ven_id";
    $conec->ejecutar($sql_up);
//  $sql_up="update interno_deuda set  ind_estado='Pendiente', ind_capital_pagado=0,ind_interes_pagado=0, ind_monto_pagado=0,ind_monto_parcial=0, ind_saldo_final=0,ind_costo_pagado=0  where ind_tabla_id=$ven_id";
//  $conec->ejecutar($sql_up);
    $sql_up = "delete from interno_deuda  where ind_tabla_id=$ven_id";
    $conec->ejecutar($sql_up);
    $sql_up = "delete from venta_cobro where vcob_ven_id=$ven_id";
    $conec->ejecutar($sql_up);
}

exit();

function actualizar_venta($ven_id = 0) {

    $conec = new ADO();



    if ($ven_id > 0) {

        $filtro = " and ven_id = $ven_id";
    }



    $sql_ventas = "select * from venta where ven_numero like 'NET-%' $filtro";

    $ventas = FUNCIONES::objetos_bd_sql($sql_ventas);



    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {

        $venta = $ventas->get_objeto();

        $upago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_ven_id='$venta->ven_id' and vpag_estado='Activo' order by vpag_id desc limit 0,1");

        if (!$upago) {

            $upago = new stdClass();

            $upago->vpag_fecha_pago = $venta->ven_fecha;

            $upago->vpag_fecha_valor = $venta->ven_fecha;

            $upago->vpag_saldo_final = $venta->ven_monto_efectivo;
        }



        $ucuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");

        if (!$ucuota) {

            $ucuota = new stdClass();

            $ucuota->ind_num_correlativo = 0;
        }



        $pagado = FUNCIONES::total_pagado_venta($venta->ven_id);



        $capital_pag = $pagado->capital;

        $capital_inc = $pagado->incremento;

        $capital_desc = $pagado->descuento;

        $cuota_pag = $ucuota->ind_num_correlativo;



        $_ucuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_num_correlativo>0 order by ind_id desc limit 1");



        $scuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 1");

        $ven_sfecha_prog = $scuota ? $scuota->ind_fecha_programada : '0000-00-00';

        $ven_cuota = $scuota->ind_monto;





        $sql_up = "update venta set 

                            

                            ven_ufecha_pago='$upago->vpag_fecha_pago', 

                            ven_ufecha_valor='$upago->vpag_fecha_valor', 

                            ven_usaldo='$upago->vpag_saldo_final',

                            ven_cuota_pag='$cuota_pag', 

                            ven_capital_pag='$capital_pag',

                            ven_capital_inc='$capital_inc', 

                            ven_capital_desc='$capital_desc',

                            ven_plazo='$_ucuota->ind_num_correlativo',

                            ven_cuota='$ven_cuota',

                            ven_rango='1',

                            ven_ufecha_prog='$_ucuota->ind_fecha_programada', 

                            ven_sfecha_prog='$ven_sfecha_prog'

                    

                where ven_id='$venta->ven_id'";

//        echo "$sql_up;<br>";



        $conec->ejecutar($sql_up, false, false);

        $ventas->siguiente();
    }
}

function importar_reservas() {

    $reservas = FUNCIONES::lista_bd_sql("select * from temp_reservas");

    $aurbs = array(
        'LUJAN' => '2',
        'BISITO' => '3',
        'OKINAWA' => '13',
        'LA QUINTA' => '11'
    );

    $conec = new ADO();

    $reg = 0;

    foreach ($reservas as $treserva) {

        $int_codigo = "NET-$treserva->cod_cliente";

        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_codigo='$int_codigo'");

        $urb_id = $aurbs[$treserva->urbanizacion];

        $sw = true;

        if (!$interno) {

            echo "NO EXISTE INTERNO $int_codigo<br>";

            $sw = false;
        }

//        continue;



        $sql_lote = "select * from lote

                        inner join manzano on (lot_man_id=man_id)

                        where man_urb_id='$urb_id' and man_nro='$treserva->mz' and lot_nro='$treserva->lote';

                    ";



        $lote = FUNCIONES::objeto_bd_sql($sql_lote);



        if (!$lote || $lote->lot_estado != 'Disponible') {

            $lot_estado = $lote->lot_estado;

            $_uv = FUNCIONES::objeto_bd_sql("select * from uv where uv_id='$lote->lot_uv_id'");

            $_zona = FUNCIONES::objeto_bd_sql("select * from zona where zon_id='$lote->lot_zon_id'");

            $lote_id = importar_lotes($urb_id, $_uv->uv_nombre, $_zona->zon_nombre, $treserva->mz, "NET-$treserva->lote", $lote->lot_superficie);

            $sql_lote = "select * from lote

                        inner join manzano on (lot_man_id=man_id)

                        where lot_id='$lote_id';

                    ";



            $lote = FUNCIONES::objeto_bd_sql($sql_lote);



            echo "-- no existe lote o esta $lot_estado ;$urb_id-$treserva->mz-$treserva->lote ---> LOTE ID: $lote_id<br>";
        }



        if (!$lote) {

            echo "-- NO EXISTE LOTE $treserva->urbanizacion - $treserva->mz - $treserva->lote <br>";

            $sw = false;
        } else {

            //            echo "-- EXISTE LOTE $tven->urbanizacion - $tven->mz - $tven->lote - $lote->lot_estado<br>";

            if ($lote->lot_estado == 'Vendido') {

                echo "-- EXISTE LOTE $treserva->urbanizacion - $treserva->mz - $treserva->lote - ($lote->lot_id) $lote->lot_estado<br>"; //echo "$treserva->cod_venta<br>";

                $sw = false;
            }
        }





        if ($lote && $lote->lot_estado == 'Disponible' && $sw) {



            $int_codigo_vdo = "NET-$treserva->cod_vendedor";

            $_vendedor = FUNCIONES::objeto_bd_sql("select * from interno where int_codigo='$int_codigo_vdo'");

            $txt_promotor = "$int_codigo_vdo | $_vendedor->int_nombre";



            $res_fecha = FUNCIONES::get_fecha_mysql($treserva->fecha_reserva);

            $res_estado = 'Pendiente';

            $monto_pag = $treserva->abono_reserva;

            if ($monto_pag > 0) {

                $res_estado = 'Habilitado';
            }

            $sql = "insert into reserva_terreno (

                        res_int_id,res_vdo_id,res_lot_id,res_fecha,res_hora,res_monto_referencial,

                        res_moneda,res_estado,res_usu_id,res_plazo_fecha,res_plazo_hora,res_nota, 

                        res_vdo_ext,res_urb_id,res_promotor,res_multinivel

                    ) values (

                        '$interno->int_id','0','$lote->lot_id','$res_fecha','00:00:00','0',

                        '2','$res_estado','admin','$res_fecha','00:00:00','',

                        '0','$urb_id','$txt_promotor','si'

                    )";

            $conec->ejecutar($sql, false, true);

            $reserva_id = ADO::$insert_id;





            $sql = "insert into reserva_pago(

                            respag_monto,respag_moneda,respag_fecha,respag_hora,

                            respag_estado,respag_usu_id,respag_glosa,respag_res_id,respag_recibo,

                            respag_suc_id

                        )values (

                            '$monto_pag','2','$res_fecha','00:00:00',

                            'Pagado','admin','Pago de reserva','$reserva_id','$treserva->cod_venta',

                            '1'

                        )";

            //echo $sql;

            $reg++;

            $conec->ejecutar($sql, true, true);
        } else {

            echo "-- no existe lote o esta $lote->lot_estado<br>";
        }
    }

    echo "-- TOTAL REGISTRADOS de " . count($reservas) . " $reg";
}

function actualizar_usuarios() {

    $conec = new ADO();

    $pagos = FUNCIONES::objetos_bd_sql("select * from venta_pago where vpag_importado=1 and vpag_monto>0 and vpag_fecha_pago>='2015-01-01' and vpag_fecha_pago<='2015-12-31' limit 100");

    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {

        $pag = $pagos->get_objeto();



        $venta = FUNCIONES::objeto_bd_sql("select ven_id, ven_urb_id from venta where ven_id=$pag->vpag_ven_id");

        $sql_sel = "select * from recibos where py='$venta->ven_urb_id' and numero_recibo='$pag->vpag_recibo'";

        $recibo = _db::objeto_sql($sql_sel);

        $_af = explode(' ', $recibo->fecha);

        $_fecha = $_af[0];

        if (!$recibo || $_fecha != $pag->vpag_fecha_pago) {

            $sql_sel = "select * from recibos where py='$venta->ven_urb_id' and numero_recibo_ini='$pag->vpag_recibo'";

            $recibo = _db::objeto_sql($sql_sel);
        }





        $_af = explode(' ', $recibo->fecha);

        $_fecha = $_af[0];

        if ($recibo && $_fecha == $pag->vpag_fecha_pago) {

            $sql_up = "update venta_pago set vpag_usu_import='$recibo->usuario' where vpag_id=$pag->vpag_id";

//            echo $sql_up.';<br>';

            $conec->ejecutar($sql_up, false, false);
        } else {

            echo "-- $sql_sel; <br>";

            echo "-- $_fecha==$pag->vpag_fecha_pago<br>";

            echo "-- no hay recibo $pag->vpag_id, $pag->vpag_monto, $pag->vpag_fecha_pago<br>";
        }





        $pagos->siguiente();
    }
}

function actualizar_cuota_plazo() {

    $ventas = FUNCIONES::lista_bd_sql("select * from venta limit 1");

    for ($i = 0; $i < count($ventas); $i++) {

        $venta = $ventas[$i];

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_tabla_id='$venta->ven_id' order by ind_id asc");

        $cuota = $cuotas[count($cuotas) - 2];

        $ven_cuota = $cuota->ind_monto;

        $ven_plazo = count($cuotas);

        $sql_up = "update venta set ven_plazo='$ven_plazo', ven_cuota='$ven_cuota' where ven_id='$venta->ven_id'";

        echo "$sql_up;<br>";
    }
}

function listado_observados() {

    $ventas = FUNCIONES::objetos_bd_sql("select * from venta where ven_id in (select distinct vpag_ven_id from venta_pago where vpag_capital_desc>0 and vpag_monto>0) order by ven_id asc ");

    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {

        $venta = $ventas->get_objeto();

        $tven = FUNCIONES::objeto_bd_sql("select * from tmp_ventas where py='$venta->ven_urb_id' and numero_adj='$venta->ven_numero'");

        if ($tven->estado == 'CREDITO') {

            $nombre = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id=$venta->ven_int_id");

            echo "$venta->ven_id;$venta->ven_urb_id;$venta->ven_numero;$nombre;$venta->ven_monto_efectivo;$tven->estado<br>";
        }

        $ventas->siguiente();
    }
}

function unir_pago(&$rec_pago, $tpag) {

    if ($tpag->Monto < 0) {

        $rec_pago->descuento = ($tpag->Monto + $tpag->Interes) * -1;

        $rec_pago->interes_desc = $tpag->Interes * -1;

        $rec_pago->form_desc = $tpag->Formularios * -1;



        $rec_pago->recibo = $tpag->Recibo;

        $af = explode(' ', $tpag->FechaDep);

        $rec_pago->fecha = $af[0];

        $rec_pago->plan_id = $tpag->Numero_Adj_His;
    } else {

        $rec_pago->monto = $tpag->Monto;

        $rec_pago->interes_pag = $tpag->Interes;

        $rec_pago->form_pag = $tpag->Formularios;



        $rec_pago->mora = $tpag->Multa;

        $rec_pago->mora_pagado = $tpag->Multa_Pagado;



        $rec_pago->recibo = $tpag->Recibo;

        $af = explode(' ', $tpag->FechaDep);

        $rec_pago->fecha = $af[0];

        $rec_pago->plan_id = $tpag->Numero_Adj_His;
    }
}

function registrar_pago($rec_pago, &$venta, &$conec = null) {

    $rec_pago = desglosar_pago($rec_pago);

//    if($rec_pago->nro_recibo=='58095'){
//        FUNCIONES::print_pre($rec_pago);
//    }



    $params = array(
        'venta' => $venta,
        'fecha_pago' => $rec_pago->fecha,
        'fecha_valor' => $rec_pago->fecha,
        'pago' => $rec_pago,
    );

    $nsaldo = generar_cobro($params);

    pagar_cuota($venta, $rec_pago->nro_recibo);



    if ($rec_pago->descuento > 0) {

        FUNCIONES::print_pre($rec_pago);

        reformular_plan_cuota($venta, false, $conec);
    }

    return $nsaldo;
}

function desglosar_pago($pago) {



    $_descuento = $pago->descuento + $pago->form_desc;



    if ($pago->interes_pag > $pago->monto) {

        $pago->interes_pag = ($pago->saldo_efectivo * $pago->porc_interes) / 100 / 12;

        if ($pago->interes_pag > $pago->monto) {

            $pago->interes_pag = 0;
        }
    }



    $_desc_monto = $_descuento - $pago->form_desc;

    $_cap_monto = $pago->monto + $pago->form_pag - $_descuento - ($pago->form_pag - $pago->form_desc);

    $pago->desc_monto = $_desc_monto;

    $pago->cap_monto = $_cap_monto;



    $pago->desc_form = $pago->form_desc;

    if ($_cap_monto > 0) {

        $pago->desc_interes = $pago->interes_pag * $_desc_monto / $pago->monto;

        if ($pago->desc_interes > $pago->interes_pag) {

            $pago->desc_interes = 0;
        }
    } else {

        $pago->desc_interes = 0;
    }



    $pago->desc_capital = $_desc_monto - $pago->desc_interes;

    $pago->desc_total = $pago->desc_form + $pago->desc_interes + $pago->desc_capital;





    $pago->pag_form = $pago->form_pag - $pago->form_desc;

    if ($pago->descuento > 0) {

        $pago->pag_interes = $pago->interes_pag - $pago->desc_interes;
    } else {

        $pago->pag_interes = $pago->interes_pag;
    }



    $pago->pag_capital = $_cap_monto - $pago->pag_interes;

    $pago->pag_total = $pago->pag_form + $pago->pag_interes + $pago->pag_capital;

    return $pago;
}

function generar_cobro($params, &$conec = null) {

//    [descuento] => 40.343
//    [interes_desc] => 0
//    [form_desc] => 2.127
//    [recibo] => 71167
//    [fecha] => 2015-02-23
//    [plan_id] => 723
//    [monto] => 704.093
//    [interes_pag] => 130.389
//    [form_pag] => 37.127
//    [mora] => 0
//    [mora_pagado] => 0
//    [desc_monto] => 40.343
//    [cap_monto] => 663.75
//    [desc_form] => 2.127
//    [desc_interes] => 7.9250974418079
//    [desc_capital] => 32.417902558192
//    [desc_total] => 42.47
//    [pag_form] => 35
//    [pag_interes] => 32.417902558192
//    [pag_capital] => 631.33209744181
//    [pag_total] => 698.75



    if ($conec == null) {

        $conec = new ADO();
    }

    $par = (object) $params;

    $venta = $par->venta;

    $pago = $par->pago;

    $cambio_usd = 1;

    if ($venta->ven_moneda == '1') {

        $cambio_usd = 6.96;
    }

    $codigo = FUNCIONES::fecha_codigo();

//    $fecha_pago = FUNCIONES::sumar_dias(15, $par->fecha_pago);
//    SUMAR CAPITAL

    $sql_cuotas_all = "select * from interno_deuda 

                            where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 

                            ind_estado='Pendiente' and ind_capital_pagado < ind_capital and ind_tipo='pcuota' order by ind_id asc";

    $acuotas = FUNCIONES::lista_bd_sql($sql_cuotas_all);

    $_sum_cap = 0;

    $tot_capial = ($pago->pag_capital . '') * 1;

    $cuotas_capital = array();



    if ($tot_capial > 0) {

        foreach ($acuotas as $acu) {

            $cuotas_capital[] = clone $acu;

            $_sum_cap+=($acu->ind_capital . '') * 1 - ($acu->ind_capital_pagado . '') * 1;

//            echo "$_sum_cap >= $tot_capial <br>";

            if ($_sum_cap >= $tot_capial) {

                break;
            }
        }
    } else {

        if (count($acuotas) > 0) {

            $cuotas_capital[] = clone $acuotas[0];
        }
    }



//    FUNCIONES::print_pre($cuotas_capital);
//    $cuotas_capital = FUNCIONES::lista_bd_sql($sql_cuotas_capital);



    $capital_desc = $pago->desc_capital;

    $capital = 0;

    $capital_ids = array();

    $capital_montos = array();

//    echo 'Cant '.count($cuotas_capital).' #######<br>';

    $fecha_valor = $cuotas_capital[count($cuotas_capital) - 1]->ind_fecha_programada;

    foreach ($cuotas_capital as $cu) {

        $mcapital = round($cu->ind_capital - $cu->ind_capital_pagado, 2);

//        echo "$cu->ind_id ****-----*** $mcapital<br>";

        if ($mcapital + $capital >= $pago->pag_capital) {

            $mcapital = round($pago->pag_capital - $capital, 2);
        }

        $capital+=$mcapital;

        $capital_ids[] = $cu->ind_id;

        $capital_montos[] = $mcapital;
    }

//    echo $capital.'=========================================<br>';
/// FORMULARIO

    $acu = $acuotas[0];

    $form_desc = $pago->desc_form;

    $form = $pago->pag_form;

    $form_ids = array();

    $form_montos = array();

    if ($form + $form_desc > 0) {

        if (count($acuotas) > 0) {

            $acu = $acuotas[0];



//            $scuota = FUNCIONES::objeto_bd_sql($sql_cuota);

            $form_ids = array($acu->ind_id);

//            $form_ids = array($scuota->ind_id);
        } else {

//            $form_ids = array($cuotas_capital[0]->ind_id);
        }

        $form_montos = array($form);
    }

    /// SUMAR MORA CONCRETADAS PENDIENTES



    $mora = 0;

    $mora_ids = array();

    $mora_montos = array();

    $mora_con_ids = array();

    $mora_con_montos = array();

    $mora_gen_ids = array();

    $mora_gen_montos = array();

    $mora_gen_dias = array();

    if (count($acuotas) > 0) {

        $acu = $acuotas[0];

        $mora = $pago->mora_pagado;

        $mora_ids = array($acu->ind_id);

        $mora_montos = array($mora);

        $mora_con_ids = array($acu->ind_id);

        $mora_con_montos = array($mora);



        $mora_gen_ids = array($acu->ind_id);

        $mora_gen_montos = array($pago->mora);

        $mora_gen_dias = array($pago->mora / 0.5);
    }



//        $saldo=0;
    // SUMAR INTERES

    $pagado = total_pagado($venta->ven_id);



    $saldo = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;



    $interes_desc = $pago->desc_interes;

    $interes = $pago->pag_interes;

    $interes_ids = array();

    $interes_montos = array();

    if ($interes > 0) {

        if (count($acuotas) > 0) {

            $acu = $acuotas[0];

//            echo '--------------*************';
//            FUNCIONES::print_pre($acu);

            $interes_ids = array($acu->ind_id);
        } else {

//            $interes_ids = array($cuotas_capital[0]->ind_id);
        }

        $interes_montos = array($interes);
    }

//        echo "$dif_dias*$interes_dia*$saldo=$interes <br>";

    $nsaldo = $saldo - $capital - $capital_desc;

    /*     * ********************************************************************** */



//        $monto=  round($interes+$capital, 2);



    $monto = round($interes + $capital + $mora + $form, 2);



//    if ($monto > 0) {

    $txt_interes_ids = implode(',', $interes_ids);

    $txt_capital_ids = implode(',', $capital_ids);

    $txt_mora_ids = implode(',', $mora_ids);

    $txt_mora_con_ids = implode(',', $mora_con_ids);

    $txt_mora_gen_ids = implode(',', $mora_gen_ids);

    $txt_form_ids = implode(',', $form_ids);

    $txt_interes_montos = implode(',', $interes_montos);

    $txt_capital_montos = implode(',', $capital_montos);

    $txt_mora_montos = implode(',', $mora_montos);

    $txt_mora_con_montos = implode(',', $mora_con_montos);

    $txt_mora_gen_montos = implode(',', $mora_gen_montos);

    $txt_mora_gen_dias = implode(',', $mora_gen_dias);

    $txt_form_montos = implode(',', $form_montos);



    $fecha_cre = date('Y-m-d H:i:s');

    $usuario_id = 'admin';

    $sql_cobro = "insert into venta_cobro(

                                vcob_ven_id,vcob_codigo,vcob_fecha_pago,vcob_fecha_valor,

                                vcob_int_id,vcob_moneda,vcob_saldo_inicial,vcob_dias_interes,

                                vcob_interes,vcob_capital,vcob_mora, vcob_form, vcob_monto,vcob_saldo_final,

                                vcob_interes_ids,vcob_capital_ids,vcob_form_ids,vcob_mora_ids,vcob_mora_con_ids,vcob_mora_gen_ids,

                                vcob_interes_montos,vcob_capital_montos,vcob_form_montos,vcob_mora_montos,vcob_mora_con_montos,vcob_mora_gen_montos,vcob_mora_gen_dias,

                                vcob_fecha_cre,vcob_usu_cre,vcob_aut,vcob_capital_desc,vcob_interes_desc,vcob_form_desc

                            )values(

                                '$venta->ven_id','$codigo','$par->fecha_pago','$fecha_valor',

                                '$venta->ven_int_id','$venta->ven_moneda','$saldo','0',

                                '$interes','$capital','$mora','$form','$monto','$nsaldo',

                                '$txt_interes_ids','$txt_capital_ids','$txt_form_ids','$txt_mora_ids','$txt_mora_con_ids','$txt_mora_gen_ids',

                                '$txt_interes_montos','$txt_capital_montos','$txt_form_montos','$txt_mora_montos','$txt_mora_con_montos','$txt_mora_gen_montos','$txt_mora_gen_dias',

                                '$fecha_cre','$usuario_id','0','$capital_desc','$interes_desc','$form_desc'

                            )";

//        echo $sql_cobro.';<br>';

    $conec->ejecutar($sql_cobro);

    return $nsaldo;

//    } else {
//        return false;
//    }
}

function total_pagado($ven_id) {

    $sql_pag = "select sum(ind_interes_pagado)as interes, sum(ind_capital_pagado) as capital, sum(ind_monto_pagado) as monto, sum(ind_capital_desc) as descuento, sum(ind_capital_inc) as incremento 

                        from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id 

                ";

    $pagado = FUNCIONES::objeto_bd_sql($sql_pag);

    return $pagado;
}

function reformular_plan_cuota(&$venta, $cuadrar_venta = false, &$conec = null) {

    if ($conec == null) {

        $conec = new ADO();
    }

    $pagado = total_pagado($venta->ven_id);

    $saldo_financiar = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;

    $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_capital_pagado>0 and ind_capital_pagado<=ind_capital and ind_estado='Pendiente'");

    $nro_inicio = 0;

    $fecha_inicio = $venta->ven_fecha;

    $afecha = explode('-', $fecha_inicio);

    $fecha_sig_cuota = FUNCIONES::sumar_meses($venta->ven_fecha, $venta->ven_rango, $afecha[2]); //FUNCIONES::siguiente_mes($venta->ven_fecha);



    if ($uid) {

        $sql_up = "update interno_deuda set ind_saldo_final=$saldo_financiar, ind_estado='Pagado' where ind_id='$uid->ind_id'";

        $conec->ejecutar($sql_up);

        $nro_inicio = $uid->ind_num_correlativo + 1;

//        echo "--ant--$uid->ind_fecha_programada <br>";

        $fecha_inicio = $uid->ind_fecha_programada;

        $afecha = explode('-', $fecha_inicio);

        $fecha_sig_cuota = FUNCIONES::sumar_meses($uid->ind_fecha_programada, $venta->ven_rango, $afecha[2]);

//        $fecha_sig_cuota=  FUNCIONES::siguiente_mes($uid->ind_fecha_programada);
//        echo "**post--$fecha_sig_cuota <br>";
    } else {

        if ($cuadrar_venta) {

            return false;
        }
    }

    if (!$nro_inicio) {

        $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pagado' order by ind_id desc limit 0,1");

        if ($uid) {

            $nro_inicio = $uid->ind_num_correlativo + 1;

            $fecha_inicio = $uid->ind_fecha_programada;

            $afecha = explode('-', $fecha_inicio);

            $fecha_sig_cuota = FUNCIONES::sumar_meses($uid->ind_fecha_programada, $venta->ven_rango, $afecha[2]);
        } else {

            $nro_inicio = 1;
        }
    }



    if ($venta->sig_fecha_pago) {

        $fecha_inicio = FUNCIONES::sumar_meses($venta->sig_fecha_pago, "-$venta->ven_rango", $afecha[2]);

        ;

        $fecha_sig_cuota = $venta->sig_fecha_pago;
    }



    $sql_del = "delete from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente'";

    $conec->ejecutar($sql_del);



    $interes_anual = $venta->ven_val_interes;



    $plan_data = array(
        'int_id' => $venta->ven_int_id,
        'saldo' => $saldo_financiar,
        'interes' => $interes_anual,
        'tipo' => 'cuota',
        'plazo' => $venta->ven_plazo,
        'cuota' => $venta->ven_cuota,
        'moneda' => $venta->ven_moneda,
        'concepto' => $venta->ven_concepto,
        'fecha' => date('Y-m-d'),
        'fecha_inicio' => $fecha_inicio,
        'fecha_pri_cuota' => $fecha_sig_cuota,
        'usuario' => 'admin',
        'tabla' => 'venta',
        'nro_cuota_inicio' => $nro_inicio,
        'tabla_id' => $venta->ven_id,
        'ind_tipo' => 'pcuota',
        'val_form' => 0,
        'rango' => $venta->ven_rango,
    );

//    echo "-------------- PLAN CUOTA <br>";
//    FUNCIONES::print_pre($plan_data);

    $nro_cuotas = generar_plan_pagos($plan_data, $conec); //
//    $cuota = FUNCIONES::get_cuota($saldo_financiar, $interes_anual, $plazo, $aplazo[$plan->Plazo]);

    $sql_up_venta = "update venta set ven_plazo=$nro_cuotas where ven_id=$venta->ven_id";

//    echo "res plazo : $nro_cuotas<br>";

    $venta->ven_plazo = $nro_cuotas;

    $conec->ejecutar($sql_up_venta);



    guardar_fecha_valor($venta, $fecha_inicio);
}

function guardar_fecha_valor($venta, $fecha_valor) {

    $conec = new ADO();

//    $fecha_valor=  FUNCIONES::get_fecha_mysql($_POST[nfecha_valor]);
//    $fecha_pago=date('Y-m-d');

    $codigo = FUNCIONES::fecha_codigo();

    $upago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");

    if (!$upago) {

        $upago = new stdClass();

        $upago->vpag_saldo_final = $venta->ven_monto_efectivo;

        $upago->vpag_fecha_valor = $venta->ven_fecha;

        $upago->vpag_fecha_pago = $venta->ven_fecha;
    }

    $fecha_cre = date('Y-m-d H:i:s');

    $insert_pago = "insert into venta_pago (

                        vpag_ven_id,vpag_codigo,vpag_fecha_pago,vpag_fecha_valor,vpag_int_id,vpag_moneda,

                        vpag_saldo_inicial,vpag_dias_interes,vpag_interes,vpag_capital,vpag_form,

                        vpag_mora,vpag_monto,vpag_saldo_final,vpag_estado,vpag_interes_ids,

                        vpag_interes_montos,vpag_capital_ids,vpag_capital_montos,

                        vpag_form_ids,vpag_form_montos,vpag_mora_ids,vpag_mora_montos,vpag_mora_con_ids,

                        vpag_mora_con_montos,vpag_mora_gen_ids,vpag_mora_gen_montos,vpag_mora_gen_dias,

                        vpag_fecha_cre,vpag_usu_cre,vpag_cob_usu,vpag_cob_codigo,vpag_cob_aut,

                        vpag_costo,vpag_costo_ids,vpag_costo_montos,vpag_capital_inc,vpag_capital_desc,

                        vpag_interes_desc,vpag_form_desc,vpag_recibo,vpag_importado

                    )values(

                        '$venta->ven_id','$codigo','$upago->vpag_fecha_pago','$fecha_valor','$venta->ven_int_id','$venta->ven_moneda',

                        '$upago->vpag_saldo_final',0,0,0,0,

                        0,0,'$upago->vpag_saldo_final','Activo','',

                        '','','0',

                        '','','','','',

                        '','','','',

                       '$fecha_cre','admin','admin','','0',

                        0,'','','0','0',

                        0,0,'','1'

                    )";

//        barra_opciones($venta);
//        echo "<br><br>";

    $conec->ejecutar($insert_pago, false);
}

function reformular_plan_venta(&$venta, &$conec = null) {

    if ($conec == null) {

        $conec = new ADO();
    }

    $saldo_financiar = $venta->ven_monto_efectivo;

    $plan = _db::objeto_sql("select * from plan_historico where PY=$venta->ven_urb_id and Numero_Adj=$venta->ven_numero order by  Numero_Adj_His asc");

    if (!$plan) {

        return;
    }

    $nro_inicio = 1;

    $sql_del = "delete from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente'";

    $conec->ejecutar($sql_del);



    if ($plan) {

        $aplazo = array('1' => 1, '2' => 2, '3' => 3, '4' => 6, '5' => 0);

        $interes_anual = $plan->Interes * 12;

        $plazo = $plan->Plan;

        if ($plazo <= 0) {

            $plazo = 1;
        }

//        $saldo_financiar = $venta->ven_monto_efectivo - $sum_cap_pagado;

        $af = explode(' ', $plan->Fecha_Historico);

        $fecha_inicio = $venta->ven_fecha;

        $fecha_pri_cuota = $af[0];

        $plan_data = array(
            'int_id' => $venta->ven_int_id,
            'saldo' => $saldo_financiar,
            'interes' => $interes_anual,
            'tipo' => 'plazo',
            'plazo' => $plazo,
            'cuota' => '',
            'moneda' => $venta->ven_moneda,
            'concepto' => $venta->ven_concepto,
            'fecha' => date('Y-m-d'),
            'fecha_inicio' => $fecha_inicio,
            'fecha_pri_cuota' => $fecha_pri_cuota,
            'usuario' => 'admin',
            'tabla' => 'venta',
            'nro_cuota_inicio' => $nro_inicio,
            'tabla_id' => $venta->ven_id,
            'ind_tipo' => 'pcuota',
            'val_form' => 0,
            'rango' => $aplazo[$plan->Plazo],
        );

        $nro_cuotas = generar_plan_pagos($plan_data, $conec); //



        $cuota = FUNCIONES::get_cuota($saldo_financiar, $interes_anual, $plazo, $aplazo[$plan->Plazo]);

        $sql_up_venta = "update venta set ven_plazo=$nro_cuotas,ven_cuota='$cuota', ven_rango='{$aplazo[$plan->Plazo]}', ven_val_interes=$interes_anual where ven_id=$venta->ven_id";

        $venta->ven_plazo = $nro_cuotas;

        $venta->ven_cuota = $cuota;

        $venta->ven_rango = $aplazo[$plan->Plazo];

        $venta->ven_val_interes = $interes_anual;

        $conec->ejecutar($sql_up_venta);



        return $plan;
    } else {

        return null;
    }
}

function reformular_plan(&$venta, $sum_cap_pagado, $nro_plan, &$conec = null) {

    if ($conec == null) {

        $conec = new ADO();
    }



    $pagado = total_pagado($venta->ven_id);

    $saldo_financiar = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;

    $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_capital_pagado>0 and ind_capital_pagado<=ind_capital and ind_estado='Pendiente'");

    $nro_inicio = 0;

    $fecha_inicio = $venta->ven_fecha;

    if ($uid) {

        $sql_up = "update interno_deuda set ind_saldo_final=$saldo_financiar, ind_estado='Pagado' where ind_id='$uid->ind_id'";

        $conec->ejecutar($sql_up);

        $nro_inicio = $uid->ind_num_correlativo + 1;

        $fecha_inicio = $uid->ind_fecha_programada;
    }

    if (!$nro_inicio) {

        $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pagado' order by ind_id desc limit 0,1");

        if ($uid) {

            $nro_inicio = $uid->ind_num_correlativo + 1;

            $fecha_inicio = $uid->ind_fecha_programada;
        } else {

            $nro_inicio = 1;
        }
    }



//    $pagado = total_pagado($venta->ven_id);
//    $saldo_financiar = $venta->ven_monto_efectivo - $pagado->capital;



    $sql_del = "delete from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente'";

    $conec->ejecutar($sql_del);

    $plan = _db::objeto_sql("select * from plan_historico where PY=$venta->ven_urb_id and Numero_Adj=$venta->ven_numero and Numero_Adj_His=$nro_plan");

    if ($plan) {

        $aplazo = array('1' => 1, '2' => 2, '3' => 3, '4' => 6, '5' => 0);

        $rango = $aplazo[$plan->Plazo];

        $interes_anual = $plan->Interes * 12;

        $plazo = $plan->Plan / $rango;

        if ($plazo <= 0) {

            $plazo = 1;
        }

//        $saldo_financiar = $venta->ven_monto_efectivo - $sum_cap_pagado;

        $af = explode(' ', $plan->Fecha_Historico);

        $fecha_pri_cuota = $af[0];

        $plan_data = array(
            'int_id' => $venta->ven_int_id,
            'saldo' => $saldo_financiar,
            'interes' => $interes_anual,
            'tipo' => 'plazo',
            'plazo' => $plazo,
            'cuota' => '',
            'moneda' => $venta->ven_moneda,
            'concepto' => $venta->ven_concepto,
            'fecha' => date('Y-m-d'),
            'fecha_inicio' => '',
            'fecha_inicio' => $fecha_inicio,
            'fecha_pri_cuota' => $fecha_pri_cuota,
            'usuario' => 'admin',
            'tabla' => 'venta',
            'nro_cuota_inicio' => $nro_inicio,
            'tabla_id' => $venta->ven_id,
            'ind_tipo' => 'pcuota',
            'val_form' => 0,
            'rango' => $rango,
        );



//        echo "-------------- PLAN DE PAGO PLAZO <BR>";
//        FUNCIONES::print_pre($plan_data);

        $nro_cuotas = generar_plan_pagos($plan_data, $conec); //



        $cuota = FUNCIONES::get_cuota($saldo_financiar, $interes_anual, $plazo, $rango);

        $sql_up_venta = "update venta set ven_plazo=$nro_cuotas,ven_cuota='$cuota', ven_rango='$rango', ven_val_interes=$interes_anual where ven_id=$venta->ven_id";

        $venta->ven_plazo = $nro_cuotas;

        $venta->ven_cuota = $cuota;

        $venta->ven_rango = $rango;

        $venta->ven_val_interes = $interes_anual;

        $conec->ejecutar($sql_up_venta);



        return $plan;
    } else {

        return null;
    }
}

function generar_plan_pagos($parametros, &$conec = null) {

    /*

      //            'int_id'=>$par->int_id,

      //            'saldo'=>$saldo_financiar,

      //            'interes'=>$par->interes_anual,

      //            'plazo'=>$_mp,

      //            'cuota'=>$_cm,

      //            'moneda'=>$par->moneda,

      //            'concepto'=>$par->concepto,

      //            'fecha'=>$par->fecha,

      //            'fecha_pri_pago'=>  $par->fecha_pri_pago,

      //            'usuario'=>  $usuario_id,

      //            'tabla'=> 'venta',

      //            'nro_cuota_inicio'=>  1,

      //            'tabla_id'=>  $par->ven_id,

      //            'ind_tipo'=>  'pcuota'

     */



    if ($conec == null) {

        $conec = new ADO();
    }

    $par = (object) $parametros;

    $lista_pagos = array();

    if ($par->tipo == "plazo") {//plazo
        $data = array(
            'tipo' => 'plazo',
            'plazo' => $par->plazo,
            'interes_anual' => $par->interes,
            'saldo' => $par->saldo,
            'fecha_inicio' => $par->fecha_inicio,
            'fecha_pri_cuota' => $par->fecha_pri_cuota,
            'nro_cuota_inicio' => $par->nro_cuota_inicio,
            'rango' => $par->rango,
            'frecuencia' => 'dia_mes'
        );

        $lista_pagos = FUNCIONES::plan_de_pagos($data);
    } elseif ($par->tipo == "cuota") {//cuota mensual
        $data = array(
            'tipo' => 'cuota',
            'cuota' => $par->cuota,
            'plazo' => $par->plazo,
            'interes_anual' => $par->interes,
            'saldo' => $par->saldo,
            'fecha_inicio' => $par->fecha_inicio,
            'fecha_pri_cuota' => $par->fecha_pri_cuota,
            'nro_cuota_inicio' => $par->nro_cuota_inicio,
            'rango' => $par->rango,
            'frecuencia' => 'dia_mes'
        );

        $lista_pagos = FUNCIONES::plan_de_pagos($data);
    }



//            FUNCIONES::print_pre($lista_pagos);
//            return;

    $sql_ins_id = "insert into interno_deuda(

                            ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_moneda,

                            ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,ind_estado,

                            ind_dias_interes,ind_concepto,ind_usu_id,ind_tipo,ind_fecha_cre,ind_form

                        )values";

    $fecha_cre = date('Y-m-d H:i:s');

    $i = 0;

    $nro_cuota = 0;

    foreach ($lista_pagos as $fila) {

        if ($i > 0) {

            $sql_ins_id.= ",";
        }

        $sql_ins_id.= "(

                            'venta','$par->tabla_id','$fila->nro_cuota','$par->int_id','$par->fecha','$par->moneda',

                            '$fila->interes','$fila->capital','$fila->monto','$fila->saldo','$fila->fecha','Pendiente',

                            '$fila->dias','Cuota Nro $fila->nro_cuota - $par->concepto','$par->usuario','pcuota','$fecha_cre','$par->val_form'

                        )";

        $nro_cuota = $fila->nro_cuota;

        $i++;
    }

//            $nro_cuota_inicio++;

    if (count($lista_pagos) > 0) {

        $conec->ejecutar($sql_ins_id);
    }



    return $nro_cuota;
}

function plan_de_pagos($parametros) {

    $params = (object) $parametros;

    $rango = $params->rango;

    $dias_mes = 30;

    if (!$rango)
        $rango = 1;



    $dias = $rango * $dias_mes;



    if ($params->tipo == 'plazo') {

        $plazo = $params->plazo;

        $interes_anual = $params->interes_anual;

        $saldo = $params->saldo;



        $fecha_pri_cuota = $params->fecha_pri_cuota;

        $nro_cuota = $params->nro_cuota_inicio;



        $interes_efectivo = (($rango * $interes_anual ) / 12) / 100;



        $cuota = FUNCIONES::get_cuota($saldo, $interes_anual, $plazo, $rango);

//            echo $cuota.'<br>';

        $lista_cuotas = array();

        $_afecha = explode('-', $fecha_pri_cuota);

        $dia = $_afecha[2];

//            $lista_cuotas[]=  FUNCIONES::primera_cuota($nro_cuota, $saldo, $fecha_partida,$fecha_pri_cuota, $interes_anual, $cuota);

        for ($i = 1; $i <= $plazo; $i++) {

            $fila = new stdClass();

            if ($i == 1) {

                $fecha = $fecha_pri_cuota;

                $dias = 30;
            } else {

                $fecha_ant = $fecha;

                $fecha = FUNCIONES::sumar_meses($fecha, "+$rango", $dia);

                $dias = FUNCIONES::diferencia_dias($fecha_ant, $fecha);
            }

            $fila->nro_cuota = $nro_cuota;

            $fila->dias = $dias;

            $fila->fecha = $fecha;



            $_interes = $saldo * $interes_efectivo;

            $interes = round($_interes, 2);



            if ($i == $plazo) {

                $capital = round($saldo, 2);

                $_capital = $saldo;
            } else {

                $capital = $cuota - $interes;

                $_capital = $cuota - $_interes;
            }



            $fila->monto = $interes + $capital;



            $fila->interes = $interes;

            $fila->capital = $capital;



            $saldo = $saldo - $capital;



            $fila->saldo = round($saldo, 2);

            $lista_cuotas[] = $fila;

            $nro_cuota++;
        }

        return $lista_cuotas;
    } elseif ($params->tipo == 'cuota') {

        $interes_anual = $params->interes_anual;

        $saldo = $params->saldo;

        $fecha_pri_cuota = $params->fecha_pri_cuota;

        $nro_cuota = $params->nro_cuota_inicio;

        $interes_efectivo = (($rango * $interes_anual ) / 12) / 100;

        $cuota = $params->cuota;



        $lista_cuotas = array();

        $_afecha = explode('-', $fecha_pri_cuota);

        $dia = $_afecha[2];

        $i = 1;

        while ($saldo > 0 && $i < 500) {

//            if($saldo<1){
//                echo $saldo.'<br>';
//            }

            $fila = new stdClass();

            if ($i == 1) {

                $fecha = $fecha_pri_cuota;
            } else {

                $fecha = FUNCIONES::sumar_meses($fecha, "+$rango", $dia);
            }

            $fila->nro_cuota = $nro_cuota;

            $fila->fecha = $fecha;



            $_interes = $saldo * $interes_efectivo;

            $interes = round($_interes, 2);



            if ($saldo < $cuota) {

                $capital = round($saldo, 2);

                $_capital = $saldo;
            } else {

                $capital = $cuota - $interes;

                $_capital = $cuota - $_interes;
            }



            $fila->interes = $interes;

            $fila->capital = $capital;

            $fila->monto = $capital + $interes;

            $saldo = round($saldo - $capital, 2);

            $fila->saldo = $saldo;

            $lista_cuotas[] = $fila;

            $nro_cuota++;

            $i++;
        }

        return $lista_cuotas;
    }
}

function insertar_pagos($ven_id = 0) {

    $conec = new ADO();



    $and_filtro = "";

    if ($ven_id > 0) {

        $and_filtro = " and ven_id = $ven_id";
    }



//    $ventas = FUNCIONES::objetos_bd_sql("select * from venta where ven_importado='1' and ven_numero like 'NET-%' and ven_estado in ('Pendiente','Pagado') $and_filtro limit 500");



    $sql_ventas = "select * from venta where ven_numero like 'NET-%' and ven_estado in ('Pendiente','Pagado') $and_filtro limit 500";

    $ventas = FUNCIONES::objetos_bd_sql($sql_ventas);

    echo '-- ' . count($ventas) . '<br>';

//    return;



    $reg = 0;



    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {



        $venta = $ventas->get_objeto();

//        $ven_id=$venta->ven_id;



        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_id='$venta->ven_int_id'");

        $aicod = explode('-', $interno->int_codigo);

        $cod_cliente = $aicod[1];



        $pagos = FUNCIONES::lista_bd_sql("select * from temp_extractos where cod_cliente='$cod_cliente' order by id asc");

        $num = 0;

        $ufecha_pago = '0000-00-00';

        $ufecha_valor = '';

//        $cuota_pag='';

        $usaldo = '';

        $planes = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id'");

        if (!count($planes) > 0) {

            $num_pagos = count($pagos);

            echo "NO TIENE PLAN DE PAGOS|$venta->ven_id|$venta->ven_numero|$num_pagos<br>";

            $ventas->siguiente();

            continue;
        }

//        $ventas->siguiente();
//        continue;
//        $sum_capital_pag=0;

        foreach ($pagos as $tpago) {



            if (true) {







                $rec_pago = new stdClass();



                $rec_pago->pag_capital = $tpago->capital;

                $rec_pago->desc_capital = 0;

                $rec_pago->desc_form = 0;

                $rec_pago->pag_form = 0;

                $rec_pago->mora_pagado = 0;

                $rec_pago->mora = 0;

                $rec_pago->fecha = get_fecha($tpago->fecha_pag);



                $params = array(
                    'venta' => $venta,
                    'fecha_pago' => $rec_pago->fecha,
                    'fecha_valor' => $rec_pago->fecha,
                    'pago' => $rec_pago,
                );



                $usaldo = generar_cobro($params);



                pagar_cuota($venta, $tpago->recibo, $tpago->usu_import);



                $ufecha_pago = $rec_pago->fecha;

                $ufecha_valor = $rec_pago->fecha;

                $num++;
            }
        }

//        $pricuota=FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_estado in ('Pendiente') and ind_tabla='venta' and ind_tabla_id='$ven_id' order by ind_id asc limit 1");
//        if($pricuota){
//            $sfecha_prog=$pricuota->ind_fecha_programada;
//        }else{
//            $sfecha_prog='0000-00-00';
//        }



        /*

          $sql_up = "update venta set

          ven_importado='2'

          where ven_id='$venta->ven_id'

          ";

          $conec->ejecutar($sql_up, false, false);

         */



//        FUNCIONES::bd_query($sql_insert);
//        if ($num > 0) {
////            echo "$sql_insert<br>";
//            FUNCIONES::bd_query($sql_insert);
//        }

        $reg++;

        $ventas->siguiente();
    }

    echo "-- <BR>TOTAL REGISTRADOS $reg";
}

function pagar_cuota($venta, $recibo, $usu_import) {

    $conec = new ADO();

    $conec->begin_transaccion();

//        $codigo = $_POST[cob_codigo];

    $cob = FUNCIONES::objeto_bd_sql("select * from venta_cobro where vcob_ven_id='$venta->ven_id' ");



    if (!$cob) {

        $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";



        return false;
    }



    $str_interes_ids = trim($cob->vcob_interes_ids);

    if ($str_interes_ids && $cob->vcob_interes > 0) {

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_interes_ids) and ind_tipo='pcuota' order by ind_id asc");

        $interes_ids = explode(',', trim($cob->vcob_interes_ids));

        if (count($interes_ids) != count($cuotas)) {

            $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";



            return false;
        }
    }

    $str_capital_ids = trim($cob->vcob_capital_ids);

    if ($str_capital_ids && $cob->vcob_capital > 0) {

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_capital_ids) and ind_tipo='pcuota' order by ind_id asc");

        $capital_ids = explode(',', trim($cob->vcob_capital_ids));

        if (count($capital_ids) != count($cuotas)) {

            $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";



            return false;
        }
    }

    $str_form_ids = trim($cob->vcob_form_ids);

    if ($str_form_ids && $cob->vcob_form > 0) {

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_form_ids) and ind_tipo='pcuota' order by ind_id asc");

        $form_ids = explode(',', trim($cob->vcob_form_ids));

        if (count($form_ids) != count($cuotas)) {

            $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";



            return false;
        }
    }

    $str_envio_ids = trim($cob->vcob_envio_ids);

    if ($str_envio_ids && $cob->vcob_envio > 0) {

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_envio_ids) and ind_tipo='pcuota' order by ind_id asc");

        $envio_ids = explode(',', trim($cob->vcob_envio_ids));

        if (count($envio_ids) != count($cuotas)) {

            $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";



            return false;
        }
    }

//        echo "<pre>";
//        print_r($cob);
//        echo "</pre>";



    $val_monto = 0;

    $interes = $cob->vcob_interes;



    $saldo_descuento = FUNCIONES::atributo_bd_sql("select sum(val_monto) as campo from beneficiario_vale where val_int_id=$venta->ven_int_id and val_estado='Activo'") * 1;

    $descuento = $val_monto;

    if ($descuento > 0) {

        if ($descuento > $interes || $descuento > $saldo_descuento) {

            $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";



            return false;
        }
    }



    $fecha_cre = date('Y-m-d H:i:s');

    $usuario_id = 'admin';

    $fecha_pago = $cob->vcob_fecha_pago;

    $pag_codigo = FUNCIONES::fecha_codigo();

    $nro_recibo = $recibo;





    $monto = $cob->vcob_monto;

    if ($val_monto > 0) {

        $interes = $cob->vcob_interes - $val_monto;

        $monto = $cob->vcob_monto - $val_monto;
    }

    $cob->vcob_interes = $interes;

    $cob->vcob_interes_montos = $interes . '';







    //crear registro venta pago

    $sql_pago = "insert into venta_pago(

                        vpag_ven_id,vpag_codigo,vpag_fecha_pago,vpag_fecha_valor,

                        vpag_int_id,vpag_moneda,vpag_saldo_inicial,vpag_dias_interes,

                        vpag_interes,vpag_capital,vpag_mora,vpag_form,vpag_envio,vpag_monto,vpag_saldo_final,

                        vpag_interes_ids,vpag_capital_ids,vpag_form_ids,vpag_envio_ids,vpag_mora_ids,vpag_mora_con_ids,vpag_mora_gen_ids,

                        vpag_interes_montos,vpag_capital_montos,vpag_form_montos,vpag_envio_montos,vpag_mora_montos,vpag_mora_con_montos,vpag_mora_gen_montos,vpag_mora_gen_dias,

                        vpag_fecha_cre,vpag_usu_cre,vpag_estado,vpag_cob_usu,vpag_cob_codigo,vpag_cob_aut,vpag_recibo,vpag_suc_id,vpag_interes_desc,

                        vpag_val_monto,vpag_usu_import

                    )values(

                        '$venta->ven_id','$pag_codigo','$fecha_pago','$cob->vcob_fecha_valor',

                        '$venta->ven_int_id','$venta->ven_moneda','$cob->vcob_saldo_inicial','$cob->vcob_dias_interes',

                        '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_envio','$monto','$cob->vcob_saldo_final',

                        '$cob->vcob_interes_ids','$cob->vcob_capital_ids','$cob->vcob_form_ids','$cob->vcob_envio_ids','$cob->vcob_mora_ids','$cob->vcob_mora_con_ids','$cob->vcob_mora_gen_ids',

                        '$cob->vcob_interes_montos','$cob->vcob_capital_montos','$cob->vcob_form_montos','$cob->vcob_envio_montos','$cob->vcob_mora_montos','$cob->vcob_mora_con_montos','$cob->vcob_mora_gen_montos','$cob->vcob_mora_gen_dias',

                        '$fecha_cre','$usuario_id','Activo','$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut','$nro_recibo','$_SESSION[suc_id]','$cob->vcob_interes_desc',

                        '$val_monto','$usu_import'

                        )";

    $conec->ejecutar($sql_pago, true, true);

    $pago_id = ADO::$insert_id;



//        mysql_insert_id

    include_once 'clases/recibo.class.php';

    $data_recibo = array(
        'recibo' => $nro_recibo,
        'fecha' => $fecha_pago,
        'monto' => $monto,
        'moneda' => $venta->ven_moneda,
        'tabla' => 'venta_pago',
        'tabla_id' => $pago_id
    );

    RECIBO::insertar($data_recibo);



    $data_pago = (object) array(
                'cob' => $cob,
                'venta' => $venta,
                'pago_id' => $pago_id,
                'fecha_pago' => $fecha_pago,
                'fecha_cre' => $fecha_cre,
                'usuario_id' => $usuario_id,
                'recibo' => $nro_recibo,
                'pag_codigo' => $pag_codigo,
                'val_monto' => $val_monto,
    );



    //actualizar interno_deuda
    // *********** PAGAR INTERES        

    pagar_cu_interes($data_pago, $conec);

    // *********** PAGAR CAPITAL        

    $res_cap = (object) pagar_cu_capital($data_pago, $conec);

    //************ PAGAR FORMULARIO        

    pagar_cu_form($data_pago, $conec);

    //****-******* PAGAR ENVIO        

    pagar_cu_envio($data_pago, $conec);

    //************ PAGO MORA        

    pagar_cu_mora($data_pago, $conec);

    //*********************************************



    $ucuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");



    $pagado = total_pagado($venta->ven_id);



    $capital_pag = $pagado->capital;

    $capital_inc = $pagado->incremento;

    $capital_desc = $pagado->descuento;

    $cuota_pag = $ucuota->ind_num_correlativo;



    $scuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 1");

    $ven_sfecha_prog = $scuota ? $scuota->ind_fecha_programada : '0000-00-00';



    $sql_up = "update venta set 

                            ven_ufecha_pago='$fecha_pago', ven_ufecha_valor='$cob->vcob_fecha_valor', 

                            ven_cuota_pag='$cuota_pag', ven_capital_pag='$capital_pag',

                            ven_capital_inc='$capital_inc', ven_capital_desc='$capital_desc', 

                            ven_usaldo='$cob->vcob_saldo_final', ven_sfecha_prog='$ven_sfecha_prog'

                    where ven_id='$venta->ven_id'";



    $conec->ejecutar($sql_up, false, false);



//        $sql_up="update venta set ven_ufecha_pago='$fecha_pago', ven_ufecha_valor='$cob->vcob_fecha_valor', ven_usaldo='$cob->vcob_saldo_final' where ven_id='$venta->ven_id'";
//        $conec->ejecutar($sql_up);



    $sql_del = "delete from venta_cobro where vcob_ven_id='$venta->ven_id' ";

    $conec->ejecutar($sql_del, false, false);



//        $urb=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$venta->ven_urb_id");
//        include_once 'clases/modelo_comprobantes.class.php';
//        include_once 'clases/registrar_comprobantes.class.php';
//        $referido=  FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$venta->ven_int_id'");
//        $glosa = "Pago de la Venta Nro. $venta->ven_id - $venta->ven_concepto - $referido - Rec. $nro_recibo" ;
//        $params=array(
//                'tabla'=>'venta_pago',
//                'tabla_id'=>$pago_id,
//                'fecha'=>$fecha_pago,
//                'moneda'=>$venta->ven_moneda,
//                'ingreso'=>true,
//                'une_id'=>$urb->urb_une_id,
//                'glosa'=>$glosa,'ca'=>'0','cf'=>0,'cc'=>0
//            );
//        $detalles = FORMULARIO::insertar_pagos($params);
//        '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_monto'
//        $data=array(
//            'moneda'=>$venta->ven_moneda,
//            'ges_id'=>$_SESSION[ges_id],
//            'fecha'=>$fecha_pago,
//            'glosa'=>$glosa,
//            'interno'=>$referido,
//            'tabla_id'=>$pago_id,
//            'urb'=>$urb,
//

//            'interes'=>$interes,
//            'capital'=>$cob->vcob_capital,
//            'form'=>$cob->vcob_form,
//            'envio'=>$cob->vcob_envio,
//            'mora'=>$cob->vcob_mora,
//            'detalles'=>$detalles,
//            'costo'=>$res_cap->costo,
//        );
//        if($urb->urb_tipo=='Interno'){
//            $comprobante = MODELO_COMPROBANTE::pago_cuota($data);
//        }elseif($urb->urb_tipo=='Externo'){
//            $comprobante = MODELO_COMPROBANTE::pago_cuota_ext($data);
//        }
//        COMPROBANTES::registrar_comprobante($comprobante);





    $exito = $conec->commit();

    if ($exito) {

//            imprimir_pago($venta,$pago_id);
    } else {

        echo "ERROR AL GUARDAR<BR>";

        ECHO implode('<br>', $mensajes);

//            $exito = false;
//            $mensajes = $conec->get_errores();
//            $mensaje = implode('<br>', $mensajes);
    }
}

function pagar_cu_interes($data, &$conec = null) {

    if ($conec == null) {

        $conec = new ADO();
    }

//        'cob'=>$cob,
//        'venta'=>$venta,
//        'pago_id'=>$pago_id,
//        'fecha_pago'=>$fecha_pago,
//        'usuario_id'=>$usuario_id,
//        'recibo'=>$usuario_id,

    $cob = $data->cob;

    $venta = $data->venta;

    $pago_id = $data->pago_id;

    $fecha_pago = $data->fecha_pago;

    $fecha_cre = $data->fecha_cre;

    $usuario_id = $data->usuario_id;

    $recibo = $data->recibo;

    $pag_codigo = $data->pag_codigo;



    $str_interes_ids = trim($cob->vcob_interes_ids);

    if ($str_interes_ids && $cob->vcob_interes > 0) {

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_interes_ids) and ind_tipo='pcuota' order by ind_id asc");

//            $vpag_interes = $cob->vcob_interes;

        $interes_montos = explode(',', trim($cob->vcob_interes_montos));

        $interes_ids = explode(',', trim($cob->vcob_interes_ids));

        if (count($interes_ids) == count($cuotas) && count($cuotas) == count($interes_montos)) {

            for ($i = 0; $i < count($cuotas); $i++) {

                $cu = $cuotas[$i];

//                    $interes_dif = $vpag_interes - ($cu->ind_interes * 1);

                $interes_pagado = $interes_montos[$i]; //$cu->ind_interes;

                $sql_ins_idp = "insert into interno_deuda_pago(

                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,

                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,

                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut

                                    )values(

                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','interes',

                                        '$fecha_pago','$interes_pagado','Activo','$fecha_cre','$usuario_id',

                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'

                                    )

                        ";

                $conec->ejecutar($sql_ins_idp);



                $sql = "update interno_deuda set 

                                ind_interes_pagado=ind_interes_pagado+$interes_pagado,

                                ind_monto_pagado=ind_capital_pagado+ind_interes_pagado

                                where ind_id = '$cu->ind_id'

                            ";

                //            echo $sql.';<br>';

                $conec->ejecutar($sql);
            }
        } else {

            $conec->rollback();

            $mensaje = "Interes: Cantidad de cuotas de interes diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";



            return;
        }
    }
}

function pagar_cu_capital($data, &$conec = null) {

    if ($conec == null) {

        $conec = new ADO();
    }



    $cob = $data->cob;

    $venta = $data->venta;

    $pago_id = $data->pago_id;

    $fecha_pago = $data->fecha_pago;

    $fecha_cre = $data->fecha_cre;

    $usuario_id = $data->usuario_id;

    $recibo = $data->recibo;

    $pag_codigo = $data->pag_codigo;



    $costo_cub = 0;

    $costo_pag = 0;

    $costo = 0;

    $costo_ids = array();

    $costo_montos = array();

    $str_capital_ids = trim($cob->vcob_capital_ids);

    /* AQUI ME QUEDE... CALCULANDO EL SALDO DEL COSTO DE VENTA.. */

    $saldo_venta = $venta->ven_monto_efectivo - $venta->ven_cuota_inicial;

    $saldo_costo = $venta->ven_costo - $venta->ven_cuota_inicial - $venta->ven_costo_cub;

    if ($str_capital_ids && $cob->vcob_capital > 0) {

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_capital_ids) and ind_tipo='pcuota' order by ind_id asc");

//            $vpag_capital = $cob->vcob_capital;

        $capital_montos = explode(',', trim($cob->vcob_capital_montos));

        $capital_ids = explode(',', trim($cob->vcob_capital_ids));

        if (count($capital_ids) == count($cuotas) && count($cuotas) == count($capital_montos)) {

            $is_reformular = false;

            for ($i = 0; $i < count($cuotas); $i++) {

                $cu = $cuotas[$i];

//                    $capital_dif = $vpag_capital - ($cu->ind_capital * 1);

                $capital_pagado = $capital_montos[$i]; //$cu->ind_capital;

                $ind_estado = 'Pendiente';

                $saldo_final = 0;

                if ($capital_pagado . '' == $cu->ind_capital . '') {

                    $ind_estado = 'Pagado';

                    $saldo_final = $cu->ind_saldo;
                } else {

                    if ($i == 0 && $i == count($cuotas) - 1) {// primer y el ultimo pago
//                            echo " ENTRO PRIMER IF <br>";
                        $ind_capital_pagado = $cu->ind_capital_pagado + $capital_pagado;

                        if ($ind_capital_pagado . '' == $cu->ind_capital . '') {

                            $ind_estado = 'Pagado';

                            $saldo_final = $cu->ind_saldo;
                        } elseif ($ind_capital_pagado > $cu->ind_capital) {

                            $ind_estado = 'Pagado';

                            $capital_dif = $ind_capital_pagado - ($cu->ind_capital * 1);

                            $saldo_final = $cu->ind_saldo - $capital_dif;

                            $is_reformular = true;

                            $ant_saldo = $cu->ind_saldo;

                            $ant_capital = $cu->ind_capital;

                            $ant_fecha_prog = $cu->ind_fecha_programada;

                            $ant_ant_cuota = $venta->ven_cuota;
                        }
                    } elseif ($i == 0) {// primer pago de captital
//                            echo " ENTRO SEGUNDO IF <br>";
                        $ind_capital_pagado = $cu->ind_capital_pagado + $capital_pagado;



                        if ($ind_capital_pagado . '' == $cu->ind_capital . '') {

                            $ind_estado = 'Pagado';

                            $saldo_final = $cu->ind_saldo;
                        } elseif ($ind_capital_pagado > $cu->ind_capital) {

                            $conec->rollback();

                            $mensaje = "El Primero de varios Pagos no puede ser mayor a al Capital acordado ";



                            return;
                        }
                    } elseif ($i == count($cuotas) - 1) { /// si es el ultimo pago
//                            echo " ENTRO TERCER IF <br>";
                        if ($capital_pagado > $cu->ind_capital) { /// pago por demas
                            $ind_estado = 'Pagado';

                            $capital_dif = $capital_pagado - ($cu->ind_capital * 1);

                            $saldo_final = $cu->ind_saldo - $capital_dif;

                            $is_reformular = true;



                            $ant_saldo = $cu->ind_saldo;

                            $ant_capital = $cu->ind_capital;

                            $ant_fecha_prog = $cu->ind_fecha_programada;

                            $ant_ant_cuota = $venta->ven_cuota;
                        } else {// realizo un pago parcial no mueve el estado ni el saldo
                        }
                    } else {

                        $conec->rollback();

                        $mensaje = "Solo el ultimo o el Primer Pago puede ser diferente ";



                        return;
                    }
                }



                $sql_ins_idp = "insert into interno_deuda_pago(

                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,

                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,

                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut

                                    )values(

                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','capital',

                                        '$fecha_pago','$capital_pagado','Activo','$fecha_cre','$usuario_id',

                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'

                                    )

                        ";



                $conec->ejecutar($sql_ins_idp);

                $saldo_costo = $venta->ven_costo - $venta->ven_res_anticipo - $venta->ven_monto_intercambio - $venta->ven_cuota_inicial;

                $costo_pagado = 0;

                if ($saldo_costo > 0) {

                    if ($cu->ind_num_correlativo == 0) {

                        $costo_pagado = $capital_pagado;

                        $costo_cub = $costo_pagado;
                    } else {

                        if ($cob->vcob_saldo_final <= 0 && $i == count($cuotas) - 1) {

                            $costo_pagado = $venta->ven_costo - $venta->ven_costo_cub - $venta->ven_costo_pag - $costo;
                        } else {

                            $costo_pagado = round(($capital_pagado * $saldo_costo) / $saldo_venta, 2);
                        }

                        $costo_pag+=$costo_pagado;
                    }

                    $costo+=$costo_pagado;

                    $costo_ids[] = $cu->ind_id;

                    $costo_montos[] = $costo_pagado;
                }

                if ($costo_pagado > 0) {

                    $sql_ins_idp = "insert into interno_deuda_pago(

                                            idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,

                                            idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,

                                            idp_cob_usu,idp_cob_codigo,idp_cob_aut

                                        )values(

                                            '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','costo',

                                            '$fecha_pago','$costo_pagado','Activo','$fecha_cre','$usuario_id',

                                            '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'

                                        )

                            ";

                    $conec->ejecutar($sql_ins_idp);
                }



                $sql = "update interno_deuda set 

                                ind_estado='$ind_estado',

                                ind_fecha_pago='$fecha_pago',

                                ind_capital_pagado=ind_capital_pagado+$capital_pagado,

                                ind_monto_pagado=ind_interes_pagado+ind_capital_pagado,

                                ind_costo_pagado=ind_costo_pagado+$costo_pagado,

                                ind_saldo_final='$saldo_final'

                                where ind_id = '$cu->ind_id'

                            ";

//                    echo $sql.';<br>';

                $conec->ejecutar($sql);
            }

            ///ACTUALIZAR COSTO DE VENTA PAGO

            $txt_costo_ids = implode(',', $costo_ids);

            $txt_costo_montos = implode(',', $costo_montos);

            $sql_up_pag_costo = "update venta_pago set vpag_costo='$costo', vpag_costo_ids='$txt_costo_ids', vpag_costo_montos='$txt_costo_montos'

                                where vpag_id=$pago_id";

            $conec->ejecutar($sql_up_pag_costo);

            ///ACTUALIZAR COSTO DE VENTA

            $set_ven_estado = "";

            if ($cob->vcob_saldo_final <= 0) {

                $set_ven_estado = " ,ven_estado='Pagado'";
            }

            $sql_up_ven_costo = "update venta set ven_costo_cub=ven_costo_cub+$costo_cub, ven_costo_pag=ven_costo_pag+$costo_pag $set_ven_estado

                                where ven_id=$venta->ven_id";

            $conec->ejecutar($sql_up_ven_costo);



//                echo "CAPITAL DIFERENCIA" . $capital_dif . '<br>';

            if ($is_reformular && $cob->vcob_reformular == '1') { // reformular
                $params = array(
                    'int_id' => $venta->ven_int_id,
                    'ven_id' => $venta->ven_id,
                    'interes_anual' => $venta->ven_val_interes,
                    'moneda' => $venta->ven_moneda,
                    'concepto' => $venta->ven_concepto,
                    'fecha' => $venta->ven_fecha,
                    'saldo' => $cob->vcob_saldo_final,
                    'tipo_plan' => 'cuota', //$cob->vcob_tipo_plan,
                    'plazo' => $venta->ven_plazo, //$cob->vcob_plazo,
                    'cuota' => $venta->ven_cuota, //$cob->vcob_cuota,
                    'nro_cuota_inicio' => $cob->vcob_nro_cuota_sig,
                    'fecha_inicio' => $cob->vcob_fecha_valor,
                    'fecha_pri_cuota' => $cob->vcob_fecha_pri_cuota,
                    'val_form' => $venta->ven_form,
                    'rango' => $venta->ven_rango,
                    'frecuencia' => $venta->ven_frecuencia,
                );

                reformular_plan($params, $conec);

                //                echo 'Reformular<br>';
            }
        } else {

            $conec->rollback();

            $mensaje = "Capital: Cantidad de cuotas de capital diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";



            return;
        }
    }

    return array('type' => 'success', 'costo' => $costo);
}

function pagar_cu_form($data, &$conec = null) {

    if ($conec == null) {

        $conec = new ADO();
    }

    $cob = $data->cob;

    $venta = $data->venta;

    $pago_id = $data->pago_id;

    $fecha_pago = $data->fecha_pago;

    $fecha_cre = $data->fecha_cre;

    $usuario_id = $data->usuario_id;

    $recibo = $data->recibo;

    $pag_codigo = $data->pag_codigo;



    $str_form_ids = trim($cob->vcob_form_ids);

    if ($str_form_ids && $cob->vcob_form > 0) {

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_form_ids) and ind_tipo='pcuota' order by ind_id asc");

//            $vpag_form = $cob->vcob_form;

        $form_montos = explode(',', trim($cob->vcob_form_montos));

        $form_ids = explode(',', trim($cob->vcob_form_ids));

        if (count($form_ids) == count($cuotas) && count($cuotas) == count($form_montos)) {

            for ($i = 0; $i < count($cuotas); $i++) {

                $cu = $cuotas[$i];

//                    $form_dif = $vpag_form - ($cu->ind_form * 1);

                $form_pagado = $form_montos[$i]; //$cu->ind_form;

                $sql_ins_idp = "insert into interno_deuda_pago(

                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,

                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,

                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut

                                    )values(

                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','formulario',

                                        '$fecha_pago','$form_pagado','Activo','$fecha_cre','$usuario_id',

                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'

                                    )

                        ";

                $conec->ejecutar($sql_ins_idp);



                $sql = "update interno_deuda set 

                                ind_form_pagado=ind_form_pagado+$form_pagado

                                where ind_id = '$cu->ind_id'

                            ";

                //            echo $sql.';<br>';

                $conec->ejecutar($sql);
            }
        } else {

            $conec->rollback();

            $mensaje = "Formulario: Cantidad de cuotas de formulario diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";



            return;
        }
    }
}

function pagar_cu_envio($data, &$conec = null) {

    if ($conec == null) {

        $conec = new ADO();
    }

    $cob = $data->cob;

    $venta = $data->venta;

    $pago_id = $data->pago_id;

    $fecha_pago = $data->fecha_pago;

    $fecha_cre = $data->fecha_cre;

    $usuario_id = $data->usuario_id;

    $recibo = $data->recibo;

    $pag_codigo = $data->pag_codigo;



    $str_envio_ids = trim($cob->vcob_envio_ids);

    if ($str_envio_ids && $cob->vcob_envio > 0) {

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_envio_ids) and ind_tipo='pcuota' order by ind_id asc");

//            $vpag_envio = $cob->vcob_envio;

        $envio_montos = explode(',', trim($cob->vcob_envio_montos));

        $envio_ids = explode(',', trim($cob->vcob_envio_ids));

        if (count($envio_ids) == count($cuotas) && count($cuotas) == count($envio_montos)) {

            for ($i = 0; $i < count($cuotas); $i++) {

                $cu = $cuotas[$i];

//                    $envio_dif = $vpag_envio - ($cu->ind_envio * 1);

                $envio_pagado = $envio_montos[$i]; //$cu->ind_envio;

                $sql_ins_idp = "insert into interno_deuda_pago(

                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,

                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,

                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut

                                    )values(

                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','envio',

                                        '$fecha_pago','$envio_pagado','Activo','$fecha_cre','$usuario_id',

                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'

                                    )

                        ";

                $conec->ejecutar($sql_ins_idp);



                $sql = "update interno_deuda set 

                                ind_envio_pagado=ind_envio_pagado+$envio_pagado

                                where ind_id = '$cu->ind_id'

                            ";

                //            echo $sql.';<br>';

                $conec->ejecutar($sql);
            }
        } else {

            $conec->rollback();

            $mensaje = "Formulario: Cantidad de cuotas de envios diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";



            return;
        }
    }
}

function pagar_cu_mora($data, &$conec = null) {

    if ($conec == null) {

        $conec = new ADO();
    }

    $cob = $data->cob;

    $venta = $data->venta;

    $pago_id = $data->pago_id;

    $fecha_pago = $data->fecha_pago;

    $fecha_cre = $data->fecha_cre;

    $usuario_id = $data->usuario_id;

    $recibo = $data->recibo;

    $pag_codigo = $data->pag_codigo;



    //************ GENERAR MORA

    $str_mora_gen_ids = trim($cob->vcob_mora_gen_ids);

    if ($str_mora_gen_ids) {

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_mora_gen_ids) and ind_tipo='pcuota' order by ind_id asc");

//            $vpag_mora_gen = $cob->vcob_mora_gen;

        $mora_gen_montos = explode(',', trim($cob->vcob_mora_gen_montos));

        $mora_gen_ids = explode(',', trim($cob->vcob_mora_gen_ids));

        if (count($mora_gen_ids) == count($cuotas) && count($cuotas) == count($mora_gen_montos)) {

            for ($i = 0; $i < count($cuotas); $i++) {

                $cu = $cuotas[$i];

//                    $mora_gen_dif = $vpag_mora_gen - ($cu->ind_mora_gen * 1);

                $mora_generado = $mora_gen_montos[$i]; //$cu->ind_mora_gen;

                $sql = "update interno_deuda set 

                                ind_mora=$mora_generado

                                where ind_id = '$cu->ind_id'

                            ";

                //            echo $sql.';<br>';

                $conec->ejecutar($sql);
            }
        } else {

            $conec->rollback();

            $mensaje = "Mora Generada: Cantidad de cuotas de mora_gen diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";



            return;
        }
    }

    //************ PAGAR MORA

    $str_mora_ids = trim($cob->vcob_mora_ids);

    if ($str_mora_ids && $cob->vcob_mora > 0) {

        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_mora_ids) and ind_tipo='pcuota' order by ind_id asc");

//            $vpag_mora = $cob->vcob_mora;

        $mora_montos = explode(',', trim($cob->vcob_mora_montos));

        $mora_ids = explode(',', trim($cob->vcob_mora_ids));

        if (count($mora_ids) == count($cuotas) && count($cuotas) == count($mora_montos)) {



            for ($i = 0; $i < count($cuotas); $i++) {

                $cu = $cuotas[$i];

//                    $mora_dif = $vpag_mora - ($cu->ind_mora * 1);

                $mora_pagado = $mora_montos[$i]; //$cu->ind_mora;

                $sql_ins_idp = "insert into interno_deuda_pago(

                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,

                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,

                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut

                                    )values(

                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','mora',

                                        '$fecha_pago','$mora_pagado','Activo','$fecha_cre','$usuario_id',

                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'

                                    )

                        ";

                $conec->ejecutar($sql_ins_idp);



                $sql = "update interno_deuda set 

                                ind_mora_pagado=ind_mora_pagado+$mora_pagado

                                where ind_id = '$cu->ind_id'

                            ";

                //            echo $sql.';<br>';

                $conec->ejecutar($sql);
            }
        } else {

            $conec->rollback();

            $mensaje = "Mora Pago: Cantidad de cuotas de mora diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";



            return;
        }
    }
}

function insertar_plan_pagos() {

    $conec = new ADO();

//    $and_filtro=" and ven_id=7720";

    $and_filtro = "";



    $ventas = FUNCIONES::objetos_bd_sql("select * from venta where ven_importado='0' and ven_numero like 'NET-%'");



    echo '-- ' . $ventas->get_num_registros() . '<br>';

//    return;



    $insert = "insert into interno_deuda(

                    ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_usu_id,ind_moneda,ind_interes,ind_capital,ind_monto,

                    ind_saldo,ind_fecha_programada,ind_estado,ind_concepto,ind_observacion,ind_form,ind_mora,

                    ind_interes_pagado,ind_capital_pagado,ind_monto_pagado,ind_saldo_final,ind_fecha_pago,ind_dias_interes,

                    ind_tipo,ind_fecha_cre,ind_estado_parcial,ind_monto_parcial

                ) values";



    $reg = 0;



//    $num_ini=1000000;
//    return;



    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {

        $venta = $ventas->get_objeto();

        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_id='$venta->ven_int_id'");

        $aicod = explode('-', $interno->int_codigo);

        $cod_cliente = $aicod[1];

        $tplan_pagos = FUNCIONES::lista_bd_sql("select * from temp_plan_pagos where cliente='$cod_cliente' order by nro_cuota*1 asc");

        $num = 0;

        $ufecha_prog = '0000-00-00';

        $j = 0;

        $sql_insert = $insert;

        $saldo_final = $venta->ven_monto_efectivo;

        $fecha_cre = date('Y-m-d');

        foreach ($tplan_pagos as $tplan) {

//            if($tplan->ind_num_correlativo!=0 || $tplan->ind_estado=='Pendiente' || $tplan->ind_tipo=='pcontado'){

            if (true) {

                if ($num > 0) {

                    $sql_insert.=',';
                }



//                $nro=$num+1;





                $nro_cuota = $tplan->nro_cuota;

                if ($tplan->ind_estado == 'Pagado') {

                    $tplan->ind_interes = $tplan->ind_interes_pagado;

                    $tplan->ind_capital = $tplan->ind_capital_pagado;

                    $tplan->ind_monto = $tplan->ind_monto_pagado;

                    $tplan->ind_saldo = $tplan->ind_saldo_final;
                }

                $capital = $tplan->capital;

                $monto = $tplan->total;

//                ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_usu_id,ind_moneda,ind_interes,ind_capital,ind_monto,
//                ind_saldo,ind_fecha_programada,ind_estado,ind_concepto,ind_observacion,ind_form,ind_mora,
//                ind_interes_pagado,ind_capital_pagado,ind_monto_pagado,ind_saldo_final,ind_fecha_pago,ind_dias_interes,
//                ind_tipo,ind_fecha_cre,ind_estado_parcial,ind_monto_parcial

                if (abs($saldo_final - $capital) <= 5) {

                    $capital = $saldo_final;
                }

                $saldo_final = $saldo_final - $capital;

                if ($saldo_final < 0) {

                    $saldo_final = 0;
                }

                $fecha_prog = get_fecha($tplan->fecha_prog);

                $sql_insert.="(

                                'venta',$venta->ven_id,'$nro_cuota',$interno->int_id,'$venta->ven_fecha','admin',2,0,$capital,$monto,

                                $saldo_final,'$fecha_prog','Pendiente','Cuota Nro $nro_cuota, $venta->ven_concepto','',0,0,

                                0,0,0,0,'0000-00-00',30,

                                'pcuota','$fecha_cre','0',0

                                )";

                $ufecha_prog = $tplan->ind_fecha_programada;

                if (count($tplan_pagos) > 1) {

                    if ($j == count($tplan_pagos) - 2) {

                        $mcuota = $tplan->ind_monto;
                    }
                } else {

                    $mcuota = $tplan->ind_monto;
                }





                $num++;
            }

            $j++;
        }

        if ($num > 0) {

            echo count($tplan_pagos) . " - $venta->ven_id<br>";

            $conec->ejecutar($sql_insert, false, false);
        }



//        FUNCIONES::bd_query($sql_insert);

        $sql_up = "update venta set

                    ven_ufecha_prog='$ufecha_prog',

                    ven_plazo='$num',

                    ven_cuota='$mcuota',

                    ven_importado='1'

                 where ven_id='$venta->ven_id'

                ";

        $conec->ejecutar($sql_up, false, false);

//        if ($num > 0) {
////            echo "$sql_insert<br>";
//            FUNCIONES::bd_query($sql_insert);
//        }

        $reg++;

        $ventas->siguiente();
    }

    echo "-- <BR>TOTAL REGISTRADOS $reg";
}

function ventas() {

//    $and_filtro = " and PY='2'  and Numero_Adj=1 ";
//    $fecha_limite='2015-08-11';
//    $and_filtro = " and fecha <='$fecha_limite'";
//    $and_filtro = " and cod_cliente in (275,28,586,805)";
    // $adjudicados = FUNCIONES::lista_bd_sql("select * from temp_ventas where urbanizacion!='OKINAWA'");

    $adjudicados = FUNCIONES::lista_bd_sql("select * from temp_ventas where 1 $and_filtro");

    echo '-- ' . count($adjudicados) . '<br>';

//    return;

    $num = 0;

    $insert = "insert into venta(

                    ven_urb_id,ven_numero,ven_lot_id,ven_lot_ids,ven_int_id,ven_co_propietario,ven_fecha,ven_moneda,

                    ven_res_id,ven_superficie,ven_metro,ven_valor,ven_decuento,ven_incremento,ven_monto,ven_res_anticipo,ven_monto_intercambio,ven_monto_efectivo,

                    ven_estado,ven_usu_id,ven_tipo,ven_val_interes,ven_cuota_inicial,ven_tipo_plan,ven_plazo,ven_cuota,ven_rango,ven_frecuencia,

                    ven_observacion,ven_codigo,ven_vdo_id,ven_comision,ven_concepto,ven_tipo_pago,ven_fecha_firma,ven_fecha_cre,ven_monto_pagar,

                    ven_form,ven_costo,ven_costo_cub,ven_costo_pag,ven_promotor,ven_importado,

                    ven_lug_id,ven_ubicacion,ven_suc_id,ven_ufecha_prog,ven_ufecha_pago,ven_ufecha_valor,

                    ven_cuota_pag,ven_capital_pag,ven_capital_desc,ven_capital_inc,ven_usaldo,ven_sfecha_prog,ven_costo_up,ven_numero_cliente,ven_multinivel,ven_porc_comisiones

                ) values";

    $sql_insert = $insert;

    $reg = 0;



    $num_ini = 1000000;



    $aurbs = array(
        'LUJAN' => '2',
        'BISITO' => '3',
        'OKINAWA' => '13',
        'LA QUINTA' => '11'
    );



    $lotes_vendidos = array();



    foreach ($adjudicados as $tven) {

        if ($num == 440) {

//            echo "$sql_insert<br>";

            FUNCIONES::bd_query($sql_insert);

            $sql_insert = $insert;

            $num = 0;
        }

        $int_codigo = "NET-$tven->cod_cliente";

        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_codigo='$int_codigo'");

        $urb_id = $aurbs[$tven->urbanizacion];



        $sql_lote = "select * from lote

                        inner join manzano on (lot_man_id=man_id)

                        where man_urb_id='$urb_id' and man_nro='$tven->mz' and lot_nro='$tven->lote';

                    ";



        $lote = FUNCIONES::objeto_bd_sql($sql_lote);



        if (!$lote || $lote->lot_estado != 'Disponible') {

            $lot_estado = $lote->lot_estado;

            $_uv = FUNCIONES::objeto_bd_sql("select * from uv where uv_id='$lote->lot_uv_id'");

            $_zona = FUNCIONES::objeto_bd_sql("select * from zona where zon_id='$lote->lot_zon_id'");

            $lote_id = importar_lotes($urb_id, $_uv->uv_nombre, $_zona->zon_nombre, $tven->mz, "NET-$tven->lote", $lote->lot_superficie);

            $sql_lote = "select * from lote

                        inner join manzano on (lot_man_id=man_id)

                        where lot_id='$lote_id';

                    ";



            $lote = FUNCIONES::objeto_bd_sql($sql_lote);



            echo "-- no existe lote o esta $lot_estado ;$urb_id-$tven->mz-$tven->lote ---> LOTE ID: $lote_id<br>";
        }

        /*

          if(!$lote){

          echo "-- NO EXISTE LOTE $tven->urbanizacion - $tven->mz - $tven->lote <br>";

          }else{

          //            echo "-- EXISTE LOTE $tven->urbanizacion - $tven->mz - $tven->lote - $lote->lot_estado<br>";

          if($lote->lot_estado=='Vendido'){

          echo "$tven->cod_venta<br>";

          }

          }

         */



//        continue;



        if ($lote && $lote->lot_estado == 'Disponible') {

            if ($num > 0) {

                $sql_insert.=',';
            }

            $int_codigo_vdo = "NET-$tven->cod_vendedor";

            $_vendedor = FUNCIONES::objeto_bd_sql("select * from interno where int_codigo='$int_codigo_vdo'");

            $txt_promotor = "$int_codigo_vdo | $_vendedor->int_nombre";



//            if($tven->ven_vdo_id>0){
//                $txt_promotor=  _db::atributo_sql("select concat(int_nombre,' ',int_apellido) as campo from interno,vendedor where vdo_int_id=int_id and vdo_id='$tven->ven_vdo_id'");
//            }
//            if(!$interno){
//                echo "-- NO EXISTE INTERNO <BR>";
//            }
//            if(!$tres){
//                echo "-- NO EXISTE TEMP_RESERVA *** '$tobj->PY' , '$tobj->Numero_Reserva' <BR>";                
//            }
//            if(!$tpromotor){
//                echo "-- NO EXISTE PROMOTOR<BR>";                
//            }



            $res_anticipo = $tven->cuota_inicial * 1;



            $ven_monto = $tven->precio;

            $saldo_efectivo = $tven->saldo_financiar;

            $ven_metro = round($ven_monto / $tven->superficie, 2);

            $concepto = FUNCIONES::get_concepto($lote->lot_id);

            $ven_numero = "NET-$tven->cod_cliente";

            $ven_numero_cliente = "NET-$tven->cod_cliente";

            $fecha_cre = date('Y-m-d H:i:s');

//                $insert="insert into venta(
//                    ven_urb_id,ven_numero,ven_lot_id,ven_lot_ids,ven_int_id,ven_co_propietario,ven_fecha,ven_moneda,
//                    ven_res_id,ven_superficie,ven_metro,ven_valor,ven_decuento,ven_incremento,ven_monto,ven_res_anticipo,ven_monto_intercambio,ven_monto_efectivo,
//                    ven_estado,ven_usu_id,ven_tipo,ven_val_interes,ven_cuota_inicial,ven_tipo_plan,ven_plazo,ven_cuota,ven_rango,ven_frecuencia,
//                    ven_observacion,ven_codigo,ven_vdo_id,ven_comision,ven_concepto,ven_tipo_pago,ven_fecha_firma,ven_fecha_cre,ven_monto_pagar,
//                    ven_form,ven_costo,ven_costo_cub,ven_costo_pag,ven_promotor,ven_importado,
//                    ven_lug_id,ven_ubicacion,ven_suc_id,ven_ufecha_prog,ven_ufecha_pago,ven_ufecha_valor,
//                    ven_cuota_pag,ven_capital_pag,ven_capital_desc,ven_capital_inc,ven_usaldo,ven_sfecha_prog,ven_costo_up
//                        ) values";

            $ven_fecha = get_fecha($tven->fecha_venta);

            $ven_moneda = 2;

            $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$urb_id");

            $costo_m2 = $urb->urb_val_costo;

            $costo = $tven->superficie * $costo_m2;

            $costo_cub = $res_anticipo;

            if ($res_anticipo > $costo) {

                $costo_cub = $costo;
            }

            $sql_insert.="(

                            '$urb_id','$ven_numero','$lote->lot_id','','$interno->int_id','0','$ven_fecha','$ven_moneda',

                            '0','$tven->superficie','$ven_metro','$ven_monto','0','0','$ven_monto','$res_anticipo',0,'$saldo_efectivo',

                            'Pendiente','admin','Credito','$tven->interes','0','plazo',0,0,1,'dia_mes',

                            '','',0,0,'$concepto','Normal','0000-00-00','$fecha_cre',0,

                            7,'$costo','$costo_cub',0,'$txt_promotor',0,

                            1,'Bolivia,Santa Cruz,Santa Cruz de la Sierra',1,

                            '0000-00-00','0000-00-00','0000-00-00',0,0,0,0,0,'0000-00-00',0,'$ven_numero_cliente','si','{$urb->urb_porc_comisiones}'

                            )";

            $num++;

            $reg++;

            $lotes_vendidos[] = $lote->lot_id;
        } else {



            echo "-- no existe lote o esta $lote->lot_estado;$urb_id-$tven->mz-$tven->lote<br>";
        }
    }



    if ($num > 0) {

//        echo "$sql_insert<br>";

        FUNCIONES::bd_query($sql_insert);
    }



    $sql_up_lotes = "update lote set lot_estado='Vendido' where lot_id in (" . implode(',', $lotes_vendidos) . ")";

    echo $sql_up_lotes . ';<br>';

//    FUNCIONES::bd_query($sql_up_lotes);



    echo "-- <BR>TOTAL REGISTRADOS $reg";
}

function reservas_pagos() {

//    $urbs=_urbs;

    $num_ini = 1000000;

    $reservas = FUNCIONES::lista_bd_sql("select * from reserva_terreno where res_estado in ('Habilitado','Pendiente') and res_numero>$num_ini");

    echo '-- ' . count($reservas) . '<br>';

    $num = 0;

    $insert = "insert into reserva_pago(

                    respag_monto,respag_moneda,respag_fecha,respag_hora,

                    respag_estado,respag_usu_id,respag_glosa,respag_res_id

            ) values";

    $sql_insert = $insert;

    $reg = 0;

    $res_pagados = array();



    foreach ($reservas as $res) {

        $res_id = $res->res_numero - $num_ini;

        $trpagos = _db::query("select * from reserva_pago where respag_res_id='$res_id'");

        foreach ($trpagos as $rpag) {

            if ($num == 440) {

//                echo "$sql_insert;<br>";

                FUNCIONES::bd_query($sql_insert);

                $sql_insert = $insert;

                $num = 0;
            }

            if ($num > 0) {

                $sql_insert.=',';
            }

            $monto = $rpag->respag_monto;

            $moneda = $rpag->respag_moneda;



//            echo "$res->res_id -- $_mon<br>";



            if ($moneda == 1) {

                $monto = $monto / 6.96;
            }





            $res_fecha = $rpag->respag_fecha;

            $res_hora = $rpag->respag_hora;

            $estado = $rpag->respag_estado;

            $usu_id = $rpag->respag_usu_id;

            $glosa = $rpag->respag_glosa; //

            $res_id = $res->res_id;



            $sql_insert.="('$monto','$moneda','$res_fecha','$res_hora',

                            '$estado','$usu_id','$glosa','$res_id'

                            )";

            $res_pagados[] = $res_id;

            $num++;

            $reg++;
        }
    }



    if ($num > 0) {

//        echo "$sql_insert;<br>";

        FUNCIONES::bd_query($sql_insert);
    }

    $sql_up_reserva = "update reserva_terreno set res_estado='Habilitado' where res_id in (" . implode(',', $res_pagados) . ")";

//    echo $sql_up_reserva.';<br>';

    FUNCIONES::bd_query($sql_up_reserva);

    echo "-- <BR>TOTAL REGISTRADOS $reg";
}

function bloquear_terreno() {

    $urbs = _urbs;

    $bloqueos = _db::query("select * from bloquear_terreno where bloq_estado in ('Habilitado')");

    echo '-- ' . count($bloqueos) . '<br>';

    $num = 0;

    $insert = "insert into bloquear_terreno(

                    bloq_lot_id,bloq_fecha,bloq_hora,bloq_estado,bloq_usu_id,bloq_int_id,bloq_nota,bloq_vdo_id,bloq_promotor

            ) values";

    $sql_insert = $insert;

    $reg = 0;

    $lotes_reservados = array();

    foreach ($bloqueos as $tobj) {

        if ($num == 440) {

//            echo "$sql_insert<br>";

            FUNCIONES::bd_query($sql_insert);

            $sql_insert = $insert;

            $num = 0;
        }

        if ($num > 0) {

            $sql_insert.=',';
        }

        $interno = new stdClass();

        if ($tobj->bloq_int_id > 0) {

            $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_id_ant='$tobj->bloq_int_id'");
        }



        $sql_lote = "select * from lote where lot_id='$tobj->bloq_lot_id'";

        $lote = FUNCIONES::objeto_bd_sql($sql_lote);



        $bloq_fecha = $tobj->bloq_fecha;

        $tpromotor = '';

        if ($tobj->bloq_vdo_id > 0) {

            $tpromotor = _db::atributo_sql("select concat(int_nombre,' ',int_apellido) from vendedor,interno where vdo_int_id=int_id and vdo_id='$tobj->bloq_vdo_id'");
        }



        if (!$interno) {

            echo "-- NO EXISTE INTERNO <BR>";
        }

        if (!$lote) {

            echo "-- NO EXISTE LOTE select * from lote, manzano, urbanizacion where urb_id='$tobj->PY' and man_nro='$tobj->MZA' and lot_nro='$tobj->Lote' and lot_man_id=man_id and man_urb_id=urb_id<BR>";
        }

        if (!$tpromotor) {

//            echo "-- NO EXISTE PROMOTOR<BR>";
        }

        $_mon = $tobj->Moneda;

        $moneda = 0;

        if ($_mon == '1') {

            $moneda = 2;
        } else {

            $moneda = 1;
        }

//        bloq_lot_id,bloq_fecha,bloq_hora,bloq_estado,bloq_usu_id,bloq_int_id,bloq_nota,bloq_vdo_id,bloq_promotor

        $sql_insert.="('$tobj->bloq_lot_id','$tobj->bloq_fecha','$tobj->bloq_hora','$tobj->bloq_estado','$tobj->bloq_usu_id','$interno->int_id','$tobj->bloq_nota','0','$tpromotor')";

        $num++;

        $reg++;

        $lotes_reservados[] = $lote->lot_id;
    }



    if ($num > 0) {

//        echo "$sql_insert<br>";

        FUNCIONES::bd_query($sql_insert);
    }



//    $sql_up_lotes = "update lote set lot_estado='Reservado' where res_id in (" . implode(',', $lotes_reservados) . ")";
//    echo $sql_up_lotes.';<br>';
//    FUNCIONES::bd_query($sql_up_lotes);



    echo "-- <BR>TOTAL REGISTRADOS $reg";
}

function reservas() {

    $urbs = _urbs;

    $reservas = _db::query("select * from reserva_terreno where res_estado in ('Habilitado','Pendiente')");

    echo '-- ' . count($reservas) . '<br>';

    $num = 0;

    $insert = "insert into reserva_terreno(

                    res_urb_id,res_numero,res_int_id,res_vdo_id,res_lot_id,res_fecha,res_hora,res_estado,res_usu_id,

                    res_monto_referencial,res_moneda,res_plazo_fecha,res_plazo_hora,res_nota,res_promotor

            ) values";

    $sql_insert = $insert;

    $reg = 0;

    $lotes_reservados = array();

    $num_ini = 1000000;

    foreach ($reservas as $tres) {

        if ($num == 440) {

//            echo "$sql_insert<br>";

            FUNCIONES::bd_query($sql_insert);

            $sql_insert = $insert;

            $num = 0;
        }



        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_id_ant='$tres->res_int_id'");

        $sql_lote = "select * from lote where lot_id='$tres->res_lot_id'";

        $lote = FUNCIONES::objeto_bd_sql($sql_lote);

        if ($num > 0 && $lote) {

            $sql_insert.=',';
        }



        $tpromotor = '';

        if ($tres->res_vdo_id > 0) {

            $tpromotor = _db::atributo_sql("select concat(int_nombre,' ',int_apellido) from vendedor,interno where vdo_int_id=int_id and vdo_id='$tobj->bloq_vdo_id'");
        }



        if (!$interno) {



            echo "-- NO EXISTE INTERNO <BR>";

            continue;
        }

        if (!$lote) {



            echo "-- NO EXISTE LOTE select * from lote, manzano, urbanizacion where urb_id='$tres->PY' and man_nro='$tres->MZA' and lot_nro='$tres->Lote' and lot_man_id=man_id and man_urb_id=urb_id<BR>";

            continue;
        }



        if (!$tpromotor) {

//            echo "-- NO EXISTE PROMOTOR<BR>";
        }

        $_mon = $tres->Moneda;

        $moneda = 0;

        if ($_mon == '1') {

            $moneda = 2;
        } else {

            $moneda = 1;
        }

        $res_nro = $num_ini + $tres->res_id;

        $sql_insert.="(

                        '$tres->res_urb_id','$res_nro','$interno->int_id',0,'$tres->res_lot_id','$tres->res_fecha','$tres->res_hora','$tres->res_estado','$tres->res_usu_id',

                        '$tres->res_monto_referencial','$tres->res_moneda','$tres->res_plazo_fecha','$tres->res_plazo_hora','$tres->res_nota','$tpromotor'

                        )";

        $num++;

        $reg++;

        $lotes_reservados[] = $lote->lot_id;
    }



    if ($num > 0) {

//        echo "$sql_insert<br>";

        FUNCIONES::bd_query($sql_insert);
    }



//    $sql_up_lotes = "update lote set lot_estado='Reservado' where res_id in (" . implode(',', $lotes_reservados) . ")";
//    echo $sql_up_lotes.';<br>';
//    FUNCIONES::bd_query($sql_up_lotes);



    echo "-- <BR>TOTAL REGISTRADOS $reg";
}

function internos() {

    $tclientes = FUNCIONES::lista_bd_sql("select * from temp_clientes");



    $insert = "insert into interno(int_codigo,int_nombre,int_apellido,int_email,int_foto,int_telefono,int_celular,int_direccion,int_ci,int_ci_exp,int_fecha_nacimiento,int_fecha_ingreso,int_usu_id) values";

    $num = 0;

    $sql_insert = $insert;

    $fecha_cre = date('Y-m-d');

    $reg = 0;



    for ($i = 0; $i < count($tclientes); $i++) {

        $tcliente = $tclientes[$i];

        if ($num == 440) {

//            echo "$sql_insert<br>";

            FUNCIONES::bd_query($sql_insert);

            $sql_insert = $insert;

            $num = 0;
        }

        $int_codigo = "NET-$tcliente->cod_cliente";

        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_codigo='$int_codigo'");



        if ($num > 0 && !$interno) {

            $sql_insert.=',';
        }



        if (!$interno) {

            $fecha_nac = get_fecha($tcliente->fecha_nac);

            $fecha_registro = get_fecha($tcliente->fecha_registro);

            $sql_insert.="('$int_codigo','$tcliente->nombre','','$tcliente->correo','','$tcliente->telefono','$tcliente->celular','$tcliente->direccion','$tcliente->ci','$tcliente->exp','$fecha_nac','$fecha_registro','admin')";

            $num++;

            $reg++;
        } else {

//            echo "Ya existe $cliente->int_id<br>;";
//            $sql_up="update interno set int_nombre='$nombre',int_telefono='$tobj->Telefono',int_direccion='$direccion',int_ci='$tobj->CI' where int_codigo='$tobj->Codigo_C'" ;
//            FUNCIONES::bd_query($sql_up);
        }

//        $reg++;
    }

    if ($num > 0) {

//        echo "$sql_insert<br>";

        FUNCIONES::bd_query($sql_insert);
    }



    echo "-- <BR>TOTAL REGISTRADOS $reg";
}

function obtener_num_mes($str_abr_mes) {

    $a_meses = array(
        'may' => '05',
        'jun' => '06',
        'dec' => '12',
        'oct' => '10',
        'nov' => '11',
        'aug' => '08',
        'jul' => '07',
        'sep' => '09',
        'apr' => '04',
        'ago' => '08',
        'dic' => '12',
        'ene' => '01',
        'feb' => '02',
        'mar' => '03',
        'abr' => '04',
    );
}

function get_fecha($fecha) {

//    echo "-- $fecha<br>";

    if (!$fecha) {

        return '0000-00-00';
    }

    return FUNCIONES::get_fecha_mysql($fecha);



    $pos = strpos('-', $fecha);

    if ($pos) {

        $af = explode('-', $fecha);

        if (is_numeric($af[1])) {

            return $fecha;
        } else {

            $mes = obtener_num_mes($af[1]);

            return "$af[0]-$mes-$af[2]";
        }
    }

    $pos = strpos('/', $fecha);

    if ($pos) {

        echo FUNCIONES::get_fecha_mysql($fecha) . "<br>";

        return FUNCIONES::get_fecha_mysql($fecha);
    }

    if (strlen($fecha) == 8) {

        $af = $fecha;

        return"$af[0]$af[1]$af[2]$af[3]-$af[4]$af[5]-$af[6]$af[7]";
    }
}

function lotes() {

    $tlotes = _db::query("select * from lotes");

    echo '-- ' . count($tlotes);

    echo '<br>';

    $insert = "insert into lote(lot_nro,lot_estado,lot_man_id,lot_zon_id,lot_superficie,lot_uv_id,lot_tipo,lot_codigo) values";

    $num = 0;

    $sql_insert = $insert;

    $reg = 0;

    foreach ($tlotes as $tobj) {

        if ($num == 440) {

//            echo "$sql_insert<br>";

            FUNCIONES::bd_query($sql_insert);

            $sql_insert = $insert;

            $num = 0;
        }

        if ($num > 0) {

            $sql_insert.=',';
        }



        $man = FUNCIONES::objeto_bd_sql("select * from manzano where man_urb_id='$tobj->PY' and man_nro='$tobj->MZA'");

        $zona = FUNCIONES::objeto_bd_sql("select * from zona where zon_urb_id='$tobj->PY' and zon_codigo='$tobj->Ubicacion'");

        $uv = FUNCIONES::objeto_bd_sql("select * from uv where uv_urb_id='$tobj->PY' and uv_nombre='$tobj->UV'");

        if (!$man) {

            echo "-- NO EXISTE MANZANO<BR>";
        }

        if (!$zona) {

            echo "-- NO EXISTE ZONA<BR>";
        }

        if (!$uv) {

            echo "-- NO EXISTE UV<BR>";
        }

        $sql_insert.="('$tobj->Lote','Disponible','$man->man_id','$zona->zon_id','$tobj->Superficie','$uv->uv_id','Lote','$tobj->PY-M{$man->man_nro}L{$tobj->Lote}')";

        $num++;

        $reg++;
    }



    if ($num > 0) {

//        echo "$sql_insert<br>";

        FUNCIONES::bd_query($sql_insert);
    }

    echo "-- <BR>TOTAL REGISTRADOS $reg";
}

function uv() {

    $tuvs = _db::query("select distinct PY, UV from lotes");

    echo '-- ' . count($tuvs);

    echo '<br>';

    $insert = "insert into uv(uv_nombre,uv_urb_id) values";

    $num = 0;

    $sql_insert = $insert;

    foreach ($tuvs as $tobj) {

        if ($num == 440) {

            echo "$sql_insert<br>";

            FUNCIONES::bd_query($sql_insert);

            $sql_insert = $insert;

            $num = 0;
        } else {

            if ($num > 0) {

                $sql_insert.=',';
            }

            $sql_insert.="('$tobj->UV','$tobj->PY')";

            $num++;
        }
    }



    if ($num > 0) {

        echo "$sql_insert<br>";

        FUNCIONES::bd_query($sql_insert);
    }
}

function manzanos() {

    $tmanzanos = _db::query("select distinct PY, MZA from lotes");

    echo '-- ' . count($tmanzanos);

    echo '<br>';

    $insert = "insert into manzano(man_nro,man_urb_id) values";

    $num = 0;

    $sql_insert = $insert;

    foreach ($tmanzanos as $tobj) {

        if ($num == 440) {

            echo "$sql_insert<br>";

            FUNCIONES::bd_query($sql_insert);

            $sql_insert = $insert;

            $num = 0;
        } else {

            if ($num > 0) {

                $sql_insert.=',';
            }

            $sql_insert.="('$tobj->MZA','$tobj->PY')";

            $num++;
        }
    }



    if ($num > 0) {

        echo "$sql_insert<br>";

        FUNCIONES::bd_query($sql_insert);
    }
}

function zonas() {

    $tzonas = _db::query("select distinct PY,ubicacion from lotes;");

    echo '-- ' . count($tzonas);

    echo '<br>';

    $insert = "insert into zona(zon_nombre,zon_precio,zon_urb_id,zon_color,zon_cuota_inicial,zon_moneda,zon_codigo) values";

    $num = 0;

    $sql_insert = $insert;

    foreach ($tzonas as $tzon) {

        if ($num == 440) {

            echo "$sql_insert<br>";

            FUNCIONES::bd_query($sql_insert);

            $sql_insert = $insert;

            $num = 0;
        } else {

            if ($num > 0) {

                $sql_insert.=',';
            }

            $ub = _db::objeto_sql("select * from ubicacionlotes where Codigo='$tzon->ubicacion'");

            $precio = _db::objeto_sql("select * from precios_m2 where CodUbicacion='$tzon->ubicacion' and PY=$tzon->PY");



            if ($ub && $precio) {

                $sql_insert.="('$ub->Ubicacion','$precio->Costo_M2','$tzon->PY','#FFF','$precio->Cuota_INI','2','$tzon->ubicacion')";

                $num++;
            } else {

                echo "NO EXISTE UBICACION O PRECIO $tzon->ubicacion -- $tzon->PY";
            }
        }
    }



    if ($num > 0) {

//        echo "$sql_insert<br>";

        FUNCIONES::bd_query($sql_insert);
    }
}

function establecer_m2_reservas() {

//    $reservas = FUNCIONES::lista_bd_sql("select res_id,zon_precio from reserva_terreno
//        inner join lote on (res_lot_id=lot_id)
//        inner join zona on (lot_zon_id=zon_id)
//        where res_promotor like 'NET%'");
//    $reservas = FUNCIONES::lista_bd_sql("select res_id,zon_precio from reserva_terreno
//        inner join lote on (res_lot_id=lot_id)
//        inner join zona on (lot_zon_id=zon_id)
//        where res_multinivel = 'si' and res_monto_m2=0");



    $reservas = FUNCIONES::lista_bd_sql("select res_id,zon_precio from reserva_terreno

        inner join lote on (res_lot_id=lot_id)

        inner join zona on (lot_zon_id=zon_id)

        where res_monto_m2=0 and res_estado in ('Pendiente','Habilitado')");



    $conec = new ADO();

    foreach ($reservas as $res) {

        $conec->ejecutar("update reserva_terreno set res_monto_m2=$res->zon_precio where res_id=$res->res_id");
    }
}

function establecer_m2_reservas_concretadas() {

//    $reservas = FUNCIONES::lista_bd_sql("select res_id,zon_precio from reserva_terreno
//        inner join lote on (res_lot_id=lot_id)
//        inner join zona on (lot_zon_id=zon_id)
//        where res_promotor like 'NET%'");
//    $reservas = FUNCIONES::lista_bd_sql("select res_id,zon_precio from reserva_terreno
//        inner join lote on (res_lot_id=lot_id)
//        inner join zona on (lot_zon_id=zon_id)
//        where res_multinivel = 'si' and res_monto_m2=0");



    $reservas = FUNCIONES::lista_bd_sql("select res_id,ven_metro from reserva_terreno

        inner join venta on (ven_res_id=res_id)

        where res_monto_m2=0 and res_estado in ('Concretado') and ven_estado in ('Pendiente','Pagado')");



    $conec = new ADO();

    foreach ($reservas as $res) {

        $conec->ejecutar("update reserva_terreno set res_monto_m2=$res->ven_metro where res_id=$res->res_id");
    }
}

function establecer_ci_reservas() {

    $reservas = FUNCIONES::lista_bd_sql("

        select * from reserva_terreno

        inner join lote on (res_lot_id=lot_id)

        inner join zona on (lot_zon_id=zon_id)

        inner join urbanizacion on (res_urb_id=urb_id)

        where res_multinivel = 'si'");



    $conec = new ADO();

    foreach ($reservas as $res) {

        $precio_terreno = $res->lot_superficie * $res->zon_precio;

        $ci = $precio_terreno * $res->urb_porc_ci_multinivel / 100;

        $conec->ejecutar("update reserva_terreno set res_ci=$ci where res_id=$res->res_id");
    }
}

function importar_lotes($urb_id, $uv, $zona, $manzano, $lote, $superfice) {

    $conec = new ADO();

//    $tlotes = FUNCIONES::lista_bd_sql("select * from tmp_pedro_sec");

    $urb = new stdClass();

    $urb->urb_id = $urb_id;

//    foreach ($tlotes as $tlote) {

    $uv_nombre = $uv;

    $man_nro = $manzano;

    $lot_nro = $lote;

    $superficie = $superfice;

    $zon_nombre = $zona;

    $zon_precio = 0;

    $valor_co = 0;



    $estado = 'Disponible';

    $codigo_lote = "M{$man_nro}L{$lot_nro}";



    $man_id = get_manzano_id($man_nro, $urb->urb_id);

    $zon_id = get_zona_id($zon_nombre, $zon_precio, $urb->urb_id);

    $uv_id = get_uv_id($uv_nombre, $urb->urb_id);

    $sql_ins = "insert into lote (

                    lot_nro,lot_estado,lot_man_id,lot_zon_id,

                    lot_superficie,lot_uv_id,lot_codigo

                )values(

                    '$lot_nro','$estado','$man_id','$zon_id',

                    '$superficie','$uv_id','$codigo_lote'

                )";

    $conec->ejecutar($sql_ins, false, true);

    $lot_id = ADO::$insert_id;

    return $lot_id;

//        $fecha=date('Y-m-d');
//        $hora=date('H:i:s');
//        $sql_ins="insert into bloquear_terreno (
//                        bloq_lot_id,bloq_fecha,bloq_hora,bloq_tipo,bloq_estado,
//                        bloq_usu_id,bloq_int_id,bloq_nota,bloq_vdo_id
//                    )values(
//                        '$lot_id','$fecha','$hora','CARTERA','Habilitado',
//                        'admin','0','','0'
//                    )";
//        $conec->ejecutar($sql_ins, false, false);
//    }
//    echo "-- OK " . count($tlotes) . '<br>';
}

function get_manzano_id($man_nro, $urb_id) {

    $conec = new ADO();

    $manzano = FUNCIONES::objeto_bd_sql("select * from manzano where man_nro='$man_nro' and man_urb_id='$urb_id'");

    if ($manzano) {

        return $manzano->man_id;
    } else {

        $sql_ins = "insert into manzano (man_nro,man_urb_id)values('$man_nro','$urb_id')";

        $conec->ejecutar($sql_ins, false, true);

        $man_id = ADO::$insert_id;

        return $man_id;
    }
}

function get_zona_id($zon_nombre, $zon_precio, $urb_id) {

    $conec = new ADO();

    $zona = FUNCIONES::objeto_bd_sql("select * from zona where zon_nombre='$zon_nombre' and zon_urb_id='$urb_id'");

    if ($zona) {

        return $zona->zon_id;
    } else {

        $sql_ins = "insert into zona (zon_nombre,zon_precio,zon_urb_id)values('$zon_nombre','$zon_precio','$urb_id')";

        $conec->ejecutar($sql_ins, false, true);

        $zon_id = ADO::$insert_id;

        return $zon_id;
    }
}

function get_uv_id($uv_nombre, $urb_id) {

    $conec = new ADO();

    $uv = FUNCIONES::objeto_bd_sql("select * from uv where uv_nombre='$uv_nombre' and uv_urb_id='$urb_id'");

    if ($uv) {

        return $uv->uv_id;
    } else {

        $sql_ins = "insert into uv (uv_nombre,uv_urb_id)values('$uv_nombre','$urb_id')";

        $conec->ejecutar($sql_ins, false, true);

        $uv_id = ADO::$insert_id;

        return $uv_id;
    }
}

