<?php

ini_set('memory_limit', '2048M');
ini_set('display_errors', 'On');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');

//$sql = "SELECT ven_id,urb_nombre AS proyecto,man_nro as mz,lot_nro as lt, ven_monto,
//    concat(int_nombre,' ',int_apellido)as cliente,ven_fecha,ven_numero,
//    ven_res_anticipo,ven_valor,ven_decuento,
//    (SUM( vpag_monto )+ven_res_anticipo) AS pagado, (
//ven_monto - ven_res_anticipo - SUM( vpag_monto )
//) AS saldo
//FROM venta
//LEFT JOIN venta_pago ON ( ven_id = vpag_ven_id ) 
//inner join interno on (int_id=ven_int_id)
//inner join lote on (ven_lot_id=lot_id)
//inner join manzano on (lot_man_id=man_id)
//inner join urbanizacion on (ven_urb_id=urb_id)
//WHERE ven_numero LIKE  'NET-%'
//AND vpag_usu_cre =  'admin'
//AND vpag_fecha_pago <=  '2017-04-22'
//AND vpag_estado =  'Activo'
//GROUP BY ven_id
//
//union
//
//SELECT ven_id,urb_nombre AS proyecto,man_nro as mz,lot_nro as lt, ven_monto,
//    concat(int_nombre,' ',int_apellido)as cliente,ven_fecha,ven_numero,
//    ven_res_anticipo,ven_valor,ven_decuento,
//    (SUM( vpag_monto )+ven_res_anticipo) AS pagado, (
//ven_monto - ven_res_anticipo - SUM( vpag_monto )
//) AS saldo
//FROM venta
//LEFT JOIN venta_pago ON ( ven_id = vpag_ven_id ) 
//inner join interno on (int_id=ven_int_id)
//inner join lote on (ven_lot_id=lot_id)
//inner join manzano on (lot_man_id=man_id)
//inner join urbanizacion on (ven_urb_id=urb_id)
//WHERE ven_numero LIKE  'NET-%'
//AND vpag_fecha_pago <=  '2017-04-22'
//AND vpag_estado =  'Activo'
//and vpag_ven_id is null
//GROUP BY ven_id
//";

$sql = "SELECT ven_id,urb_nombre AS proyecto,man_nro as mz,lot_nro as lt, ven_monto,
    concat(int_nombre,' ',int_apellido)as cliente,ven_fecha,ven_numero,
    ven_res_anticipo,ven_valor,ven_decuento
FROM venta
inner join interno on (int_id=ven_int_id)
inner join lote on (ven_lot_id=lot_id)
inner join manzano on (lot_man_id=man_id)
inner join urbanizacion on (ven_urb_id=urb_id)
WHERE ven_numero LIKE  'NET-%'";

$ventas = FUNCIONES::lista_bd_sql($sql);

$nombre_archivo = "SALDOS DE VENTAS MLM AL 22042017.csv";

header("Content-Type: text/csv");
header("content-disposition: attachment;filename=" . $nombre_archivo);

echo '"#";"VENTA";"PROYECTO";"MZ";"LOTE";"CLIENTE";"FECHA VENTA";"PRECIO LOTE";"DESCUENTO";"MONTO TOTAL";"CUOTA INICIAL";"SALDO A FINANCIAR";"PAGADO";"SALDO";"CODIGO NETZEN"' . "\n";
$i = 0;
foreach ($ventas as $pag) {
    
    $pagado = FUNCIONES::atributo_bd_sql("select sum(vpag_monto)as campo from venta_pago
        where vpag_estado='Activo' 
        and vpag_fecha_pago<='2017-04-22' 
        AND vpag_usu_cre =  'admin'
        and vpag_ven_id=$pag->ven_id")*1;
    $pagado += $pag->ven_res_anticipo;
    $saldo = $pag->ven_monto - $pagado;
    
    $i++;
    $datos = array(
        $i,
        $pag->ven_id,
        $pag->proyecto,
        $pag->mz,
        $pag->lt,
        $pag->cliente,
        FUNCIONES::get_fecha_latina($pag->ven_fecha)
        , number_format($pag->ven_valor, 2, '.', '')
        , number_format($pag->ven_decuento, 2, '.', '')
        , number_format($pag->ven_monto, 2, '.', '')
        , number_format($pag->ven_res_anticipo, 2, '.', '')
        , number_format($pag->ven_monto - $pag->ven_res_anticipo, 2, '.', '')
        , number_format($pagado, 2, '.', '')
        , number_format($saldo, 2, '.', ''),
        substr($pag->ven_numero, 4)
    );

    echo obtener_linea($datos);
}

function obtener_linea($arr_datos) {
    $s = '"' . implode('";"', $arr_datos) . '"' . "\n";
    return $s;
}

?>