<?php
	
	require_once('divisa.class.php');
	
	$divisa = new DIVISA();
	
	if($_GET['tarea']<>"")
	{
		if(!($divisa->verificar_permisos($_GET['tarea'])))
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
							$divisa->emergente();
						}
						else
						{
							
							if($divisa->datos())
							{
								$divisa->insertar_tcp();
							}
							else 
							{
								$divisa->formulario_tcp('blanco');
							}
						}
						
						break;}
		case 'VER':{
						$divisa->cargar_datos();
												
						$divisa->formulario_tcp('ver');
						
						break;}
		case 'MODIFICAR':{
						if($divisa->datos())
						{
							$divisa->modificar_tcp();
						}
						else 
						{
							if(!($_POST))
							{
								$divisa->cargar_datos();
							}
							$divisa->formulario_tcp('cargar');
						}
						break;}
								
		case 'ELIMINAR':{
						if(isset($_POST['cvd_id']))
						{
							if(trim($_POST['cvd_id'])<>"")
							{
								$divisa->eliminar_tcp();
							}
							else 
							{
								$divisa->dibujar_busqueda();
							}
						}
						else 
						{
							$divisa->formulario_confirmar_eliminacion();
						}
							
						break;}
		
		case 'ANULAR':{
							$divisa->anular();
						break;}
						
		case 'IMPRIMIR':{
							$divisa->ver();
						break;}
						
		default: $divisa->dibujar_busqueda();
	}
	
		
		
	
?>