<?php

class VENTA_IMPORTAR extends VENTA {

    function VENTA_IMPORTAR() {
        parent::__construct();
    }

    function importar_excel() {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=$_GET[id]");
        if ($venta->ven_estado != 'Pendiente') {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La Importacion no puede realizar ninguna accion por que la Venta no se encuentra en estado Pendiente", "$url", 'Volver');
            return;
        }
        if ($venta->ven_bloqueado) {
            $url = "$this->link?mod=$this->modulo";
            $this->formulario->ventana_volver("La Importacion no puede realizar ninguna accion por que la Venta se encuentra en estado Bloqueado", "$url", 'Volver');
            return;
        }
        $this->barra_opciones($venta, 'IMPORTAR EXCEL');
        echo '<br>';
        $this->formulario->dibujar_titulo("IMPORTACION DE PLAN DE PAGOS Y EXTRACTOS");
        if ($_POST) {
            $acc = $_POST[acc];
            if ($acc == 'guardar') {
                $this->guardar_importar_excel($venta);
            } else {
                $this->verificar_importar_excel($venta);
            }
        } else {
            $this->frm_importar_excel($venta);
        }
    }

    function guardar_importar_excel($venta) {
//        FUNCIONES::print_pre($_POST);
        $archivo = $_POST[dir_archivo];
        $archivo_excel = "../ztmp_archivos/" . $archivo;

        if (!is_file($archivo_excel)) {
            $mensaje = "No existe el archivo a importar";
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
        
        
        require_once 'excel/reader.php';
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('CP1251');
        $data->read($archivo_excel);
        $pagpp = 0;
        $pagex = 1;
        $numfil_pp = $data->sheets[$pagpp]['numRows'];
        $numfil_ex = $data->sheets[$pagex]['numRows'];
        if ($numfil_pp == 0 || $numfil_ex == 0) {
            $mensaje = "No existen plan de pagos en la hoja 1 o no existen pago en la hoja 2 (Hoja 1: $numfil_pp, Hoja 2: $numfil_ex)";
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
        $sw = true;
        $tplan_pagos = array();
        for ($i = 2; $i <= $numfil_pp; $i++) {
            $c = 1;
            $nro_cuota = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
            $fecha_prog = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
            $interes = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
            $capital = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
            $cuota = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
            $monto_cuota = $capital + $interes;
            $saldo = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
            if ($nro_cuota == '') {
                break;
            }
            if ($sw) {
                $saldo_fin = $saldo;
                $sw = false;
            } else {
                $saldo_fin = $saldo_fin - $capital;
            }
            $tplan_pagos[] = (object) array(
                        'nro_cuota' => $nro_cuota,
                        'fecha_prog' => $fecha_prog,
                        'capital' => $capital,
                        'interes' => $interes,
                        'cuota' => $monto_cuota,
            );
        }
        $this->imp_insertar_plan_pagos($venta, $tplan_pagos);
        $pagos=array();
        for ($i = 2; $i <= $numfil_ex; $i++) {
            $c = 1;
            $fecha_pago = trim($data->sheets[$pagex]['cells'][$i][$c++]);
//            $fecha_valor = trim($data->sheets[$pagex]['cells'][$i][$c++]);
            $nro_recibo = trim($data->sheets[$pagex]['cells'][$i][$c++]);
            $interes = trim($data->sheets[$pagex]['cells'][$i][$c++]);
            $capital = trim($data->sheets[$pagex]['cells'][$i][$c++]);
            $cuota = trim($data->sheets[$pagex]['cells'][$i][$c++]);
            $usuario = trim($data->sheets[$pagex]['cells'][$i][$c++]);
            $monto_cuota = $capital + $interes;
            if ($fecha_pago == '') {
                break;
            }
            $pagos[]=(object)array(
                'interes'=> $interes,
                'capital'=> $capital,
                'fecha_pag'=> $fecha_pago,
                'recibo'=> $nro_recibo,
                'usu_import'=> $usuario,
            );
        }
        $this->imp_insertar_pagos($venta, $pagos);
        $observacion=$_POST[observacion];
        $fecha_cre=date('Y-m-d');
        
        $bool=  copy($archivo_excel, "doc_imp/$archivo");

        $sql_ins_vneg="insert into venta_negocio(
                            vneg_tipo,vneg_ven_id,vneg_observacion,vneg_fecha,
                            vneg_estado,vneg_fecha_cre,vneg_usu_cre,vneg_parametros
                        )values(
                            'importacion','$venta->ven_id','$observacion','$fecha_cre',
                            'Activado','$fecha_cre','$_SESSION[id]','{\"archivo\":\"$archivo\"}'
                        )";
        $conec=new ADO();
        $conec->ejecutar($sql_ins_vneg);
        $this->imp_actualizar_venta($venta);
        $url = "$this->link?mod=$this->modulo";
        $this->formulario->ventana_volver("Importacion Realizada exitosamente", "$url", 'Volver','Correcto');
        
    }
    
    function imp_actualizar_venta($venta) {
        $conec = new ADO();
        $upago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_ven_id='$venta->ven_id' and vpag_estado='Activo' order by vpag_id desc limit 0,1");
        if (!$upago) {
            $upago = new stdClass();
            $upago->vpag_fecha_pago = $venta->ven_fecha;
            $upago->vpag_fecha_valor = $venta->ven_fecha;
            $upago->vpag_saldo_final = $venta->ven_monto_efectivo;
        }

        $ucuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");
        if (!$ucuota) {
            $ucuota = new stdClass();
            $ucuota->ind_num_correlativo = 0;
        }

        $pagado = FUNCIONES::total_pagado_venta($venta->ven_id);

        $capital_pag = $pagado->capital;
        $capital_inc = $pagado->incremento;
        $capital_desc = $pagado->descuento;
        $cuota_pag = $ucuota->ind_num_correlativo;

        $_ucuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_num_correlativo>0 order by ind_id desc limit 1");

        $scuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 1");
        $ven_sfecha_prog = $scuota ? $scuota->ind_fecha_programada : '0000-00-00';
        $ven_cuota = $scuota->ind_monto;

        $sql_up = "update venta set 
                            
                            ven_ufecha_pago='$upago->vpag_fecha_pago', 
                            ven_ufecha_valor='$upago->vpag_fecha_valor', 
                            ven_usaldo='$upago->vpag_saldo_final',
                            ven_cuota_pag='$cuota_pag', 
                            ven_capital_pag='$capital_pag',
                            ven_capital_inc='$capital_inc', 
                            ven_capital_desc='$capital_desc',
                            ven_plazo='$_ucuota->ind_num_correlativo',
                            ven_cuota='$ven_cuota',
                            ven_rango='1',
                            ven_ufecha_prog='$_ucuota->ind_fecha_programada', 
                            ven_sfecha_prog='$ven_sfecha_prog'
                    
                where ven_id='$venta->ven_id'";
//        echo "$sql_up;<br>";

        $conec->ejecutar($sql_up, false, false);
    }

    function imp_insertar_pagos($venta,$pagos) {
        
        $ufecha_pago = '0000-00-00';
        $ufecha_valor = '';

        $usaldo = '';
        foreach ($pagos as $tpago) {
            if (true) {
                $rec_pago = new stdClass();

                $rec_pago->pag_capital = $tpago->capital;
                $rec_pago->pag_interes = $tpago->interes;
                $rec_pago->desc_capital = 0;
                $rec_pago->desc_form = 0;
                $rec_pago->pag_form = 0;
                $rec_pago->mora_pagado = 0;
                $rec_pago->mora = 0;
                $rec_pago->fecha = FUNCIONES::get_fecha_mysql($tpago->fecha_pag);

                $params = array(
                    'venta' => $venta,
                    'fecha_pago' => $rec_pago->fecha,
                    'fecha_valor' => $rec_pago->fecha,
                    'pago' => $rec_pago,
                );

                $usaldo = $this->imp_generar_cobro($params);

                $this->imp_pagar_cuota($venta, $tpago->recibo, $tpago->usu_import);

                $ufecha_pago = $rec_pago->fecha;
                $ufecha_valor = $rec_pago->fecha;
                
            }
        }
    }
    
    function imp_pagar_cuota($venta, $recibo, $usu_import) {
        $conec = new ADO();
        $conec->begin_transaccion();
    //        $codigo = $_POST[cob_codigo];
        $cob = FUNCIONES::objeto_bd_sql("select * from venta_cobro where vcob_ven_id='$venta->ven_id' ");

        if (!$cob) {
            $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";

            return false;
        }

        $str_interes_ids = trim($cob->vcob_interes_ids);
        if ($str_interes_ids && $cob->vcob_interes > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_interes_ids) and ind_tipo='pcuota' order by ind_id asc");
            $interes_ids = explode(',', trim($cob->vcob_interes_ids));
            if (count($interes_ids) != count($cuotas)) {
                $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";

                return false;
            }
        }
        $str_capital_ids = trim($cob->vcob_capital_ids);
        if ($str_capital_ids && $cob->vcob_capital > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_capital_ids) and ind_tipo='pcuota' order by ind_id asc");
            $capital_ids = explode(',', trim($cob->vcob_capital_ids));
            if (count($capital_ids) != count($cuotas)) {
                $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";

                return false;
            }
        }
        $str_form_ids = trim($cob->vcob_form_ids);
        if ($str_form_ids && $cob->vcob_form > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_form_ids) and ind_tipo='pcuota' order by ind_id asc");
            $form_ids = explode(',', trim($cob->vcob_form_ids));
            if (count($form_ids) != count($cuotas)) {
                $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";

                return false;
            }
        }
        $str_envio_ids = trim($cob->vcob_envio_ids);
        if ($str_envio_ids && $cob->vcob_envio > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_envio_ids) and ind_tipo='pcuota' order by ind_id asc");
            $envio_ids = explode(',', trim($cob->vcob_envio_ids));
            if (count($envio_ids) != count($cuotas)) {
                $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";

                return false;
            }
        }

        $val_monto = 0;
        $interes = $cob->vcob_interes;

        $saldo_descuento = FUNCIONES::atributo_bd_sql("select sum(val_monto) as campo from beneficiario_vale where val_int_id=$venta->ven_int_id and val_estado='Activo'") * 1;
        $descuento = $val_monto;
        if ($descuento > 0) {
            if ($descuento > $interes || $descuento > $saldo_descuento) {
                $url = "link?mod=modulo&tarea=PAGOS&id=$venta->ven_id";

                return false;
            }
        }

        $fecha_cre = date('Y-m-d H:i:s');
        $usuario_id = $_SESSION[id];
        $fecha_pago = $cob->vcob_fecha_pago;
        $pag_codigo = FUNCIONES::fecha_codigo();
        $nro_recibo = $recibo;

        $monto = $cob->vcob_monto;
        if ($val_monto > 0) {
            $interes = $cob->vcob_interes - $val_monto;
            $monto = $cob->vcob_monto - $val_monto;
        }
        $cob->vcob_interes = $interes;
        $cob->vcob_interes_montos = $interes . '';

        //crear registro venta pago
        $sql_pago = "insert into venta_pago(
                            vpag_ven_id,vpag_codigo,vpag_fecha_pago,vpag_fecha_valor,
                            vpag_int_id,vpag_moneda,vpag_saldo_inicial,vpag_dias_interes,
                            vpag_interes,vpag_capital,vpag_mora,vpag_form,vpag_envio,vpag_monto,vpag_saldo_final,
                            vpag_interes_ids,vpag_capital_ids,vpag_form_ids,vpag_envio_ids,vpag_mora_ids,vpag_mora_con_ids,vpag_mora_gen_ids,
                            vpag_interes_montos,vpag_capital_montos,vpag_form_montos,vpag_envio_montos,vpag_mora_montos,vpag_mora_con_montos,vpag_mora_gen_montos,vpag_mora_gen_dias,
                            vpag_fecha_cre,vpag_usu_cre,vpag_estado,vpag_cob_usu,vpag_cob_codigo,vpag_cob_aut,vpag_recibo,vpag_suc_id,vpag_interes_desc,
                            vpag_val_monto,vpag_usu_import
                        )values(
                            '$venta->ven_id','$pag_codigo','$fecha_pago','$cob->vcob_fecha_valor',
                            '$venta->ven_int_id','$venta->ven_moneda','$cob->vcob_saldo_inicial','$cob->vcob_dias_interes',
                            '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_envio','$monto','$cob->vcob_saldo_final',
                            '$cob->vcob_interes_ids','$cob->vcob_capital_ids','$cob->vcob_form_ids','$cob->vcob_envio_ids','$cob->vcob_mora_ids','$cob->vcob_mora_con_ids','$cob->vcob_mora_gen_ids',
                            '$cob->vcob_interes_montos','$cob->vcob_capital_montos','$cob->vcob_form_montos','$cob->vcob_envio_montos','$cob->vcob_mora_montos','$cob->vcob_mora_con_montos','$cob->vcob_mora_gen_montos','$cob->vcob_mora_gen_dias',
                            '$fecha_cre','$usuario_id','Activo','$cob->vcob_usu_cre','$cob->vcob_codigo','$cob->vcob_aut','$nro_recibo','$_SESSION[suc_id]','$cob->vcob_interes_desc',
                            '$val_monto','$usu_import'
                            )";
        $conec->ejecutar($sql_pago, true, true);
        $pago_id = ADO::$insert_id;

    //        mysql_insert_id
//        include_once 'clases/recibo.class.php';
//        $data_recibo = array(
//            'recibo' => $nro_recibo,
//            'fecha' => $fecha_pago,
//            'monto' => $monto,
//            'moneda' => $venta->ven_moneda,
//            'tabla' => 'venta_pago',
//            'tabla_id' => $pago_id
//        );
//        RECIBO::insertar($data_recibo);

        $data_pago = (object) array(
                    'cob' => $cob,
                    'venta' => $venta,
                    'pago_id' => $pago_id,
                    'fecha_pago' => $fecha_pago,
                    'fecha_cre' => $fecha_cre,
                    'usuario_id' => $usuario_id,
                    'recibo' => $nro_recibo,
                    'pag_codigo' => $pag_codigo,
                    'val_monto' => $val_monto,
        );

        //actualizar interno_deuda
        // *********** PAGAR INTERES        
        $this->imp_pagar_cu_interes($data_pago, $conec);
        // *********** PAGAR CAPITAL        
        $res_cap = (object) $this->imp_pagar_cu_capital($data_pago, $conec);
        //************ PAGAR FORMULARIO        
        $this->imp_pagar_cu_form($data_pago, $conec);
        //****-******* PAGAR ENVIO        
        $this->imp_pagar_cu_envio($data_pago, $conec);
        //************ PAGO MORA        
        $this->imp_pagar_cu_mora($data_pago, $conec);
        //*********************************************

        $ucuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");

        $pagado = $this->imp_total_pagado($venta->ven_id);

        $capital_pag = $pagado->capital;
        $capital_inc = $pagado->incremento;
        $capital_desc = $pagado->descuento;
        $cuota_pag = $ucuota->ind_num_correlativo;

        $scuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 1");
        $ven_sfecha_prog = $scuota ? $scuota->ind_fecha_programada : '0000-00-00';

        $sql_up = "update venta set 
                                ven_ufecha_pago='$fecha_pago', ven_ufecha_valor='$cob->vcob_fecha_valor', 
                                ven_cuota_pag='$cuota_pag', ven_capital_pag='$capital_pag',
                                ven_capital_inc='$capital_inc', ven_capital_desc='$capital_desc', 
                                ven_usaldo='$cob->vcob_saldo_final', ven_sfecha_prog='$ven_sfecha_prog'
                        where ven_id='$venta->ven_id'";

        $conec->ejecutar($sql_up, false, false);

        $sql_del = "delete from venta_cobro where vcob_ven_id='$venta->ven_id' ";
        $conec->ejecutar($sql_del, false, false);

        $exito = $conec->commit();
        if ($exito) {
            
        } else {
//            echo "ERROR AL GUARDAR<BR>";
//            ECHO implode('<br>', $mensajes);
        }
    }

    function imp_pagar_cu_interes($data, &$conec = null) {
        if ($conec == null) {
            $conec = new ADO();
        }
    //        'cob'=>$cob,
    //        'venta'=>$venta,
    //        'pago_id'=>$pago_id,
    //        'fecha_pago'=>$fecha_pago,
    //        'usuario_id'=>$usuario_id,
    //        'recibo'=>$usuario_id,
        $cob = $data->cob;
        $venta = $data->venta;
        $pago_id = $data->pago_id;
        $fecha_pago = $data->fecha_pago;
        $fecha_cre = $data->fecha_cre;
        $usuario_id = $data->usuario_id;
        $recibo = $data->recibo;
        $pag_codigo = $data->pag_codigo;

        $str_interes_ids = trim($cob->vcob_interes_ids);
        if ($str_interes_ids && $cob->vcob_interes > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_interes_ids) and ind_tipo='pcuota' order by ind_id asc");
    //            $vpag_interes = $cob->vcob_interes;
            $interes_montos = explode(',', trim($cob->vcob_interes_montos));
            $interes_ids = explode(',', trim($cob->vcob_interes_ids));
            if (count($interes_ids) == count($cuotas) && count($cuotas) == count($interes_montos)) {
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
            } else {
                $conec->rollback();
                $mensaje = "Interes: Cantidad de cuotas de interes diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";

                return;
            }
        }
    }

    function imp_pagar_cu_capital($data, &$conec = null) {
        if ($conec == null) {
            $conec = new ADO();
        }

        $cob = $data->cob;
        $venta = $data->venta;
        $pago_id = $data->pago_id;
        $fecha_pago = $data->fecha_pago;
        $fecha_cre = $data->fecha_cre;
        $usuario_id = $data->usuario_id;
        $recibo = $data->recibo;
        $pag_codigo = $data->pag_codigo;

        $costo_cub = 0;
        $costo_pag = 0;
        $costo = 0;
        $costo_ids = array();
        $costo_montos = array();
        $str_capital_ids = trim($cob->vcob_capital_ids);
        /* AQUI ME QUEDE... CALCULANDO EL SALDO DEL COSTO DE VENTA.. */
        $saldo_venta = $venta->ven_monto_efectivo - $venta->ven_cuota_inicial;
        $saldo_costo = $venta->ven_costo - $venta->ven_cuota_inicial - $venta->ven_costo_cub;
        if ($str_capital_ids && $cob->vcob_capital > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_capital_ids) and ind_tipo='pcuota' order by ind_id asc");
    //            $vpag_capital = $cob->vcob_capital;
            $capital_montos = explode(',', trim($cob->vcob_capital_montos));
            $capital_ids = explode(',', trim($cob->vcob_capital_ids));
            if (count($capital_ids) == count($cuotas) && count($cuotas) == count($capital_montos)) {
                $is_reformular = false;
                for ($i = 0; $i < count($cuotas); $i++) {
                    $cu = $cuotas[$i];
    //                    $capital_dif = $vpag_capital - ($cu->ind_capital * 1);
                    $capital_pagado = $capital_montos[$i]; //$cu->ind_capital;
                    $ind_estado = 'Pendiente';
                    $saldo_final = 0;
                    if ($capital_pagado . '' == $cu->ind_capital . '') {
                        $ind_estado = 'Pagado';
                        $saldo_final = $cu->ind_saldo;
                    } else {
                        if ($i == 0 && $i == count($cuotas) - 1) {// primer y el ultimo pago
    //                            echo " ENTRO PRIMER IF <br>";
                            $ind_capital_pagado = $cu->ind_capital_pagado + $capital_pagado;
                            if ($ind_capital_pagado . '' == $cu->ind_capital . '') {
                                $ind_estado = 'Pagado';
                                $saldo_final = $cu->ind_saldo;
                            } elseif ($ind_capital_pagado > $cu->ind_capital) {
                                $ind_estado = 'Pagado';
                                $capital_dif = $ind_capital_pagado - ($cu->ind_capital * 1);
                                $saldo_final = $cu->ind_saldo - $capital_dif;
                                $is_reformular = true;
                                $ant_saldo = $cu->ind_saldo;
                                $ant_capital = $cu->ind_capital;
                                $ant_fecha_prog = $cu->ind_fecha_programada;
                                $ant_ant_cuota = $venta->ven_cuota;
                            }
                        } elseif ($i == 0) {// primer pago de captital
    //                            echo " ENTRO SEGUNDO IF <br>";
                            $ind_capital_pagado = $cu->ind_capital_pagado + $capital_pagado;

                            if ($ind_capital_pagado . '' == $cu->ind_capital . '') {
                                $ind_estado = 'Pagado';
                                $saldo_final = $cu->ind_saldo;
                            } elseif ($ind_capital_pagado > $cu->ind_capital) {
                                $conec->rollback();
                                $mensaje = "El Primero de varios Pagos no puede ser mayor a al Capital acordado ";

                                return;
                            }
                        } elseif ($i == count($cuotas) - 1) { /// si es el ultimo pago
    //                            echo " ENTRO TERCER IF <br>";
                            if ($capital_pagado > $cu->ind_capital) { /// pago por demas
                                $ind_estado = 'Pagado';
                                $capital_dif = $capital_pagado - ($cu->ind_capital * 1);
                                $saldo_final = $cu->ind_saldo - $capital_dif;
                                $is_reformular = true;

                                $ant_saldo = $cu->ind_saldo;
                                $ant_capital = $cu->ind_capital;
                                $ant_fecha_prog = $cu->ind_fecha_programada;
                                $ant_ant_cuota = $venta->ven_cuota;
                            } else {// realizo un pago parcial no mueve el estado ni el saldo
                            }
                        } else {
                            $conec->rollback();
                            $mensaje = "Solo el ultimo o el Primer Pago puede ser diferente ";

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
                    $saldo_costo = $venta->ven_costo - $venta->ven_res_anticipo - $venta->ven_monto_intercambio - $venta->ven_cuota_inicial;
                    $costo_pagado = 0;
                    if ($saldo_costo > 0) {
                        if ($cu->ind_num_correlativo == 0) {
                            $costo_pagado = $capital_pagado;
                            $costo_cub = $costo_pagado;
                        } else {
                            if ($cob->vcob_saldo_final <= 0 && $i == count($cuotas) - 1) {
                                $costo_pagado = $venta->ven_costo - $venta->ven_costo_cub - $venta->ven_costo_pag - $costo;
                            } else {
                                $costo_pagado = round(($capital_pagado * $saldo_costo) / $saldo_venta, 2);
                            }
                            $costo_pag+=$costo_pagado;
                        }
                        $costo+=$costo_pagado;
                        $costo_ids[] = $cu->ind_id;
                        $costo_montos[] = $costo_pagado;
                    }
                    if ($costo_pagado > 0) {
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
                $txt_costo_ids = implode(',', $costo_ids);
                $txt_costo_montos = implode(',', $costo_montos);
                $sql_up_pag_costo = "update venta_pago set vpag_costo='$costo', vpag_costo_ids='$txt_costo_ids', vpag_costo_montos='$txt_costo_montos'
                                    where vpag_id=$pago_id";
                $conec->ejecutar($sql_up_pag_costo);
                ///ACTUALIZAR COSTO DE VENTA
                $set_ven_estado = "";
                if ($cob->vcob_saldo_final <= 0) {
                    $set_ven_estado = " ,ven_estado='Pagado'";
                }
                $sql_up_ven_costo = "update venta set ven_costo_cub=ven_costo_cub+$costo_cub, ven_costo_pag=ven_costo_pag+$costo_pag $set_ven_estado
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
                        'tipo_plan' => 'cuota', //$cob->vcob_tipo_plan,
                        'plazo' => $venta->ven_plazo, //$cob->vcob_plazo,
                        'cuota' => $venta->ven_cuota, //$cob->vcob_cuota,
                        'nro_cuota_inicio' => $cob->vcob_nro_cuota_sig,
                        'fecha_inicio' => $cob->vcob_fecha_valor,
                        'fecha_pri_cuota' => $cob->vcob_fecha_pri_cuota,
                        'val_form' => $venta->ven_form,
                        'rango' => $venta->ven_rango,
                        'frecuencia' => $venta->ven_frecuencia,
                    );
//                    reformular_plan($params, $conec);
//                                    echo 'Reformular<br>';
                }
            } else {
                $conec->rollback();
                $mensaje = "Capital: Cantidad de cuotas de capital diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";

                return;
            }
        }
        return array('type' => 'success', 'costo' => $costo);
    }

    function imp_pagar_cu_form($data, &$conec = null) {
        if ($conec == null) {
            $conec = new ADO();
        }
        $cob = $data->cob;
        $venta = $data->venta;
        $pago_id = $data->pago_id;
        $fecha_pago = $data->fecha_pago;
        $fecha_cre = $data->fecha_cre;
        $usuario_id = $data->usuario_id;
        $recibo = $data->recibo;
        $pag_codigo = $data->pag_codigo;

        $str_form_ids = trim($cob->vcob_form_ids);
        if ($str_form_ids && $cob->vcob_form > 0) {
            $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_form_ids) and ind_tipo='pcuota' order by ind_id asc");
    //            $vpag_form = $cob->vcob_form;
            $form_montos = explode(',', trim($cob->vcob_form_montos));
            $form_ids = explode(',', trim($cob->vcob_form_ids));
            if (count($form_ids) == count($cuotas) && count($cuotas) == count($form_montos)) {
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
            } else {
                $conec->rollback();
                $mensaje = "Formulario: Cantidad de cuotas de formulario diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";

                return;
            }
        }
    }

function imp_pagar_cu_envio($data, &$conec = null) {
    if ($conec == null) {
        $conec = new ADO();
    }
    $cob = $data->cob;
    $venta = $data->venta;
    $pago_id = $data->pago_id;
    $fecha_pago = $data->fecha_pago;
    $fecha_cre = $data->fecha_cre;
    $usuario_id = $data->usuario_id;
    $recibo = $data->recibo;
    $pag_codigo = $data->pag_codigo;

    $str_envio_ids = trim($cob->vcob_envio_ids);
    if ($str_envio_ids && $cob->vcob_envio > 0) {
        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_envio_ids) and ind_tipo='pcuota' order by ind_id asc");
//            $vpag_envio = $cob->vcob_envio;
        $envio_montos = explode(',', trim($cob->vcob_envio_montos));
        $envio_ids = explode(',', trim($cob->vcob_envio_ids));
        if (count($envio_ids) == count($cuotas) && count($cuotas) == count($envio_montos)) {
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
        } else {
            $conec->rollback();
            $mensaje = "Formulario: Cantidad de cuotas de envios diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";

            return;
        }
    }
}

function imp_pagar_cu_mora($data, &$conec = null) {
    if ($conec == null) {
        $conec = new ADO();
    }
    $cob = $data->cob;
    $venta = $data->venta;
    $pago_id = $data->pago_id;
    $fecha_pago = $data->fecha_pago;
    $fecha_cre = $data->fecha_cre;
    $usuario_id = $data->usuario_id;
    $recibo = $data->recibo;
    $pag_codigo = $data->pag_codigo;

    //************ GENERAR MORA
    $str_mora_gen_ids = trim($cob->vcob_mora_gen_ids);
    if ($str_mora_gen_ids) {
        $cuotas = FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_id in ($str_mora_gen_ids) and ind_tipo='pcuota' order by ind_id asc");
//            $vpag_mora_gen = $cob->vcob_mora_gen;
        $mora_gen_montos = explode(',', trim($cob->vcob_mora_gen_montos));
        $mora_gen_ids = explode(',', trim($cob->vcob_mora_gen_ids));
        if (count($mora_gen_ids) == count($cuotas) && count($cuotas) == count($mora_gen_montos)) {
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
        } else {
            $conec->rollback();
            $mensaje = "Mora Generada: Cantidad de cuotas de mora_gen diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";

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
        if (count($mora_ids) == count($cuotas) && count($cuotas) == count($mora_montos)) {

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
        } else {
            $conec->rollback();
            $mensaje = "Mora Pago: Cantidad de cuotas de mora diferente a la cantidad que existe en la base de datos o la cantidad e montos es diferente";

            return;
        }
    }
}

    
    function imp_generar_cobro($params, &$conec = null) {
        if ($conec == null) {
            $conec = new ADO();
        }
        $par = (object) $params;
        $venta = $par->venta;
        $pago = $par->pago;
        $cambio_usd = 1;
        if ($venta->ven_moneda == '1') {
            $cambio_usd = 6.96;
        }
        $codigo = FUNCIONES::fecha_codigo();
    //    $fecha_pago = FUNCIONES::sumar_dias(15, $par->fecha_pago);
    //    SUMAR CAPITAL
        $sql_cuotas_all = "select * from interno_deuda 
                                where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and 
                                ind_estado='Pendiente' and ind_capital_pagado < ind_capital and ind_tipo='pcuota' order by ind_id asc";
        $acuotas = FUNCIONES::lista_bd_sql($sql_cuotas_all);
        $_sum_cap = 0;
        $tot_capial = ($pago->pag_capital . '') * 1;
        $cuotas_capital = array();

        if ($tot_capial > 0) {
            foreach ($acuotas as $acu) {
                $cuotas_capital[] = clone $acu;
                $_sum_cap+=($acu->ind_capital . '') * 1 - ($acu->ind_capital_pagado . '') * 1;
    //            echo "$_sum_cap >= $tot_capial <br>";
                if ($_sum_cap >= $tot_capial) {
                    break;
                }
            }
        } else {
            if (count($acuotas) > 0) {
                $cuotas_capital[] = clone $acuotas[0];
            }
        }

    //    FUNCIONES::print_pre($cuotas_capital);
    //    $cuotas_capital = FUNCIONES::lista_bd_sql($sql_cuotas_capital);

        $capital_desc = $pago->desc_capital;
        $capital = 0;
        $capital_ids = array();
        $capital_montos = array();
    //    echo 'Cant '.count($cuotas_capital).' #######<br>';
        $fecha_valor = $cuotas_capital[count($cuotas_capital) - 1]->ind_fecha_programada;
        foreach ($cuotas_capital as $cu) {
            $mcapital = round($cu->ind_capital - $cu->ind_capital_pagado, 2);
    //        echo "$cu->ind_id ****-----*** $mcapital<br>";
            if ($mcapital + $capital >= $pago->pag_capital) {
                $mcapital = round($pago->pag_capital - $capital, 2);
            }
            $capital+=$mcapital;
            $capital_ids[] = $cu->ind_id;
            $capital_montos[] = $mcapital;
        }
    //    echo $capital.'=========================================<br>';
    /// FORMULARIO
        $acu = $acuotas[0];
        $form_desc = $pago->desc_form;
        $form = $pago->pag_form;
        $form_ids = array();
        $form_montos = array();
        if ($form + $form_desc > 0) {
            if (count($acuotas) > 0) {
                $acu = $acuotas[0];
    //            $scuota = FUNCIONES::objeto_bd_sql($sql_cuota);
                $form_ids = array($acu->ind_id);
    //            $form_ids = array($scuota->ind_id);
            } else {
    //            $form_ids = array($cuotas_capital[0]->ind_id);
            }
            $form_montos = array($form);
        }
        /// SUMAR MORA CONCRETADAS PENDIENTES

        $mora = 0;
        $mora_ids = array();
        $mora_montos = array();
        $mora_con_ids = array();
        $mora_con_montos = array();
        $mora_gen_ids = array();
        $mora_gen_montos = array();
        $mora_gen_dias = array();
        if (count($acuotas) > 0) {
            $acu = $acuotas[0];
            $mora = $pago->mora_pagado;
            $mora_ids = array($acu->ind_id);
            $mora_montos = array($mora);
            $mora_con_ids = array($acu->ind_id);
            $mora_con_montos = array($mora);

            $mora_gen_ids = array($acu->ind_id);
            $mora_gen_montos = array($pago->mora);
            $mora_gen_dias = array($pago->mora / 0.5);
        }

    //        $saldo=0;
        // SUMAR INTERES
        $pagado = $this->imp_total_pagado($venta->ven_id);

        $saldo = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;

        $interes_desc = $pago->desc_interes;
        $interes = $pago->pag_interes;
        $interes_ids = array();
        $interes_montos = array();
        if ($interes > 0) {
            if (count($acuotas) > 0) {
                $acu = $acuotas[0];
    //            echo '--------------*************';
    //            FUNCIONES::print_pre($acu);
                $interes_ids = array($acu->ind_id);
            } else {
    //            $interes_ids = array($cuotas_capital[0]->ind_id);
            }
            $interes_montos = array($interes);
        }
    //        echo "$dif_dias*$interes_dia*$saldo=$interes <br>";
        $nsaldo = $saldo - $capital - $capital_desc;
        /*     * ********************************************************************** */

    //        $monto=  round($interes+$capital, 2);

        $monto = round($interes + $capital + $mora + $form, 2);

    //    if ($monto > 0) {
        $txt_interes_ids = implode(',', $interes_ids);
        $txt_capital_ids = implode(',', $capital_ids);
        $txt_mora_ids = implode(',', $mora_ids);
        $txt_mora_con_ids = implode(',', $mora_con_ids);
        $txt_mora_gen_ids = implode(',', $mora_gen_ids);
        $txt_form_ids = implode(',', $form_ids);
        $txt_interes_montos = implode(',', $interes_montos);
        $txt_capital_montos = implode(',', $capital_montos);
        $txt_mora_montos = implode(',', $mora_montos);
        $txt_mora_con_montos = implode(',', $mora_con_montos);
        $txt_mora_gen_montos = implode(',', $mora_gen_montos);
        $txt_mora_gen_dias = implode(',', $mora_gen_dias);
        $txt_form_montos = implode(',', $form_montos);

        $fecha_cre = date('Y-m-d H:i:s');
        $usuario_id = $_SESSION[id];
        $sql_cobro = "insert into venta_cobro(
                                    vcob_ven_id,vcob_codigo,vcob_fecha_pago,vcob_fecha_valor,
                                    vcob_int_id,vcob_moneda,vcob_saldo_inicial,vcob_dias_interes,
                                    vcob_interes,vcob_capital,vcob_mora, vcob_form, vcob_monto,vcob_saldo_final,
                                    vcob_interes_ids,vcob_capital_ids,vcob_form_ids,vcob_mora_ids,vcob_mora_con_ids,vcob_mora_gen_ids,
                                    vcob_interes_montos,vcob_capital_montos,vcob_form_montos,vcob_mora_montos,vcob_mora_con_montos,vcob_mora_gen_montos,vcob_mora_gen_dias,
                                    vcob_fecha_cre,vcob_usu_cre,vcob_aut,vcob_capital_desc,vcob_interes_desc,vcob_form_desc
                                )values(
                                    '$venta->ven_id','$codigo','$par->fecha_pago','$fecha_valor',
                                    '$venta->ven_int_id','$venta->ven_moneda','$saldo','0',
                                    '$interes','$capital','$mora','$form','$monto','$nsaldo',
                                    '$txt_interes_ids','$txt_capital_ids','$txt_form_ids','$txt_mora_ids','$txt_mora_con_ids','$txt_mora_gen_ids',
                                    '$txt_interes_montos','$txt_capital_montos','$txt_form_montos','$txt_mora_montos','$txt_mora_con_montos','$txt_mora_gen_montos','$txt_mora_gen_dias',
                                    '$fecha_cre','$usuario_id','0','$capital_desc','$interes_desc','$form_desc'
                                )";
    //        echo $sql_cobro.';<br>';
        $conec->ejecutar($sql_cobro);
        return $nsaldo;
    //    } else {
    //        return false;
    //    }
    }
    
    function imp_total_pagado($ven_id) {
        $sql_pag = "select sum(ind_interes_pagado)as interes, sum(ind_capital_pagado) as capital, sum(ind_monto_pagado) as monto, sum(ind_capital_desc) as descuento, sum(ind_capital_inc) as incremento 
                            from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id 
                    ";
        $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
        return $pagado;
    }
    

    function imp_insertar_plan_pagos($venta, $tplan_pagos) {
        $conec = new ADO();

        $insert = "insert into interno_deuda(
                    ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_usu_id,ind_moneda,ind_interes,ind_capital,ind_monto,
                    ind_saldo,ind_fecha_programada,ind_estado,ind_concepto,ind_observacion,ind_form,ind_mora,
                    ind_interes_pagado,ind_capital_pagado,ind_monto_pagado,ind_saldo_final,ind_fecha_pago,ind_dias_interes,
                    ind_tipo,ind_fecha_cre,ind_estado_parcial,ind_monto_parcial
                ) values";

        $reg = 0;

        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_id='$venta->ven_int_id'");

        $num = 0;
        $ufecha_prog = '0000-00-00';
        $j = 0;
        $sql_insert = $insert;
        $saldo_final = $venta->ven_monto_efectivo;
        $fecha_cre = date('Y-m-d');
        foreach ($tplan_pagos as $tplan) {
//            if($tplan->ind_num_correlativo!=0 || $tplan->ind_estado=='Pendiente' || $tplan->ind_tipo=='pcontado'){
            if (true) {
                if ($num > 0) {
                    $sql_insert.=',';
                }
                $nro_cuota = $tplan->nro_cuota;

                $capital = $tplan->capital;
                $monto = $tplan->cuota;
//                ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_usu_id,ind_moneda,ind_interes,ind_capital,ind_monto,
//                ind_saldo,ind_fecha_programada,ind_estado,ind_concepto,ind_observacion,ind_form,ind_mora,
//                ind_interes_pagado,ind_capital_pagado,ind_monto_pagado,ind_saldo_final,ind_fecha_pago,ind_dias_interes,
//                ind_tipo,ind_fecha_cre,ind_estado_parcial,ind_monto_parcial
                if (abs($saldo_final - $capital) <= 5) {
                    $capital = $saldo_final;
                }
                $saldo_final = $saldo_final - $capital;
                if ($saldo_final < 0) {
                    $saldo_final = 0;
                }
                $fecha_prog = FUNCIONES::get_fecha_mysql($tplan->fecha_prog);
                $sql_insert.="(
                                'venta',$venta->ven_id,'$nro_cuota',$interno->int_id,'$venta->ven_fecha','$_SESSION[id]',2,0,$capital,$monto,
                                $saldo_final,'$fecha_prog','Pendiente','Cuota Nro $nro_cuota, $venta->ven_concepto','',0,0,
                                0,0,0,0,'0000-00-00',30,
                                'pcuota','$fecha_cre','0',0
                                )";
                $ufecha_prog = $fecha_prog;
                $mcuota = $monto;



                $num++;
            }
            $j++;
        }
        if ($num > 0) {
            $conec->ejecutar($sql_insert, false, false);
        }

        $sql_up = "update venta set
                    ven_ufecha_prog='$ufecha_prog',
                    ven_plazo='$num',
                    ven_cuota='$mcuota',
                    ven_importado='1'
                 where ven_id='$venta->ven_id'
                ";
        $conec->ejecutar($sql_up, false, false);
    }

    function verificar_importar_excel($venta) {
//        FUNCIONES::print_pre($_POST);
        $archivo = $_FILES['archivo'];
//        FUNCIONES::print_pre($archivo);
        $archivo_excel = $_FILES['archivo']['tmp_name'];
        require_once 'excel/reader.php';
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('CP1251');
        $data->read($archivo_excel);
        $pagpp = 0;
        $pagex = 1;
        $numfil_pp = $data->sheets[$pagpp]['numRows'];
        $numfil_ex = $data->sheets[$pagex]['numRows'];

        if ($numfil_pp == 0 || $numfil_ex == 0) {

            $mensaje = "No existen plan de pagos en la hoja 1 o no existen pago en la hoja 2 (Hoja 1: $numfil_pp, Hoja 2: $numfil_ex)";
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
            return;
        }
        ?>
        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=<?php echo $_GET[tarea]; ?>&id=<?php echo $venta->ven_id; ?>" name="frm_sentencia">
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
            <input type="hidden" id="acc" name="acc" value="guardar">
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Planilla</div>
                    <div id="ContenedorSeleccion">     
                        <h2>PLAN DE PAGOS</h2>
                        <table class="tablaReporte" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Nro Cuota</th>
                                    <th>Fecha Prog.</th>
                                    <th>Interes</th>
                                    <th>Capital</th>
                                    <th>Cuota</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php $sw = true; ?>
                        <?php $suma = new stdClass(); ?>
                        <?php for ($i = 2; $i <= $numfil_pp; $i++) { ?>
                            <?php
                            $c = 1;

                            $nro_cuota = trim($data->sheets[$pagpp]['cells'][$i][$c++]);

                            $fecha_prog = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
                            $interes = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
                            $capital = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
                            $cuota = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
                            $monto_cuota = $capital + $interes;
                            $saldo = trim($data->sheets[$pagpp]['cells'][$i][$c++]);
                            if ($nro_cuota == '') {
                                break;
                            }
                            if ($sw) {
                                $saldo_fin = $saldo;
                                $sw = false;
                            } else {
                                $saldo_fin = $saldo_fin - $capital;
                            }
                            ?>
                                    <tr>
                                        <td><?php echo $nro_cuota; ?></td>
                                        <td><?php echo $fecha_prog; ?></td>
                                        <td><?php echo $interes; ?></td>
                                        <td><?php echo $capital; ?></td>
                                        <td><?php echo $monto_cuota; ?></td>
                                        <td><?php echo round($saldo_fin, 2); ?></td>
                                    </tr>
                            <?php
                            $suma->interes+=$interes;
                            $suma->capital+=$capital;
                            $suma->monto_cuota+=$monto_cuota;
                            ?>
                        <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2"></td>
                                    <td ><?php echo round($suma->interes, 2); ?></td>
                                    <td ><?php echo round($suma->capital, 2); ?></td>
                                    <td ><?php echo round($suma->monto_cuota, 2); ?></td>
                                    <td ></td>
                                </tr>
                            </tfoot>
                        </table>
                        <br>
                        
                        <h2>EXTRACTOS</h2>
                        <table class="tablaReporte" width="100%" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Fecha Pago</th>
                                    <!--<th>Fecha Valor</th>-->
                                    <th>Nro. Recibo</th>
                                    <th>Interes</th>
                                    <th>Capital</th>
                                    <th>Monto</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php $sw = true; ?>
                        <?php $sumaex = new stdClass(); ?>
                        <?php for ($i = 2; $i <= $numfil_ex; $i++) { ?>
                        <?php
                        $c = 1;

                        $fecha_pago = trim($data->sheets[$pagex]['cells'][$i][$c++]);
            //            $fecha_valor = trim($data->sheets[$pagex]['cells'][$i][$c++]);
                        $nro_recibo = trim($data->sheets[$pagex]['cells'][$i][$c++]);
                        $interes = trim($data->sheets[$pagex]['cells'][$i][$c++]);
                        $capital = trim($data->sheets[$pagex]['cells'][$i][$c++]);
                        $cuota = trim($data->sheets[$pagex]['cells'][$i][$c++]);
                        $usuario = trim($data->sheets[$pagex]['cells'][$i][$c++]);
                        $monto_cuota = $capital + $interes;
                        if ($fecha_pago == '') {
                            break;
                        }
                        ?>
                        <tr>
                            <td><?php echo $fecha_pago; ?></td>
                            <!--<td><?php echo $fecha_valor; ?></td>-->
                            <td><?php echo $nro_recibo; ?></td>
                            <td><?php echo $interes; ?></td>
                            <td><?php echo $capital; ?></td>
                            <td><?php echo $monto_cuota; ?></td>
                            <td><?php echo $usuario; ?></td>
                        </tr>
                        <?php
                        $sumaex->interes+=$interes;
                        $sumaex->capital+=$capital;
                        $sumaex->monto_cuota+=$monto_cuota;
                        ?>
                        <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2"></td>
                                    <td ><?php echo round($sumaex->interes, 2); ?></td>
                                    <td ><?php echo round($sumaex->capital, 2); ?></td>
                                    <td ><?php echo round($sumaex->monto_cuota, 2); ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <br>
                        <?php if ($sumaex->capital <= $suma->capital) { ?>
                        <?php
            
                        $fecha_cre = date('ymdHis');
                        $nombre_archivo = "$venta->ven_id-$fecha_cre-{$_FILES[archivo][name]}";
                        if (move_uploaded_file($_FILES[archivo][tmp_name], "../ztmp_archivos/$nombre_archivo")) {
                            ?>
                                <input type="hidden" name="dir_archivo" value="<?php echo $nombre_archivo; ?>">
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">Observacion:</div>
                                    <div id="CajaInput">
                                        <textarea id="observacion" name="observacion"></textarea>
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div id="CajaBotones">
                                        <center>
                                            <input class="boton" type="submit" value="Guardar" name="" id="btn_guardar">
                                            <input class="boton" type="button" onclick="javascript:location.href = 'gestor.php?mod=venta';" value="Volver" name="">
                                        </center>
                                    </div>
                                </div>

            <?php } else { ?>
                                <div class="ancho100">
                                    <div class="msError limpiar">No pudo copiarse el archivo</div>
                                </div>
            <?php } ?>
        <?php } else { ?>
                            <div class="ancho100">
                                <div class="msError limpiar">El Capital pagado es mayor al capital programado en el plan de pagos</div>
                            </div>
        <?php } ?>
                        <br><br>
                    </div>
                </div>
            </div>
            <script>

            </script>
        </form>
        <?php
    }

    function frm_importar_excel($venta) {
        ?>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=venta&tarea=<?php echo $_GET[tarea]; ?>&id=<?php echo $venta->ven_id; ?>" name="frm_sentencia">
            <!--<input type="hidden" id="ticket" name="ticket" value="<?php // echo Ticket::pedirTicket();   ?>">-->
            <input type="hidden" id="ven_id" name="ven_id" value="<?php echo $venta->ven_id; ?>">
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos de la Venta</div>
                    <div id="ContenedorSeleccion">                    
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Archivo:</div>
                            <div id="CajaInput">
                                <input type="file" name="archivo" id="archivo"/>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input class="boton" type="submit" value="Verificar" name="" id="btn_guardar">
                                    <input class="boton" type="button" onclick="javascript:location.href = 'gestor.php?mod=venta';" value="Volver" name="">
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>

            </script>
        </form>
        <?php
    }

}
