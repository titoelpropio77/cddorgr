<?php
date_default_timezone_set("America/La_Paz");
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
require_once('clases/cupon.class.php');
//require_once("clases/mlm.class.php");
//require_once('clases/comisiones.class.php');

if ($_GET['peticion'] == 'obtener_cupones') {
    obtener_cupones((object)$_GET);
}

if ($_GET['peticion'] == 'calcular_cupones') {
    calcular_cupones((object)$_GET);
}

function obtener_cupones($get){
    
    $ini = FUNCIONES::get_fecha_mysql($get->fecha_ini) . " 00:00:00";
    $fin = FUNCIONES::get_fecha_mysql($get->fecha_fin) . " 23:59:59";
    $s_sucs = implode(',', $get->sucursales);
    
    $sql_cupones = "select * from cupon 
    inner join venta on (cup_ven_id=ven_id)
    where cup_sorteo_id='$get->sorteo'
    and ven_estado in ('Pendiente','Pagado')
    and ven_suc_id in ($s_sucs)
    and cup_estado='Activo' and cup_eliminado='No' and cup_fecha_cre>='$ini' 
    and cup_fecha_cre<='$fin' 
    order by cup_numero asc";
    
    $cupones = FUNCIONES::lista_bd_sql($sql_cupones);
    
    foreach($cupones as $cup){
        CUPON::imprimir_cupon_anfora($cup->cup_id);
        ?>
        <br/>
        <br/>
        <p style="page-break-after: always"></p>
        <?php
    }
}

function calcular_cupones($get){
    
//    print_r($get);
    
    $ini = FUNCIONES::get_fecha_mysql($get->fecha_ini) . " 00:00:00";
    $fin = FUNCIONES::get_fecha_mysql($get->fecha_fin) . " 23:59:59";
    $s_sucs = implode(',', $get->sucursales);
    
    $sql_cupones = "select * from cupon 
    inner join venta on (cup_ven_id=ven_id)
    where cup_sorteo_id='$get->sorteo'
    and ven_estado in ('Pendiente','Pagado')
    and ven_suc_id in ($s_sucs)
    and cup_estado='Activo' and cup_eliminado='No' and cup_fecha_cre>='$ini' 
    and cup_fecha_cre<='$fin' 
    order by cup_numero asc";
    
//    echo $sql_cupones;
    
    $cupones = FUNCIONES::lista_bd_sql($sql_cupones);
    
    $minimo = $cupones[0]->cup_numero;
    $maximo = $cupones[count($cupones) - 1]->cup_numero;
    
    $cant = count($cupones);
    $cup_ids = array();
    
    foreach ($cupones as $cup) {
        $cup_ids[] = $cup->cup_id;
    }
    
    $sql_suc = "select suc_id,suc_nombre,count(cup_id)as cant 
    from ter_sucursal 
    inner join venta on (suc_id=ven_suc_id)
    inner join cupon on (ven_id=cup_ven_id)
    where cup_sorteo_id='$get->sorteo'
    and ven_estado in ('Pendiente','Pagado')
    and ven_suc_id in ($s_sucs)
    and cup_estado='Activo' 
    and cup_eliminado='No'
    and cup_fecha_cre>='$ini' 
    and cup_fecha_cre<='$fin'
    group by suc_id";
//    $suc =  FUNCIONES::objetos_bd_sql($sql_suc);
    
    $regs = FUNCIONES::lista_bd_sql($sql_suc);
    $arr_sucs = array();
    foreach ($regs as $reg) {
        $ele = new stdClass();
        $ele->id = $reg->suc_id;
        $ele->nombre = $reg->suc_nombre;
        $ele->cant = $reg->cant;
        $arr_sucs[] = $ele;
    }
    
    $s_arr = json_encode($arr_sucs);
    
    echo '{"sucursales":' . $s_arr . ',"cantidad":"' . $cant . '","minimo":"' . $minimo . '","maximo":"' . $maximo . '","cupones":"' . implode(',', $cup_ids) . '"}';
}