<?PHP

class FUNCIONES {

    public static function eco($texto) {
        if (_ENTORNO == 'DEV') {
            echo "<br/>$texto<br/>";
        }
    }

    function combo_lote($sql, $valor) {
        $conec = new ADO();
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            $cad = "";
            if ($objeto->id == $valor)
                $cad = ' selected="selected" ';

            echo '<option style="background-color:' . $objeto->zon_color . ';" value="' . $objeto->id . '"' . $cad . '>' . $objeto->nombre . '</option>';
            $conec->siguiente();
        }
    }

    function combo($sql, $valor) {
        $conec = new ADO();

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();

            $cad = "";

            if ($objeto->id == $valor)
                $cad = ' selected="selected" ';

            echo '<option value="' . $objeto->id . '"' . $cad . '>' . $objeto->nombre . '</option>';

            $conec->siguiente();
        }
    }

    function combo_comprobantes($sql, $valor) {
        $conec = new ADO();
        $conec_aux = new ADO();

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();

            $cad = "";

            if ($objeto->id == $valor)
                $cad = ' selected="selected" ';

            // $sql="SELECT 
            // ven_monto,ven_id,ven_fecha,ven_tipo_cambio,ven_tipo,ven_moneda,ven_estado,int_nombre,int_apellido,pro_nombre,vde_cantidad,vde_precio,vde_moneda,vde_pro_id 
            // FROM 
            // venta inner join interno on (ven_int_id='".$objeto->id."' and ven_estado='Pendiente' and ven_int_id=int_id)
            // inner join venta_detalle on (ven_id=vde_ven_id)
            // inner join producto on (vde_pro_id=pro_id)
            // ";
            // $conec_aux->ejecutar($sql);
            // $num_aux=$conec_aux->get_num_registros();
            // if ($num_aux > 0)
            // {
            // $nombre = $objeto->nombre."<font color=#ff0000> (Debe Productos) </font>";
            // $clase = "debe";
            // }
            // else
            // {
            $nombre = $objeto->nombre;
            $clase = "nodebe";
            // }

            echo '<option class="' . $clase . '" value="' . $objeto->id . '"' . $cad . '>' . $nombre . '</option>';

            $conec->siguiente();
        }
    }

    function combo2($sql, $valor) {
        $conec = new ADO();

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();



        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();


            if ($i == 0) {
                echo '<optgroup label="' . $this->nombre_cuenta($objeto->cue_padre_id) . '">';
                $fam = $objeto->cue_padre_id;
            }

            if ($fam <> $objeto->cue_padre_id) {
                echo '</optgroup>';
                echo '<optgroup label="' . $this->nombre_cuenta($objeto->cue_padre_id) . '">';
                $fam = $objeto->cue_padre_id;
            }

            $cad = "";
            if ($objeto->id == $valor)
                $cad = ' selected="selected" ';
            echo '<option value="' . $objeto->id . '"' . $cad . '>' . $objeto->nombre . '</option>';


            if ($i == $nun - 1) {
                echo '</optgroup>';
            }


            $conec->siguiente();
        }
    }

    function nombre($per_id) {
        $conec = new ADO();

        $sql = "select int_nombre,int_apellido from interno where int_id='$per_id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_apellido . ' ' . $objeto->int_nombre;
    }

    function nombre_cuenta($id) {
        $conec = new ADO();

        $sql = "select cue_descripcion from cuenta where cue_id='$id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->cue_descripcion;
    }

    public static function posible_concretar_venta($res_id, $fecha) {

        $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id='$res_id'");
        $monto_referencial = FUNCIONES::atributo_bd_sql("select urb_monto_anticipo as campo from urbanizacion where urb_id='$reserva->res_urb_id'");

        $incr = FUNCIONES::objeto_bd_sql("select urb_porc_incremento from urbanizacion where urb_id='$reserva->res_urb_id'");
        $urb_porc_incremento = $incr->urb_porc_incremento; // EN PORCENTAJE;

        $lote = FUNCIONES::objeto_bd_sql("select lot_superficie,zon_precio,zon_cuota_inicial,zon_moneda,lot_tipo 
		from lote inner join zona on (lot_zon_id=zon_id) 
		inner join uv on (lot_uv_id=uv_id) 
		where lot_id='" . $reserva->res_lot_id . "'");
        $superficie = $lote->lot_superficie; // SUPERFICIE DEL LOTE;
        $zon_precio = $lote->zon_precio; // PRECIO ZONA;
        $precio_lote = ($superficie * $zon_precio);

        $precio_lote_incrementado = $precio_lote + (($precio_lote * $urb_porc_incremento) / 100);

        $pagos = FUNCIONES::lista_bd_sql("select * from reserva_pago where respag_res_id='$res_id' and respag_estado='Pagado'");
        $cambio_usd = FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$fecha' and tca_mon_id='2'");
        $total_usd = 0;
        foreach ($pagos as $pag) {
            $monto = $pag->respag_monto;
            if ($pag->respag_moneda == '1') {
                $total_usd+=($monto / $cambio_usd);
            } else if ($pag->respag_moneda == '2') {
                $total_usd+=$monto;
            }
        }

        if ($total_usd > 0) {
            $porcentaje_pagado = (($total_usd * 100) / $precio_lote_incrementado);

            // if ($total_usd >= $monto_referencial) {
            if ($porcentaje_pagado >= $monto_referencial) {
                return true;
            } else {
                return false;
            }
        }
        else
            return false;
    }

    public static function lista_bd($tabla, $campo = '*', $condicion = '1') {
        $conec = new ADO();
        $sql = "select $campo from $tabla where $condicion";
        $conec->ejecutar($sql);
        $lista = array();
        for ($i = 0; $i < $conec->get_num_registros(); $i++) {
            $fila = clone $conec->get_objeto();
            $lista[] = $fila;
            $conec->siguiente();
        }
        return $lista;
    }

    public static function lista_bd_sql($sql) {
        $conec = new ADO();
//        echo $sql;
        $conec->ejecutar($sql);
        $lista = array();
        for ($i = 0; $i < $conec->get_num_registros(); $i++) {
            $fila = clone $conec->get_objeto();
            $lista[] = $fila;
            $conec->siguiente();
        }
        return $lista;
    }

    public static function objeto_bd($tabla, $col, $valor) {
        $conec = new ADO();
        $sql = "select * from $tabla where $col='$valor'";

        $conec->ejecutar($sql);
        return $conec->get_objeto();
    }

    public static function objeto_bd_sql($sql) {
        $conec = new ADO();
        $conec->ejecutar($sql);
        if ($conec->get_num_registros() > 0) {
            return $conec->get_objeto();
        } else {
            return null;
        }
    }

    public static function atributo_bd($tabla, $condicion, $campo) {
        $conec = new ADO();
        $sql = "select $campo as campo from $tabla where $condicion";
//        echo $sql."<br>";
        $conec->ejecutar($sql);
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
        $conec = new ADO();
        $sql = "select conf_valor as campo from con_configuracion where conf_nombre='$nombre' and conf_ges_id='$ges_id'";
//        echo $sql;
        $conec->ejecutar($sql);
        if ($conec->get_num_registros() > 0) {
            $objeto = $conec->get_objeto();
            return $objeto->campo;
        } else {
            return '';
        }
    }

    
    public static function atributo_bd_sql($sql) {
//        $conec = new ADO();

//        $conec->ejecutar($sql);
//        if ($conec->get_num_registros() > 0) {
//            $objeto = $conec->get_objeto();
//            return $objeto->campo;
//        } else {
//            return '';
//        }
        
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        
        if ($num > 0) {
            $objeto = mysql_fetch_object($result);
            return $objeto->campo;
        } else {
            return '';
        }
    }

    public static function objetos_bd($tabla, $condicion) {
//        $conec = new ADO();
//        $sql = "select * from $tabla where $condicion";
////        echo $sql;
//        $conec->ejecutar($sql);
//        return $conec;
        $sql = "select * from $tabla where $condicion";
        $result = mysql_query($sql);
        
    }

    public static function objetos_bd_sql($sql) {
        $conec = new ADO();
        $conec->ejecutar($sql);
        return $conec;
    }

    public static function numero_registros($tabla, $condicion) {
        $conec = new ADO();
        $sql = "select count(*) as total from $tabla where $condicion";
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        return $objeto->total * 1;
    }

    public static function invertir_fecha($fecha, $separador = '-') {
        $fecha_array = explode($separador, $fecha);
        return $fecha_array[2] . $separador . $fecha_array[1] . $separador . $fecha_array[0];
    }

    
    
    public static function bd_query($sql) {
//        $conec = new ADO();
//        $conec->ejecutar($sql);
//        return $conec;
        $result = mysql_query($sql);
        return $result;
    }

    function logs($tipo_accion, $sql, $usuario) {
        $sqlNew = str_replace("'", "/", $sql);
        $consulta = "insert into ad_logs (
										log_fecha,
										log_hora,
										log_tipo_accion,
										log_accion,
										log_usu_id,
										log_ip,
										log_pc
									)
									values
									('" . date("Y-m-d") . "',
										'" . date("H:i:s") . "',
										'" . $tipo_accion . "',
										'" . $sqlNew . "',
										'" . $usuario . "',
										'" . $_SERVER['REMOTE_ADDR'] . "',
										'" . gethostbyaddr($_SERVER['REMOTE_ADDR'])
                . "')";

        $result = mysql_query($consulta);
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

    public static function combo_data($sql, $data, $valor) {
        $conec = new ADO();
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            $cad = "";
            if ($objeto->id == $valor)
                $cad = ' selected="selected" ';
            echo '<option value="' . $objeto->id . '"' . $cad . ' data_' . $data . '="' . $objeto->{$data} . '">' . $objeto->nombre . '</option>';
            $conec->siguiente();
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

    public static function obtener_ultimo_pago($id) {
        $periodo = FUNCIONES::atributo_bd("con_periodo", "pdo_fecha_inicio<='$fecha' and pdo_fecha_fin>='$fecha'", 'pdo_id');
        return $periodo;
    }

    public static function total_debe_haber_cuenta($cue_id, $moneda, $params = null) {
        if ($params == null) {
            $params = new stdClass();
        }
        $conec_det = new ADO();

        $filtro = "";

        if ($params->fecha_inicio) {
            $filtro.=" and cmp_fecha >= '$params->fecha_inicio' ";
            if ($params->fecha_fin) {
                $filtro.=" and cmp_fecha <='$params->fecha_fin' ";
            }
        } else {
            if ($params->fecha_fin) {
                $filtro.=" and cmp_fecha <='$params->fecha_fin' ";
            }
        }

        if ($params->moneda_hecho) {
            $filtro.=" and cmp_mon_id = '$params->moneda_hecho' ";
        }
        if ($params->une_id) {
            $filtro .= " and cde_une_id='$params->une_id'";
        }

//        $fcentro_c = $_POST['id_centroc'] != '' ? ' and cde_cco_id=' . $_POST['id_centroc'] : '';
        $ges_id = $params->ges_id ? $params->ges_id : $_SESSION[ges_id];


        $sql = "
                select 
                        cde_valor
                from
                        con_comprobante c, con_comprobante_detalle cd
                where
                        c.cmp_id=cd.cde_cmp_id and c.cmp_eliminado='No' and cd.cde_cue_id=$cue_id and cd.cde_mon_id='$moneda'
                        and c.cmp_ges_id=$ges_id $filtro 
                ";
//                echo $sql;
        $conec_det->ejecutar($sql);
        $num = $conec_det->get_num_registros();
        $total_debe = 0;
        $total_haber = 0;
        $saldo = 0;
        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec_det->get_objeto();
            $debe = 0;
            $haber = 0;
            if ($objeto->cde_valor >= 0) {
                $debe = floatval($objeto->cde_valor);
                $saldo = $saldo + $debe;
                $total_debe+=$debe;
            }
            if ($objeto->cde_valor < 0) {
                $haber = floatval($objeto->cde_valor) * -1;
                $saldo = $saldo - $haber;
                $total_haber+=$haber;
            }
            $conec_det->siguiente();
        }
        $tdh = new stdClass();
        $tdh->tdebe = $total_debe;
        $tdh->thaber = $total_haber;
        return $tdh;
    }

    /**
     * Funcion para calcular dias de diferencia entre la fecha1 y la fecha2
     * @package pagepackage
     * @example fecha1 Y-m-d
     * @example fecha2 Y-m-d
     */
    public static function diferencia_dias($fecha1, $fecha2) {
        $segundos = strtotime($fecha2) - strtotime($fecha1);
        $diferencia_dias = intval($segundos / 60 / 60 / 24);
        return $diferencia_dias;
    }

    public static function numformat($valor) {
        return number_format($valor, 2);
    }

    public static function redondeo($valor, $precision = 2) {
        return round($valor, $precision);
    }

    public static function dia_max($year, $mes) {
        if ($mes == 1 || $mes == 3 || $mes == 5 || $mes == 7 || $mes == 8 || $mes == 10 || $mes == 12) {
            return 31;
        } elseif ($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) {
            return 30;
        } elseif ($mes == 2) {
            if ($year % 4 == 0) {
                return 29;
            } else {
                return 28;
            }
        }
    }

    public static function siguiente_mes($fecha, $dia = "") {
        $fa = explode("-", $fecha);
        $anio = $fa[0];
        $mes = $fa[1] + 1;
        if ($dia == '')
            $dia = $fa[2];
        if ($mes > 12) {
            $mes = 1;
            $anio = $anio + 1;
        }
        $dia_max = FUNCIONES::dia_max($anio, $mes);
        if ($dia > $dia_max) {
            $dia = $dia_max;
        }
        if (strlen($mes) == 1)
            $mes = "0" . $mes;
        if (strlen($dia) == 1)
            $dia = "0" . $dia;
        return "$anio-$mes-$dia";
    }

    public static function sumar_dias($dias, $fecha) {
        $fechaComparacion = strtotime($fecha);
        $calculo = strtotime("$dias day", $fechaComparacion);
        return date("Y-m-d", $calculo);
    }

    //PLAN DE PAGO 30 DIAS
    public static function plan_de_pagos($parametros) {

        $params = (object) $parametros;
        $rango = $params->rango;
        $frecuencia = $params->frecuencia;
        $dias_mes = 30;
        if (!$rango)
            $rango = 1;

        $dias = $rango * $dias_mes;
        $fecha_inicio = $params->fecha_inicio;
        $fecha_pri_cuota = $params->fecha_pri_cuota;

        if ($fecha_inicio > $fecha_pri_cuota) {
            $fecha_inicio = $params->fecha_pri_cuota;
        }

        $nro_cuota = $params->nro_cuota_inicio;

        $_dia = '01';
        if ($frecuencia == 'dia_mes') {
            $afpc = explode('-', $fecha_pri_cuota);
            $_dia = $afpc[2];
        }


        if ($params->tipo == 'plazo') {
            $plazo = $params->plazo;
            $interes_anual = $params->interes_anual;
            $saldo = $params->saldo;
//            $interes_efectivo = (($rango * $interes_anual ) / 12) / 100;

            $interes_dia = ($interes_anual / 360) / 100;

            $cuota = round(FUNCIONES::get_cuota($saldo, $interes_anual, $plazo, $rango), 2);

            $lista_cuotas = array();
            $sw = 1;
            for ($i = 1; $i <= $plazo; $i++) {
                $fila = new stdClass();
                if ($i == 1) {
                    $fecha_ant = $fecha_inicio;
                    $fecha = $fecha_pri_cuota;
                    $dias_interes = FUNCIONES::diferencia_dias($fecha_ant, $fecha);
                } else {
                    if ($frecuencia == '30_dias') {
                        $fecha = FUNCIONES::sumar_dias("+$dias", $fecha);
                        $dias_interes = $dias;
                    } elseif ($frecuencia == 'dia_mes') {
                        $fecha_ant = $fecha;
                        $fecha = FUNCIONES::sumar_meses($fecha, $rango, $_dia);
                        $dias_interes = FUNCIONES::diferencia_dias($fecha_ant, $fecha);
                    }
                }
                $fila->nro_cuota = $nro_cuota;
                $fila->fecha = $fecha;
                $fila->dias = $dias_interes;
                if ($sw == 1) {
                    $interes_efectivo = $dias * $interes_dia;
                } else {
                    $interes_efectivo = $dias_interes * $interes_dia;
                }


                $_interes = $saldo * $interes_efectivo;

                $interes = round($_interes, 2);

                if ($i == $plazo) {
                    $capital = round($saldo, 2);
                    $_capital = $saldo;
                } else {
                    $capital = $cuota - $interes;
                    $_capital = $cuota - $_interes;
                }

                if ($sw == 1) {
                    if ($fecha_inicio <= $fecha) {
                        $interes_efectivo = $dias_interes * $interes_dia;
                        $_interes = $saldo * $interes_efectivo;
                        $interes = round($_interes, 2);
                        $fila->dias = $dias_interes;
                        $sw++;
                    } else {
                        $interes = 0;
                        $fila->dias = 0;
                    }
                }

                if ($fecha_inicio >= $fecha) {
                    $interes = 0;
                }

                $fila->interes = $interes;
                $fila->capital = $capital;
                $fila->monto = $interes + $capital;

                $saldo = $saldo - $capital;

                $fila->saldo = round($saldo, 2);
                $lista_cuotas[] = $fila;
                $nro_cuota++;
            }
            return $lista_cuotas;
        } elseif ($params->tipo == 'cuota') {


            $interes_anual = $params->interes_anual;
            $saldo = $params->saldo;
            $interes_dia = ($interes_anual / 360) / 100;
            $cuota = round($params->cuota, 2);
            $plazo = 500;
            ;
            if ($params->plazo) {
                $plazo = $params->plazo;
            }
            $lista_cuotas = array();
            $i = 1;
            $sw = 1;
//            echo "$saldo --- & $nro_cuota  <= $plazo";
            while (round($saldo, 2) > 0 && $i <= $plazo) {
//                echo "$i <= $plazo<br>";
                $fila = new stdClass();
                if ($i == 1) {
                    $fecha_ant = $fecha_inicio;
                    $fecha = $fecha_pri_cuota;
                    $dias_interes = FUNCIONES::diferencia_dias($fecha_ant, $fecha);
                } else {
                    if ($frecuencia == '30_dias') {
                        $fecha = FUNCIONES::sumar_dias("+$dias", $fecha);
                        $dias_interes = $dias;
                    } elseif ($frecuencia == 'dia_mes') {
                        $fecha_ant = $fecha;
                        $fecha = FUNCIONES::sumar_meses($fecha, $rango, $_dia);
                        $dias_interes = FUNCIONES::diferencia_dias($fecha_ant, $fecha);
                    }
                }
                $fila->nro_cuota = $nro_cuota;
                $fila->fecha = $fecha;
//                $interes_efectivo = $dias * $interes_dia;
                if ($sw == 1) {
                    $interes_efectivo = $dias * $interes_dia;
                } else {
                    $interes_efectivo = $dias_interes * $interes_dia;
                }
                $fila->dias = $dias_interes;

                $_interes = $saldo * $interes_efectivo;

                $interes = round($_interes, 2);

                if ($saldo < $cuota || $i == $plazo) {
                    $capital = round($saldo, 2);
                    $_capital = $saldo;
                } else {
                    $capital = $cuota - $interes;
                    $_capital = $cuota - $_interes;
                }

                if ($sw == 1) {
                    if ($fecha_inicio <= $fecha) {
                        $interes_efectivo = $dias_interes * $interes_dia;
                        $_interes = $saldo * $interes_efectivo;
                        $interes = round($_interes, 2);
                        $fila->dias = $dias_interes;
                        $sw++;
                    } else {
                        $interes = 0;
                        $fila->dias = 0;
                    }
                }

                $fila->interes = $interes;
                $fila->capital = $capital;
                $fila->monto = $capital + $interes;
                $saldo = $saldo - $capital;
                $fila->saldo = round($saldo, 2);
                $lista_cuotas[] = $fila;

                $nro_cuota++;
                $i++;
            }
            return $lista_cuotas;
        } elseif ($params->tipo == 'plazo_cuota') {
            $interes_anual = $params->interes_anual;
            $saldo = $params->saldo;

            $fecha_inicio = $params->fecha_inicio;
            $fecha_pri_cuota = $params->fecha_pri_cuota;
            $nro_cuota = $params->nro_cuota_inicio;

            $interes_dia = ($interes_anual / 360) / 100;

            $cuota = round($params->cuota, 2);
            $plazo = $params->plazo;
            $lista_cuotas = array();
            $_afecha = explode('-', $fecha_pri_cuota);
            $dia = $_afecha[2];
            $i = 1;
            $sw = 1;
            while ($i <= $plazo && $i <= 500) {
                $fila = new stdClass();
                if ($i == 1) {
                    $fecha = $fecha_pri_cuota;
//                    $dias_interes = FUNCIONES::diferencia_dias($fecha_inicio, $fecha_pri_cuota);
                } else {
                    $fecha = FUNCIONES::sumar_dias("+$dias", $fecha);
                }
                $fila->nro_cuota = $nro_cuota;
                $fila->fecha = $fecha;

                $interes_efectivo = $dias * $interes_dia;

                $fila->dias = $dias;

                $_interes = $saldo * $interes_efectivo;
                $interes = round($_interes, 2);

                if ($saldo < $cuota) {
                    $capital = round($saldo, 2);
                    $_capital = $saldo;
                } else {
                    $capital = $cuota - $interes;
                    $_capital = $cuota - $_interes;
                }

                if ($sw == 1) {
                    if ($fecha_inicio <= $fecha) {
                        $interes_efectivo = $dias_interes * $interes_dia;
                        $_interes = $saldo * $interes_efectivo;
                        $interes = round($_interes, 2);
                        $fila->dias = $dias_interes;
                        $sw++;
                    } else {
                        $interes = 0;
                        $fila->dias = 0;
                    }
                }
                $fila->interes = $interes;
                $fila->capital = $capital;

                $fila->monto = $capital + $interes;
                $saldo = $saldo - $capital;
                $fila->saldo = $saldo;
                $lista_cuotas[] = $fila;
                $nro_cuota++;
                $i++;
            }
            return $lista_cuotas;
        }
    }

    public static function sumar_meses($fecha, $rango = "+1", $dia = "") {
        $af = explode('-', $fecha);
        if ($dia == '') {
            $dia = $af[2];
        }
        $nfecha = "$af[0]-$af[1]-01";
        $fechaComparacion = strtotime($nfecha);
        $calculo = strtotime("$rango month", $fechaComparacion);
        $rfecha = date("Y-m-d", $calculo);
        $arf = explode('-', $rfecha);
        $dia_max = FUNCIONES::dia_max($arf[0], $arf[1]);
        if ($dia > $dia_max)
            $dia = $dia_max;
        if (strlen($dia) == 1)
            $dia = "0" . $dia;
        $res_fecha = "$arf[0]-$arf[1]-$dia";
        return $res_fecha;
    }

    public static function plan_de_pagos_mensual($parametros) {
        $params = (object) $parametros;
        $rango = $params->rango;
        $dias_mes = 30;
        if (!$rango)
            $rango = 1;
        $dias = $rango * $dias_mes;
        if ($params->tipo == 'plazo') {
            $plazo = $params->meses;
            $interes_anual = $params->interes_anual;
            $saldo = $params->saldo;

            $fecha_pri_cuota = $params->fecha_pri_cuota;
            $nro_cuota = $params->nro_cuota_inicio;

            $interes_efectivo = (($rango * $interes_anual ) / 12) / 100;

            $cuota = FUNCIONES::get_cuota($saldo, $interes_anual, $plazo, $rango);

            $lista_cuotas = array();
            $_afecha = explode('-', $fecha_pri_cuota);
            $dia = $_afecha[2];

            for ($i = 1; $i <= $plazo; $i++) {
                $fila = new stdClass();
                if ($i == 1) {
                    $fecha = $fecha_pri_cuota;
                } else {
                    $fecha = FUNCIONES::sumar_dias("+$dias", $fecha);
                }
                $fila->nro_cuota = $nro_cuota;

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

                $fila->saldo = $saldo;
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
                $fila = new stdClass();
                if ($i == 1) {
                    $fecha = $fecha_pri_cuota;
                } else {
                    $fecha = FUNCIONES::sumar_dias("+$dias", $fecha);
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
                $saldo = $saldo - $capital;
                $fila->saldo = $saldo;
                $lista_cuotas[] = $fila;
                $nro_cuota++;
                $i++;
            }
            return $lista_cuotas;
        } elseif ($params->tipo == 'plazo_cuota') {
            $interes_anual = $params->interes_anual;
            $saldo = $params->saldo;
            $fecha_pri_cuota = $params->fecha_pri_cuota;
            $nro_cuota = $params->nro_cuota_inicio;

            $interes_efectivo = (($rango * $interes_anual ) / 12) / 100;

            $cuota = $params->cuota;
            $plazo = $params->meses;
            $lista_cuotas = array();
            $_afecha = explode('-', $fecha_pri_cuota);
            $dia = $_afecha[2];
            $i = 1;
            while ($i <= $plazo && $i <= 500) {
                $fila = new stdClass();
                if ($i == 1) {
                    $fecha = $fecha_pri_cuota;
                } else {
                    $fecha = FUNCIONES::sumar_dias("+$dias", $fecha);
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
                $saldo = $saldo - $capital;
                $fila->saldo = $saldo;
                $lista_cuotas[] = $fila;
                $nro_cuota++;
                $i++;
            }
            return $lista_cuotas;
        }
    }

    public static function primera_cuota(&$nro_cuota, &$saldo, $fecha_partida, &$fecha_pri_cuota, $interes_anual, $cuota) {
        $dias = FUNCIONES::diferencia_dias($fecha_pri_cuota, $fecha_partida);

        $interes_diario = $interes_anual / 360;
        $fila = new stdClass();
        $fila->nro_cuota = $nro_cuota;
        $fila->fecha = $fecha_pri_cuota;

        $_interes_dia = $saldo * $interes_diario;
        $fila->interes = $_interes;
        $_capital = $cuota - $_interes;
        $fila->capital = $_capital;
        $saldo = $saldo - $_capital;

        $fila->monto = $cuota;
        $fila->saldo = $saldo;
        $nro_cuota++;

        return $fila;

        echo utf8_encode("Cuota Nro: " . ($nro_cuota + $comenzar));
        echo "&nbsp;</td>";
        echo "<td>";
        if ($fecha <> '0000-00-00')
            echo FUNCIONES::get_fecha_latina($fecha);
        echo "&nbsp;</td>";
        echo "<td>";
        echo $txt_moneda;
        echo "<td>";
        $interes = $saldo * $interes_mensual;
        $interes_pag = ($interes / 30) * $dif_dias;
        echo round($interes_pag, 2);
        echo "&nbsp;</td>";
        echo "<td>";
        if ($saldo < $cuota) {
            $capital = $saldo;
        } else {
            $capital = $cuota - $interes;
        }
        echo round($capital, 2);
        echo "&nbsp;</td>";
        //    echo "<td>";
        //    $monto_formulario = $valor_form;
        //    echo round($monto_formulario, 2);
        //    echo "&nbsp;</td>";
        echo "<td>";
        echo round($capital + $interes_pag + $monto_formulario, 2);
        echo "&nbsp;</td>";
        $saldo = $saldo - $capital;
        echo "<td>";
        echo round($saldo, 2);
        echo "&nbsp;</td>";
        echo "</tr>";
    }

    public static function get_cuota($monto, $interes, $plazo, $rango = 1) {
        if ($interes > 0) {
            $interes = ($rango * $interes) / 12;
            $interes /= 100;
            $power = pow(1 + $interes, $plazo);
            return round(($monto * $interes * $power) / ($power - 1), 2);
        } else {
            return round($monto / $plazo, 2);
        }
    }

    public static function add_elementos(&$array1, $array2) {
        foreach ($array2 as $valor) {
            $array1[] = $valor;
        }
    }

    public static function get_cambios($fecha) {
        $cambios = array();
        $cambios_bd = FUNCIONES::objetos_bd_sql("select * from con_tipo_cambio where tca_fecha='$fecha'");
        for ($i = 0; $i < $cambios_bd->get_num_registros(); $i++) {
            $cam = $cambios_bd->get_objeto();
            $cambios[] = array('id' => $cam->tca_mon_id, 'val' => $cam->tca_valor);
            $cambios_bd->siguiente();
        }
        return $cambios;
    }

    public static function cambiar_monto($monto, $mon_orig, $mon_dest, $cambios) {
//        echo "$monto, $mon_orig,$mon_dest,$cambios <br>";
        if ($mon_orig == $mon_dest) {
            return $monto;
        }
        $tc = 1;
        for ($i = 0; $i < count($cambios); $i++) {
            $cambio = $cambios[$i];
            if ($mon_orig == $cambio[id] * 1) {
                $tc = $cambio[val];
            }
        }
        $monto_bol = $monto * $tc;
        $tc = 1;
        for ($i = 0; $i < count($cambios); $i++) {
            $cambio = $cambios[$i];
            if ($mon_dest == $cambio[id] * 1) {
                $tc = $cambio[val];
            }
        }
        return $monto_bol / $tc;
    }

    public static function fecha_codigo() {
        $fecha = date('YmdHis');
        $now = (string) microtime();
        return $fecha . $now[2] . $now[3];
    }

    public static function interno_nombre($int_id) {
        return FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$int_id'");
    }

    public static function usuario_nombre($usuario) {
        $conec = new ADO();

        $sql = "select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    public static function print_pre($objeto) {

        $b = true;
        if ($b) {
            echo "<pre>";
            print_r($objeto);
            echo "</pre>";
        }
    }

    public static function print_txt($texto) {
        $b = true;
        if ($b) {
            echo $texto;
        }
    }

    public static function str_mes($mes, $completo = true) {
        if ($mes == 1) {
            return $completo ? 'enero' : 'ene';
        } elseif ($mes == 2) {
            return $completo ? 'febrero' : 'feb';
        } elseif ($mes == 3) {
            return $completo ? 'marzo' : 'mar';
        } elseif ($mes == 4) {
            return $completo ? 'abril' : 'abr';
        } elseif ($mes == 5) {
            return $completo ? 'mayo' : 'may';
        } elseif ($mes == 6) {
            return $completo ? 'junio' : 'jun';
        } elseif ($mes == 7) {
            return $completo ? 'julio' : 'jul';
        } elseif ($mes == 8) {
            return $completo ? 'agosto' : 'ago';
        } elseif ($mes == 9) {
            return $completo ? 'septiembre' : 'sep';
        } elseif ($mes == 10) {
            return $completo ? 'octubre' : 'oct';
        } elseif ($mes == 11) {
            return $completo ? 'noviembre' : 'nov';
        } elseif ($mes == 12) {
            return $completo ? 'diciembre' : 'dic';
        }
    }

    public static function nro_recibo($fecha = "") { // formato mysql
        if (_CTE::$RECIBO_PERIODO) {
            if ($fecha) {
                $af = explode('-', $fecha);
                $mes = $af[1];
                $anio = $af[0];
                $periodo = FUNCIONES::objeto_bd_sql("select * from periodo_recibo where prec_anio='$anio' and prec_mes='$mes' ");
                if ($periodo) {
                    $numero = $periodo->prec_numero + 1;
                    FUNCIONES::bd_query("update periodo_recibo set prec_numero=prec_numero+1 where prec_anio='$anio' and prec_mes='$mes'");
//                     $nro_recibo;
                } else {
                    FUNCIONES::bd_query("insert into periodo_recibo(prec_anio,prec_mes,prec_numero) values('$anio','$mes','1')");
                    $numero = 1;
                }
                $nro_rec = $numero . '';
                $ceros = '';
                for ($i = strlen($nro_rec); $i < 6; $i++) {
                    $ceros.='0';
                }
                $nro_recibo = "{$anio}{$mes}{$ceros}{$nro_rec}";
                return $nro_recibo;
            } else {
                return 0;
            }
        } else {
            $nro_recibo = FUNCIONES::atributo_bd_sql('select par_recibo as campo from ad_parametro') + 1;
            FUNCIONES::bd_query('update ad_parametro set par_recibo=par_recibo+1');
            return $nro_recibo;
        }
    }

    public static function saldo_intercambio($ven_id) {
        $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$ven_id order by inter_id asc");
        $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$ven_id group by vipag_inter_id order by inter_id asc");
//        $sum_inter_pag = 0;
//        foreach ($amontos_pag as $ipag) {
//            $sum_inter_pag+=$ipag->monto;
//        }
//        $n_interes_ids = array();
        $n_interes_montos = array();
        for ($j = 0; $j < count($amontos); $j++) {
            $iprov = $amontos[$j];
            $_sum_pag = 0;
            for ($k = 0; $k < count($amontos_pag); $k++) {
                $ipag = $amontos_pag[$k];
                if ($ipag->inter_id . '' == $iprov->inter_id . '') {
                    $_sum_pag = $ipag->monto;
                    break;
                }
            }
            $dif_pag = $iprov->monto - $_sum_pag;
            if ($dif_pag > 0) {
//                $n_interes_ids[] = $iprov->inter_id;
                $n_interes_montos[$iprov->inter_id] = $dif_pag;
            }
        }
        return $n_interes_montos;
    }

    public static function limpiar_cadena($str) {
        $str = str_replace('Á', '&Aacute;', $str);
        $str = str_replace('á', '&aacute;', $str);
        $str = str_replace('É', '&Eacute;', $str);
        $str = str_replace('é', '&eacute;', $str);
        $str = str_replace('Í', '&Iacute;', $str);
        $str = str_replace('í', '&iacute;', $str);
        $str = str_replace('Ó', '&Oacute;', $str);
        $str = str_replace('ó', '&oacute;', $str);
        $str = str_replace('Ú', '&Uacute;', $str);
        $str = str_replace('ú', '&uacute;', $str);
        $str = str_replace('Ñ', '&Ntilde;', $str);
        $str = str_replace('ñ', '&ntilde;', $str);
        return $str;
    }

    public static function html_decode($str) {
        $str = str_replace('&Aacute;', 'Á', $str);
        $str = str_replace('&aacute;', 'á', $str);
        $str = str_replace('&Eacute;', 'É', $str);
        $str = str_replace('&eacute;', 'é', $str);
        $str = str_replace('&Iacute;', 'Í', $str);
        $str = str_replace('&iacute;', 'í', $str);
        $str = str_replace('&Oacute;', 'Ó', $str);
        $str = str_replace('&oacute;', 'ó', $str);
        $str = str_replace('&Uacute;', 'Ú', $str);
        $str = str_replace('&uacute;', 'ú', $str);
        $str = str_replace('&Ntilde;', 'Ñ', $str);
        $str = str_replace('&ntilde;', 'ñ', $str);
        return $str;
    }

    public static function nombremes($mes) {
        setlocale(LC_TIME, 'spanish');
        $nombre = strftime("%B", mktime(0, 0, 0, $mes, 1, 2000));
        return $nombre;
    }

    public static function num2letras($num, $fem = false, $dec = true) {
        /* ! 
          @function num2letras ()
          @abstract Dado un n?mero lo devuelve escrito.
          @param $num number - N?mero a convertir.
          @param $fem bool - Forma femenina (true) o no (false).
          @param $dec bool - Con decimales (true) o no (false).
          @result string - Devuelve el n?mero escrito en letra.

         */
//if (strlen($num) > 14) die("El n?mero introducido es demasiado grande"); 
        $matuni[2] = "dos";
        $matuni[3] = "tres";
        $matuni[4] = "cuatro";
        $matuni[5] = "cinco";
        $matuni[6] = "seis";
        $matuni[7] = "siete";
        $matuni[8] = "ocho";
        $matuni[9] = "nueve";
        $matuni[10] = "diez";
        $matuni[11] = "once";
        $matuni[12] = "doce";
        $matuni[13] = "trece";
        $matuni[14] = "catorce";
        $matuni[15] = "quince";
        $matuni[16] = "dieciseis";
        $matuni[17] = "diecisiete";
        $matuni[18] = "dieciocho";
        $matuni[19] = "diecinueve";
        $matuni[20] = "veinte";
        $matunisub[2] = "dos";
        $matunisub[3] = "tres";
        $matunisub[4] = "cuatro";
        $matunisub[5] = "quin";
        $matunisub[6] = "seis";
        $matunisub[7] = "sete";
        $matunisub[8] = "ocho";
        $matunisub[9] = "nove";

        $matdec[2] = "veint";
        $matdec[3] = "treinta";
        $matdec[4] = "cuarenta";
        $matdec[5] = "cincuenta";
        $matdec[6] = "sesenta";
        $matdec[7] = "setenta";
        $matdec[8] = "ochenta";
        $matdec[9] = "noventa";
        $matsub[3] = 'mill';
        $matsub[5] = 'bill';
        $matsub[7] = 'mill';
        $matsub[9] = 'trill';
        $matsub[11] = 'mill';
        $matsub[13] = 'bill';
        $matsub[15] = 'mill';
        $matmil[4] = 'millones';
        $matmil[6] = 'billones';
        $matmil[7] = 'de billones';
        $matmil[8] = 'millones de billones';
        $matmil[10] = 'trillones';
        $matmil[11] = 'de trillones';
        $matmil[12] = 'millones de trillones';
        $matmil[13] = 'de trillones';
        $matmil[14] = 'billones de trillones';
        $matmil[15] = 'de billones de trillones';
        $matmil[16] = 'millones de billones de trillones';

        $num = trim((string) @$num);
        if ($num[0] == '-') {
            $neg = 'menos ';
            $num = substr($num, 1);
        }
        else
            $neg = '';
        while ($num[0] == '0')
            $num = substr($num, 1);
        if ($num[0] < '1' or $num[0] > 9)
            $num = '0' . $num;
        $zeros = true;
        $punt = false;
        $ent = '';
        $fra = '';
        for ($c = 0; $c < strlen($num); $c++) {
            $n = $num[$c];
            if (!(strpos(".,'''", $n) === false)) {
                if ($punt)
                    break;
                else {
                    $punt = true;
                    continue;
                }
            } elseif (!(strpos('0123456789', $n) === false)) {
                if ($punt) {
                    if ($n != '0')
                        $zeros = false;
                    $fra .= $n;
                }
                else
                    $ent .= $n;
            }
            else
                break;
        }
        $ent = '     ' . $ent;
        if ($dec and $fra and !$zeros) {
            $fin = ' coma';
            for ($n = 0; $n < strlen($fra); $n++) {
                if (($s = $fra[$n]) == '0')
                    $fin .= ' cero';
                elseif ($s == '1')
                    $fin .= $fem ? ' una' : ' un';
                else
                    $fin .= ' ' . $matuni[$s];
            }
        }
        else
            $fin = '';
        if ((int) $ent === 0)
            return 'Cero ' . $fin;
        $tex = '';
        $sub = 0;
        $mils = 0;
        $neutro = false;
        while (($num = substr($ent, -3)) != '   ') {
            $ent = substr($ent, 0, -3);
            if (++$sub < 3 and $fem) {
                $matuni[1] = 'una';
                $subcent = 'as';
            } else {
                $matuni[1] = $neutro ? 'un' : 'uno';
                $subcent = 'os';
            }
            $t = '';
            $n2 = substr($num, 1);
            if ($n2 == '00') {
                
            } elseif ($n2 < 21)
                $t = ' ' . $matuni[(int) $n2];
            elseif ($n2 < 30) {
                $n3 = $num[2];
                if ($n3 != 0)
                    $t = 'i' . $matuni[$n3];
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            }else {
                $n3 = $num[2];
                if ($n3 != 0)
                    $t = ' y ' . $matuni[$n3];
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            }
            $n = $num[0];
            if ($n == 1) {
                $t = ' ciento' . $t;
            } elseif ($n == 5) {
                $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
            } elseif ($n != 0) {
                $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
            }
            if ($sub == 1) {
                
            } elseif (!isset($matsub[$sub])) {
                if ($num == 1) {
                    $t = ' mil';
                } elseif ($num > 1) {
                    $t .= ' mil';
                }
            } elseif ($num == 1) {
                $t .= ' ' . $matsub[$sub] . '?n';
            } elseif ($num > 1) {
                $t .= ' ' . $matsub[$sub] . 'ones';
            }
            if ($num == '000')
                $mils++;
            elseif ($mils != 0) {
                if (isset($matmil[$sub]))
                    $t .= ' ' . $matmil[$sub];
                $mils = 0;
            }
            $neutro = true;
            $tex = $t . $tex;
        }
        $tex = $neg . substr($tex, 1) . $fin;
        return ucfirst($tex);
    }

    public static function tareas_permitidas($usuario_id, $ele_id) {
        $conec = new ADO();
        $vector = array();
        $consulta = "SELECT tar_nombre
			FROM ad_elemento,ad_permiso,ad_usuario,ad_tarea
			WHERE usu_id= '" . $usuario_id . "'
			AND usu_gru_id = pmo_gru_id
			AND pmo_ele_id=ele_id
			AND pmo_tar_id=tar_id
			AND ele_id='" . $ele_id . "'
			AND ele_estado = 'H'";

        $conec->ejecutar($consulta);

        $numero = $conec->get_num_registros();

        for ($i = 0; $i < $numero; $i++) {

            $objeto = $conec->get_objeto();

            $vector[$i] = $objeto->tar_nombre;

            $conec->siguiente();
        }

        return $vector;
    }

    public static function verificar_permisos($usuario_id, $ele_id, $tarea) {
        $permisos = FUNCIONES::tareas_permitidas($usuario_id, $ele_id);
        if (is_numeric(array_search($tarea, $permisos))) {
            return true;
        } else {
            return false;
        }
    }

}

class _CTE {

    public static $RECIBO_PERIODO = true;
    public static $SERVICIOS = array(
        'ven_prop_his' => array('titulo' => 'CAMBIO DE TITULAR', 'monto' => 30)
    );
    public static $INTERNO_DEUDA = 'interno_deuda';
    public static $INTERNO_DEUDA_PAGO = 'interno_deuda_pago';
    public static $MORA = 'mora';
    public static $PAGO_MORA = 'pago_mora';
    public static $RESERVA_PAGO = 'reserva_pago';
    public static $PAGO_VENDEDORES = 'pago_vendedores';
    public static $RESERVA_REVERSION = 'reserva_reversion';
    public static $VENTA = 'venta';
    public static $COMISION = 'comision';
    public static $EXTRA_PAGO = 'extra_pago';

}

class Objeto {

    public static function insert($objeto, $tabla, $log = true) {
        $campos = (array) $objeto;
        $sql = "insert into " . $tabla . " ";
        $_metas = "(";
        $_datos = "(";
        $i = 0;
        foreach ($campos as $meta => $dato) {
            if ($i > 0) {
                $_metas.=',';
                $_datos.=',';
            }
            $_metas.=$meta;
            $_datos.="'$dato'";
            $i++;
        }
        $_metas.=")";
        $_datos.=")";
        $sql.=$_metas . " values " . $_datos;
//        echo '<br>'.$sql.'<br>';
        $conec = new ADO();
        $conec->ejecutar($sql, $log);
    }

    public static function update($objeto, $tabla, $id) {
        $campos = (array) $objeto;
        $sql = "update " . $tabla . " set ";
        $datos = "";
        $i = 0;
        foreach ($campos as $meta => $dato) {
            if ($meta != $id) {
                if ($datos != '') {
                    $datos.=',';
                }
                $datos.="$meta='$dato'";
            }
            $i++;
        }
        $sql.=" $datos where $id='{$objeto->{$id}}'";
//        echo $sql.';<br>';
        $conec = new ADO();
        $conec->ejecutar($sql);
    }

}

class _PRINT {

    public static function pre($objeto) {
//        return;

        echo "<pre>";
        print_r($objeto);
        echo "</pre>";
    }

    public static function txt($texto) {
        return;

        echo $texto;
    }

}

?>
