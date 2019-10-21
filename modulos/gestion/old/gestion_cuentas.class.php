<?php

//header('Content-Type: text/html; charset=utf-8');

class gestion_cuentas extends GESTION {

    function gestion_cuentas() {

        parent::__construct();
    }

    function adm_cuentas() {

        if (is_numeric($_GET['id'])) {

            if (!isset($_GET['p'])) {

                $_GET['p'] = 'cuenta';
            }



            $conec = new ADO();



            $sql = "select * from  con_gestion where ges_id ='" . $_GET['id'] . "'";



            $conec->ejecutar($sql);



            $objGestion = $conec->get_objeto();



            $cod_codigo = "";

            $treeURL = "";

            $label_id = "";

            $label_codigo = "";

            $label_tree_position = "";

            $label_descripcion = "";

            $label_ges_id = "";

            $label_tipo = "";

            $label_mon_id = "";

            $t_cuenta = "";

            $formato = array();

            switch ($_GET['p']) {

                case "cuenta":



                    /* $cod_codigo = $objGestion->ges_formato_cuenta;

                      $treeURL = "sueltos/tree/con_cuenta.php";

                      $label_id = "cue_id";

                      $label_codigo = "cue_codigo";

                      $label_tree_position = "cue_tree_position";

                      $label_descripcion = "cue_descripcion";

                      $label_ges_id = "cue_ges_id";

                      $label_tipo = "cue_tipo";

                      $label_mon_id = "cue_mon_id";

                      $t_cuenta="cue";

                      $formato= explode(".", $objGestion->ges_formato_cuenta); */

                    break;

                case "cc":



                    $cod_codigo = $objGestion->ges_formato_cc; //ges_formato_cc

                    $treeURL = "sueltos/tree/con_cuenta_cc.php";

                    $label_id = "cco_id";

                    $label_codigo = "cco_codigo";

                    $label_tree_position = "cco_tree_position";

                    $label_descripcion = "cco_descripcion";

                    $label_ges_id = "cco_ges_id";

                    $label_tipo = "cco_tipo";

                    $label_mon_id = "cco_mon_id";

                    $t_cuenta = "cco";

                    $formato = explode(".", $objGestion->ges_formato_cc);

                    break;

                case "ca":



                    $cod_codigo = $objGestion->ges_formato_ca;

                    $treeURL = "sueltos/tree/con_cuenta_ca.php";

                    $label_id = "can_id";

                    $label_codigo = "can_codigo";

                    $label_tree_position = "can_tree_position";

                    $label_descripcion = "can_descripcion";

                    $label_ges_id = "can_ges_id";

                    $label_tipo = "can_tipo";

                    $label_mon_id = "can_mon_id";

                    $t_cuenta = "can";

                    $formato = explode(".", $objGestion->ges_formato_ca);

                    break;

                case "cf":



                    $cod_codigo = $objGestion->ges_formato_cf; //ges_formato_cc

                    $treeURL = "sueltos/tree/con_cuenta_cf.php";

                    $label_id = "cfl_id";

                    $label_codigo = "cfl_codigo";

                    $label_tree_position = "cfl_tree_position";

                    $label_descripcion = "cfl_descripcion";

                    $label_ges_id = "cfl_ges_id";

                    $label_tipo = "cfl_tipo";

                    $label_mon_id = "cfl_mon_id";

                    $t_cuenta = "cfl";

                    $formato = explode(".", $objGestion->ges_formato_cf);

                    break;
            }
            ?>

            <!-- Menu treen -->



            <script src="js/jquery-ui3.min.js"></script>             

            <link href = "css/jquery-ui-modal.min.css" rel = "stylesheet">





            <script type="text/javascript" src="js/jquery.hotkeys.js"></script>

            <script type="text/javascript" src="js/jquery.jstree.js"></script>



            <script type="text/javascript" src="js/jquery.maskedinput.min.js"></script>



            <!--AutoSugest-->

            <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>                    

            <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />

            <!--AutoSugest-->



            <script type="text/javascript">

            <?php
            $treeCodigo = str_replace("#", "9", $cod_codigo);
            ?>



                var treeURL = "<?php echo $treeURL; ?>";

                var treeCodigo = "<?php echo $treeCodigo; ?>";

                var treePrimeraVez = false;



                var treeActivo = 0;

                var treeTipo = 0;



                var cue_codigo = "";

                var cue_descripcion = "";



                $(document).ready(function() {



                    $("#cue_codigo").mask(treeCodigo);



                    $("#plancuenta").jstree({
                        "plugins": [
                            "themes", "json_data", "ui", "crrm", "dnd", "search", "types", "hotkeys"

                        ],
                        "json_data": {
                            "ajax": {
                                "url": treeURL,
                                "data": function(n) {

                                    return {
                                        "operation": "get_children",
                                        "<?php echo $label_ges_id; ?>": $("#cue_ges_id").val(),
                                        "<?php echo $label_id; ?>" : n.attr ? n.attr("id").replace("node_", "") : 1

                                    };

                                }

                            }

                        },
                        "search": {
                            "ajax": {
                                "url": treeURL,
                                "data": function(str) {

                                    return {
                                        "operation": "search",
                                        "search_str": str

                                    };

                                }

                            }

                        },
                        "types": {
                            "max_depth": -2,
                            "max_children": -2,
                            "valid_children": ["Root"],
                            "types": {
                                "default": {
                                    "valid_children": "none",
                                    "icon": {
                                        "image": "./js/themes/file.png"

                                    }

                                },
                                "Titulo": {
                                    "valid_children": ["default", "folder"],
                                    "icon": {
                                        "image": "./js/themes/folder.png"

                                    }

                                },
                                "folder": {
                                    "valid_children": ["default", "folder"],
                                    "icon": {
                                        "image": "./js/themes/folder.png"

                                    }

                                },
                                "Root": {
                                    "valid_children": ["default", "folder"],
                                    "icon": {
                                        "image": "./js/themes/root.png"

                                    },
                                    "start_drag": false,
                                    "move_node": false,
                                    "delete_node": false,
                                    "remove": false

                                }

                            }

                        },
                        "ui": {
                            "initially_select": ["node_32"]

                        },
                        "core": {
                            "initially_open": ["node_32", "node_3"]

                        }

                    })

                            .bind("create.jstree", function(e, data) {

                        console.log(data);



                        $.post(
                                treeURL,
                                {
                                    "operation": "create_node",
                                    "<?php echo $label_id; ?>": data.rslt.parent.attr("id").replace("node_", ""),
                                    "<?php echo $label_tree_position; ?>" : data.rslt.position,
                                            "<?php echo $label_descripcion; ?>" : $("#cue_descripcion").val(),
                                            "<?php echo $label_codigo; ?>" : $("#cue_codigo").val(),
                                            "<?php echo $label_ges_id; ?>" : $("#cue_ges_id").val(),
                                            "<?php echo $label_mon_id; ?>" : $("#cue_mon_id option:selected").val().toString(),
                                            "<?php echo $label_tipo; ?>" : $("#cue_tipo option:selected").val().toString()

                                },
                                function(r) {

                                    if (r.status) {

                                        tree_form_borrar();

                                        data.inst.refresh();

                                    }

                                }

                        );



                    })

                            .bind("remove.jstree", function(e, data) {

                        data.rslt.obj.each(function() {

                            $.ajax({
                                async: false,
                                type: 'POST',
                                url: treeURL,
                                data: {
                                    "operation": "remove_node",
                                    "<?php echo $label_ges_id; ?>": $("#cue_ges_id").val(),
                                    "<?php echo $label_id; ?>" : data.rslt.obj.attr("id").replace("node_", "")

                                },
                                success: function(r) {

                                }

                            });

                        });

                    });



                    function tree_form_validar()

                    {

                        cue_codigo = $("#cue_codigo").val();

                        cue_descripcion = $("#cue_descripcion").val();

                        if (cue_codigo != "")

                        {

                            if (cue_descripcion != "")

                            {

                                var cue_tipoCombo = $("#cue_tipo option:selected").val().toString();

                                if (cue_tipoCombo != "")

                                {

                                    return true;

                                } else {

                                    $("#cue_tipo").focus();

                                    return false;

                                }

                            } else {

                                $("#cue_descripcion").focus();

                                return false;

                            }

                        } else {

                            $("#cue_codigo").focus();

                            return false;

                        }

                    }



                    function tree_form_renombrar()

                    {

                        var nodo_sel = $(".jstree-clicked");

                        if (nodo_sel.size() === 0) {

                            $.prompt("Seleccione una cuenta");

                            return false;

                        } else if (nodo_sel.size() > 1) {

                            $.prompt("Seleccione solo una cuenta");

                            return false;

                        }

                        cue_codigo = $("#cue_codigo").val();

                        cue_descripcion = $("#cue_descripcion").val();



                        $.post(
                                treeURL,
                                {
                                    "operation": "rename_node",
                                    "<?php echo $label_id; ?>": treeActivo,
                                    "<?php echo $label_codigo; ?>" : cue_codigo,
                                            "<?php echo $label_ges_id; ?>" : $("#ges_id").val(),
                                            "<?php echo $label_mon_id; ?>" : $("#cue_mon_id option:selected").val().toString(),
                                            "<?php echo $label_descripcion; ?>" : cue_descripcion

                                },
                                function(r) {

                                    if (r.status) {

                                        $('#plancuenta').jstree('refresh', -1);

                                    } else {

                                        $.prompt(r.msj);

                                    }

                                }

                        );

                    }

                    function tree_form_guardar_nuevo() {

                        var nodo_sel = $(".jstree-clicked");

                        if (nodo_sel.size() === 0) {

                            $.prompt("Seleccione una cuenta");

                            return false;

                        } else if (nodo_sel.size() > 1) {

                            $.prompt("Seleccione solo una cuenta");

                            return false;

                        }

                        var rel = nodo_sel.parent().attr('rel');

                        if (rel === 'Movimiento') {

                            $.prompt("No puede agregar sub-cuentas a una cuenta movimiento");

                            return false;

                        }

                        var tipo = $("#cue_tipo option:selected").val();

                        var level = nodo_sel.parent().attr('data-level');

                        var max_level = $("#max_level").val();

                        if (tipo === 'Movimiento' && level < max_level - 1) {

                            $.prompt("No puede agregar una cuenta de <b>movimiento</b> en el nivel " + (level * 1 + 1));

                            return false;

                        }

                        if (tipo === 'Titulo' && level >= max_level - 1) {

                            $.prompt("No puede agregar una cuenta de <b>Titulo</b> en el nivel " + (level * 1 + 1));

                            return false;

                        }



                        var id = nodo_sel.parent().attr("id").replace("node_", "");

                        var position = nodo_sel.parent().children('ul li').size();

                        var level = nodo_sel.parent().attr("level") * 1;

                        var max_level = $('#max_level').val() * 1;

                        var tipo_cuenta = 'Titulo';

                        if (level + 1 === max_level) {

                            tipo_cuenta = 'Movimiento';

                        }



                        $.post(
                                treeURL,
                                {
                                    "operation": "create_node",
                                    "<?php echo $label_id; ?>": id,
                                    "<?php echo $label_tree_position; ?>" : position,
                                            "<?php echo $label_descripcion; ?>" : $("#cue_descripcion").val(),
                                            "<?php echo $label_codigo; ?>" : $("#cue_codigo").val(),
                                            "<?php echo $label_ges_id; ?>" : $("#cue_ges_id").val(),
                                            "<?php echo $label_mon_id; ?>" : $("#cue_mon_id option:selected").val().toString(),
                                            "<?php echo $label_tipo; ?>" : tipo_cuenta

                                },
                                function(r) {

                                    if (r.status) {

                                        tree_form_borrar();

                                        $('#plancuenta').jstree('refresh', -1);

                                    } else {

                                        $.prompt(r.msj);

                                    }

                                }

                        );

                    }



                    function tree_form_borrar() {

                        $("#cue_descripcion").val("");

                    }

                    function tree_form_codigo()

                    {

                        var tCodigo = $("#cue_codigo").val();

                        return tCodigo;

                    }



                    function tree_form_descripcion()

                    {

                        var tDescripcion = $("#cue_codigo").val();

                        return tDescripcion;

                    }

                    function tree_form_valores(tCodigo, tDescripcion)

                    {

                        $("#cue_codigo").val(tCodigo);

                        $("#cue_descripcion").val(tDescripcion);

                    }

                    function trim(str) {

                        return str.replace(/^\s+|\s+$/g, "");

                    }

                    $("#mmenu input").click(function() {

                        switch (this.id) {

                            case "agregar":

                                if (tree_form_validar()) {

                                    tree_form_guardar_nuevo();

                                }

                                break;

                            case "rename":

                                if (tree_form_validar()) {

                                    tree_form_renombrar();

                                }

                                break;

                            default:

                                var cuenta = $(".jstree-clicked");

                                if (cuenta.size() > 1) {

                                    $.prompt("seleccione solo una cuenta para eliminar");

                                    return false;

                                }

                                var texto = $(cuenta[0]).text();

                                var codigo = texto.split('|');

                                var tcuenta = $("#t_cuenta").val();

                                var id_imput = this.id;

                                var gesid = $("#ges_id").val();

                                $.get('AjaxRequest.php', {peticion: 'ver_' + tcuenta, cu: trim(codigo[0]), gesid: gesid}, function(resp) {

                                    if (trim(resp) === 'ok') {

                                        $("#plancuenta").jstree(id_imput);

                                    } else {

                                        $.prompt(resp);

                                    }

                                });

                                return false;

                        }

                    });



                });

                $(document).ready(function() {

                    $("#cue_tipo").change(function() {

                        var tipo = $("#cue_tipo option:selected").val();

                        if (tipo === 'Movimiento' && $("#t_cuenta").val() === 'cue') {

                            $("#sel_moneda").show();

                        } else {

                            $("#sel_moneda").hide();

                        }

                    });

                });



            </script>

            <style>





                .cursor{

                    cursor:pointer;	

                }



                .ocultar{

                    display:none;	

                }



                .centro{

                    text-align:center;

                }



                .fondoCelda{

                    background: rgb(249,252,247); /* Old browsers */

                    background: -moz-linear-gradient(top, rgba(249,252,247,1) 0%, rgba(245,249,240,1) 100%); /* FF3.6+ */

                    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(249,252,247,1)), color-stop(100%,rgba(245,249,240,1))); 

                    background: -webkit-linear-gradient(top, rgba(249,252,247,1) 0%,rgba(245,249,240,1) 100%); 

                    background: -o-linear-gradient(top, rgba(249,252,247,1) 0%,rgba(245,249,240,1) 100%); /* Opera 11.10+ */

                    background: -ms-linear-gradient(top, rgba(249,252,247,1) 0%,rgba(245,249,240,1) 100%); /* IE10+ */

                    background: linear-gradient(to bottom, rgba(249,252,247,1) 0%,rgba(245,249,240,1) 100%); /* W3C */

                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f9fcf7', endColorstr='#f5f9f0',GradientType=0 ); /* IE6-9 */

                }





                #dataAccount tr:hover{

                    /*background: #e9eae8;*/

                    background: #f9e48c;



                    color: #bf5900;

                }



                .cuentaNivel3{

                    font-weight:bold;

                    font-size:8px;

                    text-transform:uppercase;

                }



                .cuentaNivel5{

                    font-size:8px;

                }



                .cabeceraInicialListar{

                    background: -moz-linear-gradient(top, rgba(255,255,255,1) 0%, rgba(229,229,229,1) 100%);

                    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,1))

                        , color-stop(100%,rgba(229,229,229,1)));

                    background: -webkit-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(229,229,229,1) 100%);

                    background: -ms-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(229,229,229,1) 100%);

                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e5e5e5',GradientType=0 );

                    font-family: Arial, Helvetica, sans-serif; 

                    border-bottom:2px solid #666;

                    font-size: 12px; 

                    font-weight: bold;

                    height:50px;	

                    border:1px solid #999;

                }



                .tab_lista_cuentas{

                    list-style: none;                                    

                    width: 100%;                                    

                    overflow:scroll ;

                    background-color: #ededed;

                    border-collapse: collapse;  

                    font-size: 8px;

                }

                .tab_lista_cuentas tr td{

                    padding: 3px 3px;

                }

                .tab_lista_cuentas tr:hover{

                    background-color: #f9e48c;

                }                                

                .img_del_cuenta{                                    

                    font-weight: bold;

                    cursor: pointer;

                    width: 12px;

                }

                .box_lista_cuenta{

                    width:270px;height:170px;background-color:#F2F2F2;overflow:auto;

                    border: 1px solid #8ec2ea;

                }



                .fondoCelda{

                    font-size:12px;	

                }

            </style>

            <script src="modulos/gestion/Ngestion.js?v=1"></script>

            <div class="fila_formulario_cabecera"><?php echo $objGestion->ges_descripcion; ?></div>

            <div class="aTabsCont">

                <div class="aTabsCent" style="width:99%;">

                    <ul class="aTabs">

                        <li><a href="gestor.php?mod=gestion&tarea=ESTRUCTURA&p=cuenta&id=<?php echo $_GET["id"]; ?>" <?php if ($_GET['p'] == "cuenta") { ?>class="activo" <?php } ?>>Plan de Cuentas</a></li>

                        <li><a href="gestor.php?mod=gestion&tarea=ESTRUCTURA&p=cc&id=<?php echo $_GET["id"]; ?>" <?php if ($_GET['p'] == "cc") { ?>class="activo" <?php } ?>>Centros de Costo</a></li>

                        <li><a href="gestor.php?mod=gestion&tarea=ESTRUCTURA&p=ca&id=<?php echo $_GET["id"]; ?>" <?php if ($_GET['p'] == "ca") { ?>class="activo" <?php } ?>>Cuentas Analiticas</a></li>

                        <li><a href="gestor.php?mod=gestion&tarea=ESTRUCTURA&p=cf&id=<?php echo $_GET["id"]; ?>" <?php if ($_GET['p'] == "cf") { ?>class="activo" <?php } ?>>Cuentas de Flujo</a></li>

                        <li><a href="gestor.php?mod=gestion&tarea=ESTRUCTURA&p=cpf&id=<?php echo $_GET["id"]; ?>" <?php if ($_GET['p'] == "cpf") { ?>class="activo" <?php } ?>>Cuentas para Flujo</a></li>

                    </ul>

                </div>

            </div>

            <?php
            $conec = new ADO();

            $sql = "select * from con_gestion where ges_id='$_GET[id]'";

            $conec->ejecutar($sql);

            $objeto = $conec->get_objeto();

            $formatoCodigo = "";

            switch ($_GET["p"]) {

                case "cuenta":

                    $formatoCodigo = $objeto->ges_formato_cuenta;

                    $sqlPlan = "select cue_id as 'idplandecuenta',cue_codigo as 'codigo'

			 ,cue_descripcion as 'cuenta' ,cue_mon_id as 'moneda',cue_tree_level as 'nivel',cue_mon_id as 'idmoneda' 

   	         from con_cuenta where cue_ges_id='" . $_GET['id'] . "' and cue_eliminado='No' order by cue_codigo;";

                    $auxTable = "cue";

                    $tablePlan = "con_cuenta";

                    break;

                case "cc":

                    $formatoCodigo = $objeto->ges_formato_cc;

                    $sqlPlan = "select cco_id as 'idplandecuenta',cco_codigo as 'codigo'

			 ,cco_descripcion as 'cuenta' ,cco_mon_id as 'moneda',cco_tree_level as 'nivel',cco_mon_id as 'idmoneda' 

   	         from con_cuenta_cc where cco_ges_id='" . $_GET['id'] . "' and cco_eliminado='No' order by cco_codigo;";

                    $auxTable = "cco";

                    $tablePlan = "con_cuenta_cc";

                    break;

                case "ca":

                    $formatoCodigo = $objeto->ges_formato_ca;

                    $sqlPlan = "select can_id as 'idplandecuenta',can_codigo as 'codigo'

			 ,can_descripcion as 'cuenta' ,can_mon_id as 'moneda',can_tree_level as 'nivel',can_mon_id as 'idmoneda' 

   	         from con_cuenta_ca where can_ges_id='" . $_GET['id'] . "' and can_eliminado='No' order by can_codigo;";

                    $auxTable = "can";

                    $tablePlan = "con_cuenta_ca";

                    break;

                case "cf":

                    $formatoCodigo = $objeto->ges_formato_cf;

                    $sqlPlan = "select cfl_id as 'idplandecuenta',cfl_codigo as 'codigo'

			 ,cfl_descripcion as 'cuenta' ,cfl_mon_id as 'moneda',cfl_tree_level as 'nivel',cfl_mon_id as 'idmoneda' 

   	         from con_cuenta_cf where cfl_ges_id='" . $_GET['id'] . "' and cfl_eliminado='No' order by cfl_codigo;";

                    $auxTable = "cfl";

                    $tablePlan = "con_cuenta_cf";

                    break;
            }
            ?>    





            <input type="hidden" id="max_level" value="<?php echo count($formato); ?>" />

            <input type="hidden" id="t_cuenta" value="<?php echo $t_cuenta; ?>" />

            <input type="hidden" id="ges_id" value="<?php echo $_GET['id']; ?>" />

            <input type="hidden" id="formatoCodigo" value="<?php echo $formatoCodigo; ?>" />

            <input type="hidden" id="auxtable" value="<?php echo $auxTable; ?>" />

            <input type="hidden" id="tableplan" value="<?php echo $tablePlan; ?>" />

            <input type="hidden" id="idplanpadre" value="" />

            <input type="hidden" id="haspost" />



            <div id="Contenedor_NuevaSentencia">

                <div id="FormSent" style="width:100%;">

                    <div id="ContenedorSeleccion" style="width:99%;"> 





                        <div id="dialog_cuenta" title="Actividad" style="display:none;">

                            <table width="100%" border="0">

                                <tr>

                                    <td align='right' valign='top'>&nbsp;</td>

                                    <td valign='top'>&nbsp;</td>

                                    <td  align='right' valign='top'>&nbsp;</td>

                                    <td valign='top'>&nbsp;</td>

                                </tr>

                                <tr>

                                    <td width="21%" align='right' valign='top'>Código:</td>

                                    <td width="25%" valign='top' id="codePlan"></td>

                                    <td width="18%"  align='right' valign='top'>Moneda:</td>

                                    <td width="36%" valign='top'>

                                        <select name="moneda_pg" id="moneda_pg">

                                            <!--<option value='1'>Bolivianos</option>

                                            <option value='2'>Dolares</option>-->
											
											<?php 
											$fun = new FUNCIONES();
											$fun->combo("select mon_id as id, mon_titulo as nombre from con_moneda order by id asc");
											?>

                                        </select>

                                    </td>

                                </tr>

                                <tr>

                                    <td align='right' valign='top'>Cuenta:</td>

                                    <td valign='top' colspan="3">

                                        <input name="cuenta_pg" type='text'  class="required" st id="cuenta_pg" style="width: 350px;height: auto;padding: 3px;" /></td>

                                </tr>

                            </table>

                        </div>



                        <div id="dialog_cuenta_anular" title="Actividad" style="display:none;">

                            Esta seguro de anular esta cuenta contable ?

                        </div>



            <?php

            function space($nivel) {

                $espacio = "&nbsp;";



                if ($nivel > 1) {

                    $nivel++;
                }

                for ($i = 1; $i < $nivel; $i++)
                    $espacio .= $espacio;

                return $espacio;
            }

            function typeAccount($code) {

                $type = "Movimiento";

                $code = explode(".", $code);

                $level = 0;

                for ($i = 0; $i < count($code); $i++) {

                    if ((int) $code[$i] == 0) {

                        $type = "Titulo";
                    } else {

                        $level++;
                    }
                }

                return array($type, $level);
            }

            if ($_GET["p"] == 'cuenta' || $_GET["p"] == 'cc' || $_GET["p"] == 'ca' || $_GET["p"] == 'cf') {



                $conec = new ADO();

                $conec->ejecutar($sqlPlan);

                $num = $conec->get_num_registros();



                if ($num > 0) {

                    echo "<table border='0' width='100%'  align='center'>";

                    echo "<table width='100%'  border='0' align='center' id='objetotabla'>";

                    echo "<thead><tr class='cabeceraInicialListar'>";

                    echo "<th>&nbsp;</th><th>&nbsp;</th>";

                    echo "<th >Num.</th>";

                    echo "<th width='170px'>CÃ³digo</th>";

                    echo "<th width='60%'>Cuenta</th>";

                    echo "<th >Moneda</th>";

                    echo "<th></th><th>&nbsp;</th></tr></thead>";

                    echo "<tbody id='dataAccount'>";



                    $par = 0;

                    $id_img = 1000;

                    $fila_id = 100;

                    for ($count = 0; $count < $num; $count++) {

                        $objeto = $conec->get_objeto();

                        $fila_id++;

                        $nivel = $objeto->nivel;



                        $atrib_css = "style='display:none;'";

                        if ($nivel == 0 || $nivel == 1) {

                            $atrib_css = "style=''";
                        }

                        // $moneda = "";

                        // switch ($objeto->moneda) {

                            // case "1":

                                // $moneda = "Bolivianos";

                                // break;

                            // case "2":

                                // $moneda = "Dolares";

                                // break;
                        // }
						
						$moneda = FUNCIONES::atributo_bd_sql("select mon_titulo as campo from con_moneda where mon_id='$objeto->moneda'");







                        echo "<tr id='" . $fila_id . "' height='35' class='fondoCelda' $atrib_css>";



                        $par++;

                        $id = $objeto->idplandecuenta;

                        $id_img++;

                        $clase = "fondoCelda";



                        $aux = "";

                        if ($objeto->nivel == 1) {

                            $aux = "href='#" . $fila_id . "'";
                        }

                        $level = typeAccount($objeto->codigo);

                        if (count(explode(".", $formatoCodigo)) == $level[1]) {

                            $option = "";

                            $option2 = "";

                            $textCuenta = space($objeto->nivel) . "<strong>" . $objeto->cuenta . "</strong>";
                        } else {

                            $option = "<img class='cursor' onclick= \"newTransaction(this.parentNode.parentNode.parentNode,'insert'); return false;\"

					  src='images/mas.png' title='Agregar' alt='Agregar' class='cursor' border='0' /> ";

                            $option2 = "<div align='center'><a id='c" . $fila_id . "' $aux "
                                    . " onclick=\"toggle((this.parentNode.parentNode.parentNode),'$id_img');\">

			 <img id='$id_img' class='cursor' src='images/arrowarriba.png' border='0' /></a></div>";

                            $textCuenta = space($objeto->nivel) . $objeto->cuenta;
                        }



                        echo "<td valign='middle'>$option2</td>";



                        echo "<td>

			   <div align='center' >				

					$option 

				</div>

			</td>";



                        echo "<td >" . ($count) . "</td>";

                        echo "<td align='center'>" . $objeto->codigo . "</td>";

                        echo "<td >" . $textCuenta . "</td>";

                        echo "<td align='center'>" . $moneda . "</td>";

                        echo "<td style='display:none;'>" . $objeto->nivel . "</td>";



                        echo "<td ><div align='center' class='cursor'><img src='images/b_drop.png' 

			title='Anular' alt='Anular' border='0' class='cursor' onclick='deleteCuenta($id)'/></div></td>";

                        echo "<td><div align='center'>

			 <img src='images/b_edit.png' class='cursor' onclick=\"newTransaction(this.parentNode.parentNode.parentNode,'update'); return false;\" 

			 title='Modificar' alt='Modificar' border='0' /></div></td>";

                        echo "<td style='display:none;'>" . $objeto->cuenta . "</td>";

                        echo "<td style='display:none;'>" . $objeto->idmoneda . "</td>";
                        echo "<td style='display:none;'>" . $objeto->idplandecuenta . "</td>";

                        echo "</tr>";

                        $conec->siguiente();
                    }

                    echo "</tbody></table>";
                } else {

                    echo "No se obtuvieron resultados";
                }
            }
            ?>





                        <script>



                            $(function() {



                                $("#dialog_cuenta").dialog({
                                    autoOpen: false, modal: true,
                                    position: {my: "top top", at: "top top"},
                                    dialogClass: 'myTitleClass',
                                    buttons: {
                                        "Agregar": function() {

                                            sendTransaction();

                                        }

                                    },
                                    width: 600,
                                    height: 200,
                                    show: {
                                        effect: "drop",
                                        direction: "up",
                                        duration: 400

                                    },
                                    hide: {
                                        effect: "drop",
                                        direction: "up",
                                        duration: 400

                                    }

                                });



                                $("#dialog_cuenta_anular").dialog({
                                    autoOpen: false, modal: true,
                                    position: {my: "top top", at: "top top"},
                                    dialogClass: 'myTitleClass',
                                    buttons: {
                                        "Anular": function() {

                                            sendDelete();

                                        },
                                        "Cancelar": function() {

                                            $(this).dialog("close");

                                        }

                                    },
                                    width: 600,
                                    height: 200,
                                    show: {
                                        effect: "drop",
                                        direction: "up",
                                        duration: 400

                                    },
                                    hide: {
                                        effect: "drop",
                                        direction: "up",
                                        duration: 400

                                    }

                                });



                            });





                            $(document).ready(function() {

                                on();

                            });



                        </script>



            <?php
            $flag = false;

            if ($flag) {
                ?>

                            <table cellspacing="0" cellpadding="0" class="tTreen">

                                <tbody>

                                    <tr>

                                        <td class="tTreenA" align="right" valign="top">

                                            <div id="plancuenta" class="plancuenta"> </div>

                                        </td>

                                        <td class="tTreenB" align="right" valign="top">

                                            <label>Codigo:</label>

                                            <input type="text" value="" id="cue_codigo" name="cue_codigo" style="width:250px;" placeholder="<?php echo $cod_codigo; ?>"><br><br>

                                            <label>Descripci&oacute;n:</label>

                                            <input type="text" value="" id="cue_descripcion" name="cue_descripcion" style="width:250px;" placeholder="Descripcion"><br><br>

                                            <!--<div style="display: none;">-->

                                            <label>Tipo de dato:</label>

                                            <select name="cue_tipo" id="cue_tipo" class="caja_texto">

                                                <option value="Titulo">Titulo</option>

                                                <option value="Movimiento">Movimiento</option>                                                        

                                            </select> <br><br>

                                            <!--</div>-->

                                            <div id="sel_moneda" hidden="">

                                                <label>Moneda:</label>

                                                <select name="cue_mon_id" id="cue_mon_id" class="caja_texto">

                                                    <option value="">Seleccione</option>

                <?php
                $fun = NEW FUNCIONES;

                $fun->combo("select mon_id as id,mon_titulo as nombre from con_moneda order by mon_id asc", 0);
                ?>

                                                </select> <br><br>

                                            </div>



                                            <div id="mmenu" style="height:30px; overflow:auto;">

                                                <input type="hidden" value="<?php echo $_GET['id']; ?>" id="cue_ges_id" name="cue_ges_id" style="width:250px;">

                                                <input style="width: 50px" type="button" id="agregar" value="Agregar" class="boton"/>

                                                <input style="width: 60px" type="button" id="rename" value="Renombrar" class="boton"/>

                                                <input style="width: 50px" type="button" id="remove" value="Borrar" class="boton"/>

                                            </div>



                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                <?php
            } elseif ($_GET['p'] == 'cpf') {
                ?>



                            <!--Inicio-->

                            <div id="ContenedorDiv">

                                <div class="Etiqueta" >Cuenta</div>

                                <div id="CajaInput">                                    

                                    <input name="nombre_cuenta" id="nombre_cuenta"  type="text" class="caja_texto" value="<?php //echo $_POST['nombre_cuenta']                 ?>" size="25">

                                </div>							   							   								

                            </div>

                            <!--Fin-->

                            <!--Inicio-->

                            <div id="ContenedorDiv">

                                <div class="Etiqueta" >Cuentas a listar</div>

                                <div id="CajaInput">

                                    <div class="box_lista_cuenta"> 

                                        <table id="tab_lista_cuentas" class="tab_lista_cuentas">

                                                    <?php
                                                    $conec = new ADO();

                                                    $sql = "select * from con_detalle_cf where dcf_ges_id=" . $_GET['id'];

                                                    $conec->ejecutar($sql);

                                                    for ($i = 0; $i < $conec->get_num_registros(); $i++) {

                                                        $objeto = $conec->get_objeto();

                                                        $fila = '<tr data-id="' . $objeto->dcf_cue_id . '">';

                                                        $fila .= '<td>' . FUNCIONES::atributo_bd('con_cuenta', "cue_id=$objeto->dcf_cue_id and cue_eliminado='No'", "cue_descripcion") . '</td>';

                                                        $fila .= '<td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';

                                                        $fila .= '</tr>';

                                                        echo $fila;

                                                        $conec->siguiente();
                                                    }
                                                    ?>

                                        </table>

                                        <input type="hidden" value="" name="lista_cuentas" id="lista_cuentas"/>

                                    </div>

                                </div>							   							   								

                            </div>

                            <div id="ContenedorDiv">

                                <input type="hidden" value="<?php echo $_GET['id']; ?>" id="cue_ges_id" name="cue_ges_id" style="width:250px;">

                                <input type="button" id="guardar_detalles_cf" value="Guardar" class="boton"/>                                        

                            </div>                                    

                            <!--Fin-->

                            <script>

                                var options = {
                                    script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cuenta&gesid=" + $("#cue_ges_id").val() + "&",
                                    varname: "input",
                                    json: true,
                                    shownoresults: false,
                                    maxresults: 6,
                                    callback: function(obj) {

                                        agregar_cuenta(obj);

                                    }

                                };

                                var as_json1 = new _bsn.AutoSuggest('nombre_cuenta', options);



                                function agregar_cuenta(cuenta) {

                                    if (!existe_en_lista(cuenta.id)) {

                                        var fila = '<tr data-id="' + cuenta.id + '">';

                                        fila += '<td>' + cuenta.value + '</td>';

                                        fila += '<td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';

                                        fila += '</tr>';

                                        $("#tab_lista_cuentas").append(fila);

                                        $("#nombre_cuenta").val("");

                                    }

                                }



                                function existe_en_lista(id_cuenta) {

                                    var lista = $("#tab_lista_cuentas tr");

                                    for (var i = 0; i < lista.size(); i++) {

                                        var cuenta = lista[i];

                                        var id = $(cuenta).attr("data-id");

                                        if (id === id_cuenta) {

                                            return true;

                                        }

                                    }

                                    return false;

                                }



                                $(document).on('click', '.img_del_cuenta', function() {

                                    $(this).parent().parent().remove();

                                });



                                $("#guardar_detalles_cf").click(function() {

                                    var lista = $("#tab_lista_cuentas tr");

                                    var data = "";

                                    for (var i = 0; i < lista.size(); i++) {

                                        var cuenta = lista[i];

                                        var id = $(cuenta).attr("data-id");

                                        if (i > 0) {

                                            data += "," + id;

                                        } else {

                                            data += id;

                                        }

                                    }



                                    $.post('gestorAjax.php', {dir: 'con_detalle_cf.php', cuentas: data, ges_id: $("#cue_ges_id").val()}, function(respuesta) {

                                        $.prompt(respuesta);

                                    });

                                });



                            </script>

                <?php
            }
            ?>

                    </div>



                </div>

            </div>





            <?php
        }
    }

}

