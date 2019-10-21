<?php
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';

_db::open();

//revisar_ventas();

//anular_ventas();

//isnertar_lote_venta();
insertar_venta_lotes_ruben();
function insertar_venta_lotes_ruben() {
    $conec=new ADO();
    $sventas=  FUNCIONES::lista_bd_sql("select * from tmp_ventas_ruben");
    
    $fecha_cre=date('Y-m-d');
    $nro=1;
    $ven_id=8603;
//    $metro=5.29;
    $ven_fecha='2017-03-17';
    $sum_superficie=0;
    $sum_valor=0;
    $lot_ids='';
    for ($i = 0; $i < count($sventas); $i++) {
        $sven=$sventas[$i];
        $lote=  FUNCIONES::objeto_bd_sql("select * from lote,manzano where man_urb_id=2 and man_nro='$sven->mz' and lot_nro='$sven->lote' and lot_man_id=man_id ");
        $superficie=   $sven->superficie;
        $valor=  $sven->monto;
        $metro=$sven->precio;
        if($lote){
            if($lote->lot_estado=='Disponible'){
                $concepto=  FUNCIONES::get_concepto($lote->lot_id);
                $sql_insert="insert into venta_lote (
                        vlot_orden,vlot_ven_id,vlot_lot_id,vlot_superficie,vlot_metro,vlot_valor,vlot_concepto
                    )values(
                        $nro,$ven_id,$lote->lot_id,$superficie,$metro,$valor,'$concepto'
                    )";
                echo "$sql_insert;<br>";
                
                $sum_superficie+=$superficie;
                $sum_valor+=$valor;
                if($nro>1){
                    $lot_ids.=',';
                }
                $lot_ids.=$lote->lot_id;
                
                $nro++;
                
            }else{
                echo "-- EXISTE LOTE M$sven->mz L$sven->lote, $lote->lot_id, $lote->lot_estado<br>";
            }
            
            
            
        }else{
            echo "-- NO EXISTE LOTE M$sven->mz L$sven->lote<br>";
        }
    }
    $res_anticipo=0;
    $ven_monto_efectivo=$sum_valor-$res_anticipo;
    $costo=$sum_superficie*14.03;
    $costo_cub=$costo-$res_anticipo;
    $costo_pag=0;
    $sql_venta="update venta set 
                    ven_superficie='$sum_superficie', ven_metro='$metro', ven_fecha='$ven_fecha',ven_moneda=2,
                    ven_lot_ids='$lot_ids', ven_valor='$sum_valor',ven_monto='$sum_valor',
                    ven_res_anticipo='$res_anticipo', ven_monto_efectivo='$ven_monto_efectivo',
                    ven_estado='Pendiente', ven_ufecha_valor='$ven_fecha',ven_cuota_pag=0,
                    ven_capital_pag=0, ven_usaldo='$ven_monto_efectivo',ven_sfecha_prog='0000-00-00',
                    ven_costo='$costo', ven_costo_cub='$costo_cub', ven_costo_pag='$costo_pag'
                where ven_id=$ven_id";
    echo "$sql_venta;<br>";
}
function isnertar_lote_venta() {
    $conec=new ADO();
    $sventas=  FUNCIONES::lista_bd_sql("select * from tmp_venta_sion");
    
    $fecha_cre=date('Y-m-d');
    $nro=1;
    $ven_id=5620;
    $metro=5.29;
    $ven_fecha='2015-01-02';
    $sum_superficie=0;
    $sum_valor=0;
    $lot_ids='';
    for ($i = 0; $i < count($sventas); $i++) {
        $sven=$sventas[$i];
        $lote=  FUNCIONES::objeto_bd_sql("select * from lote,manzano where man_urb_id=4 and man_nro='$sven->mz' and lot_nro='$sven->lote' and lot_man_id=man_id ");
        $superficie=  str_replace(',', '', $sven->superficie);
        $valor=  str_replace(',', '', $sven->valor);
        if($lote){
            if($lote->lot_estado=='Vendido'){
                $concepto=  FUNCIONES::get_concepto($lote->lot_id);
                $sql_insert="insert into venta_lote (
                        vlot_orden,vlot_ven_id,vlot_lot_id,vlot_superficie,vlot_metro,vlot_valor,vlot_concepto
                    )values(
                        $nro,$ven_id,$lote->lot_id,$superficie,$metro,$valor,'$concepto'
                    )";
                echo "$sql_insert;<br>";
                
                $sum_superficie+=$superficie;
                $sum_valor+=$valor;
                if($nro>1){
                    $lot_ids.=',';
                }
                $lot_ids.=$lote->lot_id;
                
                $nro++;
                
            }else{
                echo "-- EXISTE LOTE M$sven->mz L$sven->lote, $lote->lot_id, $lote->lot_estado<br>";
            }
            
            
            
        }else{
            echo "-- NO EXISTE LOTE M$sven->mz L$sven->lote<br>";
        }
    }
    $res_anticipo=0;
    $ven_monto_efectivo=$sum_valor-$res_anticipo;
    $costo=$sum_superficie*7.10;
    $costo_cub=$costo-$res_anticipo;
    $costo_pag=0;
    $sql_venta="update venta set 
                    ven_superficie='$sum_superficie', ven_metro='$metro', ven_fecha='$ven_fecha',ven_moneda=2,
                    ven_lot_ids='$lot_ids', ven_valor='$sum_valor',ven_monto='$sum_valor',
                    ven_res_anticipo='$res_anticipo', ven_monto_efectivo='$ven_monto_efectivo',
                    ven_estado='Pendiente', ven_ufecha_valor='$ven_fecha',ven_cuota_pag=0,
                    ven_capital_pag=0, ven_usaldo='$ven_monto_efectivo',ven_sfecha_prog='0000-00-00',
                    ven_costo='$costo', ven_costo_cub='$costo_cub', ven_costo_pag='$costo_pag'
                where ven_id=$ven_id";
    echo "$sql_venta;<br>";
}


function anular_ventas() {
    $conec=new ADO();
    $sventas=  FUNCIONES::lista_bd_sql("select * from tmp_venta_sion");
    include_once 'clases/registrar_comprobantes.class.php';
    include_once 'clases/recibo.class.php';
    $fecha_cre=date('Y-m-d');
    for ($i = 0; $i < count($sventas); $i++) {
        $sven=$sventas[$i];
        $lote=  FUNCIONES::objeto_bd_sql("select * from lote,manzano where man_urb_id=4 and man_nro='$sven->mz' and lot_nro='$sven->lote' and lot_man_id=man_id ");
        if($lote){
            if($lote->lot_estado=='Vendido'){
                $venta=  FUNCIONES::objeto_bd_sql("select * from venta where ven_lot_id='$lote->lot_id'");
                
//                $bool=COMPROBANTES::anular_comprobante('venta', $venta->ven_id);
                $sql_up="update venta set ven_estado='Anulado' where ven_id='$venta->ven_id'";
                $conec->ejecutar($sql_up, false, false);
                
                $sql_up_cmp = "update con_comprobante SET cmp_eliminado = 'Si', cmp_fecha_modi = '$fecha_cre' Where cmp_tabla ='venta' and cmp_tabla_id ='$venta->ven_id'";
                $conec->ejecutar($sql_up_cmp, false, false);
//                $cliente=  FUNCIONES::interno_nombre($venta->ven_int_id);
//                echo "-- LOTE VENDIDO, $venta->ven_id, M$sven->mz L$sven->lote, $lote->lot_id, $lote->lot_estado, $cliente<br>";
                $sql_del="delete from interno_deuda where ind_tabla='venta' and ind_tabla_id='$venta->ven_id'";
                $conec->ejecutar($sql_del, false, false);
                $vpagos=  FUNCIONES::lista_bd_sql("select * from venta_pago where vpag_ven_id='$venta->ven_id' and vpag_estado='Activo'");
                foreach ($vpagos as $pago) {
                    $pag_id=$pago->vpag_id;
                    
//                    $bool=COMPROBANTES::anular_comprobante('venta_pago', $pago->vpag_id);
                    $sql_up_cmp = "update con_comprobante SET cmp_eliminado = 'Si', cmp_fecha_modi = '$fecha_cre' Where cmp_tabla ='venta_pago' and cmp_tabla_id ='$pago->vpag_id'";
                    $conec->ejecutar($sql_up_cmp, false, false);
                    
                    RECIBO::anular($pago->vpag_recibo);
                    
                    $sql_up="update venta_pago set vpag_estado='Anulado', vpag_fecha_anu='$fecha_cre',vpag_usu_anu='admin', vpag_observacion_anu='Por orden de Gabriel' where vpag_id='$pag_id'";
                    $conec->ejecutar($sql_up,false,false);

                    $sql_up="update interno_deuda_pago set idp_estado='Anulado' where  idp_vpag_id='$pag_id'";
                    $conec->ejecutar($sql_up,false,false);
                }
            }else{
                echo "-- EXISTE LOTE M$sven->mz L$sven->lote, $lote->lot_id, $lote->lot_estado<br>";
            }
        }else{
            echo "NO EXISTE LOTE M$sven->mz L$sven->lote<br>";
        }
    }
}
function revisar_ventas() {
    $sventas=  FUNCIONES::lista_bd_sql("select * from tmp_venta_sion");
    for ($i = 0; $i < count($sventas); $i++) {
        $sven=$sventas[$i];
        $lote=  FUNCIONES::objeto_bd_sql("select * from lote,manzano where man_urb_id=4 and man_nro='$sven->mz' and lot_nro='$sven->lote' and lot_man_id=man_id ");
        if($lote){
            if($lote->lot_estado=='Vendido'){
                $venta=  FUNCIONES::objeto_bd_sql("select * from venta where ven_lot_id='$lote->lot_id'");
                $cliente=  FUNCIONES::interno_nombre($venta->ven_int_id);
                echo "-- LOTE VENDIDO, $venta->ven_id, M$sven->mz L$sven->lote, $lote->lot_id, $lote->lot_estado, $cliente<br>";
            }else{
                echo "-- EXISTE LOTE M$sven->mz L$sven->lote, $lote->lot_id, $lote->lot_estado<br>";
            }
            
        }else{
            
            echo "NO EXISTE LOTE M$sven->mz L$sven->lote<br>";
        }
    }
}