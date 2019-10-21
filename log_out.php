<?php 

require_once('config/database.conf.php');

require_once('clases/adodb/adodb.inc.php');

require_once('clases/usuario.class.php');

require_once('clases/coneccion.class.php');

require_once('config/constantes.php');



$usuario=new USUARIO;

	

if($usuario->get_aut()==_sesion)

{

	

	require_once('clases/usuario.class.php');

	require_once('config/zona_horaria.php');

	$usuario=new USUARIO();

	

	$usuario->cerrar_sesion();	

	

	//header("location:index.php");
	?>
    <script>
    location.href="index.php";
    </script>
    <?php

}

else

{

	header('location:index.php?aut=x');		

}

	

?>