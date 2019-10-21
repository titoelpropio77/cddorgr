<?php

class UV_MULTINIVEL extends UV {

    function UV_MULTINIVEL() {
        parent::__construct();
    }

    function datos_configuracion_comisiones() {
        if ($_POST)
            return true;
        else
            return false;
    }

    function formulario_configuracion_comisiones() {
        if ($this->datos_configuracion_comisiones()) {
            if ($_GET['acc'] == 'actualizar_configuracion_comisiones')
                $this->actualizar_configuracion_comisiones();
        }

        $conec = new ADO();
        $sql = "select * from urbanizacion where urb_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();

        $comisiones = array();
        if (!empty($objeto->urb_porc_comisiones)) {
            $comisiones = json_decode($objeto->urb_porc_comisiones);
        }


        $url = $this->link . '?mod=' . $this->modulo;

        $this->formulario->dibujar_tarea();

        if ($this->mensaje != "") {
            $this->formulario->mensaje('Correcto', $this->mensaje);
        }
        ?>
        <!--AutoSuggest-->
        <script type="text/javascript" src="js/bsn.AutoSuggest_2.1.3.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        <!--AutoSuggest-->

        <style>
            .error{
                color: #ff0000;
                margin-left: 5px;
            }
            .tab_lista_cuentas{
                list-style: none;
                width: 100%;
                overflow:scroll ;
                background-color: #ededed;
                border-collapse: collapse;
                font-size: 12px;
            }
            .tab_lista_cuentas tr td{
                padding: 3px 3px;
            }
            .tab_lista_cuentas tr:hover{
                background-color: #f9e48c;
            }
            .img_del_cuenta{
                font-weight: bold;
                cursor: pointer;
                width: 12px;
            }
            .box_lista_cuenta{
                width:270px;height:170px;background-color:#F2F2F2;overflow:auto;
                border: 1px solid #8ec2ea;
            }
            .txt_rojo{
                color: #ff0000;
            }
            .Subtitulo:hover{
                cursor: pointer;
            }
            .tit_config{
                font-size: 12px;
                font-weight: bold;
                color: #3a3a3a;
                margin-top:5px;
                padding:5px 10px 5px 10px;
                background:#cdd0d5;
                cursor: pointer;
            }
            .tit_config:hover{
                background:#bdc1c6;

            }
        </style>




        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>&id=<?php echo $_GET['id']; ?>&tarea=MULTINIVEL&acc=actualizar_configuracion_comisiones" method="POST" enctype="multipart/form-data">
                <div id="FormSent">

                    <script>
                        $(".Subtitulo").live("click", function() {
                            var id = $(this).attr('data-id');
                            $(".cont_" + id).slideToggle();
                        });
                        $(".tit_config").live("click", function() {
                            var subtitulos = $(".Subtitulo");
                            var mode = $(this).attr("data-mode");
                            for (var i = 0; i < subtitulos.size(); i++) {
                                var id = $(subtitulos[i]).attr("data-id");
                                if (mode === 'collapse') {
                                    $(".cont_" + id).slideDown();
                                } else if (mode === 'expand') {
                                    $(".cont_" + id).slideUp();
                                }
                            }
                            if (mode === 'expand') {
                                $(this).attr("data-mode", "collapse");
                                $(this).text("Expandir Todo");
                            } else if (mode === 'collapse') {
                                $(this).attr("data-mode", "expand");
                                $(this).text("Colapsar Todo");
                            }
                        });
                    </script>

                    <div data-mode="expand" class="tit_config" >Colapasar Todo</div>

                    <div data-id="d_comision" class="Subtitulo">Comisiones por Niveles</div>
                    <div class="cont_d_comision" id="ContenedorSeleccion">
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >%Comisi&oacute;n Credito</div>
                            <div id="CajaInput">
                                <input name="comision_valor" id="comision_valor"  type="text" class="caja_texto" value="" size="25" onKeyPress="return ValidarNumero(event);">
                                <input name="comision_index" id="comision_index"  type="hidden" value="">

                                <!--                                %Comisión Contado
                                                                <input name="comision_valor_contado" id="comision_valor_contado"  type="text" class="caja_texto" value="" size="25" onKeyPress="return ValidarNumero(event);">
                                                                <br />-->

                                <input type="button" value="Guardar" class="boton" id="guardar-comision">
                                <input type="button" value="Cancelar" class="boton" id="cancelar-comision" hidden="">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >% Comisi&oacute;n a listar</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta">
                                    <table id="tab_cuentas_act_disp" class="tab_lista_cuentas">
                                        <?php
                                        $arr = $comisiones->array;
                                        $arr_contado = $comisiones_contado->array;
                                        for ($i = 0; $i < count($arr); $i++) {
                                            ?>
                                            <tr data-id="<?= $i ?>">
                                                <td  id="edit_comision" data-valor="<?php echo $arr[$i] ?>" data-valor-contado="<?php echo $arr_contado[$i] ?>" data-index="<?php echo $i ?>"><a href="javascript:void(0)"> Nivel <?php echo $i + 1 ?> </a></td>
                                                <td class="td_<? echo $i; ?>"><?php echo $arr[$i]; ?></td>

                                                <td width="8%">
                                                    <?php
                                                    if ((count($arr) - $i) == 1) {
                                                        ?>
                                                        <img class="img_eliminar_comision" src="images/retener.png" id="remove_comision" data-index="<?php echo $i ?>">
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                        <?php //endforeach;  ?>

                                    </table>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->
                    </div>

                    <script>
                        $('td#edit_comision').click(function() {
                            var valor = $(this).attr('data-valor');

                            var index = $(this).attr('data-index');

                            $('#comision_valor').val(valor);


                            $('#comision_index').val(index);
                            $('#guardar-comision').val('Actualizar');
                            $('#cancelar-comision').show();
                        });

                        $('#cancelar-comision').click(function() {
                            $('#comision_valor').val('');
                            $('#comision_index').val('');
                            $('#guardar-comision').val('Guardar');
                            $('#cancelar-comision').hide();
                        });

                        $('#guardar-comision').click(function() {
                            var valor = $('#comision_valor').val().trim();
                            var index = $('#comision_index').val().trim();
                            if (valor) {
                                if (index) {
                                    $('#frm_sentencia').append('<input type="hidden" value="editar" name="metodo">');
                                } else {
                                    $('#frm_sentencia').append('<input type="hidden" value="agregar" name="metodo">');
                                }
                                $('#frm_sentencia').append('<input type="hidden" value="comision" name="accion">');
                                $('#frm_sentencia').submit();
                            } else {
                                var txt = 'Debe ingresar valor para el % de comision.';
                                $.prompt(txt);
                            }
                        });

                        $('img#remove_comision').click(function() {
                            var index = $(this).attr('data-index');
                            console.log(index);
                            var txt = 'Esta seguro de eliminar la Comisi&oacute;n?';
                            $.prompt(txt, {
                                buttons: {Eliminar: true, Cancelar: false},
                                callback: function(v, m, f) {

                                    if (v) {
                                        $('#comision_index').val(index);
                                        $('#frm_sentencia').append('<input type="hidden" value="comision" name="accion">');
                                        $('#frm_sentencia').append('<input type="hidden" value="eliminar" name="metodo">');
                                        $('#frm_sentencia').submit();

                                    }

                                }
                            });
                        });

                        function ValidarNumero(e) {
                            evt = e ? e : event;
                            tcl = (window.Event) ? evt.which : evt.keyCode;
                            if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46)) {
                                return false;
                            }
                            return true;
                        }

                    </script>

                    <div id="">
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <?php
                                    if (!($ver)) {
                                        ?>
                                                        <!--<input type="button" id="guardar-config" class="boton"  value="Guardar">-->
                        <!--                                        <input type="reset" class="boton" name="" value="Cancelar">
                                                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:history.back();">-->
                                        <?php
                                    } else {
                                        ?>
                                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:history.back();">
                                        <?php
                                    }
                                    ?>
                                </center>
                                <script>
                        $("#guardar-config").click(function() {
                            if ($('#dias_gracia option:selected').val() == '') {
                                $.prompt('Seleccione un periodo para el tiempo de gracia.', {opacity: 0.8});
                                return;
                            }
                            $('#frm_sentencia').append('<input type="hidden" value="tiempo_gracia" name="metodo">');
                            $('#frm_sentencia').append('<input type="hidden" value="comision" name="accion">');
                            $('#frm_sentencia').submit();
                        });

                        function implode_cuentas(input) {
                            var lista = $("#tab_" + input + " tr");
                            var data = "";
                            for (var i = 0; i < lista.size(); i++) {
                                var cuenta = lista[i];
                                var id = $(cuenta).attr("data-id");
                                if (i > 0) {
                                    data += "," + id;
                                } else {
                                    data += id;
                                }
                            }
                            $("#" + input).val(data);
                        }

                        setTimeout(function() {
                            $(".msCorrecto").slideUp(500);
                        }, 3000);
                                </script>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
        <?php
    }

    function actualizar_configuracion_comisiones() {
        $this->mensaje = "Los parametros de Configuracion de Comisiones fueron Actualizadas Correctamente.";

        $conec2 = new ADO();

        $conec = new ADO();

        $accion = $_POST['accion'];

        if ($accion == 'comision') {
            $metodo = $_POST['metodo'];

            if ($metodo == 'editar') {
                //******************* CREDITO *******************//
                $index = $_POST['comision_index'];
                $valor = $_POST['comision_valor'];

                $sql = "select urb_porc_comisiones from urbanizacion where urb_id='" . $_GET['id'] . "'";

                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();

                $comisiones = json_decode($objeto->urb_porc_comisiones);
                $nuevas_comision = array();

                foreach ($comisiones->array as $key => $comision) {
                    if ($key == $index) {
                        $nuevas_comision[$key] = $valor;
                    } else {
                        $nuevas_comision[$key] = $comision;
                    }
                }

                $dato = new stdClass();
                $dato->array = $nuevas_comision;

                $comision_string = json_encode($dato);

                $sql = "update urbanizacion set urb_porc_comisiones='" . $comision_string . "' where urb_id='" . $_GET['id'] . "'";
                $conec->ejecutar($sql);
                //******************* CREDITO *******************//
                //******************* INICIO - INSERTANDO CONFIGURACION CREDITO *******************//
                $sql = "select urb_porc_comisiones from urbanizacion where urb_id='" . $_GET['id'] . "'";
                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();

                $comisiones = json_decode($objeto->urb_porc_comisiones);


                //******************* FIN - INSERTANDO CONFIGURACION CREDITO *******************//
            }

            if ($metodo == 'agregar') {
                //******************* CREDITO *******************//
                $valor = $_POST['comision_valor'];

                $sql = "select urb_porc_comisiones from urbanizacion where urb_id='" . $_GET['id'] . "'";

                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();

                $comisiones = json_decode($objeto->urb_porc_comisiones);
                $comisiones_array = $comisiones->array;
                $comisiones_array[] = $valor;

                $dato = new stdClass();
                $dato->array = $comisiones_array;
                $comision_string = json_encode($dato);

                $sql = "update urbanizacion set urb_porc_comisiones='" . $comision_string . "' where urb_id='" . $_GET['id'] . "'";
                $conec->ejecutar($sql);
                //******************* CREDITO *******************//
                //******************* INICIO - INSERTANDO CONFIGURACION CREDITO *******************//
                $sql = "select urb_porc_comisiones from urbanizacion where urb_id='" . $_GET['id'] . "'";

                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();

                $comisiones = json_decode($objeto->urb_porc_comisiones);


                //******************* FIN - INSERTANDO CONFIGURACION CREDITO *******************//
            }



            if ($metodo == 'eliminar') {
                //******************* CREDITO *******************//
                $index = $_POST['comision_index'];

                $sql = "select urb_porc_comisiones from urbanizacion where urb_id='" . $_GET['id'] . "'";

                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();

                $comisiones = json_decode($objeto->urb_porc_comisiones);
                $nuevas_comision = array();
                $comisiones_array = $comisiones->array;
                unset($comisiones_array[$index]);

                $dato = new stdClass();
                $dato->array = $comisiones_array;
                $comision_string = json_encode($dato);

                $sql = "update urbanizacion set urb_porc_comisiones='" . $comision_string . "' where urb_id='" . $_GET['id'] . "'";
                $conec->ejecutar($sql);
                //******************* CREDITO *******************//
                //******************* INICIO - INSERTANDO CONFIGURACION CREDITO *******************//
                $sql = "select urb_porc_comisiones from urbanizacion where urb_id='" . $_GET['id'] . "'";

                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();


                $comisiones = json_decode($objeto->urb_porc_comisiones);


                //******************* FIN - INSERTANDO CONFIGURACION CREDITO *******************//
            }
        }
    }

    function actualizar_configuracion_comisiones3() {
        $this->mensaje = "Los parametros de Configuracion de Comisiones fueron Actualizadas Correctamente.";
        $conec = new ADO();
        $sql = "UPDATE urbanizacion set urb_porc_incremento='$_POST[urb_porc_incremento]',
                urb_porc_red='$_POST[urb_porc_red]',urb_porc_empresa='$_POST[urb_porc_empresa]',
                urb_porc_ci_multinivel='$_POST[urb_porc_ci_multinivel]'    
                where urb_id='$_GET[id]'";
        $conec->ejecutar($sql);
    }

    function formulario_configuracion_comisiones3() {
        $url = $this->link . '?mod=' . $this->modulo;

        if ($this->datos_configuracion_comisiones()) {
            if ($_GET['acc'] == 'actualizar_configuracion_comisiones3')
                $this->actualizar_configuracion_comisiones3();
        }

        $conec = new ADO();
        $sql = "select * from urbanizacion where urb_id='" . $_GET['id'] . "'";

        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        $_POST['urb_porc_incremento'] = $objeto->urb_porc_incremento;
        $_POST['urb_porc_red'] = $objeto->urb_porc_red;
        $_POST['urb_porc_empresa'] = $objeto->urb_porc_empresa;
        $_POST['urb_porc_ci_multinivel'] = $objeto->urb_porc_ci_multinivel;
        ?>
        <script>
            function ValidarNumero(e) {
                evt = e ? e : event;
                tcl = (window.Event) ? evt.which : evt.keyCode;
                if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0))
                {
                    return false;
                }
                return true;
            }

            function realizar_calculos(campo)
            {
                return false;
                if (campo.name == 'urb_porc_incremento')
                {
                    $('#urb_porc_red').val(0);
                    $('#urb_porc_empresa').val(0)
                }
                else
                {
                    if (campo.name == 'urb_porc_red')
                    {
                        var urb_porc_incremento = $('#urb_porc_incremento').val();
                        var urb_porc_red = $('#urb_porc_red').val();
                        $('#urb_porc_empresa').val(urb_porc_incremento - urb_porc_red);
                    }
                    else
                    {
                        if (campo.name == 'urb_porc_empresa')
                        {
                            var urb_porc_incremento = $('#urb_porc_incremento').val();
                            var urb_porc_empresa = $('#urb_porc_empresa').val();
                            $('#urb_porc_red').val(urb_porc_incremento - urb_porc_empresa);
                        }
                    }
                }
            }

            function enviar_formulario()
            {
                var urb_porc_empresa = $('#urb_porc_empresa').val();
                var urb_porc_incremento = $('#urb_porc_incremento').val();
                var urb_porc_red = $('#urb_porc_red').val();

                if (urb_porc_empresa >= 0 && urb_porc_incremento >= 0 && urb_porc_red >= 0)
                    $("#frm_comision3").submit();
                else
                {
                    $.prompt("El valor de % Empresa,% Incremento,% Red tiene que ser mayor a 0.");
                    return false;
                }
            }
        </script>    
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_comision3" name="frm_comision3" action="<?php echo $url; ?>&id=<?php echo $_GET['id']; ?>&tarea=MULTINIVEL&acc=actualizar_configuracion_comisiones3" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">


                        <!--Inicio-->
                        <div id="ContenedorDiv" hidden="">
                            <div class="Etiqueta" ><span class="flechas1">*</span>% Incremento</div>
                            <div id="CajaInput">
                                <input type="text" id="urb_porc_incremento" name="urb_porc_incremento" value="<?php echo $_POST[urb_porc_incremento]; ?>" onkeypress="return ValidarNumero(event);" onkeyup="realizar_calculos(this);" />
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>% Red</div>
                            <div id="CajaInput">
                                <input type="text" id="urb_porc_red" name="urb_porc_red" value="<?php echo $_POST[urb_porc_red]; ?>" onkeypress="return ValidarNumero(event);" onkeyup="realizar_calculos(this);" />
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>% Empresa</div>
                            <div id="CajaInput">
                                <input type="text" id="urb_porc_empresa" name="urb_porc_empresa" value="<?php echo $_POST[urb_porc_empresa]; ?>" onkeypress="return ValidarNumero(event);" onkeyup="realizar_calculos(this);" />
                            </div>
                        </div>
                        <!--Fin-->
                        
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>% Cuota Inicial</div>
                            <div id="CajaInput">
                                <input type="text" id="urb_porc_ci_multinivel" name="urb_porc_ci_multinivel" value="<?php echo $_POST[urb_porc_ci_multinivel]; ?>" onkeypress="return ValidarNumero(event);" onkeyup="" />
                            </div>
                        </div>
                        <!--Fin-->

                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <?php
                                    if (!($ver)) {
                                        ?>				
                                            <!--<input type="submit" class="boton" name="" value="Guardar">-->
                                        <input type="button" onclick="javascript:enviar_formulario();" class="boton" name="" value="Guardar">
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
            </form>
        </div>
        <script>
            setTimeout(function() {
                $(".msCorrecto").slideUp(500);
            }, 3000);
        </script>
        <?php
    }

}
?>
