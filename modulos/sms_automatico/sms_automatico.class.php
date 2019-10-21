<?php

class SMS_AUTOMATICO extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function SMS_AUTOMATICO() {
        //permisos
        $this->ele_id = 302;

        $this->busqueda();

        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 10;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "ban_fecha_cre";
        $this->arreglo_campos[0]["texto"] = "Fecha";
        $this->arreglo_campos[0]["tipo"] = "fecha";
        $this->arreglo_campos[0]["tamanio"] = 40;

        


        $this->link = 'gestor.php';

        $this->modulo = 'sms_automatico';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('SMS AUTOMATICO');

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

        if ($this->verificar_permisos('ENVIAR_SMS_AUTOMATICO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ENVIAR_SMS_AUTOMATICO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/mail_send.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ENVIAR A UN CELULAR';
            $nun++;
        }

        if ($this->verificar_permisos('INICIAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'INICIAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/play.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'INICIAR';
            $this->arreglo_opciones[$nun]["script"] = 'ok';
            $nun++;
        }

//		if($this->verificar_permisos('PAUSAR'))
//		{
//			$this->arreglo_opciones[$nun]["tarea"]='PAUSAR';
//			$this->arreglo_opciones[$nun]["imagen"]='images/pause.png';
//			$this->arreglo_opciones[$nun]["nombre"]='PAUSAR';
//			$this->arreglo_opciones[$nun]["script"]='ok';
//			$nun++;
//		}
//		
//		if($this->verificar_permisos('VISTA_PREVIA'))
//		{
//			$this->arreglo_opciones[$nun]["tarea"]='VISTA_PREVIA';
//			$this->arreglo_opciones[$nun]["imagen"]='images/preview.png';
//			$this->arreglo_opciones[$nun]["nombre"]='VISTA PREVIA';
//			$this->arreglo_opciones[$nun]["link"]='sueltos/detalle.php';
//			$nun++;
//		}

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
                        //txt = 'Esta seguro de ELIMINAR el sms_automatico?';
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
//                                location.href = 'gestor.php?mod=sms_automatico&tarea=' + tarea + '&id=' + id;
//                            }
//
//                        }
//
//                    }
//                });


            }

            function eliminar(id) {
                $.prompt('Esta seguro de ELIMINAR el sms_automatico?', {
                    buttons: {Si: true, No: false},
                    callback: function(v, m, f) {

                        if (v) {

                            location.href = 'gestor.php?mod=sms_automatico&tarea=ELIMINAR&id=' + id;
                        }

                    }
                });
            }

            function iniciar_envio(id) {
                $.post("datos_sms_automatico.php", {sms_automatico_id: id}, function(respuesta) {
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
                                location.href = 'gestor.php?mod=sms_automatico&tarea=INICIAR&id=' + id + '&reenviar=' + f.reenvio;
                            }

                        }
                    });
                });
            }
        </script>
        <?php
        $sql = "select distinct ban_fecha_cre from bandeja where ban_men_id=0 ";

        
        $this->set_sql($sql, " order by ban_fecha_cre desc ");

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
            <th >Fecha</th>
            <th >Destinatarios</th>
            <th >Listo</th>
            <th >Despachado</th>
            <th >Enviado</th>
			<th  class="tOpciones" width="200px">
                Opciones
            </th>	
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();

        for ($i = 0; $i < $this->numero; $i++) {

            $objeto = $this->coneccion->get_objeto();

            echo '<tr class="busqueda_campos">';

            echo "<td>";
            echo FUNCIONES::get_fecha_latina($objeto->ban_fecha_cre);
            echo "</td>";

            

            echo "<td>";
                echo FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_men_id='$objeto->sms_id' and ban_fecha_cre='$objeto->ban_fecha_cre'");
            echo "</td>";
            echo "<td>";
                echo FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_estado='LISTO' and ban_men_id='$objeto->sms_id' and ban_fecha_cre='$objeto->ban_fecha_cre'");
            echo "</td>";
            echo "<td>";
                echo FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_estado='DESPACHADO' and ban_men_id='$objeto->sms_id' and ban_fecha_cre='$objeto->ban_fecha_cre'");
            echo "</td>";
            echo "<td>";
                echo FUNCIONES::atributo_bd_sql("select count(*)  as campo from bandeja where ban_estado='ENVIADO' and ban_men_id='$objeto->sms_id' and ban_fecha_cre='$objeto->ban_fecha_cre'");
            echo "</td>";
			echo '<td width="80" >';
            echo $this->get_opciones($objeto->ban_fecha_cre);
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

        $sql = "select * from sms_automatico
				where sms_automatico_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['sms_automatico_descripcion'] = $objeto->sms_automatico_descripcion;

        $_POST['sms_automatico_texto'] = $objeto->sms_automatico_texto;

        $_POST['sms_automatico_id'] = $objeto->sms_automatico_id;
    }

    function formulario_tcp($tipo) {
        include_once("js/fckeditor/fckeditor.php");

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

        <script>
            function reset_interno()
            {
                document.frm_sentencia.usu_per_id.value = "";
                document.frm_sentencia.usu_nombre_persona.value = "";
            }
            function enviar_formulario() {

                var persona = document.frm_sentencia.usu_per_id.value;
                var grupo = document.frm_sentencia.usu_gru_id.options[document.frm_sentencia.usu_gru_id.selectedIndex].value;
                var usuario = document.frm_sentencia.usu_id.value;
                var password = document.frm_sentencia.usu_password.value;
                var estado = document.frm_sentencia.usu_estado.options[document.frm_sentencia.usu_estado.selectedIndex].value;




                if (persona != '' && grupo != '' && usuario != '' && password != '' && estado != '')
                {

                    document.frm_sentencia.submit();
                }
                else
                {
                    $.prompt('Para registrar el usuario debe ingresar Persona , Grupo , Usuario , Contrasena y Estado', {opacity: 0.8});
                }

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
                    $.prompt('- Seleccione la urbanizacion a la que enviará los sms_automatico.', {opacity: 0.8});
                }
            }

            function datos_fila_cc() {
                var cco_id = $('[name="cco_id"] option:selected').val();
                var cco_text = $('[name="cco_id"] option:selected').text();

                if (cco_id != '') {

                    if (verificar_centrocostos(cco_id)) {
                        var filas = parseFloat(document.frm_sentencia.nfilascc.value);

                        document.frm_sentencia.nfilascc.value = filas + 1;

                        if (document.frm_sentencia.nfilascc.value > 0) {
                            document.frm_sentencia.nfilasshadowncc.value = 1;
                        }

                        addTableRow('#tpruebacc', "<tr><td><input name='acceso_cc[]' type='hidden' value='" + cco_id + "'>" + cco_text +
                                "</td><td><center><img src='images/b_drop.png' onclick='javascript:remover_acceso_centrocostos(this);'></center></td></tr>");

                        limpiar_campos_cc();
                    } else {
                        $.prompt('El item seleccionado ya fue agregado a la Lista', {opacity: 0.8});
                    }
                } else {
                    $.prompt('- Seleccione el Centro de costo que dará acceso al usuario.', {opacity: 0.8});
                }
            }

            function limpiar_campos() {
                document.frm_sentencia.proy_id.value = '';
            }

            //            function limpiar_campos_cc() {
            //                document.frm_sentencia.cco_id.value = '';
            //            }

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

            //            function remover_acceso_centrocostos(row) {
            //                var filas = parseFloat(document.frm_sentencia.nfilascc.value);
            //
            //                document.frm_sentencia.nfilascc.value = filas - 1;
            //
            //                if (document.frm_sentencia.nfilascc.value == 0) {
            //                    document.frm_sentencia.nfilasshadowncc.value = '';
            //                }
            //
            //                var cant = $(row).parent().parent().parent().children().length;
            //
            //                if (cant > 1)
            //                    $(row).parent().parent().parent().remove();
            //
            //            }

            function addTableRow(id, valor) {
                $(id).append(valor);
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

            //            function verificar_centrocostos(id) {
            //                var cant = $('#tpruebacc tbody').children().length;
            //                var ban = true;
            //                if (cant > 0) {
            //                    $('#tpruebacc tbody').children().each(function() {
            //                        var dato = $(this).eq(0).children().eq(0).children().eq(0).attr('value');
            //
            //                        if (id == dato) {
            //                            ban = false;
            //                            console.log('pasando por falso');
            //                        }
            //
            //                    });
            //                }
            //                return ban;
            //            }
        </script>

        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">


                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">       

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Descripcion</div>
                            <div id="CajaInput">                                
                                <input <?php echo $_GET[id]?'readonly=""':'' ;?> name="sms_automatico_descripcion" size="45" id="sms_automatico_descripcion" value="<?php echo $_POST['sms_automatico_descripcion']; ?>"/>
                            </div>
                        </div>

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
                                    <?php if(!$_GET[id]){?>
                                    <img src="images/boton_agregar.png" style='margin:0px 0px 0px 10px' onclick="javascript:datos_fila();" onmouseout="javascript:imprimir_texto();">
                                    <?php }?>
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

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Mensaje</div>
                            <div id="CajaInput">
                                <!--<input  name="texto" maxlength="140" type="text" id="texto" size="53"  value="<?php echo $_POST['texto']; ?>"/>-->
                                <textarea  name="sms_automatico_texto" rows="3" cols="45" id="sms_automatico_texto" maxlength="140" ><?php echo $_POST['sms_automatico_texto']; ?></textarea><br/>
                                <p style="color: blue">Cantidad Escrita: <b id="text_cant_escrito"><?php echo strlen($_POST[sms_automatico_texto]);?></b></p>
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
                                    <input type="submit" class="boton" name="" value="Guardar">
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
            $('#sms_automatico_texto').keyup(function(){                
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
        $sql = "insert into sms_automatico(sms_automatico_descripcion,sms_automatico_texto,sms_automatico_usu_id) 
                            values ('" . $_POST['sms_automatico_descripcion'] . "','" . $_POST['sms_automatico_texto'] . "','" . $this->usu->get_id() . "')";
        $conec->ejecutar($sql, false);
        $ult = mysql_insert_id();
        $a_tecno = $_POST['acceso'];
        foreach ($a_tecno as $tec) {
            $sql = "insert into sms_automatico_urbanizacion(
                        sur_sms_automatico_id,sur_urb_id
                    )values(
                        '$ult','$tec')
                    ";
            $conec->ejecutar($sql);
        }
        foreach ($a_tecno as $tec) {
            $sql="  select 
                        int_nombre,int_apellido, int_telefono, int_celular 
                    from 
                        interno, venta,lote, manzano 
                    where 
                        man_urb_id='$tec' and (int_telefono!='' or int_celular!='') and ven_int_id=int_id and ven_lot_id=lot_id and lot_man_id=man_id ";
            $lista_ventas=  FUNCIONES::objetos_bd_sql($sql);
            $sql_insert="insert into bandeja(ban_cel,ban_contenido,ban_men_id, ban_estado,ban_fecha_cre,ban_hora_cre)values";
            for ($i = 0; $i < $lista_ventas->get_num_registros(); $i++) {
                $cli=$lista_ventas->get_objeto();
                $celulares=$this->get_num_celular($cli->int_telefono , $cli->int_celular);
                $fecha=date('Y-m-d');
                $hora=date('H:i:s');
                foreach($celulares as $cel){
                    $sql=$sql_insert."('$cel','$_POST[sms_automatico_texto]','$ult','LISTO','$fecha','$hora')";
                    $conec->ejecutar($sql,false);
                }
                
                $lista_ventas->siguiente();
            }            
        }
        $mensaje = 'Sms Agregado Correctamente!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
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
        $sql = "update sms_automatico set 
                sms_automatico_texto='" . $_POST['sms_automatico_texto'] . "',
                sms_automatico_descripcion='" . $_POST['sms_automatico_descripcion'] . "'												
                where sms_automatico_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);
        $sql = "delete from sms_automatico_urbanizacion where sur_sms_automatico_id='" . $_GET['id'] . "'";
        $conec->ejecutar($sql);
        $a_tecno = $_POST['acceso'];
        foreach ($a_tecno as $tec) {
            $sql = "insert into sms_automatico_urbanizacion (sur_sms_automatico_id,sur_urb_id) values('" . $_GET['id'] . "','" . $tec . "')";
            $conec->ejecutar($sql);
        }
        $sql_update="update bandeja set ban_contenido='$_POST[sms_automatico_texto]' where ban_men_id='$_GET[id]'";
        $conec->ejecutar($sql_update);
        $mensaje = 'Sms Modificado Correctamente!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
    }

    function formulario_confirmar_eliminacion() {

        $mensaje = 'Esta seguro de eliminar el Sms?';

        $this->formulario->ventana_confirmacion($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ELIMINAR', 'sms_automatico_id');
    }

    function eliminar_tcp() {
        $conec = new ADO();

        //boletin
        $sql = "delete from sms_automatico where sms_automatico_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        //categorias
        $sql = "delete from sms_automatico_urbanizacion where sur_sms_automatico_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $mensaje = 'Sms Eliminado Correctamente!';

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
    }

    function formulario_confirmar_iniciar() {

        $mensaje = 'Esta seguro de iniciar el envio del Boletín?';

        $this->formulario->ventana_confirmacion($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=INICIAR', 'bol_id');
    }

    function iniciar() {

        $this->formulario->dibujar_tarea();
        $conec = new ADO();

//        $sql = "SELECT DISTINCT int_id, CONCAT(int_nombre,' ',int_apellido) AS cliente,int_celular,sms_automatico_texto
//                FROM interno
//                INNER JOIN venta ON (int_id=ven_int_id)
//                INNER JOIN lote ON (ven_lot_id=lot_id)
//                INNER JOIN uv ON (lot_uv_id=uv_id)
//                INNER JOIN manzano ON (lot_man_id=man_id)
//                INNER JOIN urbanizacion ON (man_urb_id=urb_id)
//                INNER JOIN sms_automatico_urbanizacion ON (urb_id=sur_urb_id)
//                INNER JOIN sms_automatico ON (sur_sms_automatico_id=sms_automatico_id)
//                WHERE sms_automatico_id = '$_GET[id]' and ven_estado in ('Pendiente','Pagado','Pendiente por Cobrar') and int_celular !=''
//                GROUP BY int_id
//                ORDER BY int_id";

        $sql = "select sms_automatico_ultimo,sms_automatico_texto from sms_automatico where sms_automatico_id='$_GET[id]'";
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();

        if ($num > 0) {

            $obj = $conec->get_objeto();
            
            $ultimo = $obj->sms_automatico_ultimo;
            
            if ($_GET['reenviar']) {
                if ($_GET['reenviar'] == 'si') {
                    $ultimo = 0;
                }
            }                        
            
            $sms_automatico = $obj->sms_automatico_texto;

//            $sql = "SELECT DISTINCT int_id, CONCAT(int_nombre,' ',int_apellido) AS cliente,int_celular
//                FROM interno
//                INNER JOIN venta ON (int_id=ven_int_id)
//                INNER JOIN lote ON (ven_lot_id=lot_id)
//                INNER JOIN uv ON (lot_uv_id=uv_id)
//                INNER JOIN manzano ON (lot_man_id=man_id)
//                INNER JOIN urbanizacion ON (man_urb_id=urb_id)
//                INNER JOIN sms_automatico_urbanizacion ON (urb_id=sur_urb_id)
//                INNER JOIN sms_automatico ON (sur_sms_automatico_id=sms_automatico_id)
//                WHERE sms_automatico_id = '$_GET[id]'
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
                INNER JOIN sms_automatico_urbanizacion ON (urb_id=sur_urb_id)
                INNER JOIN sms_automatico ON (sur_sms_automatico_id=sms_automatico_id)
                WHERE sms_automatico_id = '$_GET[id]' and int_id > $ultimo
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
                        echo "Enviando: '$sms_automatico'; al cliente: $cliente con celular: $celular.<br/>";
                        $sql2 = "insert into sms_automatico_enviado(sen_sms_automatico_id,sen_int_id)values('$_GET[id]','{$interno->int_id}')";
                        $actualizador->ejecutar($sql2);
                    }

                    $conec->siguiente();
                }

                echo "<p style='color:blue'>Total de enviados: $enviados mensajes de texto.";
                $fecha_fin = date("Y-m-d");
                $hora_fin = date("H:i:s");


//                $sql = "update sms_automatico set sms_automatico_num_enviado = '$num',sms_automatico_fecha_ini='$fecha_inicio',
//                    sms_automatico_hora_ini='$hora_inicio',sms_automatico_fecha_fin='$fecha_fin',sms_automatico_hora_fin='$hora_fin',sms_automatico_ultimo=sms_automatico_ultimo + $num where sms_automatico_id = '$_GET[id]'";

                $sql = "update sms_automatico set sms_automatico_num_enviado = sms_automatico_num_enviado + $enviados,sms_automatico_fecha_ini='$fecha_inicio',
                    sms_automatico_hora_ini='$hora_inicio',sms_automatico_fecha_fin='$fecha_fin',sms_automatico_hora_fin='$hora_fin',sms_automatico_ultimo='$ultimo' where sms_automatico_id = '$_GET[id]'";

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
                    FROM sms_automatico_enviado
                    INNER JOIN interno ON(int_id=sen_int_id)
                    INNER JOIN sms_automatico ON(sms_automatico_id=sen_sms_automatico_id)
                    WHERE sms_automatico_id = $_GET[id] group by int_id";

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
                            <b>LISTA DE CLIENTES A LOS QUE SE ENVIÓ EL SMS_AUTOMATICO</b>
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

    function enviar_sms_automatico() {

        if (trim($_POST['celular']) <> "") {

            $conec = new ADO();

            //$sql = "select bol_id,bol_asunto,bol_titulo,bol_texto,bol_texto_plano,bol_pie,bol_responder from boletin where bol_id='" . $_GET['id'] . "'";

            $sql = "select sms_automatico_texto from sms_automatico where sms_automatico_id='$_GET[id]'";

            $conec->ejecutar($sql);

            $num = $conec->get_num_registros();

            if ($num > 0) {

                $objeto = $conec->get_objeto();
                $sms_automatico = $objeto->sms_automatico_texto;
                //echo "Enviando: '$sms_automatico'; al n&uacute;mero: $_POST[celular].<br/>";
                $mensaje = "Enviando: '$sms_automatico'; al n&uacute;mero: $_POST[celular].<br/>";

                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
            } else {
                //echo '<center>El sms_automatico es invalido.</center>';
                $mensaje = "<center>El sms_automatico es invalido.</center>";
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
                        <h4>Ingrese el n&uacute;mero de celular al que desea enviar el sms_automatico.</h4>
                        <form id="form_eliminacion" action="gestor.php?mod=sms_automatico&tarea=ENVIAR_SMS_AUTOMATICO&id=<?php echo $_GET['id']; ?>" method="POST" enctype="multipart/form-data">
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

    function cargar_accesos_proyectos($sms_automatico_id) {
        $conec = new ADO();

        $sql = "SELECT urb_id,urb_nombre
FROM urbanizacion
INNER JOIN sms_automatico_urbanizacion ON(urb_id=sur_urb_id)
INNER JOIN sms_automatico ON (sms_automatico_id=sur_sms_automatico_id)
WHERE sms_automatico_id = $sms_automatico_id";

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
                    <?php if(!$_GET[id]){?>
                    <center><img src="images/b_drop.png" onclick="javascript:remover_acceso(this);"></center>
                    <?php }?>
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
	
	function ver_mensajes_generados()
	{
        $conec = new ADO();
		
		$conversor = new convertir();

        $consulta = "select ban_cel,ban_contenido,ban_estado,int_nombre,int_apellido from bandeja inner join interno on (ban_int_id=int_id) where ban_fecha_cre='".$_GET['id']."' and ban_men_id='0'";

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
                            <b>LISTADO DE CLIENTES A LOS QUE SE ENVIÓ SMS AUTOMATICO EL <?php echo FUNCIONES::get_fecha_latina($_GET['id']); ?></b>
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

}
?>