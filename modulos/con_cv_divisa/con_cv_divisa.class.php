<?php

class con_cv_divisa extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function con_cv_divisa() {  //permisos
        $this->ele_id = 191;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "cvd_tipo";
        $this->arreglo_campos[0]["texto"] = "Tipo";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 25;
        
        $this->arreglo_campos[1]["nombre"] = "cvd_fecha";
        $this->arreglo_campos[1]["texto"] = "Fecha";
        $this->arreglo_campos[1]["tipo"] = "fecha";
        $this->arreglo_campos[1]["tamanio"] = 25;

        $this->arreglo_campos[2]["nombre"] = "cvd_usu_cre";
        $this->arreglo_campos[2]["texto"] = "Usuario";
        $this->arreglo_campos[2]["tipo"] = "cadena";
        $this->arreglo_campos[2]["tamanio"] = 25;

        $this->link = 'gestor.php';

        $this->modulo = 'con_cv_divisa';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('COMPRA/VENTA DIVISA');

        $this->usu = new USUARIO;
    }

    function dibujar_busqueda() {
        ?>
        <script>
            function ejecutar_script(id,tarea){
                if(tarea==='ANULAR'){
                        var txt = 'Esta seguro de ANULAR la COMPRA/VENTA de divisa?';

                        $.prompt(txt,{ 
                            buttons:{Anular:true, Cancelar:false},
                            callback: function(v,m,f){
                                if(v){
                                    location.href='gestor.php?mod=con_cv_divisa&tarea='+tarea+'&id='+id;
                                }
                            }
                        });
                    
                }
                console.log(tarea);

            }
        </script>
        <?php
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

        if ($this->verificar_permisos('ANULAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ANULAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/anular.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ANULAR';
            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }
    }

    function dibujar_listado() {
        $sql = "select  
				*
			  from 
				con_cv_divisa, interno
                            where cvd_int_id=int_id ";
        $this->set_sql($sql, 'order by cvd_id desc');

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Tipo</th>		
            <th>Cliente</th>		
            <th>Fecha</th>		
            <th>Monto Origen</th>
            <th>Moneda Origen</th>
            <th>Monto Destino</th>
            <th>Moneda Destino</th>
            <th>TC</th>
            <th>Estado</th>
            <th>Usuario</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $color=array('Activo'=>'#019721','Anulado'=>'#ff0000');
        for ($i = 0; $i < $this->numero; $i++) {

            $objeto = $this->coneccion->get_objeto();

            echo '<tr>';
            echo "<td>";
            echo $objeto->cvd_tipo;
            echo "</td>";
            echo "<td>";
            echo "$objeto->int_nombre $objeto->int_apellido";
            echo "</td>";
            echo "<td>";
            echo FUNCIONES::get_fecha_latina($objeto->cvd_fecha);
            echo "</td>";
            echo "<td>";
            echo $objeto->cvd_monto_ori;
            echo "</td>";
            echo "<td>";
            echo $objeto->cvd_mon_ori == 2 ? 'Dolares' : '';
            echo "</td>";
            echo "<td>";
            echo $objeto->cvd_monto_des;
            echo "</td>";
            echo "<td>";
            echo $objeto->cvd_mon_des == 1 ? 'Bolivianos' : ($objeto->cvd_mon_des == 3 ? 'Reales' : '');
            echo "</td>";
            echo "<td>";
            echo $objeto->cvd_tc;
            echo "</td>";
            echo "<td id='estado-$objeto->cvd_id'>";
            echo "<span style='padding:0 2px;color:#fff;background-color:{$color[$objeto->cvd_estado]}'>$objeto->cvd_estado</span>";
            echo "</td>";
            echo "<td>";
            echo $objeto->cvd_usu_cre;
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->cvd_id);
            echo "</td>";
            echo "</tr>";
            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from con_cv_divisa 
				where ban_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['ban_id'] = $objeto->ban_id;

        $_POST['ban_nombre'] = $objeto->ban_nombre;
        $_POST['ban_descripcion'] = $objeto->ban_descripcion;
    }

    function datos() {
        if ($_POST) {
            return true;
        } else {
            return false;
        }
    }

    function formulario_tcp() {
        if($_GET[popup]=='1'){
            $popup="&popup=1";
        }
        $url = $this->link . '?mod=' . $this->modulo . "&tarea=AGREGAR$popup";
        $red = $url;
        $this->formulario->dibujar_tarea('USUARIO');
        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <!--AutoSuggest-->
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        <!--AutoSuggest-->
        <!--FancyBox-->
        <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <!--FancyBox-->
        <script>
            function reset_interno() {
                document.frm_sentencia.int_id.value = "";
                document.frm_sentencia.nombre_persona.value = "";
            }
        </script>

        <div class="aTabsCont">
            <div class="aTabsCent">
                <ul class="aTabs">
                    <li ><a class="activo" href="javascript:void(0);" data-tab="Compra">Compra</a></li>
                    <li ><a href="javascript:void(0);" data-tab="Venta">Venta</a></li>
                </ul>
            </div>
        </div>
        <?php
        $usuario=  FUNCIONES::objeto_bd_sql("select * from ad_usuario where usu_id='$_SESSION[id]'");
        $base=$usuario->usu_suc_id!=5?1:3;
        
        ?>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                <input type="hidden" id="tipo" name="tipo" value="Compra">
                <input type="hidden" id="base" name="base" value="<?php echo $base; ?>">
                <input type="hidden" id="dtipo" value="<?php echo $_GET[dtipo]; ?>">

                <div id="FormSent" class="base_bs" style="width: 100%">
                    <div class="Subtitulo" id="txt_titulo">Datos</div>
                    <div id="ContenedorSeleccion" >
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" style="width: 70px;"><span class="flechas1">*</span>Persona</div>
                            <div id="CajaInput">
                                <input name="res_int_id" id="res_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['ven_int_id']?>" size="2">
                                <input name="ven_int_id" id="ven_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['ven_int_id']?>" size="2">
                                <input name="int_nombre_persona" id="int_nombre_persona"  type="text" class="caja_texto"  value="<?php echo trim($_POST['int_nombre_persona'])?>" size="40" >
<!--                                <a class="group" style="float:left; margin:0 0 0 7px;float:right;"  href="sueltos/llamada.php?accion=agregar_persona">
                                    <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                </a>-->
                                <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_interno();">
                                    <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                </a>
                            </div>
                            <script>
                                var options1 = {
                                    script:"sueltos/suggest_persona_usuario.php?json=true&",
                                    varname:"input",
                                    minchars:1,
                                    timeout:10000,
                                    noresults:"No se encontro ninguna persona",
                                    json:true,
                                    callback: function (obj) { document.getElementById('ven_int_id').value = obj.id; }
                                        };
                                var as_json1 = new _bsn.AutoSuggest('int_nombre_persona', options1);
                            </script>
                         </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" style="width: 70px;"><span class="flechas1">* </span>fecha</div>
                            <div id="CajaInput">
                                <?php FORMULARIO::cmp_fecha('fecha'); ?>
                                <span id="lbl_periodo" ></span>
                                <div id="tca_cambios" hidden=""></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" style="width: 70px;"><span class="flechas1">* </span>Observacion</div>
                            <div id="CajaInput">
                                <textarea id="observacion" name="observacion"></textarea>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" style="width: 70px;"><span class="flechas1">* </span>T. de Cambio</div>
                            <div id="CajaInput">
                                <?php if ($base == 1) { ?>
                                    <input type="hidden" id="tca" name="tca" value="" size="10">
                                    <div class="read-input" id="txt_tca" style="min-width: 50px;">&nbsp;</div>
                                <?php } elseif ($base == 3) { ?>
                                    <input type="text" id="tca" name="tca" value="" size="10" autocomplete="off">
                                <?php } ?>
                            </div>
                        </div>
                        <div id="txt_texto_1vc" style="font-size: 16px; font-style: italic;text-align: left;margin: 10px 0">Monto a Comprar:</div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" style="width: 70px;"><span class="flechas1">* </span>Moneda</div>

                            <div id="CajaInput">
                                <input type="hidden" name="moneda" id="moneda" value="2">
                                <div class="read-input" style="min-width: 60px;">Dolares</div>
                            </div>
                            <div class="Etiqueta" style="width: 70px;"><span class="flechas1">* </span>Cuenta</div>
                            <div id="CajaInput">
                                <select id="cuenta" name="cuenta" style="min-width: 200px;">
                                </select>
                            </div>
                            <div class="Etiqueta" style="width: 70px;"><span class="flechas1">* </span>Monto</div>
                            <div id="CajaInput">
                                <input type="text"  id="monto" name="monto" value="" autocomplete="off" >
                            </div>
                        </div>
                        <div id="txt_texto_2vc" style="font-size: 16px; font-style: italic;text-align: left;margin: 10px 0">Pagar Con Monto:</div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" style="width: 70px;"><span class="flechas1">* </span>Moneda</div>
                            <div id="CajaInput">
                                <input type="hidden" name="moneda_pag" id="moneda_pag" value="<?php echo $base; ?>">
                                <div class="read-input" style="min-width: 60px"><?php echo $base == 1 ? 'Bolivianos' : ($base == 3 ? 'Reales' : ''); ?></div>

                            </div>
                            <div class="Etiqueta" style="width: 70px;"><span class="flechas1">* </span>Cuenta</div>
                            <div id="CajaInput">
                                <select id="cuenta_pag" name="cuenta_pag" style="min-width: 200px;">
                                </select>
                            </div>
                            <div class="Etiqueta" style="width: 70px;"><span class="flechas1">* </span>Monto</div>
                            <div id="CajaInput">
                                <input type="hidden"  id="monto_pag" name="monto_pag" value="" autocomplete="off" >
                                <div class="read-input" id="txt_monto_pag" style="min-width: 113px">&nbsp;</div>
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input type="button" class="boton" id="btn_guardar" name="" value="Guardar" >
                                <?php if($_GET[popup]=='1'){?>
                                    <input type="button" class="boton" name="" value="Cerrar" onclick="self.close();">
                                    
                                <?php }else{?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $this->link . '?mod=' . $this->modulo; ?>';">
                                <?php } ?>
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="cue_json" hidden="">
            <?php
            $list_act_disp = FUNCIONES::objetos_bd_sql("select con_cajero_detalle.* from con_cajero_detalle, con_cajero where cjadet_usu_id='$_SESSION[id]' and cja_estado='1' and cja_usu_id=cjadet_usu_id ");

            $ges_id = $_SESSION['ges_id'];
            $ljson = "[";
            for ($i = 0; $i < $list_act_disp->get_num_registros(); $i++) {
                $_det = $list_act_disp->get_objeto();
                $sql = "select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$_det->cjadet_cue_id'";
                $cuenta = FUNCIONES::objeto_bd_sql($sql);
                if ($i > 0) {
                    $ljson.=',';
                }
                $ljson.="{\"id\":\"$cuenta->cue_id\",\"descripcion\":\"$cuenta->cue_descripcion\",\"moneda\":\"$cuenta->cue_mon_id\"}";

                $list_act_disp->siguiente();
            }
            $ljson.="]";
            echo $ljson;
            ?>
        </div>
        <script>
            mask_decimal('#tca', null);
            $('#moneda_base').change(function() {
                var base = $(this).val() * 1;
                if (base === 1) {
                    $('.base_bs').show();
                    $('.base_real').hide();
                } else if (base === 3) {
                    $('.base_bs').hide();
                    $('.base_real').show();
                }
            });
            $('#moneda_base').trigger('change');
            $('#btn_guardar').click(function() {
                var cambios = $('#tca_cambios').text();
                var monto = $('#monto').val() * 1;
                var moneda = $('#moneda option:selected').val() * 1;
                var txt_error = '##';
                var moneda_pag = $('#moneda_pag option:selected').val() * 1;
                //                    var cuenta=$('#cuenta option:selected').val();
                var cuenta = $('#cuenta option:selected').val();
                var cuenta_pag = $('#cuenta_pag option:selected').val();
                var int_id=$('#ven_int_id').val()*1;
                if(!(int_id>0)){
                    $.prompt('Ingrese la Persona');
                    return;
                }
                if (cambios === '') {
                    $.prompt('La fecha no tiene tipo de cambios disponibles');
                    return;
                }
                if (!(monto > 0)) {
                    $.prompt('Ingrese un monto mayor a 0');
                    return;
                }
                if (moneda === 0 || moneda_pag === 0) {
                    $.prompt('Las monedas de Origen y Destino deben ser diferentes');
                    return;
                }
                if (cuenta === '' || cuenta_pag === '') {
                    $.prompt('Seleccione las cuentas Origen y Destino');
                    return;
                }
                document.frm_sentencia.submit();
            });
            $('.aTabs li a').click(function() {
                var tab = $(this).attr('data-tab');
                $('#tipo').val(tab);
                $('#txt_titulo').text('Datos ' + tab);
                $('.aTabs li a').removeClass('activo');
                $(this).addClass('activo');
                if (tab === 'Compra') {
                    $('#txt_texto_1vc').text('Monto a Comprar:');
                    $('#txt_texto_2vc').text('Pagar con Monto:');

                    set_tipo_cambio();
                } else if (tab === 'Venta') {
                    $('#txt_texto_1vc').text('Monto a Vender:');
                    $('#txt_texto_2vc').text('Cobrar con Monto:');
                    set_tipo_cambio();
                }
                calcular_monto_pagar();
            });

            $('#moneda').change(function() {
                var moneda = $(this).val() * 1;
                var txt_json = trim($('#cue_json').text());
                var lista = JSON.parse(txt_json);
                $('#cuenta').find('option').remove();
                var option = '';
                if (lista.length > 0) {
                    option += '<option value=""></option>';
                }
                for (var i = 0; i < lista.length; i++) {
                    var obj = lista[i];
                    var mon = obj.moneda * 1;
                    if (moneda === mon) {
                        option += '<option value="' + obj.id + '">' + obj.descripcion + '</option>';
                    }
                }
                $('#cuenta').append(option);
                calcular_monto_pagar();
            });
            $('#moneda').trigger('change');
            $('#moneda_pag').change(function() {
                var moneda = $(this).val() * 1;
                var txt_json = trim($('#cue_json').text());
                var lista = JSON.parse(txt_json);
                $('#cuenta_pag').find('option').remove();
                var option = '';
                if (lista.length > 0) {
                    option += '<option value=""></option>';
                }
                for (var i = 0; i < lista.length; i++) {
                    var obj = lista[i];
                    var mon = obj.moneda * 1;
                    if (moneda === mon) {
                        option += '<option value="' + obj.id + '">' + obj.descripcion + '</option>';
                    }
                }
                $('#cuenta_pag').append(option);
                calcular_monto_pagar();
            });
            $('#moneda_pag').trigger('change');

            mask_decimal('#monto');
            $('#fecha').mask('99/99/9999');
            var fecha_sel = '';
            var mon_select = 0;
            $('#tca').focusout(function() {
                calcular_monto_pagar();
            });
            function set_tipo_cambio() {
                var base = $('#base').val();
                if (base === '1') {
                    var txt_cambios = trim($('#tca_cambios').text());
                    if (txt_cambios === '') {
                        $("#tca").val('');
                        $("#txt_tca").text('##');
                        return;
                    }
                    var cambios = JSON.parse(txt_cambios);
                    //                        console.log(cambios);
                    var tca = '';
                    var tipo = $('#tipo').val();
                    for (var i = 0; i < cambios.length; i++) {
                        if (cambios[i].id * 1 === 2) {
                            if (tipo === 'Compra') {
                                tca = cambios[i].val_c;
                            } else if (tipo === 'Venta') {
                                tca = cambios[i].val_v;
                            }
                        }
                    }


                    $("#tca").val(tca);
                    $("#txt_tca").text(tca);
                } else if (base === '3') {
                    $("#tca").val("");

                }
            }

            function obtener_periodo() {
                var fecha = $('#fecha').val();
                if (fecha !== fecha_sel) {
                    mostrar_ajax_load();
                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                        ocultar_ajax_load();
                        var dato = JSON.parse(respuesta);
                        if (dato.response === "ok") {
                            $('#peri_id').val(dato.id);
                            $('#tca_cambios').text(JSON.stringify(dato.cambios));
                            $('#lbl_periodo').text(dato.descripcion);
                            $('#lbl_periodo').css('color', '#0072b0');
                            set_tipo_cambio();
                        } else if (dato.response === "error") {
                            $('#peri_id').val("");
                            $('#tca_cambios').text('');
                            $('#lbl_periodo').text(dato.mensaje);
                            $('#lbl_periodo').css('color', '#ff0000');
                            $("#monto_pag").val("##");
                            set_tipo_cambio();
                            mon_select = 0;
                        }
                        fecha_sel = fecha;
                        calcular_monto_pagar();
                    });
                }
            }
            function calcular_monto_pagar() {
                var cambios = trim($('#tca_cambios').text());
                var monto = $('#monto').val() * 1;
                var moneda = $('#moneda option:selected').val() * 1;
                var txt_error = '##';
                var moneda_pag = $('#moneda_pag option:selected').val() * 1;
                //                    var cuenta=$('#cuenta option:selected').val();

                if (cambios === '') {
                    //                        $.prompt('La fecha no tiene tipo de cambios disponibles');
                    $('#monto_pag').val('');
                    $('#txt_monto_pag').text(txt_error);
                    return;
                }
                if (!(monto > 0)) {
                    $('#monto_pag').val('');
                    $('#txt_monto_pag').text(txt_error);
                    return;
                }
                if (moneda === 0 || moneda_pag === 0) {
                    $('#monto_pag').val('');
                    $('#txt_monto_pag').text(txt_error);
                    return;
                }
                if (moneda === moneda_pag) {
                    $('#monto_pag').val('');
                    $('#txt_monto_pag').text(txt_error);
                    return;
                }

                var tipo = $('#tipo').val();
                //                    console.log(monto+','+moneda+','+moneda_pag);
                //                    var monto_conv=convertir_monto(monto,moneda,moneda_pag,jcambios,tipo);
                var tca = $('#tca').val();
                var monto_conv = monto * tca;
                $('#monto_pag').val(monto_conv.toFixed(2));
                $('#txt_monto_pag').text(monto_conv.toFixed(2));
            }

            $('#monto').focusout(function() {
                calcular_monto_pagar();
            });

            var dtipo=$('#dtipo').val();
            if(dtipo==='v'){
                $('.aTabs li a').eq(1).trigger('click');
            }else{
                $('.aTabs li a').eq(0).trigger('click');
            }
            
            
            $('#fecha').focusout(function() {
                obtener_periodo();
            });
            obtener_periodo();
        </script>


        <?php
    }

    function emergente() {
        $this->formulario->dibujar_cabecera();
        $valor = trim($_POST['valor']);
        ?>

        <script>
            function poner(id, valor)
            {
                opener.document.frm_con_cv_divisa.ban_int_id.value = id;
                opener.document.frm_con_cv_divisa.ban_nombre_persona.value = valor;
                window.close();
            }
        </script>
        <br><center><form name="form" id="form" method="POST" action="gestor.php?mod=con_cv_divisa&tarea=AGREGAR&acc=Emergente">
                <table align="center">
                    <tr>
                        <td class="txt_contenido" colspan="2" align="center">
                            <input name="valor" type="text" class="caja_texto" size="30" value="<?php echo $valor; ?>">
                            <input name="Submit" type="submit" class="boton" value="Buscar">
                        </td>
                    </tr>
                </table>
            </form><center>
                <?php
                $conec = new ADO();

                if ($valor <> "") {
                    $sql = "select int_id,int_nombre,int_apellido from interno where int_nombre like '%$valor%' or int_apellido like '%$valor%'";
                } else {
                    $sql = "select int_id,int_nombre,int_apellido from interno";
                }

                $conec->ejecutar($sql);

                $num = $conec->get_num_registros();

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

                for ($i = 0; $i < $num; $i++) {
                    $objeto = $conec->get_objeto();

                    echo '<tr>
						 <td>' . $objeto->int_nombre . '</td>
						 <td>' . $objeto->int_apellido . '</td>
						 <td><a href="javascript:poner(' . "'" . $objeto->int_id . "'" . ',' . "'" . $objeto->int_nombre . ' ' . $objeto->int_apellido . "'" . ');"><center><img src="images/select.png" border="0" width="20px" height="20px"></center></a></td>
					   </tr>	 
				';

                    $conec->siguiente();
                }
                ?>
                </tbody></table>
                <?php
            }

            function insertar_tcp() {
                $conec = new ADO();
                $fecha = FUNCIONES::get_fecha_mysql($_POST[fecha]);
                $fecha_cre = date('Y-m-d H:i:s');
                $sql = "insert into con_cv_divisa (
                        cvd_tipo,cvd_int_id,cvd_fecha,cvd_monto_ori,cvd_mon_ori,cvd_cue_ori,cvd_monto_des,cvd_mon_des,cvd_cue_des,
                        cvd_tc,cvd_estado,cvd_fecha_cre,cvd_usu_cre,cvd_observacion
                    )values (
                        '$_POST[tipo]',$_POST[ven_int_id],'$fecha','$_POST[monto]','$_POST[moneda]','$_POST[cuenta]','$_POST[monto_pag]','$_POST[moneda_pag]','$_POST[cuenta_pag]',
                        '$_POST[tca]','Activo','$fecha_cre','$_SESSION[id]','$_POST[observacion]'
                    )";
//            echo "<pre>";
//            print_r($_POST);
//            echo "</pre>";
//            echo $sql.';<br>';
                $conec->ejecutar($sql,false);
                $llave= mysql_insert_id();
                $interno=  FUNCIONES::interno_nombre($_POST[ven_int_id]);
                include_once 'clases/modelo_comprobantes.class.php';
                include_once 'clases/registrar_comprobantes.class.php';
                $txt_pag_cob=$_POST[tipo]=='Venta'?'Cobrado':'Pagado';
                $txt_moneda_pag=$_POST[moneda_pag]==1?'Bolivianos':'Reales';
                $glosa="$_POST[tipo] de divisa $txt_pag_cob con $txt_moneda_pag - $interno";
                
                $cambios=  FUNCIONES::get_cambios($fecha);
                $tc_of=1;
                for ($i = 0; $i < count($cambios); $i++) {
                    $cambio = $cambios[$i];
                    if ($cambio[id] * 1==2) {
                        $tc_of = $cambio[val];
                    }
                }
                
                if($_POST[moneda_pag]==1){
                    $monto_ori=$_POST[monto]*$tc_of;
                    $monto_des=$_POST[monto_pag];
                }if($_POST[moneda_pag]==3){
                    $monto_ori=$_POST[monto]*$tc_of;
                    $monto_des=$monto_ori;
                }
                $dif=$monto_ori-$monto_des;
                $tcambios=null;
                
                $usuario=  FUNCIONES::objeto_bd_sql("select * from ad_usuario where usu_id='$_SESSION[id]'");
                if($usuario->usu_suc_id==5){/// es de Brazil
                    $tca_real=$_POST[tca];
                    $tca_usd=  FUNCIONES::objeto_bd_sql("select * from con_tipo_cambio where tca_fecha='$fecha' and tca_mon_id=2");
                    $tca_br=$tca_usd->tca_valor/$tca_real;
                    $tcambios=array('3'=>$tca_br);
                }

                $data=array(
                    'moneda'=>1,
                    'ges_id'=>$_SESSION[ges_id],
                    'fecha'=>$fecha,
                    'glosa'=>$glosa,
                    'interno'=>$interno,
                    'tabla_id'=>$llave,

                    'suc_id'=>$usuario->usu_suc_id,
                    'tcambios'=>$tcambios,

                    'tipo'=>$_POST[tipo],
                    'cuenta_ori'=>$_POST[cuenta],
                    'monto_ori'=>$monto_ori,
                    'cuenta_des'=>$_POST[cuenta_pag],
                    'monto_des'=>$monto_des,
                    'dif'=>$dif,
                    
                );

                $comprobante = MODELO_COMPROBANTE::cv_divisa($data);
                COMPROBANTES::registrar_comprobante($comprobante);
                
                $mensaje = 'Compra/Venta de divisa Agregado Correctamente';
                
                if($_GET[popup]=='1'){
                    
                    ?>
                    <div class="ancho100">
                        <div class="msInformacion limpiar"><?php echo $mensaje?></div>
                    </div>
                    <div class="ancho100">
                        <input class="boton" type="button" style="clear:both;" value="Cerrar" onclick="self.close();">
                    </div>
                    <br>
                <?php }else{
                    $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
                }
                
            }

            function formulario_confirmar_anulacion() {
                $mensaje = 'Esta seguro de eliminar la Compra/Venta de divisa?';
                $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'cvd_id');
            }

            function anular() {
                include_once 'clases/registrar_comprobantes.class.php';
                $bool=COMPROBANTES::anular_comprobante('cv_divisa', $_GET[id]);
                if(!$bool){
                    $mensaje="El pago de la cuota no puede ser Anulada por que el periodo o la fecha en el que fue realizado el pago fue cerrado.";
                    $tipo='Error';			
                    $this->formulario->ventana_volver($mensaje,$this->link . '?mod=' . $this->modulo ,'',$tipo);
                    return;
                }
                $conec = new ADO();
                $sql = "update con_cv_divisa set cvd_estado='Anulado' where cvd_id='" . $_GET[id] . "'";
                $conec->ejecutar($sql);
                $mensaje = 'Compra/Venta de divisa Anulado Correctamente.';
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

        }
        ?>