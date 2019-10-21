
<!--<iframe width="100%" height="315" src="http://youtu.be/7thp0N1C7lA" frameborder="0" allowfullscreen></iframe>
<iframe width="100%" height="315" src="//www.youtube.com/embed/AvfLTGZtoGY" frameborder="0" allowfullscreen></iframe>-->

<?php
//return;
//$year = 2012;
//tipo_cambio(2012);
//tipo_cambio(2013);
//tipo_cambio(2014);
//tipo_cambio(2015,1,8);
tipo_cambio(2014);
tipo_cambio(2015);

function tipo_cambio($year, $mes_ini = 1, $mes_fin = 12) {
    $tc_usd = '6.96';
    $tc_usd_compra = '6.85';
    $tc_usd_venta = '6.96';
    $tc_ufv = '1.00';
    $tc_real = '1.00';
    for ($m = $mes_ini; $m <= $mes_fin; $m++) {
        for ($i = 1; $i <= dia_max($year, $m); $i++) {
            $insert_usd = "INSERT INTO `con_tipo_cambio` (`tca_fecha`, `tca_mon_id`, `tca_eliminado`,`tca_valor`,`tca_valor_compra`,`tca_valor_venta`) 
                            VALUES ('$year-$m-$i', '2', 'No','$tc_usd','$tc_usd_compra','$tc_usd_venta');";
            echo $insert_usd . "<br>";
            $insert_real = "INSERT INTO `con_tipo_cambio` (`tca_fecha`, `tca_mon_id`, `tca_eliminado`,`tca_valor`,`tca_valor_compra`,`tca_valor_venta`) 
                            VALUES ('$year-$m-$i', '3', 'No','$tc_real','$tc_real','$tc_real');";
            echo $insert_real . "<br>";
            $insert_ufv = "INSERT INTO `con_tipo_cambio` (`tca_fecha`, `tca_mon_id`, `tca_eliminado`,`tca_valor`,`tca_valor_compra`,`tca_valor_venta`) 
                            VALUES ('$year-$m-$i', '4', 'No','$tc_ufv','$tc_ufv','$tc_ufv');";
            echo $insert_ufv . "<br>";
        }
    }
}

function dia_max($year, $mes) {
    if ($mes == 1 || $mes == 3 || $mes == 5 || $mes == 7 || $mes == 8 || $mes == 10 || $mes == 12) {
        return 31;
    } elseif ($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) {
        return 30;
    } elseif ($mes == 2) {
        if ($year % 4 == 0) {
            return 29;
        } else {
            return 28;
        }
    }
}

//ENERO 
//for ($i = 1; $i <= 31; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-01-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-01-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    //echo $insert_ufv.'<br>';
//}
////FEBRERO
//for ($i = 1; $i <= 28; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-02-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-02-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    //echo $insert_ufv.'<br>';
//}
////MARZO
//for ($i = 1; $i <= 31; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-03-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-03-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    echo $insert_ufv.'<br>';
//}
////ABRIL
//for ($i = 1; $i <= 30; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-04-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-04-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    echo $insert_ufv.'<br>';
//}
////MAYO
//for ($i = 1; $i <= 31; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-05-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-05-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    echo $insert_ufv.'<br>';
//}
////JUNIO
//for ($i = 1; $i <= 30; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-06-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-06-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    echo $insert_ufv.'<br>';
//}
////JULIO
//for ($i = 1; $i <= 31; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-07-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-07-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    echo $insert_ufv.'<br>';
//}
////AGOSTO
//for ($i = 1; $i <= 31; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-08-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-08-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    echo $insert_ufv.'<br>';
//}
////SEPTIEMBRE
//for ($i = 1; $i <= 30; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-09-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-09-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    echo $insert_ufv.'<br>';
//}
//
////OCTUBRE
//for ($i = 1; $i <= 31; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-10-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-10-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    echo $insert_ufv.'<br>';
//}
////NOVIEMBRE
//for ($i = 1; $i <= 30; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-11-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-11-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    echo $insert_ufv.'<br>';
//}
////DICIEMBRE
//for ($i = 1; $i <= 31; $i++) {
//    $insert_usd="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('6.96000', '2013-12-$i', '2', 'No');";
//    $insert_ufv="INSERT INTO `con_tipo_cambio` (`tca_valor`, `tca_fecha`, `tca_mon_id`, `tca_eliminado`) 
//    VALUES ('1.00000', '2013-12-$i', '3', 'No'); <br>";
//    echo $insert_usd.'<br>';
//    echo $insert_ufv.'<br>';
//}
?>
