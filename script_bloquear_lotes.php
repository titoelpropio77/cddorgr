<?php

require_once('clases/reporte.class.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');

bloquear_lotes();

function bloquear_lotes() {
    $fp = fopen("lotes/bloquear_lotes.txt", "r");
    $conec=new ADO();
    while (!feof($fp)) {
        $linea = trim(fgets($fp));
        $r=  explode(';', $linea);
        $urb_id=$r[0];
        $man_nro=$r[1];
        $lot_nro=$r[2];
        $sql_lot="select * from lote,manzano where man_urb_id=$urb_id and man_nro='$man_nro' and lot_nro='$lot_nro' and lot_man_id=man_id ";
//        $sql_lot="select * from lote ";
        
        $lote= FUNCIONES::objeto_bd_sql($sql_lot);
        $fecha_cre=date('Y-m-d');
        $hora_cre=date('H:i:s');
        if($lote){
            if($lote->lot_estado=='Disponible'){
                $sql = "insert into bloquear_terreno (
                                bloq_lot_id,bloq_fecha,bloq_hora,bloq_estado,bloq_usu_id,bloq_int_id,bloq_nota,bloq_vdo_id
                            )values (
                                '$lote->lot_id','$fecha_cre','$hora_cre','Habilitado','admin','207','BLOQUEO MASIVO POR ORDEN DE GERENCIA','0'
                            )";
                $conec->ejecutar($sql);
                $sql = "update lote set lot_estado='Bloqueado' where lot_id=$lote->lot_id";

                $conec->ejecutar($sql);
            }else{
                echo "LOTE EN ESTADO $lote->lot_estado;$linea<BR>";
            }
        }else{
            echo "NO EXISTE LOTE $linea<BR>";
        }
        
        
    }
    fclose($fp);
}
