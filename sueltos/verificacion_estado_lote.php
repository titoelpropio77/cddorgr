<link href="../css/estilos.css" rel="stylesheet" type="text/css">
<?php
require_once('mysql.php');
require("../clases/mytime_int.php");

$f_urb = (isset($_GET[urb])) ? " and urb_id=$_GET[urb]" : "";

$query = new QUERY();
if (!isset($_GET[estado]) || $_GET[estado] == 'disponible') {
    ?>
    <center>
        <center><h2>LOTES DISPONIBLES</h2></center>
        <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Uv</th>
                    <th>Manzano</th>
                    <th>Lote</th>
                    <th>Descripcion</th>
                    <th class="tOpciones" >Estados</th>
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
					where lot_estado='Disponible'
					$f_urb
					order by uv_nombre,man_nro,lot_nro asc";

                $query->consulta($sql);

                $num = $query->num_registros();

                for ($i = 0; $i < $num; $i++) {
                    list($urb_nombre, $man_nro, $lot_nro, $zon_nombre, $uv_nombre, $lot_id) = $query->valores_fila();

                    echo '<tr>';


                    // echo "<td>";
                    // echo ($i+1);
                    // echo "</td>";
                    echo "<td>";
                    echo $lot_id;
                    echo "</td>";

                    echo "<td>";
                    echo $uv_nombre;
                    echo "</td>";

                    echo "<td>";
                    echo $man_nro;
                    echo "</td>";

                    echo "<td>";
                    echo $lot_nro;
                    echo "</td>";

                    echo "<td>";
                    echo datos_lote($lot_id);
                    echo "</td>";

                    $resultado = verificar_datos_lote($lot_id, 'Disponible');
                    $color = ($resultado == 'Todo en Orden') ? 'green' : 'purple';
                    $resultado .= ($resultado == 'Todo en Orden') ? '' : ' (CONFLICTO)';

                    echo "<td style='color:white;background-color:$color'>";
                    echo $resultado;
                    echo "</td>";

                    echo "</tr>";
                }
                ?>

            </tbody>
        </table>
    </center>

    <br />
    <br />
    <?php
}

if (!isset($_GET[estado]) || $_GET[estado] == 'reservado') {
    ?>
    <center>
        <center><h2>LOTES RESERVADOS</h2></center>
        <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Uv</th>
                    <th>Manzano</th>
                    <th>Lote</th>
                    <th>Descripcion</th>
                    <th class="tOpciones" >Estados</th>
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
					where lot_estado='Reservado'
					$f_urb
					order by uv_nombre,man_nro,lot_nro asc";

                $query->consulta($sql);

                $num = $query->num_registros();

                for ($i = 0; $i < $num; $i++) {
                    list($urb_nombre, $man_nro, $lot_nro, $zon_nombre, $uv_nombre, $lot_id) = $query->valores_fila();

                    echo '<tr>';


                    // echo "<td>";
                    // echo ($i+1);
                    // echo "</td>";

                    echo "<td>";
                    echo $lot_id;
                    echo "</td>";

                    echo "<td>";
                    echo $uv_nombre;
                    echo "</td>";

                    echo "<td>";
                    echo $man_nro;
                    echo "</td>";

                    echo "<td>";
                    echo $lot_nro;
                    echo "</td>";

                    echo "<td>";
                    echo datos_lote($lot_id);
                    echo "</td>";

                    $resultado = verificar_datos_lote($lot_id, 'Reservado');
                    $color = ($resultado == 'Todo en Orden') ? 'blue' : 'purple';
                    $resultado .= ($resultado == 'Todo en Orden') ? '' : ' (CONFLICTO)';

                    echo "<td style='color:white;background-color:$color'>";
                    echo $resultado;
                    echo "</td>";

                    echo "</tr>";
                }
                ?>

            </tbody>
        </table>
    </center>

    <br />
    <br />
    <?php
}

if (!isset($_GET[estado]) || $_GET[estado] == 'bloqueado') {
    ?>
    <center>
        <center><h2>LOTES BLOQUEADOS</h2></center>
        <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Uv</th>
                    <th>Manzano</th>
                    <th>Lote</th>
                    <th>Descripcion</th>
                    <th class="tOpciones" >Estados</th>
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
					where lot_estado='Bloqueado'
					$f_urb
					order by uv_nombre,man_nro,lot_nro asc";

                $query->consulta($sql);

                $num = $query->num_registros();

                for ($i = 0; $i < $num; $i++) {
                    list($urb_nombre, $man_nro, $lot_nro, $zon_nombre, $uv_nombre, $lot_id) = $query->valores_fila();

                    echo '<tr>';


                    // echo "<td>";
                    // echo ($i+1);
                    // echo "</td>";

                    echo "<td>";
                    echo $lot_id;
                    echo "</td>";

                    echo "<td>";
                    echo $uv_nombre;
                    echo "</td>";

                    echo "<td>";
                    echo $man_nro;
                    echo "</td>";

                    echo "<td>";
                    echo $lot_nro;
                    echo "</td>";

                    echo "<td>";
                    echo datos_lote($lot_id);
                    echo "</td>";

                    $resultado = verificar_datos_lote($lot_id, 'Bloqueado');
                    $color = ($resultado == 'Todo en Orden') ? 'gray' : 'purple';
                    $resultado .= ($resultado == 'Todo en Orden') ? '' : ' (CONFLICTO)';

                    echo "<td style='color:white;background-color:$color'>";
                    echo $resultado;
                    echo "</td>";

                    echo "</tr>";
                }
                ?>

            </tbody>
        </table>
    </center>

    <br />
    <br />
    <?php
}

if (!isset($_GET[estado]) || $_GET[estado] == 'vendido') {
    ?>
    <center>
        <center><h2>LOTES VENDIDOS</h2></center>
        <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Uv</th>
                    <th>Manzano</th>
                    <th>Lote</th>
                    <th>Descripcion</th>
                    <th class="tOpciones" >Estados</th>
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
					where lot_estado='Vendido'
					$f_urb
					order by uv_nombre,man_nro,lot_nro asc";

                $query->consulta($sql);

                $num = $query->num_registros();

                for ($i = 0; $i < $num; $i++) {
                    list($urb_nombre, $man_nro, $lot_nro, $zon_nombre, $uv_nombre, $lot_id) = $query->valores_fila();

                    echo '<tr>';


                    // echo "<td>";
                    // echo ($i+1);
                    // echo "</td>";

                    echo "<td>";
                    echo $lot_id;
                    echo "</td>";

                    echo "<td>";
                    echo $uv_nombre;
                    echo "</td>";

                    echo "<td>";
                    echo $man_nro;
                    echo "</td>";

                    echo "<td>";
                    echo $lot_nro;
                    echo "</td>";

                    echo "<td>";
                    echo datos_lote($lot_id);
                    echo "</td>";

                    $resultado = verificar_datos_lote($lot_id, 'Vendido');
                    $color = ($resultado == 'Todo en Orden') ? 'red' : 'purple';
                    $resultado .= ($resultado == 'Todo en Orden') ? '' : ' (CONFLICTO)';

                    echo "<td style='color:white; background-color:$color'>";
                    echo $resultado;
                    echo "</td>";

                    echo "</tr>";
                }
                ?>

            </tbody>
        </table>
    </center>

    <?php
}

function nombre_persona($usuario) {
    $query = new QUERY();

    $sql = "select per_nombre,per_apellido from ad_usuario inner join gr_persona on (usu_id='$usuario' and usu_per_id=per_id)";

    $query->consulta($sql);

    list($per_nombre, $per_apellido) = $query->valores_fila();

    return $per_nombre . ' ' . $per_apellido;
}

function datos_lote($lote) {
    $query = new QUERY();

    $sql = "select urb_nombre,man_nro,lot_nro,zon_nombre,uv_nombre 
	from 
	lote
	inner join zona on (lot_id='" . $lote . "' and lot_zon_id=zon_id)
	inner join uv on (lot_uv_id=uv_id)	
	inner join manzano on (lot_man_id=man_id)
	inner join urbanizacion on(man_urb_id=urb_id)";

    $query->consulta($sql);

    list($urb_nombre, $man_nro, $lot_nro, $zon_nombre, $uv_nombre) = $query->valores_fila();

    $des = 'Urb:' . $urb_nombre . ' - Mza:' . $man_nro . ' - Lote:' . $lot_nro . ' - Zona:' . $zon_nombre . ' - UV:' . $uv_nombre;

    return $des;
}

function verificar_datos_lote($lote, $estado_actual_lote) {
    $query = new QUERY();

    if ($estado_actual_lote == 'Disponible') {
        $error = false;

        $salida = '';

        /*         * ***** Inicio tabla VENTA ****** */
        $sql = "select ven_id,ven_estado,ven_lot_id from venta 
            where ven_lot_id=$lote 
                and ven_estado in ('Pendiente','Pagado') 
                order by ven_id desc";



        $query->consulta($sql);

        $num = $query->num_registros();

        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                list($ven_id, $ven_estado, $ven_lot_id) = $query->valores_fila();

                $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '<br>';
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

                    $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '<br>';
                }
            }
        }
        /*         * ***** Inicio tabla VENTA ****** */

        /*         * ***** Inicio tabla RESERVA ****** */
        $sql = "select res_id,res_estado,res_lot_id from reserva_terreno where res_lot_id=$lote and res_estado in ('Pendiente','Habilitado') order by res_id desc";



        $query->consulta($sql);

        $num = $query->num_registros();

        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                list($res_id, $res_estado, $res_lot_id) = $query->valores_fila();

                $salida = $salida . 'Reserva:' . $res_id . ' - Estado:' . $res_estado . '<br>';
            }
        }
        /*         * ***** Inicio tabla RESERVA ****** */

        /*         * ***** Inicio tabla BLOQUE ****** */
        $sql = "select bloq_id,bloq_estado,bloq_lot_id from bloquear_terreno where bloq_lot_id=$lote and bloq_estado in ('Habilitado') order by bloq_id desc";



        $query->consulta($sql);

        $num = $query->num_registros();

        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                list($bloq_id, $bloq_estado, $bloq_lot_id) = $query->valores_fila();

                $salida = $salida . 'Bloqueo Nro:' . $bloq_id . ' - Estado:' . $bloq_estado . '<br>';
            }
        }
        /*         * ***** Inicio tabla RESERVA ****** */

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

            /*             * ***** Inicio tabla VENTA ****** */
            $sql = "select ven_id,ven_estado,ven_lot_id from venta where ven_lot_id=$lote and ven_estado in ('Pendiente','Pagado') order by ven_id desc";



            $query->consulta($sql);

            $num = $query->num_registros();

            if ($num > 0) {
                for ($i = 0; $i < $num; $i++) {
                    list($ven_id, $ven_estado, $ven_lot_id) = $query->valores_fila();

                    $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '<br>';
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

                        $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '<br>';
                    }
                }
            }
            /*             * ***** Inicio tabla VENTA ****** */

            /*             * ***** Inicio tabla RESERVA ****** */

            $sql = "select res_id,res_estado,res_lot_id from reserva_terreno where res_lot_id=$lote and res_estado in ('Pendiente','Habilitado','Venta') order by res_id desc";



            $query->consulta($sql);

            $num = $query->num_registros();

            if ($num == 0) {
                $salida = 'No hay ninguna reserva activa con este lote<br>';
            } else {
                if ($num > 1) {
                    for ($i = 0; $i < $num; $i++) {
                        list($res_id, $res_estado, $res_lot_id) = $query->valores_fila();

                        $salida = $salida . 'Reserva:' . $res_id . ' - Estado:' . $res_estado . '<br>';
                    }
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

                    $salida = $salida . 'Bloqueo Nro:' . $bloq_id . ' - Estado:' . $bloq_estado . '<br>';
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
            if ($estado_actual_lote == 'Bloqueado') {
                $error = false;

                $salida = '';

                /*                 * ***** Inicio tabla VENTA ****** */
                $sql = "select ven_id,ven_estado,ven_lot_id from venta where ven_lot_id=$lote and ven_estado in ('Pendiente','Pagado') order by ven_id desc";



                $query->consulta($sql);

                $num = $query->num_registros();

                if ($num > 0) {
                    for ($i = 0; $i < $num; $i++) {
                        list($ven_id, $ven_estado, $ven_lot_id) = $query->valores_fila();

                        $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '<br>';
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

                            $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '<br>';
                        }
                    }
                }
                /*                 * ***** Inicio tabla VENTA ****** */

                /*                 * ***** Inicio tabla RESERVA ****** */
                $sql = "select res_id,res_estado,res_lot_id from reserva_terreno where res_lot_id=$lote and res_estado in ('Pendiente','Habilitado') order by res_id desc";



                $query->consulta($sql);

                $num = $query->num_registros();

                if ($num > 0) {
                    for ($i = 0; $i < $num; $i++) {
                        list($res_id, $res_estado, $res_lot_id) = $query->valores_fila();

                        $salida = $salida . 'Reserva:' . $res_id . ' - Estado:' . $res_estado . '<br>';
                    }
                }
                /*                 * ***** Fin tabla RESERVA ****** */

                /*                 * ***** Inicio tabla BLOQUEO ****** */
                $sql = "select bloq_id,bloq_estado,bloq_lot_id from bloquear_terreno where bloq_lot_id=$lote and bloq_estado in ('Habilitado') order by bloq_id desc";



                $query->consulta($sql);

                $num = $query->num_registros();

                if ($num == 0) {
                    $salida = $salida . 'El Lote no se encuentre en ningun Bloqueo<br>';
                } else {
                    if ($num > 1) {
                        for ($i = 0; $i < $num; $i++) {
                            list($bloq_id, $bloq_estado, $bloq_lot_id) = $query->valores_fila();

                            $salida = $salida . 'Bloqueo Nro:' . $bloq_id . ' - Estado:' . $bloq_estado . '<br>';
                        }
                    }
                }
                /*                 * ***** Fin tabla BLOQUEO ****** */

                if ($salida == '')
                    $salida = 'Todo en Orden';
                else
                    $salida = '<b>' . $salida . '</b>';

                return $salida;
            }
            else {
                if ($estado_actual_lote == 'Vendido') {
                    $error = false;

                    $salida = '';

                    /*                     * ***** Inicio tabla VENTA ****** */
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

                                    $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '<br>';
                                }
                            }
                        } else {
                            $salida = 'Lote no se encuentra en ninguna venta<br>';
                        }
                    } else if ($num > 1) {

                        for ($i = 0; $i < $num; $i++) {
                            list($ven_id, $ven_estado, $ven_lot_id) = $query->valores_fila();

                            $salida = $salida . 'Venta:' . $ven_id . ' - Estado:' . $ven_estado . '<br>';
                        }
                    } else {
                        
                    }
                    /*                     * ***** Inicio tabla VENTA ****** */

                    /*                     * ***** Inicio tabla RESERVA ****** */
                    $sql = "select res_id,res_estado,res_lot_id from reserva_terreno where res_lot_id=$lote and res_estado in ('Pendiente','Habilitado') order by res_id desc";



                    $query->consulta($sql);

                    $num = $query->num_registros();

                    if ($num > 0) {
                        for ($i = 0; $i < $num; $i++) {
                            list($res_id, $res_estado, $res_lot_id) = $query->valores_fila();

                            $salida = $salida . 'Reserva:' . $res_id . ' - Estado:' . $res_estado . '<br>';
                        }
                    }
                    /*                     * ***** Fin tabla RESERVA ****** */

                    /*                     * ***** Inicio tabla BLOQUEO ****** */
                    $sql = "select bloq_id,bloq_estado,bloq_lot_id from bloquear_terreno where bloq_lot_id=$lote and bloq_estado in ('Habilitado') order by bloq_id desc";



                    $query->consulta($sql);

                    $num = $query->num_registros();

                    if ($num > 1) {
                        for ($i = 0; $i < $num; $i++) {
                            list($bloq_id, $bloq_estado, $bloq_lot_id) = $query->valores_fila();

                            $salida = $salida . 'Bloqueo Nro:' . $bloq_id . ' - Estado:' . $bloq_estado . '<br>';
                        }
                    }
                    /*                     * ***** Fin tabla BLOQUEO ****** */

                    if ($salida == '')
                        $salida = 'Todo en Orden';
                    else
                        $salida = '<b>' . $salida . '</b>';

                    return $salida;
                }
            }
        }
    }
}
?>

