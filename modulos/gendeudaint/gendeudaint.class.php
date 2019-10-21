<?php

class GENDEUDAINT extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	
	function GENDEUDAINT()
	{
		//permisos
		$this->ele_id=114;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="ind_concepto";
		$this->arreglo_campos[0]["texto"]="Concepto";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=40;
		
		$this->arreglo_campos[1]["nombre"]="int_numero";
		$this->arreglo_campos[1]["texto"]="Interno";
		$this->arreglo_campos[1]["tipo"]="cadena";
		$this->arreglo_campos[1]["tamanio"]=10;
		
		
		$this->link='gestor.php';
		
		$this->modulo='gendeudaint';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('GENERAR DEUDA');
		
		$this->usu=new USUARIO;
		
		
	}
		
	function dibujar_busqueda()
	{
		?>
		<script>		
		function ejecutar_script(id,tarea){
				var txt = 'Esta seguro de anular la Deuda de la Persona?';
				
				$.prompt(txt,{ 
					buttons:{Anular:true, Cancelar:false},
					callback: function(v,m,f){
						
						if(v){
								location.href='gestor.php?mod=gendeudaint&tarea='+tarea+'&id='+id;
						}
												
					}
				});
			}

		</script>
		<?php
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
		
		if($this->verificar_permisos('ANULAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='ANULAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/no.png';
			$this->arreglo_opciones[$nun]["nombre"]='ANULAR';
			$this->arreglo_opciones[$nun]["script"]='ok';
			$nun++;
		}
		
	
	}
	
	function dibujar_listado()
	{
		$sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,int_nombre,int_apellido,cue_descripcion,cco_descripcion,ind_fecha_pago,ind_fecha_programada 
		FROM 
		interno_deuda inner join interno on (ind_int_id=int_id)
		inner join cuenta on (ind_cue_id=cue_id)
		inner join centrocosto on (ind_cco_id=cco_id)
		";
		//echo $sql;
		$this->set_sql($sql,' order by ind_id desc');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Persona</th>
				
				<th>Observación</th>
				<th>Monto</th>
				<th>Moneda</th>
				<th>F Generada</th>
				<th>F Programada</th>
				<th>F Pagada</th>
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
						echo $objeto->int_nombre.' '.$objeto->int_apellido;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->ind_concepto;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->ind_monto;
					echo "&nbsp;</td>";
					
					echo "<td>";
						if($objeto->ind_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->ind_fecha);
					echo "&nbsp;</td>";
					
					echo "<td>";
						if($objeto->ind_fecha_programada <> '0000-00-00') echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);
					echo "&nbsp;</td>";
					
					echo "<td>";
						if($objeto->ind_fecha_pago <> '0000-00-00') echo $conversor->get_fecha_latina($objeto->ind_fecha_pago);
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->ind_estado;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $this->get_opciones($objeto->ind_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function datos()
	{
		if($_POST)
		{
			return true;
		}
		else
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

		
		    $this->formulario->dibujar_tarea();
		
			if($this->mensaje<>"")
			{
				$this->formulario->dibujar_mensaje($this->mensaje);
			}
			?>
			<script>
			function enviar_persona(frm,n)
			{
			  
			   var persona = document.frm_persona.interno.options[document.frm_persona.interno.selectedIndex].value;
			   var cco_id = document.frm_persona.cco_id.options[document.frm_persona.cco_id.selectedIndex].value;
			   var cuenta = document.frm_persona.cuenta.options[document.frm_persona.cuenta.selectedIndex].value;
			   var ind_concepto = document.frm_persona.ind_concepto.value;
			   var monto = document.frm_persona.monto.value;
			   var moneda = document.frm_persona.moneda.options[document.frm_persona.moneda.selectedIndex].value;
			   
			   
			   if (persona!="" && cco_id!="" && cuenta!="" && ind_concepto!="" && monto!="" && moneda!="")
			   {			      
				  frm.submit();				  
			   }
			   else
				 $.prompt('Los campos marcado con (*) son requeridos',{ opacity: 0.8 });
						  
			}
			</script>
		<div id="Contenedor_NuevaSentencia">
			<form id="frm_persona" name="frm_persona" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">A una persona</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
							   <div id="CajaInput">
							   <select name="interno" class="caja_texto">
							   <option value="">Seleccione</option>
							   <?php 		
								$fun=NEW FUNCIONES;		
								$fun->combo("select int_id as id,CONCAT(int_apellido,' ',int_nombre) as nombre from interno  order by int_apellido,int_nombre asc",$_POST['interno']);				
								?>
							   </select>
							   </div>
							</div>
							<!--Inicio-->
							<div id="ContenedorDiv">
						   <div class="Etiqueta" ><span class="flechas1">*</span>Centro Costo</div>
						   <div id="CajaInput">
							   <select name="cco_id" class="caja_texto">
							   <option value="">Seleccione</option>
							   <?php 		
								$fun=NEW FUNCIONES;		
								$fun->combo("select cco_id as id,cco_descripcion as nombre from centrocosto where cco_padre_id = 0 order by cco_descripcion asc",$_POST['cco_id']);		
								?>
							   </select>
							</div>
							</div>
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Cuenta</div>
							   <div id="CajaInput">
							   <select name="cuenta" class="caja_texto">
							   <option value="">Seleccione</option>
							   <?php 		
								$fun=NEW FUNCIONES;		
								$fun->combo("select cue_id as id, cue_descripcion  as nombre  from cuenta where (cue_tcu_id = 1 or cue_tcu_id=3 ) and cue_nivel = 3 order by cue_descripcion asc ",$_POST['cuenta'] );				
								?>
							   </select>
							   </div>
							</div>
							<!--Fin-->
							<!--Fin-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Observación</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="ind_concepto" id="ind_concepto" size="40" maxlength="250" value="<?php echo $_POST['ind_concepto'];?>">
							   </div>
							</div>
							<!--Fin-->
							
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Monto</div>	  
								<div id="CajaInput">
								 <input type="text" name="monto" id="monto" size="5" value="<?php echo $_POST['monto']?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Moneda</div>
							   <div id="CajaInput">
						
									    <select name="moneda" class="caja_texto">
										<option value="" >Seleccione</option>
										<option value="1" <?php if($_POST['moneda']=='1') echo 'selected="selected"'; ?>>Bolivianos</option>
										<option value="2" <?php if($_POST['moneda']=='2') echo 'selected="selected"'; ?>>Dolares</option>
										</select>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Fecha de Pago</div>
								 <div id="CajaInput">
									<input readonly="readonly" class="caja_texto" name="ind_fecha_programada" id="ind_fecha_programada" size="12" value="<?php echo $_POST['ind_fecha_programada'];?>" type="text">
										<input name="but_fecha_programada" id="but_fecha_programada" class="boton_fecha" value="..." type="button">
										<script type="text/javascript">
														Calendar.setup({inputField     : "ind_fecha_programada"
																		,ifFormat     :     "%Y-%m-%d",
																		button     :    "but_fecha_programada"
																		});
										</script>
								</div>		
							</div>
							<!--Fin-->
							
						</div>
					
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
								<input type="hidden" class="boton" name="persona" value="ok">
								<input type="button" class="boton" name="" value="Generar Deuda" onClick="enviar_persona(this.form);">
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
		
		if($_POST['persona']=="ok")
		{
			$sql="insert into interno_deuda(ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_cue_id,ind_cco_id,ind_fecha_programada)
			values('".$_POST['interno']."','".$_POST['monto']."','".$_POST['moneda']."','".$_POST['ind_concepto']."','".date('Y-m-d')."','".$this->usu->get_id()."','".$_POST['cuenta']."','".$_POST['cco_id']."','".$_POST['ind_fecha_programada']."')
			";
			//echo $sql;
			$conec->ejecutar($sql);	
		}
		
		$mensaje='Deuda Generada Correctamente!!!';
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	
	function anular()
	{
	
		$conec= new ADO();
		
		$sql = "select ind_estado from interno_deuda where ind_id='".$_GET['id']."'"; 
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		if($objeto->ind_estado == 'Pendiente')
		{
			$sql="update interno_deuda set 
							ind_estado='Anulado'
							where ind_id = '".$_GET['id']."'";
							
			$conec->ejecutar($sql);
			
			$mensaje='Deuda Anulada Correctamente!!!';
		}
		else
		{
			$mensaje='La deuda no puede ser anulada, por que ya fue pagada o anulada anteriormente!!!';
		}

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
}
?>