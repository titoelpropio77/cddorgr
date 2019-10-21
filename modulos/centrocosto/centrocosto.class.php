<?php

class CENTROCOSTO extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function CENTROCOSTO()
	{
		//permisos
		$this->ele_id=104;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="cco_descripcion";
		$this->arreglo_campos[0]["texto"]="Descripción";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;
		
		
		$this->link='gestor.php';
		
		$this->modulo='centrocosto';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('CENTROCOSTO');
		
		
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
		$sql="SELECT * FROM centrocosto";
		
		$this->set_sql($sql);
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Descripción</th>
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
						echo $objeto->cco_descripcion;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $this->get_opciones($objeto->cco_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from centrocosto
				where cco_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['cco_descripcion']=$objeto->cco_descripcion;
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Descripción";
			$valores[$num]["valor"]=$_POST['cco_descripcion'];
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
				
				$red=$url;
				
				if(!($ver))
				{
					$url.="&tarea=".$_GET['tarea'];
				}
				
				if($cargar)
				{
					$url.='&id='.$_GET['id'];
				}

		
		    $this->formulario->dibujar_tarea("CENTRO COSTO");
		
			if($this->mensaje<>"")
			{
				$this->formulario->mensaje('Error',$this->mensaje);
			}
			?>
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
							
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Descripción</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="cco_descripcion" id="cco_descripcion" size="60" maxlength="250" value="<?php echo $_POST['cco_descripcion'];?>">
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
				
		$sql="insert into centrocosto(cco_descripcion) values 
							('".$_POST['cco_descripcion']."')";

		$conec->ejecutar($sql);

		$mensaje='Centro de Costo Agregado Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function modificar_tcp()
	{
		$conec= new ADO();
		
		$sql="update centrocosto set 
							cco_descripcion='".$_POST['cco_descripcion']."'
							where cco_id = '".$_GET['id']."'";

		$conec->ejecutar($sql);

		$mensaje='Centro de Costo Modificado Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar el Banco?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'cco_id');
	}
	
	function eliminar_tcp()
	{
	   $conec= new ADO();
		
        $sql = "Select * from comprobante_detalle where cde_cco_id = '".$_POST['cco_id']."'";		
        $conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		if ($num > 0)
		{
		    $mensaje='El Centro de Costo no puede ser eliminado porque esta siendo usado en Comprobantes';
		}
		else
		{	
	
			$conec= new ADO();
		
			$sql="delete from centrocosto where cco_id='".$_POST['cco_id']."'";
			
			$conec->ejecutar($sql);
			
			$mensaje='Centro de costo Eliminado Correctamente!!!';
		}	
		
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
}
?>