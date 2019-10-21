<?php

class COMPROBANTES {

    function COMPROBANTES() {
        
    }

    public static function registrar_comprobante($comprobante,&$conec=null, $guardar = true, $params = null) {
//        return 0;
//        _PRINT::pre($comprobante);
        if($conec==null){
            $conec = new ADO();
        }
        
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
        $cmp_une_id = $comprobante->une_id;

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
        $cmp_usu_id = $comprobante->usu_per_id?$comprobante->usu_per_id:$_SESSION['usu_per_id'];
        $cmp_usu_cre = $comprobante->usu_id?$comprobante->usu_id:$_SESSION['id'];
        $cmp_revisado = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='revisado' and conf_ges_id='$cmp_ges_id'", "conf_valor");
        $cmp_aprobado = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='aprobado' and conf_ges_id='$cmp_ges_id'", "conf_valor");
        $cmp_estado = 'Activo';
        $cmp_fecha_cre = date('Y-m-d H:i:s');
        $cmp_fecha_modi = '0000-00-00';
        $cmp_tabla = $comprobante->tabla;
        $cmp_tabla_id = $comprobante->tabla_id;
        $cmp_tarea_id = $comprobante->tarea_id;
        $cmp_apertura = $comprobante->apertura*1;
        $cmp_eliminado = 'No';
        //echo $cmp_peri_id." - ".$cmp_tco_id." - ".$cmp_fecha;
        if(!$cmp_apertura){
            $cmp_nro = FUNCIONES::obtener_per_tc_nro($cmp_peri_id, $cmp_tco_id,$cmp_ges_id) ;
        }else{
            $cmp_nro =1;
        }
        $mon_maestra=$cmp_mon_id;
        if($comprobante->moneda_maestra){
            $mon_maestra=$comprobante->moneda_maestra;
        }

        $insert = "insert into con_comprobante (
                        cmp_tco_id, cmp_mon_id, cmp_nro, cmp_nro_documento, cmp_fecha, cmp_ges_id, cmp_peri_id,cmp_forma_pago, cmp_ban_id, cmp_ban_char, 
                        cmp_ban_nro, cmp_glosa, cmp_referido,cmp_usu_id, cmp_revisado, cmp_aprobado, cmp_estado, cmp_usu_cre, cmp_fecha_cre, cmp_fecha_modi, cmp_tabla , 
                        cmp_tabla_id,cmp_tarea_id,cmp_eliminado,cmp_une_id,cmp_apertura)
                 values(
                        $cmp_tco_id, $mon_maestra, $cmp_nro, '$cmp_nro_documento', '$cmp_fecha', $cmp_ges_id, $cmp_peri_id,'$cmp_forma_pago',$cmp_ban_id,'$cmp_ban_char',
                        '$cmp_ban_nro','$cmp_glosa','$cmp_referido',$cmp_usu_id, '$cmp_revisado', '$cmp_aprobado','$cmp_estado','$cmp_usu_cre','$cmp_fecha_cre','$cmp_fecha_modi','$cmp_tabla',
                        '$cmp_tabla_id','$cmp_tarea_id','$cmp_eliminado','$cmp_une_id','$cmp_apertura');";

        $insert = "insert into con_comprobante(cmp_tco_id,cmp_mon_id,cmp_nro,cmp_nro_documento,cmp_fecha,cmp_ges_id,cmp_peri_id,cmp_forma_pago,cmp_ban_id,cmp_ban_char,cmp_ban_nro,cmp_glosa,cmp_referido,cmp_usu_id,cmp_revisado,cmp_aprobado,cmp_estado,cmp_usu_cre,cmp_fecha_cre,cmp_fecha_modi,cmp_tabla,cmp_tabla_id,cmp_tarea_id,cmp_eliminado,cmp_une_id,cmp_apertura)values($cmp_tco_id,$mon_maestra,$cmp_nro,'$cmp_nro_documento','$cmp_fecha',$cmp_ges_id,$cmp_peri_id,'$cmp_forma_pago',$cmp_ban_id,'$cmp_ban_char','$cmp_ban_nro','$cmp_glosa','$cmp_referido',$cmp_usu_id,'$cmp_revisado','$cmp_aprobado','$cmp_estado','$cmp_usu_cre','$cmp_fecha_cre','$cmp_fecha_modi','$cmp_tabla','$cmp_tabla_id','$cmp_tarea_id','$cmp_eliminado','$cmp_une_id','$cmp_apertura');";

        
        if ($guardar) {
                $conec->ejecutar($insert, true,true);
                $cmp_id = ADO::$insert_id;
        } else {
                echo "<p>$insert</p>";
                $cmp_id = -999;
				return $cmp_id;
        }        
//        FUNCIONES::actualizar_per_tc_nro($cmp_peri_id, $cmp_tco_id, $cmp_nro);

        /** INSERTAR LOS DETALLES* */
        $cmp_detalles = $comprobante->detalles;

        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp_fecha'");

        $cde_cmp_id = $cmp_id;
        $cde_eliminado = "No";

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }
        $index = 1;
        $tcambios=$comprobante->tcambios;
//        echo $cmp_fecha." - ".count($cmp_detalles)."<br>";
        
        if ($params) {
            if ($params->insercion_masiva === TRUE) {
                $limit_ins = $params->limit_ins;
                $sql_ins_plan = "insert into con_comprobante_detalle (
                            cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,
                            cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id
                )values";
                
                $arr_sent_sql = array();
            }
        }
        
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
            $doc_id = 0;
            $une_id = 0;
            $fpago = 'Efectivo';
            $ban_nombre='';
            $descripcion = '';
            $ban_nro='';
            if ($detalle->int_id)
                $int_id = $detalle->int_id;
            if ($detalle->doc_id)
                $doc_id = $detalle->doc_id;
            if ($detalle->une_id)
                $une_id = $detalle->une_id;
            if ($detalle->fpago)
                $fpago = $detalle->fpago;
            if ($detalle->ban_nombre)
                $ban_nombre = $detalle->ban_nombre;
            if ($detalle->ban_nro)
                $ban_nro = $detalle->ban_nro;
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
            $sql = "insert into con_comprobante_detalle (
                            cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,
                            cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id
                    )values(
                            '$cde_cmp_id', '1', '$cde_secuencia', '$cde_can_id', '$cde_cco_id', '$cde_cfl_id', '$cde_cue_id', '$val_bol',
                            '$cde_glosa', '$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id'
                    )";
//            echo $sql."<br>";
            if ($params == NULL) {
                $conec->ejecutar($sql);
            } else {
                if ($params->insercion_masiva === TRUE) {
                    $sql_ins_reg = "('$cde_cmp_id','1','$cde_secuencia','$cde_can_id','$cde_cco_id','$cde_cfl_id','$cde_cue_id','$val_bol','$cde_glosa','$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id')";
                    $arr_sent_sql[] = $sql_ins_reg;
                    
                    if (count($arr_sent_sql) == $limit_ins) {
                        $s_regs = implode(',', $arr_sent_sql);
                        $arr_sent_sql = array();
                        $sql_ins_masivo = $sql_ins_plan . $s_regs;
                        $conec->ejecutar($sql_ins_masivo);
                    }
                }
            }
            
            
            $cambios->reset();
            for ($i = 0; $i < $cambios->get_num_registros(); $i++) {
                $cambio = $cambios->get_objeto();
                $cde_mon_id = $cambio->tca_mon_id;
                if($tcambios[$cde_mon_id]>0){
                    $cde_valor = $val_bol / $tcambios[$cde_mon_id];
                }else{
                    $cde_valor = $val_bol / $cambio->tca_valor;
                }
                
                $sql = "insert into con_comprobante_detalle (
                                cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,
                                cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id
                        )values(
                                '$cde_cmp_id', '$cde_mon_id', '$cde_secuencia', '$cde_can_id', '$cde_cco_id', '$cde_cfl_id', '$cde_cue_id', '$cde_valor',
                                '$cde_glosa', '$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id'
                        )";
//                echo $cde_mon_id." - ".$sql."<br>";
                if ($params == NULL) {
                    $conec->ejecutar($sql,false,false);
                } else {
                    if ($params->insercion_masiva === TRUE) {
                        $sql_ins_reg = "('$cde_cmp_id','$cde_mon_id','$cde_secuencia','$cde_can_id','$cde_cco_id','$cde_cfl_id','$cde_cue_id','$cde_valor','$cde_glosa','$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id')";
                        $arr_sent_sql[] = $sql_ins_reg;
                        
                        if (count($arr_sent_sql) == $limit_ins) {
                            $s_regs = implode(',', $arr_sent_sql);
                            $arr_sent_sql = array();
                            $sql_ins_masivo = $sql_ins_plan . $s_regs;
                            $conec->ejecutar($sql_ins_masivo);
                        }
                    }
                }
                $cambios->siguiente();
            }
            $index++;
        }
        
        if (count($arr_sent_sql) > 0 && count($arr_sent_sql) < $limit_ins) {
            $s_regs = implode(',', $arr_sent_sql);
            $arr_sent_sql = array();
            $sql_ins_masivo = $sql_ins_plan . $s_regs;
            $conec->ejecutar($sql_ins_masivo);
        }
        
        return $cmp_id;
    }

    public static function modificar_comprobante($comprobante, &$conec = null, $guardar = true, $params = null) {
//        return;
        if ($conec == null) {
            $conec = new ADO();
        }

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
        $cmp_id = $comprobante->cmp_id;
        $cmp_tco_id = $tipo_comprobante;
        $cmp_mon_id = $comprobante->mon_id;
        $cmp_nro_documento = $comprobante->nro_documento;
        $cmp_fecha = $comprobante->fecha;
        $cmp_ges_id = $comprobante->ges_id;
        $cmp_peri_id = $comprobante->peri_id;
//        $cmp_une_id = $comprobante->une_id;

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
        $cmp_usu_id = $comprobante->usu_per_id?$comprobante->usu_per_id:$_SESSION['usu_per_id'];
        $cmp_usu_cre = $comprobante->usu_id?$comprobante->usu_id:$_SESSION['id'];        
        
        $cmp_revisado = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='revisado' and conf_ges_id='$cmp_ges_id'", "conf_valor");
        $cmp_aprobado = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='aprobado' and conf_ges_id='$cmp_ges_id'", "conf_valor");
        $cmp_estado = 'Activo';
        
        $cmp_fecha_modi = date('Y-m-d H:i:s');

//        $cmp_nro = FUNCIONES::obtener_per_tc_nro($cmp_peri_id, $tipo_comprobante) + 1;
//      , cmp_nro=$cmp_nro
        $update = "UPDATE con_comprobante SET cmp_tco_id=$cmp_tco_id , cmp_mon_id=$cmp_mon_id , cmp_nro_documento='$cmp_nro_documento', cmp_fecha='$cmp_fecha', 
                    cmp_ges_id=$cmp_ges_id , cmp_peri_id=$cmp_peri_id ,cmp_forma_pago='$cmp_forma_pago', cmp_ban_id=$cmp_ban_id, cmp_ban_char='$cmp_ban_char',
                    cmp_ban_nro='$cmp_ban_nro', cmp_glosa='$cmp_glosa' , cmp_referido='$cmp_referido', cmp_usu_id=$cmp_usu_id ,cmp_revisado='$cmp_revisado', 
                    cmp_aprobado='$cmp_aprobado', cmp_estado='$cmp_estado' , cmp_fecha_modi='$cmp_fecha_modi', cmp_usu_cre='$cmp_usu_cre',
                    cmp_eliminado='no'
                    WHERE cmp_id=$cmp_id;";
        
		$ademas_cmp_nro = "";
		if ($comprobante->is_fecha_mod) {
			$cmp_nro = FUNCIONES::obtener_per_tc_nro($cmp_peri_id, $tipo_comprobante, $cmp_ges_id);
			$ademas_cmp_nro = ", cmp_nro='$cmp_nro'";
		}
		
		
        $update = "UPDATE con_comprobante SET cmp_tco_id=$cmp_tco_id,cmp_mon_id=$cmp_mon_id,cmp_nro_documento='$cmp_nro_documento',cmp_fecha='$cmp_fecha',cmp_ges_id=$cmp_ges_id,cmp_peri_id=$cmp_peri_id,cmp_forma_pago='$cmp_forma_pago',cmp_ban_id=$cmp_ban_id,cmp_ban_char='$cmp_ban_char',cmp_ban_nro='$cmp_ban_nro',cmp_glosa='$cmp_glosa',cmp_referido='$cmp_referido',cmp_usu_id=$cmp_usu_id ,cmp_revisado='$cmp_revisado',cmp_aprobado='$cmp_aprobado',cmp_estado='$cmp_estado',cmp_fecha_modi='$cmp_fecha_modi',cmp_usu_cre='$cmp_usu_cre',cmp_eliminado='no' $ademas_cmp_nro WHERE cmp_id=$cmp_id;";
        if ($guardar) {
            $conec->ejecutar($update);
        } else {
            echo "<p>$update</p>";
        }
        //FUNCIONES::actualizar_per_tc_nro($cmp_peri_id, $tipo_comprobante, $cmp_nro);
        /* --ELIMINAR DETALLES-- */
        $delete = "DELETE FROM con_comprobante_detalle WHERE cde_cmp_id=$cmp_id;";
        if ($guardar) {            
            $conec->ejecutar($delete);
        } else {
            echo "<p>$delete</p>";
        }
        /* --ELIMINAR DETALLES-- */
        /* --ELIMINAR LIBROS-- */
//        $delete = "DELETE FROM con_libro WHERE lib_cmp_id=$cmp_id;";
//        $conec->ejecutar($delete);
        /* --ELIMINAR LIBROS-- */
        /* --ELIMINAR RETENCIONES-- */
//        $delete = "DELETE FROM con_retencion WHERE ret_cmp_id=$cmp_id;";
//        $conec->ejecutar($delete);
        /* --ELIMINAR RETENCIONES-- */


        $cmp_detalles = $comprobante->detalles;

        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp_fecha'");

        $cde_cmp_id = $cmp_id;
        $cde_eliminado = "No";

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }
        $index = 1;
//        echo $cmp_fecha." - ".count($cmp_detalles)."<br>";
        
        if ($params) {
            if ($params->insercion_masiva === TRUE) {
                $limit_ins = $params->limit_ins;
                $sql_ins_plan = "insert into con_comprobante_detalle (
                            cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,
                            cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id
                )values";
                
                $arr_sent_sql = array();
            }
        }
        
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
            $doc_id = 0;
            $une_id = 0;
            $fpago = 'Efectivo';
            $ban_nombre='';
            $descripcion = '';
            $ban_nro='';
            if ($detalle->int_id)
                $int_id = $detalle->int_id;
            if ($detalle->doc_id)
                $doc_id = $detalle->doc_id;
            if ($detalle->une_id)
                $une_id = $detalle->une_id;
            if ($detalle->fpago)
                $fpago = $detalle->fpago;
            if ($detalle->ban_nombre)
                $ban_nombre = $detalle->ban_nombre;
            if ($detalle->ban_nro)
                $ban_nro = $detalle->ban_nro;
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
            $sql = "insert into con_comprobante_detalle (
                            cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,
                            cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id
                    )values(
                            '$cde_cmp_id', '1', '$cde_secuencia', '$cde_can_id', '$cde_cco_id', '$cde_cfl_id', '$cde_cue_id', '$val_bol',
                            '$cde_glosa', '$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id'
                    )";
            $sql = "insert into con_comprobante_detalle(cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values('$cde_cmp_id','1','$cde_secuencia','$cde_can_id','$cde_cco_id','$cde_cfl_id','$cde_cue_id','$val_bol','$cde_glosa','$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id')";
//            echo $sql."<br>";
            
            
            if ($params == NULL) {
                if ($guardar) {
                    $conec->ejecutar($sql);
                } else {
                    echo "<p>$sql;</p>";
                }
            } else {
                if ($params->insercion_masiva === TRUE) {
                    $sql_ins_reg = "('$cde_cmp_id','1','$cde_secuencia','$cde_can_id','$cde_cco_id','$cde_cfl_id','$cde_cue_id','$val_bol','$cde_glosa','$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id')";
                    $arr_sent_sql[] = $sql_ins_reg;
                    
                    if (count($arr_sent_sql) == $limit_ins) {
                        $s_regs = implode(',', $arr_sent_sql);
                        $arr_sent_sql = array();
                        $sql_ins_masivo = $sql_ins_plan . $s_regs;
                        $conec->ejecutar($sql_ins_masivo);
                    }
                }
            }
            
            $cambios->reset();
            for ($i = 0; $i < $cambios->get_num_registros(); $i++) {
                $cambio = $cambios->get_objeto();
                $cde_mon_id = $cambio->tca_mon_id;
                $cde_valor = $val_bol / $cambio->tca_valor;
                $sql = "insert into con_comprobante_detalle (
                                cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,
                                cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id
                        )values(
                                '$cde_cmp_id', '$cde_mon_id', '$cde_secuencia', '$cde_can_id', '$cde_cco_id', '$cde_cfl_id', '$cde_cue_id', '$cde_valor',
                                '$cde_glosa', '$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id'
                        )";
                $sql = "insert into con_comprobante_detalle(cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,cde_valor,cde_glosa,cde_eliminado,cde_int_id,cde_fpago,cde_fpago_ban_nombre,cde_fpago_ban_nro,cde_fpago_descripcion,cde_une_id,cde_doc_id)values('$cde_cmp_id','$cde_mon_id','$cde_secuencia','$cde_can_id','$cde_cco_id','$cde_cfl_id','$cde_cue_id','$cde_valor','$cde_glosa','$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id')";
//                echo $cde_mon_id." - ".$sql."<br>";
                
                
                if ($params == NULL) {
                    if ($guardar) {
                        $conec->ejecutar($sql);
                    } else {
                        echo "<p>$sql;</p>";
                    }
                } else {
                    if ($params->insercion_masiva === TRUE) {
                        $sql_ins_reg = "('$cde_cmp_id','$cde_mon_id','$cde_secuencia','$cde_can_id','$cde_cco_id','$cde_cfl_id','$cde_cue_id','$cde_valor','$cde_glosa','$cde_eliminado','$int_id','$fpago','$ban_nombre','$ban_nro','$descripcion','$une_id','$doc_id')";
                        $arr_sent_sql[] = $sql_ins_reg;
                        
                        if (count($arr_sent_sql) == $limit_ins) {
                            $s_regs = implode(',', $arr_sent_sql);
                            $arr_sent_sql = array();
                            $sql_ins_masivo = $sql_ins_plan . $s_regs;
                            $conec->ejecutar($sql_ins_masivo);
                        }
                    }
                }
                
                $cambios->siguiente();
            }
            $index++;
        }
        
        if (count($arr_sent_sql) > 0 && count($arr_sent_sql) < $limit_ins) {
            $s_regs = implode(',', $arr_sent_sql);
            $arr_sent_sql = array();
            $sql_ins_masivo = $sql_ins_plan . $s_regs;
            $conec->ejecutar($sql_ins_masivo);
        }
    }
    
    public static function anular_comprobante($tabla, $id,$conec = null, $orig = '') {
        if($conec==null){
            $conec = new ADO();
        }
        
        
        $sql = "select p.pdo_estado , c.cmp_fecha from con_comprobante c, con_periodo p 
            where cmp_peri_id=p.pdo_id and c.cmp_tabla='$tabla' and c.cmp_tabla_id='$id'";
//        echo $sql;
		$anular_cmps_usu_ids = FUNCIONES::ad_parametro('par_anular_cmps_usu_ids');
        $cmp = FUNCIONES::objeto_bd_sql($sql);
        if($cmp){
            
            if ($orig == '') {
                $is_eliminar=  FUNCIONES::ad_parametro('par_eliminar_dia')*1;
                $fecha_now=date('Y-m-d');
                // $array_user=array('admin','dvallejo','gabriel','gsoto','mvega');				
				$array_user = explode(',', $anular_cmps_usu_ids);
                if(!in_array($_SESSION[id], $array_user)){
                    if($is_eliminar){
                        if($fecha_now!=$cmp->cmp_fecha){
                            return false;
                        }
                    }
                }
            }
//        _PRINT::pre($periodo);
//            echo gettype($cmp).'ddd';
        
            if ($cmp->pdo_estado == 'Abierto') {
                
                $sql = "update con_comprobante SET cmp_eliminado = 'Si', cmp_fecha_modi = '" . date('Y-m-d') . "' Where cmp_tabla ='" . $tabla . "' and cmp_tabla_id ='" . $id . "'";
                $conec->ejecutar($sql);
                return true;
            } else {
                return false;
            }
        }else{
            return true;
        }
    }
    
    
    
}