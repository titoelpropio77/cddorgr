<?php

ini_set('display_errors', 'On');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once 'clases/registrar_comprobantes.class.php';
require_once 'excel/reader.php';
require_once('clases/log.class.php');
require_once('clases/mlm.class.php');

function fecha_mysql($fecha) {
    $date = DateTime::createFromFormat("Ymd", $fecha);
    return $date->format("Y-m-d");
}

function importar_temporal() {
    $data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('CP1251');
    $data->read("fuentes_excel/AFILIADOS.xls");
    $colum = 0;
    $filasTotal = $data->sheets[$colum]['numRows'];
    echo $filasTotal;
    $conec = new ADO();

//$filasTotal = 10;

    for ($i = 2; $i <= $filasTotal; $i++) {
        $tb_afi = new stdClass();
        $tb_afi->int_vendedor = trim($data->sheets[$colum]['cells'][$i][1]);
        $tb_afi->ven_id = trim($data->sheets[$colum]['cells'][$i][2]);
        $tb_afi->int_cliente = trim($data->sheets[$colum]['cells'][$i][3]);
        $tb_afi->int_vendedor_padre = trim($data->sheets[$colum]['cells'][$i][4]);
//        $tb_afi->fecha_registro = fecha_mysql(trim($data->sheets[$colum]['cells'][$i][5]));
        $tb_afi->fecha_registro = FUNCIONES::get_fecha_mysql(trim($data->sheets[$colum]['cells'][$i][5]));
        $tb_afi->rango_alcanzado = (trim($data->sheets[$colum]['cells'][$i][6]) * 1) + 1;

        $sql_ins = "insert into temp_afiliado(
        int_vendedor,ven_id,int_cliente,
        int_vendedor_padre,fecha_registro,rango_alcanzado
        ) values (
            '$tb_afi->int_vendedor','$tb_afi->ven_id','$tb_afi->int_cliente',
            '$tb_afi->int_vendedor_padre','$tb_afi->fecha_registro','$tb_afi->rango_alcanzado'
        )";
//        FUNCIONES::bd_query($sql_ins);
        $conec->ejecutar($sql_ins, FALSE, FALSE);
    }
}

function cargar_raices() {
    $sql = "select distinct(int_vendedor) from temp_afiliado 
            where int_vendedor <= 10 
            and int_vendedor not in(
                select int_cliente from temp_afiliado
            )";
    $vdos = FUNCIONES::lista_bd_sql($sql);
    $conec = new ADO();
    $vgru_id = FUNCIONES::atributo_bd_sql("select vgru_id as campo from vendedor_grupo where vgru_nombre='AFILIADOS'") * 1;
    $sql_vdo_pat = "insert into vendedor(vdo_int_id,vdo_cod_legado,vdo_vgru_id,vdo_estado,vdo_nivel,vdo_vendedor_id)
        values(";
    foreach ($vdos as $vdo) {
        $codigo = "NET-" . $vdo->int_vendedor;
        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_codigo='$codigo'");

        if ($interno) {

            if ($vdo->int_vendedor == 1) {
                $vdo_padre_id = 0;
            } else {
                $vdo_padre_id = 1;
            }

            $sql_vdo = $sql_vdo_pat . "'$interno->int_id','$vdo->int_vendedor','$vgru_id','Habilitado','0','$vdo_padre_id')";
            echo "<p>$sql_vdo</p>";
        } else {
            $sql_vdo = $sql_vdo_pat . "'0','$vdo->int_vendedor','$vgru_id','Habilitado','0','0')";
            echo "<p>No existe la persona $codigo.</p>";
        }

//        FUNCIONES::bd_query($sql_vdo);
        $conec->ejecutar($sql_vdo, FALSE, FALSE);
    }
}

function cargar_afiliados() {
    $sql = "select * from temp_afiliado order by int_cliente asc";
    $afils = FUNCIONES::lista_bd_sql($sql);
    $sql_vdo_pat = "insert into vendedor(vdo_int_id,vdo_cod_legado,vdo_vgru_id,vdo_estado,vdo_nivel,vdo_vendedor_id)
        values(";

    $vgru_id = FUNCIONES::atributo_bd_sql("select vgru_id as campo from vendedor_grupo where vgru_nombre='AFILIADOS'") * 1;

    $conec = new ADO();
    foreach ($afils as $afi) {

        $codigo = "NET-" . $afi->int_cliente;
        $vdo_afil = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_cod_legado='$afi->int_cliente'");

        if ($vdo_afil) {
            echo "<p>Ya existe el afiliado</p>";
            continue;
        }

        $interno = FUNCIONES::objeto_bd_sql("select * from interno where int_codigo='$codigo'");

        $nivel = 0;
        $vdo_vendedor_id = 0;
        if ($interno) {
            $sql_vdo = $sql_vdo_pat . "'$interno->int_id','$afi->int_cliente','$vgru_id','Habilitado','$nivel','$vdo_vendedor_id')";
            echo "<p>$sql_vdo</p>";
        } else {
            $sql_vdo = $sql_vdo_pat . "'0','$afi->int_cliente','$vgru_id','Habilitado','$nivel','$vdo_vendedor_id')";
            echo "<p>No existe la persona $codigo.</p>";
        }

//        FUNCIONES::bd_query($sql_vdo);
        $conec->ejecutar($sql_vdo, FALSE, FALSE);
    }
}

function emparentar_afiliados() {
    $sql = "select * from temp_afiliado order by int_cliente asc";
    $afils = FUNCIONES::lista_bd_sql($sql);
    $conec = new ADO();
    foreach ($afils as $afi) {
        $vdo_padre = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_cod_legado='$afi->int_vendedor'");
        $codigo_padre = "NET-" . $afi->int_vendedor;
        $codigo = "NET-" . $afi->int_cliente;

        if ($vdo_padre) {

            $nivel = $vdo_padre->vdo_nivel + 1;
            $vdo_vendedor_id = $vdo_padre->vdo_id;

            $sql_upd = "update vendedor set vdo_nivel='$nivel',
                vdo_vendedor_id='$vdo_vendedor_id'
                    where vdo_cod_legado='$afi->int_cliente'";

            FUNCIONES::bd_query($sql_upd);
            $conec->ejecutar($sql_upd, FALSE, FALSE);
        } else {
            $nivel = 0;
            $vdo_vendedor_id = 0;
            echo "<p>No existe el padre ($codigo_padre) de afiliado $codigo.</p>";
        }
    }
}

function importar_rangos() {
    $data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('CP1251');
    $data->read("fuentes_excel/RANGOS.xls");
    $colum = 0;
    $filasTotal = $data->sheets[$colum]['numRows'];
    echo $filasTotal;
    $conec = new ADO();

//$filasTotal = 10;

    for ($i = 2; $i <= $filasTotal; $i++) {
        $tb_afi = new stdClass();
        $tb_afi->cod_cliente = trim($data->sheets[$colum]['cells'][$i][1]);
        $tb_afi->rango_obtenido = trim($data->sheets[$colum]['cells'][$i][2]);

        $sql_ins = "insert into temp_rangos(
        cod_cliente,rango_obtenido
        ) values (
            '$tb_afi->cod_cliente','$tb_afi->rango_obtenido'
        )";
//        FUNCIONES::bd_query($sql_ins);
        $conec->ejecutar($sql_ins, FALSE, FALSE);
    }
}

function establecer_rangos() {
    $sql = "select * from temp_rangos";
    $rangos = FUNCIONES::lista_bd_sql($sql);
    $conec = new ADO();
    foreach ($rangos as $ran) {
//        $rango = $ran->rango_obtenido + 1;
        $rango = $ran->rango_obtenido;
        $sql_upd = "update vendedor set vdo_rango_alcanzado=$rango 
            where vdo_cod_legado=$ran->cod_cliente";
//        FUNCIONES::bd_query($sql_upd);
        $conec->ejecutar($sql_upd, FALSE, FALSE);
    }
}

function establecer_vendedor() {
    $ventas = FUNCIONES::lista_bd_sql("select * from venta where ven_multinivel='si'");
    $conec = new ADO();
    foreach ($ventas as $ven) {
        $campo = explode('|', $ven->ven_promotor);
        $campo = explode('-', $campo[0]);

        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_cod_legado='{$campo[1]}'");

        if ($vendedor) {
            _PRINT::pre($vendedor);
            $sql_upd = "update venta set ven_vdo_id=$vendedor->vdo_id where ven_id=$ven->ven_id";
//            FUNCIONES::bd_query($sql_upd);
            $conec->ejecutar($sql_upd, FALSE, FALSE);
        } else {
            FUNCIONES::eco("NO EXISTE EL PATROCINADOR $campo[1].");
        }
    }
}

function establecer_vendedor_reserva() {
    $ventas = FUNCIONES::lista_bd_sql("select * from reserva_terreno where res_multinivel='si'
        and res_estado='Habilitado'");
    $conec = new ADO();
    foreach ($ventas as $ven) {
        $campo = explode('|', $ven->res_promotor);
        $campo = explode('-', $campo[0]);

        $vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_cod_legado='{$campo[1]}'");

        if ($vendedor) {
            _PRINT::pre($vendedor);
            $sql_upd = "update reserva_terreno set res_vdo_id=$vendedor->vdo_id where res_id=$ven->res_id";
//            FUNCIONES::bd_query($sql_upd);
            $conec->ejecutar($sql_upd, FALSE, FALSE);
        } else {
            FUNCIONES::eco("NO EXISTE EL PATROCINADOR $campo[1].");
        }
    }
}

function establecer_venta_inicial() {
    $afiliados = FUNCIONES::lista_bd_sql("select * from vendedor 
        inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
        where vgru_nombre='AFILIADOS'
        and vdo_cod_legado > 0");
    $conec = new ADO();
    foreach ($afiliados as $afil) {
        $venta_afil = FUNCIONES::objeto_bd_sql("select * from venta 
            where ven_numero_cliente='NET-$afil->vdo_cod_legado'");
        if ($venta_afil) {
            $sql_upd = "update vendedor set vdo_venta_inicial=$venta_afil->ven_id 
                where vdo_id=$afil->vdo_id";
//            FUNCIONES::bd_query($sql_upd);
            $conec->ejecutar($sql_upd, FALSE, FALSE);
        } else {
            
        }
    }
}

function crear_usuarios() {
    $afiliados = FUNCIONES::lista_bd_sql("select * from vendedor where vdo_vgru_id=14");

    foreach ($afiliados as $afil) {
        MLM::add_usuario_vendedor($afil->vdo_int_id, $afil->vdo_id, $afil->vdo_venta_inicial);
    }
}

LOG::set_archivo_log('importacion_mlm.log');
LOG::add_log('PRIMERA ENTRADA...', 'INFO');

//importar_temporal();
//importar_rangos();
//cargar_raices();
//cargar_afiliados();
//emparentar_afiliados();
//establecer_rangos();
//establecer_vendedor();
//establecer_venta_inicial();

establecer_vendedor_reserva();
//crear_usuarios();
?>