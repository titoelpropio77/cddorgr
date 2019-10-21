<?php
//Ejecuta cada 10 minutos
require_once('mysql.php');
require_once('funciones.php');
date_default_timezone_set("America/La_Paz");
FUNCION::bd_query("truncate venta_cobro");

?>