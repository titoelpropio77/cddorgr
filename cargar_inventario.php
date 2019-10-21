<?php
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

//echo '<pre>';
//print_r(COMISION::$comision_planta);
//echo '</pre>';

//importar_parcela();
//cargar_cuadro();
//cargar_manzano();
//importar_sector();
//corregir_venta_parcela();
//importar_cobrador();

//importar_vendedores();
//importar_vendedores_int();
//importar_clientes();

//importar_detalle();
//actualizar_concepto();
//
//importar_ventas_caidas();
//importar_ventas_intercambio();
//importar_ventas_pagadas();
//importar_venta_pendiente_interes();


//importar_vendedores_pri();
//importar_vendedores_sec();
//importar_vendedores_int_pri();
//importar_vendedores_int_sec();
//importar_clientes();
//importar_venta_contado();
//importar_venta_pendiente();

//importar_venta_pendiente_esp();
//importar_pagos_pendiente();
corregir_comision();
//corregir_descuento();
//corregir_cuota_mensual();
//importar_pagos_contado();


function importar_pagos_contado(){
    $conec = new ADO();
    $sql_reserva = "select * from temp_venta 
                where estado_actual='Vendido' 
                and tipo_de_venta='Contado'
                and codigo_lote in(select replace(ven_codigo,right(ven_codigo,3),'') as codigo from venta where ven_id<=189);";
    $ventas = FUNCIONES::objetos_bd_sql($sql_reserva);

    
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $_venta= FUNCIONES::objeto_bd_sql( "select * from venta where  replace(ven_codigo,right(ven_codigo,3),'')='$venta->codigo_lote';");
        $reserva=trim($venta->reserva)*1;
        $saldo=$_venta->ven_monto;
        if($reserva){
            $fecha_pago=  FUNCIONES::get_fecha_mysql($venta->fecha_reserva);
            $saldo=$saldo-$reserva;
            $sql_reserva = "insert into interno_deuda(
                    ind_int_id,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,
                    ind_estado,ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,
                    ind_dias_interes,ind_interes,ind_capital,ind_monto,ind_saldo,ind_monto_parcial,ind_num_correlativo,
                    ind_interes_pagado, ind_capital_pagado,ind_monto_pagado, ind_saldo_final, ind_tipo
                    )values(
                    '$_venta->ven_int_id','$_venta->ven_moneda','Pago por Venta al Contado - $_venta->ven_concepto','$_venta->ven_fecha','admin',
                    'Pagado','$fecha_pago','venta','$_venta->ven_id','$fecha_pago',
                    '0','0','$reserva','$reserva','$saldo','0','0',
                    '0','$reserva','$reserva','$saldo','pcontado'
                    )";
            echo $sql_reserva.';<br>';
        }
        $cu_ini=trim($venta->cuota_inicial)*1;
        if($cu_ini){
            $fecha_pago=  FUNCIONES::get_fecha_mysql($venta->fecha_cuota_inicial);
            $saldo=$saldo-$cu_ini;
            $sql_cuota_ini= "insert into interno_deuda(
                    ind_int_id,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,
                    ind_estado,ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,
                    ind_dias_interes,ind_interes,ind_capital,ind_monto,ind_saldo,ind_monto_parcial,ind_num_correlativo,
                    ind_interes_pagado, ind_capital_pagado,ind_monto_pagado, ind_saldo_final, ind_tipo
                    )values(
                    '$_venta->ven_int_id','$_venta->ven_moneda','Pago por Venta al Contado - $_venta->ven_concepto','$_venta->ven_fecha','admin',
                    'Pagado','$fecha_pago','venta','$_venta->ven_id','$fecha_pago',
                    '0','0','$cu_ini','$cu_ini','$saldo','0','0',
                    '0','$cu_ini','$cu_ini','$saldo','pcontado'
                    )";
            echo $sql_cuota_ini.';<br>';
        }
        if($venta->estado_2=='Pagado'){
            $sql_update="update venta set ven_estado='Pagado' where ven_id ='$_venta->ven_id'";
            echo $sql_update.';<br>';
        }
        
//        $conec->ejecutar($sql,false);
        
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
}
function corregir_superficie(){
    $conec = new ADO();
    $sql = "select * from temp_venta 
                where estado_actual='Vendido' 
                and (tipo_de_venta='Crédito' or tipo_de_venta='Contado')
                and codigo_lote in(select replace(ven_codigo,right(ven_codigo,3),'') as codigo from venta where ven_id<=189);";
    $ventas = FUNCIONES::objetos_bd_sql($sql);

    
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $_venta= FUNCIONES::objeto_bd_sql( "select * from venta where  replace(ven_codigo,right(ven_codigo,3),'')='$venta->codigo_lote';");        
        $superficie=$venta->m2;
        $ven_metro=$venta->valor_lote/$venta->m2;
        $sql_update="update venta set ven_superficie='$superficie' , ven_metro='$ven_metro' where ven_id='$_venta->ven_id'";
        echo $sql_update.';<br>';        
//        $conec->ejecutar($sql_update);
        
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
}
function corregir_cuota_mensual(){
    $conec = new ADO();
    $sql = "select * from temp_venta 
                where estado_actual='Vendido' 
                and tipo_de_venta='Crédito' 
                and codigo_lote in(select replace(ven_codigo,right(ven_codigo,3),'') as codigo from venta where ven_id<=189);";
    $ventas = FUNCIONES::objetos_bd_sql($sql);

    
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $_venta= FUNCIONES::objeto_bd_sql( "select * from venta where  replace(ven_codigo,right(ven_codigo,3),'')='$venta->codigo_lote';");
        $cuota=FUNCIONES::atributo_bd_sql( "select ind_monto as campo from interno_deuda where  ind_tabla='venta' and ind_tabla_id='$_venta->ven_id' and ind_num_correlativo='1';");        
        
        
        $sql_update="update venta set ven_cuotam_aux='$cuota' where ven_id='$_venta->ven_id'";
        echo $sql_update.';<br>';        
//        $conec->ejecutar($sql_update);
        
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
}
function corregir_descuento(){
    $conec = new ADO();
    $sql = "select * from temp_venta 
                where estado_actual='Vendido' 
                and (tipo_de_venta='Crédito' or tipo_de_venta='Contado')
                and codigo_lote in(select replace(ven_codigo,right(ven_codigo,3),'') as codigo from venta where ven_id<=189);";
    $ventas = FUNCIONES::objetos_bd_sql($sql);

    
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $_venta= FUNCIONES::objeto_bd_sql( "select * from venta where  replace(ven_codigo,right(ven_codigo,3),'')='$venta->codigo_lote';");
        
        $porc_desc=  str_replace('%', '', $venta->descuento);
        $descuento=$venta->valor_lote * ($porc_desc);
        echo "----> $venta->descuento - $porc_desc | $venta->valor_lote * $porc_desc /100 = $descuento | valor:$venta->valor_lote - $descuento  = $venta->precio_final<br>";
        $sql_update="update venta set ven_decuento='$descuento' where ven_id='$_venta->ven_id'";
        echo $sql_update.';<br>';        
        $conec->ejecutar($sql_update);
        
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
}

function corregir_comision(){
    $conec = new ADO();
    $sql = "select * from temp_venta 
                where estado_actual='Vendido' 
                and (tipo_de_venta='Crédito' or tipo_de_venta='Contado')
                and codigo_lote in(select replace(ven_codigo,right(ven_codigo,3),'') as codigo from venta where ven_id<=189);";
    $ventas = FUNCIONES::objetos_bd_sql($sql);

    
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $_venta= FUNCIONES::objeto_bd_sql( "select * from venta where  replace(ven_codigo,right(ven_codigo,3),'')='$venta->codigo_lote';");
        
        
        /*
        $interno_nombre = str_replace("'", "\'", $venta->nombre_cliente);        
        $coop_nombre = str_replace("'", "\'", $venta->comprador_2);
        
        $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$interno_nombre'");
        $coop_int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$coop_nombre'");
        $fecha_ctr = FUNCIONES::get_fecha_mysql($venta->fecha_cuota_inicial);
        $precio = $venta->valor_lote;
        $porc_desc=  str_replace('%', '', $venta->descuento);
        $descuento=$venta->valor_lote * ($porc_desc/100);
        $ven_monto = $venta->precio_final;
//        $ven_tipo = $venta->meses_plazo * 1 == 0 ? 'Contado' : 'Credito';
        $ven_tipo = 'Credito';
        // M1L1AE
        $lot_codigo=$venta->mz.'L'.$venta->lote.$venta->zona.$venta->zona2;
        $id_lote = get_id_lote($lot_codigo);
        $vdo_id = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_1'") * 1;
        $vdo_ext = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_2'") * 1;
//        $monto_vendedor = $ven_monto * ($venta->porc_asesor / 100);
//        $ven_cobrador = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$venta->cobrador'") * 1;
        $_concepto = get_concepto($id_lote);        

        $tabla_id=0;
        $plan_codigo=$venta->mz.'L'.$venta->lote;
        */
        $fecha_ctr = FUNCIONES::get_fecha_mysql($venta->fecha_cuota_inicial);
        $vdo_id = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_1'") * 1;
        $tabla_id=$_venta->ven_id;
        $ven_id=$tabla_id;

        $comision_1=$venta->comision_1_dolar*1;
        if($comision_1){
            $vendedor_1=$vdo_id;
            $sql_comision="insert into comision(
                            com_ven_id, com_vdo_id, com_monto, com_moneda, com_estado, com_fecha_cre, com_usu_id
                            )values (
                            '$ven_id','$vendedor_1','$comision_1','2','Pendiente','$fecha_ctr','admin'
                            )";
            echo "$sql_comision; <br>";
            $conec->ejecutar($sql_comision);
            
            if($venta->estado_comision_1=='pagada'){
                $sql_pago="insert into pago_vendedores(
                                pve_fecha,pve_usu_id,pve_vdo_id, pve_monto, pve_moneda, pve_estado
                                )values (
                                '$fecha_ctr','admin','$vendedor_1','$comision_1','2','Activo'
                                )";
                echo "$sql_pago; <br>";
                $conec->ejecutar($sql_pago);
            }
        }
        
        $comision_2=$venta->comision_2_dolar*1;
        if($comision_2){
            
            if($venta->vendedor_2=='CRISTIAN MARCELO GRAGEDA MONTAÑO inmobiliaria'){
                $venta->vendedor_2='Cristian Marcelo Grageda Montaño';
            }
            
            $vendedor_2=FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and concat(int_nombre,' ',int_apellido)='$venta->vendedor_2'") * 1;
            $sql_comision="insert into comision(
                            com_ven_id, com_vdo_id, com_monto, com_moneda, com_estado, com_fecha_cre, com_usu_id
                            )values (
                            '$ven_id','$vendedor_2','$comision_2','2','Pendiente','$fecha_ctr','admin'
                            )";
            echo "$sql_comision; <br>";
            $conec->ejecutar($sql_comision);
            if($venta->estado_comision_2=='pagada'){
                $sql_pago="insert into pago_vendedores(
                                pve_fecha,pve_usu_id,pve_vdo_id, pve_monto, pve_moneda, pve_estado
                                )values (
                                '$fecha_ctr','admin','$vendedor_2','$comision_2','2','Activo'
                                )";
                echo "$sql_pago; <br>";
                $conec->ejecutar($sql_pago);
            }
        }
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
}

function corregir_venta_parcela(){
    $conec = new ADO();
    $sql="select * from venta";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $parc_id=get_parcela($venta->ven_codigo);
        $concepto=get_concepto($parc_id);
        $sql_update="update venta set ven_parc_id='$parc_id', ven_concepto='$concepto' where ven_id='$venta->ven_id'";
        $conec->ejecutar($sql_update);
//        echo $sql_update.';<br>';
        $cuotas=  FUNCIONES::objetos_bd_sql("select * from interno_deuda where ind_tabla_id='$venta->ven_id'");
        for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
            $cuota = $cuotas->get_objeto();
            $nro=$cuota->ind_num_correlativo;
            if($nro==0){
                $ind_concepto= "Cuota Inicial - $concepto";
            }else{
                $ind_concepto= "Cuota Nro. $nro - $concepto";
            }
            $sql_update="update interno_deuda set ind_concepto='$ind_concepto' where ind_id='$cuota->ind_id'";
            $conec->ejecutar($sql_update);
//            echo $sql_update.';<br>';
            $cuotas->siguiente();
        }        
//        $conec->ejecutar($sql_update);
        $ventas->siguiente();
//        break;
    }    
}

function get_parcela($folio){
    $sql = "select * from tmp_venta where folio='$folio'";
    $objeto=  FUNCIONES::objeto_bd_sql($sql);
    $sector = trim(str_replace("'", "\'", $objeto->sector));
    $manzana = trim(str_replace("'", "\'", $objeto->manzana));
    $cuadro = trim(str_replace("'", "\'", $objeto->cuadro));
    $parcela = trim(str_replace("'", "\'", $objeto->parcela));
    if (trim($sector == ''))
        $sector = '-1';
    if (trim($manzana == ''))
        $manzana = '-1';
    if (trim($cuadro == ''))
        $cuadro = '-1';
    if (trim($parcela == ''))
        $parcela = '-1';
    $sector=  get_sector($sector);
    $sql_campo = "select sec_id as campo from sector where sec_nro='$sector'";
    $sec_id = FUNCIONES::atributo_bd_sql($sql_campo);
    $sql_campo = "select man_id as campo from manzano where man_nro='$manzana' and man_sec_id='$sec_id'";
    $man_id = FUNCIONES::atributo_bd_sql($sql_campo);
    $sql_campo = "select cua_id as campo from cuadro where cua_nro='$cuadro' and cua_man_id='$man_id' and cua_sec_id='$sec_id'";
    $cua_id = FUNCIONES::atributo_bd_sql($sql_campo);
    $sql_campo = "select parc_id as campo from parcela where parc_nro='$parcela' and parc_cua_id='$cua_id' and parc_man_id='$man_id' and parc_sec_id='$sec_id'";
    $parc_id = FUNCIONES::atributo_bd_sql($sql_campo);
    return $parc_id;
    
}

function importar_ventas_intercambio() {
    $conec = new ADO();
    $sql = "select * from tmp_venta where 
            estado='intercambio'  and producto_o_servicio='PARCELA' and interes=0";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    echo $ventas->get_num_registros();

    $sql_insert_venta = "insert into venta(
                    ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_valor,ven_decuento,ven_monto,ven_estado,
                    ven_usu_id,ven_tipo,ven_parc_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,
                    ven_cuotam_aux,ven_interes,ven_valor_form,ven_co_propietario,ven_rango_mes,ven_res_anticipo,
                    ven_res_id,ven_codigo,ven_vdo_id,ven_fecha_cobro,ven_porc_vendedor,ven_monto_vendedor,
                    ven_pagado_vendedor,ven_niveles,ven_bobeda,ven_cobrador,ven_producto,ven_nro_contrato,ven_concepto, ven_saldo_cobrar
                    ) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $interno_nombre = str_replace("'", "\'", $venta->cliente_o_titular);
        $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$interno_nombre'");
        $fecha_ctr = FUNCIONES::get_fecha_mysql($venta->fecha_contrato);
        $ven_monto = $venta->precio - $venta->descuento;
        $ven_tipo = $venta->meses_plazo * 1 == 0 ? 'Contado' : 'Credito';
        $id_parcela = get_id_parcela($venta->sector, $venta->manzana, $venta->cuadro, $venta->parcela);
        $vdo_id = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->asesor'") * 1;
        $monto_vendedor = $ven_monto * ($venta->porc_asesor / 100);
        $ven_cobrador = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$venta->cobrador'") * 1;
        $_concepto = get_concepto($id_parcela);
        $sql_insert = $sql_insert_venta . "(
                    '$int_id','$fecha_ctr','00:00:00','2','$venta->precio','$venta->descuento','$ven_monto','Intercambio',
                    'admin','$ven_tipo','$id_parcela','$venta->cuota_inicial','$venta->meses_plazo','$venta->observaciones',
                    '$venta->cuota_mensual','$venta->interes','0','0','0','0',
                    '0','$venta->folio','$vdo_id','$venta->fecha_cobranza','$venta->porc_asesor','$monto_vendedor',
                    '$venta->asesor_a_cta','$venta->niveles','$venta->boveda','$ven_cobrador','','$venta->nro_contrato','$_concepto','$venta->saldo_por_cobrar')";
//        echo $sql_insert.";<br><br>";
        $conec->ejecutar($sql_insert);
        $sql = "select * from tmp_detalle where folio='$venta->folio' order by id";
        $cuotas = FUNCIONES::objetos_bd_sql($sql);
        $sql_insert_detalle = "insert into interno_deuda(
                    ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_estado,
                    ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,
                    ind_capital,ind_saldo,ind_monto_parcial,ind_valor_form,ind_num_correlativo,
                    ind_estado_mora,ind_estado_parcial,ind_nro_recibo,ind_monto_pagado,
                    ind_monto_acumulado,ind_monto_saldo,ind_monto_vendedor
        ) values";

        for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
            $cuota = $cuotas->get_objeto();
            $ind_capital = $cuota->monto_cuota;
            $ind_interes = 0;
            $ind_monto = $ind_capital + $ind_interes;
            if ($cuota->nro_cuota * 1 == 0) {
                $ind_concepto = "Cuota inicial - $_concepto";
            } else {
                $ind_concepto = "Cuota Nro $cuota->nro_cuota - $_concepto";
            }
            $estado = $cuota->fecha_pago != '' ? 'Pagado' : 'Pendiente';
            $f_pago = $cuota->fecha_pago != '' ? FUNCIONES::get_fecha_mysql($cuota->fecha_pago) : '0000-00-00';
            $tabla_id = get_id_venta($cuota->folio);
            $fecha_prog = FUNCIONES::get_fecha_mysql($cuota->fecha_ven);
            $interes = $cuota->interes * 1;
            $capital = $cuota->monto_cuota * 1;
            $saldo_deudor = $cuota->saldo_deudor * 1;
            $sql_insert = $sql_insert_detalle . "(
                '$int_id','$ind_monto','2','$ind_concepto','$fecha_ctr','admin','$estado',
                '$f_pago','venta','$tabla_id','$fecha_prog','$interes',
                '$capital','$saldo_deudor','0','0','$cuota->nro_cuota',
                'no','listo','$cuota->nro_rec','$cuota->monto_cob',
                '$cuota->acumulado','$cuota->saldo_deudor','$cuota->monto_asesor'
                )";
//            echo $sql_insert.";<br><br>";
            $conec->ejecutar($sql_insert);
            $cuotas->siguiente();
        }
        $ventas->siguiente();
//        if($i>2){
//            break;
//        }        
    }
    echo 'ok';
}

function importar_ventas_caidas() {
    $conec = new ADO();
    $sql = "select * from tmp_venta where 
            estado='retenido'  and producto_o_servicio='PARCELA' and interes=0";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    echo $ventas->get_num_registros();

    $sql_insert_venta = "insert into venta(
                    ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_valor,ven_decuento,ven_monto,ven_estado,
                    ven_usu_id,ven_tipo,ven_parc_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,
                    ven_cuotam_aux,ven_interes,ven_valor_form,ven_co_propietario,ven_rango_mes,ven_res_anticipo,
                    ven_res_id,ven_codigo,ven_vdo_id,ven_fecha_cobro,ven_porc_vendedor,ven_monto_vendedor,
                    ven_pagado_vendedor,ven_niveles,ven_bobeda,ven_cobrador,ven_producto,ven_nro_contrato,ven_concepto, ven_saldo_cobrar
                    ) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $interno_nombre = str_replace("'", "\'", $venta->cliente_o_titular);
        $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$interno_nombre'");
        $fecha_ctr = FUNCIONES::get_fecha_mysql($venta->fecha_contrato);
        $ven_monto = $venta->precio - $venta->descuento;
        $ven_tipo = $venta->meses_plazo * 1 == 0 ? 'Contado' : 'Credito';
        $id_parcela = get_id_parcela($venta->sector, $venta->manzana, $venta->cuadro, $venta->parcela);
        $vdo_id = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->asesor'") * 1;
        $monto_vendedor = $ven_monto * ($venta->porc_asesor / 100);
        $ven_cobrador = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$venta->cobrador'") * 1;
        $_concepto = get_concepto($id_parcela);
        $sql_insert = $sql_insert_venta . "(
                    '$int_id','$fecha_ctr','00:00:00','2','$venta->precio','$venta->descuento','$ven_monto','Retenido',
                    'admin','$ven_tipo','$id_parcela','$venta->cuota_inicial','$venta->meses_plazo','$venta->observaciones',
                    '$venta->cuota_mensual','$venta->interes','0','0','0','0',
                    '0','$venta->folio','$vdo_id','$venta->fecha_cobranza','$venta->porc_asesor','$monto_vendedor',
                    '$venta->asesor_a_cta','$venta->niveles','$venta->boveda','$ven_cobrador','','$venta->nro_contrato','$_concepto','$venta->saldo_por_cobrar')";
//        echo $sql_insert.";<br><br>";
        $conec->ejecutar($sql_insert);
        $sql = "select * from tmp_detalle where folio='$venta->folio' order by id";
        $cuotas = FUNCIONES::objetos_bd_sql($sql);
        $sql_insert_detalle = "insert into interno_deuda(
                    ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_estado,
                    ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,
                    ind_capital,ind_saldo,ind_monto_parcial,ind_valor_form,ind_num_correlativo,
                    ind_estado_mora,ind_estado_parcial,ind_nro_recibo,ind_monto_pagado,
                    ind_monto_acumulado,ind_monto_saldo,ind_monto_vendedor
        ) values";

        for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
            $cuota = $cuotas->get_objeto();
            $ind_capital = $cuota->monto_cuota;
            $ind_interes = 0;
            $ind_monto = $ind_capital + $ind_interes;
            if ($cuota->nro_cuota * 1 == 0) {
                $ind_concepto = "Cuota inicial - $_concepto";
            } else {
                $ind_concepto = "Cuota Nro $cuota->nro_cuota - $_concepto";
            }
            $estado = $cuota->fecha_pago != '' ? 'Pagado' : 'Pendiente';
            $f_pago = $cuota->fecha_pago != '' ? FUNCIONES::get_fecha_mysql($cuota->fecha_pago) : '0000-00-00';
            $tabla_id = get_id_venta($cuota->folio);
            $fecha_prog = FUNCIONES::get_fecha_mysql($cuota->fecha_ven);
            $interes = $cuota->interes * 1;
            $capital = $cuota->monto_cuota * 1;
            $saldo_deudor = $cuota->saldo_deudor * 1;
            $sql_insert = $sql_insert_detalle . "(
                '$int_id','$ind_monto','2','$ind_concepto','$fecha_ctr','admin','$estado',
                '$f_pago','venta','$tabla_id','$fecha_prog','$interes',
                '$capital','$saldo_deudor','0','0','$cuota->nro_cuota',
                'no','listo','$cuota->nro_rec','$cuota->monto_cob',
                '$cuota->acumulado','$cuota->saldo_deudor','$cuota->monto_asesor'
                )";
//            echo $sql_insert.";<br><br>";
            $conec->ejecutar($sql_insert);
            $cuotas->siguiente();
        }
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
}

function importar_ventas_pagadas() {
    $conec = new ADO();
    $sql = "select * from tmp_venta where 
            estado='pagado'  and producto_o_servicio='PARCELA'";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    echo $ventas->get_num_registros();

    $sql_insert_venta = "insert into venta(
                    ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_valor,ven_decuento,ven_monto,ven_estado,
                    ven_usu_id,ven_tipo,ven_parc_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,
                    ven_cuotam_aux,ven_interes,ven_valor_form,ven_co_propietario,ven_rango_mes,ven_res_anticipo,
                    ven_res_id,ven_codigo,ven_vdo_id,ven_fecha_cobro,ven_porc_vendedor,ven_monto_vendedor,
                    ven_pagado_vendedor,ven_niveles,ven_bobeda,ven_cobrador,ven_producto,ven_nro_contrato,ven_concepto, ven_saldo_cobrar
                    ) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $interno_nombre = str_replace("'", "\'", $venta->cliente_o_titular);
        $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$interno_nombre'");
        $fecha_ctr = FUNCIONES::get_fecha_mysql($venta->fecha_contrato);
        $ven_monto = $venta->precio - $venta->descuento;
        $ven_tipo = $venta->meses_plazo * 1 == 0 ? 'Contado' : 'Credito';
        $id_parcela = get_id_parcela($venta->sector, $venta->manzana, $venta->cuadro, $venta->parcela);
        $vdo_id = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->asesor'") * 1;
        $monto_vendedor = $ven_monto * ($venta->porc_asesor / 100);
        $ven_cobrador = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$venta->cobrador'") * 1;
        $_concepto = get_concepto($id_parcela);
        $sql_insert = $sql_insert_venta . "(
                    '$int_id','$fecha_ctr','00:00:00','2','$venta->precio','$venta->descuento','$ven_monto','Pagado',
                    'admin','$ven_tipo','$id_parcela','$venta->cuota_inicial','$venta->meses_plazo','$venta->observaciones',
                    '$venta->cuota_mensual','$venta->interes','0','0','0','0',
                    '0','$venta->folio','$vdo_id','$venta->fecha_cobranza','$venta->porc_asesor','$monto_vendedor',
                    '$venta->asesor_a_cta','$venta->niveles','$venta->boveda','$ven_cobrador','','$venta->nro_contrato','$_concepto','$venta->saldo_por_cobrar')";
        echo $sql_insert.";<br><br>";
        $conec->ejecutar($sql_insert);
        $sql = "select * from tmp_detalle where folio='$venta->folio' order by id";
        $cuotas = FUNCIONES::objetos_bd_sql($sql);
        $sql_insert_detalle = "insert into interno_deuda(
                    ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_estado,
                    ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,
                    ind_capital,ind_saldo,ind_monto_parcial,ind_valor_form,ind_num_correlativo,
                    ind_estado_mora,ind_estado_parcial,ind_nro_recibo,ind_monto_pagado,
                    ind_monto_acumulado,ind_monto_saldo,ind_monto_vendedor
        ) values";

        for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
            $cuota = $cuotas->get_objeto();
            $ind_capital = $cuota->monto_cuota;
            $ind_interes = 0;
            $ind_monto = $ind_capital + $ind_interes;
            if ($cuota->nro_cuota * 1 == 0) {
                $ind_concepto = "Cuota inicial - $_concepto";
            } else {
                $ind_concepto = "Cuota Nro $cuota->nro_cuota - $_concepto";
            }
            $estado = $cuota->fecha_pago != '' ? 'Pagado' : 'Pendiente';
            $f_pago = $cuota->fecha_pago != '' ? FUNCIONES::get_fecha_mysql($cuota->fecha_pago) : '0000-00-00';
            $tabla_id = get_id_venta($cuota->folio);
            $fecha_prog = FUNCIONES::get_fecha_mysql($cuota->fecha_ven);
            $interes = $cuota->interes * 1;
            $capital = $cuota->monto_cuota * 1;
            $saldo_deudor = $cuota->saldo_deudor * 1;
            $sql_insert = $sql_insert_detalle . "(
                '$int_id','$ind_monto','2','$ind_concepto','$fecha_ctr','admin','$estado',
                '$f_pago','venta','$tabla_id','$fecha_prog','$interes',
                '$capital','$saldo_deudor','0','0','$cuota->nro_cuota',
                'no','listo','$cuota->nro_rec','$cuota->monto_cob',
                '$cuota->acumulado','$cuota->saldo_deudor','$cuota->monto_asesor'
                )";
//            echo $sql_insert.";<br><br>";
            $conec->ejecutar($sql_insert);
            $cuotas->siguiente();
        }
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
}

function actualizar_concepto() {
    $conec = new ADO();
    $sql = "select * from venta where ven_concepto is null";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $ven_concepto = get_concepto($venta->ven_parc_id);
        $sql = "update venta set ven_concepto='$ven_concepto' where ven_id='$venta->ven_id'";
//        echo $sql.';<br>';
        $conec->ejecutar($sql);
        $ventas->siguiente();
    }
}

function importar_detalle() {
    $conec = new ADO();
    $sql = "select * from venta";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $sql = "select * from tmp_detalle where folio='$venta->ven_codigo' order by id";
        $cuotas = FUNCIONES::objetos_bd_sql($sql);
        $sql_insert_detalle = "insert into interno_deuda(
                    ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_estado,
                    ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,
                    ind_capital,ind_saldo,ind_monto_parcial,ind_valor_form,ind_num_correlativo,
                    ind_estado_mora,ind_estado_parcial,ind_nro_recibo,ind_monto_pagado,
                    ind_monto_acumulado,ind_monto_saldo,ind_monto_vendedor
        ) values";
        $_concepto = get_concepto($venta->ven_parc_id);
        for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
            $cuota = $cuotas->get_objeto();
            $ind_capital = $cuota->monto_cuota;
            $ind_interes = 0;
            $ind_monto = $ind_capital + $ind_interes;
            if ($cuota->nro_cuota * 1 == 0) {
                $ind_concepto = "Cuota inicial - $_concepto";
            } else {
                $ind_concepto = "Cuota Nro $cuota->nro_cuota - $_concepto";
            }
            $estado = $cuota->fecha_pago != '' ? 'Pagado' : 'Pendiente';
            $f_pago = $cuota->fecha_pago != '' ? FUNCIONES::get_fecha_mysql($cuota->fecha_pago) : '0000-00-00';
            $tabla_id = get_id_venta($cuota->folio);
            $fecha_prog = FUNCIONES::get_fecha_mysql($cuota->fecha_ven);
            $interes = $cuota->interes * 1;
            $capital = $cuota->monto_cuota * 1;
            $saldo_deudor = $cuota->saldo_deudor * 1;
            $sql_insert = $sql_insert_detalle . "(
                '$venta->ven_int_id','$ind_monto','2','$ind_concepto','$venta->ven_fecha','admin','$estado',
                '$f_pago','venta','$tabla_id','$fecha_prog','$interes',
                '$capital','$saldo_deudor','0','0','$cuota->nro_cuota',
                'no','listo','$cuota->nro_rec','$cuota->monto_cob',
                '$cuota->acumulado','$cuota->saldo_deudor','$cuota->monto_asesor'
                )";
//            echo $sql_insert.";<br><br>";
            $conec->ejecutar($sql_insert);
            $cuotas->siguiente();
        }
        $ventas->siguiente();
//        break;  
    }
    echo 'ok';
}

function importar_venta_pendiente_interes() {
    $conec = new ADO();
    $sql = "select * from tmp_venta where producto_o_servicio='parcela' and interes>0 and estado='activo'";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    echo $ventas->get_num_registros();
    
    $sql_insert_venta = "insert into venta(
                    ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_valor,ven_decuento,ven_monto,ven_estado,
                    ven_usu_id,ven_tipo,ven_parc_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,
                    ven_cuotam_aux,ven_interes,ven_valor_form,ven_co_propietario,ven_rango_mes,ven_res_anticipo,
                    ven_res_id,ven_codigo,ven_vdo_id,ven_fecha_cobro,ven_porc_vendedor,ven_monto_vendedor,
                    ven_pagado_vendedor,ven_niveles,ven_bobeda,ven_cobrador,ven_producto,ven_nro_contrato,ven_concepto, ven_valor_interes
                    ) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $interno_nombre = str_replace("'", "\'", $venta->cliente_o_titular);
        $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$interno_nombre'");
        $fecha_ctr = FUNCIONES::get_fecha_mysql($venta->fecha_contrato);
        $ven_monto = $venta->precio - $venta->descuento;
        $ven_tipo = $venta->meses_plazo * 1 == 0 ? 'Contado' : 'Credito';
        $id_parcela = get_id_parcela($venta->sector, $venta->manzana, $venta->cuadro, $venta->parcela);
        $vdo_id = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->asesor'") * 1;
        $monto_vendedor = $ven_monto * ($venta->porc_asesor / 100);
        $ven_cobrador = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$venta->cobrador'") * 1;
        $_concepto = get_concepto($id_parcela);
        $sql_insert = $sql_insert_venta . "(
                    '$int_id','$fecha_ctr','00:00:00','2','$venta->precio','$venta->descuento','$ven_monto','Pendiente',
                    'admin','$ven_tipo','$id_parcela','$venta->cuota_inicial','$venta->meses_plazo','$venta->observaciones',
                    '$venta->cuota_mensual','si','0','0','0','0',
                    '0','$venta->folio','$vdo_id','$venta->fecha_cobranza','$venta->porc_asesor','$monto_vendedor',
                    '$venta->asesor_a_cta','$venta->niveles','$venta->boveda','$ven_cobrador','','$venta->nro_contrato','$_concepto','$venta->interes')";
//        echo $sql_insert.";<br><br>";
        $conec->ejecutar($sql_insert);
        $sql = "select * from tmp_detalle where folio='$venta->folio' order by id";
        $cuotas = FUNCIONES::objetos_bd_sql($sql);
        $sql_insert_detalle = "insert into interno_deuda(
                    ind_int_id,ind_monto,ind_interes,ind_capital,ind_saldo,ind_moneda,
                    ind_concepto,ind_fecha,ind_usu_id,ind_estado,
                    ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,
                    ind_monto_parcial,ind_valor_form,ind_num_correlativo,
                    ind_estado_mora,ind_estado_parcial,ind_nro_recibo,ind_monto_pagado,
                    ind_monto_acumulado,ind_monto_saldo,ind_monto_vendedor
        ) values";
//        echo "$venta->precio - $venta->interes"."<br>";
        $_interes=$venta->interes/12;
        $_saldo=$venta->precio;
        echo "$_saldo - $venta->interes"."<br>";            
        for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
            $cuota = $cuotas->get_objeto();
            $ind_monto=$cuota->monto_cob;
            if($ind_monto<=0){                
                break;
            }
            
            if ($cuota->nro_cuota * 1 == 0) {
                $ind_concepto = "Cuota inicial: $_concepto";                
                $ind_interes = 0;
                $ind_capital = $cuota->monto_cob;
                
            } else {
                $ind_concepto = "Cuota Nro $cuota->nro_cuota: $_concepto";                
                $ind_interes = round($_saldo*($_interes/100), 2);
//                echo "$_saldo * $_interes/100=$ind_interes"." ";
                $ind_capital = round($ind_monto-$ind_interes, 2);
                
            }
            $_saldo=$_saldo-$ind_capital;
            
            $estado = $cuota->fecha_pago != '' ? 'Pagado' : 'Pendiente';
            $f_pago = $cuota->fecha_pago != '' ? FUNCIONES::get_fecha_mysql($cuota->fecha_pago) : '0000-00-00';
            $tabla_id = get_id_venta($cuota->folio);
            $fecha_prog = FUNCIONES::get_fecha_mysql($cuota->fecha_ven);
//            $interes = $cuota->interes * 1;
//            $capital = $cuota->monto_cuota * 1;            
            $sql_insert = $sql_insert_detalle . "(
                '$int_id','$ind_monto','$ind_interes','$ind_capital','$_saldo','2',
                '$ind_concepto','$fecha_ctr','admin','$estado',
                '$f_pago','venta','$tabla_id','$fecha_prog',
                '0','0','$cuota->nro_cuota',
                'no','listo','$cuota->nro_rec','$cuota->monto_cob',
                '$cuota->acumulado','$cuota->saldo_deudor','$cuota->monto_asesor'
                )";
            if($estado=='Pagado'){
//                echo '<span style="color:#ff0000;">'.$sql_insert."</span>".";<br><br>";
            }
            $conec->ejecutar($sql_insert);
            $cuotas->siguiente();
        }
//        echo "<br><br> $_saldo <br><br>";
        $data=array(
            'interes_anual'=>$venta->interes,
            'saldo'=>$_saldo,
            'fecha_pri_cuota'=>FUNCIONES::get_fecha_mysql($cuota->fecha_ven),
            'cuota'=>$venta->cuota_mensual,
            'tipo'=>'cuota'
        );
        $listado=  FUNCIONES::plan_de_pagos($data);
        
        foreach ($listado as $fila) {
//            $sql = "insert into interno_deuda
//                    (ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_tabla,ind_tabla_id,
//                    ind_fecha_programada,ind_interes,ind_capital,ind_saldo,ind_estado,ind_num_correlativo)
//                    values(
//                    '$params->int_id','$fila->monto','2','Cuota Nro. $fila->nro_cuota - $params->concepto','$params->fecha','$params->usuario','$params->tabla','$params->tabla_id',
//                    '$fila->fecha','$fila->interes','$fila->capital','$fila->saldo','Pendiente','$fila->nro_cuota'
//                    )";
//            $_x="ind_int_id,ind_monto,ind_interes,ind_capital,ind_saldo,ind_moneda,
//                    ind_concepto,ind_fecha,ind_usu_id,ind_estado,
//                    ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,
//                    ind_monto_parcial,ind_valor_form,ind_num_correlativo,
//                    ind_estado_mora,ind_estado_parcial,ind_nro_recibo,ind_monto_pagado,
//                    ind_monto_acumulado,ind_monto_saldo,ind_monto_vendedor";
            $sql_insert = $sql_insert_detalle . "(
                '$int_id','$fila->monto','$fila->interes','$fila->capital','$fila->saldo','2',
                'Cuota Nro $fila->nro_cuota: $_concepto','$fecha_ctr','admin','Pendiente',
                '0000-00-00','venta','$tabla_id','$fila->fecha',
                '0','0','$fila->nro_cuota',
                'no','listo','','0',
                '0','$fila->saldo','0'
                )";
//            echo $sql_insert."<br><br>";
            $conec->ejecutar($sql_insert);
        }
      
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
//    $conec = new ADO();
}

function importar_venta_contado() {
    $conec = new ADO();
    $sql = "select * from temp_venta where estado_actual='Vendido' and tipo_de_venta='Contado';";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
//    echo $ventas->get_num_registros();
    $sql_insert_venta = "insert into venta(
                    ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_valor,ven_decuento,ven_monto,ven_estado,
                    ven_usu_id,ven_tipo,ven_lot_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,
                    ven_cuotam_aux,ven_interes,ven_valor_form,ven_co_propietario,ven_rango_mes,ven_res_anticipo,
                    ven_res_id,ven_codigo,ven_vdo_id,ven_fecha_cobro,ven_concepto, ven_vdo_ext
                    ) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $interno_nombre = str_replace("'", "\'", $venta->nombre_cliente);        
        $coop_nombre = str_replace("'", "\'", $venta->comprador_2);        
        $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$interno_nombre'");
        echo "$venta->id*** select int_id as campo from interno where int_nombre='$interno_nombre' *** id: $int_id<br>";
        $coop_int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$coop_nombre'");
        $fecha_ctr = FUNCIONES::get_fecha_mysql($venta->fecha_cuota_inicial);
        $precio = $venta->valor_lote;
        $porc_desc=  str_replace('%', '', $venta->descuento);
        $descuento=$venta->valor_lote * ($porc_desc/100);
        $ven_monto = $venta->precio_final;
//        $ven_tipo = $venta->meses_plazo * 1 == 0 ? 'Contado' : 'Credito';
        $ven_tipo = 'Contado';
        // M1L1AE
        $lot_codigo=$venta->mz.'L'.$venta->lote.$venta->zona.$venta->zona2;
        $id_lote = get_id_lote($lot_codigo);
        $vdo_id = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_1'") * 1;
        $vdo_ext = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_2'") * 1;

        $_concepto = get_concepto($id_lote);
        $sql_insert = $sql_insert_venta . "(
                    '$int_id','$fecha_ctr','00:00:00','2','$precio','$descuento','$ven_monto','Pagado',
                    'admin','$ven_tipo','$id_lote','$venta->cuota_inicial_total','$venta->tiempo_meses','',
                    '$venta->cuota_fija','Si','0','$coop_int_id','0','0',
                    '0','{$lot_codigo}1','$vdo_id','$venta->dia_de_pago','$_concepto','$vdo_ext')";
        echo $sql_insert.";<br><br>";
        
        $conec->ejecutar($sql_insert);
        
        $ven_id=get_id_venta($lot_codigo.'1');
        $comision_1=$venta->comision_1_dolar*1;
        if($comision_1){
            $vendedor_1=$vdo_id;
            $sql_comision="insert into comision(
                            com_ven_id, com_vdo_id, com_monto, com_moneda, com_estado, com_fecha_cre, com_usu_id
                            )values (
                            '$ven_id','$vendedor_1','$comision_1','2','Pendiente','$fecha_ctr','admin'
                            )";
            $conec->ejecutar($sql_comision);
            if($venta->estado_comision_1=='pagada'){
                $sql_pago="insert into pago_vendedores(
                                pve_fecha,pve_usu_id,pve_vdo_id, pve_monto, pve_moneda, pve_estado
                                )values (
                                '$fecha_ctr','admin','$vendedor_1','$comision_1','2','Activo'
                                )";
                $conec->ejecutar($sql_pago);
            }
        }
        $comision_2=$venta->comision_2_dolar*1;
        if($comision_2){
            $vendedor_2=FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_2'") * 1;
            $sql_comision="insert into comision(
                            com_ven_id, com_vdo_id, com_monto, com_moneda, com_estado, com_fecha_cre, com_usu_id
                            )values (
                            '$ven_id','$vendedor_2','$comision_2','2','Pendiente','$fecha_ctr','admin'
                            )";
            $conec->ejecutar($sql_comision);
            if($venta->estado_comision_1=='pagada'){
                $sql_pago="insert into pago_vendedores(
                                pve_fecha,pve_usu_id,pve_vdo_id, pve_monto, pve_moneda, pve_estado
                                )values (
                                '$fecha_ctr','admin','$vendedor_2','$comision_2','2','Activo'
                                )";
                $conec->ejecutar($sql_pago);
            }
        }
        
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
//    $conec = new ADO();
}


function importar_venta_pendiente() {
    $conec = new ADO();
    $sql = "select * from temp_venta where estado_actual='Vendido' and tipo_de_venta='Crédito';";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
//    echo $ventas->get_num_registros();
    $sql_insert_venta = "insert into venta(
                    ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_valor,ven_decuento,ven_monto,ven_estado,
                    ven_usu_id,ven_tipo,ven_lot_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,
                    ven_cuotam_aux,ven_interes,ven_valor_form,ven_co_propietario,ven_rango_mes,ven_res_anticipo,
                    ven_res_id,ven_codigo,ven_vdo_id,ven_fecha_cobro,ven_concepto,ven_vdo_ext
                    ) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $interno_nombre = str_replace("'", "\'", $venta->nombre_cliente);        
        $coop_nombre = str_replace("'", "\'", $venta->comprador_2);        
        $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$interno_nombre'");
        $coop_int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$coop_nombre'");
        $fecha_ctr = FUNCIONES::get_fecha_mysql($venta->fecha_cuota_inicial);
        $precio = $venta->valor_lote;
        $porc_desc=  str_replace('%', '', $venta->descuento);
        $descuento=$venta->valor_lote * ($porc_desc/100);
        $ven_monto = $venta->precio_final;
//        $ven_tipo = $venta->meses_plazo * 1 == 0 ? 'Contado' : 'Credito';
        $ven_tipo = 'Credito';
        // M1L1AE
        $lot_codigo=$venta->mz.'L'.$venta->lote.$venta->zona.$venta->zona2;
        $id_lote = get_id_lote($lot_codigo);
        $vdo_id = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_1'") * 1;
        $vdo_ext = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_2'") * 1;
//        $monto_vendedor = $ven_monto * ($venta->porc_asesor / 100);
//        $ven_cobrador = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$venta->cobrador'") * 1;
        $_concepto = get_concepto($id_lote);
        $sql_insert = $sql_insert_venta . "(
                    '$int_id','$fecha_ctr','00:00:00','2','$precio','$descuento','$ven_monto','Pendiente',
                    'admin','$ven_tipo','$id_lote','$venta->cuota_inicial_total','$venta->tiempo_meses','',
                    '$venta->cuota_fija','Si','0','$coop_int_id','0','0',
                    '0','{$lot_codigo}1','$vdo_id','$venta->dia_de_pago','$_concepto','$vdo_ext')";
//        echo $sql_insert.";<br><br>";
        
        
        
        $tabla_id=0;
        $plan_codigo=$venta->mz.'L'.$venta->lote;
        $sql = "select * from temp_detalle where codigo_lote='$plan_codigo' order by cuota*1 asc;";
        $cuotas = FUNCIONES::objetos_bd_sql($sql);
        if($cuotas->get_num_registros()>0){
            $conec->ejecutar($sql_insert);
            //get_id_venta($lot_codigo.'1');            
            
            $tabla_id=get_id_venta($lot_codigo.'1');
            $ven_id=$tabla_id;
            
            $comision_1=$venta->comision_1_dolar*1;
            if($comision_1){
                $vendedor_1=$vdo_id;
                $sql_comision="insert into comision(
                                com_ven_id, com_vdo_id, com_monto, com_moneda, com_estado, com_fecha_cre, com_usu_id
                                )values (
                                '$ven_id','$vendedor_1','$comision_1','2','Pendiente','$fecha_ctr','admin'
                                )";
                $conec->ejecutar($sql_comision);
                if($venta->estado_comision_1=='pagada'){
                    $sql_pago="insert into pago_vendedores(
                                    pve_fecha,pve_usu_id,pve_vdo_id, pve_monto, pve_moneda, pve_estado
                                    )values (
                                    '$fecha_ctr','admin','$vendedor_1','$comision_1','2','Activo'
                                    )";
                    $conec->ejecutar($sql_pago);
                }
            }
            $comision_2=$venta->comision_2_dolar*1;
            if($comision_2){
                $vendedor_2=FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_2'") * 1;
                $sql_comision="insert into comision(
                                com_ven_id, com_vdo_id, com_monto, com_moneda, com_estado, com_fecha_cre, com_usu_id
                                )values (
                                '$ven_id','$vendedor_2','$comision_2','2','Pendiente','$fecha_ctr','admin'
                                )";
                $conec->ejecutar($sql_comision);
                if($venta->estado_comision_2=='pagada'){
                    $sql_pago="insert into pago_vendedores(
                                    pve_fecha,pve_usu_id,pve_vdo_id, pve_monto, pve_moneda, pve_estado
                                    )values (
                                    '$fecha_ctr','admin','$vendedor_2','$comision_2','2','Activo'
                                    )";
                    $conec->ejecutar($sql_pago);
                }
            }
        }
        
        
        $sql_insert_detalle = "insert into interno_deuda(
                    ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_estado,
                    ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,
                    ind_capital,ind_saldo,ind_monto_parcial,ind_valor_form,ind_num_correlativo,
                    ind_estado_mora,ind_estado_parcial, ind_observacion
        ) values";
        
        for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
            $cuota = $cuotas->get_objeto();
            $ind_capital = $cuota->capital;
            $ind_interes = $cuota->interes;
            $ind_monto = $ind_capital + $ind_interes;
            if ($cuota->cuota* 1 == 0) {
                $ind_concepto = "Cuota inicial: $_concepto";
            } else {
                $ind_concepto = "Cuota Nro $cuota->cuota: $_concepto";
            }
            $estado = $cuota->estado == 'Pagado' ? 'Pagado' : 'Pendiente';
            $f_pago = $cuota->fecha_de_pago_real != '' ? FUNCIONES::get_fecha_mysql($cuota->fecha_de_pago_real) : '0000-00-00';
//            $tabla_id = get_id_venta($cuota->folio);
            $fecha_prog = FUNCIONES::get_fecha_mysql($cuota->fecha_de_pago);            
            $saldo_deudor = $cuota->saldo_capital* 1;
            if($cuota->multa!=''){
                $est_mora='si';
            }else {
                $est_mora='no';
            }
            $sql_insert = $sql_insert_detalle . "(
                '$int_id','$ind_monto','2','$ind_concepto','$fecha_ctr','admin','$estado',
                '$f_pago','venta','$tabla_id','$fecha_prog','$ind_interes',
                '$ind_capital','$saldo_deudor','0','0','$cuota->cuota',
                '$est_mora','listo','$cuota->observaciones'
                )";
//            echo $sql_insert.";<br><br>";
            $conec->ejecutar($sql_insert,false);
            if($cuota->multa!=''){
                $ind_id=  mysql_insert_id();
                $sql_mora="insert into mora(mor_estado, mor_dias, mor_monto, mor_moneda, mor_ind_id)values";
                $sql_insert="$sql_mora ('Pendiente','$cuota->multa','$cuota->multa','2','$ind_id')";
                echo $sql_insert;
                $conec->ejecutar($sql_insert);
            }
            $cuotas->siguiente();
        }
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
//    $conec = new ADO();
}

function importar_pagos_pendiente() {
    $conec = new ADO();
    $sql = "select * from temp_detalle where fecha_de_pago_real!='';";    
    $cuotas = FUNCIONES::objetos_bd_sql($sql);        
    
    for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
        $cuota = $cuotas->get_objeto();
        $sql_ind="select * from interno_deuda, venta where ind_num_correlativo='$cuota->cuota' and ven_id=ind_tabla_id and replace(ven_codigo,right(ven_codigo,3),'')='$cuota->codigo_lote' ;";
        $int_deuda=  FUNCIONES::objeto_bd_sql($sql_ind);
        if($int_deuda){
            if($int_deuda->ind_estado=='Pendiente'){
                echo '+: '.$cuota->codigo_lote .', '.$cuota->cuota.'<br>';
                $mora=$cuota->multa*1;
                if($mora){
                    $est_mora='si';
                }else {
                    $est_mora='no';
                }
                $sql_update="update interno_deuda set 
                        ind_estado='Pagado',
                        ind_fecha_pago='".  FUNCIONES::get_fecha_mysql($cuota->fecha_de_pago_real)."',
                        ind_estado_mora='$est_mora'
                        where ind_id = '".$int_deuda->ind_id."'";
                echo $sql_update.';<br>';
//                $conec->ejecutar($sql_update);
                if($mora){
                    $ind_id=  mysql_insert_id();
                    $sql_mora="insert into mora(mor_estado, mor_dias, mor_monto, mor_moneda, mor_ind_id)values";
                    $sql_insert="$sql_mora ('Pendiente','$mora','$mora','2','$ind_id')";
//                    echo $sql_insert;
//                    $conec->ejecutar($sql_insert);
                }
            }  else {
                echo '-+: '.$cuota->codigo_lote .', '.$cuota->cuota.'<br>';
            }
        }else{
            echo '- : '.$cuota->codigo_lote .', '.$cuota->cuota.'<br>';
        }
        $cuotas->siguiente();
    }


    echo 'ok';
//    $conec = new ADO();
}
function importar_venta_pendiente_esp() {
    $conec = new ADO();
    $sql = "select * from temp_venta where estado_actual='Vendido' and tipo_de_venta='Crédito' and codigo_lote in ('M2L8','M4L11','M4L13','M7L14','M13L19','M13L20','M14L24','M15L15');";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
//    echo $ventas->get_num_registros();
    $sql_insert_venta = "insert into venta(
                    ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_valor,ven_decuento,ven_monto,ven_estado,
                    ven_usu_id,ven_tipo,ven_lot_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,
                    ven_cuotam_aux,ven_interes,ven_valor_form,ven_co_propietario,ven_rango_mes,ven_res_anticipo,
                    ven_res_id,ven_codigo,ven_vdo_id,ven_fecha_cobro,ven_concepto,ven_vdo_ext
                    ) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $interno_nombre = str_replace("'", "\'", $venta->nombre_cliente);        
        $coop_nombre = str_replace("'", "\'", $venta->comprador_2);        
        $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$interno_nombre'");
        $coop_int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$coop_nombre'");
        $fecha_ctr = FUNCIONES::get_fecha_mysql($venta->fecha_cuota_inicial);
        $precio = $venta->valor_lote;
        $porc_desc=  str_replace('%', '', $venta->descuento);
        $descuento=$venta->valor_lote * ($porc_desc/100);
        $ven_monto = $venta->precio_final;
//        $ven_tipo = $venta->meses_plazo * 1 == 0 ? 'Contado' : 'Credito';
        $ven_tipo = 'Credito';
        // M1L1AE
        $lot_codigo=$venta->mz.'L'.$venta->lote.$venta->zona.$venta->zona2;
        $id_lote = get_id_lote($lot_codigo);
        $vdo_id = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_1'") * 1;
        $vdo_ext = FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_2'") * 1;
//        $monto_vendedor = $ven_monto * ($venta->porc_asesor / 100);
//        $ven_cobrador = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$venta->cobrador'") * 1;
        $_concepto = get_concepto($id_lote);
        $sql_insert = $sql_insert_venta . "(
                    '$int_id','$fecha_ctr','00:00:00','2','$precio','$descuento','$ven_monto','Pendiente',
                    'admin','$ven_tipo','$id_lote','$venta->cuota_inicial_total','$venta->tiempo_meses','',
                    '$venta->cuota_fija','Si','0','$coop_int_id','0','0',
                    '0','{$lot_codigo}1','$vdo_id','$venta->dia_de_pago','$_concepto','$vdo_ext')";
//        echo $sql_insert.";<br><br>";
        
        
        
        $tabla_id=0;
        $plan_codigo=$venta->mz.'L'.$venta->lote;
        $sql = "select * from temp_detalle where codigo_lote='$plan_codigo' order by cuota*1 asc;";
        $cuotas = FUNCIONES::objetos_bd_sql($sql);
        if($cuotas->get_num_registros()>0){
            $conec->ejecutar($sql_insert);
            //get_id_venta($lot_codigo.'1');            
            
            $tabla_id=get_id_venta($lot_codigo.'1');
            $ven_id=$tabla_id;
            
            $comision_1=$venta->comision_1_dolar*1;
            if($comision_1){
                $vendedor_1=$vdo_id;
                $sql_comision="insert into comision(
                                com_ven_id, com_vdo_id, com_monto, com_moneda, com_estado, com_fecha_cre, com_usu_id
                                )values (
                                '$ven_id','$vendedor_1','$comision_1','2','Pendiente','$fecha_ctr','admin'
                                )";
                $conec->ejecutar($sql_comision);
                if($venta->estado_comision_1=='pagada'){
                    $sql_pago="insert into pago_vendedores(
                                    pve_fecha,pve_usu_id,pve_vdo_id, pve_monto, pve_moneda, pve_estado
                                    )values (
                                    '$fecha_ctr','admin','$vendedor_1','$comision_1','2','Activo'
                                    )";
                    $conec->ejecutar($sql_pago);
                }
            }
            $comision_2=$venta->comision_2_dolar*1;
            if($comision_2){
                $vendedor_2=FUNCIONES::atributo_bd_sql("select vdo_id as campo from interno,vendedor where int_id=vdo_int_id and int_nombre='$venta->vendedor_2'") * 1;
                $sql_comision="insert into comision(
                                com_ven_id, com_vdo_id, com_monto, com_moneda, com_estado, com_fecha_cre, com_usu_id
                                )values (
                                '$ven_id','$vendedor_2','$comision_2','2','Pendiente','$fecha_ctr','admin'
                                )";
                $conec->ejecutar($sql_comision);
                if($venta->estado_comision_2=='pagada'){
                    $sql_pago="insert into pago_vendedores(
                                    pve_fecha,pve_usu_id,pve_vdo_id, pve_monto, pve_moneda, pve_estado
                                    )values (
                                    '$fecha_ctr','admin','$vendedor_2','$comision_2','2','Activo'
                                    )";
                    $conec->ejecutar($sql_pago);
                }
            }
        }
        
        
        $sql_insert_detalle = "insert into interno_deuda(
                    ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_estado,
                    ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,
                    ind_capital,ind_saldo,ind_monto_parcial,ind_valor_form,ind_num_correlativo,
                    ind_estado_mora,ind_estado_parcial, ind_observacion
        ) values";
        
        for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
            $cuota = $cuotas->get_objeto();
            $ind_capital = $cuota->capital;
            $ind_interes = $cuota->interes;
            $ind_monto = $ind_capital + $ind_interes;
            if ($cuota->cuota* 1 == 0) {
                $ind_concepto = "Cuota inicial: $_concepto";
            } else {
                $ind_concepto = "Cuota Nro $cuota->cuota: $_concepto";
            }
            $estado = $cuota->estado == 'Pagado' ? 'Pagado' : 'Pendiente';
            $f_pago = $cuota->fecha_de_pago_real != '' ? FUNCIONES::get_fecha_mysql($cuota->fecha_de_pago_real) : '0000-00-00';
//            $tabla_id = get_id_venta($cuota->folio);
            $fecha_prog = FUNCIONES::get_fecha_mysql($cuota->fecha_de_pago);            
            $saldo_deudor = $cuota->saldo_capital* 1;
            if($cuota->multa!=''){
                $est_mora='si';
            }else {
                $est_mora='no';
            }
            $sql_insert = $sql_insert_detalle . "(
                '$int_id','$ind_monto','2','$ind_concepto','$fecha_ctr','admin','$estado',
                '$f_pago','venta','$tabla_id','$fecha_prog','$ind_interes',
                '$ind_capital','$saldo_deudor','0','0','$cuota->cuota',
                '$est_mora','listo','$cuota->observaciones'
                )";
//            echo $sql_insert.";<br><br>";
            $conec->ejecutar($sql_insert,false);
            $mora=$cuota->multa*1;
            if($mora){
                $ind_id=  mysql_insert_id();
                $sql_mora="insert into mora(mor_estado, mor_dias, mor_monto, mor_moneda, mor_ind_id)values";
                $sql_insert="$sql_mora ('Pendiente','$mora','$mora','2','$ind_id')";
                echo $sql_insert;
                $conec->ejecutar($sql_insert);
            }
            $cuotas->siguiente();
        }
        $ventas->siguiente();
//        break;
    }
    echo 'ok';
//    $conec = new ADO();
}

function get_id_venta($codigo) {
    $id_venta = FUNCIONES::atributo_bd_sql("select ven_id as campo from venta where ven_codigo='$codigo'");
    return $id_venta;
}

function get_id_lote($codigo) {
    $sql_campo = "select lot_id as campo from lote where lot_codigo='$codigo'";
//    echo '<br>';
//    echo $sql_campo."<br>";
//    
//    $sql_campo = "select sec_id as campo from sector where sec_nro='$sector'";
//    
//    echo $sql_campo.'<br>';
//    $sec_id = FUNCIONES::atributo_bd_sql($sql_campo);
//    $sql_campo = "select man_id as campo from manzano where man_nro='$manzana' and man_sec_id='$sec_id'";
//    $man_id = FUNCIONES::atributo_bd_sql($sql_campo);
//    $sql_campo = "select cua_id as campo from cuadro where cua_nro='$cuadro' and cua_man_id='$man_id' and cua_sec_id='$sec_id'";
//    $cua_id = FUNCIONES::atributo_bd_sql($sql_campo);
//    $sql_campo = "select parc_id as campo from parcela where parc_nro='$parcela' and parc_cua_id='$cua_id' and parc_man_id='$man_id' and parc_sec_id='$sec_id'";
    $lot_id = FUNCIONES::atributo_bd_sql($sql_campo);
    return $lot_id;
}

function get_concepto($id_lote) {
    $sql = "select urb_nombre, man_nro, lot_nro, zon_nombre, uv_nombre
            from urbanizacion, manzano, lote, zona, uv
            where urb_id=man_urb_id and man_id=lot_man_id and zon_id=lot_zon_id and uv_id=lot_uv_id and lot_id ='$id_lote';";
//    ECHO $sql;
    $lote = FUNCIONES::objeto_bd_sql($sql);
    return "Urb: $lote->urb_nombre - Mza: $lote->man_nro - Lote: $lote->lot_nro - Zona: $lote->zon_nombre - UV: $lote->uv_nombre";
}

function importar_cobrador() {
    $sql = "select distinct cobrador from tmp_venta order by cobrador";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into interno(int_nombre) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $_cobrador = $venta->cobrador;
        $arreglo = array('PAGADO', 'RESCINDIDO', 'SIN ASIGNA');

        if ($_cobrador != '' && !in_array($_cobrador, $arreglo)) {
            $cont = FUNCIONES::atributo_bd_sql("select count(*) as campo from interno where int_nombre='$_cobrador'");
//            echo $cont.'<br>';
            if ($cont == 0) {
                $sql_insert = $sql . "('$venta->cobrador')";
                echo $sql_insert . ';<br>';
            }
        }
//        $conec->ejecutar($sql_insert);
        $ventas->siguiente();
    }
    echo 'ok';
}

function importar_vendedores_int_pri() {
    $sql = "select distinct vendedor_1 as vendedor from temp_venta where estado_actual='Vendido' order by vendedor_1;";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into vendedor(vdo_int_id)values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        if ($venta->vendedor != '') {
            $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$venta->vendedor'");
            $sql_insert = $sql . "('$int_id')";
            echo $sql_insert . ';<br>';
        }
        $conec->ejecutar($sql_insert);
        $ventas->siguiente();
    }
    echo 'ok';
}
function importar_vendedores_int_sec() {
    $sql = "select distinct vendedor_2 as vendedor from temp_venta where estado_actual='Vendido' order by vendedor_2;";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into vendedor(vdo_int_id)values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        if ($venta->vendedor != '') {
            $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$venta->vendedor'");
            $sql_insert = $sql . "('$int_id')";
            echo $sql_insert . ';<br>';
        }
        $conec->ejecutar($sql_insert);
        $ventas->siguiente();
    }
    echo 'ok';
}

function importar_clientes() {
    $sql = "select 
                distinct nombre_cliente, tipo_de_documento, nro_de_documento, emision, estado_civil, direccion, telf_celular,
                telf_domicilio, telf_oficina, ciudad, correo_cliente,
                comprador_2, tipo_documento, numero, expedicion, celular, telef_domicilio, telf_oficina2,domicilio,ciudad2
            from 
                temp_venta 
            where 
                estado_actual='Vendido' order by nombre_cliente;";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into interno(
                int_nombre,int_apellido,int_email,int_telefono,int_celular,
                int_direccion,int_ci,int_usu_id
            ) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $sql_insert = "$sql ('$venta->nombre_cliente','','$venta->correo_cliente','$venta->telf_domicilio','$venta->telf_celular',
                            '$venta->direccion','$venta->nro_de_documento','admin')";
        echo $sql_insert.';<br>';
        $conec->ejecutar($sql_insert);
        if(trim($venta->comprador_2)!=''){
            $sql_insert = "$sql ('$venta->comprador_2','','','$venta->telef_domicilio','$venta->celular',
                            '$venta->domicilio','$venta->numero','admin')";
            echo $sql_insert.';<br>';
            $conec->ejecutar($sql_insert);
        }        
        $ventas->siguiente();
    }
    echo 'ok';
}
function importar_vendedores_pri() {
    $sql = "select distinct vendedor_1 as vendedor from temp_venta where estado_actual='Vendido' order by vendedor_1;";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into interno(int_nombre) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $sql_insert = $sql . "('$venta->vendedor')";
        echo $sql_insert.'<br>';
//        $conec->ejecutar($sql_insert);
        $ventas->siguiente();
    }
    echo 'ok';
}
function importar_vendedores_sec() {
    $sql = "select distinct vendedor_2 as vendedor from temp_venta where estado_actual='Vendido' order by vendedor_2;";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into interno(int_nombre) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $sql_insert = $sql . "('$venta->vendedor')";
        echo $sql_insert.'<br>';
//        $conec->ejecutar($sql_insert);
        $ventas->siguiente();
    }
    echo 'ok';
}

function importar_parcela() {
    
    $sql = "select * from sector";
    $conec = new ADO();
    $sectores = FUNCIONES::objetos_bd_sql($sql);
    $urb_id=1;
    
    $sql = "insert into parcela(parc_nro, parc_urb_id, parc_sec_id, parc_man_id, parc_cua_id) values";
    
    for ($i = 0; $i < $sectores->get_num_registros(); $i++) {
        $sector = $sectores->get_objeto();
        $sql_man = "select * from manzano where man_sec_id='$sector->sec_id'";
        $manzanos=FUNCIONES::objetos_bd_sql($sql_man);
        for ($j = 0; $j <$manzanos->get_num_registros(); $j++) {
            $manzano=$manzanos->get_objeto();
            $sql_cua="select * from cuadro where cua_man_id='$manzano->man_id'";
            $cuadros=  FUNCIONES::objetos_bd_sql($sql_cua);
            for ($k = 0; $k < $cuadros->get_num_registros(); $k++) {
                $cuadro=$cuadros->get_objeto();
                for ($m = 1; $m <=32; $m++) {                    
                    $sql_insert = $sql . "('$m','$urb_id','$sector->sec_id','$manzano->man_id','$cuadro->cua_id')";
                    echo $sql_insert.'; <br>';
                }
                $cuadros->siguiente();
            }
            
            $manzanos->siguiente();

        }
        $sectores->siguiente();
    }
    echo 'ok';
    
    
    
   
}

function cargar_cuadro() {
    $sql = "select * from sector";
    $conec = new ADO();
    $sectores = FUNCIONES::objetos_bd_sql($sql);
    $urb_id=1;
    $sql = "insert into cuadro(cua_nro,cua_urb_id, cua_man_id, cua_sec_id) values";
    for ($i = 0; $i < $sectores->get_num_registros(); $i++) {
        $sector = $sectores->get_objeto();
        $sql_man = "select * from manzano where man_sec_id='$sector->sec_id'";
        $manzanos=FUNCIONES::objetos_bd_sql($sql_man);
        for ($j = 0; $j <$manzanos->get_num_registros(); $j++) {
            $manzano=$manzanos->get_objeto();
            for ($k = 65; $k <=72; $k++) {
                $nro_cuadro=chr($k);
                $sql_insert = $sql . "('$nro_cuadro','$urb_id','$manzano->man_id','$sector->sec_id')";                
                echo $sql_insert.'; <br>';
            }
            $manzanos->siguiente();

        }
        $sectores->siguiente();
    }
    echo 'ok';
}

function cargar_manzano() {
    $sql = "select * from sector";
    $conec = new ADO();
    $sectores = FUNCIONES::objetos_bd_sql($sql);
    $urb_id=1;
    $sql = "insert into manzano(man_nro,man_urb_id, man_sec_id) values";
    $aux=0;
    for ($i = 0; $i < $sectores->get_num_registros(); $i++) {
        $sector = $sectores->get_objeto();
        
        for ($j = 1; $j <=6; $j++) {
            $_nro=($aux*6)+$j;
            $sql_insert = $sql . "('$_nro','$urb_id','$sector->sec_id')";
            echo $sql_insert.';<br>';
//            $conec->ejecutar($sql_insert);

        }
        $aux++;
        $sectores->siguiente();
    }
    echo 'ok';
}

function importar_sector() {
    $sql = "select distinct(sector) from tmp_venta order by sector asc";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into sector(sec_nro,sec_urb_id) values";
    
    $insert=array();
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $sector = str_replace("'", "\'", $venta->sector);
        if (trim($sector == ''))
            $sector = '-1';
        $txt_sector=  get_sector($sector);
        if($txt_sector){            
            if(!in_array($txt_sector, $insert)){
                $sql_insert = $sql . "('$txt_sector',1)";
                echo $sql_insert . ';<br>';
            }
            $insert[]=$txt_sector;            
        }
//        $conec->ejecutar($sql_insert);
        $ventas->siguiente();
    }
    echo 'ok';
}

function importar_interno() {
    $sql = "select * from tmp_venta";
    $conec = new ADO();

    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into interno(int_nombre, int_ci, int_telefono,int_telefono_oficina, 
    int_celular, int_direccion, int_direccion_oficina) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $cliente_o_titular = str_replace("'", "\'", $venta->cliente_o_titular);
        $carnet = str_replace("'", "\'", $venta->carnet);
        $tel_dom = str_replace("'", "\'", $venta->tel_dom);
        $tel_ofic = str_replace("'", "\'", $venta->tel_ofic);
        $tel_celu = str_replace("'", "\'", $venta->tel_celu);
        $dir_dom = str_replace("'", "\'", $venta->dir_dom);
        $dir_ofc = str_replace("'", "\'", $venta->dir_ofc);
        $sql_insert = $sql . "('$cliente_o_titular','$carnet','$tel_dom','$tel_ofic','$tel_celu','$dir_dom','$dir_ofc')";

        $conec->ejecutar($sql_insert);
        $ventas->siguiente();
    }
    echo 'ok';
}

function get_sector($sec) {
    $sector = array(
        '-1' => '-1',
        '1' => 'ORQUIDEAS',
        '1 ORQ' => 'ORQUIDEAS',
        '2' => 'JAZMINES',
        '2 JAS' => 'JAZMINES',
        '20' => '',
        '3' => 'CELESTINAS',
        '3 CEL' => 'CELESTINAS',
        '4' => 'BUGANVILLAS',
        '4 BUG' => 'BUGANVILLAS'
        );
    return $sector[$sec];
}