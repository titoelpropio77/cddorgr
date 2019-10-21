<?php

require_once('config/zona_horaria.php');
require_once('config/constantes.php');
require_once('config/database.conf.php');
require_once('clases/adodb/adodb.inc.php');
require_once('clases/usuario.class.php');
require_once('clases/coneccion.class.php');

require_once('clases/funciones.class.php');

if ($_POST['aut'] == 'ok') {
    $usuario = new USUARIO;
    $gestion = isset($_POST['ges_id']) ? $_POST['ges_id'] : '';
    if ($usuario->validar_usuario() && $gestion != '') {
        
        $usuario->iniciar_sesion();
        //header('location:principal.php');        
        ?>
        <script>
            location.href = "principal.php";
        </script>
        <?php
    }  else {
        abrir();
        login();
        cerrar(true);            
    }
} else {
    abrir();
    login();
    cerrar();
    
}

function abrir() {
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
			<link rel="shortcut icon" href="http://www.orangegroup.com.bo/img/favicon.png" type="image/png">
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
            <title><?php echo _titulo_empresa; ?></title>
            <link href="css/ini_login.css" rel="stylesheet" type="text/css" />
        </head>
        <body>
            <div id="seContenedor">
                <!-- Superior sesion-->
                <div class="superior">
                    <div class="superiorIzq"></div>
                    <div class="superiorCentro">
                        <div class="superiorCentroDer"></div>
                        <div class="superiorIcon"><img src="imagenes/iconLlave.png" width="30" height="31" /></div>
                        <div class="superiortTitulo"><?php echo _titulo_empresa; ?></div>
                        <div class="superiorVersion"></div>
                    </div>
                    <div class="superiorDer"></div>
                </div>
                <!-- cuerpo sesion-->
                <div class="seCuerpo">
                    <div class="form">
                        <span class="texto1"><br>Introduce tu nombre de usuario y contrase&ntilde;a para autentificarte.<br /><br /></span>
                        <div class="espacio">
                            <?php
                        }

    function cerrar($ban = false, $mensaje = '') {
        if ($mensaje == '') {
            $mensaje = 'Usuario no valido';
        }
        if($GLOBALS[msj_inicio]){
            $ban =true;
            $mensaje = $GLOBALS[msj_inicio];
        }
                            ?>
                        </div>


                        <!-- mensajes de error -->
                        <div class="mensajeError"><?php if ($ban || $_GET['aut'] == 'x') echo "<font color='#FF0000'>$mensaje</font>"; ?></div>                        
                        <!-- mensajes de error fin -->


                    </div>
                    <div class="logo">
                        <img class="logoImg" src="imagenes/logo.png" width="" height="" />
                    </div>
                </div>
                <!-- Inferior sesion -->
                <div class="seInferior">
                    <div class="seInferiorIzq"><img src="imagenes/iniciarSesion_28.gif" width="14" height="13" /></div>
                    <div class="seInferiorDer"><img src="imagenes/iniciarSesion_30.gif" width="14" height="13" /></div>
                </div>
                <div class="seCopy">
                    &copy; 2011 - <?php echo date('Y'); ?> Orange Group S.R.L. 
                    </p>
                </div>
        </body>
    </html>

    <?php
}

function login() {
    ?>
    <form id="form1" name="form1" action="index.php" method="post">

        <div class="titulos">Usuario</div>
        <input id="myusername" class="inp" type="text" alt="Nombre" tabindex="1" name="myusername" value="<?php echo $_POST['myusername'] ?>"/>
        <input name="aut" type="hidden" value="ok" />
        <div class="titulos">Contrase&ntilde;a</div>
        <input id="mypassword" class="inp" type="password" alt="Contrase?a" tabindex="2" name="mypassword"/>
        <div class="titulos">Gesti&oacute;n</div>
        <select name="ges_id" class="caja_texto">            
            <?php
            $fun = NEW FUNCIONES;
            $fun->combo("select ges_id as id, ges_descripcion as nombre from con_gestion where ges_estado='Abierto' order by ges_fecha_ini desc", $_POST['ges_id']);
            ?>
        </select>

        <div class="btEnviar">
            <input type="image" name="imageField" id="imageField" tabindex="3" src="imagenes/iniciarSesion_26.gif" />
        </div>
    </form>
    <?php
}
?>

