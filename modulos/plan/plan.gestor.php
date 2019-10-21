<?php
	
	require_once('plan.class.php');
	
	$plan = new PLAN();
	
	if($_GET['tarea']<>"")
	{
		if(!($plan->verificar_permisos($_GET['tarea'])))
		{
			?>
			<script>
				location.href="log_out.php";
			</script>
			<?php
		}
	}
	
	switch ($_GET['tarea'])
	{				case 'ACCEDER':{							$plan->cuentas();							break;					}
	}
		
?>