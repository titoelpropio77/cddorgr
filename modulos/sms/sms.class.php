<?php

class SMS extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function SMS() {
        //permisos
        $this->ele_id = 301;

        $this->busqueda();

        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 10;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "bol_asunto";
        $this->arreglo_campos[0]["texto"] = "Asunto";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 40;

        $this->arreglo_campos[1]["nombre"] = "bol_titulo";
        $this->arreglo_campos[1]["texto"] = "Título";
        $this->arreglo_campos[1]["tipo"] = "cadena";
        $this->arreglo_campos[1]["tamanio"] = 40;


        $this->link = 'gestor.php';

        $this->modulo = 'sms';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('SMS');

        $this->usu = new USUARIO;
    }

    function dibujar_busqueda() {

        $this->formulario->dibujar_cabecera();

        $this->dibujar_listado();
    }

    function set_opciones() {
        $nun = 0;

        if ($this->verificar_permisos('VER')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'VER';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_search.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER';
            $nun++;
        }

        if ($this->verificar_permisos('MODIFICAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'MODIFICAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_edit.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'MODIFICAR';
            $nun++;
        }

        if ($this->verificar_permisos('ELIMINAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ELIMINAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_drop.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ELIMINAR';
            $this->arreglo_opciones[$nun]["script"] = 'ok';
            $nun++;
        }

        if ($this->verificar_permisos('ENVIAR_SMS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ENVIAR_SMS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/mail_send.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ENVIAR A UN CELULAR';
            $nun++;
        }
        if ($this->verificar_permisos('CANCELAR ENVIO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'CANCELAR ENVIO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/parar.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'CANCELAR ENVIO';
            $nun++;
        }

        if ($this->verificar_permisos('INICIAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'INICIAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/play.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'INICIAR';
            $this->arreglo_opciones[$nun]["script"] = 'ok';
            $nun++;
        }


        if ($this->verificar_permisos('LECTORES')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'LECTORES';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/list.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'LECTORES';
            $this->arreglo_opciones[$nun]["link"] = 'sueltos/detalle.php';

            $nun++;
        }
    }

    function dibujar_listado() {
        ?>
        <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
        <script>
            function popup_iniciar() {
                $("a.group").fancybox({
                    'hideOnContentClick': false,
                    'overlayShow': true,
                    'zoomOpacity': true,
                    'zoomSpeedIn': 0,
                    'zoomSpeedOut': 0,
                    'easingIn': 'easeOutBack',
                    'easingOut': 'easeInBack',
                    'overlayOpacity': 0.5,
                    //'frameWidth'	:283,
                    //'frameHeight'	:235

                    'frameWidth': 700,
                    'frameHeight': 350
                });
            }

        </script>
        <script>
            function ejecutar_script(id, tarea) {

                var txt = '';
                var titulo = '';

                switch (tarea)
                {
                    case 'INICIAR':
                        //txt = 'Esta seguro de INICIAR el envio?';
                        iniciar_envio(id);
                        break;

                    case 'PAUSAR':
                        txt = 'Esta seguro de PAUSAR el envio?';
                        break;

                    case 'ELIMINAR':
                        //txt = 'Esta seguro de ELIMINAR el sms?';
                        eliminar(id);
                        break;
                }

//                $.prompt(txt, {
//                    buttons: {Si: true, No: false},
//                    callback: function(v, m, f) {
//
//                        if (v) {
//                            if (tarea != 'INICIAR') {
//                                //alert('iniciando envio de mensajes');
//
//                                location.href = 'gestor.php?mod=sms&tarea=' + tarea + '&id=' + id;
//                            }
//
//                        }
//
//                    }
//                });


            }

            function eliminar(id) {
                $.prompt('Esta seguro de ELIMINAR el sms?', {
                    buttons: {Si: true, No: false},
                    callback: function(v, m, f) {

                        if (v) {

                            location.href = 'gestor.php?mod=sms&tarea=ELIMINAR&id=' + id;
                        }

                    }
                });
            }

            function iniciar_envio(id) {
                $.post("datos_sms.php", {sms_id: id}, function(respuesta) {
                    res = eval("(" + respuesta + ")");
                    var enviados = res[0].value;
                    var combo = "";
                    if (enviados > 0) {
                        combo = "<br/>Existen clientes a los que ya se les envi&oacute; el mensaje, <br/><br/>¿Desea enviarles nuevamente el mensaje?\n\
                                <select id='reenvio' name='reenvio' style='width:8ex'>" +
                                "<option value='si'>Si</option>" +
                                "<option value='no'>No</option>" +
                                "</select>";
                        
                    }
                    var txt = 'Esta seguro de INICIAR el envio?' + combo;

                    $.prompt(txt, {
                        buttons: {Si: true, No: false},
                        callback: function(v, m, f) {

                            if (v) {
//                                alert('Cantidad de enviados: ' + enviados);
                                location.href = 'gestor.php?mod=sms&tarea=INICIAR&id=' + id + '&reenviar=' + f.reenvio;
                            }

                        }
                    });
                });
            }
        </script>
        <?php
        $sql = "SELECT 
				*
			  FROM 
				sms
				";


        $this->set_sql($sql, " order by sms_id desc ");

        $this->set_opciones();

        $this->dibujar();
        ?>
        <script type="text/javascript">
            $(document).ready(function() {
                popup_iniciar();
            });
        </script>  
        <?php
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th >Id</th>
            <th >Descripcion</th>
            <th >Criterio de Env&iacute;o</th>
            <th >Destinatarios</th>
            <th >Listo</th>
            <th >Despachado</th>
            <th >Enviado</th>
            <th >Estado</th>
            <th  class="tOpciones" width="200px">
                Opciones
            </th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();

        $arr_cri = array('Todos'=>'','Al Dia'=>'al_dia','1 Cuota Vencida'=>'1_cv','2 Cuota Vencidas'=>'2_cv','3 Cuota Vencidas'=>'3_cv','Mayor a 3 Cuotas Vencidas'=>'n_cv');
        
        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();
            $total=FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_men_id='$objeto->sms_id'");
            $t_listo=FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_estado='LISTO' and ban_men_id='$objeto->sms_id'");
            $operaciones=array();
            if($total!=$t_listo){
                $operaciones[]='ELIMINAR';
            }
            if($objeto->sms_estado=='Cancelado'){
                $operaciones[]='ELIMINAR';
                $operaciones[]='MODIFICAR';
                $operaciones[]='ELIMINAR';
                $operaciones[]='CANCELAR ENVIO';
            }
            echo '<tr class="busqueda_campos">';
            echo "<td>";
            echo $objeto->sms_id;
            echo "</td>";
            echo "<td>";
            echo $objeto->sms_descripcion;
            echo "</td>";
            //enum('LISTO','DESPACHADO','ENVIADO')
            
            echo "<td>";
            echo array_search($objeto->sms_criterio, $arr_cri);
            echo "</td>";
            
            echo "<td>";
                echo $total;
            echo "</td>";
            echo "<td>";
                echo $t_listo;
            echo "</td>";
            echo "<td>";
                echo FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_estado='DESPACHADO' and ban_men_id='$objeto->sms_id'");
            echo "</td>";
            echo "<td>";
                echo FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_estado='ENVIADO' and ban_men_id='$objeto->sms_id'");
            echo "</td>";
            echo "<td>";
                echo $objeto->sms_estado;
            echo "</td>";

            
            echo '<td width="80" >';
            echo $this->get_opciones($objeto->sms_id,'',$operaciones);
            echo "</td>";

            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }
    
    

    function mostrar_categorias($bol) {
        $conec = new ADO();

        $consulta = "select 
						cat_id,cat_nombre 
					from 
						boletin inner join boletin_categoria on (bol_id='$bol' and bol_id=bca_bol_id)
								   inner join categorias on (bca_cat_id=cat_id)
					";

        $conec->ejecutar($consulta);

        $numero = $conec->get_num_registros();

        $cad = "<ul>";

        for ($i = 0; $i < $numero; $i++) {

            $objeto = $conec->get_objeto();

            $cad.="<li>$objeto->cat_nombre</li>";

            $conec->siguiente();
        }

        $cad.="</ul>";

        return $cad;
    }

    function datos() {
        if ($_POST) {
            return true;
        }
        return false;
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from sms
				where sms_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['sms_descripcion'] = $objeto->sms_descripcion;
        
        $_POST['sms_criterio'] = $objeto->sms_criterio;

        $_POST['sms_texto'] = $objeto->sms_texto;

        $_POST['sms_id'] = $objeto->sms_id;
		
		$_POST['sms_tipo'] = $objeto->sms_tipo;
    }

    function formulario_tcp($tipo) {
        
        $this->formulario->dibujar_tarea('PERSONA');

        switch ($tipo) {
            case 'ver': {
                    $ver = true;
                    break;
                }

            case 'cargar': {
                    $cargar = true;
                    break;
                }
        }

        $url = $this->link . '?mod=' . $this->modulo;

        $red = $url . '&tarea=ACCEDER';

        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }

        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }

        if ($this->mensaje <> "") {
            ?>
            <table width="100%" cellpadding="0" cellspacing="1" style="border:1px solid #DD3C10; color:#DD3C10;">
                <tr bgcolor="#FFEBE8">
                    <td align="center">
                        <?php
                        echo $this->mensaje;
                        ?>
                    </td>
                </tr>
            </table>
            <?php
        }
        ?>
		<script type="text/javascript" src="js/util.js"></script>
        <script>
		
            function reset_interno(){
                document.frm_sentencia.usu_per_id.value = "";
                document.frm_sentencia.usu_nombre_persona.value = "";
            }
			
            function datos_fila() {
                var proy_id = $('[name="proy_id"] option:selected').val();
                var proy_text = $('[name="proy_id"] option:selected').text();
				
                if (proy_id != '') {
                    if (verificar(proy_id)) {
                        var filas = parseFloat(document.frm_sentencia.nfilas.value);
                        document.frm_sentencia.nfilas.value = filas + 1;
                        if (document.frm_sentencia.nfilas.value > 0) {
                            document.frm_sentencia.nfilasshadown.value = 1;
                        }
                        addTableRow('#tprueba', "<tr><td><input name='acceso[]' type='hidden' value='" + proy_id + "'>" + proy_text +
                                "</td><td><center><img src='images/b_drop.png' onclick='javascript:remover_acceso(this);'></center></td></tr>");
                        limpiar_campos();
                    } else {
                        $.prompt('El item seleccionado ya fue agregado a la Lista', {opacity: 0.8});
                    }
                } else {
                    $.prompt('- Seleccione la urbanizacion a la que enviará los sms.', {opacity: 0.8});
                }
            }
			
            function limpiar_campos() {
                document.frm_sentencia.proy_id.value = '';
            }
            function remover_acceso(row) {
                var filas = parseFloat(document.frm_sentencia.nfilas.value);
                document.frm_sentencia.nfilas.value = filas - 1;
                if (document.frm_sentencia.nfilas.value == 0) {
                    document.frm_sentencia.nfilasshadown.value = '';
                }
                var cant = $(row).parent().parent().parent().children().length;
                if (cant > 1)
                    $(row).parent().parent().parent().remove();
            }
		
            function addTableRow(id, valor) {
                $(id).append(valor);
            }
            
            function enviar_formulario() {
				
				var sms_tipo = $("#sms_tipo").val();
				
				if(sms_tipo !=""){
					//Urbanizacion
					if(sms_tipo =="Urbanizacion"){
						  if (document.frm_sentencia.nfilas.value == 0) {
								$.prompt('Debe escojer alguna urbanizacion.', {opacity: 0.8});
								return false;
							}
							console.log('criterio => '+ $('#criterio option:selected').val());
							if ($('#criterio option:selected').val() === 'sel') {
								$.prompt('Debe escojer algun criterio de envio.', {opacity: 0.8});
								return false;
							}
							
							console.log('enviando el formulario...');
							$('#frm_sentencia').submit();
					}
					
					//Manzano
					if(sms_tipo =="Manzano"){
						if ($('#criterio option:selected').val() === 'sel') {
							$.prompt('Debe escojer algun criterio de envio.', {opacity: 0.8});
							return false;
						}
						if (document.frm_sentencia.nfilas_man.value == 0) { 
							$.prompt('Debe escojer algun manzano.', {opacity: 0.8});
							return false;
						}
						$('#frm_sentencia').submit();
					}
					
				} else {
					$.prompt('Debe escojer "tipo de envio"', {opacity: 0.8});
				}
            }

            function verificar(id) {
                var cant = $('#tprueba tbody').children().length;
                var ban = true;
                if (cant > 0) {
                    $('#tprueba tbody').children().each(function() {
                        var dato = $(this).eq(0).children().eq(0).children().eq(0).attr('value');
                        if (id == dato) {
                            ban = false;
                            console.log('pasando por falso');
                        }

                    });
                }
                return ban;
            }
        </script>

        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">


                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">       

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Descripcion</div>
                            <div id="CajaInput">                                
                                <input <?php echo $_GET[id]?'readonly=""':'' ;?> name="sms_descripcion" size="45" id="sms_descripcion" value="<?php echo $_POST['sms_descripcion']; ?>"/>
                            </div>
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span>Criterio de Env&iacute;o</div>
                                <div id="CajaInput">
                                    <select name="criterio" id="criterio" class="caja_texto" onchange="">
                                        <option value="sel">Seleccione</option>
                                        <option value="" <?php echo $_POST[sms_criterio]==''?'selected':'';?>>Todos</option>
                                        <option value="al_dia" <?php echo $_POST[sms_criterio]=='al_dia'?'selected':'';?>>Al Dia</option>
                                        <option value="1_cv" <?php echo $_POST[sms_criterio]=='1_cv'?'selected':'';?>>1 Cuota Vencida</option>
                                        <option value="2_cv" <?php echo $_POST[sms_criterio]=='2_cv'?'selected':'';?>>2 Cuota Vencidas</option>
                                        <option value="3_cv" <?php echo $_POST[sms_criterio]=='3_cv'?'selected':'';?>>3 Cuota Vencidas</option>
                                        <option value="n_cv" <?php echo $_POST[sms_criterio]=='n_cv'?'selected':'';?>>Mayor a 3 Cuotas Vencidas</option>
                                    </select><br><br> 
                                    <span style="color: blue;">(Este criterio aplica a todas las urbanizaciones que Usted agregue)</span>
                                </div>                                
						</div>
						
						<div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span> Tipo de envio</div>
							<div id="CajaInput">
								<select name="sms_tipo" id="sms_tipo" class="caja_texto" onchange="">
									<option value="">Seleccione</option>
									<option value="Urbanizacion" <?php echo $_POST['sms_tipo']=='Urbanizacion'?'selected':'';?>>Por Urbanizacion</option>
									<option value="Manzano" <?php echo $_POST['sms_tipo']=='Manzano'?'selected':'';?>>Por Manzano</option>
								</select>
								<script>
									function sms_tipo_activo(){
										var tipo = $("#sms_tipo option:selected").val();
										if(tipo !=""){
											if(tipo == "Urbanizacion"){
												$("#Urbanizacion").show();
												$("#Manzano").hide();
											} else {
												$("#Urbanizacion").hide();
												$("#Manzano").show();
											}
										}
									}
									$(document).ready(function(){
										$("#sms_tipo").change(function(){
											sms_tipo_activo();
										});
										sms_tipo_activo();
									});
								</script>
							</div>                                
						</div>
						
						<div class="boxs hide" id="Urbanizacion">
							<div class="Subtitulo">Urbanizaciones a enviar el mensaje</div>
							<div id="ContenedorSeleccion">
								<!--Inicio-->
								<div id="ContenedorDiv">
									<div class="Etiqueta" ><span class="flechas1">* </span>Urbanizacion</div>
									<div id="CajaInput">
										<select name="proy_id" id="proy_id" class="caja_texto" onchange="">
											<option value="">Seleccione</option>
											<?php
											$fun = NEW FUNCIONES;
											$fun->combo("select urb_id as id, urb_nombre as nombre from urbanizacion order by urb_id", "");
											?>
										</select>
									</div>
									<div id="campo_nombre_urbanizacion"><input type="hidden" name="nombre_urbanizacion" id="nombre_urbanizacion" value="" size="10" /></div>
								</div>
								
								<div id="ContenedorDiv">
									<div id="CajaInput">
										<input type="hidden" name="nfilas" id="nfilas" value="0">
										<input type="hidden" name="nfilasshadown" id="nfilasshadown" value="">
										<?php // if(!$_GET[id]){?>
										<img src="images/boton_agregar.png" style='margin:0px 0px 0px 10px' onclick="javascript:datos_fila();">
										<?php // }?>
									</div>
									<div id="CajaInput" style="margin:5px 0px 0px 10px;">
										<table  width="600"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
											<thead>
												<tr>                                                                                   
													<th>Urbanizacion</th>
													<th class="tOpciones">Eliminar</th>
												</tr>							
											</thead>
											<tbody>
												<?php
												if ($ver || $cargar) {
													$this->cargar_accesos_proyectos($_GET['id']);
												}
												?>	
											</tbody>
										</table>
									</div>
								</div>
								<!--Fin-->
							</div>
						</div>
						
						<div class="boxs hide" id="Manzano">
							<!--Inicio-->
							<div id="ContenedorDiv">
								<div class="Etiqueta" ><span class="flechas1">*</span>Urbanizacion</div>
								<div id="CajaInput">
									<select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" onchange="cargar_uv(this.value);">
										<option value="">Seleccione</option>
										<?php
										$fun = NEW FUNCIONES;
										$fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion", $_POST['ven_urb_id']);
										?>
									</select>
								</div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
								<div class="Etiqueta" ><span class="flechas1">* </span>UV</div>
								<div id="CajaInput">
									<div id="uv">
										<select style="width:200px;" name="ven_uv_id" class="caja_texto">
											<option value="">Seleccione</option>
											<?php 
											if ($_POST['ven_urb_id'] <> "") {
												$fun = NEW FUNCIONES;
												$fun->combo("select uv_id as id,uv_nombre as nombre from uv where uv_urb_id='" . $_POST['ven_urb_id'] . "' ", $_POST['ven_uv_id']);
											}
											?> 
										</select>
									</div>
								</div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
								<div class="Etiqueta" ><span class="flechas1">* </span>Manzano</div>
								<div id="CajaInput">
									<div id="manzano">
										<select style="width:200px;" name="ven_man_id" class="caja_texto">
											<option value="">Seleccione</option>
										<?php
										if ($_POST['ven_urb_id'] <> "") {
											$fun = NEW FUNCIONES;
											$fun->combo("select man_id as id,man_nro as nombre from manzano where man_urb_id='" . $_POST['ven_urb_id'] . "' ", $_POST['ven_man_id']);
										}
										?>
										</select>
									</div>
								</div>
							</div>
							
							<div id="ContenedorDiv">
								<div id="CajaInput">
									<input type="hidden" name="nfilas_man" id="nfilas_man" value="0"> 
									<input type="hidden" name="nfilasshadown_man" id="nfilasshadown_man" value="">
									<img src="images/boton_agregar.png" style='margin:0px 0px 0px 10px' onclick="javascript:datos_fila_man();">
								</div>
								<div id="CajaInput" style="margin:5px 0px 0px 10px;">
									<table width="600" class="tablaReporte" id="tprueba_man" cellpadding="0" cellspacing="0">
										<thead> 
											<tr>    
												<th>Urbanizacion</th>
												<th>UV</th>
												<th>Manzano</th>
												<th class="tOpciones">Eliminar</th>
											</tr>							
										</thead>
										<tbody>
											<?php
											if ($ver || $cargar) {
												$this->cargar_manzanos($_GET['id']);
											}
											?>	
										</tbody>
									</table>
								</div>
							</div>
							
							<!--Fin-->
							 <script>
								function cargar_uv(id){
									var valores = "tarea=uv&urb=" + id;
									ejecutar_ajax('ajax.php', 'uv', valores, 'POST');
								}
								function obtener_valor_manzano() {
									var auxUrb = $('#ven_urb_id').val();
									var auxUv = $('#ven_uv_id').val();
									cargar_manzano(auxUrb, auxUv);
								}
								function obtener_valor_uv(){ 
										
								}
								function cargar_manzano(id, uv){
									var valores = "tarea=manzanos&urb=" + id + "&uv=" + uv;
									ejecutar_ajax('ajax.php', 'manzano', valores, 'POST');
								}
								
								//Nuevas funciones
								function addTableRow_man(id, valor) {
									$(id).append(valor);
								}
								
								function limpiar_campos_man() {
									document.frm_sentencia.ven_man_id.value = ''; 
								}
								
								function verificar_man(id) {
									var cant = $('#tprueba_man tbody').children().length;
									var ban = true;
									if (cant > 0) {
										var urb_id = $("#ven_urb_id").find("option:selected").val();
										var uv_id = $("#ven_uv_id").find("option:selected").val();
										var auxId = urb_id+"|"+uv_id+"|"+id;
										$('#tprueba_man tbody tr').each(function() {
											var dato = $(this).find('input').val();   
											if (auxId == dato) {
												ban = false;  
												console.log('pasando por falso'); 
											}
										});
									}
									return ban; 
								}
								
								function remover_acceso_man(row) {
									var filas = parseFloat(document.frm_sentencia.nfilas_man.value);
									document.frm_sentencia.nfilas_man.value = filas - 1;
									if (document.frm_sentencia.nfilas_man.value == 0) {
										document.frm_sentencia.nfilasshadown.value = '';
									}
									var cant = $(row).parent().parent().parent().children().length;
									if (cant > 1)
										$(row).parent().parent().parent().remove();
								}
								
								function datos_fila_man() { 
									var proy_id = $('[name="ven_man_id"] option:selected').val();
									var proy_text = $('[name="ven_man_id"] option:selected').text();
									if (proy_id != '') {
										if (verificar_man(proy_id)) {
											
											var filas = parseFloat(document.frm_sentencia.nfilas_man.value);
											document.frm_sentencia.nfilas_man.value = filas + 1;
											if (document.frm_sentencia.nfilas_man.value > 0) {
												document.frm_sentencia.nfilasshadown_man.value = 1;
											}
											
											var urb_id = $("#ven_urb_id").find("option:selected").val();
											var urb_nombre = $("#ven_urb_id").find("option:selected").text();
											
											var uv_id = $("#ven_uv_id").find("option:selected").val();
											var uv_nombre = $("#ven_uv_id").find("option:selected").text();
											
											var auxHtml = "<tr>";
											 auxHtml += "<td>" + urb_nombre +"</td>";
											 auxHtml += "<td>" + uv_nombre +"</td>";
											 auxHtml += "<td><input name='man[]' type='hidden' value='" + urb_id+"|"+uv_id+"|"+proy_id + "'>" + proy_text +"</td>"; 
											 auxHtml += "<td><center><img src='images/b_drop.png' onclick='javascript:remover_acceso_man(this);'></center></td>";
											auxHtml += "</tr>";
											
											addTableRow_man('#tprueba_man', auxHtml);
											limpiar_campos_man();
										} else {
											$.prompt('El item seleccionado ya fue agregado a la Lista', {opacity: 0.8});
										}
									} else {
										$.prompt('- Seleccione el manzano a la que enviará los sms.', {opacity: 0.8});
									}
								}
							</script>
						</div>
						
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Mensaje</div>
                            <div id="CajaInput">
                                <!--<input  name="texto" maxlength="140" type="text" id="texto" size="53"  value="<?php echo $_POST['texto']; ?>"/>-->
                                <textarea  name="sms_texto" rows="3" cols="45" id="sms_texto" maxlength="140" ><?php echo $_POST['sms_texto']; ?></textarea><br/>
                                <p style="color: blue">Cantidad Escrita: <b id="text_cant_escrito"><?php echo strlen($_POST[sms_texto]);?></b></p>
                                <p style="color: blue">Usted tiene <b>140</b> caracteres para escribir en el recuadro de arriba.</p>
                            </div>
                        </div>                    
                    </div>
					
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <?php
                                if (!($ver)) {
                                    ?>
                                <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario();">
                                    <input type="reset" class="boton" name="" value="Cancelar">
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                    <?php
                                } else {
                                    ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                    <?php
                                }
                                ?>
                            </center>
                        </div>
                    </div>
                </div>
        </div>
		
        <script>
            $('#sms_texto').keyup(function(){                
               var valor=$(this).val();
               $('#text_cant_escrito').text(valor.length);
            });
        </script>
        <?php
    }

    function hora($hora) {

        list($valorhora, $valorminuto) = split(":", $hora);

        echo '<select class="caja_texto" name="hora">';

        for ($hora = 0; $hora <= 23; $hora++) {
            $hora_men = $hora;

            if ($hora < 10) {
                $hora_men = '0' . $hora;
            }

            if ($hora == $valorhora and $valorhora <> "") {
                echo '<option value=' . $hora_men . ' selected>' . $hora_men . '</option>';
            } else {
                echo '<option value=' . $hora_men . '>' . $hora_men . '</option>';
            }
        }

        echo '</select>';
        echo '<select class="caja_texto"  name="minuto">';

        for ($minuto = 0; $minuto < 60; $minuto = $minuto + 15) {
            $minuto_men = $minuto;
            if ($minuto < 10) {
                $minuto_men = '0' . $minuto;
            }

            if ($minuto == $valorminuto and $valorminuto <> "") {
                echo '<option value=' . $minuto_men . ' selected>' . $minuto_men . '</option>';
            } else {
                echo '<option value=' . $minuto_men . '>' . $minuto_men . '</option>';
            }
        }

        echo '</select>';
    }

    function categorias() {

        if ($_GET['tarea'] == 'MODIFICAR' || $_GET['tarea'] == 'VER') {

            if ($_POST['tecno'])
                $a_categorias = $_POST['tecno'];
            else
                $a_categorias = $this->obtener_categorias($_POST['bol_id']);
        }
        else {
            if ($_POST['tecno'])
                $a_categorias = $_POST['tecno'];
            else
                $a_categorias = array();
        }


        $conec = new ADO();

        $sql = "SELECT cat_id,cat_nombre,cat_tipo
						FROM categorias
						ORDER BY cat_tipo,cat_nombre asc";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        if ($num <> 0) {
            ?>
            <div style="width:420px; float:left;">
                <?php
                for ($i = 0; $i < $num; $i++) {
                    $objeto = $conec->get_objeto();

                    if ($i == 0) {
                        echo '<div style="clear:both;"><b>' . $objeto->cat_tipo . '</b></div>';
                        $tipo = $objeto->cat_tipo;
                    }

                    if ($tipo <> $objeto->cat_tipo) {
                        echo '<div style="clear:both;"><b>' . $objeto->cat_tipo . '</b></div>';
                        $tipo = $objeto->cat_tipo;
                    }


                    if (is_numeric(array_search($objeto->cat_id, $a_categorias))) {
                        ?>
                        <div class="Tecno"><input checked="checked" type="checkbox" name="tecno[]" value="<?php echo $objeto->cat_id; ?>"><?php echo $objeto->cat_nombre; ?><br></div>
                        <?php
                    } else {
                        ?>
                        <div class="Tecno"><input type="checkbox" name="tecno[]" value="<?php echo $objeto->cat_id; ?>"><?php echo $objeto->cat_nombre; ?><br></div>
                        <?php
                    }


                    $conec->siguiente();
                }
                ?>
            </div>
            <?php
        }
    }

    function obtener_categorias($boletin) {
        $conec = new ADO();

        $vector = array();

        $consulta = "select bca_cat_id from boletin_categoria where bca_bol_id='" . $boletin . "'";

        $conec->ejecutar($consulta);

        $numero = $conec->get_num_registros();

        for ($i = 0; $i < $numero; $i++) {

            $objeto = $conec->get_objeto();

            $vector[$i] = $objeto->bca_cat_id;

            $conec->siguiente();
        }
        return $vector;
    }

    function insertar_tcp() {
		
        $conec = new ADO();
        $sql = "insert into sms(sms_descripcion,sms_texto,sms_usu_id,sms_criterio,sms_tipo) 
                            values ('" . $_POST['sms_descripcion'] . "','" . $_POST['sms_texto'] . "','" . $this->usu->get_id() . "','$_POST[criterio]','$_POST[sms_tipo]')";
        $conec->ejecutar($sql, false);
        $ult = mysql_insert_id();
		
		//Enviar por urbanizacion
		if($_POST['sms_tipo']=="Urbanizacion") {
			$a_tecno = $_POST['acceso'];
			foreach ($a_tecno as $tec) {
				$sql = "insert into sms_urbanizacion(
							sur_sms_id,sur_urb_id
						)values(
							'$ult','$tec')
						";
				$conec->ejecutar($sql);
			}
			
			$arr_crit = array(-1 => '', 0 => 'al_dia', 1 => '1_cv', 2 => '2_cv', 3 => '3_cv', 4 => 'n_cv');
			$flag = FALSE;
			
			if ($_POST[criterio] == '') {
				$flag = TRUE;
			}
			
			foreach ($a_tecno as $tec) {
				$sql="select 
							int_id,int_nombre,int_apellido, int_telefono, int_celular,ven_id 
						from 
							interno, venta,lote, manzano 
						where 
							man_urb_id='$tec' and (int_telefono!='' or int_celular!='') 
								and ven_int_id=int_id 
								and ven_lot_id=lot_id 
								and lot_man_id=man_id
								and ven_estado in ('Pendiente') ";
				$lista_ventas=  FUNCIONES::objetos_bd_sql($sql);
				$sql_insert="insert into bandeja(ban_int_id,ban_cel,ban_contenido,ban_men_id, ban_estado,ban_fecha_cre,ban_hora_cre)values";
				for ($i = 0; $i < $lista_ventas->get_num_registros(); $i++) {
					$cli=$lista_ventas->get_objeto();
					$cuantas = array_search($_POST[criterio], $arr_crit);                
					
					if ($this->cuotas_vencida($cli->ven_id, $cuantas, $i) || $flag) {
						
						$celulares=$this->get_num_celular($cli->int_telefono , $cli->int_celular);
						$fecha=date('Y-m-d');
						$hora=date('H:i:s');
						foreach($celulares as $cel){
							$sql=$sql_insert."('$cli->int_id','$cel','$_POST[sms_texto]','$ult','LISTO','$fecha','$hora')";
							$conec->ejecutar($sql,false);
							break;
						}
					}                                                
					$lista_ventas->siguiente();
				}            
			}	
		}
		
		//Enviar por manzano 
		if($_POST['sms_tipo']=="Manzano") {
			
			$man = $_POST['man'];
			$manzanosArray = array();
			foreach ($man as $val) {
				$datos = explode('|',$val);
				$manzanosArray[] = $datos[2];
				$sql = "insert into sms_manzano (
													sma_urb_id,
													sma_uv_id,
													sma_man_id,
													sma_sms_id
												)values(
													'".$datos[0]."',
													'".$datos[1]."',
													'".$datos[2]."',
													'".$ult."'
												);";
				$conec->ejecutar($sql);
			}
			
			$manzanos = implode(",", $manzanosArray);
			
			$arr_crit = array(-1 => '', 0 => 'al_dia', 1 => '1_cv', 2 => '2_cv', 3 => '3_cv', 4 => 'n_cv');
			$flag = FALSE;
			
			if ($_POST[criterio] == '') {
				$flag = TRUE;
			}
			
			$sql="select 
					int_id,int_nombre,int_apellido, int_telefono, int_celular,ven_id 
				from 
					interno, venta,lote, manzano 
				where 
					(int_telefono!='' or int_celular!='') 
					and ven_int_id=int_id 
					and ven_lot_id=lot_id 
					and lot_man_id=man_id 
					and ven_estado in ('Pendiente') 
					and man_id in(".$manzanos."); ";
			$lista_ventas=  FUNCIONES::objetos_bd_sql($sql);
			$sql_insert="insert into bandeja(ban_int_id,ban_cel,ban_contenido,ban_men_id, ban_estado,ban_fecha_cre,ban_hora_cre)values";
			for ($i = 0; $i < $lista_ventas->get_num_registros(); $i++) {
				$cli=$lista_ventas->get_objeto();
				$cuantas = array_search($_POST[criterio], $arr_crit);
				if ($this->cuotas_vencida($cli->ven_id, $cuantas, $i) || $flag) {
					$celulares=$this->get_num_celular($cli->int_telefono , $cli->int_celular);
					$fecha=date('Y-m-d');
					$hora=date('H:i:s');
					foreach($celulares as $cel){
						$sql=$sql_insert."('$cli->int_id','$cel','$_POST[sms_texto]','$ult','LISTO','$fecha','$hora')";
						$conec->ejecutar($sql,false);
						break;
					} 
				}                                                
				$lista_ventas->siguiente();
			}
		}
		
        $mensaje = 'Sms Agregado Correctamente!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
    }
	
    function cuotas_vencida($ven_id, $cuantas, $i){
        $b = FALSE;
        if ($cuantas < 0) {
            $i++;
            return TRUE;
        }
        
        $hoy = date("Y-m-d");
        $sql = "select ind_id from interno_deuda where ind_estado='Pendiente'
                and ind_fecha_programada < '$hoy'
                and ind_tabla_id='$ven_id' and ind_tabla='venta'";
        $cuotas = FUNCIONES::objetos_bd_sql($sql);
        $res = "";
        if ($cuantas < 4 ) {
            if ($cuotas->get_num_registros() == $cuantas) {
                $res = "ok";
                $b = TRUE;
            }
        } else {
            if ($cuotas->get_num_registros() >= $cuantas) {
                $res = "ok";
                $b = TRUE;
            }
        }
        
        if (($i % 2) == 0 || $res == 'ok') {
//            echo "<br/>$sql;$res<br/>";
        }
        
        return $b;
    }
    
    function get_num_celular($telefono,$celular=""){
        $numeros=array();
        $telefono=  trim($telefono);
        if($telefono){
            $i=0;
            $numero='';
            while ($i<  strlen($telefono)){
                $char=$telefono[$i];
                if($char!='-' && $char!='/'){
                    $numero.=$char;
                }
                if(strlen($numero)==8){
                    if($numero[0]=='7' || $numero[0]=='6'){
                        $numeros[]=$numero;
                        $numero='';
                    }else{
                        $numero='';
                    }
                }elseif(strlen($numero)==7){
                    if($numero[0]!='7' && $numero[0]!='6'){
                        $numero='';
                    }
                }
                $i++;
            }
        }
        $celular=  trim($celular);
        if($celular){
            $i=0;
            $numero='';
            while ($i<  strlen($celular)){
                $char=$celular[$i];
                if($char!='-' && $char!='/'){
                    $numero.=$char;
                }
                if(strlen($numero)==8){
                    if($numero[0]=='7' || $numero[0]=='6'){
                        $numeros[]=$numero;
                        $numero='';
                    }else{
                        $numero='';
                    }
                }elseif(strlen($numero)==7){
                    if($numero[0]!='7' && $numero[0]!='6'){
                        $numero='';
                    }
                }
                $i++;
            }
        }
        return $numeros;
    }

    function modificar_tcp() {
        $conec = new ADO();
        $sql = "update sms set 
		
                sms_texto='" . $_POST['sms_texto'] . "',
                sms_criterio='" . $_POST['criterio'] . "',    
                sms_descripcion='" . $_POST['sms_descripcion'] . "',
				sms_tipo='" . $_POST['sms_tipo'] . "'
				
                where sms_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);
		
		$sql_delete = "delete from bandeja where ban_men_id='".$_GET['id']."'; ";
		$conec->ejecutar($sql_delete);
		
		//Enviar por urbanizacion
		if($_POST['sms_tipo']=="Urbanizacion") {
			
			$sql = "delete from sms_urbanizacion where sur_sms_id='" . $_GET['id'] . "'";
			$conec->ejecutar($sql);
			
			$a_tecno = $_POST['acceso'];
			
			foreach ($a_tecno as $tec) {
				$sql = "insert into sms_urbanizacion (sur_sms_id,sur_urb_id) values('" . $_GET['id'] . "','" . $tec . "')";
				$conec->ejecutar($sql);
			}
			
			$ult = $_GET[id];
			$arr_crit = array(-1 => '', 0 => 'al_dia', 1 => '1_cv', 2 => '2_cv', 3 => '3_cv', 4 => 'n_cv');
			$flag = FALSE;
			if ($_POST[criterio] == '') {
				$flag = TRUE;
			}
			
			foreach ($a_tecno as $tec) {
				$sql="  select 
							int_id,int_nombre,int_apellido, int_telefono, int_celular,ven_id 
						from 
							interno, venta,lote, manzano 
						where 
							man_urb_id='$tec' and (int_telefono!='' or int_celular!='') 
								and ven_int_id=int_id 
								and ven_lot_id=lot_id 
								and lot_man_id=man_id
								and ven_estado in ('Pendiente')";
								
				$lista_ventas=  FUNCIONES::objetos_bd_sql($sql);
				
				$sql_insert="insert into bandeja(ban_int_id,ban_cel,ban_contenido,ban_men_id, ban_estado,ban_fecha_cre,ban_hora_cre)values";
				for ($i = 0; $i < $lista_ventas->get_num_registros(); $i++) {
					$cli=$lista_ventas->get_objeto();
					$cuantas = array_search($_POST[criterio], $arr_crit);                
					
					if ($this->cuotas_vencida($cli->ven_id, $cuantas, $i) || $flag) {
						
						$celulares=$this->get_num_celular($cli->int_telefono , $cli->int_celular);
						$fecha=date('Y-m-d');
						$hora=date('H:i:s');
						foreach($celulares as $cel){
							$sql=$sql_insert."('$cli->int_id','$cel','$_POST[sms_texto]','$ult','LISTO','$fecha','$hora')";
							$conec->ejecutar($sql,false);
							break;
						}
						
					}                                                
					
					$lista_ventas->siguiente();
				}            
			}
        }
		
		
		//Enviar por manzano 
		if($_POST['sms_tipo']=="Manzano") {
			
			$sms_id = $_GET['id'];
			$sql = "delete from sms_manzano where sma_sms_id='" . $sms_id . "'";
			$conec->ejecutar($sql);
			
			$man = $_POST['man'];
			$manzanosArray = array();
			foreach ($man as $val) {
				$datos = explode('|',$val);
				$manzanosArray[] = $datos[2];
				$sql = "insert into sms_manzano (
													sma_urb_id,
													sma_uv_id,
													sma_man_id,
													sma_sms_id
												)values(
													'".$datos[0]."',
													'".$datos[1]."',
													'".$datos[2]."',
													'".$sms_id."'
												);";
				$conec->ejecutar($sql);
			}
			
			$manzanos = implode(",", $manzanosArray);
			
			$arr_crit = array(-1 => '', 0 => 'al_dia', 1 => '1_cv', 2 => '2_cv', 3 => '3_cv', 4 => 'n_cv');
			$flag = FALSE;
			
			if ($_POST[criterio] == '') {
				$flag = TRUE;
			}
			
			$sql="select 
					int_id,int_nombre,int_apellido, int_telefono, int_celular,ven_id 
				from 
					interno, venta,lote, manzano 
				where 
					(int_telefono!='' or int_celular!='') 
					and ven_int_id=int_id 
					and ven_lot_id=lot_id 
					and lot_man_id=man_id 
					and ven_estado in ('Pendiente') 
					and man_id in(".$manzanos."); ";
			$lista_ventas=  FUNCIONES::objetos_bd_sql($sql);
			$sql_insert="insert into bandeja(ban_int_id,ban_cel,ban_contenido,ban_men_id, ban_estado,ban_fecha_cre,ban_hora_cre)values";
			for ($i = 0; $i < $lista_ventas->get_num_registros(); $i++) {
				$cli=$lista_ventas->get_objeto();
				$cuantas = array_search($_POST[criterio], $arr_crit);
				if ($this->cuotas_vencida($cli->ven_id, $cuantas, $i) || $flag) {
					$celulares=$this->get_num_celular($cli->int_telefono , $cli->int_celular);
					$fecha=date('Y-m-d');
					$hora=date('H:i:s');
					foreach($celulares as $cel){
						$sql=$sql_insert."('$cli->int_id','$cel','$_POST[sms_texto]','$sms_id','LISTO','$fecha','$hora')";
						$conec->ejecutar($sql,false);
						break;
					} 
				}                                                
				$lista_ventas->siguiente();
			}
		}
		
        $mensaje = 'Sms Modificado Correctamente!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
    }

    function formulario_confirmar_eliminacion() {

        $mensaje = 'Esta seguro de eliminar el Sms?';

        $this->formulario->ventana_confirmacion($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ELIMINAR', 'sms_id');
    }

    function eliminar_tcp() {
        $conec = new ADO();
        $total=FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_men_id='$_GET[id]'");
        $t_listo=FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_estado='LISTO' and ban_men_id='$_GET[id]'");
        if($total==$t_listo){
            $sql = "delete from sms where sms_id='" . $_GET['id'] . "'";
            $conec->ejecutar($sql);        
            $sql = "delete from sms_urbanizacion where sur_sms_id='" . $_GET['id'] . "'";
            $conec->ejecutar($sql);
			
            $sql = "delete from bandeja where ban_men_id='" . $_GET['id'] . "'";
            $conec->ejecutar($sql);
			
			$sql = "delete from sms_manzano where sma_sms_id ='" . $_GET['id'] . "'";
			$conec->ejecutar($sql); 
			
            $mensaje = 'Sms Eliminado Correctamente!';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
        }else{
            $this->formulario->ventana_volver("No se puede eliminar el Manual debido a que hay envios ejecutandose", $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER','','error');
        }
    }
	
    function formulario_confirmar_iniciar() {

        $mensaje = 'Esta seguro de iniciar el envio del Boletín?';

        $this->formulario->ventana_confirmacion($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=INICIAR', 'bol_id');
    }

    function iniciar() {

        $this->formulario->dibujar_tarea();
        $conec = new ADO();

//        $sql = "SELECT DISTINCT int_id, CONCAT(int_nombre,' ',int_apellido) AS cliente,int_celular,sms_texto
//                FROM interno
//                INNER JOIN venta ON (int_id=ven_int_id)
//                INNER JOIN lote ON (ven_lot_id=lot_id)
//                INNER JOIN uv ON (lot_uv_id=uv_id)
//                INNER JOIN manzano ON (lot_man_id=man_id)
//                INNER JOIN urbanizacion ON (man_urb_id=urb_id)
//                INNER JOIN sms_urbanizacion ON (urb_id=sur_urb_id)
//                INNER JOIN sms ON (sur_sms_id=sms_id)
//                WHERE sms_id = '$_GET[id]' and ven_estado in ('Pendiente','Pagado','Pendiente por Cobrar') and int_celular !=''
//                GROUP BY int_id
//                ORDER BY int_id";

        $sql = "select sms_ultimo,sms_texto from sms where sms_id='$_GET[id]'";
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();

        if ($num > 0) {

            $obj = $conec->get_objeto();
            
            $ultimo = $obj->sms_ultimo;
            
            if ($_GET['reenviar']) {
                if ($_GET['reenviar'] == 'si') {
                    $ultimo = 0;
                }
            }                        
            
            $sms = $obj->sms_texto;

//            $sql = "SELECT DISTINCT int_id, CONCAT(int_nombre,' ',int_apellido) AS cliente,int_celular
//                FROM interno
//                INNER JOIN venta ON (int_id=ven_int_id)
//                INNER JOIN lote ON (ven_lot_id=lot_id)
//                INNER JOIN uv ON (lot_uv_id=uv_id)
//                INNER JOIN manzano ON (lot_man_id=man_id)
//                INNER JOIN urbanizacion ON (man_urb_id=urb_id)
//                INNER JOIN sms_urbanizacion ON (urb_id=sur_urb_id)
//                INNER JOIN sms ON (sur_sms_id=sms_id)
//                WHERE sms_id = '$_GET[id]'
//                GROUP BY int_id
//                ORDER BY int_id                
//                LIMIT $ultimo,20";

            $sql = "SELECT DISTINCT int_id, CONCAT(int_nombre,' ',int_apellido) AS cliente,int_celular
                FROM interno
                INNER JOIN venta ON (int_id=ven_int_id)
                INNER JOIN lote ON (ven_lot_id=lot_id)
                INNER JOIN uv ON (lot_uv_id=uv_id)
                INNER JOIN manzano ON (lot_man_id=man_id)
                INNER JOIN urbanizacion ON (man_urb_id=urb_id)
                INNER JOIN sms_urbanizacion ON (urb_id=sur_urb_id)
                INNER JOIN sms ON (sur_sms_id=sms_id)
                WHERE sms_id = '$_GET[id]' and int_id > $ultimo
                GROUP BY int_id
                ORDER BY int_id                
                LIMIT 20";
//        echo $sql;

            $conec->ejecutar($sql);

            $num = $conec->get_num_registros();

            if ($num > 0) {

                $fecha_inicio = date("Y-m-d");
                $hora_inicio = date("H:i:s");
                $actualizador = new ADO();
                $enviados = 0;

                for ($i = 0; $i < $num; $i++) {

                    $interno = $conec->get_objeto();
                    $cliente = $interno->cliente;
                    $celular = $interno->int_celular;

                    if ($this->entra()) {

                        $enviados++;
                        $ultimo = $interno->int_id;
                        echo "Enviando: '$sms'; al cliente: $cliente con celular: $celular.<br/>";
                        $sql2 = "insert into sms_enviado(sen_sms_id,sen_int_id)values('$_GET[id]','{$interno->int_id}')";
                        $actualizador->ejecutar($sql2);
                    }

                    $conec->siguiente();
                }

                echo "<p style='color:blue'>Total de enviados: $enviados mensajes de texto.";
                $fecha_fin = date("Y-m-d");
                $hora_fin = date("H:i:s");


//                $sql = "update sms set sms_num_enviado = '$num',sms_fecha_ini='$fecha_inicio',
//                    sms_hora_ini='$hora_inicio',sms_fecha_fin='$fecha_fin',sms_hora_fin='$hora_fin',sms_ultimo=sms_ultimo + $num where sms_id = '$_GET[id]'";

                $sql = "update sms set sms_num_enviado = sms_num_enviado + $enviados,sms_fecha_ini='$fecha_inicio',
                    sms_hora_ini='$hora_inicio',sms_fecha_fin='$fecha_fin',sms_hora_fin='$hora_fin',sms_ultimo='$ultimo' where sms_id = '$_GET[id]'";

                $conec->ejecutar($sql);
//            echo "<p style='color:red'>$sql";
                ?>
                <table width="50%" cellpadding="0" cellspacing="1" style="border:none">
                    <tr>
                        <td align="center" height="150">
                            <input type="button" value="Volver" class="boton" onclick="javascript:location.href = '<?php echo $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER'; ?>';">
                        </td>
                    </tr>
                </table>
                <?php
            }
        }
    }

    function formulario_confirmar_pausar() {

        $mensaje = 'Esta seguro de pausar el envio del Boletín?';

        $this->formulario->ventana_confirmacion($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=PAUSAR', 'bol_id');
    }

    function pausar() {
        $conec = new ADO();

        $sql = "select bol_estado from boletin where bol_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        if ($objeto->bol_estado == 'INICIADO' && $objeto->bol_estado <> 'TERMINADO') {
            $sql = "update boletin set 
						bol_estado='PAUSADO'
						where bol_id = '" . $_GET['id'] . "'";

            $conec->ejecutar($sql);

            $this->dibujar_busqueda();
        } else {
            $mensaje = 'El envío no puede ser PAUSADO por que aun no ha sido INICIADO, o ya fue TERMINADO!!!';

            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
        }
    }

    function numero_reptores($bol_id) {
        $conec = new ADO();

        $sql = "SELECT distinct lei_correo from leidos where lei_bol_id='$bol_id'";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        return $num;
    }

    function numero_leido($bol_id) {
        $conec = new ADO();

        $sql = "SELECT count(lei_bol_id) as numero from leidos as numero where lei_bol_id='$bol_id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->numero;
    }

    function numero_envios($bol_id) {
        $conec = new ADO();

        $sql = "SELECT bol_num_enviado as numero from boletin where bol_id='$bol_id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->numero;
    }

    function entra() {
        $tiempo = time();

        echo "<p style='color:green'>$tiempo</p>";

        if ($tiempo % 3 != 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function leidos() {
        $conec = new ADO();

        $consulta = "SELECT count(0) as envios,CONCAT(int_nombre,' ',int_apellido) AS cliente,int_celular,int_id
                    FROM sms_enviado
                    INNER JOIN interno ON(int_id=sen_int_id)
                    INNER JOIN sms ON(sms_id=sen_sms_id)
                    WHERE sms_id = $_GET[id] group by int_id";

        $conec->ejecutar($consulta);

        $num = $conec->get_num_registros();

        if ($num > 0) {
            $this->formulario->dibujar_cabecera();
            ?>
            <center><BR><table align="left" border="0">
                    <tr>
                        <td>
                            <img src="images/back.png" align="left" width="" border="0"  title="VOLVER" onclick="javascript:location.href = '<?php echo $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER'; ?>';">
                        </td>
                    </tr>
                </table>
                <table width="60%" cellpadding="5" cellspacing="0"">
                    <tr >
                        <td align="center" >
                            <b>LISTA DE CLIENTES A LOS QUE SE ENVIÓ EL SMS</b>
                        </td>
                    </tr>
                </table>

                <table class="tablaLista" style="width:50%"  cellpadding="0" cellspacing="0" width="60%">

                    <thead>
                        <tr>
                            <th >
                                Nro
                            </th>
                            <th >
                                Cliente
                            </th>
                            <th >
                                Celular
                            </th>
                            <th >
                                Envios
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tot = 0;
                        for ($i = 0; $i < $num; $i++) {
                            $objeto = $conec->get_objeto();
                            ?>
                            <tr >
                                <td align="left">
                                    <?php echo $i + 1; ?>
                                </td>
                                <td align="left">
                                    <?php echo $objeto->cliente . "({$objeto->int_id})"; ?>
                                </td>
                                <td align="left">
                                    <?php echo $objeto->int_celular; ?>&nbsp;
                                </td>
                                <td>
                                    <?php echo $objeto->envios; ?>&nbsp;
                                </td>

                            </tr>
                            <?php
                            $conec->siguiente();
                        }
                        ?>
            <!--                        <tr>
                        <td align="right">
                            &nbsp;
                        </td>
                        <td align="right">
                            &nbsp;
                        </td>
                        <td align="right">
                            &nbsp;
                        </td>
                        <td align="right">
                            TOTAL
                        </td>
                        <td align="center">
                        <?php echo $tot; ?>
                        </td>

                    </tr>-->

                    </tbody></table><BR><input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER'; ?>';"></center>
            <?php
        }
    }

    function enviar_sms() {

        if (trim($_POST['celular']) <> "") {

            $conec = new ADO();

            //$sql = "select bol_id,bol_asunto,bol_titulo,bol_texto,bol_texto_plano,bol_pie,bol_responder from boletin where bol_id='" . $_GET['id'] . "'";

            $sql = "select sms_texto from sms where sms_id='$_GET[id]'";

            $conec->ejecutar($sql);

            $num = $conec->get_num_registros();

            if ($num > 0) {

                $objeto = $conec->get_objeto();
                $sms = $objeto->sms_texto;
                //echo "Enviando: '$sms'; al n&uacute;mero: $_POST[celular].<br/>";
                $mensaje = "Enviando: '$sms'; al n&uacute;mero: $_POST[celular].<br/>";

                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
            } else {
                //echo '<center>El sms es invalido.</center>';
                $mensaje = "<center>El sms es invalido.</center>";
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
            }
            ?>
<!--            <table width="50%" cellpadding="0" cellspacing="1" style="border:none">
                <tr>
                    <td align="center" height="150">
                        <input type="button" value="Volver" class="boton" onclick="javascript:location.href = '<?php echo $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER'; ?>';">
                    </td>
                </tr>
            </table>-->
            <script type="text/javascript">
            function redireccionar(par1) {
                //                                alert(par1);
                location.href = '<?php echo $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER'; ?>';
            }
            //            setTimeout(redireccionar('un parametro'), "2000");
            //                            setTimeout(function(){
            //                                location.href = '<?php // echo $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER';      ?>';
            //                            },"2000");

            </script>
            <?php
//            $this->dibujar_busqueda();
        } else {
            $this->formulario->dibujar_tarea();
            ?>
            <script type="text/javascript">
                function ValidarNumero(e)
                {
                    evt = e ? e : event;
                    tcl = (window.Event) ? evt.which : evt.keyCode;
                    if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                    {
                        return false;
                    }
                    return true;
                }
            </script>
            <table width="100%" cellpadding="0" cellspacing="1" style="border:1px solid #CCCCCC;">
                <tr>
                    <td align="center" height="150">
                        <h4>Ingrese el n&uacute;mero de celular al que desea enviar el sms.</h4>
                        <form id="form_eliminacion" action="gestor.php?mod=sms&tarea=ENVIAR_SMS&id=<?php echo $_GET['id']; ?>" method="POST" enctype="multipart/form-data">
                            <input type="text" value="" name="celular" id="celular" class="caja_texto" size="50" onkeypress="return ValidarNumero(event);"><br><br>
                            <input type="submit" value="Continuar" class="boton"> <input type="button" value="Volver" class="boton" onclick="javascript:location.href = '<?php echo $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER'; ?>';">
                        </form>
                    </td>
                </tr>
            </table>
            </div>
            <?php
        }
    }

    function obtener_datos(&$server, &$usu, &$pass, $boletin) {
        $conec = new ADO();

        $sql = "select 
		cue_servidor,cue_usu,cue_pass 
		from 
		boletin inner join cuenta on (bol_id='$boletin' and bol_cue_id=cue_id)
		";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $server = $objeto->cue_servidor;

        $usu = $objeto->cue_usu;

        $pass = $objeto->cue_pass;
    }

	function cargar_manzanos($sms_id) {
		
		 // $sql = "SELECT DISTINCT int_id, CONCAT(int_nombre,' ',int_apellido) AS cliente,int_celular
                // FROM interno
                // INNER JOIN venta ON (int_id=ven_int_id)
                // INNER JOIN lote ON (ven_lot_id=lot_id)
                // INNER JOIN uv ON (lot_uv_id=uv_id)
                // INNER JOIN manzano ON (lot_man_id=man_id)
                // INNER JOIN urbanizacion ON (man_urb_id=urb_id)
                // INNER JOIN sms_urbanizacion ON (urb_id=sur_urb_id)
                // INNER JOIN sms ON (sur_sms_id=sms_id)
                // WHERE sms_id = '$_GET[id]' and int_id > $ultimo
                // GROUP BY int_id
                // ORDER BY int_id                
                // LIMIT 20";
		
        $conec = new ADO();
        $sql = "SELECT sma_id,urb_id,urb_nombre,uv_id,uv_nombre,man_id,man_nro
				FROM urbanizacion
				INNER JOIN sms_manzano ON(urb_id=sma_urb_id)
				INNER JOIN uv ON (sma_uv_id=uv_id)
				INNER JOIN manzano ON (sma_man_id=man_id)
				INNER JOIN sms ON (sms_id=sma_sms_id)
				WHERE sms_id = $sms_id";
        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
			
			$man = $objeto->urb_id."|".$objeto->uv_id."|".$objeto->man_id;
			
            ?>
				<tr>
					<td><?php echo $objeto->urb_nombre; ?></td>
					<td><?php echo $objeto->uv_nombre; ?></td>
					<td><input name="man[]" value="<?php echo $man; ?>" type="hidden">Manzano Nro: <?php echo $objeto->man_nro; ?></td>
					<td><center><img src="images/b_drop.png" onclick="javascript:remover_acceso_man(this);"></center></td>
				</tr>
            <?php
            $conec->siguiente();
        }
        ?>
        <script>
                document.frm_sentencia.nfilas.value =<?php echo $num; ?>;
                document.frm_sentencia.nfilasshadown.value =<?php echo $num; ?>;
        </script>
        <?php
    }
	
    function cargar_accesos_proyectos($sms_id) {
        $conec = new ADO();

        $sql = "SELECT urb_id,urb_nombre
				FROM urbanizacion
				INNER JOIN sms_urbanizacion ON(urb_id=sur_urb_id)
				INNER JOIN sms ON (sms_id=sur_sms_id)
				WHERE sms_id = $sms_id";
        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            ?>
            <tr>                
                <td>
                    <input name="acceso[]" type="hidden" value="<?php echo $objeto->urb_id; ?>"><?php echo $objeto->urb_nombre; ?>
                </td>
                
                <td>
                    <?php // if(!$_GET[id]){?>
                    <center><img src="images/b_drop.png" onclick="javascript:remover_acceso(this);"></center>
                    <?php // }?>
                </td>
                </tr>
            <?php
            $conec->siguiente();
        }
        ?>
        <script>
                document.frm_sentencia.nfilas.value =<?php echo $num; ?>;
                document.frm_sentencia.nfilasshadown.value =<?php echo $num; ?>;
        </script>
        <?php
    }

    function ver_mensajes_generados(){
        $conec = new ADO();
		
		$conversor = new convertir();

        $consulta = "select ban_cel,ban_contenido,ban_estado,int_nombre,int_apellido from bandeja inner join interno on (ban_int_id=int_id) where ban_men_id='$_GET[id]'";
        $conec->ejecutar($consulta);

        $num = $conec->get_num_registros();

        if ($num > 0) {
            $this->formulario->dibujar_cabecera();
            ?>
            <center><BR><table align="left" border="0">
                    <tr>
                        <td>
                            <img src="images/back.png" align="left" width="" border="0"  title="VOLVER" onclick="javascript:location.href = '<?php echo $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER'; ?>';">
                        </td>
                    </tr>
                </table>
                <table width="80%" cellpadding="5" cellspacing="0"">
                    <tr >
                        <td align="center" >
                            <b>LISTADO DE CLIENTES A LOS QUE SE ENVIÓ SMS MANUAL "<?php echo FUNCIONES::atributo_bd_sql("select sms_descripcion as campo from sms where sms_id='$_GET[id]'"); ?>"</b>
                        </td>
                    </tr>
                </table></br>

                <table class="tablaLista" style="width:70%"  cellpadding="0" cellspacing="0" width="60%">

                    <thead>
                        <tr>
                            
							<th >
                                Nro
                            </th>
							<th >
                                Cliente
                            </th>
							<th >
                                Celular
                            </th>
                            <th >
                                Mensaje
                            </th>
                            <th >
                                Estado
                            </th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tot = 0;
                        for ($i = 0; $i < $num; $i++) {
                            $objeto = $conec->get_objeto();
                            ?>
                            <tr >
                                <td align="left">
                                    <?php echo $i + 1; ?>
                                </td>
								<td align="left">
                                    <?php echo $objeto->int_nombre.' '.$objeto->int_apellido; ?>
                                </td>
                                <td align="left">
                                    <?php echo $objeto->ban_cel; ?>
                                </td>
                                <td align="left">
                                    <?php echo $objeto->ban_contenido; ?>
                                </td>
                                <td>
                                    <?php echo $objeto->ban_estado; ?>
                                </td>

                            </tr>
                            <?php
                            $conec->siguiente();
                        }
                        ?>
           

                    </tbody></table><BR><input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER'; ?>';"></center>
            <?php
        }
    }
    
    function cancelar_envio() {
        $conec = new ADO();
        
        $sql = "update sms set sms_estado='Cancelado' where sms_id='" . $_GET['id'] . "' ";
        $conec->ejecutar($sql);
		
        $sql = "delete from bandeja where ban_men_id='" . $_GET['id'] . "' and ban_estado='LISTO'";
        $conec->ejecutar($sql);
		
        $mensaje = 'Envio de SMS cancelado Correctamente!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
    }
}
?>