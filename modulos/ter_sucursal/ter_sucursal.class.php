<?php

class ter_sucursal extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function ter_sucursal() {  //permisos
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

        $this->modulo = 'ter_sucursal';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('SUCURSAL');

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
        $sql = "select  
				*
			  from 
				ter_sucursal 
                            where suc_eliminado='No' ";
        $this->set_sql($sql, '');

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Nombre</th>		
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

        $sql = "select * from ter_sucursal 
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

    function formulario_tcp($tipo) {
//		$conec= new ADO();
//		
//		$sql="select * from interno";
//		$conec->ejecutar($sql);		
//		$nume=$conec->get_num_registros();
//		$personas=0;
//		if($nume > 0)
//		{
//			$personas=1;
//		}

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
        $page = "'gestor.php?mod=ter_sucursal&tarea=AGREGAR&acc=Emergente'";
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
                document.frm_ter_sucursal.suc_int_id.value = "";
                document.frm_ter_sucursal.suc_nombre_persona.value = "";
            }
        </script>

        <div id="Contenedor_NuevaSentencia">
            <form id="frm_ter_sucursal" name="frm_ter_sucursal" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">

                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Nombre</div>
                            <div id="CajaInput">
                                <input name="suc_nombre" id="suc_nombre"  type="text" class="caja_texto" value="<?php echo $_POST['suc_nombre']; ?>" size="50">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Descripcion</div>
                            <div id="CajaInput">
                                <textarea id="suc_descripcion" cols="33" rows="2" name="suc_descripcion"><?php echo $_POST['suc_descripcion']; ?></textarea>                                                            
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

                $conec = new ADO();
                $sql = "insert into ter_sucursal (suc_nombre,suc_descripcion,suc_eliminado)
                    values ('" . $_POST['suc_nombre'] . "','" . $_POST['suc_descripcion'] . "','No')";
                //echo $sql.'<br>';
                $conec->ejecutar($sql);
                $mensaje = 'Sucursal Agregado Correctamente';

                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

            function modificar_tcp() {
                $conec = new ADO();
                $sql = "update ter_sucursal set 
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
//                    $sql = "update ter_sucursal set suc_eliminado='si' where suc_id='" . $_POST['suc_id'] . "'";
//                    $conec->ejecutar($sql);
//                    $mensaje = 'Sucursal Eliminado Correctamente.';
//                } else {
//                    $mensaje = 'El Sucursal no puede ser eliminado, por que ya fue referenciado en algunos comprobantes.';
//                }
//                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            }

        }
        ?>