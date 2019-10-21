<?php



require_once('mysql.php');
require("../config/zona_horaria.php");

//echo setear_fecha(strtotime(date('Y-m-d')));
//return;
$fecha_actual = date('Y-m-d');
//$fecha_actual = date('2014-12-22');

/* * ************************************ RECORDATORIO 1 DIA ANTERIOR *************************************** */
//$filtro_urb=" and ven_urb_id!=5";
$filtro_urb=""; 
$fecha_cuota = sumar_fechas($fecha_actual, 1);

$query = new QUERY();
$query2 = new QUERY();

//$sql = "select ind_id,ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_estado,ind_fecha_programada,ind_interes,
//		ind_capital,ind_monto_parcial,ind_saldo 
//                
//                from interno_deuda where ind_fecha_programada='$fecha_cuota' and ind_estado='Pendiente' and ind_tabla='venta'";

$sql = "select 
                ven_id,int_id,int_nombre,int_apellido, int_telefono, int_celular ,ven_codigo,ind_fecha_programada,urb_nombre_corto,man_nro,lot_nro
        from 
                interno, venta,interno_deuda,urbanizacion,manzano,lote
        where 
                ind_fecha_programada='$fecha_cuota' and ind_tabla='venta' and (int_telefono!='' or int_celular!='') and ind_estado='Pendiente' and 
                ven_int_id=int_id and ven_id=ind_tabla_id and urb_id=ven_urb_id and ven_lot_id=lot_id and lot_man_id=man_id
				and ven_estado = 'Pendiente'
				$filtro_urb
        ";

$query->consulta($sql);

$num = $query->num_registros();
$sql_insert = "insert into bandeja(ban_int_id,ban_cel,ban_contenido,ban_men_id, ban_estado,ban_fecha_cre,ban_hora_cre)values";
for ($i = 1; $i <= $num; $i++) {
//    list($ind_id, $ind_int_id, $ind_monto, $ind_moneda, $ind_concepto, $ind_estado, $ind_fecha_programada, $ind_interes, $ind_capital, $ind_monto_parcial, $ind_saldo) = $query->valores_fila();
    list($ven_id,$int_id, $int_nombre, $int_apellido, $int_telefono, $int_celular, $codigo, $fecha_prog, $urb_nombre,$man_nro,$lot_nro) = $query->valores_fila();
    $sql_num_cu="select count(*) as campo from interno_deuda where ind_tabla_id='$ven_id' and ind_fecha_programada<='$fecha_cuota' and ind_estado='Pendiente' and ind_tabla='venta'";
    $query2->consulta($sql_num_cu);
    $obj=$query2->valores_fila();
    $num_cuotas=$obj[0];
    
    $mensaje = get_sms('antes', $fecha_prog, $man_nro,$lot_nro,$urb_nombre,$num_cuotas);
    $celulares = get_num_celular($int_telefono, $int_celular);
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    foreach ($celulares as $cel) {
        $sql_ins_bandeja = $sql_insert . "('$int_id','$cel','$mensaje','0','LISTO','$fecha','$hora')";
        $query2->consulta($sql_ins_bandeja);
        break;
    }
    
}

/* * ************************************ FIN RECORDATORIO 1 DIA ANTERIOR *************************************** */

/* * ************************************ RECORDATORIO 1 DIA DESPUES *************************************** */

$fecha_cuota = restar_fechas($fecha_actual, 1);

$query = new QUERY();
$query2 = new QUERY();


$sql = "select 
                ven_id,int_id,int_nombre,int_apellido, int_telefono, int_celular ,ven_codigo,ind_fecha_programada,urb_nombre_corto,man_nro,lot_nro
        from 
                interno, venta,interno_deuda,urbanizacion,manzano,lote
        where 
                ind_fecha_programada='$fecha_cuota' and ind_tabla='venta' and (int_telefono!='' or int_celular!='') and ind_estado='Pendiente' and 
                ven_int_id=int_id and ven_id=ind_tabla_id and urb_id=ven_urb_id and ven_lot_id=lot_id and lot_man_id=man_id 
				and ven_estado = 'Pendiente'
				$filtro_urb
        ";

$query->consulta($sql);

$num = $query->num_registros();
$sql_insert = "insert into bandeja(ban_int_id,ban_cel,ban_contenido,ban_men_id, ban_estado,ban_fecha_cre,ban_hora_cre)values";
for ($i = 1; $i <= $num; $i++) {
//    list($ind_id, $ind_int_id, $ind_monto, $ind_moneda, $ind_concepto, $ind_estado, $ind_fecha_programada, $ind_interes, $ind_capital, $ind_monto_parcial, $ind_saldo) = $query->valores_fila();
    list($ven_id,$int_id, $int_nombre, $int_apellido, $int_telefono, $int_celular, $codigo, $fecha_prog, $urb_nombre,$man_nro,$lot_nro) = $query->valores_fila();
//    $sql_num_cu="select count(*) as campo from interno_deuda where ind_tabla_id='$ven_id' and ind_fecha_programada<='$fecha_cuota' and ind_estado='Pendiente' and ind_tabla='venta'";
//    $query2->consulta($sql_num_cu);
//    $obj=$query2->valores_fila();
//    $num_cuotas=$obj[0];
    
    $mensaje = get_sms('despues', $fecha_prog, $man_nro,$lot_nro,$urb_nombre,0);
    $celulares = get_num_celular($int_telefono, $int_celular);
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    foreach ($celulares as $cel) {
        $sql_ins_bandeja = $sql_insert . "('$int_id','$cel','$mensaje','0','LISTO','$fecha','$hora')";
        $query2->consulta($sql_ins_bandeja);
        break;
    }
    
}
/* * ************************************ FIN RECORDATORIO 1 DIA DESPUES *************************************** */

//get_sms('antes', $fecha_prog, $man_nro,$lot_nro,$urb_nombre,$num_cuotas)
function get_sms($tipo, $fecha, $man_nro,$lot_nro,$urb_nombre, $num_cuotas = '') {

    $_fecha = explode('-', $fecha);
    $dia = (1 * $_fecha[2]) . ' de ' . get_mes($_fecha[1]);

    $mensaje = "";
    $fecha_lat=  get_fecha_latina($fecha);
    if ($tipo == 'antes') {
        if($num_cuotas<=2){
            $mensaje = "Estimado Cliente, Ciudad de Dios le recuerda que el $fecha_lat, vence su cuota del M{$man_nro}L{$lot_nro} $urb_nombre. Si ya pago, favor hacer caso omiso.";
        }else{ /// mayor a 3 meses
            $mensaje = "Estimado Cliente, le recordamos que el $fecha_lat tendra en mora $num_cuotas cuotas del M{$man_nro}L{$lot_nro} $urb_nombre. Agradecemos cancelar y evitar su reversion.";
        }
        
    } elseif ($tipo == 'despues') {
        $mensaje = "Estimado Cliente, Ciudad de Dios le recuerda que el $fecha_lat, vencio su cuota del M{$man_nro}L{$lot_nro} $urb_nombre. Agradecemos cancelarla cuanto antes.";
    }
    return $mensaje;
}

function get_mes($mes) {
    $mes = $mes * 1;
    if ($mes == 1) {
        return 'Enero';
    } elseif ($mes == 2) {
        return 'Febrero';
    } elseif ($mes == 3) {
        return 'Marzo';
    } elseif ($mes == 4) {
        return 'Abril';
    } elseif ($mes == 5) {
        return 'Mayo';
    } elseif ($mes == 6) {
        return 'Junio';
    } elseif ($mes == 7) {
        return 'Julio';
    } elseif ($mes == 8) {
        return 'Agosto';
    } elseif ($mes == 9) {
        return 'Septiembre';
    } elseif ($mes == 10) {
        return 'Octubre';
    } elseif ($mes == 11) {
        return 'Noviembre';
    } elseif ($mes == 12) {
        return 'Diciembre';
    }
}

function get_num_celular($telefono, $celular = "") {
    $numeros = array();
    $telefono = trim($telefono);
    if ($telefono) {
        $i = 0;
        $numero = '';
        while ($i < strlen($telefono)) {
            $char = $telefono[$i];
            if ($char != '-' && $char != '/') {
                $numero.=$char;
            }
            if (strlen($numero) == 8) {
                if ($numero[0] == '7' || $numero[0] == '6') {
                    $numeros[] = $numero;
                    $numero = '';
                } else {
                    $numero = '';
                }
            } elseif (strlen($numero) == 7) {
                if ($numero[0] != '7' && $numero[0] != '6') {
                    $numero = '';
                }
            }
            $i++;
        }
    }
    $celular = trim($celular);
    if ($celular) {
        $i = 0;
        $numero = '';
        while ($i < strlen($celular)) {
            $char = $celular[$i];
            if ($char != '-' && $char != '/') {
                $numero.=$char;
            }
            if (strlen($numero) == 8) {
                if ($numero[0] == '7' || $numero[0] == '6') {
                    $numeros[] = $numero;
                    $numero = '';
                } else {
                    $numero = '';
                }
            } elseif (strlen($numero) == 7) {
                if ($numero[0] != '7' && $numero[0] != '6') {
                    $numero = '';
                }
            }
            $i++;
        }
    }
    return $numeros;
}

function nombre_persona($interno) {
    $query = new QUERY();

    $sql = "select int_nombre,int_apellido from interno where int_id=$interno";

    $query->consulta($sql);

    list($int_nombre, $int_apellido) = $query->valores_fila();

    return $int_nombre . ' ' . $int_apellido;
}

function restar_fechas($fecha_actual, $dias) {
    $fecha = $fecha_actual;
    $nuevafecha = strtotime("-$dias day", strtotime($fecha));
    $nuevafecha = date('Y-m-d', $nuevafecha);

    return $nuevafecha;
}

function sumar_fechas($fecha_actual, $dias) {
    $fecha = $fecha_actual;
    $nuevafecha = strtotime("+$dias day", strtotime($fecha));
    $nuevafecha = date('Y-m-d', $nuevafecha);

    return $nuevafecha;
}

function enviar_celular() {
    $primer_mensaje = '';
    $segundo_mensaje = '';
    if ($tipo_mensaje == 'dia_antes') {
        $primer_mensaje = 'Apreciado cliente, Urbanización Campo Grande le recuerda que el día jueves, 31 de julio, vence su cuota del terreno 2-2005-1.  Si ya canceló quedamos muy agradecidos';
    } else {
        if ($tipo_mensaje == 'dia_despues') {
            $primer_mensaje = 'Campo Grande le recuerda que la fecha de pago de su cuota esta vencida, evite multas cancelando dentro de los 5 dias del plazo establecido.<br>';
        }
    }
}

function get_fecha_latina($fecha) {
    ereg("([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $fecha, $mifecha);
    $lafecha = $mifecha[3] . "/" . $mifecha[2] . "/" . $mifecha[1];
    return $lafecha;
}

?>
