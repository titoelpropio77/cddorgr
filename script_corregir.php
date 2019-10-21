<?php

require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';

//update_bisito();

//revisar_ventas();

//actualizar_fechas_valor();

//actualizar_pagos_capital();

//actualizar_plazo();
//actualizar_fecha_scuota();

//corregir_cuenta_gestion();
//corregir_cuenta_ca_gestion();
//corregir_cuenta_cc_gestion();
//corregir_cuenta_cf_gestion();


//eliminar_lotes();

//corregir_correlativo(2790,27,806185);

//corregrir_cmp_compra();
//corregir_cmp_gestion();
function corregir_cmp_gestion(){
    $comprabantes=FUNCIONES::lista_bd_sql("select * from con_comprobante where cmp_ges_id!=year(cmp_fecha)-2000 and cmp_eliminado='No'");
    $reg=0;
    foreach ($comprabantes as $comprobante) {
        $af=  explode('-', $comprobante->cmp_fecha);
        $ges_id=$af[0]-2000;
        if($ges_id>0){
            $periodo=FUNCIONES::objeto_bd_sql("select * from con_periodo where pdo_fecha_inicio<='$comprobante->cmp_fecha' and pdo_fecha_fin>='$comprobante->cmp_fecha'");
            if($periodo){
                $sql_up="update con_comprobante set cmp_ges_id='$ges_id', cmp_peri_id='$periodo->pdo_id' where cmp_id='$comprobante->cmp_id';";
                echo "$sql_up<br>";
                $reg++;
            }else{
                echo "-- NO EXISTE PERIODO<BR>";
            }
            
        }
    }
    echo "-- Total Registros $reg<br>";
    
}

function corregrir_cmp_compra(){
    $compras=  FUNCIONES::lista_bd_sql("select distinct com_id,com_fecha,com_referido from con_compra, con_compra_detalle where dcom_tipo_nota='retencion' and com_id=dcom_com_id and com_fecha_cre<='2016-03-22 00:00:00' order by com_fecha desc,com_id desc");
    echo "<table>
            <thead>
                <tr>
                    <th>ID</th><th>CLIENTE</th><th>FECHA</th><th>VER</th><th>MODIFICAR</th>
                </tr>
            </thead>
            
            <tbody>
            
            ";
    foreach ($compras as $compra) {
        $fecha = FUNCIONES::get_fecha_latina($compra->com_fecha);
        echo "<tr>
                    <td>$compra->com_id</td><td>$compra->com_referido</td><td>$fecha</td>
                    <td><a href='gestor.php?mod=con_compra&tarea=VER&id=$compra->com_id&auto_save=ok' target= '_blank'>ver</a></td>
                    <td><a href='gestor.php?mod=con_compra&tarea=MODIFICAR&id=$compra->com_id&auto_save=ok' target= '_blank'>modificar</a></td>
                </tr>";
    }
    echo "
            </tbody>
        </table>
        ";
}


function corregir_correlativo($ven_id,$num_inicio,$ind_id_ini){
//    $ven_id=3399;
//    $num_inicio=31;
//    $ind_id_ini=726831;
    $venta =  FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$ven_id'");
    $indlista=  FUNCIONES::lista_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id and ind_id >= $ind_id_ini and ind_estado in ('Pendiente','Pagado') order by ind_id asc" );
    $numero=$num_inicio;
    foreach ($indlista as $obj) {
        $concepto="Cuota Nro $numero - $venta->ven_concepto";
        $sql_up="update interno_deuda set ind_num_correlativo=$numero, ind_concepto='$concepto' where ind_id=$obj->ind_id";
        echo "$sql_up;<br>";

        $sql_up="update interno_deuda_pago set idp_ind_correlativo=$numero where idp_ind_id=$obj->ind_id";
        echo "$sql_up;<br>";
        
        $numero++;
    }
}

function eliminar_lotes(){
    $alotes=array(
        '35,8',
        '35,10',
        '35,22',
        '35,23',
        '35,24',
        '35,25',
        '35,35',
        '35,36',
        '35,37',
        '35,38',
        '35,39',
        '31,1',
        '31,25',
        '46,1',
        '46,4',
        '46,5',
    );
    foreach ($alotes as $strlot) {
        $al=  explode(',', $strlot);
        $mz=$al[0];
        $lot=$al[1];
        $lote=  FUNCIONES::objeto_bd_sql("select * from lote,manzano where man_nro='$mz' and lot_nro='$lot' and man_urb_id=4 and lot_man_id=man_id ");
        if($lote){
            if($lote->lot_estado=='Disponible'){
//                echo "Existe lote Disponible $mz,$lot ==> $lote->lot_id<br>";
            }else{
                echo "El Lote esta $lote->lot_estado $mz,$lot ==> $lote->lot_id<br>";
            }
        }else{
            echo "No hay lote $mz,$lot<br>";
        }
    }
}


function corregir_cuenta_gestion(){
    $sql_sel="select * from con_comprobante, con_comprobante_detalle, con_cuenta where 
                cmp_ges_id!=cue_ges_id and cmp_id=cde_cmp_id and cde_cue_id=cue_id and cmp_eliminado='No'";
    
    $detalles=  FUNCIONES::objetos_bd_sql($sql_sel);
    $conec=new ADO();
    for ($i = 0; $i < $detalles->get_num_registros(); $i++) {
        $det=$detalles->get_objeto();
        $_cuenta=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_id='$det->cde_cue_id'");
        $cuenta=FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_codigo='$_cuenta->cue_codigo' and cue_ges_id='$det->cmp_ges_id'");
        if($cuenta){
            $sql_update="update con_comprobante_detalle set cde_cue_id=$cuenta->cue_id where cde_cmp_id='$det->cde_cmp_id' and cde_mon_id='$det->cde_mon_id' and cde_secuencia='$det->cde_secuencia'";
            echo $sql_update.';<br>';
        }else{
            echo "-- $_cuenta->cue_codigo, $det->cmp_ges_id, $det->cmp_id <br>";
        }
        
//        $conec->ejecutar($sql_update, false, false);
        $detalles->siguiente();
    }
    echo $detalles->get_num_registros()."<br>";
}
function corregir_cuenta_ca_gestion(){
    $sql_sel="select * from con_comprobante, con_comprobante_detalle, con_cuenta_ca where 
                cmp_ges_id!=can_ges_id and cmp_id=cde_cmp_id and cde_can_id=can_id ";
    
    $detalles=  FUNCIONES::objetos_bd_sql($sql_sel);
    $conec=new ADO();
    for ($i = 0; $i < $detalles->get_num_registros(); $i++) {
        $det=$detalles->get_objeto();
        $_cuenta=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_ca where can_id='$det->cde_can_id'");
        $cuenta=FUNCIONES::objeto_bd_sql("select * from con_cuenta_ca where can_codigo='$_cuenta->can_codigo' and can_ges_id='$det->cmp_ges_id'");
        $sql_update="update con_comprobante_detalle set cde_can_id=$cuenta->can_id where cde_cmp_id='$det->cde_cmp_id' and cde_mon_id='$det->cde_mon_id' and cde_secuencia='$det->cde_secuencia'";
        echo $sql_update.'<br>';
//        $conec->ejecutar($sql_update, false, false);
        $detalles->siguiente();
    }
    echo $detalles->get_num_registros()."<br>";
}
function corregir_cuenta_cc_gestion(){
    $sql_sel="select * from con_comprobante, con_comprobante_detalle, con_cuenta_cc where 
                cmp_ges_id!=cco_ges_id and cmp_id=cde_cmp_id and cde_cco_id=cco_id ";
    
    $detalles=  FUNCIONES::objetos_bd_sql($sql_sel);
    $conec=new ADO();
    for ($i = 0; $i < $detalles->get_num_registros(); $i++) {
        $det=$detalles->get_objeto();
        $_cuenta=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_cc where cco_id='$det->cde_cco_id'");
        $cuenta=FUNCIONES::objeto_bd_sql("select * from con_cuenta_cc where cco_codigo='$_cuenta->cco_codigo' and cco_ges_id='$det->cmp_ges_id'");
        $sql_update="update con_comprobante_detalle set cde_cco_id=$cuenta->cco_id where cde_cmp_id='$det->cde_cmp_id' and cde_mon_id='$det->cde_mon_id' and cde_secuencia='$det->cde_secuencia'";
        echo $sql_update.'<br>';
//        $conec->ejecutar($sql_update, false, false);
        $detalles->siguiente();
    }
    echo $detalles->get_num_registros()."<br>";
}
function corregir_cuenta_cf_gestion(){
    $sql_sel="select * from con_comprobante, con_comprobante_detalle, con_cuenta_cf where 
                cmp_ges_id!=cfl_ges_id and cmp_id=cde_cmp_id and cde_cfl_id=cfl_id ";
    
    $detalles=  FUNCIONES::objetos_bd_sql($sql_sel);
    $conec=new ADO();
    for ($i = 0; $i < $detalles->get_num_registros(); $i++) {
        $det=$detalles->get_objeto();
        $_cuenta=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_cf where cfl_id='$det->cde_cfl_id'");
        $cuenta=FUNCIONES::objeto_bd_sql("select * from con_cuenta_cf where cfl_codigo='$_cuenta->cfl_codigo' and cfl_ges_id='$det->cmp_ges_id'");
        $sql_update="update con_comprobante_detalle set cde_cfl_id=$cuenta->cfl_id where cde_cmp_id='$det->cde_cmp_id' and cde_mon_id='$det->cde_mon_id' and cde_secuencia='$det->cde_secuencia'";
        echo $sql_update.'<br>';
//        $conec->ejecutar($sql_update, false, false);
        $detalles->siguiente();
    }
    echo $detalles->get_num_registros()."<br>";
}

function actualizar_fecha_scuota(){
    $conec=new ADO();
    $ventas=  FUNCIONES::objetos_bd_sql("select * from venta where ven_sfecha_prog is null limit 100");
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta=$ventas->get_objeto();
        $scuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente' order by ind_id asc limit 1");
        $ven_sfecha_prog=$scuota?$scuota->ind_fecha_programada:'0000-00-00';
        $sql_up="update venta set 
                            ven_sfecha_prog='$ven_sfecha_prog'
                    where ven_id='$venta->ven_id'";
        $conec->ejecutar($sql_up,false,false);
        $ventas->siguiente();
    }
    echo $ventas->get_num_registros().'<br>';
}
function actualizar_plazo(){
    $conec=new ADO();
    $ventas=  FUNCIONES::objetos_bd_sql("select * from venta where ven_cuota_pag>ven_plazo");
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta=$ventas->get_objeto();
        $ucuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_num_correlativo>0 order by ind_id desc limit 1");
        if(!$ucuota){
            $ucuota=new stdClass();
            $ucuota->ind_num_correlativo=0;
        }

        $sql_up="update venta set ven_plazo='$ucuota->ind_num_correlativo' where ven_id='$venta->ven_id'";
//        echo $sql_up.'<br>';
        $conec->ejecutar($sql_up,false,false);
        
        $ventas->siguiente();
    }
    echo $ventas->get_num_registros().'<br>';
}


function actualizar_pagos_capital() {
    $conec=new ADO();
    $ventas=  FUNCIONES::objetos_bd_sql("select * from venta where ven_capital_pag is null limit 10 ");
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta=$ventas->get_objeto();
        $ucuota=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_capital_pagado>0 order by ind_id desc limit 1");
        if(!$ucuota){
            $ucuota=new stdClass();
            $ucuota->ind_num_correlativo=0;
        }

        $pagado = FUNCIONES::total_pagado_venta($venta->ven_id);

        $capital_pag=$pagado->capital;
        $capital_inc=$pagado->incremento;
        $capital_desc=$pagado->descuento;
        $cuota_pag=$ucuota->ind_num_correlativo;

        $sql_up="update venta set 
                ven_cuota_pag='$cuota_pag', ven_capital_pag='$capital_pag',
                ven_capital_inc='$capital_inc', ven_capital_desc='$capital_desc'
        where ven_id='$venta->ven_id'";

        $conec->ejecutar($sql_up,false,false);
        
        $ventas->siguiente();
    }
    echo $ventas->get_num_registros().'<br>';
}

function actualizar_fechas_valor() {
    $conec=new ADO();
    $ventas=  FUNCIONES::objetos_bd_sql("select * from venta where ven_usaldo is null limit 10 ");
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta=$ventas->get_objeto();
        $upago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_ven_id='$venta->ven_id' and vpag_estado='Activo' order by vpag_id desc limit 0,1");
        if(!$upago){
            $upago=new stdClass();
            $upago->vpag_fecha_pago=$venta->ven_fecha;
            $upago->vpag_fecha_valor=$venta->ven_fecha;
            $upago->vpag_saldo_final=$venta->ven_monto_efectivo;
        }
        $sql_up="update venta set ven_ufecha_pago='$upago->vpag_fecha_pago', ven_ufecha_valor='$upago->vpag_fecha_valor', ven_usaldo='$upago->vpag_saldo_final' where ven_id='$venta->ven_id'";
        $conec->ejecutar($sql_up, false, false);
        $ventas->siguiente();
    }
}

function revisar_ventas(){
    $ventas=  FUNCIONES::objetos_bd_sql("select * from venta where ven_numero>0 and ven_id in (63,1341,1743,1764,1775,1881,1898,1915,1920,1922,1931,1938,1962,2101,2289,4071,4098,4109,4703,4710,6692,6997,7332)");
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta=$ventas->get_objeto();
        $tventa=  FUNCIONES::objeto_bd_sql("select * from tmp_ventas where numero_adj=$venta->ven_numero and py=$venta->ven_urb_id");
        if($tventa){
            $monto_efectivo=$tventa->capital_financiado;
            $dif= abs($venta->ven_monto_efectivo-$monto_efectivo);
            if($dif>0.5){
                echo "Diferente $venta->ven_urb_id; $venta->ven_id;<b>$venta->ven_monto_efectivo!=$monto_efectivo</b><a href='gestor.php?mod=venta&tarea=SEGUIMIENTO&id=$venta->ven_id'>SEG</a><br>";
            }
        }
        $ventas->siguiente();
    }
}

function update_bisito() {
    $conec=new ADO();
    $sql="select * from venta where ven_urb_id in (3,4,6) and ven_importado='0' and ven_estado='Pendiente' limit 50";
    $ventas=  FUNCIONES::lista_bd_sql($sql);
    foreach ($ventas as $venta) {
        $nsaldo=$venta->ven_monto_efectivo;
        $uvp=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='Activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
        if($uvp){
            $nsaldo=$uvp->vpag_saldo_final;
        }
        if($uvp->vpag_cob_usu=='admin'){
//            echo "reformular";
            $tmp_venta=  FUNCIONES::objeto_bd_sql("select * from tmp_ventas where py='$venta->ven_urb_id' and numero_adj='$venta->ven_numero'");

            if($tmp_venta){
                $venta->sig_fecha_pago=  FUNCIONES::get_fecha_mysql($tmp_venta->fecha_pago);
            }
            reformular_plan_cuota($venta,false, $conec);
//            echo "<pre>";
//            print_r($tmp_venta);
//            echo "</pre>";
            
            if($tmp_venta){
    //            FUNCIONES::print_pre($tmp_venta);
                $saldo_actual=round(($tmp_venta->saldo_capital.'')*1,2);


                $nsaldo=round($nsaldo,2);
                $txt_msj= "<span style='color:red'>DIF $nsaldo == $tmp_venta->saldo_capital? </span>";
                if($nsaldo!=$saldo_actual){
                    $txt_msj.=";REFORM";
                    if($nsaldo>$saldo_actual){
                        $dif=$nsaldo-$saldo_actual;
                        reformular_plan_cuota_cuadrar($venta,'desc',$dif,$saldo_actual,$nsaldo);
    //                    echo "<BR>DECREMENTAR $dif <br>";
                    }else{
                        $dif=$saldo_actual-$nsaldo;
                        reformular_plan_cuota_cuadrar($venta,'inc',$dif,$saldo_actual,$nsaldo);
    //                    echo "<BR>INCREMENTAR $dif <br>";
                    }
                }else{
                    $txt_msj.= ";NO_REFORM";
                }

            }
        }
        $sql_up_venta="update venta set ven_importado='1' where ven_id=$venta->ven_id";
        $conec->ejecutar($sql_up_venta);
    }
}

function reformular_plan_cuota_cuadrar(&$venta,$tipo,$dif,$saldo_actual,$saldo_calc, &$conec = null) {
    if ($conec == null) {
        $conec = new ADO();
    }
//    $pagado = total_pagado($venta->ven_id);
    $saldo_financiar = $saldo_actual;//$venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;
    $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_capital_pagado>0 and ind_capital_pagado<=ind_capital and ind_estado='Pendiente' order by ind_id asc");
    $nro_inicio = 0;
    $fecha_inicio=  $venta->ven_fecha;
    $afecha=  explode('-', $fecha_inicio);
    $fecha_sig_cuota=  FUNCIONES::sumar_meses($venta->ven_fecha,$venta->ven_rango,$afecha[2]);//FUNCIONES::siguiente_mes($venta->ven_fecha);
    $fecha_prog=$venta->ven_fecha;
    $fecha_pag=$venta->ven_fecha;
    
    $uvp=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='Activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 2");
    if($uvp){
        $fecha_pag=$uvp->vpag_fecha_pago;
    }
    
    if ($uid) {
        $sql_up = "update interno_deuda set ind_saldo_final=$saldo_financiar, ind_estado='Pagado' where ind_id='$uid->ind_id'";
        $conec->ejecutar($sql_up);
        $nro_inicio = $uid->ind_num_correlativo + 1;
//        echo "--ant--$uid->ind_fecha_programada <br>";
        $fecha_inicio=  $uid->ind_fecha_programada;
        $afecha=  explode('-', $fecha_inicio);
        $fecha_sig_cuota=  FUNCIONES::sumar_meses($uid->ind_fecha_programada,$venta->ven_rango,$afecha[2]);
        $fecha_prog=$uid->ind_fecha_programada;
//        echo "**post--$fecha_sig_cuota <br>";
    }
    if (!$nro_inicio) {
        $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pagado' order by ind_id desc limit 0,1");
        if ($uid) {
            $nro_inicio = $uid->ind_num_correlativo + 1;
            $fecha_inicio=  $uid->ind_fecha_programada;
            $afecha=  explode('-', $fecha_inicio);
            $fecha_sig_cuota=  FUNCIONES::sumar_meses($uid->ind_fecha_programada,$venta->ven_rango,$afecha[2]);
            $fecha_prog=$uid->ind_fecha_programada;
        } else {
            $nro_inicio = 1;
        }
    }
    
    if($venta->sig_fecha_pago){
        $fecha_inicio=FUNCIONES::sumar_meses($venta->sig_fecha_pago,"-$venta->ven_rango",$afecha[2]);;
        $fecha_sig_cuota=  $venta->sig_fecha_pago;
    }

    $sql_del = "delete from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente'";
    $conec->ejecutar($sql_del);
    $fecha_cre=date('Y-m-d');
    if($dif>0){
        $capital_desc=0;
        $capital_inc=0;
        if($tipo=='desc'){
            $capital_desc=$dif;
        }elseif($tipo=='inc'){
            $capital_inc=$dif;
        }
        
        $insert_dif="insert into interno_deuda (
                            ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_usu_id,
                            ind_moneda,ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,
                            ind_estado,ind_interes_pagado,ind_capital_pagado,ind_monto_pagado,ind_saldo_final,
                            ind_fecha_pago,ind_dias_interes,ind_concepto,ind_observacion,ind_form,ind_form_pagado,
                            ind_mora,ind_mora_pagado,ind_tipo,ind_fecha_cre,ind_estado_parcial,ind_monto_parcial,
                            ind_costo_pagado,ind_capital_inc,ind_capital_desc,ind_interes_desc,ind_form_desc
                        )values(
                            'venta','$venta->ven_id','-1','$venta->ven_int_id','$fecha_cre','admin',
                            '$venta->ven_moneda','0','0',0,'$saldo_calc','$fecha_prog',
                            'Pagado','0','0','0',$saldo_actual,
                            '$fecha_pag','0','Adecuacion de Saldo $venta->ven_concepto','',0,0,
                            0,0,'pcuota','$fecha_cre','listo','0',
                            0,'$capital_inc','$capital_desc',0,0
                                
                        )";
        $conec->ejecutar($insert_dif,false);
        $cua_ind_id=  mysql_insert_id();
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
                            vpag_interes_desc,vpag_form_desc,vpag_recibo,vpag_importado
                        )values(
                            '$venta->ven_id','$codigo','$fecha_pag','$fecha_inicio','$venta->ven_int_id','$venta->ven_moneda',
                            '$saldo_calc',0,0,0,0,
                            0,0,'$saldo_actual','Activo','',
                            '','$cua_ind_id','0',
                            '','','','','',
                            '','','','',
                           '$fecha_cre','admin','admin','','0',
                            0,'','',$capital_inc,'$capital_desc',
                            0,0,'','1'
                        )";
//        echo "$insert_pago";
        $conec->ejecutar($insert_pago,false);
    }
    
    $interes_anual = $venta->ven_val_interes;
    $plan_data = array(
        'int_id' => $venta->ven_int_id,
        'saldo' => $saldo_financiar,
        'interes' => $interes_anual,
        'tipo' => 'cuota',
        'plazo' => $venta->ven_plazo,
        'cuota' => $venta->ven_cuota,
        'moneda' => $venta->ven_moneda,
        'concepto' => $venta->ven_concepto,
        'fecha' => date('Y-m-d'),
        'fecha_inicio' => $fecha_inicio,
        'fecha_pri_cuota' => $fecha_sig_cuota,
        'usuario' => 'admin',
        'tabla' => 'venta',
        'nro_cuota_inicio' => $nro_inicio,
        'tabla_id' => $venta->ven_id,
        'ind_tipo' => 'pcuota',
        'val_form' => 0,
        'rango' => $venta->ven_rango,
    );
//    echo "-------------- PLAN CUADRAR <br>";
//    FUNCIONES::print_pre($plan_data);
//    $nro_cuotas = generar_plan_pagos($plan_data, $conec); //
    $resplan = generar_plan_pagos($plan_data, $conec); //
//    $cuota = FUNCIONES::get_cuota($saldo_financiar, $interes_anual, $plazo, $aplazo[$plan->Plazo]);
    $set_estado="";
    if(round($saldo_financiar,2)<=0){
        $set_estado=", ven_estado='Pagado'";
    }
    $sql_up_venta = "update venta set ven_plazo='$resplan->nro_cuota', ven_ufecha_prog='$resplan->ufecha' $set_estado where ven_id=$venta->ven_id";
    $venta->ven_plazo=$resplan->nro_cuota;
    $conec->ejecutar($sql_up_venta);
    
}


function reformular_plan_cuota(&$venta, $cuadrar_venta = false, &$conec = null) {
    if ($conec == null) {
        $conec = new ADO();
    }
    $pagado = total_pagado($venta->ven_id);
    $saldo_financiar = $venta->ven_monto_efectivo - $pagado->capital - $pagado->descuento + $pagado->incremento;
//    echo "select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_capital_pagado>0 and ind_capital_pagado<=ind_capital and ind_estado='Pendiente'";
    $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_capital_pagado>0 and ind_capital_pagado<=ind_capital and ind_estado='Pendiente'");
    $nro_inicio = 0;
    $fecha_inicio = $venta->ven_fecha;
    $afecha = explode('-', $fecha_inicio);
    $fecha_sig_cuota = FUNCIONES::sumar_meses($venta->ven_fecha, $venta->ven_rango, $afecha[2]); //FUNCIONES::siguiente_mes($venta->ven_fecha);
//    echo "<pre>";
//    print_r($uid);
//    echo "</pre>";
    if ($uid) {
        $sql_up = "update interno_deuda set ind_saldo_final=$saldo_financiar, ind_estado='Pagado' where ind_id='$uid->ind_id'";
        $conec->ejecutar($sql_up);
        $nro_inicio = $uid->ind_num_correlativo + 1;
//        echo "--ant--$uid->ind_fecha_programada <br>";
        $fecha_inicio = $uid->ind_fecha_programada;
        $afecha = explode('-', $fecha_inicio);
        $fecha_sig_cuota = FUNCIONES::sumar_meses($uid->ind_fecha_programada, $venta->ven_rango, $afecha[2]);
//        $fecha_sig_cuota=  FUNCIONES::siguiente_mes($uid->ind_fecha_programada);
//        echo "**post--$fecha_sig_cuota <br>";
    } else {
        if ($cuadrar_venta) {
            return false;
        }
    }
    if (!$nro_inicio) {
        $uid = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id' and ind_estado='Pagado' order by ind_id desc limit 0,1");
        if ($uid) {
            $nro_inicio = $uid->ind_num_correlativo + 1;
            $fecha_inicio = $uid->ind_fecha_programada;
            $afecha = explode('-', $fecha_inicio);
            $fecha_sig_cuota = FUNCIONES::sumar_meses($uid->ind_fecha_programada, $venta->ven_rango, $afecha[2]);
        } else {
            $nro_inicio = 1;
        }
    }

    if ($venta->sig_fecha_pago) {
        $fecha_inicio = FUNCIONES::sumar_meses($venta->sig_fecha_pago, "-$venta->ven_rango", $afecha[2]);
        ;
        $fecha_sig_cuota = $venta->sig_fecha_pago;
    }

    $sql_del = "delete from interno_deuda where ind_tabla='venta' and ind_tabla_id=$venta->ven_id and ind_estado='Pendiente'";
    $conec->ejecutar($sql_del);

    $interes_anual = $venta->ven_val_interes;

    $plan_data = array(
        'int_id' => $venta->ven_int_id,
        'saldo' => $saldo_financiar,
        'interes' => $interes_anual,
        'tipo' => 'cuota',
        'plazo' => $venta->ven_plazo,
        'cuota' => $venta->ven_cuota,
        'moneda' => $venta->ven_moneda,
        'concepto' => $venta->ven_concepto,
        'fecha' => date('Y-m-d'),
        'fecha_inicio' => $fecha_inicio,
        'fecha_pri_cuota' => $fecha_sig_cuota,
        'usuario' => 'admin',
        'tabla' => 'venta',
        'nro_cuota_inicio' => $nro_inicio,
        'tabla_id' => $venta->ven_id,
        'ind_tipo' => 'pcuota',
        'val_form' => 0,
        'rango' => $venta->ven_rango,
    );
//    echo "-------------- PLAN CUOTA <br>";
//    FUNCIONES::print_pre($plan_data);
//    $nro_cuotas = generar_plan_pagos($plan_data, $conec); //
    $resplan = generar_plan_pagos($plan_data, $conec); //
//    $cuota = FUNCIONES::get_cuota($saldo_financiar, $interes_anual, $plazo, $aplazo[$plan->Plazo]);
    $sql_up_venta = "update venta set ven_plazo='$resplan->nro_cuota', ven_ufecha_prog='$resplan->ufecha' where ven_id=$venta->ven_id";
//    echo "res plazo : $nro_cuotas<br>";
    $venta->ven_plazo = $resplan->nro_cuota;
    $conec->ejecutar($sql_up_venta);

    guardar_fecha_valor($venta, $fecha_inicio);
}

function generar_plan_pagos($parametros, &$conec = null) {
    /*
      //            'int_id'=>$par->int_id,
      //            'saldo'=>$saldo_financiar,
      //            'interes'=>$par->interes_anual,
      //            'plazo'=>$_mp,
      //            'cuota'=>$_cm,
      //            'moneda'=>$par->moneda,
      //            'concepto'=>$par->concepto,
      //            'fecha'=>$par->fecha,
      //            'fecha_pri_pago'=>  $par->fecha_pri_pago,
      //            'usuario'=>  $usuario_id,
      //            'tabla'=> 'venta',
      //            'nro_cuota_inicio'=>  1,
      //            'tabla_id'=>  $par->ven_id,
      //            'ind_tipo'=>  'pcuota'
     */

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
            'frecuencia' => 'dia_mes'
        );
        $lista_pagos = plan_de_pagos($data);
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
            'frecuencia' => 'dia_mes'
        );
        $lista_pagos = FUNCIONES::plan_de_pagos($data);
    }

//            FUNCIONES::print_pre($lista_pagos);
//            return;
    $sql_ins_id = "insert into interno_deuda(
                            ind_tabla,ind_tabla_id,ind_num_correlativo,ind_int_id,ind_fecha,ind_moneda,
                            ind_interes,ind_capital,ind_monto,ind_saldo,ind_fecha_programada,ind_estado,
                            ind_dias_interes,ind_concepto,ind_usu_id,ind_tipo,ind_fecha_cre,ind_form
                        )values";
    $fecha_cre = date('Y-m-d H:i:s');
    $i = 0;
    $nro_cuota = 0;
    $ufecha=$par->fecha;
    foreach ($lista_pagos as $fila) {
        if ($i > 0) {
            $sql_ins_id.= ",";
        }
        $sql_ins_id.= "(
                            'venta','$par->tabla_id','$fila->nro_cuota','$par->int_id','$par->fecha','$par->moneda',
                            '$fila->interes','$fila->capital','$fila->monto','$fila->saldo','$fila->fecha','Pendiente',
                            '$fila->dias','Cuota Nro $fila->nro_cuota - $par->concepto','$par->usuario','pcuota','$fecha_cre','$par->val_form'
                        )";
        $nro_cuota = $fila->nro_cuota;
        $i++;
        $ufecha=$fila->fecha;
    }
//            $nro_cuota_inicio++;
    if (count($lista_pagos) > 0) {
        $conec->ejecutar($sql_ins_id);
    }

    return (object)array('nro_cuota'=>$nro_cuota,'ufecha'=>$ufecha);
}

function guardar_fecha_valor($venta, $fecha_valor) {
    $conec = new ADO();
//    $fecha_valor=  FUNCIONES::get_fecha_mysql($_POST[nfecha_valor]);
//    $fecha_pago=date('Y-m-d');
    $codigo = FUNCIONES::fecha_codigo();
    $upago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='activo' and vpag_ven_id=$venta->ven_id order by vpag_id desc limit 1");
    if (!$upago) {
        $upago = new stdClass();
        $upago->vpag_saldo_final = $venta->ven_monto_efectivo;
        $upago->vpag_fecha_valor = $venta->ven_fecha;
        $upago->vpag_fecha_pago = $venta->ven_fecha;
    }
    $fecha_cre = date('Y-m-d H:i:s');
    $insert_pago = "insert into venta_pago (
                        vpag_ven_id,vpag_codigo,vpag_fecha_pago,vpag_fecha_valor,vpag_int_id,vpag_moneda,
                        vpag_saldo_inicial,vpag_dias_interes,vpag_interes,vpag_capital,vpag_form,
                        vpag_mora,vpag_monto,vpag_saldo_final,vpag_estado,vpag_interes_ids,
                        vpag_interes_montos,vpag_capital_ids,vpag_capital_montos,
                        vpag_form_ids,vpag_form_montos,vpag_mora_ids,vpag_mora_montos,vpag_mora_con_ids,
                        vpag_mora_con_montos,vpag_mora_gen_ids,vpag_mora_gen_montos,vpag_mora_gen_dias,
                        vpag_fecha_cre,vpag_usu_cre,vpag_cob_usu,vpag_cob_codigo,vpag_cob_aut,
                        vpag_costo,vpag_costo_ids,vpag_costo_montos,vpag_capital_inc,vpag_capital_desc,
                        vpag_interes_desc,vpag_form_desc,vpag_recibo,vpag_importado
                    )values(
                        '$venta->ven_id','$codigo','$upago->vpag_fecha_pago','$fecha_valor','$venta->ven_int_id','$venta->ven_moneda',
                        '$upago->vpag_saldo_final',0,0,0,0,
                        0,0,'$upago->vpag_saldo_final','Activo','',
                        '','','0',
                        '','','','','',
                        '','','','',
                       '$fecha_cre','admin','admin','','0',
                        0,'','','0','0',
                        0,0,'','1'
                    )";
//        $this->barra_opciones($venta);
//        echo "<br><br>";
    $conec->ejecutar($insert_pago, false);
}

function total_pagado($ven_id) {
    $sql_pag = "select sum(ind_interes_pagado)as interes, sum(ind_capital_pagado) as capital, sum(ind_monto_pagado) as monto, sum(ind_capital_desc) as descuento, sum(ind_capital_inc) as incremento 
                        from interno_deuda where ind_tabla='venta' and ind_tabla_id=$ven_id 
                ";
    $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
    return $pagado;
}
