<!--<meta http-equiv="Content-Type" content="text/html; charset=utf-8">-->
<?php
//require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';

importar_lotes_libres();


function importar_lotes_libres() {
    $conec = new ADO();
    $tlotes = FUNCIONES::lista_bd_sql("select * from temp_lotes_toborochi");
    $urb=new stdClass();
    $urb->urb_id=15 ;
    $num_reg=0;
    foreach ($tlotes as $tlote) {
        $uv_nombre = $tlote->uv;
        $man_nro = $tlote->mz;
        $lot_nro = $tlote->lote;
        $superficie = $tlote->superficie;
        $zon_nombre = $tlote->zona;
        $zon_precio = $tlote->precio;
        $valor_co = 0;

        $estado = 'Disponible';
//        if($superficie=='0' || $superficie=='1'){
//            $estado = 'Bloqueado';
//        }

        if($superficie!='0'){
            $codigo_lote = "M{$man_nro}L{$lot_nro}";
            $man_id = get_manzano_id($man_nro, $urb->urb_id);
            $zon_id = get_zona_id($zon_nombre, $zon_precio, $urb->urb_id);
            $uv_id = get_uv_id($uv_nombre, $urb->urb_id);
            $sql_ins = "insert into lote (
                        lot_nro,lot_estado,lot_man_id,lot_zon_id,
                        lot_superficie,lot_uv_id,lot_codigo
                    )values(
                        '$lot_nro','$estado','$man_id','$zon_id',
                        '$superficie','$uv_id','$codigo_lote'
                    )";
            $conec->ejecutar($sql_ins, false, true);
            $num_reg++;
        }
        
//        $lot_id = ADO::$insert_id;
//        if($superficie=='0' || $superficie=='1'){
//            $fecha=date('Y-m-d');
//            $hora=date('H:i:s');
//            $sql_ins="insert into bloquear_terreno (
//                            bloq_lot_id,bloq_fecha,bloq_hora,bloq_tipo,bloq_estado,
//                            bloq_usu_id,bloq_int_id,bloq_nota,bloq_vdo_id
//                        )values(
//                            '$lot_id','$fecha','$hora','CARTERA','Habilitado',
//                            'admin','0','','0'
//                        )";
//            $conec->ejecutar($sql_ins, false, false);
//        }
    }
    echo "-- OK " . count($tlotes) . '<br>';
    echo "-- REGISTRADOS $num_reg <BR>";
}

function get_manzano_id($man_nro, $urb_id) {
    $conec = new ADO();
    $manzano = FUNCIONES::objeto_bd_sql("select * from manzano where man_nro='$man_nro' and man_urb_id='$urb_id'");
    if ($manzano) {
        return $manzano->man_id;
    } else {
        $sql_ins = "insert into manzano (man_nro,man_urb_id)values('$man_nro','$urb_id')";
        $conec->ejecutar($sql_ins, false, true);
        $man_id = ADO::$insert_id;
        return $man_id;
    }
}

function get_zona_id($zon_nombre, $zon_precio, $urb_id) {
    $conec = new ADO();
    $zona = FUNCIONES::objeto_bd_sql("select * from zona where zon_nombre='$zon_nombre' and zon_urb_id='$urb_id'");
    if ($zona) {
        return $zona->zon_id;
    } else {
        $sql_ins = "insert into zona (zon_nombre,zon_precio,zon_urb_id)values('$zon_nombre','$zon_precio','$urb_id')";
        $conec->ejecutar($sql_ins, false, true);
        $zon_id = ADO::$insert_id;
        return $zon_id;
    }
}

function get_uv_id($uv_nombre, $urb_id) {
    $conec = new ADO();
    $uv = FUNCIONES::objeto_bd_sql("select * from uv where uv_nombre='$uv_nombre' and uv_urb_id='$urb_id'");
    if ($uv) {
        return $uv->uv_id;
    } else {
        $sql_ins = "insert into uv (uv_nombre,uv_urb_id)values('$uv_nombre','$urb_id')";
        $conec->ejecutar($sql_ins, false, true);
        $uv_id = ADO::$insert_id;
        return $uv_id;
    }
}

function importar_proyectos() {
    $conec = new ADO();
    $conec->ejecutar("truncate urbanizacion", false, false);
    $proyectos = FUNCIONES::lista_bd_sql("select * from tmp_proyecto where activo='1' order by id *1 asc");
    foreach ($proyectos as $proy) {
        $sql_insert = "insert into urbanizacion(
                    urb_codigo,urb_nombre,urb_moneda,urb_interes_anual,urb_tipo,urb_une_id,urb_val_costo,
                    urb_monto_anticipo,urb_val_form,urb_nombre_corto,urb_sistema,urb_mora_dia
                )values(
                    '$proy->id','$proy->proyecto','2','0','interno1','1','1',
                    '0','0','$proy->proyecto','fmulta','1'
                )";
        $conec->ejecutar($sql_insert, false, false);
    }
}
