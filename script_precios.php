<?php
require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

$sql_sel="select distinct ven_fecha,zon_urb_id,zon_id,zon_nombre,ven_metro from venta 
            inner join lote on (ven_lot_id=lot_id)
            inner join zona on (lot_zon_ant=zon_id)
            where  ven_fecha<='2016-03-30' 
            order by zon_id asc, ven_fecha desc;";

$listado=FUNCTIONES::lista_bd_sql($sql_sel);
