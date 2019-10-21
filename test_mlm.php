<?php

ini_set('display_errors', 'On');
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
require_once("clases/mytime_int.php");
require_once('config/constantes.php');
require_once("clases/Ticket.php");
require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');

//$vendedores = FUNCIONES::lista_bd_sql("select * from vendedor 
//    where vdo_estado='Habilitado' 
//    order by vdo_nivel desc,vdo_id desc limit 0,115");
//
//foreach ($vendedores as $vdo) {
//    $rango = MLM::calcular_rango_actual($vdo->vdo_id);
//    MLM::actualizar_rango($vdo->vdo_id, $rango);
//}
//
//$hoy = (isset($_GET[hoy])) ? $_GET[hoy] : date('Y-m-d');
//
//$d = explode('-', $hoy);
//$periodo = $d[0] . "-" . $d[1];
//$sql_ventas = "select * from venta where left(ven_fecha,7)='$periodo' 
//    and ven_estado in ('Pendiente','Pagado')
//    and ven_multinivel='si'";
//$ventas = FUNCIONES::lista_bd_sql($sql_ventas);
//foreach ($ventas as $ven) {
//    echo "<br/>" . $ven->ven_id;
//    $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$ven->ven_urb_id");
//    $data_com = array(
//        'venta' => $ven->ven_id,
//        'vendedor' => $ven->ven_vdo_id,
//        'monto' => $ven->ven_res_anticipo,
//        'moneda' => $ven->ven_moneda,
//        'fecha' => $ven->ven_fecha,
//        'urb' => $urb,
//        'lote' => $ven->ven_lot_id,
//        'usuario' => $ven->ven_usu_id,
//    );
//    COMISION::insertar_comisiones($data_com);
//}
//
//
////COMISIONAR POR LOS PAGOS DE CUOTAS
//$sql_vc = "select * from venta inner join vendedor on (ven_vdo_id=vdo_id)
//where ven_estado in ('Pendiente','Pagado')
//and vdo_estado='Habilitado'";
//$ventas = FUNCIONES::lista_bd_sql($sql_vc);
//
//foreach ($ventas as $ven) {
//    if (MLM::venta_al_dia($ven, $hoy)) {
//        $data = array(
//            'vendedor' => $ven->ven_vdo_id,
//            'fecha' => $hoy,
//            'venta' => $ven->ven_id,
//            'moneda' => $ven->ven_moneda,
//            'usuario' => $ven->ven_usu_id,
//        );
//        COMISION::comisionar_cuotas($data);
//    }
//}
//$data = array('ven_id' => 72);
//$bra_total = MLM::calcular_montos_bonos($data);
//echo $bra_total->BRA_TOTAL . " - " . $bra_total->BONO_INICIAL . " - " . $bra_total->MONTO_RED . " - " . $bra_total->FED . " - " . $bra_total->BEV . " - " . $bra_total->FDR;
//$ventas = FUNCIONES::lista_bd_sql("select * from venta where ven_multinivel='si'");
//
//foreach ($ventas as $ven) {
//    echo "<p>EJECUTANDO ...</p>";
//    $data = array('ven_id' => $ven->ven_id);
//    MLM::calcular_montos_bonos($data);
//    
//    $data_cobro = array('venta' => $ven->ven_id, 'vendedor' => $ven->ven_vdo_id);
//    MLM::insertar_comisiones_cobro($data_cobro);    
//}
//$hoy = date('2016-08-04');
//echo substr($hoy, 0, 7);
//
//echo (MLM::esta_activo(12833, $hoy))?"<BR/>ESTA ACTIVO":"<BR/>NO ESTA ACTIVO";
//
//
//$vdo_id = $_GET[vendedor];
//$vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$vdo_id");
//$nivel = 1000;
//$profundidad = $vendedor->vdo_nivel + 1;
//$red = MLM::obtener_red($vendedor->vdo_id, $nivel, TRUE, $profundidad);
//
// _PRINT::pre($red);

$arr = array(8918, 8731, 8719, 8642, 8764, 8607, 8748, 8871, 8919, 0, 8814, 9079, 8784, 0, 8855, 9024, 8745, 8886, 8887, 8970, 9010, 9011, 8954, 9124, 8946, 8926, 0, 9127, 9016, 9017, 0, 0, 0, 0, 0, 0, 9147);

//foreach ($arr as $e) {
//    MLM::esta_al_dia($e, '2017-03-31');
////    echo "shit<br>";
//}
//echo time();
//echo date("Y-m-d H:i:s");


function calcular_BRA($vdo_id, $hoy, $pdo_id, $marca_tmp = '') {
    $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
            inner join rango on (vdo_rango_actual=ran_id)
            where vdo_id=$vdo_id");

//        $vendedor->ran_gen_com_cobros = 7;

    if ($vendedor->ran_gen_com_cobros > 0) {

        $prof = $vendedor->vdo_nivel + $vendedor->ran_gen_com_cobros;
        $nivel = 1000;
        $arr_ids_hijos = MLM::obtener_red($vendedor->vdo_id, $nivel, TRUE, $prof, '', 'vendedor_tmp');
        $cant_ids = count($arr_ids_hijos);

        if ($cant_ids > 0) {
            $s_ids = implode(",", $arr_ids_hijos);
            for ($i = 0; $i <= $vendedor->ran_gen_com_cobros; $i++) {

                $nivel = $vendedor->vdo_nivel + $i;
                $s_periodo = substr($hoy, 0, 7);

//                    $sql_ventas_hijos = "select * from vendedor_tmp
//                    inner join venta on(vdo_id=ven_vdo_id)
//                    where left(ven_fecha,7)<='$s_periodo'
//                    and ven_multinivel='si'
//                    and ven_estado in ('Pendiente','Pagado')
//                    and vdo_estado='Habilitado'
//                    and vdo_id in ($s_ids)
//                    and vdo_nivel = $nivel
//                    and ven_id not in (
//                        select vof_ven_id from venta_oferta where vof_estado='Pendiente'
//                    )";

                $sql_ventas_hijos = "select * from vendedor_tmp
                    inner join venta on(vdo_id=ven_vdo_id)
                    where left(ven_fecha,7)<='$s_periodo'
                    and ven_multinivel='si'
                    and ven_estado in ('Pendiente','Pagado')
                    
                    and vdo_id in ($s_ids)
                    and vdo_nivel = $nivel
                    and ven_id not in (
                        select vof_ven_id from venta_oferta where vof_estado='Pendiente'
                    )";


                FUNCIONES::eco("SQL-BRA:<BR/>" . $sql_ventas_hijos);

                $ventas = FUNCIONES::lista_bd_sql($sql_ventas_hijos);

                foreach ($ventas as $venta) {

                    if (!MLM::venta_al_dia($venta, $hoy, TRUE)) {
                        continue;
                    }

                    $vdo_venta = FUNCIONES::objeto_bd_sql("select * from vendedor
                            where vdo_venta_inicial = {$venta->ven_id}");

                    if ($vdo_venta) {
                        if (!MLM::esta_activo($vdo_venta, $hoy)) {
                            continue;
                        }
                    }

                    if ($venta->ven_bono_bra == 0) {
                        continue;
                    }

                    $comision_cuota = FUNCIONES::objeto_bd_sql("select * from comision_cobro 
                            where comcob_ven_id=$venta->ven_id order by comcob_id asc limit 0,1");

                    $monto_comision = $comision_cuota->comcob_monto_cuota;
                    $monto = $venta->ven_bono_bra;

//                        $porc = $monto_comision * 100 / $monto;
                    $porc = 100;


                    if ($porc > 0) {
                        $k = ($venta->vdo_nivel - $vendedor->vdo_nivel);
                        $gen_i = $k + 1;
                        $total_comisionado = 0;
                        $saldo = $monto;

                        if ($monto_comision <= $saldo) {

                            $datos = array(
                                'venta' => $venta,
                                'vendedor' => $vendedor->vdo_id,
                                'monto' => $monto_comision,
                                'moneda' => $venta->ven_moneda,
                                'usuario' => $_SESSION[id],
                                'glosa' => "RESIDUAL ABIERTO $gen_i (BRA Venta " . $venta->ven_id . ')',
                                'fecha' => $hoy,
                                'tipo' => 'BRA',
                                'periodo' => $pdo_id,
                                'marca_tmp' => $marca_tmp,
                                'porcentaje' => $porc
                            );
                            echo "<pre>";
                            print_r($datos);
                            echo "</pre>";
                        }
                    }
                }
            }
        }
    } else {
        FUNCIONES::eco("la generacion: {$vendedor->ran_gen_com_cobros} de vdo_id:{$vendedor->vdo_id} al parecer no alcanzó para BRA...");
    }
}

function calcular_BVI($vdo_id, $hoy, $pdo_id, $marca_tmp = '') {

    $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
            inner join rango on (vdo_rango_actual=ran_id)
            where vdo_id=$vdo_id");

    $sql_hijos_desh_pri_nivel = "select vdo_id from vendedor_tmp 
        where vdo_vendedor_id='$vdo_id' and vdo_estado='Deshabilitado'";
    $hijos_desh = FUNCIONES::lista_bd_sql($sql_hijos_desh_pri_nivel);

    $arr_hijos_desh = array();
    foreach ($hijos_desh as $h) {
        $arr_hijos_desh[] = $h->vdo_id;
    }

    $s_ids_desh = "";
    if (count($arr_hijos_desh) > 0) {
        $s_ids_desh = implode(',', $arr_hijos_desh);
        $s_ids_desh = " and vdo_id not in ($s_ids_desh)";
    }

    if ($vendedor->ran_gen_com_ventas > 0) {

        $prof = $vendedor->vdo_nivel + $vendedor->ran_gen_com_ventas;
        $nivel = 1000;
        $arr_ids_hijos = MLM::obtener_red($vendedor->vdo_id, $nivel, FALSE, $prof, '', 'vendedor_tmp');
        $cant_ids = count($arr_ids_hijos);

        if ($cant_ids > 0) {
            $s_ids = implode(",", $arr_ids_hijos);
            for ($i = 1; $i <= $vendedor->ran_gen_com_ventas; $i++) {

                $nivel = $vendedor->vdo_nivel + $i;
                $s_periodo = substr($hoy, 0, 7);

                $sql_ventas_hijos = "select * from vendedor_tmp
                    inner join venta on(vdo_id=ven_vdo_id)
                    where left(ven_fecha,7)='$s_periodo'
                    and ven_multinivel='si'                    
                    and ven_comision_gen='1'                
                    and ven_estado in ('Pendiente','Pagado')
                    
                    and vdo_id in ($s_ids)
                    and vdo_nivel = $nivel
                    $s_ids_desh    
                    and ven_id not in (
                        select vof_ven_id from venta_oferta where vof_estado='Pendiente'
                    )";

                $ventas = FUNCIONES::lista_bd_sql($sql_ventas_hijos);
                FUNCIONES::eco("BVI");
                FUNCIONES::eco($sql_ventas_hijos);

                foreach ($ventas as $venta) {

                    $_comisiones = json_decode($venta->ven_porc_comisiones);
                    $comisiones = $_comisiones->array;
                    $porc = $comisiones[$i] / 2;
                    $monto = $venta->ven_bono_inicial;
                    $gen_i = $i + 1;

                    if ($porc > 0) {

                        $total_comisionado = 0;
                        $saldo = $monto;
                        $monto_comision = ($monto * $porc) / 100;

                        if ($monto_comision <= $saldo) {

                            $datos = array(
                                'venta' => $venta,
                                'vendedor' => $vendedor->vdo_id,
                                'monto' => $monto_comision,
                                'moneda' => $venta->ven_moneda,
                                'usuario' => $_SESSION[id],
                                'glosa' => "INGRESO RAPIDO $gen_i  (BVI Venta " . $venta->ven_id . ')',
                                'fecha' => $hoy,
                                'tipo' => 'BVI',
                                'periodo' => $pdo_id,
                                'marca_tmp' => $marca_tmp,
                                'porcentaje' => $porc
                            );
                            echo "<pre>";
                            print_r($datos);
                            echo "</pre>";
                        }
                    }
                }
            }
        }
    }
}

function calcular_BVI_oferta($vdo_id, $hoy, $pdo_id, $marca_tmp = '') {

    $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
            inner join rango on (vdo_rango_actual=ran_id)
            where vdo_id=$vdo_id");

    $sql_hijos_desh_pri_nivel = "select vdo_id from vendedor_tmp 
        where vdo_vendedor_id='$vdo_id' and vdo_estado='Deshabilitado'";
    $hijos_desh = FUNCIONES::lista_bd_sql($sql_hijos_desh_pri_nivel);

    $arr_hijos_desh = array();
    foreach ($hijos_desh as $h) {
        $arr_hijos_desh[] = $h->vdo_id;
    }

    $s_ids_desh = "";
    if (count($arr_hijos_desh) > 0) {
        $s_ids_desh = implode(',', $arr_hijos_desh);
        $s_ids_desh = " and vdo_id not in ($s_ids_desh)";
    }

    if ($vendedor->ran_gen_com_ventas > 0) {

        $prof = $vendedor->vdo_nivel + $vendedor->ran_gen_com_ventas;
        $nivel = 1000;
        $arr_ids_hijos = MLM::obtener_red($vendedor->vdo_id, $nivel, FALSE, $prof, '', 'vendedor_tmp');
        $cant_ids = count($arr_ids_hijos);

        if ($cant_ids > 0) {
            $s_ids = implode(",", $arr_ids_hijos);

            for ($i = 1; $i <= $vendedor->ran_gen_com_ventas; $i++) {

                $nivel = $vendedor->vdo_nivel + $i;
                $s_periodo = substr($hoy, 0, 7);
                $sql_ventas_hijos = "select * from vendedor_tmp
                    inner join venta on(vdo_id=ven_vdo_id)
                    where left(ven_fecha,7)<='$s_periodo'
                    and ven_multinivel='si'                    
                    and ven_estado in ('Pendiente','Pagado')
                    
                    and vdo_id in ($s_ids)
                    and vdo_nivel = $nivel
                    $s_ids_desh
                    and ven_id in (
                        select vof_ven_id from venta_oferta where vof_estado='Pendiente'
                    )";

                FUNCIONES::eco("BVI OFERTA");
                FUNCIONES::eco($sql_ventas_hijos);
                $ventas = FUNCIONES::lista_bd_sql($sql_ventas_hijos);


                foreach ($ventas as $venta) {

                    if (!MLM::venta_al_dia($venta, $hoy)) {
                        continue;
                    }

                    $oferta = FUNCIONES::objeto_bd_sql("select * from venta_oferta 
                            inner join oferta on(vof_of_id=of_id)
                            where vof_ven_id=$venta->ven_id
                            and vof_estado='Pendiente'");

                    if ($oferta == NULL) {
                        continue;
                    }

                    $of_params = json_decode($oferta->vof_parametros_mod);

                    if ($of_params->of_periodos_con_bir * 1 == 0) {
                        continue;
                    }

                    $_comisiones = json_decode($venta->ven_porc_comisiones);
                    $comisiones = $_comisiones->array;
                    $k = $venta->vdo_nivel - ($vendedor->vdo_nivel);
                    $porc = $comisiones[$k] / 2;
                    $monto = $venta->ven_bono_inicial;
                    $gen_i = $k + 1;

                    if ($porc > 0) {

                        $total_comisionado = 0;
                        $saldo = $monto;
                        $monto_comision = ($monto * $porc) / 100;

                        if ($monto_comision <= $saldo) {

                            $datos = array(
                                'venta' => $venta,
                                'vendedor' => $vendedor->vdo_id,
                                'monto' => $monto_comision,
                                'moneda' => $venta->ven_moneda,
                                'usuario' => $_SESSION[id],
                                'glosa' => "INGRESO RAPIDO $gen_i  (BVI Venta " . $venta->ven_id . ')' . "({$oferta->of_nombre})",
                                'fecha' => $hoy,
                                'tipo' => 'BVI',
                                'periodo' => $pdo_id,
                                'marca_tmp' => $marca_tmp,
                                'porcentaje' => $porc
                            );
                            echo "<pre>";
                            print_r($datos);
                            echo "</pre>";
                        }
                    }
                }
            }
        }
    }
}

function calcular_BIR_oferta($vdo_id, $hoy, $pdo_id, $marca_tmp = '') {

    $nivel = 1000;
    $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp where vdo_id=$vdo_id");
    $prof = $vendedor->vdo_nivel + 1;
    $arr_ids_hijos_directos = MLM::obtener_red($vdo_id, $nivel, FALSE, $prof, '', 'vendedor_tmp');

    $cant_ids = count($arr_ids_hijos_directos);

    if ($cant_ids > 0) {
        $s_ids = implode(",", $arr_ids_hijos_directos);
//            $s_ids = "($s_ids)";
        $s_periodo = substr($hoy, 0, 7);
        $sql_ventas_hijos = "select * from vendedor_tmp
                inner join venta on(vdo_int_id=ven_int_id and ven_id=vdo_venta_inicial)
                where left(ven_fecha,7)<='$s_periodo'
                and ven_multinivel='si'                
                and ven_estado in ('Pendiente','Pagado')
                
                and vdo_id in ($s_ids)                
                and ven_id in (
                    select vof_ven_id from venta_oferta where vof_estado='Pendiente'
                )";
        $ventas = FUNCIONES::lista_bd_sql($sql_ventas_hijos);
        FUNCIONES::eco("BIR OFERTA");
        FUNCIONES::eco($sql_ventas_hijos);

        foreach ($ventas as $venta) {

            if (!MLM::venta_al_dia($venta, $hoy, TRUE)) {
                continue;
            }

            $oferta = FUNCIONES::objeto_bd_sql("select * from venta_oferta 
                            inner join oferta on(vof_of_id=of_id)
                            where vof_ven_id=$venta->ven_id
                            and vof_estado='Pendiente'");

            if ($oferta == NULL) {
                continue;
            }

            $of_params = json_decode($oferta->vof_parametros_mod);

            if ($of_params->of_periodos_con_bir * 1 == 0) {
                continue;
            }

            $_comisiones = json_decode($venta->ven_porc_comisiones);
            $comisiones = $_comisiones->array;
            $porc = $comisiones[0];
            $monto = $venta->ven_bono_inicial;

            if ($porc > 0) {
                $total_comisionado = 0;
                $saldo = $monto;
                $monto_comision = ($monto * $porc) / 100;
                if ($monto_comision <= $saldo) {

                    $datos = array('venta' => $venta,
                        'vendedor' => $vendedor->vdo_id,
                        'monto' => $monto_comision,
                        'moneda' => $venta->ven_moneda,
                        'usuario' => $_SESSION[id],
                        'glosa' => 'INGRESO RAPIDO 1 (BIR Venta ' . $venta->ven_id . ')' . "({$oferta->of_nombre})",
                        'fecha' => $hoy,
                        'tipo' => 'BIR',
                        'periodo' => $pdo_id,
                        'marca_tmp' => $marca_tmp,
                        'porcentaje' => $porc
                    );
                    print_r($datos);
                }
            }
        }
    }
}

//$hoy = '2017-08-31';
//$vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
//            inner join rango on (vdo_rango_actual=ran_id)
//            where vdo_id=1249");
//$b = MLM::esta_al_dia($vendedor, '2017-06-30');
// calcular_BIR_oferta($vendedor->vdo_id, $hoy, 42, $marca_tmp = '');
//calcular_BVI_oferta($vendedor->vdo_id, $hoy, 44, $marca_tmp = '');
//$nivel = 1000;
//$prof = $vendedor->vdo_nivel + $vendedor->ran_gen_com_cobros;
//echo "<p>prof = > $prof</p>";
//$arr_ids_hijos = MLM::obtener_red($vendedor->vdo_id, $nivel, TRUE, $prof, '', 'vendedor_tmp');
//$hijos = FUNCIONES::lista_bd_sql("select * from vendedor_tmp where vdo_id in (" . implode(',', $arr_ids_hijos) . ")");
//foreach($hijos as $h){
//    echo "<p>vdo_id:$h->vdo_id - venta:$h->vdo_venta_inicial - vdo_nivel:$h->vdo_nivel</p>";
//}
//echo implode(',', $arr_ids_hijos);
//$vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
//            inner join rango on (vdo_rango_actual=ran_id)
//            where vdo_id=1232");
//if (MLM::esta_al_dia($vendedor, '2017-08-31')){
//    echo "esta al dia";
//} else {
//    echo "no esta al dia";
//}


//$venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id=8988");
//if (MLM::esta_al_dia($venta, '2017-08-31', true)) {
//    echo "esta al dia";
//} else {
//    echo "no esta al dia";
//}

//$hoy = '2017-08-31';
//$vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor_tmp 
//            inner join rango on (vdo_rango_actual=ran_id)
//            where vdo_id=85");
//
//calcular_BRA($vendedor->vdo_id, $hoy, 44, $marca_tmp = '');

$hoy = "2018-07-31";
$vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=168");
$res = MLM::esta_al_dia($vendedor, $hoy);
if ($res) {
    echo "esta al dia";
} else {
    echo "no esta al dia";
}        
?>
