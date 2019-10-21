<?php
	
	require_once('galeries.class.php');
	
	$galeria = new GALERIA();
	
	require_once('imagenes.class.php');
	
	$imagenes = new IMAGENES();
		
	if(!($galeria->verificar_permisos($_GET['tarea'])))
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
											
							if($_GET['acc']=='Emergente')
							{
								$galeria->emergente();
							}
							else
							{
								
								if($galeria->datos())
								{
									$galeria->insertar_tcp();
								}
								else 
								{
									$galeria->formulario_tcp('blanco');
								}
							}
						
						break;}
		case 'VER':{
						$galeria->cargar_datos();
												
						$galeria->formulario_tcp('ver');
						
						break;}
		case 'MODIFICAR':{
							
							if($galeria->datos())
							{
								$galeria->modificar_tcp();
							}
							else 
							{
								if(!($_POST))
								{
									$galeria->cargar_datos();
								}
								$galeria->formulario_tcp('cargar');
							}
							
						break;}
		case 'ELIMINAR':{
							
								if(isset($_POST['gal_id']))
								{
									if(trim($_POST['gal_id'])<>"")
									{
										$galeria->eliminar_tcp();
									}
									else 
									{
										$galeria->dibujar_busqueda();
									}
								}
								else 
								{
									$galeria->formulario_confirmar_eliminacion();
								}
							
							
						break;}
						
		case 'ACCEDER':{
							
							$galeria->dibujar_busqueda();
							break;
						}

		case 'FOTOS':{
						
							if($_GET['acc']=='ELIMINAR')
							{
								$imagenes->eliminar_tcp();
							}
							
							if(!($_GET['acc']<>""))
							{
								if($imagenes->datos())
								{
									$imagenes->insertar_tcp();
								}
								else 
								{
									$imagenes->formulario_tcp('blanco');
									
									$imagenes->dibujar_listado();
								}
																
							}
							
							break;
		}
		
		case 'VIDEOS':{
						
							if($_GET['acc']=='ELIMINAR')
							{
								$videos->eliminar_tcp();
							}
							
							if(!($_GET['acc']<>""))
							{
								if($videos->datos())
								{
									$videos->insertar_tcp();
								}
								else 
								{
									$videos->formulario_tcp('blanco');
									
									$videos->dibujar_listado();
								}
																
							}
							
							break;
		}
		
	}
		
?>