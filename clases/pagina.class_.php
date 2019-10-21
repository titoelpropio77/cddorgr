<?php

class PAGINA {

    function PAGINA() {
        
    }

    function verificar() {
        require_once('usuario.class.php');

        $usuario = new USUARIO;

        if (!($usuario->get_aut() == _sesion)) {
            header('location:index.php');
			exit();
        }
    }

    function abrir_contenido(){
//        echo "abrir_contenido";
        ?>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                <!--<meta http-equiv="Content-Type" content="text/html; charset=utf-8">-->
                <title><?php echo _titulo_empresa; ?></title>
                <?php
                if ($_GET['mod'] == 'gestion' && $_GET['tarea'] == 'ESTRUCTURA'){
                    ?>
                    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
                    <?php
                }elseif($_GET['mod'] == 'uv'){
					?>
					<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
					<?php 
				}elseif($_GET['mod'] == 'repvendidos'){
					?>
					<script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
					<?php 
				}else{?>
					<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
					<?php
				}
                ?>
                <script language="JavaScript" src="js/funciones.js"></script>
                <script type="text/javascript" src="js/calendario.js"></script>
                <script type="text/javascript" src="js/calendario2.js"></script>
                
                <script type="text/javascript" src="js/calendariosetup.js"></script>
                <script type="text/javascript" language="JavaScript1.2" src="js/stm31.js"></script>


                <?php
                if ($_GET['mod'] == 'gestion' && $_GET['tarea'] == 'ESTRUCTURA') {
                    ?>
                    <script type="text/javascript" src="js/jquery-impromptu.5.2.1.js"></script>
                    <link href="css/jquery-impromptu.5.2.1.css" rel="stylesheet" type="text/css" />
                <?php
                } else {
                    ?>
                    <script type="text/javascript" src="js/jquery-impromptu.2.7.min.js"></script>
                    <link href="css/impromptu.css" rel="stylesheet" type="text/css" />
                    <?php
                }
                ?>

                <link href="css/estilos.css" rel="stylesheet" type="text/css">

                <link rel="stylesheet" type="text/css" media="all" href="css/estilo_calendario.css" title="win2k-cold-1" />

            </head>
            <body>

                <script language="javascript" type="text/javascript">
                    $(document).ready(function() {
                        $('.tablaLista tr:not([th]):odd').addClass('tablaListaFila2'); // odd aplicando a las filas inpares					   
                    });
                </script>
            <center>

                <?php
            }

            function cerrar_contenido() {
                ?>
            </center>
        </body>
        </html>
        <?php
    }

}
?>