<?php
class INTERNO extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function INTERNO() {
        //permisos
        $this->ele_id = 79;

        $this->busqueda();

        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();

        $num=0;
        $this->arreglo_campos[$num]["nombre"] = "int_id";
        $this->arreglo_campos[$num]["texto"] = "ID";
        $this->arreglo_campos[$num]["tipo"] = "cadena";
        $this->arreglo_campos[$num]["tamanio"] = 40;
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
        $this->arreglo_campos[$num]["tamanio"] = 40;


        $this->link = 'gestor.php';

        $this->modulo = 'interno';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('PERSONA');

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
            $this->arreglo_opciones[$nun]["imagen"] = 'images/cuenta.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'CUENTAS';
            $nun++;
        }
    }

    function cuentas() {
        $this->formulario->dibujar_tarea();

        if ($_GET['acc'] == "deuda") {
            $this->pagardeuda();
        } else {
            $this->listado_cuentas();
        }
    }

    function pagardeuda() {

        $conec = new ADO();

        $sql = "select cja_cue_id from cajero where cja_usu_id = '" . $this->usu->get_id() . "'";

        $conec->ejecutar($sql);

        $nume = $conec->get_num_registros();

        if ($nume > 0) {
            $obj = $conec->get_objeto();

            $caja = $obj->cja_cue_id;

            $sql = "update interno_deuda set 
							ind_estado='Pagado',
							ind_fecha_pago='" . date('Y-m-d') . "'
							where ind_id = '" . $_GET['id'] . "'";


            $conec->ejecutar($sql);

            /*             * REFLEJO EN LAS CUENTAS* *///

            $sql = "SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_int_id,ind_cue_id,ind_cco_id 
			FROM interno_deuda
			where ind_id='" . $_GET['id'] . "'
			";

            $conec->ejecutar($sql);

            $objeto = $conec->get_objeto();

            include_once("clases/registrar_comprobantes.class.php");

            $comp = new COMPROBANTES();

            $cmp_id = $comp->ingresar_comprobante(date('Y-m-d'), $this->tc, $objeto->ind_moneda, '', $objeto->ind_int_id, $this->usu->get_id(), '1', '1', 'interno_deuda', $_GET['id']);

            if ($objeto->ind_moneda == '1')
                $mde = $objeto->ind_monto;
            else
                $mde = $objeto->ind_monto * $this->tc;

            $comp->ingresar_detalle($cmp_id, $mde, $caja, 0);

            $comp->ingresar_detalle($cmp_id, ($mde * (-1)), $objeto->ind_cue_id, $objeto->ind_cco_id, $objeto->ind_concepto);

            ///**REFLEJO EN LAS CUENTAS**///

            $this->imprimir_pago($_GET['id']);
        }
        else {
            $mensaje = 'No puede realizar ninguna cobro, por que usted no esta registrado como cajero.';

            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        }
    }

    function imprimir_pago($deuda) {
        $conec = new ADO();

        $sql = "SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,int_id,int_nombre,int_apellido,cue_descripcion,ind_fecha_pago 
		FROM 
		interno_deuda inner join interno on (ind_id='" . $deuda . "' and ind_int_id=int_id)
		
		inner join cuenta on (ind_cue_id=cue_id)
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
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=interno&tarea=CUENTAS&id=' . $objeto->int_id . '\';"></td></tr></table>
				';
        $conversor = new convertir();
        ?>
        <br><br><div id="contenido_reporte" style="clear:both;";>
            <center>
                <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                    <tr>
                        <td width="40%" >
                            <strong><?php echo _nombre_empresa; ?></strong><BR>
                            <strong>Santa Cruz - Bolivia</strong>
                        </td>
                        <td  width="20%" ><p align="center" ><strong><h3>COMPROBANTE DE PAGO DE DEUDA</h3></strong></p></td>
                        <td  width="40%" ><div align="right"><img src="imagenes/micro.png" /></div></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Persona: </strong> <?php echo $objeto->int_nombre . ' ' . $objeto->int_apellido; ?> <br>


                        </td>
                        <td align="right">

                        </td>
                    </tr>

                </table>
                <table   width="50%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cuenta</th>
                            <th>Observación</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                        </tr>			
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_pago); ?></td>
                            <td><?php echo $objeto->cue_descripcion; ?></td>
                            <td><?php echo $objeto->ind_concepto; ?></td>
                            <td><?php echo $objeto->ind_monto; ?></td>
                            <td><?php if ($objeto->ind_moneda == '1') echo 'Bolivianos';
        else echo 'Dolares'; ?></td>
                        </tr>	
                    </tbody>
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

    function cuenta_producto($producto, &$cue, &$cco) {
        $conec = new ADO();

        $sql = "select fam_cue_id,fam_cco_id from producto inner join familia on (pro_id='$producto' and pro_fam_id=fam_id)";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $cue = $objeto->fam_cue_id;

        $cco = $objeto->fam_cco_id;
    }

    function nota_de_pago($pago) {
        $conec = new ADO();

        $sql = "select vpa_ven_id,vpa_monto,vpa_moneda,vpa_tipo_cambio,vpa_fecha,vpa_usu_id from venta_pago where vpa_id='$pago'";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $objeto = $conec->get_objeto();

        $this->datos_venta($objeto->vpa_ven_id, $monto, $pagado, $moneda);

        if ($moneda == '1')
            $moneda = "Bs";
        else
            $moneda = '$us';

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

        $myday = setear_fecha(strtotime($objeto->vpa_fecha));
        ////

        echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
				  c.document.write(' . $extra1 . ');
				  var dato = document.getElementById(' . $pagina . ').innerHTML;
				  c.document.write(dato);
				  c.document.write(' . $extra2 . '); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=interno&tarea=CUENTAS&id=' . $_GET['inte'] . '\';"></td></tr></table>
				';
        ?>
        <br><br><div id="contenido_reporte" style="clear:both;";>
            <center>
                <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                    <tr>
                        <td  width="40%">
                            <strong>COLEGIO ANABAUTISTA</strong><BR>
                            <strong>Santa Cruz - Bolivia</strong>
                        </td>
                        <td><p align="center" ><strong><h3>NOTA DE PAGO</h3></strong></p></td>
                        <td  width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Fecha: </strong> <?php echo $myday; ?> <br>
                            <strong>Tipo de Cambio: </strong> <?php echo $objeto->vpa_tipo_cambio; ?> <br>
                            <strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->vpa_usu_id); ?> <br><br>
                        </td>
                        <td align="right">

                        </td>
                    </tr>

                </table>
                <table   width="50%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nro Venta</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                        </tr>			
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $objeto->vpa_ven_id; ?></td>
                            <td><?php echo $objeto->vpa_monto; ?></td>
                            <td><?php if ($objeto->vpa_moneda == '1') echo 'Bolivianos';
        else echo 'Dolares'; ?></td>
                        </tr>	
                    </tbody></table>
                <br>
                <table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >

                    <tr>
                        <td colspan="2">
                            <strong>Monto Total: </strong> <?php echo $monto . ' ' . $moneda; ?> <br>
                            <strong>Monto Pagado: </strong> <?php echo $pagado . ' ' . $moneda;
        ; ?> <br>
                            <strong>Saldo: </strong> <?php echo ($monto - $pagado) . ' ' . $moneda;
        ; ?> <br><br>
                        </td>
                    </tr>

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

            </center></div><br>

        <?php
    }

    function nombre_persona($usuario) {
        $conec = new ADO();

        $sql = "select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function datos_venta($venta, &$monto, &$pagado, &$moneda) {
        $conec = new ADO();

        $sql = "select ven_monto,ven_moneda,ven_tipo_cambio from venta where ven_id='$venta'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $moneda = $objeto->ven_moneda;

        $tc = $objeto->ven_tipo_cambio;

        if ($moneda == '1') {
            $monto = $objeto->ven_monto;
        } else {
            $monto = round(($objeto->ven_monto / $tc), 2);
        }

        $sql = "select vpa_monto,vpa_moneda,vpa_tipo_cambio from venta_pago where vpa_ven_id='$venta'";


        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $pagado = 0;

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();

            if ($moneda == '1') {
                if ($objeto->vpa_moneda == '1') {
                    $pagado+=$objeto->vpa_monto;
                } else {
                    $pagado+=($objeto->vpa_monto * $objeto->vpa_tipo_cambio);
                }
            } else {
                if ($objeto->vpa_moneda == '1') {
                    $pagado+=($objeto->vpa_monto / $objeto->vpa_tipo_cambio);
                } else {
                    $pagado+=$objeto->vpa_monto;
                }
            }

            $conec->siguiente();
        }

        $pagado = round($pagado, 2);
    }

    function listado_cuentas() {

        $this->otras_deudas();
    }

    function otras_deudas() {
        ?>
        <script>
            function pagar_deuda(id) {
                var txt = 'Esta seguro que realizara el pago de la deuda?';

                $.prompt(txt, {
                    buttons: {Pagar: true, Cancelar: false},
                    callback: function(v, m, f) {

                        if (v) {
                            location.href = 'gestor.php?mod=interno&tarea=CUENTAS&acc=deuda&id=' + id;
                        }

                    }
                });
            }

        </script>
        <br><br><center><h2>DEUDAS</h2><table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>Persona</th>
                        <th>Observación</th>
                        <th>Monto</th>
                        <th>Moneda</th>
                        <th>F Generada</th>
                        <th>F Programada</th>
                        <th>F Pagada</th>
                        <th>Estado</th>
                        <th class="tOpciones" width="70px">Opciones</th>
                    </tr>	
                </thead>
                <tbody>
        <?php
        $conec = new ADO();

        $sql = "SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,int_nombre,int_apellido,cue_descripcion,cco_descripcion,ind_fecha_programada,ind_fecha_pago 
		FROM 
		interno_deuda inner join interno on (ind_int_id='" . $_GET['id'] . "' and ind_int_id=int_id)
		inner join cuenta on (ind_cue_id=cue_id)
		inner join centrocosto on (ind_cco_id=cco_id)
		";

        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        $conversor = new convertir();

        for ($i = 0; $i < $num; $i++) {
            $objeto = $conec->get_objeto();

            echo '<tr>';

            echo "<td>";
            echo $objeto->int_nombre . ' ' . $objeto->int_apellido;
            echo "&nbsp;</td>";


            echo "<td>";
            echo $objeto->ind_concepto;
            echo "&nbsp;</td>";

            echo "<td>";
            echo $objeto->ind_monto;
            echo "&nbsp;</td>";

            echo "<td>";
            if ($objeto->ind_moneda == '1')
                echo 'Bolivianos';
            else
                echo 'Dolares';
            echo "&nbsp;</td>";

            echo "<td>";
            echo $conversor->get_fecha_latina($objeto->ind_fecha);
            echo "&nbsp;</td>";

            echo "<td>";
            if ($objeto->ind_fecha_programada <> '0000-00-00')
                echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);
            echo "&nbsp;</td>";

            echo "<td>";
            if ($objeto->ind_fecha_pago <> '0000-00-00')
                echo $conversor->get_fecha_latina($objeto->ind_fecha_pago);
            echo "&nbsp;</td>";

            $color = "#000000";

            if ($objeto->ind_estado == 'Pendiente')
                $color = "#FB0404;";
            if ($objeto->ind_estado == 'Pagado')
                $color = "#05A720;";

            echo '<td style="color:' . $color . '">';
            echo $objeto->ind_estado;
            echo "&nbsp;</td>";


            echo "<td>&nbsp;";
            if ($objeto->ind_estado == 'Pendiente') {
                ?>
                        <center>
                            <a class="linkOpciones" href="javascript:pagar_deuda('<?php echo $objeto->ind_id; ?>');">
                                <img src="images/pagar.png" border="0" title="PAGAR DEUDA" alt="pagar">
                            </a>
                        </center>
                            <?php
                        }
                        echo "</td>";
                        echo "</tr>";

                        $conec->siguiente();
                    }
                    ?>
                </tbody></table></center>
                    <?php
                }

        function dibujar_listado() {
            if ($this->obtener_grupo_id($this->usu->get_id()) == "Vendedores") {
                $sql = "SELECT *FROM interno where int_usu_id='$_SESSION[id]' and int_eliminado='No'";
            } else {
                $sql = "SELECT *FROM interno where int_eliminado='No'";
            }
            $this->set_sql($sql, " order by int_id desc");

            $this->set_opciones();

            $this->dibujar();
        }

        function dibujar_encabezado() { ?>
            <tr>
                <th>Codigo</th>
                <th>Codigo Ant</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>CI</th>
                <th>Celular</th>
                <th>Telefono</th>
                <th>Usuario Cre.</th>
                <th class="tOpciones" width="100px">Opciones</th>
            </tr>
            <?PHP
        }

            function mostrar_busqueda() {
                
                for ($i = 0; $i < $this->numero; $i++) {
                    $objeto = $this->coneccion->get_objeto();
                    echo '<tr>';

                    echo "<td>";
                    echo $objeto->int_id;
                    echo "&nbsp;</td>";
                    echo "<td>";
                    echo $objeto->int_codigo;
                    echo "&nbsp;</td>";
                    echo "<td>";
                    echo "$objeto->int_nombre";
                    echo "&nbsp;</td>";
                    echo "<td>";
                    echo "$objeto->int_apellido";
                    echo "&nbsp;</td>";
                    echo "<td>";
                    echo "$objeto->int_ci-$objeto->int_ci_exp";
                    echo "&nbsp;</td>";
                    echo "<td>";
                    echo $objeto->int_celular;
                    echo "&nbsp;</td>";
                    echo "<td>";
                    echo $objeto->int_telefono;
                    echo "&nbsp;</td>";
                    echo "<td>";
                    echo $objeto->int_usu_id;
                    echo "</td>";
                    echo "<td>";
                    echo $this->get_opciones($objeto->int_id);
                    echo "</td>";
                    echo "</tr>";

                    $this->coneccion->siguiente();
                }
            }

            function cargar_datos() {
                $conec = new ADO();

                $sql = "SELECT 
				*
				FROM 
				interno 
				where int_id = '" . $_GET['id'] . "'";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                $_POST['int_nombre'] = $objeto->int_nombre;

                $_POST['int_apellido'] = $objeto->int_apellido;

                $_POST['int_email'] = $objeto->int_email;

                $_POST['int_foto'] = $objeto->int_foto;

                $_POST['int_telefono'] = $objeto->int_telefono;

                $_POST['int_celular'] = $objeto->int_celular;

                $_POST['int_direccion'] = $objeto->int_direccion;

                $_POST['int_ci'] = $objeto->int_ci;
                $_POST['int_ci_exp'] = $objeto->int_ci_exp;

                $_POST['int_fecha_nacimiento'] = FUNCIONES::get_fecha_latina($objeto->int_fecha_nacimiento);

                $_POST['int_fecha_ingreso'] = $objeto->int_fecha_ingreso;

                $_POST['int_usu_id'] = $objeto->int_usu_id;
                $_POST['int_lug_nac'] = $objeto->int_usu_id;

                $_POST['lug_nac'] = $objeto->int_lug_nac;
                if ($_POST[lug_nac] > 0) {
                    $ubicacion = FUNCIONES::objeto_bd_sql("select * from ter_lugar, ter_estado, ter_pais where lug_est_id=est_id and est_pais_id=pais_id and lug_id=$_POST[lug_nac]");
                    $_POST['pais_nac'] = $ubicacion->pais_id;
                    $_POST['est_nac'] = $ubicacion->est_id;
                }
                $_POST['lug_res'] = $objeto->int_lug_res;
                if ($_POST[lug_res] > 0) {
                    $ubicacion = FUNCIONES::objeto_bd_sql("select * from ter_lugar, ter_estado, ter_pais where lug_est_id=est_id and est_pais_id=pais_id and lug_id=$_POST[lug_res]");
                    $_POST['pais_res'] = $ubicacion->pais_id;
                    $_POST['est_res'] = $ubicacion->est_id;
                }
                
                if ($objeto->int_referencias != '') {
                    $str_referencias=$objeto->int_referencias;
                    $str_referencias=  FUNCIONES::limpiar_cadena($str_referencias);
                    $lista_json=  json_decode($str_referencias);
                    $ref_nombres=array();
                    $ref_direcciones=array();
                    $ref_telefonos=array();
                    foreach ($lista_json as $obj) {
                        $ref_nombres[]=$obj->nombre;
                        $ref_direcciones[]=$obj->direccion;
                        $ref_telefonos[]=$obj->telefono;
                    }
                    $_POST[ref_nombre]=$ref_nombres;
                    $_POST[ref_direccion]=$ref_direcciones;
                    $_POST[ref_telefono]=$ref_telefonos;
                }
            }

            function datos() {
                if ($_POST) {
                    //texto,  numero,  real,  fecha,  mail.
                    $num = 0;
                    $valores[$num]["etiqueta"] = "Nombre";
                    $valores[$num]["valor"] = $_POST['int_nombre'];
                    $valores[$num]["tipo"] = "todo";
                    $valores[$num]["requerido"] = true;
                    $num++;
                    $valores[$num]["etiqueta"] = "C.I.";
                    $valores[$num]["valor"] = $_POST['int_ci'];
                    $valores[$num]["tipo"] = "todo";
                    $valores[$num]["requerido"] = true;
                    $num++;
                    $valores[$num]["etiqueta"] = "C.I. expedido";
                    $valores[$num]["valor"] = $_POST['int_ci_exp'];
                    $valores[$num]["tipo"] = "todo";
                    $valores[$num]["requerido"] = true;
                    $num++;
                    $valores[$num]["etiqueta"] = "Email";
                    $valores[$num]["valor"] = $_POST['int_email'];
                    $valores[$num]["tipo"] = "mail";
                    $valores[$num]["requerido"] = false;

                    $val = NEW VALIDADOR;

                    $this->mensaje = "";
                    $sw=true;
                    $msj='';
                    $filtro='';
                    if($_GET[id]){
                        $filtro=" and int_id!='$_GET[id]'";
                    }
                    $sql="select *  from interno where int_ci='$_POST[int_ci]' $filtro and int_eliminado='No'";
                    $interno=  FUNCIONES::objeto_bd_sql($sql);
                    if($interno){
                        $msj="<li>Ya existe una <b>Persona</b> con el mismo C.I. <b>$_POST[int_ci]</b> en el Sistema </li>";
                        $sw=false;
                    }

                    if ($val->validar($valores) && $sw) {
                        return true;
                    } else {
                        $this->mensaje = $val->mensaje.$msj;
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
                if($_GET[popup]=='1'){
                    $url.='&popup=1';
                }
                $this->formulario->dibujar_tarea('PERSONA');
                if ($this->mensaje <> "") {
                    $this->formulario->mensaje('Error', $this->mensaje);
                }
                ?>
        <script src="js/jquery.maskedinput-1.3.min.js"></script>
        <script src="js/util.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">    
                <div id="FormSent" style="width: 100%">
                    <div class="Subtitulo">Datos</div>
                    <div style="clear: both;"></div>
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Foto</div>
                            <div id="CajaInput">
        <?php
        if ($_POST['int_foto'] <> "") {
            $foto = $_POST['int_foto'];
            $b = true;
        } else {
            $foto = 'sin_foto.gif';
            $b = false;
        }
        if (($ver) || ($cargar)) {
            ?>
                                    <img src="imagenes/persona/chica/<?php echo $foto; ?>" border="0" ><?php if ($b and $_GET['tarea'] == 'MODIFICAR') echo '<a href="' . $this->link . '?mod=' . $this->modulo . '&tarea=MODIFICAR&id=' . $_GET['id'] . '&img=' . $foto . '&acc=Imagen"><img src="images/b_drop.png" border="0"></a>'; ?><br>
                                    <input   name="int_foto" type="file" id="int_foto" />
        <?php }else { ?>
                                    <input  name="int_foto" type="file" id="int_foto" />
        <?php } ?>
                                <input   name="fotooculta" type="hidden" id="fotooculta" value="<?php echo $_POST['int_foto'] . $_POST['fotooculta']; ?>"/>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Nombre</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_nombre" id="int_nombre" size="40" maxlength="250" value="<?php echo $_POST['int_nombre']; ?>" autocomplete="off">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Apellido</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_apellido" id="int_apellido" size="40" maxlength="250" value="<?php echo $_POST['int_apellido']; ?>">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>CI</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_ci" id="int_ci" size="20" maxlength="250"  value="<?php echo $_POST['int_ci']; ?>" autocomplete="off">
                            </div>

                            <div id="CajaInput">
                                <?php $int_ci_exp=$_POST[int_ci_exp];?>
                                <select id="int_ci_exp" name="int_ci_exp" style=" width: 60px;">
                                    <option value=""></option>
                                    <option value="SC" <?php echo $int_ci_exp=='SC'?'selected="true"':''; ?>>SC</option>
                                    <option value="BN" <?php echo $int_ci_exp=='BN'?'selected="true"':''; ?>>BN</option>
                                    <option value="PA" <?php echo $int_ci_exp=='PA'?'selected="true"':''; ?>>PA</option>
                                    <option value="LP" <?php echo $int_ci_exp=='LP'?'selected="true"':''; ?>>LP</option>
                                    <option value="OR" <?php echo $int_ci_exp=='OR'?'selected="true"':''; ?>>OR</option>
                                    <option value="PT" <?php echo $int_ci_exp=='PT'?'selected="true"':''; ?>>PT</option>
                                    <option value="CB" <?php echo $int_ci_exp=='CB'?'selected="true"':''; ?>>CB</option>
                                    <option value="TJ" <?php echo $int_ci_exp=='TJ'?'selected="true"':''; ?>>TJ</option>
                                    <option value="CH" <?php echo $int_ci_exp=='CH'?'selected="true"':''; ?>>CH</option>
                                    <option value="EX" <?php echo $int_ci_exp=='EX'?'selected="true"':''; ?>>EX</option>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha de Nacimiento</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="int_fecha_nacimiento" id="int_fecha_nacimiento" size="12" value="<?php echo $_POST['int_fecha_nacimiento']; ?>" type="text">
                            </div>		
                        </div>
                        <div id="json_estados" hidden="">
                            <?php $estados = FUNCIONES::lista_bd_sql("select * from ter_estado where est_eliminado='No'") ?>
                            <?php
                            foreach ($estados as $est) {
                                $est->est_nombre = FUNCIONES::limpiar_cadena($est->est_nombre);
                            }
                            echo json_encode($estados);
                            ?>
                        </div>
                        <div id="json_lugares" hidden="">
                            <?php $lugares = FUNCIONES::lista_bd_sql("select * from ter_lugar where lug_eliminado='No'") ?>
                            <?php
                            foreach ($lugares as $lug) {
                                $lug->lug_nombre = FUNCIONES::limpiar_cadena($lug->lug_nombre);
                            }
                            echo json_encode($lugares);
                            ?>
                        </div>
                        
                        <div id="ContenedorDiv" hidden="">
                            <div class="Etiqueta" >Fecha de Ingreso</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="int_fecha_ingreso" id="int_fecha_ingreso" size="12" value="<?php echo $_POST['int_fecha_ingreso']; ?>" type="text">
                            </div>		
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Email</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_email" id="int_email" size="40" value="<?php echo $_POST['int_email']; ?>">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Telefono</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_telefono" id="int_telefono" size="15" value="<?php echo $_POST['int_telefono']; ?>">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Celular</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_celular" id="int_celular" size="15" value="<?php echo $_POST['int_celular']; ?>">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Direccion</div>
                            <div id="CajaInput">
                                <textarea class="area_texto" name="int_direccion" id="int_direccion" cols="31" rows="3"><?php echo $_POST['int_direccion'] ?></textarea>
                            </div>
                        </div>
                        <div id="ContenedorDiv">    
                            <div class="Etiqueta" >Lugar donde  Nacio</div>
                            <div id="CajaInput">
                                <?php $paises = FUNCIONES::lista_bd_sql("select * from ter_pais where pais_eliminado='No'"); ?>
                                <div style="float: left;">
                                    <select name="pais_nac" id="pais_nac" style="width: 150px;">
                                        <?php foreach ($paises as $pais) { ?>
                                            <option value="<?php echo $pais->pais_id; ?>" <?php echo $_POST[pais_nac] == $pais->pais_id ? 'selected="true"' : '' ?>><?php echo $pais->pais_nombre; ?></option>
                                        <?php } ?>
                                    </select>

                                    <select name="est_nac" id="est_nac" style="width: 150px;">
                                        <?php $_estados = $_POST[est_nac] > 0 ? FUNCIONES::lista_bd_sql("select * from ter_estado where est_pais_id='$_POST[pais_nac]'") : null; ?>
                                        <?php if ($_estados) { ?>
                                            <?php foreach ($_estados as $est) { ?>
                                                <option value="<?php echo $est->est_id; ?>" <?php echo $_POST[est_nac] == $est->est_id ? 'selected="true"' : '' ?>><?php echo $est->est_nombre; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                    <select name="lug_nac" id="lug_nac" style="width: 150px;"> 
                                        <?php $_lugares = $_POST[lug_nac] > 0 ? FUNCIONES::lista_bd_sql("select * from ter_lugar where lug_est_id='$_POST[est_nac]'") : null; ?>
                                        <?php if ($_lugares) { ?>
                                            <?php foreach ($_lugares as $lug) { ?>
                                                <option value="<?php echo $lug->lug_id; ?>" <?php echo $_POST[lug_nac] == $lug->lug_id ? 'selected="true"' : '' ?>><?php echo $lug->lug_nombre; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                                <script>
                                    $('#pais_nac').change(function() {// mostrar_estados
                                        var pais_id = $(this).val();
                                        $('#est_nac').children().remove();
                                        $('#lug_nac').children().remove();
                                        var estados = JSON.parse(trim($('#json_estados').text()));
                                        var options = '';
                                        for (var i = 0; i < estados.length; i++) {
                                            var est = estados[i];
                                            if (pais_id === est.est_pais_id) {
                                                options += '<option value="' + est.est_id + '">' + est.est_nombre + '</option>';
                                            }
                                        }
                                        $('#est_nac').append(options);
                                        $('#est_nac').trigger('change');

                                    });
                                    $('#est_nac').change(function() {// mostrar_lugares
                                        var est_id = $(this).val();
        //                                                       $('#est_id').children().remove();
                                        $('#lug_nac').children().remove();
                                        var lugares = JSON.parse(trim($('#json_lugares').text()));
                                        var options = '';
                                        for (var i = 0; i < lugares.length; i++) {
                                            var lug = lugares[i];
                                            if (est_id === lug.lug_est_id) {
                                                options += '<option value="' + lug.lug_id + '">' + lug.lug_nombre + '</option>';
                                            }
                                        }
                                        $('#lug_nac').append(options);
                                    });
                                </script>
                                <?php if (!($_POST[lug_nac] > 0)) { ?>
                                <script>
                                    $('#pais_nac').trigger('change');
                                    $('#est_nac').trigger('change');
                                </script>
                                <?php } ?>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Lugar donde Reside</div>
                            <div id="CajaInput">
                                <?php $paises = FUNCIONES::lista_bd_sql("select * from ter_pais where pais_eliminado='No'"); ?>
                                <div style="float: left;">
                                    <select name="pais_res" id="pais_res" style="width: 150px;">
                                        <?php foreach ($paises as $pais) { ?>
                                            <option value="<?php echo $pais->pais_id; ?>" <?php echo $_POST[pais_res] == $pais->pais_id ? 'selected="true"' : '' ?>><?php echo $pais->pais_nombre; ?></option>
                                        <?php } ?>
                                    </select>
                                    <select name="est_res" id="est_res" style="width: 150px;">
                                        <?php $_estados = $_POST[est_res] > 0 ? FUNCIONES::lista_bd_sql("select * from ter_estado where est_pais_id='$_POST[pais_res]'") : null; ?>
                                        <?php if ($_estados) { ?>
                                            <?php foreach ($_estados as $est) { ?>
                                                <option value="<?php echo $est->est_id; ?>" <?php echo $_POST[est_res] == $est->est_id ? 'selected="true"' : '' ?>><?php echo $est->est_nombre; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                    <select name="lug_res" id="lug_res" style="width: 150px;"> 
                                        <?php $_lugares = $_POST[lug_res] > 0 ? FUNCIONES::lista_bd_sql("select * from ter_lugar where lug_est_id='$_POST[est_res]'") : null; ?>
                                        <?php if ($_lugares) { ?>
                                            <?php foreach ($_lugares as $lug) { ?>
                                                <option value="<?php echo $lug->lug_id; ?>" <?php echo $_POST[lug_res] == $lug->lug_id ? 'selected="true"' : '' ?>><?php echo $lug->lug_nombre; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                                <script>
                                    $('#pais_res').change(function() {// mostrar_estados
                                        var pais_id = $(this).val();
                                        $('#est_res').children().remove();
                                        $('#lug_res').children().remove();
                                        var estados = JSON.parse(trim($('#json_estados').text()));
                                        var options = '';
                                        for (var i = 0; i < estados.length; i++) {
                                            var est = estados[i];
                                            if (pais_id === est.est_pais_id) {
                                                options += '<option value="' + est.est_id + '">' + est.est_nombre + '</option>';
                                            }
                                        }
                                        $('#est_res').append(options);
                                        $('#est_res').trigger('change');

                                    });
                                    $('#est_res').change(function() {// mostrar_lugares
                                        var est_id = $(this).val();
        //                                                       $('#est_id').children().remove();
                                        $('#lug_res').children().remove();
                                        var lugares = JSON.parse(trim($('#json_lugares').text()));
                                        var options = '';
                                        for (var i = 0; i < lugares.length; i++) {
                                            var lug = lugares[i];
                                            if (est_id === lug.lug_est_id) {
                                                options += '<option value="' + lug.lug_id + '">' + lug.lug_nombre + '</option>';
                                            }
                                        }
                                        $('#lug_res').append(options);
                                    });
                                </script>
                                <?php if (!($_POST[lug_res] > 0)) { ?>
                                <script>
                                    $('#pais_res').trigger('change');
                                    $('#est_res').trigger('change');
                                </script>
                                <?php } ?>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Referencias</div>
                            <div id="CajaInput">
                                <input type="text" id="str_ref_nombre" value="" placeholder="Nombre" size="25">
                                <input type="text" id="str_ref_direccion" value="" placeholder="Direccion" size="35">
                                <input type="text" id="str_ref_telefono" value="" placeholder="Telefono" size="15">
                            </div>
                            <div id="CajaInput">
                                <img id="btn_agregar"src="images/boton_agregar.png" height="20px" style="cursor: pointer;">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >&nbsp;</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta" style="width: 470px "> 
                                    <table id="tab_urbanizacion" class="tab_lista_cuentas" >
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Direccion</th>
                                                <th>Telefono</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $ref_nombres=$_POST[ref_nombre];
                                            $ref_direcciones=$_POST[ref_direccion];
                                            $ref_telefonos=$_POST[ref_telefono];
                                            for($i=0;$i<count($ref_nombres);$i++){?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" class="ref_nombre" name="ref_nombre[]" value="<?php echo $ref_nombres[$i];?>">
                                                    <input type="hidden" class="ref_direccion" name="ref_direccion[]" value="<?php echo $ref_direcciones[$i];?>">
                                                    <input type="hidden" class="ref_telefono" name="ref_telefono[]" value="<?php echo $ref_telefonos[$i];?>">
                                                    <?php echo $ref_nombres[$i];?>
                                                </td>
                                                <td><?php echo $ref_direcciones[$i];?></td>
                                                <td><?php echo $ref_telefonos[$i];?></td>
                                                <td width="8%"><img class="img_del_detalle" src="images/retener.png"/></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                    </div>
                    <script>
                        
                        $('#btn_agregar').click(function(){
                            var nombre=trim($('#str_ref_nombre').val());
                            var direccion=trim($('#str_ref_direccion').val());
                            var telefono=trim($('#str_ref_telefono').val());
                            if(nombre!==''&& direccion!=='' && telefono!==''){
                                var bool= agregar_detalle_ref({nombre:nombre, direccion:direccion,telefono:telefono},'urbanizacion');
                                if(bool){
                                    $('#str_ref_nombre').val('');
                                    $('#str_ref_direccion').val('');
                                    $('#str_ref_telefono').val('');
                                    $('#str_ref_nombre').focus();
                                }
                            }
                            
                        });
                        
                        $('#str_ref_telefono').keypress(function(e){
                            if(e.keyCode===13){
                                $('#btn_agregar').trigger('click');
                                e.stopPropagation();
                                return false;
                            }
                            

                        });

                        function agregar_detalle_ref(objeto,input) {

//                            if (!$("#tab_"+input+' .ref_nombre[value='+objeto.nombre+']').length) {
                                var fila='';
                                fila += '<tr>';
                                fila += '   <td>';
                                fila += '       <input type="hidden" class="ref_nombre" name="ref_nombre[]" value="'+objeto.nombre+'">';
                                fila += '       <input type="hidden" class="ref_direccion" name="ref_direccion[]" value="'+objeto.direccion+'">';
                                fila += '       <input type="hidden" class="ref_telefono" name="ref_telefono[]" value="'+objeto.telefono+'">';
                                fila += '       ' + objeto.nombre;
                                fila += '   </td>';
                                fila += '   <td>';
                                fila += '       ' + objeto.direccion;
                                fila += '   </td>';
                                fila += '   <td>';
                                fila += '       ' + objeto.telefono;
                                fila += '   </td>';
                                fila += '   <td width="8%"><img class="img_del_detalle" src="images/retener.png"/></td>';
                                fila += '</tr>';
                                $("#tab_"+input+' tbody').append(fila);
                                return true;
//                            }else{
//                                return false;
//                            }
                        }
                        $(".img_del_detalle").live('click', function() {
                            $(this).parent().parent().remove();
                        });
                        
                    </script>
                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <?php if (!($ver)) { ?>
                                    <input type="submit" class="boton" name="" value="Guardar">
                                    <?php if($_GET[popup]=='1'){?>
                                        <input type="button" class="boton" name="" value="Cerrar" onclick="self.close();">
                                    <?php }else{?>
                                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                    <?php }?>
                                    
                                <?php } else { ?>
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                <?php } ?>
                            </center>
                        </div>
                    </div>
                    <script>
                        $('#frm_sentencia').submit(function (){
                            var nombre = trim($('#int_nombre').val());
                            var apellido= trim($('#int_apellido').val());
                            var ci = trim($('#int_ci').val());
                            var ci_exp= trim($('#int_ci_exp option:selected').val());
                            if(nombre===''){
                                $.prompt("El nombre es requerido");
                                return false;
                            }
                            if(apellido===''){
                                $.prompt("El apellido es requerido");
                                return false;
                            }
                            if(ci==='' || ci_exp===''){
                                $.prompt("El numero C.I. es requerido");
                                return false;
                            }
                            
                            return true;
                        });
                    </script>
                </div>
            </form>
        </div>
        <script>
            $('#int_fecha_nacimiento').mask('99/99/9999');
            $('#int_fecha_ingreso').mask('99/99/9999');
            
            
        </script>
        <?php
    }

    function insertar_tcp() {
        $verificar = NEW VERIFICAR;

//        $parametros[0] = array('int_nombre', 'int_apellido');
//        $parametros[1] = array($_POST['int_nombre'], $_POST['int_apellido']);
//        $parametros[2] = array('interno');
//
//        if ($verificar->validar($parametros)) {
            $conec = new ADO();
//            echo "<pre>";
//            print_r($_POST);
//            echo "</pre>";
//            return;

            $txt_ubicacion_nac = FUNCIONES::atributo_bd_sql("select concat(pais_nombre, ', ',est_nombre,', ',lug_nombre) as campo from ter_lugar, ter_estado, ter_pais where lug_est_id=est_id and est_pais_id=pais_id and lug_id=$_POST[lug_nac]");
            $txt_ubicacion_res = FUNCIONES::atributo_bd_sql("select concat(pais_nombre, ', ',est_nombre,', ',lug_nombre) as campo from ter_lugar, ter_estado, ter_pais where lug_est_id=est_id and est_pais_id=pais_id and lug_id=$_POST[lug_res]");

            $fecha_cre=date('Y-m-d H:i:s');
            $is_completo=0;
            
            
            
            $ref_nombres=$_POST[ref_nombre];
            $ref_direcciones=$_POST[ref_direccion];
            $ref_telefonos=$_POST[ref_telefono];
            $str_referencias="";
            if(count($ref_nombres)>0){
                $str_referencias.="[";
                for($i=0;$i<count($ref_nombres);$i++){
                    if($i>0){
                        $str_referencias.=",";
                    }

                    $str_referencias.="{\"nombre\":\"$ref_nombres[$i]\",\"direccion\":\"$ref_direcciones[$i]\",\"telefono\":\"$ref_telefonos[$i]\"}";
                }
                $str_referencias.="]";
            }
            
            if($_POST['int_email'] && $_POST['int_telefono'] && $_POST['int_celular'] &&  $_POST['int_direccion']
                && $_POST['int_fecha_nacimiento'] && $_POST[lug_nac] && $_POST[lug_res] && $str_referencias){
                $is_completo=1;
            }
            
            $fecha_nac=  FUNCIONES::get_fecha_mysql($_POST[int_fecha_nacimiento]);
            if ($_FILES['int_foto']['name'] <> "") {
                $result = $this->subir_imagen($nombre_archivo, $_FILES['int_foto']['name'], $_FILES['int_foto']['tmp_name']);

                $sql = "insert into interno(
                            int_nombre,int_apellido,int_email,int_foto,int_telefono,int_celular,
                            int_direccion,int_ci,int_ci_exp,int_fecha_nacimiento,int_fecha_ingreso,int_usu_id,
                            int_lug_nac,int_ubicacion_nac,int_lug_res,int_ubicacion_res,
                            int_fecha_cre,int_eliminado,int_completo,int_referencias
                        ) values (
                            '" . trim($_POST['int_nombre']) . "','" . trim($_POST['int_apellido']) . "','" . $_POST['int_email'] . "','" . $nombre_archivo . "','" . $_POST['int_telefono'] . "','" . $_POST['int_celular'] . "',
                            '" . $_POST['int_direccion'] . "','" . $_POST['int_ci'] . "','$_POST[int_ci_exp]','$fecha_nac','" . date('Y-m-d') . "','" . $_SESSION[id] . "',
                            '$_POST[lug_nac]','$txt_ubicacion_nac','$_POST[lug_res]','$txt_ubicacion_res',
                            '$fecha_cre','No','$is_completo','$str_referencias'
                        )";
                if (trim($result) <> '') {
                    $this->formulario->ventana_volver($result, $this->link . '?mod=' . $this->modulo);
                } else {
                    
                    $conec->ejecutar($sql,true,true);
                    $llave=ADO::$insert_id;
                    $persona=  FUNCIONES::objeto_bd_sql("select * from interno where int_id='$llave'");
                    $mensaje = 'Persona Agregada Correctamente!!!';
                    if($_GET[popup]=='1'){
                        ?>
                        <div class="ancho100">
                            <div class="msInformacion limpiar"><?php echo $mensaje?></div>
                        </div>
                        <div class="ancho100">
                            <input class="boton" type="button" style="clear:both;" value="Cerrar" onclick="self.close();">
                            <input class="boton" type="button" style="clear:both;" value="Seleccionar Persona" onclick="poner(<?php echo "'$persona->int_id','$persona->int_nombre $persona->int_apellido'"?>);">
                        </div>
                        <script>
                            function poner(id,valor){
                                var data={};
                                data.id=id;
                                data.nombre=valor;
                                window.opener.set_valor_interno(data);
                                self.close();
                                return false;
                            }			
                        </script>
                        <br>
                    <?php }else{
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
                    }
                }
            } else {
                $sql = "insert into interno(
                            int_nombre,int_apellido,int_email,int_telefono,int_celular,
                            int_direccion,int_ci,int_ci_exp,int_fecha_nacimiento,int_fecha_ingreso,int_usu_id,
                            int_lug_nac,int_ubicacion_nac,int_lug_res,int_ubicacion_res,
                            int_fecha_cre,int_eliminado,int_completo,int_referencias
                        ) values (
                            '" . trim($_POST['int_nombre']) . "','" . trim($_POST['int_apellido']) . "','" . $_POST['int_email'] . "','" . $_POST['int_telefono'] . "','" . $_POST['int_celular'] . "',
                            '" . $_POST['int_direccion'] . "','" . $_POST['int_ci'] . "','$_POST[int_ci_exp]','$fecha_nac','" . date('Y-m-d') . "','" . $_SESSION[id] . "',
                            '$_POST[lug_nac]','$txt_ubicacion_nac','$_POST[lug_res]','$txt_ubicacion_res',
                            '$fecha_cre','No','$is_completo','$str_referencias'
                        )";
                $conec->ejecutar($sql,true,true);
                $llave=ADO::$insert_id;
                $persona=  FUNCIONES::objeto_bd_sql("select * from interno where int_id='$llave'");
                $mensaje = 'Persona Agregada Correctamente!!!';
//                $mensaje = 'Compra/Venta de divisa Agregado Correctamente';
                
                if($_GET[popup]=='1'){
                    ?>
                    <div class="ancho100">
                        <div class="msInformacion limpiar"><?php echo $mensaje?></div>
                    </div>
                    <div class="ancho100">
                        <input class="boton" type="button" style="clear:both;" value="Cerrar" onclick="self.close();">
                        <input class="boton" type="button" style="clear:both; width: 120px"  value="Seleccionar Persona" onclick="poner(<?php echo "'$persona->int_id','$persona->int_nombre $persona->int_apellido'"?>);">
                    </div>
                    <script>
                        function poner(id,valor){
                            var data={};
                            data.id=id;
                            data.nombre=valor;
                            window.opener.set_valor_interno(data);
                            self.close();
                            return false;
                        }			
                    </script>
                    <br>
                <?php }else{
                    $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
                }
            }
//        } else {
//            $mensaje = 'La persona no puede ser agregada, por que existe una persona con ese nombre y apellido.';
//
//            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
//        }
    }

    function subir_imagen(&$nombre_imagen, $name, $tmp) {
        require_once('clases/upload.class.php');

        $nn = date('d_m_Y_H_i_s_') . rand();

        $upload_class = new Upload_Files();

        $upload_class->temp_file_name = trim($tmp);

        $upload_class->file_name = $nn . substr(trim($name), -4, 4);

        $nombre_imagen = $upload_class->file_name;

        $upload_class->upload_dir = "imagenes/persona/";

        $upload_class->upload_log_dir = "imagenes/persona/upload_logs/";

        $upload_class->max_file_size = 1048576;

        $upload_class->ext_array = array(".jpg", ".gif", ".png");

        $upload_class->crear_thumbnail = false;

        $valid_ext = $upload_class->validate_extension();

        $valid_size = $upload_class->validate_size();

        $valid_user = $upload_class->validate_user();

        $max_size = $upload_class->get_max_size();

        $file_size = $upload_class->get_file_size();

        $file_exists = $upload_class->existing_file();

        if (!$valid_ext) {

            $result = "La Extension de este Archivo es invalida, Intente nuevamente por favor!";
        } elseif (!$valid_size) {

            $result = "El Tamaño de este archivo es invalido, El maximo tamaño permitido es: $max_size y su archivo pesa: $file_size";
        } elseif ($file_exists) {

            $result = "El Archivo Existe en el Servidor, Intente nuevamente por favor.";
        } else {
            $upload_file = $upload_class->upload_file_with_validation();

            if (!$upload_file) {

                $result = "Su archivo no se subio correctamente al Servidor.";
            } else {
                $result = "";

                require_once('clases/class.upload.php');

                $mifile = 'imagenes/persona/' . $upload_class->file_name;

                $handle = new upload($mifile);

                if ($handle->uploaded) {
                    $handle->image_resize = true;

                    $handle->image_ratio = true;

                    $handle->image_y = 50;

                    $handle->image_x = 50;

                    $handle->process('imagenes/persona/chica/');

                    if (!($handle->processed)) {
                        echo 'error : ' . $handle->error;
                    }
                }
            }
        }

        return $result;
    }

    function modificar_tcp() {
        $verificar = NEW VERIFICAR;

//        $parametros[0] = array('int_nombre', 'int_apellido');
//        $parametros[1] = array($_POST['int_nombre'], $_POST['int_apellido']);
//        $parametros[2] = array('interno');
//        $parametros[3] = array(" and int_id <> '" . $_GET['id'] . "' ");
//
//        if ($verificar->validar($parametros)) {
            $conec = new ADO();
            $txt_ubicacion_nac = FUNCIONES::atributo_bd_sql("select concat(pais_nombre, ', ',est_nombre,', ',lug_nombre) as campo from ter_lugar, ter_estado, ter_pais where lug_est_id=est_id and est_pais_id=pais_id and lug_id=$_POST[lug_nac]");
            $txt_ubicacion_res = FUNCIONES::atributo_bd_sql("select concat(pais_nombre, ', ',est_nombre,', ',lug_nombre) as campo from ter_lugar, ter_estado, ter_pais where lug_est_id=est_id and est_pais_id=pais_id and lug_id=$_POST[lug_res]");
            $fecha_mod=date('Y-m-d H:i:s');
            
            
            
            $ref_nombres=$_POST[ref_nombre];
            $ref_direcciones=$_POST[ref_direccion];
            $ref_telefonos=$_POST[ref_telefono];
            $str_referencias="";
            if(count($ref_nombres)>0){
                $str_referencias.="[";
                for($i=0;$i<count($ref_nombres);$i++){
                    if($i>0){
                        $str_referencias.=",";
                    }

                    $str_referencias.="{\"nombre\":\"$ref_nombres[$i]\",\"direccion\":\"$ref_direcciones[$i]\",\"telefono\":\"$ref_telefonos[$i]\"}";
                }
                $str_referencias.="]";
            
            }
            
            $fecha_nac=  FUNCIONES::get_fecha_mysql($_POST[int_fecha_nacimiento]);
            
            
            $is_completo=0;
            if($_POST['int_email'] && $_POST['int_telefono'] && $_POST['int_celular'] &&  $_POST['int_direccion']
                && $_POST['int_fecha_nacimiento'] && $_POST[lug_nac] && $_POST[lug_res] && $str_referencias ){
                $is_completo=1;
            }
            
            if ($_FILES['int_foto']['name'] <> "") {
                $result = $this->subir_imagen($nombre_archivo, $_FILES['int_foto']['name'], $_FILES['int_foto']['tmp_name']);

                $sql = "update interno set 
                                    int_nombre='" .trim($_POST['int_nombre']) . "',
                                    int_apellido='" . trim($_POST['int_apellido']) . "',
                                    int_email='" . $_POST['int_email'] . "',
                                    int_foto='" . $nombre_archivo . "',
                                    int_telefono='" . $_POST['int_telefono'] . "',
                                    int_celular='" . $_POST['int_celular'] . "',
                                    int_direccion='" . $_POST['int_direccion'] . "',
                                    int_ci='" . $_POST['int_ci'] . "',
                                    int_ci_exp='" . $_POST['int_ci_exp'] . "',
                                    int_fecha_nacimiento='$fecha_nac',
                                    
                                    int_lug_nac='" . $_POST[lug_nac] . "',
                                    int_ubicacion_nac='" . $txt_ubicacion_nac . "',
                                    int_lug_res='" . $_POST[lug_res] . "',
                                    int_ubicacion_res='" . $txt_ubicacion_res . "',
                                    int_usu_mod='$_SESSION[id]',
                                    int_fecha_mod='$fecha_mod',
                                    int_referencias='$str_referencias',
                                    int_completo='$is_completo'
                                        
                                    where int_id = '" . $_GET['id'] . "'";

                if (trim($result) <> '') {
                    $this->formulario->ventana_volver($result, $this->link . '?mod=' . $this->modulo);
                } else {
                    $llave = $_GET['id'];
                    $mi = trim($_POST['fotooculta']);
                    if ($mi <> "") {
                        $mifile = "imagenes/persona/$mi";
                        @unlink($mifile);
                        $mifile2 = "imagenes/persona/chica/$mi";
                        @unlink($mifile2);
                    }
                    $conec->ejecutar($sql);
                    $mensaje = 'Persona Modificada Correctamente!!!';
                    if($_GET[popup]=='1'){
                        ?>
                        <div class="ancho100">
                            <div class="msInformacion limpiar"><?php echo $mensaje?></div>
                        </div>
                        <div class="ancho100">
                            <input class="boton" type="button" style="clear:both;" value="Cerrar" onclick="self.close();">
                        </div>
                        <br>
                    <?php }else{
                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
                    }
                }
            } else {

                $sql = "update interno set 
                            int_nombre='" . trim($_POST['int_nombre']) . "',
                            int_apellido='" . trim($_POST['int_apellido']) . "',
                            int_email='" . $_POST['int_email'] . "',
                            int_telefono='" . $_POST['int_telefono'] . "',
                            int_celular='" . $_POST['int_celular'] . "',
                            int_direccion='" . $_POST['int_direccion'] . "',
                            int_ci='" . $_POST['int_ci'] . "',
                            int_ci_exp='" . $_POST['int_ci_exp'] . "',
                            int_fecha_nacimiento='$fecha_nac',
                            int_lug_nac='" . $_POST[lug_nac] . "',
                            int_ubicacion_nac='" . $txt_ubicacion_nac . "',
                            int_lug_res='" . $_POST[lug_res] . "',
                            int_ubicacion_res='" . $txt_ubicacion_res . "',
                            int_usu_mod='$_SESSION[id]',
                            int_fecha_mod='$fecha_mod',
                            int_referencias='$str_referencias',
                            int_completo='$is_completo'
                            where int_id = '" . $_GET['id'] . "'";

                $conec->ejecutar($sql);
                $mensaje = 'Persona Modificada Correctamente!!!';
                if($_GET[popup]=='1'){
                    ?>
                    <div class="ancho100">
                        <div class="msInformacion limpiar"><?php echo $mensaje?></div>
                    </div>
                    <div class="ancho100">
                        <input class="boton" type="button" style="clear:both;" value="Cerrar" onclick="self.close();">
                    </div>
                    <br>
                <?php }else{
                    $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
                }
            }
//        } else {
//            $mensaje = 'La persona no puede ser modificada, por que existe una persona con ese nombre y apellido.';
//            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
//        }
    }

    function formulario_confirmar_eliminacion() {

        $mensaje = 'Esta seguro de eliminar la Persona?';

        $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'int_id');
    }

    function eliminar_tcp() {
        $int_id=$_POST[int_id];
        $verificar = NEW VERIFICAR;
        $usuario=  FUNCIONES::objeto_bd_sql("select * from ad_usuario where usu_per_id='$int_id'");

        if ($usuario) {
            $mensaje = 'La persona no puede ser eliminada, por que esta siendo utilizada en el modulo de usuario.';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            return;
        }
        
        $venta=  FUNCIONES::objeto_bd_sql("select * from venta where ven_int_id='$int_id'");
        if ($venta) {
            $mensaje = 'La persona no puede ser eliminada, por que esta siendo utilizada en el modulo de ventas.';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            return;
        }
        $ind=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_int_id='$int_id'");
        if ($ind) {
            $mensaje = 'La persona no puede ser eliminada, por que esta siendo utilizada en el modulo de deudas.';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            return;
        } 
        $reserva=  FUNCIONES::objeto_bd_sql("select * from reserva_terreno where res_int_id='$int_id'");
        if ($reserva) {
            $mensaje = 'La persona no puede ser eliminada, por que esta siendo utilizada en el modulo de Reserva.';
            $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
            return;
        } 
        
        $llave = $_POST['int_id'];

        $mi = $this->nombre_imagen($llave);

        if (trim($mi) <> "") {
            $mifile = "imagenes/persona/$mi";

            @unlink($mifile);

            $mifile2 = "imagenes/persona/chica/$mi";

            @unlink($mifile2);
        }

        $conec = new ADO();
        $fecha_mod=date('Y-m-d H:i:s');
        $sql = "update  interno set int_eliminado='Si', int_fecha_mod='$fecha_mod',int_usu_mod='$_SESSION[id]' where int_id='" . $_POST['int_id'] . "'";

        $conec->ejecutar($sql);

        $mensaje = 'Persona Eliminada Correctamente.';
        
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
    }

    function nombre_imagen($id) {
        $conec = new ADO();

        $sql = "select int_foto from interno where int_id='" . $id . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->int_foto;
    }

    function eliminar_imagen() {
        $conec = new ADO();

        $mi = $_GET['img'];

        $mifile = "imagenes/persona/$mi";

        @unlink($mifile);

        $mifile2 = "imagenes/persona/chica/$mi";

        @unlink($mifile2);

        $conec = new ADO();

        $sql = "update interno set 
						int_foto=''
						where int_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $mensaje = 'Imagen Eliminada Correctamente!';

        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=MODIFICAR&id=' . $_GET['id']);
    }

    function obtener_grupo_id($usu_id) {
        $conec = new ADO();

        $sql = "SELECT usu_gru_id FROM ad_usuario WHERE usu_id='$usu_id'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        return $objeto->usu_gru_id;
    }
    
    function buscar_interno(){
        $this->formulario->dibujar_cabecera();
        $valor = trim($_POST['valor']);
        $ci = trim($_POST['ci']);
            ?>		
            <script type="text/javascript" src="js/util.js"></script>
            <br>
            <center>
                <form name="form" id="form" method="POST" action="#">
                    <table align="center">
                        <tr>
                            <td class="txt_contenido" colspan="2" align="center">
                                Nombre    
                                <input name="valor" id="valor" type="text" class="caja_texto" size="30" value="<?php echo $valor; ?>">
                                CI    
                                <input name="ci" type="text" class="caja_texto" size="15" value="<?php echo $ci; ?>">
                                <input name="Submit" type="submit" class="boton" value="Buscar">
                                <input name="cerrar" type="button" class="boton" value="Cerrar" onclick="self.close()">
                            </td>
                        </tr>
                    </table>
                </form>
            </center>
            <script>
                $('#valor').select();
                function poner(id,valor){
                    var data={};
                    data.id=id;
                    data.nombre=valor;
                    var metodo=trim($('#metodo').val());
                    if(metodo===''){
                        window.opener.set_valor_interno(data)
                    }else{
                        eval('window.opener.'+metodo+'(data)');
                    }

                    self.close();
                    return false;
                }			
            </script>
            <input type="hidden" id="metodo" value="<?php echo $_GET[mt]?>">
        <?php
        $conec = new ADO();
        $filtro="";
        if($valor){
            $filtro.=" and concat(trim(int_nombre),' ',trim(int_apellido)) like '%$valor%'";
        }
        if($ci){
            $filtro.=" and int_ci like '%$ci%'";
        }
        
        $sql = "select * from interno where 1 $filtro order by int_id desc limit 20";        
        $conec->ejecutar($sql);

        $num = $conec->get_num_registros();

        echo '<table class="tablaLista" cellpadding="0" cellspacing="0">
                            <thead>
                            <tr>
                                    <th>Codigo</th>
                                    <th>Nombre</th>
                                    <th>C.I.</th>
                                    <th>Usuario</th>
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
                                     <td>' . $objeto->int_id. '</td>
                                     <td>' . $objeto->int_nombre.' '.$objeto->int_apellido . '</td>
                                     <td>' .  "$objeto->int_ci-$objeto->int_ci_exp". '</td>
                                     <td>' .  "$objeto->int_usu_id". '</td>
                                     <td><a href="javascript:poner(' . "'" . $objeto->int_id . "'" . ',' . "'" . $objeto->int_nombre . ' ' . $objeto->int_apellido . "'" . ');"><center><img src="images/ok.png" border="0" width="20px" height="20px"></center></a></td>
                               </tr>	 
                    ';

            $conec->siguiente();
        }
        ?>
                    </tbody></table>
        <?php
    }

}

        ?>