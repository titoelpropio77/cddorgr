<?php 
	date_default_timezone_set("America/La_Paz");
	require_once('config/database.conf.php');
	require_once('clases/adodb/adodb.inc.php');
	require_once('clases/usuario.class.php');
	require_once('clases/coneccion.class.php');
	require_once('clases/funciones.class.php');
	require_once('config/constantes.php');
        
        ADO::$token=date('YmdHis');	
        
	$usuario=new USUARIO;
	
if($usuario->get_aut()==_sesion){
	//require_once('clases/configuracion.class.php');
	
	//$config=new CONFIGURACION();
		
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="http://www.orangegroup.com.bo/img/favicon.png" type="image/png">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo _titulo_sistema; ?></title>
<link href="css/include_admin.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>

<!-- icons panel -->
<link href="css/panel_icons.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.liquidcarousel.min.js"></script>

<script type="text/javascript" src="js/jquery-impromptu.2.7.min.js"></script>
<link href="css/impromptu.css" rel="stylesheet" type="text/css" />

<script language="javascript" type="text/javascript">

function initMenu() {
	$('.menuAccordion ul').hide();
	/*$('.menuAccordion ul:first').show();*/
	/*******   menu nivel 1   ******/
	$('.menuAccordion .mLink1').click(function(){
		var cat = $(this).next();
		$(".menuNivel1").slideUp('normal');
		if(cat.is(':visible')){
			$(cat).slideUp('normal');
		} else {
			$(cat).slideDown('normal');
		};
	});	
};				   						   


$(document).ready(function() {
	initMenu();
	
	/* menu activo categorias */
	$("#mLink1Activo").addClass("mLink1Activo"); // activo 
	$(".mLink1").bind("click", function(event){
		$(".mLink1").removeClass("mLink1Activo");
		$(this).addClass("mLink1Activo");
		event.stopPropagation();
	})
	/* menu activo subcategorias */
	$("#mLinkSubCatActivo").addClass("mLinkSubCatActivo"); // activo 
	
        $(".mLinkSubCat").bind("click", function(event){
		$(".mLinkSubCat").removeClass("mLinkSubCatActivo");
                $(".mLinkSubCatRep").removeClass("mLinkSubCatActivoRep");
		$(this).addClass("mLinkSubCatActivo");
		event.stopPropagation();
	});
        $(".mLinkSubCatRep").bind("click", function(event){
		$(".mLinkSubCat").removeClass("mLinkSubCatActivo");
		$(".mLinkSubCatRep").removeClass("mLinkSubCatActivoRep");
		$(this).addClass("mLinkSubCatActivoRep");
		event.stopPropagation();
	});
        
	/*  centrar icons */
	var panWidth = 0;
	var panHeight = 0;
	
	function icons_centrar(){
		panWidth = $(window).width();
		panHeight = $(window).height();
		var iconsAncho = (($(window).width())-(630));
		var iconWidth = $("#icons").width();
		if(panWidth  > 980){
			$("#icons").css({"left":((panWidth/2)-(iconWidth/2)-30)+"px", "width": iconsAncho+"px"});
		}
		iframe();
	}
	
	$(window).resize(function() {
		icons_centrar();
	});
	$('#liquid').liquidcarousel({
		height:65, 
		duration:100, 
		hidearrows:false
	});
	
	// calcular iframe 
	function iframe(){
		var auxTop = (($(".top:first").height())+($(".cuerpoTop:first").height()));
		var auxInf = (($(".inferior").height())+(10));
		var result = ((panHeight)-(auxTop+auxInf));
		$("#contenido").height(((result)-(13)));
		$(".menuMod").css({"min-height" :((result)-(60))+"px"});
	}
	
	icons_centrar();
});	


</script>
</head>
<body>
	<div class="contenedor">
        <style>
            .barra_accesos{ position: absolute; right: 0; top: 60px; }
            .barra_accesos a{display: block;padding: 0 3px; border-radius: 5px; float: left;}
            .barra_accesos a:hover{ background-color: #c2c2c2;}
        </style>
    	<div class="top">
        	<img src="imagenes/panel_logo.png" class="logoTop"/>
                <div class="barra_accesos" >
                    <?php if(FUNCIONES::verificar_permisos($_SESSION[id], 191,'AGREGAR')){?>
                        <a  href="javascript:void(0)" data-url="gestor.php?mod=con_cv_divisa&tarea=AGREGAR&dtipo=c" class="a_accesos">
                            <img src="images/divisac.png" border="0" title="AGREGAR" alt="AGREGAR" width="23"/>
                        </a>
                        <a  href="javascript:void(0)" data-url="gestor.php?mod=con_cv_divisa&tarea=AGREGAR&dtipo=v" class="a_accesos">
                            <img src="images/divisav.png" border="0" title="AGREGAR" alt="AGREGAR" width="23"/>
                        </a>
                    <?php } ?>
                    <?php if(FUNCIONES::verificar_permisos($_SESSION[id], 22,'CONFIGURAR RECIBO')){?>
                        <a  href="javascript:void(0)" data-url="gestor.php?mod=usuario&tarea=CONFIGURAR RECIBO" class="a_accesos">
                            <img src="images/config_file.png" border="0" title="CONFIGURAR RECIBO" alt="CONFIGURAR RECIBO" width="23"/>
                        </a>
                    <?php } ?>
                </div>
                <script>
                    popup = null;
                    $('.a_accesos').click(function(){
                        var url=$(this).attr('data-url')+'&popup=1';
                        if(popup!==null){
                            popup.close();
                        }
                        popup = window.open(url,'reportes','left=100,width=900,height=600,top=0,scrollbars=yes');
                        popup.document.close();
                    });
                        
                    
                </script>
                <div class="icons" id="icons">
                        <div id="liquid" class="liquid">
                                <span class="previous"></span>
                                <div class="wrapper">
                                        <ul>
                                                <?php
                                                $conec=new ADO();

                                                $sql="
                                                select
                                                        ele_nombre,ele_titulo,ele_icono_acceso,acc_tarea
                                                from
                                                        ad_accesos 
                                                        inner join ad_elemento on (acc_ele_id=ele_id)
                                                where
                                                        ele_estado='H' and acc_usu_id='".$usuario->get_id()."'
                                                ";

                                                $conec->ejecutar($sql);

                                                $num=$conec->get_num_registros();

                                                for($i=0;$i<$num;$i++)
                                                {
                                                        $objeto=$conec->get_objeto();

                                                        if($objeto->ele_icono_acceso<>"")
                                                                $imagen=$objeto->ele_icono_acceso;
                                                        else	
                                                                $imagen='none.png';
                                                        ?>
                                                        <li><a target="contenido" href="gestor.php?mod=<?php echo $objeto->ele_nombre; ?>&tarea=<?php echo $objeto->acc_tarea; ?>" class="linkIcons"><img src="imagenes/<?php echo $imagen; ?>" alt="<?php echo $objeto->ele_titulo; ?>" title="<?php echo $objeto->ele_titulo; ?>"/></a></li>
                                                        <?php

                                                        $conec->siguiente();
                                                }

                                                ?>	
                                        </ul>
                                </div>
                                <span class="next"></span>
                        </div>
                </div>

                <div class="usuario">
                    <div class="usuarioIzq"></div>
                    <div class="usuarioCent">
                            <span class="usuarioEspacio">
                            <div class="usuarioFoto"><img src="imagenes/persona/chica/<? if($usuario->get_foto()<>"") echo $usuario->get_foto(); else  echo 'sin_foto.gif';?>"/></div>
                            <h4>Bienvenido</h4>
                            <? 
                                                    if(strlen(trim($usuario->get_nombre_completo()))>22)
                                                    {
                                                            echo substr(utf8_encode(trim($usuario->get_nombre_completo())), 0,22)."...";
                                                    }
                                                    else
                                                    {
                                                            echo utf8_encode(trim($usuario->get_nombre_completo()));
                                                    }
                                                    ?><br />
                            <a href="log_out.php">Cerrar Sesión</a>
                        </span>
                    </div>
                    <div class="usuarioDer"></div>
                </div>
			
                <?php
                $fecha = date("Y-m-d");
                $objeto = FUNCIONES::objeto_bd("con_gestion", 'ges_id', $_SESSION['ges_id']);
                $cambios = FUNCIONES::bd_query("select  mon_Simbolo, tca_valor from con_tipo_cambio tc, con_moneda m where tca_mon_id=mon_id and tca_fecha='$fecha';");
                ?>
                <div class="tipo_cambio">  
                    <input type="hidden" id="id_con_gestion" value="<?php echo $objeto->ges_id;?>">
                    <a href="javascript:void(0)" id="txt_con_gestion">
                        <b><?php echo "'$objeto->ges_descripcion'"; ?></b>
                    </a>&nbsp;&nbsp; | &nbsp;&nbsp;
                    <b>Tipos de Cambio para la fecha <?php echo date("d/m/Y") ?>: </b> &nbsp;&nbsp;
                    <span class="tipo_cambio_msj">
					<?php
					if ($cambios->get_num_registros() > 0) {
						for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
							$cambio = $cambios->get_objeto();
							?>
									<b><?php echo $cambio->mon_Simbolo ?></b>(<?php echo number_format(($cambio->tca_valor), 2, '.', ','); ?>)&nbsp;&nbsp;
									<?php
									$cambios->siguiente();
								}
							} else {
								?>
								No existe tipos de Cambio asignado para esta fecha.
                            <?php
                        }
                        ?>
                    </span>
                </div>
                <script>
                    $("#txt_con_gestion").click(function (){
					
                        $.get('AjaxRequest.php',{peticion:'gestiones'},function (respuesta){                            
                            var txt = 'Seleccione la Gesti&oacute;n<br>';
                            txt +=respuesta;                            
                            $.prompt(txt, {
                            buttons: {Aceptar: true, Cancelar: false},
                            callback: function(v, m, f) {
                                    if (v) {
                                        $.get('AjaxRequest.php',{peticion:'cambiar_gestion',ges_id:f.gestion},
                                            function (respuesta){                                                
                                                location.href='principal.php';
                                            }
                                        );
                                    }
                                }
                            });
                        });
                    });
                </script>
        </div>
                
        <!-- cuerpo s-->
        <div class="cuerpo">
        	<div class="cuerpoTop">
            	<div class="cuerpoTopIzq"></div>
                <div class="cuerpoTopDer"></div>
            </div>
            
            <div class="fondo">
            	<div class="fondo2">
            <script>
                                $(document).ready(function() {
                                    $("#pestania_mostrar_ocultar_menu").click(function() {
                                        $(".menuModulo").each(function() {
                                            displaying = $(this).css("display");
                                            if (displaying == "block") {

                                                $(this).animate({
                                                    width: "toggle"
                                                }, 5, function() {
                                                    $('#pestania_mostrar_ocultar_menu').css("margin-left", "13px");
                                                    $(this).next().css("width", "100%");
                                                    $('.topder').css("width", "38px");
                                                    $('.span').css("width", "38px");
                                                    $('#pestania_mostrar_ocultar_menu span').text(">>");
                                                    $('.topizq').css("display", "block");
                                                });
                                            } else {
                                                $(this).animate({
                                                    width: "toggle"
                                                }, 5, function() {
                                                    $('#pestania_mostrar_ocultar_menu').css("margin-left", "20%");
                                                    $(this).next().css("width", "77.5%");
                                                    $('.topder').css("width", "50px");
                                                    $('.span').css("width", "50px");
                                                    $('#pestania_mostrar_ocultar_menu span').text("<<");
                                                    $('.topizq').css("display", "none");
                                                });
                                            }
                                        });
                                    });
                                });
                            </script>
                            <style>
                                #pestania_mostrar_ocultar_menu{
                                    color:#FFFFFF;
                                    width: 50px;
                                    height:30px;
                                    #padding: 6px 0 0;
                                    background: rgba(0, 0, 0, 0) url("imagenes/admin_22.gif"); 
                                    z-index:3000;
                                    position:absolute;
                                    margin-left:20%;
                                    text-align:center;
                                    font-weight:bold;
                                    cursor: pointer;
                                }
                                .topizq{
                                    float: left;
                                    width: 12px;
                                    height: 30px;
                                    background: url(../imagenes/admin_20.gif) 0 0 no-repeat;
                                }
                                .topder{
                                    float: left;
                                    width: 50px;
                                    height: 30px;
                                }
                                .span{
                                    width:50px;
                                    height:30px;
                                    float:left;
                                    margin-top:8px;
                                    text-align:center;
                                    font-weight:bold;
                                }
                            </style>
                    <div id="pestania_mostrar_ocultar_menu">
                        <div class="topizq" style="display:none;"></div>
                        <div class="topder"><span class="span"><?php echo '<<'; ?></span></div>
                    </div>
                    
            	<div class="menuModulo">
                	<div class="menuTop">
                    	<div class="menuTopIzq"></div>
                        <div class="menuTopCent">.: MENU :.</div>
                    </div>
                    
                    <div class="menuMod">
                    
                        <ul class="menuAccordion">
                            <?				
                            require_once('herramientas/menu/menu.class.php');
                            $menu=NEW MENU($usuario->get_id());	
                            $menu->dibujar_menu();
                            ?>

                        </ul>
                  </div>
                    
                    <div class="menuInf">
                    	<div class="menuInfDer"></div>
                    </div>
                </div> 
                
                <div class="contenido">
                    <iframe id="contenido" name="contenido" src="inicio.php" class="iframeContenido" allowtransparency="true"  allowtransparency="allowtransparency" frameborder="0">
                    </iframe>	
                </div>
                         	
            </div>
            
            
            </div>
        </div>
        
        
        
        <!-- Informacion -->
        <div class="inferior">
        	<div class="inferiorIzq"></div>
            <div class="inferiorCent">© 2011 - <?php echo date("Y");?> Orange Group </div>
            <div class="inferiorDer"></div>
        </div>
        
    
    </div>
</body>
</html>
<?php
}
else 
{
	header('location:index.php?aut=x');		
}
?>
