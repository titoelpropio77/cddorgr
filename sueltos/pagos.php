<link href="../css/estilos.css" rel="stylesheet" type="text/css">
<?php
require_once('mysql.php');
require("../clases/mytime_int.php");

$query= new QUERY();
		
$sql="SELECT 
		ven_monto,ven_id,ven_fecha,ven_tipo_cambio,ven_tipo,ven_moneda,ven_estado,int_numero,per_nombre,per_apellido,ven_usu_id 
		FROM 
		venta inner join interno on (ven_id='".$_GET['id']."' and ven_int_id=int_id)
		inner join socio on (int_soc_id=soc_id)
		inner join gr_persona on (soc_per_id=per_id)
		";

$query->consulta($sql);

list($ven_monto,$ven_id,$ven_fecha,$ven_tipo_cambio,$ven_tipo,$ven_moneda,$ven_estado,$int_numero,$per_nombre,$per_apellido,$ven_usu_id )=$query->valores_fila();
$myday = setear_fecha(strtotime($ven_fecha));
if($ven_moneda=='1')
{
	$monto=$ven_monto;
	$moneda="Bs";
}
else
{
	$monto=round(($ven_monto/$ven_tipo_cambio),2);
	$moneda='$us';
}	
?>
<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
	
	   <tr>
		<td width="35%">
		<strong>Nro Venta: </strong> <?php echo $_GET['id'];?> <br>
		<strong>Socio: </strong> <?php echo $per_nombre.' '.$per_apellido;?> <br>
		<strong>Interno: </strong> <?php echo $int_numero;?> <br>
		<strong>Tipo: </strong> <?php echo $ven_tipo;?> <br>
		
		</td>
		<td>
		<center><h3>PAGOS DE VENTA</h3></center>
		</td>
		<td align="right" width="35%">
		<strong>Fecha: </strong> <?php echo $myday;?> <br>
		<strong>Tipo de Cambio: </strong> <?php echo $ven_tipo_cambio;?> <br>
		<strong>Usuario: </strong> <?php echo nombre_persona($ven_usu_id);?> <br>
		</td>
	  </tr>
	 
</table>
<?

		
$sql = "select vpa_monto,vpa_moneda,vpa_tipo_cambio,vpa_fecha from venta_pago where vpa_ven_id='".$_GET['id']."'"; 

$query->consulta($sql);

$num=$query->num_registros();

if($num>0)
{
	?>
	
	<br><center><table width="100%" class="tablaReporte" cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>Fecha</th>
		<th>Tipo de Cambio</th>
		<th>Moneda</th>
		<th class="tOpciones" >Bs</th>
		<th class="tOpciones" >$us</th>
	</tr>
	</thead>
	<?php
	$tbol=0;
	$tsus=0;
	for($i=0;$i<$num;$i++)
	{
		list($vpa_monto,$vpa_moneda,$vpa_tipo_cambio,$vpa_fecha)=$query->valores_fila();
		?>
		<tr>
			<td><?php echo date('d/m/Y',strtotime($vpa_fecha));?></td>
			<td><?php echo $vpa_tipo_cambio;?></td>
		<td><?php if($vpa_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
		<?
		if($vpa_moneda=="1")
		{
			$psus=($vpa_monto/$vpa_tipo_cambio);
			$tbol+=$vpa_monto;
			$tsus+=$psus;
			?>
			<td><?php echo $vpa_monto;?></td>
			<td><?php echo round($psus,2);?></td>
			<?php
		}
		else
		{
			$pbs=($vpa_monto*$vpa_tipo_cambio);
			$tsus+=$vpa_monto;
			$tbol+=$pbs;
			
			?>
			<td><?php echo $pbs;?></td>
			<td><?php echo $vpa_monto;?></td>
			<?php
		}
		?>	
			
			
			
		</tr>
		<?php		
	}
	?>
	
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><b><?php echo $tbol;?></b></td>
		<td><b><?php echo $tsus;?></b></td>
	</tr>
	
	</table></center><br>
	<?php
	if($ven_moneda=='1')
	{
		$saldo=$monto-$tbol;
		$pagado=$tbol;
	}
	else
	{
		$saldo=$monto-$tsus;
		$pagado=$tsus;
	}
	?>
	<strong>Monto: </strong> <?php echo $monto." ".$moneda;?> <br>	
	<strong>Pagado: </strong> <?php echo $pagado." ".$moneda;?> <br>	
	<b>Saldo:</b> <?php echo $saldo.' '.$moneda;?> <br>
	<?php
}
else
{

}

function nombre_persona($usuario)
{
	$query= new QUERY();
	
	$sql="select per_nombre,per_apellido from ad_usuario inner join gr_persona on (usu_id='$usuario' and usu_per_id=per_id)";
	
	$query->consulta($sql);

	list($per_nombre,$per_apellido)=$query->valores_fila();
	
	return $per_nombre.' '.$per_apellido; 
		
}	
?>

