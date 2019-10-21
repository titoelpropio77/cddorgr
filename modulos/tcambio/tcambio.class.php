<?php

class TCAMBIO extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function TCAMBIO()
	{
		//permisos
		$this->ele_id=69;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="tca_fecha";
		$this->arreglo_campos[0]["texto"]="Fecha";
		$this->arreglo_campos[0]["tipo"]="fecha";
		$this->arreglo_campos[0]["tamanio"]=12;
		
		
		$this->link='gestor.php';
		
		$this->modulo='tcambio';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('TIPO DE CAMBIO');
		
		
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
		$sql="SELECT * FROM tipo_cambio";
		
		$this->set_sql($sql,'  order by tca_id desc');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Fecha</th>
				<th>Valor Venta</th>
                <th>Valor Compra</th>
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
						echo $conversor->get_fecha_latina($objeto->tca_fecha);
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->tca_valor;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->tca_valor_compra;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $this->get_opciones($objeto->tca_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from tipo_cambio
				where tca_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['tca_fecha']=$objeto->tca_fecha;
		
		$_POST['tca_valor']=$objeto->tca_valor;
		
		$_POST['tca_valor_compra']=$objeto->tca_valor_compra;
	}
	
	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Fecha";
			$valores[$num]["valor"]=$_POST['tca_fecha'];
			$valores[$num]["tipo"]="fecha";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Valor Venta";
			$valores[$num]["valor"]=$_POST['tca_valor'];
			$valores[$num]["tipo"]="real";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Valor Compra";
			$valores[$num]["valor"]=$_POST['tca_valor_compra'];
			$valores[$num]["tipo"]="real";
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
							   <div class="Etiqueta" ><span class="flechas1">* </span>Fecha</div>
								 <div id="CajaInput">
									<input readonly="readonly" class="caja_texto" name="tca_fecha" id="tca_fecha" size="12" value="<?php echo date('Y-m-d');?>" type="text">
								</div>		
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Valor Venta</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="tca_valor" id="tca_valor" size="10" maxlength="250" value="<?php echo $_POST['tca_valor'];?>">
							   </div>
							</div>
							<!--Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Valor Compra</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="tca_valor_compra" id="tca_valor_compra" size="10" maxlength="250" value="<?php echo $_POST['tca_valor_compra'];?>">
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
				
		$sql="insert into tipo_cambio(tca_fecha,tca_valor,tca_valor_compra) values 
							('".$_POST['tca_fecha']."','".$_POST['tca_valor']."','".$_POST['tca_valor_compra']."')";

		$conec->ejecutar($sql);

		$mensaje='Tipo de Cambio Agregado Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function modificar_tcp()
	{
		$conec= new ADO();
		
		$sql="update tipo_cambio set 
							tca_fecha='".$_POST['tca_fecha']."',
							tca_valor='".$_POST['tca_valor']."'
							where tca_id = '".$_GET['id']."'";

		$conec->ejecutar($sql);

		$mensaje='Tipo de Cambio Modificado Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar el Tipo de Cambio?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'tca_id');
	}
	
	function eliminar_tcp()
	{
		$conec= new ADO();
		
		$sql="delete from tipo_cambio where tca_id='".$_POST['tca_id']."'";
		
		$conec->ejecutar($sql);
		
		$mensaje='Tipo de Cambio Eliminado Correctamente!!!';
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
}
?>