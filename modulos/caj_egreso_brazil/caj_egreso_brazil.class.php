<?php

class caj_egreso_brazil extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function caj_egreso_brazil() {  //permisos
        $this->ele_id = 192;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "ban_nombre";
        $this->arreglo_campos[0]["texto"] = "Nombre";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 25;

        $this->arreglo_campos[1]["nombre"] = "ban_descripcion";
        $this->arreglo_campos[1]["texto"] = "Descripcion";
        $this->arreglo_campos[1]["tipo"] = "cadena";
        $this->arreglo_campos[1]["tamanio"] = 25;

        $this->link = 'gestor.php';

        $this->modulo = 'caj_egreso_brazil';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('EGRESO DE CAJA BRAZIL');

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
    }

    function dibujar_listado() {
        $sql = "select * from caj_egreso_brazil where 1 ";
        $this->set_sql($sql, '');
        $this->set_opciones();
        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Beneficiario</th>
            <th>Fecha</th>
            <th>Monto</th>
            <th>Descripci&oacute;n</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();

        for ($i = 0; $i < $this->numero; $i++) {

            $objeto = $this->coneccion->get_objeto();
            echo '<tr>';

            echo "<td>";
            echo $objeto->suc_nombre;
            echo "&nbsp;";
            echo "</td>";

            echo "<td>";
            echo $objeto->suc_descripcion;
            echo "&nbsp;";
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->suc_id);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from caj_egreso_brazil 
				where suc_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['suc_id'] = $objeto->suc_id;

        $_POST['suc_nombre'] = $objeto->suc_nombre;
        $_POST['suc_descripcion'] = $objeto->suc_descripcion;
    }

    function datos() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Nombre";
            $valores[$num]["valor"] = $_POST['suc_nombre'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;
            $valores[$num]["etiqueta"] = "Descripcion";
            $valores[$num]["valor"] = $_POST['suc_descripcion'];
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

    function formulario() {
        if($_POST[frm_params]=='ok'){
            $this->formulario_tcp();
        }else{
            $this->formulario_params();
        }
    }

    function formulario_params() {
        $this->formulario->dibujar_tarea('USUARIO');
        $url = "$this->link?mod=$this->modulo&tarea=AGREGAR";
        $red = "$this->link?mod=$this->modulo&tarea=ACCEDER";
        ?>
        <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_caj_egreso_brazil" name="frm_caj_egreso_brazil" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <input type="hidden" name="frm_params" value="ok">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos para Generar</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Fecha</div>
                            <div id="CajaInput">
                                <?php FORMULARIO::cmp_fecha('fecha');?>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Fecha Inicio</div>
                            <div id="CajaInput">
                                <input name="fecha_ini" id="fecha_ini"  type="text" class="caja_texto" value="<?php echo date('d/m/Y'); ?>" >
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Fecha Fin</div>
                            <div id="CajaInput">
                                <input name="fecha_fin" id="fecha_fin"  type="text" class="caja_texto" value="<?php echo date('d/m/Y'); ?>" >
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="submit" class="boton" name="" value="Generar" >
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                </center>
                            </div>
                        </div>
                    </div>
                        
                    <script>
                        $('#fecha_ini').mask('99/99/9999');
                        $('#fecha_fin').mask('99/99/9999');
                        $('#fecha').mask('99/99/9999');
                    </script>
                </div>
            </form>
        </div>
        <?php
    }

    function formulario_tcp() {
        $this->formulario->dibujar_tarea('USUARIO');
        $url = "$this->link?mod=$this->modulo&tarea=AGREGAR";
        $red = "$this->link?mod=$this->modulo&tarea=ACCEDER";
        ?>
        <script src="js/jquery.maskedinput-1.3.min.js" type="text/javascript"></script>
        <script src="js/util.js" type="text/javascript"></script>
        <style>
            .fleft{ float: left;margin-left: 3px; }
            .cazul{ color:#0000ff; }
            .cnegro{ color:#000; }
        </style>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_caj_egreso_brazil" name="frm_caj_egreso_brazil" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
                <input type="hidden" name="frm_gen" value="ok">
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha: </div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $_POST[fecha]?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Desde </div>
                            <div id="CajaInput">
                                <!--<span style="float: left; margin: 5px 8px;"> desde </span>-->
                                <div class="read-input"><?php echo $_POST[fecha_ini]?></div>
                                <span style="float: left; margin: 5px 8px;"> hasta </span>
                                <div class="read-input"><?php echo $_POST[fecha_fin]?></div>
                            </div>
                        </div>
                        <?php
                        $mon_des=3;
                        $sql_sel="select * from con_cv_divisa
                                    where
                                        cvd_tipo='Venta' and cvd_usu_cre='$_SESSION[id]' and 
                                        cvd_estado='Activo' and cvd_mon_des='$mon_des' and cvd_comparado='0'";
                        $dventas=  FUNCIONES::objetos_bd_sql($sql_sel);
//                        echo $dventas->get_num_registros();
                        ?>
                        <div id="ContenedorDiv">
                            <table class="tablaLista" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Monto Ori.</th>
                                        <th>Moneda Ori.</th>
                                        <th>Monto Des.</th>
                                        <th>Moneda Des.</th>
                                        <th>TC</th>
                                        <th>Usuario</th>
                                        <th>M.C.</th>
                                        <th>Saldo M.C.</th>
                                        <th class="tOpciones" style="width: 80px;"><input type="checkbox" class="checkprincipal" name="chk0"></th> 
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $sum_mc=0;
                                    ?>
                                    <?php for($i=0;$i<$dventas->get_num_registros();$i++){?>
                                        <?php $dven=$dventas->get_objeto();?>
                                        <tr>
                                            <td><?php echo $dven->cvd_tipo;?></td>
                                            <td><?php echo FUNCIONES::interno_nombre($dven->cvd_int_id);?></td>
                                            <td><?php echo FUNCIONES::get_fecha_latina($dven->cvd_fecha);?></td>
                                            <td><?php echo $dven->cvd_monto_ori;?></td>
                                            <td><?php echo $dven->cvd_mon_ori==2?'Dolares':'';?></td>
                                            <td><?php echo $dven->cvd_monto_des;?></td>
                                            <td><?php echo $dven->cvd_mon_des==3?'Reales':'';;?></td>
                                            <td><?php echo $dven->cvd_tc;?></td>
                                            <td><?php echo $dven->cvd_usu_cre;?></td>
                                            <td><?php echo $dven->cvd_monto_cmp;?></td>
                                            <td><?php echo $mc=$dven->cvd_monto_des-$dven->cvd_monto_cmp;?></td>
                                            <td>
                                                <input type="checkbox" class="checkdetalle fleft" data-monto="<?php echo $mc;?>" data-id="<?php echo $dven->cvd_id;?>">&nbsp;
                                                <input type="text" class="comision_mes fleft" value="" size="5" data-id="<?php echo $dven->cvd_id;?>">&nbsp;
                                            </td>
                                            <?php
                                            $sum_mc+=$mc;
                                            ?>
                                        </tr>
                                        <?php $dventas->siguiente();?>
                                    <?php }?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="10" style="text-align: right;">Total </td>
                                        <td ><?php echo number_format($sum_mc, 2);?></td>
                                        <td ><span id="monto_marcado"></span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div hidden="">
                            <input type="hidden" name="comision_a_pagar" id="comision_a_pagar" value="">
                            <input type="hidden" name="com_ids" id="com_ids" value="">
                            <input type="hidden" name="com_montos" id="com_montos" value="">
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Monto: </div>
                            <div id="CajaInput">
                                <div class="read-input" id="txt_comision">&nbsp;</div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >T. Cambio: </div>
                            <div id="CajaInput">
                                <input type="text" id="tca" name="tca" value="" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="submit" class="boton" name="" value="Guardar" >
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                </center>
                            </div>
                        </div>
                    </div>
                    <script>
                        mask_decimal('.comision_mes',null);
                        $('.checkprincipal').click(function (){
                            var check=$(this).prop('checked');
                            var detalles=$('.checkdetalle');
                            for(var i=0;i<detalles.size();i++){
                                if(check){
                                    $(detalles[i]).prop('checked','checked');
                                    var monto=$(detalles[i]).attr('data-monto')*1;
                                    $(detalles[i]).next('input').val(monto);
                                    $(detalles[i]).next('input').addClass('cazul');
                                }else{
                                    $(detalles[i]).prop('checked','');
                                    $(detalles[i]).next('input').val('');
                                    $(detalles[i]).next('input').removeClass('cazul');
                                }
                            }
                            sumar_montos();
                        });
                        $('.comision_mes').focusout(function (){
                            var monto=$(this).prev('input').attr('data-monto')*1;
                            var valor=$(this).val()*1;
                            if(valor>monto){
                                $(this).prev('input').prop('checked','checked');
                                $(this).val(monto);
                                $(this).addClass('cazul');
                            }else if(valor===monto){
                                $(this).prev('input').prop('checked','checked');
                                $(this).addClass('cazul');
                            }else if(valor<0){
                                $(this).prev('input').prop('checked','');
                                $(this).val(monto);
                                $(this).removeClass('cazul');
                            }else{
                                $(this).prev('input').prop('checked','');
                                $(this).removeClass('cazul');
                            }

                            sumar_montos();
                        });
                        $('.checkdetalle').click(function (){
                            var detalles=$('.checkdetalle');
                            var marcados=$('.checkdetalle[type=checkbox]:checked"');
                            if(detalles.size()===marcados.size()){
                                $('.checkprincipal').prop('checked','checked');
                            }else{
                                $('.checkprincipal').prop('checked','');
                            }
                            var check=$(this).prop('checked');
                            if(check){
                                var monto=$(this).attr('data-monto')*1;
                                $(this).next('input').val(monto);
                                $(this).next('input').addClass('cazul');
                            }else{
                                $(this).next('input').val('');
                                $(this).next('input').removeClass('cazul');
                            }
                            sumar_montos();
                        });
                        function sumar_montos(){
//                            var marcados=$('.checkdetalle[type=checkbox]:checked"');
                            var marcados=$('.comision_mes');
                            var total=0;
                            var comisiones='';
                            var montos='';
                            var j=0;
                            for(var i=0;i<marcados.size();i++){
                                var monto=$(marcados[i]).val()*1;
                                if(monto>0){
                                    if(j>0){
                                        comisiones+=',';
                                    }
                                    if(j>0){
                                        montos+=',';
                                    }
                                    var com_id=$(marcados[i]).attr('data-id');
        //                            var com_monto=$(marcados[i]).val();
                                    comisiones+=com_id;
                                    montos+=monto+'';
                                    total+=monto;
                                    j++;
                                }
                            }
                            var str_total=total.toFixed(2);
                            $('#com_ids').val(comisiones);
                            $('#com_montos').val(montos);
                            $('#comision_a_pagar').val(str_total);
                            $('#txt_comision').text(str_total);
                            $('#monto_marcado').text(str_total);
                            $('#comision_a_pagar').trigger('focusout');
                        }
                    </script>
                </div>
            </form>
        </div>
        <?php
    }

    function emergente() {
        $this->formulario->dibujar_cabecera();
        $valor = trim($_POST['valor']);
        ?>

        <script>
            function poner(id, valor)
            {
                opener.document.frm_caj_egreso_brazil.suc_int_id.value = id;
                opener.document.frm_caj_egreso_brazil.suc_nombre_persona.value = valor;
                window.close();
            }
        </script>
        <br><center><form name="form" id="form" method="POST" action="gestor.php?mod=caj_egreso_brazil&tarea=AGREGAR&acc=Emergente">
                <table align="center">
                    <tr>
                        <td class="txt_contenido" colspan="2" align="center">
                            <input name="valor" type="text" class="caja_texto" size="30" value="<?php echo $valor; ?>">
                            <input name="Submit" type="submit" class="boton" value="Buscar">
                        </td>
                    </tr>
                </table>
            </form><center>
        <?php
        $conec = new ADO();

        if ($valor <> "") {
            $sql = "select int_id,int_nombre,int_apellido from interno where int_nombre like '%$valor%' or int_apellido like '%$valor%'";
        } else {
            $sql = "select int_id,int_nombre,int_apellido from interno";
        }

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        echo '<table class="tablaLista" cellpadding="0" cellspacing="0">
					<thead>
					<tr>
						<th>
							Nombre
						</th>
						<th>
							Apellido
						</th>
						<th width="80" class="tOpciones">
							Seleccionar
						</th>
				</tr>
				</thead>
				<tbody>
			';

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();

            echo '<tr>
						 <td>' . $objeto->int_nombre . '</td>
						 <td>' . $objeto->int_apellido . '</td>
						 <td><a href="javascript:poner(' . "'" . $objeto->int_id . "'" . ',' . "'" . $objeto->int_nombre . ' ' . $objeto->int_apellido . "'" . ');"><center><img src="images/select.png" border="0" width="20px" height="20px"></center></a></td>
					   </tr>	 
				';

            $conec->siguiente();
        }
        ?>
                </tbody></table>
                <?php
            }

            function insertar_tcp() {

                $conec = new ADO();
                $sql = "insert into caj_egreso_brazil (suc_nombre,suc_descripcion,suc_eliminado)
                    values ('" . $_POST['suc_nombre'] . "','" . $_POST['suc_descripcion'] . "','No')";
                //echo $sql.'<br>';
                $conec->ejecutar($sql);
                $mensaje = 'Sucursal Agregado Correctamente';

                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function modificar_tcp() {
                $conec = new ADO();
                $sql = "update caj_egreso_brazil set 
                        suc_nombre='" . $_POST['suc_nombre'] . "',
                        suc_descripcion='" . $_POST['suc_descripcion'] . "'
                        where suc_id='" . $_GET['id'] . "'";
                //echo $sql;	
                $conec->ejecutar($sql);
                $mensaje = 'Sucursal Modificado Correctamente';
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function formulario_confirmar_eliminacion() {
                $mensaje = 'Esta seguro de eliminar el Sucursal?';
                $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'suc_id');
            }

            function eliminar_tcp() {
//                $cantidad = FUNCIONES::atributo_bd("ad_venta", "usu_suc_id='" . $_POST['suc_id'] . "'", 'count(*)');
////            echo$cantidad."<br>";
//                if ($cantidad == 0) {
////                echo "elimino";
////                return;
//                    $conec = new ADO();
//                    $sql = "update caj_egreso_brazil set suc_eliminado='si' where suc_id='" . $_POST['suc_id'] . "'";
//                    $conec->ejecutar($sql);
//                    $mensaje = 'Sucursal Eliminado Correctamente.';
//                } else {
//                    $mensaje = 'El Sucursal no puede ser eliminado, por que ya fue referenciado en algunos comprobantes.';
//                }
//                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

        }
        ?>