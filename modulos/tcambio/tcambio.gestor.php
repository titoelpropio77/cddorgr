<?php
	
	require_once('tcambio.class.php');
	
	$tcambio = new TCAMBIO();
	
	if($_GET['tarea']<>"")
	{
		if(!($tcambio->verificar_permisos($_GET['tarea'])))
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
							$tcambio->emergente();
						}
						else
						{
							if($tcambio->datos())
							{
								$tcambio->insertar_tcp();
							}
							else 
							{
								$tcambio->formulario_tcp('blanco');
							}
						}
						
						
						break;}
		case 'VER':{
						$tcambio->cargar_datos();
												
						$tcambio->formulario_tcp('ver');
						
						break;}
		case 'MODIFICAR':{
							
							if($tcambio->datos())
							{
								$tcambio->modificar_tcp();
							}
							else 
							{
								if(!($_POST))
								{
									$tcambio->cargar_datos();
								}
								$tcambio->formulario_tcp('cargar');
							}
							
						break;}
		case 'ELIMINAR':{
							
								if(isset($_POST['tca_id']))
								{
									if(trim($_POST['tca_id'])<>"")
									{
										$tcambio->eliminar_tcp();
									}
									else 
									{
										$tcambio->dibujar_busqueda();
									}
								}
								else 
								{
									$tcambio->formulario_confirmar_eliminacion();
								}
							
							
						break;}
		
		default: $tcambio->dibujar_busqueda();break;
	}
		
?>