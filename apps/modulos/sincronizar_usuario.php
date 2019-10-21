<?php
/* 
$_GET['token']
Array
(
    [0] => admin //Usuario
    [1] => Administradores //Grupo
    [2] => 1 //int_id
)
*/

require_once("cliente.class.php");
require_once("seguimiento.class.php");
require_once("seguimientoAccion.class.php");
include_once("proforma.class.php"); 
include_once("reserva.class.php");
include_once("comiciones.class.php");
include_once("usuario.class.php"); 

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

//Variales Temporales page
//$_SESSION["pageTempId"];
//$_SESSION["pageModulo"];

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) {
        case "todo":

            // Recibir datos de POST sincronizacion para guardar
            if (isset($_POST)) {
                $datosForm = preg_replace('/\\\\\"/', "\"", $_POST['datosForm']);
                $jsonDatos = json_decode($datosForm);
				
				// Temporal post
				$_SESSION["pageTempId"] = $jsonDatos->pageTempId;
				$_SESSION["pageModulo"] = $jsonDatos->pageModulo;
				
                // Sincronizar cliente
                if (isset($jsonDatos->clientes)) {
                    $Cliente = new CLIENTE();
                    $Cliente->sincronizar($jsonDatos->clientes);
                }
				
				// Sincronizar seguimiento
                if (isset($jsonDatos->seguimiento)) {
                    $Seguimiento = new SEGUIMIENTO();
                    $Seguimiento->sincronizar($jsonDatos->seguimiento);
                }
				
				// Sincronizar seguimiento
                if (isset($jsonDatos->accion)) {
                    $Accion = new ACCION();
                    $Accion->sincronizar($jsonDatos->accion);
                }
				
				// Sincronizar proforma
                if (isset($jsonDatos->proforma)) { 
                    $Proforma = new PROFORMA();
                    $Proforma->sincronizar($jsonDatos->proforma); 
                }
				
				// Sincronizar reserva
                if (isset($jsonDatos->reserva)) { 
                    $Reserva = new RESERVA();
                    $Reserva->sincronizar($jsonDatos->reserva); 
                }
				// Sincronizar cambiar contraseña
				if (isset($jsonDatos->usuario)) { 
                    $Usuario= new USUARIO();
                    $Usuario->cambiar_contrasenia($jsonDatos->usuario[0]);
                }
            }
			
			// Sincronizar todos
            todo($mensaje, $accion, $datos, $opciones);
			
            break;
    }
}

function todo(&$mensaje, &$accion, &$datos, &$opciones) {
    $Encript = new Encryption();
    $usuDatos = explode("|", $Encript->decode($_GET['token']));

    $int_usu_id = $usuDatos[2];

    $opcionesDatos = array();
	$opcionesRespuesta = array();
	
	// Verificar que tablas y modulos va sincronizar
    if ($_GET['tablas'] != "") {
        $tablasArray = explode("|", $_GET['tablas']);
        for ($i = 0; $i < count($tablasArray); $i++) {
            // cliente
            if ($tablasArray[$i] == "cliente") {
                $opcionesDatos['cliente'] = cliente($int_usu_id, $usuDatos);
            }
            // seguimiento
            if ($tablasArray[$i] == "seguimiento") {
                $opcionesDatos['seguimiento'] = seguimiento($int_usu_id, $usuDatos);
				$opcionesRespuesta['page'] = _seguimiento_tempid($_SESSION["pageTempId"]);
            } 
			// accion
            if ($tablasArray[$i] == "accion") {
                $opcionesDatos['accion'] = accion($int_usu_id, $usuDatos);
				$opcionesRespuesta['page'] = _seguimiento_tempid($_SESSION["pageTempId"]);
            }
			// proforma
            if ($tablasArray[$i] == "proforma") {
                $opcionesDatos['proforma'] = proforma($int_usu_id, $usuDatos);
				$opcionesRespuesta['page'] = _proforma_tempid($_SESSION["pageTempId"]);
            }
			// reserva
			if ($tablasArray[$i] == "reserva") {
                $opcionesDatos['reserva'] = reserva($int_usu_id, $usuDatos);
				$opcionesRespuesta['page'] = _reserva_tempid($_SESSION["pageTempId"]);
            }
			//usuario
			if ($tablasArray[$i] == "usuario") {
				$opcionesDatos['usuario'] = usuario($int_usu_id, $usuDatos);
			}
        }
    } else {
        $opcionesDatos['cliente'] = cliente($int_usu_id, $usuDatos);
        $opcionesDatos['seguimiento'] = seguimiento($int_usu_id, $usuDatos);
		$opcionesDatos['accion'] = accion($int_usu_id, $usuDatos);
		$opcionesDatos['proforma'] = proforma($int_usu_id, $usuDatos);
		$opcionesDatos['reserva'] = reserva($int_usu_id, $usuDatos); 
		
		//Sincronizacion por defecto 
		$opcionesDatos['urbanizacion'] = urbanizacion($int_usu_id, $usuDatos);
		$opcionesDatos['uv'] = uv($int_usu_id, $usuDatos);
		$opcionesDatos['manzano'] = manzano($int_usu_id, $usuDatos);
		$opcionesDatos['lote'] = lote($int_usu_id, $usuDatos);
		$opcionesDatos['vendedor'] = vendedor($int_usu_id, $usuDatos);
		$opcionesDatos['parametro'] = parametro($int_usu_id, $usuDatos);
		
		//Cominiones
		$Comiciones = new COMICIONES();
		$opcionesDatos['comiciones'] = $Comiciones->todos();
		
		//Usuario Contraseña
		$opcionesDatos['usuario'] = usuario($int_usu_id, $usuDatos);
    }
	
	$_SESSION["pageTempId"] = "";
    $datos = json_encode($opcionesDatos);
	$opciones = json_encode($opcionesRespuesta);
    $mensaje = "";
    $accion = "correcto";
}

function parametro($int_usu_id, $usuDatos) { 
    $arr = array();
    if ($int_usu_id != '') { 
        $sql = "select par_pro_ci,par_pro_descuento1,par_pro_descuento2,par_pro_descuento3,par_pro_descuento4,par_pro_descuento5 from ad_parametro ;";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;
				$o->ci = $objeto->par_pro_ci;
				$o->d1 = $objeto->par_pro_descuento1;
				$o->d2 = $objeto->par_pro_descuento2;
				$o->d3 = $objeto->par_pro_descuento3;
				$o->d4 = $objeto->par_pro_descuento4;
				$o->d5 = $objeto->par_pro_descuento5;
                array_push($arr, $o);
            }
        }
    }
    return $arr;
}
function usuario($int_usu_id, $usuDatos) { 
    $arr = array();
    if ($int_usu_id != '') { 
        $sql = "select usu_password from ad_usuario where usu_id='".$usuDatos[0] ."';"; 
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;				
				$o->p = $objeto->usu_password;
                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

function reserva($int_usu_id, $usuDatos) { 
    $arr = array();
    if ($int_usu_id != '') { 
        $sql = "select * from reserva_terreno where res_usu_id='".$usuDatos[0] ."';";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;				
				$o->i = $objeto->res_id;
				$o->ii = $objeto->res_int_id;
				$o->vi = $objeto->res_vdo_id;
				$o->li = $objeto->res_lot_id;
				$o->f = $objeto->res_fecha;
				$o->h = $objeto->res_hora;
				$o->e = $objeto->res_estado;
				$o->ui = $objeto->res_usu_id;
				$o->pf = $objeto->res_plazo_fecha;
				$o->ph = $objeto->res_plazo_hora;
				$o->n = $objeto->res_nota;
				$o->urb = $objeto->res_urb_id;
				$o->m = $objeto->res_moneda;
				$o->mr = $objeto->res_monto_referencial;
				$o->m2 = $objeto->res_monto_m2;

                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

function vendedor($int_usu_id, $usuDatos) { 
    $arr = array();
    if ($int_usu_id != '') { 
		//$sql = "select vdo_id,vdo_int_id,vdo_grupo from vendedor where vdo_estado='Habilitado';";
		$sql = "select vdo_id,vdo_int_id, vdo_vgru_id from ad_usuario 
													  inner join interno on (usu_per_id=int_id)
													  inner join vendedor on (vdo_int_id=int_id)
													  where vdo_estado='Habilitado' order by vdo_id asc"; 
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass; 
                $o->i = $objeto->vdo_id;
                $o->ii = $objeto->vdo_int_id;
				//$o->g = utf8_encode($objeto->vdo_grupo);
				$o->g = utf8_encode($objeto->vdo_vgru_id);
                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

function lote($int_usu_id, $usuDatos) {
    $arr = array();
    if ($int_usu_id != '') {
	
		//=== uv_zona_precio === //
        // $sql = "SELECT lot_id,lot_nro,lot_estado,lot_man_id,lot_superficie,lot_uv_id,lot_tipo, zon_precio, (lot_superficie*zon_precio) as precio,zon_moneda FROM lote 
		// inner join zona on(lot_zon_id=zon_id)
		// inner join uv_zona_precio on (lot_uv_id=uvz_uv_id and lot_zon_id=uvz_zon_id)";
		
		//=== zona ===// 
		$sql = "SELECT lot_id,lot_nro,lot_estado,lot_man_id,lot_superficie,lot_uv_id,lot_tipo, zon_precio, (lot_superficie*zon_precio) as precio,zon_moneda FROM lote 
		inner join zona on(lot_zon_id=zon_id)";
		
        $result = mysql_query($sql);
        $num = mysql_num_rows($result); 
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;
                $o->i = $objeto->lot_id;
                $o->n = $objeto->lot_nro;
				$o->e = $objeto->lot_estado;
				$o->m = $objeto->lot_man_id;
                $o->s = $objeto->lot_superficie;
				$o->u = $objeto->lot_uv_id;
				$o->t = $objeto->lot_tipo;
				//$o->pc = $objeto->uvz_precio_cont;
				$o->pc = $objeto->zon_precio; 
				$o->p = $objeto->precio;
				$o->z = $objeto->zon_moneda;
                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

function manzano($int_usu_id, $usuDatos) { 
    $arr = array();
    if ($int_usu_id != '') { 
        $sql = "SELECT * FROM manzano order by man_nro asc";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;
                $o->i = $objeto->man_id;
                $o->n = utf8_encode($objeto->man_nro);
				$o->u = $objeto->man_urb_id;
                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

function uv($int_usu_id, $usuDatos) { 
    $arr = array();
    if ($int_usu_id != '') { 
        $sql = "SELECT * FROM uv order by uv_nombre asc";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;
                $o->i = $objeto->uv_id;
                $o->n = utf8_encode($objeto->uv_nombre);
				$o->u = $objeto->uv_urb_id;
                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

function urbanizacion($int_usu_id, $usuDatos) { 
    $arr = array();
    if ($int_usu_id != '') {  
        $sql = "SELECT urb_id,urb_nombre,urb_maxplazo,urb_interes_anual,urb_val_form FROM urbanizacion where urb_ventas_internas='Si' order by urb_nombre asc";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;
                $o->i = $objeto->urb_id;
                $o->n = utf8_encode($objeto->urb_nombre);
				$o->m = $objeto->urb_maxplazo;
				$o->ia = $objeto->urb_interes_anual."|".$objeto->urb_val_form;
                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

function proforma($int_usu_id, $usuDatos) {
    $arr = array();
    if ($int_usu_id != '') { 
        $sql = "SELECT * FROM proforma WHERE pro_usu_id='" . $usuDatos[0] . "' order by pro_id desc";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;
                $o->i = $objeto->pro_id;
                $o->ui = $objeto->pro_usu_id;
                $o->ii = $objeto->pro_int_id;
                $o->f = $objeto->pro_fecha;
                $o->h = $objeto->pro_hora;
                $o->ia = $objeto->pro_lote_precio;
                $o->ti = $objeto->pro_app_tempid;
				$o->ah = $objeto->pro_parametros;
				$o->li = $objeto->pro_lot_id;
				$o->urb = $objeto->pro_urb_id;
				$o->u = $objeto->pro_uv;
				$o->ci = $objeto->pro_ci;
				$o->m = $objeto->pro_manzano;
				$o->pia = $objeto->pro_urb_interes_anual;  
                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

function accion($int_usu_id, $usuDatos) {
    $arr = array();
    if ($int_usu_id != '') { 
        $sql = "SELECT * FROM seguimiento_accion WHERE sac_usu_id='" . $usuDatos[0] . "' order by sac_id desc"; 
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;
                $o->i = $objeto->sac_id;
                $o->ui = $objeto->sac_usu_id;
                $o->ii = $objeto->sac_int_id;
                $o->f = $objeto->sac_fecha;
                $o->h = $objeto->sac_hora;
                $o->a = utf8_encode($objeto->sac_accion);
                $o->si = $objeto->sac_seg_id;
				$o->e = utf8_encode($objeto->sac_estado);
				$o->al = $objeto->sac_alerta;
                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

function seguimiento($int_usu_id, $usuDatos) {
    $arr = array();
    if ($int_usu_id != '') {
        $sql = "SELECT * FROM seguimiento WHERE seg_usu_id='" . $usuDatos[0] . "' order by seg_id desc"; 
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;
                $o->i = $objeto->seg_id;
                $o->ui = $objeto->seg_usu_id;
                $o->ii = $objeto->seg_int_id;
                $o->f = $objeto->seg_fecha;
                $o->h = $objeto->seg_hora;
                $o->tc = utf8_encode($objeto->seg_tipo_contacto);
                $o->s = utf8_encode($objeto->seg_situacion);
                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

function cliente($int_usu_id, $usuDatos) {
    $arr = array();
    if ($int_usu_id != '') { 
        //$sql = "SELECT int_id, int_nombre,int_apellido,int_email,int_telefono,int_celular,int_fecha_nacimiento,int_direccion,int_usu_id,int_ci,int_estado_civil,int_ci_exp FROM interno  order by int_id desc"; //WHERE int_usu_id='".$usuDatos[0]."'
        $sql = "SELECT int_id, int_nombre,int_apellido,int_email,int_telefono,int_celular,int_fecha_nacimiento,int_direccion,int_usu_id,int_ci,int_estado_civil,int_ci_exp FROM interno WHERE int_usu_id='".$usuDatos[0]."'  or int_id='".$usuDatos[2]."' order by int_id desc LIMIT 0, 2000";
		$result = mysql_query($sql); 
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $o = new stdClass;
                $o->i = $objeto->int_id;
                $o->n = utf8_encode($objeto->int_nombre);
                $o->a = utf8_encode($objeto->int_apellido);
                $o->e = utf8_encode($objeto->int_email);
                $o->t = utf8_encode($objeto->int_telefono);
                $o->c = utf8_encode($objeto->int_celular);
                $o->f = $objeto->int_fecha_nacimiento;
                $o->d = utf8_encode($objeto->int_direccion);
                $o->in = $objeto->int_usu_id;

                $o->ci = $objeto->int_ci;
                $o->ec = $objeto->int_estado_civil;
                $o->ex = $objeto->int_ci_exp;

                if ($usuDatos[0] == $objeto->int_usu_id) {
                    $o->cr = "miclientes";
                } else {
                    $o->cr = "";
                }

                array_push($arr, $o);
            }
        }
    }
    return $arr;
}

$json = '{
	"respuesta":
	{
		"mensaje": "' . $mensaje . '",
		"accion": "' . $accion . '",
		"datos":' . $datos . ',
		"opciones":' . $opciones . '
	}
}';

// Responder json
if (isset($_POST)) {
    echo '[' . $json . ']';
} else {
    echo 'iqCallback([' . $json . '])';
}
?>
