<?php

class CON_CONFIGURACION {

    var $mensaje;

    function CON_CONFIGURACION() {
        $this->link = 'gestor.php';
        $this->modulo = 'con_configuracion';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('CONFIGURACION DEL MODULO CONTABLE');
    }

    function datos() {
        if ($_POST)
            return true;
        else
            return false;
    }

    function actualizar() {

//        _PRINT::pre($_POST);
        $this->mensaje = "Los parametros fueron actualizados correctamente";
        
        $fil_ges="and conf_ges_id='".$_SESSION['ges_id']."'";
        
        $conec=new ADO();
        
        $sql = "update con_configuracion set conf_valor='".$_POST['revisado']."' where conf_nombre='revisado' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['aprobado']."' where conf_nombre='aprobado' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['cred_fiscal']."' where conf_nombre='cred_fiscal' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['deb_fiscal']."' where conf_nombre='deb_fiscal' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['val_iva']."' where conf_nombre='val_iva' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['it']."' where conf_nombre='it' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['itpagar']."' where conf_nombre='itpagar' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['val_it']."' where conf_nombre='val_it' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['nit']."' where conf_nombre='nit' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['razon_social']."' where conf_nombre='razon_social' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['direccion']."' where conf_nombre='direccion' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['cant_fil']."' where conf_nombre='cant_fil' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['cant_fil_vc']."' where conf_nombre='cant_fil_vc' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['aj_inflacion']."' where conf_nombre='aj_inflacion' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['aj_capital']."' where conf_nombre='aj_capital' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['aj_reserva']."' where conf_nombre='aj_reserva' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['reex_ingresos']."' where conf_nombre='reex_ingresos' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['reex_egresos']."' where conf_nombre='reex_egresos' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['dif_cambio']."' where conf_nombre='dif_cambio' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['mant_valor']."' where conf_nombre='mant_valor' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['usar_reex']."' where conf_nombre='usar_reex' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['aj_res_acumulado']."' where conf_nombre='aj_res_acumulado' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['cuentas_cap']."' where conf_nombre='cuentas_cap' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['cuentas_res']."' where conf_nombre='cuentas_res' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['cuentas_acu']."' where conf_nombre='cuentas_acu' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['ret_it_bien']."' where conf_nombre='ret_it_bien' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['ret_it_serv']."' where conf_nombre='ret_it_serv' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['ret_iue_serv']."' where conf_nombre='ret_iue_serv' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['ret_iue_bien']."' where conf_nombre='ret_iue_bien' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['cuentas_act_disp']."' where conf_nombre='cuentas_act_disp' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['porc_ret_it']."' where conf_nombre='porc_ret_it' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['porc_ret_iue_serv']."' where conf_nombre='porc_ret_iue_serv' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['porc_ret_iue_bien']."' where conf_nombre='porc_ret_iue_bien' $fil_ges";
        $conec->ejecutar($sql);        
        $sql = "update con_configuracion set conf_valor='".$_POST['formula_est_res']."' where conf_nombre='formula_est_res' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['formula_bal_gral']."' where conf_nombre='formula_bal_gral' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['formula_est_flu']."' where conf_nombre='formula_est_flu' $fil_ges";
        $conec->ejecutar($sql);
        $sql = "update con_configuracion set conf_valor='".$_POST['tit_act_disp']."' where conf_nombre='tit_act_disp' $fil_ges";
        $conec->ejecutar($sql);
        
    }

    function formulario_tcp() {
        if ($this->datos()) {
            $this->actualizar();
        }

        $conec = new ADO();
        $ges_id=$_SESSION['ges_id'];
        $sql = "select * from con_configuracion where conf_editable='1' and conf_ges_id='$ges_id' order by conf_orden";

        $conec->ejecutar($sql);
        $gesid=$_SESSION['ges_id'];
        $objeto = $conec->get_objeto();
        $revisado=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $aprobado=$objeto->conf_valor;        
        
        $conec->siguiente();        
        $objeto = $conec->get_objeto();        
        $cred_fiscal=$objeto->conf_valor;
        if($cred_fiscal!='')
            $txt_cred_fiscal=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$cred_fiscal' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $deb_fiscal=$objeto->conf_valor;
        if($deb_fiscal!='')
            $txt_deb_fiscal=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$deb_fiscal' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $val_iva=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $it=$objeto->conf_valor;
        if($it!='')
            $txt_it=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$it' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $itpagar=$objeto->conf_valor;
        if($itpagar!='')
            $txt_itpagar=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$itpagar' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $val_it=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $nit=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $razon_social=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $direccion=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $can_fil=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $can_fil_vc=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $aj_inflacion=$objeto->conf_valor;
        if($aj_inflacion !='')
            $txt_aj_inflacion=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$aj_inflacion' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $aj_capital=$objeto->conf_valor;
        if($aj_capital!='')
            $txt_aj_capital=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$aj_capital' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $aj_reserva=$objeto->conf_valor;
        if($aj_reserva!='')            
            $txt_aj_reserva=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$aj_reserva' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $reex_ingresos=$objeto->conf_valor;
        if($reex_ingresos!='')
            $txt_reex_ingresos=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$reex_ingresos' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $reex_egresos=$objeto->conf_valor;
        if($reex_egresos!='')
            $txt_reex_egresos=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$reex_egresos' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $dif_cambio=$objeto->conf_valor;
        if($dif_cambio!='')
            $txt_dif_cambio=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$dif_cambio' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $mant_valor=$objeto->conf_valor;
        if($mant_valor!='')
            $txt_mant_valor=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$mant_valor' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $usar_reex=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $aj_res_acumulado=$objeto->conf_valor;
        if($aj_res_acumulado!='')
            $txt_aj_res_acumulado=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$aj_res_acumulado' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $cuentas_cap=$objeto->conf_valor!=''?explode(',',$objeto->conf_valor):array();
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $cuentas_res=$objeto->conf_valor!=''?explode(',',$objeto->conf_valor):array();
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $cuentas_acu=$objeto->conf_valor!=''?explode(',',$objeto->conf_valor):array();
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $ret_it_bien=$objeto->conf_valor;
        if($ret_it_bien!='')
            $txt_ret_it_bien=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$ret_it_bien' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $ret_iue_serv=$objeto->conf_valor;
        if($ret_iue_serv!='')
            $txt_ret_iue_serv=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$ret_iue_serv' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $ret_iue_bien=$objeto->conf_valor;
        if($ret_iue_bien!='')
            $txt_ret_iue_bien=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$ret_iue_bien' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $cuentas_act_disp=$objeto->conf_valor!=''?explode(',',$objeto->conf_valor):array();
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $porc_ret_it=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $porc_ret_iue_serv=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $porc_ret_iue_bien=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $formula_est_res=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $formula_bal_gral=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $formula_est_flu=$objeto->conf_valor;
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $tit_act_disp=$objeto->conf_valor;
        if($ret_iue_bien!='')
            $txt_tit_act_disp=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$tit_act_disp' and cue_ges_id='$gesid'", "cue_descripcion");
        
        $conec->siguiente();
        $objeto = $conec->get_objeto();        
        $ret_it_serv=$objeto->conf_valor;
        if($ret_it_serv!='')
            $txt_ret_it_serv=  FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$ret_it_serv' and cue_ges_id='$gesid'", "cue_descripcion");
        
        
        $no_existe='No existe una cuenta con el mismo codigo en esta gesti&oacute;n';
//        _PRINT::pre($cuentas_cap);

        $url = $this->link . '?mod=' . $this->modulo;

        $this->formulario->dibujar_tarea();

        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Correcto', $this->mensaje);
        }
        ?>
        <!--AutoSuggest-->
        <script type="text/javascript" src="js/bsn.AutoSuggest_2.1.3.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        <!--AutoSuggest-->
        
        <style>
            .error{
                color: #ff0000;
                margin-left: 5px;
            }            
            .tab_lista_cuentas{
                list-style: none;                                    
                width: 100%;                                    
                overflow:scroll ;
                background-color: #ededed;
                border-collapse: collapse;  
                font-size: 12px;
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
            .txt_rojo{
                color: #ff0000;
            }
            .Subtitulo:hover{
                cursor: pointer;
            }
            .tit_config{
                font-size: 12px;
                font-weight: bold;
                color: #3a3a3a;
                margin-top:5px;                
                padding:5px 10px 5px 10px;
                background:#cdd0d5;        
                cursor: pointer;        
            }
            .tit_config:hover{               
                background:#bdc1c6;        
                
            }
        </style>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent">
                    <script>                    
                        $(".Subtitulo").live("click",function (){
                            var id=$(this).attr('data-id');                            
                            $(".cont_"+id).slideToggle();
                        });
                        $(".tit_config").live("click", function (){
                            var subtitulos=$(".Subtitulo");
                            var mode=$(this).attr("data-mode");
                            for(var i=0;i<subtitulos.size();i++){
                                var id=$(subtitulos[i]).attr("data-id");
                                if(mode==='collapse'){
                                    $(".cont_"+id).slideDown();
                                }else if(mode==='expand'){
                                    $(".cont_"+id).slideUp();
                                }
                            }                            
                            if(mode==='expand'){
                                $(this).attr("data-mode","collapse");
                                $(this).text("Expandir Todo");                                
                            }else if(mode==='collapse'){
                                $(this).attr("data-mode","expand");
                                $(this).text("Colapsar Todo");                                
                            }
                        });
                    </script>
                    
                    <div data-mode="expand" class="tit_config" >Colapasar Todo</div>                    
                    <div data-id="d_cmp" class="Subtitulo">Datos de elaboracion del comprobante</div>
                    <div style="clear: both"></div>
                    <div class="cont_d_cmp" id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Revisado:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="revisado" id="revisado" size="25" value="<?php echo $revisado; ?>">                                
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Aprobado:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="aprobado" id="aprobado" size="25" value="<?php echo $aprobado; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Nro. de filas por detalle:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="cant_fil" id="cant_fil" size="25" value="<?php echo $can_fil; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                    </div>
                    <div hidden="" id="cue_titulos_root">
                        <img src="images/format-saldo.png" width="13" class="format_campo_formula">
                        <img src="images/del-campo.png" width="13" class="del_campo_formula">
                        <?php
                        $sql="select * from con_cuenta where cue_tree_level=1 and cue_ges_id='$ges_id' order by cue_codigo;";
                        $_cuentas_raiz=  FUNCIONES::objetos_bd_sql($sql);
                        ?>
                        <select class="camp_signo">
                            <option value="1">+&nbsp;&nbsp;</option>
                            <option value="-1">-&nbsp;&nbsp;</option>
                        </select>
                        <select class="camp_cuenta">
                            <?php                            
                            for($i=0;$i<$_cuentas_raiz->get_num_registros();$i++){
                                $cu=$_cuentas_raiz->get_objeto();
                            ?>
                                <option value="<?=$cu->cue_codigo?>"><?=$cu->cue_descripcion;?></option>
                            <?php
                                $_cuentas_raiz->siguiente();
                            }
                            ?>
                        </select>
                    </div>
                    <div hidden="" id="cfl_titulos_root">
                        <img src="images/format-saldo.png" width="13" class="format_campo_formula">
                        <img src="images/del-campo.png" width="13" class="del_campo_formula">
                        <?php
                        $_cuentas_cf=  FUNCIONES::objetos_bd_sql("select * from con_cuenta_cf where cfl_tree_level=1 and cfl_ges_id='$ges_id' order by cfl_codigo");
                        ?>
                        <select class="camp_signo">
                            <option value="1">+&nbsp;&nbsp;</option>
                            <option value="-1">-&nbsp;&nbsp;</option>
                        </select>
                        <select class="camp_cuenta">
                            <?php                            
                            for($i=0;$i<$_cuentas_cf->get_num_registros();$i++){
                                $cu=$_cuentas_cf->get_objeto();
                            ?>
                                <option value="<?=$cu->cfl_codigo?>"><?=$cu->cfl_descripcion;?></option>
                            <?php
                                $_cuentas_cf->siguiente();
                            }
                            ?>
                        </select>
                    </div>
                    <style>
                        .box_formula{
                            float: left;
                            /*margin-right: 5px*/
                        }
                        .box_campo_formula{
                            position: relative;
                            float: left;
                            margin-right: 5px;
                        }
                        .box_txt_est_res{
                            float: left;
                            font-weight: bold;
                        }
                        .box_add_campo{
                            float: left;
                        }
                        .camp_signo{
                            font-weight: bold;
                        }
                        .box_add_campo,.del_campo_formula,.format_campo_formula{
                            cursor: pointer;
                        }                        
                        .del_campo_formula{
                            position: absolute;
                            top: -12px;
                            right: 0px;
                            opacity: 0.7;
                        }
                        .format_campo_formula{
                            position: absolute;
                            top: -12px;
                            right: 15px;
                            opacity: 0.7;
                        }
                        .del_campo_formula:hover, .format_campo_formula:hover{
                            opacity: 1.0;
                        }
                    </style>
                    <script>
                        $("#add_campo_formula").live('click',function (){
                            var html="<div class='box_campo_formula' data-op='1'>";
                            var data=$(this).attr('data-tc');
                            if(data==='cfl'){
                                html+=$("#cfl_titulos_root").html();
                            }else{
                                html+=$("#cue_titulos_root").html();
                            }
                            
                            html+="<div>";
                            $(this).parent().prev().append(html);
                            
                        });
                        $(".del_campo_formula").live('click',function (){
//                            var clase=$(this).parent().attr("class");
//                            var box_campos=$("."+clase);
                            var box_campos=$(this).parent().parent().children();
                            if(box_campos.size()>1){
                                $(this).parent().remove();
                            }else{
                                $.prompt("Debe haber al menos un campo");
                            }
                            
                        });
                        
                        $(".format_campo_formula").live('click',function (){
                            var txt = 'Seleccione la operacion del calculo del saldo<br>'
                            + '<select id="operacion" style="width: 100px" name="operacion">';
                            var padre=$(this).parent();
                            if(padre.attr('data-op')==='1'){
                                txt += '             <option value="1" selected="">Debe - Haber</option>';
                                txt += '             <option value="-1">Haber - Debe</option>';
                            }else{
                                txt += '             <option value="1" >Debe - Haber</option>';
                                txt += '             <option value="-1" selected="">Haber - Debe</option>';
                            }
                            txt += '         </select>';
                            var t_formula=$(this).parent().parent().attr("id");
                            if(t_formula==='box_formula_bal_gral_1'|| t_formula==='box_formula_bal_gral_2'){
                                txt+='<br>Sumar el Resultado de la gesti&oacute;n<br>';
                                txt+='<select id="sum_est_res" style="width: 100px" name="sum_est_res">';
                                var padre=$(this).parent();
                                if(padre.attr('data-sum')==='1'){
                                    txt += '             <option value="0" >No</option>';
                                    txt += '             <option value="1" selected="">Si</option>';
                                }else{
                                    txt += '             <option value="0" selected="">No</option>';
                                    txt += '             <option value="1" >Si</option>';
                                }
                                txt += '         </select>';
                            }
                            
                            $.prompt(txt, {
                            buttons: {Aceptar: true, Cancelar: false},
                            callback: function(v, m, f) {
                                        if (v) {       
                                            padre.attr("data-op",f.operacion);
                                            padre.attr("data-sum",f.sum_est_res);
                                        }

                                    }
                            }); 
                        });
                        
                        function formula_estado_resultado(){
//                            var campos=$(".box_campo_est_res");
                            var campos=$("#box_formula_est_res").children();
                            var txt_form='[';
                            for(var i=0;i<campos.size();i++){
                                var signo=$(campos[i]).find('.camp_signo option:selected').val();
                                var cuenta=$(campos[i]).find('.camp_cuenta option:selected').val();
                                var op=$(campos[i]).attr('data-op');
                                if(i>0)
                                    txt_form+=',';
                                txt_form+='{"signo":"'+signo+'","cuenta":"'+cuenta+'","op":"'+op+'"}';
                            }
                            txt_form+=']';
                            $("#formula_est_res").val(txt_form);
                        }
                        function formula_balance_general(){
//                            var campos=$(".box_campo_est_res");
                            var campos=$("#box_formula_bal_gral_1").children();
                            var txt_form_1='[';
                            for(var i=0;i<campos.size();i++){
                                var signo=$(campos[i]).find('.camp_signo option:selected').val();
                                var cuenta=$(campos[i]).find('.camp_cuenta option:selected').val();
                                var op=$(campos[i]).attr('data-op');
                                var sum=$(campos[i]).attr('data-sum');
                                if(i>0)
                                    txt_form_1+=',';
                                txt_form_1+='{"signo":"'+signo+'","cuenta":"'+cuenta+'","op":"'+op+'","sum":"'+sum+'"}';
                            }
                            txt_form_1+=']';
                            
                            campos=$("#box_formula_bal_gral_2").children();
                            var txt_form_2='[';
                            for(var i=0;i<campos.size();i++){
                                var signo=$(campos[i]).find('.camp_signo option:selected').val();
                                var cuenta=$(campos[i]).find('.camp_cuenta option:selected').val();
                                var op=$(campos[i]).attr('data-op');
                                var sum=$(campos[i]).attr('data-sum');
                                if(i>0)
                                    txt_form_2+=',';
                                txt_form_2+='{"signo":"'+signo+'","cuenta":"'+cuenta+'","op":"'+op+'","sum":"'+sum+'"}';
                            }
                            txt_form_2+=']';
                            var txt_form='{"1":'+txt_form_1+',"2":'+txt_form_2+'}';
                            $("#formula_bal_gral").val(txt_form);
                        }
                        
                        function formula_estado_flujo(){
//                            var campos=$(".box_campo_est_res");
                            var campos=$("#box_formula_est_flu").children();
                            var txt_form='[';
                            for(var i=0;i<campos.size();i++){
                                var signo=$(campos[i]).find('.camp_signo option:selected').val();
                                var cuenta=$(campos[i]).find('.camp_cuenta option:selected').val();
                                var op=$(campos[i]).attr('data-op');
                                if(i>0)
                                    txt_form+=',';
                                txt_form+='{"signo":"'+signo+'","cuenta":"'+cuenta+'","op":"'+op+'"}';
                            }
                            txt_form+=']';
                            $("#formula_est_flu").val(txt_form);
                        }
                    </script>
                    <div data-id="d_formula" class="Subtitulo">Estado de Resultado y Balance General</div>
                    <div style="clear: both"></div>
                    <div class="cont_d_formula" id="ContenedorSeleccion" >
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" style="width:  auto;">F&oacute;rmula del Estado de Resultado:</div>
                            <div style="clear: both"></div><br>
                            <div id="CajaInput">
                                <input type="hidden" name="formula_est_res" id="formula_est_res" value='<?=$formula_est_res?>'>
                                <div class="box_txt_est_res">&nbsp; &nbsp; Resultado = &nbsp;</div>
                                <div class="box_formula" id="box_formula_est_res" >
                                    <?php
                                    $list_est_res= json_decode($formula_est_res);
                                    for($i=0;$i<count($list_est_res);$i++){
                                        $campo=$list_est_res[$i];
                                    ?>
                                    <div class='box_campo_formula' data-op='<?=$campo->op;?>'>
                                        <img src="images/format-saldo.png" width="13" class="format_campo_formula">
                                        <img src="images/del-campo.png" width="13" class="del_campo_formula">
                                        <select class="camp_signo">
                                            <?php if($campo->signo=='1'){ ?>
                                            <option value="1" selected="">+&nbsp;&nbsp;</option>
                                            <option value="-1">-&nbsp;&nbsp;</option>
                                            <?php }else{ ?>
                                                <option value="1" >+&nbsp;&nbsp;</option>
                                                <option value="-1" selected="">-&nbsp;&nbsp;</option>
                                            <?php } ?>
                                        </select>
                                        <select class="camp_cuenta">
                                            <?php      
                                            $_cuentas_raiz->reset();
                                            for($j=0;$j<$_cuentas_raiz->get_num_registros();$j++){
                                                $cu=$_cuentas_raiz->get_objeto();
                                                if($cu->cue_codigo==$campo->cuenta){
                                                ?>                                                
                                                    <option value="<?=$cu->cue_codigo?>" selected=""><?=$cu->cue_descripcion;?></option>
                                                <?}else{?>
                                                    <option value="<?=$cu->cue_codigo?>"><?=$cu->cue_descripcion;?></option>
                                                <?}?>
                                            <?php
                                                $_cuentas_raiz->siguiente();
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php
                                    }
                                    ?>                                    
                                </div>
                                <div class="box_add_campo"><img src="images/add-campo.png" width="16" id="add_campo_formula"></div>
                                
                                
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" style="width: auto;" >F&oacute;rmula del Balance General:</div>
                            <div style="clear: both"></div><br>
                            <div id="CajaInput">
                                <input type="hidden" name="formula_bal_gral" id="formula_bal_gral" value='<?=$formula_bal_gral?>'>
                                <div style="float: left;">&nbsp;&nbsp;&nbsp;&nbsp;</div>
                                <div class="box_formula" id="box_formula_bal_gral_1" >
                                    <?php
                                    $list_bal_gral= json_decode($formula_bal_gral);
//                                    _PRINT::pre($list_bal_gral);
                                    $eq1=$list_bal_gral->{'1'};                                    
                                    for($i=0;$i<count($eq1);$i++){
                                        $campo=$eq1[$i];
//                                        print_r($campo);
                                    ?>
                                    <div class='box_campo_formula' data-op='<?=$campo->op;?>' data-sum="<?=$campo->sum;?>">
                                        <img src="images/format-saldo.png" width="13" class="format_campo_formula">
                                        <img src="images/del-campo.png" width="13" class="del_campo_formula">
                                        <select class="camp_signo">
                                            <?php if($campo->signo=='1'){ ?>
                                            <option value="1" selected="">+&nbsp;&nbsp;</option>
                                            <option value="-1">-&nbsp;&nbsp;</option>
                                            <?php }else{ ?>
                                                <option value="1" >+&nbsp;&nbsp;</option>
                                                <option value="-1" selected="">-&nbsp;&nbsp;</option>
                                            <?php } ?>
                                        </select>
                                        <select class="camp_cuenta">
                                            <?php      
                                            $_cuentas_raiz->reset();
                                            
                                            for($j=0;$j<$_cuentas_raiz->get_num_registros();$j++){
                                                $cu=$_cuentas_raiz->get_objeto();
                                                if($cu->cue_codigo==$campo->cuenta){
                                                ?>                                                
                                                    <option value="<?=$cu->cue_codigo?>" selected=""><?=$cu->cue_descripcion;?></option>
                                                <?}else{?>
                                                    <option value="<?=$cu->cue_codigo?>"><?=$cu->cue_descripcion;?></option>
                                                <?}?>
                                            <?php
                                                $_cuentas_raiz->siguiente();
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php
                                    }
                                    ?>                                    
                                </div>
                                <div class="box_add_campo"><img src="images/add-campo.png" width="16" id="add_campo_formula"></div>
                                <div class="box_txt_est_res">&nbsp; = &nbsp;</div>
                                <div class="box_formula" id="box_formula_bal_gral_2" >
                                    <?php                                    
                                    $eq2=$list_bal_gral->{'2'};
                                    for($i=0;$i<count($eq2);$i++){
                                        $campo=$eq2[$i];
                                    ?>
                                    <div class='box_campo_formula' data-op='<?=$campo->op;?>' data-sum="<?=$campo->sum;?>">
                                        <img src="images/format-saldo.png" width="13" class="format_campo_formula">
                                        <img src="images/del-campo.png" width="13" class="del_campo_formula">
                                        <select class="camp_signo">
                                            <?php if($campo->signo=='1'){ ?>
                                            <option value="1" selected="">+&nbsp;&nbsp;</option>
                                            <option value="-1">-&nbsp;&nbsp;</option>
                                            <?php }else{ ?>
                                                <option value="1" >+&nbsp;&nbsp;</option>
                                                <option value="-1" selected="">-&nbsp;&nbsp;</option>
                                            <?php } ?>
                                        </select>
                                        <select class="camp_cuenta">
                                            <?php      
                                            $_cuentas_raiz->reset();
                                            for($j=0;$j<$_cuentas_raiz->get_num_registros();$j++){
                                                $cu=$_cuentas_raiz->get_objeto();
                                                if($cu->cue_codigo==$campo->cuenta){
                                                ?>                                                
                                                    <option value="<?=$cu->cue_codigo?>" selected=""><?=$cu->cue_descripcion;?></option>
                                                <?}else{?>
                                                    <option value="<?=$cu->cue_codigo?>"><?=$cu->cue_descripcion;?></option>
                                                <?}?>
                                            <?php
                                                $_cuentas_raiz->siguiente();
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php
                                    }
                                    ?>                                    
                                </div>
                                <div class="box_add_campo"><img src="images/add-campo.png" width="16" id="add_campo_formula"></div>
                            </div>
                        </div>
                        <!--Fin-->                        
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            
                            <div class="Etiqueta" style="text-align: left;">T&iacute;tulo de Activo Disponible:</div>
                            <div style="clear: both"></div>
                            <div id="CajaInput">                                
                                <input type="text" class="caja_texto cue_complete" name="tit_act_disp" data-cod="<?php echo $txt_tit_act_disp==''?'':$tit_act_disp;?>"  id="tit_act_disp" size="25" value="<?php echo $tit_act_disp; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_tit_act_disp==''?'txt_rojo':'';?>" name="txt_tit_act_disp" id="txt_tit_act_disp" size="55" value="<?php echo $txt_tit_act_disp==''?$no_existe:$txt_tit_act_disp;?>"  readonly="">
                            </div>
                        </div>                        
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" style="width:  auto;">F&oacute;rmula del Estado de Flujo de Efectivo:</div>
                            <div style="clear: both"></div><br>
                            <div id="CajaInput">
                                <input type="hidden" name="formula_est_flu" id="formula_est_flu" value='<?=$formula_est_flu?>'>
                                <div class="box_txt_est_res">&nbsp; &nbsp; Resultado = &nbsp;</div>
                                <div class="box_formula" id="box_formula_est_flu" >
                                    <?php
                                    $list_est_flu= json_decode($formula_est_flu);
                                    for($i=0;$i<count($list_est_flu);$i++){
                                        $campo=$list_est_flu[$i];
                                    ?>
                                    <div class='box_campo_formula' data-op='<?=$campo->op;?>'>
                                        <img src="images/format-saldo.png" width="13" class="format_campo_formula">
                                        <img src="images/del-campo.png" width="13" class="del_campo_formula">
                                        <select class="camp_signo">
                                            <?php if($campo->signo=='1'){ ?>
                                            <option value="1" selected="">+&nbsp;&nbsp;</option>
                                            <option value="-1">-&nbsp;&nbsp;</option>
                                            <?php }else{ ?>
                                                <option value="1" >+&nbsp;&nbsp;</option>
                                                <option value="-1" selected="">-&nbsp;&nbsp;</option>
                                            <?php } ?>
                                        </select>
                                        <select class="camp_cuenta">
                                            <?php      
                                            
                                            $_cuentas_cf->reset();
                                            for($j=0;$j<$_cuentas_cf->get_num_registros();$j++){
                                                $cu=$_cuentas_cf->get_objeto();
                                                if($cu->cfl_codigo==$campo->cuenta){
                                                ?>                                                
                                                    <option value="<?=$cu->cfl_codigo?>" selected=""><?=$cu->cfl_descripcion;?></option>
                                                <?}else{?>
                                                    <option value="<?=$cu->cfl_codigo?>"><?=$cu->cfl_descripcion;?></option>
                                                <?}?>
                                            <?php
                                                $_cuentas_cf->siguiente();
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php
                                    }
                                    ?>                                    
                                </div>
                                <div class="box_add_campo"><img src="images/add-campo.png" width="16" id="add_campo_formula" data-tc="cfl"></div>
                                
                                
                            </div>
                        </div>
                        <!--Fin-->
                        
                    </div>
                    
                    <div data-id="d_vc" class="Subtitulo">Datos de libro de Compra y Venta</div>
                    <div style="clear: both"></div>
                    <div class="cont_d_vc" id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de IVA Credito Fiscal:</div>
                            <div id="CajaInput">                                
                                <input type="text" class="caja_texto cue_complete" name="cred_fiscal" data-cod="<?php echo $txt_cred_fiscal==''?'':$cred_fiscal;?>"  id="cred_fiscal" size="25" value="<?php echo $cred_fiscal; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_cred_fiscal==''?'txt_rojo':'';?>" name="txt_cred_fiscal" id="txt_cred_fiscal" size="55" x value="<?php echo $txt_cred_fiscal==''?$no_existe:$txt_cred_fiscal;?>"  readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de IVA Debito Fiscal:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="deb_fiscal" data-cod="<?php echo $txt_deb_fiscal==''?'':$deb_fiscal;?>" id="deb_fiscal" size="25" value="<?php echo $deb_fiscal; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_deb_fiscal==''?'txt_rojo':'';?>" name="txt_deb_fiscal" id="txt_deb_fiscal" size="55" value="<?php echo $txt_deb_fiscal==''?$no_existe:$txt_deb_fiscal;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Porcentaje de IVA:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="val_iva" id="val_iva" size="25" value="<?php echo $val_iva; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de IT:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="it" data-cod="<?php echo $txt_it==''?'':$it;?>" id="it" size="25" value="<?php echo $it; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_it==''?'txt_rojo':'';?>" name="txt_it" id="txt_it" size="55" value="<?php echo $txt_it==''?$no_existe:$txt_it;?>" readonly="">                                
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de IT x Pagar:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="itpagar" data-cod="<?php echo $txt_itpagar==''?'':$itpagar;?>" id="itpagar" size="25" value="<?php echo $itpagar; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_itpagar==''?'txt_rojo':'';?>" name="txt_itpagar" id="txt_itpagar" size="55" value="<?php echo $txt_itpagar==''?$no_existe:$txt_itpagar;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Porcentaje IT:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="val_it" id="val_it" size="25" value="<?php echo $val_it; ?>">                                
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Nro de Filas por Folio</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="cant_fil_vc" id="can_fil_vc" size="25" value="<?php echo $can_fil_vc; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <script>
                            function autosuggest(inputHidden, inputVisible,tipo){
                                var strtipo='';
                                if(tipo!==undefined){
                                    strtipo='&cu_tipo='+tipo;
                                }
//                                alert("ss");
//                                $("#"+inputVisible).removeAttr("readonly");
                                var options = {
                                    script: "AjaxRequest.php?peticion=listCuenta&limit=10&tipo=cuenta"+strtipo+"&",
                                    //                    script: "test.php?peticion=listCuenta&json=true&limit=6&tipo="+tipocuenta+"&",
                                    varname: "input",
                                    json: true,
                                    shownoresults: false,
                                    maxresults: 10,
                                    callback: function(obj) {
                                        $("#"+inputVisible).val(obj.info);
                                        $("#"+inputVisible).attr("data-cod",obj.info);
                                        $("#txt_"+inputVisible).val(obj.value);
                                        $("#txt_"+inputVisible).removeClass('txt_rojo');
                                        $(".msCorrecto").hide();
                                    }
                                };
                                var as_json = new bsn.AutoSuggest(inputVisible, options);
                            }
                            
                            autosuggest("","tit_act_disp",'t');
                            autosuggest("","cred_fiscal");
                            autosuggest("","deb_fiscal");
                            autosuggest("","it");
                            autosuggest("","itpagar");
                            
                        </script>
                    </div>
                    <div data-id="d_ret" class="Subtitulo">Cuentas de Retencion</div>
                    <div style="clear: both"></div>
                    <div class="cont_d_ret" id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de Retenci&oacute;n IT Servicio:</div>
                            <div id="CajaInput">                                
                                <input type="text" class="caja_texto cue_complete" name="ret_it_serv" data-cod="<?php echo $txt_ret_it_serv==''?'':$ret_it_serv;?>"  id="ret_it_serv" size="25" value="<?php echo $ret_it_serv; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_ret_it_serv==''?'txt_rojo':'';?>" name="txt_ret_it_serv" id="txt_ret_it_serv" size="55" value="<?php echo $txt_ret_it_serv==''?$no_existe:$txt_ret_it_serv;?>"  readonly="">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de Retenci&oacute;n IT Bien:</div>
                            <div id="CajaInput">                                
                                <input type="text" class="caja_texto cue_complete" name="ret_it_bien" data-cod="<?php echo $txt_ret_it_bien==''?'':$ret_it_bien;?>"  id="ret_it_bien" size="25" value="<?php echo $ret_it_bien; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_ret_it_bien==''?'txt_rojo':'';?>" name="txt_ret_it_bien" id="txt_ret_it_bien" size="55" value="<?php echo $txt_ret_it_bien==''?$no_existe:$txt_ret_it_bien;?>"  readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Porcentaje Retenci&oacute;n IT:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="porc_ret_it" id="porc_ret_it" size="25" value="<?php echo $porc_ret_it; ?>">                                
                            </div>
                        </div>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de IUE de Servicio:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="ret_iue_serv" data-cod="<?php echo $txt_ret_iue_serv==''?'':$ret_iue_serv;?>" id="ret_iue_serv" size="25" value="<?php echo $ret_iue_serv; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_ret_iue_serv==''?'txt_rojo':'';?>" name="txt_ret_iue_serv" id="txt_ret_iue_serv" size="55" value="<?php echo $txt_ret_iue_serv==''?$no_existe:$txt_ret_iue_serv;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Porcentaje Retenci&oacute;n IUE Servicio:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="porc_ret_iue_serv" id="porc_ret_iue_serv" size="25" value="<?php echo $porc_ret_iue_serv; ?>">                                
                            </div>
                        </div>
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de IUE de Bienes:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="ret_iue_bien" data-cod="<?php echo $txt_ret_iue_bien==''?'':$ret_iue_bien;?>" id="ret_iue_bien" size="25" value="<?php echo $ret_iue_bien; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_ret_iue_bien==''?'txt_rojo':'';?>" name="txt_ret_iue_bien" id="txt_ret_iue_bien" size="55" value="<?php echo $txt_ret_iue_bien==''?$no_existe:$txt_ret_iue_bien;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Porcentaje Retenci&oacute;n IUE Bienes:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="porc_ret_iue_bien" id="porc_ret_iue_bien" size="25" value="<?php echo $porc_ret_iue_bien; ?>">                                
                            </div>
                        </div>
                        
                        <script>
                            autosuggest("","ret_it_serv");
                            autosuggest("","ret_it_bien");
                            autosuggest("","ret_iue_serv");
                            autosuggest("","ret_iue_bien");
                        </script>
                    </div>
                    <div data-id="d_ajuste" class="Subtitulo" hidden="">Parametros para ajuste</div>
                    <div style="clear: both"></div>
<!--                    <div class="cont_d_ajuste" id="ContenedorSeleccion" hidden="">-->
                    <div class="" id="ContenedorSeleccion" hidden="">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de Ajuste por Inflaci&oacute;n:</div>
                            <div id="CajaInput">                                
                                <input type="text" class="caja_texto cue_complete" name="aj_inflacion" data-cod="<?php echo $txt_aj_inflacion==''?'':$aj_inflacion;?>" id="aj_inflacion" size="25" value="<?php echo $aj_inflacion; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_aj_inflacion==''?'txt_rojo':'';?>" name="txt_aj_inflacion" id="txt_aj_inflacion" size="55" value="<?php echo $txt_aj_inflacion==''?$no_existe:$txt_aj_inflacion;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de Ajuste de Capital:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" data-cod="<?php echo $txt_aj_capital==''?'':$aj_capital;?>" name="aj_capital" id="aj_capital" size="25" value="<?php echo $aj_capital; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_aj_inflacion==''?'txt_rojo':'';?>" name="txt_aj_capital" id="txt_aj_capital" size="55" value="<?php echo $txt_aj_capital==''?$no_existe:$txt_aj_capital;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de Ajuste de Reservas:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="aj_reserva" data-cod="<?php echo $txt_aj_reserva==''?'':$aj_reserva;?>" id="aj_reserva" size="25" value="<?php echo $aj_reserva; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_aj_reserva==''?'txt_rojo':'';?>" name="txt_aj_reserva" id="txt_aj_reserva" size="55" value="<?php echo $txt_aj_reserva==''?$no_existe:$txt_aj_reserva;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Cuenta de Resultados Acumulados:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="aj_res_acumulado" data-cod="<?php echo $txt_aj_res_acumulado==''?'':$aj_res_acumulado;?>" id="aj_res_acumulado" size="25" value="<?php echo $aj_res_acumulado; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_aj_res_acumulado==''?'txt_rojo':'';?>" name="txt_aj_res_acumulado" id="txt_aj_res_acumulado" size="55" value="<?php echo $txt_aj_res_acumulado==''?$no_existe:$txt_aj_res_acumulado;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Usar cuentas de reexpresi&oacute;n de ingresos y egresos<span class="flechas1">* </span>&nbsp;</div>
                            <div id="CajaInput">
                                <select class="caja_texto" name="usar_reex" id="usar_reex" style="width: 50px">                                    
                                    <option value="Si" <?php if ($usar_reex=='Si')echo 'selected'; ?>>Si</option>
                                    <option value="No" <?php if ($usar_reex=='No')echo 'selected'; ?>>No</option>                                      
                                </select>                                   
                                <!--<input type="checkbox" class="caja_texto" name="usar_reex" id="usar_reex" value="<?php // echo $usar_reex;?>" <?php // if($usar_reex) echo 'checked=""'; ?>>-->
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Reexpresi&oacute;n de Ingresos:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="reex_ingresos" data-cod="<?php echo $txt_reex_ingresos==''?'':$reex_ingresos;?>" id="reex_ingresos" size="25" value="<?php echo $reex_ingresos; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_reex_ingresos==''?'txt_rojo':'';?>" name="txt_reex_ingresos" id="txt_reex_ingresos" size="45" value="<?php echo $txt_reex_ingresos==''?$no_existe:$txt_reex_ingresos;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Reexpresi&oacute;n de Egresos:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="reex_egresos" data-cod="<?php echo $txt_reex_egresos==''?'':$reex_egresos;?>" id="reex_egresos" size="25" value="<?php echo $reex_egresos; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_reex_egresos==''?'txt_rojo':'';?>" name="txt_reex_egresos" id="txt_reex_egresos" size="45" value="<?php echo $txt_reex_egresos==''?$no_existe:$txt_reex_egresos;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Diferencia de Cambio:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="dif_cambio" id="dif_cambio" size="25" value="<?php echo $dif_cambio; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_dif_cambio==''?'txt_rojo':'';?>" name="txt_dif_cambio" id="txt_dif_cambio" size="45" value="<?php echo $txt_dif_cambio==''?$no_existe:$txt_dif_cambio;?>" readonly="">
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Mantenimiento de Valor</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto cue_complete" name="mant_valor" data-cod="<?php echo $txt_mant_valor==''?'':$mant_valor;?>" id="mant_valor" size="25" value="<?php echo $mant_valor; ?>">
                                <input type="text" class="caja_texto <?php echo $txt_mant_valor==''?'txt_rojo':'';?>" name="txt_mant_valor" id="txt_mant_valor" size="45" value="<?php echo $txt_mant_valor==''?$no_existe:$txt_mant_valor;?>" readonly="">
                            </div>
                        </div>
                        <!--Fin-->
                        <script>                            
                            autosuggest("","aj_inflacion");
                            autosuggest("","aj_capital");
                            autosuggest("","aj_reserva");
                            autosuggest("","reex_ingresos");
                            autosuggest("","reex_egresos");
                            autosuggest("","dif_cambio");
                            autosuggest("","mant_valor");
                            autosuggest("","aj_res_acumulado");
                            
                            $("#frm_sentencia").submit(function (){
                                return false;
                            });
                            
                            $(".cue_complete").live("keypress",function (e){                                
                                if(e.keyCode===46 || e.keyCode===8) {
                                   var id= $(this).attr("id");
                                   $(this).attr("data-cod",'');
                                   $("#txt_"+id).val("");
                                }
                            });
                            $(".cue_complete").live("focusout",function (e){
                                if($(this).attr('data-cod')===''){
                                   var name=$(this).attr('name');
                                   $("#txt_"+name).addClass('txt_rojo');                                   
                                   $("#txt_"+name).val("No existe una cuenta con el mismo codigo en esta gestión");                                   
                                }
                            });
                        </script>
                    </div>
                    
                    <div data-id="d_emp" class="Subtitulo">Datos de la empresa</div>
                    <div style="clear: both"></div>
                    <div class="cont_d_emp" id="ContenedorSeleccion">                        
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>NIT:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="nit" id="nit" size="25" value="<?php echo $nit; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Nombre de la empresa:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="razon_social" id="razon_social" size="25" value="<?php echo $razon_social; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">* </span>Direcci&oacute;n:</div>
                            <div id="CajaInput">
                                <input type="text" class="caja_texto" name="direccion" id="direccion" size="25" value="<?php echo $direccion; ?>">
                            </div>
                        </div>
                        <!--Fin-->
                    </div>
                    
                    <div data-id="d_capital"class="Subtitulo" hidden="">Cuentas de Capital</div>
                    <div style="clear: both"></div>
                    <!--<div class="cont_d_capital" id="ContenedorSeleccion">-->
                    <div class="" id="ContenedorSeleccion" hidden="">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuenta</div>
                            <div id="CajaInput">                                    
                                <input name="txt_cuentas_cap" id="txt_cuentas_cap"  type="text" class="caja_texto" value="" size="25">
                                <input name="cuentas_cap" id="cuentas_cap"  type="hidden" value="">
                            </div>							   							   								
                        </div>
                        <!--Fin-->
                        <?php $txt_vacio="No existe la cuenta en esta gesti&oacute;n";?>
                        <!--Inicio-->                           
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuentas a listar</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta"> 
                                    <table id="tab_cuentas_cap" class="tab_lista_cuentas">                                        
                                        <? foreach ($cuentas_cap as $cap): ?>
                                        <tr data-id="<?=$cap?>">
                                            <?php $txt_cu=FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$cap' and cue_ges_id='$gesid'", "cue_descripcion");?>
                                            <td class="<?php echo $txt_cu==''?'txt_rojo':'';?>"><?=$txt_cu==''?$txt_vacio:$txt_cu;?></td>
                                            <td width="8%"><img class="img_del_cuenta" src="images/retener.png"></td>
                                        </tr>                                        
                                        <? endforeach; ?>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                    </div>
                    
                    <div data-id="d_reserva" class="Subtitulo" hidden="">Cuentas de Reservas</div>
                    <div style="clear: both"></div>
                    <!--<div class="cont_d_reserva" id="ContenedorSeleccion">-->
                    <div class="" id="ContenedorSeleccion" hidden="">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuenta</div>
                            <div id="CajaInput">                                    
                                <input name="txt_cuentas_res" id="txt_cuentas_res"  type="text" class="caja_texto" value="" size="25">
                                <input name="cuentas_res" id="cuentas_res"  type="hidden" value="">
                            </div>							   							   								
                        </div>
                        <!--Fin-->                            
                        <!--Inicio-->                           
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuentas a listar</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta"> 
                                    <table id="tab_cuentas_res" class="tab_lista_cuentas">
                                        <? foreach ($cuentas_res as $res): ?>
                                        
                                        <tr data-id="<?=$res?>">
                                            <?$txt_cu= FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$res' and cue_ges_id='$gesid'", "cue_descripcion");?>
                                            <td class="<?= $txt_cu==''?'txt_rojo':'';?>"><?=$txt_cu==''?$txt_vacio:$txt_cu;?></td>
                                            <td width="8%"><img class="img_del_cuenta" src="images/retener.png"></td>
                                        </tr>                                        
                                        <? endforeach; ?>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                    </div>
                    
                    <div data-id="d_acumulado" class="Subtitulo" hidden="">Cuentas de Resultados Acumulados</div>
                    <div style="clear: both"></div>
                    <!--<div class="cont_d_acumulado" id="ContenedorSeleccion" >-->
                    <div class="" id="ContenedorSeleccion" hidden="">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuenta</div>
                            <div id="CajaInput">                                    
                                <input name="txt_cuentas_acu" id="txt_cuentas_acu"  type="text" class="caja_texto" value="" size="25">
                                <input name="cuentas_acu" id="cuentas_acu"  type="hidden" value="">
                            </div>							   							   								
                        </div>
                        <!--Fin-->                            
                        <!--Inicio-->                           
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuentas a listar</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta"> 
                                    <table id="tab_cuentas_acu" class="tab_lista_cuentas">
                                        <? foreach ($cuentas_acu as $acu): ?>
                                        <tr data-id="<?=$acu?>">
                                            <?php $txt_cu=FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$acu' and cue_ges_id='$gesid'", "cue_descripcion");?>
                                            <td class="<?= $txt_cu==''?'txt_rojo':'';?>"><?=$txt_cu==''?$txt_vacio:$txt_cu;?></td>
                                            <td width="8%"><img class="img_del_cuenta" src="images/retener.png"></td>
                                        </tr>                                        
                                        <? endforeach; ?>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                    </div>
                    <div data-id="d_act_disp" class="Subtitulo">Cuentas de Activo Disponible</div>
                    <div style="clear: both"></div>
                    <div class="cont_d_act_disp" id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuenta</div>
                            <div id="CajaInput">                                    
                                <input name="txt_cuentas_act_disp" id="txt_cuentas_act_disp"  type="text" class="caja_texto" value="" size="25">
                                <input name="cuentas_act_disp" id="cuentas_act_disp"  type="hidden" value="">
                            </div>							   							   								
                        </div>
                        <!--Fin-->                            
                        <!--Inicio-->                           
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Cuentas a listar</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta"> 
                                    <table id="tab_cuentas_act_disp" class="tab_lista_cuentas">
                                        <? foreach ($cuentas_act_disp as $act_disp): ?>
                                        <tr data-id="<?=$act_disp?>">
                                            <?php $txt_cu=FUNCIONES::atributo_bd("con_cuenta", "cue_codigo='$act_disp' and cue_ges_id='$gesid'", "cue_descripcion");?>
                                            <td class="<?= $txt_cu==''?'txt_rojo':'';?>"><?=$txt_cu==''?$txt_vacio:$txt_cu;?></td>
                                            <td width="8%"><img class="img_del_cuenta" src="images/retener.png"></td>
                                        </tr>                                        
                                        <? endforeach; ?>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                    </div>
                    <script>
                        function complete_cuenta(input){
                            var options = {
                                script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cuenta&",
                                varname: "input",
                                json: true,
                                shownoresults: false,
                                maxresults: 6,
                                callback: function(obj) {
                                    agregar_cuenta(obj,input);
                                }
                            };
                            var as_json1 = new bsn.AutoSuggest('txt_'+input, options);                                      
                        }
                        
                        function agregar_cuenta(cuenta,input) {
                            if (!existe_en_lista(cuenta.info,input)) {
                                var fila = '<tr data-id="' + cuenta.info + '">';
                                fila += '<td>' + cuenta.value + '</td>';
                                fila += '<td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                fila += '</tr>';
                                $("#tab_"+input).append(fila);                                
                            }
                            $("#txt_"+input).val("");
                        }
                        
                        function existe_en_lista(id_cuenta,input) {
                            console.log("#tab_"+input+" tr");
                            var lista = $("#tab_"+input+" tr");
                            console.log(lista.size());
                            for (var i = 0; i < lista.size(); i++) {
                                var cuenta = lista[i];
                                var id = $(cuenta).attr("data-id");
                                console.log(id+" - "+id_cuenta);
                                if (id === id_cuenta) {
                                    return true;
                                }
                            }
                            return false;
                        }
                        
                        $(".img_del_cuenta").live('click', function() {
                            $(this).parent().parent().remove();
                        });
                        complete_cuenta("cuentas_cap");
                        complete_cuenta("cuentas_res");
                        complete_cuenta("cuentas_acu");
                        complete_cuenta("cuentas_act_disp");
                        
                    </script>

                    
                    <div id="ContenedorSeleccion">
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
        <?php
        if (!($ver)) {
            ?>
                                        <input type="button" id="guardar-config" class="boton"  value="Guardar">
<!--                                        <input type="reset" class="boton" name="" value="Cancelar">
                                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:history.back();">-->
            <?php
        } else {
            ?>
                                        <input type="button" class="boton" name="" value="Volver" onclick="javascript:history.back();">
                                        <?php
                                    }
                                    ?>
                                </center>
                                <script>
                                    $("#guardar-config").click(function (){
                                        implode_cuentas("cuentas_cap");
                                        implode_cuentas("cuentas_res");
                                        implode_cuentas("cuentas_acu");                                        
                                        implode_cuentas("cuentas_act_disp");
                                        formula_estado_resultado();
                                        formula_balance_general();
                                        formula_estado_flujo();
//                                        return false;
                                        document.frm_sentencia.submit();
                                    });
                                    
                                    function implode_cuentas(input){
                                        var lista = $("#tab_"+input+" tr");
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
                                        $("#"+input).val(data);
                                    }
                                </script>
                            </div>
                        </div>
                    </div>
            </form>
        </div>	
                                    <?php
                                }

                            }
                            ?>