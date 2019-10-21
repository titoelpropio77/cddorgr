<?php
ini_set('display_errors', 'On');

require_once('conexion.php');
require_once('clases/coneccion.class.php');
require_once('clases/usuario.class.php');
require_once('clases/funciones.class.php');
require_once('clases/conversiones.class.php');
require_once('config/constantes.php');
require_once('config/zona_horaria.php');

function ejecutar_script_sql($script) {
//    $comando = 'mysql -u sistema_orange -p ofOOD18IVfF1 sistema_tierrafuturo < ' . $script;
//    $ultima_linea = system($comando, $retornoCompleto);
//    print_r( $ultima_linea );
//    print_r( $retornoCompleto );
    $mysqli = new mysqli(_SERVIDOR_BASE_DE_DATOS, _USUARIO_BASE_DE_DATOS, _PASSWORD_BASE_DE_DATOS, _BASE_DE_DATOS);



    if ($mysqli->connect_errno) {
        echo "Fallo al conectar a MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    echo $mysqli->host_info . "\n";

    $fileSQL = file_get_contents($script);

//    FUNCIONES::print_pre($fileSQL);

    /* Ejecutar consulta multiquery */
    if ($mysqli->multi_query($fileSQL)) {
        do {
            /* Almacenar primer juego de resultados */
            if ($result = $mysqli->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    print_r($row);
                    echo "<br/>";
                }
                $result->free();
            }
            /* mostrar divisor */
            if ($mysqli->more_results()) {
                printf("-----------------<br/>");
            }
            // Avanzar al siguiente resultado
        } while ($mysqli->next_result());
    }

    /* cerrar conexión */
    $mysqli->close();
}

if ($_POST[ejecutar] == 'ok') {

    $script_sql = $_POST[script_sql];
	
    if (is_file($script_sql)) {
		echo "<p>Ejecutando $script_sql...</p>";
		ejecutar_script_sql($script_sql);
    } else {
		echo "<p>$script_sql no es un archivo o no existe...</p>";
	}
	
} else {
    ?>
    <form id="frm_ejecutar" name="frm_ejecutar" method="POST" enctype="multipart/form-data" action="script_machetazo.php">
        <textarea id="script_sql" name="script_sql" cols="100" rows="10"></textarea><br>
        <input type="button" id="btn_enviar" name="btn_enviar" value="EJECUTAR" />
        <input type="hidden" id="ejecutar" name="ejecutar" value="ok" />
    </form>
    <script src="js/jquery-1.10.2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#btn_enviar').click(function() {
                if ($('#script_sql').val() === '') {
                    alert('Ingrese el nombre de un script sql valido...');
                    return false;
                }
                $('#frm_ejecutar').submit();
            });
        });
    </script>
    <?php
}