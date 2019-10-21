<?php

require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
include_once 'clases/registrar_comprobantes.class.php';


cargar_venta();
//cargar_comision();
//cargar_pagos();
//cargar_pagos_reserva();

function cargar_venta() {
    $ges_id = 13;
    $ventas = FUNCIONES::objetos_bd_sql("select * from venta where ven_estado!='anulado'");
    echo 'ventas: '.$ventas->get_num_registros().'<br>';

    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $llave = $venta->ven_id;
        $interno = FUNCIONES::atributo_bd("interno", "int_id=" . $venta->ven_int_id, "concat(int_nombre,' ',int_apellido )");
        $fecha_cmp = $venta->ven_fecha;        
        if($venta->ven_tipo_pago=='Intercambio'){
            $monto_efectivo = $venta->ven_monto_efectivo+$venta->ven_cuota_inicial+$venta->ven_res_anticipo;
        }else{
            $monto_efectivo = $venta->ven_monto_efectivo+$venta->ven_cuota_inicial+$venta->ven_res_anticipo;
        }
        
        $monto_intercambio = $venta->ven_monto_intercambio;
        $monto = $monto_efectivo;
        $ven_tipo = $venta->ven_tipo;
        $ven_anticipo = $venta->ven_res_anticipo;
        $_POST[superficie] = $venta->ven_superficie;
        $_POST[ven_int_id] = $venta->ven_int_id;
        $_POST[descuento] = $venta->ven_decuento;
        $_POST[ven_tipo_pago] = $venta->ven_tipo_pago;
        $_POST['id_res'] = $venta->ven_res_id;
        $concepto = $venta->ven_concepto;
        $fcosto = 0.75;
        if ($ven_tipo == 'Contado') {
            $glosa = "Venta al contado Nro. $llave a " . $concepto;

            $comprobante = new stdClass();
            $comprobante->tipo = "Diario";
            $comprobante->mon_id = $venta->ven_moneda;
            $comprobante->nro_documento = str_replace('-', '', $fecha_cmp);
            $comprobante->fecha = $fecha_cmp;
            $comprobante->ges_id = $ges_id;
            $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
            $comprobante->forma_pago = "Efectivo";
            $comprobante->ban_id = 0;
            $comprobante->ban_char = '';
            $comprobante->ban_nro = '';
            $comprobante->glosa = $glosa;
            $comprobante->referido = $interno;
            $comprobante->tabla = "venta";
            $comprobante->tabla_id = $llave;

            $costo_lote = $_POST[superficie] * $fcosto;
            $anticipo = 0;
            if (isset($_POST['id_res']) && $_POST['id_res'] != "0") {
                $anticipo = $ven_anticipo * 1;
            }
            if ($monto - $anticipo > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.2.02.2.01'), "debe" => $monto - $anticipo, "haber" => 0, ///Documentos por Cobrar M/E Vigente
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $_POST[ven_int_id]
                );
            }
            if ($anticipo) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '2.1.1.05.2.01'), "debe" => $anticipo, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $_POST[ven_int_id]
                );
            }
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '5.1.1.01.1.01'), "debe" => $costo_lote, "haber" => 0,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0'
            );
            if ($_POST[descuento]) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '5.1.1.01.1.21'), "debe" => $_POST[descuento], "haber" => 0,
                    "glosa" => $glosa, "ca" => '0', "cf" => FUNCIONES::get_cuenta_cf($ges_id, '2.02.017'), "cc" => '0'
                );
            }
            if ($monto + $_POST[descuento] > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '4.1.1.01.1.02'), "debe" => 0, "haber" => $monto + $_POST[descuento], //Ingreso por VENTA Lote al Credito
                    "glosa" => $glosa, "ca" => '0', "cf" => FUNCIONES::get_cuenta_cf($ges_id, '1.01.002'), "cc" => FUNCIONES::get_cuenta_cc($ges_id, '1.03')
                );
            }

            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.3.01.1.01'), "debe" => 0, "haber" => $costo_lote,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0'
            );

            if ($_POST[ven_tipo_pago] == 'Intercambio') {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.2.3.02.1.05'), "debe" => $monto_intercambio, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $_POST[ven_int_id]
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '4.1.1.01.1.06'), "debe" => 0, "haber" => $monto_intercambio,
                    "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0'
                );
            }
//            FUNCIONES::print_pre($comprobante);
            COMPROBANTES::registrar_comprobante($comprobante);
        } elseif ($ven_tipo == 'Credito') {
            $monto_total = $monto;
            $glosa = "Venta al Credito Nro. $llave: " . $concepto;
            $comprobante = new stdClass();
            $comprobante->tipo = "Diario";
            $comprobante->mon_id = $venta->ven_moneda;
            $comprobante->nro_documento = date("Ydm");
            $comprobante->fecha = $fecha_cmp;
            $comprobante->ges_id = $ges_id;
            $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
            $comprobante->forma_pago = "Efectivo";
            $comprobante->ban_id = 0;
            $comprobante->ban_char = '';
            $comprobante->ban_nro = '';
            $comprobante->glosa = $glosa;
            $comprobante->referido = $interno;
            $comprobante->tabla = "venta";
            $comprobante->tabla_id = $llave;

            $costo_lote = $_POST[superficie] * $fcosto;
            $anticipo = 0;

            if (isset($_POST['id_res']) && $_POST['id_res'] != 0) {
                $anticipo = $ven_anticipo * 1;
            }
            if ($monto_total - $anticipo > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.2.02.2.01'), "debe" => $monto_total - $anticipo, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $_POST[ven_int_id]
                );
            }
            $interes = 0;
            if ($venta->ven_val_interes > 0) {
                $interes = FUNCIONES::atributo_bd_sql("select sum(ind_interes) as campo  from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id'");
                $interes = $interes * 1;
            }
            if ($interes) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.2.03.2.01'), "debe" => $interes, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $_POST[ven_int_id]
                );
            }
            if ($anticipo) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '2.1.1.05.2.01'), "debe" => $anticipo, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $_POST[ven_int_id]
                );
            }
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '5.1.1.01.1.01'), "debe" => $costo_lote, "haber" => 0,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0'
            );
            $tot_expensas = 0;
            if ($tot_expensas) {//
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.2.01.2.01'), "debe" => $tot_expensas, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $_POST[ven_int_id]
                );
            }
            if ($_POST[descuento]) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '5.1.1.01.1.21'), "debe" => $_POST[descuento], "haber" => 0,
                    "glosa" => $glosa, "ca" => '0', "cf" => FUNCIONES::get_cuenta_cf($ges_id, '2.02.017'), "cc" => '0'
                );
            }
            if ($monto_total + $_POST[descuento] > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '4.1.1.01.1.03'), "debe" => 0, "haber" => $monto_total + $_POST[descuento],
                    "glosa" => $glosa, "ca" => '0', "cf" => FUNCIONES::get_cuenta_cf($ges_id, '1.01.003'), "cc" => FUNCIONES::get_cuenta_cc($ges_id, '1.03')
                );
            }
            if ($interes) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '2.1.2.01.2.01'), "debe" => 0, "haber" => $interes,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $_POST[ven_int_id]
                );
            }

            if ($tot_expensas) {//
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '2.1.2.01.2.02'), "debe" => 0, "haber" => $tot_expensas,
                    "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0'
                );
            }
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.3.01.1.01'), "debe" => 0, "haber" => $costo_lote,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0'
            );

            if ($_POST[ven_tipo_pago] == 'Intercambio') {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.2.3.02.1.05'), "debe" => $monto_intercambio, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $_POST[ven_int_id]
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '4.1.1.01.1.06'), "debe" => 0, "haber" => $monto_intercambio,
                    "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0'
                );
            }
//            FUNCIONES::print_pre($comprobante);
            COMPROBANTES::registrar_comprobante($comprobante);
        }
        $ventas->siguiente();
    }
}

function cargar_pagos() {
    $ges_id = 13;
    $pagos = FUNCIONES::objetos_bd_sql("select * from interno_deuda where ind_estado='Pagado' and ind_tipo!='Intercambio'");
    echo 'pagos: '.$pagos->get_num_registros().'<br>';
    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $objeto = $pagos->get_objeto();
        $interno = FUNCIONES::atributo_bd("interno", "int_id=" . $objeto->ind_int_id, "concat(int_nombre,' ',int_apellido )");
        $fecha_cmp = $objeto->ind_fecha_pago;
        $ind_monto_pagado = $objeto->ind_monto_pagado;
        $ind_interes_pagado = $objeto->ind_interes_pagado;
        $capital_pagado = $objeto->ind_capital_pagado;
        
        if($objeto->ind_tipo=='pcontado'){
            $objeto->ind_concepto=  str_replace("Pago por Venta al Contado - ", "", $objeto->ind_concepto);
            $glosa = "Cobro de Cuota " . $objeto->ind_concepto; //"Recaudacion de Cobranza ".$fecha_comprobante." - ".$interno;
        }else{
            $glosa = "Cobro de " . $objeto->ind_concepto; //"Recaudacion de Cobranza ".$fecha_comprobante." - ".$interno;
        }        
//        $cc=  FUNCIONES::get_cuenta_cc($ges_id, "01.002");
        $comprobante = new stdClass();
        $comprobante->tipo = "Ingreso";
        $comprobante->mon_id = $objeto->ind_moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $interno;
        $comprobante->tabla = "interno_deuda";
        $comprobante->tabla_id = $objeto->ind_id;

        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.1.01.2.01'), "debe" => $ind_monto_pagado, "haber" => 0,
            'glosa' => $glosa, 'ca' => '0', 'cf' => FUNCIONES::get_cuenta_cc($ges_id, "1.01.003"), 'cc' => '0'
        );
        if ($ind_interes_pagado>0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '2.1.2.01.2.01'), "debe" => $ind_interes_pagado, "haber" => 0,
                "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $objeto->ind_int_id
            );
        }

        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.2.02.2.01'), "debe" => 0, "haber" => $capital_pagado,
            "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $objeto->ind_int_id
        );
        if ($ind_interes_pagado>0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '1.1.2.03.2.01'), "debe" => 0, "haber" => $ind_interes_pagado,
                "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, '01.00001'), "cf" => '0', "cc" => '0', 'int_id' => $objeto->ind_int_id
            );
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, '4.1.2.01.1.03'), "debe" => 0, "haber" => $ind_interes_pagado,
                "glosa" => $glosa, "ca" => '0', "cf" => FUNCIONES::get_cuenta_cf($ges_id, '1.02.001'), "cc" => FUNCIONES::get_cuenta_cc($ges_id, '1.03')
            );
        }
//        echo $objeto->ind_id.'<br>';
        COMPROBANTES::registrar_comprobante($comprobante);
        $pagos->siguiente();
//        if($i==10){
//            break;
//        }
        
    }
}

function cargar_comision(){
    include_once 'clases/registrar_comprobantes.class.php';
    $comisiones=  FUNCIONES::objetos_bd_sql("select * from comision where com_estado!='Anulado'");
    $ges_id=13;
    echo 'comisiones: '.$comisiones->get_num_registros().'<br>';
    for ($i = 0; $i < $comisiones->get_num_registros(); $i++) {
        $comision=$comisiones->get_objeto();
        
        $vdo_id=$comision->com_vdo_id;
        $monto_com=$comision->com_monto;
        $moneda=$comision->com_moneda;
        $fecha=$comision->com_fecha_cre;

        $llave = $comision->com_id;
        $glosa="Provision de comision a vendedor Interno por la venta Nro $comision->com_ven_id";
        $vendedor = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from vendedor, interno where vdo_int_id=int_id and vdo_id='$vdo_id'");
        
        
        $comprobante = new stdClass();
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha);
        $comprobante->forma_pago="Efectivo";
        $comprobante->ban_id=0;
        $comprobante->ban_char='';
        $comprobante->ban_nro='';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $vendedor;
        $comprobante->tabla = "comision";
        $comprobante->tabla_id = $llave;

        $comprobante->detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, '6.1.1.01.1.04'),"debe"=>$monto_com,"haber"=>0,
                        "glosa"=>$glosa,"ca"=> '0',"cf"=>'0',"cc"=>  FUNCIONES::get_cuenta_cc($ges_id, '1.03')
                );
        $vdo_can_id=  FUNCIONES::atributo_bd_sql("select vdo_can_id as campo from vendedor where vdo_id='$vdo_id'");
        $comprobante->detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, '2.1.1.01.2.02'),"debe"=>0,"haber"=>$monto_com,
                        "glosa"=>$glosa,"ca"=> FUNCIONES::get_cuenta_ca($ges_id, $vdo_can_id),"cf"=>'0',"cc"=>  '0'
                );

        COMPROBANTES::registrar_comprobante($comprobante);
        $comisiones->siguiente();
    }
}

function cargar_pagos_reserva(){
    include_once 'clases/registrar_comprobantes.class.php';        
    $reserva_pagos=  FUNCIONES::objetos_bd_sql("select * from reserva_pago where respag_estado='Pagado'");
    $ges_id=13;
    echo 'reserva_pagos: '.$reserva_pagos->get_num_registros().'<br>';
    for ($i = 0; $i < $reserva_pagos->get_num_registros(); $i++) {
        $pago=$reserva_pagos->get_objeto();
        $llave = $pago->respag_id;
        $fecha_cmp=  $pago->respag_fecha;
        $monto=$pago->respag_monto*1;
        $reserva=  FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id='$pago->respag_res_id'");
        $interno=FUNCIONES::atributo_bd("interno", "int_id='$reserva->res_int_id'", "concat(int_nombre,' ',int_apellido)");
        $int_id=$reserva->res_int_id;
        $glosa="Pago de Reserva: ".$pago->respag_glosa;

        $comprobante = new stdClass();
        $comprobante->tipo = "Ingreso";
        $comprobante->mon_id = $pago->respag_moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago='Efectivo';
        $comprobante->ban_id='';
        $comprobante->ban_char='';
        $comprobante->ban_nro='';
        $comprobante->glosa =$glosa;
        $comprobante->referido = $interno;
        $comprobante->tabla = "reserva_pago";
        $comprobante->tabla_id = $llave;       
        
        $caja=$pago->respag_moneda=='1'?'1.1.1.01.1.01':'1.1.1.01.2.01';
        $comprobante->detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, $caja),"debe"=>$monto,"haber"=>0,
                        'glosa'=>$glosa,'ca'=>'0','cf'=>FUNCIONES::get_cuenta_cf($ges_id, "1.01.001"),'cc'=>'0'
                );
        $ant_cli=$pago->respag_moneda=='1'?'2.1.1.05.1.01':'2.1.1.05.2.01';
        $comprobante->detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, $ant_cli),"debe"=>0,"haber"=>$monto,
                        "glosa"=>$glosa,"ca"=>  FUNCIONES::get_cuenta_ca($ges_id, '01.00001'),"cf"=> '0',"cc"=>'0','int_id'=>$int_id
                );
        
        COMPROBANTES::registrar_comprobante($comprobante);   
        $reserva_pagos->siguiente();
    }
}
?>
