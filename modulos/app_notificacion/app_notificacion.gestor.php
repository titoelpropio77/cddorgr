<?php
	
	require_once('app_notificacion.class.php');
	
	$textos = new NOTIFICACION();
	
	if(!($textos->verificar_permisos($_GET['tarea'])))
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
			if($textos->datos())
			{
				$textos->insertar_tcp();
			}
			else 
			{
				$textos->formulario_tcp('blanco');
			}	
			break;
		}
		case 'VER':{
			$textos->cargar_datos();
			$textos->formulario_tcp('ver');
			break;
		}
		case 'MODIFICAR':{
			if($_GET['acc']=='Imagen')
			{
				$textos->eliminar_imagen();
			}
			else
			{
				if($textos->datos())
				{
					$textos->modificar_tcp();
				}
				else 
				{
					if(!($_POST))
					{
						$textos->cargar_datos();
					}
					$textos->formulario_tcp('cargar');
				}
			}
		break;
		}
		case 'NOTIFICACION PUSH':{
			if($_POST['confirmar']=="ok"){
				$textos->notificacion_enviar();
			} else {
				$textos->notificacion_preguntar();
			} 
			break;
		}
		case 'ELIMINAR':{
							
			if(isset($_POST['not_id']))
			{
				if(trim($_POST['not_id'])<>"")
				{
					$textos->eliminar_tcp();
				}
				else 
				{
					$textos->dibujar_busqueda();
				}
			}
			else 
			{
				$textos->formulario_confirmar_eliminacion();
			}	
		break;
		}
		case 'ACCEDER':{		
			$textos->dibujar_busqueda();
			break;
		}
		
	}
		
?>