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

//$ventas_sin_bra = FUNCIONES::lista_bd_sql("select * from venta
//left join comision_cobro on (ven_id=comcob_ven_id)
//where comcob_ven_id is null
//and ven_multinivel = 'si'
//and ven_estado in ('Pendiente','Pagado');");
//
//foreach ($ventas_sin_bra as $ven) {
//    $params_mlm = FUNCIONES::objeto_bd_sql("select * from lote_multinivel 
//        where lm_lot_id=$ven->ven_lot_id");
//    if ($params_mlm) {
//        $sql_upd = "update venta set 
//            ven_bono_bra=$params_mlm->lm_bra 
//            where ven_id=$ven->ven_id";
//        echo $sql_upd."<br/>";
//        FUNCIONES::bd_query($sql_upd);
//        $data = array('venta' => $ven->ven_id, 'vendedor' => $ven->ven_vdo_id);
//        MLM::insertar_comisiones_cobro($data);
//    } else {
//        echo "No existen parametros para lote => $ven->ven_lot_id...<br/>";
//    }
//}

//$ventas_sin_bra = FUNCIONES::lista_bd_sql("SELECT * 
//FROM venta
//WHERE ven_multinivel =  'si'
//AND (
//ven_bono_bra =0
//OR ven_bono_inicial =0
//)
//AND ven_estado
//IN (
//'Pendiente',  'Pagado'
//)");

if (!isset($_GET[venta])) {
//    echo "ingrese la venta para actualizar....";
//    return false;
} else {
    $ven_id = $_GET[venta]*1;
    $f_venta = " AND ven_id={$ven_id}";
}

$ventas_sin_bra = FUNCIONES::lista_bd_sql("SELECT * 
FROM venta
WHERE ven_multinivel =  'si'
$f_venta");

foreach ($ventas_sin_bra as $ven) {
    $params_mlm = FUNCIONES::objeto_bd_sql("select * from lote_multinivel 
        where lm_lot_id=$ven->ven_lot_id");
    if ($params_mlm) {
        
        $bono_inicial = ($ven->ven_res_anticipo > $params_mlm->lm_comision_base) ? $params_mlm->lm_comision_base : $ven->ven_res_anticipo;
        
        $monto_red = $params_mlm->lm_bra + $bono_inicial;
        $sql_upd = "update venta set 
            ven_bono_bra=$params_mlm->lm_bra,
            ven_bono_inicial=$bono_inicial,
            ven_monto_red=$monto_red,
            ven_saldo_red=$monto_red
            where ven_id=$ven->ven_id";
        echo $sql_upd . "<br/>";
        FUNCIONES::bd_query($sql_upd);

        $comcob_bra = FUNCIONES::objeto_bd_sql("select * from comision_cobro
            where comcob_ven_id=$ven->ven_id limit 0,1");

        if ($comcob_bra === NULL) {
            $data = array('venta' => $ven->ven_id, 'vendedor' => $ven->ven_vdo_id);
            MLM::insertar_comisiones_cobro($data);
        }
    } else {
        echo "No existen parametros para lote => $ven->ven_lot_id...<br/>";
    }
}
?>