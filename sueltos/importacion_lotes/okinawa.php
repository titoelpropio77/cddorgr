<?php
// exit();
require_once('mysql.php');
require_once 'excel/reader.php';
require_once('../../config/database.conf.php');
mysql_connect(_SERVIDOR_BASE_DE_DATOS,_USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or
die("Could not connect: " . mysql_error());
mysql_select_db(_BASE_DE_DATOS);

$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read("excel/okinawa.xls");
$filasTotal = $data->sheets[0]['numRows'];

echo "Total Filas = ".$filasTotal."<br>";

$urbanizacion = 13;
for ($i = 2; $i <= $filasTotal; $i++){
	list(
		$uv,
		$manzano,
		$lote,
		$superficie,
		$precio_m2,
		$zona
	) = array(
		trim($data->sheets[0]['cells'][$i][1]),
		trim($data->sheets[0]['cells'][$i][2]),
		trim($data->sheets[0]['cells'][$i][3]),
		trim($data->sheets[0]['cells'][$i][4]),
		trim($data->sheets[0]['cells'][$i][5]),
		trim($data->sheets[0]['cells'][$i][6])
	);
	
	echo $uv.' '.$manzano.' '.$lote.' '.$superficie. ' '.$precio_m2.' '.$zona.'<br /><br />';
	// continue;

	if(existe_uv($uv,$urbanizacion,$uv_id)==false)
		$uv_id = insertar_uv($uv,$urbanizacion);

	if(existe_manzano($manzano,$urbanizacion,$manzano_id)==false)
		$manzano_id = insertar_manzano($manzano,$urbanizacion);

	$zon_nombre 		= $zona;
	$zon_precio 		= $precio_m2;
	$zon_color  		= '';
	$zon_cuota_inicial 	= '30';
	$zon_moneda 		= '2';

	if(existe_zona($zon_nombre,$urbanizacion,$zona_id)==false)
		$zona_id = insertar_zona($zon_nombre,$zon_precio,$urbanizacion,$zon_color,$zon_cuota_inicial,$zon_moneda);

	$lot_nro		= $lote;
	$lot_estado		= 'Disponible';
	$lot_man_id		= $manzano_id;
	$lot_zon_id		= $zona_id;
	$lot_superficie	= $superficie;
	$lot_uv_id		= $uv_id;
	$lot_tipo		= 'Lote';

	insertar_lote($lot_nro,$lot_estado,$lot_man_id,$lot_zon_id,$lot_superficie,$lot_uv_id,$lot_tipo);
}

//-- FUNCIONES PARA UV --//
function existe_uv($nombre,$urbanizacion, &$uv_id){
    $query = new QUERY();
    $sql = "select uv_id,uv_nombre from uv where uv_nombre='" . $nombre . "' and uv_urb_id='" . $urbanizacion . "'";
    $query->consulta($sql);
    $num = $query->num_registros();
    if ($num > 0){
        list($uv_id, $uv_nombre) = $query->valores_fila();
        $uv_id = $uv_id;
		return true;
    }else{
        $uv_id = 0;
        return false;
    }
}

function insertar_uv($nombre_uv,$urbanizacion){
    $query = new QUERY();
    $sql = "insert into uv(uv_nombre,uv_urb_id) values('" . $nombre_uv . "','" . $urbanizacion . "')";
    $query->consulta($sql);
    $uv_id = mysql_insert_id();
	return $uv_id;
}

//-- FUNCIONES PARA UV --//

//-- FUNCIONES PARA MANZANO --//
function existe_manzano($nombre,$urbanizacion, &$manzano_id){
    $query = new QUERY();
    $sql = "select man_id,man_nro from manzano where man_nro='" . $nombre . "' and man_urb_id='" . $urbanizacion . "'";
    $query->consulta($sql);
    $num = $query->num_registros();
    if ($num > 0){
        list($man_id, $man_nro) = $query->valores_fila();
        $manzano_id = $man_id;
		return true;
    }else{
        $manzano_id = 0;
        return false;
    }
}

function insertar_manzano($nombre_manzano,$urbanizacion){
    $query = new QUERY();
    $sql = "insert into manzano(man_nro,man_urb_id) values('" . $nombre_manzano . "','" . $urbanizacion . "')";
    $query->consulta($sql);
    $manzano_id = mysql_insert_id();
	return $manzano_id;
}
//-- FUNCIONES PARA MANZANO --//


//-- FUNCIONES PARA ZONA --//
function existe_zona($nombre,$urbanizacion, &$zon_id){
    $query = new QUERY();
    $sql = "select zon_id,zon_nombre from zona where zon_nombre='" . trim($nombre) . "' and zon_urb_id='" . $urbanizacion . "'";
    $query->consulta($sql);
    $num = $query->num_registros();
    if ($num > 0){
        list($zon_id, $zon_nombre) = $query->valores_fila();
        $zon_id = $zon_id;
        return true;
    }else{
        $zon_id = 0;
        return false;
    }
}

function insertar_zona($zon_nombre,$zon_precio,$urbanizacion,$zon_color,$zon_cuota_inicial,$zon_moneda){
    $query = new QUERY();
    $sql = "insert into zona(zon_nombre,zon_precio,zon_urb_id,zon_color,zon_cuota_inicial,zon_moneda) values('" . $zon_nombre . "','" . $zon_precio . "','" . $urbanizacion . "',
	'" . $zon_color . "','" . $zon_cuota_inicial . "','" . $zon_moneda . "')";
    $query->consulta($sql);
    $zon_id = mysql_insert_id();
	return $zon_id;
}
//-- FUNCIONES PARA ZONA --//

function insertar_lote($lot_nro,$lot_estado,$lot_man_id,$lot_zon_id,$lot_superficie,$lot_uv_id,$lot_tipo){
    $query = new QUERY();
    $sql = "insert into lote(lot_nro,lot_estado,lot_man_id,lot_zon_id,lot_superficie,lot_uv_id,lot_tipo) values('" . $lot_nro . "','" . $lot_estado . "','" . $lot_man_id . "',
	'" . $lot_zon_id . "','" . $lot_superficie . "','" . $lot_uv_id . "','" . $lot_tipo . "')";
    $query->consulta($sql);
}
?>