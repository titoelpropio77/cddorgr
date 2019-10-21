<?php
	
	require_once('oferta.class.php');
	
	$oferta = new OFERTA();
	
	if(!($oferta->verificar_permisos($_GET['tarea'])))
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
						
							if($oferta->datos())
							{
								$oferta->insertar_tcp();
							}
							else 
							{
								$oferta->formulario_tcp('blanco');
							}
					
						
						
						break;}
		case 'VER':{
						$oferta->cargar_datos();
												
						$oferta->formulario_tcp('ver');
						
						break;}
		case 'MODIFICAR':{
							if($_GET['acc']=='Imagen')
							{
								$oferta->eliminar_imagen();
							}
							else
							{
								if($oferta->datos())
								{
									$oferta->modificar_tcp();
								}
								else 
								{
									if(!($_POST))
									{
										$oferta->cargar_datos();
									}
									$oferta->formulario_tcp('cargar');
								}
							}
						break;}
		case 'ELIMINAR':{
							
								if(isset($_POST['ofe_id']))
								{
									if(trim($_POST['ofe_id'])<>"")
									{
										$oferta->eliminar_tcp();
									}
									else 
									{
										$oferta->dibujar_busqueda();
									}
								}
								else 
								{
									$oferta->formulario_confirmar_eliminacion();
								}
							
							
						break;}
		case 'ACCEDER':{	
						
						if($_GET['acc']<>"")
						{
							$oferta->orden($_GET['tec'],$_GET['acc'],$_GET['or']);
						}
						$oferta->dibujar_busqueda();
						break;
						}
		
	}
		
?>