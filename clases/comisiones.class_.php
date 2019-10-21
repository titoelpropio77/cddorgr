<?php

class COMISION {

    private static $tabla = 'comision';

    function COMISION() {
        
    }

    public static function set_tabla($tabla) {
        self::$tabla = $tabla;
    }

    public static function get_tabla() {
        return self::$tabla;
    }

    public static function insertar_comision($data) {
        $data = (object) $data;
//        $obj = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$data->venta'");
        
        $ven_id = 0;
        if ($data->venta) {
            $venta = $data->venta;
            $ven_id = $venta->ven_id;
        }
        
        $estado = ($data->estado) ? $data->estado : 'Pendiente';
        $fecha_imp = ($data->fecha_imp) ? $data->fecha_imp : NULL;
        
        $com_pagado = 0;
        if ($estado == 'Pagado') {
            $com_pagado = $data->monto;
        }

        $sql = "insert into " . COMISION::get_tabla() . "(com_ven_id,com_vdo_id,
                com_monto,com_moneda,com_pagado,
                com_estado,com_fecha_cre,
                com_usu_id,com_observacion,
                com_tipo,com_porcentaje,com_pdo_id,
                com_fecha_mod,com_usu_mod,com_fecha_imp,com_marca_tmp)
                values('{$ven_id}','$data->vendedor',
                '$data->monto','$data->moneda','$com_pagado',
                '$estado','$data->fecha',
                '$data->usuario','$data->glosa',
                '$data->tipo','$data->porcentaje','$data->periodo',
                '$data->fecha_mod','$data->usuario_mod','$fecha_imp','$data->marca_tmp');";
//        FUNCIONES::eco($sql);

        FUNCIONES::bd_query($sql);

        //REFLEJO CONTABLE
        return false;

        $com_id = mysql_insert_id();
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        $datos = array(
            'venta' => $data->venta,
            'vendedor' => $data->vendedor,
            'urb' => $urb,
            'moneda' => $data->moneda,
            'fecha' => $data->fecha,
            'com_id' => $com_id,
            'com_monto' => $data->monto,
            'glosa' => "Provision de $data->glosa"
        );
        COMISION::provisionar_comision($datos);
    }

    public static function actualizar_comisiones($data) {
                        
        FUNCIONES::eco("<p style='color:red'>actualizar_comisiones...</p>");
        $data = (object) $data;
        
//        $filtro = ($data->vendedor > 0) ? " and com_vdo_id=$data->vendedor" :"";
        $filtro = "";
        
        $sql_anul = "update comision set com_estado='Anulado' where com_pdo_id=$data->periodo 
            and com_estado='Pendiente' and com_tipo in ('BIR','BVI','BRA','FED') $filtro";
        FUNCIONES::bd_query($sql_anul);
        FUNCIONES::bd_query("delete from comision where com_pdo_id=$data->periodo 
            and com_estado='Anulado' and com_tipo in ('BIR','BVI','BRA','FED') $filtro");

//        $next_id = (FUNCIONES::atributo_bd_sql("select max(com_id)as campo from comision") * 1) + 1;
//        FUNCIONES::bd_query("ALTER TABLE `comision` AUTO_INCREMENT=$next_id");
    }

    public static function insertar_comisiones($data) {

        $data = (object) $data;

        if ($data->vendedor <= 0) {
            return;
        }

        FUNCIONES::eco("-- entrando a comisionar a todo mundo");
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$data->venta");
        $monto = $venta->ven_bono_inicial;

        $sql_vdo = "select vdo.*,inte.int_id from vendedor vdo,interno inte 
                    where vdo.vdo_id='$data->vendedor'
                    and vdo.vdo_int_id=inte.int_id";
        $vendedor = FUNCIONES::objeto_bd_sql($sql_vdo);

        $vendedor_padre = FUNCIONES::objeto_bd_sql("select vdo.*,inte.int_id from vendedor vdo,interno inte 
                    where vdo.vdo_id='$vendedor->vdo_vendedor_id'
                    and vdo.vdo_int_id=inte.int_id");

        $_comisiones = json_decode($venta->ven_porc_comisiones);
        $comisiones = $_comisiones->array;

        $total_comisionado = 0;
        $saldo = $monto;
//        $monto_red = $saldo_red;

        if ($comisiones != '') {
            $porc = $comisiones[0];
            $monto_comision = ($monto * $porc) / 100;

            if ($monto_comision <= $saldo) {

                $datos = array('venta' => $venta,
                    'vendedor' => $vendedor->vdo_id,
                    'monto' => $monto_comision,
                    'moneda' => $data->moneda,
                    'usuario' => $data->usuario,
                    'usuario_mod' => $data->usuario_mod,
                    'glosa' => 'Bono de Inicio Rapido por la Venta Nro. ' . $data->venta,
                    'fecha' => $data->fecha,
                    'fecha_mod' => $data->fecha_mod,
                    'tipo' => 'BIR',
                    'periodo' => $data->periodo,
                    'porcentaje' => $porc);
                COMISION::insertar_comision($datos);
                $saldo -= $monto_comision;
                $total_comisionado += $monto_comision;
            }

            $monto = $monto - $monto_comision;
            for ($i = 1; $i < count($comisiones); $i++) {

                if ($vendedor_padre) {

                    $rango_actual_padre = MLM::rango_actual($vendedor_padre->vdo_id);
                    $generacion = MLM::hijo_generacion($vendedor_padre->vdo_id, $vendedor->vdo_id);

                    if ($rango_actual_padre && ($generacion <= $rango_actual_padre->ran_gen_com_ventas)) {

                        if (MLM::esta_al_dia($vendedor_padre->int_id, $data->fecha) &&
                                MLM::esta_activo($vendedor_padre, $data->fecha)) {
//                        if (MLM::esta_al_dia($vendedor_padre->int_id, $data->fecha)) {
//                            echo "esta al dia";
                            $porc = $comisiones[$i];
                            $monto_comision = ($monto * $porc) / 100;

                            if ($monto_comision <= $saldo) {

                                $datos = array('venta' => $venta,
                                    'vendedor' => $vendedor_padre->vdo_id,
                                    'monto' => $monto_comision,
                                    'moneda' => $data->moneda,
                                    'usuario' => $data->usuario,
                                    'usuario_mod' => $data->usuario_mod,
                                    'glosa' => 'Bono de Venta Indirecta Residual por la Venta Nro. ' . $data->venta,
                                    'fecha' => $data->fecha,
                                    'fecha_mod' => $data->fecha_mod,
                                    'tipo' => 'BVI',
                                    'periodo' => $data->periodo,
                                    'porcentaje' => $porc / 2);
                                COMISION::insertar_comision($datos);

                                $saldo -= $monto_comision;
                                $total_comisionado += $monto_comision;
                            }
                        }
                    }

                    $vendedor_padre = FUNCIONES::objeto_bd_sql("select vdo.*,inte.int_id from vendedor vdo,interno inte 
                    where vdo.vdo_id='$vendedor_padre->vdo_vendedor_id'
                    and vdo.vdo_int_id=inte.int_id");
                } else {
                    break;
                }
            }
        }

        if (COMISION::$tabla == 'comision') {
            $saldo_red = $venta->ven_saldo_red - $total_comisionado;
            $sql_upd_saldo_red = "update venta set ven_saldo_red='$saldo_red' where ven_id='$data->venta'";
            FUNCIONES::bd_query($sql_upd_saldo_red);
            FUNCIONES::eco($sql_upd_saldo_red);
        }
    }

    public static function insertar_comisiones_cobro($data) {

        $data = (object) $data;
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$data->venta'");
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$venta->ven_urb_id");

        echo "<p>EJECUTANDO insertar_comisiones_cobro($data->venta - $data->vendedor)...</p>";

        $monto_vendedor = $venta->ven_bono_bra / 7;
        $plazo = $urb->urb_nro_cuotas_multinivel;
        $monto_cuota = $monto_vendedor / $plazo;

        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$data->vendedor'");

        if ($vendedor) {
            $sql_insert = "insert into comision_cobro(comcob_ven_id,comcob_vdo_id,
                            comcob_monto_total,comcob_pagado,
                            comcob_saldo,comcob_monto_cuota)
                            values('$venta->ven_id','$vendedor->vdo_id',
                            '$monto_vendedor','0',
                            '$monto_vendedor','$monto_cuota');";
            FUNCIONES::bd_query($sql_insert);
            FUNCIONES::eco($sql_insert);
        }
    }

    public static function comisionar_cuotas($data) {
        require_once('clases/comisiones.class.php');
        $data = (object) $data;

        if ($data->vendedor <= 0) {
            return;
        }

        FUNCIONES::eco("-- entrando a comisionar por cuotas a todo mundo");

        $sql_vdo = "select vdo.*,inte.int_id from vendedor vdo,interno inte 
                    where vdo.vdo_id='$data->vendedor'
                    and vdo.vdo_int_id=inte.int_id";
        $vendedor = FUNCIONES::objeto_bd_sql($sql_vdo);

        $vendedor_padre = FUNCIONES::objeto_bd_sql("select vdo.*,inte.int_id from vendedor vdo,interno inte 
                    where vdo.vdo_id='$vendedor->vdo_vendedor_id'
                    and vdo.vdo_int_id=inte.int_id");
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$data->venta");
        $total_comisionado = 0;

        if (MLM::esta_al_dia($vendedor->int_id, $data->fecha) &&
                MLM::esta_activo($vendedor, $data->fecha)) {
//        if (MLM::esta_al_dia($vendedor->int_id, $data->fecha)) {

            $comision_cuota = FUNCIONES::objeto_bd_sql("select * from comision_cobro 
            where comcob_ven_id=$data->venta 
                and comcob_vdo_id=$data->vendedor");
            $monto_comision = $comision_cuota->comcob_monto_cuota;
            $total_comisionado += $monto_comision;

            $porc = $monto_comision * 100 / $venta->ven_bono_bra;

//            if ($monto_comision <= $saldo_red) {
            if (TRUE) {

                $datos = array('venta' => $venta,
                    'vendedor' => $vendedor->vdo_id,
                    'monto' => $monto_comision,
                    'moneda' => $data->moneda,
                    'usuario' => $data->usuario,
                    'usuario_mod' => $data->usuario_mod,
                    'glosa' => 'Bono Residual Abierto por la Venta Nro. ' . $data->venta,
                    'fecha' => $data->fecha,
                    'fecha_mod' => $data->fecha_mod,
                    'tipo' => 'BRA',
                    'periodo' => $data->periodo,
                    'porcentaje' => $porc);
                COMISION::insertar_comision($datos);

                if (COMISION::$tabla == 'comision') {
                    $saldo = $comision_cuota->comcob_saldo - $monto_comision;
                    $pagado = $comision_cuota->comcob_pagado + $monto_comision;
                    $sql_upd = "update comision_cobro set comcob_saldo='$saldo',
                                comcob_pagado='$pagado'
                                    where comcob_ven_id='$data->venta' 
                                        and comcob_vdo_id='$vendedor->vdo_id'";
                    FUNCIONES::bd_query($sql_upd);
                    FUNCIONES::eco($sql_upd);
                }
            }
        }

        while ($vendedor_padre) {

            if ($vendedor_padre) {

                $rango_actual_padre = MLM::rango_actual($vendedor_padre->vdo_id);
                $generacion = MLM::hijo_generacion($vendedor_padre->vdo_id, $vendedor->vdo_id);

                if ($rango_actual_padre && ($generacion <= $rango_actual_padre->ran_gen_com_cobros)) {

                    if (MLM::esta_al_dia($vendedor_padre->int_id, $data->fecha) &&
                            MLM::esta_activo($vendedor_padre, $data->fecha)) {
//                    if (MLM::esta_al_dia($vendedor_padre->int_id, $data->fecha)) {

                        $comision_cuota = FUNCIONES::objeto_bd_sql("select * from comision_cobro 
                            where comcob_ven_id='$data->venta'
                                and comcob_vdo_id='$vendedor_padre->vdo_id'");

                        $monto_comision = $comision_cuota->comcob_monto_cuota;
                        $total_comisionado += $monto_comision;
                        $porc = $monto_comision * 100 / $venta->ven_bono_bra;

//                        if ($monto_comision <= $saldo_red) {
                        if (TRUE) {

                            $datos = array('venta' => $venta,
                                'vendedor' => $vendedor_padre->vdo_id,
                                'monto' => $monto_comision,
                                'moneda' => $data->moneda,
                                'usuario' => $data->usuario,
                                'usuario_mod' => $data->usuario_mod,
                                'glosa' => 'Bono Residual Abierto por la Venta Nro. ' . $data->venta,
                                'fecha' => $data->fecha,
                                'fecha_mod' => $data->fecha_mod,
                                'tipo' => 'BRA',
                                'periodo' => $data->periodo,
                                'porcentaje' => $porc);
                            COMISION::insertar_comision($datos);

                            if (COMISION::$tabla == 'comision') {
                                $saldo = $comision_cuota->comcob_saldo - $monto_comision;
                                $pagado = $comision_cuota->comcob_pagado + $monto_comision;
                                $sql_upd = "update comision_cobro set comcob_saldo='$saldo',
                                    comcob_pagado='$pagado'
                                        where comcob_ven_id='$data->venta' 
                                            and comcob_vdo_id='$vendedor_padre->vdo_id'";
                                FUNCIONES::bd_query($sql_upd);
                                FUNCIONES::eco($sql_upd);
                            }
                        }
                    }
                }

                $vendedor_padre = FUNCIONES::objeto_bd_sql("select vdo.*,inte.int_id from vendedor vdo,interno inte 
                    where vdo.vdo_id='$vendedor_padre->vdo_vendedor_id'
                    and vdo.vdo_int_id=inte.int_id");
            } else {
                break;
            }
        }
        if (COMISION::$tabla == 'comision') {
            $saldo_red = $venta->ven_saldo_red - $total_comisionado;
            FUNCIONES::bd_query("update venta set ven_saldo_red=$saldo_red where ven_id=$venta->ven_id");
        }
    }

    public static function provisionar_comision($datos) {
        $datos = (object) $datos;
        $ges_id = $_SESSION['ges_id'];

        $glosa = $datos->glosa;
        $vendedor = FUNCIONES::objeto_bd_sql("select vdo.vdo_can_id,concat(inte.int_nombre,' ',inte.int_apellido) as referido 
            from vendedor vdo, interno inte where vdo_int_id=int_id and vdo_id='$datos->vendedor'");

        $params = array(
            'glosa' => $glosa,
            'ges_id' => $ges_id,
            'urb' => $datos->urb,
            'moneda' => $datos->moneda,
            'fecha' => $datos->fecha,
            'interno' => $vendedor->referido,
            'tabla_id' => $datos->com_id,
            'monto' => $datos->com_monto,
            'vdo_can_id' => $vendedor->vdo_can_id,
        );
        include_once 'clases/registrar_comprobantes.class.php';
        include_once 'clases/modelo_comprobantes.class.php';
        $comprobante = MODELO_COMPROBANTE::comision($params);
        COMPROBANTES::registrar_comprobante($comprobante);
    }

}

?>
