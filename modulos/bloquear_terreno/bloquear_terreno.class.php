<?php

class BLOQUEAR_TERRENO extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function BLOQUEAR_TERRENO() {
        /**
         * Constructor de la clase BLOQUEAR_TERRENO
         *
         *  Inicializa los datos de la clase BLOQUEAR_TERRENO
         *
         * @param none
         * @return none
         */
        //permisos
        $this->ele_id = 166;

        $this->busqueda();

        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "bloq_id";
        $this->arreglo_campos[0]["texto"] = "Nro";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 40;

        $this->arreglo_campos[1]["nombre"] = "urb_nombre";
        $this->arreglo_campos[1]["texto"] = "Urbanización";
        $this->arreglo_campos[1]["tipo"] = "cadena";
        $this->arreglo_campos[1]["tamanio"] = 40;

        $this->arreglo_campos[2]["nombre"] = "uv_nombre";
        $this->arreglo_campos[2]["texto"] = "Uv";
        $this->arreglo_campos[2]["tipo"] = "cadena";
        $this->arreglo_campos[2]["tamanio"] = 40;

        $this->arreglo_campos[3]["nombre"] = "man_nro";
        $this->arreglo_campos[3]["texto"] = "Manzano";
        $this->arreglo_campos[3]["tipo"] = "cadena";
        $this->arreglo_campos[3]["tamanio"] = 40;

        $this->arreglo_campos[4]["nombre"] = "lot_nro";
        $this->arreglo_campos[4]["texto"] = "Lote";
        $this->arreglo_campos[4]["tipo"] = "cadena";
        $this->arreglo_campos[4]["tamanio"] = 40;

        $this->arreglo_campos[5]["nombre"] = "bloq_fecha";
        $this->arreglo_campos[5]["texto"] = "Fecha";
        $this->arreglo_campos[5]["tipo"] = "fecha";
        $this->arreglo_campos[5]["tamanio"] = 12;

        $this->arreglo_campos[6]["nombre"] = "bloq_estado";
        $this->arreglo_campos[6]["texto"] = "Estado";
        $this->arreglo_campos[6]["tipo"] = "comboarray";
        $this->arreglo_campos[6]["valores"] = "Habilitado,Deshabilitado:Habilitado,Deshabilitado";

        $this->arreglo_campos[7]["nombre"] = "bloq_usu_id";
        $this->arreglo_campos[7]["texto"] = "Usuario";
        $this->arreglo_campos[7]["tipo"] = "cadena";
        $this->arreglo_campos[7]["tamanio"] = 25;
        
        $this->arreglo_campos[8]["nombre"] = "int_nombre_apellido";
        $this->arreglo_campos[8]["campo_compuesto"] = "concat(int_nombre,' ',int_apellido)";
        $this->arreglo_campos[8]["texto"] = "Nombre completo";
        $this->arreglo_campos[8]["tipo"] = "compuesto";
        $this->arreglo_campos[8]["tamanio"] = 40;

        $this->link = 'gestor.php';

        $this->modulo = 'bloquear_terreno';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('BLOQUEAR TERRENOS');

        $this->usu = new USUARIO;
    }

    function dibujar_busqueda() {
        ?>
        <script>
            function ejecutar_script(id, tarea) {

                if (tarea == 'ANULAR')
                {
                    var txt = 'Esta seguro de Deshabilitar el registro seleccionado?';

                    $.prompt(txt, {
                        buttons: {Deshabilitar: true, Cancelar: false},
                        callback: function(v, m, f) {

                            if (v) {
                                location.href = 'gestor.php?mod=bloquear_terreno&tarea=' + tarea + '&id=' + id;
                            }

                        }
                    });
                }

                if (tarea == 'RETENER')
                {
                    var txt = 'Esta seguro de retener la Venta?';

                    $.prompt(txt, {
                        buttons: {Retener: true, Cancelar: false},
                        callback: function(v, m, f) {

                            if (v) {
                                location.href = 'gestor.php?mod=venta&tarea=' + tarea + '&id=' + id;
                            }

                        }
                    });
                }
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

        if ($this->verificar_permisos('ELIMINAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ELIMINAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_drop.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ELIMINAR';
            $nun++;
        }

        if ($this->verificar_permisos('ANULAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ANULAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/anular.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'DESHABILITAR';
            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }
    }

    function dibujar_listado() {

        $sql = "SELECT 
			bloq_id,bloq_lot_id,bloq_fecha,bloq_hora,bloq_estado,bloq_usu_id,bloq_nota,bloq_int_id,bloq_vdo_id,urb_nombre,man_nro,lot_nro,lot_tipo,uv_nombre 
			FROM 
			bloquear_terreno 
			inner join lote on (bloq_lot_id=lot_id)
			inner join uv on (uv_id=lot_uv_id)
			inner join manzano on (lot_man_id=man_id)
			inner join urbanizacion on (man_urb_id=urb_id) 
                        left join interno on (bloq_int_id=int_id)
			";


        $this->set_sql($sql, ' order by bloq_id desc ');

        $this->set_opciones();

        $this->dibujar();
    }

    function nombre_persona($int_id) {
        $sql = "select concat(int_nombre,' ',int_apellido)as persona from interno where int_id='" . $int_id . "'";
        $conec = new ADO();
        $conec->ejecutar($sql);
        $nombre = '&nbsp;';
        if ($conec->get_num_registros() > 0) {
            $nombre = $conec->get_objeto()->persona;
        }
        return $nombre;
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Nro</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Urbanizacion</th>
            <th>Uv</th>
            <th>Manzano</th>
            <th>Lote</th>
            <th>Vendedor</th>
            <th>Cliente</th>
            <th>Nota</th>   
            <th>Estado</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();

        for ($i = 0; $i < $this->numero; $i++) {

            $objeto = $this->coneccion->get_objeto();
            echo '<tr>';

            echo "<td>";
            echo $objeto->bloq_id;
            echo "</td>";
            echo "<td>";
            echo $conversor->get_fecha_latina($objeto->bloq_fecha);
            echo "</td>";
            echo "<td>";
            echo $objeto->bloq_hora;
            echo "</td>";
            echo "<td>";
            echo $objeto->urb_nombre;
            echo "</td>";
            echo "<td>";
            echo $objeto->uv_nombre;
            echo "</td>";
            echo "<td>";
            echo $objeto->man_nro;
            echo "</td>";
            echo "<td>";
            echo $objeto->lot_nro;
            echo "</td>";

            echo "<td>";
            echo $this->nombre_persona_vendedor($objeto->bloq_vdo_id);
            echo "</td>";

            echo "<td>";
            echo $this->nombre_persona($objeto->bloq_int_id);
            echo "</td>";

            echo "<td>";
            if ($objeto->bloq_nota != '')
                echo $objeto->bloq_nota;
            else
                echo "&nbsp;";

            echo "</td>";

            echo "<td>";
            echo $objeto->bloq_estado;
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->bloq_id);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from ad_usuario
				where usu_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['usu_id'] = $objeto->usu_id;

        $_POST['usu_password'] = '123456789';

        $_POST['usu_per_id'] = $objeto->usu_per_id;

        $_POST['usu_estado'] = $objeto->usu_estado;

        $_POST['usu_gru_id'] = $objeto->usu_gru_id;

        $fun = NEW FUNCIONES;

        $_POST['usu_nombre_persona'] = $fun->nombre($objeto->usu_per_id);
    }

    function datos() {
        if ($_POST) {
            $valores_lote = explode("-", $_POST['ven_lot_id']);
            $id_lote = $valores_lote[0];

            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Lote";
            $valores[$num]["valor"] = $id_lote;
            $valores[$num]["tipo"] = "numero";
            $valores[$num]["requerido"] = true;

            $val = NEW VALIDADOR;

            $this->mensaje = "";

            if ($val->validar($valores)) {
                return true;
            } else {
                $this->mensaje = $val->mensaje;
                return false;
            }
        }
        return false;
    }

    function formulario_tcp($tipo) {
        $conec = new ADO();

        $sql = "select * from interno";
        $conec->ejecutar($sql);
        $nume = $conec->get_num_registros();
        $personas = 0;
        if ($nume > 0) {
            $personas = 1;
        }

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

        $red = $url;

        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }

        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }
        $page = "'gestor.php?mod=usuario&tarea=AGREGAR&acc=Emergente'";
        $extpage = "'persona'";
        $features = "'left=325,width=600,top=200,height=420,scrollbars=yes'";

        $this->formulario->dibujar_tarea('USUARIO');

        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <script>
            function set_valor_interno(data) {
                document.frm_sentencia.bloq_int_id.value = data.id;
                document.frm_sentencia.int_nombre_persona.value = data.nombre;
            }
            function reset_interno()
            {
                document.frm_sentencia.usu_per_id.value = "";
                document.frm_sentencia.usu_nombre_persona.value = "";
            }

            function reset_interno2()
            {
                document.frm_sentencia.bloq_int_id.value = "";
                document.frm_sentencia.int_nombre_persona.value = "";
            }

            function enviar_formulario() {

                var urb_id = document.frm_sentencia.ven_urb_id.value;
                var uv_id = document.frm_sentencia.ven_uv_id.value;
                var man_id = document.frm_sentencia.ven_man_id.value;
                var lot_id = document.frm_sentencia.ven_lot_id.value;
                var int_id = document.frm_sentencia.bloq_int_id.value;
                var nota = document.frm_sentencia.bloq_nota.value;



                if (urb_id != '' && uv_id != '' && man_id != '' && lot_id != '')
                {

                    document.frm_sentencia.submit();
                }
                else
                {
                    $.prompt('Para bloquear un terreno debe seleccionar la Urbanizacion, Uv, Manzana y Lote.', {opacity: 0.8});
                }

            }

            function cargar_datos(valor)
            {
                var datos = valor;
                var val = datos.split('-');

                document.frm_sentencia.valor_terreno.value = parseFloat(val[1]) * parseFloat(val[2]);
                document.frm_sentencia.superficie.value = val[1];
                document.frm_sentencia.valor.value = val[2];
                document.frm_sentencia.ven_moneda.value = val[4];

                if (val[4] == 1)
                {
                    var simbolo_moneda_vm2 = '&nbsp;&nbsp;&nbsp;&nbsp;Valor m2 Bs&nbsp;&nbsp;&nbsp;';
                    var simbolo_moneda_vt = 'Valor del Terreno Bs';
                    var simbolo_moneda_desc = 'Bs';
                }
                else
                {
                    var simbolo_moneda_vm2 = '&nbsp;&nbsp;&nbsp;Valor m2 $us&nbsp;&nbsp;';
                    var simbolo_moneda_vt = 'Valor del Terreno $us';
                    var simbolo_moneda_desc = '$us';
                }

                $('#simb_moneda_vm2').html(simbolo_moneda_vm2);
                $('#simb_moneda_vt').html(simbolo_moneda_vt);
                $('#simb_moneda_descuento').html('&nbsp;' + simbolo_moneda_desc);


                if (val[5] == 'Vivienda')
                {
                    $('#seccion_sup_valor').css("visibility", "hidden");
                }
                else
                {
                    $('#seccion_sup_valor').css("visibility", "visible");
                }

                calcular_cuota()
                calcular_monto()
            }

            function calcular_monto()
            {
                /*var vt=parseFloat(document.frm_sentencia.valor_terreno.value);
                 var des=parseFloat(document.frm_sentencia.descuento.value);
                 var td=(vt*des)/100;
                 document.frm_sentencia.monto.value=vt-td;*/

                var vt = parseFloat(document.frm_sentencia.valor_terreno.value);
                var des = parseFloat(document.frm_sentencia.descuento.value);
                //var td=(vt*des)/100;
                document.frm_sentencia.monto.value = vt - des;
            }
            function calcular_valor_terreno()
            {
                var sup = parseFloat(document.frm_sentencia.superficie.value);
                var val = parseFloat(document.frm_sentencia.valor.value);

                document.frm_sentencia.valor_terreno.value = sup * val;

                calcular_cuota()
                calcular_monto()
            }

            function cargar_uv(id)
            {
//                cargar_lote(0);

                var valores = "tarea=uv&urb=" + id;

                ejecutar_ajax('ajax.php', 'uv', valores, 'POST');
            }

            function cargar_manzano(id, uv)
            {
//                cargar_lote(0);

                var valores = "tarea=manzanos&urb=" + id + "&uv=" + uv;

                ejecutar_ajax('ajax.php', 'manzano', valores, 'POST');
            }

            function cargar_lote(id, uv)
            {
                var valores = "tarea=lotes&man=" + id + "&uv=" + uv;

                ejecutar_ajax('ajax.php', 'lote', valores, 'POST');
            }

            function obtener_valor_uv() {
                var axuUv = $('#ven_uv_id').val();
                var axuMan = $('#ven_man_id').val();

                cargar_lote(axuMan, axuUv);
            }

            function obtener_valor_manzano() {
                var auxUrb = $('#ven_urb_id').val();
                var auxUv = $('#ven_uv_id').val();

                cargar_manzano(auxUrb, auxUv);
            }

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
            function calcular_cuota()
            {
                var tipo = document.frm_sentencia.ven_tipo.options[document.frm_sentencia.ven_tipo.selectedIndex].value;

                if (tipo == 'Credito')
                {
                    var v_vt = parseFloat(document.frm_sentencia.valor_terreno.value);

                    var dato_s = document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;

                    var dato_a = dato_s.split('-');

                    document.frm_sentencia.cuota_inicial.value = (dato_a[3] * v_vt) / 100;
                }
            }
        </script>
        <!--MaskedInput-->
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <!--MaskedInput-->
        <!--AutoSuggest-->
        <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        <!--AutoSuggest-->
        <!--FancyBox-->
        <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <!--FancyBox-->
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizaci?n</div>
                            <div id="CajaInput">
                                <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" onchange="cargar_uv(this.value);">
                                    <option value="">Seleccione</option>
        <?php
        $fun = NEW FUNCIONES;
        $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion where urb_eliminado='No'", $_POST['ven_urb_id']);
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
                                    <select style="width:200px;" name="ven_man_id" class="caja_texto" onchange="cargar_lote(this.value);">
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
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Lote</div>
                            <div id="CajaInput">
                                <div id="lote">
                                    <select style="width:200px;" name="ven_lot_id" class="caja_texto">
                                        <option value="">Seleccione</option>
                                        <?php
                                        if ($_POST['ven_man_id'] <> "") {
                                            $fun = NEW FUNCIONES;
                                            $fun->combo("select lot_id as id,lot_nro as nombre from lote where lot_man_id='" . $_POST['ven_man_id'] . "' ", $_POST['ven_lot_id']);
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1"></span>Vendedor</div>
                            <div id="CajaInput">
                                        <?php
                                        if ($this->obtener_grupo_id($this->usu->get_id()) == "Vendedores") {
                                            $id_interno = $this->obtener_id_interno_tbl_usuario($this->usu->get_id());
                                            ?>
                                    <select style="width:200px;" name="bloq_vdo_id" id="bloq_vdo_id" class="caja_texto">
            <?php
            $fun = NEW FUNCIONES;
            $fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado' AND vdo_int_id=$id_interno", $_POST['bloq_vdo_id']);
            ?>
                                    </select>
            <?php
        } else {
            ?>
                                    <select style="width:200px;" name="bloq_vdo_id" id="bloq_vdo_id" class="caja_texto">
                                        <option value="">Seleccione</option>
            <?php
            $fun = NEW FUNCIONES;
            $fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado'", $_POST['bloq_vdo_id']);
            ?>
                                    </select>
                                            <?php
                                        }
                                        ?>
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1"></span>Cliente</div>
                            <div id="CajaInput">
                                <input name="bloq_int_id" id="bloq_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['bloq_int_id'] ?>" size="2">
                                <input name="int_nombre_persona"readonly="readonly" id="int_nombre_persona"  type="text" class="caja_texto" value="<?php echo $_POST['int_nombre_persona'] ?>" size="40">
                                <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                    <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                </a>
                                <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;" data-url="gestor.php?mod=interno&tarea=ACCEDER&acc=buscar" href="javascript:void(0)">
                                    <img src="images/b_search.png" border="0" title="BUSCAR" alt="BUSCAR">
                                </a>
                                <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_interno2();">
                                    <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                </a>
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1"></span>Nota</div>
                            <div id="CajaInput">
                                <textarea name="bloq_nota" id="bloq_nota"><?php echo $_POST['bloq_nota']; ?></textarea>
                            </div>
                        </div>

                    </div>

                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
        <?php
        if (!($ver)) {
            ?>
                                        <!--<input type="submit" class="boton" name="" value="Guardar">-->
                                    <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario()">
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
            </form>
        </div>
        <?php if (!($ver || $cargar)) { ?>
        <?php } ?>
        <script type="text/javascript">
            popup = null;
            $('a.group').click(function() {
                var url = $(this).attr('data-url') + '&popup=1';
                if (popup !== null) {
                    popup.close();
                }
                popup = window.open(url, 'reportes', 'left=100,width=900,height=600,top=0,scrollbars=yes');
                popup.document.close();
            });
        </script>
                                <?php
                            }

                            function obtener_grupo_id($usu_id) {
                                $conec = new ADO();

                                $sql = "SELECT usu_gru_id FROM ad_usuario WHERE usu_id='$usu_id'";

                                $conec->ejecutar($sql);

                                $objeto = $conec->get_objeto();

                                return $objeto->usu_gru_id;
                            }

                            function obtener_id_interno_tbl_usuario($usu_id) {
                                $conec = new ADO();

                                $sql = "SELECT usu_per_id FROM ad_usuario WHERE usu_id='$usu_id'";

                                $conec->ejecutar($sql);

                                $objeto = $conec->get_objeto();

                                return $objeto->usu_per_id;
                            }

                            function emergente() {
                                $this->formulario->dibujar_cabecera();

                                $valor = trim($_POST['valor']);
                                ?>

        <script>
            function poner(id, valor)
            {
                opener.document.frm_usuario.usu_per_id.value = id;
                opener.document.frm_usuario.usu_nombre_persona.value = valor;
                window.close();
            }
        </script>
        <br><center><form name="form" id="form" method="POST" action="gestor.php?mod=usuario&tarea=AGREGAR&acc=Emergente">
                <table align="center">
                    <tr>
                        <td class="txt_contenido" colspan="2" align="center">
                            <input name="valor" type="text" class="caja_texto" size="30" value="<?php echo $valor; ?>">
                            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
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

                $valores = explode("-", $_POST['ven_lot_id']);
                $id_lote = $valores[0];


                include_once("clases/seguimiento_lote.class.php");

                $seguimiento_lote = new SEGUIMIENTO_LOTE();

                $resultado = $seguimiento_lote->verificar_seguimiento_lote_bloqueo($id_lote);

                if ($resultado == '') {
                    $verificar = NEW VERIFICAR;

                    $parametros[0] = array('bloq_lot_id', 'bloq_estado');
                    $parametros[1] = array($id_lote, 'Habilitado');
                    $parametros[2] = array('bloquear_terreno');

                    if ($verificar->validar($parametros)) {
                        $conec = new ADO();

                        $sql = "insert into bloquear_terreno (bloq_lot_id,bloq_fecha,bloq_hora,bloq_estado,bloq_usu_id,bloq_int_id,bloq_nota,bloq_vdo_id) values ('" . $id_lote . "','" . date('Y-m-d') . "','" . date('H:i') . "','Habilitado','" . $this->usu->get_id() . "','" . $_POST['bloq_int_id'] . "','" . $_POST['bloq_nota'] . "','" . $_POST['bloq_vdo_id'] . "')";

                        $conec->ejecutar($sql);


                        //Cambio Estado de 'Bloqueado' el Lote.
                        $sql = "update lote set lot_estado='Bloqueado' where lot_id=$id_lote";

                        $conec->ejecutar($sql);

                        $mensaje = 'Terreno Bloqueado Correctamente';
                    } else {
                        $mensaje = 'No se pudo Bloquear el Terreno, por que el Lote que seleccionó, ya se encuentra Bloqueado.';
                    }
                } else {
                    $mensaje = $resultado;
                }

                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function modificar_tcp() {
                $conec = new ADO();

                $codigo = "";

                if ($_POST['usu_password'] <> "123456789") {
                    $sql = "update ad_usuario set 
								usu_password='" . md5($_POST['usu_password']) . "',
								usu_per_id='" . $_POST['usu_per_id'] . "',
								usu_estado='" . $_POST['usu_estado'] . "',
								usu_gru_id='" . $_POST['usu_gru_id'] . "'
								where usu_id='" . $_GET['id'] . "'";
                } else {
                    $sql = "update ad_usuario set 
								usu_per_id='" . $_POST['usu_per_id'] . "',
								usu_estado='" . $_POST['usu_estado'] . "',
								usu_gru_id='" . $_POST['usu_gru_id'] . "'
								where usu_id='" . $_GET['id'] . "'";
                }



                $conec->ejecutar($sql);

                $mensaje = 'Usuario Modificado Correctamente';

                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function formulario_confirmar_eliminacion() {

                $mensaje = 'Esta seguro de eliminar el usuario?';

                $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'usu_id');
            }

            function eliminar_tcp() {
                $verificar = NEW VERIFICAR;

                $parametros[0] = array('log_usu_id');
                $parametros[1] = array($_POST['usu_id']);
                $parametros[2] = array('ad_logs');

                if ($verificar->validar($parametros)) {
                    $conec = new ADO();

                    $sql = "delete from ad_usuario where usu_id='" . $_POST['usu_id'] . "'";

                    $conec->ejecutar($sql);

                    $mensaje = 'Usuario Eliminado Correctamente.';
                } else {
                    $mensaje = 'El usuario no puede ser eliminado, por que ya realizo varias acciones en el sistema.';
                }

                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function anular() {
                $conec = new ADO();

                $sql = "select bloq_estado from bloquear_terreno where bloq_id='" . $_GET['id'] . "'";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                $estado = $objeto->bloq_estado;

                if ($estado == 'Habilitado') {
                    /*                     * *************************************************** */
                    /* 													  */
                    /* 	Busco el Lote a Colocar en estado Disponible      */
                    /* 													  */
                    /*                     * *************************************************** */
                    $sql = "select bloq_lot_id from bloquear_terreno where bloq_id='" . $_GET['id'] . "'";

                    $conec->ejecutar($sql);

                    $objeto = $conec->get_objeto();

                    $lote = $objeto->bloq_lot_id;


                    /*                     * *************************************************** */
                    /* 													  */
                    /* 	Una vez Encontrado el Lote, ponemos en estado	  */
                    /* 	'Disponible'									  */
                    /*                     * *************************************************** */

                    $sql = "update lote set lot_estado='Disponible' where lot_id='$lote'";

                    $conec->ejecutar($sql);



                    /*                     * *************************************************** */
                    /* 													  */
                    /* 	EL registro en la tabla 'bloquear_terreno' coloco  */
                    /* 	en estado 'Deshabilitado'						  */
                    /*                     * *************************************************** */

                    $sql = "update bloquear_terreno set bloq_estado='Deshabilitado' where bloq_id='" . $_GET['id'] . "'";

                    $conec->ejecutar($sql);

                    $mensaje = 'Se Deshabilitó Correctamente';
                } else {
                    $mensaje = 'No se pudo <b>Deshabilitar el Bloqueo de Terreno</b> por que el Terreno ya se encuentra en estado <b>Deshabilitado</b>';
                }

                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function nombre_persona_vendedor($vdo_id) {
                $conec = new ADO();

                $sql = "SELECT concat(int_nombre,' ',int_apellido)as persona FROM interno
		inner join vendedor on (vdo_int_id=int_id) 
		WHERE vdo_id='$vdo_id'";

                $nombre = '&nbsp;';

                $conec->ejecutar($sql);

                if ($conec->get_num_registros() > 0) {
                    $nombre = $conec->get_objeto()->persona;
                }

                return $nombre;
            }

        }
        ?>