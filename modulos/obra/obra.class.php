<?php

class OBRA extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function OBRA()
	{
		//permisos
		$this->ele_id=142;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="obr_titulo";
		$this->arreglo_campos[0]["texto"]="Nombre";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;

		
		$this->link='gestor.php';
		
		$this->modulo='obra';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('URBANIZACION');
		
		
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
		$sql="SELECT obr_id,obr_titulo,obr_imagen,obr_descripcion FROM obra";
		
		$this->set_sql($sql);
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Nombre</th>
	           
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
						echo $objeto->obr_titulo;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $this->get_opciones($objeto->obr_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from obra
				where obr_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		 	 	 	 	 	 	 	 	 
		$_POST['obr_titulo']=$objeto->obr_titulo;
		$_POST['obr_descripcion']=$objeto->obr_descripcion;
		$_POST['obr_imagen']=$objeto->obr_imagen;
		
		
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Nombre";
			$valores[$num]["valor"]=$_POST['obr_titulo'];
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

		
		    $this->formulario->dibujar_tarea('OBRA');
		
			if($this->mensaje<>"")
			{
				$this->formulario->dibujar_mensaje($this->mensaje);
			}
			?>
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Imagen</div>
							   <div id="CajaInput">
							   <?php
								if($_POST['obr_imagen']<>"")
								{	$foto=$_POST['obr_imagen'];
									$b=true;
								}	
								else
								{	$foto='sin_foto.gif';
									$b=false;
								}
								if(($ver)||($cargar))
								{	?>
									<img src="imagenes/obra/<?php echo $foto;?>" border="0" width="70"><?php if($b and $_GET['tarea']=='MODIFICAR') echo '<a href="'.$this->link.'?mod='.$this->modulo.'&tarea=MODIFICAR&id='.$_GET['id'].'&img='.$foto.'&acc=Imagen"><img src="images/b_drop.png" border="0"></a>';?><br>
									<input   name="obr_imagen" type="file" id="obr_imagen" /><span class="flechas1"></span>
									<?php
								}
								else 
								{
									?>
									<input  name="obr_imagen" type="file" id="obr_imagen" /><span class="flechas1"></span>
									<?php
								}
								?>
								<input   name="fotooculta" type="hidden" id="fotooculta" value="<?php echo $_POST['obr_imagen'].$_POST['fotooculta'];?>"/>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Titulo</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="obr_titulo" id="obr_titulo" maxlength="250"  size="40" value="<?php echo $_POST['obr_titulo'];?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Descripción</div>
							   <div id="CajaInput">	   
							   <textarea rows="10" cols="50" name="obr_descripcion" id="obr_descripcion"><?php echo $_POST['obr_descripcion'];?></textarea>									
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
				
		if($_FILES['obr_imagen']['name']<>"")
		{
			$result=$this->subir_imagen($nombre_archivo,$_FILES['obr_imagen']['name'],$_FILES['obr_imagen']['tmp_name']);
			
			$sql="insert into obra(obr_titulo,obr_imagen,obr_descripcion) values 
								('".$_POST['obr_titulo']."','".$nombre_archivo."','".$_POST['obr_descripcion']."')";
				
			if(trim($result)<>'')
			{
				$this->formulario->ventana_volver($result,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
			}
			else 
			{
				$conec->ejecutar($sql);
				
				$mensaje='Urbanizacion Agregada Correctamente!!!';
				
				$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
			}
		}
		else
		{
			$sql="insert into obra(obr_titulo,obr_descripcion) values 
								('".$_POST['obr_titulo']."','".$_POST['obr_descripcion']."')";

			//echo $sql;
			$conec->ejecutar($sql);

			$mensaje='Urbanizacion Agregada Correctamente!!!';

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

		 $upload_class->upload_dir = "imagenes/obra/"; 

		 $upload_class->upload_log_dir = "imagenes/obra/upload_logs/"; 

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
				
					$mifile='imagenes/obra/'.$upload_class->file_name;
					
					$handle = new upload($mifile);
					
					if ($handle->uploaded) 
					{
					    
						$handle->image_resize          = true;
						$handle->image_ratio           = true;
						$handle->image_y               = 185;
						$handle->image_x               = 185;
			   
						$handle->process('imagenes/obra/chica/');
					    
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
		
		if($_FILES['obr_imagen']['name']<>"")
		{	 	 	 	 	 	 	
			 	 	 	 	
			$result=$this->subir_imagen($nombre_archivo,$_FILES['obr_imagen']['name'],$_FILES['obr_imagen']['tmp_name']);
			
				$sql="update obra set 
								obr_titulo='".$_POST['obr_titulo']."',
								obr_imagen='".$nombre_archivo."',
								obr_descripcion='".$_POST['obr_descripcion']."'
								where obr_id = '".$_GET['id']."'";
						
			
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
					$mifile="imagenes/obra/$mi";
				
					@unlink($mifile);
				
				}
				
				
				$conec->ejecutar($sql);
			
				$mensaje='Urbanizacion Modificada Correctamente!!!';
					
				$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
			}
		}
		else
		{
			
				$sql="update obra set 
								obr_titulo='".$_POST['obr_titulo']."',							
								obr_descripcion='".$_POST['obr_descripcion']."'
								where obr_id = '".$_GET['id']."'";

			$conec->ejecutar($sql);

			$mensaje='Urbanizacion Modificada Correctamente!!!';

			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		}
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar la Urbanizacion?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo".'&tarea=ELIMINAR','obr_id');
	}
	
	function eliminar_tcp()
	{
		
			$llave=$_POST['obr_id'];
		
			$mi=$this->nombre_imagen($llave);
			
			if(trim($mi)<>"")
			{
				$mifile="imagenes/promociones/$mi";
			
				@unlink($mifile);
				
			}
			
			$conec= new ADO();
			
			$sql="delete from obra where obr_id='".$_POST['obr_id']."'";
			
			$conec->ejecutar($sql);
			
			$mensaje='Urbanizacion Eliminada Correctamente!!!';
				
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
	}
	
	function nombre_imagen($id)
	{
		$conec= new ADO();
		
		$sql="select obr_imagen from obra where obr_id='".$id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->obr_imagen;
	}
	
	function eliminar_imagen()
	{
		$conec= new ADO();
		
		$mi=$_GET['img'];
		
		$mifile="imagenes/obra/$mi";
				
		@unlink($mifile);
		
		
		$conec= new ADO();
		
		$sql="update obra set 
						obr_imagen=''
						where obr_id = '".$_GET['id']."'";
						
		$conec->ejecutar($sql);
		
		$mensaje='Imagen Eliminado Correctamente!';
			
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=MODIFICAR&id='.$_GET['id']);
		
	}
}
?>
