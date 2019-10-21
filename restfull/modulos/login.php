<?php

$mensaje = "Sin datos";
$accion = "error";
$datos = '""';
$opciones = '""';

if (isset($_GET["tarea"])) {
    switch ($_GET['tarea']) {
        case "login": {
                login($mensaje, $accion, $datos, $opciones);
                break;
            }
        case "rango": {
                rango($mensaje, $accion, $datos, $opciones, $_GET['usu_id'], $_GET['vdo_id']);
                break;
            }
        case "leer_notificacion": {
                leer_notificacion($mensaje, $accion, $datos, $opciones, $_GET['not_id']);
                break;
            }
    }
}

function rango(&$mensaje, &$accion, &$datos, &$opciones, $usu_id, $vdo_id) {
    // $sql="SELECT ran_nombre
    // FROM ad_usuario 
    // INNER JOIN interno ON(int_id=usu_per_id) 
    // INNER JOIN vendedor ON(vdo_int_id=usu_per_id)
    // left join vendedor_rango on(vran_vdo_id=vdo_id)
    // left join rango on(ran_id=vran_ran_id)
    // WHERE usu_id='".$usu_id."'                         
    // AND usu_estado='1' 
    // AND vran_eliminado = 'No'
    // ORDER BY ran_id DESC limit 0,1";

    $sql = "SELECT ran_nombre,int_foto
                    FROM ad_usuario 
                    INNER JOIN interno ON(int_id=usu_per_id) 
                    INNER JOIN vendedor ON(vdo_int_id=usu_per_id)					
					left join rango on(ran_id=vdo_rango_alcanzado)
                    WHERE usu_id='" . $usu_id . "'                         
                            AND usu_estado='1'                             
                            ORDER BY ran_id DESC limit 0,1";
    $result = mysql_query($sql);
    $num = mysql_num_rows($result);
    if ($num > 0) {

        $objeto = mysql_fetch_object($result);
        $o = new stdClass;
        $o->r = utf8_encode($objeto->ran_nombre);
        $o->notificaciones = notificaciones($vdo_id);
        $o->foto = utf8_encode($objeto->int_foto);
        $datos = json_encode($o);
        $accion = "correcto";
        $mensaje = "";
    }
}

function notificaciones($vdo_id) {
    $sql = "select * from vendedor_notificacion where vdonot_vdo_id='$vdo_id'
			and vdonot_estado='Pendiente' order by vdonot_fecha desc,vdonot_hora desc";
    $result = mysql_query($sql);
    $num = mysql_num_rows($result);
    $mensajes = array();
    for ($i = 0; $i < $num; $i++) {
        $objeto = mysql_fetch_object($result);
        $o = new stdClass;
        $o->id_not = $objeto->vdonot_id;
        $o->mensaje = $objeto->vdonot_mensaje;
        $o->momento = $objeto->vdonot_fecha . " - " . $objeto->vdonot_hora;
        $mensajes[] = $o;
    }

    return $mensajes;
}

function leer_notificacion(&$mensaje, &$accion, &$datos, &$opciones, $not_id) {
    $sql_update = "update vendedor_notificacion set vdonot_estado='Leido' where vdonot_id='$not_id'";
    $result = mysql_query($sql_update);
    $accion = "correcto";
}

function login(&$mensaje, &$accion, &$datos, &$opciones) {
    $usu_id = trim($_POST['usu_id']);
    $usu_password = MD5(trim($_POST['usu_password']));
    if ((isset($usu_id)) && (isset($usu_password))) {
    
        // $sql="SELECT usu_id,usu_gru_id,
        // usu_per_id,int_nombre,
        // int_apellido,usu_password,vdo_id,ran_nombre
        // FROM ad_usuario 
        // INNER JOIN interno ON(int_id=usu_per_id) 
        // INNER JOIN vendedor ON(vdo_int_id=usu_per_id)
        // left join vendedor_rango on(vran_vdo_id=vdo_id)
        // left join rango on(ran_id=vran_ran_id)
        // WHERE usu_id='".$usu_id."' 
        // AND usu_password='".$usu_password."' 
        // AND usu_estado='1' ORDER BY ran_id DESC limit 0,1";

        $sql = "SELECT usu_id,usu_gru_id,
                        usu_per_id,int_nombre,
                        int_apellido,int_foto,usu_password,vdo_id
                    FROM ad_usuario 
                    INNER JOIN interno ON(int_id=usu_per_id) 
                    LEFT JOIN vendedor ON(vdo_int_id=usu_per_id)					
                    WHERE (usu_id='" . $usu_id . "' 
                    AND usu_password='" . $usu_password . "' 
                    AND vdo_estado='Habilitado' AND usu_estado='1' 
                    AND vdo_vgru_id=14 AND vdo_venta_inicial > 0)                     
                    OR (usu_gru_id='Administradores'
                    AND usu_id='" . $usu_id . "' 
                    AND usu_password='" . $usu_password . "' AND usu_estado='1')
                    limit 0,1";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            //usu_per_id
            $Encript = new Encryption();
            $objeto = mysql_fetch_object($result);

            $o = new stdClass;
            $o->i = $objeto->usu_id;
            $o->g = $objeto->usu_gru_id;
            $o->p = $objeto->usu_per_id;
            //$o->c = $objeto->usu_password;

            $o->f = utf8_encode($objeto->int_foto);
            $o->n = utf8_encode($objeto->int_nombre);
            $o->a = utf8_encode($objeto->int_apellido);
            $o->r = utf8_encode($objeto->ran_nombre);
            $o->v = utf8_encode($objeto->vdo_id);

            $o->pa = utf8_encode($objeto->int_nombre . " " . $objeto->int_apellido . "|");
            $o->t = $Encript->encode($objeto->usu_id . "|" . $objeto->usu_gru_id . "|" . $objeto->usu_per_id . "|" . $objeto->vdo_id);

            //Registrar logs 
            logs("APLICACION", "Ingreso al Sistema desde la aplicacion", $objeto->usu_id);

            // Correocto Bienvenido, Isaac Quispe usted a iniciado sesión
            $datos = json_encode($o);
            $accion = "correcto";
            $mensaje = "Bienvenido, <b>" . $o->pa . "</b> usted ha iniciado sesi&oacute;n";
        } else {
            $mensaje = "Por favor escriba su usuario y contrase&ntilde;a correctamente.";
            $accion = "error";
        }
    } else {
        $mensaje = "Por favor escriba su usuario y contrase&ntilde;a";
        $accion = "error";
    }
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