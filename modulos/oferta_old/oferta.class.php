<?php

class OFERTA extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function OFERTA()
	{
		//permisos
		$this->ele_id=140;
		
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
		
		$this->modulo='oferta';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('POLITICAS');
		
		
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
		$sql="SELECT * FROM oferta";
		
		$this->set_sql($sql,' order by ofe_orden desc ');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function orden($tecnologia,$accion,$ant_orden)
	{
		$conec= new ADO();
		
		if($accion=='s')
			$cad=" where ofe_orden > $ant_orden order by ofe_orden asc";
		else
			$cad=" where ofe_orden < $ant_orden order by ofe_orden desc";

		$consulta = "
		select 
			ofe_id,ofe_orden 
		from 
			oferta
		$cad
		limit 0,1
		";	

		$conec->ejecutar($consulta);

		$num = $conec->get_num_registros();   

		if($num > 0)
		{
			$objeto=$conec->get_objeto();
			
			$nu_orden=$objeto->ofe_orden;
			
			$id=$objeto->ofe_id;
			
			$consulta = "update oferta set ofe_orden='$nu_orden' where ofe_id='$tecnologia'";	

			$conec->ejecutar($consulta);
			
			$consulta = "update oferta set ofe_orden='$ant_orden' where ofe_id='$id'";	

			$conec->ejecutar($consulta);
		}	
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>T&iacute;tulo</th>
							
				<th>Orden</th>
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
						echo $objeto->ofe_titulo;
					echo "&nbsp;</td>";
				
					echo "<td>";
						?>
						<center><a href="<?php echo $this->link.'?mod='.$this->modulo.'&tarea=ACCEDER&tec='.$objeto->ofe_id.'&acc=s&or='.$objeto->ofe_orden;?>"><img src="images/subir.png" border="0"></a><a href="<?php echo $this->link.'?mod='.$this->modulo.'&tarea=ACCEDER&tec='.$objeto->ofe_id.'&acc=b&or='.$objeto->ofe_orden;?>"><img src="images/bajarr.png" border="0"></a></center>
						<?php
					echo "</td>";
					
					echo "<td>";
						if($objeto->ofe_estado=='H') echo 'Habilitado'; else echo 'Deshabilitado';
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $this->get_opciones($objeto->ofe_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from oferta
				where ofe_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		 	 	 	 	 	 	 	 	 
		$_POST['ser_nombre']=$objeto->ofe_titulo;	
		$_POST['ser_descripcion']=$objeto->ofe_publicar;	
		$_POST['ofe_estado']=$objeto->ofe_estado;
		
		
		
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
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

		
		    $this->formulario->dibujar_tarea('OFERTAS');
		
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
							   <div class="Etiqueta" ><span class="flechas1">* </span>Pregunta</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="ser_nombre" id="ser_nombre" maxlength="250"  size="50" value="<?php echo $_POST['ser_nombre'];?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Respuesta</div>
							   <div id="CajaInput">	   
							   <textarea rows="10" cols="50" name="ser_descripcion" id="ser_descripcion"><?php echo $_POST['ser_descripcion'];?></textarea>									
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Estado</div>
							   <div id="CajaInput">
								<select name="ofe_estado" class="caja_texto">
									<option value="H" <?php if($_POST['ofe_estado']=='H') echo 'selected="selected"'; ?>>Habilitado</option>
									<option value="D" <?php if($_POST['ofe_estado']=='D') echo 'selected="selected"'; ?>>Deshabilitado</option>
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
		
		$sql=" select max(ofe_orden) as ultimo from oferta";
	
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$orden=$objeto->ultimo + 1;
				
		$sql="insert into oferta(ofe_titulo,ofe_publicar,ofe_orden,ofe_estado) 
						values 
								('".$_POST['ser_nombre']."','".$_POST['ser_descripcion']."','$orden','".$_POST['ofe_estado']."')";

		$conec->ejecutar($sql,false);
		
		$mensaje='Pregunta Agregada Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		
	}	
	
	function modificar_tcp()
	{
		$conec= new ADO();
					
		$sql="update oferta set 
							ofe_titulo='".$_POST['ser_nombre']."',
							ofe_publicar='".$_POST['ser_descripcion']."',
							ofe_estado='".$_POST['ofe_estado']."'
							where ofe_id = '".$_GET['id']."'";

		$conec->ejecutar($sql);
		
		$mensaje='Pregunta Modificado Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar la Pregunta?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo".'&tarea=ELIMINAR','ser_id');
	}
	
	function eliminar_tcp()
	{
		
		$conec= new ADO();
		
		$sql="delete from oferta where ofe_id='".$_POST['ser_id']."'";
		
		$conec->ejecutar($sql);

		$mensaje='Pregunta Eliminado Correctamente!!!';
		
	
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
	}
	
}
?>