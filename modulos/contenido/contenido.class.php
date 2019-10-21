<?php

class CONTENIDO extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function CONTENIDO()
	{
		//permisos
		$this->ele_id=141;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="cli_nombre";
		$this->arreglo_campos[0]["texto"]="Nombre";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;

		
		$this->link='gestor.php';
		
		$this->modulo='contenido';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('CONTENIDO');
		
		
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
		$sql="SELECT cli_id,cli_nombre,cli_imagen,cli_link FROM clientes";
		
		$this->set_sql($sql);
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>T&iacute;tulo</th>
	           
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
						echo $objeto->cli_nombre;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $this->get_opciones($objeto->cli_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from clientes
				where cli_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		 	 	 	 	 	 	 	 	 
		$_POST['cli_nombre']=$objeto->cli_nombre;
		
		$_POST['cli_imagen']=$objeto->cli_imagen;
		
		$_POST['cli_link']=$objeto->cli_link;
		
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Nombre";
			$valores[$num]["valor"]=$_POST['cli_nombre'];
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

		
		    $this->formulario->dibujar_tarea('CLIENTES');
		
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
								if($_POST['cli_imagen']<>"")
								{	$foto=$_POST['cli_imagen'];
									$b=true;
								}	
								else
								{	$foto='sin_foto.gif';
									$b=false;
								}
								if(($ver)||($cargar))
								{	?>
									<img src="imagenes/contenido/<?php echo $foto;?>" border="0" width="80"><?php if($b and $_GET['tarea']=='MODIFICAR') echo '<a href="'.$this->link.'?mod='.$this->modulo.'&tarea=MODIFICAR&id='.$_GET['id'].'&img='.$foto.'&acc=Imagen"><img src="images/b_drop.png" border="0"></a>';?><br>
									<input   name="cli_imagen" type="file" id="cli_imagen" /><span class="flechas1"></span>
									<?php
								}
								else 
								{
									?>
									<input  name="cli_imagen" type="file" id="cli_imagen" /><span class="flechas1"></span>
									<?php
								}
								?>
								<input   name="fotooculta" type="hidden" id="fotooculta" value="<?php echo $_POST['cli_imagen'].$_POST['fotooculta'];?>"/>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>T&iacute;tulo</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="cli_nombre" id="cli_nombre" maxlength="250"  size="40" value="<?php echo $_POST['cli_nombre'];?>">
							   </div>
							</div>
							<!--Fin-->
							
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Descripción</div>
							   <div id="CajaInput">
								  <textarea rows="20" cols="50" name="cli_link" id="cli_link"><?php echo $_POST['cli_link'];?></textarea>									
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
				
		if($_FILES['cli_imagen']['name']<>"")
		{
			$result=$this->subir_imagen($nombre_archivo,$_FILES['cli_imagen']['name'],$_FILES['cli_imagen']['tmp_name']);
			
			$sql="insert into clientes(cli_nombre,cli_imagen,cli_link) values 
								('".$_POST['cli_nombre']."','".$nombre_archivo."','".$_POST['cli_link']."')";
				
			if(trim($result)<>'')
			{
				$this->formulario->ventana_volver($result,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
			}
			else 
			{
				$conec->ejecutar($sql);
				
				$mensaje='Contenido Agregado Correctamente!!!';
				
				$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
			}
		}
		else
		{
			$sql="insert into clientes(cli_nombre,cli_link) values 
								('".$_POST['cli_nombre']."','".$_POST['cli_link']."')";

			//echo $sql;
			$conec->ejecutar($sql);

			$mensaje='Contenido Agregado Correctamente!!!';

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

		 $upload_class->upload_dir = "imagenes/contenido/"; 

		 $upload_class->upload_log_dir = "imagenes/contenido/upload_logs/"; 

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
		
		if($_FILES['cli_imagen']['name']<>"")
		{	 	 	 	 	 	 	
			 	 	 	 	
			$result=$this->subir_imagen($nombre_archivo,$_FILES['cli_imagen']['name'],$_FILES['cli_imagen']['tmp_name']);
			
				$sql="update clientes set 
								cli_nombre='".$_POST['cli_nombre']."',
								cli_imagen='".$nombre_archivo."',
								cli_link='".$_POST['cli_link']."'
								where cli_id = '".$_GET['id']."'";
						
			
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
					$mifile="imagenes/contenido/$mi";
				
					@unlink($mifile);
				
				}
				
				
				$conec->ejecutar($sql);
			
				$mensaje='Contenido Modificado Correctamente!!!';
					
				$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
			}
		}
		else
		{
			
				$sql="update clientes set 
								cli_nombre='".$_POST['cli_nombre']."',								
								cli_link='".$_POST['cli_link']."'
								where cli_id = '".$_GET['id']."'";

			$conec->ejecutar($sql);

			$mensaje='Contenido Modificado Correctamente!!!';

			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		}
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar el Contenido?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo".'&tarea=ELIMINAR','cli_id');
	}
	
	function eliminar_tcp()
	{
		
			$llave=$_POST['cli_id'];
		
			$mi=$this->nombre_imagen($llave);
			
			if(trim($mi)<>"")
			{
				$mifile="imagenes/contenido/$mi";
			
				@unlink($mifile);
				
			}
			
			$conec= new ADO();
			
			$sql="delete from clientes where cli_id='".$_POST['cli_id']."'";
			
			$conec->ejecutar($sql);
			
			$mensaje='Contenido Eliminado Correctamente!!!';
				
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
	}
	
	function nombre_imagen($id)
	{
		$conec= new ADO();
		
		$sql="select cli_imagen from clientes where cli_id='".$id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->cli_imagen;
	}
	
	function eliminar_imagen()
	{
		$conec= new ADO();
		
		$mi=$_GET['img'];
		
		$mifile="imagenes/contenido/$mi";
				
		@unlink($mifile);
		
		
		$conec= new ADO();
		
		$sql="update clientes set 
						cli_imagen=''
						where cli_id = '".$_GET['id']."'";
						
		$conec->ejecutar($sql);
		
		$mensaje='Imagen Eliminada Correctamente!';
			
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=MODIFICAR&id='.$_GET['id']);
		
	}
}
?>
