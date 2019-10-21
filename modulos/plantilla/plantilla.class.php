<?php

class PLANTILLA extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $tipo_cambio;
	var $usu;
	function PLANTILLA()
	{
		//permisos
		$this->ele_id=107;		
		$this->busqueda();		
		$this->tipo_cambio = $this->tc;
			$this->ban_agregar=false;
	
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="pla_descripcion";
		$this->arreglo_campos[0]["texto"]="Descripción";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;
		
		
		$this->link='gestor.php';
		
		$this->modulo='plantilla';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('PLANTILLAS');
		$this->usu=new USUARIO;
		
	}
	
	function dibujar_busqueda()
	{
	  ?>
		<script>		
		function ejecutar_script(id,tarea){
				var txt = 'Esta seguro de eliminar la Plantilla?';
				
				$.prompt(txt,{ 
					buttons:{Anular:true, Cancelar:false},
					callback: function(v,m,f){
						
						if(v){
								location.href='gestor.php?mod=plantilla&tarea='+tarea+'&id='+id;
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
		
		if($this->verificar_permisos('MODIFICAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='MODIFICAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_edit.png';
			$this->arreglo_opciones[$nun]["nombre"]='MODIFICAR';
			$nun++;
		}
		
		if($this->verificar_permisos('ELIMINAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='ELIMINAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_drop.png';
			$this->arreglo_opciones[$nun]["nombre"]='ELIMINAR';
			$nun++;
		}
	}
	
	function dibujar_listado()
	{
		$sql = "SELECT pla_id,pla_estado,pla_descripcion,pla_tco_id,tco_descripcion
                FROM plantilla   inner join tipo_comprobante on (pla_tco_id = tco_id)";								 
				
		
		
		
		$this->set_sql($sql,' order by pla_id desc');
		
		$this->set_opciones();
		
		$this->dibujar();		
	}
	
	function ver()
	{
		$this->cargar_datos();												
	    $this->formulario_tcp('cargar');
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Descripcion</th>				
				<th>Tipo Comprobante</th>
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
						echo $objeto->pla_descripcion;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->tco_descripcion;
					echo "&nbsp;</td>";
					echo "<td>";
							if($objeto->pla_estado=='1') echo 'Habilitado'; else echo 'Desabilitado';
					echo "&nbsp;</td>";
					
					
					echo "<td>";
						echo $this->get_opciones($objeto->pla_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from plantilla
				where pla_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['pla_id']=$objeto->pla_id;
		
		$_POST['pla_tc']= 7.00;
		
		$_POST['pla_descripcion']=$objeto->pla_descripcion;
		
		$_POST['pla_estado']=$objeto->pla_estado;
		
		$_POST['pla_tco_id']=$objeto->pla_tco_id;
		
	
		
		
	}
	
	function datos()
	{
		if($_POST)
		{
			return true;
		}
			return false;
	}
	
	function formulario_tcp($tipo)
	{
				?>
				<script type="text/javascript">
				
				function validar(form)
				{				    
					var tipo_com = document.frm_sentencia.pla_tco_id.options[document.frm_sentencia.pla_tco_id.selectedIndex].value;
					var descripcion = document.frm_sentencia.pla_descripcion.value;
					var estado = document.frm_sentencia.pla_estado.options[document.frm_sentencia.pla_estado.selectedIndex].value;
					
					
					var cantfilas = $('#tprueba tbody').children().length;
					var tbs = $('#tbs').attr('value');
					var tsus = $('#tsus').attr('value');
					
					
					   if (tipo_com!="")
					   {
					       if (descripcion!="")
						   {
						       if (estado!="")
							   {		
                                   							   
									  document.frm_sentencia.pla_tco_id.disabled = false;
									  form.submit();
								  
							   }
							   else
							     $.prompt('Seleccione un estado',{ opacity: 0.8 });
						   }
						   else
						     $.prompt('Introduzca la Descripcion',{ opacity: 0.8 });
					   }
					   else
					     $.prompt('Seleccione un Tipo de Comprobante',{ opacity: 0.8 });
					
					
					
				}
				
				
				function cargar_cuenta(id)
				{
					var	valores="tid="+id+"&t=1";
					var	valores1="tid="+id+"&t=2";					
					ejecutar_ajax('ajax.php','cuenta',valores,'POST');					
				}			
				
				
				function convertir_sus(row)
				{
				    
				     var tc = parseFloat($("#cmp_tc").attr('value'));				 
                      if (isNaN(parseFloat($(row).attr('value'))))		 					 
					  $(row).parent().parent().parent().children().eq(4).children().eq(0).children().eq(0).attr('value',0.00);
					
                      else		
                      {
                         if (parseFloat($(row).attr('value')) < 0 )
						 {
						    $(row).parent().parent().parent().children().eq(4).children().eq(0).children().eq(0).attr('value',0.00);							
							$(row).parent().parent().parent().children().eq(3).children().eq(0).children().eq(0).attr('value',0.00);
						 }
						else 		        
						   $(row).parent().parent().parent().children().eq(4).children().eq(0).children().eq(0).attr('value',roundNumber((parseFloat($(row).attr('value')) / tc),2));
					  }
					  total();
				}
				
				
				function convertir_bs(row)
				{
				    
				     var tc = parseFloat($("#cmp_tc").attr('value'));	
					 
					 if (isNaN(parseFloat($(row).attr('value'))))						 
				          $(row).parent().parent().parent().children().eq(3).children().eq(0).children().eq(0).attr('value',0.00);
					 else
					 {
					    if (parseFloat($(row).attr('value')) < 0 )
						{
						  $(row).parent().parent().parent().children().eq(4).children().eq(0).children().eq(0).attr('value',0.00);						
							$(row).parent().parent().parent().children().eq(3).children().eq(0).children().eq(0).attr('value',0.00);
						}
						else					   
						 $(row).parent().parent().parent().children().eq(3).children().eq(0).children().eq(0).attr('value',roundNumber((parseFloat($(row).attr('value')) * tc),2));
					  }	 
					  total();
				}
				
				function total()
				{
				   var cant = $('#tprueba tbody').children().length;                    
				   var totalbs = 0;
                   var totalsus = 0;					
				   $('#tprueba tbody').children().each(function(){				 
					 totalbs += isNaN(parseFloat($(this).children().eq(3).children().eq(0).children().eq(0).attr('value')))? 0 : parseFloat($(this).children().eq(3).children().eq(0).children().eq(0).attr('value'));
                      totalsus += isNaN(parseFloat($(this).children().eq(4).children().eq(0).children().eq(0).attr('value')))? 0 : parseFloat($(this).children().eq(4).children().eq(0).children().eq(0).attr('value'));							   
				   }); 				   					
					$('#tbs').attr('value',roundNumber(parseFloat(totalbs),2));
				    $('#tsus').attr('value',roundNumber(parseFloat(totalsus),2));                   
				}
				
				function remove(row)
				{  	
				   var cant =  $(row).parent().parent().parent().children().length;  
				  
				    if (cant > 1)  
					{
						$(row).parent().parent().parent().remove();	
						total();
					}	
					
				}


				function addTableRow(id,valor){ 	
					 $(id).append(valor);
				}

				function datos_fila()
				{				
					var valor;
					var tipo;
					var precio;
					var moneda;
					var bs;
					var sus;
					var tc=parseFloat(document.frm_sentencia.pla_tc.value);					
					var tpbs=parseFloat(document.frm_sentencia.tbs.value);
					var tpsus=parseFloat(document.frm_sentencia.tsus.value);		
					
					tipo=document.frm_sentencia.pla_tco_id.options[document.frm_sentencia.pla_tco_id.selectedIndex].value;
					
					if(tipo!='')
					{
						
						
						var cuenta=document.frm_sentencia.cde_cue_id.options[document.frm_sentencia.cde_cue_id.selectedIndex].value;
						var centrocosto=document.frm_sentencia.pld_cco_id.options[document.frm_sentencia.pld_cco_id.selectedIndex].value;
						var moneda=document.frm_sentencia.pld_mon_id.options[document.frm_sentencia.pld_mon_id.selectedIndex].value;
						var tipoaux=document.frm_sentencia.pld_tipo.options[document.frm_sentencia.pld_tipo.selectedIndex].value;
					
						if(cuenta != '' && centrocosto!= '' && moneda!= '' && tipoaux!= '')
						{
							document.frm_sentencia.pla_tco_id.disabled=true;
							
							var valores=cuenta.split('Ø');
							var centro=centrocosto.split('Ø');
							var mone=moneda.split('Ø');		
                            var tipop=tipoaux.split('Ø');								
							
							addTableRow('#tprueba','<tr><td>'+valores[1]+'</td><td><input type=\'hidden\' name=\'cue_id[]\' value=\''+valores[0]+'\'>'+valores[2]+'</td><td><input type=\'hidden\' name=\'cco_id[]\' value=\''+centro[0]+'\'>'+centro[1]+'</td><td><center><input type=\'text\' name=\'preciobs[]\'  size=\'8\' value=\'\' onKeyUp=\'convertir_sus(this)\' onKeyPress=\'return ValidarNumero(event);\'> </center></td><td><center><input type=\'text\' size=\'8\' name=\'preciosus[]\'  value=\'\' onKeyUp=\'convertir_bs(this)\' onKeyPress=\'return ValidarNumero(event);\'></center></td><td><input type=\'hidden\' name=\'mon_id[]\' value=\''+mone[0]+'\'>'+mone[1]+'</td><td><input type=\'hidden\' name=\'tipo[]\' value=\''+tipop[0]+'\'>'+tipop[1]+'</td><td><center><img src=\'images/b_drop.png\' onclick=\'remove(this);\'></center></td></tr>');
						}
						else
						{
							$.prompt('Seleccione la Cuenta, Centro de Costo, moneda y el tipo',{ opacity: 0.8 });
						}	
					}
					else
					{
						
							$.prompt('Seleccione el Tipo de Comprobante',{ opacity: 0.8 });			   
						
					}						
				}			
				
				function ValidarNumero(e)
				{ 			   
				    evt = e ? e : event;
				    tcl = (window.Event) ? evt.which : evt.keyCode;
				    if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
				    {
				        return false;
				    }
				    return true;
				}				
				
				</script>
				<?
				
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

		
		    $this->formulario->dibujar_tarea('PLANTILLA');
		
			if($this->mensaje<>"")
			{
				$this->formulario->dibujar_mensaje($this->mensaje);
			}
			
			if($this->verificar_permisos('ACCEDER'))
			{	
			?>
			<table align=right border=0><tr><td><a href="gestor.php?mod=plantilla&tarea=ACCEDER" title="LISTADO DE PLANTILLAS"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
			<?php
			}
			?>
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent" style="width:90%">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion" >
						<!--Inicio-->
						<div id="ContenedorDiv">								
                                <div class="Etiqueta" >Tipo de Cambio</div>
								 <div id="CajaInput">
								     <input readonly="readonly" class="caja_texto" name="pla_tc" id="cmp_tc" size="12" value="<?php echo $this->tipo_cambio;?>" type="text">									 
								</div>	  								
							</div>
						 <!--Fin-->						 
						 <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Tipo Comprobante</div>
							   <div id="CajaInput">
							   <select name="pla_tco_id" class="caja_texto" onchange="cargar_cuenta(this.value);" <?php  if ($tipo=="cargar") echo "disabled";?>>
							   <option value="">Seleccione</option>
							   <?php 		
								$fun=NEW FUNCIONES;		
								$fun->combo("select tco_id as id,tco_descripcion as nombre from tipo_comprobante order by tco_id asc",$_POST['pla_tco_id']);		
								?>
							   </select>
							   </div>
							</div>
							<!--Fin-->
						 
							
								<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Descripcion</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="pla_descripcion" id="cmp_intersado" size="45" maxlength="100" value="<?php echo $_POST['pla_descripcion'];?>">
							   </div>
							</div>
							<!--Fin-->											
							
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Estado</div>
							   <div id="CajaInput">
								    <select name="pla_estado" class="caja_texto">
									<option value="" >Seleccione</option>
									<option value="1" <?php if($_POST['pla_estado']=='1') echo 'selected="selected"'; ?>>Habilitado</option>
									<option value="0" <?php if($_POST['pla_estado']=='0') echo 'selected="selected"'; ?>>Deshabilitado</option>
									</select>
							   </div>
							</div>
							
							
							
							
							<div id="ContenedorDiv">
							<fieldset id="legend_detalle" style="border:1px dashed #8ec2ea; color:#005c89; padding:3px; float:rigth;">
							 <legend>Datos del Detalle</legend>	
                                   						
							<!--Inicio-->
							
							   <div class="Etiqueta" ><span class="flechas1">*</span>Agregar Cuenta</div>
							   <div id="CajaInput">
							     <div id="cuenta">
								   <select name="cde_cue_id" id="cde_cue_id" class="caja_texto">
								   <option value="">Seleccione</option>
								   <?php 	
                                    		
                                      if ($tipo=="cargar")
									  {
									       $tco_id = $_POST['pla_tco_id'];
									       if ($tco_id == 1)	   
										      $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where (cue_tcu_id = 1 or cue_tcu_id=3 ) and cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id'] );		
										   else
										     if ($tco_id == 2) 
									            $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where (cue_tcu_id = 2 or cue_tcu_id=3 ) and cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id']);		
											 else	
									            $fun->combo("select CONCAT(cue_id,'Ø',cue_numero,'Ø',cue_descripcion) as id, cue_descripcion  as nombre  from cuenta where  cue_nivel = 3 order by cue_descripcion asc ",$_POST['cde_cue_id']);		
										 
                                      }								
									
									?>
								   </select>
								  </div>
								</div>
								
								
								<div class="Etiqueta" ><span class="flechas1">*</span>Agregar Centro Costo</div>
							   <div id="CajaInput">
								   <select name="pld_cco_id" class="caja_texto">
								   <option value="">Seleccione</option>
								   <?php 	
                                         								   
									          $fun=NEW FUNCIONES;											  
									          $fun->combo("select CONCAT(cco_id,'Ø',cco_descripcion) as id,cco_descripcion as nombre from centrocosto where cco_padre_id = 0 order by cco_descripcion asc",$_POST['cde_cco_id']);		
										
									?>
								   </select>
								</div>
								<br>
								<div id="ContenedorDiv">
									<div class="Etiqueta" ><span class="flechas1">*</span>Moneda</div>
									<div id="CajaInput">
									<select name="pld_mon_id" class="caja_texto">
									<option value="">Seleccione</option>
								   <?php 		
									$fun=NEW FUNCIONES;		
									$fun->combo("select CONCAT(mon_id,'Ø',mon_descripcion) as id,mon_descripcion as nombre from moneda order by mon_id asc",$_POST['pld_mon_id']);		
									?>
									</select>
									</div>
								</div>
								<div id="ContenedorDiv">
								   <div class="Etiqueta" ><span class="flechas1">*</span>Tipo </div>
								   <div id="CajaInput">
									    <select name="pld_tipo" class="caja_texto">
										<option value="">Seleccione</option>
										<option value="1ØEditable" <?php if($_POST['pld_tipo']=='1') echo 'selected="selected"'; ?>>Editable</option>
										<option value="0ØNo Editable" <?php if($_POST['pld_tipo']=='0') echo 'selected="selected"'; ?>>No Editable</option>
										</select>
								   </div>
								</div>
																
							    <div id="CajaInput">
								  <img src="images/boton_agregar.png" style='margin:0px 0px 0px 10px' onclick="javascript:datos_fila();">
							   </div>
							    						    
							   </fieldset>	
							</div>				
							
							
							
							<!--Fin-->
						
						
						
						
						<div id="ContenedorDiv">
							<div>
								<table class="tablaLista" id="tprueba" cellpadding="0" cellspacing="0">
								<thead>
								<tr>
						        	
									<th>Codigo</th>
									<th>Detalle</th>
									<th>Centro Costo</th>                                    								
									<th class="tOpciones" width="150">Bolivianos</th>
									<th class="tOpciones" width="155">Dolares</th>
									<th class="tOpciones" width="155">Moneda</th>
									<th class="tOpciones">Tipo</th>	
									<th>Eliminar</th>
								</tr>							
							    </thead>
								<tbody>	
								     <?php
									    
										 if ($tipo=="cargar")
										 {
										       $conec= new ADO();		
											   $sql="SELECT pld_cue_id,cue_descripcion,cue_numero, pld_cco_id, cco_descripcion,pld_monto, pld_mon_id,pld_tipo
											         FROM plantilla_detalle inner join cuenta on (pld_cue_id = cue_id)
													                        inner join centrocosto on (pld_cco_id = cco_id)
													 WHERE pld_pla_id = ".$_GET['id']."
													 ORDER BY pld_id asc";
												$conec->ejecutar($sql);	
												
													$num=$conec->get_num_registros();
		                                            $totalbs = 0;
													$totalsus = 0;
													for($i=0;$i<$num;$i++)
													{
														$objeto=$conec->get_objeto();														
														
														
														echo '<tr>';																				
																$precio=$objeto->pld_monto;		
																
																
																echo "<td>";
																	echo $objeto->cue_numero;
																echo "</td>";																
																echo "<td>";
																echo "<input type='hidden' name='cue_id[]' value='$objeto->pld_cue_id'>";    
																	echo $objeto->cue_descripcion;
																echo "</td>";
																
																echo "<td>";
																echo "<input type='hidden' name='cco_id[]' value='$objeto->pld_cco_id'>";    
																	echo $objeto->cco_descripcion;
																echo "</td>";
																
																
																
																if($objeto->pld_mon_id=='1')
																{															    
                        											$moneda = "Bolivianos";					
																	$bs=$precio;
																	$sus=round(($bs/$this->tipo_cambio),2);
																	$totalbs+=$bs;
																	$totalsus+=$sus;
																}
																else
																{																    
																	$moneda='Dolares';
																	$bs=round(($precio*$this->tipo_cambio),2);
																	$sus=$precio;
																	$totalbs+=$bs;
																	$totalsus+=$sus;
																}
																
																			
																
																echo "<td>";
																echo "<center><input type='text' name='preciobs[]'  size='8' value='".$bs."' onKeyUp='convertir_sus(this)' onKeyPress='return ValidarNumero(event);'> </center>";
																echo "</td>";
																
																echo "<td>";
																echo "<center><input type='text' name='preciosus[]'  size='8' value='".$sus."' onKeyUp='convertir_bs(this)' onKeyPress='return ValidarNumero(event);'> </center>";
																echo "</td>";
																
																echo "<td>";
																echo "<input type='hidden' name='mon_id[]' value='$objeto->pld_mon_id'>";    
																	echo $moneda;
																echo "</td>";
																
																if($objeto->pld_tipo=='1')
																    $tipo = "Editable";
																else
																    $tipo = "NO Editable";
                                                             																
																
																echo "<td>";
																echo "<input type='hidden' name='tipo[]' value='$objeto->pld_tipo'>";    
																	echo $tipo;
																echo "</td>";
																
																
																echo "<td>";
																echo "<center><img src='images/b_drop.png' onclick='remove(this);'></center>";
																echo "</td>";
															echo "</tr>";
														
														$conec->siguiente();
													}

										 
										 }
									 
									 ?>
								</tbody>
								<tfoot>	
									<tr>
						        	
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>									
									<td>Total Bs<input type="text" name="tbs" id="tbs" size="10" value="<?php if ($tipo=="cargar") echo $totalbs; else echo "0.00"; ?>" readonly="readonly"></td>
									<td>Total $us<input type="text" name="tsus" id="tsus" size="10" value="<?php if ($tipo=="cargar") echo $totalsus; else echo "0.00"; ?>" readonly="readonly"></td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									</tr>	
								</tfoot>

								</table>
							</div>
						</div>
						
						</div>
					
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
								<?php
								if(!($ver) && ($_GET['tarea']!="VER"))
								{
									?>
									<input type="button" class="boton" name="" value="Guardar" onClick="validar(this.form);">
									
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
		<?php
	}
	
	function insertar_tcp()
	{ 
	    $conec= new ADO();		
		$sql="Insert into plantilla (pla_descripcion,pla_estado,pla_tco_id) 
		                     values ('".$_POST['pla_descripcion']."','".$_POST['pla_estado']."','".$_POST['pla_tco_id']."')";
		$conec->ejecutar($sql,false);	
        
		$pla_id = mysql_insert_id();
	    
        $num = count($_POST['cue_id']);				    
		
		for($i=0; $i<$num; $i++)
		{	  
            if ($_POST['mon_id'][$i]==1)
			    $monto = $_POST['preciobs'][$i];
			else
			    $monto = $_POST['preciosus'][$i];
			
		     $sql="Insert into plantilla_detalle (pld_cue_id,pld_cco_id,pld_monto,pld_mon_id,pld_pla_id,pld_tipo) 
		                     values ('".$_POST['cue_id'][$i]."','".$_POST['cco_id'][$i]."','".$monto."','".$_POST['mon_id'][$i]."','".$pla_id."','".$_POST['tipo'][$i]."')";
		     $conec->ejecutar($sql,false);	   
		}		

	    $mensaje='Plantilla Agregado Correctamente!!!   :';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	
	   //$this->nota_comprobante($cmp_id);
	  	
	}
	
	function modificar_tcp()
	{	     
	    $conec= new ADO();								 
		$sql="Update plantilla SET pla_descripcion ='".$_POST['pla_descripcion']."',pla_estado = '".$_POST['pla_estado']."',pla_tco_id ='".$_POST['pla_tco_id']."'
		      WHERE pla_id=".$_GET['id'];					 
		$conec->ejecutar($sql);			
		
		$sql="DELETE FROM plantilla_detalle WHERE pld_pla_id=".$_GET['id'];	
	    $conec->ejecutar($sql);
        $num = count($_POST['cue_id']);				    
		
		for($i=0; $i<$num; $i++)
		{	  
            if ($_POST['mon_id'][$i]==1)
			    $monto = $_POST['preciobs'][$i];
			else
			    $monto = $_POST['preciosus'][$i];
			
		     $sql="Insert into plantilla_detalle (pld_cue_id,pld_cco_id,pld_monto,pld_mon_id,pld_pla_id,pld_tipo) 
		                     values ('".$_POST['cue_id'][$i]."','".$_POST['cco_id'][$i]."','".$monto."','".$_POST['mon_id'][$i]."','".$_GET['id']."','".$_POST['tipo'][$i]."')";
		     $conec->ejecutar($sql,false);	   
		}		

	    $mensaje='Plantilla Modificada Correctamente!!!   :';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	
	}
	
	function eliminar()
	{ 
	  $conec= new ADO();		
		$sql="DELETE FROM plantilla WHERE pla_id=".$_GET['id'];
		$conec->ejecutar($sql);
		$sql="DELETE FROM plantilla_detalle WHERE pld_pla_id=".$_GET['id'];
		$conec->ejecutar($sql);
		$mensaje='Plantilla Eliminada Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}

}
?>