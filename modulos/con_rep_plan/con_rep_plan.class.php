<?php

class CON_REP_PLAN extends BUSQUEDA {

    var $formulario;
    var $mensaje;

    function CON_REP_PLAN() {
        $this->coneccion = new ADO();
        $this->link = 'gestor.php';
        $this->modulo = 'con_rep_plan';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('PLAN DE CUENTAS');
    }

    function dibujar_busqueda() {
        $this->formulario();
    }

    function formulario() {
        $this->formulario->dibujar_cabecera();
        if (!($_POST['formu'] == 'ok')) {
            
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
            <!--MaskedInput-->
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <!--MaskedInput-->						
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=<?php echo $this->modulo; ?>" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">
                        <div class="Subtitulo">Filtro del Reporte</div>
                        <div id="ContenedorSeleccion">
                            <input type="hidden" id="ges_fecha_ini" value=""/>
                            <input type="hidden" id="ges_fecha_fin" value=""/>
                            <div id="ContenedorDiv">                                
                                <div class="Etiqueta" >Gesti&oacute;n</div>
                                <div id="CajaInput">
                                    <select name="gestion" class="caja_texto" id="gestion" style="min-width: 140px">                                        
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo_data("select ges_id as id,ges_descripcion as nombre, concat(ges_fecha_ini,',',ges_fecha_fin) as fechas from con_gestion where ges_eliminado='No' order by ges_fecha_ini desc","fechas", $_SESSION['ges_id']);
                                        ?>
                                    </select>
                                </div>		
                            </div>
                            
                        </div>

                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="hidden" class="boton" name="formu" value="ok">
                                    <input type="submit" class="boton" name="" value="Generar Reporte" >
                                </center>
                            </div>
                        </div>
                    </div>
                </form>	
                <div>
                    <script>
                        jQuery(function($) {
                            $("#fecha_inicio").mask("99/99/9999");
                            $("#fecha_fin").mask("99/99/9999");
                        });
                    </script>

                    <?php
                }

                if ($_POST['formu'] == 'ok')
                    $this->mostrar_reporte();
            }

            function barra_de_impresion() {
                $pagina = "'contenido_reporte'";

                $page = "'about:blank'";

                $extpage = "'reportes'";

                $features = "'left=100,width=800,height=500,top=0,scrollbars=yes'";

                $extra1 = "'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
			  </head>
			  <body>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
                $extra1.=" <a href=javascript:imprimir_estado();>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
                $extra2 = "'</center></body></html>'";

                $myday = setear_fecha(strtotime(date('Y-m-d')));

                echo '	<table align=right border=0><tr>
                        <td>
                            <a href="javascript:document.formulario.submit();">
                                <img src="images/actualizar.png" width="20" title="ACTUALIZAR"/>
                            <a/>
                        </td>
                        <td><a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
                        c.document.write(' . $extra1 . ');
                        var dato = document.getElementById(' . $pagina . ').innerHTML;
                        c.document.write(dato);
                        c.document.write(' . $extra2 . '); c.document.close();
                        ">
                      <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR"></a>
                      <a href="javascript:exportar_excel()"><img src="images/excel.png" align="right" width="20" border="0" title="EXPORTAR EXCEL"></a>
                      </td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=' . $this->modulo . '\';"></td></tr></table><br><br>
		';
            }

            function mostrar_reporte() {                
                $this->barra_de_impresion();
                ?>
                <div id="contenido_reporte" style="clear:both;">
                    <script src="js/jquery-1.3.2.min.js"></script>
                    <script>
                        function exportar_excel(){
                            $('#MyTable').attr('border','1');
                            window.open('data:application/vnd.ms-excel,' + escape($('#cont-table').html()));
//                            e.preventDefault();
                            $('#MyTable').attr('border','0');
                        }

                        function imprimir_estado(){
                            $(".det_estado").hide();
                            window.print();
                            $(".det_estado").show();
                        }                
                    </script>
                    <input type="hidden" id="paramentros" value="<?php echo $parametros?>"/>
                    <center>
                        <?php
                        $ges_id = $_POST['gestion'];
                        $nombre_empresa=  FUNCIONES::parametro('razon_social',$ges_id);
                        $datos_empresa=  FUNCIONES::parametro('direccion');
                        ?>
                        <table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >                            
                            <tr>
                                <td width="40%">
                                    <strong><?php echo $nombre_empresa; ?></strong></br>
                                    <?php echo $datos_empresa; ?></br></br>
                                </td>
                                <td width="20%">
                                    <p align="center" >
                            <center>
                                <strong><h3>PLAN DE CUENTAS</h3></strong></br>
                                <strong><?php echo FUNCIONES::atributo_bd("con_gestion", "ges_id=" . $_POST['gestion'], "ges_descripcion") ?></strong>
                                
                            </center>
                            </p>
                            </td>
                            <td width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
                            </tr> 
                        </table>
                        <?php

                        $this->mostrar_plan_cuentas($_POST[gestion]);
                        ?>
                    </center>
                    <br>
                    <table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?></td></tr>
                    </table>
                </div></br>
                <?php
            }
            
//            function mostrar_tabla_resultados($cu_ingreso,$cu_costo,$cu_gasto){
            function mostrar_plan_cuentas($gestion){
                $monedas=  FUNCIONES::objetos_bd_sql("select * from con_moneda");
                $amoneda=array();
                for ($i = 0; $i<$monedas->get_num_registros(); $i++) {
                    $_moneda= $monedas->get_objeto();
                    $amoneda[$_moneda->mon_id]=$_moneda->mon_Simbolo;
                    $monedas->siguiente();
                }
                ?>
                <div id="cont-table">
                    <table   width="95%" id="MyTable" class="tablaReporte" cellpadding="0" cellspacing="0">
                        <thead>                        
                            <tr>                                    
                                <th><b>#</b></th>
                                <th><b>Codigo</b></th>
                                <th><b>Descripcion</b></th>
                                <th><b>Moneda</b></th>
                                <th><b>Nivel</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $cuentas =  FUNCIONES::objetos_bd_sql("select * from con_cuenta where cue_ges_id=$gestion and cue_codigo!='' order by cue_codigo asc"); ?>
                            <?php $num_item=1; ?>
                            <?php for ($i=0;$i<$cuentas->get_num_registros();$i++){ ?>
                                <?php $objeto=$cuentas->get_objeto(); ?>
                                    <tr>
                                        <td ><i><?php echo $num_item;?></i></td>
                                        <td ><?php echo $objeto->cue_codigo;?></td>
                                        <td ><?php echo $this->espacio(($objeto->cue_tree_level-1)*5).$objeto->cue_descripcion;?></td>
                                        <td ><?php echo $objeto->cue_tipo=='Movimiento'?$amoneda[$objeto->cue_mon_id]:'';?></td>
                                        <td ><?php echo $objeto->cue_tree_level;?></td>
                                    </tr>
                                <?php $cuentas->siguiente(); ?>
                                <?php $num_item++; ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
                    
            function espacio($n){
                $sp="";
                for ($i = 0; $i < $n; $i++) {
                    $sp.="&nbsp;";
                }
                return $sp;
            }
            
            function obtener_sumas_cuenta($id_cuenta) {
                $cuentas = new ADO();
                $sql = "select cue_id, cue_codigo, cue_descripcion, cue_tipo, cue_padre_id, cue_tree_level 
                      from con_cuenta where cue_padre_id=$id_cuenta order by cue_codigo;";
                $cuentas->ejecutar($sql);
                $monto = 0;
                $detalles = array();
                for ($i=0; $i < $cuentas->get_num_registros(); $i++) {
                    $cuenta = $cuentas->get_objeto();
                    $cuenta_m = new stdClass();
                    $cuenta_m->id = $cuenta->cue_id;
                    $cuenta_m->codigo = $cuenta->cue_codigo;
                    $cuenta_m->descripcion = $cuenta->cue_descripcion;
                    $cuenta_m->level = $cuenta->cue_tree_level;
                    $cuenta_m->tipo = $cuenta->cue_tipo;
                    if ($cuenta->cue_tipo == "Movimiento") {                        
                        $suma = $this->total_debe_haber_cuenta($cuenta->cue_id);
                        $saldo = $suma->tdebe - $suma->thaber;                        
                        $cuenta_m->tmonto = $saldo;
                        $cuenta_m->detalles = array();
                        $monto+=$saldo;
                        if($i==$cuentas->get_num_registros()-1){
                            $cuenta_m->ultimo=true; 
                        }
                    }else{
                        $calculo=$this->obtener_sumas_cuenta($cuenta->cue_id);
                        $cuenta_m->tmonto = $calculo->tmonto;
                        $cuenta_m->detalles = $calculo->detalles;
                        $monto+=$calculo->tmonto;
                    }
                    $detalles[]=$cuenta_m;
                    $cuentas->siguiente();
                }
                $resp = new stdClass();
                $resp->tmonto = $monto;
                $resp->detalles = $detalles;
                return $resp;
            }

            function total_debe_haber_cuenta($id_cuenta) {
                $conversor = new convertir();
                $conec_det = new ADO();
                $filtro = "";
                if ($_POST['fecha_inicio'] <> "") {
                    $filtro.=" and cmp_fecha >= '" . $conversor->get_fecha_mysql($_POST['fecha_inicio']) . "' ";

                    if ($_POST['fecha_fin'] <> "") {
                        $filtro.=" and cmp_fecha <='" . $conversor->get_fecha_mysql($_POST['fecha_fin']) . "' ";
                    }
                } else {
                    if ($_POST['fecha_fin'] <> "") {
                        $filtro.=" and cmp_fecha <='" . $conversor->get_fecha_mysql($_POST['fecha_fin']) . "' ";
                    }
                }

                if ($_POST['moneda_hecho'] <> "") {
                    $filtro.=" and cmp_mon_id = '" . $_POST['moneda_hecho'] . "' ";
                }

                $ges_id = $_POST['gestion'];
                $fcentro_c=$_POST['id_centroc']!=''?' and cde_cco_id='.$_POST['id_centroc']:'';
                $and_une_id="";
                if($_POST[cde_une_id]){
                    $and_une_id=" and cde_une_id='$_POST[cde_une_id]'";
                }							
                $sql = "
                        select 
                            cde_valor
                        from
                            con_comprobante c, con_comprobante_detalle cd
                        where
                            c.cmp_id=cd.cde_cmp_id and c.cmp_eliminado='No' and cd.cde_cue_id=$id_cuenta $filtro $fcentro_c
                            and c.cmp_ges_id=$ges_id and cde_mon_id='" . $_POST['moneda_reporte'] . "' $and_une_id
                        ";
                //echo $sql." <br>";
                $conec_det->ejecutar($sql);
                $num = $conec_det->get_num_registros();
                $total_debe = 0;
                $total_haber = 0;
                $saldo = 0;
                for ($i = 0; $i < $num; $i++) {
                    $objeto = $conec_det->get_objeto();
                    $debe = 0;
                    $haber = 0;
                    if ($objeto->cde_valor >= 0) {
                        $debe = floatval($objeto->cde_valor);
                        $saldo = $saldo + $debe;
                        $total_debe+=$debe;
                    }
                    if ($objeto->cde_valor < 0) {
                        $haber = floatval($objeto->cde_valor) * -1;
                        $saldo = $saldo - $haber;
                        $total_haber+=$haber;
                    }
                    $conec_det->siguiente();
                }
                $tdh = new stdClass();
                $tdh->tdebe = $total_debe;
                $tdh->thaber = $total_haber;
                return $tdh;
            }



    function movimiento($cuenta,$max_level,$signo=1) {
        $sw=true;
        if(!isset($_POST['saldo_cero']) || !$_POST['saldo_cero']){
            if($cuenta->tmonto==0){
                $sw=false;
            }
        }
        if($sw){
        ?>
            <tr>
                <td style="" >
                    <?php echo $cuenta->codigo; ?>
                </td>                    
                <td style=" <?php if($cuenta->tipo!="Movimiento") echo 'font-weight: bold'?>" >
                    <?php
                    if($cuenta->tipo=="Movimiento"){
                    ?>
                    <a style="float: left; padding-right: 3px" href="javascript:void(0);" class="det_estado" data-id="<?php echo $cuenta->id;?>"><img src="images/b_browse.png" width="14px" /></a>
                    <?php
                    }
                    ?>
                    <div style="float: left"><?php echo $this->espacio(($cuenta->level-1)*2). $cuenta->descripcion; ?></div>                    
                </td>
                <?php
                    for($i=1;$i<=$max_level;$i++){
                ?>
                    <?if($i==($max_level+1)-$cuenta->level){?>
                    <td style="border-right:none; border-left: 1px solid #d6d8d7; <?php if($cuenta->tipo!="Movimiento") {echo 'font-weight: bold;';} if($cuenta->ultimo){echo 'border-bottom: 1px solid #000;';} ?>" >
                        <?php echo number_format($cuenta->tmonto*$signo, 2, ".", ","); ?>&nbsp;
                    </td>
                    <?php }else{?>
                    <td style="border-right:none; border-left: 1px solid #d6d8d7;">
                        &nbsp;
                    </td>	
                    <?php }?>
               <?php
                    }
               ?>                    
            </tr>
    <?php
        }
        $detalles=$cuenta->detalles;
        for($i=0;$i<  count($detalles);$i++){
            $cuenta_h=$detalles[$i];
            $this->movimiento($cuenta_h, $max_level,$signo);
        }
    }                   


    function totales($formula_val,$max_level) {
        ?>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo 1+$max_level?>" align="right">
                            T O T A L
                        </td>
                        <td>
                            <?php 
                            $total=0;
                            foreach ($formula_val as $campo) {
                                $cuenta=$campo->cuenta;
                                $total+=($cuenta->tmonto*$campo->op)*$campo->signo;
                            }
                            echo number_format($total,2);                             
                            ?>&nbsp;
                        </td>                        
                    </tr>
                </tfoot>
        <?php
    }

    

}
?>