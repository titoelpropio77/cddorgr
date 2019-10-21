<?php

class UV_PARAMETROS extends UV {

    function UV_PARAMETROS() {
        parent::__construct();
    }

    function cuentas() {
        $sql = "select * from urbanizacion
				where urb_id = '" . $_GET['id'] . "'";
        $urb = FUNCIONES::objeto_bd_sql($sql);
        if ($_POST[form] == 'ok') {
            if ($urb->urb_tipo == 'Interno') {
                $this->modificar_cuentas($urb);
            } elseif ($urb->urb_tipo == 'Externo') {
               $this->modificar_cuentas_ext($urb);
                // $this->modificar_cuentas($urb);
            }
        } else {
            if ($urb->urb_tipo == 'Interno') {
                $this->ver_cuentas($urb);
            } elseif ($urb->urb_tipo == 'Externo') {
               $this->ver_cuentas_ext($urb);
                // $this->ver_cuentas($urb);
            }
        }
    }

    function modificar_cuentas_ext($urb) {
        $conec = new ADO();
        $sql = "update urbanizacion set                 
                    urb_cta_por_pagar='$_POST[urb_cta_por_pagar]',
                    urb_cue_desistimiento='$_POST[urb_cue_desistimiento]',
                    urb_reserva='$_POST[urb_reserva]'
                where urb_id ='$_GET[id]'";
//        echo $sql.'<br>';
        $conec->ejecutar($sql);
        $mensaje = 'Cuentas de Urbanización Modificada Correctamente!!!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function modificar_cuentas($urb) {
        $conec = new ADO();
        $sql = "update urbanizacion set                 
                urb_costo='$_POST[urb_costo]',
                urb_costo_diferido='$_POST[urb_costo_diferido]',
                urb_inv_terrenos='$_POST[urb_inv_terrenos]',
                urb_inv_terrenos_adj='$_POST[urb_inv_terrenos_adj]',
                urb_ing_diferido='$_POST[urb_ing_diferido]',
                
                urb_reserva='$_POST[urb_reserva]',
                
                urb_ing_reserva='$_POST[urb_ing_reserva]',
                urb_ing_cambio_tit='$_POST[urb_ing_cambio_tit]',
                urb_ing_cambio_ubi='$_POST[urb_ing_cambio_ubi]',
                urb_ing_fusion='$_POST[urb_ing_fusion]',
                
                urb_cta_por_cobrar_usd='$_POST[urb_cta_por_cobrar_usd]',
                urb_ing_por_venta_usd='$_POST[urb_ing_por_venta_usd]',
                urb_ing_por_interes_usd='$_POST[urb_ing_por_interes_usd]',
                urb_ing_por_form_usd='$_POST[urb_ing_por_form_usd]',
                urb_ing_por_mora_usd='$_POST[urb_ing_por_mora_usd]',
                
                urb_cta_por_cobrar_bs='$_POST[urb_cta_por_cobrar_bs]',
                urb_ing_por_venta_bs='$_POST[urb_ing_por_venta_bs]',
                urb_ing_por_interes_bs='$_POST[urb_ing_por_interes_bs]',
                urb_ing_por_form_bs='$_POST[urb_ing_por_form_bs]',
                urb_ing_por_mora_bs='$_POST[urb_ing_por_mora_bs]',
                    
                urb_cue_costo_producto='$_POST[urb_cue_costo_producto]',
                urb_cue_costo_diferido_producto='$_POST[urb_cue_costo_diferido_producto]',
                urb_cue_inv_producto='$_POST[urb_cue_inv_producto]',
                urb_cue_reserva_producto='$_POST[urb_cue_reserva_producto]',
                urb_cue_por_cobrar_producto='$_POST[urb_cue_por_cobrar_producto]',
                urb_cue_ing_producto='$_POST[urb_cue_ing_producto]',
                urb_cue_ing_diferido_producto='$_POST[urb_cue_ing_diferido_producto]',
                    
                urb_cue_descuento_capital_usd='$_POST[urb_cue_descuento_capital_usd]',
                urb_cue_descuento_capital_bs='$_POST[urb_cue_descuento_capital_bs]',
                urb_cue_incremento_capital_usd='$_POST[urb_cue_incremento_capital_usd]',
                urb_cue_incremento_capital_bs='$_POST[urb_cue_incremento_capital_bs]',
                urb_cue_inv_producto_adj='$_POST[urb_cue_inv_producto_adj]'
                where urb_id ='$_GET[id]'";
//        echo $sql.'<br>';
        $conec->ejecutar($sql);
        $mensaje = 'Cuentas de Urbanización Modificada Correctamente!!!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function ver_cuentas_ext($urb) {

        $_POST['urb_cta_por_pagar'] = $urb->urb_cta_por_pagar;
        $_POST['urb_reserva'] = $urb->urb_reserva;
        $_POST['urb_cue_desistimiento'] = $urb->urb_cue_desistimiento;

//        $no_existe="No existe una cuenta con el mismo codigo en esta gesti&oacute;n";
        $no_existe = "NO EXISTE UNA CUENTA CON EL MISMO CODIGO EN ESTA GESTI&Oacute;N";

        if ($_POST['urb_cta_por_pagar'] != '')
            $txt_urb_cta_por_pagar = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cta_por_pagar]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_reserva'] != '')
            $txt_urb_reserva = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_reserva]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_cue_desistimiento'] != '')
            $txt_urb_cue_desistimiento = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_desistimiento]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        ?>
        <script src="js/util.js"></script>
        <!--AutoSuggest-->
        <script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="js/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css" media="screen" charset="utf-8" />
        <!--AutoSuggest-->
        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=uv&tarea=CUENTAS&id=<?PHP echo $_GET[id]; ?>" name="frm_sentencia">
            <input type="hidden" name="form" value="ok">
            <div class="Subtitulo">Contabilidad</div>
            <div id="ContenedorSeleccion">
                <style>
                    .Etiqueta{width: 170px;}
                    .cgreen{color: #059101}
                    .corange{color: #e44b00}
                </style>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Cuenta Por Pagar</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cta_por_pagar" id="urb_cta_por_pagar" size="25" value="<?php echo $_POST['urb_cta_por_pagar']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cta_por_pagar == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cta_por_pagar" id="txt_urb_cta_por_pagar" size="70" value="<?php echo $txt_urb_cta_por_pagar == '' ? $no_existe : $txt_urb_cta_por_pagar; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Reserva</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_reserva" id="urb_reserva" size="25" value="<?php echo $_POST['urb_reserva']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_reserva == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_reserva" id="txt_urb_reserva" size="70" value="<?php echo $txt_urb_reserva == '' ? $no_existe : $txt_urb_reserva; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Cuenta Por Pagar Desistimiento</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_desistimiento" id="urb_cue_desistimiento" size="25" value="<?php echo $_POST['urb_cue_desistimiento']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_desistimiento == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_desistimiento" id="txt_urb_cue_desistimiento" size="70" value="<?php echo $txt_urb_cue_desistimiento == '' ? $no_existe : $txt_urb_cue_desistimiento; ?>" readonly="">
                    </div>
                </div>
            </div>

            <div id="ContenedorDiv">
                <div id="CajaBotones">
                    <center>
                        <input type="button" id="btn_guardar" class="boton" name="" value="Guardar">
                        <input type="reset" class="boton" name="" value="Cancelar">
                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                    </center>
                </div>
            </div>
        </form>

        <script>

            var projects = JSON.parse('<?php echo FUNCIONES::cuentas_json(); ?>');
            function autosuggest(inputHidden, inputVisible, tipo) {
                autocomplete_ui(
                        {
                            input: '#' + inputVisible,
                            bus_info: true,
                            lista: projects,
                            select: function (obj) {
                                $("#" + inputVisible).val(obj.info);
                                $("#" + inputVisible).attr("data-cod", obj.info);
                                $("#txt_" + inputVisible).val(obj.value);
                                $("#txt_" + inputVisible).removeClass('txt_rojo');
                                $(".msCorrecto").hide();
        //                            agregar_cuenta(obj,input);
                                return false;
                            }

                        }
                );
            }
            autosuggest("", "urb_cta_por_pagar");
            autosuggest("", "urb_reserva");
            autosuggest("", "urb_cue_desistimiento");

            $('#frm_sentencia').submit(function () {
                return false;
            });
            $('#btn_guardar').click(function () {
                document.frm_sentencia.submit();
            });

        </script>
        <?php
    }

    function ver_cuentas($urb) {

        $_POST['urb_costo'] = $urb->urb_costo;
        $_POST['urb_costo_diferido'] = $urb->urb_costo_diferido;
        $_POST['urb_inv_terrenos'] = $urb->urb_inv_terrenos;
        $_POST['urb_inv_terrenos_adj'] = $urb->urb_inv_terrenos_adj;
        $_POST['urb_ing_diferido'] = $urb->urb_ing_diferido;

        $_POST['urb_reserva'] = $urb->urb_reserva;
        $_POST['urb_ing_reserva'] = $urb->urb_ing_reserva;
        $_POST['urb_ing_cambio_tit'] = $urb->urb_ing_cambio_tit;
        $_POST['urb_ing_cambio_ubi'] = $urb->urb_ing_cambio_ubi;
        $_POST['urb_ing_fusion'] = $urb->urb_ing_fusion;

        $_POST['urb_cta_por_cobrar_usd'] = $urb->urb_cta_por_cobrar_usd;
        $_POST['urb_ing_por_venta_usd'] = $urb->urb_ing_por_venta_usd;
        $_POST['urb_ing_por_interes_usd'] = $urb->urb_ing_por_interes_usd;
        $_POST['urb_ing_por_form_usd'] = $urb->urb_ing_por_form_usd;
        $_POST['urb_ing_por_mora_usd'] = $urb->urb_ing_por_mora_usd;

//        $_POST['urb_reserva_bs'] = $objeto->urb_reserva_bs;
        $_POST['urb_cta_por_cobrar_bs'] = $urb->urb_cta_por_cobrar_bs;
        $_POST['urb_ing_por_venta_bs'] = $urb->urb_ing_por_venta_bs;
        $_POST['urb_ing_por_interes_bs'] = $urb->urb_ing_por_interes_bs;
        $_POST['urb_ing_por_form_bs'] = $urb->urb_ing_por_form_bs;
        $_POST['urb_ing_por_mora_bs'] = $urb->urb_ing_por_mora_bs;
        
        $_POST['urb_cue_costo_producto'] = $urb->urb_cue_costo_producto;
        $_POST['urb_cue_costo_diferido_producto'] = $urb->urb_cue_costo_diferido_producto;
        $_POST['urb_cue_inv_producto'] = $urb->urb_cue_inv_producto;
        $_POST['urb_cue_reserva_producto'] = $urb->urb_cue_reserva_producto;
        $_POST['urb_cue_por_cobrar_producto'] = $urb->urb_cue_por_cobrar_producto;
        $_POST['urb_cue_ing_producto'] = $urb->urb_cue_ing_producto;
        $_POST['urb_cue_ing_diferido_producto'] = $urb->urb_cue_ing_diferido_producto;
        $_POST['urb_cue_inv_producto_adj'] = $urb->urb_cue_inv_producto_adj;
        
        $_POST['urb_cue_descuento_capital_bs'] = $urb->urb_cue_descuento_capital_bs;
        $_POST['urb_cue_descuento_capital_usd'] = $urb->urb_cue_descuento_capital_usd;
        $_POST['urb_cue_incremento_capital_bs'] = $urb->urb_cue_incremento_capital_bs;
        $_POST['urb_cue_incremento_capital_usd'] = $urb->urb_cue_incremento_capital_usd;

//        $no_existe="No existe una cuenta con el mismo codigo en esta gesti&oacute;n";
        $no_existe = "NO EXISTE UNA CUENTA CON EL MISMO CODIGO EN ESTA GESTI&Oacute;N";

        if ($_POST['urb_costo'] != '')
            $txt_urb_costo = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_costo]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_costo_diferido'] != '')
            $txt_urb_costo_diferido = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_costo_diferido]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_inv_terrenos'] != '')
            $txt_urb_inv_terrenos = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_inv_terrenos]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_inv_terrenos_adj'] != '')
            $txt_urb_inv_terrenos_adj = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_inv_terrenos_adj]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_diferido'] != '')
            $txt_urb_ing_diferido = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_diferido]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");

        if ($_POST['urb_reserva'] != '')
            $txt_urb_reserva = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_reserva]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");

        if ($_POST['urb_ing_reserva'] != '')
            $txt_urb_ing_reserva = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_reserva]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_cambio_tit'] != '')
            $txt_urb_ing_cambio_tit = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_cambio_tit]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_cambio_ubi'] != '')
            $txt_urb_ing_cambio_ubi = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_cambio_ubi]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_fusion'] != '')
            $txt_urb_ing_fusion = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_fusion]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");

        if ($_POST['urb_cta_por_cobrar_usd'] != '')
            $txt_urb_cta_por_cobrar_usd = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cta_por_cobrar_usd]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_por_venta_usd'] != '')
            $txt_urb_ing_por_venta_usd = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_por_venta_usd]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_por_interes_usd'] != '')
            $txt_urb_ing_por_interes_usd = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_por_interes_usd]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_por_form_usd'] != '')
            $txt_urb_ing_por_form_usd = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_por_form_usd]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_por_mora_usd'] != '')
            $txt_urb_ing_por_mora_usd = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_por_mora_usd]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");


        if ($_POST['urb_cta_por_cobrar_bs'] != '')
            $txt_urb_cta_por_cobrar_bs = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cta_por_cobrar_bs]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_por_venta_bs'] != '')
            $txt_urb_ing_por_venta_bs = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_por_venta_bs]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_por_interes_bs'] != '')
            $txt_urb_ing_por_interes_bs = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_por_interes_bs]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_por_form_bs'] != '')
            $txt_urb_ing_por_form_bs = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_por_form_bs]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_ing_por_mora_bs'] != '')
            $txt_urb_ing_por_mora_bs = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_ing_por_mora_bs]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        
        //CUENTAS DE CASAS
        if ($_POST['urb_cue_costo_producto'] != '')
            $txt_urb_cue_costo_producto = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_costo_producto]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        
        if ($_POST['urb_cue_costo_diferido_producto'] != '')
            $txt_urb_cue_costo_diferido_producto = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_costo_diferido_producto]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        
        if ($_POST['urb_cue_inv_producto'] != '')
            $txt_urb_cue_inv_producto = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_inv_producto]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        
        if ($_POST['urb_cue_reserva_producto'] != '')
            $txt_urb_cue_reserva_producto = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_reserva_producto]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        
        if ($_POST['urb_cue_por_cobrar_producto'] != '')
            $txt_urb_cue_por_cobrar_producto = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_por_cobrar_producto]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        
        if ($_POST['urb_cue_ing_producto'] != '')
            $txt_urb_cue_ing_producto = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_ing_producto]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        
        if ($_POST['urb_cue_ing_diferido_producto'] != '')
            $txt_urb_cue_ing_diferido_producto = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_ing_diferido_producto]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        
        if ($_POST['urb_cue_inv_producto_adj'] != '')
            $txt_urb_cue_inv_producto_adj = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_inv_producto_adj]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        
        if ($_POST['urb_cue_descuento_capital_usd'] != '')
            $txt_urb_cue_descuento_capital_usd = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_descuento_capital_usd]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_cue_descuento_capital_bs'] != '')
            $txt_urb_cue_descuento_capital_bs = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_descuento_capital_bs]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_cue_incremento_capital_usd'] != '')
            $txt_urb_cue_incremento_capital_usd = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_incremento_capital_usd]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        if ($_POST['urb_cue_incremento_capital_bs'] != '')
            $txt_urb_cue_incremento_capital_bs = FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$_POST[urb_cue_incremento_capital_bs]' and cue_ges_id='$_SESSION[ges_id]'", "cue_descripcion");
        
        //CUENTAS DE CASAS
        
        $this->formulario->dibujar_titulo("CUENTAS CONTABLES - " . strtoupper($urb->urb_nombre));
        ?>

        <!--AutoSuggest-->
        <script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css" media="screen" charset="utf-8" />
        <!--AutoSuggest-->
        
        <style>
            .Subtitulo:hover{
                cursor: pointer;
                background-color: #92BF4C;
                color: white;
            }
        </style>
        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=uv&tarea=CUENTAS&id=<?PHP echo $_GET[id]; ?>" name="frm_sentencia">
            <input type="hidden" name="form" value="ok">
            <div target-id="cuentas_lote" class="Subtitulo">Contabilidad - Lotes</div>
            <div id="ContenedorSeleccion" class="cuentas_lote">
                <style>
                    .Etiqueta{width: 170px;}
                    .cgreen{color: #059101}
                    .corange{color: #e44b00}
                </style>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Costo</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_costo" id="urb_costo" size="25" value="<?php echo $_POST['urb_costo']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_costo == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_costo" id="txt_urb_costo" size="70" value="<?php echo $txt_urb_costo == '' ? $no_existe : $txt_urb_costo; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Costo Diferido</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_costo_diferido" id="urb_costo_diferido" size="25" value="<?php echo $_POST['urb_costo_diferido']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_costo_diferido == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_costo_diferido" id="txt_urb_costo_diferido" size="70" value="<?php echo $txt_urb_costo_diferido == '' ? $no_existe : $txt_urb_costo_diferido; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Inventario de Terrenos</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_inv_terrenos" id="urb_inv_terrenos" size="25" value="<?php echo $_POST['urb_inv_terrenos']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_inv_terrenos == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_inv_terrenos" id="txt_urb_inv_terrenos" size="70" value="<?php echo $txt_urb_inv_terrenos == '' ? $no_existe : $txt_urb_inv_terrenos; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Inventario de Terrenos Adj.</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_inv_terrenos_adj" id="urb_inv_terrenos_adj" size="25" value="<?php echo $_POST['urb_inv_terrenos_adj']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_inv_terrenos_adj == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_inv_terrenos_adj" id="txt_urb_inv_terrenos_adj" size="70" value="<?php echo $txt_urb_inv_terrenos_adj == '' ? $no_existe : $txt_urb_inv_terrenos_adj; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso Diferido</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_diferido" id="urb_ing_diferido" size="25" value="<?php echo $_POST['urb_ing_diferido']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_diferido == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_diferido" id="txt_urb_ing_diferido" size="70" value="<?php echo $txt_urb_ing_diferido == '' ? $no_existe : $txt_urb_ing_diferido; ?>" readonly="">
                    </div>
                </div>

                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Reserva </div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_reserva" id="urb_reserva" size="25" value="<?php echo $_POST['urb_reserva']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_reserva == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_reserva" id="txt_urb_reserva" size="70" value="<?php echo $txt_urb_reserva == '' ? $no_existe : $txt_urb_reserva; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso Reserva </div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_reserva" id="urb_ing_reserva" size="25" value="<?php echo $_POST['urb_ing_reserva']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_reserva == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_reserva" id="txt_urb_ing_reserva" size="70" value="<?php echo $txt_urb_ing_reserva == '' ? $no_existe : $txt_urb_ing_reserva; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso Cambio Titular </div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_cambio_tit" id="urb_ing_cambio_tit" size="25" value="<?php echo $_POST['urb_ing_cambio_tit']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_cambio_tit == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_cambio_tit" id="txt_urb_ing_cambio_tit" size="70" value="<?php echo $txt_urb_ing_cambio_tit == '' ? $no_existe : $txt_urb_ing_cambio_tit; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso Cambio Ubicacion </div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_cambio_ubi" id="urb_ing_cambio_ubi" size="25" value="<?php echo $_POST['urb_ing_cambio_ubi']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_cambio_ubi == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_cambio_ubi" id="txt_urb_ing_cambio_ubi" size="70" value="<?php echo $txt_urb_ing_cambio_ubi == '' ? $no_existe : $txt_urb_ing_cambio_ubi; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso Fusion de Venta</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_fusion" id="urb_ing_fusion" size="25" value="<?php echo $_POST['urb_ing_fusion']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_fusion == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_fusion" id="txt_urb_ing_fusion" size="70" value="<?php echo $txt_urb_ing_fusion == '' ? $no_existe : $txt_urb_ing_fusion; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Cuentas por Cobrar <b class="cgreen">($us.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cta_por_cobrar_usd" id="urb_cta_por_cobrar_usd" size="25" value="<?php echo $_POST['urb_cta_por_cobrar_usd']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cta_por_cobrar_usd == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cta_por_cobrar_usd" id="txt_urb_cta_por_cobrar_usd" size="70" value="<?php echo $txt_urb_cta_por_cobrar_usd == '' ? $no_existe : $txt_urb_cta_por_cobrar_usd; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso por Venta <b class="cgreen">($us.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_por_venta_usd" id="urb_ing_por_venta_usd" size="25" value="<?php echo $_POST['urb_ing_por_venta_usd']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_por_venta_usd == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_por_venta_usd" id="txt_urb_ing_por_venta_usd" size="70" value="<?php echo $txt_urb_ing_por_venta_usd == '' ? $no_existe : $txt_urb_ing_por_venta_usd; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso por Interes <b class="cgreen">($us.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_por_interes_usd" id="urb_ing_por_interes_usd" size="25" value="<?php echo $_POST['urb_ing_por_interes_usd']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_por_interes_usd == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_por_interes_usd" id="txt_urb_ing_por_interes_usd" size="70" value="<?php echo $txt_urb_ing_por_interes_usd == '' ? $no_existe : $txt_urb_ing_por_interes_usd; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso por Formulario <b class="cgreen">($us.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_por_form_usd" id="urb_ing_por_form_usd" size="25" value="<?php echo $_POST['urb_ing_por_form_usd']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_por_form_usd == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_por_form_usd" id="txt_urb_ing_por_form_usd" size="70" value="<?php echo $txt_urb_ing_por_form_usd == '' ? $no_existe : $txt_urb_ing_por_form_usd; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso por Mora <b class="cgreen">($us.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_por_mora_usd" id="urb_ing_por_mora_usd" size="25" value="<?php echo $_POST['urb_ing_por_mora_usd']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_por_mora_usd == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_por_mora_usd" id="txt_urb_ing_por_mora_usd" size="70" value="<?php echo $txt_urb_ing_por_mora_usd == '' ? $no_existe : $txt_urb_ing_por_mora_usd; ?>" readonly="">
                    </div>
                </div>
                
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Descuento Capital <b class="cgreen">($us.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_descuento_capital_usd" id="urb_cue_descuento_capital_usd" size="25" value="<?php echo $_POST['urb_cue_descuento_capital_usd']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_descuento_capital_usd == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_descuento_capital_usd" id="txt_urb_cue_descuento_capital_usd" size="70" value="<?php echo $txt_urb_cue_descuento_capital_usd == '' ? $no_existe : $txt_urb_cue_descuento_capital_usd; ?>" readonly="">
                    </div>
                </div>
                
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Incremento Capital <b class="cgreen">($us.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_incremento_capital_usd" id="urb_cue_incremento_capital_usd" size="25" value="<?php echo $_POST['urb_cue_incremento_capital_usd']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_incremento_capital_usd == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_incremento_capital_usd" id="txt_urb_cue_incremento_capital_usd" size="70" value="<?php echo $txt_urb_cue_incremento_capital_usd == '' ? $no_existe : $txt_urb_cue_incremento_capital_usd; ?>" readonly="">
                    </div>
                </div>
                
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Descuento Capital <b class="corange">(Bs.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_descuento_capital_bs" id="urb_cue_descuento_capital_bs" size="25" value="<?php echo $_POST['urb_cue_descuento_capital_bs']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_descuento_capital_bs == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_descuento_capital_bs" id="txt_urb_cue_descuento_capital_bs" size="70" value="<?php echo $txt_urb_cue_descuento_capital_bs == '' ? $no_existe : $txt_urb_cue_descuento_capital_bs; ?>" readonly="">
                    </div>
                </div>
                
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Incremento Capital <b class="corange">(Bs.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_incremento_capital_bs" id="urb_cue_incremento_capital_bs" size="25" value="<?php echo $_POST['urb_cue_incremento_capital_bs']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_incremento_capital_bs == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_incremento_capital_bs" id="txt_urb_cue_incremento_capital_bs" size="70" value="<?php echo $txt_urb_cue_incremento_capital_bs == '' ? $no_existe : $txt_urb_cue_incremento_capital_bs; ?>" readonly="">
                    </div>
                </div>
                
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Cuentas por Cobrar <b class="corange">(Bs.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cta_por_cobrar_bs" id="urb_cta_por_cobrar_bs" size="25" value="<?php echo $_POST['urb_cta_por_cobrar_bs']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cta_por_cobrar_bs == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cta_por_cobrar_bs" id="txt_urb_cta_por_cobrar_bs" size="70" value="<?php echo $txt_urb_cta_por_cobrar_bs == '' ? $no_existe : $txt_urb_cta_por_cobrar_bs; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso por Venta <b class="corange">(Bs.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_por_venta_bs" id="urb_ing_por_venta_bs" size="25" value="<?php echo $_POST['urb_ing_por_venta_bs']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_por_venta_bs == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_por_venta_bs" id="txt_urb_ing_por_venta_bs" size="70" value="<?php echo $txt_urb_ing_por_venta_bs == '' ? $no_existe : $txt_urb_ing_por_venta_bs; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso por Interes <b class="corange">(Bs.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_por_interes_bs" id="urb_ing_por_interes_bs" size="25" value="<?php echo $_POST['urb_ing_por_interes_bs']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_por_interes_bs == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_por_interes_bs" id="txt_urb_ing_por_interes_bs" size="70" value="<?php echo $txt_urb_ing_por_interes_bs == '' ? $no_existe : $txt_urb_ing_por_interes_bs; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso por Formulario <b class="corange">(Bs.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_por_form_bs" id="urb_ing_por_form_bs" size="25" value="<?php echo $_POST['urb_ing_por_form_bs']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_por_form_bs == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_por_form_bs" id="txt_urb_ing_por_form_bs" size="70" value="<?php echo $txt_urb_ing_por_form_bs == '' ? $no_existe : $txt_urb_ing_por_form_bs; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso por Mora <b class="corange">(Bs.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_por_mora_bs" id="urb_ing_por_mora_bs" size="25" value="<?php echo $_POST['urb_ing_por_mora_bs']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_ing_por_mora_bs == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_por_mora_bs" id="txt_urb_ing_por_mora_bs" size="70" value="<?php echo $txt_urb_ing_por_mora_bs == '' ? $no_existe : $txt_urb_ing_por_mora_bs; ?>" readonly="">
                    </div>
                </div>

                <!--                <div id="ContenedorDiv">
                                    <div class="Etiqueta"><span class="flechas1">*</span>Costo del terreno</div>
                                    <div id="CajaInput">
                                        <input type="text" class="caja_texto" name="costo_terreno" id="costo_terreno" size="25" value="<?php // echo $_POST['costo_terreno'];    ?>" >
                                    </div>
                                </div>-->

            </div>
            
            <div style="clear: both;"></div>
            <div target-id="cuentas_casa" class="Subtitulo">Contabilidad - Casas</div>
            <div id="ContenedorSeleccion" class="cuentas_casa">
                <style>
                    .Etiqueta{width: 170px;}
                    .cgreen{color: #059101}
                    .corange{color: #e44b00}
                </style>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Costo</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_costo_producto" id="urb_cue_costo_producto" size="25" value="<?php echo $_POST['urb_cue_costo_producto']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_costo_producto == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_costo_producto" id="txt_urb_cue_costo_producto" size="70" value="<?php echo $txt_urb_cue_costo_producto == '' ? $no_existe : $txt_urb_cue_costo_producto; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Costo Diferido</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_costo_diferido_producto" id="urb_cue_costo_diferido_producto" size="25" value="<?php echo $_POST['urb_cue_costo_diferido_producto']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_costo_diferido_producto == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_costo_diferido_producto" id="txt_urb_cue_costo_diferido_producto" size="70" value="<?php echo $txt_urb_cue_costo_diferido_producto == '' ? $no_existe : $txt_urb_cue_costo_diferido_producto; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Inventario de Casas</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_inv_producto" id="urb_cue_inv_producto" size="25" value="<?php echo $_POST['urb_cue_inv_producto']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_inv_producto == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_inv_producto" id="txt_urb_cue_inv_producto" size="70" value="<?php echo $txt_urb_cue_inv_producto == '' ? $no_existe : $txt_urb_cue_inv_producto; ?>" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Inventario de Casas Adj.</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_inv_producto_adj" id="urb_cue_inv_producto_adj" size="25" value="<?php echo $_POST['urb_cue_inv_producto_adj']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_inv_producto_adj == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_inv_producto_adj" id="txt_urb_cue_inv_producto_adj" size="70" value="<?php echo $txt_urb_cue_inv_producto_adj == '' ? $no_existe : $txt_urb_cue_inv_producto_adj; ?>" readonly="">
                    </div>
                </div>
                
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso por Venta <b class="cgreen">($us.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_ing_producto" id="urb_cue_ing_producto" size="25" value="<?php echo $_POST['urb_cue_ing_producto']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_ing_producto == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_ing_producto" id="txt_urb_cue_ing_producto" size="70" value="<?php echo $txt_urb_cue_ing_producto == '' ? $no_existe : $txt_urb_cue_ing_producto; ?>" readonly="">
                    </div>
                </div>
                
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso Diferido</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_ing_diferido_producto" id="urb_cue_ing_diferido_producto" size="25" value="<?php echo $_POST['urb_cue_ing_diferido_producto']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_ing_diferido_producto == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_ing_diferido_producto" id="txt_urb_cue_ing_diferido_producto" size="70" value="<?php echo $txt_urb_cue_ing_diferido_producto == '' ? $no_existe : $txt_urb_cue_ing_diferido_producto; ?>" readonly="">
                    </div>
                </div>

                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Reserva Casa</div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_reserva_producto" id="urb_cue_reserva_producto" size="25" value="<?php echo $_POST['urb_cue_reserva_producto']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_reserva_producto == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_reserva_producto" id="txt_urb_cue_reserva_producto" size="70" value="<?php echo $txt_urb_cue_reserva_producto == '' ? $no_existe : $txt_urb_cue_reserva_producto; ?>" readonly="">
                    </div>
                </div>
<!--                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Ingreso Reserva </div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_ing_reserva" id="urb_ing_reserva" size="25" value="<?php // echo $_POST['urb_ing_reserva']; ?>" >
                        <input type="text" class="caja_texto <?php // echo $txt_urb_ing_reserva == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_ing_reserva" id="txt_urb_ing_reserva" size="70" value="<?php // echo $txt_urb_ing_reserva == '' ? $no_existe : $txt_urb_ing_reserva; ?>" readonly="">
                    </div>
                </div>-->
                
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Cuentas por Cobrar <b class="cgreen">($us.)</b></div>
                    <div id="CajaInput">
                        <input type="text" class="caja_texto" name="urb_cue_por_cobrar_producto" id="urb_cue_por_cobrar_producto" size="25" value="<?php echo $_POST['urb_cue_por_cobrar_producto']; ?>" >
                        <input type="text" class="caja_texto <?php echo $txt_urb_cue_por_cobrar_producto == '' ? 'txt_rojo' : ''; ?>" name="txt_urb_cue_por_cobrar_producto" id="txt_urb_cue_por_cobrar_producto" size="70" value="<?php echo $txt_urb_cue_por_cobrar_producto == '' ? $no_existe : $txt_urb_cue_por_cobrar_producto; ?>" readonly="">
                    </div>
                </div>                               
            </div>
            <?php $red = "$this->link?mod=$this->modulo&tarea=ACCEDER";?>
            <div id="ContenedorDiv">
                <div id="CajaBotones">
                    <center>
                        <input type="button" id="btn_guardar" class="boton" name="" value="Guardar">
                        <input type="reset" class="boton" name="" value="Cancelar">
                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                    </center>
                </div>
            </div>
        </form>

        <script>
            mask_decimal('#costo_terreno', null);
            var projects = JSON.parse('<?php echo FUNCIONES::cuentas_json(); ?>');
            function autosuggest(inputHidden, inputVisible, tipo) {
                autocomplete_ui(
                        {
                            input: '#' + inputVisible,
                            bus_info: true,
                            lista: projects,
                            select: function (obj) {
                                $("#" + inputVisible).val(obj.info);
                                $("#" + inputVisible).attr("data-cod", obj.info);
                                $("#txt_" + inputVisible).val(obj.value);
                                $("#txt_" + inputVisible).removeClass('txt_rojo');
                                $(".msCorrecto").hide();
        //                            agregar_cuenta(obj,input);
                                return false;
                            }

                        }
                );
            }
            autosuggest("", "urb_costo");
            autosuggest("", "urb_costo_diferido");
            autosuggest("", "urb_inv_terrenos");
            autosuggest("", "urb_inv_terrenos_adj");
            autosuggest("", "urb_ing_diferido");
            autosuggest("", "urb_reserva");

            autosuggest("", "urb_ing_reserva");
            autosuggest("", "urb_ing_cambio_tit");
            autosuggest("", "urb_ing_cambio_ubi");
            autosuggest("", "urb_ing_fusion");

            autosuggest("", "urb_cta_por_cobrar_usd");
            autosuggest("", "urb_ing_por_venta_usd");
            autosuggest("", "urb_ing_por_interes_usd");
            autosuggest("", "urb_ing_por_form_usd");
            autosuggest("", "urb_ing_por_mora_usd");

            autosuggest("", "urb_cta_por_cobrar_bs");
            autosuggest("", "urb_ing_por_venta_bs");
            autosuggest("", "urb_ing_por_interes_bs");
            autosuggest("", "urb_ing_por_form_bs");
            autosuggest("", "urb_ing_por_mora_bs");
            
            //CUENTAS CASA
            autosuggest("", "urb_cue_costo_producto");
            autosuggest("", "urb_cue_costo_diferido_producto");
            autosuggest("", "urb_cue_inv_producto");
            autosuggest("", "urb_cue_reserva_producto");
            autosuggest("", "urb_cue_por_cobrar_producto");
            autosuggest("", "urb_cue_ing_producto");
            autosuggest("", "urb_cue_ing_diferido_producto");
            autosuggest("", "urb_cue_inv_producto_adj");
            
            autosuggest("", "urb_cue_descuento_capital_usd");
            autosuggest("", "urb_cue_descuento_capital_bs");
            autosuggest("", "urb_cue_incremento_capital_usd");
            autosuggest("", "urb_cue_incremento_capital_bs");
            $('#frm_sentencia').submit(function () {
                return false;
            });
            $('#btn_guardar').click(function () {
                document.frm_sentencia.submit();
            });

            $('.Subtitulo').click(function(){
                var target = $(this).attr('target-id');
                $('.' + target).slideToggle();
            });
        </script>
        <?php
    }

    function parametros_mlm() {
        $sql = "select * from urbanizacion
				where urb_id = '" . $_GET['id'] . "'";
        $urb = FUNCIONES::objeto_bd_sql($sql);
        $this->formulario->dibujar_titulo("PARAMETRO MLM \"$urb->urb_nombre\"");
        if ($_POST[form] == 'ok') {
            $this->modificar_parametros_mlm($urb);
        } else {
            $this->frm_parametros_mlm($urb);
        }
    }

    function modificar_parametros_mlm($urb){
        $conec=new ADO();
        $uv_tipo=$_POST[uv_tipo_sel];
        if($uv_tipo=='uno'){
            $filtro.=" and uv_id='$_POST[uv]'";
        }else if($uv_tipo=='varios'){
            $filtro.=" and uv_id in ($_POST[uv_ids])";
        }
        
        $man_tipo=$_POST[man_tipo_sel];
        if($man_tipo=='uno'){
            $filtro.=" and man_id='$_POST[manzano]'";
        }else if($man_tipo=='varios'){
            $filtro.=" and man_id in ($_POST[man_ids])";
        }
        
        $zon_tipo=$_POST[zon_tipo_sel];
        if($zon_tipo=='uno'){
            $filtro.=" and zon_id='$_POST[zona]'";
        }else if($zon_tipo=='varios'){
            $filtro.=" and zon_id in ($_POST[zon_ids])";
        }
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
        $urb_id=$urb->urb_id;
        $sql_sel="select * from lote
                    inner join manzano on (lot_man_id=man_id)
                    inner join uv on (lot_uv_id=uv_id)
                    inner join zona on (lot_zon_id=zon_id)
                    where man_urb_id='$urb_id' $filtro
                ";
//        echo "$sql_sel;<br>";

        $list_lotes=  FUNCIONES::lista_bd_sql($sql_sel);
        $sql_ins="insert into lote_multinivel(
                        lm_lot_id,lm_anticipo_min,lm_anticipo_tipo,lm_comision_base,
                        lm_comision_contado,lm_bra,lm_historial
                    )values";
        $ins_value="";
        if($_POST[is_anticipo]){
            $anticipo_mlm=$_POST[anticipo_mlm];
            $anticipo_tipo=$_POST[anticipo_tipo];
        }else{
            $anticipo_mlm='NULL';
            $anticipo_tipo='NULL';
        }   
        $ins_value.=",'$anticipo_mlm','$anticipo_tipo'";
        if($_POST[is_comision]){
            $comision_base=$_POST[comision_base];
        }else{
            $comision_base='NULL';
        }
        $ins_value.=",'$comision_base'";
        if($_POST[is_comision_cont]){
            $comision_contado=$_POST[comision_contado];
        }else{
            $comision_contado='NULL';
        }
        $ins_value.=",'$comision_contado'";
        if($_POST[is_bra]){
            $bra=$_POST[bra];
        }else{
            $bra='NULL';
        }
        $ins_value.=",'$bra'";
        $usu_id=$_SESSION[id];
        $fecha_cre=date('Y-m-d H:i:s');
        $ins_value.=",'Creacion;URB;$usu_id;$fecha_cre;a:$anticipo_mlm;at:$anticipo_tipo;cb:$comision_base;cc:$comision_contado;bra:$bra|'";
        
        
        $cont_up=0;
        $up_value="";
        if($_POST[is_anticipo]){
            $anticipo_mlm=$_POST[anticipo_mlm];
            $anticipo_tipo=$_POST[anticipo_tipo];
            if($cont_up>0){
                $up_value.=",";
            }
            $up_value.=" lm_anticipo_min='$anticipo_mlm', lm_anticipo_tipo='$anticipo_tipo'";
            $cont_up++;
        }else{
            $anticipo_mlm='--';
            $anticipo_tipo='--';
        }   
        
        if($_POST[is_comision]){
            $comision_base=$_POST[comision_base];
            if($cont_up>0){
                $up_value.=",";
            }
            $up_value.=" lm_comision_base='$comision_base'";
            $cont_up++;
        }else{
            $comision_base='--';
        }
        
        if($_POST[is_comision_cont]){
            $comision_contado=$_POST[comision_contado];
            if($cont_up>0){
                $up_value.=",";
            }
            $up_value.=" lm_comision_contado='$comision_contado'";
            $cont_up++;
        }else{
            $comision_contado='--';
        }
        
        if($_POST[is_bra]){
            $bra=$_POST[bra];
            if($cont_up>0){
                $up_value.=",";
            }
            $up_value.=" lm_bra='$bra'";
            $cont_up++;
        }else{
            $bra='--';
        }
        if($cont_up>0){
            $up_value.=",";
        }
        $up_value.=" lm_historial=concat(lm_historial,'Modificacion;URB;$usu_id;$fecha_cre;a:$anticipo_mlm;at:$anticipo_tipo;cb:$comision_base;cc:$comision_contado;bra:$bra|')";
        $sql_up="update lote_multinivel set $up_value ";
        
        $parametros="a:$anticipo_mlm;at:$anticipo_tipo;cb:$comision_base;cc:$comision_contado;bra:$bra";
        
        foreach ($list_lotes as $objeto) {
            $lot_id=$objeto->lot_id;
            $lote_mlm=  FUNCIONES::objeto_bd_sql("select * from lote_multinivel where lm_lot_id='$lot_id'");
            if($lote_mlm){//actualizar
                $sql_update="$sql_up where lm_lot_id='$lot_id'";
//                echo "$sql_update;<br>";
                $conec->ejecutar($sql_update);
            }else{//insertar
                $sql_insertar="$sql_ins('$lot_id'{$ins_value})";
//                echo "$sql_insertar;<br>";
                $conec->ejecutar($sql_insertar);
            }
        }
        $sql_sel=  str_replace("'", "\'", $sql_sel);
        $sql_ins_log="insert into lote_multinivel_log (lmlog_urb_id,lmlog_usu_cre,lmlog_fecha_cre,lmlog_consulta,lmlog_parametros)values('$urb_id','$usu_id','$fecha_cre','$sql_sel','$parametros')";
        
        $conec->ejecutar($sql_ins_log);
        
        ?>
    <style>
        .box_estado_lote{color: #fff; padding: 2px 5px;}
    </style>
    <center>
        <h3>LOTES</h3>
        <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Uv</th>
                    <th>Manzano</th>
                    <th>Lote</th>
                    <th>Zona</th>
                    <th>Superficie</th>
                    <th>Estado</th>
                </tr>							
            </thead>
            <tbody>
                <?php $color=array('Disponible'=>'#008902','Reservado'=>'#0006D1','Vendido'=>'#EF0000','Bloqueado'=>'#727272'); ?>
                <?php $i=1; ?>
                <?php foreach ($list_lotes as $fila) { ?>
                    <tr >
                        <td><?php echo $i;?></td>
                        <td><?php echo $fila->uv_nombre;?></td>
                        <td><?php echo $fila->man_nro;?></td>
                        <td><?php echo $fila->lot_nro;?></td>
                        <td><?php echo $fila->zon_nombre;?></td>
                        <td><?php echo $fila->lot_superficie;?></td>
                        <td><span class="box_estado_lote" style="background-color: <?php echo $color[$fila->lot_estado]?>;"><?php echo $fila->lot_estado;?></span></td>
                        <?php $i++;?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <br><br>
    <?php
    }
    
    function frm_parametros_mlm($urb) {
        ?>
        <script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css" media="screen" charset="utf-8" />
        <script src="js/chosen.jquery.min.js"></script>
        <link href="css/chosen.min.css" rel="stylesheet"/>
        <style>
            .sel_text{width: 20px;height: 20px; border: 1px solid #dadada;float: left;margin-left: 2px; cursor: pointer; background-color: #ff0000}
            .sel_text_active{background-color: #059101}
        </style>
        <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=uv&tarea=PARAMETROS MLM&id=<?PHP echo $_GET[id]; ?>" name="frm_sentencia">
            <input type="hidden" name="form" value="ok">
            <input type="hidden" name="urb_id" id="urb_id" value="<?php echo $urb->urb_id;?>">
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
            <div class="Subtitulo">PARAMETROS MLM</div>
            <div id="ContenedorSeleccion" style="width: 100%;padding: 10px 0;">
                <style>
                    .Etiqueta{width: 170px;}
                    .cgreen{color: #059101}
                    .corange{color: #e44b00}
                </style>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Uv</div>
                    <div id="CajaInput">
                        <select id="uv_tipo_sel" name="uv_tipo_sel" style="min-width: 80px;">
                            <option value="">--Todos--</option>
                            <option value="uno">Solo uno</option>
                            <option value="varios">Varios</option>
                        </select>
                    </div>
                    <div id="CajaInput" class="box_uv box_uv_uno" >
                        <select id="uv" name="uv" style="min-width: 80px;" >
                            <option value=""></option>
                            <?php $uvs = FUNCIONES::lista_bd_sql("select * from uv where uv_urb_id='$urb->urb_id' order by uv_nombre asc"); ?>
                            <?php foreach ($uvs as $_uv) { ?>
                                <option value="<?php echo $_uv->uv_id; ?>"><?php echo $_uv->uv_nombre; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div id="CajaInput" class="box_uv box_uv_varios" >
                        <a href="javascript:void(0);" class="detalle-grupos" data-tipo="uv" style="float: left;"><img src="images/mapplus.png"></a>
                        <input type="hidden" id="uv_ids" name="uv_ids">
                        <div class="read-input" id="txt_uv_nombres">&nbsp;</div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Manzano</div>
                    <div id="CajaInput">
                        <select id="man_tipo_sel" name="man_tipo_sel" style="min-width: 80px;">
                            <option value="">--Todos--</option>
                            <option value="uno">Solo uno</option>
                            <option value="varios">Varios</option>
                        </select>
                    </div>
                    <div id="CajaInput" class="box_manzano box_manzano_uno" >
                        <select id="manzano" name="manzano" style="min-width: 80px;" >
                            <option value=""></option>
                            <?php $manzanos = FUNCIONES::lista_bd_sql("select * from manzano where man_urb_id='$urb->urb_id' order by man_nro*1 asc"); ?>
                            <?php foreach ($manzanos as $man) { ?>
                                <option value="<?php echo $man->man_id; ?>"><?php echo $man->man_nro; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div id="CajaInput" class="box_manzano box_manzano_varios" >
                        <a href="javascript:void(0);" class="detalle-grupos" data-tipo="manzano" style="float: left;"><img src="images/mapplus.png"></a>
                        <input type="hidden" id="man_ids" name="man_ids">
                        <div class="read-input" id="txt_man_nros">&nbsp;</div>
                    </div>

                </div>
                
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Zona</div>
                    <div id="CajaInput">
                        <select id="zon_tipo_sel" name="zon_tipo_sel" style="min-width: 80px;">
                            <option value="">--Todos--</option>
                            <option value="uno">Solo uno</option>
                            <option value="varios">Varios</option>
                        </select>
                    </div>
                    <div id="CajaInput" class="box_zona box_zona_uno" >
                        <select id="zona" name="zona" style="min-width: 80px;" >
                            <option value=""></option>
                            <?php $zonas = FUNCIONES::lista_bd_sql("select * from zona where zon_urb_id='$urb->urb_id' order by zon_nombre asc"); ?>
                            <?php foreach ($zonas as $_zon) { ?>
                                <option value="<?php echo $_zon->zon_id; ?>"><?php echo $_zon->zon_nombre; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div id="CajaInput" class="box_zona box_zona_varios" >
                        <a href="javascript:void(0);" class="detalle-grupos" data-tipo="zona" style="float: left;"><img src="images/mapplus.png"></a>
                        <input type="hidden" id="zon_ids" name="zon_ids">
                        <div class="read-input" id="txt_zon_nombres">&nbsp;</div>
                    </div>

                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Anticipo Minimo</div>
                    <div id="CajaInput">
                        <input type="hidden" id="is_anticipo" name="is_anticipo" value="0">
                        <i class="sel_text sel_text_active" data-tipo="anticipo">&nbsp;</i>
                    </div>
                    <div id="CajaInput" style="margin-left: 5px;" class="campo_anticipo">
                        <input type="text" name="anticipo_mlm" id="anticipo_mlm" value="" size="7">
                    </div>
                    <div id="CajaInput" style="margin-left: 5px;" class="campo_anticipo">
                        <select name="anticipo_tipo" id="anticipo_tipo" style="min-width: 90px">
                            <option value="fijo">Fijo</option>
                            <option value="porc">Porcentarje</option>
                        </select>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Comision Base</div>
                    <div id="CajaInput">
                        <input type="hidden" id="is_comision" name="is_comision" value="0">
                        <i class="sel_text sel_text_active" data-tipo="comision">&nbsp;</i>
                    </div>
                    <div id="CajaInput" style="margin-left: 5px;" class="campo_comision">
                        <input type="text" name="comision_base" id="comision_base" value="" size="7"> $us
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Comision Contado</div>
                    <div id="CajaInput">
                        <input type="hidden" id="is_comision_cont" name="is_comision_cont" value="0">
                        <i class="sel_text sel_text_active" data-tipo="comision_cont">&nbsp;</i>
                    </div>
                    <div id="CajaInput" style="margin-left: 5px;" class="campo_comision_cont">
                        <input type="text" name="comision_contado" id="comision_contado" value="" size="7"> %
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">B.R.A.</div>
                    <div id="CajaInput">
                        <input type="hidden" id="is_bra" name="is_bra" value="0">
                        <i class="sel_text sel_text_active" data-tipo="bra">&nbsp;</i>
                    </div>
                    <div id="CajaInput" style="margin-left: 5px;" class="campo_bra">
                        <input type="text" name="bra" id="bra" value="" size="7"> $us
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <input type="button" class="boton" id="btn_vista_previa" name="" value="Vista Previa">
                </div>
            </div>
            <div id="ContenedorSeleccion" style="width: 100%;padding: 10px 0;">
                <div id="ContenedorDiv">
                    <input type="submit" class="boton" name="" value="Guardar">
                    
                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo "gestor.php?mod=uv"; ?>';">
                </div>
            </div>
            
            <div id="ContenedorSeleccion" style="width: 100%;padding: 10px 0;">
                <center>
                    <h2>Historial de Parametrizaciones</h2>
                    <table width="96%"   class="tablaReporte" id="tab_plan_efectivo" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Fecha de Parametrizacion</th>
                                <th>Usuario</th>                                                                                            
                                <th>Anticipo Minimo</th>
                                <th>Comision Base</th>
                                <th>Comision Contado</th>
                                <th>B.R.A.</th>                                
                            </tr>							
                        </thead>
                        <tbody>
                            <?php
                            $registros = FUNCIONES::lista_bd_sql("select * from lote_multinivel_log 
                                where lmlog_urb_id='$_GET[id]' order by lmlog_fecha_cre desc");
                            
                            foreach ($registros as $reg) {
                                $fecha_hora = explode(' ', $reg->lmlog_fecha_cre);
                                $parametros = explode(';', $reg->lmlog_parametros);
                                $am = explode(':',$parametros[0]);
                                $am = $am[1];
                                $tam = explode(':',$parametros[1]);
                                $tam = ($tam[1] == 'porc') ? '%': '$us.';
                                $cm = explode(':',$parametros[2]);
                                $cm = $cm[1];
                                
                                $cc = explode(':',$parametros[3]);
                                $cc = $cc[1];
                                
                                $bra = explode(':',$parametros[4]);
                                $bra = $bra[1];
                                ?>
                            <tr>
                                <td><?php echo FUNCIONES::get_fecha_latina($fecha_hora[0]) . " " . $fecha_hora[1];?></td>
                                <td><?php echo $reg->lmlog_usu_cre;?></td>
                                <td><?php echo $am . " " . $tam;?></td>
                                <td><?php echo $cm . ' $us.';?></td>
                                <td><?php echo $cc . ' %';?></td>
                                <td><?php echo $bra . ' $us.';?></td>
                            </tr>    
                                <?php
                            }
                            ?>
                        </tbody>
                        
                    </table>
                </center>
            </div>
            
        </form>
        <script>
            mask_decimal('#anticipo_mlm, #comision_base, #comision_contado, #bra',null);
            $('#manzano, #uv, #zona').chosen({
                allow_single_deselect: true
            });
            $('#man_tipo_sel').change(function(){
                var tipo_sel=$('#man_tipo_sel option:selected').val();
                $('.box_manzano').hide();
                $('.box_manzano_'+tipo_sel).show();
            });
            $('#uv_tipo_sel').change(function(){
                var tipo_sel=$('#uv_tipo_sel option:selected').val();
                $('.box_uv').hide();
                $('.box_uv_'+tipo_sel).show();
            });
            $('#zon_tipo_sel').change(function(){
                var tipo_sel=$('#zon_tipo_sel option:selected').val();
                $('.box_zona').hide();
                $('.box_zona_'+tipo_sel).show();
            });
            $('#man_tipo_sel').trigger('change');
            $('#uv_tipo_sel').trigger('change');
            $('#zon_tipo_sel').trigger('change');

            $('.sel_text').click(function(){
                var tipo=$(this).attr('data-tipo');
                if($(this).hasClass('sel_text_active')){
                    $(this).removeClass('sel_text_active');
                    $('#is_'+tipo).val('0');
                    $('.campo_'+tipo).hide();
                }else{
                    $(this).addClass('sel_text_active');
                    $('#is_'+tipo).val('1');
                    $('.campo_'+tipo).show();
                    $('.campo_'+tipo+' input').eq(0).focus();
                }
            });
            
            $('.sel_text').trigger('click');
            
            $('#btn_vista_previa').click(function(){
                var bool=validar_form();
                if(!bool){
                    return;
                }
                var man_tipo=$('#man_tipo_sel option:selected').val();
                if(man_tipo==='uno'){
                    var man_id=$('#manzano').val();
                }else if(man_tipo==='varios'){
                    var man_ids=$('#man_ids').val();
                }
                //validar Uv
                var uv_tipo=$('#uv_tipo_sel option:selected').val();
                if(uv_tipo==='uno'){
                    var uv_id=$('#uv').val();
                }else if(uv_tipo==='varios'){
                    var uv_ids=$('#uv_ids').val();
                }
                //validar Uv
                var zon_tipo=$('#zon_tipo_sel option:selected').val();
                if(zon_tipo==='uno'){
                    var zon_id=$('#zona').val();
                }else if(zon_tipo==='varios'){
                    var zon_ids=$('#zon_ids').val();
                }
                var params={};
                params.tarea='vista_lotes';
                params.urb_id=$('#urb_id').val();
                params.man_tipo=man_tipo;
                params.man_id=man_id;
                params.man_ids=man_ids;
                params.uv_tipo=uv_tipo;
                params.uv_id=uv_id;
                params.uv_ids=uv_ids;
                params.zon_tipo=zon_tipo;
                params.zon_id=zon_id;
                params.zon_ids=zon_ids;
                mostrar_ajax_load();
                $.post('ajax.php',params,function(resp){
                    abrir_popup(resp,false);
                    ocultar_ajax_load();
                });
            });
            
            $('#frm_sentencia').submit(function(){
                var bool=validar_form();
                
                if(bool){
                    return true;
                }else{
                    return false;
                }
                
            });
            
            function validar_form(){
                //validar Uv
                var uv_tipo=$('#uv_tipo_sel option:selected').val();
                if(uv_tipo==='uno'){
                    var uv_id=$('#uv').val();
                    if(uv_id===''){
                        $.prompt('Seleccione una UV');
                        return false;
                    }
                }else if(uv_tipo==='varios'){
                    var uv_ids=$('#uv_ids').val();
                    if(uv_ids===''){
                        $.prompt('Seleccione uno o varios UVs');
                        return false;
                    }
                }
                
                //validar manzano
                var man_tipo=$('#man_tipo_sel option:selected').val();
                if(man_tipo==='uno'){
                    var man_id=$('#manzano').val();
                    if(man_id===''){
                        $.prompt('Seleccione un Manzano');
                        return false;
                    }
                }else if(man_tipo==='varios'){
                    var man_ids=$('#man_ids').val();
                    if(man_ids===''){
                        $.prompt('Seleccione uno o varios Manzanos');
                        return false;
                    }
                }
                
                //validar Uv
                var zon_tipo=$('#zon_tipo_sel option:selected').val();
                if(zon_tipo==='uno'){
                    var zon_id=$('#zona').val();
                    if(zon_id===''){
                        $.prompt('Seleccione una Zona');
                        return false;
                    }
                }else if(zon_tipo==='varios'){
                    var zon_ids=$('#zon_ids').val();
                    if(zon_ids===''){
                        $.prompt('Seleccione uno o varios Zonas');
                        return false;
                    }
                }
                var sum_campos=0;
                if($('#is_anticipo').val()==='1'){
                    var antipo_mlm=$('#anticipo_mlm').val();
                    if(antipo_mlm===''){
                        $.prompt('Ingrese el anticipo minimo');
                        return false;
                    }else{
                        sum_campos++;
                    }
                }
                if($('#is_comision').val()==='1'){
                    var comision_base=$('#comision_base').val();
                    if(comision_base===''){
                        $.prompt('Ingrese la comision base al Credito');
                        return false;
                    }else{
                        sum_campos++;
                    }
                }
                if($('#is_comision_cont').val()==='1'){
                    var comision_contado=$('#comision_contado').val();
                    if(comision_contado===''){
                        $.prompt('Ingrese la comision base al Contado');
                        return false;
                    }else{
                        sum_campos++;
                    }
                }
                if($('#is_bra').val()==='1'){
                    var bra=$('#bra').val();
                    if(bra===''){
                        $.prompt('Ingrese el B.R.A.');
                        return false;
                    }else{
                        sum_campos++;
                    }
                }
                if(sum_campos===0){
                    $.prompt('Seleccione al menos un campo (Anticipo Minimo, Comision Base, Comision Contado o B.R.A.) para asignar a los lotes');
                    return false;
                }
                return true;
            }

            var popup = null;
            function abrir_popup(html,show_aceptar) {
                if (popup !== null) {
                    popup.close();
                }
                popup = window.open('about:blank', 'reportes', 'left=100,width=950,height=600,top=0,scrollbars=yes');
                var extra = '';
                extra += '<html ><head><title>Vista Previa</title>';
                extra += '</head>';
                extra += '<link href=css/estilos.css rel=stylesheet type=text/css />';
                extra += '</head> <body> <div id=imprimir> <div id=status> <p>';
                if(show_aceptar===undefined || show_aceptar){
                    extra += '<a href="javascript:void(0);" id="btn_aceptar">Aceptar</a>';
                }
                
                extra += '<a href=javascript:self.close();>Cerrar</a>';
                
                extra += '</td> </p> </div> </div> <center>';
                popup.document.write(extra);
                popup.document.write(html);
                popup.document.write('</center></body></html>');
                popup.document.close();
            }
            $('.detalle-grupos').mousedown(function (){
                var tipo=$(this).attr('data-tipo');
                cargar_frm_detalle(tipo,'',0);
            });
            function cargar_frm_detalle(tipo){
                
                var html=$('#box-det-'+tipo).html();
                abrir_popup(html);
            }
            
            function cargar_detalle_manzano(data){
                console.log(data);
                $('#man_ids').val(data.man_ids);
                $('#txt_man_nros').text(data.man_nros);
            }
            function cargar_detalle_uv(data){
                console.log(data);
                $('#uv_ids').val(data.uv_ids);
                $('#txt_uv_nombres').text(data.uv_nombres);
            }
            function cargar_detalle_zona(data){
                console.log(data);
                $('#zon_ids').val(data.zon_ids);
                $('#txt_zon_nombres').text(data.zon_nombres);
            }
            $('input[type=text]').attr('autocomplete','off')
        </script>
        <?php $this->popup_manzanos($urb); ?>
        <?php $this->popup_uv($urb); ?>
        <?php $this->popup_zona($urb); ?>
        
        <?php
    }

    function popup_manzanos($urb) {
        ?>
        <div id="box-det-manzano" style="display: none;" >
            <script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
            <script src="js/util.js" type="text/javascript"></script>
            <script type="text/javascript" src="js/jquery-impromptu.2.7.min.js"></script>
            <br>
            <style>
                .tab_selector{
                    border-collapse: collapse; 
                }
                .tab_selector tr th, .tab_selector tr td{
                    border: 1px solid #dadada; padding: 5px; font-size: 12px;
                }
                .tab_selector tr td{
                    border: 1px solid #dadada;
                }
                .fil_active{background-color: #059101;color: #fff; }
                .fil_ele{cursor: pointer}
                .sel_all{width: 20px;height: 20px; border: 1px solid #dadada;float: left;margin-left: 2px; cursor: pointer;}
                .sel_all_active{background-color: #059101}
            </style>
            <table class="tab_selector">
                <thead>
                    <tr>
                        <th><span style="float: left;">MANZANO</span> <i class="sel_all">&nbsp;</i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $manzanos = FUNCIONES::lista_bd_sql("select * from manzano where man_urb_id='$urb->urb_id' order by man_nro*1 asc"); ?>
                    <?php foreach ($manzanos as $man) { ?>
                    <tr class="fil_ele" data-id="<?php echo $man->man_id?>" >
                        <td><?php echo $man->man_nro;?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <br>
            <script>
                $('.fil_ele').mousedown(function(e){
//                    var id=$(this).attr('data-id');
                    if($(this).hasClass('fil_active')){
                        $(this).removeClass('fil_active');
                    }else{
                        $(this).addClass('fil_active');
                    }
                });
                $('.sel_all').mousedown(function(){
                    if($(this).hasClass('sel_all_active')){
                        $(this).removeClass('sel_all_active');
                        $('.fil_ele').removeClass('fil_active');
                    }else{
                        $(this).addClass('sel_all_active');
                        $('.fil_ele').addClass('fil_active');
                    }
                });
                $("#btn_aceptar").click(function(){
                    
                    var str_man_ids='';
                    var str_man_nros='';
                    var selects=$('.fil_active');
                    var num = selects.size();
                    for(var i=0;i<num;i++){
                        var ele=$(selects[i]);
                        if(i>0){
                            str_man_ids+=',';
                            str_man_nros+=',';
                        }
                        str_man_ids+=ele.attr('data-id');
                        str_man_nros+=trim(ele.text());
                        
                    }
                    var data={};
                    data.man_ids=str_man_ids;
                    data.man_nros=str_man_nros;
                    window.opener.cargar_detalle_manzano(data);
                    self.close();
                });
            </script>
        </div>
        <?php
    }
    
    function popup_uv($urb) {
        ?>
        <div id="box-det-uv" style="display: none;" >
            <script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
            <script src="js/util.js" type="text/javascript"></script>
            <script type="text/javascript" src="js/jquery-impromptu.2.7.min.js"></script>
            <br>
            <style>
                .tab_selector{
                    border-collapse: collapse; 
                }
                .tab_selector tr th, .tab_selector tr td{
                    border: 1px solid #dadada; padding: 5px; font-size: 12px;
                }
                .tab_selector tr td{
                    border: 1px solid #dadada;
                }
                .fil_active{background-color: #059101;color: #fff; }
                .fil_ele{cursor: pointer}
                .sel_all{width: 20px;height: 20px; border: 1px solid #dadada;float: left;margin-left: 2px; cursor: pointer;}
                .sel_all_active{background-color: #059101}
            </style>
            <table class="tab_selector">
                <thead>
                    <tr>
                        <th><span style="float: left;">MANZANO</span> <i class="sel_all">&nbsp;</i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $uvs = FUNCIONES::lista_bd_sql("select * from uv where uv_urb_id='$urb->urb_id' order by uv_nombre asc"); ?>
                    <?php foreach ($uvs as $uv) { ?>
                    <tr class="fil_ele" data-id="<?php echo $uv->uv_id?>" >
                        <td><?php echo $uv->uv_nombre;?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <br>
            <script>
                $('.fil_ele').mousedown(function(e){
//                    var id=$(this).attr('data-id');
                    if($(this).hasClass('fil_active')){
                        $(this).removeClass('fil_active');
                    }else{
                        $(this).addClass('fil_active');
                    }
                });
                $('.sel_all').mousedown(function(){
                    if($(this).hasClass('sel_all_active')){
                        $(this).removeClass('sel_all_active');
                        $('.fil_ele').removeClass('fil_active');
                    }else{
                        $(this).addClass('sel_all_active');
                        $('.fil_ele').addClass('fil_active');
                    }
                });
                $("#btn_aceptar").click(function(){
                    
                    var str_uv_ids='';
                    var str_uv_nombres='';
                    var selects=$('.fil_active');
                    var num = selects.size();
                    for(var i=0;i<num;i++){
                        var ele=$(selects[i]);
                        if(i>0){
                            str_uv_ids+=',';
                            str_uv_nombres+=',';
                        }
                        str_uv_ids+=ele.attr('data-id');
                        str_uv_nombres+=trim(ele.text());
                        
                    }
                    var data={};
                    data.uv_ids=str_uv_ids;
                    data.uv_nombres=str_uv_nombres;
                    window.opener.cargar_detalle_uv(data);
                    self.close();
                });
            </script>
        </div>
        <?php
    }
    
    function popup_zona($urb) {
        ?>
        <div id="box-det-zona" style="display: none;" >
            <script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
            <script src="js/util.js" type="text/javascript"></script>
            <script type="text/javascript" src="js/jquery-impromptu.2.7.min.js"></script>
            <br>
            <style>
                .tab_selector{
                    border-collapse: collapse; 
                }
                .tab_selector tr th, .tab_selector tr td{
                    border: 1px solid #dadada; padding: 5px; font-size: 12px;
                }
                .tab_selector tr td{
                    border: 1px solid #dadada;
                }
                .fil_active{background-color: #059101;color: #fff; }
                .fil_ele{cursor: pointer}
                .sel_all{width: 20px;height: 20px; border: 1px solid #dadada;float: left;margin-left: 2px; cursor: pointer;}
                .sel_all_active{background-color: #059101}
            </style>
            <table class="tab_selector">
                <thead>
                    <tr>
                        <th><span style="float: left;">MANZANO</span> <i class="sel_all">&nbsp;</i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $zonas = FUNCIONES::lista_bd_sql("select * from zona where zon_urb_id='$urb->urb_id' order by zon_nombre asc"); ?>
                    <?php foreach ($zonas as $zon) { ?>
                    <tr class="fil_ele" data-id="<?php echo $zon->zon_id?>" >
                        <td><?php echo $zon->zon_nombre;?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <br>
            <script>
                $('.fil_ele').mousedown(function(e){
//                    var id=$(this).attr('data-id');
                    if($(this).hasClass('fil_active')){
                        $(this).removeClass('fil_active');
                    }else{
                        $(this).addClass('fil_active');
                    }
                });
                $('.sel_all').mousedown(function(){
                    if($(this).hasClass('sel_all_active')){
                        $(this).removeClass('sel_all_active');
                        $('.fil_ele').removeClass('fil_active');
                    }else{
                        $(this).addClass('sel_all_active');
                        $('.fil_ele').addClass('fil_active');
                    }
                });
                $("#btn_aceptar").click(function(){
                    
                    var str_zon_ids='';
                    var str_zon_nombres='';
                    var selects=$('.fil_active');
                    var num = selects.size();
                    for(var i=0;i<num;i++){
                        var ele=$(selects[i]);
                        if(i>0){
                            str_zon_ids+=',';
                            str_zon_nombres+=',';
                        }
                        str_zon_ids+=ele.attr('data-id');
                        str_zon_nombres+=trim(ele.text());
                        
                    }
                    var data={};
                    data.zon_ids=str_zon_ids;
                    data.zon_nombres=str_zon_nombres;
                    window.opener.cargar_detalle_zona(data);
                    self.close();
                });
            </script>
        </div>
        <?php
    }
    
    
    function print_titulo_tarea($titulo,$simple=false){ ?>
        <style>
            .link_estrucctura{
                color: #B2002C;
                
            }
            .link_estrucctura:hover{
                color: #5c5c5c;
            }                
        </style>
        <?php if(!$simple){?>
            <div style="padding: 5px ;background-color: #d0d0d0; border-top: 1px solid #808080;font-size: 14px; color: #B2002C">
                <b><?php echo $titulo;?></b>
            </div>
        <?php }else{?>
            <div style="padding: 5px ;background-color: #d0d0d0; border-top: 1px solid #808080;font-size: 14px; color: #000">
                <b><?php echo $titulo;?></b>
            </div>
        <?php }?>
        <?php
    }
    
    function link_estructura($tipo=""){
            $titulo="";
        if($tipo=="manzano"){
            $titulo=  $this->nombre_urb($_GET[urb_id]);
        }elseif($tipo=="lote"){
            $titulo.="<a class='link_estrucctura' href='gestor.php?mod=uv&tarea=ESTRUCTURA&sub=manzano&acc=listar&urb_id=$_GET[urb_id]'>";
            $titulo.=  $this->nombre_urb($_GET[urb_id]);
            $titulo.="</a>";
            $titulo.=" > ";                
            $titulo.='MANZANO: '.$this->nombre_manzano($_GET[man_id]);
        }
        return $titulo;
    }
    
    function nombre_urb($id){
        $sql="select urb_nombre as campo from urbanizacion where urb_id='".$id."'";            
        return FUNCIONES::atributo_bd_sql($sql);
    }
    function nombre_manzano($id){
        $sql="select man_nro as campo from manzano where man_id='".$id."'";
        return FUNCIONES::atributo_bd_sql($sql);
    }
    
    function estructura(){
        $this->formulario->dibujar_titulo('ESTRUCTURA URBANIZACION');
        if($_GET[sub]==''){$_GET[sub]='manzano';$_GET[urb_id]=$_GET[id];}
        if($_GET[acc]=='')$_GET[acc]='listar';
        $this->print_titulo_tarea($this->link_estructura($_GET[sub]));
        $this->print_titulo_tarea(strtoupper($_GET[acc].' '.$_GET[sub]),true);                

        if($_GET[sub]=='manzano'){
            if($_GET[acc]=='agregar'){
                if($_POST[val]=='ok'){
                    $this->insertar_manzano();
                }else{
                    $this->form_manzano('agregar');
                }
            }elseif($_GET[acc]=='modificar'){
                if($_POST[val]=='ok'){
                    $this->modificar_manzano();
                }else{
                    $this->cargar_manzano();
                    $this->form_manzano('modificar');
                }
            }elseif($_GET[acc]=='eliminar'){
                $this->eliminar_manzano();
            }else{
                $this->listado_manzanos($_GET[urb_id]);
            }
        }elseif($_GET[sub]=='lote'){
            if($_GET[acc]=='agregar'){
                if($_POST[val]=='ok'){
                    $this->insertar_lote();
                }else{
                    $this->form_lote('agregar');
                }
            }elseif($_GET[acc]=='modificar'){
                if($_POST[val]=='ok'){
                    $this->modificar_lote();
                    
                }else{
                    $this->cargar_lote();
                    $this->form_lote('modificar');
                }
            }elseif($_GET[acc]=='eliminar'){
                $this->eliminar_lote();
                
            }else{
                $this->listado_lotes($_GET[man_id]);
            }
        }
    }
    
    function cargar_lote(){
        $sql="select * from lote where lot_id=$_GET[lot_id]";
        $urb_id=$_GET[urb_id];
        $man_id=$_GET[man_id];
        
        $lote=  FUNCIONES::objeto_bd_sql($sql);
        $_POST[urb_id]=$urb_id;
        $_POST[man_id]=$man_id;
        $_POST[lot_id]=$lote->lot_id;
        $_POST[lot_nro]=$lote->lot_nro;
        $_POST[lot_superficie]=$lote->lot_superficie;
//        $_POST[lot_valor_co]=$lote->lot_valor_co*1;
        $_POST[lot_uv_id]=$lote->lot_uv_id;
        $_POST[lot_zon_id]=$lote->lot_zon_id;
    }
    function eliminar_lote(){
        $man_id=  trim($_GET[man_id]);
        $lot_id=  trim($_GET[lot_id]);
        $urb_id=  trim($_GET[urb_id]);
        $conec=new ADO();
//        $nlotes=FUNCIONES::atributo_bd_sql("select count(*)as campo from lote where lot_man_id='$man_id'");
//        if($nlotes==0){
        $lote =  FUNCIONES::objeto_bd_sql("select * from lote where lot_id='$lot_id'");
//        echo "$lote->lot_estado";
        if($lote->lot_estado=='Disponible'){
            $sql="delete from lote where lot_id='$lot_id' -- lot_id=$lot_id,lot_superficie=$lote->lot_superficie,lot_uv_id=$lote->lot_uv_id,lot_zon_id=$lote->lot_zon_id,lot_man_id=$lote->lot_man_id,lot_valor_co=$lote->lot_valor_co";
            $conec->ejecutar($sql);
            $msj="Lote eliminado Exitosamente";
            $mtipo='Correcto';
        }else{
            $msj="No se puede eliminar el Lote por que no se encuentra disponible";
            $mtipo='Error';
        }

        $url=  "$this->link?mod=$this->modulo&tarea=ESTRUCTURA&sub=lote&urb_id=$urb_id&man_id=$man_id";
        $this->mostrar_mensaje($msj,"$url", 'Volver',$mtipo);
    }
    function modificar_lote(){
        $man_id=  trim($_POST[man_id]);
        $urb_id=  trim($_POST[urb_id]);
        $lot_id=  trim($_POST[lot_id]);
        $lot_nro=$_POST[lot_nro];
        $lot_superficie=$_POST[lot_superficie];
//        $lot_valor_co=$_POST[lot_valor_co];
        $lot_uv_id=$_POST[lot_uv_id];
        $lot_zon_id=$_POST[lot_zon_id];
        
        
        $conec=new ADO();
        $sql="update lote set 
            lot_nro='$lot_nro',
            lot_superficie='$lot_superficie',
            
            lot_uv_id='$lot_uv_id',
            lot_zon_id='$lot_zon_id'
            where
            lot_id='$lot_id'";
        $conec->ejecutar($sql);
        
        $anticipo_mlm=$_POST[anticipo_mlm];
        $anticipo_tipo=$_POST[anticipo_tipo];
        $comision_base=$_POST[comision_base];
        $comision_contado=$_POST[comision_contado];
        $bra=$_POST[bra];
        $usu_id=$_SESSION[id];
        $fecha_cre=date('Y-m-d H:i:s');
        
//        lm_lot_id,lm_anticipo_min,lm_anticipo_tipo,lm_comision_base,
//                        lm_comision_contado,lm_bra,lm_historial
        
//        "lm_historial=concat(lm_historial,"
        $lote_mlm=  FUNCIONES::objeto_bd_sql("select * from lote_multinivel where lm_lot_id='$lot_id'");
        if($lote_mlm){
            $historial="Modificacion;Lote;$usu_id;$fecha_cre;a:$anticipo_mlm;at:$anticipo_tipo;cb:$comision_base;cc:$comision_contado;bra:$bra|";
            $sql_up="update lote_multinivel set lm_anticipo_min='$anticipo_mlm', lm_anticipo_tipo='$anticipo_tipo', lm_comision_base='$comision_base', 
                 lm_comision_contado='$comision_contado', lm_bra='$bra', lm_historial=concat(lm_historial,'$historial') where lm_lot_id='$lot_id' ";
            $conec->ejecutar($sql_up);
        }else{
            $historial="Creacion;Lote;$usu_id;$fecha_cre;a:$anticipo_mlm;at:$anticipo_tipo;cb:$comision_base;cc:$comision_contado;bra:$bra|";
            $sql_ins="insert into lote_multinivel(
                            lm_lot_id,lm_anticipo_min,lm_anticipo_tipo,lm_comision_base,
                            lm_comision_contado,lm_bra,lm_historial
                        )values(
                            '$lot_id','$anticipo_mlm','$anticipo_tipo','$comision_base',
                            '$comision_contado','$bra','$historial'
                        )";
            $conec->ejecutar($sql_ins);
        }
        
        
        
        $url=  "$this->link?mod=$this->modulo&tarea=ESTRUCTURA&sub=lote&urb_id=$urb_id&man_id=$man_id";
        $this->mostrar_mensaje("Lote Modificiado Exitosamente","$url", 'Volver','Correcto');
    }
    function insertar_lote(){
        $man_id=  trim($_POST[man_id]);
        $urb_id=  trim($_POST[urb_id]);
        $lot_nro=$_POST[lot_nro];
        $lot_superficie=$_POST[lot_superficie];
//        $lot_valor_co=$_POST[lot_valor_co];
        $lot_uv_id=$_POST[lot_uv_id];
        $lot_zon_id=$_POST[lot_zon_id];
        $lot_estado='Disponible';
        $conec=new ADO();
        $sql="insert into lote (lot_man_id, lot_nro, lot_superficie,  lot_uv_id, lot_zon_id, lot_estado) values
            ('$man_id','$lot_nro','$lot_superficie','$lot_uv_id','$lot_zon_id','$lot_estado')";
//            echo $sql;
        $conec->ejecutar($sql,true,true);
        $lote_id=ADO::$insert_id;
        
        $anticipo_mlm=$_POST[anticipo_mlm];
        $anticipo_tipo=$_POST[anticipo_tipo];
        $comision_base=$_POST[comision_base];
        $comision_contado=$_POST[comision_contado];
        $bra=$_POST[bra];
        $usu_id=$_SESSION[id];
        $fecha_cre=date('Y-m-d H:i:s');
        $historial="Creacion;Lote;$usu_id;$fecha_cre;a:$anticipo_mlm;at:$anticipo_tipo;cb:$comision_base;cc:$comision_contado;bra:$bra|";
        $sql_ins="insert into lote_multinivel(
                        lm_lot_id,lm_anticipo_min,lm_anticipo_tipo,lm_comision_base,
                        lm_comision_contado,lm_bra,lm_historial
                    )values(
                        '$lote_id','$anticipo_mlm','$anticipo_tipo','$comision_base',
                        '$comision_contado','$bra','$historial'
                    )";
        $conec->ejecutar($sql_ins);
        $url=  "$this->link?mod=$this->modulo&tarea=ESTRUCTURA&sub=lote&urb_id=$urb_id&man_id=$man_id";
        $this->mostrar_mensaje("Lote ingresado Exitosamente","$url", 'Volver','Correcto');
        
    }
    function form_lote($tipo){
        $urb_id=$_GET[urb_id];
        $man_id=$_GET[man_id];
        
        $obj_lote = FUNCIONES::objeto_bd_sql("select * from lote where lot_id={$_POST[lot_id]}");
        
        $s_vis = ($obj_lote->lot_estado == 'Disponible')?"visible":"hidden";
        ?>
        <script src="js/util.js"></script>
        <form name="formulario" id="formulario" enctype="multipart/form-data" method="POST" 
              action="gestor.php?mod=uv&tarea=ESTRUCTURA&sub=lote&acc=<?php echo $tipo?>&urb_id=<?php echo $urb_id;?>&man_id=<?php echo $man_id;?>">
            <input type="hidden" name="urb_id" value="<?php echo $urb_id;?>">
            <input type="hidden" name="man_id" value="<?php echo $man_id;?>">
            <input type="hidden" name="lot_id" value="<?php echo $_POST[lot_id];?>">
            <input type="hidden" name="val" value="ok">
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
            <div id="ContenedorSeleccion">
                <div style="visibility: <?php echo $s_vis;?>">
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Nro</div>
                    <div id="CajaInput">
                        <input type="text" id="lot_nro" name="lot_nro" size="25" value="<?php echo $_POST[lot_nro];?>" class="caja_texto" autocomplete="off">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Superficie</div>
                    <div id="CajaInput">
                        <input type="text" id="lot_superficie" name="lot_superficie" size="25" value="<?php echo $_POST[lot_superficie];?>" class="caja_texto" autocomplete="off">
                    </div>
                </div>
<!--                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Valor Construccion</div>
                    <div id="CajaInput">
                        <input type="text" id="lot_valor_co" name="lot_valor_co" size="25" value="<?php // echo $_POST[lot_valor_co];?>" class="caja_texto" autocomplete="off">
                    </div>
                </div>-->
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Zona</div>
                    <div id="CajaInput">
                        <select id="lot_zon_id" name="lot_zon_id">
                            <option value="0" selected="" data_zon_precio="0">-- Seleccione --</option>
                            <?
                            $fun=new FUNCIONES();
                            $fun->combo_data("select zon_id as id, zon_nombre as nombre,zon_precio from zona where zon_urb_id='$urb_id'",'zon_precio', $_POST[lot_zon_id]);
                            ?>
                        </select>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Valor Total</div>
                    <div id="CajaInput">
                        <div class="read-input" id="txt_valor_total">0</div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>UV</div>
                    <div id="CajaInput">
                        <select id="lot_uv_id" name="lot_uv_id">
                            <option value="">-- Seleccione --</option>
                            <?
                            
                            $fun->combo("select uv_id as id, uv_nombre as nombre from uv where uv_urb_id='$urb_id'", $_POST[lot_uv_id]);
                            ?>
                        </select>
                    </div>
                </div>
            </div>
                <div style="text-align: left"><b >PARAMETROS MLM</b></div>
                <hr>
                <br>
                <?php $lote_mul=  FUNCIONES::objeto_bd_sql("select * from lote_multinivel where lm_lot_id='$_POST[lot_id]'"); ?>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Anticipo Minimo</div>
                    <div id="CajaInput" style="margin-left: 5px;" class="campo_anticipo">
                        <input type="text" name="anticipo_mlm" id="anticipo_mlm" value="<?php echo $lote_mul->lm_anticipo_min*1; ?>" size="7" autocomplete="off">
                    </div>
                    <div id="CajaInput" style="margin-left: 5px;" class="campo_anticipo">
                        <select name="anticipo_tipo" id="anticipo_tipo" style="min-width: 90px">
                            <option value="fijo" <?php echo $lote_mul->lm_anticipo_tipo=='fijo'?'selected="true"':''; ?>>Fijo</option>
                            <option value="porc" <?php echo $lote_mul->lm_anticipo_tipo=='porc'?'selected="true"':''; ?>>Porcentarje</option>
                        </select>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Comision Base</div>
                    <div id="CajaInput" style="margin-left: 5px;" class="campo_comision">
                        <input type="text" name="comision_base" id="comision_base" value="<?php echo $lote_mul->lm_comision_base*1; ?>" size="7" autocomplete="off"> $us
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">Comision Contado</div>
                    <div id="CajaInput" style="margin-left: 5px;" class="campo_comision_cont">
                        <input type="text" name="comision_contado" id="comision_contado" value="<?php echo $lote_mul->lm_comision_contado*1; ?>" size="7" autocomplete="off"> %
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta">B.R.A.</div>
                    <div id="CajaInput" style="margin-left: 5px;" class="campo_bra">
                        <input type="text" name="bra" id="bra" value="<?php echo $lote_mul->lm_bra*1; ?>" size="7" autocomplete="off"> $us
                    </div>
                </div>
            </div>
            <div id="CajaBotones">
                <center>
                    <input class="boton" type="submit" value="Guardar" name="">
                    <?php $volver="gestor.php?mod=uv&tarea=ESTRUCTURA&sub=lote&urb_id=$urb_id&man_id=$man_id"; ?>
                    <input class="boton" type="button" onclick="javascript:location.href='<?php echo $volver;?>';" value="Volver" name="">
                </center>
            </div>
        </form>
        <script>
            $('#lot_nro').focus();
            mask_decimal('#lot_superficie',null);
            mask_decimal('#lot_valor_co',null);
            $('#lot_superficie, #lot_valor_co').keyup(function(){
                calcular_valor();
            });
            function calcular_valor(){
                var sup=$('#lot_superficie').val()*1;
                
                var precio=$('#lot_zon_id option:selected').attr('data_zon_precio')*1;
//                console.log(sup+'*'+precio+'+'+vc);
                var valor=(sup*precio);
                $('#txt_valor_total').text(valor.toFixed(2));
            }
            $('#lot_zon_id').change(function(){
                calcular_valor();
            });
            $('#formulario').submit(function (){
                var lot_nro=$('#lot_nro').val();
                var lot_superficie=$('#lot_superficie').val()*1;
                var lot_uv_id=$('#lot_uv_id option:selected').val()*1;
                var lot_zon_id=$('#lot_zon_id option:selected').val()*1;
                if(lot_nro===''){
                    $.prompt('Ingrese un numero de Lote');
                    return false;
                }
                if(lot_superficie===0){
                    $.prompt('Ingrese un valor a la superficie');
                    return false;
                }
                if(lot_uv_id===0){
                    $.prompt('Seleccion la UV');
                    return false;
                }
                if(lot_zon_id===0){
                    $.prompt('Seleccion la Zona');
                    return false;
                }
                
            });
            calcular_valor();
        </script>
        <?php

    }
    function listado_lotes($man_id){
        $lotes=  FUNCIONES::objetos_bd_sql("select * from lote,uv,zona where lot_uv_id=uv_id and lot_zon_id=zon_id and lot_man_id='$man_id' order by lot_nro*1 asc");
        $urb_id=$_GET[urb_id];
        $man_id=$_GET[man_id];
        ?>
        <div style="text-align: left; margin: 2px;">
            <a class="link_azul" style="float: right;"title="AGREGAR" href="gestor.php?mod=uv&tarea=ESTRUCTURA&sub=lote&acc=agregar&urb_id=<?php echo $_GET[urb_id];?>&man_id=<?php echo $_GET[man_id];?>">
                <img border="0" src="images/boton_agregar.png">
            </a>
        </div>
        <div style="font-weight: bold; font-size: 14px; color: #033D9B; clear: both;">
            Numero de Registro: <?php echo $lotes->get_num_registros();?>
        </div>
        <style>
            .box_estado_lote{color: #fff; padding: 2px 5px;}
        </style>
        <table class="tablaLista" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th style="width: 60px;">Id</th>
                    <th>Nro. Lote</th>
                    <th>Uv</th>
                    <th>Zona</th>
                    <th>Superficie</th>
                    <th>Valor Total</th>
                    <th>Estado</th>
                    <th class="tOpciones" width="10%">Opciones</th>                        
                </tr>
            </thead>
            <tbody>
                <?php
                $color=array('Disponible'=>'#008902','Reservado'=>'#0006D1','Vendido'=>'#EF0000','Bloqueado'=>'#727272');
                ?>
                <?php for ($i = 0; $i < $lotes->get_num_registros(); $i++) {?>
                    <?php $lot=$lotes->get_objeto();?>
                    <tr>
                        <td><?php echo $lot->lot_id;?></td>
                        <td style="text-align: center;"><b><?php echo $lot->lot_nro;?></b></td>
                        
                        <td><?php echo $lot->uv_nombre;?></td>
                        <td><?php echo $lot->zon_nombre;?></td>
                        <td><?php echo $lot->lot_superficie;?></td>
                        <td><?php echo ($lot->lot_superficie*$lot->zon_precio);?></td>
                        <td><span class="box_estado_lote" style="background-color: <?php echo $color[$lot->lot_estado]?>;"><?php echo $lot->lot_estado;?></span></td>
                        <td>
                            <?php 
                               // if($lot->lot_estado=='Disponible' || $lot->lot_estado=='Reservado'){
                                if($lot->lot_estado=='Disponible'){    
                                ?>
                                <a class="linkOpciones" title="MODIFICAR"
                                   href="<?php echo "gestor.php?mod=uv&tarea=ESTRUCTURA&sub=lote&acc=modificar&urb_id=$urb_id&man_id=$man_id&lot_id=$lot->lot_id"?>">
                                    <img width="16" border="0" alt="MODIFICAR" src="images/b_edit.png">
                                </a>
                                <a class="linkOpciones btn_eliminar" data-urb_id="<?php echo $urb_id;?>" data-man_id="<?php echo $man_id;?>" data-lot_id="<?php echo $lot->lot_id;?>" 
                                    title="ELIMINAR" href="javascript:void(0);">
                                    <img width="16" border="0" alt="ELIMINAR" src="images/b_drop.png">
                                </a>
                            <?php }?>
                        </td>
                    </tr>
                    <?php $lotes->siguiente();?>
                <?php }?>
            </tbody>
        </table>
        <script>
            $('.btn_eliminar').click(function(){
                var txt = 'Esta seguro de Eliminar el Manzano?';
                var urb_id=$(this).attr('data-urb_id');
                var man_id=$(this).attr('data-man_id');
                var lot_id=$(this).attr('data-lot_id');
                $.prompt(txt,{
                    buttons:{Eliminar:true, Cancelar:false},
                    callback: function(v,m,f){
                        if(v){
                            location.href='gestor.php?mod=uv&tarea=ESTRUCTURA&sub=lote&acc=eliminar&urb_id='+urb_id+'&man_id='+man_id+'&lot_id='+lot_id;
                        }
                    }
                });
            });
        </script>
        <?php
    }

    function cargar_manzano(){
        $sql="select * from manzano where man_id=$_GET[man_id]";
        $manzano=  FUNCIONES::objeto_bd_sql($sql);
        $_POST[man_id]=$manzano->man_id;
        $_POST[man_nro]=$manzano->man_nro;
        $_POST[man_matricula]=$manzano->man_matricula;
        $_POST[man_superficie]=$manzano->man_superficie;
        $_POST[man_lote_ini]=$manzano->man_lote_ini;
        $_POST[man_lote_fin]=$manzano->man_lote_fin;
    }
    function eliminar_manzano(){
        $man_id=  trim($_GET[man_id]);
        $urb_id=  trim($_GET[urb_id]);
        $conec=new ADO();
        $nlotes=FUNCIONES::atributo_bd_sql("select count(*)as campo from lote where lot_man_id='$man_id'");
        if($nlotes==0){
            $sql="delete from manzano where man_id='$man_id'";
            $conec->ejecutar($sql);
            $msj="Manzano eliminado Exitosamente";
            $mtipo='Correcto';
        }else{
            $msj="No se puede eliminar el Manzano por que tiene varios lotes registrados";
            $mtipo='Error';
        }
        $url=  "$this->link?mod=$this->modulo&tarea=ESTRUCTURA&sub=manzano&urb_id=$urb_id";
        $this->mostrar_mensaje($msj,"$url", 'Volver',$mtipo);
    }
    function modificar_manzano(){
        $id=  trim($_POST[man_id]);
        $nro=  trim($_POST[man_nro]);
        $urb_id=  trim($_POST[urb_id]);
        $man_matricula=  trim($_POST[man_matricula]);
        $man_superficie=  trim($_POST[man_superficie]);
        $man_lote_ini=  trim($_POST[man_lote_ini]);
        $man_lote_fin=  trim($_POST[man_lote_fin]);
        $conec=new ADO();
        $sql="update manzano set 
            man_nro='$nro',
            man_matricula='$man_matricula',
            man_superficie='$man_superficie',
            man_lote_ini='$man_lote_ini',
            man_lote_fin='$man_lote_fin'
            where
            man_id='$id'";
        $conec->ejecutar($sql);
        $url=  "$this->link?mod=$this->modulo&tarea=ESTRUCTURA&sub=manzano&urb_id=$urb_id";
        $this->mostrar_mensaje("Manzano Modificado Exitosamente","$url", 'Volver','Correcto');
    }
    function insertar_manzano(){
        $nro=  trim($_POST[man_nro]);
        $urb_id=  trim($_POST[urb_id]);
        $man_matricula=  trim($_POST[man_matricula]);
        $man_superficie=  trim($_POST[man_superficie]);
        $man_lote_ini=  trim($_POST[man_lote_ini]);
        $man_lote_fin=  trim($_POST[man_lote_fin]);
        
        $conec=new ADO();
        $sql="insert into manzano (
            man_nro, man_urb_id,man_matricula,
            man_superficie,man_lote_ini,man_lote_fin
            ) values
            ('$nro','$urb_id','$man_matricula',
                '$man_superficie','$man_lote_ini','$man_lote_fin'
            )";
//            echo $sql;
        $conec->ejecutar($sql);
        $url=  "$this->link?mod=$this->modulo&tarea=ESTRUCTURA&sub=manzano&urb_id=$urb_id";
        $this->mostrar_mensaje("Manzano ingresado Exitosamente","$url", 'Volver','Correcto');
        
    }
    function form_manzano($tipo){
        if ($tipo == 'modificar') {
            $datos = FUNCIONES::objeto_bd_sql("select man_id,ifnull(min(cast(lot_nro as unsigned int)), 1)as minimo
                ,ifnull(max(cast(lot_nro as unsigned int)),1)as maximo
                ,ifnull(sum(lot_superficie),0)as man_superficie
                from manzano inner join lote on(man_id=lot_man_id)
                where man_id='$_POST[man_id]'");
            
            if ($_POST[man_superficie] == '0') {                
                $_POST[man_superficie] = $datos->man_superficie;
                $cal_superficie = " calculado";
            }
            
            if ($_POST[man_lote_ini] == '') {                
                $_POST[man_lote_ini] = $datos->minimo;
                $cal_lote_ini = " calculado";
            }
            
            if ($_POST[man_lote_fin] == '') {                
                $_POST[man_lote_fin] = $datos->maximo;
                $cal_lote_fin = " calculado";
            }
        }
        ?>
        <style>
            .calculado{
                background-color: yellow;
                color: red;
            }
        </style>
        <script src="js/util.js"></script>
        <form name="formulario" id="formulario" enctype="multipart/form-data" method="POST" 
              action="gestor.php?mod=uv&tarea=ESTRUCTURA&sub=manzano&acc=<?php echo $tipo?>&urb_id=<?php echo $_GET[urb_id];?>">
            <input type="hidden" name="urb_id" value="<?php echo $_GET[urb_id];?>">
            <input type="hidden" name="man_id" value="<?php echo $_POST[man_id];?>">
            <input type="hidden" name="val" value="ok">
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
            <div id="ContenedorSeleccion">                    
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Nro</div>
                    <div id="CajaInput">
                        <input type="text" id="man_nro" name="man_nro" size="25" value="<?php echo $_POST[man_nro];?>" class="caja_texto" autocomplete="off">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Matricula</div>
                    <div id="CajaInput">
                        <input type="text" id="man_matricula" name="man_matricula" size="25" value="<?php echo $_POST[man_matricula];?>" class="caja_texto" autocomplete="off">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Superficie</div>
                    <div id="CajaInput">
                        <input data-valor="<?php echo $_POST[man_superficie];?>" type="text" id="man_superficie" name="man_superficie" size="25" value="<?php echo $_POST[man_superficie];?>" class="datos_manzano caja_texto <?php echo $cal_superficie;?>" autocomplete="off">
                    </div><span id="sp_man_superficie"></span>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Lote Inicial</div>
                    <div id="CajaInput">
                        <input data-valor="<?php echo $_POST[man_lote_ini];?>" type="text" id="man_lote_ini" name="man_lote_ini" size="25" value="<?php echo $_POST[man_lote_ini];?>" class="datos_manzano caja_texto <?php echo $cal_lote_ini;?>" autocomplete="off">
                    </div><span id="sp_man_lote_ini"></span>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta"><span class="flechas1">*</span>Lote Final</div>
                    <div id="CajaInput">
                        <input data-valor="<?php echo $_POST[man_lote_fin];?>" type="text" id="man_lote_fin" name="man_lote_fin" size="25" value="<?php echo $_POST[man_lote_fin];?>" class="datos_manzano caja_texto <?php echo $cal_lote_fin;?>" autocomplete="off">
                    </div><span id="sp_man_lote_fin"></span>
                </div>
            </div>
            <div id="CajaBotones">
                <center>
                    <input class="boton" type="submit" value="Guardar" name="">
                    <?php $volver="gestor.php?mod=uv&tarea=ESTRUCTURA&sub=manzano&urb_id=$_GET[urb_id]"; ?>
                    <input class="boton" type="button" onclick="javascript:location.href='<?php echo $volver;?>';" value="Volver" name="">
                </center>
            </div>
        </form>
        <script>
            $('#man_nro').focus();
            mask_decimal('#man_superficie', null);
            $('#formulario').submit(function (){
               if(!ingreso_campo('#man_nro')){
                    $.prompt('Ingrese un numero de manzano');
                    return false;
                } 
            });
            
            <?php if ($tipo == 'modificar') {?>
            $('.datos_manzano').focusin(function(){
                console.log('removiendo clase calculado');
                $(this).removeClass('calculado');
                $(this).parent().next('span').html('');
            });
            
            $('.datos_manzano').focusout(function(){
                console.log('adicionando clase calculado');
                var valor = $(this).attr('data-valor');
                if (valor === $(this).val()) {
                    $(this).addClass('calculado');
                    $(this).parent().next('span').html('&nbsp;&nbsp;Valor calculado por el sistema').css('color','red');
                }
            });
            
            $('.calculado').each(function(){
                $(this).parent().next('span').html('&nbsp;&nbsp;Valor calculado por el sistema').css('color','red');
            });
            <?php }?>
        </script>
        <?php

    }
    function listado_manzanos($urb_id){
        $manzanos=  FUNCIONES::objetos_bd_sql("select * from manzano where man_urb_id='$urb_id' order by man_nro*1 asc");
        ?>
        
        <div style="text-align: left; margin: 2px;">
            <a class="link_azul" style="float: right;"title="AGREGAR" href="gestor.php?mod=uv&tarea=ESTRUCTURA&sub=manzano&acc=agregar&urb_id=<?php echo $_GET[urb_id];?>">
                <img border="0" src="images/boton_agregar.png">
            </a>
        </div>
        <div style="font-weight: bold; font-size: 14px; color: #033D9B; clear: both;">
            Numero de Registro: <?php echo $manzanos->get_num_registros();?>
        </div>
        <table class="tablaLista" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th style="width: 60px;">Id</th>
                    <th>Nro. Manzano</th>
                    <th style="width: 100px;">Cant. Lotes</th>
                    <th class="tOpciones" width="10%">Opciones</th>                        
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < $manzanos->get_num_registros(); $i++) {?>
                    <?php $man=$manzanos->get_objeto();?>
                    <tr>
                        <td><?php echo $man->man_id;?></td>
                        <td style="text-align: center;"><b><?php echo $man->man_nro;?></b></td>
                        <td><?php echo FUNCIONES::atributo_bd_sql("select count(*)as campo from lote where lot_man_id='$man->man_id'");?></td>
                        <td>
                            <a class="linkOpciones" title="MODIFICAR"
                               href="gestor.php?mod=uv&tarea=ESTRUCTURA&sub=manzano&acc=modificar&urb_id=<?php echo $_GET[urb_id]?>&man_id=<?php echo $man->man_id?>">
                                <img width="16" border="0" alt="MODIFICAR" src="images/b_edit.png">
                            </a>
                            <a class="linkOpciones btn_eliminar" data-urb_id="<?php echo $man->man_urb_id;?>" data-man_id="<?php echo $man->man_id;?>" title="ELIMINAR" href="javascript:void(0);">
                                <img width="16" border="0" alt="ELIMINAR" src="images/b_drop.png">
                            </a>
                            <a class="linkOpciones" title="LOTES" 
                               href="gestor.php?mod=uv&tarea=ESTRUCTURA&sub=lote&acc=listar&urb_id=<?php echo $_GET[urb_id]?>&man_id=<?php echo $man->man_id?>">
                                <img width="16" border="0" alt="LOTES" src="images/lotes.png">
                            </a>
                        </td>
                    </tr>
                    <?php $manzanos->siguiente();?>
                <?php }?>
            </tbody>
        </table>
        <script>
            $('.btn_eliminar').click(function(){
                var txt = 'Esta seguro de Eliminar el Lote?';
                var urb_id=$(this).attr('data-urb_id');
                var man_id=$(this).attr('data-man_id');
                $.prompt(txt,{
                    buttons:{Eliminar:true, Cancelar:false},
                    callback: function(v,m,f){
                        if(v){
                            location.href='gestor.php?mod=uv&tarea=ESTRUCTURA&sub=manzano&acc=eliminar&urb_id='+urb_id+'&man_id='+man_id;
                        }
                    }
                });
            });
        </script>
        <?php
    }
    
    function mostrar_mensaje($msj,$url,$label='Volver',$tipo='Informacion') {
        ?>
        <div class="ms<?php echo $tipo;?> limpiar"><?php echo $msj;?></div>
        <div>&nbsp;</div>
        <input class="botongrande" type="button" style="clear:both;" value="<?php echo $label;?>" onclick="javascript:location.href='<?php echo $url;?>';" >
        <?php
    }

}
?>