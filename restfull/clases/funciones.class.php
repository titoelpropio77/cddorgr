<?php

class FUNCIONES {

    public static function objeto_bd($tabla, $col, $valor) {
        $conec = new QUERY();
        $sql = "select * from $tabla where $col='$valor'";
        $conec->consulta($sql);
        return $conec->get_objeto();
    }

    public static function objeto_bd_sql($sql) {
        $conec = new QUERY();
        $conec->consulta($sql);
        if ($conec->get_num_registros() > 0) {
            return $conec->get_objeto();
        } else {
            return null;
        }
    }

    public static function atributo_bd($tabla, $condicion, $campo) {
        $conec = new QUERY();
        $sql = "select $campo as campo from $tabla where $condicion";
        //echo $sql."<br>";
        $conec->consulta($sql);
        if ($conec->get_num_registros() > 0) {
            $objeto = $conec->get_objeto();
            return $objeto->campo;
        } else {
            return '';
        }
    }

    public static function parametro($nombre, $ges_id = 0) {
        if ($ges_id == 0) {
            $ges_id = $_SESSION['ges_id'];
        }
        $conec = new QUERY();
        $sql = "select conf_valor as campo from con_configuracion where conf_nombre='$nombre' and conf_ges_id='$ges_id'";
//        echo $sql;
        $conec->consulta($sql);
        if ($conec->get_num_registros() > 0) {
            $objeto = $conec->get_objeto();
            return $objeto->campo;
        } else {
            return '';
        }
    }

    public static function atributo_bd_sql($sql) {
        $conec = new QUERY();
//        echo $sql."<br>";
        $conec->consulta($sql);
        if ($conec->get_num_registros() > 0) {
            $objeto = $conec->get_objeto();
            return $objeto->campo;
        } else {
            return '';
        }
    }

    public static function objetos_bd($tabla, $condicion) {
        $conec = new QUERY();
        $sql = "select * from $tabla where $condicion";
//        echo $sql.'<br>';
        $conec->consulta($sql);
        return $conec;
    }

    public static function objetos_bd_sql($sql) {
        $conec = new QUERY();
        $conec->consulta($sql);
        return $conec;
    }

    public static function numero_registros($tabla, $condicion) {
        $conec = new QUERY();
        $sql = "select count(*) as total from $tabla where $condicion";
        $conec->consulta($sql);
        $objeto = $conec->get_objeto();
        return $objeto->total * 1;
    }

    public static function invertir_fecha($fecha, $separador = '-') {
        $fecha_array = explode($separador, $fecha);
        return $fecha_array[2] . $separador . $fecha_array[1] . $separador . $fecha_array[0];
    }

    public static function obtener_per_tc_nro($idper, $idtc) {
        $conec = new QUERY();
        $sql = "select coc_nro from con_contador_comprobante where coc_pdo_id=$idper and coc_tco_id=$idtc";
        $conec->consulta($sql);
        $nro = $conec->get_objeto();
        return $nro->coc_nro;
    }

    public static function actualizar_per_tc_nro($idper, $idtc, $nro) {
        $conec = new QUERY();
        $sql = "update con_contador_comprobante set coc_nro=$nro where coc_pdo_id=$idper and coc_tco_id=$idtc";
        $conec->consulta($sql);
    }

    public static function bd_query($sql) {
        $conec = new QUERY();
        $conec->consulta($sql);
        return $conec;
    }

    public static function comparar_fechas($fecha, $fecha_comparar) {
        if ($fecha_comparar == null) {
            $fecha_comparar = date("Y-m-d");
        }

        $fecha = strtotime($fecha);
        $fecha_comparar = strtotime($fecha_comparar);

        if ($fecha == $fecha_comparar) {
            return 0;
        } else if ($fecha < $fecha_comparar) {
            return -1;
        } else if ($fecha > $fecha_comparar) {
            return 1;
        }

        return false;
    }

    public static function get_fecha_latina($fecha) {
        preg_match('/' . "([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})" . '/i', $fecha, $mifecha);
        $lafecha = $mifecha[3] . "/" . $mifecha[2] . "/" . $mifecha[1];
        return $lafecha;
    }

    public static function get_fecha_mysql($fecha) {
        preg_match('"([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})"', $fecha, $mifecha);
        $lafecha = $mifecha[3] . "-" . $mifecha[2] . "-" . $mifecha[1];
        return $lafecha;
    }

    function combo_data($sql, $data, $valor) {
        $conec = new QUERY();
        $conec->consulta($sql);
        $num = $conec->get_num_registros();
        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            $cad = "";
            if ($objeto->id == $valor)
                $cad = ' selected="selected" ';
            echo '<option value="' . $objeto->id . '"' . $cad . ' data_' . $data . '="' . $objeto->{$data} . '">' . $objeto->nombre . '</option>';
            $conec->mover_siguiente();
        }
    }

    public static function get_cuenta($ges_id, $codigo) {
        $cuenta = FUNCIONES::atributo_bd("con_cuenta", "cue_ges_id=$ges_id and cue_codigo='$codigo'", "cue_id");
        return $cuenta;
    }

    public static function get_cuenta_ca($ges_id, $codigo) {
        $can_id = FUNCIONES::atributo_bd("con_cuenta_ca", "can_ges_id=$ges_id and can_codigo='$codigo'", "can_id");
        return $can_id;
    }

    public static function get_cuenta_cf($ges_id, $codigo) {
        $cfl_id = FUNCIONES::atributo_bd("con_cuenta_cf", "cfl_ges_id=$ges_id and cfl_codigo='$codigo'", "cfl_id");
        return $cfl_id;
    }

    public static function get_cuenta_cc($ges_id, $codigo) {
        $coc_id = FUNCIONES::atributo_bd("con_cuenta_cc", "cco_ges_id=$ges_id and cco_codigo='$codigo'", "cco_id");
        return $coc_id;
    }

    public static function obtener_periodo($fecha) {
        $periodo = FUNCIONES::atributo_bd("con_periodo", "pdo_fecha_inicio<='$fecha' and pdo_fecha_fin>='$fecha'", 'pdo_id');
        return $periodo;
    }

    public static function registrar_comprobante($comprobante) {
        $conec = new QUERY();
        switch ($comprobante->tipo) {
            case "Ingreso":
                $tipo_comprobante = 1;
                break;
            case "Egreso":
                $tipo_comprobante = 2;
                break;
            case "Diario":
                $tipo_comprobante = 3;
                break;
            default:
                break;
        }
        $cmp_tco_id = $tipo_comprobante;
        $cmp_mon_id = $comprobante->mon_id;
        $cmp_nro_documento = $comprobante->nro_documento;
        $cmp_fecha = $comprobante->fecha;
        $cmp_ges_id = $comprobante->ges_id;
        $cmp_peri_id = $comprobante->peri_id;

        $cmp_forma_pago = $comprobante->forma_pago;
        if ($cmp_forma_pago == 'Efectivo' || $cmp_forma_pago == '') {
            $cmp_ban_id = 0;
            $cmp_ban_char = '';
            $cmp_ban_nro = '';
        } elseif ($cmp_forma_pago == 'Cheque') {
            if ($tipo_comprobante == 1) {
                $cmp_ban_id = 0;
                $cmp_ban_char = $comprobante->ban_char;
                $cmp_ban_nro = $comprobante->ban_nro;
            } elseif ($tipo_comprobante == 2) {
                $cmp_ban_id = $comprobante->ban_id;
                $cmp_ban_char = '';
                $cmp_ban_nro = $comprobante->ban_nro;
            }
        } elseif ($cmp_forma_pago == 'Deposito') {
            if ($tipo_comprobante == 1) {
                $cmp_ban_id = $comprobante->ban_id;
                $cmp_ban_char = '';
                $cmp_ban_nro = $comprobante->ban_nro;
            } elseif ($tipo_comprobante == 2) {
                $cmp_ban_id = 0;
                $cmp_ban_char = $comprobante->ban_char;
                $cmp_ban_nro = $comprobante->ban_nro;
            }
        } elseif ($cmp_forma_pago == 'Transferencia') {
            $cmp_ban_id = $comprobante->ban_id;
            $cmp_ban_char = $comprobante->ban_char;
            $cmp_ban_nro = $comprobante->ban_nro;
        }

        $cmp_glosa = $comprobante->glosa;
        $cmp_referido = $comprobante->referido;
        $cmp_usu_id = 2230; // Sintesis .;
        $cmp_revisado = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='revisado'", "conf_valor");
        $cmp_aprobado = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='aprobado'", "conf_valor");
        $cmp_estado = 'Activo';
        $cmp_fecha_cre = date('Y-m-d');
        $cmp_fecha_modi = '0000-00-00';
        $cmp_tabla = $comprobante->tabla;
        $cmp_tabla_id = $comprobante->tabla_id;
        $cmp_eliminado = 'No';
        //echo $cmp_peri_id." - ".$cmp_tco_id." - ".$cmp_fecha;
        $cmp_nro = FUNCIONES::obtener_per_tc_nro($cmp_peri_id, $cmp_tco_id) + 1;

        $insert = "insert into con_comprobante (cmp_tco_id, cmp_mon_id, cmp_nro, cmp_nro_documento, cmp_fecha, cmp_ges_id, cmp_peri_id,cmp_forma_pago, cmp_ban_id, cmp_ban_char, cmp_ban_nro, cmp_glosa, cmp_referido,cmp_usu_id, cmp_revisado, cmp_aprobado, cmp_estado, cmp_fecha_cre, cmp_fecha_modi, cmp_tabla , cmp_tabla_id,cmp_eliminado)
                 values($cmp_tco_id, $cmp_mon_id, $cmp_nro, '$cmp_nro_documento', '$cmp_fecha', $cmp_ges_id, $cmp_peri_id,'$cmp_forma_pago',$cmp_ban_id,'$cmp_ban_char','$cmp_ban_nro','$cmp_glosa','$cmp_referido','$cmp_usu_id', '$cmp_revisado', '$cmp_aprobado','$cmp_estado','$cmp_fecha_cre','$cmp_fecha_modi','$cmp_tabla',$cmp_tabla_id,'$cmp_eliminado');";
//        echo $insert.';<br>';
        $conec->consulta($insert, false);
        $cmp_id = mysql_insert_id();
        FUNCIONES::actualizar_per_tc_nro($cmp_peri_id, $cmp_tco_id, $cmp_nro);

        /** INSERTAR LOS DETALLES* */
        $cmp_detalles = $comprobante->detalles;

        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp_fecha'");

        if ($cambios->get_num_registros() == 0) {
            $cambios = FUNCIONES::objetos_bd_sql("select * from con_tipo_cambio where tca_fecha in (select max(tca_fecha) as campo from con_tipo_cambio)");
        }

        $cde_cmp_id = $cmp_id;
        $cde_eliminado = "No";

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->mover_siguiente();
        }
        $index = 1;
        foreach ($cmp_detalles as $cmp_detalle) {
            $detalle = (object) $cmp_detalle;
            $cde_secuencia = $index;
            $cde_can_id = (isset($detalle->ca) && $detalle->ca != "") ? $detalle->ca : 0;
            $cde_cco_id = (isset($detalle->cc) && $detalle->cc != "") ? $detalle->cc : 0;
            $cde_cfl_id = (isset($detalle->cf) && $detalle->cf != "") ? $detalle->cf : 0;
            $cde_cue_id = (isset($detalle->cuen) && $detalle->cuen != "") ? $detalle->cuen : 0;
            $cde_glosa = $detalle->glosa;
            $debe = $detalle->debe;
            $haber = $detalle->haber;
            $int_id = 0;
            $fpago = 'Efectivo';
            $descripcion = '';
            if ($detalle->int_id)
                $int_id = $detalle->int_id;
            if ($detalle->fpago)
                $fpago = $detalle->fpago;
            if ($detalle->descripcion)
                $descripcion = $detalle->descripcion;
            $valor = 0;
            if ($debe != "") {
                $valor = floatval($debe) * 1;
            }
            if ($haber != "") {
                $valor = floatval($haber) * (-1);
            }
            $val_bol = $valor * $tc;
            $sql = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_descripcion)
                values($cde_cmp_id, 1, $cde_secuencia, $cde_can_id, $cde_cco_id, $cde_cfl_id, $cde_cue_id, $val_bol,'$cde_glosa', '$cde_eliminado','$int_id','$fpago','$descripcion')";
//            echo $sql.";<br>";
            $conec->consulta($sql);
            $cambios->reset();
            for ($i = 0; $i < $cambios->get_num_registros(); $i++) {
                $cambio = $cambios->get_objeto();
                $cde_mon_id = $cambio->tca_mon_id;
                $cde_valor = $val_bol / $cambio->tca_valor;
                $sql = "insert into con_comprobante_detalle (cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_descripcion)
                    values($cde_cmp_id, $cde_mon_id, $cde_secuencia, $cde_can_id, $cde_cco_id, $cde_cfl_id, $cde_cue_id, $cde_valor,'$cde_glosa', '$cde_eliminado','$int_id','$fpago','$descripcion')";
//                echo $sql.";<br>";
//                echo $cde_mon_id." - ".$sql."<br>";
                $conec->consulta($sql);
                $cambios->mover_siguiente();
            }
            $index++;
        }
        return $cmp_id;
    }

    public static function anular_comprobante($tabla, $id) {
        $sql = "select p.pdo_estado as estado from con_comprobante c, con_periodo p 
            where cmp_peri_id=p.pdo_id and c.cmp_tabla='$tabla' and c.cmp_tabla_id='$id';";
//        echo $sql;
        $periodo = FUNCIONES::objeto_bd_sql($sql);
//        _PRINT::pre($periodo);
        if ($periodo != null) {
            if ($periodo->estado == 'Abierto') {
                $conec = new QUERY();
                $sql = "update con_comprobante SET cmp_eliminado = 'Si', cmp_fecha_modi = '" . date('Y-m-d') . "' Where cmp_tabla ='" . $tabla . "' and cmp_tabla_id ='" . $id . "'";
                $conec->consulta($sql);
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public static function sumar_dias($dias, $fecha) {
        $fechaComparacion = strtotime($fecha);
        $calculo = strtotime("$dias day", $fechaComparacion);
        return date("Y-m-d", $calculo);
    }

    public static function diferencia_dias($fecha1, $fecha2) {
        $segundos = strtotime($fecha2) - strtotime($fecha1);
        $diferencia_dias = intval($segundos / 60 / 60 / 24);
        return $diferencia_dias;
    }
    
    public static function actualizar_foto($int_id, $foto){
        FUNCIONES::bd_query("update interno set int_foto='$foto' where int_id='$int_id'");
    }

}
