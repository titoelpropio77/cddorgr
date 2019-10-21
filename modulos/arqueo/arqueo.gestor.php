<?php
	
	require_once('arqueo.class.php');
	
	$arqueo = new ARQUEO();
	
	if($_GET['tarea']<>"")
	{
		if(!($arqueo->verificar_permisos($_GET['tarea'])))
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
							$arqueo->emergente();
						}
						else
						{
							if($arqueo->datos())
							{
								$arqueo->insertar_tcp();
							}
							else 
							{
								$arqueo->formulario_tcp('blanco');
							}
						}
						
						
						break;}
		case 'VER':{
						$arqueo->cargar_datos();
												
						$arqueo->formulario_tcp('ver');
						
						break;}
		case 'MODIFICAR':{
							
							if($arqueo->datos())
							{
								$arqueo->modificar_tcp();
							}
							else 
							{
								if(!($_POST))
								{
									$arqueo->cargar_datos();
								}
								$arqueo->formulario_tcp('cargar');
							}
							
						break;}
	case 'IMPRIMIR':{

						//$arqueo->imprimir();
						$arqueo->nota_comprobante($_GET['id']);

						break;}	
	
	case 'ANULAR':{

							$arqueo->anular();

						break;}
		
		default: $arqueo->dibujar_busqueda();break;
	}
		
?>