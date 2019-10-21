<?php

ini_set('memory_limit', '256M');
date_default_timezone_set("America/La_Paz");
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');

//ini_set('display_errors', 'On');
$urb_id = $_GET[urb_id] ? $_GET[urb_id] : '2';
$uv= isset($_GET[uv]) ? $_GET[uv] : '';
$and_filtro="";
if($uv){
    $and_filtro=" and lot_uv_id=$uv";
}
$lotes = FUNCIONES::objetos_bd_sql("select lot_id, lot_estado, cor_lot_coorX,cor_lot_coorY from lote, lote_cordenada_imagen where cor_lot_id=lot_id and cor_urb_id='$urb_id' $and_filtro");
if ($lotes->get_num_registros() > 0) {
    $urb=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$urb_id'");
    $str_uv="";
    if($uv){
        $str_uv="0$uv";
    }
    
    $img = imagecreatefromjpeg("imagenes/uv/mapa_$urb_id{$str_uv}.jpg");
//    list($ancho, $alto, $tipo, $atributos) = getimagesize("imagenes/uv/mapa_$urb_id.jpg");
//    echo "$ancho, $alto, $tipo, $atributos";
//$img=  imagecreatefromjpeg("mapa.jpg"); 

    $rojo = imagecolorallocate($img, 255, 0, 0); // vendido
    $verde = imagecolorallocate($img, 0, 255, 0); // disponible
    $azul = imagecolorallocate($img, 0, 0, 255); // reservado
    $gris = imagecolorallocate($img, 123, 123, 132); // bloqueado


    for ($i = 0; $i < $lotes->get_num_registros(); $i++) {
        $lote = $lotes->get_objeto();
        $color = $verde;
        if ($lote->lot_estado == 'Vendido') {
            $color = $rojo;
        } elseif ($lote->lot_estado == 'Reservado') {
            $color = $azul;
        } elseif ($lote->lot_estado == 'Bloqueado') {
            $color = $gris;
        }
        $size=$urb->urb_punto_size;
        imagefilledellipse($img, $lote->cor_lot_coorX + 4, $lote->cor_lot_coorY + 3, $size, $size , $color);
        $lotes->siguiente();
    }
    
//    imagejpeg($img, "../zmapa/cdd_mapa.jpg", 100);
    
//    $f = "../zmapa/cdd_mapa_urb_$urb_id.jpg";
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"cdd_mapa_urb_{$urb->urb_nombre}.jpg\"\n");
    imagejpeg($img, null, 100);
//    $fp = fopen($f, "r");

    fpassthru($img);
}





