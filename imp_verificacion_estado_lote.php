
<?php
if (!isset($_GET[exp])) {
    ?>
    <link href="css/estilos.css" rel="stylesheet" type="text/css">
    <?php
}
ini_set('memory_limit', '2048M');
ini_set('display_errors', 'On');
require_once('config/database.conf.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('mysql.php');

if (isset($_GET[urb]) && isset($_GET[estado])) {
    if ($_GET[modo] == 'csv') {
        procesar($_GET[urb], $_GET[estado]);    
    } else {
        procesar_html($_GET[urb], $_GET[estado]);
    }
} else {
    echo "<p style='color:red'>Especifique el ID de la urbanizacion y el estado de los lotes...</p>";
}

function obtener_linea($arr_datos) {
    $s = '"' . implode('";"', $arr_datos) . '"' . "\n";
    return $s;
}

function procesar_html($urb_id, $estado){
    $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$urb_id'");
    $arr_colores = array(
        'Disponible' => 'green',
        'Reservado' => 'blue',
        'Vendido' => 'red',
        'Bloqueado' => 'gray',
        'Conflicto' => 'purple');
    
        ?>
    <link type="text/css" href="css/estilos.css"  rel="stylesheet"/>
        <center>
                <center><h2>URB:<?php echo strtoupper($urbanizacion->urb_nombre); ?> - LOTES <?php echo strtoupper($estado); ?>S</h2></center>
            <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Uv</th>
                        <th>Manzano</th>
                        <th>Lote</th>
                        <th>Descripcion</th>
                        <th class="tOpciones" >Estado</th>
                        <th class="tOpciones" >Obs.</th>
                    </tr>		
                </thead>
                <tbody>
            <?php
    
        $sql = "SELECT 
                urb_nombre,man_nro,lot_nro,zon_nombre,uv_nombre,lot_id
                FROM 
                lote 
                inner join manzano on (lot_man_id=man_id)
                inner join urbanizacion on (man_urb_id=urb_id)
                inner join uv on (lot_uv_id=uv_id)
                inner join zona on (lot_zon_id=zon_id)
                where lot_estado='$estado'
                and urb_id='$urb_id'
                order by uv_nombre,man_nro,lot_nro asc";

        $lotes = FUNCIONES::objetos_bd_sql($sql);

        for ($i = 0; $i < $lotes->get_num_registros(); $i++) {

            $lot = $lotes->get_objeto();
            $resultado = verificar_datos_lote($lot->lot_id, $estado);
            $color = ($resultado == 'Todo en Orden') ? $arr_colores[$estado] : 'purple';
            $estado_op = ($resultado == 'Todo en Orden') ? $resultado : 'CONFLICTO';
            $desc_resultado = ($resultado == 'Todo en Orden') ? '' : $resultado;

            if ($resultado == 'Todo en Orden') {
                $lotes->siguiente();
                continue;
            }
            
            echo '<tr>';

            echo "<td>";
            echo $lot->lot_id;
            echo "</td>";

            echo "<td>";
            echo $lot->uv_nombre;
            echo "</td>";

            echo "<td>";
            echo $lot->man_nro;
            echo "</td>";

            echo "<td>";
            echo $lot->lot_nro;
            echo "</td>";

            echo "<td>";
            echo datos_lote($lot->lot_id);
            echo "</td>";
            echo "<td style='color:white; background-color:$color'>";
            echo $estado_op;
            echo "</td>";

            echo "<td style='color:white; background-color:$color'>";
            echo $desc_resultado;
            echo "</td>";
            echo "</tr>";
            
            $lotes->siguiente();
        }
        
            ?>

                        </tbody>
                    </table>
                </center>

            <?php
        
        
}

function procesar($urb_id, $estado) {
    $urbanizacion = FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$urb_id'");
    $arr_colores = array(
        'Disponible' => 'green',
        'Reservado' => 'blue',
        'Vendido' => 'red',
        'Bloqueado' => 'gray',
        'Conflicto' => 'purple');
    if (!isset($_GET[exp])) {
        ?>
        <center>
        <!--        <center><h2>URB:<?php echo strtoupper($urbanizacion->urb_nombre); ?> - LOTES <?php echo strtoupper($estado); ?>S</h2></center>
            <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Uv</th>
                        <th>Manzano</th>
                        <th>Lote</th>
                        <th>Descripcion</th>
                        <th class="tOpciones" >Estado</th>
                        <th class="tOpciones" >Obs.</th>
                    </tr>		
                </thead>
                <tbody>-->
            <?php
        }
        $sql = "SELECT 
					urb_nombre,man_nro,lot_nro,zon_nombre,uv_nombre,lot_id
					FROM 
					lote 
					inner join manzano on (lot_man_id=man_id)
					inner join urbanizacion on (man_urb_id=urb_id)
					inner join uv on (lot_uv_id=uv_id)
					inner join zona on (lot_zon_id=zon_id)
					where lot_estado='$estado'
					and urb_id='$urb_id'
					order by uv_nombre,man_nro,lot_nro asc";

        $lotes = FUNCIONES::objetos_bd_sql($sql);
        $arr_lineas_csv = array();

        for ($i = 0; $i < $lotes->get_num_registros(); $i++) {

            $lot = $lotes->get_objeto();
            $resultado = verificar_datos_lote($lot->lot_id, $estado);
            $color = ($resultado == 'Todo en Orden') ? $arr_colores[$estado] : 'purple';
            $estado_op = ($resultado == 'Todo en Orden') ? $resultado : 'CONFLICTO';
            $desc_resultado = ($resultado == 'Todo en Orden') ? '' : $resultado;

            if (!isset($_GET[exp])) {
//                    echo '<tr>';
//
//                    echo "<td>";
//                    echo $lot->lot_id;
//                    echo "</td>";
//
//                    echo "<td>";
//                    echo $lot->uv_nombre;
//                    echo "</td>";
//
//                    echo "<td>";
//                    echo $lot->man_nro;
//                    echo "</td>";
//
//                    echo "<td>";
//                    echo $lot->lot_nro;
//                    echo "</td>";
//
//                    echo "<td>";
//                    echo datos_lote($lot->lot_id);
//                    echo "</td>";
//                    echo "<td style='color:white; background-color:$color'>";
//                    echo $estado_op;
//                    echo "</td>";
//
//                    echo "<td style='color:white; background-color:$color'>";
//                    echo $desc_resultado;
//                    echo "</td>";
//                    echo "</tr>";
            }

            $datos = array($lot->lot_id, $lot->uv_nombre, $lot->man_nro, $lot->lot_nro, $estado_op, strip_tags($desc_resultado));
            $arr_lineas_csv[] = $datos;
            $lotes->siguiente();
        }
        if (!isset($_GET[exp])) {
            ?>

            <!--            </tbody>
                    </table>
                </center>-->

            <?php
        }
        $nombre_archivo = $urbanizacion->urb_id . "_" . strtoupper($urbanizacion->urb_nombre) . "_" . strtoupper($estado) . ".csv";
        exportar_csv(array('nom_archivo' => $nombre_archivo, 'datos' => $arr_lineas_csv));
    }

    function exportar_csv($datos) {
        $datos = (object) $datos;
        $nombre_archivo = $datos->nom_archivo;
        $arr_lineas_csv = $datos->datos;
        header("Content-Type: text/csv");
        header("content-disposition: attachment;filename=" . $nombre_archivo);
        echo '"ID";"UV";"MZ";"LOTE";"ESTADO";"OBSERVACIONES"' . "\n";

        for ($i = 0; $i < count($arr_lineas_csv); $i++) {
            echo obtener_linea($arr_lineas_csv[$i]);
        }
    }

    function nombre_persona($usuario) {
        $query = new QUERY();

        $sql = "select per_nombre,per_apellido from ad_usuario inner join gr_persona on (usu_id='$usuario' and usu_per_id=per_id)";

        $query->consulta($sql);

        list($per_nombre, $per_apellido) = $query->valores_fila();

        return $per_nombre . ' ' . $per_apellido;
    }

    function datos_lote($lote) {

        $sql = "select urb_nombre,man_nro,lot_nro,zon_nombre,uv_nombre 
	from 
	lote
	inner join zona on (lot_id='" . $lote . "' and lot_zon_id=zon_id)
	inner join uv on (lot_uv_id=uv_id)	
	inner join manzano on (lot_man_id=man_id)
	inner join urbanizacion on(man_urb_id=urb_id)";

        $lot = FUNCIONES::objeto_bd_sql($sql);
        $des = 'Urb:' . $lot->urb_nombre . ' - Mza:' . $lot->man_nro . ' - Lote:' . $lot->lot_nro . ' - Zona:' . $lot->zon_nombre . ' - UV:' . $lot->uv_nombre;
        return $des;
    }

    function verificar_datos_lote($lote, $estado_actual_lote) {
        $query = new QUERY();

        if ($estado_actual_lote == 'Disponible') {
            $error = false;

            $salida = '';

            /*             * ***** Inicio tabla VENTA ****** */
            $sql = "select ven_id,ven_estado,ven_lot_id from venta 
            where ven_lot_id=$lote 
                and ven_estado in ('Pendiente','Pagado') 
                order by ven_id desc";
            $query->consulta($sql);
            $num = $query->num_registros();

            if ($num > 0) {
                for ($i = 0; $i < $num; $i++) {
                    list($ven_id, $ven_estado, $ven_lot_id) = $query->valores_fila();

                    $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '|<br>';
                }
            } else {
                $sql2 = "select ven_id,ven_estado from venta_lote 
                    inner join venta on (vlot_ven_id=ven_id)
                    where vlot_lot_id=$lote and ven_estado in ('Pendiente','Pagado')";
                $query2 = new QUERY();
                $query2->consulta($sql2);

                $num2 = $query2->num_registros();

                if ($num2 > 0) {
                    for ($j = 0; $j < $num2; $j++) {
                        list($ven_id, $ven_estado) = $query2->valores_fila();

                        $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '|<br>';
                    }
                }
            }
            /*             * ***** Inicio tabla VENTA ****** */

            /*             * ***** Inicio tabla RESERVA ****** */
            $sql = "select res_id,res_estado,res_lot_id from reserva_terreno where res_lot_id=$lote and res_estado in ('Pendiente','Habilitado','Venta') order by res_id desc";
            $query->consulta($sql);
            $num = $query->num_registros();

            if ($num > 0) {
                for ($i = 0; $i < $num; $i++) {
                    list($res_id, $res_estado, $res_lot_id) = $query->valores_fila();

                    $salida = $salida . 'Reserva:' . $res_id . ' - Estado:' . $res_estado . '|<br>';
                }
            }
            /*             * ***** Inicio tabla RESERVA ****** */

            /*             * ***** Inicio tabla BLOQUE ****** */
            $sql = "select bloq_id,bloq_estado,bloq_lot_id from bloquear_terreno where bloq_lot_id=$lote and bloq_estado in ('Habilitado') order by bloq_id desc";
            $query->consulta($sql);
            $num = $query->num_registros();

            if ($num > 0) {
                for ($i = 0; $i < $num; $i++) {
                    list($bloq_id, $bloq_estado, $bloq_lot_id) = $query->valores_fila();

                    $salida = $salida . 'Bloqueo Nro:' . $bloq_id . ' - Estado:' . $bloq_estado . '|<br>';
                }
            }
            /*             * ***** Inicio tabla RESERVA ****** */

            if ($salida == '')
                $salida = 'Todo en Orden';
            else
                $salida = '<b>' . $salida . '</b>';

            return $salida;
        }
        else {
            if ($estado_actual_lote == 'Reservado') {

                $error = false;
                $salida = '';
                $salida_por_estado = "";

                /*                 * ***** Inicio tabla VENTA ****** */
                $sql = "select ven_id,ven_estado,ven_lot_id from venta where ven_lot_id=$lote and ven_estado in ('Pendiente','Pagado') order by ven_id desc";
                $query->consulta($sql);
                $num = $query->num_registros();

                if ($num > 0) {
                    for ($i = 0; $i < $num; $i++) {
                        list($ven_id, $ven_estado, $ven_lot_id) = $query->valores_fila();

                        $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '|<br>';
                    }
                } else {
                    $sql2 = "select ven_id,ven_estado from venta_lote 
                    inner join venta on (vlot_ven_id=ven_id)
                    where vlot_lot_id=$lote and ven_estado in ('Pendiente','Pagado')";
                    $query2 = new QUERY();
                    $query2->consulta($sql2);

                    $num2 = $query2->num_registros();

                    if ($num2 > 0) {
                        for ($j = 0; $j < $num2; $j++) {
                            list($ven_id, $ven_estado) = $query2->valores_fila();

                            $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '|<br>';
                        }
                    }
                }
                /*                 * ***** Inicio tabla VENTA ****** */

                /*                 * ***** Inicio tabla RESERVA ****** */

                $sql = "select res_id,res_estado,res_lot_id from reserva_terreno where res_lot_id=$lote and res_estado in ('Pendiente','Habilitado','Venta') order by res_id desc";
                $query->consulta($sql);
                $num = $query->num_registros();

                if ($num == 0) {
                    $salida = 'No hay ninguna reserva activa con este lote<br>';
                } else if ($num > 1) {

                    for ($i = 0; $i < $num; $i++) {
                        list($res_id, $res_estado, $res_lot_id) = $query->valores_fila();
                        $salida = $salida . 'Reserva:' . $res_id . ' - Estado:' . $res_estado . '|<br>';
                    }
                } else if ($num == 1) {
                    for ($i = 0; $i < $num; $i++) {
                        list($res_id, $res_estado, $res_lot_id) = $query->valores_fila();
                        $salida_por_estado = 'Reserva:' . $res_id . ' - Estado:' . $res_estado . '|<br>';
                    }
                }

                /*                 * ***** Inicio tabla RESERVA ****** */

                /*                 * ***** Inicio tabla BLOQUE ****** */
                $sql = "select bloq_id,bloq_estado,bloq_lot_id from bloquear_terreno where bloq_lot_id=$lote and bloq_estado in ('Habilitado') order by bloq_id desc";
                $query->consulta($sql);
                $num = $query->num_registros();

                if ($num > 0) {
                    for ($i = 0; $i < $num; $i++) {
                        list($bloq_id, $bloq_estado, $bloq_lot_id) = $query->valores_fila();
                        $salida = $salida . 'Bloqueo Nro:' . $bloq_id . ' - Estado:' . $bloq_estado . '|<br>';
                    }
                }
                /*                 * ***** Inicio tabla RESERVA ****** */

                if ($salida == '') {
                    $salida = 'Todo en Orden';
                } else {
                    $salida = $salida_por_estado . $salida;
                    $salida = '<b>' . $salida . '</b>';
                }

                return $salida;
            } else {
                if ($estado_actual_lote == 'Bloqueado') {
                    $error = false;
                    $salida = '';
                    $salida_por_estado = "";

                    /*                     * ***** Inicio tabla VENTA ****** */
                    $sql = "select ven_id,ven_estado,ven_lot_id from venta where ven_lot_id=$lote and ven_estado in ('Pendiente','Pagado') order by ven_id desc";
                    $query->consulta($sql);
                    $num = $query->num_registros();

                    if ($num > 0) {
                        for ($i = 0; $i < $num; $i++) {
                            list($ven_id, $ven_estado, $ven_lot_id) = $query->valores_fila();

                            $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '|<br>';
                        }
                    } else {
                        $sql2 = "select ven_id,ven_estado from venta_lote 
                    inner join venta on (vlot_ven_id=ven_id)
                    where vlot_lot_id=$lote and ven_estado in ('Pendiente','Pagado')";
                        $query2 = new QUERY();
                        $query2->consulta($sql2);

                        $num2 = $query2->num_registros();

                        if ($num2 > 0) {
                            for ($j = 0; $j < $num2; $j++) {
                                list($ven_id, $ven_estado) = $query2->valores_fila();

                                $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '|<br>';
                            }
                        }
                    }
                    /*                     * ***** Inicio tabla VENTA ****** */

                    /*                     * ***** Inicio tabla RESERVA ****** */
                    $sql = "select res_id,res_estado,res_lot_id from reserva_terreno where res_lot_id=$lote and res_estado in ('Pendiente','Habilitado','Venta') order by res_id desc";
                    $query->consulta($sql);
                    $num = $query->num_registros();

                    if ($num > 0) {
                        for ($i = 0; $i < $num; $i++) {
                            list($res_id, $res_estado, $res_lot_id) = $query->valores_fila();

                            $salida = $salida . 'Reserva:' . $res_id . ' - Estado:' . $res_estado . '|<br>';
                        }
                    }
                    /*                     * ***** Fin tabla RESERVA ****** */

                    /*                     * ***** Inicio tabla BLOQUEO ****** */
                    $sql = "select bloq_id,bloq_estado,bloq_lot_id from bloquear_terreno where bloq_lot_id=$lote and bloq_estado in ('Habilitado') order by bloq_id desc";
                    $query->consulta($sql);
                    $num = $query->num_registros();

                    if ($num == 0) {
                        $salida = $salida . 'El Lote no se encuentre en ningun Bloqueo<br>';
                    } else if ($num > 1) {
                        for ($i = 0; $i < $num; $i++) {
                            list($bloq_id, $bloq_estado, $bloq_lot_id) = $query->valores_fila();
                            $salida = $salida . 'Bloqueo Nro:' . $bloq_id . ' - Estado:' . $bloq_estado . '|<br>';
                        }
                    } else if ($num == 1) {
                        for ($i = 0; $i < $num; $i++) {
                            list($bloq_id, $bloq_estado, $bloq_lot_id) = $query->valores_fila();
                            $salida_por_estado = 'Bloqueo Nro:' . $bloq_id . ' - Estado:' . $bloq_estado . '|<br>';
                        }
                    }
                    /*                     * ***** Fin tabla BLOQUEO ****** */

                    if ($salida == '') {
                        $salida = 'Todo en Orden';
                    } else {
                        $salida = $salida_por_estado . $salida;
                        $salida = '<b>' . $salida . '</b>';
                    }

                    return $salida;
                } else {
                    if ($estado_actual_lote == 'Vendido') {

                        $error = false;
                        $salida = '';
                        $salida_por_estado = "";
                        /*                         * ***** Inicio tabla VENTA ****** */
                        $sql = "select ven_id,ven_estado,ven_lot_id from venta where ven_lot_id=$lote and ven_estado in ('Pendiente','Pagado') order by ven_id desc";

                        $query->consulta($sql);
                        $num = $query->num_registros();

                        if ($num == 0) {

                            $sql2 = "select ven_id,ven_estado from venta_lote 
                            inner join venta on (vlot_ven_id=ven_id)
                            where vlot_lot_id=$lote and ven_estado in ('Pendiente','Pagado')";
                            $query2 = new QUERY();
                            $query2->consulta($sql2);

                            $num2 = $query2->num_registros();

                            if ($num2 > 0) {
                                if ($num2 > 1) {
                                    for ($j = 0; $j < $num2; $j++) {
                                        list($ven_id, $ven_estado) = $query2->valores_fila();

                                        $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '|<br>';
                                    }
                                }
                            } else {
                                $salida = 'Lote no se encuentra en ninguna venta<br>';
                            }
                        } else if ($num > 1) {

                            for ($i = 0; $i < $num; $i++) {
                                list($ven_id, $ven_estado, $ven_lot_id) = $query->valores_fila();
                                $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '|<br>';
                            }
                        } else if ($num == 1) {

                            for ($i = 0; $i < $num; $i++) {
                                list($ven_id, $ven_estado, $ven_lot_id) = $query->valores_fila();
                                $salida_por_estado = 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '|<br>';
                            }
                        }
                        /*                         * ***** Inicio tabla VENTA ****** */

                        /*                         * ***** Inicio tabla RESERVA ****** */
                        $sql = "select res_id,res_estado,res_lot_id from reserva_terreno where res_lot_id=$lote and res_estado in ('Pendiente','Habilitado','Venta') order by res_id desc";
                        $query->consulta($sql);
                        $num = $query->num_registros();

                        if ($num > 0) {
                            for ($i = 0; $i < $num; $i++) {
                                list($res_id, $res_estado, $res_lot_id) = $query->valores_fila();

                                $salida = $salida . 'Reserva:' . $res_id . ' - Estado:' . $res_estado . '|<br>';
                            }
                        }
                        /*                         * ***** Fin tabla RESERVA ****** */

                        /*                         * ***** Inicio tabla BLOQUEO ****** */
                        $sql = "select bloq_id,bloq_estado,bloq_lot_id from bloquear_terreno where bloq_lot_id=$lote and bloq_estado in ('Habilitado') order by bloq_id desc";
                        $query->consulta($sql);
                        $num = $query->num_registros();

                        if ($num > 1) {
                            for ($i = 0; $i < $num; $i++) {
                                list($bloq_id, $bloq_estado, $bloq_lot_id) = $query->valores_fila();

                                $salida = $salida . 'Bloqueo Nro:' . $bloq_id . ' - Estado:' . $bloq_estado . '|<br>';
                            }
                        }
                        /*                         * ***** Fin tabla BLOQUEO ****** */

                        if ($salida == '') {
                            $salida = 'Todo en Orden';
                        } else {
                            $salida = $salida_por_estado . $salida;
                            $salida = '<b>' . $salida . '</b>';
                        }
                        return $salida;
                    }
                }
            }
        }
    }