<?php
date_default_timezone_set("America/La_Paz");
require_once('clases/pagina.class.php');
require_once("clases/busqueda.class.php");
require_once("clases/formulario.class.php");
require_once('clases/coneccion.class.php');
require_once('clases/conversiones.class.php');
require_once('clases/usuario.class.php');
require_once('clases/validar.class.php');
require_once('clases/verificar.php');
require_once('clases/funciones.class.php');

class Elemento {

    var $id;
    var $value;

    function __construct($id, $value) {
        $this->id = $id;
        $this->value = $value;
    }

}

if ($_POST['id']) {
    procesar($_POST['id']);
}

if ($_POST['anticipos']) {
    anticipos($_POST['anticipos']);
}

if ($_POST['tipo_cambio']) {
    obtenerTC();
}

function anticipos($res_id) {
    $bus = new BUSQUEDA();
    $tc = $bus->tipo_cambio();
    $sql = "select respag_monto as monto,cmp_mon_id as moneda from reserva_pago 
                inner join comprobante on (cmp_tabla_id=respag_id and cmp_tabla='reserva_pago')
                where respag_res_id=$res_id and respag_estado='Pagado'";
    
    $conec = new ADO();
    $conec->ejecutar($sql);
    $num = $conec->get_num_registros();
    $totalBs = 0;
    $totalSus = 0;
    $result = array();

    if ($num > 0) {
        for ($i = 0; $i < $num; $i++) {
            $obj = $conec->get_objeto();
            if ($obj->moneda == '1') {
                $totalBs += $obj->monto;
                $totalSus += $obj->monto / $tc;
            } else {
                $totalSus += $obj->monto;
                $totalBs += $obj->monto * $tc;
            }
            $conec->siguiente();
        }
        $bs = new Elemento('bs', $totalBs);
        $sus = new Elemento('sus', $totalSus);
        array_push($result, $bs);
        array_push($result, $sus);
        echo json_encode($result);
    }
}

function procesar($id) {

    $sql = "select * from reserva_terreno where res_id='" . $id . "'";
    $conec = new ADO();
    $conec->ejecutar($sql);
    $num = $conec->get_num_registros();
    $result = array();

    if ($num > 0) {
        $obj = $conec->get_objeto();
        $estado = new Elemento("estado", $obj->res_estado);
        array_push($result, $estado);
    }

    echo json_encode($result);
}

function obtenerTC() {
    $bus = new BUSQUEDA();
    $result = array();
    $tc = new Elemento("tc", $bus->tc);
    array_push($result, $tc);
    echo json_encode($result);
}

?>
