<?php

class COMICIONES {
	var $usuDatos;
	var $encript;
	var $vdo_id;
	
	function COMICIONES(){
		$this->encript = new Encryption();	
		$this->usuDatos = explode("|",$this->encript->decode($_GET['token']));
		$this->vdo_id = $this->vendedor_id();
	}
	
	function todos() {
	
		$opcionesDatos = array();
		$opcionesDatos['pen'] = $this->pendientes();
		$opcionesDatos['pag'] = $this->pagadas();
		$opcionesDatos['ina'] = $this->inactivas();
		
		return array($opcionesDatos);
	}
	
	function pendientes() {
		
		$html = "";
		$html .= '<table width="100%" class="table" data-filter-text-only="true" cellpadding="0" cellspacing="0">';
			$html .= '<thead>';
				$html .= '<tr>';
					$html .= '<th>Nro</th>';
					$html .= '<th>Venta Nro</th>';
					$html .= '<th data-hide="phone,tablet">Cliente</th>';
					$html .= '<th data-hide="phone,tablet">Vendedor</th>';
					$html .= '<th data-hide="phone,tablet">Tipo Venta</th>';
					$html .= '<th data-hide="phone,tablet">Monto Valor Terreno</th>';
					$html .= '<th data-hide="phone,tablet">Periodo</th>';
					$html .= '<th data-hide="phone,tablet">Urbanizaci&oacute;n</th>';
					$html .= '<th data-hide="phone,tablet">Manzano</th>';
					$html .= '<th data-hide="phone,tablet">Lote</th>';
					$html .= '<th>Monto Bs</th>';
					$html .= '<th>Monto $us</th>';
				$html .= '</tr>';
			$html .= '</thead>';
		$html .= '<tbody>';
				
        $sql = "SELECT distinct com_id,com_monto,com_moneda,com_estado,com_ven_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,ven_fecha,ven_tipo,ven_valor, com_vdo_id
		FROM
		comision inner join venta on (com_ven_id=ven_id)
		inner join lote on (ven_lot_id=lot_id)
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on (man_urb_id=urb_id)
		inner join vendedor on (com_vdo_id=vdo_id)
		inner join interno on (vdo_int_id=int_id)
		where
		com_estado='Pendiente' and com_vdo_id='" .$this->vdo_id . "' and ven_estado!='Anulado'
		order by com_id asc,ven_fecha asc";

		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		
        $totalbs = 0;
        $totalsus = 0;
        for ($i = 0; $i < $num; $i++) {
		
				$objeto = mysql_fetch_object($result);
				
				$sql_venta_propietario = "select * from venta where ven_id=$objeto->com_ven_id and ven_vdo_id=$objeto->com_vdo_id";
				
				
				$resul_venta_propietario = mysql_query($sql_venta_propietario);
				$registro_ventas = mysql_num_rows($resul_venta_propietario);
				
				//echo $sql_venta_propietario; 
				
				$sql_venta = "select * from venta where ven_id=$objeto->com_ven_id";
				$resul_venta = mysql_query($sql_venta);
				$venta = mysql_fetch_object($resul_venta);
				
				$fecha_venta = $venta->ven_fecha; 
				
				$date = new DateTime($fecha_venta);
				$date->modify('first day of this month');
				$primer_dia =  $date->format('Y-m-d');
				$date->modify('last day of this month');
				$ultimo_dia =  $date->format('Y-m-d');
				
				$sql_venta_mes = "select * from venta where ven_vdo_id=$objeto->com_vdo_id and ven_fecha between '$primer_dia' and '$ultimo_dia'";
				$resul_venta_mes = mysql_query($sql_venta_mes);
				$num_venta_mes = mysql_num_rows($resul_venta_mes);
				
				if ($registro_ventas < 1 && $num_venta_mes < 1) {
					continue;
				}
				
				// particionamos la fecha
				$fecha='';
				$fecha  = $objeto->ven_fecha;
				$porciones_fecha = explode("-", $fecha);
				$mes 	= $porciones_fecha[1]; // mes
				$anio 	= $porciones_fecha[0]; // anio

					$html .= '<tr>';
						$html .= '<td>'.($i + 1).' &nbsp;</td>';
						$html .= '<td>'.$objeto->com_ven_id.' &nbsp;</td>';
						$html .= '<td>'.utf8_encode($this->get_nombre_propietario($objeto->com_ven_id)).' &nbsp;</td>';
						$html .= '<td>'.utf8_encode($this->get_nombre_vendedor($objeto->com_ven_id)).' &nbsp;</td>';
						$html .= '<td>'.$objeto->ven_tipo.' &nbsp;</td>';
						$html .= '<td>'.$objeto->ven_tipo.' &nbsp;</td>';
						$html .= '<td>'.$objeto->ven_valor.' &nbsp;</td>';
						$html .= '<td>'.$mes.'/'.$anio.' &nbsp;</td>';
						$html .= '<td>'.$objeto->urb_nombre.' &nbsp;</td>';
						$html .= '<td>'.$objeto->man_nro.' &nbsp;</td>';
						$html .= '<td>'.$objeto->lot_nro.' &nbsp;</td>';
						if ($objeto->com_moneda == '1') {
							$bs = $objeto->com_monto;
							$sus = round($objeto->com_monto / $this->tc, 2);

							$totalbs = $totalbs + $bs;
							$totalsus = $totalsus + $sus;
						} else {
							$sus = $objeto->com_monto;
							$bs = round($objeto->com_monto * $this->tc, 2);
							$totalbs = $totalbs + $bs;
							$totalsus = $totalsus + $sus;
						}
						$html .= '<td>'.$bs.' &nbsp;</td>';
						$html .= '<td>'.$sus.' &nbsp;</td>';
					$html .= '</tr>';
				}
				
				$html .= '</tbody>';
				$html .= '<tfoot>';
					$html .= '<tr>';
						$html .= '<td>&nbsp;</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '<td>&nbsp;</td>';
						$html .= '<td>Totales:</td>';
						$html .= '<td>'.$totalbs . ' Bs</td>';
						$html .= '<td>'.$totalsus . ' $us</td>';
					$html .= '</tr>';
				$html .= '</tfoot>';
			$html .= '</table>';
			
			if($num >0 ) {
			} else{
				$html ='';
			}
		return $html;
	}
	
    function pagadas() {
		
		$html = "";
			$html .= '<table class="table" data-filter-text-only="true" cellpadding="0" cellspacing="0">';
				$html .= '<thead>';
					$html .= '<tr>';
						$html .= '<th>Venta Nro</th>';
						$html .= '<th>Cliente</th>';
						$html .= '<th data-hide="phone,tablet">Vendedor</th>';
						$html .= '<th data-hide="phone,tablet">Tipo Venta</th>';
						$html .= '<th data-hide="phone,tablet">Monto Venta</th>';
						$html .= '<th data-hide="phone,tablet">Urbanizaci&oacute;n</th>';
						$html .= '<th data-hide="phone,tablet">Manzano</th>';
						$html .= '<th data-hide="phone,tablet">Lote</th>';
						$html .= '<th>Monto</th>';
						$html .= '<th>Moneda</th>';
					$html .= '</tr>';
				$html .= '</thead>';
				$html .= '<tbody>';
					
					$sql = "SELECT com_id,com_monto,com_moneda,com_estado,com_ven_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,ven_tipo,ven_monto
					FROM
					comision inner join venta on (com_estado='Pagado' and com_vdo_id='" . $this->vdo_id. "' and com_ven_id=ven_id)
					inner join lote on (ven_lot_id=lot_id)
					inner join manzano on (lot_man_id=man_id)
					inner join urbanizacion on (man_urb_id=urb_id)
					inner join vendedor on (com_vdo_id=vdo_id)
					inner join interno on (vdo_int_id=int_id)
					ORDER BY com_fecha_pag DESC";
					
					$resul = mysql_query($sql);
					$num = mysql_num_rows($resul);
					
					$tot = 0;
					for ($i = 0; $i < $num; $i++) {
						$objeto = mysql_fetch_object($resul);

						$html .= '<tr>';
							$html .= '<td>'.$objeto->com_ven_id.' &nbsp;</td>';
							$html .= '<td>'.utf8_encode($this->get_nombre_propietario($objeto->com_ven_id)).' &nbsp;</td>';
							$html .= '<td>'.utf8_encode($this->get_nombre_vendedor($objeto->com_ven_id)).' &nbsp;</td>';
							$html .= '<td>'.$objeto->ven_tipo.' &nbsp;</td>';
							$html .= '<td>'.$objeto->ven_monto.' &nbsp;</td>';
							$html .= '<td>'.utf8_encode($objeto->urb_nombre).' &nbsp;</td>';
							$html .= '<td>'.$objeto->man_nro.' &nbsp;</td>';
							$html .= '<td>'.$objeto->lot_nro.' &nbsp;</td>';
							$html .= '<td>'.$objeto->com_monto.' &nbsp;</td>';
							//$html .= '<td>'.($tot+=$objeto->com_monto).' &nbsp;</td>';
							
							$monedas = "";
							if ($objeto->com_moneda == '1'){
								$monedas = "Bolivianos";
							} else {
								$monedas = "Dolares";
							}
							$html .= '<td>'.$monedas.' &nbsp;</td>';
						$html .= '</tr>';
					}
			
			$html .= '</tbody>';
			$html .= '<tfoot>';
				$html .= '<tr>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>'.$tot.' </td>';
					$html .= '<td>&nbsp;</td>';
				$html .= '</tr>';
			$html .= '</tfoot>';
		$html .= '</table>';
		
		if($num >0 ) {
		} else{
			$html ='';
		}
			
		return $html;
    }
	
	function inactivas() {
		
		
		$html .= '<table class="table" data-filter-text-only="true" cellpadding="0" cellspacing="0">';
			$html .= '<thead>';
				$html .= '<tr>';
					$html .= '<th>Nro</th>';
					$html .= '<th>Venta Nro</th>';
					$html .= '<th>Cliente</th>';
					$html .= '<th  data-hide="phone,tablet">Vendedor</th>';
					$html .= '<th  data-hide="phone,tablet">Tipo Venta</th>';
					$html .= '<th  data-hide="phone,tablet">Monto Valor Terreno</th>';
					$html .= '<th  data-hide="phone,tablet">Periodo</th>';
					$html .= '<th  data-hide="phone,tablet">Urbanizaci&oacute;n</th>';
					$html .= '<th  data-hide="phone,tablet">Manzano</th>';
					$html .= '<th data-hide="phone,tablet">Lote</th>';
					$html .= '<th>Monto Bs</th>';
					$html .= '<th>Monto $us</th>';
				$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';
			
        $sql = "SELECT distinct com_id,com_monto,com_moneda,com_estado,com_ven_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,ven_fecha,ven_tipo,ven_valor, com_vdo_id
		FROM
		comision inner join venta on (com_ven_id=ven_id)
		inner join lote on (ven_lot_id=lot_id)
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on (man_urb_id=urb_id)
		inner join vendedor on (com_vdo_id=vdo_id)
		inner join interno on (vdo_int_id=int_id)
		where
		com_estado='Pendiente' and com_vdo_id='" .$this->vdo_id. "' and ven_estado!='Anulado'
		order by com_id asc,ven_fecha asc";
		
		$resul = mysql_query($sql);
		$num = mysql_num_rows($resul);
							
        $totalbs = 0;
        $totalsus = 0;
        for ($i = 0; $i < $num; $i++) {
			
			//echo $i;
			
			$objeto = mysql_fetch_object($resul);
			
            $sql_venta_propietario = "select * from venta where ven_id=$objeto->com_ven_id and ven_vdo_id=$objeto->com_vdo_id";
			$resul_venta_propietario = mysql_query($sql_venta_propietario);
			$registro_ventas = mysql_num_rows($resul_venta_propietario);
		
			
            $sql_venta = "select * from venta where ven_id=$objeto->com_ven_id";
			$resul_venta = mysql_query($sql_venta);
            $venta = mysql_fetch_object($resul_venta);
			
            $fecha_venta = $venta->ven_fecha;
            $date = new DateTime($fecha_venta);

            $date->modify('first day of this month');
            $primer_dia =  $date->format('Y-m-d');
            $date->modify('last day of this month');
            $ultimo_dia =  $date->format('Y-m-d');
			
            $sql_venta_mes = "select * from venta where ven_vdo_id=$objeto->com_vdo_id and ven_fecha between '$primer_dia' and '$ultimo_dia'";
			$resul_venta_propietario = mysql_query($sql_venta_mes);
			$num_venta_propietario = mysql_num_rows($resul_venta_propietario);
			
            if ($registro_ventas > 0 || $num_venta_propietario > 0) {
            	continue;
            }
			
			// particionamos la fecha
			$fecha='';
			$fecha  = $objeto->ven_fecha;
			$porciones_fecha = explode("-", $fecha);
			$mes 	= $porciones_fecha[1]; // mes
			$anio 	= $porciones_fecha[0]; // anio
			
			$html .= '<tr>';
				$html .= '<td>'.($i + 1).' &nbsp;</td>';
				$html .= '<td>'.$objeto->com_ven_id.' &nbsp;</td>';
				$html .= '<td>'.utf8_encode($this->get_nombre_propietario($objeto->com_ven_id)).' &nbsp;</td>';
				$html .= '<td>'.utf8_encode($this->get_nombre_vendedor($objeto->com_ven_id)).' &nbsp;</td>';
				$html .= '<td>'.$objeto->ven_tipo.' &nbsp;</td>';
				$html .= '<td>'.$objeto->ven_valor.' &nbsp;</td>';
				$html .= '<td>'.$mes.'/'.$anio.' &nbsp;</td>';
				$html .= '<td>'.utf8_encode($objeto->urb_nombre).' &nbsp;</td>';
				$html .= '<td>'.$objeto->man_nro.' &nbsp;</td>';
				$html .= '<td>'.$objeto->lot_nro.' &nbsp;</td>';
				
				if ($objeto->com_moneda == '1') {
					$bs = $objeto->com_monto;
					$sus = round($objeto->com_monto / $this->tc, 2);

					$totalbs = $totalbs + $bs;
					$totalsus = $totalsus + $sus;
				} else {
					$sus = $objeto->com_monto;
					$bs = round($objeto->com_monto * $this->tc, 2);
					$totalbs = $totalbs + $bs;
					$totalsus = $totalsus + $sus;
				}
				$html .= '<td>'.$bs.' &nbsp;</td>';
				$html .= '<td>'.$sus.' &nbsp;</td>';
				$html .= '</tr>';
			}
                                
			$html .= '</tbody>';
			$html .= '<tfoot>';
				$html .= '<tr>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>&nbsp;</td>';
					$html .= '<td>Totales:</td>';
					$html .= '<td> '.$totalbs . ' Bs </td>';
					$html .= '<td> '.$totalsus . ' $us </td>';
				$html .= '</tr>';
			$html .= '</tfoot>';
		$html .= '</table>';
		
		if($num >0 ) {
		} else{
			$html ='';
		}
		
		return $html;
    }
	
	function vendedor_id() {
		$sql = "select vdo_id from vendedor where vdo_int_id='".$this->usuDatos[2]."';";
		//$sql = "select vdo_id from vendedor where vdo_int_id='2';"; 
		$result = mysql_query($sql); 
		$num = mysql_num_rows($result);
		if($num >0 ) {
			$objeto = mysql_fetch_object($result);
			return $objeto->vdo_id;
		} else {
			return 0;
		}
	}
	
	function get_nombre_propietario($venta_id) {
    	$sql_venta = "select interno.* from interno
    	inner join venta on (ven_int_id=int_id)
    	where ven_id=$venta_id";
		$result = mysql_query($sql_venta);
		$propietario = mysql_fetch_object($result);
    	return $propietario->int_nombre . ' ' . $propietario->int_apellido;
    }

    function get_nombre_vendedor($venta_id) {
    	$sql_venta = "select interno.* from venta
    	inner join vendedor on (ven_vdo_id=vdo_id)
    	inner join interno on (vdo_int_id=int_id)
    	where ven_id=$venta_id";
		$result = mysql_query($sql_venta);
		$vendedor = mysql_fetch_object($result);
		
    	return $vendedor->int_nombre . ' ' . $vendedor->int_apellido;
    }
}
