<?php

require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';

//actualizar_zonas_lujan();
//actualizar_zonas_bisito();
//actualizar_zonas_okina();

//actualizar_zonas_renaceri();
//actualizar_zonas_renacerii();

function actualizar_zonas_renacerii() {
    $conec=new ADO();
    $urb_id=10;
    $sql_sel="select * from tmp_lotes_renacerii";
    $lista=  FUNCIONES::lista_bd_sql($sql_sel);
    foreach ($lista as $tlote) {
        $lote=  FUNCIONES::objeto_bd_sql("select * from lote inner join manzano on (lot_man_id=man_id) where man_urb_id='$urb_id' and man_nro='$tlote->mz' and lot_nro='$tlote->lote'");
        if($lote){
            $zona_new=  FUNCIONES::objeto_bd_sql("select * from zona where zon_urb_id='$urb_id' and zon_nombre='$tlote->zona_new'");
            if($zona_new){
                $sql_up="update lote set lot_zon_id='$zona_new->zon_id' where lot_id='$lote->lot_id'";
//                $conec->ejecutar($sql_up,false,false);
                echo "$sql_up;<br>";
            }else{
                echo "-- NO EXISTE ZONA;$urb_id;$tlote->zona_new<br>";
            }
        }else{
            echo "-- NO EXISTE LOTE;$tlote->uv;$tlote->mz;$tlote->lote<br>";
        }
    }
}
function actualizar_zonas_renaceri() {
    $conec=new ADO();
    $urb_id=8;
    $sql_sel="select * from tmp_lotes_renaceri";
    $lista=  FUNCIONES::lista_bd_sql($sql_sel);
    foreach ($lista as $tlote) {
        $lote=  FUNCIONES::objeto_bd_sql("select * from lote inner join manzano on (lot_man_id=man_id) where man_urb_id='$urb_id' and man_nro='$tlote->mz' and lot_nro='$tlote->lote'");
        if($lote){
            $zona_new=  FUNCIONES::objeto_bd_sql("select * from zona where zon_urb_id='$urb_id' and zon_nombre='$tlote->zona_new'");
            if($zona_new){
                $sql_up="update lote set lot_zon_id='$zona_new->zon_id' where lot_id='$lote->lot_id'";
//                $conec->ejecutar($sql_up,false,false);
                echo "$sql_up;<br>";
            }else{
                echo "-- NO EXISTE ZONA;$urb_id;$tlote->zona_new<br>";
            }
        }else{
            echo "-- NO EXISTE LOTE;$tlote->uv;$tlote->mz;$tlote->lote<br>";
        }
    }
}
function actualizar_zonas_okina() {
    $conec=new ADO();
    $urb_id=13;
    $sql_sel="select * from tmp_lotes_okina";
    $lista=  FUNCIONES::lista_bd_sql($sql_sel);
    foreach ($lista as $tlote) {
        $lote=  FUNCIONES::objeto_bd_sql("select * from lote inner join manzano on (lot_man_id=man_id) where man_urb_id='$urb_id' and man_nro='$tlote->mz' and lot_nro='$tlote->lote'");
        if($lote){
            $zona_new=  FUNCIONES::objeto_bd_sql("select * from zona where zon_urb_id='$urb_id' and zon_nombre='$tlote->zona_new'");
            if($zona_new){
                $sql_up="update lote set lot_zon_id='$zona_new->zon_id' where lot_id='$lote->lot_id'";
//                $conec->ejecutar($sql_up,false,false);
                echo "$sql_up;<br>";
            }else{
                echo "-- NO EXISTE ZONA;$urb_id;$tlote->zona_new<br>";
            }
        }else{
            echo "-- NO EXISTE LOTE;$tlote->uv;$tlote->mz;$tlote->lote<br>";
        }
    }
}
function actualizar_zonas_bisito() {
    $conec=new ADO();
    $urb_id=3;
    $sql_sel="select * from tmp_lotes_bisito";
    $lista=  FUNCIONES::lista_bd_sql($sql_sel);
    foreach ($lista as $tlote) {
        $lote=  FUNCIONES::objeto_bd_sql("select * from lote inner join manzano on (lot_man_id=man_id) where man_urb_id='$urb_id' and man_nro='$tlote->mz' and lot_nro='$tlote->lote'");
        if($lote){
            $zona_new=  FUNCIONES::objeto_bd_sql("select * from zona where zon_urb_id='$urb_id' and zon_nombre='$tlote->zona_new'");
            if($zona_new){
                $sql_up="update lote set lot_zon_id='$zona_new->zon_id' where lot_id='$lote->lot_id'";
//                $conec->ejecutar($sql_up,false,false);
                echo "$sql_up;<br>";
            }else{
                echo "-- NO EXISTE ZONA;$urb_id;$tlote->zona_new<br>";
            }
        }else{
            echo "-- NO EXISTE LOTE;$tlote->uv;$tlote->mz;$tlote->lote<br>";
        }
    }
}
function actualizar_zonas_lujan() {
    $conec=new ADO();
    $urb_id=2;
    $sql_sel="select * from tmp_lotes_lujan ";
    $lista=  FUNCIONES::lista_bd_sql($sql_sel);
    foreach ($lista as $tlote) {
        $lote=  FUNCIONES::objeto_bd_sql("select * from lote inner join manzano on (lot_man_id=man_id) where man_urb_id='$urb_id' and man_nro='$tlote->mz' and lot_nro='$tlote->lote'");
        if($lote){
            $zona_new=  FUNCIONES::objeto_bd_sql("select * from zona where zon_urb_id='$urb_id' and zon_nombre='$tlote->zona_new'");
            if($zona_new){
                $sql_up="update lote set lot_zon_id='$zona_new->zon_id' where lot_id='$lote->lot_id'";
                $conec->ejecutar($sql_up,false,false);
//                echo "$sql_up;<br>";
            }else{
                echo "-- NO EXISTE ZONA;$urb_id;$tlote->zona_new<br>";
            }
        }else{
            echo "-- NO EXISTE LOTE;$tlote->uv;$tlote->mz;$tlote->lote<br>";
        }
    }
}