<?php
	
	require_once('clientes.class.php');
	
	$clientes = new CLIENTES();
	
	if(!($clientes->verificar_permisos($_GET['tarea'])))
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
						
							if($clientes->datos())
							{
								$clientes->insertar_tcp();
							}
							else 
							{
								$clientes->formulario_tcp('blanco');
							}
					
						
						
						break;}
		case 'VER':{
						$clientes->cargar_datos();
												
						$clientes->formulario_tcp('ver');
						
						break;}
		case 'MODIFICAR':{
							if($_GET['acc']=='Imagen')
							{
								$clientes->eliminar_imagen();
							}
							else
							{
								if($clientes->datos())
								{
									$clientes->modificar_tcp();
								}
								else 
								{
									if(!($_POST))
									{
										$clientes->cargar_datos();
									}
									$clientes->formulario_tcp('cargar');
								}
							}
						break;}
		case 'ELIMINAR':{
							
								if(isset($_POST['cli_id']))
								{
									if(trim($_POST['cli_id'])<>"")
									{
										$clientes->eliminar_tcp();
									}
									else 
									{
										$clientes->dibujar_busqueda();
									}
								}
								else 
								{
									$clientes->formulario_confirmar_eliminacion();
								}
							
							
						break;}
		case 'ACCEDER':{	
						$clientes->dibujar_busqueda();
						break;
						}
		
	}
		
?>