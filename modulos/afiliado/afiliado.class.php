<?php

class AFILIADO extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function AFILIADO() {  //permisos
        $this->ele_id = 410;
        $this->busqueda();
        
//        if (!($this->verificar_permisos('AGREGAR'))) {
//            $this->ban_agregar = false;
//        }
        $this->ban_agregar = FALSE;
        //fin permisos

        $this->num_registros = 15;

        $this->coneccion = new ADO();
        $num = 0;
        
        $this->arreglo_campos[$num]["nombre"] = "vdo_id";
        $this->arreglo_campos[$num]["texto"] = "ID";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 25;
        $num++;
        
        $this->arreglo_campos[$num]["nombre"] = "vdo_venta_inicial";
        $this->arreglo_campos[$num]["texto"] = "Codigo";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 25;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "int_nombre_apellido";
        $this->arreglo_campos[$num]["campo_compuesto"] = "concat(int_nombre,' ',int_apellido)";
        $this->arreglo_campos[$num]["texto"] = "Nombre completo";
        $this->arreglo_campos[$num]["tipo"] = "compuesto";
        $this->arreglo_campos[$num]["tamanio"] = 40;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "int_ci";
        $this->arreglo_campos[$num]["texto"] = "C.I.";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 25;
        
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "vdo_debitar";
        $this->arreglo_campos[$num]["texto"] = "Debito Automatico";
        $this->arreglo_campos[$num]["tipo"] = "comboarray";
        $this->arreglo_campos[$num]["valores"] = "No,Si:No,Si";


        $this->link = 'gestor.php';

        $this->modulo = 'afiliado';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('AFILIADO');

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

        if ($this->verificar_permisos('CUENTAS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'CUENTAS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/listado.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'HISTORIAL';
            $nun++;
        }

        if ($this->verificar_permisos('PAGOS COMISIONES')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'PAGOS COMISIONES';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/cuenta.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'PAGOS COMISIONES';
            $nun++;
        }
        
        if ($this->verificar_permisos('VER HISTORIAL')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'VER HISTORIAL';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/plan.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER HISTORIAL';
            $nun++;
        }
        
        if ($this->verificar_permisos('AUTORIZACION')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'AUTORIZACION';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/autorizacion.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'AUTORIZAR DEBITO AUTOMATICO';
            $nun++;
        }
        
        if ($this->verificar_permisos('DEBITO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'DEBITO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/extractos.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'LISTADO DE DEBITOS';
            $nun++;
        }
    }

    function dibujar_listado() {
        $sql = "SELECT int_nombre,int_apellido,vgru_nombre,vendedor.*
        FROM 
        vendedor 
        inner join interno on(vdo_int_id=int_id)
        inner join vendedor_grupo on(vdo_vgru_id=vgru_id) 
        inner join venta on (vdo_venta_inicial=ven_id)
        where vgru_nombre='AFILIADOS' 
        and vdo_venta_inicial>0
        and vdo_estado='Habilitado' 
        and ven_multinivel='si' 
        and ven_estado in ('Pendiente','Pagado')
        ";
		
		$sql = "SELECT int_nombre,int_apellido,vgru_nombre,vendedor.*
        FROM 
        vendedor 
        inner join interno on(vdo_int_id=int_id)
        inner join vendedor_grupo on(vdo_vgru_id=vgru_id) 
        inner join venta on (vdo_venta_inicial=ven_id)
        where vgru_nombre='AFILIADOS' 
        and vdo_venta_inicial>0
        
        and ven_multinivel='si' 
        and ven_estado in ('Pendiente','Pagado')
        ";
		
        $this->set_sql($sql, 'order by vdo_id desc');

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>ID</th>
            <th>Codigo</th>				
            <th>Persona</th>				
            <th>Patrocinador</th>				
<!--            <th>Cuenta Analitica</th>-->
            <!--<th>Tipo de Vendedor</th>-->
            <th>Estado</th>

            <th class="tOpciones" width="300px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();

        for ($i = 0; $i < $this->numero; $i++) {

            $operaciones=array();
            $filtros=array();
            
            $objeto = $this->coneccion->get_objeto();
            
            if($objeto->vdo_debitar == 'No'){
                $operaciones[] = "AUTORIZACION";
                $operaciones[] = "DEBITO";
            }
            
            echo '<tr>';

            echo "<td>";
            echo $objeto->vdo_id;
            echo "</td>";
            
            echo "<td>";
            echo $objeto->vdo_venta_inicial;
            echo "</td>";
            echo "<td>";
            echo $objeto->int_nombre . " " . $objeto->int_apellido;
            echo "&nbsp;";
            
            $patrocinador = FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido)as campo
                from interno inner join vendedor on (int_id=vdo_int_id)
                where vdo_id=$objeto->vdo_vendedor_id");
            echo "<td>";
            echo $patrocinador;
            echo "&nbsp;";
            
            echo "</td>";

//            echo "<td>";
//            if ($objeto->vdo_can_id != '') {
//                $ges_id = $_SESSION['ges_id'];
//                echo FUNCIONES::atributo_bd("con_cuenta_ca", "can_ges_id='$ges_id' and can_codigo='$objeto->vdo_can_id'", "can_descripcion");
//            }
//            echo "&nbsp;";
//            echo "</td>";

//            echo "<td>";
//            echo ucwords($objeto->vgru_nombre);
//            echo "&nbsp;";
//            echo "</td>";
            
            echo "<td>";
            echo $objeto->vdo_estado;
            echo "&nbsp;";
            echo "</td>";

            echo "<td>";
            echo $this->get_opciones($objeto->vdo_id, "", $operaciones, $filtros);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from vendedor inner join interno on (vdo_int_id=int_id)
				where vdo_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['vdo_id'] = $objeto->vdo_id;

        $_POST['vdo_int_id'] = $objeto->vdo_int_id;

        $_POST['nombre_persona'] = $objeto->int_nombre . ' ' . $objeto->int_apellito;

        $_POST['vdo_estado'] = $objeto->vdo_estado;

        //$_POST['vdo_cco_id']=$objeto->vdo_cco_id;

        $_POST['id_cuenta_a'] = $objeto->vdo_can_id;
        $ges_id = $_SESSION['ges_id'];
        $_POST['nombre_cuenta_a'] = FUNCIONES::atributo_bd("con_cuenta_ca", "can_ges_id='$ges_id' and can_codigo='$objeto->vdo_can_id'", "can_descripcion");


        $_POST['vdo_vgru_id'] = $objeto->vdo_vgru_id;


        $fun = NEW FUNCIONES;

        $_POST['vdo_nombre_persona'] = $fun->nombre($objeto->vdo_int_id);
    }

    function datos() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Persona";
            $valores[$num]["valor"] = $_POST['vdo_int_id'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;

//			$valores[$num]["etiqueta"]="Cuenta Analitica";
//			$valores[$num]["valor"]=$_POST['id_cuenta_a'];
//			$valores[$num]["tipo"]="todo";
//			$valores[$num]["requerido"]=true;
//			$num++;
            $valores[$num]["etiqueta"] = "Tipo de Vendedor";
            $valores[$num]["valor"] = $_POST['vdo_vgru_id'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;

            $valores[$num]["etiqueta"] = "Estado";
            $valores[$num]["valor"] = $_POST['vdo_estado'];
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
        $conec = new ADO();

        $sql = "select * from interno";
        $conec->ejecutar($sql);
        $nume = $conec->get_num_registros();
        $personas = 0;
        if ($nume > 0) {
            $personas = 1;
        }

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
        $page = "'gestor.php?mod=afiliado&tarea=AGREGAR&acc=Emergente'";
        $extpage = "'persona'";
        $features = "'left=325,width=600,top=200,height=420,scrollbars=yes'";

        $this->formulario->dibujar_tarea('USUARIO');

        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <script>
            function reset_interno()
            {
                document.frm_vendedor.vdo_int_id.value = "";
                document.frm_vendedor.vdo_nombre_persona.value = "";
            }
        </script>
        <!--AutoSuggest-->
        <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        <!--AutoSuggest-->
        <!--FancyBox-->
        <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <!--FancyBox-->
        <script>
            function validar_vendedor(frm)
            {

                var vendedor = document.frm_vendedor.vdo_int_id.value;
        //			   var cuenta_a=document.frm_vendedor.id_cuenta_a.value;			   
                var vgru_id = $('#vdo_vgru_id option:selected').val();

                if (vendedor !== '' && vgru_id > 0)
                {
                    document.frm_vendedor.submit();
                }
                else
                    $.prompt('Debes Completar los campos con (*).<br> Debes agregar por lo menos 1 Comision por Urbanizacion.', {opacity: 0.8});
            }


            function set_valor_interno(data) {
                document.frm_vendedor.vdo_int_id.value = data.id;
                document.frm_vendedor.vdo_nombre_persona.value = data.nombre;
            }



        </script>

        <div id="Contenedor_NuevaSentencia">
            <form id="frm_vendedor" name="frm_vendedor" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
                            <div id="CajaInput">
                                <?php if ($personas <> 0) { ?>
                                    <input name="vdo_int_id" id="vdo_int_id" type="hidden" readonly="readonly" class="caja_texto" value="<?php echo $_POST['vdo_int_id'] ?>" size="2">
                                    <input name="vdo_nombre_persona" id="vdo_nombre_persona" readonly="readonly" disabled="disabled" type="text" class="caja_texto" value="<?php echo $_POST['vdo_nombre_persona'] ?>" size="35">
                                    <?php if (!($ver || $cargar)) { ?>
                                        <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                            <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                        </a>
                                        <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;" data-url="gestor.php?mod=interno&tarea=ACCEDER&acc=buscar" href="javascript:void(0)">
                                            <img src="images/b_search.png" border="0" title="BUSCAR" alt="BUSCAR">
                                        </a>
                                        <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_interno();">
                                            <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                        </a>
                                    <?php } ?>
                                    <?php
                                } else {
                                    echo 'No se le asigno ningúna personas, para poder cargar las personas.';
                                }
                                ?>
                            </div>
                        </div>

                        <div id="ContenedorDiv" hidden="">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta Anal&iacute;tica</div>
                            <div id="CajaInput">
                                <input name="id_cuenta_a" id="id_cuenta_a" type="hidden" readonly="readonly" class="caja_texto" value="<?php echo $_POST['id_cuenta_a']; ?>" size="2">
                                <input name="nombre_cuenta_a" id="nombre_cuenta_a"  type="text" class="caja_texto" value="<?php echo $_POST['nombre_cuenta_a']; ?>" size="25">
                            </div>							   							   								
                        </div>
                        <!--Fin-->
                        <script>
            function complete_cuenta_ca() {
                var options_ca = {
                    script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=ca&",
                    varname: "input",
                    json: true,
                    shownoresults: false,
                    maxresults: 6,
                    callback: function(obj) {
                        $("#id_cuenta_a").val(obj.info);
                    }
                };
                var as_json2 = new _bsn.AutoSuggest('nombre_cuenta_a', options_ca);
            }
            complete_cuenta_ca();
            $("#nombre_cuenta_a").live("keyup", function() {
                if ($(this).val() === '') {
                    $("#id_cuenta_a").val("");
                }
            });
                        </script>
                        <div id="ContenedorDiv" hidden="">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Tipo de Vendedor</div>
                            <div id="CajaInput">
                                <select name="vdo_vgru_id" id="vdo_vgru_id"class="caja_texto">
                                    <option value="" >Seleccione</option>
                                    <?php
                                    $fun = new FUNCIONES();
                                    $fun->combo("select vgru_id as id, vgru_nombre as nombre from vendedor_grupo where vgru_eliminado='No' and vgru_estado='Habilitado'", $_POST[vdo_vgru_id]);
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Estado</div>
                            <div id="CajaInput">
                                <select name="vdo_estado" class="caja_texto">
                                    <option value="" >Seleccione</option>
                                    <option value="Habilitado" <?php if ($_POST['vdo_estado'] == 'Habilitado') echo 'selected="selected"'; ?>>Habilitado</option>
                                    <option value="Deshabilitado" <?php if ($_POST['vdo_estado'] == 'Deshabilitado') echo 'selected="selected"'; ?>>Deshabilitado</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <script>
                        $('#vdo_comision').change(function() {
                            var comision = $(this).val();
                            if (comision === 'porc') {
                                $('.txt_valor_unidad').text('%');
                            } else if (comision === 'fijo') {
                                $('.txt_valor_unidad').text('$us');
                            } else {
                                $('.txt_valor_unidad').text('');
                            }
                        });

                        mask_decimal('#vdo_valor_cont, #vdo_valor_cred', null);
                        $('#vdo_comision').trigger('change');
                    </script>

                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <?php
                                if (!($ver)) {
                                    ?>
                                        <!--<input type="submit" class="boton" name="" value="Guardar">-->
                                    <input type="buttom" class="boton" name="" value="Guardar" onclick="javascript:validar_vendedor(this);">
                                    <input type="reset" class="boton" name="" value="Cancelar">
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                    <?php
                                } else {
                                    ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                <?php }
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

    function emergente() {
        $this->formulario->dibujar_cabecera();
        $valor = trim($_POST['valor']);
        ?>

        <script>
                        function poner(id, valor)
                        {
                            opener.document.frm_vendedor.vdo_int_id.value = id;
                            opener.document.frm_vendedor.vdo_nombre_persona.value = valor;
                            window.close();
                        }
        </script>
        <br><center><form name="form" id="form" method="POST" action="gestor.php?mod=afiliado&tarea=AGREGAR&acc=Emergente">
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
                $verificar = NEW VERIFICAR;
                $parametros[0] = array('vdo_int_id');
                $parametros[1] = array($_POST['vdo_int_id']);
                $parametros[2] = array('vendedor');

                if ($verificar->validar($parametros)) {
                    $conec = new ADO();
                    $sql = "insert into vendedor (
                                    vdo_int_id,vdo_can_id,vdo_vgru_id,vdo_estado
                                ) values (
                                    '$_POST[vdo_int_id]','$_POST[id_cuenta_a]','$_POST[vdo_vgru_id]','$_POST[vdo_estado]'
                                )";
                    echo $sql;
                    $conec->ejecutar($sql, false);
                    $mensaje = 'Vendedor Agregado Correctamente';
                } else {
                    $mensaje = 'La persona que seleccionaste, ya se encuentra registrado como vendedor.';
                }
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function cargar_comision_vendedor($id_vendedor) {
                $conec = new ADO();

                $sql = "select * from vendedor_comision
            inner join urbanizacion on (urb_id=vdocom_urb_id) where vdocom_vdo_id='" . $id_vendedor . "'";

                $conec->ejecutar($sql);
                $num = $conec->get_num_registros();
                $cad = "";

                for ($i = 0; $i < $num; $i++) {
                    $objeto = $conec->get_objeto();

                    $tipo_comision_muestra = '';
                    if ($objeto->vdocom_tipo_comi == 'metro_cuadrado')
                        $tipo_comision_muestra = 'Por M2';
                    else
                    if ($objeto->vdocom_tipo_comi == 'porcentaje')
                        $tipo_comision_muestra = 'Por Porcentaje';
                    else
                    if ($objeto->vdocom_tipo_comi == 'monto_especifico')
                        $tipo_comision_muestra = 'Monto Fijo';

                    $moneda_muestra = '';
                    if ($objeto->vdocom_moneda == 1)
                        $moneda_muestra = 'Bolivianos';
                    if ($objeto->vdocom_moneda == 2)
                        $moneda_muestra = 'Dolares';
                    if ($objeto->vdocom_moneda == '')
                        $moneda_muestra = 'S/Moneda';
                    ?>
                    <tr>
                        <td><input name="comu[]" type="hidden" value="<?php echo $objeto->vdocom_urb_id; ?>"><input name="comu_urbanizacion[]" type="hidden" value="<?php echo $objeto->vdocom_urb_id; ?>"><?php echo $objeto->urb_nombre; ?></td>
                        <td><input name="comu_tipo_comision[]" type="hidden" value="<?php echo $objeto->vdocom_tipo_comi; ?>"><?php echo $tipo_comision_muestra; ?></td>
                        <td><input name="comu_contado[]" type="hidden" value="<?php echo $objeto->vdocom_contado; ?>"><?php echo $objeto->vdocom_contado; ?></td>
                        <td><input name="comu_credito[]" type="hidden" value="<?php echo $objeto->vdocom_credito; ?>"><?php echo $objeto->vdocom_credito; ?></td>
                        <td><input name="comu_moneda[]" type="hidden" value=""><?php echo $moneda_muestra; ?></td>
                        <td><center><img style="float:none;" src="images/b_drop.png" id="img-del-comisiones" ></center></td></tr>
                    <?php
                    $conec->siguiente();
                }
                ?>
                <script>
                    document.frm_vendedor.nfilas.value =<?php echo $num; ?>;
                    document.frm_vendedor.nfilasshadown.value =<?php echo $num; ?>;
                </script>
                <?php
            }

            function modificar_tcp() {
                $conec = new ADO();
                $sql = "update vendedor set 
                        vdo_int_id='" . $_POST['vdo_int_id'] . "',
                        vdo_can_id='" . $_POST['id_cuenta_a'] . "',
                        vdo_vgru_id='" . $_POST['vdo_vgru_id'] . "',
                        vdo_estado='" . $_POST['vdo_estado'] . "'
                        where vdo_id='" . $_GET['id'] . "'";
                //echo $sql;	
                $conec->ejecutar($sql);
                $llave = $_GET['id'];
                /* ======================================================================= */
                /* ======================================================================= */
                $mensaje = 'Vendedor Modificado Correctamente';

                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function formulario_confirmar_eliminacion() {
                $mensaje = 'Esta seguro de eliminar el vendedor?';
                $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'vdo_id');
            }

            function eliminar_tcp() {
                $verificar = NEW VERIFICAR;
                $parametros[0] = array('com_vdo_id');
                $parametros[1] = array($_POST['vdo_id']);
                $parametros[2] = array('comision');

                if ($verificar->validar($parametros)) {
                    $conec = new ADO();
                    $sql = "delete from vendedor where vdo_id='" . $_POST['vdo_id'] . "'";
                    $conec->ejecutar($sql);
                    $sql = "delete from vendedor_comision where vdocom_vdo_id='" . $_POST['vdo_id'] . "'";
                    $conec->ejecutar($sql);
                    $mensaje = 'Vendedor Eliminado Correctamente.';
                } else {
                    $mensaje = 'El vendedor no puede ser eliminado, por que ya realizo algunas ventas.';
                }
                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function total_comisionado($vendedor) {
                $sql = "select sum(com_monto)as total from comision where com_vdo_id='" . $vendedor . "'";
                $conec = new ADO();
                $conec->ejecutar($sql);
                $num = $conec->get_num_registros();
                $total = 0;
                if ($num > 0) {
                    if ($conec->get_objeto()->total != NULL) {
                        $total = $conec->get_objeto()->total;
                    }
                }
                return $total;
            }

            function comision_pagada($vendedor) {
                //$sql = "select sum(com_monto)as total from comision where com_estado='Pagado' and com_vdo_id='".$vendedor."'";
                $sql = "select sum(pve_monto)as total from pago_vendedores where pve_estado='Activo' and pve_vdo_id='" . $vendedor . "'";
                $conec = new ADO();
                $conec->ejecutar($sql);
                $num = $conec->get_num_registros();
                $total = 0;
                if ($num > 0) {
                    if ($conec->get_objeto()->total != NULL) {
                        $total = $conec->get_objeto()->total;
                    }
                }
                return $total;
            }

            function formulario_pago_comision() {
                $url = $this->link . '?mod=' . $this->modulo;
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
                                location.href = 'gestor.php?mod=afiliado&tarea=CUENTAS&acc=descuentos&acc2=add&comdes_motivo=' + comdes_motivo + '&comdes_monto=' + comdes_monto + '&id=<?php echo $_GET['id']; ?>&com=<?php echo $_GET['com']; ?>&ven=<?php echo $_GET['ven']; ?>';
                            } else {
                                $.prompt('El monto a descontar no pueder ser mayor a: ' + (total_comisionado - total_descuento));
                            }
                        }
                    }

                    function enviar_formulario_pago_comision(moneda) {
                        var comision_a_pagar = document.getElementById('comision_a_pagar_' + moneda).value;
                        var glosa = document.getElementById('pag_glosa_' + moneda).value;
                        var fecha = document.getElementById('pag_fecha_' + moneda).value;
                        if (comision_a_pagar === '') {
                            $.prompt('El monto de la comision a pagar no debe ser vacio.');
                            return false;
                        }
                        if (glosa === '') {
                            $.prompt('La glosa de la comision a pagar no debe ser vacio.');
                            return false;
                        }
                        if (fecha !== '') {
                            $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                var dato = JSON.parse(respuesta);
                                if (dato.response !== "ok") {
                                    $.prompt(dato.mensaje);
                                } else {
                                    var _monto = $('#comision_a_pagar_2').val();
                                    var _moneda = $('#com_mon_id').val();
                                    if (!validar_fpag_montos(_monto, _moneda, dato.cambios)) {
                                        $.prompt('El monto a Pagar no cocuerda con los pagos realizados');
                                        return false;
                                    }
                                    document.frm_vendedor.submit();
                                }
                            });
                        } else {
                            $.prompt('La fecha del pago de la comision no debe ser vacio.');
                        }



                    }
                </script>
                <script type="text/javascript" src="js/util.js"></script>
                <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                <div id="Contenedor_NuevaSentencia">
                    <form id="frm_vendedor" name="frm_vendedor" action="<?php echo $url . '&tarea=PAGOS COMISIONES&acc=pagar&id=' . $_GET['id']; ?>" method="POST" enctype="multipart/form-data">  
                        <input type="hidden" name="moneda_pago" id="moneda_pago" value="2">
                        <div id="FormSent">
                            <div style="float: left; width: 50%; display: none;">
                                <div class="Subtitulo">Comisiones en Bolivianos</div>
                                <div id="ContenedorSeleccion" >
                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Total Comisionado</div>
                                        <div id="CajaInput">
                                            <input name="total_comisionado" id="total_comisionado" readonly="readonly" type="text" class="caja_texto" value="<?php $total_comisionado = $this->total_comisionado($_GET['id'], 1);
        echo number_format($total_comisionado, 2, '.', ',');
                ?>" size="10" onkeypress="javascript:return ValidarNumero(event);">
                                                   <?php
                                                   echo '&nbsp;Bs.';
                                                   ?>
                                        </div>                                    

                                    </div>
                                    <!--Fin-->

                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta">Comision Pagada</div>                                    
                                        <div id="CajaInput">                                        
                                            <input name="comision_pagada" id="comision_pagada" readonly="readonly" type="text" class="caja_texto" value="<?php $comision_pagada = $this->comision_pagada($_GET['id'], 1);
                                                   echo number_format($comision_pagada, 2, '.', ',');
                                                   ?>" size="10" onkeypress="javascript:return ValidarNumero(event);">
        <?= 'Bs.' ?>
                                        </div>
                                    </div>
                                    <!--Fin-->

                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Saldo Comision</div>
                                        <div id="CajaInput">
                                            <input name="saldo_comision" id="saldo_comision" readonly="readonly" type="text" class="caja_texto" value="<?php echo number_format(($total_comisionado - $comision_pagada), 2, '.', ','); ?>" size="10" onkeypress="javascript:return ValidarNumero(event);">
        <?= 'Bs.' ?>
                                        </div>
                                    </div>
                                    <!--Fin-->
                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Comision a Pagar</div>
                                        <div id="CajaInput">
                                            <input name="comision_a_pagar_1" id="comision_a_pagar_1" type="text" class="caja_texto" value="" size="10" onkeypress="javascript:return ValidarNumero(event);">
        <?= 'Bs.' ?>
                                        </div>
                                    </div>
                                    <!--Fin-->
                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">*</span>Glosa</div>
                                        <div id="CajaInput">
                                            <input  name="pag_glosa_1" type="text" id="pag_glosa_1" value="<?php echo $_POST['pag_glosa']; ?>" size="35"/>
                                        </div>
                                    </div>
                                    <!--Fin-->
                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="pag_fecha_1" id="pag_fecha_1" size="12" value="<?php if (isset($_POST['pag_fecha']))
            echo $_POST['pag_fecha'];
        else
            echo date("d/m/Y");
        ?>" type="text">
                                        </div>
                                    </div>
                                    <!--Fin-->

                                    <!--Inicio-->
                                    <div id="ContenedorDiv" style="text-align: center">
                                        <div class="Etiqueta" >&nbsp;</div>
                                        <div id="CajaInput">
                                            <input type="button" class="boton" name="" value="Pagar" onclick="javascript:enviar_formulario_pago_comision('1');">
                                        </div>                                        
                                    </div>
                                    <!--Fin-->

                                    <script>
                    jQuery(function($) {
                        $("#pag_fecha_2").mask("99/99/9999");
                    });
                                    </script>
                                </div>
                            </div>
                            <div style="float: left; width: 100%;">
                                <div class="Subtitulo">Comisiones en Dolares</div>
                                <div id="ContenedorSeleccion" >
                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Total Comisionado</div>
                                        <div id="CajaInput">
                                            <input name="total_comisionado" id="total_comisionado" readonly="readonly" type="text" class="caja_texto" value="<?php $total_comisionado = $this->total_comisionado($_GET['id'], '2');
        echo number_format($total_comisionado, 2, '.', ',');
        ?>" size="10" onkeypress="javascript:return ValidarNumero(event);">
        <?= '$us.' ?>
                                        </div>                                    

                                    </div>
                                    <!--Fin-->

                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta">Comision Pagada</div>                                    
                                        <div id="CajaInput">                                        
                                            <input name="comision_pagada" id="comision_pagada" readonly="readonly" type="text" class="caja_texto" value="<?php $comision_pagada = $this->comision_pagada($_GET['id'], 2);
        echo number_format($comision_pagada, 2, '.', ',');
        ?>" size="10" onkeypress="javascript:return ValidarNumero(event);">
        <?= '$us.' ?>
                                        </div>
                                    </div>
                                    <!--Fin-->

                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >Saldo Comision</div>
                                        <div id="CajaInput">
                                            <input name="saldo_comision" id="saldo_comision" readonly="readonly" type="text" class="caja_texto" value="<?php echo number_format(($total_comisionado - $comision_pagada), 2, '.', ','); ?>" size="10" onkeypress="javascript:return ValidarNumero(event);">
        <?= '$us.' ?>
                                        </div>
                                    </div>
                                    <!--Fin-->
                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">* </span>Comision a Pagar</div>
                                        <div id="CajaInput">
                                            <input name="comision_a_pagar_2" id="comision_a_pagar_2" type="text" class="caja_texto" value="" size="10" onkeypress="javascript:return ValidarNumero(event);">
                                            <input name="com_mon_id" id="com_mon_id" type="hidden" value="2" >
        <?= '$us.' ?>
                                        </div>
                                    </div>
                                    <!--Fin-->
                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">*</span>Glosa</div>
                                        <div id="CajaInput">
                                            <input  name="pag_glosa_2" type="text" id="pag_glosa_2" value="<?php echo $_POST['pag_glosa']; ?>" size="35"/>
                                        </div>
                                    </div>                                    
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                        <div id="CajaInput">
                                            <input class="caja_texto" name="pag_fecha_2" id="pag_fecha_2" size="12" value="<?php if (isset($_POST['pag_fecha']))
                                               echo $_POST['pag_fecha'];
                                           else
                                               echo date("d/m/Y");
                                           ?>" type="text">
                                        </div>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" ><b>Pagos</b></div>
        <?php $params = array('monto' => 'ind_monto_pagado'); ?>
        <?php FORMULARIO::frm_pago($params); ?>
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div class="Etiqueta" >&nbsp;</div>
                                        <div id="CajaInput">
                                            <input type="button" class="boton" name="" value="Pagar" onclick="javascript:enviar_formulario_pago_comision('2');">
                                        </div>
                                    </div>
                                    <!--Fin-->

                                    <script>
                    jQuery(function($) {
                        $("#pag_fecha_1").mask("99/99/9999");
                    });
                                    </script>
                                </div>
                            </div>



                            <div id="ContenedorDiv">
                                <div id="CajaBotones">
                                    <center>                                        
                                        <input type="reset" class="boton" name="" value="Cancelar">
                                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=afiliado&tarea=ACCEDER';">                                            
                                    </center>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <?php
            }

            function ver_comprobante_pago_comision($pve_id) {
                $sql = "select pve_vdo_id,pve_tipo_comision,pve_monto,pve_moneda,pve_fecha,pve_glosa,int_nombre,int_apellido ,pve_comisiones,pve_montos
                    from pago_vendedores 
                    inner join vendedor on(vdo_id=pve_vdo_id)
                    inner join interno on(int_id=vdo_int_id)
                    where pve_id ='" . $pve_id . "'";
                $conec = new ADO();
                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                ////
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
                $extra1.=" <a href=javascript:window.print();>Imprimir</a> 
                              <a href=javascript:self.close();>Cerrar</a></td>
                              </p>
                              </div>
                              </div>
                              <center>'";
                $extra2 = "'</center></body></html>'";

                echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
                              c.document.write(' . $extra1 . ');
                              var dato = document.getElementById(' . $pagina . ').innerHTML;
                              c.document.write(dato);
                              c.document.write(' . $extra2 . '); c.document.close();
                              ">
                            <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                            </a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="location.href=\'gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&id=' . $objeto->pve_vdo_id . '&form=pagadas\';"></td></tr></table>
                            ';
                $conversor = new convertir();
                ?>

                <br><br><div id="contenido_reporte" style="clear:both">
                    <center>
                        <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                <tr>
                    <td width="30%" >
                        <strong><?php echo _nombre_empresa; ?></strong><BR>
                        <strong>Santa Cruz - Bolivia</strong>
                    </td>
                    <td  width="40%" ><p align="center" ><strong><h3><center>COMPROBANTE DE PAGO DE COMISION<center></h3></strong></p></td>
                    <td  width="30%" ><div align="right"><img src="imagenes/micro.png" /></div></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>Vendedor: </strong> <?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?> 
                    </td>
                    <td align="right">
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>Fecha de Pago: </strong> <?php echo $conversor->get_fecha_latina($objeto->pve_fecha); ?>
                    </td>
                    <td align="right">
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>Moneda: </strong> <?php if ($objeto->pve_moneda == '1')
                    echo 'Bolivianos';
                else
                    echo 'Dolares';
                ?>
                    </td>
                    <td align="right">
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>Glosa: </strong> <?php echo $objeto->pve_glosa; ?>
                    </td>
                    <td align="right">
                    </td>
                </tr>

                </table>
        <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>Fecha Pago</th>
                    <th>Concepto</th>
                    <th>Monto</th>

                </tr>			
            </thead>
            <tbody>
        <?php
        $_comisiones = array();
        $_ids = explode(',', $objeto->pve_comisiones);
        $_montos = explode(',', $objeto->pve_montos);
        for ($i = 0; $i < count($_ids); $i++) {
            $_comisiones[$_ids[$i]] = $_montos[$i];
        }
        
        if ($objeto->pve_tipo_comision != 'FED') {
        
            $comisiones = FUNCIONES::objetos_bd_sql("
                select * from venta inner join comision on (ven_id=com_ven_id )
                where 
                com_id in($objeto->pve_comisiones)");
            for ($j = 0; $j < $comisiones->get_num_registros(); $j++) {
                $comision = $comisiones->get_objeto();
            ?>
            <tr>
                <td><?php echo $conversor->get_fecha_latina($comision->com_fecha_pag); ?></td>
                <td> <?php echo $comision->com_observacion; ?></td>
                <td><?php echo $_comisiones[$comision->com_id]; ?></td>
            <?php 
                $sum_monto += $_comisiones[$comision->com_id]; 
            ?>
            </tr>	
            <?php 
                $comisiones->siguiente(); 
            } 
        } else {
            ?>
            <tr>
                <td><?php echo $conversor->get_fecha_latina($objeto->pve_fecha); ?></td>
                <td> <?php echo $objeto->pve_glosa; ?></td>
                <td><?php echo $objeto->pve_monto; ?></td>
            </tr>
            <?php            
        }
            ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total</td>

                    <td><?php echo $sum_monto; ?></td>
                </tr>
            </tfoot>
        </table>

        <br><br><br><br>
        <table border="0"  width="90%" style="font-size:12px;">
            <tr>
                <td width="50%" align ="center">-------------------------------------</td>
                <td width="50%" align ="center">-------------------------------------</td>
            </tr>
            <tr>
                <td align ="center"><strong>Recibi Conforme</strong></td>
                <td align ="center"><strong>Entregue Conforme</strong></td>
            </tr>
        </table>

    </center>
    <br><br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr></table>
    </div>

        <?php
    }

    function listar_pagos_comisiones() {
        echo "<pre>";
//        print_r($_REQUEST);
        echo "</pre>";
        $ademas = "";
        
        if ($_REQUEST[urb_id] != '') {
            $ademas .= "&urb_id=$_REQUEST[urb_id]";
        }
        
        if ($_REQUEST[pdo_id] != '') {
            $ademas .= "&pdo_id=$_REQUEST[pdo_id]";
        }
        
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$_GET[id]'");
        $this->formulario->dibujar_titulo("BONOS DEL AFILIADO " . '"' . FUNCIONES::interno_nombre($vendedor->vdo_int_id) . '"');
        echo "<br>";
        ?>        
        <script>
            function anular_pago_comision(id) {
                var txt = '¿Está seguro de querer anular el pago de la comisión?';

                $.prompt(txt, {
                    buttons: {Aceptar: true, Cancelar: false},
                    callback: function(v, m, f) {
                        if (v) {
                            location.href = 'gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&acc=anular&pag_id=' + id;
                        }
                    }
                });
            }

            function ver_comprobante_pago_comision(id, flag) {
                var algomas = '';
                if (flag == 1) {
                    algomas = '&bono=ok';
                }
                location.href = 'gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&acc=ver&pag_id=' + id + algomas;
            }
        </script>

        <div class="aTabsCont" style="margin-left: 95px;">
            <div class="aTabsCent">
                <ul class="aTabs">

                    <li><a href="gestor.php?mod=<?php echo $this->modulo ?>&tarea=<?php echo $_GET['tarea'] ?>&id=<?php echo $_GET['id'] ?>&form=BIR<?php echo $ademas;?>" <?php if ($_GET['form'] == "BIR" || $_GET['form'] == "") { ?>class="activo" <?php } ?>>BIR</a></li>
                    <li><a href="gestor.php?mod=<?php echo $this->modulo ?>&tarea=<?php echo $_GET['tarea'] ?>&id=<?php echo $_GET['id'] ?>&form=BVI<?php echo $ademas;?>" <?php if ($_GET['form'] == "BVI") { ?>class="activo" <?php } ?>>BVI</a></li>
                    <li><a href="gestor.php?mod=<?php echo $this->modulo ?>&tarea=<?php echo $_GET['tarea'] ?>&id=<?php echo $_GET['id'] ?>&form=BRA<?php echo $ademas;?>" <?php if ($_GET['form'] == "BRA") { ?>class="activo" <?php } ?>>BRA</a></li>
                    <li><a href="gestor.php?mod=<?php echo $this->modulo ?>&tarea=<?php echo $_GET['tarea'] ?>&id=<?php echo $_GET['id'] ?>&form=FED<?php echo $ademas;?>" <?php if ($_GET['form'] == "FED") { ?>class="activo" <?php } ?>>FED</a></li>
                    <li><a href="gestor.php?mod=<?php echo $this->modulo ?>&tarea=<?php echo $_GET['tarea'] ?>&id=<?php echo $_GET['id'] ?>&form=pagadas<?php echo $ademas;?>" <?php if ($_GET['form'] == "pagadas") { ?>class="activo" <?php } ?>>Pagadas</a></li>
                </ul>
            </div>
        </div>

        <div id="ContenedorSeleccion">
    <?php
    if ($_GET['form'] == "BIR" || $_GET['form'] == "") {
        $this->formulario_comision('BIR');
    } elseif ($_GET['form'] == 'BVI') {
        $this->formulario_comision('BVI');
    } elseif ($_GET['form'] == 'BRA') {
        $this->formulario_comision('BRA');
    } elseif ($_GET['form'] == 'FED') {
        $this->formulario_fondos();
    } elseif ($_GET['form'] == 'pagadas') {
        $this->listar_todos_pagos();
    }
    ?>
        </div>

        <script>
            
            $('.pestania').click(function() {
                $('.pestania').each(function() {
                    $(this).removeClass('activo');
                    var div = $(this).attr('data-target');
                    $('.' + div).hide();
                });
                $(this).addClass('activo');
                var div = $(this).attr('data-target');
                $('.' + div).show();
            });
        </script>
        <br><br>

        <div class="div_directas" style="display: none;">
            <?php
    //                    $_POST[opcion] = 'todo';
    //                    $this->formulario_comision('');
            ?>
        </div>


        <div id="h_devoluciones" class="div_regalias" style="display: none;">
        <?php
    //                    $_POST[opcion] = 'todo';
    //                    $this->formulario_comision('regalia');
        ?>
        </div>

        <div class="div_bonos" style="display: none">
            div_bonos
        <?php
    //                    $_POST[opcion] = 'todo';
    //                    $this->formulario_comision();
        ?>
        </div>


        <?php
    }
    
    function listado_fondos(){
        $filtro="";
        if($_POST[opcion]=='gestion'){
            $filtro="and left(ven_fecha,7)='$_POST[anio]-$_POST[mes]'";
        }

        $sql="select *
                from comision
                where com_vdo_id='$_GET[id]' 
                    and com_estado='Pendiente'                         
                    $filtro
                    and com_tipo='FED'";
//            echo $sql;
        $comisiones=  FUNCIONES::objetos_bd_sql($sql);
        $listado=array();
        for ($i = 0; $i < $comisiones->get_num_registros(); $i++) {                
            $comision=$comisiones->get_objeto();

//                if ($this->se_podra_pagar($comision->com_fecha_cre, $comision->com_vdo_id)) {
                $fila=new stdClass();
                $fila->com_id=$comision->com_id;
//                    $fila->nro=$comision->ven_id;
                $fila->concepto=$comision->com_observacion;
//                    $fila->titular=$comision->int_nombre.' '.$comision->int_apellido;
//                    $fila->folio=$comision->ven_codigo;
                $fila->fecha=  FUNCIONES::get_fecha_latina($comision->com_fecha_cre);
//                    $fila->precio=$comision->ven_monto;
                $fila->tcomision=$comision->com_monto;
                $fila->fecha_asignacion=  FUNCIONES::get_fecha_latina($comision->com_fecha_cre);
                $fila->comision_asignada=$comision->com_monto;
                $fila->comision_pagada=$comision->com_pagado;
                $fila->comision_mes=round($comision->com_monto-$comision->com_pagado,2);

                $fila->tpagado=0;
                $fila->tsaldo=0;
                $listado[]=$fila;
//                }

            $comisiones->siguiente();
        }            
        return $listado;

    }
    
    function formulario_fondos($opciones = '') {
//        FUNCIONES::print_pre($_POST);
            ?>
            <script type="text/javascript" src="js/util.js"></script>
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <style>
                .fleft{
                    float: left;margin-left: 3px;
                }
                .cazul{
                    color:#0000ff;
                }
                .cnegro{
                    color:#000;
                }
            </style>
            <?php $listado=$this->listado_fondos(); ?>
            <?php if(count($listado)>0){?>
                <form id="frm_eliminar" name="frm_eliminar" action="" method="POST" enctype="multipart/form-data">  
                    <input type="hidden" name="opcion" value="<?php echo $_POST[opcion];?>">
                    <input type="hidden" name="anio" value="<?php echo $_POST[anio];?>">
                    <input type="hidden" name="mes" value="<?php echo $_POST[mes];?>">
                    <input type="hidden" name="urb_id" value="<?php echo $_POST[urb_id];?>">
                </form>
                <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <!--<th>Nro venta</th>-->
                            <!--<th>Titular</th>-->                            
                            <th>Fecha Asignacion</th>
                            <th>Observacion</th>
                            <th>Comision del Mes</th>                            
                            <th>Pagado</th>                            
                            <th>Saldo</th>
                            <?php
                            if ($opciones == '') {
                            ?>
                            <th class="tOpciones"><input type="checkbox" class="checkprincipal" name="chk0"></th> 
                            <th class="tOpciones">&nbsp;</th> 
                            <?php
                            }
                            ?>
                        </tr>	
                    </thead>

                    <?php $tmonto_v=0; ?>
                    <?php $tmonto_mes=0; ?>
                    <?php $tpagado=0; ?>
                    <?php $tsaldo=0; ?>
                    <tbody>
                        <?php foreach ($listado as $fila) {?>
                            <tr>
<!--                                <td><?php echo $fila->nro;?>&nbsp;</td>
                                <td><?php echo $fila->titular;?>&nbsp;</td>-->
                                <td><?php echo $fila->fecha_asignacion;?>&nbsp;</td>
                                <td><?php echo $fila->concepto;?>&nbsp;</td>                                
                                <td><?php echo number_format($fila->comision_asignada, 2);?></td>
                                <td><?php echo number_format($fila->comision_pagada, 2);?></td>
                                <td><b><?php echo number_format($fila->comision_mes, 2);?></b>&nbsp;</td>
                                <?php
                            if ($opciones == '') {
                            ?>
                                <td>
                                    <input type="checkbox" class="checkdetalle fleft" data-monto="<?php echo $fila->comision_mes;?>" data-id="<?php echo $fila->com_id;?>">&nbsp;
                                    <input type="text" class="comision_mes fleft" value="" size="5" data-id="<?php echo $fila->com_id;?>">&nbsp;
                                </td>
                                <td>
                                    <?php if($fila->comision_pagada==0){?>
                                    <a class="linkOpciones" title="ELIMINAR" href="javascript:eliminar_comision(<?php echo $fila->com_id?>);" >
                                        <img width="16" border="0" alt="ELIMINAR" src="images/b_drop.png">
                                    </a>
                                    <?php }?>
                                </td>
                                
                            <?php 
                            
                                    }
                            $tmonto_mes+=$fila->comision_mes; 
                            ?>                                
                            </tr>                       
                        <?php }?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right;">Total </td>
                            <td ><?php echo number_format($tmonto_mes, 2);?></td>
                                <?php
                            if ($opciones == '') {
                            ?>
                            <td >&nbsp;</td>
                            <td >&nbsp;</td>
                            <?php
                            }
                            ?>
                        </tr>
                        <?php
                            if ($opciones == '') {
                            ?>
                        <tr>
                            <td colspan="4" style="text-align: right;">Total Seleccionado</td>
                            <td ><span id="monto_marcado"></span></td>
                            
                            <td >&nbsp;</td>
                            <td >&nbsp;</td>
                                                        
                        </tr>
                        <?php
                            }
                            ?>
                    </tfoot>
                </table>
                <form id="frm_vendedor" name="frm_vendedor" action="gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&tipo=FED&acc=pagar<?php echo '&id='.$_GET['id']; ?>" method="POST" enctype="multipart/form-data">
                    <div id="ContenedorDiv" style="display: <?php echo $opciones==''?'block':'none';?>">
                        <div id="CajaBotones">
                            <input type="hidden" name="comision_a_pagar" id="comision_a_pagar" value="">
                            <input type="hidden" name="com_ids" id="com_ids" value="">
                            <input type="hidden" name="com_montos" id="com_montos" value="">
                            <input type="hidden" name="com_moneda" id="com_moneda" value="2">
                            <input type="hidden" name="urb_id" id="urb_id" value="<?php echo $_POST[urb_id];?>">
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Comision a Pagar</div>
                                <div id="CajaInput">
                                    <input  name="txt_comision" type="text" id="txt_comision" value="" size="25" readonly=""/>
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Glosa</div>
                                <div id="CajaInput">
                                    <input  name="pag_glosa" type="text" id="pag_glosa" value="<?php echo $_POST['pag_glosa']; ?>" size="70"/>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                <div id="CajaInput">
                                    <input class="caja_texto" name="pag_fecha" id="pag_fecha" size="12" value="<?php if (isset($_POST['pag_fecha'])) echo $_POST['pag_fecha'];else echo date("d/m/Y"); ?>" type="text">
                                </div>
                            </div>
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><b>Pagos</b></div>                                
                                <?php FORMULARIO::frm_pago(array('cmp_fecha'=>'pag_fecha','cmp_monto'=>'comision_a_pagar','cmp_moneda'=>'com_moneda'));?>
                            </div>
                            <div style="text-align: left;">
                                <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario_pago_comision();">                            
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&id=<?php echo $_GET['id']; ?>';">                                            
                            </div>
                        </div>
                    </div>
                </form>
                <script>
                    mask_decimal('.comision_mes',null);
                    function eliminar_comision(id){
                        var txt = '?Est? seguro de querer anular el la Comision Asignada?';
                        $.prompt(txt, {
                            buttons: {Aceptar: true, Cancelar: false},
                            callback: function(v, m, f) {
                                if (v) {
                                    $('#frm_eliminar').attr('action','gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&acc=anular_comision&id=<?php echo $_GET[id];?>&com_id='+id);
                                    document.frm_eliminar.submit();
                                }
                            }
                        });
                        
                    }
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
//                        var marcados=$('.checkdetalle[type=checkbox]:checked"');
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
                        
                        $('#com_ids').val(comisiones);
                        $('#com_montos').val(montos);
                        $('#comision_a_pagar').val(total);
                        $('#txt_comision').val(total.toFixed(2));
                        $('#monto_marcado').text(total);
                        $('#comision_a_pagar').trigger('focusout');
                    }
                    function enviar_formulario_pago_comision(){
                        console.info('aaa');
                        var comision_a_pagar = document.getElementById('comision_a_pagar').value;
                        var glosa = document.getElementById('pag_glosa').value;                        
                        var fecha = document.getElementById('pag_fecha').value;
                        if(comision_a_pagar === '' || (comision_a_pagar*1) === 0){
                            $.prompt('El monto de la comision a pagar no debe ser vacio o cero.');
                            return false;
                        }                        
                        
                        if(glosa === ''){                            
                            $.prompt('La glosa de la comision a pagar no debe ser vacio.');
                            return false;
                        }                        
                        if(fecha!==''){
                            $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                var dato = JSON.parse(respuesta);
                                if (dato.response !== "ok") {
                                    $.prompt(dato.mensaje);                                    

                                }else{
                                    if(!validar_fpag_montos(dato.cambios)){
                                        $.prompt('El monto a Pagar no cocuerda con los pagos realizados');
                                        return false;
                                    }
                                    document.frm_vendedor.submit();
                                }
                            });
                        }else{
                            $.prompt('La fecha del pago de la comision no debe ser vacio.');
                        }
                    }
                    jQuery(function($) {
                        $("#pag_fecha").mask("99/99/9999");
                    });
                </script>
            <?php }else{?>
                <?php
//                $mensaje = 'No existe comisiones pendientes para este periodo';
                $mensaje = 'No existen pagos pendientes de este tipo.';
//                $this->formulario->dibujar_tarea(); 
//                $this->formulario->mensaje("",$mensaje); 
                $ruta = $this->link . '?mod=' . $this->modulo . "&tarea=" . $_GET[tarea] . "&id=" . $_GET[id];
                $this->formulario->ventana_volver($mensaje, $ruta, "", "");
                ?>
            <?php }?>
            <?php
        }

    function listar_todos_pagos($vdo = '') {
//            echo "listando pagos";
        $this->listar_pagos('BIR', $vdo);
        $this->listar_pagos('BVI', $vdo);
        $this->listar_pagos('BRA', $vdo);
        $this->listar_pagos('FED', $vdo);
    }

    function listar_pagos($tipo = '', $vdo = '') {
        $algomas = "0";
        if ($tipo == 'BIR') {
            $tipo_comision = "BONO BIR";
        } elseif ($tipo == 'BVI') {
            $tipo_comision = "BONO BVI";
        } elseif ($tipo == 'BRA') {
            $tipo_comision = "BONO BRA";
//            $algomas = "1";
        } elseif ($tipo == 'FED') {
            $tipo_comision = "BONO FED";
//            $algomas = "1";
        }
        ?>
        <div id="h_pagos" class="div_pagadas" >
            <center>
                <h2>HISTORIAL DE PAGOS DE <?php echo $tipo_comision; ?></h2>
                <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Monto</th>                                
                            <th>Usuario</th>
                            <?php
                            if ($tipo != 'FED') {
                                ?>
                                <th>Ventas</th>
                            <?php
                        }
                        ?>
                            <th>Estado</th>
                        <?php
                        if ($vdo == '') {
                            ?>
                                <th class="tOpciones">Opciones</th> 
                            <?php
                        }
                        ?>

                        </tr>	
                    </thead>
                    <tbody>
                        <?php
                        $sql = "select * from pago_vendedores where pve_vdo_id='" . $_GET['id'] .
                                "' and pve_estado!='Anulado' and pve_monto>0 
and pve_tipo_comision='$tipo'";
                        $conec = new ADO();
                        $conec->ejecutar($sql);
                        $num = $conec->get_num_registros();
                        $conv = new convertir();
                        ?>
                        <?php
                        for ($i = 0; $i < $num; $i++) {
                            $obj = $conec->get_objeto();
                            echo '<tr>';

                            echo '<td>';
                            echo $conv->get_fecha_latina($obj->pve_fecha);
                            echo '&nbsp;</td>';

                            echo '<td style="text-align:right">';
                            echo $obj->pve_monto;
                            echo '&nbsp;</td>';

                            echo '<td>';
                            echo $obj->pve_usu_id;
                            echo '&nbsp;</td>';

                            if ($tipo != 'FED') {
                                echo '<td>';
                                $comisiones = FUNCIONES::objetos_bd_sql("select * from comision where com_id in($obj->pve_comisiones)");
                                for ($j = 0; $j < $comisiones->get_num_registros(); $j++) {
                                    $comision = $comisiones->get_objeto();
                                    if ($j > 0)
                                        echo ',';
                                    echo $comision->com_ven_id;
                                    $comisiones->siguiente();
                                }
                                echo '&nbsp;</td>';
                            }


                            echo '<td>';
                            echo $obj->pve_estado;
                            echo '&nbsp;</td>';

                            if ($vdo == '') {

                                echo '<td>';
                                ?>
                            <center>
                                <table>
                                    <tr>
                                        <td><a class="linkOpciones" href="javascript:ver_comprobante_pago_comision('<?php echo $obj->pve_id; ?>','<?php echo $algomas; ?>');">
                                                <img src="images/ver.png" border="0" title="VER COMPROBANTE" alt="ver">
                                            </a>
                                        </td>
                            <?php
                            if ($obj->pve_estado != 'Anulado') {
                                ?>
                                            <td><a class="linkOpciones" href="javascript:anular_pago_comision('<?php echo $obj->pve_id; ?>');">
                                                    <img src="images/anular.png" border="0" title="ANULAR PAGO COMISION" alt="anular">
                                                </a>
                                            </td>
<?php
}
?>
                                    </tr>
                                </table>
                            </center>

                <?php
                echo '</td>';
            }
            echo '</tr>';

            $conec->siguiente();
        }
        ?>

                    </tbody>
                </table>
            </center>
        </div>
        <br/>
        <br/>
<?php
}

    function listar_pagos_comisiones_() {
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$_GET[id]'");
        ?>
                                                    <div id="Contenedor_NuevaSentencia">
                                                        <form id="frm_vendedor" name="frm_vendedor" action="gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&acc=form<?php echo '&id=' . $_GET['id']; ?>" method="POST" enctype="multipart/form-data">  
                                                            <div id="FormSent">
                                                                <div class="Subtitulo">Pagar Vendedor</div>
                                                                <div id="ContenedorSeleccion">                                    
                                                                    <!--Inicio-->
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" >Total Comisionado:</div>
                                                                        <div id="CajaInput" >
                                                                            <div class="read-input"><?php echo FUNCIONES::interno_nombre($vendedor->vdo_int_id); ?></div>
                                                                        </div>
                                                                    </div>
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" >Total Comisionado:</div>
                                                                        <div id="CajaInput" style="margin-top: 3px">
                                                                            &nbsp;&nbsp;<b><?php $total_comisionado = $this->total_comisionado($_GET['id']);
                                                                        echo number_format($total_comisionado, 2, '.', ',');
                                                                        ?></b>
                                                                        </div>
                                                                    </div>
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" >Comisi&oacute;n Pagada:</div>
                                                                        <div id="CajaInput" style="margin-top: 3px">
                                                                            &nbsp;&nbsp;<b><?php $comision_pagada = $this->comision_pagada($_GET['id']);
                                                                        echo number_format($comision_pagada, 2, '.', ',');
        ?></b>
                                                                        </div>
                                                                    </div>
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" >Saldo Comisi&oacute;n:</div>
                                                                        <div id="CajaInput" style="margin-top: 3px">
                                                                            &nbsp;&nbsp;<b><?php echo number_format($total_comisionado - $comision_pagada, 2, '.', ','); ?></b>
                                                                        </div>
                                                                    </div>
                                                                    <!--                                    <div id="ContenedorDiv" >
                                                                                                            <div class="Etiqueta" >Urbanizacion: </div>
                                                                                                            <div id="CajaInput">
                                                                                                                <select name="urb_id" id="urb_id">
        <?php
//                                                $fun=new FUNCIONES();
//                                                $fun->combo("select urb_id as id, urb_nombre as nombre from urbanizacion", $_POST[urb_id]);
        ?>
                                                                                                                </select>
                                                                                                            </div>
                                                                                                        </div>-->
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" >Pagar: </div>
                                                                        <div id="CajaInput">
                                                                            <select name="opcion" id="opcion">
                                                                                <option value="todo">Todo</option>
                                                                                <option value="gestion" selected="">Por Gesti&oacute;n</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div id="pag_gestion">
                                                                        <div id="ContenedorDiv">
                                                                            <div class="Etiqueta" >A&ntilde;o</div>
                                                                            <div id="CajaInput">
                                                                                <input type="text" id="anio" name="anio" maxlength="4" value="<?php echo date('Y'); ?>">
                                                                            </div>
                                                                        </div>
        <?php $_mes = date('m'); ?>
                                                                        <div id="ContenedorDiv">
                                                                            <div class="Etiqueta" >Mes</div>
                                                                            <div id="CajaInput">
                                                                                <select name="mes">
                                                                                    <option value="01" <?php echo $_mes == 1 ? 'selected=""' : ''; ?>>Enero</option>
                                                                                    <option value="02" <?php echo $_mes == 2 ? 'selected=""' : ''; ?>>Febrero</option>
                                                                                    <option value="03" <?php echo $_mes == 3 ? 'selected=""' : ''; ?>>Marzo</option>
                                                                                    <option value="04" <?php echo $_mes == 4 ? 'selected=""' : ''; ?>>Abril</option>
                                                                                    <option value="05" <?php echo $_mes == 5 ? 'selected=""' : ''; ?>>Mayo</option>
                                                                                    <option value="06" <?php echo $_mes == 6 ? 'selected=""' : ''; ?>>Junio</option>
                                                                                    <option value="07" <?php echo $_mes == 7 ? 'selected=""' : ''; ?>>Julio</option>
                                                                                    <option value="08" <?php echo $_mes == 8 ? 'selected=""' : ''; ?>>Agosto</option>
                                                                                    <option value="09" <?php echo $_mes == 9 ? 'selected=""' : ''; ?>>Septiembre</option>
                                                                                    <option value="10" <?php echo $_mes == 10 ? 'selected=""' : ''; ?>>Octubre</option>
                                                                                    <option value="11" <?php echo $_mes == 11 ? 'selected=""' : ''; ?>>Noviembre</option>
                                                                                    <option value="12" <?php echo $_mes == 12 ? 'selected=""' : ''; ?>>Diciembre</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div id="ContenedorDiv">
                                                                        <div id="CajaBotones">
                                                                            <center>
                                                                                <input type="submit" class="boton" name="" value="Generar" >
                                                                            </center>
                                                                        </div>
                                                                    </div>
                                                                    <script>
                                                                        $('#opcion').change(function() {
                                                                            var valor = $('#opcion option:selected').val();
                                                                            if (valor === 'todo') {
                                                                                $('#pag_gestion').hide();
                                                                            } else if (valor === 'gestion') {
                                                                                $('#pag_gestion').show();
                                                                            }
                                                                        });
                                                                        $('#opcion').trigger('change');
                                                                    </script>                                        
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <script>
                                                        function anular_pago_comision(id) {
                                                            var txt = 'Esta seguro de querer anular el pago de la comision?';

                                                            $.prompt(txt, {
                                                                buttons: {Aceptar: true, Cancelar: false},
                                                                callback: function(v, m, f) {
                                                                    if (v) {
                                                                        location.href = 'gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&acc=anular&pag_id=' + id;
                                                                    }
                                                                }
                                                            });
                                                        }

                                                        function ver_comprobante_pago_comision(id) {
                                                            location.href = 'gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&acc=ver&pag_id=' + id;
                                                        }
                                                    </script>

                                                    <div class="aTabsCont" style="margin-left: 95px;">
                                                        <div class="aTabsCent">
                                                            <ul class="aTabs">
                                                                <li><a href="javascript:void(0)" class="activo" id="tabs_pag" >Pagos</a></li>
                                                                <!--<li><a href="javascript:void(0)" id="tabs_dev">Devoluciones</a></li>-->                            
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <script>
                                                        $('#tabs_pag').click(function() {
                                                            $('#h_pagos').show();
                                                            $('#h_devoluciones').hide();
                                                            $('.activo').removeClass('activo');
                                                            $('#tabs_pag').addClass('activo');
                                                        });
                                                        $('#tabs_dev').click(function() {
                                                            $('#h_pagos').hide();
                                                            $('#h_devoluciones').show();
                                                            $('.activo').removeClass('activo');
                                                            $('#tabs_dev').addClass('activo');
                                                        });
                                                    </script>
                                                    <br><br>
                                                    <div id="h_pagos">
                                                        <center>
                                                            <h2>HISTORIAL DE PAGOS</h2>
                                                            <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Fecha</th>
                                                                        <th>Monto</th>                                
                                                                        <th>Usuario</th>
                                                                        <th>Ventas</th>
                                                                        <th>Estado</th>
                                                                        <th class="tOpciones">Opciones</th> 
                                                                    </tr>	
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $sql = "select * from pago_vendedores where pve_vdo_id='" . $_GET['id'] . "' and pve_estado!='Anulado' and pve_monto>0";
                                                                    $conec = new ADO();
                                                                    $conec->ejecutar($sql);
                                                                    $num = $conec->get_num_registros();
                                                                    $conv = new convertir();
                                                                    ?>
                                                                    <?php
                                                                    for ($i = 0; $i < $num; $i++) {
                                                                        $obj = $conec->get_objeto();
                                                                        echo '<tr>';

                                                                        echo '<td>';
                                                                        echo $conv->get_fecha_latina($obj->pve_fecha);
                                                                        echo '&nbsp;</td>';

                                                                        echo '<td style="text-align:right">';
                                                                        echo $obj->pve_monto;
                                                                        echo '&nbsp;</td>';

                                                                        echo '<td>';
                                                                        echo $obj->pve_usu_id;
                                                                        echo '&nbsp;</td>';

                                                                        echo '<td>';
                                                                        $comisiones = FUNCIONES::objetos_bd_sql("select * from comision where com_id in($obj->pve_comisiones)");
                                                                        for ($j = 0; $j < $comisiones->get_num_registros(); $j++) {
                                                                            $comision = $comisiones->get_objeto();
                                                                            if ($j > 0)
                                                                                echo ',';
                                                                            echo $comision->com_ven_id;
                                                                            $comisiones->siguiente();
                                                                        }
                                                                        echo '&nbsp;</td>';

                                                                        echo '<td>';
                                                                        echo $obj->pve_estado;
                                                                        echo '&nbsp;</td>';

                                                                        echo '<td>';
                                                                        ?>
                                                                    <center>
                                                                        <table>
                                                                            <tr>
                                                                                <td><a class="linkOpciones" href="javascript:ver_comprobante_pago_comision('<?php echo $obj->pve_id; ?>');">
                                                                                        <img src="images/ver.png" border="0" title="VER COMPROBANTE" alt="ver">
                                                                                    </a>
                                                                                </td>
                                                                    <?php
                                                                    if ($obj->pve_estado != 'Anulado') {
                                                                        ?>
                                                                                    <td><a class="linkOpciones" href="javascript:anular_pago_comision('<?php echo $obj->pve_id; ?>');">
                                                                                            <img src="images/anular.png" border="0" title="ANULAR PAGO COMISION" alt="anular">
                                                                                        </a>
                                                                                    </td>
                <?php
            }
            ?>
                                                                            </tr>
                                                                        </table>
                                                                    </center>

            <?php
            echo '</td>';

            echo '</tr>';

            $conec->siguiente();
        }
        ?>

                                                                </tbody>
                                                            </table>
                                                        </center>
                                                    </div>
                                                    <div id="h_devoluciones" style="display: none;">
                                                        <center>
                                                            <h2>HISTORIAL DE DEVOLUCIONES</h2>
                                                            <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Fecha</th>
                                                                        <th>Monto</th>                                
                                                                        <th>Usuario</th>
                                                                        <th>Estado</th>
                                                                        <th class="tOpciones">Opciones</th> 
                                                                    </tr>	
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $sql = "select * from pago_vendedores where pve_vdo_id='" . $_GET['id'] . "' and pve_estado!='Anulado' and pve_monto<0";
                                                                    $conec = new ADO();
                                                                    $conec->ejecutar($sql);
                                                                    $num = $conec->get_num_registros();
                                                                    $conv = new convertir();
                                                                    ?>
                                                                    <?php
                                                                    for ($i = 0; $i < $num; $i++) {
                                                                        $obj = $conec->get_objeto();
                                                                        echo '<tr>';

                                                                        echo '<td>';
                                                                        echo $conv->get_fecha_latina($obj->pve_fecha);
                                                                        echo '&nbsp;</td>';

                                                                        echo '<td style="text-align:right">';
                                                                        echo $obj->pve_monto * -1;
                                                                        echo '&nbsp;</td>';

                                                                        echo '<td>';
                                                                        echo $obj->pve_usu_id;
                                                                        echo '&nbsp;</td>';

                                                                        echo '<td>';
                                                                        echo $obj->pve_estado;
                                                                        echo '&nbsp;</td>';

                                                                        echo '<td>';
                                                                        ?>
                                                                    <center>
                                                                        <table>
                                                                            <tr>
                                                                                <td><a class="linkOpciones" href="javascript:ver_comprobante_pago_comision('<?php echo $obj->pve_id; ?>');">
                                                                                        <img src="images/ver.png" border="0" title="VER COMPROBANTE" alt="ver">
                                                                                    </a>
                                                                                </td>
                                                                    <?php
                                                                    if ($obj->pve_estado != 'Anulado') {
                                                                        ?>
                                                                                    <td><a class="linkOpciones" href="javascript:anular_pago_comision('<?php echo $obj->pve_id; ?>');">
                                                                                            <img src="images/anular.png" border="0" title="ANULAR PAGO COMISION" alt="anular">
                                                                                        </a>
                                                                                    </td>
                <?php
            }
            ?>
                                                                            </tr>
                                                                        </table>
                                                                    </center>

                                                        <?php
                                                        echo '</td>';

                                                        echo '</tr>';

                                                        $conec->siguiente();
                                                    }
                                                    ?>

                                                                </tbody>
                                                            </table>
                                                        </center>
                                                    </div>
        <?php
    }

    function formulario_comision($tipo = '', $opciones = '') {
//        FUNCIONES::print_pre($_POST);
        ?>
                                                    <script type="text/javascript" src="js/util.js"></script>
                                                    <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                                                    <style>
                                                        .fleft{
                                                            float: left;margin-left: 3px;
                                                        }
                                                        .cazul{
                                                            color:#0000ff;
                                                        }
                                                        .cnegro{
                                                            color:#000;
                                                        }
                                                    </style>
        <?php $listado = $this->listado_comisiones($tipo); ?>
                                                                <?php if (count($listado) > 0) { ?>
                                                        <form id="frm_eliminar" name="frm_eliminar" action="" method="POST" enctype="multipart/form-data">  
                                                            <input type="hidden" name="opcion" value="<?php echo $_POST[opcion]; ?>">
                                                            <input type="hidden" name="anio" value="<?php echo $_POST[anio]; ?>">
                                                            <input type="hidden" name="mes" value="<?php echo $_POST[mes]; ?>">
                                                            <input type="hidden" name="urb_id" value="<?php echo $_POST[urb_id]; ?>">
                                                        </form>
                                                        <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nro venta</th>
                                                                    <th>Titular</th>                            
                                                                    <th>Fecha Asignacion</th>
                                                                    <th>Observacion</th>
                                                                    <th>Comision del Mes</th>                            
                                                                    <th>Pagado</th>                            
                                                                    <th>Saldo</th>                            
                                                                <?php
                                                                if ($opciones == '') {
                                                                    ?>
                                                                        <th class="tOpciones"><input type="checkbox" class="checkprincipal" name="chk0"></th> 
                                                                        <th class="tOpciones">&nbsp;</th>     
                <?php
            }
            ?>
                                                                </tr>	
                                                            </thead>

                                                                    <?php $tmonto_v = 0; ?>
            <?php $tmonto_mes = 0; ?>
            <?php $tpagado = 0; ?>
            <?php $tsaldo = 0; ?>
                                                            <tbody>
                                                                        <?php foreach ($listado as $fila) { ?>
                                                                    <tr>
                                                                        <td><?php echo $fila->nro . "<b>($fila->periodo)</b>";?>&nbsp;</td>
                                                                        <td><?php echo $fila->titular; ?>&nbsp;</td>
                                                                        <td><?php echo $fila->fecha_asignacion; ?>&nbsp;</td>
                                                                        <td><?php echo $fila->concepto; ?>&nbsp;</td>                                
                                                                        <td><?php echo number_format($fila->comision_asignada, 2); ?></td>
                                                                        <td><?php echo number_format($fila->comision_pagada, 2); ?></td>
                                                                        <td><b><?php echo number_format($fila->comision_mes, 2); ?></b>&nbsp;</td>
                                                                        <?php
                                                                        if ($opciones == '') {
                                                                            ?>
                                                                            <td>
                                                                                <input type="checkbox" class="checkdetalle fleft" data-monto="<?php echo $fila->comision_mes; ?>" data-id="<?php echo $fila->com_id; ?>">&nbsp;
                                                                                <input type="text" class="comision_mes fleft" value="" size="5" data-id="<?php echo $fila->com_id; ?>">&nbsp;
                                                                            </td>
                                                                            <td>
                                                                            <?php if ($fila->comision_pagada == 0) { ?>
                                                                                    <a class="linkOpciones" title="ELIMINAR" href="javascript:eliminar_comision(<?php echo $fila->com_id ?>);" >
                                                                                        <img width="16" border="0" alt="ELIMINAR" src="images/b_drop.png">
                                                                                    </a>
                    <?php } ?>
                                                                            </td>
                                                                            <?php
                                                                        }
                                                                        $tmonto_mes+=$fila->comision_mes;
                                                                        ?>                                
                                                                    </tr>                       
                                                                <?php } ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td colspan="6" style="text-align: right;">Total </td>
                                                                    <td ><?php echo number_format($tmonto_mes, 2); ?></td>
            <?php
            if ($opciones == '') {
                ?>
                                                                        <td >&nbsp;</td>
                                                                        <td >&nbsp;</td>
                                                                    <?php
                                                                }
                                                                ?>
                                                                </tr>
            <?php
            if ($opciones == '') {
                ?>
                                                                    <tr>
                                                                        <td colspan="6" style="text-align: right;">Total Seleccionado</td>
                                                                        <td ><span id="monto_marcado"></span></td>
                                                                        <td >&nbsp;</td>
                                                                        <td >&nbsp;</td>

                                                                    </tr>
                <?php
            }
            ?>
                                                            </tfoot>
                                                        </table>
                                                        <form id="frm_vendedor" name="frm_vendedor" action="gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&acc=pagar&tipo=<?php echo $tipo;?><?php echo '&id=' . $_GET['id']; ?>" method="POST" enctype="multipart/form-data">
                                                            <div id="ContenedorDiv" style="display:<?php echo $opciones == '' ? 'block' : 'none'; ?>">
                                                                <div id="CajaBotones">
                                                                    <input type="hidden" name="comision_a_pagar" id="comision_a_pagar" value="">
                                                                    <input type="hidden" name="com_ids" id="com_ids" value="">
                                                                    <input type="hidden" name="com_montos" id="com_montos" value="">
                                                                    <input type="hidden" name="com_moneda" id="com_moneda" value="2">
                                                                    <input type="hidden" name="urb_id" id="urb_id" value="<?php echo $_POST[urb_id]; ?>">
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" ><span class="flechas1">*</span>Comision a Pagar</div>
                                                                        <div id="CajaInput">
                                                                            <input  name="txt_comision" type="text" id="txt_comision" value="" size="25" readonly=""/>
                                                                        </div>
                                                                    </div>
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" ><span class="flechas1">*</span>Glosa</div>
                                                                        <div id="CajaInput">
                                                                            <input  name="pag_glosa" type="text" id="pag_glosa" value="<?php echo $_POST['pag_glosa']; ?>" size="70"/>
                                                                        </div>
                                                                    </div>
                                                                    <!--Fin-->
                                                                    <!--Inicio-->
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                                                        <div id="CajaInput">
                                                                            <input class="caja_texto" name="pag_fecha" id="pag_fecha" size="12" value="<?php if (isset($_POST['pag_fecha']))
                echo $_POST['pag_fecha'];
            else
                echo date("d/m/Y");
            ?>" type="text">
                                                                        </div>
                                                                    </div>
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" ><b>Pagos</b></div>                                
            <?php FORMULARIO::frm_pago(array('cmp_fecha' => 'pag_fecha', 'cmp_monto' => 'comision_a_pagar', 'cmp_moneda' => 'com_moneda')); ?>
                                                                    </div>
                                                                    <div style="text-align: left;">
                                                                        <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario_pago_comision();">                            
                                                                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&id=<?php echo $_GET['id']; ?>';">                                            
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                        <script>
                                                            mask_decimal('.comision_mes', null);
                                                            function eliminar_comision(id) {
                                                                var txt = '?Est? seguro de querer anular el la Comision Asignada?';
                                                                $.prompt(txt, {
                                                                    buttons: {Aceptar: true, Cancelar: false},
                                                                    callback: function(v, m, f) {
                                                                        if (v) {
                                                                            $('#frm_eliminar').attr('action', 'gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&acc=anular_comision&id=<?php echo $_GET[id]; ?>&com_id=' + id);
                                                                            document.frm_eliminar.submit();
                                                                        }
                                                                    }
                                                                });

                                                            }
                                                            $('.checkprincipal').click(function() {
                                                                var check = $(this).prop('checked');
                                                                var detalles = $('.checkdetalle');
                                                                for (var i = 0; i < detalles.size(); i++) {
                                                                    if (check) {
                                                                        $(detalles[i]).prop('checked', 'checked');
                                                                        var monto = $(detalles[i]).attr('data-monto') * 1;
                                                                        $(detalles[i]).next('input').val(monto);
                                                                        $(detalles[i]).next('input').addClass('cazul');
                                                                    } else {
                                                                        $(detalles[i]).prop('checked', '');
                                                                        $(detalles[i]).next('input').val('');
                                                                        $(detalles[i]).next('input').removeClass('cazul');
                                                                    }
                                                                }
                                                                sumar_montos();
                                                            });
                                                            $('.comision_mes').focusout(function() {
                                                                var monto = $(this).prev('input').attr('data-monto') * 1;
                                                                var valor = $(this).val() * 1;
                                                                if (valor > monto) {
                                                                    $(this).prev('input').prop('checked', 'checked');
                                                                    $(this).val(monto);
                                                                    $(this).addClass('cazul');
                                                                } else if (valor === monto) {
                                                                    $(this).prev('input').prop('checked', 'checked');
                                                                    $(this).addClass('cazul');
                                                                } else if (valor < 0) {
                                                                    $(this).prev('input').prop('checked', '');
                                                                    $(this).val(monto);
                                                                    $(this).removeClass('cazul');
                                                                } else {
                                                                    $(this).prev('input').prop('checked', '');
                                                                    $(this).removeClass('cazul');
                                                                }
                                                                sumar_montos();
                                                            });
                                                            $('.checkdetalle').click(function() {
                                                                var detalles = $('.checkdetalle');
                                                                var marcados = $('.checkdetalle[type=checkbox]:checked"');
                                                                if (detalles.size() === marcados.size()) {
                                                                    $('.checkprincipal').prop('checked', 'checked');
                                                                } else {
                                                                    $('.checkprincipal').prop('checked', '');
                                                                }
                                                                var check = $(this).prop('checked');
                                                                if (check) {
                                                                    var monto = $(this).attr('data-monto') * 1;
                                                                    $(this).next('input').val(monto);
                                                                    $(this).next('input').addClass('cazul');
                                                                } else {
                                                                    $(this).next('input').val('');
                                                                    $(this).next('input').removeClass('cazul');
                                                                }


                                                                sumar_montos();
                                                            });
                                                            function sumar_montos() {
                                                                //                        var marcados=$('.checkdetalle[type=checkbox]:checked"');
                                                                var marcados = $('.comision_mes');
                                                                var total = 0;
                                                                var comisiones = '';
                                                                var montos = '';
                                                                var j = 0;
                                                                for (var i = 0; i < marcados.size(); i++) {
                                                                    var monto = $(marcados[i]).val() * 1;
                                                                    if (monto > 0) {
                                                                        if (j > 0) {
                                                                            comisiones += ',';
                                                                        }
                                                                        if (j > 0) {
                                                                            montos += ',';
                                                                        }
                                                                        var com_id = $(marcados[i]).attr('data-id');
                                                                        //                            var com_monto=$(marcados[i]).val();
                                                                        comisiones += com_id;
                                                                        montos += monto + '';
                                                                        total += monto;
                                                                        j++;
                                                                    }
                                                                }

                                                                $('#com_ids').val(comisiones);
                                                                $('#com_montos').val(montos);
                                                                $('#comision_a_pagar').val(total);
                                                                $('#txt_comision').val(total.toFixed(2));
                                                                $('#monto_marcado').text(total);
                                                                $('#comision_a_pagar').trigger('focusout');
                                                            }
                                                            function enviar_formulario_pago_comision() {
                                                                console.info('aaa');
                                                                var comision_a_pagar = document.getElementById('comision_a_pagar').value;
                                                                var glosa = document.getElementById('pag_glosa').value;
                                                                var fecha = document.getElementById('pag_fecha').value;
                                                                if (comision_a_pagar === '' || (comision_a_pagar * 1) === 0) {
                                                                    $.prompt('El monto de la comision a pagar no debe ser vacio o cero.');
                                                                    return false;
                                                                }

                                                                if (glosa === '') {
                                                                    $.prompt('La glosa de la comision a pagar no debe ser vacio.');
                                                                    return false;
                                                                }
                                                                if (fecha !== '') {
                                                                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                                                        var dato = JSON.parse(respuesta);
                                                                        if (dato.response !== "ok") {
                                                                            $.prompt(dato.mensaje);

                                                                        } else {
                                                                            if (!validar_fpag_montos(dato.cambios)) {
                                                                                $.prompt('El monto a Pagar no cocuerda con los pagos realizados');
                                                                                return false;
                                                                            }
                                                                            document.frm_vendedor.submit();
                                                                            //                                        console.log('enviando formulario......');
                                                                        }
                                                                    });
                                                                } else {
                                                                    $.prompt('La fecha del pago de la comision no debe ser vacio.');
                                                                }
                                                            }
                                                            jQuery(function($) {
                                                                $("#pag_fecha").mask("99/99/9999");
                                                            });
                                                        </script>
        <?php }else { ?>
            <?php
//                $mensaje = 'No existe comisiones pendientes para este periodo';
            $mensaje = 'No existen pagos pendientes de este tipo.';
//            $this->formulario->dibujar_tarea();
//            $this->formulario->mensaje("", $mensaje);
            $ruta = $this->link . '?mod=' . $this->modulo . "&tarea=" . $_GET[tarea] . "&id=" . $_GET[id];
            $this->formulario->ventana_volver($mensaje, $ruta, "", "");
            ?>
        <?php } ?>
        <?php
    }

    function formulario_comision_() {

//        FUNCIONES::print_pre($_POST);
        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$_GET[id]'");
        $this->formulario->dibujar_titulo("PAGO A AFILIADO " . '"' . FUNCIONES::interno_nombre($vendedor->vdo_int_id) . '"');
        echo "<br>";
        ?>
                                                    <script type="text/javascript" src="js/util.js"></script>
                                                    <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                                                    <style>
                                                        .fleft{
                                                            float: left;margin-left: 3px;
                                                        }
                                                        .cazul{
                                                            color:#0000ff;
                                                        }
                                                        .cnegro{
                                                            color:#000;
                                                        }
                                                    </style>
        <?php $listado = $this->listado_comisiones(); ?>
        <?php if (count($listado) > 0) { ?>
                                                        <form id="frm_eliminar" name="frm_eliminar" action="" method="POST" enctype="multipart/form-data">  
                                                            <input type="hidden" name="opcion" value="<?php echo $_POST[opcion]; ?>">
                                                            <input type="hidden" name="anio" value="<?php echo $_POST[anio]; ?>">
                                                            <input type="hidden" name="mes" value="<?php echo $_POST[mes]; ?>">
                                                            <input type="hidden" name="urb_id" value="<?php echo $_POST[urb_id]; ?>">
                                                        </form>
                                                        <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nro venta</th>
                                                                    <th>Titular</th>                            
                                                                    <th>Fecha Asignacion</th>
                                                                    <th>Observacion</th>
                                                                    <th>Comision del Mes</th>                            
                                                                    <th>Pagado</th>                            
                                                                    <th>Saldo</th>                            
                                                                    <th class="tOpciones"><input type="checkbox" class="checkprincipal" name="chk0"></th> 
                                                                    <th class="tOpciones">&nbsp;</th> 
                                                                </tr>	
                                                            </thead>

                                                                        <?php $tmonto_v = 0; ?>
            <?php $tmonto_mes = 0; ?>
            <?php $tpagado = 0; ?>
                                                                        <?php $tsaldo = 0; ?>
                                                            <tbody>
                                                                    <?php foreach ($listado as $fila) { ?>
                                                                    <tr>
                                                                        <td><?php echo $fila->nro; ?>&nbsp;</td>
                                                                        <td><?php echo $fila->titular; ?>&nbsp;</td>
                                                                        <td><?php echo $fila->fecha_asignacion; ?>&nbsp;</td>
                                                                        <td><?php echo $fila->concepto; ?>&nbsp;</td>                                
                                                                        <td><?php echo number_format($fila->comision_asignada, 2); ?></td>
                                                                        <td><?php echo number_format($fila->comision_pagada, 2); ?></td>
                                                                        <td><b><?php echo number_format($fila->comision_mes, 2); ?></b>&nbsp;</td>
                                                                        <td>
                                                                            <input type="checkbox" class="checkdetalle fleft" data-monto="<?php echo $fila->comision_mes; ?>" data-id="<?php echo $fila->com_id; ?>">&nbsp;
                                                                            <input type="text" class="comision_mes fleft" value="" size="5" data-id="<?php echo $fila->com_id; ?>">&nbsp;
                                                                        </td>
                                                                        <td>
                <?php if ($fila->comision_pagada == 0) { ?>
                                                                                <a class="linkOpciones" title="ELIMINAR" href="javascript:eliminar_comision(<?php echo $fila->com_id ?>);" >
                                                                                    <img width="16" border="0" alt="ELIMINAR" src="images/b_drop.png">
                                                                                </a>
                <?php } ?>
                                                                        </td>
                <?php $tmonto_mes+=$fila->comision_mes; ?>                                
                                                                    </tr>                       
            <?php } ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td colspan="6" style="text-align: right;">Total </td>
                                                                    <td ><?php echo number_format($tmonto_mes, 2); ?></td>
                                                                    <td >&nbsp;</td>
                                                                    <td >&nbsp;</td>

                                                                </tr>
                                                                <tr>
                                                                    <td colspan="6" style="text-align: right;">Total Seleccionado</td>
                                                                    <td ><span id="monto_marcado"></span></td>
                                                                    <td >&nbsp;</td>
                                                                    <td >&nbsp;</td>

                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                        <form id="frm_vendedor" name="frm_vendedor" action="gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&acc=pagar<?php echo '&id=' . $_GET['id']; ?>" method="POST" enctype="multipart/form-data">
                                                            <div id="ContenedorDiv">
                                                                <div id="CajaBotones">
                                                                    <input type="hidden" name="comision_a_pagar" id="comision_a_pagar" value="">
                                                                    <input type="hidden" name="com_ids" id="com_ids" value="">
                                                                    <input type="hidden" name="com_montos" id="com_montos" value="">
                                                                    <input type="hidden" name="com_moneda" id="com_moneda" value="2">
                                                                    <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket(); ?>">
                                                                    <input type="hidden" name="urb_id" id="urb_id" value="<?php echo $_POST[urb_id]; ?>">
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" ><span class="flechas1">*</span>Comision a Pagar</div>
                                                                        <div id="CajaInput">
                                                                            <input  name="txt_comision" type="text" id="txt_comision" value="" size="25" readonly=""/>
                                                                        </div>
                                                                    </div>
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" ><span class="flechas1">*</span>Glosa</div>
                                                                        <div id="CajaInput">
                                                                            <input  name="pag_glosa" type="text" id="pag_glosa" value="<?php echo $_POST['pag_glosa']; ?>" size="70"/>
                                                                        </div>
                                                                    </div>
                                                                    <!--Fin-->
                                                                    <!--Inicio-->
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                                                        <div id="CajaInput">
                                                                            <input class="caja_texto" name="pag_fecha" id="pag_fecha" size="12" value="<?php if (isset($_POST['pag_fecha']))
                                                                           echo $_POST['pag_fecha'];
                                                                       else
                                                                           echo date("d/m/Y");
                                                                       ?>" type="text">
                                                                        </div>
                                                                    </div>
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" ><b>Pagos</b></div>                                
            <?php FORMULARIO::frm_pago(array('cmp_fecha' => 'pag_fecha', 'cmp_monto' => 'comision_a_pagar', 'cmp_moneda' => 'com_moneda')); ?>
                                                                    </div>
                                                                    <div style="text-align: left;">
                                                                        <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario_pago_comision();">                            
                                                                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&id=<?php echo $_GET['id']; ?>';">                                            
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                        <script>
                                                            mask_decimal('.comision_mes', null);
                                                            function eliminar_comision(id) {
                                                                var txt = ' seguro de querer anular el la Comision Asignada?';
                                                                $.prompt(txt, {
                                                                    buttons: {Aceptar: true, Cancelar: false},
                                                                    callback: function(v, m, f) {
                                                                        if (v) {
                                                                            $('#frm_eliminar').attr('action', 'gestor.php?mod=afiliado&tarea=PAGOS COMISIONES&acc=anular_comision&id=<?php echo $_GET[id]; ?>&com_id=' + id);
                                                                            document.frm_eliminar.submit();
                                                                        }
                                                                    }
                                                                });

                                                            }
                                                            $('.checkprincipal').click(function() {
                                                                var check = $(this).prop('checked');
                                                                var detalles = $('.checkdetalle');
                                                                for (var i = 0; i < detalles.size(); i++) {
                                                                    if (check) {
                                                                        $(detalles[i]).prop('checked', 'checked');
                                                                        var monto = $(detalles[i]).attr('data-monto') * 1;
                                                                        $(detalles[i]).next('input').val(monto);
                                                                        $(detalles[i]).next('input').addClass('cazul');
                                                                    } else {
                                                                        $(detalles[i]).prop('checked', '');
                                                                        $(detalles[i]).next('input').val('');
                                                                        $(detalles[i]).next('input').removeClass('cazul');
                                                                    }
                                                                }
                                                                sumar_montos();
                                                            });
                                                            $('.comision_mes').focusout(function() {
                                                                var monto = $(this).prev('input').attr('data-monto') * 1;
                                                                var valor = $(this).val() * 1;
                                                                if (valor > monto) {
                                                                    $(this).prev('input').prop('checked', 'checked');
                                                                    $(this).val(monto);
                                                                    $(this).addClass('cazul');
                                                                } else if (valor === monto) {
                                                                    $(this).prev('input').prop('checked', 'checked');
                                                                    $(this).addClass('cazul');
                                                                } else if (valor < 0) {
                                                                    $(this).prev('input').prop('checked', '');
                                                                    $(this).val(monto);
                                                                    $(this).removeClass('cazul');
                                                                } else {
                                                                    $(this).prev('input').prop('checked', '');
                                                                    $(this).removeClass('cazul');
                                                                }
                                                                sumar_montos();
                                                            });
                                                            $('.checkdetalle').click(function() {
                                                                var detalles = $('.checkdetalle');
                                                                var marcados = $('.checkdetalle[type=checkbox]:checked"');
                                                                if (detalles.size() === marcados.size()) {
                                                                    $('.checkprincipal').prop('checked', 'checked');
                                                                } else {
                                                                    $('.checkprincipal').prop('checked', '');
                                                                }
                                                                var check = $(this).prop('checked');
                                                                if (check) {
                                                                    var monto = $(this).attr('data-monto') * 1;
                                                                    $(this).next('input').val(monto);
                                                                    $(this).next('input').addClass('cazul');
                                                                } else {
                                                                    $(this).next('input').val('');
                                                                    $(this).next('input').removeClass('cazul');
                                                                }


                                                                sumar_montos();
                                                            });
                                                            function sumar_montos() {
                                                                //                        var marcados=$('.checkdetalle[type=checkbox]:checked"');
                                                                var marcados = $('.comision_mes');
                                                                var total = 0;
                                                                var comisiones = '';
                                                                var montos = '';
                                                                var j = 0;
                                                                for (var i = 0; i < marcados.size(); i++) {
                                                                    var monto = $(marcados[i]).val() * 1;
                                                                    if (monto > 0) {
                                                                        if (j > 0) {
                                                                            comisiones += ',';
                                                                        }
                                                                        if (j > 0) {
                                                                            montos += ',';
                                                                        }
                                                                        var com_id = $(marcados[i]).attr('data-id');
                                                                        //                            var com_monto=$(marcados[i]).val();
                                                                        comisiones += com_id;
                                                                        montos += monto + '';
                                                                        total += monto;
                                                                        j++;
                                                                    }
                                                                }

                                                                $('#com_ids').val(comisiones);
                                                                $('#com_montos').val(montos);
                                                                $('#comision_a_pagar').val(total);
                                                                $('#txt_comision').val(total.toFixed(2));
                                                                $('#monto_marcado').text(total);
                                                                $('#comision_a_pagar').trigger('focusout');
                                                            }
                                                            function enviar_formulario_pago_comision() {
                                                                console.info('aaa');
                                                                var comision_a_pagar = document.getElementById('comision_a_pagar').value;
                                                                var glosa = document.getElementById('pag_glosa').value;
                                                                var fecha = document.getElementById('pag_fecha').value;
                                                                if (comision_a_pagar === '' || (comision_a_pagar * 1) === 0) {
                                                                    $.prompt('El monto de la comision a pagar no debe ser vacio o cero.');
                                                                    return false;
                                                                }

                                                                if (glosa === '') {
                                                                    $.prompt('La glosa de la comision a pagar no debe ser vacio.');
                                                                    return false;
                                                                }
                                                                if (fecha !== '') {
                                                                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                                                        var dato = JSON.parse(respuesta);
                                                                        if (dato.response !== "ok") {
                                                                            $.prompt(dato.mensaje);

                                                                        } else {
                                                                            if (!validar_fpag_montos(dato.cambios)) {
                                                                                $.prompt('El monto a Pagar no cocuerda con los pagos realizados');
                                                                                return false;
                                                                            }
                                                                            document.frm_vendedor.submit();
                                                                        }
                                                                    });
                                                                } else {
                                                                    $.prompt('La fecha del pago de la comision no debe ser vacio.');
                                                                }
                                                            }
                                                            jQuery(function($) {
                                                                $("#pag_fecha").mask("99/99/9999");
                                                            });
                                                        </script>
                                                    <?php }else { ?>
                                                        <?php
                                                        $mensaje = 'No existe comisiones pendientes para este periodo';
                                                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=PAGOS COMISIONES&id=$_GET[id]");
                                                        ?>
                                                    <?php } ?>
                                                    <?php
                                                }

    function listado_comisiones($tipo = '') {
        $filtro = "";
        
//        if ($_POST[opcion] == 'gestion') {
//            $filtro = "and left(ven_fecha,7)='$_POST[anio]-$_POST[mes]'";
//        }
        
        if ($_REQUEST[pdo_id] != '') {
            $filtro .= " and com_pdo_id='$_REQUEST[pdo_id]'";
        }
        
        if ($_REQUEST[urb_id] != '') {
            $filtro .= " and ven_urb_id='$_REQUEST[urb_id]'";
        }


        $sql = "select com_id,com_pagado, ven_codigo,ven_id, ven_fecha, int_nombre, int_apellido, 
        ven_codigo, ven_fecha, ven_monto, ven_concepto, com_monto , 
        ven_fecha_firma,com_fecha_cre,com_vdo_id,com_pdo_id,pdo_usu_mod,pdo_fecha_mod,
        pdo_fecha_cre
        from venta,comision, interno,comision_periodo
        where com_vdo_id='$_GET[id]' 
        and com_estado='Pendiente'
        and com_ven_id=ven_id 
        and com_pdo_id=pdo_id 
        and int_id=ven_int_id $filtro
        and com_tipo='$tipo'  order by com_pdo_id desc,com_fecha_cre desc";
//            echo $sql;
        $comisiones = FUNCIONES::objetos_bd_sql($sql);
        $listado = array();
        for ($i = 0; $i < $comisiones->get_num_registros(); $i++) {
            $comision = $comisiones->get_objeto();

//                if ($this->se_podra_pagar($comision->com_fecha_cre, $comision->com_vdo_id)) {
                                    
            $fila = new stdClass();
            
            if ($comision->pdo_usu_mod != '' || $comision->pdo_usu_mod != NULL) {
                $dat_fecha = explode(' ', $comision->pdo_fecha_mod);
            } else {
                $dat_fecha = explode(' ', $comision->pdo_fecha_cre);
            }
            
            $fila->fecha = FUNCIONES::get_fecha_latina($dat_fecha[0]);
            $fila->fecha_asignacion = FUNCIONES::get_fecha_latina($dat_fecha[0]);
            
            $fila->com_id = $comision->com_id;
            $fila->nro = $comision->ven_id;
            $fila->concepto = $comision->ven_concepto;
            $fila->titular = $comision->int_nombre . ' ' . $comision->int_apellido;
            $fila->folio = $comision->ven_codigo;
            $fila->periodo = FUNCIONES::atributo_bd_sql("select pdo_descripcion as campo from con_periodo where pdo_id='$comision->com_pdo_id'");
//            $fila->fecha = FUNCIONES::get_fecha_latina($comision->com_fecha_cre);
            $fila->precio = $comision->ven_monto;
            $fila->tcomision = $comision->com_monto;
//            $fila->fecha_asignacion = FUNCIONES::get_fecha_latina($comision->com_fecha_cre);
            $fila->comision_asignada = $comision->com_monto;
            $fila->comision_pagada = $comision->com_pagado;
            $fila->comision_mes = round($comision->com_monto - $comision->com_pagado, 2);

            $fila->tpagado = 0;
            $fila->tsaldo = 0;
            $listado[] = $fila;
//                }

            $comisiones->siguiente();
        }
        return $listado;
    }

    function listado_comisiones_() {
        $filtro = "";
        if ($_POST[opcion] == 'gestion') {
            $filtro = "and left(ven_fecha,7)='$_POST[anio]-$_POST[mes]'";
        }
        $sql = "select com_id,com_pagado, ven_id, ven_fecha, int_nombre, int_apellido, 
        ven_codigo, ven_fecha, ven_monto, ven_concepto, com_monto , ven_fecha_firma
        from venta,comision, interno
        where                    
        com_vdo_id='$_GET[id]' 
        and com_estado='Pendiente' 
        and com_ven_id=ven_id 
        and int_id=ven_int_id $filtro order by com_pdo_id desc,com_fecha_cre desc";
        
        echo $sql;

        $comisiones = FUNCIONES::objetos_bd_sql($sql);
        $listado = array();
        for ($i = 0; $i < $comisiones->get_num_registros(); $i++) {
            $comision = $comisiones->get_objeto();
            $fila = new stdClass();
            $fila->com_id = $comision->com_id;
            $fila->nro = $comision->ven_id;
            $fila->concepto = $comision->ven_concepto;
            $fila->titular = $comision->int_nombre . ' ' . $comision->int_apellido;
            $fila->folio = $comision->ven_codigo;
            $fila->fecha = FUNCIONES::get_fecha_latina($comision->ven_fecha);
            $fila->precio = $comision->ven_monto;
            $fila->tcomision = $comision->com_monto;
            $fila->fecha_asignacion = FUNCIONES::get_fecha_latina($comision->ven_fecha);
            $fila->comision_asignada = $comision->com_monto;
            $fila->comision_pagada = $comision->com_pagado;
            $fila->comision_mes = round($comision->com_monto - $comision->com_pagado, 2);

            $fila->tpagado = 0;
            $fila->tsaldo = 0;
            $listado[] = $fila;
            $comisiones->siguiente();
        }
        return $listado;
    }

//        function pagos_comisiones(){
//            if($_GET['acc'] <> ""){
//                if($_GET['acc'] == 'pagar'){
//                    $this->pagar_monto_comision($_GET['id']);
//                }
//                if($_GET['acc'] == 'anular'){
//                    $this->anular_monto_comision($_GET['pag_id']);
//                }
//                if($_GET['acc'] == 'anular_comision'){
//                    $this->anular_asignacion_comision($_GET['com_id']);
//                }
//                if($_GET['acc'] == 'ver'){
//                    $this->ver_comprobante_pago_comision($_GET['pag_id']);
//                }
//                if($_GET['acc'] == 'form'){
//                    $this->formulario_comision();
//                }
//            }else{                
//                $this->listar_pagos_comisiones();
//            }
//        }

    function pagos_comisiones() {
        if ($_GET['acc'] <> "") {
            if ($_GET['acc'] == 'pagar') {
                $this->pagar_monto_comision($_GET['id']);
            }
            if ($_GET['acc'] == 'anular') {
                $this->anular_monto_comision($_GET['pag_id']);
            }
            if ($_GET['acc'] == 'anular_comision') {
                $this->anular_asignacion_comision($_GET['com_id']);
            }
            if ($_GET['acc'] == 'ver') {
                $tipo = '';
                if ($_GET[bono] == 'ok') {
                    $tipo = 'bono';
                }
                $this->ver_comprobante_pago_comision($_GET['pag_id'], $tipo);
            }
            if ($_GET['acc'] == 'form') {
                $this->formulario_comision();
            }
        } else {
            if ($_POST) {
                $this->listar_pagos_comisiones();
            } else {
                if ($_GET[form] != '') {
                    $this->listar_pagos_comisiones();
                } else {
                    $this->frm_filtros();                        
                }
            }
        }
    }

    function anular_asignacion_comision($id) {
        include_once 'clases/registrar_comprobantes.class.php';
        $bool = COMPROBANTES::anular_comprobante('comision', $id);
        if (!$bool) {
            $mensaje = "La transaccion no puede ser anulada por que el periodo en el que fue realizado esta cerrado.";
            $tipo = 'Error';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
            return;
        }
        $conec = new ADO();

        $sql2 = "update comision set com_estado='Anulado' where com_id='" . $id . "'";

        $conec->ejecutar($sql2);
        $this->formulario_comision();
    }

    function pagar_monto_comision($vdo_id) {

        $conec = new ADO();

        if (true) {

            $tipo_comision = $_GET[tipo];
            $sql = "select int_nombre,int_apellido,int_id,vdo_can_id from interno inner join vendedor on (vdo_int_id=int_id) where vdo_id='" . $vdo_id . "'";
            $conec->ejecutar($sql);
            $obj = $conec->get_objeto();
            $interesado = $obj->int_nombre . " " . $obj->int_apellido;
            $moneda = '2';
            $monto = $_POST['comision_a_pagar'];
            $glosa = $_POST['pag_glosa']; //'Pago de Comision - Vendedor: ' . $interesado . " - Monto: " . $monto;
            $comisiones = $_POST['com_ids']; //'Pago de Comision - Vendedor: ' . $interesado . " - Monto: " . $monto;
            $montos = $_POST['com_montos']; //'Pago de Comision - Vendedor: ' . $interesado . " - Monto: " . $monto;
            $pag_fecha = FUNCIONES::get_fecha_mysql($_POST['pag_fecha']);

            $sql_insert = "insert into pago_vendedores(pve_fecha,pve_hora,
                pve_usu_id,pve_vdo_id,
                pve_monto,pve_moneda,
                pve_estado,pve_glosa,pve_comisiones,pve_montos,pve_tipo_comision)
        values('" . $pag_fecha . "','" . date('H:i') . "','" .
                    $this->usu->get_id() . "','" . $vdo_id . "','" .
                    $monto . "','" . $moneda . "','Activo','" . $glosa . "','$comisiones','$montos','$tipo_comision')";

            $conec->ejecutar($sql_insert, true, true);
            $llave = ADO::$insert_id;
            $usuario = $this->usu->get_id();

            $_comisiones = array();
            $_ids = explode(',', $comisiones);
            $_montos = explode(',', $montos);
            for ($i = 0; $i < count($_ids); $i++) {
                $_comisiones[$_ids[$i]] = $_montos[$i];
            }

            $arr_tipo_une = array();
            $arr_une_ids = array();
            $arr_tipos = array('BIR', 'BVI', 'BRA', 'FED');
            
            $list_comisiones = FUNCIONES::objetos_bd_sql("select * from comision 
            left join venta on (com_ven_id=ven_id) 
            left join urbanizacion on (ven_urb_id=urb_id)    
            where com_id in ($comisiones)");

            for ($i = 0; $i < $list_comisiones->get_num_registros(); $i++) {
                $comision = $list_comisiones->get_objeto();

                $com_pagado = $comision->com_pagado;
                $com_pagado+=$_comisiones[$comision->com_id];
                $set_estado = " , com_estado='Pendiente'";
                if ($com_pagado == $comision->com_monto) {
                    $set_estado = " , com_estado='Pagado'";
                }
                $sql_update = "update comision set com_fecha_pag='$pag_fecha', com_usu_id_pago='$usuario', com_pagado='$com_pagado' $set_estado where com_id='$comision->com_id'";
                $conec->ejecutar($sql_update);
                
                $arr_tipo_une[$comision->com_tipo][$comision->urb_une_id * 1] += $_comisiones[$comision->com_id];
                $arr_une_ids[] = $comision->urb_une_id * 1;
                
                $list_comisiones->siguiente();
            }
            include_once 'clases/registrar_comprobantes.class.php';
            include_once 'clases/modelo_comprobantes.class.php';
            $glosa = "Pago de Bono $tipo_comision, " . $glosa;
            $params = array(
                'tabla' => 'pago_vendedores',
                'tabla_id' => $llave,
                'fecha' => $pag_fecha,
                'moneda' => $moneda,
                'ingreso' => false,
                'une_id' => 0,
                'glosa' => $glosa, 'ca' => '0', 'cf' => '0', 'cc' => '0',
            );

            $detalles = FORMULARIO::insertar_pagos($params);
            
            $arr_cuentas_pas = array(
                'BIR' => '2.1.2.06.2.12',
                'BVI' => '2.1.2.06.2.13',
                'BRA' => '2.1.2.06.2.14',
                'FED' => '2.1.2.06.2.15'
            );

            $detalles2 = array();

            $arr_une_ids = array_unique($arr_une_ids);
            foreach ($arr_tipos as $tipo) {
                foreach ($arr_une_ids as $une_id) {

                    $monto = $arr_tipo_une[$tipo][$une_id];

                    if ($monto > 0) {
                        $cuenta_pas = $arr_cuentas_pas[$tipo];
                        $detalles2[] = array(
                            "cuen" => FUNCIONES::get_cuenta($_SESSION[ges_id], $cuenta_pas),
                            "debe" => $monto,
                            "haber" => 0,
                            "glosa" => $glosa,
                            "ca" => 0, "cf" => '0', "cc" => 0, 'une_id' => $une_id
                        );
                    }
                }
            }

            $data = array(
                'moneda' => $moneda,
                'ges_id' => $_SESSION[ges_id],
                'fecha' => $pag_fecha,
                'glosa' => $glosa,
                'interno' => $interesado,
                'tabla_id' => $llave,
                'urb' => null,
                'vdo_can_codigo' => 0,
                'monto' => $monto,
                'detalles' => $detalles,
                'detalles2' => $detalles2,
            );
                                    
            $comprobante = MODELO_COMPROBANTE::pago_bonos($data);
            COMPROBANTES::registrar_comprobante($comprobante);
            $this->ver_comprobante_pago_comision($llave);
        } else {
            $mensaje = 'No puedes realizar ningun cobro, por que no estas registrado como cajero.';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=ACCEDER");
        }
    }
    
    function actualizar_saldos_BRA($vdo_id) {
    //    $sql = "select * from comision_cobro";
        $sql = "select comcob_vdo_id as vdo_id,comcob_ven_id as ven_id,sum(com_monto)as pagado from comision 
                inner join comision_cobro on (com_ven_id=comcob_ven_id and com_vdo_id=comcob_vdo_id)
                            where com_estado='Pagado' and com_tipo='BRA' and comcob_vdo_id=$vdo_id
                group by comcob_vdo_id,comcob_ven_id
                ";
        $regs = FUNCIONES::lista_bd_sql($sql);

        foreach ($regs as $reg) {
            $pagado = $reg->pagado;

            $sql_upd = "update comision_cobro set comcob_pagado=$pagado,
                comcob_saldo=(comcob_saldo - $pagado)
                where comcob_ven_id=$reg->ven_id and comcob_vdo_id=$reg->vdo_id";
            FUNCIONES::bd_query($sql_upd);
        }
    }

                                                function obtener_datos_para_pago_comision(&$cco) {
                                                    $sql = "select par_pagocomisiones_cc from ad_parametro";
                                                    $conec = new ADO();
                                                    $conec->ejecutar($sql);
                                                    $cco = $conec->get_objeto()->par_pagocomisiones_cc;
                                                }

                                                function anular_monto_comision($pag_id) {
                                                    include_once 'clases/registrar_comprobantes.class.php';
                                                    $bool = COMPROBANTES::anular_comprobante('pago_vendedores', $pag_id);
                                                    if (!$bool) {
                                                        $mensaje = "La transaccion no puede ser anulada por que el periodo en el que fue realizado esta cerrado.";
                                                        $tipo = 'Error';
                                                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
                                                        return;
                                                    }
                                                    $conec = new ADO();
                                                    $pago = FUNCIONES::objeto_bd_sql("select * from pago_vendedores where pve_id='$pag_id'");
                                                    $sql2 = "update pago_vendedores set pve_estado='Anulado' where pve_id='" . $pag_id . "'";
                                                    $conec->ejecutar($sql2);

                                                    $_comisiones = array();
                                                    $_ids = explode(',', $pago->pve_comisiones);
                                                    $_montos = explode(',', $pago->pve_montos);
                                                    for ($i = 0; $i < count($_ids); $i++) {
                                                        $_comisiones[$_ids[$i]] = $_montos[$i];
                                                    }

                                                    $list_comisiones = FUNCIONES::objetos_bd_sql("select * from comision where com_id in ($pago->pve_comisiones)");

                                                    for ($i = 0; $i < $list_comisiones->get_num_registros(); $i++) {
                                                        $comision = $list_comisiones->get_objeto();

                                                        $com_pagado = $comision->com_pagado;
                                                        $com_pagado = $com_pagado - $_comisiones[$comision->com_id];
//                echo "$com_pagado==$comision->com_monto <br>";
                                                        $sql_update = "update comision set com_fecha_pag='0000-00-00', com_usu_id_pago='', com_pagado='$com_pagado' ,com_estado='Pendiente' where com_id='$comision->com_id'";
                                                        $conec->ejecutar($sql_update);
                                                        $list_comisiones->siguiente();
                                                    }

//            $sql3="update comision set com_estado='Pendiente', com_fecha_pag='0000-00-00', com_usu_id_pago='' where com_id in ($pago->pve_comisiones)";
//            $conec->ejecutar($sql3);
                                                    $mensaje = 'Pago de comision anulado Correctamente';
                                                    $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . "&tarea=PAGOS COMISIONES&id=$pago->pve_vdo_id");
                                                }

                                                function cuentas() {
                                                    $this->formulario->dibujar_tarea();

                                                    if ($_GET['acc'] == "comision") {
                                                        $this->pagar_comision();
                                                    } else {
                                                        if ($_GET['acc'] == "frm_cambiar") {
                                                            $this->frm_cambiar();
                                                        } else {
                                                            if ($_GET['acc'] == "cambiar") {
                                                                $this->cambiar();
                                                                $this->listado_cuentas();
                                                            } else {
                                                                if ($_GET['acc'] == "imprimir") {
                                                                    $this->imprimir_pago($_GET['id']);
                                                                } else {
                                                                    $this->listado_cuentas();
                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                                function cambiar() {
                                                    $conec = new ADO();

                                                    $sql = "update comision set com_vdo_id='" . $_POST['vendedor'] . "' where com_id='" . $_POST['comision'] . "'";

                                                    $conec->ejecutar($sql);

                                                    $sql = "select com_ven_id from comision where com_id='" . $_POST['comision'] . "'";

                                                    $conec->ejecutar($sql);

                                                    $objeto = $conec->get_objeto();

                                                    $venta = $objeto->com_ven_id;

                                                    $sql = "update venta set ven_vdo_id='" . $_POST['vendedor'] . "' where ven_id='" . $venta . "'";

                                                    $conec->ejecutar($sql);

                                                    $this->formulario->mensaje('Correcto', 'La comisiï¿½n fue asignada a otro vendedor.');
                                                }

                                                function frm_cambiar() {
                                                    ?>
                                                    <div id="Contenedor_NuevaSentencia">
                                                        <form id="frm_vendedor" name="frm_vendedor" action="gestor.php?mod=afiliado&tarea=CUENTAS&id=<?php echo $_GET['id']; ?>&acc=cambiar" method="POST" enctype="multipart/form-data">  
                                                            <div id="FormSent">

                                                                <div class="Subtitulo">Seleccione el vendedor al que asignara la comisiï¿½n</div>
                                                                <div id="ContenedorSeleccion">
                                                                    <!--Inicio-->
                                                                    <div id="ContenedorDiv">
                                                                        <div class="Etiqueta" >Vendedor</div>
                                                                        <div id="CajaInput">
                                                                            <select style="width:200px;" name="vendedor" class="caja_texto">
        <?php
        $fun = NEW FUNCIONES;
        $fun->combo("select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado'", $_POST['vendedor']);
        ?>
                                                                            </select>
                                                                            <input name="comision" type="hidden" value="<?php echo $_GET['com']; ?>">
                                                                        </div>
                                                                    </div>
                                                                    <!--Fin-->
                                                                </div>

                                                                <div id="ContenedorDiv">
                                                                    <div id="CajaBotones">
                                                                        <center>

                                                                            <input type="submit" class="boton" name="" value="Cambiar">
                                                                            <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=afiliado&tarea=CUENTAS&id=<?php echo $_GET['id']; ?>';">

                                                                        </center>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
        <?php
    }

    function listado_cuentas() {
        ?>
                                                    <script>
                                                        function pagar_comision(id) {
                                                            var txt = 'Estas seguro que realizaras el pago de la comisiï¿½n?';

                                                            $.prompt(txt, {
                                                                buttons: {Pagar: true, Cancelar: false},
                                                                callback: function(v, m, f) {

                                                                    if (v) {
                                                                        location.href = 'gestor.php?mod=afiliado&tarea=CUENTAS&acc=comision&id=' + id;
                                                                    }

                                                                }
                                                            });
                                                        }

                                                        function cambiar_comision(id) {
                                                            var txt = 'Estas seguro que cambiara la comisiï¿½n a otro vendedor?';

                                                            $.prompt(txt, {
                                                                buttons: {Cambiar: true, Cancelar: false},
                                                                callback: function(v, m, f) {

                                                                    if (v) {
                                                                        location.href = 'gestor.php?mod=afiliado&tarea=CUENTAS&acc=frm_cambiar&id=<?php echo $_GET['id']; ?>&com=' + id;
                                                                    }

                                                                }
                                                            });
                                                        }

                                                    </script>
        <?php
        $conec = new ADO();

        $sql = "select int_nombre,int_apellido from vendedor
		inner join interno on (vdo_int_id=int_id) where vdo_id=" . $_GET['id'];

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $objeto = $conec->get_objeto();
        ?>
                                                    <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                                                        <tr>
                                                            <td width="40%" ></td>
                                                            <td  width="20%" ><p align="center" ><strong><h3><center></center></h3></strong></p></td>
                                                            <td  width="40%" ><div align="right"></div></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                <br />
                                                                <strong>Vendedor: </strong> <?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?>
                                                            </td>
                                                            <td align="right">
                                                            </td>
                                                        </tr> 
                                                    </table>
                                                    <br><br><center><h2>HISTORIAL DE COMISIONES ASIGNADAS</h2><table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Venta Nro</th>
                                                                    <th>Nombre</th>
                                                                    <th>Tipo Venta</th>
                                                                    <th>Monto Venta</th>
                                                                    <th>Fecha</th>
                                                                    <th>Urbanizaciï¿½n</th>
                                                                    <th>Manzano</th>					
                                                                    <th>Lote</th>
                                                                    <th>Monto Bs</th>
                                                                    <th>Monto $us</th>
                                                                    <!--<th class="tOpciones" width="70px">Opciones</th>-->
                                                                </tr>	
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $conec = new ADO();

                                                                $sql = "SELECT distinct com_id,com_monto,com_moneda,com_estado,com_ven_id,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,ven_fecha,ven_tipo,ven_monto 
		FROM 
		comision inner join venta on (com_ven_id=ven_id)
		inner join lote on (ven_lot_id=lot_id) 
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on (man_urb_id=urb_id)
		inner join vendedor on (com_vdo_id=vdo_id)
		inner join interno on (ven_int_id=int_id)
		where
		com_estado='Pendiente' and com_vdo_id='" . $_GET['id'] . "' and ven_estado!='Anulado'
		order by ven_id asc";

                                                                $conec->ejecutar($sql);

                                                                $num = $conec->get_num_registros();

                                                                $conversor = new convertir();
                                                                $totalbs = 0;
                                                                $totalsus = 0;
                                                                for ($i = 0; $i < $num; $i++) {
                                                                    $objeto = $conec->get_objeto();

                                                                    echo '<tr>';

                                                                    echo "<td>";
                                                                    echo $objeto->com_ven_id;
                                                                    echo "&nbsp;</td>";
                                                                    echo "<td>";
                                                                    echo $objeto->int_nombre . ' ' . $objeto->int_apellido;
                                                                    echo "&nbsp;</td>";
                                                                    echo "<td>";
                                                                    echo $objeto->ven_tipo;
                                                                    echo "&nbsp;</td>";
                                                                    echo "<td>";
                                                                    echo $objeto->ven_monto;
                                                                    echo "&nbsp;</td>";
                                                                    echo "<td>";
                                                                    echo $conversor->get_fecha_latina($objeto->ven_fecha);
                                                                    echo "&nbsp;</td>";
                                                                    echo "<td>";
                                                                    echo $objeto->urb_nombre;
                                                                    echo "&nbsp;</td>";

                                                                    echo "<td>";
                                                                    echo $objeto->man_nro;
                                                                    echo "&nbsp;</td>";
                                                                    echo "<td>";
                                                                    echo $objeto->lot_nro;
                                                                    echo "&nbsp;</td>";
                                                                    if ($objeto->com_moneda == '1') {
                                                                        $bs = $objeto->com_monto;
                                                                        $sus = round($objeto->com_monto / $this->tc, 2);

                                                                        $totalbs = $totalbs + $bs;
                                                                        $totalsus = $totalsus + $sus;
                                                                    } else {
                                                                        $sus = $objeto->com_monto;
                                                                        $bs = round($objeto->com_monto * $this->tc, 2);
                                                                        $totalbs = $totalbs + $bs;
                                                                        $totalsus = $totalsus + $sus;
                                                                    }

                                                                    echo "<td>";
                                                                    //echo $objeto->com_monto;
                                                                    echo $bs;
                                                                    echo "&nbsp;</td>";
                                                                    //$tot+=$objeto->com_monto;

                                                                    echo "<td>";
                                                                    //if($objeto->com_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';
                                                                    echo $sus;
                                                                    echo "&nbsp;</td>";

//					echo "<td>&nbsp;";
//						
//						
                                                                    ?>
                                    <!--						<center>
                                                                                    <table>
                                                                                            <tr>
                                                                                                    <td><a class="linkOpciones" href="javascript:pagar_comision('<?php // echo $objeto->com_id; ?>');">
                                                                                                                    <img src="images/pagar.png" border="0" title="PAGAR COMISION" alt="pagar">
                                                                                                            </a>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                    <a class="linkOpciones" href="javascript:cambiar_comision('<?php // echo $objeto->com_id; ?>');">
                                                                                                            <img src="images/cambiar.png" border="0" title="CAMBIAR COMISION" alt="Cambiar">
                                                                                                    </a>
                                                                                                    </td>
                                                                                            </tr>
                                                                                    </table>
                                                                                    
                                                                                    </center>-->
            <?php
//						
//					echo "</td>";
            echo "</tr>";

            $conec->siguiente();
        }
        ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td>&nbsp;</td>
                                                                    <td>&nbsp;</td>
                                                                    <td>&nbsp;</td>
                                                                    <td>&nbsp;</td>
                                                                    <td>&nbsp;</td>
                                                                    <td>&nbsp;</td>
                                                                    <td>&nbsp;</td>					
                                                                    <td>&nbsp;</td>
                                                                    <td><?php echo $totalbs . ' Bs'; ?></td>
                                                                    <td><?php echo $totalsus . ' $us'; ?></td>
                                                                    <!--<td>&nbsp;</td>-->
                                                                </tr>	
                                                            </tfoot>
                                                        </table></center>
                                                    <?php
                                                }

                                                function pagar_comision() {

                                                    $conec = new ADO();

                                                    $sql = "select cja_cue_id from cajero where cja_usu_id = '" . $this->usu->get_id() . "'";

                                                    $conec->ejecutar($sql);

                                                    $nume = $conec->get_num_registros();

                                                    if ($nume > 0) {
                                                        $obj = $conec->get_objeto();

                                                        $caja = $obj->cja_cue_id;

                                                        $sql = "update comision set 
							com_estado='Pagado',
							com_fecha_pag='" . date('Y-m-d') . "'
							where com_id = '" . $_GET['id'] . "'";


                                                        $conec->ejecutar($sql);

                                                        /*                                                         * REFLEJO EN LAS CUENTAS* *///
                                                        /*
                                                          $sql="SELECT com_ven_id,com_monto,com_moneda,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,vdo_cco_id,vdo_cue_id,vdo_int_id
                                                          FROM
                                                          comision inner join venta on (com_id='".$_GET['id']."' and com_ven_id=ven_id)
                                                          inner join lote on (ven_lot_id=lot_id)
                                                          inner join manzano on (lot_man_id=man_id)
                                                          inner join urbanizacion on (man_urb_id=urb_id)
                                                          inner join vendedor on (com_vdo_id=vdo_id)
                                                          inner join interno on (vdo_int_id=int_id)
                                                          ";

                                                          $conec->ejecutar($sql);

                                                          $objeto=$conec->get_objeto();

                                                          include_once("clases/registrar_comprobantes.class.php");

                                                          $comp = new COMPROBANTES();

                                                          $cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$this->tc,$objeto->com_moneda,'',$objeto->vdo_int_id,$this->usu->get_id(),'2','1','comision',$_GET['id']);

                                                          //if($objeto->com_moneda=='1')
                                                          $mde=$objeto->com_monto;
                                                          //else
                                                          //$mde=$objeto->com_monto*$this->tc;

                                                          $cco=$this->obtener_cco_urbanizacion($objeto->com_ven_id);

                                                          if($objeto->com_moneda=='1')
                                                          {

                                                          $comp->ingresar_detalle($cmp_id,$mde*(-1),$caja,0);

                                                          $comp->ingresar_detalle($cmp_id,$mde,$objeto->vdo_cue_id,$cco,"Comisiï¿½n por la venta del terreno: Urb:$objeto->urb_nombre - Mza:$objeto->man_nro - Lote:$objeto->lot_nro");

                                                          }
                                                          else
                                                          {

                                                          $comp->ingresar_detalle($cmp_id,0,$caja,0,'',$mde*(-1));

                                                          $comp->ingresar_detalle($cmp_id,0,$objeto->vdo_cue_id,$cco,"Comisiï¿½n por la venta del terreno: Urb:$objeto->urb_nombre - Mza:$objeto->man_nro - Lote:$objeto->lot_nro",$mde);

                                                          }
                                                         */
                                                        ///**REFLEJO EN LAS CUENTAS**///

                                                        $this->imprimir_pago($_GET['id']);
                                                    } else {
                                                        $mensaje = 'No puedes realizar ninguna cobro, por que no estas registrado como cajero.';

                                                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
                                                    }
                                                }

                                                function imprimir_pago($id) {
                                                    $conec = new ADO();

                                                    $sql = "SELECT com_vdo_id,com_monto,com_moneda,com_fecha_pag,int_nombre,int_apellido,urb_nombre,man_nro,lot_nro,vdo_cco_id,vdo_cue_id,vdo_int_id 
		FROM 
		comision inner join venta on (com_id='" . $id . "' and com_ven_id=ven_id)
		inner join lote on (ven_lot_id=lot_id) 
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on (man_urb_id=urb_id)
		inner join vendedor on (com_vdo_id=vdo_id)
		inner join interno on (vdo_int_id=int_id)		
		";

                                                    $conec->ejecutar($sql);

                                                    $objeto = $conec->get_objeto();

                                                    ////
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
                                                    $extra1.=" <a href=javascript:window.print();>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
                                                    $extra2 = "'</center></body></html>'";


                                                    echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=afiliado&tarea=CUENTAS&id=' . $objeto->com_vdo_id . '\';"></td></tr></table>
				';
                                                    $conversor = new convertir();
                                                    ?>
                                                    <br><br><div id="contenido_reporte" style="clear:both;";>
                                                        <center>
                                                            <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                                                                <tr>
                                                                    <td width="30%" >
                                                                        <strong><?php echo _nombre_empresa; ?></strong><BR>
                                                                        <strong>Santa Cruz - Bolivia</strong>
                                                                    </td>
                                                                    <td  width="40%" ><p align="center" ><strong><h3><center>COMPROBANTE DE PAGO DE COMISIï¿½N<center></h3></strong></p></td>
                                                                                            <td  width="30%" ><div align="right"><img src="imagenes/micro.png" /></div></td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td colspan="2">
                                                                                                    <strong>Vendedor: </strong> <?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?> <br/><br/>


                                                                                                </td>
                                                                                                <td align="right">

                                                                                                </td>
                                                                                            </tr>

                                                                                            </table>
                                                                                            <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                                                                                                <thead>
                                                                                                    <tr>
                                                                                                        <th>Fecha</th>
                                                                                                        <th>Concepto</th>
                                                                                                        <th>Monto</th>
                                                                                                        <th>Moneda</th>
                                                                                                    </tr>			
                                                                                                </thead>
                                                                                                <tbody>
                                                                                                    <tr>
                                                                                                        <td><?php echo $conversor->get_fecha_latina($objeto->com_fecha_pag); ?></td>
                                                                                                        <td><?php echo "Comisiï¿½n por la venta del terreno: Urb:$objeto->urb_nombre - Mza:$objeto->man_nro - Lote:$objeto->lot_nro"; ?></td>
                                                                                                        <td><?php echo $objeto->com_monto; ?></td>
                                                                                                        <td><?php if ($objeto->com_moneda == '1')
                                                        echo 'Bolivianos';
                                                    else
                                                        echo 'Dolares';
                                                    ?></td>
                                                                                                    </tr>	
                                                                                                </tbody>
                                                                                            </table>

                                                                                            <br><br><br><br>
                                                                                            <table border="0"  width="90%" style="font-size:12px;">
                                                                                                <tr>
                                                                                                    <td width="50%" align center">-------------------------------------</td>
                                                                                                    <td width="50%" align ="center">-------------------------------------</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td align ="center"><strong>Recibi Conforme</strong></td>
                                                                                                    <td align ="center"><strong>Entregue Conforme</strong></td>
                                                                                                </tr>
                                                                                            </table>

                                                                                        </center>
                                                                                        <br><br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr></table>
                                                                                        </div>

        <?php
    }

    function obtener_cco_urbanizacion($id_venta) {
        $conec = new ADO();

        $sql = "SELECT urb_id,urb_cco_id from urbanizacion
			inner join uv on (uv_urb_id=urb_id)
			inner r join lote on (lot_uv_id=uv_id)
			inner join venta on (ven_lot_id=lot_id)
			inner join centrocosto on (cco_id=urb_cco_id)
			where ven_id=$id_venta";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->urb_cco_id;
    }
    
    function frm_filtros(){
        $this->formulario->dibujar_tarea();
        $url = $this->link . "?mod=" . $this->modulo . "&tarea=" . $_GET[tarea] . "&id=" . $_GET[id];
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
//                                        if ($tipo == 'cargar') {
//                                            $fun = new FUNCIONES();
//                                            $fun->combo("select pdo_id as id,pdo_descripcion as nombre from con_periodo
//                                                    where pdo_eliminado='No'
//                                                    and pdo_id='{$_POST[pdo_id]}'", "");
//                                        } else {
//                                            
//                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->
                        
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta">Proyecto<span class="flechas1">*</span></div>
                            <div id="CajaInput">
                                <div id="periodos">
                                    <select id="urb_id" name="urb_id">
                                        <option value="">Seleccione</option>
                                        <?php                                        
                                            $fun = new FUNCIONES();
                                            $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion
                                                order by nombre asc", "");
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->

                      
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">                                
                                <input type="button" class="boton" name="vista" value="Generar" onclick="javascript:enviar(this.form);">
                            </div>
                        </div>


                    </div>
                    <div id="ContenedorDiv" style="display: none">
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
            
        </script>
        <?php
    }

}
?>