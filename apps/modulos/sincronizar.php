<?php

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) {
        case "todo":
            todo($mensaje, $accion, $datos, $opciones);
            break;
    }
}

function todo(&$mensaje, &$accion, &$datos, &$opciones) 
{
    $suscriptor_id=$_GET['sus_id'];
	//$convertir = new convertir();
	$opcionesDatos = array(
		/*
		"configuracion" => configuracion(),
		"categorias" => categorias(),
		"departamentos" => departamentos(),
		
		"directorio" => directorio(), 
		"directorio_categorias" => directorio_categorias(),
		"directorio_marcas" => directorio_marcas(),
		"directorio_fotos" => directorio_fotos(),
		"directorio_sucursales" => directorio_sucursales(),
		"marcas" => marcas(),
		"suscriptor_vehiculo" => suscriptor_vehiculo($suscriptor_id),
		"suscriptor_servicio" => suscriptor_servicio($suscriptor_id),
		"directorio_calificacion" => directorio_calificacion($suscriptor_id),
		"publicidad_foto" => publicidad_foto(),
		"suscriptor_favorito" => suscriptor_favorito($suscriptor_id)
			*/
	);
	
	$datos = json_encode($opcionesDatos);
	//$datos = json_encode($opcionesDatos,  JSON_UNESCAPED_UNICODE);
	$mensaje = "";
	$accion = "correcto";
}

function suscriptor_favorito($suscriptor_id) 
{	$arr = array();
	if($suscriptor_id!='')
	{
		$sql = "SELECT * FROM suscriptor_favorito WHERE susfav_sus_id='$suscriptor_id'"; 
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		
		if ($num > 0) {
			for ($i = 0; $i < $num; $i++)
			{
				$objeto = mysql_fetch_object($result);
				$o = new stdClass; 
				$o->i = intval($objeto->susfav_id);
				$o->d = intval($objeto->susfav_dir_id);
				$o->s = intval($objeto->susfav_sus_id);
				array_push($arr, $o);
			}
		}
	}
	return $arr;
}

function publicidad_foto() {
	
	$arr = array();
	
	$sql = "SELECT * FROM publicidad_foto
	INNER JOIN publicidad on (pfo_pub_id=pub_id)
	WHERE pub_estado='1'"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	
	if ($num > 0) {
		for ($i = 0; $i < $num; $i++)
		{
			$objeto = mysql_fetch_object($result);
			$o = new stdClass; 
			$o->i = intval($objeto->pfo_id);
			$o->t = utf8_encode($objeto->pfo_titulo);
			$o->u = utf8_encode($objeto->pfo_url);
			$o->o = intval($objeto->pfo_orden);
			$o->a = utf8_encode($objeto->pfo_archivo);
			$o->p = $objeto->pfo_pub_id;
			array_push($arr, $o);
		}
	}
	
	return $arr;
}

function directorio_calificacion($suscriptor_id) { 
	
	$arr = array();
	if($suscriptor_id!='')
	{
		$sql = "SELECT * FROM directorio_calificacion WHERE dircalif_sus_id='$suscriptor_id'"; 
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		
		if ($num > 0) {
			for ($i = 0; $i < $num; $i++)
			{
				$objeto = mysql_fetch_object($result);
				$o = new stdClass; 
				$o->i = intval($objeto->dircalif_id);
				$o->d = intval($objeto->dircalif_direc_id);
				$o->s = intval($objeto->dircalif_sus_id);
				$o->v = $objeto->dircalif_valor;
				array_push($arr, $o);
			}
		}
	}
	return $arr;
}

function suscriptor_servicio($suscriptor_id) 
{	$arr = array();
	if($suscriptor_id!='')
	{
		$sql = "SELECT * FROM suscriptor_servicio WHERE susserv_sus_id='$suscriptor_id'"; 
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		
		if ($num > 0) {
			for ($i = 0; $i < $num; $i++)
			{
				$objeto = mysql_fetch_object($result);
				$o = new stdClass; 
				$o->i = intval($objeto->susserv_id);
				$o->t = utf8_encode($objeto->susserv_titulo);
				$o->v = intval($objeto->susserv_veh_id);
				$o->c = utf8_encode($objeto->susserv_costo);
				$o->f = $objeto->susserv_fecha;
				$o->k = intval($objeto->susserv_kilometraje);
				$o->d = intval($objeto->susserv_dir_id); 
				$o->n = utf8_encode($objeto->susserv_nota);
				$o->s = intval($objeto->susserv_sus_id);
				array_push($arr, $o);
			}
		}
	}
	return $arr;
}

function suscriptor_vehiculo($suscriptor_id) {
	$arr = array();
	if($suscriptor_id!='')
	{
		$sql = "SELECT * FROM suscriptor_vehiculo WHERE veh_sus_id='$suscriptor_id'"; 
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		
		if ($num > 0) {
			for ($i = 0; $i < $num; $i++)
			{
				$objeto = mysql_fetch_object($result);
				$o = new stdClass; 
				$o->i = utf8_encode($objeto->veh_id);
				$o->n = utf8_encode($objeto->veh_nombre);
				$o->m = utf8_encode($objeto->veh_mar_id);
				$o->s = utf8_encode($objeto->veh_sus_id);
				$o->p = utf8_encode($objeto->veh_nroplaca);
				$o->a = utf8_encode($objeto->veh_anio);
				$o->d = utf8_encode($objeto->veh_descripcion);
				
				array_push($arr, $o);
			}
		}
	}
	return $arr;
} 

function directorio_sucursales() {
	$sql = "SELECT rsu_nombre,rsu_descripcion,rsu_direccion,rsu_telefono,rsu_correo,rsu_horario,rsu_cuidad,rsu_latitud,rsu_longitud,rsu_orden,rsu_dir_id FROM directorio_sucursales INNER JOIN directorio ON (rsu_dir_id=dir_id) WHERE dir_estado='Habilitado'"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	$arr = array();
	if ($num > 0) {
		for ($i = 0; $i < $num; $i++)
		{
			$objeto = mysql_fetch_object($result);
			$o = new stdClass; 
			$o->nom = utf8_encode($objeto->rsu_nombre);
			$o->des = utf8_encode($objeto->rsu_descripcion);
			$o->dir = utf8_encode($objeto->rsu_direccion);
			$o->tel = utf8_encode($objeto->rsu_telefono);
			$o->cor = utf8_encode($objeto->rsu_correo);
			$o->hor = utf8_encode($objeto->rsu_horario);
			$o->ciu = utf8_encode($objeto->rsu_cuidad); 
			$o->lat = $objeto->rsu_latitud;
			$o->lon = $objeto->rsu_longitud;
			$o->ord = $objeto->rsu_orden;
			$o->d = $objeto->rsu_dir_id; 
			array_push($arr, $o);
		}
	}
	return $arr;
}

function directorio_fotos() {
	$sql = "SELECT dfo_titulo,dfo_dir_id,dfo_archivo,dfo_orden FROM directorio_foto INNER JOIN directorio ON (dfo_dir_id=dir_id) WHERE dir_estado='Habilitado'"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	
	$arr = array();
	if ($num > 0) { 
		for ($i = 0; $i < $num; $i++)
		{
			$objeto = mysql_fetch_object($result);
			$o = new stdClass; 
			$o->t = utf8_encode($objeto->dfo_titulo);
			$o->d = $objeto->dfo_dir_id;
			$o->a = $objeto->dfo_archivo;
			$o->o = $objeto->dfo_orden; 
			array_push($arr, $o);
		}
	}
	return $arr;
}

function directorio_marcas() {
	$sql = "SELECT dcr_dir_id,dcr_mar_id FROM directorio_marca_relacion INNER JOIN directorio ON (dcr_dir_id=dir_id) WHERE dir_estado='Habilitado'"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	
	$arr = array();
	if ($num > 0) {
		for ($i = 0; $i < $num; $i++)
		{
			$objeto = mysql_fetch_object($result);
			$o = new stdClass; 
			$o->d = $objeto->dcr_dir_id;
			$o->m = $objeto->dcr_mar_id;
			array_push($arr, $o);
		}
	}
	return $arr;
}

function directorio_categorias() {
	$sql = "SELECT dcr_dir_id,dcr_cat_id FROM directorio_categorias_relacion INNER JOIN directorio ON (dcr_dir_id=dir_id) WHERE dir_estado='Habilitado'"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	
	$arr = array();
	if ($num > 0) {
		for ($i = 0; $i < $num; $i++)
		{
			$objeto = mysql_fetch_object($result);
			$o = new stdClass; 
			$o->d = $objeto->dcr_dir_id;
			$o->c = $objeto->dcr_cat_id; 
			array_push($arr, $o);
		}
	}
	return $arr;
}

function directorio(){ 
	$sql = "SELECT * FROM directorio WHERE dir_estado='Habilitado' ORDER BY dir_orden DESC"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	
	$arr = array();
	if ($num > 0) {
		for ($i = 0; $i < $num; $i++)
		{
			$objeto = mysql_fetch_object($result);
			$o = new stdClass; 
			$o->i = $objeto->dir_id;
			$o->n = utf8_encode($objeto->dir_nombre);
			$o->d = utf8_encode($objeto->dir_descripccion);
			$o->im = $objeto->dir_imagen;	
			$o->w = utf8_encode($objeto->dir_web);
			$o->f = utf8_encode($objeto->dir_facebook);
			$o->o = $objeto->dir_orden;
			$o->c = promedio_puntaje_directorio($objeto->dir_id);	//Promerio de calificacion 
			$o->ct = cantidad_usuarios_calificaron($objeto->dir_id);	//Total usuarios que calificaron 
			$o->cm = categoria_marker($objeto->dir_id);
			array_push($arr, $o); 
		}
	}
	return $arr;
}

function categoria_marker($dir_id)
{
	$resul ="";
	
	$sql = "
	SELECT * FROM directorio_categorias_relacion 
	INNER JOIN categoria_multiple ON (dcr_cat_id = cat_id) 
	WHERE dcr_dir_id='".$dir_id."' ORDER BY dcr_cat_id DESC LIMIT 0,1;";
	
	$result = mysql_query($sql);

	$num = mysql_num_rows($result); 
	if ($num > 0) 
	{
		$objeto = mysql_fetch_object($result);
		
		$cat_padre = $objeto->cat_padre;
		$sql_imagen = "
		SELECT cat_imagen FROM categoria_multiple 
		WHERE cat_id='".$cat_padre."'";
		
		$result_imagen = mysql_query($sql_imagen);
		$num_imagen = mysql_num_rows($result_imagen);
		if($num_imagen>0)
		{
			$objeto_imagen = mysql_fetch_object($result_imagen);
			$cat_imagen = $objeto_imagen->cat_imagen;
			if($cat_imagen!='')
				$resul = $cat_imagen;
			else
				$resul='marker.png';
		}
		else
			$resul='marker.png';
	}
	else
		$resul='marker.png';
	
	return $resul;
}

function configuracion(){
	$arr = array();
	$o = new stdClass;
	$o->f = date("Y-m-d"); 
	$o->h = date("h:i:s"); 
	array_push($arr, $o);
	return $arr;
}

function marcas(){
	$sql = "SELECT * FROM marcas ORDER BY mar_orden DESC"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	
	$arr = array();
	if ($num > 0) {
		for ($i = 0; $i < $num; $i++)
		{
			$objeto = mysql_fetch_object($result);
			$o = new stdClass;
			$o->i = $objeto->mar_id; 
			$o->n = utf8_encode($objeto->mar_nombre);
			$o->o = $objeto->mar_orden; 
			array_push($arr, $o);
		}
	}
	return $arr;
}

function departamentos(){
	$sql = "SELECT * FROM departamentos ORDER BY dep_orden DESC"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	
	$arr = array();
	if ($num > 0) {
		for ($i = 0; $i < $num; $i++)
		{
			$objeto = mysql_fetch_object($result);
			$o = new stdClass;
			$o->i = $objeto->dep_id; 
			$o->n = utf8_encode($objeto->dep_nombre);
			$o->o = $objeto->dep_orden; 
			array_push($arr, $o);
		}
	}
	return $arr;
}

function categorias(){
	$sql = "SELECT * FROM categoria_multiple WHERE cat_estado='1' ORDER BY cat_orden DESC"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	
	$arr = array();
	if ($num > 0) {
		for ($i = 0; $i < $num; $i++)
		{
			$objeto = mysql_fetch_object($result);
			$o = new stdClass;
			$o->i = $objeto->cat_id; 
			$o->n = utf8_encode($objeto->cat_nombre);
			$o->o = $objeto->cat_orden; 
			$o->p = $objeto->cat_padre; 
			
			$auxImg = "sin_foto.gif";
			if($objeto->cat_imagen !="") { 
				$auxImg = $objeto->cat_imagen;
			} 
			$o->img = $auxImg; 
			array_push($arr, $o);
		}
	}
	return $arr;
}


// --- ---- CALIFICACION DE DIRECTORIOS -- -- -- //
function promedio_puntaje_directorio($directorio){
	$sql = "SELECT * FROM directorio_calificacion WHERE dircalif_direc_id='$directorio'"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	$promedio =0;
	$suma=0;
	
	if ($num > 0) 
	{
		for ($i = 0; $i < $num; $i++)
		{
			$objeto = mysql_fetch_object($result);
			$suma+=$objeto->dircalif_valor;
		}
		if($suma>0)
		{
			$promedio = ($suma/$num);
		}
	}
	return $promedio;
}

function cantidad_usuarios_calificaron($directorio){
	$sql = "SELECT * FROM directorio_calificacion WHERE dircalif_direc_id='$directorio'"; 
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	return $num;
}
// --- ---- CALIFICACION DE DIRECTORIOS -- -- -- //

$json = '{
	"respuesta":
	{
		"mensaje": "' . $mensaje . '",
		"accion": "' . $accion . '",
		"datos":' . $datos . ',
		"opciones":' . $opciones . '
	}
}';
echo 'iqCallback([' . $json . '])';

?> 