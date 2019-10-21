<?php

class VENTA_PRODUCTO extends VENTA {

    function VENTA_PRODUCTO() {
        parent::__construct();
    }

    function producto() {

        if ($_GET['acc'] <> "") {
            if ($_GET['acc'] == 'guardar') {

                $this->guardar_producto($_REQUEST);
            } elseif ($_GET['acc'] == 'eliminar') {

                $this->eliminar_producto($_GET['id']);
            } elseif ($_GET['acc'] == 'frm_pagar') {

                $this->cargar_datos_producto();
                $this->formulario->dibujar_tarea();
                $datos = new stdClass();
                $datos->get = $_GET;
                $datos->post = $_POST;
                $this->formulario_pagar_producto($datos);
            } elseif ($_GET['acc'] == 'pagar') {

                $datos = new stdClass();
                $datos->post = $_POST;
                $datos->get = $_GET;
                $this->formulario->dibujar_tarea();
                $this->guardar_pago_producto($datos);
            } elseif ($_GET[acc] == 'ver_pagos') {

                $datos = new stdClass();
                $datos->post = $_POST;
                $datos->get = $_GET;
                $this->formulario->dibujar_tarea();
                $this->listado_pagos_producto($datos);
            } elseif ($_GET[acc] == 'ver_pago') {

                $datos = new stdClass();
                $datos->post = $_POST;
                $datos->get = $_GET;
                $this->mostrar_recibo($_GET[id]);
            } elseif ($_GET[acc] == 'anular_pago') {

                $datos = new stdClass();
                $datos->post = $_POST;
                $datos->get = $_GET;
                $this->anular_pago_producto($_GET[id]);
            } else {
                $this->formulario_producto('concretar', $_GET[rprod_id]);
            }
        } else {
            $this->formulario->dibujar_tarea();
            $cant_ventas_prods = FUNCIONES::atributo_bd_sql("select count(vprod_id)
                as campo from venta_producto where vprod_ven_id='$_GET[id]'
                and vprod_estado in ('Pendiente','Pagado')") * 1;

            if ($cant_ventas_prods == 0) {
//                $this->formulario_producto('nuevo');
            }
            $this->listado_productos($_REQUEST);
        }
    }

    function guardar_producto($datosForm) {
        $datosForm = (object) $datosForm;
//        echo "<pre>";
//        print_r($datosForm);
//        echo "</pre>";

        $producto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto where uprod_id=$datosForm->prod_id");
        $costo = $producto->uprod_costo;
        $costo_cub = $datosForm->anticipo;
        $costo_pag = 0;
        if ($costo_cub > $costo) {
            $costo_cub = $costo;
        }

        $user = $this->usu->get_id();
        $fecha = FUNCIONES::get_fecha_mysql($datosForm->vprod_fecha);
        $hoy = date("Y-m-d H:i:s");
        $sql_insert = "
                insert into venta_producto(
                    vprod_precio,vprod_descuento,
                    vprod_monto,vprod_ven_id,
                    vprod_moneda,vprod_prod_id,
                    vprod_usu_cre,vprod_fecha_cre,vprod_fecha,
                    vprod_vdo_id,vprod_tipo,
                    vprod_anticipo,vprod_monto_efectivo,
                    vprod_costo,vprod_costo_cub,vprod_rprod_id
                )
                values(
                    '$datosForm->vprod_precio','$datosForm->vprod_descuento',
                    '$datosForm->vprod_monto','$datosForm->vprod_ven_id',
                    '$datosForm->vprod_moneda','$datosForm->prod_id',
                    '$user','$hoy','$fecha',
                    '$datosForm->vprod_vdo_id','$datosForm->vprod_tipo',
                    '$datosForm->anticipo','$datosForm->vprod_monto_efectivo',
                    '$costo','$costo_cub','$datosForm->vprod_rprod_id'
                )";
        $conec = new ADO();
//        FUNCIONES::bd_query($sql_insert, TRUE, TRUE);
        $conec->ejecutar($sql_insert, TRUE, TRUE);
        $vprod_id = ADO::$insert_id;
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$datosForm->vprod_ven_id'");
        $descripcion = "Venta de Producto $producto->uprod_nombre - Venta $datosForm->vprod_ven_id";

        $monto = $datosForm->vprod_precio - $datosForm->vprod_descuento;

        if ($datosForm->vprod_rprod_id > 0) {
            $sql_upd = "update reserva_producto set rprod_estado='Concretado' 
            where rprod_id='$datosForm->vprod_rprod_id'";
            FUNCIONES::bd_query($sql_upd);
        }
        if ($datosForm->vprod_tipo == 'Credito') {
            $params = array(
                'rango' => $datosForm->ven_rango,
                'frecuencia' => $datosForm->ven_frecuencia,
                'fecha_inicio' => FUNCIONES::get_fecha_mysql($datosForm->vprod_fecha),
                'fecha_pri_cuota' => FUNCIONES::get_fecha_mysql($datosForm->fecha_pri_cuota),
                'nro_cuota_inicio' => 1,
                'tipo' => ($datosForm->def_plan_efectivo == 'mp') ? 'plazo' : 'cuota',
                'plazo' => $datosForm->meses_plazo,
                'interes_anual' => $datosForm->interes_anual,
                'saldo' => $datosForm->vprod_monto_efectivo,
                'cuota' => $datosForm->cuota_mensual
            );

            $lista_cuotas = FUNCIONES::plan_de_pagos($params);

//        echo "<pre>";
//        print_r($lista_cuotas);
//        echo "</pre>";

            foreach ($lista_cuotas as $fila) {
                $sql_cuo = "insert into interno_deuda_producto(
                idpr_tabla,idpr_tabla_id,idpr_num_correlativo,idpr_int_id,
                idpr_fecha,idpr_moneda,idpr_interes,idpr_capital,
                idpr_monto,idpr_saldo,idpr_fecha_programada,idpr_estado,
                idpr_dias_interes,idpr_concepto,
                idpr_usu_id,idpr_tipo,idpr_fecha_cre,idpr_form,idpr_venta_id
            )values(
                'venta_producto','$vprod_id','$fila->nro_cuota','$venta->ven_int_id',
                '$hoy','$datosForm->vprod_moneda','$fila->interes','$fila->capital',
                '$fila->monto','$fila->saldo','$fila->fecha','Pendiente',
                '$fila->dias','Cuota Nro $fila->nro_cuota - $descripcion',
                '$user','pcuota','$hoy','0','$datosForm->vprod_ven_id'
            )";

                FUNCIONES::bd_query($sql_cuo);
            }
        }

        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");
        $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$venta->ven_int_id'");
        $data = array(
            'moneda' => $datosForm->vprod_moneda,
            'ges_id' => $_SESSION[ges_id],
            'fecha' => $fecha,
            'glosa' => $descripcion,
            'interno' => $referido,
            'tabla_id' => $vprod_id,
            'urb' => $urb,
            'anticipo' => $datosForm->anticipo,
            'saldo_efectivo' => $datosForm->vprod_monto_efectivo,
            'monto_intercambio' => 0,
            'intercambio_ids' => '',
            'intercambio_montos' => '',
//                'monto_pagar' => $monto_pagar,
            'costo' => $costo,
            'costo_cub' => $costo_cub,
//                'monto_producto' => $precio_prod,
//                'monto_venta' => $monto,
//                'prorat_lote' => $prorat_lote,
//                'prorat_producto' => $prorat_prod,
//                'costo_producto' => $costo_producto,
//                'costo_producto_cub' => $costo_prod_cub,
        );

        $comprobante = MODELO_COMPROBANTE::venta_producto($data);
        COMPROBANTES::registrar_comprobante($comprobante);

        $mensaje = 'Producto agregado Correctamente a la venta';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTO&id=$datosForm->vprod_ven_id");
    }

    function modificar_producto($datosForm) {
        $datosForm = (object) $datosForm;
//        echo "<pre>";
//        print_r($datosForm);
//        echo "</pre>";
        $sql_upd = "
            update urbanizacion_producto set 
                uprod_nombre='$datosForm->uprod_nombre',
                uprod_descripcion='$datosForm->uprod_descripcion',
                uprod_precio='$datosForm->uprod_precio',
                uprod_moneda='$datosForm->uprod_moneda'
            where uprod_id='$datosForm->id'";
//        echo "modificando el producto $datosForm->id";
        FUNCIONES::bd_query($sql_upd);
        $mensaje = 'Producto modificado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTO&id=$datosForm->urb_id");
    }

    function eliminar_producto($vprod_id) {

//        $bool = TRUE;
        $producto = FUNCIONES::objeto_bd_sql("select * from venta_producto where vprod_id='$vprod_id'");
        $pagado = 1 * FUNCIONES::atributo_bd_sql("select sum(vp_monto)as campo from venta_pago_producto where vp_vprod_id=$vprod_id and vp_estado='Activo'");
        if ($pagado > 0) {
            $mensaje = "La venta del producto no puede ser Anulada porque existen pagos realizados.";
            $tipo = 'Error';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTO&id=$producto->vprod_ven_id", '', $tipo);
            return;
        }

        $conec = new ADO();
        $sql2 = "update venta_producto set vprod_estado='Anulado' where vprod_id='$vprod_id'";
        $conec->ejecutar($sql2);

        $sql2 = "update interno_deuda_producto set idpr_estado='Anulado' where idpr_tabla='venta_producto' and idpr_tabla_id='$vprod_id'";
        $conec->ejecutar($sql2);

        include_once 'clases/registrar_comprobantes.class.php';
        COMPROBANTES::anular_comprobante('venta_producto', $vprod_id);

        if ($producto->vprod_rprod_id > 0) {
            $sql_upd = "update reserva_producto set rprod_estado='Habilitado' 
            where rprod_id='$producto->vprod_rprod_id'";
            FUNCIONES::bd_query($sql_upd);
        }

        $mensaje = 'Venta del Producto anulado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTO&id=$producto->vprod_ven_id");
    }

    function cargar_datos_producto() {
        $producto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto where uprod_id='$_GET[id]'");
        $_POST[uprod_id] = $producto->uprod_id;
        $_POST[uprod_nombre] = $producto->uprod_nombre;
        $_POST[uprod_descripcion] = $producto->uprod_descripcion;
        $_POST[uprod_urb_id] = $producto->uprod_urb_id;
        $_POST[uprod_precio] = $producto->uprod_precio;
        $_POST[uprod_moneda] = $producto->uprod_moneda;
    }

    function formulario_pagar_producto($datos) {
        $get = $datos->get;
        $url = $this->link . "?mod=" . $this->modulo . "&tarea=" . $get[tarea] . "&acc=pagar&id=$get[id]";
        $venta_producto = FUNCIONES::objeto_bd_sql("
            select vp.*,m.mon_Simbolo from venta_producto vp
            inner join con_moneda m on(vp.vprod_moneda=m.mon_id)
            where vprod_id='$get[id]'
                ");
        $volver = $this->link . "?mod=" . $this->modulo . "&tarea=PRODUCTO&id=$venta_producto->vprod_ven_id";
        ?>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <script>
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

                function f_tipo_pago() {
                    var tipo = document.getElementById('tipo_pago').options[document.getElementById('tipo_pago').selectedIndex].value;
                    var tipo = $('#tipo_pago option:selected').val();
                    if (tipo == '1') {
                        $('#div_datos_operacion').css("display", "none");
                    } else {
                        if (tipo == '2') {
                            $('#lbl_transaccion').html('Nro de Cheque');
                        }
                        if (tipo == '3') {
                            $('#lbl_transaccion').html('Nro de Deposito');
                        }
                        if (tipo == '4') {
                            $('#lbl_transaccion').html('Nro de Transferencia');
                        }
                        $('#div_datos_operacion').css("display", "block");
                    }
                }

                function cambiar_moneda() {
                    var pagara = parseFloat(document.frm_sentencia.respag_monto.value);
                    var moneda_original = document.frm_sentencia.moneda_original.value;
                    var moneda_sel = document.frm_sentencia.moneda.options[document.frm_sentencia.moneda.selectedIndex].value;
                    var valor_convertido = parseFloat(document.frm_sentencia.respag_monto.value);
                    var tc = parseFloat(<?php echo $this->tc; ?>);
                    if (moneda_original != moneda_sel) {
                        if (moneda_sel == '1') {
                            pagara *= tc;
                        } else {
                            pagara /= tc;
                        }
                    }
                    document.frm_sentencia.valor_convertido.value = roundNumber(pagara, 2);
                }

                function enviar_formulario_anticipo() {
                    var monto = $('#vp_monto').val() * 1;
                    var caja = $('#caja option:selected').val();
                    if (monto > 0 && caja != '') {
                        document.frm_sentencia.submit();
                    } else {
                        $.prompt('Ingrese el monto a pagar del producto y seleccione la caja.');
                    }
                }
            </script>

            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">

                <div id="FormSent">



                    <div class="Subtitulo">Pagos Producto</div>

                    <div id="ContenedorSeleccion">
                        <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span><label id="lbl_fecha" name="lbl_fecha">Fecha Pago</label></div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="vp_fecha" id="vp_fecha" size="20" value="<?php echo date('d/m/Y'); ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">

                            <div class="Etiqueta" ><span class="flechas1">* </span>Monto Producto</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" readonly="readonly" name="" id="" size="12" value="<? echo $venta_producto->vprod_monto; ?>" type="text" onKeyPress="return ValidarNumero(event);">
                                <input  class="caja_texto" readonly="readonly" name="vp_moneda" id="vp_moneda" size="2" value="<? echo $venta_producto->vprod_moneda; ?>" type="hidden" >
        <?php
        echo $venta_producto->mon_Simbolo;
        ?>
                            </div>
                        </div>
                        <!--Fin-->
                                <?php
                                $pagado = 1 * FUNCIONES::atributo_bd_sql("select sum(vp_monto)as campo from venta_pago_producto where vp_vprod_id='$get[id]' and vp_estado='Activo'");
                                ?>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Monto Pagado</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" name="vp_pagado" readonly id="vp_pagado" size="12" value="<?php echo $pagado; ?>" type="text" onKeyPress="return ValidarNumero(event);">
                        <?php
                        echo $venta_producto->mon_Simbolo;
                        ?>
                            </div>

                        </div>
                        <!--Fin-->
                                <?php ?>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Saldo</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" name="vp_saldo" readonly id="vp_saldo" size="12" readonly value="<?php echo ($venta_producto->vprod_monto - $pagado); ?>" type="text" onKeyPress="return ValidarNumero(event);">
                        <?php
                        echo $venta_producto->mon_Simbolo;
                        ?>
                            </div>

                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Monto a Pagar:</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" name="vp_monto" id="vp_monto" size="12" value="" type="text" onKeyPress="return ValidarNumero(event);">
        <?php
        echo $venta_producto->mon_Simbolo;
        ?>
                            </div>

                        </div>
                        <!--Fin-->

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><b>Pagos</b></div>                                
        <?php FORMULARIO::frm_pago(array('cmp_fecha' => 'vp_fecha', 'cmp_monto' => 'vp_monto', 'cmp_moneda' => 'vp_moneda')); ?>
                        </div>




                    </div>

                    <div id="ContenedorDiv">

                        <div id="CajaBotones">

                            <center>

                                <input type="button" class="boton" name="" value="Guardar" onclick="enviar_formulario_anticipo();">
                                <input type="reset" class="boton" name="" value="Cancelar">
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $volver; ?>';">                                            

                            </center>

                        </div>

                    </div>

                </div>

        </div>
        <script>
                jQuery(function($) {

                    $("#vp_fecha").mask("99/99/9999");

                });

        </script>
        <?php
    }

    function formulario_producto($modo, $rprod_id = 0) {

        $url = $this->link . '?mod=' . $this->modulo . "&tarea=$_GET[tarea]";
        if ($modo == 'nuevo') {
            $url .= "&acc=guardar&id=$_GET[id]";
            $volver = $this->link . "?mod=venta&tarea=ACCEDER";
        } else if ($modo == 'concretar') {
            $url .= "&acc=guardar&id=$_GET[id]";
            $volver = $this->link . "?mod=venta&tarea=ACCEDER";
            $reserva = FUNCIONES::objeto_bd_sql("select * from reserva_producto where rprod_id='$rprod_id'");

            $anticipos = 1 * FUNCIONES::atributo_bd_sql("
            select sum(respag_monto)as campo from reserva_pago_producto 
            where respag_rprod_id=$rprod_id and respag_estado='Pagado'");
        }

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$_GET[id]'");
        
        $pri_cuo_pend = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_tabla='venta'
            and ind_tabla_id='$_GET[id]' and ind_estado='Pendiente' order by ind_fecha_programada asc limit 0,1");
        
        $fecha_pri_cuota = ($pri_cuo_pend) ? FUNCIONES::get_fecha_latina($pri_cuo_pend->ind_fecha_programada) 
                : FUNCIONES::get_fecha_latina(FUNCIONES::sumar_dias(30, date("Y-m-d")));
        ?>        
        <script>
            function ValidarNumero(e) {
                evt = e ? e : event;
                tcl = (window.Event) ? evt.which : evt.keyCode;
                if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                {
                    return false;
                }
                return true;
            }

            function cargar_datos_producto(origen) {
                console.error('enviando desde concretar reserva => ' + origen);
                var prod = $('#vprod_prod_id option:selected').val();
                console.log(prod);
                var datos = prod.split('|');
                console.log(datos[1]);
                $('#prod_id').val(datos[0]);
                $('#uprod_descripcion').val(datos[2]);
                $('#vprod_precio').val(datos[3]);
                $('#vprod_moneda').val(datos[4]);
                $('.simb_moneda_precio').text(datos[5]);
        //                $('#vprod_descuento').val(0);
                $('#vprod_descuento').trigger('keyup');
            }

            function enviar_formulario_producto() {

                var vprod_prod_id = $('#vprod_prod_id option:selected').val() * 1;

                if (vprod_prod_id === 0) {
                    $.prompt('Debe elegir un producto.');
                    return false;
                }

                var exp_reg = /^([0][1-9]|[12][0-9]|3[01])(\/|-)([0][1-9]|[1][0-2])\2(\d{4})$/;
                var vprod_fecha = $('#vprod_fecha').val();

                if (!exp_reg.test(vprod_fecha)) {
                    $.prompt('Debe indicar una fecha correcta.');
                    return false;
                }

                var precio = $('#vprod_precio').val() * 1;
                var tipo = $('#vprod_tipo option:selected').val();

                if (tipo === '') {
                    $.prompt('Debe especificar el tipo de venta del producto.');
                    return false;
                }

                if (precio === '' || precio === 0) {
                    $.prompt('Debe especificar un precio para el producto.');
                    return false;
                }

                if (tipo == 'Credito') {
                    var interes_anual = $('#interes_anual').val() * 1;
                    var def_plan_efectivo = $('#def_plan_efectivo option:selected').val();

                    if (interes_anual < 0) {
                        $.prompt('La tasa de interes no puede ser menor a 0');
                        return false;
                    }

                    if (def_plan_efectivo == 'mp') {
                        if ($('#meses_plazo').val() * 1 <= 0) {
                            $.prompt('Indique la cantidad de meses plazo mayor a 0(cero)');
                            return false;
                        }
                    } else if (def_plan_efectivo == 'cm') {
                        if ($('#cuota_mensual').val() * 1 <= 0) {
                            $.prompt('Indique la cuota mensual mayor a 0(cero)');
                            return false;
                        }
                    }

                    var fecha_pri_cuota = $('#fecha_pri_cuota').val();

                    if (!exp_reg.test(fecha_pri_cuota)) {
                        $.prompt('Debe indicar una fecha correcta para la primer cuota.');
                        return false;
                    }
                }

                console.log('enviando el formulario...');
                $('#frm_vendedor').submit();
            }

            function calcular_monto() {
                var desc = 1 * $('#vprod_descuento').val();
                var precio = 1 * $('#vprod_precio').val();
                var anticipo = 1 * $('#anticipo').val();
                var monto = precio - desc;
                $('#vprod_monto').val(monto);
                var monto_efectivo = monto - anticipo;
                $('#vprod_monto_efectivo').val(monto_efectivo);
            }
        </script>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_vendedor" name="frm_efectivo" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Agregar Producto(Venta <?php echo $venta->ven_id;?>)</div>
                    <div id="ContenedorSeleccion">
                        <div id="div_hiddens">
                            <input type="hidden" id="vprod_moneda" name="vprod_moneda" value="" />
                            <input type="hidden" id="prod_id" name="prod_id" value="" />
                            <input type="hidden" id="vprod_ven_id" name="vprod_ven_id" value="<?php echo $venta->ven_id; ?>" />
                            <input type="hidden" id="vprod_rprod_id" name="vprod_rprod_id" value="<?php echo $rprod_id; ?>" />
                            <input type="hidden" id="ven_urb_id" name="ven_urb_id" value="<?php echo $venta->ven_urb_id; ?>" />
                        </div>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Producto:</div>
                            <div id="CajaInput">
                                <select id="vprod_prod_id" name="vprod_prod_id" onchange="cargar_datos_producto();">

        <?php
        $fun = new FUNCIONES();
        if ($modo == 'nuevo') {
            echo '<option value="">Seleccione</option>';
            $fun->combo("select concat(p.uprod_id,'|',p.uprod_nombre,'|',p.uprod_descripcion,'|',p.uprod_precio,'|',p.uprod_moneda,'|',m.mon_Simbolo)as id, 
            p.uprod_nombre as nombre from urbanizacion_producto p
            inner join venta v on(p.uprod_urb_id=v.ven_urb_id)
            inner join con_moneda m on (p.uprod_moneda=m.mon_id)
            where v.ven_id = $_GET[id]
            and p.uprod_eliminado = 'No'", "");
        } else {

            $p = FUNCIONES::objeto_bd_sql("select p.uprod_id,p.uprod_nombre,p.uprod_descripcion,p.uprod_precio,p.uprod_moneda,m.mon_Simbolo
            from urbanizacion_producto p
            inner join venta v on(p.uprod_urb_id=v.ven_urb_id)
            inner join con_moneda m on (p.uprod_moneda=m.mon_id)
            where v.ven_id = $_GET[id]
            and p.uprod_id='$reserva->rprod_prod_id'
            and p.uprod_eliminado = 'No'");

            if ($p) {
                $valor = "$p->uprod_id|$p->uprod_nombre|$p->uprod_descripcion|$p->uprod_precio|$p->uprod_moneda|$p->mon_Simbolo";
                $texto = $p->uprod_nombre;
                echo "<option selected value='$valor'>$texto</option>";
            }
        }
        ?>
                                </select>
                            </div>                                    

                        </div> 
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Tipo de Venta:</div>
                            <div id="CajaInput">
                                <select id="vprod_tipo" name="vprod_tipo" onchange="">
                                    <!--<option value="">Seleccione</option>-->
                                    <option value="Credito">Credito</option>
                                    <option value="Contado">Contado</option>
                                </select>
                            </div>                                    

                        </div> 
                        <!--Fin-->                        

                        <!--Inicio-->


                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1"></span>Descripcion:</div>
                            <div id="CajaInput">                                        
                                <textarea name="uprod_descripcion" id="uprod_descripcion"><?php // echo $_POST[uprod_descripcion];  ?></textarea>
                            </div>
                        </div>                                

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Precio:</div>                            

                            <div id="CajaInput">
                                <span class="simb_moneda_precio" style="color: black; margin: 5px" class="flechas1"></span>
                                <input id="vprod_precio" name="vprod_precio" readonly value="<?php // echo $_POST[vprod_precio];  ?>" type="text" size="10" onkeypress="return ValidarNumero(event);" />
                            </div>                                                             

                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Descuento:</div>

                            <div id="CajaInput">
                                <span class="simb_moneda_precio" style="color: black; margin: 5px" class="flechas1"></span>
                                <input id="vprod_descuento" name="vprod_descuento" value="<?php echo $reserva->rprod_descuento; ?>" type="text" size="10" onkeypress="return ValidarNumero(event);" onkeyup="calcular_monto();" />
                            </div>                    

                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Monto:</div>                            

                            <div id="CajaInput">
                                <span class="simb_moneda_precio" style="color: black; margin: 5px" class="flechas1"></span>
                                <input id="vprod_monto" name="vprod_monto" value="<?php // echo $_POST[vprod_monto];  ?>" readonly type="text" size="10" onkeypress="return ValidarNumero(event);" />
                            </div>      
        <?php
        if ($modo == 'concretar') {
            ?>
                                <div class="Etiqueta" ><span class="flechas1">*</span>Reserva:</div>
                                <div id="CajaInput">
                                    <span class="simb_moneda_precio" style="color: black; margin: 5px" class="flechas1"></span>
                                    <input id="anticipo" name="anticipo" value="<?php echo $anticipos; ?>" type="text" size="10" readonly />
                                </div>                    
                                <?php
                            } else {
                                ?>
                                <input id="anticipo" name="anticipo" value="0" type="hidden" size="10"/>
            <?php
        }
        ?>

                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Saldo Efectivo:</div>

                            <div id="CajaInput">
                                <span class="simb_moneda_precio" style="color: black; margin: 5px" class="flechas1"></span>
                                <input id="vprod_monto_efectivo" name="vprod_monto_efectivo" value="<?php // echo $_POST[vprod_monto];  ?>" type="text" size="10" onkeypress="return ValidarNumero(event);" />
                            </div>
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Fecha de Venta:</div>                            

                            <div id="CajaInput">
                                <input class="caja_texto" name="vprod_fecha" id="vprod_fecha" size="12" value="<?php echo date("d/m/Y"); ?>" />
                            </div>                                                             

                        </div>

                        <div id="plan_credito" style="display: none; color:#3a3a3a">
                            <div class="Subtitulo">Datos del Credito</div>
                            <div id="ContenedorSeleccion">
                                <div id="ContenedorDiv">
                                    <div id="CajaInput" style="display: none" name="divComenzarEn" >
                                        <span style="float: left; margin-top: 2px;">Interes Anual: &nbsp;</span>
                                        <input type="hidden" name="interes_anual" id="interes_anual" size="8" value="0" >
                                    </div>

                                    <div id="CajaInput" name="divCuotaInicial" style="display: none;">
                                        <span style="float: left; margin-top: 2px;">&nbsp;&nbsp;&nbsp;&nbsp;Cuota Inicial: &nbsp;</span>
                                        <input type="text" name="cuota_inicial" id="cuota_inicial" size="8" value=""  onKeyPress="return ValidarNumero(event);">
                                    </div>
                                    <!--                            </div>
                                                                <div id="ContenedorDiv">-->
                                    <div id="CajaInput">
                                        <span style="float: left; margin-top: 2px;">&nbsp;&nbsp;&nbsp;&nbsp;Definir Plan de Pagos por: &nbsp;</span>
                                        <select  id="def_plan_efectivo" name="def_plan_efectivo" data-tipo="efectivo">
                                            <option value="mp">Meses Plazo</option>
                                            <option value="cm">Cuota Mensual</option>
                                            <!--<option value="manual">Manual</option>-->
                                        </select>
                                    </div>
                                </div>
                                <div id="ContenedorDiv"  >
                                    <div id="CajaInput">
                                        <span style="float: left; margin-top: 2px; margin-right: 5px;" >Nro de Cuotas: </span>
                                        <input type="text" name="meses_plazo" id="meses_plazo" size="8" value="" onKeyPress="return ValidarNumero(event);" autocomplete="off">
                                    </div>
                                    <!--                                <div id="CajaInput" name="divCuotaMensual" hidden="">
                                                                        <select hidden="" id="def_cuota" style="width: 100px; float: left; margin-top: 3px;">
                                                                            <option value="dcuota">Monto Cuota</option>
                                                                            <option value="dcapital">Monto Capital</option>
                                                                        </select>
                                                                    </div>-->
                                    <div id="CajaInput" name="divCuotaMensual" >
                                        <span style="float: left; margin-top: 2px; margin-right: 5px;">Monto Cuota: </span>
                                        <input type="text" name="cuota_mensual" id="cuota_mensual" size="8" value="" onKeyPress="return ValidarNumero(event);" autocomplete="off">
                                    </div>
                                    <div id="CajaInput">
                                        <span style="float: left; margin-top: 2px; margin-left: 15px; margin-right: 5px;">Fecha Pri. Cuota: </span>
                                        <input class="caja_texto" readonly name="fecha_pri_cuota" id="fecha_pri_cuota" size="12" value="<?php echo $fecha_pri_cuota; ?>" type="text" autocomplete="off">
                                        <script>
            $("#fecha_pri_cuota").mask("99/99/9999");
                                        </script>
                                    </div>

                                </div>
                                <div id="ContenedorDiv">
                                    <div id="CajaInput" name="divCuotaMensual" >
                                        <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Rango: </span>
                                        <select id="ven_rango" name="ven_rango">
                                            <option value="1">Mensual</option>
                                            <option value="2">Bimestral</option>
                                            <option value="3">Trimestral</option>                                                            
                                            <option value="4">Cuatrimestral</option>
                                            <option value="6">Semestral</option>
                                        </select>
                                    </div>
                                    <div id="CajaInput" name="divCuotaMensual" >
                                        <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Frec.: </span>
                                        <select id="ven_frecuencia" name="ven_frecuencia">
                                            <option value="30_dias">Cada 30 dias</option>
                                            <option value="dia_mes">Mantener el dia</option>
                                        </select>
                                    </div>
                                    <div id="CajaInput">
                                        <img id="ver_plan_efectivo" src="imagenes/generar.png" style='margin:0px 0px 0px 5px; cursor: pointer' onclick="javascript:ver_plan_pago();">
                                    </div>
                                    <div id="CajaInput">
                                        <img id="add_cuota_efectivo"src="images/btn_add_detalle.png" style='margin-left: 5px; cursor: pointer' onclick="javascript:datos_fila('efectivo');">
                                    </div>
                                </div>

                                <div style="clear: both"></div>
                                <div class="ContenedorDiv" id="plan_manual_efectivo">
                                    <table width="96%"   class="tablaReporte" id="tab_plan_efectivo" cellpadding="0" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nro. Cuota</th>
                                                <th>Fecha de Pago</th>                                                            
                                                <th>Mondeda</th>
                                                <th>Interes</th>
                                                <th>Capital</th>
                                                <th>Monto a Pagar</th>
                                                <th>Saldo</th>
                                                <th></th>
                                            </tr>							
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>	
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>
                                                    <input type="hidden" id="c_total_efectivo" value="0">
                                                    <input type="hidden" id="pag_total_efectivo" value="0">
                                                </td>
                                                <td>&nbsp;</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario_producto();">
                                <input type="reset" class="boton" name="" value="Cancelar">
                                <!--<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=uv&tarea=ACCEDER';">-->                                            
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $volver; ?>';">                                            
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
        if ($p) {
            ?>
            <script>
            cargar_datos_producto('reservaaaa');
            //                    $('#vprod_prod_id').trigger('change');
            </script>
            <?php
        }
        ?>
        <script>
            jQuery(function($) {

                $("#vprod_fecha").mask("99/99/9999");

            });

        </script>
        <script>
            $('#is_condonacion').change(function() {
                //                        console.info('aa');
                var val = $(this).val();
                if (val === 'no') {
                    $('.box-pagos-mora').show();
                } else if (val === 'si') {
                    $('.box-pagos-mora').hide();
                }
            });

            $('#vprod_tipo').change(function() {
                var tipo = $(this).val();

                if (tipo == 'Contado') {
                    $("#plan_credito").hide();
                } else {
                    $("#plan_credito").show();
                }
            });

            $('#vprod_tipo').trigger('change');

            $('#def_plan_efectivo').change(function() {
                var tipo = 'efectivo';

                var tipo_venta = $('#vprod_tipo option:selected').val();



                var def = $(this).val();
                if (def === 'mp') {
                    $('#meses_plazo').parent().show();
                    $('#cuota_mensual').parent().hide();
                    $('#cuota_interes').parent().hide();
                    $('#ver_plan_efectivo').show();
                    $('#add_cuota_efectivo').hide();
                    $('#fecha_pri_cuota').prev('span').text('Fecha Pri. Cuota: ');
                    $('#plan_manual_efectivo').hide();
                    $('#def_cuota').parent().hide();

                } else if (def === 'cm') {
                    $('#meses_plazo').parent().hide();
                    $('#cuota_mensual').parent().show();
                    $('#cuota_interes').parent().hide();
                    $('#ver_plan_efectivo').show();
                    $('#add_cuota_efectivo').hide();
                    $('#cuota_mensual').prev('span').text('Monto Cuota: ');
                    $('#cuota_mensual').prev('span').show();
                    $('#fecha_pri_cuota').prev('span').text('Fecha Pri. Cuota: ');
                    $('#plan_manual_efectivo').hide();
                    $('#def_cuota').parent().hide();
                } else if (def === 'manual') {
                    $('#meses_plazo').parent().hide();
                    $('#cuota_mensual').parent().show();
                    $('#cuota_interes').parent().show();
                    $('#ver_plan_efectivo').hide();
                    $('#add_cuota_efectivo').show();
                    //                                                            $('#cuota_mensual').prev('span').text('Capital Cuota: ');
                    $('#cuota_mensual').prev('span').hide();
                    $('#fecha_pri_cuota').prev('span').text('Fecha Programada: ');
                    $('#plan_manual_efectivo').show();
                    $('#def_cuota').parent().show();
                }
            });
            $('#def_plan_efectivo').trigger('change');

            function ver_plan_pago() {
                var tipo_venta = $('#vprod_tipo option:selected').val();
                if (tipo_venta === 'Credito') {
                    var saldo_financiar = 0;
                    var ncuotas = 0;
                    var fecha_pri_cuota = 0;
                    var monto_cuota = 0;


                    var def = $('#def_plan_efectivo option:selected').val();
                    if (def === 'mp') {
                        ncuotas = $('#meses_plazo').val();
                        monto_cuota = '';
                    } else if (def === 'cm') {
                        ncuotas = '';
                        monto_cuota = $('#cuota_mensual').val();
                    }
                    var monto_efectivo = $('#vprod_monto_efectivo').val();
                    //                                                            var anticipo=0;
                    //                                                            if($('#ven_anticipo').length){
                    //                                                                anticipo=$('#ven_anticipo').val();
                    //                                                            }                                            
                    var cuota_inicial = $('#cuota_inicial').val();
                    saldo_financiar = monto_efectivo - cuota_inicial;
                    fecha_pri_cuota = $('#fecha_pri_cuota').val();
                    var rango = $('#ven_rango option:selected').val();
                    var frec = $('#ven_frecuencia option:selected').val();
                    //                                                if(_MODALIDAD==='interes'){
                    var interes = $('#interes_anual').val();
                    //                                                }
                    var fecha_pri_mysql = fecha_mysql(fecha_pri_cuota);
                    var fecha_ini_mysql = fecha_mysql($('#vprod_fecha').val());
                    if (fecha_ini_mysql > fecha_pri_mysql) {
                        $.prompt('-La fecha de venta no puede ser mayor a la fecha de primer Pago', {opacity: 0.8});
                        $('#fecha_pri_cuota').attr('readonly',false);
                        $("#fecha_pri_cuota").mask("99/99/9999");
                        return false;
                    }

                    console.log('ncuotas => ' + ncuotas + ' - monto_cuota => ' + monto_cuota + ' - monto_efectivo => ' + monto_efectivo + ' - fecha_pri_cuota => ' + fecha_pri_cuota);
                    if ((ncuotas * 1 > 0 || monto_cuota * 1 > 0) && monto_efectivo > +0 && fecha_pri_cuota !== '') {
                        var moneda = $('#vprod_moneda').val();//document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
                        var par = {};
                        par.tarea = 'plan_pagos';
                        par.saldo_financiar = saldo_financiar;
                        par.monto_total = saldo_financiar;
                        par.meses_plazo = ncuotas;
                        par.ven_moneda = moneda;
                        par.fecha_inicio = $('#vprod_fecha').val();
                        par.fecha_pri_cuota = fecha_pri_cuota;
                        par.cuota_mensual = monto_cuota;
                        par.interes = interes;
                        par.rango = rango;
                        par.frecuencia = frec;
                        par.urbanizacion = $('#ven_urb_id').val();

                        $.post('ajax.php', par, function(resp) {
                            abrir_popup(resp);
                        });

                    } else {
                        $('#tprueba tbody').remove();
                        $.prompt('-La Fecha no debe estar vacia.</br>-Los meses de plazo o la cuota mensual debe ser mayor a cero.', {opacity: 0.8});
                    }
                } else {
                    $.prompt('La venta es al contado, no necesita generar un plan de pagos.', {opacity: 0.8});
                }
            }
            var popup = null;
            function abrir_popup(html) {
                if (popup !== null) {
                    popup.close();
                }
                popup = window.open('about:blank', 'reportes', 'left=100,width=900,height=500,top=0,scrollbars=yes');
                var extra = '';
                extra += '<html><head><title>Vista Previa</title><head>';
                extra += '<link href=css/estilos.css rel=stylesheet type=text/css />';
                extra += '</head> <body> <div id=imprimir> <div id=status> <p>';

                extra += '<a href=javascript:window.print();>Imprimir</a>  <a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>';
                popup.document.write(extra);
                popup.document.write(html);
                popup.document.write('</center></body></html>');
                popup.document.close();

            }
        </script>
        <?php
    }

    function listado_productos($datos) {
        $url = $this->link . '?mod=' . $this->modulo . "&tarea=$_GET[tarea]";
        $datos = (object) $datos;
        ?>
        <script>
            function eliminar_producto(id) {
                var txt = '¿Esta seguro de querer eliminar el producto?';
                $.prompt(txt, {
                    buttons: {Aceptar: true, Cancelar: false},
                    callback: function(v, m, f) {
                        if (v) {
                            location.href = 'gestor.php?mod=venta&tarea=PRODUCTO&acc=eliminar&&id=' + id;
                        }
                    }
                });
            }

        </script>


        <br><br>
        <div id="h_pagos">
            <center>
                <h2>LISTADO DE PRODUCTOS</h2>
                <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th>NOMBRE</th>                            
                            <th>MONEDA</th>                            
                            <th>MONTO</th>
                            <th>PAGADO</th>                                
                            <th>SALDO</th>                                
                            <th class="tOpciones">Opciones</th> 
                        </tr>	
                    </thead>
                    <tbody>
        <?php
        $sql = "select p.uprod_nombre,p.uprod_descripcion,vp.vprod_monto,
        vp.vprod_estado,m.mon_Simbolo,m.mon_titulo,vp.vprod_id,vp.vprod_tipo from 
        venta_producto vp
        inner join urbanizacion_producto p on(vp.vprod_prod_id=p.uprod_id)
        inner join con_moneda m on (vp.vprod_moneda=m.mon_id)                                
        where vp.vprod_ven_id = '$datos->id'

        and vp.vprod_estado in ('Pendiente','Pagado')";
        $conec = new ADO();
        $conec->ejecutar($sql);

        for ($i = 0; $i < $conec->get_num_registros(); $i++) {
            $producto = $conec->get_objeto();
            if ($producto->vprod_tipo == 'Contado') {
                $pagado = 1 * FUNCIONES::atributo_bd_sql("
                                select sum(vp_monto)as campo from venta_pago_producto 
                                where vp_vprod_id=$producto->vprod_id and vp_estado='Activo'");
            } else {
                $pagado = 1 * FUNCIONES::atributo_bd_sql("
                                select sum(vpag_capital_prod)as campo from venta_pago
                                where vpag_vprod_id=$producto->vprod_id and vpag_estado='Activo'");
            }
            ?>
                            <tr>
                                <td><?php echo strtoupper($producto->uprod_nombre); ?><br/>(<?php echo $producto->uprod_descripcion; ?>)</td>                                
                                <td><?php echo $producto->mon_titulo; ?></td>
                                <td>
                            <?php
//                                    echo $producto->mon_Simbolo . ". " . number_format($producto->vprod_monto, 2, '.', ',');
                            echo number_format($producto->vprod_monto, 2, '.', ',');
                            ?>
                                </td>
                                <td><?php echo number_format($pagado, 2, '.', ','); ?></td>
                                <td><?php echo number_format(($producto->vprod_monto - $pagado), 2, '.', ','); ?></td>

                                <td>
                        <center>
                            <table>
                                <tr>
            <?php
            if ($producto->vprod_estado == 'Pendiente') {
                if ($producto->vprod_tipo == 'Contado') {
                    ?>
                                            <td>
                                                <a href="<?php echo $url . "&acc=frm_pagar&id=$producto->vprod_id"; ?>">
                                                    <img src="images/cuenta.png" alt="PAGOS" title="PAGOS" border="0">
                                                </a>
                                            </td>
                                            <?php
                                        }
                                    }
                                    if ($producto->vprod_tipo == 'Contado') {
                                        ?>
                                        <td>
                                            <a href="<?php echo $url . "&acc=ver_pagos&id=$producto->vprod_id"; ?>">
                                                <img src="images/mapplus.png" alt="VER" title="VER PAGOS" border="0">
                                            </a>
                                        </td>
                                        <?php
                                    }
                                    $cuotas_pagadas = FUNCIONES::atributo_bd_sql("select count(idpr_id)as campo 
                from interno_deuda_producto where idpr_estado in ('Pagado','Pendiente')
                and idpr_capital_pagado > 0
                and idpr_tabla='venta_producto' and idpr_tabla_id='$producto->vprod_id'");
                                    if ($producto->vprod_estado == 'Pendiente' && $cuotas_pagadas == 0) {
                                        ?>
                                        <td>
                                            <!--<a href="<?php // echo $url."&acc=eliminar&id=$producto->uprod_id";               ?>">-->
                                            <a href="javascript:eliminar_producto('<?php echo $producto->vprod_id; ?>')">    
                                                <img src="images/anular.png" alt="ELIMINAR" title="ELIMINAR PRODUCTO" border="0">
                                            </a>
                                        </td>
                                        <?php
                                    }
                                    ?>
                                </tr>
                            </table>
                        </center>
                        </td>
                        </tr>
                                    <?php
                                    $conec->siguiente();
                                }
                                ?>
                    </tbody>

                </table>
            </center>
        </div>
                    <?php
                    $cant_ventas_prods = FUNCIONES::atributo_bd_sql("select count(vprod_id)
                as campo from venta_producto where vprod_ven_id='$_GET[id]'
                and vprod_estado in ('Pendiente','Pagado')") * 1;

                    if ($cant_ventas_prods == 1) {
                        ?>
            <div id="ContenedorDiv">
                <div id="CajaBotones">
                    <center>                                        
                        <input type="button" onclick="javascript:location.href = 'gestor.php?mod=venta&tarea=ACCEDER';" value="Volver" name="" class="boton">                                            
                    </center>
                </div>
            </div>
            <?php
        }
    }

    function guardar_pago_producto($datos) {

//        echo "<pre>";
//        print_r($datos);
//        echo "</pre>";
//        exit();

        $get = $datos->get;
        $post = $datos->post;
        $user = $this->usu->get_id();
        $hoy = date("Y-m-d");
        $fecha = FUNCIONES::get_fecha_mysql($post[vp_fecha]);
        $hora = date("h:i:s");
        $nro_recibo = FUNCIONES::nro_recibo($fecha);

        $sql_pago = "
            insert into venta_pago_producto(
                vp_vprod_id,vp_monto,vp_interes,vp_capital,
                vp_moneda,vp_usu_id,
                vp_fecha,vp_fecha_cre,vp_hora,vp_nro_recibo
            )
            values(
                '$get[id]','$post[vp_monto]',,'0','$post[vp_monto]'
                '$post[vp_moneda]','$user',
                '$fecha','$hoy','$hora','$nro_recibo'
            )";
        FUNCIONES::bd_query($sql_pago, FALSE);
        $llave = ADO::$insert_id;

        $venta_producto = FUNCIONES::objeto_bd_sql("select * from venta_producto 
        inner join urbanizacion_producto on (vprod_prod_id=uprod_id)
        where vprod_id=$get[id]");

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where 
        ven_id='$venta_producto->vprod_ven_id'");

        $producto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto 
        where uprod_id=$venta_producto->vprod_prod_id");

        $pagado_producto = FUNCIONES::atributo_bd_sql("
            select sum(vp_monto)as campo from venta_pago_producto
            where vp_vprod_id=$get[id] and vp_estado='Activo'") * 1;

        if ($pagado_producto >= $venta_producto->vprod_monto) {
            FUNCIONES::bd_query("update venta_producto set vprod_estado='Pagado' 
            where vprod_id=$get[id]");
        }

        $this->mostrar_recibo($llave);
        ?>
        <br><br><br>
        <?php
    }

    function mostrar_recibo($vp_id) {

        $pago_producto = FUNCIONES::objeto_bd_sql("select * from venta_pago_producto where vp_id='$vp_id'");

        $venta_producto = FUNCIONES::objeto_bd_sql("select * from venta_producto 
        inner join urbanizacion_producto on (vprod_prod_id=uprod_id)
        where vprod_id=$pago_producto->vp_vprod_id");

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where 
        ven_id='$venta_producto->vprod_ven_id'");

        $producto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto 
        where uprod_id=$venta_producto->vprod_prod_id");

        $pagado_producto = FUNCIONES::atributo_bd_sql("
        select sum(vp_monto)as campo from venta_pago_producto
        where vp_vprod_id='$pago_producto->vp_vprod_id' 
        and vp_estado='Activo'") * 1;

        include_once 'clases/recibo.class.php';

        $det_cabecera = array();
        $det_body = array();

        if ($pago_producto->vp_interes > 0) {
            $det_cabecera[] = 'Interes';
            $det_body[] = $pago_producto->vp_interes;
        }

        if ($pago_producto->vp_capital > 0) {
            $det_cabecera[] = 'Capital';
            $det_body[] = $pago_producto->vp_capital;
        }


        $det_cabecera[] = 'Total';
        $det_body[] = $pago_producto->vp_monto;


        $saldo_producto = $venta_producto->vprod_monto - $pagado_producto;

        $det_cabecera[] = 'Total Aportado';
        $det_body[] = $pagado_producto;

        $det_cabecera[] = 'Saldo Deudor';
        $det_body[] = $saldo_producto;


        $data = array(
            'titulo' => 'PAGO DE PRODUCTO',
            'referido' => FUNCIONES::interno_nombre($venta->ven_int_id),
            'usuario' => FUNCIONES::usuario_nombre($pago_producto->vp_usu_id),
            'monto' => $pago_producto->vp_monto,
            'moneda' => $pago_producto->vp_moneda,
            'nro_recibo' => $pago_producto->vp_nro_recibo,
            'fecha' => $pago_producto->vp_fecha,
            'concepto' => "Pago de Producto {$producto->uprod_nombre} de la Venta Nro. $venta->ven_id - " . $venta->ven_concepto,
            'has_detalle' => '1',
            'det_cabecera' => $det_cabecera,
            'det_body' => $det_body,
            'nota' => "sin nota",
        );
        ?>
        <br><br><br>
        <?php
        RECIBO::pago($data);
    }

    function listado_pagos_producto($datos) {
        $get = $datos->get;
        $url = $this->link . "?mod=" . $this->modulo . "&tarea=$get[tarea]";
//        echo "mostrando pagos de $get[id]...";
        ?>


        <script>
            function anular_pago(id) {
                var txt = 'Esta seguro de anular el pago del Producto?';

                $.prompt(txt, {
                    buttons: {Anular: true, Cancelar: false},
                    callback: function(v, m, f) {

                        if (v) {
                            location.href = 'gestor.php?mod=venta&tarea=PRODUCTO&acc=anular_pago&id=' + id;
                        }

                    }
                });
            }
            function ver_comprobante(id)
            {
                location.href = 'gestor.php?mod=reserva&tarea=ANTICIPOS RESERVA&acc=ver_comprobante&id=' + id;
            }
        </script>
        <div id="contenido_reporte" style="clear: both;">
            <center>
                <table class="tablaLista" cellpadding="0" cellspacing="0" width="60%">
                    <thead>
                        <tr>
                            <th>Nro</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                            <th class="tOpciones" width="100px">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
        <?php
        $sql = "
                        select * from venta_pago_producto 
                        inner join con_moneda on (vp_moneda=mon_id)                        
                        where vp_vprod_id=$get[id] 
                        and vp_estado='Activo'    
                        order by vp_fecha desc,vp_hora desc";
        $pagos = FUNCIONES::objetos_bd_sql($sql);
//                    echo $sql;
        for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
            $k = $i + 1;
            $pago = $pagos->get_objeto();
            ?>
                            <tr>
                                <td style="text-align: center"><?php echo $k; ?></td>
                                <td style="text-align: center"><?php echo FUNCIONES::get_fecha_latina($pago->vp_fecha); ?></td>
                                <td style="text-align: center"><?php echo $pago->vp_hora; ?></td>
                                <td style="text-align: right"><?php echo $pago->vp_monto; ?></td>
                                <td style="text-align: center"><?php echo $pago->mon_titulo; ?></td>
                                <td>
                        <center>
                            <table>
                                <tr>                                    
                                    <td>
                                        <a href="<?php echo $url . "&acc=ver_pago&id=$pago->vp_id"; ?>">
                                            <img src="images/ver.png" alt="VER" title="VER PAGOS" border="0">
                                        </a>
                                    </td>
                                    <td>                                        
                                        <a href="javascript:anular_pago('<?php echo $pago->vp_id; ?>')">    
                                            <img src="images/anular.png" alt="ANULAR" title="ANULAR PAGO" border="0">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </center>
                        </td>
                        </tr>
            <?php
            $pagos->siguiente();
        }
        $venta_producto = FUNCIONES::objeto_bd_sql("select * from venta_producto where vprod_id=$get[id]");
        $volver = $this->link . "?mod=" . $this->modulo . "&tarea=PRODUCTO&id=$venta_producto->vprod_ven_id";
        ?>
                    </tbody>
                </table>
            </center>
        </div>

        <!--<div id="ContenedorSeleccion">-->
        <div id="ContenedorDiv">

            <div id="CajaBotones">
                <center>
                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $volver; ?>';">                                            
                </center>
            </div>

        </div>
        <!--</div>-->    
        <?php
    }

    function anular_pago_producto($vp_id) {

        $bool = TRUE;
        if (!$bool) {
            $mensaje = "El pago de la mora no puede ser Anulada por que el periodo en el que fue realizado el pago fue cerrado.";
            $tipo = 'Error';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
            return;
        }
        $producto = FUNCIONES::objeto_bd_sql("select * from venta_pago_producto where vp_id='$vp_id'");
        $conec = new ADO();
        $sql2 = "update venta_pago_producto set vp_estado='Anulado' where vp_id='$vp_id'";
        $conec->ejecutar($sql2);

        $venta_producto = FUNCIONES::objeto_bd_sql("
                select vp.* from venta_producto vp
                inner join venta_pago_producto vpp on (vp.vprod_id=vpp.vp_vprod_id) 
                where vpp.vp_id=$vp_id");

        if ($venta_producto->vprod_estado == 'Pagado') {
            FUNCIONES::bd_query("update venta_producto set vprod_estado='Pendiente' where vprod_id=$venta_producto->vprod_id");
        }

//        include_once 'clases/registrar_comprobantes.class.php';
//        $comp = new COMPROBANTES();
//        $comp->anular_comprobante_tabla("venta_pago_producto", $vp_id);

        $mensaje = 'Pago del Producto anulado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTO&acc=ver_pagos&id=$producto->vp_vprod_id");
    }

    function reserva_producto() {
        if ($_GET['acc'] <> "") {
            if ($_GET['acc'] == 'guardar') {
                $this->guardar_reserva_producto($_REQUEST);
            } elseif ($_GET['acc'] == 'eliminar') {
                $this->eliminar_reserva_producto($_GET['id']);
            } elseif ($_GET['acc'] == 'frm_pagar') {
                $this->cargar_datos_producto();
                $this->formulario->dibujar_tarea();
                $datos = new stdClass();
                $datos->get = $_GET;
                $datos->post = $_POST;
                $this->formulario_pagar_anticipo_producto($datos);
            } elseif ($_GET['acc'] == 'pagar') {

                $venta = FUNCIONES::objeto_bd_sql("select * from venta 
                inner join reserva_producto on (ven_id=rprod_ven_id)
                inner join urbanizacion_producto on (rprod_prod_id=uprod_id)
                where rprod_id='$_GET[id]'");

                $glosa = 'Reserva de Casa - ' . $venta->uprod_nombre . ' - Venta:' . $venta->ven_id;

                $datos = array(
                    'rprod_id' => $_GET[id],
                    'fecha' => FUNCIONES::get_fecha_mysql($_POST[vp_fecha]),
                    'monto' => $_POST[vp_monto],
                    'moneda' => $venta->rprod_moneda,
                    'venta' => $venta,
                    'glosa' => $glosa,
                );

                $resp = $this->guardar_anticipo_producto($datos);

                $tipo = "";
                if ($resp->ok) {
                    
                } else {
                    $tipo = "Error";
                }
                $link = $this->link . "?mod=" . $this->modulo . "&tarea=" . $_GET[tarea] . "&id=" . $venta->ven_id;
                $this->formulario->ventana_volver($resp->mensaje, $link, "", $tipo);
            } elseif ($_GET[acc] == 'ver_pagos') {
                $datos = new stdClass();
                $datos->post = $_POST;
                $datos->get = $_GET;
                $this->formulario->dibujar_tarea();
                $this->listado_anticipos_producto($datos);
            } elseif ($_GET[acc] == 'ver_pago') {
                $datos = new stdClass();
                $datos->post = $_POST;
                $datos->get = $_GET;
                $this->mostrar_recibo_anticipo($_GET[id]);
            } elseif ($_GET[acc] == 'anular_pago') {
                $datos = new stdClass();
                $datos->post = $_POST;
                $datos->get = $_GET;
                $this->anular_anticipo_producto($_GET[id]);
            } elseif ($_GET[acc] == 'concretar') {
                $this->formulario_producto('concretar', $_GET[rprod_id]);
            }
        } else {
            $this->formulario->dibujar_tarea();
            $cant_reservas_prods = FUNCIONES::atributo_bd_sql("select count(rprod_id)
                as campo from reserva_producto where rprod_ven_id='$_GET[id]'
                and rprod_estado in ('Pendiente','Habilitado','Concretado')") * 1;

            if ($cant_reservas_prods == 0) {
                $this->frm_reserva_producto('nuevo');
            } else {
                $this->listado_reserva_productos($_REQUEST);
            }
        }
    }

    function frm_reserva_producto($modo) {

        $url = $this->link . '?mod=' . $this->modulo . "&tarea=$_GET[tarea]";
        if ($modo == 'nuevo') {
            $url .= "&acc=guardar&id=$_GET[id]";
            $volver = $this->link . "?mod=venta&tarea=ACCEDER";
        } else if ($modo == 'modificar') {
            $url .= "&acc=modificar&id=$_GET[id]&urb_id=$_POST[uprod_urb_id]";
            $volver = $this->link . "?mod=uv&tarea=PRODUCTO&id=$_POST[uprod_urb_id]";
        }

        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$_GET[id]'");
        ?>        
        <script>
            function ValidarNumero(e) {
                evt = e ? e : event;
                tcl = (window.Event) ? evt.which : evt.keyCode;
                if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                {
                    return false;
                }
                return true;
            }

            function enviar_formulario_producto() {

                var rprod_prod_id = $('#rprod_prod_id option:selected').val() * 1;

                if (rprod_prod_id === 0) {
                    $.prompt('Debe elegir un producto.');
                    return false;
                }

                var exp_reg = /^([0][1-9]|[12][0-9]|3[01])(\/|-)([0][1-9]|[1][0-2])\2(\d{4})$/;
                var rprod_fecha = $('#rprod_fecha').val();

                if (!exp_reg.test(rprod_fecha)) {
                    $.prompt('Debe indicar una fecha correcta.');
                    return false;
                }

                var precio = $('#rprod_precio').val() * 1;

        //                var tipo = $('#rprod_tipo option:selected').val();                
        //                if (tipo === '') {
        //                    $.prompt('Debe especificar el tipo de venta del producto.');
        //                    return false;
        //                }

                if (precio === '' || precio === 0) {
                    $.prompt('Debe especificar un precio para el producto.');
                    return false;
                }

//                var anticipo = $('#anticipo').val() * 1;
//                if (anticipo === '' || anticipo === 0) {
//                    $.prompt('Debe especificar un anticipo mayor a 0(cero) para la reserva del producto.');
//                    return false;
//                }

//                var fecha = $('#rprod_fecha').val();
//                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
//                    var dato = JSON.parse(respuesta);
//                    if (dato.response === "ok") {
//                        if (!validar_fpag_montos(dato.cambios)) {
//                            $.prompt('El monto a Pagar no concuerda con los pagos realizados');
//                            return false;
//                        }
//                        console.log('enviando el formulario...');
//                        $('#frm_vendedor').submit();
//                        console.log('ok');
//                    } else if (dato.response === "error") {
//                        $.prompt(dato.mensaje);
//                        return false;
//                    }
//                });

                        console.log('enviando el formulario...');
                        $('#frm_vendedor').submit();
            }

            function calcular_monto() {
                var desc = 1 * $('#rprod_descuento').val();
                var precio = 1 * $('#rprod_precio').val();
                var monto = precio - desc;
                $('#rprod_monto').val(monto);
            }
        </script>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_vendedor" name="frm_efectivo" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent" style="width:80%">

                    <div class="Subtitulo">Reservar Producto</div>
                    <div id="ContenedorSeleccion">
                        <div id="div_hiddens">
                            <input type="hidden" id="rprod_moneda" name="rprod_moneda" value="<?php echo $venta->ven_moneda; ?>" />
                            <input type="hidden" id="prod_id" name="prod_id" value="" />
                            <input type="hidden" id="rprod_ven_id" name="rprod_ven_id" value="<?php echo $_GET[id]; ?>" />
                            <input type="hidden" id="ven_urb_id" name="ven_urb_id" value="<?php echo $venta->ven_urb_id; ?>" />
                        </div>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Producto:</div>
                            <div id="CajaInput">
                                <select id="rprod_prod_id" name="rprod_prod_id" onchange="cargar_datos_producto();">
                                    <option value="">Seleccione</option>
        <?php
        $fun = new FUNCIONES();
        $fun->combo("
                                                select concat(p.uprod_id,'|',p.uprod_nombre,'|',p.uprod_descripcion,'|',p.uprod_precio,'|',p.uprod_moneda,'|',m.mon_Simbolo)as id, 
                                                p.uprod_nombre as nombre from urbanizacion_producto p
                                                inner join venta v on(p.uprod_urb_id=v.ven_urb_id)
                                                inner join con_moneda m on (p.uprod_moneda=m.mon_id)
                                                where v.ven_id = $_GET[id]
                                                and p.uprod_eliminado = 'No'", "");
        ?>
                                </select>
                            </div>                                    

                        </div> 
                        <!--Fin-->

                        <!--Inicio-->
                        <!--<div id="ContenedorDiv">
                            <div class="Etiqueta" >Tipo de Venta:</div>
                            <div id="CajaInput">
                                <select id="rprod_tipo" name="rprod_tipo" onchange="">
                                    <option value="">Seleccione</option>
                                    <option value="Credito">Credito</option>
                                    <option value="Contado">Contado</option>
                                </select>
                            </div>                                    

                        </div>--> 
                        <!--Fin-->
                        <input type="hidden" value="Credito" id="rprod_tipo" name="rprod_tipo" />
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1"></span>Descripcion:</div>
                            <div id="CajaInput">                                        
                                <textarea name="uprod_descripcion" id="uprod_descripcion"><?php echo $_POST[uprod_descripcion]; ?></textarea>
                            </div>
                        </div>                                

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Precio:</div>                            

                            <div id="CajaInput">
                                <span class="simb_moneda_precio" style="color: black; margin: 5px" class="flechas1"></span>
                                <input id="rprod_precio" name="rprod_precio" readonly value="<?php echo $_POST[rprod_precio]; ?>" type="text" size="10" onkeypress="return ValidarNumero(event);" />
                            </div>                                                             

                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Descuento:</div>                            

                            <div id="CajaInput">
                                <span class="simb_moneda_precio" style="color: black; margin: 5px" class="flechas1"></span>
                                <input id="rprod_descuento" name="rprod_descuento" value="0" type="text" size="10" onkeypress="return ValidarNumero(event);" onkeyup="calcular_monto();" />
                            </div>                                                             

                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Monto:</div>                            

                            <div id="CajaInput">
                                <span class="simb_moneda_precio" style="color: black; margin: 5px" class="flechas1"></span>
                                <input id="rprod_monto" name="rprod_monto" value="<?php echo $_POST[rprod_monto]; ?>" type="text" size="10" onkeypress="return ValidarNumero(event);" />
                            </div>                                                             

                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Fecha de Reserva:</div>                            

                            <div id="CajaInput">
                                <input class="caja_texto" name="rprod_fecha" id="rprod_fecha" size="12" value="<?php echo date("d/m/Y"); ?>" />
                            </div>                                                             

                        </div>
                        
                        
                        <div class="Subtitulo div_pagos" style="display: none;">PAGOS</div>
                        <div id="ContenedorSeleccion" class="div_pagos" style="display: none;">

                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Anticipo:</div>
                                <div id="CajaInput">
                                    <span class="simb_moneda_precio" style="color: black; margin: 5px" class="flechas1"></span>
                                    <input id="anticipo" name="anticipo" value="0" type="text" size="10" onkeypress="return ValidarNumero(event);" onkeyup="calcular_monto();" />
                                </div>

                            </div>
                            
                            <div id="ContenedorDiv" class="" style="display: block;">
                                <div class="Etiqueta" ><b>Pagos</b></div>                                
        <?php FORMULARIO::frm_pago(array('cmp_fecha' => 'rprod_fecha', 'cmp_monto' => 'anticipo', 'cmp_moneda' => 'rprod_moneda')); ?>
                            </div>

                        </div>
                        

                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario_producto();">
                                <input type="reset" class="boton" name="" value="Cancelar">
                                <!--<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=uv&tarea=ACCEDER';">-->                                            
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $volver; ?>';">                                            
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script>

            function cargar_datos_producto() {
                var prod = $('#rprod_prod_id option:selected').val();

//                if (prod != '') {
//                    $('.div_pagos').show();
//                } else {
//                    $('.div_pagos').hide();
//                }

                console.log(prod);
                var datos = prod.split('|');
                console.log(datos[1]);
                $('#prod_id').val(datos[0]);
                $('#uprod_descripcion').val(datos[2]);
                $('#rprod_precio').val(datos[3]);
        //                $('#rprod_moneda').val(datos[4]);
                $('.simb_moneda_precio').text(datos[5]);
                $('#rprod_descuento').val(0);
                $('#rprod_descuento').trigger('keyup');
            }

            cargar_datos_producto();

            jQuery(function($) {

                $("#rprod_fecha").mask("99/99/9999");

            });

            $('#anticipo').keyup(function() {
                var anticipo = $('#anticipo').val() * 1;
                var monto = $('#rprod_monto').val() * 1;
                if (anticipo > monto) {
                    $('#anticipo').val(0);
                    $.prompt('El anticipo no puede ser mayor al monto del producto.');
                }
            });

        </script>
        <script>


            var popup = null;
            function abrir_popup(html) {
                if (popup !== null) {
                    popup.close();
                }
                popup = window.open('about:blank', 'reportes', 'left=100,width=900,height=500,top=0,scrollbars=yes');
                var extra = '';
                extra += '<html><head><title>Vista Previa</title><head>';
                extra += '<link href=css/estilos.css rel=stylesheet type=text/css />';
                extra += '</head> <body> <div id=imprimir> <div id=status> <p>';

                extra += '<a href=javascript:window.print();>Imprimir</a>  <a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>';
                popup.document.write(extra);
                popup.document.write(html);
                popup.document.write('</center></body></html>');
                popup.document.close();

            }
        </script>
        <?php
    }

    function guardar_reserva_producto($datosForm) {
        $datosForm = (object) $datosForm;
//        echo "<pre>";
//        print_r($datosForm);
//        echo "</pre>";
//        return false;

        $user = $this->usu->get_id();
        $fecha = FUNCIONES::get_fecha_mysql($datosForm->rprod_fecha);
        $hoy = date("Y-m-d");

        $estado = ($datosForm->anticipo > 0) ? 'Habilitado' : 'Pendiente';

        $sql_insert = "
                insert into reserva_producto(
                    rprod_precio,rprod_descuento,
                    rprod_monto,rprod_ven_id,
                    rprod_moneda,rprod_prod_id,
                    rprod_usu_cre,rprod_fecha_cre,rprod_fecha,
                    rprod_vdo_id,rprod_tipo,rprod_estado
                )
                values(
                    '$datosForm->rprod_precio','$datosForm->rprod_descuento',
                    '$datosForm->rprod_monto','$datosForm->rprod_ven_id',
                    '$datosForm->rprod_moneda','$datosForm->prod_id',
                    '$user','$hoy','$fecha',
                    '$datosForm->rprod_vdo_id','$datosForm->rprod_tipo','$estado'    
                )";
        $conec = new ADO();
//        FUNCIONES::bd_query($sql_insert, TRUE, TRUE);
        $conec->ejecutar($sql_insert, TRUE, TRUE);
        $rprod_id = ADO::$insert_id;
        $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$datosForm->rprod_ven_id'");

        $producto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto where uprod_id=$datosForm->prod_id");

//        $reserva = $datos->rprod_id;
//        $fecha = $datos->fecha;
//        $moneda = $datos->moneda;
//        $monto = $datos->monto;
//        $venta = $datos->venta;
//        $glosa = $datos->glosa;

        $b_anticipo = FALSE;
        $resp = NULL;
        if ($datosForm->anticipo > 0) {
            $datos = array(
                'rprod_id' => $rprod_id,
                'fecha' => $fecha,
                'moneda' => $datosForm->rprod_moneda,
                'monto' => $datosForm->anticipo,
                'venta' => $venta,
                'glosa' => 'Reserva de Casa - ' . $producto->uprod_nombre . ' - Venta:' . $venta->ven_id,
            );

            $resp = $this->guardar_anticipo_producto($datos);
            $b_anticipo = $resp->ok;
        }

        $mensaje = 'Producto Reservado Correctamente a la venta';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=RESERVA PRODUCTO&id=$datosForm->rprod_ven_id");

        if ($b_anticipo) {
            $this->mostrar_recibo_anticipo($resp->datos);
        }
    }

    function guardar_anticipo_producto($datos) {

        $datos = (object) $datos;

        $reserva = $datos->rprod_id;
        $fecha = $datos->fecha;
        $moneda = $datos->moneda;
        $monto = $datos->monto;
        $venta = $datos->venta;
        $glosa = $datos->glosa;
        $nro_recibo = FUNCIONES::nro_recibo($fecha);

        $sql = "insert into reserva_pago_producto(respag_monto,respag_moneda,respag_fecha,respag_hora,
                    respag_estado,respag_usu_id,respag_glosa,respag_rprod_id,respag_recibo,
                    respag_suc_id
                )values (
                    '$monto','$moneda','$fecha','" . date('H:i:s') . "',
                    'Pagado','$_SESSION[id]','$glosa','$reserva','$nro_recibo',
                    '$_SESSION[suc_id]'
                )";
        //echo $sql;
        $conec = new ADO();
        $conec->begin_transaccion();
        $conec->ejecutar($sql, true, true);

        $llave = ADO::$insert_id;

        include_once 'clases/recibo.class.php';
        $data_recibo = array(
            'recibo' => $nro_recibo,
            'fecha' => $fecha,
            'monto' => $monto,
            'moneda' => $moneda,
            'tabla' => 'reserva_pago_producto',
            'tabla_id' => $llave
        );
        RECIBO::insertar($data_recibo);

        $fecha_cmp = $fecha;
        $urb = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

        if (true) {
            include_once 'clases/modelo_comprobantes.class.php';
            include_once 'clases/registrar_comprobantes.class.php';
            $referido = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$venta->ven_int_id'");

            $params = array(
                'tabla' => 'reserva_pago_producto',
                'tabla_id' => $llave,
                'fecha' => $fecha_cmp,
                'moneda' => $moneda,
                'ingreso' => true,
                'une_id' => $urb->urb_une_id,
                'glosa' => $glosa, 'ca' => '0', 'cf' => 0, 'cc' => 0
            );
            $detalles = FORMULARIO::insertar_pagos($params);

            $data = array(
                'moneda' => $moneda,
                'ges_id' => $_SESSION[ges_id],
                'fecha' => $fecha_cmp,
                'glosa' => $glosa,
                'interno' => $referido,
                'tabla_id' => $llave,
                'urb' => $urb,
                'monto' => $monto,
                'detalles' => $detalles,
            );

            $comprobante = MODELO_COMPROBANTE::anticipo_producto($data);

            COMPROBANTES::registrar_comprobante($comprobante);
        }

        $exito = $conec->commit();

        $resp = new stdClass();

        if ($exito) {
            $resp->ok = true;
            $resp->mensaje = "Pago de Anticipo agregado correctamente.";
            $resp->datos = $llave;
        } else {
            $resp->ok = false;
            $mensajes = $conec->get_errores();
//            $mensaje = implode('<br>', $mensajes);
            $resp->mensaje = $mensajes;
        }

        return $resp;
    }

    function listado_reserva_productos($datos) {
        $url = $this->link . '?mod=' . $this->modulo . "&tarea=$_GET[tarea]";
        $datos = (object) $datos;
        ?>
        <script>
            function eliminar_producto(id) {
                var txt = '¿Esta seguro de querer eliminar la reserva del producto?';
                $.prompt(txt, {
                    buttons: {Aceptar: true, Cancelar: false},
                    callback: function(v, m, f) {
                        if (v) {
                            location.href = '<?php echo $url; ?>&acc=eliminar&id=' + id;
                        }
                    }
                });
            }

            function concretar_venta(id) {
                var txt = '¿Esta seguro de querer concretar la reserva del producto?';
                $.prompt(txt, {
                    buttons: {Aceptar: true, Cancelar: false},
                    callback: function(v, m, f) {
                        if (v) {
                            location.href = 'gestor.php?mod=venta&tarea=PRODUCTO&acc=concretar&id=<?php echo $datos->id; ?>&rprod_id=' + id;
                        }
                    }
                });
            }

        </script>


        <br><br>
        <div id="h_pagos">
            <center>
                <h2>LISTADO DE RESERVA DE PRODUCTOS(Venta <?php echo $datos->id;?>)</h2>
                <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th>NOMBRE</th>                            
                            <th>MONEDA</th>                            
                            <th>MONTO</th>
                            <th>PAGADO</th>                                
                            <th>SALDO</th>                                
                            <th class="tOpciones">Opciones</th> 
                        </tr>	
                    </thead>
                    <tbody>
        <?php
        $sql = "select p.uprod_nombre,p.uprod_descripcion,rp.rprod_monto,
        rp.rprod_estado,m.mon_Simbolo,m.mon_titulo,rp.rprod_id,rp.rprod_tipo from 
        reserva_producto rp
        inner join urbanizacion_producto p on(rp.rprod_prod_id=p.uprod_id)
        inner join con_moneda m on (rp.rprod_moneda=m.mon_id)                                
        where rp.rprod_ven_id = '$datos->id'

        and rp.rprod_estado in ('Pendiente','Habilitado','Concretado')";
        $conec = new ADO();
        $conec->ejecutar($sql);

        for ($i = 0; $i < $conec->get_num_registros(); $i++) {
            $producto = $conec->get_objeto();
            $pagado = 1 * FUNCIONES::atributo_bd_sql("
                                select sum(respag_monto)as campo from reserva_pago_producto 
                                where respag_rprod_id=$producto->rprod_id and respag_estado='Pagado'");
            ?>
                            <tr>
                                <td><?php echo strtoupper($producto->uprod_nombre); ?><br/>(<?php echo $producto->uprod_descripcion; ?>)</td>                                
                                <td><?php echo $producto->mon_titulo; ?></td>
                                <td>
                            <?php
                            echo number_format($producto->rprod_monto, 2, '.', ',');
                            ?>
                                </td>
                                <td><?php echo number_format($pagado, 2, '.', ','); ?></td>
                                <td><?php echo number_format(($producto->rprod_monto - $pagado), 2, '.', ','); ?></td>

                                <td>
                        <center>
                            <table>
                                <tr>
            <?php
            if ($producto->rprod_estado == 'Habilitado') {
                ?>
                                        <td>
                                            <a href="<?php echo $url . "&acc=ver_pagos&id=$producto->rprod_id"; ?>">
                                                <img src="images/mapplus.png" alt="VER" title="VER PAGOS" border="0">
                                            </a>
                                        </td>
                                        <td>                        
                                            <a href="javascript:concretar_venta('<?php echo $producto->rprod_id; ?>');">
                                                <img src="images/migrar.png" alt="CONCRETAR" title="CONCRETAR" border="0">
                                            </a>
                                        </td>
                <?php
            }

            if ($producto->rprod_estado == 'Pendiente') {
                ?>
                                        <td>
                                            <a href="<?php echo $url . "&acc=frm_pagar&id=$producto->rprod_id"; ?>">
                                                <img src="images/cuenta.png" alt="PAGOS" title="PAGOS" border="0">
                                            </a>
                                        </td>
                                        <td>                        
                                            <a href="javascript:concretar_venta('<?php echo $producto->rprod_id; ?>');">
                                                <img src="images/migrar.png" alt="CONCRETAR" title="CONCRETAR" border="0">
                                            </a>
                                        </td>
                                        <td>                    
                                            <a href="javascript:eliminar_producto('<?php echo $producto->rprod_id; ?>')">    
                                                <img src="images/anular.png" alt="ELIMINAR" title="ELIMINAR PRODUCTO" border="0">
                                            </a>
                                        </td>
                <?php
            }
            ?>
                                </tr>
                            </table>
                        </center>
                        </td>
                        </tr>
            <?php
            $conec->siguiente();
        }
        ?>
                    </tbody>

                </table>
            </center>
        </div>
        <?php
        $cant_ventas_prods = FUNCIONES::atributo_bd_sql("select count(rprod_id)
                as campo from reserva_producto where rprod_ven_id='$_GET[id]'
                and rprod_estado in ('Pendiente','Habilitado','Concretado')") * 1;

        if ($cant_ventas_prods == 1) {
            ?>
            <div id="ContenedorDiv">
                <div id="CajaBotones">
                    <center>                                        
                        <input type="button" onclick="javascript:location.href = 'gestor.php?mod=venta&tarea=ACCEDER';" value="Volver" name="" class="boton">                                            
                    </center>
                </div>
            </div>
            <?php
        }
    }

    function formulario_pagar_anticipo_producto($datos) {
        $get = $datos->get;
        $url = $this->link . "?mod=" . $this->modulo . "&tarea=" . $get[tarea] . "&acc=pagar&id=$get[id]";
        $reserva_producto = FUNCIONES::objeto_bd_sql("
            select vp.*,m.mon_Simbolo from reserva_producto vp
            inner join con_moneda m on(vp.rprod_moneda=m.mon_id)
            where rprod_id='$get[id]'
                ");
        $volver = $this->link . "?mod=" . $this->modulo . "&tarea=$_GET[tarea]&id=$reserva_producto->rprod_ven_id";
        ?>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <script>
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

            function f_tipo_pago() {
                var tipo = document.getElementById('tipo_pago').options[document.getElementById('tipo_pago').selectedIndex].value;
                var tipo = $('#tipo_pago option:selected').val();
                if (tipo == '1') {
                    $('#div_datos_operacion').css("display", "none");
                } else {
                    if (tipo == '2') {
                        $('#lbl_transaccion').html('Nro de Cheque');
                    }
                    if (tipo == '3') {
                        $('#lbl_transaccion').html('Nro de Deposito');
                    }
                    if (tipo == '4') {
                        $('#lbl_transaccion').html('Nro de Transferencia');
                    }
                    $('#div_datos_operacion').css("display", "block");
                }
            }

            function cambiar_moneda() {
                var pagara = parseFloat(document.frm_sentencia.respag_monto.value);
                var moneda_original = document.frm_sentencia.moneda_original.value;
                var moneda_sel = document.frm_sentencia.moneda.options[document.frm_sentencia.moneda.selectedIndex].value;
                var valor_convertido = parseFloat(document.frm_sentencia.respag_monto.value);
                var tc = parseFloat(<?php echo $this->tc; ?>);
                if (moneda_original != moneda_sel) {
                    if (moneda_sel == '1') {
                        pagara *= tc;
                    } else {
                        pagara /= tc;
                    }
                }
                document.frm_sentencia.valor_convertido.value = roundNumber(pagara, 2);
            }

            function enviar_formulario_anticipo() {
                var monto = $('#vp_monto').val() * 1;
                var caja = $('#caja option:selected').val();
                if (monto > 0 && caja != '') {
                    document.frm_sentencia.submit();
                } else {
                    $.prompt('Ingrese el monto a pagar del producto y seleccione la caja.');
                }
            }
            </script>

            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">

                <div id="FormSent">



                    <div class="Subtitulo">Pagos Producto</div>

                    <div id="ContenedorSeleccion">
                        <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">* </span><label id="lbl_fecha" name="lbl_fecha">Fecha Pago</label></div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="vp_fecha" id="vp_fecha" size="20" value="<?php echo date('d/m/Y'); ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">

                            <div class="Etiqueta" ><span class="flechas1">* </span>Monto Producto</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" readonly="readonly" name="" id="" size="12" value="<? echo $reserva_producto->rprod_monto; ?>" type="text" onKeyPress="return ValidarNumero(event);">
                                <input  class="caja_texto" readonly="readonly" name="vp_moneda" id="vp_moneda" size="2" value="<? echo $reserva_producto->rprod_moneda; ?>" type="hidden" >
        <?php
        echo $reserva_producto->mon_Simbolo;
        ?>
                            </div>
                        </div>
                        <!--Fin-->
        <?php
        $pagado = 1 * FUNCIONES::atributo_bd_sql("select sum(respag_monto)as campo from reserva_pago_producto where respag_rprod_id='$get[id]' and respag_estado='Pagado'");
        ?>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Monto Pagado</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" name="vp_pagado" readonly id="vp_pagado" size="12" value="<?php echo $pagado; ?>" type="text" onKeyPress="return ValidarNumero(event);">
                                <?php
                                echo $reserva_producto->mon_Simbolo;
                                ?>
                            </div>

                        </div>
                        <!--Fin-->
                        <?php ?>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Saldo</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" name="vp_saldo" readonly id="vp_saldo" size="12" readonly value="<?php echo ($reserva_producto->rprod_monto - $pagado); ?>" type="text" onKeyPress="return ValidarNumero(event);">
        <?php
        echo $reserva_producto->mon_Simbolo;
        ?>
                            </div>

                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Monto a Pagar:</div>
                            <div id="CajaInput">
                                <input  class="caja_texto" name="vp_monto" id="vp_monto" size="12" value="" type="text" onKeyPress="return ValidarNumero(event);">
        <?php
        echo $reserva_producto->mon_Simbolo;
        ?>
                            </div>

                        </div>
                        <!--Fin-->

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><b>Pagos</b></div>                                
        <?php FORMULARIO::frm_pago(array('cmp_fecha' => 'vp_fecha', 'cmp_monto' => 'vp_monto', 'cmp_moneda' => 'vp_moneda')); ?>
                        </div>




                    </div>

                    <div id="ContenedorDiv">

                        <div id="CajaBotones">

                            <center>

                                <input type="button" class="boton" name="" value="Guardar" onclick="enviar_formulario_anticipo();">
                                <input type="reset" class="boton" name="" value="Cancelar">
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $volver; ?>';">                                            

                            </center>

                        </div>

                    </div>

                </div>

        </div>
        <script>
            jQuery(function($) {

                $("#vp_fecha").mask("99/99/9999");

            });

        </script>
        <?php
    }

    function listado_anticipos_producto($datos) {
        $get = $datos->get;
        $url = $this->link . "?mod=" . $this->modulo . "&tarea=$get[tarea]";
//        echo "mostrando pagos de $get[id]...";
        ?>


        <script>
            function anular_pago(id) {
                var txt = 'Esta seguro de anular el pago del Producto?';

                $.prompt(txt, {
                    buttons: {Anular: true, Cancelar: false},
                    callback: function(v, m, f) {

                        if (v) {
                            location.href = '<?php echo $url ?>&acc=anular_pago&id=' + id;
                        }

                    }
                });
            }
            function ver_comprobante(id)
            {
                location.href = 'gestor.php?mod=reserva&tarea=ANTICIPOS RESERVA&acc=ver_comprobante&id=' + id;
            }
        </script>
        <div id="contenido_reporte" style="clear: both;">
            <center>
                <br/><br/><h2>ANTICIPOS</h2><br/><br/>
                <table class="tablaLista" cellpadding="0" cellspacing="0" width="60%">
                    <thead>
                        <tr>
                            <th>Nro</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                            <th class="tOpciones" width="100px">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
        <?php
        $sql = "
                        select * from reserva_pago_producto 
                        inner join con_moneda on (respag_moneda=mon_id)                        
                        where respag_rprod_id=$get[id] 
                        and respag_estado='Pagado'    
                        order by respag_fecha desc,respag_hora desc";
        $pagos = FUNCIONES::objetos_bd_sql($sql);
//                    echo $sql;
        for ($i = 0; $i < $pagos->get_num_registros(); $i++) {
            $k = $i + 1;
            $pago = $pagos->get_objeto();
            ?>
                            <tr>
                                <td style="text-align: center"><?php echo $k; ?></td>
                                <td style="text-align: center"><?php echo FUNCIONES::get_fecha_latina($pago->respag_fecha); ?></td>
                                <td style="text-align: center"><?php echo $pago->respag_hora; ?></td>
                                <td style="text-align: right"><?php echo $pago->respag_monto; ?></td>
                                <td style="text-align: center"><?php echo $pago->mon_titulo; ?></td>
                                <td>
                        <center>
                            <table>
                                <tr>                                    
                                    <td>
                                        <a href="<?php echo $url . "&acc=ver_pago&id=$pago->respag_id"; ?>">
                                            <img src="images/ver.png" alt="VER" title="VER PAGOS" border="0">
                                        </a>
                                    </td>
                                    <td>                                        
                                        <a href="javascript:anular_pago('<?php echo $pago->respag_id; ?>')">    
                                            <img src="images/anular.png" alt="ANULAR" title="ANULAR PAGO" border="0">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </center>
                        </td>
                        </tr>
            <?php
            $pagos->siguiente();
        }
        $venta_producto = FUNCIONES::objeto_bd_sql("select * from reserva_producto where rprod_id=$get[id]");
        $volver = $this->link . "?mod=" . $this->modulo . "&tarea=$_GET[tarea]&id=$venta_producto->rprod_ven_id";
        ?>
                    </tbody>
                </table>
            </center>
        </div>

        <!--<div id="ContenedorSeleccion">-->
        <div id="ContenedorDiv">

            <div id="CajaBotones">
                <center>
                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $volver; ?>';">                                            
                </center>
            </div>

        </div>
        <!--</div>-->    
        <?php
    }

    function mostrar_recibo_anticipo($vp_id) {

        $pago_producto = FUNCIONES::objeto_bd_sql("select * from reserva_pago_producto where respag_id='$vp_id'");

        $venta_producto = FUNCIONES::objeto_bd_sql("select * from reserva_producto 
        inner join urbanizacion_producto on (rprod_prod_id=uprod_id)
        where rprod_id=$pago_producto->respag_rprod_id");

        $pagina = "'contenido_reporte'";
        $page = "'about:blank'";
        $extpage = "'reportes'";
        $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";
        $extra1 = "'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
                                <link href=css/recibos.css rel=stylesheet type=text/css>
			  </head>
			  <body onload=window.print();>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
        $extra1.=" <a href=\'javascript:window.print();\'>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
        $extra2 = "'</center></body></html>'";

        $ret_url = "$this->link?mod=$this->modulo&tarea=$_GET[tarea]&acc=ver_pagos&id=$pago_producto->respag_rprod_id";

        echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'' . $ret_url . '\';"></td></tr></table>
				';



        $venta = FUNCIONES::objeto_bd_sql("select * from venta where 
        ven_id='$venta_producto->rprod_ven_id'");

        $producto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto 
        where uprod_id=$venta_producto->rprod_prod_id");

        include_once 'clases/recibo.class.php';

        $data = array(
            'titulo' => 'PAGO DE ANTICIPO DE PRODUCTO',
            'referido' => FUNCIONES::interno_nombre($venta->ven_int_id),
            'usuario' => FUNCIONES::usuario_nombre($pago_producto->vp_usu_id),
            'monto' => $pago_producto->respag_monto,
            'moneda' => $pago_producto->respag_moneda,
            'nro_recibo' => $pago_producto->respag_recibo,
            'fecha' => $pago_producto->respag_fecha,
            'concepto' => "Pago de Anticipo de Producto {$producto->uprod_nombre} de la Venta Nro. $venta->ven_id - " . $venta->ven_concepto,
//            'has_detalle' => '1',
//            'det_cabecera' => $det_cabecera,
//            'det_body' => $det_body,
//            'nota' => "sin nota",
        );
        ?>
        <br><br><br>
        <?php
        RECIBO::pago($data);
    }

    function anular_anticipo_producto($vp_id) {

        $venta_producto = FUNCIONES::objeto_bd_sql("
        select vp.* from reserva_producto vp
        inner join reserva_pago_producto vpp on (vp.rprod_id=vpp.respag_rprod_id) 
        where vpp.respag_id=$vp_id");

        $sql = "select * from con_periodo inner join con_comprobante on (pdo_id=cmp_peri_id)
            where cmp_tabla='reserva_pago_producto' and cmp_tabla_id='$vp_id'
                and pdo_estado='Abierto'";
        $bool = FUNCIONES::objeto_bd_sql($sql);
        $url = "$this->link?mod=$this->modulo&tarea=$_GET[tarea]&acc=ver_pagos&id=$venta_producto->rprod_id";
        if (!$bool) {
            $mensaje = "El pago no puede ser Anulado por que el periodo en el que fue realizado el pago fue cerrado.";
            $tipo = 'Error';
            $this->formulario->ventana_volver($mensaje, $url, '', $tipo);
            return false;
        }
//        echo "yendo a anular...";
//        return FALSE;

        $conec = new ADO();
        $sql2 = "update reserva_pago_producto set respag_estado='Anulado' where respag_id='$vp_id'";
        $conec->ejecutar($sql2);

        include_once 'clases/registrar_comprobantes.class.php';
        $resp = COMPROBANTES::anular_comprobante('reserva_pago_producto', $vp_id);

        if ($resp) {

            $total_anticipos = FUNCIONES::atributo_bd_sql("select sum(respag_monto)as campo 
            from reserva_pago_producto where respag_rprod_id='$venta_producto->rprod_id'
            and respag_estado='Pagado'") * 1;

            if ($total_anticipos == 0) {
                FUNCIONES::bd_query("update reserva_producto set rprod_estado='Pendiente' where rprod_id=$venta_producto->rprod_id");
            }
            $tipo = "";
            $mensaje = 'Pago del Anticipo de Producto anulado Correctamente';
        } else {

            $sql2 = "update reserva_pago_producto set respag_estado='Pagado' where respag_id='$vp_id'";
            $conec->ejecutar($sql2);
            $tipo = "Error";
            $mensaje = 'Ocurrio un error con la anulación del pago';
        }
        $this->formulario->ventana_volver($mensaje, $url, $tipo);
    }

    function eliminar_reserva_producto($rprod_id) {
//        echo "eliminando la reserva de producto nro: $rprod_id...";

        $venta_producto = FUNCIONES::objeto_bd_sql("
        select vp.* from reserva_producto vp        
        where vp.rprod_id=$rprod_id");
        FUNCIONES::bd_query("update reserva_producto set rprod_estado='Deshabilitado' where rprod_id='$rprod_id'");

        $url = "$this->link?mod=$this->modulo&tarea=$_GET[tarea]&id=$venta_producto->rprod_ven_id";
        $mensaje = 'Reserva de Producto deshabilitada correctamente';
        $this->formulario->ventana_volver($mensaje, $url);
    }

    function concretar_venta_producto() {
//        $reserva_producto = FUNCIONES::objeto_bd_sql("select * from reserva_producto where rprod_id=''");
    }

}
?>