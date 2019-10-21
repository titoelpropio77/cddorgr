<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class MLM {

    public static $rangos_eternos = array();
    public static $rango_mas_alto = 6;

    public static function agregar_asociado($data) {
        $data = (object) $data;
        $interno_id = $data->int_id;
        $vendedor_id = $data->vdo_id;
        $venta_id = $data->ven_id;
        //$cuenta_vendedor_id = 172;
        $cuenta_vendedor_id = 0;
        $conexion = new ADO();
        $sql_vendedor = "select * from vendedor where vdo_int_id=$interno_id";

        $vendedor_asociado = FUNCIONES::objeto_bd_sql($sql_vendedor);

//        if ($vendedor_asociado === NULL) {
        if (TRUE) {

            $nivel = FUNCIONES::atributo_bd_sql("select vdo_nivel as campo 
                from vendedor where vdo_id='$vendedor_id'");
            $nivel++;
            $vdo_grupo_id = FUNCIONES::atributo_bd_sql("select vgru_id as campo from vendedor_grupo where vgru_nombre='AFILIADOS'") * 1;
            $sql_insertar = "insert into vendedor(
                                vdo_int_id, vdo_cco_id,vdo_vgru_id,
                                vdo_estado, vdo_vendedor_id,vdo_venta_inicial,vdo_nivel,vdo_can_id,
                                vdo_rango_actual,vdo_rango_alcanzado)
                            values ($interno_id, 0,'$vdo_grupo_id',
                                'Habilitado', $vendedor_id, 
                                $venta_id,$nivel,'','1','1');";
            $conexion->ejecutar($sql_insertar, true, true);
            FUNCIONES::eco($sql_insertar);
            $vendedor_id = ADO::$insert_id;

            MLM::add_usuario_vendedor($interno_id, $vendedor_id, $venta_id);
        }
    }

    public static function add_usuario_vendedor($interno_id, $vendedor_id, $ven_id) {
        $sql_interno = "select * from interno where int_id=$interno_id";
        $conex = new ADO();
        $conex->ejecutar($sql_interno);
        $interno = $conex->get_objeto();
        $nombre = $interno->int_nombre;
        $nombre = trim($nombre);
        $nombre_array = explode(' ', $nombre);
        $char_nombre = $nombre[0];
        $char_nombre = strtolower($char_nombre);
        $apellido = $interno->int_apellido;
        $apellido = trim($apellido);
        $apellido_array = explode(' ', $apellido);
        $char_apellido = $apellido[0];
        $char_apellido = strtolower($char_apellido);
        $username = $char_nombre . $char_apellido . $vendedor_id;


        $sql_usuario = "select * from ad_usuario where usu_per_id=$interno_id";

        $conex->ejecutar($sql_usuario);
//        if ($conex->get_num_registros() == 0) {
        if (TRUE) {

            $pass_usuario = md5($username);
            $sql_insert_usuario = "
                INSERT INTO `ad_usuario` (
                    `usu_id`, `usu_password`,`usu_per_id`, 
                    `usu_estado`, `usu_gru_id`) 
                VALUES ('$username', '$pass_usuario',$interno_id, 
                    '1','AFILIADOS');";
            $conex->ejecutar($sql_insert_usuario);
            FUNCIONES::eco($sql_insert_usuario);
        }
    }

    public static function esta_al_dia($vendedor, $fecha) {

        if (MLM::esta_exenta_bra($vendedor->vdo_venta_inicial, $fecha)) {
            return TRUE;
        }
        
        if (in_array($vendedor->vdo_id, self::$rangos_eternos)) {
            FUNCIONES::eco("AFILIADO $vendedor->vdo_id AL DIA");
            return TRUE;
        }

        $ven_id = $vendedor->vdo_venta_inicial;

        if ($ven_id == 0) {
            return false;
        }

        $s_periodo = substr($fecha, 0, 7);
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$ven_id");
        
        if (substr($venta->ven_fecha, 0, 7) == $s_periodo) {
            FUNCIONES::eco("VENTA $ven_id AL DIA");
            return TRUE;
        }
        
        $arr_estados_validos = array('Pendiente','Pagado');
        if (in_array($venta->ven_estado, $arr_estados_validos) === FALSE) {
            FUNCIONES::eco("VENTA $ven_id NO ESTA AL DIA");
            return false;
        }

        if ($venta->ven_estado == 'Pagado' && $venta->ven_fecha_act_mlm >= $fecha) {
            FUNCIONES::eco("VENTA $ven_id AL DIA");
            return TRUE;
        } else if ($venta->ven_fecha_act_mlm < $fecha) {
            FUNCIONES::eco("VENTA $ven_id NO ESTA AL DIA");
            return FALSE;
        }

        $b = true;

        $sql_cuota = "select ind_id from interno_deuda where ind_tabla_id='$ven_id'
                        and ind_tabla='venta' and ind_estado='Pendiente' 
                        and ind_fecha_programada < '$fecha';";
        FUNCIONES::eco($sql_cuota);
        $cuotas = FUNCIONES::objetos_bd_sql($sql_cuota);

        if ($cuotas->get_num_registros() > 0) {
            $b = false;
            FUNCIONES::eco("VENTA $ven_id NO ESTA AL DIA");
        } else {
            $datos_fecha = explode('-', $fecha);
			$periodo = $datos_fecha[0].'-'.$datos_fecha[1];
            $sql_cuota = "select * from interno_deuda where ind_tabla_id='$ven_id'
                        and ind_tabla='venta' and ind_estado='Pagado' 
                        and ind_fecha_programada <= '$fecha' 
						and left(ind_fecha_programada,7)='$periodo'
                        order by ind_fecha_programada desc limit 1;";
            FUNCIONES::eco($sql_cuota);
            $cuota = FUNCIONES::objeto_bd_sql($sql_cuota);
            
            if ($cuota) {
                if (substr($cuota->ind_fecha_pago, 0, 7) <= $s_periodo) {
                    $b = true;
                    FUNCIONES::eco("VENTA $ven_id AL DIA");
                } else {
                    $b = false;
                    FUNCIONES::eco("VENTA $ven_id NO ESTA AL DIA");
                }
            } else {
                $b = false;
                FUNCIONES::eco("VENTA $ven_id NO ESTA AL DIA");
            }
            
            
        }

        return $b;
    }

    public static function venta_al_dia($venta, $fecha, $bra = FALSE) {
        
//        $datos_fecha = explode('-', $fecha);
//        $periodo = $datos_fecha[0] . "-" . $datos_fecha[1];
        $periodo = substr($fecha, 0, 7);
        
        if (substr($venta->ven_fecha, 0, 7) == $periodo) {
            FUNCIONES::eco("VENTA $venta->ven_id AL DIA");
            return TRUE;
        }
        
        if (MLM::esta_exenta_bra($venta->ven_id, $fecha)) {
            return TRUE;
        }
        
        $b = FALSE;

        $sql_cuota = "select * from interno_deuda where ind_tabla_id='$venta->ven_id'
                        and ind_tabla='venta' and ind_estado in ('Pagado','Pendiente')
                        and ind_num_correlativo > 0
                        and left(ind_fecha_programada,7) = '$periodo'                             
                        order by ind_fecha_programada asc limit 0,1;";
        FUNCIONES::eco($sql_cuota);
        $cuota = FUNCIONES::objeto_bd_sql($sql_cuota);

        if ($cuota) {
            if ($cuota->ind_estado == 'Pagado') {
                if (substr($cuota->ind_fecha_pago, 0, 7) <= $periodo) {
                    FUNCIONES::eco("VENTA $venta->ven_id ESTA AL DIA 1");
                    $b = TRUE;
                } else {
                    FUNCIONES::eco("VENTA $venta->ven_id NO ESTA AL DIA 1");
                    $b = FALSE;
                }
                
            } else {
                FUNCIONES::eco("VENTA $venta->ven_id NO ESTA AL DIA 1");
            }
        } else {
            if ($bra) {
                FUNCIONES::eco("VENTA $venta->ven_id NO ESTA AL DIA 2");
            } else {
                FUNCIONES::eco("VENTA $venta->ven_id ESTA AL DIA 2");
                $b = TRUE;
            }
        }

        return $b;
    }

    public static function esta_activo($vendedor, $fecha) {

        if ($vendedor->vdo_vendedor_id == 0) {
            return true;
        }

        $b = false;
        $int_id = $vendedor->vdo_int_id;
        $ven_id = $vendedor->vdo_venta_inicial;
        $sql = "SELECT ven.ven_fecha_act_mlm
                    FROM venta ven
                    WHERE ven.ven_int_id = '$int_id'
                    AND ven.ven_id = $ven_id
                    AND ven.ven_estado in ('Pendiente','Pagado')    
                    AND ven.ven_multinivel='si'
                    ORDER BY ven.ven_fecha_act_mlm DESC
                    LIMIT 0,1";
        FUNCIONES::eco("<p style='color:brown'>$sql</p>");
        $ventas = FUNCIONES::objetos_bd_sql($sql);

        for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
            $venta = $ventas->get_objeto();
            $pdo_fecha_act = substr($venta->ven_fecha_act_mlm, 0, 7);
            $pdo_fecha = substr($fecha, 0, 7);

            if (strcmp($pdo_fecha_act, $pdo_fecha) >= 0) {
                $b = true;
                break;
            }
            $ventas->siguiente();
        }

        if ($b) {
            FUNCIONES::eco("ESTA ACTIVO");
        } else {
            FUNCIONES::eco("NO ESTA ACTIVO");
        }

        return $b;
    }

    public static function obtener_rangos($arr_vdo_ids, $campo_rango = 'vdo_rango_alcanzado') {
        $arr_rangos = array();
        $s_ids = implode(',', $arr_vdo_ids);
        $sql_vdo = "select vdo_id,LOWER(ran_nombre)AS ran_nombre from vendedor 
            inner join rango on($campo_rango=ran_id)
            where vdo_id in ($s_ids)";

//        FUNCIONES::eco($sql_vdo);

        $rangos = FUNCIONES::lista_bd_sql($sql_vdo);

        foreach ($rangos as $ran) {

//            $ran->ran_nombre = ($ran->ran_nombre == 'asociado')?'person':$ran->ran_nombre;

            $arr_rangos[$ran->vdo_id] = $ran->ran_nombre;
        }

        return $arr_rangos;
    }

    public static function obtener_red($vdo_id, &$nivel, $self = false, $profundidad = 0, $this_not = '', $tabla = 'vendedor') {

        $arr_vendedores = array();
        if ($self) {
            //if ($this_not != $vdo_id) {                                    
            $arr_vendedores[] = $vdo_id;
            //}//esto es para incluirlo si es diferente de $this_not
        }

        $estado = "";
        if ($tabla == 'vendedor') {
            $estado = "and vdo_estado = 'Habilitado'";
        }

        $sql = "select vdo_id from $tabla where vdo_vendedor_id='$vdo_id'
                    and vdo_nivel <= $profundidad
                    $estado";
        
//        echo "<p>$sql</p>";
        
        $vendedores = FUNCIONES::objetos_bd_sql($sql);
        $num = $vendedores->get_num_registros();

        if ($profundidad > 0) {

            for ($i = 0; $i < $num; $i++) {

                $obj = $vendedores->get_objeto();

                $arr_aux = MLM::obtener_red($obj->vdo_id, $nivel, true, $profundidad, '', $tabla);
                for ($j = 0; $j < count($arr_aux); $j++) {

                    if ($this_not != $arr_aux[$j]) {//esto es para incluirlo si es diferente de $this_not                                    
                        $arr_vendedores[] = $arr_aux[$j];
                    }//esto es para incluirlo si es diferente de $this_not
                }

                $vendedores->siguiente();
            }
        } else {
            return $arr_aux = array();
        }

        return $arr_vendedores;
    }

    public static function rango_actual($vdo_id) {
//        $sql = "select vran.*,ran.* from vendedor_rango vran,rango ran 
//                where vran_vdo_id='$vdo_id' 
//                and vran.vran_ran_id=ran.ran_id
//                and vran.vran_eliminado = 'No'
//                order by vran_ran_id desc limit 0,1";

        $sql = "select ran.* from rango ran 
                inner join vendedor vdo on (ran.ran_id=vdo.vdo_rango_actual)
                where vdo.vdo_id=$vdo_id";

        $rango = FUNCIONES::objeto_bd_sql($sql);
        return $rango;
    }

    public static function rango_eterno($vdo_id, $rango) {

        if (in_array($vdo_id, self::$rangos_eternos)) {
            $rango_eterno = FUNCIONES::objeto_bd_sql("select * from rango_eterno 
                inner join rango on (ret_ran_id=ran_id)
                where ret_vdo_id=$vdo_id and ret_eliminado='No'");

            if ($rango->ran_id >= $rango_eterno->ran_id) {
                return $rango;
            } else {
                return $rango_eterno;
            }
        } else {
            return $rango;
        }
    }

    public static function cargar_rangos_eternos() {
        $sql = "select ret_vdo_id from rango_eterno where ret_eliminado='No'";
        $res = FUNCIONES::lista_bd_sql($sql);

        foreach ($res as $elem) {
            self::$rangos_eternos[] = $elem->ret_vdo_id;
        }

//        print_r(self::$rangos_eternos);
    }

    public static function calcular_rango_actual($vdo_id, $cuando = '') {
        $s_rango = NULL;

        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$vdo_id");

        if (in_array($vdo_id, self::$rangos_eternos)) {

            if ($vendedor->vdo_rango_alcanzado == self::$rango_mas_alto) {
                $s_rango = FUNCIONES::objeto_bd_sql("select * from rango where ran_id=$vendedor->vdo_rango_alcanzado");

                if ($vdo_id == 1) {
//                    echo "<p>EL AFILIADO NODO RAIZ...</p>";
//                    print_r($vendedor);
//                    print_r($s_rango);
                }

                return $s_rango;
            }
        }

        $rango_mas_bajo = FUNCIONES::objeto_bd_sql("select * from rango where ran_hijos_directos>0 
            order by ran_id asc limit 0,1");
        $nivel = 1000;
        $vdo_nivel = $vendedor->vdo_nivel;

        $frontales = MLM::obtener_red($vdo_id, $nivel, FALSE, $vdo_nivel + 1);
        $red_completa = MLM::obtener_red($vdo_id, $nivel, TRUE, $vdo_nivel + 7);
        $cant_frontales = count($frontales);

        if ($cant_frontales < $rango_mas_bajo->ran_hijos_directos) {

            $s_rango = FUNCIONES::objeto_bd_sql("select * from rango where ran_id=1");
            $s_rango = MLM::rango_eterno($vdo_id, $s_rango);

            $texto = "<p style='color:red'>$s_rango->ran_nombre - $vdo_id</p>";
            FUNCIONES::eco($texto);

            return $s_rango;
        }

        $sql_rp = "select * from rango 
            where ran_hijos_directos <= $cant_frontales 
                order by ran_hijos_directos desc limit 0,1";
        $rango_posible = FUNCIONES::objeto_bd_sql($sql_rp);

        $b = TRUE;
        $sw = TRUE;
        $hoy = ($cuando != '') ? $cuando : date('Y-m-d');
        while ($rango_posible->ran_rango_menor > 0 && $sw) {

            $texto = "<p>entrando al while...</p>";
            FUNCIONES::eco($texto);

            $ev_piernas = MLM::evaluar_piernas_calificadas($vdo_id, $rango_posible, $frontales);
            if (!$ev_piernas) {

                $texto = "<br>entro a obtener rango menor {$rango_posible->ran_nombre}";
                FUNCIONES::eco($texto);

                $sql_rp = "select * from rango 
                    where ran_id=$rango_posible->ran_rango_menor";
                $rango_posible = FUNCIONES::objeto_bd_sql($sql_rp);
                continue;
            }

            $ev_front = MLM::evaluar_frontales($vdo_id, $frontales, $hoy, $rango_posible);
            if (!$ev_front) {

                $texto = "<br>entro a obtener rango menor {$rango_posible->ran_nombre}";
                FUNCIONES::eco($texto);

                $sql_rp = "select * from rango 
                    where ran_id=$rango_posible->ran_rango_menor";
                $rango_posible = FUNCIONES::objeto_bd_sql($sql_rp);
                continue;
            }

            $ev_asoc = MLM::evaluar_asociados($vdo_id, $red_completa, $hoy, $rango_posible);
            if (!$ev_asoc) {

                $texto = "<br>entro a obtener rango menor {$rango_posible->ran_nombre}";
                FUNCIONES::eco($texto);

                $sql_rp = "select * from rango 
                    where ran_id=$rango_posible->ran_rango_menor";
                $rango_posible = FUNCIONES::objeto_bd_sql($sql_rp);
                continue;
            }

            $sev_front = ($ev_front) ? "true" : "false";
            $sev_asoc = ($ev_asoc) ? "true" : "false";
            $sev_piernas = ($ev_piernas) ? "true" : "false";

            $desc = "({$vdo_id})" . $rango_posible->ran_nombre . "-" . $sev_front . "-" . $sev_asoc . "-" . $sev_piernas;
            // FUNCIONES::bd_query("insert into dump(descr)values('$desc')");

            $sw = FALSE;
            $texto = "<p style='color:red'>{$rango_posible->ran_nombre} - $vdo_id</p>";
            FUNCIONES::eco($texto);
            $s_rango = $rango_posible;
        }

        if ($s_rango === NULL) {
            FUNCIONES::eco("El s_rango es NULL...");
            $s_rango = FUNCIONES::objeto_bd_sql("select * from rango where ran_id=1");
        }

        $s_rango = MLM::rango_eterno($vdo_id, $s_rango);

        $texto = "<p style='color:blue'>vamos a retornar el rango...{$s_rango->ran_nombre} => vdo_id:$vdo_id</p>";
        FUNCIONES::eco($texto);
        return $s_rango;
    }

    public static function evaluar_frontales($vdo_id, $arr_front, $hoy, $rango_posible) {
        FUNCIONES::eco("INI: EVALUAR_FRONTALES DE => $vdo_id");
        $cant_frontales = count($arr_front);
        $texto = "<p>tam arr_front => $cant_frontales</p>";
        FUNCIONES::eco($texto);
        $b = FALSE;
        $cont_front_al_dia = 0;
        for ($i = 0; $i < $cant_frontales; $i++) {

            $front = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id={$arr_front[$i]} 
                and vdo_estado='Habilitado'");
            FUNCIONES::eco("EVALUANDO AL FRONTAL => " . $front->vdo_id);

            if (MLM::esta_al_dia($front, $hoy)) {
                $cont_front_al_dia++;
                FUNCIONES::eco("ESTA AL DIA FRONTAL => " . $front->vdo_id);
            } else {
                FUNCIONES::eco("NO ESTA AL DIA FRONTAL => " . $front->vdo_id);
            }

            if ($cont_front_al_dia == $rango_posible->ran_hijos_directos) {
                $b = TRUE;
                break;
            }
        }

        if ($b) {
            FUNCIONES::eco("ESTAN AL DIA TODOS LOS FRONTALES({$rango_posible->ran_hijos_directos}) PARA RANGO:{$rango_posible->ran_nombre}.");
        } else {
            FUNCIONES::eco("SOLO ESTAN AL DIA {$cont_front_al_dia} FRONTALES({$rango_posible->ran_hijos_directos}) PARA RANGO:{$rango_posible->ran_nombre}.");
        }

        FUNCIONES::eco("FIN: EVALUAR_FRONTALES");
        return $b;
    }

    public static function evaluar_asociados($vdo_id, $arr_red, $hoy, $rango_posible) {
        FUNCIONES::eco("INI: EVALUAR_ASOCIADOS DE => $vdo_id");
        $cant_red = count($arr_red);
        $texto = "<p>tam arr_red => $cant_red</p>";
        FUNCIONES::eco($texto);
        $b = FALSE;
        $cont_asoc_al_dia = 0;
        for ($i = 0; $i < $cant_red; $i++) {

            $asoc = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id={$arr_red[$i]} and vdo_estado='Habilitado'");
            FUNCIONES::eco("EVALUANDO AL ASOCIADO => " . $asoc->vdo_id);
            if (MLM::esta_al_dia($asoc, $hoy)) {
                $cont_asoc_al_dia++;
                FUNCIONES::eco("ESTA AL DIA ASOCIADO => " . $asoc->vdo_id);
            } else {
                FUNCIONES::eco("NO ESTA AL DIA ASOCIADO => " . $asoc->vdo_id);
            }

            if ($cont_asoc_al_dia == $rango_posible->ran_min_asociados) {
                $b = TRUE;
                break;
            }
        }

        if ($b) {
            FUNCIONES::eco("ESTAN AL DIA TODOS LOS ASOCIADOS({$rango_posible->ran_min_asociados}) PARA RANGO:{$rango_posible->ran_nombre}.");
        } else {
            FUNCIONES::eco("SOLO ESTAN AL DIA {$cont_asoc_al_dia} ASOCIADOS({$rango_posible->ran_min_asociados}) PARA RANGO:{$rango_posible->ran_nombre}.");
        }

        FUNCIONES::eco("FIN: EVALUAR_ASOCIADOS");
        return $b;
    }

    public static function evaluar_piernas_calificadas($vdo_id, $rango_posible, $arr_frontales) {

        if ($rango_posible->ran_hijos_rango_menor == 0) {
            FUNCIONES::eco("ESTAN TODAS LAS PIERNAS CALIFICADAS({$rango_posible->ran_hijos_rango_menor}) PARA RANGO:{$rango_posible->ran_nombre}.");
            return TRUE;
        }

        FUNCIONES::eco("INI: EVALUAR_PIERNAS DE => $vdo_id - $rango_posible->ran_nombre");
        $cont = 0;
        $cant_frontales = count($arr_frontales);
        $b = FALSE;
        $texto = "<p>ev_pier del vdo:$vdo_id => $rango_posible->ran_nombre</p>";
//        FUNCIONES::eco($texto);
        for ($i = 0; $i < $cant_frontales; $i++) {

            $asoc = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id={$arr_frontales[$i]} 
                and vdo_estado='Habilitado'");
            $texto = "<p>evaluando al asoc {$asoc->vdo_id} => $asoc->vdo_rango_alcanzado >= $rango_posible->ran_rango_menor</p>";
            FUNCIONES::eco($texto);
            if ($asoc->vdo_rango_alcanzado >= $rango_posible->ran_rango_menor) {
                $cont++;
            }

            if ($cont == $rango_posible->ran_hijos_rango_menor) {
                $b = TRUE;
                break;
            }
        }

        if ($b) {
            FUNCIONES::eco("ESTAN TODAS LAS PIERNAS CALIFICADAS({$rango_posible->ran_hijos_rango_menor}) PARA RANGO:{$rango_posible->ran_nombre}.");
        } else {
            FUNCIONES::eco("SOLO ESTAN {$cont} PIERNAS CALIFICADAS({$rango_posible->ran_hijos_rango_menor}) PARA RANGO:{$rango_posible->ran_nombre}.");
        }

        FUNCIONES::eco("EL CONTADOR => $cont");
        FUNCIONES::eco("FIN: EVALUAR_PIERNAS");
        return $b;
    }

    public static function actualizar_rango($vdo_id, $rango, $pdo_id) {

        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$vdo_id");
        $ademas = ($rango->ran_id > $vendedor->vdo_rango_alcanzado) ? ",vdo_rango_alcanzado=$rango->ran_id" : "";
        $sql_upd = "update vendedor set vdo_rango_actual=$rango->ran_id $ademas where vdo_id=$vdo_id";
        FUNCIONES::bd_query($sql_upd);

        $rango_vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_rango
            where vran_vdo_id=$vdo_id and vran_pdo_id=$pdo_id and vran_eliminado='No'");
        $hoy = date("Y-m-d H:i:s");
        if ($rango_vendedor) {
            $sql_upd_ran = "update vendedor_rango set vran_ran_id=$rango->ran_id,
                vran_fecha_mod='$hoy',
                vran_usu_mod='{$_SESSION[id]}'
                where vran_id=$rango_vendedor->vran_id";
            FUNCIONES::bd_query($sql_upd_ran);
        } else {
            $sql_ins = "insert into vendedor_rango(
                vran_vdo_id,vran_ran_id,
                vran_pdo_id,vran_fecha_cre,
                vran_usu_id
                )values(
                '$vdo_id','{$rango->ran_id}',
                '$pdo_id','$hoy',
                '{$_SESSION[id]}'
                )";
            FUNCIONES::bd_query($sql_ins);
        }
    }

    public static function hijo_generacion($vdo_mayor, $vdo_menor) {
        $sql_menor = "select vdo_nivel as campo from vendedor where vdo_id='$vdo_menor'";
        $sql_mayor = "select vdo_nivel as campo from vendedor where vdo_id='$vdo_mayor'";

        $nivel_menor = FUNCIONES::atributo_bd_sql($sql_menor);
        $nivel_mayor = FUNCIONES::atributo_bd_sql($sql_mayor);

        return abs(($nivel_menor * 1) - ($nivel_mayor * 1));
    }

    public static function enviar_notificacion($datos) {
        $objeto = (object) $datos;
        $sql_ins = "insert into vendedor_notificacion(vdonot_fecha,vdonot_hora,
                                                            vdonot_usu_id,vdonot_mensaje,
                                                            vdonot_tipo,vdonot_vdo_id,vdonot_estado)
                                                values('$objeto->fecha','$objeto->hora',
                                                       '$objeto->usuario','$objeto->mensaje',
                                                        '$objeto->tipo','$objeto->vendedor','Pendiente')";
        FUNCIONES::bd_query($sql_ins);
    }

    public static function hijos_directos($vdo_id) {
        $sql = "select vdo_id from vendedor where vdo_vendedor_id='$vdo_id'
                    and vdo_estado='Habilitado'";
        $hijos = FUNCIONES::objetos_bd_sql($sql);
        return $hijos->get_num_registros();
    }

    public static function hijos_rango($vdo_id, $rango) {
        $nivel = 0;
        $red = MLM::obtener_red($vdo_id, $nivel, FALSE, 100);
        $hijos = 0;
        for ($i = 0; $i < count($red); $i++) {
            if (MLM::es_rango($red[$i], $rango)) {
                $hijos++;
            }
        }
        return $hijos;
    }

    public static function es_rango($vdo_id, $rango) {
        $sql = "select vran_id from vendedor_rango where vran_vdo_id='$vdo_id'
                    and vran_ran_id >='$rango' and vran_eliminado = 'No'";
        $vdo_ran = FUNCIONES::objetos_bd_sql($sql);

        if ($vdo_ran->get_num_registros() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function mantiene_rango($vdo_id) {

        $rango_actual = MLM::rango_actual($vdo_id);
        $hijos_directos = MLM::hijos_directos($vdo_id);
        $rango_menor = $rango_actual->ran_rango_menor;
        $hijos_rango = MLM::hijos_rango($vdo_id, $rango_menor);
        $b = FALSE;
        if (($hijos_directos >= $rango_actual->ran_hijos_directos) &&
                ($hijos_rango >= $rango_actual->ran_hijos_rango_menor)) {
            $b = TRUE;
        }
        return $b;
    }

    public static function cacular_montos_bonos_($data) {  //DEPRECATED 25/04/2017
        $data = (object) $data;
        $objeto = FUNCIONES::objeto_bd_sql("select * from venta 
            inner join lote on (ven_lot_id=lot_id)
            inner join zona on (lot_zon_id=zon_id)
            inner join urbanizacion on (ven_urb_id=urb_id)
            where ven_id=$data->ven_id");
        $res = new stdClass();
        if ($objeto) {
            $zon_precio = $objeto->zon_precio / (1 + ($objeto->urb_porc_red / 100));
            $aumento_red = $zon_precio * ($objeto->urb_porc_red / 100);
            $precio_red = $zon_precio * (1 + ($objeto->urb_porc_red / 100));
            $precio_vexsa = $precio_red * (1 + ($objeto->urb_porc_empresa / 100));
            $BIR = $precio_vexsa * ($objeto->urb_porc_ci_multinivel / 100);
            $saldo_red = $aumento_red - $BIR;
            $BRA = round($saldo_red / 7, 2);
            $porc_BRA = $BRA / $objeto->urb_nro_cuotas_multinivel;
            $BRA_mes = intval($porc_BRA * $objeto->lot_superficie);
            $BRA_total = $BRA_mes * 7 * $objeto->urb_nro_cuotas_multinivel;


            $precio_venta = ($precio_vexsa * $objeto->lot_superficie) + ($objeto->urb_nro_cuotas_multinivel * $objeto->urb_monto_seguro_cuota);
//            $bono_inicial = $precio_venta * ($objeto->urb_porc_ci_multinivel / 100);

            if ($data->oferta > 0) {
                $ofertas = FUNCIONES::objetos_bd_sql("select * from oferta where of_id=$data->oferta limit 0,1");
                $bono_inicial = $objeto->ven_res_anticipo;
            } else {
                $bono_inicial = FUNCIONES::atributo_bd_sql("select urc_monto as campo from urbanizacion_rango_ci 
                where urc_monto < $objeto->ven_res_anticipo order by urc_monto desc limit 0,1") * 1;
            }

            $monto_red = $aumento_red * $objeto->lot_superficie;

            $res->BRA_TOTAL = $BRA_total;
            $res->BONO_INICIAL = $bono_inicial;
            $res->MONTO_RED = $monto_red;
            $saldo_bonos_prim = $monto_red - ($bono_inicial + $BRA_total);

            if ($saldo_bonos_prim > 0) {
                $res->FED = ($objeto->urb_porc_fed / 100) * $saldo_bonos_prim;
                $res->BEV = ($objeto->urb_porc_bev / 100) * $saldo_bonos_prim;
                $res->FDR = ($objeto->urb_porc_fdr / 100) * $saldo_bonos_prim;
            } else {
                $res->FED = 0;
                $res->BEV = 0;
                $res->FDR = 0;
            }

            $sql_upd = "update venta set ven_monto_red=$monto_red,
                ven_saldo_red=$monto_red,
                ven_bono_inicial=$bono_inicial,
                ven_bono_bra=$BRA_total,
                ven_bono_fed=$res->FED,
                ven_bono_bev=$res->BEV,
                ven_bono_fdr=$res->FDR where ven_id=$data->ven_id";
            FUNCIONES::bd_query($sql_upd);
        }

        return $res;
    }

    public static function calcular_montos_bonos($data) {
        $data = (object) $data;
        $objeto = FUNCIONES::objeto_bd_sql("select * from venta 
            inner join lote on (ven_lot_id=lot_id)
            inner join zona on (lot_zon_id=zon_id)
            inner join urbanizacion on (ven_urb_id=urb_id)
            where ven_id=$data->ven_id");
        $res = new stdClass();
        if ($objeto) {

            $lote_mlm = FUNCIONES::objeto_bd_sql("select * from lote_multinivel where lm_lot_id=$objeto->lot_id");

            if ($lote_mlm === NULL) {
                $res->BRA_TOTAL = 0;
                $res->BONO_INICIAL = 0;
                $res->MONTO_RED = 0;
                $res->FED = 0;
                $res->BEV = 0;
                $res->FDR = 0;
                return $res;
            }

            $BRA_total = $lote_mlm->lm_bra;

			$bono_inicial = 0;
			if ($objeto->ven_tipo == 'Credito') {
				if ($data->oferta > 0) {
					$ofertas = FUNCIONES::objetos_bd_sql("select * from oferta where of_id=$data->oferta limit 0,1");
					$bono_inicial = $objeto->ven_res_anticipo;
				} else {
					$bono_inicial = ($objeto->ven_res_anticipo > $lote_mlm->lm_comision_base) ? $lote_mlm->lm_comision_base : $objeto->ven_res_anticipo;
				}
			} else {
				$bono_inicial = $objeto->ven_res_anticipo;
			}

            $monto_red = $BRA_total + $bono_inicial;

            $res->BRA_TOTAL = $BRA_total;
            $res->BONO_INICIAL = $bono_inicial;
            $res->MONTO_RED = $monto_red;
            $saldo_bonos_prim = $monto_red - ($bono_inicial + $BRA_total);

            if ($saldo_bonos_prim > 0) {
                $res->FED = ($objeto->urb_porc_fed / 100) * $saldo_bonos_prim;
                $res->BEV = ($objeto->urb_porc_bev / 100) * $saldo_bonos_prim;
                $res->FDR = ($objeto->urb_porc_fdr / 100) * $saldo_bonos_prim;
            } else {
                $res->FED = 0;
                $res->BEV = 0;
                $res->FDR = 0;
            }

            $sql_upd = "update venta set 
                ven_monto_red=$monto_red,
                ven_saldo_red=$monto_red,
                ven_bono_inicial=$bono_inicial,
                ven_bono_bra=$BRA_total,
                ven_bono_fed=$res->FED,
                ven_bono_bev=$res->BEV,
                ven_bono_fdr=$res->FDR
                where ven_id=$data->ven_id";
            FUNCIONES::bd_query($sql_upd);
        }

        return $res;
    }

    public static function insertar_comisiones_cobro($data) {
        require_once('clases/comisiones.class.php');
        COMISION::insertar_comisiones_cobro($data);
    }

    public static function anular_afiliado($venta) {
        $afiliado = FUNCIONES::objeto_bd_sql("select * from vendedor
            where vdo_int_id=$venta->ven_int_id
            and vdo_venta_inicial=$venta->ven_id");
        $conec = new ADO();
        $resp = new stdClass();
        if ($afiliado) {

            $hijos = FUNCIONES::lista_bd_sql("select * from vendedor 
                where vdo_vendedor_id=$afiliado->vdo_id");

            if (count($hijos) == 0) {
                $resp->exito = TRUE;
                $sql_del = "delete from vendedor where vdo_id=$afiliado->vdo_id";
                FUNCIONES::eco($sql_del);
                $conec->ejecutar($sql_del);
            } else {
                $resp->exito = FALSE;
                $resp->mensaje = "No puede anularse la venta, ya que el cliente tiene afiliados al cual patrocina.";
            }
        } else {
            $resp->exito = TRUE;
        }
        return $resp;
    }

    public static function generar_bonos($data) {
        FUNCIONES::eco(date("d/m/Y H:i:s"));
        require_once('comisiones.class.php');
        $data = (object) $data;

        if ($data->origen == 'vista_previa') {
            $marca_tmp = "tmp_" . time();
        }

        MLM::cargar_rangos_eternos();

//        print_r($data);
//        return;
        $pdo_id = $data->pdo_id;
        $vdo_id = $data->vdo_id * 1;

        $f_vdo = "";
        if ($vdo_id > 0) {
            $vdo_selec = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$vdo_id");
            $profundidad = $vdo_selec->vdo_nivel + 1000;
            $nivel = 10000;
            $red_vdo = MLM::obtener_red($vdo_selec->vdo_id, $nivel, TRUE, $profundidad, '');

            // echo "<pre>";
            // print_r($red_vdo);
            // echo "</pre>";

            if (_ENTORNO == 'DEV' && $_SESSION[id] == 'admin') {
                _PRINT::pre($red_vdo);
            }

            if (count($red_vdo) > 0) {
                $s_ids = implode(",", $red_vdo);
                $f_vdo = " and vdo_id in($s_ids)";
            }
        }

        FUNCIONES::eco("INI:establecer_niveles" . date("d/m/Y H:i:s"));
        MLM::establecer_niveles();
        FUNCIONES::eco("FIN:establecer_niveles" . date("d/m/Y H:i:s"));
        // return;

        $vendedores = FUNCIONES::lista_bd_sql("select * from vendedor 
        inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
        where vdo_estado='Habilitado'
        and vgru_nombre='AFILIADOS'
        $f_vdo
        order by vdo_nivel desc,vdo_id desc limit 0,10000");

        $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo where pdo_id=$pdo_id");

        $fecha_calculo = $periodo->pdo_fecha_fin;

        $ahora = date("Y-m-d");

        foreach ($vendedores as $vdo) {
            $rango = MLM::calcular_rango_actual($vdo->vdo_id, $fecha_calculo);
            MLM::actualizar_rango($vdo->vdo_id, $rango, $pdo_id);
        }

        FUNCIONES::eco("llegando a la compresion...");

        MLM::compresion_dinamica($fecha_calculo, $vdo_id * 1);
        FUNCIONES::eco("despues de la compresion...");
       // return;

        $d = explode('-', $fecha_calculo);
        $periodo = $d[0] . "-" . $d[1];

        $tabla_accion = "";
        if ($data->origen == 'vista_previa') {

            $tabla_accion = "comision_tmp";

            if ($vdo_id > 0) {

                $sql_delete = "delete from comision_tmp where com_vdo_id=$vdo_id and com_pdo_id=$pdo_id";
            } else {
                $sql_delete = "truncate table comision_tmp";
            }
            FUNCIONES::eco("SQL_DEL:" . $sql_delete);
            FUNCIONES::bd_query($sql_delete);
        } else if ($data->origen == 'agregar') {
            $tabla_accion = "comision";
        } else if ($data->origen == 'modificar') {
            $tabla_accion = "comision";
            COMISION::actualizar_comisiones(array('periodo' => $pdo_id, 'vendedor' => $vdo_id));
            $fecha_mod = date("Y-m-d H:i:s");
            $usu_mod = $_SESSION[id];
        }

        COMISION::set_tabla($tabla_accion);

        if ($vdo_id > 0) {
            $f_vdo = " and vdo_id=$vdo_id";
        }

        $sql_ventas = "select * from vendedor_tmp 
        where vdo_estado='Habilitado' $f_vdo";
        $texto = $sql_ventas;
        FUNCIONES::eco("<p style='color:blue'>" . $texto . "</p>");
        $vendedores = FUNCIONES::lista_bd_sql($sql_ventas);


        foreach ($vendedores as $vdo) {

            MLM::calcular_BIR($vdo->vdo_id, $fecha_calculo, $pdo_id, $marca_tmp);
            MLM::calcular_BVI($vdo->vdo_id, $fecha_calculo, $pdo_id, $marca_tmp);
            MLM::calcular_BRA($vdo->vdo_id, $fecha_calculo, $pdo_id, $marca_tmp);
        }

        $sql_ventas_oferta = "select * from vendedor_tmp 
        where vdo_estado='Habilitado' $f_vdo";
        $texto = $sql_ventas_oferta;
        FUNCIONES::eco("<p style='color:blue'>" . $texto . "</p>");
        $vendedores = FUNCIONES::lista_bd_sql($sql_ventas_oferta);


        foreach ($vendedores as $vdo) {

            MLM::calcular_BIR_oferta($vdo->vdo_id, $fecha_calculo, $pdo_id, $marca_tmp);
            MLM::calcular_BVI_oferta($vdo->vdo_id, $fecha_calculo, $pdo_id, $marca_tmp);
            MLM::calcular_BRA_oferta($vdo->vdo_id, $fecha_calculo, $pdo_id, $marca_tmp);
        }

        FUNCIONES::eco(date("d/m/Y H:i:s"));


        $asoc_diamantes = MLM::diamantes_para_cobrar($pdo_id, $periodo);
        $can_asoc = count($asoc_diamantes);

        $monto_repartir = MLM::fondo_diamante($periodo);


        if ($monto_repartir > 0 && $can_asoc > 0) {

            $monto_comision = $monto_repartir / $can_asoc;
            $porc_fed = $monto_comision * 100 / $monto_repartir;
            foreach ($asoc_diamantes as $asoc) {

                $datos = array(
                    'venta' => 0,
                    'vendedor' => $asoc,
                    'monto' => $monto_comision,
                    'moneda' => 2,
                    'usuario' => $_SESSION[id],
                    'glosa' => 'FONDO DIAMANTE: ' . $periodo,
                    'fecha' => $fecha_calculo,
                    'tipo' => 'FED',
                    'periodo' => $pdo_id,
                    'marca_tmp' => $marca_tmp,
                    'porcentaje' => $porc_fed
                );

                COMISION::insertar_comision($datos);
            }
        }

        return $marca_tmp;
    }

//    obtener_red($vdo_id, &$nivel, $self = false, $profundidad = 0, $this_not = '', $tabla = 'vendedor') {
    public static function diamantes_para_cobrar($pdo_id, $periodo) {

        $sql = "select * from vendedor 
            inner join rango on(vdo_rango_actual=ran_id)
            inner join vendedor_rango on(ran_id=vran_ran_id and vran_pdo_id=$pdo_id AND vdo_id=vran_vdo_id)
            where ran_nombre='DIAMANTE' and vdo_estado='Habilitado'
            and vdo_id != 1";

        FUNCIONES::eco("SQL_DIAMANTE:" . $sql);
        $asoc_diamantes = FUNCIONES::lista_bd_sql($sql);

        $cant_ventas_diamante = FUNCIONES::atributo_bd_sql("select par_cant_ventas_diamante as campo from ad_parametro limit 0,1") * 1;
        $arr_diam = array();
        $arr_diam[] = 1;

        foreach ($asoc_diamantes as $asoc) {

            $nivel = 1000;
            $prof = $asoc->vdo_nivel + 7;
            $red_asoc = MLM::obtener_red($asoc->vdo_id, $nivel, TRUE, $prof);

            if (count($red_asoc) > 0) {
                $s_red = implode(',', $red_asoc);

                $sql_ventas = "select * from venta 
                    inner join vendedor on(ven_vdo_id=vdo_id)
                    where left(ven_fecha,7)='$periodo'
                    and ven_multinivel='si'
                    and ven_estado in ('Pendiente','Pagado')
                    and vdo_estado='Habilitado'
                    and vdo_id in ($s_red)";

                $ventas = FUNCIONES::lista_bd_sql($sql_ventas);

                if (count($ventas) >= $cant_ventas_diamante) {
                    $arr_diam[] = $asoc->vdo_id;
                }
            }
        }

        return $arr_diam;
    }

    public static function fondo_diamante($periodo) {

        $fd = FUNCIONES::atributo_bd_sql("select sum(ven_bono_fed)as campo from venta                     
                where left(ven_fecha,7)='$periodo'
                and ven_multinivel='si'
                and ven_estado in ('Pendiente','Pagado')") * 1;
        return $fd;
    }

    public static function json_red($vdo_id, $profundidad) {
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$vdo_id");
        $nivel = 1000;
        $profundidad += $vendedor->vdo_nivel;
        $red = MLM::obtener_red($vendedor->vdo_id, $nivel, TRUE, $profundidad);

        if (count($red)) {
            $s_vdo_ids = implode(',', $red);
            $sql_vdos = "select * from vendedor
                inner join interno on (vdo_int_id=int_id)
                where vdo_id in ($s_vdo_ids)
                order by vdo_nivel desc";
//            echo $sql_vdos;
            $vendedores = FUNCIONES::lista_bd_sql($sql_vdos);

            foreach ($vendedores as $vdo) {
                $nodo = new stdClass();
                $nodo->id = $vdo->vdo_id;
                $nodo->label = $vdo->int_nombre . " " . $vdo->int_apellido;
                $nodo->image = "estrella.png";
                $nodo->shape = "image";
            }
        }
    }

    public static function compresion_dinamica($hoy, $vdo_id = 0) {

        $red = array();
        if ($vdo_id > 0) {
            $nivel = 1000;
            $red = MLM::obtener_red($vdo_id, $nivel, TRUE, 1000, '');
        }

        MLM::clonar_red($red);
        MLM::depurar_inactivos($hoy, $red);
        MLM::ajustar_arbol($red, $vdo_id);
    }

    public static function clonar_red($red) {

        if (count($red) > 0) {
            $s_afils = implode(',', $red);
            $s_afils = " and vdo_id in ($s_afils)";
        }

        FUNCIONES::bd_query("truncate table vendedor_tmp");
        $sql_clon = "insert into vendedor_tmp select vdo.* from vendedor vdo
        inner join vendedor_grupo vg on(vdo.vdo_vgru_id=vg.vgru_id)
        where vg.vgru_nombre='AFILIADOS'
        $s_afils";
        FUNCIONES::bd_query($sql_clon);
    }

    public static function depurar_inactivos($hoy, $red) {
        if (count($red) > 0) {
            $s_afils = implode(',', $red);
            $s_afils = " and vdo_id in ($s_afils)";
        }

        $sql = "select * from vendedor_tmp where 1=1 $s_afils";

        $afiliados = FUNCIONES::lista_bd_sql($sql);

        foreach ($afiliados as $afil) {

            if (in_array($afil->vdo_id, self::$rangos_eternos)) {
                continue;
            }

            if (!MLM::esta_al_dia($afil, $hoy)) {
                $sql_upd = "update vendedor_tmp set vdo_estado='Deshabilitado'
                    where vdo_id=$afil->vdo_id";
                FUNCIONES::bd_query($sql_upd);
            }
        }
    }

    public static function ajustar_arbol($red, $raiz = 0) {

        if (count($red) > 0) {
            $s_afils = implode(',', $red);
            $s_afils = " and vdo_id in ($s_afils)";
        }

        $afiliados = FUNCIONES::lista_bd_sql("select * from vendedor_tmp
        where vdo_estado='Deshabilitado' 
        $s_afils
        order by vdo_nivel asc");

        foreach ($afiliados as $afil) {
//            $sql_hijos = "select * from vendedor_tmp where vdo_vendedor_id=$afil->vdo_id";
//            $hijos = FUNCIONES::lista_bd_sql($sql_hijos);
//            foreach ($hijos as $hijo) {
//                $sql_
//            }
            $sql_padre = "select * from vendedor_tmp 
                where vdo_id=$afil->vdo_vendedor_id";
            $vdo_padre = FUNCIONES::objeto_bd_sql($sql_padre);

            if (_ENTORNO == 'DEV' && $_SESSION[id] == 'admin') {
                echo "VDO_PADRE 1:<pre>";
                print_r($vdo_padre);
                echo "</pre>";
            }

            if ($vdo_padre) {
                while ($vdo_padre->vdo_estado == 'Deshabilitado' && $vdo_padre->vdo_vendedor_id > 0) {

                    if (_ENTORNO == 'DEV' && $_SESSION[id] == 'admin') {
                        echo "while :<pre>";
                        print_r($vdo_padre);
                        echo "</pre>";
                    }

                    $vdo_padre = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
                    where vdo_id=$vdo_padre->vdo_vendedor_id");
                }

                if ($vdo_padre == NULL) {
                    $vdo_padre_id = 0;
                } else {
                    $vdo_padre_id = $vdo_padre->vdo_id;
                }
                //obtener_red($vdo_id, &$nivel, $self = false, $profundidad = 0, $this_not = '')
                $nivel = 1000;
//                $red_hijos = MLM::obtener_red($afil->vdo_id, $nivel, false, 1000, '', 'vendedor_tmp');
                $profundidad = $afil->vdo_nivel + 1;
                $red_hijos = MLM::obtener_red($afil->vdo_id, $nivel, false, $profundidad, '', 'vendedor_tmp');
//                $dif_niveles = $afil->vdo_nivel - $vdo_padre->vdo_nivel;


                if (_ENTORNO == 'DEV' && $_SESSION[id] == 'admin') {
                    echo "VDO_PADRE 2:<pre>";
                    print_r($vdo_padre);
                    echo "</pre>";
                }

                foreach ($red_hijos as $id_hijo) {
                    $hijo = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp where vdo_id=$id_hijo");
//                    $new_nivel = $hijo->vdo_nivel - $dif_niveles;
                    $new_nivel = $vdo_padre->vdo_nivel + 1;
                    $sql_upd_nivel = "update vendedor_tmp set vdo_nivel=$new_nivel,
                        vdo_vendedor_id=$vdo_padre_id
                        where vdo_id=$hijo->vdo_id";
                    FUNCIONES::bd_query($sql_upd_nivel);
                }
            } else {
                $sql_upd_padre0 = "update vendedor_tmp set vdo_vendedor_id=0,vdo_nivel=vdo_nivel - 1
                    where vdo_vendedor_id=$afil->vdo_id";
                FUNCIONES::bd_query($sql_upd_padre0);
            }
        }

        if ($raiz > 0) {
            $vdo_raiz = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp
                where vdo_id=$raiz");

            if ($vdo_raiz) {
                MLM::establecer_niveles($vdo_raiz->vdo_id, $vdo_raiz->vdo_nivel, 'vendedor_tmp');
            } else {
                MLM::establecer_niveles(0, 0, 'vendedor_tmp');
            }
        } else {
            MLM::establecer_niveles(0, 0, 'vendedor_tmp');
        }
    }

    public static function establecer_niveles($padre_id = 0, $nivel = 0, $tabla = 'vendedor') {
        $sql = "select * from $tabla 
            inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
            where vdo_vendedor_id=$padre_id 
            and vgru_nombre='AFILIADOS'";
        $raices = FUNCIONES::lista_bd_sql($sql);

        if ($padre_id == 0) {
            $nivel = 0;
        } else {
            $nivel++;
        }

        foreach ($raices as $raiz) {
            $sql_upd = "update $tabla set vdo_nivel=$nivel where vdo_id=$raiz->vdo_id";
            FUNCIONES::bd_query($sql_upd);
            MLM::establecer_niveles($raiz->vdo_id, $nivel, $tabla);
        }
    }

    //**CALCULO DE BONOS POR VENDEDOR DE ACUERDO A LA RED COMPRIMIDA**//
// obtener_red($vdo_id, &$nivel, $self = false, $profundidad = 0, $this_not = '', $tabla = 'vendedor')
    public static function calcular_BIR($vdo_id, $hoy, $pdo_id, $marca_tmp = '') {

        $nivel = 1000;
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp where vdo_id=$vdo_id");
        $prof = $vendedor->vdo_nivel + 1;
        $arr_ids_hijos_directos = MLM::obtener_red($vdo_id, $nivel, FALSE, $prof, '', 'vendedor_tmp');

        $cant_ids = count($arr_ids_hijos_directos);

        if ($cant_ids > 0) {
            $s_ids = implode(",", $arr_ids_hijos_directos);
//            $s_ids = "($s_ids)";
            $s_periodo = substr($hoy, 0, 7);

            $sql_ventas_hijos = "select * from vendedor_tmp
                inner join venta on(vdo_int_id=ven_int_id and ven_id=vdo_venta_inicial)
                where left(ven_fecha,7)='$s_periodo'
                and ven_multinivel='si'             
                and ven_comision_gen='1'                
                and ven_estado in ('Pendiente','Pagado')
                
                and vdo_id in ($s_ids)
                and ven_id not in (
                    select vof_ven_id from venta_oferta where vof_estado='Pendiente'
                )";

            $ventas = FUNCIONES::lista_bd_sql($sql_ventas_hijos);

            foreach ($ventas as $venta) {
                $_comisiones = json_decode($venta->ven_porc_comisiones);
                $comisiones = $_comisiones->array;

                $porc = $comisiones[0];

                if ($venta->ven_tipo == 'Contado') {
                    $params_mlm = FUNCIONES::objeto_bd_sql("select * from lote_multinivel 
                        where lm_lot_id=$venta->ven_lot_id");

                    $porc = $params_mlm->lm_comision_contado;
                }

                $monto = $venta->ven_bono_inicial;

                if ($porc > 0) {
                    $total_comisionado = 0;
                    $saldo = $monto;
                    $monto_comision = ($monto * $porc) / 100;
                    if ($monto_comision <= $saldo) {

                        $datos = array('venta' => $venta,
                            'vendedor' => $vendedor->vdo_id,
                            'monto' => $monto_comision,
                            'moneda' => $venta->ven_moneda,
                            'usuario' => $_SESSION[id],
                            'glosa' => 'INGRESO RAPIDO 1 (BIR Venta ' . $venta->ven_id . ')',
                            'fecha' => $hoy,
                            'tipo' => 'BIR',
                            'periodo' => $pdo_id,
                            'marca_tmp' => $marca_tmp,
                            'porcentaje' => $porc
                        );
                        COMISION::insertar_comision($datos);
                        $saldo -= $monto_comision;
                        $total_comisionado += $monto_comision;
                    }
                }
            }
        }
    }

    public static function calcular_BVI($vdo_id, $hoy, $pdo_id, $marca_tmp = '') {

        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
            inner join rango on (vdo_rango_actual=ran_id)
            where vdo_id=$vdo_id");

        $sql_hijos_desh_pri_nivel = "select vdo_id from vendedor_tmp 
        where vdo_vendedor_id='$vdo_id' and vdo_estado='Deshabilitado'";
        $hijos_desh = FUNCIONES::lista_bd_sql($sql_hijos_desh_pri_nivel);
        
        $arr_hijos_desh = array();
        foreach ($hijos_desh as $h) {
            $arr_hijos_desh[] = $h->vdo_id;
        }
        
        $s_ids_desh = "";
        if (count($arr_hijos_desh) > 0) {
            $s_ids_desh = implode(',', $arr_hijos_desh);
            $s_ids_desh = " and vdo_id not in ($s_ids_desh)";
        }
        
        if ($vendedor->ran_gen_com_ventas > 0) {

            $prof = $vendedor->vdo_nivel + $vendedor->ran_gen_com_ventas;
            $nivel = 1000;
            $arr_ids_hijos = MLM::obtener_red($vendedor->vdo_id, $nivel, FALSE, $prof, '', 'vendedor_tmp');
            $cant_ids = count($arr_ids_hijos);
                        

            if ($cant_ids > 0) {
                $s_ids = implode(",", $arr_ids_hijos);                
                for ($i = 1; $i <= $vendedor->ran_gen_com_ventas; $i++) {

                    $nivel = $vendedor->vdo_nivel + $i;
                    $s_periodo = substr($hoy, 0, 7);


                    $sql_ventas_hijos = "select * from vendedor_tmp
                    inner join venta on(vdo_id=ven_vdo_id)
                    where left(ven_fecha,7)='$s_periodo'
                    and ven_multinivel='si'                    
                    and ven_comision_gen='1'                
                    and ven_estado in ('Pendiente','Pagado')
                    
                    and vdo_id in ($s_ids)
                    $s_ids_desh
                    and vdo_nivel = $nivel
                    and ven_id not in (
                        select vof_ven_id from venta_oferta where vof_estado='Pendiente'
                    )";

                    $ventas = FUNCIONES::lista_bd_sql($sql_ventas_hijos);
                    
                    foreach ($ventas as $venta) {

                        $_comisiones = json_decode($venta->ven_porc_comisiones);
                        $comisiones = $_comisiones->array;
                        $obj_afi = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp where vdo_venta_inicial='$venta->ven_id'");
                        $k = ($obj_afi->vdo_nivel - $vendedor->vdo_nivel) - 1;
                        $porc = $comisiones[$k] / 2;
                        $monto = $venta->ven_bono_inicial;
                        $gen_i = $k + 1;

                        if ($porc > 0) {

                            $total_comisionado = 0;
                            $saldo = $monto;
                            $monto_comision = ($monto * $porc) / 100;

                            if ($monto_comision <= $saldo) {

                                $datos = array(
                                    'venta' => $venta,
                                    'vendedor' => $vendedor->vdo_id,
                                    'monto' => $monto_comision,
                                    'moneda' => $venta->ven_moneda,
                                    'usuario' => $_SESSION[id],
                                    'glosa' => "INGRESO RAPIDO $gen_i  (BVI Venta " . $venta->ven_id . ')',
                                    'fecha' => $hoy,
                                    'tipo' => 'BVI',
                                    'periodo' => $pdo_id,
                                    'marca_tmp' => $marca_tmp,
                                    'porcentaje' => $porc
                                );
                                COMISION::insertar_comision($datos);
                                $saldo -= $monto_comision;
                                $total_comisionado += $monto_comision;
                            }
                        }
                    }
                }
            }
        }
    }

    public static function calcular_BRA($vdo_id, $hoy, $pdo_id, $marca_tmp = '') {
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
            inner join rango on (vdo_rango_actual=ran_id)
            where vdo_id=$vdo_id");

//        $vendedor->ran_gen_com_cobros = 7;

        if ($vendedor->ran_gen_com_cobros > 0) {

            $prof = $vendedor->vdo_nivel + $vendedor->ran_gen_com_cobros;
            $nivel = 1000;
            $arr_ids_hijos = MLM::obtener_red($vendedor->vdo_id, $nivel, TRUE, $prof, '', 'vendedor_tmp');
            $cant_ids = count($arr_ids_hijos);

            if ($cant_ids > 0) {
                $s_ids = implode(",", $arr_ids_hijos);
                for ($i = 0; $i <= $vendedor->ran_gen_com_cobros; $i++) {

                    $nivel = $vendedor->vdo_nivel + $i;
                    $s_periodo = substr($hoy, 0, 7);

                    $sql_ventas_hijos = "select * from vendedor_tmp
                    inner join venta on(vdo_id=ven_vdo_id)
                    where left(ven_fecha,7)<='$s_periodo'
                    and ven_multinivel='si'
                    and ven_estado in ('Pendiente','Pagado')
                    
                    and vdo_id in ($s_ids)
                    and vdo_nivel = $nivel
                    and ven_id not in (
                        select vof_ven_id from venta_oferta where vof_estado='Pendiente'
                    )";

                    
                    FUNCIONES::eco("SQL-BRA:<BR/>" . $sql_ventas_hijos);

                    $ventas = FUNCIONES::lista_bd_sql($sql_ventas_hijos);

                    foreach ($ventas as $venta) {
                        
                        if ($i == 0) {
                            $desc = MLM::es_descendiente($vendedor->vdo_id, $venta->ven_id);
                            if ($desc == NULL) {
                                FUNCIONES::eco("<span style='color:blue'>vendedor_vdo_id:$vendedor->vdo_id - venta_ven_id:$venta->ven_id</span>");
//                                echo ("<span style='color:blue'>vendedor_vdo_id:$vendedor->vdo_id - venta_ven_id:$venta->ven_id</span>");
                                continue;
                            } else {
                                FUNCIONES::eco("<span style='color:blue'>ES DESCENDIENTE - vendedor_vdo_id:$vendedor->vdo_id - venta_ven_id:$venta->ven_id</span>");
//                                echo ("<span style='color:blue'>ES DESCENDIENTE - vendedor_vdo_id:$vendedor->vdo_id - venta_ven_id:$venta->ven_id</span>");
                            }
                        }

                        if (!MLM::venta_al_dia($venta, $hoy, TRUE)) {
                            continue;
                        }

                        $vdo_venta = FUNCIONES::objeto_bd_sql("select * from vendedor
                            where vdo_venta_inicial = {$venta->ven_id}");

                        if ($vdo_venta) {
                            if (!MLM::esta_activo($vdo_venta, $hoy)) {
                                continue;
                            }
                        }

                        if ($venta->ven_bono_bra == 0) {
                            continue;
                        }

                        $comision_cuota = FUNCIONES::objeto_bd_sql("select * from comision_cobro 
                            where comcob_ven_id=$venta->ven_id order by comcob_id asc limit 0,1");

                        $monto_comision = $comision_cuota->comcob_monto_cuota;
                        $monto = $venta->ven_bono_bra;

//                        $porc = $monto_comision * 100 / $monto;
                        $porc = 100;


                        if ($porc > 0) {
                            $k = ($venta->vdo_nivel - $vendedor->vdo_nivel);
                            $gen_i = $k + 1;
                            $total_comisionado = 0;
                            $saldo = $monto;

                            if ($monto_comision <= $saldo) {

                                $datos = array(
                                    'venta' => $venta,
                                    'vendedor' => $vendedor->vdo_id,
                                    'monto' => $monto_comision,
                                    'moneda' => $venta->ven_moneda,
                                    'usuario' => $_SESSION[id],
                                    'glosa' => "RESIDUAL ABIERTO $gen_i (BRA Venta " . $venta->ven_id . ')',
                                    'fecha' => $hoy,
                                    'tipo' => 'BRA',
                                    'periodo' => $pdo_id,
                                    'marca_tmp' => $marca_tmp,
                                    'porcentaje' => $porc
                                );
                                COMISION::insertar_comision($datos);
                                $saldo -= $monto_comision;
                                $total_comisionado += $monto_comision;
                            }
                        }
                    }
                }
            }
        } else {
            FUNCIONES::eco("la generacion: {$vendedor->ran_gen_com_cobros} de vdo_id:{$vendedor->vdo_id} al parecer no alcanzó para BRA...");
        }
    }

    public static function calcular_BIR_oferta($vdo_id, $hoy, $pdo_id, $marca_tmp = '') {

        $nivel = 1000;
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp where vdo_id=$vdo_id");
        $prof = $vendedor->vdo_nivel + 1;
        $arr_ids_hijos_directos = MLM::obtener_red($vdo_id, $nivel, FALSE, $prof, '', 'vendedor_tmp');

        $cant_ids = count($arr_ids_hijos_directos);

        if ($cant_ids > 0) {
            $s_ids = implode(",", $arr_ids_hijos_directos);
//            $s_ids = "($s_ids)";
            $s_periodo = substr($hoy, 0, 7);
            $sql_ventas_hijos = "select * from vendedor_tmp
                inner join venta on(vdo_int_id=ven_int_id and ven_id=vdo_venta_inicial)
                where left(ven_fecha,7)<='$s_periodo'
                and ven_multinivel='si'                
                and ven_estado in ('Pendiente','Pagado')
                
                and vdo_id in ($s_ids)
                and ven_id in (
                    select vof_ven_id from venta_oferta where vof_estado='Pendiente'
                )";
            $ventas = FUNCIONES::lista_bd_sql($sql_ventas_hijos);
            FUNCIONES::eco("BIR OFERTA");
            FUNCIONES::eco($sql_ventas_hijos);

            foreach ($ventas as $venta) {

                if (!MLM::venta_al_dia($venta, $hoy)) {
                    continue;
                }

                $oferta = FUNCIONES::objeto_bd_sql("select * from venta_oferta 
                            inner join oferta on(vof_of_id=of_id)
                            where vof_ven_id=$venta->ven_id
                            and vof_estado='Pendiente'");

                if ($oferta == NULL) {
                    continue;
                }

                $of_params = json_decode($oferta->vof_parametros_mod);

                if ($of_params->of_periodos_con_bir * 1 == 0) {
                    continue;
                }

                $_comisiones = json_decode($venta->ven_porc_comisiones);
                $comisiones = $_comisiones->array;
                $porc = $comisiones[0];
                $monto = $venta->ven_bono_inicial;

                if ($porc > 0) {
                    $total_comisionado = 0;
                    $saldo = $monto;
                    $monto_comision = ($monto * $porc) / 100;
                    if ($monto_comision <= $saldo) {

                        $datos = array('venta' => $venta,
                            'vendedor' => $vendedor->vdo_id,
                            'monto' => $monto_comision,
                            'moneda' => $venta->ven_moneda,
                            'usuario' => $_SESSION[id],
                            'glosa' => 'INGRESO RAPIDO 1 (BIR Venta ' . $venta->ven_id . ')' . "({$oferta->of_nombre})",
                            'fecha' => $hoy,
                            'tipo' => 'BIR',
                            'periodo' => $pdo_id,
                            'marca_tmp' => $marca_tmp,
                            'porcentaje' => $porc
                        );
                        COMISION::insertar_comision($datos);
                        $saldo -= $monto_comision;
                        $total_comisionado += $monto_comision;
                    }
                }
            }
        }
    }

    public static function calcular_BVI_oferta($vdo_id, $hoy, $pdo_id, $marca_tmp = '') {

        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
            inner join rango on (vdo_rango_actual=ran_id)
            where vdo_id=$vdo_id");
        
        $sql_hijos_desh_pri_nivel = "select vdo_id from vendedor_tmp 
        where vdo_vendedor_id='$vdo_id' and vdo_estado='Deshabilitado'";
        $hijos_desh = FUNCIONES::lista_bd_sql($sql_hijos_desh_pri_nivel);
        
        $arr_hijos_desh = array();
        foreach ($hijos_desh as $h) {
            $arr_hijos_desh[] = $h->vdo_id;
        }
        
        $s_ids_desh = "";
        if (count($arr_hijos_desh) > 0) {
            $s_ids_desh = implode(',', $arr_hijos_desh);
            $s_ids_desh = " and vdo_id not in ($s_ids_desh)";
        }

        if ($vendedor->ran_gen_com_ventas > 0) {

            $prof = $vendedor->vdo_nivel + $vendedor->ran_gen_com_ventas;
            $nivel = 1000;
            $arr_ids_hijos = MLM::obtener_red($vendedor->vdo_id, $nivel, FALSE, $prof, '', 'vendedor_tmp');
            $cant_ids = count($arr_ids_hijos);

            if ($cant_ids > 0) {
                $s_ids = implode(",", $arr_ids_hijos);
                
                for ($i = 1; $i <= $vendedor->ran_gen_com_ventas; $i++) {

                    $nivel = $vendedor->vdo_nivel + $i;
                    $s_periodo = substr($hoy, 0, 7);
                    $sql_ventas_hijos = "select * from vendedor_tmp
                    inner join venta on(vdo_id=ven_vdo_id)
                    where left(ven_fecha,7)<='$s_periodo'
                    and ven_multinivel='si'                    
                    and ven_estado in ('Pendiente','Pagado')
                    
                    and vdo_id in ($s_ids)
                    and vdo_nivel = $nivel
                    $s_ids_desh
                    and ven_id in (
                        select vof_ven_id from venta_oferta where vof_estado='Pendiente'
                    )";
                                        
                    FUNCIONES::eco("BVI OFERTA");
                    FUNCIONES::eco($sql_ventas_hijos);
                    $ventas = FUNCIONES::lista_bd_sql($sql_ventas_hijos);
                    
                    
                    foreach ($ventas as $venta) {

                        if (!MLM::venta_al_dia($venta, $hoy)) {
                            continue;
                        }

                        $oferta = FUNCIONES::objeto_bd_sql("select * from venta_oferta 
                            inner join oferta on(vof_of_id=of_id)
                            where vof_ven_id=$venta->ven_id
                            and vof_estado='Pendiente'");

                        if ($oferta == NULL) {
                            continue;
                        }

                        $of_params = json_decode($oferta->vof_parametros_mod);

                        if ($of_params->of_periodos_con_bir * 1 == 0) {
                            continue;
                        }

                        $_comisiones = json_decode($venta->ven_porc_comisiones);
                        $comisiones = $_comisiones->array;
                        $obj_afi = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp where vdo_venta_inicial='$venta->ven_id'");
                        $k = ($obj_afi->vdo_nivel - $vendedor->vdo_nivel) - 1;
                        $porc = $comisiones[$k] / 2;
                        $monto = $venta->ven_bono_inicial;
                        $gen_i = $k + 1;

                        if ($porc > 0) {

                            $total_comisionado = 0;
                            $saldo = $monto;
                            $monto_comision = ($monto * $porc) / 100;

                            if ($monto_comision <= $saldo) {

                                $datos = array(
                                    'venta' => $venta,
                                    'vendedor' => $vendedor->vdo_id,
                                    'monto' => $monto_comision,
                                    'moneda' => $venta->ven_moneda,
                                    'usuario' => $_SESSION[id],
                                    'glosa' => "INGRESO RAPIDO $gen_i  (BVI Venta " . $venta->ven_id . ')' . "({$oferta->of_nombre})",
                                    'fecha' => $hoy,
                                    'tipo' => 'BVI',
                                    'periodo' => $pdo_id,
                                    'marca_tmp' => $marca_tmp,
                                    'porcentaje' => $porc
                                );
                                COMISION::insertar_comision($datos);
                                $saldo -= $monto_comision;
                                $total_comisionado += $monto_comision;
                            }
                        }
                    }
                }
            }
        }
    }

    public static function calcular_BRA_oferta($vdo_id, $hoy, $pdo_id, $marca_tmp = '') {
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
            inner join rango on (vdo_rango_actual=ran_id)
            where vdo_id=$vdo_id");

//        $vendedor->ran_gen_com_cobros = 7;

        if ($vendedor->ran_gen_com_cobros > 0) {

            $prof = $vendedor->vdo_nivel + $vendedor->ran_gen_com_cobros;
            $nivel = 1000;
            $arr_ids_hijos = MLM::obtener_red($vendedor->vdo_id, $nivel, TRUE, $prof, '', 'vendedor_tmp');
            $cant_ids = count($arr_ids_hijos);

            if ($cant_ids > 0) {
                $s_ids = implode(",", $arr_ids_hijos);
                for ($i = 0; $i <= $vendedor->ran_gen_com_cobros; $i++) {

                    $nivel = $vendedor->vdo_nivel + $i;
                    $s_periodo = substr($hoy, 0, 7);
                    $sql_ventas_hijos = "select * from vendedor_tmp
                    inner join venta on(vdo_id=ven_vdo_id)
                    where left(ven_fecha,7)<='$s_periodo'
                    and ven_multinivel='si'
                    and ven_estado in ('Pendiente','Pagado')
                    
                    and vdo_id in ($s_ids)
                    and vdo_nivel = $nivel
                    and ven_id in (
                        select vof_ven_id from venta_oferta where vof_estado='Pendiente'
                    )";


                    FUNCIONES::eco("BRA OFERTA");
                    FUNCIONES::eco($sql_ventas_hijos);

                    $ventas = FUNCIONES::lista_bd_sql($sql_ventas_hijos);

                    foreach ($ventas as $venta) {
                        
                        if ($i == 0) {
                            $desc = MLM::es_descendiente($vendedor->vdo_id, $venta->ven_id);
                            if ($desc == NULL) {
                                FUNCIONES::eco("<span style='color:blue'>vendedor_vdo_id:$vendedor->vdo_id - venta_ven_id:$venta->ven_id</span>");
                                continue;
                            } else {
                                FUNCIONES::eco("<span style='color:blue'>ES DESCENDIENTE - vendedor_vdo_id:$vendedor->vdo_id - venta_ven_id:$venta->ven_id</span>");
                            }
                        }
                        
                        $oferta = FUNCIONES::objeto_bd_sql("select * from venta_oferta 
                            inner join oferta on(vof_of_id=of_id)
                            where vof_ven_id=$venta->ven_id
                            and vof_estado='Pendiente'");

                        if ($oferta == NULL) {
                            continue;
                        }

                        $of_params = json_decode($oferta->vof_parametros_mod);

                        if ($of_params->of_periodos_sin_bra * 1 == 0) {
                            continue;
                        }

                        if (!MLM::venta_al_dia($venta, $hoy, TRUE)) {
                            continue;
                        }

                        if ($venta->ven_bono_bra == 0) {
                            continue;
                        }

                        $comision_cuota = FUNCIONES::objeto_bd_sql("select * from comision_cobro 
                            where comcob_ven_id=$venta->ven_id order by comcob_id asc limit 0,1");

//                        $monto_comision = $comision_cuota->comcob_monto_cuota;
                        $monto_comision = 0;
                        $monto = $venta->ven_bono_bra;
//                        $porc = 100;
                        $porc = 0;

                        if (true) {
                            $k = ($venta->vdo_nivel - $vendedor->vdo_nivel);
                            $gen_i = $k + 1;
                            $total_comisionado = 0;
                            $saldo = $monto;

                            if ($monto_comision <= $saldo) {

                                $datos = array(
                                    'venta' => $venta,
                                    'vendedor' => $vendedor->vdo_id,
                                    'monto' => $monto_comision,
                                    'moneda' => $venta->ven_moneda,
                                    'usuario' => $_SESSION[id],
                                    'glosa' => "RESIDUAL ABIERTO $gen_i (BRA Venta " . $venta->ven_id . ')' . "({$oferta->of_nombre})",
                                    'fecha' => $hoy,
                                    'tipo' => 'BRA',
                                    'periodo' => $pdo_id,
                                    'marca_tmp' => $marca_tmp,
                                    'porcentaje' => $porc
                                );
                                COMISION::insertar_comision($datos);
                                $saldo -= $monto_comision;
                                $total_comisionado += $monto_comision;
                            }
                        }
                    }
                }
            }
        }
    }

    public static function es_descendiente_old_version($vdo_mayor, $vdo_menor) {

        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$vdo_menor'");
        $vendedor_mayor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$vdo_mayor'");
        $nivel = $vendedor->vdo_nivel;
        $padre = $vendedor->vdo_vendedor_id;
        $b = false;
        $datos = null;
        while ($nivel >= $vendedor_mayor->vdo_nivel) {
            if ($vdo_mayor == $padre) {
                $datos = new stdClass();
                $datos->enlace = $vendedor->vdo_id;
                $datos->flag = true;
                $b = true;
                break;
            }
            $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$padre'");
            $nivel = $vendedor->vdo_nivel;
            $padre = $vendedor->vdo_vendedor_id;
        }
        return $datos;
    }
    
    public static function es_descendiente($vdo_mayor, $desc_ven_id) {

        $vendedor = FUNCIONES::objeto_bd_sql("select vdo_id,vdo_nivel,vdo_vendedor_id 
            from vendedor where vdo_venta_inicial='$desc_ven_id'");
        
        $vendedor_mayor = FUNCIONES::objeto_bd_sql("select vdo_id,vdo_nivel 
            from vendedor where vdo_id='$vdo_mayor'");
        
        $nivel = $vendedor->vdo_nivel;
        $padre = $vendedor->vdo_vendedor_id;
        $b = false;
        $datos = null;
        while ($nivel >= $vendedor_mayor->vdo_nivel) {
            if ($vdo_mayor == $padre) {
                $datos = new stdClass();
                $datos->enlace = $vendedor->vdo_id;
                $datos->flag = true;
                $b = true;
                break;
            }
            
            $vendedor = FUNCIONES::objeto_bd_sql("select vdo_id,vdo_nivel,vdo_vendedor_id 
                from vendedor where vdo_id='$padre'");
            
            $nivel = $vendedor->vdo_nivel;
            $padre = $vendedor->vdo_vendedor_id;
        }
        return $datos;
    }
    
    public static function esta_exenta_bra($ven_id, $hoy){
        $s_periodo = substr($hoy, 0, 7);
        
        $excepcion = FUNCIONES::objeto_bd_sql("select * from venta_excepcion_bra 
            where vex_ven_id='$ven_id'
            and vex_periodos like '%$s_periodo%'
            and vex_eliminado='No'");
        
        if ($excepcion) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
?>