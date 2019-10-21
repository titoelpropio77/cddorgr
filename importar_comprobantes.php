<?php

require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'excel/reader.php';

$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read("comprobantes2.xls");
$colum=0;
$filasTotal = $data->sheets[$colum]['numRows'];
echo $filasTotal;
//return;
$list_pagos = array();

$i = 1;


while ($i <= $filasTotal) {
    $pago = new stdClass();
    $pago->nro = trim($data->sheets[$colum]['cells'][$i][2]);
    $pago->tipo = trim($data->sheets[$colum]['cells'][$i][5]);
    $pago->forma_pago = trim($data->sheets[$colum]['cells'][$i][9]);    

    $pago->fecha = trim($data->sheets[$colum]['cells'][$i + 1][2]);
    $pago->tc = trim($data->sheets[$colum]['cells'][$i + 1][5]);    
    $pago->banco = trim($data->sheets[$colum]['cells'][$i + 1][9]);    

    $pago->nro_doc = trim($data->sheets[$colum]['cells'][$i + 2][2]);
    $pago->ban_nro = trim($data->sheets[$colum]['cells'][$i + 2][9]);    
    
    $pago->glosa = trim($data->sheets[$colum]['cells'][$i + 3][2]);    
    
    
    $j = $i + 6;
    $sw = true;
    $detalles = array();
    while ($sw) {
        $detalle = new stdClass();
        if ($data->sheets[$colum]['cells'][$j][1] != "") {
            $cuenta = obtener_cod_desc($data->sheets[$colum]['cells'][$j][1]);
            $detalle->codigo = $cuenta->codigo;
            $detalle->descripcion = $cuenta->descripcion;
            $detalle->glosa = trim($data->sheets[$colum]['cells'][$j][4]);
            $detalle->debe = trim($data->sheets[$colum]['cells'][$j][9]);
            $detalle->haber = trim($data->sheets[$colum]['cells'][$j][11]);
            $detalle->ca = trim($data->sheets[$colum]['cells'][$j][12]);
            $detalles[] = $detalle;
        } else {
            $sw = false;
        }

        $j++;
    }
    $pago->detalles = $detalles;
    $i = $j;
    $list_pagos[] = $pago;
}

//echo'<pre>';
//print_r($list_comprobantes);
//echo'</pre>';


$_comprobantes = array();
foreach ($list_pagos as $pago) {
    $comprobante = new stdClass();
    $comprobante->tipo = obtener_tipo($pago->tipo);
    $comprobante->mon_id = 2;
    $comprobante->nro_documento = $pago->nro_doc;
    $comprobante->fecha = FUNCIONES::get_fecha_mysql($pago->fecha);
    $comprobante->ges_id = 1;
    $comprobante->peri_id = obtener_periodo(FUNCIONES::get_fecha_mysql($pago->fecha));
    
    $tipo_comprobante=obtener_id_comp($comprobante->tipo);
    $comprobante->forma_pago=obtener_forma_pago_low($pago->forma_pago);
    $cmp_forma_pago=$comprobante->forma_pago;
    if($cmp_forma_pago=='Efectivo'|| $cmp_forma_pago==''){
        $comprobante->ban_id=0;
        $comprobante->ban_char='';
        $comprobante->ban_nro='';
    }  elseif ($cmp_forma_pago=='Cheque') {            
        if($tipo_comprobante==1){
            $comprobante->ban_id=0;
            $comprobante->ban_char=$pago->banco;
            $comprobante->ban_nro=$pago->ban_nro;
        }  elseif ($tipo_comprobante==2) {
            $comprobante->ban_id=  FUNCIONES::atributo_bd("con_banco", "ban_nombre='$pago->banco'", "ban_id");
            $comprobante->ban_char='';
            $comprobante->ban_nro=$pago->ban_nro;
        }

    } elseif($cmp_forma_pago=='Deposito'){
        if($tipo_comprobante==1){
            $comprobante->ban_id=FUNCIONES::atributo_bd("con_banco", "ban_nombre='$pago->banco'", "ban_id");
            $comprobante->ban_char='';
            $comprobante->ban_nro=$pago->ban_nro;               
        }  elseif ($tipo_comprobante==2) {
            $comprobante->ban_id=0;
            $comprobante->ban_char=$pago->banco;        
            $comprobante->ban_nro=$pago->ban_nro;
        }

    } elseif ($cmp_forma_pago=='Transferencia' ) {
        $comprobante->ban_id=0;
        $comprobante->ban_char="";
        $comprobante->ban_nro='21';
    }
    
    $comprobante->glosa = $pago->glosa;
    $comprobante->referido = "";
    $comprobante->tabla = "";
    $comprobante->tabla_id = 0;
    $detalles = $pago->detalles;
    $_detalles = array();
    foreach ($detalles as $detalle) {
        $_detalle = new stdClass();
        $_detalle->ca = obtener_cuenta_analitica($detalle->ca);
        $_detalle->cc = 14;
        $_detalle->cf = 0;
        $_detalle->cuen = obtener_cuenta($detalle->codigo);
        $_detalle->glosa = $detalle->glosa;
        $_detalle->debe = $detalle->debe;
        $_detalle->haber = $detalle->haber;
        $_detalles[] = $_detalle;
    }
    $comprobante->detalles = $_detalles;
    $_comprobantes[] = $comprobante;
}
//echo '<br>'.count($_comprobantes);
//echo '<pre>';
//print_r($_comprobantes);
//echo '</pre>';

for ($i = 0; $i < count($_comprobantes); $i++) {
    $comprobante_aux=$_comprobantes[$i];
    COMPROBANTES::registrar_comprobante($comprobante_aux);
}

echo 'OK.';

//
//foreach ($_comprobantes as $comprobante) {
//    COMPROBANTES::registrar_comprobante($comprobante);
//}

//echo '<pre>';
//print_r($_comprobantes);
//echo '</pre>';

function obtener_id_comp($tipo){
    switch ($tipo) {
    case "Ingreso":
        return 1;
        break;
    case "Egreso":
        return 2;
        break;
    case "Diario":
        return 3;
        break;
    default:
        return 1;
        break;
}
}

function obtener_cuenta_analitica($codigo) {
    $can_id=  FUNCIONES::atributo_bd("con_cuenta_ca", "can_ges_id=1 and can_codigo='$codigo'", "can_id");
    return $can_id;
}
function obtener_cuenta($codigo) {
    $cuenta=  FUNCIONES::atributo_bd("con_cuenta", "cue_ges_id=1 and cue_codigo='$codigo'", "cue_id");
    return $cuenta;
}

function obtener_tipo($tipo) {
    if ($tipo == "I") {
        return "Ingreso";
    } elseif ($tipo == "E") {
        return "Egreso";
    } elseif ($tipo == "T") {
        return "Diario";
    } else {
        return "Ingreso";
    }
}

function obtener_forma_pago_low($forma_pago){
    if($forma_pago=="EFECTIVO"){
        return "Efectivo";
    }elseif($forma_pago=="CHEQUE"){
        return "Cheque";
    }elseif($forma_pago=="DEPOSITO"){
        return "Deposito";
    }elseif($forma_pago=="TRANSFERENCIA"){
        return "Transferencia";
    }
}


function obtener_cod_desc($txtcuenta) {
    $object = new stdClass();
    $object->codigo = trim(substr($txtcuenta, 0, 12));
    $object->descripcion = trim(substr($txtcuenta, 12));
    return $object;
}

function obtener_periodo($fecha) {
    $periodo = FUNCIONES::atributo_bd("con_periodo", "pdo_fecha_inicio<='$fecha' and pdo_fecha_fin>='$fecha'", 'pdo_id');
    return $periodo;
}
?>