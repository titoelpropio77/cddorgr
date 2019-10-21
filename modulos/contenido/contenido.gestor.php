<?php
	
	require_once('contenido.class.php');
	
	$contenido = new CONTENIDO();
	
	if(!($contenido->verificar_permisos($_GET['tarea'])))
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
						
							if($contenido->datos())
							{
								$contenido->insertar_tcp();
							}
							else 
							{
								$contenido->formulario_tcp('blanco');
							}
					
						
						
						break;}
		case 'VER':{
						$contenido->cargar_datos();
												
						$contenido->formulario_tcp('ver');
						
						break;}
		case 'MODIFICAR':{
							if($_GET['acc']=='Imagen')
							{
								$contenido->eliminar_imagen();
							}
							else
							{
								if($contenido->datos())
								{
									$contenido->modificar_tcp();
								}
								else 
								{
									if(!($_POST))
									{
										$contenido->cargar_datos();
									}
									$contenido->formulario_tcp('cargar');
								}
							}
						break;}
		case 'ELIMINAR':{
							
								if(isset($_POST['cli_id']))
								{
									if(trim($_POST['cli_id'])<>"")
									{
										$contenido->eliminar_tcp();
									}
									else 
									{
										$contenido->dibujar_busqueda();
									}
								}
								else 
								{
									$contenido->formulario_confirmar_eliminacion();
								}
							
							
						break;}
		case 'ACCEDER':{	
						$contenido->dibujar_busqueda();
						break;
						}
		
	}
		
?>