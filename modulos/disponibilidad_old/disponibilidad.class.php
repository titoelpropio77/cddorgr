<?php

class DISPONIBILIDAD extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	
	function DISPONIBILIDAD()
	{
		$this->coneccion= new ADO();
		
		$this->link='gestor.php';
		
		$this->modulo='disponibilidad';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('PLANOS Y DISPONIBILIDAD');
	}
	
	function dibujar_busqueda()
	{
			$this->formulario();
	}
	
	function formulario()
	{
		$this->formulario->dibujar_cabecera();
		?>

		<div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=disponibilidad" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
					<div class="Subtitulo">Filtro del Reporte</div>
					<div id="ContenedorSeleccion">
						<div id="ContenedorDiv">
						   <div class="Etiqueta" ><span class="flechas1">*</span>Urbanización</div>
						   <div id="CajaInput">
								<select style="width:200px;" name="urb_id" id="urb_id" class="caja_texto">
									   <option value="">Seleccione</option>
									   <?php 		
										$fun=NEW FUNCIONES;		
										$fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion",$_POST['ven_urb_id']);				
										?>
							   </select>
						   </div>
						</div>	
					</div>
					
					<div id="ContenedorDiv">
					   <div id="CajaBotones">
							<center>
								<input type="hidden" class="boton" name="oculto" value="ok">
								<input type="button" class="boton" name="" onclick="javascript:enviar_formulario();" value="Abrir en contenido"> 
								<input type="button" class="boton" name="" onclick="javascript:plano_nueva_ventana();" value="Abrir en nueva ventana" style="width: auto;">
							</center>
					   </div>
					</div>
				</div>
			</form>	
		</div>
		
		<script type="text/javascript"> 
			function enviar_formulario()
			{
				$urbaniza = $("#urb_id").val();
				if($urbaniza==""){
					$.prompt('Para Mostar el Mapa debe seleccionar una Urbanización',{ opacity: 0.8 });
				} else {
					document.frm_sentencia.submit();
				}
			}
			function plano_nueva_ventana()
			{
				$urbaniza = $("#urb_id").val();
				if($urbaniza==""){
					$.prompt('Para Mostar el Mapa debe seleccionar una Urbanización',{ opacity: 0.8 });
				} else {
					window.open('modulos/uv/index.php?id='+$urbaniza, '_blank');
				}
			} 
		</script>
         <?php
	}
	
	function mostrar_reporte()
	{		
		$_SESSION['cliente'] = 'privado';  
		?>
		<script type="text/javascript" src="swfobject.js"></script>
		<div id="flashcontent">
		</div>
		<script type="text/javascript">
		   var so = new SWFObject("mapa_print.swf", "mymovie", "100%", "100%", "8", "#FFFFFF");
		   so.addParam("flashVars", "url=urvanizacion.xml.php&id=<?php echo $_POST['urb_id']; ?>");
		   so.addParam("play", "true");
		   so.addParam("allowFullScreen", "true");
		   so.write("flashcontent");
		   
		   $(document).ready(function(){
		   
		   });
		</script>
		<?php
	}
	
	
}
?>