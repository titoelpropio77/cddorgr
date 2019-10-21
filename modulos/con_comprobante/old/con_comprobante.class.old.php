<?php
class con_comprobante extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $tipo_cambio;
    var $usu;
    var $_sum_cuentas;

    function CON_COMPROBANTE() {
        //permisos
        $this->ele_id = 108;
        $this->busqueda();
        $this->tipo_cambio = $this->tc;
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;
        $this->coneccion = new ADO();
        $this->arreglo_campos[0]["nombre"] = "cmp_nro";
        $this->arreglo_campos[0]["texto"] = "Nro";
        $this->arreglo_campos[0]["tipo"] = "numero";

        $this->arreglo_campos[1]["nombre"] = "cmp_nro_documento";
        $this->arreglo_campos[1]["texto"] = "Nro de Doc.";
        $this->arreglo_campos[1]["tipo"] = "cadena";
        $this->arreglo_campos[1]["tamanio"] = 50;

        $this->arreglo_campos[2]["nombre"] = "cmp_referido";
        $this->arreglo_campos[2]["texto"] = "Referido";
        $this->arreglo_campos[2]["tipo"] = "cadena";
        $this->arreglo_campos[2]["tamanio"] = 50;

        $this->arreglo_campos[3]["nombre"] = "cmp_glosa";
        $this->arreglo_campos[3]["texto"] = "Concepto";
        $this->arreglo_campos[3]["tipo"] = "cadena";
        $this->arreglo_campos[3]["tamanio"] = 50;

        $this->arreglo_campos[4]["nombre"] = "cmp_fecha";
        $this->arreglo_campos[4]["texto"] = "Fecha";
        $this->arreglo_campos[4]["tipo"] = "fecha";
        
        $this->arreglo_campos[5]["nombre"] = "cmp_tco_id";
        $this->arreglo_campos[5]["texto"] = "Tipo";
        $this->arreglo_campos[5]["tipo"] = "comboarray";
        $this->arreglo_campos[5]["valores"] = "1,2,3,4:Ingreso,Egreso,Diario,Ajuste";
        
        $this->arreglo_campos[6]["nombre"] = "cmp_peri_id";
        $this->arreglo_campos[6]["texto"] = "Periodo";
        $this->arreglo_campos[6]["tipo"] = "combosql";
        $this->arreglo_campos[6]["sql"] = "select pdo_id as codigo, pdo_descripcion as descripcion from con_periodo where pdo_ges_id='".$_SESSION['ges_id']."' and pdo_eliminado='No'";
        
        $this->link = 'gestor.php';
        $this->modulo = 'con_comprobante';
        $this->formulario = new FORMULARIO();
        $this->formulario->set_titulo('ASIENTO CONTABLE');
        $this->usu = new USUARIO;
    }
    
    function dibujar_busqueda() {
        $this->formulario->dibujar_cabecera();
        $this->dibujar_listado();
    }

    function set_opciones() {
        $nun = 0;

        if ($this->verificar_permisos('VER')) {
//            echo "VER - ";
            $this->arreglo_opciones[$nun]["tarea"] = 'VER';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_search.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER';
            $nun++;
        }

        if ($this->verificar_permisos('MODIFICAR')) {
//            echo "MODIFICAR - ";
            $this->arreglo_opciones[$nun]["tarea"] = 'MODIFICAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_edit.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'MODIFICAR';
            $nun++;
        }

        if ($this->verificar_permisos('ELIMINAR')) {
//            echo "ELIMINAR";
            $this->arreglo_opciones[$nun]["tarea"] = 'ELIMINAR';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_drop.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'ELIMINAR';
            $nun++;
        }
    }

    function dibujar_listado() {
        $sql = "SELECT cmp_id, tco_descripcion, mon_id, mon_titulo, mon_Simbolo, cmp_nro, cmp_nro_documento, cmp_fecha,
                ges_descripcion, pdo_id, pdo_descripcion, cmp_glosa, cmp_referido, cmp_tabla,cmp_usu_cre
                FROM con_comprobante cmp, con_periodo p, con_gestion g, con_moneda m, con_tipo_comprobante tc
                where cmp.cmp_tco_id= tc.tco_id and cmp.cmp_mon_id=m.mon_id and cmp.cmp_ges_id=g.ges_id and cmp.cmp_peri_id=pdo_id and cmp_eliminado='No' ";
        $this->set_sql($sql," order by  cmp_id desc ");
//        $this->set_sql($sql," order by cmp_id desc ");
        $this->set_opciones();
        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Tipo</th>
            <th>Usuario</th>
            <th>Nro.</th>
            <th>Nro. de Doc.</th>
            <th>Fecha</th>
            <th>Gesti&oacute;n</th>
            <th>Periodo</th>
            <th>Moneda</th>               
            <th>Referido/Pagado</th>
            <th>Glosa</th>         
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
//        $conversor = new convertir();

        for ($i = 0; $i < $this->numero; $i++) {
            
            $objeto = $this->coneccion->get_objeto();
            $operaciones=array();
            if($objeto->cmp_tabla!='' ||$objeto->tco_descripcion=='Ajustes' ){
                $operaciones[]="MODIFICAR";
            }
            if($objeto->cmp_tabla!='' ){
                $operaciones[]="ELIMINAR";
            }
            echo '<tr>';
            echo "<td>";
            echo $objeto->tco_descripcion ;
            echo "</td>";
            echo "<td>";
            echo $objeto->cmp_usu_cre ;
            echo "</td>";
            echo "<td>";
            echo $objeto->cmp_nro;
            echo "</td>";
            echo "<td>";
            echo $objeto->cmp_nro_documento;
            echo "</td>";
            echo "<td>";
            echo FUNCIONES::get_fecha_latina($objeto->cmp_fecha);
            echo "</td>";
            echo "<td>";
            echo $objeto->ges_descripcion;
            echo "</td>";
            echo "<td>";
            echo $objeto->pdo_descripcion;
            echo "</td>";
            echo "<td>";
            echo $objeto->mon_titulo;
            echo "</td>";
            echo "<td>";
            echo $objeto->cmp_referido;
            echo "</td>";
            echo "<td>";
            echo $objeto->cmp_glosa;
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->cmp_id,"",$operaciones);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();
        $sql = "select * from con_comprobante
				where cmp_id= " . $_GET['id'];
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
        $_POST['comp_nro'] = $objeto->cmp_nro;
        $_GET['form'] = FUNCIONES::atributo_bd("con_tipo_comprobante", "tco_id=$objeto->cmp_tco_id", "tco_descripcion"); //$objeto->tco_descripcion;        
        $_POST['comp_tco_id'] = $objeto->cmp_tco_id;
        $_POST['comp_nro_documento'] = $objeto->cmp_nro_documento;
        $_POST['comp_fecha'] = FUNCIONES::get_fecha_latina($objeto->cmp_fecha);
        $_POST['cmp_ges_id'] = $objeto->cmp_ges_id;
        $_POST['cmp_peri_id'] = $objeto->cmp_peri_id;
        $_POST['comp_mon_id'] = $objeto->cmp_mon_id;
        $_POST['comp_referido'] = $objeto->cmp_referido;
        $_POST['comp_glosa'] = $objeto->cmp_glosa;
        $_POST['comp_forma_pago']=$objeto->cmp_forma_pago;
        $_POST['comp_ban_id']=$objeto->cmp_ban_id;
        $_POST['comp_ban_char']=$objeto->cmp_ban_char;
        $_POST['comp_ban_nro']=$objeto->cmp_ban_nro;
        $_POST['comp_une_id']=$objeto->cmp_une_id;
        
        $cmp_id=$objeto->cmp_id;
        $sql = "select * from con_comprobante_detalle
				where cde_mon_id=$objeto->cmp_mon_id and cde_cmp_id= " . $_GET['id'];
        $conec->ejecutar($sql);
        $num_filas = $conec->get_num_registros();
        $_POST['num_filas'] = $num_filas;
        $_POST['cont_filas'] = $num_filas+1;
        $tdebe = 0.0;
        $thaber = 0.0;
        
        for ($i = 1; $i <= $num_filas; $i++) {
            $detalle = $conec->get_objeto();
//            echo _PRINT::pre($detalle);
            $_POST["cuen$i"] = $detalle->cde_cue_id;
            $_POST["cuen_nombre$i"] = FUNCIONES::atributo_bd("con_cuenta", "cue_id=$detalle->cde_cue_id", "cue_descripcion");
            $valor = floatval($detalle->cde_valor);
            if ($valor >= 0) {
                $_POST["debe$i"] = $valor;
                $tdebe = $tdebe + $valor;
            }
            if ($valor < 0) {
                $_POST["haber$i"] = $valor * -1;
                $thaber = $thaber + ($valor * -1);
            }
//            echo $_POST["debe$i"].'<br>';
            $_POST["glosa$i"] = $detalle->cde_glosa;

            $val_can = floatval($detalle->cde_can_id);
            if ($val_can > 0) {
                $_POST["ca$i"] = $detalle->cde_can_id;
                $_POST["ca_nombre$i"] = FUNCIONES::atributo_bd("con_cuenta_ca", "can_id=$detalle->cde_can_id", "can_descripcion");
            }

            $val_cfl = floatval($detalle->cde_cfl_id);
            if ($val_cfl > 0) {
                $_POST["cf$i"] = $detalle->cde_cfl_id;
                $_POST["cf_nombre$i"] = FUNCIONES::atributo_bd("con_cuenta_cf", "cfl_id=$detalle->cde_cfl_id", "cfl_descripcion");
            }

            $val_cco = floatval($detalle->cde_cco_id);
            if ($val_cco > 0) {
                $_POST["cc$i"] = $detalle->cde_cco_id;
                $_POST["cc_nombre$i"] = FUNCIONES::atributo_bd("con_cuenta_cc", "cco_id=$detalle->cde_cco_id", "cco_descripcion");
            }
            
            $val_une= floatval($detalle->cde_une_id);
            if ($val_une > 0) {
                $_POST["une$i"] = $detalle->cde_une_id;
            }
            
            if($detalle->cde_libro!=''){
                $_POST['data_lib_' . $i] = $detalle->cde_libro;
            }
            if($detalle->cde_cu!=''){
                $_POST['data_cu_' . $i] = $detalle->cde_cu;
            }
            if($detalle->cde_idpadre!='' && $detalle->cde_idpadre!='0'){
                $_POST['data_idpadre_' . $i] = $detalle->cde_idpadre;
            }
            
            if($detalle->cde_libro=='v' || $detalle->cde_libro=='c'){
//                echo $this->obtener_libro_v_c($cmp_id, $i);
                $_POST['data_obj_' . $i] = $this->obtener_libro_v_c($cmp_id, $i);
            }elseif($detalle->cde_libro=='r'){
                $_POST['data_obj_' . $i] = $this->obtener_libro_retencion($cmp_id, $i);
            }
            
            $conec->siguiente();
        }
        $_POST["tdebe"] = $tdebe;
        $_POST["thaber"] = $thaber;
        
    }
    
    function obtener_libro_v_c($cmp_id, $sec){
        $sql="select * from con_libro
                where lib_cmp_id='$cmp_id' and lib_cde_sec='$sec';";

        $libro=FUNCIONES::objeto_bd_sql($sql);

        $det='{';
        if($libro->lib_libro=='Compra'){
            $det.='"tipo":"'.$libro->lib_tipo.'",';
        }
        $det.='"fecha":"'.  FUNCIONES::get_fecha_latina($libro->lib_fecha).'",';
        $det.='"cta_disp":"'. $libro->lib_cuenta_act_disp.'",';
        $det.='"nit":"'.$libro->lib_nit.'",';
        $det.='"aut":"'.$libro->lib_nro_autorizacion.'",';
        $det.='"control":"'.$libro->lib_cod_control.'",';
        $det.='"nro_fact":"'.$libro->lib_nro_factura.'",';
        if($libro->lib_libro=='Compra'){
            $det.='"nro_pol":"'.$libro->lib_nro_poliza.'",';
        }
        $det.='"cliente":"'.str_replace('"', '\"', $libro->lib_cliente) .'",';
        $det.='"tot_fact":"'.round($libro->lib_tot_factura,2).'",';
        $det.='"tot_ice":"'.round($libro->lib_ice,2).'",';
        $det.='"imp_ext":"'.round($libro->lib_imp_exentos,2).'",';
        $det.='"imp_neto":"'.round($libro->lib_imp_neto,2).'",';
        $det.='"iva":"'.round($libro->lib_iva,2).'"';
        
        if($libro->lib_libro=='Venta'){
            $det.=',"estado":"'.$libro->lib_estado.'"';
        }
        if($libro->lib_libro=='Compra'){
            
            $det.=',"pagos":'.$libro->lib_pagos_cc.'';
        }
        $det.="}";
        return $det;
    }
    function obtener_libro_retencion($cmp_id, $sec){
        $sql="select * from con_retencion
                where ret_cmp_id='$cmp_id' and ret_cde_sec='$sec';";
//        echo $sql;
        $retencion=FUNCIONES::objeto_bd_sql($sql);

        $det='{';        
        $det.='"fecha":"'.  FUNCIONES::get_fecha_latina($retencion->ret_fecha).'",';
        $det.='"cta_disp":"'.$retencion->ret_cuenta_act_disp.'",';
        $det.='"neto":"'.round($retencion->ret_neto,2).'",';
        $det.='"tipo":"'.$retencion->ret_tipo.'",';
        $det.='"asumido":"'.$retencion->ret_asumido.'",';        
        $det.='"it":"'.round($retencion->ret_it,2).'",';
        $det.='"iue":"'.round($retencion->ret_iue,2).'",';
        $det.='"res":"'.round($retencion->ret_result,2).'"';
        $det.="}";
        return $det;
    }

    function datos() {
        if ($_POST) {
            $tipoc = $_GET['form'];
            $num = 0;
            $valores[$num]["etiqueta"] = "Periodo";
            $valores[$num]["valor"] = $_POST['cmp_peri_id'];
            $valores[$num]["tipo"] = "numero";
            $valores[$num]["requerido"] = true;
            $num++;
            
//            $valores[$num]["etiqueta"] = "Unidad de Negocio";
//            $valores[$num]["valor"] = $_POST['comp_une_id'];
//            $valores[$num]["tipo"] = "numero";
//            $valores[$num]["requerido"] = true;
//            $num++;

            if ($tipoc == "Ingreso" || $tipoc == "Egreso" || $tipoc == "Diario") {
                $valores[$num]["etiqueta"] = "Moneda";
                $valores[$num]["valor"] = $_POST['comp_mon_id'];
                $valores[$num]["tipo"] = "numero";
                $valores[$num]["requerido"] = true;
                $num++;
            }
                

            if ($tipoc == "Ingreso" || $tipoc == "Egreso") {
                $valores[$num]["etiqueta"] = "Glosa";
            } else if ($tipoc == "Diario" || $tipoc == "Ajustes") {
                $valores[$num]["etiqueta"] = "Por los Siguiente";
            }
            $valores[$num]["valor"] = $_POST['comp_glosa'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;
            
            if ($tipoc == "Ingreso" || $tipoc == "Egreso") {
                $fp=$_POST['comp_forma_pago'];
                
                if($fp=='Cheque'){
                    if($tipoc=="Ingreso"){
                        $valores[$num]["etiqueta"] = "Banco";
                        $valores[$num]["valor"] = $_POST['comp_ban_char'];
                        $valores[$num]["tipo"] = "todo";
                        $valores[$num]["requerido"] = true;
                        $num++;                        
                    }elseif($tipoc=="Egreso"){
                        $valores[$num]["etiqueta"] = "Banco";
                        $valores[$num]["valor"] = $_POST['comp_ban_id'];
                        $valores[$num]["tipo"] = "numero";
                        $valores[$num]["requerido"] = true;
                        $num++;
                    }
                    $valores[$num]["etiqueta"] = "Nro. de Cheque";
                    $valores[$num]["valor"] = $_POST['comp_ban_nro'];
                    $valores[$num]["tipo"] = "todo";
                    $valores[$num]["requerido"] = true;
                    $num++;
                }
                if($fp=='Deposito'){
                    if($tipoc=="Ingreso"){
                        $valores[$num]["etiqueta"] = "Banco";
                        $valores[$num]["valor"] = $_POST['comp_ban_id'];
                        $valores[$num]["tipo"] = "numero";
                        $valores[$num]["requerido"] = true;
                        $num++;                        
                    }elseif($tipoc=="Egreso"){
                        $valores[$num]["etiqueta"] = "Banco";
                        $valores[$num]["valor"] = $_POST['comp_ban_char'];
                        $valores[$num]["tipo"] = "todo";
                        $valores[$num]["requerido"] = true;
                        $num++;
                    }
                    $valores[$num]["etiqueta"] = "Nro. de Deposito";
                    $valores[$num]["valor"] = $_POST['comp_ban_nro'];
                    $valores[$num]["tipo"] = "todo";
                    $valores[$num]["requerido"] = true;
                    $num++;
                }
                if($fp=='Transferencia' ){
                    if($tipoc=="Ingreso"){
                        $valores[$num]["etiqueta"] = "Banco Destino";
                    }elseif($tipoc=="Egreso"){
                        $valores[$num]["etiqueta"] = "Banco Origen";
                    }                    
                    $valores[$num]["valor"] = $_POST['comp_ban_id'];
                    $valores[$num]["tipo"] = "numero";
                    $valores[$num]["requerido"] = true;
                    $num++;
                    
                    if($tipoc=="Ingreso"){
                        $valores[$num]["etiqueta"] = "Banco Origen";
                    }elseif($tipoc=="Egreso"){
                        $valores[$num]["etiqueta"] = "Banco Destino";
                    }                    
                    $valores[$num]["valor"] = $_POST['comp_ban_char'];
                    $valores[$num]["tipo"] = "todo";
                    $valores[$num]["requerido"] = true;    
                    $num++;
                    $valores[$num]["etiqueta"] = "Nro. de Transferencia";
                    $valores[$num]["valor"] = $_POST['comp_ban_nro'];
                    $valores[$num]["tipo"] = "todo";
                    $valores[$num]["requerido"] = true;
                    $num++;
                }                
            }
            $val = NEW VALIDADOR;
            $this->mensaje = "";
            $sw=true;
            $msj='';
            if($_GET[id]){
                $sql="select *  from con_comprobante where cmp_id='$_GET[id]'";
                $_cmp=  FUNCIONES::objeto_bd_sql($sql);
                if($_cmp->cmp_peri_id!=$_POST['cmp_peri_id']){
                    $nfecha=$_POST[comp_fecha];
                    $afecha=  FUNCIONES::get_fecha_latina($_cmp->cmp_fecha);
                    $msj="<li>El <b>Periodo</b> a modificar de la fecha <b>$nfecha</b> es diferente al periodo de la fecha <b>$afecha</b> en el que se creo el comprobante</li>";
                    $sw=false;
                }
            }
            
            if ($val->validar($valores) && $sw) {
                return true;
            } else {
                $this->mensaje = $val->mensaje.$msj;
                return false;
            }
            return true;
        }
        return false;
    }
    function total_debe_haber_cuenta($id_cuenta,$moneda, $ges_id, $fecha_inicio,$fecha_fin) {
        $conec_det = new ADO();
        $filtro = "";        
        if ($fecha_inicio <> "") {
            $filtro.=" and cmp_fecha >= '" . $fecha_inicio . "' ";

            if ($fecha_fin <> "") {
                $filtro.=" and cmp_fecha <='" . $fecha_fin . "' ";
            }
        } else {
            if ($fecha_fin <> "") {
                $filtro.=" and cmp_fecha <='" . $fecha_fin . "' ";
            }
        }
//        $ges_id = $_POST['gestion'];
        $sql = "
                select 
                    cde_valor
                from
                    con_comprobante c, con_comprobante_detalle cd
                where
                    c.cmp_id=cd.cde_cmp_id and cd.cde_cue_id=$id_cuenta $filtro 
                    and c.cmp_ges_id=$ges_id and cde_mon_id='" . $moneda . "';						
                ";
//                echo $sql." <br>";
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
    
    function generar_cmp_ajuste_dif_camb(){
        $ges_id = $_SESSION['ges_id'];
        $mon_id = 2;
        $fecha_fin =  FUNCIONES::get_fecha_mysql($_POST['comp_fecha']);
        $_cmp = FUNCIONES::objeto_bd_sql("select * from con_comprobante where cmp_tco_id=4 and cmp_eliminado='No' and cmp_fecha='$fecha_fin'");
        if($_cmp!=null){
            $this->resetear_detalle();
            $this->mensaje="Ya existe un comprobante de ajuste por <b>diferencia de cambio</b> realizado para este periodo";
            return;
        }
        
        $fecha_ini=  FUNCIONES::atributo_bd_sql("select ges_fecha_ini from con_gestion where ges_id='$ges_id';");        
        $txt_usd=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$fecha_fin' and tca_mon_id=2;");
        $usd_f=0;
        if($txt_usd){
            $usd_f=$txt_usd*1;
        }        
        $sql_cu = "select * from con_cuenta where cue_tipo='Movimiento' and cue_mon_id='$mon_id' and cue_ges_id='$ges_id' order by cue_codigo;";
        $cuentas_usd = FUNCIONES::objetos_bd_sql($sql_cu);        
        $sum_cuentas = array();        
        for($i=0;$i<$cuentas_usd->get_num_registros();$i++){
            $cuenta=$cuentas_usd->get_objeto();            
            $tdh_bs=  $this->total_debe_haber_cuenta($cuenta->cue_id, 1, $ges_id, $fecha_ini, $fecha_fin);
            $tdh_usd=$this->total_debe_haber_cuenta($cuenta->cue_id, 2, $ges_id, $fecha_ini, $fecha_fin);
//            _PRINT::pre($tdh_bs);
//            _PRINT::pre($tdh_usd);
            $saldo_bs=$tdh_bs->tdebe-$tdh_bs->thaber;            
            $saldo_usd=$tdh_usd->tdebe-$tdh_usd->thaber;            
            $saldo_usd_to_bs=$saldo_usd*$usd_f;
            $_cu=new stdClass();
            $_cu->id=$cuenta->cue_id;
            $_cu->valor=$saldo_usd_to_bs-$saldo_bs;            
            $sum_cuentas[]=$_cu;            
            $cuentas_usd->siguiente();
        }
//        _PRINT::pre($sum_cuentas);
        if(count($sum_cuentas)>0){
            $i=1;
            $tdebe=0;
            $thaber=0;
            
            $g_tdebe=0;
            $g_thaber=0;
            
            foreach ($sum_cuentas as $cuenta) {
                if($cuenta->valor!=0){
                    $_POST["cuen$i"] = $cuenta->id;
                    $_POST["cuen_nombre$i"] = FUNCIONES::atributo_bd("con_cuenta", "cue_id=$cuenta->id", "cue_descripcion");
                    $valor = floatval($cuenta->valor);

                    if($valor>0){
                        $_POST["debe$i"] = round($valor,2);
                        $_POST["haber$i"] = '';
                        $tdebe = $tdebe + round($valor,2);;
                    }else{
                        $_POST["debe$i"] = '';
                        $_POST["haber$i"] = round($valor*(-1),2);
                        $thaber = $thaber + round($valor*(-1),2);;
                    }                

                    $_POST["glosa$i"] = $_POST['comp_glosa'];
                    $i++;
                }
            }
            $g_tdebe=$tdebe;
            $g_thaber=$thaber;
            if($tdebe>0 || $thaber>0){
                $dif_cambio=  FUNCIONES::parametro("dif_cambio", $ges_id);            
                $sql="select * from con_cuenta where cue_codigo='$dif_cambio' and cue_ges_id='".$ges_id."'";
//                echo $sql;
                $cu_dif=  FUNCIONES::objeto_bd_sql($sql);
                if($tdebe>0){
                    $_POST["cuen$i"] = $cu_dif->cue_id;
                    $_POST["cuen_nombre$i"] = $cu_dif->cue_descripcion;
//                    $valor = floatval($this->sumatoria($cuentas));
                    $_POST["debe$i"] = '';
                    $_POST["haber$i"] = round($tdebe,2);
                    $g_thaber=$tdebe+$thaber; 
                    $_POST["glosa$i"] = $_POST['comp_glosa'];
                    $i++;
                }
                if($thaber>0){
                    $_POST["cuen$i"] = $cu_dif->cue_id;
                    $_POST["cuen_nombre$i"] = $cu_dif->cue_descripcion;
//                    $valor = floatval($this->sumatoria($cuentas));
                    $_POST["debe$i"] = round($thaber,2);
                    $_POST["haber$i"] = '';
                    $g_tdebe=$thaber+$tdebe;
                    $_POST["glosa$i"] = $_POST['comp_glosa'];
                    $i++;
                }
                $_POST["tdebe"] = round($g_tdebe,2);
                $_POST["thaber"] = round($g_thaber,2);
                $_POST['num_filas'] = $i-1;
                $_POST['cont_filas'] = $i;                
            }else{
                $this->mensaje="No se pudo realizar un asiento de ajuste debido a que no hubo movimiento en las cuentas de Dolar o no hubo variacion del tipo de cambio";
                $_POST["tdebe"] = round($tdebe,2);
                $_POST["thaber"] = round($thaber,2);
                $_POST['num_filas'] = $i;
                $_POST['cont_filas'] = $i+1;
            }
        }else{
            $_POST["tdebe"] = round(0,2);
            $_POST["thaber"] = round(0,2);
            $_POST['num_filas'] = 1;
            $_POST['cont_filas'] = 2;
        }        
    }    

    function generar_cmp_ajuste_ufv(){
//        _PRINT::pre($_POST);
        $ges_id = $_SESSION['ges_id'];
        $mon_id = 3;
        $per_ajuste = $_POST['cmp_peri_id'];
        $fecha=  FUNCIONES::get_fecha_mysql($_POST['comp_fecha']);
        $txt_ufv=  FUNCIONES::atributo_bd_sql("select tca_valor as campo from con_tipo_cambio where tca_fecha='$fecha' and tca_mon_id=3;");
        $ufv_f=0;
        if($txt_ufv){
            $ufv_f=$txt_ufv*1;
        }
        
        $sql_pdos = "select * from con_periodo where pdo_ges_id='$ges_id' and pdo_eliminado='No' order by pdo_fecha_inicio asc;";
        $lis_periodos = FUNCIONES::objetos_bd_sql($sql_pdos);
        $periodos = array();
        
        for ($i = 0; $i < $lis_periodos->get_num_registros(); $i++) {
            $pdo = $lis_periodos->get_objeto();
            $sql = "select cmp_fecha, tca_valor as campo
                    from con_comprobante , con_tipo_cambio 
                    where cmp_fecha=tca_fecha and cmp_tco_id=4 and cmp_tipo_ajuste='aj_ufv' and tca_mon_id=3 and cmp_peri_id='$pdo->pdo_id' and cmp_eliminado='No';";
            $val = FUNCIONES::atributo_bd_sql($sql);
            $periodo=new stdClass();
            $periodo->id=$pdo->pdo_id;
            if ($val) {
                $periodo->val=$val;
            } else {
                $periodo->val=-1;
            }
            $periodos[] = $periodo;
            $lis_periodos->siguiente();
        }
        $cmps = array();
        //----------------------------
        $pos_per_ajuste=$this->pos_periodo($periodos, $per_ajuste);
        $periodo= $periodos[$pos_per_ajuste];
        if ($periodo->val * 1 == -1) {
            if ($this->existen_ajustes($per_ajuste, $periodos)) {
                $num=0;
                //EGRESOS
                $sql_egr = "select * from con_cuenta where cue_tipo='Movimiento' and cue_mon_id='$mon_id' and cue_ges_id='$ges_id' and cue_codigo>='5.0.0.00.000' order by cue_codigo;";
                $cuentas_egr = FUNCIONES::objetos_bd_sql($sql_egr);
                $sum_egr = $this->sumatoria_cuentas($cuentas_egr, $periodos, $per_ajuste,$pos_per_ajuste, $ufv_f);
                _PRINT::txt("----------EGRESOS-------------------");
                _PRINT::pre($sum_egr);
                _PRINT::txt("-----------------------------");
                $cmps[]=$this->cargar_cuentas_egr($sum_egr,$num);
                
//                $num=isset($cmps[0]->numfilas)?$cmps[0]->numfilas+1:$num;
                $num=$cmps[0]->numfilas;
                //INGRESOS
                $sql_ing = "select * from con_cuenta where cue_tipo='Movimiento' and cue_mon_id='$mon_id' and cue_ges_id='$ges_id' and '4.0.0.00.000'<cue_codigo and cue_codigo<'5.0.0.00.000' order by cue_codigo;";
                $cuentas_ing = FUNCIONES::objetos_bd_sql($sql_ing);        
                $sum_ing = $this->sumatoria_cuentas($cuentas_ing, $periodos, $per_ajuste,$pos_per_ajuste, $ufv_f);
                _PRINT::txt("----------INGRESOS-------------------");
                _PRINT::pre($sum_ing);
                _PRINT::txt("-----------------------------");
                $cmps[]=$this->cargar_cuentas_ing($sum_ing,$num);
                
//                $num=isset($cmps[1]->numfilas)?$cmps[1]->numfilas+1:$num;
                $num=$cmps[1]->numfilas;
                
                //ACTIVOS
                $sql_act = "select * from con_cuenta where cue_tipo='Movimiento' and cue_mon_id='$mon_id' and cue_ges_id='$ges_id' and '1.0.0.00.000'<cue_codigo and cue_codigo<'2.0.0.00.000' order by cue_codigo;";
                $cuentas_act = FUNCIONES::objetos_bd_sql($sql_act);        
                $sum_act = $this->sumatoria_cuentas($cuentas_act, $periodos, $per_ajuste,$pos_per_ajuste, $ufv_f);
                _PRINT::txt("----------ACTIVOS-------------------");
                 _PRINT::pre($sum_act);
                _PRINT::txt("-----------------------------");
                $cmps[]=$this->cargar_cuentas_act($sum_act,$num);
                
//                $num=isset($cmps[2]->numfilas)?$cmps[2]->numfilas+1:$num;
                $num=$cmps[2]->numfilas;
                
                //PASIVOS
                $sql_pas = "select * from con_cuenta where cue_tipo='Movimiento' and cue_mon_id='$mon_id' and cue_ges_id='$ges_id' and '2.0.0.00.000'<cue_codigo and cue_codigo<'3.0.0.00.000' order by cue_codigo;";
                $cuentas_pas = FUNCIONES::objetos_bd_sql($sql_pas);        
                $sum_pas = $this->sumatoria_cuentas($cuentas_pas, $periodos, $per_ajuste,$pos_per_ajuste, $ufv_f);
                _PRINT::txt("----------PASIVOS-------------------");
                _PRINT::pre($sum_pas);
                _PRINT::txt("-----------------------------");
                $cmps[]=$this->cargar_cuentas_pas($sum_pas,$num);
                
//                $num=isset($cmps[3]->numfilas)?$cmps[3]->numfilas+1:$num;
                $num=$cmps[3]->numfilas;
                
                //PATRIMONIO
                $cu_pat_cap=  FUNCIONES::parametro('cuentas_cap');
                $cu_pat_cap="'".str_replace(",", "','", $cu_pat_cap)."'";
                
                $sql_pat_cap = "select * from con_cuenta 
                            where cue_tipo='Movimiento' and cue_mon_id='3' 
                            and cue_ges_id='1' and cue_codigo in ($cu_pat_cap)order by cue_codigo;";
//                echo $sql_pat."<br>";
                $cuentas_pat_cap = FUNCIONES::objetos_bd_sql($sql_pat_cap);        
                $sum_pat_cap = $this->sumatoria_cuentas($cuentas_pat_cap, $periodos, $per_ajuste,$pos_per_ajuste, $ufv_f);
                _PRINT::txt("----------PATRIMONIO CAPITAL-------------------");
                _PRINT::pre($sum_pat_cap);
                _PRINT::txt("-----------------------------");
                $cmps[]=$this->cargar_cuentas_pat($sum_pat_cap,'cap',$num);
                
//                $num=isset($cmps[4]->numfilas)?$cmps[4]->numfilas+1:$num;
                $num=$cmps[4]->numfilas;
                
                $cu_pat_res=  FUNCIONES::parametro('cuentas_res');
                $cu_pat_res="'".str_replace(",", "','", $cu_pat_res)."'";
                
                $sql_pat_res = "select * from con_cuenta 
                            where cue_tipo='Movimiento' and cue_mon_id='3' 
                            and cue_ges_id='1' and cue_codigo in ($cu_pat_res)order by cue_codigo;";
//                echo $sql_pat."<br>";
                $cuentas_pat_res= FUNCIONES::objetos_bd_sql($sql_pat_res);        
                $sum_pat_res= $this->sumatoria_cuentas($cuentas_pat_res, $periodos, $per_ajuste,$pos_per_ajuste, $ufv_f);
                _PRINT::txt("----------PATRIMONIO RESERVA-------------------");
                _PRINT::pre($sum_pat_res);
                _PRINT::txt("-----------------------------");
                $cmps[]=$this->cargar_cuentas_pat($sum_pat_res,'res',$num);
                
//                $num=isset($cmps[5]->numfilas)?$cmps[5]->numfilas+1:$num;
                $num=$cmps[5]->numfilas;
                
                $cu_pat_acu=  FUNCIONES::parametro('cuentas_acu');
                $cu_pat_acu="'".str_replace(",", "','", $cu_pat_acu)."'";
                
                $sql_pat_acu = "select * from con_cuenta 
                            where cue_tipo='Movimiento' and cue_mon_id='3' 
                            and cue_ges_id='1' and cue_codigo in ($cu_pat_acu)order by cue_codigo;";
//                echo $sql_pat_acu;

                $cuentas_pat_acu= FUNCIONES::objetos_bd_sql($sql_pat_acu);        
                $sum_pat_acu= $this->sumatoria_cuentas($cuentas_pat_acu, $periodos, $per_ajuste,$pos_per_ajuste, $ufv_f);
                _PRINT::txt("----------PATRIMONIO RESULTADO ACUMULADO-------------------");
                _PRINT::pre($sum_pat_acu);
                _PRINT::txt("-----------------------------");
                $cmps[]=$this->cargar_cuentas_pat($sum_pat_acu,'acu',$num);
                
                
//                $num=isset($cmps[6]->numfilas)?$cmps[6]->numfilas:$num-1;
                $num=$cmps[6]->numfilas;
                
                _PRINT::txt("----------SUMAS-------------------");
                _PRINT::pre($cmps);
                _PRINT::txt($num);                
                _PRINT::txt("----------SUMAS-------------------");
                
                
                $tdebe=0;
                $thaber=0;
//                $filas=0; 
                for($i=0;$i<count($cmps);$i++){
                    $tdebe+=$cmps[$i]->tdebe;
                    $thaber+=$cmps[$i]->thaber;                    
                }
                $_POST["tdebe"] = round($tdebe,2);
                $_POST["thaber"] = round($thaber,2);
                $_POST['num_filas'] = $num;//1;
                $_POST['cont_filas'] = $num+1;
                
            } else {
                $this->resetear_detalle();
                $this->mensaje.="En alguno de los <b>periodos</b> anteriores no existena ajustes realizados<br>";
            }
        } else {
            $this->resetear_detalle();
            $this->mensaje.="El <b>periodo</b> seleccionado ya tiene un ajuste realizado<br>";
        }
    }
    function resetear_detalle(){        
        $_POST['cuen1'] = '';
        $_POST['cuen_nombre1'] ='';
        $_POST['debe1'] ='';
        $_POST['haber1'] ='';
        $_POST['glosa1'] ='';
        $_POST['ca1'] ='';
        $_POST['ca_nombre1'] ='';
        $_POST['cf1'] ='';
        $_POST['cf_nombre1'] ='';
        $_POST['cc1'] ='';
        $_POST['cc_nombre1'] ='';
        $_POST['num_filas'] = 1;
        $_POST['cont_filas'] =2;
        $_POST['tdebe'] ='0.00';
        $_POST['thaber'] ='0.00';
    }
    function sumatoria_cuentas($cuentas,$periodos,$per_ajuste,$pos_per_ajuste, $ufv_f){
//        $sum_cuentas = array();
//        $pos_per_ajuste=$this->pos_periodo($periodos, $per_ajuste);
//        $periodo= $periodos[$pos_per_ajuste];
//
//        if ($periodo->val * 1 == -1) {
//            if ($this->existen_ajustes($per_ajuste, $periodos)) {
                
                $sum_cuentas = array();
                $periodos[$pos_per_ajuste]->val=$ufv_f;
                for ($i = 0; $i < $cuentas->get_num_registros(); $i++) {
                    $cuenta = $cuentas->get_objeto();            
                    for ($j = 0; $j <= $pos_per_ajuste; $j++) {
                        $periodo=$periodos[$j];
                        $fecha=  FUNCIONES::get_fecha_mysql($_POST['comp_fecha']);                                
                        $sql = "select cde_cue_id, cde_valor as monto, cmp_fecha , cmp_peri_id , cmp_ges_id ,tca_valor as ufv
                            from con_comprobante_detalle, con_comprobante, con_tipo_cambio
                            where cde_cmp_id=cmp_id and tca_fecha=cmp_fecha and cmp_fecha<='$fecha' and cmp_eliminado='No' and cmp_tco_id!=4 and tca_mon_id=3 and cmp_peri_id='$periodo->id' and cde_cue_id='$cuenta->cue_id' and cde_mon_id=1 ;";
                        $comprobantes = FUNCIONES::objetos_bd_sql($sql);
//                        echo $sql."<br>";
//                        echo $comprobantes->get_num_registros().",";
                        for($m=0;$m<$comprobantes->get_num_registros();$m++) {
                            $cmp=$comprobantes->get_objeto();
//                            _PRINT::pre($cmp);
                            $monto=$cmp->monto;
                            $ufv_i=$cmp->ufv;
                            $res=0;
                            for ($k = $j; $k <= $pos_per_ajuste; $k++) {
                                $_periodo=$periodos[$k];
                                $_monto=$monto;
                                $monto= $this->calcular($monto, $ufv_i, $_periodo->val);
                                $ufv_i=$_periodo->val;
                                $res=$monto-$_monto;                        
                            }
                            $id=$cuenta->cue_id;
                            $this->sumar_cuenta($sum_cuentas,$id, $res);
//                            $sum_cuentas[$id]=$sum_cuentas[$id]+$res;
                            $comprobantes->siguiente();
                        }
                    }
                    $cuentas->siguiente();
                }
                $periodos[$pos_per_ajuste]->val=-1;
                return $sum_cuentas;

//            } else {                
//                $this->mensaje.="En alguno de los <b>periodos</b> anteriores no existena ajustes realizados<br>";
//            }
//        } else {
//            $this->mensaje.="El <b>periodo</b> seleccionado ya tiene un ajuste realizado<br>";
//        }
    }
    function sumar_cuenta(&$sum_cuentas,$id,$valor){
        for($i=0;$i<count($sum_cuentas);$i++){
            $cu=$sum_cuentas[$i];
            if($cu->id==$id){
                $cu->valor=$cu->valor+$valor;
                $sum_cuentas[$i]=$cu;
                return;
            }                    
        }
        $cu=new stdClass();
        $cu->id=$id;
        $cu->valor=$valor;        
        $sum_cuentas[]=$cu;
    }
    
    function cargar_cuentas_egr($cuentas,$num){
//        _PRINT::pre($cuentas);
        if(count($cuentas)==0){
            $cmp=new stdClass();
            $cmp->numfilas=$num;
            return $cmp;
        }        
        $cmp=new stdClass();                
        $tdebe = 0.0;
//        $thaber = 0.0;
        $usar_reex=FUNCIONES::parametro('usar_reex');        
        if($usar_reex=='Si'){
            $i=$num+1;
            $valor = floatval($this->sumatoria($cuentas));            
            $reex_egr=FUNCIONES::parametro('reex_egresos');
            $sql="select * from con_cuenta where cue_codigo='$reex_egr' and cue_ges_id='".$_SESSION['ges_id']."'";
            $cu_egr=  FUNCIONES::objeto_bd_sql($sql);            
            $_POST["cuen$i"] = $cu_egr->cue_id;
            $_POST["cuen_nombre$i"] = $cu_egr->cue_descripcion;            
            $_POST["debe$i"] = round($valor,2);
            $_POST["haber$i"] = '';
            $_POST["glosa$i"] = $_POST['comp_glosa'];            
            $i++;            
            $cod_aitb=  FUNCIONES::parametro('aj_inflacion');
            $sql="select * from con_cuenta where cue_codigo='$cod_aitb' and cue_ges_id='".$_SESSION['ges_id']."'";
            $cu_aitb=  FUNCIONES::objeto_bd_sql($sql);
            $_POST["cuen$i"] = $cu_aitb->cue_id;
            $_POST["cuen_nombre$i"] = $cu_aitb->cue_descripcion;
            $_POST["debe$i"] = '';
            $_POST["haber$i"] = round($valor,2);
            $_POST["glosa$i"] = $_POST['comp_glosa'];            
            $cmp->tdebe=$valor;
            $cmp->thaber=$valor;
            $cmp->numfilas=$i;
            return $cmp;
        }elseif($usar_reex=='No'){
            $i=$num+1;
            foreach ($cuentas as $cuenta) {
                $_POST["cuen$i"] = $cuenta->id;
                $_POST["cuen_nombre$i"] = FUNCIONES::atributo_bd("con_cuenta", "cue_id=$cuenta->id", "cue_descripcion");
                $valor = floatval($cuenta->valor);

                $_POST["debe$i"] = round($valor,2);
                $_POST["haber$i"] = '';
                $tdebe = $tdebe + round($valor,2);;
                $_POST["glosa$i"] = $_POST['comp_glosa'];
                $i++;
            }
            $cod_aitb=  FUNCIONES::parametro('aj_inflacion');
            $sql="select * from con_cuenta where cue_codigo='$cod_aitb' and cue_ges_id='".$_SESSION['ges_id']."'";
            $cu_aitb=  FUNCIONES::objeto_bd_sql($sql);
            $_POST["cuen$i"] = $cu_aitb->cue_id;
            $_POST["cuen_nombre$i"] = $cu_aitb->cue_descripcion;
//            $valor = floatval($this->sumatoria($cuentas));
            $_POST["debe$i"] = '';
            $_POST["haber$i"] = round($tdebe,2);
            $_POST["glosa$i"] = $_POST['comp_glosa'];            
            $cmp->tdebe=$tdebe;
            $cmp->thaber=$tdebe;
            $cmp->numfilas=$i;
            return $cmp;
        }
    }
    
    function cargar_cuentas_ing($cuentas,$num){
        if(count($cuentas)==0){
//            return new stdClass();
            $cmp=new stdClass();
            $cmp->numfilas=$num;
            return $cmp;
        }
        
        $cmp=new stdClass();                
        $thaber = 0.0;
//        $thaber = 0.0;
        $usar_reex=FUNCIONES::parametro('usar_reex');        
        if($usar_reex=='Si'){
            $i=$num+1;
            $valor = floatval($this->sumatoria($cuentas))*-1;            
            $reex_ing=FUNCIONES::parametro('reex_ingresos');
            $sql="select * from con_cuenta where cue_codigo='$reex_ing' and cue_ges_id='".$_SESSION['ges_id']."'";
            $cu_egr=  FUNCIONES::objeto_bd_sql($sql);            
            $_POST["cuen$i"] = $cu_egr->cue_id;
            $_POST["cuen_nombre$i"] = $cu_egr->cue_descripcion;            
            $_POST["debe$i"] = '';
            $_POST["haber$i"] = round($valor,2);
            $_POST["glosa$i"] = $_POST['comp_glosa'];            
            $i++;            
            $cod_aitb=  FUNCIONES::parametro('aj_inflacion');
            $sql="select * from con_cuenta where cue_codigo='$cod_aitb' and cue_ges_id='".$_SESSION['ges_id']."'";
            $cu_aitb=  FUNCIONES::objeto_bd_sql($sql);
            $_POST["cuen$i"] = $cu_aitb->cue_id;
            $_POST["cuen_nombre$i"] = $cu_aitb->cue_descripcion;
            $_POST["debe$i"] = round($valor,2);
            $_POST["haber$i"] = '';
            $_POST["glosa$i"] = $_POST['comp_glosa'];            
            $cmp->tdebe=$valor;
            $cmp->thaber=$valor;
            $cmp->numfilas=$i;
            return $cmp;
        }elseif($usar_reex=='No'){
            $i=$num+1;
            foreach ($cuentas as $cuenta) {
                $_POST["cuen$i"] = $cuenta->id;
                $_POST["cuen_nombre$i"] = FUNCIONES::atributo_bd("con_cuenta", "cue_id=$cuenta->id", "cue_descripcion");
                $valor = floatval($cuenta->valor);

                $_POST["debe$i"] = '';
                $_POST["haber$i"] = round($valor,2)*-1;
                $thaber = $thaber + ($valor*-1);
                $_POST["glosa$i"] = $_POST['comp_glosa'];
                $i++;
            }
            $cod_aitb=  FUNCIONES::parametro('aj_inflacion');
            $sql="select * from con_cuenta where cue_codigo='$cod_aitb' and cue_ges_id='".$_SESSION['ges_id']."'";
            $cu_aitb=  FUNCIONES::objeto_bd_sql($sql);
            $_POST["cuen$i"] = $cu_aitb->cue_id;
            $_POST["cuen_nombre$i"] = $cu_aitb->cue_descripcion;
//            $valor = floatval($this->sumatoria($cuentas));
            $_POST["debe$i"] = round($thaber,2);
            $_POST["haber$i"] = '';
            $_POST["glosa$i"] = $_POST['comp_glosa'];
            
            $cmp->tdebe=$thaber;
            $cmp->thaber=$thaber;
            $cmp->numfilas=$i;
            return $cmp;
        }
    }
    function cargar_cuentas_act($cuentas,$num){
        if(count($cuentas)==0){
            $cmp=new stdClass();
            $cmp->numfilas=$num;
            return $cmp;
//            return new stdClass();
        }        
        $cmp=new stdClass();                
        $tdebe = 0.0;
//        $thaber = 0.0;
        $i=$num+1;
        foreach ($cuentas as $cuenta) {
            $_POST["cuen$i"] = $cuenta->id;
            $_POST["cuen_nombre$i"] = FUNCIONES::atributo_bd("con_cuenta", "cue_id=$cuenta->id", "cue_descripcion");
            $valor = floatval($cuenta->valor);
            $_POST["debe$i"] = round($valor,2);
            $_POST["haber$i"] = '';
            $tdebe = $tdebe + $valor;
            $_POST["glosa$i"] = $_POST['comp_glosa'];
            $i++;
        }
        $cod_aitb=  FUNCIONES::parametro('aj_inflacion');
        $sql="select * from con_cuenta where cue_codigo='$cod_aitb' and cue_ges_id='".$_SESSION['ges_id']."'";
        $cu_aitb=  FUNCIONES::objeto_bd_sql($sql);
        $_POST["cuen$i"] = $cu_aitb->cue_id;
        $_POST["cuen_nombre$i"] = $cu_aitb->cue_descripcion;
//            $valor = floatval($this->sumatoria($cuentas));
        $_POST["debe$i"] = '';
        $_POST["haber$i"] = round($tdebe,2);
        $_POST["glosa$i"] = $_POST['comp_glosa'];

        $cmp->tdebe=$tdebe;
        $cmp->thaber=$tdebe;
        $cmp->numfilas=$i;
        return $cmp;

    }
    
    function cargar_cuentas_pas($cuentas,$num){
        if(count($cuentas)==0){
//            return new stdClass();
            $cmp=new stdClass();
            $cmp->numfilas=$num;
            return $cmp;
        }
        $cmp=new stdClass();                
        $thaber = 0.0;
        $i=$num+1;
        foreach ($cuentas as $cuenta) {
            $_POST["cuen$i"] = $cuenta->id;
            $_POST["cuen_nombre$i"] = FUNCIONES::atributo_bd("con_cuenta", "cue_id=$cuenta->id", "cue_descripcion");
            $valor = floatval($cuenta->valor);

            $_POST["debe$i"] = '';
            $_POST["haber$i"] = round($valor,2)*-1;
            $thaber = $thaber + ($valor*-1);
            $_POST["glosa$i"] = $_POST['comp_glosa'];
            $i++;
        }
        $cod_aitb=  FUNCIONES::parametro('aj_inflacion');
        $sql="select * from con_cuenta where cue_codigo='$cod_aitb' and cue_ges_id='".$_SESSION['ges_id']."'";
        $cu_aitb=  FUNCIONES::objeto_bd_sql($sql);
        $_POST["cuen$i"] = $cu_aitb->cue_id;
        $_POST["cuen_nombre$i"] = $cu_aitb->cue_descripcion;

        $_POST["debe$i"] = round($thaber,2);
        $_POST["haber$i"] = '';
        $_POST["glosa$i"] = $_POST['comp_glosa'];

        $cmp->tdebe=$thaber;
        $cmp->thaber=$thaber;
        $cmp->numfilas=$i;
        return $cmp;
    }
    function cargar_cuentas_pat($cuentas,$tipo,$num){
        if(count($cuentas)==0){
//            return new stdClass();
            $cmp=new stdClass();
            $cmp->numfilas=$num;
            return $cmp;
        }
        $cmp=new stdClass();                
        $thaber = 0.0;
        
        $i=$num+1;
        $valor = floatval($this->sumatoria($cuentas))*-1;
        if($tipo=='cap'){
            $aj_pat=FUNCIONES::parametro('aj_capital');            
        }elseif($tipo=='res'){
            $aj_pat=FUNCIONES::parametro('aj_reserva');
        }elseif($tipo=='acu'){
            $aj_pat=FUNCIONES::parametro('aj_res_acumulado');
        }        
        
        $sql="select * from con_cuenta where cue_codigo='$aj_pat' and cue_ges_id='".$_SESSION['ges_id']."'";        
        $cu_pat=  FUNCIONES::objeto_bd_sql($sql);            
        $_POST["cuen$i"] = $cu_pat->cue_id;
        $_POST["cuen_nombre$i"] = $cu_pat->cue_descripcion;            
        $_POST["debe$i"] = '';
        $_POST["haber$i"] = round($valor,2);
        $_POST["glosa$i"] = $_POST['comp_glosa'];
        
        $i++;
        $cod_aitb=  FUNCIONES::parametro('aj_inflacion');
        $sql="select * from con_cuenta where cue_codigo='$cod_aitb' and cue_ges_id='".$_SESSION['ges_id']."'";
        $cu_aitb=  FUNCIONES::objeto_bd_sql($sql);
        $_POST["cuen$i"] = $cu_aitb->cue_id;
        $_POST["cuen_nombre$i"] = $cu_aitb->cue_descripcion;

        $_POST["debe$i"] = round($valor,2);
        $_POST["haber$i"] = '';
        $_POST["glosa$i"] = $_POST['comp_glosa'];

        $cmp->tdebe=round($valor,2);
        $cmp->thaber=round($valor,2);
        $cmp->numfilas=$i;
        return $cmp;
    }
    
    function existen_ajustes($id_peri, $periodos) {
        $sw = true;
        foreach ($periodos as $periodo) {
            if ($periodo->id != $id_peri) {
                if ($periodo->val * 1 == -1) {
                    return false;
                }
            } else {
                break;
            }
        }
        return $sw;
    }

    function calcular($monto, $ufv_i, $ufv_f) {
        return ($monto * ($ufv_f / $ufv_i));
    }
    function pos_periodo($periodos,$id){
        for ($i = 0; $i < count($periodos); $i++) {
            if($periodos[$i]->id==$id){
                return $i;
            }
        }    
        return null;
    }

    function sumatoria($sum_cuentas) {
        $total=0;
        foreach ($sum_cuentas as $cuenta) {
            $total+=$cuenta->valor;
        }
        return $total;
    }

    function formulario_tcp($tipo) {
        ?>		
        
        <?
        switch ($tipo) {
            case 'ver': {
                    $ver = true;
                    break;
                }

            case 'cargar': {
                    $cargar = true;
                    break;
                }
        }


        $estilo = '';

        if(!isset($_GET['info'])){
            $this->formulario->dibujar_tarea('COMPROBANTE');
        }

        if ($this->mensaje <> "") {
            $this->formulario->dibujar_mensaje($this->mensaje,"","","",false);
        }
        
        if(!isset($_GET['info'])){
            if ($this->verificar_permisos('ACCEDER')) {
                ?>
                <table align=right border=0><tr><td><a href="gestor.php?mod=con_comprobante&tarea=ACCEDER" title="LISTADO DE COMPROBANTES"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
                <?php
            }
        }else{
             echo '<div id=imprimir>
                    <div id=status>
                        <p>                            
                            <a href="gestor.php?mod=con_comprobante&info=ok&tarea=VER&id='.$_GET['id'].'">Cancelar</a></td>
                            <a href=javascript:self.close();>Cerrar</a></td>                            
                        </p>
                    </div>
                </div>';
        }
        ?>
        <!--FancyBox-->
        <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <!--FancyBox-->

        <!--MaskedInput-->
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <!--MaskedInput-->

        <!--AutoSuggest-->
        <script type="text/javascript" src="js/bsn.AutoSuggest_2.1.3.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        
        <script type="text/javascript" src="js/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css" media="screen" charset="utf-8" />
        <!--AutoSuggest-->
        <!--VALIDAR-->

        <link rel="stylesheet" href="js/toolltip/tip-yellow/tip-yellow.css" type="text/css">
        <script type="text/javascript" src="js/toolltip/jquery.poshytip.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
        <!--VALIDAR-->
        <?
        if(!isset($_GET['info'])){
        ?>
        <div class="aTabsCont">
            <div class="aTabsCent">
                <ul class="aTabs">
                    <li><a href="gestor.php?mod=con_comprobante&tarea=AGREGAR&form=Ingreso" <?php if ($_GET['form'] == "Ingreso" || $_GET['form'] == "") { ?>class="activo" <?php } ?>>Ingreso</a></li>
                    <li><a href="gestor.php?mod=con_comprobante&tarea=AGREGAR&form=Egreso" <?php if ($_GET['form'] == "Egreso") { ?>class="activo" <?php } ?>>Egreso</a></li>
                    <li><a href="gestor.php?mod=con_comprobante&tarea=AGREGAR&form=Diario" <?php if ($_GET['form'] == "Diario") { ?>class="activo" <?php } ?>>Diario</a></li>
                    <li hidden=""><a href="gestor.php?mod=con_comprobante&tarea=AGREGAR&form=Ajustes" <?php if ($_GET['form'] == "Ajustes") { ?>class="activo" <?php } ?>>Ajustes</a></li>
                </ul>
            </div>
        </div>
        <?php
        }else{
        ?>        
        <h1><?php echo "Comprobante de ".$_GET['form']?></h1>
        <?php } ?>
        <script type="text/javascript">            
            function obtener_periodo() {
                var fecha = $('#comp_fecha').val();
                if (fecha !== fecha_sel) {
                    $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                        var dato = JSON.parse(respuesta);
                        if (dato.response === "ok") {
                            $('#cmp_peri_id').val(dato.id);
                            $('#label-id-periodo').text(dato.descripcion);
                            $('#label-id-periodo').css('color', '#0072b0');
                            $('#cambios').val(JSON.stringify(dato.cambios));
                        } else if (dato.response === "error") {
                            $('#cmp_peri_id').val("");
                            $('#label-id-periodo').text(dato.mensaje);
                            $('#label-id-periodo').css('color', '#ff0000');
                            $('#cambios').val('');
                        }
                        fecha_sel = fecha;
//                        cambiar_valores_moneda();
                    });
                }
            }
               
            function activar_bancos(){
                var tipo_c=$("#comp_tco_id").val()*1;
                var val=$('#comp_forma_pago option:selected').val();

//                alert(val);
                if(val==='Efectivo'){
                    $("#box_banco_id").hide();
                    $("#box_banco_char").hide();
                    $("#box_banco_nro").hide();
                }else if(val==='Cheque'){
                    if(tipo_c===1){
                        $("#box_banco_id").hide();
                        $("#box_banco_char").show();
                        $("#box_banco_nro").show();
                        $("#tit_ban_char").text("Banco");
                        $("#tit_ban_nro").text("Nro Cheque");
                    }else if(tipo_c===2){
                        $("#box_banco_id").show();
                        $("#box_banco_char").hide();
                        $("#box_banco_nro").show();
                        $("#tit_ban_id").text("Banco");
                        $("#tit_ban_nro").text("Nro Cheque");
                    }
                }else if(val==='Deposito'){
                    if(tipo_c===1){      
                        $("#box_banco_id").show();
                        $("#box_banco_char").hide();
                        $("#box_banco_nro").show();
                        $("#tit_ban_id").text("Banco");
                        $("#tit_ban_nro").text("Nro Deposito");
                    }else if(tipo_c===2){
                        $("#box_banco_id").hide();
                        $("#box_banco_char").show();
                        $("#box_banco_nro").show();
                        $("#tit_ban_char").text("Banco");
                        $("#tit_ban_nro").text("Nro Deposito");
                    }
                }else if(val==='Transferencia'){
                    $("#box_banco_id").show();
                    $("#box_banco_char").show();
                    $("#box_banco_nro").show();
                    if(tipo_c===1){                              
                        $("#tit_ban_id").text("Banco Destino");
                        $("#tit_ban_char").text("Banco Origen");
                        $("#tit_ban_nro").text("Nro Deposito");
                    }else if(tipo_c===2){                        
                        $("#tit_ban_id").text("Banco Origen");
                        $("#tit_ban_char").text("Banco Destino");
                        $("#tit_ban_nro").text("Nro Transferencia");
                    }
                }
            }
            
        </script>

        <div id="Contenedor_NuevaSentencia">
            <?php
            switch ($_GET['form']) {
                case "Ingreso":
                    echo $this->formulario_ingreso($tipo);
                    break;
                case "Egreso":
                    echo $this->formulario_egreso($tipo);
                    break;
                case "Diario":
                    echo $this->formulario_diario($tipo);
                    break;
                case "Ajustes":
                    echo $this->formulario_ajuste($tipo);
                    break;
                default:
                    $_GET['form'] = 'Ingreso';
                    echo $this->formulario_ingreso($tipo);
                    break;
            }
            ?>
        </div>
        <div class="msInfo" >
            <style>
                .info_leyenda td{
                    padding-right: 0px;
                    font-size: 11px;
                    padding-bottom: 5px;
                }
                .info_leyenda{
                    width: 100%;
                }                
            </style>
            
            <table class="info_leyenda">
                
                <tr>
                    <td colspan="3"><b style="border-bottom: 1px solid #589cbc; display: block">Metodos abrebiados del Teclado</b></td>
                </tr>
                <tr>
                    <td>Nueva Fila: <b>F2</b></td>
                    <td>Abrir Lib. de Venta: <b>V</b> sobre Debe o Haber</td>                    
                    <td>Abrir Lib. de Compra: <b>C</b> sobre Debe o Haber</td>
                </tr>
                <tr>
                    <td>Modificar Lib. de Compra o Venta: <b>Enter</b> sobre Debe o Haber</td>
                    <td>Cancelar Modificacion en Venta o Compra: <b>Esc</b> </td>
                    <td>Aceptar Modificacion en Venta o Compra: <b>Enter</b></td>
                </tr>
            </table>
        </div>
        <script type="text/javascript">
            
            function remove(row) {
                var numfilas = $("#cmp_detalle tbody tr").length;
                if (!(numfilas > 1)) {
                    $.prompt('Debe existir por lo menos un detalle');
                    return false;
                }
//                var filas = parseFloat(document.frm_sentencia.nfilas.value);
                var fila=$(row).parent().parent().parent();
                var id_sel=$(row_select).attr("data-id");
                var id_fila=$(fila).attr("data-id");
                if(id_fila===id_sel){
                    row_select=null;
                }
                var id_rm=$(fila).attr("data-id");
                var lib_rm=$(fila).attr("data-lib");
                
                $(fila).remove();
                if(lib_rm!==undefined){
                    eliminar_generados(id_rm, lib_rm);
                }
                
                var numfilas = $("#cmp_detalle tbody tr").length;
                if (numfilas===0) {
                    agregar_plantilla_click();
                }
                resetear_valores_campos();
                sumar_debe_haber();
            }
            
            function eliminar_generados(id_rm, lib_rm){
                var id_sel=$(row_select).attr("data-id");
                var filas_cmp=$("#cmp_detalle tbody").children();
                for(var i=0;i<filas_cmp.size();i++){
                    var fil=$(filas_cmp[i]);
                    var f_id=fil.attr("data-id");
                    var f_idpadre=fil.attr("data-idpadre");
                    var f_lib=fil.attr("data-lib");                    
                    if(f_idpadre===id_rm && f_lib==='g'+lib_rm){
                        if(f_id===id_sel){
                            row_select=null;
                        }
                        fil.remove();
                    }                        
                }
            }
            function sumar_debe_haber(){
                
                var nf = $("#num_filas").val();
                var valorTotal = 0;
                var i = 1;
                var j = 1;
//                alert("ddd -> "+nf);
//                alert("cont  -> "+inputContar);
                while (i <= nf) {
                    if($("#debe" + j).length){
                        var numero = $("#debe" + j).val() * 1;
                        valorTotal += numero;
                        i++;
                    }
//                    if)
                    j++;
                }
                $("#tdebe").val(valorTotal.toFixed(2));

                i = 1;
                j = 1;
                var valorTotal = 0;
                while (i <= nf) {
                    if($("#debe" + j).length){
                        var numero = $("#haber" + j).val() * 1;
                        valorTotal += numero;
                        i++;
                    }
                    j++;
                }
                $("#thaber").val(valorTotal.toFixed(2));
                
            }

            function addTableRow(id, valor) {
                $(id).append(valor);
            }

            $("#add_detalle").live('click',function (){                
                agregar_plantilla_click();
            });
            
            function agregar_plantilla_click(parametros){
                agregar_plantilla(inputContar,parametros);
                if(parametros===undefined){
                    $("#cuen_nombre"+inputContar).focus();
                }
                inputContar++;
            }
            
            var UNE_ID=0;
            var id_centro_costo=0;
            var txt_centro_costo="";
                   
            function agregar_plantilla(inputId,parametros){
                id_centro_costo=0;
                txt_centro_costo="";
            
                var idpadre="";
                var isgen="";
                var id_cu="";
                var txt_cu="";
                var val_debe="";
                var val_haber="";
                var editable="";
                var hidden="";
                var tcu="";
                var completable=true;
                var cc_id=id_centro_costo;
                var cc_nombre=txt_centro_costo;
                if(parametros!==undefined){
                    idpadre='data-idpadre="'+parametros.idpadre+'"';
                    isgen='data-lib="g'+parametros.tipo+'"';
                    tcu='data-cu="'+parametros.tcu+'"';
                    id_cu=parametros.id_cu;
                    txt_cu=parametros.txt_cu;
                    val_debe=parametros.val_debe;
                    val_haber=parametros.val_haber;
                    if(!parametros.editable){
                        editable='readonly=""';
                        hidden='hidden=""';
                        completable=false;
                    }
                    
                    var cc_read="";
                    if(parametros.cc_id!==undefined){
                        cc_id=parametros.cc_id;
                        cc_nombre=parametros.cc_nombre;
                        cc_read='readonly="true"';
                    }
                }
                var html_select='';
                html_select+='<select id="une' + inputId + '" name="une' + inputId + '" class="sel_unegocio" >';
                html_select+='  <option value="0"></option>';
                var une_ids=$('.une_ids');
                var lon=une_ids.size();
                for(var i=0;i<lon;i++){
                    var _une_id=$(une_ids[i]).val()*1;
                    var une_sel='';
                    if(UNE_ID===_une_id){
                        une_sel='selected="true"';
                    }
                    html_select+='<option value="'+_une_id+'" '+une_sel+' >'+$(une_ids[i]).attr('data-nombre');+'</option>';
                }
                html_select+='</select>';
                
                var filas = parseFloat(document.frm_sentencia.nfilas.value);
                document.frm_sentencia.nfilas.value = filas + 1;
                if (document.frm_sentencia.nfilas.value > 0) {
                    document.frm_sentencia.nfilasshadown.value = 1;
                }
                
                
                
                var glosa=$("#comp_glosa").val();
                var fila = '';
                fila += '<tr class="tablaListaFila2" data-id="'+inputId+'" '+idpadre+' '+isgen+' '+tcu+' >';
                fila+='		<td>';
                fila+='		<input type="hidden" size="10" value="'+id_cu+'" id="cuen' + inputId + '" name="cuen' + inputId + '" class="caja_texto idcuenta">';
                fila+='		<input type="text" '+editable+' size="40" value="'+txt_cu+'" id="cuen_nombre' + inputId + '" name="cuen_nombre' + inputId + '" class="caja_texto caja_cuenta box_input">';
                fila+='		</td>';
                fila+='		<td>';
                fila+='		<input type="text" '+editable+' size="10" value="'+val_debe+'" id="debe' + inputId + '" name="debe' + inputId + '" class="caja_texto box_input box_debe" autocomplete="off">';
                fila+='		</td>';
                fila+='		<td>';
                fila+='		<input type="text" '+editable+' size="10" value="'+val_haber+'" id="haber' + inputId + '" name="haber' + inputId + '" class="caja_texto box_input box_haber" autocomplete="off">';
                fila+='		</td>';
                fila+='		<td>';
                fila+='		<input type="text" size="15" value="'+glosa+'" id="glosa' + inputId + '" name="glosa' + inputId + '" class="caja_texto box_input">';
                fila+='		</td>';
                fila+='		<td>';
                fila+='		<input type="hidden" size="10" value="" id="ca' + inputId + '" name="ca' + inputId + '" class="caja_texto">';
                fila+='		<input type="text" size="18" value="" id="ca_nombre' + inputId + '" name="ca_nombre' + inputId + '" class="caja_texto box_input">';
                fila+='		</td>';
                fila+='		<td>';
                fila+='		<input type="hidden" size="10" value="" id="cf' + inputId + '" name="cf' + inputId + '" class="caja_texto">';
                fila+='		<input type="text" size="18" value="" id="cf_nombre' + inputId + '" name="cf_nombre' + inputId + '" class="caja_texto box_input" readonly="">';
                fila+='		</td>';
                fila+='		<td>';
                fila+='		<input type="hidden" size="10" value="'+cc_id+'" id="cc' + inputId + '" name="cc' + inputId + '" class="caja_texto">';
                fila+='		<input type="text" size="18" '+cc_read+' value="'+cc_nombre+'" id="cc_nombre' + inputId + '" name="cc_nombre' + inputId + '" class="caja_texto box_input">';
                fila+='		</td>';
                fila+='		<td>';
                fila+='             '+html_select;
                fila+='		</td>';
                fila+='		<td class="grillaCuenta"><center '+hidden+'><img  class="del-row img-del-det" src=\'images/b_drop.png\' ></center></td></tr>';
                fila += '</tr>';
//                var fila = '<tr class="tablaListaFila2" data-id="'+inputId+'" '+idpadre+' '+isgen+' '+tcu+'>';
//                    fila += '<td>\n\
//                                <input type="hidden" size="10" value="'+id_cu+'" id="cuen' + inputId + '" name="cuen' + inputId + '" class="caja_texto idcuenta">\n\
//                                <input type="text" '+editable+' size="40" value="'+txt_cu+'" id="cuen_nombre' + inputId + '" name="cuen_nombre' + inputId + '" class="caja_texto caja_cuenta box_input">\n\
//                            </td>\n\
//                            <td>\n\
//                                <input type="text" '+editable+' size="10" value="'+val_debe+'" id="debe' + inputId + '" name="debe' + inputId + '" class="caja_texto box_input box_debe" autocomplete="off">\n\
//                            </td>\n\
//                            <td>\n\
//                                <input type="text" '+editable+' size="10" value="'+val_haber+'" id="haber' + inputId + '" name="haber' + inputId + '" class="caja_texto box_input box_haber" autocomplete="off">\n\
//                            </td>\n\
//                            <td>\n\
//                                <input type="text" size="15" value="'+glosa+'" id="glosa' + inputId + '" name="glosa' + inputId + '" class="caja_texto box_input">\n\
//                            </td>\n\
//                            <td>\n\
//                                <input type="hidden" size="10" value="" id="ca' + inputId + '" name="ca' + inputId + '" class="caja_texto">\n\
//                                <input type="text" size="18" value="" id="ca_nombre' + inputId + '" name="ca_nombre' + inputId + '" class="caja_texto box_input">\n\
//                            </td>\n\
//                            <td>\n\
//                                <input type="hidden" size="10" value="" id="cf' + inputId + '" name="cf' + inputId + '" class="caja_texto">\n\
//                                <input type="text" size="18" value="" id="cf_nombre' + inputId + '" name="cf_nombre' + inputId + '" class="caja_texto box_input" readonly="">\n\
//                            </td>\n\
//                            <td>\n\
//                                <input type="hidden" size="10" value="'+cc_id+'" id="cc' + inputId + '" name="cc' + inputId + '" class="caja_texto">\n\
//                                <input type="text" size="18" '+cc_read+' value="'+cc_nombre+'" id="cc_nombre' + inputId + '" name="cc_nombre' + inputId + '" class="caja_texto box_input">\n\
//                            </td>\n\
//                            <td>\n\
//                            </td>\n\
//                            <td class="grillaCuenta"><center '+hidden+'><img  class="del-row img-del-det" src=\'images/b_drop.png\' ></center></td></tr>';
//                fila += '</tr>';

                addTableRow('#cmp_detalle', fila);                
                agregar_plantilla_evento(inputId,'nuevo',completable);
                if(!completable){
                    if(indetalle(id_cu)){
                        autosuggest_cf('cf' + inputId, 'cf_nombre' + inputId,id_cu);
                    }
                }
                inputId++;
                resetear_valores_campos();
            }
            
            $('.sel_unegocio').live('change',function(){
                console.log('cambio');
                UNE_ID=$(this).val()*1;
            });

            
            function MascaraDecimal(input, campo) {
                $(input).live('keypress', function(e) {
//                    if($(this).attr("readonly")!==undefined && e.keyCode!==113){
//                        return false;
//                    }
                    if (e.keyCode !== 13 && e.keyCode !== 8 && e.keyCode !== 46 && e.keyCode !== 37 && e.keyCode !== 39 && e.keyCode !== 35 && e.keyCode !== 36 && e.keyCode !== 9) {                        
                        var valor = $(this).val();
                        var char = String.fromCharCode(e.which);
                        valor = valor + char;
                        if (!/^([0-9])*[.]?[0-9]*$/.test(valor)) {
                            e.preventDefault();
                        } 
                    }
                });
                $(input).live('keyup', function(e) {
//                    if (e.keyCode === 8 || e.keyCode === 46) {
                        var valor = $(this).val() * 1;                        
                        var nf = $("#num_filas").val();
                        var valorTotal = valor * 1;
                        var i = 1;
                        var j = 1;
                        while (i <= nf) {                            
                            if($("#" + campo + j).length){                                
                                if (input !== "#" + campo + j) {
                                    var numero = $("#" + campo + j).val() * 1;
                                    valorTotal += numero;
                                }
                                i++;
                            }
                            j++;
                        }
                        $("#t" + campo).val(valorTotal.toFixed(2));
//                    }
                });
            }

            function agregar_plantilla_evento(inputId,op,completable) {
                if(completable===undefined){
                    autosuggest_generico('cuen' + inputId, 'cuen_nombre' + inputId, 'cuenta',inputId,op);
                }else{
                    if(completable){
                        autosuggest_generico('cuen' + inputId, 'cuen_nombre' + inputId, 'cuenta',inputId,op);
                    }
                }
                
                autosuggest_generico('ca' + inputId, 'ca_nombre' + inputId, 'ca',inputId,op);
                //autosuggest_generico('cf' + inputId, 'cf_nombre' + inputId, 'cf');
                autosuggest_generico('cc' + inputId, 'cc_nombre' + inputId, 'cc',inputId,op);
                verificar_event_cuenta(inputId);
                MascaraDecimal("#debe" + inputId, "debe");
                MascaraDecimal("#haber" + inputId, "haber");
            }

            function verificar_event_cuenta(inputId) {
                $('#cuen_nombre' + inputId).keypress(function(e) {                    
                    var read=$(this).attr("readonly");
//                    alert(read);
                    if (read===undefined &&( e.keyCode === 8 || e.keyCode === 46)) {
                        $('#cuen' + inputId).val("");
                        $('#cf' + inputId).val("");
                        $('#cf_nombre' + inputId).val("");
                        $('#cf_nombre'+inputId).attr("readonly","");
                    }
                });
                $('#cf_nombre' + inputId).keypress(function(e) {
                    if (e.keyCode === 8 || e.keyCode === 46) {                        
                        $('#cf' + inputId).val("");                        
                    }
                });
            }

            function ValidarNumero(e)
            {
                evt = e ? e : event;
                tcl = (window.Event) ? evt.which : evt.keyCode;
                if ((tcl < 48 || tcl > 57) && (tcl != 8 && tcl != 0 && tcl != 46))
                {
                    return false;
                }
                return true;
            }

            function resetear_valores_campos()
            {
                $('#num_filas').val($("#cmp_detalle tbody tr").length);
            }

            
            
            $("#comp_forma_pago").live('change',function (){
                    activar_bancos();
            });
            
            var inputContar = <?php
                                $contf = isset($_POST['cont_filas']) ? intval($_POST['cont_filas']) : 1;
                                echo $contf+1;
                                ?>;
            $(document).keypress(function (evt){
                
                if(evt.keyCode===113 && !open_compra && !open_venta){
                    $("#add_detalle").trigger('click');
//            agregar_plantilla_click();
                }
                if(evt.keyCode===115){
                    $("#btn-submit").trigger("click");
                }
            });
            $(document).ready(function() {
                $("#comp_fecha").live('focusout',function (){
                    obtener_periodo();
                });
                
                $('.del-row').live('click', function() {
                    remove(this);
                });



                var index = 1;
                var nf =<?php echo $contf ?>;
                for (var index = 1; index <= nf; index++) {
                    var read=$("#cuen_nombre"+index).attr("readonly");
                    var b=true;
                    if(read!==undefined){
                        b=false;
                    }
                    agregar_plantilla_evento(index,'modificar',b);
                    var idcu=$("#cuen"+index).val();
                    if(indetalle(idcu)){
                        autosuggest_cf('cf'+index,'cf_nombre'+index,idcu);
                    }
                }
                $('#comp_fecha').keypress(function (event){
                    if(event.keyCode===13){                    
                        obtener_periodo();
                    }
                });
            });
        </script>

        <script>

            $('#frm_sentencia').submit(function() {                
                return false;
            });

            function mostrar_mensaje(input, mensaje) {
                //                alert(input + ' -- '+mensaje);
                $(input).poshytip({
                    content: mensaje,
                    showOn: 'focus',
                    alignTo: 'target',
                    alignX: 'right',
                    alignY: 'center',
                    offsetX: 5,
                    showTimeout: 100
                });
                $(input).poshytip('show');
            }
            $('#btn-submit').click(function() {
                var numfilas = $('#num_filas').val();
                var i=1;
                var j=1;
                while (i <= numfilas) {                    
                    if($("#cuen"+j).length){
                        if (!verificar_cuenta(j)) {
                            return false;
                        }
                        if (!verificar_debe_haber(j)) {
                            return false;
                        }
                        if ($("#tdebe").val()*1 !== $("#thaber").val()*1) {
                            mostrar_mensaje('#thaber', 'El total del Debe y el Haber deben ser iguales');
                            return false;
                        }
                        i++;
                    }
                    j++;
                }
                $("#cont_filas").val(inputContar);
                $("#generado_libro input").remove();
                var detalles=$("#cmp_detalle tbody").children();
                for(var i=0;i<detalles.size();i++){
                    var det=detalles[i];
                    var d_lib=$(det).attr(("data-lib"));
                    if(d_lib!==undefined){
                        var d_id=$(det).attr(("data-id"));
                        if(d_lib==='v' || d_lib==='c' || d_lib==='r'){
                            var d_obj=$(det).attr(("data-obj"));
                            var hidden='<input type="hidden" name="data_obj_'+d_id+'" '+"value='"+d_obj+"'>";
                            $("#generado_libro").append(hidden);
                        }else{
                            var d_cu=$(det).attr(("data-cu"));
                            var d_idpadre=$(det).attr(("data-idpadre"));
                            
                            var hidden='<input type="hidden" name="data_cu_'+d_id+'" '+"value='"+d_cu+"'>";
                            $("#generado_libro").append(hidden);
                            
                            var hidden='<input type="hidden" name="data_idpadre_'+d_id+'" '+"value='"+d_idpadre+"'>";
                            $("#generado_libro").append(hidden);
                        }
                        var hidden='<input type="hidden" name="data_lib_'+d_id+'" '+"value='"+d_lib+"'>";
                        $("#generado_libro").append(hidden);
                        
                    }
                }
//                return false;
                document.frm_sentencia.submit();
            });

            function verificar_debe_haber(index) {
                var valord = $('#debe' + index).val();
                var valorh = $('#haber' + index).val();
                if ((valord === "" && valorh !== "") || (valord !== "" && valorh === "")) {
                    return true;
                } else {
                    mostrar_mensaje('#haber' + index, 'Ingrese un debe o un haber valido');
                    return false;
                }
            }

            function verificar_cuenta(index) {
                var idcuenta = $('#cuen' + index).val();
                if (idcuenta === "") {
                    mostrar_mensaje('#cuen_nombre' + index, 'Ingrese una cuenta valida');
                    return false;
                }
                return true;
            }

            var lcuentas = JSON.parse('<?php echo FUNCIONES::cuentas_json();?>');
            var lcuentas_ca = JSON.parse('<?php echo FUNCIONES::cuentas_ca_json();?>');
            var lcuentas_cc = JSON.parse('<?php echo FUNCIONES::cuentas_cc_json();?>');
            var lcuentas_cf = JSON.parse('<?php echo FUNCIONES::cuentas_cf_json();?>');
            
            function autosuggest_generico(inputHidden, inputVisible, tipocuenta,inputId,op) {
//                var options = {
//                    script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=" + tipocuenta + "&",
//                    //                    script: "test.php?peticion=listCuenta&json=true&limit=6&tipo="+tipocuenta+"&",
//                    varname: "input",
//                    json: true,
//                    shownoresults: false,
//                    maxresults: 6,
//                    callback: function(obj) {                        
//                        $("#"+inputVisible).val($('<div/>').html(obj.value).text());
//                        $("#"+inputHidden).val(obj.id);                        
//                        if(tipocuenta==="cuenta" && indetalle(obj.id)){
//                            autosuggest_cf('cf' + inputId, 'cf_nombre' + inputId,obj.id);
//                        }
//                        if(tipocuenta==="cc"){
//                            var id=obj.id*1;
//                            if(id_centro_costo!==id){
//                                id_centro_costo=id;
//                                txt_centro_costo=obj.value;
//                            }
//                            
//                        }
//                    }
//                };
//                var as_json = new bsn.AutoSuggest(inputVisible, options);
                var projects=null;
                if(tipocuenta==='cuenta'){
                    projects=lcuentas;
                }else if(tipocuenta==='ca'){
                    projects=lcuentas_ca;
                }else if(tipocuenta==='cc'){
                    projects=lcuentas_cc;
                }else if(tipocuenta==='cf'){
                    projects=lcuentas_cf;
                }
                autocomplete_ui(
                    {
                        input:'#'+inputVisible,
                        bus_info:true,
                        lista:projects,
                        select:function(obj){
                            $("#"+inputVisible).val($('<div/>').html(obj.value).text());
                            $("#"+inputHidden).val(obj.id);                        
                            if(tipocuenta==="cuenta" && indetalle(obj.id)){
                                autosuggest_cf('cf' + inputId, 'cf_nombre' + inputId,obj.id);
                            }
                            if(tipocuenta==="cc"){
                                var id=obj.id*1;
                                if(id_centro_costo!==id){
                                    id_centro_costo=id;
                                    txt_centro_costo=obj.value;
                                }

                            }
                        }

                    }
                );
                
                if(op==='modificar'){
                    if(tipocuenta==="cuenta" ){
                        var idcuenta=$("#"+inputHidden).val();
                        if(indetalle(idcuenta)){
                            autosuggest_cf('cf' + inputId, 'cf_nombre' + inputId,idcuenta);
                        }
                    }
                }
            }
            function autosuggest_cf(inputHidden, inputVisible,cue_id){
            
                $("#"+inputVisible).removeAttr("readonly");
                
                autocomplete_ui(
                    {
                        input:'#'+inputVisible,
                        bus_info:true,
                        lista:lcuentas_cf,
                        select:function(obj){
                            $("#"+inputHidden).val(obj.id);
                        }

                    }
                );
                
//                var options = {
//                    script: "AjaxRequest.php?peticion=listCuenta&limit=6&tipo=cf&cue="+cue_id+"&",                    
//                    varname: "input",
//                    json: true,
//                    shownoresults: false,
//                    maxresults: 6,
//                    callback: function(obj) {
//                        $("#"+inputHidden).val(obj.id);
//                    }
//                };
//                var as_json = new bsn.AutoSuggest(inputVisible, options);
            }
            
            function indetalle(idcuenta){
                var txtdetalle=$("#detalle_flujo").val();
                if(txtdetalle!==""){
                    var arr_det=txtdetalle.split(',');
                    for(var i=0;i<arr_det.length;i++){
                        var id=arr_det[i];
                        if(id===idcuenta){
                            return true;
                        }
                    }
                    return false;
                }else{
                    return false;
                }
            }
        </script>
        <?php
    }
    
    function formulario_detalle(){
        if(!isset($_POST['detalle_flujo'])){
            $ges_id=  $_SESSION['ges_id'];
            $detalles=FUNCIONES::objetos_bd("con_detalle_cf", "dcf_ges_id=$ges_id");
            $txt_det="";
            for ($i = 0; $i <$detalles->get_num_registros() ; $i++) {
                $detalle=$detalles->get_objeto();
                if($i>0){
                    $txt_det.=",";
                }
                $txt_det.=$detalle->dcf_cue_id;
                $detalles->siguiente();
            }
            $_POST['detalle_flujo']=$txt_det;
        }        
        ?>        
            <div style="position: relative; padding-bottom: 5px;">
                <?php
                if($_GET['form']=="Ingreso" || $_GET['form']=="Diario"){
                    ?>
                        <a id="libro_v" href="#librov" title="LIBRO DE VENTA" style="float: left" hidden="">&nbsp;</a>
                        <a id="v_click" href="javascript:void(0);" title="LIBRO DE VENTA" style="float: left"><img src="images/librov.png" width="18px"></a>
                    <?php
                }
                if ($_GET['form']=="Egreso" || $_GET['form']=="Diario") {
                    ?>
                    <a id="libro_c" href="#libroc" title="LIBRO DE COMPRA" style="float: left" hidden="">&nbsp;</a>                
                    <a id="c_click" href="javascript:void(0);" title="LIBRO DE COMPRA" style="float: left"><img src="images/libroc.png" width="18px"></a>
                    <?php
                }
                ?>
                <? 
                if($_GET['form']=='Egreso'){
                ?>
                    <a id="libro_r" href="#libror" title="RETENCI&Oacute;N" style="float: left" hidden="">&nbsp;</a>                
                    <a id="r_click" href="javascript:void(0);" title="RETENCIONES" style="float: left"><img src="images/retenercont.png" width="18px"></a>
                <?                
                }?>
                <div style="clear: both"></div>
            </div>
        <?php 
        $tmp_uneg=  FUNCIONES::objetos_bd_sql("select * from con_unidad_negocio where une_eliminado='no'");
        $unegocio=array();
        for($j=0;$j<$tmp_uneg->get_num_registros();$j++){
            $un=$tmp_uneg->get_objeto();
            $unegocio[]=(object)array('id'=>$un->une_id,'nombre'=> $un->une_nombre);
            $tmp_uneg->siguiente();
        }
        ?>
        <?php foreach ($unegocio as $un) { ?><input type="hidden" class="une_ids" value='<?php echo $un->id;?>' data-nombre="<?php echo $un->nombre;?>"/><?php }?>
        
        <input type="hidden" id="cambios" name="cambios" value=""/>
        <input type="hidden" id="detalle_flujo" name="detalle_flujo" value='<?php echo $_POST['detalle_flujo'];?>'/>
        <table class="tablaLista" width="100%" id="cmp_detalle" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>Cuenta</th>
                    <th>Debe</th>
                    <th>Haber</th>
                    <th>Glosa</th>
                    <th>C. Analitica</th>
                    <th>C. de Flujo</th>
                    <th>C. de Costo</th>
                    <th>U. Negocio</th>
                    <th></th>
                </tr>							
            </thead>
            <tbody>
                <?php
                $nf=0;
                if(isset($_POST['num_filas'])){
                    $nf=intval($_POST['num_filas']);
                }else{
                    $nf=1;
                    $_POST["cuen1"]="";
                }                
                $i=1;
                $j=1;
                while($i<=$nf){
                    if(isset($_POST["cuen$j"])){
                        $data_lib = (isset($_POST['data_lib_' . $j]) && $_POST['data_lib_' . $j] != "") ? "data-lib='".$_POST['data_lib_' . $j]."'" : '';
                        
                        $data_obj = (isset($_POST['data_obj_' . $j]) && $_POST['data_obj_' . $j] != "") ? "data-obj='".str_replace('\"', '"', $_POST['data_obj_' . $j])."'" : '';
                        
                        $data_cu = (isset($_POST['data_cu_' . $j]) && $_POST['data_cu_' . $j] != "") ? "data-cu='".$_POST['data_cu_' . $j]."'" : '';
                        $data_idpadre = (isset($_POST['data_idpadre_' . $j]) && $_POST['data_idpadre_' . $j] != "") ? "data-idpadre='".$_POST['data_idpadre_' . $j]."'" : '';
                        $read_only_dh="";
                        $read_only_cu="";
                        $hidden_del="";
                        if((isset($_POST['data_lib_' . $j]) && $_POST['data_lib_' . $j] != "") ||$_GET['form']=='Ajustes'){
                            $read_only_dh="readonly=''";
                            if($_POST['data_lib_' . $j]=='gv'||$_POST['data_lib_' . $j]=='gc'||$_POST['data_lib_' . $j]=='gr'|| $_GET['form']=='Ajustes'){
                                $read_only_cu="readonly=''";
                                $hidden_del="hidden=''";
                            }
                        }
                    ?>
                    <tr data-id="<?php echo $j;?>" <?php echo $data_lib.' '.$data_obj.' '.$data_cu.' '.$data_idpadre;?>>
                        <td>
                            <input class="caja_texto idcuenta" type="hidden" name="cuen<?php echo $j ?>" id="cuen<?php echo $j ?>" value="<?php echo $_POST["cuen$j"]; ?>" size="10" />
                            <input <?php echo $read_only_cu;?> class="caja_texto caja_cuenta box_input" type="text" name="cuen_nombre<?php echo $j ?>" id="cuen_nombre<?php echo $j ?>" value="<?php echo $_POST["cuen_nombre$j"]; ?>" size="40" />
                        </td>
                        <td><input <?php echo $read_only_dh;?> class="caja_texto box_input box_debe" type="text" name="debe<?php echo $j ?>" id="debe<?php echo $j ?>" value="<?php echo $_POST["debe$j"]; ?>"  size="10" autocomplete="off"/></td>
                        <td><input <?php echo $read_only_dh;?> class="caja_texto box_input box_haber" type="text" name="haber<?php echo $j ?>" id="haber<?php echo $j ?>" value="<?php echo $_POST["haber$j"]; ?>" size="10" autocomplete="off"/></td>
                        <td><input class="caja_texto box_input" type="text" name="glosa<?php echo $j ?>" id="glosa<?php echo $j ?>" value="<?php echo $_POST["glosa$j"]; ?>" size="15" /></td>
                        <td>
                            <input class="caja_texto" type="hidden" name="ca<?php echo $j ?>" id="ca<?php echo $j ?>" value="<?php echo $_POST["ca$j"]; ?>" size="10" />
                            <input class="caja_texto box_input" type="text" name="ca_nombre<?php echo $j ?>" id="ca_nombre<?php echo $j ?>" value="<?php echo $_POST["ca_nombre$j"]; ?>" size="18" />
                        </td>
                        <td>
                            <input class="caja_texto" type="hidden" name="cf<?php echo $j ?>" id="cf<?php echo $j ?>" value="<?php echo $_POST["cf$j"]; ?>" size="10" />
                            <input class="caja_texto box_input" type="text" name="cf_nombre<?php echo $j ?>" id="cf_nombre<?php echo $j ?>" value="<?php echo $_POST["cf_nombre$j"]; ?>" size="18" readonly=""/>
                        </td>
                        <td>
                            <input class="caja_texto" type="hidden" name="cc<?php echo $j ?>" id="cc<?php echo $j ?>" value="<?php echo $_POST["cc$j"]; ?>" size="10" />
                            <input class="caja_texto box_input" type="text" name="cc_nombre<?php echo $j ?>" id="cc_nombre<?php echo $j ?>" value="<?php echo $_POST["cc_nombre$j"]; ?>" size="18" />
                        </td>
                        <td>
                            <select name="une<?php echo $j;?>" id="une<?php echo $j;?>" class="sel_unegocio">
                                <option value=""></option>
                                <?php foreach ($unegocio as $un) { ?> <option value="<?php echo $un->id;?>" <?php echo $_POST["une$j"]==$un->id?'selected="true"':''; ?>><?php echo $un->nombre;?></option> <?php }?>
                            </select>
                        </td>
                        <td class="grillaCuenta"><center <?php echo $hidden_del;?>><img  class="del-row img-del-det" src="images/b_drop.png" ></center></td>
                    </tr>
            <?php
                        $i++;
                    }
                    $j++;
                } 
            ?>
            </tbody>    
            <tfoot>	
                <tr>
                    <td style="text-align: left;">                        
                        <?php if($_GET['form']!=='Ajustes'){?>
                        <img id="add_detalle" src="imagenes/agregardet.png" style="margin:0; padding: 0;border: 0; cursor: pointer;" value="agregar"/>
                        <?php } ?>
                        <input type="hidden" name="num_filas" id="num_filas" value="<?php echo $nf ?>"/>
                        <input type="hidden" name="cont_filas" id="cont_filas" value="<?php echo $_POST['cont_filas']; ?>"/>
                    </td>
                    <td><input type="text" name="tdebe" id="tdebe" size="10" value="<?
                        $tdebe = isset($_POST["tdebe"]) ? $_POST["tdebe"] : 0;
                        echo $tdebe;
                        ?>" readonly="readonly"></td>
                    <td><input type="text" name="thaber" id="thaber" size="10" value="<?
                        $thaber = isset($_POST["thaber"]) ? $_POST["thaber"] : 0;
                        echo $thaber;
                        ?>" readonly="readonly"></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>	
            </tfoot>

        </table>
        <div hidden="" id="generado_libro">
            
        </div>
        <?php
            $ges_id=$_SESSION['ges_id'];
            $cred_fiscal= FUNCIONES::parametro('cred_fiscal');
            $deb_fiscal= FUNCIONES::parametro('deb_fiscal');
            $val_iva= FUNCIONES::parametro('val_iva');
            $it= FUNCIONES::parametro('it'); 
            $itpagar= FUNCIONES::parametro('itpagar');
            $val_it= FUNCIONES::parametro('val_it');

            $cu_cf=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$cred_fiscal'");
            $cu_df=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$deb_fiscal'");
            $cu_it=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$it'");
            $cu_itpagar=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$itpagar'");
            
            if($_GET['form']=='Egreso'){
                $ret_it= FUNCIONES::parametro('ret_it');
                $ret_iue_serv= FUNCIONES::parametro('ret_iue_serv');
                $ret_iue_bien= FUNCIONES::parametro('ret_iue_bien');
                
                $cu_ret_it=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$ret_it'");
                $cu_ret_iue_serv=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$ret_iue_serv'");
                $cu_ret_iue_bien=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$ret_iue_bien'");

                $porc_ret_it= FUNCIONES::parametro('porc_ret_it');
                $porc_ret_iue_serv= FUNCIONES::parametro('porc_ret_iue_serv');
                $porc_ret_iue_bien= FUNCIONES::parametro('porc_ret_iue_bien');
            }
        ?>
        <input type="hidden" id="id_cred_fiscal" value="<?php echo $cu_cf->cue_id;?>">
        <input type="hidden" id="txt_cred_fiscal" value="<?php echo $cu_cf->cue_descripcion;?>">
        <input type="hidden" id="id_deb_fiscal" value="<?php echo $cu_df->cue_id;?>">
        <input type="hidden" id="txt_deb_fiscal" value="<?php echo $cu_df->cue_descripcion;?>">
        <input type="hidden" id="val_iva" value="<?php echo ($val_iva/100);?>">
        <input type="hidden" id="id_it" value="<?php echo $cu_it->cue_id;?>">
        <input type="hidden" id="txt_it" value="<?php echo $cu_it->cue_descripcion;?>">
        <input type="hidden" id="id_itpagar" value="<?php echo $cu_itpagar->cue_id;?>">
        <input type="hidden" id="txt_itpagar" value="<?php echo $cu_itpagar->cue_descripcion;?>">
        <input type="hidden" id="val_it" value="<?php echo ($val_it/100);?>">        
        <?php 
        if($_GET['form']=='Egreso'){
            ?>
            <input type="hidden" id="id_ret_it" value="<?php echo $cu_ret_it->cue_id;?>">
            <input type="hidden" id="txt_ret_it" value="<?php echo $cu_ret_it->cue_descripcion;?>">
            <input type="hidden" id="porc_ret_it" value="<?php echo ($porc_ret_it/100);?>">
            
            <input type="hidden" id="id_ret_iue_serv" value="<?php echo $cu_ret_iue_serv->cue_id;?>">
            <input type="hidden" id="txt_ret_iue_serv" value="<?php echo $cu_ret_iue_serv->cue_descripcion;?>">
            <input type="hidden" id="porc_ret_iue_serv" value="<?php echo ($porc_ret_iue_serv/100);?>">
            
            <input type="hidden" id="id_ret_iue_bien" value="<?php echo $cu_ret_iue_bien->cue_id;?>">
            <input type="hidden" id="txt_ret_iue_bien" value="<?php echo $cu_ret_iue_bien->cue_descripcion;?>">
            <input type="hidden" id="porc_ret_iue_bien" value="<?php echo ($porc_ret_iue_bien/100);?>">
            
        <?php
        }
        ?>        
        <style>
            .tab_detalle{
                border-collapse: collapse;   
                width: 100%;
            }
            .tab_detalle tbody tr td{
                border :1px solid #cccccc;
                font-size: 12px;
                padding: 2px;
            }
            .tab_detalle thead tr th{
                border :1px solid #000;
                font-size: 12px;
                color:#fff;                            
            }
            .txt_detalle{
                /*width: 99%;*/
                /*font-size: 12px;*/
            }
            .img-del-vc{
                cursor: pointer;
            }
            #fancy_div{
                overflow: scroll;                            
            }
            #fancy_close{
                display: none;
            }
            .tab_det_venta thead tr th {
                background-color: #218c21; 
            }
            .tab_det_compra thead tr th {
                background-color: #ff0000; 
            }
            .cont_vc{
                background-color: #fbfbfb;  
            }
            .cont_medio{
                float: left;
                width: 50%;
            }
        </style>
        
        <?php
        $parametro=  FUNCIONES::parametro('cuentas_act_disp');
        $cuentas_act=$parametro!=''?explode(',', $parametro):array();
        $cuentas_act_disp=array();
        $ges_id=$_SESSION['ges_id'];
        foreach($cuentas_act as $cta){            
            $sql="select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$cta'";
            $cuenta=  FUNCIONES::objeto_bd_sql($sql);
            $_cu=new stdClass();
            $_cu->id=$cuenta->cue_id;
            $_cu->descripcion=$cuenta->cue_descripcion;
            $cuentas_act_disp[]=$_cu;
        }
        ?>
        <div id="librov" style="display: none;">
            <div class="cont_vc" >
                <h3 style="color: #000;">Ventas</h3>
                <div class="cont_medio">
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Fecha:</div>
                        <div id="CajaInput">                                    
                            <input name="lib_fecha" id="lib_fecha"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_fecha">&nbsp;</span>
                        </div>
                        <script type="text/javascript">
                            $("#fancy_div #lib_fecha").mask("99/99/9999");
                        </script>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Cta. Disponible:</div>
                        <div id="CajaInput">
                            <select name="lib_ctas_act_disp" id="lib_ctas_act_disp" style="min-width: 150px">
                                <option value="0">Seleccione</option>
                                <?php foreach($cuentas_act_disp as $cta){ ?>
                                <option value="<?=$cta->id?>"><?=$cta->descripcion?></option>
                                <?php }?>
                            </select>
                            &nbsp;<span style="color: #ff0000;" hidden="" id="msj_ret_ctas_act_disp">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">                            
                        <div class="Etiqueta2" >Nro. de NIT:</div>
                        <div id="CajaInput">
                            <input name="lib_nit" id="lib_nit"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_nit">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Nro. Autorizaci&oacute;n:</div>
                        <div id="CajaInput">
                            <input name="lib_aut" id="lib_aut"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_aut">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Cod. de Control:</div>
                        <div id="CajaInput">
                            <input name="lib_control" id="lib_control"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_control">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Nro. de Factura:</div>
                        <div id="CajaInput">
                            <input name="lib_nro_fact" id="lib_nro_fact"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_nro_fact">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Razon Social:</div>
                        <div id="CajaInput">
                            <input name="lib_cliente" id="lib_cliente"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_cliente">&nbsp;</span>
                        </div>
                    </div>
                    
                </div>
                <div class="cont_medio">
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Total Factura:</div>
                        <div id="CajaInput">
                            <input name="lib_tot_fact" id="lib_tot_fact"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_tot_fact">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Total I.C.E.:</div>
                        <div id="CajaInput">
                            <input name="lib_tot_ice" id="lib_tot_ice"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_tot_ice">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Importes Excentos:</div>
                        <div id="CajaInput">
                            <input name="lib_imp_ext" id="lib_imp_ext"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_imp_ext">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Importes Neto:</div>
                        <div id="CajaInput">
                            <input name="lib_imp_neto" id="lib_imp_neto"  type="text" class="txt_detalle caja_texto" value="" size="25">
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Debito Fiscal IVA:</div>
                        <div id="CajaInput">
                            <input name="lib_iva" id="lib_iva"  type="text" class="txt_detalle caja_texto" value="" size="25">
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >V/A:</div>
                        <div id="CajaInput">
                            <select id="lib_estado" class="lib_estado" name="" type="text" style="width:40px">
                                <option selected="" value="V">V</option>
                                <option value="A">A</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div id="ContenedorDiv2">
                    <input class="boton" type="button" value="Aceptar" id="lib_aceptar" data-tipo="v" >
                    <input class="boton" type="button" value="Borrar" id="lib_borrar" data-tipo="v" >
                    <input class="boton" type="button" value="Cancelar" id="lib_cancelar" data-tipo="v">
                </div>                
                <table class="tab_detalle tab_det_venta" id="v_detalle" border="0" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Nro de NIT</th>
                            <th>Nro Autoriz.</th>
                            <th>Cod. Control</th>
                            <th>Nro de Factura</th>
                            <th>Razon Social</th>
                            <th>Total Factura</th>
                            <th>Total I.C.E.</th>
                            <th>Importes Exentos</th>
                            <th>Importe Neto</th>
                            <th>Devito Fiscal IVA</th>
                            <th>V/A</th>                            
                        </tr>							
                    </thead>
                    <tbody>
                    </tbody>                   
                </table>
            </div>
        </div>
        <div id="libroc" style="display: none;">
            <div class="cont_vc">
                <h3 style="color: #000;">Compras</h3>
                <div class="cont_medio">
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Tipo:</div>
                        <div id="CajaInput">
                            <input name="lib_tipo" id="lib_tipo"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_tipo">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Fecha:</div>
                        <div id="CajaInput">                                    
                            <input name="lib_fecha" id="lib_fecha"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_fecha">&nbsp;</span>
                        </div>
                        <script type="text/javascript">
                            $("#fancy_div #lib_fecha").mask("99/99/9999");
                        </script>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Cta. Disponible:</div>
                        <div id="CajaInput">
                            <select name="lib_ctas_act_disp" id="lib_ctas_act_disp" style="min-width: 150px">
                                <option value="0">Seleccione</option>
                                <?php foreach($cuentas_act_disp as $cta){ ?>
                                <option value="<?=$cta->id?>"><?=$cta->descripcion?></option>
                                <?php }?>
                            </select>
                            &nbsp;<span style="color: #ff0000;" hidden="" id="msj_ret_ctas_act_disp">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Nro. de NIT:</div>
                        <div id="CajaInput">
                            <input name="lib_nit" id="lib_nit"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_nit">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Nro. Autorizaci&oacute;n:</div>
                        <div id="CajaInput">
                            <input name="lib_aut" id="lib_aut"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_aut">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Cod. de Control:</div>
                        <div id="CajaInput">
                            <input name="lib_control" id="lib_control"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_control">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Nro. de Factura:</div>
                        <div id="CajaInput">
                            <input name="lib_nro_fact" id="lib_nro_fact"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_nro_fact">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Nro. de Poliza Imp.:</div>
                        <div id="CajaInput">
                            <input name="lib_nro_pol" id="lib_nro_pol"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_nro_pol">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Razon Social:</div>
                        <div id="CajaInput">
                            <input name="lib_cliente" id="lib_cliente"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_cliente">&nbsp;</span>
                        </div>
                    </div>
                </div>
                
                <div class="cont_medio">                    
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Total Factura:</div>
                        <div id="CajaInput">
                            <input name="lib_tot_fact" id="lib_tot_fact"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_tot_fact">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Total I.C.E.:</div>
                        <div id="CajaInput">
                            <input name="lib_tot_ice" id="lib_tot_ice"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_tot_ice">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Importes Excentos:</div>
                        <div id="CajaInput">
                            <input name="lib_imp_ext" id="lib_imp_ext"  type="text" class="txt_detalle caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_lib_imp_ext">&nbsp;</span>
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Importes Neto:</div>
                        <div id="CajaInput">
                            <input name="lib_imp_neto" id="lib_imp_neto"  type="text" class="txt_detalle caja_texto" value="" size="25" readonly="">
                        </div>
                    </div>
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Credito Fiscal IVA:</div>
                        <div id="CajaInput">
                            <input name="lib_iva" id="lib_iva"  type="text" class="txt_detalle caja_texto" value="" size="25" readonly="">
                        </div>
                    </div>
                    
                    <div id="ContenedorDiv2">
                        <div class="Etiqueta2" >Dividir Gastos:</div>
                        <div id="CajaInput">                            
                            <select name="lib_gasto_cc" id="lib_gasto_cc">
                                <?php
                                $funcion=new FUNCIONES();
                                $funcion->combo("select cco_descripcion as nombre, cco_id as id from con_cuenta_cc where cco_ges_id='$_SESSION[ges_id]' and cco_tipo='Movimiento' order by cco_codigo", 0);
                                ?>
                            </select>
                            <input id="lib_gasto_monto"  type="text" class="txt_detalle caja_texto" value="" size="8">&nbsp;<img style="float: right; width: 18px;"id="add_gasto" src="images/add-campo.png">                            
                            <div class="box_lista_cuenta">
                                <table id="tab_gastos" class="tab_lista_cuentas">                                    
                                </table>
                            </div>
                            <span style="color: #ff0000;" hidden="" id="msj_gasto_monto">&nbsp;</span>
                        </div>
                    </div>
                    
                </div>                
                <div id="ContenedorDiv2">
                    <input class="boton" type="button" value="Aceptar" id="lib_aceptar" data-tipo="c" >
                    <input class="boton" type="button" value="Borrar" id="lib_borrar" data-tipo="c" >
                    <input class="boton" type="button" value="Cancelar" id="lib_cancelar" data-tipo="c">
                </div>
            <table class="tab_detalle tab_det_compra" id="c_detalle" border="0" cellspacing="0" cellpadding="0"> 
                <thead>
                    <tr>                        
                        <th >Tipo</th>
                        <th style="width: 80px">Fecha</th>
                        <th>Nro de NIT</th>
                        <th>Nro Autoriz.</th>
                        <th>Cod. Control</th>
                        <th>Nro de Factura</th>
                        <th>Nro Poliza Imp.</th>
                        <th>Razon Social</th>
                        <th>Total Factura</th>
                        <th>Total I.C.E.</th>
                        <th>Importes Exentos</th>
                        <th>Importe Neto</th>
                        <th>Credito Fiscal IVA</th>
                    </tr>							
                </thead>
                <tbody>                    

                </tbody>    
            </table>
            </div>
        </div>
        <style>
            .box_lista_cuenta{
                width:213px;height:120px;background-color:#F2F2F2;overflow:auto;
                border: 1px solid #8ec2ea;
            }
            .tab_lista_cuentas{
                list-style: none;                                    
                width: 100%;                                    
                overflow:scroll ;
                background-color: #ededed;
                border-collapse: collapse;  
                font-size: 11px;                
            }
            .tab_lista_cuentas tr td{
                padding: 3px 3px;
            }
            .tab_lista_cuentas tr:hover{
                background-color: #f9e48c;
            }
            #add_gasto, .img_del_cuenta{
                cursor: pointer;
            }
        </style>
        <script>
            $('#fancy_div #lib_gasto_monto').live('keypress',function (e){
                if(e.keyCode===40){
                    $('#fancy_div #add_gasto').trigger('click');
                }                
            });
            $('#fancy_div #add_gasto').live('click',function (){
                var id_cc=$("#fancy_div #lib_gasto_cc option:selected").val()*1;            
                var monto=$("#fancy_div #lib_gasto_monto").val()*1;
                if(monto>0 && !existe_centro_costo(id_cc)){
                    var txt_cc=$("#fancy_div #lib_gasto_cc option:selected").text();
                    var monto=$("#fancy_div #lib_gasto_monto").val()*1;
                    agregar_detatlle_pagos_compra(id_cc,txt_cc,monto);
                    $("#fancy_div #lib_gasto_monto").val('');
                    $("#fancy_div #lib_gasto_monto").focus();
                }
                
            });
            
            function agregar_detatlle_pagos_compra(id_cc,txt_cc,monto){
                var fila='';
                fila+='<tr>';
                fila+='     <td>';
                fila+='         <input type="hidden" class="lib_list_gasto_cc" value="'+id_cc+'"> ';
                fila+='         <input type="hidden" class="lib_list_txt_gasto_cc" value="'+txt_cc+'"> ';
                fila+='         '+txt_cc;
                fila+='     </td>';
                fila+='     <td><input type="hidden" class="lib_list_gasto_monto" value="'+monto+'"> '+monto+'</td>';
                fila+='     <td width="15px" ><img class="img_del_cuenta" width="15px" src="images/retener.png"></td>';
                fila+='</tr>';
                $('#fancy_div #tab_gastos').append(fila);
            }
            function existe_centro_costo(id_cc){
                var c_costos=$('.lib_list_gasto_cc');
                for(var i=0;i<c_costos.size();i++){
                    var id=$(c_costos[i]).val()*1;
                    if(id===id_cc){
                        return true;
                    }
                }
                return false;
            }
            $('.img_del_cuenta').live('click',function(){
                $(this).parent().parent().remove();
            });
        </script>
        
        <?php if($_GET['form']=='Egreso'){?>
        <div id="libror" style="display: none;">
            <div class="cont_vc">
                <h3 style="color: #000;">Retenci&oacute;n</h3>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Fecha:</div>
                    <div id="CajaInput">                                    
                        <input name="ret_fecha" id="ret_fecha"  type="text" class="caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_ret_fecha">&nbsp;</span>
                    </div>
                    <script type="text/javascript">
                        $("#fancy_div #ret_fecha").mask("99/99/9999");
                    </script>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Cuenta Act. Disponible:</div>
                    <div id="CajaInput">
                        <select name="ret_ctas_act_disp" id="ret_ctas_act_disp" style="min-width: 163px">
                            <option value="0">Seleccione</option>
                            <?php foreach($cuentas_act_disp as $cta){ ?>
                            <option value="<?=$cta->id?>"><?=$cta->descripcion?></option>
                            <?php }?>
                        </select>
                        &nbsp;<span style="color: #ff0000;" hidden="" id="msj_ret_ctas_act_disp">&nbsp;</span>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Importe Neto:</div>
                    <div id="CajaInput">
                        <input name="ret_neto" id="ret_neto"  type="text" class="caja_texto" value="" size="25">&nbsp;<span style="color: #ff0000;" hidden="" id="msj_ret_neto">&nbsp;</span>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Tipo de Retenci&oacute;n:</div>
                    <div id="CajaInput">
                        <select name="ret_tipo" id="ret_tipo" style="min-width: 163px">
                            <option value="Servicios">Servicios</option>
                            <option value="Bienes">Bienes</option>
                        </select>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Impuesto de retenci&oacute;n:</div>
                    <div id="CajaInput">
                        <select name="ret_asumido" id="ret_asumido" style="min-width: 163px">
                            <option value="1">Asumido</option>
                            <option value="-1">No Asumido</option>
                        </select>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Retencion IT:</div>
                    <div id="CajaInput">
                        <input name="ret_it" id="ret_it"  type="text" class="caja_texto" value="" size="25" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Retencion IUE:</div>
                    <div id="CajaInput">
                        <input name="ret_iue" id="ret_iue"  type="text" class="caja_texto" value="" size="25" readonly="">
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div class="Etiqueta" >Monto Calculado:</div>
                    <div id="CajaInput">
                        <input name="ret_res" id="ret_res"  type="text" class="caja_texto" value="" size="25" readonly="">
                        <!--<span id="monto_calc"style="color: blue"></span>-->
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <input class="boton" type="button" value="Aceptar" id="lib_aceptar" data-tipo="r" >
                    <input class="boton" type="button" value="Borrar" id="lib_borrar" data-tipo="r" >
                    <input class="boton" type="button" value="Cancelar" id="lib_cancelar" data-tipo="r">
                </div>
                
            </div>
            <script>
                function calcular_monto_retencion(){
                    var imp_neto= $("#fancy_div #ret_neto").val()*1;
                    var ret_tipo= $("#fancy_div #ret_tipo option:selected").val();
                    var ret_asumido= $("#fancy_div #ret_asumido option:selected").val()*1;
                    if(imp_neto===''){
                        $("#fancy_div #ret_it").val("0");
                        $("#fancy_div #ret_iue").val("0");
                        $("#fancy_div #ret_res").val("0");
                        return;
                    }
                    var porc_it=$("#porc_ret_it").val()*1;;
                    var porc_iue=0;
                    if(ret_tipo==='Servicios'){
                        porc_iue=$("#porc_ret_iue_serv").val()*1;
                    }else if(ret_tipo==='Bienes'){
                        porc_iue=$("#porc_ret_iue_bien").val()*1;
                    }
                    var ret_it=0;
                    var ret_iue=0;
                    
                    var imp_calc=imp_neto;
                    if(ret_asumido>0){
                        imp_calc=(imp_neto/(100-((porc_it+porc_iue)*100)))*100;
                    }                    
                    ret_it=(imp_calc*porc_it).toFixed(2);
                    ret_iue=(imp_calc*porc_iue).toFixed(2);                        
                    
                    var ret_res=(imp_neto+((ret_it*1+ret_iue*1)*ret_asumido));
                    
                    $("#fancy_div #ret_it").val(ret_it);
                    $("#fancy_div #ret_iue").val(ret_iue);
                    $("#fancy_div #ret_res").val(ret_res.toFixed(2));
                }
                
                $("#fancy_div #ret_neto").live('keypress', function(e) {
                    if (e.keyCode !== 13 && e.keyCode !== 8 && e.keyCode !== 46 && e.keyCode !== 37 && e.keyCode !== 39 && e.keyCode !== 35 && e.keyCode !== 36 && e.keyCode !== 9) {                        
                        var valor = $(this).val();
                        var char = String.fromCharCode(e.which);
                        valor = valor + char;                        
                        if (!/^([0-9])*[.]?[0-9]*$/.test(valor)) {
                            e.preventDefault();
                        } 
                    }
                });
                $("#fancy_div #ret_neto").live('keyup', function(e) {                    
                    calcular_monto_retencion();
                });
                
                $("#fancy_div #ret_tipo").live('change', function(e) {                    
                    calcular_monto_retencion();
                });
                $("#fancy_div #ret_asumido").live('change', function(e) {                    
                    calcular_monto_retencion();
                });
            </script>
        </div>
        <?}?>
        <input type="hidden" id="fecha_actual" value="<?php echo date("d/m/Y")?>">        
        <script>
            var row_select=null;
            var open_venta=false;
            var open_compra=false;
            var open_retencion=false;
            var lib_aceptar=false;
            var lib_borrar=false;
            $(".box_input").live("focusin",function (){
                if(row_select!==null){
                    $(row_select).children().find(".box_input").css({'border':'1px solid #bfc4c9'});
                }
                var fila=$(this).parent().parent();
                var tlib=$(fila).attr("data-lib");
                if(tlib===undefined){
                    $(fila).children().find(".box_input").css({'border':'1px solid #02a1fd'});
                }else if(tlib==='v'){
                    $(fila).children().find(".box_input").css({'border':'1px solid #028630'});
                }else if(tlib==='c'){
                    $(fila).children().find(".box_input").css({'border':'1px solid #ff0000'});
                }else if(tlib==='gv'){
                    $(fila).children().find(".box_input").css({'border':'1px solid #46b96e'});
                }else if(tlib==='gc'){
                    $(fila).children().find(".box_input").css({'border':'1px solid #ff7878'});          
                }else if(tlib==='r'){
                    $(fila).children().find(".box_input").css({'border':'1px solid #c5a702'});
                }else if(tlib==='gr'){
                    $(fila).children().find(".box_input").css({'border':'1px solid #dfc94b'});
                }                
                row_select=fila;
            });
            
            $(".box_haber,.box_debe").live("keypress",function (evt){                
                if(evt.keyCode===13){
                    if(row_select!==null){
                        var tlib=$(row_select).attr("data-lib");
                        if(tlib!==undefined){
                            abrir_formulario(tlib);
                        }
                    }
                }
                if(evt.which===86 || evt.which===118){
                    if(row_select!==null){
                        abrir_formulario('v');
                    }
                }
                if(evt.which===99 || evt.which===67){
                    if(row_select!==null){
                        abrir_formulario('c');
                    }
                }
                if(evt.which===114 || evt.which===82){
                    if(row_select!==null){
                        abrir_formulario('r');
                    }
                }
            });
            $("#v_click").click(function (){
                abrir_formulario('v');
            });
            
            $("#c_click").click(function (){
                 abrir_formulario('c');
            });
            $("#r_click").click(function (){
                 abrir_formulario('r');
            });

            function abrir_formulario(tipo){
                var tcambios=$("#cambios").val();                
                if(tcambios!==''){
                    if(row_select!==null){
                        var cuenta=$(row_select).children().find(".idcuenta")[0];
                        var idcuenta=$(cuenta).val();
                        if(idcuenta!==""){
                            var tlib=$(row_select).attr("data-lib");
                            if(tlib===tipo || tlib===undefined ){
                                if(!$("#libro_"+tipo).length){
                                    return;
                                }
                                var frm_width=900;
                                var frm_height=450;
                                if(tipo==='v'){
                                    open_venta=true;
                                }else if(tipo==='c'){
                                    open_compra=true;
                                }else if(tipo==='r'){
                                    open_retencion=true;
                                    frm_width=500;
                                    frm_height=300;
                                }
                                preparar_libro(tipo);
                                lib_aceptar=false;
                                lib_borrar=false;
//                                $("#fancy_div").css("background-color",'#fbfbfb');
                                $("#libro_"+tipo).fancybox({
                                    'hideOnContentClick': false,
                                    'overlayShow' : true,
                                    'zoomOpacity' : true,
                                    'zoomSpeedIn' : 300,
                                    'zoomSpeedOut' : 200,
                                    'overlayOpacity':0.5,
                                    'frameWidth' :frm_width,
                                    'frameHeight' :frm_height,                                    
                                    'callbackOnClose':function (){
                                        if(tipo==='v'){
                                            open_venta=false;
                                        }else if(tipo==='c'){
                                            open_compra=false;
                                        }else if(tipo==='r'){
                                            open_retencion=false;
                                        }
                                        if(lib_aceptar){
                                            if(tipo==='v'||tipo==='c'){
                                                modificar_libro(tipo);
                                            }else if(tipo==='r'){
                                                modificar_retencion();
                                            }
                                        }
                                        $(row_select).children().find(".box_debe").focus();
                                    },
                                    'callbackOnShow':function(){
                                        if(tipo==='v'||tipo==='c'){
                                            preparar_formulario_venta_compra(tipo);                                        
//                                            var row_sel_id=$(row_select).attr("data-id");
                                            $("#fancy_div #lib_fecha").mask("99/99/9999");
                                            $("#fancy_div #lib_control").mask("**-**-**-**");
                                            if(tlib===undefined){
                                                $('#fancy_div #lib_fecha').focus();
                                            }else{
                                                $('#fancy_div #lib_tot_fact').select();
                                            }
                                        }else if(tipo==='r'){
                                            preparar_formulario();
                                            if(tlib===undefined){
                                                $('#fancy_div #ret_fecha').focus();
                                            }else{
                                                $('#fancy_div #ret_neto').select();
                                            }
                                        }
                                    }
                                }).trigger('click');
                            }else{
                                var text="";
                                if(tlib==='c'){
                                    text='El detalle ya esta asignada a un libro de Compra';
                                }else if(tlib==='v'){
                                    text='El detalle ya esta asignada a un libro de Venta';
                                }else if(tlib==='gc'){
                                    text='El detalle es generado a partir de un Libro de Compra';
                                }else if(tlib==='gv'){
                                    text='El detalle es generado a partir de un Libro de Venta';
                                }else if(tlib==='r'){
                                    text='El detalle ya esta asignada a una Retencion';
                                }else if(tlib==='gr'){
                                    text='El detalle es generado a partir de una Retencion';
                                }

                                $.prompt(text,{
                                buttons:{Ok:true},
                                callback: function(v,m,f){
                                        if(v){
                                            $(row_select).children().find(".caja_cuenta").focus();
                                        }
                                    }
                                });
                            }
                        }else{
                            $.prompt("La cuenta de la fila seleccionada no debe ser vacia",{
                                buttons:{Ok:true},
                                callback: function(v,m,f){
                                    if(v){
                                        $(row_select).children().find(".caja_cuenta").focus();
                                    }
                                }
                            });                        
                        }
                    }else{
                        $.prompt("Debe seleccionar una fila en el detalle primero");
                    }
                }else{
                    $.prompt("No Existen tipos de cambio asignados a la fecha.");
                }
            }
            
            function modificar_libro(tipo){
                if(lib_borrar){
                    var id_rm=$(row_select).attr("data-id");
                    var lib_rm=$(row_select).attr("data-lib");                    
                    eliminar_generados(id_rm, lib_rm);
                    resetear_valores_campos();
                    $(row_select).removeAttr("data-lib");
                    $(row_select).removeAttr("data-obj");                    
                    $("#cmp_detalle #debe"+id_rm).removeAttr("readonly");
                    $("#cmp_detalle #haber"+id_rm).removeAttr("readonly");
                    $("#cmp_detalle #debe"+id_rm).val('');
                    $("#cmp_detalle #haber"+id_rm).val('');
                    $("#cmp_detalle #debe"+id_rm).focus('');
                    sumar_debe_haber();
                    return false;
                }
                var id=$(row_select).attr("data-id");
                var objeto={};
                if(tipo==='c'){                    
                    objeto.tipo=$('#fancy_div #lib_tipo').val();
                }
                objeto.fecha=$('#fancy_div #lib_fecha').val();
                objeto.cta_disp=$('#fancy_div #lib_ctas_act_disp option:selected').val();
                var txt_cu_act_disp=$('#fancy_div #lib_ctas_act_disp option:selected').text();
                objeto.nit=$('#fancy_div #lib_nit').val();
                objeto.aut=$('#fancy_div #lib_aut').val();
                objeto.control=$('#fancy_div #lib_control').val();
                objeto.nro_fact=$('#fancy_div #lib_nro_fact').val();
                if(tipo==='c'){
                    objeto.nro_pol=$('#fancy_div #lib_nro_pol').val();
                }
                objeto.cliente=$('#fancy_div #lib_cliente').val();
                objeto.tot_fact=$('#fancy_div #lib_tot_fact').val();
                objeto.tot_ice=$('#fancy_div #lib_tot_ice').val();
                objeto.imp_ext=$('#fancy_div #lib_imp_ext').val();
                objeto.imp_neto=$('#fancy_div #lib_imp_neto').val();
                objeto.iva=$('#fancy_div #lib_iva').val();
                objeto.pagos=obtener_lista_pagos();
                var est=1;
                if(tipo==='v'){
                    objeto.estado=$('#fancy_div #lib_estado option:selected').val();
                    if(objeto.estado==='A'){
                        est=0;
                    }
                }
                console.log(objeto);
                var res=cambiar_valor_moneda((objeto.tot_fact-objeto.iva)*est);
                var iva=cambiar_valor_moneda((objeto.iva)*est);
                var tot_fact=cambiar_valor_moneda(objeto.tot_fact);
                
                if(tipo==='v'){
                    $(row_select).find(".box_haber").val(res);
                    $(row_select).find(".box_debe").val("");
                    
                    var val_it=$("#val_it").val();
                    
                    var it=cambiar_valor_moneda((objeto.imp_neto * val_it)*est);
                    
                    var lib=$(row_select).attr("data-lib");
                    if(lib===undefined){
                        var parametros={
                            idpadre: id,id_cu: $("#id_deb_fiscal").val(),txt_cu: $("#txt_deb_fiscal").val(),tcu: 'df',
                            val_debe: "",val_haber: iva,tipo: tipo,editable:false
                        };
                        agregar_plantilla_click(parametros);
                        var parametros={
                            idpadre: id,id_cu: $("#id_it").val(),txt_cu: $("#txt_it").val(),tcu: 'it',
                            val_debe: it,val_haber: "",tipo: tipo,editable:false
                        };
                        agregar_plantilla_click(parametros);                        
                        var parametros={
                            idpadre: id,id_cu: $("#id_itpagar").val(),txt_cu: $("#txt_itpagar").val(),tcu: 'itpagar',
                            val_debe: "",val_haber: it,tipo: tipo,editable:false
                        };
                        agregar_plantilla_click(parametros);
                        var parametros={
                            idpadre: id,id_cu: objeto.cta_disp,txt_cu: txt_cu_act_disp,tcu: 'vcad',//cuenta activo disp
                            val_debe: objeto.tot_fact*est,val_haber: "",tipo: tipo,editable:false
                        };
                        agregar_plantilla_click(parametros);
                    }else{
                        
                        modificar_generados(id,"g"+lib,'df',iva,false);
                        modificar_generados(id,"g"+lib,'df',iva,false);
                        modificar_genrados(id,"g"+lib,'it',it,true);
                        modificar_generados(id,"g"+lib,'itpagar',it,false);
                        modificar_generados(id,"g"+lib,'vcad',objeto.tot_fact*est,true,true,objeto.cta_disp,txt_cu_act_disp);
                    }
//                    sumar_debe_haber();
                }else if(tipo==='c'){
                    var val_iva=$('#val_iva').val();
                    var ccmonto=objeto.pagos[0].cc_monto;
                    var res_0=cambiar_valor_moneda(ccmonto-(ccmonto*val_iva));
                    var det_id=$(row_select).attr('data-id');
                    $(row_select).find(".box_debe").val(res_0);
                    $(row_select).find(".box_haber").val("");
                    $(row_select).find("#cc"+det_id).val(objeto.pagos[0].cc_id);
                    $(row_select).find("#cc_nombre"+det_id).val(objeto.pagos[0].cc_nombre);
                    $(row_select).find("#cc_nombre"+det_id).attr('readonly','true');
                    
                    var lib=$(row_select).attr("data-lib");                    
                    if(lib!==undefined){                        
                        eliminar_generados_compra(id,"g"+lib,'sub');
                        eliminar_generados_compra(id,"g"+lib,'cf');
                        eliminar_generados_compra(id,"g"+lib,'ccad');
                    }                    
                    
                    for(var i=1;i<objeto.pagos.length;i++){
                        ccmonto=objeto.pagos[i].cc_monto;
                        var res=cambiar_valor_moneda(ccmonto-(ccmonto*val_iva));
                        var parametros={
                            idpadre: id,id_cu: $('#cuen'+det_id).val(),txt_cu: $('#cuen_nombre'+det_id).val(),tcu: 'sub',
                            val_debe: res,val_haber: "",tipo: tipo,editable:false,cc_id:objeto.pagos[i].cc_id, cc_nombre:objeto.pagos[i].cc_nombre
                        };
                        agregar_plantilla_click(parametros);
                    }
                    
                    var parametros={
                        idpadre: id,id_cu: $("#id_cred_fiscal").val(),txt_cu: $("#txt_cred_fiscal").val(),tcu: 'cf',
                        val_debe: iva,val_haber: "",tipo: tipo,editable:false
                    };
                    agregar_plantilla_click(parametros);
                    var parametros={
                        idpadre: id,id_cu: objeto.cta_disp,txt_cu: txt_cu_act_disp,tcu: 'ccad',//cuenta activo disp
                        val_debe: "",val_haber: tot_fact,tipo: tipo,editable:false
                    };
                    agregar_plantilla_click(parametros);
                    
                    if(lib===undefined){
//                        var parametros={
//                            idpadre: id,id_cu: $("#id_cred_fiscal").val(),txt_cu: $("#txt_cred_fiscal").val(),tcu: 'cf',
//                            val_debe: iva,val_haber: "",tipo: tipo,editable:false
//                        };
//                        agregar_plantilla_click(parametros);
//                        var parametros={
//                            idpadre: id,id_cu: objeto.cta_disp,txt_cu: txt_cu_act_disp,tcu: 'ccad',//cuenta activo disp
//                            val_debe: "",val_haber: objeto.tot_fact,tipo: tipo,editable:false
//                        };
//                        agregar_plantilla_click(parametros);
                    }else{
//                        modificar_generados(id,"g"+lib,'cf',iva,true);
//                        modificar_generados(id,"g"+lib,'ccad',objeto.tot_fact,false,true,objeto.cta_disp,txt_cu_act_disp);
                    }
                    
                }
                resetear_valores_campos();
                sumar_debe_haber();
                
                $(row_select).attr("data-lib",tipo);
                $(row_select).attr("data-obj",JSON.stringify(objeto));
                
                var txt_debe=$(row_select).children().find(".box_debe");
                var txt_haber=$(row_select).children().find(".box_haber");
                $(txt_debe[0]).attr("readonly","");
                $(txt_haber[0]).attr("readonly","");
                $(txt_debe[0]).focus();
            }
            
            function obtener_lista_pagos(){
                var ids_cc=$('#fancy_div .lib_list_gasto_cc');
                var txt_cc=$('#fancy_div .lib_list_txt_gasto_cc');
                var montos=$('#fancy_div .lib_list_gasto_monto');
                var pagos=[];
                for(var i=0;i<montos.size();i++){
                    var id_c=$(ids_cc[i]).val();
                    var txt_c=$(txt_cc[i]).val();
                    var monto=$(montos[i]).val();
                    var objeto={cc_id:id_c,cc_nombre:txt_c,cc_monto:monto};
                    pagos[i]=objeto;                
                }
                return pagos;
            }
            
            function modificar_retencion(){
                
                if(lib_borrar){
                    var id_rm=$(row_select).attr("data-id");
                    var lib_rm=$(row_select).attr("data-lib");                    
                    eliminar_generados(id_rm, lib_rm);
                    resetear_valores_campos();
                    $(row_select).removeAttr("data-lib");
                    $(row_select).removeAttr("data-obj");
                    
                    $("#cmp_detalle #debe"+id_rm).removeAttr("readonly");
                    $("#cmp_detalle #haber"+id_rm).removeAttr("readonly");
                    $("#cmp_detalle #debe"+id_rm).val('');
                    $("#cmp_detalle #haber"+id_rm).val('');
                    $("#cmp_detalle #debe"+id_rm).focus('');
                    sumar_debe_haber();
                    return false;                    
                }
                var objeto={};
                
                objeto.fecha=$('#fancy_div #ret_fecha').val();
                objeto.cta_disp=$('#fancy_div #ret_ctas_act_disp option:selected').val();                
                var txt_cu_act_disp=$('#fancy_div #ret_ctas_act_disp option:selected').text();
                objeto.neto=$('#fancy_div #ret_neto').val();
                objeto.tipo=$('#fancy_div #ret_tipo option:selected').val();
                objeto.asumido=$('#fancy_div #ret_asumido option:selected').val();
                objeto.it=$('#fancy_div #ret_it').val();
                objeto.iue=$('#fancy_div #ret_iue').val();
                objeto.res=$('#fancy_div #ret_res').val();                
                
                var neto=cambiar_valor_moneda(objeto.neto);
                var res=cambiar_valor_moneda(objeto.res);
                
                var cu1=neto;                
                var cu2=res;
                
                if(objeto.asumido*1>0){
                    cu1=res;
                    cu2=neto;
                }
                var it=cambiar_valor_moneda(objeto.it);
                var iue=cambiar_valor_moneda(objeto.iue);
                
                $(row_select).find(".box_debe").val((cu2*1+it*1+iue*1).toFixed(2));
                $(row_select).find(".box_haber").val("");
                
                var id=$(row_select).attr("data-id");
                var lib=$(row_select).attr("data-lib");
                var cu_iue=0;
                var txt_cu_iue="";
                if(objeto.tipo==='Servicios'){
                    cu_iue=$("#id_ret_iue_serv").val();
                    txt_cu_iue=$("#txt_ret_iue_serv").val();
                }else if(objeto.tipo==='Bienes'){
                    cu_iue=$("#id_ret_iue_bien").val();
                    txt_cu_iue=$("#txt_ret_iue_bien").val();
                }
                var tipo='r';
                if(lib===undefined){
                    var parametros={
                        idpadre: id,id_cu: cu_iue,txt_cu: txt_cu_iue,tcu: 'riue',
                        val_debe: "",val_haber: iue,tipo: tipo,editable:false
                    };
                    agregar_plantilla_click(parametros);                        
                    var parametros={
                        idpadre: id,id_cu: $("#id_ret_it").val(),txt_cu: $("#txt_ret_it").val(),tcu: 'rit',
                        val_debe: '',val_haber: it,tipo: tipo,editable:false
                    };
                    agregar_plantilla_click(parametros);                        
                    var parametros={
                        idpadre: id,id_cu: objeto.cta_disp,txt_cu: txt_cu_act_disp,tcu: 'rcad',//cuenta activo disp
                        val_debe: "",val_haber: cu2,tipo: tipo,editable:false
                    };
                    agregar_plantilla_click(parametros);
                }else{
                    modificar_generados(id,"g"+lib,'riue',iue,false);
                    modificar_generados(id,"g"+lib,'rit',it,false);
                    modificar_generados(id,"g"+lib,'rcad',cu2,false,true,objeto.cta_disp,txt_cu_act_disp);
                }
                resetear_valores_campos();
                sumar_debe_haber();
                
                $(row_select).attr("data-lib",'r');
                $(row_select).attr("data-obj",JSON.stringify(objeto));
                
                var txt_debe=$(row_select).children().find(".box_debe");
                var txt_haber=$(row_select).children().find(".box_haber");
                $(txt_debe[0]).attr("readonly","");
                $(txt_haber[0]).attr("readonly","");
                $(txt_debe[0]).focus();
            }   
            
            
            function cambiar_valor_moneda(valor,tcambios,moneda){
                if(tcambios ===undefined){
                    tcambios=JSON.parse($("#cambios").val());
                }
                if(moneda===undefined){
                    moneda=document.frm_sentencia.comp_mon_id.value;
                }                
                var tc=1;                
                for(var i=0;i<tcambios.length;i++){
                    var cambio=tcambios[i];
                    if(cambio.id===moneda){                        
                        tc=cambio.val;
                    }
                }
                return (valor/tc).toFixed(2);
            }
            
            function cambiar_valores_moneda(){
                var txt_cambios=$("#cambios").val();
                if(txt_cambios===""){
                    txt_cambios="[]";
                }
                var tcambios=JSON.parse(txt_cambios);
                var moneda=document.frm_sentencia.comp_mon_id.value;
                var detalles=$("#cmp_detalle tbody").children();
                var val_it=$("#val_it").val();
                for(var i=0;i<detalles.size();i++){
                    var detalle=$(detalles[i]);                    
                    var d_obj=detalle.attr("data-obj");
                    if(d_obj!==undefined){
                        var d_lib=detalle.attr("data-lib");
                        var d_id=detalle.attr("data-id");
                        
                        if(tcambios.length>0){
                            var libro = JSON.parse(d_obj);
                            if(d_lib==='v'){
                                var res=cambiar_valor_moneda(libro.tot_fact-libro.iva,tcambios,moneda);
                                var iva=cambiar_valor_moneda(libro.iva,tcambios,moneda);
                                var est=1;
                                if(libro.estado==='A')est= 0;
                                var it=cambiar_valor_moneda((libro.imp_neto * val_it)*est,tcambios,moneda);
                                res=res*est;
                                iva=iva*est;
                                $(detalle).find(".box_haber").val(res);
                                modificar_generados(d_id,"g"+d_lib,'df',iva,false);
                                modificar_generados(d_id,"g"+d_lib,'it',it,true);
                                modificar_generados(d_id,"g"+d_lib,'itpagar',it,false);                                
                            }else if(d_lib==='c'){                                
                                var res=cambiar_valor_moneda(libro.tot_fact-libro.iva,tcambios,moneda);
                                var iva=cambiar_valor_moneda(libro.iva,tcambios,moneda);
                                $(detalle).find(".box_debe").val(res);
                                modificar_generados(d_id,"g"+d_lib,'cf',iva,true);   
                            }else if(d_lib==='r'){
                                var neto=cambiar_valor_moneda(libro.neto);
                                var res=cambiar_valor_moneda(libro.res);
                                var cu1=neto;                
                                var cu2=res;
                                if(libro.asumido*1>0){
                                    cu1=res;
                                    cu2=neto;
                                }
                                var it=cambiar_valor_moneda(libro.it);
                                var iue=cambiar_valor_moneda(libro.iue);
                                
                                $(detalle).find(".box_debe").val((iue*1+it*1+cu2*1).toFixed(2));
                                modificar_generados(d_id,"g"+d_lib,'riue',iue,false);
                                modificar_generados(d_id,"g"+d_lib,'rit',it,false);
                                modificar_generados(d_id,"g"+d_lib,'rcad',cu2,false);
                            }                            
                        }else{
                            if(d_lib==='v'){
                                $(detalle).find(".box_haber").val("");
                                modificar_generados(d_id,"g"+d_lib,'df','',false);
                                modificar_generados(d_id,"g"+d_lib,'it','',true);
                                modificar_generados(d_id,"g"+d_lib,'itpagar','',false);
                            }else if(d_lib==='c'){
                                $(detalle).find(".box_debe").val("");
                                modificar_generados(d_id,"g"+d_lib,'cf','',true);
                            }else if(d_lib==='r'){
                                $(detalle).find(".box_debe").val("");
                                modificar_generados(d_id,"g"+d_lib,'riue','',false);
                                modificar_generados(d_id,"g"+d_lib,'rit','',false);
                                modificar_generados(d_id,"g"+d_lib,'rcad','',false);
                            }
                        }
                        sumar_debe_haber();
                    }                    
                }
            }
            function eliminar_generados_compra(idpadre,lib,cu){
                var detalles=$("#cmp_detalle tbody").children();
                for(var i=0;i<detalles.size();i++){
                    var detalle=$(detalles[i]);
                    var d_idpadre=detalle.attr("data-idpadre");
                    var d_lib=detalle.attr("data-lib");
                    var d_cu=detalle.attr("data-cu");
                    if(d_idpadre===idpadre && d_lib===lib && d_cu===cu){
                        $(detalle).remove();
                    }
                }
            }
            function modificar_generados(idpadre,lib,cu,valor,debe,mod_cuenta,idcu,txtcu){
                var detalles=$("#cmp_detalle tbody").children();
                for(var i=0;i<detalles.size();i++){
                    var detalle=$(detalles[i]);
                    var d_idpadre=detalle.attr("data-idpadre");
                    var d_lib=detalle.attr("data-lib");
                    var d_cu=detalle.attr("data-cu");
                    if(d_idpadre===idpadre && d_lib===lib && d_cu===cu){
                        if(debe){
                            detalle.find(".box_debe").val(valor);
                        }else{
                            detalle.find(".box_haber").val(valor);
                        }
                        if(mod_cuenta!==undefined && mod_cuenta){
                            detalle.find(".idcuenta").val(idcu);
                            detalle.find(".caja_cuenta").val(txtcu);
                        }
                    }
                }
            }
            
            function preparar_libro(tipo){
                if(tipo==='v'|| tipo==='c'){
                    $('#'+tipo+'_detalle tbody').children().remove();
                    var detalles=$("#cmp_detalle tbody").children();
                    var row_sel_id=$(row_select).attr("data-id");
                    for(var i=0;i<detalles.size();i++){
                        var detalle=$(detalles[i]);
                        if(detalle.attr('data-lib')===tipo && detalle.attr('data-lib')!==undefined ){
                            var row_id=detalle.attr("data-id");
                            var editable= row_sel_id===row_id;
                            var txt_objeto=detalle.attr("data-obj");
                            agregar_fila_vc(row_id, tipo,JSON.parse(txt_objeto),editable);
                            console.log(txt_objeto);
                        }
                        
                    }
//                    if($(row_select).attr('data-lib')===undefined){
//                        var objeto={tipo:"1",fecha:"",nit:"",aut:"",control:"",nro_fact:"",nro_pol:"",cliente:"",tot_fact:"",
//                                    tot_ice:"",imp_ext:"",imp_neto:"",iva:"",estado:"V"};                    
//                        agregar_fila_vc(row_sel_id,tipo,objeto,true);
//                    }
                }
//                else if(tipo==='r'){  
//                    
//                }
                if($(row_select).attr('data-lib')!==undefined){
                    $("#librov #lib_borrar").removeAttr('hidden');
                    $("#libroc #lib_borrar").removeAttr('hidden');
                    $("#libror #lib_borrar").removeAttr('hidden');
                }else{
                    $("#librov #lib_borrar").attr('hidden','');
                    $("#libroc lib_borrar").attr('hidden','');
                    $("#libror #lib_borrar").attr('hidden','');
                }
            }
            
            function preparar_formulario_venta_compra(tipo){
                var objeto=null;
                if($(row_select).attr('data-lib')!==undefined){
                    var txt_objeto=row_select.attr("data-obj");
                    objeto=JSON.parse(txt_objeto);                    
                }else{
                    var objeto={tipo:"1",fecha:$("#fecha_actual").val(),cta_disp:0,nit:"",aut:"",control:"",nro_fact:"",nro_pol:"",cliente:"",tot_fact:"",
                                    tot_ice:"",imp_ext:"",imp_neto:"",iva:"",estado:"V", pagos:[]};     
                }
//                console.log(objeto.neto);
//                console.log(objeto);
                if(tipo==='c'){
                    $('#fancy_div #lib_tipo').val(objeto.tipo);
                }
                $('#fancy_div #lib_fecha').val(objeto.fecha);
                $('#fancy_div #lib_ctas_act_disp option[value="'+objeto.cta_disp+'"]').attr('selected','selected');
                $('#fancy_div #lib_nit').val(objeto.nit);
                $('#fancy_div #lib_aut').val(objeto.aut);
                $('#fancy_div #lib_control').val(objeto.control);
                $('#fancy_div #lib_nro_fact').val(objeto.nro_fact);
                if(tipo==='c'){
                    $('#fancy_div #lib_nro_pol').val(objeto.nro_pol);
                }
                $('#fancy_div #lib_cliente').val(objeto.cliente);
                $('#fancy_div #lib_tot_fact').val(objeto.tot_fact);
                $('#fancy_div #lib_tot_ice').val(objeto.tot_ice);
                $('#fancy_div #lib_imp_ext').val(objeto.imp_ext);
                $('#fancy_div #lib_imp_neto').val(objeto.imp_neto);
                $('#fancy_div #lib_iva').val(objeto.iva);
                if(tipo==='v'){
                    $('#fancy_div #lib_estado option[value="'+objeto.estado+'"]').attr('selected','selected');
                }
                if(tipo==='c'){
                    var pagos=objeto.pagos;
                    for(var i=0;i<pagos.length;i++){
                        agregar_detatlle_pagos_compra(pagos[i].cc_id,pagos[i].cc_nombre,pagos[i].cc_monto);
                    }
                }
            }
            function preparar_formulario(){
                var objeto={fecha:$("#fecha_actual").val()};

                if($(row_select).attr('data-lib')!==undefined){
                    var txt_objeto=row_select.attr("data-obj");
                    objeto=JSON.parse(txt_objeto);                    
                }
//                console.log(objeto.neto);
//                console.log(objeto);
                $("#fancy_div #ret_fecha").val(objeto.fecha);
                $('#fancy_div #ret_ctas_act_disp option[value="'+objeto.cta_disp+'"]').attr('selected','selected');
                $("#fancy_div #ret_neto").val(objeto.neto);
                $('#fancy_div #ret_tipo option[value="'+objeto.tipo+'"]').attr('selected','selected');
                $('#fancy_div #ret_asumido option[value="'+objeto.asumido+'"]').attr('selected','selected');
                $('#fancy_div #ret_it').val(objeto.it);
                $('#fancy_div #ret_iue').val(objeto.iue);
                $('#fancy_div #ret_res').val(objeto.res);                
                
            }
            
            $("#lib_aceptar").live("click",function (){
                var tipo=$("#fancy_div #lib_aceptar").attr('data-tipo');
                if(tipo==='r'){
                    if(!validar_retencion()){
                        return false;
                    }                    
                }else if(tipo==='v'||tipo==='c'){
                    if(!validar_libro_vc(tipo)){
                        return false;
                    }
                }
                lib_aceptar=true;
                $.fn.fancybox.close();
                
                
            });
            
            $("#lib_borrar").live("click",function (){
                lib_aceptar=true;
                lib_borrar=true;
                $.fn.fancybox.close();
            });
            
            $("#lib_cancelar").live("click",function (){
                $.fn.fancybox.close();                
            });
            function trim(str) {
                return str.replace(/^\s+|\s+$/g,"");
            }
            function validar_libro_vc(tipo){
                var sw=true;
                if(tipo==='c'){
                    if(trim($('#fancy_div #lib_tipo').val())===''){
                        $('#fancy_div #msj_lib_tipo').html('Ingrese un <b>Tipo de Factura</b>');
                        $('#fancy_div #msj_lib_tipo').fadeIn(500);
                        sw=false;
                    }
                }
                if(trim($('#fancy_div #lib_fecha').val())===''){
                    $('#fancy_div #msj_lib_fecha').html('Ingrese una <b>Fecha</b> valida');
                    $('#fancy_div #msj_lib_fecha').fadeIn(500);
                    sw=false;
                }
                if(trim($('#fancy_div #lib_nit').val())===''){
                    $('#fancy_div #msj_lib_nit').html('Seleccione un <b>Nit</b>');
                    $('#fancy_div #msj_lib_nit').fadeIn(500);
                    sw=false;
                }
                if(trim($('#fancy_div #lib_aut').val())===''){
                    $('#fancy_div #msj_lib_aut').html('Ingrese un <b>Nro. de Autorización</b>');
                    $('#fancy_div #msj_lib_aut').fadeIn(500);
                    sw=false;
                }
                if(trim($('#fancy_div #lib_control').val())===''){
                    $('#fancy_div #msj_lib_control').html('Ingrese un <b>Cod. de Control</b>');
                    $('#fancy_div #msj_lib_control').fadeIn(500);
                    sw=false;
                }
                if(trim($('#fancy_div #lib_nro_fact').val())===''){
                    $('#fancy_div #msj_lib_nro_fact').html('Ingrese un <b>Nro. de Factura</b>');
                    $('#fancy_div #msj_lib_nro_fact').fadeIn(500);
                    sw=false;
                }
                if(tipo==='c'){
                    if(trim($('#fancy_div #lib_nro_pol').val())===''){
                        $('#fancy_div #msj_lib_nro_pol').html('Ingrese un <b>Nro. de Poliza</b>');
                        $('#fancy_div #msj_lib_nro_pol').fadeIn(500);
                        sw=false;
                    }
                }
                if(trim($('#fancy_div #lib_cliente').val())===''){
                    $('#fancy_div #msj_lib_cliente').html('Ingrese <b>Razon Social</b>');
                    $('#fancy_div #msj_lib_cliente').fadeIn(500);
                    sw=false;
                }
                if(trim($('#fancy_div #lib_tot_fact').val())===''){
                    $('#fancy_div #msj_lib_tot_fact').html('Ingrese <b>Total Factura</b>');
                    $('#fancy_div #msj_lib_tot_fact').fadeIn(500);
                    sw=false;
                }
                if(tipo==='c'){
                    var monto_neto=$('#fancy_div #lib_imp_neto').val()*1;
                    console.info(sumar_gastos_montos()+' == '+monto_neto);
                    if(sumar_gastos_montos()!==monto_neto){/// _validar_costos
                        $('#fancy_div #msj_gasto_monto').html('Los montos no concuerdan con el <b>Importe Neto</b>');
                        $('#fancy_div #msj_gasto_monto').fadeIn(500);
                        sw=false;
                    }
                }
                return sw;
            }
            
            function sumar_gastos_montos(){
                var montos=$('#fancy_div .lib_list_gasto_monto');
                var sum_monto=0;
                for(var i=0;i<montos.size();i++){
                    sum_monto+=$(montos[i]).val()*1;
                }
                return sum_monto;
            }
            
            function validar_retencion(){
                var fecha=$('#fancy_div #ret_fecha').val();
                var cta_disp=$('#fancy_div #ret_ctas_act_disp option:selected').val();
                var neto=$('#fancy_div #ret_neto').val();
                if(fecha===''){
                    $('#fancy_div #msj_ret_fecha').html('Ingrese una <b>Fecha</b> valida');
                    $('#fancy_div #msj_ret_fecha').fadeIn(500);
                }
                if(cta_disp==='0'){
                    $('#fancy_div #msj_ret_ctas_act_disp').html('Seleccione una <b>Cuenta</b>');
                    $('#fancy_div #msj_ret_ctas_act_disp').fadeIn(500);
                }
                if(neto===''){
                    $('#fancy_div #msj_ret_neto').html('Ingrese un <b>Importe Neto</b>');
                    $('#fancy_div #msj_ret_neto').fadeIn(500);
                }
                return neto!=='' && cta_disp!=='0' && fecha!=='';
            }
            
            $("#fancy_div #ret_fecha, #fancy_div #ret_neto").live('keyup',function (){
                if($(this).val()!==''){
                    var id=$(this).attr('id');
                    $("#fancy_div #msj_"+id).fadeOut(500);
                }
            });
            $(".txt_detalle").live('keyup',function (){
                if($(this).val()!==''){
                    var id=$(this).attr('id');
                    $("#fancy_div #msj_"+id).fadeOut(500);
                }
            });
            
            $("#fancy_div #ret_ctas_act_disp").live('change',function (){                                
                $("#fancy_div #msj_ret_ctas_act_disp").fadeOut(500);                
            });

            function agregar_fila_vc(inputId, tipo,objeto,editable){                
                var sel="1";
                if(!editable){                    
                    sel='';
                }
                var fila="";
                fila += '<tr data-id="'+inputId+'" data-sel="'+sel+'">';
                if(tipo==='c'){
                    fila += '   <td>'+objeto.tipo+'</td>';
                }
                fila += '   <td>'+objeto.fecha+'</td>';
                fila += '   <td>'+objeto.nit+'</td>';
                fila += '   <td>'+objeto.aut+'</td>';
                fila += '   <td>'+objeto.control+'</td>';
                fila += '   <td>'+objeto.nro_fact+'</td>';
                if(tipo==='c'){
                    fila += '   <td>'+objeto.nro_pol+'</td>';
                }
                fila += '   <td>'+objeto.cliente+'</td>';
                fila += '   <td>'+objeto.tot_fact+'</td>';
                fila += '   <td>'+objeto.tot_ice+'</td>';
                fila += '   <td>'+objeto.imp_ext+'</td>';
                fila += '   <td>'+objeto.imp_neto+'</td>';
                fila += '   <td>'+objeto.iva+'</td>';
                if(tipo==='v'){
                    fila += '   <td>'+objeto.estado+'</td>';
                }
                fila += '</tr>';
                addTableRow('#'+tipo+'_detalle tbody', fila);
            }
            
            $(".img-del-vc").live('click',function(){
                $(this).parent().parent().parent().remove();
            });
            $(".txt_detalle,#fancy_div #ret_neto").live('keypress',function(evt){
                if(evt.keyCode===13){
                    $("#lib_aceptar").trigger('click');
                }
            });
            
            $("#fancy_div #lib_tot_fact,#fancy_div #lib_tot_ice,#fancy_div #lib_imp_ext,#fancy_div #lib_gasto").live('keypress', function(e) {
                if (e.keyCode !== 13 && e.keyCode !== 8 && e.keyCode !== 46 && e.keyCode !== 37 && e.keyCode !== 39 && e.keyCode !== 35 && e.keyCode !== 36 && e.keyCode !== 9) {                        
                    var valor = $(this).val();
                    var char = String.fromCharCode(e.which);
                    valor = valor + char;                        
                    if (!/^([0-9])*[.]?[0-9]*$/.test(valor)) {
                        e.preventDefault();
                    } 
                }
            });
            $("#fancy_div #lib_tot_fact,#fancy_div #lib_tot_ice,#fancy_div #lib_imp_ext").live('keyup', function(e) {
//                if (e.keyCode === 8 || e.keyCode === 46) {                    
                    var tf=($("#fancy_div #lib_tot_fact").val())*1;
                    var ice=($("#fancy_div #lib_tot_ice").val())*1;
                    var ext=($("#fancy_div #lib_imp_ext").val())*1;
                    var imp_neto=tf-ice-ext;
                    var iva=$("#val_iva").val();
                    var imp_neto=tf-ice-ext;
                    var val_iva=imp_neto*iva;
                    $("#fancy_div #lib_imp_neto").val(imp_neto);
                    $("#fancy_div #lib_iva").val(val_iva);
//                }
            });
            $("#fancy_div #lib_nit").live(('focusout'), function(){
                var nit=$(this).val();
                nit = nit.replace(/^\s*|\s*$/g,"");
                if(nit!==''){
//                    var fila=$(this).parent().parent();
                    $.get("AjaxRequest.php?peticion=cliente&nit="+nit,function (respuesta){
                        var nombre=$('<div/>').html(respuesta).text();
                        $("#fancy_div #lib_cliente").val(nombre);
                    });
                }
            });
            $("#comp_mon_id").live('change', function (){
                cambiar_valores_moneda();
            });
        </script>
        <?php
    }
    function formulario_egreso($tipo) {

        switch ($tipo) {
            case 'ver': {
                    $ver = true;
                    break;
                }
            case 'cargar': {
                    $cargar = true;
                    break;
                }
        }
        $url = $this->link . '?mod=' . $this->modulo;

        $red = $url;

        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }

        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }
        ?>
        <style>
            .img-del-det{
                float:none; cursor: pointer;
            }
        </style>
        <?php
        $info="";
        if(isset($_GET['info'])){
            $info="&info=ok";
        }
        ?>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url . '&form=' . $_GET['form'].$info; ?>" method="POST" enctype="multipart/form-data">  
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
            <input type="hidden" id="comp_tco_id" name="comp_tco_id" value="2"/>
            <div id="FormSent" style="width:100%; position: relative">
                <div id="ContenedorSeleccion" >
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Nro</div>
                        <div id="CajaInput">
                            <div id="campo_nro">
                                <input class="caja_texto" name="comp_nro" id="comp_nro" size="5" value="<?php echo $_POST['comp_nro']; ?>" type="text" readonly="readonly">
                            </div>								 
                        </div>                        					
                    </div>
                    <!--Fin-->                    
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Nro Doc</div>
                        <div id="CajaInput">
                            <input class="caja_texto" name="comp_nro_documento" id="comp_nro_documento" size="5" value="<?php echo $_POST['comp_nro_documento']; ?>" type="text">
                        </div> 	  								
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Fecha</div>
                        <div id="CajaInput">
                            <input class="caja_texto" name="comp_fecha" id="comp_fecha" size="12" value="<?php
                            $fecha = isset($_POST['comp_fecha']) ? $_POST['comp_fecha'] : date('d/m/Y');
                            echo $fecha;
                            ?>" type="text" >
                            <input name="but_fecha_pago" id="but_fecha" class="boton_fecha" value="..." type="button">
                            <input type="hidden" name="cmp_peri_id" id="cmp_peri_id" value=""/>
                            <span id="label-id-periodo"></span>
                        </div>
                        <script type="text/javascript">
                            fecha_sel = "";
                            Calendar.setup({
                                inputField: "comp_fecha",
                                ifFormat: "%d/%m/%Y",
                                button: "but_fecha",
                                onUpdate: obtener_periodo
                            });
                            $("#comp_fecha").mask("99/99/9999");
                            obtener_periodo();



                        </script>                        
                    </div>
<!--                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">*</span>Unidad de Negocio</div>
                        <div id="CajaInput">
                            <select id="comp_une_id" name="comp_une_id">
                                <option value="">Seleccione</option>
                                <?php // $fun=new FUNCIONES();?>
                                <?php // $fun->combo("select une_id as id, une_nombre as nombre from con_unidad_negocio where une_eliminado='no'", $_POST[comp_une_id]);?>
                            </select>
                        </div>
                    </div>-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">*</span>Moneda</div>
                        <div id="CajaInput">
                            <select name="comp_mon_id" id="comp_mon_id" class="caja_texto">
                                <!--<option value="">Seleccione</option>-->
                                <?php
                                $fun = NEW FUNCIONES;
                                $fun->combo("select mon_id as id,mon_titulo as nombre from con_moneda order by mon_id asc", $_POST['comp_mon_id']);
                                ?>
                            </select>
                        </div>
                    </div>
                    <!--Fin-->
                    <div id="box_forma_pago" style="position: absolute; top: 95px;left: 400px; width: 500px">
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Forma de Pago:</div>
                            <div id="CajaInput">
                                <select name="comp_forma_pago" id="comp_forma_pago">
                                    <option value="Efectivo" <?php echo $_POST['comp_forma_pago']=='Efectivo'?'selected':''; ?> >Efectivo</option>
                                    <option value="Cheque" <?php echo $_POST['comp_forma_pago']=='Cheque'?'selected':''; ?> >Cheque</option>
                                    <option value="Deposito" <?php echo $_POST['comp_forma_pago']=='Deposito'?'selected':''; ?> >Deposito</option>
                                    <option value="Transferencia" <?php echo $_POST['comp_forma_pago']=='Transferencia'?'selected':''; ?> >Transferencia</option>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->   
                        <!--Inicio-->
                        <div id="ContenedorDiv" >
                            <div id="box_banco_id" hidden="">
                                <div class="Etiqueta" ><span id="tit_ban_id">Banco A:</span></div>
                                <div id="CajaInput">
                                    <select name="comp_ban_id" class="caja_texto">
                                        <option value="">Seleccione</option>
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("select ban_id as id,ban_nombre as nombre from con_banco where ban_eliminado='No' order by ban_id ", $_POST['comp_ban_id']);
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->   
                        <!--Inicio-->
                        <div id="ContenedorDiv" >
                            <div id="box_banco_char" hidden="">                                
                                <div class="Etiqueta" style="" ><span id="tit_ban_char">Banco:</span></div>
                                <div id="CajaInput">
                                    <input type="text" name="comp_ban_char" id="comp_ban_char" value="<?php echo $_POST['comp_ban_char'];?>" size="25"/>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->  
                        <!--Inicio-->
                        <div id="ContenedorDiv" >
                            <div id="box_banco_nro" hidden="">
                                <div class="Etiqueta" style="" ><span id="tit_ban_nro">Nro:</span></div>
                                <div id="CajaInput">
                                    <input type="text" name="comp_ban_nro" id="comp_ban_nro" value="<?php echo $_POST['comp_ban_nro'];?>" size="25"/>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->  
                        <script>
                            activar_bancos();
                        </script>
                    </div>
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Pagado a:</div>
                        <div id="CajaInput">
                            <input type="text" class="caja_texto" name="comp_referido" id="comp_referido" size="45" maxlength="100" value="<?php echo $_POST['comp_referido']; ?>">
                        </div>
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Glosa:</div>
                        <div id="CajaInput">
                            <textarea name="comp_glosa" id="comp_glosa" rows="2" cols="33"><?php echo $_POST['comp_glosa']; ?></textarea>
                        </div>
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">  
                        <input type="hidden" name="nfilas" id="nfilas" value="1">
                        <input type="hidden" name="nfilasshadown" id="nfilasshadown" value="">
                        
                    </div>
                    <!--Fin-->

                    <div id="ContenedorDiv">
                        <div>
                            <?php $this->formulario_detalle(); ?>                            
                        </div>
                    </div>

                </div>

                <div id="ContenedorDiv">
                    <div id="CajaBotones">
                        <center>
                            <?php
                            if (!($ver)) {
                                ?>
                                <input type="button" id="btn-submit" class="boton" name="" value="Guardar">

                                <?php
                            } else {
                                ?>
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                <?php
                            }
                            ?>
                        </center>
                    </div>
                </div>	
            </div>
        </form>
        <?php
    }

    function formulario_ingreso($tipo) {        
        switch ($tipo) {
            case 'ver': {
                    $ver = true;
                    break;
                }

            case 'cargar': {
                    $cargar = true;
                    break;
                }
        }
        $url = $this->link . '?mod=' . $this->modulo;

        $red = $url;

        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }

        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }
        ?>
        <style>
            .img-del-det{
                float:none; cursor: pointer;
            }
        </style>
        <?php
        $info="";
        if(isset($_GET['info'])){
            $info="&info=ok";
        }
        ?>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url . '&form=' . $_GET['form'].$info; ?>" method="POST" enctype="multipart/form-data">  
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
            <input type="hidden" id="comp_tco_id" name="comp_tco_id" value="1"/>
            <div id="FormSent" style="width:100%; position: relative">                
                <div id="ContenedorSeleccion" >                    
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Nro</div>
                        <div id="CajaInput">
                            <div id="campo_nro">
                                <input class="caja_texto" name="comp_nro" id="comp_nro" size="5" value="<?php echo $_POST['comp_nro']; ?>" type="text" readonly="readonly">
                            </div>								 
                        </div>                        
                    </div>
                    <!--Fin-->                    
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Nro Doc</div>
                        <div id="CajaInput">
                            <input class="caja_texto" name="comp_nro_documento" id="comp_nro_documento" size="5" value="<?php echo $_POST['comp_nro_documento']; ?>" type="text">
                        </div> 	  								
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Fecha</div>
                        <div id="CajaInput">
                            <input class="caja_texto" name="comp_fecha" id="comp_fecha" size="12" value="<?php
                            $fecha = isset($_POST['comp_fecha']) ? $_POST['comp_fecha'] : date('d/m/Y');
                            echo $fecha;
                            ?>" type="text" >
                            <input name="but_fecha_pago" id="but_fecha" class="boton_fecha" value="..." type="button">
                            <input type="hidden" name="cmp_peri_id" id="cmp_peri_id" value=""/>
                            <span id="label-id-periodo"></span>
                        </div>
                        <script type="text/javascript">
                            fecha_sel = "";
                            Calendar.setup({
                                inputField: "comp_fecha",
                                ifFormat: "%d/%m/%Y",
                                button: "but_fecha",
                                onUpdate: obtener_periodo
                            });
                            $("#comp_fecha").mask("99/99/9999");
                            obtener_periodo();
                        </script>                        
                    </div>
<!--                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">*</span>Unidad de Negocio</div>
                        <div id="CajaInput">
                            <select id="comp_une_id" name="comp_une_id">
                                <option value="">Seleccione</option>
                                <?php // $fun=new FUNCIONES();?>
                                <?php // $fun->combo("select une_id as id, une_nombre as nombre from con_unidad_negocio where une_eliminado='no'", $_POST[comp_une_id]);?>
                            </select>
                        </div>
                    </div>-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">*</span>Moneda</div>
                        <div id="CajaInput">
                            <select name="comp_mon_id" id="comp_mon_id" class="caja_texto">
                                <!--<option value="">Seleccione</option>-->
                                <?php
                                $fun = NEW FUNCIONES;
                                $fun->combo("select mon_id as id,mon_titulo as nombre from con_moneda order by mon_id asc", $_POST['comp_mon_id']);
                                ?>
                            </select>
                        </div>
                    </div>
                    <!--Fin-->
                    <div id="box_forma_pago" style="position: absolute; top: 95px;left: 400px; width: 500px">
                        <!--Inicio-->
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Forma de Pago:</div>
                            <div id="CajaInput">
                                <select name="comp_forma_pago" id="comp_forma_pago">
                                    <option value="Efectivo" <?php echo $_POST['comp_forma_pago']=='Efectivo'?'selected':''; ?> >Efectivo</option>
                                    <option value="Cheque" <?php echo $_POST['comp_forma_pago']=='Cheque'?'selected':''; ?> >Cheque</option>
                                    <option value="Deposito" <?php echo $_POST['comp_forma_pago']=='Deposito'?'selected':''; ?> >Deposito</option>
                                    <option value="Transferencia" <?php echo $_POST['comp_forma_pago']=='Transferencia'?'selected':''; ?> >Transferencia</option>
                                </select>
                            </div>
                        </div>
                        <!--Fin-->   
                        <!--Inicio-->
                        <div id="ContenedorDiv" >
                            <div id="box_banco_id" hidden="">
                                <div class="Etiqueta" ><span id="tit_ban_id">Banco A:</span></div>
                                <div id="CajaInput">
                                    <select name="comp_ban_id" class="caja_texto">
                                        <option value="">Seleccione</option>
                                        <?php
                                        $fun = NEW FUNCIONES;
                                        $fun->combo("select ban_id as id,ban_nombre as nombre from con_banco where ban_eliminado='No' order by ban_id ", $_POST['comp_ban_id']);
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->   
                        <!--Inicio-->
                        <div id="ContenedorDiv" >
                            <div id="box_banco_char" hidden="">                                
                                <div class="Etiqueta" style="" ><span id="tit_ban_char">Banco:</span></div>
                                <div id="CajaInput">
                                    <input type="text" name="comp_ban_char" id="comp_ban_char" value="<?php echo $_POST['comp_ban_char'];?>" size="25"/>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->  
                        <!--Inicio-->
                        <div id="ContenedorDiv" >
                            <div id="box_banco_nro" hidden="">
                                <div class="Etiqueta" style="" ><span id="tit_ban_nro">Nro:</span></div>
                                <div id="CajaInput">
                                    <input type="text" name="comp_ban_nro" id="comp_ban_nro" value="<?php echo $_POST['comp_ban_nro'];?>" size="25"/>
                                </div>
                            </div>
                        </div>
                        <!--Fin-->  
                        <script>
                            activar_bancos();
                        </script>
                    </div>
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Recib&iacute; de:</div>
                        <div id="CajaInput">
                            <input type="text" class="caja_texto" name="comp_referido" id="comp_referido" size="45" maxlength="100" value="<?php echo $_POST['comp_referido']; ?>">
                        </div>
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Glosa:</div>
                        <div id="CajaInput">
                            <textarea name="comp_glosa" id="comp_glosa" rows="2" cols="33"><?php echo $_POST['comp_glosa']; ?></textarea>
                        </div>
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">  
                        <input type="hidden" name="nfilas" id="nfilas" value="1">
                        <input type="hidden" name="nfilasshadown" id="nfilasshadown" value="">                        
                    </div>
                    <!--Fin-->

                    <div id="ContenedorDiv">
                        <div>
                            <?php $this->formulario_detalle(); ?>
                        </div>
                    </div>

                </div>

                <div id="ContenedorDiv">
                    <div id="CajaBotones">
                        <center>
                            <?php
                            if (!($ver)) {
                                ?>
                                <input type="button" id="btn-submit" class="boton" name="" value="Guardar">

                                <?php
                            } else {
                                ?>
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                <?php
                            }
                            ?>
                        </center>
                    </div>
                </div>	
            </div>
        </form>
        <?php
    }

    function formulario_diario($tipo) {
        switch ($tipo) {
            case 'ver': {
                    $ver = true;
                    break;
                }

            case 'cargar': {
                    $cargar = true;
                    break;
                }
        }
        $url = $this->link . '?mod=' . $this->modulo;

        $red = $url;

        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }

        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }
        ?>
        <style>
            .img-del-det{
                float:none; cursor: pointer;
            }
        </style>

        <?php
        $info="";
        if(isset($_GET['info'])){
            $info="&info=ok";
        }
        ?>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url . '&form=' . $_GET['form'].$info; ?>" method="POST" enctype="multipart/form-data">  
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
            <input type="hidden" id="comp_tco_id" name="comp_tco_id" value="3"/>
            <div id="FormSent" style="width:100%">
                <div id="ContenedorSeleccion" >
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Nro</div>
                        <div id="CajaInput">
                            <div id="campo_nro">
                                <input class="caja_texto" name="comp_nro" id="comp_nro" size="5" value="<?php echo $_POST['comp_nro']; ?>" type="text" readonly="readonly">
                            </div>								 
                        </div>                        					
                    </div>
                    <!--Fin-->                    
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Fecha</div>
                        <div id="CajaInput">
                            <input class="caja_texto" name="comp_fecha" id="comp_fecha" size="12" value="<?php
                            $fecha = isset($_POST['comp_fecha']) ? $_POST['comp_fecha'] : date('d/m/Y');
                            echo $fecha;
                            ?>" type="text" >
                            <input name="but_fecha_pago" id="but_fecha" class="boton_fecha" value="..." type="button">
                            <input type="hidden" name="cmp_peri_id" id="cmp_peri_id" value=""/>
                            <span id="label-id-periodo"></span>
                        </div>
                        <script type="text/javascript">
                            fecha_sel = "";
                            Calendar.setup({
                                inputField: "comp_fecha",
                                ifFormat: "%d/%m/%Y",
                                button: "but_fecha",
                                onUpdate: obtener_periodo
                            });
                            $("#comp_fecha").mask("99/99/9999");
                            obtener_periodo();



                        </script>                        
                    </div>
<!--                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">*</span>Unidad de Negocio</div>
                        <div id="CajaInput">
                            <select id="comp_une_id" name="comp_une_id">
                                <option value="">Seleccione</option>
                                <?php // $fun=new FUNCIONES();?>
                                <?php // $fun->combo("select une_id as id, une_nombre as nombre from con_unidad_negocio where une_eliminado='no'", $_POST[comp_une_id]);?>
                            </select>
                        </div>
                    </div>-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" ><span class="flechas1">*</span>Moneda</div>
                        <div id="CajaInput">
                            <select name="comp_mon_id" id="comp_mon_id" class="caja_texto">
                                <!--<option value="">Seleccione</option>-->
                                <?php
                                $fun = NEW FUNCIONES;
                                $fun->combo("select mon_id as id,mon_titulo as nombre from con_moneda order by mon_id asc", $_POST['comp_mon_id']);
                                ?>
                            </select>
                        </div>
                    </div>
                    <!--Fin-->

                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Por los Siguiente:</div>
                        <div id="CajaInput">
                            <textarea name="comp_glosa" id="comp_glosa" rows="2" cols="33"><?php echo $_POST['comp_glosa']; ?></textarea>
                        </div>
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">  
                        <input type="hidden" name="nfilas" id="nfilas" value="1">
                        <input type="hidden" name="nfilasshadown" id="nfilasshadown" value="">                        
                    </div>
                    <!--Fin-->

                    <div id="ContenedorDiv">
                        <div>
                            <?php $this->formulario_detalle(); ?>
                        </div>
                    </div>

                </div>

                <div id="ContenedorDiv">
                    <div id="CajaBotones">
                        <center>
                            <?php
                            if (!($ver)) {
                                ?>
                                <input type="button" id="btn-submit" class="boton" name="" value="Guardar">

                                <?php
                            } else {
                                ?>
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                <?php
                            }
                            ?>
                        </center>
                    </div>
                </div>	
            </div>
        </form>
        <?php
    }

    function formulario_ajuste($tipo) {
        switch ($tipo) {
            case 'ver': {
                    $ver = true;
                    break;
                }

            case 'cargar': {
                    $cargar = true;
                    break;
                }
        }
        $url = $this->link . '?mod=' . $this->modulo;

        $red = $url;

        if (!($ver)) {
            $url.="&tarea=" . $_GET['tarea'];
        }

        if ($cargar) {
            $url.='&id=' . $_GET['id'];
        }
        ?>
        <style>
            .img-del-det{
                float:none; cursor: pointer;
            }
        </style>

        <?php
        $info="";
        if(isset($_GET['info'])){
            $info="&info=ok";
        }
        ?>
        <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url . '&form=' . $_GET['form'].$info; ?>" method="POST" enctype="multipart/form-data">  
            <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
            <input type="hidden" id="comp_tco_id" name="comp_tco_id" value="3"/>
            <div id="FormSent" style="width:100%">
                <div id="ContenedorSeleccion" >
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Nro</div>
                        <div id="CajaInput">
                            <div id="campo_nro">
                                <input class="caja_texto" name="comp_nro" id="comp_nro" size="5" value="<?php echo $_POST['comp_nro']; ?>" type="text" readonly="readonly">
                            </div>								 
                        </div>                        					
                    </div>
                    <!--Fin-->                    
                    <!--Inicio-->
                    <div id="ContenedorDiv" hidden="">
                        <div class="Etiqueta" >Fecha</div>
                        <div id="CajaInput">
                            <input class="caja_texto" name="comp_fecha" id="comp_fecha" size="12" value="<?php
                            $fecha = isset($_POST['comp_fecha']) ? $_POST['comp_fecha'] : '';
                            echo $fecha;
                            ?>" type="text" >
                            <input name="but_fecha_pago" id="but_fecha" class="boton_fecha" value="..." type="button">
                            <input type="hidden" name="cmp_peri_id" id="cmp_peri_id" value="<?php echo $_POST['cmp_peri_id'];?>"/>
                            <span id="label-id-periodo"></span>
                            
                        </div>
                        <script type="text/javascript">
//                            fecha_sel = "";
//                            Calendar.setup({
//                                inputField: "comp_fecha",
//                                ifFormat: "%d/%m/%Y",
//                                button: "but_fecha",
//                                onUpdate: obtener_periodo
//                            });
//                            $("#comp_fecha").mask("99/99/9999");
//                            obtener_periodo();
                        

                        </script>                        
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Periodo</div>
                        <div id="CajaInput" >
                            <select id="select_periodo">
                                <option value="0">Seleccione</option>
                            <?php
                                $ges_id=$_SESSION['ges_id'];
                                if($_POST['cmp_ges_id']){
                                    $ges_id=$_POST['cmp_ges_id'];
                                }
                                $sql="select pdo_id as id, pdo_descripcion as nombre, concat(pdo_fecha_inicio,',',pdo_fecha_fin) as fechas from con_periodo where pdo_ges_id='$ges_id' and pdo_eliminado='No' order by pdo_fecha_inicio desc";
                                
                                FUNCIONES::combo_data($sql,'fechas' ,$_POST['cmp_peri_id']);
                            ?>
                            </select>                            
                        </div>
                        <script type="text/javascript">
                            $("#select_periodo").change(function (){
                                if($(this).val()!=='0'){
                                    asignar_fecha();
                                    $("#cmp_peri_id").val($(this).val());
                                }else{
                                    $("#comp_fecha").val('');
                                    $("#cmp_peri_id").val('');
                                }
                                
                            });
                            function asignar_fecha() {
                                var fechas = $("#select_periodo option:selected").attr("data_fechas");
                                var afechas = fechas.split(',');                                
                                var afechaf = afechas[1].split('-');                                
                                var fechaf = afechaf[2] + '/' + afechaf[1] + '/' + afechaf[0];                                
                                $("#comp_fecha").val(fechaf);
                            }
                        </script>                        
                    </div>
                    <!--Fin-->
                    
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Por los Siguiente:</div>
                        <div id="CajaInput">
                            <textarea name="comp_glosa" id="comp_glosa" rows="2" cols="33"><?php echo $_POST['comp_glosa']; ?></textarea>
                        </div>
                    </div>
                    <!--Fin-->                    
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >Tipo de Ajuste:</div>
                        <div id="CajaInput">
                            <select name="comp_tipo_ajuste">
                                <option value="0">Seleccione</option>
                                <option value="aj_ufv" <?php if($_POST['comp_tipo_ajuste']=='aj_ufv') echo "selected=''"?> >Ajuste de UFV</option>
                                <option value="dif_camb" <?php if($_POST['comp_tipo_ajuste']=='dif_camb') echo "selected=''"?> >Diferencia de Cambio</option>
                            </select>
                        </div>
                    </div>
                    <!--Fin-->
                    <!--Inicio-->
                    <div id="ContenedorDiv">
                        <div class="Etiqueta" >&nbsp;</div>
                        <div id="CajaInput">
                            <input type="hidden" name="generar_ajuste" id="generar_ajuste" value="0">
                            <input type="button" id="btn-generar-ajuste" class="boton" name="" value="Generar">
                        </div>
                    </div>
                    <!--Fin-->                    
                    <script>
                        $("#btn-generar-ajuste").click(function (){
                            $("#generar_ajuste").val("1");
                            document.frm_sentencia.submit();
                        });
                    </script>
                    <!--Inicio-->
                    <div id="ContenedorDiv">  
                        <input type="hidden" name="nfilas" id="nfilas" value="1">
                        <input type="hidden" name="nfilasshadown" id="nfilasshadown" value="">                        
                    </div>
                    <!--Fin-->
                    <div id="ContenedorDiv">
                        <div>
                            <?php $this->formulario_detalle(); ?>
                        </div>
                    </div>
                </div>
                <div id="ContenedorDiv">
                    <div id="CajaBotones">
                        <center>
                            <?php
                            if (!($ver)) {
                                ?>
                                <input type="button" id="btn-submit" class="boton" name="" value="Guardar">
                                <?php
                            } else {
                                ?>
                                <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = '<?php echo $red; ?>';">
                                <?php
                            }
                            ?>
                        </center>
                    </div>
                </div>	
            </div>
        </form>
        <?php
    }
    function eliminar_tcp() {
        $conect=new ADO();                
        $cmp_id=$_POST['cmp_id'];
        $sql="update con_comprobante set cmp_eliminado='Si' where cmp_id='$cmp_id'";
        $conect->ejecutar($sql);
        $sql="update con_comprobante_detalle set cde_eliminado='Si' where cde_cmp_id='$cmp_id'";
        $conect->ejecutar($sql);        
        $mensaje = 'Comprobante Eliminado Correctamente.';
        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
        
    }
    function insertar_tcp() {
//        echo "<pre>";
//        print_r($_POST);
//        echo "</pre>";
        $conec = new ADO();
        $formulario = $_GET['form'];
        switch ($formulario) {
            case "Ingreso":
                $tipo_comprobante = 1;
                break;
            case "Egreso":
                $tipo_comprobante = 2;
                break;
            case "Diario":
                $tipo_comprobante = 3;
                break;
            case "Ajustes":
                $tipo_comprobante = 4;
                $_POST['comp_mon_id']=1;
                break;
            default:
                break;
        }
        $conversor=new convertir();
        $cmp_tco_id = $tipo_comprobante;
        $cmp_mon_id = $_POST['comp_mon_id'];
        $cmp_nro_documento = $_POST['comp_nro_documento'];
        $cmp_fecha = $conversor->get_fecha_mysql($_POST['comp_fecha']);
        $cmp_ges_id = $_SESSION['ges_id'];
        $cmp_peri_id = $_POST['cmp_peri_id'];
        
        $cmp_tipo_ajuste='null';
        if($tipo_comprobante==4){
            $cmp_tipo_ajuste=$_POST['comp_tipo_ajuste'];
        }
        $cmp_forma_pago=$_POST['comp_forma_pago'];
        if($cmp_forma_pago=='Efectivo'|| $cmp_forma_pago==''){
            $cmp_ban_id=0;
            $cmp_ban_char='';
            $cmp_ban_nro='';
        }  elseif ($cmp_forma_pago=='Cheque') {            
            if($tipo_comprobante==1){
                $cmp_ban_id=0;
                $cmp_ban_char=$_POST['comp_ban_char'];        
                $cmp_ban_nro=$_POST['comp_ban_nro'];                
            }  elseif ($tipo_comprobante==2) {
                $cmp_ban_id=$_POST['comp_ban_id'];
                $cmp_ban_char='';
                $cmp_ban_nro=$_POST['comp_ban_nro'];
            }
            
        } elseif($cmp_forma_pago=='Deposito'){
            if($tipo_comprobante==1){
                $cmp_ban_id=$_POST['comp_ban_id'];
                $cmp_ban_char='';
                $cmp_ban_nro=$_POST['comp_ban_nro'];               
            }  elseif ($tipo_comprobante==2) {
                $cmp_ban_id=0;
                $cmp_ban_char=$_POST['comp_ban_char'];        
                $cmp_ban_nro=$_POST['comp_ban_nro'];
            }
        
        } elseif ($cmp_forma_pago=='Transferencia' ) {
            $cmp_ban_id=$_POST['comp_ban_id'];        
            $cmp_ban_char=$_POST['comp_ban_char'];        
            $cmp_ban_nro=$_POST['comp_ban_nro'];
        }
        
        $cmp_glosa = $_POST['comp_glosa'];
        $cmp_referido = $_POST['comp_referido'];
        $cmp_usu_id = $_SESSION['usu_per_id'];
        $cmp_usu_cre = $_SESSION['id'];
        $cmp_revisado = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='revisado' and conf_ges_id='$_SESSION[ges_id]'", "conf_valor");
        $cmp_aprobado = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='aprobado' and conf_ges_id='$_SESSION[ges_id]'", "conf_valor");
        $cmp_estado = 'Activo';
        $cmp_fecha_cre = date('Y-m-d H:i:s');
        $cmp_fecha_modi = '0000-00-00';
        $cmp_eliminado = 'No';

        $cmp_nro = FUNCIONES::obtener_per_tc_nro($cmp_peri_id, $tipo_comprobante);

        $insert = "insert into con_comprobante (
                        cmp_tco_id, cmp_mon_id, cmp_nro, cmp_nro_documento, cmp_fecha, cmp_ges_id, 
                        cmp_peri_id,cmp_forma_pago, cmp_ban_id, cmp_ban_char, cmp_ban_nro, cmp_glosa, 
                        cmp_referido,cmp_usu_id, cmp_revisado, cmp_aprobado, cmp_estado,cmp_usu_cre, cmp_fecha_cre, 
                        cmp_fecha_modi, cmp_eliminado,cmp_tipo_ajuste)
                 values(
                        '$cmp_tco_id', '$cmp_mon_id', $cmp_nro, '$cmp_nro_documento', '$cmp_fecha', '$cmp_ges_id', 
                        '$cmp_peri_id','$cmp_forma_pago',$cmp_ban_id,'$cmp_ban_char','$cmp_ban_nro','$cmp_glosa',
                        '$cmp_referido','$cmp_usu_id', '$cmp_revisado', '$cmp_aprobado','$cmp_estado','$cmp_usu_cre','$cmp_fecha_cre',
                        '$cmp_fecha_modi','$cmp_eliminado','$cmp_tipo_ajuste'
                );";
        
//        echo $insert;
        
        $conec->ejecutar($insert, false);
        $cmp_id = mysql_insert_id();
//        FUNCIONES::actualizar_per_tc_nro($cmp_peri_id, $tipo_comprobante, $cmp_nro);
        /** INSERTAR LOS DETALLES* */
        $numfilas = $_POST['num_filas'];
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp_fecha'");
        $cde_cmp_id = $cmp_id;
        $cde_eliminado = "No";
        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }
        $_i=1;
        $_j = 1;
        $padres=array();
        while ($_i <= $numfilas) {
            if(isset($_POST['cuen'.$_j])){
                $cde_secuencia = $_i;
                $cde_can_id = (isset($_POST['ca' . $_j]) && $_POST['ca' . $_j] != "") ? $_POST['ca' . $_j] : 0;
                $cde_cco_id = (isset($_POST['cc' . $_j]) && $_POST['cc' . $_j] != "") ? $_POST['cc' . $_j] : 0;
                $cde_cfl_id = (isset($_POST['cf' . $_j]) && $_POST['cf' . $_j] != "") ? $_POST['cf' . $_j] : 0;
                $cde_cue_id = (isset($_POST['cuen' . $_j]) && $_POST['cuen' . $_j] != "") ? $_POST['cuen' . $_j] : 0;
                $cde_glosa = $_POST['glosa' . $_j];
                $debe = $_POST['debe' . $_j];
                $haber = $_POST['haber' . $_j];
                $une_id = $_POST['une' . $_j];
                $valor = 0;
                if ($debe != "") {
                    $valor = floatval($debe) * 1;
                }
                if ($haber != "") {
                    $valor = floatval($haber) * (-1);
                }
                $val_bol = $valor * $tc;
                
                $lib = (isset($_POST['data_lib_' . $_j]) && $_POST['data_lib_' . $_j] != "") ? $_POST['data_lib_' . $_j] : 'null';
                $cu = (isset($_POST['data_cu_' . $_j]) && $_POST['data_cu_' . $_j] != "") ? $_POST['data_cu_' . $_j] : 'null';
                if($lib=='gv' || $lib=='gc' || $lib=='gr'){
                    $idpadre_ant = (isset($_POST['data_idpadre_' . $_j]) && $_POST['data_idpadre_' . $_j] != "") ? $_POST['data_idpadre_' . $_j] : '0';;
                    if(isset($padres[$idpadre_ant])){
                        $idpadre=$padres[$idpadre_ant];
                    }else{
                        $idpadre='null';
                    }                    
                }elseif($lib=='v' || $lib=='c'|| $lib =='r'){
                    $padres[$_j.'']=$_i.'';
                    $idpadre = 'null';
                }else{
                    $idpadre = 'null';
                }
                
                $sql = "insert into con_comprobante_detalle(
                            cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,
                            cde_valor,cde_glosa,cde_eliminado,cde_libro,cde_cu,cde_idpadre,cde_une_id
                            )values(
                            $cde_cmp_id, 1, $cde_secuencia, $cde_can_id, $cde_cco_id, $cde_cfl_id, $cde_cue_id, 
                            $val_bol,'$cde_glosa', '$cde_eliminado','$lib','$cu','$idpadre','$une_id '
                        )";
                $conec->ejecutar($sql);
//                echo $sql.';<br>';
                if($tipo_comprobante!=4){
                    $cambios->reset();
                    for ($i = 0; $i < $cambios->get_num_registros(); $i++) {
                        $cambio = $cambios->get_objeto();
                        $cde_mon_id = $cambio->tca_mon_id;
                        $cde_valor = $val_bol / $cambio->tca_valor;
                        $sql = "insert into con_comprobante_detalle(
                            cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,
                            cde_valor,cde_glosa,cde_eliminado,cde_libro,cde_cu,cde_idpadre,cde_une_id
                            )values(
                                $cde_cmp_id, $cde_mon_id, $cde_secuencia, $cde_can_id, $cde_cco_id, $cde_cfl_id, $cde_cue_id, 
                                $cde_valor,'$cde_glosa', '$cde_eliminado','$lib','$cu','$idpadre','$une_id'
                            )";
//                        echo $sql.';<br>';
                        $conec->ejecutar($sql);
                        $cambios->siguiente();
                    }
                }
//                echo '<br><br>';
                if($lib=='v'||$lib=='c'){
                    
                    $tlibro=$lib=='v'?'Venta':'Compra';
                    $txt_libro=$_POST['data_obj_'.$_j];                    
                    $txt_libro=  htmlentities($txt_libro);

                    $txt_libro=str_replace("&quot;", '"', $txt_libro);
                    $txt_libro=str_replace('\"', '"', $txt_libro);

                    $libro= json_decode($txt_libro);

                    $cliente=html_entity_decode($libro->cliente);

                    $fecha_mysql=  FUNCIONES::get_fecha_mysql($libro->fecha);
//                    echo "--$--";
                    $pagos= json_encode($libro->pagos);
                    $pagos=  htmlentities($pagos);
                    $pagos=str_replace("&quot;", '"', $pagos);
                    $pagos=str_replace('\"', '"', $pagos);
                    
                    $sql = "insert into con_libro (lib_tipo, lib_fecha, lib_nit, lib_nro_autorizacion, lib_cod_control, lib_nro_factura, 
                                    lib_nro_poliza, lib_cliente, lib_tot_factura, lib_ice, lib_imp_exentos, lib_imp_neto, lib_iva,lib_estado,
                                    lib_libro, lib_cmp_id, lib_cde_sec, lib_ges_id, lib_cuenta_ie, lib_cuenta_act_disp,lib_pagos_cc, lib_eliminado)
                            values('$libro->tipo','$fecha_mysql', '$libro->nit', '$libro->aut', '$libro->control', '$libro->nro_fact',
                                '$libro->nro_pol', '$cliente', '$libro->tot_fact','$libro->tot_ice', '$libro->imp_ext','$libro->imp_neto','$libro->iva','$libro->estado',
                                '$tlibro','$cde_cmp_id','$cde_secuencia','$cmp_ges_id','$cde_cue_id','$libro->cta_disp','$pagos','no')";
//                    echo "<br>".$sql;
                    $conec->ejecutar($sql);
                    $this->actualizar_razon_social($libro->nit, $cliente);
                    
                }elseif($lib=='r'){
                    
                    $txt_retencion=$_POST['data_obj_'.$_j];       
//                    echo $txt_retencion.'<br>';
                    $txt_retencion=  htmlentities($txt_retencion);                   
//                    echo $txt_retencion.'<br>';
                    $txt_retencion=str_replace("&quot;", '"', $txt_retencion);
//                    echo $txt_retencion.'<br>';
                    $txt_retencion=str_replace('\"', '"', $txt_retencion);
//                    echo $txt_retencion.'<br>';

                    $retencion = json_decode($txt_retencion);

                    $fecha_mysql = FUNCIONES::get_fecha_mysql($retencion->fecha);

                    $sql = "insert into con_retencion (ret_fecha, ret_cuenta_gasto, ret_glosa, ret_neto, ret_it, ret_iue,ret_result, ret_cuenta_act_disp, ret_tipo, ret_asumido, 
                                                    ret_cmp_id, ret_cde_sec, ret_ges_id, ret_eliminado)
                                            values('$fecha_mysql', '$cde_cue_id', '$cde_glosa', '$retencion->neto', '$retencion->it', '$retencion->iue', '$retencion->res', '$retencion->cta_disp','$retencion->tipo', '$retencion->asumido','$cde_cmp_id','$cde_secuencia','$cmp_ges_id','No')";
//                                    echo "<br>".$sql;
                    $conec->ejecutar($sql);
//                    $this->actualizar_razon_social($retencion->nit, $cliente);
                }
                
                $_i++;
            }
            $_j++;
        }
        $this->imprimir_cmp($cmp_id);
    }

    function modificar_tcp() {
        $conec = new ADO();
        $conversor=new convertir();
        $formulario = $_GET['form'];
        switch ($formulario) {
            case "Ingreso":
                $tipo_comprobante = 1;
                break;
            case "Egreso":
                $tipo_comprobante = 2;
                break;
            case "Diario":
                $tipo_comprobante = 3;
                break;
            case "Ajustes":
                $tipo_comprobante = 4;
                break;
            default:
                break;
        }

        $cmp_id = $_GET['id'];
        $cmp_tco_id = $tipo_comprobante;
        $cmp_mon_id = $_POST['comp_mon_id'];
        $cmp_nro_documento = $_POST['comp_nro_documento'];
        $cmp_fecha = $conversor->get_fecha_mysql($_POST['comp_fecha']);
        $cmp_ges_id = $_SESSION['ges_id'];
        $cmp_peri_id = $_POST['cmp_peri_id'];
        $cmp_une_id= $_POST['comp_une_id'];
        
        
        $cmp_forma_pago=$_POST['comp_forma_pago'];
        if($cmp_forma_pago=='Efectivo'|| $cmp_forma_pago==''){
            $cmp_ban_id=0;
            $cmp_ban_char='';
            $cmp_ban_nro='';
        }  elseif ($cmp_forma_pago=='Cheque') {            
            if($tipo_comprobante==1){
                $cmp_ban_id=0;
                $cmp_ban_char=$_POST['comp_ban_char'];        
                $cmp_ban_nro=$_POST['comp_ban_nro'];                
            }  elseif ($tipo_comprobante==2) {
                $cmp_ban_id=$_POST['comp_ban_id'];
                $cmp_ban_char='';
                $cmp_ban_nro=$_POST['comp_ban_nro'];
            }
            
        } elseif($cmp_forma_pago=='Deposito'){
            if($tipo_comprobante==1){
                $cmp_ban_id=$_POST['comp_ban_id'];
                $cmp_ban_char='';
                $cmp_ban_nro=$_POST['comp_ban_nro'];               
            }  elseif ($tipo_comprobante==2) {
                $cmp_ban_id=0;
                $cmp_ban_char=$_POST['comp_ban_char'];        
                $cmp_ban_nro=$_POST['comp_ban_nro'];
            }
        
        } elseif ($cmp_forma_pago=='Transferencia' ) {
            $cmp_ban_id=$_POST['comp_ban_id'];        
            $cmp_ban_char=$_POST['comp_ban_char'];        
            $cmp_ban_nro=$_POST['comp_ban_nro'];
        }
        $cmp_glosa = $_POST['comp_glosa'];
        $cmp_referido = $_POST['comp_referido'];
        $cmp_usu_id = $_SESSION['usu_per_id'];
        $cmp_revisado = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='revisado' and conf_ges_id='$_SESSION[ges_id]'", "conf_valor");
        $cmp_aprobado = FUNCIONES::atributo_bd("con_configuracion", "conf_nombre='aprobado' and conf_ges_id='$_SESSION[ges_id]'", "conf_valor");
        $cmp_estado = 'Activo';
        $cmp_fecha_modi = date("Y-m-d H:i:s");

//        $cmp_nro = FUNCIONES::obtener_per_tc_nro($cmp_peri_id, $tipo_comprobante) + 1;
//      , cmp_nro=$cmp_nro
        $update = "UPDATE con_comprobante SET cmp_tco_id=$cmp_tco_id , cmp_mon_id=$cmp_mon_id , cmp_nro_documento='$cmp_nro_documento', cmp_fecha='$cmp_fecha', 
                    cmp_ges_id=$cmp_ges_id , cmp_peri_id=$cmp_peri_id ,cmp_forma_pago='$cmp_forma_pago', cmp_ban_id=$cmp_ban_id, cmp_ban_char='$cmp_ban_char',
                    cmp_ban_nro='$cmp_ban_nro', cmp_glosa='$cmp_glosa' , cmp_referido='$cmp_referido', cmp_usu_id=$cmp_usu_id ,cmp_revisado='$cmp_revisado', 
                    cmp_aprobado='$cmp_aprobado', cmp_estado='$cmp_estado' , cmp_fecha_modi='$cmp_fecha_modi', cmp_une_id='$cmp_une_id'
                    WHERE cmp_id=$cmp_id;";
        //echo $update;
        $conec->ejecutar($update);
        //FUNCIONES::actualizar_per_tc_nro($cmp_peri_id, $tipo_comprobante, $cmp_nro);
        /* --ELIMINAR DETALLES-- */
        $delete = "DELETE FROM con_comprobante_detalle WHERE cde_cmp_id=$cmp_id;";
        $conec->ejecutar($delete);
        /* --ELIMINAR DETALLES-- */
        /* --ELIMINAR LIBROS-- */
        $delete = "DELETE FROM con_libro WHERE lib_cmp_id=$cmp_id;";
        $conec->ejecutar($delete);
        /* --ELIMINAR LIBROS-- */
        /* --ELIMINAR RETENCIONES-- */
        $delete = "DELETE FROM con_retencion WHERE ret_cmp_id=$cmp_id;";
        $conec->ejecutar($delete);
        /* --ELIMINAR RETENCIONES-- */
        
        
        $numfilas = $_POST['num_filas'];
        $cambios = FUNCIONES::objetos_bd("con_tipo_cambio", "tca_fecha='$cmp_fecha'");
        $cde_cmp_id = $cmp_id;
        $cde_eliminado = "No";

        $tc = 1;
        for ($j = 0; $j < $cambios->get_num_registros(); $j++) {
            $cambio = $cambios->get_objeto();
            if ($cmp_mon_id == $cambio->tca_mon_id) {
                $tc = $cambio->tca_valor;
            }
            $cambios->siguiente();
        }
        $_i=1;
        $_j = 1;
        $padres=array();
//        echo "'".$numfilas."' <br>";
        while ($_i <= $numfilas) {
//            echo "'".$_POST['cuen'.$_j]."' ->";
//            echo "con j: ".$_j."<br>";
            if(isset($_POST['cuen'.$_j])){
                $cde_secuencia = $_i;
                $cde_can_id = (isset($_POST['ca' . $_j]) && $_POST['ca' . $_j] != "") ? $_POST['ca' . $_j] : 0;
                $cde_cco_id = (isset($_POST['cc' . $_j]) && $_POST['cc' . $_j] != "") ? $_POST['cc' . $_j] : 0;
                $cde_cfl_id = (isset($_POST['cf' . $_j]) && $_POST['cf' . $_j] != "") ? $_POST['cf' . $_j] : 0;
                $cde_cue_id = (isset($_POST['cuen' . $_j]) && $_POST['cuen' . $_j] != "") ? $_POST['cuen' . $_j] : 0;
                $cde_glosa = $_POST['glosa' . $_j];
                $debe = $_POST['debe' . $_j];
                $haber = $_POST['haber' . $_j];
                $une_id  = $_POST['une' . $_j];
                $valor = 0;
                if ($debe != "") {
                    $valor = floatval($debe) * 1;
                }
                if ($haber != "") {
                    $valor = floatval($haber) * (-1);
                }
                $val_bol = $valor * $tc;
                
                $lib = (isset($_POST['data_lib_' . $_j]) && $_POST['data_lib_' . $_j] != "") ? $_POST['data_lib_' . $_j] : 'null';
                $cu = (isset($_POST['data_cu_' . $_j]) && $_POST['data_cu_' . $_j] != "") ? $_POST['data_cu_' . $_j] : 'null';
                if($lib=='gv' || $lib=='gc' || $lib=='gr'){                    
                    $idpadre_ant = (isset($_POST['data_idpadre_' . $_j]) && $_POST['data_idpadre_' . $_j] != "") ? $_POST['data_idpadre_' . $_j] : '0';;
                    if(isset($padres[$idpadre_ant])){
                        $idpadre=$padres[$idpadre_ant];
                    }else{
                        $idpadre='null';
                    }                    
                }elseif($lib=='v' || $lib=='c' || $lib=='r'){
                    $padres[$_j.'']=$_i.'';
                    $idpadre = 'null';
                }else{
                    $idpadre = 'null';
                }
                
                 $sql = "insert into con_comprobante_detalle(
                            cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,
                            cde_valor,cde_glosa,cde_eliminado,cde_libro,cde_cu,cde_idpadre,cde_une_id
                            )values(
                            $cde_cmp_id, 1, $cde_secuencia, $cde_can_id, $cde_cco_id, $cde_cfl_id, $cde_cue_id, 
                            $val_bol,'$cde_glosa', '$cde_eliminado','$lib','$cu','$idpadre','$une_id'
                            )";
                $conec->ejecutar($sql);
//                echo $sql;
                $cambios->reset();
                for ($i = 0; $i < $cambios->get_num_registros(); $i++) {
                    $cambio = $cambios->get_objeto();
                    $cde_mon_id = $cambio->tca_mon_id;
                    $cde_valor = $val_bol / $cambio->tca_valor;
                    $sql = "insert into con_comprobante_detalle(
                            cde_cmp_id,cde_mon_id,cde_secuencia,cde_can_id,cde_cco_id,cde_cfl_id,cde_cue_id,
                            cde_valor,cde_glosa,cde_eliminado,cde_libro,cde_cu,cde_idpadre,cde_une_id
                            )values(
                                $cde_cmp_id, $cde_mon_id, $cde_secuencia, $cde_can_id, $cde_cco_id, $cde_cfl_id, $cde_cue_id, 
                                $cde_valor,'$cde_glosa', '$cde_eliminado','$lib','$cu','$idpadre','$une_id'
                            )";
                    $conec->ejecutar($sql);
                    $cambios->siguiente();
                }
                
                if($lib=='v'||$lib=='c' ){
                    $tlibro=$lib=='v'?'Venta':'Compra';
                    $txt_libro=$_POST['data_obj_'.$_j];
//                  
                    $txt_libro=  htmlentities($txt_libro);
                    $txt_libro=str_replace("&quot;", '"', $txt_libro);
                    $txt_libro=str_replace('\"', '"', $txt_libro);
                    
                    $libro= json_decode($txt_libro);
                    
                    $pagos= json_encode($libro->pagos);
                    $pagos=  htmlentities($pagos);
                    $pagos=str_replace("&quot;", '"', $pagos);
                    $pagos=str_replace('\"', '"', $pagos);
                    
                    $cliente=html_entity_decode($libro->cliente);
                    $fecha_mysql=  FUNCIONES::get_fecha_mysql($libro->fecha);                    
                    $sql = "insert into con_libro (lib_tipo, lib_fecha, lib_nit, lib_nro_autorizacion, lib_cod_control, lib_nro_factura, 
                                    lib_nro_poliza, lib_cliente, lib_tot_factura, lib_ice, lib_imp_exentos, lib_imp_neto, lib_iva,lib_estado,
                                    lib_libro, lib_cmp_id, lib_cde_sec, lib_ges_id,lib_cuenta_ie,lib_cuenta_act_disp,lib_pagos_cc, lib_eliminado)
                            values('$libro->tipo','$fecha_mysql', '$libro->nit', '$libro->aut', '$libro->control', '$libro->nro_fact',
                                '$libro->nro_pol', '$cliente', '$libro->tot_fact','$libro->tot_ice', '$libro->imp_ext','$libro->imp_neto','$libro->iva','$libro->estado',
                                '$tlibro','$cde_cmp_id','$cde_secuencia','$cmp_ges_id','$cde_cue_id','$libro->cta_disp','$pagos','no')";

                    $conec->ejecutar($sql);
                    $this->actualizar_razon_social($libro->nit, $cliente);
                }elseif($lib=='r'){
                    
                    $txt_retencion=$_POST['data_obj_'.$_j];       
//                    echo $txt_retencion.'<br>';
                    $txt_retencion=  htmlentities($txt_retencion);                   
//                    echo $txt_retencion.'<br>';
                    $txt_retencion=str_replace("&quot;", '"', $txt_retencion);
//                    echo $txt_retencion.'<br>';
                    $txt_retencion=str_replace('\"', '"', $txt_retencion);
//                    echo $txt_retencion.'<br>';
                    $retencion = json_decode($txt_retencion);

                    $fecha_mysql = FUNCIONES::get_fecha_mysql($retencion->fecha);

                    $sql = "insert into con_retencion (ret_fecha, ret_cuenta_gasto, ret_glosa, ret_neto, ret_it, ret_iue,ret_result, ret_cuenta_act_disp, ret_tipo, ret_asumido, 
                                                    ret_cmp_id, ret_cde_sec, ret_ges_id, ret_eliminado)
                                            values('$fecha_mysql', '$cde_cue_id', '$cde_glosa', '$retencion->neto', '$retencion->it', '$retencion->iue', '$retencion->res', '$retencion->cta_disp','$retencion->tipo', '$retencion->asumido','$cde_cmp_id','$cde_secuencia','$cmp_ges_id','No')";
//                                    echo "<br>".$sql;
                    $conec->ejecutar($sql);
//                    $this->actualizar_razon_social($retencion->nit, $cliente);
                }
                $_i++;
            }
            $_j++;
        }
        $this->imprimir_cmp($cmp_id);
    }
    public function actualizar_razon_social($nit,$razon_social){
        $rs=FUNCIONES::objeto_bd_sql("select * from con_razonsocial where rs_nit ='$nit';");
//        echo "'$rs'";
        if($rs==null){
            $conec = new ADO();
            $sql="insert into con_razonsocial (rs_nit, rs_nombre) VALUES ('$nit', '$razon_social');";
            $conec->ejecutar($sql);
        }else{
            if($razon_social!=$rs->rs_nombre){
                $conec = new ADO();
                $sql="update con_razonsocial set rs_nombre='$razon_social' where rs_nit='$nit';";
                $conec->ejecutar($sql);
            }
        }                
    }
    
    function formulario_confirmar_eliminacion() {

        $mensaje = 'Esta seguro de eliminar el Comprobante?';

        $this->formulario->ventana_confirmacion($mensaje, $this->link . "?mod=$this->modulo", 'cmp_id');
    }
    
    public function imprimir_cmp($id_cmp) {
        
        $conec = new ADO();
        $conecdet = new ADO();

        $sql = "SELECT cmp.cmp_tco_id,cmp_id, tco_descripcion, mon_id, mon_titulo, mon_Simbolo, cmp_nro, cmp_nro_documento, cmp_fecha,cmp_tabla,
                ges_descripcion, pdo_descripcion, cmp_forma_pago,cmp_ban_id, cmp_ban_char, cmp_ban_nro, cmp_glosa, cmp_referido, cmp_revisado, 
                cmp_aprobado, concat(int_nombre,' ',int_apellido) as cmp_elaborado, cmp_tabla,cmp_ges_id, cmp_une_id
                FROM con_comprobante cmp, con_periodo p, con_gestion g, con_moneda m, con_tipo_comprobante tc, interno i
                where cmp.cmp_usu_id=i.int_id and cmp.cmp_tco_id= tc.tco_id and cmp.cmp_mon_id=m.mon_id and cmp.cmp_ges_id=g.ges_id and cmp.cmp_peri_id=pdo_id and cmp_id=$id_cmp;";

//        echo $sql;
        $conec->ejecutar($sql);
        $objeto = $conec->get_objeto();
//        _PRINT::pre($objeto);
        $_mon = _moneda_cmp;
        if($_mon!=0){
            $mon_id=_moneda_cmp;
        }else{
            $mon_id=$objeto->mon_id;
        }
        ;//$objeto->mon_id;
//        $mon_id=$objeto->mon_id;
        $sqldet = "SELECT cde_mon_id, cde_secuencia, cde_can_id, cde_cco_id, cde_cfl_id, cde_cue_id, 
                        cue_descripcion, cue_codigo,cde_valor,cde_glosa,cde_int_id, cde_fpago, 
                        cde_fpago_descripcion,cde_fpago_ban_nombre,cde_fpago_ban_nro,
                        cde_fpago_descripcion,cde_une_id                
                FROM con_comprobante_detalle cd, con_cuenta c
                WHERE c.cue_id=cd.cde_cue_id and cd.cde_cmp_id=$id_cmp and cd.cde_mon_id=$mon_id
                order by cde_secuencia;";

        $conecdet->ejecutar($sqldet);

        $montoTotal = 0.0;
        for ($i = 0; $i < $conecdet->get_num_registros(); $i++) {
            $detalle = $conecdet->get_objeto();
            $valor = floatval($detalle->cde_valor);
            if ($valor >= 0) {
                $montoTotal = $montoTotal + $valor;
            }
            $conecdet->siguiente();
        }
        $montoTotal = round($montoTotal,2);
        $conecdet->reset();

        $pagina = "'contenido_reporte'";
        $page = "'about:blank'";
        $extpage = "'reportes'";
        $features = "'left=100,width=850,height=550,top=0,scrollbars=yes'";
        $extra1 = "'<html>
				<head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />				
			  </head>
			  <body>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
        $extra1.=" <a href=javascript:window.print();>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
        $extra2 = "'</center></body></html>'";

        if(!isset($_GET['info'])){
            echo '<table align=right border=0>
                <tr>
                    <td>
                        <a href="javascript:var c = window.open(' . $page . ',' . $extpage . ',' . $features . ');
                                      c.document.write(' . $extra1 . ');
                                      var dato = document.getElementById(' . $pagina . ').innerHTML;
                                      c.document.write(dato);
                                      c.document.write(' . $extra2 . '); c.document.close();">
                            <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                        </a>
                </td>' .
                '<td>
                <img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=con_comprobante&tarea=ACCEDER\';">
                </td></tr></table>';
        }else{
            $editar=""; 
            if($objeto->cmp_tabla==""){
                $editar='<a href="gestor.php?mod=con_comprobante&tarea=MODIFICAR&info=ok&id='.$_GET['id'].'">Editar</a>';
            }
            echo '<div id=imprimir>
                    <div id=status>
                        <p>'.
                            $editar
                            .'<a href=javascript:window.print();>Imprimir</a> 
                            <a href=javascript:self.close();>Cerrar</a></td>
                        </p>
                    </div>
                </div>';
        }
        ?>
        <br />
        <br />
        <br />
                <?php
                function nombremes($mes) {
                    setlocale(LC_TIME, 'spanish');
                    $nombre = strftime("%B", mktime(0, 0, 0, $mes, 1, 2000));
                    return $nombre;
                }
                ?>
        
         <?php
            if($objeto->cmp_tabla!=''){
                $activos_disp=  FUNCIONES::parametro('cuentas_act_disp', $objeto->cmp_ges_id);
//                echo $activos_disp;
                $a_activos=  explode(',', $activos_disp);
                $activos_ids=array();
                for ($i = 0; $i < count($a_activos); $i++) {
                    $_cuenta=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_codigo='$a_activos[$i]' and cue_ges_id='$objeto->cmp_ges_id'");
                    $activos_ids[]=$_cuenta->cue_id;
                }
                $forma_pago=array();
                for($i=0;$i<$conecdet->get_num_registros() ;$i++){
                    $_det=$conecdet->get_objeto();
                    if(in_array($_det->cde_cue_id, $activos_ids) && !in_array($_det->cde_fpago, $forma_pago)){
                        $forma_pago[]=$_det->cde_fpago;
                    }
                    $conecdet->siguiente();
                }
                $conecdet->reset();
            }
            
            $aunegocio = array();
            $unegocios=  FUNCIONES::objetos_bd_sql("select * from con_unidad_negocio where une_eliminado='no'");
            for($j;$j<$unegocios->get_num_registros();$j++){
                $un=$unegocios->get_objeto();
                $aunegocio[$un->une_id]=$un->une_nombre;
                $unegocios->siguiente();
            }
            
        ?>
        <div id="contenido_reporte" style="clear:both; ">
            <link href="css/recibos.css" rel="stylesheet" type="text/css" />
            <style>
                .tab_detalle{
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 12px;
                }
                .tab_detalle thead, .tab_detalle tbody, .tab_detalle tfoot{
                    border:#000 1px solid;
                }
                table.tab_detalle th{
                    text-align: left;
                    /*background-color: #00aaf8;*/
                    border-right:#000 1px solid;
                }
                table.tab_detalle  td{
                    text-align: left;
                    /*background-color: #00aaf8;*/
                    border-right: #000 1px solid;
                }
                .nombre_cuenta_box{
                    width: 48%;
                    float: left;
                }
                .tab_detalle td, .tab_detalle th{
                    padding: 4px 5px;
                }
                .firmas{
                    border-collapse: collapse;
                    border: 1px solid #000;
                    width: 100%;
                    font-size: 12px;
                }
                .firmas td{
                    text-align: center;
                    border: 1px solid #000;
                    padding: 3px;
                }
            </style>
           
            
            <?php 
            $i=0;
            $nro_pag=1;
            $cant_fil=  FUNCIONES::atributo_bd("con_configuracion", "conf_ges_id='$_SESSION[ges_id]' and conf_nombre='cant_fil'", "conf_valor")*1;
            $tdebe=0;
            $thaber =0;
            
            $_cant_fil=0;
            
            while($i<$conecdet->get_num_registros() || $nro_pag==1){
                
            ?>
            <div class="recibo">
                <div class="reciboTop">
                    <img class="reciboLogo" src="imagenes/micro.png" width="120" height="50"alt="">
                    <div class="reciboTi">
                        <br>
                        <div class="reciboText">Comprobante de <?php echo $objeto->tco_descripcion; ?></div>                        
                        <div style="font-size: 16px;"><?php echo FUNCIONES::atributo_bd('con_unidad_negocio', "une_id='$objeto->cmp_une_id'", 'une_nombre');?></div>                        
                    </div>                    
                    <div class="reciboMoney" style="font-size: 14px; width: 120px;">
                        <div style="border: 1px solid #000; padding: 2px">
                            <div style="float: left">
                                <b>Nro.</b> <span><?php echo $objeto->cmp_nro; ?></span>
                            </div>
                            <div style="float: right">
                                <b>Pag.</b> <span><?php echo $nro_pag; ?></span>
                            </div>
                            <div style="clear: both"></div>
                        </div>
                        <div style="border: 1px solid #000; position: relative; top: -1px;padding: 2px">
                            <b><?php echo $objeto->mon_Simbolo; ?>.</b> <span><?php echo number_format(($montoTotal), 2, '.', ','); ?></span>
                        </div>
                    </div>   
                    <div style="clear: both"></div>                    
                </div>
                <?php if($nro_pag==1){?>
                <div style="font-size: 12px; text-align: left;  margin-bottom: 10px;">
                    <?php
                    $fecha = $objeto->cmp_fecha;
                    $fecha_array = explode('-', $fecha);
                    ?>
                    <span class="reciboLabels">SANTA &nbsp; CRUZ, &nbsp; <?php echo $fecha_array[2]; ?> &nbsp; de &nbsp;<?php echo (nombremes($fecha_array[1])); ?> &nbsp; del &nbsp; <?php echo $fecha_array[0] ?></span>                     
                </div>
                <?php }?>
                <div class="reciboCont">
                    <?php if($nro_pag==1){?>
                    <table class="tRecibo" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <?php
                        if ($objeto->tco_descripcion == "Ingreso" || $objeto->tco_descripcion == "Egreso") {
                            ?>                        
                            <tr>
                                <td class="tReciboLinea" colspan="4">                                
                                    <?php
                                    if ($objeto->tco_descripcion == "Ingreso") {
                                        ?>      
                                        <span class="reciboLabels">Recibido de:</span>
                                        <?php
                                    } else if ($objeto->tco_descripcion == "Egreso") {
                                        ?>
                                        <span class="reciboLabels">Pagado a:</span>
                                        <?php
                                    }
                                    ?>
                                    <span class="reciboTexts"> <?php echo $objeto->cmp_referido; ?></span>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Importe:</span>
                                <span class="reciboTexts">
                                    <?php
                                    $int_montoTotal = intval($montoTotal);
                                    if ($int_montoTotal == $montoTotal) {
                                        echo strtoupper($this->num2letras($montoTotal)) . '&nbsp;&nbsp;00/100';
                                    } else {
                                        $val = explode('.', $montoTotal);
                                        echo strtoupper($this->num2letras($val[0]));
                                        if (strlen($val[1]) == 1)
                                            echo '&nbsp;&nbsp;' . $val[1] . '0/100';
                                        else
                                            echo '&nbsp;&nbsp;' . $val[1] . '/100';
                                    }
                                    ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </span>
                                <span class="reciboLabels">
                                    <?php
                                    echo FUNCIONES::atributo_bd_sql("select mon_titulo as campo from con_moneda where mon_id='$mon_id'");//$objeto->mon_titulo;
                                    ?>
                                </span> 
                            </td>
                        </tr>
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Por concepto de:</span> 
                                <span class="reciboTexts"> <?php echo $objeto->cmp_glosa; ?></span>
                            </td>
                        </tr>
                        
                        <?php if($objeto->cmp_tco_id!=4 && $objeto->cmp_tco_id!=3 && $objeto->cmp_tabla==''){?>                        
                        <tr>
                            <td class="tReciboLinea" colspan="4">
                                <span class="reciboLabels">Forma de Pago:</span> 
                                <span class="reciboTexts"> <?php echo $objeto->cmp_forma_pago; ?></span>
                                <?php
                                if($objeto->cmp_forma_pago=="Cheque"){
                                    
                                    if($objeto->tco_descripcion=="Ingreso"){
                                        echo '<span class="reciboLabels">Banco:</span>';
                                        echo '<span class="reciboTexts">'.$objeto->cmp_ban_char.':</span>';
                                    }elseif($objeto->tco_descripcion=="Egreso"){
                                        echo '<span class="reciboLabels">Banco:</span>';
                                        echo '<span class="reciboTexts">'.FUNCIONES::atributo_bd('con_banco', 'ban_id='.$objeto->cmp_ban_id, 'ban_nombre').':</span>';                                        
                                    }
                                    echo '<span class="reciboLabels">Nro. de Cheque:</span>';
                                    echo '<span class="reciboTexts">'.$objeto->cmp_ban_nro.':</span>';
                                    
                                }elseif($objeto->cmp_forma_pago=="Deposito"){
                                    if($objeto->tco_descripcion=="Ingreso"){
                                        echo '<span class="reciboLabels">Banco:</span>';
                                        echo '<span class="reciboTexts">'.FUNCIONES::atributo_bd('con_banco', 'ban_id='.$objeto->cmp_ban_id, 'ban_nombre').':</span>';
                                    }elseif($objeto->tco_descripcion=="Egreso"){
                                        echo '<span class="reciboLabels">Banco:</span>';
                                        echo '<span class="reciboTexts">'.$objeto->cmp_ban_char.':</span>';                                        
                                    }
                                    echo '<span class="reciboLabels">Nro. de Deposito:</span>';
                                    echo '<span class="reciboTexts">'.$objeto->cmp_ban_nro.':</span>';
                                    
                                }elseif($objeto->cmp_forma_pago=="Transferencia"){
                                    if($objeto->tco_descripcion=="Ingreso"){
                                        echo '<span class="reciboLabels">Banco Destino:</span>';
                                        echo '<span class="reciboTexts">'.FUNCIONES::atributo_bd('con_banco', 'ban_id='.$objeto->cmp_ban_id, 'ban_nombre').':</span>';
                                        echo '<span class="reciboLabels">Banco Origen:</span>';
                                        echo '<span class="reciboTexts">'.$objeto->cmp_ban_char.':</span>';
                                    }elseif($objeto->tco_descripcion=="Egreso"){
                                        echo '<span class="reciboLabels">Banco Origen:</span>';
                                        echo '<span class="reciboTexts">'.FUNCIONES::atributo_bd('con_banco', 'ban_id='.$objeto->cmp_ban_id, 'ban_nombre').':</span>';
                                        echo '<span class="reciboLabels">Banco Destino:</span>';
                                        echo '<span class="reciboTexts">'.$objeto->cmp_ban_char.':</span>';                                        
                                    }
                                    echo '<span class="reciboLabels">Nro. de Trans.:</span>';
                                    echo '<span class="reciboTexts">'.$objeto->cmp_ban_nro.':</span>';
                                }
                                ?>                                
                            </td>
                        </tr>
                        <?php } elseif($objeto->cmp_tabla && $forma_pago && count ($forma_pago)>0){ ?> 
                            <tr>
                                <td class="tReciboLinea" colspan="4">
                                    <span class="reciboLabels">Forma de Pago: </span> 
                                    <span class="reciboTexts"> 
                                        <?php
                                            echo implode(',', $forma_pago);
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php }?>
                    </table><br>
                    <?php } ?>

                    <table class="tab_detalle">
                        <thead >
                            <tr >
                                <th width="15%">C&Oacute;DIGO</th>
                                <th width="55%">CUENTA</th>
                                <th width="15%">DEBE</th>
                                <th width="15%">HABER</th>                                                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php                            
//                            $tdebe=0;
//                            $thaber =0;  
//                            echo $i;
                            $sw=true;
//                            echo $nro_pag.'---<br>';
                            if($nro_pag==1){                                
                                $sum_fil=$cant_fil-3;
//                                echo "$sum_fil***<br>";
                            }elseif($nro_pag==$conecdet->get_num_registros()){
                                $sum_fil=$cant_fil-2;
                            }else{
                                $sum_fil=$cant_fil;
                            }
                            $_cant_fil=$_cant_fil+$sum_fil;
//                            echo "$_cant_fil<br>";
                            while($sw && $i<$conecdet->get_num_registros()){
                                $detalle = $conecdet->get_objeto()
                                ?>
                                <tr >
                                    <td><?php echo $detalle->cue_codigo; ?></td>
                                    <?php
                                    $valor = floatval($detalle->cde_valor);
                                    if($valor>=0){
                                        $tdebe+=$valor;
                                    }else{
                                        $thaber+=($valor*-1);
                                    }                                    
                                    ?>
                                    <td>
                                        <?php
                                        $espaciado="";
                                        if ($valor < 0){
                                            $espaciado='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                        }
                                        ?>
                                        <?php echo $espaciado;?><span style="text-decoration: underline"><?php echo $detalle->cue_descripcion;?>
                                            <?php if($detalle->cde_une_id){?><em ><?php echo "({$aunegocio[$detalle->cde_une_id]})";?></em> <?php }?>
                                        </span>
                                        
                                        <?php if($detalle->cde_can_id>0){echo '<br>'.$espaciado;?>
                                            <span style="font-size: 12px;font-weight: bold;">
                                            <?php $_ca=  FUNCIONES::objeto_bd_sql("select * from con_cuenta_ca where can_id=$detalle->cde_can_id");?>
                                            <?php echo $_ca->can_descripcion; echo $_ca->can_codigo=='01.00001'&&$detalle->cde_int_id?" - ".FUNCIONES::atributo_bd_sql("select concat(int_nombre,' ',int_apellido) as campo from interno where int_id='$detalle->cde_int_id'"):''; ?> </span>
                                        <?php }?>
                                        
                                        <?php if($detalle->cde_glosa!=""){echo '<br>'.$espaciado;?><span style="font-size: 12px; "><?php echo $detalle->cde_glosa;?> </span><?php }?>
                                        <?php if($detalle->cde_fpago!="Efectivo"){?>
                                            | <em style="font-size: 12px; ">
                                                <?php echo $detalle->cde_fpago;?> 
                                                <?php echo $detalle->cde_fpago_ban_nombre?", $detalle->cde_fpago_ban_nombre":"";?> 
                                                <?php echo $detalle->cde_fpago_ban_nro?", $detalle->cde_fpago_ban_nro":"";?> 
                                                <?php echo $detalle->cde_fpago_descripcion?", $detalle->cde_fpago_descripcion":"";?>
                                            </em>
                                        <?php }?>
                                    </td>
                                    <td><?php
                                        if ($valor >= 0)
                                            echo number_format(($valor), 2, '.', ',');
                                        else
                                            echo '&nbsp;';
                                        ?>
                                    </td>
                                    <td><?php
                                        if ($valor < 0)
                                            echo number_format(($valor*-1), 2, '.', ',');
                                        else
                                            echo '&nbsp;';
                                        ?>
                                    </td>                                    
                                </tr>
                                <?php
                                
                                if($i>=$_cant_fil-1){/// para que entre en varias hojas repitiendo cabecera..
//                                if(($i+1)%$cant_fil==0){/// para que entre en varias hojas repitiendo cabecera..
                                    $sw=false;
                                }
                                $conecdet->siguiente();
                                $i++;
                            }
//                            $i++
                                ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"><b>TOTAL</b></td>                                
                                <td><b><?php echo number_format(($tdebe), 2, '.', ','); ?></b></td>
                                <td><b><?php echo number_format(($thaber), 2, '.', ','); ?></b></td>                                
                            </tr>                            
                        </tfoot>
                    </table>                    
                    <br>
                    <?php
                    $beneficiado = $objeto->tco_descripcion == "Egreso";
//                    echo "$i==".$conecdet->get_num_registros();
                    ?>
                    <?php if($i==$conecdet->get_num_registros()){?>
                    
                    <table class="firmas">                        
                        <tr>
                            <td>&nbsp;<br>&nbsp;<br>&nbsp;</td>
                            <td>&nbsp;<br></td>
                            <td>&nbsp;<br></td>
                            <?php if ($beneficiado) { ?>
                                <td>&nbsp;<br></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td><b>Elaborado</b></td>
                            <td><b>Revisado</b></td>
                            <td><b>Aprobado</b></td>
                            <?php if ($beneficiado) { ?>
                                <td><b>Beneficiado</b></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td><em><?php echo $objeto->cmp_elaborado ?></em></td>
                            <td><em><?php echo $objeto->cmp_revisado ?></em></td>
                            <td><em><?php echo $objeto->cmp_aprobado ?></em></td>
                            <?php if ($beneficiado) { ?>
                                <td><?php echo $objeto->cmp_referido ?></td>
                            <?php } ?>
                        </tr>
                    </table>
                    <?php } ?>
                </div>                
            </div>
            <?php
            if($i>=$conecdet->get_num_registros()){
            ?>
            <b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))) . ' ' . date('H:i'); ?>
            <?php
            }
            ?>
            <div style="page-break-after: always"></div>
            <?php
                $nro_pag++; 
            }
            ?>            
            <!-- recibo -->

        </div>
        <?php
    }

    public static function num2letras($num, $fem = false, $dec = true) {
        $matuni[2] = "dos";
        $matuni[3] = "tres";
        $matuni[4] = "cuatro";
        $matuni[5] = "cinco";
        $matuni[6] = "seis";
        $matuni[7] = "siete";
        $matuni[8] = "ocho";
        $matuni[9] = "nueve";
        $matuni[10] = "diez";
        $matuni[11] = "once";
        $matuni[12] = "doce";
        $matuni[13] = "trece";
        $matuni[14] = "catorce";
        $matuni[15] = "quince";
        $matuni[16] = "dieciseis";
        $matuni[17] = "diecisiete";
        $matuni[18] = "dieciocho";
        $matuni[19] = "diecinueve";
        $matuni[20] = "veinte";
        $matunisub[2] = "dos";
        $matunisub[3] = "tres";
        $matunisub[4] = "cuatro";
        $matunisub[5] = "quin";
        $matunisub[6] = "seis";
        $matunisub[7] = "sete";
        $matunisub[8] = "ocho";
        $matunisub[9] = "nove";

        $matdec[2] = "veint";
        $matdec[3] = "treinta";
        $matdec[4] = "cuarenta";
        $matdec[5] = "cincuenta";
        $matdec[6] = "sesenta";
        $matdec[7] = "setenta";
        $matdec[8] = "ochenta";
        $matdec[9] = "noventa";
        $matsub[3] = 'mill';
        $matsub[5] = 'bill';
        $matsub[7] = 'mill';
        $matsub[9] = 'trill';
        $matsub[11] = 'mill';
        $matsub[13] = 'bill';
        $matsub[15] = 'mill';
        $matmil[4] = 'millones';
        $matmil[6] = 'billones';
        $matmil[7] = 'de billones';
        $matmil[8] = 'millones de billones';
        $matmil[10] = 'trillones';
        $matmil[11] = 'de trillones';
        $matmil[12] = 'millones de trillones';
        $matmil[13] = 'de trillones';
        $matmil[14] = 'billones de trillones';
        $matmil[15] = 'de billones de trillones';
        $matmil[16] = 'millones de billones de trillones';
        
        $num = trim((string) @$num);
        if ($num[0] == '-') {
            $neg = 'menos ';
            $num = substr($num, 1);
        }
        else
            $neg = '';
        while ($num[0] == '0')
            $num = substr($num, 1);
        if ($num[0] < '1' or $num[0] > 9)
            $num = '0' . $num;
        $zeros = true;
        $punt = false;
        $ent = '';
        $fra = '';
        for ($c = 0; $c < strlen($num); $c++) {
            $n = $num[$c];
            if (!(strpos(".,'''", $n) === false)) {
                if ($punt)
                    break;
                else {
                    $punt = true;
                    continue;
                }
            } elseif (!(strpos('0123456789', $n) === false)) {
                if ($punt) {
                    if ($n != '0')
                        $zeros = false;
                    $fra .= $n;
                }
                else
                    $ent .= $n;
            }
            else
                break;
        }
        $ent = '     ' . $ent;
        if ($dec and $fra and !$zeros) {
            $fin = ' coma';
            for ($n = 0; $n < strlen($fra); $n++) {
                if (($s = $fra[$n]) == '0')
                    $fin .= ' cero';
                elseif ($s == '1')
                    $fin .= $fem ? ' una' : ' un';
                else
                    $fin .= ' ' . $matuni[$s];
            }
        }
        else
            $fin = '';
        if ((int) $ent === 0)
            return 'Cero ' . $fin;
        $tex = '';
        $sub = 0;
        $mils = 0;
        $neutro = false;
        while (($num = substr($ent, -3)) != '   ') {
            $ent = substr($ent, 0, -3);
            if (++$sub < 3 and $fem) {
                $matuni[1] = 'una';
                $subcent = 'as';
            } else {
                $matuni[1] = $neutro ? 'un' : 'uno';
                $subcent = 'os';
            }
            $t = '';
            $n2 = substr($num, 1);
            if ($n2 == '00') {
                
            } elseif ($n2 < 21)
                $t = ' ' . $matuni[(int) $n2];
            elseif ($n2 < 30) {
                $n3 = $num[2];
                if ($n3 != 0)
                    $t = 'i' . $matuni[$n3];
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            }else {
                $n3 = $num[2];
                if ($n3 != 0)
                    $t = ' y ' . $matuni[$n3];
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            }
            $n = $num[0];
            if ($n == 1) {
                $t = ' ciento' . $t;
            } elseif ($n == 5) {
                $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
            } elseif ($n != 0) {
                $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
            }
            if ($sub == 1) {
                
            } elseif (!isset($matsub[$sub])) {
                if ($num == 1) {
                    $t = ' mil';
                } elseif ($num > 1) {
                    $t .= ' mil';
                }
            } elseif ($num == 1) {
                $t .= ' ' . $matsub[$sub] . '?n';
            } elseif ($num > 1) {
                $t .= ' ' . $matsub[$sub] . 'ones';
            }
            if ($num == '000')
                $mils++;
            elseif ($mils != 0) {
                if (isset($matmil[$sub]))
                    $t .= ' ' . $matmil[$sub];
                $mils = 0;
            }
            $neutro = true;
            $tex = $t . $tex;
        }
        $tex = $neg . substr($tex, 1) . $fin;
        return ucfirst($tex);
    }
}
?>