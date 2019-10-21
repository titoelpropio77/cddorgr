<?php

class PROFORMA extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function PROFORMA()
	{
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='proforma';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('PROFORMA');
		
		$this->usu=new USUARIO;
	}
	
	
	function dibujar_busqueda()
	{
			$this->formulario();
	}
	
	function formulario()
	{
	
		$this->formulario->dibujar_cabecera();
		
		$this->datos_venta($ci,$im);		
		
		?>
		
						<?php
						if($this->mensaje<>"")
						{
						?>
							<table width="100%" cellpadding="0" cellspacing="1" style="border:1px solid #DD3C10; color:#DD3C10;">
							<tr bgcolor="#FFEBE8">
								<td align="center">
									<?php
										echo $this->mensaje;
									?>
								</td>
							</tr>
							</table>
						<?php
						}
						?>
						
						<script type="text/javascript">
							/*
							function cargar_manzano(id)
							{
								cargar_lote(0);
								
								var	valores="tarea=manzanos&urb="+id;			

								ejecutar_ajax('ajax.php','manzano',valores,'POST');									
							}
							
							function cargar_lote(id)
							{
								var	valores="tarea=lotes&man="+id;			

								ejecutar_ajax('ajax.php','lote',valores,'POST');									
							}
							*/
							
							function cargar_uv(id)
							{
								cargar_lote(0);
								
								var	valores="tarea=uv&urb="+id;			
			
								ejecutar_ajax('ajax.php','uv',valores,'POST');									
							}
							
							function cargar_manzano(id,uv)
							{
								cargar_lote(0);
								
								var	valores="tarea=manzanos&urb="+id+"&uv="+uv;			
			
								ejecutar_ajax('ajax.php','manzano',valores,'POST');									
							}
							
							function cargar_lote(id,uv)
							{
								var	valores="tarea=lotes&man="+id+"&uv="+uv;			
			
								ejecutar_ajax('ajax.php','lote',valores,'POST');									
							}
							
							function obtener_valor_uv(){
								var axuUv = $('#ven_uv_id').val();
								var axuMan = $('#ven_man_id').val();
								
								cargar_lote(axuMan,axuUv);
							}
							
							function obtener_valor_manzano(){
								var auxUrb = $('#ven_urb_id').val();
								var auxUv = $('#ven_uv_id').val();
								
								cargar_manzano(auxUrb,auxUv);
							}
							
							function enviar(frm)
							{
							   var superficie = parseFloat(document.frm_sentencia.superficie.value);
							   var precio = parseFloat(document.frm_sentencia.precio.value);
							   var meses = parseFloat(document.frm_sentencia.meses.value);
							   
							   var urbanizacion = parseFloat(document.frm_sentencia.ven_urb_id.value);
							   var manzano = parseFloat(document.frm_sentencia.ven_man_id.value);
							   var lote = parseFloat(document.frm_sentencia.ven_lot_id.value);
							   //var descuentocontado = parseFloat(document.frm_sentencia.descuentocontado.value);
							   //if (superficie > 0 && precio > 0 && meses > 0 && meses >= 0 && descuentocontado >= 0)
							   if (superficie > 0 && precio > 0 && meses > 0 && meses >= 0 && urbanizacion != '' && manzano != '' && lote != '')
							   {
		
									document.frm_sentencia.submit();
								 
							   }
							   else
								 $.prompt('Los campos marcado con (*) son requeridos y deben se numeros.',{ opacity: 0.8 });
							}
							
							function cargar_datos(valor)
							{
								var datos = valor;
								var val = datos.split('-');
								document.frm_sentencia.valor_terreno.value=roundNumber((parseFloat(val[1])*parseFloat(val[2])),2);
								document.frm_sentencia.superficie.value=val[1];
								document.frm_sentencia.precio.value=val[2];
								document.frm_sentencia.prof_moneda.value=val[4];
								
								if(val[4]==1)
								{
									var simbolo_moneda_vm2='Precio m2 Bs';
									var simbolo_moneda_vt='Valor del Terreno Bs';
								}
								else
								{
									var simbolo_moneda_vm2='Precio m2 $us';
									var simbolo_moneda_vt='Valor del Terreno $us';
								}
								
								$('#simb_moneda_vm2').html('<span class="flechas1">*</span>' + simbolo_moneda_vm2);
								$('#simb_moneda_vt').html('<span class="flechas1">*</span>' + simbolo_moneda_vt);
								
								var v_vt=parseFloat(document.frm_sentencia.valor_terreno.value);
								var v_ci=parseFloat(document.frm_sentencia.ci.value);
								
								document.frm_sentencia.cuota_inicial.value=(v_ci * v_vt) / 100;
							}
							
							function ValidarNumero(e)
							{ 			   
								evt = e ? e : event;
								tcl = (window.Event) ? evt.which : evt.keyCode;
								if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0))
								{
									return false;
								}
								
								return true;
							}
							
							function ValidarNumeroReal(e)
							{ 			   
								evt = e ? e : event;
								tcl = (window.Event) ? evt.which : evt.keyCode;
								if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
								{
									return false;
								}
								return true;
							}	
							
							function calcular_valor_terreno()
							{
								var sup=parseFloat(document.frm_sentencia.superficie.value);
								var val=parseFloat(document.frm_sentencia.precio.value);
								
								document.frm_sentencia.valor_terreno.value=roundNumber((sup*val),2);
								
							}
							
							function limpiar_combos()
							{
								document.frm_sentencia.ven_urb_id.value='';
								document.frm_sentencia.ven_man_id.value='';
								document.frm_sentencia.ven_lot_id.value='';
							}			
						</script>
						<div id="Contenedor_NuevaSentencia">
						<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=proforma" method="POST" enctype="multipart/form-data">  
									<div id="FormSent">
										<div class="Subtitulo">Datos</div>
					                    <div id="ContenedorSeleccion">
											<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Urbanización</div>
							   <div id="CajaInput">
									<select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" onchange="cargar_uv(this.value);">
										   <option value="">Seleccione</option>
										   <?php 		
											$fun=NEW FUNCIONES;		
											$fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion",$_POST['ven_urb_id']);				
											?>
								   </select>
								   <input type="hidden" name="ci" id="ci"  value="<?php echo $ci; ?>">
								   <input type="hidden" name="im" id="im"  value="<?php echo $im; ?>">
							   </div>
							</div>
							<!--Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>UV</div>
							   <div id="CajaInput">
							   <div id="uv">
							   <select style="width:200px;" name="ven_uv_id" class="caja_texto">
							   <option value="">Seleccione</option>
							   <?php 		
								if($_POST['ven_urb_id']<>"")
								{
									$fun=NEW FUNCIONES;		
									$fun->combo("select uv_id as id,uv_nombre as nombre from uv where uv_urb_id='".$_POST['ven_urb_id']."' ",$_POST['ven_uv_id']);				
								}
								?>
							   </select>
							   </div>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Manzano</div>
							   <div id="CajaInput">
							   <div id="manzano">
							   <select style="width:200px;" name="ven_man_id" class="caja_texto" onchange="cargar_lote(this.value);">
							   <option value="">Seleccione</option>
							   <?php 		
								if($_POST['ven_urb_id']<>"")
								{
									$fun=NEW FUNCIONES;		
									$fun->combo("select man_id as id,man_nro as nombre from manzano where man_urb_id='".$_POST['ven_urb_id']."' ",$_POST['ven_man_id']);				
								}
								?>
							   </select>
							   </div>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Lote</div>
							   <div id="CajaInput">
							   <div id="lote">
							   <select style="width:200px;" name="ven_lot_id" class="caja_texto">
							   <option value="">Seleccione</option>
							   <?php 		
								if($_POST['ven_man_id']<>"")
								{
									$fun=NEW FUNCIONES;		
									$fun->combo("select lot_id as id,lot_nro as nombre from lote where lot_man_id='".$_POST['ven_man_id']."' ",$_POST['ven_lot_id']);				
								}
								?>
							   </select>
							   </div>
							   </div>
							</div>
							<!--Fin-->
                            <!--Inicio-->
							  <input readonly="true" type="hidden" name="prof_moneda" id="prof_moneda" size="5" value="<?php echo $_POST['prof_moneda'];?>">
							<!--Fin-->											
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Superficie</div>
								 <div id="CajaInput">
									<input class="caja_texto" readonly="readonly" name="superficie" id="superficie" size="12" value="<?php echo $_POST['superficie'];?>" type="text">
								</div>		
							</div>
							
							<div id="ContenedorDiv">
							   <div class="Etiqueta" id="simb_moneda_vm2" ><span class="flechas1">*</span >Precio m2</div>
								 <div id="CajaInput">
									<input class="caja_texto" <?php if($this->obtener_grupo_id($this->usu->get_id())!="Administradores") {?> readonly="readonly" <?php } ?> onKeyPress="return ValidarNumeroReal(event);" onKeyUp="javascript:calcular_valor_terreno();" name="precio" id="precio" size="12" value="<?php echo $_POST['precio'];?>" type="text">
										
								</div>		
							</div>	
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" id="simb_moneda_vt" ><span class="flechas1">*</span>Valor del Terreno</div>
							    <div id="CajaInput">
								  <input readonly="true" type="text" readonly="readonly" name="valor_terreno" id="valor_terreno" size="5" value="<?php echo $_POST['valor_terreno'];?>">
								</div>
								<div id="CajaInput">
								  &nbsp;&nbsp;&nbsp;Cuota Inicial&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="cuota_inicial" id="cuota_inicial" size="5" value="<?php echo $_POST['cuota_inicial'];?>"  onKeyPress="return ValidarNumero(event);">
							   </div>
							  
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Descuento (%)</div>
								<div id="CajaInput">
									<input class="caja_texto" name="descuento" onKeyPress="return ValidarNumero(event);" id="descuento" size="5" value="<?php if($_POST['descuento']<>"")  echo $_POST['descuento']; else echo '0';?>" type="text"> 
										
								</div>	
								<div id="CajaInput">
									  &nbsp;&nbsp;&nbsp;Meses Plazo<font color="#FF0000">*</font>&nbsp;&nbsp;&nbsp;<input class="caja_texto" onKeyPress="return ValidarNumero(event);" name="meses" id="meses" size="5" value="<?php if($_POST['meses']<>"")  echo $_POST['meses']; else echo '0';?>" type="text">
										
								</div>		
							</div>
                            <!--Fin-->
                            <!--Inicio
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Descuento al Contado</div>
								<div id="CajaInput">
									<input class="caja_texto" name="descuentocontado" onKeyPress="return ValidarNumero(event);" id="descuentocontado" size="5" value="<?php if($_POST['descuentocontado']<>"")  echo $_POST['descuentocontado']; else echo '0';?>" type="text">
										
								</div>	
										
							</div>
                            Fin-->			
						</div>
						
						<div id="ContenedorDiv">
							   <div id="CajaBotones">
									<center>
									<input type="hidden" class="boton" name="formu" value="ok">
									<input type="button" class="boton" name="" value="Generar Proforma" onClick="enviar();">
									</center>
							   </div>
						</div>
					</div>
		</form>	
		<div>
		<?php
	
		
		if($_POST['formu']=='ok')
			$this->mostrar_reporte();
	}
	
	function obtener_grupo_id($usu_id)
	{
		$conec= new ADO();
		
		$sql="SELECT usu_gru_id FROM ad_usuario WHERE usu_id='$usu_id'";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->usu_gru_id; 
	}
	
	function datos_venta(&$ci,&$im)
	{
		$conec= new ADO();
		
		$sql="select par_cuota_inicial,par_interes_mensual   
		from 
		ad_parametro 
		";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$ci=$objeto->par_cuota_inicial;
		
		$im=$objeto->par_interes_mensual;
		
	}
	
	function mostrar_reporte()
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
				</a></td></tr></table><br><br>
				';?>
						
			<?php
			$conec= new ADO();
		
			$sql="  select urb_nombre,man_nro,zon_nombre,zon_moneda,uv_nombre,lot_nro
					from lote
					inner join zona on (lot_zon_id=zon_id)
					inner join uv on (lot_uv_id=uv_id)
					inner join manzano on (lot_id='".$_POST['ven_lot_id']."' and lot_man_id=man_id)
					inner join urbanizacion on (man_urb_id=urb_id)
			";
			$conec->ejecutar($sql);
			
			$objeto=$conec->get_objeto();			
			?>	 

			<div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				<td width="40%" >
				<strong><?php echo _nombre_empresa; ?></strong><BR>
				<strong>Santa Cruz - Bolivia</strong>
				</td>
				<td  width="20%" ><p align="center" ><strong><h3><center>PROFORMA</center></h3></strong></p></td>
				<td  width="40%" ><div align="right"><img src="imagenes/micro.png" /></div></td>
			  </tr>
			  
			</table><br/>
			<table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>
						<b>Terreno</b>
					</th>
					<th>
						<b>Superficie</b>
					</th>
					<th>
						<b>Precio m2</b>
					</th>					
					
					<th>
						<b>Des. Global</b>
					</th>
					<!--<th>
						<b>Des. Contado</b>
					</th>-->
					
					<th class="tOpciones">Al Contado</th>
					<th >
						<b>Plazo</b>
					</th>
					<th >
						<b>Cuota Mensual</b>
					</th>
					<th>
						<b>Couta Inicial</b>
					</th>
					<th class="tOpciones">Al Credito</th>
		
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>
						<?php echo "Urbanización:$objeto->urb_nombre<br>Manzano:$objeto->man_nro<br>Lote:$objeto->lot_nro, Zona:$objeto->zon_nombre, UV:$objeto->uv_nombre"; ?>
					</td>
					<td>
						<?php echo $_POST['superficie']; ?> m2
					</td>
					<td>
						<?php echo $_POST['precio']; ?> <?php if($_POST['prof_moneda']==1) echo 'Bs'; else echo '$us'; ?>
					</td>
					
					<td>
						<?php echo $_POST['descuento']; ?> %
					</td>
					<!--<td>
						<?php echo $_POST['descuentocontado']; ?> %
					</td>-->
					<?php
					$dc=$_POST['descuentocontado']/100;
					
					///
					$sql="select par_interes_mensual 
					from 
					ad_parametro 
					";

					$conec->ejecutar($sql);
					
					$objeto=$conec->get_objeto();
					
					$interes_anual=$objeto->par_interes_mensual;
		
					///
					
					if($_POST['descuento']==0)
					{
						
						
						?>
						<td>
							<?php 
							$tot=$_POST['superficie']*$_POST['precio'];
							$con=$tot-($tot*$dc);
							echo round($con,2);
							
							$cuota=$this->cuota(($tot-$_POST['cuota_inicial']),$interes_anual,$_POST['meses']);
							
							?> <?php if($_POST['prof_moneda']==1) echo 'Bs'; else echo '$us'; ?>
						</td>
						<td>
						<?php echo $_POST['meses']; ?> Meses
						</td>
						<td>
						<?php echo round($cuota,2); ?> <?php if($_POST['prof_moneda']==1) echo 'Bs'; else echo '$us'; ?>
						</td>
						<td>
							<?php echo $_POST['cuota_inicial']; ?> <?php if($_POST['prof_moneda']==1) echo 'Bs'; else echo '$us'; ?>
						</td>
						<td>
							<?php 
							
							
							echo round((($cuota*$_POST['meses']) + $_POST['cuota_inicial']),2); ?> <?php if($_POST['prof_moneda']==1) echo 'Bs'; else echo '$us'; ?>
						</td>
						<?php
					}
					else
					{
						?>
						<td>
							<?php 
							$tot=$_POST['superficie']*$_POST['precio'];
							$con=$tot-($tot*$dc);
							echo round($con-($con*($_POST['descuento']/100)),2); 
							
							$stot=$tot-($tot*($_POST['descuento']/100));
							
							$cuota=$this->cuota(($stot-$_POST['cuota_inicial']),$interes_anual,$_POST['meses']);
							
							?> <?php if($_POST['prof_moneda']==1) echo 'Bs'; else echo '$us'; ?>
						</td>
						<td>
						<?php echo $_POST['meses']; ?> Meses
						</td>
						<td>
						<?php echo round($cuota,2); ?> <?php if($_POST['prof_moneda']==1) echo 'Bs'; else echo '$us'; ?>
						</td>
						<td>
							<?php echo $_POST['cuota_inicial']; ?> <?php if($_POST['prof_moneda']==1) echo 'Bs'; else echo '$us'; ?>
						</td>
						<td>
							<?php 
							echo round((($cuota*$_POST['meses']) + $_POST['cuota_inicial']),2); ?> <?php if($_POST['prof_moneda']==1) echo 'Bs'; else echo '$us'; ?>
						</td>
						<?php
					}
					?>
				</tr>
				</tbody>
				</table>
				</center>
				<br>
				<table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
				</div><br>
                
		<?php
	}
	
	function cuota($monto,$interes,$meses)
	{
		if($interes > 0)
		{
			$interes /= 100; 
			
			$interes /= 12;
			
			$power = pow(1 + $interes, $meses);
			
			return ($monto * $interes * $power) / ($power - 1);
		}
		else
		{
			return round($monto/$meses,2);
		}
	}
	
	
}
?>