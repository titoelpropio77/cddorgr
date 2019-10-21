<?php
	
	require_once('centrocosto.class.php');
	
	$centro = new CENTROCOSTO();
	
	if($_GET['tarea']<>"")
	{
		if(!($centro->verificar_permisos($_GET['tarea'])))
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
						if($_GET['acc']=='Emergente')
						{
							$centro->emergente();
						}
						else
						{
							if($centro->datos())
							{
								$centro->insertar_tcp();
							}
							else 
							{
								$centro->formulario_tcp('blanco');
							}
						}
						
						
						break;}
		case 'VER':{
						$centro->cargar_datos();
												
						$centro->formulario_tcp('ver');
						
						break;}
		case 'MODIFICAR':{
							
							if($centro->datos())
							{
								$centro->modificar_tcp();
							}
							else 
							{
								if(!($_POST))
								{
									$centro->cargar_datos();
								}
								$centro->formulario_tcp('cargar');
							}
							
						break;}
		case 'ELIMINAR':{
							
								if(isset($_POST['cco_id']))
								{
									if(trim($_POST['cco_id'])<>"")
									{
										$centro->eliminar_tcp();
									}
									else 
									{
										$centro->dibujar_busqueda();
									}
								}
								else 
								{
									$centro->formulario_confirmar_eliminacion();
								}
							
							
						break;}
		
		default: $centro->dibujar_busqueda();break;
	}
		
?>