<?php

class CUENTA extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function CUENTA()
	{
		//permisos
		$this->ele_id=105;		
		$this->busqueda();
		
	//	if(!($this->verificar_permisos('AGREGAR')))
	//	{
			$this->ban_agregar=false;
	//	}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="per_nombre";
		$this->arreglo_campos[0]["texto"]="Nombre";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;
		
		$this->arreglo_campos[1]["nombre"]="per_apellido";
		$this->arreglo_campos[1]["texto"]="Apellido";
		$this->arreglo_campos[1]["tipo"]="cadena";
		$this->arreglo_campos[1]["tamanio"]=40;
		
		
		$this->link='gestor.php';
		
		$this->modulo='plancuenta';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('PLAN DE CUENTAS');
		
		
	}
		
	function dibujar()
	{
		
	//	$this->formulario->dibujar_cabecera();
		
		$this->formulario_tcp('blanco');
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
		$sql="SELECT 
		mic_id,mic_marca,mic_tipo,mic_modelo,mic_placa,per_nombre,per_apellido,int_numero 
		FROM 
		micro inner join socio on (mic_soc_id=soc_id)
		inner join gr_persona on (soc_per_id=per_id)
		inner join interno on (mic_int_id=int_id)
		";
		
		$this->set_sql($sql);
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Socio</th>
				<th>Interno</th>
				<th>Marca</th>
				<th>Tipo</th>
				<th>Modelo</th>
				<th>Placa</th>
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
						echo $objeto->per_nombre.' '.$objeto->per_apellido;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->int_numero;
					echo "&nbsp;</td>";
					
						echo "<td>";
						echo $objeto->mic_marca;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->mic_tipo;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->mic_modelo;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->mic_placa;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $this->get_opciones($objeto->mic_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="SELECT 
				mic_id,mic_int_id,mic_fecha_ingreso,mic_marca,mic_tipo,mic_modelo,mic_placa,mic_motor,mic_chasis,mic_color,mic_poliza,per_nombre,per_apellido,soc_per_id,mic_soc_id,int_numero 
				FROM 
				micro inner join socio on (mic_soc_id=soc_id)
				inner join gr_persona on (soc_per_id=per_id)
				inner join interno on (mic_int_id=int_id)
				where mic_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['mic_int_id']=$objeto->mic_int_id;
		
		$_POST['mic_soc_id']=$objeto->mic_soc_id;
		
		$_POST['mic_fecha_ingreso']=$objeto->mic_fecha_ingreso;
		
		$_POST['mic_tipo']=$objeto->mic_tipo;
		
		$_POST['mic_placa']=$objeto->mic_placa;
		
		$_POST['mic_marca']=$objeto->mic_marca;
		
		$_POST['mic_modelo']=$objeto->mic_modelo;
		
		$_POST['mic_motor']=$objeto->mic_motor;
		
		$_POST['mic_chasis']=$objeto->mic_chasis;
		
		$_POST['mic_color']=$objeto->mic_color;
		
		$_POST['mic_poliza']=$objeto->mic_poliza;
		
		$_POST['mic_soc_id']=$objeto->mic_soc_id;
		
		$_POST['mic_nombre_persona']=$objeto->per_nombre.' '.$objeto->per_apellido;
		
		$_POST['mic_numero_interno']=$objeto->int_numero;
	}
	
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Codigo";
			$valores[$num]["valor"]=$_POST['numero'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Descripcion";
			$valores[$num]["valor"]=$_POST['descripcion'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Tipo de Cuenta";
			$valores[$num]["valor"]=$_POST['cue_tcu_id'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;		
			
			$val=NEW VALIDADOR;
			
			$this->mensaje="";
			
			if($val->validar($valores))
			{
				return true;
			}
				
			else
			{
				$this->mensaje=$val->mensaje;
				return false;
			}
		}
			return false;
	}
	
	function formulario_tcp($tipo)
	{
	        ?>
			    <script type="text/javascript" src="js/_bsJavascript/core/lang/Bs_Array.class.js"></script>
				<script type="text/javascript" src="js/_bsJavascript/components/tree/Bs_Tree.class.js"></script>
				<script type="text/javascript" src="js/_bsJavascript/components/tree/Bs_TreeElement.class.js"></script>
				
				<script type="text/javascript"> 
				  
				   <?php	
                          include_once("Generar_Arbol.php");                            
						
					    $conec= new ADO();	
						$sql="Select ccc_id, ccc_descripcion_cuenta,ccc_nivel_cuenta from conf_cc where ccc_ges_id = 2";
						$conec->ejecutar($sql);
						$object=$conec->get_objeto();		
		           ?>
				
				  var NivelArbol = <? echo $object->ccc_nivel_cuenta;?>;
				  var Format = <? echo "'".$object->ccc_descripcion_cuenta."'"; ?>;

				  function ColocarDatos(Dato)
				  {
					  valor=Dato.split("Ø"); 
					  frm_sentencia.tipo.value= '';

					  if (frm_sentencia.tipo.value != 1)
					  {
					      frm_sentencia.adicionar.disabled = false;
				          frm_sentencia.modificar.disabled = false;
				          frm_sentencia.eliminar.disabled = false;
				          frm_sentencia.guardar.disabled = true; 
						  frm_sentencia.numero.readOnly = true;
				          frm_sentencia.descripcion.readOnly = true;  	  
				         
						  frm_sentencia.IdFamilia.value = valor[0];
						  frm_sentencia.IdFamiPadre.value = valor[0] ;
						  frm_sentencia.Nivel.value = valor[4] ;
						  var pos = Obtener_Posicion(frm_sentencia.cue_tcu_id,valor[5]);
						  frm_sentencia.cue_tcu_id.options[pos].selected = true;
						  frm_sentencia.numero.value = valor[1] ;
						  frm_sentencia.descripcion.value = valor[2] ;
						  		  
						  if  (frm_sentencia.Nivel.value < NivelArbol )
						      frm_sentencia.adicionar.disabled = false;
							else
						      frm_sentencia.adicionar.disabled = true;
					  }
					  else
					  {
					    if (valor[4] < NivelArbol)
						{
				          frm_sentencia.IdFamilia.value = valor[0];
						  frm_sentencia.IdFamiPadre.value =valor[0] ;	 
						  frm_sentencia.Nivel.value = valor[4] ;
						}
					  }  
				  }
				  
				  function Obtener_Posicion(Objeto,IdCliente)
				  {
					   var pos=0;
					   for (i=0; i<Objeto.length; i++)
					   {
						  if (Objeto.options[i].value == IdCliente)
						   {
							  pos = i;
							  break;
						   }
					   }
					   return pos;
				  }
  
				  function ColocarPadre(Dato)
				  {	     
					  frm_sentencia.IdFamiPadre.value =Dato;
					  frm_sentencia.Nivel.value =Dato;  	 
					  frm_sentencia.tipo.value= '';
					  frm_sentencia.adicionar.disabled = false;
					  frm_sentencia.modificar.disabled = true;
					  frm_sentencia.eliminar.disabled = true;
					  frm_sentencia.guardar.disabled = true; 
					  frm_sentencia.numero.readOnly = true;
					  frm_sentencia.descripcion.readOnly = true;  
					  
					  if  (frm_sentencia.Nivel.value < NivelArbol )
						      frm_sentencia.adicionar.disabled = false;
							else
						      frm_sentencia.adicionar.disabled = true;	
				  }
				  
				  function init()
				   {          
				       <?php
					        $arbol=new TArbol();
			                $arbol->nombreVariable="a";
			                llenarPrincipal($arbol,$object->ccc_nivel_cuenta); 			  
			                echo $arbol->toJavascript();
					   ?>
					   
					  t = new Bs_Tree();
			          t.imageDir="js/_bsJavascript/components/tree/img/winXp/";		 		  		  
					  t.useAutoSequence=false;
					 // t.rememberState = true;
			          t.initByArray(a);		
					 // t.autoCollapse = true;	  	
					 //t.expandAll();
			          t.drawInto('treeDiv1');		   
					 // t.applyState();  
				   }	 
				   
				function ValidarCodigo()
				{
				  var Long = frm_sentencia.numero.value;
				  if  (Long.length == Format.length)
				      return true;
				  else
				     {
					   //alert("Inserte El Codigo Completo");
				       return false;
					 }
				}   
				 
				function btn_enviar(form)
				{
				    var tipo_cuenta = document.frm_sentencia.cue_tcu_id.options[document.frm_sentencia.cue_tcu_id.selectedIndex].value;
					var codigo      = document.frm_sentencia.numero.value;
					var descripcion = document.frm_sentencia.descripcion.value;
					
					var tipo       = document.frm_sentencia.tipo.value;
					
					if ((codigo!="") && (ValidarCodigo()))
					{
					   if (descripcion!="")
						{
						   if (tipo_cuenta!="")
							{
							  if  (tipo==1)
							  {
							    form.submit();
							  }
							  else
							   if  (tipo==2)
							   {
							      form.submit();
							   }
							}
						    else
							 $.prompt('Seleccione un tipo de cuenta',{ opacity: 0.8 });
						}
					    else
						 $.prompt('La Descripcion no puede ser vacio',{ opacity: 0.8 });
					}
				    else
					 $.prompt('El Codigo no puede ser vacio y tiene que estar completo',{ opacity: 0.8 });
					
				}
				
				function btn_nuevo()
				{ 
				  if (t.getActiveElement())
				  {
					  frm_sentencia.tipo.value= 1;
					//  verificar_nivel();	  
					  
					  if (frm_sentencia.Nivel.value == 0)
					     frm_sentencia.numero.value ="";
					  frm_sentencia.descripcion.value ="";    
					   frm_sentencia.cue_tcu_id.options[0].selected = true;
					  frm_sentencia.adicionar.disabled = true;
					  frm_sentencia.modificar.disabled = true;
					  frm_sentencia.eliminar.disabled = true;
					  frm_sentencia.guardar.disabled = false;
					  frm_sentencia.numero.readOnly = false;
					  frm_sentencia.descripcion.readOnly = false;  
					  frm_sentencia.action = "gestor.php?mod=plancuenta&tarea=AGREGAR";
				  }
				  else
				    $.prompt('Seleccione un Item Por Favor',{ opacity: 0.8 });
				    //alert("Seleccione un Item Por Favor");
				}

				
				
				
				
				
				function btn_cancelar()
				{
				  
				  frm_sentencia.adicionar.disabled = false;
				  frm_sentencia.modificar.disabled = false;
				  frm_sentencia.eliminar.disabled = false;
				  frm_sentencia.guardar.disabled = true;
				  frm_sentencia.numero.readOnly = true;
				  frm_sentencia.descripcion.readOnly = true;  
				  frm_sentencia.tipo.value = '';
				  
				  
				  elemento = t.getActiveElement(); 
				  if (elemento)
				    if (elemento.id!=0)
				      ColocarDatos(elemento.id);     
				}



				function btn_modificar()
				{
				  if (t.getActiveElement())
				  {
					  frm_sentencia.tipo.value = 2;
					  
					  frm_sentencia.adicionar.disabled = true;
					  frm_sentencia.modificar.disabled = true;
					  frm_sentencia.eliminar.disabled = true;
					  frm_sentencia.guardar.disabled = false;  
					  frm_sentencia.descripcion.readOnly = false;  
					  frm_sentencia.action = "gestor.php?mod=plancuenta&tarea=MODIFICAR";
				  }
				  else
				    $.prompt('Seleccione un Item Por Favor',{ opacity: 0.8 });
				  //  alert("Seleccione un Item Por Favor");
				}

				function btn_eliminar()
				{
					Elemento = t.getActiveElement();
					 if (Elemento)
				     { 
					    if (Elemento.getChildren()=='')
						{
					       frm_sentencia.tipo.value = 3;  
						   frm_sentencia.action = "gestor.php?mod=plancuenta&tarea=ELIMINAR";
				           frm_sentencia.submit();
						}
						else
						  $.prompt('no se puede Eliminar Porque tiene Sub-Items!',{ opacity: 0.8 });
						 // alert("no se puede Eliminar Porque tiene Sub-Items!");
					 }
					 else
					   $.prompt('no se puede Eliminar Porque tiene Sub-Items!',{ opacity: 0.8 });
				       //alert("Seleccione un Item Por Favor");
				}


				   
				</script>
				
				
			<?php
	      
			
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

		
		    $this->formulario->dibujar_tarea('PLAN DE CUENTAS');
		
			if($this->mensaje<>"")
			{
				$this->formulario->dibujar_mensaje($this->mensaje);
			}		
		
			?>
		
			
			
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion" >
						 <table border="0" width="100%">
						 <tr>
						     <td width="50%">
                                 <div id="ContenedorDiv" >						
							           <div id="treeDiv1" style=" height:300px;  overflow:scroll; " >tree is loading...</div><script> init();</script>
							      </div>
							 </td>
							 <td width="50%">
								     <!--Inicio-->
										<div id="ContenedorDiv">
										   <div class="Etiqueta" >
										       <font color='#FF0000' >El Formato de Codigo: </font> (<? echo $object->ccc_descripcion_cuenta; ?>)
										   </div>
										   

										</div>
									 <!--Fin-->
									 
									 <!--Inicio-->
									<div id="ContenedorDiv">
									   <div class="Etiqueta" ><span class="flechas1">*</span>Codigo</div>
									   <div id="CajaInput">										    
											<input name="numero" id="numero" readonly type="text" class="caja_texto" value="" size="30" >											
									   </div>

									</div>
									<!--Fin-->
									
									<!--Inicio-->
									<div id="ContenedorDiv">
									   <div class="Etiqueta" ><span class="flechas1">*</span>Descripcion</div>
									   <div id="CajaInput">										    
											<input name="descripcion" id="descripcion" readonly type="text" class="caja_texto" value="" size="40">											
											<input name="tipo"  type="hidden"  value="" >
						                      <input name="IdFamilia"  type="hidden" value="">
						                      <input name="IdFamiPadre"  type="hidden" value="">
						                      <input name="Nivel"  type="hidden" value="">
									   </div>

									</div>										
									<!--Fin-->
									<!--Inicio-->
									<div id="ContenedorDiv">
									   <div class="Etiqueta" ><span class="flechas1">*</span>Tipo Cuenta</div>
									   <div id="CajaInput">
									   <select name="cue_tcu_id" class="caja_texto">
									   <option value="">Seleccione</option>
									   <?php 		
										$fun=NEW FUNCIONES;		
										$fun->combo("select tcu_id as id,tcu_descripcion as nombre from tipo_cuenta order by tcu_id asc",$_POST['cue_tcu_id']);		
										?>
									   </select>
									   </div>
									</div>
									<!--Fin-->
								<td>
								</tr>
							</table>						
						</div>
					
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
								<?php
								if($this->verificar_permisos('AGREGAR'))
		                        {
								 ?>
								    <input type="button" class="boton" name="adicionar" value="Adicionar" onclick="btn_nuevo();">
								 <?php
								}
								
								if($this->verificar_permisos('MODIFICAR'))
		                        {
								 ?>
								     <input type="button" class="boton" name="modificar" value="Modificar" onclick="btn_modificar();">
								 <?php
								}
								
								if($this->verificar_permisos('ELIMINAR'))
		                        {
								 ?>
								     <input type="button" class="boton" name="eliminar" value="Eliminar" onclick="btn_eliminar();">
								 <?php
								}
								?>
								
								<input type="button" class="boton" name="guardar" value="Guardar" onclick="btn_enviar(this.form);">
								<input type="button" class="boton" name="cancelar" value="Cancelar" onclick="btn_cancelar();">
								
								
								
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
		$verificar=NEW VERIFICAR;
		
		$parametros[0]=array('cue_numero');
		$parametros[1]=array($_POST['numero']);
		$parametros[2]=array('cuenta');
		
		if($verificar->validar($parametros))
		{
			$conec= new ADO();
		
			$sql="insert into cuenta (cue_numero,cue_nivel,cue_descripcion,cue_padre_id,cue_tcu_id) values ('".$_POST['numero']."','".($_POST['Nivel'] +1)."','".$_POST['descripcion']."','".$_POST['IdFamiPadre']."','".$_POST['cue_tcu_id']."')";
			
			
			$conec->ejecutar($sql);
			
			$mensaje='Cuenta Agregada Correctamente';
		}
		else
		{
			$mensaje='El Cuenta no puede ser agregada, por que ya existe una cuenta con ese codigo.';
		}
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=AGREGAR');
		
	}
	
	function modificar_tcp()
	{
	    
		    $conec= new ADO();			
			$sql="update cuenta set 
								cue_numero='".$_POST['numero']."',
								cue_descripcion='".$_POST['descripcion']."',
								cue_tcu_id='".$_POST['cue_tcu_id']."'
								
								where cue_id = '".$_POST['IdFamilia']."'";

			$conec->ejecutar($sql);

			$mensaje='Cuenta Modificada Correctamente!!!';	
	
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=AGREGAR');
	  
	
	
		
		
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar el micro?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'mic_id');
	}
	
	function eliminar_tcp()
	{
	    $conec= new ADO();
		
        $sql = "Select * from comprobante_detalle where cde_cue_id = '".$_POST['IdFamilia']."'";		
        $conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		if ($num > 0)
		{
		    $mensaje='La cuenta no puede ser eliminada porque esta siendo usada en Comprobantes';
		}
		else
		{	
	
			$conec= new ADO();
			
			$sql="delete from cuenta where cue_id='".$_POST['IdFamilia']."'";
			
			$conec->ejecutar($sql);
			
			$mensaje='Cuenta Eliminado Correctamente!!!';
		}
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=AGREGAR');
	}
}
?>