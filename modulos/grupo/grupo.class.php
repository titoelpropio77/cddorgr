<?php

class GRUPO extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function GRUPO()
	{
		//permisos
		$this->ele_id=24;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="gru_id";
		$this->arreglo_campos[0]["texto"]="Id";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;
		
		$this->arreglo_campos[1]["nombre"]="gru_descripcion";
		$this->arreglo_campos[1]["texto"]="Descripci&oacute;n";
		$this->arreglo_campos[1]["tipo"]="cadena";
		$this->arreglo_campos[1]["tamanio"]=25;
		
		$this->link='gestor.php';
		
		$this->modulo='grupo';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('GRUPO');
	}
	
	
	function dibujar_busqueda()
	{
		?>
		<script>		
		function ejecutar_script(id,tarea){
				var txt = 'Esta seguro de eliminar al grupo?';
				
				$.prompt(txt,{ 
					buttons:{Si:true, No:false},
					callback: function(v,m,f){
						
						if(v){
								location.href='gestor.php?mod=grupo&tarea='+tarea+'&id='+id;
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
			$this->arreglo_opciones[$nun]["script"]="ok";
			$nun++;
		}
		
	}
	
	function dibujar_listado()
	{
		$sql="SELECT * FROM ad_grupo";
		
		$this->set_sql($sql);
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Id</th>
				<th>Descripci&oacute;n</th>
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
						echo $objeto->gru_id;
					echo "</td>";
					echo "<td>";
						echo $objeto->gru_descripcion;
					echo "</td>";
					echo "<td>";
						if($objeto->gru_estado=='1') echo 'Habilitado'; else echo 'Deshabilitado';
					echo "</td>";
					echo "<td>";
						echo $this->get_opciones($objeto->gru_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from ad_grupo
				where gru_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['gru_id']=$objeto->gru_id;
		
		$_POST['gru_descripcion']=$objeto->gru_descripcion;
		
		$_POST['gru_estado']=$objeto->gru_estado;
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Id";
			$valores[$num]["valor"]=$_POST['gru_id'];
			$valores[$num]["tipo"]="texto";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Descripci&oacute;n";
			$valores[$num]["valor"]=$_POST['gru_descripcion'];
			$valores[$num]["tipo"]="texto";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Estado";
			$valores[$num]["valor"]=$_POST['gru_estado'];
			$valores[$num]["tipo"]="numero";
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
		
		$red=$url.'&tarea=ACCEDER';
		
		if(!($ver))
		{
			$url.="&tarea=".$_GET['tarea'];
		}
		
		if($cargar)
		{
			$url.='&id='.$_GET['id'];
		}
			

		$this->formulario->dibujar_tarea();
	
		if($this->mensaje<>"")
		{
			$this->formulario->mensaje('Error',$this->mensaje);
		}
		?>
			<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent" style="width:100%";>
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Id</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" <?php if($_GET['tarea']=="MODIFICAR") echo 'readonly="readonly"';?>  name="gru_id" id="gru_id" size="25" value="<?php echo $_POST['gru_id'];?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Descripci&oacute;n	</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="gru_descripcion" id="gru_descripcion" size="60" value="<?php echo $_POST['gru_descripcion'];?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Estado</div>
							   <div id="CajaInput">
							   <select name="gru_estado" class="caja_texto">
									<option value="" >Seleccione</option>
									<option value="1" <?php if($_POST['gru_estado']=='1') echo 'selected="selected"'; ?>>Habilitado</option>
									<option value="0" <?php if($_POST['gru_estado']=='0') echo 'selected="selected"'; ?>>Deshabilitado</option>
								</select>
							   </div>
							</div>
							<!--Fin-->
									
						</div>
						<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Permisos</div>
							   <div id="CajaInput">
							   <div>
							   <table class="tablaLista" cellpadding="0" cellspacing="0" style="width:100%;">
								<tbody>
								<?php $this->cargar_padres($a_tareas);?>
								</tbody>
								</table>
								</div>
								
							   </div>
							</div>
							<!--Fin-->
					
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
								<?php
								if(!($ver))
								{
									?>
									<input type="submit" class="boton" name="" value="Guardar">
									<input type="reset" class="boton" name="" value="Cancelar">
									<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
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
	
	function tareas()
	{
		$conec=new ADO();
		
		$sql="SELECT tar_id,tar_nombre
						FROM ad_tarea
						ORDER BY tar_id asc";
	
		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();
		
		$a_tareas=array();

		if($num<>0)
		{						 
				
		   for($i=0;$i<$num;$i++)
			{
				$objeto=$conec->get_objeto();
				
				$a_tareas[]=array($objeto->tar_id,$objeto->tar_nombre);
				
				$conec->siguiente();
			}
				   
		}

		return $a_tareas;	
	}
	
	function tareas_elemento($ele)
	{
		$conec=new ADO();
		
		$sql="SELECT eta_tar_id
						FROM ad_elemento_tarea
						where eta_ele_id='$ele'
						";
	
		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();

		if($num<>0)
		{						 
				
		   for($i=0;$i<$num;$i++)
			{
				$objeto=$conec->get_objeto();
				
				$a_tarea_elemento[]=$objeto->eta_tar_id;
				
				$conec->siguiente();
			}
				   
		}

		return $a_tarea_elemento;	
	}
	
	function cargar_padres($a_tareas)
	{
		
		if($_GET['tarea']=='MODIFICAR' || $_GET['tarea']=='VER')
		{
			$a_permisos=$this->obtener_permisos($_POST['gru_id']);
			$ban=true;
		}
		else
		{
			$ban=false;			
		}
		
		
		$conec=new ADO();
		
		$sql="SELECT distinct  ele_id,ele_padre,ele_nombre,ele_titulo,ele_tipo
						FROM ad_elemento
						where ele_padre='0' and ele_estado='H'
						ORDER BY ele_orden";
	
		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();
 
		if($num<>0)
		{						 
		
		    for($i=0;$i<$num;$i++)
			{
				$objeto=$conec->get_objeto();
				
				$a_tarea_elemento=$this->tareas_elemento($objeto->ele_id);
				
//				$a_tarea_elemento=$this->tareas_elemento($objeto->ele_id);
						
				$nume=count($a_tarea_elemento);
				
				?>
				<thead>
								<tr style="height:20px;">
						        	<th style="font-size:9px; "></th>
									<?php 
									$a_tareas=$this->tareas();
									foreach($a_tareas as $tarea)
									{	
										?>
										<th class="tOpciones" style="font-size:8px;"><?echo $tarea[1];?></th>
										<?php
									}
									?>
								</tr>							
							    </thead>
				<tr>
					<td><b><?php echo $objeto->ele_titulo;?></b></td>
					<?php
					foreach($a_tareas as $tarea)
					{	
						if($nume > 0)
						{
							if(is_numeric(array_search($tarea[0],$a_tarea_elemento)))
							{
								if($ban)
								{
									if(is_numeric(array_search($objeto->ele_id.'-'.$tarea[0],$a_permisos)))
									{
										?>
										<td><center><input checked="checked" type="checkbox" name="permiso[]" value="<?php echo $objeto->ele_id.'-'.$tarea[0];?>"></center></td> 
										<?php
									}
									else
									{
										?>
										<td><center><input type="checkbox" name="permiso[]" value="<?php echo $objeto->ele_id.'-'.$tarea[0];?>"></center></td> 
										<?php
									}
								}
								else
								{
									?>
									<td><center><input type="checkbox" name="permiso[]" value="<?php echo $objeto->ele_id.'-'.$tarea[0];?>"></center></td> 
									<?php								
								}
							}
							else
							{
								?>
								<td>&nbsp;</td>
								<?php
							}
						}
						else
						{
							?>
							<td>&nbsp;</td>
							<?php
						}
					}
					?>
				</tr>
				<?php
				
				$this->cargar_hijos($objeto->ele_id,$a_tareas,$a_permisos,$ban);
				
				$conec->siguiente();
			}
				   
		}	
	}
	
	function cargar_hijos($padre,$a_tareas,$a_permisos,$ban){
		$conec=new ADO();
		$sql="SELECT distinct  ele_id,ele_padre,ele_nombre,ele_titulo,ele_tipo
					FROM ad_elemento
					where ele_padre='$padre' and ele_estado='H'
					ORDER BY ele_orden";
		$conec->ejecutar($sql);
		$num=$conec->get_num_registros();
		if($num<>0){
				   for($i=0;$i<$num;$i++)
					{
						$objeto=$conec->get_objeto();
						
						$a_tarea_elemento=$this->tareas_elemento($objeto->ele_id);
						
						$nume=count($a_tarea_elemento);
			
						?>
						<tr>
							<td><?php echo $objeto->ele_titulo;?></td>
							<?php
							foreach($a_tareas as $tarea)
							{	
								if($nume > 0)
								{
								
									if(is_numeric(array_search($tarea[0],$a_tarea_elemento)))
									{
										if($ban)
										{
											if(is_numeric(array_search($objeto->ele_id.'-'.$tarea[0],$a_permisos)))
											{
												?>
												<td><center><input checked="checked" type="checkbox" name="permiso[]" value="<?php echo $objeto->ele_id.'-'.$tarea[0];?>"></center></td> 
												<?php
											}
											else
											{
												?>
												<td><center><input type="checkbox" name="permiso[]" value="<?php echo $objeto->ele_id.'-'.$tarea[0];?>"></center></td> 
												<?php
											}
										}
										else
										{
											?>
											<td><center><input type="checkbox" name="permiso[]" value="<?php echo $objeto->ele_id.'-'.$tarea[0];?>"></center></td> 
											<?php								
										}
									}
									else
									{
										?>
										<td>&nbsp;</td>
										<?php
									}
								}
								else
								{
									?>
									<td>&nbsp;</td>
									<?php
								}
							}
							?>
						</tr>
						<?php
						
						$conec->siguiente();
					}
		}	
	}
	
	function insertar_tcp()
	{
		$verificar=NEW VERIFICAR;
		
		$parametros[0]=array('gru_id');
		$parametros[1]=array($_POST['gru_id']);
		$parametros[2]=array('ad_grupo');
		
		if($verificar->validar($parametros))
		{
			$conec= new ADO();
		
			$sql="insert into ad_grupo values ('".$_POST['gru_id']."','".$_POST['gru_descripcion']."','".$_POST['gru_estado']."')";
			
			$conec->ejecutar($sql);
			
			///*guardamos los permisos*///
			if($_POST['permiso'])
			{
				$a_per=$_POST['permiso'];
				
				foreach ($a_per as $per)
				{	   
				    $vector = explode("-",$per);
				   				 
					$sql="insert into ad_permiso (pmo_ele_id,pmo_tar_id,pmo_gru_id) values('".$vector[0]."','".$vector[1]."','".$_POST['gru_id']."')";
					
					$conec->ejecutar($sql,false);
				  
				}    
			}
			///**///
			
			$mensaje='Grupo Agregado Correctamente.';
			
			$tipo='Correcto';
		}
		else
		{
			$mensaje='El grupo no puede ser agregado, por que ya existe un grupo con ese Id.';
			
			$tipo='Error';
		}
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER'.'Ø'.$this->link.'?mod='.$this->modulo.'&tarea=AGREGAR','Ir al listadoØContinuar Agregando',$tipo);
		
	}
	
	function modificar_tcp()
	{
		$conec= new ADO();
		
		$codigo="";
	
		$sql="update ad_grupo set 
								gru_descripcion='".$_POST['gru_descripcion']."',
								gru_estado='".$_POST['gru_estado']."'
								where gru_id='".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		///*modificamos los permisos*///
		
		$sql = "delete from ad_permiso where pmo_gru_id='".$_POST['gru_id']."'";
		
		$conec->ejecutar($sql);
		
		if($_POST['permiso'])
		{
			$a_per=$_POST['permiso'];
			
			foreach ($a_per as $per)
			{	   
			    $vector = explode("-",$per);
			   				 
				$sql="insert into ad_permiso (pmo_ele_id,pmo_tar_id,pmo_gru_id) values('".$vector[0]."','".$vector[1]."','".$_POST['gru_id']."')";
				
				$conec->ejecutar($sql,false);
			  
			}    
		}
		
		///**///
		
		$mensaje='Grupo Modificado Correctamente.';
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER','','Correcto');
	}
	
	function obtener_permisos($grupo)
	{
		$conec= new ADO();
		
		$vector=array();

		$consulta = "select pmo_ele_id,pmo_tar_id from ad_permiso where pmo_gru_id='".$grupo."'";	

		$conec->ejecutar($consulta);

		$numero = $conec->get_num_registros();   

		for($i=0;$i<$numero;$i++)
		{

		  $objeto=$conec->get_objeto();
		  
		  $vector[$i] = $objeto->pmo_ele_id.'-'.$objeto->pmo_tar_id;		  

		  $conec->siguiente();

		} 
		return $vector;
	}
	
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar el grupo?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'gru_id','Confirmacion');
	}
	
	function eliminar_tcp()
	{
		$verificar=NEW VERIFICAR;
		
		$parametros[0]=array('usu_gru_id');
		$parametros[1]=array($_GET['id']);
		$parametros[2]=array('ad_usuario');
		
		if($verificar->validar($parametros))
		{
			$conec= new ADO();
		
			$sql="delete from ad_grupo where gru_id='".$_GET['id']."'";
			 
			$conec->ejecutar($sql);
			
			$sql="delete from ad_permiso where pmo_gru_id='".$_GET['id']."'";
			 
			$conec->ejecutar($sql);
			
			$mensaje='Grupo Eliminado Correctamente.';
			
			$tipo='Correcto';
			
		}
		else
		{
			$mensaje='El grupo no puede ser eliminado, por que esta siendo utilizado en el modulo de usuarios.';
			
			$tipo='Error';
		}		
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER','',$tipo);
	}
}
?>