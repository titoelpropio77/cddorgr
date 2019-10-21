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
		
                $this->usu = new USUARIO();
                
		$this->formulario->set_titulo('PLANOS Y DISPONIBILIDAD');
	}
	
	function dibujar_busqueda()
	{
			$this->formulario();
	}
	
	function formulario()
	{
		if($_POST['oculto']=='ok')
		{
			$this->mostrar_reporte();
		}
		else
		{
		//$this->formulario->dibujar_cabecera();
		$this->formulario->dibujar_cabecera();
		?>
        <script>
        function enviar_formulario()
		{
			var urbanizacion=document.frm_sentencia.urb_id.options[document.frm_sentencia.urb_id.selectedIndex].value;
			
			if(urbanizacion!='')
			{
				document.frm_sentencia.submit();
			}
			else
			{
				$.prompt('Para Mostar el Mapa debe seleccionar la Urbanización',{ opacity: 0.8 });			   
			}
		}
		</script>
		<div id="Contenedor_NuevaSentencia">
						<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=disponibilidad" method="POST" enctype="multipart/form-data">  
									<div id="FormSent">
										<div class="Subtitulo">Filtro del Reporte</div>
					                    <div id="ContenedorSeleccion">
										<!--Inicio-->
										<div id="ContenedorDiv">
										   <div class="Etiqueta" ><span class="flechas1">*</span>Urbanización</div>
										   <div id="CajaInput">
												<select style="width:200px;" name="urb_id" id="urb_id" class="caja_texto">
													   <option value="">Seleccione</option>
													   <?php 		
														$fun=NEW FUNCIONES;		
														$fun->combo("SELECT urb_id AS id,urb_nombre AS nombre FROM urbanizacion"); 			
														?>
											   </select>
										   </div>
										</div>
										<!--Fin-->
												
										</div>
										
										<div id="ContenedorDiv">
					                           <div id="CajaBotones">
													<center>
													<input type="hidden" class="boton" name="oculto" value="ok">
													<input type="button" class="boton" name="" onclick="javascript:enviar_formulario();" value="Aceptar">
													</center>
											   </div>
					                    </div>
									</div>
						</form>	
						<div>	
        
         <?php
		 }
		//$this->mostrar_reporte();
	}
	
	function mostrar_reporte()
	{		
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