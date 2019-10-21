<?php
ini_set('display_errors', 'On');
require_once('clases/mlm.class.php');
require_once('clases/cartera.class.php');

class REPVENDEDOR_RAMIFICACION extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $tabla;
    var $arr_estados;
    var $arra_rangos;

    function REPVENDEDOR_RAMIFICACION() {
        //permisos
        $this->ele_id = 407;

        $this->busqueda();

        //fin permisos

        $this->coneccion = new ADO();

        $this->link = 'gestor.php';

        $this->modulo = 'repvendedor_ramificacion';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('VENDEDORES RAMIFICACI&Oacute;N');
        $this->tabla = "vendedor";

        if ($_GET[tabla] == 'vendedor_tmp') {
            $this->tabla = "vendedor_tmp";
        }
    }

    function dibujar_busqueda() {
        $this->formulario();
    }

    function formulario() {


        if (!($_POST['formu'] == 'ok')) {
            $this->formulario->dibujar_cabecera();
            ?>

            <?php
            if ($this->mensaje <> "") {
                ?>
                <table width="100%" cellpadding="0" cellspacing="1" style="border:1px solid #DD3C10; color:#DD3C10;">
                    <tr bgcolor="#FFEBE8">
                        <td align="center">
                            <?php
                            echo $this->mensaje;
                            ?>
                        </td>
                    </tr>
                </table>
                <?php
            }
            ?>
            <!--AutoSuggest-->
            <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
            <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
            <!--AutoSuggest-->
            <script src="js/chosen.jquery.min.js"></script>
            <link href="css/chosen.min.css" rel="stylesheet"/>
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=<?php echo $this->modulo ?>" method="POST" enctype="multipart/form-data">
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">

                            <!--Inicio-->
                            <!--<div id="ContenedorDiv">
                                <div class="Etiqueta" >Nombre Vendedor</div>
                                <div id="CajaInput">

                                    <input name="interno" id="interno" readonly type="hidden" class="caja_texto" value="<?php echo $_POST['interno'] ?>" size="2">
                                    <input name="int_nombre_persona" <? if ($_GET['change'] == "ok") { ?>readonly="readonly" <? } ?> id="int_nombre_persona"  type="text" class="caja_texto" value="<?php echo $_POST['int_nombre_persona'] ?>" size="40">
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onClick="reset_interno();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                </div>
                            </div>-->
                            <!--Fin-->

                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta"><span class="flechas1">*</span>Afiliado</div>
                                <div id="CajaInput">
                                    <select style="width:350px;" name="interno" id="interno" data-placeholder="-- Seleccione --" class="caja_texto">
                                        <option value=""></option>
                                        <?php $sql = "select vdo_id as id,concat(int_nombre,' ',int_apellido,' ( ',vdo_venta_inicial,' )') as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               inner join venta on (vdo_venta_inicial=ven_id)
                                               where (vdo_estado='Habilitado'
                                               and vgru_nombre='AFILIADOS'
                                               and ven_multinivel='si'
                                               and ven_estado in ('Pendiente','Pagado','Retenido')
											   )
                                               
                                               union
                                               
                                               select vdo_id as id,concat(int_nombre,' ',int_apellido,' ( ',vdo_venta_inicial,' )') as nombre
                                               from vendedor 
                                               inner join interno on (vdo_int_id=int_id) 
                                               inner join vendedor_grupo on (vdo_vgru_id=vgru_id)
                                               
                                                where vdo_cod_legado=1
                                                "; ?>
                                        <?php $vendedores1 = FUNCIONES::objetos_bd_sql($sql); ?>
                                        <?php for ($i = 0; $i < $vendedores1->get_num_registros(); $i++) { ?>
                                            <?php $objeto = $vendedores1->get_objeto(); ?>
                                            <option value="<?php echo $objeto->id; ?>"><?php echo $objeto->nombre ?></option>
                                            <?php $vendedores1->siguiente(); ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->                        

                            <!--Inicio -->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta">Nivel</div>
                                <div id="CajaInput">
                                    <input type="text" onKeyPress="return ValidarNumero(event);" name="nivel" id="nivel" value="15">
                                </div>
                            </div>
                            <!--Fin-->

                            <?php if ($_SESSION[id] == 'admin') { ?>
                                <!--Inicio -->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta">vendedor_tmp</div>
                                    <div id="CajaInput">
                                        <input type="checkbox" id="tabla" name="tabla" />
                                    </div>
                                </div>
                                <!--Fin-->
                            <?php } ?>
                        </div>

                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="hidden" class="boton" name="formu" value="ok">
                                    <input type="submit" class="boton" name="" value="Mostrar Red">
                                </center>
                            </div>
                        </div>
                    </div>
                </form>
                <div>
                    <script>
                                        $('#nivel').change(function() {
                                            var value = $(this).val();
                                            $(this).val(parseInt(value));
                                        });

                                        $('#interno').chosen({
                                            allow_single_deselect: true
                                        });
                                        function ValidarNumero(e) {
                                            evt = e ? e : event;
                                            tcl = (window.Event) ? evt.which : evt.keyCode;
                                            if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46)) {
                                                return false;
                                            }
                                            return true;
                                        }
                                        //                                        var options1 = {
                                        //                                            script: "sueltos/suggest_persona_vendedor.php?json=true&",
                                        //                                            varname: "input",
                                        //                                            minchars: 1,
                                        //                                            timeout: 10000,
                                        //                                            noresults: "No se encontro ninguna persona, vendedor",
                                        //                                            json: true,
                                        //                                            callback: function(obj) {
                                        //                                                document.getElementById('interno').value = obj.id;
                                        //                                            }
                                        //                                        };
                                        //                                        var as_json1 = new _bsn.AutoSuggest('int_nombre_persona', options1);

                    </script>
                    <?php
                }

                if ($_POST['formu'] == 'ok')
                    $this->mostrar_reporte();
            }

            function limpiar_cadena($cadena) {
                $cadena = str_replace('Á', 'A', $cadena);
                $cadena = str_replace('á', 'a', $cadena);
                $cadena = str_replace('É', 'E', $cadena);
                $cadena = str_replace('é', 'e', $cadena);
                $cadena = str_replace('Í', 'I', $cadena);
                $cadena = str_replace('í', 'i;', $cadena);
                $cadena = str_replace('Ó', 'O;', $cadena);
                $cadena = str_replace('ó', 'o', $cadena);
                $cadena = str_replace('Ú', 'U', $cadena);
                $cadena = str_replace('ú', 'u', $cadena);
                $cadena = str_replace('Ñ', 'N', $cadena);
                $cadena = str_replace('ñ', 'n;', $cadena);
                $cadena = str_replace('º', '', $cadena);
                $cadena = str_replace('±', 'n', $cadena);
                return $cadena;
            }

            private function get_comisiones($interno_id, $vendedor_id) {
                $sql_comision = "select sum(com_porcentaje)as com_porcentaje from comision
					inner join venta on (ven_id=com_ven_id)
					where ven_int_id='$interno_id' and com_vdo_id ='$vendedor_id' and com_estado !='Anulado' group by ven_id";
                $conex = new ADO();
//             	echo $sql_comision . '<br>';
                $conex->ejecutar($sql_comision);

                $registros = $conex->get_num_registros();
                $datos = array();
                for ($i = 0; $i < $registros; $i++) {
                    $comision = $conex->get_objeto();
                    $datos[] = ($comision->com_porcentaje * 1);
                    $conex->siguiente();
                }

                return implode('%, ', $datos);
            }

            function rango_actual($vdo_id = 0) {
//                include_once 'clases/mlm.class.php';
//                return "tooo";
                if ($vdo_id == 0) {
                    return "";
                }

                if ($this->tabla == 'vendedor_tmp') {
                    $campo_rango = "vdo_rango_actual";
                } else {
                    $campo_rango = "vdo_rango_alcanzado";
                }

                $rango = FUNCIONES::atributo_bd_sql("select ran_nombre as campo from rango 
                    inner join {$this->tabla} on(ran_id=$campo_rango) 
                    where vdo_id=$vdo_id");
                return $rango;
            }

            function tree_vendedores($padre_id, $nivel_limite = 0, $nivel = 0) {

                if ($padre_id > 0) {

                    $sql_vdo = "select int_nombre, int_apellido, int_id,vdo.*,
                        ven.*
                        from {$this->tabla} vdo 
                        inner join interno on(vdo.vdo_int_id=int_id)
                        inner join vendedor_grupo on(vdo.vdo_vgru_id=vgru_id)
                        inner join venta ven on (vdo.vdo_venta_inicial=ven.ven_id)
                        where vdo.vdo_id='$padre_id'                         
                        and vdo.vdo_estado='Habilitado' 
                        and vgru_nombre='AFILIADOS'
                        and ven.ven_multinivel='si'
                        and ven.ven_estado in ('Pendiente','Pagado')
                                
                        union
                                               
                        select int_nombre,int_apellido,int_id,vdo2.*,ven2.*
                        from {$this->tabla} vdo2
                        inner join interno on (vdo2.vdo_int_id=int_id) 
                        inner join vendedor_grupo on (vdo2.vdo_vgru_id=vgru_id)
                        inner join venta ven2 on (vdo2.vdo_venta_inicial=ven2.ven_id or vdo2.vdo_venta_inicial=0)
                        where vdo_cod_legado=1";

                        $sql_vdo = "select int_nombre,int_apellido,int_id,vdo.*
                            from {$this->tabla} vdo
                            inner join interno on(vdo.vdo_int_id=int_id)                        
                        inner join venta ven on (vdo.vdo_venta_inicial=ven.ven_id and ven.ven_estado in ('Pendiente','Pagado','Retenido'))
                        where (vdo.vdo_id='$padre_id'                         
                        and vdo.vdo_estado='Habilitado')                         
                        union
                        select int_nombre,int_apellido,int_id,vdo2.*
                        from {$this->tabla} vdo2
                        inner join interno on (vdo2.vdo_int_id=int_id)                                                 
                        where vdo2.vdo_cod_legado=1";
//                    echo "<p>$sql_vdo</p>";
                    $vendedor = FUNCIONES::objeto_bd_sql($sql_vdo);
                    // $nombre=  explode(' ', $vendedor->int_nombre);
                    // $apellido=  explode(' ', $vendedor->int_apellido);

                    $nombre = $vendedor->int_nombre;
                    $apellido = $vendedor->int_apellido;
//                    $nombre .= " " . $apellido;
//                     $porcentaje= FUNCIONES::atributo_bd_sql("select com_porcentaje as campo from comision where com_ven_id='$vendedor->vdo_venta_inicial' and com_vdo_id='$_POST[vdo_analisis]'");
//                    $porcentaje = $this->get_comisiones($vendedor->int_id, $_POST[vdo_analisis]);
//                    $txt_porc = '';
//                    if ($porcentaje) {
//                        $txt_porc = " ($porcentaje%)";
//                    }
                    // $name= $this->limpiar_cadena("($vendedor->vdo_codigo-[$vendedor->vdo_nivel])".$nombre[0]).' '.$this->limpiar_cadena($apellido[0]) . $txt_porc;
//                    $name = $this->limpiar_cadena("({$this->rango_actual($vendedor->vdo_id)}-[$vendedor->vdo_nivel-$vendedor->vdo_id])" . $nombre) . ' ' . $this->limpiar_cadena($apellido) . $txt_porc;

                    /*
                      if ($this->tabla == 'vendedor_tmp') {
                      if ($vendedor->vdo_estado == 'Deshabilitado') {
                      $desc_aldia = "(*****)";
                      }
                      } else {
                      $hoy = date("Y-m-d");
                      MLM::cargar_rangos_eternos();
                      if (!MLM::esta_al_dia($vendedor, $hoy)) {
                      $desc_aldia = "(*****)";
                      }
                      }
                     */

//                    $nombre = $desc_aldia . "($vendedor->vdo_id-$vendedor->vdo_venta_inicial-$vendedor->vdo_nivel)[{$this->rango_actual($vendedor->vdo_id)}]" . $nombre . " " . $apellido . "$desc_aldia";
                    $nombre = $nombre . " " . $apellido . "$desc_aldia";
                    $name = $this->limpiar_cadena($nombre);
                } else {
                    $vendedor = null;
                    $name = 'Raiz';
                }
                $tree = new stdClass();
                $tree->name = $name; //utf8_encode($name);
                $tree->id = $vendedor->vdo_venta_inicial;
//                $tree->image = ($this->arr_estados[$vendedor->vdo_venta_inicial] != NULL)?$this->arr_estados[$vendedor->vdo_venta_inicial]:'asociado';

                $rango = $this->arr_rangos[$vendedor->vdo_id];
                $estado_cartera = ($this->arr_estados[$vendedor->vdo_venta_inicial] != NULL) ? $this->arr_estados[$vendedor->vdo_venta_inicial] : 'asociado';
                $image = $rango;
                $tree->image = $image;

                $tree->info = array(
                    'vdo_id' => $vendedor->vdo_id,
                    'ven_id' => $vendedor->vdo_venta_inicial,
                    'nombre' => '',
                    'rango' => $rango,
                    'estado_cartera' => $estado_cartera,
                );
//                $tree->info = $this->arr_rangos[$vendedor->vdo_id];
//                $tree->name = $name;
//                $tree->name = "";
//                $ob_json="{";
//                $ob_json.="\"name\":\"$name\",";
                $hijos = FUNCIONES::objetos_bd_sql("select * from {$this->tabla} 
                    inner join vendedor_grupo on (vdo_vgru_id=vgru_id)                  
                    inner join venta on (vdo_venta_inicial=ven_id)
                    where vdo_vendedor_id='$padre_id' 
                    and vdo_estado='Habilitado'
                    and vgru_nombre='AFILIADOS'
                    and ven_multinivel='si'
                    and ven_estado in ('Pendiente','Pagado','Retenido')");

                if ($hijos->get_num_registros() > 0) {

//                    $ob_json.='"children":[';
                    $children = array();
                    for ($i = 0; $i < $hijos->get_num_registros(); $i++) {
                        $_vendedor = $hijos->get_objeto();

                        if ($nivel_limite == 0 || $nivel < $nivel_limite) {
                            $children[] = $this->tree_vendedores($_vendedor->vdo_id, $nivel_limite, $nivel + 1);
                        }
                        $hijos->siguiente();
                    }
                    $tree->children = $children;
//                    $ob_json.=']}';
                } else {

                    $tree->size = 0;
                }
                return $tree;
            }

            function altura($nodo) {
                if (count($nodo->children) == 0) {
                    return 1;
                } else {
                    $hm = 0;
                    for ($i = 0; $i < count($nodo->children); $i++) {
                        $h = $this->altura($nodo->children[$i]) + 1;
                        if ($h > $hm) {
                            $hm = $h;
                        }
                    }
                    return $hm;
                }
            }

            function ancho_modelo2($tree) {
//                echo "<pre>";
//                print_r($tree);
//                echo "</pre>";
                $sum_ancho = 0;
                $childrens = $tree->children;
                $num = count($childrens);
                if ($num > 0) {
                    for ($i = 0; $i < $num; $i++) {
                        $nodo = $childrens[$i];
                        $sum_ancho+=$this->ancho_modelo2($nodo);
                    }
                    return $sum_ancho;
                } else {
                    $sum_ancho = 1;
                    return $sum_ancho;
                }
            }

            function ancho($tree) {
                $alturaNodos = $this->altura($tree);
                $hijos_max = 0;
                for ($i = 0; $i < $alturaNodos; $i++) {
                    $hijosnivel = $this->obtenerHijosNivel($tree, 0, $i);

                    if ($hijosnivel > $hijos_max) {
                        $hijos_max = $hijosnivel;
                    }
                }
                return $hijos_max;
            }

            function obtenerHijosNivel($nodos, $na, $nc) {
                if ($na === $nc)
                    return 1;
                else {
                    $contador = 0;
                    for ($i = 0; $i < count($nodos->children); $i++) {
                        $contador += $this->obtenerHijosNivel($nodos->children[$i], $na + 1, $nc);
                    }
                    return $contador;
                }
            }

            function evaluar_ventas($red) {
//                $nivel = 1000;
//
//                $inc = (($_POST[nivel] * 1) > 0) ? ($_POST[nivel] * 1) : 1000;
//
//                $profundidad = $vendedor->vdo_nivel + $inc;
//                $red = MLM::obtener_red($vendedor->vdo_id, $nivel, TRUE, $profundidad);

                $arr_estados = array();

                if (count($red) > 0) {
                    $s_vdo_ids = implode(',', $red);
                    $sql_ventas = "select ven_id from venta 
                        inner join vendedor on(ven_id=vdo_venta_inicial)
                        where vdo_id in ($s_vdo_ids)";

//                    FUNCIONES::eco("SQL_VENTAS: $sql_ventas");
                    $ventas = FUNCIONES::lista_bd_sql($sql_ventas);
                    $arr_ventas = array();

                    foreach ($ventas as $ven) {
                        $arr_ventas[] = $ven->ven_id;
                    }
                    $hoy = date("Y-m-d");
                    CARTERA::ingresar_ventas($arr_ventas, $hoy);
                    $arr_estados = CARTERA::obtener_estados();
                }

                return $arr_estados;
            }

            function mostrar_reporte() {
                if (isset($_POST[tabla])) {
                    $this->tabla = "vendedor_tmp";
                }

                if ($_POST[interno]) {
                    $vendedor_id = $_POST[interno];
                } else {
                    $vendedor_id = FUNCIONES::atributo_bd_sql("select vdo_id as campo from {$this->tabla}
                    where vdo_cod_legado = 1")*1;
                }
                $_POST[vdo_analisis] = $_POST[interno];


                $obj_vendedor = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id=$vendedor_id");
                $nivel = 1000;
                $inc = (($_POST[nivel] * 1) > 0) ? ($_POST[nivel] * 1) : 1000;
                $profundidad = $obj_vendedor->vdo_nivel + $inc;
                $red = MLM::obtener_red($obj_vendedor->vdo_id, $nivel, TRUE, $profundidad);


                $this->arr_estados = $this->evaluar_ventas($red);
                $this->arr_rangos = MLM::obtener_rangos($red, 'vdo_rango_actual');

//                print_r($this->arr_estados);
//                print_r($this->arr_rangos);

                $tree_vendedores = $this->tree_vendedores($vendedor_id, $_POST[nivel] * 1);

//                $altura = $this->altura($tree_vendedores);
//                $ancho = $this->ancho($tree_vendedores);
                $ancho = $this->ancho_modelo2($tree_vendedores);
//                echo "altura : $altura -- ancho : $ancho <br>";
//                $ancho_nodo = 300;
//                $altura = $altura * $ancho_nodo;
                $ancho = $ancho * 33;

//                echo "altura : $altura -- ancho : $ancho<br>";
//                echo '<pre>';
//                print_r($tree_vendedores);
//                echo '</pre>';
//                return;
                $json_vendedores = "[" . json_encode($tree_vendedores) . "]";
//                echo $json_vendedores ;

                $this->barra_opciones();
                ?>
                <style>
                    /* ESTADOS DE CARTERA */
                    .tree_mlm .box_node .img_node_asociado{
                        background-image: url("css/images_mlm/person.png"); cursor: pointer;
                    }

                    .tree_mlm .box_node .img_node_estrella{
                        background-image: url("css/images_mlm/estrella.png"); cursor: pointer;
                    }

                    .tree_mlm .box_node .img_node_perla{
                        background-image: url("css/images_mlm/perla.png"); cursor: pointer;
                    }

                    .tree_mlm .box_node .img_node_rubi{
                        background-image: url("css/images_mlm/rubi.png"); cursor: pointer;
                    }

                    .tree_mlm .box_node .img_node_esmeralda{
                        background-image: url("css/images_mlm/esmeralda.png"); cursor: pointer;
                    }

                    .tree_mlm .box_node .img_node_diamante{
                        background-image: url("css/images_mlm/diamante.png"); cursor: pointer;
                    }

                    /* ESTADOS DE CARTERA */
                    .tree_mlm .box_node .img_node_mora{
                        background-image: url("css/images_mlm/mora.png"); cursor: pointer;
                    }

                    .tree_mlm .box_node .img_node_ci_pagada{
                        background-image: url("css/images_mlm/ci_pagada.png"); cursor: pointer;
                    }

                    .tree_mlm .box_node .img_node_cm_pagada{
                        background-image: url("css/images_mlm/cm_pagada.png"); cursor: pointer;
                    }

                    .tree_mlm .box_node .img_node_cm_vencer{
                        background-image: url("css/images_mlm/cm_vencer.png"); cursor: pointer;
                    }

                    .tree_mlm .box_node .img_node_cm_vencida{
                        background-image: url("css/images_mlm/cm_vencida.png"); cursor: pointer;
                    }

                    .tree_mlm .box_node .img_node_reversion{
                        background-image: url("css/images_mlm/reversion.png"); cursor: pointer;
                    }

                    .burbuja{
                        background-color: #efefef;
                        border-color: #cccccc;
                        border-radius: 15px;
                        border-style: solid;
                        border-width: 2px;
                        color: #333333;
                        font-family: "Arial";
                        font-size: 12px;
                        overflow: visible;
                        padding: 5px;
                        text-decoration: none;
                        /*width: 300px;*/
                    }

                    .leyendas{
                        background-color: #ffffff;
                        border-color: #999999;
                        border-radius: 10px;
                        border-style: solid;
                        border-width: 1px;
                        color: #000000;
                        font-family: "Raleway",Helvetica,sans-serif;
                        font-size: 10px;
                        font-style: normal;
                        font-weight: normal;
                        padding: 5px;
                        transition: all 0.5s ease 0s;
                    }
                    .box_contenedor{
                        background-color: #f6f6f6; 
                    }
                    .box_marco_info{
                        float: left; text-align: left; position: absolute;
                        background-color: #f6f6f6; 
                    }

                    .box_marco_tree{
                        float: left; background-color: #fff; 
                    }
                    #str_buscar{
                        width: 200px; height: 25px; float: left;
                        /*margin: 5px 0 0 5px;*/
                    }
                    #btn_buscar_af img{
                        height: 25px;
                    }
                    #btn_buscar_af:hover{
                        opacity: 0.8;
                    }
                    #btn_buscar_af{
                        float: left; padding: 0px; 
                    }

                    .box_barra_left{
                        float: left; margin: 5px 0 0 5px ;
                    }
                    .box_barra_right{
                        float: right; margin: 5px 5px 0 0 ;
                    }
                    .box_barra_right img{
                        height: 25px;
                    }
                    .box_barra_right a:hover{
                        background-color: #c7c7c7;
                    }
                    .box_barra_right a{
                        background-color: #dbdbdb; display: block;
                    }
                    .box_imagen{
                        width: 100px; margin: 10px auto;
                    }                    
                    .box_imagen img{
                        width: 100px;
                    }
                    .box_tabla_info{
                        margin-bottom: 10px;
                    }
                    .tabla_info{
                        border-collapse: collapse; width: 100%; font-family: arial; font-size: 13px;
                    }
                    .tabla_info tr td{
                        padding: 2px 5px;
                    }
                    .tabla_info tr:nth-child(odd) {
                        background-color:#dadada;
                    }

                    #div_leyenda_rangos img{ width: 15px;}


                </style>
                <link href="css/tree_mlm.css" rel="stylesheet" type="text/css">
                <script src="js/tree_mlm.js"></script>
                <script src="js/jquery.base64.js"></script>
                <script src="js/util.js"></script>

                <script type="text/javascript" src="js/jquery-ui.min.js"></script>
                <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css" media="screen" charset="utf-8" />

                <?php
                $ancho_marco = 450;
                $ancho_total = $ancho + $ancho_marco;
                ?>
                <div style="height: 22px;">&nbsp;</div>
                <div class="box_contenedor" style="width: <?php echo "{$ancho_total}px"; ?>;">
                    <div class="box_marco_info" style="width: <?php echo "{$ancho_marco}px"; ?>;">
                        <input type="hidden" id="ancho_marco" value="<?php echo $ancho_marco; ?>">
                        <input type="hidden" id="ancho_total" value="<?php echo $ancho_total; ?>">
                        <div class="box_barra_left">
                            <input type="hidden" id="af_id" value="">
                            <input placeholder="Buscar Afiliado" type="text" id="str_buscar">
                            <a hidden="" id="btn_buscar_af" href="javascript:void(0);" ><img  src="images/buscar_afiliado.png"></a>
                        </div>
                        <div class="box_barra_right">
                            <a id="btn_mostrar_tree" href="javascript:void(0);" ><img  src="images/tree_afiliado.png"></a>
                        </div>
                        <div style="clear: both;"></div>
                        <div class="box_imagen">
                            <img src="images/no_foto.png">
                        </div>
                        <div class="box_tabla_info">
                            <table class="tabla_info" id="div_tabla_info">

                            </table>
                        </div>

                        <div id="div_leyenda_cartera" style="clear: both">
                            <table class="ImpresionconMarcoDocumentos leyendas" cellspacing="2" cellpadding="2" align="LEFT" border="0"> 
                                <tbody>
                                    <tr>	
                                        <td class="TitulosBrowse" nowrap="" align="LEFT">Color</td>
                                        <td class="TitulosBrowse" nowrap="" align="LEFT">Descripcion de estado</td>	
                                    </tr>
                                    <tr>
                                        <td bgcolor="#088A08" align="LEFT">Verde</td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Cuota Inicial Pagada</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#045FB4" align="LEFT">Azul</td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Cuota Mensual Pagada</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#FFFF00" align="LEFT">Amarillo</td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Cuota Mensual Por Vencer</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#5C3520" align="LEFT">Café</td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Cuota Mensual Vencida (+ DE 30 DIAS)</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#F20707" align="LEFT">Rojo</td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Asociado en Mora (+ DE 60 DIAS)</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#000000" align="LEFT">Negro</td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Proceso de Reversion (+ DE 90 DIAS)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div id="div_leyenda_rangos" style="clear: both" hidden="">
                            <table class="ImpresionconMarcoDocumentos leyendas" cellspacing="2" cellpadding="2" align="LEFT" border="0"> 
                                <tbody>
                                    <tr>	
                                        <td class="TitulosBrowse" nowrap="" align="LEFT">Figura</td>
                                        <td class="TitulosBrowse" nowrap="" align="LEFT">Descripcion de Rango</td>	
                                    </tr>
                                    <tr>
                                        <td bgcolor="#FFFFFF" align="LEFT">
                                            <img src="css/images_mlm/person.png" width="33">
                                        </td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Asociado</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#FFFFFF" align="LEFT">
                                            <img src="css/images_mlm/estrella.png" width="33">
                                        </td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Estrella</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#FFFFFF" align="LEFT">
                                            <img src="css/images_mlm/perla.png" width="33">
                                        </td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Perla</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#FFFFFF" align="LEFT">
                                            <img src="css/images_mlm/rubi.png" width="33">
                                        </td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Rubi</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#FFFFFF" align="LEFT">
                                            <img src="css/images_mlm/esmeralda.png" width="33">
                                        </td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Esmeralda</td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#FFFFFF" align="LEFT">
                                            <img src="css/images_mlm/diamante.png" width="33">
                                        </td>
                                        <td class="TitulosEstadosdeCuentas" nowrap="" align="LEFT">Diamante</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="box_marco_tree" style="margin-left: <?php echo "{$ancho_marco}px"; ?>;width: <?php echo "{$ancho}px"; ?>;">
                        <ul id="tree_mlm" class="tree_mlm">

                        </ul>
                    </div>
                    <div style="clear: both;"></div>
                </div>


                <script>
                    var s_enc = $.base64.encode('shit');
                    var s_dec = $.base64.decode(s_enc);
                    var vista_activa = 'rangos';
                    console.log(s_enc);
                    console.log(s_dec);

                    root = JSON.parse('<?php echo $json_vendedores; ?>');

                    var params = {};
                    params.listado = root;
                    params.show_name = false;
                    graficar_arbol('#tree_mlm', params);

                    function cargar_eventos_tree() {
                        $('.icon_ml').click(function() {
                            if ($(this).hasClass('icon_less')) {// ocultar
                                var ul = $(this).parent().next();
                                $(ul).hide();
                                $(this).removeClass('icon_less');
                                $(this).addClass('icon_more');
                            } else if ($(this).hasClass('icon_more')) {// mostrar
                                var ul = $(this).parent().next();
                                $(ul).show();
                                $(this).removeClass('icon_more');
                                $(this).addClass('icon_less');
                            }
                        });

                        var hide = false;

                        $('.image_node').mouseover(function(e) {
                            hide = false;

                            var box_node = $(this).parent();
                            var item = $(this);
                            var li = $(this).parent().parent();
                            var info = JSON.parse($(li).attr('data-info'));
        //                            console.log(info);
                            var posicion = $(item).position();
                            var desplazamiento = $(item).offset();
                            var ancho_window = $(window).width();
                            var alto_window = $(window).height();
                            var st = $(document).scrollTop();
                            var sl = $(document).scrollLeft();
                            var dw = $(document).width();
                            var dh = $(document).height();
                            console.log('document: ancho=' + dw + ' alto=' + dh);
                            console.log('scroll: top=' + st + ' left=' + sl);
                            console.log('window: ancho=' + ancho_window + ' alto=' + alto_window);
                            console.log('posicion: x=' + posicion.left + ' y=' + posicion.top + ' ; desplazamiento: x=' + desplazamiento.left + ' y=' + desplazamiento.top);
                            var ven_id = info.ven_id;
                            var desc = info.estado_cartera;
                            var div_id = "afil_" + ven_id;

                            var left = 50;
                            var suma_ancho = (ancho_window + sl) - (desplazamiento.left + 400);
                            console.log('suma_ancho => '+suma_ancho);
                            if (suma_ancho < 0) {
                                left = -350;
                                console.log('por menor a 0 left => ' + left);
                            } 
//                            else {
//                                left = 50;
//                                console.log('por mayor a 0 left => ' + left);
//                            }
                            
                            var top = $(li).attr('data-nivel') * 3 * 33;
                            var top_2 = $(li).attr('data-nivel') * 3 * 33;
                            var suma_alto = (alto_window + st) - (top_2 + 400);
                            
                            console.log('suma_alto => '+suma_alto);
                            if (suma_alto < 0) {
                                top = (top_2 - (suma_alto*(-1))) + 22;
                            } else {
                                top = desplazamiento.top;
                            }
                            
                            console.log('top => ' + top);

                            if ($('#' + div_id).length > 0) {
//                                var ancho_div = $('#' + div_id).width();
//                                var off_set_div = $('#' + div_id).offset();
//                                console.log('ancho ' + div_id + ' => ' + ancho_div);
                                $('#' + div_id).css({position: 'absolute', top: top, left: left});
                                $('#' + div_id).show();

                            } else {
                                $.get('ajax_comisiones.php', {peticion: 'info', venta: ven_id, desc_estado: desc}, function(respuesta) {

                                    var dato = JSON.parse(respuesta);
                                    if (dato.response === "ok") {
                                        //                            console.info('OK');
                                        $('.info').hide();
                                        
                                        var s_popup = $.base64.decode(dato.info);
                                        var str_hide = 'display: block;';
                                        if (hide) {
                                            str_hide = 'display: none;';
                                        }
                                        var html = '<div id="' + div_id + 
                                                '" class="info" id="41" style="' + str_hide + 
                                                ' background-color:#D6D6D6; position: absolute; top:' + top + 
                                                'px; left:' + left + 
                                                'px; width:320px; z-index: 99999999;">' + s_popup + 
                                                '</div>';

                                        $(li).append(html);

                                    } else if (dato.response === "error") {
                                        $.prompt(dato.mensaje);
                                        return false;
                                    }
                                });
                            }

                        });

                        $('.image_node').mouseout(function(e) {
                            hide = true;
                            $('.info').hide();
                        });

                        $('.image_node').click(function(e) {
                            var li = $(this).parent().parent();
                            var info = JSON.parse($(li).attr('data-info'));
                            var ven_id = info.ven_id;
                            cargar_informacion(ven_id);
                        });

        //                        $(document).mousemove(function(event){
        //                            console.log("Coordenadas en del ratón en la parte visible del navegador: " + event.clientX + ", " + event.clientY);
        //                            console.log("Coordenadas absolutas del ratón en la página actual: " + event.pageX + ", " + event.pageY);
        //                        });


                    }

                    $('#a_rangos').click(function() {
                        //                        alert('vista rangos');
                        vista_activa = 'rangos';
                        $('#div_leyenda_cartera').hide();
                        $('#div_leyenda_rangos').show();

                        $('.image_node').each(function() {
                            var li = $(this).parent().parent();
                            var info = JSON.parse($(li).attr('data-info'));
                            $(this).attr('class', 'img_node image_node img_node_' + info.rango);
                        });


                    });

                    $('#a_pagos').click(function() {
                        //                        alert('vista pagos');
                        vista_activa = 'pagos';
                        $('#div_leyenda_rangos').hide();
                        $('#div_leyenda_cartera').show();

                        $('.image_node').each(function() {
                            var li = $(this).parent().parent();
                            var info = JSON.parse($(li).attr('data-info'));
                            $(this).attr('class', 'img_node image_node img_node_' + info.estado_cartera);
                        });
                    });

                    $('#a_info_lat').click(function() {
                        var box_marco = $('.box_marco_info');
                        var ancho_total = $('#ancho_total').val();
                        var ancho_marco = $('#ancho_marco').val();

                        if (box_marco.is(":visible")) {
                            box_marco.hide();
                            ancho_total = ancho_total - ancho_marco;
                            $('.box_marco_tree').css({'margin-left': '0'});
                        } else {
                            box_marco.show();
                            $('.box_marco_tree').css({'margin-left': ancho_marco + 'px'});
                        }
                        $('.box_contenedor').css({'width': ancho_total + 'px'});
                    });

                    $('#a_rangos').trigger('click');


                    function cargar_informacion(id) {

                        mostrar_ajax_load();
                        $.post('ajax_comisiones.php', {tarea: 'info_afiliado', id: id}, function(respuesta) {
                            ocultar_ajax_load();
                            var resp = JSON.parse(respuesta);
                            if (resp.imagen === '') {
                                $('.box_imagen img').attr('src', 'images/no_foto.png');
                            } else {
                                $('.box_imagen img').attr('src', 'images/' + resp.imagen);
                            }
                            var infos = resp.infos;

                            $('#div_tabla_info').children().remove();
                            var tr = '';
                            for (var i = 0; i < infos.length; i++) {
                                var label = infos[i].label;
                                var value = infos[i].value;
                                tr += '<tr>';
                                tr += '	<td style="width: 30%">' + label + '</td>';
                                tr += '	<td>' + value + '</td>';
                                tr += '</tr>';
                            }
                            $('#div_tabla_info').append(tr);
                            id_activo = id;

                        })
                    }

                    var id_activo = root[0].id;

                    cargar_informacion(root[0].id);

                    $('.image_node').click(function(e) {
                        var li = $(this).parent().parent();
                        var info = JSON.parse($(li).attr('data-info'));
                        var ven_id = info.ven_id;
                        cargar_informacion(ven_id);
                    });
                    function buscar_afiliados(lista, term, nro_resp) {

                        var result = [];
                        if (nro_resp === 10) {
                            return result;
                        }
                        for (var i = 0; i < lista.length; i++) {
                            var obj = lista[i];
                            var _value = obj.name.toLowerCase();
                            if (_value.search(term) >= 0) {
                                var res = {id: obj.id, value: obj.name, info: obj.image + '-' + obj.id};
                                result.push(res);
                                nro_resp++;
                            } else {
                                var _id = obj.id.toLowerCase();
                                if (_id == term) {
                                    var res = {id: obj.id, value: obj.name, info: obj.image + '-' + obj.id};
                                    result.push(res);
                                    nro_resp++;
                                }
                            }
                            if (obj.children !== undefined && obj.children.length) {
                                result = result.concat(buscar_afiliados(obj.children, term, nro_resp));
                            }
                        }
                        return result;
                    }

                    $("#str_buscar").autocomplete({
                        minLength: 0,
                        source: function(request, response) {
                            var results = [];
                            var term = request.term.toLowerCase();
                            var nro_resp = 0;
                            results = buscar_afiliados(root, term, 0);
                            response(results.slice(0, 10));
                        },
                        focus: function() {
                            return false;
                        },
                        select: function(event, ui) {
                            seleccionar_afiliado({id: ui.item.id, value: ui.item.value, info: ui.item.info});
                            $("#str_buscar").val('');
                            return false;
                        }
                    }).autocomplete("instance")._renderItem = function(ul, item) {
                        var info = "";
                        if (item.info) {
                            info = "<br><span style='font-size:11px'>" + item.info + "</span></a>";
                        }
                        return $("<li>")
                                .append("<a><b>" + item.label + "</b>" + info)
                                .appendTo(ul);
                    };

                    function seleccionar_afiliado(infos) {
                        $('#af_id').val(infos.id);
                        $('#str_buscar').val(infos.value);
                        cargar_informacion(infos.id);

                    }
                    $('#btn_buscar_af').click(function() {
                        var af_id = $('#af_id').val();
                        cargar_informacion(af_id);
                    });
                    function buscar_nodo(lista, id) {
                        var result = [];
                        for (var i = 0; i < lista.length; i++) {
                            var nodo = lista[i];
                            if (nodo.id == id) {
                                result.push(nodo);
                                return result;
                            } else {
                                if (nodo.children !== undefined && nodo.children.length) {
                                    result = buscar_nodo(nodo.children, id);
                                    if (result.length > 0) {
                                        return result;
                                    }
                                }

                            }
                        }
                        return result;
                    }
                    $('#btn_mostrar_tree').click(function() {
                        console.log(id_activo);
                        var ntree = buscar_nodo(root, id_activo);
                        var params = {};
                        params.listado = ntree;
                        params.show_name = false;
                        $('#tree_mlm').children().remove();
                        graficar_arbol('#tree_mlm', params);
                        cargar_eventos_tree();
                        
                        if (vista_activa === 'rangos') {
                            $('#a_rangos').trigger('click');
                        } else {
                            $('#a_pagos').trigger('click');
                        }
                    });
                    cargar_eventos_tree();
                </script>
                <?php
            }

            function json_vendedores($padre_id) {

                if ($padre_id > 0) {
//                    echo "select int_nombre, int_apellido, vendedor.* from vendedor,interno where vdo_id='$padre_id' and vdo_int_id=int_id";
                    $vendedor = FUNCIONES::objeto_bd_sql("select int_nombre, int_apellido, vdo.* from {$this->tabla} vdo,interno 
                        where vdo.vdo_id='$padre_id' and vdo.vdo_int_id=int_id");
                    $name = "$vendedor->int_nombre $vendedor->int_apellido";
                } else {
                    $vendedor = null;
                    $name = 'Raiz';
                }
                $ob_json = "{";
                $ob_json.="\"name\":\"$name\",";
                $hijos = FUNCIONES::objetos_bd_sql("select * from {$this->tabla} where vdo_vendedor_id='$padre_id'");
                if ($hijos->get_num_registros() > 0) {
                    $ob_json.='"children":[';
                    for ($i = 0; $i < $hijos->get_num_registros(); $i++) {
                        $_vendedor = $hijos->get_objeto();
                        if ($i > 0) {
                            $ob_json.=',';
                        }
                        $ob_json.=$this->json_vendedores($_vendedor->vdo_id);
                        $hijos->siguiente();
                    }
                    $ob_json.=']}';
                } else {
                    $ob_json.='"size":0}';
                }
                return $ob_json;
            }

            function obtener_ultimos_datos_interno($interno, &$telefono, &$celular, &$email) {
                $conec = new ADO();

                //Ultimo Telefono de la Persona
                $sql = "select int_telefono from interno where int_id=$interno ";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                $telefono = $objeto->inttelf_telefono;

                //Ultimo Celular de la Persona
                $sql = "select int_celular from interno where int_id=$interno ";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                $celular = $objeto->intcel_celular;

                //Ultimo Email de la Persona
                $sql = "select int_email from interno where int_id=$interno ";

                $conec->ejecutar($sql);

                $objeto = $conec->get_objeto();

                $email = $objeto->intemail_email;
            }

            function obtener_total_pagos_acumulados($ind_id) {
                $sql = "select * from interno_deuda_pagos where idp_ind_id='" . $ind_id . "' and idp_estado='Activo'";

                //echo $sql;
                $conec = new ADO();
                $conec->ejecutar($sql);
                $total = 0;
                $num = $conec->get_num_registros();

                if ($num > 0) {

                    for ($i = 0; $i < $num; $i++) {
                        $obj = $conec->get_objeto();
                        $total += $obj->idp_monto;
                        $conec->siguiente();
                    }
                }

                return $total;
            }

            function barra_opciones() {

                $permisos = "";

                if (TRUE) {
                    $permisos.="<td><a id='a_info_lat' title='CUADRO INFORMACION' href='#'><img width='18' src='images/cuadro_info.png'></a></td>";
                    $permisos.="<td><a id='a_rangos' title='VISTA RANGOS' href='#'><img width='18' src='images/jerarquia.png'></a></td>";
                    $permisos.="<td><a id='a_pagos' title='VISTA PAGOS' href='#'><img width='18' src='images/cash.png'></a></td>";
                }

                echo '  <div id="box_barra_opciones" style="z-index:9999">
                        <table id="barra_opciones" align=right border=0>
                            <tr>
                                ' . $permisos . '
                            </tr>
                        </table>
                    </div>
                            ';
            }

        }
        ?>