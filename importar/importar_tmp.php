<?php

require_once 'excel/reader.php';
include 'conexion.php';
_db::open();

importar_tmp_ventas('urb_bisito');
importar_tmp_ventas('urb_lujan');
importar_tmp_ventas('urb_norte');

function importar_tmp_ventas($urb) {

    $pagina = 0;
    $data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('CP1251');
    $data->read("$urb.xls");
    $filasTotal = $data->sheets[$pagina]['numRows'];

    echo 'Total Excel: ' . $filasTotal . "<br>";
    

    $reg=0;
    $num = 0;
    $insert = "insert into tmp_ventas (py,nombre_py,numero_adj,uv,mza,lote,nombre,forma_pago,tipo_cartera,dias_i,credito,fecha_venc_ini,ult_fecha_venc,precio,cuota_inicial,capital,saldo_pagar,saldo_actual,aportado,saldo,amortizacion_aport,superficie,monto_mora,fecha_ult_pago,ultpago,ultimas_cuotas,cuotas_deudas) values";
    $sql_insert = $insert;
    $show = false;
    for ($i = 2; $i <= $filasTotal; $i++) {
        if ($num == 440) {
            if ($show) {
                echo $sql_insert . ';<br>';
            }
            _db::execute($sql_insert);
            $sql_insert = $insert;
            $num = 0;
        }
        
        $col = 1;
        $py = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $nombre_py = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $numero_adj = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $uv = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $mza = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $lote = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $nombre = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $forma_pago = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $tipo_cartera = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $dias_i = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $credito = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $fecha_venc_ini = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $ult_fecha_venc = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $precio = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $cuota_inicial = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $capital = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $saldo_pagar = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $aportado = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $saldo = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $amortizacion_aport = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $superficie = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $monto_mora = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $fecha_ult_pago = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $ultpago = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $ultimas_cuotas = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $cuotas_deudas = trim($data->sheets[$pagina]['cells'][$i][$col++]);
        $saldo_actual= ($precio*1)-($cuota_inicial*1)-($amortizacion_aport*1);
        if($py!=''){
            if ($num > 0) {
                $sql_insert.=',';
            }
            $sql_insert.="('$py','$nombre_py','$numero_adj','$uv','$mza','$lote','$nombre','$forma_pago','$tipo_cartera','$dias_i','$credito','$fecha_venc_ini','$ult_fecha_venc','$precio','$cuota_inicial','$capital','$saldo_pagar','$saldo_actual','$aportado','$saldo','$amortizacion_aport','$superficie','$monto_mora','$fecha_ult_pago','$ultpago','$ultimas_cuotas','$cuotas_deudas')";
            $num++;
            $reg++;
        }

//    echo "$cuenta - $moneda - $nivel <br>";
    }
    if ($num > 0) {
        if ($show) {
            echo $sql_insert . ';<br>';
        }
        _db::execute($sql_insert);
    }
    echo "<BR>TOTAL REGISTRADOS $reg <br><br>";
}
