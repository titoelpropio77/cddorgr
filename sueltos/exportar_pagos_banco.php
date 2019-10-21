<?php
	header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="'.date('Y-m-d').'.xls"');
	header("Pragma: no-cache");
	header("Expires: 0");
	
	//echo date("l");
    //echo "\r\n";
	
	require_once('../config/database.conf.php');
	mysql_connect(_SERVIDOR_BASE_DE_DATOS,_USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or die("Could not connect: " . mysql_error());
	mysql_select_db(_BASE_DE_DATOS);

			?>
                <table border="1" bordercolor="#CCCCCC">
                <tr>
                    <td><b>Codigo</b></td>
                    <td><b>Nombre Depositante</b></td>
                    <td><b>Fecha Vencimiento</b></td>
					<td><b>Importe</b></td>
					<td><b>Mora</b></td>
					<td><b>Aviso</b></td>
					<td><b>Aviso</b></td>
                </tr>
				<?php
				
				if($_POST['check_ventas']=='Si')
				{
					$sql="SELECT *
					FROM 
					venta 
					where ven_estado='Pendiente'";
				}
				else
				{
					$sql="SELECT *
					FROM 
					venta 
					where ven_id in (".$_POST['venta'].") and ven_estado='Pendiente'";
				}

				$result = mysql_query($sql);
			
				$num=mysql_num_rows($result);
				
				for($i=0;$i<$num;$i++)
				{
					$objeto=mysql_fetch_object($result);
					
					if($_POST['check_cuotas']=='Si')
					{
						$sql2="SELECT *
						FROM 
						interno_deuda
						inner join interno on (ind_int_id=int_id) 
						where ind_estado='Pendiente' and ind_tabla='venta' and ind_tabla_id=".$objeto->ven_id." ORDER BY ind_id ASC";
					}
					else
					{
						//$ultimo_exportado=obtener_ultimo_exportado($objeto->ven_id);
						
						/*if($ultimo_exportado<>0)
						{
							$ultimo_exportado=$ultimo_exportado-1;
						}
						*/
						
						$sql2="SELECT *
						FROM 
						interno_deuda
						inner join interno on (ind_int_id=int_id) 
						where ind_estado='Pendiente' and ind_tabla='venta' and ind_tabla_id=".$objeto->ven_id." ORDER BY ind_id ASC LIMIT 0,".$_POST['cuota'];
					}
					
					
					$result2 = mysql_query($sql2);
				
					$num2=mysql_num_rows($result2);
					
					for($j=0;$j<$num2;$j++)
					{
						$objeto2=mysql_fetch_object($result2);
						
						$ind_id=$objeto2->ind_id;
					
					?>
					
                    <tr>
                        <td><?php echo generar_codigo($objeto->ven_lot_id); ?></td>
                        <td>
						<?php
						
						if(strlen(trim($objeto2->int_nombre.' '.$objeto2->int_apellido))>40)
						{
							echo substr(utf8_encode(trim($objeto2->int_nombre.' '.$objeto2->int_apellido)), 0,40);
						}
						else
						{
							echo utf8_encode(trim($objeto2->int_nombre.' '.$objeto2->int_apellido));
						}

						//echo strtoupper($objeto2->int_nombre.' '.$objeto2->int_apellido);
						//if($objeto->ven_co_propietario<>0)
							//echo ' / '.strtoupper(nombre_interno($objeto->ven_co_propietario));
						?>
                        </td>
                        <td><?php echo substr($objeto2->ind_fecha_programada,0,4).substr($objeto2->ind_fecha_programada,5,2).substr($objeto2->ind_fecha_programada,8,2); ?></td>
                        <td><?php echo $objeto2->ind_monto; ?></td>
                        <td><?php echo $objeto->ven_codigo; ?></td>
                        <td>0</td>
						<td><?php echo $objeto2->ind_num_correlativo; ?></td>
                    </tr>
					<?php
					
						/*
						$sql_update="UPDATE 
						interno_deuda
						set ind_expbanco='Si' 
						where ind_id=$ind_id";
						
						$result3 = mysql_query($sql_update);
						*/
					}
				}
				
				if($_POST['check_ventas']=='Si')
				{
					$ventas='Todas';
				}
				else
				{
					$ventas=$_POST['venta'];
				}
				
				if($_POST['check_cuotas']=='Si')
				{
					$cuotas='Todas';
				}
				else
				{
					$cuotas=$_POST['cuota'];
				}
				
				$sql="insert into exportacion_banco (expban_titulo,expban_ventas,expban_cuotas,expban_fecha) values ('".$_POST['titulo']."','".$ventas."','".$cuotas."','".date('Y-m-d')."')";
			
				$result = mysql_query($sql);
				?>
                </table>
                <?php

	function nombre_interno($int_id)
	{
				
		$sql="select int_nombre,int_apellido from interno where int_id=$int_id";
		
		$result = mysql_query($sql);
		
		$objeto=mysql_fetch_object($result);
		
		return $objeto->int_nombre.' '.$objeto->int_apellido;
	}
	
	function obtener_ultimo_exportado($venta)
	{
				
		$sql="select ind_id,ind_expbanco from interno_deuda where ind_tabla='venta' and ind_expbanco='Si' ORDER BY ind_id DESC LIMIT 0,1";
		
		$result = mysql_query($sql);
		
		$num=mysql_num_rows($result);
		
		if($num>0)
		{
			$objeto=mysql_fetch_object($result);
		
			return $objeto->ind_id;
		}
		else
		{
			/*
			$sql="select ind_id,ind_expbanco from interno_deuda where ind_tabla='venta' ORDER BY ind_id ASC LIMIT 0,1";
			
			$result = mysql_query($sql);
		
			$num=mysql_num_rows($result);
			
			if($num>0)
			{
				$objeto=mysql_fetch_object($result);
			
				return $objeto->ind_id;
			}
			*/
			return 0;
		}	
	}
	
	function generar_codigo($id_lote)
	{			
		$sql="select urb_nombre,man_nro,lot_nro,zon_nombre,uv_nombre 
		from 
		lote
		inner join zona on (lot_id='".$id_lote."' and lot_zon_id=zon_id)
		inner join uv on (lot_uv_id=uv_id)	
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on(man_urb_id=urb_id)";
		
		$result = mysql_query($sql);
		
		$objeto=mysql_fetch_object($result);
		
		$man_nro=$objeto->man_nro;
		
		if(strlen($man_nro)==1)
			$man_nro='0'.$man_nro;
		
		$lot_nro=$objeto->lot_nro;
		
		if(strlen($lot_nro)==1)
			$lot_nro='0'.$lot_nro;
		
		return 'A'.trim($man_nro).trim($lot_nro);
	}
?>