<?php
	
	require_once('banner.class.php');
	
	$banner = new BANNER();
	
	if(!($banner->verificar_permisos($_GET['tarea'])))
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
						
							if($banner->datos())
							{
								$banner->insertar_tcp();
							}
							else 
							{
								$banner->formulario_tcp('blanco');
							}
					
						
						
						break;}
		case 'VER':{
						$banner->cargar_datos();
												
						$banner->formulario_tcp('ver');
						
						break;}
		case 'MODIFICAR':{
							if($_GET['acc']=='Imagen')
							{
								$banner->eliminar_imagen();
							}
							else
							{
								if($banner->datos())
								{
									$banner->modificar_tcp();
								}
								else 
								{
									if(!($_POST))
									{
										$banner->cargar_datos();
									}
									$banner->formulario_tcp('cargar');
								}
							}
						break;}
		case 'ELIMINAR':{
							
								if(isset($_POST['ser_id']))
								{
									if(trim($_POST['ser_id'])<>"")
									{
										$banner->eliminar_tcp();
									}
									else 
									{
										$banner->dibujar_busqueda();
									}
								}
								else 
								{
									$banner->formulario_confirmar_eliminacion();
								}
							
							
						break;}
		case 'ACCEDER':{	
						
						if($_GET['acc']<>"")
						{
							$banner->orden($_GET['tec'],$_GET['acc'],$_GET['or']);
						}
						$banner->dibujar_busqueda();
						break;
						}
		
	}
		
?>