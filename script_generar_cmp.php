<!--<meta http-equiv="Content-Type" content="text/html; charset=utf-8">-->
<?php
//echo "hola mundo";
//return;
//echo "update";
//return;
//$var='[{"cue_id":"1004","cue_txt":"Caja General Central M/N","monto":"600","mon_id":"1","fpago":"Efectivo","ban_nombre":"","ban_nro":"","descripcion":"Ñato "él"}]';
//echo $var;
//$var=  htmlentities($var);
//echo "<pre>";
//echo $var;
//echo "</pre>";
//return;
//echo "actualizar suc";
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
include_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/formulario.class.php';
//define('_urbs', '2,3,4,6');
//_db::open();
//generar_cmp_venta();
//generar_cmp_pagos();
//generar_cmp_pago_vendedor();
//modificar_cmp_venta();
//modificar_cmp_pagos();
//modificar_cmp_pago_vendedor();
//generar_cmp_comision();
//modificar_cmp_compra();

//modificar_cmp_pagos_importado();


//generar_cmp_reserva_pago_externo();


generar_cmp_venta_pago();
function generar_cmp_venta_pago() {
    
    $vpag_id=114017;
    $cu_pago='1.1.1.03.2.01';
    
    $conec = new ADO();
//    $pagos = FUNCIONES::objetos_bd_sql("select * from venta_pago where vpag_fecha_pago >='2015-01-01' and vpag_fecha_pago<='2015-12-31' and vpag_monto>0 and vpag_cmp='0' order by vpag_id asc limit 10");
    $pagos = FUNCIONES::objetos_bd_sql("select * from venta_pago where vpag_id=$vpag_id");


    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pago = $pagos->get_objeto();
        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_pago' and cmp_tabla_id='$pago->vpag_id' and cmp_eliminado='No'");
        if (!$cmp) {
//            echo "generar $pago->vpag_id<br>";
            cmp_venta_pago($pago,$cu_pago);
//            cmp_venta($venta);
        } else {
            $sql_up = "update venta_pago set vpag_cmp='1' where vpag_id='$pago->vpag_id'";
            $conec->ejecutar($sql_up, false, false);
            echo "ya fue generado<br>";
        }

        $pagos->siguiente();
    }
    echo $pagos->get_num_registros() . '<br>';
}

function cmp_venta_pago($pago,$cu_pago) {
    $conec = new ADO();
//    $conec->begin_transaccion();
//    echo "select * from venta where ven_id='$pago->vpag_ven_id'";
    $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pago->vpag_ven_id'");
//    $id_lote=$venta->ven_lot_id;
//    $lote = FUNCIONES::objeto_bd_sql("select * from lote where lot_id='$venta->ven_lot_id'");
//    $concepto= FUNCIONES::get_concepto($id_lote);
    $monto = $pago->vpag_monto;
    $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$venta->ven_urb_id");
//    if ($urb->urb_tipo == 'Interno') {
    if (true) {
        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';
        $nro_recibo = $pago->vpag_recibo;
        $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$venta->ven_int_id'");
        $glosa = "Pago de la Venta Nro. $venta->ven_id - $venta->ven_concepto - $referido - Rec. $nro_recibo ";
        $ges_id = 15;
//        $params=array(
//                'tabla'=>'venta_pago',
//                'tabla_id'=>$pago_id,
//                'fecha'=>$fecha_pago,
//                'moneda'=>$venta->ven_moneda,
//                'ingreso'=>true,
//                'une_id'=>$urb->urb_une_id,
//                'glosa'=>$glosa,'ca'=>'0','cf'=>0,'cc'=>0
//            );
        
        $detalles = array(
            array("cuen" => FUNCIONES::get_cuenta($ges_id, $cu_pago), "debe" => $monto, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            )
        );
//        FORMULARIO::insertar_pagos($params);
//        '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_monto'
        $fecha_pago = $pago->vpag_fecha_pago;
        $data = array(
            'moneda' => $venta->ven_moneda,
            'ges_id' => $ges_id,
            'fecha' => $fecha_pago,
            'glosa' => $glosa,
            'interno' => $referido,
            'tabla_id' => $pago->vpag_id,
            'urb' => $urb,
            'interes' => $pago->vpag_interes * 1,
            'capital' => $pago->vpag_capital * 1,
            'form' => $pago->vpag_form * 1,
            'envio' => $pago->vpag_envio * 1,
            'mora' => $pago->vpag_mora * 1,
            'detalles' => $detalles,
            'costo' => $pago->vpag_costo,
        );
        $usuario=  FUNCIONES::objeto_bd_sql("select * from ad_usuario where usu_id='$pago->vpag_usu_cre'");
        if ($urb->urb_tipo == 'Interno') {
            $comprobante = MODELO_COMPROBANTE::pago_cuota($data);
        }else{
            $comprobante = MODELO_COMPROBANTE::pago_cuota_ext($data);
        }
        
        $comprobante->usu_per_id = $usuario->usu_per_id;
        $comprobante->usu_id = $pago->vpag_usu_cre;
        echo "<pre>";
        print_r($comprobante);
        echo "</pre>";
        COMPROBANTES::registrar_comprobante($comprobante);
        $sql_up = "update venta_pago set vpag_cmp='1' where vpag_id='$pago->vpag_id'";
        $conec->ejecutar($sql_up, false, false);
    }else{
        echo "NO ES INTERNO";
    }
}




function generar_cmp_reserva_pago_externo() {
    $sql = "select * from reserva_pago where respag_estado='Pagado' and respag_fecha>='2016-03-07'
            and respag_res_id in (select res_id from reserva_terreno where res_urb_id in (7,8,9,10)) ;";
    $rpagos = FUNCIONES::lista_bd_sql($sql);
    echo count($rpagos);
    foreach ($rpagos as $rpago) {
//        echo "<pre>";
//        print_r($rpago);
        $cmp=  FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='reserva_pago' and cmp_tabla_id='$rpago->respag_id' and cmp_eliminado='No'");
        if(!$cmp){
            cmp_pago_reservas($rpago);
        }
//        echo "</pre>";
    }
}

//echo "aaa";
function cmp_pago_reservas($rpago) {
    $auser = array(
        'ksoruco' => '1.1.1.01.2.09',
        'mfernandez' => '1.1.1.01.2.10',
        'bsoria' => '1.1.1.01.2.13'
    );
    $ges_id = 16;
    $cue_id = FUNCIONES::get_cuenta($ges_id, $auser[$rpago->respag_usu_id]);
    $_POST[a_fpag_monto] = array($rpago->respag_monto);
    $_POST[a_fpag_mon_id] = array(2);
    $_POST[a_fpag_forma_pago] = array('Efectivo');
    $_POST[a_fpag_ban_nombre] = array('');
    $_POST[a_fpag_ban_nro] = array('');
    $_POST[a_fpag_cue_id] = array($cue_id);
    $_POST[a_fpag_descripcion] = array('');


    $reserva=  FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_id='$rpago->respag_res_id'");
    $referido=  FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$reserva->res_int_id'");
//                    $glosa = "Pago de Reserva: " . $glosa;
    $urb=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$reserva->res_urb_id'");
    $des=  FUNCIONES::get_concepto($reserva->res_lot_id);
    $glosa = "Pago de la Reserva Nro. $rpago->respag_id - $des - $referido - Rec. $reserva->respag_recibo";
    $fecha_cmp=$rpago->respag_fecha;
    $moneda=$rpago->respag_moneda;
    $params=array(
            'tabla'=>'reserva_pago',
            'tabla_id'=>$rpago->respag_id,
            'fecha'=>$fecha_cmp,
            'moneda'=>$moneda,
            'ingreso'=>true,
            'une_id'=>$urb->urb_une_id,
            'glosa'=>$glosa,'ca'=>'0','cf'=>0,'cc'=>0
        );
    $detalles = FORMULARIO::insertar_pagos($params);

    $data=array(
        'moneda'=>$moneda,
        'ges_id'=>$ges_id,
        'fecha'=>$fecha_cmp,
        'glosa'=>$glosa,
        'interno'=>$referido,
        'tabla_id'=>$rpago->respag_id,
        'urb'=>$urb,
        'monto'=>$rpago->respag_monto,
        'detalles'=>$detalles,
    );

    $comprobante = MODELO_COMPROBANTE::anticipo($data);
    $comprobante->usu_per_id=  FUNCIONES::atributo_bd_sql("select usu_per_id  as campo from ad_usuario where usu_id='$rpago->respag_usu_id'");
    $comprobante->usu_id=$rpago->respag_usu_id ;

    echo "<pre>";
    print_r($comprobante);
    echo "</pre>";

    COMPROBANTES::registrar_comprobante($comprobante);
}

function modificar_cmp_compra() {
//    $filtro=" and com_id=331";
    $filtro = "";

    $sql_sel = "select distinct com_id,com_fecha,com_referido,com_usu_cre from con_compra, con_compra_detalle
            where dcom_tipo_nota='retencion' and com_id=dcom_com_id and com_fecha_cre<='2016-03-22 00:00:00' $filtro
            order by com_fecha asc,com_id asc
        ";
    $compras = FUNCIONES::lista_bd_sql($sql_sel);
    echo count($compras);
    foreach ($compras as $compra) {
        cmp_compra($compra);
    }
}

function cmp_compra($compra) {
//        echo "<pre>";
//        print_r($compra);
//        echo "</pre>";
//                return;
    $fecha = $compra->com_fecha; //FUNCIONES::get_fecha_mysql($_POST[com_fecha]);
    $conec = new ADO();
    $fecha_mod = date('Y-m-d H:i:s');
    $glosa = $compra->com_glosa; //$_POST[com_glosa];
    $referido = $compra->com_referido;
    $com_id = $compra->com_id;


    $com_moneda = 1;
    $detalles = array();


    $cambios = FUNCIONES::objetos_bd_sql("select * from con_tipo_cambio where tca_fecha='$fecha'");
    $ges_id = FUNCIONES::atributo_bd_sql("select ges_id as campo from con_gestion where ges_fecha_ini<='$fecha' and ges_fecha_fin>='$fecha'");


    $cue_credito_fiscal = FUNCIONES::parametro('cred_fiscal', $ges_id);
    $cue_cf_id = FUNCIONES::get_cuenta($ges_id, $cue_credito_fiscal);
    $porc_iva = FUNCIONES::parametro('val_iva', $ges_id);

//        $cue_ret_it = FUNCIONES::parametro('ret_it', $ges_id);                
//        $cue_it_id=FUNCIONES::get_cuenta($ges_id, $cue_ret_it);
    $porc_ret_it = FUNCIONES::parametro('porc_ret_it', $ges_id);

    //                if ($obj->tipo_ret == 'Bienes') { // bienes
    $bcue_ret_it = FUNCIONES::parametro('ret_it_bien', $ges_id);
    $bcue_it_id = FUNCIONES::get_cuenta($ges_id, $bcue_ret_it);
    $bcue_ret_iue = FUNCIONES::parametro('ret_iue_bien', $ges_id);
    $bcue_iue_id = FUNCIONES::get_cuenta($ges_id, $bcue_ret_iue);
    $bporc_ret_iue = FUNCIONES::parametro('porc_ret_iue_bien', $ges_id);
//                } elseif ($obj->tipo_ret == 'Servicios') { // servicio
    $scue_ret_it = FUNCIONES::parametro('ret_it_serv', $ges_id);
    $scue_it_id = FUNCIONES::get_cuenta($ges_id, $scue_ret_it);
    $scue_ret_iue = FUNCIONES::parametro('ret_iue_serv', $ges_id);
    $scue_iue_id = FUNCIONES::get_cuenta($ges_id, $scue_ret_iue);
    $sporc_ret_iue = FUNCIONES::parametro('porc_ret_iue_serv', $ges_id);


    $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='con_compra' and cmp_tabla_id='$com_id'");

    $det_compra = FUNCIONES::lista_bd_sql("select * from con_compra_detalle where dcom_com_id=$com_id and dcom_tipo_nota!=''");

    foreach ($det_compra as $det) { /// limpiar ñ,Ñ tildes 
//        $str_det = FUNCIONES::limpiar_cadena($str_det);
////                    $str_det=  htmlentities($str_det);
////                    $str_det=str_replace("&quot;", '"', $str_det);
//        $str_det = str_replace('\"', '"', $str_det);
        $obj = new stdClass();
        $obj->moneda = $det->dcom_moneda;
        $obj->glosa = $det->dcom_glosa;
        $obj->unegocios = json_decode($det->dcom_unegocios);
        $obj->gastos = json_decode($det->dcom_gastos);
        $obj->pagos = json_decode(FUNCIONES::limpiar_cadena($det->dcom_pagos));
        $obj->importe = $det->dcom_importe;
        $obj->excento = $det->dcom_excento;
        $obj->tipo_nota = $det->dcom_tipo_nota;
        $obj->tipo_ret = $det->dcom_tipo_ret;
//        echo "<pre>";
//        print_r($obj);
//        echo "</pre>";
//        $obj = json_decode($str_det);

        $dmoneda = $obj->moneda;
//                    $dglosa=$obj->glosa;
        $dglosa = trim(FUNCIONES::html_decode($obj->glosa));
//                    $dglosa=  html_entity_decode($obj->glosa);

        $aunegocios = $obj->unegocios;
//        $str_unegocios = "[";
//        $i = 0;
//        foreach ($aunegocios as $uneg) {
//            if ($i > 0) {
//                $str_unegocios.=",";
//            }
//            $str_unegocios.="{\"une_id\":\"$uneg->une_id\",\"une_porc\":\"$uneg->une_porc\"}"; // desc
//            $i++;
//        }
//        $str_unegocios.="]";

        $mimporte = FORMULARIO::convertir_fpag_monto($obj->importe, $dmoneda, $com_moneda, $cambios);
        $mexento = FORMULARIO::convertir_fpag_monto($obj->excento, $dmoneda, $com_moneda, $cambios);

        $agastos = $obj->gastos;
        $str_gastos = "[";
        $i = 0;
        foreach ($agastos as $gast) {
            if ($i > 0) {
                $str_gastos.=",";
            }
            $str_gastos.="{\"cue_id\":\"$gast->cue_id\",\"monto\":\"$gast->monto\"}"; // cue_desc

            $gmonto = FORMULARIO::convertir_fpag_monto($gast->monto, $dmoneda, $com_moneda, $cambios);

            $gasto_neto = $gmonto;
//                        $cue_cf_id= FUNCIONES::get_cuenta($ges_id, $cue_credito_fiscal);
//                        $porc_iva = FUNCIONES::parametro('val_iva', $ges_id);        
            if ($obj->tipo_nota == 'factura') {
                $gexcento = ($mexento * $gmonto) / $mimporte;
                $monto_iva = ($gmonto - $gexcento) * $porc_iva / 100;
//                    $monto_iva = $gmonto * $porc_iva / 100;
                $gasto_neto = $gmonto - $monto_iva;
            } else if ($obj->tipo_nota == 'retencion') {
                if ($obj->tipo_ret == 'Bienes') {
                    $porc_ret_iue = $bporc_ret_iue;
                } elseif ($obj->tipo_ret == 'Servicios') {
                    $porc_ret_iue = $sporc_ret_iue;
                }
                $n_monto = $gmonto / ((100 - ($porc_ret_iue + $porc_ret_it)) / 100);
                $monto_ret_it = $n_monto * $porc_ret_it / 100;
                $monto_ret_iue = $n_monto * $porc_ret_iue / 100;
                $gasto_neto = $gmonto + $monto_ret_it + $monto_ret_iue;
            }
//                        $gmonto=$gast->monto;

            $sum_gmontos = 0;
            for ($j = 0; $j < count($aunegocios); $j++) {
                $une = $aunegocios[$j];
                $_nmonto = 0;
                if ($j == count($aunegocios) - 1) { //ultimos
                    $_nmonto = $gasto_neto - $sum_gmontos;
                } else {
                    $_nmonto = ($gasto_neto * $une->une_porc) / 100;
                }
                $sum_gmontos+=$_nmonto;
                $detalles[] = array("cuen" => $gast->cue_id, "debe" => $_nmonto, "haber" => 0,
                    "glosa" => $dglosa, "ca" => 0, "cf" => 0, "cc" => 0,
                    'fpago' => '', 'ban_nombre' => '', 'ban_nro' => '', 'descripcion' => '',
                    'une_id' => $une->une_id
                );
            }
            $i++;
        }
        $str_gastos.="]";


        if ($obj->tipo_nota == 'factura') { //iva
//                $monto_iva = $mimporte * $porc_iva / 100;
            $monto_iva = ($mimporte - $mexento) * $porc_iva / 100;
            if ($monto_iva > 0) {
                $detalles[] = array("cuen" => $cue_cf_id, "debe" => $monto_iva, "haber" => 0,
                    "glosa" => $dglosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => 0
                );
            }
        } else if ($obj->tipo_nota == 'retencion') {
            if ($obj->tipo_ret == 'Bienes') {
                $cue_it_id = $bcue_it_id;
                $cue_ret_iue = $bcue_iue_id;
                $porc_ret_iue = $bporc_ret_iue;
            } elseif ($obj->tipo_ret == 'Servicios') {
                $cue_it_id = $scue_it_id;
                $cue_ret_iue = $scue_iue_id;
                $porc_ret_iue = $sporc_ret_iue;
            }
            $n_monto = $mimporte / ((100 - ($porc_ret_iue + $porc_ret_it)) / 100);
            $monto_ret_it = $n_monto * $porc_ret_it / 100;
            $monto_ret_iue = $n_monto * $porc_ret_iue / 100;

            if ($monto_ret_iue > 0) {
                $detalles[] = array("cuen" => $cue_ret_iue, "debe" => 0, "haber" => $monto_ret_iue,
                    "glosa" => $dglosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => 0
                );
            }
            if ($monto_ret_it > 0) {
                $detalles[] = array("cuen" => $cue_it_id, "debe" => 0, "haber" => $monto_ret_it,
                    "glosa" => $dglosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => 0
                );
            }
        }

        $apagos = $obj->pagos;
        $str_pagos = "[";
        $i = 0;
        foreach ($apagos as $pag) {
            if ($i > 0) {
                $str_pagos.=",";
            }
            $descripcion = trim(FUNCIONES::html_decode($pag->descripcion));
            $str_pagos.="{\"cue_id\":\"$pag->cue_id\",\"monto\":\"$pag->monto\",\"mon_id\":\"$pag->mon_id\",\"fpago\":\"$pag->fpago\",\"ban_nombre\":\"$pag->ban_nombre\",\"ban_nro\":\"$pag->ban_nro\",\"descripcion\":\"$descripcion\"}"; // cue_desc

            $nmonto = FORMULARIO::convertir_fpag_monto($pag->monto, $pag->mon_id, $com_moneda, $cambios);

            $detalles[] = array("cuen" => $pag->cue_id, "debe" => 0, "haber" => $nmonto,
                "glosa" => $dglosa, "ca" => 0, "cf" => 0, "cc" => 0,
                'fpago' => $pag->fpago, 'ban_nombre' => $pag->ban_nombre, 'ban_nro' => $pag->ban_nro,
                'descripcion' => $descripcion, 'une_id' => 0
            );
            $i++;
        }
        $str_pagos.=']';



//                    $cliente=html_entity_decode($libro->cliente);
    }
    include_once 'clases/modelo_comprobantes.class.php';
    include_once 'clases/registrar_comprobantes.class.php';

    $data = array(
        'cmp_id' => $cmp->cmp_id,
        'moneda' => $com_moneda,
        'ges_id' => $ges_id,
        'fecha' => $fecha,
        'glosa' => $glosa,
        'interno' => $referido,
        'tabla_id' => $com_id,
        'detalles' => $detalles,
    );

    $comprobante = MODELO_COMPROBANTE::compra($data);

    $comprobante->usu_per_id = FUNCIONES::atributo_bd_sql("select usu_per_id as campo from ad_usuario where usu_id='$compra->com_usu_cre'");
//    echo "select usu_per_id as campo from ad_usuario where usu_id='$compra->com_usu_cre'";
    $comprobante->usu_id = $compra->com_usu_cre;

//    echo "<pre>";
//    print_r($comprobante);
//    echo "</pre>";

    COMPROBANTES::modificar_comprobante($comprobante);
//                $sql_up="update con_libro set lib_cmp_id='$cmp_id' where lib_com_id='$com_id'";
//                $conec->ejecutar($sql_up);
//                $sql_up="update con_retencion set ret_cmp_id='$cmp_id' where ret_com_id='$com_id'";
//                $conec->ejecutar($sql_up);
//    $mensaje = 'Sucursal Modificado Correctamente';
//    $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
}

function generar_cmp_comision() {
    $vdo_id = 31;
    $comisiones = FUNCIONES::lista_bd_sql("select * from comision where com_vdo_id=$vdo_id");
    foreach ($comisiones as $comision) {
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$comision->com_ven_id'");
        $ges_id = FUNCIONES::atributo_bd_sql("select ges_id as campo from con_gestion where ges_fecha_ini<='$comision->com_fecha_cre' and ges_fecha_fin>='$comision->com_fecha_cre'");
        $glosa = "Provision de comision a vendedor Interno por la venta Nro $comision->com_ven_id";
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$comision->com_vdo_id");
        $referido = FUNCIONES::interno_nombre($vendedor->vdo_int_id);
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        $monto_com = $comision->com_monto;
        $data = array(
            'moneda' => 2,
            'ges_id' => $ges_id,
            'fecha' => $comision->com_fecha_cre,
            'glosa' => $glosa,
            'interno' => $referido,
            'tabla_id' => $comision->com_id,
            'urb' => $urb,
            'monto' => $monto_com,
        );
        $comprobante = MODELO_COMPROBANTE::comision($data);
        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='comision' and cmp_tabla_id='$comision->com_id' and cmp_eliminado='No'");
        if ($cmp) {
            $comprobante->cmp_id = $cmp->cmp_id;
            $comprobante->usu_per_id = FUNCIONES::atributo_bd_sql("select usu_per_id as campo from ad_usuario where usu_id='$comision->com_usu_id'");
            $comprobante->usu_id = $comision->com_usu_id;
            COMPROBANTES::modificar_comprobante($comprobante);
        } else {
            $comprobante->usu_per_id = FUNCIONES::atributo_bd_sql("select usu_per_id as campo from ad_usuario where usu_id='$comision->com_usu_id'");
            $comprobante->usu_id = $comision->com_usu_id;
            COMPROBANTES::registrar_comprobante($comprobante);
        }
        echo '<pre>';
        print_r($comprobante);
        echo '</pre>';
    }
}

function modificar_cmp_pago_vendedor() {
    include_once 'clases/registrar_comprobantes.class.php';
    include_once 'clases/modelo_comprobantes.class.php';
    $pagos = FUNCIONES::lista_bd_sql("select * from pago_vendedores where pve_monto>0 and pve_estado='Activo'");
    $acajas = array(
        '1' => '1.1.1.01.2.26',
        '6' => '1.1.1.01.2.26',
        '5' => '1.1.1.01.2.26',
        '4' => '1.1.1.01.2.26',
        '3' => '1.1.1.01.2.26',
        '2' => '1.1.1.01.2.26',
        '7' => '1.1.1.01.2.26',
        '9' => '1.1.1.01.2.18',
        '10' => '1.1.1.01.2.18',
        '8' => '1.1.1.01.2.26',
        '11' => '1.1.1.03.2.04',
        '23' => '1.1.1.03.2.01',
        '24' => '1.1.1.03.2.07',
        '19' => '1.1.1.03.2.07',
        '18' => '1.1.1.03.2.07',
        '17' => '1.1.1.03.2.07',
        '16' => '1.1.1.03.2.07',
        '15' => '1.1.1.03.2.07',
        '14' => '1.1.1.03.2.07',
        '12' => '1.1.1.03.2.07',
        '25' => '1.1.1.03.2.07',
        '21' => '1.1.2.01.2.02',
        '20' => array(
            array('cod' => '1.1.2.01.2.02', 'monto' => '500', 'moneda' => '2'),
            array('cod' => '1.1.1.03.2.07', 'monto' => '964.09', 'moneda' => '2'),
        ), //'1.1.2.01.2.02',
    );
    $ges_id = 16;
    foreach ($pagos as $pago) {
        $_cuenta = $acajas[$pago->pve_id];
        if ($_cuenta) {
            $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='pago_vendedores' and cmp_tabla_id='$pago->pve_id' and cmp_eliminado='No'");
            if ($cmp) {
                if (gettype($_cuenta) == 'string') {
                    $cue_id = FUNCIONES::get_cuenta($ges_id, $_cuenta);
                    $_POST[a_fpag_monto] = array($pago->pve_monto);
                    $_POST[a_fpag_mon_id] = array(2);
                    $_POST[a_fpag_forma_pago] = array('Efectivo');
                    $_POST[a_fpag_ban_nombre] = array('');
                    $_POST[a_fpag_ban_nro] = array('');
                    $_POST[a_fpag_cue_id] = array($cue_id);
                    $_POST[a_fpag_descripcion] = array('');
                } else {
                    $a_fpag_monto = array();
                    $a_fpag_mon_id = array();
                    $a_fpag_forma_pago = array();
                    $a_fpag_ban_nombre = array();
                    $a_fpag_ban_nro = array();
                    $a_fpag_cue_id = array();
                    $a_fpag_descripcion = array();
                    $lcuentas = $_cuenta;
                    foreach ($lcuentas as $obj) {
                        $obj = (object) $obj;
                        $cue_id = FUNCIONES::get_cuenta(16, $obj->cod);
                        $a_fpag_monto[] = $obj->monto;
                        $a_fpag_mon_id[] = $obj->moneda;
                        $a_fpag_forma_pago[] = 'Efectivo';
                        $a_fpag_ban_nombre[] = '';
                        $a_fpag_ban_nro[] = '';
                        $a_fpag_cue_id[] = $cue_id;
                        $a_fpag_descripcion[] = '';
                    }
                    $_POST[a_fpag_monto] = $a_fpag_monto;
                    $_POST[a_fpag_mon_id] = $a_fpag_mon_id;
                    $_POST[a_fpag_forma_pago] = $a_fpag_forma_pago;
                    $_POST[a_fpag_ban_nombre] = $a_fpag_ban_nombre;
                    $_POST[a_fpag_ban_nro] = $a_fpag_ban_nro;
                    $_POST[a_fpag_cue_id] = $a_fpag_cue_id;
                    $_POST[a_fpag_descripcion] = $a_fpag_descripcion;
                }
                $glosa = "Pago de Comision Nro. $pago->pve_id, " . $pago->pve_glosa;
                $pag_fecha = $pago->pve_fecha;
                $moneda = $pago->pve_moneda;
                $interesado = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno,vendedor where vdo_id=$pago->pve_vdo_id and vdo_int_id=int_id ");
                $params = array(
                    'tabla' => 'pago_vendedores',
                    'tabla_id' => $pago->pve_id,
                    'fecha' => $pag_fecha,
                    'moneda' => $moneda,
                    'ingreso' => false,
                    'une_id' => 0,
                    'guardar_pago' => false,
                    'glosa' => $glosa, 'ca' => '0', 'cf' => '0', 'cc' => '0',
                );

                $detalles = FORMULARIO::insertar_pagos($params);

                $data = array(
                    'moneda' => $moneda,
                    'ges_id' => $ges_id,
                    'fecha' => $pag_fecha,
                    'glosa' => $glosa,
                    'interno' => $interesado,
                    'tabla_id' => $pago->pve_id,
                    'urb' => null,
                    'vdo_can_codigo' => 0,
                    'monto' => $pago->pve_monto,
                    'detalles' => $detalles,
                );
                $int_id = FUNCIONES::atributo_bd_sql("select int_id as campo from interno,ad_usuario where int_id=usu_per_id and usu_id='$pago->pve_usu_id'");
                $comprobante = MODELO_COMPROBANTE::pago_vendedor($data);
                $comprobante->cmp_id = $cmp->cmp_id;

                $comprobante->usu_id = $pago->pve_usu_id;
                $comprobante->usu_per_id = $int_id;

                COMPROBANTES::modificar_comprobante($comprobante);
                echo "MODIFICAR $cmp->cmp_id<br>";
            } else {
                echo "ya existe comprobante $pago->pve_id<br>";
            }
        } else {
            echo "no hay codigo $pago->pve_id<br>";
        }
    }
}

function generar_cmp_pago_vendedor() {
    include_once 'clases/registrar_comprobantes.class.php';
    include_once 'clases/modelo_comprobantes.class.php';
    $pagos = FUNCIONES::lista_bd_sql("select * from pago_vendedores where pve_monto>0 and pve_estado='Activo'");
    $acajas = array(
        '1' => '1.1.1.01.2.26',
        '6' => '1.1.1.01.2.26',
        '5' => '1.1.1.01.2.26',
        '4' => '1.1.1.01.2.26',
        '3' => '1.1.1.01.2.26',
        '2' => '1.1.1.01.2.26',
        '7' => '1.1.1.01.2.26',
        '9' => '1.1.1.01.2.18',
        '10' => '1.1.1.01.2.18',
        '8' => '1.1.1.01.2.26',
        '11' => '1.1.1.03.2.04',
        '23' => '1.1.1.03.2.01',
        '24' => '1.1.1.03.2.07',
        '19' => '1.1.1.03.2.07',
        '18' => '1.1.1.03.2.07',
        '17' => '1.1.1.03.2.07',
        '16' => '1.1.1.03.2.07',
        '15' => '1.1.1.03.2.07',
        '14' => '1.1.1.03.2.07',
        '12' => '1.1.1.03.2.07',
        '25' => '1.1.1.03.2.07',
        '21' => '1.1.2.01.2.02',
        '20' => array(
            array('cod' => '1.1.2.01.2.02', 'monto' => '500', 'moneda' => '2'),
            array('cod' => '1.1.1.03.2.07', 'monto' => '964.09', 'moneda' => '2'),
        ), //'1.1.2.01.2.02',
    );

    foreach ($pagos as $pago) {
        $_cuenta = $acajas[$pago->pve_id];
        if ($_cuenta) {
            $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='pago_vendedores' and cmp_tabla_id='$pago->pve_id' and cmp_eliminado='No'");
            if (!$cmp) {
                if (gettype($_cuenta) == 'string') {
                    $cue_id = FUNCIONES::get_cuenta(16, $_cuenta);
                    $_POST[a_fpag_monto] = array($pago->pve_monto);
                    $_POST[a_fpag_mon_id] = array(2);
                    $_POST[a_fpag_forma_pago] = array('Efectivo');
                    $_POST[a_fpag_ban_nombre] = array('');
                    $_POST[a_fpag_ban_nro] = array('');
                    $_POST[a_fpag_cue_id] = array($cue_id);
                    $_POST[a_fpag_descripcion] = array('');
                } else {
                    $a_fpag_monto = array();
                    $a_fpag_mon_id = array();
                    $a_fpag_forma_pago = array();
                    $a_fpag_ban_nombre = array();
                    $a_fpag_ban_nro = array();
                    $a_fpag_cue_id = array();
                    $a_fpag_descripcion = array();
                    $lcuentas = $_cuenta;
                    foreach ($lcuentas as $obj) {
                        $obj = (object) $obj;
                        $cue_id = FUNCIONES::get_cuenta(16, $obj->cod);
                        $a_fpag_monto[] = $obj->monto;
                        $a_fpag_mon_id[] = $obj->moneda;
                        $a_fpag_forma_pago[] = 'Efectivo';
                        $a_fpag_ban_nombre[] = '';
                        $a_fpag_ban_nro[] = '';
                        $a_fpag_cue_id[] = $cue_id;
                        $a_fpag_descripcion[] = '';
                    }
                    $_POST[a_fpag_monto] = $a_fpag_monto;
                    $_POST[a_fpag_mon_id] = $a_fpag_mon_id;
                    $_POST[a_fpag_forma_pago] = $a_fpag_forma_pago;
                    $_POST[a_fpag_ban_nombre] = $a_fpag_ban_nombre;
                    $_POST[a_fpag_ban_nro] = $a_fpag_ban_nro;
                    $_POST[a_fpag_cue_id] = $a_fpag_cue_id;
                    $_POST[a_fpag_descripcion] = $a_fpag_descripcion;
                }
                $glosa = "Pago de Comision Nro. $pago->pve_id, " . $pago->pve_glosa;
                $pag_fecha = $pago->pve_fecha;
                $moneda = $pago->pve_moneda;
                $interesado = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno,vendedor where vdo_id=$pago->pve_vdo_id and vdo_int_id=int_id ");
                $params = array(
                    'tabla' => 'pago_vendedores',
                    'tabla_id' => $pago->pve_id,
                    'fecha' => $pag_fecha,
                    'moneda' => $moneda,
                    'ingreso' => false,
                    'une_id' => 0,
                    'glosa' => $glosa, 'ca' => '0', 'cf' => '0', 'cc' => '0',
                );

                $detalles = FORMULARIO::insertar_pagos($params);

                $data = array(
                    'moneda' => $moneda,
                    'ges_id' => $_SESSION[ges_id],
                    'fecha' => $pag_fecha,
                    'glosa' => $glosa,
                    'interno' => $interesado,
                    'tabla_id' => $pago->pve_id,
                    'urb' => null,
                    'vdo_can_codigo' => 0,
                    'monto' => $pago->pve_monto,
                    'detalles' => $detalles,
                );

                $comprobante = MODELO_COMPROBANTE::pago_vendedor($data);
                COMPROBANTES::registrar_comprobante($comprobante);
            } else {
                echo "ya existe comprobante $pago->pve_id<br>";
            }
        } else {
            echo "no hay codigo $pago->pve_id<br>";
        }
    }
}

function modificar_cmp_venta() {
    $filtro = " and ven_id in (7710,7711,7712,7713,7714,7715,7719,7720,7721,7722,7723,7724,7725,7726,7727,7728,7729,8380,8381,8382)";
//    $filtro=" and ven_id=7710";
    $ventas = FUNCIONES::objetos_bd_sql("select * from venta where 1=1 $filtro");
    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta' and cmp_tabla_id='$venta->ven_id' and cmp_eliminado='No'");
        if ($cmp) {
            echo "MODIFICAR $venta->ven_id<br>";
            mod_cmp_venta($venta, $cmp);
        } else {
            echo "NO HAY VENTA $venta->ven_id<br>";
        }
        $ventas->siguiente();
    }
    echo $ventas->get_num_registros();
}

function mod_cmp_venta($venta, $cmp) {
    $conec = new ADO();
    $conec->begin_transaccion();
    $id_lote = $venta->ven_lot_id;
    $lote = FUNCIONES::objeto_bd_sql("select * from lote where lot_id='$venta->ven_lot_id'");

    $concepto = FUNCIONES::get_concepto($id_lote);

    if (true) {
        if (true) {
            $ven_anticipo = $venta->ven_res_anticipo;
            $ven_fecha = $venta->ven_fecha;
            $superficie = $lote->lot_superficie;
            $valor_metro = $venta->ven_metro;
            $valor = $superficie * $valor_metro;
            $descuento = $venta->ven_decuento;
//            $monto=$valor-$descuento;
            $monto_intercambio = $venta->ven_monto_intercambio;
            $monto_efectivo = $venta->ven_monto_efectivo;

            $monto_pagar = $venta->ven_monto_pagar; //devolver

            $int_id = $venta->ven_int_id;
            $urb_id = $venta->ven_urb_id;
            $moneda = $venta->ven_moneda;

            $cambio_usd = 1;
            if ($moneda == '1') {
                $cambio_usd = FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$ven_fecha' and tca_mon_id=2");
            }
            $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$urb_id");

            $costo = $superficie * ($urb->urb_val_costo * $cambio_usd);
            $costo_cub = $ven_anticipo + $monto_intercambio;

            if ($costo_cub > $costo) {
                $costo_cub = $costo;
            }

            $llave = $venta->ven_id;

            if ($urb->urb_tipo == 'Interno') {
                $ges_id = 15;
                include_once 'clases/modelo_comprobantes.class.php';
                include_once 'clases/registrar_comprobantes.class.php';
                $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$int_id'");
                $glosa = "Venta Nro. $llave - $referido - " . $concepto;
                $data = array(
                    'moneda' => $moneda,
                    'ges_id' => $ges_id,
                    'fecha' => $ven_fecha,
                    'glosa' => $glosa,
                    'interno' => $referido,
                    'tabla_id' => $llave,
                    'urb' => $urb,
                    'anticipo' => $ven_anticipo,
                    'saldo_efectivo' => $monto_efectivo,
                    'monto_intercambio' => $monto_intercambio,
                    'intercambio_ids' => '',
                    'intercambio_montos' => '',
                    'monto_pagar' => $monto_pagar,
                    'costo' => $costo,
                    'costo_cub' => $costo_cub,
                );

                $comprobante = MODELO_COMPROBANTE::venta($data);

                $comprobante->cmp_id = $cmp->cmp_id;
                $comprobante->usu_per_id = $cmp->cmp_usu_id;
                $comprobante->usu_id = $cmp->cmp_usu_cre;

//                echo "<pre>";
//                print_r($comprobante);
//                echo "</pre>";

                COMPROBANTES::modificar_comprobante($comprobante);
            }
        }
    }
}

function generar_cmp_venta() {
    $ventas = FUNCIONES::objetos_bd_sql("select * from venta where ven_fecha>='2015-01-01' and ven_fecha<='2015-11-25' order by ven_id asc limit 1");


    for ($i = 0; $i < $ventas->get_num_registros(); $i++) {
        $venta = $ventas->get_objeto();
        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta' and cmp_tabla_id='$venta->ven_id' and cmp_eliminado='No'");
        if (!$cmp) {
            echo "generar $venta->ven_id<br>";
            cmp_venta($venta);
        }

        $ventas->siguiente();
    }
    echo $ventas->get_num_registros();
}

function cmp_venta($venta) {
    $conec = new ADO();
    $conec->begin_transaccion();
    $id_lote = $venta->ven_lot_id;
    $lote = FUNCIONES::objeto_bd_sql("select * from lote where lot_id='$venta->ven_lot_id'");

    $concepto = FUNCIONES::get_concepto($id_lote);

    if (true) {
        if (true) {

            $ven_anticipo = $venta->ven_res_anticipo;

            $ven_fecha = $venta->ven_fecha;
            $superficie = $lote->lot_superficie;
            $valor_metro = $venta->ven_metro;
            $valor = $superficie * $valor_metro;
            $descuento = $venta->ven_decuento;
//            $monto=$valor-$descuento;
            $monto_intercambio = $venta->ven_monto_intercambio;
            $monto_efectivo = $venta->ven_monto_efectivo;

            $monto_pagar = $venta->ven_monto_pagar; //devolver


            $int_id = $venta->ven_int_id;
            $urb_id = $venta->ven_urb_id;
            $moneda = $venta->ven_moneda;

            $cambio_usd = 1;
            if ($moneda == '1') {
                $cambio_usd = FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$ven_fecha' and tca_mon_id=2");
            }



            $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$urb_id");

            $costo = $superficie * ($urb->urb_val_costo * $cambio_usd);
            $costo_cub = $ven_anticipo + $monto_intercambio;

            if ($costo_cub > $costo) {
                $costo_cub = $costo;
            }

            $llave = $venta->ven_id;

            if ($urb->urb_tipo == 'Interno') {
                $ges_id = 15;
                include_once 'clases/modelo_comprobantes.class.php';
                include_once 'clases/registrar_comprobantes.class.php';
                $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$int_id'");
                $glosa = "Venta Nro. $llave - $referido - " . $concepto;
                $data = array(
                    'moneda' => $moneda,
                    'ges_id' => $ges_id,
                    'fecha' => $ven_fecha,
                    'glosa' => $glosa,
                    'interno' => $referido,
                    'tabla_id' => $llave,
                    'urb' => $urb,
                    'anticipo' => $ven_anticipo,
                    'saldo_efectivo' => $monto_efectivo,
                    'monto_intercambio' => $monto_intercambio,
                    'intercambio_ids' => '',
                    'intercambio_montos' => '',
                    'monto_pagar' => $monto_pagar,
                    'costo' => $costo,
                    'costo_cub' => $costo_cub,
                );

                $comprobante = MODELO_COMPROBANTE::venta($data);
                $comprobante->usu_per_id = '1';
                $comprobante->usu_id = 'admin';
                COMPROBANTES::registrar_comprobante($comprobante);
            }
        }
    }
}

function modificar_cmp_pagos() {
    $conec = new ADO();
    $filtro = " and vpag_id in (110441,115141,115142,115143,115144,115145,115146,115147,115148,115149,115150,115151,115152,115153,115154,115155,115156,115160,115161,115162,115163,115164,115165,115166,115167,115168,115169,115170,115171,115172,115173,115174,115175,115176,115177,115178,115179,115180,115181,115182,115183,115184,115185,115186,115187,115188,115189,115190,115191,115192,115193,115196,115197,115198,115199,115200,115201,115202,115203,115204,115205,115206,115209,115210,115211,115212,115213,115214,115215,115216,115217,115218,115219,115220,115221,115223,115224,115225,115226,115230,115232,115235,115236,115238,115239,115240,115242,115243,115245,115246,115247,115248,115249,115250,115251,115252,115253,115254,115255,115256,115257,115258,115259,115263,115264,115265,115266,115267,115269,115270,115271,115272,115276,115277,115279,115280,115281,115282,115285,115286,115287,115288,115289,115292,115293,115294,115295,115296,115297,115298,115299,115301,115302,115304,115305,115306,115307,115308,115309,115310,115311,115312,115313,115314,115315,115316,115317,115318,115319,115320,115321,115322,115323,115324,115325,115326,115327,115328,115329,115332,115333,115335,115338,115339,115340,115341,115342,115343,115344,115345,115346,115347,115348,115349,115350,115351,115352,115353,115354,115355,115356,115357,115358,115359,115360,115361,115362,115363,115364,115365,115366,115367,115368,115369,115371,115372,115373,115374,115375,115376,115377,115380,115381,115382,115383,115384,115385,115386,115387,115388,115389,115390,115391,115392,115393,115394,115395,115396,115397,115398,115399,115401,115402,115404,115405,115406,115407,115408,115409,115412,115415,115416,115417,115418,115419,115420,115421,115422,115423,115424,115425,115426,115427,115428,115429,115430,115431,115432,115433,115434,115435,115436,115437,115438,115439,115440,115441,115442,115443,115444,115449,115450,115451,115452,115458,115459,115460,115461,115462,115463,115464,115465,115466,115467,115468,115469,115470,115471,115472,115473,115474,115475,115476,115477,115478,115479,115480,115481,115482,115483,115484,115486,115487,115490,115491,115492,115494,115495,115496,115497,115499,115500,115501,115502,115503,115504,115505,115506,115508,115509,115516,115517,115518,115520,115522,115523,115524,115525,115526,115527,115528,115529,115531,115532,115533,115534,115535,115537,115538,115539,115540,115541,115542,115543,115544,115545,115546,115547,115548,115549,115550,115551,115552,115553,115554,115555,115556,115557,115558,115559,115560,115561,115562,115564,115565,115566,115567,115568,115569,115570,115571,115572,115573,115574,115575,115577,115578,115579,115580,115581,115582,115583,115585,115586,115587,115589,115590,115591,115592,115593,115594,115595,115596,115597,115598,115599,115600,115601,115602,115603,115604,117340,117341,117342,117343,117344,117345,117346,117347,117348,117349,117350,117351,117352,117353,117354,117355,117356,117357,117358,117359,117360,117361,117362,117363,117364,117365,117366,117369,117370,117371,117372,117373,117374,117375,117376,117377,117378,117379,117380,117381,117382,117384,117385,117386,117387,117388,117389,117390,117391,117392,117393,117394,117395,117398,117399,117400,117401,117402,117403,117404,117405,117406,117407,117408,117409,117410,117411,117412,117413,117414,117415,117416,117417,117418,117419,117420,117421,117422,117423,117424,117425,117426,117427,117428,117430,117431,117432,117433,117434,117435,117436,117437,117438,117439,117440,117441,117442,117443,117444,117445,117449,117450,117451,117452,117453,117454,117455,117456,117457,117458,117459,117460,117461,117462,117463,117464,117465,117466,117467,117468,117469,117470,117471,117472,117473,117474,117475,117478,117479,117480,117481,117482,117483,117484,117485,117486,117487,117488,117489,117490,117491,117492,117493,117494,117495,117496,117497,117498,117499,117500,117501,117502,117503,117504,117505,117506,117507,117508,117509,117510,117511,117512,117513,117514,117515,117516,117517,117518,117519,117521,117522,117523,117524,117525,117526,117527,117528,117529,117530,117531,117532,117533,117534,117535,117536,117537,117538,117539,117540,117541,117542,117543,117544,117545,117546,117547,117548,117549,117550,117551,117552,117553,117554,117555,117556,117557,117558,117559,117560,117561,117562,117563,117564,117565,117566,117567,117568,117569,117570,117571,117573,117574,117575,117576,117577,117578,117579,117580,117581,117582,117583,117586,117588,117589,117590,117591,117595,117596,117597,117598,117599,117604,117605,117608,117609,117610,117611,117612,117613,117614,117616,117617,117618,117619,117620,117621,117622,117623,117624,117625,117626,117627,117628,117629,117630,117631,117632,117633,117636,117637,117638,117639,117640,117641,117642,117643,117644,117645,117646,117647,117648,117649,117650,117651,117652,117653,117655,117656,117659,117660,117661,117662,117663,117665,117667,117670,117672,117673,117676,117677,117678,117679,117680,117681,117682,117683,117684,117685,117686,117687,117688,117689,117690,117691,117692,117693,117694,117695,117696,117697,117698,117699,117700,117701,117702,117703,117704,117705,117706,117707,117708,117709,117710,117711,117712,117713,117716,117717,117718,117719,117720,117721,117722,117723,117724,117725,117726,117727,117728,117729,117730,117731,117732,117733,117734,117735,117736,117739,117741,117742,117743,117744,117745,117746,117747,117748,117749,117750,117751,117752,117753,117754,117755,117756,117757,117758,117759,117760,117763,117765,117766,117767,117768,117769,117770,117771,117772,117773,117774,117775,117776,117777,117779,117780,117781,117782,117783,117784,117787,117788,117789,117790,117792,117793,117794,117795,117796,117797,117798,117799,117800,117801,117802,117803,117804,117805,117806,117807,117808,117809,117810,117811,117816,117818,117819,117820,117821,117822,117823,117824,117825,117826)";
//    $filtro=" and vpag_id = 115142";
    $pagos = FUNCIONES::objetos_bd_sql("select * from venta_pago where 1=1 $filtro");

    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pago = $pagos->get_objeto();
        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_pago' and cmp_tabla_id='$pago->vpag_id' and cmp_eliminado='No'");
        if ($cmp) {

            echo "MODIFICAR $pago->vpag_id<br>";
            mod_cmp_pago($pago, $cmp);
//            cmp_venta($venta);
        } else {
//            $sql_up="update venta_pago set vpag_cmp='1' where vpag_id='$pago->vpag_id'";
//            $conec->ejecutar($sql_up,false,false);
//            echo "ya fue generado<br>";
        }

        $pagos->siguiente();
    }
    echo $pagos->get_num_registros() . '<br>';
}

function mod_cmp_pago($pago, $cmp) {
    $conec = new ADO();

    $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pago->vpag_ven_id'");

    $monto = $pago->vpag_monto;
    $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$venta->ven_urb_id");
    if ($urb->urb_tipo == 'Interno') {
        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';
        $nro_recibo = $pago->vpag_recibo;
        $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$venta->ven_int_id'");
        $glosa = "Pago de la Venta Nro. $venta->ven_id - $venta->ven_concepto - $referido - Rec. $nro_recibo";
        $ges_id = $cmp->cmp_ges_id;
        $det_pagos = FUNCIONES::lista_bd_sql("select * from con_pago where fpag_tabla='venta_pago' and fpag_tabla_id='$pago->vpag_id' and fpag_estado='Activo'");
        $detalles = array();
        foreach ($det_pagos as $dpag) {
            $dmonto = $dpag->fpag_monto;
            if ($pago->vpag_moneda == 2) {
                if ($dpag->fpag_mon_id == 1) {
                    $dmonto = $dpag->fpag_monto / 6.96;
                }
            } elseif ($pago->vpag_moneda == 1) {
                if ($dpag->fpag_mon_id == 2) {
                    $dmonto = $dpag->fpag_monto * 6.96;
                }
            }
            $detalles[] = array("cuen" => $dpag->fpag_cue_id, "debe" => $dmonto, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $dpag->fpag_une_id
            );
        }
//        FORMULARIO::insertar_pagos($params);
//        '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_monto'
        $fecha_pago = $pago->vpag_fecha_pago;
        $data = array(
            'moneda' => $venta->ven_moneda,
            'ges_id' => $ges_id,
            'fecha' => $fecha_pago,
            'glosa' => $glosa,
            'interno' => $referido,
            'tabla_id' => $pago->vpag_id,
            'urb' => $urb,
            'interes' => $pago->vpag_interes * 1,
            'capital' => $pago->vpag_capital * 1,
            'form' => $pago->vpag_form * 1,
            'envio' => $pago->vpag_envio * 1,
            'mora' => $pago->vpag_mora * 1,
            'detalles' => $detalles,
            'costo' => $pago->vpag_costo,
        );
//        echo "<pre>";
//        print_r($data);
//        echo "</pre>";
        $comprobante = MODELO_COMPROBANTE::pago_cuota($data);
        $comprobante->cmp_id = $cmp->cmp_id;
        $comprobante->usu_per_id = $cmp->cmp_usu_id;
        $comprobante->usu_id = $cmp->cmp_usu_cre;
//        echo "<pre>";
//        print_r($comprobante);
//        echo "</pre>";
        COMPROBANTES::modificar_comprobante($comprobante);
//        $sql_up="update venta_pago set vpag_cmp='1' where vpag_id='$pago->vpag_id'";
//        $conec->ejecutar($sql_up,false,false);
    }
}

function generar_cmp_pagos() {
    $conec = new ADO();
    $pagos = FUNCIONES::objetos_bd_sql("select * from venta_pago where vpag_fecha_pago >='2015-01-01' and vpag_fecha_pago<='2015-12-31' and vpag_monto>0 and vpag_cmp='0' order by vpag_id asc limit 10");


    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pago = $pagos->get_objeto();
        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_pago' and cmp_tabla_id='$pago->vpag_id' and cmp_eliminado='No'");
        if (!$cmp) {
//            echo "generar $pago->vpag_id<br>";
            cmp_pago($pago);
//            cmp_venta($venta);
        } else {
            $sql_up = "update venta_pago set vpag_cmp='1' where vpag_id='$pago->vpag_id'";
            $conec->ejecutar($sql_up, false, false);
            echo "ya fue generado<br>";
        }

        $pagos->siguiente();
    }
    echo $pagos->get_num_registros() . '<br>';
}

function cmp_pago($pago) {
    $auser = array(
        'ramadacaja' => '1.1.1.01.2.10',
        'lapazsistema' => '1.1.1.01.2.18',
        'cartera20' => '1.1.1.01.2.27',
        'CAJA1' => '1.1.1.01.2.09',
        'caja8' => '1.1.1.01.2.19',
        'carterabrasil' => '1.1.1.01.2.08',
        'cartera10' => '1.1.1.01.2.27',
        'cartera4' => '1.1.1.01.2.13',
        'cartera15' => '1.1.1.01.2.27',
        'cajagraciela' => '1.1.1.01.2.16',
        'ramadasistema' => '1.1.1.01.2.21',
        'sysmauricio' => '1.1.1.01.2.22',
        'sysjose' => '1.1.1.01.2.27',
        'cartera18' => '1.1.1.01.2.12',
        'ramadacartera' => '1.1.1.01.2.21',
        'sys_daniel' => '1.1.1.01.2.27',
        'cartera21' => '1.1.1.01.2.14',
        'Eufronio' => '1.1.1.01.2.27',
        'consulta1' => '1.1.1.01.2.27',
        'carteracbba' => '1.1.1.01.2.19',
        'contador2' => '1.1.1.01.2.27',
        'carbrenda' => '1.1.1.01.2.27',
        'cartera17' => '1.1.1.01.2.27',
        
        'caja1'=>'1.1.1.01.2.09',
        'CARTERA21'=>'1.1.1.01.2.14',
        'CARTERABRASIL'=>'1.1.1.01.2.08',
        'Cartera18'=>'1.1.1.01.2.12',
        'CAJA8'=>'1.1.1.01.2.19',
        'LAPAZSISTEMA'=>'1.1.1.01.2.18',
        'eufronio'=>'1.1.1.01.2.27'
    );

    $conec = new ADO();
//    $conec->begin_transaccion();
//    echo "select * from venta where ven_id='$pago->vpag_ven_id'";
    $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pago->vpag_ven_id'");
//    $id_lote=$venta->ven_lot_id;
//    $lote = FUNCIONES::objeto_bd_sql("select * from lote where lot_id='$venta->ven_lot_id'");
//    $concepto= FUNCIONES::get_concepto($id_lote);
    $monto = $pago->vpag_monto;
    $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$venta->ven_urb_id");
    if ($urb->urb_tipo == 'Interno') {
        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';
        $nro_recibo = $pago->vpag_recibo;
        $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$venta->ven_int_id'");
        $glosa = "Pago de la Venta Nro. $venta->ven_id - $venta->ven_concepto - $referido - Rec. $nro_recibo ($pago->vpag_usu_import)";
        $ges_id = 15;
//        $params=array(
//                'tabla'=>'venta_pago',
//                'tabla_id'=>$pago_id,
//                'fecha'=>$fecha_pago,
//                'moneda'=>$venta->ven_moneda,
//                'ingreso'=>true,
//                'une_id'=>$urb->urb_une_id,
//                'glosa'=>$glosa,'ca'=>'0','cf'=>0,'cc'=>0
//            );
        if ($pago->vpag_usu_import) {
            $cu_pago = $auser[$pago->vpag_usu_import];
        } else {
            $cu_pago = '1.1.1.01.2.27';
        }
        $detalles = array(
            array("cuen" => FUNCIONES::get_cuenta($ges_id, $cu_pago), "debe" => $monto, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            )
        );
//        FORMULARIO::insertar_pagos($params);
//        '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_monto'
        $fecha_pago = $pago->vpag_fecha_pago;
        $data = array(
            'moneda' => $venta->ven_moneda,
            'ges_id' => $ges_id,
            'fecha' => $fecha_pago,
            'glosa' => $glosa,
            'interno' => $referido,
            'tabla_id' => $pago->vpag_id,
            'urb' => $urb,
            'interes' => $pago->vpag_interes * 1,
            'capital' => $pago->vpag_capital * 1,
            'form' => $pago->vpag_form * 1,
            'envio' => $pago->vpag_envio * 1,
            'mora' => $pago->vpag_mora * 1,
            'detalles' => $detalles,
            'costo' => $pago->vpag_costo,
        );

        $comprobante = MODELO_COMPROBANTE::pago_cuota($data);
        $comprobante->usu_per_id = '1';
        $comprobante->usu_id = 'admin';
        COMPROBANTES::registrar_comprobante($comprobante);
        $sql_up = "update venta_pago set vpag_cmp='1' where vpag_id='$pago->vpag_id'";
        $conec->ejecutar($sql_up, false, false);
    }
}
function modificar_cmp_pagos_importado() {
    $conec = new ADO();
    $sql_sel="select distinct venta_pago.* from venta_pago 
                inner join con_comprobante on (cmp_tabla='venta_pago' and cmp_tabla_id=vpag_id)
                inner join con_comprobante_detalle on (cmp_id=cde_cmp_id and cde_cue_id=997 and cde_mon_id=1)
                limit 2000
            ";
//    $sql_sel="select * from venta_pago where vpag_fecha_pago >='2015-01-01' and vpag_fecha_pago<='2015-12-31' and vpag_monto>0 and vpag_cmp='0' order by vpag_id asc limit 10";
    $pagos = FUNCIONES::objetos_bd_sql($sql_sel);
    echo $pagos->get_num_registros().'<br>';
//    return;
    for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
        $pago = $pagos->get_objeto();
        $cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tabla='venta_pago' and cmp_tabla_id='$pago->vpag_id' and cmp_eliminado='No'");
        if ($cmp) {
//            echo "generar $pago->vpag_id<br>";
            mod_cmp_pago_importado($cmp,$pago);
//            cmp_venta($venta);
        } else {
            echo "No fue generado<br>";
        }

        $pagos->siguiente();
    }
    echo $pagos->get_num_registros() . '<br>';
}

function mod_cmp_pago_importado($cmp,$pago) {
    $auser = array(
        'ramadacaja' => '1.1.1.01.2.10',
        'lapazsistema' => '1.1.1.01.2.18',
        'cartera20' => '1.1.1.01.2.27',
        'CAJA1' => '1.1.1.01.2.09',
        'caja8' => '1.1.1.01.2.19',
        'carterabrasil' => '1.1.1.01.2.08',
        'cartera10' => '1.1.1.01.2.27',
        'cartera4' => '1.1.1.01.2.13',
        'cartera15' => '1.1.1.01.2.27',
        'cajagraciela' => '1.1.1.01.2.16',
        'ramadasistema' => '1.1.1.01.2.21',
        'sysmauricio' => '1.1.1.01.2.22',
        'sysjose' => '1.1.1.01.2.27',
        'cartera18' => '1.1.1.01.2.12',
        'ramadacartera' => '1.1.1.01.2.21',
        'sys_daniel' => '1.1.1.01.2.27',
        'cartera21' => '1.1.1.01.2.14',
        'Eufronio' => '1.1.1.01.2.27',
        'consulta1' => '1.1.1.01.2.27',
        'carteracbba' => '1.1.1.01.2.19',
        'contador2' => '1.1.1.01.2.27',
        'carbrenda' => '1.1.1.01.2.27',
        'cartera17' => '1.1.1.01.2.27',
        
        'caja1'=>'1.1.1.01.2.09',
        'CARTERA21'=>'1.1.1.01.2.14',
        'CARTERABRASIL'=>'1.1.1.01.2.08',
        'Cartera18'=>'1.1.1.01.2.12',
        'CAJA8'=>'1.1.1.01.2.19',
        'LAPAZSISTEMA'=>'1.1.1.01.2.18',
        'eufronio'=>'1.1.1.01.2.27'
    );

    $conec = new ADO();
//    $conec->begin_transaccion();
//    echo "select * from venta where ven_id='$pago->vpag_ven_id'";
    $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$pago->vpag_ven_id'");
//    $id_lote=$venta->ven_lot_id;
//    $lote = FUNCIONES::objeto_bd_sql("select * from lote where lot_id='$venta->ven_lot_id'");
//    $concepto= FUNCIONES::get_concepto($id_lote);
    $monto = $pago->vpag_monto;
    $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id=$venta->ven_urb_id");
    if ($urb->urb_tipo == 'Interno') {
        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';
        $nro_recibo = $pago->vpag_recibo;
        $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$venta->ven_int_id'");
        $glosa = "Pago de la Venta Nro. $venta->ven_id - $venta->ven_concepto - $referido - Rec. $nro_recibo ($pago->vpag_usu_import)";
        $ges_id = 15;
//        $params=array(
//                'tabla'=>'venta_pago',
//                'tabla_id'=>$pago_id,
//                'fecha'=>$fecha_pago,
//                'moneda'=>$venta->ven_moneda,
//                'ingreso'=>true,
//                'une_id'=>$urb->urb_une_id,
//                'glosa'=>$glosa,'ca'=>'0','cf'=>0,'cc'=>0
//            );
        if ($pago->vpag_usu_import) {
            $cu_pago = $auser[$pago->vpag_usu_import];
            
        } else {
            $cu_pago = '1.1.1.01.2.27';
        }
        $detalles = array(
            array("cuen" => FUNCIONES::get_cuenta($ges_id, $cu_pago), "debe" => $monto, "haber" => 0,
                "glosa" => $glosa, "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $urb->urb_une_id
            )
        );
//        FORMULARIO::insertar_pagos($params);
//        '$cob->vcob_interes' ,'$cob->vcob_capital','$cob->vcob_mora','$cob->vcob_form','$cob->vcob_monto'
        $fecha_pago = $pago->vpag_fecha_pago;
        $data = array(
            'moneda' => $venta->ven_moneda,
            'ges_id' => $ges_id,
            'fecha' => $fecha_pago,
            'glosa' => $glosa,
            'interno' => $referido,
            'tabla_id' => $pago->vpag_id,
            'urb' => $urb,
            'interes' => $pago->vpag_interes * 1,
            'capital' => $pago->vpag_capital * 1,
            'form' => $pago->vpag_form * 1,
            'envio' => $pago->vpag_envio * 1,
            'mora' => $pago->vpag_mora * 1,
            'detalles' => $detalles,
            'costo' => $pago->vpag_costo,
        );

        $comprobante = MODELO_COMPROBANTE::pago_cuota($data);
        $comprobante->cmp_id = $cmp->cmp_id;
        $comprobante->usu_per_id = $cmp->cmp_usu_id;
        $comprobante->usu_id = $cmp->cmp_usu_cre;
        COMPROBANTES::modificar_comprobante($comprobante);
        $sql_up = "update venta_pago set vpag_cmp='1' where vpag_id='$pago->vpag_id'";
        $conec->ejecutar($sql_up, false, false);
    }
}