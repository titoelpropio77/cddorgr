<?php

require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
include_once 'clases/modelo_comprobantes.class.php';
require_once 'clases/registrar_comprobantes.class.php';
require_once 'clases/formulario.class.php';

if ($_GET[venta]*1 == 0) {
    echo "<p>Indique la venta a convertirse mlm.</p>";
    return false;
}
$oferta = 0;
if ($_GET[oferta]*1 > 0) {
    $oferta = $_GET[oferta]*1;
}

$ven_id = $_GET[venta]*1;
$venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$ven_id'");

if ($venta->ven_multinivel === 'no') {
    //MLM::agregar_asociado
    include_once 'clases/mlm.class.php';

    $vendedor = 81;
    $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
    $fecha_act_mlm = FUNCIONES::atributo_bd_sql("select 
                                    DATE_ADD('{$venta->ven_fecha}',INTERVAL 60 month)as campo");

                                    
    $hoy = date('Y-m-d');
    $now = date('H:i:s');
                                    
    $ven_log = $venta->ven_log . "|$hoy $now - Se convirtio en venta multinivel, anterior vendedor vdo_id:$venta->ven_vdo_id.";
    $sql_com = "update venta set ven_porc_comisiones='$urb->urb_porc_comisiones',
    ven_vdo_id='$vendedor',
    ven_multinivel='si',
    ven_log='$ven_log',
    ven_fecha_act_mlm='$fecha_act_mlm'
    where ven_id=$venta->ven_id";
    
    FUNCIONES::bd_query($sql_com);

    $data_asociado = array(
        'int_id' => $venta->ven_int_id,
        'vdo_id' => $vendedor,
        'ven_id' => $venta->ven_id
    );
    MLM::agregar_asociado($data_asociado);


    if ($oferta > 0) {
        include_once 'clases/oferta.class.php';
        $data_oferta = array('venta' => $venta->ven_id, 'oferta' => $oferta);
        OFERTA::guardar_venta_oferta($data_oferta);
    }
    $data_bono = array('ven_id' => $venta->ven_id, 'oferta' => $oferta);
    MLM::calcular_montos_bonos($data_bono);
    $data_cobro = array('venta' => $venta->ven_id, 'vendedor' => $vendedor);
    MLM::insertar_comisiones_cobro($data_cobro);
} else {
    echo "<p>La venta ya es multinivel...</p>";
    return false;
}