<?php

class OFERTA extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function OFERTA() {
        //permisos
        $this->ele_id = 411;

        $this->busqueda();

        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "of_nombre";
        $this->arreglo_campos[0]["texto"] = "Nombre";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 40;

        $this->link = 'gestor.php';

        $this->modulo = 'oferta';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('OFERTAS');
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
    }

    function dibujar_listado() {
        $sql = "SELECT * FROM oferta where of_eliminado='No'";

        $this->set_sql($sql, ' order by of_id desc ');

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Nombre</th>
            <th>Descripcion</th>
            <th>Estado</th>	
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>

        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();
        $hoy = date('Y-m-d');
        for ($i = 0; $i < $this->numero; $i++) {

            $objeto = $this->coneccion->get_objeto();
            echo '<tr>';

            echo "<td>";
            echo $objeto->of_nombre;
            echo "&nbsp;</td>";

            echo "<td>";
            echo $objeto->of_descripcion;
            echo "&nbsp;</td>";

            echo "<td style='text-align:center'>";
            echo ($objeto->of_fecha_fin >= $hoy && $objeto->of_fecha_ini <= $hoy) ? "Vigente" : "Caducada";
            echo "&nbsp;</td>";

            echo "<td>";
            echo $this->get_opciones($objeto->of_id);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from oferta
				where of_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['of_nombre'] = $objeto->of_nombre;
        $_POST['of_descripcion'] = $objeto->of_descripcion;
        $_POST['of_periodos_sin_bra'] = $objeto->of_periodos_sin_bra;
        $_POST['of_periodos_con_bir'] = $objeto->of_periodos_con_bir;

        $_POST['of_fecha_ini'] = FUNCIONES::get_fecha_latina($objeto->of_fecha_ini);
        $_POST['of_fecha_fin'] = FUNCIONES::get_fecha_latina($objeto->of_fecha_fin);

        $_POST['of_forma_ci'] = $objeto->of_forma_ci;
        $_POST['of_monto_fijo'] = $objeto->of_monto_fijo;
        $_POST['of_porc'] = $objeto->of_porc;

        $_POST['of_plazo'] = $objeto->of_plazo;
    }

    function datos() {

        return true;
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Nombre";
            $valores[$num]["valor"] = $_POST['of_nombre'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;

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


        $this->formulario->dibujar_tarea('OFERTAS');

        if ($this->mensaje <> "") {
            $this->formulario->dibujar_mensaje($this->mensaje);
        }
        ?>
        <script type="text/javascript">
            function validar_formulario() {

                if ($('#of_nombre').val() === '') {
                    $.prompt('Indique el nombre de la oferta.');
                    return false;
                }

                if ($('#of_descripcion').val() === '') {
                    $.prompt('Indique una descripcion para la oferta.');
                    return false;
                }

                if ($('#of_fecha_ini').val() === '') {
                    $.prompt('Indique desde cuando estara vigente la oferta.');
                    return false;
                }

                if ($('#of_fecha_fin').val() === '') {
                    $.prompt('Indique hasta cuando estara vigente la oferta.');
                    return false;
                }

                if ($('#of_periodos_con_bir').val() * 1 === 0) {
                    $.prompt('Indique una cantidad mayor a cero para periodos con BIR/BVI de la oferta.');
                    return false;
                }

                if ($('#of_periodos_sin_bra').val() * 1 === 0) {
                    $.prompt('Indique una cantidad mayor a cero para periodos sin BRA de la oferta.');
                    return false;
                }

        //                if ($('#of_plazo').val() * 1 === 0) {
        //                    $.prompt('Indique una cantidad mayor a cero para el plazo de las ventas con esta oferta.');
        //                    return false;
        //                }

                if ($('#of_forma_ci option:selected').val() === 'monto_fijo') {
                    if ($('#of_monto_fijo').val() * 1 === 0) {
                        $.prompt('Indique una cantidad mayor a cero para el monto fijo de la cuota inicial de esta oferta.');
                        return false;
                    }
                } else if ($('#of_forma_ci option:selected').val() === 'porcentaje') {
                    if ($('#of_porc').val() * 1 === 0) {
                        $.prompt('Indique una cantidad mayor a cero para el porcentaje de la cuota inicial de esta oferta.');
                        return false;
                    }
                }

                return true;
            }

            function enviar_formulario() {

                if (validar_formulario()) {
                    $('#frm_sentencia').submit();
                }
            }
        </script>
        <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
        <script src="js/util.js" type="text/javascript"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent" style="width:800px">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Nombre</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="of_nombre" id="of_nombre" maxlength="250"  size="50" value="<?php echo $_POST['of_nombre']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Descripcion</div>
                            <div id="CajaInput">	   
                                <textarea rows="10" cols="50" name="of_descripcion" id="of_descripcion"><?php echo $_POST['of_descripcion']; ?></textarea>									
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Periodo de Vigencia</div>
                            <div id="CajaInput">
                                &nbsp;Desde:&nbsp;&nbsp;<input type="text" class="caja_texto" name="of_fecha_ini" id="of_fecha_ini" maxlength="15"  size="15" value="<?php echo $_POST['of_fecha_ini']; ?>">
                            </div>

                            <div id="CajaInput">
                                &nbsp;Hasta:&nbsp;&nbsp;<input type="text" class="caja_texto" name="of_fecha_fin" id="of_fecha_fin" maxlength="15"  size="15" value="<?php echo $_POST['of_fecha_fin']; ?>">
                            </div>

                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Periodos Con BIR/BVI</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="of_periodos_con_bir" id="of_periodos_con_bir" maxlength="15"  size="15" value="<?php echo ($_POST['of_periodos_con_bir'] == '') ? 0 : $_POST['of_periodos_con_bir']; ?>">
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Periodos Sin BRA</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="of_periodos_sin_bra" id="of_periodos_sin_bra" maxlength="15"  size="15" value="<?php echo ($_POST['of_periodos_sin_bra'] == '') ? 0 : $_POST['of_periodos_sin_bra']; ?>">
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv" hidden="">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Meses Plazo</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="of_plazo" id="of_plazo" maxlength="15"  size="15" value="<?php echo ($_POST['of_plazo'] == '') ? 0 : $_POST['of_plazo']; ?>">
                            </div>
                        </div>
                        <!--Fin-->                        

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Forma de Cuota Inicial</div>
                            <div id="CajaInput">
                                <select id="of_forma_ci" name="of_forma_ci" class="caja_texto">
                                    <option value="cuota_mensual" <?php if ($_POST['of_forma_ci'] == 'cuota_mensual') echo 'selected="selected"'; ?>>Cuota Mensual</option>
                                    <option value="monto_fijo" <?php if ($_POST['of_forma_ci'] == 'monto_fijo') echo 'selected="selected"'; ?>>Monto Fijo</option>
                                    <option value="porcentaje" <?php if ($_POST['of_forma_ci'] == 'porcentaje') echo 'selected="selected"'; ?>>Porcentaje</option>
                                </select>
                            </div>

                            <div class="div_monto_fijo" id="CajaInput" hidden="">
                                &nbsp;$us:&nbsp;&nbsp;<input type="text" class="caja_texto div_monto_fijo" name="of_monto_fijo" id="of_monto_fijo" maxlength="15"  size="15" value="<?php echo ($_POST['of_monto_fijo'] == '') ? 0 : $_POST['of_monto_fijo']; ?>">
                            </div>

                            <div class="div_porcentaje" id="CajaInput" hidden="">
                                &nbsp;%:&nbsp;&nbsp;<input type="text" class="caja_texto div_porcentaje" name="of_porc" id="of_porc" maxlength="15"  size="15" value="<?php echo ($_POST['of_porc'] == '') ? 0 : $_POST['of_porc']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
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
            </form>
        </div>
        <script>
            mask_decimal('#of_periodos_con_bir', null);
            mask_decimal('#of_periodos_sin_bra', null);
            mask_decimal('#of_plazo', null);
            mask_decimal('#of_monto_fijo', null);
            mask_decimal('#of_porc', null);
            $('#of_fecha_fin').mask('99/99/9999');
            $('#of_fecha_ini').mask('99/99/9999');

            $('#of_forma_ci').change(function() {
                var opcion = $('#of_forma_ci option:selected').val();

                if (opcion === 'monto_fijo') {
                    $('.div_monto_fijo').show();
                    $('.div_porcentaje').hide();
                    $('#of_porc').val('0');
                } else if (opcion === 'porcentaje') {
                    $('.div_porcentaje').show();
                    $('.div_monto_fijo').hide();
                    $('#of_monto_fijo').val('0');
                } else {
                    $('.div_monto_fijo').hide();
                    $('.div_porcentaje').hide();
                    $('#of_monto_fijo').val('0');
                    $('#of_porc').val('0');
                }
            });
            $('#of_forma_ci').trigger('change');
        </script>
        <?php
    }

    function insertar_tcp() {

//        echo "<pre>POST";
//        print_r($_POST);
//        echo "</pre>";
//        
//        echo "<pre>GET";
//        print_r($_GET);
//        echo "</pre>";

        $conec = new ADO();
        $of_fecha_ini = FUNCIONES::get_fecha_mysql($_POST[of_fecha_ini]);
        $of_fecha_fin = FUNCIONES::get_fecha_mysql($_POST[of_fecha_fin]);
        $hoy = date('Y-m-d');
        $_POST[of_plazo] = 59;
        $sql = "insert into oferta(
                    of_nombre,of_descripcion,of_plazo,of_periodos_sin_bra,
                    of_periodos_con_bir,of_fecha_ini,of_fecha_fin,
                    of_usu_id,of_fecha_cre,of_forma_ci,of_monto_fijo,of_porc                    
                ) values (
                    '$_POST[of_nombre]','$_POST[of_descripcion]','$_POST[of_plazo]','$_POST[of_periodos_sin_bra]',
                    '$_POST[of_periodos_con_bir]','$of_fecha_ini','$of_fecha_fin',
                    '$_SESSION[id]','$hoy','$_POST[of_forma_ci]','$_POST[of_monto_fijo]','$_POST[of_porc]'
                )";

        echo $sql;
        $conec->ejecutar($sql);
        $mensaje = 'Oferta Agregada Correctamente!!!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
    }

    function modificar_tcp() {

        $conec = new ADO();
        $of_fecha_ini = FUNCIONES::get_fecha_mysql($_POST[of_fecha_ini]);
        $of_fecha_fin = FUNCIONES::get_fecha_mysql($_POST[of_fecha_fin]);
        $hoy = date('Y-m-d');
        $sql = "update oferta set 
                of_nombre='" . $_POST['of_nombre'] . "',
                of_descripcion='" . $_POST['of_descripcion'] . "',
                of_periodos_sin_bra='" . $_POST['of_periodos_sin_bra'] . "',    
                of_periodos_con_bir='" . $_POST['of_periodos_con_bir'] . "',        
                of_fecha_ini='" . $of_fecha_ini . "',            
                of_fecha_fin='" . $of_fecha_fin . "',           
                of_usu_mod='" . $_SESSION[id] . "',               
                of_fecha_mod='" . $hoy . "',        
                of_forma_ci='" . $_POST['of_forma_ci'] . "',            
                of_monto_fijo='" . $_POST['of_monto_fijo'] . "',            
                of_porc='" . $_POST['of_porc'] . "',                
                of_plazo='" . $_POST['of_plazo'] . "'
                where of_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);
        $mensaje = 'Oferta Modificada Correctamente!!!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
    }

    function formulario_confirmar_eliminacion() {

        $mensaje = 'Esta seguro de eliminar la Oferta?';
        $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo" . '&tarea=ELIMINAR', 'of_id');
    }

    function eliminar_tcp() {

        $conec = new ADO();
        $sql = "update oferta set of_eliminado='Si' where of_id='" . $_POST['of_id'] . "'";
        $conec->ejecutar($sql);
        $mensaje = 'Pregunta Eliminado Correctamente!!!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER');
    }

}
?>