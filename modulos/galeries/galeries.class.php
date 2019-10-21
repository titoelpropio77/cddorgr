<?php

class GALERIA extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	
	function GALERIA()
	{
		//permisos
		$this->ele_id=146;
		
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
		
		$this->modulo='galeries';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('FOTOS DE AVANCE');
		
		$this->usu=new USUARIO;
		
	}
	
	
	function dibujar_busqueda()
	{
		
		if ($this->usu->get_gru_id() == 'Clientes')
		{
		
			$this->formulario->dibujar_cabecera();
			
			$this->dibujar_galeria();
		
		}
		else
		{
			
			$this->formulario->dibujar_cabecera();
			
			$this->dibujar_listado();
		
		}
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
		$sql="SELECT gal_id,gal_estado,int_nombre,int_apellido 
				FROM galeriados inner join interno on(gal_titulo=int_id)";
		
		$this->set_sql($sql,' order by gal_orden desc ');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Cliente</th>
				<th>Estado</th>
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
						echo $objeto->int_nombre." ".$objeto->int_apellido;
					echo "&nbsp;</td>";
					echo "<td>";
						if($objeto->gal_estado=='1') echo 'Visible'; else echo 'No Visible';
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
			galeriados
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
			
			$consulta = "update galeriados set gal_orden='$nu_orden' where gal_id='$galeria'";	

			$conec->ejecutar($consulta);
			
			$consulta = "update galeriados set gal_orden='$ant_orden' where gal_id='$id'";	

			$conec->ejecutar($consulta);
		}	
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from galeriados
				where gal_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['gal_titulo']=$objeto->gal_titulo;
		
		$_POST['gal_estado']=$objeto->gal_estado;
				
		$fun=NEW FUNCIONES;		
		
		$_POST['usu_nombre_persona']=$fun->nombre($objeto->gal_titulo);
		
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
			
			$page="'gestor.php?mod=galeries&tarea=AGREGAR&acc=Emergente'";
			$extpage="'persona'";
			$features="'left=325,width=600,top=200,height=420,scrollbars=yes'";
			
		
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
							   <div class="Etiqueta" ><span class="flechas1">*</span>Cliente</div>
							   <div id="CajaInput">
								    <input name="gal_titulo" id="gal_titulo" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['gal_titulo']?>" size="2">
									<input name="usu_nombre_persona" id="usu_nombre_persona" readonly="readonly"  type="text" class="caja_texto" value="<?php echo $_POST['usu_nombre_persona']?>" size="40">
									<?php
									if($_GET['tarea']=='AGREGAR')
									{
									?>
										<img src="images/ir.png"  onclick="javascript:window.open(<?php echo $page;?>,<?php echo $extpage;?>,<?php echo $features;?>);">
									<?php
									}
									?>	
							   </div>

							</div>
							<!--Fin-->							
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Estado</div>
							   <div id="CajaInput">
								    <select name="gal_estado" class="caja_texto">
									<option value="" >Seleccione</option>
									<option value="1" <?php if($_POST['gal_estado']=='1') echo 'selected="selected"'; ?>>Habilitado</option>
									<option value="0" <?php if($_POST['gal_estado']=='0') echo 'selected="selected"'; ?>>Deshabilitado</option>
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
	
	function emergente()
	{
		$this->formulario->dibujar_cabecera();
		
		$valor=trim($_POST['valor']);
	?>
			
			<script>
				function poner(id,valor)
				{
					opener.document.frm_sentencia.gal_titulo.value=id;
					opener.document.frm_sentencia.usu_nombre_persona.value=valor;
					window.close();
				}			
			</script>
			<br><center><form name="form" id="form" method="POST" action="gestor.php?mod=galeries&tarea=AGREGAR&acc=Emergente">
				<table align="center">
					<tr>
						<td class="txt_contenido" colspan="2" align="center">
							<input name="valor" type="text" class="caja_texto" size="30" value="<?php echo $valor;?>">
							<input name="Submit" type="submit" class="boton" value="Buscar">
						</td>
					</tr>
				</table>
			</form><center>
			<?php
			
			$conec= new ADO();
		
			if($valor<>"")
			{
				$sql="select int_id,int_nombre,int_apellido from interno where int_nombre like '%$valor%' or int_apellido like '%$valor%'";
			}
			else
			{
				$sql="select int_id,int_nombre,int_apellido from interno";
			}
			
			$conec->ejecutar($sql);
			
			$num=$conec->get_num_registros();
			
			echo '<table class="tablaLista" cellpadding="0" cellspacing="0">
					<thead>
					<tr>
						<th>
							Nombre
						</th>
						<th>
							Apellido
						</th>
						<th width="80" class="tOpciones">
							Seleccionar
						</th>
				</tr>
				</thead>
				<tbody>
			';
			
			for($i=0;$i<$num;$i++)
			{
				$objeto=$conec->get_objeto();
				
				echo '<tr>
						 <td>'.$objeto->int_nombre.'</td>
						 <td>'.$objeto->int_apellido.'</td>
						 <td><a href="javascript:poner('."'".$objeto->int_id."'".','."'".$objeto->int_nombre.' '.$objeto->int_apellido."'".');"><center><img src="images/select.png" border="0" width="20px" height="20px"></center></a></td>
					   </tr>	 
				';
				
				$conec->siguiente();
			}
			
			?>
			</tbody></table>
			<?php
	}
	
	function insertar_tcp()
	{
				
		$conec= new ADO();
		
		$sql=" select max(gal_orden) as ultimo from galeriados ";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$orden=$objeto->ultimo + 1;
				
		$sql="insert into galeriados(gal_titulo,gal_orden,gal_estado) values 
							('".$_POST['gal_titulo']."','$orden','".$_POST['gal_estado']."')";
		
		
		
		$conec->ejecutar($sql);

		$mensaje='Galeria Agregada Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		
	}
	
	function modificar_tcp()
	{
		$conec= new ADO();	
		
			$sql="update galeriados set 
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
	
	function dibujar_galeria()
	{	
		
		$conec=new ADO();

		$sql="SELECT gal_id 
		FROM 
		galeriados
		where gal_titulo='".$this->usu->get_usu_per_id()."'";

		$conec->ejecutar($sql);
		
		$objetos=$conec->get_objeto();
		
		$galeria_id = $objetos->gal_id;
		
		?>
			<link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
			
			<script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
			<script>
			$(document).ready(function() {
				$("a.linkImg").fancybox({
					'hideOnContentClick': true,
					'overlayOpacity':0.5,
					'transitionIn'	:	'elastic',
					'transitionOut'	:	'elastic',
					'speedIn'		:	600, 
					'speedOut'		:	200, 
					'overlayShow'	:	false

				});
			});
			</script>
	
		<div style="float:left; width:100%">
		<?php
		
			$sql="SELECT * 
			FROM 
			galeria_fotodos
			where gfo_gal_id='".$galeria_id."'
			order by gfo_id desc ";


			$conec->ejecutar($sql);
			
			$num=$conec->get_num_registros();
			
			for($i=0;$i<$num;$i++)
			{
				
				$objeto=$conec->get_objeto();
				
						?>	
							<div class="clienList">
								<a href="imagenes/foto/<?php echo $objeto->gfo_archivo;?>" class="linkImg">
									<img src="imagenes/foto/chica/<?php echo $objeto->gfo_archivo; ?>" border="0" class="imgb5">
								</a>
							</div>
						<?php		
						
				$conec->siguiente();
			}
			?>
		</div>
	<?php
		
	}
}
?>