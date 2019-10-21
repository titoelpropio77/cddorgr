<?php
	
	require_once('exportarpagos_banco.class.php');
	
	$para = new EXPORTARPAGOS_BANCO();
	
	if($_GET['tarea']<>"")
	{
		if(!($para->verificar_permisos($_GET['tarea'])))
		{
			?>
			<script>
				location.href="log_out.php";
			</script>
			<?php
		}
	}
	
	switch ($_GET['tarea'])
	{
		case 'AGREGAR':{
						
					

					if($para->datos())
					{
						if($_GET['acc']=='exportar_pagos_banco')
							$para->insertar_tcp();
						else
							$para->vista_previa();
					}
					else 
					{
						$para->formulario_tcp('blanco');
					}
						
						
						break;}
		case 'VER':{
						$para->ver($_GET['id']);
												
						//$para->formulario_tcp('ver');
						
						break;}
		
											
		default: $para->dibujar_busqueda();
	}
?>