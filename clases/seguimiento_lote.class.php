<?php
 class SEGUIMIENTO_LOTE

 {

	function SEGUIMIENTO_LOTE()
	{

		

	}


	//Verificacion del terreno antes de realizar una reserva. Verificamos si el lote esta en algun modulo ya activo, como ser Venta, Bloqueo de Terreno, Reserva.
	function verificar_seguimiento_lote_reserva($lote) 
	{
		$conec=new ADO;
		
		$resultado='';

		$sql="SELECT ven_id FROM venta WHERE ven_lot_id=".$lote." and ven_estado in ('Pendiente','Pagado','Pendiente por Cobrar')";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$ventas='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$ventas=$ventas.$objeto->ven_id;
				else
					$ventas=$ventas.$objeto->ven_id.',';
				
				$conec->siguiente();
			}
		}
		
		if($ventas<>'')
			$resultado=$resultado.'El lote se encuentra en la(s) venta(s) <b>'.$ventas.'</b><br>';
		
		
		
		
		$sql="SELECT res_id FROM reserva_terreno WHERE res_lot_id=".$lote." and res_estado in ('Pendiente','Habilitado')";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$reservas='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$reservas=$reservas.$objeto->res_id;
				else
					$reservas=$reservas.$objeto->res_id.',';
				
				$conec->siguiente();
			}
		}
		
		if($reservas<>'')
			$resultado=$resultado.'El lote se encuentra en la(s) reserva(s) <b>'.$reservas.'</b><br>';
		
		
		
		
		
		$sql="SELECT bloq_id FROM bloquear_terreno WHERE bloq_lot_id=".$lote." and bloq_estado = 'Habilitado'";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$bloqueados='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$bloqueados=$bloqueados.$objeto->bloq_id;
				else
					$bloqueados=$bloqueados.$objeto->bloq_id.',';
			
				$conec->siguiente();
			}
		}
		
		if($bloqueados<>'')
			$resultado=$resultado.'El lote se encuentra en el (los) terreno(s) bloqueado(s) <b>'.$bloqueados.'</b><br>';
			
		
		return $resultado;
		
	}
	
	
	
	//Verificacion del terreno antes de realizar una Bloqueo. Verificamos si el lote esta en algun modulo ya activo, como ser Venta, Bloqueo de Terreno, Reserva.
	function verificar_seguimiento_lote_bloqueo($lote) 
	{
		$conec=new ADO;
		
		$resultado='';

		$sql="SELECT ven_id FROM venta WHERE ven_lot_id=".$lote." and ven_estado in ('Pendiente','Pagado','Pendiente por Cobrar')";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$ventas='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$ventas=$ventas.$objeto->ven_id;
				else
					$ventas=$ventas.$objeto->ven_id.',';
				
				$conec->siguiente();
			}
		}
		
		if($ventas<>'')
			$resultado=$resultado.'El lote se encuentra en la(s) venta(s) <b>'.$ventas.'</b><br>';
		
		
		
		
		$sql="SELECT res_id FROM reserva_terreno WHERE res_lot_id=".$lote." and res_estado in ('Pendiente','Habilitado')";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$reservas='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$reservas=$reservas.$objeto->res_id;
				else
					$reservas=$reservas.$objeto->res_id.',';
				
				$conec->siguiente();
			}
		}
		
		if($reservas<>'')
			$resultado=$resultado.'El lote se encuentra en la(s) reserva(s) <b>'.$reservas.'</b><br>';
		
		
		
		
		
		$sql="SELECT bloq_id FROM bloquear_terreno WHERE bloq_lot_id=".$lote." and bloq_estado = 'Habilitado'";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$bloqueados='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$bloqueados=$bloqueados.$objeto->bloq_id;
				else
					$bloqueados=$bloqueados.$objeto->bloq_id.',';
			
				$conec->siguiente();
			}
		}
		
		if($bloqueados<>'')
			$resultado=$resultado.'El lote se encuentra en el (los) terreno(s) bloqueado(s) <b>'.$bloqueados.'</b><br>';
			
		
		return $resultado;
		
	}
	
	//Verificacion del terreno antes de realizar una reserva. Verificamos si el lote esta en algun modulo ya activo, como ser Venta, Bloqueo de Terreno, Reserva.
	function verificar_seguimiento_lote_venta($lote) 
	{
		$conec=new ADO;
		
		$resultado='';

		$sql="SELECT ven_id FROM venta WHERE ven_lot_id=".$lote." and ven_estado in ('Pendiente','Pagado','Pendiente por Cobrar')";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$ventas='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$ventas=$ventas.$objeto->ven_id;
				else
					$ventas=$ventas.$objeto->ven_id.',';
				
				$conec->siguiente();
			}
		}
		
		if($ventas<>'')
			$resultado=$resultado.'El lote se encuentra en la(s) venta(s) <b>'.$ventas.'</b><br>';
		
		
		
		
		$sql="SELECT res_id FROM reserva_terreno WHERE res_lot_id=".$lote." and res_estado in ('Pendiente','Habilitado')";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$reservas='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$reservas=$reservas.$objeto->res_id;
				else
					$reservas=$reservas.$objeto->res_id.',';
				
				$conec->siguiente();
			}
		}
		
		if($reservas<>'')
			$resultado=$resultado.'El lote se encuentra en la(s) reserva(s) <b>'.$reservas.'</b><br>';
		
		
		
		
		
		$sql="SELECT bloq_id FROM bloquear_terreno WHERE bloq_lot_id=".$lote." and bloq_estado = 'Habilitado'";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$bloqueados='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$bloqueados=$bloqueados.$objeto->bloq_id;
				else
					$bloqueados=$bloqueados.$objeto->bloq_id.',';
			
				$conec->siguiente();
			}
		}
		
		if($bloqueados<>'')
			$resultado=$resultado.'El lote se encuentra en el (los) terreno(s) bloqueado(s) <b>'.$bloqueados.'</b><br>';
			
		
		return $resultado;
		
	}
	
	//Verificacion del terreno antes de realizar una reserva. Verificamos si el lote esta en algun modulo ya activo, como ser Venta, Bloqueo de Terreno, Reserva.
	function verificar_seguimiento_lote_concretarreserva($lote) 
	{
		$conec=new ADO;
		
		$resultado='';

		$sql="SELECT ven_id FROM venta WHERE ven_lot_id=".$lote." and ven_estado in ('Pendiente','Pagado','Pendiente por Cobrar')";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$ventas='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$ventas=$ventas.$objeto->ven_id;
				else
					$ventas=$ventas.$objeto->ven_id.',';
				
				$conec->siguiente();
			}
		}
		
		if($ventas<>'')
			$resultado=$resultado.'El lote se encuentra en la(s) venta(s) <b>'.$ventas.'</b><br>';
		
		
		/*
		
		$sql="SELECT res_id FROM reserva_terreno WHERE res_lot_id=".$lote." and res_estado in ('Pendiente','Habilitado')";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$reservas='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$reservas=$reservas.$objeto->res_id;
				else
					$reservas=$reservas.$objeto->res_id.',';
				
				$conec->siguiente();
			}
		}
		
		if($reservas<>'')
			$resultado=$resultado.'El lote se encuentra en la(s) reserva(s) <b>'.$reservas.'</b><br>';
		
		*/
		
		
		
		$sql="SELECT bloq_id FROM bloquear_terreno WHERE bloq_lot_id=".$lote." and bloq_estado = 'Habilitado'";
		
		$conec->ejecutar($sql,false);
		
		$num = $conec->get_num_registros();
		
		$bloqueados='';
		
		if($num > 0)
		{
			for ($i = 0; $i < $num; $i++) 
			{
            	$objeto = $conec->get_objeto();
				
				if($num - $i == 1)
					$bloqueados=$bloqueados.$objeto->bloq_id;
				else
					$bloqueados=$bloqueados.$objeto->bloq_id.',';
			
				$conec->siguiente();
			}
		}
		
		if($bloqueados<>'')
			$resultado=$resultado.'El lote se encuentra en el (los) terreno(s) bloqueado(s) <b>'.$bloqueados.'</b><br>';
			
		
		return $resultado;
		
	}
	
 }
?>