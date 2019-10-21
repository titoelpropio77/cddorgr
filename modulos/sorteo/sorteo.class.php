<?php

class SORTEO extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function SORTEO() {
        //permisos
        $this->ele_id = 414;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos		
        $this->num_registros = 14;
        $this->coneccion = new ADO();
        $this->arreglo_campos[0]["nombre"] = "sor_descripcion";
        $this->arreglo_campos[0]["texto"] = "Descripción";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 40;
        $this->link = 'gestor.php';
        $this->modulo = 'sorteo';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('SORTEO');
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
                
                if (tarea == 'SORTEO DETALLE') {
                    
                    var html = '<br />Entre:&nbsp;<input type="number" id="r1" name="r1" value="<?php echo $vals->chico;?>" />&nbsp;y&nbsp;<input type="number" id="r2" name="r2" value="<?php echo $vals->grande;?>" />';
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
        if ($this->verificar_permisos('IMPRIMIR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'IMPRIMIR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/printer.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'IMPRIMIR';
//            $this->arreglo_opciones[$nun]["script"] = "ok";
            $nun++;
        }

        if ($this->verificar_permisos('MODIFICAR')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'MODIFICAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/sorteo.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'SORTEAR';
            $nun++;
        }
        
//        if ($this->verificar_permisos('SORTEO DETALLE')) {
//            $this->arreglo_opciones[$nun]["tarea"] = 'SORTEO DETALLE';
//            $this->arreglo_opciones[$nun]["imagen"] = 'images/templates.png';
//            $this->arreglo_opciones[$nun]["nombre"] = 'VER SORTEO DETALLADO';
//            $this->arreglo_opciones[$nun]["script"] = "ok";
//            $nun++;
//        }
//
//            if($this->verificar_permisos('ELIMINAR')){
//                $this->arreglo_opciones[$nun]["tarea"]='ELIMINAR';
//                $this->arreglo_opciones[$nun]["imagen"]='images/b_drop.png';
//                $this->arreglo_opciones[$nun]["nombre"]='ELIMINAR';
//                $nun++;
//            }
        
            if($this->verificar_permisos('PARAMETROS')){
                $this->arreglo_opciones[$nun]["tarea"]='PARAMETROS';
                $this->arreglo_opciones[$nun]["imagen"]='images/parametros.png';
                $this->arreglo_opciones[$nun]["nombre"]='PARAMETROS';
                $nun++;
            }
    }

    function dibujar_listado() {
        $sql = "SELECT * from sorteo";
//            echo $sql;
        $this->set_sql($sql, ' order by sor_id desc');
        $this->set_opciones();
        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Nombre</th>
            <th>Descripcion</th>            
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>

        <?PHP
    }

    function mostrar_busqueda() {

        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();

            $operaciones = array();
            $filtros = array();
            
            echo '<tr>';
            echo "<td>";
            echo $objeto->sor_nombre;
            echo "&nbsp;</td>";

            echo "<td>";
            echo $objeto->sor_descripcion;
            echo "&nbsp;</td>";
            
            echo "<td>";
            echo $this->get_opciones($objeto->sor_id, "", $operaciones, $filtros);
            echo "</td>";
            echo "</tr>";
            $this->coneccion->siguiente();
        }
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
    


    function formulario_tcp($sor_id) {
        
        $titulo = ($_GET[tarea] == 'IMPRIMIR')?'IMPRIMIR CUPONES':'SORTEAR CUPONES';
        
        $this->formulario->dibujar_titulo($titulo);
        
        $sorteo = FUNCIONES::objeto_bd_sql("select * from sorteo where sor_id='$sor_id'");
        $suc_ids = $sorteo->sor_sucursales;
        if ($sorteo === NULL) {
            $this->formulario->mensaje('Error', "No existe el Sorteo.");
            return false;
        }
        
        
        $url = $this->link . '?mod=' . $this->modulo . "&tarea=" . $_GET[tarea] . "&id=" . $_GET[id];
        $red = $url;
                
        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <!--MaskedInput-->
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <!--MaskedInput-->						
        <script type="text/javascript">
            
            var _TAREA = '<?php echo $_GET[tarea];?>';
            function ver_bonos(){
                if (_TAREA == 'IMPRIMIR') {
                    obtener_cupones();
                } else {
                    sortear();
                }
            }
            
            function sortear(){
                var arr_sucs = [];
                var sucs = $('.suc_ids');
                for (var i = 0; i < sucs.length; i++) {
                    arr_sucs.push($(sucs[i]).val());
                }
                var params = {
                    peticion: 'calcular_cupones', 
                    sorteo: '<?php echo $_GET[id];?>',
                    fecha_ini: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val(),
                    sucursales: arr_sucs
                };
                
                $.get('ajax_sorteo.php', params, 
                    function(respuesta) {
                        var resp = JSON.parse(respuesta);
                        var cant = resp.cantidad;
                        var minimo = resp.minimo;
                        var maximo = resp.maximo;
                        
                        if (cant*1 == 0) {
                            $.prompt("No existen cupones para sortear.",{opacity:0.8});
                        } else {
                            
                            var det_sucs = resp.sucursales;
                            
                            var tab_html = '<h2 style="text-align:center;">DETALLE DE CUPONES</h2><br/>';
//                            tab_html += '<h3>Venta:' + ven_id + '&nbsp;&nbsp;Titular:'+titular+'</h3>';
                            tab_html += '<table class="tablaLista">';
                            tab_html += '<thead>';
                            tab_html += '<th>SUCURSAL</th><th>CUPONES</th>';
                            tab_html += '</thead>';
                            tab_html += '<tbody>';

                            var tot_cups = 0;
                            for (var i = 0; i < det_sucs.length; i++) {
                                var elem = det_sucs[i];
                                tab_html += '<tr><td><b>' + elem.nombre + '</b></td><td style=text-align:right>' + elem.cant + '</td></tr>';
                                tot_cups += elem.cant*1;
                            }

                            tab_html += '</tbody>';                                
                            tab_html += '<tfoot>';
                            tab_html += '<tr><td><b>Total:</b></td><td style=text-align:right><b>' + tot_cups + '</b></td></tr>';
                            tab_html += '</tfoot></table><br/>';
                            
                            $.prompt(tab_html + "Se sortearan " + cant + " cupones, desde el cupon " + 
                                    minimo + " al cupon " + maximo + ".",{ 
                                buttons:{Aceptar:true, Cancelar:false},
                                callback: 
                                function(v,m,f){
                                    if(v){
                                        enviar(respuesta);
                                    }
                                }
                            });
                        }
                    }
                );
            }
            
            function obtener_cupones() {
                var arr_sucs = [];
                var sucs = $('.suc_ids');
                for (var i = 0; i < sucs.length; i++) {
                    arr_sucs.push($(sucs[i]).val());
                }
                var params = {
                    peticion: 'calcular_cupones', 
                    sorteo: '<?php echo $_GET[id];?>',
                    fecha_ini: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val(),
                    sucursales: arr_sucs
                };
                
                $.get('ajax_sorteo.php', params, 
                    function(respuesta) {
                        var resp = JSON.parse(respuesta);
                        var cant = resp.cantidad;
                        
                        if (cant*1 == 0) {
                            $.prompt("No existen cupones para imprimir.",{opacity:0.8});
                        } else {
                        
                            var det_sucs = resp.sucursales;
                            
                            var tab_html = '<h2 style="text-align:center;">DETALLE DE CUPONES</h2><br/>';
//                            tab_html += '<h3>Venta:' + ven_id + '&nbsp;&nbsp;Titular:'+titular+'</h3>';
                            tab_html += '<table class="tablaLista">';
                            tab_html += '<thead>';
                            tab_html += '<th>SUCURSAL</th><th>CUPONES</th>';
                            tab_html += '</thead>';
                            tab_html += '<tbody>';

                            var tot_cups = 0;
                            for (var i = 0; i < det_sucs.length; i++) {
                                var elem = det_sucs[i];
                                tab_html += '<tr><td><b>' + elem.nombre + '</b></td><td style=text-align:right>' + elem.cant + '</td></tr>';
                                tot_cups += elem.cant*1;
                            }

                            tab_html += '</tbody>';                                
                            tab_html += '<tfoot>';
                            tab_html += '<tr><td><b>Total:</b></td><td style=text-align:right><b>' + tot_cups + '</b></td></tr>';
                            tab_html += '</tfoot></table><br/>';
                            
                            $.prompt(tab_html + "Se imprimiran " + cant + " cupones, esto puede demorar, aliste el papel necesario, ¿Que desea hacer?",{ 
                                buttons:{Continuar:true, Cancelar:false},
                                callback: 
                                function(v,m,f){
                                    if(v){
                                        imprimir_cupones();
                                    }
                                }
                            });
                        }
                    }
                );
                                
            }
            
            function imprimir_cupones(){
                
                var arr_sucs = [];
                var sucs = $('.suc_ids');
                for (var i = 0; i < $('.suc_ids').length; i++) {
                    arr_sucs.push($(sucs[i]).val());
                }
                var params = {
                    peticion: 'obtener_cupones', 
                    sorteo: '<?php echo $_GET[id];?>',
                    fecha_ini: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val(),
                    sucursales: arr_sucs
                };

                $.get('ajax_sorteo.php', params, 
                    function(respuesta) {

                        console.log('mostrando la vista previa de los cupones');                            
                        mostrar_ajax_load();
                        ocultar_ajax_load();
                        abrir_popup(respuesta);
                    }
                );
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

            function enviar(json_resp) {
                $('#json_detalle').val(json_resp);
                $('#frm_sentencia').submit();
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

                        <?php 
                        if ($_GET[tarea] == 'MODIFICAR') {
                            ?>
                        <p style="font-weight: bold;">Indique a continuacion los cupones que se estableceran como sorteados, los cuales ya no entraran en un siguiente sorteo.</p>
                        <br/>
                            <?php
                        }
                        ?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Sorteo</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $sorteo->sor_nombre; ?></div>
                            </div>
                            
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Descripcion</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $sorteo->sor_descripcion; ?></div>
                            </div>
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Sucursales:</div>
                            <div id="CajaInput">
                                <?php                            
                                
                                $sucursales =  FUNCIONES::lista_bd_sql("select suc_id,suc_nombre from ter_sucursal where suc_id in ($suc_ids)");
                                ?>
                                <select style="min-width: 320px;"  name="suc_id" id="suc_id" class="caja_texto" data-placeholder="-- Seleccione --">
                                    <option value=""></option>
                                    <? foreach ($sucursales as $suc): ?>
                                        <?php $txt_suc = "$suc->suc_nombre";?>
                                        <option value="<?php echo $suc->suc_id?>"><?php echo $txt_suc;?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Lista de Sucursales Participantes:</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta" style="min-width: 320px;"> 
                                    <table id="tab_suc_ids" class="tab_lista_cuentas">
                                        <thead>
                                            <tr>
                                                <th>Sucursales</th>
                                                <th width="8%" class="tOpciones"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if($suc_ids){
                                                $a_sucs =  explode(',', $suc_ids);
                                            }else{
                                                $a_sucs = array();
                                            }
                                            
                                            ?>
                                            <?php 
                                            for ($i = 0; $i < count($a_sucs); $i++) {
                                                $_suc_id = $a_sucs[$i];
//                                                $sql_suc = "select suc_id,suc_nombre,count(cup_id)as cant 
//                                                from ter_sucursal 
//                                                inner join venta on (suc_id=ven_suc_id)
//                                                inner join cupon on (ven_id=cup_ven_id)
//                                                where suc_id='$_suc_id'
//                                                and cup_estado='Activo' 
//                                                and cup_eliminado='No'";
//                                                $suc =  FUNCIONES::objeto_bd_sql($sql_suc);
                                                
                                                $sql_suc = "select suc_id,suc_nombre 
                                                from ter_sucursal where suc_id='$_suc_id'";
                                                $suc =  FUNCIONES::objeto_bd_sql($sql_suc);
                                             ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="suc_ids[]" class="suc_ids" value="<?php echo $suc->suc_id;?>">
                                                    <?php echo "$suc->suc_nombre";?>
                                                    <?php // echo "$suc->suc_nombre ($suc->cant cupones)";?>
                                                </td>
                                                <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>
                                            </tr>
                                            <?php                                             
                                            }
                                            ?>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                        
                        <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Fecha Inicio</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fecha_inicio" id="fecha_inicio" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_fecha_inicio();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                    <span class="flechas1">(DD/MM/AAAA)</span>
                                </div>		
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Fecha Fin</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="fecha_fin" id="fecha_fin" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_fecha_fin();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                    <span class="flechas1">(DD/MM/AAAA)</span>
                                </div>		
                            </div>
                            <!--Fin-->
                        <input type="hidden" id="json_detalle" name="json_detalle" value="" />
                        

                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <input type="button" class="boton" name="vista" value="Vista Previa" onclick="javascript:ver_bonos();">
                            </div>
                        </div>


                    </div>
                    <div id="ContenedorDiv" hidden="">
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
            jQuery(function($) {
                $("#fecha_inicio").mask("99/99/9999");
                $("#fecha_fin").mask("99/99/9999");
            });
            
            $('#suc_id').chosen({
                allow_single_deselect:true
            }).change(function(){
//                                console.log($(this).val());
                var id=trim($('#suc_id option:selected').val());
                var texto=trim($('#suc_id option:selected').text());

                agregar_sucursal({id:id, texto:texto},'suc_ids');
                $(this).val('');
                $('#suc_id option:[value=""]').attr('selected','true');
                $('#suc_id').trigger('chosen:updated');
            });

            function agregar_sucursal(user,input) {
                console.log(user);
                if (!$('.suc_ids[value='+user.id+']').length) {

                    var fila='';
                    fila += '<tr>';
                    fila += '   <td>';
                    fila += '       <input type="hidden" name="suc_ids[]" class="suc_ids" value="'+user.id+'">';
                    fila += '       ' + user.texto;
                    fila += '   </td>';
                    fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                    fila += '</tr>';

                    $("#tab_"+input+' tbody').append(fila);                                
                }
            }
            
            $(".img_del_cuenta").live('click', function() {
                $(this).parent().parent().remove();
            });
        </script>

        <?php
    }
    
    function insertar_tcp(){}

    function modificar_tcp() {

        $det = json_decode(stripslashes($_POST[json_detalle]));
        
//        echo "</pre>GET";
//        print_r($_GET);
//        echo "</pre>";
//        
//        echo "</pre>POST";
//        print_r($_POST);
//        echo "</pre>";
//        return FALSE;

        $conec = new ADO();
        $conec->begin_transaccion();

        
        $ahora = date("Y-m-d H:i:s");
        $ini = FUNCIONES::get_fecha_mysql($_POST[fecha_inicio]);
        $fin = FUNCIONES::get_fecha_mysql($_POST[fecha_fin]);
        
        $sql = "insert into sorteo_detalle(
            sdet_fecha_ini,sdet_fecha_fin,sdet_cantidad,sdet_fecha_cre,
            sdet_usu_cre,sdet_primer_cupon,sdet_ultimo_cupon,sdet_sor_id
        )values(
            '$ini','$fin','$det->cantidad','$ahora',
            '{$_SESSION[id]}','$det->minimo','$det->maximo','$_GET[id]'
        )";
        $conec->ejecutar($sql);
        
        $sql_upd = "update cupon set cup_estado='Sorteado' where cup_id in ($det->cupones)";
        $conec->ejecutar($sql_upd);
        
        $success = $conec->commit();
        if ($success) {
            $mensaje = 'Cupones sorteados correctamente!!!';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            
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

    function frm_parametros($get, $post) {
        
        $titulo = ($_GET[tarea] == 'IMPRIMIR')?'IMPRIMIR CUPONES':'SORTEAR CUPONES';
        
        $this->formulario->dibujar_titulo($titulo);
        
        $sorteo = FUNCIONES::objeto_bd_sql("select * from sorteo where sor_id='$get->id'");        
        if ($sorteo === NULL) {
            $this->formulario->mensaje('Error', "No existe el Sorteo.");
            return false;
        }
        
        
        $url = $this->link . '?mod=' . $this->modulo . "&tarea=" . $_GET[tarea] . "&id=" . $_GET[id];
//        $red = $url;
        
        $url_volver = $this->link . '?mod=' . $this->modulo;
                
        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        
        $suc_ids = $sorteo->sor_sucursales;
        ?>
        <script type="text/javascript" src="js/util.js"></script>
        <!--MaskedInput-->
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <!--MaskedInput-->						
        <script type="text/javascript">
            
            var _TAREA = '<?php echo $_GET[tarea];?>';
            function ver_bonos(){
                if (_TAREA == 'IMPRIMIR') {
                    obtener_cupones();
                } else {
                    sortear();
                }
            }
            
            function sortear(){
                var params = {
                    peticion: 'calcular_cupones', 
                    sorteo: '<?php echo $_GET[id];?>',
                    fecha_ini: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val()
                };
                
                $.get('ajax_sorteo.php', params, 
                    function(respuesta) {
                        var resp = JSON.parse(respuesta);
                        var cant = resp.cantidad;
                        var minimo = resp.minimo;
                        var maximo = resp.maximo;
                        
                        if (cant*1 == 0) {
                            $.prompt("No existen cupones para sortear.",{opacity:0.8});
                        } else {
                            
                            $.prompt("Se sortearan " + cant + " cupones, desde el cupon " + 
                                    minimo + " al cupon " + maximo + ".",{ 
                                buttons:{Aceptar:true},
                                callback: 
                                function(v,m,f){
                                    if(v){
                                        enviar(respuesta);
                                    }
                                }
                            });
                        }
                    }
                );
            }
            
            function obtener_cupones() {
                
                var params = {
                    peticion: 'calcular_cupones', 
                    sorteo: '<?php echo $_GET[id];?>',
                    fecha_ini: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val()
                };
                
                $.get('ajax_sorteo.php', params, 
                    function(respuesta) {
                        var resp = JSON.parse(respuesta);
                        var cant = resp.cantidad;
                        
                        if (cant*1 == 0) {
                            $.prompt("No existen cupones para imprimir.",{opacity:0.8});
                        } else {
                        
                            $.prompt("Se imprimiran " + cant + " cupones, esto puede demorar, aliste el papel necesario, ¿Que desea hacer?",{ 
                                buttons:{Continuar:true, Cancelar:false},
                                callback: 
                                function(v,m,f){
                                    if(v){
                                        imprimir_cupones();
                                    }
                                }
                            });
                        }
                    }
                );
                                
            }
            
            function imprimir_cupones(){
                var params = {
                    peticion: 'obtener_cupones', 
                    sorteo: '<?php echo $_GET[id];?>',
                    fecha_ini: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val()
                };

                $.get('ajax_sorteo.php', params, 
                    function(respuesta) {

                        console.log('mostrando la vista previa de los cupones');                            
                        mostrar_ajax_load();
                        ocultar_ajax_load();
                        abrir_popup(respuesta);
                    }
                );
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

            function enviar() {
                
                var filas = $('#tab_suc_ids tbody tr').length;
                console.log('filas => ' + filas);
                
                // if (filas == 0) {
				if (false) {
                    $.prompt('Debe ingresar al menos 1 sucursal.');
                    return false;
                }
                console.log("enviando el formulario...");
                $('#frm_sentencia').submit();
                
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

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Sucursales:</div>
                            <div id="CajaInput">
                                <?php                            
                                
                                $sucursales =  FUNCIONES::lista_bd_sql("select suc_id,suc_nombre from ter_sucursal");
                                ?>
                                <select style="min-width: 320px;"  name="suc_id" id="suc_id" class="caja_texto" data-placeholder="-- Seleccione --">
                                    <option value=""></option>
                                    <? foreach ($sucursales as $suc): ?>
                                        <?php $txt_suc = "$suc->suc_nombre ($suc->suc_id)";?>
                                        <option value="<?php echo $suc->suc_id?>"><?php echo $txt_suc;?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Lista de Sucursales Participantes:</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta" style="min-width: 320px;"> 
                                    <table id="tab_suc_ids" class="tab_lista_cuentas">
                                        <thead>
                                            <tr>
                                                <th>Sucursales</th>
                                                <th width="8%" class="tOpciones"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if($suc_ids){
                                                $a_sucs =  explode(',', $suc_ids);
                                            }else{
                                                $a_sucs = array();
                                            }
                                            
                                            ?>
                                            <?php 
                                            for ($i = 0; $i < count($a_sucs); $i++) {
                                                $_suc_id = $a_sucs[$i];
                                                
//                                                $sql_suc = "select suc_id,suc_nombre,count(cup_id)as cant 
//                                                from ter_sucursal 
//                                                inner join venta on (suc_id=ven_suc_id)
//                                                inner join cupon on (ven_id=cup_ven_id)
//                                                where suc_id='$_suc_id'
//                                                and cup_estado='Activo' 
//                                                and cup_eliminado='No'";
                                                
                                                $sql_suc = "select suc_id,suc_nombre 
                                                from ter_sucursal where suc_id='$_suc_id'";
                                                
                                                $suc =  FUNCIONES::objeto_bd_sql($sql_suc);
                                             ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="suc_ids[]" class="suc_ids" value="<?php echo $suc->suc_id;?>">
                                                    <?php 
                                                    echo "$suc->suc_nombre ($suc->suc_id)";
//                                                    echo "$suc->suc_nombre ($suc->cant cupones)";
                                                    ?>
                                                </td>
                                                <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>
                                            </tr>
                                            <?php                                             
                                            }
                                            ?>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                        <script>
                            //tab_cuentas_fmod_user
                            $('#suc_id').chosen({
                                allow_single_deselect:true
                            }).change(function(){
//                                console.log($(this).val());
                                var id=trim($('#suc_id option:selected').val());
                                var texto=trim($('#suc_id option:selected').text());
                                
                                agregar_sucursal({id:id, texto:texto},'suc_ids');
                                $(this).val('');
                                $('#suc_id option:[value=""]').attr('selected','true');
                                $('#suc_id').trigger('chosen:updated');
                            });
                            
                            function agregar_sucursal(user,input) {
                                console.log(user);
                                if (!$('.suc_ids[value='+user.id+']').length) {
                                    
                                    var fila='';
                                    fila += '<tr>';
                                    fila += '   <td>';
                                    fila += '       <input type="hidden" name="suc_ids[]" class="suc_ids" value="'+user.id+'">';
                                    fila += '       ' + user.texto;
                                    fila += '   </td>';
                                    fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                    fila += '</tr>';
                                    
                                    $("#tab_"+input+' tbody').append(fila);                                
                                }
                            }
                            
                            $(".img_del_cuenta").live('click', function() {
                                $(this).parent().parent().remove();
                            });
                        </script>
                        
                    </div>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <?php if (TRUE) { ?>
                                    <input type="button" class="boton" name="guardar" value="Guardar" onclick="enviar();">
                                    <input type="reset" class="boton" name="" value="Cancelar">
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $url_volver; ?>';">
                                <?php } else { ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $url_volver; ?>';">
                                <?php } ?>
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script>
            
        </script>

        <?php
    }
    
    function actualizar_parametros($get, $post){
        $get = (object)$get;
//        echo "<pre>GET";
//        print_r($get);
//        echo "</pre>";
//        
//        echo "<pre>POST";
//        print_r($post);
//        echo "</pre>";
//        exit();
//        
        $sucursales = implode(',', $post[suc_ids]);
        
        $sql_upd = "update sorteo set sor_sucursales='$sucursales' where sor_id='$get->id'";
        FUNCIONES::bd_query($sql_upd);
        
        $mensaje = 'Parametros modificacos Correctamente!!!';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }
    
    

}
?>