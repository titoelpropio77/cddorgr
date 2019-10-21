<?php
$arr_ventas = array(9841 =>	0.48,
9843 =>	0.88,
9852 =>	0.34,
9853 =>	0.35,
9857 =>	0.72,
9862 =>	0.88,
9864 =>	0.71,
9866 =>	0.8 ,
9877 =>	0.14,
9879 =>	0.72,
9880 =>	0.72,
9881 =>	0.01,
9882 =>	0.72,
9883 =>	0.72,
9885 =>	0.72,
9886 =>	0.32,
9887 =>	0.32,
9888 =>	0.72);

foreach ($arr_ventas as $clave => $valor) {
	$sql_upd_ven = "update venta set ven_monto_efectivo=(ven_monto_efectivo+$valor) where ven_id='$clave';";
	echo "<p>$sql_upd_ven</p>";
	
	$sql_upd_vpag = "update venta_pago set vpag_saldo_inicial=(vpag_saldo_inicial+$valor),vpag_saldo_final=(vpag_saldo_final+$valor) where vpag_ven_id='$clave' and vpag_estado='Activo';";
	echo "<p>$sql_upd_vpag</p>";
}
?>