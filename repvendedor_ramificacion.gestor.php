<?php

ini_set('display_errors', 'On');

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
require_once('config/variables.php');
require_once ("clases/Ticket.php");
require_once('repvendedor_ramificacion/repvendedor_ramificacion.class.php');

$est = new REPVENDEDOR_RAMIFICACION();
$est->dibujar_busqueda();