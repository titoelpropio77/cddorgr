<?php

class CAM_CONTRA
{
	var $mensaje;
	
	function CAM_CONTRA()
	{
		$this->link='gestor.php';
		
		$this->modulo='cam_contra';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('CAMBIAR CONTRASEÑA');
		
	}

	function datos()
	{
		
		
		if($_POST)
		{
			$this->mensaje="";
			
			if(!(trim(($_POST['actual']))<>''))
			{
				$this->mensaje.="El campo <b>Contraseña actual</b> es requerido<br>";
			}
			
			if(!(trim(($_POST['nueva']))<>''))
			{
				$this->mensaje.="El campo <b>Contraseña nueva</b> es requerido<br>";
			}
			
			if(!(trim(($_POST['confirmacion']))<>''))
			{
				$this->mensaje.="El campo <b>Confirmar contraseña nueva</b> es requerido<br>";
			}
			
			if($this->mensaje=="")
		
				return true;
			
			else
			
				return false;
		}
		else
		
			return false;
		
		
	}
	
	function obtener_password($id)
	{
		$conec= new ADO();
		
		$sql="select usu_password from ad_usuario where usu_id='$id'";
		 
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->usu_password; 
		
	}
	
	function gestionar(&$actual,&$nueva,&$confirmacion,&$color1,&$color2)
	{
		require_once('clases/usuario.class.php');
		
		$usuario=NEW USUARIO();
		
		$usu=$usuario->get_id();
		
		$actual_base=$this->obtener_password($usu);
		
		if(md5($actual)==$actual_base)
		{
			if($nueva==$confirmacion)
			{
				$pass=md5($nueva);
				$actual="";
				$nueva="";
				$confirmacion="";
				$this->mensaje="La contraseña fue actualizada correctamente";
				$color1='#49994B';
				$color2='#D3F6D4';
				
				$conec= new ADO();
		
				$sql="update ad_usuario set usu_password='$pass' where usu_id='$usu'";
		 
				$conec->ejecutar($sql);
			}
			else
			{
				$this->mensaje="La contraseña nueva no es igual a la contraseña de confirmación";
				$color1='#DD3C10';
				$color2='#FFEBE8';
			}
		}
		else
		{
			$this->mensaje="La contraseña actual es incorrecta";
			$color1='#DD3C10';
			$color2='#FFEBE8';
		}
		
	}

	function formulario_tcp()
	{
		$actual=trim($_POST['actual']);
			
		$nueva=trim($_POST['nueva']);
		
		$confirmacion=trim($_POST['confirmacion']);
		
		$color1='#DD3C10';
		
		$color2='#FFEBE8';
		
		require_once('clases/usuario.class.php'); 
			
			$usuario=NEW USUARIO(); 
		
		if($_GET['ea']=='ok')
		{
			$conec=new ADO();
			
			$sql="delete from ad_accesos where acc_id='".$_GET['id']."'";
				
			$conec->ejecutar($sql);
		}
		else
		{
			if($_POST['accesos']=='ok')
			{
				
				$conec=new ADO();
			
				$sql="INSERT INTO `ad_accesos` (
					`acc_id` ,
					`acc_usu_id` ,
					`acc_ele_id` ,
					`acc_tarea`
					)
					VALUES (
					NULL , '".$usuario->get_id()."', '".$_POST['modulo']."', '".$_POST['tarea']."'
					)";
				
				$conec->ejecutar($sql);
			}
			else
			{
				if($this->datos())
				{	
					$this->gestionar($actual,$nueva,$confirmacion,$color1,$color2);
				}
			}
		}
		
		$url=$this->link.'?mod='.$this->modulo;
		
		$this->formulario->dibujar_tarea();
		
		
			if($this->mensaje<>"")
			{
			?>
				<table width="100%" cellpadding="0" cellspacing="1" style="border:1px solid <?php echo $color1;?>; color:<?php echo $color1;?>;">
				<tr bgcolor="<?php echo $color2;?>">
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
			
			<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Cambiar Contraseña</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Contraseña actual	</div>
							   <div id="CajaInput">
							   <input type="password" class="caja_texto" name="actual" id="actual" size="25" value="<?php echo $actual;?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Contraseña nueva		</div>
							   <div id="CajaInput">
							   <input type="password" class="caja_texto" name="nueva" id="nueva" size="25" value="<?php echo $nueva;?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Confirmar contraseña nueva	</div>
							   <div id="CajaInput">
							   <input type="password" class="caja_texto" name="confirmacion" id="confirmacion" size="25" value="<?php echo $confirmacion;?>">
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
									<input type="submit" class="boton" name="" value="Cambiar">
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
		<script>
		function tareas()
		{
			var modulo=document.frm_accesos.modulo.options[document.frm_accesos.modulo.selectedIndex].value;
			
			var	valores="tarea=cargar_tareas&modulo="+modulo+"&usuario=<?php echo $usuario->get_id(); ?>";			

			ejecutar_ajax('ajax.php','combo_tareas',valores,'POST');
		}
		
		function enviar_accesos()
		{
			var mo=document.frm_accesos.modulo.options[document.frm_accesos.modulo.selectedIndex].value;
			
			var ta=document.frm_accesos.tarea.options[document.frm_accesos.tarea.selectedIndex].value;
			
			if(mo!= '' && ta!='')
			{
				document.frm_accesos.submit();
			}
			else
			{
				$.prompt('Seleccione el modulo y la tarea.',{ opacity: 0.8 });
			}
		}
		</script>
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_accesos" name="frm_accesos" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Accesos Directos</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Modulo</div>
							   <div id="CajaInput">
								    <input type="hidden" class="caja_texto" name="accesos" id="accesos" value="ok">
									<select name="modulo" class="caja_texto" onchange="tareas();">
									<option value="" >Seleccione</option>
									<?php $this->cargar_padres(); ?>
									</select>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Tarea</div>
							   <div id="CajaInput">
								    <div id="combo_tareas">
									<select name="tarea" class="caja_texto">
										<option value="" >Seleccione</option>
									</select>
									</div>
							   </div>
							</div>
							<!--Fin-->
							
							
						</div>
					
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
								
									<input type="button" class="boton" name="" value="Agregar" onclick="enviar_accesos();">
							
								</center>
						   </div>
						</div>
				</div>
			</form>
		</div>	
		
		<div id="ContenedorDiv">
			<div style="margin-left:260px;">
				<table width="60%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Modulo</th>
					<th>Tarea</th>
					<th class="tOpciones" >Eliminar</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$conec=new ADO();
		
				$sql="
				select
					acc_id,ele_titulo,acc_tarea
				from
					ad_accesos 
					inner join ad_elemento on (acc_ele_id=ele_id)
				where
					acc_usu_id='".$usuario->get_id()."'
				";
				
				$conec->ejecutar($sql);

				$num=$conec->get_num_registros();
		 
			    for($i=0;$i<$num;$i++)
				{
					$objeto=$conec->get_objeto();
					
					?>
					<tr>
						<td><?php echo $objeto->ele_titulo;?></td>
						<td><?php echo $objeto->acc_tarea;?></td>
						<td><center><a href="gestor.php?mod=cam_contra&tarea=ACCEDER&ea=ok&id=<?php echo  $objeto->acc_id; ?>"><img src="images/b_drop.png" alt="ELIMINAR" title="ELIMINAR" border="0"></a></center></td>
					</tr>
					<?php
					
					$conec->siguiente();
				}
				
				?>			
				</tbody>
				</table>
				
			</div>
		</div>
	
		<?php
	}
	
	function cargar_padres()
	{
		require_once('clases/usuario.class.php');
		
		$usuario=NEW USUARIO();
		
		$conec=new ADO();
		
		$sql="SELECT distinct  ele_id,ele_padre,ele_nombre,ele_titulo,ele_tipo,ele_icono
					FROM ad_elemento,ad_permiso,ad_usuario
					WHERE usu_id= '".$usuario->get_id()."'
					AND usu_gru_id = pmo_gru_id
					AND pmo_ele_id=ele_id
					AND ele_padre='0'
					AND ele_estado = 'H'
					ORDER BY ele_orden";
		
		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();
 

		if($num<>0)
		{						 
				
				   for($i=0;$i<$num;$i++)
					{
						$objeto=$conec->get_objeto();
						
						?>
					    <optgroup label="<?php echo $objeto->ele_titulo;?>">
							<?php
								$this->cargar_hijos($objeto->ele_id,$usuario->get_id());
							?>  
						 </optgroup>
						<?php
						
						$conec->siguiente();
					}
				   
		}	
	}
	
	function cargar_hijos($padre,$usuario)
	{
		$conec=new ADO();
		
		$sql="SELECT distinct  ele_id,ele_padre,ele_nombre,ele_titulo,ele_tipo,ele_tarea
					FROM ad_elemento,ad_permiso,ad_usuario
					WHERE usu_id= '".$usuario."'
					AND usu_gru_id = pmo_gru_id
					AND pmo_ele_id=ele_id
					AND ele_padre='$padre'
					AND ele_estado = 'H'
					ORDER BY ele_orden";
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		
		if($num<>0)
		{						 
				   for($i=0;$i<$num;$i++)
					{
						$objeto=$conec->get_objeto();
						
						$ruta="gestor.php?mod=".$objeto->ele_nombre.'&tarea='.$objeto->ele_tarea;
						
						?>
						<option value="<?php echo $objeto->ele_id; ?>"><?php echo $objeto->ele_titulo; ?></option>
						<?php
						
						$conec->siguiente();
					}
		}	
	}
}
?>