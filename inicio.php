<link href="css/estilos.css" rel="stylesheet" type="text/css">
<?php
date_default_timezone_set("America/La_Paz");
require_once('clases/coneccion.class.php');
	
require_once('clases/usuario.class.php');

ADO::$token=date('YmdHis');
$conec= new ADO();
$usu=new USUARIO;

$sql ="select 
	int_nombre,int_apellido
	from
	interno
	where int_id='".$usu->get_usu_per_id()."'
	";

$conec->ejecutar($sql);	

$objeto=$conec->get_objeto();	

?>
<br/><br/><br/><br/><br/><br/>
<h2><center> Bienvenid@ <?php echo $objeto->int_nombre.' '.$objeto->int_apellido; ?></center></h2>

<?php
//session_start();
//echo session_id();
//echo '<pre>';
//print_r($_SESSION);
//echo '</pre>';
exit;

																					
$sql ="select 
	lot_nro,man_nro,urb_nombre,urb_banco,urb_bolivianos,urb_dolares,int_ci 
	from 
	venta 
	inner join lote on (ven_int_id='".$usu->get_usu_per_id()."' and ven_estado='Pendiente' and ven_lot_id=lot_id)
	inner join manzano on (lot_man_id=man_id)
	inner join urbanizacion on (man_urb_id=urb_id)
	inner join interno on (ven_int_id=int_id)
	";

$conec->ejecutar($sql);					
							
$num=$conec->get_num_registros();

if($num > 0)							
{
	$pagina="'contenido_reporte'";
		
		$page="'about:blank'";
		
		$extpage="'reportes'";
		
		$features="'left=100,width=800,height=500,top=0,scrollbars=yes'";
		
		$extra1="'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
			  </head>
			  <body>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
		$extra1.=" <a href=javascript:window.print();>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
		$extra2="'</center></body></html>'"; 
		
		
		////
				
		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td></tr></table><br><br>
				';
	
	
	?>
	<div id="contenido_reporte" style="clear:both;";>
	<br/><br/>
	<center><p>Información para que pueda realizar el pago de sus cuotas en el Banco</p></center>
	<table class="tablaLista" cellpadding="0" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th >Terreno</th>
			<th >Banco</th>
			<th >Bolivianos</th>
			<th >Dolares</th>
			<th >Codigo</th>
		</tr>
	</thead>
	<tbody>
	<?php	
	
	for($i=0;$i<$num;$i++)												
	{														
		$objeto=$conec->get_objeto();

		?>
		<tr>
			<td><?php echo "Urb:$objeto->urb_nombre - Mza:$objeto->man_nro - Lote:$objeto->lot_nro"; ?></td>
			<td><?php echo $objeto->urb_banco; ?></td>
			<td><?php echo $objeto->urb_bolivianos; ?></td>
			<td><?php echo $objeto->urb_dolares; ?></td>
			<td><?php echo ereg_replace("[^0-9]", "", $objeto->int_ci).$objeto->man_nro.$objeto->lot_nro; ?></td>
		<?php
		
		$conec->siguiente();																									
	}
	?>
	</tbody>
	</table>
	</div>
	<?php
	
}	
?>