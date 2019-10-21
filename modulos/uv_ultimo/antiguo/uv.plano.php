<?php
class Plano
{
	var $formulario;
	var $mensaje;
	var $modulo = "uv";
	
	var $path_base = "";
	var $path_base_admin = ""; 
	function Plano()
	{
		// verificar el mapa si es publico o privado 
		if ($_GET['mapa']=="puplico") {
			$this->path_base = ""; 
			$this->path_base_admin = "../../";
		} else {
			$this->path_base = "modulos/uv/";
			$this->path_base_admin = "";
			
		}
		
		require_once($this->path_base_admin."clases/formulario.class.php");
		$this->formulario = new FORMULARIO();
		
		// Crear conexxion a la base de datos
		require_once($this->path_base_admin.'config/database.conf.php');
		mysql_connect(_SERVIDOR_BASE_DE_DATOS,_USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS) or
												die("Could not connect: " . mysql_error());
		mysql_select_db(_BASE_DE_DATOS);
		
	}
	
	function plano_configuracion()
	{
		$conec = new ADO();
		$sql="SELECT * FROM urbanizacion_plano_config WHERE pla_urb_id='".$_GET['id']."'";
		$conec->ejecutar($sql);
		$num = $conec->get_num_registros();
		if($num > 0) {
			$objetoDatos = $conec->get_objeto();
			$formAcion = "ACTUALIZAR";
		} else {
			$formAcion = "CREAR";
		}
		?>
			<center>
            	<div class="fila_formulario_cabecera"> MODIFICAR DISPONIBILIDAD</div>
				<div id="Contenedor_NuevaSentencia">
					<form id="frm_sentencia" name="frm_sentencia" action="" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="pla_urb_id" value="<?php echo $_GET['id']; ?>"> 
						<input type="hidden" name="formAcion" value="<?php echo $formAcion; ?>"> 
						<div id="FormSent">
							<div class="Subtitulo">Configuraci&oacute;nes</div>
								<div id="ContenedorSeleccion"> 
									<fieldset style="overflow:hidden;">
										<legend class="prueba puntero" style="cursor: pointer;"><b>Administrador</b></legend>
										<div id="ContenedorDiv">
										   <div class="Etiqueta">Imagen de Plano</div>
										   <div id="CajaInput"> 
												<img src="imagenes/uv/<?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_imagen; } ?>" border="0" width="80">
												<input   name="pla_imagen" type="file" id="pla_imagen" /> <?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_imagen; } ?><br>
												<span class="flechas1">&nbsp;&nbsp;Tamaño maximo (6500 X 6500) Formato JPG o PNG</span><br>
												<small>La imagen debe se cuadrado</small>
										   </div>
										</div>
										
										<div id="ContenedorDiv">
										   <div class="Etiqueta">Imagen de Plano Navegador</div> 
										   <div id="CajaInput">
												<img src="imagenes/uv/<?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_navegador_imagen; } ?>" border="0" width="80">
												<input name="pla_navegador_imagen" type="file" id="pla_navegador_imagen" /> <?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_navegador_imagen; } ?><br>
												<span class="flechas1">&nbsp;&nbsp;Tamaño Ideal (230 X 230) Formato JPG o PNG</span><br>
												<small>La imagen de navegador bebe ser proporcional a la imagen del plano</small> 
										   </div>
										</div>
							
										<div id="ContenedorDiv">
										   <div class="Etiqueta" ><span class="flechas1">* </span>Imagen de Plano Medidas</div>
										   <div id="CajaInput">
												<input type="text" placeholder="width" class="caja_texto" name="pla_plano_width" id="pla_plano_width" maxlength="250"  size="30" value="<?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_plano_width; } ?>"> Por 
												<input type="text" placeholder="height" class="caja_texto" name="pla_plano_height" id="pla_plano_height" maxlength="250"  size="30" value="<?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_plano_height; } ?>"> Pixel
										   </div>
										</div>
										
										<div id="ContenedorDiv">
										   <div class="Etiqueta" ><span class="flechas1">* </span>Puntos a mostrar</div>
										   <div id="CajaInput">
												<input type="text" placeholder="width" class="caja_texto" name="pla_punto_width" id="pla_punto_width" maxlength="250"  size="30" value="<?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_punto_width; } ?>"> Por 
												<input type="text" placeholder="height" class="caja_texto" name="pla_punto_height" id="pla_punto_height" maxlength="250"  size="30" value="<?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_punto_height; } ?>"> Pixel
										   </div>
										</div>
									</fieldset>
									
									<fieldset style="overflow:hidden;">
										<legend class="prueba puntero" style="cursor: pointer;"><b>Vista P&uacute;blico</b></legend> 
										   <div id="ContenedorDiv">
											   <div class="Etiqueta" ><span class="flechas1">* </span>Mostrar Navegador</div>
											   <div id="CajaInput">
													<select name="pla_navegador_mostrar" class="caja_texto"> 
														<?php
															if($formAcion=="ACTUALIZAR"){
														?>
															<option value="yes" <?php if($objetoDatos->pla_navegador_mostrar=='yes'){ echo 'selected="selected"'; }?>>Si</option>
															<option value="no" <?php if($objetoDatos->pla_navegador_mostrar=='no'){ echo 'selected="selected"'; } ?>>No</option>
														<?php } else { ?>
															<option value="yes">Si</option>
															<option value="no">No</option>
														<?php } ?>
													</select>
											   </div>
											</div>
											
											<div id="ContenedorDiv">
											   <div class="Etiqueta" ><span class="flechas1">* </span>Pantalla Opci&oacute;n de  Completo</div>
											   <div id="CajaInput">
													<select name="pla_pantalla_completo" class="caja_texto">
														<?php
															if($formAcion=="ACTUALIZAR"){
														?>
															<option value="fullscreen" <?php if($objetoDatos->pla_pantalla_completo=='fullscreen'){ echo 'selected="selected"'; } ?>>Si</option>
															<option value="no" <?php if($objetoDatos->pla_pantalla_completo=='no'){ echo 'selected="selected"'; } ?>>No</option> 
														<?php } else { ?>
															<option value="fullscreen">Si</option>
															<option value="no">No</option> 
														<?php } ?>
													</select>
											   </div>
											</div>
											
											<div id="ContenedorDiv">
											   <div class="Etiqueta" ><span class="flechas1">* </span>Mostrar puntos <br>(segun el % de zoom)</div>
											   <div id="CajaInput">
													<select name="pla_zoom_factor" class="caja_texto">
														<option value="1" <?php if($objetoDatos->pla_zoom_factor=='1'){ echo 'selected="selected"'; }?>>1</option>
														<option value="0.05" <?php if($objetoDatos->pla_zoom_factor=='0.05'){ echo 'selected="selected"'; }?>>05</option>
														<option value="0.10" <?php if($objetoDatos->pla_zoom_factor=='0.10'){ echo 'selected="selected"'; }?>>10</option>
														<option value="0.15" <?php if($objetoDatos->pla_zoom_factor=='0.15'){ echo 'selected="selected"'; } ?>>15</option>
														<option value="0.20" <?php if($objetoDatos->pla_zoom_factor=='0.20'){ echo 'selected="selected"'; } ?>>20</option>
														<option value="0.25" <?php if($objetoDatos->pla_zoom_factor=='0.25'){ echo 'selected="selected"'; } ?>>25</option>
														<option value="0.30" <?php if($objetoDatos->pla_zoom_factor=='0.30'){ echo 'selected="selected"'; } ?>>30</option>
														<option value="0.35" <?php if($objetoDatos->pla_zoom_factor=='0.35'){ echo 'selected="selected"'; } ?>>35</option>
														<option value="0.40" <?php if($objetoDatos->pla_zoom_factor=='0.40'){ echo 'selected="selected"'; } ?>>40</option>
														<option value="0.45" <?php if($objetoDatos->pla_zoom_factor=='0.45'){ echo 'selected="selected"'; } ?>>45</option>
														<option value="0.50" <?php if($objetoDatos->pla_zoom_factor=='0.50'){ echo 'selected="selected"'; } ?>>50</option>
														<option value="0.55" <?php if($objetoDatos->pla_zoom_factor=='0.55'){ echo 'selected="selected"'; } ?>>55</option>
														<option value="0.60" <?php if($objetoDatos->pla_zoom_factor=='0.60'){ echo 'selected="selected"'; } ?>>60</option>
														<option value="0.65" <?php if($objetoDatos->pla_zoom_factor=='0.65'){ echo 'selected="selected"'; } ?>>65</option>
														<option value="0.70" <?php if($objetoDatos->pla_zoom_factor=='0.70'){ echo 'selected="selected"'; } ?>>70</option>
														<option value="0.75" <?php if($objetoDatos->pla_zoom_factor=='0.75'){ echo 'selected="selected"'; } ?>>75</option>
														<option value="0.80" <?php if($objetoDatos->pla_zoom_factor=='0.80'){ echo 'selected="selected"'; } ?>>80</option>
														<option value="0.85" <?php if($objetoDatos->pla_zoom_factor=='0.85'){ echo 'selected="selected"'; } ?>>85</option>
														<option value="0.90" <?php if($objetoDatos->pla_zoom_factor=='0.90'){ echo 'selected="selected"'; } ?>>90</option>
														<option value="0.95" <?php if($objetoDatos->pla_zoom_factor=='0.95'){ echo 'selected="selected"'; } ?>>95</option> 
													</select>
											   </div>
											</div>

									</fieldset>
								</div>
								<div id="ContenedorDiv">
								   <div id="CajaBotones">
										<center>
											<input type="submit" class="boton" name="" value="Guardar">
											<input type="reset" class="boton" name="" value="Cancelar">
											<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='';">
										</center>
								   </div>
								</div>
						</div>
					</form>
				</div>
			</center>	
		<?php
	}
	
	function plano_puntos() {
		$conec = new ADO();
		$sql="SELECT * FROM urbanizacion_plano_config WHERE pla_urb_id='".$_GET['id']."'";
		$conec->ejecutar($sql);
		$num = $conec->get_num_registros();
		if($num > 0) {
			$objetoDatos = $conec->get_objeto();
			$formAcion = "ACTUALIZAR";
		?>
			<link rel="stylesheet" type="text/css"  href="modulos/uv/editor/css/mapa_cordenadas.css"/> 
            <link rel="stylesheet" type="text/css"  href="modulos/uv/editor/css/alertify.css"/>
			<script type="text/javascript" src="modulos/uv/editor/js/jquery-ui.min.js"></script>
			
			<link rel="stylesheet" type="text/css"  href="modulos/uv/editor/js/vTip/css/vtip.css"/> 
			<script type="text/javascript" src="modulos/uv/editor/js/vTip/vtip.js"></script>
			
			
            <script src="modulos/uv/editor/js/plugins/CSSPlugin.min.js"></script> 
            <script src="modulos/uv/editor/js/easing/EasePack.min.js"></script> 
            <script src="modulos/uv/editor/js/TweenLite.min.js"></script>
			
			<script src="modulos/uv/editor/js/alertify.min.js"></script>
            <script type="text/javascript">
				var urb_id = "<?php echo $_GET['id']; ?>";
				var uvArray = '<?php echo $this->uv_lista(); ?>';
				var manzanoArray = '<?php echo $this->uv_manzanos(); ?>';
				var loteArray = '';
				
				var mapaUvActivo = 0; 
				var mapaManzanoActivo = 0;
				
				var lot_coorX = 0;
				var lot_coorY = 0;
				var lot_ID = "";
				var lot_nro = 0;
				var lot_estado = ""; 
				var lot_agregados_array = new Array();
				
				var punto_width = '<?php echo $objetoDatos->pla_punto_width/2; ?>';
				var punto_height = '<?php echo $objetoDatos->pla_punto_height/2; ?>';
				
                function mapa_reset() {
                    var posX = "-<?php echo $objetoDatos->pla_plano_width/2; ?>";
                    var posY = "-<?php echo $objetoDatos->pla_plano_height/2; ?>";
                    TweenLite.to("#mapaBox", 1.5, {left: posX, top: posY, width: "<?php echo $objetoDatos->pla_plano_width; ?>", height: "<?php echo $objetoDatos->pla_plano_height; ?>", onComplete: mapa_reset_completo});
                }
                function mapa_reset_completo() {
                    $("#mapaMarker").show();
                }
                function mapa_ajustar() {
                    $("#mapaMarker").hide();
                    var posX = (($(".mapaScroll").width()) / (2)) / 2;
                    TweenLite.to("#mapaBox", 0.5, {left: posX, top: 0, height: "95%", width: "auto"});
                }
				
				//============= Combo =========//
				function mapa_uv_generar() {
					var mHtml = "";
					var jsondata = JSON.parse(uvArray);
					$.each(jsondata, function(i, val) {
						if(val.uv_id==mapaUvActivo){
							mHtml += '<option value="'+val.uv_id+'" selected="selected">'+val.uv_nombre+'</option>';
						} else {
							mHtml += '<option value="'+val.uv_id+'">'+val.uv_nombre+'</option>';
						}
					});
					return mHtml;
				}
				
				function mapa_manzano_generar() {
					var mHtml = "";
					var jsondata = JSON.parse(manzanoArray);
					$.each(jsondata, function(i, val) {
						if(val.man_id==mapaManzanoActivo){
							mHtml += '<option value="'+val.man_id+'" selected="selected">Manzano Nro: '+val.man_nro+'</option>';
						} else {
							mHtml += '<option value="'+val.man_id+'">Manzano Nro: '+val.man_nro+'</option>';
						}
					});
					return mHtml;
				}
				
				function mapa_lotes_generar() {
					var mHtml = '<option value="">Seleccione</option>';
					if(loteArray !="") {
						var jsondata = JSON.parse(loteArray);
						var mContar = jsondata.length;
						var auxUltimoID = parseFloat(lot_nro) + 1;
						$.each(jsondata, function(i, val) {
						
							// comprobar si ya a sido agregado
							var auxAgredados = "";
							if(val.class==""){
								auxAgredados = mapa_lotes_agregados(val.lot_id);
							} else {
								auxAgredados = val.class;
							}
							// verificar el ultimo que se agrego selected="selected"
							var auxSelected = "";
							if(auxUltimoID == val.lot_nro){
								auxSelected = ' selected="selected" ';
							}
							
							mHtml += '<option data-tes="'+auxUltimoID+'" value="'+val.lot_id+'" class="'+auxAgredados+'" data-estado="'+val.estado+'" '+auxSelected+'>'+val.lot_nro+'</option>';  
						});
					}
					
					
					return mHtml;
				}
				
				function mapa_lotes_agregados(lot_id) {
					var result = "";
					for (var i = 0; i < lot_agregados_array.length; i++) {
						if(lot_agregados_array[i]==lot_id) {
							result = "classOption";
						}
					}
					return result;
				}
				
				function mapa_combobox_change() {
				
					$("select").change(function () {
						if($(this).attr("id")=="uv"){
							mapaUvActivo = $(this).val();
							mapa_cargar();
						}
						if($(this).attr("id")=="manzano"){
							mapaManzanoActivo = $(this).val(); 
							mapa_cargar();
						}
						if($(this).attr("id")=="lote"){
						
						}
					});
                }
				
				function mapa_combobox_lote_select()
				{
					var jsondata = JSON.parse(loteArray);
					$.each(jsondata, function(i, val) {
						if(val.lot_id == lot_ID){
							lot_nro = val.lot_nro;
							lot_estado = val.estado;
						}
					});
					lot_agregados_array.push(lot_ID);
				}
				
				function mapa_cargar() {
					var auxUv = $("#uv").val();
					var auxManzano = $("#manzano").val();
					$("#loteCargando").css({"visibility":"visible"}); 
					$.ajax({
						type: "POST", 
						url: 'modulos/uv/ajax_gestor.php?ajaxTarea=lotes&man_id='+auxManzano+"&uv_id="+auxUv,
						success: function (results) {
							var jsondata = JSON.parse(results);
							var accion = jsondata[0].respuesta.accion.toString();
							if(accion == "correcto"){
								lot_agregados_array = new Array();
								loteArray = JSON.stringify(jsondata[0].respuesta.datos);
								if(jsondata[0].respuesta.datos.length > 0) {
									alertify.success(jsondata[0].respuesta.mensaje.toString()); 
								} else {
									alertify.log("Este manzano no tiene lotes relacionados con esta UV");
								}
								$("#lote").html(mapa_lotes_generar());
								$("#loteCargando").css({"visibility":"hidden"});
							} else {
								alertify.error(jsondata[0].respuesta.mensaje.toString()); 
							}
						}
					});
				}
				
				function mapa_marker_tooltip() { 
					vtip();
				}
				
				//============= Combo fin  =========// //$('#mousefollow-examples div').powerTip({ followMouse: true });
				function mapa_marker_drag() {
					$(".mapaMarkerPuntos").draggable({
                        start: function() {
                        },
                        stop: function() {
							$.ajax({ 
								type: "POST",
								url: 'modulos/uv/ajax_gestor.php?ajaxTarea=actualizar',
								data: "&lot_id="+$(this).attr("data-id")+"&lot_coorX="+$(this).css("left")+"&lot_coorY="+$(this).css("top"),
								success: function (results) {
									var jsondata = JSON.parse(results);
									var accion = jsondata[0].respuesta.accion.toString();
									if(accion == "correcto"){
										alertify.success(jsondata[0].respuesta.mensaje.toString());
									} else {
										alertify.error(jsondata[0].respuesta.mensaje.toString()); 
									}
								}
							});
                        }
                    });
				}
				
				//============= calcular ventana =========//
				function pantalla_actualizar(){
				
					var auxHeadWidth = $(".fila_formulario_cabecera").height();
					var auxHeight = (($(window).height()) -(40)) - auxHeadWidth;
					$("#mapaScroll").height(auxHeight);
				}
				
                $(document).ready(function() {

                    $("#mapaBox").dblclick(function(event)
					{
						var auxCoorX = event.pageX - $(this).offset().left;
						var auxCoorY = event.pageY - $(this).offset().top;
						lot_coorX = auxCoorX - punto_width;
						lot_coorY = auxCoorY - punto_height;
						
						var txt = '<div class="mapaForm">AGREGAR PUNTO<br/>';
							txt += '<label>UV.</label><select id="uv" name="uv">'+mapa_uv_generar()+'</select><br>';
							txt += '<label>Manzano</label><select id="manzano" name="manzano">'+mapa_manzano_generar()+'</select><br>';
							txt += '<label>Lote</label><select id="lote" name="lote">'+mapa_lotes_generar()+'</select><div id="loteCargando" class="mapaLoad">&nbsp;</div><br>';
							txt +="</div>"; 
						$.prompt(txt,{
							 buttons:{Agregar:true, Cancelar:false},
							 callback: function(v,m,f){
							  if(v){
									lot_ID = f.lote; 
									mapa_combobox_lote_select();
									$.ajax({
										type: "POST",
										url: 'modulos/uv/ajax_gestor.php?ajaxTarea=agregar',
										data: "&lot_id="+lot_ID+"&lot_coorX="+lot_coorX+"&lot_coorY="+lot_coorY+"&urb_id="+urb_id,
										success: function (results) {
											var jsondata = JSON.parse(results);
											var accion = jsondata[0].respuesta.accion.toString();
											if(accion == "correcto"){
												$("#mapaMarker").append('<div style="left: '+lot_coorX+'px; top: '+lot_coorY+'px;" data-id="'+lot_ID+'" class="mapaMarkerPuntos mapaEstados'+lot_estado+'">&nbsp;</div>');
												mapa_marker_drag();
												mapa_marker_tooltip();
												alertify.success(jsondata[0].respuesta.mensaje.toString());
											} else {
												alertify.error(jsondata[0].respuesta.mensaje.toString()); 
											} 
										}
									});
									
								}
							 }
						});
						mapa_combobox_change();
                    });
					
                    $("#mapaBox").draggable({
                        scroll: true
                    });
					
                    $(window).resize(function () {
						pantalla_actualizar();
					});
					pantalla_actualizar();

					mapa_marker_drag();
					mapa_marker_tooltip();
                    mapa_ajustar();
                });

            </script> 
			<style>
				.mapaMarkerPuntos{
					height: <?php echo $objetoDatos->pla_punto_width; ?>px;
					width: <?php echo $objetoDatos->pla_punto_height; ?>px;
				}
			</style>
			<div class="mapaConten">
				<div class="mapaOpciones">
					<input type="button" value="Tamaño Original" onclick="mapa_reset();"/>
					<input type="button" value="Ajustar Imagen" onclick="mapa_ajustar();"/>
					<input type="button" value="Actualizar Pantalla" onclick="location.reload();"/>
				</div>
				<div class="mapaScroll" id="mapaScroll">

					<div class="mapaBox" id="mapaBox" style="width: <?php echo $objetoDatos->pla_plano_width; ?>px; height: <?php echo $objetoDatos->pla_plano_height; ?>px;"> 
						<div class="mapaCont"> 
							<img src="imagenes/uv/<?php echo $objetoDatos->pla_imagen; ?>" usemap="#Map" border="0" /> 
							<div class="mapaMarker" id="mapaMarker" data-punto-width="<?php echo $objetoDatos->pla_punto_width; ?>" data-punto-height="<?php echo $objetoDatos->pla_punto_height; ?>">
								<?php
									echo $this->lotes_coordenadas();
								?>
							</div>
						</div>
					</div>

				</div>
			</div>
		<?php
		} else{
		?>
		<div class="fila_formulario_cabecera">URBANIZACION PLANO</div>
		<div class="msAlerta limpiar">
			<h2><b>Antes de continuar primero debes agregar el plano y hacer configuraciones previas</b></h2>
			<p><br>
				<a href="gestor.php?mod=<?php echo $this->modulo; ?>&tarea=CONFIGURACION&id=<?php echo $_GET['id']; ?>">Configurar Ahora</a> 
			</p>
		</div>
		
		<center style="clear:both;">
			<br>
			<table>
				<tbody>
					<tr>
						<td>
							<input type="submit" onClick="window.history.back();" style="clear:both;" class="botongrande" value="Volver Atras">
						</td>
					</tr>
				</tbody>
			</table>
		
		</center>
		<?php 
		}
	}
	
	function plano_ver()
	{
		$sql="SELECT * FROM urbanizacion_plano_config WHERE pla_urb_id='".$_GET['id']."'";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		if($num > 0) {
			$objetoDatos = mysql_fetch_object($result);
			$formAcion = "ACTUALIZAR";
		?>
		<?php if($_GET['mapa']=="puplico") { ?>
		
		<!DOCTYPE html> 
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<meta name="viewport" content="initial-scale=1, maximum-scale=1"/>
			<link rel="shortcut icon" href="http://www.orangegroup.com.bo/img/favicon.png" type="image/png">
			<title>Disponibilidad</title>
		<?php } ?>
			<link rel="stylesheet" type="text/css"  href="<?php echo $this->path_base; ?>view/skin_round_silver/global.css"/>
			<?php if($_GET['mapa']=="puplico") { ?><script type="text/javascript" src="<?php echo $this->path_base_admin; ?>js/jquery-1.3.2.min.js"></script><?php } ?> 
			<script type="text/javascript" src="<?php echo $this->path_base; ?>view/java/FWDMegazoom.min.js"></script>
			<script type="text/javascript"> 
				var megazoom;
				FWDUtils.onReady(function(){
					megazoom =  new FWDMegazoom({
						//----main----//
						parentId:"productHolder",
						playListAndSkinId:"megazoomPlayList",
						displayType:"<?php echo $objetoDatos->pla_pantalla_completo; ?>",
						skinPath:"<?php echo $this->path_base; ?>view/skin_round_silver/skin/",
						imagePath:"<?php echo $this->path_base_admin; ?>imagenes/uv/<?php echo $objetoDatos->pla_imagen; ?>",
						preloaderText:"Cargando mapa...",
						useEntireScreen:"yes",
						addKeyboardSupport:"yes",
						addDoubleClickSupport:"yes",
						imageWidth:<?php echo $objetoDatos->pla_plano_width; ?>, 
						imageHeight:<?php echo $objetoDatos->pla_plano_height; ?>,
						zoomFactor:1.4,
						doubleClickZoomFactor:1,
						startZoomFactor:"default",
						panSpeed:8,
						zoomSpeed:.1,
						backgroundColor:"#FFFFFF",
						preloaderFontColor:"#585858",
						preloaderBackgroundColor:"#FFFFFF",
						//----lightbox-----//
						lightBoxWidth:800,
						lightBoxHeight:550,
						lightBoxBackgroundOpacity:.8,
						lightBoxBackgroundColor:"#000000",
						//----controller----//
						buttons:"moveLeft, moveRight, moveDown, moveUp, scrollbar, hideOrShowMarkers, hideOrShowController, info, fullscreen",
						buttonsToolTips:"Move left, Move right, Move down, Move up, Zoom level: , Hide markers/Show markers, Hide controller/Show controller, Info, Full screen/Normal screen",
						controllerPosition:"bottom",
						inversePanDirection:"yes",
						startSpaceBetweenButtons:10,
						spaceBetweenButtons:10,
						startSpaceForScrollBarButtons:20,
						startSpaceForScrollBar:6,
						hideControllerDelay:3,
						controllerMaxWidth:900,
						controllerBackgroundOpacity:1,
						controllerOffsetY:3,
						scrollBarOffsetX:0,
						scrollBarHandlerToolTipOffsetY:4,
						zoomInAndOutToolTipOffsetY:-4,
						buttonsToolTipOffsetY:0,
						hideControllerOffsetY:2,
						buttonToolTipFontColor:"#585858",
						//----navigator----//
						showNavigator:"<?php echo $objetoDatos->pla_navegador_mostrar; ?>",
						navigatorImagePath:"<?php echo $this->path_base_admin; ?>imagenes/uv/<?php echo $objetoDatos->pla_navegador_imagen; ?>",
						navigatorPosition:"topright",
						navigatorOffsetX:6,
						navigatorOffsetY:6,
						navigatorHandlerColor:"#FF0000",
						navigatorBorderColor:"#FFFFFF",
						//----info window----//
						infoWindowBackgroundOpacity:.6,
						infoWindowBackgroundColor:"#FFFFFF",
						infoWindowScrollBarColor:"#585858",
						//----markers-----//
						showMarkersInfo:"no",
						markerToolTipOffsetY:0,
						markerToolTipOffsetY:0,
						//----context menu----//
						showScriptDeveloper:"no",
						contextMenuLabels:"Mover a la izquierda, Mover a la derecha, Mover hacia abajo, Mover hacia arriba, + Zoom /- Zoom , Ocultar marcadores/Mostrar marcadores, Ocultar controlador/Mostrar controlador, Informacion, Pantalla completa/Pantalla normal",
						contextMenuBackgroundColor:"#d1cfcf",
						contextMenuBorderColor:"#8f8d8d",
						contextMenuSpacerColor:"#acacac",
						contextMenuItemNormalColor:"#585858",
						contextMenuItemSelectedColor:"#FFFFFF",
						contextMenuItemDisabledColor:"#b7b4b4"
					});
				})
			</script>
		</head>
		<body>
			<div id="megazoomPlayList" style="display:none">
				<!-- info window-->
				<ul data-info="">
					<div class="infoDiv">
						<h1 class="largeLabel">INFORMACI&Oacute;N DE URBANIZACI&Oacute;N</h1> 
						<img class="leftImage" src="<?php echo $this->path_base_admin; ?>imagenes/uv/<?php echo $objetoDatos->pla_navegador_imagen; ?>" width="230" height="230">
						<p class="leftImageParagraph"><span class="boldDark">This type of window support unlimited text</span>, if the html content is too large on a mouse enabled device a scrollbar will appear and if the device has touch support the html content can be scrolled with the finger. This window has a responsive layout this means that it will adapt based on the available space (resize the browser window to see this feature in action). <a class="link" href="http://www.google.com" target="_blank">external link</a> amet tincidunt ligula semper. Sed luctus lorem dui, ut lobortis diam. Curabitur est sapien, viverra et aliquet ut, semper nec magna. In molestie, leo a ornare mollis, orci lacus fermentum felis, a scelerisque ante urna tincidunt diam. Ut pharetra est eu neque feugiat molestie. Sed nec laoreet ligula. Nulla cursus sapien ac massa ultrices id placerat massa varius.Nunc ac turpis nulla. Vestibulum placerat metus urna.</p>
						<div class="separator"></div>
					</div>
				</ul>
				
				<!-- marcadores -->
				<ul data-markers="">
					<?php
					
						$sql="SELECT 
						cor_lot_coorX,cor_lot_coorY,lot_nro,lot_superficie,lot_estado,man_nro,zon_nombre 
						FROM 
						lote_cordenada_imagen 
						inner join lote on (cor_lot_id=lot_id)
						inner join manzano on (lot_man_id=man_id)
						inner join zona on (lot_zon_id = zon_id)
						
						WHERE cor_urb_id='".$_GET['id']."'";
						
						
						$result = mysql_query($sql);
						$num = mysql_num_rows($result);
						if($num > 0) {
							for($i=0; $i<$num; $i++)
							{
								$objetos = mysql_fetch_object($result);
								
								$coordX0 = (($objetos->cor_lot_coorX)+($objetoDatos->pla_punto_width/2));
								$coordY0 = (($objetos->cor_lot_coorY)+($objetoDatos->pla_punto_height/5.5));  
								$coordX = $coordX0;
								$coordY = $coordY0; 
								?>
								<li data-marker-type="tooltip" data-show-content="yes" data-reg-point="centertop" data-marker-normal-state-path="<?php echo $this->path_base; ?>editor/img/<?php echo $objetos->lot_estado.'.png';?>" data-marker-selected-state-path="<?php echo $this->path_base; ?>editor/img/<?php echo $objetos->lot_estado.'.png';?>" data-marker-left="<?php echo $coordX; ?>" data-marker-top="<?php echo $coordY; ?>" data-marker-width="<?php echo $objetoDatos->pla_punto_width; ?>" data-marker-height="<?php echo $objetoDatos->pla_punto_height; ?>" data-show-after-zoom-factor="<?php echo $objetoDatos->pla_zoom_factor; ?>">
									<div class="groenlandToolTipInfoDiv">
										<div> 
											<p class="groenland1P">
												<b>Manzano: </b> <?php echo $objetos->man_nro; ?><br/>
												<b>Lote: </b> <?php echo $objetos->lot_nro; ?><br/> 
												<b>Superficie: </b> <?php echo $objetos->lot_superficie; ?>m2.<br/>
												<b>Categoria: </b> <?php echo $objetos->zon_nombre; ?><br/>
												<b>Estado: </b><?php if($objetos->lot_estado=="Bloqueado") { echo "Vendido a Grupo"; } else { echo $objetos->lot_estado; }  ?><br/>
											</p>
										</div>
									</div>
								</li>
								<?php 
							}
						}
					?>
				</ul>
			</div>
		<?php if($_GET['mapa']=="puplico") { ?>
		</body> 
		</html>
		<?php } ?>
		
		<?php
		} else{
		?>
		
		<div class="fila_formulario_cabecera">URBANIZACION PLANO</div>
		<div class="msAlerta limpiar">
			<h2><b>Antes de continuar primero debes agregar el plano y hacer configuraciones previas</b></h2>
			<p><br>
				<a href="gestor.php?mod=<?php echo $this->modulo; ?>&tarea=CONFIGURACION&id=<?php echo $_GET['id']; ?>">Configurar Ahora</a> 
			</p>
		</div>
		
		<center style="clear:both;">
			<br>
			<table>
				<tbody>
					<tr>
						<td>
							<input type="submit" onClick="window.history.back();" style="clear:both;" class="botongrande" value="Volver Atras">
						</td>
					</tr>
				</tbody>
			</table>
		</center>
		<?php
		}
	}
	
	function uv_lista() {
		$datos = "[";
		$conec = new ADO();
		$sql="SELECT * FROM uv WHERE uv_urb_id='".$_GET['id']."' ORDER BY uv_id ASC";
		$conec->ejecutar($sql);
		$num = $conec->get_num_registros();
		for( $i=0; $i<$num;  $i++)
		{
			$objeto = $conec->get_objeto();
			$datos .='{"uv_id": "'.$objeto->uv_id.'","uv_nombre":"'.$objeto->uv_nombre.'"}';
			if($i < $num-1)
			{
				$datos .=',';
			}
			$conec->siguiente();
		}
		return $datos."]"; 
	}
	
	function uv_manzanos() {
		$datos = "[";
		$conec = new ADO(); 
		$sql="SELECT * FROM manzano WHERE man_urb_id='".$_GET['id']."' ORDER BY man_id ASC";
		$conec->ejecutar($sql);
		$num = $conec->get_num_registros();
		for( $i=0; $i<$num;  $i++)
		{
			$objeto = $conec->get_objeto();
			$datos .='{"man_id": "'.$objeto->man_id.'","man_nro":"'.$objeto->man_nro.'"}';
			if($i < $num-1)
			{
				$datos .=',';
			}
			$conec->siguiente();
		}
		return $datos."]"; 
	}
	
	function lotes_coordenadas()
	{
		$datos = "";
		$conec = new ADO();
		$sql="SELECT * FROM uv WHERE uv_urb_id='".$_GET['id']."' ORDER BY uv_id DESC";
		$conec->ejecutar($sql);
		$num = $conec->get_num_registros();
		for( $i=0; $i<$num;  $i++)
		{
			$objeto = $conec->get_objeto();
			$sql="SELECT * FROM lote WHERE lot_uv_id='".$objeto->uv_id."'";
			$conec2 = new ADO();
			$conec2->ejecutar($sql);
			$num2 = $conec2->get_num_registros();
			for( $l=0; $l<$num2;  $l++)
			{
				$objeto2 = $conec2->get_objeto();
				$sql="SELECT * FROM lote_cordenada_imagen WHERE cor_lot_id='".$objeto2->lot_id."'";
				
				$conec3 = new ADO();
				$conec3->ejecutar($sql);
				$num3 = $conec3->get_num_registros();
				if($num3 > 0){
					$objeto3 = $conec3->get_objeto(); 
					$datos .= '<a class="mapaMarkerPuntos mapaEstados'.$objeto2->lot_estado.'" class="tooltips" title="'.$this->lote_info_resumen($objeto3->cor_lot_id).'" data-id="'.$objeto3->cor_lot_id.'" style="left:'.$objeto3->cor_lot_coorX.'px; top:'.$objeto3->cor_lot_coorY.'px; ">&nbsp;</a>'; 
				}
				$conec2->siguiente();	
			}
			$conec->siguiente();
		}
		return $datos;
	}
	
	function plano_configuracion_insertar()
	{
		$conec= new ADO();
		$imgSql = "";
		$imgInsert = "";
		
		if($_FILES['pla_imagen']['name']<>"")
		{
			$result = $this->subir_imagen($nombre_archivo,$_FILES['pla_imagen']['name'], $_FILES['pla_imagen']['tmp_name'], "mapa_");
			$imgSql .= ",pla_imagen";
			$imgInsert .= ",'".$nombre_archivo."'";
		}
		
		if($_FILES['pla_navegador_imagen']['name']<>"")
		{
			$result = $this->subir_imagen($nombre_archivo, $_FILES['pla_navegador_imagen']['name'], $_FILES['pla_navegador_imagen']['tmp_name'], "mapa_navegador_");
			$imgSql .= ",pla_navegador_imagen";
			$imgInsert .= ",'".$nombre_archivo."'";
		}
		
		$sql="insert into urbanizacion_plano_config (
										pla_urb_id,
										pla_navegador_mostrar,
										pla_pantalla_completo,
										pla_plano_width,
										pla_plano_height,
										pla_punto_width,
										pla_punto_height,
										pla_zoom_factor
										".$imgSql."
									) values (
										'".$_POST['pla_urb_id']."',
										'".$_POST['pla_navegador_mostrar']."',
										'".$_POST['pla_pantalla_completo']."',
										'".$_POST['pla_plano_width']."',
										'".$_POST['pla_plano_height']."',
										'".$_POST['pla_punto_width']."', 
										'".$_POST['pla_punto_height']."',
										'".$_POST['pla_zoom_factor']."'
										".$imgInsert."
									)";

		$conec->ejecutar($sql);
		$mensaje='Plano Agregado Correctamente!!!';
		$this->formulario->ventana_volver($mensaje,'gestor.php?mod=uv&tarea=ACCEDER');
	}
	
	function plano_configuracion_modificar()
	{
		$conec= new ADO();	
		$imgSql = "";
		if($_FILES['pla_imagen']['name']<>"")
		{
			$this->eliminar_imagen1($_POST['pla_urb_id']);
			$result = $this->subir_imagen($nombre_archivo,$_FILES['pla_imagen']['name'], $_FILES['pla_imagen']['tmp_name'], "mapa_");
			$imgSql .= ", pla_imagen ='".$nombre_archivo."'";
			if(trim($result)<>'') { 
			
			}
		}
		
		if($_FILES['pla_navegador_imagen']['name']<>"")
		{
			$this->eliminar_imagen2($_POST['pla_urb_id']); 
			$result = $this->subir_imagen($nombre_archivo, $_FILES['pla_navegador_imagen']['name'], $_FILES['pla_navegador_imagen']['tmp_name'], "mapa_navegador_");
			$imgSql .= ", pla_navegador_imagen='".$nombre_archivo."'";
			if(trim($result)<>'') { 
			
			}
		}
		
		$sql="update urbanizacion_plano_config set 
								pla_navegador_mostrar='".$_POST['pla_navegador_mostrar']."',
								pla_pantalla_completo='".$_POST['pla_pantalla_completo']."',
								pla_plano_width='".$_POST['pla_plano_width']."',
								pla_plano_height='".$_POST['pla_plano_height']."',
								pla_punto_width='".$_POST['pla_punto_width']."',
								pla_punto_height='".$_POST['pla_punto_height']."',
								pla_zoom_factor='".$_POST['pla_zoom_factor']."'
								".$imgSql."
								where pla_urb_id = '".$_POST['pla_urb_id']."'"; 
								
		$conec->ejecutar($sql);						
		$mensaje='Plano Modificado Correctamente!!!';
		$this->formulario->ventana_volver($mensaje,'gestor.php?mod=uv&tarea=ACCEDER');
	}
	
	function subir_imagen(&$nombre_imagen,$name,$tmp, $nombre='')
	{	
		 require_once('clases/upload.class.php');
		 $nn=date('d_m_Y_H_i_s_').rand();
		 $upload_class = new Upload_Files(); 
		 $upload_class->temp_file_name = trim($tmp); 	
		 if($nombre !=""){
			$upload_class->file_name = $nombre.$_POST['pla_urb_id'].substr(trim($name), -4, 4); 
		 } else  {
			$upload_class->file_name = $nn.substr(trim($name), -4, 4);
		 }
		 $nombre_imagen=$upload_class->file_name;		 		 
		 $upload_class->upload_dir = "imagenes/uv/"; 
		 $upload_class->upload_log_dir = "imagenes/uv/upload_logs/"; 
		 $upload_class->max_file_size = 1048576; 	// 20480=20MB
		 $upload_class->ext_array = array(".jpg", ".png", "swf");
         $upload_class->crear_thumbnail=false;
		 $valid_ext = $upload_class->validate_extension(); 
		 $valid_size = $upload_class->validate_size(); 
		 $valid_user = $upload_class->validate_user(); 
		 $max_size = $upload_class->get_max_size(); 
		 $file_size = $upload_class->get_file_size(); 
		 $file_exists = $upload_class->existing_file(); 		
		if (!$valid_ext) { 				   
			$result = "La Extension de este Archivo es invalida, Intente nuevamente por favor!"; 
		} 
		elseif (!$valid_size) { 
			$result = "El Tamaño de este archivo es invalido, El maximo tamaño permitido es: $max_size y su archivo pesa: $file_size"; 
		}    
		elseif ($file_exists) { 
			$result = "El Archivo Existe en el Servidor, Intente nuevamente por favor."; 
		} 
		else 
		{		    
			$upload_file = $upload_class->upload_file_with_validation(); 
			if (!$upload_file) { 
				$result = "Su archivo no se subio correctamente al Servidor."; 
			}
			else 
			{ 
				$result = "";
			} 
		} 	
		return $result;	
	}
	
	function eliminar_imagen1($pla_urb_id)
	{
		$conec= new ADO();
		$sql="select * from urbanizacion_plano_config where pla_urb_id='".$pla_urb_id."'";
		$result = mysql_query($sql);
		$objeto =  mysql_fetch_object($result);
		if(trim($objeto->pla_imagen)<>"")
		{
			$mifile="imagenes/uv/$objeto->pla_imagen";
			if (file_exists($mifile)) {
				@unlink($mifile);
			} 
		}
	} 
	
	function eliminar_imagen2($pla_urb_id)
	{
		$sql="select * from urbanizacion_plano_config where pla_urb_id='".$pla_urb_id."'";
		$result = mysql_query($sql);
		$objeto =  mysql_fetch_object($result);
		if(trim($objeto->pla_navegador_imagen)<>"")
		{
			$mifile="imagenes/uv/$objeto->pla_navegador_imagen";
			if (file_exists($mifile)) {
				@unlink($mifile);
			}
		}
	}
	
	function lote_info($lot_id)
	{
		$text='';
		$result = "";
		$sql="
			select 
			lot_nro,lot_superficie,lot_estado,man_nro, zon_nombre
			from lote 
			inner join manzano on (lot_man_id=man_id)
			inner join urbanizacion on (man_urb_id=urb_id)
			inner join zona on (lot_zon_id = zon_id)
			inner join uv on (lot_uv_id = uv_id)
			where lot_id='".$lot_id."'
		";
		
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		$objeto = mysql_fetch_object($result);
		
		$precio = (($objeto->lot_superficie) * ($objeto->zon_precio));
		$solicitante="";
		$sufijo ="";
		if($objeto->zon_moneda == 2)
		{
			$sufijo = '$us';
		} else {
			$sufijo = 'Bs';
		}
		
		$text=$objeto->lot_estado;
		// if($objeto->lot_estado=='Vendido')
		// {
			// $sql2="
			// select 
			// int_nombre,int_apellido
			// from venta 
			// inner join interno on (ven_lot_id='".$lot_id."' and (ven_estado='Pendiente' or ven_estado='Pagado') and ven_int_id=int_id) 
			// ";
			
			// $result2 = mysql_query($sql2);
			// $objeto2 = mysql_fetch_object($result2);
			
			// $solicitante=utf8_encode($objeto2->int_nombre.' '.$objeto2->int_apellido);
		// }
		// else
		// {
			// if($objeto->lot_estado=='Reservado')
			// {
				// $sql3="
				// select 
				// res_vdo_id,int_nombre,int_apellido
				// from reserva_terreno 
				
				// inner join interno on (res_lot_id='".$lot_id."' and (res_estado='Habilitado') and res_int_id=int_id)";
				
				// $result3 = mysql_query($sql3);
				// $objeto3 = mysql_fetch_object($result3);
			
				// $solicitante='Vendedor: '.utf8_encode($this->nombre_persona_vendedor($objeto3->res_vdo_id)).' Cliente: '.utf8_encode($objeto3->int_nombre.' '.$objeto3->int_apellido);
			// }
			// else
			// {
				// if($objeto->lot_estado=='Bloqueado')
				// {
					
					// $solicitante='';
					
				// }
			// }
		// }
		?>
		<b>Manzano: </b> <?php echo $objeto->man_nro; ?><br/>
		<b>Lote: </b> <?php echo $objeto->lot_nro; ?><br/> 
		<b>Superficie: </b> <?php echo $objeto->lot_superficie; ?>m2.<br/>
		<b>Categoria: </b> <?php echo $objeto->zon_nombre; ?><br/>
		<b>Estado: </b><?php if($text=="Bloqueado") { echo "Vendido a Grupo"; } else { echo $text; }  ?><br/>
		<?php
	}
	
	function lote_info_resumen($lot_id)
	{
		$text='';
		$result = "";
		$sql="
			select 
			lot_nro,lot_superficie,lot_estado,man_nro,urb_nombre, zon_precio, uv_nombre, zon_moneda
			from lote 
			inner join manzano on (lot_man_id=man_id)
			inner join urbanizacion on (man_urb_id=urb_id)
			inner join zona on (lot_zon_id = zon_id)
			inner join uv on (lot_uv_id = uv_id)
			where lot_id='".$lot_id."'
		";
		
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		$objeto = mysql_fetch_object($result);
		
		$precio = (($objeto->lot_superficie) * ($objeto->zon_precio));
		$solicitante="";
		$sufijo ="";
		if($objeto->zon_moneda == 2)
		{
			$sufijo = '$us';
		} else {
			$sufijo = 'Bs';
		}
		
		$text=$objeto->lot_estado;
		if($objeto->lot_estado=='Vendido')
		{
			$sql2="
			select 
			int_nombre,int_apellido
			from venta 
			inner join interno on (ven_lot_id='".$lot_id."' and (ven_estado='Pendiente' or ven_estado='Pagado') and ven_int_id=int_id) 
			";
			
			$result2 = mysql_query($sql2);
			$objeto2 = mysql_fetch_object($result2);
			
			$solicitante=utf8_encode($objeto2->int_nombre.' '.$objeto2->int_apellido);
		}
		else
		{
			if($objeto->lot_estado=='Reservado')
			{
				$sql3="
				select 
				res_vdo_id,int_nombre,int_apellido
				from reserva_terreno 
				
				inner join interno on (res_lot_id='".$lot_id."' and (res_estado='Habilitado') and res_int_id=int_id)";
				
				$result3 = mysql_query($sql3);
				$objeto3 = mysql_fetch_object($result3);
			
				$solicitante='Vendedor: '.utf8_encode($this->nombre_persona_vendedor($objeto3->res_vdo_id)).' Cliente: '.utf8_encode($objeto3->int_nombre.' '.$objeto3->int_apellido);
			}
			else
			{
				if($objeto->lot_estado=='Bloqueado')
				{
					
					$solicitante='Terreno Bloqueado';
					
				}
			}
		}
		
		$auxResult = "UV: ".$objeto->uv_nombre." | ";
		$auxResult .= "Manzano: ".$objeto->man_nro." | ";
		$auxResult .= "Lote: ".$objeto->lot_nro." | "; 
		$auxResult .= "ID: ".$lot_id;
		$result =  $auxResult;
		return $result;
	}
	
	function lote_estado($lot_id)
	{
		$sql="select * from lote where lot_id='".$lot_id."'";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		$markerImg = "marker.png";  
		if($num > 0)
		{
			$objeto = mysql_fetch_object($result);
			$markerImg = $objeto->lot_estado.".png";
		}
		return $markerImg;
	}
	
	function nombre_persona_vendedor($vdo_id)
	{
		$sql="SELECT int_nombre,int_apellido FROM interno
		inner join vendedor on (vdo_int_id=int_id) 
		WHERE vdo_id='$vdo_id'";
		
		$result = mysql_query($sql);
		$objeto = mysql_fetch_object($result);
		return $objeto->int_nombre.' '.$objeto->int_apellido; 
	}
}
?>