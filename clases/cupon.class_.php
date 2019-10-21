<?php
session_start();

class CUPON {

    public static function generar_cupones($vpag_id, $sorteo) {
        $pago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_id='$vpag_id'");

        $resp = new stdClass();
        $resp->ok = TRUE;

        $ind_ids = "";

//        if ($pago->vpag_interes_ids != '' && $pago->vpag_interes_ids != NULL) {
//            $ind_ids = $pago->vpag_interes_ids;
//        } else if ($pago->vpag_capital_ids != '' && $pago->vpag_capital_ids != NULL) {
//            $ind_ids = $pago->vpag_capital_ids;
//        }

        if ($pago->vpag_capital_ids != '' && $pago->vpag_capital_ids != NULL) {
            $ind_ids = $pago->vpag_capital_ids;
        }

        if ($ind_ids == '') {
            $resp->ok = FALSE;
            $resp->mensaje = "No hay cuotas para generar cupones.";
            return $resp;
        }

        $sql_deudas = "select * from interno_deuda where ind_id in ($ind_ids)
        and ind_estado='Pagado'";

        echo "<p style='color:green'>$sql_deudas;</p>";

        $cuotas_pagadas = FUNCIONES::objetos_bd_sql($sql_deudas);

        $resp->mens_errors = array();
        for ($i = 0; $i < $cuotas_pagadas->get_num_registros(); $i++) {

            $cuota = $cuotas_pagadas->get_objeto();

            if ($cuota->ind_fecha_pago > $cuota->ind_fecha_programada) {
                $cuotas_pagadas->siguiente();
                continue;
            }

            $datos_cupon = (object) array(
                        'cuota' => $cuota,
                        'sorteo' => $sorteo,
                        'vpag_id' => $vpag_id,
                        'ven_id' => $pago->vpag_ven_id,
            );

            if (substr($cuota->ind_fecha_pago, 0, 7) == substr($cuota->ind_fecha_programada, 0, 7)) {

                $resp_cup = CUPON::generar_cupon($datos_cupon);

                if (!$resp_cup->ok) {
                    $resp->ok = FALSE;
                    $resp->mens_errors[] = $resp_cup->mensaje;
                }
            } else {
                $resp_cup = CUPON::generar_cupon($datos_cupon);
                if (!$resp_cup->ok) {
                    $resp->ok = FALSE;
                    $resp->mens_errors[] = $resp_cup->mensaje;
                }

                $resp_cup2 = CUPON::generar_cupon($datos_cupon);
                if (!$resp_cup2->ok) {
                    $resp->ok = FALSE;
                    $resp->mens_errors[] = $resp_cup2->mensaje;
                }
            }

            $cuotas_pagadas->siguiente();
        }

        $cupones = FUNCIONES::atributo_bd_sql("select count(cup_id)as campo 
        from cupon where cup_vpag_id='$vpag_id' and cup_sorteo_id='$sorteo'
        and cup_estado='Activo' and cup_eliminado='No'") * 1;

        $resp->mensaje = "Se crearon $cupones cupones exitosamente.";

        return $resp;
    }

    private static function generar_cupon($datos_cupon) {

        $resp = new stdClass();

        $resp_num = CUPON::obtener_numero($datos_cupon);

        if (!$resp_num->ok) {
            $resp->ok = FALSE;
            $resp->mensaje = "Error al obtener el numero de cupon.";
            return $resp;
        }

        $numero = $resp_num->datos;
        $now = date('Y-m-d H:i:s');

        $cuota = $datos_cupon->cuota;
        $ind_id = $cuota->ind_id;
        $sql_ins = "insert into cupon(cup_vpag_id,cup_ven_id,cup_sorteo_id,cup_ind_id,
        cup_numero,cup_fecha_cre,cup_usu_cre)values('$datos_cupon->vpag_id',
        '$datos_cupon->ven_id','$datos_cupon->sorteo','$ind_id','$numero','$now',
        '$_SESSION[id]')";

        FUNCIONES::bd_query($sql_ins);

        $resp->ok = TRUE;

        return $resp;
    }

    private static function obtener_numero($params) {

        $resp = new stdClass();

//        $ind_id = $params->ind_id;
//        $vpag_id = $params->vpag_id;
        $sorteo = $params->sorteo;

//        $cuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda
//        where ind_id='$ind_id'");
        $cuota = $params->cuota;

        if ($cuota == NULL) {
            $resp->ok = FALSE;
            $resp->mensaje = "No existe la cuota.";
            return false;
        }

        $obj_sorteo = FUNCIONES::objeto_bd_sql("select *
        from sorteo where sor_id='$sorteo' and sor_eliminado='No'");

        if ($obj_sorteo == NULL) {
            $resp->ok = FALSE;
            $resp->mensaje = "No existe el sorteo.";
            return false;
        }

        $d = explode('-', $cuota->ind_fecha_programada);
        $periodo = $d[0] . $d[1];
        $numero = $obj_sorteo->sor_numero_cupon;

        $nro_cup = $numero . '';

        $ceros = '';
        for ($i = strlen($nro_cup); $i < 10; $i++) {
            $ceros .= '0';
        }

        $numero++;
        $sql_upd_sorteo = "update sorteo set sor_numero_cupon='$numero' 
        where sor_id='$sorteo'";
        FUNCIONES::bd_query($sql_upd_sorteo);

        $nro_cupon = "{$sorteo}{$ceros}{$nro_cup}";

        $resp->ok = TRUE;
        $resp->datos = $nro_cupon;

        return $resp;
    }

    public static function anular_cupones($vpag_id, $sorteo) {

//        echo "<pre>SESSION";
//        print_r($_SESSION);
//        echo "SESSION</pre>";

        $now = date('Y-m-d H:i:s');
        $user = $_SESSION[id];
        $sql = "update cupon set cup_estado='Anulado',cup_usu_anu='$user',
        cup_fecha_anu='$now' where cup_vpag_id='$vpag_id' and cup_sorteo_id='$sorteo' 
        and cup_estado='Activo' and cup_eliminado='No'";

        $conec = new ADO();
        $conec->ejecutar($sql);
        $count = mysql_affected_rows();

        return $count;
    }

    public static function imprimir_cupon($cup_id) {
        $cupon = FUNCIONES::objeto_bd_sql("select * from cupon where cup_id='$cup_id'");
        ?>
        <div id="cupon2" style='position: relative; width:213px; height:148px;'>
            <img src="imagenes/cupon2.jpg" style="z-index: 1; width:213px; height:148px;" />
            <div style="position: absolute; left: 107px; top: 44px; font-size: 16px; border: 1px red dashed; color: red; z-index: 10">
                <!--NUMERO DE CUPON-->
                <?php echo $cupon->cup_numero;?>
            </div>
        </div>
        <?php
    }
    
    public static function imprimir_cupon_($cup_id) {
        $cupon = FUNCIONES::objeto_bd_sql("select * from cupon where cup_id='$cup_id'");
        ?>
        <div id="cupon2" style='position: relative; width:213px; height:148px; background-size: 100% 100%; background-image: url("imagenes/cupon2.jpg")!important;'>
            <div style="position: absolute; left: 107px; top: 44px; font-size: 16px; border: 1px red dashed; color: red;">
                <!--NUMERO DE CUPON-->
                <?php echo $cupon->cup_numero;?>
            </div>
        </div>
        <?php
    }

    public static function imprimir_cupon_anfora($cup_id) {
        
        $cupon = FUNCIONES::objeto_bd_sql("select * from cupon where cup_id='$cup_id'");
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$cupon->cup_ven_id'");
        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_id='$venta->ven_int_id'");
        $lote = FUNCIONES::objeto_bd_sql("select * from lote inner join manzano on (lot_man_id=man_id)
            inner join urbanizacion on (man_urb_id=urb_id)
            where lot_id='$venta->ven_lot_id'");
        ?>
        <div id="cupon1" style='position: relative; width:213px; height:116px;'>
            <img src="imagenes/cupon1.jpg" style="z-index: 1; width:213px; height:116px;" />
            <div style="position: absolute; left: 107px; top: 30px; font-size: 16px; border: 1px red dashed; color: red;z-index: 10">
                <!--NUMERO DE CUPON-->
                <?php echo $cupon->cup_numero;?>
            </div>

            <div style="position: absolute; left: 34px; top: 60px; font-size: 9px;z-index: 10">
                <!--NOMBRE COMPLETO-->
                <?php echo strtoupper($interno->int_nombre . " " . $interno->int_apellido);?>
            </div>
            <div style="position: absolute; left: 34px; top: 71px; font-size: 9px;z-index: 10">
                <!--NUMERO DE CELULAR-->
                <?php echo $interno->int_celular;?>
            </div>

            <div style="position: absolute; left: 117px; top: 71px; font-size: 9px;z-index: 10">
                <!--CARNET DE IDENTIDAD-->
                <?php echo $interno->int_ci . " " . $interno->int_ci_exp;?>
            </div>

            <div style="position: absolute; left: 34px; top: 82px; font-size: 9px;z-index: 10">
                <!--CIUDAD-->
                <?php 
                $datos_lug = explode(',', $interno->int_ubicacion_res);
                echo strtoupper($datos_lug[2]);
                ?>
            </div>

            <div style="position: absolute; left: 34px; top: 93px; font-size: 9px;z-index: 10">
                <!--LOTE-->
                <?php echo $lote->lot_nro;?>
            </div>

            <div style="position: absolute; left: 134px; top: 93px; font-size: 9px;z-index: 10">
                <!--MANZANO URBANIZACION-->
                <?php echo $lote->man_nro;?>
            </div>
            
            <div style="position: absolute; left: 34px; top: 104px; font-size: 9px;z-index: 10">
                <!--MANZANO URBANIZACION-->
                <?php echo strtoupper($lote->urb_nombre);?>
            </div>
        </div>
        <?php
    }
    
    public static function imprimir_cupon_anfora_($cup_id) {
        
        $cupon = FUNCIONES::objeto_bd_sql("select * from cupon where cup_id='$cup_id'");
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$cupon->cup_ven_id'");
        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_id='$venta->ven_int_id'");
        $lote = FUNCIONES::objeto_bd_sql("select * from lote inner join manzano on (lot_man_id=man_id)
            inner join urbanizacion on (man_urb_id=urb_id)
            where lot_id='$venta->ven_lot_id'");
        ?>
        <div id="cupon1" style='position: relative; width:213px; height:116px; background-size: 100% 100%; background-image: url("imagenes/cupon1.jpg");'>
            
            <div style="position: absolute; left: 107px; top: 30px; font-size: 16px; border: 1px red dashed; color: red;">
                <!--NUMERO DE CUPON-->
                <?php echo $cupon->cup_numero;?>
            </div>

            <div style="position: absolute; left: 34px; top: 60px; font-size: 9px;">
                <!--NOMBRE COMPLETO-->
                <?php echo strtoupper($interno->int_nombre . " " . $interno->int_apellido);?>
            </div>
            <div style="position: absolute; left: 34px; top: 71px; font-size: 9px;">
                <!--NUMERO DE CELULAR-->
                <?php echo $interno->int_celular;?>
            </div>

            <div style="position: absolute; left: 117px; top: 71px; font-size: 9px;">
                <!--CARNET DE IDENTIDAD-->
                <?php echo $interno->int_ci . " " . $interno->int_ci_exp;?>
            </div>

            <div style="position: absolute; left: 34px; top: 82px; font-size: 9px;">
                <!--CIUDAD-->
                <?php 
                $datos_lug = explode(',', $interno->int_ubicacion_res);
                echo strtoupper($datos_lug[2]);
                ?>
            </div>

            <div style="position: absolute; left: 34px; top: 93px; font-size: 9px;">
                <!--LOTE-->
                <?php echo $lote->lot_nro;?>
            </div>

            <div style="position: absolute; left: 134px; top: 93px; font-size: 9px;">
                <!--MANZANO URBANIZACION-->
                <?php echo $lote->man_nro;?>
            </div>
            
            <div style="position: absolute; left: 34px; top: 104px; font-size: 9px;">
                <!--MANZANO URBANIZACION-->
                <?php echo strtoupper($lote->urb_nombre);?>
            </div>
        </div>
        <?php
    }

}
?>