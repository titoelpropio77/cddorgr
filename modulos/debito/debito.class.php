<?php

class DEBITO extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function DEBITO() {
        //permisos
        $this->ele_id = 413;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos		
        $this->num_registros = 14;
        $this->coneccion = new ADO();
        
        $num = 0;
        $this->arreglo_campos[$num]["nombre"] = "dp_pdo_id";
        $this->arreglo_campos[$num]["texto"] = "Periodo de Debito";
        $this->arreglo_campos[$num]["tipo"] = "combosql";
        $this->arreglo_campos[$num]["sql"] = "select pdo_id as codigo,pdo_descripcion 
        as descripcion from con_periodo inner join debito_periodo on (pdo_id=dp_pdo_id)
        where dp_eliminado='No'";
        
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "dp_descripcion";
        $this->arreglo_campos[$num]["texto"] = "Descripción";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 40;
        
        
        $this->link = 'gestor.php';
        $this->modulo = 'debito';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('DEBITO AUTOMATICO');
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
                
                if (tarea == 'ELIMINAR') {
                    var txt = 'Esta seguro de eliminar el proceso de debitacion automatica.';
                    $.prompt(txt, {
                        buttons: {Aceptar: true, Cancelar: false},
                        callback: function(v, m, f) {

                            if (v) {
                                var url = 'gestor.php?mod=debito&tarea=' + tarea + '&id=' + id;
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
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_search.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'MODIFICAR';
//            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }
        
        if ($this->verificar_permisos('ELIMINAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'ELIMINAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_drop.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ELIMINAR';
            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }
      
    }

    function dibujar_listado() {
        $sql = "SELECT cp.*, ges.ges_descripcion, pdo.pdo_descripcion
        FROM debito_periodo cp
        INNER JOIN con_periodo pdo ON ( cp.dp_pdo_id = pdo.pdo_id ) 
        INNER JOIN con_gestion ges ON ( pdo.pdo_ges_id = ges.ges_id ) 
        where cp.dp_eliminado='No'";
//            echo $sql;
        $this->set_sql($sql, ' order by cp.dp_pdo_id desc');
        $this->set_opciones();
        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Gestion</th>
            <th>Periodo</th>
            <th>Descripcion</th>
            <th>Estado</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>

        <?PHP
    }

    function mostrar_busqueda() {

        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();

            $operaciones = array();
            $filtros = array();

            if ($objeto->dp_estado == 'Cerrado') {
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
            echo $objeto->dp_descripcion;
            echo "&nbsp;</td>";

            echo "<td>";
            echo $objeto->dp_estado;
            echo "&nbsp;</td>";

            echo "<td>";
            echo $this->get_opciones($objeto->dp_id, "", $operaciones, $filtros);
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
                    var valores = "tarea=periodos_debito&gestion=" + id;
                    ejecutar_ajax('ajax_comisiones.php', 'periodos', valores, 'POST');
                } else {
                    console.log('no haciendo nada');
                }
            }

            function ver_debitos() {

                if (!validar_formulario('vista_previa')) {
                    return false;
                }

                console.log('mostrando la vista previa de los DEBITOS');
                var par = {};
                par.tarea = 'debitos';                                                
                par.pdo_id = $('#pdo_id option:selected').val();

                mostrar_ajax_load();
                $.post('ajax_comisiones.php', par, function(resp) {
                    ocultar_ajax_load();
                    abrir_popup(resp);
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
                extra += '</p> </div> </div> <center>';
                
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
                var fecha = $('#fecha_pago').val();
                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                    
                    var dato = JSON.parse(respuesta);
                    if (dato.response !== "ok") {
                        $.prompt(dato.mensaje);
                    } else {
                        if (validar_formulario('')) {
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

            function validar_formulario(origen) {
                if ($('#gestion option:selected').val() === '') {
                    $.prompt("Seleccione la gestion.", {opacity: 0.8});
                    return false;
                }
                
                if ($('#pdo_id option:selected').val() === '') {
                    $.prompt("Seleccione el periodo.", {opacity: 0.8});
                    return false;
                }
                                                
                if (origen != 'vista_previa') {
                    
//                    if ($('#descripcion').val() === '') {
//                        $.prompt("Ingrese una descripcion.", {opacity: 0.8});
//                        return false;
//                    }
                    
                    if ($('#debitos').text() === '') {
                        $.prompt("No existen debitos automaticos para realizar.", {opacity: 0.8});
                        return false;
                    }
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
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
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
                            <div class="Etiqueta">Periodo De Bonificacion<span class="flechas1">*</span></div>
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
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Fecha de Pago<span class="flechas1">*</span></div>
                            <div id="CajaInput">
                                <input type="text" id="fecha_pago" name="fecha_pago" value="<?php echo date('d/m/Y');?>" />
                            </div>
                        </div> 
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Descripcion<span class="flechas1"></span></div>
                            <div id="CajaInput">
                                <input type="text" id="descripcion" name="descripcion" value="" size="50" />
                                
                            </div>
                        </div> 
                                                                                                                                      
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <input type="button" class="boton" name="vista" value="Ver Debitos" onclick="javascript:ver_debitos();">
                            </div>
                        </div>
                        
                        <div id="ContenedorDiv">
                            <center>
                                <table id="lista_debitos" class="tablaReporte">
                                    <thead>
                                    <th>Codigo</th>
                                    <th>Afiliado</th>
                                    <th>Lote</th>
                                    <th>Comision Periodo</th>
                                    <th>Monto Cuota</th>
                                    <th>Monto Debito</th>
                                    <th>Saldo Cuota</th>
                                    <th>Saldo Comision Periodo</th>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                            </center>
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
                <textarea id="debitos" name="debitos" style="display: none;"></textarea>
            </form>
        </div>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script>
            $(document).ready(function(){
                $("#fecha_pago").mask("99/99/9999");
            });
            function recibir_debitos(debitos){
                var s_arr = JSON.stringify(debitos);
                console.log(s_arr);
                $('#debitos').text(s_arr);
                
                var dim_debs = debitos.length;
                console.log('dim => ' + dim_debs);
                
                var html = "";
                $('#lista_debitos tbody').html('');
                for (var i = 0; i < dim_debs; i++) {
                    var deb = debitos[i];
                    html += "<tr>"; 
                    html += "<td>" + deb.ven_id + "</td>";
                    html += "<td>" + deb.afiliado + "</td>";
                    html += "<td>" + deb.terreno + "</td>";
                    html += "<td>" + deb.comision_acumulada + "</td>";
                    html += "<td>" + deb.cuota_mensual + "</td>";
                    html += "<td>" + deb.debito + "</td>";
                    html += "<td>" + deb.saldo_cuota + "</td>";
                    html += "<td>" + deb.saldo_comision_periodo + "</td>";
                    html += "</tr>"; 
                }
                $('#lista_debitos tbody').append(html);
            }
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

//        echo "<pre>GET";
//        print_r($_GET);
//        echo "</pre>";
        
        $_POST[debitos] = stripcslashes($_POST[debitos]);
//        echo "<pre>POST";
//        print_r($_POST);
//        echo "</pre>";
//        return FALSE;

        $conec = new ADO();
        $conec->begin_transaccion();

        $verificar = NEW VERIFICAR;

        $parametros[0] = array('dp_pdo_id','dp_eliminado');
        $parametros[1] = array($_POST[pdo_id],'No');
        $parametros[2] = array('debito_periodo');

        if (!$verificar->validar($parametros)) {
            $mensaje = 'Ya existe una Generacion de Debitos automaticos correspondiente a este Periodo.';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, "", "Error");
            return FALSE;
        }
        
        $debitos = json_decode($_POST[debitos]);
        include_once 'modulos/afiliado/afiliado.class.php';
        include_once 'modulos/afiliado/afiliado_debito.class.php';
        $obj_afil = new AFILIADO_DEBITO();
        
        $observaciones = array();
        
        $ges_periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
        inner join con_gestion on (pdo_ges_id=ges_id)
        where pdo_id='$_POST[pdo_id]'");
        
//        $desc = "Debitacion Automatica - Periodo:$periodo->pdo_descripcion - 
//        Gestion:$periodo->ges_descripcion";
        
//        $desc = $_POST[descripcion];
        $desc = "DEBITO AUTOMATICO: " . $ges_periodo->pdo_descripcion . "-" . substr($ges_periodo->pdo_fecha_fin, 0, 4);
        $hoy = date('Y-m-d H:i:s');
        $fecha_pago = FUNCIONES::get_fecha_mysql($_POST[fecha_pago]);
        
        $sql_ins = "insert into debito_periodo(dp_pdo_id,dp_descripcion,dp_fecha_cre,
        dp_usu_cre,dp_fecha_pago)values('$_POST[pdo_id]','$desc','$hoy','$_SESSION[id]','$fecha_pago')";
        
        $conec->ejecutar($sql_ins, TRUE, TRUE);
        $dp_id = ADO::$insert_id;
        
        foreach ($debitos as $deb) {
            
            $venta = FUNCIONES::objeto_bd_sql("select * from venta where ven_id='$deb->ven_id'");
            $afiliado = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$deb->vdo_id'");
            $fecha_pago=  FUNCIONES::get_fecha_mysql($_POST[fecha_pago]);
            
            $resp = $this->generar_cobro($deb, $venta, $afiliado, $fecha_pago);            
            if (!$resp->ok) {
                $obs = new stdClass();
                $obs->vdo_id = $deb->vdo_id;
                $obs->mensaje = $resp->mensaje;
                $observaciones[] = $obs;
                continue;
            }
            
            $comision_acumulada = $deb->comision_acumulada;
            
            $cobro=  FUNCIONES::objeto_bd_sql("select *  from venta_cobro where 
            vcob_ven_id='$venta->ven_id'");
            
            $resp2 = $obj_afil->pagar_cuota($venta, $afiliado, $cobro, $desc);            
            if (!$resp2->ok) {
                $obs = new stdClass();
                $obs->vdo_id = $deb->vdo_id;
                $obs->mensaje = $resp2->mensaje;
                $observaciones[] = $obs;
                continue;
            }
            
//            echo "<p>paso pagar_cuota</p>";
            $datos3 = (object)array(
                'venta' => $venta,
                'afiliado' => $afiliado,
                'cobro' => $cobro,
                'pag_id' => $resp2->datos,
                'comision_acumulada' => $comision_acumulada,
                'periodo' => $_POST[pdo_id],
                'dp_id' => $dp_id,
                'datos_cmp' => $resp2->datos_cmp,
                'glosa' => $resp2->glosa,
                'fecha_pago' => FUNCIONES::get_fecha_mysql($_POST[fecha_pago]),
            );
            
            $resp3 = $obj_afil->generar_debito($datos3);                    
            if (!$resp3->ok){
                $obs = new stdClass();
                $obs->vdo_id = $deb->vdo_id;
                $obs->mensaje = $resp3->mensaje;
                $observaciones[] = $obs;
                continue;
            }
//            echo "<p>paso generar_debito</p>";
            
        }
        
        $count = count($observaciones);
        
        $dp_estado = ($count > 0) ? 'Abierto' : 'Cerrado';
        $dp_observados = json_encode($observaciones);
        
        $sql_upd = "update debito_periodo set
        dp_observados='$dp_observados',dp_estado='$dp_estado'
        where dp_id='$dp_id'";
        
        $conec->ejecutar($sql_upd);
                                
        if ($count > 0) {
            $this->formulario->ventana_volver("Se realizo la Debitacion automatica 
            pero hubieron algunas observaciones que se detallan a continuacion.", 
            $this->link . '?mod=' . $this->modulo);
            echo "<pre>";
            print_r($observaciones);
            echo "</pre>";
        } else {
            $this->formulario->ventana_volver("Debitacion automatica realizada exitosamente.", 
            $this->link . '?mod=' . $this->modulo);
        }
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
        
        $debitos = FUNCIONES::atributo_bd_sql("select count(deb_id)as campo 
            from debito where deb_dp_id='$_GET[id]' and deb_estado='Activo'")*1;
        
        if ($debitos > 0) {
            $mensaje = 'El Proceso de Debitacion Automatica no puede eliminarse, existen debitos activos de este proceso.';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', 'Error');
            return false;
        }
        
        $conec = new ADO();
        $sql = "update debito_periodo set dp_eliminado='Si' where dp_id='" . $_GET['id'] . "'";
        $conec->ejecutar($sql);
        $mensaje = 'Proceso de Debitacion Automatica Eliminado Correctamente!!!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function ver_debitos($dp_id) {
        $this->barra_de_impresion();
        $debito_periodo = FUNCIONES::objeto_bd_sql("select * from debito_periodo 
        where dp_id='$dp_id'");
        
        $pdo_id = $debito_periodo->dp_pdo_id;
        
        $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
        inner join con_gestion on (pdo_ges_id=ges_id)
        where pdo_id='$pdo_id'");
        
        $sql = "select concat(int_nombre,' ',int_apellido)as afiliado,
        vdo_venta_inicial as codigo_afiliado,ven_concepto as terreno,
        deb_vpag_id,deb_monto,deb_estado
        from debito 
        inner join debito_periodo on(deb_dp_id=dp_id) 
        inner join vendedor on (deb_vdo_id=vdo_id)
        inner join interno on (vdo_int_id=int_id)
        inner join venta on (vdo_venta_inicial=ven_id)
        where dp_id='$dp_id' 
        and dp_pdo_id='$pdo_id' 
        and deb_eliminado='No'";
        
        $lista_debitados = FUNCIONES::lista_bd_sql($sql);        
        $cant = count($lista_debitados);
        
        if ($cant == 0) {
            echo "<h3>No existen debitos automaticos para este periodo.</h3>";
            return false;
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
                <br/><br/><h2>Lista de Debitos Automaticos</h2>
                <h2>Periodo de Comisiones: <?php echo strtoupper($periodo->pdo_descripcion." - " . $periodo->ges_descripcion);?></h2><br/>
                <h3>Fecha de Pago: <?php echo FUNCIONES::get_fecha_latina($debito_periodo->dp_fecha_pago);?></h3><br/>
                <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                    <thead>                                             
                        <th>
                            Cod. Afiliado
                        </th>                        
                        <th>
                            Afiliado
                        </th>
                        <th>
                            Terreno
                        </th>
                        <th>
                            Det. Cuota Mensual
                        </th>
                        <th>
                            Monto Cuota Mensual
                        </th>
                        <th>
                            Monto Pagado Cuota
                        </th>          
                        <th>
                            Estado
                        </th>          
                    </thead>
                    <tbody>
        <?php
        $sum_monto = 0;
        $sum_monto_pagado = 0;
        foreach ($lista_debitados as $deb) {
            
            $pago = FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_id='$deb->deb_vpag_id'");
            $cuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda 
            where ind_id in ($pago->vpag_capital_ids)");
            
            $monto_cuota = $cuota->ind_monto;
            $nro_cuota = "<b>Nro:</b>".$cuota->ind_num_correlativo . " - <b>Fecha Prog:</b>" . FUNCIONES::get_fecha_latina($cuota->ind_fecha_programada);
            $color = ($deb->deb_estado == 'Activo')?'green':'red';
            ?>
                        <tr>
                            <td><?php echo $deb->codigo_afiliado;?></td>
                            <td><?php echo $deb->afiliado;?></td>
                            <td><?php echo $deb->terreno;?></td>
                            <td><?php echo $nro_cuota;?></td>
                            <td class="derecha"><?php echo $monto_cuota;?></td>
                            <td class="derecha"><?php echo $deb->deb_monto;?></td>
                            <td><span style="color:white; background-color: <?php echo $color;?>;"><?php echo $deb->deb_estado;?></span></td>
                        </tr>
            <?php
            $sum_monto += $monto_cuota;
            $sum_monto_pagado += $deb->deb_monto;
        }
        ?>
                        <tr>
                            <td class="derecha" colspan="4"><b>Total:</b></td>
                            <td class="derecha"><b><?php echo number_format($sum_monto, '2', '.', ',');?></b></td>
                            <td class="derecha"><b><?php echo number_format($sum_monto_pagado, '2', '.', ',');?></b></td>
                            <td>&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </center>
        </div>
        <?php
    }
    
    function ver_debitos_($dp_id) {
        
        $debito_periodo = FUNCIONES::objeto_bd_sql("select * from debito_periodo 
        where dp_id='$dp_id'");
        
        $pdo_id = $debito_periodo->dp_pdo_id;
        
        $periodo = FUNCIONES::objeto_bd_sql("select * from con_periodo 
        inner join con_gestion on (pdo_ges_id=ges_id)
        where pdo_id='$pdo_id'");
        
        $sql = "select concat(int_nombre,' ',int_apellido)as afiliado,
        vdo_id,vdo_venta_inicial,sum(com_monto - com_pagado)as comision_mensual,
        ind_fecha_programada,ind_monto,ind_monto_pagado,ind_estado,ind_id from
        vendedor inner join comision on (vdo_id=com_vdo_id)
        inner join comision_periodo on (com_pdo_id=pdo_id)
        inner join interno on (vdo_int_id=int_id)
        inner join interno_deuda on (ind_tabla='venta' and ind_tabla_id=vdo_venta_inicial)
        where pdo_id='$pdo_id' and pdo_estado in('Abierto','Cerrado')
        and com_estado='Pendiente'
        and ind_estado='Pendiente' 
        and left(ind_fecha_programada,7) = '$next_periodo'    
        group by vdo_id
        order by afiliado asc";
    
        $lista_afil = FUNCIONES::lista_bd_sql($sql);
        $cant = count($lista_afil);
        
        if ($cant == 0) {
            echo "<h3>No existen debitos automaticos para este periodo.</h3>";
            return false;
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
                
                <br/><br/><h3>Lista de Debitos Automaticos</h3>
            <h3>Periodo de Comisiones: <?php echo strtoupper($periodo->pdo_descripcion." - " . $periodo->ges_descripcion);?></h3><br/>
            <br/>
            <table width="90%"   class="tablaReporte" id="tprueba" cellpadding="0" cellspacing="0">
                <thead>
                        <th>
                            ID
                        </th>                        
                        <th>
                            Afiliado
                        </th>                        
                        <th>
                            Codigo
                        </th>
                        <th>
                            Lote
                        </th>
                        <th>
                            Comision del Periodo
                        </th>
                        <th>
                            Fecha de Sig. Cuota
                        </th>
                        <th>
                            Monto de Sig. Cuota
                        </th>
                        <th>
                            Monto Debitado
                        </th>
                        <th>
                            Saldo de la Cuota
                        </th>
                        <th>
                            Saldo de Com. de Periodo
                        </th>
                        
                        </thead>
                        <tbody>
                            <?php
                            foreach ($lista_afil as $elem) {
                                $lote = FUNCIONES::get_concepto_corto($elem->vdo_venta_inicial);
                                $monto_cuota = $elem->ind_monto - $elem->ind_monto_pagado;                                
                                $monto_debito = ($monto_cuota <= $elem->comision_mensual) ? $monto_cuota : $elem->comision_mensual;
                                $saldo_cuota = $monto_cuota - $monto_debito;
                                $saldo_com_pdo = $elem->comision_mensual - $monto_debito;
                                
                                if ($elem->comision_mensual == 0) {
                                    continue;
                                }
                                ?>
                            <tr>
                                <td><?php echo $elem->vdo_id;?></td>
                                <td><?php echo $elem->afiliado;?></td>
                                <td><?php echo $elem->vdo_venta_inicial;?></td>
                                <td><?php echo $lote;?></td>
                                <td><?php echo number_format($elem->comision_mensual, 2, '.', ',');?></td>
                                <td><?php echo FUNCIONES::get_fecha_latina($elem->ind_fecha_programada);?></td>
                                <td><?php echo number_format($monto_cuota, 2, '.', ',');?></td>
                                <td>
                                    <input data-ven_id="<?php echo $elem->vdo_venta_inicial;?>" 
                                    data-vdo_id="<?php echo $elem->vdo_id;?>" 
                                    class="montos_debito" type="text" id="debito_<?php echo $elem->vdo_id;?>" name="monto_debito[]" 
                                    value="<?php echo $monto_debito;?>" 
                                    data-debito_maximo="<?php echo $monto_debito;?>" data-ind_id="<?php echo $elem->ind_id;?>" 
                                    data-comision_mensual="<?php echo $elem->comision_mensual;?>"
                                    data-cuota_mensual="<?php echo $monto_cuota;?>"/>
                                    <br>                                    
                                    <p style="display: none; color:red;" id="msj_<?php echo $elem->vdo_id;?>"></p>
                                </td>
                                <td id="saldo_cuota_<?php echo $elem->vdo_id;?>"><?php echo $saldo_cuota;?></td>
                                <td id="saldo_com_pdo_<?php echo $elem->vdo_id;?>"><?php echo $saldo_com_pdo;?></td>
                                
                            </tr>
                                <?php
                            }
                            ?>
                        </tbody>
            </table>
                

            </center>
        </div>
        <?php
        
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
    
    function generar_cobro($obj_debito, $venta, $afiliado, $fecha_pago){
        
        $conec=new ADO();
        $conec->begin_transaccion();
        $resp = new stdClass();
        
        
        $cuota = FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_id='$obj_debito->ind_id'");
//        $afiliado = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$obj_debito->vdo_id'");
        
        $upago=  FUNCIONES::objeto_bd_sql("select * from venta_pago where vpag_estado='activo' 
        and vpag_ven_id=$obj_debito->ven_id 
        order by vpag_id desc limit 1");
        
        if(!$upago){
//            $venta = FUNCIONES::objeto_bd_sql("select ven_monto_efectivo,ven_fecha from venta where ven_id='$obj_debito->ven_id'");
            $upago=new stdClass();
            $upago->vpag_saldo_final = $venta->ven_monto_efectivo;
            $upago->vpag_fecha_valor = $venta->ven_fecha;
            $upago->vpag_fecha_pago = $venta->ven_fecha;
        }
        
        
        $form = 0;
        $envio = 0;
        $mora = 0;
        $monto_debito = $obj_debito->debito;
        
        $interes_ids = '';
        $capital_ids = '';
        $interes_montos = '';
        $capital_montos = '';
        
        if ($monto_debito <= $cuota->ind_interes) {
            $interes = $obj_debito->debito;
            $capital = 0;
            $interes_ids .= $cuota->ind_id;
            $interes_montos .= $interes;
        } else {
            $interes = $cuota->ind_interes;
            $capital = $monto_debito - $interes;
            $interes_ids .= $cuota->ind_id;
            $capital_ids .= $cuota->ind_id;
            $capital_montos .= $capital;
        }
        
        $monto = $capital + $interes + $form + $mora + $envio;
        
        $saldo_inicial = $upago->vpag_saldo_final;
        $saldo_final = $saldo_inicial - $capital;
        
        $codigo = FUNCIONES::fecha_codigo();        
        $fecha_valor=  $fecha_pago;        
        $fecha_cre=date('Y-m-d H:i:s');
        
        $dias = FUNCIONES::diferencia_dias($upago->vpag_fecha_valor, $fecha_valor);
        
        $sql_del = "delete from venta_cobro where vcob_ven_id=$venta->ven_id";
        $conec->ejecutar($sql_del);
                        
        $sql_ins = "insert into venta_cobro(
            vcob_ven_id,vcob_codigo,vcob_fecha_pago,vcob_fecha_valor,vcob_int_id,vcob_moneda,vcob_saldo_inicial,
            vcob_dias_interes,vcob_interes,vcob_capital,vcob_form,vcob_envio,vcob_mora,vcob_monto,vcob_saldo_final,
            vcob_interes_ids,vcob_interes_montos,vcob_capital_ids,vcob_capital_montos,
            vcob_fecha_cre,vcob_usu_cre,vcob_aut,vcob_reformular,
            vcob_fecha_inicio
        )values(
            '$obj_debito->ven_id','$codigo','$fecha_pago','$fecha_valor',
            '$afiliado->vdo_int_id','$cuota->ind_moneda','$saldo_inicial',
            '$dias','$interes','$capital','$form','$envio','$mora','$monto',
            '$saldo_final','$interes_ids','$interes_montos','$capital_ids',
            '$capital_montos','$fecha_cre','$_SESSION[id]','1','0','$fecha_valor'
        )";
        $conec->ejecutar($sql_ins);
        
        $exito = $conec->commit();
        if($exito){
            $mensaje = "Cobro Autorizado Guardado Exitosamente";
            $resp->ok = TRUE;
            $resp->mensaje = $mensaje;
        }else{                            
            $exito = false;
            $mensajes=$conec->get_errores();
            $mensaje = implode('<br>', $mensajes);
            $resp->ok = FALSE;
            $resp->mensaje = $mensaje;
        }
        
        return $resp;
    }
        

}
?>