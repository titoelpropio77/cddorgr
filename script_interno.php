<?php

require_once('clases/reporte.class.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');



//exportar_internos();
//corregir_nh();
pago_comisiones() ;
function pago_comisiones() {
    $listado=FUNCIONES::lista_bd_sql("select int_nombre,int_apellido,pve_usu_id,pve_fecha,pve_monto, pve_moneda,pve_glosa from pago_vendedores, interno, vendedor where vdo_id=pve_vdo_id and vdo_int_id=int_id order by pve_fecha asc");
    echo '<table>';
    foreach ($listado as $obj) {
        echo "<tr>";
        echo "<td>$obj->int_nombre $obj->int_nombre</td>";
        echo "<td>$obj->pve_usu_id</td>";
        echo "<td>".FUNCIONES::get_fecha_latina($obj->pve_fecha)."</td>";
        echo "<td>$obj->pve_monto</td>";
        echo "<td>$obj->pve_moneda</td>";
        echo "<td>$obj->pve_glosa</td>";
        echo "</tr>";
    }
    echo '</table>';
}
function corregir_nh() {
    $internos = FUNCIONES::objetos_bd_sql("select * from interno where int_nombre like '%?%';");
    for ($i = 0; $i < $internos->get_num_registros(); $i++) {
        $obj=$internos->get_objeto();
        $int_nombre=  str_replace('?', 'ñ', $obj->int_nombre);
        $sql_up="update interno set int_nombre='$int_nombre' where int_id='$obj->int_id'";
        echo $sql_up.';<br>';
        $internos->siguiente();
    }
}
function exportar_internos() {
    $internos = FUNCIONES::objetos_bd_sql("select * from interno order by int_nombre asc");
    $result=array();
    for ($i = 0; $i < $internos->get_num_registros(); $i++) {
        $obj=$internos->get_objeto();
        $nro=$i+1;
        $reservas=FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_int_id='$obj->int_id'");
        $str_reservas="";
        if($reservas){
            $str_reservas="SI";
        }
        $ventas=FUNCIONES::objeto_bd_sql("select * from venta where ven_int_id='$obj->int_id'");
        $str_ventas="";
        if($ventas){
            $str_ventas="SI";
        }
        $result[]=array($obj->int_nombre,$obj->int_apellido,$obj->int_id,'',$str_reservas,$str_ventas,$obj->int_email,$obj->int_telefono,$obj->int_celular,$obj->int_direccion,$obj->int_ci,$obj->int_ci_exp,  FUNCIONES::get_fecha_latina($obj->int_fecha_nacimiento));
        $internos->siguiente();
    }
    $data = array(
        'type' => 'success',
        'titulo' => 'Listado de Clientes',
        'info' => null,
        'modulo' => '',
        'head' => array('NOMBRE', 'APELLIDO','CODIGO','UNIFICAR CON','RESERVAS','VENTAS', 'MAIL', 'TELEFONO', 'CELULAR', 'DIRECCION', 'CI', 'EXP', 'FECHA NAC'),
        'result' => $result,
        'foot' => null
    );
    REPORTE::excel($data);
}
