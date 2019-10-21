<link href="../css/estilos.css" rel="stylesheet" type="text/css">
<?php
require_once('mysql.php');
require("../clases/mytime_int.php");

		$query= new QUERY();
		
		$sql = "select 
				ven_id,ven_fecha,ven_tipo_cambio,ven_usu_id,ven_tipo,ven_moneda,ven_monto,int_numero,per_nombre,per_apellido 
				from 
				venta inner join interno on(ven_id='".$_GET['id']."' and ven_int_id=int_id)
				inner join socio on (int_soc_id=soc_id)
				inner join gr_persona on (soc_per_id=per_id)
				"; 

		$query->consulta($sql);

		list($ven_id,$ven_fecha,$tc,$ven_usu_id,$ven_tipo,$ven_moneda,$monto,$int_numero,$per_nombre,$per_apellido)=$query->valores_fila();
		
		$myday = setear_fecha(strtotime($ven_fecha));
		
		?>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				   <tr>
				    <td  width="35%">
				    <strong>Nro de Venta: </strong> <?php echo $ven_id;?> <br>
					<strong>Socio: </strong> <?php echo $per_nombre.' '.$per_apellido;?> <br>
					<strong>Interno: </strong> <?php echo $int_numero;?> <br>
					<strong>Tipo: </strong> <?php echo $ven_tipo;?> <br>
					<strong>Moneda: </strong> <?php if($ven_moneda=="1") echo "Bolivianos"; else echo "Dolares";?> <br><br>
					</td>
					<td>
					<center><h3>DETALLE DE VENTA</h3></center>
					</td>
				    <td align="right" width="35%">
					<strong>Fecha: </strong> <?php echo $myday;?> <br>
					<strong>Tipo de Cambio: </strong> <?php echo $tc;?> <br>
					<strong>Usuario: </strong> <?php echo nombre_persona($ven_usu_id);?> <br>
					</td>
				  </tr>
				 
			</table>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Producto</th>
					<th>Precio</th>
					<th>Cantidad</th>
					<th class="tOpciones" >Bs</th>
					<th class="tOpciones" >$us</th>
				</tr>		
				</thead>
				<tbody>
		<?php
		
		$sql = "select 
				pro_nombre,vde_cantidad,vde_precio,vde_moneda
				from 
				venta_detalle inner join producto on(vde_ven_id='".$_GET['id']."' and vde_pro_id=pro_id)
				"; 
		//echo $sql;
		
		$query->consulta($sql);
		
		$num=$query->num_registros();
		
		for($i=0;$i<$num;$i++)
		{
			list($pro_nombre,$vde_cantidad,$vde_precio,$vde_moneda)=$query->valores_fila();

			echo '<tr>';
									
					$precio=$vde_precio;
					$cantidad=$vde_cantidad;
					
					echo "<td>";
						echo $pro_nombre;
					echo "</td>";
					
					if($vde_moneda=='1')
					{
						$moneda='Bs ';
						$bs=$precio*$cantidad;
						$sus=round(($bs/$tc),2);
					}
					else
					{	
						$moneda='$us ';
						$bs=$cantidad*$precio*$tc;
						$sus=$cantidad*$precio;
					}
					
					echo "<td>";
						echo $moneda.' '.$precio;
					echo "</td>";
					
					echo "<td>";
						echo $cantidad;
					echo "</td>";
					
					echo "<td>";
						echo $bs;
					echo "</td>";
					
					echo "<td>";
						echo $sus;
					echo "</td>";
					
				echo "</tr>";
			
			
		}
		?>
		<tr>
					<td class="">
						&nbsp;
					</td>
					<td class="">
						&nbsp;
					</td>
					<td class="">
						&nbsp;
					</td>
					<td class="">
						<b><?php echo $monto;?></b>
					</td>
					<td class="">
						<b><?php echo round(($monto/$tc),2);?></b>
					</td>
				</tr>
		<?php		
		///
		echo "</tbody></table></center><br>";



function nombre_persona($usuario)
{
	$query= new QUERY();
	
	$sql="select per_nombre,per_apellido from ad_usuario inner join gr_persona on (usu_id='$usuario' and usu_per_id=per_id)";
	
	$query->consulta($sql);

	list($per_nombre,$per_apellido)=$query->valores_fila();
	
	return $per_nombre.' '.$per_apellido; 
		
}	
?>

