<?php

require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';





//periodos(12,2012);
//periodos(13,2013);
//periodos(14,2014);
//periodos(15,2015);


//importar_tipo_cambio();


function periodos($ges_id,$anio,$ini=1,$fin=12) {
    for($i=$ini;$i<=$fin;$i++){
        $data=array(
            'ini'=>1,
            'fin'=>  dia_max($anio, $i),
            'mes'=>  $i,
            'anio'=>  $anio,
            'ges_id'=>  $ges_id,
        );
        insertar_periodo((object)$data);
    }
}


function insertar_periodo($params) {
    
    $conec = new ADO();
    $fecha_i = "$params->anio-$params->mes-$params->ini";
    $fecha_f = "$params->anio-$params->mes-$params->fin";
    $mes=  strtoupper(FUNCIONES::str_mes($params->mes));
    $sql = "insert into con_periodo(pdo_descripcion,pdo_fecha_inicio,pdo_fecha_fin,pdo_estado,pdo_ges_id)
		values ('" . $mes . "','" . $fecha_i . "','" . $fecha_f . "','Abierto','$params->ges_id')";

    $conec->ejecutar($sql, false);

    $llave = mysql_insert_id();

    
    

    $sql = "SELECT tco_id FROM con_tipo_comprobante";

    $conec->ejecutar($sql);

    $num = $conec->get_num_registros();

    for ($i = 0; $i < $num; $i++) {
        $objeto = $conec->get_objeto();
        $sql="insert into con_contador_comprobante(coc_nro,coc_pdo_id,coc_tco_id) value ('0','$llave','$objeto->tco_id')";
        FUNCIONES::bd_query($sql);
        $conec->siguiente();
    }
    echo 'insertado todos los periodos <br>';
}

function dia_max($year, $mes){
    if($mes==1 || $mes==3 || $mes==5 || $mes==7 || $mes==8 || $mes==10 || $mes==12){
        return 31;        
    }elseif ($mes==4 || $mes==6 || $mes==9 || $mes==11) {
        return 30;
    }elseif ($mes==2) {
        if($year%4==0){
            return 29;
        }else{
            return 28;
        }
    }
}


function importar_tipo_cambio() {
    $tcambios=  FUNCIONES::lista_bd_sql("select * from tmp_tipo_cambio");
    foreach ($tcambios as $cambio) {
        $fecha=  FUNCIONES::get_fecha_mysql($cambio->fecha);
        $_cambio=  FUNCIONES::objeto_bd_sql("select * from con_tipo_cambio where tca_fecha='$fecha' and tca_mon_id='$cambio->moneda'");
        if($_cambio){
            $sql_up="update con_tipo_cambio set tca_valor='$cambio->oficial', tca_valor_compra='$cambio->compra', tca_valor_venta='$cambio->venta' where tca_fecha='$fecha' and tca_mon_id='$cambio->moneda'";
            echo "$sql_up;<br>";
        }else{
            $sql_insert="insert into con_tipo_cambio (tca_fecha,tca_mon_id,tca_valor,tca_valor_compra,tca_valor_venta) values ('$fecha','$cambio->moneda','$cambio->oficial','$cambio->compra','$cambio->venta')";
            echo "$sql_insert;<br>";
        }
    }
//    echo "<pre>";
//    print_r($tcambios);
//    echo "</pre>";
}
