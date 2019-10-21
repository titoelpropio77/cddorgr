<?php

require_once('database.conf.php');
define('_base', _BASE_DE_DATOS);
define('_host', _SERVIDOR_BASE_DE_DATOS);
define('_usu', _USUARIO_BASE_DE_DATOS);
define('_pas', _PASSWORD_BASE_DE_DATOS);

class QUERY {

    var $BaseDatos;
    var $ServConexion_IDor;
    var $Usuario;
    var $Clave;
    var $Conexion_ID = 0;
    var $Consulta_ID = 0;
    var $row;
    var $Errno = 0;
    var $Error = "";

    function QUERY() {
        $this->conectar();
    }

    function conectar() {
        $this->BaseDatos = _base;
        $this->ServConexion_IDor = _host;
        $this->Usuario = _usu;
        $this->Clave = _pas;

        $this->Conexion_ID = mysql_connect($this->ServConexion_IDor, $this->Usuario, $this->Clave);

        if (!$this->Conexion_ID) {
            $this->Error = "Ha fallado la conexion.";
            return 0;
        }

        if (!@mysql_select_db($this->BaseDatos, $this->Conexion_ID)) {
            $this->Error = "Imposible abrir " . $this->BaseDatos;
            return 0;
        }
        return $this->Conexion_ID;
    }

    function cerrar() {
        @mysql_close($this->Conexion_ID);
    }

    function consulta($sql = "") {
        if ($sql == "") {
            $this->Error = "No ha especificado una consulta SQL";
            return 0;
        }

        //$this->LogSQL($sql);

        $this->Consulta_ID = @mysql_query($sql, $this->Conexion_ID);

        if (!$this->Consulta_ID) {
            $this->Errno = mysql_errno();
            $this->Error = mysql_error();
        }

        return $this->Consulta_ID;
    }

    function mover_anterior() {
        if ($this->row >= 0) {
            $this->row--;

            return true;
        }

        return false;
    }

    function mover_siguiente() {
        if ($this->Consulta_ID + 1 < $this->num_registros()) {
            $this->Consulta_ID++;
            return true;
        }

        return false;
    }

    function num_campos() {

        return mysql_num_fields($this->Consulta_ID);
    }

    function num_registros() {
        return mysql_num_rows($this->Consulta_ID);
    }

    function valores_fila() {
        return mysql_fetch_row($this->Consulta_ID);
    }

    function objeto() {
        return mysql_fetch_object($this->Consulta_ID);
    }

    function nombre_campo($numcampo) {

        return mysql_field_name($this->Consulta_ID, $numcampo);
    }

    function ver_consulta() {

        echo "<table>";
        while ($row = mysql_fetch_row($this->Consulta_ID)) {

            echo "<tr class='fila_oscura'> \n";

            for ($i = 0; $i < $this->num_campos(); $i++) {

                echo "<td>" . $row[$i] . "</td>\n";
            }
            echo "</tr>\n";
        }
        echo "</table>";
    }

    function LogSQL($sql, $force = false) {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "Unknown";
        }
        if (isset($_SERVER['REMOTE_HOST'])) {
            $cliente = $_SERVER['REMOTE_HOST'];
        } else {
            $cliente = "cristian";
        }

        $persona = NEW PERSONA;
        $usuario = $persona->get_usu_id();


        $cmd = "^\*\*\*|" .
                "^ *INSERT|^ *DELETE|^ *UPDATE|^ *ALTER|^ *CREATE|" .
                "^ *BEGIN|^ *COMMIT|^ *ROLLBACK|^ *GRANT|^ *REVOKE";
        $sql = str_replace("'", "/", $sql);
        $sql = str_replace(".", "-", $sql);
        $consulta = "INSERT INTO `log` ( `log_accion` , `log_usuario` , `log_nombre_equipo` , `log_ip_equipo` , `log_fecha` )
					  VALUES ('$sql', '$usuario', '$cliente', '$ip', now());";
        if (eregi($cmd, $sql)) {
            mysql_query($consulta, $this->Conexion_ID);
        }
    }

}

;
?>