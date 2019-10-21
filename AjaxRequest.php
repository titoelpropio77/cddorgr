<?php

date_default_timezone_set("America/La_Paz");
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

$usuario = new USUARIO;
if (!($usuario->get_aut() == _sesion)) {
    echo '{"response":"error", "mensaje":"No tiene permisos"}';
    return;
}

if ($_GET['peticion'] == 'idPeriodo') {
    $conversor = new convertir();
    $ges_id = 0;
    if (isset($_GET['gesid'])) {
        $ges_id = $_GET['gesid'];
    } else {
        $ges_id = $_SESSION['ges_id'];
    }
    $fecha = $conversor->get_fecha_mysql($_GET['fecha']);
    $condicion = "pdo_ges_id=$ges_id and '$fecha'>=pdo_fecha_inicio and '$fecha'<=pdo_fecha_fin and pdo_eliminado='No' and pdo_estado='Abierto'";
    $periodo = FUNCIONES::objetos_bd("con_periodo", $condicion);
    if ($periodo->get_num_registros() > 0) {
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$fecha' and tca_eliminado='No'");
        if ($cambios->get_num_registros() > 0) {
            $cam_val = '[';
            $sw=true;
            for ($index = 0; $index < $cambios->get_num_registros(); $index++) {
                $camb = $cambios->get_objeto();
                if ($index > 0)
                    $cam_val.=',';
                if($camb->tca_valor*1>0){
                    $cam_val.='{"id":"' . $camb->tca_mon_id . '","val":"' . $camb->tca_valor . '","val_c":"' . $camb->tca_valor_compra . '","val_v":"' . $camb->tca_valor_venta . '"}';
                }else{
                    $sw=false;
                }
                $cambios->siguiente();
            }
            $cam_val.= ']';
            $objeto = $periodo->get_objeto();
            if($sw){
                echo '{"response":"ok", "id":"' . $objeto->pdo_id . '", "descripcion":"' . $objeto->pdo_descripcion . '","cambios":' . $cam_val . '}';
            }else{
                echo '{"response":"error", "mensaje":"El valor del tipo de cambio es incorrecto."}';
            }
        } else {
            echo '{"response":"error", "mensaje":"No existen tipos de cambios asignados a esta fecha."}';
        }
        return;
    } else {
        echo '{"response":"error", "mensaje":"La fecha seleccionada no pertenece a ningun periodo activo en esta Gestion."}';
        return;
    }
}
if ($_GET['peticion'] == 'listCuenta') {
    $ges_id = 0;
    if (isset($_GET['gesid'])) {
        $ges_id = $_GET['gesid'];
    } else {
        $ges_id = $_SESSION['ges_id'];
    }
    $input = strtolower($_GET['input']);
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 0;
    $tipo_c = isset($_GET['tipo']) ? $_GET['tipo'] : 'cuenta';
    if ($tipo_c == 'cuenta') {
        $tipo = "Movimiento";
        $and_filtro.='';
        if ($_GET['cue_padre']) {
            $and_filtro.="and cue_codigo like '$_GET[cue_padre].%' ";
        }
        if (isset($_GET['cu_tipo']) && $_GET['cu_tipo'] == 't') {
            $tipo = 'Titulo';
        }
        $condicion = "cue_eliminado='No' and cue_ges_id=$ges_id and cue_tipo='$tipo' and (cue_descripcion like '%$input%' or cue_codigo like '%$input%') $and_filtro ORDER BY cue_descripcion limit 0,$limit";
        $cuentas = FUNCIONES::objetos_bd("con_cuenta", $condicion);
        echo '{"results":[';
        $arr = array();
        for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
            $cuenta = $cuentas->get_objeto();
            $arr[] = '{"id":"' . $cuenta->cue_id . '","value":"' . htmlentities($cuenta->cue_descripcion) . '", "info":"' . $cuenta->cue_codigo . '"}';
            $cuentas->siguiente();
        }
        echo implode(",", $arr);
        echo ']}';
    } else if ($tipo_c == 'ca') {
        //$condicion = "can_eliminado='No' and can_descripcion like '%$input%' ORDER BY can_descripcion limit 0,$limit";        
        $condicion = "can_eliminado='No' and can_ges_id=$ges_id and can_tipo='Movimiento' and (can_descripcion like '%$input%' or can_codigo like '%$input%') ORDER BY can_descripcion limit 0,$limit";
        $cuentas = FUNCIONES::objetos_bd("con_cuenta_ca", $condicion);
        echo '{"results":[';
        //{"id": "2", "value": "Altman, Alisha", "info": ""},
        $arr = array();
        for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
            $cuenta = $cuentas->get_objeto();
            $arr[] = '{"id":"' . $cuenta->can_id . '","value":"' . htmlentities($cuenta->can_descripcion) . '", "info":"' . $cuenta->can_codigo . '"}';
            $cuentas->siguiente();
        }
        echo implode(",", $arr);
        echo ']}';
    } else if ($tipo_c == 'cc') {
        $condicion = "cco_eliminado='No' and cco_ges_id=$ges_id and cco_tipo='Movimiento' and (cco_descripcion like '%$input%' or cco_codigo like '%$input%') ORDER BY cco_descripcion limit 0,$limit";
        $cuentas = FUNCIONES::objetos_bd("con_cuenta_cc", $condicion);
        echo '{"results":[';
        //{"id": "2", "value": "Altman, Alisha", "info": ""},
        $arr = array();
        for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
            $cuenta = $cuentas->get_objeto();
            $arr[] = '{"id":"' . $cuenta->cco_id . '","value":"' . htmlentities($cuenta->cco_descripcion) . '", "info":"' . $cuenta->cco_codigo . '"}';
            $cuentas->siguiente();
        }
        echo implode(",", $arr);
        echo ']}';
    } else if ($tipo_c == 'cf') {
        $cuenta = $_GET['cue'];
        if ($cuenta == -1 || FUNCIONES::numero_registros("con_detalle_cf", "dcf_ges_id=$ges_id and dcf_cue_id=$cuenta") > 0) {
            $condicion = "cfl_eliminado='No' and cfl_ges_id=$ges_id and cfl_tipo='Movimiento' and (cfl_descripcion like '%$input%' or cfl_codigo like '%$input%') ORDER BY cfl_descripcion limit 0,$limit";
            $cuentas = FUNCIONES::objetos_bd("con_cuenta_cf", $condicion);

            echo '{"results":[';
            $arr = array();
            for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
                $cuenta = $cuentas->get_objeto();
                $arr[] = '{"id":"' . $cuenta->cfl_id . '","value":"' . htmlentities($cuenta->cfl_descripcion) . '", "info":"' . $cuenta->cfl_codigo . '"}';
                $cuentas->siguiente();
            }
            echo implode(",", $arr);
            echo ']}';
        } else {
            echo '{"results":[]}';
        }
    }
    return;
}
if ($_GET['peticion'] == 'periodos') {
    $ges_id = $_GET['gesid'];
    $sql = "select * from con_periodo where pdo_ges_id='$ges_id' and pdo_eliminado='No' order by pdo_fecha_inicio desc;";
    $pdos = FUNCIONES::objetos_bd_sql($sql);
    $result = '[';
    for ($i = 0; $i < $pdos->get_num_registros(); $i++) {
        $pdo = $pdos->get_objeto();
        if ($i > 0)
            $result.=',';
        $result.='{"descripcion":"' . $pdo->pdo_descripcion . '","fechai":"' . $pdo->pdo_fecha_inicio . '","fechaf":"' . $pdo->pdo_fecha_fin . '"}';
        $pdos->siguiente();
    }
    $result.=']';
    echo $result;
    return;
}
if ($_GET['peticion'] == 'cliente') {
    $nit = $_GET['nit'];
    $sql = "select * from con_razonsocial where rs_nit ='$nit';";
    $razon = FUNCIONES::objeto_bd_sql($sql);
    $nombre = htmlentities($razon->rs_nombre);
    echo $nombre;
    return;
}
if ($_GET['peticion'] == 'gestiones') {
//    echo $_SESSION['ges_id'];
    echo '<select id="gestion" style="width: 100px" name="gestion">';
    $sql = "select ges_id as id, ges_descripcion as nombre from con_gestion where ges_estado='Abierto' order by ges_fecha_ini desc";
    $gestiones = FUNCIONES::objetos_bd_sql($sql);
    for ($i = 0; $i < $gestiones->get_num_registros(); $i++) {
        $gestion = $gestiones->get_objeto();
        $cad = '';
        if ($gestion->id == $_SESSION['ges_id'])
            $cad = ' selected="selected" ';

        echo '<option value="' . $gestion->id . '"' . $cad . '>' . htmlentities($gestion->nombre) . '</option>';
        $gestiones->siguiente();
    }

    echo '</select>';
    return;
}
if ($_GET['peticion'] == 'cambiar_gestion') {
    $_SESSION['ges_id'] = $_GET['ges_id'];
    return;
}

if ($_GET['peticion'] == 'ver_cue') {
    $codigo = $_GET['cu'];
    $ges_id = isset($_GET['gesid']) ? $_GET['gesid'] : $_SESSION['ges_id'];
    $sql = "select * from con_cuenta where cue_codigo='$codigo' and cue_ges_id='$ges_id'";
//    echo $sql;
    $cuenta = FUNCIONES::objeto_bd_sql($sql);
    if (!cuenta_in_comprobante($cuenta, 'cue', 'con_cuenta')) {
        echo 'ok';
    } else {
        if ($cuenta->cue_tipo == 'Movimiento') {
            echo "La cuenta '$cuenta->cue_descripcion' ya tiene movimientos realizado en los comprobantes";
        } elseif ($cuenta->cue_tipo == 'Titulo') {
            echo "La cuenta '$cuenta->cue_descripcion' tiene hijos con movimientos en los comprobantes";
        }
    }
}
if ($_GET['peticion'] == 'ver_cco') {
    $codigo = $_GET['cu'];
    $ges_id = isset($_GET['gesid']) ? $_GET['gesid'] : $_SESSION['ges_id'];
    $sql = "select * from con_cuenta_cc where cco_codigo='$codigo' and cco_ges_id='$ges_id'";
    $cuenta = FUNCIONES::objeto_bd_sql($sql);
    if (!cuenta_in_comprobante($cuenta, 'cco', 'con_cuenta_cc')) {
        echo 'ok';
    } else {
        if ($cuenta->cco_tipo == 'Movimiento') {
            echo "La cuenta '$cuenta->cco_descripcion' ya tiene movimientos realizado en los comprobantes";
        } elseif ($cuenta->cco_tipo == 'Titulo') {
            echo "La cuenta '$cuenta->cco_descripcion' tiene hijos con movimientos en los comprobantes";
        }
    }
}
if ($_GET['peticion'] == 'ver_can') {
    $codigo = $_GET['cu'];
    $ges_id = isset($_GET['gesid']) ? $_GET['gesid'] : $_SESSION['ges_id'];
    $sql = "select * from con_cuenta_ca where can_codigo='$codigo' and can_ges_id='$ges_id'";
    $cuenta = FUNCIONES::objeto_bd_sql($sql);
    if (!cuenta_in_comprobante($cuenta, 'can', 'con_cuenta_ca')) {
        echo 'ok';
    } else {
        if ($cuenta->can_tipo == 'Movimiento') {
            echo "La cuenta '$cuenta->can_descripcion' ya tiene movimientos realizado en los comprobantes";
        } elseif ($cuenta->can_tipo == 'Titulo') {
            echo "La cuenta '$cuenta->can_descripcion' tiene hijos con movimientos en los comprobantes";
        }
    }
}
if ($_GET['peticion'] == 'ver_cfl') {
    $codigo = $_GET['cu'];
    $ges_id = isset($_GET['gesid']) ? $_GET['gesid'] : $_SESSION['ges_id'];
    $sql = "select * from con_cuenta_cf where cfl_codigo='$codigo' and cfl_ges_id='$ges_id'";
//    echo $sql."<br>";
    $cuenta = FUNCIONES::objeto_bd_sql($sql);
    if (!cuenta_in_comprobante($cuenta, 'cfl', 'con_cuenta_cf')) {
        echo 'ok';
    } else {
        if ($cuenta->cfl_tipo == 'Movimiento') {
            echo "La cuenta '$cuenta->cfl_descripcion' ya tiene movimientos realizado en los comprobantes";
        } elseif ($cuenta->cfl_tipo == 'Titulo') {
            echo "La cuenta '$cuenta->cfl_descripcion' tiene hijos con movimientos en los comprobantes";
        }
    }
}

if ($_GET['peticion'] == 'internos') {
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 0;
    $input = strtolower($_GET['input']);

    $sql = "select int_id as id,CONCAT(int_nombre,' ',int_apellido) as nombre from interno where CONCAT(int_nombre,' ',int_apellido) like '%$input%'order by int_nombre,int_apellido asc limit 0,$limit";
//    echo $sql.';<br>';
    $internos = FUNCIONES::objetos_bd_sql($sql);
    if ($internos->get_num_registros() > 0) {
        echo '{"results":[';
        $arr = array();
        for ($i = 0; $i < $internos->get_num_registros(); $i++) {
            $interno = $internos->get_objeto();
            $arr[] = '{"id":"' . $interno->id . '","value":"' . htmlentities($interno->nombre) . '", "info":""}';
            $internos->siguiente();
        }
        echo implode(",", $arr);
        echo ']}';
    } else {
        echo '{"results":[]}';
    }
}

if ($_GET['peticion'] == 'cajas') {
    $ges_id = isset($_GET['ges_id']) ? $_GET['ges_id'] : $_SESSION['ges_id'];
    $obj_act = FUNCIONES::atributo_bd_sql("select conf_valor as campo from con_configuracion where conf_ges_id='$ges_id' and conf_nombre='cuentas_act_disp'");
    $cuentas_act_disp = $obj_act != '' ? explode(',', $obj_act) : array();
    $array_cue_id = array();
    foreach ($cuentas_act_disp as $act_disp) {
        $cue_id = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$act_disp' and cue_ges_id='$ges_id'", "cue_id");
        if ($cue_id) {
            $array_cue_id[] = $cue_id;
        }
    }

    $obj_act = FUNCIONES::lista_bd_sql("select * from con_cajero_detalle where cjadet_usu_id='$_GET[usu_id]'");
    $cuentas_act_disp_usu = FUNCIONES::lista_bd_sql("select * from con_cajero_detalle where cjadet_usu_id='$_GET[usu_id]'");
    $array_cue_id_usu = array();
    foreach ($cuentas_act_disp_usu as $act_disp) {
        $cue_id = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$act_disp->cjadet_cue_id' and cue_ges_id='$ges_id'", "cue_id");
        if ($cue_id) {
            $array_cue_id_usu[] = $cue_id;
        }
    }

    $list_cue_id = implode(',', $array_cue_id);
    $list_cue_id_usu = implode(',', $array_cue_id_usu);
    $sql = "select distinct cue_id,cue_codigo,cue_mon_id,cue_descripcion from con_comprobante, con_comprobante_detalle, con_cuenta
            where cue_id=cde_cue_id and cde_cmp_id=cmp_id and cmp_ges_id='$ges_id'and 
                ((cmp_usu_cre='$_GET[usu_id]' and cde_cue_id in($list_cue_id)) or cde_cue_id in ($list_cue_id_usu)
                    )";
    
//    $sql = "select distinct cue_id,cue_codigo,cue_mon_id,cue_descripcion from 
//        con_cuenta
//        left join con_comprobante_detalle on (cue_id=cde_cue_id)
//        left join con_comprobante on (cde_cmp_id=cmp_id)        
//        where  cmp_ges_id='$ges_id'and 
//            ((cde_cue_id in($list_cue_id)) or cde_cue_id in ($list_cue_id_usu))";
    
//    echo $sql;
    $cuentas = FUNCIONES::objetos_bd_sql($sql);
    if ($cuentas->get_num_registros() > 0) {
        echo '[';
        $arr = array();
        for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
            $cuenta = $cuentas->get_objeto();
            $arr[] = '{"id":"' . $cuenta->cue_id . '","codigo":"' . $cuenta->cue_codigo . '","moneda":"' . $cuenta->cue_mon_id . '" ,"nombre":"' . htmlentities($cuenta->cue_descripcion) . '"}';
            $cuentas->siguiente();
        }
        echo implode(",", $arr);
        echo ']';
    } else {
        echo '[]';
    }
}

if ($_GET['peticion'] == 'ctitulos') {
    $ges_id = isset($_GET['ges_id']) ? $_GET['ges_id'] : $_SESSION['ges_id'];
    $sql = "select * from con_cuenta where cue_tree_level=1 and cue_ges_id='$ges_id' order by cue_codigo";
    $lista = FUNCIONES::lista_bd_sql($sql);

    $i = 0;
    $resp = "[";
    foreach ($lista as $obj) {
        if ($i > 0) {
            $resp.=",";
        }
        $resp.="{\"id\":\"$obj->cue_id\",\"nombre\":\"$obj->cue_descripcion\"}";
        $i++;
    }
    $resp.="]";
    echo $resp;
}
if ($_GET['peticion'] == 'dif_fechas') {
    $fecha1 = FUNCIONES::get_fecha_mysql($_GET[fecha1]);
    $fecha2 = FUNCIONES::get_fecha_mysql($_GET[fecha2]);
    $dias = FUNCIONES::diferencia_dias($fecha1, $fecha2);
    echo "{\"type\":\"success\",\"res\":\"$dias\"}";
}

if ($_GET['peticion'] == 'valor_m2') {
//    echo "lote:$_GET[lote] - meses_plazo:$_GET[meses_plazo]";
    calcular_m2($_GET[lote], $_GET[meses_plazo]);
}

if ($_GET['peticion'] == 'urb_productos') {
    obtener_productos($_GET['urb_id']);
}

function obtener_productos($urb_id){
    $sql_prods = "select * from urbanizacion_producto where uprod_urb_id='$urb_id' 
        and uprod_eliminado='No'";
    $productos = FUNCIONES::lista_bd_sql($sql_prods);
    
    $resp = new stdClass();
    $resp->productos = $productos;
    echo json_encode($resp);
}

/*
 * verifica si existe la cuen en un comprobante
 * @cuenta objeto de la fila con_cuenta
 */

function cuenta_in_comprobante($cuenta, $_cu, $tabla) {
    $col_cu = "cde_" . $_cu . "_id";
    if ($cuenta->{$_cu . '_tipo'} == 'Movimiento') {
        $sql = "select * from con_comprobante_detalle , con_comprobante
                where cmp_id=cde_cmp_id and cmp_eliminado='No' and $col_cu='" . $cuenta->{$_cu . '_id'} . "'";
//        echo $sql;
        $detalles = FUNCIONES::objetos_bd_sql($sql);
        if ($detalles->get_num_registros() > 0) {
            return true;
        } else {
            return false;
        }
    } elseif ($cuenta->{$_cu . '_tipo'} == 'Titulo') {
        $sql = "select * from $tabla where " . $_cu . "_padre_id='" . $cuenta->{$_cu . '_id'} . "'";
        $cuentas = FUNCIONES::objetos_bd_sql($sql);
        if ($cuentas->get_num_registros() > 0) {
//            return true;
            for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
                if (cuenta_in_comprobante($cuentas->get_objeto(), $_cu, $tabla)) {
                    return true;
                }
                $cuentas->siguiente();
            }
        }
    }
    return false;
}

function calcular_m2($lot_id, $meses_plazo){
    $sql = "select * from lote where lot_id='$lot_id'";
    $lote = FUNCIONES::objeto_bd_sql($sql);
    
    $resp = new stdClass();
    
    if ($lote) {        
        
        $sql_zpr = "select * from zona_precio where zpr_zon_id='$lote->lot_zon_id'
        and zpr_meses_min<='$meses_plazo' and zpr_meses_max>='$meses_plazo'
        limit 0,1";
        
        $zon_precio = FUNCIONES::objeto_bd_sql($sql_zpr);
        
        if ($zon_precio) {
            $resp->ok = "si";
            $resp->dato = $zon_precio->zpr_precio;
        } else {
            $resp->ok = "no";
            $resp->mensaje = "no existe el valor del m2 para los meses indicados.";
        }
        
    } else {
        $resp->ok = "no";
        $resp->mensaje = "no existe el lote";
    }
    
    echo json_encode($resp);
}

?>