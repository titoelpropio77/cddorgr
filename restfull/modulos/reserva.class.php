<?php

class RESERVA {

    var $usuDatos;
    var $encript;

    function RESERVA() {
        $this->encript = new Encryption();
        $this->usuDatos = explode("|", $this->encript->decode($_GET['token']));
    }

    function sincronizar($jsonDatos) {
        for ($i = 0; $i < count($jsonDatos); $i++) {
            if ($jsonDatos[$i]->res_registro == "Nuevo") {
                $this->agregar($jsonDatos[$i]);
            }
            if ($jsonDatos[$i]->res_registro == "Antiguo") {
                $this->modificar($jsonDatos[$i]);
            }
            if ($jsonDatos[$i]->res_registro == "Eliminado") {
                $this->eliminar($jsonDatos[$i]);
            }
        }
    }

    function eliminar($jsonDatos) {
        $respuesta = new stdClass();
        if ($this->verificar_estado($jsonDatos->res_id)) {

            $sql = "DELETE FROM reserva_terreno WHERE res_id='" . $jsonDatos->res_id . "'";
            $result = mysql_query($sql);

            //Cambiar estado de lote
            if ($this->verificar_lote_estado($jsonDatos->res_lot_id) == "Reservado") {
                $sql = "update lote set lot_estado='Disponible' where lot_id='" . $jsonDatos->res_lot_id . "'";
                $result = mysql_query($sql);
            }

            $respuesta->mensaje = "Reserva eliminado correctamente";
            $respuesta->estado = TRUE;
        } else {
            $respuesta->mensaje = "No se puede eliminar la reserva";
            $respuesta->estado = FALSE;
        }
        return $respuesta;
    }

    function agregar($jsonDatos) {
        // Verificar Interno Id
        $res_int_id = _cliente_tempid($jsonDatos->res_int_id);

        $respuesta = new stdClass();
        $datos_lote = explode('-', $jsonDatos->res_lot_id);
        $lot_id = $datos_lote[0];
//        $res = $this->politicas_reservas($jsonDatos->res_vdo_id, $lot_id);

        if (FALSE) {

            $respuesta->mensaje = $res->mensaje;
            $respuesta->accion = "error";
            $respuesta->estado = $res->estado;
        } else {

            if ($this->verificar($jsonDatos->res_lot_id) == "si") {

                $lote = FUNCIONES::objeto_bd_sql("
                        select * from lote inner join zona on (lot_zon_id=zon_id)
                        where lot_id=$lot_id");

                $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion 
                where urb_id={$jsonDatos->res_urb_id}");

                $moneda = $lote->zon_moneda;
                $res_fecha = date('Y-m-d');
                $res_plazo_fecha = FUNCIONES::sumar_dias($urbanizacion->urb_dias_plazo_res_pend, $res_fecha);
                if (TRUE) {
                    $ci = $urbanizacion->urb_monto_anticipo;
                } else {

                    $valor = $lote->lot_superficie * $lote->zon_precio;

                    $ci = $urbanizacion->urb_monto_anticipo * $valor / 100;
                }

                $sql = "insert into reserva_terreno (
                            res_int_id,
                            res_vdo_id,
                            res_lot_id,
                            res_fecha,
                            res_hora,
                            res_estado ,
                            res_usu_id,
                            res_plazo_fecha, 
                            res_plazo_hora,
                            res_nota,
                            res_urb_id,
                            res_moneda,
                            res_monto_referencial,
                            res_monto_m2,
                            res_app_tempid,
                            res_ci,
							res_multinivel
                        ) values ( 
                            '" . $res_int_id . "',
                            '" . $jsonDatos->res_vdo_id . "',
                            '" . $jsonDatos->res_lot_id . "',
                            '" . $res_fecha . "',
                            '" . date('H:i') . "',
                            'Pendiente',
                            '" . $this->usuDatos[0] . "',
                            '" . $res_plazo_fecha . "',
                            '" . date('H:i') . "',
                            '" . strip_tags(utf8_decode($jsonDatos->res_nota)) . "',
                            '" . $jsonDatos->res_urb_id . "',
                            '" . $jsonDatos->res_moneda . "',
                            '" . $jsonDatos->res_monto_referencial . "',
                            '" . $jsonDatos->res_monto_m2 . "',
                            '" . $jsonDatos->res_id . "',
                            '" . $ci . "',
							'si'
                        )";
                
//                if ($jsonDatos->int_foto != '') {                    
//                    FUNCIONES::actualizar_foto($jsonDatos->res_int_id, $jsonDatos->int_foto);
//                }
                
                //Registrar logs 
                logs("APLICACION", $sql, $this->usuDatos[0]);

                $result = mysql_query($sql);
                $insertId = mysql_insert_id();

                // Actualizar Lote
                $sql = "update lote set lot_estado='Reservado' where lot_id='" . $jsonDatos->res_lot_id . "'";
                $result = mysql_query($sql);

                $respuesta->id = $insertId;
                $respuesta->mensaje = "Reserva agregada correctamente";
                $respuesta->estado = TRUE;
                $respuesta->accion = "correcto";
            } else {
                $respuesta->mensaje = "No se pudo reservar el Terreno, <br/>por que ya se encuentra Reservado, Bloqueado o Vendido";
                $respuesta->estado = FALSE;
                $respuesta->accion = "error";
            }
        }
        return $respuesta;
    }

    function verificar($lot_id) {
        $sql = "SELECT lot_estado FROM lote WHERE  lot_id='" . $lot_id . "'";
        $result = mysql_query($sql);
        $objeto = mysql_fetch_object($result);

        if ($objeto->lot_estado == 'Disponible') {
            return "si";
        } else {
            return "no";
        }
    }

    function verificar_lote_estado($lot_id) {
        $sql = "SELECT lot_estado FROM lote WHERE  lot_id='" . $lot_id . "'";
        $result = mysql_query($sql);
        $objeto = mysql_fetch_object($result);
        return $objeto->lot_estado;
    }

    function verificar_estado($res_id) {
        $sql = "SELECT res_estado FROM reserva_terreno WHERE res_id='" . $res_id . "'";
        $result = mysql_query($sql);
        $objeto = mysql_fetch_object($result);
        if ($objeto->res_estado == 'Pendiente') {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function modificar($jsonDatos) {
        $respuesta = new stdClass();
        if ($this->verificar_estado($jsonDatos->res_id)) {
            if ($this->verificar($jsonDatos->res_lot_id) == "si") {
                $sql = "UPDATE reserva_terreno SET 
											res_int_id='" . $jsonDatos->res_int_id . "',
											res_vdo_id='" . $jsonDatos->res_vdo_id . "',
											res_lot_id='" . $jsonDatos->res_lot_id . "',
											res_fecha='" . date('Y-m-d') . "',
											res_hora='" . date('H:i') . "',
											res_plazo_fecha='" . date('Y-m-d') . "',
											res_plazo_hora='" . date('H:i') . "',
											res_urb_id='" . $jsonDatos->res_urb_id . "',
											res_moneda='" . $jsonDatos->res_moneda . "',
											res_monto_referencial='" . $jsonDatos->res_monto_referencial . "', 
											res_monto_m2='" . $jsonDatos->res_monto_m2 . "',
											res_nota='" . strip_tags(utf8_decode($jsonDatos->res_nota)) . "'
											
										WHERE res_id='" . $jsonDatos->res_id . "'";

                $result = mysql_query($sql);
                $respuesta->mensaje = "Reserva modificada correctamente";
                $respuesta->accion = "correcto";
            } else {
                $respuesta->mensaje = "No se pudo reservar el Terreno, <br/>por que ya se encuentra <br/>Reservado, Bloqueado o Vendido";
                $respuesta->accion = "error";
            }
        } else {
            $respuesta->mensaje = "Esta reserva no se puede modificar<br/> por ya ha sido revisado o cambiado de estado.";
            $respuesta->accion = "error";
        }
        return $respuesta;
    }

    function listar($estado) {

        $sql = "SELECT res.*, lot.lot_superficie,mon.mon_titulo,mon.mon_Simbolo,
                upper(CONCAT(intcli.int_nombre,' ',intcli.int_apellido)) AS cliente, 
                upper(CONCAT(intvdo.int_nombre,' ',intvdo.int_apellido)) AS nom_vendedor
                FROM reserva_terreno res,
                interno intcli,
                interno intvdo,
                vendedor vdo,lote lot,con_moneda mon
                WHERE  res.res_int_id=intcli.int_id 
                AND res.res_vdo_id=vdo.vdo_id 
                AND vdo.vdo_int_id=intvdo.int_id 
                AND res.res_lot_id=lot.lot_id 
                AND res.res_moneda=mon.mon_id 
                AND (res_usu_id='{$this->usuDatos[0]}' or res_vdo_id={$this->usuDatos[3]})
                AND res_estado='$estado' 
                AND res_fecha >= '2017-05-01' 
                order by res_fecha desc,res_hora desc";

        // echo "<p style='blue'>$sql</p>";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $reservas = new stdClass();
        $reservas->lista = array();
        $reservas->estado = strtoupper($estado);
        for ($i = 0; $i < $num; $i++) {
            $objeto = mysql_fetch_object($result);
            
            $anticipos = FUNCIONES::atributo_bd_sql("select sum(respag_monto) as campo 
                from reserva_pago 
                where respag_res_id='$objeto->res_id' and respag_estado='Pagado'")*1;
            
            $elem = new stdClass();
            $elem->nro = $objeto->res_id;
            $elem->cliente = html_encode($objeto->cliente);
            $elem->hora = $objeto->res_hora;
            $elem->vendedor = $objeto->nom_vendedor;
            $elem->fecha = $this->fecha_latina($objeto->res_fecha);
            $elem->descripcion = $this->descripcion_lote($objeto->res_lot_id);
            $elem->nota = $objeto->res_nota;
            $elem->moneda = $objeto->mon_Simbolo;
            $elem->cuota_inicial = $objeto->res_ci;
            $elem->ci_pagado = $anticipos;
            $elem->valor_terreno = $objeto->res_monto_m2 * $objeto->lot_superficie;

            $reservas->lista[] = $elem;
        }
        return $reservas;
    }

    function fecha_latina($fecha) {
        $datos = explode('-', $fecha);
        return $datos[2] . "/" . $datos[1] . "/" . $datos[0];
    }

    function descripcion_lote($lote) {
        $desc = "";
        $sql = "select urb_nombre,man_nro,lot_nro from lote
            inner join manzano on(lot_man_id=man_id)
            inner join urbanizacion on(man_urb_id=urb_id)
            where lot_id='$lote'";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);

        if ($num > 0) {
            $obj = mysql_fetch_object($result);
            $desc = "Urb:$obj->urb_nombre - Mza:$obj->man_nro - Lote:$obj->lot_nro";
        }

        return $desc;
    }

    function politicas_reservas($vdo_id, $lot_id) {
        $params_pol = FUNCIONES::objeto_bd_sql("select * from urbanizacion
						inner join uv on (urb_id=uv_urb_id)
						inner join manzano on (urb_id=man_urb_id)
						inner join lote on (man_id=lot_man_id and uv_id=lot_uv_id)
						where lot_id=$lot_id");

        $cant_res_pend = FUNCIONES::atributo_bd_sql("
		select count(0)as campo from reserva_terreno 
		where res_estado='Pendiente'
		and res_vdo_id=$vdo_id") * 1;

        $hoy = date("Y-m-d");
        $res = new stdClass();
        $res->estado = true;
        $res->mensaje = "";
        $veces = $params_pol->urb_veces_consecutiva_reserva;
        $dias_ex = $params_pol->urb_dias_exclusion_reserva;
        $max_reservas = $params_pol->urb_max_reservas_pendientes;
        $puede_reservar = $this->puede_reservar_lote($vdo_id, $lot_id, $dias_ex, $veces, $hoy);
        //$puede_reservar = true;

        if (!$puede_reservar) {
            $res->estado = false;
            $res->mensaje = "El vendedor no puede reservar $veces consecutivas este lote";
        }

        if ($cant_res_pend >= $params_pol->urb_max_reservas_pendientes) {
            $res->estado = false;
            $res->mensaje = "El vendedor no puede tener mas de $max_reservas reservas sin dinero.";
        }

        return $res;
    }

    function puede_reservar_lote($vdo_id, $lot_id, $urb_dias_exclusion_reserva, $urb_veces_consecutiva_reserva, $hoy) {

        $ultimas_reservas = FUNCIONES::objetos_bd_sql("
		select * from reserva_terreno 
		where res_lot_id=$lot_id 
		order by res_id desc limit 0,$urb_veces_consecutiva_reserva");
        // $hoy = date('Y-m-d');
        $b = false;
        $cant = $ultimas_reservas->get_num_registros();

        $k = 0;
        $fecha_ultima = "";
        $veces = $urb_veces_consecutiva_reserva - 1;
        $rastro = 0;

        // echo "<p>cant:$cant - veces:$veces</p>";

        if ($cant < $veces) {
            $rastro .= "-1";
            $b = true;
        } else {
            $rastro .= "-2";
            for ($i = 0; $i < $veces; $i++) {
                $ultima_reserva = $ultimas_reservas->get_objeto();

                if ($i == 0) {
                    $fecha_ultima = $ultima_reserva->res_fecha;
                }

                if ($ultima_reserva->res_vdo_id != $vdo_id) {
                    $rastro .= "-3";
                    $b = true;
                    $i = $veces;
                }

                $ultimas_reservas->siguiente();
            }

            if ($b === false) {
                $rastro .= "-4";
                $dif_dias = FUNCIONES::diferencia_dias($fecha_ultima, $hoy);
                // echo "<p>dif_dias:$dif_dias</p>";
                if ($dif_dias > $urb_dias_exclusion_reserva) {
                    $rastro .= "-5";
                    $b = true;
                }
            }
        }
        // echo "<p style='color:red'>rastro:$rastro</p>";
        return $b;
    }

}
