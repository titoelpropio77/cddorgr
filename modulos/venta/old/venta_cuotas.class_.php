<?php
class VENTA_CUOTAS extends VENTA {
    function VENTA_CUOTAS(){
        parent::__construct();
    }

    function seguimiento() {
        $this->listado_pagos();   
    }

    function extractos() {
        $acc=$_GET[acc];
        $venta= FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if($acc=='verpago'){
            $this->imprimir_pago($venta,$_GET[pag_id]);
        }elseif($acc=='imp_ticket'){
            $this->imprimir_pago_ticket($venta,$_GET[pag_id]);
        }elseif($acc=='anular'){
            if($_POST[pag_id]){
                $this->anular_pago($_POST[pag_id]);
            }else{
                $this->listado_extractos($venta);
            }
        }else{
            $this->listado_extractos($venta);
        }
    }

    function anular_pago($pag_id, $origen = '') {
        $obsevacion_anu=$_POST[observacion_anu];
        $pag = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_id=$pag_id");
        $u_pag=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_ven_id='$_GET[id]' and vpag_estado='Activo' order by vpag_id desc limit 0,1");

        $resp = new stdClass();
        if ($pag->vpag_estado!='Activo' || $u_pag->vpag_id!=$pag->vpag_id || $pag->vpag_importado=='1') {
            if($pag){   
                $redirect="&tarea=EXTRACTOS&id=$pag->vpag_ven_id";
            }else{
                $redirect="&tarea=ACCEDER";
            }
            if ($origen == '') {
                $url = "$this->link?mod=$this->modulo$redirect";
                $this->formulario->ventana_volver("No se puede eliminar el pago por que no esta disponible", "$url", 'Volver', 'Error');
                return false;
            } else {
                $resp->ok = FALSE;
                $resp->mensaje = "No se puede eliminar el pago $pag_id por que no esta disponible";
                return $resp;
            }
        }
        
        include_once 'clases/registrar_comprobantes.class.php';
        $bool=COMPROBANTES::anular_comprobante('venta_pago', $pag_id);
        if(!$bool){
            
            $mensaje="El pago de la cuota no puede ser Anulada por que el periodo o la fecha en el que fue realizado el pago fue cerrado.";
            if ($origen == '') {
                $tipo='Error';			
                $this->formulario->ventana_volver($mensaje,$this->link . '?mod=' . $this->modulo."&tarea=EXTRACTOS&id=$pag->vpag_ven_id" ,'',$tipo);
                return;
            } else {
                $resp->ok = FALSE;
                $resp->mensaje = $mensaje;
                return $resp;
            }
        }
        include_once 'clases/recibo.class.php';
        RECIBO::anular($pag->vpag_recibo);
        
        $venta=  FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pag->vpag_ven_id'");
        $conec=new ADO();
        $conec->begin_transaccion();
        // anular pago..
        $fecha_cre=date('Y-m-d H:i:s');
        $sql_up="update venta_pago set vpag_estado='Anulado', vpag_fecha_anu='$fecha_cre',vpag_usu_anu='$_SESSION[id]', vpag_observacion_anu='$obsevacion_anu' where vpag_id='$pag_id'";
        $conec->ejecutar($sql_up);
        
        $sql_up="update interno_deuda_pago set idp_estado='Anulado' where  idp_vpag_id='$pag_id'";
        $conec->ejecutar($sql_up);
        $monto_pagado=($pag->vpag_monto.'')*1;
        if($monto_pagado>0){
            // DESHACER PAGO DE INTERES
            $str_interes_ids=trim($pag->vpag_interes_ids);
            if($str_interes_ids){
                $cuotas=  FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_interes_ids) and ind_tipo='pcuota' order by ind_id asc");
                $interes_ids = explode(',', trim($pag->vpag_interes_ids));
                $interes_montos = explode(',', trim($pag->vpag_interes_montos));
                if (count($interes_ids) ==  count($cuotas) && count($cuotas)==  count($interes_montos)){
                    for ($i = 0; $i < count($cuotas); $i++) {
                        $cu=$cuotas[$i];
                        $interes_pagado=$interes_montos[$i];
                        $sql="update interno_deuda set 
                                    ind_interes_pagado=ind_interes_pagado-$interes_pagado,
                                    ind_monto_pagado=ind_capital_pagado+ind_interes_pagado
                                where ind_id = '$cu->ind_id'
                            ";
                        $conec->ejecutar($sql);
                    }
                }else{
                    $conec->rollback();
                    
                    $mensaje = "Interes: Cantidad de cuotas de interes diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                    if ($origen == '') {
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                        return;
                    } else {
                        $resp->ok = FALSE;
                        $resp->mensaje = $mensaje;
                        return $resp;
                    }
                }
            }
            
            // DESHACER PAGO DE COSTO
            $str_costo_ids=trim($pag->vpag_costo_ids);
            if($str_costo_ids && $pag->vpag_costo>0){
                $cuotas=  FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_costo_ids) and ind_tipo='pcuota' order by ind_id asc");
                $costo_ids = explode(',', trim($pag->vpag_costo_ids));
                $costo_montos = explode(',', trim($pag->vpag_costo_montos));
                if (count($costo_ids) ==  count($cuotas) && count($cuotas)==  count($costo_montos)){
                    $costo_cub=$costos_pag=0;
                    for ($i = 0; $i < count($cuotas); $i++) {
                        $cu=$cuotas[$i];
                        $costo_pagado=$costo_montos[$i];
                        $sql="update interno_deuda set 
                                    ind_costo_pagado=ind_costo_pagado-$costo_pagado
                                where ind_id = '$cu->ind_id'
                            ";
                        if($cu->ind_num_correlativo==0){
                            $costo_cub=$costo_pagado;
                        }else{
                            $costos_pag+=$costo_pagado;
                        }
                        $conec->ejecutar($sql);
                    }
                    $sql_up_ven_costo="update venta set ven_costo_cub=ven_costo_cub-$costo_cub, ven_costo_pag=ven_costo_pag-$costos_pag 
                                        where ven_id=$venta->ven_id";
                    $conec->ejecutar($sql_up_ven_costo);
                }else{
                    $conec->rollback();
                    $mensaje = "Costo: Cantidad de cuotas de costo es diferente a la cantidad que existe en la base de datos o la cantidad de montos es diferente";
                    if ($origen == '') {
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                        return;
                    } else {
                        $resp->ok = FALSE;
                        $resp->mensaje = $mensaje;
                        return $resp;
                    }                    
                }
            }
            // DESHACER PAGO DE FORMULARIO
            $str_form_ids=trim($pag->vpag_form_ids);
            if($str_form_ids){
                $cuotas=  FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_form_ids) and ind_tipo='pcuota' order by ind_id asc");
                $form_ids = explode(',', trim($pag->vpag_form_ids));
                $form_montos = explode(',', trim($pag->vpag_form_montos));
                if (count($form_ids) ==  count($cuotas) && count($cuotas)==  count($form_montos)){
                    for ($i = 0; $i < count($cuotas); $i++) {
                        $cu=$cuotas[$i];
                        $form_pagado=$form_montos[$i];
                        $sql="update interno_deuda set 
                                    ind_form_pagado=ind_form_pagado-$form_pagado
                                where ind_id = '$cu->ind_id'
                            ";
                        $conec->ejecutar($sql);
                    }
                }else{
                    $conec->rollback();
                    $mensaje = "Forulario: Cantidad de cuotas de form diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                    if ($origen == '') {
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                        return;
                    } else {
                        $resp->ok = FALSE;
                        $resp->mensaje = $mensaje;
                        return $resp;
                    }
                }
            }
            // DESHACER PAGO DE FORMULARIO
            $str_envio_ids=trim($pag->vpag_envio_ids);
            if($str_envio_ids){
                $cuotas=  FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_envio_ids) and ind_tipo='pcuota' order by ind_id asc");
                $envio_ids = explode(',', trim($pag->vpag_envio_ids));
                $envio_montos = explode(',', trim($pag->vpag_envio_montos));
                if (count($envio_ids) ==  count($cuotas) && count($cuotas)==  count($envio_montos)){
                    for ($i = 0; $i < count($cuotas); $i++) {
                        $cu=$cuotas[$i];
                        $envio_pagado=$envio_montos[$i];
                        $sql="update interno_deuda set 
                                    ind_envio_pagado=ind_envio_pagado-$envio_pagado
                                where ind_id = '$cu->ind_id'
                            ";
                        $conec->ejecutar($sql);
                    }
                }else{
                    $conec->rollback();
                    $mensaje = "Forulario: Cantidad de cuotas de envio diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                    if ($origen == '') {
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                        return;
                    } else {
                        $resp->ok = FALSE;
                        $resp->mensaje = $mensaje;
                        return $resp;
                    }
                }
            }
            // DESHACER PAGO MORA
            // DESHACER MORA GENERADA
            $str_mora_gen_ids=trim($pag->vpag_mora_gen_ids);
            if($str_mora_gen_ids){
                $cuotas=  FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_mora_gen_ids) and ind_tipo='pcuota' order by ind_id asc");
                $mora_gen_ids = explode(',', trim($pag->vpag_mora_gen_ids));
                $mora_gen_montos = explode(',', trim($pag->vpag_mora_gen_montos));
                if (count($mora_gen_ids) ==  count($cuotas) && count($cuotas)==  count($mora_gen_montos)){
                    for ($i = 0; $i < count($cuotas); $i++) {
                        $cu=$cuotas[$i];
                        $sql = "update interno_deuda set 
                                    ind_mora=0
                                    where ind_id = '$cu->ind_id'
                                ";
                        $conec->ejecutar($sql);
                    }
                }else{
                    $conec->rollback();
                    $mensaje = "Mora Generado: Cantidad de cuotas de mora_gen diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                    if ($origen == '') {
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                        return;
                    } else {
                        $resp->ok = FALSE;
                        $resp->mensaje = $mensaje;
                        return $resp;
                    }
                }
            }
            $str_mora_ids=trim($pag->vpag_mora_ids);
            if($str_mora_ids){
                $cuotas=  FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_mora_ids) and ind_tipo='pcuota' order by ind_id asc");
                $mora_ids = explode(',', trim($pag->vpag_mora_ids));
                $mora_montos = explode(',', trim($pag->vpag_mora_montos));
                if (count($mora_ids) ==  count($cuotas) && count($cuotas)==  count($mora_montos)){
                    for ($i = 0; $i < count($cuotas); $i++) {
                        $cu=$cuotas[$i];
                        $mora_pagado=$mora_montos[$i];
                        $sql="update interno_deuda set 
                                    ind_mora_pagado=ind_mora_pagado-$mora_pagado
                                where ind_id = '$cu->ind_id'
                            ";
                        $conec->ejecutar($sql);
                    }
                }else{
                    $conec->rollback();
                    $mensaje = "Forulario: Cantidad de cuotas de mora diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                    if ($origen == '') {
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                        return;
                    } else {
                        $resp->ok = FALSE;
                        $resp->mensaje = $mensaje;
                        return $resp;
                    }
                }
            }
            
            // DESHACER PAGO DE CAPITAL
            $str_capital_ids=trim($pag->vpag_capital_ids);
            if($str_capital_ids){
                $cuotas=  FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_capital_ids) and ind_tipo='pcuota' order by ind_id asc");
                $capital_ids = explode(',', trim($pag->vpag_capital_ids));
                $capital_montos = explode(',', trim($pag->vpag_capital_montos));
//                echo FUNCIONES::print_pre($cuotas);
                if (count($capital_ids) ==  count($cuotas) && count($cuotas)==  count($capital_montos)){
                    for ($i = 0; $i < count($cuotas); $i++) {
                        $cu=$cuotas[$i];
                        $capital_pagado=$capital_montos[$i];
                        $sql="update interno_deuda set 
                                    ind_estado='Pendiente',
                                    ind_capital_pagado=ind_capital_pagado-$capital_pagado,
                                    ind_monto_pagado=ind_interes_pagado+ind_capital_pagado,
                                    ind_saldo_final=0
                                where ind_id = '$cu->ind_id'
                            ";
                        
                        if($venta->ven_frecuencia=='30_dias'){
                            $dias=$venta->ven_rango*30;
                            $fecha_pri_cuota=  FUNCIONES::sumar_dias($dias, $cu->ind_fecha_programada);
                        }elseif($venta->ven_frecuencia=='dia_mes'){
                            $af=  explode('-', $cu->ind_fecha_programada);
                            $fecha_pri_cuota=  FUNCIONES::sumar_meses($cu->ind_fecha_programada, $venta->ven_rango, $af[2]);
                        }
//                        echo "$cu->ind_capital_pagado>$cu->ind_capital <br>";
                        if($cu->ind_capital_pagado>$cu->ind_capital && $i == count($cuotas)-1){ //es la ultima cuota y el capital pagado es mayor al capital prog
                                $params = array(
                                    'int_id' => $venta->ven_int_id,
                                    'ven_id' => $venta->ven_id,
                                    'interes_anual' => $venta->ven_val_interes,
                                    'moneda' => $venta->ven_moneda,
                                    'concepto' => $venta->ven_concepto,
                                    'fecha' => $venta->ven_fecha,
                                    'saldo' => $cu->ind_saldo,
                                    'tipo_plan' => 'cuota',
                                    'plazo' => $venta->ven_plazo,
                                    'cuota' => $venta->ven_cuota,
                                    'nro_cuota_inicio' => $cu->ind_num_correlativo+1,
                                    'fecha_inicio' => $cu->ind_fecha_programada,
                                    'fecha_pri_cuota' => $fecha_pri_cuota,
                                    'val_form' => $venta->ven_form,
                                    'ind_id' => $cu->ind_id,
                                    'rango' => $venta->ven_rango,
                                    'frecuencia' => $venta->ven_frecuencia,

                                );
                                $this->reformular_plan($params, $conec);
                    //                echo 'Reformular<br>';

    //                        $sql="update interno_deuda set 
    //                                ind_capital=ind_capital_pagado,
    //                                ind_monto=ind_capital_pagado+ind_interes,
    //                                ind_saldo=ind_saldo_final
    //                            where ind_id = '$cu->ind_id'
    //                        ";
    //                        $conec->ejecutar($sql);
                        }
                        $conec->ejecutar($sql);
                    }
                    $sql_up_venta="update venta set ven_estado='Pendiente' where ven_id='$venta->ven_id'";
                    $conec->ejecutar($sql_up_venta);
                }else{
                    $conec->rollback();
                    $mensaje = "Capital: Cantidad de cuotas de capital diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                    if ($origen == '') {
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                        return;
                    } else {
                        $resp->ok = FALSE;
                        $resp->mensaje = $mensaje;
                        return $resp;
                    }
                }
            }
            
        }else{
            $capital_inc=($pag->vpag_capital_inc.'')*1;
            $capital_desc=($pag->vpag_capital_desc.'')*1;
            if($capital_inc>0 ||$capital_desc>0){
                $str_capital_ids= $pag->vpag_capital_ids;
//                $cu=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_id in ($str_capital_ids) and ind_tipo='pcuota' order by ind_id asc limit 1");
                ///PRIMER PENDIENTE
                $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_tipo='pcuota' and ind_capital_pagado=0 and ind_estado='Pendiente' order by ind_id asc limit 1");
                $nro_inicio=0;
                $fecha_pri_cuota='';
                if($uid){
                    $nro_inicio=$uid->ind_num_correlativo;
                    $fecha_pri_cuota=$uid->ind_fecha_programada;
                }else{
                    ///ULTIMO PAGADO
                    $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_tipo='pcuota' and ind_estado='Pagado' order by ind_id desc limit 1");
                    if($uid){
                        $nro_inicio=$uid->ind_num_correlativo+1;
                        if($venta->ven_frecuencia=='30_dias'){
                            $dias=$venta->ven_rango*30;
                            $fecha_pri_cuota=  FUNCIONES::sumar_dias($dias, $uid->ind_fecha_programada);
                        }elseif($venta->ven_frecuencia=='dia_mes'){
                            $af=  explode('-', $uid->ind_fecha_programada);
                            $fecha_pri_cuota=  FUNCIONES::sumar_meses($uid->ind_fecha_programada, $venta->ven_rango, $af[2]);
                        }
                    }else{
                        $nro_inicio=1;
                        if($venta->ven_frecuencia=='dia_mes'){
                            $af=  explode('-', $venta->ven_fecha);
                            $fecha_pri_cuota=  FUNCIONES::sumar_meses($venta->ven_fecha, $venta->ven_rango, $af[2]);
                        }else{// 30 dias
                            $dias=$venta->ven_rango*30;
                            $fecha_pri_cuota=  FUNCIONES::sumar_dias($dias, $venta->ven_fecha);
                        }
                        
                    }
                }
                $sql_up_id="delete from interno_deuda where ind_id in ($str_capital_ids)";
                $conec->ejecutar($sql_up_id);
                
//                $pagago= $this->total_pagado($venta->ven_id);
//                $saldo_financiar=$venta->ven_monto_efectivo-$pagago->capital-$pagago->interes+$pagago->incremento;
                $upago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_ven_id='$venta->ven_id' and vpag_estado='Activo' and vpag_id <$pag_id order by vpag_id desc");
                if($upago){
                    $saldo_financiar=$upago->vpag_saldo_final;
                    $fecha_inicio=$upago->vpag_fecha_valor;
                }else{
                    $saldo_financiar=$venta->ven_monto_efectivo;
                    $fecha_inicio=$venta->ven_fecha;
                }
                $params = array(
                    'int_id' => $venta->ven_int_id,
                    'ven_id' => $venta->ven_id,
                    'interes_anual' => $venta->ven_val_interes,
                    'moneda' => $venta->ven_moneda,
                    'concepto' => $venta->ven_concepto,
                    'fecha' => $venta->ven_fecha,
                    'saldo' => $saldo_financiar,
                    'tipo_plan' => 'cuota',
                    'plazo' => $venta->ven_plazo,
                    'cuota' => $venta->ven_cuota,
                    'nro_cuota_inicio' => $nro_inicio,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_pri_cuota' => $fecha_pri_cuota,
                    'val_form' => $venta->ven_form,

                    'rango' => $venta->ven_rango,
                    'frecuencia' => $venta->ven_frecuencia,
                );
//                echo "lllllllllllllllll pppppp<br>";
//                FUNCIONES::print_pre($params);
//                echo "lllllllllllllllll pppppp<br>";
                
                $this->reformular_plan($params, $conec);
            }
        }
        
        $u_pago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_ven_id='$venta->ven_id' and vpag_estado='Activo' order by vpag_id desc limit 0,1");

        if(!$u_pago){
            $u_pago=new stdClass();
            $u_pago->vpag_fecha_pago=$venta->ven_fecha;
            $u_pago->vpag_fecha_valor=$venta->ven_fecha;
            $u_pago->vpag_saldo_final=$venta->ven_monto_efectivo;
        }
        
        $ucuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");
        $pagado = $this->total_pagado($venta->ven_id);

        $capital_pag=$pagado->capital;
        $capital_inc=$pagado->incremento;
        $capital_desc=$pagado->descuento;
        $cuota_pag=$ucuota->ind_num_correlativo*1;
        
        $scuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 1");
        $ven_sfecha_prog=$scuota?$scuota->ind_fecha_programada:'0000-00-00';
        
        $sql_up="update venta set 
                        ven_ufecha_pago='$u_pago->vpag_fecha_pago', ven_ufecha_valor='$u_pago->vpag_fecha_valor', 
                        ven_cuota_pag='$cuota_pag', ven_capital_pag='$capital_pag',
                        ven_capital_inc='$capital_inc', ven_capital_desc='$capital_desc', 
                        ven_usaldo='$u_pago->vpag_saldo_final', ven_sfecha_prog='$ven_sfecha_prog'
                    where ven_id='$venta->ven_id'";

        $conec->ejecutar($sql_up);

        $sql_up_vale="update beneficiario_vale set val_estado='Anulado' where val_tabla='venta_pago' and val_tabla_id='$pag->vpag_id'";
        $conec->ejecutar($sql_up_vale);
        
        $this->barra_opciones($venta, 'EXTRACTOS');    
        echo "<br>";
        $exito = $conec->commit();
        if($exito){
            
            include_once 'clases/cupon.class.php';
            CUPON::anular_cupones($pag->vpag_id, 1);
            
            $url="$this->link?mod=$this->modulo&tarea=EXTRACTOS&id=$_GET[id]";
            $mensaje = "Anulacion de pago realizado Exitosamente";
            if ($origen == '') {
                $this->formulario->ventana_volver($mensaje,$url, '', "Correcto");
            } else {
                $resp->ok = TRUE;
                $resp->mensaje = $mensaje;
                return $resp;
            }
        }else{                            
            $exito = false;
            $mensajes=$conec->get_errores();
            $mensaje = implode('<br>', $mensajes);
//            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            
            if ($origen == '') {
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                return;
            } else {
                $resp->ok = FALSE;
                $resp->mensaje = $mensaje;
                return $resp;
            }
        }
    }

    function pagos() {
//        ini_set('display_errors', 'On');
//        $this->formulario->dibujar_tarea();
        $acc = $_GET[acc];
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$_GET[id]'");
        
        
        if ($acc != 'pagar') {                    
            $this->barra_opciones($venta,'PAGOS');
        }
        if($venta->ven_estado!='Pendiente'){
            $url=  "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La venta ya no se encuentra en estado </>Pendiente</b> para ser pagada","$url",'Volver' ,'Error');
            return;
        }
        
        if($venta->ven_bloqueado){
            $url=  "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado","$url",'Volver' );
            return;
        }
        
        
        if ($acc == 'pagar') {
            if ($_POST[cob_codigo]) {
                    //guardar pago
                $this->pagar_cuota($venta);
            } else {
                $this->barra_opciones($venta,'PAGOS');
                $url=  "$this->link?mod=$this->modulo&tarea=PAGOS&id=$venta->ven_id";
                $this->formulario->ventana_volver("No existe nigun cobro a ejecutarse por esta venta con codigo","$url",'Volver' ,'Error');
                return;
            }
        } else {
            $cobro_aut = FUNCIONES::objeto_bd_sql("select * from venta_cobro where vcob_ven_id=$_GET[id] and vcob_aut=1");
            $form_cajero = true;
            $conec = new ADO();
            
            if (!$cobro_aut) {// no existe cobro autorizado
                $mod_fecha=FUNCIONES::ad_parametro('par_modificar_fecha');
                if(!$mod_fecha){
                    $_POST[fecha_pago]=date('d/m/Y');
                    $_POST[fecha_valor]=date('d/m/Y');
                }
                if ($_POST[fecha_pago]) {
                    $conec->begin_transaccion();
                    // delete cualquier cobro cajero.
                    $sql_del = "delete from venta_cobro where vcob_ven_id=$_GET[id] and vcob_aut=0";
                    $conec->ejecutar($sql_del);
                    // inserto un cobro cajero
                    $params = array(
                        'venta' => $venta,
                        'fecha_pago' => FUNCIONES::get_fecha_mysql($_POST[fecha_pago]),
                        'fecha_valor' => FUNCIONES::get_fecha_mysql($_POST[fecha_valor]),
                    );
                    $bool=$this->generar_cobro($params);
                    $conec->commit();
                    if(!$bool){
                        if($_GET[ori]=='caja'){
                            $url=  "$this->link?mod=caja&tarea=ACCEDER";
                        }else{
                            $url=  "$this->link?mod=$this->modulo&tarea=ACCEDER";
                        }
                        
                        $this->formulario->ventana_volver("El Pago para la Fecha \"$_POST[fecha_pago]\" es 0","$url",'Volver' ,'Error');
                        return;
                    }
                    $form_cajero = true;
                } else {
                    // muestro formulario cobro cajero
                    $this->frm_fecha_cobro($venta);
                    $form_cajero = false;
                }
            }
            if ($form_cajero) {
                $this->frm_pagar_cuota($venta);
            }
        }
    }

    function imprimir_pago_ticket($venta,$pago_id){
        $this->barra_opciones($venta,'',false);
        echo '<br><br>';
        $this->formulario->dibujar_tarea();
        
        $conec= new ADO();		
        $sql="select *
                from venta_pago
                where vpag_id=$pago_id
        ";
        //echo $sql;
        $conec->ejecutar($sql);
        $objeto=$conec->get_objeto();
        ////
        include_once 'clases/recibo.class.php';
//        echo "<br>";
//        echo "<br>";
//        echo "<br>";
        
        $ucuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pendiente' and ind_fecha_programada>'$objeto->vpag_fecha' order by ind_id asc");
        if($ucuota){
            $nfecha=$ucuota->ind_fecha_programada;
        }else{
            $nfecha= FUNCIONES::sumar_dias(1, $objeto->vpag_fecha);
        }
        $par=  (Object)$this->calcular_cobro(array('venta'=>$venta,'fecha_pago'=>$nfecha,'fecha_valor'=>$nfecha));
        $txt_nfecha=  FUNCIONES::get_fecha_latina($nfecha);

//        $det_cabecera=array('Interes','Capital','Form.','Total');
//        $det_body=array($objeto->vpag_interes,$objeto->vpag_capital,$objeto->vpag_form,$objeto->vpag_monto);
        $det_cabecera=array();
        $det_body=array();
        
        if($objeto->vpag_interes>0){
            $det_cabecera[]='Interes';
            $det_body[]=$objeto->vpag_interes;
        }
        if($venta->ven_urb_id=='4'){
            $det_cabecera[]='Capital Pagado';
        }else{
            $det_cabecera[]='Capital';
        }
        $det_body[]=$objeto->vpag_capital;
        
        if($objeto->vpag_form>0){
            $det_cabecera[]='Form';
            $det_body[]=$objeto->vpag_form;
        }
        if($objeto->vpag_interes>0 || $objeto->vpag_form>0){
            $det_cabecera[]='Total';
            $det_body[]=$objeto->vpag_monto;
        }
        
        if($objeto->vpag_capital>0){
            $det_cabecera[]='Nro Cuotas';
            $det_body[]=$this->str_numeros_cuotas($objeto->vpag_capital_ids);
        }
        $pagado=  $this->total_pagado($venta->ven_id);
//        if($venta->ven_urb_id=='4'){
            $det_cabecera[]='Total Aportado';
            $det_body[]=$pagado->capital;
//        }
//        if($venta->ven_urb_id=='4'){
            $det_cabecera[]='Saldo Deudor';
            $det_body[]=$objeto->vpag_saldo_final;
//        }

        $data=array(
            'titulo'=>'PAGO DE CUOTA',
            'referido'=>  FUNCIONES::interno_nombre($venta->ven_int_id),
            'usuario'=>  FUNCIONES::usuario_nombre($objeto->vpag_usu_cre),
            'monto'=> $objeto->vpag_monto,
            'moneda'=> $objeto->vpag_moneda,
            'nro_recibo'=> $objeto->vpag_recibo,
            'fecha'=> $objeto->vpag_fecha_pago,
            'concepto'=> "Pago de la Venta Nro. $venta->ven_id - " . $venta->ven_concepto,
            'has_detalle'=>'1',
            'det_cabecera'=>$det_cabecera,
            'det_body'=>$det_body,
            'nota'=>"El proximo pago en fecha $txt_nfecha, debera pagar un monto de $par->monto (interes: $par->interes, capital:$par->capital, Formulario: $par->form)",
        );

            
            ?>
            <br><br><br>
                <?php RECIBO::pago_ticket($data);?>
            <?php		

    }
    function imprimir_pago($venta,$pago_id){
        
        $cupones = FUNCIONES::atributo_bd_sql("select count(cup_id)as campo from cupon
        where cup_vpag_id='$pago_id' and cup_estado='Activo' and cup_eliminado='No'")*1;
        
        $id_pago = $pago_id;
        if ($cupones == 0) {
            $id_pago = 0;
        }
        
        $this->barra_opciones($venta,'',true, $id_pago);
        echo '<br><br>';
        $this->formulario->dibujar_tarea();
        
        $conec= new ADO();		
        $sql="select *
                from venta_pago
                where vpag_id=$pago_id
        ";
        //echo $sql;
        $conec->ejecutar($sql);
        $objeto=$conec->get_objeto();
        ////
        include_once 'clases/recibo.class.php';
        echo "<br>";
        echo "<br>";
        echo "<br>";
        
        $ucuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pendiente' and ind_fecha_programada>'$objeto->vpag_fecha' order by ind_id asc");
        if($ucuota){
            $nfecha=$ucuota->ind_fecha_programada;
        }else{
            $nfecha= FUNCIONES::sumar_dias(1, $objeto->vpag_fecha);
        }
        $par=  (Object)$this->calcular_cobro(array('venta'=>$venta,'fecha_pago'=>$nfecha,'fecha_valor'=>$nfecha));
        $txt_nfecha=  FUNCIONES::get_fecha_latina($nfecha);

//        $det_cabecera=array('Interes','Capital','Form.','Total');
//        $det_body=array($objeto->vpag_interes,$objeto->vpag_capital,$objeto->vpag_form,$objeto->vpag_monto);
        $det_cabecera=array();
        $det_body=array();
        
        if($objeto->vpag_interes>0){
            $det_cabecera[]='Interes';
            $det_body[]=$objeto->vpag_interes;
        }
        if($venta->ven_urb_id=='4'){
            $det_cabecera[]='Capital Pagado';
        }else{
            $det_cabecera[]='Capital';
        }
        $det_body[]=$objeto->vpag_capital;
        
        if($objeto->vpag_form>0){
            $det_cabecera[]='Form';
            $det_body[]=$objeto->vpag_form;
        }
        if($objeto->vpag_interes>0 || $objeto->vpag_form>0){
            $det_cabecera[]='Total';
            $det_body[]=$objeto->vpag_monto;
        }
        
        if($objeto->vpag_capital>0){
            $det_cabecera[]='Nro Cuotas';
            $det_body[]=$this->str_numeros_cuotas($objeto->vpag_capital_ids);
        }
        $pagado=  $this->total_pagado($venta->ven_id);
//        if($venta->ven_urb_id=='4'){
            $det_cabecera[]='Total Aportado';
            $det_body[]=$pagado->capital;
//        }
//        if($venta->ven_urb_id=='4'){
            $det_cabecera[]='Saldo Deudor';
            $det_body[]=$objeto->vpag_saldo_final;
//        }

        $data=array(
            'titulo'=>'PAGO DE CUOTA',
            'referido'=>  FUNCIONES::interno_nombre($venta->ven_int_id),
            'usuario'=>  FUNCIONES::usuario_nombre($objeto->vpag_usu_cre),
            'monto'=> $objeto->vpag_monto,
            'moneda'=> $objeto->vpag_moneda,
            'nro_recibo'=> $objeto->vpag_recibo,
            'fecha'=> $objeto->vpag_fecha_pago,
            'concepto'=> "Pago de la Venta Nro. $venta->ven_id - " . $venta->ven_concepto,
            'has_detalle'=>'1',
            'det_cabecera'=>$det_cabecera,
            'det_body'=>$det_body,
            'nota'=>"El proximo pago en fecha $txt_nfecha, debera pagar un monto de $par->monto (interes: $par->interes, capital:$par->capital, Formulario: $par->form)",
        );

            
            ?>
            <br><br><br>
                <?php RECIBO::pago($data);?>
            <script>
                popup=null;
                $('#a_cupon').click(function(){
                    
                    var id = '<?php echo $pago_id;?>';
                    if(popup!==null){
                        popup.close();
                    }
                    
//                    var ruta='gestor.php?mod=con_comprobante&info=ok&tarea=VER&id=';
//                    window.open(ruta+id,'Comprobante','width=900, height=500, scrollbars=yes');
//                    popup = window.open(ruta+id,'Comprobante','width=900, height=500, scrollbars=yes');
//                    popup.document.close();
                    
                    var ruta='gestor.php?mod=venta&tarea=CUPON&id=<?php echo $venta->ven_id;?>&pag_id=<?php echo $pago_id;?>';
                    window.open(ruta,'Comprobante','width=900, height=500, scrollbars=yes');
                    popup = window.open(ruta,'Comprobante','width=900, height=500, scrollbars=yes');
                    popup.document.close();
                
                });
            </script>
            <?php		

    }
    
    function cupon(){
        $this->imprimir_cupones($_GET[pag_id]);
    }
    
    function imprimir_cupones($vpag_id) {

        $pagina = "'contenido_reporte'";
        $page = "'about:blank'";
        $extpage = "'reportes'";
        $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";
        $extra1 = "<html><head><title>Vista Previa</title><head>
                        <link href=css/estilos.css rel=stylesheet type=text/css />
                  </head>
                  <body>
                  <div id=imprimir>
                  <div id=status>
                  <p>";
        
//        $extra1.=" <a href=javascript:window.print();>Imprimir</a> 
//                          <a href=javascript:self.close();>Cerrar</a>
//                          </p>
//                          </div>
//                          </div>
//                          <center>";
//        
//        $extra2 = "</center></body></html>";      
        
        $extra1.=" <a href=javascript:window.print();>Imprimir</a> 
                          <a href=javascript:self.close();>Cerrar</a>
                          </p>
                          </div>
                          </div>";
        
        $extra2 = "</body></html>";                      
        echo $extra1.$extra2;

        $cupones = FUNCIONES::lista_bd_sql("select * from cupon where cup_estado='Activo'
        and cup_eliminado='No' and cup_vpag_id='$vpag_id'");
        
        include_once 'clases/cupon.class.php';
        ?>
            <!--<div id="contenido_reporte">-->
                <?php
                foreach ($cupones as $cupon) {
                    ?>
                <!--<hr style="color: black;" />-->
                    <?php
//                    CUPON::imprimir_cupon_anfora($cupon->cup_id);                    
                    CUPON::imprimir_cupon($cupon->cup_id);
                    ?>
                <br/>                                        
                <p style="page-break-after: always"></p>
                    <?php
                }
                ?>
            <!--</div>-->    
            <script>
            window.print();
            </script>
        <?php        
    }

    function guardar_vale($val_monto,$venta,$nro_recibo,$vpag_id,$fecha_pago) {
        $conec=new ADO();
        $monto=$val_monto;
        $moneda=$venta->ven_moneda;//$_POST[moneda];
        $descripcion="Vale consumido por el Pago Nro $vpag_id, $venta->ven_concepto Rec. $nro_recibo";
        $fecha=  $fecha_pago;
        $tipo=  'MEN';
        $fecha_cre=date('Y-m-d H:i:s');
        $sql_ins="insert into beneficiario_vale(
                        val_int_id,val_monto,val_moneda,val_fecha,val_descripcion,
                        val_tipo,val_estado,val_tabla,val_tabla_id,val_usu_cre,val_fecha_cre
                    )values(
                        '$venta->ven_int_id','-$monto','$moneda','$fecha','$descripcion',
                        '$tipo','Activo','venta_pago','$vpag_id','$_SESSION[id]','$fecha_cre'
                    );";
        $conec->ejecutar($sql_ins);
    }
    
    function pagar_cuota($venta) {
        $conec = new ADO();
        $conec->begin_transaccion();
        $codigo = $_POST[cob_codigo];
        
        $cob = FUNCIONES::objeto_bd_sql("select * from venta_cobro where vcob_ven_id='$venta->ven_id' and vcob_codigo='$codigo'");
        if (!$cob) {
            $url = "$this->link?mod=$this->modulo&tarea=PAGOS&id=$venta->ven_id";
            $this->formulario->ventana_volver("No existe nigun cobro a ejecutarse por esta venta", "$url", 'Volver', 'Error');
            return false;
        }
        
        $vpago=FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_ven_id='$venta->ven_id' and vpag_cob_codigo='$codigo'");
        if ($vpago) {
            $url = "$this->link?mod=$this->modulo&tarea=PAGOS&id=$venta->ven_id";
            $this->formulario->ventana_volver("Ya existe un pago con el mismo codigo en la Venta", "$url", 'Volver', 'Error');
            return false;
        }
        
        $str_interes_ids = trim($cob->vcob_interes_ids);
        if ($str_interes_ids && $cob->vcob_interes > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_interes_ids) and ind_tipo='pcuota' order by ind_id asc");
            $interes_ids = explode(',', trim($cob->vcob_interes_ids));
            if (count($interes_ids) !=  count($cuotas) ){
                $url = "$this->link?mod=$this->modulo&tarea=PAGOS&id=$venta->ven_id";
                $this->formulario->ventana_volver("No existen las cuotas para cobrar el interes", "$url", 'Volver', 'Error');
                return false;
            }
        }
        $str_capital_ids = trim($cob->vcob_capital_ids);
        if ($str_capital_ids && $cob->vcob_capital > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_capital_ids) and ind_tipo='pcuota' order by ind_id asc");
            $capital_ids = explode(',', trim($cob->vcob_capital_ids));
            if (count($capital_ids) !=  count($cuotas) ){
                $url = "$this->link?mod=$this->modulo&tarea=PAGOS&id=$venta->ven_id";
                $this->formulario->ventana_volver("No existen las cuotas para cobrar el capital", "$url", 'Volver', 'Error');
                return false;
            }
        }
        $str_form_ids = trim($cob->vcob_form_ids);
        if ($str_form_ids && $cob->vcob_form > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_form_ids) and ind_tipo='pcuota' order by ind_id asc");
            $form_ids = explode(',', trim($cob->vcob_form_ids));
            if (count($form_ids) !=  count($cuotas) ){
                $url = "$this->link?mod=$this->modulo&tarea=PAGOS&id=$venta->ven_id";
                $this->formulario->ventana_volver("No existen las cuotas para cobrar el formulario", "$url", 'Volver', 'Error');
                return false;
            }
        }
        $str_envio_ids = trim($cob->vcob_envio_ids);
        if ($str_envio_ids && $cob->vcob_envio > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_envio_ids) and ind_tipo='pcuota' order by ind_id asc");
            $envio_ids = explode(',', trim($cob->vcob_envio_ids));
            if (count($envio_ids) !=  count($cuotas) ){
                $url = "$this->link?mod=$this->modulo&tarea=PAGOS&id=$venta->ven_id";
                $this->formulario->ventana_volver("No existen las cuotas para cobrar el envio", "$url", 'Volver', 'Error');
                return false;
            }
        }
//        echo "<pre>";
//        print_r($cob);
//        echo "</pre>";
        
        $val_monto=$_POST[val_monto]*1;
        $interes=$cob->vcob_interes;
        
        $saldo_descuento =  FUNCIONES::atributo_bd_sql("select sum(val_monto) as campo from beneficiario_vale where val_int_id=$venta->ven_int_id and val_estado='Activo'")*1;
        $descuento=$val_monto;
        if($descuento>0){
            if($descuento>$interes || $descuento>$saldo_descuento){
                $url = "$this->link?mod=$this->modulo&tarea=PAGOS&id=$venta->ven_id";
                $this->formulario->ventana_volver("El descuento de debe ser menor al interes y al saldo de descuento", "$url", 'Volver', 'Error');
                return false;
            }
        }

        $fecha_cre = date('Y-m-d H:i:s');
        $usuario_id = $this->usu->get_id();
        $fecha_pago = $cob->vcob_fecha_pago;
        $pag_codigo=  FUNCIONES::fecha_codigo();
        if($_POST[vpag_nro_recibo]){
            $nro_recibo= trim($_POST[vpag_nro_recibo]);
        }else{
            $nro_recibo=  FUNCIONES::nro_recibo($fecha_pago);
        }
        
        
        
        $monto=$cob->vcob_monto;
        if($val_monto>0){
            $interes=$cob->vcob_interes-$val_monto;
            $monto=$cob->vcob_monto-$val_monto;
        }
        $cob->vcob_interes=$interes;
        $cob->vcob_interes_montos=$interes.'';
        
        
        
        //crear registro venta pago
        $sql_pago = "insert into venta_pago(
                        vpag_ven_id,vpag_codigo,vpag_fecha_pago,vpag_fecha_valor,
                        vpag_int_id,vpag_moneda,vpag_saldo_inicial,vpag_dias_interes,
                        vpag_interes,vpag_capital,vpag_mora,vpag_form,vpag_envio,vpag_monto,vpag_saldo_final,
                        vpag_interes_ids,vpag_capital_ids,vpag_form_ids,vpag_envio_ids,vpag_mora_ids,vpag_mora_con_ids,vpag_mora_gen_ids,
                        vpag_interes_montos,vpag_capital_montos,vpag_form_montos,vpag_envio_montos,vpag_mora_montos,vpag_mora_con_montos,vpag_mora_gen_montos,vpag_mora_gen_dias,
                        vpag_fecha_cre,vpag_usu_cre,vpag_estado,vpag_cob_usu,vpag_cob_codigo,vpag_cob_aut,vpag_recibo,vpag_suc_id,vpag_interes_desc,
                        vpag_val_monto
                    )values(
                        '$venta->ven_id','$pag_codigo','$fecha_pago','$cob->vcob_fecha_valor',
                        '$venta->ven_int_id','$venta->ven_moneda','$cob->vcob_saldo_inicial','$cob->vcob_dias_interes',
                        '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_envio','$monto','$cob->vcob_saldo_final',
                        '$cob->vcob_interes_ids','$cob->vcob_capital_ids','$cob->vcob_form_ids','$cob->vcob_envio_ids','$cob->vcob_mora_ids','$cob->vcob_mora_con_ids','$cob->vcob_mora_gen_ids',
                        '$cob->vcob_interes_montos','$cob->vcob_capital_montos','$cob->vcob_form_montos','$cob->vcob_envio_montos','$cob->vcob_mora_montos','$cob->vcob_mora_con_montos','$cob->vcob_mora_gen_montos','$cob->vcob_mora_gen_dias',
                        '$fecha_cre','$usuario_id','Activo','$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut','$nro_recibo','$_SESSION[suc_id]','$cob->vcob_interes_desc',
                        '$val_monto'
                        )";
        $conec->ejecutar($sql_pago, true, true);
        $pago_id = ADO::$insert_id;
        $this->guardar_vale($val_monto,$venta,$nro_recibo,$pago_id,$fecha_pago);
//        mysql_insert_id
        include_once 'clases/recibo.class.php';
        $data_recibo=array(
                'recibo'=>$nro_recibo,
                'fecha'=>$fecha_pago,
                'monto'=>$monto,
                'moneda'=>$venta->ven_moneda,
                'tabla'=>'venta_pago',
                'tabla_id'=>$pago_id
                
            );
        RECIBO::insertar($data_recibo);
        
        $data_pago=(object)array(
            'cob'=>$cob,
            'venta'=>$venta,
            'pago_id'=>$pago_id,
            'fecha_pago'=>$fecha_pago,
            'fecha_cre'=>$fecha_cre,
            'usuario_id'=>$usuario_id,
            'recibo'=>$nro_recibo,
            'pag_codigo'=>$pag_codigo,
            'val_monto'=>$val_monto,
        );
        
        //actualizar interno_deuda
        // *********** PAGAR INTERES        
        $this->pagar_cu_interes($data_pago,$conec);
        // *********** PAGAR CAPITAL        
        $res_cap=(object)$this->pagar_cu_capital($data_pago,$conec);
        //************ PAGAR FORMULARIO        
        $this->pagar_cu_form($data_pago,$conec);
        //****-******* PAGAR ENVIO        
        $this->pagar_cu_envio($data_pago,$conec);
        //************ PAGO MORA        
        $this->pagar_cu_mora($data_pago,$conec);
        //*********************************************
        
        $ucuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");

        $pagado = $this->total_pagado($venta->ven_id);

        $capital_pag=$pagado->capital;
        $capital_inc=$pagado->incremento;
        $capital_desc=$pagado->descuento;
        $cuota_pag=$ucuota->ind_num_correlativo;
        
        $scuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 1");
        $ven_sfecha_prog=$scuota?$scuota->ind_fecha_programada:'0000-00-00';
        
        $sql_up="update venta set 
                            ven_ufecha_pago='$fecha_pago', ven_ufecha_valor='$cob->vcob_fecha_valor', 
                            ven_cuota_pag='$cuota_pag', ven_capital_pag='$capital_pag',
                            ven_capital_inc='$capital_inc', ven_capital_desc='$capital_desc', 
                            ven_usaldo='$cob->vcob_saldo_final', ven_sfecha_prog='$ven_sfecha_prog'
                    where ven_id='$venta->ven_id'";

        $conec->ejecutar($sql_up);

//        $sql_up="update venta set ven_ufecha_pago='$fecha_pago', ven_ufecha_valor='$cob->vcob_fecha_valor', ven_usaldo='$cob->vcob_saldo_final' where ven_id='$venta->ven_id'";
//        $conec->ejecutar($sql_up);
        
        $sql_del = "delete from venta_cobro where vcob_ven_id='$venta->ven_id' and vcob_codigo='$codigo'";
        $conec->ejecutar($sql_del);
        
        $urb=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$venta->ven_urb_id");

        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';
        $referido=  FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$venta->ven_int_id'");
        $glosa = "Pago de la Venta Nro. $venta->ven_id - $venta->ven_concepto - $referido - Rec. $nro_recibo" ;

        $params=array(
                'tabla'=>'venta_pago',
                'tabla_id'=>$pago_id,
                'fecha'=>$fecha_pago,
                'moneda'=>$venta->ven_moneda,
                'ingreso'=>true,
                'une_id'=>$urb->urb_une_id,
                'glosa'=>$glosa,'ca'=>'0','cf'=>0,'cc'=>0
            );
        $detalles = FORMULARIO::insertar_pagos($params);
//        '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_monto'
        $data=array(
            'moneda'=>$venta->ven_moneda,
            'ges_id'=>$_SESSION[ges_id],
            'fecha'=>$fecha_pago,
            'glosa'=>$glosa,
            'interno'=>$referido,
            'tabla_id'=>$pago_id,
            'urb'=>$urb,

            'interes'=>$interes,
            'capital'=>$cob->vcob_capital,
            'form'=>$cob->vcob_form,
            'envio'=>$cob->vcob_envio,
            'mora'=>$cob->vcob_mora,
            'detalles'=>$detalles,
            'costo'=>$res_cap->costo,
        );
        if($urb->urb_tipo=='Interno'){
            $comprobante = MODELO_COMPROBANTE::pago_cuota($data);
        }elseif($urb->urb_tipo=='Externo'){
            $comprobante = MODELO_COMPROBANTE::pago_cuota_ext($data);
        }

        COMPROBANTES::registrar_comprobante($comprobante);

        
        $exito = $conec->commit();
        if ($exito) {
            include_once 'clases/cupon.class.php';
            
            CUPON::generar_cupones($pago_id, 1);
            
            $this->imprimir_pago($venta,$pago_id);
//            $this->formulario->ventana_volver("Pago realizado Correctamente", $this->link . '?mod=' . $this->modulo, '', $tipo);
        } else {
            $exito = false;
            $mensajes = $conec->get_errores();
            $mensaje = implode('<br>', $mensajes);
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }
    }
    
    
    function pagar_cu_interes($data,&$conec=null){
        if($conec==null){
            $conec=new ADO();
        }
//        'cob'=>$cob,
//        'venta'=>$venta,
//        'pago_id'=>$pago_id,
//        'fecha_pago'=>$fecha_pago,
//        'usuario_id'=>$usuario_id,
//        'recibo'=>$usuario_id,
        $cob=$data->cob;
        $venta=$data->venta;
        $pago_id=$data->pago_id;
        $fecha_pago=$data->fecha_pago;
        $fecha_cre=$data->fecha_cre;
        $usuario_id=$data->usuario_id;
        $recibo=$data->recibo;
        $pag_codigo=$data->pag_codigo;
        
        $str_interes_ids = trim($cob->vcob_interes_ids);
        if ($str_interes_ids && $cob->vcob_interes > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_interes_ids) and ind_tipo='pcuota' order by ind_id asc");
//            $vpag_interes = $cob->vcob_interes;
            $interes_montos = explode(',', trim($cob->vcob_interes_montos));
            $interes_ids = explode(',', trim($cob->vcob_interes_ids));
            if (count($interes_ids) ==  count($cuotas) && count($cuotas)==  count($interes_montos)){
                for ($i = 0; $i < count($cuotas); $i++) {
                    $cu = $cuotas[$i];
//                    $interes_dif = $vpag_interes - ($cu->ind_interes * 1);
                    $interes_pagado = $interes_montos[$i]; //$cu->ind_interes;
                    $sql_ins_idp = "insert into interno_deuda_pago(
                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,
                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                    )values(
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','interes',
                                        '$fecha_pago','$interes_pagado','Activo','$fecha_cre','$usuario_id',
                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'
                                    )
                        ";
                    $conec->ejecutar($sql_ins_idp);
                    
                    $sql = "update interno_deuda set 
                                ind_interes_pagado=ind_interes_pagado+$interes_pagado,
                                ind_monto_pagado=ind_capital_pagado+ind_interes_pagado
                                where ind_id = '$cu->ind_id'
                            ";
                    //            echo $sql.';<br>';
                    $conec->ejecutar($sql);
                }
            }else{
                $conec->rollback();
                $mensaje = "Interes: Cantidad de cuotas de interes diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                return;
            }
        }
    }
    
    function pagar_cu_capital($data,&$conec=null){
        if($conec==null){
            $conec=new ADO();
        }

        $cob=$data->cob;
        $venta=$data->venta;
        $pago_id=$data->pago_id;
        $fecha_pago=$data->fecha_pago;
        $fecha_cre=$data->fecha_cre;
        $usuario_id=$data->usuario_id;
        $recibo=$data->recibo;
        $pag_codigo=$data->pag_codigo;
        
        $costo_cub=0;
        $costo_pag=0;
        $costo=0;
        $costo_ids=array();
        $costo_montos=array();
        $str_capital_ids = trim($cob->vcob_capital_ids);
        /*AQUI ME QUEDE... CALCULANDO EL SALDO DEL COSTO DE VENTA..*/
        $saldo_venta=$venta->ven_monto_efectivo-$venta->ven_cuota_inicial;
        $saldo_costo=$venta->ven_costo-$venta->ven_cuota_inicial-$venta->ven_costo_cub;
        if ($str_capital_ids && $cob->vcob_capital > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_capital_ids) and ind_tipo='pcuota' order by ind_id asc");
//            $vpag_capital = $cob->vcob_capital;
            $capital_montos = explode(',', trim($cob->vcob_capital_montos));
            $capital_ids = explode(',', trim($cob->vcob_capital_ids));
            if (count($capital_ids) ==  count($cuotas) && count($cuotas)==  count($capital_montos)){
                $is_reformular=false;
                for ($i = 0; $i < count($cuotas); $i++) {
                    $cu = $cuotas[$i];
//                    $capital_dif = $vpag_capital - ($cu->ind_capital * 1);
                    $capital_pagado = $capital_montos[$i]; //$cu->ind_capital;
                    $ind_estado = 'Pendiente';
                    $saldo_final=0;
                    if ($capital_pagado.''==$cu->ind_capital.'') {  
                        $ind_estado = 'Pagado';
                        $saldo_final = $cu->ind_saldo;
                    }else{
                        if($i == 0 && $i == count($cuotas)-1){// primer y el ultimo pago
//                            echo " ENTRO PRIMER IF <br>";
                            $ind_capital_pagado=$cu->ind_capital_pagado+$capital_pagado;
                            if($ind_capital_pagado.''==$cu->ind_capital.''){
                                $ind_estado = 'Pagado';
                                $saldo_final = $cu->ind_saldo;
                            }elseif($ind_capital_pagado > $cu->ind_capital){
                                $ind_estado = 'Pagado';
                                $capital_dif = $ind_capital_pagado - ($cu->ind_capital * 1);
                                $saldo_final = $cu->ind_saldo-$capital_dif;
                                $is_reformular=true;
                                $ant_saldo=$cu->ind_saldo;
                                $ant_capital=$cu->ind_capital;
                                $ant_fecha_prog=$cu->ind_fecha_programada;
                                $ant_ant_cuota=$venta->ven_cuota;
                            }
                            
                        }elseif($i == 0){// primer pago de captital
//                            echo " ENTRO SEGUNDO IF <br>";
                            $ind_capital_pagado=$cu->ind_capital_pagado+$capital_pagado;
                            
                            if($ind_capital_pagado.'' ==$cu->ind_capital.''){    
                                $ind_estado = 'Pagado';
                                $saldo_final = $cu->ind_saldo;
                            }elseif($ind_capital_pagado > $cu->ind_capital){
                                $conec->rollback();
                                $mensaje = "El Primero de varios Pagos no puede ser mayor a al Capital acordado ";
                                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                                return;
                            }
                        }elseif($i == count($cuotas)-1){ /// si es el ultimo pago
//                            echo " ENTRO TERCER IF <br>";
                            if($capital_pagado>$cu->ind_capital){ /// pago por demas
                                $ind_estado = 'Pagado';
                                $capital_dif = $capital_pagado - ($cu->ind_capital * 1);
                                $saldo_final = $cu->ind_saldo-$capital_dif;
                                $is_reformular=true;
                                
                                $ant_saldo=$cu->ind_saldo;
                                $ant_capital=$cu->ind_capital;
                                $ant_fecha_prog=$cu->ind_fecha_programada;
                                $ant_ant_cuota=$venta->ven_cuota;
                            }else{// realizo un pago parcial no mueve el estado ni el saldo
                                
                            }
                        }else{
                            $conec->rollback();
                            $mensaje = "Solo el ultimo o el Primer Pago puede ser diferente ";
                            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                            return;
                        }
                    }
                    
                    $sql_ins_idp = "insert into interno_deuda_pago(
                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,
                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                    )values(
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','capital',
                                        '$fecha_pago','$capital_pagado','Activo','$fecha_cre','$usuario_id',
                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'
                                    )
                        ";

                    $conec->ejecutar($sql_ins_idp);
                    $saldo_costo=$venta->ven_costo-$venta->ven_res_anticipo-$venta->ven_monto_intercambio-$venta->ven_cuota_inicial;
                    $costo_pagado=0;
                    if($saldo_costo>0){
                        if($cu->ind_num_correlativo==0){
                            $costo_pagado=$capital_pagado;
                            $costo_cub=$costo_pagado;
                        }else{
                            if($cob->vcob_saldo_final<=0 && $i==count($cuotas)-1){
                                $costo_pagado=$venta->ven_costo-$venta->ven_costo_cub-$venta->ven_costo_pag-$costo;
                            }else{
                                $costo_pagado=round(($capital_pagado*$saldo_costo)/$saldo_venta,2);
                            }
                            $costo_pag+=$costo_pagado;
                        }
                        $costo+=$costo_pagado;
                        $costo_ids[]=$cu->ind_id;
                        $costo_montos[]=$costo_pagado;
                    }
                    if($costo_pagado>0){
                        $sql_ins_idp = "insert into interno_deuda_pago(
                                            idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,
                                            idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                            idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                        )values(
                                            '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','costo',
                                            '$fecha_pago','$costo_pagado','Activo','$fecha_cre','$usuario_id',
                                            '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'
                                        )
                            ";
                        $conec->ejecutar($sql_ins_idp);
                    }

                    $sql = "update interno_deuda set 
                                ind_estado='$ind_estado',
                                ind_fecha_pago='$fecha_pago',
                                ind_capital_pagado=ind_capital_pagado+$capital_pagado,
                                ind_monto_pagado=ind_interes_pagado+ind_capital_pagado,
                                ind_costo_pagado=ind_costo_pagado+$costo_pagado,
                                ind_saldo_final='$saldo_final'
                                where ind_id = '$cu->ind_id'
                            ";
//                    echo $sql.';<br>';
                    $conec->ejecutar($sql);
                }
                ///ACTUALIZAR COSTO DE VENTA PAGO
                $txt_costo_ids=  implode(',', $costo_ids);
                $txt_costo_montos=  implode(',', $costo_montos);
                $sql_up_pag_costo="update venta_pago set vpag_costo='$costo', vpag_costo_ids='$txt_costo_ids', vpag_costo_montos='$txt_costo_montos'
                                where vpag_id=$pago_id";
                $conec->ejecutar($sql_up_pag_costo);
                ///ACTUALIZAR COSTO DE VENTA
                $set_ven_estado="";
                if($cob->vcob_saldo_final<=0){
                    $set_ven_estado=" ,ven_estado='Pagado'";
                }
                $sql_up_ven_costo="update venta set ven_costo_cub=ven_costo_cub+$costo_cub, ven_costo_pag=ven_costo_pag+$costo_pag $set_ven_estado
                                where ven_id=$venta->ven_id";
                $conec->ejecutar($sql_up_ven_costo);
                
//                echo "CAPITAL DIFERENCIA" . $capital_dif . '<br>';
                if ($is_reformular && $cob->vcob_reformular == '1') { // reformular
                    $params = array(
                        'int_id' => $venta->ven_int_id,
                        'ven_id' => $venta->ven_id,
                        'interes_anual' => $venta->ven_val_interes,
                        'moneda' => $venta->ven_moneda,
                        'concepto' => $venta->ven_concepto,
                        'fecha' => $venta->ven_fecha,
                        'saldo' => $cob->vcob_saldo_final,
                        'tipo_plan' => 'cuota',//$cob->vcob_tipo_plan,
                        'plazo' => $venta->ven_plazo,//$cob->vcob_plazo,
                        'cuota' =>  $venta->ven_cuota,//$cob->vcob_cuota,
                        'nro_cuota_inicio' => $cob->vcob_nro_cuota_sig,
                        'fecha_inicio' => $cob->vcob_fecha_valor,
                        'fecha_pri_cuota' => $cob->vcob_fecha_pri_cuota,
                        'val_form' => $venta->ven_form,
                        'rango'=>  $venta->ven_rango,
                        'frecuencia'=>  $venta->ven_frecuencia,
                    );
                    $this->reformular_plan($params, $conec);
    //                echo 'Reformular<br>';
                }
            }else{
                $conec->rollback();
                $mensaje = "Capital: Cantidad de cuotas de capital diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                return;
            }
        }
        return array('type'=>'success','costo'=>$costo);
        
    }
    
    function pagar_cu_form($data,&$conec=null){
        if($conec==null){
            $conec=new ADO();
        }
        $cob=$data->cob;
        $venta=$data->venta;
        $pago_id=$data->pago_id;
        $fecha_pago=$data->fecha_pago;
        $fecha_cre=$data->fecha_cre;
        $usuario_id=$data->usuario_id;
        $recibo=$data->recibo;
        $pag_codigo=$data->pag_codigo;
        
        $str_form_ids = trim($cob->vcob_form_ids);
        if ($str_form_ids && $cob->vcob_form > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_form_ids) and ind_tipo='pcuota' order by ind_id asc");
//            $vpag_form = $cob->vcob_form;
            $form_montos = explode(',', trim($cob->vcob_form_montos));
            $form_ids = explode(',', trim($cob->vcob_form_ids));
            if (count($form_ids) ==  count($cuotas) && count($cuotas)==  count($form_montos)){
                for ($i = 0; $i < count($cuotas); $i++) {
                    $cu = $cuotas[$i];
//                    $form_dif = $vpag_form - ($cu->ind_form * 1);
                    $form_pagado = $form_montos[$i]; //$cu->ind_form;
                    $sql_ins_idp = "insert into interno_deuda_pago(
                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,
                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                    )values(
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','formulario',
                                        '$fecha_pago','$form_pagado','Activo','$fecha_cre','$usuario_id',
                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'
                                    )
                        ";
                    $conec->ejecutar($sql_ins_idp);
                    
                    $sql = "update interno_deuda set 
                                ind_form_pagado=ind_form_pagado+$form_pagado
                                where ind_id = '$cu->ind_id'
                            ";
                    //            echo $sql.';<br>';
                    $conec->ejecutar($sql);
                }
            }else{
                $conec->rollback();
                $mensaje = "Formulario: Cantidad de cuotas de formulario diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                return;
            }
        }
    }
    
    function pagar_cu_envio($data,&$conec=null){
        if($conec==null){
            $conec=new ADO();
        }
        $cob=$data->cob;
        $venta=$data->venta;
        $pago_id=$data->pago_id;
        $fecha_pago=$data->fecha_pago;
        $fecha_cre=$data->fecha_cre;
        $usuario_id=$data->usuario_id;
        $recibo=$data->recibo;
        $pag_codigo=$data->pag_codigo;
        
        $str_envio_ids = trim($cob->vcob_envio_ids);
        if ($str_envio_ids && $cob->vcob_envio > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_envio_ids) and ind_tipo='pcuota' order by ind_id asc");
//            $vpag_envio = $cob->vcob_envio;
            $envio_montos = explode(',', trim($cob->vcob_envio_montos));
            $envio_ids = explode(',', trim($cob->vcob_envio_ids));
            if (count($envio_ids) ==  count($cuotas) && count($cuotas)==  count($envio_montos)){
                for ($i = 0; $i < count($cuotas); $i++) {
                    $cu = $cuotas[$i];
//                    $envio_dif = $vpag_envio - ($cu->ind_envio * 1);
                    $envio_pagado = $envio_montos[$i]; //$cu->ind_envio;
                    $sql_ins_idp = "insert into interno_deuda_pago(
                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,
                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                    )values(
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','envio',
                                        '$fecha_pago','$envio_pagado','Activo','$fecha_cre','$usuario_id',
                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'
                                    )
                        ";
                    $conec->ejecutar($sql_ins_idp);
                    
                    $sql = "update interno_deuda set 
                                ind_envio_pagado=ind_envio_pagado+$envio_pagado
                                where ind_id = '$cu->ind_id'
                            ";
                    //            echo $sql.';<br>';
                    $conec->ejecutar($sql);
                }
            }else{
                $conec->rollback();
                $mensaje = "Formulario: Cantidad de cuotas de envios diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                return;
            }
        }
    }
        
    function pagar_cu_mora($data,&$conec=null){
        if($conec==null){
            $conec=new ADO();
        }
        $cob=$data->cob;
        $venta=$data->venta;
        $pago_id=$data->pago_id;
        $fecha_pago=$data->fecha_pago;
        $fecha_cre=$data->fecha_cre;
        $usuario_id=$data->usuario_id;
        $recibo=$data->recibo;
        $pag_codigo=$data->pag_codigo;
        
        //************ GENERAR MORA
        $str_mora_gen_ids = trim($cob->vcob_mora_gen_ids);
        if ($str_mora_gen_ids) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_mora_gen_ids) and ind_tipo='pcuota' order by ind_id asc");
//            $vpag_mora_gen = $cob->vcob_mora_gen;
            $mora_gen_montos = explode(',', trim($cob->vcob_mora_gen_montos));
            $mora_gen_ids = explode(',', trim($cob->vcob_mora_gen_ids));
            if (count($mora_gen_ids) ==  count($cuotas) && count($cuotas)==  count($mora_gen_montos)){
                for ($i = 0; $i < count($cuotas); $i++) {
                    $cu = $cuotas[$i];
//                    $mora_gen_dif = $vpag_mora_gen - ($cu->ind_mora_gen * 1);
                    $mora_generado = $mora_gen_montos[$i]; //$cu->ind_mora_gen;
                    $sql = "update interno_deuda set 
                                ind_mora=$mora_generado
                                where ind_id = '$cu->ind_id'
                            ";
                    //            echo $sql.';<br>';
                    $conec->ejecutar($sql);
                }
            }else{
                $conec->rollback();
                $mensaje = "Mora Generada: Cantidad de cuotas de mora_gen diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                return;
            }
        }
        //************ PAGAR MORA
        $str_mora_ids = trim($cob->vcob_mora_ids);
        if ($str_mora_ids && $cob->vcob_mora > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_mora_ids) and ind_tipo='pcuota' order by ind_id asc");
//            $vpag_mora = $cob->vcob_mora;
            $mora_montos = explode(',', trim($cob->vcob_mora_montos));
            $mora_ids = explode(',', trim($cob->vcob_mora_ids));
            if (count($mora_ids) ==  count($cuotas) && count($cuotas)==  count($mora_montos)){
                
                for ($i = 0; $i < count($cuotas); $i++) {
                    $cu = $cuotas[$i];
//                    $mora_dif = $vpag_mora - ($cu->ind_mora * 1);
                    $mora_pagado = $mora_montos[$i]; //$cu->ind_mora;
                    $sql_ins_idp = "insert into interno_deuda_pago(
                                        idp_ind_id,idp_ind_correlativo,idp_vpag_id,idp_vpag_recibo,idp_vpag_codigo,idp_tipo,
                                        idp_fecha,idp_monto,idp_estado,idp_fecha_cre,idp_usu_cre,
                                        idp_cob_usu,idp_cob_codigo,idp_cob_aut
                                    )values(
                                        '$cu->ind_id','$cu->ind_num_correlativo','$pago_id','$recibo','$pag_codigo','mora',
                                        '$fecha_pago','$mora_pagado','Activo','$fecha_cre','$usuario_id',
                                        '$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut'
                                    )
                        ";
                    $conec->ejecutar($sql_ins_idp);
                    
                    $sql = "update interno_deuda set 
                                ind_mora_pagado=ind_mora_pagado+$mora_pagado
                                where ind_id = '$cu->ind_id'
                            ";
                    //            echo $sql.';<br>';
                    $conec->ejecutar($sql);
                }
            }else{
                $conec->rollback();
                $mensaje = "Mora Pago: Cantidad de cuotas de mora diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                return;
            }
        }
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
        
        $sql_sel_ucu_pen=("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$par->ven_id' and ind_estado='Pendiente' and (ind_capital_pagado+ind_interes_pagado+ind_envio_pagado+ind_form_pagado)>0 and ind_tipo='pcuota' and ind_num_correlativo>0 order by ind_id asc limit 1");

        $ucu_pen=  FUNCIONES::objeto_bd_sql($sql_sel_ucu_pen);
//        FUNCIONES::print_pre($ucu_pen);
        if($ucu_pen){
            $_saldo_final=$ucu_pen->ind_saldo+$ucu_pen->ind_capital-$ucu_pen->ind_capital_pagado;
            $sql_up="update interno_deuda set ind_estado='Pagado' , ind_saldo_final='$_saldo_final' where ind_id=$ucu_pen->ind_id";
            $conec->ejecutar($sql_up);
        }
        $and_filtro='';
        if($par->ind_id){
            $and_filtro=" and ind_id>$par->ind_id";
        }
        $sql_del = "delete from interno_deuda where ind_estado='Pendiente' and ind_tabla='venta' and ind_tabla_id='".$par->ven_id."' and ind_tipo='pcuota' $and_filtro";
        $conec->ejecutar($sql_del);
        
        if($adecuacion!=null){
            $this->icremento_descuento_venta($adecuacion, $conec);
        }
        
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
                $plazo=$par->plazo;
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

            $vplazo=$this->generar_plan_pagos($plan_data,$conec);//
            $sql_up_venta="update venta set ven_plazo='$vplazo', ven_cuota='$vcuota', ven_rango='$par->rango', ven_frecuencia='$par->frecuencia',ven_sfecha_prog='$par->fecha_pri_cuota' where ven_id=$par->ven_id";
            $conec->ejecutar($sql_up_venta);
        }else{
            $sql_up_venta="update venta set ven_estado='Pagado', ven_sfecha_prog='0000-00-00' where ven_id=$par->ven_id";
            $conec->ejecutar($sql_up_venta);
        }
        
    }
    
    function calcular_cobro($params) {
        $conec=new ADO();
        $par=(object)$params;
        $venta=$par->venta;
        $cambio_usd=1;
        if($venta->ven_moneda=='1'){
            $cambio_usd=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$par->fecha_pago' and tca_mon_id=2");
        }
        

        $fecha_pago = FUNCIONES::sumar_dias(15, $par->fecha_pago);
        /// SUMAR CAPITAL
        $fecha_valor = $par->fecha_valor;
        $sql_cuotas_capital="select * from interno_deuda 
                            where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                            ind_fecha_programada<='$fecha_pago' and ind_estado='Pendiente' and
                            ind_capital_pagado < ind_capital and ind_tipo='pcuota' order by ind_id asc
                            ";  
        
//        echo $sql_cuotas_capital.';<br>';
        
        $cuotas_capital=  FUNCIONES::lista_bd_sql($sql_cuotas_capital);
        
        $capital=0;
        $capital_ids=array();
        $capital_montos=array();
        foreach ($cuotas_capital as $cu) {
            $mcapital=$cu->ind_capital-$cu->ind_capital_pagado;
            $capital+=$mcapital;
            $capital_ids[]=$cu->ind_id;
            $capital_montos[]=$mcapital;
        }

        $form=  FUNCIONES::ad_parametro('par_valor_form')*$cambio_usd;

        $form_ids=array();
        $form_montos=array();
        if($form>0){
            if(count($cuotas_capital)<=0){
                $sql_cuota="select * from interno_deuda 
                    where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                    ind_estado='Pendiente' and ind_tipo='pcuota' order by ind_id asc limit 0,1
                    ";
                $scuota=  FUNCIONES::objeto_bd_sql($sql_cuota);
                $form_ids=array($scuota->ind_id);
            }else{
                $form_ids=array($cuotas_capital[0]->ind_id);
            }
            $form_montos=array($form);
        }
        /// SUMAR MORA CONCRETADAS PENDIENTES
        $mora=0;
        /// SUMAR MORAS GENERADAS
        
//        $saldo=0;
        // SUMAR INTERES
        $pagado=  $this->total_pagado($venta->ven_id);
        
//        $saldo=$venta->ven_monto_efectivo-$pagado->capital;
        $saldo=$venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;
        
        $saldo_inicial=$saldo;
        
        $sql_fv="select max(vpag_fecha_valor) as campo from venta_pago where vpag_ven_id=$venta->ven_id and vpag_estado='Activo'";
        $u_fecha_valor=  FUNCIONES::atributo_bd_sql($sql_fv);
        if(!$u_fecha_valor){
            $u_fecha_valor=$venta->ven_fecha;
        }

        $dif_dias=  FUNCIONES::diferencia_dias($u_fecha_valor, $fecha_valor);

        $interes_dia = ($venta->ven_val_interes / 360) / 100;

        $interes=  round($dif_dias*$interes_dia*$saldo,2);
        $interes_ids=array();
        $interes_montos=array();
        if($interes>0){
            if(count($cuotas_capital)<=0){
                $sql_cuota="select * from interno_deuda 
                    where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                    ind_estado='Pendiente' and ind_tipo='pcuota' order by ind_id asc limit 0,1
                    ";
                $scuota=  FUNCIONES::objeto_bd_sql($sql_cuota);
                $interes_ids=array($scuota->ind_id);
            }else{
                $interes_ids=array($cuotas_capital[0]->ind_id);
            }
            $interes_montos=array($interes);
        }
//        echo "$dif_dias*$interes_dia*$saldo=$interes <br>";
        $nsaldo=$saldo-$capital;
/*************************************************************************/

//        $monto=  round($interes+$capital, 2);
        
        $monto=  round($interes+$capital+$mora+$form, 2);
        if($monto>0){
//            $txt_interes_ids=implode(',', $interes_ids);
//            $txt_capital_ids=implode(',', $capital_ids);
//            $txt_mora_ids=implode(',', $mora_ids);
//            $txt_mora_con_ids=implode(',', $mora_con_ids);
//            $txt_mora_gen_ids=implode(',', $mora_gen_ids);
//            $txt_form_ids=implode(',', $form_ids);
//            $txt_interes_montos=implode(',', $interes_montos);
//            $txt_capital_montos=implode(',', $capital_montos);
//            $txt_mora_montos=implode(',', $mora_montos);
//            $txt_mora_con_montos=implode(',', $mora_con_montos);
//            $txt_mora_gen_montos=implode(',', $mora_gen_montos);
//            $txt_mora_gen_dias=implode(',', $mora_gen_dias);
//            $txt_form_montos=implode(',', $form_montos);
//            
//            $fecha_cre=date('Y-m-d H:i:s');
//            $usuario_id=$this->usu->get_id();
//            $sql_cobro = "insert into venta_cobro(
//                                vcob_ven_id,vcob_codigo,vcob_fecha_pago,vcob_fecha_valor,
//                                vcob_int_id,vcob_moneda,vcob_saldo_inicial,vcob_dias_interes,
//                                vcob_interes,vcob_capital,vcob_mora, vcob_form, vcob_monto,vcob_saldo_final,
//                                vcob_interes_ids,vcob_capital_ids,vcob_form_ids,vcob_mora_ids,vcob_mora_con_ids,vcob_mora_gen_ids,
//                                vcob_interes_montos,vcob_capital_montos,vcob_form_montos,vcob_mora_montos,vcob_mora_con_montos,vcob_mora_gen_montos,vcob_mora_gen_dias,
//                                vcob_fecha_cre,vcob_usu_cre,vcob_aut
//                            )values(
//                                '$venta->ven_id','$codigo','$par->fecha_pago','$fecha_valor',
//                                '$venta->ven_int_id','$venta->ven_moneda','$saldo_inicial','$dif_dias',
//                                '$interes','$capital','$mora','$form','$monto','$nsaldo',
//                                '$txt_interes_ids','$txt_capital_ids','$txt_form_ids','$txt_mora_ids','$txt_mora_con_ids','$txt_mora_gen_ids',
//                                '$txt_interes_montos','$txt_capital_montos','$txt_form_montos','$txt_mora_montos','$txt_mora_con_montos','$txt_mora_gen_montos','$txt_mora_gen_dias',
//                                '$fecha_cre','$usuario_id','0'
//                            )";
            //echo $sql_cobro.';<br>';
            $resp=array(
                'interes'=>$interes,
                'capital'=>$capital,
                'form'=>$form,
                'monto'=>$monto,
            );
            return $resp;
//            $conec->ejecutar($sql_cobro);
//            return true;
        }else{
            return null;
        }
    }

    
    function generar_cobro($params,&$conec=null) {
//        echo "<pre>";
//        print_r($_SESSION);
//        echo "</pre>";
        if($conec==null){
            $conec=new ADO();
        }
        $par=(object)$params;
        $venta=$par->venta;
        $cambio_usd=1;
        if($venta->ven_moneda=='1'){
            $cambio_usd=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$par->fecha_pago' and tca_mon_id=2");
        }
        
        $codigo = FUNCIONES::fecha_codigo();
        $fecha_pago = FUNCIONES::sumar_dias(15, $par->fecha_pago);
        /// SUMAR CAPITAL
        $fecha_valor = $par->fecha_valor;
        $sql_cuotas_capital="select * from interno_deuda 
                            where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                            ind_fecha_programada<='$fecha_pago' and ind_estado='Pendiente' and
                            ind_capital_pagado < ind_capital and ind_tipo='pcuota' order by ind_id asc
                            ";  
        
//        echo $sql_cuotas_capital.';<br>';
        
        $cuotas_capital=  FUNCIONES::lista_bd_sql($sql_cuotas_capital);
        
        $capital=0;
        $capital_ids=array();
        $capital_montos=array();
        foreach ($cuotas_capital as $cu) {
            $mcapital=$cu->ind_capital-$cu->ind_capital_pagado;
            $capital+=$mcapital;
            $capital_ids[]=$cu->ind_id;
            $capital_montos[]=$mcapital;
        }

//        $form=  FUNCIONES::ad_parametro('par_valor_form')*$cambio_usd;
//        $form=  FUNCIONES::atributo_bd_sql("select urb_val_form as campo from urbanizacion where urb_id=$venta->ven_urb_id")*$cambio_usd;
        $form= $venta->ven_form;

        $form_ids=array();
        $form_montos=array();
        if($form>0){
            if(count($cuotas_capital)<=0){
                $sql_cuota="select * from interno_deuda 
                    where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                    ind_estado='Pendiente' and ind_tipo='pcuota' order by ind_id asc limit 0,1
                    ";
                $scuota=  FUNCIONES::objeto_bd_sql($sql_cuota);
                $form_ids=array($scuota->ind_id);
            }else{
                $form_ids=array($cuotas_capital[0]->ind_id);
            }
            $form_montos=array($form);
        }
        /// SUMAR MORA CONCRETADAS PENDIENTES
        $sql_cuotas_mora="select * from interno_deuda 
                            where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                            ind_fecha_programada<='$fecha_pago' and 
                            ind_capital_pagado > 0 and
                            ind_capital_pagado<=ind_capital and 
                            ind_mora_pagado < ind_mora and ind_tipo='pcuota' order by ind_id asc
                            ";  
        $cuotas_mora=  array();//FUNCIONES::lista_bd_sql($sql_cuotas_mora);
        
        $mora=0;
        $mora_ids=array();
        $mora_montos=array();
        $mora_con_ids=array();
        $mora_con_montos=array();
        foreach ($cuotas_mora as $cu) {
            $mmora=$cu->ind_mora-$cu->ind_mora_pagado;
            $mora+=$mmora;
            $mora_ids[]=$cu->ind_id;
            $mora_montos[]=$mmora;
            $mora_con_ids[]=$cu->ind_id;
            $mora_con_montos[]=$mmora;
            
        }
        /// SUMAR MORAS GENERADAS
        $sql_cuotas_mora="select * from interno_deuda 
                            where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                            ind_fecha_programada<='$fecha_pago' and 
                            ind_capital_pagado=0 and
                            ind_mora=0 and 
                            ind_tipo='pcuota' order by ind_id asc
                            ";  
        $cuotas_mora=  FUNCIONES::lista_bd_sql($sql_cuotas_mora);
        
        
        $mora_gen_ids=array();
        $mora_gen_montos=array();
        $mora_gen_dias=array();
        $monto_dia_mora=  FUNCIONES::ad_parametro('par_val_dias_mora')*$cambio_usd;
        
//        =  FUNCIONES::ad_parametro('par_val_dias_mora');
        foreach ($cuotas_mora as $cu) {
            $num_dias_mora=FUNCIONES::diferencia_dias($cu->ind_fecha_programada, $par->fecha_pago);
            if($num_dias_mora>0){
                $mmora=$num_dias_mora*$monto_dia_mora;
                $mora+=$mmora;
                $mora_ids[]=$cu->ind_id;
                $mora_montos[]=$mmora;
                $mora_gen_ids[]=$cu->ind_id;
                $mora_gen_montos[]=$mmora;
                $mora_gen_dias[]=$num_dias_mora;
            }
        }

//        $saldo=0;
        // SUMAR INTERES
        $pagado=  $this->total_pagado($venta->ven_id);
        
//        $saldo=$venta->ven_monto_efectivo-$pagado->capital;
        $saldo=$venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;
        
        $saldo_inicial=$saldo;
        
        $sql_fv="select vpag_fecha_valor as campo from venta_pago where vpag_ven_id=$venta->ven_id and vpag_estado='Activo' order by vpag_id desc";
        $u_fecha_valor=  FUNCIONES::atributo_bd_sql($sql_fv);
        if(!$u_fecha_valor){
            $u_fecha_valor=$venta->ven_fecha;
        }

        $dif_dias=  FUNCIONES::diferencia_dias($u_fecha_valor, $fecha_valor);

        $interes_dia = ($venta->ven_val_interes / 360) / 100;

        $interes=  round($dif_dias*$interes_dia*$saldo,2);
        $interes_ids=array();
        $interes_montos=array();
        if($interes>0){
            if(count($cuotas_capital)<=0){
                $sql_cuota="select * from interno_deuda 
                    where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                    ind_estado='Pendiente' and ind_tipo='pcuota' order by ind_id asc limit 0,1
                    ";
                $scuota=  FUNCIONES::objeto_bd_sql($sql_cuota);
                $interes_ids=array($scuota->ind_id);
            }else{
                $interes_ids=array($cuotas_capital[0]->ind_id);
            }
            $interes_montos=array($interes);
        }
//        echo "$dif_dias*$interes_dia*$saldo=$interes <br>";
/*************************************************************************/        
        $_monto=$interes+$capital+$mora+$form;
//        $suc_envios=array(5);
        $porc_envio = FUNCIONES::porcentaje_envio($_SESSION[suc_id]);
//        $suc_envios = json_decode(FUNCIONES::ad_parametro('par_suc_envio'));
//        $usu_suc_id=$_SESSION[suc_id];
//        
//        for ($i = 0; $i < count($suc_envios); $i++) {
//            $esuc=$suc_envios[$i];
//            if($esuc->suc_id==$usu_suc_id){
//                $porc_envio = $esuc->suc_porc;
//            }
//        }
        
        $envio=  $_monto*($porc_envio/100);
        
        
        $envio_ids=array();
        $envio_montos=array();
        if($envio>0){
            if(count($cuotas_capital)<=0){
                $sql_cuota="select * from interno_deuda 
                    where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                    ind_estado='Pendiente' and ind_tipo='pcuota' order by ind_id asc limit 0,1
                    ";
                $scuota=  FUNCIONES::objeto_bd_sql($sql_cuota);
                $envio_ids=array($scuota->ind_id);
            }else{
                $envio_ids=array($cuotas_capital[0]->ind_id);
            }
            $envio_montos=array($envio);
        }

/*************************************************************************/        
        $nsaldo=$saldo-$capital;
        $monto =  round($interes+$capital+$mora+$form+$envio, 2);
/*************************************************************************/

//        $monto=  round($interes+$capital, 2);
        
//        $monto =  round($interes+$capital+$mora+$form, 2);
//        if($monto>0){
        if($interes+$capital>0){
            $txt_interes_ids=implode(',', $interes_ids);
            $txt_capital_ids=implode(',', $capital_ids);
            $txt_mora_ids=implode(',', $mora_ids);
            $txt_mora_con_ids=implode(',', $mora_con_ids);
            $txt_mora_gen_ids=implode(',', $mora_gen_ids);
            $txt_form_ids=implode(',', $form_ids);
            $txt_envio_ids=implode(',', $envio_ids);
            
            $txt_interes_montos=implode(',', $interes_montos);
            $txt_capital_montos=implode(',', $capital_montos);
            $txt_mora_montos=implode(',', $mora_montos);
            $txt_mora_con_montos=implode(',', $mora_con_montos);
            $txt_mora_gen_montos=implode(',', $mora_gen_montos);
            $txt_mora_gen_dias=implode(',', $mora_gen_dias);
            $txt_form_montos=implode(',', $form_montos);
            $txt_envio_montos=implode(',', $envio_montos);
            
            $fecha_cre=date('Y-m-d H:i:s');
            $usuario_id=$this->usu->get_id();
            $sql_cobro = "insert into venta_cobro(
                                vcob_ven_id,vcob_codigo,vcob_fecha_pago,vcob_fecha_valor,
                                vcob_int_id,vcob_moneda,vcob_saldo_inicial,vcob_dias_interes,
                                vcob_interes,vcob_capital,vcob_mora, vcob_form, vcob_envio, vcob_monto,vcob_saldo_final,
                                vcob_interes_ids,vcob_capital_ids,vcob_form_ids,vcob_envio_ids,vcob_mora_ids,vcob_mora_con_ids,vcob_mora_gen_ids,
                                vcob_interes_montos,vcob_capital_montos,vcob_form_montos,vcob_envio_montos,vcob_mora_montos,vcob_mora_con_montos,vcob_mora_gen_montos,vcob_mora_gen_dias,
                                vcob_fecha_cre,vcob_usu_cre,vcob_aut
                            )values(
                                '$venta->ven_id','$codigo','$par->fecha_pago','$fecha_valor',
                                '$venta->ven_int_id','$venta->ven_moneda','$saldo_inicial','$dif_dias',
                                '$interes','$capital','$mora','$form',$envio,'$monto','$nsaldo',
                                '$txt_interes_ids','$txt_capital_ids','$txt_form_ids','$txt_envio_ids','$txt_mora_ids','$txt_mora_con_ids','$txt_mora_gen_ids',
                                '$txt_interes_montos','$txt_capital_montos','$txt_form_montos','$txt_envio_montos','$txt_mora_montos','$txt_mora_con_montos','$txt_mora_gen_montos','$txt_mora_gen_dias',
                                '$fecha_cre','$usuario_id','0'
                            )";
            //echo $sql_cobro.';<br>';
            $conec->ejecutar($sql_cobro);
            return true;
        }else{
            return false;
        }
    }
    
    function frm_fecha_cobro($venta) {
        $this->formulario->dibujar_tarea();
        ?>
        <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
        <script src="js/util.js" type="text/javascript"></script>
        <form id="frm_sentencia" name="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=PAGOS&id=<?php echo $venta->ven_id?>" >
            <div id="FormSent" style="width:80%" >
                <div class="Subtitulo">INGRESE LA FECHA DE PAGO Y FECHA VALOR</div>
                <div id="ContenedorSeleccion">
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Fecha Pago</div>
                        <div id="CajaInput">
                            <input id="fecha_pago" name="fecha_pago" class="caja_texto" type="text" value="<?php echo date('d/m/Y');?>" size="20" autocomplete="off">
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Fecha Valor</div>
                        <div id="CajaInput">
                            <input id="fecha_valor" name="fecha_valor" class="caja_texto" type="text" value="<?php echo date('d/m/Y');?>" size="20" autocomplete="off" >
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input class="boton" type="submit" id="btn_enviar" value="Pagar" name="">
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
            $("#fecha_pago").mask("99/99/9999");
            $("#fecha_valor").mask("99/99/9999");
            $('#frm_sentencia').submit(function (){
                var fecha_p=$('#fecha_pago').val();
                
                var fecha_v=$('#fecha_valor').val();
//                return false;
                if(fecha_p!=='' && fecha_v!==''){
//                    return false;
                    mostrar_ajax_load();
                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha_p}, function(respuesta) {
                        ocultar_ajax_load();
//                        return false;
                        var dato = JSON.parse(respuesta);
                        if (dato.response === "ok") {
                            document.frm_sentencia.submit();
                        } else if (dato.response === "error") {
                            $.prompt(dato.mensaje);
                            return false;
                        }
                    });
                }else{
                    $.prompt('Ingrese la Fecha de Pago y la Fecha Valor');
                }
                return false;
            });
        </script>
        <?php
    }
    
    function frm_pagar_cuota($venta){
        $this->formulario->dibujar_tarea();
        $cobro=  FUNCIONES::objeto_bd_sql("select *  from venta_cobro where vcob_ven_id='$venta->ven_id'");
        if(!$cobro){
            $url=  "$this->link?mod=$this->modulo&tarea=PAGOS&id=$venta->ven_id";
            $this->formulario->ventana_volver("No existe nigun cobro a ejecutarse por esta venta","$url", 'Volver','Error');
            return false;
        }
        ?>
        <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
        <script src="js/util.js" type="text/javascript"></script>
        <form id="frm_sentencia" name="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=PAGOS&id=<?php echo $venta->ven_id?>&acc=pagar" >
            <div id="FormSent" style="width:80%">
                <div class="Subtitulo">PAGOS</div>
                <div id="ContenedorSeleccion">
                    <input type="hidden" name="cob_codigo" value="<?php echo $cobro->vcob_codigo?>">
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Nro. Venta</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_id;?></div>
                        </div>
                        <div class="Etiqueta" style="width:100px">Cliente</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo FUNCIONES::interno_nombre($venta->ven_int_id);?></div>
                        </div>
                    </div>
                    
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Fecha Pago</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($cobro->vcob_fecha_pago);?></div>
                        </div>
                        <div class="Etiqueta" style="width:100px">Fecha Valor</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($cobro->vcob_fecha_valor);?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Concepto</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_concepto;?></div>
                        </div>
                    </div>

                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Interes Anual</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_val_interes*1;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Saldo Actual</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $cobro->vcob_saldo_inicial;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Dias Interes</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $cobro->vcob_dias_interes;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Interes</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $cobro->vcob_interes;?></div>
                        </div>
                        <div class="Etiqueta" style="width: 60px">Descuento</div>
                        <div id="CajaInput">
                            <input type="text" name="val_monto" id="val_monto" value="" size="5" autocomplete="off">
                        </div>
                        <?php 
                        $cambio_usd=1;
                        $txt_mon='$us';
                        if($venta->ven_moneda=='1'){
                            $txt_mon='Bs';
                            $cambio_usd=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$cobro->vcob_fecha_pago' and tca_mon_id=2");
                        }
                        $descuento=  FUNCIONES::atributo_bd_sql("select sum(val_monto) as campo from beneficiario_vale where val_int_id=$venta->ven_int_id and val_estado='Activo'")*1;
                        ?>
                        <input type="hidden" id="saldo_descuento" value="<?php echo $descuento;?>">
                        <span style="float: left; margin: 5px 3px;">(<?php echo "$descuento $txt_mon"?>)</span>
                        <?php // if($_SESSION[usu_loged]=='admin'){?>
                        <?php if($_SESSION[id]=='gsoto' || $_SESSION[id]=='gvargas' || $_SESSION[id]=='ngil'){?>
                            <div class="Etiqueta" style="width: 60px">Recibo</div>
                            <div id="CajaInput">
                                <input type="text" name="vpag_nro_recibo" id="vpag_nro_recibo" value="" size="15" autocomplete="off">
                            </div>
                        <?php } ?>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Capital</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $cobro->vcob_capital;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Formulario</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $cobro->vcob_form;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv" hidden="">
                        <div class="Etiqueta">Moras</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $cobro->vcob_mora;?></div>
                        </div>
                    </div>
                    <?php if($cobro->vcob_envio>0){ ?>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Cap, Int, Form</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $cobro->vcob_capital+$cobro->vcob_interes+$cobro->vcob_form;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Envio</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $cobro->vcob_envio;?></div>
                        </div>
                    </div>
                    <?php } ?>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta"><b>Monto</b></div>
                        <div id="CajaInput">
                            <div class="read-input" id="txt_pag_monto"><?php echo $cobro->vcob_monto;?></div>
                        </div>
                    </div>
                    <input type="hidden" id="pag_monto_total" value="<?php echo $cobro->vcob_monto;?>">
                    
                    <input type="hidden" id="pag_fecha" value="<?php echo FUNCIONES::get_fecha_latina($cobro->vcob_fecha_pago);?>">
                    <input type="hidden" id="pag_interes" value="<?php echo $cobro->vcob_interes;?>">
                    <input type="hidden" id="pag_monto" value="<?php echo $cobro->vcob_monto;?>">
                    <input type="hidden" id="moneda" value="<?php echo $cobro->vcob_moneda;?>">
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><b>Pagos</b></div>                                
                        <?php FORMULARIO::frm_pago(array('cmp_fecha'=>'pag_fecha','cmp_monto'=>'pag_monto','cmp_moneda'=>'moneda'));?>
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input class="boton" type="button" onclick="enviar_frm_pagos();" value="Guardar" name="">
                                <input class="boton" type="button" onclick="location.href='gestor.php?mod=venta&tarea=PAGOS&id=<?php echo $venta->ven_id?>';" value="Volver" name="">
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
            mask_decimal('#val_monto');
            $('#val_monto').keyup(function(){
                var monto_total=$('#pag_monto_total').val()*1;
                var descuento=$(this).val()*1;
                var pag_monto=monto_total-descuento;
                $('#pag_monto').val(pag_monto.toFixed(2));
                $('#txt_pag_monto').text(pag_monto.toFixed(2));
                $('#pag_monto').trigger('focusout');
                
            });
            $('#frm_sentencia').submit(function (){
                return false;
            });
            function enviar_frm_pagos(){
                var saldo_descuento=$('#saldo_descuento').val()*1;
                var interes=$('#pag_interes').val()*1;
                var descuento=$('#val_monto').val()*1;
                if(descuento>0){
                    if(descuento>interes || descuento>saldo_descuento){
                        $.prompt('El descuento de debe ser menor al interes y al saldo de descuento');
                        return false;
                    }
                }
                var fecha=$('#pag_fecha').val();
                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                    var dato = JSON.parse(respuesta);
                    if (dato.response === "ok") {
                        if(!validar_fpag_montos(dato.cambios)){
                            $.prompt('El monto a Pagar no concuerda con los pagos realizados');
                            return false;
                        }
                        document.frm_sentencia.submit();
                        console.log('ok');
                    } else if (dato.response === "error") {
                        $.prompt(dato.mensaje);
                        return false;
                    }
                });
            }
        </script>
        <?php
    }
    
    
    /*PAGO DE CUOTAS*/
    
    function listado_extractos($venta){
            $grupo_id = $this->obtener_grupo_id($this->usu->get_id());
            $this->barra_opciones($venta, 'EXTRACTOS',true);
            $has_permiso_ver_cmp= in_array($_SESSION[usu_gru_id], array('Administradores','CONTADOR'));
            ?>
            <style>
                .reg_estado{color: #fff; padding: 1px 3px;}
                .est_green{background-color: #019721}
                .est_gold{background-color: #ff9601}
                .est_red{background-color: #ff0000;}
                .det_pagos, .det_venta{cursor: pointer;}
            </style>
            <script src="js/util.js"></script>
            <div id="contenido_reporte" style="clear:both;">
            <?php
            $this->cabecera_venta($venta);
            ?>
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=$this->modulo&tarea=$_GET[tarea]&id=$_GET[id]"; ?>&acc=anular" method="POST" enctype="multipart/form-data">  
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
                <input type="hidden" id="pag_id" name="pag_id" value="">
                <input type="hidden" id="observacion_anu" name="observacion_anu" value="">
            </form>
            <script>		
                function anular_pago(id){
//                    var txt = 'Esta seguro de anular el Pago?';
                    var txt = 'Esta seguro de anular el Pago de Mensualidad?' +
                                '<br />Observacion:<br /><textarea id="observacion_anu" name="observacion_anu" rows="2" cols="40"></textarea><br />' 
                                ;
                    $.prompt(txt,{ 
                        buttons:{Aceptar:true, Cancelar:false},
                        callback: function(v,m,f){						
                            if(v){
                                $('#pag_id').val(id);
                                $('#observacion_anu').val(f.observacion_anu);
                                document.frm_sentencia.submit();
//                                location.href='gestor.php?mod=venta&tarea=EXTRACTOS&id=<?php // echo $_GET[id];?>&acc=anular&pag_id='+id;
                            }
                        }
                    });
                }
                
                popup=null;
                function ver_comprobante(id){
                    if(popup!==null){
                        popup.close();
                    }
                    var ruta='gestor.php?mod=con_comprobante&info=ok&tarea=VER&id=';
                    window.open(ruta+id,'Comprobante','width=900, height=500, scrollbars=yes');
                    popup = window.open(ruta+id,'Comprobante','width=900, height=500, scrollbars=yes');
                    popup.document.close();
                }
                $(document).ready(function(){
                    $('.btn_ver_origen').click(function() {
                            var tabla=$(this).attr('data-tabla');
                            var tabla_id=$(this).attr('data-id');
                            if(tabla==='Venta'){
                                window.open('gestor.php?mod=venta&tarea=SEGUIMIENTO&id='+tabla_id, 'reportes', 'left=100,width=900,height=500,top=0,scrollbars=yes');
                            }

                        });
                });
                    
            </script>
            <?php if($venta->ven_res_anticipo>0){ ?>
            <br><br><center><h2>ANTICIPOS</h2>
            <table class="tablaReporte" cellpadding="0" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th >#</th>
                        <th >Ant. Id</th>
                        <th >Fecha</th>
                        <th >Hora</th>
                        <th >Monto</th>
                        <th >Moneda</th>
                        <th >Usu. Pago</th>
                        <th >Transaccion Origen</th>
                        <th >Cuenta</th>
                        <th class="tOpciones" width="100px">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM reserva_pago
                            inner join reserva_terreno on (res_id=respag_res_id)                
                            WHERE 
                            respag_res_id='$venta->ven_res_id' and respag_estado='Pagado' 
                            order by 
                            respag_fecha asc,respag_hora asc";
                    $lista_anticipos=  FUNCIONES::objetos_bd_sql($sql);
                    $num_anticipos = $lista_anticipos->get_num_registros();
                    for ($i = 0; $i < $num_anticipos; $i++) {
                        $anticipo=$lista_anticipos->get_objeto();
                        ?>
                    <tr>
                        <td><?php $nro=$i+1; echo "<em>$nro</em>"; ?></td>
                        <td><?php echo $anticipo->respag_id;?></td>
                        <td><?php echo FUNCIONES::get_fecha_latina($anticipo->respag_fecha);?></td>
                        <td><?php echo $anticipo->respag_hora;?></td>
                        <td><?php echo $anticipo->respag_monto;?></td>
                        <td><?php echo $anticipo->respag_moneda=='2'?'Dolares':'Bolivianos';?></td>
                        <td><?php echo $anticipo->respag_usu_id;?></td>
                        <td><?php echo $anticipo->respag_tabla?"<a class='btn_ver_origen' href='javascript:void(0);' data-tabla='$anticipo->respag_tabla' data-id='$anticipo->respag_tabla_id'>$anticipo->respag_tabla: $anticipo->respag_tabla_id</a>":"";?></td>
                        <?php 
                            $lista_pagos=  FUNCIONES::lista_bd_sql("select cue_descripcion,fpag_monto,fpag_mon_id from con_pago 
                                                                    inner join con_cuenta on (cue_id=fpag_cue_id)
                                                                    where fpag_tabla_id=$anticipo->respag_id and fpag_tabla='reserva_pago' and fpag_estado='activo' ");
                            $str_pagos='';
                            for($k=0;$k<count($lista_pagos);$k++){
                                $fp=$lista_pagos[$k];
                                if($k>0){
                                    $str_pagos.=',';
                                }
                                $monto=$fp->fpag_monto*1;
                                $str_pagos.="$fp->cue_descripcion <b>($monto)</b>";
                            }
                        ?>
                        <td><?php echo $str_pagos; ?></td>
                        <td>
                            <?php if ($has_permiso_ver_cmp) { ?>
                            <?php 
                                $sql_sel="select * from con_comprobante where cmp_tabla='reserva_pago' and cmp_tabla_id='$anticipo->respag_id' and cmp_eliminado='No'";
                                $cmp=  FUNCIONES::objeto_bd_sql($sql_sel);
                                if($cmp){?>
                                    <a class="link_cmp"  href="javascript:ver_comprobante('<?php echo $cmp->cmp_id; ?>');">
                                        <img width="20" src="images/ver_nota.png" >
                                    </a>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        </tr>
                    <?php 
                        $lista_anticipos->siguiente();
                    } 
                    ?>
                </tbody>
            </table>
            <?php } ?>
            <br><br><center><h2>EXTRACTO DE PAGOS</h2>
            <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th >Pag. Id</th>
                        <th >Fecha</th>
                        <th >F. Valor</th>
                        <th >Recibo</th>
                        <th >Dias Int.</th>
                        <th >Interes</th>
                        <th >Capital</th>
                        <th >Mora</th>
                        <th >Form.</th>
                        <th >Envio.</th>
                        <th >Monto</th>
                        <th >Vale</th>
                        <th >Desc.</th>
                        <th >Inc.</th>
                        <th >Saldo</th>
                        <th >Cuotas</th>
                        <th >Usu. Cobro</th>
                        <th >Aut.</th>
                        <th >Usu. Aut.</th>
                        <th >Cuentas</th>
                        <th class="tOpciones" width="70px">OP</th>
                        <th class="tOpciones" width="10px">&nbsp;</th>
                    </tr>	
                    </thead>
                    <tbody>
                        <?php
                        $arr_cupones = FUNCIONES::arr_cupones($_GET[id]);
                        $arr_debitos = FUNCIONES::arr_debitos($_GET[id]);
                        
                        $sql="select *
                                from 
                                    venta_pago                                     
                                where
                                vpag_ven_id=$_GET[id] and vpag_estado='Activo'                                 
                                order by vpag_id asc";
                        $pagos=  FUNCIONES::objetos_bd_sql($sql);
                        $total= new stdClass();
                        ?>
                        <?php for ($i = 0; $i < $pagos->get_num_registros(); $i++) {?>
                            <?php $pag=$pagos->get_objeto();?>
                            <tr>
                                <td><?php echo $pag->vpag_id;?></td>
                                <td><?php echo FUNCIONES::get_fecha_latina($pag->vpag_fecha_pago);?></td>
                                <td><?php echo FUNCIONES::get_fecha_latina($pag->vpag_fecha_valor);?></td>
                                <td><?php echo $pag->vpag_recibo;?></td>
                                <td><?php echo $pag->vpag_dias_interes;?></td>
                                <td><?php echo number_format($pag->vpag_interes, 2);?></td>
                                <td><?php echo number_format($pag->vpag_capital, 2);?></td>
                                <td><?php echo number_format($pag->vpag_mora, 2);?></td>
                                <td><?php echo number_format($pag->vpag_form, 2);?></td>
                                <td><?php echo number_format($pag->vpag_envio, 2);?></td>
                                <td><?php echo number_format($pag->vpag_monto, 2);?></td>
                                <td><?php echo number_format($pag->vpag_val_monto, 2);?></td>
                                <td><?php echo number_format($pag->vpag_capital_desc, 2);?></td>
                                <td><?php echo number_format($pag->vpag_capital_inc, 2);?></td>
                                <td><?php echo number_format($pag->vpag_saldo_final, 2);?></td>
                                <td><?php echo $this->str_numeros_cuotas($pag->vpag_capital_ids);?></td>
                                <td><?php echo $pag->vpag_usu_cre;?></td>
                                <td><?php echo $pag->vpag_cob_aut?'Si':'No';?></td>
                                <td><?php echo $pag->vpag_cob_aut?$pag->vpag_cob_usu:'&nbsp;';?></td>
                                <?php                                 
                                $pagado_con_debito = ($arr_debitos[$pag->vpag_id]) ? true : false;
                                if ($pagado_con_debito) {
                                    $str_pagos = $arr_debitos[$pag->vpag_id];
                                } else {
                                                                    
                                    $lista_pagos=  FUNCIONES::lista_bd_sql("select cue_descripcion,fpag_monto,fpag_mon_id from con_pago 
                                                                            inner join con_cuenta on (cue_id=fpag_cue_id)
                                                                            where fpag_tabla_id=$pag->vpag_id and fpag_tabla='venta_pago' and fpag_estado='activo' ");
                                    $str_pagos='';
                                    for($k=0;$k<count($lista_pagos);$k++){
                                        $fp=$lista_pagos[$k];
                                        if($k>0){
                                            $str_pagos.=',';
                                        }
                                        $monto=$fp->fpag_monto*1;
                                        $str_pagos.="$fp->cue_descripcion <b>($monto)</b>";
                                    }
                                }
                                ?>
                                <td><?php echo $str_pagos; ?></td>
                                <?php
                                $total->interes+=$pag->vpag_interes;
                                $total->capital+=$pag->vpag_capital;
                                $total->mora+=$pag->vpag_mora;
                                $total->form+=$pag->vpag_form;
                                $total->envio+=$pag->vpag_envio;
                                $total->monto+=$pag->vpag_monto;
                                $total->vale_monto+=$pag->vpag_val_monto;
                                $total->cap_desc+=$pag->vpag_capital_desc;
                                $total->cap_inc+=$pag->vpag_capital_inc;
                                ?>
                                <td>
                                    <a class="linkOpciones" title="VER" href="gestor.php?mod=venta&tarea=EXTRACTOS&id=<?php echo $pag->vpag_ven_id;?>&acc=verpago&pag_id=<?php echo $pag->vpag_id;?>">
                                        <img width="16" border="0" alt="VER" src="images/b_search.png">
                                    </a> 
                                    <a class="linkOpciones" title="VER" href="gestor.php?mod=venta&tarea=EXTRACTOS&id=<?php echo $pag->vpag_ven_id;?>&acc=imp_ticket&pag_id=<?php echo $pag->vpag_id;?>">
                                        <img width="16" border="0" alt="VER" src="images/print_ticket.png">
                                    </a>
                                    <?php 
//                                    echo "<p>vpag_ven_id:". $pag->vpag_venta_id . " - grupo:" . $this->obtener_grupo_id($this->usu->get_id()) . " - i:$i</p>";
                                    if(($pag->vpag_venta_id == 0 || $pag->vpag_venta_id == NULL) && 
                                        $grupo_id =="Administradores" && 
                                        $i == $pagos->get_num_registros()-1 && 
                                        $pag->vpag_importado=='0' && 
                                        !$pagado_con_debito){ 
                                        ?>
                                        <a class="linkOpciones" title="ANULAR" onclick="anular_pago('<?php echo $pag->vpag_id?>');" href="javascript:void(0);">
                                            <img width="16" border="0" alt="ANULAR" src="images/anular.png">
                                        </a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($has_permiso_ver_cmp) {                                         
                                        $sql_sel="select * from con_comprobante where cmp_tabla='venta_pago' and cmp_tabla_id='$pag->vpag_id' and cmp_eliminado='No'";
                                        $cmp=  FUNCIONES::objeto_bd_sql($sql_sel);
                                        if($cmp){?>
                                        <a class="linkOpciones"  href="javascript:ver_comprobante('<?php echo $cmp->cmp_id; ?>');">
                                            <img width="20" src="images/ver_nota.png" >
                                        </a>
                                        <?php                                         
                                        } 
                                        ?>
                                    <?php                                     
                                    } 
                                    if($pag->vpag_venta_id>0){
                                    ?>
                                    <img class="det_venta" src="images/b_browse.png" data-id="<?php echo $pag->vpag_venta_id;?>">
                                    <?php                                     
                                    }else{
                                    ?>
                                    <img class="det_pagos" src="images/b_browse.png" data-id="<?php echo $pag->vpag_id;?>">
                                    <?php                                     
                                    }

                                    if ($arr_cupones[$pag->vpag_id]) {
                                        ?>
                                    <a title='CUPON' href='#' data-ven_id="<?php echo $venta->ven_id;?>" data-vpag_id="<?php echo $pag->vpag_id;?>" class='cupon'><img width='18' src='images/cupon_48.png'></a>
                                        <?php
                                    }
                                    ?>
                                                                                                            
                                </td>
                            </tr>
                            <?php $pagos->siguiente();?>
                        <?php }?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">TOTALES</td>
                            <td><?php echo number_format($total->interes, 2);?></td>
                            <td><?php echo number_format($total->capital, 2);?></td>
                            <td><?php echo number_format($total->mora, 2);?></td>
                            <td><?php echo number_format($total->form, 2);?></td>
                            <td><?php echo number_format($total->envio, 2);?></td>
                            <td><?php echo number_format($total->monto, 2);?></td>
                            <td><?php echo number_format($total->vale_monto, 2);?></td>
                            <td><?php echo number_format($total->cap_desc, 2);?></td>
                            <td><?php echo number_format($total->cap_inc, 2);?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    </tfoot>
            <?php

            ?>
            </tbody></table></center>
            <br><br><br>
            <script>
                popup=null;
                $('.det_pagos').click(function(){
                    var par={};
                    par.tarea='cuota_pagos';
                    par.tipo='pago';
                    par.vpag_id=$(this).attr('data-id');
                    mostrar_ajax_load();
                    $.post('ajax.php',par,function(resp){
                        ocultar_ajax_load();
                        var html=resp;
                        if(popup!==null){
                            popup.close();
                        }
                        popup = window.open('about:blank','reportes','left=100,width=800,height=600,top=0,scrollbars=yes');
                        var extra='';
                        popup.document.write(extra);
                        popup.document.write(html);
                        popup.document.close();
                    });
                });
                $('.det_venta').click(function(){
                    var ven_id=$(this).attr('data-id');
                    if(popup!==null){
                        popup.close();
                    }
                    popup = window.open('gestor.php?mod=venta&tarea=SEGUIMIENTO&id='+ven_id,'reportes','left=100,width=800,height=600,top=0,scrollbars=yes');
//                    var extra='';
//                    popup.document.write(extra);
//                    popup.document.write(html);
                    popup.document.close();
                });
                
                
                popup=null;
                $('.cupon').click(function(){
                    
                    var id = $(this).attr('data-ven_id');
                    var pag_id = $(this).attr('data-vpag_id');
                    if(popup!==null){
                        popup.close();
                    }
                    
//                    var ruta='gestor.php?mod=con_comprobante&info=ok&tarea=VER&id=';
//                    window.open(ruta+id,'Comprobante','width=900, height=500, scrollbars=yes');
//                    popup = window.open(ruta+id,'Comprobante','width=900, height=500, scrollbars=yes');
//                    popup.document.close();
                    
                    var ruta='gestor.php?mod=venta&tarea=CUPON&id=' + id + '&pag_id=' + pag_id;
                    window.open(ruta,'Comprobante','width=900, height=500, scrollbars=yes');
                    popup = window.open(ruta,'Comprobante','width=900, height=500, scrollbars=yes');
                    popup.document.close();
                
                });
            
            </script>
                
            <?php

    }
    
    function str_numeros_cuotas($str_ind_ids) {
        $str_ind_ids=trim($str_ind_ids);
        if(!$str_ind_ids){
            return '';
        }
         $cuotas=  FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_ind_ids) and ind_tipo='pcuota' ");
         $li_cu=array();
         foreach ($cuotas as $cu) {
             if($cu->ind_num_correlativo>0){
                 $li_cu[]=$cu->ind_num_correlativo;
             }else{
                 if($cu->ind_num_correlativo==-3){//fusion
                     $li_cu[]="FV $cu->ind_venta_id";
                 }else{
                     if($cu->ind_capital_desc>0 && $cu->ind_capital_inc>0){
                         $li_cu[]="Desc., Inc.";
                     }elseif($cu->ind_capital_desc>0){
                         $li_cu[]="Desc.";
                     }elseif($cu->ind_capital_inc>0){
                         $li_cu[]="Inc.";
                     }
                     
                 }
             }
             
         }
         return implode(',', $li_cu);
    }

    function str_numeros_cuotas_producto($str_ind_ids) {
        $str_ind_ids=trim($str_ind_ids);
        if(!$str_ind_ids){
            return '';
        }
         $cuotas=  FUNCIONES::lista_bd_sql("select * from interno_deuda_producto where idpr_id in ($str_ind_ids) and idpr_tipo='pcuota' ");
         $li_cu=array();
         foreach ($cuotas as $cu) {
             if($cu->idpr_num_correlativo>0){
                 $li_cu[]=$cu->idpr_num_correlativo;
             }else{
                 if($cu->idpr_num_correlativo==-3){//fusion
                     $li_cu[]="FV $cu->idpr_venta_id";
                 }else{
                     if($cu->idpr_capital_desc>0 && $cu->idpr_capital_inc>0){
                         $li_cu[]="Desc., Inc.";
                     }elseif($cu->idpr_capital_desc>0){
                         $li_cu[]="Desc.";
                     }elseif($cu->idpr_capital_inc>0){
                         $li_cu[]="Inc.";
                     }
                     
                 }
             }
             
         }
         return implode(',', $li_cu);
    }
    
    function listado_pagos_casa($venta){
//        $venta_producto = FUNCIONES::objeto_bd_sql("select * from venta_producto
//        where vprod_ven_id='$venta->ven_id' and vprod_estado='Pendiente'
//        order by vprod_id asc limit 0,1");
        
        $venta_producto = FUNCIONES::obtener_producto($venta->ven_id);
        
        ?>
            <div class="box-plan box-plan-casa">    
                <br><br>
                <?php
                $this->cabecera_venta_producto($venta_producto);
                ?>
            <br><br><center><h2>SEGUIMIENTO DE CUOTAS</h2>
            <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>		        	
                        <th >Id</th>
                        <th >Usuario</th>
                        <th >Nro. Cuota</th>
                        <th>Interes </th>
                        <th>Capital </th>
                        <th>Cuota </th>
                        <th>Form. </th>
                        <th>Monto </th>
                        <th>F. Prog.</th>
                        <th>F. Pag.</th>
                        <th class="tOpciones">Int. Pag.</th>
                        <th class="tOpciones">Cap. Pag.</th>
                        <th >Dif. Capital</th>
                        <th>Cuota Pag.</th>
                        
                        <!--<th >Mora Pag.</th>-->
                        <!--<th>Form.</th>-->
                        <th class="tOpciones">Form Pag.</th>
                        <th class="tOpciones">Envio Pag.</th>
                        <th>Monto. Pag.</th>
                        
                        <th>Desc.</th>
                        <th>Inc.</th>
                        <th>Saldo</th>
                        <!--<th>Costo</th>-->
                        <?php
                        $tiene_intereses = FUNCIONES::tiene_intereses($venta->ven_id);
                        if (!$tiene_intereses) {                            
                        ?>
                        <th>Dias Retraso</th>
                        <th>Multa</th>
                        <?php
                        }
                        ?>
                        <th>Estado</th>
                        <!--<th class="tOpciones">&nbsp;</th>-->
                    </tr>
                </thead>
                <tbody>
                <?php
                $conec= new ADO();
                $sql="SELECT idpr_id,idpr_monto,idpr_moneda,idpr_concepto,idpr_fecha,idpr_estado,idpr_monto_parcial,int_nombre,
                    int_apellido,idpr_fecha_programada,idpr_fecha_pago,idpr_interes,idpr_capital,idpr_saldo,
                    idpr_estado_mora,idpr_tabla_id, idpr_estado_parcial,idpr_interes_pagado,idpr_capital_pagado, 
                    idpr_monto_pagado, idpr_saldo_final, idpr_mora,idpr_mora_pagado,idpr_form,idpr_form_pagado,idpr_num_correlativo,idpr_usu_id,
                    idpr_costo_pagado,idpr_capital_desc,idpr_capital_inc,idpr_venta_id,idpr_envio_pagado
                FROM 
                interno_deuda_producto inner join interno on (idpr_tabla='venta_producto' and(idpr_estado='Pendiente' or idpr_estado='Pagado') and idpr_tabla_id='".$venta_producto->vprod_id."' and idpr_int_id=int_id)		
                order by idpr_id asc";
                
//                echo $sql.'<br>';
                $conec->ejecutar($sql);
                $num=$conec->get_num_registros();
                $conversor = new convertir();
                //$form=  FUNCIONES::atributo_bd_sql("select urb_val_form as campo from urbanizacion where urb_id='$venta->ven_urb_id'");
                $form = $venta->ven_form;
                if (!$tiene_intereses) {
                    $arr_dias_retr = FUNCIONES::array_dias_retraso($venta->ven_id, date('Y-m-d'));
                    $total_multa = 0;
                }
				?>
                    
                <?php for($i=0;$i<$num;$i++){ ?>
                    <?php $cu=$conec->get_objeto(); ?>
                    <?php $total_pagado=$cu->idpr_interes_pagado+$cu->idpr_capital_pagado+$cu->idpr_form_pagado+$cu->idpr_envio_pagado+$cu->idpr_mora_pagado; ?>
                    <tr>
                        <td><?php echo $cu->idpr_id;?></td>
                        <td><?php echo $cu->idpr_usu_id;?></td>
                        <td>
                            <?php 
                            if($cu->idpr_num_correlativo>=0){
                                echo $cu->idpr_num_correlativo>0?"<b># $cu->idpr_num_correlativo</b>":($cu->idpr_num_correlativo==0?'Cuota Inicial':'');
                            }else{
                                if($cu->idpr_num_correlativo==-3){
                                    echo "<b>FV $cu->idpr_venta_id</b>";
                                }else{
                                    if($cu->idpr_capital_desc>0 && $cu->idpr_capital_inc>0){
                                        echo "<b>Desc., Inc.</b>";
                                    }elseif($cu->idpr_capital_desc>0){
                                        echo "<b>Desc.</b>";
                                    }elseif($cu->idpr_capital_inc>0){
                                        echo "<b>Inc.</b>";
                                    }
                                }
                            }
                            ?>
                        </td>  
                        <td><?php echo number_format($cu->idpr_interes, 2);?></td>
                        <td><?php echo number_format($cu->idpr_capital, 2);?></td>
                        <td><?php echo number_format($cu->idpr_monto, 2);?></td>
                        <td><?php echo number_format($form, 2);?></td>
                        <td><?php echo number_format($form+$cu->idpr_monto, 2);?></td>
                        <td style="color:<?php echo $cu->idpr_fecha_programada < date('Y-m-d') && $cu->idpr_estado=='Pendiente'?'#ff0000':'000'?>"><?php echo FUNCIONES::get_fecha_latina($cu->idpr_fecha_programada);?></td>
                        <td><?php echo $cu->idpr_capital_pagado>0 || $cu->idpr_capital_desc>0 || $cu->idpr_capital_inc>0 ?FUNCIONES::get_fecha_latina($cu->idpr_fecha_pago):'';?></td>
                        <td><?php echo $total_pagado>0?number_format($cu->idpr_interes_pagado, 2):'';?></td>
                        <td><?php echo $total_pagado>0?number_format($cu->idpr_capital_pagado, 2):'';?></td>
                        <?php $dif_capital=$total_pagado>0?$cu->idpr_capital-$cu->idpr_capital_pagado:0;?>
                        <td><?php echo $dif_capital>0?number_format($dif_capital, 2):'';?></td>
                        
                        <td><?php echo $total_pagado>0?number_format($cu->idpr_monto_pagado, 2):'';?></td>
                        
                        <td><?php echo $total_pagado>0?number_format($cu->idpr_form_pagado, 2):'';?></td>
                        <td><?php echo $total_pagado>0?number_format($cu->idpr_envio_pagado, 2):'';?></td>
                        <td><?php echo $total_pagado>0?number_format($total_pagado, 2):'';?></td>
                        <td><?php echo $cu->idpr_capital_pagado>0 || $cu->idpr_capital_desc>0?number_format($cu->idpr_capital_desc, 2):'';?></td>
                        <td><?php echo $cu->idpr_capital_pagado>0 || $cu->idpr_capital_inc>0?number_format($cu->idpr_capital_inc, 2):'';?></td>
                        <?php 
                            if($cu->idpr_capital_pagado>0){
                                $saldo_ini=$cu->idpr_saldo+$cu->idpr_capital;
                                $saldo_fin=$saldo_ini-$cu->idpr_capital_pagado-$cu->idpr_capital_desc+$cu->idpr_capital_inc;
                            }else{
                                $saldo_fin=$cu->idpr_saldo;
                            }
                        ?>
                        <td><?php echo $cu->idpr_estado=='Pagado'?number_format($cu->idpr_saldo_final, 2):number_format($saldo_fin, 2);?></td>
                        <!--<td><?php // echo $total_pagado>0?number_format($cu->idpr_costo_pagado, 2):'';?></td>-->
                        <?php
                        if (!$tiene_intereses) {
                        ?>
                        <td >
                            <?php 
                            $dias_retraso =  $arr_dias_retr[$cu->idpr_id];
                            echo $dias_retraso;
                            ?>
                        </td>
                        <td >
                            <?php 
                            $multa_dia = $dias_retraso * $venta->ven_multa_dia;
                            echo $multa_dia;
                            $total_multa += $multa_dia;
                            ?>
                        </td>
                        <?php
                        }
                        ?>
                        <td >
                            <span class="reg_estado <?php echo $cu->idpr_estado=='Pagado'?'est_green':($cu->idpr_capital_pagado+$cu->idpr_interes_pagado>0?'est_gold':'est_red');?>">
                            <?php echo $cu->idpr_estado;?>
                            </span>
                        </td>
                        <!--<td>
                            <?php // if($cu->idpr_venta_id>0){?>
                                <img class="det_venta" src="images/b_browse.png" data-id="<?php echo $cu->idpr_venta_id;?>">
                            <?php // }else{?>
                                <?php // if($cu->idpr_capital_pagado+$cu->idpr_interes_pagado+$cu->idpr_mora_pagado+$cu->idpr_form_pagado>0){?>
                                <img class="det_pagos" data-id="<?php echo $cu->idpr_id;?>" src="images/b_browse.png">
                                <?php // } ?>
                            <?php // } ?>
                        </td>-->
                    </tr>
                    <?php $conec->siguiente(); ?>
                <?php                 
                                } 
                                if (!$tiene_intereses) {
                                ?>
                    <tr>
                        <td colspan="21">&nbsp;</td>
                        <td colspan="1"><?php echo number_format($total_multa, 2, '.', ',');?></td>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <?php
                                }
                    ?>
            </tbody>  
                </table>
                </center>
            </div>
        <?php
    }
    
    function listado_pagos(){
        $venta= FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");

        $this->barra_opciones($venta, 'SEGUIMIENTO',true);
            ?>
            <style>
                .reg_estado{color: #fff; padding: 1px 3px;}
                .est_green{background-color: #019721}
                .est_gold{background-color: #ff9601}
                .est_red{background-color: #ff0000;}
                .det_pagos,.det_venta{cursor: pointer;}
            </style>
            <script src="js/util.js"></script>
            <div id="contenido_reporte" style="clear:both;">
            <?php
            $this->cabecera_venta($venta);
            ?>
            <br><br><center><h2>SEGUIMIENTO DE CUOTAS</h2>
            <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>		        	
                        <th >Id</th>
                        <th >Usuario</th>
                        <th >Nro. Cuota</th>
                        <th>Interes </th>
                        <th>Capital </th>
                        <th>Cuota </th>
                        <th>Form. </th>
                        <th>Monto </th>
                        <th>F. Prog.</th>
                        <th>F. Pag.</th>
                        <th class="tOpciones">Int. Pag.</th>
                        <th class="tOpciones">Cap. Pag.</th>
                        <th >Dif. Capital</th>
                        <th>Cuota Pag.</th>
                        
                        <!--<th >Mora Pag.</th>-->
                        <!--<th>Form.</th>-->
                        <th class="tOpciones">Form Pag.</th>
                        <th class="tOpciones">Envio Pag.</th>
                        <th>Monto. Pag.</th>
                        
                        <th>Desc.</th>
                        <th>Inc.</th>
                        <th>Saldo</th>
                        <!--<th>Costo</th>-->
                        <?php
//                        $tiene_intereses = FUNCIONES::tiene_intereses($venta->ven_id);
                        $tiene_intereses = true;
                        if (!$tiene_intereses) {                            
                        ?>
                        <th>Dias Retraso</th>
                        <th>Multa</th>
                        <?php
                        }
                        ?>
                        <th>Estado</th>
                        <th class="tOpciones">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $conec= new ADO();
                $sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_monto_parcial,int_nombre,
                    int_apellido,ind_fecha_programada,ind_fecha_pago,ind_interes,ind_capital,ind_saldo,
                    ind_estado_mora,ind_tabla_id, ind_estado_parcial,ind_interes_pagado,ind_capital_pagado, 
                    ind_monto_pagado, ind_saldo_final, ind_mora,ind_mora_pagado,ind_form,ind_form_pagado,ind_num_correlativo,ind_usu_id,
                    ind_costo_pagado,ind_capital_desc,ind_capital_inc,ind_venta_id,ind_envio_pagado
                FROM 
                interno_deuda inner join interno on (ind_tabla='venta' and(ind_estado='Pendiente' or ind_estado='Pagado') and ind_tabla_id='".$_GET['id']."' and ind_int_id=int_id)		
                order by ind_id asc";
                //echo $sql.'<br>';
                $conec->ejecutar($sql);
                $num=$conec->get_num_registros();
                $conversor = new convertir();
                //$form=  FUNCIONES::atributo_bd_sql("select urb_val_form as campo from urbanizacion where urb_id='$venta->ven_urb_id'");
                $form = $venta->ven_form;
                if (!$tiene_intereses) {
                    $arr_dias_retr = FUNCIONES::array_dias_retraso($venta->ven_id, date('Y-m-d'));
                    $total_multa = 0;
                }
				?>
                    
                <?php for($i=0;$i<$num;$i++){ ?>
                    <?php $cu=$conec->get_objeto(); ?>
                    <?php $total_pagado=$cu->ind_interes_pagado+$cu->ind_capital_pagado+$cu->ind_form_pagado+$cu->ind_envio_pagado+$cu->ind_mora_pagado; ?>
                    <tr>
                        <td><?php echo $cu->ind_id;?></td>
                        <td><?php echo $cu->ind_usu_id;?></td>
                        <td>
                            <?php 
                            if($cu->ind_num_correlativo>=0){
                                echo $cu->ind_num_correlativo>0?"<b># $cu->ind_num_correlativo</b>":($cu->ind_num_correlativo==0?'Cuota Inicial':'');
                            }else{
                                if($cu->ind_num_correlativo==-3){
                                    echo "<b>FV $cu->ind_venta_id</b>";
                                }else{
                                    if($cu->ind_capital_desc>0 && $cu->ind_capital_inc>0){
                                        echo "<b>Desc., Inc.</b>";
                                    }elseif($cu->ind_capital_desc>0){
                                        echo "<b>Desc.</b>";
                                    }elseif($cu->ind_capital_inc>0){
                                        echo "<b>Inc.</b>";
                                    }
                                }
                            }
                            ?>
                        </td>  
                        <td><?php echo number_format($cu->ind_interes, 2);?></td>
                        <td><?php echo number_format($cu->ind_capital, 2);?></td>
                        <td><?php echo number_format($cu->ind_monto, 2);?></td>
                        <td><?php echo number_format($form, 2);?></td>
                        <td><?php echo number_format($form+$cu->ind_monto, 2);?></td>
                        <td style="color:<?php echo $cu->ind_fecha_programada < date('Y-m-d') && $cu->ind_estado=='Pendiente'?'#ff0000':'000'?>"><?php echo FUNCIONES::get_fecha_latina($cu->ind_fecha_programada);?></td>
                        <td><?php echo $cu->ind_capital_pagado>0 || $cu->ind_capital_desc>0 || $cu->ind_capital_inc>0 ?FUNCIONES::get_fecha_latina($cu->ind_fecha_pago):'';?></td>
                        <td><?php echo $total_pagado>0?number_format($cu->ind_interes_pagado, 2):'';?></td>
                        <td><?php echo $total_pagado>0?number_format($cu->ind_capital_pagado, 2):'';?></td>
                        <?php $dif_capital=$total_pagado>0?$cu->ind_capital-$cu->ind_capital_pagado:0;?>
                        <td><?php echo $dif_capital>0?number_format($dif_capital, 2):'';?></td>
                        
                        <td><?php echo $total_pagado>0?number_format($cu->ind_monto_pagado, 2):'';?></td>
                        
                        <td><?php echo $total_pagado>0?number_format($cu->ind_form_pagado, 2):'';?></td>
                        <td><?php echo $total_pagado>0?number_format($cu->ind_envio_pagado, 2):'';?></td>
                        <td><?php echo $total_pagado>0?number_format($total_pagado, 2):'';?></td>
                        <td><?php echo $cu->ind_capital_pagado>0 || $cu->ind_capital_desc>0?number_format($cu->ind_capital_desc, 2):'';?></td>
                        <td><?php echo $cu->ind_capital_pagado>0 || $cu->ind_capital_inc>0?number_format($cu->ind_capital_inc, 2):'';?></td>
                        <?php 
                            if($cu->ind_capital_pagado>0){
                                $saldo_ini=$cu->ind_saldo+$cu->ind_capital;
                                $saldo_fin=$saldo_ini-$cu->ind_capital_pagado-$cu->ind_capital_desc+$cu->ind_capital_inc;
                            }else{
                                $saldo_fin=$cu->ind_saldo;
                            }
                        ?>
                        <td><?php echo $cu->ind_estado=='Pagado'?number_format($cu->ind_saldo_final, 2):number_format($saldo_fin, 2);?></td>
                        <!--<td><?php // echo $total_pagado>0?number_format($cu->ind_costo_pagado, 2):'';?></td>-->
                        <?php
                        if (!$tiene_intereses) {
                        ?>
                        <td >
                            <?php 
                            $dias_retraso =  $arr_dias_retr[$cu->ind_id];
                            echo $dias_retraso;
                            ?>
                        </td>
                        <td >
                            <?php 
                            $multa_dia = $dias_retraso * $venta->ven_multa_dia;
                            echo $multa_dia;
                            $total_multa += $multa_dia;
                            ?>
                        </td>
                        <?php
                        }
                        ?>
                        <td >
                            <span class="reg_estado <?php echo $cu->ind_estado=='Pagado'?'est_green':($cu->ind_capital_pagado+$cu->ind_interes_pagado>0?'est_gold':'est_red');?>">
                            <?php echo $cu->ind_estado;?>
                            </span>
                        </td>
                        <td>
                            <?php if($cu->ind_venta_id>0){?>
                                <img class="det_venta" src="images/b_browse.png" data-id="<?php echo $cu->ind_venta_id;?>">
                            <?php }else{?>
                                <?php if($cu->ind_capital_pagado+$cu->ind_interes_pagado+$cu->ind_mora_pagado+$cu->ind_form_pagado>0){?>
                                <img class="det_pagos" data-id="<?php echo $cu->ind_id;?>" src="images/b_browse.png">
                                <?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php $conec->siguiente(); ?>
                <?php                 
                                } 
                                if (!$tiene_intereses) {
                                ?>
                    <tr>
                        <td colspan="21">&nbsp;</td>
                        <td colspan="1"><?php echo number_format($total_multa, 2, '.', ',');?></td>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <?php
                                }
                    ?>
            </tbody>
        </table>
        <br><br><br>
        <script>
            popup=null;
            $('.det_pagos').click(function(){
                var par={};
                par.tarea='cuota_pagos';
                par.tipo='cuota';
                par.ind_id=$(this).attr('data-id');
                mostrar_ajax_load();
                $.post('ajax.php',par,function(resp){
                    ocultar_ajax_load();
                    var html=resp;
                    if(popup!==null){
                        popup.close();
                    }
                    popup = window.open('about:blank','reportes','left=100,width=800,height=600,top=0,scrollbars=yes');
                    var extra='';
                    popup.document.write(extra);
                    popup.document.write(html);
                    popup.document.close();
                });
            });
            
            $('.det_venta').click(function(){
                var ven_id=$(this).attr('data-id');
                popup = window.open('gestor.php?mod=venta&tarea=SEGUIMIENTO&id='+ven_id,'reportes','left=100,width=800,height=600,top=0,scrollbars=yes');
//                    var extra='';
//                    popup.document.write(extra);
//                    popup.document.write(html);
                popup.document.close();
            });
            
        </script>
    </center>
<?php

    }
    /*PAGO DE CUOTAS*/

    function autorizacion_pago() {
        $venta=  FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$_GET[id]'");
        $cob_aut = FUNCIONES::objeto_bd_sql("select * from venta_cobro where vcob_ven_id=$venta->ven_id");
        if($venta->ven_bloqueado){
            $url=  "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado","$url",'Volver' );
            return;
        }
        if($cob_aut && $cob_aut->vcob_aut=='1'){
            if($_GET[acc]=='anular'){
                $conec=new ADO();
                $sql_del="delete from venta_cobro where vcob_ven_id='$venta->ven_id'";
                $conec->ejecutar($sql_del);
                $mensaje = "Anulacion del cobro de la Venta Nro $venta->ven_id fue anulado correctamente";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo."&tarea=AUTORIZACION&id=$venta->ven_id",'Generar Cobro','Correcto');
            }  else {
                $this->mostrar_cobro_autorizado($venta,$cob_aut);
            }
        }else{
            if($_POST){
                $this->guardar_autorizacion($venta);
            }else{
                $this->frm_autorizar_cobro($venta);
            }
        }
    }
    
    function mostrar_cobro_autorizado($venta,$cobro) {
        $this->formulario->dibujar_titulo("AUTORIZAR COBRO");
            ?>
        <div id="FormSent" style="width:80%">
            <div class="Subtitulo">COBROS AUTORIZADOS</div>
            <div id="ContenedorSeleccion">
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Codigo</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $cobro->vcob_codigo?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Fecha Pago</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($cobro->vcob_fecha_pago);?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Fecha Valor</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($cobro->vcob_fecha_valor);?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Saldo Inicial</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $cobro->vcob_saldo_inicial;?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta fwbold">Interes</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $cobro->vcob_interes;?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta fwbold">Capital</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $cobro->vcob_capital;?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta fwbold">Formulario</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $cobro->vcob_form;?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta fwbold">Envio</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $cobro->vcob_envio;?></div>
                    </div>
                </div>
                <div id="ContenedorDiv" hidden="">
                    <div class="Etiqueta fwbold">Mora</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $cobro->vcob_mora;?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta fwbold">Monto</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $cobro->vcob_monto;?></div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Saldo Final</div>
                    <div id="CajaInput">
                        <div class="read-input"><?php echo $cobro->vcob_saldo_final;?></div>
                    </div>
                </div>
                <?php 
                    $cuotas=array();
                    // interes
                    $interes_ids=trim($cobro->vcob_interes_ids)?explode(',', $cobro->vcob_interes_ids):array();
                    $interes_montos=trim($cobro->vcob_interes_montos)?explode(',', $cobro->vcob_interes_montos):array();
                    for ($i = 0; $i < count($interes_ids); $i++) {
                        $_id=$interes_ids[$i];
                        $_monto=$interes_montos[$i];
                        $cuota=$cuotas[$_id];
                        if(!$cuota){
                            $cuota=new stdClass();
                        }
                        $cuota->interes=$_monto;
                        $cuotas[$_id]=$cuota;
                    }
                    // capital
                    $capital_ids=trim($cobro->vcob_capital_ids)?explode(',', $cobro->vcob_capital_ids):array();
                    $capital_montos=trim($cobro->vcob_capital_montos)?explode(',', $cobro->vcob_capital_montos):array();
                    for ($i = 0; $i < count($capital_ids); $i++) {
                        $_id=$capital_ids[$i];
                        $_monto=$capital_montos[$i];
                        $cuota=$cuotas[$_id];
                        if(!$cuota){
                            $cuota=new stdClass();
                        }
                        $cuota->capital=$_monto;
                        $cuotas[$_id]=$cuota;
                    }
                    // formulario
                    $form_ids=trim($cobro->vcob_form_ids)?explode(',', $cobro->vcob_form_ids):array();
                    $form_montos=trim($cobro->vcob_form_montos)?explode(',', $cobro->vcob_form_montos):array();
                    for ($i = 0; $i < count($form_ids); $i++) {
                        $_id=$form_ids[$i];
                        $_monto=$form_montos[$i];
                        $cuota=$cuotas[$_id];
                        if(!$cuota){
                            $cuota=new stdClass();
                        }
                        $cuota->form=$_monto;
                        $cuotas[$_id]=$cuota;
                    }
                    $envio_ids=trim($cobro->vcob_envio_ids)?explode(',', $cobro->vcob_envio_ids):array();
                    $envio_montos=trim($cobro->vcob_envio_montos)?explode(',', $cobro->vcob_envio_montos):array();
                    for ($i = 0; $i < count($envio_ids); $i++) {
                        $_id=$envio_ids[$i];
                        $_monto=$envio_montos[$i];
                        $cuota=$cuotas[$_id];
                        if(!$cuota){
                            $cuota=new stdClass();
                        }
                        $cuota->envio=$_monto;
                        $cuotas[$_id]=$cuota;
                    }
                    // mora
                    $mora_ids=trim($cobro->vcob_mora_ids)?explode(',', $cobro->vcob_mora_ids):array();
                    $mora_montos=trim($cobro->vcob_mora_montos)?explode(',', $cobro->vcob_mora_montos):array();
                    for ($i = 0; $i < count($mora_ids); $i++) {
                        $_id=$mora_ids[$i];
                        $_monto=$mora_montos[$i];
                        $cuota=$cuotas[$_id];
                        if(!$cuota){
                            $cuota=new stdClass();
                        }
                        $cuota->mora=$_monto;
                        $cuotas[$_id]=$cuota;
                    }
                    ksort($cuotas);
                ?>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Pago de Cuotas</div>
                    <div id="CajaInput">
                        <table class="tablaLista" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th style="width: ">Id</th>
                                    <th>Nro Cuota</th>
                                    <th>Interes</th>
                                    <th>Capital</th>
                                    <th>Formulario</th>
                                    <th>Envio</th>
                                    <th hidden="">Mora</th>
                                    <th>Saldo</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $saldo_ini=$cobro->vcob_saldo_inicial; ?>
                                <?php $interes=$capital=$form=$envio=$mora=$sum_total=0; ?>
                                <?php foreach ($cuotas as $ind_id => $cuota) {?>
                                    <?php $cuota->nro=  FUNCIONES::atributo_bd_sql("select ind_num_correlativo as campo from interno_deuda where ind_id='$ind_id'");?>
                                    <tr>
                                        <td><?php echo $ind_id;?></td>
                                        <td><?php echo $cuota->nro;?></td>
                                        <td><?php echo $cuota->interes*1;?></td>
                                        <td><?php echo $cuota->capital*1;?></td>
                                        <td><?php echo $cuota->form*1;?></td>
                                        <td><?php echo $cuota->envio*1;?></td>
                                        <!--<td><?php // echo $cuota->mora*1;?></td>-->
                                        <td><?php echo $saldo_ini=$saldo_ini-$cuota->capital;?></td>
                                        <td><?php echo $total=$cuota->interes+$cuota->capital+$cuota->form+$cuota->envio+$cuota->mora;?></td>
                                        <?php
                                            $interes+=$cuota->interes*1;
                                            $capital+=$cuota->capital*1;
                                            $form+=$cuota->form*1;
                                            $mora+=$cuota->mora*1;
                                            $envio+=$cuota->envio*1;
                                            $sum_total+=$total*1;
                                        ?>
                                    </tr>
                                <?php }?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <td ><?php echo $interes;?></td>
                                    <td ><?php echo $capital;?></td>
                                    <td ><?php echo $form;?></td>
                                    <td ><?php echo $envio;?></td>
                                    <!--<td ><?php // echo $mora;?></td>-->
                                    <td >&nbsp;</td>
                                    <td ><?php echo $sum_total;?></td>
                                </tr>
                            </tfoot>
                        </table>
                        
                    </div>
                </div>
                
                <?php if($cobro->vcob_reformular){?>
                    <div style="text-align: left; border-bottom: 1px solid #3a3a3a; color: #3a3a3a; font-weight: bold;margin-bottom: 10px;">
                        Adecuar Saldo 
                    </div>
                    <?php if($cobro->vcob_tipo_plan=='plazo'){?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Plazo</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $cobro->vcob_plazo;?></div>
                            </div>
                        </div>
                    <?php }else if($cobro->vcob_tipo_plan=='cuota'){?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Cuota</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $cobro->vcob_cuota;?></div>
                            </div>
                        </div>
                    <?php }?>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Fecha 1er Cuota</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($cobro->vcob_fecha_pri_cuota);?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Nro. de Cuota Sig.</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $cobro->vcob_nro_cuota_sig;?></div>
                        </div>
                    </div>
                <?php } ?>
                <div id="ContenedorDiv">
                    <input class="boton" type="button" name="" value="Eliminar" onclick="location.href='gestor.php?mod=venta&tarea=AUTORIZACION&id=<?php echo $venta->ven_id;?>&acc=anular';" style="background: #ff0000;">
                </div>
                
            </div>
        </div>
        <?php
//            echo '<pre>';
//            print_r($cobro);
//            echo '</pre>';
    }
    
    function guardar_autorizacion($venta) {
        $this->barra_opciones($venta, 'AUTORIZACION');
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
//        return;
        $conec=new ADO();
        $conec->begin_transaccion();
        
        $cob_aut = FUNCIONES::objeto_bd_sql("select * from venta_cobro where vcob_ven_id=$venta->ven_id");
        if($cob_aut){
            if($cob_aut->vcob_aut=='0'){
                $sql_del="delete from venta_cobro where vcob_ven_id='$venta->ven_id'";
                $conec->ejecutar($sql_del);
            }else if($cob_aut->vcob_aut=='1'){
                $mensaje = "Ya existe un cobro Autorizado para la Venta Nro $venta->ven_id";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
                return false;
            }
        }
        $saldo_inicial=$_POST[saldo];
        $codigo = FUNCIONES::fecha_codigo();
        $fecha_pago=  FUNCIONES::get_fecha_mysql($_POST[fecha_pago]);
        $fecha_valor=  FUNCIONES::get_fecha_mysql($_POST[fecha_valor]);
        $interes=$_POST[interes]*1;
        $capital=$_POST[capital]*1;
        $form=$_POST[form]*1;
        $envio=$_POST[envio]*1;
        $mora=$_POST[mora]*1;
        $monto=$capital+$interes+$form+$mora+$envio;
        
        $fecha_cre=date('Y-m-d H:i:s');
        
        $capital_ids=$_POST[capital_ids];
        $capital_montos=$_POST[capital_montos];
        
        $interes_ids= '';
        $interes_montos=  '';
        if($interes>0){
            $interes_montos=$interes;
            if($capital>0){
                $arcap_ids=  explode(',',$capital_ids );
                $interes_ids=$arcap_ids[0];
            }else{
                $scuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 0,1");
                $interes_ids=$scuota->ind_id;
            }
        }

        $form_ids= '';
        $form_montos=  '';
        if($form>0){
            $form_montos=$form;
            if($capital>0){
                $arcap_ids=  explode(',',$capital_ids );
                $form_ids=$arcap_ids[0];
            }else{
                $scuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 0,1");
                $form_ids=$scuota->ind_id;
            }
        }
        $envio_ids= '';
        $envio_montos=  '';
        if($envio>0){
            $envio_montos=$envio;
            if($capital>0){
                $arcap_ids=  explode(',',$capital_ids );
                $envio_ids=$arcap_ids[0];
            }else{
                $scuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 0,1");
                $envio_ids=$scuota->ind_id;
            }
        }
        
        $saldo_final=$_POST[saldo_final];
        $u_saldo=$_POST[u_saldo];
        $refor=0;
        $tipo_plan='cuota';
        $plazo='';
        $cuota='';
        $nro_cuota_sig = '0';
        $fecha_pri_cuota = '0000-00-00';
        if($saldo_final<$u_saldo){ /// saldo final menor al ultimo saldo por que pago mas y se descuenta la ultima cuota
            $refor=1;
            $tipo_plan='cuota';
            $plazo='';
            $cuota=$venta->ven_cuota;
            $nro_cuota_sig=$_POST[nro_cuota_sig];
            $fecha_pri_cuota = $_POST[fecha_cuota_sig];
        }
        
        $dias_interes=$_POST[dias_interes];
        
//        $form_ids=$_POST[form_ids];
//        $form_montos=$_POST[form_montos];

        $mora_con_ids=$_POST[mora_con_ids];
        $mora_con_montos=$_POST[mora_con_montos];
        $mora_gen_ids=$_POST[mora_gen_ids];
        $mora_gen_montos=$_POST[mora_gen_montos];
        $mora_gen_dias=$_POST[mora_gen_dias];
        $mora_ids=$_POST[mora_ids];
        $mora_montos=$_POST[mora_montos];

        $interes_desc=$_POST[interes_ini]-$interes;
        
        
        $sql_ins = "insert into venta_cobro(
                        vcob_ven_id,vcob_codigo,vcob_fecha_pago,vcob_fecha_valor,vcob_int_id,vcob_moneda,vcob_saldo_inicial,
                        vcob_dias_interes,vcob_interes,vcob_capital,vcob_form,vcob_envio,vcob_mora,vcob_monto,vcob_saldo_final,
                        vcob_interes_ids,vcob_interes_montos,vcob_capital_ids,vcob_capital_montos,vcob_form_ids,vcob_form_montos,
                        vcob_envio_ids,vcob_envio_montos,vcob_mora_ids,vcob_mora_montos,vcob_mora_con_ids,vcob_mora_con_montos,
                        vcob_mora_gen_ids,vcob_mora_gen_montos,vcob_mora_gen_dias,
                        vcob_fecha_cre,vcob_usu_cre,vcob_aut,vcob_reformular,vcob_tipo_plan,vcob_plazo,vcob_cuota,
                        vcob_fecha_inicio,vcob_fecha_pri_cuota,vcob_nro_cuota_sig,vcob_interes_desc
                    )values(
                        $venta->ven_id,'$codigo','$fecha_pago','$fecha_valor','$venta->ven_int_id','$venta->ven_moneda','$saldo_inicial',
                        '$dias_interes','$interes','$capital','$form','$envio','$mora','$monto','$saldo_final',
                        '$interes_ids','$interes_montos','$capital_ids','$capital_montos','$form_ids','$form_montos',
                        '$envio_ids','$envio_montos',
                        '$mora_ids','$mora_montos','$mora_con_ids','$mora_con_montos',
                        '$mora_gen_ids','$mora_gen_montos','$mora_gen_dias',
                        '$fecha_cre','$_SESSION[id]','1','$refor','$tipo_plan','$plazo','$cuota',
                        '$fecha_valor','$fecha_pri_cuota','$nro_cuota_sig','$interes_desc'
                    )";
//        echo $sql_ins.'<br>';
        $conec->ejecutar($sql_ins);
        
        $exito = $conec->commit();
        if($exito){
            $mensaje = "Cobro Autorizado Guardado Exitosamente";
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo."&tarea=AUTORIZACION&id=$venta->ven_id",'Ver Cobro','Correcto');
        }else{                            
            $exito = false;
            $mensajes=$conec->get_errores();
            $mensaje = implode('<br>', $mensajes);
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }
        
        
    }
    
    function frm_autorizar_cobro($venta) {
        $this->barra_opciones($venta, 'AUTORIZACION');
        echo "<br>";
        $this->formulario->dibujar_titulo("AUTORIZAR COBRO");
        $ven_id=$_GET[id];
        $upago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='activo' and vpag_ven_id=$ven_id order by vpag_id desc limit 1");
        if(!$upago){
            $upago=new stdClass();
            $upago->vpag_saldo_final=$venta->ven_monto_efectivo;
            $upago->vpag_fecha_valor=$venta->ven_fecha;
            $upago->vpag_fecha_pago=$venta->ven_fecha;
        }
//        $pagado=  $this->total_pagado($ven_id);
        $urb=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        ?>
        <style>
            .img-boton{
                margin-left: 2px; float: left;cursor: pointer;
            }
            .img-boton:hover{opacity: 0.7}
            .tablaReporte thead tr th{ padding: 0 10px;}
            
            .nav-paso{ float: left; }
            .nav-pasos{ width: 100%; margin: 0 auto; }
            .num-paso{
                width: 35px; height: 33px; color: #fff; line-height: 32px; margin-bottom: 8px; border-radius: 17px; font-size: 25px;
            }
            .estado-espera{ background-color: #727272; }
            .estado-activo{ background-color: #3066ff; }
            .estado-success{ background-color: #068400; }
            .box-input-read{background: #ededed; border: 1px solid #bfc4c9; float: left; font-size: 12px; height: 23px; line-height: 22px; padding: 0 4px; width: 140px; font-style: italic;}
            .fwbold{font-weight: bold;}
        </style>
        <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
        <script src="js/util.js" type="text/javascript"></script>
        <form id="frm_sentencia" name="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=AUTORIZACION&id=<?php echo $venta->ven_id?>" >
            <input type="hidden" name="ven_id" id="ven_id" value="<?php echo $venta->ven_id;?>">
            <input type="hidden" name="interes_anual" id="interes_anual" value="<?php echo $venta->ven_val_interes;?>">
            <input type="hidden" name="saldo" id="saldo" value="<?php echo $upago->vpag_saldo_final;?>">
            <input type="hidden" name="plazo" id="plazo" value="<?php echo $venta->ven_plazo;?>">
            <input type="hidden" name="cuota" id="cuota" value="<?php echo $venta->ven_cuota;?>">
            <input type="hidden" name="u_fecha_valor" id="u_fecha_valor" value="<?php echo $upago->vpag_fecha_valor;?>">
            <input type="hidden" name="u_fecha_pago" id="u_fecha_pago" value="<?php echo $upago->vpag_fecha_pago;?>">
            
            <div class="nav-pasos" >
                <div id="nav-paso-1" class="nav-paso" style="width: 50%">
                    <div class="num-paso estado-activo">1</div>
                    <div class="estado-activo">&nbsp;</div>
                </div>
                <div id="nav-paso-2" class="nav-paso" style="width: 50%">
                    <div class="num-paso estado-espera">2</div>
                    <div class="estado-espera">&nbsp;</div>
                </div>
                <div style="clear: both;"></div>
            </div>
            <div class="cont-pasos">
                <div class="box-paso" id="frm_paso1">
                    <div id="Contenedor_NuevaSentencia">
                        <div id="FormSent" style="width:80%" >
                            <div class="Subtitulo">INGRESE LA FECHA DE PAGO Y FECHA VALOR</div>
                            <div id="ContenedorSeleccion">
                                <?php
                                $def_fecha=date('d/m/Y');
//                                $def_fecha='26/12/2014';
                                ?>
                                <?php $str_cliente=  FUNCIONES::interno_nombre($venta->ven_int_id);?>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Nro. Venta</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $venta->ven_id;?></div>
                                    </div>
                                    <div class="Etiqueta" style="width: 80px;">Cliente</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $str_cliente;?></div>
                                    </div>
                                </div>
                                
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Concepto</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $venta->ven_concepto;?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Moneda</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_moneda($venta->ven_moneda);?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Ultima Fecha Pago</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago);?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Ultima Fecha Valor</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor);?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Saldo</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $upago->vpag_saldo_final;?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Fecha Pago</div>
                                    <div id="CajaInput">
                                        <?php FORMULARIO::cmp_fecha('fecha_pago');?>
                                        <!--<input id="fecha_pago" name="fecha_pago" class="caja_texto" type="text" value="<?php // echo $def_fecha;?>" size="20" autocomplete="off">-->
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Fecha Valor</div>
                                    <div id="CajaInput">
                                        <input id="fecha_valor" name="fecha_valor" class="caja_texto" type="text" value="<?php echo $def_fecha;?>" size="20" autocomplete="off">
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div id="CajaBotones">
                                        <center>
                                            <input class="boton" type="button" value="Siguiente >>" onclick="javascript:frm_paso(2);">
                                        </center>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-paso" id="frm_paso2">
                    <div id="Contenedor_NuevaSentencia">
                        <div id="FormSent" style="width:80%">
                            <div class="Subtitulo">AUTORIZACION DE PAGO</div>
                            <div id="ContenedorSeleccion">
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Nro. Venta</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $venta->ven_id;?></div>
                                    </div>
                                    <div class="Etiqueta" style="width: 80px;">Cliente</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $str_cliente;?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Ultima Fecha Pago</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago);?></div>
                                    </div>
                                    <div class="Etiqueta" style="width: 80px;">Fecha Pago</div>
                                    <div id="CajaInput">
                                        <div class="read-input" id="txt_fecha_pago">&nbsp;</div>
                                        <!--<input type="text" size="20" name="fecha_pago" id="fecha_pago" value="<?php // echo date('d/m/Y');?>">-->
                                    </div>
                                </div>
                                
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Ultima Fecha Valor</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor);?></div>
                                    </div>
                                    <div class="Etiqueta" style="width: 80px;">Fecha Valor</div>
                                    <div id="CajaInput">
                                        <div class="read-input" id="txt_fecha_valor">&nbsp;</div>
                                        <!--<input type="text" size="20" name="fecha_valor" id="fecha_valor" value="<?php // echo date('d/m/Y');?>">-->
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Concepto</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $venta->ven_concepto;?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Moneda</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo FUNCIONES::get_moneda($venta->ven_moneda);?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Saldo</div>
                                    <div id="CajaInput">
                                        <div class="read-input"><?php echo $upago->vpag_saldo_final;?></div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Dias Interes</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="dias_interes" id="dias_interes" value="">
                                        <input type="hidden" name="interes_ini" id="interes_ini" value="" >
                                        <div class="read-input" id="txt_dias_interes">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Interes</div>
                                    <div id="CajaInput">
                                        <?php
                                        $desc_interes_usu_ids=  FUNCIONES::ad_parametro('par_desc_interes_usu_ids');
                                        $usu_ids=  explode(',', $desc_interes_usu_ids);
                                        $usu_descint=  in_array($_SESSION[id], $usu_ids);
                                        ?>
                                        <?php if($usu_descint){?>
                                            <input type="text" class="caja_texto" name="interes" id="interes" value="" autocomplete="off">
                                        <?php }else{?>
                                            <input id="interes" type="hidden" autocomplete="off" value="" name="interes">
                                            <div id="cmp_lab_interes" class="read-input"></div>
                                        <?php }?>
                                        
                                        
                                        
                                    </div>
                                </div>
                                <div id="ContenedorDiv" hidden="">
                                    <div class="Etiqueta">Formulario Pendiente</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="ap_form_ids" id="ap_form_ids" value="">
                                        <input type="hidden" name="ap_form_montos" id="ap_form_montos" value="">
                                        <input type="hidden" name="ap_form" id="ap_form" value="">
                                        <div class="read-input" id="txt_ap_form">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv" hidden="">
                                    <?php 
                                    $sql_cuotas_mora="select * from interno_deuda 
                                                        where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                                                        ind_capital_pagado > 0 and
                                                        ind_mora_pagado < ind_mora and ind_tipo='pcuota' order by ind_id asc
                                                    ";  
//                                    $cuotas_mora=  FUNCIONES::lista_bd_sql($sql_cuotas_mora);
                                    $cuotas_mora=  array();
                                    
                                    $ap_mora_con=0;
                                    
                                    $ap_mora_con_ids=array();
                                    $ap_mora_con_montos=array();
                                    foreach ($cuotas_mora as $cu) {
                                        $mmora=$cu->ind_mora-$cu->ind_mora_pagado;
                                        $ap_mora_con+=$mmora;
                                        $ap_mora_con_ids[]=$cu->ind_id;
                                        $ap_mora_con_montos[]=$mmora;
                                    }
                                    ?>
                                    <div class="Etiqueta">Mora Pendiente</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="ap_mora_con_ids" id="ap_mora_con_ids" value="<?php echo implode(',', $ap_mora_con_ids); ?>">
                                        <input type="hidden" name="ap_mora_con_montos" id="ap_mora_con_montos" value="<?php echo implode(',', $ap_mora_con_montos); ?>">
                                        <input type="hidden" name="ap_mora_con" id="ap_mora_con" value="<?php echo $ap_mora_con;?>">
                                        <div class="read-input" id="txt_ap_mora_con"><?php echo $ap_mora_con;?></div>
                                    </div>
                                </div>
                                
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Nro. de Cuotas</div>
                                    <div id="CajaInput">
                                        <input type="text" size="20" name="sol_nro_cuotas" id="sol_nro_cuotas" value="" autocomplete="off">
                                    </div>
                                    <img src="images/enter.png" width="25" class="img-boton" id="enter-cuotas">
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Cuotas</div>
                                    <div id="CajaInput">
                                        <table class="tablaReporte" id="list_cuotas" width="100%" cellspacing="0" cellpadding="0">
                                            <thead>
                                                <tr>
                                                    <th>Nro</th>
                                                    <th>Fecha Prog.</th>
                                                    <th>Interes</th>
                                                    <th>Capital</th>
                                                    <th>Monto</th>
                                                    <th>Saldo</th>
                                                    <!--<th>formulario</th>-->
                                                    <th>Mora</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <input type="hidden" id="nro_cuota_sig" name="nro_cuota_sig">
                                                        <input type="hidden" id="fecha_cuota_sig" name="fecha_cuota_sig">
                                                        <input type="hidden" id="s_cuota" name="s_cuota">
                                                        <input type="hidden" id="nro_cuotas" name="nro_cuotas">
                                                        <input type="hidden" id="u_capital" name="u_capital">
                                                        <input type="hidden" id="u_saldo" name="u_saldo" value="<?php echo $upago->vpag_saldo_final;?>">
                                                        <input type="hidden" id="nu_capital_ids" name="nu_capital_ids">
                                                        <input type="hidden" id="nu_capital_montos" name="nu_capital_montos">
                                                        <input type="hidden" id="nu_capital" name="nu_capital">
                                                        <span id="txt_nu_capital"></span>
                                                    </td>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
<!--                                                    <td>
                                                        <input type="hidden" id="nu_form_ids" name="nu_form_ids">
                                                        <input type="hidden" id="nu_form_montos" name="nu_form_montos">
                                                        <input type="hidden" id="nu_form" name="nu_form">
                                                        <span id="txt_nu_form"></span>
                                                    </td>-->
                                                    <td>
                                                        <input type="hidden" id="nu_mora_gen_ids" name="nu_mora_gen_ids">
                                                        <input type="hidden" id="nu_mora_gen_montos" name="nu_mora_gen_montos">
                                                        <input type="hidden" id="nu_mora_gen_dias" name="nu_mora_gen_dias">
                                                        <input type="hidden" id="nu_mora_gen" name="nu_mora_gen">
                                                        <span id="txt_nu_mora_gen"></span>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Capital</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="capital_ids" id="capital_ids" value="">
                                        <input type="hidden" name="capital_montos" id="capital_montos" value="">
                                        <!--<input type="text" class="caja_texto" name="capital" id="capital" value="" autocomplete="off">-->
                                        <input type="hidden" name="capital" id="capital" value="">
                                        <div class="read-input" id="txt_capital">&nbsp;</div>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Formulario</div>
                                    <div id="CajaInput">
<!--                                        <input type="hidden" name="form_ids" id="form_ids" value="">
                                        <input type="hidden" name="form_montos" id="form_montos" value="">-->

                                        <input type="hidden" name="moneda" id="moneda" value="<?php echo $venta->ven_moneda;?>" >
                                        <input type="hidden" name="par_val_form" id="par_val_form" value="<?php echo $venta->ven_form;?>" >
                                        <input type="text" class="caja_texto" name="form" id="form" value="" autocomplete="off">
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Envio</div>
                                    <div id="CajaInput">
                                        
                                        <input type="hidden" name="par_val_envio" id="par_val_envio" value="" >
                                        <input type="text" class="caja_texto" name="envio" id="envio" value="" autocomplete="off">
                                    </div>
                                </div>
                                <div id="ContenedorDiv" hidden="">
                                    <div class="Etiqueta fwbold">Mora</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="mora_con_ids" id="mora_con_ids" value="">
                                        <input type="hidden" name="mora_con_montos" id="mora_con_montos" value="">
                                        <input type="hidden" name="mora_gen_ids" id="mora_gen_ids" value="">
                                        <input type="hidden" name="mora_gen_montos" id="mora_gen_montos" value="">
                                        <input type="hidden" name="mora_gen_dias" id="mora_gen_dias" value="">
                                        
                                        <input type="hidden" name="mora_ids" id="mora_ids" value="">
                                        <input type="hidden" name="mora_montos" id="mora_montos" value="">
<!--                                        <input type="hidden" name="mora" id="mora" value="">
                                        <div class="read-input" id="txt_mora">0</div>-->
                                        <input type="text" class="caja_texto" name="mora" id="mora" value="" autocomplete="off">
                                    </div>
                                </div>
<!--                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Monto</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="monto" id="monto" value="">
                                        <div class="read-input" id="txt_monto">&nbsp;</div>
                                    </div>
                                </div>-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta fwbold">Monto</div>
                                    <div id="CajaInput">
                                        <input type="text" class="caja_texto" name="monto" id="monto" value="" autocomplete="off">
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Saldo</div>
                                    <div id="CajaInput">
                                        <input type="hidden" name="saldo_final" id="saldo_final" value="">
                                        <div class="read-input" id="txt_saldo_final">&nbsp;</div>
                                    </div>
                                </div>
                                
                                <div style="clear: both;"></div>
                                <div id="ContenedorDiv">
                                    <div id="CajaBotones">
                                        <center>
                                            <input class="boton" type="button" onclick="frm_paso(1);" value="<< Anterior" name="">
                                            <input class="boton" type="button" onclick="enviar_frm_pagos();" value="Guardar" name="">
                                        </center>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <style>
            .cmp_edit_input,.cmp_ico_edit,read-input{
                float: left;
            }
            .cmp_ico_edit{cursor: pointer;}
            .cmp_ico_edit img{height: 23px;}
            .dnone{display: none;}
        </style>
        <script>
            $("#fecha_pago").select();
            $("#fecha_pago").mask("99/99/9999");
            $("#fecha_valor").mask("99/99/9999");
            $('#frm_sentencia').submit(function (){
                var fecha_p=$('#fecha_pago').val();
                var fecha_v=$('#fecha_valor').val();
                if(fecha_p!=='' && fecha_v!==''){
                    mostrar_ajax_load();
                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha_p}, function(respuesta) {
                        ocultar_ajax_load();
                        var dato = JSON.parse(respuesta);
                        if (dato.response === "ok") {
                            document.frm_sentencia.submit();
                        } else if (dato.response === "error") {
                            $.prompt(dato.mensaje);
                            return false;
                        }
                    });
                }else{
                    $.prompt('Ingrese la Fecha de Pago y la Fecha Valor');
                }
                return false;
            });
            
            $('#fecha_valor').keypress(function (e){
                if(e.keyCode===13){
                    frm_paso(2);
                }
            });
            
            function frm_paso(nro_paso){
                if(nro_paso===1){
                    habilitar_formulario(1);
                }else if(nro_paso===2){
                    var fecha_p=$('#fecha_pago').val();
                    var fecha_v=$('#fecha_valor').val();
                    
                    if(fecha_p!=='' && fecha_v!==''){
                        var ufecha_p=$('#u_fecha_pago').val();
                        var ufecha_v=$('#u_fecha_valor').val();
                        var afecha_p=fecha_mysql(fecha_p);
                        var afecha_v=fecha_mysql(fecha_v);
                        
                        if(afecha_p>=ufecha_p && afecha_v>=ufecha_v){
                            mostrar_ajax_load();
                            $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha_p}, function(respuesta) {
                                
                                var dato = JSON.parse(respuesta);
                                if (dato.response === "ok") {
                                    _CAMBIOS=dato.cambios;
                                    var interes_anual=$('#interes_anual').val(); // formato mysql
                                    var saldo=$('#saldo').val(); // formato mysql
                                    
                                    var u_fecha=fecha_latina($('#u_fecha_valor').val()); // formato mysql
                                    var n_fecha=$('#fecha_valor').val(); // formato lat to mysql

                                    $.get('AjaxRequest.php', {peticion: 'dif_fechas', fecha1: u_fecha,fecha2:n_fecha}, function(respuesta) {
                                        ocultar_ajax_load();
                                        var resp=JSON.parse(respuesta);
                                        if(resp.type==='success'){
                                            var dias=resp.res;;
                            //                console.log(dias);
                            //                console.log('('+interes_anual+'/'+360+'/'+100+')'+'*'+dias);
                                            var interes=(interes_anual/360/100)*dias*saldo;
                                            $('#dias_interes').val(dias);
                                            $('#interes_ini').val(interes.toFixed(2));
                                            $('#interes').val(interes.toFixed(2));
                                            $('#interes').trigger('change');
//                                            $('#txt_interes').text(interes.toFixed(2));
                                            $('#txt_dias_interes').text(dias);
    //                                        calcular_monto();
                                            habilitar_formulario(2);
                                        }else{
                                            $.prompt('Dato incorrecto');
                                            return false;
                                        }
                                    });
                                        
                                    
                                } else if (dato.response === "error") {
                                    ocultar_ajax_load();
                                    $.prompt(dato.mensaje);
                                    return false;
                                }
                            });
                        }else{
                            $.prompt('La fecha valor y la fecha de pago actual debe ser mayor o igual a la ultima fecha de valor y fecha de pago');
                        }
                    }else{
                        $.prompt('Ingrese la Fecha de Pago y la Fecha Valor');
                    }
                    return false;
                }
            }
            var _FECHA_PAGO='';
            var _FECHA_VALOR='';
            var _CAMBIOS=null;
            function habilitar_formulario(form){
                if(form===1){
                    $('.box-paso').hide();
                    $('#frm_paso1').show();
                    $('#nav-paso-2 .estado-activo').each(function(){
                        $(this).removeClass('estado-activo');
                        $(this).addClass('estado-espera');
                    });
                    $('#nav-paso-1 .estado-success').each(function(){
                        $(this).removeClass('estado-success');
                        $(this).addClass('estado-activo');
                    });
                }else if(form===2){
                    _FECHA_PAGO=$('#fecha_pago').val();
                    _FECHA_VALOR=$('#fecha_valor').val();
                    $('#txt_fecha_pago').text(_FECHA_PAGO);
                    $('#txt_fecha_valor').text(_FECHA_VALOR);
                    $('.box-paso').hide();
                    $('#frm_paso2').show();
                    $('#nav-paso-2 .estado-espera').each(function(){
                        $(this).removeClass('estado-espera');
                        $(this).addClass('estado-activo');
                    });
                    $('#nav-paso-1 .estado-activo').each(function(){
                        $(this).removeClass('estado-activo');
                        $(this).addClass('estado-success');
                    });
                    
                    reset_capital();
                    reset_form();
                    reset_mora();
                    reset_envio();
                    calcular_monto();
//                    calcular_interes();
                    
                }
            }
            
            
            
            mask_decimal('#interes',null);
            mask_decimal('#form',null);
            mask_decimal('#mora',null);
            mask_decimal('#monto',null);
            mask_decimal('#envio',null);
//            cmp_edit("#capital",
//                {
//                    keyup:function(obj,e){
//                        var objcap=get_ini_capital();
//                        var capital=$(obj).val()*1;
//                        
//                        var capital_ids=new Array();
//                        var capital_montos=new Array();
//                        
//                        var icapital_ids=objcap.capital_ids.split(',');
//                        var icapital_montos=objcap.capital_montos.split(',');;
//
//                        for(var i=0;i<icapital_ids.length;i++){
//                            var icap_id=icapital_ids[i];
//                            var icap_monto=icapital_montos[i]*1;
//                            var res_cap=0;
//                            if(capital>=icap_monto){
//                                if(i===icapital_ids.length-1){
//                                    capital_ids.push(icap_id);
//                                    capital_montos.push(capital);
//                                    res_cap=capital;
//                                }else{
//                                    capital_ids.push(icap_id);
//                                    capital_montos.push(icap_monto);
//                                    res_cap=icap_monto;
//                                }
//                                
//                            }else if(capital>0 && capital<icap_monto){
//                                capital_ids.push(icap_id);
//                                capital_montos.push(capital);
//                                res_cap=capital;
//                            }
//                            capital=(capital-res_cap).toFixed(2)*1;
//                        }
//                        $('#capital_ids').val(capital_ids.join(','));
//                        $('#capital_montos').val(capital_montos.join(','));
//                        calcular_monto();
//                    },
//                    cancel:function(){
//                        var capital=reset_capital();
//                        calcular_monto();
//                        return capital.toFixed(2);
//                    }
//                }
//            );
            <?php if($usu_descint){?>
            cmp_edit("#interes",
                {
                    keyup:function(obj,e){
                        calcular_monto();
                        
                    },
                    cancel:function(){
                        var form=reset_interes();
                        calcular_monto();
                        return form.toFixed(2)*1;
                    }
                }
            );
            <?php }else{ ?>
            $('#interes').change(function(){
                $('#cmp_lab_interes').text($(this).val());
            });
            <?php } ?>
            cmp_edit("#form",
                {
                    keyup:function(obj,e){
                        calcular_monto();
                        
                    },
                    cancel:function(){
                        var form=reset_form();
                        calcular_monto();
                        return form.toFixed(2)*1;
                    }
                }
            );
            cmp_edit("#envio",
                {
                    keyup:function(obj,e){
                        calcular_monto();
                        
                    },
                    cancel:function(){
                        var form=reset_envio();
                        calcular_monto();
                        return form.toFixed(2)*1;
                    }
                }
            );
            cmp_edit("#mora",
                {
                    keyup:function(obj,e){
                        var objmora=get_ini_mora();
                        var mora=$(obj).val()*1;
                        
                        var mora_ids=new Array();
                        var mora_montos=new Array();
                        
                        var imora_ids=objmora.mora_ids.split(',');
                        var imora_montos=objmora.mora_montos.split(',');;

                        for(var i=0;i<imora_ids.length;i++){
                            var imora_id=imora_ids[i];
                            var imora_monto=imora_montos[i]*1;
                            var res_mora=0;
                            if(mora>=imora_monto){
                                if(i===imora_ids.length-1){
                                    mora_ids.push(imora_id);
                                    mora_montos.push(mora);
                                    res_mora=mora;
                                }else{
                                    mora_ids.push(imora_id);
                                    mora_montos.push(imora_monto);
                                    res_mora=imora_monto;
                                }
                            }else if(mora>0 && mora<imora_monto){
                                mora_ids.push(imora_id);
                                mora_montos.push(mora);
                                res_mora=mora;
                            }
                            mora=(mora-res_mora).toFixed(2)*1;
                        }
                        $('#mora_ids').val(mora_ids.join(','));
                        $('#mora_montos').val(mora_montos.join(','));
                        calcular_monto();
                    },
                    cancel:function(){
                        var mora=reset_mora();
                        calcular_monto();
                        return mora.toFixed(2);
                    }
                }
            );
            
            cmp_edit("#monto",
                {
                    keyup:function(obj,e){
                        
                        var interes=$('#interes').val()*1;
                        var form=$('#form').val()*1;
                        var mora=$('#mora').val()*1;
                        var objcap=get_ini_capital();
//                        console.log($(obj).val()+'-'+interes+'-'+form+'-'+mora);
                        var capital=$(obj).val()*1-interes-form-mora;
//                        console.log(capital);
                        $('#capital').val(capital.toFixed(2));
                        $('#txt_capital').text(capital.toFixed(2));
                        var capital_ids=new Array();
                        var capital_montos=new Array();
                        
                        var icapital_ids=objcap.capital_ids.split(',');
                        var icapital_montos=objcap.capital_montos.split(',');;

                        for(var i=0;i<icapital_ids.length;i++){
                            var icap_id=icapital_ids[i];
                            var icap_monto=icapital_montos[i]*1;
                            var res_cap=0;
                            if(capital>=icap_monto){
                                if(i===icapital_ids.length-1){
                                    capital_ids.push(icap_id);
                                    capital_montos.push(capital);
                                    res_cap=capital;
                                }else{
                                    capital_ids.push(icap_id);
                                    capital_montos.push(icap_monto);
                                    res_cap=icap_monto;
                                }
                                
                            }else if(capital>0 && capital<icap_monto){
                                capital_ids.push(icap_id);
                                capital_montos.push(capital);
                                res_cap=capital;
                            }
                            capital=(capital-res_cap).toFixed(2)*1;
                        }
                        $('#capital_ids').val(capital_ids.join(','));
                        $('#capital_montos').val(capital_montos.join(','));
                        calcular_saldo_final();
//                        calcular_monto();
                    },
                    cancel:function(){
                        var interes=$('#interes').val()*1;
                        var form=$('#form').val()*1;
                        var mora=$('#mora').val()*1;
                        var capital=reset_capital();
                        calcular_monto();
//                        calcular_saldo_final();
                        return (capital+interes+form+mora).toFixed(2)*1;
                        
                    }
                }
            );

            habilitar_formulario(1);
            
            mask_decimal('#sol_nro_cuotas',null);

            $('#enter-cuotas').click(function (){
                cargar_cuotas(); 
            });

            $('#sol_nro_cuotas').keypress(function (e){
                if(e.keyCode===13){
                    cargar_cuotas();
                }
            });

            $('#fecha_valor').change(function(){
//                calcular_interes();
            });

            function cargar_cuotas(){
                var sol_nro_cuotas=$('#sol_nro_cuotas').val()*1;
                var params={};
                params.tarea='vcuotas';
                params.fecha_pago=_FECHA_PAGO;
                params.ven_id=$('#ven_id').val();
                params.nro_cuotas=sol_nro_cuotas;
                mostrar_ajax_load();
                $.post('ajax.php',params,function (respuesta){
                    ocultar_ajax_load();
                    
                    var resp=JSON.parse(trim(respuesta));
//                    console.log(respuesta);
                    
                    var nu_capital_ids=new Array();
                    var nu_capital_montos=new Array();
                    var sum_nu_capital=0;
                    
                    var nu_form_ids=new Array();
                    var nu_form_montos=new Array();
                    var sum_nu_form=0;
                    
                    var nu_mora_gen_ids=new Array();
                    var nu_mora_gen_montos=new Array();
                    var nu_mora_gen_dias=new Array();
                    var sum_nu_mora_gen=0;
                    
                    $('#list_cuotas tbody tr').remove();
                    var trows='';
                    var u_capital=0;
                    var lista=resp.listac;
                    for(var i=0;i<lista.length;i++){
                        var fil=lista[i];
                        trows+='<tr>';
                        trows+='     <td>';
                        trows+='        <input type="hidden" name="cuotas[]" value="'+fil.id+'">';
                        trows+='        '+fil.nro;
                        trows+='     </td>';
                        trows+='     <td>'+fecha_latina(fil.fecha_prog)+'</td>';
                        trows+='     <td>'+fil.interes+'</td>';
                        trows+='     <td>'+fil.capital+'</td>';
                        trows+='     <td>'+fil.monto+'</td>';
                        trows+='     <td>'+fil.saldo+'</td>';
//                        trows+='     <td>'+fil.form+'</td>';
                        trows+='     <td>'+fil.mora+'</td>';
                        trows+='</tr>';
                        
                        nu_capital_ids.push(fil.id);
                        nu_capital_montos.push(fil.capital);
                        sum_nu_capital+=fil.capital*1;
                        
                        if(fil.form*1>0){
                            nu_form_ids.push(fil.id);
                            nu_form_montos.push(fil.form);
                            sum_nu_form+=fil.form*1;
                        }
                        
                        if(fil.mora*1>0){
                            nu_mora_gen_ids.push(fil.id);
                            nu_mora_gen_montos.push(fil.mora);
                            nu_mora_gen_dias.push(fil.mora_dias);
                            sum_nu_mora_gen+=fil.mora*1;
                        }
                        u_capital=fil.capital;
                        
                    }
                    sum_nu_capital=sum_nu_capital.toFixed(2);
                    sum_nu_form=sum_nu_form.toFixed(2);
                    sum_nu_mora_gen=sum_nu_mora_gen.toFixed(2);
                    
                    $('#nro_cuotas').val(lista.length);
                    $('#u_capital').val(u_capital);
                    var saldo=$('#saldo').val()*1;
                    var u_saldo=saldo-sum_nu_capital;
                    $('#u_saldo').val(u_saldo.toFixed(2));
                    
                    $('#nu_capital_ids').val(nu_capital_ids.join(','));
                    $('#nu_capital_montos').val(nu_capital_montos.join(','));
                    $('#nu_capital').val(sum_nu_capital);
                    $('#txt_nu_capital').text(sum_nu_capital);
                    
                    $('#nu_form_ids').val(nu_form_ids.join(','));
                    $('#nu_form_montos').val(nu_form_montos.join(','));
                    $('#nu_form').val(sum_nu_form);
                    $('#txt_nu_form').text(sum_nu_form);
                    
                    $('#nu_mora_gen_ids').val(nu_mora_gen_ids.join(','));
                    $('#nu_mora_gen_montos').val(nu_mora_gen_montos.join(','));
                    $('#nu_mora_gen_dias').val(nu_mora_gen_dias.join(','));
                    $('#nu_mora_gen').val(sum_nu_mora_gen);
                    $('#txt_nu_mora_gen').text(sum_nu_mora_gen);
                    $('#list_cuotas tbody').append(trows);
                    
                    var s_cuota=resp.scuota;
                    if(s_cuota===null){
                        var u_fil=lista[lista.length-1];
                        s_cuota={};
                        s_cuota.nro=u_fil.nro*1+1;
                        s_cuota.fecha_prog=sumar_dias(u_fil.fecha_prog,30);
                    }
//                    console.log(s_cuota);
                    $('#s_cuota').val(JSON.stringify(s_cuota));
                    $('#nro_cuota_sig').val(s_cuota.nro);
                    $('#fecha_cuota_sig').val(s_cuota.fecha_prog);
                    
//                    $('#capital').val(sum_nu_capital);
//                    $('#txt_capital').text(sum_nu_capital);
                    
                    reset_capital();
                    reset_form();
                    reset_mora();
                    calcular_monto();
                });
            }

            function get_ini_capital(){
                var capital_ids=trim($('#nu_capital_ids').val());
                var capital_montos=trim($('#nu_capital_montos').val());
                var capital=$('#nu_capital').val()*1;
                return {'capital_ids': capital_ids,'capital_montos': capital_montos,'capital':capital};
            }

            function get_ini_form(){
                var ap_form_ids=trim($('#ap_form_ids').val());
                var ap_form_montos=trim($('#ap_form_montos').val());
                var ap_form=$('#ap_form').val()*1;
                var nu_form_ids=trim($('#nu_form_ids').val());
                var nu_form_montos=trim($('#nu_form_montos').val());
                var nu_form=$('#nu_form').val()*1;
                
                var form_ids='';
                var form_montos='';
                if(ap_form_ids!=='' && nu_form_ids!==''){
                    form_ids=ap_form_ids+','+nu_form_ids;
                    form_montos=ap_form_montos+','+nu_form_montos;
                }else if(ap_form_ids!=='' && nu_form_ids===''){
                    form_ids=ap_form_ids;
                    form_montos=ap_form_montos;
                }else if(ap_form_ids==='' && nu_form_ids!==''){
                    form_ids=nu_form_ids;
                    form_montos=nu_form_montos;
                }
                var form=ap_form+nu_form;
                return {'form_ids': form_ids,'form_montos': form_montos,'form':form};
            }
            
            function get_ini_mora(){
                var ap_mora_con_ids=trim($('#ap_mora_con_ids').val());
                var ap_mora_con_montos=trim($('#ap_mora_con_montos').val());
                var ap_mora_con=$('#ap_mora_con').val()*1;
                
                var nu_mora_gen_ids=trim($('#nu_mora_gen_ids').val());
                var nu_mora_gen_montos=trim($('#nu_mora_gen_montos').val());
                var nu_mora_gen_dias=trim($('#nu_mora_gen_dias').val());
                var nu_mora_gen=$('#nu_mora_gen').val()*1;
                
                
                var mora_con_ids=ap_mora_con_ids;
                var mora_con_montos=ap_mora_con_montos;
                var mora_con=ap_mora_con;
                
                var mora_gen_ids=nu_mora_gen_ids;
                var mora_gen_montos=nu_mora_gen_montos;
                var mora_gen_dias=nu_mora_gen_dias;
                var mora_gen=nu_mora_gen;
                
                var mora_ids='';
                var mora_montos='';
                if(mora_con_ids!=='' && mora_gen_ids!==''){
                    mora_ids=mora_con_ids+','+mora_gen_ids;
                    mora_montos=mora_con_montos+','+mora_gen_montos;
                }else if(mora_con_ids!=='' && mora_gen_ids===''){
                    mora_ids=mora_con_ids;
                    mora_montos=mora_con_montos;
                }else if(mora_con_ids==='' && mora_gen_ids!==''){
                    mora_ids=mora_gen_ids;
                    mora_montos=mora_gen_montos;
                }
                var mora=mora_con+mora_gen;
                return {
                        'mora_ids': mora_ids,'mora_montos': mora_montos,'mora':mora,
                        'mora_con_ids':mora_con_ids,'mora_con_montos':mora_con_montos,'mora_con':mora_con,
                        'mora_gen_ids':mora_gen_ids,'mora_gen_montos':mora_gen_montos,'mora_gen_dias':mora_gen_dias,'mora_gen':mora_gen,
                        };
            }
            
            function reset_capital(){
                var obcap=get_ini_capital();
                
                $('#capital_ids').val(obcap.capital_ids);
                $('#capital_montos').val(obcap.capital_montos);
                
                $('#capital').val(obcap.capital.toFixed(2));
                $('#txt_capital').text(obcap.capital.toFixed(2));
                $('#capital').trigger('change');
                return obcap.capital;

            }
            
            function reset_interes(){
                var interes=$('#interes_ini').val();
                $('#interes').val(interes);
                $('#interes').trigger('change');
                return interes;
            }
            function reset_form(){
                var moneda=$('#moneda').val()*1;
                var cambio_usd=1;
                if(moneda==1){
                    for(var i=0;i<_CAMBIOS.length;i++){
                        var cambio=_CAMBIOS[i];
                        if(cambio.id==2){
                            cambio_usd=cambio.val;
                        }
                    }
                }
//                $('#form_ids').val(obform.form_ids);
//                $('#form_montos').val(obform.form_montos);
                var val_form=$('#par_val_form').val()*cambio_usd;
                $('#form').val(val_form.toFixed(2));
                $('#form').trigger('change');
                return val_form;
            }
            function reset_envio(){
                $('#envio').val(0);
                $('#envio').trigger('change');
                return 0;
            }
            
            function reset_mora(){
                var obmora=get_ini_mora();
                $('#mora_con_ids').val(obmora.mora_con_ids);
                $('#mora_con_montos').val(obmora.mora_con_montos);
                $('#mora_con').val(obmora.mora_con);
                $('#mora_gen_ids').val(obmora.mora_gen_ids);
                $('#mora_gen_montos').val(obmora.mora_gen_montos);
                $('#mora_gen_dias').val(obmora.mora_gen_dias);
                $('#mora_gen').val(obmora.mora_gen);
                
                $('#mora_ids').val(obmora.mora_ids);
                $('#mora_montos').val(obmora.mora_montos);
                $('#mora').val(obmora.mora.toFixed(2));
                $('#mora').trigger('change');
                return obmora.mora;
            }
            
//            function calcular_interes(){
//                var interes_anual=$('#interes_anual').val(); // formato mysql
//                var saldo=$('#saldo').val(); // formato mysql
//                var u_fecha=$('#u_fecha_valor').val(); // formato mysql
//                var n_fecha=fecha_mysql($('#fecha_valor').val()); // formato lat to mysql
//                
//                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha_p}, function(respuesta) {
//                    var dias=diferencia_dias(u_fecha,n_fecha);
//    //                console.log(dias);
//    //                console.log('('+interes_anual+'/'+360+'/'+100+')'+'*'+dias);
//                    var interes=(interes_anual/360/100)*dias*saldo;
//                    $('#dias_interes').val(dias);
//                    $('#interes').val(interes.toFixed(2));
//                    $('#txt_interes').text(interes.toFixed(2));
//                    $('#txt_dias_interes').text(dias);
//                    calcular_monto();
//                });
//                    
//                
//            }

            function calcular_monto(){
                var interes=$('#interes').val()*1;
                var capital=$('#capital').val()*1;
                var form=$('#form').val()*1;
                var envio=$('#envio').val()*1;
                var mora=$('#mora').val()*1;
//                console.log(interes+'+'+capital);
                var monto=interes+capital+form+mora+envio;
//                console.log(monto);
                $('#monto').val(monto.toFixed(2));
                $('#monto').trigger('change');
//                $('#txt_monto').text(monto.toFixed(2));
                calcular_saldo_final();
            }
            function calcular_saldo_final(){
                var nro_cuotas=$('#nro_cuotas').val()*1;
                var saldo=$('#saldo').val()*1;
                var capital=$('#capital').val()*1;
                var saldo_final=(saldo-capital).toFixed(2)*1;
                $('#saldo_final').val(saldo_final.toFixed(2));
                $('#txt_saldo_final').text(saldo_final.toFixed(2));
                var u_saldo=$('#u_saldo').val()*1;
//                console.log(saldo_final+'==='+u_saldo);
                if(saldo_final===u_saldo || nro_cuotas===0){
                    $('#box-reformular').hide();
                }else{
                    var str_scuota=$('#s_cuota').val();
                    if(str_scuota!=='null'){
                        var scuota=JSON.parse(str_scuota);
                    }
                    
                    $('#def_plan_efectivo option:selected').removeAttr('selected')
                    
//                    $('#box-reformular').hide();
                    if(saldo_final<u_saldo){ // pago MAS capital (mantiene la cuota)
                        $('#box-reformular').show();
                        $('#def_plan_efectivo option:[value="cm"]').attr('selected','true');
                        $('#cuota_mensual').val($('#cuota').val());
                    }else if(saldo_final>u_saldo){ // pago MENOS capital (mantiene el plazo)
                        $('#box-reformular').hide();
//                        $('#def_plan_efectivo option:[value="cm"]').attr('selected','true');
//                        $('#cuota_mensual').val($('#cuota').val());
                        
//                        $('#def_plan_efectivo option:[value="mp"]').attr('selected','true');
//                        var plazo=$('#plazo').val()*1;
//                        var plazo_res=plazo-(scuota.nro*1)+1;
//                        if(plazo_res<=0){
//                            plazo_res=1;
//                        }
//                        $('#meses_plazo').val(plazo_res);
                    }
                    $('#def_plan_efectivo').trigger('change');
                    $('#fecha_pri_cuota').val(fecha_latina(scuota.fecha_prog));
                }
            }

            function enviar_frm_pagos(){
                var capital_ids=trim($('#capital_ids').val()).split(',');
                var capital_montos=trim($('#capital_montos').val()).split(',');
                var capital=$('#capital').val()*1;
                var nro_cuotas=$('#nro_cuotas').val()*1;
                                                               
                if(nro_cuotas>0){
                    var objcap=get_ini_capital();
                    var icapital_ids=(objcap.capital_ids).split(',');
                    var icapital_montos=(objcap.capital_montos).split(',');
                    if(!(capital_ids.length === capital_montos.length && 
                        icapital_ids.length===icapital_montos.length && 
                        capital_ids.length===icapital_ids.length)){
                        $.prompt("El total capital a pagar no cubre con el Capital de la ultima Cuota");
                        return false;
                    }
                }
                if(nro_cuotas<=0){
                    if(capital!==0){
                        $.prompt('El Capital no debe variar ya que el Numero de Cuotas es 0');
                        return false; 
                    }
                }
                
//                var objform=get_ini_form();
//                var form=$('#form').val()*1;
//                var iform=objform.form*1;
                
//                if(form>iform){
//                    $.prompt('El pago del Formulario no debe exceder a '+iform);
//                    return false;
//                }
                var objmora=get_ini_mora();
                var mora=$('#mora').val()*1;
                var imora=objmora.mora*1;
                
                if(mora>imora){
                    $.prompt('El pago de la Mora no debe exceder a '+imora);
                    return false;
                }
                
                var saldo_final=$('#saldo_final').val()*1;

                if(saldo_final<0){
                    $.prompt("El Saldo Final debe ser Mayor a 0");
                    return false;
                }
//                return false;

                var fecha=$('#fecha_pago').val();
                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                    var dato = JSON.parse(respuesta);
                    if (dato.response === "ok") {
//                        console.info('OK');
                        document.frm_sentencia.submit();                        
                    } else if (dato.response === "error") {
                        $.prompt(dato.mensaje);
                        return false;
                    }
                });
            }

//            calcular_interes();
            
            $('#def_plan_efectivo').change(function(){
                var def=$(this).val();
                if(def==='mp'){
                    $('#meses_plazo').parent().show();
                    $('#cuota_mensual').parent().hide();
                    $('#cuota_interes').parent().hide();
                    $('#ver_plan_efectivo').show();
                    $('#add_cuota_efectivo').hide();
                    $('#fecha_pri_cuota').prev('span').text('Fecha de la Primer Cuota: ');
                    $('#plan_manual_efectivo').hide();
                    $('#def_cuota').parent().hide();

                }else if(def==='cm'){
                    $('#meses_plazo').parent().hide();
                    $('#cuota_mensual').parent().show();
                    $('#cuota_interes').parent().hide();
                    $('#ver_plan_efectivo').show();
                    $('#add_cuota_efectivo').hide();
                    $('#cuota_mensual').prev('span').text('Monto Cuota: ');
                    $('#cuota_mensual').prev('span').show();
                    $('#fecha_pri_cuota').prev('span').text('Fecha de la Primer Cuota: ');
                    $('#plan_manual_efectivo').hide();
                    $('#def_cuota').parent().hide();
                }else if(def==='manual'){
                    $('#meses_plazo').parent().hide();
                    $('#cuota_mensual').parent().show();
                    $('#cuota_interes').parent().show();
                    $('#ver_plan_efectivo').hide();
                    $('#add_cuota_efectivo').show();
                    $('#cuota_mensual').prev('span').hide();
                    $('#fecha_pri_cuota').prev('span').text('Fecha Programada: ');
                    $('#plan_manual_efectivo').show();
                    $('#def_cuota').parent().show();
                }
            });
            
            $('#def_plan_efectivo').trigger('change');
            
            function ver_plan_pago(){
                var saldo_financiar=0;
                var ncuotas=0;
                var fecha_pri_cuota=0;
                var monto_cuota=0;
                var def=$('#def_plan_efectivo option:selected').val();
                if(def==='mp'){
                    ncuotas = $('#meses_plazo').val();
                    monto_cuota = '';
                }else if(def==='cm'){
                    ncuotas = '';
                    monto_cuota = $('#cuota_mensual').val();
                }
                saldo_financiar=$('#saldo_final').val()*1;
                fecha_pri_cuota =$('#fecha_pri_cuota').val();
                var fecha_inicio =$('#fecha_valor').val();

                var interes = $('#interes_anual').val();

                if ((ncuotas*1 > 0 || monto_cuota*1 > 0) && saldo_financiar > 0  && fecha_pri_cuota !== '') {
                    var moneda = 2;//document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
                    var par={};
                    par.tarea='plan_pagos';
                    par.saldo_financiar=saldo_financiar;
                    par.monto_total=saldo_financiar;
                    par.meses_plazo=ncuotas;
                    par.cuota_mensual=monto_cuota;
                    par.ven_moneda=moneda;
                    par.nro_inicio=$('#nro_cuota_sig').val();
                    par.fecha_inicio=fecha_inicio;
                    par.fecha_pri_cuota=fecha_pri_cuota;
                    par.interes=interes;
                    mostrar_ajax_load();
                    $.post('ajax.php',par,function(resp){
                        ocultar_ajax_load();
                        abrir_popup(resp);
                    });

                } else {
                    $('#tprueba tbody').remove();
                    $.prompt('-La Fecha no debe estar vacia.</br>-Los meses de plazo o la cuota mensual debe ser mayor a cero.', {opacity: 0.8});
                }
                
            }
            var popup=null;
            function abrir_popup(html){
                if(popup!==null){
                    popup.close();
                }
                popup = window.open('about:blank','reportes','left=100,width=900,height=500,top=0,scrollbars=yes');
                var extra='';
                extra+='<html><head><title>Vista Previa</title><head>';
                extra+='<link href=css/estilos.css rel=stylesheet type=text/css />';
                extra+='</head> <body> <div id=imprimir> <div id=status> <p>';
                extra+='<a href=javascript:window.print();>Imprimir</a>  <a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>';
                popup.document.write(extra);
                popup.document.write(html);
                popup.document.write('</center></body></html>');
                popup.document.close();

            }
            
//            mostrar_ajax_load();
//            setTimeout('ocultar_ajax_load()',500);
//            ocultar_ajax_load();
        </script>
        <?php
    }
    
    function reformular() {
//        $pago_parc=  FUNCIONES::objetos_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$_GET[id] and ind_capital_pagado>0 and ind_capital_pagado< ind_capital and ind_estado='Pendiente'");
//        if($pago_parc->get_num_registros()>0){
//            $this->formulario->ventana_volver("No puede reformular el plan de pagos por que no completo el pago de una cuota", "gestor.php?mod=venta&tarea=ACCEDER", 'Volver', 'Error');
//            return;
//        }
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$_GET[id]'");
        if($venta->ven_bloqueado){
            $url=  "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La No puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado","$url",'Volver' );
            return;
        }
        if($_POST){
            $this->guardar_reformulacion($venta);
        }else{
            $this->frm_reformulacion($venta);
        }
    }
    
    function guardar_reformulacion($venta){
//        echo '<pre>';
//        print_r($_POST);
//        echo '</pre>';
        $atp=array('mp'=>'plazo','cm'=>'cuota','manual'=>'manual');
        $conec=new ADO();
        $conec->begin_transaccion();
        $fecha_ref=FUNCIONES::get_fecha_mysql($_POST[fecha_ref]);
        $saldo=$_POST[saldo];
        $saldo_final=$_POST[saldo_final];
        
        $tipo_plan=$atp[$_POST[def_plan_efectivo]];
//        $tipo_plan='cuota';
        $nro_cuota_inicio=$_POST[nro_cuota_sig];
        $plazo=$_POST[meses_plazo];
        if($tipo_plan=='cuota'){
            $plazo +=($nro_cuota_inicio-1);
        }
        
        
        
        $cuota=$_POST[cuota_mensual];
//        $plazo=$venta->ven_plazo;
//        $cuota=$venta->ven_cuota;
        
        
        $rango=$_POST[ven_rango];
        $frecuencia=$_POST[ven_frecuencia];
        
        $fecha_inicio=  FUNCIONES::get_fecha_mysql($_POST[fecha_inicio]);
        $fecha_pri_cuota=  FUNCIONES::get_fecha_mysql($_POST[fecha_pri_cuota]);
        $capital_desc=$_POST[descuento];
        $capital_inc=$_POST[incremento];
        $par_adecuacion=null;
        if($capital_desc>0 || $capital_inc>0){
            $par_adecuacion=array(
                'capital_desc'=>$capital_desc,
                'capital_inc'=>$capital_inc,
                'saldo'=>$saldo,
                'saldo_final'=>$saldo_final,
                'fecha_valor'=>$fecha_inicio,
                'fecha_ref'=>$fecha_ref,
                'venta'=>$venta,
                
            );
//            $this->icremento_descuento_venta($venta, $params, $conec);
        }
        $params=array(
            'int_id'=>$venta->ven_int_id,
            'ven_id'=>$venta->ven_id,
            'interes_anual'=>$venta->ven_val_interes,
            'moneda'=>$venta->ven_moneda,
            'concepto'=>$venta->ven_concepto,
            'fecha'=>$venta->ven_fecha,
            'saldo'=>$saldo_final,
            'tipo_plan'=>$tipo_plan,
            'cuota'=>$cuota,
            'plazo'=>$plazo,
            'nro_cuota_inicio'=>$nro_cuota_inicio,
            'fecha_inicio'=>$fecha_inicio,
            'fecha_pri_cuota'=>$fecha_pri_cuota,
            'val_form'=>$venta->ven_form,
            'rango'=>$rango,
            'frecuencia'=>$frecuencia,
        );
        $ser_params=  serialize($params);
        $fecha_cre=date('Y-m-d');
        $sql_ins_ref="insert into venta_reformulacion (vref_ven_id,vref_usu,vref_fecha,vref_fecha_cre,vref_parametros)values('$venta->ven_id','$_SESSION[id]','$fecha_ref','$fecha_cre','$ser_params')";
        $conec->ejecutar($sql_ins_ref);
        $this->reformular_plan($params,$conec,$par_adecuacion);
        $exito = $conec->commit();
        $this->barra_opciones($venta, 'REFORMULAR');
        echo "<br>";
        if($exito){
            $mensaje = "Reformulacion realizado Exitosamente";
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Correcto');
//            $this->formulario->ventana_volver("Pago realizado Correctamente", $this->link . '?mod=' . $this->modulo, '', $tipo);
        }else{                            
            $exito = false;
            $mensajes=$conec->get_errores();
            $mensaje = implode('<br>', $mensajes);
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }
    }
    
    function icremento_descuento_venta($params,&$conec=null) {
        if($conec==null){
            $conec=new ADO();
        }
        $par=(object)$params;
        $capital_desc=$par->capital_desc;
        $capital_inc=$par->capital_inc;
        $saldo=$par->saldo;
        $saldo_final=$par->saldo_final;
        $fecha_valor=$par->fecha_valor;
        $fecha_ref=$par->fecha_ref;
        $venta=$par->venta;
        $fecha_cre=date('Y-m-d');
        $insert_dif="insert into interno_deuda (
                            ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_usu_id,
                            ind_moneda,ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,
                            ind_estado,ind_interes_pagado,ind_capital_pagado,ind_monto_pagado,ind_saldo_final,
                            ind_fecha_pago,ind_dias_interes,ind_concepto,ind_observacion,ind_form,ind_form_pagado,
                            ind_mora,ind_mora_pagado,ind_tipo,ind_fecha_cre,ind_estado_parcial,ind_monto_parcial,
                            ind_costo_pagado,ind_capital_inc,ind_capital_desc,ind_interes_desc,ind_form_desc
                        )values(
                            'venta','$venta->ven_id','-1','$venta->ven_int_id','$fecha_cre','$_SESSION[id]',
                            '$venta->ven_moneda','0','0',0,'$saldo','$fecha_valor',
                            'Pagado','0','0','0',$saldo_final,
                            '$fecha_ref','0','Adecuacion de Saldo $venta->ven_concepto','',0,0,
                            0,0,'pcuota','$fecha_cre','listo','0',
                            0,'$capital_inc','$capital_desc',0,0

                        )";
        $conec->ejecutar($insert_dif,true,true);
        $cua_ind_id=  ADO::$insert_id;
        $codigo=  FUNCIONES::fecha_codigo();
        $insert_pago="insert into venta_pago (
                            vpag_ven_id,vpag_codigo,vpag_fecha_pago,vpag_fecha_valor,vpag_int_id,vpag_moneda,
                            vpag_saldo_inicial,vpag_dias_interes,vpag_interes,vpag_capital,vpag_form,
                            vpag_mora,vpag_monto,vpag_saldo_final,vpag_estado,vpag_interes_ids,
                            vpag_interes_montos,vpag_capital_ids,vpag_capital_montos,
                            vpag_form_ids,vpag_form_montos,vpag_mora_ids,vpag_mora_montos,vpag_mora_con_ids,
                            vpag_mora_con_montos,vpag_mora_gen_ids,vpag_mora_gen_montos,vpag_mora_gen_dias,
                            vpag_fecha_cre,vpag_usu_cre,vpag_cob_usu,vpag_cob_codigo,vpag_cob_aut,
                            vpag_costo,vpag_costo_ids,vpag_costo_montos,vpag_capital_inc,vpag_capital_desc,
                            vpag_interes_desc,vpag_form_desc,vpag_recibo,vpag_importado,vpag_suc_id
                        )values(
                            '$venta->ven_id','$codigo','$fecha_ref','$fecha_valor','$venta->ven_int_id','$venta->ven_moneda',
                            '$saldo',0,0,0,0,
                            0,0,'$saldo_final','Activo','',
                            '','$cua_ind_id','0',
                            '','','','','',
                            '','','','',
                           '$fecha_cre','$_SESSION[id]','$_SESSION[id]','','0',
                            0,'','','$capital_inc','$capital_desc',
                            0,0,'','0','$_SESSION[suc_id]'
                        )";
        $conec->ejecutar($insert_pago,false);
        
        $ucuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");

        $pagado = $this->total_pagado($venta->ven_id);

        $capital_pag=$pagado->capital;
        $tcapital_inc=$pagado->incremento;
        $tcapital_desc=$pagado->descuento;
        $cuota_pag=$ucuota->ind_num_correlativo;
        
        $sql_up="update venta set 
                            ven_ufecha_pago='$fecha_ref', ven_ufecha_valor='$fecha_valor', 
                            ven_cuota_pag='$cuota_pag', ven_capital_pag='$capital_pag',
                            ven_capital_inc='$tcapital_inc', ven_capital_desc='$tcapital_desc', 
                            ven_usaldo='$saldo_final'
                    where ven_id='$venta->ven_id'";

        $conec->ejecutar($sql_up);
//        
//        $sql_up="update venta set ven_ufecha_pago='$fecha_ref', ven_ufecha_valor='$fecha_valor', ven_usaldo='$saldo_final' where ven_id='$venta->ven_id'";
//        $conec->ejecutar($sql_up);

    }

    
    
    function pri_cuota_pendiente($venta){
        $pcuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pendiente' and (ind_capital_pagado+ind_interes_pagado+ind_form_pagado)=0 and ind_tipo='pcuota' and ind_num_correlativo>0 order by ind_id asc limit 1");
        if(!$pcuota){
//            $ucuota=FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pendiente' and ind_capital_pagado>0 and ind_tipo='pcuota' and ind_num_correlativo>0 order by ind_id asc limit 1");
//            if(!$ucuota){
            $ucuota=FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pagado' and ind_tipo='pcuota' order by ind_id desc limit 1");
            if(!$ucuota){
                $ucuota=new stdClass();
                $ucuota->ind_num_correlativo=0;
                $ucuota->ind_fecha_programada=$venta->ven_fecha;
            }
//            }
            $pcuota=new stdClass();
            $pcuota->ind_num_correlativo=$ucuota->ind_num_correlativo+1;
            if($venta->ven_frecuencia=='30_dias'){
                $pcuota->ind_fecha_programada=  FUNCIONES::sumar_dias(30, $ucuota->ind_fecha_programada);
            }elseif($venta->ven_frecuencia=='dia_mes'){
                $af=  explode('-', $ucuota->ind_fecha_programada);
                $pcuota->ind_fecha_programada =  FUNCIONES::sumar_meses($ucuota->ind_fecha_programada, $venta->ven_rango, $af[2]);
            }
        }
        return $pcuota;
    }
    function frm_reformulacion($venta){
        $this->barra_opciones($venta, 'REFORMULAR');
        echo "<br>";
        $this->formulario->dibujar_titulo("REFORMULACION DE VENTA");
        $pagado=  $this->total_pagado($venta->ven_id);
        $upago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
        if(!$upago){
            $upago=new stdClass();
            $upago->vpag_saldo_final=$venta->ven_monto_efectivo;
            $upago->vpag_fecha_valor=$venta->ven_fecha;
            $upago->vpag_fecha_pago='0000-00-00';
        }
        $scuota=  $this->pri_cuota_pendiente($venta);
//        $scuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pendiente' and ind_tipo='pcuota' and ind_num_correlativo!=0 order by ind_id asc limit 1");
//        if(!$scuota){
//            $ucuota=FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pagado' and ind_tipo='pcuota' order by ind_id desc limit 1");
//            $scuota=new stdClass();
//            
//            $scuota->ind_num_correlativo=$ucuota->ind_num_correlativo+1;
//            $scuota->ind_fecha_programada=  FUNCIONES::sumar_dias(30, $ucuota->ind_fecha_programada);
//        }
//        $pagado=  $this->total_pagado($ven_id);
        $urb=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        ?>
        <style>
            .img-boton{
                margin-left: 2px; float: left;cursor: pointer;
            }
            .img-boton:hover{opacity: 0.7}
            .tablaReporte thead tr th{ padding: 0 10px;}
        </style>
        <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
        <script src="js/util.js" type="text/javascript"></script>
        <form id="frm_sentencia" name="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=REFORMULAR&id=<?php echo $venta->ven_id?>" >
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
            <input type="hidden" name="ven_id" id="ven_id" value="<?php echo $venta->ven_id;?>">
            <input type="hidden" name="ven_moneda" id="ven_moneda" value="<?php echo $venta->ven_moneda;?>">
            <input type="hidden" name="interes_anual" id="interes_anual" value="<?php echo $venta->ven_val_interes;?>">
            <input type="hidden" name="form" id="form" value="<?php echo $venta->ven_form;?>">
            <input type="hidden" name="saldo" id="saldo" value="<?php echo $upago->vpag_saldo_final;?>">
            <input type="hidden" name="plazo" id="plazo" value="<?php echo $venta->ven_plazo;?>">
            <input type="hidden" name="cuota" id="cuota" value="<?php echo $venta->ven_cuota;?>">
            <input type="hidden" name="multinivel" id="multinivel" value="<?php echo $venta->ven_multinivel;?>">
            <input type="hidden" name="urbanizacion" id="urbanizacion" value="<?php echo $venta->ven_urb_id;?>">
            <input type="hidden" name="nro_cuota_sig" id="nro_cuota_sig" value="<?php echo $scuota->ind_num_correlativo;?>">
            <input type="hidden" name="fecha_cuota_sig" id="fecha_cuota_sig" value="<?php echo $scuota->ind_fecha_programada;?>">
            <!--<input type="hidden" name="fecha_inicio" id="fecha_inicio" value="<?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor);?>">-->
            <div id="FormSent" style="width:100%">
                <div class="Subtitulo">REFORMULAR PLAN DE PAGOS</div>
                <div id="ContenedorSeleccion">
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Nro. Venta</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_id;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Concepto</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_concepto;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Fecha de Vencimiento</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($venta->ven_ufecha_prog);?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta"><b>Fecha de Reformulacion</b></div>
                        <div id="CajaInput">
                            <?php FORMULARIO::cmp_fecha('fecha_ref');?>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Ultima Fecha Valor</div>
                        <div id="CajaInput">
                            <!--<input type="text" class="caja_texto" id="fecha_inicio" name="fecha_inicio" value="<?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor);?>">-->
                            <input type="hidden" class="caja_texto" id="fecha_inicio" name="fecha_inicio" value="<?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor);?>">
                            <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_valor);?></div>
                        </div>
                        <div class="Etiqueta">Ultima Fecha Pago</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago);?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Saldo Efectivo</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_monto_efectivo;?></div>
                        </div>
                        <div class="Etiqueta">Capital Inc.</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $pagado->incremento;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Capital Pagado</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $pagado->capital;?></div>
                        </div>
                        <div class="Etiqueta">Capital Desc.</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $pagado->descuento;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Saldo</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $upago->vpag_saldo_final;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Cuota</div>
                        <div id="CajaInput">
                            <input type="hidden" id="ven_cuota" name="ven_cuota" value="<?php echo $venta->ven_cuota;?>">
                            <div class="read-input"><?php echo $venta->ven_cuota;?></div>
                        </div>
                        <div class="Etiqueta">Interes Anual</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $venta->ven_val_interes*1;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Plazo</div>
                        <div id="CajaInput">
                            <input type="hidden" id="ven_plazo" name="ven_plazo" value="<?php echo $venta->ven_plazo;?>">
                            <div class="read-input"><?php echo $venta->ven_plazo;?></div>
                        </div>
                        <div class="Etiqueta">Cuotas Pagadas</div>
                        <div id="CajaInput">
                            <div class="read-input"><?php echo $scuota->ind_num_correlativo-1;?></div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta"><b>Descuento</b></div>
                        <div id="CajaInput">
                            <input type="text" name="descuento" id="descuento" value="" autocomplete="off">
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta"><b>Incremento</b></div>
                        <div id="CajaInput">
                            <input type="text" name="incremento" id="incremento" value="" autocomplete="off">
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta">Saldo Final</div>
                        <div id="CajaInput">
                            <input type="hidden" name="saldo_final" id="saldo_final" value="<?php echo $upago->vpag_saldo_final;?>">
                            <div id="txt_saldo_final" class="read-input"><?php echo $upago->vpag_saldo_final;?></div>
                        </div>
                    </div>
                    <div id="box-reformular" style="">
                        <div style="text-align: left; border-bottom: 1px solid #3a3a3a; color: #3a3a3a; font-weight: bold;margin-bottom: 10px;">
                            Adecuar Saldo 
                        </div>
                        <div id="ContenedorDiv">
                            <div id="CajaInput">
                                <span style="float: left; margin-top: 2px;">Definir Plan de Pagos por: &nbsp;</span>
                                <select  id="def_plan_efectivo" name="def_plan_efectivo" data-tipo="efectivo" style="width: 100px;">
                                    <option value="mp">Meses Plazo</option>
                                    <option value="cm">Cuota Mensual</option>
                                    
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div id="CajaInput" name="divCuotaMensual" >
                                <select  id="def_cuota" style="width: 100px; float: left; margin-top: 3px;">
                                    <option value="dcuota">Monto Cuota</option>
                                    <option value="dcapital">Monto Capital</option>
                                </select>
                            </div>
                            <div id="CajaInput" name="divCuotaMensual" style="margin-right: 5px;">
                                <span style="float: left; margin-top: 2px; margin-right: 5px;">Monto Cuota: </span>
                                <input type="text" name="cuota_mensual" id="cuota_mensual" size="8" value="" onKeyPress="return ValidarNumero(event);" autocomplete="off">
                            </div>
                            <div id="CajaInput">
                                <span style="float: left; margin-top: 2px; margin-right: 5px;" >Nro de Cuotas: </span>
                                <input type="text" name="meses_plazo" id="meses_plazo" size="8" value="<?php echo $plazo_rest=$venta->ven_plazo-($scuota->ind_num_correlativo-1);?>" onKeyPress="return ValidarNumero(event);" autocomplete="off">
                            </div>
                            <div id="CajaInput">
                                <span style="float: left; margin-top: 2px; margin-left: 15px; margin-right: 5px;">Fecha de la Primer Cuota: </span>
                                <input class="caja_texto" name="fecha_pri_cuota" id="fecha_pri_cuota" size="12" value="<?php echo FUNCIONES::get_fecha_latina ($scuota->ind_fecha_programada);?>" type="text">
                                <script>
                                    $("#fecha_pri_cuota").mask("99/99/9999");
                                </script>
                            </div>
                            <div id="CajaInput" name="divCuotaMensual" >
                                <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Rango: </span>
                                <select id="ven_rango" name="ven_rango">
                                    <option value="1" <?php echo $venta->ven_rango=='1'?'selected=""':'';?>>Mensual</option>
                                    <option value="2" <?php echo $venta->ven_rango=='2'?'selected=""':'';?>>Bimestral</option>
                                    <option value="3" <?php echo $venta->ven_rango=='3'?'selected=""':'';?>>Trimestral</option>
                                    <option value="4" <?php echo $venta->ven_rango=='4'?'selected=""':'';?>>Cuatrimestral</option>
                                    <option value="6" <?php echo $venta->ven_rango=='6'?'selected=""':'';?>>Semestral</option>
                                </select>
                            </div>
                            <div id="CajaInput" name="divCuotaMensual" >
                                <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Frec.: </span>
                                <select id="ven_frecuencia" name="ven_frecuencia">
                                    <option value="30_dias" <?php echo $venta->ven_frecuencia=='30_dias'?'selected=""':'';?>>Cada 30 dias</option>
                                    <option value="dia_mes" <?php echo $venta->ven_frecuencia=='dia_mes'?'selected=""':'';?>>Mantener el dia</option>
                                </select>
                            </div>
                            <div id="CajaInput">
                                <img id="ver_plan_efectivo" src="imagenes/generar.png" style='margin:0px 0px 0px 5px; cursor: pointer' onclick="javascript:ver_plan_pago();">
                            </div>
                            <div id="CajaInput">
                                <img id="add_cuota_efectivo"src="images/btn_add_detalle.png" style='margin-left: 5px; cursor: pointer' onclick="javascript:datos_fila('efectivo');">
                            </div>
                        </div>
                        <div style="clear: both"></div>
                        <div class="ContenedorDiv" id="plan_manual_efectivo">
                            <table width="96%"   class="tablaReporte" id="tab_plan_efectivo" cellpadding="0" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Nro. Cuota</th>
                                        <th>Fecha de Pago</th>                                                            
                                        <th>Mondeda</th>
                                        <th>Interes</th>
                                        <th>Capital</th>
                                        <th>Monto a Pagar</th>
                                        <th>Saldo</th>
                                        <th></th>
                                    </tr>							
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>
                                            <input type="hidden" id="c_total_efectivo" value="0">
                                            <input type="hidden" id="pag_total_efectivo" value="0">
                                        </td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input class="boton" type="button" onclick="enviar_frm_reformulacion();" value="Guardar" name="">
                                <input class="boton" type="button" onclick="location.href='gestor.php?mod=venta&tarea=ACCEDER';" value="Volver" name="">
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script>
            mask_decimal('#descuento',null);
            mask_decimal('#incremento',null);
            $('#descuento, #incremento').keyup(function (){
                calcular_saldo_final();
            });
            
            function calcular_saldo_final(){
                var desc=$('#descuento').val()*1;
                var inc=$('#incremento').val()*1;
                var saldo=$('#saldo').val()*1;
                var saldo_final=saldo-desc+inc;
                $('#saldo_final').val(saldo_final.toFixed(2));
                $('#txt_saldo_final').text(saldo_final.toFixed(2));
                
            }
            
            $('#fecha_ref').mask('99/99/9999');
            $('#fecha_inicio').mask('99/99/9999');
            $('#def_plan_efectivo').change(function(){
                var def=$(this).val();
                if(def==='mp'){
                    $('#meses_plazo').parent().show();
                    $('#cuota_mensual').parent().hide();
                    $('#cuota_interes').parent().hide();
                    $('#ver_plan_efectivo').show();
                    $('#add_cuota_efectivo').hide();
                    $('#fecha_pri_cuota').prev('span').text('Fecha Pri Cuota: ');
                    $('#plan_manual_efectivo').hide();
                    $('#def_cuota').parent().hide();
                }else if(def==='cm'){
                    $('#meses_plazo').parent().show();
                    $('#cuota_mensual').parent().show();
                    $('#cuota_interes').parent().hide();
                    $('#ver_plan_efectivo').show();
                    $('#add_cuota_efectivo').hide();
                    $('#meses_plazo').prev('span').text('Maximo Plazo: ');
                    $('#cuota_mensual').prev('span').text('Monto Cuota: ');
                    $('#cuota_mensual').prev('span').show();
                    $('#fecha_pri_cuota').prev('span').text('Fecha Pri Cuota: ');
                    $('#plan_manual_efectivo').hide();
                    $('#def_cuota').parent().hide();
                }else if(def==='manual'){
                    $('#meses_plazo').parent().hide();
                    $('#cuota_mensual').parent().show();
                    $('#cuota_interes').parent().show();
                    $('#ver_plan_efectivo').hide();
                    $('#add_cuota_efectivo').show();
                    $('#cuota_mensual').prev('span').hide();
                    $('#fecha_pri_cuota').prev('span').text('Fecha Programada: ');
                    $('#plan_manual_efectivo').show();
                    $('#def_cuota').parent().show();
                }
            });
            
            $('#def_plan_efectivo').trigger('change');
            
            function ver_plan_pago(){
                var saldo_financiar=0;
                var ncuotas=0;
                var fecha_pri_cuota=0;
                var monto_cuota=0;
                var def=$('#def_plan_efectivo option:selected').val();
                if(def==='mp'){
                    ncuotas = $('#meses_plazo').val();
                    monto_cuota = '';
                }else if(def==='cm'){
                    var cu_pagadas=$('#nro_cuota_sig').val()*1-1;
                    ncuotas = ($('#meses_plazo').val()*1)+cu_pagadas;
                    monto_cuota = $('#cuota_mensual').val();
                }
                saldo_financiar=$('#saldo_final').val()*1;
                fecha_pri_cuota =$('#fecha_pri_cuota').val();
                var fecha_inicio =$('#fecha_inicio').val();
                var fp_mysql=fecha_mysql(fecha_pri_cuota);
                var fi_mysql=fecha_mysql(fecha_inicio);
                if(fi_mysql>fp_mysql){
                    $.prompt('La fecha de la Primera cuota no puede ser menor a la Ultima fecha Valor');
                    return;
                }
                
                var rango =$('#ven_rango option:selected').val();
                var frec =$('#ven_frecuencia option:selected').val();
                
                var interes = $('#interes_anual').val();
                var form = $('#form').val();

                if ((ncuotas*1 > 0 || monto_cuota*1 > 0) && saldo_financiar > 0  && fecha_pri_cuota !== '') {
                    var moneda = $('#ven_moneda').val();//document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
                    var par={};
                    par.tarea='plan_pagos';
                    par.saldo_financiar=saldo_financiar;
                    par.monto_total=saldo_financiar;
                    par.meses_plazo=ncuotas;
                    par.cuota_mensual=monto_cuota;
                    par.ven_moneda=moneda;
                    par.nro_inicio=$('#nro_cuota_sig').val();
                    par.fecha_inicio=fecha_inicio;
                    par.fecha_pri_cuota=fecha_pri_cuota;
                    par.interes=interes;
                    par.form=form;
                    par.rango=rango;
                    par.frecuencia=frec;
                    par.ven_id=$('#ven_id').val();
                    par.urbanizacion = $('#urbanizacion').val();
                    par.multinivel = $('#multinivel').val();
                    mostrar_ajax_load();
                    $.post('ajax.php',par,function(resp){
                        ocultar_ajax_load();
                        abrir_popup(resp);
                    });

                } else {
                    $('#tprueba tbody').remove();
                    $.prompt('-La Fecha no debe estar vacia.</br>-Los meses de plazo o la cuota mensual debe ser mayor a cero.', {opacity: 0.8});
                }
                
            }
            var popup=null;
            function abrir_popup(html){
                if(popup!==null){
                    popup.close();
                }
                popup = window.open('about:blank','reportes','left=100,width=900,height=500,top=0,scrollbars=yes');
                var extra='';
                extra+='<html><head><title>Vista Previa</title><head>';
                extra+='<link href=css/estilos.css rel=stylesheet type=text/css />';
                extra+='</head> <body> <div id=imprimir> <div id=status> <p>';
                extra+='<a href=javascript:window.print();>Imprimir</a>  <a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>';
                popup.document.write(extra);
                popup.document.write(html);
                popup.document.write('</center></body></html>');
                popup.document.close();

            }
            
            function enviar_frm_reformulacion(){
                
                var saldo_final=$('#saldo').val()*1;
                
                if(saldo_final<=0){
                    $.prompt("El Saldo Final debe ser Mayor a 0");
                    return false;
                }
                
                var def=$('#def_plan_efectivo option:selected').val();
                var fecha_pri_cuota =$('#fecha_pri_cuota').val();
                var fecha_inicio =$('#fecha_inicio').val();
                var fp_mysql=fecha_mysql(fecha_pri_cuota);
                var fi_mysql=fecha_mysql(fecha_inicio);
                if(fi_mysql>fp_mysql){
                    $.prompt('La fecha de la Primera cuota no puede ser menor a la Ultima fecha Valor');
                    return;
                }
                
                if(def==='mp'){
                    var mp=$('#meses_plazo').val()*1;
                    var fpc=$('#fecha_pri_cuota').val();
                    if(!(mp>0 && fpc!=='')){
                        $.prompt('Revise los datos del credito efectivo:<br> - La meses plazo <br> - Fecha de la primera cuota ');
                        return false;
                    }   
                }else if(def==='cm'){
                    var cm=$('#cuota_mensual').val()*1;
                    var fpc=$('#fecha_pri_cuota').val();
                    if(!(cm>0 && fpc!=='')){
                        $.prompt('Revise los datos del credito efectivo:<br> - La cuota Mensual <br> - Fecha de la primera cuota ');
                        return false;
                    }
                }else if(def==='manual'){
                    var capital_total=$('#c_total_efectivo').val()*1;
                    var saldo=$('#saldo_final').val();
                    if(capital_total!==saldo){
                        $.prompt('en el plan de pagos manual del monto en efectivo falta definir mas cuotas para igualar al monto en efectivo de la venta');
                        return false;
                    }
                }
                
                revisar_ufecha_prog(function(){
                   var fecha=$('#fecha_ref').val();
                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                        ocultar_ajax_load();
                        var dato = JSON.parse(respuesta);
                        if (dato.response === "ok") {
//                            console.info('OK');
                            document.frm_sentencia.submit();
                        } else if (dato.response === "error") {
                            $.prompt(dato.mensaje);
                            return false;
                        }
                    }); 
                });
            }
            
            function revisar_ufecha_prog(funcion){
                var ncuotas=0;                
                var monto_cuota=0;
                var def=$('#def_plan_efectivo option:selected').val();
                if(def==='mp'){
                    ncuotas = $('#meses_plazo').val();
                    monto_cuota = '';
                }else if(def==='cm'){
                    ncuotas = $('#meses_plazo').val();
                    monto_cuota = $('#cuota_mensual').val();
                }
                var saldo_financiar=$('#saldo_final').val()*1;
                
                var rango =$('#ven_rango option:selected').val();
                var frec =$('#ven_frecuencia option:selected').val();
                
                var interes = $('#interes_anual').val();
                var fecha_pri_cuota =$('#fecha_pri_cuota').val();
                var fecha_inicio =$('#fecha_inicio').val();
                
                var moneda = $('#ven_moneda').val();
                var par={};
                par.tarea='rev_ufecha_prog';
                par.saldo_financiar=saldo_financiar;
                par.monto_total=saldo_financiar;
                par.meses_plazo=ncuotas;
                par.cuota_mensual=monto_cuota;
                par.ven_moneda=moneda;
                par.nro_inicio=$('#nro_cuota_sig').val();
                par.fecha_inicio=fecha_inicio;
                par.fecha_pri_cuota=fecha_pri_cuota;
                par.interes=interes;
                par.rango=rango;
                par.frecuencia=frec;
                par.ven_id=$('#ven_id').val();
                mostrar_ajax_load();
                $.post('ajax.php',par,function(resp){
                    var r=JSON.parse(trim(resp));
                    if(r.type==='success'){
                        funcion();
                    }else{
                        ocultar_ajax_load();
                        $.prompt(r.msj);
                    }
                    
                });
                
            }
            
        </script>
        <?php
    }

    
}
?>