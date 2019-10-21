<?php

class VIDEOS extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function VIDEOS()
	{
		
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='galeria';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('GALERIA');
	}
	
	function dibujar_busqueda()
	{
		
		//$this->formulario->dibujar_cabecera();
		
		$this->dibujar_listado();
	}
	
	
	function dibujar_listado()
	{
		
		$this->dibujar_encabezado();
		
		$this->mostrar_busqueda();
		
		$this->cerrar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
	    <div style="clear:both;"></div><center>

	<table class="tablaLista" cellpadding="0" cellspacing="0" width="60%">
		<thead>
			<tr>
				<th>Caratula</th>
				
				<th>Título</th>
				<th>Descripción</th>
	            <th class="tOpciones" width="100px">Opciones</th>
			</tr>
			</thead>
		<tbody>
			
		<?PHP
	}
	
	function cerrar()
	{
		?>
		</tbody>
		</table>
		<?php
	}
	
	function mostrar_busqueda()
	{
		$conec=new ADO();

		$sql="SELECT * 
		FROM 
		galeria_video
		where gvi_gal_id='".$_GET['id']."'
		order by gvi_id desc ";
		//echo $sql;

		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		for($i=0;$i<$num;$i++)
			{
				
				$objeto=$conec->get_objeto();
				echo '<tr>';
									
					echo "<td>";
						?>
						<center><img src="imagenes/galeria/chica/<?php echo $objeto->gvi_caratula; ?>" border="0" width="100"></center>
						<?php
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->gvi_titulo;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->gvi_descripcion;
					echo "&nbsp;</td>";
					?>
					<td>
						<center>
						<a href="javascript:ejecutar_script('<?php echo $_GET['id']; ?>','<?php echo $objeto->gvi_id; ?>');"><img src="images/b_drop.png" alt="ELIMINAR" title="ELIMINAR" border="0"></a>
						</center>
					</td>
					<?php
				echo "</tr>";
				
				$conec->siguiente();
			}
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Video";
			$valores[$num]["valor"]=$_FILES['gvi_archivo']['name'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Caratula";
			$valores[$num]["valor"]=$_FILES['gvi_caratula']['name'];
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
				
				$url.='&tarea=VIDEOS';
				
				$url.='&id='.$_GET['id'];
				

		
		    $this->formulario->dibujar_tarea('GALERIA - VIDEOS');
		
			if($this->mensaje<>"")
			{
				$this->formulario->dibujar_mensaje($this->mensaje);
			}
			?>

		<script>		
		function ejecutar_script(id,gvi){
				var txt = 'Esta seguro de eliminar la foto?';
				
				$.prompt(txt,{ 
					buttons:{Si:true, No:false},
					callback: function(v,m,f){
						
						if(v){
								location.href='gestor.php?mod=galeria&acc=ELIMINAR&tarea=VIDEOS&id='+id+'&gvi='+gvi;
						}
												
					}
				});
			}

		</script>

		<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Video</div>
							   <div id="CajaInput">
									<input  name="gvi_archivo" type="file" id="gvi_archivo" />
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Caratula</div>
							   <div id="CajaInput">
									<input  name="gvi_caratula" type="file" id="gvi_caratula" />
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Título</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="gvi_titulo" id="gvi_titulo" maxlength="250"  size="40" value="<?php echo $_POST['gvi_titulo'];?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Descripción</div>
							   <div id="CajaInput">
							   <textarea class="area_texto" name="gvi_descripcion" id="gvi_descripcion" cols="31" rows="3"><?php echo $_POST['gvi_descripcion']?></textarea>
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
		
		$result=$this->subir_imagen($nombre_archivo,$_FILES['gvi_archivo']['name'],$_FILES['gvi_archivo']['tmp_name']);
		
		$result2=$this->subir_imagen2($nombre_archivo2,$_FILES['gvi_caratula']['name'],$_FILES['gvi_caratula']['tmp_name']);
		
		$sql="insert into galeria_video(gvi_caratula,gvi_archivo,gvi_descripcion,gvi_gal_id,gvi_titulo) values 
							('".$nombre_archivo2."','".$nombre_archivo."','".$_POST['gvi_descripcion']."','".$_GET['id']."','".$_POST['gvi_titulo']."')";
		
	
		if(trim($result)<>'' or trim($result2)<>'')
		{
			$this->formulario->ventana_volver($result,$this->link.'?mod='.$this->modulo.'&tarea=VIDEOS&id='.$_GET['id']);
		}
		else 
		{
			$conec->ejecutar($sql);
			
			$mensaje='Video Agregado Correctamente.';
		
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=VIDEOS&id='.$_GET['id']);
		}
		
	}
	
	function subir_imagen(&$nombre_imagen,$name,$tmp)
	{	
		 require_once('clases/upload.class.php');

		 $nn=date('d_m_Y_H_i_s_').rand();

		 $upload_class = new Upload_Files();

		 $upload_class->temp_file_name = trim($tmp); 	
		 
		 $aname=explode(".", $name);
		 
		 $nume=count($aname);
		 
		 $upload_class->file_name = $nn.'.'.$aname[$nume-1];
		 
		 $nombre_imagen=$upload_class->file_name;		 		 

		 $upload_class->upload_dir = "imagenes/galeria/"; 

		 $upload_class->upload_log_dir = "imagenes/galeria/upload_logs/"; 

		 $upload_class->max_file_size = 5242880; 	

		 $upload_class->ext_array = array(".flv");

         $upload_class->crear_thumbnail=false;

		 $valid_ext = $upload_class->validate_extension(); 

		 $valid_size = $upload_class->validate_size(); 

		 $valid_user = $upload_class->validate_user(); 

		 $max_size = $upload_class->get_max_size(); 

		 $file_size = $upload_class->get_file_size(); 

		 $file_exists = $upload_class->existing_file(); 		

		if (!$valid_ext) { 				   

			$result = "La Extension de este Video es invalida, Intente nuevamente por favor!"; 

		} 

		elseif (!$valid_size) { 

			$result = "El Tamaño de este archivo es invalido, El maximo tamaño permitido es: $max_size y su archivo pesa: $file_size"; 

		}    

		elseif ($file_exists) { 

			$result = "El Video Existe en el Servidor, Intente nuevamente por favor."; 

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

					// require_once('clases/class.upload.php');

					// $mifile='imagenes/galeria/'.$upload_class->file_name;

					// $handle = new upload($mifile);

					// if ($handle->uploaded) 
					// {
					    // $handle->image_resize          = true;

						// $handle->image_ratio           = true;

						// $handle->image_y               = 125;

						// $handle->image_x               = 125;

						// $handle->process('imagenes/galeria/chica/');

						// if (!($handle->processed)) 
						// {
					        // echo 'error : ' . $handle->error;
					    // }

					// }
			} 
		} 	

		return $result;	

	}
	
	function subir_imagen2(&$nombre_imagen,$name,$tmp)
	{	
		 require_once('clases/upload.class.php');

		 $nn=date('d_m_Y_H_i_s_').rand();

		 $upload_class = new Upload_Files();

		 $upload_class->temp_file_name = trim($tmp); 	
		 
		 $aname=explode(".", $name);
		 
		 $nume=count($aname);
		 
		 $upload_class->file_name = $nn.'.'.$aname[$nume-1];
		 
		 $nombre_imagen=$upload_class->file_name;		 		 

		 $upload_class->upload_dir = "imagenes/galeria/"; 

		 $upload_class->upload_log_dir = "imagenes/galeria/upload_logs/"; 

		 $upload_class->max_file_size = 1048576; 	

		 $upload_class->ext_array = array(".jpg",".gif",".png");

         $upload_class->crear_thumbnail=false;

		 $valid_ext = $upload_class->validate_extension(); 

		 $valid_size = $upload_class->validate_size(); 

		 $valid_user = $upload_class->validate_user(); 

		 $max_size = $upload_class->get_max_size(); 

		 $file_size = $upload_class->get_file_size(); 

		 $file_exists = $upload_class->existing_file(); 		

		if (!$valid_ext) { 				   

			$result = "La Extension de este Foto es invalida, Intente nuevamente por favor!"; 

		} 

		elseif (!$valid_size) { 

			$result = "El Tamaño de este archivo es invalido, El maximo tamaño permitido es: $max_size y su archivo pesa: $file_size"; 

		}    

		elseif ($file_exists) { 

			$result = "El Foto Existe en el Servidor, Intente nuevamente por favor."; 

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

					$mifile='imagenes/galeria/'.$upload_class->file_name;

					$handle = new upload($mifile);

					if ($handle->uploaded) 
					{
					    $handle->image_resize          = true;

						$handle->image_ratio           = true;

						$handle->image_y               = 125;

						$handle->image_x               = 125;

						$handle->process('imagenes/galeria/chica/');

						if (!($handle->processed)) 
						{
					        echo 'error : ' . $handle->error;
					    }

					}
			} 
		} 	

		return $result;	

	}
	
	function eliminar_tcp()
	{
		$llave=$_GET['gvi'];
	
		$mi=$this->nombre_imagen($llave);
		
		$mi2=$this->nombre_imagen2($llave);
		
		if(trim($mi)<>"")
		{
			$mifile="imagenes/galeria/$mi";
		
			@unlink($mifile);
			
			$mifile="imagenes/galeria/$mi2";
		
			@unlink($mifile);
			
			$mifile="imagenes/galeria/chica/$mi2";
		
			@unlink($mifile);
			
		}
		
		$conec= new ADO();
		
		$sql="delete from galeria_video where gvi_id='".$_GET['gvi']."'";
		
		$conec->ejecutar($sql);
		
		$mensaje='Video Eliminado Correctamente.';
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=VIDEOS&id='.$_GET['id']);
		
	}
	
	function nombre_imagen($id)
	{
		$conec= new ADO();
		
		$sql="select gvi_archivo from galeria_video where gvi_id='".$id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->gvi_archivo;
	}
	
	function nombre_imagen2($id)
	{
		$conec= new ADO();
		
		$sql="select gvi_caratula from galeria_video where gvi_id='".$id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->gvi_caratula;
	}
}
?>