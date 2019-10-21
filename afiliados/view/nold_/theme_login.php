<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" href="http://orangegroup.com.bo/web/img/favicon.png" type="image/png" />
        <title><?php echo _empresa_nombre; ?></title>
        <!-- Vendor styles --> 
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/fonts/fontawesome/css/font-awesome.css" />
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/metisMenu/dist/metisMenu.css" />
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/animate.css/animate.css" />
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/bootstrap/dist/css/bootstrap.css" />
        <!-- App styles -->
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css" />
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/fonts/pe-icon-7-stroke/css/helper.css" />
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/css/style.css"> 
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/toastr/build/toastr.min.css" />
		
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/preloader/css/materialPreloader.min.css" /> 
    </head>
    <body class="blank" style="background-image: url('<?php echo _base_url?>recursos/img/fondo.jpg');">

        <div class="splash"> 
            <div class="color-line"></div>
            <div class="splash-title">
                <h1>Espere un Momento</h1>
                <p>Estamos conectando al servidor...</p>
                <img src="<?php echo _base_url; ?>recursos/img/loading-bars.svg" width="64" height="64" /> 
            </div>
        </div>

        <!--[if lt IE 7]>
        <p class="alert alert-danger">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <?php
        require_once ("view/" . $view);
        ?>

        <!-- App scripts -->
        <script src="<?php echo _base_url; ?>recursos/librerias/jquery/jquery.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/jquery-ui/jquery-ui.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/slimScroll/jquery.slimscroll.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/bootstrap/dist/js/bootstrap.min.js"></script>  
        <script src="<?php echo _base_url; ?>recursos/librerias/metisMenu/dist/metisMenu.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/toastr/build/toastr.min.js"></script>
		
        <script src="<?php echo _base_url; ?>recursos/librerias/preloader/js/materialPreloader.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/iCheck/icheck.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/js/orangegroup.init.js"></script>
    </body>
</html>
