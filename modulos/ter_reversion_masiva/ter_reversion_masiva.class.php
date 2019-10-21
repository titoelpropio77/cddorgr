<?php

class ter_reversion_masiva extends BUSQUEDA {

    var $formulario;
    var $mensaje;
    var $usu;

    function ter_reversion_masiva() {  //permisos
        $this->ele_id = 192;
        $this->busqueda();
        if (!($this->verificar_permisos('AGREGAR'))) {
            $this->ban_agregar = false;
        }
        //fin permisos

        $this->num_registros = 14;

        $this->coneccion = new ADO();

        $this->arreglo_campos[0]["nombre"] = "ban_nombre";
        $this->arreglo_campos[0]["texto"] = "Nombre";
        $this->arreglo_campos[0]["tipo"] = "cadena";
        $this->arreglo_campos[0]["tamanio"] = 25;

        $this->arreglo_campos[1]["nombre"] = "ban_descripcion";
        $this->arreglo_campos[1]["texto"] = "Descripcion";
        $this->arreglo_campos[1]["tipo"] = "cadena";
        $this->arreglo_campos[1]["tamanio"] = 25;

        $this->link = 'gestor.php';

        $this->modulo = 'ter_reversion_masiva';

        $this->formulario = new FORMULARIO();

        $this->formulario->set_titulo('REVERSION MASIVA DE VENTAS');

        $this->usu = new USUARIO;
    }

    function dibujar_busqueda() {
        $this->formulario->dibujar_cabecera();

        $this->dibujar_listado();
    }

    function set_opciones() {
        $nun = 0;

        if ($this->verificar_permisos('VER')) {
            $this->arreglo_opciones[$nun]["tarea"] = 'VER';
            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_search.png';
            $this->arreglo_opciones[$nun]["nombre"] = 'VER';
            $nun++;
        }

//        if ($this->verificar_permisos('MODIFICAR')) {
//            $this->arreglo_opciones[$nun]["tarea"] = 'MODIFICAR';
//            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_edit.png';
//            $this->arreglo_opciones[$nun]["nombre"] = 'MODIFICAR';
//            $nun++;
//        }

//        if ($this->verificar_permisos('ELIMINAR')) {
//            $this->arreglo_opciones[$nun]["tarea"] = 'ELIMINAR';
//            $this->arreglo_opciones[$nun]["imagen"] = 'images/b_drop.png';
//            $this->arreglo_opciones[$nun]["nombre"] = 'ELIMINAR';
//            $nun++;
//        }
    }

    function dibujar_listado() {
        $sql = "select * from ter_reversion_masiva ";
        $this->set_sql($sql, 'order by vrm_id desc');

        $this->set_opciones();

        $this->dibujar();
    }

    function dibujar_encabezado() {
        ?>
        <tr>
            <th>Id</th>		
            <th>Urbanizaciones</th>		
            <th>Fecha</th>		
            <th>Sucursal</th>		
            <th>Condicion</th>		
            <th>Nro. Cuotas</th>		
            <th>Nota</th>		
            <th>Usuario</th>
            <th class="tOpciones" width="100px">Opciones</th>
        </tr>
        <?PHP
    }

    function mostrar_busqueda() {
        
        for ($i = 0; $i < $this->numero; $i++) {
            $objeto = $this->coneccion->get_objeto();
            $str_urbs="";
            $urbanizaciones=  FUNCIONES::lista_bd_sql("select urb_id,urb_nombre_corto from urbanizacion where urb_id in ($objeto->vrm_urb_ids)");
            $j=0;
            foreach ($urbanizaciones as $urb) {
                if($j>0){
                    $str_urbs.=", ";
                }
                $str_urbs.=$urb->urb_nombre_corto;
            }
            echo '<tr>';
            echo "<td>";
            echo $objeto->vrm_id;
            echo "</td>";
            echo "<td>";
            echo "$str_urbs";
            echo "</td>";
            echo "<td>";
            echo FUNCIONES::get_fecha_latina($objeto->vrm_fecha);
            echo "</td>";
            echo "<td>";
            if($objeto->vrm_suc_id>0){
                echo FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal where suc_id='$objeto->vrm_suc_id'");
            }else{
                echo "TODOS";
            }
            echo "</td>";
            echo "<td>";
            echo $objeto->vrm_condicion;
            echo "</td>";
            echo "<td>";
            echo $objeto->vrm_num_cuotas;
            echo "</td>";
            echo "<td>";
            echo $objeto->vrm_nota;
            echo "</td>";
            echo "<td>";
            echo $objeto->vrm_usu_cre;
            echo "</td>";
            echo "<td>";
            echo $this->get_opciones($objeto->vrm_id);
            echo "</td>";
            echo "</tr>";

            $this->coneccion->siguiente();
        }
    }

    function cargar_datos() {
        $conec = new ADO();

        $sql = "select * from ter_reversion_masiva 
				where suc_id = '" . $_GET['id'] . "'";

        $conec->ejecutar($sql);

        $objeto = $conec->get_objeto();

        $_POST['suc_id'] = $objeto->suc_id;

        $_POST['suc_nombre'] = $objeto->suc_nombre;
        $_POST['suc_descripcion'] = $objeto->suc_descripcion;
    }

    function datos() {
        if ($_POST) {
            //texto,  numero,  real,  fecha,  mail.
            $num = 0;
            $valores[$num]["etiqueta"] = "Nombre";
            $valores[$num]["valor"] = $_POST['suc_nombre'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;
            $valores[$num]["etiqueta"] = "Descripcion";
            $valores[$num]["valor"] = $_POST['suc_descripcion'];
            $valores[$num]["tipo"] = "todo";
            $valores[$num]["requerido"] = true;
            $num++;

            $val = NEW VALIDADOR;

            $this->mensaje = "";

            if ($val->validar($valores)) {
                return true;
            } else {
                $this->mensaje = $val->mensaje;
                return false;
            }
        }
        return false;
    }

    function agregar_reversion_masiva() {
        if($_POST[parametros]=='ok'){
            if($_POST[guardar]=='ok'){
                $this->guardar_reversion_masiva();
            }else{
                $this->frm_verificar_reversion();
            }
        }else{
            $this->frm_parametros_reversion();
        }
    }

    function guardar_reversion_masiva() {
        include_once 'clases/modelo_comprobantes.class.php';
        include_once 'clases/registrar_comprobantes.class.php';
        $conec=new ADO();
        
        $str_urb_ids="";
        if(count($_POST[urb_ids])>0){
            $str_urb_ids= implode(',', $_POST[urb_ids]);
        }else{
            $str_urb_ids=  FUNCIONES::atributo_bd_sql("select group_concat(distinct urb_id) as campo from urbanizacion");
        }
        $fecha=  FUNCIONES::get_fecha_mysql($_POST[fecha]);
        $suc_id=$_POST[ven_suc_id];
        
        $acondicion=array(
            '='=>'Igual',
            '>='=>'Mayor Igual',
            '<='=>'Menor Igual',
            '>'=>'Mayor',
            '<'=>'Menor',
            
        );
        $op_rel=$_POST[op_rel];
        $condicion=$acondicion[$op_rel];
        $nro_cuotas=$_POST[nro_cuotas];
        $fecha_cre=date('Y-m-d H:i:s');
        $nota=$_POST[nota];
        $sql_insert="insert into ter_reversion_masiva(
                        vrm_urb_ids,vrm_fecha,vrm_suc_id,vrm_condicion,vrm_num_cuotas,vrm_nota,vrm_usu_cre,vrm_fecha_cre
                    )values(
                        '$str_urb_ids','$fecha','$suc_id','$condicion','$nro_cuotas','$nota','$_SESSION[id]','$fecha_cre'
                    )";

        $conec->ejecutar($sql_insert,true,true);
//        echo "$sql_insert;<br>";
        $llave=  ADO::$insert_id;
        $ven_ids=  implode(',', $_POST[ven_ids]);
        $listado=$this->obtener_listado_mora($ven_ids);

        $num=count($listado);
        for ($i = 0; $i < $num; $i++) {
            $objeto=$listado[$i];
            
            $ufecha_pago=  FUNCIONES::get_fecha_mysql($objeto->ufecha_pago);
            $sql_ins_det="insert into ter_reversion_masiva_detalle(
                            dvrm_vrm_id,dvrm_ven_id,dvrm_lote,dvrm_cuotas,dvrm_ufecha,
                            dvrm_dias_interes,dvrm_cartera,dvrm_capital,dvrm_interes,dvrm_form,
                            dvrm_monto,dvrm_int_consumido,dvrm_capital_pagado,dvrm_aportado
                        )values(
                            '$llave','$objeto->ven_id','$objeto->lote','$objeto->cuotas_mora','$ufecha_pago',
                            '$objeto->dias_interes','$objeto->ecartera','$objeto->capital','$objeto->interes','$objeto->form',
                            '$objeto->monto','$objeto->int_consumido','$objeto->capital_pagado','$objeto->aportado'
                        )";
            $conec->ejecutar($sql_ins_det);
            $this->guardar_retencion($objeto,$fecha);
            if($i==2){
                break;
            }
        }
        $this->mostrar_listado_reversion($llave);
    }
    
    function guardar_retencion($objeto,$fecha){
//        FUNCIONES::print_pre($_POST);
//            return;
        $conec= new ADO();
        $venta=$objeto->venta;
        $lote_id=$venta->ven_lot_id;
//        echo "$venta->ven_estado $venta->ven_bloqueado <br>";
        if($venta->ven_estado == 'Pendiente' && !$venta->ven_bloqueado){
//            echo "REVERTIR TERRENO $venta->ven_id<BR>";
                $fecha_cmp= $fecha;
                $sql="update venta set ven_estado='Retenido' where ven_id = '$venta->ven_id'";
                $conec->ejecutar($sql);

                $sql="update lote set lot_estado='Disponible' where lot_id = '$lote_id'";
                $conec->ejecutar($sql);

                $sql="update interno_deuda set  ind_estado='Retenido' where  ind_tabla = 'venta' and ind_tabla_id = '$venta->ven_id' and ind_estado = 'Pendiente' ";
                $conec->ejecutar($sql);
                
                $pagado=$objeto->pagado;//$this->total_pagado($venta->ven_id);
                $tot_pagado=$venta->ven_res_anticipo+$venta->ven_venta_pagado+$pagado->capital;
                $saldo =$venta->ven_monto_efectivo-$pagado->capital-$pagado->descuento+$pagado->incremento;
                
                $monto_intercambio=$venta->ven_monto_intercambio;
                
                $amontos=  FUNCIONES::lista_bd_sql("select vint_inter_id as inter_id, vint_monto as monto from venta_intercambio where vint_ven_id=$venta->ven_id order by inter_id asc");
                $amontos_pag=FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$venta->ven_id group by vipag_inter_id order by inter_id asc");

                
                $observacion=  trim($_POST[observacion]);
                $params=array(
                    'costo'=>$venta->ven_costo,
                    'costo_pagado'=>$venta->ven_costo_cub+$pagado->costo,
                    'saldo_efectivo'=>$saldo,
                    'total_pagado'=>$tot_pagado,
                    
                    'intercambio'=>$monto_intercambio,
                    'inter_montos'=>$amontos,
                    'inter_montos_pag'=>$amontos_pag,
                );
                
                $str_params=  json_encode($params);
                $fecha_cre=  date('Y-m-d H:i:s');
                $usu_cre=$_SESSION[id];
                $sql_insert="insert into venta_negocio(
                                vneg_tipo,vneg_ven_id,vneg_ven_ori,vneg_observacion,vneg_fecha,vneg_costo,vneg_moneda,vneg_parametros,vneg_estado,vneg_fecha_cre,vneg_usu_cre
                            )values(
                                'reversion','$venta->ven_id','0','$observacion','$fecha_cmp','0','$venta->ven_moneda','$str_params','Activado','$fecha_cre','$usu_cre'
                            )";
                $conec->ejecutar($sql_insert,true,true);
                $tarea_id = ADO::$insert_id;
                

                $referido=  FUNCIONES::interno_nombre($venta->ven_int_id);
                $glosa="Reversion de la Venta Nro. $venta->ven_id, $venta->ven_concepto";
                $urb=  FUNCIONES::objeto_bd_sql("select * from urbanizacion where urb_id='$venta->ven_urb_id'");

                
                $data=array(
                    'moneda'=>$venta->ven_moneda,
                    'ges_id'=>$_SESSION[ges_id],
                    'fecha'=>$fecha_cmp,
                    'glosa'=>$glosa,
                    'interno'=>$referido,
                    'tabla_id'=>$venta->ven_id,
                    'tarea_id'=>$tarea_id,
                    'urb'=>$urb,

                    'costo'=>$venta->ven_costo,
                    'costo_pagado'=>$venta->ven_costo_cub+$pagado->costo,
                    'saldo_efectivo'=>$saldo,
                    'total_pagado'=>$tot_pagado,
                    
                    'intercambio'=>$monto_intercambio,
                    'inter_montos'=>$amontos,
                    'inter_montos_pag'=>$amontos_pag,
                );

                $comprobante = MODELO_COMPROBANTE::venta_retencion($data);

                COMPROBANTES::registrar_comprobante($comprobante);

        }		

    }	
    
    function obtener_detalle_reversion_masiva($vrm_id){
        
        $conec=new ADO();
        $sql_sel="select venta.*,ter_reversion_masiva_detalle.*,urb_nombre_corto from ter_reversion_masiva_detalle, venta, urbanizacion where dvrm_vrm_id='$vrm_id' and urb_id=ven_urb_id and ven_id=dvrm_ven_id";
        
        $conec->ejecutar($sql_sel);
        

        $num = $conec->get_num_registros();

        $result=array();
        $nro=1;
        $total=new stdClass();
        for ($i = 0; $i < $num; $i++) {
            $obj=$conec->get_objeto();
            $ufecha_pago=  FUNCIONES::get_fecha_latina($obj->dvrm_ufecha);//FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago);
           
            $cliente=  FUNCIONES::interno_nombre($obj->ven_int_id);
            $str_moneda='Bolivianos';
            if($obj->ven_moneda=='2'){
                $str_moneda='Dolares';
            }
            
            
            $ndias=  $obj->dvrm_dias_interes;//FUNCIONES::diferencia_dias($obj->ven_ufecha_valor,$fecha_inicio);
            $interes=$obj->dvrm_interes;
            $form=$obj->dvrm_form;
            $capital=$obj->dvrm_capital;
            
            $monto=round($interes+$form+$capital,2);

            
            $mon=$obj->ven_moneda;
            $total->{"capital_$mon"}+=$capital;            
            $total->{"interes_$mon"}+=$interes;
            $total->{"form_$mon"}+=$form;
            $total->{"monto_$mon"}+=$monto;
            //            'Interes Pagar','Form. Pagar','Capital Pagar','Monto Pagar'

            $des_lotes=$obj->dvrm_lote;//"UV{$obj->uv_nombre}M{$obj->man_nro}L{$obj->lot_nro}";
            
            $est_cartera=$obj->dvrm_cartera;
            
            $sum_inter_pag=$obj->dvrm_int_consumido;
            $pagado=$this->total_pagado_venta($obj->ven_id);
            $aportado=$pagado->capital+$obj->ven_res_anticipo+$sum_inter_pag;
            $saldo_final=$obj->ven_monto_efectivo-$pagado->capital-$pagado->descuento+$pagado->incremento;
            $result[]=(object)array(
                'nro'=>$nro,
                'ven_id'=>$obj->ven_id,
                'cliente'=>$cliente,
                'proyecto'=>$obj->urb_nombre_corto,
                'moneda'=>$str_moneda,
                'lote'=>$des_lotes,
                'cuotas_mora'=>$obj->dvrm_cuotas,
                'capital'=>$capital,
                'ufecha_pago'=>$ufecha_pago,
                'dias_interes'=>$ndias,
                'ecartera'=>$est_cartera,
                'interes'=>$interes,
                'form'=>$form,
                'monto_total'=>$monto,
                'monto_venta'=>$obj->ven_monto,
                'anticipo'=>$obj->ven_res_anticipo,
                'intercambio'=>$obj->ven_monto_intercambio,
                'int_consumido'=>$sum_inter_pag,
                'saldo_financiar'=>$obj->ven_monto_efectivo,
                'capital_pagado'=>$pagado->capital,
                'saldo_final'=>$saldo_final,
                'aportado'=>$aportado,
                
                'mon_id'=>$obj->ven_moneda,
            );
            $nro++;
            $conec->siguiente();
        }
        return $result;

    }
    function obtener_listado_mora($ven_ids=""){
        $conec = new ADO();
        $info = array();
        $filtro = '';
        
        if($ven_ids){
            $filtro .= " and ven_id in ($ven_ids)";
        }
        if($_POST[urb_ids]){
            $filtro .= " and urb_id in (".  implode(',', $_POST[urb_ids]).")";
        }
        
        if ($_POST['fecha'] <> "") {
//            $filtro.=" and ven_fecha >= '" . FUNCIONES::get_fecha_mysql($_POST['inicio']) . "' ";
            $fecha_inicio=FUNCIONES::get_fecha_mysql($_POST['fecha']);
            $filtro.=" and ven_sfecha_prog <='$fecha_inicio' and ven_sfecha_prog!='0000-00-00'";
            $filtro.=" and ind_fecha_programada<='$fecha_inicio'";
        } 

        
        
        if ($_POST[ven_suc_id]){
            $filtro .= " and ven_suc_id='$_POST[ven_suc_id]'";
        }
        $having="";
        if($_POST[nro_cuotas]*1>0){
            $op_rel=$_POST[op_rel];
            $having = "  having(count(*){$op_rel}{$_POST[nro_cuotas]})";
        }

        $filtro .= " and ven_estado='Pendiente' and ven_tipo='Credito'";
        
        $sql = "SELECT 
                venta.*,urb_id,int_nombre,int_apellido,man_nro,lot_nro,
                    cast(man_nro as SIGNED) as manzano ,cast(lot_nro as SIGNED) as lote,
                    suc_nombre,uv_nombre,urb_nombre_corto,
                    int_telefono,int_celular,int_direccion,urb_val_form,
                    sum(ind_capital-ind_capital_pagado) as capital_pendiente,count(*) as cuotas_pendientes
                FROM 
                venta
                inner join ter_sucursal on (ven_suc_id=suc_id)
                inner join interno on (ven_int_id=int_id)
                inner join lote on (ven_lot_id=lot_id)
                inner join manzano on (lot_man_id=man_id)
                inner join urbanizacion on (man_urb_id=urb_id)
                inner join uv on (lot_uv_id=uv_id)
                inner join interno_deuda on (ind_tabla='venta' and ind_tabla_id=ven_id)
                where ven_estado='Pendiente' and ven_bloqueado='0' and ven_usaldo>0 $filtro
                and ind_estado='Pendiente' and ind_capital_pagado < ind_capital and ind_tipo='pcuota'
                group by ven_id
                $having
                order by ven_sfecha_prog asc";
//        echo "$sql;<br>";

        $conec->ejecutar($sql);
        

        $num = $conec->get_num_registros();

        $result=array();
        $nro=1;
        $total=new stdClass();
        for ($i = 0; $i < $num; $i++) {
            $obj=$conec->get_objeto();
            $ufecha_pago=  FUNCIONES::get_fecha_latina($obj->ven_ufecha_pago);//FUNCIONES::get_fecha_latina($upago->vpag_fecha_pago);
            
            $cliente="$obj->int_nombre $obj->int_apellido";
//            $vendedor="$comision->int_nombre $comision->int_apellido";
            
            $str_moneda='Bolivianos';
            if($obj->ven_moneda=='2'){
                $str_moneda='Dolares';
            }
            
            $interes_dia=(($obj->ven_val_interes/360)/100)*$obj->ven_usaldo;
            $ndias=  FUNCIONES::diferencia_dias($obj->ven_ufecha_valor,$fecha_inicio);
            $interes=round($interes_dia*$ndias,2);
            $form=$obj->urb_val_form;


            $capital=$obj->capital_pendiente;

            
            $monto=round($interes+$form+$capital,2);

            $saldo_financiar=$obj->ven_monto_efectivo;
            $mon=$obj->ven_moneda;
            $total->{"capital_$mon"}+=$capital;            
            $total->{"interes_$mon"}+=$interes;
            $total->{"form_$mon"}+=$form;
            $total->{"monto_$mon"}+=$monto;
            //            'Interes Pagar','Form. Pagar','Capital Pagar','Monto Pagar'
            // 
            $des_lotes="UV{$obj->uv_nombre}M{$obj->man_nro}L{$obj->lot_nro}";
            if($obj->ven_lot_ids){
                $alotes=  FUNCIONES::lista_bd_sql("select man_nro,lot_nro from uv,manzano,lote where lot_uv_id=uv_id and man_id=lot_man_id and lot_id in ($obj->ven_lot_ids)");
                $des_lotes="";
                $j=0;
                foreach ($alotes as $lot) {
                    if($j>0){
                        $des_lotes.=", ";
                    }
                    $des_lotes.="UV{$lot->uv_nombre}M{$lot->man_nro}L{$lot->lot_nro}";
                    $j++;
                }
            }
            if($obj->ven_usaldo>0){
                $est_cartera= FUNCIONES::estado_cartera($ndias);// 'Vigente';
            }else{
                $est_cartera='Pagado';
            }
            $amontos_pag=FUNCIONES::lista_bd_sql("select vipag_inter_id as inter_id, sum(vipag_monto) as monto from venta_intercambio_pago where vipag_estado='Activo' and vipag_ven_id=$obj->ven_id group by vipag_inter_id order by inter_id asc");
            $sum_inter_pag=0;
            foreach ($amontos_pag as $ipag) {
                $sum_inter_pag+=$ipag->monto;
            }
            $pagado=$this->total_pagado_venta($obj->ven_id);
            $aportado=$pagado->capital+$obj->ven_res_anticipo+$sum_inter_pag;
            $saldo_final=$obj->ven_monto_efectivo-$pagado->capital-$pagado->descuento+$pagado->incremento;
            $result[]=(object)array(
                'nro'=>$nro,
                'ven_id'=>$obj->ven_id,
                'cliente'=>$cliente,
                'proyecto'=>$obj->urb_nombre_corto,
                'moneda'=>$str_moneda,
                'lote'=>$des_lotes,
                'cuotas_mora'=>$obj->cuotas_pendientes,
                'capital'=>$capital,
                'ufecha_pago'=>$ufecha_pago,
                'dias_interes'=>$ndias,
                'ecartera'=>$est_cartera,
                'interes'=>$interes,
                'form'=>$form,
                'monto_total'=>$monto,
                'monto_venta'=>$obj->ven_monto,
                'anticipo'=>$obj->ven_res_anticipo,
                'intercambio'=>$obj->ven_monto_intercambio,
                'int_consumido'=>$sum_inter_pag,
                'saldo_financiar'=>$obj->ven_monto_efectivo,
                'capital_pagado'=>$pagado->capital,
                'saldo_final'=>$saldo_final,
                'aportado'=>$aportado,
                
                'pagado'=>$pagado,
                'venta'=>clone $obj,
                'mon_id'=>$obj->ven_moneda,
            );
            $nro++;
            $conec->siguiente();
        }
        return $result;

    }
    
    function frm_verificar_reversion() {
        $url = $this->link . '?mod=' . $this->modulo."&tarea=" . $_GET['tarea'];
        $this->formulario->dibujar_titulo('VERIFICAR LAS VENTAS PARA REVERSION MASIVA');
        ?>
        <script src="js/jquery.thfloat-0.7.2.min.js"></script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent" style="width:100%;">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <input type="hidden" name="parametros" value="ok">
                        <input type="hidden" name="guardar" value="ok">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Urbanizaciones</div>
                            <div id="CajaInput">
                                <?
                                $str_urbs="TODOS";
                                if(count($_POST[urb_ids])>0){
                                    $str_urbs=  implode(', ', $_POST[urb_nombres]);
                                }
                                ?>
                                <div class="read-input"><?php echo $str_urbs;?></div>
                            </div>		
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha</div>
                            <div id="CajaInput">
                                <div class="read-input"><?php echo $_POST[fecha];?></div>
                            </div>		
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Sucursal</div>
                            <div id="CajaInput">
                                <?php
                                $str_suc="TODOS";
                                if($_POST[ven_suc_id]){
                                    $str_suc=  FUNCIONES::atributo_bd_sql("select suc_nombre as campo where suc_id='$_POST[ven_suc_id]'");
                                }
                                ?>
                                <div class="read-input"><?php echo $str_suc;?></div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nro de Cuotas</div>
                            <div id="CajaInput">
                                <?php
                                $str_op_rel=$_POST[op_rel]?$_POST[op_rel]:"=";
                                $str_nro_cuotas=$_POST[nro_cuotas]?$_POST[nro_cuotas]:"0";
                                ?>
                                <div class="read-input"><?php echo "$str_op_rel $str_nro_cuotas";?></div>
                            </div>
                        </div>
                        <table class="tablaLista" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nro Venta</th>
                                    <th>Cliente</th>
                                    <th>Proyecto</th>
                                    <th>Moneda</th>
                                    <th>Lote</th>
                                    <th>Cuotas Mora</th>
                                    <th>U. Fecha Pago</th>
                                    <th>Dias Interes</th>
                                    <th>Est. Cartera</th>
                                    <th>Capitales Mora</th>
                                    <th>Interes</th>
                                    <th>Formulario</th>
                                    <th>Total Deuda</th>
                                    <th>Monto Venta</th>
                                    <th class="topciones">Anticipo</th>
                                    <th>Intercambio</th>
                                    <th class="topciones">Int. Consumido</th>
                                    <th>Saldo Financiar</th>
                                    <th class="topciones">Capital Pagado</th>
                                    <th>Saldo Capital</th>
                                    <th class="topciones">Total Aportado</th>
                                    <th class="topciones">Revertir</th>
                                    <th >&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $aestados=array('Vigente'=>'#00A015','Vencido'=>'#C6C600','Mora'=>'#D87E00','Ejecucion'=>'#FF0000');
                                $listado=$this->obtener_listado_mora();
                                $total=new stdClass();
                                ?>

                                <?php foreach ($listado as $det) {?>
                                <tr>
                                    <td><?php echo $det->nro;?></td>
                                    <td>
                                        <input type="hidden" class="ven_ids" name="ven_ids[]" value="<?php echo $det->ven_id;?>">
                                        <?php echo $det->ven_id;?>
                                    </td>
                                    <td><?php echo $det->cliente;?></td>
                                    <td><?php echo $det->proyecto;?></td>
                                    <td><?php echo $det->moneda;?></td>
                                    <td><?php echo $det->lote;?></td>
                                    <td><?php echo $det->cuotas_mora;?></td>
                                    <td><?php echo $det->ufecha_pago;?></td>
                                    <td><?php echo $det->dias_interes;?></td>
                                    <td>
                                        <span style="padding: 2px 3px; color: #fff; background-color: <?php echo $aestados[$det->ecartera]?>">
                                        <?php echo $det->ecartera;?>
                                        </span>
                                    </td>

                                    <td><?php echo $det->capital;?></td>
                                    <td><?php echo $det->interes;?></td>
                                    <td><?php echo $det->form;?></td>
                                    <td><?php echo $det->monto_total;?></td>
                                    
                                    <td><?php echo $det->monto_venta;?></td>
                                    <td><?php echo $det->anticipo;?></td>
                                    <td><?php echo $det->intercambio;?></td>
                                    <td><?php echo $det->int_consumido;?></td>
                                    <td><?php echo $det->saldo_financiar;?></td>
                                    <td><?php echo $det->capital_pagado;?></td>
                                    <td><?php echo $det->saldo_final;?></td>
                                    <td><?php echo $det->aportado;?></td>
                                    <td>
                                        <img class="btn_aprove_venta" src="images/aprove.png" width="25" data-id="<?php echo $det->ven_id;?>">
                                        <img class="btn_cancel_venta" src="images/dis_select.png" width="25" data-id="<?php echo $det->ven_id;?>" style="display: none;">
                                    </td>
                                    <td>
                                        <img class="btn_ver_venta" src="images/b_browse.png" width="16" data-id="<?php echo $det->ven_id;?>">
                                        <img class="btn_ver_venta_nota" src="images/notas.png" width="16" data-id="<?php echo $det->ven_id;?>">
                                    </td>
                                </tr>
                                <?php
                                $mon=$det->mon_id;
                                $total->{"capital_$mon"}+=$det->capital;            
                                $total->{"interes_$mon"}+=$det->interes;
                                $total->{"form_$mon"}+=$det->form;
                                $total->{"monto_$mon"}+=$det->monto_total;
                                $total->{"monto_venta_$mon"}+=$det->monto_venta;
                                $total->{"anticipo_$mon"}+=$det->anticipo;
                                $total->{"intercambio_$mon"}+=$det->intercambio;
                                $total->{"int_consumido_$mon"}+=$det->int_consumido;
                                $total->{"saldo_financiar_$mon"}+=$det->saldo_financiar;
                                $total->{"capital_pagado_$mon"}+=$det->capital_pagado;
                                $total->{"saldo_final_$mon"}+=$det->saldo_final;
                                $total->{"aportado_$mon"}+=$det->aportado;
                                ?>
                            <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="10">Totales Bs.</td>
                                    <td ><?php echo $total->capital_1*1;?></td>
                                    <td ><?php echo $total->interes_1*1;?></td>
                                    <td ><?php echo $total->form_1*1;?></td>
                                    <td ><?php echo $total->monto_1*1;?></td>
                                    <td ><?php echo $total->monto_venta_1*1;?></td>
                                    <td ><?php echo $total->anticipo_1*1;?></td>
                                    <td ><?php echo $total->intercambio_1*1;?></td>
                                    <td ><?php echo $total->int_consumido_1*1;?></td>
                                    <td ><?php echo $total->saldo_financiar_1*1;?></td>
                                    <td ><?php echo $total->capital_pagado_1*1;?></td>
                                    <td ><?php echo $total->saldo_final_1*1;?></td>
                                    <td ><?php echo $total->aportado_1*1;?></td>
                                    <td >&nbsp;</td>
                                    <td >&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan="10">Totales $us.</td>
                                    <td ><?php echo $total->capital_2*1;?></td>
                                    <td ><?php echo $total->interes_2*1;?></td>
                                    <td ><?php echo $total->form_2*1;?></td>
                                    <td ><?php echo $total->monto_2*1;?></td>
                                    <td ><?php echo $total->monto_venta_2*1;?></td>
                                    <td ><?php echo $total->anticipo_2*1;?></td>
                                    <td ><?php echo $total->intercambio_2*1;?></td>
                                    <td ><?php echo $total->int_consumido_2*1;?></td>
                                    <td ><?php echo $total->saldo_financiar_2*1;?></td>
                                    <td ><?php echo $total->capital_pagado_2*1;?></td>
                                    <td ><?php echo $total->saldo_final_2*1;?></td>
                                    <td ><?php echo $total->aportado_2*1;?></td>
                                    <td >&nbsp;</td>
                                    <td >&nbsp;</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div id="ContenedorSeleccion">
                        <div id="CajaBotones">
                            <input type="button" id="btn_revertir" class="boton" name="" value="Revertir" >
                            <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=ter_reversion_masiva&tarea=AGREGAR';">
                        </div>
                    </div> 
                </div>
                <?php if(count($_POST[urb_ids])>0){ ?>
                    <?php foreach ($_POST[urb_ids] as $urb_id) {?>
                    <input type="hidden" name="urb_ids[]" value="<?php echo $urb_id;?>">
                    <?php } ?>
                <?php } ?>
                <input type="hidden" id="fecha" name="fecha" value="<?php echo $_POST[fecha]?>">
                <input type="hidden" name="ven_suc_id" value="<?php echo $_POST[ven_suc_id]?>">
                <input type="hidden" name="op_rel" value="<?php echo $_POST[op_rel]?>">
                <input type="hidden" name="nro_cuotas" value="<?php echo $_POST[nro_cuotas]?>">
                <textarea hidden="" name="nota" ><?php echo $_POST[nota]?></textarea>
                <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
            </form>
        </div>
        <style>
            .btn_ver_venta, .btn_ver_venta_nota{display: inline; cursor: pointer;}
            .btn_aprove_venta, .btn_cancel_venta {display: inline; cursor: pointer;}
            .btn_aprove_venta:hover, .btn_cancel_venta:hover {opacity: 0.7}
        </style>
        <script>
            $('.btn_aprove_venta').click(function(){
                
                $(this).hide();
                var cancel=$(this).next();
                $(cancel).show();
                
                var tr=$(this).parent().parent();
                $(tr).find('td').eq(1).find('.ven_ids').remove();
            });
            $('.btn_cancel_venta').click(function(){
                $(this).hide();
                var aprove=$(this).prev();
                $(aprove).show();
                var tr=$(this).parent().parent();
                var ven_id=$(this).attr('data-id');
                var input='<input type="hidden" class="ven_ids" name="ven_ids[]" value="'+ven_id+'">';
                $(tr).find('td').eq(1).append(input);
                
            });
            $(".tablaLista").thfloat();
            $('.btn_ver_venta').click(function(){
                var ven_id=$(this).attr('data-id');
                window.open('gestor.php?mod=venta&tarea=SEGUIMIENTO&id='+ven_id,'reportes','left=150,width=1000,height=500,top=0,scrollbars=yes');
            });
            $('.btn_ver_venta_nota').click(function(){
                var ven_id=$(this).attr('data-id');
                window.open('gestor.php?mod=venta&tarea=NOTA&id='+ven_id,'reportes','left=150,width=1000,height=500,top=0,scrollbars=yes');
            });
            
            $('#btn_revertir').click(function(){
                var fecha=$('#fecha').val();
                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                    var dato = JSON.parse(respuesta);
                    if (dato.response === "ok") {
                        document.frm_sentencia.submit();
                    } else if (dato.response === "error") {
                        $.prompt(dato.mensaje);
                        return false;
                    }
                });
            });
        </script>
            
        <?php
    }

    function frm_parametros_reversion() {
        $url = $this->link . '?mod=' . $this->modulo."&tarea=" . $_GET['tarea'];
        $this->formulario->dibujar_titulo('PARAMETRO PARA REVERSION MASIVA');
        if ($this->mensaje <> "") {
            $this->formulario->mensaje('Error', $this->mensaje);
        }
        ?>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script src="js/chosen.jquery.min.js"></script>
        <link href="css/chosen.min.css" rel="stylesheet"/>
        <script src="js/util.js"> </script>
        <div id="Contenedor_NuevaSentencia">
            <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">  
                <div id="FormSent" style="width:100%;">
                    <div class="Subtitulo">Datos</div>
                    <div id="ContenedorSeleccion">
                        <input type="hidden" name="parametros" value="ok">
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizacion</div>
                            <div id="CajaInput">
                                <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto">
                                    <option value="">-- Seleccione --</option>
                                    <?php
                                    $fun = NEW FUNCIONES;
                                    $fun->combo("select urb_id as id,urb_nombre as nombre from urbanizacion where 1 and urb_eliminado='No'", $_POST['ven_urb_id']);
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >&nbsp;</div>
                            <div id="CajaInput">
                                <div class="box_lista_cuenta"> 
                                    <table id="tab_urbanizacion" class="tab_lista_cuentas">
                                        <tbody>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>							   							   								
                        </div>
                        <script>
                            $('#ven_urb_id').change(function(){
//                                    console.log($(this).val());
                                var id=trim($('#ven_urb_id option:selected').val());
                                var valor=trim($('#ven_urb_id option:selected').text());
                                agregar_cuenta({id:id, valor:valor},'urbanizacion');
                                $(this).val('');
//                                    $('#cja_cue_id option:[value=""]').attr('selected','true');
//                                    $('#cja_cue_id').trigger('chosen:updated');
                            });

                            function agregar_cuenta(objeto,input) {

                                if (!$("#tab_"+input+' .urb_ids[value='+objeto.id+']').length) {
                                    var fila='';
                                    fila += '<tr>';
                                    fila += '   <td>';
                                    fila += '       <input type="hidden" class="urb_ids" name="urb_ids[]" value="'+objeto.id+'">';
                                    fila += '       <input type="hidden" class="urb_nombres" name="urb_nombres[]" value="'+objeto.valor+'">';
                                    fila += '       ' + objeto.valor;
                                    fila += '   </td>';
                                    fila += '   <td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                                    fila += '</tr>';
                                    $("#tab_"+input+' tbody').append(fila);
                                }
                            }
                            $(".img_del_cuenta").live('click', function() {
                                $(this).parent().parent().remove();
                            });
                        </script>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Fecha</div>
                            <div id="CajaInput">
                                <input class="caja_texto" name="fecha" id="fecha" size="12" value="<?php echo date('d/m/Y'); ?>" type="text">
                            </div>		
                        </div>

                        <div id="ContenedorDiv">
                            <div class="Etiqueta" ><span class="flechas1">*</span>Sucursal</div>
                            <div id="CajaInput">
                                <select style="width:200px;" name="ven_suc_id" id="ven_suc_id" class="caja_texto" >
                                    <option value="">-- Todos --</option>
                                    <?php
                                    $fun->combo("select suc_id as id, suc_nombre as nombre from ter_sucursal", 0);
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nro de Cuotas</div>
                            <div id="CajaInput">
                                <select id="op_rel" name="op_rel" style="width: 50px">
                                    <option value="=">=</option>
                                    <option value=">=">>=</option>
                                    <option value="<="><=</option>
                                    <option value=">">></option>
                                    <option value="<"><</option>
                                </select>
                                <input class="caja_texto" name="nro_cuotas" id="nro_cuotas" size="12" value="" type="text">
                            </div>		
                        </div>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta" >Nota</div>
                            <div id="CajaInput">
                                <textarea id="nota" name="nota"></textarea>
                            </div>		
                        </div>
                    </div>
                    <div id="ContenedorSeleccion">
                        <div id="CajaBotones">
                            <input type="button" id="btn_generar" class="boton" name="" value="Generar" >
                            <input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href = 'gestor.php?mod=ter_reversion_masiva';">
                        </div>
                    </div> 
                </div>
            </form>
        </div>
        <script>
            $("#fecha").mask("99/99/9999");
            mask_decimal('#nro_cuotas',null);
            $('#nro_cuotas').keypress(function(e){
                if(e.keyCode===13){
                    $('#btn_generar').trigger('click');
                }
            });
            $('#btn_generar').click(function(){
                var fecha=$('#fecha').val();
                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                    var dato = JSON.parse(respuesta);
                    if (dato.response === "ok") {
                        document.frm_sentencia.submit();
                    } else if (dato.response === "error") {
                        $.prompt(dato.mensaje);
                        return false;
                    }
                });
            });
        </script>
        <?php
    }

        
    function total_pagado_venta($ven_id) {
//        $sql_pag = "select sum(vpag_interes)as interes, sum(vpag_capital) as capital, sum(vpag_monto) as monto, 
//                            sum(vpag_capital_desc) as descuento, sum(vpag_capital_inc) as incremento ,
//                            sum(vpag_costo) as costo
//                            from venta_pago where vpag_ven_id='$ven_id' and vpag_estado='Activo'
//                    ";
//        $pagado = FUNCIONES::objeto_bd_sql($sql_pag);
        $pagado = FUNCIONES::total_pagado($ven_id);
        return $pagado;
    }
    
    function mostrar_listado_reversion($vrm_id) {
        $url = $this->link . '?mod=' . $this->modulo."&tarea=" . $_GET['tarea'];
        $this->formulario->dibujar_titulo('LISTADO DE REVERSION MASIVA');
        $vrm=  FUNCIONES::objeto_bd_sql("select * from ter_reversion_masiva where vrm_id='$vrm_id'");
        
        ?>
        <script src="js/jquery.thfloat-0.7.2.min.js"></script>

        <div >
            <a href="javascript:var c = window.open('about:blank','reportes','left=100,width=800,height=500,top=0,scrollbars=yes'); c.document.write('<html><head><title>Vista Previa</title><head> <link href=css/estilos.css rel=stylesheet type=text/css /> </head> <body> <div id=imprimir> <div id=status> <p> <a href=javascript:window.print();>Imprimir</a> ... <a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>'); var dato = document.getElementById('contenido_reportes').innerHTML; c.document.write(dato); c.document.write('</center></body></html>'); c.document.close(); ">
                <img border="0" align="right" width="20" title="IMPRIMIR" src="images/printer.png">
            </a>
        </div>
        <div id="contenido_reportes">
            <br><br>
            <div id="ContenedorDiv">
                <div class="Etiqueta" ><b>Urbanizaciones</b></div>
                <div id="CajaInput">
                    <?
                    $str_urbs=  FUNCIONES::atributo_bd_sql("select group_concat(distinct urb_nombre) as campo from urbanizacion where urb_id in ($vrm->vrm_urb_ids)");

                    ?>
                    <div class="read-input"><?php echo $str_urbs;?></div>
                </div>		
            </div>
            <div id="ContenedorDiv">
                <div class="Etiqueta" >Fecha</div>
                <div id="CajaInput">
                    <div class="read-input"><?php echo FUNCIONES::get_fecha_latina($vrm->vrm_fecha);?></div>
                </div>		
            </div>
            <div id="ContenedorDiv">
                <div class="Etiqueta" >Sucursal</div>
                <div id="CajaInput">
                    <?php
                    $str_suc="TODOS";
                    if($vrm->vrm_suc_id){
                        $str_suc=  FUNCIONES::atributo_bd_sql("select suc_nombre as campo where suc_id='$vrm->vrm_suc_id'");
                    }
                    ?>
                    <div class="read-input"><?php echo $str_suc;?></div>
                </div>
            </div>
            <div id="ContenedorDiv">
                <div class="Etiqueta" >Nro de Cuotas</div>
                <div id="CajaInput">
                    <div class="read-input"><?php echo "$vrm->vrm_condicion a <b>$vrm->vrm_num_cuotas</b>";?></div>
                </div>
            </div>
            <table class="tablaLista" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nro Venta</th>
                        <th>Cliente</th>
                        <th>Proyecto</th>
                        <th>Moneda</th>
                        <th>Lote</th>
                        <th>Cuotas Mora</th>
                        <th>U. Fecha Pago</th>
                        <th>Dias Interes</th>
                        <th>Est. Cartera</th>
                        <th>Capitales Mora</th>
                        <th>Interes</th>
                        <th>Formulario</th>
                        <th>Total Deuda</th>
                        <th>Monto Venta</th>
                        <th class="topciones">Anticipo</th>
                        <th>Intercambio</th>
                        <th class="topciones">Int. Consumido</th>
                        <th>Saldo Financiar</th>
                        <th class="topciones">Capital Pagado</th>
                        <th>Saldo Capital</th>
                        <th class="topciones">Total Aportado</th>
                        <th >&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $aestados=array('Vigente'=>'#00A015','Vencido'=>'#C6C600','Mora'=>'#D87E00','Ejecucion'=>'#FF0000');
                    
                    $listado=$this->obtener_detalle_reversion_masiva($vrm_id);
                    
                    $total=new stdClass();
                    ?>

                    <?php foreach ($listado as $det) {?>
                    <tr>
                        <td><?php echo $det->nro;?></td>
                        <td>
                            <input type="hidden" class="ven_ids" name="ven_ids[]" value="<?php echo $det->ven_id;?>">
                            <?php echo $det->ven_id;?>
                        </td>
                        <td><?php echo $det->cliente;?></td>
                        <td><?php echo $det->proyecto;?></td>
                        <td><?php echo $det->moneda;?></td>
                        <td><?php echo $det->lote;?></td>
                        <td><?php echo $det->cuotas_mora;?></td>
                        <td><?php echo $det->ufecha_pago;?></td>
                        <td><?php echo $det->dias_interes;?></td>
                        <td>
                            <span style="padding: 2px 3px; color: #fff; background-color: <?php echo $aestados[$det->ecartera]?>">
                            <?php echo $det->ecartera;?>
                            </span>
                        </td>

                        <td><?php echo $det->capital;?></td>
                        <td><?php echo $det->interes;?></td>
                        <td><?php echo $det->form;?></td>
                        <td><?php echo $det->monto_total;?></td>

                        <td><?php echo $det->monto_venta;?></td>
                        <td><?php echo $det->anticipo;?></td>
                        <td><?php echo $det->intercambio;?></td>
                        <td><?php echo $det->int_consumido;?></td>
                        <td><?php echo $det->saldo_financiar;?></td>
                        <td><?php echo $det->capital_pagado;?></td>
                        <td><?php echo $det->saldo_final;?></td>
                        <td><?php echo $det->aportado;?></td>
                        
                        <td>
                            <img class="btn_ver_venta" src="images/b_browse.png" width="16" data-id="<?php echo $det->ven_id;?>">
                            <img class="btn_ver_venta_nota" src="images/notas.png" width="16" data-id="<?php echo $det->ven_id;?>">
                        </td>
                    </tr>
                    <?php
                    $mon=$det->mon_id;
                    $total->{"capital_$mon"}+=$det->capital;            
                    $total->{"interes_$mon"}+=$det->interes;
                    $total->{"form_$mon"}+=$det->form;
                    $total->{"monto_$mon"}+=$det->monto_total;
                    $total->{"monto_venta_$mon"}+=$det->monto_venta;
                    $total->{"anticipo_$mon"}+=$det->anticipo;
                    $total->{"intercambio_$mon"}+=$det->intercambio;
                    $total->{"int_consumido_$mon"}+=$det->int_consumido;
                    $total->{"saldo_financiar_$mon"}+=$det->saldo_financiar;
                    $total->{"capital_pagado_$mon"}+=$det->capital_pagado;
                    $total->{"saldo_final_$mon"}+=$det->saldo_final;
                    $total->{"aportado_$mon"}+=$det->aportado;
                    ?>
                <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="10">Totales Bs.</td>
                        <td ><?php echo $total->capital_1*1;?></td>
                        <td ><?php echo $total->interes_1*1;?></td>
                        <td ><?php echo $total->form_1*1;?></td>
                        <td ><?php echo $total->monto_1*1;?></td>
                        <td ><?php echo $total->monto_venta_1*1;?></td>
                        <td ><?php echo $total->anticipo_1*1;?></td>
                        <td ><?php echo $total->intercambio_1*1;?></td>
                        <td ><?php echo $total->int_consumido_1*1;?></td>
                        <td ><?php echo $total->saldo_financiar_1*1;?></td>
                        <td ><?php echo $total->capital_pagado_1*1;?></td>
                        <td ><?php echo $total->saldo_final_1*1;?></td>
                        <td ><?php echo $total->aportado_1*1;?></td>
                        
                        <td >&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="10">Totales $us.</td>
                        <td ><?php echo $total->capital_2*1;?></td>
                        <td ><?php echo $total->interes_2*1;?></td>
                        <td ><?php echo $total->form_2*1;?></td>
                        <td ><?php echo $total->monto_2*1;?></td>
                        <td ><?php echo $total->monto_venta_2*1;?></td>
                        <td ><?php echo $total->anticipo_2*1;?></td>
                        <td ><?php echo $total->intercambio_2*1;?></td>
                        <td ><?php echo $total->int_consumido_2*1;?></td>
                        <td ><?php echo $total->saldo_financiar_2*1;?></td>
                        <td ><?php echo $total->capital_pagado_2*1;?></td>
                        <td ><?php echo $total->saldo_final_2*1;?></td>
                        <td ><?php echo $total->aportado_2*1;?></td>
                        
                        <td >&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
                    
                
        </div>
        <style>
            .btn_ver_venta, .btn_ver_venta_nota{display: inline; cursor: pointer;}
            .btn_aprove_venta, .btn_cancel_venta {display: inline; cursor: pointer;}
            .btn_aprove_venta:hover, .btn_cancel_venta:hover {opacity: 0.7}
        </style>
        <script>
            $('.btn_aprove_venta').click(function(){
                
                $(this).hide();
                var cancel=$(this).next();
                $(cancel).show();
                
                var tr=$(this).parent().parent();
                $(tr).find('td').eq(1).find('.ven_ids').remove();
            });
            $('.btn_cancel_venta').click(function(){
                $(this).hide();
                var aprove=$(this).prev();
                $(aprove).show();
                var tr=$(this).parent().parent();
                var ven_id=$(this).attr('data-id');
                var input='<input type="hidden" class="ven_ids" name="ven_ids[]" value="'+ven_id+'">';
                $(tr).find('td').eq(1).append(input);
                
            });
            $(".tablaLista").thfloat();
            $('.btn_ver_venta').click(function(){
                var ven_id=$(this).attr('data-id');
                window.open('gestor.php?mod=venta&tarea=SEGUIMIENTO&id='+ven_id,'reportes','left=150,width=1000,height=500,top=0,scrollbars=yes');
            });
            $('.btn_ver_venta_nota').click(function(){
                var ven_id=$(this).attr('data-id');
                window.open('gestor.php?mod=venta&tarea=NOTA&id='+ven_id,'reportes','left=150,width=1000,height=500,top=0,scrollbars=yes');
            });
            
            $('#btn_revertir').click(function(){
                var fecha=$('#fecha').val();
                $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                    var dato = JSON.parse(respuesta);
                    if (dato.response === "ok") {
                        document.frm_sentencia.submit();
                    } else if (dato.response === "error") {
                        $.prompt(dato.mensaje);
                        return false;
                    }
                });
            });
        </script>
            
        <?php
    }
}
        ?>