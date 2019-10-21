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
										   <div class="Etiqueta" ><span class="flechas1">* </span>Tama&ntilde;io de imagen</div>
										   <div id="CajaInput">
												<input type="text" placeholder="width" class="caja_texto" name="pla_img_width" id="pla_img_width" maxlength="250"  size="30" value="<?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_img_width; } ?>"> Por 
												<input type="text" placeholder="height" class="caja_texto" name="pla_img_height" id="pla_img_height" maxlength="250"  size="30" value="<?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_img_height; } ?>"> Pixel
										   </div>
										</div>
										
										
										
										
									</fieldset>
									
									<fieldset style="overflow:hidden;">
										<legend class="prueba puntero" style="cursor: pointer;"><b>Vista P&uacute;blico</b></legend> 

											<div id="ContenedorDiv">
											   <div class="Etiqueta" ><span class="flechas1">* </span>Tama&ntilde;io del punto marker</div>
											   <div id="CajaInput">
													<input type="text" placeholder="width" class="caja_texto" name="pla_marker" id="pla_marker" maxlength="250"  size="30" value="<?php if($formAcion=="ACTUALIZAR"){ echo $objetoDatos->pla_marker; } ?>">  
											   </div>
											</div>
											 
											<div id="ContenedorDiv">
											   <div class="Etiqueta" ><span class="flechas1">* </span>El panel izquierdo</div>
											   <div id="CajaInput">
													<select name="pla_panel_left" class="caja_texto"> 
														<option value="Automatico" <?php if($objetoDatos->pla_panel_left=='Automatico'){ echo 'selected="selected"'; }?>>Automatico</option>
														<option value="Mostrar" <?php if($objetoDatos->pla_panel_left=='Mostrar'){ echo 'selected="selected"'; } ?>>Mostrar</option>
														<option value="Ocultar" <?php if($objetoDatos->pla_panel_left=='Ocultar'){ echo 'selected="selected"'; } ?>>Ocultar</option>
													</select>
											   </div>
											</div>
											
											<div id="ContenedorDiv">
											   <div class="Etiqueta" ><span class="flechas1">* </span>Tab ver (Planos de ubanizaciones)</div>
											   <div id="CajaInput">
													<select name="pla_tab_plano" class="caja_texto">  
														<option value="Mostrar" <?php if($objetoDatos->pla_tab_plano=='Mostrar'){ echo 'selected="selected"'; }?>>Mostrar</option>
														<option value="Ocultar" <?php if($objetoDatos->pla_tab_plano=='Ocultar'){ echo 'selected="selected"'; } ?>>Ocultar</option>
													</select>
											   </div>
											</div>
											
											
											<div id="ContenedorDiv">
											   <div class="Etiqueta" ><span class="flechas1">* </span>Mostrar puntos <br>(segun el % de zoom)</div>
											   <div id="CajaInput">											   
													<select name="pla_zoom" class="caja_texto">
														<option value="default" <?php if($objetoDatos->pla_zoom=='default'){ echo 'selected="selected"'; }?>>Por defecto</option>
														<option value="6" <?php if($objetoDatos->pla_zoom=='6'){ echo 'selected="selected"'; }?>>1%</option>
														<option value="5" <?php if($objetoDatos->pla_zoom=='5'){ echo 'selected="selected"'; }?>>3%</option>
														<option value="4" <?php if($objetoDatos->pla_zoom=='4'){ echo 'selected="selected"'; } ?>>6%</option>
														<option value="3" <?php if($objetoDatos->pla_zoom=='3'){ echo 'selected="selected"'; } ?>>12%</option>
														<option value="2" <?php if($objetoDatos->pla_zoom=='2'){ echo 'selected="selected"'; } ?>>25%</option>
														<option value="1" <?php if($objetoDatos->pla_zoom=='1'){ echo 'selected="selected"'; } ?>>50%</option>
														<option value="0" <?php if($objetoDatos->pla_zoom=='0'){ echo 'selected="selected"'; } ?>>100%</option>
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
				
				var punto_tamanio = <?php echo $objetoDatos->pla_marker; ?>;
				
                function mapa_reset() {
                    var posX = "-<?php echo $objetoDatos->pla_img_width/2; ?>";
                    var posY = "-<?php echo $objetoDatos->pla_img_height/2; ?>";
                    TweenLite.to("#mapaBox", 1.5, {left: posX, top: posY, width: "<?php echo $objetoDatos->pla_img_width; ?>", height: "<?php echo $objetoDatos->pla_img_height; ?>", onComplete: mapa_reset_completo});
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
							
							mHtml += '<option data-tes="'+auxUltimoID+'" value="'+val.lot_id+'" class="'+auxAgredados+'" data-nro="'+val.lot_nro+'" data-estado="'+val.estado+'" '+auxSelected+' >'+val.lot_nro+'</option>'; 
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
						
							var lot_coorX = parseInt($(this).css("left"));
							var lot_coorY = parseInt($(this).css("top")); 
							
							$.ajax({
								type: "POST",
								url: 'modulos/uv/ajax_gestor.php?ajaxTarea=actualizar',
								data: "&lot_id="+$(this).attr("data-id")+"&lot_coorX="+lot_coorX+"&lot_coorY="+lot_coorY,
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
						
						lot_coorX = ((auxCoorX) - (punto_tamanio/2));
						lot_coorY = ((auxCoorY) - (punto_tamanio/2));
						
						var txt = '<div class="mapaForm">AGREGAR PUNTO<br/>';
						
							txt += '<div class="mapaFormFila mapaFormBox">';
								txt += 'Crear punto en lote <input type="radio" name="tipomarker" value="agregar" checked>';
							txt += '</div>';
							
							txt += '<div class="mapaFormFila mapaFormBox">';
								txt += 'Crear punto en manzano <input type="radio" name="tipomarker" value="agregar_manzano">';
							txt += '</div>';
							
							txt += '<div class="mapaFormFila mapaFormBox">';
								txt += 'Crear punto en equipamiento <input type="radio" name="tipomarker" value="agregar_equipamiento">';
							txt += '</div>';
							
							txt += '<div class="mapaFormFila mapaFormFilaUv"><label>UV: </label><select id="uv" name="uv">'+mapa_uv_generar()+'</select></div>';
							txt += '<div class="mapaFormFila mapaFormFilaManz"><label>Manzano: </label><select id="manzano" name="manzano">'+mapa_manzano_generar()+'</select></div>';
							txt += '<div class="mapaFormFila mapaFormFilaLote"><label>Lote:</label><select id="lote" name="lote">'+mapa_lotes_generar()+'</select><div id="loteCargando" class="mapaLoad">&nbsp;</div></div>'; 
							
							txt += '<div class="mapaFormFila mapaFormFilaNom"><label>Nombre: </label> <input type="text" name="equipamiento" value=""></div>';
							
							txt +="</div>";
							
						$.prompt(txt,{
							 buttons:{Agregar:true, Cancelar:false},
							 callback: function(v,m,f){
							  if(v){
									lot_ID = f.lote; 
									lot_nro = m.find("#lote option:selected").attr('data-nro');
									lot_estado = m.find("#lote option:selected").attr('data-estado');
									lot_agregados_array.push(lot_ID);
									
									var auxManzanoText = $("#manzano option:selected").text();
									var ajaxTarea = f.tipomarker;
									
									$.ajax({
										type: "POST",
										url: 'modulos/uv/ajax_gestor.php?ajaxTarea='+ajaxTarea,
										data: "&lot_id="+lot_ID+"&lot_coorX="+lot_coorX+"&lot_coorY="+lot_coorY+"&urb_id="+urb_id+"&man_id="+f.manzano+"&equipamiento="+f.equipamiento,
										success: function (results) {
											var jsondata = JSON.parse(results);
											var accion = jsondata[0].respuesta.accion.toString();
											if(accion == "correcto"){
												$("#mapaMarker").append('<div style="left: '+lot_coorX+'px; top: '+lot_coorY+'px;" data-id="'+lot_ID+'" class="mapaMarkerPuntos mapaEstados'+lot_estado+'" title="UV:  | '+auxManzanoText+'| Lote: '+lot_nro+' | ID: ">&nbsp;</div>'); 
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
						tipo_marker("agregar");
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
					
					$('input[name="tipomarker"]').live('change', function(e) {
						var auxValue = $(this).val();
						tipo_marker(auxValue);
	
					});
					
                });
				
				function tipo_marker(tipo) {
					switch (tipo)
					{
						case "agregar":
							$(".mapaFormFilaUv").show(); 
							$(".mapaFormFilaManz").show();
							$(".mapaFormFilaLote").show();
							
							$(".mapaFormFilaNom").hide();
						break;
						case "agregar_manzano":
						
							$(".mapaFormFilaManz").show();
							
							$(".mapaFormFilaLote").hide();
							$(".mapaFormFilaUv").hide(); 
							$(".mapaFormFilaNom").hide();
						break;
						case "agregar_equipamiento":
						
							$(".mapaFormFilaUv").hide(); 
							$(".mapaFormFilaManz").hide();
							$(".mapaFormFilaLote").hide();
							
							$(".mapaFormFilaNom").show();
						break;
					} 
				}
				
            </script> 
			<style>
				.mapaMarkerPuntos{
					height: <?php echo $objetoDatos->pla_marker; ?>px;
					width: <?php echo $objetoDatos->pla_marker; ?>px;
				}
			</style>
			<div class="mapaConten">
				<div class="mapaOpciones">
					<input type="button" value="Tamaño Original" onclick="mapa_reset();"/>
					<input type="button" value="Ajustar Imagen" onclick="mapa_ajustar();"/>
					<input type="button" value="Actualizar Pantalla" onclick="location.reload();"/>
				</div>
				<div class="mapaScroll" id="mapaScroll">

					<div class="mapaBox" id="mapaBox" style="width: <?php echo $objetoDatos->pla_img_width; ?>px; height: <?php echo $objetoDatos->pla_img_height; ?>px;"> 
						<div class="mapaCont"> 
							<img src="imagenes/uv/<?php echo $objetoDatos->pla_imagen; ?>" usemap="#Map" border="0" /> 
							<div class="mapaMarker" id="mapaMarker" data-punto-width="<?php echo $objetoDatos->pla_marker; ?>" data-punto-height="<?php echo $objetoDatos->pla_marker; ?>">
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
	function plano_view()
	{
		require_once("view.php"); 
	}
	function plano_ver()
	{
		$sql="SELECT * FROM urbanizacion_plano_config WHERE pla_urb_id='".$_GET['id']."'";
		$result = mysql_query($sql);
		$num = mysql_num_rows($result);
		if($num > 0) {
		
			$encodeLimpio = str_replace(array('+','/','='),array('-','_',''),base64_encode($_SESSION['id']));
			$url_actual = "http://" . $_SERVER["SERVER_NAME"]."/modulos/uv/?u=".$_GET['id']."&mapa=".$encodeLimpio;
			
			?> <br/><br/>
				<div class="fila_formulario_cabecera">PLANO DE DISPONIBILIDAD</div><br/> 
				<a href="<?php echo $url_actual; ?>" class="boton" style="padding: 5px 20px; height:auto;" target="_blank">Abrir en una ventana</a>
			<?php
		} else {  
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
		
		$sql="insert into urbanizacion_plano_config (
										pla_urb_id, 
										pla_tab_plano,
										pla_marker,
										pla_panel_left,
										pla_img_width,
										pla_img_height,
										pla_zoom
										".$imgSql."
									) values (
										'".$_POST['pla_urb_id']."',
										'".$_POST['pla_tab_plano']."',
										'".$_POST['pla_marker']."',
										'".$_POST['pla_panel_left']."',
										'".$_POST['pla_img_width']."', 
										'".$_POST['pla_img_height']."',
										'".$_POST['pla_zoom']."'
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
		
		/*
		pla_urb_id 	
		pla_tab_plano 	
		pla_panel_left 	
		pla_zoom 	
		pla_marker 	
		pla_img_width 	
		pla_img_height 	
		pla_imagen 
		*/
		
		$sql="update urbanizacion_plano_config set 
								pla_tab_plano='".$_POST['pla_tab_plano']."',
								pla_panel_left='".$_POST['pla_panel_left']."',
								pla_zoom='".$_POST['pla_zoom']."',
								pla_marker='".$_POST['pla_marker']."',
								pla_img_width='".$_POST['pla_img_width']."',
								pla_img_height='".$_POST['pla_img_height']."'
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