<?php
	
	require_once('cuentasx.class.php');
	
	$cuentasx = new cuentasx();
	
	if($_GET['tarea']<>"")
	{
		if(!($cuentasx->verificar_permisos($_GET['tarea'])))
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
						
						if($cuentasx->datos())
						{
							$cuentasx->insertar_tcp();
						}
						else 
						{
							$cuentasx->formulario_tcp('blanco');
						}
						
						
						
						break;}
			case 'ANULAR':{

							$cuentasx->anular();

						break;}
			
			case 'CUENTAS':{
						if($_GET['acc']=='ver')
						{
							$cuentasx->mostrar_nota_pago_historial($_GET['cup_id'],$_GET['cup_fecha']);
						}
						else
						{
							if($_GET['acc']=='anular')
							{
								$cuentasx->anular_pago($_GET['cup_id']);
							}
							else
							{
								$cuentasx->cuentas();
							}
						}
						
						break;}
						
			case 'PAGO_INTERES_CUENTASX':{
						if($_GET['acc']=='ver')
						{
							$cuentasx->mostrar_nota_pago_historial_interes($_GET['cupi_id'],$_GET['cupi_fecha']);
						}
						else
						{
							if($_GET['acc']=='anular')
							{
								$cuentasx->anular_pago_interes($_GET['cupi_id']);
							}
							else
							{
								$cuentasx->cuentas_intereses();
							}
						}
						
						break;}
		
		default: $cuentasx->dibujar_busqueda();break;
	}		
?>