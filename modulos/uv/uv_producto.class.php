<?php

class UV_PRODUCTO extends UV {

    function UV_PRODUCTO() {
        parent::__construct();
    }

    function producto() {
        if ($_GET['acc'] <> "") {
            if ($_GET['acc'] == 'guardar') {
                $this->guardar_producto($_REQUEST);
            }
            if ($_GET['acc'] == 'eliminar') {
                $this->eliminar_producto($_GET['id']);
            }
            if ($_GET['acc'] == 'editar') {
                $this->cargar_datos_producto();
                $this->formulario->dibujar_tarea();
                $this->formulario_producto('modificar');
            }
            if ($_GET['acc'] == 'modificar') {
                $this->modificar_producto($_REQUEST);
//                $this->formulario_producto('modificar');
            }

//            if ($_GET[acc] == 'plan_comp') {
//                $datos = new stdClass();
//                $datos->post = $_POST;
//                $datos->get = $_GET;
//                $this->form_niveles($datos);
//                $this->form_porcentajes($datos);
//                $this->form_fondos($datos);
//            }
//
//            if ($_GET[acc] == 'actualizar_niveles') {
//                $datos = new stdClass();
//                $datos->post = $_POST;
//                $datos->get = $_GET;
//                $this->actualizar_niveles($datos);
//                $this->form_niveles($datos);
//                $this->form_porcentajes($datos);
//                $this->form_fondos($datos);
//            }
//
//            if ($_GET[acc] == 'actualizar_porcentajes') {
//                $datos = new stdClass();
//                $datos->post = $_POST;
//                $datos->get = $_GET;
//                $this->actualizar_porcentajes($datos);
//                $this->form_niveles($datos);
//                $this->form_porcentajes($datos);
//                $this->form_fondos($datos);
//            }
//
//            if ($_GET[acc] == 'actualizar_fondos') {
//                $datos = new stdClass();
//                $datos->post = $_POST;
//                $datos->get = $_GET;
//                $this->actualizar_fondos($datos);
//                $this->form_niveles($datos);
//                $this->form_porcentajes($datos);
//                $this->form_fondos($datos);
//            }
        } else {
            $this->formulario->dibujar_tarea();
            $this->formulario_producto('nuevo');
            $this->listado_productos($_REQUEST);
        }
    }

    function guardar_producto($datosForm) {
        $datosForm = (object) $datosForm;
//        echo "<pre>";
//        print_r($datosForm);
//        echo "</pre>";
//        echo "guardando historial de multas para la urb $urb_id";

        $hoy = date("Y-m-d");
        $usuario = $this->usu->get_id();
        $sql_insert = "
                insert into urbanizacion_producto(
                    uprod_nombre,uprod_descripcion,
                    uprod_urb_id,uprod_precio,
                    uprod_costo,uprod_ci,
                    uprod_moneda,uprod_superficie,
                    uprod_cant_habs,uprod_fecha_cre,
                    uprod_usu_id
                )
                values(
                    '$datosForm->uprod_nombre','$datosForm->uprod_descripcion',
                    '$datosForm->id','$datosForm->uprod_precio',
                    '$datosForm->uprod_costo','$datosForm->uprod_ci',    
                    '$datosForm->uprod_moneda','$datosForm->uprod_superficie',
                    '$datosForm->uprod_cant_habs','$hoy',
                    '$usuario'    
                )";
        FUNCIONES::bd_query($sql_insert);
        $mensaje = 'Producto agregado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTO&id=$datosForm->id");
    }

    function modificar_producto($datosForm) {
        $datosForm = (object) $datosForm;
//        echo "<pre>";
//        print_r($datosForm);
//        echo "</pre>";
        $usu_mod = $this->usu->get_id();
        $hoy = date("Y-m-d");
        $sql_upd = "
            update urbanizacion_producto set 
                uprod_nombre='$datosForm->uprod_nombre',
                uprod_descripcion='$datosForm->uprod_descripcion',
                uprod_precio='$datosForm->uprod_precio',
                uprod_ci='$datosForm->uprod_ci',
                uprod_costo='$datosForm->uprod_costo',    
                uprod_superficie='$datosForm->uprod_superficie',
                uprod_cant_habs='$datosForm->uprod_cant_habs',    
                uprod_usu_mod='$usu_mod',    
                uprod_fecha_mod='$hoy',        
                uprod_moneda='$datosForm->uprod_moneda'
            where uprod_id='$datosForm->id'";
//        echo "modificando el producto $datosForm->id";
        FUNCIONES::bd_query($sql_upd);
        $mensaje = 'Producto modificado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTO&id=$datosForm->urb_id");
    }

    function eliminar_producto($uprod_id) {

        $bool = TRUE;
        if (!$bool) {
            $mensaje = "El pago de la mora no puede ser Anulada por que el periodo en el que fue realizado el pago fue cerrado.";
            $tipo = 'Error';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
            return;
        }
        $producto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto where uprod_id='$uprod_id'");
        $conec = new ADO();
        $sql2 = "update urbanizacion_producto set uprod_eliminado='Si' where uprod_id='$uprod_id'";
        $conec->ejecutar($sql2);
        $mensaje = 'Producto eliminado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=PRODUCTO&id=$producto->uprod_urb_id");
    }

    function cargar_datos_producto() {
        $producto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto where uprod_id='$_GET[id]'");
        $_POST[uprod_id] = $producto->uprod_id;
        $_POST[uprod_nombre] = $producto->uprod_nombre;
        $_POST[uprod_descripcion] = $producto->uprod_descripcion;
        $_POST[uprod_urb_id] = $producto->uprod_urb_id;
        $_POST[uprod_precio] = $producto->uprod_precio;
        $_POST[uprod_costo] = $producto->uprod_costo;
        $_POST[uprod_ci] = $producto->uprod_ci;
        $_POST[uprod_moneda] = $producto->uprod_moneda;
        $_POST[uprod_superficie] = $producto->uprod_superficie;
        $_POST[uprod_cant_habs] = $producto->uprod_cant_habs;
    }

    function formulario_producto($modo) {
        $url = $this->link . '?mod=' . $this->modulo . "&tarea=$_GET[tarea]";
        if ($modo == 'nuevo') {
            $url .= "&acc=guardar&id=$_GET[id]";
            $volver = $this->link . "?mod=uv&tarea=ACCEDER";
        } else if ($modo == 'modificar') {
            $url .= "&acc=modificar&id=$_GET[id]&urb_id=$_POST[uprod_urb_id]";
            $volver = $this->link . "?mod=uv&tarea=PRODUCTO&id=$_POST[uprod_urb_id]";
        }
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

            function validar_campos_descuento() {
                var comdes_motivo = document.getElementById('comdes_motivo').value;
                var comdes_monto = document.getElementById('comdes_monto').value;
                var total_comisionado = parseFloat(document.getElementById('total_comisionado').value);
                var total_descuento = parseFloat(document.getElementById('total_descuento').value);

                if (comdes_motivo === '' || comdes_monto === '') {
                    $.prompt('Existen datos en blanco para el descuento.');
                } else {
                    if ((total_comisionado - total_descuento) >= parseFloat(comdes_monto)) {
                        location.href = 'gestor.php?mod=vendedor&tarea=CUENTAS&acc=descuentos&acc2=add&comdes_motivo=' + comdes_motivo + '&comdes_monto=' + comdes_monto + '&id=<?php echo $_GET['id']; ?>&com=<?php echo $_GET['com']; ?>&ven=<?php echo $_GET['ven']; ?>';
                    } else {
                        $.prompt('El monto a descontar no pueder ser mayor a: ' + (total_comisionado - total_descuento));
                    }
                }
            }

            function enviar_formulario_producto() {

                var nombre = $('#uprod_nombre').val();
                var precio = $('#uprod_precio').val() * 1;
//                var moneda = $('#uprod_moneda option:selected').val();                

                if (nombre === '') {
                    $.prompt('Debe especificar un nombre para el producto.');
                    return false;
                }

                if (precio === '' || precio === 0) {
                    $.prompt('Debe especificar un precio para el producto.');
                    return false;
                }

//                if (moneda === '') {
//                    $.prompt('Debe especificar una moneda para el precio del producto.');
//                    return false;
//                }
                                
//                console.log('moneda => ' + moneda);                
                $('#frm_vendedor').submit();
            }
        </script>
        <script type="text/javascript" src="js/util.js?v=<?php echo time();?>"></script>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_vendedor" name="frm_vendedor" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Nombre:</div>
                            <div id="CajaInput">
                                <input id="uprod_nombre" name="uprod_nombre" value="<?php echo $_POST[uprod_nombre]; ?>" type="text" size="50" />
                            </div>                                    

                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1"></span>Descripcion:</div>
                            <div id="CajaInput">                                        
                                <textarea name="uprod_descripcion" id="uprod_descripcion"><?php echo $_POST[uprod_descripcion]; ?></textarea>
                            </div>
                        </div>                                
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1"></span>Superficie de Construccion(M2):</div>
                            <div id="CajaInput">                                        
                                <input type="text" name="uprod_superficie" id="uprod_superficie" value="<?php echo $_POST[uprod_superficie]; ?>" />
                            </div>
                        </div>                                
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1"></span>Cantidad de Habitaciones:</div>
                            <div id="CajaInput">                                        
                                <input type="text" name="uprod_cant_habs" id="uprod_cant_habs" value="<?php echo $_POST[uprod_cant_habs]; ?>" />
                            </div>
                        </div>                                

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Costo(USD):</div>
                            <div id="CajaInput">
                                <input id="uprod_costo" name="uprod_costo" value="<?php echo $_POST[uprod_costo]; ?>" type="text" size="10" />
                            </div>                                    
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Precio(USD):</div>
                            <div id="CajaInput">
                                <input id="uprod_precio" name="uprod_precio" value="<?php echo $_POST[uprod_precio]; ?>" type="text" size="10" />
                            </div>                                    
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Cuota Inicial(USD):</div>
                            <div id="CajaInput">
                                <input id="uprod_ci" name="uprod_ci" value="<?php echo $_POST[uprod_ci]; ?>" type="text" size="10" />
                            </div>                                    
                        </div>
                        
                        <input type="hidden" id="uprod_moneda" name="uprod_moneda" value="2"/>
                        <div id="ContenedorDiv" style="display: none;">
                            <div class="Etiqueta" >Moneda:</div>
                            <div id="CajaInput">
                                <!--<select id="uprod_moneda" name="uprod_moneda">-->
                                    <!--<option value="">Seleccione</option>-->
                                    <?php
//                                    $fun = new FUNCIONES();
//                                    $fun->combo("select mon_id as id,mon_titulo as nombre from con_moneda order by id asc", $_POST[uprod_moneda]);
                                    ?>
                                <!--</select>-->
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
            mask_decimal('#uprod_precio', null);
            mask_decimal('#uprod_costo', null);
            mask_decimal('#uprod_ci', null);
            mask_decimal('#uprod_superficie', null);
            mask_integer('#uprod_cant_habs', null);
        </script>
        <?php
    }

    function listado_productos($datos) {
        $url = $this->link . '?mod=' . $this->modulo . "&tarea=$_GET[tarea]";
        $datos = (object) $datos;
//                                            echo "listado_productos...";
//                                            echo "<pre>";
//                                            print_r($datos);
//                                            echo "</pre>";
//                                            return;
        ?>
        <script>
            function eliminar_producto(id) {
                var txt = '¿Esta seguro de querer eliminar el producto?';
                $.prompt(txt, {
                    buttons: {Aceptar: true, Cancelar: false},
                    callback: function(v, m, f) {
                        if (v) {
                            location.href = 'gestor.php?mod=uv&tarea=PRODUCTO&acc=eliminar&&id=' + id;
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
                            <th>DESCRIPCION</th>                                
                            <th>COSTO</th>                                
                            <th>PRECIO</th>                                
                            <th>C. INICIAL</th>                                
                            <th class="tOpciones">Opciones</th> 
                        </tr>	
                    </thead>
                    <tbody>
                        <?php
                        $sql = "select * from urbanizacion_producto 
                                                                inner join con_moneda on(uprod_moneda=mon_id)
                                                                inner join urbanizacion on(uprod_urb_id=urb_id)
                                                                where urb_id = '$datos->id'
                                                                    and uprod_eliminado='No'
                                                                order by uprod_id asc";
                        $conec = new ADO();
                        $conec->ejecutar($sql);

                        for ($i = 0; $i < $conec->get_num_registros(); $i++) {
                            $producto = $conec->get_objeto();
                            ?>
                            <tr>
                                <td><?php echo strtoupper($producto->uprod_nombre); ?></td>
                                <td><?php echo $producto->uprod_descripcion; ?></td>
                                <td>
                                    <?php
                                    echo $producto->mon_Simbolo . ". " . $producto->uprod_costo;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    echo $producto->mon_Simbolo . ". " . $producto->uprod_precio;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    echo $producto->mon_Simbolo . ". " . $producto->uprod_ci;
                                    ?>
                                </td>

                                <td>
                        <center>
                            <table>
                                <tr>
                                    <td>
                                        <a href="<?php echo $url . "&acc=editar&id=$producto->uprod_id"; ?>">
                                            <img src="images/b_edit.png" alt="VER" title="MODIFICAR PRODUCTO" border="0">
                                        </a>
                                    </td>
                                    
                                    <td>
                                        <!--<a href="<?php // echo $url."&acc=eliminar&id=$producto->uprod_id";           ?>">-->
                                        <a href="javascript:eliminar_producto('<?php echo $producto->uprod_id; ?>')">    
                                            <img src="images/b_drop.png" alt="ELIMINAR" title="ELIMINAR PRODUCTO" border="0">
                                        </a>
                                    </td>
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
    }

    //****PLAN DE COMPENSACION****//

    function form_niveles($datos) {
        $post = $datos->post;
        $get = $datos->get;
//        if ($post) {
//            if ($get[acc] == 'actualizar_niveles') {
//                $this->actualizar_niveles($post);
////                return;
//            }
//        }
        $sql_prod = "select * from urbanizacion_producto where uprod_id=$get[id]";
//        echo "$sql_prod";
        $producto = FUNCIONES::objeto_bd_sql($sql_prod);

        $comisiones = array();
        if (!empty($producto->uprod_porc_comision_credito)) {
            $comisiones = json_decode($producto->uprod_porc_comision_credito);
        }

        $comisiones_contado = array();
        if (!empty($producto->uprod_porc_comision_contado)) {
            $comisiones_contado = json_decode($producto->uprod_porc_comision_contado);
        }

//        print_r($comisiones->array);
//        print_r($comisiones_contado->array);
//        return;

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
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>&id=<?php echo $get['id']; ?>&tarea=PRODUCTO&acc=actualizar_niveles" method="POST" enctype="multipart/form-data">
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

                                %Comisión Contado
                                <input name="comision_valor_contado" id="comision_valor_contado"  type="text" class="caja_texto" value="" size="25" onKeyPress="return ValidarNumero(event);">
                                <br />

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
                                                <td class="td_<? echo $i; ?>"><?php echo $arr_contado[$i]; ?></td>
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

                                        <?php //endforeach;    ?>

                                    </table>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->
                    </div>

                    <script>
                        $('td#edit_comision').click(function() {
                            var valor = $(this).attr('data-valor');
                            var valor_contado = $(this).attr('data-valor-contado');

                            var index = $(this).attr('data-index');

                            $('#comision_valor').val(valor);
                            $('#comision_valor_contado').val(valor_contado);

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

    function actualizar_niveles($datos) {
        $post = $datos->post;
        $get = $datos->get;
//        echo "<pre>";
//        print_r($post);
//        echo "</pre>";
//        return;

        $this->mensaje = "Los parametros de Configuracion de Comisiones fueron Actualizadas Correctamente.";

        $conec2 = new ADO();

        $conec = new ADO();

        $accion = $post['accion'];

        if ($accion == 'comision') {
            $metodo = $post['metodo'];

            if ($metodo == 'editar') {
                //******************* CREDITO *******************//
                $index = $post['comision_index'];
                $valor = $post['comision_valor'];

                $sql = "select * from urbanizacion_producto where uprod_id='" . $get['id'] . "'";


                $objeto = FUNCIONES::objeto_bd_sql($sql);

                $comisiones = json_decode($objeto->uprod_porc_comision_credito);
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

                //******************* CREDITO *******************//
                //******************* CONTADO *******************//
                $index = $_POST['comision_index'];
                $valor = $_POST['comision_valor_contado'];

                $comisiones = json_decode($objeto->uprod_porc_comision_contado);
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

                $comision_string_contado = json_encode($dato);

                $sql = "update urbanizacion_producto set uprod_porc_comision_contado='$comision_string_contado',uprod_porc_comision_credito='$comision_string' where uprod_id='" . intval($get['id']) . "'";
                $conec->ejecutar($sql);
                //******************* CONTADO *******************//
                //******************* INICIO - INSERTANDO CONFIGURACION CREDITO *******************//
                $sql = "select * from urbanizacion_producto where uprod_id='" . $get['id'] . "'";
                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();

                $comisiones = json_decode($objeto->uprod_porc_comision_credito);

                foreach ($comisiones->array as $key => $comision) {
                    $sql = "select * from producto_comision where pcom_key='$key' AND pcom_tipo='Credito' AND pcom_prod_id='" . $get['id'] . "' ORDER BY pcom_id ASC";

                    $conec2->ejecutar($sql);

                    $num2 = $conec2->get_num_registros();

                    if ($num2 > 0) {
                        $objeto2 = $conec2->get_objeto();

                        $sql = "INSERT INTO `producto_comision` (`pcom_key`, `pcom_porc_empresa`, `pcom_porc_patrocinio`, `pcom_porc_huerfanos`,`pcom_tipo` ,`pcom_prod_id`) VALUES ('$key', '$objeto2->pcom_porc_empresa', '$objeto2->pcom_porc_patrocinio', '$objeto2->pcom_porc_huerfanos','Credito', '" . $get['id'] . "');";
                        $conec->ejecutar($sql);

                        $sql = "delete from producto_comision where pcom_id='" . $objeto2->pcom_id . "' AND pcom_tipo='Credito' AND pcom_prod_id='" . $get['id'] . "'";
                        $conec->ejecutar($sql);
                    }
                }
                //******************* FIN - INSERTANDO CONFIGURACION CREDITO *******************//
                //******************* INICIO - INSERTANDO CONFIGURACION CONTADO *******************//


                $comisiones = json_decode($objeto->uprod_porc_comision_contado);

                foreach ($comisiones->array as $key => $comision) {
                    $sql = "select * from producto_comision where pcom_key='$key' AND pcom_tipo='Contado' AND pcom_prod_id='" . $get['id'] . "' ORDER BY pcom_id ASC";

                    $conec2->ejecutar($sql);

                    $num2 = $conec2->get_num_registros();

                    if ($num2 > 0) {
                        $objeto2 = $conec2->get_objeto();

                        $sql = "INSERT INTO `producto_comision` (`pcom_key`, `pcom_porc_empresa`, `pcom_porc_patrocinio`, `pcom_porc_huerfanos`,`pcom_tipo` ,`pcom_prod_id`) VALUES ('$key', '$objeto2->pcom_porc_empresa', '$objeto2->pcom_porc_patrocinio', '$objeto2->pcom_porc_huerfanos','Contado', '" . $get['id'] . "');";
                        $conec->ejecutar($sql);

                        $sql = "delete from producto_comision where pcom_id='" . $objeto2->pcom_id . "' AND pcom_tipo='Contado' AND pcom_prod_id='" . $get['id'] . "'";
                        $conec->ejecutar($sql);
                    }
                }
                //******************* FIN - INSERTANDO CONFIGURACION CONTADO *******************//
            }

            if ($metodo == 'agregar') {
                //******************* CREDITO *******************//
                $valor = $post['comision_valor'];

                $sql = "select * from urbanizacion_producto where uprod_id='" . $get['id'] . "'";


                $producto = FUNCIONES::objeto_bd_sql($sql);

                $comisiones = json_decode($producto->uprod_porc_comision_credito);
                $comisiones_array = $comisiones->array;
                $comisiones_array[] = $valor;

                $dato = new stdClass();
                $dato->array = $comisiones_array;
                $comision_string = json_encode($dato);


//                FUNCIONES::bd_query($sql);
                //******************* CREDITO *******************//
                //******************* CONTADO *******************//
                $valor = $post['comision_valor_contado'];

                $comisiones = json_decode($producto->uprod_porc_comision_contado);
                $comisiones_array = $comisiones->array;
                $comisiones_array[] = $valor;

                $dato = new stdClass();
                $dato->array = $comisiones_array;
                $comision_string_contado = json_encode($dato);

                $sql = "
                    update urbanizacion_producto set uprod_porc_comision_credito='$comision_string',
                        uprod_porc_comision_contado='$comision_string_contado'
                        where uprod_id='$get[id]'";
//                echo $sql;
                $conec->ejecutar($sql);
                //******************* CONTADO *******************//
                //******************* INICIO - INSERTANDO CONFIGURACION CREDITO *******************//
                $sql = "select * from urbanizacion_producto where uprod_id='$get[id]'";

                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();

                $comisiones = json_decode($objeto->uprod_porc_comision_credito);

                foreach ($comisiones->array as $key => $comision) {
                    $sql = "
                            select * from producto_comision 
                            where pcom_key='$key' 
                                AND pcom_tipo='Credito' 
                                AND pcom_prod_id='" . $get['id'] .
                            "' ORDER BY pcom_id ASC";

                    $conec2->ejecutar($sql);

                    $num2 = $conec2->get_num_registros();

                    if ($num2 > 0) {
                        $objeto2 = $conec2->get_objeto();

                        $sql = "INSERT INTO `producto_comision` (`pcom_key`, `pcom_porc_empresa`, `pcom_porc_patrocinio`, `pcom_porc_huerfanos`, `pcom_tipo` ,`pcom_prod_id`) 
                            VALUES ('$key', '$objeto2->pcom_porc_empresa', '$objeto2->pcom_porc_patrocinio', '$objeto2->pcom_porc_huerfanos', 'Credito' ,'" . $get['id'] . "');";
                        $conec->ejecutar($sql);

                        $sql = "delete from producto_comision where pcom_id='" . $objeto2->pcom_id . "' AND pcom_tipo='Credito' AND pcom_prod_id='" . $get['id'] . "'";
                        $conec->ejecutar($sql);
                    } else {
                        $sql = "INSERT INTO `producto_comision` (`pcom_key`, `pcom_porc_empresa`, `pcom_porc_patrocinio`, `pcom_porc_huerfanos`, `pcom_tipo` , `pcom_prod_id`) 
                            VALUES ('$key', '0', '0', '0', 'Credito' ,'" . $get['id'] . "');";
                        $conec->ejecutar($sql);
                    }
                }
                //******************* FIN - INSERTANDO CONFIGURACION CREDITO *******************//
                //******************* INICIO - INSERTANDO CONFIGURACION CONTADO *******************//
                $sql = "select * from urbanizacion_producto where uprod_id='$get[id]'";

                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();

                $comisiones = json_decode($objeto->uprod_porc_comision_contado);

                foreach ($comisiones->array as $key => $comision) {
                    $sql = "
                            select * from producto_comision 
                            where pcom_key='$key' 
                                AND pcom_tipo='Contado' 
                                AND pcom_prod_id='" . $get['id'] .
                            "' ORDER BY pcom_id ASC";

                    $conec2->ejecutar($sql);

                    $num2 = $conec2->get_num_registros();

                    if ($num2 > 0) {
                        $objeto2 = $conec2->get_objeto();

                        $sql = "INSERT INTO `producto_comision` (`pcom_key`, `pcom_porc_empresa`, `pcom_porc_patrocinio`, `pcom_porc_huerfanos`, `pcom_tipo` ,`pcom_prod_id`) 
                            VALUES ('$key', '$objeto2->pcom_porc_empresa', '$objeto2->pcom_porc_patrocinio', '$objeto2->pcom_porc_huerfanos', 'Contado' ,'" . $get['id'] . "');";
                        $conec->ejecutar($sql);

                        $sql = "delete from producto_comision where pcom_id='" . $objeto2->pcom_id . "' AND pcom_tipo='Contado' AND pcom_prod_id='" . $get['id'] . "'";
                        $conec->ejecutar($sql);
                    } else {
                        $sql = "INSERT INTO `producto_comision` (`pcom_key`, `pcom_porc_empresa`, `pcom_porc_patrocinio`, `pcom_porc_huerfanos`, `pcom_tipo` , `pcom_prod_id`) 
                            VALUES ('$key', '0', '0', '0', 'Contado' ,'" . $get['id'] . "');";
                        $conec->ejecutar($sql);
                    }
                }
                //******************* FIN - INSERTANDO CONFIGURACION CONTADO *******************//
            }



            if ($metodo == 'eliminar') {
                //******************* CREDITO *******************//
                $index = $post['comision_index'];

                $sql = "select * from urbanizacion_producto where uprod_id='" . $get['id'] . "'";

                $objeto = FUNCIONES::objeto_bd_sql($sql);

                $comisiones = json_decode($objeto->uprod_porc_comision_credito);
                $nuevas_comision = array();
                $comisiones_array = $comisiones->array;
                unset($comisiones_array[$index]);

                $dato = new stdClass();
                $dato->array = $comisiones_array;
                $comision_string = json_encode($dato);

                //******************* CREDITO *******************//
                //******************* CONTADO *******************//
                $index = $_POST['comision_index'];

                $comisiones = json_decode($objeto->uprod_porc_comision_contado);
                $nuevas_comision = array();
                $comisiones_array = $comisiones->array;
                unset($comisiones_array[$index]);

                $dato = new stdClass();
                $dato->array = $comisiones_array;
                $comision_string_contado = json_encode($dato);

                $sql = "update urbanizacion_producto set uprod_porc_comision_credito='$comision_string',
                        uprod_porc_comision_contado='$comision_string_contado'
                        where uprod_id='" . intval($get['id']) . "'";
                $conec->ejecutar($sql);
                //******************* CONTADO *******************//
                //******************* INICIO - INSERTANDO CONFIGURACION CREDITO *******************//
                $sql = "select * from urbanizacion_producto where uprod_id='" . $get['id'] . "'";

                $conec->ejecutar($sql);
                $objeto = $conec->get_objeto();


                $comisiones = json_decode($objeto->uprod_porc_comision_credito);

                $i = 0;
                $ids = '';

                $arr = $comisiones->array;
                $tamanio = count($arr);

                if ($tamanio > 0) {
                    foreach ($comisiones->array as $key => $comision) {

                        $sql = "select * from producto_comision where pcom_key='$key' AND pcom_tipo='Credito' AND pcom_prod_id='" . $get['id'] . "' ORDER BY pcom_id ASC";

                        $conec2->ejecutar($sql);

                        $num2 = $conec2->get_num_registros();

                        if ($num2 > 0) {
                            $objeto2 = $conec2->get_objeto();

                            if ($i == 0)
                                $ids = $objeto2->pcom_id;
                            if ($i > 0)
                                $ids.=',' . $objeto2->pcom_id;


                            $sql = "UPDATE `producto_comision` set `pcom_key`='$key', `pcom_porc_empresa`='$objeto2->pcom_porc_empresa', `pcom_porc_patrocinio`='$objeto2->pcom_porc_patrocinio', `pcom_porc_huerfanos`='$objeto2->pcom_porc_huerfanos',`pcom_tipo`='Credito' where pcom_id='$objeto2->pcom_id'";
                            $conec->ejecutar($sql);

                            $i++;
                        }
                    }

                    if ($ids != '') {
                        $sql = "delete from producto_comision where pcom_id not in ($ids) AND pcom_tipo='Credito' AND pcom_prod_id='" . $get['id'] . "'";
                        $conec->ejecutar($sql);
                    }
                } else {
                    $sql = "delete from producto_comision where pcom_tipo='Credito' AND pcom_prod_id='" . $get['id'] . "'";
                    $conec->ejecutar($sql);
                }
                //******************* FIN - INSERTANDO CONFIGURACION CREDITO *******************//
                //******************* INICIO - INSERTANDO CONFIGURACION CONTADO *******************//
//                $sql = "select urb_porc_comisiones_contado from urbanizacion where urb_id='" . intval($get['id']) . "'";
//
//                $conec->ejecutar($sql);
//                $objeto = $conec->get_objeto();

                $comisiones = json_decode($objeto->uprod_porc_comision_credito);

                $i = 0;
                $ids = '';

                $arr = $comisiones->array;
                $tamanio = count($arr);

                if ($tamanio > 0) {
                    foreach ($comisiones->array as $key => $comision) {
                        $sql = "select * from producto_comision where pcom_key='$key' AND pcom_tipo='Contado' AND pcom_prod_id='" . $get['id'] . "' ORDER BY pcom_id ASC";

                        $conec2->ejecutar($sql);

                        $num2 = $conec2->get_num_registros();

                        if ($num2 > 0) {
                            $objeto2 = $conec2->get_objeto();

                            if ($i == 0)
                                $ids = $objeto2->pcom_id;
                            if ($i > 0)
                                $ids.=',' . $objeto2->pcom_id;

                            $sql = "UPDATE `producto_comision` set `pcom_key`='$key', `pcom_porc_empresa`='$objeto2->pcom_porc_empresa', `pcom_porc_patrocinio`='$objeto2->pcom_porc_patrocinio', `pcom_porc_huerfanos`='$objeto2->pcom_porc_huerfanos',`pcom_tipo`='Contado' where pcom_id='$objeto2->pcom_id'";
                            $conec->ejecutar($sql);

                            $i++;
                        }
                    }

                    if ($ids != '') {
                        $sql = "delete from producto_comision where pcom_id not in ($ids) AND pcom_tipo='Contado' AND pcom_prod_id='" . $get['id'] . "'";
                        $conec->ejecutar($sql);
                    }
                } else {
                    $sql = "delete from producto_comision where pcom_tipo='Contado' AND pcom_prod_id='" . $get['id'] . "'";
                    $conec->ejecutar($sql);
                }

                //******************* FIN - INSERTANDO CONFIGURACION CONTADO *******************//
            }
        }
    }

    function form_porcentajes($datos) {
        $get = $datos->get;
        $post = $datos->post;
        $url = $this->link . '?mod=' . $this->modulo;

        $producto = FUNCIONES::objeto_bd_sql("select * from urbanizacion_producto where uprod_id=$get[id]");

//        if ($this->datos_configuracion_comisiones()) {
//            if ($_GET['acc'] == 'actualizar_configuracion_comisiones2')
//                $this->actualizar_configuracion_comisiones2();
//        }
        $red = $url . "&tarea=PRODUCTO&id=$producto->uprod_urb_id";
        ?>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_vendedor" name="frm_vendedor" action="<?php echo $url; ?>&id=<?php echo $get['id']; ?>&tarea=PRODUCTO&acc=actualizar_porcentajes" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">


                        <!--Inicio-->
                        <div id="ContenedorDiv">

                            <!--<div id="CajaInput" style="margin:5px 0px 0px 10px;">-->
                            <table  width="100%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>VENTA POR</th>
                                        <th>Empresa</th>
                                        <th>Fondo Patrocinio</th>
                                        <th>Fondo Huerfanos</th>
                                    </tr>							
                                </thead>
                                <tbody>
                                    <?php
                                    $this->cargar_porcentajes($get['id']);
                                    ?>	
                                </tbody>
                            </table>
                            <!--</div>-->
                        </div>
                        <!--Fin-->

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
            </form>
        </div>
        <?php
    }

    function cargar_porcentajes($prod_id) {
        $conec = new ADO();
        $sql = "select * from urbanizacion_producto where uprod_id='" . $prod_id . "'";

        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();

        $comisiones = array();
        if (!empty($objeto->uprod_porc_comision_credito)) {
            $comisiones = json_decode($objeto->uprod_porc_comision_credito);
        }

        $conec = new ADO();

        $sql = "SELECT * FROM producto_comision WHERE pcom_prod_id='" . $prod_id . "' GROUP BY pcom_key";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();

            $key = $objeto->pcom_key;

            $sql = "select * from producto_comision where pcom_key='$key' AND pcom_tipo='Contado' AND pcom_prod_id='" . $prod_id . "' ORDER BY pcom_id ASC";
            $obj_confcom_contado = FUNCIONES::objeto_bd_sql($sql);

            $sql = "select * from producto_comision where pcom_key='$key' AND pcom_tipo='Credito' AND pcom_prod_id='" . $prod_id . "' ORDER BY pcom_id ASC";
            $obj_confcom_credito = FUNCIONES::objeto_bd_sql($sql);
            ?>
            <tr data-id="<?php echo $objeto->pcom_key; ?>">
                <td  id="edit_comision" data-valor="<?php echo $objeto->pcom_key; ?>" data-index="<?php echo $objeto->pcom_key; ?>"> Nivel <?php echo $objeto->pcom_key + 1 ?> </td>
                <td class="td_<?php echo $objeto->pcom_key; ?>">
                    Contado <input type="text" name="empresa_contado<?php echo $objeto->pcom_key; ?>" id="empresa_contado<?php echo $obj_confcom_contado->pcom_key; ?>" value="<?php echo $obj_confcom_contado->pcom_porc_empresa; ?>" size="5" />
                    Credito <input type="text" name="empresa_credito<?php echo $obj_confcom_credito->pcom_key; ?>" id="empresa_credito<?php echo $obj_confcom_credito->pcom_key; ?>" value="<?php echo $obj_confcom_credito->pcom_porc_empresa; ?>" size="5" />
                </td>
                <td class="td_<?php echo $objeto->pcom_key; ?>">
                    Contado <input type="text" name="patrocinio_contado<?php echo $obj_confcom_contado->pcom_key; ?>" id="patrocinio_contado<?php echo $obj_confcom_contado->pcom_key; ?>" value="<?php echo $obj_confcom_contado->pcom_porc_patrocinio; ?>" size="5" />
                    Credito <input type="text" name="patrocinio_credito<?php echo $obj_confcom_credito->pcom_key; ?>" id="patrocinio_credito<?php echo $obj_confcom_credito->pcom_key; ?>" value="<?php echo $obj_confcom_credito->pcom_porc_patrocinio; ?>" size="5" />
                </td>
                <td class="td_<?php echo $objeto->pcom_key; ?>">
                    Contado <input type="text" name="huerfanos_contado<?php echo $obj_confcom_contado->pcom_key; ?>" id="huerfanos_contado<?php echo $obj_confcom_contado->pcom_key; ?>" value="<?php echo $obj_confcom_contado->pcom_porc_huerfanos; ?>" size="5" />
                    Credito <input type="text" name="huerfanos_credito<?php echo $obj_confcom_credito->pcom_key; ?>" id="huerfanos_credito<?php echo $obj_confcom_credito->pcom_key; ?>" value="<?php echo $obj_confcom_credito->pcom_porc_huerfanos; ?>" size="5" />
                </td>
            </tr>
            <?php
            $conec->siguiente();
        }
    }

    function actualizar_porcentajes($datos) { //CREDITO
        $post = $datos->post;
        $get = $datos->get;
        $conec = new ADO();
        $sql = "select * from urbanizacion_producto where uprod_id='" . $get['id'] . "'";

        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();

        //******************* CREDITO *******************//
        $comisiones = array();
        if (!empty($objeto->uprod_porc_comision_credito)) {
            $comisiones = json_decode($objeto->uprod_porc_comision_credito);

            foreach ($comisiones->array as $key => $comision) {
                $sql = "UPDATE `producto_comision` SET pcom_porc_empresa='" . $_POST['empresa_credito' . $key] . "',pcom_porc_patrocinio='" . $_POST['patrocinio_credito' . $key] . "',pcom_porc_huerfanos='" . $_POST['huerfanos_credito' . $key] . "' WHERE pcom_key='$key' AND pcom_tipo='Credito' AND pcom_prod_id='" . intval($get['id']) . "'";
                $conec->ejecutar($sql);
            }
        }
        //******************* CREDITO *******************//

        unset($comisiones);

        //******************* CONTADO *******************//
        $comisiones = array();
        if (!empty($objeto->uprod_porc_comision_contado)) {
            $comisiones = json_decode($objeto->uprod_porc_comision_contado);

            foreach ($comisiones->array as $key => $comision) {
                $sql = "UPDATE `producto_comision` SET pcom_porc_empresa='" . $_POST['empresa_contado' . $key] . "',pcom_porc_patrocinio='" . $_POST['patrocinio_contado' . $key] . "',pcom_porc_huerfanos='" . $_POST['huerfanos_contado' . $key] . "' WHERE pcom_key='$key' AND pcom_tipo='Contado' AND pcom_prod_id='" . intval($get['id']) . "'";
                $conec->ejecutar($sql);
            }
        }
        //******************* CONTADO *******************//
        $this->mensaje = "Los parametros de Configuracion de Comisiones fueron Actualizadas Correctamente.";
    }

    function form_fondos($datos) {
        $post = $datos->post;
        $get = $datos->get;
        $url = $this->link . '?mod=' . $this->modulo;

//        if ($this->datos_configuracion_comisiones()) {
//            if ($_GET['acc'] == 'actualizar_configuracion_comisiones3')
//                $this->actualizar_configuracion_comisiones3();
//        }
        $conec = new ADO();
        $sql = "select * from urbanizacion_producto where uprod_id='" . $get['id'] . "'";

        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();

        $red = $url . "&tarea=PRODUCTO&id=$objeto->uprod_urb_id";
        $_POST['uprod_vdo_empresa'] = $objeto->uprod_vdo_empresa;
        $_POST['uprod_vdo_fondopatrocinio'] = $objeto->uprod_vdo_fondopatrocinio;
        $_POST['uprod_vdo_fondohuerfanos'] = $objeto->uprod_vdo_fondohuerfanos;
        ?>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_comision3" name="frm_comision3" action="<?php echo $url; ?>&id=<?php echo $get['id']; ?>&tarea=PRODUCTO&acc=actualizar_fondos" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">


                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Empresa</div>
                            <div id="CajaInput">
                                <select name="uprod_vdo_empresa" class="caja_texto">
                                    <option value="">Seleccione</option>
                                    <?php
                                    $fun = NEW FUNCIONES;
                                    $fun->combo("select vdo_id as id,CONCAT(int_nombre,' ',int_apellido) as nombre from vendedor
							inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado'", $_POST['uprod_vdo_empresa']);
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Fondo Patrocinio</div>
                            <div id="CajaInput">
                                <select name="uprod_vdo_fondopatrocinio" class="caja_texto">
                                    <option value="">Seleccione</option>
                                    <?php
                                    $fun = NEW FUNCIONES;
                                    $fun->combo("select vdo_id as id,CONCAT(int_nombre,' ',int_apellido) as nombre from vendedor
							inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado'", $_POST['uprod_vdo_fondopatrocinio']);
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Fondo Huerfanos</div>
                            <div id="CajaInput">
                                <select name="uprod_vdo_fondohuerfanos" class="caja_texto">
                                    <option value="">Seleccione</option>
                                    <?php
                                    $fun = NEW FUNCIONES;
                                    $fun->combo("select vdo_id as id,CONCAT(int_nombre,' ',int_apellido) as nombre from vendedor
							inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado'", $_POST['uprod_vdo_fondohuerfanos']);
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->

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
            </form>
        </div>
        <?php
    }

    function actualizar_fondos($datos) {
        $get = $datos->get;
        $post = $datos->post;

        $this->mensaje = "Los parametros de Configuracion de Comisiones fueron Actualizadas Correctamente.";

        $conec = new ADO();
        $sql = "UPDATE urbanizacion_producto set uprod_vdo_empresa='" . $post['uprod_vdo_empresa'] . "',uprod_vdo_fondopatrocinio='" . $post['uprod_vdo_fondopatrocinio'] . "',uprod_vdo_fondohuerfanos='" . $post['uprod_vdo_fondohuerfanos'] . "' where uprod_id='" . $get['id'] . "'";
        $conec->ejecutar($sql);
    }

}
?>