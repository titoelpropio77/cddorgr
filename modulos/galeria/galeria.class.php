<?php

class GALERIA extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function GALERIA()
	{
		//permisos
		$this->ele_id=145;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="gal_titulo";
		$this->arreglo_campos[0]["texto"]="Título";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;
		
		$this->link='gestor.php';
		
		$this->modulo='galeria';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('GALERIA');
		
		
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
		
		if($this->verificar_permisos('FOTOS'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='FOTOS';
			$this->arreglo_opciones[$nun]["imagen"]='images/fotos.png';
			$this->arreglo_opciones[$nun]["nombre"]='FOTOS';
			$nun++;
		}
		
		if($this->verificar_permisos('VIDEOS'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='VIDEOS';
			$this->arreglo_opciones[$nun]["imagen"]='images/videos.png';
			$this->arreglo_opciones[$nun]["nombre"]='VIDEOS';
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
		$sql="SELECT * FROM galeria";
		
		$this->set_sql($sql,' order by gal_orden desc ');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Título</th>
				<th>Estado</th>
				<th>Orden</th>
				<th class="tOpciones" width="140px">Opciones</th>
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
						echo $objeto->gal_titulo;
					echo "&nbsp;</td>";
					echo "<td>";
						if($objeto->gal_estado=='1') echo 'Visible'; else echo 'No Visible';
					echo "</td>";
					echo "<td>";
						?>
						<center><a href="<?php echo $this->link.'?mod='.$this->modulo.'&tarea=ACCEDER&cli='.$objeto->gal_id.'&acc=s&or='.$objeto->gal_orden;?>"><img src="images/subir.png" border="0"></a><a href="<?php echo $this->link.'?mod='.$this->modulo.'&tarea=ACCEDER&cli='.$objeto->gal_id.'&acc=b&or='.$objeto->gal_orden;?>"><img src="images/bajarr.png" border="0"></a></center>
						<?php
					echo "</td>";
					echo "<td>";
						echo $this->get_opciones($objeto->gal_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function orden($galeria,$accion,$ant_orden)
	{
		$conec= new ADO();
		
		if($accion=='s')
			$cad=" where gal_orden > $ant_orden order by gal_orden asc";
		else
			$cad=" where gal_orden < $ant_orden order by gal_orden desc";

		$consulta = "
		select 
			gal_id,gal_orden 
		from 
			galeria
		$cad
		limit 0,1
		";	

		$conec->ejecutar($consulta);

		$num = $conec->get_num_registros();   

		if($num > 0)
		{
			$objeto=$conec->get_objeto();
			
			$nu_orden=$objeto->gal_orden;
			
			$id=$objeto->gal_id;
			
			$consulta = "update galeria set gal_orden='$nu_orden' where gal_id='$galeria'";	

			$conec->ejecutar($consulta);
			
			$consulta = "update galeria set gal_orden='$ant_orden' where gal_id='$id'";	

			$conec->ejecutar($consulta);
		}	
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from galeria
				where gal_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		 	 	 	 	 	 	 	 	 
		$_POST['gal_titulo']=$objeto->gal_titulo;
		
		$_POST['gal_estado']=$objeto->gal_estado;
		
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			
			$valores[$num]["etiqueta"]="Título";
			$valores[$num]["valor"]=$_POST['gal_titulo'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			
			$valores[$num]["etiqueta"]="Estado";
			$valores[$num]["valor"]=$_POST['gal_estado'];
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

		
		    $this->formulario->dibujar_tarea('GALERIA');
		
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
							   <div class="Etiqueta" ><span class="flechas1">* </span>Título</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="gal_titulo" id="gal_titulo" maxlength="250"  size="40" value="<?php echo $_POST['gal_titulo'];?>">
							   </div>
							</div>
							<!--Fin-->
							
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Estado</div>
							   <div id="CajaInput">
								    <select name="gal_estado" class="caja_texto">
									<option value="" >Seleccione</option>
									<option value="1" <?php if($_POST['gal_estado']=='1') echo 'selected="selected"'; ?>>Visible</option>
									<option value="0" <?php if($_POST['gal_estado']=='0') echo 'selected="selected"'; ?>>No Visible</option>
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
		
		$sql=" select max(gal_orden) as ultimo from galeria ";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$orden=$objeto->ultimo + 1;
				
		$sql="insert into galeria(gal_titulo,gal_orden,gal_estado) values 
							('".$_POST['gal_titulo']."','$orden','".$_POST['gal_estado']."')";

		$conec->ejecutar($sql);

		$mensaje='Galeria Agregada Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		
	}
	
	function modificar_tcp()
	{
		$conec= new ADO();	
		
			$sql="update galeria set 
							gal_titulo='".$_POST['gal_titulo']."',
							gal_estado='".$_POST['gal_estado']."'
							where gal_id = '".$_GET['id']."'";

		$conec->ejecutar($sql);

		$mensaje='Galeria Modificada Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar la galeria?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo".'&tarea=ELIMINAR','gal_id');
	}
	
	function eliminar_tcp()
	{
		

		
			$conec= new ADO();
			
			$sql="delete from galeria where gal_id='".$_POST['gal_id']."'";
			
			$conec->ejecutar($sql);
	
			$mensaje='Galeria Eliminado Correctamente!!!';
			
		
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
	}
	
}
?>