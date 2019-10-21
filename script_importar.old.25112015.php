<!--<meta http-equiv="Content-Type" content="text/html; charset=utf-8">-->
<?php
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
define('_urbs', '2,3,4,6');
_db::open();
//zonas();
//manzanos();
//uv();
//lotes();

//internos();
//reservas(); 
//reservas_pagos();  
//ventas(); 
//set_des_inc_ventas();
//insertar_pagos();
//listado_observados();

function listado_observados() {
    $ventas = FUNCIONES::objetos_bd_sql("select * from venta where ven_id in (select distinct vpag_ven_id from venta_pago where vpag_capital_desc>0 and vpag_monto>0) order by ven_id asc ");
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $tven= FUNCIONES::objeto_bd_sql("select * from tmp_ventas where py='$venta->ven_urb_id' and numero_adj='$venta->ven_numero'");
        if($tven->estado=='CREDITO'){
            $nombre=FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id=$venta->ven_int_id");
            echo "$venta->ven_id;$venta->ven_urb_id;$venta->ven_numero;$nombre;$venta->ven_monto_efectivo;$tven->estado<br>";
        }
        $ventas->siguiente();
        
    }

}

function insertar_pagos() {
    $fecha_limite='2015-11-16';
    $conec = new ADO();
    
//    $sql_trunc="truncate interno_deuda";$conec->ejecutar($sql_trunc);
//    $sql_trunc="truncate venta_cobro";$conec->ejecutar($sql_trunc);
//    $sql_trunc="truncate venta_pago";$conec->ejecutar($sql_trunc);
//    $sql_trunc="truncate interno_deuda_pago";$conec->ejecutar($sql_trunc);
//    $sql_trunc="update venta set ven_estado='Pendiente', ven_tipo_plan='plazo',ven_plazo='0',ven_cuota='0',ven_rango='1', ven_costo_pag=0, ven_importado='0' ";$conec->ejecutar($sql_trunc);
//    
//    $and_filtro = "";
//    $and_filtro = " and ven_id=3170";
//    $and_filtro = " and ven_id=2403 ";
//    $and_filtro = " and (ven_id=2403  or ven_id=2405 or ven_id=2406)";
//    $and_filtro = " and ven_id=4605";
//    $and_filtro = " and ven_id=4406";    
//    $and_filtro = " and ven_id=805";
//    $and_filtro = " and ven_id >=1 and ven_id <=4625";
//    $and_filtro = " and ven_urb_id=3 and ven_id >=4626 and ven_id <=7225";

//    $and_filtro = " and ven_id >=1 and ven_id <=10";
//    $and_filtro = " and ven_id >=11 and ven_id <=50";
//    $and_filtro = " and ven_id >=51 and ven_id <=200";
//    $and_filtro = " and ven_id >=201 and ven_id <=220";
//    $and_filtro = " and ven_id >=221 and ven_id <=230";
//    $and_filtro = " and ven_id >=231 and ven_id <=250";
//    $and_filtro = " and ven_id >=251 and ven_id <=1000";
//    $and_filtro = " and ven_id >=1001 and ven_id <=7313";
//    $and_filtro = " and ven_id >=2288 and ven_id <=2290"; 
//    $and_filtro = " and ven_id >=2291 and ven_id <=3000"; 
//    $and_filtro = " and ven_id >=1 and ven_id <=1000";
    
//    $and_filtro = " and ven_id >=805 and ven_id <=1300";
//    $and_filtro = " and ven_id >=1301 and ven_id <=2000";
//    $and_filtro = " and ven_id >=1001 and ven_id <=2000";
//    $and_filtro = " and ven_id >=2001 and ven_id <=3000";
//    $and_filtro = " and ven_id >=3001 and ven_id <=4000";
//    $and_filtro = " and ven_id >=4001 and ven_id <=5000";
//    $and_filtro = " and ven_id >=5001 and ven_id <=6000";
//    $and_filtro = " and ven_id >=6001 and ven_id <=7000";
//    $and_filtro = " and ven_id >=7001 and ven_id <=8000";
    
//    $and_filtro = " and ven_id >=51 and ven_id <=500";
//    $and_filtro = " and ven_id >=501 and ven_id <=1000";
//    $and_filtro = " and ven_id >=1001 and ven_id <=2000";
//    $and_filtro = " and ven_id >=2001 and ven_id <=3000";
//    $and_filtro = " and ven_id >=3001 and ven_id <=4000";
//    $and_filtro = " and ven_id >=4001 and ven_id <=5000";
//    $and_filtro = " and ven_id >=5001 and ven_id <=6C000";
//    $and_filtro = " and ven_id >=6001 and ven_id <=7000";
    $and_filtro = " and ven_id >=7001 and ven_id <=8000";
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

            reformular_plan_cuota($venta,true, $conec);
            //cuadrar saldo a financiar
            $tmp_venta=  FUNCIONES::objeto_bd_sql("select * from tmp_ventas where py='$venta->ven_urb_id' and numero_adj='$venta->ven_numero'");
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
//        echo $txt_msj;
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
        reformular_plan_cuota($venta, $conec);
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
                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_codigo,idp_tipo,
                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                    )values(
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$pag_codigo','interes',
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
                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_codigo,idp_tipo,
                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                    )values(
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$pag_codigo','capital',
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
                                            idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_codigo,idp_tipo,
                                            idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                            idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                        )values(
                                            '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$pag_codigo','costo',
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
                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_codigo,idp_tipo,
                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                    )values(
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$pag_codigo','formulario',
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
                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_codigo,idp_tipo,
                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                    )values(
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$pag_codigo','mora',
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
    $sql_up_venta = "update venta set ven_plazo=$nro_cuotas where ven_id=$venta->ven_id";
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
            $fecha_inicio=$uid->$uid->ind_fecha_programada;
            $afecha=  explode('-', $fecha_inicio);
            $fecha_sig_cuota=  FUNCIONES::sumar_meses($uid->ind_fecha_programada,$venta->ven_rango,$afecha[2]);
        } else {
            $nro_inicio = 1;
        }
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
        'fecha_inicio' => '',
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
        $lista_pagos = plan_de_pagos($data);
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

function ventas() { 
//    $and_filtro = " and PY='2'  and Numero_Adj=1 ";
    $urbs=_urbs;
//    $fecha_limite='2015-08-11';
//    $and_filtro = " and fecha <='$fecha_limite'";
    $and_filtro = "";
    $adjudicados = _db::query("select * from adjudicatario  where PY in ($urbs) $and_filtro order by fecha asc ");
    echo '-- ' . count($adjudicados) . '<br>';
//    return;
    $num = 0;
    $insert = "insert into venta(
                    ven_int_id,ven_co_propietario,ven_lot_id,ven_urb_id,ven_fecha,ven_moneda,ven_res_id,
                    ven_metro,ven_superficie,ven_valor,ven_decuento,ven_monto,ven_res_anticipo,ven_monto_intercambio,ven_monto_efectivo,
                    ven_estado,ven_usu_id,ven_tipo,ven_val_interes,ven_cuota_inicial,ven_tipo_plan,ven_plazo,ven_cuota,ven_frecuencia,
                    ven_observacion,ven_codigo,ven_vdo_id,ven_comision,ven_concepto,
                    ven_tipo_pago,ven_fecha_cre,ven_monto_pagar,ven_form,ven_intercambio_ids,ven_intercambio_montos,
                    ven_costo,ven_costo_cub,ven_costo_pag,ven_numero,ven_promotor
                ) values";
    $sql_insert = $insert;
    $reg = 0;
//    $lotes_vendidos=array();
    foreach ($adjudicados as $tobj) {
        if ($num == 440) {
//            echo "$sql_insert<br>";
            FUNCIONES::bd_query($sql_insert);
            $sql_insert = $insert;
            $num = 0;
        }

        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_codigo='$tobj->Codigo_C'");
        $tres = _db::objeto_sql("select * from reservas_adjudicados where PY='$tobj->PY' and Numero_Reserva='$tobj->Numero_Reserva'");
        $sql_lote = "select * from lote, manzano, urbanizacion, uv, zona where urb_id='$tres->PY' and man_nro='$tres->MZA' and lot_nro='$tres->Lote' and lot_man_id=man_id and man_urb_id=urb_id and lot_zon_id=zon_id and lot_uv_id=uv_id";
        $lote = FUNCIONES::objeto_bd_sql($sql_lote);
        if ($lote) {
            if ($num > 0) {
                $sql_insert.=',';
            }
            $af = explode(' ', $tobj->Fecha);
            $ven_fecha = $af[0];
            $tpromotor = _db::objeto_sql("select * from promotor where id='$tres->CodPromotor'");
//            if(!$interno){
//                echo "-- NO EXISTE INTERNO <BR>";
//            }
//            if(!$tres){
//                echo "-- NO EXISTE TEMP_RESERVA *** '$tobj->PY' , '$tobj->Numero_Reserva' <BR>";                
//            }
//            if(!$tpromotor){
//                echo "-- NO EXISTE PROMOTOR<BR>";                
//            }
            $_mon = $tobj->Moneda;
            $moneda = 0;
            $valor_m2 = $tobj->Costo_M2;
            $superficie = $lote->lot_superficie;
            
            $tmp_venta=  FUNCIONES::objeto_bd_sql("select * from tmp_ventas where py='$tobj->PY' and numero_adj='$tobj->Numero_Adj'");
            
            if($superficie==1){
                $superficie=($tmp_venta->capital_financiado+$tres->Cuota_INI)/$valor_m2;
            }
            $valor = $superficie * $valor_m2;
            $descuento = 0;
            $monto = $valor - $descuento;
            $res_anticipo = $tres->Cuota_INI;
//            $saldo_financiar = $monto - $tres->Cuota_INI;
            $estado = 'Pendiente';
            $ven_tipo = 'Credito';

            
            $interes = $tobj->Interes * 12;
            $concepto = "Urb:$lote->urb_nombre - Mza:$lote->man_nro - Lote:$lote->lot_nro - Zona:$lote->zon_nombre - UV:$lote->uv_nombre";
            $fecha_cre = date('Y-m-d H:i:s');
            $costo_m2 = 3.5;
            $costo = $superficie * $costo_m2;
            $costo_cub = $res_anticipo;
//            $monto_efectivo=$tobj->SaldoCapital+$tobj->CapitalPagado;
            
            if(!$tmp_venta){
                if ($tobj->TotalPagar == 0) {
                    $monto_efectivo = $tobj->SaldoCapital + $tobj->CapitalPagado;
                    if ($monto_efectivo == 0) {
                        $monto_efectivo = $monto - $res_anticipo;
                    }
                } else {
                    $monto_efectivo = $monto - $res_anticipo;
                }
            }else{
                $monto_efectivo=$tmp_venta->capital_financiado;
            }
            if ($monto_efectivo <= 0) {
                $estado = 'Pagado';
                $ven_tipo = 'Contado';
            }
            $monto_pagar = 0;
            if ($monto_efectivo < 0) {
                $monto_pagar = $monto_efectivo * (-1);
                $monto_efectivo =0;
            }

//            if($monto_efectivo==0){
//                
//            }
            if ($res_anticipo > $costo) {
                $costo_cub = $costo;
            }

            if ($_mon == '1') {
                $moneda = 2;
            } else {
                $moneda = 1;
            }
            //        $insert="insert into venta(
            //                    ven_int_id,ven_co_propietario,ven_lot_id,ven_urb_id,ven_fecha,ven_moneda,ven_res_id,
            //                    ven_metro,ven_superficie,ven_valor,ven_decuento,ven_monto,ven_res_anticipo,ven_monto_intercambio,ven_monto_efectivo,
            //                    ven_estado,ven_usu_id,ven_tipo,ven_val_interes,ven_cuota_inicial,ven_tipo_plan,ven_plazo,ven_cuota,ven_frecuencia
            //                    ven_observacion,ven_codigo,ven_vdo_id,ven_comision,ven_vdo_sec,ven_comision_sec,ven_concepto,
            //                    ven_tipo_pago,ven_fecha_cre,ven_monto_pagar,ven_form,ven_intercambio_ids,ven_intercambio_montos,
            //                    ven_costo,ven_costo_cub,ven_costo_pag,ven_numero,ven_promotor
            //                ) values";
            $sql_insert.="('$interno->int_id','0','$lote->lot_id','$tobj->PY','$ven_fecha','$moneda','0',
                            '$valor_m2','$superficie','$valor','$descuento','$monto','$res_anticipo','0','$monto_efectivo',
                            '$estado','admin','$ven_tipo','$interes','0','plazo','','','dia_mes',
                            '','$tobj->PY-M{$lote->man_nro}L{$lote->lot_nro}-1',0,0,'$concepto',
                            'Normal','$fecha_cre','$monto_pagar','0','','',
                            '$costo','$costo_cub','0','$tobj->Numero_Adj','$tpromotor->Nombre'
                            )";
            $num++;
            $reg++;
//            $lotes_vendidos[]=$lote->lot_id;
        } else {
            if (!$tres) {
                echo "-- NO EXISTE TEMP_RESERVA *** '$tobj->PY' , '$tobj->Numero_Reserva' <BR>";
            }
            echo "-- NO EXISTE LOTE --$tres->PY, $tres->MZA, $tres->Lote-- select * from lote, manzano, urbanizacion, uv, zona where urb_id='$tres->PY' and man_nro='$tres->MZA' and lot_nro='$tres->Lote' and lot_man_id=man_id and man_urb_id=urb_id and lot_zon_id=zon_id and lot_uv_id=uv_id<BR>";
        }
    }

    if ($num > 0) {
//        echo "$sql_insert<br>";
        FUNCIONES::bd_query($sql_insert);
    }

//    $sql_up_lotes="update lote set lot_estado='Vendido' where res_id in (".  implode(',', $lotes_vendidos).")";
//    echo $sql_up_lotes.';<br>';
//    FUNCIONES::bd_query($sql_up_lotes);

    echo "-- <BR>TOTAL REGISTRADOS $reg";
}

function reservas_pagos_0() {
//    $urbs=_urbs;
    $reservas = FUNCIONES::lista_bd_sql("select * from reserva_terreno where res_estado in ('Habilitado','Pendiente')");
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
        $trpagos = _db::query("select * from reservas_pagos where PY='$res->res_urb_id' and Numero_Reserva='$res->res_numero' and sw=0 and round(Monto,2)>0 order by fecha asc");
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
            $monto = $rpag->Monto;
            $_mon = $res->Moneda;
            $moneda = 0;
            if ($_mon == '1') {
                $moneda = 2;
            } else {
                $moneda = 1;
            }
            $af = explode(' ', $rpag->Fecha);
            $res_fecha = $af[0];
            $res_hora = $af[1];
            $estado = 'Pagado';
            $usu_id = 'admin';
            $glosa = "Pago de anticipo de Reserva $rpag->PY,$rpag->Numero_Reserva";
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

function reservas_pagos() {
//    $urbs=_urbs;
    $reservas = FUNCIONES::lista_bd_sql("select * from reserva_terreno where res_estado in ('Habilitado','Pendiente')");
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
        $trpagos = _db::query("select * from reservas_pagos where PY='$res->res_urb_id' and Numero_Reserva='$res->res_numero' and sw=0 and round(Monto,2)>0 order by fecha asc");
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
            $monto = $rpag->Monto;
            $_mon = $res->Moneda;
//            $moneda = 0;
//            if ($_mon == '1') {
//                $moneda = 2;
//            } else {
//                $moneda = 1;
//            }
            $moneda = 2;
            if ($_mon == '1') { // dolar
                $monto_pag = $monto;
//                $moneda = 2;
            } else { //bolivianos
                $monto_pag = $monto / 6.96;
//                $moneda = 1;
            }
            $af = explode(' ', $rpag->Fecha);
            $res_fecha = $af[0];
            $res_hora = $af[1];
            $estado = 'Pagado';
            $usu_id = 'admin';
            $glosa = "Pago de anticipo de Reserva $rpag->PY,$rpag->Numero_Reserva";
            $res_id = $res->res_id;

            $sql_insert.="('$monto_pag','$moneda','$res_fecha','$res_hora',
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

function reservas() {
    $urbs=_urbs;
    $tlotes = _db::query("select * from reservas where PY in ($urbs)order by fecha asc");
    echo '-- ' . count($tlotes) . '<br>';
    $num = 0;
    $insert = "insert into reserva_terreno(
                    res_urb_id,res_numero,res_int_id,res_vdo_id,res_lot_id,res_fecha,res_hora,res_estado,res_usu_id,
                    res_monto_referencial,res_moneda,res_plazo_fecha,res_plazo_hora,res_nota,res_promotor
            ) values";
    $sql_insert = $insert;
    $reg = 0;
    $lotes_reservados = array();
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
        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_codigo='$tobj->Codigo_C'");
        $sql_lote = "select * from lote, manzano, urbanizacion where urb_id='$tobj->PY' and man_nro='$tobj->MZA' and lot_nro='$tobj->Lote' and lot_man_id=man_id and man_urb_id=urb_id";
        $lote = FUNCIONES::objeto_bd_sql($sql_lote);
        $af = explode(' ', $tobj->Fecha);
        $res_fecha = $af[0];
        $res_hora = $af[1];
        $tpromotor = _db::objeto_sql("select * from promotor where id='$tobj->CodPromotor'");
        if (!$interno) {
            echo "-- NO EXISTE INTERNO <BR>";
        }
        if (!$lote) {
            echo "-- NO EXISTE LOTE select * from lote, manzano, urbanizacion where urb_id='$tobj->PY' and man_nro='$tobj->MZA' and lot_nro='$tobj->Lote' and lot_man_id=man_id and man_urb_id=urb_id<BR>";
        }
        if (!$tpromotor) {
            echo "-- NO EXISTE PROMOTOR<BR>";
        }
        $_mon = $tobj->Moneda;
        $moneda = 0;
        if ($_mon == '1') {
            $moneda = 2;
        } else {
            $moneda = 1;
        }
        $sql_insert.="('$tobj->PY','$tobj->Numero_Reserva','$interno->int_id','0','$lote->lot_id','$res_fecha','$res_hora','Pendiente','admin',
                        '$tobj->Cuota_INI','$moneda','','','','$tpromotor->Nombre'
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
    $tclientes = _db::query("select * from clientes where Codigo_C>9999");
    echo '-- ' . count($tclientes);
    echo '<br>';
    $insert = "insert into interno(int_codigo,int_nombre,int_apellido,int_email,int_foto,int_telefono,int_celular,int_direccion,int_ci,int_fecha_nacimiento,int_fecha_ingreso,int_usu_id) values";
    $num = 0;
    $sql_insert = $insert;
    $fecha_cre = date('Y-m-d');
    $reg = 0;
    foreach ($tclientes as $tobj) {
        if ($num == 440) {
//            echo "$sql_insert<br>";
            FUNCIONES::bd_query($sql_insert);
            $sql_insert = $insert;
            $num = 0;
        }
        $interno=  FUNCIONES::objeto_bd_sql("select * from interno where int_codigo='$tobj->Codigo_C'");
        
        if ($num > 0 && !$interno) {
            $sql_insert.=',';
        }
        $nombre = str_replace("'", "\'", $tobj->Nombre);
        $direccion = str_replace("'", "\'", $tobj->Direccion);
        
        if(!$interno){
            $sql_insert.="('$tobj->Codigo_C','$nombre','','','','$tobj->Telefono','','$direccion','$tobj->CI','','$fecha_cre','admin')";
            $num++;
        }else{
//            $sql_up="update interno set int_nombre='$nombre',int_telefono='$tobj->Telefono',int_direccion='$direccion',int_ci='$tobj->CI' where int_codigo='$tobj->Codigo_C'" ;
//            FUNCIONES::bd_query($sql_up);
        }
        $reg++;
    }

    if ($num > 0) {
//        echo "$sql_insert<br>";
        FUNCIONES::bd_query($sql_insert);
    }
    echo "-- <BR>TOTAL REGISTRADOS $reg";
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