<?php
class PLAN extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	
	function PLAN()
	{
		//permisos
		$this->ele_id=143;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}

		$this->coneccion= new ADO();	
		$this->link='gestor.php';
		
		$this->modulo='plan';
		$this->formulario = new FORMULARIO();
		$this->formulario->set_titulo('FECHAS DE PAGO');
		
		$this->usu=new USUARIO;
	}
	function cuentas()	{				$this->formulario->dibujar_cabecera();
		$this->dibujar_listado();			
	}				function dibujar_listado()	{				?>			<script type="text/javascript" src="js/ajax.js"></script>				<script type="text/javascript">							function cargar_fechas(id)				{					var	valores="tarea=fechas&deuda="+id+"&man="+'<?echo $this->usu->get_usu_per_id()?>';								ejecutar_ajax('ajax.php','plan_de_pagoss',valores,'POST');													}												</script>						<div id="Contenedor_NuevaSentencia">			<form id="frm_sentencia" name="frm_sentencia" method="POST" enctype="multipart/form-data">  				<div id="FormSent" style="width:820px;">						<div id="ContenedorSeleccion">													<div id="ContenedorDiv">								<div class="Etiqueta" ><span class="flechas1">*</span>Lotes Comprados</div>																	<div id="CajaInput">										<select style="width:400px;" name="ver_fechas" class="caja_texto" onchange="cargar_fechas(this.value);">											   <option>Seleccione</option>											   <?php 																										$conec= new ADO();																								$sql = "SELECT ind_tabla_id, ind_concepto																FROM 																interno_deuda 																																where ind_int_id='".$this->usu->get_usu_per_id()."'																																group by ind_tabla_id desc";																																								$conec->ejecutar($sql);												$num=$conec->get_num_registros();												for($i=0;$i<$num;$i++)												{														$objeto=$conec->get_objeto();																									?>																										<option value="<? echo $objeto->ind_tabla_id?>"><? $cad = explode("-",$objeto->ind_concepto); echo trim($cad[1].$cad[2].$cad[3].$cad[4].$cad[5]);?></option>																								<?																										$conec->siguiente();																									}																								?>									   </select>								   </div>														</div>													</div>										</div>									</form>												<div id="ContenedorDiv">					<div id="contenido_reporte">					<div id="plan_de_pagoss">											</div>					</div>				</div>			<?			}		}
?>