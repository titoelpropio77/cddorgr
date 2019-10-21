<?php

class BANNER extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function BANNER()
	{
		//permisos
		$this->ele_id=144;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=6;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="ser_nombre";
		$this->arreglo_campos[0]["texto"]="Nombre";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;
		
		$this->link='gestor.php';
		
		$this->modulo='banners';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('BANNER');
		
		
	}
	
	
	function dibujar_busqueda()
	{
		
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
		$sql="SELECT * FROM banner";
		
		$this->set_sql($sql,' order by ser_orden desc ');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function orden($tecnologia,$accion,$ant_orden)
	{
		$conec= new ADO();
		
		if($accion=='s')
			$cad=" where ser_orden > $ant_orden order by ser_orden asc";
		else
			$cad=" where ser_orden < $ant_orden order by ser_orden desc";

		$consulta = "
		select 
			ser_id,ser_orden 
		from 
			banner
		$cad
		limit 0,1
		";	

		$conec->ejecutar($consulta);

		$num = $conec->get_num_registros();   

		if($num > 0)
		{
			$objeto=$conec->get_objeto();
			
			$nu_orden=$objeto->ser_orden;
			
			$id=$objeto->ser_id;
			
			$consulta = "update banner set ser_orden='$nu_orden' where ser_id='$tecnologia'";	

			$conec->ejecutar($consulta);
			
			$consulta = "update banner set ser_orden='$ant_orden' where ser_id='$id'";	

			$conec->ejecutar($consulta);
		}	
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>T&iacute;tulo</th>				
				<th>Publicados</th>				
				<th>Orden</th>
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
						echo $objeto->ser_nombre;
					echo "&nbsp;</td>";
					
					echo "<td>";
						if($objeto->ser_publicado=='H') echo 'Si'; else echo 'No';
					echo "&nbsp;</td>";
					
					echo "<td>";
						?>
						<center><a href="<?php echo $this->link.'?mod='.$this->modulo.'&tarea=ACCEDER&tec='.$objeto->ser_id.'&acc=s&or='.$objeto->ser_orden;?>"><img src="images/subir.png" border="0"></a><a href="<?php echo $this->link.'?mod='.$this->modulo.'&tarea=ACCEDER&tec='.$objeto->ser_id.'&acc=b&or='.$objeto->ser_orden;?>"><img src="images/bajarr.png" border="0"></a></center>
						<?php
					echo "</td>";
					echo "<td>";
						echo $this->get_opciones($objeto->ser_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from banner
				where ser_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		 	 	 	 	 	 	 	 	 
		$_POST['ser_nombre']=$objeto->ser_nombre;	
		$_POST['ser_descripcion']=$objeto->ser_descripcion;	
		$_POST['ser_publicado']=$objeto->ser_publicado;	
		$_POST['ser_estado']=$objeto->ser_estado;	
		$_POST['ser_imagen']=$objeto->ser_imagen;
		
		
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			if($_GET['tarea']=='AGREGAR')
			{
			$valores[$num]["etiqueta"]="Imagen";
			$valores[$num]["valor"]=$_FILES['ser_imagen']['name'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			}
			$valores[$num]["etiqueta"]="Nombre";
			$valores[$num]["valor"]=$_POST['ser_nombre'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			
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

		
		    $this->formulario->dibujar_tarea('bannerS');
		
			if($this->mensaje<>"")
			{
				$this->formulario->dibujar_mensaje($this->mensaje);
			}
			?>
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent" style="width:800px">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Imagen</div>
							   <div id="CajaInput">
							   <?php
								if($_POST['ser_imagen']<>"")
								{	$foto=$_POST['ser_imagen'];
									$b=true;
								}	
								else
								{	$foto='sin_foto.gif';
									$b=false;
								}
								if(($ver)||($cargar))
								{	?>
									<img src="imagenes/banner/<?php echo $foto;?>" border="0" width="80"><br>
									<input   name="ser_imagen" type="file" id="ser_imagen" />
									<?php
								}
								else 
								{
									?>
									<input  name="ser_imagen" type="file" id="ser_imagen" />
									<?php
								}
								?>
								<input   name="fotooculta" type="hidden" id="fotooculta" value="<?php echo $_POST['ser_imagen'].$_POST['fotooculta'];?>"/>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Titulo</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="ser_nombre" id="ser_nombre" maxlength="250"  size="50" value="<?php echo $_POST['ser_nombre'];?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1"></span>Publicar</div>
							   <div id="CajaInput">
							    <select name="ser_publicado" class="caja_texto">
								
									<option value="D" <?php if($_POST['ser_publicado']=='D') echo 'selected="selected"'; ?>>No</option>
									<option value="H" <?php if($_POST['ser_publicado']=='H') echo 'selected="selected"'; ?>>Si</option>
									
								</select>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Estado</div>
							   <div id="CajaInput">
								<select name="ser_estado" class="caja_texto">
									<option value="H" <?php if($_POST['ser_estado']=='H') echo 'selected="selected"'; ?>>Habilitado</option>
									<option value="D" <?php if($_POST['ser_estado']=='D') echo 'selected="selected"'; ?>>Deshabilitado</option>
									</select>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Descripción</div>
							   <div id="CajaInput">	   
							   <textarea rows="10" cols="50" name="ser_descripcion" id="ser_descripcion"><?php echo $_POST['ser_descripcion'];?></textarea>									
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
	
	function insertar_tcp()
	{
		$conec= new ADO();
		
		$result=$this->subir_imagen($nombre_archivo,$_FILES['ser_imagen']['name'],$_FILES['ser_imagen']['tmp_name']);
								
		if(trim($result)<>'')
		{
			$this->formulario->ventana_volver($result,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		}
		else 
		{
			$sql=" select max(ser_orden) as ultimo from banner";
		
			$conec->ejecutar($sql);
			
			$objeto=$conec->get_objeto();
			
			$orden=$objeto->ultimo + 1;
					
			
			$sql="insert into banner(ser_nombre,ser_orden,ser_descripcion,ser_imagen,ser_publicado,ser_estado) values 
									('".$_POST['ser_nombre']."','$orden','".$_POST['ser_descripcion']."','".$nombre_archivo."','".$_POST['ser_publicado']."','".$_POST['ser_estado']."')";

			$conec->ejecutar($sql,false);
			
			
			$mensaje='Banner Agregado Correctamente!!!';

			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		}
	}
	
	function subir_imagen(&$nombre_imagen,$name,$tmp)
	{	
		 require_once('clases/upload.class.php');

		 $nn=date('d_m_Y_H_i_s_').rand();

		 $upload_class = new Upload_Files();

		 $upload_class->temp_file_name = trim($tmp); 	
		 
		 $upload_class->file_name = strtolower($nn.substr(trim($name), -4, 4));

		 $nombre_imagen=$upload_class->file_name;		 		 

		 $upload_class->upload_dir = "imagenes/banner/"; 

		 $upload_class->upload_log_dir = "imagenes/banner/upload_logs/"; 

		 $upload_class->max_file_size = 2097152; 	

		 $upload_class->ext_array = array(".jpg",".gif",".png");

         $upload_class->crear_thumbnail=false;

		 $valid_ext = $upload_class->validate_extension(); 

		 $valid_size = $upload_class->validate_size(); 

		 $valid_user = $upload_class->validate_user(); 

		 $max_size = $upload_class->get_max_size(); 

		 $file_size = $upload_class->get_file_size(); 

		 $file_exists = $upload_class->existing_file(); 		

		if (!$valid_ext) { 				   

			$result = "La Extension de este Archivo es invalida, Intente nuevamente por favor!"; 

		} 

		elseif (!$valid_size) { 

			$result = "El Tamaño de este archivo es invalido, El maximo tamaño permitido es: $max_size y su archivo pesa: $file_size"; 

		}    

		elseif ($file_exists) { 

			$result = "El Archivo Existe en el Servidor, Intente nuevamente por favor."; 

		} 

		else 
		{		    
			$upload_file = $upload_class->upload_file_with_validation(); 

			if (!$upload_file) { 

				$result = "Su archivo no se subio correctamente al Servidor."; 
			}

			else 
			{ 
				$result = "";
				
			} 
		} 	

		return $result;	

	}
	
	
	
	function modificar_tcp()
	{
		$conec= new ADO();
		
		if($_FILES['ser_imagen']['name']<>"")
		{	 	 	 	 	 	 	
			 	 	 	 	
			$result=$this->subir_imagen($nombre_archivo,$_FILES['ser_imagen']['name'],$_FILES['ser_imagen']['tmp_name']);
		
			$sql="update banner set 
								ser_nombre='".$_POST['ser_nombre']."',
								ser_descripcion='".$_POST['ser_descripcion']."',
								ser_estado='".$_POST['ser_estado']."',
								ser_publicado='".$_POST['ser_publicado']."',
								ser_imagen='".$nombre_archivo."'
								where ser_id = '".$_GET['id']."'";
						
			
			if(trim($result)<>'')
			{
					
				$this->formulario->ventana_volver($result,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
					
			}
			else
			{
				$llave=$_GET['id'];
				
				$mi=trim($_POST['fotooculta']);
				
				if($mi<>"")
				{
					$mifile="imagenes/banner/$mi";
				
					@unlink($mifile);
				
				}
				
				$conec->ejecutar($sql);
			
				$mensaje='Banner Modificado Correctamente!!!';
					
				$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
			}
		}
		else
		{
			
			$sql="update banner set 
								ser_nombre='".$_POST['ser_nombre']."',
								ser_estado='".$_POST['ser_estado']."',
								ser_publicado='".$_POST['ser_publicado']."',
								ser_descripcion='".$_POST['ser_descripcion']."'
								where ser_id = '".$_GET['id']."'";

			$conec->ejecutar($sql);
			
			$mensaje='Banner Modificado Correctamente!!!';

			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		}
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar el Banner?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo".'&tarea=ELIMINAR','ser_id');
	}
	
	function eliminar_tcp()
	{
		
			$llave=$_POST['ser_id'];
			
			$mi=$this->nombre_imagen($llave);
			
			$mifile="imagenes/banner/$mi";
				
			@unlink($mifile);
		
			$conec= new ADO();
			
			$sql="delete from banner where ser_id='".$_POST['ser_id']."'";
			
			$conec->ejecutar($sql);
	
			$mensaje='Banner Eliminado Correctamente!!!';
			
		
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
	}
	
	function nombre_imagen($id)
	{
		$conec= new ADO();
		
		$sql="select ser_imagen from banner where ser_id='".$id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->ser_imagen;
	}
	
}
?>