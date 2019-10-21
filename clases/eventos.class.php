<?php

class EVENTOS {

    public static function cambiar_propietario($data, $acc = 'P') { // P: pagar, R: anular
        $data = (object) $data;
        $objeto = $data->objeto;
        $fecha = $data->fecha;
        if ($acc == 'P') { // Pagar
            $conec = new ADO();
            $ven_id = $objeto->vph_ven_id;
            $sql = "update venta set ven_int_id='$objeto->vph_int_id_nuevo' where ven_id=$ven_id";
            //        echo "$sql;<br>";
            $conec->ejecutar($sql);
            $sql = "update interno_deuda set ind_int_id='$objeto->vph_int_id_nuevo' where ind_tabla='venta' and ind_tabla_id=$ven_id and ind_estado='Pendiente'";
            //        echo "$sql;<br>";    
            $conec->ejecutar($sql);
            $sql = "update venta set ven_co_propietario='$objeto->vph_cop_id_nuevo' where ven_id=$ven_id";
            //        echo "$sql;<br>";
            $conec->ejecutar($sql);
            $sql_up = "update venta_propietarios_historial set vph_estado='Pagado' where vph_id='$objeto->vph_id'";
            //        echo "$sql_up;<br>";
            $conec->ejecutar($sql_up);
			
			$venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$ven_id'");			
			if ($venta->ven_multinivel == 'si') {
			
				$afiliado = FUNCIONES::objeto_bd_sql("select * from vendedor 
				where vdo_venta_inicial='$venta->ven_id' and vdo_int_id='$objeto->vph_int_id'");
				
				if ($afiliado) {
					$log = $afiliado->vdo_log . "|Cambio de propietario; anterior:$afiliado->vdo_int_id - nuevo:$objeto->vph_int_id_nuevo";
					$sql_upd = "update vendedor set vdo_int_id='$objeto->vph_int_id_nuevo',vdo_log='$log'
					where vdo_venta_inicial='$venta->ven_id' and vdo_int_id='$objeto->vph_int_id'";
					
					$conec->ejecutar($sql_upd);
				}
								
			}
        } else {// Anular
            $conec = new ADO();
            $ven_id = $objeto->vph_ven_id;
            $sql = "update venta set ven_int_id='$objeto->vph_int_id' where ven_id=$ven_id";
            //        echo "$sql;<br>";
            $conec->ejecutar($sql);
            $sql = "update interno_deuda set ind_int_id='$objeto->vph_int_id' where ind_tabla='venta' and ind_tabla_id=$ven_id and ind_estado='Pendiente'";
            //        echo "$sql;<br>";    
            $conec->ejecutar($sql);
            $sql = "update venta set ven_co_propietario='$objeto->vph_cop_id' where ven_id=$ven_id";
            //        echo "$sql;<br>";
            $conec->ejecutar($sql);
            $sql_up = "update venta_propietarios_historial set vph_estado='Anulado' where vph_id='$objeto->vph_id'";
            //        echo "$sql_up;<br>";
            $conec->ejecutar($sql_up);
        }
    }

    public static function cambiar_lote($data, $acc = 'P') { // P: pagar, R: anular
        $data = (object) $data;
        $objeto = $data->objeto;
        $par = json_decode($objeto->vneg_parametros);
        $fecha_pag = $data->fecha;
        $conec = new ADO();
        if ($acc == 'P') { // Pagar
            // insertar venta...
            
            $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$objeto->vneg_ven_id'");
            $valor = $par->new_lot_sup * $par->ven_metro;

            $fecha = $objeto->vneg_fecha;
            $monto = $valor - $par->ven_descuento + $par->ven_incremento;
            $monto_efectivo = $par->ven_monto_efectivo;
            $estado = 'Pendiente';
            $ven_tipo = 'Credito';
            
            $monto_intercambio=$venta->ven_monto_intercambio;
            
            $str_inter_ids= '';
            $str_inter_montos='';
            
            if($monto_intercambio>0){
                $a_saldo_inter=  FUNCIONES::saldo_intercambio($venta->ven_id);
                
                $n_inter_ids=array_keys($a_saldo_inter);
                $n_inter_montos=array_values($a_saldo_inter);
                
                $saldo_intercambio = array_sum($a_saldo_inter);

                $str_inter_ids=  implode(',', $n_inter_ids);
                $str_inter_montos=  implode(',', $n_inter_montos);
            }
            
            $monto_pagar = 0; //devolver
            if ($monto_efectivo <= 0) {
                $estado = 'Pagado';
                $ven_tipo = 'Contado';
                
                $monto_pagar = $monto_efectivo * (-1);
                $monto_efectivo = 0;
            }
            $atp = array('mp' => 'plazo', 'cm' => 'cuota', 'manual' => 'manual');
            $tipo_plan = $atp[$par->def_plan_efectivo];
            
            $moneda=$venta->ven_moneda;
            $cambio_usd=1;
            if($moneda=='1'){
                $cambio_usd=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$fecha' and tca_mon_id=2");
            }

//            FUNCIONES::print_pre($objeto);
//            FUNCIONES::print_pre($par);
            $lote_id = $par->new_lot_id;
            $lote = FUNCIONES::objeto_bd_sql("select * from lote where lot_id='$lote_id'");
            $venta_nro = FUNCIONES::atributo_bd("venta", "ven_lot_id='$lote_id'", "count(*)") + 1;
            $codigo = $lote->lot_codigo . $venta_nro;
            $concepto = EVENTOS::lote_concepto($lote_id);
            $fecha_cre = date('Y-m-d H:i:s');

            $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$par->new_urb_id");
            $costo = $par->new_lot_sup * ($urb->urb_val_costo * $cambio_usd);
            $costo_cub = $par->ven_pagado;
            $costo_pag = 0;
            if ($costo_cub > $costo) {
                $costo_cub = $costo;
            }
            
            $ven_orden = FUNCIONES::orden_venta_lote($lote_id);

            $sql = "insert into venta(
                        ven_int_id,ven_co_propietario,ven_lot_id,ven_urb_id,ven_fecha,ven_moneda,ven_res_id,
                        ven_metro,ven_superficie,ven_valor,ven_decuento,ven_incremento,ven_monto,ven_res_anticipo,ven_monto_intercambio,ven_monto_efectivo,
                        ven_estado,ven_usu_id,ven_tipo,ven_val_interes,ven_cuota_inicial,ven_tipo_plan,ven_plazo,ven_cuota,
                        ven_observacion,ven_codigo,ven_vdo_id,ven_comision,ven_concepto,
                        ven_tipo_pago,ven_fecha_cre,ven_monto_pagar,ven_form,ven_intercambio_ids,ven_intercambio_montos,
                        ven_costo,ven_costo_cub,ven_costo_pag,ven_venta_id,ven_venta_pagado,
                        ven_lug_id, ven_ubicacion,ven_suc_id,ven_ufecha_prog,
                        ven_ufecha_pago,ven_ufecha_valor,ven_usaldo,ven_cuota_pag,ven_capital_pag,ven_capital_inc,ven_capital_desc,ven_orden
                    ) values (
                        '$venta->ven_int_id','$venta->ven_co_propietario','$lote_id','$par->new_urb_id','$fecha','$moneda','0',
                        '$par->ven_metro','$par->new_lot_sup','$valor','$par->ven_descuento','$par->ven_incremento','$monto','0','$saldo_intercambio','$monto_efectivo',
                        '$estado','$_SESSION[id]','$ven_tipo','$par->interes_anual','0','$tipo_plan','0','0',
                        '$objeto->vneg_observacion', '$codigo','$venta->ven_vdo_id','0','$concepto',
                        'Normal','$fecha_cre','$monto_pagar',$venta->ven_form,'$str_inter_ids','$str_inter_montos',
                        '$costo','$costo_cub','$costo_pag','$venta->ven_id','$par->ven_pagado',
                        '$venta->ven_lug_id','$venta->ven_ubicacion','$venta->ven_suc_id','$venta->ven_ufecha_prog',
                        '$fecha','$fecha','$monto_efectivo',0,0,0,0,'$ven_orden'
                    )";
            $conec->ejecutar($sql, true,true);
            $llave = ADO::$insert_id;
			
			$ven_can_codigo = FUNCIONES::crear_cuentas_analiticas($llave);
            
            if($monto_intercambio>0){
                $inter_ids=$n_inter_ids;
                $inter_montos=$n_inter_montos;
                for($i=0;$i<count($inter_ids);$i++){
                    $_inter_id=$inter_ids[$i];
                    $_inter_monto=$inter_montos[$i];
                    $sql_insert="insert into venta_intercambio(
                                    vint_ven_id,vint_inter_id,vint_monto,vint_estado
                                )values(
                                    $llave,'$_inter_id','$_inter_monto','Pendiente'
                                )";
                    $conec->ejecutar($sql_insert);
                }
            }
            
            $vplazo = 0;
            if ($monto_efectivo > 0) {
                $plan_data = array(
                    'ven_id' => $llave,
                    'int_id' => $venta->ven_int_id,
                    'fecha' => $fecha,
                    'moneda' => $moneda,
                    'concepto' => $concepto,
                    'monto' => $monto_efectivo,
                    'cuota_inicial' => 0,
                    'interes_anual' => $par->interes_anual,
                    'tipo_plan' => $tipo_plan,
                    'plazo' => $par->meses_plazo,
                    'cuota' => $par->cuota_mensual,
                    'rango' => $par->ven_rango,
                    'frecuencia' => $par->ven_frecuencia,
                    'fecha_pri_cuota' => FUNCIONES::get_fecha_mysql($par->fecha_pri_cuota),
                    'det_plan_manual' => $par->det_plan_efectivo,
                );
                $vplazo = EVENTOS::insertar_plan_pagos($plan_data, $conec);
            }
            $sql_up="update venta_negocio set vneg_estado='Activado' where vneg_id=$objeto->vneg_id";
            $conec->ejecutar($sql_up);
            $sql_up="update venta set ven_estado='Cambiado' where ven_id=$objeto->vneg_ven_id";
            $conec->ejecutar($sql_up);
            $sql_up="update interno_deuda set ind_estado='Retenido' where ind_estado='Pendiente'and ind_capital_pagado=0 and ind_tabla='venta' and ind_tabla_id=$objeto->vneg_ven_id";
            $conec->ejecutar($sql_up);

            $sql_up_lote="update lote set lot_estado='Vendido' where lot_id='$lote_id'";
            $conec->ejecutar($sql_up_lote);
            $sql_up_lote="update lote set lot_estado='Disponible' where lot_id='$par->lote_id_ant'";
            $conec->ejecutar($sql_up_lote);

            include_once 'clases/modelo_comprobantes.class.php';
            include_once 'clases/registrar_comprobantes.class.php';
            
            $venta_ori= $venta;// FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$objeto->vneg_ven_ori");
            $venta_des=  FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$llave'");
            $referido=  FUNCIONES::interno_nombre($venta->ven_int_id);
            
            $glosa="Cambio de Ubicacion de la Venta Nro. $venta_ori->ven_id a la Venta Nro $venta_des->ven_id, $venta_des->ven_concepto";
            
            $urb_ori=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta_ori->ven_urb_id'");
            $urb_des=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$par->new_urb_id'");

            $pagado_ori=  EVENTOS::total_pagado($venta_ori->ven_id);
            
//            $ret_ingreso=$par->ret_ingreso;
//            $capital_efectivo=$par->tot_capital;
            $saldo_ori =$venta_ori->ven_monto_efectivo-$pagado_ori->capital-$pagado_ori->descuento+$pagado_ori->incremento;
            
            $amontos =array();
            $amontos_pag =array();
            if($monto_intercambio>0){
                $amontos = FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
                $amontos_pag = FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");
            }
            

            $data=array(
                'moneda'=>$venta_ori->ven_moneda,
                'moneda_des'=>$venta->ven_moneda,
                'ges_id'=>$_SESSION[ges_id],
                'fecha'=>$fecha,
                'glosa'=>$glosa,
                'interno'=>$referido,
                'tabla_id'=>$venta_ori->ven_id,
                'urb_ori'=>$urb_ori,
                'urb_des'=>$urb_des,

                'costo'=>$venta_ori->ven_costo,
                'costo_pagado'=>$venta_ori->ven_costo_cub+$pagado_ori->costo,
                
                'pagado_ori'=>$par->ven_pagado,                
                'saldo_ori'=>$saldo_ori,
                
                'saldo'=>$par->ven_monto_efectivo,
                'monto_pagar'=>$monto_pagar,
                
                'intercambio'=>$monto_intercambio,
                'inter_montos'=>$amontos,
                'inter_montos_pag'=>$amontos_pag,
                'costo_des'=>$costo,
                'costo_cub_des'=>$costo_cub,
                'ven_can_codigo_ori' => $venta_ori->ven_can_codigo,
                'ven_can_codigo_dest' => $venta_des->ven_can_codigo,
                'tarea_id' => $objeto->vneg_id,
                'orden' => $ven_orden,
            );
//'$costo','$costo_cub','$costo_pag'
            $comprobante = MODELO_COMPROBANTE::venta_cambio_lote($data);

            COMPROBANTES::registrar_comprobante($comprobante);
            
            if ($venta->ven_multinivel == 'si') {
                $datos_mul = (object)array(
                    'ant_ven_id' => $venta->ven_id, 
                    'new_ven_id' => $llave
                );
                EVENTOS::tratar_multinivel($datos_mul);
            }
            
            if ($venta->ven_of_id > 0) {
                $datos_oferta = (object)array(
                    'ant_ven_id' => $venta->ven_id, 
                    'new_ven_id' => $llave
                );
                EVENTOS::tratar_oferta($datos_oferta);
            }
            
        } else {// Anular
            $data = (object)$data;
            $obj_vneg = $data->objeto;
            $parametros = json_decode($obj_vneg->vneg_parametros);
            $obj_bloq = FUNCIONES::objeto_bd_sql("select * from bloquear_terreno where bloq_id='$parametros->bloq_id'");
            
            if ($obj_bloq) {
                $sql_upd = "update lote set lot_estado='Disponible' 
                where lot_id='$obj_bloq->bloq_lot_id'";
                $conec->ejecutar($sql_upd);
                
                $sql_upd_bloq = "update bloquear_terreno set bloq_estado='Deshabilitado' 
                where bloq_id='$obj_bloq->bloq_id'";
                $conec->ejecutar($sql_upd_bloq);
            }
            
            $sql = "update venta_negocio set vneg_estado='Anulado' where vneg_id='$obj_vneg->vneg_id'";
            $conec->ejecutar($sql);
        }
    }
    
    public static function tratar_multinivel($datos){
        
        $conec = new ADO();
        $venta_anterior = FUNCIONES::objeto_bd_sql("select * from venta where 
        ven_id='$datos->ant_ven_id'");
        
        $sql_upd = "update venta set ven_multinivel='si',
        ven_saldo_red='$venta_anterior->ven_saldo_red',
        ven_monto_red='$venta_anterior->ven_monto_red',
        ven_bono_inicial='$venta_anterior->ven_bono_inicial',
        ven_bono_bra='$venta_anterior->ven_bono_bra',
        ven_bono_fed='$venta_anterior->ven_bono_fed',
        ven_bono_bev='$venta_anterior->ven_bono_bev',
        ven_bono_fdr='$venta_anterior->ven_bono_fdr',
        ven_porc_comisiones='$venta_anterior->ven_porc_comisiones',
        ven_comision_gen='0',    
        ven_fecha_act_mlm='$venta_anterior->ven_fecha_act_mlm'
        where ven_id='$datos->new_ven_id'";
        
        $conec->ejecutar($sql_upd);
        
        include_once 'clases/mlm.class.php';
        
        $data_cobro = array('venta' => $datos->new_ven_id, 'vendedor' => $venta_anterior->ven_vdo_id);
        MLM::insertar_comisiones_cobro($data_cobro);
                        
//        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$venta_anterior->ven_vdo_id'");
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_int_id='$venta_anterior->ven_int_id'
                and vdo_venta_inicial='$venta_anterior->ven_id'");
        $log = ($vendedor->vdo_log === NULL) ? '' : $vendedor->vdo_log;
        $log = $log . "|se cambio el valor de venta inicial de {$datos->ant_ven_id} a {$datos->new_ven_id}";
        
//        $sql_upd_vdo = "update vendedor set vdo_venta_inicial='$datos->new_ven_id',
//        vdo_log='$log' where vdo_id='$venta_anterior->ven_vdo_id'";
        $sql_upd_vdo = "update vendedor set vdo_venta_inicial='$datos->new_ven_id',
        vdo_log='$log' where vdo_id='$vendedor->vdo_id'";
        
//        echo "<p style='color:blue'>$sql_upd_vdo</p>";
        $conec->ejecutar($sql_upd_vdo);
    }
    
    public static function tratar_oferta($datos){
        $conec = new ADO();
        $venta_anterior = FUNCIONES::objeto_bd_sql("select * from venta_oferta where 
        vof_ven_id='$datos->ant_ven_id' and vof_eliminado='No'");
        
        $sql_ins = "insert into venta_oferta(
        vof_ven_id,vof_of_id,
        vof_parametros_cre,vof_parametros_mod
        )values(
        '$datos->new_ven_id','$venta_anterior->vof_of_id',
        '$venta_anterior->vof_parametros_cre','$venta_anterior->vof_parametros_mod'
        )";
        
        $conec->ejecutar($sql_ins);
    }

    public static function fusion_venta($data, $acc = 'P',$conec=null) { // P: pagar, R: anular
        if($conec==null){
            $conec=new ADO();
        }
        $data = (object) $data;
        $objeto = $data->objeto;
        $par = json_decode($objeto->vneg_parametros);
        $fecha_pag = $data->fecha;
        if ($acc == 'P') { // Pagar
//            FUNCIONES::print_pre($objeto);
//            FUNCIONES::print_pre($par);
            
            $fecha = $objeto->vneg_fecha;
            $venta=  FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$objeto->vneg_ven_id");
            
            $capital_pag=$par->tot_capital;
            $capital_desc=0;//$par->ori_descuento;
            $capital_inc=0;//$par->ori_incremento;
            
            
//            $upago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='Activo' and vpag_ven_id=2406 order by vpag_id desc limit 1");
//            $fecha_valor=$venta->ven_fecha;
//            if($upago){
//                $fecha_valor=$upago->vpag_fecha_valor;
//            }
            
            $fecha_valor=FUNCIONES::get_fecha_mysql($par->u_fecha_valor);
            
            $saldo_final=$par->ven_monto_efectivo;
            $saldo=$saldo_final-$capital_pag-$capital_desc+$capital_inc;
            
            $fecha_cre=date('Y-m-d');
            
            $saldo_costo=$venta->ven_costo-$venta->ven_res_anticipo-$venta->ven_monto_intercambio-$venta->ven_cuota_inicial-$venta->ven_venta_pagado;
            $costo_pagado=0;
            if($saldo_costo>0){
                
                if($saldo_final<=0){
                    $costo_pagado=$venta->ven_costo-$venta->ven_costo_cub-$venta->ven_costo_pag;
                }else{
                    $costo_pagado=round(($capital_pag*$saldo_costo)/$venta->ven_monto_efectivo,2);
                }
            }
            $monto_pagar=0;
            if($saldo_final<0){
                $capital_pag=$par->tot_capital+$par->ven_monto_efectivo;
                $monto_pagar=$par->ven_monto_efectivo*(-1);
                $saldo_final=0;
            }
            
        $sql_sel_ucu_pen=("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$par->ven_id' and ind_estado='Pendiente' and (ind_capital_pagado+ind_interes_pagado+ind_envio_pagado+ind_form_pagado)>0 and ind_tipo='pcuota' and ind_num_correlativo>0 order by ind_id asc limit 1");

        $ucu_pen=  FUNCIONES::objeto_bd_sql($sql_sel_ucu_pen);
//        FUNCIONES::print_pre($ucu_pen);
        if($ucu_pen){
            $sql_up="update interno_deuda set ind_estado='Pagado' , ind_saldo_final='$par->saldo' where ind_id=$ucu_pen->ind_id";
            $conec->ejecutar($sql_up);
        }
            
            
            $insert_dif="insert into interno_deuda (
                                ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_usu_id,
                                ind_moneda,ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,
                                ind_estado,ind_interes_pagado,ind_capital_pagado,ind_monto_pagado,ind_saldo_final,
                                ind_fecha_pago,ind_dias_interes,ind_concepto,ind_observacion,ind_form,ind_form_pagado,
                                ind_mora,ind_mora_pagado,ind_tipo,ind_fecha_cre,ind_estado_parcial,ind_monto_parcial,
                                ind_costo_pagado,ind_capital_inc,ind_capital_desc,ind_interes_desc,ind_form_desc,ind_venta_id
                            )values(
                                'venta','$venta->ven_id','-3','$venta->ven_int_id','$fecha_cre','$_SESSION[id]',
                                '$venta->ven_moneda','0','$capital_pag','$capital_pag','$saldo','$fecha_valor',
                                'Pagado','0','$capital_pag','$capital_pag',$saldo_final,
                                '$fecha','0','Fusion de Aportes $par->ori_ven_id','',0,0,
                                0,0,'pcuota','$fecha_cre','listo','0',
                                '$costo_pagado','$capital_inc','$capital_desc',0,0,'$par->ori_ven_id'

                            )";
            $conec->ejecutar($insert_dif,false);
            $cua_ind_id=  mysql_insert_id();
            $codigo=  FUNCIONES::fecha_codigo();
            $nro_recibo = FUNCIONES::nro_recibo($fecha);
            $insert_pago="insert into venta_pago (
                                vpag_ven_id,vpag_codigo,vpag_fecha_pago,vpag_fecha_valor,vpag_int_id,vpag_moneda,
                                vpag_saldo_inicial,vpag_dias_interes,vpag_interes,vpag_capital,vpag_form,
                                vpag_mora,vpag_monto,vpag_saldo_final,vpag_estado,vpag_interes_ids,
                                vpag_interes_montos,vpag_capital_ids,vpag_capital_montos,
                                vpag_form_ids,vpag_form_montos,vpag_mora_ids,vpag_mora_montos,vpag_mora_con_ids,
                                vpag_mora_con_montos,vpag_mora_gen_ids,vpag_mora_gen_montos,vpag_mora_gen_dias,
                                vpag_fecha_cre,vpag_usu_cre,vpag_cob_usu,vpag_cob_codigo,vpag_cob_aut,
                                vpag_costo,vpag_costo_ids,vpag_costo_montos,vpag_capital_inc,vpag_capital_desc,
                                vpag_interes_desc,vpag_form_desc,vpag_recibo,vpag_importado,vpag_venta_id,vpag_suc_id
                            )values(
                                '$venta->ven_id','$codigo','$fecha','$fecha_valor','$venta->ven_int_id','$venta->ven_moneda',
                                '$saldo',0,0,$capital_pag,0,
                                0,$capital_pag,'$saldo_final','Activo','',
                                '','$cua_ind_id','$capital_pag',
                                '','','','','',
                                '','','','',
                               '$fecha_cre','$_SESSION[id]','$_SESSION[id]','','0',
                                0,'','','$capital_inc','$capital_desc',
                                0,0,'$nro_recibo','0','$par->ori_ven_id','$_SESSION[suc_id]'
                            )";
            $conec->ejecutar($insert_pago, true, true);
            $pago_id = ADO::$insert_id;
            
            include_once 'clases/recibo.class.php';
            $data_recibo=array(
                'recibo'=>$nro_recibo,
                'fecha'=>$fecha,
                'monto'=>$capital_pag,
                'moneda'=>$venta->ven_moneda,
                'tabla'=>'venta_pago',
                'tabla_id'=>$pago_id

            );
            RECIBO::insertar($data_recibo);
            
            $ucuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");
            $pagado = EVENTOS::total_pagado($venta->ven_id);
            $tcapital_pag=$pagado->capital;
            $tcapital_inc=$pagado->incremento;
            $tcapital_desc=$pagado->descuento;
            $cuota_pag=$ucuota->ind_num_correlativo;
            $sql_up="update venta set 
                            ven_ufecha_pago='$fecha', ven_ufecha_valor='$fecha_valor', 
                            ven_cuota_pag='$cuota_pag', ven_capital_pag='$tcapital_pag',
                            ven_capital_inc='$tcapital_inc', ven_capital_desc='$tcapital_desc', 
                            ven_usaldo='$saldo_final'
                        where ven_id='$venta->ven_id'";

            $conec->ejecutar($sql_up);
                
//            $sql_up="update venta set ven_ufecha_pago='$fecha', ven_ufecha_valor='$fecha_valor', ven_usaldo='$saldo_final' where ven_id='$venta->ven_id'";
//            $conec->ejecutar($sql_up);
            
            $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_tipo='pcuota' and ind_estado='Pagado' and ind_num_correlativo>0 order by ind_id desc limit 1");
            
            if($uid){
                $nro_inicio=$uid->ind_num_correlativo+1;
            }else{
                $nro_inicio=1;
            }
            if ($saldo_final > 0) {
                $atp = array('mp' => 'plazo', 'cm' => 'cuota', 'manual' => 'manual');
                $tipo_plan = $atp[$par->def_plan_efectivo];
                $params = array(
                    'int_id' => $venta->ven_int_id,
                    'ven_id' => $venta->ven_id,
                    'interes_anual' => $venta->ven_val_interes,
                    'moneda' => $venta->ven_moneda,
                    'concepto' => $venta->ven_concepto,
                    'fecha' => $venta->ven_fecha,
                    'saldo' => $saldo_final,
                    'tipo_plan' => 'cuota' ,
                    'plazo' => $venta->ven_plazo,
                    'cuota' => $venta->ven_cuota,
                    'nro_cuota_inicio' => $nro_inicio,
                    'fecha_inicio' => $fecha_valor,
                    'fecha_pri_cuota' => FUNCIONES::get_fecha_mysql($par->fecha_pri_cuota),
                    'val_form' => $venta->ven_form,
                    'rango' => $venta->ven_rango,
                    'frecuencia' => $venta->ven_frecuencia,
                );
//                FUNCIONES::print_pre($params);
                EVENTOS::reformular_plan($params, $conec);
            }
//            if($saldo_final<=0){
//                $sql_up="update venta set ven_estado='Pagado' where ven_id=$objeto->vneg_ven_id";
//                $conec->ejecutar($sql_up);
//            }
            
            $tarea_id = $objeto->vneg_id;
            
            $sql_up="update venta_negocio set vneg_estado='Activado' where vneg_id=$objeto->vneg_id";
            $conec->ejecutar($sql_up);
            $sql_up="update venta set ven_estado='Fusionado' where ven_id=$par->ori_ven_id";
            $conec->ejecutar($sql_up);
            $venta_ori=  FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$objeto->vneg_ven_ori");
//            $venta_ori=  FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$par->ori_ven_id'");
            $sql_up_lote="update lote set lot_estado='Disponible' where lot_id='$venta_ori->ven_lot_id'";
            $conec->ejecutar($sql_up_lote);
            
            $sql_sel_ucu_pen="select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$par->ori_ven_id' and ind_estado='Pendiente' and (ind_capital_pagado+ind_interes_pagado+ind_envio_pagado+ind_form_pagado)>0 and ind_tipo='pcuota' and ind_num_correlativo>0 order by ind_id asc limit 1";

            
            
            $ucu_pen=  FUNCIONES::objeto_bd_sql($sql_sel_ucu_pen);
    //        FUNCIONES::print_pre($ucu_pen);
            
//            $venta_ori=  FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$objeto->vneg_ven_ori");
            $pagado_ori=  EVENTOS::total_pagado($venta_ori->ven_id);
            $tot_pagado_ori=$par->ori_tot_capital;
            $ret_ingreso=$par->ret_ingreso;
            $capital_efectivo=$par->tot_capital;
            $saldo_ori =$venta_ori->ven_monto_efectivo-$pagado_ori->capital-$pagado_ori->descuento+$pagado_ori->incremento;
            
            if($ucu_pen){
                $sql_up="update interno_deuda set ind_estado='Pagado' , ind_saldo_final='$saldo_ori' where ind_id=$ucu_pen->ind_id";
                $conec->ejecutar($sql_up);
            }
            $sql_up="update interno_deuda set ind_estado='Retenido' where ind_estado='Pendiente'and ind_capital_pagado=0 and ind_tabla='venta' and ind_tabla_id=$par->ori_ven_id";
            $conec->ejecutar($sql_up);
            
            include_once 'clases/modelo_comprobantes.class.php';
            include_once 'clases/registrar_comprobantes.class.php';
            
            
            $referido=  FUNCIONES::interno_nombre($venta->ven_int_id);
            
            $glosa="Fusion de Pagos de la Venta Nro. $venta_ori->ven_id a la Venta Nro $venta->ven_id, $venta->ven_concepto";
            
            $urb_ori=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta_ori->ven_urb_id'");
            $urb_des=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

            

            $monto_intercambio=$venta->ven_monto_intercambio;

            $amontos=  FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
            $amontos_pag=FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");

            $data=array(
                'moneda'=>$venta_ori->ven_moneda,
                'moneda_des'=>$venta->ven_moneda,
                'ges_id'=>$_SESSION[ges_id],
                'fecha'=>$fecha,
                'glosa'=>$glosa,
                'interno'=>$referido,
                'tabla_id'=>$venta_ori->ven_id,
				'tarea_id'=>$tarea_id,
                'urb_ori'=>$urb_ori,
                'urb_des'=>$urb_des,

                'costo'=>$venta_ori->ven_costo,
                'costo_pagado_ori'=>$venta_ori->ven_costo_cub+$pagado_ori->costo,

                'saldo_ori'=>$saldo_ori,
                'tot_pagado_ori'=>$tot_pagado_ori,
                'ret_ingreso'=>$ret_ingreso,
                'capital_efectivo'=>$capital_pag,

                'intercambio'=>$monto_intercambio,
                'inter_montos'=>$amontos,
                'inter_montos_pag'=>$amontos_pag,
                
                'costo_pagado'=>$costo_pagado,
                'monto_pagar'=>$monto_pagar,
				'ven_can_codigo_ori' => $venta_ori->ven_can_codigo,
				'ven_can_codigo_dest' => $venta->ven_can_codigo,
            );
//            FUNCIONES::print_pre($data);            
            if ($urb_ori->urb_tipo == 'Externo' || $urb_des->urb_tipo == 'Externo') {
                //echo "<p>venta_fusion_ext</p>";
                $comprobante = MODELO_COMPROBANTE::venta_fusion_ext($data);
            } else {
                //echo "<p>venta_fusion</p>";
                $comprobante = MODELO_COMPROBANTE::venta_fusion($data);
            }            
            //FUNCIONES::print_pre($comprobante);
            COMPROBANTES::registrar_comprobante($comprobante);
            
        } else {// Anular
        }
    }
    
    public static function total_pagado($ven_id) {
//        $sql_pag = "select sum(ind_interes_pagado)as interes, sum(ind_capital_pagado) as capital, sum(ind_monto_pagado) as monto, 
//                            sum(ind_capital_desc) as descuento, sum(ind_capital_inc) as incremento ,
//                            sum(ind_costo_pagado) as costo
//                            from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id 
//                    ";
//        $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
        $pagado = FUNCIONES::total_pagado($ven_id);
        return $pagado;
    }

    public static function lote_concepto($lot_id) {
        $conec = new ADO();

        $sql = "select urb_nombre,man_nro,lot_nro,zon_nombre,uv_nombre 
        from 
        lote
        inner join zona on (lot_id='" . $lot_id . "' and lot_zon_id=zon_id)
        inner join uv on (lot_uv_id=uv_id)	
        inner join manzano on (lot_man_id=man_id)
        inner join urbanizacion on(man_urb_id=urb_id)
        ";

        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        $des = 'Urb:' . $objeto->urb_nombre . ' - Mza:' . $objeto->man_nro . ' - Lote:' . $objeto->lot_nro . ' - Zona:' . $objeto->zon_nombre . ' - UV:' . $objeto->uv_nombre;
        return $des;
    }

    function reformular_plan($params,&$conec=null,$adecuacion=null) {
//        'venta'=>$venta,
//        'int_id'=>$venta->ven_int_id,
//        'interes_anual'=>$venta->ven_val_interes,
//        'moneda'=>$venta->ven_moneda,
//        'concepto'=>$venta->ven_concepto,
//        'fecha'=>$venta->ven_concepto,
//        'saldo'=>$cob->vcob_saldo,
//        'tipo_plan'=>$cob->vcob_tipo_plan,
//        'plazo'=>$cob->vcob_plazo,
//        'cuota'=>$cob->vcob_cuota,
//        'fecha_inicio'=>$cob->vcob_fecha_valor,
//        'fecha_pri_cuota'=>$cob->vcob_fecha_pri_cuota,
        if($conec==null){
            $conec=new ADO();
        }
        
        $par=(object) $params;
        
        $sql_sel_ucu_pen="select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$par->ven_id' and ind_estado='Pendiente' and (ind_capital_pagado+ind_interes_pagado+ind_envio_pagado+ind_form_pagado)>0 and ind_tipo='pcuota' and ind_num_correlativo>0 order by ind_id asc limit 1";

        $ucu_pen=  FUNCIONES::objeto_bd_sql($sql_sel_ucu_pen);
//        FUNCIONES::print_pre($ucu_pen);
        if($ucu_pen){
            $sql_up="update interno_deuda set ind_estado='Pagado' , ind_saldo_final='$par->saldo' where ind_id=$ucu_pen->ind_id";
            $conec->ejecutar($sql_up);
        }
        
        
        $and_filtro='';
        if($par->ind_id){
            $and_filtro=" and ind_id>$par->ind_id";
        }
        
        $sql_del = "delete from interno_deuda where ind_estado='Pendiente' and ind_tabla='venta' and ind_tabla_id='".$par->ven_id."' and ind_tipo='pcuota' $and_filtro";
        $conec->ejecutar($sql_del);
        
        $saldo_financiar=$par->saldo;
//        echo "saldo $saldo_financiar <br>";
        if($saldo_financiar>0){
            $plazo="";
            $cuota="";
            if($par->tipo_plan=='plazo'){
                $plazo=$par->plazo;
                $vcuota = FUNCIONES::get_cuota($saldo_financiar, $par->interes_anual, $plazo, 1);
            }elseif($par->tipo_plan=='cuota'){
                $cuota=$par->cuota;
                $vcuota = $cuota;
            }
            $usuario_id=$_SESSION[id];
            $plan_data=array(
                'int_id'=>$par->int_id,
                'saldo'=>$saldo_financiar,                                
                'interes'=>$par->interes_anual,
                'tipo'=>$par->tipo_plan,
                'plazo'=>$par->plazo,
                'cuota'=>$par->cuota,
                'moneda'=>$par->moneda,
                'concepto'=>$par->concepto,
                'fecha'=>$par->fecha,
                'fecha_inicio'=>$par->fecha_inicio,
                'fecha_pri_cuota'=>  $par->fecha_pri_cuota,
                'usuario'=>  $usuario_id,
                'tabla'=> 'venta',
                'nro_cuota_inicio'=>  $par->nro_cuota_inicio,
                'tabla_id'=>  $par->ven_id,
                'ind_tipo'=>  'pcuota',
                'val_form'=>  $par->val_form,
                'rango'=>  $par->rango,
                'frecuencia'=>  $par->frecuencia,
            );
//            echo "<pre>";
//            print_r($plan_data);
//            echo "</pre>";
            $vplazo=  EVENTOS::generar_plan_pagos($plan_data,$conec);//
            $sql_up_venta="update venta set ven_plazo='$vplazo', ven_cuota='$vcuota', ven_rango='$par->rango', ven_frecuencia='$par->frecuencia' where ven_id=$par->ven_id";
            $conec->ejecutar($sql_up_venta);
        }else{
            $sql_up_venta="update venta set ven_estado='Pagado' where ven_id=$par->ven_id";
            $conec->ejecutar($sql_up_venta);
        }
        
    }
    
    public static function insertar_plan_pagos($params, &$conec = null) {
        if ($conec == null) {
            $conec = new ADO();
        }
//        FUNCIONES::print_pre($params);
        $par = (object) $params;
        $usuario_id = $_SESSION[id];
        $monto = $par->monto;
        $cuota_inicial = $par->cuota_inicial * 1;
        $saldo_financiar = $monto - $cuota_inicial;
        $tipo_plan = $par->tipo_plan;
        $fecha_cre = date('Y-m-d H:i:s');
        if ($cuota_inicial > 0) {
            $sql = "insert into interno_deuda(
                            ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_moneda,
                            ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,ind_estado,
                            ind_dias_interes,ind_concepto,ind_usu_id,ind_tipo,ind_fecha_cre,ind_form
                        )values(
                            'venta','$par->ven_id','0','$par->int_id','$par->fecha','$par->moneda',
                            '0','$cuota_inicial','$cuota_inicial','$saldo_financiar','$par->fecha','Pendiente',
                            '0','Cuota Inicial - $par->concepto','$usuario_id','pcuota','$fecha_cre','0'
                        )";
            $conec->ejecutar($sql);
        }
        if ($tipo_plan == 'plazo' || $tipo_plan == 'cuota') {
            if ($tipo_plan == 'plazo') {
                $_mp = $par->plazo;
                $_cm = '';
                $vcuota = FUNCIONES::get_cuota($saldo_financiar, $par->interes_anual, $_mp, 1);
            } elseif ($tipo_plan == 'cuota') {
                $_mp = $par->plazo;
                $_cm = $par->cuota;
                $vcuota = $_cm;
            }
            $plan_data = array(
                'int_id' => $par->int_id,
                'saldo' => $saldo_financiar,
                'interes' => $par->interes_anual,
                'tipo' => $tipo_plan,
                'plazo' => $_mp,
                'cuota' => $_cm,
                'moneda' => $par->moneda,
                'concepto' => $par->concepto,
                'fecha' => $par->fecha,
                'fecha_inicio' => $par->fecha,
                'fecha_pri_cuota' => $par->fecha_pri_cuota,
                'usuario' => $usuario_id,
                'tabla' => 'venta',
                'nro_cuota_inicio' => 1,
                'tabla_id' => $par->ven_id,
                'ind_tipo' => 'pcuota',
                'rango' => $par->rango,
                'frecuencia' => $par->frecuencia
            );
            $vplazo = EVENTOS::generar_plan_pagos($plan_data, $conec); //
            $sql_up_venta = "update venta set ven_plazo='$vplazo', ven_cuota='$vcuota', ven_rango='$par->rango', ven_frecuencia='$par->frecuencia' where ven_id=$par->ven_id";
            $conec->ejecutar($sql_up_venta);
            return $vplazo;
        }
    }

    public static function generar_plan_pagos($parametros, &$conec) {

        if ($conec == null) {
            $conec = new ADO();
        }
        $par = (object) $parametros;
        $lista_pagos = array();
        if ($par->tipo == "plazo") {//plazo
            $data = array(
                'tipo' => 'plazo',
                'plazo' => $par->plazo,
                'interes_anual' => $par->interes,
                'saldo' => $par->saldo,
                'fecha_inicio' => $par->fecha_inicio,
                'fecha_pri_cuota' => $par->fecha_pri_cuota,
                'nro_cuota_inicio' => $par->nro_cuota_inicio,
                'rango' => $par->rango,
                'frecuencia' => $par->frecuencia
            );
            $lista_pagos = FUNCIONES::plan_de_pagos($data);
        } elseif ($par->tipo == "cuota") {//cuota mensual
            $data = array(
                'tipo' => 'cuota',
                'cuota' => $par->cuota,
                'plazo' => $par->plazo,
                'interes_anual' => $par->interes,
                'saldo' => $par->saldo,
                'fecha_inicio' => $par->fecha_inicio,
                'fecha_pri_cuota' => $par->fecha_pri_cuota,
                'nro_cuota_inicio' => $par->nro_cuota_inicio,
                'rango' => $par->rango,
                'frecuencia' => $par->frecuencia
            );
            $lista_pagos = FUNCIONES::plan_de_pagos($data);
        } elseif ($par->cuota != "" && $par->plazo != "") {//plazo cuota
            $data = array(
                'tipo' => 'plazo_cuota',
                'cuota' => $par->cuota_mensual,
                'plazo' => $par->plazo,
                'interes_anual' => $par->interes,
                'saldo' => $par->saldo,
                'fecha_inicio' => $par->fecha_inicio,
                'fecha_pri_cuota' => $par->fecha_pri_cuota,
                'nro_cuota_inicio' => $par->nro_cuota_inicio
            );
            $lista_pagos = FUNCIONES::plan_de_pagos($data);
        }
        $nro_cuota = 0;
//            FUNCIONES::print_pre($lista_pagos);
//            return;
        $fecha_cre = date('Y-m-d H:i:s');
        foreach ($lista_pagos as $fila) {
            $sql = "insert into interno_deuda(
                            ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_moneda,
                            ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,ind_estado,
                            ind_dias_interes,ind_concepto,ind_usu_id,ind_tipo,ind_fecha_cre,ind_form
                        )values(
                            'venta','$par->tabla_id','$fila->nro_cuota','$par->int_id','$par->fecha','$par->moneda',
                            '$fila->interes','$fila->capital','$fila->monto','$fila->saldo','$fila->fecha','Pendiente',
                            '$fila->dias','Cuota Nro $fila->nro_cuota - $par->concepto','$par->usuario','pcuota','$fecha_cre','$par->val_form'
                        )";
//                echo "$sql;<br>";
            $nro_cuota = $fila->nro_cuota;
            $conec->ejecutar($sql);
        }
//            $nro_cuota_inicio++;
        return $nro_cuota;
    }

}
