<?php
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');
require_once("clases/mytime_int.php");
require_once('config/constantes.php');
require_once("clases/Ticket.php");
require_once("clases/mlm.class.php");
require_once('clases/comisiones.class.php');

function procesar(){
    $sql = "select * from venta
            where ven_multinivel='si'
            and (ven_monto-ven_res_anticipo)!=ven_monto";
    
    $ventas = FUNCIONES::lista_bd_sql($sql);
    
    foreach ($ventas as $venta) {
        $sql = "selec * from interno_deuda where ind_tabla'venta'
            and ind_tabla_id=$venta->ven_id";
    }
}
?>