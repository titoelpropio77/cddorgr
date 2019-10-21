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
//importar_cuadro();
//importar_manzano();
//importar_sector();
//importar_vendedores();
//importar_cobrador();
//importar_vendedores_int();
//importar_venta();
//importar_detalle();
//actualizar_concepto();
//importar_ventas_pagadas();
//corregir_venta_parcela();
//pagar_comisiones();
//corregir_detalle();

//corregir_venta(192);

//corregir_ca();


$venta =  FUNCIONES::objeto_bd_sql("select * from venta where ven_id=109");
echo "<pre>";
print_r($venta);
echo "</pre>";

function corregir_ca(){
    $sql="select * from con_cuenta_ca where can_ges_id=13";
    $cuentas=  FUNCIONES::objetos_bd_sql($sql);
    for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
        $cu=$cuentas->get_objeto();
        $codigo=  explode('.', $cu->can_codigo);
        $cod1=($codigo[0]*1)+1;
        $sql_up="update con_cuenta_ca set can_codigo='0$cod1.$codigo[1]' where can_id='$cu->can_id'";
        echo $sql_up.';<br>';
        $cuentas->siguiente();
    }
}


function ejecutar_sql($sql) {
    $conec=new ADO();
//    $conec->ejecutar($sql);
    echo $sql.';<br>';
}
function corregir_venta($id) {
    $conec=new ADO();
    $sql = "select * from venta where ven_id='$id'";
    $venta = FUNCIONES::objeto_bd_sql($sql);
    $ven_valor=$venta->ven_valor;
    $ven_monto_inter=$venta->ven_monto_intercambio;
    $ven_cuota_inicial=$venta->ven_cuota_inicial;    
    $descuento=8496.44;
    $ven_monto=$ven_valor-$descuento;
    $monto_efectivo=$ven_monto-$ven_monto_inter-$ven_cuota_inicial ;
    
    $sql="update venta set ven_decuento='$descuento' , ven_monto_efectivo='$monto_efectivo' where ven_id='$id'";
    ejecutar_sql($sql);
    
    
    $delete = "delete from interno_deuda where ind_tabla='venta' and ind_tabla_id='$id' and ind_num_correlativo>0";
    ejecutar_sql($delete);
    $saldo_fin=$ven_monto-$ven_cuota_inicial;
    $sql="update interno_deuda set ind_saldo_final='$saldo_fin' ind_saldo='$saldo_fin' where ind_tabla='venta' and ind_tabla_id='$id' and ind_num_correlativo=0";
    ejecutar_sql($sql);
    
    
    $saldo_real = $ven_monto-$ven_cuota_inicial;
    $nro_cuota = 1;
    $_POST[ven_tipo_pago] ='Intercambio';
    $monto_intercambio=$ven_monto_inter;
    if ($_POST[ven_tipo_pago] == 'Intercambio') {
        
        $_fpri = '15/07/2014';
        
        $plan_data = array(
            'int_id' => $venta->ven_int_id,
            'saldo' => $monto_intercambio,            
            'interes' => 0,
            'meses_plazo' => '',
            'cuota_mensual' => 250,
            'moneda' => 2,
            'concepto' => 'Urb:Mirador del Urubo - Mza:11 - Lote:6 - Zona:AN - UV:I',
            'fecha' => FUNCIONES::get_fecha_mysql('10/05/2014'),
            'fecha_pri_pago' => FUNCIONES::get_fecha_mysql($_fpri),
            'usuario' => 'admin',
            'tabla' => 'venta',
            'nro_cuota_inicio' => $nro_cuota,
            'tabla_id' => 192,
            'ind_tipo' => 'intercambio'
        );
        $nro_cuota = generar_plan_pagos($plan_data, $conec, $saldo_real); //
        
    }


    $plan_data = array(
        'int_id' => $venta->ven_int_id,
        'saldo' => $monto_efectivo,
        'interes' => 12,
        'meses_plazo' => 96,
        'cuota_mensual' => '',
        'moneda' => 2,
        'concepto' => 'Urb:Mirador del Urubo - Mza:11 - Lote:6 - Zona:AN - UV:I',
        'fecha' => FUNCIONES::get_fecha_mysql('10/05/2014'),
        'fecha_pri_pago' => FUNCIONES::get_fecha_mysql('15/07/2015'),
        'usuario' => 'admin',
        'tabla' => 'venta',
        'nro_cuota_inicio' => $nro_cuota,
        'tabla_id' => 192,        
        'ind_tipo' => 'pcuota'
    );
    generar_plan_pagos($plan_data, $conec, $saldo_real); //
}

function generar_plan_pagos($parametros, &$conec, &$saldo_real = null) {
    if ($conec == null) {
        $conec = new ADO();
    }
    $params = (object) $parametros;
    $lista_pagos = array();
    if ($params->cuota_mensual == "" && $params->meses_plazo != "") {//plazo
        $data = array(
            'tipo' => 'plazo',
            'meses' => $params->meses_plazo,
            'interes_anual' => $params->interes,
            'saldo' => $params->saldo,
            'fecha_pri_cuota' => $params->fecha_pri_pago,
            'nro_cuota_inicio' => $params->nro_cuota_inicio
        );
        $lista_pagos = FUNCIONES::plan_de_pagos($data);
    } elseif ($params->cuota_mensual != "" && $params->meses_plazo == "") {//cuota mensual
        $data = array(
            'tipo' => 'cuota',
            'interes_anual' => $params->interes,
            'saldo' => $params->saldo,
            'fecha_pri_cuota' => $params->fecha_pri_pago,
            'cuota' => $params->cuota_mensual,
            'nro_cuota_inicio' => $params->nro_cuota_inicio
        );
        $lista_pagos = FUNCIONES::plan_de_pagos($data);
    } elseif ($params->cuota_mensual != "" && $params->meses_plazo != "") {//cuota mensual
        $data = array(
            'tipo' => 'cuota',
            'interes_anual' => $params->interes,
            'saldo' => $params->cuota_mensual * $params->meses_plazo,
            'fecha_pri_cuota' => $params->fecha_pri_pago,
            'cuota' => $params->cuota_mensual,
            'nro_cuota_inicio' => $params->nro_cuota_inicio
        );
        $lista_pagos = FUNCIONES::plan_de_pagos($data);
    }
    $nro_cuota_inicio = 0;
    foreach ($lista_pagos as $fila) {
        $_saldo = $fila->saldo;
        if ($saldo_real != null) {
            $saldo_real = $saldo_real - $fila->capital;
            $_saldo = $saldo_real;
        }
        $estado='Pendiente';
        $fecha_pago='0000-00-00';
        $interes_pag=0;
        $capital_pag=0;
        $monto_pag=0;            
        if($params->ind_tipo=='intercambio'){
            $estado='Pagado';
            $fecha_pago='2014-07-21';
            $interes_pag=$fila->interes;
            $capital_pag=$fila->capital;
            $monto_pag=$fila->monto;            
        }
        $sql = "insert into interno_deuda(
                        ind_int_id,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,
                        ind_estado,ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,
                        ind_dias_interes,ind_interes,ind_capital,ind_monto,ind_saldo,ind_monto_parcial,ind_num_correlativo,ind_tipo,
                        ind_interes_pagado,ind_capital_pagado,ind_monto_pagado,ind_saldo_final
                        )values(
                        '$params->int_id','$params->moneda','Cuota Nro $fila->nro_cuota - $params->concepto','$params->fecha','$params->usuario',
                        '$estado','$fecha_pago','$params->tabla','$params->tabla_id','$fila->fecha',
                        '30','$fila->interes','$fila->capital','$fila->monto','$_saldo','0','$fila->nro_cuota','$params->ind_tipo',
                        '$interes_pag','$capital_pag','$monto_pag','$_saldo'
                        )";
        $nro_cuota_inicio = $fila->nro_cuota;
        echo $sql.';<br>';
//        $conec->ejecutar($sql);
    }
    $nro_cuota_inicio++;
    return $nro_cuota_inicio;
}

function corregir_detalle() {
//    $sql="select * from venta where ven_id in ('137','148','154','172','216','217')";
    $sql = "select * from venta where ven_id in ('148')";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
//    echo $ventas->get_num_registros();
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();

        $sql = "select * from tmp_plan where ven_id='$venta->ven_id' order by id";
        $cuotas = FUNCIONES::objetos_bd_sql($sql);
        $sql_insert_detalle = "insert into interno_deuda(
                    ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_estado,
                    ind_fecha_pago,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,
                    ind_capital,ind_saldo,ind_num_correlativo,
                    ind_estado_mora,ind_estado_parcial,
                    ind_interes_pagado, ind_capital_pagado,ind_monto_pagado,
                    ind_saldo_final,ind_tipo
        ) values";
//        echo "$venta->precio - $venta->interes"."<br>";
        $saldo_deudor = $venta->ven_monto;
        $j = 0;
        $_concepto = $venta->ven_concepto;
        echo '<br>';
        $sql_del = "delete from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id'";
        echo $sql_del . ';<br>';
        echo '<br>';

        $cuota_aux = 0;
        while ($j < $cuotas->get_num_registros() && $saldo_deudor > 0) {
            $cuota = $cuotas->get_objeto();
            $capital_gen = $cuota->capital * 1;
            $interes_gen = $cuota->interes * 1;

            $monto_gen = $capital_gen + $interes_gen;
            if ($cuota->cuota * 1 == 0) {
                $ind_concepto = "Cuota inicial: $_concepto";
            } else {
                $ind_concepto = "Cuota Nro $cuota->cuota: $_concepto";
            }

            if ($cuota->cuota * 1 == 1) {
                $cuota_aux = $monto_gen;
            }
            $estado = 'Pendiente';
//            $f_pago = $cuota->fecha!= '' ? FUNCIONES::get_fecha_mysql($cuota->fecha_pago) : '0000-00-00';
            $tabla_id = $venta->ven_id;
            $fecha_prog = FUNCIONES::get_fecha_mysql($cuota->fecha);
            $saldo_deudor = $cuota->saldo;
//            $interes_pag = 0;
//            $capital_pag = $cuota->monto * 1;
//            $monto_pag=$interes_pag+$capital_pag;
//            if($capital_pag){
//                $saldo_deudor = $saldo_deudor -$capital_pag;
//            }else{
//                if($saldo_deudor -$capital_gen<0){
//                    $capital_gen=$saldo_deudor;
//                    $monto_gen=$capital_gen+$interes_gen;
//                }
//                $saldo_deudor = $saldo_deudor -$capital_gen;
//            }
            $sql_insert = $sql_insert_detalle . "(
                '$venta->ven_int_id','$monto_gen','2','$ind_concepto','$venta->ven_fecha','admin','$estado',
                '0000-00-00','venta','$tabla_id','$fecha_prog','$interes_gen',
                '$capital_gen','$saldo_deudor','$cuota->cuota',
                'no','listo',
                '0','0','0',
                '0','pcuota'
                )";
            echo $sql_insert . ";<br>";
//                $conec->ejecutar($sql_insert);
            $cuotas->siguiente();
            $j++;
        }
        echo '<br><br>';
        $sql_up = "update venta set ven_cuotam_aux='$cuota_aux' where ven_id='$venta->ven_id'";
        echo $sql_up . ';<br>';
        echo '<br>';

        $ventas->siguiente();
    }
}

function pagar_comisiones() {
    $conec = new ADO();
    $sql = "select * from venta where ven_id<='2004'and ven_parc_id!=0";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    //ven_pagado_vendedor
    echo $ventas->get_num_registros();
    $sql_comision = "insert into comision (
                    com_ven_id, com_vdo_id, com_monto, com_moneda, com_estado, com_fecha_cre,
                    com_fecha_pag, com_usu_id, com_usu_id_pago, com_observacion
                    )values";
    $sql_pago = "insert into pago_vendedores (
                    pve_fecha, pve_hora,pve_usu_id, pve_vdo_id, pve_monto, 
                    pve_moneda, pve_estado, pve_glosa,pve_comisiones                    
                    )values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $sql_insert_com = $sql_comision . "('$venta->ven_id','$venta->ven_vdo_id','$venta->ven_pagado_vendedor','2','Pagado','$venta->ven_fecha',
                            '$venta->ven_fecha','admin','admin','Comision de importacion')";
        $conec->ejecutar($sql_insert_com, false);
        $llave = mysql_insert_id();
//        echo $sql_insert_com.'<br><br>';

        $sql_insert_pag = $sql_pago . "('$venta->ven_fecha','00:00:00','admin', '$venta->ven_vdo_id','$venta->ven_pagado_vendedor',
                            '2','Activo','Pago de comisiones Importadas','$llave')";
        $conec->ejecutar($sql_insert_pag);
//        echo $sql_insert_pag.'<br><br>';
        $ventas->siguiente();
//        if($i==50){
//            break;
//        }
    }

    echo 'ok';
}

function corregir_venta_parcela() {
    $conec = new ADO();
    $sql = "select * from venta";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $parc_id = get_parcela($venta->ven_codigo);
        $concepto = get_concepto($parc_id);
        $sql_update = "update venta set ven_parc_id='$parc_id', ven_concepto='$concepto' where ven_id='$venta->ven_id'";
        $conec->ejecutar($sql_update);
//        echo $sql_update.';<br>';
        $cuotas = FUNCIONES::objetos_bd_sql("select * from interno_deuda where ind_tabla_id='$venta->ven_id'");
        for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
            $cuota = $cuotas->get_objeto();
            $nro = $cuota->ind_num_correlativo;
            if ($nro == 0) {
                $ind_concepto = "Cuota Inicial - $concepto";
            } else {
                $ind_concepto = "Cuota Nro. $nro - $concepto";
            }
            $sql_update = "update interno_deuda set ind_concepto='$ind_concepto' where ind_id='$cuota->ind_id'";
            $conec->ejecutar($sql_update);
//            echo $sql_update.';<br>';
            $cuotas->siguiente();
        }
//        $conec->ejecutar($sql_update);
        $ventas->siguiente();
//        break;
    }
}

function get_parcela($folio) {
    $sql = "select * from tmp_venta where folio='$folio'";
    $objeto = FUNCIONES::objeto_bd_sql($sql);
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
    $sector = get_sector($sector);
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

function importar_ventas_pagadas() {
    $conec = new ADO();
    $sql = "select * from tmp_venta where 
            estado='pagado' and saldo_por_cobrar<=0 and producto_o_servicio='PARCELA' and interes=0";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    echo $ventas->get_num_registros();

    $sql_insert_venta = "insert into venta(
                    ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_valor,ven_decuento,ven_monto,ven_estado,
                    ven_usu_id,ven_tipo,ven_parc_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,
                    ven_cuotam_aux,ven_interes,ven_valor_form,ven_co_propietario,ven_rango_mes,ven_res_anticipo,
                    ven_res_id,ven_codigo,ven_vdo_id,ven_fecha_cobro,ven_porc_vendedor,ven_monto_vendedor,
                    ven_pagado_vendedor,ven_niveles,ven_bobeda,ven_cobrador,ven_producto,ven_nro_contrato,ven_concepto
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
                    '$venta->asesor_a_cta','$venta->niveles','$venta->boveda','$ven_cobrador','','$venta->nro_contrato','$_concepto')";
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

function actualizar_concepto() {
    $conec = new ADO();
    $sql = "select * from venta";
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

function importar_venta() {
    $conec = new ADO();
    $sql = "select * from tmp_venta where producto_o_servicio='parcela' and interes=0 and estado='activo'";
    $ventas = FUNCIONES::objetos_bd_sql($sql);
//    echo $ventas->get_num_registros();
    $sql_insert_venta = "insert into venta(
                    ven_int_id,ven_fecha,ven_hora,ven_moneda,ven_valor,ven_decuento,ven_monto,ven_estado,
                    ven_usu_id,ven_tipo,ven_lot_id,ven_cuota_inicial,ven_meses_plazo,ven_observacion,
                    ven_cuotam_aux,ven_interes,ven_valor_form,ven_co_propietario,ven_rango_mes,ven_res_anticipo,
                    ven_res_id,ven_codigo,ven_vdo_id,ven_fecha_cobro,ven_porc_vendedor,ven_monto_vendedor,
                    ven_pagado_vendedor,ven_niveles,ven_bobeda,ven_cobrador,ven_producto,ven_nro_contrato
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
        $sql_insert = $sql_insert_venta . "(
                    '$int_id','$fecha_ctr','00:00:00','2','$venta->precio','$venta->descuento','$ven_monto','Pendiente',
                    'admin','$ven_tipo','$id_parcela','$venta->cuota_inicial','$venta->meses_plazo','$venta->observaciones',
                    '$venta->cuota_mensual','$venta->interes','0','0','0','0',
                    '0','$venta->folio','$vdo_id','$venta->fecha_cobranza','$venta->porc_asesor','$monto_vendedor',
                    '$venta->asesor_a_cta','$venta->niveles','$venta->boveda','$ven_cobrador','','$venta->nro_contrato')";
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
        $_concepto = get_concepto($id_parcela);
        for ($j = 0; $j < $cuotas->get_num_registros(); $j++) {
            $cuota = $cuotas->get_objeto();
            $ind_capital = $cuota->monto_cuota;
            $ind_interes = 0;
            $ind_monto = $ind_capital + $ind_interes;
            if ($cuota->nro_cuota * 1 == 0) {
                $ind_concepto = "Cuota inicial: $_concepto";
            } else {
                $ind_concepto = "Cuota Nro $cuota->nro_cuota: $_concepto";
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
//    $conec = new ADO();
}

function get_id_venta($folio) {
    $id_venta = FUNCIONES::atributo_bd_sql("select ven_id as campo from venta where ven_codigo='$folio'");
    return $id_venta;
}

function get_id_parcela($sector, $manzana, $cuadro, $parcela) {
    $sector = trim(str_replace("'", "\'", $sector));
    $manzana = trim(str_replace("'", "\'", $manzana));
    $cuadro = trim(str_replace("'", "\'", $cuadro));
    $parcela = trim(str_replace("'", "\'", $parcela));
    if (trim($sector == ''))
        $sector = '-1';
    if (trim($manzana == ''))
        $manzana = '-1';
    if (trim($cuadro == ''))
        $cuadro = '-1';
    if (trim($parcela == ''))
        $parcela = '-1';
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

function get_concepto($parc_id) {
    $sql = "select sec_nro, man_nro,cua_nro,parc_nro from sector, manzano, cuadro, parcela 
            where parc_sec_id=sec_id and parc_man_id=man_id and parc_cua_id=cua_id and parc_id ='$parc_id';";
    $parcela = FUNCIONES::objeto_bd_sql($sql);
    return "Sector: $parcela->sec_nro, Mz: $parcela->man_nro, Cuadro: $parcela->cua_nro, Parcela: $parcela->parc_nro";
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

function importar_vendedores_int() {
    $sql = "select distinct asesor from tmp_venta order by asesor";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into vendedor(vdo_int_id)values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        if ($venta->asesor != '') {
            $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno where int_nombre='$venta->asesor'");
            $sql_insert = $sql . "('$int_id')";
            echo $sql_insert . ';<br>';
        }
//        $conec->ejecutar($sql_insert);
        $ventas->siguiente();
    }
    echo 'ok';
}

function importar_vendedores() {
    $sql = "select distinct asesor from tmp_venta order by asesor";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into interno(int_nombre) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $sql_insert = $sql . "('$venta->asesor')";
        $conec->ejecutar($sql_insert);
        $ventas->siguiente();
    }
    echo 'ok';
}

function importar_parcela() {
    $sql = "select distinct sector,manzana,cuadro,parcela from tmp_venta order by sector asc, manzana asc, cuadro asc, parcela asc";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
//    echo $ventas->get_num_registros();
//    return;
    $sql = "insert into parcela(parc_nro,parc_cua_id,parc_man_id, parc_sec_id) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $sector = trim(str_replace("'", "\'", $venta->sector));
        $manzana = trim(str_replace("'", "\'", $venta->manzana));
        $cuadro = trim(str_replace("'", "\'", $venta->cuadro));
        $parcela = trim(str_replace("'", "\'", $venta->parcela));
        if (trim($sector == ''))
            $sector = '-1';
        if (trim($manzana == ''))
            $manzana = '-1';
        if (trim($cuadro == ''))
            $cuadro = '-1';
        if (trim($parcela == ''))
            $parcela = '-1';
        $sector = get_sector($sector);
        $sql_campo = "select sec_id as campo from sector where sec_nro='$sector'";
        $sec_id = FUNCIONES::atributo_bd_sql($sql_campo);
        $sql_campo = "select man_id as campo from manzano where man_nro='$manzana' and man_sec_id='$sec_id'";
        $man_id = FUNCIONES::atributo_bd_sql($sql_campo);
        $sql_campo = "select cua_id as campo from cuadro where cua_nro='$cuadro' and cua_man_id='$man_id' and cua_sec_id='$sec_id'";
        $cua_id = FUNCIONES::atributo_bd_sql($sql_campo);

        if ($sector) {
            $sql_insert = $sql . "('$parcela','$cua_id','$man_id','$sec_id')";
            //        echo $sql_insert.';<br>';
            $conec->ejecutar($sql_insert);
        }
        $ventas->siguiente();
    }
    echo 'ok';
}

function importar_cuadro() {
    $sql = "select distinct sector,manzana,cuadro from tmp_venta order by sector asc, manzana asc, cuadro asc";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);

    $sql = "insert into cuadro(cua_nro,cua_urb_id, cua_man_id, cua_sec_id) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $sector = trim(str_replace("'", "\'", $venta->sector));
        $manzana = trim(str_replace("'", "\'", $venta->manzana));
        $cuadro = trim(str_replace("'", "\'", $venta->cuadro));
        if (trim($sector == ''))
            $sector = '-1';
        if (trim($manzana == ''))
            $manzana = '-1';
        if (trim($cuadro == ''))
            $cuadro = '-1';
        $sector = get_sector($sector);
        $sql_campo = "select sec_id as campo from sector where sec_nro='$sector'";
        $sec_id = FUNCIONES::atributo_bd_sql($sql_campo);
        $sql_campo = "select man_id as campo from manzano where man_nro='$manzana' and man_sec_id='$sec_id'";
        $man_id = FUNCIONES::atributo_bd_sql($sql_campo);
        if (sector) {
            $sql_insert = $sql . "('$cuadro','1','$man_id','$sec_id')";
            //        echo $sql_insert.';<br>';
            $conec->ejecutar($sql_insert);
        }
        $ventas->siguiente();
    }
    echo 'ok';
}

function importar_manzano() {
    $sql = "select distinct manzana, sector from tmp_venta order by sector asc, manzana asc";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);

    $sql = "insert into manzano(man_nro,man_sec_id) values";
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $manzana = str_replace("'", "\'", $venta->manzana);
        $sector = trim(str_replace("'", "\'", $venta->sector));
        if (trim($manzana == ''))
            $manzana = '-1';
        if (trim($sector == ''))
            $sector = '-1';
        $sector = get_sector($sector);
        $sql_campo = "select sec_id as campo from sector where sec_nro='$sector'";
        $sec_id = FUNCIONES::atributo_bd_sql($sql_campo);
        if ($sector) {
            $sql_insert = $sql . "('$manzana','$sec_id')";
//            echo $sql_insert.';<br>';
            $conec->ejecutar($sql_insert);
        }

        $ventas->siguiente();
    }
    echo 'ok';
}

function importar_sector() {
    $sql = "select distinct(sector) from tmp_venta order by sector asc";
    $conec = new ADO();
    $ventas = FUNCIONES::objetos_bd_sql($sql);
    $sql = "insert into sector(sec_nro,sec_urb_id) values";

    $insert = array();
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $sector = str_replace("'", "\'", $venta->sector);
        if (trim($sector == ''))
            $sector = '-1';
        $txt_sector = get_sector($sector);
        if ($txt_sector) {
            if (!in_array($txt_sector, $insert)) {
                $sql_insert = $sql . "('$txt_sector',1)";
                echo $sql_insert . ';<br>';
            }
            $insert[] = $txt_sector;
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