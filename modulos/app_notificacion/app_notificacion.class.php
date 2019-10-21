<?php

class NOTIFICACION extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $appID = '55e519c71779591b4b8b4567';
	var $appSecret = 'ddcf419e9e8ea1bffd600338bb4e113a';
	
	function NOTIFICACION() 
	{
		//permisos
		$this->ele_id=195;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["Titulo"]="not_titulo";
		$this->arreglo_campos[0]["app_notificacion"]="Titulo";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;
		
		$this->link='gestor.php';
		
		$this->modulo='app_notificacion';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('NOTIFICACION');
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
			$this->arreglo_opciones[$nun]["Titulo"]='VER';
			$nun++;
		}
		
		if($this->verificar_permisos('MODIFICAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='MODIFICAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_edit.png';
			$this->arreglo_opciones[$nun]["Titulo"]='MODIFICAR';
			$nun++;
		}
		
		if($this->verificar_permisos('NOTIFICACION PUSH'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='NOTIFICACION PUSH';
			$this->arreglo_opciones[$nun]["imagen"]='imagenes/notificacion.png'; 
			$this->arreglo_opciones[$nun]["Titulo"]='NOTIFICACION PUSH';
			$nun++;
		}
		if($this->verificar_permisos('ELIMINAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='ELIMINAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_drop.png';
			$this->arreglo_opciones[$nun]["Titulo"]='ELIMINAR';
			$nun++;
		}
	}
	
	function dibujar_listado()
	{
		$sql="SELECT * FROM app_notificacion "; 
		
		$this->set_sql($sql, " ORDER BY not_id DESC");
		
		$this->set_opciones();
		
		$this->dibujar();	
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Nro.</th>
				<th>Fecha</th> 
				<th>Mensaje</th>
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
					echo ($i+1);
				echo "&nbsp;</td>";
				echo "<td>";
					echo $conversor->get_fecha_latina($objeto->not_fecha);
				echo "&nbsp;</td>";
				echo "<td>";
					echo $objeto->not_descripcion; 
				echo "&nbsp;</td>";
				echo "<td>";
					echo $objeto->not_estado;
				echo "&nbsp;</td>";
				echo "<td>";
					echo $this->get_opciones($objeto->not_id);
				echo "</td>";
			echo "</tr>";
			$this->coneccion->siguiente();
		}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from app_notificacion
				where not_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['not_descripcion']=$objeto->not_descripcion; 
		
		$_POST['not_fecha']=$objeto->not_fecha; 
		
		$_POST['not_estado']=$objeto->not_estado; 
	}
	
	function datos()
	{
		if($_POST)
		{
			//app_notificacion,  numero,  real,  fecha,  mail.
			$num=0; 			
			$valores[$num]["etiqueta"]="Descripción";
			$valores[$num]["valor"]=$_POST['not_descripcion'];
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

		
		    $this->formulario->dibujar_tarea('app_notificacion');
		
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
								<textarea name="not_descripcion" id="not_descripcion" rows="3" cols="40" maxlength="107"><?php echo $_POST['not_descripcion']; ?></textarea>
								<p class="help-block"> 
									<small class="text-muted">
										Cantidad: <b id="tiLargoEscrito"></b>/<b id="tiLargoTotal"></b><br/>
									</small>
								</p>
								<small>La descripcion del mensaje debe ser corto y claro.</small>
								<script>
									$(document).ready(function () {
										$('#tiLargoEscrito').html($("#not_descripcion").text().length);
										$("#tiLargoTotal").text($("#not_descripcion").attr('maxlength'));
										$('#not_descripcion').keyup(function () {
											var valor = $(this).val();
											$('#tiLargoEscrito').text(valor.length);
										});
									});
								</script>  
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
		$sql="insert into app_notificacion (not_descripcion,not_fecha,not_estado,not_usu_id) values 
		('".strip_tags($_POST['not_descripcion'])."','".date("Y-m-d")."','Pendiente','".$_SESSION['id']."')";
		$conec->ejecutar($sql);
		$mensaje='Notificacion Agregado Correctamente!!!';
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
	}
	
	function notificacion_preguntar() {
		$this->formulario->dibujar_cabecera();
		?>
		<br />
		<div class="ancho100">
			<div class="msInformacion limpiar">Estas seguro de enviar la notificacion?</div>
		</div>
		<br>
		<center style="clear:both; float:none;">
			<form id="form_eliminacion" name="form_eliminacion" action="" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="not_id" value="<?php echo $_GET['id'];?>">
				<input type="hidden" name="confirmar" value="ok"> 
				<input type="submit" value="Si" class="boton"> 
				<input type="button" value="Cancelar" class="boton" onclick="window.location='gestor.php?mod=app_notificacion&tarea=ACCEDER';">
			</form>
		</center>
		<?php
	}
	
	function notificacion_enviar(){ 		
		$conec= new ADO();
		$sql="select * from app_notificacion where not_estado='Pendiente' and not_id='".$_POST["not_id"]."'";
		$conec->ejecutar($sql);
		$num = $conec->get_num_registros();
		if($num >0){ 
			$objeto = $conec->get_objeto();
			
			require_once('PushBots.class.php');
			$pb = new PushBots();
			
			$pb->App($this->appID, $this->appSecret);
			
			// Notification Settings
			$pb->Alert(utf8_encode($objeto->not_descripcion)); 
			$pb->Platform(array("0","1"));
			$pb->Badge("0"); 
			
			// Push it !
			$pb->Push();
			
			$sql = "update app_notificacion set not_estado='Enviado' where not_id='" . $_POST['not_id'] . "'";
			$conec->ejecutar($sql);
		
			$mensaje='Notificacion enviada correctamente!!!';
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		} else {
			$mensaje='La notificaci&oacute;n ha sido enviada anteriormente';
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
		}
	}
	
	function formulario_confirmar_eliminacion() 
	{
		$mensaje='Esta seguro de eliminar el app_notificacion?';
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo".'&tarea=ELIMINAR','not_id');
	}
	
	function eliminar_tcp()
	{
		$conec= new ADO();
		
		$sql="delete from app_notificacion where not_id='".$_POST['not_id']."'";
		
		$conec->ejecutar($sql);
		
		$mensaje='Notificacion Eliminado Correctamente!!!';
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
	}
}
?>