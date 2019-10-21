<?php
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'excel/reader.php';
require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');
require_once("clases/log.class.php");

function provisionar_ventas(){
    $sql = "select * from venta where ven_multinivel='si'";
    
    $ventas = FUNCIONES::lista_bd_sql($sql);
    
    foreach ($ventas as $venta) {
        $bono_inicial = $venta->ven_res_anticipo;
        $algun_bra = FUNCIONES::objeto_bd_sql("select * from comision 
            where com_ven_id=$venta->ven_id 
            and com_tipo='BRA'
            and com_estado in ('Pendiente','Pagado') 
            order by com_id asc 
            limit 0,1");
        
        $bra_venta = 0;
        if ($algun_bra) {
            $bra_venta = $algun_bra->com_monto * 420;
            
            $monto_vendedor = $bra_venta / 7;
            $monto_cuota = $monto_vendedor / 60;
            $sql_insert = "insert into comision_cobro(comcob_ven_id,comcob_vdo_id,
                            comcob_monto_total,comcob_pagado,
                            comcob_saldo,comcob_monto_cuota)
                            values('$venta->ven_id','$venta->ven_vdo_id',
                            '$monto_vendedor','0',
                            '$monto_vendedor','$monto_cuota');";
            FUNCIONES::bd_query($sql_insert);
        } else {
            FUNCIONES::eco("NO EXISTE NINGUN BRA PARA VENTA $venta->ven_id<BR>");
        }
        
        $monto_red = $bono_inicial + $bra_venta;
        $pagado_red = FUNCIONES::atributo_bd_sql("select sum(com_monto)as campo from comision 
            where com_estado='Pagado'
            and com_ven_id=$venta->ven_id")*1;
        
        $saldo_red = $monto_red - $pagado_red;
        
        $sql_upd = "update venta set 
            ven_bono_inicial=$bono_inicial, 
            ven_bono_bra=$bra_venta,
            ven_monto_red=$monto_red,
            ven_saldo_red=$saldo_red
            where ven_id=$venta->ven_id";
        
        FUNCIONES::bd_query($sql_upd);
    }
}

provisionar_ventas();

?>