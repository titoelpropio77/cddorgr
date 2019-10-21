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
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/css/estilos.css">  
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/toastr/build/toastr.min.css" />
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/impromptu/jquery-impromptu.min.css" />
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/preloader/css/materialPreloader.min.css" />         
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/bootstrap-fileinput-master/css/fileinput.min.css" />    
        <link rel="stylesheet" href="<?php echo _base_url; ?>recursos/librerias/sweetalert/lib/sweet-alert.css" />


        <script src="<?php echo _base_url; ?>recursos/librerias/jquery/jquery.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/js/jquery.base64.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/bootstrap-fileinput-master/js/fileinput.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/file_validator/js/file-validator.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/sweetalert/lib/sweet-alert.min.js"></script>

        <script src="<?php echo _base_url; ?>recursos/js/sistema.tools.js"></script>
        <script>
            var _base_url = "<?php echo _base_url; ?>";
        </script>
    </head>
    <body class="fixed-navbar fixed-footer fixed-sidebar fixed-navbar">
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
        <!-- Header --> 
        <div id="header">
            <div class="color-line">
            </div>
            <div id="logo" class="light-version" style="padding: 0;">
                
                    <?php // echo _empresa_nombre; ?>
                    <img class="logoImg" src="<?php echo _base_url?>recursos/img/logo.png?v=<?php echo time();?>" width="180" height="56">
                
            </div>
            <nav role="navigation">
                <div class="header-link hide-menu"><i class="fa fa-bars"></i></div>
                <div class="small-logo" style="padding: 0;">
                    <!--<span class="text-primary"><?php // echo _empresa_nombre; ?></span>-->
                    <img class="logoImg" src="<?php echo _base_url?>recursos/img/logo.png?v=<?php echo time();?>" width="180" height="56">
                </div>

                <div class="mobile-menu">
                    <button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
                        <i class="fa fa-chevron-down"></i>
                    </button> 
                    <div class="collapse mobile-navbar" id="mobile-collapse">
                        <ul class="nav navbar-nav">
                            <li> 
                                <a class="" href="<?php echo _base_url; ?>contrasena">Cambiar Contrase&ntilde;a</a>
                            </li> 
                            <li>
                                <a class="" href="<?php echo _base_url; ?>salir">Cerrar sesi&oacute;n</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="navbar-right"> 
                    <ul class="nav navbar-nav no-borders conSession" >
                        <li class="dropdown"> 
                            <a class="dropdown-toggle label-menu-corner" href="#" data-toggle="dropdown" id="enlace_notificaciones">
                                <i class="pe-7s-speaker"></i>
                                <?php
                                $notificaciones = $_SESSION['notificaciones'];
                                $num_mens = count($notificaciones);
                                ?>
                                <span class="label label-success" id="span_num_not"><?php echo $num_mens > 0 ? $num_mens : ''; ?></span>
                            </a>
                            <ul class="dropdown-menu hdropdown notification animated flipInX" style="width:300px" id="ul_notificaciones">
                                <?php
                                if ($num_mens > 0) {

                                    for ($i = 0; $i < $num_mens; $i++) {
                                        $not = $notificaciones[$i]
                                        ?>
                                        <li id="<?php echo $not->id_not; ?>">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <a href="#">
                                                        <?php echo $not->mensaje; ?>															 
                                                    </a>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="button" class="btn btn-primary btn_leer" id="<?php echo $not->id_not; ?>"><i class="fa fa-check"></i> </button>
                                                </div>
                                            </div>																																				

                                        </li>											
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <li>
                                        <a>
                                            No hay notificaciones en este momento													 
                                        </a>
                                    </li>
                                    <?php
                                }
                                ?>

                                <!--
<li>
    <a>
        <span class="label label-warning">WAR</span> There are many variations.
    </a>
</li>
<li>
    <a>
        <span class="label label-danger">ERR</span> Contrary to popular belief.
    </a>
</li>
<li class="summary"><a href="#">See all notifications</a></li>
                                -->
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a class="dropdown-toggle label-menu-corner" href="#" data-toggle="dropdown">
                                <i class="pe-7s-keypad"></i>
                                <span class="label label-success"></span>
                            </a>
                            <div class="dropdown-menu hdropdown bigmenu animated flipInX">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <a href="<?php echo _base_url; ?>planodisponibilidad"> 
                                                    <i class="fa fa-map text-info"></i>
                                                    <h5>Disponibilidad</h5>
                                                </a>
                                            </td>
                                            <?php
                                            if (FALSE) {
                                            ?>
                                            <td>
                                                <a href="<?php echo _base_url; ?>miscomisiones/Pendiente">
                                                    <i class="fa fa-money text-warning"></i>
                                                    <h5>Comisiones</h5>
                                                </a>
                                            </td>
                                            <?php
                                            }
                                            ?>
                                            <td>
                                                <a href="<?php echo _base_url; ?>reserva/agregar">
                                                    <i class="pe pe-7s-users text-success"></i>
                                                    <h5>Reservar</h5>
                                                </a>
                                            </td>
                                        </tr>
                                    <tbody>
                                        <tr> 
                                            <td>
                                                <!--<a href="<?php // echo _base_url; ?>mired/estructura"> 
                                                    <i class="fa fa-share-alt text-info"></i>
                                                    <h5>Mi Red</h5> 
                                                </a>-->
                                                
                                                <a href="javascript:mostrar_mi_red('<?php echo $_SESSION[vdo_id];?>','<?php echo _sistema_url;?>');"> 
                                                    <i class="fa fa-share-alt text-info"></i>
                                                    <h5>Mi Red</h5> 
                                                </a>
                                                
                                            </td> 
                                            <!--
<td>
    <a href="<?php echo _base_url; ?>miscomisiones/Pagado">
        <i class="fa fa-money text-warning"></i>
        <h5>Comiciones</h5>
    </a>
</td>
<td>
    <a href="<?php echo _base_url; ?>reserva/agregar">
        <i class="pe pe-7s-users text-success"></i>
        <h5>Reservar</h5>
    </a>
</td>
                                            --> 
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </li>

                        <li class="dropdown">
                            <a href="#" class="linkSesionCerrar">
                                <i class="pe-7s-upload pe-rotate-90"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Navigation -->
        <aside id="menu" class="conSession"> 
            <div id="navigation">
                <div class="profile-picture">
                    <a href="<?php echo _base_url; ?>">
                        <!--<img src="<?php echo _base_url; ?>recursos/img/profile.jpg" class="img-circle m-b" alt="logo">-->
                        <img src="<?php echo _base_url; ?>recursos/img/timthumb.php?src=<?php echo _sistema_url; ?>imagenes/persona/<?php echo $_SESSION[afil_foto];?>&h=75&w=75&zc=1" class="img-circle m-b" alt="profile">
                    </a>
                    <div class="stats-label text-color">
                        <span class="font-extra-bold font-uppercase"><?php echo $_SESSION['afil_nombre_completo']; ?></span>
                        <div class="dropdown"> 
                            <a class="dropdown-toggle" href="#" data-toggle="dropdown">
                                <small class="text-muted">Opciones<b class="caret"></b></small>
                            </a>
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a href="<?php echo _base_url; ?>contrasena">Mi Perfil</a></li>
                                <li><a href="<?php echo _base_url; ?>contrasena">Cambiar Contrase&ntilde;a</a></li>
                                <li><a href="<?php echo _base_url; ?>salir">Cerrar sesi&oacute;n</a></li>
                            </ul>
                        </div>
                        <div id="sparkline1" class="small-chart m-t-sm"></div>
                        <div>
                            <small class="text-muted">Rango: <?php echo $_SESSION['afil_rango']; ?></small>
                        </div>
                    </div>
                </div>

                <ul class="nav" id="side-menu"> 
                    <!--<li class="active">--> 
                    <li class="<?php echo $_GET[uri1] == 'portada' ? "active" : ""; ?>"> 
                        <a href="<?php echo _base_url; ?>portada"> <span class="nav-label">Inicio</span></a>
                    </li>

                    <!--MI RED(FORMA ANTERIOR)-->
                    <!--<li class="<?php echo $_GET[uri1] == 'mired' ? "active" : ""; ?>">
                        <a href="<?php echo _base_url; ?>mired"> <span class="nav-label">Mi Red</span></a>
                    </li>--> 
                    <!--MI RED(FORMA ANTERIOR)-->

                    <li class="<?php echo $_GET[uri1] == 'mired' ? "active" : " "; ?>">
                        <a href="#"><span class="nav-label">Mi Red</span><span class="fa arrow"></span> </a>
                        <ul style="" aria-expanded="true" class="nav nav-second-level collapse <?php echo $_GET[uri1] == 'mired' ? "in" : " "; ?>">
                            <!--<li class="<?php echo $_GET[uri2] == 'estructura' ? "active" : " "; ?>"><a href="<?php echo _base_url; ?>mired/estructura">Estructura</a></li>--> 
                            <li class="<?php echo $_GET[uri2] == 'estructura' ? "active" : " "; ?>"><a href="javascript:mostrar_mi_red('<?php echo $_SESSION[vdo_id];?>','<?php echo _sistema_url;?>');">Estructura</a></li> 
                            <li class="<?php echo $_GET[uri2] == 'datos-linea' ? "active" : " "; ?>"><a href="<?php echo _base_url; ?>mired/datos-linea">Datos 1 y 2 Linea</a></li>                                    
                        </ul>
                    </li>

                    <?php
                    if (FALSE) {
                    ?>
                    
                    <li class="<?php echo $_GET[uri1] == 'miscomisiones' ? "active" : " "; ?>">
                        <a href="#"><span class="nav-label">Mis Comisiones</span><span class="fa arrow"></span> </a>
                        <ul style="" aria-expanded="true" class="nav nav-second-level collapse <?php echo $_GET[uri1] == 'miscomisiones' ? "in" : " "; ?>">
                            <li class="<?php echo $_GET[uri2] == 'Pendiente' ? "active" : " "; ?>"><a href="<?php echo _base_url; ?>miscomisiones/Pendiente">Ver</a></li> 
                            <!--<li class="<?php echo $_GET[uri2] == 'Pagado' ? "active" : " "; ?>"><a href="<?php echo _base_url; ?>miscomisiones/Pagado">Pagadas</a></li>-->
                            <li class="<?php echo $_GET[uri2] == 'historial' ? "active" : " "; ?>"><a href="<?php echo _base_url; ?>miscomisiones/historial">Historial</a></li>
                        </ul>
                    </li>

                    <?php
                    }
                    ?>
                    
                    <li class="<?php echo $_GET[uri1] == 'reserva' ? "active" : ""; ?>">
                        <a href="#"><span class="nav-label">Mis Reservas</span><span class="fa arrow"></span> </a>
                        <ul style="" aria-expanded="true" class="nav nav-second-level collapse <?php echo $_GET[uri1] == 'reserva' ? "in" : ""; ?>">
                            <li class="<?php echo $_GET[uri2] == 'agregar' ? "active" : ""; ?>"><a href="<?php echo _base_url; ?>reserva/agregar">Agregar</a></li> 
                            <li class="<?php echo $_GET[uri2] == 'listar' ? "active" : ""; ?>"><a href="<?php echo _base_url; ?>reserva/listar">Listado</a></li>
                        </ul>
                    </li>

                    <li class="<?php echo $_GET[uri1] == 'planodisponibilidad' ? "active" : ""; ?>">
                        <a href="<?php echo _base_url; ?>planodisponibilidad"> <span class="nav-label">Planos y Disponibilidad</span></a>
                    </li>

                    <!--<li class="<?php echo $_GET[uri1] == 'reserva' ? "active" : ""; ?>">
                        <a href="#"><span class="nav-label">CRM</span><span class="fa arrow"></span> </a> 
                        <ul style="" aria-expanded="true" class="nav nav-second-level collapse <?php echo $_GET[uri1] == 'crm' ? "in" : ""; ?>">
                            <li class="<?php echo $_GET[uri2] == 'agregar' ? "active" : ""; ?>"><a href="<?php echo _base_url; ?>crm/seguimiento/agregar">Seguimiento</a></li>
                            <li class="<?php echo $_GET[uri2] == 'agregar' ? "active" : ""; ?>"><a href="<?php echo _base_url; ?>crm/calendario/view">Calendario</a></li>
                        </ul>
                    </li>-->

                    <li class="<?php echo $_GET[uri1] == 'contrasena' ? "active" : ""; ?>">
                        <a href="<?php echo _base_url; ?>contrasena"> <span class="nav-label">Mi Contraseña</span></a>
                    </li>
                </ul>
            </div> 
        </aside>

        <!-- Main Wrapper -->
        <div id="wrapper">
            <div class="content animate-panel">
<?php
if (isset($_SESSION['mensajes'])) {
    $mensaje = $_SESSION['mensajes'];
    $_SESSION['mensajes'] = '';
    echo $mensaje;
}

if ($view != NULL) {
    require_once ("view/" . $view);
}
?>
            </div>
            <footer class="footer"> 
                <span class="pull-right"> 
                    Desarrollado por:<a href="http://orangegroup.com.bo/" target="_blank" style="color:#E67E22;"> Orange Group SRL</a>
                </span>
                &copy; <?php echo date('Y'); ?> <?php echo _empresa_nombre; ?>				
            </footer>
        </div>

        <!-- App scripts -->

        <script src="<?php echo _base_url; ?>recursos/librerias/jquery-ui/jquery-ui.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/slimScroll/jquery.slimscroll.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/bootstrap/dist/js/bootstrap.min.js"></script>  
        <script src="<?php echo _base_url; ?>recursos/librerias/metisMenu/dist/metisMenu.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/toastr/build/toastr.min.js"></script> 
        <script src="<?php echo _base_url; ?>recursos/librerias/impromptu/jquery-impromptu.min.js"></script> 
        <script src="<?php echo _base_url; ?>recursos/librerias/preloader/js/materialPreloader.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/iCheck/icheck.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/librerias/mask/jquery.maskedinput.min.js"></script>
        <script src="<?php echo _base_url; ?>recursos/js/orangegroup.init.js"></script> 
        <script src="<?php echo _base_url; ?>recursos/js/orangegroup.tools.js"></script>
        <script src="<?php echo _base_url; ?>recursos/js/jquery.serializeObject.js"></script> 

        <script>
            $(document).ready(function() {

                $('.btn_leer').click(function() {
                    var not_id = $(this).attr('id');
                    console.log('not_id => ' + not_id);

                    $.post(_base_url + 'ajax', {tarea: 'leer_notificacion', not_id: not_id}, function(respuesta) {
                        var res = eval("(" + respuesta + ")");

                        console.log(res);

                        res = res[0].respuesta;
                        if (res.accion == 'correcto') {
                            $('li [id="' + not_id + '"]').remove();
                            var num_not = $('#span_num_not').text() * 1;
                            num_not -= 1;
                            num_not = num_not > 0 ? num_not : '';
                            $('#span_num_not').text(num_not);

                            if (num_not == 0) {
                                $('#ul_notificaciones').html("<li><a>No hay notificaciones en este momento</a></li>");
                            }

                            $('#enlace_notificaciones').trigger('click');
                        }
                    });
                });

                $('#enlace_notificaciones').click(function() {
                    console.log('clickeando las notificaciones...');
                });

            });
        </script>
    </body>
</html>