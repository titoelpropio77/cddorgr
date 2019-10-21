<?php
class VENTA_SIMULACION extends BUSQUEDA {
	var $formulario;
	var $mensaje;
	var $usu;

        var $ver_estado;
        var $ver_pagado;
        var $ver_saldo_capital;

	function VENTA_SIMULACION(){
		//permisos
		$this->ele_id=205;
		$this->busqueda();
                if(!($this->verificar_permisos('AGREGAR'))){
                    $this->ban_agregar=false;
		}
               
                $this->ver_estado=true;
                if(!($this->verificar_permisos('VER ESTADO'))){
                    $this->ver_estado=false;
		}
                $this->ver_pagado=true;
                if(!($this->verificar_permisos('VER PAGADO'))){
                    $this->ver_pagado=false;
		}
                $this->ver_saldo_capital=true;
                if(!($this->verificar_permisos('VER SALDO CAPITAL'))){
                    $this->ver_saldo_capital=false;
		}
		//fin permisos
		$this->num_registros=14;
		$this->coneccion= new ADO();
                $num=0;
		
		$this->link='gestor.php';
		
		$this->modulo='venta_simulacion';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('VENTA_SIMULACION');
		
		$this->usu=new USUARIO;
	}
	
	function formulario_tcp($tipo){
            $conec= new ADO();			
            $sql="select * from interno";
            $conec->ejecutar($sql);		
            $nume=$conec->get_num_registros();
            $personas=0;
            if($nume > 0){
                $personas=1;
            }?>
            <script type="text/javascript" src="js/ajax.js"></script>
            <?
            switch ($tipo){
                    case 'ver':{
                            $ver=true;
                            break;
                    }
                    case 'cargar':{
                            $cargar=true;
                            break;
                    }
            }
            $url=$this->link.'?mod='.$this->modulo;
            $red=$url;
            if(!($ver)){
                $url.="&tarea=".$_GET['tarea'];
            }

            if($cargar){
                $url.='&id='.$_GET['id'];
            }
            $this->formulario->dibujar_tarea('PERSONA');
//            $this->datos_venta($ci,$im);	
            if($this->mensaje<>""){
                $this->formulario->dibujar_mensaje($this->mensaje);
            }
            ?>
            <table align=right border=0><tr>
            <?php if($this->verificar_permisos('ACCEDER')){	?>
            <td><a href="gestor.php?mod=venta&tarea=ACCEDER" title="LISTADO DE VENTA_SIMULACIONS"><img border="0" width="20" src="images/listado.png"></a></td>
            <?php } ?>
            </tr></table>
        <!--MaskedInput-->
        <style>
            .img-boton{
                margin-left: 2px; float: left;cursor: pointer;
            }
            .img-boton:hover{opacity: 0.7}
            .tablaReporte thead tr th{ padding: 0 10px;}
            
            .nav-paso{ float: left; }
            .nav-pasos{ width: 100%; margin: 0 auto; }
            .num-paso{
                width: 35px; height: 33px; color: #fff; line-height: 32px; margin-bottom: 8px; border-radius: 17px; font-size: 25px;
            }
            .estado-espera{ background-color: #727272; }
            .estado-activo{ background-color: #3066ff; }
            .estado-success{ background-color: #068400; }
            .box-input-read{background: #ededed; border: 1px solid #bfc4c9; float: left; font-size: 12px; height: 23px; line-height: 22px; padding: 0 4px; width: 140px; font-style: italic;}
            .fwbold{font-weight: bold;}
        </style>
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
        <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <script type="text/javascript" src="js/util.js"></script>
		<div id="Contenedor_NuevaSentencia">
                    <form id="frm_sentencia" name="frm_sentencia" action="#" method="POST" enctype="multipart/form-data">  
                        <input type="hidden" id="ticket" name="ticket" value="<?php echo Ticket::pedirTicket();?>">
                        <div class="nav-pasos" >
                            <div id="nav-paso-1" class="nav-paso" style="width: 50%">
                                <div class="num-paso estado-activo">1</div>
                                <div class="estado-activo">&nbsp;</div>
                            </div>
                            <div id="nav-paso-2" class="nav-paso" style="width: 50%">
                                <div class="num-paso estado-espera">2</div>
                                <div class="estado-espera">&nbsp;</div>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                        <?php if($tipo=="reserva"){ ?>
                        <input type="hidden" name="frm_reserva" value="1">
                        <?php }?>
                        <div class="cont-pasos">
                            <div class="box-paso" id="frm_paso1">
                                <div id="FormSent" style="width:90%;">
                                    <div class="Subtitulo">Datos</div>
                                    <div id="ContenedorSeleccion" style="position: relative;">
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
                                           <div id="CajaInput">
                                                <?php
                                                if($personas<>0)
                                                {
                                                ?>
                                                    <input name="res_int_id" id="res_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['ven_int_id']?>" size="2">
                                                    <input name="ven_int_id" id="ven_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['ven_int_id']?>" size="2">
                                                    <input name="int_nombre_persona"  readonly="true"
                                                           id="int_nombre_persona"  type="text" class="caja_texto" 
                                                        value="<?php echo trim($_POST['int_nombre_persona'])?>" 
                                                        size="40" >
                                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                                    <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                                    </a>
                                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;" data-url="gestor.php?mod=interno&tarea=ACCEDER&acc=buscar" href="javascript:void(0)">
                                                        <img src="images/b_search.png" border="0" title="BUSCAR" alt="BUSCAR">
                                                    </a>
                                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_interno();">
                                                    <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                                    </a>
                                                <?php
                                                }else{
                                                    echo 'No se le asigno ninguna personas, para poder cargar las personas.';
                                                }
                                                ?>
                                                   <input type="hidden" name="im" id="im"  value="<?php echo $im; ?>">
                                           </div>
                                            <?php  $conversor = new convertir(); ?>
                                        </div>
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta"><span class="flechas1">*</span>Co-Propietario</div>
                                           <div id="CajaInput">
                                                    <?php if($personas<>0){ ?>
                                                        <input name="ven_co_propietario" id="ven_co_propietario" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['ven_co_propietario']?>" size="2">
                                                        <input name="int_nombre_copropietario" <? if($_GET['change']=="ok"){ ?>readonly="readonly" <? } ?> id="int_nombre_copropietario"  type="text" class="caja_texto" value="<?php echo $_POST['int_nombre_copropietario']?>" size="40">
                                                        <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                                        <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                                        </a>
                                                        <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;" data-url="gestor.php?mod=interno&tarea=ACCEDER&acc=buscar&mt=set_valor_copropietario" href="javascript:void(0)">
                                                            <img src="images/b_search.png" border="0" title="BUSCAR" alt="BUSCAR">
                                                        </a>
                                                        <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_co_propietario();">
                                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                                        </a>
                                                        <?php
                                                    }else{
                                                        echo 'No se le asigno ning?na personas, para poder cargar las personas.';
                                                    }
                                                   ?>
                                           </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" ><span class="flechas1">*</span>Fecha</div>
                                           <div id="CajaInput">
                                               <?php FORMULARIO::cmp_fecha('ven_fecha');?>
                                                <!--<input class="caja_texto" name="ven_fecha" id="ven_fecha" size="12" value="<?php // if (isset($_POST['ven_fecha'])) echo $_POST['ven_fecha']; else echo date("d/m/Y"); ?>" type="text"><label id="lbl_periodo" ></label>-->
                                                <input type="hidden" id="ven_peri_id" value="" >
                                                <input type="hidden" id="tca_cambios" value="" >
                                           </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" ><span class="flechas1">*</span>Sucursal</div>
                                           <div id="CajaInput">
                                               <select name="ven_suc_id">
                                                   <option value="">-- Seleccione --</option>
                                                   <?php
                                                   $fun=new FUNCIONES();
                                                   $fun->combo("select suc_id as id, suc_nombre as nombre from ter_sucursal where suc_eliminado='no'", $_SESSION[suc_id]);
                                                   ?>
                                               </select>
                                               <!--<div class="read-input"><?php // echo FUNCIONES::atributo_bd_sql("select suc_nombre as campo from ter_sucursal, ad_usuario where usu_suc_id=suc_id and usu_id='$_SESSION[id]'");?></div>-->
                                           </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" ><span class="flechas1">*</span>Lugar de venta</div>
                                           <div id="CajaInput">
                                               <?php $paises=  FUNCIONES::lista_bd_sql("select * from ter_pais where pais_eliminado='No'");?>
                                                <div style="float: left;">
                                                    <select name="pais_id" id="pais_id" style="width: 150px;">
                                                        <?php foreach ($paises as $pais) {?>
                                                            <option value="<?php echo $pais->pais_id;?>"><?php echo $pais->pais_nombre;?></option>
                                                        <?php }?>
                                                    </select>
                                                    <select name="est_id" id="est_id" style="width: 150px;">

                                                    </select>
                                                    <select name="lug_id" id="lug_id" style="width: 150px;">

                                                    </select>
                                                </div>
                                                <div id="json_estados" hidden="">
                                                   <?php $estados =  FUNCIONES::lista_bd_sql("select * from ter_estado where est_eliminado='No'")?>
                                                   <?php foreach ($estados as $est) {
                                                        $est->est_nombre=  FUNCIONES::limpiar_cadena($est->est_nombre);
                                                    }
                                                    echo json_encode($estados);
                                                    ?>
                                                </div>
                                                <div id="json_lugares" hidden="">
                                                    <?php $lugares =  FUNCIONES::lista_bd_sql("select * from ter_lugar where lug_eliminado='No'")?>
                                                    <?php foreach ($lugares as $lug) {
                                                         $lug->lug_nombre=  FUNCIONES::limpiar_cadena($lug->lug_nombre);
                                                     }
                                                     echo json_encode($lugares);
                                                     ?>
                                                </div>
                                                <script>
                                                    $('#pais_id').change(function (){
                                                        var pais_id=$(this).val();
                                                       $('#est_id').children().remove();
                                                       $('#lug_id').children().remove();
                                                       var estados=JSON.parse(trim($('#json_estados').text()));
                                                       var options='';
                                                       for(var i=0;i<estados.length;i++){
                                                           var est=estados[i];
                                                           if(pais_id===est.est_pais_id){
                                                                options+='<option value="'+est.est_id+'">'+est.est_nombre+'</option>';
                                                           }
                                                       }
                                                       $('#est_id').append(options);
                                                       $('#est_id').trigger('change');
                                                       
                                                   });
                                                    $('#est_id').change(function (){
                                                        var est_id=$(this).val();
//                                                       $('#est_id').children().remove();
                                                       $('#lug_id').children().remove();
                                                       var lugares=JSON.parse(trim($('#json_lugares').text()));
                                                       var options='';
                                                       for(var i=0;i<lugares.length;i++){
                                                           var lug=lugares[i];
                                                           if(est_id===lug.lug_est_id){
                                                                options+='<option value="'+lug.lug_id+'">'+lug.lug_nombre+'</option>';
                                                           }
                                                       }
                                                       $('#lug_id').append(options);
                                                       
                                                   });
                                                   $('#pais_id').trigger('change');
                                                   $('#est_id').trigger('change');
                                                </script>
                                           </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >Vendedor</div>
                                            <div id="CajaInput">
                                                <?php if($tipo!="reserva"){?>
                                                    <select style="width:200px;" name="vendedor" class="caja_texto">
                                                         <option value="">Seleccione</option>
                                                         <?php $sql="select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre
                                                                    from vendedor 
                                                                    inner join interno on (vdo_int_id=int_id) 
                                                                    where vdo_estado='Habilitado' ";?>
                                                         <?php $vendedores1=  FUNCIONES::objetos_bd_sql($sql);?>
                                                         <?php for($i=0;$i<$vendedores1->get_num_registros();$i++){?>
                                                            <?php $objeto=$vendedores1->get_objeto();?>
                                                            <option value="<?php echo $objeto->id;?>"><?php echo $objeto->nombre?></option>
                                                            <?php $vendedores1->siguiente();?>
                                                         <?php }?>
                                                    </select>
                                                    <?php                                                                       
                                                }else{
                                                    $sql="select vdo_id as id,concat(int_nombre,' ',int_apellido) as nombre from vendedor inner join interno on (vdo_int_id=int_id) where vdo_estado='Habilitado' AND  vdo_id=".$_POST['vendedor'];
                                                    $vendedor=FUNCIONES::objeto_bd_sql($sql);
                                                    ?>
                                                        <select style="width:200px;" name="vendedor" class="caja_texto">
                                                            <option value="<?php echo $vendedor->id;?>"><?php echo $vendedor->nombre;?></option>
                                                        </select>
                                                    <?php
                                                }
                                                ?>
                                            </div>

                                        </div>

                                        <div style="position: absolute; left: 400px; top: 145px;">
                                            <div class="Etiqueta" style="min-width: 30px; width: 60px;">Observaci&oacute;n</div>
                                            <div id="CajaInput">
                                                <textarea name="ven_observacion" id="ven_observacion"></textarea>
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" ><span class="flechas1">*</span>Urbanizaci&oacute;n</div>
                                            <div id="CajaInput">
                                                <?php
                                                if($tipo!="reserva"){
                                                    if(isset($_GET['lote_venta']) && $_GET['lote_venta']!=''){
                                                        $sql="select urb_id as id, urb_nombre as nombre from lote l, zona z, urbanizacion u 
                                                                where l.lot_zon_id=z.zon_id and z.zon_urb_id=u.urb_id 
																and u.urb_eliminado='No'
																and l.lot_id='".$_GET['lote_venta']."';";
                                                        $urbanizacion=FUNCIONES::objeto_bd_sql($sql);
                                                        ?>
                                                            <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" >
                                                                <option value="<?php echo $urbanizacion->id;?>"><?php echo $urbanizacion->nombre;?></option>                                                                            
                                                            </select>
                                                        <?php  
                                                    } else{
                                                        ?>
                                                        <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" onchange="cargar_uv(this.value);">
                                                            <?php $urbs= FUNCIONES::objetos_bd_sql("select * from urbanizacion where 1 and urb_eliminado='No'");?>
                                                            <option value="">Seleccione</option>
                                                            <?php for($i=0;$i<$urbs->get_num_registros();$i++){?>
                                                                <?php $urb=$urbs->get_objeto();?>
                                                                <option value="<?php echo $urb->urb_id;?>" data-interes="<?php echo $urb->urb_interes_anual;?>"><?php echo $urb->urb_nombre;?></option>
                                                                <?php $urbs->siguiente();?>
                                                            <?php }?>
                                                        </select>
                                                        <?
                                                    }

                                                }else{
                                                    $sql="select urb_id as id,urb_nombre as nombre, urb_interes_anual from urbanizacion where urb_id='".$_POST['ven_urb_id']."' and urb_eliminado='No'";
                                                    $urbanizacion=FUNCIONES::objeto_bd_sql($sql);
                                                    ?>
                                                        <select style="width:200px;" name="ven_urb_id" id="ven_urb_id" class="caja_texto" >
                                                            <option value="<?php echo $urbanizacion->id;?>" data-interes="<?php echo $urbanizacion->urb_interes_anual;?>"><?php echo $urbanizacion->nombre;?></option>                                                                            
                                                        </select>
                                                    <?php  

                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" ><span class="flechas1">* </span>UV</div>
                                            <div id="CajaInput">
                                                <div id="uv">
                                                    <?php
                                                    if($tipo!="reserva"){
                                                        if(isset($_GET['lote_venta']) && $_GET['lote_venta']!=''){
                                                            $sql="select uv_id as id, uv_nombre as nombre from lote l, uv uv 
                                                                    where l.lot_uv_id=uv.uv_id and l.lot_id='".$_GET['lote_venta']."';";
                                                            $uv=FUNCIONES::objeto_bd_sql($sql);
                                                            ?>
                                                                <select style="width:200px;" name="ven_uv_id" id="ven_uv_id" class="caja_texto" >
                                                                    <option value="<?php echo $uv->id;?>">Uv Nro: <?php echo $uv->nombre;?></option>                                                                            
                                                                </select>
                                                        <?php  
                                                        } else{
                                                            ?>
                                                                <select style="width:200px;" name="ven_uv_id" class="caja_texto">
                                                                    <option value="">Seleccione</option>
                                                                    <?php 		
                                                                        if($_POST['ven_urb_id']<>"")
                                                                        {
                                                                            $fun=NEW FUNCIONES;		
                                                                            $fun->combo("select uv_id as id,uv_nombre as nombre from uv where uv_urb_id='".$_POST['ven_urb_id']."' ",$_POST['ven_uv_id']);				
                                                                        }
                                                                         ?>
                                                                </select>
                                                            <?php
                                                        }
                                                    }else{
                                                        $sql="select uv_id as id,uv_nombre as nombre from uv where uv_urb_id='".$_POST['ven_urb_id']."' and uv_id='".$_POST['ven_uv_id']."'";
                                                        $uv=FUNCIONES::objeto_bd_sql($sql);
                                                        ?>
                                                        <select style="width:200px;" name="ven_uv_id" class="caja_texto">
                                                            <option value="<?php echo $uv->id?>">Uv Nro: <?php echo $uv->nombre;?></option>                                                                            
                                                        </select>  
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                           </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" ><span class="flechas1">* </span>Manzano</div>
                                           <div id="CajaInput">
                                                <div id="manzano">
                                                    <?
                                                    if($tipo!="reserva"){
                                                        if(isset($_GET['lote_venta']) && $_GET['lote_venta']!=''){
                                                            $sql="select man_id as id,man_nro as nombre  from lote l, manzano m 
                                                                    where l.lot_man_id=m.man_id and l.lot_id='".$_GET['lote_venta']."';";
                                                            $mz=FUNCIONES::objeto_bd_sql($sql);
                                                            ?>
                                                                <select style="width:200px;" name="ven_man_id" id="ven_man_id" class="caja_texto" >
                                                                    <option value="<?php echo $mz->id;?>">Manzano Nro: <?php echo $mz->nombre;?></option>                                                                            
                                                                </select>
                                                        <?php  
                                                        } else{
                                                        ?>
                                                            <select style="width:200px;" name="ven_man_id" class="caja_texto" onchange="cargar_lote(this.value);">
                                                                <option value="">Seleccione</option>
                                                                <?php 		
                                                                     if($_POST['ven_urb_id']<>"")
                                                                     {
                                                                        $fun=NEW FUNCIONES;		
                                                                        $fun->combo("select man_id as id,man_nro as nombre from manzano where man_urb_id='".$_POST['ven_urb_id']."' ",$_POST['ven_man_id']);				
                                                                     }
                                                                     ?>
                                                            </select>
                                                        <?php
                                                        }
                                                    }else{
                                                        $sql="select man_id as id,man_nro as nombre from manzano where man_urb_id='".$_POST['ven_urb_id']."' and man_id=' ".$_POST['ven_man_id']."'";
                                                        $mz=FUNCIONES::objeto_bd_sql($sql);
                                                        ?>
                                                        <select style="width:200px;" name="ven_man_id" class="caja_texto" onchange="cargar_lote(this.value);">
                                                            <option value="<?php echo $mz->id;?>">Manzano Nro: <?php echo $mz->nombre;?></option>                                                                            
                                                        </select>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                           </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                           <div class="Etiqueta" ><span class="flechas1">* </span>Lote</div>
                                           <div id="CajaInput">
                                                <div id="lote">
                                                    <?php 
                                                    if($tipo!="reserva"){
                                                        if(isset($_GET['lote_venta']) && $_GET['lote_venta']!=''){
                                                            $sql="select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre 
                                                                    from lote inner join zona on (lot_zon_id=zon_id) inner join uv on (lot_uv_id=uv_id) where lot_id='".$_GET['lote_venta']."'";
                                                            $mz=FUNCIONES::objeto_bd_sql($sql);
                                                            ?>
                                                                <select style="width:200px;" name="ven_lot_id" id="ven_lot_id" class="caja_texto" >
                                                                    <option value="<?php echo $mz->id;?>">Uv Nro: <?php echo $mz->nombre;?></option>                                                                            
                                                                </select>
                                                                <script>
                                                                    cargar_datos($("#ven_lot_id option:selected").val());
                                                                </script>
                                                        <?php  
                                                        } else{
                                                        ?>
                                                            <select style="width:200px;" name="ven_lot_id" class="caja_texto">
                                                                <option value="">Seleccione</option>
                                                                <?php 		
                                                                     if($_POST['ven_man_id']<>""){
                                                                        $fun=NEW FUNCIONES;		
                                                                        $fun->combo("select lot_id as id,lot_nro as nombre from lote where lot_man_id='".$_POST['ven_man_id']."' ",$_POST['ven_lot_id']);				
                                                                     }
                                                                     ?>
                                                            </select>
                                                        <?php
                                                        }
                                                    }else{
                                                        $sql="select concat(lot_id,'-',lot_superficie,'-',zon_precio,'-',zon_cuota_inicial,'-',zon_moneda,'-',lot_tipo) as id,concat('Lote Nro: ',lot_nro,' (Zona ',zon_nombre,': ',zon_precio,' - UV:',uv_nombre,')') as nombre from lote inner join zona on (lot_zon_id=zon_id) inner join uv on (lot_uv_id=uv_id) where lot_id='".$_POST['ven_lot_id']."'";
                                                        //echo $sql;
                                                        $lote=FUNCIONES::objeto_bd_sql($sql);
                                                        ?>
                                                        <select style="width:200px;" name="ven_lot_id" id="ven_lot_id"class="caja_texto">
                                                            <option value="<?php echo $lote->id;?>"><?php echo $lote->nombre;?></option>
                                                        </select>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                           </div>
                                        </div>
                                        <div id="ContenedorDiv" >
                                            <div class="Etiqueta" ><span class="flechas1">*</span>Moneda</div>
                                            <div id="CajaInput">
                                                <select style="width:100px;" name="ven_moneda" class="caja_texto" id="ven_moneda" onchange="javascript:f_moneda();" >
                                                     <option value="1" <?php if($_POST['ven_moneda']=='1') echo 'selected="selected"'; ?>>Boliviano</option>
                                                     <option value="2" <?php if($_POST['ven_moneda']=='2') echo 'selected="selected"'; ?>>Dolar</option>
                                                </select>
                                            </div>                                                            
                                        </div>
                                        <div id="ContenedorDiv" >
                                            <input type="button" id="btn_siguiente" class="boton" value="Siguiente >>" onclick="frm_paso(2);">
                                        </div>
                                    </div>
                                </div>
                                
                                <style>
                                    .del_fespecial{
                                    cursor:pointer;
                                    }
                                    .del_fespecial_h{
                                        display: none;
                                    }
                                    .fsitalic{ font-style: italic;}
                                </style>
                            </div>
                            <div class="box-paso" id="frm_paso2">
                                <div id="FormSent" style="width:90%;">
                                    <div class="Subtitulo">Datos de Pago</div>
                                    <div id="ContenedorSeleccion" style="position: relative;">
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta fsitalic" >Cliente</div>
                                            <div id="CajaInput">
                                                <div class="read-input fsitalic" id="txt_cliente">&nbsp;</div>
                                            </div>
                                            <div class="Etiqueta" >Fecha</div>
                                            <div id="CajaInput">
                                                <div class="read-input fsitalic" id="txt_fecha">&nbsp;</div>
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >Vendedor</div>
                                            <div id="CajaInput">
                                                <div class="read-input fsitalic" id="txt_vendedor">&nbsp;</div>
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >Terreno</div>
                                            <div id="CajaInput">
                                                <div class="read-input fsitalic" id="txt_terreno">&nbsp;</div>
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >Moneda</div>
                                            <div id="CajaInput">
                                                <div class="read-input fsitalic" id="txt_moneda">&nbsp;</div>
                                            </div>
                                        </div>
                                        
                                        <div id="ContenedorDiv">
                                            <div id="seccion_sup_valor">
                                                <div class="Etiqueta" >Superficie</div>
                                                <div id="CajaInput">
                                                  <input readonly="true" type="text" name="superficie" id="superficie" size="8" value="" >
                                                </div>
                                                <div id="CajaInput">
                                                    <span id="simb_moneda_vm2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Valor m2&nbsp;&nbsp;&nbsp;</span>
                                                    <input type="text" name="valor" id="valor" size="8" value=""   onKeyUp="javascript:calcular_valor_terreno();">
                                                    <input type="hidden" name="valorhidden" id="valorhidden" data-moneda="" value="" >
                                                </div>

                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" id="simb_moneda_vt"><span class="flechas1">*</span>Valor del Terreno</div>
                                            <div id="CajaInput">
                                                  <input readonly="true" type="text" name="valor_terreno" id="valor_terreno" size="8" value="">
                                            </div>
                                            <div id="CajaInput">
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Descuento&nbsp;&nbsp;&nbsp;
                                                <input type="text" name="descuento" id="descuento" size="8" value="0" onKeyUp="javascript:calcular_monto();" autocomplete="off"><span id="simb_moneda_descuento"></span>
                                            </div>							  
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >Monto a Pagar</div>
                                            <div id="CajaInput">
                                                  <input readonly="true" type="text" name="monto" id="monto" size="8" value="">
                                            </div>
                                            <div id="CajaInput">
                                                &nbsp;&nbsp;&nbsp;
                                                Anticipo&nbsp;<input type="text" name="ven_anticipo" id="ven_anticipo" size="8" value="<?php echo $_POST['ven_anticipo']; ?>" autocomplete="off" onkeyup ="javascript:calcular_monto_efectivo();"><span id="simb_moneda_anticipo"></span>
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >Intercambio</div>
                                            <div id="CajaInput">
                                                <input type="text" name="ven_monto_intercambio" id="ven_monto_intercambio" size="8" value="" onkeyup="javascript:calcular_intercambio();" autocomplete="off" >
                                            </div>
                                        </div>
                                        <div id="ContenedorSeleccion" style="width: 100%; padding: 5px 0; margin-bottom: 10px; display: none;" class="cont_det_intercambio" >
                                            <div id="ContenedorDiv">
                                                <div class="Etiqueta" >&nbsp;</div>
                                                <div id="CajaInput">
                                                    <input type="text" name="det_inter_monto" id="det_inter_monto" size="8" value="" autocomplete="off" >
                                                </div>
                                                <div id="CajaInput">
                                                    <select name="det_inter_id" id="det_inter_id" >
                                                        <option value="">-- Seleccione --</option>
                                                        <?php 
                                                        $fun=new FUNCIONES();
                                                        $fun->combo("select inter_id as id, inter_nombre as nombre from ter_intercambio where inter_eliminado='no'", '')
                                                        ?>
                                                    </select>
                                                </div>
                                                <div id="CajaInput" style="margin-left: 5px; cursor: pointer; ">
                                                    <img src="images/btn_add_detalle.png" onclick="add_detalle_intercambio();">
                                                </div>
                                            </div>
                                            <div id="ContenedorDiv">
                                                <div class="Etiqueta" >&nbsp;</div>
                                                <div id="CajaInput">
                                                    <table id="tab_intercambios" class="tablaLista" cellspacing="0" cellpadding="0">
                                                        <thead>
                                                            <tr>
                                                                <th>Monto</th>
                                                                <th>Tipo</th>
                                                                <th class="tOpciones"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <td style="padding: 0 3px;font-size: 11px">
                                                                    <span id="txt_total_inter"class="fwbold"></span>
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td>&nbsp;</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" >Saldo Efectivo</div>
                                            <div id="CajaInput">
                                                <input readonly="true" type="text" name="ven_monto_efectivo" id="ven_monto_efectivo" size="8" value="" >
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv">
                                            <div class="Etiqueta" ><span class="flechas1">*</span>Tipo</div>
                                            <div class="read-input" id="txt_ven_tipo">&nbsp;</div>
                                            <input type="hidden" id="ven_tipo" name="ven_tipo" value="">
                                        </div>
                                        <div style="clear: both;"></div>
                                        <div id="plan_credito" style="display: none; color:#3a3a3a">
                                            <div class="Subtitulo">Datos Credito</div>
                                            <div id="ContenedorSeleccion">
                                                <div id="ContenedorDiv">
                                                    <div id="CajaInput" name="divComenzarEn" >
                                                        <span style="float: left; margin-top: 2px;">Interes Anual: &nbsp;</span>
                                                        <input type="text" name="interes_anual" id="interes_anual" size="8" value="8" >
                                                    </div>

                                                    <div id="CajaInput" name="divCuotaInicial" style="display: none;">
                                                        <span style="float: left; margin-top: 2px;">&nbsp;&nbsp;&nbsp;&nbsp;Cuota Inicial: &nbsp;</span>
                                                        <input type="text" name="cuota_inicial" id="cuota_inicial" size="8" value=""  onKeyPress="return ValidarNumero(event);">
                                                    </div>
                                                </div>
                                                <div id="ContenedorDiv">
                                                    <div id="CajaInput">
                                                        <span style="float: left; margin-top: 2px;">Definir Plan de Pagos por: &nbsp;</span>
                                                        <select  id="def_plan_efectivo" name="def_plan_efectivo" data-tipo="efectivo">
                                                            <option value="mp">Meses Plazo</option>
                                                            <option value="cm">Cuota Mensual</option>
                                                            <!--<option value="manual">Manual</option>-->
                                                        </select>
                                                    </div>
                                                </div>
                                                <div id="ContenedorDiv"  >
                                                    <div id="CajaInput">
                                                        <span style="float: left; margin-top: 2px; margin-right: 5px;" >Nro de Cuotas: </span>
                                                        <input type="text" name="meses_plazo" id="meses_plazo" size="8" value="" onKeyPress="return ValidarNumero(event);">
                                                    </div>
                                                    <div id="CajaInput" name="divCuotaMensual" >
                                                        <select  id="def_cuota" style="width: 100px; float: left; margin-top: 3px;">
                                                            <option value="dcuota">Monto Cuota</option>
                                                            <option value="dcapital">Monto Capital</option>
                                                        </select>
                                                    </div>
                                                    <div id="CajaInput" name="divCuotaMensual" >
                                                        <span style="float: left; margin-top: 2px; margin-right: 5px;">Monto Cuota: </span>
                                                        <input type="text" name="cuota_mensual" id="cuota_mensual" size="8" value="" onKeyPress="return ValidarNumero(event);">
                                                    </div>
                                                    <div id="CajaInput">
                                                        <span style="float: left; margin-top: 2px; margin-left: 15px; margin-right: 5px;">Fecha Pri. Cuota: </span>
                                                        <input class="caja_texto" name="fecha_pri_cuota" id="fecha_pri_cuota" size="12" value="<?php echo FUNCIONES::get_fecha_latina (FUNCIONES::sumar_dias(30,date("Y-m-d")));?>" type="text">
                                                        <script>
                                                            $("#fecha_pri_cuota").mask("99/99/9999");
                                                        </script>
                                                    </div>
                                                    <div id="CajaInput" name="divCuotaMensual" >
                                                        <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Rango: </span>
                                                        <select id="ven_rango" name="ven_rango">
                                                            <option value="1">Mensual</option>
                                                            <option value="2">Bimestral</option>
                                                            <option value="3">Trimestral</option>                                                            
                                                            <option value="4">Cuatrimestral</option>
                                                            <option value="6">Semestral</option>
                                                        </select>
                                                    </div>
                                                    <div id="CajaInput" name="divCuotaMensual" >
                                                        <span style="float: left; margin-top: 2px; margin-right: 5px;"> &nbsp;&nbsp; Frec.: </span>
                                                        <select id="ven_frecuencia" name="ven_frecuencia">
                                                            <option value="30_dias">Cada 30 dias</option>
                                                            <option value="dia_mes">Mantener el dia</option>
                                                        </select>
                                                    </div>
                                                    <div id="CajaInput">
                                                        <img id="ver_plan_efectivo" src="imagenes/generar.png" style='margin:0px 0px 0px 5px; cursor: pointer' onclick="javascript:ver_plan_pago();">
                                                    </div>
                                                    <div id="CajaInput">
                                                        <img id="add_cuota_efectivo"src="images/btn_add_detalle.png" style='margin-left: 5px; cursor: pointer' onclick="javascript:datos_fila('efectivo');">
                                                    </div>
                                                </div>
                                                <div id="ContenedorDiv"  >
                                                    
                                                </div>
                                                <div id="ContenedorDiv"  >
                                                    
                                                </div>
                                                
                                                <div style="clear: both"></div>
                                                <div class="ContenedorDiv" id="plan_manual_efectivo">
                                                    <table width="96%"   class="tablaReporte" id="tab_plan_efectivo" cellpadding="0" cellspacing="0">
                                                        <thead>
                                                            <tr>
                                                                <th>Nro. Cuota</th>
                                                                <th>Fecha de Pago</th>                                                            
                                                                <th>Mondeda</th>
                                                                <th>Interes</th>
                                                                <th>Capital</th>
                                                                <th>Monto a Pagar</th>
                                                                <th>Saldo</th>
                                                                <th></th>
                                                            </tr>							
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                        <tfoot>	
                                                            <tr>
                                                                <td>&nbsp;</td>
                                                                <td>&nbsp;</td>
                                                                <td>&nbsp;</td>
                                                                <td>&nbsp;</td>
                                                                <td>&nbsp;</td>
                                                                <td>&nbsp;</td>
                                                                <td>
                                                                    <input type="hidden" id="c_total_efectivo" value="0">
                                                                    <input type="hidden" id="pag_total_efectivo" value="0">
                                                                </td>
                                                                <td>&nbsp;</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="ContenedorDiv" >
                                            <br>
                                            <input type="button" id="btn_anterior" class="boton" value="<< Anterior" onclick="frm_paso(1);">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <script>
                                mask_decimal('#ven_anticipo',null);
                                function frm_paso(nro_paso){
                                    if(nro_paso===1){
                                        habilitar_formulario(1);
                                    }else if(nro_paso===2){
                                        var suc_id=$('#ven_suc_id option:selected').val();
                                        var fecha=$('#ven_fecha').val();
                                        var interno=document.frm_sentencia.ven_int_id.value;
                                        var moneda=$('#ven_moneda option:selected').val();//document.frm_sentencia.ven_moneda.value;
                                        var urbanizacion=document.frm_sentencia.ven_urb_id.options[document.frm_sentencia.ven_urb_id.selectedIndex].value;
                                        var manzano=document.frm_sentencia.ven_man_id.options[document.frm_sentencia.ven_man_id.selectedIndex].value;
                                        var lote=document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;
                                        console.log(fecha+' - '+interno+' - '+' - '+moneda+' - '+urbanizacion+' - '+manzano+' - '+lote  );
                                        if(fecha!=='' && interno!=='' &&  moneda!=='' && urbanizacion!=='' && manzano!=='' && lote!=='' && suc_id!=='' ){
                                            mostrar_ajax_load();
                                            $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                                ocultar_ajax_load();
                                                var dato = JSON.parse(respuesta);
                                                if (dato.response === "ok") {
                                                    habilitar_formulario(2);
                                                } else if (dato.response === "error") {
                                                    $.prompt(dato.mensaje);
                                                    return false;
                                                }
                                            });

                                        }else{
                                            $.prompt('Ingrese Correctamente los datos de la Venta');
                                        }
                                        return false;
                                    }
                                }
                                var _FECHA_PAGO='';
                                var _FECHA_VALOR='';
                                function habilitar_formulario(form){
                                    if(form===1){
                                        $('.box-paso').hide();
                                        $('#frm_paso1').show();
                                        $('#nav-paso-2 .estado-activo').each(function(){
                                            $(this).removeClass('estado-activo');
                                            $(this).addClass('estado-espera');
                                        });
                                        $('#nav-paso-1 .estado-success').each(function(){
                                            $(this).removeClass('estado-success');
                                            $(this).addClass('estado-activo');
                                        });
                                    }else if(form===2){
                                        _VEN_FECHA=$('#ven_fecha').val();
                                        
                                        $('#txt_fecha_pago').text(_FECHA_PAGO);
                                        $('#txt_fecha_valor').text(_FECHA_VALOR);
                                        $('.box-paso').hide();
                                        $('#frm_paso2').show();
                                        $('#nav-paso-2 .estado-espera').each(function(){
                                            $(this).removeClass('estado-espera');
                                            $(this).addClass('estado-activo');
                                        });
                                        $('#nav-paso-1 .estado-activo').each(function(){
                                            $(this).removeClass('estado-activo');
                                            $(this).addClass('estado-success');
                                        });
                                        $('#descuento').val(0);
                                        $('#cuota_inicial').val(0);
                                        $('#meses_plazo').val('');
                                        $('#cuota_mensual').val('');
                                        $('#tab_plan_efectivo tbody tr').remove();
                                        $('#c_total_efectivo').val('');
                                        $('#pag_total_efectivo').val('');
                                        $('#fecha_pri_cuota').val(fecha_latina(sumar_dias(fecha_mysql(_VEN_FECHA),30)));
                                        
                                        var txt_cliente=$('#int_nombre_persona').val();
                                        var txt_fecha=$('#ven_fecha').val();
                                        var txt_vendedor=$('select[name="vendedor"] option:selected').text();
                                        var txt_terreno=$('#ven_urb_id option:selected').text()+' - '+$('select[name="ven_man_id"] option:selected').text()+' - '+$('#ven_lot_id option:selected').text();
                                        var txt_moneda=$('#ven_moneda option:selected').text();
                                        $('#txt_cliente').text(txt_cliente);
                                        $('#txt_fecha').text(txt_fecha);
                                        $('#txt_vendedor').text(txt_vendedor);
                                        $('#txt_terreno').text(txt_terreno);
                                        $('#txt_moneda').text(txt_moneda);
                                        calcular_monto();

                                    }
                                }
                                habilitar_formulario(1);
                                mask_decimal('#det_inter_monto',null);
                                function add_detalle_intercambio(){
                                    var monto=$('#det_inter_monto').val()*1;
                                    var id=$('#det_inter_id option:selected').val()*1;
                                    var txt_intercambio=$('#det_inter_id option:selected').text();
                                    var monto_inter=$('#ven_monto_intercambio').val()*1;
                                    console.log(id+' - '+monto);
                                    var _total=sumar_intercambio();
                                    var _monto=monto+_total;
                                    if(id>0 && monto >0 && _monto<=monto_inter){
                                        var fila='';
                                        fila+='<tr>';
                                        fila+='      <td>';
                                        fila+='         <input type="hidden" name="intercambio_ids[]" class="intercambio_ids" value="'+id+'">';
                                        fila+='         <input type="hidden" name="intercambio_montos[]" class="intercambio_montos" value="'+monto+'">';
                                        fila+='         '+monto;
                                        fila+='      </td>';
                                        fila+='      <td>';
                                        fila+='         '+txt_intercambio;
                                        fila+='      </td>';
                                        fila+='      <td>';
                                        fila+='         <img src="images/retener.png" class="del_inter cpointer" onclick="delete_intercambio(this);">';
                                        fila+='      </td>';
                                        fila+='</tr>';
                                        $('#tab_intercambios tbody').append(fila);
    //                                                            var total=sumar_intercambio();
                                        $('#txt_total_inter').text(_monto);

                                        $('#det_inter_monto').val('');
                                        $('#det_inter_id option[value=""]').attr('selected','true');

                                    }else{
                                        $.prompt('Ingrese Correctamente el Monto de Intercambio');
                                        return false;
                                    }
                                }
                                function delete_intercambio(obj){
                                    $(obj).parent().parent().remove();
                                    var _monto=sumar_intercambio();
                                    $('#txt_total_inter').text(_monto);
                                }
                                function sumar_intercambio(){
                                    var montos = $('.intercambio_montos');
                                    var lon=$(montos).size();
                                    var sum=0;
                                    for(var i=0;i<lon;i++){
                                        sum+=$(montos[i]).val()*1;
                                    }
                                    return sum.toFixed(2)*1;
                                }

                                var mon_select=0;
                                mask_decimal('#interes_anual',null);

                                function cargar_datos(valor){
    //                                    alert(valor);
                                    var valor=$('#ven_lot_id option:selected').val();
                                    var cambios=$("#tca_cambios").val();
                                    if(cambios===""){
                                        return false;
                                    }
                                    if (typeof(valor) === "undefined") {
                                        return false;
                                    }                                        
                                    var datos = valor;
                                    var val = datos.split('-');
                                    document.frm_sentencia.valor_terreno.value=(parseFloat(val[1])*parseFloat(val[2])).toFixed(2);
                                    document.frm_sentencia.superficie.value=val[1];
                                    document.frm_sentencia.valor.value=val[2];
                                    document.frm_sentencia.valorhidden.value=val[2];
                                    document.frm_sentencia.ven_moneda.value=val[4];
                                    mon_select=val[4];
                                    $("#valorhidden").attr("data-moneda",val[4]);
                                    document.frm_sentencia.ven_tipo.value="Contado";                                        
                                    $('#ven_tipo').trigger('change');
    //                                    calcular_cuota();
                                    calcular_monto();
                                }

                                function calcular_descuento(){
                                    var vt=parseFloat(document.frm_sentencia.valor_terreno.value);
                                    var porc_desc=parseFloat(document.frm_sentencia.porc_descuento.value);
                                    var desc=vt*(porc_desc/100 );

                                    //var td=(vt*des)/100;
                                    document.frm_sentencia.descuento.value=desc.toFixed(2);
                                    calcular_monto();
                                }
                                function calcular_monto(){
                                    var vt=parseFloat(document.frm_sentencia.valor_terreno.value);
                                    var des=document.frm_sentencia.descuento.value;
                                    if(des===""){
                                        des=0;
                                    }
                                    //var td=(vt*des)/100;
                                    document.frm_sentencia.monto.value=(vt-des).toFixed(2);

                                    calcular_monto_efectivo();
                                }

                                var _ANT_INTERCAMBIO=$('#ven_monto_intercambio').val()*1;
                                function calcular_intercambio(){
                                    var intercambio=$('#ven_monto_intercambio').val()*1;
                                    if(_ANT_INTERCAMBIO!==intercambio){
                                        if(intercambio>0){
                                            $('.cont_det_intercambio').show();
                                            $('.cont_det_intercambio #tab_intercambios tbody tr').remove();
                                            $('#txt_total_inter').text('');
    //                                                            $('#tipo_intercambio').show();
                                        }else{
                                            $('.cont_det_intercambio').hide();
                                            $('.cont_det_intercambio #tab_intercambios tbody tr').remove();
                                            $('#txt_total_inter').text('');
    //                                                            $('#tipo_intercambio').hide();
                                        }
                                        _ANT_INTERCAMBIO=intercambio;
                                        calcular_monto_efectivo();
                                    }
                                }
                                function calcular_monto_efectivo(){
                                    var monto=$('#monto').val()*1;
                                    var anticipo=0;
                                    if($('#ven_anticipo').length){
                                        anticipo=$('#ven_anticipo').val()*1;
                                    }
                                    var intercambio=$('#ven_monto_intercambio').val()*1;
    //                                                        if(intercambio>0){
    //                                                            $('.cont_det_intercambio').show();
    //                                                            $('.cont_det_intercambio #tab_intercambios tbody tr').remove();
    ////                                                            $('#tipo_intercambio').show();
    //                                                        }else{
    //                                                            $('.cont_det_intercambio').hide();
    //                                                            $('.cont_det_intercambio #tab_intercambios tbody tr').remove();
    ////                                                            $('#tipo_intercambio').hide();
    //                                                        }

                                    console.log(monto+'-'+anticipo+'-'+intercambio);
                                    var efectivo=monto-anticipo-intercambio;
                                    $('#ven_monto_efectivo').val(efectivo.toFixed(2));
                                    $('#tab_plan_efectivo tbody tr').remove();
                                    $('#c_total_efectivo').val('');
                                    $('#pag_total_efectivo').val('');
                                    if(efectivo>0){//credigo
                                        var interes=$('#ven_urb_id option:selected').attr('data-interes');
                                        $('#interes_anual').val(interes);
                                        var sup=$('#superficie').val()*1;
                                        var vm2=$('#valor').val()*1;
    //                                                        var ci=(sup*vm2)*0.1;
                                        var ci=0;
                                        $('#cuota_inicial').val(ci.toFixed(2));
                                        $("#plan_credito").show();
                                        var tipo_pag=$('#ven_tipo_pago option:selected').val();
                                        if(tipo_pag==='Intercambio'){
                                            $("#plan_credito_inter").show();
                                        }else{
                                            $("#plan_credito_inter").hide();
                                        }
                                        $('#txt_ven_tipo').text('Credito');
                                        $('#ven_tipo').val('Credito');

                                    }else{// contado
                                        document.frm_sentencia.cuota_inicial.value="";
                                        document.frm_sentencia.meses_plazo.value="";                                            
                                        document.frm_sentencia.cuota_mensual.value="";
                                        $("#plan_credito").hide();
                                        $("#plan_credito_inter").hide();
                                        $('#tprueba tbody').remove();

                                        $('#txt_ven_tipo').text('Contado');
                                        $('#ven_tipo').val('Contado');
                                    }
                                }

                                function calcular_valor_terreno(){
                                    var sup=parseFloat(document.frm_sentencia.superficie.value);
                                    var val=document.frm_sentencia.valor.value;
                                    if(val===""){
                                        val=0;
                                    }
                                    var vt=sup*val;
                                    document.frm_sentencia.valor_terreno.value=vt.toFixed(2);
    //					calcular_cuota();
                                    calcular_monto();

                                }			

                                function cargar_uv(id){
                                    var valores="tarea=uv&urb="+id;			
                                    ejecutar_ajax('ajax.php','uv',valores,'POST');
                                }

                                function cargar_manzano(id,uv){
                                    //cargar_lote(0);					
                                    var valores="tarea=manzanos&urb="+id+"&uv="+uv;
                                    ejecutar_ajax('ajax.php','manzano',valores,'POST');
                                }

                                function cargar_lote(id,uv){
                                    var valores="tarea=lotes&man="+id+"&uv="+uv;
                                    ejecutar_ajax('ajax.php','lote',valores,'POST');
                                }

                                function obtener_valor_uv(){
                                    var axuUv = $('#ven_uv_id').val();
                                    var axuMan = $('#ven_man_id').val();
                                    cargar_lote(axuMan,axuUv);
                                }

                                function obtener_valor_manzano(){
                                        var auxUrb = $('#ven_urb_id').val();
                                        var auxUv = $('#ven_uv_id').val();

                                        cargar_manzano(auxUrb,auxUv);
                                }

                                function ver_plan_pago(){
                                    var tipo_venta = document.frm_sentencia.ven_tipo.value;
                                    if (tipo_venta === 'Credito') {
                                        var saldo_financiar=0;
                                        var ncuotas=0;
                                        var fecha_pri_cuota=0;
                                        var monto_cuota=0;


                                        var def=$('#def_plan_efectivo option:selected').val();
                                        if(def==='mp'){
                                            ncuotas = $('#meses_plazo').val();
                                            monto_cuota = '';
                                        }else if(def==='cm'){
                                            ncuotas = '';
                                            monto_cuota = $('#cuota_mensual').val();
                                        }
                                        var monto_efectivo =$('#ven_monto_efectivo').val();
    //                                                            var anticipo=0;
    //                                                            if($('#ven_anticipo').length){
    //                                                                anticipo=$('#ven_anticipo').val();
    //                                                            }                                            
                                        var cuota_inicial=$('#cuota_inicial').val();
                                        saldo_financiar=monto_efectivo-cuota_inicial;
                                        fecha_pri_cuota =$('#fecha_pri_cuota').val();
                                        var rango =$('#ven_rango option:selected').val();
                                        var frec =$('#ven_frecuencia option:selected').val();
    //                                                if(_MODALIDAD==='interes'){
                                        var interes = $('#interes_anual').val();
    //                                                }
                                        var fecha_pri_mysql=fecha_mysql(fecha_pri_cuota);
                                        var fecha_ini_mysql=fecha_mysql($('#ven_fecha').val());
                                        if(fecha_ini_mysql>fecha_pri_mysql ){
                                            $.prompt('-La fecha de venta no puede ser mayor a la fecha de primer Pago', {opacity: 0.8});
                                            return false;
                                        }
                                        if ((ncuotas*1 > 0 || monto_cuota*1 > 0) && monto_efectivo > +0  && fecha_pri_cuota !== '') {
                                            var moneda = $('#ven_moneda option:selected').val();//document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
                                            var par={};
                                            par.tarea='plan_pagos';
                                            par.saldo_financiar=saldo_financiar;
                                            par.monto_total=saldo_financiar;
                                            par.meses_plazo=ncuotas;
                                            par.ven_moneda=moneda;
                                            par.fecha_inicio=$('#ven_fecha').val();
                                            par.fecha_pri_cuota=fecha_pri_cuota;
                                            par.cuota_mensual=monto_cuota;
                                            par.interes=interes;
                                            par.rango=rango;
                                            par.frecuencia=frec;
                                            $.post('ajax.php',par,function(resp){
                                                abrir_popup(resp);
                                            });

                                        } else {
                                            $('#tprueba tbody').remove();
                                            $.prompt('-La Fecha no debe estar vacia.</br>-Los meses de plazo o la cuota mensual debe ser mayor a cero.', {opacity: 0.8});
                                        }
                                    } else {
                                        $.prompt('La venta es al contado, no necesita generar un plan de pagos.', {opacity: 0.8});
                                    }
                                }
                                var popup=null;
                                function abrir_popup(html){
                                    if(popup!==null){
                                        popup.close();
                                    }
                                    popup = window.open('about:blank','reportes','left=100,width=900,height=500,top=0,scrollbars=yes');
                                    var extra='';
                                    extra+='<html><head><title>Vista Previa</title><head>';
                                    extra+='<link href=css/estilos.css rel=stylesheet type=text/css />';
                                    extra+='</head> <body> <div id=imprimir> <div id=status> <p>';

                                    extra+='<a href=javascript:window.print();>Imprimir</a>  <a href=javascript:self.close();>Cerrar</a></td> </p> </div> </div> <center>';
                                    popup.document.write(extra);
                                    popup.document.write(html);
                                    popup.document.write('</center></body></html>');
                                    popup.document.close();

                                }

                                function obtener_tipo_cambio(moneda){
                                    var tcambios= JSON.parse($("#tca_cambios").val());
                                    for(var i=0;i<tcambios.length;i++){
                                        if(tcambios[i].id==moneda){
                                            return tcambios[i].id;
                                        }
                                    }
                                    return 1;
                                }

                                function actualizar_total(row,columna)
                                {
                                        var dato=$(row).parent().parent().parent().children().eq(columna).children().eq(0).attr('value');
                                        var datos=dato.split('?');
                                        var tpbs=parseFloat(document.frm_sentencia.tbs.value);
                                        var tpsus=parseFloat(document.frm_sentencia.tsus.value);
                                        document.frm_sentencia.tbs.value=parseFloat(roundNumber((tpbs-datos[0]),2));
                                        document.frm_sentencia.tsus.value=parseFloat(roundNumber((tpsus-datos[1]),2));

                                }

                                function remove(row){  	
                                   var cant =  $(row).parent().parent().parent().children().length;  				  
                                    if (cant > 1)  
                                        $(row).parent().parent().parent().remove();					
                                }

                                function addTableRow(id,valor){ 	
                                    $(id).append(valor);
                                }

                                function enviar_formulario(){
    //                                    console.log('aa');
                                    var fecha = document.getElementById('ven_fecha').value;                        
                                    var interno=document.frm_sentencia.ven_int_id.value;
                                    var tipo=document.frm_sentencia.ven_tipo.value;
                                    //var moneda=document.frm_sentencia.ven_moneda.options[document.frm_sentencia.ven_moneda.selectedIndex].value;
                                    var moneda=document.frm_sentencia.ven_moneda.value;
                                    var urbanizacion=document.frm_sentencia.ven_urb_id.options[document.frm_sentencia.ven_urb_id.selectedIndex].value;
                                    var manzano=document.frm_sentencia.ven_man_id.options[document.frm_sentencia.ven_man_id.selectedIndex].value;
                                    var lote=document.frm_sentencia.ven_lot_id.options[document.frm_sentencia.ven_lot_id.selectedIndex].value;
                                    var cuota_mensual=document.frm_sentencia.cuota_mensual.value;

                                    var valor_terreno=parseFloat(document.frm_sentencia.valor_terreno.value);
                                    var descuento=parseFloat(document.frm_sentencia.descuento.value);					

                                    if(interno!=='' && tipo!=='' && moneda!=='' && urbanizacion!=='' && manzano!=='' && lote!=='' && (descuento<valor_terreno)){
                                        var monto_e=$('#monto').val()*1 ;
                                        if(monto_e<0){
                                            $.prompt('El monto efectivo debe ser mayor o igual 0.');
                                            return false;
                                        }
                                        var intercambio=$('#ven_monto_intercambio').val()*1;
                                        if(intercambio>0){
                                            var sum_det_inter=sumar_intercambio();
                                            if(intercambio!==sum_det_inter){
                                                $.prompt('La Suma del detalle de intercambio debe ser igual al Monto de Intercambio.');
                                                return false;
                                            }

                                        }
    //                                        console.info($('#ven_tipo option:selected').val())
                                        if($('#ven_tipo').val()==='Credito'){
    //                                            var cuota_inicial=$('#cuota_inicial').val()*1;
    //                                            var reserva=0;
    //                                            if($('#ven_anticipo').length){
    //                                                reserva=$('#ven_anticipo').val()*1;
    //                                            }
                                            
                                            var prog_efectivo=monto_e;
                                            var def=$('#def_plan_efectivo option:selected').val();
                                            if(def==='mp'){
                                                var mp=$('#meses_plazo').val()*1;
                                                var fpc=$('#fecha_pri_cuota').val();
                                                
                                                var fecha_pri_mysql=fecha_mysql(fpc);
                                                var fecha_ini_mysql=fecha_mysql($('#ven_fecha').val());
                                                if(fecha_ini_mysql>fecha_pri_mysql ){
                                                    $.prompt('-La fecha de venta no puede ser mayor a la fecha de primer Pago', {opacity: 0.8});
                                                    return false;
                                                }
                                                if(!(mp>0 && fpc!=='')){
                                                    $.prompt('Revise los datos del credito efectivo:<br> - La meses plazo <br> - Fecha de la primera cuota ');
                                                    return false;
                                                }   
                                            }else if(def==='cm'){
                                                var cm=$('#cuota_mensual').val()*1;
                                                var fpc=$('#fecha_pri_cuota').val();
                                                
                                                var fecha_pri_mysql=fecha_mysql(fpc);
                                                var fecha_ini_mysql=fecha_mysql($('#ven_fecha').val());
                                                if(fecha_ini_mysql>fecha_pri_mysql ){
                                                    $.prompt('-La fecha de venta no puede ser mayor a la fecha de primer Pago', {opacity: 0.8});
                                                    return false;
                                                }
                                                if(!(cm>0 && fpc!=='')){
                                                    $.prompt('Revise los datos del credito efectivo:<br> - La cuota Mensual <br> - Fecha de la primera cuota ');
                                                    return false;
                                                }
                                            }else if(def==='manual'){
                                                var capital_total=$('#c_total_efectivo').val()*1;
                                                var anticipo=0;
                                                if($('#ven_anticipo').length){
                                                    anticipo=$('#ven_anticipo').val()*1;
                                                }
                                                var cuota_i=$('#cuota_inicial').val()*1;
                                                var saldo=prog_efectivo-anticipo-cuota_i;
                                                if(capital_total!==saldo){
                                                    $.prompt('en el plan de pagos manual del monto en efectivo falta definir mas cuotas para igualar al monto en efectivo de la venta');
                                                    return false;
                                                }
                                            }
                                        }
                                        /* ----------------------- monto efectivo ----------------------- */
                                        var fecha=$('#ven_fecha').val();
                                        if(fecha!==''){
                                            $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                                var dato = JSON.parse(respuesta);
                                                if (dato.response !== "ok") {
                                                    $.prompt(dato.mensaje);                                    

                                                }else{
//                                                            $.prompt('todo bien..');
                                                    document.frm_sentencia.submit();
                                                }
                                            });
                                        }else{
                                            $.prompt('La fecha del pago de la Venta no debe ser vacio.');
                                        }
                                        console.info('ok');

                                    }else{
                                        $.prompt('Para Guardar la Venta dede seleccionar los campos Persona, Tipo, Moneda, Urbanizaci?n, Manzano, Lote.</br>- El Descuento debe ser menos al Monto a Pagar',{ opacity: 0.8 });			   
                                    }
                                }

                                function verificar(id)
                                {
                                        var cant = $('#tprueba tbody').children().length;               
                                        var ban=true;
                                        if(cant > 0)
                                        {
                                                $('#tprueba tbody').children().each(function(){
                                                var dato=$(this).eq(0).children().eq(0).children().eq(0).attr('value');	
                                                var datos=dato.split('?');
                                                if(id===datos[2])
                                                {
                                                        ban=false;
                                                }

                                                }); 				
                                        }  
                                        return ban;				
                                }


                                function limpiar_meses_plazo(){
                                    //document.frm_sentencia.meses_plazo.value="";
                                    if(document.frm_sentencia.cuota_mensual.value===""){
                                        $('.meses_plazo').css("visibility","visible");
                                        document.frm_sentencia.meses_plazo.value="";						
                                        $('.mes_cuota').css("visibility","hidden");
                                        document.frm_sentencia.mes_cuota.value="";
                                    }else{
                                        $('.mes_cuota').css("visibility","visible");
                                        $('.meses_plazo').css("visibility","hidden");
                                        document.frm_sentencia.meses_plazo.value="";
                                    }
                                }

                                function limpiar_cuota_mensual(){
                                    document.frm_sentencia.cuota_mensual.value="";
                                }
                                
                                function set_valor_interno(data){
                                    document.frm_sentencia.ven_int_id.value = data.id;
                                    document.frm_sentencia.int_nombre_persona.value = data.nombre;
                                }

                                function reset_interno(){
                                    document.frm_sentencia.ven_int_id.value="";
                                    document.frm_sentencia.int_nombre_persona.value="";
                                }

                                function set_valor_copropietario (data){
                                    document.frm_sentencia.ven_co_propietario.value=data.id;
                                    document.frm_sentencia.int_nombre_copropietario.value=data.nombre;
                                }
                                function reset_co_propietario(){
                                    document.frm_sentencia.ven_co_propietario.value="";
                                    document.frm_sentencia.int_nombre_copropietario.value="";
                                }
                                mask_decimal('#ven_monto_intercambio',null)

                                $('#def_plan_efectivo').change(function(){
                                    var tipo='efectivo';

                                    var def=$(this).val();
                                    if(def==='mp'){
                                        $('#meses_plazo').parent().show();
                                        $('#cuota_mensual').parent().hide();
                                        $('#cuota_interes').parent().hide();
                                        $('#ver_plan_efectivo').show();
                                        $('#add_cuota_efectivo').hide();
                                        $('#fecha_pri_cuota').prev('span').text('Fecha Pri. Cuota: ');
                                        $('#plan_manual_efectivo').hide();
                                        $('#def_cuota').parent().hide();

                                    }else if(def==='cm'){
                                        $('#meses_plazo').parent().hide();
                                        $('#cuota_mensual').parent().show();
                                        $('#cuota_interes').parent().hide();
                                        $('#ver_plan_efectivo').show();
                                        $('#add_cuota_efectivo').hide();
                                        $('#cuota_mensual').prev('span').text('Monto Cuota: ');
                                        $('#cuota_mensual').prev('span').show();
                                        $('#fecha_pri_cuota').prev('span').text('Fecha Pri. Cuota: ');
                                        $('#plan_manual_efectivo').hide();
                                        $('#def_cuota').parent().hide();
                                    }else if(def==='manual'){
                                        $('#meses_plazo').parent().hide();
                                        $('#cuota_mensual').parent().show();
                                        $('#cuota_interes').parent().show();
                                        $('#ver_plan_efectivo').hide();
                                        $('#add_cuota_efectivo').show();
    //                                                            $('#cuota_mensual').prev('span').text('Capital Cuota: ');
                                        $('#cuota_mensual').prev('span').hide();
                                        $('#fecha_pri_cuota').prev('span').text('Fecha Programada: ');
                                        $('#plan_manual_efectivo').show();
                                        $('#def_cuota').parent().show();
                                    }
                                });
                                $('#def_plan_efectivo').trigger('change');

                                $('#cuota_inicial,#interes_anual').change(function(){
                                    var def=$('#def_plan_efectivo option:selected').val();
                                    if(def==='manual'){
                                        limpiar_plan_manual();
                                    }
                                });

                                function limpiar_plan_manual(){
                                    $('#tab_plan_efectivo tbody tr').remove();
                                        $('#c_total_efectivo').val('');
                                        $('#pag_total_efectivo').val('');
                                }

                                function obtener_ultima_fecha(){
                                    var det_plan=$('.det_plan_efectivo');
                                    if(det_plan.length){
                                        var ult_det=$(det_plan).last();
                                        var fila=JSON.parse($(ult_det).val());
                                        console.log(fecha_mysql(fila.fecha));
                                        return fecha_mysql(fila.fecha);
                                    }else{
                                        console.log(fecha_mysql($('#ven_fecha').val()));
                                        return fecha_mysql($('#ven_fecha').val());
                                    }
                                }

                                function datos_fila(){
                                    var moneda=2;
                                    var txt_moneda='Dolares';


                                    var ci=$('#cuota_inicial').val();
                                    var anticipo=0;
                                    if($('#ven_anticipo').length){
                                        anticipo=$('#ven_anticipo').val();
                                    }
                                    var intercambio=$('#ven_monto_intercambio').val();
                                    var efectivo=$('#monto').val();
                                    var monto_financiar=efectivo-ci-anticipo-intercambio;

                                    var fecha = $('#fecha_pri_cuota').val();
                                    var n_fecha=fecha_mysql(fecha);
                                    //CALCULAR INTERES
                                    var u_fecha=obtener_ultima_fecha();

                                    console.log(n_fecha+'>'+u_fecha);
                                    if(n_fecha<=u_fecha){
                                        $.prompt('Ingrese una Fecha mayor a la fecha de la ultima cuota o a la fecha de la venta');
                                        return false;
                                    }

                                    var capital_total=$("#c_total_efectivo").val()*1;
                                    var saldo_final=monto_financiar-capital_total;
                                    var interes_anual=$('#interes_anual').val();
                                    var dias=diferencia_dias(u_fecha,n_fecha);
                                    var interes_dia=(interes_anual/360)/100;

                                    var interes= ((dias*interes_dia)*saldo_final).toFixed(2)*1;//$('#cuota_interes').val()*1;calcular
                                    console.log(dias+'*'+interes_dia+'*'+saldo_final);
                                    var monto_pagar=($('#cuota_mensual').val()*1).toFixed(2)*1;
                                    if(monto_pagar<=0){
                                        $.prompt('Ingrese un Monto');
                                        return false;
                                    }
                                    var capital= monto_pagar;
                                    var def_cuota=$('#def_cuota option:selected').val();
                                    if(def_cuota==='dcuota'){
                                        capital=(monto_pagar-interes).toFixed(2)*1;
                                        if(capital<=0){
                                            $.prompt('El monto de la Cuota Ingresada no cubre el interes a la Fecha ');
                                            return false;
                                        }
                                    }
    //                                                        var capital= $('#cuota_mensual').val()*1;
                                    var tab='#tab_plan_efectivo';

                                    if ((monto_financiar !== '' && parseInt(monto_financiar) > 0) && moneda !== '' && fecha !== '' && (interes !== '' || parseInt(interes) >= 0) && (capital !== '' || parseInt(capital) > 0)){
                                        var saldo_hidden = (monto_financiar - (capital_total+capital*1)).toFixed(2)*1;
                                        var det_plan={fecha:fecha,interes:interes,capital:capital,saldo:saldo_hidden};

                                        if(saldo_hidden>=0){
                                            var montopagar = (interes + capital*1).toFixed(2);
                                            var nro=$(tab+' tbody tr').size();
                                            $(tab+" .del_fespecial").attr('class','del_fespecial_h');                       
                                            var txt_fila='';
                                            txt_fila+='<tr>';
                                            txt_fila+='     <td>';
                                            txt_fila+='         <input class="det_plan_efectivo" name="det_plan_efectivo[]" type="hidden" value=\'' + JSON.stringify(det_plan)+ '\'>' ;
                                            txt_fila+=          (nro*1+1 )+ '&nbsp;';
                                            txt_fila+='     </td>';                        
                                            txt_fila+='     <td>' + fecha + '</td>';
                                            txt_fila+='     <td>' + txt_moneda + '</td>';
                                            txt_fila+='     <td>' + interes+ '</td>';
                                            txt_fila+='     <td>' + capital + '</td>';
                                            txt_fila+='     <td>' + montopagar + '</td>';
                                            txt_fila+='     <td>' + saldo_hidden + '</td>';

                                            txt_fila+='     <td><img data-tipo="efectivo" src="images/b_drop.png" class="del_fespecial"></td>';
                                            txt_fila+='</tr>';                        
                                            $(tab+' tbody').append(txt_fila);
                                            calcular_monto_capital();                                                                                                        
                                            limpiar();
                                        }else{
                                            $.prompt('- El capital a ingresar es sobrepasa el monto acordado', {opacity: 0.8});
                                        }
                                    }else{
                                        $.prompt('- Ingrese Fecha <br>-Ingrese Interes a Pagar<br>-Ingrese Capital a Pagar', {opacity: 0.8});
                                    }
                                }

                                function limpiar(tipo){

                                        $("#cuota_interes").val("");
                                        $("#cuota_mensual").val("");
                                        $("#cuota_mensual").focus();
                                        var fecha_act=$("#fecha_pri_cuota").val();
                                        var nfecha=siguiente_mes(fecha_mysql(fecha_act));
                                        $("#fecha_pri_cuota").val(fecha_latina(nfecha));

                                }

                                function calcular_monto_capital(){
                                    var tab='#tab_plan_efectivo';
                                    var filas =$(tab+" tbody tr");
                                    var tcapital=0;
                                    var tmontopagar=0;
                                    for(var i=0;i<filas.size();i++){
                                        var cols=$(filas[i]).children();
                                        var capital=$(cols[4]).text();                                                
                                        var monto_pagar=$(cols[5]).text();                                                
                                        tcapital+=capital*1;
                                        tmontopagar+=monto_pagar*1;
                                    }
                                    $("#c_total_efectivo").val(tcapital);
                                    $("#pag_total_efectivo").val(tmontopagar);
                                }

                                $(".del_fespecial").live('click',function (){
                                    $(this).parent().parent().remove();
                                    var tipo=$(this).attr('data-tipo');
                                    var tab='#tab_plan_'+tipo;
                                    var filas =$(tab+" tbody tr");
                                    $(filas[filas.size()-1]).find('img').attr('class','del_fespecial');
                                    calcular_monto_capital(tipo);
                                });

                                fecha_sel = "";
                                function obtener_periodo() {
                                    var fecha = $('#ven_fecha').val();
                                    if (fecha !== fecha_sel) {
                                        mostrar_ajax_load();
                                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: fecha}, function(respuesta) {
                                            ocultar_ajax_load();
                                            var dato = JSON.parse(respuesta);
                                            if (dato.response === "ok") {
                                                $('#ven_peri_id').val(dato.id);
                                                $('#tca_cambios').val(JSON.stringify(dato.cambios));
                                                $('#lbl_periodo').text(dato.descripcion);
                                                $('#lbl_periodo').css('color', '#0072b0');
                                                if($("#ven_anticipo").length){
                                                    calcular_reserva();
                                                }
                                                cargar_datos($("#ven_lot_id option:selected").val());
    //                                                                    calcular_valor_terreno();
    //                                                                    calcular_monto();

    //                                                                    calcular_cuota();


                                            } else if (dato.response === "error") {
                                                $('#ven_peri_id').val("");
                                                $('#tca_cambios').val(dato.cambios);
                                                $('#lbl_periodo').text(dato.mensaje);
                                                $('#lbl_periodo').css('color', '#ff0000');

                                                $("#superficie").val("##");
                                                $("#valor").val("##");
                                                $("#valor_terreno").val("##");
                                                if($("#ven_anticipo").length){
                                                    $("#ven_anticipo").val("##");
                                                }
                                                $("#monto").val("##");
                                                mon_select=0;
                                            }
                                            fecha_sel = fecha;

                                        });
                                    }
                                }
                                $("#ven_fecha").focusout(function (){                                                                    
                                    obtener_periodo();                                                        
                                });
                                obtener_periodo();

                                function calcular_reserva(){
//                                    var anticipos=$("#res_anticipos").val();
//                                    var cambios=$("#tca_cambios").val();                                    
//                                    var moneda=$("#ven_moneda option:selected").val();                                                                                                                
////                                    var janticipos=JSON.parse(anticipos);
//                                    var jcambios=JSON.parse(cambios);                
//                                    var sum_anticipo=0;
//                                    for(var i = 0; i < janticipos.length; i++ )  {                                                            
//                                        if(janticipos[i].moneda===moneda){
//                                            sum_anticipo=sum_anticipo+(janticipos[i].monto*1);
//    //                                                                alert(sum_anticipo+" true");
//                                        }else{
//                                            sum_anticipo=sum_anticipo+convertir_monto(moneda,janticipos[i].moneda,janticipos[i].monto,jcambios);
//    //                                                                alert(sum_anticipo+" false");
//                                        }
//
//                                    }
//                                    $("#ven_anticipo").val(sum_anticipo.toFixed(2));
                                }


                                function convertir_monto(moneda,moneda_monto, monto,jcambios){
                                    var valor_base=monto*valor(moneda_monto,jcambios);
    //                                                        alert(valor_base);
    //                                                        alert( valor_base/valor(moneda,jcambios));
                                    return valor_base/valor(moneda,jcambios);

                                }

                                function valor(moneda, jcambios){
                                    for(var i=0;i<jcambios.length;i++){
                                        if(moneda===jcambios[i].id){
                                            return jcambios[i].val*1;
                                        }                                                            
                                    }
                                    return 1;
                                }

                                function f_moneda(){

                                    var moneda=$("#ven_moneda option:selected").val();

                                    var cambios=$("#tca_cambios").val();
                                    if(cambios===""){
                                        return false;
                                    }
                                    var jcambios=JSON.parse(cambios);

                                    var superficie=$("#superficie").val();
                                    if(superficie==="##"){
                                        superficie=0;                                                            
                                    }else{
                                        superficie=superficie*1;
                                    }
                                    var valor=$("#valor").val();
                                    if(valor==="##"){
                                        valor=0;
                                    }else{
                                        valor=valor*1;
                                    }
                                    var conv_superficie=superficie;//convertir_monto(moneda,mon_select,superficie,jcambios);
                                    var conv_valor=convertir_monto(moneda,mon_select,valor,jcambios);
    //                                                        document.frm_sentencia.superficie.value=conv_superficie.toFixed(2);
                                    document.frm_sentencia.valor.value=conv_valor.toFixed(2);                                                        
                                    document.frm_sentencia.valor_terreno.value=(conv_valor*conv_superficie).toFixed(2);
                                    calcular_monto();
                                    if($("#ven_anticipo").length){
                                        calcular_reserva()
    //                                                        var anticipo=$("#ven_anticipo").val();
    //                                                        if(anticipo==="##"){
    //                                                            anticipo=0;
    //                                                        }else{
    //                                                            anticipo=anticipo*1;
    //                                                        }
    //                                                        var conv_anticipo=convertir_monto(moneda,mon_select,anticipo,jcambios);
    //                                                        document.frm_sentencia.ven_anticipo.value=conv_anticipo.toFixed(2);
                                    }
                                    mon_select=moneda;
                                }                                                    

                                function f_tipo(){
                                    var tipo=document.frm_sentencia.ven_tipo.options[document.frm_sentencia.ven_tipo.selectedIndex].value;
                                    if(tipo==='Contado'){
                                        document.frm_sentencia.cuota_inicial.value="";
                                        document.frm_sentencia.meses_plazo.value="";                                            
                                        document.frm_sentencia.cuota_mensual.value="";                                            
                                        document.frm_sentencia.descuento.value=0;                                                            
                                        $("#plan_credito").hide();
                                        $("#plan_credito_inter").hide();
                                        $('#tprueba tbody').remove();
                                    }else{
                                        if(tipo==='Credito'){
                                            var interes=$('#ven_urb_id option:selected').attr('data-interes');
                                            $('#interes_anual').val(interes);
                                            var sup=$('#superficie').val()*1;
                                            var vm2=$('#valor').val()*1;
                                            var ci=0;
    //                                                            var ci=(sup*vm2)*0.1;
                                            $('#cuota_inicial').val(ci.toFixed(2));
                                            $("#plan_credito").show();
                                            var tipo_pag=$('#ven_tipo_pago option:selected').val();
                                            if(tipo_pag==='Intercambio'){
                                                $("#plan_credito_inter").show();
                                            }else{
                                                $("#plan_credito_inter").hide();
                                            }
                                        }                                                                
                                    }
                                }

                                function f_tipo_cancelacion(){
                                    var tipo_pag =document.frm_sentencia.ven_tipo_pago.options[document.frm_sentencia.ven_tipo_pago.selectedIndex].value;
                                    if(tipo_pag==='Normal'){
                                        document.frm_sentencia.monto_intercambio.value="";
                                        document.frm_sentencia.monto_efectivo.value=$("#monto").val();
                                        $(".pago_intercambio").hide();
                                        var tipo=$('#ven_tipo option:selected').val();
                                        if(tipo==='Contado'){
                                            $("#plan_credito").hide();
                                            $("#plan_credito_inter").hide();
                                        }else{// credito                                                                    
                                            $("#plan_credito").show();
                                            $("#plan_credito_inter").hide();
                                        }
                                    }else{
                                        if(tipo_pag==='Intercambio'){
                                            document.frm_sentencia.monto_intercambio.value="";
                                            document.frm_sentencia.monto_efectivo.value=$("#monto").val();;
                                            $(".pago_intercambio").show();
                                            var tipo=$('#ven_tipo option:selected').val();
                                            if(tipo==='Contado'){
                                                $("#plan_credito").hide();
                                                $("#plan_credito_inter").hide();
                                            }else{// credito                                                                    
                                                $("#plan_credito").show();
                                                $("#plan_credito_inter").show();
                                            }
                                        }                                                                
                                    }
                                }
                                cargar_datos($("#ven_lot_id option:selected").val());
                            </script>
                    </form>
		</div>
        <?php // if($tipo!="reserva"){?>
                    
        <?php // }?>
                    
        
        <script>
			jQuery(function($){
			   $("#ven_fecha").mask("99/99/9999");
			   $("#ven_fecha_1pago").mask("99/99/9999");  
			});
		</script>
		<?php
	}
	
	
    // CAMBIAR LOTE
    
    
        
}
?>