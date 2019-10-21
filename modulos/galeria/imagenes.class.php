<?php

class IMAGENES extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function IMAGENES()
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
				<th>Foto</th>
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
		galeria_foto
		where gfo_gal_id='".$_GET['id']."'
		order by gfo_id desc ";
		//echo $sql;

		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		for($i=0;$i<$num;$i++)
			{
				
				$objeto=$conec->get_objeto();
				echo '<tr>';
									
					echo "<td>";
						?>
						<center><img src="imagenes/galeria/chica/<?php echo $objeto->gfo_archivo; ?>" border="0" width="100"></center>
						<?php
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->gfo_descripcion;
					echo "&nbsp;</td>";
					?>
					<td>
						<center>
						<a href="javascript:ejecutar_script('<?php echo $_GET['id']; ?>','<?php echo $objeto->gfo_id; ?>');"><img src="images/b_drop.png" alt="ELIMINAR" title="ELIMINAR" border="0"></a>
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
			$valores[$num]["etiqueta"]="Foto";
			$valores[$num]["valor"]=$_FILES['gfo_archivo']['name'];
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
				
				$url.='&tarea=FOTOS';
				
				$url.='&id='.$_GET['id'];
				

		
		    $this->formulario->dibujar_tarea('GALERIA - FOTOS');
		
			if($this->mensaje<>"")
			{
				$this->formulario->mensaje('Error',$this->mensaje);
			}
			?>

		<script>		
		function ejecutar_script(id,gfo){
				var txt = 'Esta seguro de eliminar la foto?';
				
				$.prompt(txt,{ 
					buttons:{Si:true, No:false},
					callback: function(v,m,f){
						
						if(v){
								location.href='gestor.php?mod=galeria&acc=ELIMINAR&tarea=FOTOS&id='+id+'&gfo='+gfo;
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
							   <div class="Etiqueta" ><span class="flechas1">* </span>Foto</div>
							   <div id="CajaInput">
									<input  name="gfo_archivo" type="file" id="gfo_archivo" />
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Descripción</div>
							   <div id="CajaInput">
							   <textarea class="area_texto" name="gfo_descripcion" id="gfo_descripcion" cols="31" rows="3"><?php echo $_POST['gfo_descripcion']?></textarea>
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
			
		
		$result=$this->subir_imagen($nombre_archivo,$_FILES['gfo_archivo']['name'],$_FILES['gfo_archivo']['tmp_name']);
		
		$sql="insert into galeria_foto(gfo_archivo,gfo_descripcion,gfo_gal_id) values 
							('".$nombre_archivo."','".$_POST['gfo_descripcion']."','".$_GET['id']."')";
		
		if(trim($result)<>'')
		{
			$this->formulario->ventana_volver($result,$this->link.'?mod='.$this->modulo.'&tarea=FOTOS&id='.$_GET['id']);
		}
		else 
		{
			$conec->ejecutar($sql);
			
			$mensaje='Foto Agregada Correctamente.';
		
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=FOTOS&id='.$_GET['id']);
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
		 
		 $ext = strtolower($aname[$nume-1]);
		 
		 $upload_class->file_name = $nn.'.'.$ext;
		 
		 $nombre_imagen=$upload_class->file_name;		 		 

		 $upload_class->upload_dir = "imagenes/galeria/"; 

		 $upload_class->upload_log_dir = "imagenes/galeria/upload_logs/"; 

		 $upload_class->max_file_size = 3245728; 	

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

						$handle->image_y               = 140;

						$handle->image_x               = 187;

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
		$llave=$_GET['gfo'];
	
		$mi=$this->nombre_imagen($llave);
		
		if(trim($mi)<>"")
		{
			$mifile="imagenes/galeria/$mi";
		
			@unlink($mifile);
			
			$mifile2="imagenes/galeria/chica/$mi";
		
			@unlink($mifile2);
			
		}
		
		$conec= new ADO();
		
		$sql="delete from galeria_foto where gfo_id='".$_GET['gfo']."'";
		
		$conec->ejecutar($sql);
		
		$mensaje='Foto Eliminada Correctamente.';
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=FOTOS&id='.$_GET['id']);
		
	}
	
	function nombre_imagen($id)
	{
		$conec= new ADO();
		
		$sql="select gfo_archivo from galeria_foto where gfo_id='".$id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->gfo_archivo;
	}
}
?>