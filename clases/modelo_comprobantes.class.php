<?php

class MODELO_COMPROBANTE {

    public static $comisiones_por_pagar = '2.1.2.06.2.01'; //'2.1.2.05.2.02';
    public static $egreso_por_comisiones = '6.2.1.01.2.02';
    public static $monto_por_pagar_bs = '2.1.1.01.1.02';
    public static $monto_por_pagar_sus = '2.1.1.01.2.02';
    public static $reserva_por_devolver = '2.3.2.01.2.12';
    public static $ingreso_por_reversion = "4.1.4.01.2.01";
    public static $gasto_por_reversion = "6.2.1.04.2.01";
    public static $cuenta_por_pagar_desestimiento = "2.1.1.01.2.06";
    public static $cuenta_por_pagar_intercambio = "2.1.1.01.2.02";
    public static $dif_cambio_diponibilidades = "4.2.1.01.1.01";
    public static $arr_cuentas_mlm = array(
        'BIR' => array('cue_pas' => '2.1.2.06.2.12', 'cue_gas' => '6.2.1.01.2.07'),
        'BVI' => array('cue_pas' => '2.1.2.06.2.13', 'cue_gas' => '6.2.1.01.2.08'),
        'BRA' => array('cue_pas' => '2.1.2.06.2.14', 'cue_gas' => '6.2.1.01.2.09'),
        'FED' => array('cue_pas' => '2.1.2.06.2.15', 'cue_gas' => '6.2.1.01.2.10'),
    );

    public static function brasil_cdivisa($data) {
        $data = (object) $data;

        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->cmp_id = $data->cmp_id;
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "con_brasil_cdivisa";
        $comprobante->tabla_id = $data->tabla_id;
        $comprobante->tcambios = $data->tcambios;
        $comprobante->detalles = $data->detalles;

        return $comprobante;
    }

    public static function brasil_traspaso($data) {
        $data = (object) $data;

        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->cmp_id = $data->cmp_id;
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "con_brasil_traspaso";
        $comprobante->tabla_id = $data->tabla_id;
        $comprobante->tcambios = $data->tcambios;
        $comprobante->detalles = $data->detalles;

        return $comprobante;
    }

    public static function traspaso($data) {
        $data = (object) $data;

        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->cmp_id = $data->cmp_id;
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "con_traspaso";
        $comprobante->tabla_id = $data->tabla_id;

        $comprobante->detalles = $data->detalles;

        return $comprobante;
    }

    public static function compra($data) {
        $data = (object) $data;

        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->cmp_id = $data->cmp_id;
        $comprobante->tipo = "Egreso";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "con_compra";
        $comprobante->tabla_id = $data->tabla_id;

        $comprobante->detalles = $data->detalles;

        return $comprobante;
    }

    public static function anticipo($data) {
        $data = (object) $data;
        $urb = $data->urb;
        $une_id = $urb->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->tipo = "Ingreso";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "reserva_pago";
        $comprobante->tabla_id = $data->tabla_id;

        $comprobante->detalles = $data->detalles;

        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb->urb_reserva), "debe" => 0, "haber" => $data->monto,
            "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0', 'une_id' => $une_id
        );
        return $comprobante;
    }

    public static function anticipo_producto($data) {
        $data = (object) $data;
        $urb = $data->urb;
        $une_id = $urb->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->tipo = "Ingreso";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "reserva_pago_producto";
        $comprobante->tabla_id = $data->tabla_id;

        $comprobante->detalles = $data->detalles;

        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb->urb_cue_reserva_producto), "debe" => 0, "haber" => $data->monto,
            "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0', 'une_id' => $une_id
        );
        return $comprobante;
    }

    public static function reserva_cambio_lote($data) {
        $data = (object) $data;
        $urb_ori = $data->urb_ori;
        $urb_des = $data->urb_des;
        $une_ori_id = $urb_ori->urb_une_id;
        $une_des_id = $urb_des->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->tipo = "Ingreso";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "reserva_cambio_lote";
        $comprobante->tabla_id = $data->tabla_id;

        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb_ori->urb_reserva), "debe" => $data->anticipo, "haber" => 0,
            "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0', 'une_id' => $une_ori_id
        );
        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb_des->urb_reserva), "debe" => 0, "haber" => $data->anticipo,
            "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0', 'une_id' => $une_des_id
        );
        return $comprobante;
    }

    public static function pago_vendedor($data) {
        $data = (object) $data;

        $ges_id = $data->ges_id;
        $glosa = $data->glosa;

        $comprobante = new stdClass();
        $comprobante->une_id = $data->urb->urb_une_id;
        $comprobante->tipo = "Egreso";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = "Efectivo";
        $comprobante->ban_id = 0;
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "pago_vendedores";
        $comprobante->tabla_id = $data->tabla_id;

        $comisiones_por_pagar = MODELO_COMPROBANTE::$comisiones_por_pagar;
        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $comisiones_por_pagar), "debe" => $data->monto, "haber" => 0,
            "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $data->vdo_can_codigo), "cf" => '0', "cc" => '0'
        );
        FUNCIONES::add_elementos($comprobante->detalles, $data->detalles);
        return $comprobante;
    }

    public static function pago_bonos($data) {
        $data = (object) $data;

        $ges_id = $data->ges_id;
        $glosa = $data->glosa;

        $comprobante = new stdClass();
        $comprobante->une_id = $data->urb->urb_une_id;
        $comprobante->tipo = "Egreso";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = "Efectivo";
        $comprobante->ban_id = 0;
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "pago_vendedores";
        $comprobante->tabla_id = $data->tabla_id;

//        $comisiones_por_pagar = MODELO_COMPROBANTE::$comisiones_por_pagar;
//        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $comisiones_por_pagar), "debe" => $data->monto, "haber" => 0,
//            "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $data->vdo_can_codigo), "cf" => '0', "cc" => '0'
//        );


        FUNCIONES::add_elementos($comprobante->detalles, $data->detalles2);
        FUNCIONES::add_elementos($comprobante->detalles, $data->detalles);
        return $comprobante;
    }

    public static function pago_cuota_ext($data) {
        $data = (object) $data;
        $interno = $data->interno;

        $glosa = $data->glosa;
        $ges_id = $data->ges_id;
        $urb = $data->urb;

        $cta_por_pagar = $urb->urb_cta_por_pagar;

        $comprobante = new stdClass();

        $comprobante->tipo = "Ingreso";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $interno;
        $comprobante->tabla = "venta_pago";
        $comprobante->tabla_id = $data->tabla_id;

        $interes = $data->interes;
        $capital = $data->capital;
        $form = $data->form;
        $envio = $data->envio;
        $mora = $data->mora;

        $monto = $interes + $capital + $form + $envio + $mora;
        $comprobante->detalles = $data->detalles;

        if ($monto > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_por_pagar), "debe" => 0, "haber" => $monto,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            );
        }
//        echo "<pre>";
//        print_r($comprobante);
//        echo "</pre>";
        return $comprobante;
    }

    public static function pago_cuota($data) {
        $data = (object) $data;
        $interno = $data->interno;


        $glosa = $data->glosa;
        $ges_id = $data->ges_id;
        $urb = $data->urb;

        $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');

        $cue_costo = $urb->urb_costo;
        $cue_costo_diferido = $urb->urb_costo_diferido;

        $cue_ing_diferido = $urb->urb_ing_diferido;

        $cue_costo_producto = $urb->urb_cue_costo_producto;
        $cue_costo_diferido_producto = $urb->urb_cue_costo_diferido_producto;

        $cue_ing_diferido_producto = $urb->urb_cue_ing_diferido_producto;

        $cue_cta_por_cobrar = $urb->{"urb_cta_por_cobrar$mon"};
        $cue_por_cobrar_producto = $urb->{"urb_cue_por_cobrar_producto"};
        $cue_ing_por_venta = $urb->{"urb_ing_por_venta$mon"};
        $cue_ing_producto = $urb->{"urb_cue_ing_producto"};
        $cue_ing_por_interes = $urb->{"urb_ing_por_interes$mon"};
        $cue_ing_por_form = $urb->{"urb_ing_por_form$mon"};
        $cue_ing_por_mora = $urb->{"urb_ing_por_mora$mon"};
        $cue_ing_por_envio = '4.1.2.02.2.01';

        $comprobante = new stdClass();

        $comprobante->tipo = "Ingreso";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $interno;
        $comprobante->tabla = "venta_pago";
        $comprobante->tabla_id = $data->tabla_id;

//            'moneda'=>$venta->ven_moneda,
//            'ges_id'=>$_SESSION[ges_id],
//            'fecha'=>$fecha_pago,
//            'glosa'=>$glosa,
//            'interno'=>$referido,
//            'tabla_id'=>$pago_id,
//            'urb'=>$urb,
//            'interes'=>$cob->vcob_interes,
//            'capital'=>$cob->vcob_capital,
//            'form'=>$cob->vcob_form,
//            'mora'=>$cob->vcob_mora,
//            'detalles'=>$detalles,

        $interes = $data->interes;
        $capital = $data->capital;
        $form = $data->form;
        $envio = $data->envio;
        $mora = $data->mora;
        $costo = $data->costo;
        $costo_producto = $data->costo_producto;

        $prorat_lote = $data->prorat_lote;
        $prorat_producto = $data->prorat_producto;

//        $anticipo_prorateado = ($data->anticipo/2) * $prorat_producto;
//        $anticipo_prorateado = ($data->anticipo/2) * $prorat_lote;

        $facturar = FUNCIONES::ad_parametro('par_facturar');

        $ven_can_codigo = $data->ven_can_codigo;

        if ($facturar) {
            $cue_gasto_it = FUNCIONES::parametro('it', $ges_id);
            $cue_it_xpagar = FUNCIONES::parametro('itpagar', $ges_id);
            $cue_debito_fiscal = FUNCIONES::parametro('deb_fiscal', $ges_id);
            $porc_it = FUNCIONES::parametro('val_it', $ges_id);
            $porc_iva = FUNCIONES::parametro('val_iva', $ges_id);
        }
        $comprobante->detalles = $data->detalles;
        if ($urb->urb_tipo == 'Interno') {
            if ($capital > 0) {

                if ($prorat_lote > 0) {

                    $cap_lote_pror = $capital * $prorat_lote;
                    $capital_lote = $cap_lote_pror;
                    //                $capital_lote = ($cap_lote_pror > $anticipo_prorateado)? ($cap_lote_pror - $anticipo_prorateado):0;
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar), "debe" => 0, "haber" => $capital_lote,
                        "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => $capital_lote, "haber" => 0,
                        "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta), "debe" => 0, "haber" => $capital_lote,
                        "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }

                if ($prorat_producto > 0) {

                    $cap_prod_pror = $capital * $prorat_producto;
//                    $capital_producto = $cap_prod_pror + $anticipo_prorateado;
//                    $capital_producto = $capital - $capital_lote;
                    $capital_producto = $cap_prod_pror;
                    //  PARA PRODUCTO
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_por_cobrar_producto), "debe" => 0, "haber" => $capital_producto,
                        "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido_producto), "debe" => $capital_producto, "haber" => 0,
                        "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_producto), "debe" => 0, "haber" => $capital_producto,
                        "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                    //  PARA PRODUCTO
                }
            } elseif ($capital < 0) {
                $capital = $capital * (-1);
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar), "debe" => $capital * $prorat_lote, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => 0, "haber" => $capital * $prorat_lote,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta), "debe" => $capital * $prorat_lote, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );


                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar), "debe" => $capital * $prorat_producto, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => 0, "haber" => $capital * $prorat_producto,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta), "debe" => $capital * $prorat_producto, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }

            if ($interes > 0) {
                $iva_interes = 0;
                $it_interes = 0;
                if ($facturar) {
                    $iva_interes = round($interes * $porc_iva / 100, 2);
                    $it_interes = round($interes * $porc_it / 100, 2);
                    $interes = $interes - $iva_interes;
                }
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_interes), "debe" => 0, "haber" => $interes,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                if ($it_interes > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_gasto_it), "debe" => $it_interes, "haber" => 0,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_it_xpagar), "debe" => 0, "haber" => $it_interes,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
                if ($iva_interes > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_debito_fiscal), "debe" => 0, "haber" => $iva_interes,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
            }
            if ($form > 0) {
                $iva_form = 0;
                $it_form = 0;
                if ($facturar) {
                    $iva_form = round($form * $porc_iva / 100, 2);
                    $it_form = round($form * $porc_it / 100, 2);
                    $form = $form - $iva_form;
                }
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_form), "debe" => 0, "haber" => $form,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                if ($it_form > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_gasto_it), "debe" => $it_form, "haber" => 0,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_it_xpagar), "debe" => 0, "haber" => $it_form,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
                if ($iva_form > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_debito_fiscal), "debe" => 0, "haber" => $iva_form,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
            }
            if ($envio > 0) {

                $iva_envio = 0;
                $it_envio = 0;
                if ($facturar) {
                    $iva_envio = round($envio * $porc_iva / 100, 2);
                    $it_envio = round($envio * $porc_it / 100, 2);
                    $envio = $envio - $iva_envio;
                }
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_envio), "debe" => 0, "haber" => $envio,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                if ($it_envio > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_gasto_it), "debe" => $it_envio, "haber" => 0,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_it_xpagar), "debe" => 0, "haber" => $it_envio,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
                if ($iva_envio > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_debito_fiscal), "debe" => 0, "haber" => $iva_envio,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
            }
            if ($mora > 0) {
                $iva_mora = 0;
                $it_mora = 0;
                if ($facturar) {
                    $iva_mora = round($mora * $porc_iva / 100, 2);
                    $it_mora = round($mora * $porc_it / 100, 2);
                    $mora = $mora - $iva_mora;
                }
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_mora), "debe" => 0, "haber" => $mora,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                if ($it_mora > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_gasto_it), "debe" => $it_mora, "haber" => 0,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_it_xpagar), "debe" => 0, "haber" => $it_mora,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
                if ($iva_mora > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_debito_fiscal), "debe" => 0, "haber" => $iva_mora,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
            }


            if ($costo > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo), "debe" => $costo, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido), "debe" => 0, "haber" => $costo,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }

            if ($costo_producto > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_producto), "debe" => $costo_producto, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido_producto), "debe" => 0, "haber" => $costo_producto,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }

            if ($data->capital_producto > 0) {
                //  PARA PRODUCTO
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_por_cobrar_producto), "debe" => 0, "haber" => $data->capital_producto,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido_producto), "debe" => $data->capital_producto, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_producto), "debe" => 0, "haber" => $data->capital_producto,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                //  PARA PRODUCTO
            }

            if ($data->costo_producto_separado > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_producto), "debe" => $data->costo_producto_separado, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido_producto), "debe" => 0, "haber" => $data->costo_producto_separado,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
        }

        /*
          elseif ($urb->urb_tipo == 'Externo') {
          $monto = $data->monto;
          $cta_por_pagar = $urb->urb_cta_por_pagar;
          if ($monto > 0) {
          $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_por_pagar), "debe" => 0, "haber" => $monto,
          "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
          );
          }
          }
         */
//        echo "<pre>";
//        print_r($comprobante);
//        echo "</pre>";
        return $comprobante;
    }

    public static function comision($data) {
        $data = (object) $data;
        $urb = $data->urb;
        $glosa = $data->glosa;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = "Efectivo";
        $comprobante->ban_id = 0;
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "comision";
        $comprobante->tabla_id = $data->tabla_id;

        $comisiones_por_pagar = MODELO_COMPROBANTE::$comisiones_por_pagar;
        $egreso_por_comisiones = MODELO_COMPROBANTE::$egreso_por_comisiones;

        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $egreso_por_comisiones), "debe" => $data->monto, "haber" => 0,
            "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
        );
        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $comisiones_por_pagar), "debe" => 0, "haber" => $data->monto,
            "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => '0', 'une_id' => $urb->urb_une_id
        );
        return $comprobante;
    }

    public static function comision_multinivel($data) {
        $data = (object) $data;
        $urb = $data->urb;
        $glosa = $data->glosa;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = "Efectivo";
        $comprobante->ban_id = 0;
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "comision";
        $comprobante->tabla_id = $data->tabla_id;

        $cuentas = (object) MODELO_COMPROBANTE::$arr_cuentas_mlm[$data->tipo_bono];

        $comisiones_por_pagar = $cuentas->cue_pas;
        $egreso_por_comisiones = $cuentas->cue_gas;

        $comprobante->detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $egreso_por_comisiones),
            "debe" => $data->monto,
            "haber" => 0,
            "glosa" => $glosa,
            "ca" => '0',
            "cf" => '0',
            "cc" => 0,
            'une_id' => $urb->urb_une_id
        );
        $comprobante->detalles[] = array(
            "cuen" => FUNCIONES::get_cuenta($ges_id, $comisiones_por_pagar),
            "debe" => 0,
            "haber" => $data->monto,
            "glosa" => $glosa,
            "ca" => 0,
            "cf" => '0',
            "cc" => '0',
            'une_id' => $urb->urb_une_id
        );
        return $comprobante;
    }

    public static function venta($data) {
        $data = (object) $data;
        $urb = $data->urb;
        $glosa = $data->glosa;
        $ges_id = $data->ges_id;

        ///cuentas
        $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
        $cue_costo = $urb->urb_costo;
        $cue_costo_diferido = $urb->urb_costo_diferido;
        $cue_inv_terrenos = ($data->orden == 1) ? $urb->urb_inv_terrenos : $urb->urb_inv_terrenos_adj;
        $cue_ing_diferido = $urb->urb_ing_diferido;
        $cue_reserva = $urb->urb_reserva;

        $cue_costo_producto = $urb->urb_cue_costo_producto;
        $cue_costo_diferido_producto = $urb->urb_cue_costo_diferido_producto;
        $cue_inv_producto = $urb->urb_cue_inv_producto;
        $cue_cta_por_cobrar_producto = $urb->urb_cue_por_cobrar_producto;
        $cue_ing_diferido_producto = $urb->urb_cue_ing_diferido_producto;

        $cue_cta_por_cobrar = $urb->{"urb_cta_por_cobrar$mon"};
        $cue_ing_por_venta = $urb->{"urb_ing_por_venta$mon"};
        $cue_ing_producto = $urb->urb_cue_ing_producto;

        $codigo_cue_descuento = $urb->{"urb_cue_descuento_capital$mon"};

//        $cue_ing_por_interes="$urb->urb_ing_por_interes$mon";
//        $cue_ing_por_form="$urb->urb_ing_por_form$mon";
//        $cue_ing_por_mora="$urb->urb_ing_por_mora$mon";

        $moneda = $data->moneda;

        $comprobante = new stdClass();
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = "Efectivo";
        $comprobante->ban_id = 0;
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta";
        $comprobante->tabla_id = $data->tabla_id;

        $anticipo = $data->anticipo;
        $saldo_efectivo = $data->saldo_efectivo;

        $monto_intercambio = $data->monto_intercambio;
        $intercambio_ids = $data->intercambio_ids;
        $intercambio_montos = $data->intercambio_montos;
        $monto_pagar = $data->monto_pagar;

        $inventario = $data->costo;
        $costo = $data->costo_cub;
        $costo_diferito = $data->costo - $data->costo_cub;
        $monto_comision = $data->monto_comision;

        $monto_producto = $data->monto_producto;
        $inventario_producto = $data->costo_producto;
        $costo_producto_cubierto = $data->costo_producto_cub;
        $costo_diferido_producto = $inventario_producto - $costo_producto_cubierto;

        $prorat_lote = $data->prorat_lote;
        $prorat_producto = $data->prorat_producto;

        $ven_can_codigo = $data->ven_can_codigo;
        $descuento = $data->descuento;


        if ($anticipo > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_reserva), "debe" => $anticipo, "haber" => 0,
                "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            );
        }
        if ($saldo_efectivo > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar), "debe" => $saldo_efectivo, "haber" => 0,
                "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            );
        }

        if ($descuento > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_cue_descuento), "debe" => $descuento, "haber" => 0,
                "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            );
        }

        if ($inventario > 0) {
            if ($costo > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo), "debe" => $costo, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            if ($costo_diferito > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido), "debe" => $costo_diferito, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }

            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_terrenos), "debe" => 0, "haber" => $inventario,
                "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            );
        }

        if ($monto_intercambio > 0) {
            for ($i = 0; $i < count($intercambio_ids); $i++) {
                $inter_id = $intercambio_ids[$i];
                $inter_monto = $intercambio_montos[$i];
                if ($inter_monto > 0) {
                    $obj_inter = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id='$inter_id'");
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $obj_inter->{"inter_cuenta_debe$mon"}), "debe" => $inter_monto, "haber" => 0,
                        "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
            }
        }
        if ($monto_pagar > 0) {
            $monto_por_pagar = $moneda == 1 ? MODELO_COMPROBANTE::$monto_por_pagar_bs : MODELO_COMPROBANTE::$monto_por_pagar_sus;

            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, "$monto_por_pagar"), "debe" => 0, "haber" => $monto_pagar,
                "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            );
        }

        if ($urb->urb_tipo == 'Interno') {
            $monto_ing_venta = ($anticipo + $monto_intercambio + $descuento) - $monto_pagar;
            if ($monto_ing_venta > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta), "debe" => 0, "haber" => $monto_ing_venta,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );

//                if ($prorat_producto > 0) {
//                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_producto), "debe" => 0, "haber" => $monto_ing_venta * $prorat_producto,
//                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
//                    );
//                }
            }
            if ($saldo_efectivo > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => 0, "haber" => $saldo_efectivo,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }



            if ($monto_producto > 0) {

                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar_producto), "debe" => $monto_producto, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );

                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido_producto), "debe" => 0, "haber" => $monto_producto,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );

                if ($inventario_producto > 0) {
                    if ($costo_producto_cubierto > 0) {
                        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_producto), "debe" => $costo_producto_cubierto, "haber" => 0,
                            "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                        );
                    }
                    if ($costo_diferido_producto > 0) {
                        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido_producto), "debe" => $costo_diferido_producto, "haber" => 0,
                            "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                        );
                    }

                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_producto), "debe" => 0, "haber" => $inventario_producto,
                        "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
            }
        } elseif ($urb->urb_tipo == 'interno2') {
            if ($inventario > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_terrenos), "debe" => 0, "haber" => $inventario,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            $monto_ingreso = $anticipo + $monto_intercambio - $monto_comision;
            if ($monto_ingreso > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta), "debe" => 0, "haber" => $monto_ingreso,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            if ($monto_comision > 0) {
                $comisiones_por_pagar = MODELO_COMPROBANTE::$comisiones_por_pagar;
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $comisiones_por_pagar), "debe" => 0, "haber" => $monto_comision,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            $monto_regulador = $anticipo + $monto_intercambio + $saldo_efectivo - $inventario - $monto_ingreso - $monto_comision;
            if ($monto_regulador > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => 0, "haber" => $monto_regulador,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
//            echo "$inventario-$monto_ingreso-$monto_comision-$monto_regulador";
        }
        return $comprobante;
    }

    public static function venta_producto($data) {

        $data = (object) $data;
        $urb = $data->urb;
        $glosa = $data->glosa;
        $ges_id = $data->ges_id;

        ///cuentas
        $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
        $cue_costo = $urb->urb_cue_costo_producto;
        $cue_costo_diferido = $urb->urb_cue_costo_diferido_producto;
        $cue_inv_terrenos = $urb->urb_cue_inv_producto;
        $cue_ing_diferido = $urb->urb_cue_ing_diferido_producto;
        $cue_reserva = $urb->urb_cue_reserva_producto;

        $cue_costo_producto = $urb->urb_cue_costo_producto;
        $cue_costo_diferido_producto = $urb->urb_cue_costo_diferido_producto;
        $cue_inv_producto = $urb->urb_cue_inv_producto;
        $cue_cta_por_cobrar_producto = $urb->urb_cue_por_cobrar_producto;
        $cue_ing_diferido_producto = $urb->urb_cue_ing_diferido_producto;

        $cue_cta_por_cobrar = $urb->urb_cue_por_cobrar_producto;
        $cue_ing_por_venta = $urb->urb_cue_ing_producto;


        $moneda = $data->moneda;

        $comprobante = new stdClass();
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = "Efectivo";
        $comprobante->ban_id = 0;
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_producto";
        $comprobante->tabla_id = $data->tabla_id;

        $anticipo = $data->anticipo;
        $saldo_efectivo = $data->saldo_efectivo;

        $monto_intercambio = $data->monto_intercambio;
        $intercambio_ids = $data->intercambio_ids;
        $intercambio_montos = $data->intercambio_montos;
        $monto_pagar = $data->monto_pagar;

        $inventario = $data->costo;
        $costo_cubierto = $data->costo_cub;
        $costo_diferido = $inventario - $costo_cubierto;
        $monto_comision = $data->monto_comision;

//        $monto_producto = $data->monto_producto;
//        $inventario_producto = $data->costo_producto;
//        $costo_producto_cubierto = $data->costo_producto_cub;
//        $costo_diferido_producto = $inventario_producto - $costo_producto_cubierto;
//        $prorat_lote = $data->prorat_lote;
//        $prorat_producto = $data->prorat_producto;


        if ($anticipo > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_reserva), "debe" => $anticipo, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            );
        }
        if ($saldo_efectivo > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar), "debe" => $saldo_efectivo, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            );
        }
//        if ($monto_intercambio > 0) {
//            for ($i = 0; $i < count($intercambio_ids); $i++) {
//                $inter_id = $intercambio_ids[$i];
//                $inter_monto = $intercambio_montos[$i];
//                if ($inter_monto > 0) {
//                    $obj_inter = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id='$inter_id'");
//                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $obj_inter->{"inter_cuenta_debe$mon"}), "debe" => $inter_monto, "haber" => 0,
//                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
//                    );
//                }
//            }
//        }
//        if ($monto_pagar > 0) {
//            $monto_por_pagar = $moneda == 1 ? MODELO_COMPROBANTE::$monto_por_pagar_bs : MODELO_COMPROBANTE::$monto_por_pagar_sus;
//
//            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, "$monto_por_pagar"), "debe" => 0, "haber" => $monto_pagar,
//                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
//            );
//        }

        if ($urb->urb_tipo == 'Interno') {
            $monto_ing_venta = $anticipo + $monto_intercambio - $monto_pagar;
            if ($monto_ing_venta > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta), "debe" => 0, "haber" => $monto_ing_venta,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            if ($saldo_efectivo > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => 0, "haber" => $saldo_efectivo,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }

            if ($inventario > 0) {
                if ($costo_cubierto > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo), "debe" => $costo_cubierto, "haber" => 0,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }
                if ($costo_diferido > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido), "debe" => $costo_diferido, "haber" => 0,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                    );
                }

                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_producto), "debe" => 0, "haber" => $inventario,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }

//            if ($monto_producto > 0) {
//                
//                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar_producto), "debe" => $monto_producto, "haber" => 0,
//                            "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
//                        );
//                
//                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido_producto), "debe" => 0, "haber" => $monto_producto,
//                            "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
//                        );
//                
//                if ($inventario_producto > 0) {
//                    if ($costo_producto_cubierto > 0) {
//                        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_producto), "debe" => $costo_producto_cubierto, "haber" => 0,
//                            "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
//                        );
//                    }
//                    if ($costo_diferido_producto > 0) {
//                        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido_producto), "debe" => $costo_diferido_producto, "haber" => 0,
//                            "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
//                        );
//                    }
//
//                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_producto), "debe" => 0, "haber" => $inventario_producto,
//                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
//                    );
//                }
//            }
        } elseif ($urb->urb_tipo == 'interno2') {
            if ($inventario > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_terrenos), "debe" => 0, "haber" => $inventario,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            $monto_ingreso = $anticipo + $monto_intercambio - $monto_comision;
            if ($monto_ingreso > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta), "debe" => 0, "haber" => $monto_ingreso,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            if ($monto_comision > 0) {
                $comisiones_por_pagar = MODELO_COMPROBANTE::$comisiones_por_pagar;
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $comisiones_por_pagar), "debe" => 0, "haber" => $monto_comision,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            $monto_regulador = $anticipo + $monto_intercambio + $saldo_efectivo - $inventario - $monto_ingreso - $monto_comision;
            if ($monto_regulador > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => 0, "haber" => $monto_regulador,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
//            echo "$inventario-$monto_ingreso-$monto_comision-$monto_regulador";
        }
        return $comprobante;
    }

    public static function anular_reserva($data) {

        $data = (object) $data;
        $urb = $data->urb;
        $une_id = $urb->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "reserva_anulacion";
        $comprobante->tabla_id = $data->tabla_id;

        $reserva_por_devolver = MODELO_COMPROBANTE::$reserva_por_devolver;
        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb->urb_reserva), "debe" => $data->monto, "haber" => 0,
            "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0', 'une_id' => $une_id
        );
        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $reserva_por_devolver), "debe" => 0, "haber" => $data->monto,
            "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0', 'une_id' => $une_id
        );
        return $comprobante;
    }

    public static function devolucion_reserva($data) {

        $data = (object) $data;
        $urb = $data->urb;
        $une_id = $urb->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->tipo = $data->dev_monto > 0 ? 'Egreso' : 'Diario';
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "reserva_devolucion";
        $comprobante->tabla_id = $data->tabla_id;

        $reserva_por_devolver = MODELO_COMPROBANTE::$reserva_por_devolver;
        if ($data->monto_pagado > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $reserva_por_devolver), "debe" => $data->monto_pagado, "haber" => 0,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0', 'une_id' => $une_id
            );
        }
        if ($data->dev_ingreso > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb->urb_ing_reserva), "debe" => 0, "haber" => $data->dev_ingreso,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0', 'une_id' => $une_id
            );
        }
        if ($data->dev_monto > 0) {
            FUNCIONES::add_elementos($comprobante->detalles, $data->detalles);
        }
        return $comprobante;
    }

    public static function extra_pago($data) {

        $data = (object) $data;
        $urb = $data->urb;
        $une_id = $urb->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;

        $comprobante = new stdClass();
        $comprobante->tipo = 'Ingreso';
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "extra_pago";
        $comprobante->tabla_id = $data->tabla_id;


        $facturar = $data->tipo->ept_facturado * 1;
        if ($facturar) {
            $cue_gasto_it = FUNCIONES::parametro('it', $ges_id);
            $cue_it_xpagar = FUNCIONES::parametro('itpagar', $ges_id);
            $cue_debito_fiscal = FUNCIONES::parametro('deb_fiscal', $ges_id);
            $porc_it = FUNCIONES::parametro('val_it', $ges_id);
            $porc_iva = FUNCIONES::parametro('val_iva', $ges_id);
        }


        $comprobante->detalles = $data->detalles;
//        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $data->tipo->tep_cuenta), "debe" => 0, "haber" => $monto,
//                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0','une_id'=>$une_id
//            );
        $monto = $data->monto;
        $iva_monto = 0;
        $it_monto = 0;
        if ($facturar) {
            $iva_monto = round($monto * $porc_iva / 100, 2);
            $it_monto = round($monto * $porc_it / 100, 2);
            $monto = $monto - $iva_monto;
        }
        $tipo = $data->tipo;
        $cue_ingreso = $tipo->ept_cuenta;
        // if ($tipo->ept_llave != '' && $urb->urb_tipo == 'Interno') {
        // $cue_ingreso = $urb->{'urb_' . $tipo->ept_llave};
        // }
        if ($urb->urb_ing_cambio_tit != null) {
            $cue_ingreso = $urb->urb_ing_cambio_tit;
        }

        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ingreso), "debe" => 0, "haber" => $monto,
            "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
        );
        if ($it_monto > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_gasto_it), "debe" => $it_monto, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_it_xpagar), "debe" => 0, "haber" => $it_monto,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
        }
        if ($iva_monto > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_debito_fiscal), "debe" => 0, "haber" => $iva_monto,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
        }
        return $comprobante;
    }

    public static function venta_retencion($data) {
        $data = (object) $data;
        $urb = $data->urb;
        $une_id = $urb->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;

        $comprobante = new stdClass();
        $comprobante->tipo = 'Diario';
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_retencion";
        $comprobante->tabla_id = $data->tabla_id;
        $comprobante->tarea_id = $data->tarea_id;

        $costo = $data->costo;
        $costo_pagado = $data->costo_pagado;
        $saldo_efectivo = $data->saldo_efectivo;
        $total_pagado_efectivo = $data->total_pagado;

        //$ing_reversion = "4.1.4.01.2.01";
        //$gast_xreversion = "6.2.1.04.2.01";
        //$cta_xpagar_desestimiento = "2.1.1.01.2.06";
        //$cta_xpagar_intercambio = "2.1.1.01.2.02";
        // $ing_reversion = MODELO_COMPROBANTE::$ingreso_por_reversion; //"4.1.4.01.2.01";

        $ing_reversion = ($data->moneda == 1) ? $urb->urb_ing_por_reversion_bs : $urb->urb_ing_por_reversion_usd;

        $gast_xreversion = MODELO_COMPROBANTE::$gasto_por_reversion; //"6.2.1.04.2.01";
        $cta_xpagar_desestimiento = MODELO_COMPROBANTE::$cuenta_por_pagar_desestimiento; //"2.1.1.01.2.06";
        $cta_xpagar_intercambio = MODELO_COMPROBANTE::$cuenta_por_pagar_intercambio; //"2.1.1.01.2.02";

        $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');

        $cue_costo_diferido = $urb->urb_costo_diferido;
        $cue_inv_terrenos = $urb->urb_inv_terrenos;
        $cue_inv_diferido_adj = $urb->urb_inv_terrenos_adj;
        $cue_ing_diferido = $urb->urb_ing_diferido;

        $cue_cta_por_cobrar = $urb->{"urb_cta_por_cobrar$mon"};



        if ($costo > 0) {
            if ($costo_pagado > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $ing_reversion), "debe" => 0, "haber" => $costo_pagado,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
                );
            }
            $costo_diferido = $costo - $costo_pagado;
            if ($costo_diferido > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido), "debe" => 0, "haber" => $costo_diferido,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $data->ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $une_id
                );
            }

            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_diferido_adj), "debe" => $costo, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
        }

        if ($saldo_efectivo > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => $saldo_efectivo, "haber" => 0,
                "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $data->ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar), "debe" => 0, "haber" => $saldo_efectivo,
                "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $data->ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
        }

//        'intercambio'=>$monto_intercambio,
//        'inter_montos'=>$amontos,
//        'inter_montos_pag'=>$amontos_pag,
        $intercambio = $data->intercambio;
        $inter_montos = $data->inter_montos;
        $inter_montos_pag = $data->inter_montos_pag;
        $sum_inter_pag = 0;
        foreach ($inter_montos_pag as $ipag) {
            $sum_inter_pag += $ipag->monto;
        }

        $total_pagado = $total_pagado_efectivo + $intercambio;
        if ($total_pagado > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $gast_xreversion), "debe" => $total_pagado, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
            $pagar_desestimiento = $total_pagado_efectivo + $sum_inter_pag;
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_xpagar_desestimiento), "debe" => 0, "haber" => $pagar_desestimiento,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
            for ($j = 0; $j < count($inter_montos); $j++) {
                $iprov = $inter_montos[$j];
                $_sum_pag = 0;
                for ($k = 0; $k < count($inter_montos_pag); $k++) {
                    $ipag = $inter_montos_pag[$k];
                    if ($ipag->inter_id . '' == $iprov->inter_id . '') {
                        $_sum_pag = $ipag->monto;
                        break;
                    }
                }
                $dif_pag = $iprov->monto - $_sum_pag;
                if ($dif_pag > 0) {
                    $motivo = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id='$iprov->inter_id'");
                    $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
                    $inter_cuenta_debe = $motivo->{"inter_cuenta_debe$mon"};
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $inter_cuenta_debe), "debe" => 0, "haber" => $dif_pag,
                        "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $data->ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $une_id
                    );
                }
            }
        }
        /// devolucion por cada intercambio

        return $comprobante;
    }

    public static function venta_retencion_ext($data) {
        $data = (object) $data;
        $urb = $data->urb;
        $une_id = $urb->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;

        $comprobante = new stdClass();
        $comprobante->tipo = 'Diario';
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_retencion";
        $comprobante->tabla_id = $data->tabla_id;
        $comprobante->tarea_id = $data->tarea_id;

        $costo = $data->costo;
        $costo_pagado = $data->costo_pagado;
        $saldo_efectivo = $data->saldo_efectivo;
        $total_pagado_efectivo = $data->total_pagado;

        //$ing_reversion = "4.1.4.01.2.01";
        //$gast_xreversion = "6.2.1.04.2.01";
        //$cta_xpagar_desestimiento = "2.1.1.01.2.06";
        //$cta_xpagar_intercambio = "2.1.1.01.2.02";

        $ing_reversion = MODELO_COMPROBANTE::$ingreso_por_reversion; //"4.1.4.01.2.01";
        $gast_xreversion = MODELO_COMPROBANTE::$gasto_por_reversion; //"6.2.1.04.2.01";
        $cta_xpagar = $urb->urb_cta_por_pagar;
        $cta_xpagar_desestimiento = $urb->urb_cue_desistimiento; //"2.1.1.01.2.06";
        $cta_xpagar_intercambio = MODELO_COMPROBANTE::$cuenta_por_pagar_intercambio; //"2.1.1.01.2.02";

        $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');

        $cue_costo_diferido = $urb->urb_costo_diferido;
        $cue_inv_terrenos = $urb->urb_inv_terrenos;
        $cue_inv_diferido_adj = $urb->urb_inv_terrenos_adj;
        $cue_ing_diferido = $urb->urb_ing_diferido;

        $cue_cta_por_cobrar = $urb->{"urb_cta_por_cobrar$mon"};


//        'intercambio'=>$monto_intercambio,
//        'inter_montos'=>$amontos,
//        'inter_montos_pag'=>$amontos_pag,
        $intercambio = $data->intercambio;
        $inter_montos = $data->inter_montos;
        $inter_montos_pag = $data->inter_montos_pag;
        $sum_inter_pag = 0;
        foreach ($inter_montos_pag as $ipag) {
            $sum_inter_pag += $ipag->monto;
        }

        $total_pagado = $total_pagado_efectivo + $intercambio;
        if ($total_pagado > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_xpagar), "debe" => $total_pagado, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
            $pagar_desestimiento = $total_pagado_efectivo + $sum_inter_pag;
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_xpagar_desestimiento), "debe" => 0, "haber" => $pagar_desestimiento,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
            for ($j = 0; $j < count($inter_montos); $j++) {
                $iprov = $inter_montos[$j];
                $_sum_pag = 0;
                for ($k = 0; $k < count($inter_montos_pag); $k++) {
                    $ipag = $inter_montos_pag[$k];
                    if ($ipag->inter_id . '' == $iprov->inter_id . '') {
                        $_sum_pag = $ipag->monto;
                        break;
                    }
                }
                $dif_pag = $iprov->monto - $_sum_pag;
                if ($dif_pag > 0) {
                    $motivo = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id='$iprov->inter_id'");
                    $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
                    $inter_cuenta_debe = $motivo->{"inter_cuenta_debe$mon"};
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $inter_cuenta_debe), "debe" => 0, "haber" => $dif_pag,
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
                    );
                }
            }
        }
        /// devolucion por cada intercambio

        return $comprobante;
    }

    public static function venta_reactivacion($data) {
        $data = (object) $data;
        $urb = $data->urb;
        $une_id = $urb->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;

        $comprobante = new stdClass();
        $comprobante->tipo = 'Diario';
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_activacion";
        $comprobante->tabla_id = $data->tabla_id;
        $comprobante->tarea_id = $data->tarea_id;

        $rdetalles = $data->rdetalles;

        foreach ($rdetalles as $rdet) {
            $cuenta = FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_id='$rdet->cde_cue_id'");
            $monto = $rdet->cde_valor * (-1);
            $debe = 0;
            $haber = 0;
            if ($monto > 0) {
                $debe = $monto;
            } else {
                $haber = $monto * (-1);
            }

            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cuenta->cue_codigo), "debe" => $debe, "haber" => $haber,
                "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $data->ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $rdet->cde_une_id
            );
        }

        /// devolucion por cada intercambio

        return $comprobante;
    }

    public static function venta_fusion($data) {
        $data = (object) $data;
        $urb_ori = $data->urb_ori;
        $urb_des = $data->urb_des;

        $une_ori_id = $urb_ori->urb_une_id;
        $une_des_id = $urb_des->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;

        $comprobante = new stdClass();
        $comprobante->tipo = 'Diario';
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_fusion";
        $comprobante->tabla_id = $data->tabla_id;
        $comprobante->tarea_id = $data->tarea_id;


        /*         * **************************** */
//        'costo'=>$venta_ori->ven_costo,
//        'costo_pagado'=>$venta_ori->ven_costo_cub+$pagado_ori->costo,
//
//        'saldo_ori'=>$saldo_ori,
//        'tot_pagado_ori'=>$tot_pagado_ori,
//        'ret_ingreso'=>$ret_ingreso,
//        'capital_efectivo'=>$capital_pag,
//
//        'intercambio'=>$monto_intercambio,
//        'inter_montos'=>$amontos,
//        'inter_montos_pag'=>$amontos_pag,
//
//        'costo_pagado'=>$costo_pagado,
//        'monto_pagar'=>$monto_pagar

        /*         * **************************** */

        $can_codigo_ori = $data->ven_can_codigo_ori;
        $can_codigo_dest = $data->ven_can_codigo_dest;

        $costo = $data->costo;
        $costo_pagado = $data->costo_pagado_ori;

//        'costo'=>$venta_ori->ven_costo,
//        'costo_pagado'=>$venta_ori->ven_costo_cub+$pagado_ori->costo,
//
//        'saldo_ori'=>$saldo_ori,
//        'tot_pagado_ori'=>$tot_pagado_ori,
//        'ret_ingreso'=>$ret_ingreso,
//        'capital_efectivo'=>$capital_efectivo,        

        $mon_ori = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
        $mon_des = $data->moneda_des == '1' ? '_bs' : ($data->moneda_des == '2' ? '_usd' : '');

        $saldo_ori = $data->saldo_ori;

        // $ing_reversion = MODELO_COMPROBANTE::$ingreso_por_reversion; //"4.1.4.01.2.01";
        $ing_reversion = $urb_ori->{"urb_ing_por_reversion$mon_ori"};


        $gast_xreversion = MODELO_COMPROBANTE::$gasto_por_reversion; // "6.2.1.04.2.01";
        $cta_xpagar_desestimiento = MODELO_COMPROBANTE::$cuenta_por_pagar_desestimiento; // "2.1.1.01.2.06";


        $cue_costo_diferido = $urb_ori->urb_costo_diferido;
        $cue_inv_diferido_adj = $urb_ori->urb_inv_terrenos_adj;
//        $cue_ing_diferido = $urb->urb_ing_diferido;
        $cue_ing_diferido = $urb_ori->urb_ing_diferido;

        $cue_cta_por_cobrar_ori = $urb_ori->{"urb_cta_por_cobrar$mon_ori"};

        $cue_cta_por_cobrar_des = $urb_des->{"urb_cta_por_cobrar$mon_des"};

        $can_id_orig = FUNCIONES::get_cuenta_ca($ges_id, $can_codigo_ori);
        $can_id_dest = FUNCIONES::get_cuenta_ca($ges_id, $can_codigo_dest);

        if ($costo > 0) {
            if ($costo_pagado > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $ing_reversion), "debe" => 0, "haber" => $costo_pagado,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
            }
            $costo_diferido = $costo - $costo_pagado;
            if ($costo_diferido > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido), "debe" => 0, "haber" => $costo_diferido,
                    "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
            }

            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_diferido_adj), "debe" => $costo, "haber" => 0,
                "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
            );
        }

        if ($saldo_ori > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => $saldo_ori, "haber" => 0,
                "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
            );
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar_ori), "debe" => 0, "haber" => $saldo_ori,
                "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
            );
        }


        $intercambio = $data->intercambio;
        $inter_montos = $data->inter_montos;
        $inter_montos_pag = $data->inter_montos_pag;
        $sum_inter_pag = 0;
        foreach ($inter_montos_pag as $ipag) {
            $sum_inter_pag += $ipag->monto;
        }



        $tot_pagado_ori = $data->tot_pagado_ori; /// todo lo que pago
        $ret_ingreso = $data->ret_ingreso; // me quede
        $capital_efectivo = $data->capital_efectivo; // amortizo a capital

        $total_pagado = ($tot_pagado_ori + $intercambio) - $sum_inter_pag;


        $tot_ing_venta_des = 0;
        if ($total_pagado > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $gast_xreversion), "debe" => $total_pagado, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
            );
            for ($j = 0; $j < count($inter_montos); $j++) {
                $iprov = $inter_montos[$j];
                $_sum_pag = 0;
                for ($k = 0; $k < count($inter_montos_pag); $k++) {
                    $ipag = $inter_montos_pag[$k];
                    if ($ipag->inter_id . '' == $iprov->inter_id . '') {
                        $_sum_pag = $ipag->monto;
                        break;
                    }
                }
                $dif_pag = $iprov->monto - $_sum_pag;
                if ($dif_pag > 0) {

                    $motivo = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id='$iprov->inter_id'");
                    $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
                    $inter_cuenta_debe = $motivo->{"inter_cuenta_debe$mon"};
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $inter_cuenta_debe), "debe" => 0, "haber" => $dif_pag,
                        "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                    );

                    $tot_ing_venta_des += $dif_pag;
                }
            }
            if ($ret_ingreso > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $ing_reversion), "debe" => 0, "haber" => $ret_ingreso,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
            }
        }
        $mon = $data->moneda_des == '1' ? '_bs' : ($data->moneda_des == '2' ? '_usd' : '');


        $cue_costo_des = $urb_des->urb_costo;
        $cue_costo_diferido_des = $urb_des->urb_costo_diferido;

        $cue_ing_diferido_des = $urb_des->urb_ing_diferido;

        $cue_cta_por_cobrar_des = $urb_des->{"urb_cta_por_cobrar$mon"};
        $cue_ing_por_venta_des = $urb_des->{"urb_ing_por_venta$mon"};

//        $cta_xpagar_desestimiento = "2.1.1.01.2.06";
        if ($capital_efectivo > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar_des), "debe" => 0, "haber" => $capital_efectivo,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
            $monto_pagar = $data->monto_pagar;
            if ($monto_pagar > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_xpagar_desestimiento), "debe" => 0, "haber" => $monto_pagar,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                );
            }
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido_des), "debe" => $capital_efectivo, "haber" => 0,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta_des), "debe" => 0, "haber" => $capital_efectivo,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
        }
        $costo_des = $data->costo_pagado;
        if ($costo_des > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_des), "debe" => $costo_des, "haber" => 0,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido_des), "debe" => 0, "haber" => $costo_des,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
        }


        /// devolucion por cada intercambio

        return $comprobante;
    }

    public static function venta_fusion_ext($data) {
        $data = (object) $data;
        $urb_ori = $data->urb_ori;
        $urb_des = $data->urb_des;

        $une_ori_id = $urb_ori->urb_une_id;
        $une_des_id = $urb_des->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;

        $comprobante = new stdClass();
        $comprobante->tipo = 'Diario';
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_fusion";
        $comprobante->tabla_id = $data->tabla_id;
        $comprobante->tarea_id = $data->tarea_id;


        $costo = $data->costo;
        $costo_pagado = $data->costo_pagado_ori;

        $saldo_ori = $data->saldo_ori;

        $ing_reversion = MODELO_COMPROBANTE::$ingreso_por_reversion; //"4.1.4.01.2.01";
        $gast_xreversion = MODELO_COMPROBANTE::$gasto_por_reversion; // "6.2.1.04.2.01";
        $cta_xpagar_desestimiento = MODELO_COMPROBANTE::$cuenta_por_pagar_desestimiento; // "2.1.1.01.2.06";
        $cta_xpagar_ori = $urb_ori->urb_cta_por_pagar;
        $cta_xpagar_des = $urb_des->urb_cta_por_pagar;

        $mon_ori = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
        $mon_des = $data->moneda_des == '1' ? '_bs' : ($data->moneda_des == '2' ? '_usd' : '');

        $cue_costo_diferido = $urb_ori->urb_costo_diferido;
        $cue_inv_diferido_adj = $urb_ori->urb_inv_terrenos_adj;
        $cue_ing_diferido = $urb_ori->urb_ing_diferido;

        $cue_cta_por_cobrar_ori = $urb_ori->{"urb_cta_por_cobrar$mon_ori"};

        $cue_cta_por_cobrar_des = $urb_des->{"urb_cta_por_cobrar$mon_des"};

        $mon = $data->moneda_des == '1' ? '_bs' : ($data->moneda_des == '2' ? '_usd' : '');

        $cue_costo_des = $urb_des->urb_costo;
        $cue_costo_diferido_des = $urb_des->urb_costo_diferido;

        $cue_ing_diferido_des = $urb_des->urb_ing_diferido;

        $cue_cta_por_cobrar_des = $urb_des->{"urb_cta_por_cobrar$mon"};
        $cue_ing_por_venta_des = $urb_des->{"urb_ing_por_venta$mon"};

        $intercambio = $data->intercambio;
        $inter_montos = $data->inter_montos;
        $inter_montos_pag = $data->inter_montos_pag;
        $sum_inter_pag = 0;
        foreach ($inter_montos_pag as $ipag) {
            $sum_inter_pag += $ipag->monto;
        }

        $tot_pagado_ori = $data->tot_pagado_ori; /// todo lo que pago
        $ret_ingreso = $data->ret_ingreso; // me quede
        $capital_efectivo = $data->capital_efectivo; // amortizo a capital

        $total_pagado = ($tot_pagado_ori + $intercambio) - $sum_inter_pag;

        $tot_ing_venta_des = 0;

        $can_id_orig = FUNCIONES::get_cuenta_ca($ges_id, $data->ven_can_codigo_ori);
        $can_id_dest = FUNCIONES::get_cuenta_ca($ges_id, $data->ven_can_codigo_dest);

        if ($urb_ori->urb_tipo == 'Externo') {
            if ($total_pagado > 0) {

                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_xpagar_ori), "debe" => $total_pagado, "haber" => 0,
                    "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );

                for ($j = 0; $j < count($inter_montos); $j++) {

                    $iprov = $inter_montos[$j];
                    $_sum_pag = 0;
                    for ($k = 0; $k < count($inter_montos_pag); $k++) {
                        $ipag = $inter_montos_pag[$k];
                        if ($ipag->inter_id . '' == $iprov->inter_id . '') {
                            $_sum_pag = $ipag->monto;
                            break;
                        }
                    }

                    $dif_pag = $iprov->monto - $_sum_pag;
                    if ($dif_pag > 0) {

                        $motivo = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id='$iprov->inter_id'");
                        $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
                        $inter_cuenta_debe = $motivo->{"inter_cuenta_debe$mon"};
                        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $inter_cuenta_debe), "debe" => 0, "haber" => $dif_pag,
                            "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                        );

                        $tot_ing_venta_des += $dif_pag;
                    }
                }

                if ($ret_ingreso > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb_ori->urb_ing_reserva), "debe" => 0, "haber" => $ret_ingreso,
                        "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                    );
                }
            }
        } else {
            if ($costo > 0) {
                if ($costo_pagado > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $ing_reversion), "debe" => 0, "haber" => $costo_pagado,
                        "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                    );
                }
                $costo_diferido = $costo - $costo_pagado;
                if ($costo_diferido > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido), "debe" => 0, "haber" => $costo_diferido,
                        "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                    );
                }

                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_diferido_adj), "debe" => $costo, "haber" => 0,
                    "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
            }

            if ($saldo_ori > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => $saldo_ori, "haber" => 0,
                    "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar_ori), "debe" => 0, "haber" => $saldo_ori,
                    "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
            }

            if ($total_pagado > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $gast_xreversion), "debe" => $total_pagado, "haber" => 0,
                    "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
                for ($j = 0; $j < count($inter_montos); $j++) {
                    $iprov = $inter_montos[$j];
                    $_sum_pag = 0;
                    for ($k = 0; $k < count($inter_montos_pag); $k++) {
                        $ipag = $inter_montos_pag[$k];
                        if ($ipag->inter_id . '' == $iprov->inter_id . '') {
                            $_sum_pag = $ipag->monto;
                            break;
                        }
                    }
                    $dif_pag = $iprov->monto - $_sum_pag;
                    if ($dif_pag > 0) {

                        $motivo = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id='$iprov->inter_id'");
                        $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
                        $inter_cuenta_debe = $motivo->{"inter_cuenta_debe$mon"};
                        $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $inter_cuenta_debe), "debe" => 0, "haber" => $dif_pag,
                            "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                        );

                        $tot_ing_venta_des += $dif_pag;
                    }
                }
                if ($ret_ingreso > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb_ori->urb_ing_reserva), "debe" => 0, "haber" => $ret_ingreso,
                        "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                    );
                }
            }
        }

        if ($urb_des->urb_tipo == 'Externo') {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_xpagar_des), "debe" => 0, "haber" => $capital_efectivo,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
        } else {

            if ($capital_efectivo > 0) {

                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar_des), "debe" => 0, "haber" => $capital_efectivo,
                    "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                );

                $monto_pagar = $data->monto_pagar;
                if ($monto_pagar > 0) {
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_xpagar_desestimiento), "debe" => 0, "haber" => $monto_pagar,
                        "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                    );
                }

                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido_des), "debe" => $capital_efectivo, "haber" => 0,
                    "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                );

                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta_des), "debe" => 0, "haber" => $capital_efectivo,
                    "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                );
            }

            $costo_des = $data->costo_pagado;
            if ($costo_des > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_des), "debe" => $costo_des, "haber" => 0,
                    "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido_des), "debe" => 0, "haber" => $costo_des,
                    "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                );
            }
        }

        return $comprobante;
    }

    public static function venta_cambio_lote($data) {
        $data = (object) $data;
        $urb_ori = $data->urb_ori;
        $urb_des = $data->urb_des;

        $une_ori_id = $urb_ori->urb_une_id;
        $une_des_id = $urb_des->urb_une_id;

        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;

        $comprobante = new stdClass();
        $comprobante->tipo = 'Diario';
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_cambio_lote";
        $comprobante->tabla_id = $data->tabla_id;
        $comprobante->tarea_id = $data->tarea_id;

        $can_codigo_ori = $data->ven_can_codigo_ori;
        $can_codigo_dest = $data->ven_can_codigo_dest;

        $can_id_orig = FUNCIONES::get_cuenta_ca($ges_id, $can_codigo_ori);
        $can_id_dest = FUNCIONES::get_cuenta_ca($ges_id, $can_codigo_dest);

        $costo = $data->costo;
        $costo_pagado = $data->costo_pagado;

        $pagado_ori = $data->pagado_ori;
        $saldo_ori = $data->saldo_ori;

        $saldo = $data->saldo;

        $mon_ori = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
        $mon_des = $data->moneda_des == '1' ? '_bs' : ($data->moneda_des == '2' ? '_usd' : '');

        // $ing_reversion = MODELO_COMPROBANTE::$ingreso_por_reversion; //"4.1.4.01.2.01";

        $ing_reversion = $urb_ori->{"urb_ing_por_reversion$mon_ori"};
        $gast_xreversion = MODELO_COMPROBANTE::$gasto_por_reversion; //"6.2.1.04.2.01";
//        $cta_xpagar_desestimiento = "2.1.1.01.2.06";
//        $cta_xpagar_intercambio = "2.1.1.01.2.02";



        $cue_costo_diferido_ori = $urb_ori->urb_costo_diferido;
        $cue_inv_terrenos_adj = $urb_ori->urb_inv_terrenos_adj;
        $cue_ing_diferido_ori = $urb_ori->urb_ing_diferido;

//        $cue_cta_por_cobrar_ori = $urb_des->{"urb_cta_por_cobrar$mon_ori"};
        $cue_cta_por_cobrar_ori = $urb_ori->{"urb_cta_por_cobrar$mon_ori"};






        if ($costo > 0) {
            if ($costo_pagado > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $ing_reversion), "debe" => 0, "haber" => $costo_pagado,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
            }
            $costo_diferido = round($costo - $costo_pagado, 2);
            if ($costo_diferido > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido_ori), "debe" => 0, "haber" => $costo_diferido,
                    "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
            }

            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_terrenos_adj), "debe" => $costo, "haber" => 0,
                "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
            );
        }

        if ($saldo_ori > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido_ori), "debe" => $saldo_ori, "haber" => 0,
                "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
            );
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar_ori), "debe" => 0, "haber" => $saldo_ori,
                "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
            );
        }

        $intercambio = $data->intercambio;
        $inter_montos = $data->inter_montos;
        $inter_montos_pag = $data->inter_montos_pag;
        $sum_inter_pag = 0;
        foreach ($inter_montos_pag as $ipag) {
            $sum_inter_pag += $ipag->monto;
        }

        $total_pagado = ($pagado_ori + $intercambio) - $sum_inter_pag;

        $tot_ing_venta_des = 0;
        if ($total_pagado > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $gast_xreversion), "debe" => $total_pagado, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
            );
            for ($j = 0; $j < count($inter_montos); $j++) {
                $iprov = $inter_montos[$j];
                $_sum_pag = 0;
                for ($k = 0; $k < count($inter_montos_pag); $k++) {
                    $ipag = $inter_montos_pag[$k];
                    if ($ipag->inter_id . '' == $iprov->inter_id . '') {
                        $_sum_pag = $ipag->monto;
                        break;
                    }
                }
                $dif_pag = $iprov->monto - $_sum_pag;
                if ($dif_pag > 0) {

                    $motivo = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id='$iprov->inter_id'");
                    $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
                    $inter_cuenta_debe = $motivo->{"inter_cuenta_debe$mon"};
                    $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $inter_cuenta_debe), "debe" => 0, "haber" => $dif_pag,
                        "glosa" => $glosa, "ca" => $can_id_orig, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                    );

                    $tot_ing_venta_des += $dif_pag;
                }
            }
            $pagar_desestimiento = $pagado_ori;
            if ($pagar_desestimiento > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb_des->urb_reserva), "debe" => 0, "haber" => $pagar_desestimiento,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb_des->urb_reserva), "debe" => $pagar_desestimiento, "haber" => 0,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                );
                $tot_ing_venta_des += $pagado_ori;
            }
        }

//        $cue_cta_por_cobrar_des = $urb_des->{"urb_cta_por_cobrar$mon_des"};

        $cue_ing_diferido_des = $urb_des->urb_ing_diferido;
//        $cue_reserva = $urb_des->urb_reserva;

        $cue_cta_por_cobrar_des = $urb_des->{"urb_cta_por_cobrar$mon_des"};
        $cue_ing_por_venta_des = $urb_des->{"urb_ing_por_venta$mon_des"};

        if ($saldo > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar_des), "debe" => $saldo, "haber" => 0,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
        }

        for ($j = 0; $j < count($inter_montos); $j++) {
            $iprov = $inter_montos[$j];
            $_sum_pag = 0;
            for ($k = 0; $k < count($inter_montos_pag); $k++) {
                $ipag = $inter_montos_pag[$k];
                if ($ipag->inter_id . '' == $iprov->inter_id . '') {
                    $_sum_pag = $ipag->monto;
                    break;
                }
            }
            $dif_pag = $iprov->monto - $_sum_pag;
            if ($dif_pag > 0) {
                $motivo = FUNCIONES::objeto_bd_sql("select * from ter_intercambio where inter_id='$iprov->inter_id'");
                $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
                $inter_cuenta_debe = $motivo->{"inter_cuenta_debe$mon"};
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $inter_cuenta_debe), "debe" => $dif_pag, "haber" => 0,
                    "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                );
            }
        }
        $monto_pagar = $data->monto_pagar;
        $tot_ing_venta_des = $tot_ing_venta_des - $monto_pagar;


        if ($tot_ing_venta_des > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta_des), "debe" => 0, "haber" => $tot_ing_venta_des,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
        }
        $cuenta_por_pagar_desestimiento = MODELO_COMPROBANTE::$cuenta_por_pagar_desestimiento;

        if ($monto_pagar > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cuenta_por_pagar_desestimiento), "debe" => 0, "haber" => $monto_pagar,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
        }



        if ($saldo > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido_des), "debe" => 0, "haber" => $saldo,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
        }


        $cue_costo_des = $urb_des->urb_costo;
        $cue_costo_diferido_des = $urb_des->urb_costo_diferido;
        $cue_inv_terrenos_des = ($data->orden == 1) ? $urb_des->urb_inv_terrenos : $urb_des->urb_inv_terrenos_adj;


        $inventario_des = $data->costo_des;
        $costo_des = $data->costo_cub_des;
        $costo_diferito_des = round($data->costo_des - $data->costo_cub_des, 2);

        if ($inventario_des > 0) {
            if ($costo_des > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_des), "debe" => $costo_des, "haber" => 0,
                    "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            if ($costo_diferito_des > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido_des), "debe" => $costo_diferito_des, "haber" => 0,
                    "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }

            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_terrenos_des), "debe" => 0, "haber" => $inventario_des,
                "glosa" => $glosa, "ca" => $can_id_dest, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            );
        }


//        if($monto_pagar>0){
//            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_terrenos_des), "debe" => 0, "haber" => $monto_pagar,
//                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
//            );
//        }


        return $comprobante;
    }

    public static function devolucion_venta($data) {

        $data = (object) $data;
        $urb = $data->urb;
        $une_id = $urb->urb_une_id;
        $glosa = $data->glosa;
        $fecha_cmp = $data->fecha;
        $ges_id = $data->ges_id;
        $comprobante = new stdClass();
        $comprobante->tipo = $data->dev_monto > 0 ? 'Egreso' : 'Diario';
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $fecha_cmp;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_cmp);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_devolucion";
        $comprobante->tabla_id = $data->tabla_id;

        $facturar = 0; // $urb->urb_facturado;

        if ($facturar) {
            $cue_gasto_it = FUNCIONES::parametro('it', $ges_id);
            $cue_it_xpagar = FUNCIONES::parametro('itpagar', $ges_id);
            $cue_debito_fiscal = FUNCIONES::parametro('deb_fiscal', $ges_id);
            $porc_it = FUNCIONES::parametro('val_it', $ges_id);
            $porc_iva = FUNCIONES::parametro('val_iva', $ges_id);
        }
        $cta_xpagar_desestimiento = MODELO_COMPROBANTE::$cuenta_por_pagar_desestimiento;

        $cue_ing_devolucion = ($data->moneda == 1) ? $urb->urb_ing_por_devolucion_bs : $urb->urb_ing_por_devolucion_usd;

        if ($data->monto_pagado > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_xpagar_desestimiento), "debe" => $data->monto_pagado, "haber" => 0,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => '0', 'une_id' => $une_id
            );
        }
        if ($data->dev_monto > 0) {
            FUNCIONES::add_elementos($comprobante->detalles, $data->detalles);
        }
        if ($data->dev_ingreso > 0) {
            $monto = $data->dev_ingreso;
            $iva_monto = 0;
            $it_monto = 0;
            if ($facturar) {
                $iva_monto = round($monto * $porc_iva / 100, 2);
                $it_monto = round($monto * $porc_it / 100, 2);
                $monto = $monto - $iva_monto;
            }

            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_devolucion), "debe" => 0, "haber" => $monto,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
            );
            if ($it_monto > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_gasto_it), "debe" => $it_monto, "haber" => 0,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_it_xpagar), "debe" => 0, "haber" => $it_monto,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
                );
            }
            if ($iva_monto > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_debito_fiscal), "debe" => 0, "haber" => $iva_monto,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
                );
            }
        }

        return $comprobante;
    }

    public static function venta_intercambio_pago($data) {
        $data = (object) $data;
        $interno = $data->interno;


        $glosa = $data->glosa;
        $ges_id = $data->ges_id;
        $urb = $data->urb;

        $comprobante = new stdClass();

        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $interno;
        $comprobante->tabla = "venta_intercambio_pago";
        $comprobante->tabla_id = $data->tabla_id;

//        'monto'=>$monto,
//        'debito'=>$debito,
//        'une_gastos_ids'=>$_POST[gastos_une_ids],
//        'une_gastos_porc'=>$_POST[gastos_une_porc],
//        echo "---$inter_cuenta_debe---";

        $cta_gasto_id = $data->cta_gasto_id;
        $motivo_inter = $data->motivo_inter;
        $monto = $data->monto;
        $debito = $data->debito;
        $gastos_ids = $data->une_gastos_ids;
        $gastos_porc = $data->une_gastos_porc;

        $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
        $inter_cuenta_debe = $motivo_inter->{"inter_cuenta_debe$mon"};

        if ($debito == 'factura') {
            $cue_credito_fiscal = FUNCIONES::parametro('cred_fiscal', $ges_id);
            $porc_iva = FUNCIONES::parametro('val_iva', $ges_id);
            $monto_iva = $monto * $porc_iva / 100;
            $gasto_neto = $monto - $monto_iva;
            $sum_gmonto = 0;
            if ($gasto_neto > 0) {
                for ($i = 0; $i < count($gastos_ids); $i++) {
                    $gid = $gastos_ids[$i];
                    $gporc = $gastos_porc[$i];
                    $gmonto = $gasto_neto * $gporc / 100;
                    if ($i == count($gastos_ids) - 1) {
                        $gmonto = $gasto_neto - $sum_gmonto;
                    }
                    $sum_gmonto += $gmonto;
                    if ($gmonto > 0) {
                        $comprobante->detalles[] = array("cuen" => $cta_gasto_id, "debe" => $gmonto, "haber" => 0,
                            "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $gid
                        );
                    }
                }
            }
            if ($monto_iva > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_credito_fiscal), "debe" => $monto_iva, "haber" => 0,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            if ($monto > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $inter_cuenta_debe), "debe" => 0, "haber" => $monto,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
        } elseif ($debito == 'retencion') {
            $cue_ret_it = FUNCIONES::parametro('ret_it', $ges_id);
            $porc_ret_it = FUNCIONES::parametro('porc_ret_it', $ges_id);
            if ($motivo_inter->inter_tipo == 'Bienes') { // bienes
                $cue_ret_iue = FUNCIONES::parametro('ret_iue_bien', $ges_id);
                $porc_ret_iue = FUNCIONES::parametro('porc_ret_iue_bien', $ges_id);
            } elseif ($motivo_inter->inter_tipo == 'Servicio') { // servicio
                $cue_ret_iue = FUNCIONES::parametro('ret_iue_serv', $ges_id);
                $porc_ret_iue = FUNCIONES::parametro('porc_ret_iue_serv', $ges_id);
            }
            $n_monto = $monto / ((100 - ($porc_ret_iue + $porc_ret_it)) / 100);

            $monto_ret_it = $n_monto * $porc_ret_it / 100;
            $monto_ret_iue = $n_monto * $porc_ret_iue / 100;
            $gasto_neto = $monto + $monto_ret_it + $monto_ret_iue;

            if ($gasto_neto > 0) {
                for ($i = 0; $i < count($gastos_ids); $i++) {
                    $gid = $gastos_ids[$i];
                    $gporc = $gastos_porc[$i];
                    $gmonto = $gasto_neto * $gporc / 100;
                    if ($i == count($gastos_ids) - 1) {
                        $gmonto = $gasto_neto - $sum_gmonto;
                    }
                    $sum_gmonto += $gmonto;
                    if ($gmonto > 0) {
                        $comprobante->detalles[] = array("cuen" => $cta_gasto_id, "debe" => $gmonto, "haber" => 0,
                            "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $gid
                        );
                    }
                }
            }
            if ($monto_ret_iue > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ret_iue), "debe" => 0, "haber" => $monto_ret_iue,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            if ($monto_ret_it > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ret_it), "debe" => 0, "haber" => $monto_ret_it,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
            if ($monto > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $inter_cuenta_debe), "debe" => 0, "haber" => $monto,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
        } else {
            $gasto_neto = $monto;
            if ($gasto_neto > 0) {
                for ($i = 0; $i < count($gastos_ids); $i++) {
                    $gid = $gastos_ids[$i];
                    $gporc = $gastos_porc[$i];
                    $gmonto = $gasto_neto * $gporc / 100;
                    if ($i == count($gastos_ids) - 1) {
                        $gmonto = $gasto_neto - $sum_gmonto;
                    }
                    $sum_gmonto += $gmonto;
                    if ($gmonto > 0) {
                        $comprobante->detalles[] = array("cuen" => $cta_gasto_id, "debe" => $gmonto, "haber" => 0,
                            "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $gid
                        );
                    }
                }
            }
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $inter_cuenta_debe), "debe" => 0, "haber" => $monto,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            );
        }
//        FUNCIONES::print_pre($comprobante);
        return $comprobante;
    }

    public static function cv_divisa($data) {
        $data = (object) $data;
        $interno = $data->interno;
        $glosa = $data->glosa;
        $ges_id = $data->ges_id;

        $comprobante = new stdClass();

        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $interno;
        $comprobante->tabla = "cv_divisa";
        $comprobante->tabla_id = $data->tabla_id;

        $comprobante->tcambios = $data->tcambios;

        $dif_cambio_diponibilidades = MODELO_COMPROBANTE::$dif_cambio_diponibilidades;

        if ($data->tipo == 'Compra') {
            $comprobante->detalles[] = array("cuen" => $data->cuenta_ori, "debe" => $data->monto_ori, "haber" => 0,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => 0, 'int_id' => '0'
            );
            $comprobante->detalles[] = array("cuen" => $data->cuenta_des, "debe" => 0, "haber" => $data->monto_des,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => 0, 'int_id' => '0'
            );
            if ($data->dif > 0) {
                $dif = $data->dif;
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $dif_cambio_diponibilidades), "debe" => 0, "haber" => $dif,
                    "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => 0, 'int_id' => '0'
                );
            } elseif ($data->dif < 0) {
                $dif = $data->dif * (-1);
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $dif_cambio_diponibilidades), "debe" => $dif, "haber" => 0,
                    "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => 0, 'int_id' => '0'
                );
            }
        } else if ($data->tipo == 'Venta') {
            $comprobante->detalles[] = array("cuen" => $data->cuenta_ori, "debe" => 0, "haber" => $data->monto_ori,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => 0, 'int_id' => '0'
            );
            $comprobante->detalles[] = array("cuen" => $data->cuenta_des, "debe" => $data->monto_des, "haber" => 0,
                "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => 0, 'int_id' => '0'
            );
            if ($data->dif > 0) {
                $dif = $data->dif;
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $dif_cambio_diponibilidades), "debe" => 0, "haber" => $dif,
                    "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => 0, 'int_id' => '0'
                );
            } elseif ($data->dif < 0) {
                $dif = $data->dif * (-1);
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $dif_cambio_diponibilidades), "debe" => $dif, "haber" => 0,
                    "glosa" => $glosa, "ca" => '0', "cf" => '0', "cc" => 0, 'int_id' => '0'
                );
            }
        }



        return $comprobante;
    }

    public static function pago_descuento_incremento($data) {

        $data = (object) $data;
        $urbanizacion = $data->urbanizacion;
        $venta = $data->venta;
        $pag = $data->pago;
        $fecha_pago = $pag->vpag_fecha_pago;
        $glosa = $data->glosa;
        $ges_id = $data->ges_id;

        $comprobante = new stdClass();
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = "Efectivo";
        $comprobante->ban_id = 0;
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_pago_desc_inc";
        $comprobante->tabla_id = $data->tabla_id;

        if ($data->origen == 'ajustes') {
            if (!$data->con_detalles) {
                return $comprobante;
            }
        }

        if ($venta->ven_moneda == 1) {
            $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_bs;
            $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_bs;
            $codigo_incremento_capital = $urbanizacion->urb_cue_incremento_capital_bs;
            $codigo_descuento_capital = $urbanizacion->urb_cue_descuento_capital_bs;
        } else {
            $codigo_cta_por_cobrar = $urbanizacion->urb_cta_por_cobrar_usd;
            $codigo_ingreso_por_venta = $urbanizacion->urb_ing_por_venta_usd;
            $codigo_incremento_capital = $urbanizacion->urb_cue_incremento_capital_usd;
            $codigo_descuento_capital = $urbanizacion->urb_cue_descuento_capital_usd;
        }

        $codigo_ingreso_diferido = $urbanizacion->urb_ing_diferido;
        $codigo_costo_diferido = $urbanizacion->urb_costo_diferido;
        $codigo_costo = $urbanizacion->urb_costo;

        $costo_por_descuento = $data->costo;

        if ($pag->vpag_capital_inc > 0) {

            $detalles[] = array(
                "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_cta_por_cobrar),
                "debe" => $pag->vpag_capital_inc,
                "haber" => 0,
                "glosa" => $glosa,
                "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
                "cf" => '0',
                "cc" => 0,
                'une_id' => $urbanizacion->urb_une_id
            );

            $detalles[] = array(
                "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_ingreso_diferido),
                "debe" => 0,
                "haber" => $pag->vpag_capital_inc,
                "glosa" => $glosa,
                "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
                "cf" => '0',
                "cc" => 0,
                'une_id' => $urbanizacion->urb_une_id
            );
        }

        if ($pag->vpag_capital_desc > 0) {

            $detalles[] = array(
                "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_descuento_capital),
                "debe" => $pag->vpag_capital_desc,
                "haber" => 0,
                "glosa" => $glosa,
                "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
                "cf" => '0',
                "cc" => 0,
                'une_id' => $urbanizacion->urb_une_id
            );

            $detalles[] = array(
                "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_cta_por_cobrar),
                "debe" => 0,
                "haber" => $pag->vpag_capital_desc,
                "glosa" => $glosa,
                "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
                "cf" => '0',
                "cc" => 0,
                'une_id' => $urbanizacion->urb_une_id
            );

            $detalles[] = array(
                "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_ingreso_diferido),
                "debe" => $pag->vpag_capital_desc,
                "haber" => 0,
                "glosa" => $glosa,
                "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
                "cf" => '0',
                "cc" => 0,
                'une_id' => $urbanizacion->urb_une_id
            );

            $detalles[] = array(
                "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_ingreso_por_venta),
                "debe" => 0,
                "haber" => $pag->vpag_capital_desc,
                "glosa" => $glosa,
                "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
                "cf" => '0',
                "cc" => 0,
                'une_id' => $urbanizacion->urb_une_id
            );

            if ($costo_por_descuento > 0) {
                $detalles[] = array(
                    "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_costo),
                    "debe" => $costo_por_descuento,
                    "haber" => 0,
                    "glosa" => $glosa,
                    "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
                    "cf" => '0',
                    "cc" => 0,
                    'une_id' => $urbanizacion->urb_une_id
                );

                $detalles[] = array(
                    "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_costo_diferido),
                    "debe" => 0,
                    "haber" => $costo_por_descuento,
                    "glosa" => $glosa,
                    "ca" => FUNCIONES::get_cuenta_ca($ges_id, $venta->ven_can_codigo),
                    "cf" => '0',
                    "cc" => 0,
                    'une_id' => $urbanizacion->urb_une_id
                );
            }
        }

        $comprobante->detalles = $detalles;
        return $comprobante;
    }

    /*
      MODELOS PARA LOS COMPROBANTES DE LA INFORMACION MIGRADA
     */

    public static function venta_migracion($data) {
        $data = (object) $data;
        $urb = $data->urb;
        $venta = $data->venta;
        $glosa = $data->glosa;
        $ges_id = $data->ges_id;

        // FUNCIONES::print_pre($urb);

        $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
        $cue_costo = $urb->urb_costo;
        $cue_costo_diferido = $urb->urb_costo_diferido;
        $cue_inv_terrenos = $urb->urb_inv_terrenos;
        $cue_ing_diferido = $urb->urb_ing_diferido;
        $cue_reserva = $urb->urb_reserva;

        $cue_costo_producto = $urb->urb_cue_costo_producto;
        $cue_costo_diferido_producto = $urb->urb_cue_costo_diferido_producto;
        $cue_inv_producto = $urb->urb_cue_inv_producto;
        $cue_cta_por_cobrar_producto = $urb->urb_cue_por_cobrar_producto;
        $cue_ing_diferido_producto = $urb->urb_cue_ing_diferido_producto;

        $cue_cta_por_cobrar = $urb->{"urb_cta_por_cobrar$mon"};
        $cue_ing_por_venta = $urb->{"urb_ing_por_venta$mon"};
        $cue_ing_producto = $urb->urb_cue_ing_producto;

        $codigo_cue_descuento = $urb->{"urb_cue_descuento_capital$mon"};

        $moneda = $data->moneda;

        $comprobante = new stdClass();
        $comprobante->tipo = "Diario";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = "Efectivo";
        $comprobante->ban_id = 0;
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $data->interno;
        $comprobante->tabla = "venta_migracion";
        $comprobante->tabla_id = $data->tabla_id;

        $anticipo = $data->anticipo;
        $saldo_efectivo = $data->saldo_efectivo;

        $monto_intercambio = $data->monto_intercambio;
        $intercambio_ids = $data->intercambio_ids;
        $intercambio_montos = $data->intercambio_montos;
        $monto_pagar = $data->monto_pagar;

        $inventario = $data->costo;
        $costo = $data->costo;
        $costo_cub = $data->costo_cub;
        $costo_diferido = $costo - $costo_cub;
        $monto_comision = $data->monto_comision;

        $monto_producto = $data->monto_producto;
        $inventario_producto = $data->costo_producto;
        $costo_producto_cubierto = $data->costo_producto_cub;
        $costo_diferido_producto = $inventario_producto - $costo_producto_cubierto;

        $prorat_lote = $data->prorat_lote;
        $prorat_producto = $data->prorat_producto;

        $ven_can_codigo = $data->ven_can_codigo;
        $descuento = $data->descuento;

        $pagos_gest_ant_2015 = $data->pagos_gest_ant_2015;
        $costo_gest_ant_2015 = $data->costo_gest_ant_2015;

        if ($saldo_efectivo > 0) {
            $comprobante->detalles[] = array(
                "cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar),
                "debe" => $saldo_efectivo,
                "haber" => 0,
                "glosa" => $glosa,
                "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo),
                "cf" => '0',
                "cc" => 0,
                "une_id" => $urb->urb_une_id
            );

            $comprobante->detalles[] = array(
                "cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido),
                "debe" => 0,
                "haber" => $saldo_efectivo,
                "glosa" => $glosa,
                "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo),
                "cf" => '0',
                "cc" => 0,
                "une_id" => $urb->urb_une_id
            );
        }

        // if ($descuento > 0) {
        // $comprobante->detalles[] = array(
        // "cuen" => FUNCIONES::get_cuenta($ges_id, $codigo_cue_descuento), "debe" => $descuento, 
        // "haber" => 0, "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), 
        // "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
        // );
        // }

        if ($inventario > 0) {
            if ($costo_cub > 0) {
                $comprobante->detalles[] = array(
                    "cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo),
                    "debe" => $costo_cub,
                    "haber" => 0,
                    "glosa" => $glosa,
                    "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo),
                    "cf" => '0',
                    "cc" => 0,
                    "une_id" => $urb->urb_une_id
                );
            }
            if ($costo_diferido > 0) {
                $comprobante->detalles[] = array(
                    "cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido),
                    "debe" => $costo_diferido,
                    "haber" => 0,
                    "glosa" => $glosa,
                    "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo),
                    "cf" => '0',
                    "cc" => 0,
                    "une_id" => $urb->urb_une_id
                );
            }

            $comprobante->detalles[] = array(
                "cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_terrenos),
                "debe" => 0,
                "haber" => $inventario,
                "glosa" => $glosa,
                "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo),
                "cf" => '0',
                "cc" => 0,
                "une_id" => $urb->urb_une_id
            );
        }

        if ($venta->ven_tipo == 'Credito') {

            if ($pagos_gest_ant_2015 > 0) {

                $capital_lote = $pagos_gest_ant_2015;
                $glosa_2 = "Pagos Anteriores a la Gestion 2015(Venta $data->tabla_id)";

                $comprobante->detalles[] = array(
                    "cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar), "debe" => 0,
                    "haber" => $capital_lote, "glosa" => $glosa_2, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo),
                    "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => $capital_lote, "haber" => 0,
                    "glosa" => $glosa_2, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }

            if ($costo_gest_ant_2015 > 0) {
                $glosa_3 = "Costo Cubierto por los Pagos Anteriores a la Gestion 2015(Venta $data->tabla_id)";
                $costo = $costo_gest_ant_2015;
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo), "debe" => $costo, "haber" => 0,
                    "glosa" => $glosa_3, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido), "debe" => 0, "haber" => $costo,
                    "glosa" => $glosa_3, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
        }

        return $comprobante;
    }

    public static function pago_migracion($data) {
        $data = (object) $data;
        $interno = $data->interno;


        $glosa = $data->glosa;
        $ges_id = $data->ges_id;
        $urb = $data->urb;

        $mon = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');

        $cue_costo = $urb->urb_costo;
        $cue_costo_diferido = $urb->urb_costo_diferido;

        $cue_ing_diferido = $urb->urb_ing_diferido;

        $cue_costo_producto = $urb->urb_cue_costo_producto;
        $cue_costo_diferido_producto = $urb->urb_cue_costo_diferido_producto;

        $cue_ing_diferido_producto = $urb->urb_cue_ing_diferido_producto;

        $cue_cta_por_cobrar = $urb->{"urb_cta_por_cobrar$mon"};
        $cue_por_cobrar_producto = $urb->{"urb_cue_por_cobrar_producto"};
        $cue_ing_por_venta = $urb->{"urb_ing_por_venta$mon"};
        $cue_ing_producto = $urb->{"urb_cue_ing_producto"};
        $cue_ing_por_interes = $urb->{"urb_ing_por_interes$mon"};
        $cue_ing_por_form = $urb->{"urb_ing_por_form$mon"};
        $cue_ing_por_mora = $urb->{"urb_ing_por_mora$mon"};
        $cue_ing_por_envio = '4.1.2.02.2.01';

        $comprobante = new stdClass();

        $comprobante->tipo = "Ingreso";
        $comprobante->mon_id = $data->moneda;
        $comprobante->nro_documento = date("Ydm");
        $comprobante->fecha = $data->fecha;
        $comprobante->ges_id = $ges_id;
        $comprobante->peri_id = FUNCIONES::obtener_periodo($data->fecha);
        $comprobante->forma_pago = 'Efectivo';
        $comprobante->ban_id = '';
        $comprobante->ban_char = '';
        $comprobante->ban_nro = '';
        $comprobante->glosa = $glosa;
        $comprobante->referido = $interno;
        $comprobante->tabla = "venta_pago_migracion";
        $comprobante->tabla_id = $data->tabla_id;

        $interes = $data->interes;
        $capital = $data->capital;
        $form = $data->form;
        $envio = $data->envio;
        $mora = $data->mora;
        $costo = $data->costo;

        $ven_can_codigo = $data->ven_can_codigo;

        if ($urb->urb_tipo == 'Interno') {
            if ($capital > 0) {

                $capital_lote = $capital;

                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => $capital_lote, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );

                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar), "debe" => 0, "haber" => $capital_lote,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );

                // $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta), "debe" => 0, "haber" => $capital_lote,
                // "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                // );
            }

            if ($costo > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo), "debe" => $costo, "haber" => 0,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido), "debe" => 0, "haber" => $costo,
                    "glosa" => $glosa, "ca" => FUNCIONES::get_cuenta_ca($ges_id, $ven_can_codigo), "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
                );
            }
        }

        return $comprobante;
    }

}
