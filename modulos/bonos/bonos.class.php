<?php

class BONOS extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function BONOS() {
        //permisos
        $this->ele_id = 409;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos		
        $this->num_registros = 14;
        $this->coneccion = new ADO();
        $this->arreglo_campos[0]["nombre"] = "fd_descripcion";
        $this->arreglo_campos[0]["texto"] = "Descripci�n";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 40;
        $this->link = 'gestor.php';
        $this->modulo = 'bonos';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('BONOS');
        $this->usu = new USUARIO();
    }

    function dibujar_busqueda() {
        $vals = FUNCIONES::objeto_bd_sql("SELECT MAX( vdo_venta_inicial )as grande , MIN( vdo_venta_inicial ) as chico
                FROM vendedor
                WHERE vdo_venta_inicial >0");
        ?>
        <script>
            function ejecutar_script(id, tarea) {

                if (tarea == 'VER') {
                    $.post('ajax_comisiones.php', {tarea: 'fond', fd_id: id}, function(resp) {
                        abrir_popup(resp);
                    });
                }

                if (tarea == 'BONOS DETALLE') {

                    var url = 'gestor.php?mod=bonos&tarea=' + tarea + '&id=' + id;
                    console.log(url);
                    location.href = url;
                    return false;

                    var html = '<br />Entre:&nbsp;<input type="number" id="r1" name="r1" value="<?php echo $vals->chico; ?>" />&nbsp;y&nbsp;<input type="number" id="r2" name="r2" value="<?php echo $vals->grande; ?>" />';
                    var script = '';
                    var txt = 'Indique de cuales Afiliados(Nro de Venta) quiere visualizar el reporte:' + html;
                    $.prompt(txt, {
                        buttons: {Aceptar: true, Cancelar: false},
                        callback: function(v, m, f) {

                            if (v) {
                                var url = 'gestor.php?mod=bonos&tarea=' + tarea + '&id=' + id + '&r1=' + f.r1 + '&r2=' + f.r2;
                                console.log(url);
                                location.href = url;
                            }

                        }
                    });

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
        $this->formulario->dibujar_cabecera();
        $this->dibujar_listado();
    }

    function set_opciones() {
        $nun = 0;
        if ($this->verificar_permisos('VER')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'VER';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_search.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER';
//            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }

        if ($this->verificar_permisos('MODIFICAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'MODIFICAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_edit.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'MODIFICAR';
            $nun++;
        }

        if ($this->verificar_permisos('BONOS DETALLE')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'BONOS DETALLE';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/templates.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER BONOS DETALLADO';
//            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }

        if ($this->verificar_permisos('BONOS RESUMEN')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'BONOS RESUMEN';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/mapplus.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER RESUMEN DE COMISIONES';
//            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }
        
        if ($this->verificar_permisos('PARAMETROS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'PARAMETROS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/parametros.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'PARAMETROS VARIOS';
            $nun++;
        }
//
//            if($this->verificar_permisos('ELIMINAR')){
//                $this->arreglo_opciones[$nun]["tarea"]='ELIMINAR';
//                $this->arreglo_opciones[$nun]["imagen"]='images/b_drop.png';
//                $this->arreglo_opciones[$nun]["nombre"]='ELIMINAR';
//                $nun++;
//            }
    }

    function dibujar_listado() {
        $sql = "SELECT cp.pdo_id,ges.ges_descripcion,pdo.pdo_descripcion,cp.pdo_estado,
            cp.pdo_ver_comisiones 
            FROM comision_periodo cp
            inner join con_periodo pdo on(cp.pdo_id=pdo.pdo_id)
            inner join con_gestion ges on(pdo.pdo_ges_id=ges.ges_id)";
//            echo $sql;
        $this->set_sql($sql, ' order by cp.pdo_id desc');
        $this->set_opciones();
        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Gestion</th>
            <th>Periodo</th>
            <th>Estado</th>
            <th>Visible</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>

        <?PHP
    }

    function mostrar_busqueda() {
        $color = array('Abierto' => '#ff9601', 'Cerrado' => '#0356ff');
        $color_visible = array('Si' => '#32cd32', 'No' => '#ff0000');
        
        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();

            $operaciones = array();
            $filtros = array();

            if ($objeto->pdo_estado == 'Cerrado') {
                $operaciones[] = "MODIFICAR";
            }

//            if ($objeto->pdo_html == NULL) {
//                $operaciones[] = "VER";
//            }

            echo '<tr>';
            echo "<td>";
            echo $objeto->ges_descripcion;
            echo "&nbsp;</td>";

            echo "<td>";
            echo $objeto->pdo_descripcion;
            echo "&nbsp;</td>";

            echo "<td>";
            echo "<span style='padding:0 2px;color:#fff;background-color:{$color[$objeto->pdo_estado]}'>$objeto->pdo_estado</span>";
            echo "&nbsp;</td>";
            
            echo "<td style='text-align:center'>";
//            echo $objeto->pdo_ver_comisiones;
            echo "<span style='padding:0 2px;color:#fff;background-color:{$color_visible[$objeto->pdo_ver_comisiones]}'>$objeto->pdo_ver_comisiones</span>";
            echo "&nbsp;</td>";

            echo "<td>";
            echo $this->get_opciones($objeto->pdo_id, "", $operaciones, $filtros);
            echo "</td>";
            echo "</tr>";
            $this->coneccion->siguiente();
        }
    }

    function obtener_cajas($usu_id) {
        $sql = "select * from con_cuenta where cue_ges_id='$_SESSION[ges_id]' and cue_codigo in(select cjadet_cue_id from con_cajero_detalle where cjadet_usu_id='$usu_id')";
//            echo $sql;
        $cajas = FUNCIONES::objetos_bd_sql($sql);
        $txt_cajas = "";
        for ($i = 0; $i < $cajas->get_num_registros(); $i++) {
            $objeto = $cajas->get_objeto();
            if ($i > 0) {
                $txt_cajas.=', ';
            }
            $txt_cajas.=$objeto->cue_descripcion;
            $cajas->siguiente();
        }
        return $txt_cajas;
    }

    function cargar_datos() {
        $conec = new ADO();
        $sql = "select * from comision_periodo cp
            inner join con_periodo pdo on (cp.pdo_id=pdo.pdo_id)
            inner join con_gestion ges on (pdo.pdo_ges_id=ges.ges_id)
            where cp.pdo_id = '" . $_GET['id'] . "'";
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        $_POST['pdo_id'] = $objeto->pdo_id;
        $_POST['gestion'] = $objeto->ges_id;
    }

    function datos() {
        if ($_POST) {
            return true;
        } else {
            return false;
        }
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
        $this->formulario->dibujar_tarea('CAJERO');
        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript">
            function cargar_periodos(id) {
                if (true) {
                    console.log('entrando para ajax');
                    var valores = "tarea=periodos&gestion=" + id;
                    ejecutar_ajax('ajax_comisiones.php', 'periodos', valores, 'POST');
                } else {
                    console.log('no haciendo nada');
                }
            }

            function ver_bonos() {
                var fecha = '<?php echo date('d/m/Y'); ?>';
                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                    var dato = JSON.parse(respuesta);
                    if (dato.response !== "ok") {
                        $.prompt(dato.mensaje);
                    } else {
                        if (!validar_formulario()) {
                            return false;
                        }

                        console.log('mostrando la vista previa de los BONOSs');
                        var par = {};
                        par.tarea = 'bonos';
                        par.recalcular = ($('#recalcular').is(':checked'))? 1: 0;
                        par.vdo_id = $('#vendedor option:selected').val();
                        par.pdo_id = $('#pdo_id option:selected').val();
                        par.agrupado_por = $('#agrupado_por option:selected').val();
                        mostrar_ajax_load();
                        $.post('ajax_comisiones.php', par, function(resp) {
                            ocultar_ajax_load();
                            abrir_popup(resp);
                        });
                    }
                });

            }

            var popup = null;
            function abrir_popup(html) {
                if (popup !== null) {
                    popup.close();
                }
                popup = window.open('about:blank', 'reportes', 'left=100,width=1024,height=1024,top=0,scrollbars=yes');
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

            function ValidarNumero(e) {
                evt = e ? e : event;
                tcl = (window.Event) ? evt.which : evt.keyCode;
                if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                {
                    return false;
                }
                return true;
            }

            function enviar(form) {
                var fecha = '<?php echo date('d/m/Y'); ?>';
                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                    var dato = JSON.parse(respuesta);
                    if (dato.response !== "ok") {
                        $.prompt(dato.mensaje);
                    } else {
                        if (validar_formulario()) {
                            mostrar_ajax_load();
                            form.submit();
                            ocultar_ajax_load();
                        }
                    }
                });


            }

            function set_marca_tmp(marca_tmp) {
                $('#marca_tmp').val(marca_tmp);
            }

            function validar_formulario() {
                if ($('#gestion option:selected').val() === '') {
                    $.prompt("Seleccione la gestion.", {opacity: 0.8});
                    return false;
                }
                if ($('#pdo_id option:selected').val() === '') {
                    $.prompt("Seleccione el periodo.", {opacity: 0.8});
                    return false;
                }
                return true;
            }
        </script>
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
            .add_det{
                cursor: pointer;
            }
        </style>
        <script src="js/util.js"></script>
        <script src="js/chosen.jquery.min.js"></script>
        <link href="css/chosen.min.css" rel="stylesheet"/>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">				  
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">


                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Gesti&oacute;n<span class="flechas1">*</span></div>
                            <div id="CajaInput">
                                <select id="gestion" name="gestion" onChange="javascript:cargar_periodos(this.value);">

                                    <?php
                                    if ($tipo == 'cargar') {
                                        $fun = new FUNCIONES();
                                        $fun->combo("select ges_id as id,ges_descripcion as nombre from con_gestion 
                                                    where ges_eliminado='No' and ges_estado='Abierto' 
                                                    and ges_id='{$_POST[gestion]}'", "");
                                    } else {
                                        ?>
                                        <option value="">Seleccione</option>
                                        <?php
                                        $fun = new FUNCIONES();
                                        $fun->combo("select ges_id as id,ges_descripcion as nombre from con_gestion 
                                                    where ges_eliminado='No' and ges_estado='Abierto'", $_SESSION[ges_id]);
                                        ?>
                                        <script>
            var id = $('#gestion option:selected').val();
            cargar_periodos(id);
                                        </script>    
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Periodo Mensual<span class="flechas1">*</span></div>
                            <div id="CajaInput">
                                <div id="periodos">
                                    <select id="pdo_id" name="pdo_id">
                                        <?php
                                        if ($tipo == 'cargar') {
                                            $fun = new FUNCIONES();
                                            $fun->combo("select pdo_id as id,pdo_descripcion as nombre from con_periodo
                                                    where pdo_eliminado='No'
                                                    and pdo_id='{$_POST[pdo_id]}'", "");
                                        } else {
                                            
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Agrupado por:<span class="flechas1">*</span></div>
                            <div id="CajaInput">

                                <select id="agrupado_por" name="agrupado_por">
                                    <option value="sin_agrupar">Sin Agrupar</option>
                                    <option value="asociado">Por Afiliado</option>
                                </select>

                            </div>
                        </div>
                        <!--Fin-->
                        <input type="hidden" id="marca_tmp" name="marca_tmp" value="" />
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Afiliado</div>
                            <div id="CajaInput">
                                <select style="width:350px;" name="vendedor" id="vendedor" data-placeholder="-- Seleccione --" class="caja_texto">
                                    <option value=""></option>
                                    <?php $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido,' ( ',vdo_venta_inicial,' )') as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               where vdo_estado='Habilitado'
                                               and vgru_nombre='AFILIADOS'"; ?>
                                    <?php $vendedores1 = FUNCIONES::objetos_bd_sql($sql); ?>
                                    <?php for ($i = 0; $i < $vendedores1->get_num_registros(); $i++) { ?>
                                        <?php $objeto = $vendedores1->get_objeto(); ?>
                                        <option value="<?php echo $objeto->id; ?>"><?php echo $objeto->nombre ?></option>
                                        <?php $vendedores1->siguiente(); ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->                        
                        <?php
                        if ($_GET[tarea] == 'MODIFICAR') {
                        ?>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Recalcular<span class="flechas1"></span></div>
                            <div id="CajaInput">                               
                                <input type="checkbox" checked id="recalcular" name="recalcular"/>
                            </div>
                        </div>
                        <!--Fin-->                        
                        <?php
                        }
                        ?>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Calculo Definitivo<span class="flechas1"></span></div>
                            <div id="CajaInput">                               
                                <input type="checkbox" id="calculo_definitivo" name="calculo_definitivo"/>
                            </div>
                        </div>
                        <!--Fin-->                        

                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <input type="button" class="boton" name="vista" value="Vista Previa" onclick="javascript:ver_bonos();">
                            </div>
                        </div>


                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <?php if (!($ver)) { ?>
                                    <input type="button" class="boton" name="guardar" value="Guardar" onclick="enviar(this.form);">
                                    <input type="reset" class="boton" name="" value="Cancelar">
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                <?php } else { ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                <?php } ?>
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script>
            $('#vendedor').chosen({
                allow_single_deselect: true
            });
        </script>

        <?php
    }

    function copiar_temporal($marca_tmp) {
        $sql = "select * from comision_tmp where com_marca_tmp='$marca_tmp'";
        $comisiones = FUNCIONES::lista_bd_sql($sql);
        $resp = new stdClass();

        if ($marca_tmp == '') {
            $resp->exito = FALSE;
            return $resp;
        }

        if (count($comisiones) > 0) {
            $resp->exito = TRUE;
            $sql_ins = "insert into comision (com_ven_id,com_vdo_id,com_monto,com_pagado,com_moneda,
            com_estado,com_fecha_cre,com_usu_id,com_observacion,com_tipo,com_porcentaje,
            com_pdo_id,com_marca_tmp)
            select com_ven_id,com_vdo_id,com_monto,com_pagado,com_moneda,
            com_estado,com_fecha_cre,com_usu_id,com_observacion,com_tipo,com_porcentaje,
            com_pdo_id,com_marca_tmp 
            from comision_tmp where com_marca_tmp='{$marca_tmp}'";
            FUNCIONES::bd_query($sql_ins);
        } else {
            $resp->exito = FALSE;
        }
        return $resp;
    }

    function insertar_tcp() {

//        echo "</pre>";
//        print_r($_POST);
//        echo "</pre>";
//        return FALSE;

        $conec = new ADO();
        $conec->begin_transaccion();

        $verificar = NEW VERIFICAR;

        $parametros[0] = array('pdo_id');
        $parametros[1] = array($_POST[pdo_id]);
        $parametros[2] = array('comision_periodo');

        if (!$verificar->validar($parametros)) {
            $mensaje = 'Ya existe una Generacion de Bonos correspondiente a este Periodo.';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, "", "Error");
            return;
        }

        if (true) {
            require_once("clases/mlm.class.php");
            $conec2 = new ADO();
            $hoy = date("Y-m-d H:i:s");
            $pdo_id = $_POST[pdo_id];

            if (isset($_POST[calculo_definitivo])) {
                $campo = ",pdo_estado";
                $valor = ",'Cerrado'";
            }

            $sql_ins = "insert into comision_periodo(pdo_id,pdo_usu_cre,pdo_fecha_cre $campo)
                values('$pdo_id','$_SESSION[id]','$hoy' $valor)";
            $conec2->ejecutar($sql_ins);
            $vdo_id = $_POST[vendedor];

            $resp = $this->copiar_temporal($_POST[marca_tmp]);

            if (!$resp->exito) {

                $data = array(
                    'pdo_id' => $pdo_id,
                    'origen' => 'agregar',
                    'vdo_id' => $vdo_id
                );

                MLM::generar_bonos($data);
            }

            if (isset($_POST[calculo_definitivo])) {
                require_once('clases/comisiones.class.php');
                // COMISION::provision_masiva($pdo_id);
                
                $this->revisar_ventas_con_oferta($pdo_id);
            }

            $success = $conec->commit();
            if ($success) {
//                $mensaje = 'Bonos Generados Correctamente!!!';

                $this->ver_bonos(
                        array(
                            'pdo_id' => $pdo_id,
                            'vdo_id' => $vdo_id,
                            'agrupado_por' => $_POST[agrupado_por],
                            'origen' => 'agregar'
                        )
                );
//                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=ACCEDER");
            } else {
                echo "<p style='color:red'>{$conec->get_errores()}</p>";
                $mensaje = implode('<br>', $conec->get_errores());
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&meta=$_GET[meta]&data=$_GET[data]", "", "Error");
            }
        } else {
            $mensaje = 'El Usuario ' . $_POST['cja_usu_id'] . ' ya esta registrado como con_cajero!!!';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }
    }

    function revisar_ventas_con_oferta($pdo_id) {

        $periodo = FUNCIONES::atributo_bd_sql("select left(pdo_fecha_fin,7)as campo
            from con_periodo where pdo_id=$pdo_id");

        $sql = "select * from venta_oferta 
            inner join venta on (vof_ven_id= ven_id and vof_of_id=ven_of_id)
            where vof_estado='Pendiente'";
        $ventas = FUNCIONES::lista_bd_sql($sql);
        $arr_afectadas = array();
        foreach ($ventas as $ven) {

            $of_params = json_decode($ven->vof_parametros_mod);
            $of_params_cre = json_decode($ven->vof_parametros_cre);
            $pdos_bir = $of_params_cre->pdos_bir;
            $pdos_bra = $of_params_cre->pdos_bra;
            $bir = $of_params->of_periodos_con_bir * 1;
            $bra = $of_params->of_periodos_sin_bra * 1;
            
//            $restar_bir = true;
//            $restar_bra = true;
            $restar_bir = FALSE;
            $restar_bra = FALSE;

            if (substr($ven->ven_fecha, 0, 7) == $periodo) {

                $sql_cuota = "select * from interno_deuda where ind_tabla='venta'
                and ind_tabla_id=$ven->ven_id and left(ind_fecha_programada,7)='$periodo'
                and ind_num_correlativo>0 and ind_estado in ('Pendiente','Pagado')";
                $cuota = FUNCIONES::objeto_bd_sql($sql_cuota);
                if ($cuota == NULL) {
                    $restar_bra = FALSE;
                }
            }
            
            if (in_array($periodo, $pdos_bir)) {
                $restar_bir = TRUE;
            }
            
            if (in_array($periodo, $pdos_bra)) {
                $restar_bra = TRUE;
            }

            if ($restar_bir) {
                if ($bir > 0) {
                    $bir--;
                }
            }

            if ($restar_bra) {
                if ($bra > 0) {
                    $bra--;
                }
            }

            $ademas = "";
            if ($bir == 0 && $bra == 0) {
                $ademas = " , vof_estado='Ejecutado'";
            }
            
            if ($of_params->of_periodos_sin_bra != $bra || $of_params->of_periodos_con_bir != $bir) {
                
                $new_of = new stdClass();
                $new_of->of_id = "$of_params->of_id";
                $new_of->of_periodos_sin_bra = "$bra";
                $new_of->of_periodos_con_bir = "$bir";
                $of_json = json_encode($new_of);
                
                $sql_upd_of = "update venta_oferta set vof_parametros_mod='$of_json'
                            $ademas
                            where vof_ven_id=$ven->ven_id and vof_of_id=$ven->ven_of_id";
                $arr_afectadas[] = $sql_upd_of;
                FUNCIONES::bd_query($sql_upd_of);
            }                        
        }
        
        if ($_SESSION[id] == 'admin') {
            echo "<p>RESULTADOS DE PERIODO: $periodo</p>";
            if (count($arr_afectadas) > 0) {
                foreach ($arr_afectadas as $value) {
                    echo "$value<br/>";
                }
            } else {
                echo "No vario nada<br/>";
            }
        }
    }

    function obtener_equipo($rango) {
//        $sql = "select concat(int_nombre,' ',int_apellido)as nombre,vran_vdo_id from vendedor_rango 
//            inner join vendedor on(vran_vdo_id=vdo_id)
//            inner join interno on (vdo_int_id=int_id)
//            left join rango on(vran_ran_id=ran_id)
//            where vdo_estado = 'Habilitado'
//            and ran_nombre='$rango'";

        $sql = "select concat(int_nombre,' ',int_apellido)as nombre,vdo_id from vendedor
            inner join interno on (vdo_int_id=int_id)
            left join rango on(vdo_rango_actual=ran_id)
            where vdo_estado = 'Habilitado'
            and ran_nombre='$rango'";
        $diamonds = FUNCIONES::objetos_bd_sql($sql);
        return $diamonds;
    }

    function insertar_bonos($periodo, $gestion) {

        $str_periodo = FUNCIONES::atributo_bd_sql("select pdo_descripcion as campo
                    from con_periodo where pdo_id='$periodo'");
        $str_gestion = FUNCIONES::atributo_bd_sql("select ges_descripcion as campo
                            from con_gestion where ges_id='$gestion'");

        include_once 'modulos/vendedor/vendedor.class.php';
        include_once 'modulos/venta/venta.class.php';
        $sql = "select vdo_id from vendedor where vdo_estado='Habilitado'";
        $conec = new ADO();
        $conec->ejecutar($sql);
        $periodo = FUNCIONES::atributo_bd_sql("select left(pdo.pdo_fecha_inicio,7)as campo 
                                    from con_periodo pdo where pdo.pdo_id=$periodo");
        $cl_vdo = new VENDEDOR();
        $cl_ven = new VENTA();

        $hoy = date("Y-m-d");
        for ($i = 0; $i < $conec->get_num_registros(); $i++) {
            $vdo = $conec->get_objeto();
            $lider = $cl_vdo->es_lider($vdo->vdo_id, $periodo);
            if ($lider) {
                $sql = "INSERT INTO `comision` (`com_ven_id` ,`com_vdo_id` ,
                                        `com_monto` ,`com_moneda` ,
                                        `com_estado` ,`com_fecha_cre`,
                                        `com_porcentaje`,`com_descripcion`,
                                        com_usu_id,com_tipo)
	                    VALUES ('0','{$vdo->vdo_id}',
                                    '{$lider->bono}', '2',
	                            'Pendiente', '$hoy',
                                    '{$lider->porc}','Bono {$lider->rango} $str_gestion-$str_periodo',
                                    '{$this->usu->get_id()}','bono');";

                FUNCIONES::bd_query($sql, FALSE);
                $com_id = mysql_insert_id();
                //PROVISION DEL BONO
                $urb = FUNCIONES::objetos_bd_sql("select * from urbanizacion where urb_id='1'");
                $datos = array(
                    'vendedor' => $vdo->vdo_id,
                    'urb' => $urb,
                    'moneda' => '2',
                    'fecha' => $hoy,
                    'com_id' => $com_id,
                    'com_monto' => $lider->bono,
                    'glosa' => "Provision de Bono {$lider->rango} $str_gestion-$str_periodo"
                );
                $cl_ven->provisionar_comision($datos);
                //PROVISION DEL BONO
            }
            $conec->siguiente();
        }
    }

    function red_limpia_($venta) {
        include_once 'modulos/ven_comisiones/ven_comisiones.class.php';
        $vcom = new VEN_COMISIONES();
        $lista_limpia = array();
        $sql_padre = "select vdo_vendedor_id as campo from vendedor 
                            where vdo_id='$venta->ven_vdo_id'";
        //    echo "$sql_padre<br/>";
        $vendedor_padre = FUNCIONES::atributo_bd_sql($sql_padre);

        while ($vendedor_padre != '' && $vendedor_padre <> 0) {
            //        echo "el vendedor padre sacado:$vendedor_padre, de la venta:$venta->ven_id<br/>";       
            if ($vcom->se_podra_pagar($venta->ven_fecha_firma, $vendedor_padre)) {

                $lista_limpia[] = $vendedor_padre;
            }
            $vendedor_padre = FUNCIONES::atributo_bd_sql("select vdo_vendedor_id as campo from vendedor 
                            where vdo_id='$vendedor_padre'");
        }
        //    echo "saliendo del while y retornando";
        return $lista_limpia;
    }

    function red_limpia($venta) {
        include_once 'modulos/ven_comisiones/ven_comisiones.class.php';
        $vcom = new VEN_COMISIONES();
        $lista_limpia = array();
        $sql_padre = "select * from vendedor 
                            where vdo_id='$venta->ven_vdo_id'";
        if ($venta->ven_id == 5) {
            echo "<br/>El VDO_id: $venta->ven_vdo_id<br/>";
        }
        //    echo "$sql_padre<br/>";
        $vendedor_padre = FUNCIONES::objeto_bd_sql($sql_padre);

        while ($vendedor_padre->vdo_vendedor_id != '' && $vendedor_padre->vdo_vendedor_id <> 0) {
            //        echo "el vendedor padre sacado:$vendedor_padre, de la venta:$venta->ven_id<br/>";       
            if ($venta->ven_id == 5) {
                echo "<br/>El Vendedor_id: $vendedor_padre->vdo_vendedor_id<br/>";
            }
            if ($vcom->se_podra_pagar($venta->ven_fecha_firma, $vendedor_padre->vdo_vendedor_id) || $vendedor_padre->vdo_nivel == 0) {

                $lista_limpia[] = $vendedor_padre->vdo_vendedor_id;
            }
            $vendedor_padre = FUNCIONES::objeto_bd_sql("select * from vendedor 
                            where vdo_id='$vendedor_padre->vdo_vendedor_id'");
        }
        //    echo "saliendo del while y retornando";
        return $lista_limpia;
    }

    function comisiones_padres($datos) {

        include_once 'modulos/venta/venta.class.php';
        $cl_venta = new VENTA();
        $params = (object) $datos;
        $venta = $params->venta;
        $monto_lote = $cl_venta->monto_lote($venta->ven_lot_id);
//        $monto_lote = $venta->ven_monto;        
        $periodo = $params->periodo;

        $sql_comisiones = "select par_cad_comisiones from ad_parametro";
        $config = FUNCIONES::objeto_bd_sql($sql_comisiones);
        $comisiones = json_decode($config->par_cad_comisiones);
        $comisiones = $comisiones->array;

        $lista_items = array();
        $lista_limpia = $this->red_limpia($venta);
        $i = 0;
        foreach ($comisiones as $key => $porcentaje_comision) {



            //        echo "entrando al foreach";
            $i++;
            if ($i == 1) {
                continue;
            }
            if (empty($lista_limpia)) {
                break;
            }
            //        echo "no estaba vacia";
//            $monto_comision = $porcentaje_comision*$venta->ven_monto/100;        
            $monto_comision = $porcentaje_comision * $monto_lote / 100;
            $item = new stdClass();
            $item->venta = $venta->ven_id;
            $item->vendedor = array_shift($lista_limpia);
            $item->monto = $monto_comision;
            $item->moneda = $venta->ven_moneda;
            $item->porcentaje = $porcentaje_comision;

            if ($venta->ven_id == 5) {
                echo "<BR/>VENDEDOR:$item->vendedor<BR/>";
            }

            $lista_items[] = $item;
        }

        return $lista_items;
    }

    function modificar_tcp() {

        if (isset($_POST[calculo_definitivo])) {
            FUNCIONES::eco("si esta enviando...");
        } else {
            FUNCIONES::eco("no esta enviando...");
        }

        if (_ENTORNO == 'DEV') {
            _PRINT::pre($_POST);
        }

//        echo "</pre>";
//        print_r($_POST);
//        echo "</pre>";
//        return FALSE;

        $conec = new ADO();
        $conec->begin_transaccion();

        if (isset($_POST[calculo_definitivo])) {
            $ademas = ",pdo_estado='Cerrado'";
        }
        $ahora = date("Y-m-d H:i:s");
        $sql = "update comision_periodo set pdo_usu_mod='{$_SESSION[id]}',
                pdo_fecha_mod='$ahora' $ademas
                where pdo_id = '$_GET[id]'";
        $conec->ejecutar($sql);
        $pdo_id = $_GET[id];
        $vdo_id = $_POST[vendedor] * 1;
        require_once("clases/mlm.class.php");
        require_once("clases/comisiones.class.php");

        if (isset($_POST[recalcular])) {
            
            COMISION::actualizar_comisiones(array('periodo' => $pdo_id, 'vendedor' => $vdo_id));
            $resp = $this->copiar_temporal($_POST[marca_tmp]);

            if (!$resp->exito) {
                $data = array('pdo_id' => $pdo_id, 'origen' => 'modificar', 'vdo_id' => $vdo_id);
                MLM::generar_bonos($data);
            }
        }

        if (isset($_POST[calculo_definitivo])) {
            require_once('clases/comisiones.class.php');
            // COMISION::provision_masiva($pdo_id);
            $this->revisar_ventas_con_oferta($pdo_id);
        }

        $success = $conec->commit();
        if ($success) {
//            $mensaje = 'Bonos Recalculados Correctamente!!!';
//            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            $this->ver_bonos(
                    array(
                        'pdo_id' => $pdo_id,
                        'vdo_id' => $vdo_id,
                        'agrupado_por' => $_POST[agrupado_por],
                        'origen' => 'modificar'
                    )
            );
        } else {
            echo '<br>' . $conec->get_errores();
            $mensaje = implode('<br>', $conec->get_errores());
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&meta=$_GET[meta]&data=$_GET[data]");
        }
    }

    function formulario_confirmar_eliminacion() {
        $mensaje = 'Esta seguro de eliminar el Cajero?';
        $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'cja_usu_id');
    }

    function eliminar_tcp() {
        $conec = new ADO();
        $sql = "delete from con_cajero where cja_usu_id='" . $_POST['cja_usu_id'] . "'";
        $conec->ejecutar($sql);
        $mensaje = 'Cajero Eliminado Correctamente!!!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function ver_bonos($params) {
        include_once 'clases/mlm.class.php';

        $pdo_id = $params[pdo_id];
        $vdo_id = $params[vdo_id];

        $f_vendedor = ($vdo_id > 0) ? " and com_vdo_id=$vdo_id" : "";

        $com_per = FUNCIONES::objeto_bd_sql("select * from comision_periodo where pdo_id=$pdo_id");
        if ($params[origen] != 'vista_previa') {
            if ($com_per) {
                $fecha_emision = $com_per->pdo_fecha_cre;
            } else {
                $fecha_emision = date('Y-m-d H:i:s');
            }
        } else {
            $fecha_emision = date('Y-m-d H:i:s');
        }

        $date = DateTime::createFromFormat("Y-m-d H:i:s", $fecha_emision);
        $fecha_emision = $date->format("d/m/Y H:i:s");
        $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
            inner join con_gestion on(pdo_ges_id=ges_id)
            where pdo_id=$pdo_id");
        $desc_periodo = strtoupper($periodo->pdo_descripcion) . " - " . strtoupper($periodo->ges_descripcion);

        if ($vdo_id > 0) {
            $sql_com = "select vdo.*,inte.*,b.ran_nombre as rango_alcanzado,a.ran_nombre as rango_actual from vendedor_tmp vdo
               inner join interno inte on(vdo.vdo_int_id=int_id)
               inner join rango a on (vdo.vdo_rango_actual=a.ran_id)
               inner join rango b on (vdo.vdo_rango_alcanzado=b.ran_id)
               where vdo.vdo_id=$vdo_id";

            $afiliado = FUNCIONES::objeto_bd_sql($sql_com);
        }

        $estado = "Pendiente";
        if ($params[origen] != 'vista_previa') {
            if ($params[origen] == 'generar') {
                $estado = "Pagado";
            }
            ob_start();
            $tabla = "comision";
        } else {
            $tabla = ($params[tabla]) ? $params[tabla] : "comision_tmp";
            ?>
            <script>
                window.opener.set_marca_tmp('<?php echo $params[marca_tmp]; ?>');
            </script>
            <?php
        }
        ?>
        <div id="clone" style="display:none;"></div>
        <div id="contenido_reporte" style="clear:both;">
            <script>


                function importar_excel() {
                    var copy = $('#contenido_reporte').clone();

                    $('#clone').html(copy);


                    $('.tablaReporte').attr('border', '1');
                    window.open('data:application/vnd.ms-excel,' + escape($('#clone').html()));
                    e.preventDefault();
                    // window.open('data:application/vnd.ms-excel,' + escape(modificado));
                    // e.preventDefault();
                    $('.tablaReporte').attr('border', '0');
                }
            </script>
            <style>
                .derecha{
                    text-align: right;
                }
            </style>            
            <center>
                <div style="float: left">            
                    <table id="tab_cabecera">
                        <tr>
                            <td colspan="2"><b>REPORTE DE BONIFICACIONES</b></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>&nbsp;</b></td>
                        </tr>
                        <tr>
                            <td><b>Fecha y Hora de Emision:</b></td><td><?php echo $fecha_emision; ?></td>
                        </tr>
                        <tr>
                            <td><b>Periodo:</b></td><td><?php echo $desc_periodo; ?></td>
                        </tr>
                        <?php if ($vdo_id > 0) { ?>
                            <tr>
                                <td><b>Afiliado:</b></td><td><?php echo strtoupper($afiliado->int_nombre . "" . $afiliado->int_apellido); ?></td>
                            </tr>
                            <tr>
                                <td><b>Rango Alcanzado:</b></td><td><?php echo $afiliado->rango_alcanzado; ?></td>
                            </tr>
                            <tr>
                                <td><b>Rango Periodo:</b></td><td><?php echo $afiliado->rango_actual; ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td><b>Agrupado Por:</b></td><td><?php echo ($params[agrupado_por] == 'asociado') ? "Por Afiliado" : "Sin Agrupar"; ?></td>
                        </tr>
                    </table>
                    <style>
                        #tab_cabecera{
                            border: 1px black solid;
                            font-family: arial;
                            font-size: 12px;
                        }

                    </style>   
                </div>
                <div style="clear:both;"></div>
                <br/><br/>
                <?php
                $total_bonos = 0;
                if ($params[agrupado_por] == 'sin_agrupar') {
                    ?>
                    <h2>Bono de Inicio Rapido</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
                        <th>
                            Asociado
                        </th>                        
                        <th>
                            Descripcion
                        </th>
                        <th>
                            Venta
                        </th>
                        <th>
                            Afiliado
                        </th>
                        <th>
                            Inicial(BIR + BVI) USD
                        </th>
                        <th>
                            BIR(USD)
                        </th>
                        <th>
                            %
                        </th>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "select com.*,vdo.*,pat.*,ven.*,ran.ran_nombre,
                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
                        inner join venta ven on (com.com_ven_id=ven.ven_id)
                        inner join interno afil on (ven.ven_int_id=afil.int_id)
                        left join rango ran on (vdo.vdo_rango_actual=ran.ran_id)
                        where com.com_estado='$estado'
                        and com.com_tipo='BIR'
                        $f_vendedor
                        and com.com_pdo_id=$pdo_id";
                            FUNCIONES::eco("BIR => " . $sql);
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            $totalMontoCi = 0;
                            foreach ($comisiones as $com) {
                                $desc_rango = " ($com->ran_nombre - $com->vdo_venta_inicial)";
                                ?>
                                <tr>
                                    <td><?php echo $com->int_nombre . " " . $com->int_apellido . $desc_rango; ?></td>
                                    <td><?php echo $com->com_observacion; ?></td>
                                    <td style="text-align: center"><?php echo $com->com_ven_id; ?></td>
                                    <td><?php echo $com->afiliado; ?></td>
                                    <td class="derecha"><?php echo number_format($com->ven_bono_inicial, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_porcentaje, 2, ".", ","); ?></td>
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
//                            $totalMontoCi += $com->ven_res_anticipo;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5"><b>Totales:</b></td>
                                <!--<td class="derecha"><?php // echo number_format($totalMontoCi, 2, '.', ',')                  ?></td>-->
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfoot>
                    </table>

                    <br/>
                    <br/>

                    <h2>Bono Indirecto de Ventas</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
                        <th>
                            Asociado
                        </th>                        
                        <th>
                            Descripcion
                        </th>
                        <th>
                            Venta
                        </th>
                        <th>
                            Afiliado
                        </th>
                        <th>
                            Inicial(BIR + BVI) USD
                        </th>
                        <th>
                            BIV(USD)
                        </th>
                        <th>
                            %
                        </th>
                        </thead>
                        <tbody>
                            <?php
//                            $sql = "select * from $tabla 
//                        inner join vendedor on (com_vdo_id=vdo_id)
//                        inner join interno on (vdo_int_id=int_id)
//                        inner join venta on (com_ven_id=ven_id)
//                        where com_estado='Pendiente'
//                        and com_tipo='BVI'
//                        and com_pdo_id=$pdo_id";

                            $sql = "select com.*,vdo.*,pat.*,ven.*,ran.ran_nombre,
                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
                        inner join venta ven on (com.com_ven_id=ven.ven_id)
                        inner join interno afil on (ven.ven_int_id=afil.int_id)
                        left join rango ran on (vdo.vdo_rango_actual=ran.ran_id)
                        where com.com_estado='$estado'
                        and com.com_tipo='BVI'
                        $f_vendedor
                        and com.com_pdo_id=$pdo_id";
                            FUNCIONES::eco("BVI => " . $sql);
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            $totalMontoCi = 0;
                            foreach ($comisiones as $com) {
                                $desc_rango = " ($com->ran_nombre - $com->vdo_venta_inicial)";
                                ?>
                                <tr>
                                    <td><?php echo $com->int_nombre . " " . $com->int_apellido . $desc_rango; ?></td>
                                    <td><?php echo $com->com_observacion; ?></td>
                                    <td style="text-align: center"><?php echo $com->com_ven_id; ?></td>
                                    <td><?php echo $com->afiliado; ?></td>
                                    <td class="derecha"><?php echo number_format($com->ven_bono_inicial, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_porcentaje, 2, ".", ","); ?></td>
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                                $totalMontoCi += $com->ven_res_anticipo;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5"><b>Totales:</b></td>
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfoot>
                    </table>

                    <br/>
                    <br/>
                    <h2>Bono Residual Abierto</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
                        <th>
                            Asociado
                        </th>                        
                        <th>
                            Descripcion
                        </th>                        
                        <th>
                            Venta
                        </th>
                        <th>
                            Afiliado
                        </th>
                        <th>
                            BRA TOTAL(USD)
                        </th>
                        <th>
                            BRA(USD)
                        </th>
                        <th>
                            %
                        </th>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "select com.*,vdo.*,pat.*,ven.*,ran.ran_nombre,
                        concat(afil.int_nombre,' ',afil.int_apellido)as afiliado from $tabla com
                        inner join vendedor vdo on (com.com_vdo_id=vdo.vdo_id)
                        inner join interno pat on (vdo.vdo_int_id=pat.int_id)
                        inner join venta ven on (com.com_ven_id=ven.ven_id)
                        inner join interno afil on (ven.ven_int_id=afil.int_id)
                        left join rango ran on (vdo.vdo_rango_actual=ran.ran_id)
                        where com.com_estado='$estado'
                        and com.com_tipo='BRA'
                        $f_vendedor
                        and com.com_pdo_id=$pdo_id";
                            FUNCIONES::eco("BRA => " . $sql);
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            $totalMontoCi = 0;
                            foreach ($comisiones as $com) {
                                $desc_rango = " ($com->ran_nombre - $com->vdo_venta_inicial)";
                                ?>
                                <tr>
                                    <td><?php echo $com->int_nombre . " " . $com->int_apellido . $desc_rango; ?></td>
                                    <td><?php echo $com->com_observacion; ?></td>
                                    <td style="text-align: center"><?php echo $com->com_ven_id; ?></td>
                                    <td><?php echo $com->afiliado; ?></td>
                                    <td class="derecha"><?php echo number_format($com->ven_bono_bra, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_porcentaje, 2, ".", ","); ?></td>
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                                $totalMontoCi += $com->ven_res_anticipo;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5"><b>Totales:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfoot>
                    </table>

                    <br/>
                    <br/>
                    <h2>Fondo Especial Diamante</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
                        <th>
                            Asociado
                        </th>                                                
                        <th>
                            FED(USD)
                        </th>
                        <th>
                            %
                        </th>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "select * from $tabla 
                        inner join vendedor on (com_vdo_id=vdo_id)
                        inner join interno on (vdo_int_id=int_id)                        
                        where com_estado='$estado'
                        and com_tipo='FED'
                        $f_vendedor
                        and com_pdo_id=$pdo_id";
                            FUNCIONES::eco("FED => " . $sql);
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            $totalMontoCi = 0;
                            foreach ($comisiones as $com) {
                                ?>
                                <tr>
                                    <td><?php echo $com->int_nombre . " " . $com->int_apellido; ?></td>                                                                        
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($com->com_porcentaje, 2, ".", ","); ?></td>
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="1"><b>Totales:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfoot>
                    </table>

                    <?php
                }

                if ($params[agrupado_por] == 'asociado') {
                    ?>
                    <h2>Bono de Inicio Rapido</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
                        <th>
                            Asociado
                        </th>

                        <th>
                            BIR(USD)
                        </th>

                        </thead>
                        <tbody>
                            <?php
                            $sql = "select com_vdo_id,concat(int_nombre,' ',int_apellido)as nom_vendedor,
                            sum(com_monto)as com_monto from $tabla
                            inner join vendedor on (com_vdo_id=vdo_id)
                            inner join interno on (vdo_int_id=int_id)
                            where com_tipo='BIR'
                            and com_estado='$estado'
                            $f_vendedor
                            and com_pdo_id=$pdo_id
                            group by com_vdo_id
                            order by com_vdo_id asc";
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            FUNCIONES::eco("BIR => " . $sql);
                            foreach ($comisiones as $com) {
                                ?>
                                <tr>
                                    <td><?php echo $com->nom_vendedor; ?></td>                                                
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>                        
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><b>Totales:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>                        
                            </tr>
                        </tfoot>
                    </table>
                    <br/>
                    <br/>
                    <h2>Bono Indirecto de Ventas</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
                        <th>
                            Asociado
                        </th>

                        <th>
                            BIV(USD)
                        </th>

                        </thead>
                        <tbody>
                            <?php
                            $sql = "select com_vdo_id,concat(int_nombre,' ',int_apellido)as nom_vendedor,
                            sum(com_monto)as com_monto from $tabla
                            inner join vendedor on (com_vdo_id=vdo_id)
                            inner join interno on (vdo_int_id=int_id)
                            where com_tipo='BVI'
                            and com_estado='$estado'
                            $f_vendedor
                            and com_pdo_id=$pdo_id
                            group by com_vdo_id
                            order by com_vdo_id asc";
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            FUNCIONES::eco("BVI => " . $sql);
                            foreach ($comisiones as $com) {
                                ?>
                                <tr>
                                    <td><?php echo $com->nom_vendedor; ?></td>                                                
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>                        
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><b>Totales:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>                        
                            </tr>
                        </tfoot>
                    </table>

                    <br/>
                    <br/>
                    <h2>Bono Residual Abierto</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
                        <th>
                            Asociado
                        </th>

                        <th>
                            BRA(USD)
                        </th>

                        </thead>
                        <tbody>
                            <?php
                            $sql = "select com_vdo_id,concat(int_nombre,' ',int_apellido)as nom_vendedor,
                            sum(com_monto)as com_monto from $tabla
                            inner join vendedor on (com_vdo_id=vdo_id)
                            inner join interno on (vdo_int_id=int_id)
                            where com_tipo='BRA'
                            and com_estado='$estado'
                            $f_vendedor
                            and com_pdo_id=$pdo_id
                            group by com_vdo_id
                            order by com_vdo_id asc";
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            FUNCIONES::eco("BRA => " . $sql);
                            foreach ($comisiones as $com) {
                                ?>
                                <tr>
                                    <td><?php echo $com->nom_vendedor . "({$com->com_vdo_id})"; ?></td>                                                
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>                        
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><b>Totales:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>                        
                            </tr>
                        </tfoot>
                    </table>

                    <br/>
                    <br/>
                    <h2>Fondo Especial Diamante</h2><br/>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
                        <th>
                            Asociado
                        </th>

                        <th>
                            FED(USD)
                        </th>

                        </thead>
                        <tbody>
                            <?php
                            $sql = "select com_vdo_id,concat(int_nombre,' ',int_apellido)as nom_vendedor,
                            sum(com_monto)as com_monto from $tabla
                            inner join vendedor on (com_vdo_id=vdo_id)
                            inner join interno on (vdo_int_id=int_id)
                            where com_tipo='FED'
                            and com_estado='$estado'
                            $f_vendedor
                            and com_pdo_id=$pdo_id
                            group by com_vdo_id
                            order by com_vdo_id asc";
                            $comisiones = FUNCIONES::lista_bd_sql($sql);
                            $totalMonto = 0;
                            FUNCIONES::eco("FED => " . $sql);
                            foreach ($comisiones as $com) {
                                ?>
                                <tr>
                                    <td><?php echo $com->nom_vendedor; ?></td>                                                
                                    <td class="derecha"><?php echo number_format($com->com_monto, 2, ".", ","); ?></td>                        
                                </tr>
                                <?php
                                $totalMonto += $com->com_monto;
                            }
                            $total_bonos += $totalMonto;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><b>Totales:</b></td>                        
                                <td class="derecha"><?php echo number_format($totalMonto, 2, '.', ',') ?></td>                        
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                }
                ?>
                <br/>
                <br/>
                <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                    <tfoot>
                        <tr>
                            <td class="derecha"><b>Total Bonos:</b></td>
                            <td class="derecha"><b><?php echo number_format($total_bonos, 2, '.', ','); ?></b></td>
                        </tr>
                    </tfoot>
                </table>

            </center>
        </div>
        <?php
        if ($params[origen] != 'vista_previa') {
            $body = ob_get_contents();
            $data = base64_encode($body);
            FUNCIONES::bd_query("update comision_periodo set pdo_html='$data' where pdo_id=$pdo_id");
            ob_clean();
            echo $body;
        }
    }

    function ver_bonos2($pdo_id) {
        $cierre = FUNCIONES::objeto_bd_sql("select * from comision_periodo where pdo_id=$pdo_id");
        $this->barra_de_impresion();

        if ($cierre->pdo_html != '' && $cierre->pdo_html != NULL) {
            echo base64_decode($cierre->pdo_html);
        } else {
            $data = array(
                'pdo_id' => $cierre->pdo_id,
                'vdo_id' => 0,
                'agrupado_por' => 'asociado',
                'origen' => 'generar'
            );
            $this->ver_bonos($data);
        }
    }

    function barra_de_impresion() {
        $pagina = "'contenido_reporte'";

        $page = "'about:blank'";

        $extpage = "'reportes'";

        $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";

        $extra1 = "'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
			  </head>
			  <body>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
        $extra1.=" <a href=javascript:imprimir_mayor(); >Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";

        $extra2 = "'</center></body></html>'";

        $myday = setear_fecha(strtotime(date('Y-m-d')));

        echo '	<table align=right border=0>
                    <tr>
                        <td><a onclick="javascript:importar_excel();" href="#" id="importar_excel"><img src="images/excel.png" align="right" border="0" title="EXPORTAR EXCEL"></a></td>
						<td>
                            <a href="javascript:location.reload(true);;">
                                <img src="images/actualizar.png" width="20" title="ACTUALIZAR"/>
                            <a/>
                        </td>
                       <td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=' . $this->modulo . '&tarea=ACCEDER' . '' . '\';"></td></tr></table><br><br>
                  ';
    }

    function ver_bonos_full($pdo_id) {

        require_once("modulos/afiliado/afiliado.class.php");
        require_once("modulos/afiliado/afiliado_historial.class.php");


        $r1 = $_GET['r1'] * 1;
        $r2 = $_GET['r2'] * 1;
        $min = $r1;
        $max = $r2;

        if ($r1 > $r2) {
            $max = $r1;
            $min = $r2;
        }

//        $sql = "select distinct(vdo_id) from vendedor
//        inner join comision on (vdo_id=com_vdo_id)
//        inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
//        inner join venta on (vdo_venta_inicial=ven_id)
//        where vgru_nombre='AFILIADOS'
//        and com_estado in ('Pendiente','Pagado')
//        and com_pdo_id = $pdo_id
//        and vdo_venta_inicial > 0
//        and ven_estado in ('Pendiente','Pagado')
//        order by vdo_venta_inicial asc";

        $sql = "select distinct(vdo_id) from vendedor
        inner join comision on (vdo_id=com_vdo_id)
        inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
        inner join venta on (com_ven_id=ven_id)
        where vgru_nombre='AFILIADOS'
        and com_estado in ('Pendiente','Pagado')
        and com_pdo_id = $pdo_id
        
        and ven_estado in ('Pendiente','Pagado')
        order by vdo_venta_inicial asc";

        if ($_SESSION[id] == 'admin') {
            echo $sql;
        }

        $afiliados = FUNCIONES::lista_bd_sql($sql);

        $af_hist = new AFILIADO_HISTORIAL();
        if (count($afiliados) > 0) {
            $this->barra_de_impresion();
            ?>
            <div id="contenido_reporte" style="clear:both;">
                <?php
                foreach ($afiliados as $afil) {
                    $_GET['id'] = $afil->vdo_id;
                    $_POST['pdo_id'] = $pdo_id;
                    $_POST['agrupado_por'] = 'sin_agrupar';

                    $af_hist->generar_historial($_POST, $_GET, FALSE);
                }
                ?>
            </div>
            <?php
        } else {
            $mensaje = "NO EXISTEN REGISTROS";
            $this->formulario->ventana_volver($mensaje, $this->link . "?mod=bonos&tarea=ACCEDER");
        }
    }

    function resumen_de_comision($pdo_id) {
        $this->barra_de_impresion();
        $sql = "
            select vdo_id,concat('Nodo Raiz')as asociado,'0'as vdo_venta_inicial,
            'NN'as proyecto,'NN'as mz,'NN'as lt from vendedor where vdo_id=81
            union            
            select distinct(vdo_id),
            concat(int_nombre,' ',int_apellido)as asociado,vdo_venta_inicial,
            urb_nombre as proyecto,man_nro as mz,lot_nro as lt
            from vendedor 
            inner join interno on (vdo_int_id=int_id)
            inner join vendedor_grupo on(vdo_vgru_id=vgru_id)
            inner join comision on (vdo_id=com_vdo_id)
            inner join venta on (vdo_venta_inicial=ven_id)
            inner join lote on (ven_lot_id=lot_id)
            inner join manzano on (lot_man_id=man_id)
            inner join urbanizacion on (man_urb_id=urb_id)
            where com_estado in ('Pendiente','Pagado')
            and vgru_nombre='AFILIADOS'
            and com_pdo_id='$pdo_id'
            and com_tipo in ('BIR','BVI','BRA','BEV','FED')";

        $afiliados = FUNCIONES::lista_bd_sql($sql);

        if (count($afiliados) > 0) {
            $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo where pdo_id=$pdo_id");
            ?>
            <style>
                .centro{
                    text-align: center !important;
                }
                .derecha{
                    text-align: right !important;
                }
            </style>         
            <script>


                function importar_excel() {
                    var copy = $('#contenido_reporte').clone();

                    $('#clone').html(copy);


                    $('.tablaReporte').attr('border', '1');
                    window.open('data:application/vnd.ms-excel,' + escape($('#clone').html()));
                    e.preventDefault();
                    // window.open('data:application/vnd.ms-excel,' + escape(modificado));
                    // e.preventDefault();
                    $('.tablaReporte').attr('border', '0');
                }
            </script>   
            <div id="clone" style="display:none;"></div>
            <div id="contenido_reporte" style="clear:both;">
                <h2>RESUMEN DE COMISIONES</h2>
                <br/>
                <h4>Periodo <?php echo strtoupper($periodo->pdo_descripcion) . " - " . substr($periodo->pdo_fecha_inicio,0,4);?></h4>
                <br/><br/>
                <center>
                    <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                        <thead>
                        <th class="asociado">
                            Asociado
                        </th>                        
                        <th>
                            Proyecto
                        </th>
                        <th>
                            Mz
                        </th>
                        <th>
                            Lote
                        </th>
                        <th>
                            BIR
                        </th>                        
                        <th>
                            BVI
                        </th>
                        <th>
                            BRA
                        </th>
                        <th>
                            Total Comision
                        </th>
                        </thead>
                        <tbody>
                            <?php
                            $totales = new stdClass();
                            $totales->bir = 0;
                            $totales->bvi = 0;
                            $totales->bra = 0;
                            $totales->todo = 0;
                            foreach ($afiliados as $afil) {

                                $datos = (object) array(
                                            'tipos' => array('BIR', 'BVI', 'BRA'),
                                            'vendedor' => $afil->vdo_id,
                                            'periodo' => $pdo_id
                                );
                                $totales_bonos = $this->totales_comision_periodo($datos);
                                $total = $totales_bonos->BIR + $totales_bonos->BVI + $totales_bonos->BRA;
                                $totales->todo += $totales_bonos->BIR + $totales_bonos->BVI + $totales_bonos->BRA;
                                $totales->bir += $totales_bonos->BIR;
                                $totales->bvi += $totales_bonos->BVI;
                                $totales->bra += $totales_bonos->BRA;
                                ?>
                                <tr>
                                    <td class="asociado"><?php echo "$afil->asociado  ($afil->vdo_venta_inicial)"; ?></td>
                                    <td><?php echo $afil->proyecto; ?></td>
                                    <td class="centro"><?php echo $afil->mz; ?></td>
                                    <td class="centro"><?php echo $afil->lt; ?></td>
                                    <td class="derecha"><?php echo number_format($totales_bonos->BIR, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($totales_bonos->BVI, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($totales_bonos->BRA, 2, ".", ","); ?></td>
                                    <td class="derecha"><?php echo number_format($total, 2, ".", ","); ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="derecha" colspan="4"><b>Total A Pagar:</b></td>                        
                                <td class="derecha"><?php echo number_format($totales->bir, 2, ".", ","); ?></td>
                                <td class="derecha"><?php echo number_format($totales->bvi, 2, ".", ","); ?></td>
                                <td class="derecha"><?php echo number_format($totales->bra, 2, ".", ","); ?></td>
                                <td class="derecha"><?php echo number_format($totales->todo, 2, ".", ","); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </center>
            </div>
            <?php
        }
    }

    function totales_comision_periodo($datos) {

        $totales = array();
        $arr_tipos = $datos->tipos;

        foreach ($arr_tipos as $tipo) {

            $sql = "select sum(com_monto)as campo from comision 
            where com_tipo='$tipo'
                and com_vdo_id='$datos->vendedor'
                and com_pdo_id='$datos->periodo'
                and com_estado in ('Pendiente','Pagado')";

            $comision = FUNCIONES::atributo_bd_sql($sql) * 1;

            $totales[$tipo] = $comision;
        }

        return (object) $totales;
    }
    
    function parametros($pdo_id){
        
        if ($_POST) {
            $this->modificar_parametros((object)$_GET, (object)$_POST);
        } else {
            $this->frm_parametros($pdo_id);
        }
    }
    
    function frm_parametros($pdo_id){
        
        $this->formulario->dibujar_tarea();
        
        if ($_GET[msj]) {
//            echo $_GET[msj];
//            $this->formulario->mensaje('Correcto', $_GET[msj]);
//            $link = $this->link . "?mod=" . $this->modulo . "&tarea=PARAMETROS&id=" . $pdo_id;
//            header('Location: '. $link);
            $url_reload = $this->link . "?mod=" . $this->modulo . "&tarea=PARAMETROS&id=" . $pdo_id;
            ?>
            <script>
            var txt = '<?php echo $_GET[msj];?>';
            $.prompt(txt, {
                        buttons: {Aceptar: true, Cancelar: false},
                        callback: function(v, m, f) {

                            if (v) {
                                var url = '<?php echo $url_reload;?>';
//                                console.log(url);
                                location.href = url;
                            }

                        }
                    });
            </script>
            <?php
            //                location.href = "";
        }
        
        $sql = "select * from comision_periodo cp
        inner join con_periodo pdo on (cp.pdo_id=pdo.pdo_id)
        inner join con_gestion ges on (pdo.pdo_ges_id=ges.ges_id)
        where cp.pdo_id='$pdo_id'";
        
        $periodo_coms = FUNCIONES::objeto_bd_sql($sql);
        $estado = $periodo_coms->pdo_estado;
        $descripcion = $periodo_coms->pdo_descripcion . " - " . $periodo_coms->ges_descripcion;
        ?>
        
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=$this->modulo&tarea=$_GET[tarea]&id=$_GET[id]"; ?>" method="POST" enctype="multipart/form-data">                
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">         
            <div id="Contenedor_NuevaSentencia">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos del Periodo</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Descripcion</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $descripcion; ?></div>
                            </div>
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Estado</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $estado; ?></div>
                            </div>
                        </div>
                        <?php
                        $fun = new FUNCIONES();
                        ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Ver Comisiones del Periodo</div>
                            <div id="CajaInput">
                                <select id="pdo_ver_comisiones" name="pdo_ver_comisiones">
                                    <?php
                                    $sql_ver = "select 'Si' as id,'Si' as nombre
                                    union 
                                    select 'No' as id,'No' as nombre";
                                    $fun->combo($sql_ver, $periodo_coms->pdo_ver_comisiones);
                                    ?>
                                </select>
                            </div>
                        </div>
                        

                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <input type="button" id="btn_guardar" value="Guardar" class="boton">
                            <input type="button" id="btn_volver" value="Volver" class="boton" onclick="location.href = 'gestor.php?mod=<?php echo $this->modulo; ?>&tarea=ACCEDER';">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php
        if ($_GET[msj]) {

            ?>
            <script>
//                $('.limpiar').fadeOut(3000);

            </script>    
            <?php
        }
        ?>    
        <script>
            
        $('#btn_guardar').click(function() {
            document.frm_sentencia.submit();
        });

        
        </script>
        <?php
    }
    
    function modificar_parametros($get, $post){
        echo "modificando parametros....";
        $sql_upd = "update comision_periodo set pdo_ver_comisiones='$post->pdo_ver_comisiones'
                where pdo_id='$get->id'";
        FUNCIONES::bd_query($sql_upd);
        $mensaje = "Parametros modificados correctamente.";
        $link = $this->link . "?mod=" . $this->modulo . "&tarea=" . $get->tarea . "&id=" . $get->id;
//        $link = $this->link . "?mod=" . $this->modulo . "&tarea=" . $get->tarea . "&id=" . $get->id . "&msj=" . $mensaje;
//        header('Location: '. $link);
        $this->formulario->ventana_volver($mensaje, $link);
    }

}
?>