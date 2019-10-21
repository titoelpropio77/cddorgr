<?php

class CLIENTE {

    var $usuDatos;
    var $encript;

    function CLIENTE() {
        $this->encript = new Encryption();
        $this->usuDatos = explode("|", $this->encript->decode($_GET['token']));
    }

    function sincronizar($jsonDatos) {
        for ($i = 0; $i < count($jsonDatos); $i++) {
            if ($jsonDatos[$i]->int_registro == "Nuevo") {
                $this->agregar($jsonDatos[$i]);
            }
            if ($jsonDatos[$i]->int_registro == "Antiguo") {
                $this->modificar($jsonDatos[$i]);
            }
        }
    }

    function verificar_interno($int_nombre, $int_apellido) {
        $sql = "SELECT int_id FROM interno 
                    WHERE int_nombre='" . trim($int_nombre) . "' 
                        and int_apellido='" . trim($int_apellido) . "' and int_eliminado='No'; ";

        // echo $sql;
        // exit;
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            return true;
        } else {
            return false;
        }
    }

    function verificar_por_cedula($cedula) {
        $sql = "SELECT int_id FROM interno 
                    WHERE int_ci='$cedula' and int_eliminado='No'";
        $internos = FUNCIONES::objetos_bd_sql($sql);

        if ($internos->get_num_registros() > 0) {
            return true;
        } else {
            return false;
        }
        
    }

    function agregar($jsonDatos) {
        $respuesta = new stdClass();
        $conv = new convertir();

        if ($this->verificar_por_cedula(strip_tags(utf8_decode($jsonDatos->int_ci)))) {
            $respuesta->mensaje = "No se pudo registrar por que ya existe un cliente registrado con la Cedula proporcionada.";
            $respuesta->estado = FALSE;
            return $respuesta;
        }

        if ($this->verificar_interno($jsonDatos->int_nombre, $jsonDatos->int_apellido) == false) {

            $fecha_nac = $conv->get_fecha_mysql($jsonDatos->int_fecha_nacimiento);
            $sql = "insert into interno (
                            int_nombre,
                            int_apellido,
                            int_email,
                            int_telefono,
                            int_celular,
                            int_direccion,
                            int_ci,
                            int_estado_civil,
                            int_fecha_ingreso,
                            int_fecha_nacimiento,
                            int_ci_exp,
                            int_app_tempid,
                            int_usu_id
                    ) values ( 
                            '" . strip_tags(utf8_decode($jsonDatos->int_nombre)) . "',
                            '" . strip_tags(utf8_decode($jsonDatos->int_apellido)) . "',
                            '" . strip_tags(utf8_decode($jsonDatos->int_email)) . "',
                            '" . strip_tags(utf8_decode($jsonDatos->int_telefono)) . "',
                            '" . strip_tags(utf8_decode($jsonDatos->int_celular)) . "',
                            '" . strip_tags(utf8_decode($jsonDatos->int_direccion)) . "', 
                            '" . strip_tags(utf8_decode($jsonDatos->int_ci)) . "',
                            '" . strip_tags(utf8_decode($jsonDatos->int_estado_civil)) . "', 
                            '" . date('Y-m-d') . "',
                            '" . $fecha_nac . "',
                            '" . strip_tags(utf8_decode($jsonDatos->int_ci_exp)) . "',
                            '" . $jsonDatos->int_id . "',
                            '" . $this->usuDatos[0] . "' 
                    )";
            //Registrar logs 
            logs("APLICACION", $sql, $this->usuDatos[0]);

            $result = mysql_query($sql);
            $respuesta->id = mysql_insert_id();
            $respuesta->mensaje = "Cliente agregado correctamente";
            $respuesta->estado = TRUE;
        } else {
            $respuesta->mensaje = "No se pudo registrar por que <br>el cliente ya se encuentra registrado";
            $respuesta->estado = FALSE;
        }
        return $respuesta;
    }

    function modificar($jsonDatos) {
        $respuesta = new stdClass();
        $sql = "UPDATE interno SET 
                        int_nombre='" . strip_tags(utf8_decode($jsonDatos->int_nombre)) . "',
                        int_apellido='" . strip_tags(utf8_decode($jsonDatos->int_apellido)) . "',
                        int_email='" . strip_tags(utf8_decode($jsonDatos->int_email)) . "',
                        int_telefono='" . strip_tags(utf8_decode($jsonDatos->int_telefono)) . "',
                        int_celular='" . strip_tags(utf8_decode($jsonDatos->int_celular)) . "',
                        int_direccion='" . strip_tags(utf8_decode($jsonDatos->int_direccion)) . "',
                        int_ci='" . $jsonDatos->int_ci . "',
                        int_estado_civil='" . $jsonDatos->int_estado_civil . "',
                        int_fecha_nacimiento='" . $jsonDatos->int_fecha_nacimiento . "',
                        int_ci_exp='" . strip_tags(utf8_decode($jsonDatos->int_ci_exp)) . "'
                WHERE int_id='" . $jsonDatos->int_id . "'";

        $result = mysql_query($sql);

        //Registrar logs 
        logs("APLICACION", $sql, $this->usuDatos[0]);


        $respuesta->mensaje = "Cliente modificado correctamente";
        $respuesta->estado = TRUE;
        return $respuesta;
    }

    function obtener_clientes($criterio) {

        $usu_id = $this->usuDatos[0];
        $vdo_id = $this->usuDatos[3];

//            $sql ="select int_id as id,CONCAT(int_nombre, ' ', int_apellido) as nombre , int_usu_id as usuario 
//                from interno
//                
//                    where CONCAT(int_nombre, ' ', int_apellido) like '%$criterio%' 
//                        and (int_usu_id='$usu_id') limit 15";

        $sql = "SELECT int_id AS id, CONCAT(int_nombre, ' ', int_apellido) AS nombre, int_usu_id AS usuario
                    FROM interno
                    LEFT JOIN vendedor ON (vdo_int_id=int_id)
                    WHERE CONCAT(int_nombre, ' ', int_apellido) LIKE '%$criterio%' 
                    and int_eliminado='No'
                    AND (int_usu_id='$usu_id' or vdo_vendedor_id='$vdo_id')
                    LIMIT 15";

//            echo "$sql";

        $result = mysql_query($sql);
        $num = mysql_num_rows($result);

        $aResults = array();

        for ($j = 0; $j < $num; $j++) {
            $objeto = mysql_fetch_object($result);

            $aResults[] = array("id" => ($objeto->id), "value" => htmlspecialchars(utf8_encode($objeto->nombre . ' (' . $objeto->usuario . ')')), "info" => htmlspecialchars(utf8_encode($objeto->nombre)));
        }

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Pragma: no-cache"); // HTTP/1.0

        $str_result = "";

        if (true) {
            header("Content-Type: application/json");

//                    echo "{\"results\": [";
            $str_result = "{\"results\": [";
            $arr = array();

            for ($i = 0; $i < count($aResults); $i++) {
                $arr[] = "{\"id\": \"" . $aResults[$i]['id'] . "\", \"value\": \"" . $aResults[$i]['value'] . "\", \"info\": \"\"}";
            }

            $str_result .= implode(", ", $arr);
            $str_result .= "]}";
        }

        return $str_result;
    }

}

