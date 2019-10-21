<?php

class CUENTASX extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	
	function CUENTASX()
	{
		//permisos
		$this->ele_id=148;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="int_nombre";
		$this->arreglo_campos[0]["texto"]="Nombre";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;
		
		$this->arreglo_campos[1]["nombre"]="int_apellido";
		$this->arreglo_campos[1]["texto"]="Apellido";
		$this->arreglo_campos[1]["tipo"]="cadena";
		$this->arreglo_campos[1]["tamanio"]=40;
		
		$this->arreglo_campos[2]["nombre"]="cux_tipo";
		$this->arreglo_campos[2]["texto"]="Tipo";
		$this->arreglo_campos[2]["tipo"]="comboarray";
		$this->arreglo_campos[2]["valores"]="1,2:X Cobrar,X Pagar";	
		
		$this->arreglo_campos[3]["nombre"]="cux_estado";
		$this->arreglo_campos[3]["texto"]="Estado";
		$this->arreglo_campos[3]["tipo"]="comboarray";
		$this->arreglo_campos[3]["valores"]="Pendiente,Pagado:Pendiente,Pagado";		
		
		
		$this->link='gestor.php';
		
		$this->modulo='cuentasx';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('CUENTAS X COBRAR / X PAGAR');
		
		$this->usu=new USUARIO;
		
		
	}
	
	function cuentas()
	{
		$this->formulario->dibujar_tarea();
		
		$this->xpagar();
		
	}
	
	
	
	function xpagar()
	{
		if($_POST['cux_id']<>"")
		{
			$conec= new ADO();
			
			$sql="select cja_cue_id from cajero where cja_usu_id = '".$this->usu->get_id()."'";
			
			$conec->ejecutar($sql);		

			$nume=$conec->get_num_registros();

			if($nume > 0)
			{
				$obj = $conec->get_objeto();
				
				$caja=$obj->cja_cue_id;
				
				$sql = "insert into cuentax_pago (cup_cux_id,cup_monto,cup_moneda,cup_tipo_cambio,cup_fecha,cup_usu_id,cup_estado)
					values('".$_POST['cux_id']."','".$_POST['monto']."','".$_POST['monedacuenta']."','".$_POST['tca']."','".date('Y-m-d')."','".$this->usu->get_id()."','Pagado')		
				"; 
				
				$conec->ejecutar($sql,false);
				
				$llave=mysql_insert_id();
				
				///**REFLEJO EN LAS CUENTAS**///
				
				include_once("clases/registrar_comprobantes.class.php");
				 
				$comp = new COMPROBANTES();		
				
				
				///INTERNO
				$sql="select 
				cux_id,cux_cue_cp,cux_int_id,cux_moneda,cux_concepto,cux_tipo,cux_cco_id 
				from 
				cuentasx	
				where 
				cux_id = '".$_POST['cux_id']."'";
				
				$conec->ejecutar($sql);		
				
				$obj = $conec->get_objeto();
				
				//if($_POST['monedacuenta']=='1')
				//{
					$subtotal=$_POST['monto'];
				//}
				//else
				//{
					//$subtotal=$_POST['monto']*$_POST['tca'];
				//}
				
				
				$cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$_POST['tca'],$_POST['monedacuenta'],'',$obj->cux_int_id,$this->usu->get_id(),$obj->cux_tipo,'1','cuentax_pago',$llave);			   
				
				
				
				if($obj->cux_tipo==1)
				{
					if($_POST['monedacuenta']=='1')
					{
						$comp->ingresar_detalle($cmp_id,$subtotal,$caja,0);

						$comp->ingresar_detalle($cmp_id,($subtotal * (-1)),$obj->cux_cue_cp,$obj->cux_cco_id,'Pago de: '.$obj->cux_concepto);
					}
					else
					{
						$comp->ingresar_detalle($cmp_id,0,$caja,0,'',$subtotal);

						$comp->ingresar_detalle($cmp_id,0,$obj->cux_cue_cp,$obj->cux_cco_id,'Pago de: '.$obj->cux_concepto,($subtotal * (-1)));
					}
				}
				else
				{
					if($_POST['monedacuenta']=='1')
					{
						$comp->ingresar_detalle($cmp_id,($subtotal * (-1)),$caja,0);

						$comp->ingresar_detalle($cmp_id,$subtotal,$obj->cux_cue_cp,$obj->cux_cco_id,'Pago de: '.$obj->cux_concepto);
					}
					else
					{
						$comp->ingresar_detalle($cmp_id,0,$caja,0,'',($subtotal * (-1)));

						$comp->ingresar_detalle($cmp_id,0,$obj->cux_cue_cp,$obj->cux_cco_id,'Pago de: '.$obj->cux_concepto,$subtotal);
					}
				}
						
				///**FIN REFLEJO**///
				
				if($_POST['monto']==$_POST['saldo'])
				{
					$sql = "update cuentasx set cux_estado='Pagado' where cux_id='".$_POST['cux_id']."'"; 
					
					$conec->ejecutar($sql,false);
				}
				
				$this->nota_de_pago($llave);
				
			}
			else
			{
				$mensaje='No puede realizar ningun cobro, por que usted no esta registrado como cajero.';
				
				$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
			}
			
		}
		else
		{
			$url=$this->link.'?mod='.$this->modulo.'&tarea=CUENTAS';
			
			$tipocambio=$this->tc;
			
			$this->datos_cuenta($_GET['id'],$monto,$pagado,$moneda,$des);
			
			$saldo=$monto-$pagado;
			
			if($moneda=='1')
			{
				$mon="Bs";
				
			}
			else
			{
				$mon='$us';
				
			}
			
			?>
			<script>
			function enviar_formulario(){
					
					var monto=parseFloat(document.frm_sentencia.monto.value);
					var monedacuenta=document.frm_sentencia.monedacuenta.value;
					var saldo=parseFloat(document.frm_sentencia.saldo.value);
					
					if(monto > 0)
					{
						if(monto > saldo)
						{
							$.prompt('El Monto debe ser Menor o Igual que el saldo y no Mayor.',{ opacity: 0.8 });
						}
						else
						{
							document.frm_sentencia.submit();
						}
					}
					else
					{
						$.prompt('Para registrar el pago debe introducir el monto.',{ opacity: 0.8 });			   
					}
				}
			function ejecutar_script(id,tarea){
					var txt='Esta seguro de anular este pago de Cuota?';
					$.prompt(txt,{  
						buttons:{Aceptar:true, Cancelar:false},
						callback: function(v,m,f){
							
							if(v){
									location.href='gestor.php?mod=cuentasx&tarea='+tarea+'&acc=anular&cup_id='+id;
							}
													
						}			
			
					}); 
				}
			</script>
			
            <table align=right border=0><tr><td><a href="gestor.php?mod=cuentasx" title="VOLVER"><img border="0" width="20" src="images/back.png"></a></td></tr></table>
            
            <table align=right border=0><tr><td><a style="float:left; margin:0 0 0 7px;" class="group" href="sueltos/llamada.php?accion=agregar_divisa"><img border="0" src="images/compra_venta_divisa.png"></a></td></tr></table>
            
            <!--FancyBox-->
            <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
            <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
            <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
            <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
            <!--FancyBox-->
			<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Pago de Cuenta: <?php echo $des; ?></div>
						<div id="ContenedorSeleccion">
							
							
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto</div>
							   <div id="CajaInput">
							  <input type="text" name="montototal" id="montototal" size="10" value="<?php echo $monto;?>" readonly="readonly"><?php echo $mon;?>
							   </div>
							   <div id="CajaInput">
							   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							   
							   Tipo de Cambio&nbsp;<input type="text" name="tca" id="tca" size="5" value="<?php echo $tipocambio;?>" readonly="readonly">
							   <input type="hidden" name="cux_id" id="cux_id" value="<?php echo $_GET['id'];?>">
							   <input type="hidden" name="monedacuenta" id="monedacuenta" value="<?php echo $moneda;?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Pagado</div>
							   <div id="CajaInput">
							   <input type="text" name="pagado" id="pagado" size="10" value="<?php echo $pagado;?>" readonly="readonly"><?php echo $mon;?>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Saldo</div>
							   <div id="CajaInput">
							   <input type="text" name="saldo" id="saldo" size="10" value="<?php echo $saldo;?>" readonly="readonly"><?php echo $mon;?>&nbsp;&nbsp;&nbsp;
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto a Pagar</div>
							   <div id="CajaInput">
							   <input type="text" name="monto" id="monto" size="10" value=""><?php echo $mon;?>&nbsp;&nbsp;&nbsp;Nota:<font color="#FF0000">( Al Realizar el ultimo pago debe ser identico al Saldo)</font>
							   </div>
							</div>
							<!--Fin-->
						</div>
						
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
								<?php
								if(!($ver))
								{
									?>
									<input type="button" class="boton" name="" value="Pagar Cuenta" onclick="javascript:enviar_formulario()">
									<?php
								}
								else
								{
									?>
									<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
									<?php
								}
								?>
								</center>
						   </div>
						</div>
				</div>
			</form>
		</div>
        <script type="text/javascript">
			$(document).ready(function(){
				$("a.group").fancybox({
					'hideOnContentClick': false,
					'overlayShow'	: true,
					'zoomOpacity'	: true,
					'zoomSpeedIn'	: 300,
					'zoomSpeedOut'	: 200,
					'overlayOpacity':0.5,
					
					'frameWidth'	:700,
					'frameHeight'	:350,
					'type'			:'iframe'
				});
				
				$('a.close').click(function(){
				 $(this).fancybox.close();
				});

			});
		</script>
        
        <?php
        
		$this->dibujar_encabezado_pagos();
		
		$this->mostrar_busqueda_pagos();
		
		?>
		
		<?php
		}
			
	}
	
	
	
	function dibujar_encabezado_pagos()
	{

		?><div style="clear:both;"></div><center>
		<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
			<tr>
				
				<td><center><strong><h3>LISTADO DE PAGOS</h3></strong></center></p></td>
				
			</tr>
				 
		</table>
        <br />
		<table class="tablaLista" cellpadding="0" cellspacing="0" width="60%">
			<thead>
				<tr>

					<th >

						Nro.

					</th>
					<th >

						Monto

					</th>

				
					
					<th >

						Moneda

					</th>
					<th >

						Tipo Cambio

					</th>					
					<th >

						Fecha

					</th>
					
					<th class="tOpciones" width="100px">

						Opciones

					</th>

				</tr>
			</thead>
			<tbody>
		<?PHP

	}

	

	function mostrar_busqueda_pagos()
	{
		$conversor = new convertir();
		$conec=new ADO();

		$sql="SELECT 
		cup_id,cup_cux_id,cup_monto,cup_moneda,cup_tipo_cambio,cup_fecha,cup_hora,cup_usu_id,cup_estado
		FROM 
		cuentax_pago
		WHERE 
		cup_cux_id='".$_GET['id']."' AND cup_estado='Pagado'
		ORDER BY 
		cup_id ASC";

		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();
		$sumatorio=0;
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();
			$sumatorio+=$objeto->cup_monto;
			echo '<tr class="busqueda_campos">';

			?>
				
				<td align="left">
					<center>
					<?php echo $i+1;?>
					</center>
				</td>
				
                <td align="left">
					<center>
					<?php echo $objeto->cup_monto;?>
					</center>
				</td>
                
                <td align="left">
					<center>
					<?php if($objeto->cup_moneda==1){echo "Bolivianos"; } else { echo "Dolares"; }?>
					</center>
				</td>
                
                <td align="left">
					<center>
					<?php echo $objeto->cup_tipo_cambio;?>
					</center>
				</td>
				
				<td align="left">
					<center>
					
					<?php echo $conversor->get_fecha_latina($objeto->cup_fecha);?>
					</center>
				</td>				

				<td>

					<center>

					<a href="gestor.php?mod=cuentasx&tarea=CUENTAS&acc=ver&cup_id=<?php echo $objeto->cup_id;?>&cup_fecha=<?php echo $objeto->cup_fecha;?>"><img src="images/ver.png" alt="VER" title="VER" border="0"></a>
					
				<!--	gestor.php?mod=credito_prendario&tarea=INTERES&acc=anular&cre_id=<?php //echo $objeto->cpi_id;?> -->
					<a href="javascript:ejecutar_script('<?php echo $objeto->cup_id;?>','CUENTAS');" target="contenido" ><img src="images/anular.png" alt="ANULAR" title="ANULAR" border="0"></a>

					</center>

				</td>

			<?php

			echo "</tr>";

			$conec->siguiente();

		}
		echo "</tbody>";
			?>
			<tfoot>	
				<tr>
				<td><b>Total</b></td>
				<td><? echo $sumatorio; ?></td>
				<td colspan="4" >&nbsp;</td></tr>
			</tfoot>			
			<?		

		echo "</table></center><br>";
	}
	
	function datos_cuenta_historial($cuenta,$fecha,&$monto,&$pagado,&$moneda,&$des="")
	{
		$conec= new ADO();
		
		$sql = "select cux_monto,cux_moneda,cux_concepto from cuentasx where cux_id='$cuenta'"; 
		
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();

		$moneda=$objeto->cux_moneda;
		
		$monto=$objeto->cux_monto;
		
		$des=$objeto->cux_concepto;
		
		$sql = "select sum(cup_monto) as pagado from cuentax_pago where cup_cux_id='$cuenta' AND cup_fecha <= '$fecha' AND cup_estado='Pagado'"; 
		
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();
			
		$pagado=$objeto->pagado;
	}
	
	function anular_pago($id)
	{
	
		$conec= new ADO();
		
		$sql="update cuentax_pago set 
							cup_estado='Anulado'
							where cup_id = '$id'";
							
		$conec->ejecutar($sql);
		
		
		include_once("clases/registrar_comprobantes.class.php");
			 
		$comp = new COMPROBANTES();	
		
		$comp->anular_comprobante_tabla('cuentax_pago',$id);
		
		
			
		$mensaje='Pago Anulado Correctamente!!!';
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function mostrar_nota_pago_historial($pago,$fecha)
	{	
		$conec= new ADO();
		
		$sql = "SELECT 
		cup_cux_id,cup_monto,cup_moneda,cup_tipo_cambio,cup_fecha,cup_usu_id,cux_concepto,cux_tipo 
		FROM 
		cuentax_pago INNER JOIN cuentasx ON (cup_cux_id=cux_id) 
		WHERE cup_id='$pago' AND cup_fecha <= '$fecha'";
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$objeto=$conec->get_objeto();
		
		$this->datos_cuenta_historial($objeto->cup_cux_id,$objeto->cup_fecha,$monto,$pagado,$moneda);
		
		if($moneda=='1')
			$moneda="Bs";
		else
			$moneda='$us';
		
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
		
		$myday = setear_fecha(strtotime($objeto->cup_fecha));
		////
		
		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=CUENTAS&id='.$_POST['cux_id'].'\';"></td></tr></table>
				';
		?>
		<br><br><div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td  width="40%">
				    <strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong>
				   </td>
				    <td><center><strong><h3>NOTA DE <?php if($objeto->cux_tipo==1) echo 'COBRO'; else echo 'PAGO'; ?></h3></strong></center></p></td>
				    <td  width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
				    <strong>Fecha: </strong> <?php echo $myday;?> <br>
					<strong>Tipo de Cambio: </strong> <?php echo $objeto->cup_tipo_cambio;?> <br>
					<strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cup_usu_id);?> <br><br>
					</td>
				    <td align="right">
					
					</td>
				  </tr>
				 
			</table>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Cuenta</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>			
				</thead>
				<tbody>
				<tr>
					<td><?php echo $objeto->cux_concepto;?></td>
					<td><?php echo $objeto->cup_monto;?></td>
					<td><?php if($objeto->cup_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>	
				</tbody></table>
				<br>
				<table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >
				
				   <tr>
				    <td colspan="2">
				    <strong>Monto: </strong> <?php echo $monto.' '.$moneda;?> <br>
					<strong>Pagado: </strong> <?php echo $pagado.' '.$moneda;;?> <br>
					<strong>Saldo: </strong> <?php echo ($monto-$pagado).' '.$moneda;;?> <br><br>
					</td>
				  </tr>
				 
			</table>
			
			<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>
			
			</center></div><br>
				
		<?php		
		
	}
	
	
	
	function nota_de_pago($pago)
	{	
		$conec= new ADO();
		
		$sql = "select 
		cup_cux_id,cup_monto,cup_moneda,cup_tipo_cambio,cup_fecha,cup_usu_id,cux_concepto,cux_tipo 
		from 
		cuentax_pago inner join cuentasx on (cup_cux_id=cux_id) 
		where cup_id='$pago'"; 
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$objeto=$conec->get_objeto();
		
		$this->datos_cuenta($objeto->cup_cux_id,$monto,$pagado,$moneda);
		
		if($moneda=='1')
			$moneda="Bs";
		else
			$moneda='$us';
		
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
		
		$myday = setear_fecha(strtotime($objeto->cup_fecha));
		////
		
		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=CUENTAS&id='.$_POST['cux_id'].'\';"></td></tr></table>
				';
		?>
		<br><br><div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td  width="40%">
				    <strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong>
				   </td>
				    <td><center><strong><h3>NOTA DE <?php if($objeto->cux_tipo==1) echo 'COBRO'; else echo 'PAGO'; ?></h3></strong></center></p></td>
				    <td  width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
				    <strong>Fecha: </strong> <?php echo $myday;?> <br>
					<strong>Tipo de Cambio: </strong> <?php echo $objeto->cup_tipo_cambio;?> <br>
					<strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cup_usu_id);?> <br><br>
					</td>
				    <td align="right">
					
					</td>
				  </tr>
				 
			</table>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Cuenta</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>			
				</thead>
				<tbody>
				<tr>
					<td><?php echo $objeto->cux_concepto;?></td>
					<td><?php echo $objeto->cup_monto;?></td>
					<td><?php if($objeto->cup_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>	
				</tbody></table>
				<br>
				<table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >
				
				   <tr>
				    <td colspan="2">
				    <strong>Monto: </strong> <?php echo $monto.' '.$moneda;?> <br>
					<strong>Pagado: </strong> <?php echo $pagado.' '.$moneda;;?> <br>
					<strong>Saldo: </strong> <?php echo ($monto-$pagado).' '.$moneda;;?> <br><br>
					</td>
				  </tr>
				 
			</table>
			
			<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>
			
			</center></div><br>
				
		<?php		
		
	}
	
	function nombre_persona($usuario)
	{
		$conec= new ADO();
		
		$sql="select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->int_nombre.' '.$objeto->int_apellido; 
			
	}
	
	function datos_cuenta($cuenta,&$monto,&$pagado,&$moneda,&$des="")
	{
		$conec= new ADO();
		
		$sql = "select cux_monto,cux_moneda,cux_concepto from cuentasx where cux_id='$cuenta'"; 
		
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();

		$moneda=$objeto->cux_moneda;
		
		$monto=$objeto->cux_monto;
		
		$des=$objeto->cux_concepto;
		
		$sql = "select sum(cup_monto) as pagado from cuentax_pago where cup_cux_id='$cuenta' AND cup_estado='Pagado'"; 
		
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();
			
		$pagado=$objeto->pagado;
	}
		
	function dibujar_busqueda()
	{
		?>
		<script>		
		function ejecutar_script(id,tarea){
				var txt = 'Esta seguro de anular la Deuda de la Persona?';
				
				$.prompt(txt,{ 
					buttons:{Anular:true, Cancelar:false},
					callback: function(v,m,f){
						
						if(v){
								location.href='gestor.php?mod=gendeudaint&tarea='+tarea+'&id='+id;
						}
												
					}
				});
			}

		</script>
		<?php
		$this->formulario->dibujar_cabecera();
		
		$this->dibujar_listado();
	}
	
		
	function set_opciones()
	{
				
		$nun=0;
		
		if($this->verificar_permisos('VER'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='VER';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_search.png';
			$this->arreglo_opciones[$nun]["nombre"]='VER';
			$nun++;
		}
		
		if($this->verificar_permisos('ANULAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='ANULAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/no.png';
			$this->arreglo_opciones[$nun]["nombre"]='ANULAR';
			$this->arreglo_opciones[$nun]["script"]='ok';
			$nun++;
		}
		
		if($this->verificar_permisos('CUENTAS'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='CUENTAS';
			$this->arreglo_opciones[$nun]["imagen"]='images/cuenta.png';
			$this->arreglo_opciones[$nun]["nombre"]='PAGOS';
			$nun++;
		}
		
		if($this->verificar_permisos('PAGO_INTERES_CUENTASX'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='PAGO_INTERES_CUENTASX';
			$this->arreglo_opciones[$nun]["imagen"]='images/pago_interes.png';
			$this->arreglo_opciones[$nun]["nombre"]='PAGO DE INTERESES';
			$nun++;
		}
		
	}
	
	function dibujar_listado()
	{
		$sql="SELECT cux_id,cux_monto,cux_moneda,cux_concepto,cux_fecha,cux_estado,cux_cue_es,cux_cue_cp,int_nombre,int_apellido,cux_tipo
		FROM 
		cuentasx inner join interno on (cux_int_id=int_id)
		
		";
		
		$this->set_sql($sql,' order by cux_id desc');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Persona</th>
				<th>Tipo</th>
				<th>Observación</th>
				<th>Monto</th>
				<th>Pagado</th>
				<th>Saldo</th>
				<th>Fecha</th>
				<th>Estado</th>
	            <th class="tOpciones" width="100px">Opciones</th>
			</tr>
			
		<?PHP
	}
	
	function mostrar_busqueda()
	{
		$conversor = new convertir();
		
		for($i=0;$i<$this->numero;$i++)
			{
				
				$objeto=$this->coneccion->get_objeto();
				echo '<tr>';
									
					echo "<td>";
						echo $objeto->int_nombre.' '.$objeto->int_apellido;
					echo "&nbsp;</td>";
					echo "<td>";
						if($objeto->cux_tipo=='1') echo ' X Cobrar'; else echo ' X Pagar';
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->cux_concepto;
					echo "&nbsp;</td>";
					$this->datos_cuenta($objeto->cux_id,$monto,$pagado,$moneda);
					echo "<td>";
						echo $monto; if($objeto->cux_moneda=='1') echo ' Bs'; else echo ' $us';
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $pagado; if($objeto->cux_moneda=='1') echo ' Bs'; else echo ' $us';
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $monto-$pagado; if($objeto->cux_moneda=='1') echo ' Bs'; else echo ' $us';
					echo "&nbsp;</td>";
					
	
					
					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->cux_fecha);
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->cux_estado;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $this->get_opciones($objeto->cux_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function datos()
	{
		if($_POST)
		{
			return true;
		}
		else
			return false;	
	}
	
	function formulario_tcp($tipo)
	{
		$conec= new ADO();
		
		$sql="select * from interno";
		$conec->ejecutar($sql);		
		$nume=$conec->get_num_registros();
		$personas=0;
		if($nume > 0)
		{
			$personas=1;
		}
				switch ($tipo)
				{
					case 'ver':{
								$ver=true;
								break;
								}
							
					case 'cargar':{
								$cargar=true;
								break;
								}
				}
				
				$url=$this->link.'?mod='.$this->modulo;
				
				$red=$url;
				
				if(!($ver))
				{
					$url.="&tarea=".$_GET['tarea'];
				}
				
				if($cargar)
				{
					$url.='&id='.$_GET['id'];
				}

		
		    $this->formulario->dibujar_tarea();
			
			$tipocambio=$this->tc;
		
			if($this->mensaje<>"")
			{
				$this->formulario->dibujar_mensaje($this->mensaje);
			}
			?>
			<script>
			function ValidarMoneda(e)
			{ 			   
				evt = e ? e : event;
				tcl = (window.Event) ? evt.which : evt.keyCode;
				if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
				{
					return false;
				}
				return true;
			}
			function enviar_persona(frm,n)
			{
			  
			   var persona = document.frm_persona.interno.value;
			   var cco_id = document.frm_persona.cco_id.options[document.frm_persona.cco_id.selectedIndex].value;
			   
			   var cp = document.frm_persona.cp.options[document.frm_persona.cp.selectedIndex].value;
			   var ind_concepto = document.frm_persona.ind_concepto.value;
			   var monto = document.frm_persona.monto.value;
			   var moneda = document.frm_persona.moneda.options[document.frm_persona.moneda.selectedIndex].value;
			   var interes_mensual = document.frm_persona.interes_mensual.value;
			   
			   
			   if (persona!="" && cco_id!="" && cp!="" && ind_concepto!="" && monto!="" && moneda!="" && interes_mensual!="")
			   {			      
				  frm.submit();				  
			   }
			   else
				 $.prompt('Los campos marcado con (*) son requeridos',{ opacity: 0.8 });
						  
			}
			
			function cargar_cuenta(id)
			{
				
				var	valores="tid="+id+"&t=6";
				ejecutar_ajax('ajax.php','cuenta2',valores,'POST');
				
				var mid=parseInt(id)
				
				
				if(mid < 2)
				{	
					mid=2;
					
				}
				else
				{
					if(mid > 1)
					{	
						mid=1;
						
					}
				}	
				
				var	valores="tid="+mid+"&t=5";
				ejecutar_ajax('ajax.php','cuenta',valores,'POST');	
			}
			function cambiar_titulo(id)
			{
				if(id==1)
				{
					$('#titulo_etiqueta1').html('Cuenta de Egreso');
					$('#titulo_etiqueta2').html('Cuenta de Ingreso');
				}
				else
				{
					$('#titulo_etiqueta1').html('Cuenta de Ingreso');
					$('#titulo_etiqueta2').html('Cuenta de Egreso');
				}
				
				
			}
			function reset_interno()
			{
				document.frm_persona.interno.value="";
				document.frm_persona.nombre_persona.value="";
			}
			</script>
        <!--AutoSuggest-->
		<script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
		<link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
		<!--AutoSuggest-->
        <!--FancyBox-->
        <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
		<script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
		<script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <!--FancyBox-->
        <table align=right border=0><tr><td><a style="float:left; margin:0 0 0 7px;" class="group" href="sueltos/llamada.php?accion=agregar_divisa"><img border="0" src="images/compra_venta_divisa.png"></a></td></tr></table>
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_persona" name="frm_persona" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
							   <div id="CajaInput">
								   <?php
								   if($personas<>0)
								   {
								   ?>
										<input name="interno" id="interno" type="hidden" readonly="readonly" class="caja_texto" value="<?php echo $_POST['interno']?>" size="2">
										<input name="nombre_persona" <? if($_GET['change']=="ok"){ ?>readonly="readonly" <? } ?> id="nombre_persona"  type="text" class="caja_texto" value="<?php echo $_POST['nombre_persona']?>" size="35">
                                        
                                        <a class="group" style="float:left; margin:0 0 0 7px;float:right;"  href="sueltos/llamada.php">
										<img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
										</a>
                                        <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_interno();">
										<img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
										</a>
									<?php
								   }
								   else
								   {
										echo 'No se le asigno ningúna personas, para poder cargar las personas.';
									}
								   ?>
								</div>
							    <div id="CajaInput">
							   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                               
							   Tipo de Cambio&nbsp;<input type="text" name="tca" id="tca" size="5" value="<?php echo $tipocambio;?>" readonly="readonly">
							
							   </div>
							</div>
							 <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Tipo</div>
							   <div id="CajaInput">
							   <select name="tipo" class="caja_texto" onchange="cambiar_titulo(this.value);cargar_cuenta(this.value);">
							   <option value="">Seleccione</option>
							   <option value="1">X Cobrar</option>
							   <option value="2">X Pagar</option>
							   </select>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
						   <div class="Etiqueta" ><span class="flechas1">*</span>Centro Costo</div>
						   <div id="CajaInput">
							   <select name="cco_id" class="caja_texto">
							   <option value="">Seleccione</option>
							   <?php 		
								$fun=NEW FUNCIONES;		
								$fun->combo("select cco_id as id,cco_descripcion as nombre from centrocosto where cco_padre_id = 0 order by cco_descripcion asc",$_POST['cco_id']);		
								?>
							   </select>
							</div>
							</div>
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" id="titulo_etiqueta1">Cuenta de Entrada/Salida</div>
							   <div id="CajaInput">
							     <div id="cuenta">
								   <select name="es" id="es" class="caja_texto">
								   <option value="">Seleccione</option>
								   </select>
								  </div>
								</div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" id="titulo_etiqueta2"><span class="flechas1">*</span>Cuenta de Cobro/Pago</div>
							   <div id="CajaInput">
							     <div id="cuenta2">
								   <select name="cp" id="cp" class="caja_texto">
								   <option value="">Seleccione</option>
								   </select>
								  </div>
								</div>
							</div>
							<!--Fin-->
							<!--Fin-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Observación</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="ind_concepto" id="ind_concepto" size="40" maxlength="250" value="<?php echo $_POST['ind_concepto'];?>">
							   </div>
							</div>
							<!--Fin-->
							
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Monto</div>	  
								<div id="CajaInput">
								 <input type="text" name="monto" id="monto" size="5" value="<?php echo $_POST['monto']?>" onkeypress="return ValidarMoneda(event);">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Moneda</div>
							   <div id="CajaInput">
						
									    <select name="moneda" class="caja_texto">
										<option value="" >Seleccione</option>
										<option value="1" <?php if($_POST['moneda']=='1') echo 'selected="selected"'; ?>>Bolivianos</option>
										<option value="2" <?php if($_POST['moneda']=='2') echo 'selected="selected"'; ?>>Dolares</option>
										</select>
							   </div>
							</div>
							<!--Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Interes Mensual</div>	  
								<div id="CajaInput">
								 <input type="text" name="interes_mensual" id="interes_mensual" size="5" value="<?php echo $_POST['interes_mensual']?>" onkeypress="return ValidarMoneda(event);">&nbsp;%
							   </div>
							</div>
							<!--Fin-->
							
						</div>
					
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
								<input type="hidden" class="boton" name="persona" value="ok">
								<input type="button" class="boton" name="" value="Generar Deuda" onClick="enviar_persona(this.form);">
								</center>
						   </div>
						</div>
				</div>
			</form>
		</div>
        <script>
		var options1 = {
					script:"sueltos/persona.php?json=true&",
					varname:"input",
					minchars:1,
					timeout:10000,
					noresults:"No se encontro ninguna persona",
					json:true,
					callback: function (obj) { document.getElementById('interno').value = obj.id; f_particular(); }
				};
		var as_json1 = new _bsn.AutoSuggest('nombre_persona', options1);
		</script>
        <script type="text/javascript">
			$(document).ready(function(){
				$("a.group").fancybox({
					'hideOnContentClick': false,
					'overlayShow'	: true,
					'zoomOpacity'	: true,
					'zoomSpeedIn'	: 300,
					'zoomSpeedOut'	: 200,
					'overlayOpacity':0.5,
					
					'frameWidth'	:700,
					'frameHeight'	:350,
					'type'			:'iframe'
				});
				
				$('a.close').click(function(){
				 $(this).fancybox.close();
				});

			});
		</script>
		<?php
	}
	
	function insertar_tcp()
	{
		$conec= new ADO();
			
		$sql="select cja_cue_id from cajero where cja_usu_id = '".$this->usu->get_id()."'";
		
		$conec->ejecutar($sql);		

		$nume=$conec->get_num_registros();

		if($nume > 0)
		{
			$obj = $conec->get_objeto();
			
			$caja=$obj->cja_cue_id;
			
			$sql="insert into cuentasx(cux_int_id,cux_monto,cux_moneda,cux_concepto,cux_fecha,cux_usu_id,cux_cue_es,cux_cco_id,cux_cue_cp,cux_estado,cux_tipo,cux_tipo_cambio,cux_interes_mensual)
			values('".$_POST['interno']."','".$_POST['monto']."','".$_POST['moneda']."','".$_POST['ind_concepto']."','".date('Y-m-d')."','".$this->usu->get_id()."','".$_POST['es']."','".$_POST['cco_id']."','".$_POST['cp']."','Pendiente','".$_POST['tipo']."','".$_POST['tca']."','".$_POST['interes_mensual']."')
			";
			
			$conec->ejecutar($sql,false);	
			
			$llave=mysql_insert_id();
			
			///**REFLEJO EN LAS CUENTAS**///
			
			include_once("clases/registrar_comprobantes.class.php");
			 
			$comp = new COMPROBANTES();		
			
			
			//if($_POST['moneda']=='1')
			//{
				$subtotal=$_POST['monto'];
			//}
			//else
			//{
				//$subtotal=$_POST['monto']*$_POST['tca'];
			//}
			
			if($_POST['tipo']=='1')
			{
				$tipo=2;
			}
			else
			{
				$tipo=1;
			}
			
			
			$cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$_POST['tca'],$_POST['moneda'],'',$_POST['interno'],$this->usu->get_id(),$tipo,'1','cuentasx',$llave);			   
			
			
			if($tipo==1)
			{
				//$comp->ingresar_detalle($cmp_id,$subtotal,$caja,0);
			
				//$comp->ingresar_detalle($cmp_id,($subtotal * (-1)),$_POST['es'],$_POST['cco_id'],$_POST['ind_concepto']);
				
				
				if($_POST['moneda']=='1')
				{
					$comp->ingresar_detalle($cmp_id,$subtotal,$caja,0);

					$comp->ingresar_detalle($cmp_id,($subtotal * (-1)),$_POST['es'],$_POST['cco_id'],$_POST['ind_concepto']);
				}
				else
				{
					$comp->ingresar_detalle($cmp_id,0,$caja,0,'',$subtotal);

					$comp->ingresar_detalle($cmp_id,0,$_POST['es'],$_POST['cco_id'],$_POST['ind_concepto'],($subtotal * (-1)));
				}
				
			}
			else
			{
				//$comp->ingresar_detalle($cmp_id,($subtotal*(-1)),$caja,0);
			
				//$comp->ingresar_detalle($cmp_id,$subtotal,$_POST['es'],$_POST['cco_id'],$_POST['ind_concepto']);
				
				if($_POST['moneda']=='1')
				{
					$comp->ingresar_detalle($cmp_id,($subtotal * (-1)),$caja,0);

					$comp->ingresar_detalle($cmp_id,$subtotal,$_POST['es'],$_POST['cco_id'],$_POST['ind_concepto']);
				}
				else
				{
					$comp->ingresar_detalle($cmp_id,0,$caja,0,'',($subtotal * (-1)));

					$comp->ingresar_detalle($cmp_id,0,$_POST['es'],$_POST['cco_id'],$_POST['ind_concepto'],$subtotal);
				}
				
			}
			
			
					
			///**FIN REFLEJO**///
			
			if($_POST['monto']==$_POST['saldo'])
			{
				$sql = "update cuentasx set cux_estado='Pagado' where cux_id='".$_POST['cux_id']."'"; 
				
				$conec->ejecutar($sql,false);
			}
			
			$this->comprobante_cuenta_x($llave);
			
		}
		else
		{
			$mensaje='No puede realizar ningun cobro, por que usted no esta registrado como cajero.';
			
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		}
	}
	
	function nombre_cuenta($id)
	{
		$conec= new ADO();
		
		$sql = "select cue_descripcion from cuenta where cue_id='$id'"; 
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->cue_descripcion;
	}
	
	function anular()
	{
	
		$conec= new ADO();
		
		$sql = "select ind_estado from interno_deuda where ind_id='".$_GET['id']."'"; 
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		if($objeto->ind_estado == 'Pendiente')
		{
			$sql="update interno_deuda set 
							ind_estado='Anulado'
							where ind_id = '".$_GET['id']."'";
							
			$conec->ejecutar($sql);
			
			$mensaje='Deuda Anulada Correctamente!!!';
		}
		else
		{
			$mensaje='La deuda no puede ser anulada, por que ya fue pagada o anulada anteriormente!!!';
		}

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function comprobante_cuenta_x($llave)
	{	
		$conec= new ADO();
		
		$sql = "select 
		cux_id,cux_cue_cp,cux_int_id,cux_cue_es,cux_monto,cux_moneda,cux_concepto,cux_fecha,cux_usu_id,cux_cco_id,cux_estado,cux_tipo,cux_tipo_cambio 
		from 
		cuentasx 
		where cux_id='$llave'"; 
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$objeto=$conec->get_objeto();
		
		$this->datos_cuenta($objeto->cux_id,$monto,$pagado,$moneda);
		
		if($moneda=='1')
			$moneda="Bs";
		else
			$moneda='$us';
		
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
		
		$myday = setear_fecha(strtotime($objeto->cux_fecha));
		////
		
		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=CUENTAS&id='.$_POST['cux_id'].'\';"></td></tr></table>
				';
		?>
		<br><br><div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td  width="33%">
				    <strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong>
				   </td>
				    <td width="34%"><p align="center" ><strong><h3>COMPROBANTE DE CUENTA POR <?php if($objeto->cux_tipo==1){ echo "COBRAR";} else { echo "PAGAR";}  ?></h3></strong></p></td>
				    <td  width="33%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
				    <strong>Fecha: </strong> <?php echo $myday;?> <br>
					<strong>Tipo de Cambio: </strong> <?php echo $objeto->cux_tipo_cambio;?> <br>
					<strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cux_usu_id);?> <br><br>
					</td>
				    <td align="right">
					
					</td>
				  </tr>
				 
			</table>
			<table   width="50%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Concepto</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>			
				</thead>
				<tbody>
				<tr>
					<td><?php echo $objeto->cux_concepto;?></td>
					<td><?php echo $objeto->cux_monto;?></td>
					<td><?php if($objeto->cux_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>	
				</tbody></table>
				<br>
				<table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >
				
				   <tr>
				    <td colspan="2">
				    <strong>Monto: </strong> <?php echo $monto.' '.$moneda;?> <br>
					<strong>Pagado: </strong> <?php echo $pagado.' '.$moneda;?> <br>
					<strong>Saldo: </strong> <?php echo ($monto-$pagado).' '.$moneda;?> <br><br>
					</td>
				  </tr>
				 
			</table>
			
			<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>
			
			</center></div><br>
				
		<?php		
		
	}
	
/////////////////////////////////////////////////////////--  PAGOS INTERESES --//////////////////////////////////////////////////////////////
	function cuentas_intereses()
	{
		$this->formulario->dibujar_tarea();
		
		$this->xpagar_intereses();
		
	}
	
	function xpagar_intereses()
	{
		if($_POST['cux_id']<>"")
		{
			$conec= new ADO();
			
			$sql="select cja_cue_id from cajero where cja_usu_id = '".$this->usu->get_id()."'";
			
			$conec->ejecutar($sql);		

			$nume=$conec->get_num_registros();

			if($nume > 0)
			{
				$obj = $conec->get_objeto();
				
				$caja=$obj->cja_cue_id;
				
				$sql = "insert into cuentax_pago_interes (cupi_cux_id,cupi_monto,cupi_moneda,cupi_tipo_cambio,cupi_fecha,cupi_usu_id,cupi_estado,cupi_monto_interes)
					values('".$_POST['cux_id']."','".$_POST['monto']."','".$_POST['monedacuenta']."','".$_POST['tca']."','".date('Y-m-d')."','".$this->usu->get_id()."','Pagado','".$_POST['interes']."')		
				"; 
				
				$conec->ejecutar($sql,false);
				
				$llave=mysql_insert_id();
				
				///**REFLEJO EN LAS CUENTAS**///
				
				include_once("clases/registrar_comprobantes.class.php");
				 
				$comp = new COMPROBANTES();		
				
				
				///INTERNO
				$sql="select 
				cux_id,cux_cue_cp,cux_int_id,cux_moneda,cux_concepto,cux_tipo,cux_cco_id 
				from 
				cuentasx	
				where 
				cux_id = '".$_POST['cux_id']."'";
				
				$conec->ejecutar($sql);		
				
				$obj = $conec->get_objeto();
				
				//if($_POST['monedacuenta']=='1')
				//{
					$subtotal=$_POST['interes'];
				//}
				//else
				//{
					//$subtotal=$_POST['interes']*$_POST['tca'];
				//}
				
				
				$cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$_POST['tca'],$_POST['monedacuenta'],'',$obj->cux_int_id,$this->usu->get_id(),$obj->cux_tipo,'1','cuentax_pago_interes',$llave);			   
				
				
				
				if($obj->cux_tipo==1)
				{
					if($_POST['monedacuenta']=='1')
					{
						$this->cuenta_cco_interes_xcobrar($cue,$cco);

						$comp->ingresar_detalle($cmp_id,$subtotal,$caja,0);

						$comp->ingresar_detalle($cmp_id,($subtotal * (-1)),$cue,$cco,'Pago de: '.$obj->cux_concepto);
					}
					else
					{
						$this->cuenta_cco_interes_xcobrar($cue,$cco);

						$comp->ingresar_detalle($cmp_id,0,$caja,0,'',$subtotal);
						
						$comp->ingresar_detalle($cmp_id,0,$cue,$cco,'Pago de: '.$obj->cux_concepto,($subtotal * (-1)));
					}
				}
				else
				{
					if($_POST['monedacuenta']=='1')
					{
						$this->cuenta_cco_interes_xpagar($cue,$cco);

						$comp->ingresar_detalle($cmp_id,($subtotal * (-1)),$caja,0);

						$comp->ingresar_detalle($cmp_id,$subtotal,$cue,$cco,'Pago de: '.$obj->cux_concepto);
					}
					else
					{
						$this->cuenta_cco_interes_xpagar($cue,$cco);

						$comp->ingresar_detalle($cmp_id,0,$caja,0,'',($subtotal * (-1)));

						$comp->ingresar_detalle($cmp_id,0,$cue,$cco,'Pago de: '.$obj->cux_concepto,$subtotal);
					}

				}
						
				///**FIN REFLEJO**///
				
				/*if($_POST['monto']==$_POST['saldo'])
				{
					$sql = "update cuentasx set cux_estado='Pagado' where cux_id='".$_POST['cux_id']."'"; 
					
					$conec->ejecutar($sql,false);
				}*/
				
				$this->nota_de_pago_interes($llave);
				
			}
			else
			{
				$mensaje='No puede realizar ningun cobro, por que usted no esta registrado como cajero.';
				
				$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
			}
			
		}
		else
		{
			$url=$this->link.'?mod='.$this->modulo.'&tarea=PAGO_INTERES_CUENTASX';
			
			$tipocambio=$this->tc;
			
			$this->datos_cuenta_interes($_GET['id'],$monto,$pagado,$moneda,$des);
			
			$saldo=$monto-$pagado;
			
			if($moneda=='1')
			{
				$mon="Bs";
				
			}
			else
			{
				$mon='$us';
				
			}
			
			?>
			<script>
			function enviar_formulario_interes(){
					
					var interes=parseFloat(document.frm_sentencia.interes.value);
					var monedacuenta=document.frm_sentencia.monedacuenta.value;
					var saldo=parseFloat(document.frm_sentencia.saldo.value);
					
					if(interes > 0)
					{
						if(interes > saldo)
						{
							$.prompt('El Monto debe ser Menor o Igual que el saldo y no Mayor.',{ opacity: 0.8 });
						}
						else
						{
							document.frm_sentencia.submit();
						}
					}
					else
					{
						$.prompt('Para registrar el pago debe introducir el monto.',{ opacity: 0.8 });			   
					}
				}
			function anular_pago_interes(id,tarea){
					var txt='Esta seguro de anular este pago de Cuota?';
					$.prompt(txt,{  
						buttons:{Aceptar:true, Cancelar:false},
						callback: function(v,m,f){
							
							if(v){
									location.href='gestor.php?mod=cuentasx&tarea='+tarea+'&acc=anular&cupi_id='+id;
							}
													
						}			
			
					}); 
				}
			</script>
            
			<table align=right border=0><tr><td><a href="gestor.php?mod=cuentasx" title="VOLVER"><img border="0" width="20" src="images/back.png"></a></td></tr></table>
			
            <table align=right border=0><tr><td><a style="float:left; margin:0 0 0 7px;" class="group" href="sueltos/llamada.php?accion=agregar_divisa"><img border="0" src="images/compra_venta_divisa.png"></a></td></tr></table>
            
            <!--FancyBox-->
            <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
            <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
            <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
            <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
            <!--FancyBox-->
            <div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Pago de Cuenta: <?php echo $des; ?></div>
						<div id="ContenedorSeleccion">
							
							
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto</div>
							   <div id="CajaInput">
							  <input type="text" name="montototal" id="montototal" size="10" value="<?php echo $monto;?>" readonly="readonly"><?php echo $mon;?>
							   </div>
							   <div id="CajaInput">
							   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							   
							   Tipo de Cambio&nbsp;<input type="text" name="tca" id="tca" size="5" value="<?php echo $tipocambio;?>" readonly="readonly">
							   <input type="hidden" name="cux_id" id="cux_id" value="<?php echo $_GET['id'];?>">
							   <input type="hidden" name="monedacuenta" id="monedacuenta" value="<?php echo $moneda;?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Pagado</div>
							   <div id="CajaInput">
							   <input type="text" name="pagado" id="pagado" size="10" value="<?php echo $pagado;?>" readonly="readonly"><?php echo $mon;?>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Saldo</div>
							   <div id="CajaInput">
							   <input type="text" name="saldo" id="saldo" size="10" value="<?php echo $saldo;?>" readonly="readonly"><?php echo $mon;?>&nbsp;&nbsp;&nbsp;
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto a Pagar</div>
							   <div id="CajaInput">
							   <input type="text" name="monto" id="monto" size="10" value=""><?php echo $mon;?>&nbsp;&nbsp;&nbsp;Nota:<font color="#FF0000">( Al Realizar el ultimo pago debe ser identico al Saldo)</font>
							   </div>
							</div>
							Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto Interes (Saldo)</div>
							   <div id="CajaInput">
							   <input type="text" name="interes" id="interes" size="10" value="<?php echo ($this->obtener_porc_interes($_GET['id'])/100) * $saldo; ?>"><?php echo $mon;?>&nbsp;<?php echo $this->obtener_porc_interes($_GET['id']).'%'; ?>
							   </div>
							</div>
							<!--Fin-->
						</div>
						
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
								<?php
								if(!($ver))
								{
									?>
									<input type="button" class="boton" name="" value="Pagar Cuenta" onclick="javascript:enviar_formulario_interes()">
									<?php
								}
								else
								{
									?>
									<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
									<?php
								}
								?>
								</center>
						   </div>
						</div>
				</div>
			</form>
		</div>
        <script type="text/javascript">
			$(document).ready(function(){
				$("a.group").fancybox({
					'hideOnContentClick': false,
					'overlayShow'	: true,
					'zoomOpacity'	: true,
					'zoomSpeedIn'	: 300,
					'zoomSpeedOut'	: 200,
					'overlayOpacity':0.5,
					
					'frameWidth'	:700,
					'frameHeight'	:350,
					'type'			:'iframe'
				});
				
				$('a.close').click(function(){
				 $(this).fancybox.close();
				});

			});
		</script>
        <?php
        
		$this->dibujar_encabezado_pagos_interes();
		
		$this->mostrar_busqueda_pagos_interes();
		
		?>
		
		<?php
		}
			
	}
	
	function datos_cuenta_interes($cuenta,&$monto,&$pagado,&$moneda,&$des="")
	{
		$conec= new ADO();
		
		$sql = "select cux_monto,cux_moneda,cux_concepto from cuentasx where cux_id='$cuenta'"; 
		
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();

		$moneda=$objeto->cux_moneda;
		
		$monto=$objeto->cux_monto;
		
		$des=$objeto->cux_concepto;
		
		$sql = "select sum(cup_monto) as pagado from cuentax_pago where cup_cux_id='$cuenta' AND cup_estado='Pagado'"; 
		
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();
			
		$pagado=$objeto->pagado;
	}
	
	function obtener_porc_interes($id)
	{
		$conec= new ADO();
		
		$sql = "select cux_id,cux_interes_mensual from cuentasx where cux_id='$id'"; 
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->cux_interes_mensual;
	}
	
	function cuenta_cco_interes_xcobrar(&$cue,&$cco)
	{
		$conec= new ADO();
		
		$sql="select * from ad_parametro";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$cue=$objeto->par_cpc_cue;
		
		$cco=$objeto->par_cpc_cc;
	}
	
	function cuenta_cco_interes_xpagar(&$cue,&$cco)
	{
		$conec= new ADO();
		
		$sql="select * from ad_parametro";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$cue=$objeto->par_cpp_cue;
		
		$cco=$objeto->par_cpp_cc;
	}
	
	function nota_de_pago_interes($pago)
	{	
		$conec= new ADO();
		
		$sql = "select 
		cupi_cux_id,cupi_monto,cupi_moneda,cupi_tipo_cambio,cupi_fecha,cupi_usu_id,cupi_monto_interes,cux_concepto,cux_tipo 
		from 
		cuentax_pago_interes inner join cuentasx on (cupi_cux_id=cux_id) 
		where cupi_id='$pago'"; 
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$objeto=$conec->get_objeto();
		
		$this->datos_cuenta_interes($objeto->cupi_cux_id,$monto,$pagado,$moneda);
		
		if($moneda=='1')
			$moneda="Bs";
		else
			$moneda='$us';
		
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
		
		$myday = setear_fecha(strtotime($objeto->cupi_fecha));
		////
		
		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=PAGO_INTERES_CUENTASX&id='.$_POST['cux_id'].'\';"></td></tr></table>
				';
		?>
		<br><br><div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td  width="40%">
				    <strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong>
				   </td>
				    <td><center><strong><h3>NOTA DE <?php if($objeto->cux_tipo==1) echo 'COBRO '; else echo 'PAGO '; ?>DE INTERES</h3></strong></center></p></td>
				    <td  width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
				    <strong>Fecha: </strong> <?php echo $myday;?> <br>
					<strong>Tipo de Cambio: </strong> <?php echo $objeto->cupi_tipo_cambio;?> <br>
					<strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cupi_usu_id);?> <br><br>
					</td>
				    <td align="right">
					
					</td>
				  </tr>
				 
			</table>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Cuenta</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>			
				</thead>
				<tbody>
				<tr>
					<td><?php echo $objeto->cux_concepto;?></td>
					<td><?php echo $objeto->cupi_monto_interes;?></td>
					<td><?php if($objeto->cupi_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>	
				</tbody></table>
				<br>
				<!--<table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >
				
				   <tr>
				    <td colspan="2">
				    <strong>Monto: </strong> <?php echo $monto.' '.$moneda;?> <br>
					<strong>Pagado: </strong> <?php echo $pagado.' '.$moneda;;?> <br>
					<strong>Saldo: </strong> <?php echo ($monto-$pagado).' '.$moneda;;?> <br><br>
					</td>
				  </tr>
				 
			</table>-->
			
			<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>
			
			</center></div><br>
				
		<?php		
		
	}
	
	function dibujar_encabezado_pagos_interes()
	{

		?><div style="clear:both;"></div><center>
		<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
			<tr>
				
				<td><center><strong><h3>LISTADO DE PAGOS</h3></strong></center></p></td>
				
			</tr>
				 
		</table>
        <br />
		<table class="tablaLista" cellpadding="0" cellspacing="0" width="60%">
			<thead>
				<tr>

					<th >

						Nro.

					</th>
					<th >

						Monto

					</th>

				
					
					<th >

						Moneda

					</th>
					<th >

						Tipo Cambio

					</th>					
					<th >

						Fecha

					</th>
					
					<th class="tOpciones" width="100px">

						Opciones

					</th>

				</tr>
			</thead>
			<tbody>
		<?PHP

	}

	

	function mostrar_busqueda_pagos_interes()
	{
		$conversor = new convertir();
		$conec=new ADO();

		$sql="SELECT 
		cupi_id,cupi_cux_id,cupi_monto,cupi_moneda,cupi_tipo_cambio,cupi_fecha,cupi_hora,cupi_usu_id,cupi_estado,cupi_monto_interes
		FROM 
		cuentax_pago_interes
		WHERE 
		cupi_cux_id='".$_GET['id']."' AND cupi_estado='Pagado'
		ORDER BY 
		cupi_id ASC";

		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();
		$sumatorio=0;
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();
			$sumatorio+=$objeto->cupi_monto_interes;
			echo '<tr class="busqueda_campos">';

			?>
				
				<td align="left">
					<center>
					<?php echo $i+1; ?>
					</center>
				</td>
				
                <td align="left">
					<center>
					<?php echo $objeto->cupi_monto_interes;?>
					</center>
				</td>
                
                <td align="left">
					<center>
					<?php if($objeto->cupi_moneda==1){echo "Bolivianos"; } else { echo "Dolares"; }?>
					</center>
				</td>
                
                <td align="left">
					<center>
					<?php echo $objeto->cupi_tipo_cambio;?>
					</center>
				</td>
				
				<td align="left">
					<center>
					
					<?php echo $conversor->get_fecha_latina($objeto->cupi_fecha);?>
					</center>
				</td>				

				<td>

					<center>

					<a href="gestor.php?mod=cuentasx&tarea=PAGO_INTERES_CUENTASX&acc=ver&cupi_id=<?php echo $objeto->cupi_id;?>&cupi_fecha=<?php echo $objeto->cupi_fecha;?>"><img src="images/ver.png" alt="VER" title="VER" border="0"></a>
					
				<!--	gestor.php?mod=credito_prendario&tarea=INTERES&acc=anular&cre_id=<?php //echo $objeto->cpi_id;?> -->
					<a href="javascript:anular_pago_interes('<?php echo $objeto->cupi_id;?>','PAGO_INTERES_CUENTASX');" target="contenido" ><img src="images/anular.png" alt="ANULAR" title="ANULAR" border="0"></a>

					</center>

				</td>

			<?php

			echo "</tr>";

			$conec->siguiente();

		}
		echo "</tbody>";
			?>
			<tfoot>	
				<tr>
				<td><b>Total</b></td>
				<td><? echo $sumatorio; ?></td>
				<td colspan="4" >&nbsp;</td></tr>
			</tfoot>			
			<?		

		echo "</table></center><br>";
	}
	
	function anular_pago_interes($id)
	{
	
		$conec= new ADO();
		
		$sql="update cuentax_pago_interes set 
							cupi_estado='Anulado'
							where cupi_id = '$id'";
							
		$conec->ejecutar($sql);
		
		
		include_once("clases/registrar_comprobantes.class.php");
			 
		$comp = new COMPROBANTES();	
		
		$comp->anular_comprobante_tabla('cuentax_pago_interes',$id);
		
		
			
		$mensaje='Pago Anulado Correctamente!!!';
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function mostrar_nota_pago_historial_interes($pago,$fecha)
	{	
		$conec= new ADO();
		
		$sql = "SELECT 
		cupi_cux_id,cupi_monto,cupi_moneda,cupi_tipo_cambio,cupi_fecha,cupi_usu_id,cupi_monto_interes,cux_concepto,cux_tipo 
		FROM 
		cuentax_pago_interes INNER JOIN cuentasx ON (cupi_cux_id=cux_id) 
		WHERE cupi_id='$pago' AND cupi_fecha <= '$fecha'";
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$objeto=$conec->get_objeto();
		
		$this->datos_cuenta_historial_interes($objeto->cupi_cux_id,$objeto->cupi_fecha,$monto,$pagado,$moneda);
		
		if($moneda=='1')
			$moneda="Bs";
		else
			$moneda='$us';
		
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
		
		$myday = setear_fecha(strtotime($objeto->cup_fecha));
		////
		
		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=CUENTAS&id='.$_POST['cux_id'].'\';"></td></tr></table>
				';
		?>
		<br><br><div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td  width="40%">
				    <strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong>
				   </td>
				    <td><center><strong><h3>NOTA DE <?php if($objeto->cux_tipo==1) echo 'COBRO'; else echo 'PAGO'; ?></h3></strong></center></p></td>
				    <td  width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
				    <strong>Fecha: </strong> <?php echo $myday;?> <br>
					<strong>Tipo de Cambio: </strong> <?php echo $objeto->cupi_tipo_cambio;?> <br>
					<strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cupi_usu_id);?> <br><br>
					</td>
				    <td align="right">
					
					</td>
				  </tr>
				 
			</table>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Cuenta</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>			
				</thead>
				<tbody>
				<tr>
					<td><?php echo $objeto->cux_concepto;?></td>
					<td><?php echo $objeto->cupi_monto_interes;?></td>
					<td><?php if($objeto->cupi_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>	
				</tbody></table>
				<br>
				<!--<table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >
				
				   <tr>
				    <td colspan="2">
				    <strong>Monto: </strong> <?php echo $monto.' '.$moneda;?> <br>
					<strong>Pagado: </strong> <?php echo $pagado.' '.$moneda;;?> <br>
					<strong>Saldo: </strong> <?php echo ($monto-$pagado).' '.$moneda;;?> <br><br>
					</td>
				  </tr>
				 
			</table>-->
			
			<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>
			
			</center></div><br>
				
		<?php		
		
	}
	
	function datos_cuenta_historial_interes($cuenta,$fecha,&$monto,&$pagado,&$moneda,&$des="")
	{
		$conec= new ADO();
		
		$sql = "select cux_monto,cux_moneda,cux_concepto from cuentasx where cux_id='$cuenta'"; 
		
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();

		$moneda=$objeto->cux_moneda;
		
		$monto=$objeto->cux_monto;
		
		$des=$objeto->cux_concepto;
		
		$sql = "select sum(cupi_monto_interes) as pagado from cuentax_pago_interes where cupi_cux_id='$cuenta' AND cupi_fecha <= '$fecha' AND cupi_estado='Pagado'"; 
		
		$conec->ejecutar($sql);	
		
		$objeto=$conec->get_objeto();
			
		$pagado=$objeto->pagado;
	}
}
?>