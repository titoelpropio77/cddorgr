<?php

class GESTION extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function GESTION() {
        //permisos
        $this->ele_id = 158;

        $this->busqueda();

        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos
        $this->num_registros = 14;
        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "gru_descripcion";
        $this->arreglo_campos[0]["texto"] = "Grupo";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 25;

        $this->arreglo_campos[1]["nombre"] = "int_nombre";
        $this->arreglo_campos[1]["texto"] = "Nombre";
        $this->arreglo_campos[1]["tipo"] = "cadena";
        $this->arreglo_campos[1]["tamanio"] = 25;

        $this->arreglo_campos[2]["nombre"] = "int_apellido";
        $this->arreglo_campos[2]["texto"] = "Apellido";
        $this->arreglo_campos[2]["tipo"] = "cadena";
        $this->arreglo_campos[2]["tamanio"] = 25;

        $this->arreglo_campos[3]["nombre"] = "ges_id";
        $this->arreglo_campos[3]["texto"] = "Usuario";
        $this->arreglo_campos[3]["tipo"] = "cadena";
        $this->arreglo_campos[3]["tamanio"] = 25;

        $this->link = 'gestor.php';
        $this->modulo = 'gestion';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('GESTION');
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
            $nun++;
        }

        if ($this->verificar_permisos('PERIODOS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'PERIODOS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/periodo.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'PERIODOS';
            $nun++;
        }

        if ($this->verificar_permisos('ESTRUCTURA')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ESTRUCTURA';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/estructura.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'CUENTAS, CC, CA, CF';
            $nun++;
        }
    }

    function dibujar_listado() {
        $sql = "select * from con_gestion where ges_eliminado='No'";
        $this->set_sql($sql, "order by ges_fecha_ini desc");
        $this->set_opciones();
        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Gesti&oacute;n</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Plan de Cuentas</th>
            <th>Centros de Costo</th>
            <th>Cuentas Analiticas</th>
            <th>Cuentas de Flujo</th>
            <th class="tOpciones" width="150px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();
        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();
            echo '<tr>';

            echo "<td>";
            echo $objeto->ges_descripcion;
            echo "</td>";

            echo "<td>";
            echo $conversor->get_fecha_latina($objeto->ges_fecha_ini);
            echo "</td>";

            echo "<td>";
            echo $conversor->get_fecha_latina($objeto->ges_fecha_fin);
            echo "</td>";

            echo "<td>";
            echo $objeto->ges_formato_cuenta;
            echo "</td>";

            echo "<td>";
            echo $objeto->ges_formato_cc;
            echo "</td>";

            echo "<td>";
            echo $objeto->ges_formato_ca;
            echo "</td>";

            echo "<td>";
            echo $objeto->ges_formato_cf;
            echo "</td>";

            echo "<td>";
            echo $this->get_opciones($objeto->ges_id);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();
        $conversor = new convertir();
        $sql = "select * from con_gestion
				where ges_id = '" . $_GET['id'] . "'";

        
        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['ges_id'] = $objeto->ges_id;

        $_POST['ges_descripcion'] = $objeto->ges_descripcion;

        $_POST['ges_fecha_inicio'] = $conversor->get_fecha_latina($objeto->ges_fecha_ini);

        $_POST['ges_fecha_fin'] = $conversor->get_fecha_latina($objeto->ges_fecha_fin);

        $_POST['ges_formato_cuenta'] = $objeto->ges_formato_cuenta;

        $_POST['ges_formato_cc'] = $objeto->ges_formato_cc;

        $_POST['ges_formato_ca'] = $objeto->ges_formato_ca;

        $_POST['ges_formato_cf'] = $objeto->ges_formato_cf;

        $fun = NEW FUNCIONES;

        //$_POST['usu_nombre_persona']=$fun->nombre($objeto->usu_per_id);

        $conec = new ADO();

        $sql = "select * from con_gestion_moneda
		inner join con_moneda on (mon_id=gesmon_mon_id) where gesmon_ges_id='" . $_GET['id'] . "'";
//        echo $sql;
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();

        $monedas = array();
        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            $moneda = new stdClass();
            $moneda->id = $objeto->mon_id;
            $moneda->nombre = $objeto->mon_titulo;
            $moneda->simbolo = $objeto->mon_Simbolo;
            $monedas[] = $moneda;
            $conec->siguiente();
        }
        $_POST['ges_monedas'] = json_encode($monedas);
    }

    function datos() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Gestion";
            $valores[$num]["valor"] = $_POST['ges_descripcion'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;
            $valores[$num]["etiqueta"] = "Fecha Inicio";
            $valores[$num]["valor"] = $_POST['ges_fecha_inicio'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;
            $valores[$num]["etiqueta"] = "Fecha Fin";
            $valores[$num]["valor"] = $_POST['ges_fecha_fin'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;
//            $valores[$num]["etiqueta"] = "Formato Cuenta";
//            $valores[$num]["valor"] = $_POST['ges_formato_cuenta'];
//            $valores[$num]["tipo"] = "todo";
//            $valores[$num]["requerido"] = true;
//            $num++;
//            $valores[$num]["etiqueta"] = "Formato Centro de Costo";
//            $valores[$num]["valor"] = $_POST['ges_formato_cc'];
//            $valores[$num]["tipo"] = "todo";
//            $valores[$num]["requerido"] = true;
//            $num++;
//            $valores[$num]["etiqueta"] = "Formato Cuenta Analitica";
//            $valores[$num]["valor"] = $_POST['ges_formato_ca'];
//            $valores[$num]["tipo"] = "todo";
//            $valores[$num]["requerido"] = true;
//            $num++;
//            $valores[$num]["etiqueta"] = "Formato Cuenta de Flujo";
//            $valores[$num]["valor"] = $_POST['ges_formato_cf'];
//            $valores[$num]["tipo"] = "todo";
//            $valores[$num]["requerido"] = true;
//            $num++;

            $this->mensaje = "";
            $val_fecha = true;
            if ($_POST['ges_fecha_inicio'] && $_POST['ges_fecha_fin']) {
                $conversor = new convertir();
                $fechai = $conversor->get_fecha_mysql($_POST['ges_fecha_inicio']);
                $fechaf = $conversor->get_fecha_mysql($_POST['ges_fecha_fin']);

                if (FUNCIONES::comparar_fechas($fechai, $fechaf) < 0) {
                    $gestiones = FUNCIONES::objetos_bd("con_gestion", "ges_eliminado='No'");
                    for ($i = 0; $i < $gestiones->get_num_registros(); $i++) {

                        $gestion = $gestiones->get_objeto();
                        if ($_GET['id'] != $gestion->ges_id) {
                            if (FUNCIONES::comparar_fechas($fechai, $gestion->ges_fecha_ini) >= 0 && FUNCIONES::comparar_fechas($fechai, $gestion->ges_fecha_fin) <= 0) {
                                $this->mensaje.="La <b>Fecha Inicial</b> se encuentra en el rango de la <b>$gestion->ges_descripcion</b> <br>";
                                $val_fecha = false;
                            }
                            if (FUNCIONES::comparar_fechas($fechaf, $gestion->ges_fecha_ini) >= 0 && FUNCIONES::comparar_fechas($fechaf, $gestion->ges_fecha_fin) <= 0) {
                                $this->mensaje.="La <b>Fecha Final</b> se encuentra en el rango de la <b>$gestion->ges_descripcion</b> <br>";
                                $val_fecha = false;
                            }
                        }
                        $gestiones->siguiente();
                    }
                } else {
                    $this->mensaje.="El campo <b>Fecha Fin</b> debe ser mayor a la <b>Fecha Inicio</b> <br>";
                    $val_fecha = false;
                }
            }


            $val = NEW VALIDADOR;
            if ($val->validar($valores)) {
                return true && $val_fecha;
            } else {
                $this->mensaje .= $val->mensaje;
                return false;
            }
        }
        return false;
    }

    function formulario_tcp($tipo) {
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
        $features = "'left=325,width=600,top=200,height=420'";

        $this->formulario->dibujar_tarea('USUARIO');

        if ($this->mensaje <> "") {
            $this->formulario->dibujar_mensaje($this->mensaje);
        }
        ?>
        <script>
            function ValidarNumero(e) {
                evt = e ? e : event;
                tcl = (window.Event) ? evt.which : evt.keyCode;

                //if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                if (tcl == 35 || tcl == 46 || tcl == 8)
                {
                    return true;
                }
                else
                    return false;
            }

            function limpiar_campos() {
        //                document.frm_gestion.ges_mon_id.value = '';
        //                document.frm_gestion.nombre_moneda.value = '';
        //                document.frm_gestion.simbolo_moneda.value = '';
            }
            function remove(row) {
                var filas = parseFloat(document.frm_gestion.nfilas.value);

                document.frm_gestion.nfilas.value = filas - 1;

                if (document.frm_gestion.nfilas.value == 0) {
                    document.frm_gestion.nfilasshadown.value = '';
                }

                var cant = $(row).parent().parent().parent().children().length;

                if (cant > 1)
                    $(row).parent().parent().parent().remove();

            }

            function addTableRow(id, valor)
            {
                $(id).append(valor);
            }

            function datos_fila(){
        //                var mon_id = document.frm_gestion.ges_mon_id.options[document.frm_gestion.ges_mon_id.selectedIndex].value;
        //                //var tipo_comision=document.frm_gestion.vdo_tipo_comi.options[document.frm_gestion.vdo_tipo_comi.selectedIndex].value;
        //                var nombre_moneda = document.frm_gestion.nombre_moneda.value;
        //                var simbolo_moneda = document.frm_gestion.simbolo_moneda.value;

                var mon_id = $("#ges_monedas_combo option:selected").val();
                var nombre_moneda = $("#ges_monedas_combo  option:selected").text();
                var simbolo_moneda = $("#ges_monedas_combo option:selected").attr("data_simbolo");

                if (mon_id !== ''){
                    //var valores=valor.split('|');
                    //var valores=valor.split('|');
                    if (verificar(mon_id)){
                        var filas = parseFloat(document.frm_gestion.nfilas.value);
                        document.frm_gestion.nfilas.value = filas + 1;
                        if (document.frm_gestion.nfilas.value > 0) {
                            document.frm_gestion.nfilasshadown.value = 1;
                        }
                        var fila = '<tr>';
                        fila += '<td>';
                        fila += '<input name="mon_id" type="hidden" value="' + mon_id + '">'
                        fila += '<span>' + nombre_moneda + '</span>';
                        fila += '</td>';
                        fila += '<td>' + simbolo_moneda + '</td>';
                        fila += '<td><center><img style="float:none;" src="images/b_drop.png" class="img_del_mon"></center></td>';
                        fila += '</tr>';
                        addTableRow('#tges_moneda', fila);
                        limpiar_campos();
                    }
                    else
                    {
                        $.prompt('El item seleccionado ya fue agregado a la Lista', {opacity: 0.8});
                    }
                }
                else
                {
                    $.prompt('- Seleccione Moneda.', {opacity: 0.8});
                }
            }

            function verificar(id)
            {
                var cant = $('#tges_moneda tbody').children().length;
                var ban = true;
                if (cant > 0)
                {
                    $('#tges_moneda tbody').children().each(function() {
                        var dato = $(this).eq(0).children().eq(0).children().eq(0).attr('value');

                        if (id == dato)
                        {
                            ban = false;
                        }

                    });
                }
                return ban;
            }

            function actualizar_seccion_moneda(id)
            {
                var valores = "tarea=actualizar_seccion_moneda&mon_id=" + id;

                cargar_pagina('ajax.php', 'seccion_moneda', valores, 'POST');
            }

            $("#frm_gestion").live("submit", function() {
                var filas = $("#tges_moneda tbody tr");
                var monedas = {};//new Array();            
                for (var i = 0; i < filas.size(); i++) {
                    var moneda = {};
                    moneda.id = $(filas[i]).children("td").eq(0).find("input").val();
                    moneda.nombre = $(filas[i]).children("td").eq(0).find("span").text();
                    moneda.simbolo = $(filas[i]).children("td").eq(1).text();
                    monedas[i] = moneda;
                }
                var json_mon = JSON.stringify(monedas);
                
                $("#ges_monedas").val(json_mon);
            });
            $(".img_del_mon").live("click", function() {
                remove(this);
            });
        </script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_gestion" name="frm_gestion" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Gestion</div>
                            <div id="CajaInput">

                                <input name="ges_descripcion" id="ges_descripcion"  type="text" class="caja_texto" value="<?php echo $_POST['ges_descripcion'] ?>" size="25">

                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span>Fecha Inicio</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" name="ges_fecha_inicio" id="ges_fecha_inicio" size="12" value="<?php echo $_POST['ges_fecha_inicio']; ?>" type="text">
                                <input name="but_fecha_pago" id="but_fecha_pago" class="boton_fecha" value="..." type="button">
                                <script type="text/javascript">
                                    $("#ges_fecha_inicio").mask("99/99/9999");
//                                    Calendar.setup({inputField: "ges_fecha_inicio"
//                                                , ifFormat: "%d/%m/%Y",
//                                        button: "but_fecha_pago"
//                                    });
                                </script>
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span>Fecha Fin</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="ges_fecha_fin" id="ges_fecha_fin" size="12" value="<?php echo $_POST['ges_fecha_fin']; ?>" type="text">
                                <input name="but_fecha_pago2" id="but_fecha_pago2" class="boton_fecha" value="..." type="button">
                                <script type="text/javascript">
                                    $("#ges_fecha_fin").mask("99/99/9999");
//                                    Calendar.setup({inputField: "ges_fecha_fin"
//                                                , ifFormat: "%d/%m/%Y",
//                                        button: "but_fecha_pago2"
//                                    });
                                </script>
                            </div>
                        </div>
                        <!--Fin-->
                        <?php
                        $sql="select * from con_gestion where ges_eliminado='No' order by ges_fecha_ini desc;";
                        $gestiones=  FUNCIONES::objetos_bd_sql($sql);                        
                        ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Cuentas</div>
                            <div id="CajaInput">
                                <?php 
                                if(!isset($_GET['id'])){
                                ?>
                                <select id="cp_cuenta" name="cp_cuenta" style="width: 70px">
                                    <option value="n">Nuevo</option>
                                    <option value="c">Copiar</option>
                                </select>&nbsp;
                                <?php
                                }
                                ?>
                                <input name="ges_formato_cuenta" id="ges_formato_cuenta"  type="text" class="caja_texto" value="<?php echo $_POST['ges_formato_cuenta'] ?>" size="25" onkeypress="return ValidarNumero(event);">
                                <select id="ges_cuenta" name="ges_cuenta" style="width: 100px;display: none;" >
                                    <?php
                                    for($i=0;$i<$gestiones->get_num_registros();$i++){
                                        $gestion=$gestiones->get_objeto();
                                    ?>
                                    <option value="<?php echo $gestion->ges_id;?>"><?php echo $gestion->ges_descripcion;?></option>
                                    <?php                                 
                                        $gestiones->siguiente();
                                    } 
                                    ?>
                                </select>
                            </div>
                        </div>                        
                        <script>
                            $("#cp_cuenta").change(function (){
                                var op=$("#cp_cuenta option:selected").val();
                                if(op==='n'){
                                    $("#ges_formato_cuenta").show();
                                    $("#ges_cuenta").hide();
                                }else if(op==='c'){
                                    $("#ges_formato_cuenta").hide();
                                    $("#ges_cuenta").show();
                                }
                            });
                        </script>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Centros de Costo</div>
                            <div id="CajaInput">
                                <?php 
                                if(!isset($_GET['id'])){
                                ?>
                                <select id="cp_cc" name="cp_cc" style="width: 70px">
                                    <option value="n">Nuevo</option>
                                    <option value="c">Copiar</option>
                                </select>&nbsp;
                                <?php
                                }
                                ?>
                                <input name="ges_formato_cc" id="ges_formato_cc"  type="text" class="caja_texto" value="<?php echo $_POST['ges_formato_cc'] ?>" size="25" onkeypress="return ValidarNumero(event);">
                                <select id="ges_cc" name="ges_cc" style="width: 100px;display: none;" >
                                    <?php
                                    $gestiones->reset();
                                    for($i=0;$i<$gestiones->get_num_registros();$i++){
                                        $gestion=$gestiones->get_objeto();
                                    ?>
                                    <option value="<?php echo $gestion->ges_id;?>"><?php echo $gestion->ges_descripcion;?></option>
                                    <?php                                 
                                        $gestiones->siguiente();
                                    } 
                                    ?>
                                </select>
                            </div>
                        </div>
                        <script>
                            $("#cp_cc").change(function (){
                                var op=$("#cp_cc option:selected").val();
                                if(op==='n'){
                                    $("#ges_formato_cc").show();
                                    $("#ges_cc").hide();
                                }else if(op==='c'){
                                    $("#ges_formato_cc").hide();
                                    $("#ges_cc").show();
                                }
                            });
                        </script>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Cuentas Analiticas</div>
                            <div id="CajaInput">
                                <?php 
                                if(!isset($_GET['id'])){
                                ?>
                                <select id="cp_ca" name="cp_ca" style="width: 70px">
                                    <option value="n">Nuevo</option>
                                    <option value="c">Copiar</option>
                                </select>&nbsp;
                                <?php
                                }
                                ?>
                                <input name="ges_formato_ca" id="ges_formato_ca"  type="text" class="caja_texto" value="<?php echo $_POST['ges_formato_ca'] ?>" size="25" onkeypress="return ValidarNumero(event);">
                                <select id="ges_ca" name="ges_ca" style="width: 100px;display: none;" >
                                    <?php
                                    $gestiones->reset();
                                    for($i=0;$i<$gestiones->get_num_registros();$i++){
                                        $gestion=$gestiones->get_objeto();
                                    ?>
                                    <option value="<?php echo $gestion->ges_id;?>"><?php echo $gestion->ges_descripcion;?></option>
                                    <?php                                 
                                        $gestiones->siguiente();
                                    } 
                                    ?>
                                </select>
                            </div>
                        </div>
                        <script>
                            $("#cp_ca").change(function (){
                                var op=$("#cp_ca option:selected").val();
                                if(op==='n'){
                                    $("#ges_formato_ca").show();
                                    $("#ges_ca").hide();
                                }else if(op==='c'){
                                    $("#ges_formato_ca").hide();
                                    $("#ges_ca").show();
                                }
                            });
                        </script>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Cuentas de Flujo</div>
                            <div id="CajaInput">
                                <?php 
                                if(!isset($_GET['id'])){
                                ?>
                                <select id="cp_cf" name="cp_cf" style="width: 70px">
                                    <option value="n">Nuevo</option>
                                    <option value="c">Copiar</option>
                                </select>&nbsp;
                                <?php
                                }
                                ?>
                                <input name="ges_formato_cf" id="ges_formato_cf"  type="text" class="caja_texto" value="<?php echo $_POST['ges_formato_cf'] ?>" size="25" onkeypress="return ValidarNumero(event);">
                                <select id="ges_cf" name="ges_cf" style="width: 100px;display: none;" >
                                    <?php
                                    $gestiones->reset();
                                    for($i=0;$i<$gestiones->get_num_registros();$i++){
                                        $gestion=$gestiones->get_objeto();
                                    ?>
                                    <option value="<?php echo $gestion->ges_id;?>"><?php echo $gestion->ges_descripcion;?></option>
                                    <?php                                 
                                        $gestiones->siguiente();
                                    } 
                                    ?>
                                </select>                                
                            </div>
                        </div>
                        <script>
                            $("#cp_cf").change(function (){
                                var op=$("#cp_cf option:selected").val();
                                if(op==='n'){
                                    $("#ges_formato_cf").show();
                                    $("#ges_cf").hide();
                                }else if(op==='c'){
                                    $("#ges_formato_cf").hide();
                                    $("#ges_cf").show();
                                }
                            });
                        </script>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Configuraci&oacute;n</div>
                            <div id="CajaInput">
                                <?php 
                                if(!isset($_GET['id'])){
                                ?>
                                <select id="cp_conf" name="cp_conf" style="width: 70px">
                                    <option value="n">Nuevo</option>
                                    <option value="c">Copiar</option>
                                </select>&nbsp;
                                <?php
                                }
                                ?>                                
                                <select id="ges_conf" name="ges_conf" style="width: 100px;display: none;" >
                                    <?php
                                    $gestiones->reset();
                                    for($i=0;$i<$gestiones->get_num_registros();$i++){
                                        $gestion=$gestiones->get_objeto();
                                    ?>
                                    <option value="<?php echo $gestion->ges_id;?>"><?php echo $gestion->ges_descripcion;?></option>
                                    <?php                                 
                                        $gestiones->siguiente();
                                    } 
                                    ?>
                                </select>                                
                            </div>
                        </div>
                        <script>
                            $("#cp_conf").change(function (){
                                var op=$("#cp_conf option:selected").val();
                                if(op==='n'){                                    
                                    $("#ges_conf").hide();
                                }else if(op==='c'){                                    
                                    $("#ges_conf").show();
                                }
                            });
                        </script>
                        <!--Fin-->
                        <div class="Subtitulo">Monedas con las que se trabajar&aacute; la gesti&oacute;n</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Moneda</div>
                                <div id="CajaInput">
                                    <select name="ges_mon_id" class="caja_texto" id="ges_monedas_combo">
                                        <option value="">Seleccione</option>
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo_data("select mon_id as id,mon_titulo as nombre, mon_Simbolo as simbolo from con_moneda where mon_eliminado='No' order by mon_id asc", "simbolo", $_POST['comp_mon_id']);
                                        ?>
                                    </select>
                                </div>                                

                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div id="CajaInput">
                                    <input type="hidden" name="nfilas" id="nfilas" value="0">
                                    <input type="hidden" name="nfilasshadown" id="nfilasshadown" value="">
                                    <img src="images/boton_agregar.png" style='margin:0px 0px 0px 10px' onclick="javascript:datos_fila();">
                                </div>
                                <div id="CajaInput" style="margin:5px 0px 0px 10px;">
                                    <table  width="600"   class="tablaReporte" id="tges_moneda" cellpadding="0" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Titulo</th>
                                                <th>Simbolo</th>
                                                <th class="tOpciones">Eliminar</th>
                                            </tr>							
                                        </thead>
                                        <tbody>
                                            <?php
//                                            if ($ver || $cargar) {
//                                                $this->cargar_monedas_gestion($_GET['id']);
//                                            }
                                            $ges_monedas = $_POST['ges_monedas'];
                                            if ($ges_monedas == "") {
                                                $ges_monedas = "{}";
                                            }
                                            $monedas = json_decode($ges_monedas);
                                            foreach ($monedas as $moneda) {
                                                ?>
                                                <tr>
                                                    <td>
                                                        <input name="mon_id" type="hidden" value="<?php echo $moneda->id; ?>">                                                        
                                                        <span><?php echo $moneda->nombre; ?></span>
                                                    </td>
                                                    <td><?php echo $moneda->simbolo; ?></td>
                                                    <td><center><img style="float:none;" src="images/b_drop.png" class="img_del_mon"></center></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>  

                                        </tbody>                                        
                                    </table>
                                    <input type="hidden" id="ges_monedas" name="ges_monedas" value="">
                                </div>
                            </div>
                            <!--Fin-->
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
            </form>
        </div>
        <?php
    }

    function insertar_tcp() {
        $verificar = NEW VERIFICAR;

        $parametros[0] = array('ges_descripcion');
        $parametros[1] = array(trim($_POST['ges_descripcion']));
        $parametros[2] = array('con_gestion');

        if ($verificar->validar($parametros)) {
            $conec = new ADO();
            $fecha_i=  FUNCIONES::get_fecha_mysql($_POST['ges_fecha_inicio']);
            $fecha_f=  FUNCIONES::get_fecha_mysql($_POST['ges_fecha_fin']);
            $format_cuenta=$_POST['ges_formato_cuenta'];
            if($_POST['cp_cuenta']=='c'){
                $ges_cuenta=$_POST['ges_cuenta'];
                $format_cuenta=  FUNCIONES::atributo_bd("con_gestion", "ges_id='$ges_cuenta'", 'ges_formato_cuenta');
            }
            $format_cc=$_POST['ges_formato_cc'];
            if($_POST['cp_cc']=='c'){
                $ges_cc=$_POST['ges_cc'];
                $format_cc=  FUNCIONES::atributo_bd("con_gestion", "ges_id='$ges_cc'", 'ges_formato_cc');
            }
            $format_ca=$_POST['ges_formato_ca'];
            if($_POST['cp_ca']=='c'){
                $ges_ca=$_POST['ges_ca'];
                $format_ca=  FUNCIONES::atributo_bd("con_gestion", "ges_id='$ges_ca'", 'ges_formato_ca');
            }
            $format_cf=$_POST['ges_formato_cf'];
            if($_POST['cp_cf']=='c'){
                $ges_cf=$_POST['ges_cf'];
                $format_cf=  FUNCIONES::atributo_bd("con_gestion", "ges_id='$ges_cf'", 'ges_formato_cf');
            }
            $sql = "insert into con_gestion (ges_descripcion,ges_formato_cuenta,ges_formato_ca,ges_formato_cc,ges_formato_cf,ges_fecha_ini,ges_fecha_fin,ges_estado) 
			values ('" . $_POST['ges_descripcion'] . "','" . $format_cuenta . "','" . $format_cc . "','" . $format_ca . "','" . $format_cf . "','" . $fecha_i . "','" . $fecha_f . "','Abierto')";

            $conec->ejecutar($sql, false);
            $ges_id = mysql_insert_id();
            
            // Plan de Cuentas
            if($_POST['cp_cuenta']=='n'){
                $sql = "INSERT INTO `con_cuenta` (`cue_ges_id`, `cue_codigo`, `cue_descripcion`, `cue_tipo`, `cue_padre_id`, `cue_eliminado`, `cue_tree_left`, `cue_tree_right`, `cue_tree_position`, `cue_tree_level`) 
                                                            VALUES ($ges_id, '', 'Plan de Cuentas', 'Root', 1, 'No', '0', '1', 2, 0);";
                $conec->ejecutar($sql);
            }elseif($_POST['cp_cuenta']=='c'){
                $ges_cuenta=$_POST['ges_cuenta'];
                $this->copiar_cuentas($ges_id, $ges_cuenta);
            }
            //Centros de Costo
            if($_POST['cp_cc']=='n'){
                $sql = "INSERT INTO `con_cuenta_cc` (`cco_ges_id`, `cco_codigo`, `cco_descripcion`, `cco_tipo`, `cco_padre_id`, `cco_eliminado`, `cco_tree_left`, `cco_tree_right`, `cco_tree_position`, `cco_tree_level`) 
									VALUES ($ges_id, '', 'Centros de Costo', 'Root', 1, 'No', '0', '1', 2, 0) ;";
                $conec->ejecutar($sql);
            }elseif($_POST['cp_cc']=='c'){
                $ges_cc=$_POST['ges_cc'];
                $this->copiar_cuentas_cc($ges_id, $ges_cc);
            }
                

            //Cuentas Analiticas
            if($_POST['cp_ca']=='n'){
                $sql = "INSERT INTO `con_cuenta_ca` (`can_ges_id`, `can_codigo`, `can_descripcion`, `can_tipo`, `can_padre_id`, `can_eliminado`, `can_tree_left`, `can_tree_right`, `can_tree_position`, `can_tree_level`) 
									VALUES ($ges_id, '', 'Cuentas Analiticas', 'Root', 1, 'No', '0', '1', 2, 0) ;";
                $conec->ejecutar($sql);
            }elseif($_POST['cp_ca']=='c'){
                $ges_ca=$_POST['ges_ca'];
                $this->copiar_cuentas_ca($ges_id, $ges_ca);
            }
            //Cuentas de Flujo
            if($_POST['cp_cf']=='n'){
                $sql = "INSERT INTO `con_cuenta_cf` (`cfl_ges_id`, `cfl_codigo`, `cfl_descripcion`, `cfl_tipo`, `cfl_padre_id`, `cfl_eliminado`, `cfl_tree_left`, `cfl_tree_right`, `cfl_tree_position`, `cfl_tree_level`) 
									VALUES ($ges_id, '', 'Cuentas de Flujo', 'Root', 1, 'No', '0', '1', 2, 0) ;";
                $conec->ejecutar($sql);
            }elseif($_POST['cp_cf']=='c'){
                $ges_cf=$_POST['ges_cf'];
                $this->copiar_cuentas_cf($ges_id, $ges_cf);
            }
            //Configuracion de la Gestion
            if($_POST['cp_conf']=='n'){
                $this->copiar_configuracion($ges_id, 0);
            }elseif($_POST['cp_conf']=='c'){
                $ges_cf=$_POST['ges_conf'];
                $this->copiar_configuracion($ges_id, $ges_cf);
            }
            $mensaje = 'Gesti&oacute;n Agregado Correctamente';
            //insertar gestion moneda
            $ges_monedas = $_POST['ges_monedas'];
            $ges_monedas=  str_replace('\"', '"', $ges_monedas);
            $monedas = json_decode($ges_monedas);
            foreach ($monedas as $moneda) {
                $insert = "INSERT INTO con_gestion_moneda(gesmon_mon_id, gesmon_ges_id) VALUES
                        ($moneda->id, $ges_id);";
//                echo $insert;
                $conec->ejecutar($insert);
            }
        } else {
            $mensaje = 'La Gesti&oacute;n no puede ser agregado, por que ya existe una Gestion con ese nombre.';
        }

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }
    
    function copiar_cuentas($ges_id,$ges_cuenta){
        $sql="select * from con_cuenta where cue_ges_id='$ges_cuenta' order by cue_id";
        $cuentas=  FUNCIONES::objetos_bd_sql($sql);
        $max=  FUNCIONES::atributo_bd("con_cuenta", "1", "max(cue_id)");
        $inc_id=$max+1;
//        echo $inc_id."<br>";
        $_cuentas=array();
        $_padres=array();
        for($i=0;$i<$cuentas->get_num_registros();$i++){    
            $cuenta= clone $cuentas->get_objeto();
            $_padres[$cuenta->cue_id]=$inc_id;
            $cuenta->cue_id=$inc_id;
            $cuenta->cue_ges_id=$ges_id;
            if($cuenta->cue_padre_id>1){
                $cuenta->cue_padre_id=$_padres[$cuenta->cue_padre_id];
            }    
            $_cuentas[]=$cuenta;    
            $cuentas->siguiente();
            $inc_id++;
        }
        $conect=new ADO();
        for($i=0;$i<count($_cuentas);$i++){
            $cuenta=$_cuentas[$i];
            $sql="insert into con_cuenta(cue_id, cue_ges_id, cue_codigo,   cue_descripcion, cue_tipo, cue_padre_id, cue_eliminado, cue_mon_id, cue_tree_left, cue_tree_right, cue_tree_position, cue_tree_level)
                    values('$cuenta->cue_id','$cuenta->cue_ges_id','$cuenta->cue_codigo','$cuenta->cue_descripcion','$cuenta->cue_tipo','$cuenta->cue_padre_id','$cuenta->cue_eliminado','$cuenta->cue_mon_id','$cuenta->cue_tree_left','$cuenta->cue_tree_right','$cuenta->cue_tree_position','$cuenta->cue_tree_level');";
            $conect->ejecutar($sql);
        }       
    }
    
    function copiar_cuentas_ca($ges_id,$ges_ca){
        $sql="select * from con_cuenta_ca where can_ges_id='$ges_ca' order by can_id";
        $cuentas=  FUNCIONES::objetos_bd_sql($sql);
        $max=  FUNCIONES::atributo_bd("con_cuenta_ca", "1", "max(can_id)");
        $inc_id=$max+1;
//        echo $inc_id."<br>";
        $_cuentas=array();
        $_padres=array();
        for($i=0;$i<$cuentas->get_num_registros();$i++){    
            $cuenta= clone $cuentas->get_objeto();
            $_padres[$cuenta->can_id]=$inc_id;
            $cuenta->can_id=$inc_id;
            $cuenta->can_ges_id=$ges_id;
            if($cuenta->can_padre_id>1){
                $cuenta->can_padre_id=$_padres[$cuenta->can_padre_id];
            }    
            $_cuentas[]=$cuenta;    
            $cuentas->siguiente();
            $inc_id++;
        }
        $conect=new ADO();
        for($i=0;$i<count($_cuentas);$i++){
            $cuenta=$_cuentas[$i];
            $sql="insert into con_cuenta_ca(can_id, can_ges_id, can_codigo,   can_descripcion, can_tipo, can_padre_id, can_eliminado, can_mon_id, can_tree_left, can_tree_right, can_tree_position, can_tree_level)
                    values('$cuenta->can_id','$cuenta->can_ges_id','$cuenta->can_codigo','$cuenta->can_descripcion','$cuenta->can_tipo','$cuenta->can_padre_id','$cuenta->can_eliminado','$cuenta->can_mon_id','$cuenta->can_tree_left','$cuenta->can_tree_right','$cuenta->can_tree_position','$cuenta->can_tree_level');";
            $conect->ejecutar($sql);
        }
    }
    
    function copiar_cuentas_cc($ges_id,$ges_cc){
        $sql="select * from con_cuenta_cc where cco_ges_id='$ges_cc' order by cco_id";
        $cuentas=  FUNCIONES::objetos_bd_sql($sql);
        $max=  FUNCIONES::atributo_bd("con_cuenta_cc", "1", "max(cco_id)");
        $inc_id=$max+1;
//        echo $inc_id."<br>";
        $_cuentas=array();
        $_padres=array();
        for($i=0;$i<$cuentas->get_num_registros();$i++){    
            $cuenta= clone $cuentas->get_objeto();
            $_padres[$cuenta->cco_id]=$inc_id;
            $cuenta->cco_id=$inc_id;
            $cuenta->cco_ges_id=$ges_id;
            if($cuenta->cco_padre_id>1){
                $cuenta->cco_padre_id=$_padres[$cuenta->cco_padre_id];
            }    
            $_cuentas[]=$cuenta;    
            $cuentas->siguiente();
            $inc_id++;
        }
        $conect=new ADO();
        for($i=0;$i<count($_cuentas);$i++){
            $cuenta=$_cuentas[$i];
            $sql="insert into con_cuenta_cc(cco_id, cco_ges_id, cco_codigo,   cco_descripcion, cco_tipo, cco_padre_id, cco_eliminado, cco_mon_id, cco_tree_left, cco_tree_right, cco_tree_position, cco_tree_level)
                    values('$cuenta->cco_id','$cuenta->cco_ges_id','$cuenta->cco_codigo','$cuenta->cco_descripcion','$cuenta->cco_tipo','$cuenta->cco_padre_id','$cuenta->cco_eliminado','$cuenta->cco_mon_id','$cuenta->cco_tree_left','$cuenta->cco_tree_right','$cuenta->cco_tree_position','$cuenta->cco_tree_level');";
            $conect->ejecutar($sql);
        }
    }
    function copiar_cuentas_cf($ges_id,$ges_cf){
        $sql="select * from con_cuenta_cf where cfl_ges_id='$ges_cf' order by cfl_id";
        $cuentas=  FUNCIONES::objetos_bd_sql($sql);
        $max=  FUNCIONES::atributo_bd("con_cuenta_cf", "1", "max(cfl_id)");
        $inc_id=$max+1;
//        echo $inc_id."<br>";
        $_cuentas=array();
        $_padres=array();
        for($i=0;$i<$cuentas->get_num_registros();$i++){    
            $cuenta= clone $cuentas->get_objeto();
            $_padres[$cuenta->cfl_id]=$inc_id;
            $cuenta->cfl_id=$inc_id;
            $cuenta->cfl_ges_id=$ges_id;
            if($cuenta->cfl_padre_id>1){
                $cuenta->cfl_padre_id=$_padres[$cuenta->cfl_padre_id];
            }    
            $_cuentas[]=$cuenta;    
            $cuentas->siguiente();
            $inc_id++;
        }
        $conect=new ADO();
        for($i=0;$i<count($_cuentas);$i++){
            $cuenta=$_cuentas[$i];
            $sql="insert into con_cuenta_cf(cfl_id, cfl_ges_id, cfl_codigo,   cfl_descripcion, cfl_tipo, cfl_padre_id, cfl_eliminado, cfl_mon_id, cfl_tree_left, cfl_tree_right, cfl_tree_position, cfl_tree_level)
                    values('$cuenta->cfl_id','$cuenta->cfl_ges_id','$cuenta->cfl_codigo','$cuenta->cfl_descripcion','$cuenta->cfl_tipo','$cuenta->cfl_padre_id','$cuenta->cfl_eliminado','$cuenta->cfl_mon_id','$cuenta->cfl_tree_left','$cuenta->cfl_tree_right','$cuenta->cfl_tree_position','$cuenta->cfl_tree_level');";
            $conect->ejecutar($sql);
        }
    }
    function copiar_configuracion($ges_id, $ges_conf) {
        $sql = "select * from con_configuracion where conf_ges_id='$ges_conf' order by conf_orden";    
        $configs = FUNCIONES::objetos_bd_sql($sql);
        $conect=new ADO();
        for ($i = 0; $i < $configs->get_num_registros(); $i++) {    
            $conf=$configs->get_objeto();
            $insert = "INSERT INTO `con_configuracion` (`conf_orden`,`conf_nombre`, `conf_valor`, `conf_tconf_id`, `conf_editable`, `conf_ges_id`, `conf_eliminado`) 
                        VALUES ('$conf->conf_orden','$conf->conf_nombre', '$conf->conf_valor', '$conf->conf_tconf_id', '$conf->conf_editable', '$ges_id', 'No');";
            $conect->ejecutar($insert);
            $configs->siguiente();
        }
    }

    function cargar_monedas_gestion($id_gestion) {
        $conec = new ADO();

        $sql = "select * from con_gestion_moneda
		inner join con_moneda on (mon_id=gesmon_mon_id) where gesmon_ges_id='" . $id_gestion . "'";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $cad = "";

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            ?>
            <tr><td><input name="comu[]" type="hidden" value="<?php echo $objeto->gesmon_mon_id; ?>"><input name="comu_urbanizacion[]" type="hidden" value="<?php $objeto->gesmon_mon_id; ?>"><?php echo $objeto->mon_titulo; ?></td><td><?php echo $objeto->mon_Simbolo; ?></td><td><center><img style="float:none;" src="images/b_drop.png" onclick="javascript:remove(this);"></center></td></tr>
            <?php
            $conec->siguiente();
        }
        ?>
        <script>
                                    document.frm_vendedor.nfilas.value =<?php echo $num; ?>;
                                    document.frm_vendedor.nfilasshadown.value =<?php echo $num; ?>;
        </script>
        <?php
    }

    function modificar_tcp() {
        $verificar = NEW VERIFICAR;

        $parametros[0] = array('ges_descripcion');
        $parametros[1] = array(trim($_POST['ges_descripcion']));
        $parametros[2] = array('con_gestion');
        $parametros[3] = array(" and ges_id <> '" . $_GET['id'] . "' ");

        $conversor = new convertir();

        if ($verificar->validar($parametros)) {
            $conec = new ADO();

            $sql = "update con_gestion set 
                    ges_descripcion='" . $_POST['ges_descripcion'] . "',
                    ges_formato_cuenta='" . $_POST['ges_formato_cuenta'] . "',
                    ges_formato_ca='" . $_POST['ges_formato_ca'] . "',
                    ges_formato_cc='" . $_POST['ges_formato_cc'] . "',
                    ges_formato_cf='" . $_POST['ges_formato_cf'] . "',
                    ges_fecha_ini='" . $conversor->get_fecha_mysql($_POST['ges_fecha_inicio']) . "',
                    ges_fecha_fin='" . $conversor->get_fecha_mysql($_POST['ges_fecha_fin']) . "'
                    where ges_id='" . $_GET['id'] . "'";

            $conec->ejecutar($sql);
            $ges_id=$_GET['id'];
            //eliminar mondedas dela gestion =id
            $delete="DELETE FROM con_gestion_moneda WHERE gesmon_ges_id=".$ges_id;
            $conec->ejecutar($delete);
            //insertar gestion moneda
            $ges_monedas = $_POST['ges_monedas'];
            $ges_monedas=  str_replace('\"', '"', $ges_monedas);
            $monedas = json_decode($ges_monedas);
            foreach ($monedas as $moneda) {
                $insert = "INSERT INTO con_gestion_moneda(gesmon_mon_id, gesmon_ges_id) VALUES
                            ($moneda->id, $ges_id);";                
//                echo $insert;
                $conec->ejecutar($insert);
            }
            $mensaje = 'Gestion Modificado Correctamente';
        } else {
            $mensaje = 'La Gestion no puede ser modificado, por que ya existe una Gestion con ese nombre.';
        }

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function formulario_confirmar_eliminacion() {

        $mensaje = 'Esta seguro de eliminar el usuario?';

        $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'ges_id');
    }

    function eliminar_tcp() {
        /*
          $verificar=NEW VERIFICAR;
          $parametros[0]=array('log_ges_id');
          $parametros[1]=array($_POST['ges_id']);
          $parametros[2]=array('ad_logs');

          if($verificar->validar($parametros))
          {
         */
        
        $cantidad=  FUNCIONES::atributo_bd("con_comprobante", "cmp_ges_id='".$_POST['ges_id']."' and cmp_eliminado='No'", 'count(*)');
        if($cantidad==0){
//            echo 'elmino';
//            return;
            $conec = new ADO();
            
            // borrar gestion 
            $sql = "delete from con_gestion where ges_id='" . $_POST['ges_id'] . "'";
            $conec->ejecutar($sql);

            // Plan de Cuentas
            $sql = "delete from con_cuenta where cue_ges_id='" . $_POST['ges_id'] . "'";
            $conec->ejecutar($sql);

            //Centros de Costo
            $sql = "delete from  con_cuenta_cc where cco_ges_id='" . $_POST['ges_id'] . "'";
            $conec->ejecutar($sql);

            //Cuentas Analiticas
            $sql = "delete from con_cuenta_ca where can_ges_id='" . $_POST['ges_id'] . "'";
            $conec->ejecutar($sql);

            //Cuentas de Flujo
            $sql = "delete from con_cuenta_cf where cfl_ges_id='" . $_POST['ges_id'] . "'";
            $conec->ejecutar($sql);        
            $mensaje = 'Gesti&oacute;n Eliminado Correctamente.';
        }else{
            $mensaje = 'La Gestion No se puede Eliminar ya que existen <b>Comprobantes</b> realizados en dicha gestion.';
        }
        /*
          }
          else
          {
          $mensaje='El usuario no puede ser eliminado, por que ya realizo varias acciones en el sistema.';
          }
         */
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

/////////////////////////////////////////////--- TAREA : PERIODOS ---/////////////////////////////////////////

    function guardar_periodo() {
        include_once("clases/registrar_control_numero.class.php");

        $control_numero = new CONTROL_NUMERO();

        $conec = new ADO();
        $fecha_i=  FUNCIONES::get_fecha_mysql($_POST['peri_fecha_inicio']);
        $fecha_f=  FUNCIONES::get_fecha_mysql($_POST['peri_fecha_fin']);
        $sql = "insert into con_periodo(pdo_descripcion,pdo_fecha_inicio,pdo_fecha_fin,pdo_estado,pdo_ges_id)
		values ('" . $_POST['peri_descripcion'] . "','" . $fecha_i . "','" . $fecha_f . "','" . $_POST['peri_estado'] . "','" . $_GET['id'] . "')";

        $conec->ejecutar($sql, false);

        $llave = mysql_insert_id();

        $mes = substr($fecha_i, 5, 2);
        $anno = substr($fecha_i, 0, 4);

        $sql = "SELECT tco_id FROM con_tipo_comprobante";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();

            $control_numero->insertar_control_numero($llave, $objeto->tco_id, 1);

            $conec->siguiente();
        }
    }

    function eliminar_producto($id_producto) {

        $verificar = NEW VERIFICAR;

        $parametros[0] = array('vde_pro_id');
        $parametros[1] = array($id_producto);
        $parametros[2] = array('venta_detalle');

        if ($verificar->validar($parametros)) {
            $parametros[0] = array('plo_pro_id');
            $parametros[1] = array($id_producto);
            $parametros[2] = array('producto_lote');

            if ($verificar->validar($parametros)) {
                $conec = new ADO();

                $sql = "delete from producto where pro_id='" . $id_producto . "'";

                $conec->ejecutar($sql);

                $this->mensaje = 'Producto eliminado correctamente';
            } else {
                $this->mensaje = 'El producto no puede ser eliminado, por que esta siendo utilizado en el modulo ingreso de productos.';
            }
        } else {
            $this->mensaje = 'El producto no puede ser eliminado, por que esta siendo utilizado en el modulo ventas.';
        }
    }

    function modificar_periodo() {

        $conec = new ADO();
        $fecha_i=  FUNCIONES::get_fecha_mysql($_POST['peri_fecha_inicio']);
        $fecha_f=  FUNCIONES::get_fecha_mysql($_POST['peri_fecha_fin']);
        $sql = "update con_periodo set pdo_descripcion='".$_POST['peri_descripcion']."', pdo_fecha_inicio='$fecha_i', pdo_fecha_fin='$fecha_f', pdo_estado='".$_POST['peri_estado']."'
                where pdo_id='".$_GET['peri_id']."'";

        $conec->ejecutar($sql);
        $mensaje = 'Periodo Modificado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
    }
    function eliminar_periodo() {
        $cantidad=  FUNCIONES::atributo_bd("con_comprobante", "cmp_peri_id='".$_GET['peri_id']."' and cmp_eliminado='No'", 'count(*)');
        if($cantidad==0){
            $conec = new ADO();
            $sql = "update con_periodo set pdo_eliminado='Si'
                    where pdo_id='".$_GET['peri_id']."'";
            $conec->ejecutar($sql);
        }else{
            $mensaje = 'El Periodo No se puede Eliminar ya que existen <b>Comprobantes</b> realizados en dicho Periodo.';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }

        
        //$mensaje = 'Periodo Eliminado Correctamente';
        //$this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
    }

    function limpiar() {
        $_POST['pro_codigo'] = "";
        $_POST['pro_nombre'] = "";
        $_POST['pro_descripcion'] = "";
        $_POST['pro_precio'] = "";
        $_POST['pro_precio_cre'] = "";
        $_POST['pro_precio_com'] = "";
        $_POST['pro_cant_min'] = "";
        $_POST['pro_unidad_medida'] = "";
    }

    function cargar_datos_periodo() {
        $conec = new ADO();

        $sql = "select * from con_periodo where pdo_id ='" . $_GET['peri_id'] . "'";
        //echo $sql;
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        $_POST['peri_id'] = $objeto->pdo_id;
        $_POST['peri_descripcion'] = $objeto->pdo_descripcion;
        $_POST['peri_fecha_inicio'] = FUNCIONES::get_fecha_latina($objeto->pdo_fecha_inicio);
        $_POST['peri_fecha_fin'] = FUNCIONES::get_fecha_latina($objeto->pdo_fecha_fin);
        $_POST['peri_estado'] = $objeto->pdo_estado;
        $_POST['peri_ges_id'] = $objeto->pdo_ges_id;
    }

    function datos_periodo() {

        if ($_POST) {
            require_once('clases/validar.class.php');

            $i = 0;
            $valores[$i]["etiqueta"] = "Nombre";
            $valores[$i]["valor"] = $_POST['peri_descripcion'];
            $valores[$i]["tipo"] = "todo";
            $valores[$i]["requerido"] = true;
            $i++;
            $valores[$i]["etiqueta"] = "Fecha Inicio";
            $valores[$i]["valor"] = FUNCIONES::get_fecha_mysql($_POST['peri_fecha_inicio']);
            $valores[$i]["tipo"] = "fecha";
            $valores[$i]["requerido"] = true;
            $i++;
            $valores[$i]["etiqueta"] = "Fecha Fin";
            $valores[$i]["valor"] = FUNCIONES::get_fecha_mysql($_POST['peri_fecha_fin']);
            $valores[$i]["tipo"] = "fecha";
            $valores[$i]["requerido"] = true;
            $i++;
            $valores[$i]["etiqueta"] = "Estado";
            $valores[$i]["valor"] = $_POST['peri_estado'];
            $valores[$i]["tipo"] = "numero";
            $valores[$i]["requerido"] = true;

            $val = NEW VALIDADOR;
            $rango='';
            if($_GET['acc'] != 'MODIFICAR_PERIODO'){
                $rango = $this->validar_rango_fechas(FUNCIONES::get_fecha_mysql($_POST['peri_fecha_inicio']), FUNCIONES::get_fecha_mysql($_POST['peri_fecha_fin']));
                $val->mensaje.=$rango;
            }
            
            $this->mensaje = "";            
            if ($val->validar($valores) && !$rango) {
                return true;
            } else {
                $this->mensaje = $val->mensaje;
                return false;
            }
        }
        return false;
    }
    function validar_rango_fechas($fechai,$fechaf){
        $periodos=  FUNCIONES::objetos_bd("con_periodo", "pdo_ges_id='".$_GET['id']."' and pdo_eliminado='No'");
        
        for($i=0;$i<$periodos->get_num_registros();$i++){
            $pdo=$periodos->get_objeto();
//            echo "$fechai - $fechaf |-| $pdo->pdo_fecha_inicio - $pdo->pdo_fecha_fin<br>";
            if($pdo->pdo_fecha_inicio<=$fechai && $pdo->pdo_fecha_fin>=$fechai){
//                echo "<li>La <b>fecha de inicio</b> se encuentra en el rango del periodo <b>$pdo->pdo_descripcion</b></li>" ;
                return "<li>La <b>fecha de inicio</b> se encuentra en el rango del periodo <b>$pdo->pdo_descripcion</b></li>" ;
            }
            if($pdo->pdo_fecha_inicio<=$fechaf && $pdo->pdo_fecha_fin>=$fechaf){
//                echo "<li>La <b>fecha de fin</b> se encuentra en el rango del periodo <b>$pdo->pdo_descripcion</b></li>" ;
                return "<li>La <b>fecha de fin</b> se encuentra en el rango del periodo <b>$pdo->pdo_descripcion</b></li>" ;
            }
            $periodos->siguiente();
        }
        
        return '';
    }
    function nombre_categoria($id_categoria) {
        $conec = new ADO();

        $sql = "SELECT fam_id,fam_descripcion FROM familia WHERE fam_id=" . $id_categoria;

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $objeto = $conec->get_objeto();

        return $objeto->fam_descripcion;
    }

    function formulario_tcp_periodo($tipo) {

        $url = $this->link . '?mod=' . $this->modulo;

        $re = $url;

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

        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }

        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }

        if ($_GET['acc'] == 'MODIFICAR_PERIODO') {
            $url.='&acc=MODIFICAR_PERIODO';
        }

        $this->formulario->dibujar_tarea('PERIODOS');

        if ($this->mensaje <> "") {
            $this->formulario->dibujar_mensaje($this->mensaje);
        }
        ?>


        <div id="Contenedor_NuevaSentencia">
            <script>
                function Validar(e)
                {
                    evt = e ? e : event;
                    tcl = (window.Event) ? evt.which : evt.keyCode;
                    if (tcl == 13)
                    {
                        return false;
                    }
                    return true;
                }
            </script>
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url . '&peri_id=' . $_GET['peri_id']; ?>" method="POST" enctype="multipart/form-data">

                <div id="FormSent">



                    <div class="Subtitulo">Periodos <?php //echo $this->nombre_categoria($_GET['id']);            ?> </div>

                    <div id="ContenedorSeleccion">

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Nombre</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="peri_descripcion" id="peri_descripcion" size="20" value="<?php echo $_POST['peri_descripcion']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span>Fecha Inicio</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" name="peri_fecha_inicio" id="peri_fecha_inicio" size="12" value="<?php
        if ($_POST['peri_fecha_inicio'] != '')
            echo $_POST['peri_fecha_inicio'];
        else
            echo date('d/m/Y');
        ?>" type="text">
                                <input name="but_fecha_pago" id="but_fecha_pago" class="boton_fecha" value="..." type="button">
                                <script type="text/javascript">
//                                    Calendar.setup({inputField: "peri_fecha_inicio"
//                                                , ifFormat: "%d/%m/%Y",
//                                        button: "but_fecha_pago"
//                                    });
                                </script>
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span>Fecha Fin</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" name="peri_fecha_fin" id="peri_fecha_fin" size="12" value="<?php
                        if ($_POST['peri_fecha_fin'] != '')
                            echo $_POST['peri_fecha_fin'];
                        else
                            echo date('d/m/Y');
        ?>" type="text">
                                <input name="but_fecha_pago2" id="but_fecha_pago2" class="boton_fecha" value="..." type="button">
                                <script type="text/javascript">
//                                    Calendar.setup({inputField: "peri_fecha_fin"
//                                                , ifFormat: "%d/%m/%Y",
//                                        button: "but_fecha_pago2"
//                                    });

                                    $('#peri_fecha_inicio').mask('99/99/9999');
                                    $('#peri_fecha_fin').mask('99/99/9999');
                                </script>
                            </div>
                        </div>
                        <!--Fin-->



                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Estado</div>
                            <div id="CajaInput">
                                <select name="peri_estado" class="caja_texto">
                                    <option value="1" <?php if ($_POST['peri_estado'] == 'Abierto') echo 'selected="selected"'; ?>>Abierto</option>
                                    <option value="2" <?php if ($_POST['peri_estado'] == 'Cerrado') echo 'selected="selected"'; ?>>Cerrado</option>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->

                    </div>

                    <div id="ContenedorDiv">

                        <div id="CajaBotones">

                            <center>

                                <input type="submit" class="boton" name="" value="Enviar">

                                <input type="reset" class="boton" name="" value="Cancelar">

                                <input type="button" class="boton" name="" value="Volver" onclick="location.href = '<?php
                        if ($_GET['acc'] == "MODIFICAR_PERIODO")
                            echo $this->link . '?mod=' . $this->modulo . "&tarea=PERIODOS&id=" . $_GET['id'];
                        else
                            echo $this->link . '?mod=' . $this->modulo;
        ?>';">

                            </center>

                        </div>

                    </div>

                </div>

        </div>

        <?php
    }

    function dibujar_encabezado_periodo() {
        ?><div style="clear:both;"></div><center>

            <table class="tablaLista" cellpadding="0" cellspacing="0" width="60%">
                <thead>
                    <tr>

                        <th >

                            Gestion

                        </th>

                        <th >

                            Periodo

                        </th>

                        <th >

                            Fecha Inicio

                        </th>

                        <th >

                            Fecha Fin

                        </th>

                        <th >

                            Estado

                        </th>

                        <th class="tOpciones" width="100px">

                            Opciones

                        </th>

                    </tr>
                </thead>
                <tbody>
        <?PHP
    }

    function mostrar_busqueda_periodo() {
        $convertir = new convertir();

        $conec = new ADO();

        $sql = "SELECT * from con_periodo
		inner join con_gestion on (ges_id=pdo_ges_id)
		where pdo_ges_id='" . $_GET['id'] . "' and pdo_eliminado='No'";

        //echo $sql;

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();
            echo '<tr class="busqueda_campos">';
            ?>
                    <td align="left">
            <?php echo $objeto->ges_descripcion; ?>
                    </td>
                    <td align="left">
            <?php echo $objeto->pdo_descripcion; ?>
                    </td>
                    <td align="left">
            <?php echo $convertir->get_fecha_latina($objeto->pdo_fecha_inicio); ?>
                    </td>
                    <td align="left">
            <?php echo $convertir->get_fecha_latina($objeto->pdo_fecha_fin); ?>
                    </td>
                    <td align="left">
            <?php echo $objeto->pdo_estado; ?>
                    </td>
                    <td>
                    <center>
                        <table>
                            <tr>
                                <td><a href="gestor.php?mod=gestion&tarea=PERIODOS&acc=MODIFICAR_PERIODO&peri_id=<?php echo $objeto->pdo_id; ?>&id=<?php echo $_GET['id']; ?>"><img src="images/b_edit.png" alt="MODIFICAR" title="MODIFICAR" border="0"></a></td>
                                <td><a href="gestor.php?mod=gestion&tarea=PERIODOS&acc=ELIMINAR_PERIODO&peri_id=<?php echo $objeto->pdo_id; ?>&id=<?php echo $_GET['id']; ?>"><img src="images/b_drop.png" alt="ELIMINAR" title="ELIMINAR" border="0"></a></td>   
                            </tr>
                        </table>
                    </center>
                    </td>

            <?php
            echo "</tr>";





            $conec->siguiente();
        }

        echo "</tbody></table></center><br>";
    }

    function formulario_tcp_cuenta($tipo) {
    ?>

    <?php
            }

            function formulario_tabs() {
                if (is_numeric($_GET['id'])) {
                    if (!isset($_GET['p'])) {
                        $_GET['p'] = 'cuenta';
                    }

                    $conec = new ADO();

                    $sql = "select * from  con_gestion where ges_id ='" . $_GET['id'] . "'";

                    $conec->ejecutar($sql);

                    $objGestion = $conec->get_objeto();

                    $cod_codigo = "";
                    $treeURL = "";
                    $label_id = "";
                    $label_codigo = "";
                    $label_tree_position = "";
                    $label_descripcion = "";
                    $label_ges_id = "";
                    $label_tipo = "";
                    $label_mon_id = "";
                    $t_cuenta="";                    
                    $formato= array();
                    switch ($_GET['p']) {
                        case "cuenta":

                            $cod_codigo = $objGestion->ges_formato_cuenta;
                            $treeURL = "sueltos/tree/con_cuenta.php";
                            $label_id = "cue_id";
                            $label_codigo = "cue_codigo";
                            $label_tree_position = "cue_tree_position";
                            $label_descripcion = "cue_descripcion";
                            $label_ges_id = "cue_ges_id";
                            $label_tipo = "cue_tipo";
                            $label_mon_id = "cue_mon_id";
                            $t_cuenta="cue";
                            $formato= explode(".", $objGestion->ges_formato_cuenta);
                            break;
                        case "cc":

                            $cod_codigo = $objGestion->ges_formato_cc; //ges_formato_cc
                            $treeURL = "sueltos/tree/con_cuenta_cc.php";
                            $label_id = "cco_id";
                            $label_codigo = "cco_codigo";
                            $label_tree_position = "cco_tree_position";
                            $label_descripcion = "cco_descripcion";
                            $label_ges_id = "cco_ges_id";
                            $label_tipo = "cco_tipo";
                            $label_mon_id = "cco_mon_id";
                            $t_cuenta="cco";
                            $formato= explode(".", $objGestion->ges_formato_cc);
                            break;
                        case "ca":

                            $cod_codigo = $objGestion->ges_formato_ca;
                            $treeURL = "sueltos/tree/con_cuenta_ca.php";
                            $label_id = "can_id";
                            $label_codigo = "can_codigo";
                            $label_tree_position = "can_tree_position";
                            $label_descripcion = "can_descripcion";
                            $label_ges_id = "can_ges_id";
                            $label_tipo = "can_tipo";
                            $label_mon_id = "can_mon_id";
                            $t_cuenta="can";
                            $formato= explode(".", $objGestion->ges_formato_ca);
                            break;
                        case "cf":

                            $cod_codigo = $objGestion->ges_formato_cf; //ges_formato_cc
                            $treeURL = "sueltos/tree/con_cuenta_cf.php";
                            $label_id = "cfl_id";
                            $label_codigo = "cfl_codigo";
                            $label_tree_position = "cfl_tree_position";
                            $label_descripcion = "cfl_descripcion";
                            $label_ges_id = "cfl_ges_id";
                            $label_tipo = "cfl_tipo";
                            $label_mon_id = "cfl_mon_id";
                            $t_cuenta="cfl";
                            $formato= explode(".", $objGestion->ges_formato_cf);
                            break;
                    }

                    //$cue_codigo = "#.#.#.##.###"; //ges_formato_cuenta
                    ?>
                    <!-- Menu treen -->
                    <script type="text/javascript" src="js/jquery.hotkeys.js"></script>
                    <script type="text/javascript" src="js/jquery.jstree.js"></script>

                    <script type="text/javascript" src="js/jquery.maskedinput.min.js"></script>

                    <link rel="stylesheet" href="js/toolltip/tip-yellow/tip-yellow.css" type="text/css">
                    <script type="text/javascript" src="js/toolltip/jquery.poshytip.js"></script>
                    <!--AutoSugest-->
                    <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>                    
                    <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
                    <!--AutoSugest-->

                    <script type="text/javascript">
                        <?php
                        $treeCodigo = str_replace("#", "9", $cod_codigo);
                        ?>

                        var treeURL = "<?php echo $treeURL; ?>";
                        var treeCodigo = "<?php echo $treeCodigo; ?>";
                        var treePrimeraVez = false;

                        var treeActivo = 0;
                        var treeTipo = 0;

                        var cue_codigo = "";
                        var cue_descripcion = "";

                        $(document).ready(function() {

                            $("#cue_codigo").mask(treeCodigo);

                            $('#cue_codigo').poshytip({
                                content: 'Escriba el codigo <b><?php echo $cod_codigo; ?></b>',
                                showOn: 'focus',
                                alignTo: 'target',
                                alignX: 'right',
                                alignY: 'center',
                                offsetX: 5,
                                showTimeout: 100
                            });

                            $('#cue_descripcion').poshytip({
                                content: 'Escriba una descripccion',
                                showOn: 'focus',
                                alignTo: 'target',
                                alignX: 'right',
                                alignY: 'center',
                                offsetX: 5,
                                showTimeout: 100
                            });

                            $('#cue_tipo').poshytip({
                                content: 'Por favor seleccione tipo',
                                showOn: 'focus',
                                alignTo: 'target',
                                alignX: 'right',
                                alignY: 'center',
                                offsetX: 5,
                                showTimeout: 100
                            });

                            $('#comp_mon_id').poshytip({
                                content: 'Por favor seleccione moneda',
                                showOn: 'focus',
                                alignTo: 'target',
                                alignX: 'right',
                                alignY: 'center',
                                offsetX: 5,
                                showTimeout: 100
                            });

                            $("#plancuenta").jstree({
                                "plugins": [
                                    "themes", "json_data", "ui", "crrm", "dnd", "search", "types", "hotkeys"
                                ],
                                "json_data": {
                                    "ajax": {
                                        "url": treeURL,
                                        "data": function(n) {
                                            return {
                                                "operation": "get_children",
                                                "<?php echo $label_ges_id; ?>": $("#cue_ges_id").val(),
                                                "<?php echo $label_id; ?>" : n.attr ? n.attr("id").replace("node_", "") : 1
                                            };
                                        }
                                    }
                                },
                                "search": {
                                    "ajax": {
                                        "url": treeURL,
                                        "data": function(str) {
                                            return {
                                                "operation": "search",
                                                "search_str": str
                                            };
                                        }
                                    }
                                },
                                "types": {
                                    "max_depth": -2,
                                    "max_children": -2,
                                    "valid_children": ["Root"],
                                    "types": {
                                        "default": {
                                            "valid_children": "none",
                                            "icon": {
                                                "image": "./js/themes/file.png"
                                            }
                                        },
                                        "Titulo": {
                                            "valid_children": ["default", "folder"],
                                            "icon": {
                                                "image": "./js/themes/folder.png"
                                            }
                                        },
                                        "folder": {
                                            "valid_children": ["default", "folder"],
                                            "icon": {
                                                "image": "./js/themes/folder.png"
                                            }
                                        },
                                        "Root": {
                                            "valid_children": ["default", "folder"],
                                            "icon": {
                                                "image": "./js/themes/root.png"
                                            },
                                            "start_drag": false,
                                            "move_node": false,
                                            "delete_node": false,
                                            "remove": false
                                        }
                                    }
                                },
                                "ui": {
                                    "initially_select": ["node_32"]
                                },
                                "core": {
                                    "initially_open": ["node_32", "node_3"]
                                }
                            })
                            .bind("create.jstree", function(e, data) {
        //                                                    alert(data.rslt.parent.attr("id").replace("node_", ""));
                                console.log(data);
                                    
                                $.post(
                                        treeURL,
                                        {
                                            "operation": "create_node",
                                            "<?php echo $label_id; ?>": data.rslt.parent.attr("id").replace("node_", ""),
                                            "<?php echo $label_tree_position; ?>" : data.rslt.position,
                                            "<?php echo $label_descripcion; ?>" : $("#cue_descripcion").val(),
                                            "<?php echo $label_codigo; ?>" : $("#cue_codigo").val(),
                                            "<?php echo $label_ges_id; ?>" : $("#cue_ges_id").val(),
                                            "<?php echo $label_mon_id; ?>" : $("#cue_mon_id option:selected").val().toString(),
                                            "<?php echo $label_tipo; ?>" : $("#cue_tipo option:selected").val().toString()
                                        },
                                        function(r) {
                                            if (r.status) {
                                                //$(data.rslt.obj).attr("id", "node_" + r.id);
                                                tree_form_borrar();
                                                data.inst.refresh();
                                            }
                                        }
                                );

                            })
                                    .bind("remove.jstree", function(e, data) {
                                data.rslt.obj.each(function() {
                                    $.ajax({
                                        async: false,
                                        type: 'POST',
                                        url: treeURL,
                                        data: {
                                            "operation": "remove_node",
                                            "<?php echo $label_ges_id; ?>": $("#cue_ges_id").val(),
                                            "<?php echo $label_id; ?>" : data.rslt.obj.attr("id").replace("node_", "")
                                        },
                                        success: function(r) {
                                            /*
                                             if(!r.status) {
                                             data.inst.refresh();
                                             }
                                             */
                                        }
                                    });
                                });
                            });

                            function tree_form_validar()
                            {
                                cue_codigo = $("#cue_codigo").val();
                                cue_descripcion = $("#cue_descripcion").val();
                                if (cue_codigo != "")
                                {
                                    if (cue_descripcion != "")
                                    {
                                        var cue_tipoCombo = $("#cue_tipo option:selected").val().toString();
                                        if (cue_tipoCombo != "")
                                        {
                                            return true;
                                        } else {
                                            $("#cue_tipo").focus();
                                            $('#cue_tipo').poshytip('show');
                                            return false;
                                        }
                                    } else {
                                        $("#cue_descripcion").focus();
                                        $('#cue_descripcion').poshytip('show');
                                        return false;
                                    }
                                } else {
                                    $("#cue_codigo").focus();
                                    $('#cue_codigo').poshytip('show');
                                    return false;
                                }
                            }

                            function tree_form_renombrar()
                            {
                                var nodo_sel=$(".jstree-clicked");
                                if(nodo_sel.size()===0){
                                    $.prompt("Seleccione una cuenta");
                                    return false;
                                }else if(nodo_sel.size()>1){
                                    $.prompt("Seleccione solo una cuenta");
                                    return false;
                                }
                                cue_codigo = $("#cue_codigo").val();
                                cue_descripcion = $("#cue_descripcion").val();

                                $.post(
                                        treeURL,
                                        {
                                            "operation": "rename_node",
                                            "<?php echo $label_id; ?>": treeActivo,
                                            "<?php echo $label_codigo; ?>" : cue_codigo,
                                            "<?php echo $label_ges_id; ?>" : $("#ges_id").val(),
                                            "<?php echo $label_mon_id; ?>" : $("#cue_mon_id option:selected").val().toString(),
                                            "<?php echo $label_descripcion; ?>" : cue_descripcion
                                        },
                                        function(r) {
                                            if (r.status) {
                                                $('#plancuenta').jstree('refresh', -1);
                                            }else{
                                                $.prompt(r.msj);
                                            }
                                        }
                                );
                            }
                            function tree_form_guardar_nuevo(){
                                var nodo_sel=$(".jstree-clicked");
                                if(nodo_sel.size()===0){
                                    $.prompt("Seleccione una cuenta");
                                    return false;
                                }else if(nodo_sel.size()>1){
                                    $.prompt("Seleccione solo una cuenta");
                                    return false;
                                }
                                var rel=nodo_sel.parent().attr('rel');                                
                                if(rel==='Movimiento'){
                                    $.prompt("No puede agregar sub-cuentas a una cuenta movimiento");
                                    return false;
                                }
                                var tipo=$("#cue_tipo option:selected").val();
                                var level=nodo_sel.parent().attr('data-level');
                                var max_level=$("#max_level").val();
                                if(tipo==='Movimiento' && level<max_level-1){
                                    $.prompt("No puede agregar una cuenta de <b>movimiento</b> en el nivel "+(level*1+1));
                                    return false;
                                }
                                if(tipo==='Titulo' && level>=max_level-1){
                                    $.prompt("No puede agregar una cuenta de <b>Titulo</b> en el nivel "+(level*1+1));
                                    return false;
                                }
                                
                                var id=nodo_sel.parent().attr("id").replace("node_", "");
                                var position=nodo_sel.parent().children('ul li').size();
                                var level=nodo_sel.parent().attr("level")*1;
                                var max_level=$('#max_level').val()*1;
                                var tipo_cuenta='Titulo';
                                if(level+1===max_level){
                                    tipo_cuenta='Movimiento';
                                }
                                
                                $.post(
                                        treeURL,
                                        {
                                            "operation": "create_node",
                                            "<?php echo $label_id; ?>": id,
                                            "<?php echo $label_tree_position; ?>" : position,
                                            "<?php echo $label_descripcion; ?>" : $("#cue_descripcion").val(),
                                            "<?php echo $label_codigo; ?>" : $("#cue_codigo").val(),
                                            "<?php echo $label_ges_id; ?>" : $("#cue_ges_id").val(),
                                            "<?php echo $label_mon_id; ?>" : $("#cue_mon_id option:selected").val().toString(),
                                            "<?php echo $label_tipo; ?>" : tipo_cuenta//$("#cue_tipo option:selected").val().toString()
                                        },
                                        function(r) {
                                            if (r.status) {
                                                //$(data.rslt.obj).attr("id", "node_" + r.id);
                                                tree_form_borrar();
//                                                data.inst.refresh();
                                                $('#plancuenta').jstree('refresh', -1);
                                            }else{
                                                $.prompt(r.msj);
                                            }
                                        }
                                );                                           
                            }

                            function tree_form_borrar(){
                                //$("#cue_codigo").val("");
                                $("#cue_descripcion").val("");
                            }
                            function tree_form_codigo()
                            {
                                var tCodigo = $("#cue_codigo").val();
                                return tCodigo;
                            }

                            function tree_form_descripcion()
                            {
                                var tDescripcion = $("#cue_codigo").val();
                                return tDescripcion;
                            }
                            function tree_form_valores(tCodigo, tDescripcion)
                            {
                                $("#cue_codigo").val(tCodigo);
                                $("#cue_descripcion").val(tDescripcion);
                            }
                            function trim(str) {
                                return str.replace(/^\s+|\s+$/g,"");
                            }

                            $("#mmenu input").click(function() {
                                switch (this.id) {
                                    case "agregar":
                                        if (tree_form_validar()){
//                                            var cue_tipo = "";
//                                            var cue_tipoCombo = $("#cue_tipo option:selected").val().toString();
//                                            if (cue_tipoCombo === "Titulo"){
//                                                cue_tipo = "folder";
//                                            } else {
//                                                cue_tipo = "default";
//                                            }
//                                            $("#plancuenta").jstree("create", null, "last", {"attr": {"rel": cue_tipo}});
                                            tree_form_guardar_nuevo();
                                        }
                                        break;
                                    case "rename":
                                        if (tree_form_validar()) {
                                            tree_form_renombrar();
                                        }
                                        break;
                                    default:
                                        var cuenta=$(".jstree-clicked");
                                        if(cuenta.size()>1){
                                            $.prompt("seleccione solo una cuenta para eliminar");
                                            return false;
                                        }
                                        var texto=$(cuenta[0]).text();
                                        var codigo=texto.split('|');
                                        var tcuenta=$("#t_cuenta").val();
                                        var id_imput=this.id;
                                        var gesid=$("#ges_id").val();
                                        $.get('AjaxRequest.php',{peticion:'ver_'+tcuenta,cu:trim(codigo[0]),gesid:gesid},function (resp){
                                            if(trim(resp)==='ok'){
                                                $("#plancuenta").jstree(id_imput);
                                            }else{
                                                $.prompt(resp);
                                            }
                                        });
                                        return false;
                                }
                            });

                        });
                        $(document).ready(function (){
                            $("#cue_tipo").change(function(){
                                var tipo=$("#cue_tipo option:selected").val();                                
                                if(tipo==='Movimiento' && $("#t_cuenta").val()==='cue'){
                                    $("#sel_moneda").show();
                                }else{
                                    $("#sel_moneda").hide();
                                }
                            });
                        });
                                        
                    </script>

                    <div class="fila_formulario_cabecera"><?php echo $objGestion->ges_descripcion; ?></div>
                    <div class="aTabsCont">
                        <div class="aTabsCent">
                            <ul class="aTabs">
                                <li><a href="gestor.php?mod=gestion&tarea=ESTRUCTURA&p=cuenta&id=<?php echo $_GET["id"]; ?>" <?php if ($_GET['p'] == "cuenta") { ?>class="activo" <?php } ?>>Plan de Cuentas</a></li>
                                <li><a href="gestor.php?mod=gestion&tarea=ESTRUCTURA&p=cc&id=<?php echo $_GET["id"]; ?>" <?php if ($_GET['p'] == "cc") { ?>class="activo" <?php } ?>>Centros de Costo</a></li>
                                <li><a href="gestor.php?mod=gestion&tarea=ESTRUCTURA&p=ca&id=<?php echo $_GET["id"]; ?>" <?php if ($_GET['p'] == "ca") { ?>class="activo" <?php } ?>>Cuentas Analiticas</a></li>
                                <li><a href="gestor.php?mod=gestion&tarea=ESTRUCTURA&p=cf&id=<?php echo $_GET["id"]; ?>" <?php if ($_GET['p'] == "cf") { ?>class="activo" <?php } ?>>Cuentas de Flujo</a></li>
                                <li><a href="gestor.php?mod=gestion&tarea=ESTRUCTURA&p=cpf&id=<?php echo $_GET["id"]; ?>" <?php if ($_GET['p'] == "cpf") { ?>class="activo" <?php } ?>>Cuentas para Flujo</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <input type="hidden" id="max_level" value="<?php echo count($formato);?>">
                    <input type="hidden" id="t_cuenta" value="<?php echo $t_cuenta;?>">
                    <input type="hidden" id="ges_id" value="<?php echo $_GET['id'];?>">
                    <div id="Contenedor_NuevaSentencia">
                        <div id="FormSent">
                            <div id="ContenedorSeleccion">
                                <?php
                                if($_GET["p"]!='cpf'){
                                ?>                                
                                    <table cellspacing="0" cellpadding="0" class="tTreen">
                                        <tbody>
                                            <tr>
                                                <td class="tTreenA" align="right" valign="top">
                                                    <div id="plancuenta" class="plancuenta"> </div>
                                                </td>
                                                <td class="tTreenB" align="right" valign="top">
                                                    <label>Codigo:</label>
                                                    <input type="text" value="" id="cue_codigo" name="cue_codigo" style="width:250px;" placeholder="<?php echo $cod_codigo; ?>"><br><br>
                                                    <label>Descripci&oacute;n:</label>
                                                    <input type="text" value="" id="cue_descripcion" name="cue_descripcion" style="width:250px;" placeholder="Descripcion"><br><br>
                                                    <!--<div style="display: none;">-->
                                                        <label>Tipo de dato:</label>
                                                        <select name="cue_tipo" id="cue_tipo" class="caja_texto">
                                                            <option value="Titulo">Titulo</option>
                                                            <option value="Movimiento">Movimiento</option>                                                        
                                                        </select> <br><br>
                                                    <!--</div>-->
                                                    <div id="sel_moneda" hidden="">
                                                        <label>Moneda:</label>
                                                        <select name="cue_mon_id" id="cue_mon_id" class="caja_texto">
                                                            <option value="">Seleccione</option>
                                                            <?php
                                                            $fun = NEW FUNCIONES;
                                                            $fun->combo("select mon_id as id,mon_titulo as nombre from con_moneda order by mon_id asc", 0);
                                                            ?>
                                                        </select> <br><br>
                                                    </div>

                                                    <div id="mmenu" style="height:30px; overflow:auto;">
                                                        <input type="hidden" value="<?php echo $_GET['id']; ?>" id="cue_ges_id" name="cue_ges_id" style="width:250px;">
                                                        <input style="width: 50px" type="button" id="agregar" value="Agregar" class="boton"/>
                                                        <input style="width: 60px" type="button" id="rename" value="Renombrar" class="boton"/>
                                                        <input style="width: 50px" type="button" id="remove" value="Borrar" class="boton"/>
                                                    </div>

                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                <?php
                                }elseif ($_GET['p']=='cpf') {
                                ?>
                                    <style>
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
                                    </style>
                                    
                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Cuenta</div>
                                        <div id="CajaInput">                                    
                                            <input name="nombre_cuenta" id="nombre_cuenta"  type="text" class="caja_texto" value="<?php //echo $_POST['nombre_cuenta']                ?>" size="25">
                                        </div>							   							   								
                                    </div>
                                    <!--Fin-->
                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Cuentas a listar</div>
                                        <div id="CajaInput">
                                            <div class="box_lista_cuenta"> 
                                                <table id="tab_lista_cuentas" class="tab_lista_cuentas">
                                                    <?php
                                                    $conec = new ADO();
                                                    $sql="select * from con_detalle_cf where dcf_ges_id=".$_GET['id'];
                                                    $conec->ejecutar($sql);
                                                    for ($i = 0; $i < $conec->get_num_registros(); $i++) {
                                                        $objeto=$conec->get_objeto();
                                                        $fila = '<tr data-id="' . $objeto->dcf_cue_id . '">';
                                                        $fila .= '<td>' . FUNCIONES::atributo_bd('con_cuenta', "cue_id=$objeto->dcf_cue_id and cue_eliminado='No'", "cue_descripcion") . '</td>';
                                                        $fila .= '<td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                                        $fila .= '</tr>';
                                                        echo $fila;
                                                        $conec->siguiente();
                                                    }
                                                    ?>
                                                </table>
                                                <input type="hidden" value="" name="lista_cuentas" id="lista_cuentas"/>
                                            </div>
                                        </div>							   							   								
                                    </div>
                                    <div id="ContenedorDiv">
                                        <input type="hidden" value="<?php echo $_GET['id']; ?>" id="cue_ges_id" name="cue_ges_id" style="width:250px;">
                                        <input type="button" id="guardar_detalles_cf" value="Guardar" class="boton"/>                                        
                                    </div>                                    
                                    <!--Fin-->
                                    <script>
                                        var options = {
                                            script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cuenta&gesid="+$("#cue_ges_id").val()+"&",
                                            varname: "input",
                                            json: true,
                                            shownoresults: false,
                                            maxresults: 6,
                                            callback: function(obj) {
                                                agregar_cuenta(obj);
                                            }
                                        };
                                        var as_json1 = new _bsn.AutoSuggest('nombre_cuenta', options);

                                        function agregar_cuenta(cuenta) {
                                            if (!existe_en_lista(cuenta.id)) {
                                                var fila = '<tr data-id="' + cuenta.id + '">';
                                                fila += '<td>' + cuenta.value + '</td>';
                                                fila += '<td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                                fila += '</tr>';
                                                $("#tab_lista_cuentas").append(fila);
                                                $("#nombre_cuenta").val("");
                                            }
                                        }

                                        function existe_en_lista(id_cuenta) {
                                            var lista = $("#tab_lista_cuentas tr");
                                            for (var i = 0; i < lista.size(); i++) {
                                                var cuenta = lista[i];
                                                var id = $(cuenta).attr("data-id");
                                                if (id === id_cuenta) {
                                                    return true;
                                                }
                                            }
                                            return false;
                                        }

                                        $(document).on('click','.img_del_cuenta',function (){
                                            $(this).parent().parent().remove();                                            
                                        });
                                        
                                        $("#guardar_detalles_cf").click(function (){
                                            var lista = $("#tab_lista_cuentas tr");
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
                                            
                                            $.post('gestorAjax.php',{dir:'con_detalle_cf.php' ,cuentas:data,ges_id:$("#cue_ges_id").val() },function (respuesta){
                                                $.prompt(respuesta);
                                            });
                                        });
                                    
                                    </script>
                                <?php
                                }
                                ?>
                            </div>

                        </div>
                    </div>


            <?php
        }
    }

}
?>