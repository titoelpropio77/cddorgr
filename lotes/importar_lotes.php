<?php

require_once 'excel/reader.php';
require_once('./config2/mysql.php');
require_once './config2/constantes.php';

//mysql_connect(_SERVIDOR_BASE_DE_DATOS, _USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or die("Could not connect: " . mysql_error());
//mysql_select_db(_BASE_DE_DATOS);


$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
//$data->read("4 hermanos.xls");
$arch = 'lotes.xls';
$data->read($arch);
$filasTotal = $data->sheets[0]['numRows'];
//$urb_id = _URBANIZACION_ID;
$urb_id = 1;
//echo $filasTotal;

for ($i = 2; $i <= $filasTotal; $i++) {
    list(
            $uv,
            $manzano,
            $lote,
            $sus,
            $superficie,
            $zona            
            ) = array(
        $data->sheets[0]['cells'][$i][1],
        $data->sheets[0]['cells'][$i][2],
        $data->sheets[0]['cells'][$i][3],
        $data->sheets[0]['cells'][$i][4],
        $data->sheets[0]['cells'][$i][5],
        $data->sheets[0]['cells'][$i][6],
    );

    insertar_lote(limpiar($lote), 'Disponible', limpiar($manzano), $zona, limpiar($superficie), limpiar($uv), 'Lote', '0', $urb_id, $sus);

    echo $uv . "/" . $manzano . "/" . $lote . "/" . $superficie . "/" . $zona;
}

function limpiar($cadena) {
    return preg_replace("[\s+]", '', $cadena);
}

function existe_uv($desc,$urb = '') {
    $sql = "select uv_id from uv where uv_nombre='" . $desc . "' and uv_urb_id='$urb'";
    $q = new QUERY();
    $q->consulta($sql);
    $num = $q->num_registros();
    $id = -1;
    if ($num > 0) {
        $obj = $q->objeto();
        $id = $obj->uv_id;
    }
    return $id;
}

function existe_zona($desc, $urb_id = '') {
    $sql = "select zon_id from zona where zon_nombre='" . $desc . "' and zon_urb_id='$urb_id'";
    $q = new QUERY();
    $q->consulta($sql);
    $num = $q->num_registros();
    $id = -1;
    if ($num > 0) {
        $obj = $q->objeto();
        $id = $obj->zon_id;
    }
    return $id;
}

function existe_manzano($desc, $urb = '') {
    $sql = "select man_id from manzano where man_nro='" . $desc . "' and man_urb_id='$urb'";
    $q = new QUERY();
    $q->consulta($sql);
    $num = $q->num_registros();
    $id = -1;
    if ($num > 0) {
        $obj = $q->objeto();
        $id = $obj->man_id;
    }
    return $id;
}

function insertar_uv($uv_nombre, $uv_urb_id) {
    $sql = "insert into uv(uv_nombre,uv_urb_id)values('" . $uv_nombre . "','" . $uv_urb_id . "')";
    $q = new QUERY();
    $q->consulta($sql);
    return mysql_insert_id();
}

function insertar_manzano($man_nro, $man_urb_id) {
    $sql = "insert into manzano(man_nro,man_urb_id)values('" . $man_nro . "','" . $man_urb_id . "')";
    $q = new QUERY();
    $q->consulta($sql);
    return mysql_insert_id();
}

function insertar_zona($zon_nombre, $sus, $zon_urb_id) {
    $sql = "insert into zona(zon_nombre,zon_urb_id,zon_precio,zon_moneda)values('$zon_nombre','$zon_urb_id','$sus','2')";
    $q = new QUERY();
    $q->consulta($sql);
    return mysql_insert_id();
}

function insertar_lote($lot_nro, $lot_estado, $manzano, $zona, $lot_superficie, $uv, $lot_tipo, $lot_sup_vivienda, $urb_id, $sus) {
    $zon_id = existe_zona($zona, $urb_id);
    $man_id = existe_manzano($manzano, $urb_id);
    $uv_id = existe_uv($uv, $urb_id);

    if ($man_id == -1) {
        $man_id = insertar_manzano($manzano, $urb_id);
    }

    if ($zon_id == -1) {
        $zon_id = insertar_zona($zona, $sus, $urb_id);
    }

    if ($uv_id == -1) {
        $uv_id = insertar_uv($uv, $urb_id);
    }
    $sql = "insert into lote(lot_nro,
                                lot_estado,
                                lot_man_id,
                                lot_superficie,
                                lot_zon_id,
                                lot_uv_id,
                                lot_tipo,
                                lot_sup_vivienda)
            values('" .
            $lot_nro . "','" .
            $lot_estado . "','" .
            $man_id . "','" .
            $lot_superficie . "','" .
            $zon_id . "','" .
            $uv_id . "','" .
            $lot_tipo . "','" .
            $lot_sup_vivienda . "')";
    $q = new QUERY();
    $q->consulta($sql);
}

?>