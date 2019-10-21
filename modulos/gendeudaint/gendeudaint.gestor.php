<?php
	
	require_once('gendeudaint.class.php');
	
	$gendeudaint = new GENDEUDAINT();
	
	if($_GET['tarea']<>"")
	{
		if(!($gendeudaint->verificar_permisos($_GET['tarea'])))
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
						
						if($gendeudaint->datos())
						{
							$gendeudaint->insertar_tcp();
						}
						else 
						{
							$gendeudaint->formulario_tcp('blanco');
						}
						
						
						
						break;}
			case 'ANULAR':{

							$gendeudaint->anular();

						break;}
		
		default: $gendeudaint->dibujar_busqueda();break;
	}
		
?>