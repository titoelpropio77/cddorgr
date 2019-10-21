
<?php

require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
define('_urbs', '2,3,4,6');
//define('_urbs', '3');
_db::open();
//update_bisito();

if($_GET[corregir]=='ok'){
    $venta=  FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[ven_id]");
//    echo "<pre>";
//    print_r($venta);
//    echo "</pre>";
    $tventa=  FUNCIONES::objeto_bd_sql("select * from tmp_ventas where numero_adj=$venta->ven_numero and py=$venta->ven_urb_id");
    $monto_efectivo=$tventa->capital_financiado;
//    resetear_venta($venta->ven_id, $monto_efectivo);
//    insertar_pagos($venta->ven_id); 
}else{
    revisar_ventas();
}


function revisar_ventas(){
    $ventas=  FUNCIONES::objetos_bd_sql("select * from venta where ven_numero>0 and ven_id in (63,1341,1743,1764,1775,1881,1898,1915,1920,1922,1931,1938,1962,2101,2289,4071,4098,4109,4703,4710,6692,6997,7332)");
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta=$ventas->get_objeto();
        $tventa=  FUNCIONES::objeto_bd_sql("select * from tmp_ventas where numero_adj=$venta->ven_numero and py=$venta->ven_urb_id");
        if($tventa){
            $monto_efectivo=$tventa->capital_financiado;
            $dif= abs($venta->ven_monto_efectivo-$monto_efectivo);
            if($dif>0.5){
                echo "Diferente $venta->ven_urb_id; $venta->ven_id;<b>$venta->ven_monto_efectivo!=$monto_efectivo</b>
                    <a href='gestor.php?mod=venta&tarea=SEGUIMIENTO&id=$venta->ven_id' target='_blank'>SEG</a>
                    <a href='script_corregir_venta.php?corregir=ok&ven_id=$venta->ven_id' target='_blank'>CORREGIR</a>
                    <br>";
                
            }
        }
        $ventas->siguiente();
    }
}


function resetear_venta($ven_id,$monto_efectivo){
    $conec = new ADO();
    $sql_up="update venta set ven_importado='0', ven_costo_pag=0,ven_monto_efectivo='$monto_efectivo' , ven_estado='Pendiente' where ven_id=$ven_id";
    $conec->ejecutar($sql_up);
    $sql_up="delete from interno_deuda_pago where idp_vpag_id in (select vpag_id from venta_pago where vpag_ven_id=$ven_id);";
    $conec->ejecutar($sql_up);
    $sql_up="delete from venta_pago where vpag_ven_id=$ven_id";
    $conec->ejecutar($sql_up);
    $sql_up="delete from interno_deuda where ind_tabla_id=$ven_id";
    $conec->ejecutar($sql_up);
    $sql_up="delete from venta_cobro where vcob_ven_id=$ven_id";
    $conec->ejecutar($sql_up);
}


function insertar_pagos($ven_id) {
    $fecha_limite='2015-11-25';
    $conec = new ADO();
    
    $and_filtro = " and ven_id =$ven_id ";
    
//    echo "select * from venta where ven_estado='Pendiente' and ven_importado='0' $and_filtro order by ven_id asc <br>";
    $ventas = FUNCIONES::objetos_bd_sql("select * from venta where ven_estado='Pendiente' and ven_importado='0' $and_filtro order by ven_id asc ");
    
    $jr = 0;

    /// RECORRER LOS PAGOS POR RECIBO
    $nro_ventas=0;
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
//        echo "select * from pagos where  fecha<='$fecha_limite' and PY=$venta->ven_urb_id and Numero_Adj=$venta->ven_numero and sw=0 and tipo in ('0','2') order by fecha asc,recibo asc,Secuencia asc; <br>";
        $tpagos = _db::objetos_sql("select * from pagos where  fecha<='$fecha_limite' and PY=$venta->ven_urb_id and Numero_Adj=$venta->ven_numero and sw=0 and tipo in ('0','2') order by fecha asc,recibo asc,Secuencia asc");
        $nsaldo=$venta->ven_monto_efectivo;
        if(count($tpagos)){
            $pha = 0;
            $plan_act = null;
            $scap_pagado = 0;

    //        $recibo_actual = -1;
            $rec_pago = new stdClass();
            $num_pago=0;

            $j=0;

            while ($j<  count($tpagos)) {
                $tpag=$tpagos[$j];
                if ($tpag->Numero_Adj_His != $pha) {
    //                echo "CMP -- REF. $tpag->Numero_Adj_His != $pha <BR>";
    //                if($jr==0){
    //                echo "== REFORMULAR $recibo_actual <BR>";
                    $plan_act = reformular_plan($venta, $scap_pagado, $tpag->Numero_Adj_His, $conec);
    //                }
                    $jr++;
                    $pha = $tpag->Numero_Adj_His;
                }
                $rec_pago = new stdClass();
                unir_pago($rec_pago, $tpag);
                $tpag_sig=null;
                if($j+1<count($tpagos)){
                    $tpag_sig=$tpagos[$j+1];
                }

                if($tpag_sig && $tpag_sig->Recibo==$tpag->Recibo){
                    unir_pago($rec_pago, $tpag_sig);
                    $j++;
                }
                $rec_pago->nro_recibo=$tpag->Recibo;
                $rec_pago->saldo_efectivo=$nsaldo;
                $rec_pago->porc_interes=$plan_act->Interes * 12;
                $nsaldo=registrar_pago($rec_pago, $venta,$conec);
                $num_pago++;
                $j++;
            }
            $tmp_venta=  FUNCIONES::objeto_bd_sql("select * from tmp_ventas where py='$venta->ven_urb_id' and numero_adj='$venta->ven_numero'");
            
            if($tmp_venta){
                $venta->sig_fecha_pago=  FUNCIONES::get_fecha_mysql($tmp_venta->fecha_pago);
            }
            reformular_plan_cuota($venta,true, $conec);
            //cuadrar saldo a financiar
            if($tmp_venta){
    //            FUNCIONES::print_pre($tmp_venta);
                $saldo_actual=round(($tmp_venta->saldo_capital.'')*1,2);
                $nsaldo=round($nsaldo,2);
                $txt_msj= "<span style='color:red'>DIF $nsaldo == $tmp_venta->saldo_capital? </span>";
                if($nsaldo!=$saldo_actual){
                    $txt_msj.=";REFORM";
                    if($nsaldo>$saldo_actual){
                        $dif=$nsaldo-$saldo_actual;
                        reformular_plan_cuota_cuadrar($venta,'desc',$dif,$saldo_actual,$nsaldo);
//                    echo "<BR>DECREMENTAR $dif <br>";
                    }else{
                        $dif=$saldo_actual-$nsaldo;
                        reformular_plan_cuota_cuadrar($venta,'inc',$dif,$saldo_actual,$nsaldo);
    //                    echo "<BR>INCREMENTAR $dif <br>";
                    }
                }else{
                    $txt_msj.= ";NO_REFORM";
                }

            }
        }else{
            reformular_plan_venta($venta, $conec);
        }
        $txt_msj.= ";Venta $venta->ven_id; Nro Pagos $num_pago; <a href='gestor.php?mod=venta&tarea=SEGUIMIENTO&id=$venta->ven_id' target='_blank'>LISTADO CUOTAS</a>, <a href='gestor.php?mod=venta&tarea=EXTRACTOS&id=$venta->ven_id' target='_blank'>LISTADO EXTRACTOS</a>, <a href='gestor.php?mod=venta&tarea=VER&id=$venta->ven_id' target='_blank'>VER</a><br>";
        echo $txt_msj;
        $sql_up_venta="update venta set ven_importado='1' where ven_id=$venta->ven_id";
        $conec->ejecutar($sql_up_venta);
        $ventas->siguiente();
        $nro_ventas++;
    }
    echo "$nro_ventas OK<br>";
}

function unir_pago(&$rec_pago,$tpag){
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

function registrar_pago($rec_pago,&$venta,&$conec=null) {
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
    $nsaldo=generar_cobro($params);
    pagar_cuota($venta,$rec_pago->nro_recibo);
    
    if($rec_pago->descuento>0){
        FUNCIONES::print_pre($rec_pago);
        reformular_plan_cuota($venta,false, $conec);
    }
    return $nsaldo;
}

function desglosar_pago($pago) {

    $_descuento = $pago->descuento + $pago->form_desc;
    
    if($pago->interes_pag>$pago->monto){
        $pago->interes_pag=($pago->saldo_efectivo*$pago->porc_interes)/100/12;
        if($pago->interes_pag>$pago->monto){
            $pago->interes_pag=0;
        }
    }

    $_desc_monto = $_descuento - $pago->form_desc;
    $_cap_monto = $pago->monto + $pago->form_pag - $_descuento - ($pago->form_pag - $pago->form_desc);
    $pago->desc_monto = $_desc_monto;
    $pago->cap_monto = $_cap_monto;

    $pago->desc_form = $pago->form_desc;
    if($_cap_monto>0){
        $pago->desc_interes = $pago->interes_pag * $_desc_monto / $pago->monto;
        if($pago->desc_interes>$pago->interes_pag){
            $pago->desc_interes =0;
        }
    }else{
        $pago->desc_interes =0;
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
    $tot_capial = ($pago->pag_capital.'')*1;
    $cuotas_capital = array();
    
    if($tot_capial>0){
        foreach ($acuotas as $acu) {
            $cuotas_capital[] = clone $acu;
            $_sum_cap+=($acu->ind_capital.'')*1 - ($acu->ind_capital_pagado.'')*1;
//            echo "$_sum_cap >= $tot_capial <br>";
            if ($_sum_cap >= $tot_capial) {
                break;
            }
        }
    }else{
        if(count($acuotas)>0){
            $cuotas_capital[]=clone $acuotas[0];
        }
    }
    
//    FUNCIONES::print_pre($cuotas_capital);

//    $cuotas_capital = FUNCIONES::lista_bd_sql($sql_cuotas_capital);

    $capital_desc = $pago->desc_capital;
    $capital = 0;
    $capital_ids = array();
    $capital_montos = array();
//    echo 'Cant '.count($cuotas_capital).' #######<br>';
    $fecha_valor=$cuotas_capital[count($cuotas_capital)-1]->ind_fecha_programada;
    foreach ($cuotas_capital as $cu) {
        $mcapital = round($cu->ind_capital - $cu->ind_capital_pagado,2);
//        echo "$cu->ind_id ****-----*** $mcapital<br>";
        if ($mcapital + $capital >= $pago->pag_capital) {
            $mcapital = round($pago->pag_capital - $capital,2);
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

function pagar_cuota(&$venta,$nro_recibo) {
    $conec = new ADO();
    $conec->begin_transaccion();
//    $codigo = $_POST[cob_codigo];
    $cob = FUNCIONES::objeto_bd_sql("select * from venta_cobro where vcob_ven_id='$venta->ven_id'");

    if (!$cob) {
//        $url = "$this->link?mod=$this->modulo&tarea=PAGOS&id=$venta->ven_id";
        echo "No existe nigun cobro a ejecutarse por esta venta";
        return false;
    }
//    echo "<pre>";
//    print_r($cob);
//    echo "</pre>";

    $fecha_cre = date('Y-m-d H:i:s');
    $usuario_id = 'admin';
    $fecha_pago = $cob->vcob_fecha_pago;
    $pag_codigo = FUNCIONES::fecha_codigo();
    //crear registro venta pago
    $sql_pago = "insert into venta_pago(
                        vpag_ven_id,vpag_codigo,vpag_fecha_pago,vpag_fecha_valor,
                        vpag_int_id,vpag_moneda,vpag_saldo_inicial,vpag_dias_interes,
                        vpag_interes,vpag_capital,vpag_mora,vpag_form,vpag_monto,vpag_saldo_final,
                        vpag_interes_ids,vpag_capital_ids,vpag_form_ids,vpag_mora_ids,vpag_mora_con_ids,vpag_mora_gen_ids,
                        vpag_interes_montos,vpag_capital_montos,vpag_form_montos,vpag_mora_montos,vpag_mora_con_montos,vpag_mora_gen_montos,vpag_mora_gen_dias,
                        vpag_fecha_cre,vpag_usu_cre,vpag_estado,vpag_cob_usu,vpag_cob_codigo,vpag_cob_aut,vpag_capital_desc,vpag_interes_desc,vpag_form_desc,
                        vpag_recibo,vpag_importado
                    )values(
                        '$venta->ven_id','$pag_codigo','$fecha_pago','$cob->vcob_fecha_valor',
                        '$venta->ven_int_id','$venta->ven_moneda','$cob->vcob_saldo_inicial','$cob->vcob_dias_interes',
                        '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_monto','$cob->vcob_saldo_final',
                        '$cob->vcob_interes_ids','$cob->vcob_capital_ids','$cob->vcob_form_ids','$cob->vcob_mora_ids','$cob->vcob_mora_con_ids','$cob->vcob_mora_gen_ids',
                        '$cob->vcob_interes_montos','$cob->vcob_capital_montos','$cob->vcob_form_montos','$cob->vcob_mora_montos','$cob->vcob_mora_con_montos','$cob->vcob_mora_gen_montos','$cob->vcob_mora_gen_dias',
                        '$fecha_cre','$usuario_id','Activo','$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut','$cob->vcob_capital_desc','$cob->vcob_interes_desc','$cob->vcob_form_desc',
                        '$nro_recibo','1'
                    )";
    $conec->ejecutar($sql_pago, false);
    $pago_id = mysql_insert_id();

    //actualizar interno_deuda
    // ******** PAGAR INTERES
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
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$nro_recibo','$pag_codigo','interes',
                                        '$fecha_pago','$interes_pagado','Activo','$fecha_cre','$usuario_id',
                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'
                                    )
                        ";
                $conec->ejecutar($sql_ins_idp);

                $set_int_desc = "";
                if ($i == 0 && $cob->vcob_interes_desc>0) {
                    $set_int_desc = ", ind_interes_desc=$cob->vcob_interes_desc";
                }

                $sql = "update interno_deuda set 
                                ind_interes_pagado=ind_interes_pagado+$interes_pagado,  
                                ind_monto_pagado=ind_capital_pagado+ind_interes_pagado $set_int_desc
                                where ind_id = '$cu->ind_id'
                            ";
                //            echo $sql.';<br>';
                $conec->ejecutar($sql);
            }
        } else {
            $conec->rollback();
            $mensaje = "Interes: Cantidad de cuotas de interes diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
    }
    // ******** PAGAR CAPITAL
    $costo_cub = 0;
    $costo_pag = 0;
    $costo = 0;
    $costo_ids = array();
    $costo_montos = array();
    $str_capital_ids = trim($cob->vcob_capital_ids);
    /* AQUI ME QUEDE... CALCULANDO EL SALDO DEL COSTO DE VENTA.. */
    $saldo_venta = $venta->ven_monto_efectivo - $venta->ven_cuota_inicial;
    $saldo_costo = $venta->ven_costo - $venta->ven_cuota_inicial - $venta->ven_costo_cub;
    if (($str_capital_ids && $cob->vcob_capital > 0) || ($cob->vcob_capital_desc>0 && $str_capital_ids )) {
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
//                echo "cmp capital: $capital_pagado== $cu->ind_capital<br>";
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
                            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
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
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
                        return;
                    }
                }

                $sql_ins_idp = "insert into interno_deuda_pago(
                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,
                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                    )values(
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$nro_recibo','$pag_codigo','capital',
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
                                            '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$nro_recibo','$pag_codigo','costo',
                                            '$fecha_pago','$costo_pagado','Activo','$fecha_cre','$usuario_id',
                                            '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'
                                        )
                            ";
                    $conec->ejecutar($sql_ins_idp);
                }
                $set_cap_desc = "";
                if ($i == count($cuotas) - 1 && $cob->vcob_capital_desc>0) {
                    $set_cap_desc = ", ind_capital_desc=$cob->vcob_capital_desc";
                }

                $sql = "update interno_deuda set 
                                ind_estado='$ind_estado',
                                ind_fecha_pago='$fecha_pago',
                                ind_capital_pagado=ind_capital_pagado+$capital_pagado,
                                ind_monto_pagado=ind_interes_pagado+ind_capital_pagado,
                                ind_costo_pagado=ind_costo_pagado+$costo_pagado, 
                                ind_saldo_final='$saldo_final' $set_cap_desc
                                where ind_id = '$cu->ind_id'
                            ";
//                    echo $sql.';<br>';
                $conec->ejecutar($sql);
            }
            //ACTUALIZAR COSTO DE VENTA PAGO
            $txt_costo_ids = implode(',', $costo_ids);
            $txt_costo_montos = implode(',', $costo_montos);
            $sql_up_pag_costo = "update venta_pago set vpag_costo='$costo', vpag_costo_ids='$txt_costo_ids', vpag_costo_montos='$txt_costo_montos'
                                where vpag_id=$pago_id";
            $conec->ejecutar($sql_up_pag_costo);
            //ACTUALIZAR COSTO DE VENTA
            $set_ven_estado = "";
            if ($cob->vcob_saldo_final <= 0) {
                $set_ven_estado = " ,ven_estado='Pagado'";
                $venta->ven_estado='Pagado';
            }
            $sql_up_ven_costo = "update venta set ven_costo_cub=ven_costo_cub+$costo_cub, ven_costo_pag=ven_costo_pag+$costo_pag $set_ven_estado
                                where ven_id=$venta->ven_id";
            $conec->ejecutar($sql_up_ven_costo);
            $venta->ven_costo_cub=$venta->ven_costo_cub+$costo_cub;
            $venta->ven_costo_pag=$venta->ven_costo_pag+$costo_pag;

        } else {
            $conec->rollback();
            $mensaje = "Capital: Cantidad de cuotas de capital diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
    }


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
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$nro_recibo','$pag_codigo','formulario',
                                        '$fecha_pago','$form_pagado','Activo','$fecha_cre','$usuario_id',
                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'
                                    )
                        ";
                $conec->ejecutar($sql_ins_idp);

                $set_form_desc = "";
                if ($i == 0 && $cob->vcob_form_desc) {
                    $set_form_desc = ", ind_form_desc=$cob->vcob_form_desc";
                }

                $sql = "update interno_deuda set 
                                ind_form_pagado=ind_form_pagado+$form_pagado $set_form_desc
                                where ind_id = '$cu->ind_id'
                            ";
                //            echo $sql.';<br>';
                $conec->ejecutar($sql);
            }
        } else {
            $conec->rollback();
            $mensaje = "Formulario: Cantidad de cuotas de formulario diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
    }

    //************ PAGO MORA
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
                                ind_mora=+$mora_generado
                                where ind_id = '$cu->ind_id'
                            ";
                //            echo $sql.';<br>';
                $conec->ejecutar($sql);
            }
        } else {
            $conec->rollback();
            $mensaje = "Mora Generada: Cantidad de cuotas de mora_gen diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
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
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$nro_recibo','$pag_codigo','mora',
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
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
    }
    $sql_del = "delete from venta_cobro where vcob_ven_id='$venta->ven_id'";
    $conec->ejecutar($sql_del);

    $exito = $conec->commit();
    if ($exito) {
//        $this->imprimir_pago($pago_id);
//            $this->formulario->ventana_volver("Pago realizado Correctamente", $this->link . '?mod=' . $this->modulo, '', $tipo);
    } else {
        $exito = false;
        $mensajes = $conec->get_errores();
        $mensaje = implode('<br>', $mensajes);
        echo "$mensaje ";
//        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }
}


function reformular_plan_cuota_cuadrar(&$venta,$tipo,$dif,$saldo_actual,$saldo_calc, &$conec = null) {
    if ($conec == null) {
        $conec = new ADO();
    }
//    $pagado = total_pagado($venta->ven_id);
    $saldo_financiar = $saldo_actual;//$venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;
    $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_capital_pagado>0 and ind_capital_pagado<=ind_capital and ind_estado='Pendiente' order by ind_id asc");
    $nro_inicio = 0;
    $fecha_inicio=  $venta->ven_fecha;
    $afecha=  explode('-', $fecha_inicio);
    $fecha_sig_cuota=  FUNCIONES::sumar_meses($venta->ven_fecha,$venta->ven_rango,$afecha[2]);//FUNCIONES::siguiente_mes($venta->ven_fecha);
    $fecha_prog=$venta->ven_fecha;
    $fecha_pag=$venta->ven_fecha;
    
    $uvp=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='Activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 2");
    if($uvp){
        $fecha_pag=$uvp->vpag_fecha_pago;
    }
    
    if ($uid) {
        $sql_up = "update interno_deuda set ind_saldo_final=$saldo_financiar, ind_estado='Pagado' where ind_id='$uid->ind_id'";
        $conec->ejecutar($sql_up);
        $nro_inicio = $uid->ind_num_correlativo + 1;
//        echo "--ant--$uid->ind_fecha_programada <br>";
        $fecha_inicio=  $uid->ind_fecha_programada;
        $afecha=  explode('-', $fecha_inicio);
        $fecha_sig_cuota=  FUNCIONES::sumar_meses($uid->ind_fecha_programada,$venta->ven_rango,$afecha[2]);
        $fecha_prog=$uid->ind_fecha_programada;
//        echo "**post--$fecha_sig_cuota <br>";
    }
    if (!$nro_inicio) {
        $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pagado' order by ind_id desc limit 0,1");
        if ($uid) {
            $nro_inicio = $uid->ind_num_correlativo + 1;
            $fecha_inicio=  $uid->ind_fecha_programada;
            $afecha=  explode('-', $fecha_inicio);
            $fecha_sig_cuota=  FUNCIONES::sumar_meses($uid->ind_fecha_programada,$venta->ven_rango,$afecha[2]);
            $fecha_prog=$uid->ind_fecha_programada;
        } else {
            $nro_inicio = 1;
        }
    }
    
    if($venta->sig_fecha_pago){
        $fecha_inicio=FUNCIONES::sumar_meses($venta->sig_fecha_pago,"-$venta->ven_rango",$afecha[2]);;
        $fecha_sig_cuota=  $venta->sig_fecha_pago;
    }

    $sql_del = "delete from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente'";
    $conec->ejecutar($sql_del);
    $fecha_cre=date('Y-m-d');
    if($dif>0){
        $capital_desc=0;
        $capital_inc=0;
        if($tipo=='desc'){
            $capital_desc=$dif;
        }elseif($tipo=='inc'){
            $capital_inc=$dif;
        }
        
        $insert_dif="insert into interno_deuda (
                            ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_usu_id,
                            ind_moneda,ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,
                            ind_estado,ind_interes_pagado,ind_capital_pagado,ind_monto_pagado,ind_saldo_final,
                            ind_fecha_pago,ind_dias_interes,ind_concepto,ind_observacion,ind_form,ind_form_pagado,
                            ind_mora,ind_mora_pagado,ind_tipo,ind_fecha_cre,ind_estado_parcial,ind_monto_parcial,
                            ind_costo_pagado,ind_capital_inc,ind_capital_desc,ind_interes_desc,ind_form_desc
                        )values(
                            'venta','$venta->ven_id','-1','$venta->ven_int_id','$fecha_cre','admin',
                            '$venta->ven_moneda','0','0',0,'$saldo_calc','$fecha_prog',
                            'Pagado','0','0','0',$saldo_actual,
                            '$fecha_pag','0','Adecuacion de Saldo $venta->ven_concepto','',0,0,
                            0,0,'pcuota','$fecha_cre','listo','0',
                            0,'$capital_inc','$capital_desc',0,0
                                
                        )";
        $conec->ejecutar($insert_dif,false);
        $cua_ind_id=  mysql_insert_id();
        $codigo=  FUNCIONES::fecha_codigo();
        $insert_pago="insert into venta_pago (
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
                            '$venta->ven_id','$codigo','$fecha_pag','$fecha_inicio','$venta->ven_int_id','$venta->ven_moneda',
                            '$saldo_calc',0,0,0,0,
                            0,0,'$saldo_actual','Activo','',
                            '','$cua_ind_id','0',
                            '','','','','',
                            '','','','',
                           '$fecha_cre','admin','admin','','0',
                            0,'','',$capital_inc,'$capital_desc',
                            0,0,'','1'
                        )";
//        echo "$insert_pago";
        $conec->ejecutar($insert_pago,false);
    }
    
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
//    echo "-------------- PLAN CUADRAR <br>";
//    FUNCIONES::print_pre($plan_data);
    $nro_cuotas = generar_plan_pagos($plan_data, $conec); //
//    $cuota = FUNCIONES::get_cuota($saldo_financiar, $interes_anual, $plazo, $aplazo[$plan->Plazo]);
    $set_estado="";
    if(round($saldo_financiar,2)<=0){
        $set_estado=", ven_estado='Pagado'";
    }
    $sql_up_venta = "update venta set ven_plazo=$nro_cuotas $set_estado where ven_id=$venta->ven_id";
    $venta->ven_plazo=$nro_cuotas;
    $conec->ejecutar($sql_up_venta);
    
}

function reformular_plan_cuota(&$venta,$cuadrar_venta=false, &$conec = null) {
    if ($conec == null) {
        $conec = new ADO();
    }
    $pagado = total_pagado($venta->ven_id);
    $saldo_financiar = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;
    $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_capital_pagado>0 and ind_capital_pagado<=ind_capital and ind_estado='Pendiente'");
    $nro_inicio = 0;
    $fecha_inicio = $venta->ven_fecha;
    $afecha=  explode('-', $fecha_inicio);
    $fecha_sig_cuota=  FUNCIONES::sumar_meses($venta->ven_fecha,$venta->ven_rango,$afecha[2]);//FUNCIONES::siguiente_mes($venta->ven_fecha);
    
    if ($uid) {
        $sql_up = "update interno_deuda set ind_saldo_final=$saldo_financiar, ind_estado='Pagado' where ind_id='$uid->ind_id'";
        $conec->ejecutar($sql_up);
        $nro_inicio = $uid->ind_num_correlativo + 1;
//        echo "--ant--$uid->ind_fecha_programada <br>";
        $fecha_inicio=$uid->ind_fecha_programada;
        $afecha=  explode('-', $fecha_inicio);
        $fecha_sig_cuota=  FUNCIONES::sumar_meses($uid->ind_fecha_programada,$venta->ven_rango,$afecha[2]);
//        $fecha_sig_cuota=  FUNCIONES::siguiente_mes($uid->ind_fecha_programada);
//        echo "**post--$fecha_sig_cuota <br>";
    }else{
        if($cuadrar_venta){
            return false;
        }
    }
    if (!$nro_inicio) {
        $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pagado' order by ind_id desc limit 0,1");
        if ($uid) {
            $nro_inicio = $uid->ind_num_correlativo + 1;
            $fecha_inicio=$uid->ind_fecha_programada;
            $afecha=  explode('-', $fecha_inicio);
            $fecha_sig_cuota=  FUNCIONES::sumar_meses($uid->ind_fecha_programada,$venta->ven_rango,$afecha[2]);
        } else {
            $nro_inicio = 1;
        }
    }
    
    if($venta->sig_fecha_pago){
        $fecha_inicio=FUNCIONES::sumar_meses($venta->sig_fecha_pago,"-$venta->ven_rango",$afecha[2]);;
        $fecha_sig_cuota=  $venta->sig_fecha_pago;
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
    $venta->ven_plazo=$nro_cuotas;
    $conec->ejecutar($sql_up_venta);
    
    guardar_fecha_valor($venta, $fecha_inicio);
}

function guardar_fecha_valor($venta,$fecha_valor) {
    $conec=new ADO();
//    $fecha_valor=  FUNCIONES::get_fecha_mysql($_POST[nfecha_valor]);
//    $fecha_pago=date('Y-m-d');
    $codigo=  FUNCIONES::fecha_codigo();
    $upago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
    if(!$upago){
        $upago=new stdClass();
        $upago->vpag_saldo_final=$venta->ven_monto_efectivo;
        $upago->vpag_fecha_valor=$venta->ven_fecha;
        $upago->vpag_fecha_pago=$venta->ven_fecha;
    }
    $fecha_cre=date('Y-m-d H:i:s');
    $insert_pago="insert into venta_pago (
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
//        $this->barra_opciones($venta);
//        echo "<br><br>";
    $conec->ejecutar($insert_pago,false);
    
}

function reformular_plan_venta(&$venta, &$conec = null) {
    if ($conec == null) {
        $conec = new ADO();
    }
    $saldo_financiar = $venta->ven_monto_efectivo;
    $plan = _db::objeto_sql("select * from plan_historico where PY=$venta->ven_urb_id and Numero_Adj=$venta->ven_numero order by  Numero_Adj_His asc");
    if(!$plan){
        return;
    }
    $nro_inicio = 1;
    $sql_del = "delete from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente'";
    $conec->ejecutar($sql_del);
    
    if ($plan) {
        $aplazo = array('1' => 1, '2' => 2, '3' => 3, '4' => 6, '5' => 0);
        $interes_anual = $plan->Interes * 12;
        $plazo = $plan->Plan;
        if($plazo<=0){
            $plazo=1;
        }
//        $saldo_financiar = $venta->ven_monto_efectivo - $sum_cap_pagado;
        $af = explode(' ', $plan->Fecha_Historico);
        $fecha_inicio=$venta->ven_fecha;
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
        $venta->ven_plazo=$nro_cuotas;
        $venta->ven_cuota=$cuota;
        $venta->ven_rango=$aplazo[$plan->Plazo];
        $venta->ven_val_interes=$interes_anual;
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
    $fecha_inicio=$venta->ven_fecha;
    if ($uid) {
        $sql_up = "update interno_deuda set ind_saldo_final=$saldo_financiar, ind_estado='Pagado' where ind_id='$uid->ind_id'";
        $conec->ejecutar($sql_up);
        $nro_inicio = $uid->ind_num_correlativo + 1;
        $fecha_inicio=$uid->ind_fecha_programada;
    }
    if (!$nro_inicio) {
        $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pagado' order by ind_id desc limit 0,1");
        if ($uid) {
            $nro_inicio = $uid->ind_num_correlativo + 1;
            $fecha_inicio=$uid->ind_fecha_programada;
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
        $rango=$aplazo[$plan->Plazo];
        $interes_anual = $plan->Interes * 12;
        $plazo = $plan->Plan/$rango;
        if($plazo<=0){
            $plazo=1;
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
        $venta->ven_plazo=$nro_cuotas;
        $venta->ven_cuota=$cuota;
        $venta->ven_rango=$rango;
        $venta->ven_val_interes=$interes_anual;
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
    } elseif ($par->tipo == "cuota" ) {//cuota mensual
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
    $nro_cuota=0;
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
    if(count($lista_pagos)>0){
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

            $fila->saldo = round($saldo,2);
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
            $saldo = round($saldo - $capital,2);
            $fila->saldo = $saldo;
            $lista_cuotas[] = $fila;
            $nro_cuota++;
            $i++;
        }
        return $lista_cuotas;
    }
}