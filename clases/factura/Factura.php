<?php
require_once('config/database.conf.php');
require_once('clases/adodb/adodb.inc.php');
require_once('clases/coneccion.class.php');
require_once('control.php');

class Factura {

    const SUCURSAL_CASA_MATRIZ_ID = '0';

    private $conexion;
    private $estado_validos;

    function __construct() {
        $this->conexion = new ADO();
        $this->estado_validos = array('impreso', 'copia', 'anulado', 'consulta', 'creacion');
    }

    /**
     * Proceso para almacenar informacion que sea enviada 
     * por el cliente en el objeto $factura_datos, este objeto se debe enviar 
     * la siguiente llaves y su valor:
     * - nombre_cliente: String con el nombre o razon social para quien se emitira 
     * la factura.
     * - nit_cliente: String con el valor de NIT o CI(Cedula de Identidad) del cliente para quien se emitira 
     * la factura.
     * - fecha_transicion: String con el valor de la fecha para la cual se emitira la factura
     * en formato (YYYY-mm-dd).
     * - monto_transicion: Valor o monto con el que se emitira la factura en formato
     * donde la parte decimal separada por ".". <br>
     * Ej: 125744.43, 145.0, 10
     * Hasta cuatro decimales despues del punto.
     * En caso que uno de los parametros no se envie correctamente no se procesara 
     * el registro de los datos para emitir la factura, si ocurre un problema en el proceso de almacenamiento de 
     * la informacion retorna valor boolean false en otro caso retorna valor true.
     * @param stdClass $factura
     * @param int $sucursal_id: Id de la sucursal para la cual se emitira la factura.
     */
    public function guardar_factura(stdClass $factura,&$conec=null) {
        if($conec==null){
            $conec=new ADO();
        }
        $sql_sucursal = "select * from fac_sucursal where suc_id=$factura->sucursal_id";
//        $this->conexion->ejecutar($sql_sucursal);
        $sucursal = FUNCIONES::objeto_bd_sql($sql_sucursal);//$this->conexion->get_objeto();
        $fecha = new \DateTime($factura->fecha_transicion);

        if ($factura->nombre_cliente && isset($factura->nit_cliente) && $factura->fecha_transicion && $factura->fecha_transicion == $fecha->format('Y-m-d') && $factura->monto_transicion && is_numeric($factura->monto_transicion) && $sucursal) {
            $fecha_transicion = $factura->fecha_transicion;
            $numero_factura = $this->get_numero_factura_habilitado($fecha_transicion);
            if ($numero_factura > 0) {
                $fecha = $factura->fecha_transicion;
                // Obtener Dosificacion activa
                $sql_dosificacion = "select * from fac_dosificacion where '$fecha' BETWEEN dos_fecha_autorizacion_inicial and dos_fecha_autorizacion_final and dos_eliminado is null";
//                $this->conexion->ejecutar($sql_dosificacion);
                $dosificacion = FUNCIONES::objeto_bd_sql($sql_dosificacion);// $this->conexion->get_objeto();

                $datos = new stdClass();
                $nit_cliente = $factura->nit_cliente;
                $monto = $factura->monto_transicion;
                // cc = Codigo Control
                $fecha_cc = str_replace('-', '', $fecha_transicion);
                $generador_cc = new generar_control();
                $codigo_control = $generador_cc->generar($numero_factura, $nit_cliente, $fecha_cc, $monto, $dosificacion->dos_llave_dosificacion, $dosificacion->dos_numero_autorizacion);

                // Carga la informacion para almacenar en la base de datos.
                $datos->numero_factura = $numero_factura;
                $datos->codigo_control = $codigo_control;
                $datos->nit_cliente = $nit_cliente;
                $datos->nombre_cliente = $factura->nombre_cliente;
                $datos->fecha = $fecha_transicion;
                $datos->monto = $monto;
                $datos->importe = $factura->importe;
                $datos->sucursal_id = $factura->sucursal_id;
                $datos->dosificacion_id = $dosificacion->dos_id;
                $datos->fecha_limite_emision = $dosificacion->dos_fecha_autorizacion_final;
                $datos->tabla = $factura->tabla;
                $datos->tabla_id = $factura->tabla_id;
                $datos->detalles = $factura->detalles;

                $respuesta = $this->registrar_factura($datos,$conec);

                if ($respuesta) {
                    return $respuesta;
                }
            }
        }

        return false;
    }

    /**
     * Retorna el objeto de la sucursal con identificador igual
     * al enviado por parametro, en el caso que no encuentre informacion
     * retorna valor boolean false.
     * @param string $identificador
     * @return stdClass|boolean
     */
    public function get_sucursal($identificador) {
        $sql_sucursal = "select * from fac_sucursal where suc_identificador='$identificador'";
        $this->conexion->ejecutar($sql_sucursal);
        $sucursal = $this->conexion->get_objeto();
        if ($sucursal) {
            return $sucursal;
        }
        return false;
    }

    /**
     * Para almacenar toda la informacion enviada en la tabla fac_factura.
     * 
     * @param stdClass $datos
     * @return boolean
     */
    private function registrar_factura(stdClass $datos, &$conec) {
        if($conec==null){
            $conec=new ADO();
        }
        try {

            $sql_insert = "INSERT INTO 
			fac_factura 
				(`fac_numero`, 
				`fac_nit_cliente`, 
				`fac_nombre_cliente`, 
				`fac_fecha_transicion`, 
				`fac_monto_transicion`, 
				`fac_importe`, 
				`fac_fecha_registro`, 
				`fac_sucursal_id`, 
				`fac_dosificacion_id`, 
				`fac_fecha_limite_emision`, 
				`fac_codigo_control`,
				`fac_tabla`,
				`fac_tabla_id`
                                ) 
			VALUES 
				('" . $datos->numero_factura . "', 
				'" . $datos->nit_cliente . "', 
				'" . $datos->nombre_cliente . "', 
				'" . $datos->fecha . "', 
				" . $datos->monto . ",
				" . $datos->importe . ",
				'" . date('Y-m-d') . "',  
				'" . $datos->sucursal_id . "',  
				'" . $datos->dosificacion_id . "',
				'" . $datos->fecha_limite_emision . "',
				'" . $datos->codigo_control . "',
				'" . $datos->tabla . "',
				'" . $datos->tabla_id . "',
                                )"
            ;

            $conec->ejecutar($sql_insert, false);

            $factura_id = mysql_insert_id();
            $detalles=$datos->detalles();
            foreach ($detalles as $detalle) {
                $det=(Object) $detalle;
                $cantidad=$det->cantidad?$det->cantidad:'1';
                $sql_detalle = "insert into fac_factura_detalle (
                                    fde_fac_id,fde_concepto,fde_cantidad,fde_importe
                                ) VALUES (
                                    '$factura_id','$det->concepto','$cantidad',$det->importe
                                );";
                $conec->ejecutar($sql_detalle);
            }
            
            $this->guardar_hitorial($factura_id);

            return $factura_id;
        } catch (Exception $e) {
            return false;
        }
    }

    public function anular_factura($factura_id) {
        $sql_factura = "select * from fac_factura where fac_id=$factura_id";

        $this->conexion->ejecutar($sql_factura);
        $factura = $this->conexion->get_objeto();
        if ($factura && $factura->fac_estado != 'anulado') {
            $sql_anulado = "update fac_factura set fac_estado='anulado' where fac_id=$factura_id";

            $this->conexion->ejecutar($sql_anulado);
            $this->guardar_hitorial($factura_id, 'anulado');
            return true;
        }

        return false;
    }

    /**
     * Proceso para dar de baja factura por el valor del parametro
     * $codigo_control, si no se encuentra factura con el codigo de control
     * enviado o ya esta anulada la factura retorna valor boolean false en otro
     * caso retorna valor boolean true.
     * @param string $factura_codigo_control
     * @return boolean
     */
    public function anular_factura_por_codigo_control($codigo_control) {
        $sql_factura = "select * from fac_factura where fac_codigo_control=$codigo_control";

        $this->conexion->ejecutar($sql_factura);
        $factura = $this->conexion->get_objeto();
        if ($factura && $factura->fac_estado != 'anulado') {
            $factura_id = $factura->fac_id;
            $sql_anulado = "update fac_factura set fac_estado='anulado' where fac_id=$factura_id";

            $this->conexion->ejecutar($sql_anulado);
            $this->guardar_hitorial($factura_id, 'anulado');
            return true;
        }

        return false;
    }

    /**
     * @param int $factura_id
     * @param string $estado: 'impreso', 'copia', 'anulado', 'consulta', 'creacion'
     * @return boolean
     */
    public function guardar_hitorial($factura_id, $estado = 'creacion') {
        try {
            if (in_array($estado, $this->estado_validos)) {
                $usuario = new USUARIO;
                $int_id = $usuario->get_usu_per_id();
                $sql_historial = "INSERT INTO `fac_factura_historial`(
						`fhi_fac_numero`, 
						`fhi_int_id`, 
						`fhi_fecha_registro`, 
						`fhi_hora_registro`, 
						`fhi_estado`) 
						VALUES ($factura_id, $int_id, '" . date('Y-m-d') . "', '" . date('H:i:s') . "', '$estado')";
                $this->conexion->ejecutar($sql_historial);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function get_string_QR($factura_id) {
        $sql_factura = "select * from fac_factura where fac_id=$factura_id";

        $this->conexion->ejecutar($sql_factura);
        $factura = $this->conexion->get_objeto();
        if ($factura) {
            if (!$factura->fac_qr) {
                // Datos de configuracion
                $sql_config = "select * from fac_factura_configuracion";
                $this->conexion->ejecutar($sql_config);
                $config = $this->conexion->get_objeto();
                // datos de la factura
                $sql_factura = "SELECT * from fac_factura
				inner join fac_sucursal on(suc_id=fac_sucursal_id)
				inner join fac_dosificacion on(dos_id=fac_dosificacion_id)
				where fac_id=$factura_id";
                $this->conexion->ejecutar($sql_factura);
                $factura = $this->conexion->get_objeto();

                $val = explode('.', $factura->fac_monto_transicion);
                $entera = $val[0];
                $decimal = round('0.' . $val[1], 2);
                $decimal = str_replace('0.', '', $decimal);
                if ((int) $decimal < 10) {
                    $decimal .= '0';
                }
                $monto = $entera . '.' . $decimal;

                $string_qr = '';
                $string_qr .= $config->fcon_nit . '|';
                $string_qr .= $config->fcon_razon_social . '|';
                $string_qr .= $factura->fac_numero . '|';
                $string_qr .= $factura->dos_numero_autorizacion . '|';
                $string_qr .= str_replace('-', '', $factura->fac_fecha_transicion) . '|';
                $string_qr .= $monto . '|';
                $string_qr .= $factura->fac_codigo_control . '|';
                $string_qr .= str_replace('-', '', $factura->fac_fecha_limite_emision) . '|';
                // importe por CIE: 
                $string_qr .= '0|';
                // Importe por venta
                $string_qr .= '0|';
                $string_qr .= $factura->fac_nit_cliente . '|';
                $string_qr .= $factura->fac_nombre_cliente;

                $sql_update = "update fac_factura set fac_qr='$string_qr' where fac_id=$factura_id";
                $this->conexion->ejecutar($sql_update);

                return $string_qr;
            } else {
                return $factura->fac_qr;
            }
        }

        return '';
    }

    /**
     * Retorna numero entero habilitado para generar una factura
     * revisando un periodo con disificacion configurada, en el caso
     * que no exista dosificacion definida retorna valor 0.
     * 
     * @param string $fecha: String de fecha en formato (YYYY-mm-dd)
     * @return int
     */
    public function get_numero_factura_habilitado($fecha) {
        $sql_dosificacion = "select * from fac_dosificacion where '$fecha' BETWEEN dos_fecha_autorizacion_inicial and dos_fecha_autorizacion_final and dos_eliminado is null";

        $this->conexion->ejecutar($sql_dosificacion);
        if ($this->conexion->get_num_registros() > 0) {
            $dosificacion = $this->conexion->get_objeto();
            $sql_facturas = "select * from fac_factura where fac_dosificacion_id=$dosificacion->dos_id order by fac_numero DESC";
            $this->conexion->ejecutar($sql_facturas);
            $ultima_factura = $this->conexion->get_objeto();
            if ($this->conexion->get_num_registros() > 0) {
                $nro_factura = $ultima_factura->fac_numero;
                return ($nro_factura + 1);
            } else {
                return 1;
            }
        }

        return 0;
    }

    public function get_html_factura($factura_id) {
        require_once 'clases/factura/NumberToLetter.class.php';
        $numero_letra = new NumberToLetter();
        $conversor = new convertir();
        $sql_configuracion = "select * from fac_factura_configuracion";
        $this->conexion->ejecutar($sql_configuracion);
        $configuracion = $this->conexion->get_objeto();

        // Informacion de la factura, sucursal y dosificacion.
        $sql_factura = "SELECT * from fac_factura
		inner join fac_sucursal on(suc_id=fac_sucursal_id)
		inner join fac_dosificacion on(dos_id=fac_dosificacion_id)
		where fac_id=$factura_id";
        $this->conexion->ejecutar($sql_factura);
        $factura = $this->conexion->get_objeto();

        // Factura Detalle
        $detalles = array();
        $sql_detalle = "select * from fac_factura_detalle where fde_tabla='interno_deuda' and fde_fac_id=$factura_id";
        $this->conexion->ejecutar($sql_detalle);
        $registros = $this->conexion->get_num_registros();
        $conexion = new ADO();
        for ($index = 0; $index < $registros; $index++) {
            $detalle = $this->conexion->get_objeto();
            $sql_interno_deuda = "select * from interno_deuda where ind_id=$detalle->fde_tabla_id";
            $conexion->ejecutar($sql_interno_deuda);
            $interno_deuda = $conexion->get_objeto();
            $interno_id = $interno_deuda->ind_int_id;
            $detalles[] = $interno_deuda;
            $this->conexion->siguiente();
        }

        $titulo_factura = '';
        $texto_advertencia_SIN = '&quot;LA ALTERACI&Oacute;N, FALSIFICACI&Oacute;N O COMERCIALIZACI&Oacute;N ILEGAL DE ESTE DOCUMENTO, TIENE C&Aacute;RCEL&quot;';
        $string_qr = $this->get_string_QR($factura_id);

        // Registrar Historico por consulta
// 		$this->guardar_hitorial($factura_id, 'consulta');
        // Generar Imagen QR
        include ('lib/full/qrlib.php');
        include('config_qr_image.php');
        $tempDir = EXAMPLE_TMP_SERVERPATH;

        $codeContents = $string_qr;

        $fileName = 'orange_group_QR_' . md5($codeContents) . '.png';

        $pngAbsoluteFilePath = $tempDir . $fileName;
        $urlRelativeFilePath = EXAMPLE_TMP_URLRELPATH . $fileName;

        // generating
        if (!file_exists($pngAbsoluteFilePath)) {
            QRcode::png($codeContents, $pngAbsoluteFilePath);
        }

        // Para factura sin impresion y no anulada
        if ($factura->fac_estado == 'emitido' && $factura->fac_nro_impresion == 0) {
            $titulo_factura = 'factura original';

            // Para factura Anulada
        } elseif ($factura->fac_estado == 'anulado') {
            $titulo_factura = 'factura anulada';

            // Para factura impresa y no anuladada
        } elseif ($factura->fac_estado == 'emitido' && $factura->fac_nro_impresion > 0) {
            $titulo_factura = 'factura copia';
        }

        // Obtener sucursales
        $sql_sucursal = "select * from fac_sucursal order by suc_id asc";
        $this->conexion->ejecutar($sql_sucursal);
        $registros = $this->conexion->get_num_registros();

        // Inicio de buffer para almacenar el HTML generado de la factura
        ob_start();

        $pagina = "'fac_contenedor'";

        $page = "'about:blank'";

        $extpage = "'reportes'";

        $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";

        $extra1 = "'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
				<link href=clases/factura/css/factura.css rel=stylesheet type=text/css />
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

        echo '<table align=right border=0><tr><td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
			  c.document.write(' . $extra1 . ');
			  var dato = document.getElementById(' . $pagina . ').innerHTML;
			  c.document.write(dato);
			  c.document.write(' . $extra2 . '); c.document.close();
			  ">
			<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR" id="imprimir">
			</a>
			 <script>
			  	$("img#imprimir").click(function() {
			  		console.log("imprimio");
			  		var datos = {factura_id: ' . $factura_id . ', accion: "imprimir"};
						$.ajax({
							data:  datos,
							url:   "ajax.php",
							type:  "post",
							beforeSend: function () {
								console.log("Procesando petision");
							},
							success:  function (response) {
								// Cuando el servidor responde a tu peticion
								
								if (response == true) {
			  					console.log($($("img#imprimir")).parent());
			  						$($($($("img#imprimir")).parent()).parent()).append("<label id=registro_impresion>IMPRESION REGISTRADA</label>");
								} else {
			  						$($($($("img#imprimir")).parent()).parent()).append("<label id=registro_impresion style=background-color: red;>IMPRESION NO REGISTRADO</label>");
								}
			  					$("#registro_impresion").fadeOut(2000);
							}
						});
				});
			 </script>
			</table>
			';
        ?>
        <br><br>
        <link href="clases/factura/css/factura.css" rel="stylesheet" type="text/css">
        <br>
        <div class="fac_contenedor" id="fac_contenedor">
            <div class="fac_reporte_izquierdo">
                <table style="width: 90%;">
                    <tbody>
                        <tr align="center">
                            <td>
                                Colegio Evang&eacute;lico <br>
                                &quot;ANABAUTISTA&quot;<br>
        <?php echo htmlentities(strtoupper($configuracion->fcon_razon_social)); ?>
                            </td>
                        </tr>
                        <tr align="center">
                            <td>
        <?php
        if ($factura->suc_identificador == '0') {
            echo 'Casa Matriz y Sucursal I';
        } else {
            echo $factura->suc_identificador;
        }
        ?>
                            </td>
                        </tr>
                        <tr align="center">
                            <td><?php echo htmlentities($factura->suc_direccion); ?></td>
                        </tr>
                        <!-- Para cargar informacion Telefono -->
                                <?php if ($factura->suc_telefono): ?>
                            <tr align="center">
                                <td>Tel&eacute;fono(s): <?php echo $factura->suc_telefono ?></td>
                            </tr>
        <?php endif; ?>
                        <!-- Para cargar informacion Celular -->
                        <?php if ($factura->suc_celular): ?>
                            <tr align="center">
                                <td>Celular(es): <?php echo $factura->suc_celular ?></td>
                            </tr>
                        <?php endif; ?>
                        <!-- Para cargar informacion Alcaldia -->
                        <?php if ($factura->suc_alcaldia): ?>
                            <tr align="center">
                                <td><?php echo $factura->suc_alcaldia ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr align="center">
                            <td>
                                <hr>
                                <strong> <?php echo strtoupper($titulo_factura); ?></strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <table>
                    <tbody>
                        <tr align="center">
                            <td>NIT : <?php echo $configuracion->fcon_nit ?></td>
                        </tr>
                        <tr align="center">
                            <td>FACTURA N&ordm; <?php echo $factura->fac_numero ?></td>
                        </tr>
                        <tr align="center">
                            <td>AUTORIZACI&Oacute;N N&ordm; <?php echo $factura->dos_numero_autorizacion; ?></td>
                        </tr>
                    </tbody>
                </table>

                <br>
                <table style="width: 90%">
                    <tbody>
                        <tr align="center">
                            <td>
                                Actividad Econ&oacute;mica : <?php echo $configuracion->fcon_actividad_economica ?>
                                <br>
                                <hr>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <table style="width: 90%;">
                    <tbody>
                        <tr align="left">
                            <td>
                                Fecha : <?php echo $conversor->get_fecha_latina($factura->fac_fecha_transicion); ?>
                                &nbsp;&nbsp; 
                                Hora: <?php echo date('H:i'); ?>
                            </td>
                        </tr>
                        <tr align="left">
                            <td>
                                Se&ntilde;or(es) : <?php echo $factura->fac_nombre_cliente; ?>
                            </td>
                        </tr>
                        <tr align="left">
                            <td>
                                NIT/CI : <?php echo $factura->fac_nit_cliente; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <hr style="width: 90%;">
                <table style="width: 90%;">
                    <tbody>
                        <tr align="left">
                            <td>
                                <strong>Estudiante : </strong><br> <?php echo $this->get_nombre_persona($interno_id) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <table style="width: 90%;">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><hr></td>
                            <td><hr></td>
                        </tr>
        <?php
        foreach ($detalles as $detalle) :
            ?>
                            <tr>
                                <td align="left">
            <?php echo $detalle->ind_concepto ?>
                                </td>
                                <td align="center">
                            <?php echo $detalle->ind_monto ?>
                                </td>
                            </tr>
        <?php endforeach; ?>
                        <tr>
                            <td><hr></td>
                            <td><hr></td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <table style="width: 87%;">
                    <tbody>
                        <tr align="right">
                            <td>
                                Total Bs. <?php
        $val = explode('.', $factura->fac_monto_transicion);
        $entera = $val[0];
        $decimal = round('0.' . $val[1], 2);
        $decimal = str_replace('0.', '', $decimal);
        if ((int) $decimal < 10) {
            $decimal .= '0';
        }
        echo $entera . '.' . $decimal;
        ?>
                            </td>
                        <tr align="left">
                            <td>
                                Son : <?php
                                $val = explode('.', $factura->fac_monto_transicion);
                                echo strtoupper($numero_letra->to_word($val[0]));
                                if (strlen($val[1]) == 1)
                                    echo '&nbsp;&nbsp;' . $val[1] . '0/100 ' . strtoupper('bolivianos');
                                else {
                                    $decimal = round('0.' . $val[1], 2);
                                    $decimal = str_replace('0.', '', $decimal);
                                    if ((int) $decimal < 10) {
                                        $decimal .= '0';
                                    }
                                    echo '&nbsp;&nbsp;' . $decimal . '/100 ' . strtoupper('bolivianos');
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <hr style="width: 90%;">
                <br>
                <table style="width: 90%;">
                    <tbody>
                        <tr align="left">
                            <td>C&Oacute;DIGO DE CONTROL: <?php echo $factura->fac_codigo_control ?></td>
                        </tr>
                        <tr align="left">
                            <td>FECHA LIMITE DE EMISI&Oacute;N: <?php echo $conversor->get_fecha_latina($factura->fac_fecha_limite_emision); ?></td>
                        </tr>
                        <tr align="center">
                            <td><img alt="" src="clases/factura/<?php echo $urlRelativeFilePath; ?>" width="94.48px" height="94.48px"></td>
                        </tr>
                    </tbody>
                </table>
                <div align="right" style="width: 87%">
                    <strong style="font-size: 8px !important;"><?php echo $texto_advertencia_SIN ?></strong>
                </div>
            </div>

            <div class="fac_div_central_rerpote" style="display: none;">
            </div>

            <div class="fac_reporte_derecho" style="display: none;">
                <br>
                <table style="width: 90%;">
                    <tbody>
                        <tr align="center">
                            <td><img src="imagenes/micro.png" width="40%" height="70px"alt=""></td>
                        </tr>
                        <tr align="center">
                            <td><strong>NIT : <?php echo $configuracion->fcon_nit ?></strong></td>
                        </tr>
                    </tbody>
                </table>
        <?php
        for ($index = 0; $index < $registros; $index++):
            $sucursal = $this->conexion->get_objeto();
            ?>
                    <table style="width: 95%;">
                        <tbody>
                            <tr align="center">
            <?php if ($sucursal->suc_identificador == '0') { ?>
                                    <td><strong>CASA MATRIZ</strong></td>
                    <?php } else { ?>
                                    <td><strong><?php echo $sucursal->suc_identificador ?></strong></td>
                    <?php } ?>
                            </tr>
                            <tr align="center">
                                <td><?php echo $sucursal->suc_direccion ?></td>
                            </tr>
                            <!-- Para cargar informacion Telefono -->
                                <?php if ($sucursal->suc_telefono): ?>
                                <tr align="center">
                                    <td>Tel&eacute;fono(s): <?php echo $sucursal->suc_telefono ?></td>
                                </tr>
            <?php endif; ?>
                            <!-- Para cargar informacion Celular -->
            <?php if ($sucursal->suc_celular): ?>
                                <tr align="center">
                                    <td>Celular(es): <?php echo $sucursal->suc_celular ?></td>
                                </tr>
            <?php endif; ?>
                            <!-- Para cargar informacion Alcaldia -->
                            <?php if ($sucursal->suc_alcaldia): ?>
                                <tr align="center">
                                    <td><?php echo $sucursal->suc_alcaldia ?></td>
                                </tr>
            <?php endif; ?>
                        </tbody>
                    </table>
                    <br><br>
                            <?php
                            $this->conexion->siguiente();
                        endfor;
                        ?>
            </div>


        </div>
                <?php
                // Fin de buffer para almacenar el HTML generado de la factura
                $html = ob_get_contents();
                ob_clean();

                return $html;
            }

            private function get_nombre_persona($interno_id) {
                if ($interno_id) {
                    $conec = new ADO();

                    $sql = "select int_nombre,int_apellido from interno where int_id=$interno_id";

                    $conec->ejecutar($sql);

                    $objeto = $conec->get_objeto();

                    return $objeto->int_nombre . ' ' . $objeto->int_apellido;
                }
                return '';
            }

            public function registrar_impresion($factura_id) {
                $sql_factura = "select * from fac_factura where fac_id=$factura_id";

                $this->conexion->ejecutar($sql_factura);
                $factura = $this->conexion->get_objeto();
                if ($factura->fac_id) {
                    $cantidad_impresion = $factura->fac_nro_impresion;
                    $cantidad_impresion++;
                    $sql_update_factura = "update fac_factura set fac_nro_impresion=$cantidad_impresion where fac_id=$factura_id";

                    $this->conexion->ejecutar($sql_update_factura);
                    if ($cantidad_impresion > 1) {
                        $this->guardar_hitorial($factura_id, 'copia');
                    } else {
                        $this->guardar_hitorial($factura_id, 'impreso');
                    }
                    return true;
                }
                return false;
            }

        }