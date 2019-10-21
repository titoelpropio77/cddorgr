<?PHP

class BUSQUEDA {

    var $num_registros;
    var $sql;
    var $arreglo_opciones;
    var $coneccion;
    var $numero;
    var $arreglo_campos;
    var $link;
    var $modulo;
    var $mifiltro = "";
    var $mostrar_filtro;
    var $num_paginas;
    var $ban_agregar = true;
    var $ele_id = 0;
    var $a_permisos = array();
    var $usua = "";
    var $tc = 0;

    function BUSQUEDA($num = 10) {
        $this->usua = new USUARIO;
        $this->num_registros = $num;
        $this->a_permisos = $this->tareas_permitidas();
        //$this->tc=$this->tipo_cambio();
    }

    function tipo_cambio() {
        $conec = new ADO();
        $sql = "select tca_valor from con_tipo_cambio where tca_fecha='" . date("Y-m-d") . "';";
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        return $objeto->tca_valor;
    }

    function paginar($get_ingresar) {
        //echo $this->sql;

        $conec = new ADO();

        $conec->ejecutar($this->sql);
        //echo 'sql: '.  $this->sql;
        //numero de registros

        $this->numero = $conec->get_num_registros();

        //numero de paginas
        $this->num_paginas = intval($this->numero / $this->num_registros);

        if (($this->num_paginas * $this->num_registros) <> ($this->numero / $this->num_registros)) {
            $this->num_paginas+=1;
        }

        if (!($this->num_paginas > 0)) {
            $this->num_paginas = 1;
        }

        //numero de Pagina Actual
        if ((isset($_POST['pagina_actual'])) && ($_POST['pagina_actual'] > 0)) {
            $num_pagina_actual = $_POST['pagina_actual'];
        } else {
            $num_pagina_actual = 1;
        }

        if ($num_pagina_actual > $this->num_paginas) {
            $num_pagina_actual = 1;
        }

        $inicio_limit = $num_pagina_actual * $this->num_registros - $this->num_registros;

        $sql = $this->sql . ' LIMIT ' . $inicio_limit . ' , ' . $this->num_registros;

        $this->sql = $sql;
        ?>
        <br>
        <div>
            <table width="100%" cellpadding="10" cellspacing="0" style="border:0px solid #CCCCCC;">

                <tr class="busqueda_paginar" valign="middle">
                    <td align="left">
                        <?php
                        echo "&nbsp;&nbsp;&nbsp;N&uacute;mero de registros : " . $this->numero;
                        ?>
                    </td>
                    <?php
                    if ($this->ban_agregar) {
                        if (isset($_GET['cat_id'])) {
                            $url = $this->link . '?cat_id=' . $_GET['cat_id'];
                        } else {
                            $url = $this->link . '?mod=' . $this->modulo . $get_ingresar;
                        }
                        ?>
                        <td  width="190" align='center'>
                            <a href='<?php echo $url ?>&tarea=AGREGAR' class='link_azul' title='AGREGAR'>
                                <img src='images/boton_agregar.png' border='0'>
                            </a>
                        </td>
                        <?php
                    }
                    ?>

                    <td  width="120" align="center">
                        <a href="#" onclick="formulario_filtro();" class="link_azul"  title="BUSCAR">
                            <img src="images/boton_nbuscar.png" alt="Buscar" border="0">
                        </a>
                    </td>
                    <td  width="15" align="right">
                        <a href="#" onclick="document.form_filtro.pagina_actual.value = Number(document.getElementById('combo_paginacion').value) - 1;
                                         document.form_filtro.submit();" ><img src="images/bd_prevpage.png" border="0"></a>
                    </td>

                    <td  width="120" align="center">
                        <select name="combo_paginacion" id="combo_paginacion" class="combo_busqueda" onclick="" onchange="document.form_filtro.pagina_actual.value = document.getElementById('combo_paginacion').value;
                                         form_filtro.submit();">
                                    <?php
                                    for ($i = 0; $i < $this->num_paginas; $i++) {
                                        ?>
                                <option value="<?php echo ($i + 1); ?>" <?php if (($i + 1) == $num_pagina_actual) { ?> selected <?php } ?>>Pagina <?php echo ($i + 1); ?> de <?php echo $this->num_paginas; ?></option>
                                <?php
                            }
                            ?>		
                        </select>
                    </td>
                    <td width="15" align="left">
                        <a href="#" onclick="document.form_filtro.pagina_actual.value = Number(document.getElementById('combo_paginacion').value) + 1;
                                         document.form_filtro.submit();" ><img src="images/bd_nextpage.png" border="0"></a>
                    </td>
                </tr>
            </table>
        </div>
        <br>
        <?php
    }

    function mostrar_busqueda() {
        
    }

    function poner_filtro($filtro) {
        $this->mifiltro = $filtro;
    }

    function set_sql($sql = "", $filtro = "") {
        $this->sql = $sql;

        $this->get_opciones_filtro();

        if (strpos($this->sql, 'where') > 0) {
            $this->sql.=' ' . $filtro;
        } else {
            $this->sql.=' where 1=1 ' . $filtro;
        }
        //echo $this->sql; 
    }

    function get_opciones_filtro() {

        $sql_filter = '';

        $conversor = new convertir();



        for ($i = 0; $i < count($this->arreglo_campos); $i++) {
            $mi_like = "";
            if (trim($_POST['texto_' . $this->arreglo_campos[$i]["nombre"]]) <> "") {
                switch ($this->arreglo_campos[$i]["tipo"]) {
                    case 'numero': {
                            $sql_filter.=' and ' . $this->arreglo_campos[$i]["nombre"] . ' ' . $_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] . " " . $_POST['texto_' . $this->arreglo_campos[$i]["nombre"]];
                            break;
                        }
                    case 'cadena': {
                            if ($_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] == 'like')
                                $mi_like = "%";
                            $sql_filter.=' and ' . $this->arreglo_campos[$i]["nombre"] . ' ' . $_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] . " '" . $mi_like . $_POST['texto_' . $this->arreglo_campos[$i]["nombre"]] . $mi_like . "'";
                            break;
                        }
                    case 'compuesto': {
                            if ($_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] == 'like')
                                $mi_like = "%";
                            $sql_filter.=' and ' . $this->arreglo_campos[$i]["campo_compuesto"] . ' ' . $_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] . " '" . $mi_like . $_POST['texto_' . $this->arreglo_campos[$i]["nombre"]] . $mi_like . "'";
                            break;
                        }
                    case 'fecha': {
                            $sql_filter.=' and ' . $this->arreglo_campos[$i]["nombre"] . ' ' . $_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] . " '" . $conversor->get_fecha_mysql($_POST['texto_' . $this->arreglo_campos[$i]["nombre"]]) . "%'";
                            break;
                        }
                    case 'combosql': {
                            $sql_filter.=' and ' . $this->arreglo_campos[$i]["nombre"] . ' ' . $_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] . " '" . $mi_like . $_POST['texto_' . $this->arreglo_campos[$i]["nombre"]] . $mi_like . "'";
                            break;
                        }
                    case 'comboarray': {
                        if ($this->arreglo_campos[$i]["nombre"] == 'producto') {
                            $valor = $_POST['texto_' . $this->arreglo_campos[$i]["nombre"]];
                                
                            if ($valor == 'compra_conjunta'){
                                $s_mas = " and ven_prod_id in (SELECT uprod_id FROM  urbanizacion_producto)";
                            } else if ($valor == 'compra_posterior') {
                                $s_mas = " and ven_id in (SELECT vprod_ven_id FROM  venta_producto WHERE vprod_estado in ('Pendiente','Pagado'))";
                            } else if ($valor == 'reserva_producto') {
                                $s_mas = " and ven_id in (SELECT rprod_ven_id FROM  reserva_producto WHERE rprod_estado in ('Pendiente','Habilitado'))";
                            }
                            $sql_filter .= $s_mas;
                        } else {
                            $sql_filter.=' and ' . $this->arreglo_campos[$i]["nombre"] . ' ' . $_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] . " '" . $mi_like . $_POST['texto_' . $this->arreglo_campos[$i]["nombre"]] . $mi_like . "'";
                        }                            
                            break;
                        }
                }
            }
        }

        if (strpos($this->sql, 'where') > 0) {
            $this->sql.=' ' . $sql_filter;
        } else {
            $sql_filter = substr($sql_filter, 4);
            if (trim($sql_filter) <> "") {
                $this->sql.=' where ' . $sql_filter;
            }
        }

        if (trim($sql_filter) <> "") {
            $this->mostrar_filtro = true;
        }

        $this->sql = $this->sql . $this->mifiltro;
    }

    function set_opciones() {

        $this->arreglo_opciones[0]["tarea"] = 'VER';
        $this->arreglo_opciones[0]["imagen"] = 'graficos/b_search.png';
        $this->arreglo_opciones[0]["nombre"] = 'VER';

        $this->arreglo_opciones[1]["tarea"] = 'MODIFICAR';
        $this->arreglo_opciones[1]["imagen"] = 'graficos/b_edit.png';
        $this->arreglo_opciones[1]["nombre"] = 'MODIFICAR';

        $this->arreglo_opciones[2]["tarea"] = 'ELIMINAR';
        $this->arreglo_opciones[2]["imagen"] = 'graficos/b_drop.png';
        $this->arreglo_opciones[2]["nombre"] = 'ELIMINAR';

        $this->arreglo_opciones[3]["tarea"] = 'IMPRIMIR';
        $this->arreglo_opciones[3]["imagen"] = 'graficos/b_print.png';
        $this->arreglo_opciones[3]["nombre"] = 'IMPRIMIR';
    }

    function form_filtro($get_ingresar = "") {

        if (isset($_GET['cat_id'])) {
            $url = $this->link . '?cat_id=' . $_GET['cat_id'] . '&mod=' . $this->mod . '&tarea=ACCEDER';
        } else {
            $url = $this->link . '?mod=' . $this->modulo . '&tarea=ACCEDER';
        }
        ?>
        <center>
            <div id="formulario_de_filtro" style="display:none;">
                <form id="form_filtro" name="form_filtro" action="<?php echo $url . $get_ingresar; ?>" method="POST" enctype="multipart/form-data">
                    <table width="60%" cellpadding="5" cellspacing="5" border="0">
                        <?php
                        //<td>Orden&nbsp;A:<input type="checkbox" name="A_<?php echo $this->arreglo_campos[$i]["nombre"];">&nbsp;D:<input type="checkbox" name="D_<?php echo $this->arreglo_campos[$i]["nombre"];"></td>

                        for ($i = 0; $i < count($this->arreglo_campos); $i++) {
                            ?>
                            <tr class="busqueda_filtro">
                                <td width="5%">
                                </td>
                                <td align= "right">
                                    <?php
                                    echo $this->arreglo_campos[$i]["texto"] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                    ?>
                                </td>

                                <td width="100" align="left">

                                    <select name="combo_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>" class="combo_busqueda">
                                        <?php
                                        if (($this->arreglo_campos[$i]["tipo"] <> 'combosql') && ($this->arreglo_campos[$i]["tipo"] <> 'comboarray')) {
                                            ?>
                                            <option value="like" <?php if ($_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] == 'like') { ?> selected <?php } ?>>SIMILAR</option>
                                            <option value="=" <?php if ($_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] == '=') { ?> selected <?php } ?>>IGUAL</option>
                                            <option value="<" <?php if ($_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] == '<') { ?> selected <?php } ?>>MENOR</option>
                                            <option value=">" <?php if ($_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] == '>') { ?> selected <?php } ?>>MAYOR</option>
                                            <option value="<>" <?php if ($_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] == '<>') { ?> selected <?php } ?>>DISTINTO</option>
                                            <?php
                                        } else {
                                            ?>

                                            <option value="=" <?php if ($_POST['combo_' . $this->arreglo_campos[$i]["nombre"]] == '=') { ?> selected <?php } ?>>IGUAL</option>

                                            <?php
                                        }
                                        ?>
                                    </select>			



                                </td>

                                <td align="left">
                                    <?php
                                    if (($this->arreglo_campos[$i]["tipo"] == 'concat') && ($this->arreglo_campos[$i]["tipo"] <> 'combosql') && ($this->arreglo_campos[$i]["tipo"] <> 'comboarray')) {
                                        ?>
                                        <input type="text" value="<?php echo $_POST['texto_' . $this->arreglo_campos[$i]["nombre"]]; ?>" id="texto_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>" name="texto_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>" class="caja_texto" size="<?php echo $this->arreglo_campos[$i]["tamanio"]; ?>">
                                        <?php
                                    }
                                    if (($this->arreglo_campos[$i]["tipo"] <> 'combosql') && ($this->arreglo_campos[$i]["tipo"] <> 'comboarray') && ($this->arreglo_campos[$i]["tipo"] <> 'concat')) {
                                        ?>
                                        <input type="text" value="<?php echo $_POST['texto_' . $this->arreglo_campos[$i]["nombre"]]; ?>" id="texto_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>" name="texto_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>" class="caja_texto" size="<?php echo $this->arreglo_campos[$i]["tamanio"]; ?>">
                                        <?php
                                    }

                                    if ($this->arreglo_campos[$i]["tipo"] == 'fecha') {
                                        ?>
                                        <input name="but_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>" id="but_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>" type="button" class="boton_fecha" value="...">
                                        <script type="text/javascript">
                             Calendar.setup({inputField: "texto_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>"
                                         , ifFormat: "%d/%m/%Y",
                                 button: "but_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>"
                             });
                                        </script>		
                                        <?php
                                    }
                                    if (($this->arreglo_campos[$i]["tipo"] == 'combosql') || ($this->arreglo_campos[$i]["tipo"] == 'comboarray')) {
                                        ?>
                                        <select  id="texto_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>" name="texto_<?php echo $this->arreglo_campos[$i]["nombre"]; ?>" class="caja_texto">
                                            <option value="">Seleccione</option>
                                            <?php
                                            if ($this->arreglo_campos[$i]["tipo"] == 'combosql') {
                                                $miconec = new ADO();
                                                $miconec->ejecutar($this->arreglo_campos[$i]["sql"]);
                                                $nume = $miconec->get_num_registros();

                                                for ($j = 0; $j < $nume; $j++) {
                                                    $objeto = $miconec->get_objeto();
                                                    ?>
                                                    <option value="<?php echo $objeto->codigo; ?>" <?php if ($_POST['texto_' . $this->arreglo_campos[$i]["nombre"]] == $objeto->codigo) echo 'selected="selected"' ?>><?php echo $objeto->descripcion; ?></option>
                                                    <?php
                                                    $miconec->siguiente();
                                                }
                                            }
                                            else {
                                                list($cllav, $ctit) = split(":", $this->arreglo_campos[$i]["valores"]);
                                                $llav = split(",", $cllav);
                                                $tit = split(",", $ctit);
//                                                $colores = explode(",", $ccolores);
                                                $colores = $this->arreglo_campos[$i]["colores"];
//                                                var_dump($colores);
                                                $nume = count($llav);
//                                                $cant_col = count($colores);

                                                for ($j = 0; $j < $nume; $j++) {
                                                    if ($colores[$llav[$j]] != '') {
//                                                        echo "<p>algo</p>";
                                                        $style = 'style="color:#ffffff;background-color:' . $colores[$llav[$j]] . '"';
                                                    } else {
                                                        $style = 'style="color:#000000;"';
                                                    }
                                                    ?>
                                                    <option <?php echo $style;?> value="<?php echo $llav[$j]; ?>" <?php if ($_POST['texto_' . $this->arreglo_campos[$i]["nombre"]] == $llav[$j]) echo 'selected="selected"' ?>><?php echo $tit[$j]; ?></option>
                                                    <?php
                                                }
                                            }
                                            ?> 	
                                        </select>
                                        <?php
                                    }
                                    ?>
                                </td>


                                <td width="5%"></td>

                            </tr>

                            <?php
                        }
                        ?>
                        <tr>
                            <td colspan="5" align="right">
                                <br>
                                <input type="hidden" name="pagina_actual">
                                <textarea name="texto_sql" cols="85" rows="10"  style="display:none;"><?php echo $this->sql; ?></textarea>
                                <input type="submit" class="boton" name="" value="Buscar">
                                <input type="reset" class="boton" name="" value="Cancelar">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </center>

        <?php
        if ($this->mostrar_filtro) {
            ?>
            <script type="text/javascript" language="Javascript">
                document.getElementById('formulario_de_filtro').style.display = 'block';
            </script>
            <?php
        }
    }

    function dibujar($paginar = true, $filtro = true, $get_ingresar = "") {

        if ($paginar)
            $this->paginar($get_ingresar);

        $this->ejecutar_consulta();

        if ($filtro)
            $this->form_filtro($get_ingresar);
        ?>
        <table class="tablaLista" cellpadding="0" cellspacing="0">
            <thead>
                <?PHP
                $this->dibujar_encabezado();
                ?>

            </thead>

            <tbody>
                <?php
                $this->mostrar_busqueda();
                ?>
            </tbody>
        </table>
        <?PHP
    }

    function get_opciones($id, $valores = "", $operaciones = null, $filtros = array()) {
        $txt_filtro = '';
        foreach ($filtros as $key => $value) {
            $txt_filtro.="&$key=$value";
        }
        ?>
        <center>
            <?php
            for ($i = 0; $i < count($this->arreglo_opciones); $i++) {
                if (!$this->esta_operacion($operaciones, $this->arreglo_opciones[$i]["tarea"])) {
                    if ($this->arreglo_opciones[$i]["script"] == "ok") {
                        ?>
                        <a class="linkOpciones" href="javascript:ejecutar_script('<?php echo $id; ?>','<?php echo $this->arreglo_opciones[$i]["tarea"]; ?>');" title="<?PHP echo $this->arreglo_opciones[$i]["nombre"]; ?>"><img src="<?PHP echo $this->arreglo_opciones[$i]["imagen"]; ?>" width="16" alt="<?PHP echo $this->arreglo_opciones[$i]["nombre"]; ?>" border="0"></a>
                        <?php
                    } else {
                        ?>
                        <a class="linkOpciones" href="<?PHP echo $this->link . "?mod=" . $this->modulo . "&tarea=" . $this->arreglo_opciones[$i]["tarea"] . "&id=" . $id . $valores . $txt_filtro; ?>" title="<?PHP echo $this->arreglo_opciones[$i]["nombre"]; ?>"><img src="<?PHP echo $this->arreglo_opciones[$i]["imagen"]; ?>" width="16" alt="<?PHP echo $this->arreglo_opciones[$i]["nombre"]; ?>" border="0"></a>
                            <?php
                        }
                    }
                }
                ?>
        </center>
        <?php
    }

    function esta_operacion($operaciones, $op) {
        if ($operaciones != null) {
            foreach ($operaciones as $value) {
                if ($value == $op) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }

    function ejecutar_consulta() {

        $this->coneccion->ejecutar($this->sql);

        $this->numero = $this->coneccion->get_num_registros();
    }

    function dibujar_encabezado() {
        
    }

    function tareas_permitidas() {

        $conec = new ADO();

        $vector = array();

        $consulta = "SELECT tar_nombre
			FROM ad_elemento,ad_permiso,ad_usuario,ad_tarea
			WHERE usu_id= '" . $this->usua->get_id() . "'
			AND usu_gru_id = pmo_gru_id
			AND pmo_ele_id=ele_id
			AND pmo_tar_id=tar_id
			AND ele_id='" . $this->ele_id . "'
			AND ele_estado = 'H'";

        $conec->ejecutar($consulta);

        $numero = $conec->get_num_registros();

        for ($i = 0; $i < $numero; $i++) {

            $objeto = $conec->get_objeto();

            $vector[$i] = $objeto->tar_nombre;

            $conec->siguiente();
        }

        return $vector;
    }

    function verificar_permisos($tarea) {
        if (is_numeric(array_search($tarea, $this->a_permisos))) {
            return true;
        } else {
            return false;
        }
    }

}
?>