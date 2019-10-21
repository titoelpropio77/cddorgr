<?php

class LOG extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function LOG()
	{
		//permisos
		$this->ele_id=120;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$num = 0;
                
                $this->arreglo_campos[$num]["nombre"]="log_usu_id";
		$this->arreglo_campos[$num]["texto"]="Login";
		$this->arreglo_campos[$num]["tipo"]="cadena";
		$this->arreglo_campos[$num]["tamanio"]=40;
		$num++;
                
		$this->arreglo_campos[$num]["nombre"]="log_nombre";
		$this->arreglo_campos[$num]["texto"]="Nombre Completo";
		$this->arreglo_campos[$num]["tipo"]="cadena";
		$this->arreglo_campos[$num]["tamanio"]=40;
		$num++;
//		$this->arreglo_campos[1]["nombre"]="int_apellido";
//		$this->arreglo_campos[1]["texto"]="Apellido";
//		$this->arreglo_campos[1]["tipo"]="cadena";
//		$this->arreglo_campos[1]["tamanio"]=40;
		
		$this->arreglo_campos[$num]["nombre"]="log_fecha";
		$this->arreglo_campos[$num]["texto"]="Fecha";
		$this->arreglo_campos[$num]["tipo"]="fecha";
		$this->arreglo_campos[$num]["tamanio"]=12;
                $num++;
		$this->arreglo_campos[$num]["nombre"]="log_hora";
		$this->arreglo_campos[$num]["texto"]="hora";
		$this->arreglo_campos[$num]["tipo"]="cadena";
		$this->arreglo_campos[$num]["tamanio"]=12;
                $num++;
		$this->arreglo_campos[$num]["nombre"]="log_token";
		$this->arreglo_campos[$num]["texto"]="Token";
		$this->arreglo_campos[$num]["tipo"]="cadena";
		$this->arreglo_campos[$num]["tamanio"]=12;
                $num++;
		$this->arreglo_campos[$num]["nombre"]="log_modulo";
		$this->arreglo_campos[$num]["texto"]="Modulo";
		$this->arreglo_campos[$num]["tipo"]="cadena";
		$this->arreglo_campos[$num]["tamanio"]=12;
                $num++;
		$this->arreglo_campos[$num]["nombre"]="log_tarea";
		$this->arreglo_campos[$num]["texto"]="Tarea";
		$this->arreglo_campos[$num]["tipo"]="cadena";
		$this->arreglo_campos[$num]["tamanio"]=12;
                $num++;
		$this->arreglo_campos[$num]["nombre"]="log_modulo_id";
		$this->arreglo_campos[$num]["texto"]="Modulo ID";
		$this->arreglo_campos[$num]["tipo"]="cadena";
		$this->arreglo_campos[$num]["tamanio"]=12;
		$num++;
		$this->arreglo_campos[$num]["nombre"]="log_accion";
		$this->arreglo_campos[$num]["texto"]="Acción";
		$this->arreglo_campos[$num]["tipo"]="cadena";
		$this->arreglo_campos[$num]["tamanio"]=40;
		
		
		$this->link='gestor.php';
		
		$this->modulo='log';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('LOG');
		
		
	}
		
	function dibujar_busqueda()
	{
		
		$this->formulario->dibujar_cabecera();
		
		$this->dibujar_listado();
	}
	
		
	function set_opciones()
	{
				
	}
	
	function dibujar_listado()
	{
            if ($_GET[mostrar] == 'hoy') {
                $filtro_adhoc = " where log_fecha='" . date('Y-m-d') . "' and log_usu_id='admin'";                				
            }
            
            $sql="SELECT 
		*
		FROM 
		ad_logs $filtro_adhoc
		";
		
		$this->set_sql($sql,' order by log_id desc ');
				
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
				<th>Fecha</th>
				<th>Hora</th>
                                <th>Token</th>
                                <th>Modulo</th>
                                <th>Origen</th>
				<th>Acción</th>
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
						echo $objeto->log_usu_id;
					echo "</td>";
					echo "<td>";
						echo $objeto->log_nombre;
					echo "</td>";
					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->log_fecha);
					echo "</td>";
					echo "<td>";
						echo $objeto->log_hora;
					echo "</td>";
					echo "<td>";
						echo $objeto->log_token;
					echo "</td>";
					echo "<td>";
						echo "$objeto->log_modulo:$objeto->log_tarea:$objeto->log_modulo_id";
					echo "</td>";
					echo "<td>";
						echo $objeto->log_tipo_accion;
					echo "</td>";
					echo "<td>";
						echo $objeto->log_accion;
					echo "</td>";
					
					
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from tipo_pago
				where tpa_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['tpa_descripcion']=$objeto->tpa_descripcion;
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Descripción";
			$valores[$num]["valor"]=$_POST['tpa_descripcion'];
			$valores[$num]["tipo"]="texto";
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

		
		    $this->formulario->dibujar_tarea('PERSONA');
		
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
							   <div class="Etiqueta" ><span class="flechas1">* </span>Descripción</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="tpa_descripcion" id="tpa_descripcion" size="60" maxlength="250" value="<?php echo $_POST['tpa_descripcion'];?>">
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
				
		$sql="insert into tipo_pago(tpa_descripcion) values 
							('".$_POST['tpa_descripcion']."')";

		$conec->ejecutar($sql);

		$mensaje='Tipo de Pago Agregado Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function modificar_tcp()
	{
		$conec= new ADO();
		
		$sql="update tipo_pago set 
							tpa_descripcion='".$_POST['tpa_descripcion']."'
							where tpa_id = '".$_GET['id']."'";

		$conec->ejecutar($sql);

		$mensaje='Tipo de Pago Modificado Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar el Tipo de Pago?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'tpa_id');
	}
	
	function eliminar_tcp()
	{
		$conec= new ADO();
		
		$sql="delete from tipo_pago where tpa_id='".$_POST['tpa_id']."'";
		
		$conec->ejecutar($sql);
		
		$mensaje='Tipo de Pago Eliminado Correctamente!!!';
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
}
?>