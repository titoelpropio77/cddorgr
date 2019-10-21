<?php
	
	require_once('grupo.class.php');
	
	$grupo = new GRUPO();
	
	if(!($grupo->verificar_permisos($_GET['tarea'])))
	{
		?>
		<script>
			location.href="log_out.php";
		</script>
		<?php
	}
	
	switch ($_GET['tarea'])
	{
		case 'AGREGAR':{
							if($grupo->datos())
							{
								$grupo->insertar_tcp();
							}
							else 
							{
								$grupo->formulario_tcp('blanco');
							}
							break;
		}
		case 'VER':{
							$grupo->cargar_datos();
													
							$grupo->formulario_tcp('ver');
							
							break;
		}
		case 'MODIFICAR':{
							if($grupo->datos())
							{
								$grupo->modificar_tcp();
							}
							else 
							{
								if(!($_POST))
								{
									$grupo->cargar_datos();
								}
								$grupo->formulario_tcp('cargar');
							}
							break;
		}
		case 'ELIMINAR':{
							
							$grupo->eliminar_tcp();
								
							break;
		}
		case 'ACCEDER':{
							$grupo->dibujar_busqueda();
							break;
		}
	}
?>