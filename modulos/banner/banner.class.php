<?php

class BANNER extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function BANNER()
	{
		//permisos
		$this->ele_id=139;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="ser_nombre";
		$this->arreglo_campos[0]["texto"]="Nombre";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;
		
		$this->link='gestor.php';
		
		$this->modulo='banner';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('NOTICIA');
		
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
		$sql="SELECT * FROM servicio";
		
		$this->set_sql($sql,' order by ser_fecha desc ');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
		
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>T&iacute;tulo</th>				
				<th>Fecha de publicacion</th>				
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
						echo $objeto->ser_nombre;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->ser_fecha,1);
					echo "&nbsp;</td>";
					
					echo "<td>";
						if($objeto->ser_estado=='H') echo 'Si'; else echo 'No';
					echo "&nbsp;</td>";
					
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
		
		$sql="select * from servicio
				where ser_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		 	 	 	 	 	 	 	 	 
		$_POST['ser_nombre']=$objeto->ser_nombre;	
		$_POST['ser_descripcion']=$objeto->ser_descripcion;	
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

		
		    $this->formulario->dibujar_tarea('SERVICIOS');
		
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
									<img src="imagenes/noticia/<?php echo $foto;?>" border="0" width="80"><br>
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
							   <div class="Etiqueta" ><span class="flechas1">* </span>Descripción</div>
							   <div id="CajaInput">	   
							   <textarea rows="10" cols="50" name="ser_descripcion" id="ser_descripcion"><?php echo $_POST['ser_descripcion'];?></textarea>									
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
			
			$sql="insert into servicio(ser_nombre,ser_descripcion,ser_imagen,ser_fecha,ser_estado) values 
									('".$_POST['ser_nombre']."','".$_POST['ser_descripcion']."','".$nombre_archivo."','".date('Y-m-d H:i:s')."','".$_POST['ser_estado']."')";

			$conec->ejecutar($sql,false);
			
			
			$mensaje='Noticia Agregada Correctamente!!!';

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

		 $upload_class->upload_dir = "imagenes/noticia/"; 

		 $upload_class->upload_log_dir = "imagenes/noticia/upload_logs/"; 

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
				
				require_once('clases/class.upload.php');
				
					$mifile='imagenes/noticia/'.$upload_class->file_name;
					
					$handle = new upload($mifile);
					
					if ($handle->uploaded) 
					{
					    
						$handle->image_resize          = true;
						$handle->image_ratio           = true;
						$handle->image_y               = 90;
						$handle->image_x               = 100;
			   
						$handle->process('imagenes/noticia/chica/');
					    
						if (!($handle->processed)) 
						{
					        echo 'error : ' . $handle->error;
					    }
		
						
					}
				
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
		
			$sql="update servicio set 
								ser_nombre='".$_POST['ser_nombre']."',
								ser_descripcion='".$_POST['ser_descripcion']."',
								ser_estado='".$_POST['ser_estado']."',
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
					$mifile="imagenes/noticia/$mi";
				
					@unlink($mifile);
				
				}
				
				$conec->ejecutar($sql);
			
				$mensaje='Noticia Modificada Correctamente!!!';
					
				$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
			}
		}
		else
		{
			
			$sql="update servicio set 
								ser_nombre='".$_POST['ser_nombre']."',
								ser_estado='".$_POST['ser_estado']."',
								ser_descripcion='".$_POST['ser_descripcion']."'
								where ser_id = '".$_GET['id']."'";

			$conec->ejecutar($sql);
			
			$mensaje='Noticia Modificado Correctamente!!!';

			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		}
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar la Noticia?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo".'&tarea=ELIMINAR','ser_id');
	}
	
	function eliminar_tcp()
	{
		
			$llave=$_POST['ser_id'];
			
			$mi=$this->nombre_imagen($llave);
			
			$mifile="imagenes/noticia/$mi";
				
			@unlink($mifile);
		
			$conec= new ADO();
			
			$sql="delete from servicio where ser_id='".$_POST['ser_id']."'";
			
			$conec->ejecutar($sql);
	
			$mensaje='Noticia Eliminado Correctamente!!!';
			
		
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
	}
	
	function nombre_imagen($id)
	{
		$conec= new ADO();
		
		$sql="select ser_imagen from servicio where ser_id='".$id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->ser_imagen;
	}
	
}
?>