<script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
<link href="../css/estilos.css" rel="stylesheet" type="text/css">
<link href="../css/examples.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../js/jquery.maskedinput-1.3.min.js"></script> 
<script type="text/javascript" src="../js/jquery-impromptu.2.7.min.js"></script> 
<script>
    jQuery(function($) {
        $("#int_fecha_ingreso").mask("99/99/9999");
        $("#int_fecha_nacimiento").mask("99/99/9999");
    });
</script>

<style>
    #hiddenemp{display:none;}
</style>

<script>
    function enviar_formulario_persona()
    {
        var nombre = document.frm_agregar.int_nombre.value;
        var apellido = document.frm_agregar.int_apellido.value;
        //document.frm_agregar.submit();
        if (nombre != '' && apellido != '')
        {
            document.frm_agregar.submit();
        }
        else
        {
            $.prompt('Los campos marcado con (*) son requeridos.', {opacity: 0.8});
        }
    }
</script>

<?php
require_once('mysql.php');
require("../clases/mytime_int.php");
require("../clases/conversiones.class.php");
require('../clases/session.class.php');
require('funciones.php');
?>
<!--<a href="javascript:window.parent.print();"><img src="images/printer.png" border="0" width="16"></a>-->
<?
$red = "gestor.php?mod=venta&tarea=AGREGAR";

/* ================================================ */

function dibujar_mensaje($mensaje) {
    ?>
    <link href="../css/estilos.css" rel="stylesheet" type="text/css">
    <div>
        <div class="fila_formulario_cabecera">VOLVER</div>
        <div>
            <br>	
            <table class="tablaLista" cellpadding="0" cellspacing="0">
                <thead>
                    <tr bgcolor="#DEDEDE">
                        <th >
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>

                <center><h1><?php echo $mensaje; ?></h1></center>

                </td>
                </tr>
                </tbody>
            </table>
            <br><center>
                <form id="form_eliminacion" action="<?php echo "../gestor.php?mod=venta&tarea=AGREGAR"; ?>" method="POST" enctype="multipart/form-data">
                    <input type="button" value="Volver" class="boton" onclick="cierra();">
                </form>
                <!--<a href="#" onclick="cierra();"> Cerrar</a>-->
                <script>
            function cierra()
            {
                parent.$.fn.fancybox.close();
                return false
            }
                </script>
            </center>
        </div>			
    </div>
    <?php
}

function revisar_registro() {
    $query = new QUERY();

    $sql = "SELECT * FROM interno WHERE int_nombre='" . trim($_POST['int_nombre']) . "' AND int_apellido='" . trim($_POST['int_apellido']) . "'";

    $query->consulta($sql);

    $num = $query->num_registros();

    if ($num > 0) {
        return false;
    } else {
        return true;
    }
}

function insertar_tcp() {
    $session = new SESSION;
    $query = new QUERY();
    $txt_ubicacion_nac = FUNCION::atributo_bd_sql("select concat(pais_nombre, ', ',est_nombre,', ',lug_nombre) as campo from ter_lugar, ter_estado, ter_pais where lug_est_id=est_id and est_pais_id=pais_id and lug_id=$_POST[lug_nac]");
    $txt_ubicacion_res = FUNCION::atributo_bd_sql("select concat(pais_nombre, ', ',est_nombre,', ',lug_nombre) as campo from ter_lugar, ter_estado, ter_pais where lug_est_id=est_id and est_pais_id=pais_id and lug_id=$_POST[lug_res]");
    if ($_FILES['int_foto']['name'] <> "") {

        $conversor = new convertir();
        $result = subir_imagen($nombre_archivo, $_FILES['int_foto']['name'], $_FILES['int_foto']['tmp_name']);
        
        $sql = "insert into interno(
                    int_nombre,int_apellido,int_ci,int_fecha_nacimiento,int_fecha_ingreso,
                    int_email,int_telefono,int_celular,int_direccion,int_foto,int_usu_id,
                    int_lug_nac,int_ubicacion_nac,int_lug_res,int_ubicacion_res
                ) values(
                    '" . trim($_POST['int_nombre']) . "','" . trim($_POST['int_apellido']) . "','" . trim($_POST['int_ci']) . "','" . $conversor->get_fecha_mysql($_POST['int_fecha_nacimiento']) . "','" . date('Y-m-d'). "',
                    '" . trim($_POST['int_email']) . "','" . trim($_POST['int_telefono']) . "','" . trim($_POST['int_celular']) . "','" . trim($_POST['int_direccion']) . "','" . $nombre_archivo . "','" . $session->get('id') . "',
                    '$_POST[lug_nac]','$txt_ubicacion_nac','$_POST[lug_res]','$txt_ubicacion_res'
                    
                )";

        if (trim($result) <> '') {

            //$this->formulario->ventana_volver($result,$this->link.'?mod='.$this->modulo);
            //echo "";
        } else {
            $query->consulta($sql);
        }
    } else {
        $conversor = new convertir();

        $sql = "insert into interno(
                    int_nombre,int_apellido,int_ci,int_fecha_nacimiento,int_fecha_ingreso,
                    int_email,int_telefono,int_celular,int_direccion,int_usu_id,
                    int_lug_nac,int_ubicacion_nac,int_lug_res,int_ubicacion_res
                ) values(
                    '" . trim($_POST['int_nombre']) . "','" . trim($_POST['int_apellido']) . "','" . trim($_POST['int_ci']) . "','" . $conversor->get_fecha_mysql($_POST['int_fecha_nacimiento']) . "','" . date('Y-m-d') . "',
                    '" . trim($_POST['int_email']) . "','" . trim($_POST['int_telefono']) . "','" . trim($_POST['int_celular']) . "','" . trim($_POST['int_direccion']) . "','" . $session->get('id') . "',
                    '$_POST[lug_nac]','$txt_ubicacion_nac','$_POST[lug_res]','$txt_ubicacion_res'
                    
                )";

        $query->consulta($sql);
    }
//    echo "$sql;<br>";
}

function validar_formulario() {
    if ($_POST['int_nombre'] != "" && $_POST['int_apellido'] != "") {
        return true;
    } else {
        return false;
    }
}

/* ================================================ */

if ($_POST['oculto1'] == 1) {
    if (validar_formulario()) {
        if (revisar_registro()) {
            insertar_tcp();
            dibujar_mensaje('PERSONA AGREGADA CORRECTAMENTE');
        } else {
            dibujar_mensaje('NO SE PUEDE REGISTRAR A LA PERSONA, POR QUE YA EXISTE UNA PERSONA CON EL MISMO NOMBRE Y APELLIDO');
        }
    } else {
        dibujar_mensaje('LOS CAMPOS "NOMBRE" Y "APELLIDO" SON REQUERIDOS ');
    }
} else {
    ?>
    <script>
        function cierra()
        {
            parent.$.fn.fancybox.close();
            return false
        }
    </script>
    <script src="../js/util.js"></script>
    <div id="hiddenemp2" >
        <div class="fila_formulario_cabecera" style="background:#94CBE6;"> <?PHP echo "AGREGAR NUEVA PERSONA"; ?></div>
        <div id="Contenedor_NuevaSentencia" style="width:98%;">		
            <form id="frm_agregar" name="frm_agregar" action="<? echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">  

                <div id="FormSent">

                    <div class="Subtitulo">Datos Personales</div>
                    <div id="ContenedorSeleccion">

                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Foto</div>
                            <div id="CajaInput">
    <?php
    if ($_POST['int_foto'] <> "") {
        $foto = $_POST['int_foto'];
        $b = true;
    } else {
        $foto = 'sin_foto.gif';
        $b = false;
    }
    if (($ver) || ($cargar)) {
        ?>
                                    <img src="imagenes/persona/chica/<?php echo $foto; ?>" border="0" ><?php if ($b and $_GET['tarea'] == 'MODIFICAR') echo '<a href="' . $this->link . '?mod=' . $this->modulo . '&tarea=MODIFICAR&id=' . $_GET['id'] . '&img=' . $foto . '&acc=Imagen"><img src="images/b_drop.png" border="0"></a>'; ?><br>
                                    <input   name="int_foto" type="file" id="int_foto" />
        <?php
    }
    else {
        ?>
                                    <input  name="int_foto" type="file" id="int_foto" />
        <?php
    }
    ?>
                                <input   name="fotooculta" type="hidden" id="fotooculta" value="<?php echo $_POST['int_foto'] . $_POST['fotooculta']; ?>"/>
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Nombre</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_nombre" id="int_nombre" size="40" maxlength="250" value="<?php echo $_POST['int_nombre']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Apellido</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_apellido" id="int_apellido" size="40" maxlength="250" value="<?php echo $_POST['int_apellido']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >CI</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_ci" id="int_ci" size="20" maxlength="250"  value="<?php echo $_POST['int_ci']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha de Nacimiento</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="int_fecha_nacimiento" id="int_fecha_nacimiento" size="12" value="<?php echo $_POST['int_fecha_nacimiento']; ?>" type="text">
                                <span class="flechas1">(DD/MM/AAAA)</span>
                                <!--	<input name="but_fecha_pagos" id="but_fecha_pagos" class="boton_fecha" value="..." type="button"> -->
                            </div>		
                        </div>
                        <div id="json_estados" hidden="">
                            <?php $estados =  FUNCION::lista_bd_sql("select * from ter_estado where est_eliminado='No'")?>
                            <?php foreach ($estados as $est) {
                                 $est->est_nombre=  FUNCION::limpiar_cadena($est->est_nombre);
                             }
                             echo json_encode($estados);
                             ?>
                        </div>
                        <div id="json_lugares" hidden="">
                            <?php $lugares =  FUNCION::lista_bd_sql("select * from ter_lugar where lug_eliminado='No'")?>
                            <?php foreach ($lugares as $lug) {
                                 $lug->lug_nombre=  FUNCION::limpiar_cadena($lug->lug_nombre);
                             }
                             echo json_encode($lugares);
                             ?>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Lugar donde  Nacio</div>
                            <div id="CajaInput">
                                <?php $paises=  FUNCION::lista_bd_sql("select * from ter_pais where pais_eliminado='No'");?>
                                 <div style="float: left;">
                                     <select name="pais_nac" id="pais_nac" style="width: 80px;">
                                         <?php foreach ($paises as $pais) {?>
                                             <option value="<?php echo $pais->pais_id;?>" <?php echo $_POST[pais_nac]==$pais->pais_id?'selected="true"':''?>><?php echo $pais->pais_nombre;?></option>
                                         <?php }?>
                                     </select>

                                     <select name="est_nac" id="est_nac" style="width: 100px;">
                                         <?php $_estados=  $_POST[est_nac]>0?FUNCION::lista_bd_sql("select * from ter_estado where est_pais_id='$_POST[pais_nac]'"):null;?>
                                         <?php if($_estados){?>
                                            <?php foreach ($_estados as $est) {?>
                                                <option value="<?php echo $est->est_id;?>" <?php echo $_POST[est_nac]==$est->est_id?'selected="true"':''?>><?php echo $est->est_nombre;?></option>
                                            <?php }?>
                                         <?php }?>
                                     </select>
                                     <select name="lug_nac" id="lug_nac" style="width: 100px;"> 
                                         <?php $_lugares=  $_POST[lug_nac]>0?FUNCION::lista_bd_sql("select * from ter_lugar where lug_est_id='$_POST[est_nac]'"):null;?>
                                         <?php if($_lugares){?>
                                            <?php foreach ($_lugares as $lug) {?>
                                                <option value="<?php echo $lug->lug_id;?>" <?php echo $_POST[lug_nac]==$lug->lug_id?'selected="true"':''?>><?php echo $lug->lug_nombre;?></option>
                                            <?php }?>
                                         <?php }?>
                                     </select>
                                 </div>
                                 <script>
                                     $('#pais_nac').change(function (){// mostrar_estados
                                         var pais_id=$(this).val();
                                        $('#est_nac').children().remove();
                                        $('#lug_nac').children().remove();
                                        var estados=JSON.parse(trim($('#json_estados').text()));
                                        var options='';
                                        for(var i=0;i<estados.length;i++){
                                            var est=estados[i];
                                            if(pais_id===est.est_pais_id){
                                                 options+='<option value="'+est.est_id+'">'+est.est_nombre+'</option>';
                                            }
                                        }
                                        $('#est_nac').append(options);
                                        $('#est_nac').trigger('change');

                                    });
                                     $('#est_nac').change(function (){// mostrar_lugares
                                         var est_id=$(this).val();
//                                                       $('#est_id').children().remove();
                                        $('#lug_nac').children().remove();
                                        var lugares=JSON.parse(trim($('#json_lugares').text()));
                                        var options='';
                                        for(var i=0;i<lugares.length;i++){
                                            var lug=lugares[i];
                                            if(est_id===lug.lug_est_id){
                                                 options+='<option value="'+lug.lug_id+'">'+lug.lug_nombre+'</option>';
                                            }
                                        }
                                        $('#lug_nac').append(options);
                                    });
                                 </script>
                                 <?php if(!($_POST[lug_nac]>0)){?>
                                 <script>
                                    $('#pais_nac').trigger('change');
                                    $('#est_nac').trigger('change');
                                 </script>
                                 <?php }?>
                            </div>
                         </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Lugar donde  Reside</div>
                            <div id="CajaInput">
                                <?php $paises=  FUNCION::lista_bd_sql("select * from ter_pais where pais_eliminado='No'");?>
                                 <div style="float: left;">
                                     <select name="pais_res" id="pais_res" style="width: 80px;">
                                         <?php foreach ($paises as $pais) {?>
                                             <option value="<?php echo $pais->pais_id;?>" <?php echo $_POST[pais_res]==$pais->pais_id?'selected="true"':''?>><?php echo $pais->pais_nombre;?></option>
                                         <?php }?>
                                     </select>

                                     <select name="est_res" id="est_res" style="width: 100px;">
                                         <?php $_estados=  $_POST[est_res]>0?FUNCION::lista_bd_sql("select * from ter_estado where est_pais_id='$_POST[pais_res]'"):null;?>
                                         <?php if($_estados){?>
                                            <?php foreach ($_estados as $est) {?>
                                                <option value="<?php echo $est->est_id;?>" <?php echo $_POST[est_res]==$est->est_id?'selected="true"':''?>><?php echo $est->est_nombre;?></option>
                                            <?php }?>
                                         <?php }?>
                                     </select>
                                     <select name="lug_res" id="lug_res" style="width: 100px;"> 
                                         <?php $_lugares=  $_POST[lug_res]>0?FUNCION::lista_bd_sql("select * from ter_lugar where lug_est_id='$_POST[est_res]'"):null;?>
                                         <?php if($_lugares){?>
                                            <?php foreach ($_lugares as $lug) {?>
                                                <option value="<?php echo $lug->lug_id;?>" <?php echo $_POST[lug_res]==$lug->lug_id?'selected="true"':''?>><?php echo $lug->lug_nombre;?></option>
                                            <?php }?>
                                         <?php }?>
                                     </select>
                                 </div>
                                 <script>
                                     $('#pais_res').change(function (){// mostrar_estados
                                         var pais_id=$(this).val();
                                        $('#est_res').children().remove();
                                        $('#lug_res').children().remove();
                                        var estados=JSON.parse(trim($('#json_estados').text()));
                                        var options='';
                                        for(var i=0;i<estados.length;i++){
                                            var est=estados[i];
                                            if(pais_id===est.est_pais_id){
                                                 options+='<option value="'+est.est_id+'">'+est.est_nombre+'</option>';
                                            }
                                        }
                                        $('#est_res').append(options);
                                        $('#est_res').trigger('change');

                                    });
                                     $('#est_res').change(function (){// mostrar_lugares
                                         var est_id=$(this).val();
//                                                       $('#est_id').children().remove();
                                        $('#lug_res').children().remove();
                                        var lugares=JSON.parse(trim($('#json_lugares').text()));
                                        var options='';
                                        for(var i=0;i<lugares.length;i++){
                                            var lug=lugares[i];
                                            if(est_id===lug.lug_est_id){
                                                 options+='<option value="'+lug.lug_id+'">'+lug.lug_nombre+'</option>';
                                            }
                                        }
                                        $('#lug_res').append(options);
                                    });
                                 </script>
                                 <?php if(!($_POST[lug_res]>0)){?>
                                 <script>
                                    $('#pais_res').trigger('change');
                                    $('#est_res').trigger('change');
                                 </script>
                                 <?php }?>
                            </div>
                         </div>
                        <div id="ContenedorDiv" hidden="">
                            <div class="Etiqueta" >Fecha de Ingreso</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="int_fecha_ingreso" id="int_fecha_ingreso" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                                <span class="flechas1">(DD/MM/AAAA)</span>
                                <!--	<input name="but_fecha_pagos" id="but_fecha_pagos" class="boton_fecha" value="..." type="button"> -->
                            </div>		
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Email</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_email" id="int_email" size="40" value="<?php echo $_POST['int_email']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Telefono</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_telefono" id="int_telefono" size="15" value="<?php echo $_POST['int_telefono']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Celular</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="int_celular" id="int_celular" size="15" value="<?php echo $_POST['int_celular']; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Direccion</div>
                            <div id="CajaInput">
                                <textarea class="area_texto" name="int_direccion" id="int_direccion" cols="31" rows="3"><?php echo $_POST['int_direccion'] ?></textarea>
                            </div>
                        </div>
                        <!--Fin-->
                    </div>

                    <div id="ContenedorDiv">
                        <div id="CajaBotones">
                            <center>
                                <input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario_persona();">
                                <input type="button" class="boton" name="" value="Volver" onclick="cierra();">
                                <input name="oculto1" id="oculto1" readonly="readonly" type="hidden" class="caja_texto" value="1" size="2">									
                            </center>								

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>	
    <div id="hiddenemp">
        <div class="fila_formulario_cabecera">VOLVER A VENTA</div>
        <div>
            <br>	
            <table class="tablaLista" cellpadding="0" cellspacing="0">
                <thead>
                    <tr bgcolor="#DEDEDE">
                        <th >
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>

                <center><h1>PERSONA AGREGADA CORRECTAMENTE</h1></center>

                </td>
                </tr>
                </tbody>
            </table>
            <br><center>
                <form id="form_eliminacion" action="<?php echo $red; ?>" method="POST" enctype="multipart/form-data">
                    <input type="submit" value="Volver" class="boton">
                </form>
            </center>
        </div>			
    </div>			
    <?
}

function nombre_persona($usuario) {
    $query = new QUERY();

    $sql = "select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";

    $query->consulta($sql);

    list($int_nombre, $int_apellido) = $query->valores_fila();

    return $int_nombre . ' ' . $int_apellido;
}

function subir_imagen(&$nombre_imagen, $name, $tmp) {
    require_once('../clases/upload.class.php');

    $nn = date('d_m_Y_H_i_s_') . rand();

    $upload_class = new Upload_Files();

    $upload_class->temp_file_name = trim($tmp);

    $upload_class->file_name = strtolower($nn . substr(trim($name), -4, 4));

    $nombre_imagen = $upload_class->file_name;

    $upload_class->upload_dir = "../imagenes/persona/";

    $upload_class->upload_log_dir = "../imagenes/persona/upload_logs/";

    $upload_class->max_file_size = 1048576;

    $upload_class->ext_array = array(".jpg", ".gif", ".png");

    $upload_class->crear_thumbnail = false;

    $valid_ext = $upload_class->validate_extension();

    $valid_size = $upload_class->validate_size();

    $valid_user = $upload_class->validate_user();

    $max_size = $upload_class->get_max_size();

    $file_size = $upload_class->get_file_size();

    $file_exists = $upload_class->existing_file();

    if (!$valid_ext) {

        $result = "La Extension de este Archivo es invalida, Intente nuevamente por favor!";
    } elseif (!$valid_size) {

        $result = "El Tamaño de este archivo es invalido, El maximo tamaño permitido es: $max_size y su archivo pesa: $file_size";
    } elseif ($file_exists) {

        $result = "El Archivo Existe en el Servidor, Intente nuevamente por favor.";
    } else {
        $upload_file = $upload_class->upload_file_with_validation();

        if (!$upload_file) {

            $result = "Su archivo no se subio correctamente al Servidor.";
        } else {
            $result = "";

            require_once('../clases/class.upload.php');

            $mifile = '../imagenes/persona/' . $upload_class->file_name;

            $handle = new upload($mifile);

            if ($handle->uploaded) {
                $handle->image_resize = true;

                $handle->image_ratio = true;

                $handle->image_y = 50;

                $handle->image_x = 50;

                $handle->process('../imagenes/persona/chica/');

                if (!($handle->processed)) {
                    echo 'error : ' . $handle->error;
                }
            }
        }
    }

    return $result;
}
?>

