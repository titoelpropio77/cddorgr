
<?php
require_once('mysql.php');
require("../clases/mytime_int.php");

		$query= new QUERY();
		
		$query2= new QUERY();
		
		
		
		$sql = "select 
				ven_id,cmp_id,ven_monto 
				from 
				comprobante inner join venta on(cmp_tabla_id=ven_id)"; 
		//echo $sql;
		
		$query->consulta($sql);
		
		$num=$query->num_registros();
		
		for($i=0;$i<$num;$i++)
		{
			list($ven_id,$cmpid,$ven_monto)=$query->valores_fila();

			$valor=($ven_monto*6.96);
			$sql2 = "update comprobante_detalle set cde_monto=$valor where cde_cmp_id=$cmpid AND cde_cue_id=5"; 
		//echo $sql;
		
			$query2->consulta($sql2);
			
			$valor=(($ven_monto*6.96)*(-1));
			$sql2 = "update comprobante_detalle set cde_monto=$valor where cde_cmp_id=$cmpid AND cde_cue_id=17"; 
		//echo $sql;
		
			$query2->consulta($sql2);	
			
		}
		?>
		

