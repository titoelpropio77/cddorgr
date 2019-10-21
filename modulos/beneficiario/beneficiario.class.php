<?php

class beneficiario extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function beneficiario() {  //permisos
        $this->ele_id = 214;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();

        
        $num=0;
        $this->arreglo_campos[$num]["nombre"] = "int_nombre_apellido";
        $this->arreglo_campos[$num]["campo_compuesto"] = "concat(int_nombre,' ',int_apellido)";
        $this->arreglo_campos[$num]["texto"] = "Nombre completo";
        $this->arreglo_campos[$num]["tipo"] = "compuesto";
        $this->arreglo_campos[$num]["tamanio"] = 40;

        $this->link = 'gestor.php';

        $this->modulo = 'beneficiario';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('BENEFICIARIO');

        $this->usu = new USUARIO;
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
        if ($this->verificar_permisos('VALE')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'VALE';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/vale.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VALES';
            $nun++;
        }
    }

    function dibujar_listado() {
        $sql = "select  
				*
			  from 
				beneficiario, interno
                            where ben_eliminado='No' and int_id=ben_int_id";
        $this->set_sql($sql, '');

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Nombre</th>		
            <th>Estado</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        
        for ($i = 0; $i < $this->numero; $i++) {

            $objeto = $this->coneccion->get_objeto();
            echo '<tr>';

            echo "<td>";
            echo "$objeto->int_nombre $objeto->int_apellido";
            echo "&nbsp;";
            echo "</td>";
            echo "<td>";
            echo $objeto->ben_estado;
            echo "&nbsp;";
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->ben_id);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from beneficiario ,interno
				where ben_id = '{$_GET['id']}' and int_id=ben_int_id";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['ben_id'] = $objeto->ben_id;

        $_POST['int_id'] = $objeto->ben_int_id;
        $_POST['txt_int_nombre'] = "$objeto->int_nombre $objeto->int_apellido";
        $_POST['ben_estado'] = $objeto->ben_estado;
    }

    function datos() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            if($_GET[tarea]=='AGREGAR'){
                $valores[$num]["etiqueta"] = "Persona";
                $valores[$num]["valor"] = $_POST['int_id'];
                $valores[$num]["tipo"] = "todo";
                $valores[$num]["requerido"] = true;
                $num++;
            }
            
            $valores[$num]["etiqueta"] = "Estado";
            $valores[$num]["valor"] = $_POST['ben_estado'];
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

        $red = $url;

        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }

        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }
        $page = "'gestor.php?mod=beneficiario&tarea=AGREGAR&acc=Emergente'";
        $extpage = "'persona'";
        $features = "'left=325,width=600,top=200,height=420,scrollbars=yes'";

        $this->formulario->dibujar_tarea('USUARIO');

        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <script>
            function set_valor_interno(data){
                document.frm_sentencia.int_id.value = data.id;
                document.frm_sentencia.txt_int_nombre.value = data.nombre;
            }
            function reset_interno()
            {
                document.frm_sentencia.int_id.value = "";
                document.frm_sentencia.txt_int_nombre.value = "";
            }
        </script>

        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
                            <div id="CajaInput">
                                <?php if($_GET[tarea]=='AGREGAR'){?>
                                    <input name="int_id" id="int_id" type="hidden" readonly="readonly" class="caja_texto" value="<?php echo $_POST['int_id'] ?>" size="2">
                                    <input name="txt_int_nombre" readonly="readonly" id="txt_int_nombre"  type="text" class="caja_texto" value="<?php echo $_POST['txt_int_nombre'] ?>" size="25">
                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                        <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                    </a>
                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;" data-url="gestor.php?mod=interno&tarea=ACCEDER&acc=buscar" href="javascript:void(0)">
                                        <img src="images/b_search.png" border="0" title="BUSCAR" alt="BUSCAR">
                                    </a>
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_interno();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                <?php }else{ ?>
                                    <input name="txt_int_nombre" id="txt_int_nombre"  type="hidden" value="<?php echo $_POST['txt_int_nombre'] ?>" >
                                    <div class="read-input"><?php echo $_POST['txt_int_nombre']; ?></div>
                                <?php } ?>
                            </div>							   							   								
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Estado</div>
                            <div id="CajaInput">
                                <select name="ben_estado" class="caja_texto">
                                    <option value="" >Seleccione</option>
                                    <option value="Habilitado" <?php if ($_POST['ben_estado'] == 'Habilitado') echo 'selected="selected"'; ?>>Habilitado</option>
                                    <option value="Deshabilitado" <?php if ($_POST['ben_estado'] == 'Deshabilitado') echo 'selected="selected"'; ?>>Deshabilitado</option>
                                </select>
                            </div>
                        </div>
                    </div>



                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
        <?php
        if (!($ver)) {
            ?>
                                        <!--<input type="submit" class="boton" name="" value="Guardar">-->
                                    <input type="submit" class="boton" name="" value="Guardar" >
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
        <?php if (!($ver || $cargar)) { ?>
            
                                <?php } ?>
        
        <?php
    }

    function insertar_tcp() {
        $ben=  FUNCIONES::objeto_bd_sql("select * from beneficiario where ben_int_id='$_POST[int_id]' and ben_eliminado='No'");
        if($ben){
            $mensaje = 'Ya existe el Beneficiario';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo,'','Error');
            return;
        }

        $conec = new ADO();
        $sql = "insert into beneficiario (ben_int_id,ben_estado,ben_eliminado)
            values ('" . $_POST['int_id'] . "','" . $_POST['ben_estado'] . "','No')";
        //echo $sql.'<br>';
        $conec->ejecutar($sql);
        $mensaje = 'Benficiario Agregado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function modificar_tcp() {
        $conec = new ADO();
        $sql = "update beneficiario set 
                ben_estado='" . $_POST['ben_estado'] . "'
                where ben_id='" . $_GET['id'] . "'";
//        echo $sql;	
        $conec->ejecutar($sql);
        $mensaje = 'Benficiario Modificado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function formulario_confirmar_eliminacion() {
        $mensaje = 'Esta seguro de eliminar el Benficiario?';
        $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'ben_id');
    }

    function eliminar_tcp() {
//                $cantidad = FUNCIONES::atributo_bd("ad_venta", "usu_ben_id='" . $_POST['ben_id'] . "'", 'count(*)');
////            echo$cantidad."<br>";
//                if ($cantidad == 0) {
////                echo "elimino";
////                return;
//                    $conec = new ADO();
//                    $sql = "update beneficiario set ben_eliminado='si' where ben_id='" . $_POST['ben_id'] . "'";
//                    $conec->ejecutar($sql);
//                    $mensaje = 'Benficiario Eliminado Correctamente.';
//                } else {
//                    $mensaje = 'El Benficiario no puede ser eliminado, por que ya fue referenciado en algunos comprobantes.';
//                }
                $mensaje = 'Benficiario Eliminado Correctamente.';
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function descuentos(){
        $ben_id=$_GET[id];
        $benef=  FUNCIONES::objeto_bd_sql("select * from beneficiario where ben_id=$ben_id");
        if($_GET[acc]=='nuevo'){
            if($_POST){
                $this->guardar_descuentos($benef);
            }else{
                $this->frm_descuentos($benef);
            }
        }elseif($_GET[acc]=='anular'){
            $this->anular_descuentos($benef);
        }else{
            $this->resumen_descuentos($benef);
        }
    }
    
    function anular_descuentos($benef){
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
        
        $conec=new ADO();
        $val_id=$_POST[val_id];
        $saldo =  FUNCIONES::atributo_bd_sql("select sum(val_monto) as campo from beneficiario_vale where val_id!='$val_id' and val_int_id=$benef->ben_int_id and val_estado='Activo'");
        if($saldo<0){
            $mensaje = 'El Vale no se puede eliminar por que parte de el ya fue consumido';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo."&tarea=VALE&id=$benef->ben_id",'','Error');
            return;
        }
//        echo "ok";
//        return;
        
        if($val_id>0){
            $observacion_anu=$_POST[observacion_anu];
            $fecha_anu=date('Y-m-d H:i:s');

            $sql_up="update beneficiario_vale 
                        set 
                            val_usu_anu='$_SESSION[id]', val_observacion_anu='$observacion_anu', val_fecha_anu='$fecha_anu', val_estado='Anulado' 
                        where 
                            val_id='$val_id'";
//            echo "$sql_up";
            $conec->ejecutar($sql_up);
            $mensaje = 'Vale Eliminado Correctamente';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo."&tarea=VALE&id=$benef->ben_id",'','Correcto');
        }else{
            $this->resumen_descuentos($benef);
        }
    }
    
    function frm_descuentos($benef){
        $this->formulario->dibujar_titulo('AGREGAR VALE');
        ?>
        <script src="js/util.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=beneficiario&tarea=VALE&acc=nuevo<?php echo '&id='.$_GET['id']; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">
                    <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">    
                    <div class="Subtitulo">Pagar Vendedor</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Fecha:</div>
                            <div id="CajaInput">
                                <?php echo FORMULARIO::cmp_fecha('fecha');?>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Monto:</div>
                            <div id="CajaInput">
                                <input type="text" name="monto" id="monto" value="">
                                <input type="hidden" name="moneda" id="moneda" value="2">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Descripcion:</div>
                            <div id="CajaInput">
                                <textarea name="descripcion" id="descripcion"></textarea>
                            </div>
                        </div>
                        <input type="hidden" name="tipo" id="tipo" value="MEN">
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="button" class="boton" id="btn_guardar" value="Guardar" >
                                    <input type="button" class="boton" id="btn_volver" value="volver" >
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script>
            mask_decimal('#monto');
            $('#btn_volver').click(function (){
                location.href='gestor.php?mod=beneficiario&tarea=VALE&id=<?php echo $benef->ben_id;?>';
            });
            $('#btn_guardar').click(function (){
                var monto=$('#monto').val()*1;
                if(monto<0){
                    $.prompt('Ingrese un Monto mayor a Cero');
                    return false;
                }
                document.frm_sentencia.submit();
            });
        </script>
        <?php
    }
    
    function guardar_descuentos($benef){
        
        $conec=new ADO();
        $monto=$_POST[monto];
        $moneda=$_POST[moneda];
        $descripcion=$_POST[descripcion];
        $fecha=  FUNCIONES::get_fecha_mysql($_POST[fecha]);
        $tipo=  $_POST[tipo];
        $fecha_cre=date('Y-m-d H:i:s');
        $sql_ins="insert into beneficiario_vale(
                        val_int_id,val_monto,val_moneda,val_fecha,val_descripcion,
                        val_tipo,val_estado,val_usu_cre,val_fecha_cre
                    )values(
                        '$benef->ben_int_id','$monto','$moneda','$fecha','$descripcion',
                        '$tipo','Activo','$_SESSION[id]','$fecha_cre'
                    );";
        $conec->ejecutar($sql_ins);
        $mensaje = 'Vale Agregado Correctamente';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo."&tarea=VALE&id=$benef->ben_id",'','Correcto');
    }

    function total_consumido($int_id){
//        echo "select sum(val_monto) as campo from beneficiario_vale where val_int_id=$int_id and val_estado='Activo' and val_monto<0";
        $monto_total = FUNCIONES::atributo_bd_sql("select sum(val_monto) as campo from beneficiario_vale where val_int_id=$int_id and val_estado='Activo' and val_monto<0");
        return $monto_total*(-1);
    }
    function total_descuento($int_id){
        $monto_total = FUNCIONES::atributo_bd_sql("select sum(val_monto) as campo from beneficiario_vale where val_int_id=$int_id and val_estado='Activo' and val_monto>0");
        return $monto_total;
    }
    function resumen_descuentos($benef){
        $this->formulario->dibujar_titulo('VALES');
        ?>
                <div id="Contenedor_NuevaSentencia">
                    <div id="FormSent">
                        <div class="Subtitulo">Pagar Vendedor</div>
                        <div id="ContenedorSeleccion">
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Total Descuento:</div>
                                <div class="read-input"><?php echo FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$benef->ben_int_id'")?></div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Total Descuento:</div>
                                <div id="CajaInput" style="margin-top: 3px">
                                    &nbsp;&nbsp;<b><?php $total_descuento = $this->total_descuento($benef->ben_int_id); echo number_format($total_descuento,2,'.',','); ?></b>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Total Consumido:</div>
                                <div id="CajaInput" style="margin-top: 3px">
                                    &nbsp;&nbsp;<b><?php $total_consumido = $this->total_consumido($benef->ben_int_id); echo number_format($total_consumido,2,'.',','); ?></b>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Saldo Descuento:</div>
                                <div id="CajaInput" style="margin-top: 3px">
                                    &nbsp;&nbsp;<b><?php echo number_format($total_descuento-$total_consumido,2,'.',','); ?></b>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div id="CajaBotones">
                                    <center>
                                        <input type="button" class="boton" name="" value="Generar" onclick="location.href='gestor.php?mod=beneficiario&tarea=VALE<?php echo '&id='.$_GET['id']; ?>&acc=nuevo';">
                                    </center>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo "gestor.php?mod=$this->modulo&tarea=$_GET[tarea]&id=$_GET[id]"; ?>&acc=anular" method="POST" enctype="multipart/form-data">  
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
                <input type="hidden" id="val_id" name="val_id" value="">
                <input type="hidden" id="observacion_anu" name="observacion_anu" value="">

            </form>
                <script>
                    function anular_descuento(id) {
                        var txt = 'Esta seguro de anular el el VALE?' +
                                '<br />Observacion:<br /><textarea id="observacion_anu" name="observacion_anu" rows="2" cols="40"></textarea><br />' 
                                ;
                        $.prompt(txt,{ 
                            buttons:{Aceptar:true, Cancelar:false},
                            callback: function(v,m,f){						
                                if(v){
                                    $('#val_id').val(id);
                                    $('#observacion_anu').val(f.observacion_anu);
                                    document.frm_sentencia.submit();
    //                                location.href='gestor.php?mod=venta&tarea=EXTRACTOS&id=<?php // echo $_GET[id];?>&acc=anular&pag_id='+id;
                                }
                            }
                        });
                    }
                </script>
                
                <div class="aTabsCont" style="margin-left: 95px;">
                    <div class="aTabsCent">
                        <ul class="aTabs">
                            <li><a href="javascript:void(0)" class="activo" id="tabs_pag" >Descuentos</a></li>
                            <li><a href="javascript:void(0)" id="tabs_dev">Consumido</a></li>                            
                        </ul>
                    </div>
                </div>
                <script>
                    $('#tabs_pag').click(function (){
                        $('#h_pagos').show();
                        $('#h_devoluciones').hide();
                        $('.activo').removeClass('activo');
                        $('#tabs_pag').addClass('activo');
                    });
                    $('#tabs_dev').click(function (){
                        $('#h_pagos').hide();
                        $('#h_devoluciones').show();
                        $('.activo').removeClass('activo');
                        $('#tabs_dev').addClass('activo');
                    });
                </script>
                <br><br>
                <div id="h_pagos">
                    <center>
                        <h2>DESCUENTOS</h2>
                        <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>                                
                                    <th>Moneda</th>                                
                                    <th>Descripcion</th>                                
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th class="tOpciones">Opciones</th> 
                                </tr>	
                            </thead>
                            <tbody>
                                <?php                                
                                $sql = "select * from beneficiario_vale where val_int_id='$benef->ben_int_id' and val_estado='Activo' and val_monto>0";
                                $listado=  FUNCIONES::lista_bd_sql($sql);
                                
                                foreach ($listado as $obj) {
                                    ?>
                                    <tr>
                                        <td><?php echo FUNCIONES::get_fecha_latina($obj->val_fecha);?></td>
                                        <td><?php echo $obj->val_monto;?></td>
                                        <td><?php echo $obj->val_moneda==1?'Bolivianos':'Dolares';?></td>
                                        <td><?php echo $obj->val_descripcion;?></td>
                                        <td><?php echo $obj->val_usu_cre;?></td>
                                        <td><?php echo $obj->val_estado;?></td>
                                        <td>
                                            <?php if ($obj->val_estado != 'Anulado') { ?>
                                            <a class="linkOpciones" href="javascript:anular_descuento('<?php echo $obj->val_id; ?>');">
                                                <img src="images/anular.png" border="0" title="ANULAR PAGO COMISION" alt="anular">
                                            </a>
                                            <?php } ?>
                                        </td>

                                    </tr>
                                <?php } ?>

                        </tbody>
                    </table>
                </center>
            </div>
            <div id="h_devoluciones" style="display: none;">
                <center>
                    <h2>DESCUENTOS CONSUMIDOS</h2>
                    <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>                                
                                    <th>Moneda</th>                                
                                    <th>Descripcion</th>                                
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                </tr>	
                            </thead>
                            <tbody>
                                <?php                                
                                $sql = "select * from beneficiario_vale where val_int_id='$benef->ben_int_id' and val_estado='Activo' and val_monto<0";
                                $listado=  FUNCIONES::lista_bd_sql($sql);
                                
                                foreach ($listado as $obj) {
                                    ?>
                                    <tr>
                                        <td><?php echo FUNCIONES::get_fecha_latina($obj->val_fecha);?></td>
                                        <td><?php echo $obj->val_monto*(-1);?></td>
                                        <td><?php echo $obj->val_moneda==1?'Bolivianos':'Dolares';?></td>
                                        <td><?php echo $obj->val_descripcion;?></td>
                                        <td><?php echo $obj->val_usu_cre;?></td>
                                        <td><?php echo $obj->val_estado;?></td>
                                    </tr>
                                <?php } ?>

                        </tbody>
                    </table>
            </center>
        </div>
                    <?php
    }
    

}
        ?>