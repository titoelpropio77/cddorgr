<?php
	
	require_once('plancuenta.class.php');
	
	$cuen = new CUENTA();
	
	if($_GET['tarea']<>"")
	{
		if(!($cuen->verificar_permisos($_GET['tarea'])))
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
							if($cuen->datos())
							{
								$cuen->insertar_tcp();
							}
							else 
							{
							     $cuen->dibujar();
								//$cuen->formulario_tcp('blanco');
							}					
						
						break;
					   }
		
		case 'MODIFICAR':{
							
							if($cuen->datos())
							{
								$cuen->modificar_tcp();
							}
							else 
							{
								if(!($_POST))
								{
									$cuen->cargar_datos();
								}
								$cuen->formulario_tcp('cargar');
							}
							
						break;}
		case 'ELIMINAR':{
							
								
							$cuen->eliminar_tcp();
								
							
							
						break;}
		
		default: $cuen->dibujar();break;
	}
		
?>