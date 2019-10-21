<?php
class REP_DETALLE_MORAS extends BUSQUEDA {
	var $formulario;
	var $mensaje;	
	function REP_DETALLE_MORAS(){
		//permisos
            $this->ele_id=165;
            $this->busqueda();
            //fin permisos
            $this->coneccion= new ADO();
            $this->link='gestor.php';
            $this->modulo='rep_detalle_moras';
            $this->formulario = new FORMULARIO();
            $this->formulario->set_titulo('LISTADO DE CUOTAS EN MORA');
	}	
	
	function dibujar_busqueda(){
            $this->formulario();
	}
	
	function formulario(){
		$this->formulario->dibujar_cabecera();		
		if(!($_POST['formu']=='ok')){
		?>		
                        <?php
                        if($this->mensaje<>"")
                        {
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
                        <!--MaskedInput-->
                        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                        <!--MaskedInput-->
                        <script>	
                            function reset_fecha_inicio(){
                                document.frm_sentencia.fecha_inicio.value='';
                            }
                            function reset_fecha_fin(){
                                document.frm_sentencia.fecha_fin.value='';
                            }
                            function reset_interno(){
                                document.frm_sentencia.interno.value = "";
                                document.frm_sentencia.int_nombre_persona.value = "";
                            }
                            function ocultar_estado(valor){
                                if(valor=='M')
                                    $('#seccion_estado').css('display','none');
                                else
                                    $('#seccion_estado').css('display','block');
                            }
                        </script>
                        <!--AutoSuggest-->
                        <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
                        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
                        <!--AutoSuggest-->					
                        <div id="Contenedor_NuevaSentencia">
                            <form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=rep_detalle_moras" method="POST" enctype="multipart/form-data">  
                                <div id="FormSent">
                                    <div class="Subtitulo">Filtro del Reporte</div>
                                    <div id="ContenedorSeleccion">
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >Codigo de Venta</div>
                                            <div id="CajaInput">												
                                                <input name="formu" readonly type="hidden" class="caja_texto" value="ok" size="2">
                                                <input name="ven_codigo" id="ven_codigo"  type="text" class="caja_texto" value="" size="40">
                                            </div>
                                        </div>                                        	
                                    </div>
                                    <div id="ContenedorDiv">
                                        <div id="CajaBotones">
                                            <center>
                                               <input type="hidden" class="boton" name="formu" value="ok">
                                               <input type="submit" class="boton" name="" value="Generar Reporte">
                                            </center>
                                         </div>
                                    </div>
                                </div>
                            </form>	
                        </div>
                        <script>
                            jQuery(function($){
                                $("#fecha_inicio").mask("99/99/9999");
                                $("#fecha_fin").mask("99/99/9999"); 
                             });
                        </script>                        
		<?php
		}
		
		if($_POST['formu']=='ok')
			$this->mostrar_reporte();
	}
	
        function barra_de_impresion(){
            $pagina="'contenido_reporte'";		
            $page="'about:blank'";		
            $extpage="'reportes'";		
            $features="'left=100,width=800,height=500,top=0,scrollbars=yes'";		
            $extra1="'<html><head><title>Vista Previa</title><head>
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
            $extra2="'</center></body></html>'"; 		
            $myday = setear_fecha(strtotime(date('Y-m-d')));
            ?>		
            <?php 
                echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
                      c.document.write('.$extra1.');
                      var dato = document.getElementById('.$pagina.').innerHTML;
                      c.document.write(dato);
                      c.document.write('.$extra2.'); c.document.close();
                      ">
                    <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                    </a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=rep_detalle_moras\';"></td></tr></table><br><br>
            ';
        }
	function mostrar_reporte(){
            if(!isset($_POST['info'])){
                    $this->barra_de_impresion();
            }else{
                echo '<div id=imprimir>
                    <div id=status>
                        <p> 
                            <a href=javascript:window.print();>Imprimir</a> 
                            <a href=javascript:self.close();>Cerrar</a></td>
                        </p>
                    </div>
                </div>';
            }
            $conversor = new convertir();
            ////
            ?>				 

            <div id="contenido_reporte" style="clear:both;">
                <center>
                    <table style="font-size:12px;" width="100%" cellpadding="5" cellspacing="0" >
                            <tr>
                                <td width="40%" >
                                <strong><?php echo _nombre_empresa; ?></strong><BR>
                                    </td>
                                <td  width="20%" ><p align="center" ><strong><h3><center>LISTADO DE CUOTAS EN MORA</center></h3></strong></p></td>
                                <td  width="40%" ><div align="right"><img src="imagenes/micro.png" /></div></td>
                            </tr>
                    </table>                    
                    <?php
                    $conec= new ADO();
                    $ven_codigo=  trim($_POST[ven_codigo]);
                    $ven_id=trim($_POST[ven_id]);
                    $_filtro='';
                    if($ven_codigo!=''){
                        $_filtro=" and ven_codigo='$ven_codigo'";
                    }elseif($ven_id!=''){                        
                        $_filtro=" and ven_id='$ven_id'";
                    }
                    $sql = "select ven_id, ven_codigo, ven_fecha,ven_usu_id,ven_tipo,ven_moneda,ven_monto,ven_int_id,ven_co_propietario,ven_res_anticipo,
                            int_nombre,int_apellido, ven_cuota_inicial,ven_meses_plazo,ven_observacion
                            from venta, interno
                            where ven_int_id=int_id $_filtro";
//                    echo $sql;
                    $conec->ejecutar($sql);		
                    $num=$conec->get_num_registros();		
                    $objeto=$conec->get_objeto();
                    $monto_venta=$objeto->ven_monto;
                    $monto_pagado=  $this->montopagado($objeto->ven_id);
                    $monto_mora_pagado=  $this->multa_pagada($objeto->ven_id);
                    ?>
                    <table border='0' style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >                       
                            <tr>
                             <td colspan="2">
                                 <strong><br/>Nro de Venta: </strong> <?php echo $_GET[id];?> <br>
                             <?php
                                if($objeto->ven_int_id <> 0){
                                    $persona=$objeto->int_nombre.' '.$objeto->int_apellido;?>
                                    <strong>Persona: </strong> <?php echo $persona;?> <br>
                                <?php }?>
                                <?php
                                 if($objeto->ven_co_propietario <> 0){
                                         $persona=$this->obtener_nombrepersona_interno($objeto->ven_co_propietario); 
                                         ?>
                                         <strong>Co-Propietario: </strong> <?php echo $persona;?> <br>
                                         <?php
                                 }?>
                                 <!--<strong>Tipo: </strong> <?php // echo $objeto->ven_tipo;?> <br>-->
                                 <strong>Moneda: </strong> <?php $moneda=$objeto->ven_moneda; if($moneda=="1") echo "Bolivianos"; else echo "Dolares"; ?> <br>                             
                                 <?php
                                 if($objeto->ven_observacion<>''){
                                 ?>
                                    <strong>Observación: </strong> <?php echo $objeto->ven_observacion;?> <br><br>
                                 <?php
                                 }else{
                                 ?>

                                 <?php
                                 }
                                 ?>
                                 </td>
                                <td align="right">
                                    <strong>Fecha: </strong> <?php echo $myday = setear_fecha(strtotime($objeto->ven_fecha));?> <br>
                                    <!--<strong>Tipo de Cambio: </strong> <?php // echo $tc;?> <br>-->
                                    <strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->ven_usu_id);?><br>
                                    <strong>Codigo: </strong> <?php echo $objeto->ven_codigo;?><br>
                               </td>
                           </tr>
                    </table>
                    <style>
                        .texto_rojo{
                            color: #ff0000;
                        }
                        .texto_verde{
                            color: #189300;
                        }
                        .texto_cafe{
                            color: #C46202;
                        }
                    </style>
                    <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>F. Programada</th>
                                <th>F. Pagada</th>
                                <th>Dias Mora</th>
                                <th>Monto Mora</th>
                                <th class="tOpciones">Monto</th>
                                <th class="tOpciones">Monto Pagado</th>
                                <th class="tOpciones">Monto por Pagar</th>
                                <th >Estado</th>
                                <th >Total</th>
                            </tr>	
                        </thead>
                        <tbody>
                            <?
                            $fecha_actual=  date('Y-m-d');
                            $sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_monto_parcial,int_nombre,
                                    int_apellido,ind_fecha_programada,ind_fecha_pago,ind_interes,ind_capital,ind_saldo,ind_valor_form,
                                    ind_estado_mora,ind_tabla_id, ind_estado_parcial, ind_num_correlativo
                                FROM 
                                interno_deuda, venta, interno
                                where ind_tabla='venta' and ind_tabla_id=ven_id and ven_int_id=int_id and (ind_estado='Pendiente' or ind_estado='Pagado') 
                                $_filtro and ind_fecha_programada <='$fecha_actual' order by ind_num_correlativo";
                            $conec->ejecutar($sql);
                            $num=$conec->get_num_registros();
                            $val_mor_dias=FUNCIONES::atributo_bd_sql("select par_val_dias_mora as campo from ad_parametro");
                            $id_ultimo = -1;
                            $sum_mora_v=0;
                            $sum_mora_p=0;
                            $sum_monto_pagar=0;
                            $sum_monto_pagado=0;
                            ?>
                            <?php for($i=0;$i<$num;$i++){?>
                                <?php $objeto=$conec->get_objeto();?>
                                <tr>
                                    <td><?php echo $objeto->ind_concepto;?></td>
                                    <td class="<?php echo $objeto->ind_estado=='Pagado'?'texto_verde':($objeto->ind_estado_parcial=='listo'?'texto_rojo':'texto_cafe'); ?>" >
                                        <?php echo FUNCIONES::get_fecha_latina($objeto->ind_fecha_programada);?>
                                    </td>
                                    <td><?php echo $objeto->ind_fecha_pago!='0000-00-00'?FUNCIONES::get_fecha_latina($objeto->ind_fecha_pago):'';?></td>
                                    <?php if($objeto->ind_estado_mora=='si'){?>
                                        <?php $mora= FUNCIONES::objeto_bd_sql("select * from mora where mor_ind_id='$objeto->ind_id' and mor_eliminado='no'");?>
                                        <td><?php echo $mora->mor_dias*1;?></td>                                        
                                        <td><?php echo number_format($mora->mor_monto, 2) ;?></td>
                                        <?php $sum_mora_p+=$mora->mor_monto;?>
                                    <?php }else{?>
                                        <?php if($objeto->ind_estado=='Pendiente'){?>
                                            <td class="texto_rojo"><?php echo $dias=  FUNCIONES::diferencia_dias($objeto->ind_fecha_programada, date($fecha_actual));?></td>
                                            <td class="texto_rojo"><?php echo $dias*$val_mor_dias;?></td>
                                            <?php $sum_mora_v+=$dias*$val_mor_dias;?></td>
                                        <?php }else{?>
                                            <td>0</td>
                                            <td>0.00</td>
                                        <?php }?>                                        
                                    <?php }?>
                                    <td><?php echo $objeto->ind_monto;?></td>
                                    <td>
                                        <?php $monto_pag=0;?>
                                        <?php if($objeto->ind_estado=='Pagado'){?>
                                            <?php if($objeto->ind_estado_parcial=='listo'){?>
                                                <?php echo $objeto->ind_monto;?>
                                                <?php $sum_monto_pagado+=$objeto->ind_monto;?>
                                                <?php $monto_pag=$objeto->ind_monto;?>
                                            <?php }elseif($objeto->ind_estado_parcial=='finalizado'){?>
                                                <?php echo $objeto->ind_monto_parcial;?>
                                                <?php $sum_monto_pagado+=$objeto->ind_monto_parcial;?>
                                                <?php $monto_pag=$objeto->ind_monto_parcial;?>
                                            <?php }?>                                            
                                        <?php } elseif ($objeto->ind_estado=='Pendiente') {?>
                                            <?php if($objeto->ind_estado_parcial=='listo'){?>
                                                <?php echo '0';?>
                                                <?php $sum_monto_pagado+=0;?>
                                                <?php                                         '0';?>
                                            <?php }elseif($objeto->ind_estado_parcial=='iniciado'){?>
                                                <?php echo $objeto->ind_monto_parcial;?>
                                                <?php $sum_monto_pagado+=$objeto->ind_monto_parcial;?>
                                                <?php $monto_pag=$objeto->ind_monto_parcial;?>
                                            <?php }?>                                        
                                        <?php }?>
                                    </td>
                                    
                                    <td>
                                        <?php if($monto_pag>=$objeto->ind_monto){?>
                                            0
                                        <?php }else{?>
                                            <?php echo $objeto->ind_monto-$monto_pag;?>
                                            <?php $sum_monto_pagar +=$objeto->ind_monto-$monto_pag;?>
                                        <?php }?>
                                    </td>
                                    
                                    <td class="<?php echo $objeto->ind_estado=='Pagado'?'texto_verde':($objeto->ind_estado_parcial=='listo'?'texto_rojo':'texto_cafe'); ?>" >
                                        <?php echo $objeto->ind_estado;?>
                                    </td>
                                    <?php if($id_ultimo==-1){
                                        $id_ultimo=$this->fecha_ultima_cuota_pagada($objeto->ind_id);
                                    }?>
                                    <?php if($id_ultimo==$objeto->ind_id){?>                                    
                                        <td><b><?php echo number_format($sum_mora_p, 2);?></b></td>
                                    <?php }else{?>
                                        <?php if($objeto->ind_estado=='Pendiente' && $i==($num-1)){?>
                                            <td><b><?php echo number_format($sum_mora_v, 2);?></b></td>
                                        <?php }else{?>
                                            <td>&nbsp;</td>
                                        <?php }?>
                                    <?php }?>
                                </tr>
                                <?php $conec->siguiente();?>
                            <?php }?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">Total</td>
                                <td ><?php echo number_format($sum_mora_p+$sum_mora_v, 2);?></td>
                                <td >&nbsp;</td>
                                <td ><?php echo number_format($sum_monto_pagado,2);?></td>
                                <td ><?php echo number_format($sum_monto_pagar,2);?></td>
                                <td >&nbsp;</td>
                                <td >&nbsp;</td>                                
                            </tr>
                        </tfoot>                        
                    </table>                    
                </center>
                <div style="text-align: left;">
                    <b>Monto de Venta: </b><?php echo number_format($monto_venta, 2);?><br>
                    <b>Monto de Pagado: </b><?php echo number_format($monto_pagado, 2);?><br>                    
                    <b>Saldo: </b><?php echo number_format($monto_venta-$monto_pagado, 2);?><br>
                    <b>Cuotas por pagar: </b><?php echo number_format($sum_monto_pagar, 2);?><br>
                    
                    <b>Mora Generado: </b><?php echo number_format($sum_mora_p, 2);?><br>                    
                    <b>Mora Pagado: </b><?php echo number_format($monto_mora_pagado, 2);?><br>
                    <b>Monto Mora Saldo: </b><?php echo number_format($sum_mora_p-$monto_mora_pagado, 2);?><br>
                    <b>Mora Proyectado: </b><?php echo number_format($sum_mora_v, 2);?><br>
                    <b>Total Monto Mora: </b><?php echo number_format($sum_mora_v+($sum_mora_p-$monto_mora_pagado), 2);?><br>
                    <b>Total a Pagar: </b><?php echo number_format($sum_monto_pagar+($sum_mora_v+($sum_mora_p-$monto_mora_pagado)), 2);?><br>
                </div>
                <br>
                <table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
            </div>            
        <?php
        }
	
	function obtener_ultimos_datos_interno($interno,&$telefono,&$celular,&$email)
	{
		$conec= new ADO();
		
		//Ultimo Telefono de la Persona
		$sql="select int_telefono from interno where int_id=$interno ";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$telefono=$objeto->inttelf_telefono;
		
		//Ultimo Celular de la Persona
		$sql="select int_celular from interno where int_id=$interno ";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$celular=$objeto->intcel_celular;
		
		//Ultimo Email de la Persona
		$sql="select int_email from interno where int_id=$interno ";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$email=$objeto->intemail_email;
	}
	
	function obtener_total_pagos_acumulados($ind_id) {
        $sql = "select * from interno_deuda_pagos where idp_ind_id='" . $ind_id . "' and idp_estado='Activo'";
		
        //echo $sql;
        $conec = new ADO();
        $conec->ejecutar($sql);
        $total = 0;
        $num = $conec->get_num_registros();
        
        if ($num > 0) {
            
            for($i = 0; $i < $num; $i++){
                $obj = $conec->get_objeto();                
                $total += $obj->idp_monto;                
                $conec->siguiente();
            }
            
        }
        
        return $total;
    }
    
    function nombre_persona($usuario){
        $conec= new ADO();
        $sql="select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";
        $conec->ejecutar($sql);
        $objeto=$conec->get_objeto();
        return $objeto->int_nombre.' '.$objeto->int_apellido; 
    }
    function fecha_ultima_cuota_pagada($ind_id){
        $conec= new ADO();		
        $sql="SELECT * FROM interno_deuda where ind_id=$ind_id";		
        $conec->ejecutar($sql);
        $objeto=$conec->get_objeto();
        $id_venta = $objeto->ind_tabla_id;

        $sql="select * from interno_deuda where ind_tabla='venta' and ind_tabla_id='$id_venta' and ind_estado='Pendiente' and ind_estado_parcial='iniciado' order by ind_id DESC LIMIT 0,1";

        $conec->ejecutar($sql); 
        $num=$conec->get_num_registros();
        if($num>0){
            $objeto=$conec->get_objeto();
            return $objeto->ind_id;
        }else{
            $sql="SELECT * FROM interno_deuda where ind_tabla='venta' and ind_tabla_id='$id_venta' AND ind_estado='Pagado' ORDER BY ind_id DESC LIMIT 0,1";
            $conec->ejecutar($sql);
            $objeto=$conec->get_objeto();
            $ind_id = $objeto->ind_id;
            if($ind_id==''){
                return $ind_id = $this->primera_cuota($id_venta);
            }else{
                return $ind_id;
            }
        }
    }
    function primera_cuota($id_venta){
        $conec=new ADO();
        $sql="SELECT * FROM interno_deuda where ind_tabla='venta' and ind_tabla_id='$id_venta' ORDER BY ind_num_correlativo DESC LIMIT 0,1";
        $conec->ejecutar($sql);
        $objeto=$conec->get_objeto();
        $ind_id = $objeto->ind_id;
        return $ind_id;
        
    }
    function montopagado($id){
        $conec=new ADO();
        //Sumatoria del monto de los registros que estan en estado 'Pagados'
        $sql="select sum(ind_monto) as monto from interno_deuda where ind_tabla='venta' and ind_estado='Pagado' and ind_tabla_id='$id'";
        $conec->ejecutar($sql);
        $objeto=$conec->get_objeto();
        $monto_totales=$objeto->monto;
        //Sumatoria del monto de los formularios(ind_valor_form) que estan en estado 'Pagados'
        $sql="select sum(ind_valor_form) as monto_valores_form from interno_deuda where ind_tabla='venta' and ind_estado='Pagado' and ind_tabla_id='$id'";
        $conec->ejecutar($sql);
        $objeto=$conec->get_objeto();
        $monto_valores_form=$objeto->monto_valores_form;
        //Sumatoria de los montos Parciales que estan en estado 'Pendiente'
        $sql="select sum(ind_monto_parcial) as monto_parcial from interno_deuda where ind_tabla='venta' and ind_estado='Pendiente' and ind_tabla_id='$id'";
        $conec->ejecutar($sql);
        $objeto=$conec->get_objeto();
        $monto_parciales=$objeto->monto_parcial;
        return ($monto_totales + $monto_valores_form + $monto_parciales);
    }
    
    function multa_pagada($idventa){
        $sql = "select sum(pmo_monto)as total from pago_mora where pmo_estado='Activo' and pmo_ven_id='".$idventa."'";
        $conec = new ADO();
        $conec->ejecutar($sql);
        $num = $conec->get_num_registros();
        $total = 0;
        if($num > 0){
            if($conec->get_objeto()->total != NULL){
                $total = $conec->get_objeto()->total;
            }
        }
        return $total;
    }
    
    function dibujar_popup(){
        $form="";
        if(isset($_GET['form'])){
            $form="&form=".$_GET['form'];
        }
        ?>
            <!DOCTYPE html>
            <html>
                <head>        
                </head>
                <body>
                    <div id="iframe">
                        <form name="formulario" action="gestor.php?mod=<?php echo $this->modulo.$form;?>" method="POST">
                            <input type="hidden" name="formu" value="ok">
                            <input type="hidden" name="fecha_inicio" value="<?php echo $_GET['ven'];?>">                            
                        </form>
                    </div>
                </body>
                <script>                        
                    document.formulario.submit();
                </script>
            </html>
        <?php
    }
}
?>