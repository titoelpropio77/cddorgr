<?php

require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

copiar_nuevas_variables();
//actualizar_orden();
//copiar_configuracion(15, 0);
function copiar_configuracion($ges_id, $ges_conf) {
    $sql = "select * from con_configuracion where conf_ges_id='$ges_conf' order by conf_orden";
//    echo $sql;
    $configs = FUNCIONES::objetos_bd_sql($sql);
//    echo $configs->get_num_registros();
    for ($i = 0; $i < $configs->get_num_registros(); $i++) {
//        _PRINT::pre($configs->get_objeto());
        $conf=$configs->get_objeto();
        $insert = "INSERT INTO `con_configuracion` (`conf_orden`,`conf_nombre`, `conf_valor`, `conf_tconf_id`, `conf_editable`, `conf_ges_id`, `conf_eliminado`) 
                    VALUES ('$conf->conf_orden','$conf->conf_nombre', '$conf->conf_valor', '$conf->conf_tconf_id', '$conf->conf_editable', '$ges_id', 'No');";
        echo $insert."<br>";
        $configs->siguiente();
    }
    return;
//    $max = FUNCIONES::atributo_bd("con_cuenta_cf", "1", "max(cfl_id)");
//    $inc_id = $max + 1;
//        echo $inc_id."<br>";
//    $_cuentas = array();
//    $_padres = array();
    for ($i = 0; $i < $configs->get_num_registros(); $i++) {
        $cuenta = clone $configs->get_objeto();
        $_padres[$cuenta->cfl_id] = $inc_id;
        $cuenta->cfl_id = $inc_id;
        $cuenta->cfl_ges_id = $ges_id;
        if ($cuenta->cfl_padre_id > 1) {
            $cuenta->cfl_padre_id = $_padres[$cuenta->cfl_padre_id];
        }
        $_cuentas[] = $cuenta;
        $configs->siguiente();
        $inc_id++;
    }
    $conect = new ADO();
    for ($i = 0; $i < count($_cuentas); $i++) {
        $cuenta = $_cuentas[$i];
        $sql = "insert into con_cuenta_cf(cfl_id, cfl_ges_id, cfl_codigo,   cfl_descripcion, cfl_tipo, cfl_padre_id, cfl_eliminado, cfl_mon_id, cfl_tree_left, cfl_tree_right, cfl_tree_position, cfl_tree_level)
                    values('$cuenta->cfl_id','$cuenta->cfl_ges_id','$cuenta->cfl_codigo','$cuenta->cfl_descripcion','$cuenta->cfl_tipo','$cuenta->cfl_padre_id','$cuenta->cfl_eliminado','$cuenta->cfl_mon_id','$cuenta->cfl_tree_left','$cuenta->cfl_tree_right','$cuenta->cfl_tree_position','$cuenta->cfl_tree_level');";
//        $conect->ejecutar($sql);
        echo $sql;
    }
}

function actualizar_orden() {
    $sql = "select * from con_gestion;";
    $gestiones = FUNCIONES::objetos_bd_sql($sql);

    $sql = "select * from con_configuracion where conf_ges_id='0'";
    $insert = "INSERT INTO `sistemac_gis`.`con_configuracion` (`conf_nombre`, `conf_valor`, `conf_tconf_id`, `conf_editable`, `conf_ges_id`, `conf_eliminado`) VALUES ('asdfsda', '12312', '1', '1', '1', 'No');";
    $configs = FUNCIONES::objetos_bd_sql($sql);
    for ($j = 0; $j < $gestiones->get_num_registros(); $j++) {
        $gestion = $gestiones->get_objeto();
        $ges_id = $gestion->ges_id;
        $configs->reset();
        for ($i = 0; $i < $configs->get_num_registros(); $i++) {
            $conf = $configs->get_objeto();
            $insert = "UPDATE `con_configuracion` SET conf_orden='$conf->conf_orden' WHERE conf_nombre='$conf->conf_nombre' and conf_ges_id='$ges_id';";
            echo $insert . "<br>";
            $configs->siguiente();
        }
        $gestiones->siguiente();
    }
}

function copiar_nuevas_variables() {
    $sql = "select * from con_gestion;";
    $gestiones = FUNCIONES::objetos_bd_sql($sql);

    $sql = "select * from con_configuracion where conf_ges_id='0'";
    $insert = "INSERT INTO `sistemac_gis`.`con_configuracion` (`conf_nombre`, `conf_valor`, `conf_tconf_id`, `conf_editable`, `conf_ges_id`, `conf_eliminado`) VALUES ('asdfsda', '12312', '1', '1', '1', 'No');";
    $configs = FUNCIONES::objetos_bd_sql($sql);
    for ($j = 0; $j < $gestiones->get_num_registros(); $j++) {
        $gestion = $gestiones->get_objeto();
        $ges_id = $gestion->ges_id;
        $configs->reset();
        for ($i = 0; $i < $configs->get_num_registros(); $i++) {
            $conf = $configs->get_objeto();

            $sql = "select * from con_configuracion where conf_nombre='$conf->conf_nombre' and conf_ges_id='$ges_id'";
//        echo $sql;
            if (FUNCIONES::objeto_bd_sql($sql) == null) {
                $insert = "INSERT INTO `con_configuracion` (`conf_orden`,`conf_nombre`, `conf_valor`, `conf_tconf_id`, `conf_editable`, `conf_ges_id`, `conf_eliminado`) 
                    VALUES ('$conf->conf_orden','$conf->conf_nombre', '$conf->conf_valor', '$conf->conf_tconf_id', '$conf->conf_editable', '$ges_id', 'No');";
                echo $insert . "<br>";
            }
            $configs->siguiente();
        }
        $gestiones->siguiente();
    }
}

return;


//
//$sql="describe vendedor ;";
//$tablas=FUNCIONES::objetos_bd_sql($sql);
//echo $sql;
////return;
//for($i=0;$i<$tablas->get_num_registros();$i++){
//    FUNCIONES::print_pre($tablas->get_objeto());
//    $tablas->siguiente();
//}





$array = array();
$array[55] = 20;
$array[94] = 200;
$array[102] = 100;

$array[55] = $array[55] + 200;
$array[55] = $array[55] + 200;
$array[105] = $array[105] + 150;

FUNCIONES::print_pre($array);
return;





$ges_id = 1;
$n_ges = 13;
//$sql="select * from con_cuenta where cue_ges_id='$ges_id' order by cue_id";
//$cuentas=  FUNCIONES::objetos_bd_sql($sql);
//$max=  FUNCIONES::atributo_bd("con_cuenta", "1", "max(cue_id)");
//$inc_id=$max+1;
//echo $inc_id."<br>";
//$_cuentas=array();
//$_padres=array();
//for($i=0;$i<$cuentas->get_num_registros();$i++){    
//    $cuenta= clone $cuentas->get_objeto();
//    $_padres[$cuenta->cue_id]=$inc_id;
//    $cuenta->cue_id=$inc_id;
//    $cuenta->cue_ges_id=$n_ges;
//    if($cuenta->cue_padre_id>1){
//        $cuenta->cue_padre_id=$_padres[$cuenta->cue_padre_id];
//    }    
//    $_cuentas[]=$cuenta;    
//    $cuentas->siguiente();
//    $inc_id++;
//}
//
//for($i=0;$i<count($_cuentas);$i++){
//    $cuenta=$_cuentas[$i];
//    $sql="insert into con_cuenta(cue_id, cue_ges_id, cue_codigo,   cue_descripcion, cue_tipo, cue_padre_id, cue_eliminado, cue_mon_id, cue_tree_left, cue_tree_right, cue_tree_position, cue_tree_level)
//            values('$cuenta->cue_id','$cuenta->cue_ges_id','$cuenta->cue_codigo','$cuenta->cue_descripcion','$cuenta->cue_tipo','$cuenta->cue_padre_id','$cuenta->cue_eliminado','$cuenta->cue_mon_id','$cuenta->cue_tree_left','$cuenta->cue_tree_right','$cuenta->cue_tree_position','$cuenta->cue_tree_level');";
//    echo $sql."<br>";
//}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
//$sql="select * from con_cuenta_ca where can_ges_id='$ges_id' order by can_id";
//$cuentas=  FUNCIONES::objetos_bd_sql($sql);
//$max=  FUNCIONES::atributo_bd("con_cuenta_ca", "1", "max(can_id)");
//$inc_id=$max+1;
//echo $inc_id."<br>";
//$_cuentas=array();
//$_padres=array();
//for($i=0;$i<$cuentas->get_num_registros();$i++){    
//    $cuenta= clone $cuentas->get_objeto();
//    $_padres[$cuenta->can_id]=$inc_id;
//    $cuenta->can_id=$inc_id;
//    $cuenta->can_ges_id=$n_ges;
//    if($cuenta->can_padre_id>1){
//        $cuenta->can_padre_id=$_padres[$cuenta->can_padre_id];
//    }    
//    $_cuentas[]=$cuenta;    
//    $cuentas->siguiente();
//    $inc_id++;
//}
//
//for($i=0;$i<count($_cuentas);$i++){
//    $cuenta=$_cuentas[$i];
//    $sql="insert into con_cuenta_ca(can_id, can_ges_id, can_codigo,   can_descripcion, can_tipo, can_padre_id, can_eliminado, can_mon_id, can_tree_left, can_tree_right, can_tree_position, can_tree_level)
//            values('$cuenta->can_id','$cuenta->can_ges_id','$cuenta->can_codigo','$cuenta->can_descripcion','$cuenta->can_tipo','$cuenta->can_padre_id','$cuenta->can_eliminado','$cuenta->can_mon_id','$cuenta->can_tree_left','$cuenta->can_tree_right','$cuenta->can_tree_position','$cuenta->can_tree_level');";
//    echo $sql."<br>";
//}
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//$sql="select * from con_cuenta_cc where cco_ges_id='$ges_id' order by cco_id";
//$cuentas=  FUNCIONES::objetos_bd_sql($sql);
//$max=  FUNCIONES::atributo_bd("con_cuenta_cc", "1", "max(cco_id)");
//$inc_id=$max+1;
//echo $inc_id."<br>";
//$_cuentas=array();
//$_padres=array();
//for($i=0;$i<$cuentas->get_num_registros();$i++){    
//    $cuenta= clone $cuentas->get_objeto();
//    $_padres[$cuenta->cco_id]=$inc_id;
//    $cuenta->cco_id=$inc_id;
//    $cuenta->cco_ges_id=$n_ges;
//    if($cuenta->cco_padre_id>1){
//        $cuenta->cco_padre_id=$_padres[$cuenta->cco_padre_id];
//    }    
//    $_cuentas[]=$cuenta;    
//    $cuentas->siguiente();
//    $inc_id++;
//}
//
//for($i=0;$i<count($_cuentas);$i++){
//    $cuenta=$_cuentas[$i];
//    $sql="insert into con_cuenta_cc(cco_id, cco_ges_id, cco_codigo,   cco_descripcion, cco_tipo, cco_padre_id, cco_eliminado, cco_mon_id, cco_tree_left, cco_tree_right, cco_tree_position, cco_tree_level)
//            values('$cuenta->cco_id','$cuenta->cco_ges_id','$cuenta->cco_codigo','$cuenta->cco_descripcion','$cuenta->cco_tipo','$cuenta->cco_padre_id','$cuenta->cco_eliminado','$cuenta->cco_mon_id','$cuenta->cco_tree_left','$cuenta->cco_tree_right','$cuenta->cco_tree_position','$cuenta->cco_tree_level');";
//    echo $sql."<br>";
//}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "select * from con_cuenta_cf where cfl_ges_id='$ges_id' order by cfl_id";
$cuentas = FUNCIONES::objetos_bd_sql($sql);
$max = FUNCIONES::atributo_bd("con_cuenta_cf", "1", "max(cfl_id)");
$inc_id = $max + 1;
echo $inc_id . "<br>";
$_cuentas = array();
$_padres = array();
for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
    $cuenta = clone $cuentas->get_objeto();
    $_padres[$cuenta->cfl_id] = $inc_id;
    $cuenta->cfl_id = $inc_id;
    $cuenta->cfl_ges_id = $n_ges;
    if ($cuenta->cfl_padre_id > 1) {
        $cuenta->cfl_padre_id = $_padres[$cuenta->cfl_padre_id];
    }
    $_cuentas[] = $cuenta;
    $cuentas->siguiente();
    $inc_id++;
}

for ($i = 0; $i < count($_cuentas); $i++) {
    $cuenta = $_cuentas[$i];
    $sql = "insert into con_cuenta_cf(cfl_id, cfl_ges_id, cfl_codigo,   cfl_descripcion, cfl_tipo, cfl_padre_id, cfl_eliminado, cfl_mon_id, cfl_tree_left, cfl_tree_right, cfl_tree_position, cfl_tree_level)
            values('$cuenta->cfl_id','$cuenta->cfl_ges_id','$cuenta->cfl_codigo','$cuenta->cfl_descripcion','$cuenta->cfl_tipo','$cuenta->cfl_padre_id','$cuenta->cfl_eliminado','$cuenta->cfl_mon_id','$cuenta->cfl_tree_left','$cuenta->cfl_tree_right','$cuenta->cfl_tree_position','$cuenta->cfl_tree_level');";
    echo $sql . "<br>";
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>