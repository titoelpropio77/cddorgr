<?php
	
	require_once('obra.class.php');
	
	$obra = new OBRA();
	
	if(!($obra->verificar_permisos($_GET['tarea'])))
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
						
							if($obra->datos())
							{
								$obra->insertar_tcp();
							}
							else 
							{
								$obra->formulario_tcp('blanco');
							}
					
						
						
						break;}
		case 'VER':{
						$obra->cargar_datos();
												
						$obra->formulario_tcp('ver');
						
						break;}
		case 'MODIFICAR':{
							if($_GET['acc']=='Imagen')
							{
								$obra->eliminar_imagen();
							}
							else
							{
								if($obra->datos())
								{
									$obra->modificar_tcp();
								}
								else 
								{
									if(!($_POST))
									{
										$obra->cargar_datos();
									}
									$obra->formulario_tcp('cargar');
								}
							}
						break;}
		case 'ELIMINAR':{
							
								if(isset($_POST['obr_id']))
								{
									if(trim($_POST['obr_id'])<>"")
									{
										$obra->eliminar_tcp();
									}
									else 
									{
										$obra->dibujar_busqueda();
									}
								}
								else 
								{
									$obra->formulario_confirmar_eliminacion();
								}
							
							
						break;}
		case 'ACCEDER':{	
						$obra->dibujar_busqueda();
						break;
						}
		
	}
		
?>