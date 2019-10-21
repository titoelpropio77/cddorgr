<?php

class USU extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function USU() {
        //permisos
        $this->ele_id = 22;

        $this->busqueda();

        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();
        $num=0;
//        $this->arreglo_campos[$num]["nombre"] = "gru_descripcion";
//        $this->arreglo_campos[$num]["texto"] = "Grupo";
//        $this->arreglo_campos[$num]["tipo"] = "cadena";
//        $this->arreglo_campos[$num]["tamanio"] = 25;
        
        $this->arreglo_campos[$num]["nombre"] = "gru_id";
        $this->arreglo_campos[$num]["texto"] = "Grupo";
        $this->arreglo_campos[$num]["tipo"] = "combosql";
        $this->arreglo_campos[$num]["sql"] = "select gru_id as codigo,gru_descripcion as descripcion from ad_grupo";
        
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "int_nombre_apellido";
        $this->arreglo_campos[$num]["campo_compuesto"] = "concat(int_nombre,' ',int_apellido)";
        $this->arreglo_campos[$num]["texto"] = "Nombre completo";
        $this->arreglo_campos[$num]["tipo"] = "compuesto";
        $this->arreglo_campos[$num]["tamanio"] = 40;
        $num++;
        $this->arreglo_campos[$num]["nombre"] = "usu_id";
        $this->arreglo_campos[$num]["texto"] = "Usuario";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 25;

        $this->link = 'gestor.php';

        $this->modulo = 'usuario';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('USUARIO');
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
        if ($this->verificar_permisos('PARAMETROS')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'PARAMETROS';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/parametros.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'PARAMETROS';
            $nun++;
        }
        if ($this->verificar_permisos('CONFIGURAR RECIBO')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'CONFIGURAR RECIBO';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/config_file.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'CONFIGURAR RECIBO';
            $nun++;
        }
    }

    function dibujar_listado() {
        $sql = "SELECT 
				usu_id,int_id,int_nombre,int_apellido,gru_descripcion,usu_estado ,usu_suc_id,usu_gru_id
			  FROM 
				ad_usuario inner join interno on(usu_per_id=int_id)
				inner join ad_grupo on (usu_gru_id=gru_id)
				";

        $this->set_sql($sql, ' order by gru_descripcion asc ');

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Grupo</th>
            <th>Persona</th>
            <th>Usuario</th>
            <th>Estado</th>
            <th>Sucursal</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        $conversor = new convertir();

        for ($i = 0; $i < $this->numero; $i++) {

            $objeto = $this->coneccion->get_objeto();
            
            if ($objeto->usu_gru_id == 'AFILIADOS') {
                
                $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor 
                    where vdo_vgru_id=14 and vdo_int_id=$objeto->int_id");
                
                if ($vendedor->vdo_venta_inicial == 0) {
                    $this->coneccion->siguiente();
                    continue;
                }                                
            }
            
            echo '<tr>';

            echo "<td>";
            echo $objeto->gru_descripcion;
            echo "</td>";
            echo "<td>";
            echo $objeto->int_nombre . " " . $objeto->int_apellido;
            echo "</td>";
            echo "<td>";
            echo $objeto->usu_id;
            echo "</td>";
            echo "<td>";
            if ($objeto->usu_estado == '1')
                echo 'Habilitado';
            else
                echo 'Deshabilitado';
            echo "</td>";
            echo "<td>";
            echo $objeto->usu_suc_id>0?FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal where suc_id='$objeto->usu_suc_id'"):'';
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->usu_id);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from ad_usuario
				where usu_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['usu_id'] = $objeto->usu_id;

        $_POST['usu_password'] = '123456789';

        $_POST['usu_per_id'] = $objeto->usu_per_id;

        $_POST['usu_estado'] = $objeto->usu_estado;

        $_POST['usu_gru_id'] = $objeto->usu_gru_id;

        $fun = NEW FUNCIONES;

        $_POST['usu_nombre_persona'] = $fun->nombre($objeto->usu_per_id);
    }

    function datos() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Persona";
            $valores[$num]["valor"] = $_POST['usu_per_id'];
            $valores[$num]["tipo"] = "numero";
            $valores[$num]["requerido"] = true;
            $num++;
            $valores[$num]["etiqueta"] = "Grupo";
            $valores[$num]["valor"] = $_POST['usu_gru_id'];
            $valores[$num]["tipo"] = "texto";
            $valores[$num]["requerido"] = true;
            $num++;
            $valores[$num]["etiqueta"] = "Usuario";
            $valores[$num]["valor"] = $_POST['usu_id'];
            $valores[$num]["tipo"] = "texto";
            $valores[$num]["requerido"] = true;
            $num++;
            $valores[$num]["etiqueta"] = "Contraseña";
            $valores[$num]["valor"] = $_POST['usu_password'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;
            $valores[$num]["etiqueta"] = "Estado";
            $valores[$num]["valor"] = $_POST['usu_estado'];
            $valores[$num]["tipo"] = "numero";
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
        $page = "'gestor.php?mod=usuario&tarea=AGREGAR&acc=Emergente'";
        $extpage = "'persona'";
        $features = "'left=325,width=600,top=200,height=420,scrollbars=yes'";

        $this->formulario->dibujar_tarea('USUARIO');

        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <script>
            function set_valor_interno(data){
                document.frm_usuario.usu_per_id.value = data.id;
                document.frm_usuario.usu_nombre_persona.value = data.nombre;
            }
            function reset_interno()
            {
                document.frm_usuario.usu_per_id.value = "";
                document.frm_usuario.usu_nombre_persona.value = "";
            }
            function enviar_formulario() {

                var persona = document.frm_usuario.usu_per_id.value;
                var grupo = document.frm_usuario.usu_gru_id.options[document.frm_usuario.usu_gru_id.selectedIndex].value;
                var usuario = document.frm_usuario.usu_id.value;
                var password = document.frm_usuario.usu_password.value;
                var estado = document.frm_usuario.usu_estado.options[document.frm_usuario.usu_estado.selectedIndex].value;




                if (persona != '' && grupo != '' && usuario != '' && password != '' && estado != '')
                {

                    document.frm_usuario.submit();
                }
                else
                {
                    $.prompt('Para registrar el usuario debe ingresar Persona , Grupo , Usuario , Contrasena y Estado', {opacity: 0.8});
                }

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
        <!--FancyBox-->
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_usuario" name="frm_usuario" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <!--Inicio
                        <div id="ContenedorDiv">
                           <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
                           <div id="CajaInput">
                                    <input name="usu_per_id" id="usu_per_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['usu_per_id'] ?>" size="2">
                                        <input name="usu_nombre_persona" id="usu_nombre_persona" readonly="readonly"  type="text" class="caja_texto" value="<?php echo $_POST['usu_nombre_persona'] ?>" size="40">
        <?php
        if ($_GET['tarea'] == 'AGREGAR') {
            ?>
                                                    <img src="images/ir.png"  onclick="javascript:window.open(<?php echo $page; ?>,<?php echo $extpage; ?>,<?php echo $features; ?>);">
            <?php
        }
        ?>	
                           </div>

                        </div>
                        Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
                            <div id="CajaInput">
        <?php
        if ($personas <> 0) {
            ?>
                                    <input name="usu_per_id" id="usu_per_id" type="hidden" readonly="readonly" class="caja_texto" value="<?php echo $_POST['usu_per_id'] ?>" size="2">
                                    <input name="usu_nombre_persona" readonly="readonly" id="usu_nombre_persona"  type="text" class="caja_texto" value="<?php echo $_POST['usu_nombre_persona'] ?>" size="25">
                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                        <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                    </a>
                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;" data-url="gestor.php?mod=interno&tarea=ACCEDER&acc=buscar" href="javascript:void(0)">
                                        <img src="images/b_search.png" border="0" title="BUSCAR" alt="BUSCAR">
                                    </a>
                        <?php if (!($ver || $cargar)) { ?>
                                        <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_interno();">
                                            <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                        </a>
                                <?php
                            }
                            ?>
                            <?php
                        } else {
                            echo 'No se le asigno ningúna personas, para poder cargar las personas.';
                        }
                        ?>
                            </div>							   							   								
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Grupo</div>
                            <div id="CajaInput">
                                <select name="usu_gru_id" class="caja_texto">
                                    <option value="">Seleccione</option>
                                <?php
                                $fun = NEW FUNCIONES;
                                $fun->combo("select gru_id as id,gru_descripcion as nombre from ad_grupo", $_POST['usu_gru_id']);
                                ?>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Usuario</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" <?php if ($_GET['tarea'] == "MODIFICAR") echo 'readonly="readonly"'; ?>  name="usu_id" id="usu_id" size="25" value="<?php echo $_POST['usu_id']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Contraseña</div>
                            <div id="CajaInput">
                                <input type="password" class="caja_texto" name="usu_password" id="usu_password" size="25" value="<?php echo $_POST['usu_password']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Estado</div>
                            <div id="CajaInput">
                                <select name="usu_estado" class="caja_texto">
                                    <option value="" >Seleccione</option>
                                    <option value="1" <?php if ($_POST['usu_estado'] == '1') echo 'selected="selected"'; ?>>Habilitado</option>
                                    <option value="0" <?php if ($_POST['usu_estado'] == '0') echo 'selected="selected"'; ?>>Deshabilitado</option>
                                </select>
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
                                        <!--<input type="submit" class="boton" name="" value="Guardar">-->
                                    <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario()">
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
        <script type="text/javascript">
            $(document).ready(function() {
                $("a.group").fancybox({
                    'hideOnContentClick': false,
                    'overlayShow': true,
                    'zoomOpacity': true,
                    'zoomSpeedIn': 300,
                    'zoomSpeedOut': 200,
                    'overlayOpacity': 0.5,
                    'frameWidth': 700,
                    'frameHeight': 350,
                    'type': 'iframe'
                });

                $('a.close').click(function() {
                    $(this).fancybox.close();
                });

            });
        </script>
        <?php
    }

    function emergente() {
        $this->formulario->dibujar_cabecera();

        $valor = trim($_POST['valor']);
        ?>

        <script>
            function poner(id, valor)
            {
                opener.document.frm_usuario.usu_per_id.value = id;
                opener.document.frm_usuario.usu_nombre_persona.value = valor;
                window.close();
            }
        </script>
        <br><center><form name="form" id="form" method="POST" action="gestor.php?mod=usuario&tarea=AGREGAR&acc=Emergente">
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

                $parametros[0] = array('usu_id');
                $parametros[1] = array($_POST['usu_id']);
                $parametros[2] = array('ad_usuario');

                if ($verificar->validar($parametros)) {
                    $conec = new ADO();

                    $sql = "insert into ad_usuario (usu_id,usu_password,usu_per_id,usu_estado,usu_gru_id) values ('" . $_POST['usu_id'] . "','" . md5($_POST['usu_password']) . "','" . $_POST['usu_per_id'] . "','" . $_POST['usu_estado'] . "','" . $_POST['usu_gru_id'] . "')";

                    $conec->ejecutar($sql);

                    $mensaje = 'Usuario Agregado Correctamente';
                } else {
                    $mensaje = 'El usuario no puede ser agregado, por que ya existe una persona con ese nombre de usuario.';
                }

                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function modificar_tcp() {
                $conec = new ADO();

                $codigo = "";

                if ($_POST['usu_password'] <> "123456789") {
                    $sql = "update ad_usuario set 
								usu_password='" . md5($_POST['usu_password']) . "',
								usu_per_id='" . $_POST['usu_per_id'] . "',
								usu_estado='" . $_POST['usu_estado'] . "',
								usu_gru_id='" . $_POST['usu_gru_id'] . "'
								where usu_id='" . $_GET['id'] . "'";
                } else {
                    $sql = "update ad_usuario set 
								usu_per_id='" . $_POST['usu_per_id'] . "',
								usu_estado='" . $_POST['usu_estado'] . "',
								usu_gru_id='" . $_POST['usu_gru_id'] . "'
								where usu_id='" . $_GET['id'] . "'";
                }



                $conec->ejecutar($sql);

                $mensaje = 'Usuario Modificado Correctamente';

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
            
            function paramentros() {
                if($_POST){
                    $this->guardar_paramentros_usuario();
                }else{
                    $usuario=  FUNCIONES::objeto_bd_sql("select * from ad_usuario where usu_id='$_GET[id]'");
                    $this->frm_paramentros_usuario($usuario);
                }
            }
            function guardar_paramentros_usuario(){
                
                $conec=new ADO();
                $sql_up="update ad_usuario set usu_suc_id='$_POST[suc_id]' where usu_id='$_GET[id]'";
                $conec->ejecutar($sql_up);
                $this->formulario->ventana_volver("Usuario Modificado Correctamente", $this->link . '?mod=' . $this->modulo);
            }
            
            function frm_paramentros_usuario($usuario){
                $this->formulario->dibujar_tarea('USUARIO');
                ?>
            <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=usuario&tarea=PARAMETROS&id=<?php echo $_GET[id]?>" name="frm_sentencia">
                <div id="FormSent" style="width:90%;">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion" style="position: relative;">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta"><span class="flechas1">*</span>Sucursal</div>
                            <div id="CajaInput">
                                <select id="suc_id" name="suc_id" style="min-width: 200px;">
                                    <option value=""></option>
                                <?php 
                                    $fun=new FUNCIONES();
                                    $fun->combo("select suc_id as id, suc_nombre as nombre from ter_sucursal where suc_eliminado='no'", $usuario->usu_suc_id);
                                ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="FormSent" style="width:90%;">
                    <div id="ContenedorSeleccion" style="position: relative;">
                        <div id="ContenedorDiv">
                            <input id="btn_guardar" class="boton" type="button" onclick="frm_paso(2);" value="Guardar">
                            <input id="btn_volver" class="boton" type="button" onclick="frm_paso(2);" value="Volver">
                        </div>
                    </div>
                </div>
            </form>
            <script>
                $('#btn_guardar').click(function(){
                    var suc_id=$('#suc_id option:selected').val();
                    if(!(suc_id>0)){
                        $.prompt('Seleccione la Sucursal');
                        return false;
                    }
                    document.frm_sentencia.submit();
                });
                $('#btn_volver').click(function(){
                    location.href='gestor.php?mod=usuario&tarea=ACCEDER';
                });
            </script>
            <?php
            }
            function configurar_recibo() {
                if($_POST){
                    $this->guardar_configurar_recibo();
                }else{
                    $usuario=  FUNCIONES::objeto_bd_sql("select * from ad_usuario where usu_id='$_GET[id]'");
                    $this->frm_configurar_recibo($usuario);
                }
            }
            function guardar_configurar_recibo(){
                $conec=new ADO();
                $json_parametros="{";                
                $json_parametros.="\"font_size\":\"$_POST[font_size]\",";
                $json_parametros.="\"line_height\":\"$_POST[line_height]\"";
                $json_parametros.="}";
                
                $usu_id=$_GET[id];
                if(!$usu_id){
                    $usu_id=$_SESSION[id];
                }
                
                $sql_up="update ad_usuario set usu_recibo_parametro='$json_parametros' where usu_id='$usu_id'";
                $conec->ejecutar($sql_up);
                if($_GET[popup]!='1'){
                    $this->formulario->ventana_volver("Parametros de Recibo Modificado Correctamente", $this->link . '?mod=' . $this->modulo);
                }else{
                    $this->formulario->ventana_volver("Parametros de Recibo Modificado Correctamente", $this->link . '?mod=' . $this->modulo."&tarea=CONFIGURAR RECIBO&id=$usu_id&popup=1");
                    ?>
                    <input id="btn_cerrar" class="boton" type="button" onclick="self.close();" value="Cerrar">
                    <?php
                }
            }
            
            function frm_configurar_recibo($usuario){
                $this->formulario->dibujar_tarea('USUARIO');
                $usu_id=$_GET[id];
                if(!$usu_id){
                    $usu_id=$_SESSION[id];
                }
                $json_parametros=  FUNCIONES::atributo_bd_sql("select usu_recibo_parametro as campo from ad_usuario where usu_id='$usu_id'");
                $params=  json_decode($json_parametros);
                $url_popup="";
                if($_GET[popup]=='1'){
                    $url_popup="&popup=1";
                }
//                $font_size=$params->font_size;
                ?>
            <form id="frm_sentencia" enctype="multipart/form-data" method="POST" action="gestor.php?mod=usuario&tarea=CONFIGURAR RECIBO&id=<?php echo $usu_id.$url_popup;?>" name="frm_sentencia">
                <div id="FormSent" style="width:90%;">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion" style="position: relative;">
                        <table class="tablaLista" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>Parametro</th>
                                    <th>valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tamaño de Letra</td>
                                    <td>
                                        <select id="font_size" name="font_size" style="width: 100px;">
                                            <option value="12px" <?php echo $params->font_size=='12px'?'selected=""':''; ?>>12px</option>
                                            <option value="14px" <?php echo $params->font_size=='14px'?'selected=""':''; ?>>14px</option>
                                            <option value="16px" <?php echo $params->font_size=='16px'?'selected=""':''; ?>>16px</option>
                                            <option value="18px" <?php echo $params->font_size=='18px'?'selected=""':''; ?>>18px</option>
                                            <option value="20px" <?php echo $params->font_size=='20px'?'selected=""':''; ?>>20px</option>
                                            <option value="22px" <?php echo $params->font_size=='22px'?'selected=""':''; ?>>22px</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Interlineado</td>
                                    <td>
                                        <select id="line_height" name="line_height" style="width: 100px;">
                                            <option value="12px" <?php echo $params->line_height=='12px'?'selected=""':''; ?>>12px</option>
                                            <option value="14px" <?php echo $params->line_height=='14px'?'selected=""':''; ?>>14px</option>
                                            <option value="16px" <?php echo $params->line_height=='16px'?'selected=""':''; ?>>16px</option>
                                            <option value="18px" <?php echo $params->line_height=='18px'?'selected=""':''; ?>>18px</option>
                                            <option value="20px" <?php echo $params->line_height=='20px'?'selected=""':''; ?>>20px</option>
                                            <option value="22px" <?php echo $params->line_height=='22px'?'selected=""':''; ?>>22px</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="FormSent" style="width:90%;">
                    <div id="ContenedorSeleccion" style="position: relative;">
                        <div id="ContenedorDiv">
                            <input id="btn_guardar" class="boton" type="button" value="Guardar">
                            <?php if($_GET[popup]=='1'){?>
                                <input id="btn_cerrar" class="boton" type="button" onclick="self.close();" value="Cerrar">
                            <?php }else{ ?>
                                <input id="btn_volver" class="boton" type="button" value="Volver">
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </form>
            <script>
                $('#btn_guardar').click(function(){
                    
                    document.frm_sentencia.submit();
                });
                $('#btn_volver').click(function(){
                    location.href='gestor.php?mod=usuario&tarea=ACCEDER';
                });
            </script>
            <?php
            }

        }
        
        
        ?>