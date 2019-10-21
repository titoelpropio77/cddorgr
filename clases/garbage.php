<?php

class algo {

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


        $costo = $data->costo;
        $costo_pagado = $data->costo_pagado_ori;

//        'costo'=>$venta_ori->ven_costo,
//        'costo_pagado'=>$venta_ori->ven_costo_cub+$pagado_ori->costo,
//
//        'saldo_ori'=>$saldo_ori,
//        'tot_pagado_ori'=>$tot_pagado_ori,
//        'ret_ingreso'=>$ret_ingreso,
//        'capital_efectivo'=>$capital_efectivo,        



        $saldo_ori = $data->saldo_ori;

        $ing_reversion = MODELO_COMPROBANTE::$ingreso_por_reversion; //"4.1.4.01.2.01";
        $gast_xreversion = MODELO_COMPROBANTE::$gasto_por_reversion; // "6.2.1.04.2.01";
        $cta_xpagar_desestimiento = MODELO_COMPROBANTE::$cuenta_por_pagar_desestimiento; // "2.1.1.01.2.06";


        $mon_ori = $data->moneda == '1' ? '_bs' : ($data->moneda == '2' ? '_usd' : '');
        $mon_des = $data->moneda_des == '1' ? '_bs' : ($data->moneda_des == '2' ? '_usd' : '');

        $cue_costo_diferido = $urb_ori->urb_costo_diferido;
        $cue_inv_diferido_adj = $urb_ori->urb_inv_terrenos_adj;
//        $cue_ing_diferido = $urb->urb_ing_diferido;
        $cue_ing_diferido = $urb_ori->urb_ing_diferido;

        $cue_cta_por_cobrar_ori = $urb_ori->{"urb_cta_por_cobrar$mon_ori"};

        $cue_cta_por_cobrar_des = $urb_des->{"urb_cta_por_cobrar$mon_des"};



        if ($costo > 0) {
            if ($costo_pagado > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $ing_reversion), "debe" => 0, "haber" => $costo_pagado,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
            }
            $costo_diferido = $costo - $costo_pagado;
            if ($costo_diferido > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido), "debe" => 0, "haber" => $costo_diferido,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                );
            }

            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_inv_diferido_adj), "debe" => $costo, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
            );
        }

        if ($saldo_ori > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido), "debe" => $saldo_ori, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
            );
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_cta_por_cobrar_ori), "debe" => 0, "haber" => $saldo_ori,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
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
                        "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_ori_id
                    );

                    $tot_ing_venta_des += $dif_pag;
                }
            }
            if ($ret_ingreso > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $urb_ori->urb_ing_reserva), "debe" => 0, "haber" => $ret_ingreso,
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
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
            $monto_pagar = $data->monto_pagar;
            if ($monto_pagar > 0) {
                $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cta_xpagar_desestimiento), "debe" => 0, "haber" => $monto_pagar,
                    "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
                );
            }
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_diferido_des), "debe" => $capital_efectivo, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_ing_por_venta_des), "debe" => 0, "haber" => $capital_efectivo,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
        }
        $costo_des = $data->costo_pagado;
        if ($costo > 0) {
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_des), "debe" => $costo_des, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
            $comprobante->detalles[] = array("cuen" => FUNCIONES::get_cuenta($ges_id, $cue_costo_diferido_des), "debe" => 0, "haber" => $costo_des,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_des_id
            );
        }


        /// devolucion por cada intercambio

        return $comprobante;
    }

}
