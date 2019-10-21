<?php
	
	require_once('plantilla.class.php');
	
	$comprobante = new PLANTILLA();
	
	if($_GET['tarea']<>"")
	{
		if(!($comprobante->verificar_permisos($_GET['tarea'])))
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
		                                   			  
						
							if($comprobante->datos())
							{
								$comprobante->insertar_tcp();
							}
							else 
							{
								$comprobante->formulario_tcp('blanco');
							}
					
					
						
						break;}
		case 'VER':{
						$comprobante->ver();
														
						break;}
						
						
						
						
	case 'MODIFICAR':{
						if($comprobante->datos())
						{
							$comprobante->modificar_tcp();
						}
						else 
						{
							if(!($_POST))
							{
								$comprobante->cargar_datos();
							}
							$comprobante->formulario_tcp('cargar');
						}
						break;}		
						
						
						
		case 'ELIMINAR':{

							$comprobante->eliminar();

						break;}
		
		default: {
				
				if($comprobante->verificar_permisos('ACCEDER'))
				{
					$comprobante->dibujar_busqueda();
				}
				else
				{
					?>
					<script>
						location.href="log_out.php";
					</script>
					<?php
				}
				
				break;
		}
	}
		
?>