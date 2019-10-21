<?php
	
	require_once('backup.class.php');
	
	$backup = new BACKUP();
	
	if(!($backup->verificar_permisos($_GET['tarea'])))
	{
		?>
		<script>
			location.href="log_out.php";
		</script>
		<?php
	}
	
	switch ($_GET['tarea'])
	{
		case 'ACCEDER':{
							$backup->realizar_backup();
							break;
			
		}
	}
?>