<link href="../css/estilos.css" rel="stylesheet" type="text/css">
<?php

$persona=$_GET['id'];

$internos="";


require_once('mysql.php');

		$query= new QUERY();
		
		$sql = "select soc_fecha_ingreso,per_nombre,per_apellido,per_fecha_nacimiento
		from socio inner join gr_persona on (soc_per_id='$persona' and soc_per_id=per_id)"; 

		$query->consulta($sql);

		list($per_fecha_ingreso,$per_nombre,$per_apellido,$per_fecha_nacimiento)=$query->valores_fila();
		
		?>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				   <tr>
				    <td  width="100%">
					<strong><center><?PHP echo utf8_encode('DATOS DEL CUMPLEAÑERO');?></center></strong><br><br>
					<strong>Nombre Completo: </strong> <?php echo utf8_encode($per_nombre.' '.$per_apellido);?> <br><br>
					<strong>Fecha de Nacimiento: </strong> <?php if($per_fecha_nacimiento <> '0000-00-00') echo date('d/m/Y',strtotime($per_fecha_nacimiento));?> <br><br>
					<strong>Fecha de Ingreso: </strong> <?php if($per_fecha_ingreso <> '0000-00-00') echo date('d/m/Y',strtotime($per_fecha_ingreso));?> <br><br>
					<?php
					internos($internos,$query,$persona);
					?>
					<strong>Interno: </strong> <?echo $internos;?> <br>
					</td>
				  </tr>
				 
			</table>
	
		<?php
		
function internos(&$internos,$query,$persona)
{
	$query= new QUERY();
	
	$sql="select int_numero from gr_persona inner join socio on(per_id='$persona' and per_id=soc_per_id)
	inner join interno on (soc_id=int_soc_id)
	";
	
	$query->consulta($sql);

	$num=$query->num_registros();
	
	for($i=0;$i<$num;$i++)
	{
		list($int_numero)=$query->valores_fila();
		
		if($i==0)
		{
			$internos.=$int_numero;
		}
		else
		{
			$internos.=', '.$int_numero;
		}
	}
		
}	
?>

