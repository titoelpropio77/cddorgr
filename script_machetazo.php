<?php
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
include_once 'clases/registrar_comprobantes.class.php';

if ($_POST[ejecutar] == 'ok') {

    $sqls = explode(';--', $_POST[sentencia_sql]);
    if (count($sqls) > 0) {
        $conec = new ADO();
        foreach ($sqls as $sql) {
            if ($sql != '') {
//                $sql = str_replace("\'", "'", $sql);
                $sql = stripslashes($sql);
                
//                FUNCIONES::bd_query($sql);
                $conec->ejecutar($sql);
                echo $sql . "<br/>";
            }
        }
    }
} else {
    ?>
    <form id="frm_ejecutar" name="frm_ejecutar" method="POST" enctype="multipart/form-data" action="script_machetazo.php">
        <textarea id="sentencia_sql" name="sentencia_sql" cols="100" rows="10"></textarea><br>
        <input type="button" id="btn_enviar" name="btn_enviar" value="EJECUTAR" />
        <input type="hidden" id="ejecutar" name="ejecutar" value="ok" />
    </form>
    <script src="js/jquery-1.10.2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#btn_enviar').click(function() {
                if ($('#sentencia_sql').val() === '') {
                    alert('Ingrese una sentencia sql valida...');
                    return false;
                }
                $('#frm_ejecutar').submit();
            });
        });
    </script>
    <?php
}
?>