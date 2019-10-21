<?php
	
	require_once('log.class.php');
	
	$log = new LOG();
	
	if($_GET['tarea']<>"")
	{
		if(!($log->verificar_permisos($_GET['tarea'])))
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
	
							
					
							
		default: $log->dibujar_busqueda();break;
							
							
					
		
		
	}
		
?>