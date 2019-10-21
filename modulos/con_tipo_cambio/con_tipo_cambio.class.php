<?php

class CON_TIPO_CAMBIO extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function CON_TIPO_CAMBIO() {
        //permisos
        $this->ele_id = 69;

        $this->busqueda();

        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "tca_fecha";
        $this->arreglo_campos[0]["texto"] = "Fecha";
        $this->arreglo_campos[0]["tipo"] = "fecha";
        $this->arreglo_campos[0]["tamanio"] = 25;
        $this->link = 'gestor.php';

        $this->modulo = 'con_tipo_cambio';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('TIPO DE CAMBIO');
    }

    function dibujar_busqueda() {

        $this->formulario->dibujar_cabecera();

        $this->dibujar_listado();
    }

    function set_opciones() {
        $nun = 0;
        if ($this->verificar_permisos('MODIFICAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'MODIFICAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_edit.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'MODIFICAR';
            $nun++;
        }
    }

    function dibujar_listado() {
        $sql = "SELECT * FROM con_tipo_cambio inner join con_moneda on (tca_mon_id=mon_id)";

        $this->set_sql($sql, " order by tca_fecha desc,tca_mon_id asc ");

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Fecha</th>
            <th>Moneda</th>
            <th>Valor Oficial</th>
            <th>Valor Compra</th>
            <th>Valor Venta</th>
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
            echo FUNCIONES::get_fecha_latina($objeto->tca_fecha);
            echo "</td>";
            echo "<td>";
            echo $objeto->mon_Simbolo;
            echo "</td>";
            echo "<td>";
            echo $objeto->tca_valor;
            echo "</td>";
            echo "<td>";
            echo $objeto->tca_valor_compra;
            echo "</td>";
            echo "<td>";
            echo $objeto->tca_valor_venta;
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->tca_fecha,"&mon_id=$objeto->tca_mon_id");
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from con_tipo_cambio
				where ges_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['ges_id'] = $objeto->usu_id;

        $_POST['ges_descripcion'] = $objeto->ges_descripcion;

        $_POST['ges_fecha_inicio'] = $objeto->ges_fecha_ini;

        $_POST['ges_fecha_fin'] = $objeto->ges_fecha_fin;

        $_POST['ges_formato_cuenta'] = $objeto->ges_formato_cuenta;

        $_POST['ges_formato_cc'] = $objeto->ges_formato_cc;

        $_POST['ges_formato_ca'] = $objeto->ges_formato_ca;

        $_POST['ges_formato_cf'] = $objeto->ges_formato_cf;

        $fun = NEW FUNCIONES;

        //$_POST['usu_nombre_persona']=$fun->nombre($objeto->usu_per_id);
    }

    function datos() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Fecha";
            $valores[$num]["valor"] = $_POST['tca_fecha'];
            $valores[$num]["tipo"] = "todo";
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

        $this->formulario->dibujar_tarea('TIPO DE CAMBIO');

        if ($this->mensaje <> "") {
            $this->formulario->dibujar_mensaje($this->mensaje);
        }
        ?>
        <script>
            function ValidarNumero(e)
            {
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
        </script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_usuario" name="frm_usuario" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span>Fecha</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="tca_fecha" id="tca_fecha" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Valores</div>
                            <div id="CajaInput">
                                <table width="300px" class="tablaLista" cellpadding="0" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Moneda</th>
                                            <th>Valor</th>
                                            <th>Valor Compra</th>
                                            <th>Valor Venta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $conec = new ADO();

                                        $sql = "SELECT mon_id,mon_simbolo FROM con_moneda where mon_id > 1 and mon_eliminado='No'";

                                        $conec->ejecutar($sql);

                                        $num = $conec->get_num_registros();

                                        for ($i = 0; $i < $num; $i++) {
                                            $objeto = $conec->get_objeto();
                                            ?>
                                            <tr>
                                                <td><?php echo $objeto->mon_simbolo; ?></td>
                                                <td>
                                                    <input name="moneda_valor<?php echo $i; ?>" id="moneda_valor<?php echo $i; ?>"  type="text" class="caja_texto" value="" size="10" autocomplete="off">
                                                    <input name="moneda_id<?php echo $i; ?>" id="moneda_id<?php echo $i; ?>"  type="hidden" class="caja_texto" value="<?php echo $objeto->mon_id; ?>" >
                                                </td>
                                                <td>
                                                    <input name="moneda_valor_compra<?php echo $i; ?>" id="moneda_valor_compra<?php echo $i; ?>"  type="text" class="caja_texto" value="" size="10" autocomplete="off">
                                                </td>
                                                <td>
                                                    <input name="moneda_valor_venta<?php echo $i; ?>" id="moneda_valor_venta<?php echo $i; ?>"  type="text" class="caja_texto" value="" size="10" autocomplete="off">
                                                </td>
                                            </tr>
                                            <?php
                                            $conec->siguiente();
                                        }
                                        ?>
                                    <input name="numero_monedas" id="numero_monedas"  type="hidden" class="caja_texto" value="<?php echo $num; ?>" >
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <script>
                            $('#tca_fecha').mask('99/99/9999');
                        </script>


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
        $conec = new ADO();

        $num = $_POST['numero_monedas'];

        for ($i = 0; $i < $num; $i++) {
            $sql = "insert into con_tipo_cambio (tca_mon_id,tca_fecha,tca_valor,tca_valor_compra,tca_valor_venta) 
		values ('" . $_POST["moneda_id$i"] . "','" . FUNCIONES::get_fecha_mysql($_POST['tca_fecha']). "','" . $_POST["moneda_valor$i"] . "','{$_POST["moneda_valor_compra$i"]}','{$_POST["moneda_valor_venta$i"]}')";


            $conec->ejecutar($sql);
        }

        $mensaje = 'Tipo de Cambio Agregado Correctamente';

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function formulario_modificar($tipo) {

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
        $url.='&id=' . $_GET['id']."&mon_id=$_GET[mon_id]";
        $this->formulario->dibujar_tarea('USUARIO');

        if ($this->mensaje <> "") {
            $this->formulario->dibujar_mensaje($this->mensaje);
        }
//        echo "select *  from con_tipo_cambio,con_moneda where tca_mon_id=mon_id and tca_fecha='$_GET[id]' and tca_mon_id='$_GET[mon_id]'";
        $objeto=  FUNCIONES::objeto_bd_sql("select *  from con_tipo_cambio,con_moneda where tca_mon_id=mon_id and tca_fecha='$_GET[id]' and tca_mon_id='$_GET[mon_id]'");
        
        ?>
        <script>
                                    function ValidarNumero(e)
                                    {
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
        </script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_usuario" name="frm_usuario" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span>Fecha</div>
                            <div id="CajaInput">
                                <input type="hidden" name="tca_fecha" id="tca_fecha" value="<?php echo $objeto->tca_fecha;?>">
                                <input type="hidden" name="tca_mon_id" id="tca_mon_id" value="<?php echo $objeto->tca_mon_id;?>">
                                <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($objeto->tca_fecha);?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Valores</div>
                            <div id="CajaInput">
                                <table width="300px" class="tablaLista" cellpadding="0" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Moneda</th>
                                            <th>Valor</th>
                                            <th>Valor Compra</th>
                                            <th>Valor Venta</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr>
                                            <td><?php echo $objeto->mon_Simbolo; ?></td>
                                            <td>
                                                <input name="tca_valor" id="tca_valor" type="text" class="caja_texto" value="<?php echo $objeto->tca_valor; ?>" size="10">
                                            </td>
                                            <td>
                                                <input name="tca_valor_compra" id="tca_valor_compra" type="text" class="caja_texto" value="<?php echo $objeto->tca_valor_compra; ?>" size="10">
                                            </td>
                                            <td>
                                                <input name="tca_valor_venta" id="tca_valor_venta" type="text" class="caja_texto" value="<?php echo $objeto->tca_valor_venta; ?>" size="10">
                                            </td>
                                            
                                        </tr>
                                    </tbody>
                                </table>
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

    function modificar_tcp() {
        $conec = new ADO();
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
        $sql_update="update con_tipo_cambio 
            set tca_valor='$_POST[tca_valor]', tca_valor_compra='$_POST[tca_valor_compra]', tca_valor_venta='$_POST[tca_valor_venta]'
                where tca_fecha='$_POST[tca_fecha]' and tca_mon_id='$_POST[tca_mon_id]'";
        echo $sql_update;
        $conec->ejecutar($sql_update);

        $mensaje = 'Tipo de Cambio Modificado Correctamente';

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

/////////////////////////////////////////////--- TAREA : PERIODOS ---/////////////////////////////////////////

    function guardar_periodo() {
        include_once("clases/registrar_control_numero.class.php");

        $control_numero = new CONTROL_NUMERO();

        $conec = new ADO();

        $sql = "insert into con_periodo(pdo_descripcion,pdo_fecha_inicio,pdo_fecha_fin,pdo_estado,pdo_ges_id)
		values ('" . $_POST['peri_descripcion'] . "','" . $_POST['peri_fecha_inicio'] . "','" . $_POST['peri_fecha_fin'] . "','" . $_POST['peri_estado'] . "','" . $_GET['id'] . "')";

        $conec->ejecutar($sql, false);

        $llave = mysql_insert_id();

        $mes = substr($_POST['peri_fecha_inicio'], 5, 2);
        $anno = substr($_POST['peri_fecha_inicio'], 0, 4);

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

        $mensaje = 'Periodo Modificado Correctamente';

        $sql = "update cont_periodo set

		peri_descripcion='" . $_POST['pro_nombre'] . "',
		pro_codigo='" . $_POST['pro_codigo'] . "',
		pro_descripcion='" . $_POST['pro_descripcion'] . "',
		pro_precio='" . $_POST['pro_precio'] . "',
		pro_cant_min='" . $_POST['pro_cant_min'] . "',
		pro_precio_cre='" . $_POST['pro_precio_cre'] . "',
		pro_precio_cre2='" . $_POST['pro_precio_cre2'] . "',
		pro_precio_com='" . $_POST['pro_precio_com'] . "',
		pro_moneda='" . $_POST['pro_moneda'] . "',
		pro_tipo='" . $_POST['pro_tipo'] . "',
		pro_destacado='" . $_POST['pro_destacado'] . "',
		pro_des_img_1='" . $_POST['pro_des_img_1'] . "',
		pro_des_img_2='" . $_POST['pro_des_img_2'] . "',
		pro_des_img_3='" . $_POST['pro_des_img_3'] . "',
		pro_des_img_4='" . $_POST['pro_des_img_4'] . "',
		pro_unidad_medida='" . $_POST['pro_unidad_medida'] . "'
		where pro_id= '" . $_GET['pro_id'] . "'";

        $conec->ejecutar($sql);

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=PRODUCTOS&id=' . $_GET['id']);
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

        $sql = "select * from cont_periodo where peri_id ='" . $_GET['peri_id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['peri_id'] = $objeto->peri_id;

        $_POST['peri_descripcion'] = $objeto->peri_descripcion;

        $_POST['peri_fecha_inicio'] = $objeto->peri_fecha_inicio;

        $_POST['peri_fecha_fin'] = $objeto->peri_fecha_fin;

        $_POST['peri_estado'] = $objeto->peri_estado;

        $_POST['peri_ges_id'] = $objeto->peri_ges_id;
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
            $valores[$i]["valor"] = $_POST['peri_fecha_inicio'];
            $valores[$i]["tipo"] = "fecha";
            $valores[$i]["requerido"] = true;
            $i++;
            $valores[$i]["etiqueta"] = "Fecha Fin";
            $valores[$i]["valor"] = $_POST['peri_fecha_fin'];
            $valores[$i]["tipo"] = "fecha";
            $valores[$i]["requerido"] = true;
            $i++;
            $valores[$i]["etiqueta"] = "Estado";
            $valores[$i]["valor"] = $_POST['peri_estado'];
            $valores[$i]["tipo"] = "numero";
            $valores[$i]["requerido"] = true;

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
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url . '&peri_id=' . $_GET['peri_id']; ?>" method="POST" enctype="multipart/form-data">

                <div id="FormSent">



                    <div class="Subtitulo">Productos - Categoria <?php //echo $this->nombre_categoria($_GET['id']);  ?> </div>

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
                                <input readonly="readonly" class="caja_texto" name="peri_fecha_inicio" id="peri_fecha_inicio" size="12" value="<?php if ($_POST['peri_fecha_inicio'] <> '')
            echo $_POST['peri_fecha_inicio'];
        else
            echo date('Y-m-d');
        ?>" type="text">
                                <input name="but_fecha_pago" id="but_fecha_pago" class="boton_fecha" value="..." type="button">
                                <script type="text/javascript">
                                    Calendar.setup({inputField: "peri_fecha_inicio"
                                                , ifFormat: "%Y-%m-%d",
                                        button: "but_fecha_pago"
                                    });
                                </script>
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span>Fecha Fin</div>
                            <div id="CajaInput">
                                <input readonly="readonly" class="caja_texto" name="peri_fecha_fin" id="peri_fecha_fin" size="12" value="<?php if ($_POST['peri_fecha_fin'] <> '')
            echo $_POST['peri_fecha_fin'];
        else
            echo date('Y-m-d');
        ?>" type="text">
                                <input name="but_fecha_pago2" id="but_fecha_pago2" class="boton_fecha" value="..." type="button">
                                <script type="text/javascript">
                                    Calendar.setup({inputField: "peri_fecha_fin"
                                                , ifFormat: "%Y-%m-%d",
                                        button: "but_fecha_pago2"
                                    });
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

                                <input type="button" class="boton" name="" value="Volver" onclick="location.href = '<?php if ($_GET['acc'] == "MODIFICAR_PERIODO")
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
		inner join con_tipo_cambio on (ges_id=pdo_ges_id)
		where pdo_ges_id='" . $_GET['id'] . "'";

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
                                <td><a href="gestor.php?mod=gestion&tarea=PERIODOS&acc=MODIFICAR_PERIODO&peri_id=<?php echo $objeto->peri_id; ?>&id=<?php echo $_GET['id']; ?>"><img src="images/b_edit.png" alt="MODIFICAR" title="MODIFICAR" border="0"></a></td>
                                <td><a href="gestor.php?mod=gestion&tarea=PERIODOS&acc=ELIMINAR_PERIODO&peri_id=<?php echo $objeto->peri_id; ?>&id=<?php echo $_GET['id']; ?>"><img src="images/b_drop.png" alt="ELIMINAR" title="ELIMINAR" border="0"></a></td>   
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
                <link rel="stylesheet" href="css/zTreeStyle/zTreeStyle.css" type="text/css"> 
                <!--<link rel="stylesheet" href="css/classic-impromptu.css" type="text/css">--> 
                <script type="text/javascript" src="js/ztree/jquery.ztree.core-3.5.min.js"></script>
                <script type="text/javascript" src="js/ztree/jquery.ztree.excheck-3.5.min.js"></script> 
                <script type="text/javascript" src="js/ztree/jquery.ztree.exedit-3.5.js"></script>


                <script type="text/javascript">
                                    var treeURL = "sueltos/padres.php";
                                    var setting = {
                                        async: {
                                            enable: true,
                                            url: treeURL,
                                            autoParam: ["id", "name=n", "level=lv"],
                                            otherParam: {"otherParam": "zTreeAsyncTest"},
                                            dataFilter: filter
                                        },
                                        view: {expandSpeed: "",
                                            addHoverDom: addHoverDom,
                                            removeHoverDom: removeHoverDom,
                                            selectedMulti: false
                                        },
                                        edit: {
                                            enable: true
                                        },
                                        data: {
                                            simpleData: {
                                                enable: true
                                            }
                                        },
                                        callback: {
                                            beforeRemove: beforeRemove,
                                            //onRemove: onRemove,
                                            beforeRename: beforeRename
                                        }
                                    };

                                    function filter(treeId, parentNode, childNodes) {
                                        if (!childNodes)
                                            return null;
                                        for (var i = 0, l = childNodes.length; i < l; i++) {
                                            childNodes[i].name = childNodes[i].name.replace(/\.n/g, '.');
                                        }
                                        return childNodes;
                                    }
                                    function onRemove(treeId, treeNode) {
                                        //alert(treeNode.i);
                                    }
                                    function beforeRemove(treeId, treeNode) {
                                        var zTree = $.fn.zTree.getZTreeObj("treeDemo");
                                        zTree.selectNode(treeNode);

                                        //return confirm("Confirm delete node '" + treeNode.name + "'");


                                        //return confirm("Confirm delete node '" + treeNode.name + "' it?");
                                        // "__"+treeNode.id+"__"+treeNode.name+"__"
                                        /*
                                         var zTree = $.fn.zTree.getZTreeObj("treeDemo");
                                         zTree.treeObj.trigger(consts.event.REMOVE, [treeNode.id, treeNode]);
                                         zTree.removeNode(treeNode);
                                         */
                                        /*
                                         $.prompt("Estas seguro de elimunar?",{
                                         buttons:{Anular:true, Cancelar:false},
                                         callback: function(v,m,f){
                                                 
                                         if(v){
                                                 
                                         //_removeNode
                                         //view.removeNode(setting, node);
                                         //setting.treeObj.trigger(consts.event.REMOVE, [setting.treeId, node]);
                                         $.ajax({
                                         type: "POST",
                                         url: treeURL,
                                         data: "&tarea=eliminar&id="+treeNode.id,
                                         success: function (dataCheck) {
                                         if(dataCheck=="si"){
                                         return true;
                                         } else {
                                         return false;
                                         }
                                         }
                                         });
                                                 
                                         } else {
                                         return false;
                                         }						
                                         }
                                         });
                                         */
                                        //return false;
                                    }
                                    function beforeRename(treeId, treeNode, newName) {
                                        if (newName.length == 0) {
                                            alert("Node name can not be empty.");
                                            return false;
                                        }
                                        return true;
                                    }

                                    var newCount = 1;
                                    function addHoverDom(treeId, treeNode) {
                                        var sObj = $("#" + treeNode.tId + "_span");
                                        if ($("#addBtn_" + treeNode.id).length > 0)
                                            return;
                                        var addStr = "<span class='button add' id='addBtn_" + treeNode.id
                                                + "' title='add node' onfocus='this.blur();'></span>";
                                        sObj.append(addStr);
                                        var btn = $("#addBtn_" + treeNode.id);
                                        if (btn)
                                            btn.bind("click", function() {
                                                var zTree = $.fn.zTree.getZTreeObj("treeDemo");
                                                zTree.addNodes(treeNode, {id: (100 + newCount), pId: treeNode.id, name: "new node" + (newCount++)});
                                            });
                                    }
                                    ;
                                    function removeHoverDom(treeId, treeNode) {
                                        $("#addBtn_" + treeNode.id).unbind().remove();
                                    }
                                    ;
                                    function trim(myString)
                                    {
                                        return myString.replace(/^\s+/g, '').replace(/\s+$/g, '')
                                    }

                                    $(document).ready(function() {

                                        $.fn.zTree.init($("#treeDemo"), setting);


                                    });

                </script>
                <!--- NUEVO CODIGO -->
                <TABLE border="0" height="600px" align="left">
                    <TR>
                        <TD width="260px" align="left" valign="top" style="BORDER-RIGHT: #999999 1px dashed">
                            <ul id="treeDemo" class="ztree"></ul>
                        </TD>
                        <TD width="770px" align="left" valign="top">

                            <IFRAME ID="testIframe" Name="testIframe" FRAMEBORDER="0" SCROLLING="AUTO" width="100%"  height="600px" SRC=""> </iframe>

                        </TD>
                    </TR>
                </TABLE>
                <!--- NUEVO CODIGO FIN -->
        <?php
    }

    function formulario_tabs() {
        echo 'PONELO LOKO';
    }

}
?>