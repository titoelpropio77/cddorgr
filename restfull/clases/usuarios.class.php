<?php

class USUARIOS {

    function get_usuario($usu_id) {
        $sql = "select int_nombre,int_apellido 
		from
		ad_usuario inner join interno on (usu_id='$usu_id' and usu_per_id=int_id)
		";

        $result = mysql_query($sql);

        $num = mysql_num_rows($result);

        $objeto = mysql_fetch_object($result);

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function get_persona($int_id) {
        $sql = "select int_nombre,int_apellido from interno where int_id='$int_id'";

        $result = mysql_query($sql);

        $num = mysql_num_rows($result);

        $objeto = mysql_fetch_object($result);

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function get_interno_correo($int_id) {
        $sql = "select int_email from interno where int_id='".$int_id."';";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $objeto = mysql_fetch_object($result);
        return $objeto->int_email;
    }
	
    function get_usuario_correo($int_usu_id) { 
        $sql = "select int_email from ad_usuario inner join interno on (usu_id='$int_usu_id' and usu_per_id=int_id)"; 
        $result = mysql_query($sql);
        $num = mysql_num_rows($result); 
        $objeto = mysql_fetch_object($result);
        return $objeto->int_email;
    }
    
    function get_persona_interno($usuario) {
        $sql = "select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $objeto = mysql_fetch_object($result);
        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function get_vendedor($vendedor) {

        $sql = "select int_nombre,int_apellido from vendedor inner join interno on (vdo_id='$vendedor' and vdo_int_id=int_id)";

        $result = mysql_query($sql);

        $num = mysql_num_rows($result);

        $objeto = mysql_fetch_object($result);

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function nombre_persona_vendedor($vdo_id) {

        $sql = "SELECT int_nombre,int_apellido FROM interno
		inner join vendedor on (vdo_int_id=int_id) 
		WHERE vdo_id='$vdo_id'";

        $result = mysql_query($sql);

        $num = mysql_num_rows($result);

        $objeto = mysql_fetch_object($result);

        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

}

?>