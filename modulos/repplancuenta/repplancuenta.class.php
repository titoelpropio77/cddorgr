<?php

class REPPLANCUENTA extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function REPPLANCUENTA()
	{
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='repplancuenta';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('PLAN DE CUENTAS');
	}
	
	
	function dibujar_busqueda()
	{
			$this->formulario();
	}
	
	function formulario()
	{
	
		$this->formulario->dibujar_cabecera();	
		$this->mostrar_reporte();
	}
	
	function hijos($cue_id,$cad)
	{
	    $conec= new ADO();
	    $sql = "SELECT cue_id, cue_numero, cue_nivel,cue_descripcion, cue_padre_id,cue_tcu_id
				FROM cuenta
				WHERE cue_padre_id = $cue_id "; 
				
	    $conec->ejecutar($sql);		
		$num=$conec->get_num_registros();
	    
	    for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();
			
		
			$ne = "<b>";
			   $nec = "</b>";
			if ($objeto->cue_nivel == 3)
			{
			   	$ne = "";
			$nec = "";
            }
			
			
			echo '<tr>';			
					
					echo "<td>";
					    
						echo $ne.$cad.$objeto->cue_numero.$nec;
					echo "</td>";
					
					echo "<td>";
						echo $ne.$cad.$objeto->cue_descripcion.$nec;
					echo "</td>";		
			echo "</tr>";		
				
              $this->hijos($objeto->cue_id,$cad."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");				
			
			$conec->siguiente();
		}
	}
	
	
	function mostrar_reporte()
	{		
		$conec= new ADO();			
		$sql =  "SELECT cue_id, cue_numero, cue_descripcion, cue_padre_id,cue_tcu_id
				FROM cuenta
				WHERE cue_padre_id = 0"; 
					
		
		
		
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$bol=0;
		$dol=0;
		
		////
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
		
		$myday = setear_fecha(strtotime(date('Y-m-d')));
		////
				
		?>		
				<?php echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=repbalance\';"></td></tr></table><br><br>
				';?>
						
				 

			<div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="40%">
					<strong><?php echo _nombre_empresa; ?></strong><BR>
					
				   </td>
				    <td><center><strong><h3>PLAN DE CUENTAS</h3></strong><BR></center></td>
				    <td width="40%"><div align="right"><img src="imagenes/micro.png"/></div></td>
				  </tr>
				   <tr>
		    <td colspan="2">
		    <strong>Impreso el: </strong> <?php echo $myday;?> <br><br></td>
			
		    <td></td>
		  </tr>
				 
			</table>
			<table   width="90%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>				
				<tr>
					<th>
						<b>Codigo</b>
					</th>
					<th>
						<b>Cuenta</b>
					</th>										
				</tr>
				</thead>
				<tbody>
		<?php				
		
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();
			
			echo '<tr>';			
					
					echo "<td>";
						echo "<b>".$objeto->cue_numero."</b>";
					echo "</td>";
					
					echo "<td>";
						echo "<b>".$objeto->cue_descripcion."</b>";
					echo "</td>";				
					
				echo "</tr>";		
            $this->hijos($objeto->cue_id,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");				
					$conec->siguiente();
		}
		?>
	
		
		</tbody>
		</table>
		</center>
		<br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
		</div><br>
		<?php
	}
	
	
}
?>