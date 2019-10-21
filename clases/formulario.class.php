<?php

class FORMULARIO
{
	var $titulo;

	function FORMULARIO()
	{

	} 

	function set_titulo($titulo)
	{
		$this->titulo=$titulo;
	}

	function dibujar_cabecera()
	{
		?>
            	<div class="fila_formulario_cabecera"> <?PHP echo $this->titulo; ?></div>
		<?PHP
	}

	function dibujar_tarea()
	{
		?>
            	<div class="fila_formulario_cabecera"> <?PHP echo $_GET['tarea'].' '.$this->titulo; ?></div>
		<?PHP
	}
	
	function dibujar_titulo($titulo)
	{
		?>
            	<div class="fila_formulario_cabecera"> <?PHP echo $titulo; ?></div>
		<?PHP
	}

	function ventana_volver($mensaje,$link,$titulo="",$tipo="")
	{
		$this->dibujar_cabecera();

		?>
		<div>
		<br>	
		<table class="tablaLista" cellpadding="0" cellspacing="0">
			<thead>

				<tr bgcolor="#DEDEDE">

					<th >

						<?php

							//echo $_GET['tarea'].' '.$this->titulo;

							echo $_GET['tarea'];

						?>

					</th>

				</tr>

			</thead>

		</table>
		<?php
		$this->mensaje($tipo,$mensaje);
		?>
		<br>
		<center style="clear:both;">
		<br>
		<table>
		<tr>
		<?php
		$opciones = explode("Ø",$link);
		$titulos = explode("Ø",$titulo);
		$i=0;
		
		foreach($opciones as $op)
		{	
			if(!($titulos[$i] <> ""))
			{
				$titulo="Volver";
			}
			else
			{
				$titulo=$titulos[$i];
			}
			$i++;
			?>
			<td>
			<form id="form_eliminacion" action="<?php echo $op;?>" method="POST" enctype="multipart/form-data">

					<input type="submit" value="<?echo $titulo;?>" class="botongrande" style="clear:both;"/>

			</form>
			</td>
			
			<?php
		}
		?>
		</tr>
		</table>
		</center>
		</div>
		<?php

	}
	
	function dibujar_mensaje($mensaje,$link="",$titulo="",$tipo="",$show_btn=true){
		
		
		$this->mensaje($tipo,$mensaje);
		?>
		<br>
		<center style="clear:both;">
		<br>
		<table>
		<tr>
		<?php
		$opciones = explode("Ø",$link);
		$titulos = explode("Ø",$titulo);
		$i=0;
		
		foreach($opciones as $op)
		{	
			if(!($titulos[$i] <> ""))
			{
				$titulo="Volver";
			}
			else
			{
				$titulo=$titulos[$i];
			}
			$i++;
			?>
			<td>
                        <?php if($show_btn){?>                        
			<form id="form_eliminacion" action="<?php echo $op;?>" method="POST" enctype="multipart/form-data">                            
                            <input type="submit" value="<?echo $titulo;?>" class="botongrande" style="clear:both;"/>                            
			</form>
                        <?php }?>
			</td>
        <?php
		}
	}

	function ventana_confirmacion($mensaje,$link,$nombre_id,$tipo="")
	{
		$this->dibujar_cabecera();
		?>
		<div>
		<br>	
		<table class="tablaLista" cellpadding="0" cellspacing="0">
			<thead>
				<tr bgcolor="#DEDEDE">
					<th >
						<?php
							//echo $_GET['tarea'].' '.$this->titulo;
							echo $_GET['tarea'];
						?>
					</th>
				</tr>
			</thead>
		</table>
		<?php
		$this->mensaje($tipo,$mensaje);
		?>
		<br><center style="clear:both; float:none;">
		<form id="form_eliminacion" name="form_eliminacion" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">

									<input type="hidden" name="<?php echo $nombre_id; ?>" value="<?php echo $_GET['id'];?>">

									<input type="submit" value="Si" class="boton">

									<input type="button" value="Cancelar" class="boton" onclick="document.form_eliminacion.<?php echo $nombre_id; ?>.value=''; document.form_eliminacion.submit();">

								</form>
					</center>
			</div>
		<?php
	}
	
	function mensaje($tipo,$mensaje)
	{
		if(!($tipo<>""))
			$tipo='Informacion';
		
		?>
		<div class="ancho100">
			<div class="ms<?php echo $tipo; ?> limpiar"><?php echo $mensaje; ?></div>
		</div>
		<?php
	}

	public static function cmp_fecha($name,$value=''){
            if($value==''){
               $value=date('d/m/Y'); 
            }
            
            $fmod_usu_ids=  FUNCIONES::ad_parametro('par_fmod_usu_ids');
            $usup=  explode(',', $fmod_usu_ids); //array('arturo','gabriel','gsoto','rangelo','jvillarroel','lpedraza','gvargas','elopez','mpedraza','admin');
            $edit_fecha=  FUNCIONES::ad_parametro('par_modificar_fecha');
//            $edit_fecha=false;
            ?>
                <?php if($edit_fecha || in_array($_SESSION[id], $usup)){?>
                    <input class="caja_texto" name="<?php echo $name;?>" id="<?php echo $name;?>" size="20" value="<?php echo $value;?>" type="text">
                <?php }else{?>
                    <input name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo $value;?>" type="hidden">
                    <div class="read-input" id="txt_cmp_fecha" style="min-width: 113px"><?php echo $value;?></div>
                <?php } ?>
                    
            <?php
	}
        
        
        public static function frm_pago($params=null) {
            $params=(object)$params;
            ?>
                <script src="js/chosen.jquery.min.js"></script>
                <link href="css/chosen.min.css" rel="stylesheet"/>
                <input type="hidden" id="input-cmp-fecha" value="<?php echo $params->cmp_fecha?>">
                <input type="hidden" id="input-cmp-monto" value="<?php echo $params->cmp_monto?>">
                <!--<input type="hidden" id="input-moneda" value="<?php // echo $params->moneda;?>">-->
                <input type="hidden" id="input-cmp-moneda" value="<?php echo $params->cmp_moneda;?>">
                
                <input type="hidden" id="tipo_flujo" value="<?php echo $params->tipo_flujo?$params->tipo_flujo:'ingreso';?>">
                <input id="cambios" type="hidden" value="">
            <style>
                .box-fpago{float: left; margin-left: 5px; color: #3a3a3a; }
                #btn-add-det-fpago{ cursor: pointer; float: left;margin-top: 8px;}
                .img-del-fpag{cursor: pointer;}
            </style>
            <?php
            $usu_id=$_SESSION['id'];
            // $list_act_disp=  FUNCIONES::objetos_bd_sql("select con_cajero_detalle.* from con_cajero_detalle, con_cajero where cjadet_usu_id='$usu_id' and cja_estado='1' and cja_usu_id=cjadet_usu_id and cjadet_pago='1'");
			$list_act_disp=  FUNCIONES::objetos_bd_sql("select con_cajero_detalle.* from con_cajero_detalle, con_cajero where cjadet_usu_id='$usu_id' and cja_estado='1' and cja_usu_id=cjadet_usu_id");
            $cuentas_act_disp=array();
            $ges_id=$_SESSION['ges_id'];                

            for($i=0;$i<$list_act_disp->get_num_registros();$i++){
                $_det=$list_act_disp->get_objeto();
                $sql="select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$_det->cjadet_cue_id'";                    
                $cuenta=  FUNCIONES::objeto_bd_sql($sql);
                $_cu=new stdClass();
                $_cu->id=$cuenta->cue_id;
                $_cu->descripcion=$cuenta->cue_descripcion;
                $_cu->moneda=$cuenta->cue_mon_id;
                $cuentas_act_disp[]=$_cu;
                $list_act_disp->siguiente();
            } ?>
            <?php if (count($cuentas_act_disp)>0){?>
            <div style="float: left;" id="div-box-pagos">
                <div class="box-fpago">
                    <span>Cuenta Activo</span>
                    <div>
                        <select id="fpag_cue_id" style="min-width: 150px;margin-top: 3px;">
                            <option value="0" data-moneda="0">Seleccione</option>
                            <?php foreach($cuentas_act_disp as $cta){ ?>
                            <option value="<?php echo $cta->id?>" data-moneda="<?php echo $cta->moneda;?>"><?php echo $cta->descripcion?></option>
                            <?php }?>
                        </select>
                    </div>
                </div>
                <div class="box-fpago">
                    <span>Monto</span>
                    <div>
                        <input type="text" id="fpag_monto" value="" style="width: 60px;float: left;" autocomplete="off"> <span id="txt_moneda" style="margin: 4px 0 0 3px ;float: left;width: 45px;"></span>
                        <div style=" clear: both; float: left;">
                            <div style="float: left;"><b>Saldo Bs. &nbsp;:</b>&nbsp;&nbsp;<span id="txt-saldo-bs"></span></div><img id="copy-bs" width="13" style="float: left; cursor: pointer; margin: 1px 0 0 3px;" src="images/copy.png"> <br>
                            <div style="float: left;"><b>Saldo $us.:</b>&nbsp;&nbsp;<span id="txt-saldo-usd"></span></div><img id="copy-usd" width="13" style="float: left; cursor: pointer; margin: 1px 0 0 3px;" src="images/copy.png">
                        </div>
                        
                    </div>
                </div>
                <div class="box-fpago">
                    <span>Forma Pago</span>
                    <div>
                        <select id="fpag_forma_pago" style="margin-top: 3px;">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Deposito">Deposito</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                    </div>
                </div>
                <div class="box-fpago" id="box_fpag_ban_nombre">
                    <span id="txt_fpag_ban_nombre">Banco</span>
                    <div>
                        <input type="text" id="fpag_ban_nombre" value="" style="width: 80px;float: left;"> 
                    </div>
                </div>
                <div class="box-fpago" id="box_fpag_ban_nro">
                    <span id="txt_fpag_ban_nro">Nro</span>
                    <div>
                        <input type="text" id="fpag_ban_nro" value="" style="width: 80px;float: left;"> 
                    </div>
                </div>
                <div class="box-fpago">
                    <span>Descripcion</span>
                    <div>
                        <textarea id="fpag_descripcion"></textarea>
                    </div>
                </div>
                <div class="box-fpago">
                    <a href="javascript:void(0);" id="btn-add-det-fpago"><img id="btn-add-det-fpago" src="images/btn_add_detalle.png"></a>
                </div>
                <table class="tablaLista" cellspacing="0" cellpadding="0" id="lista-pagos">
                    <thead>
                        <tr>
                            <th>Cuenta Activo</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                            <th>Forma Pago</th>                            
                            <th>Banco</th>                            
                            <th>Nro</th>                            
                            <th>Descripci&oacute;n</th>
                            <th class="tOpciones"></th>                            
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr >
                            <td style="text-align: left;">Total</td>
                            <td style="text-align: left;">
                                <input type="hidden" id="fpag_tmonto" name="fpag_tmonto" value="0">
                                <span id="txt_fpag_tmonto">0</span>
                            </td>
                            <td style="text-align: left;"><span id="txt_fpag_moneda">0</span></td>
                            <td colspan="5">&nbsp;</td>
                        </tr>                        
                    </tfoot>
                </table>
                <div class="box-fpago" style="display: block; width: 100%">
                    <span>Dif. Cambio:</span>
                    <div>
                        <input type="text" name="fpag_dif_cambio" id="fpag_dif_cambio" value="" size="10" autocomplete="off"> <b id="txt_mon_dif_cambio"></b>
                    </div>
                </div>
            </div>
            <b id="msj-pagos" hidden="" style="color: #ff0000;float: left; padding-top: 3px">"USTED NO TIENE NIGUNA CAJA DE ASIGNADA O ESTA DESABILITADO COMO CAJERO"</b>
            <?php }else{ ?>
                <b style="color: #ff0000;float: left; padding-top: 3px">"USTED NO TIENE NIGUNA CAJA DE ASIGNADA O ESTA DESABILITADO COMO CAJERO"</b>
             <?php }?>
            <script>
                if($('#fpag_cue_id_chosen').length){
                    $('#fpag_cue_id_chosen').remove();
                }
                $('#fpag_cue_id').chosen({
                        allow_single_deselect:true
                });

                $('#fpag_cue_id_chosen').css('min-width','300px');
                mask_decimal('#fpag_dif_cambio',null);
                
                var cmp_fecha=$('#input-cmp-fecha').val();
                var cmp_monto=$('#input-cmp-monto').val();
                var cmp_moneda=$('#input-cmp-moneda').val();
                var fecha_sel='-1';
                $('#'+cmp_fecha).focusout(function(){
                    obtener_periodo();
                });
                $('#'+cmp_monto).focusout(function(){
                    $('#fpag_dif_cambio').val('');
                    sumar_fpag_monto();
                });
                $('#'+cmp_moneda).change(function(){
                    var str_moneda='';
                    if($(this).val()==='1'){str_moneda='Bs';}else{str_moneda='$us';}
                    $('#txt_mon_dif_cambio').text(str_moneda);
                    sumar_fpag_monto();
                });
                
                var str_moneda='';
                if($('#'+cmp_moneda).val()==='1'){str_moneda='Bs';}else{str_moneda='$us';}
                $('#txt_mon_dif_cambio').text(str_moneda);
                
                $('#fpag_forma_pago').change(function(){
                    activar_pag_bancos();
                });
                function activar_pag_bancos(){
                    var tipo_c=$("#tipo_flujo").val();
                    var val=$('#fpag_forma_pago option:selected').val();
                    var egreso='egreso';
                    var ingreso='ingreso';
    //                alert(val);
                    $("#fpag_ban_nombre").val('');
                    $("#fpag_ban_nro").val('');
                    if(val==='Efectivo'){
                        $("#box_fpag_ban_nombre").hide();
                        $("#box_fpag_ban_nro").hide();
                    }else if(val==='Cheque'){
                        if(tipo_c===ingreso){//ingreso
                            $("#box_fpag_ban_nombre").show();
                            $("#box_fpag_ban_nro").show();
                        }else if(tipo_c===egreso){//egreso
                            $("#box_fpag_ban_nombre").hide();
                            $("#box_fpag_ban_nro").show();
                        }
                    }else if(val==='Deposito'){
                        if(tipo_c===ingreso){
                            $("#box_fpag_ban_nombre").hide();
                            $("#box_fpag_ban_nro").show();
                        }else if(tipo_c===egreso){
                            $("#box_fpag_ban_nombre").show();
                            $("#box_fpag_ban_nro").show();
                        }
                    }else if(val==='Transferencia'){
                        $("#box_fpag_ban_nombre").show();
                        $("#box_fpag_ban_nro").show();
                    }
                }
                function obtener_periodo() {
                    var _fecha = $('#'+cmp_fecha).val();
//                    console.info(_fecha);
                    if (_fecha !== fecha_sel) {
                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: _fecha}, function(respuesta) {
                            var dato = JSON.parse(respuesta);
                            if (dato.response === "ok") {
//                                $('#'+cmp_fecha).after(dato.descripcion);
                                $('#cambios').val(JSON.stringify(dato.cambios));
                                $('#msj-pagos').hide();
                                $('#div-box-pagos').show();
                                sumar_fpag_monto();
                            } else if (dato.response === "error") {
                                $('#msj-pagos').text(dato.mensaje.toUpperCase());
                                $('#msj-pagos').show();
                                $('#div-box-pagos').hide();
                                $('#cambios').val('');
                            }
                            fecha_sel = _fecha;
                        });
                    }
                }
                
                $('#fpag_cue_id').change(function (){
                    var mon_id=$('#fpag_cue_id option:selected').data('moneda')*1;

                    if(mon_id===1){
                        $('#txt_moneda').text('Bolivianos');
                    }else if (mon_id===2){
                        $('#txt_moneda').text('Dolares');
                    }else if (mon_id===0){
                        $('#txt_moneda').text('');
                    }
                });
                mask_decimal('#fpag_monto',null);
                $('#btn-add-det-fpago').click(function (){
                    var monto=$('#fpag_monto').val()*1;
                    var moneda=$('#fpag_cue_id option:selected').data('moneda')*1;
                    var txt_moneda=$('#txt_moneda').text();
                    var fpago=$('#fpag_forma_pago option:selected').val();
                    var cuenta=$('#fpag_cue_id option:selected').val()*1;
                    var txt_cuenta=$('#fpag_cue_id option:selected').text();                    
                    var descripcion=$('#fpag_descripcion').val();
                    
                    var ban_nombre=$("#fpag_ban_nombre").val();
                    var ban_nro=$("#fpag_ban_nro").val();
                    
                    if(monto<=0 || cuenta<=0 || moneda<=0){
                        $.prompt('Ingrese correctamente el monto, seleccion una cuenta y verifique que la cuenta tenga una moneda');
                        return false;
                    }                    
                    var fila="";
                    fila+='<tr>';
                    fila+='     <td><input type="hidden" class="a_fpag_cue_id" name="a_fpag_cue_id[]" value="'+cuenta+'">'+txt_cuenta+'</td>';
                    fila+='     <td><input type="hidden" class="a_fpag_monto" name="a_fpag_monto[]" value="'+monto+'">'+monto+'</td>';
                    fila+='     <td><input type="hidden" class="a_fpag_mon_id" name="a_fpag_mon_id[]" value="'+moneda+'">'+txt_moneda+'</td>';
                    fila+='     <td><input type="hidden" class="a_fpag_forma_pago" name="a_fpag_forma_pago[]" value="'+fpago+'">'+fpago+'</td>';                    
                    fila+='     <td><input type="hidden" class="a_fpag_ban_nombre" name="a_fpag_ban_nombre[]" value="'+ban_nombre+'">'+ban_nombre+'</td>';                    
                    fila+='     <td><input type="hidden" class="a_fpag_ban_nro" name="a_fpag_ban_nro[]" value="'+ban_nro+'">'+ban_nro+'</td>';                    
                    fila+='     <td><input type="hidden" class="a_fpag_descripcion" name="a_fpag_descripcion[]" value="'+descripcion+'">'+descripcion+'</td>';
                    fila+='     <td><img class="img-del-fpag" src="images/b_drop.png"></td>';
                    fila+='</tr>';
                    $('#lista-pagos tbody').append(fila);
                    $('#fpag_monto').val('');
                    $('#fpag_mon_id option[value=1]').attr('selected','true');
                    $('#fpag_forma_pago option[value=Efectivo]').attr('selected','true');
                    $('#fpag_cue_id option[value=0]').attr('selected','true');    
                    $('#fpag_descripcion').val('');
                    $('#txt_moneda').text('');
                    $('#fpag_monto').focus();
                    activar_pag_bancos();
                    $('#fpag_dif_cambio').val('');
                    sumar_fpag_monto();
                });
                $('.img-del-fpag').live('click',function (){
                    $('#fpag_dif_cambio').val('');
                    $(this).parent().parent().remove();
                    sumar_fpag_monto();
                });
                function sumar_fpag_monto(){
                    var cambios=JSON.parse($('#cambios').val());
                    var moneda=$('#'+cmp_moneda).val()*1;
                    var montos=$('.a_fpag_monto');
                    var monedas=$('.a_fpag_mon_id');
                    var sum_monto=0;
                    for(var i=0; i<montos.size();i++){
                        var _monto=$(montos[i]).val()*1;
                        var _moneda=$(monedas[i]).val()*1;
                        
                        sum_monto+=convertir_fpag_monto(_monto,_moneda,moneda,cambios)*1;
                    }
//                    monto=monto.toFixed(2)*1;
                    sum_monto=sum_monto.toFixed(2)*1;
                    $('#fpag_tmonto').val(sum_monto);
                    $('#txt_fpag_tmonto').text(sum_monto);
                    if(moneda===2){
                        $('#txt_fpag_moneda').text('Dolares');
                    }else{
                        $('#txt_fpag_moneda').text('Bolivianos');
                    }
                    
                    var dif_cambio=$('#fpag_dif_cambio').val()*1;
                    var monto_pag=$('#'+cmp_monto).val()*1;
                    monto_pag+=dif_cambio;
                    
                    var saldo=monto_pag-sum_monto;
                    
                    if(moneda===2){
                        var saldo_bs=convertir_fpag_monto(saldo,moneda,1,cambios)*1;
                        var saldo_usd=saldo;
                    }else if (moneda===1){
                        var saldo_bs=saldo;
                        var saldo_usd=convertir_fpag_monto(saldo,moneda,2,cambios)*1;
                    }
                    
                    $('#txt-saldo-bs').text(saldo_bs.toFixed(2));
                    $('#txt-saldo-usd').text(saldo_usd.toFixed(2));
//                    var saldo=
                }
                $('#fpag_dif_cambio').keyup(function(){
                    sumar_fpag_monto();
                });
                function validar_fpag_montos(cambios){
//                    monto=monto*1;
//                    moneda=moneda*1;
                    var monto=$('#'+cmp_monto).val()*1;
                    var dif_cambio=$('#fpag_dif_cambio').val()*1;
                    monto+=dif_cambio;
                    var moneda=$('#'+cmp_moneda).val()*1;
//                    console.info(cmp_moneda);
//                    console.info(monto+' - '+moneda);
                    var montos=$('.a_fpag_monto');
                    var monedas=$('.a_fpag_mon_id');
                    var monto_cmp=0;
                    for(var i=0; i<montos.size();i++){
                        var _monto=$(montos[i]).val()*1;
                        var _moneda=$(monedas[i]).val()*1;
//                        console.log('monto: '+monto+','+_moneda+' === '+ moneda);
//                        console.log(cambios);
                        monto_cmp+=convertir_fpag_monto(_monto,_moneda,moneda,cambios)*1;
                    }
                    monto=monto.toFixed(2)*1;
                    monto_cmp=monto_cmp.toFixed(2)*1;
                    console.log(monto+' == '+monto_cmp);
                    if(monto===monto_cmp){
                        return true;
                    }else{
                        return false;
                    }
                }
                
                function convertir_fpag_monto(monto, mon_orig,mon_dest,cambios){                    
                    if(mon_orig===mon_dest){                        
                        return monto;
                    }
                    var tc = 1;
                    for (var i = 0; i < cambios.length; i++) {
                        var cambio = cambios[i];
                        if (mon_orig === cambio.id*1) {
                            tc = cambio.val;
                        }                        
                    }                    
                    var monto_bol=monto*tc;
                    tc=1;
                    for (i = 0; i < cambios.length; i++) {
                        var cambio = cambios[i];
                        if (mon_dest === cambio.id*1) {
                            tc = cambio.val;
                        }
                    }
//                    console.log('conv ---> '+(monto_bol/tc));
                    return monto_bol/tc;                    
                }
                
                $('#copy-bs').click(function (){
                    var saldo= trim($('#txt-saldo-bs').text())*1;
                    $('#fpag_monto').val(saldo);
                });
                $('#copy-usd').click(function (){
                    var saldo= trim($('#txt-saldo-usd').text())*1;
                    $('#fpag_monto').val(saldo);
                });
                setTimeout('obtener_periodo()',200);
                activar_pag_bancos();
            </script>
        <?php         
        }
        
        
        public static function anular_pagos($tabla, $tabla_id, &$conec=null){
            if($conec==null){
                $conec=new ADO();
            }
            $sql="update con_pago set fpag_estado='Anulado' where fpag_tabla='$tabla' and fpag_tabla_id='$tabla_id'";
            $conec->ejecutar($sql);
        }
        
        public static function insertar_pagos($params,$conec=null){
            $params=(object)$params;
            if($conec==null){
                $conec=new ADO();
            }
            $sql="insert into con_pago(
                fpag_forma_pago,fpag_cue_id, fpag_monto,fpag_mon_id, fpag_descripcion, 
                fpag_tabla, fpag_tabla_id, fpag_fecha, fpag_estado,fpag_tipo,fpag_une_id,fpag_une_porc
                ) values";
            $montos=$_POST[a_fpag_monto];
            $monedas=$_POST[a_fpag_mon_id];
            $fpagos=$_POST[a_fpag_forma_pago];
            $ban_nombres=$_POST[a_fpag_ban_nombre];
            $ban_nros=$_POST[a_fpag_ban_nro];
            $cuentas=$_POST[a_fpag_cue_id];
            $descripciones=$_POST[a_fpag_descripcion];
            $detalles=array();
            $cambios=  FUNCIONES::objetos_bd_sql("select * from con_tipo_cambio where tca_fecha='$params->fecha'");    
            
            $une_id='0';
            $une_porc='100';
            $is_array=false;
            
            $type=gettype($params->une_id);
            if($type=='array'){
                $keys=  array_keys($params->une_id);
                $values= array_values($params->une_id);
                $une_id=  implode(',', $keys);
                $une_porc=implode(',', $values);
                
                $is_array=true;
            }else if($type=='integer' || $type=='string'){
                $une_id=$params->une_id;
                $une_porc='100';
            }
            $guardar_pago=true;
            if(isset($params->guardar_pago)){
                $guardar_pago=$params->guardar_pago;
            }
            for ($i = 0; $i < count($montos); $i++) {
                $tipo='Egreso';
                if($params->ingreso){ $tipo='Ingreso';}
                $sql_det="$sql (
                        '$fpagos[$i]','$cuentas[$i]','$montos[$i]','$monedas[$i]','$descripciones[$i]',
                        '$params->tabla','$params->tabla_id','$params->fecha','Activo','$tipo','$une_id','$une_porc'
                    )";
                if($guardar_pago){
                    $conec->ejecutar($sql_det);
                }
                
                $monto=  FORMULARIO::convertir_fpag_monto($montos[$i], $monedas[$i], $params->moneda, $cambios);
                if(!$is_array){
                    if($params->ingreso){ $debe=$monto;$haber=0;}
                    else{$debe=0; $haber=$monto; }
                    $detalles[]=array("cuen"=>$cuentas[$i],"debe"=>$debe,"haber"=>$haber,
                                "glosa"=>$params->glosa,"ca"=>$params->ca,"cf"=>$params->cf,"cc"=>$params->cc,
                                'fpago'=>$fpagos[$i],'ban_nombre'=>$ban_nombres[$i],'ban_nro'=>$ban_nros[$i],
                                'descripcion'=>$descripciones[$i],'une_id'=>$une_id
                        );
                }else{
                    $aune_ids=$params->une_id;
                    $j=1;
                    $sum_asig=0;
                    foreach ($aune_ids as $_uid => $_porc) {
                        if($j<count($aune_ids)){
                            $_amonto = round(($_porc*$monto)/100,2);
                            $sum_asig+=$_amonto;
                        }else{
                            $_amonto = round($monto-$sum_asig,2);
                        }
                        if($params->ingreso){ $debe=$_amonto;$haber=0;}
                        else{$debe=0; $haber=$_amonto; }
                        $detalles[]=array("cuen"=>$cuentas[$i],"debe"=>$debe,"haber"=>$haber,
                                "glosa"=>$params->glosa,"ca"=>$params->ca,"cf"=>$params->cf,"cc"=>$params->cc,
                                'fpago'=>$fpagos[$i],'ban_nombre'=>$ban_nombres[$i],'ban_nro'=>$ban_nros[$i],
                                'descripcion'=>$descripciones[$i],'une_id'=>$_uid
                        );
                        
                        $j++;    
                    }
                }
                
            }
            $dif_cambio=$_POST[fpag_dif_cambio]*1;
            if($dif_cambio>0){
                if($params->ingreso){ $debe=0;$haber=$dif_cambio;}
                else{$debe=$dif_cambio; $haber=0; }
                $ges_id=$_SESSION[ges_id];
                $_une_id=0;
                if(!$is_array){
                    $_une_id=$une_id;
                }
                $detalles[]=array("cuen"=>  FUNCIONES::get_cuenta($ges_id, MODELO_COMPROBANTE::$dif_cambio_diponibilidades),"debe"=>$debe,"haber"=>$haber,
                            "glosa"=>$params->glosa,"ca"=>$params->ca,"cf"=>$params->cf,"cc"=>$params->cc,
                            'descripcion'=>'','une_id'=>$_une_id
                    );
            }
            return $detalles;
        }
        
        public static function convertir_fpag_monto($monto,$mon_orig,$mon_dest,$cambios){   
//            echo "monto: $monto, mon_ori: $mon_orig, mon_des: $mon_dest,cambios:  <br>";
            if($mon_orig===$mon_dest){
                return $monto;
            }
            $tc = 1;            
            $cambios->reset();
            for ($i = 0; $i < $cambios->get_num_registros(); $i++) {
                $cambio = $cambios->get_objeto();
                if ($mon_orig == $cambio->tca_mon_id*1) {
                    $tc = $cambio->tca_valor;
                }
                $cambios->siguiente();
            }                    
            $monto_bol=$monto*$tc;
            $tc=1;
            $cambios->reset();
            for ($i = 0; $i < $cambios->get_num_registros(); $i++) {
                $cambio = $cambios->get_objeto();
                if ($mon_dest ==$cambio->tca_mon_id*1) {
                    $tc = $cambio->tca_valor;
                }
                $cambios->siguiente();
            }
//                    console.log('conv ---> '+(monto_bol/tc));
            return $monto_bol/$tc;                    
        }
        
        public static function mostrar_mensaje($msj,$tipo='Informacion') {
            ?>
            <div class="ancho100">
                <div class="ms<?php echo $tipo?> limpiar"><?php echo $msj;?></div>
            </div>
            <?php
        }
        public static function detalle_compra() {
            ?>
<!--            <div id="box-det-compra" style="display: none;" >
                <br>
                <script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
                <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                <script type="text/javascript" src="js/util.js"></script>
                <script type="text/javascript" src="js/bsn.AutoSuggest_2.1.3.js"></script>
                <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />-->
                <style>
                    .titulo-detalle{margin-bottom: 10px;  border-bottom: 1px solid #dadada; text-align: left;font-size: 13px; font-weight: bold; color: #3A3A3A;}
                    .detalle-tipos { font-size: 13px; font-weight: bold; margin: 0 auto; text-align: center; margin-bottom: 10px;}
                    .detalle-tipos a{ padding: 5px 10px; color: #000;text-decoration: none; background-color: #92BF4C}
                    .detalle-tipos a:hover {background-color: #3399FF; color: #fff}
                    .detalle-tipos a.tselect{background-color: #FF0000 !important; color: #fff}
                    .box-column{float: left; width: 30%; }
                    .Etiqueta3{ color:#3a3a3a; width:80px; text-align:right;float: left; }
                    
                    .tab_lista_cuentas{
                        list-style: none; width: 100%; overflow:scroll ;
                        background-color: #ededed; border-collapse: collapse; font-size: 12px;
                    }
                    .tab_lista_cuentas tr td{ padding: 3px 3px; }
                    .tab_lista_cuentas tr:hover{ background-color: #f9e48c; }                                
                    .img_del_cuenta{ font-weight: bold; cursor: pointer; width: 12px; }
                    .box_lista_cuenta{ width: 270px;height:80px;background-color:#F2F2F2;overflow:auto; border: 1px solid #8ec2ea; }
                    .box-error{border: 1px solid #ff0000;}
                </style>
                <input type="hidden" id="num_fil" value="">
                <input type="hidden" id="data_objeto" value="">
                <input type="hidden" id="tipo_nota" name="tipo_nota" value="">
                <div class="msError" style="display: none;" >
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
                
                $ret_it= FUNCIONES::parametro('ret_it');
                $ret_iue_serv= FUNCIONES::parametro('ret_iue_serv');
                $ret_iue_bien= FUNCIONES::parametro('ret_iue_bien');
                
                $cu_ret_it=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$ret_it'");
                $cu_ret_iue_serv=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$ret_iue_serv'");
                $cu_ret_iue_bien=  FUNCIONES::objeto_bd_sql("select * from con_cuenta where cue_ges_id='$ges_id' and cue_codigo='$ret_iue_bien'");

                $porc_ret_it= FUNCIONES::parametro('porc_ret_it');
                $porc_ret_iue_serv= FUNCIONES::parametro('porc_ret_iue_serv');
                $porc_ret_iue_bien= FUNCIONES::parametro('porc_ret_iue_bien');
                ?>
                <input type="hidden" id="id_cred_fiscal" value="<?php echo $cu_cf->cue_id;?>">
                <input type="hidden" id="txt_cred_fiscal" value="<?php echo $cu_cf->cue_descripcion;?>">
                <input type="hidden" id="id_deb_fiscal" value="<?php echo $cu_df->cue_id;?>">
                <input type="hidden" id="txt_deb_fiscal" value="<?php echo $cu_df->cue_descripcion;?>">
                <input type="hidden" id="val_iva" value="<?php echo ($val_iva);?>">
                <input type="hidden" id="id_it" value="<?php echo $cu_it->cue_id;?>">
                <input type="hidden" id="txt_it" value="<?php echo $cu_it->cue_descripcion;?>">
                <input type="hidden" id="id_itpagar" value="<?php echo $cu_itpagar->cue_id;?>">
                <input type="hidden" id="txt_itpagar" value="<?php echo $cu_itpagar->cue_descripcion;?>">
                <input type="hidden" id="val_it" value="<?php echo ($val_it);?>">
                
                <input type="hidden" id="id_ret_it" value="<?php echo $cu_ret_it->cue_id;?>">
                <input type="hidden" id="txt_ret_it" value="<?php echo $cu_ret_it->cue_descripcion;?>">
                <input type="hidden" id="porc_ret_it" value="<?php echo ($porc_ret_it);?>">

                <input type="hidden" id="id_ret_iue_serv" value="<?php echo $cu_ret_iue_serv->cue_id;?>">
                <input type="hidden" id="txt_ret_iue_serv" value="<?php echo $cu_ret_iue_serv->cue_descripcion;?>">
                <input type="hidden" id="porc_ret_iue_serv" value="<?php echo ($porc_ret_iue_serv);?>">

                <input type="hidden" id="id_ret_iue_bien" value="<?php echo $cu_ret_iue_bien->cue_id;?>">
                <input type="hidden" id="txt_ret_iue_bien" value="<?php echo $cu_ret_iue_bien->cue_descripcion;?>">
                <input type="hidden" id="porc_ret_iue_bien" value="<?php echo ($porc_ret_iue_bien);?>">

                <div class="box-column">
                    <div id="ContenedorDiv">
                        <div class="Etiqueta3" >Moneda:</div>
                        <div id="CajaInput" >
                            <select id="rec_moneda" name="rec_moneda" style="width: 80px">
                                <option value="1">Bolivianos</option>
                                <option value="2">Dolares</option>
                            </select>
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta3" >Fecha:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_fecha" name="rec_fecha" value="<?php echo date('d/m/Y');?>">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-factura">
                        <div class="Etiqueta3" >tipo:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_com_tipo" name="rec_com_tipo" value="">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-factura">
                        <div class="Etiqueta3" >Nro. de NIT:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_nit" name="rec_nit" value="">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-factura">
                        <div class="Etiqueta3" >Nro. de Aut.:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_aut" name="rec_aut" value="">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-factura">
                        <div class="Etiqueta3" >Cod. de Control:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_control" name="rec_control" value="">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-factura">
                        <div class="Etiqueta3" >Nro. de Factura:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_nro_fac" name="rec_nro_fac" value="">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-factura">
                        <div class="Etiqueta3" >Nro. de Poliza:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_poliza" name="rec_poliza" value="">
                        </div>
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta3" >Proveedor:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_proveedor" name="rec_proveedor" value="">
                        </div>
                    </div>
                </div>
                <div class="box-column">
                    <div id="ContenedorDiv">
                        <div class="Etiqueta3" >Importe:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_importe"  name="rec_importe" value="" autocomplete="off">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-factura">
                        <div class="Etiqueta3" >Total I.C.E.:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_ice" name="rec_ice" value="" autocomplete="off">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-factura">
                        <div class="Etiqueta3" >Imp. Excentos:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_excento" name="rec_excento" value="" autocomplete="off">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-factura">
                        <div class="Etiqueta3" >Imp. Neto:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_imp_neto" name="rec_imp_neto" value="" autocomplete="off" readonly="">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-factura">
                        <div class="Etiqueta3" >Cred. Fiscal IVA:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_iva" name="rec_iva" value="" autocomplete="off" readonly="">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-retencion">
                        <div class="Etiqueta3" >Tipo de Ret.:</div>
                        <div id="CajaInput" >
                            <select id="rec_tipo_ret" name="rec_tipo_ret" style="width: 70px">
                                <option value="Servicios">Servicios</option>
                                <option value="Bienes">Bienes</option>
                            </select>
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-retencion">
                        <div class="Etiqueta3" >Retencion IT:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_it" name="rec_it" value="" autocomplete="off" readonly="">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-retencion">
                        <div class="Etiqueta3" >Retencion IUE:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_iue" name="rec_iue" value="" autocomplete="off" readonly="">
                        </div>
                    </div>
                    <div id="ContenedorDiv" class="div-rec div-retencion">
                        <div class="Etiqueta3" >Monto Res.:</div>
                        <div id="CajaInput" >
                            <input type="text" id="rec_ret_res" name="rec_ret_res" value="" autocomplete="off" readonly="">
                        </div>
                    </div>
                    <div id="ContenedorDiv" >
                        <div class="Etiqueta3" >Glosa.:</div>
                        <div id="CajaInput" >
                            <textarea id="rec_glosa" name="rec_glosa"></textarea>
                        </div>
                    </div>
                </div>
                <div class="box-column" style="width: 40%">
                    <div id="ContenedorDiv">
                        <div class="Etiqueta3" >Gasto: </div>
                        <div id="CajaInput">                                    
                            <input name="txt_cuentas_act_disp" id="txt_cuentas_act_disp"  type="text" class="caja_texto" value="" size="25">
                            <input name="cuentas_act_disp" id="cuentas_act_disp"  type="hidden" value="">
                        </div>							   							   								
                    </div>
                    <div id="ContenedorDiv">
                        <div class="Etiqueta3" >Gastos a listar: </div>
                        <div id="CajaInput">
                            <div class="box_lista_cuenta">
                                <table id="tab_cuentas_act_disp" class="tab_lista_cuentas">
                                </table>
                            </div>
                        </div>							   							   								
                    </div>
                    <?php $gastos= json_decode(FUNCIONES::ad_parametro('par_gastos'));?>
                    <?php foreach ($gastos as $gasto) {?>
                        <?php $gasto=(object)$gasto;?>
                        <div id="ContenedorDiv">
                            <div class="Etiqueta3 gastos_une_txt"><?php echo FUNCIONES::atributo_bd_sql("select une_nombre as campo from con_unidad_negocio where une_id='$gasto->une_id'");?>:</div>
                            <div id="CajaInput">
                                <input type="hidden" class="gastos_une_ids" name="gastos_une_ids[]"value="<?php echo $gasto->une_id*1;?>" size="15">
                                <input type="text" id="une_porc_<?php echo $gasto->une_id;?>" class="gastos_une_porc" name="gastos_une_porc[]"value="<?php echo $gasto->une_porc*1;?>" size="15"> %
                            </div>
                        </div>   
                    <?php }?>
                </div>
                <div style="clear: both;"></div>
                <div class="titulo-detalle" >
                    <span>Pagos </span>
                </div>
                <div >
                    <div class="box-column" style="width: 100%">
                        <div id="ContenedorDiv">
                            <?php FORMULARIO::frm_pago(array('cmp_fecha'=>'rec_fecha','cmp_monto'=>'rec_importe','cmp_moneda'=>'rec_moneda','tipo_flujo'=>'egreso'));?>
                        </div>
                    </div>
                </div>
                <div id="ContenedorSeleccion" style="width: 100%; padding: 15px 0;">
                    <input type="button" class="boton" value="Aceptar" id="btn_aceptar">
                    <input type="button" class="boton" value="Cancelar" id="btn_cancelar">
                </div>
                <div id="lista_cuenta_gastos" style="display: none;">
                    <?php
                    $filtro='';
                    $titulos_gastos=_VAR::$TITULOS_GASTOS;
                    if(count($titulos_gastos)>0){
                        $filtro="and (";
                        for ($i = 0; $i < count($titulos_gastos); $i++) {
                            $tg=$titulos_gastos[$i];
                            if($i>0){
                                $filtro.=" or ";
                            }
                            $filtro.=" cue_codigo like '$tg.%' ";
                        }
                        $filtro.=")";
                    }
                    echo FUNCIONES::cuentas_json($filtro);
                    ?>
                </div>
                <script>
                    mask_decimal('#rec_importe',null);
                    mask_decimal('#rec_ice',null);
                    mask_decimal('#rec_excento',null);
                    mask_decimal('.gast_monto',null);
                    $('#rec_importe').keyup(function (){
                        var tipo_nota=$('#tipo_nota').val();
                        if(tipo_nota==='factura'){
                            calcular_neto_iva();
                        }
                        if(tipo_nota==='retencion'){
                            calcular_retencion();
                        }
                    });
                    $('#rec_ice, #rec_excento').keyup(function (){
                        calcular_neto_iva();
                    });
                    $('#rec_tipo_ret').change(function (){
                        calcular_retencion();
                    });
                    function calcular_retencion(){
                        var imp_neto= $("#rec_importe").val()*1;
                        var ret_tipo= $("#rec_tipo_ret option:selected").val();
                        var porc_it=$("#porc_ret_it").val()*1;
                        var porc_iue=0;
                        if(ret_tipo==='Servicios'){
                            porc_iue=$("#porc_ret_iue_serv").val()*1;
                        }else if(ret_tipo==='Bienes'){
                            porc_iue=$("#porc_ret_iue_bien").val()*1;
                        }
                        
                        var n_monto = imp_neto / ((100 - (porc_iue + porc_it)) / 100);
                        
                        var monto_ret_it = n_monto * porc_it / 100;
                        var monto_ret_iue = n_monto * porc_iue / 100;
                        var gasto_neto = imp_neto + monto_ret_it + monto_ret_iue;
                        $('#rec_it').val(monto_ret_it.toFixed(2)*1);
                        $('#rec_iue').val(monto_ret_iue.toFixed(2)*1);
                        $('#rec_ret_res').val(gasto_neto.toFixed(2)*1);
                    }
                    function calcular_neto_iva(){
                        var tf=($("#rec_importe").val())*1;
                        var ice=($("#rec_ice").val())*1;
                        var ext=($("#rec_excento").val())*1;
                        var imp_neto=tf-ice-ext;
                        var iva=$("#val_iva").val();
                        var imp_neto=tf-ice-ext;
                        var val_iva=imp_neto*iva/100;
                        
                        $("#rec_imp_neto").val(imp_neto.toFixed(2)*1);
                        $("#rec_iva").val(val_iva.toFixed(2)*1);
                    }
                    $('#btn_aceptar').click(function(){
                        if(!validar_campos()){
                            $('.msError').slideDown(400);
                            setTimeout("$('.msError').slideUp(400)",10000);
                            setTimeout("$('.box-error').removeClass('box-error')",10000);
                            return false;
                        }
                        var tipo_nota=$('#tipo_nota').val();
                        var moneda=$('#rec_moneda option:selected').val();
                        var fecha=$('#rec_fecha').val();
                        var proveedor=$('#rec_proveedor').val();
                        var importe=$('#rec_importe').val();
                        var glosa=$('#rec_glosa').val();
                        var gastos=[];
                        var tab_filas= $('#tab_cuentas_act_disp tbody tr');
                        for (var i=0;i<tab_filas.size();i++){
                            var tr=tab_filas[i];
                            var cue_id=$(tr).attr('data-id');
                            var desc=$(tr).find('td').text();
                            var gmonto=$(tr).find('.gast_monto').val();
                            gastos.push({cue_id:cue_id,cue_desc:desc,monto:gmonto});
                        }
                        var une_ids=$('.gastos_une_ids');
                        var une_porcs=$('.gastos_une_porc');
                        var une_txts=$('.gastos_une_txt');
                        var unegocios=[];
                        for (var i=0;i<une_ids.size();i++){
                            var une_id=$(une_ids[i]).val();
                            var une_porc=$(une_porcs[i]).val();
                            var une_txt=$(une_txts[i]).text();
                            unegocios.push({une_id:une_id, une_porc:une_porc,une_txt:une_txt})
                        }

                        var pag_cue_ids=$('.a_fpag_cue_id');
                        var pag_montos=$('.a_fpag_monto');
                        var pag_mon_ids=$('.a_fpag_mon_id');
                        var pag_fpagos=$('.a_fpag_forma_pago');
                        var pag_ban_nombres=$('.a_fpag_ban_nombre');
                        var pag_ban_nros=$('.a_fpag_ban_nro');
                        var pag_descripciones=$('.a_fpag_descripcion');
                        var pagos=[];
                        for (var i=0;i<pag_cue_ids.size();i++){
                            var pag_cue_id=$(pag_cue_ids[i]).val();
                            var pag_cue_txt=$(pag_cue_ids[i]).parent().text();
                            var pag_monto=$(pag_montos[i]).val();
                            var pag_mon_id=$(pag_mon_ids[i]).val();
                            var pag_fpago=$(pag_fpagos[i]).val();
                            var pag_ban_nombre=$(pag_ban_nombres[i]).val();
                            var pag_ban_nro=$(pag_ban_nros[i]).val();
                            var pag_descripcion=$(pag_descripciones[i]).val();
                            pagos.push({cue_id:pag_cue_id, cue_desc:pag_cue_txt, monto:pag_monto, mon_id:pag_mon_id, 
                                        fpago:pag_fpago, ban_nombre:pag_ban_nombre, ban_nro:pag_ban_nro,descripcion:pag_descripcion
                                });
                        }
                        var dif_cambio = $('#fpag_dif_cambio').val()*1;
                        var com_tipo='';
                        var nit='';
                        var aut='';
                        var control='';
                        var nro_fac='';
                        var poliza='';
                        var tice='';
                        var excento='';
                        var ineto='';
                        var iva='';
                        
                        var tipo_ret='';
                        var ret_it='';
                        var ret_iue='';
                        var ret_res='';
                        if(tipo_nota==='factura'){
                            com_tipo=$('#rec_com_tipo').val();
                            nit=$('#rec_nit').val();
                            aut=$('#rec_aut').val();
                            control=$('#rec_control').val();
                            nro_fac=$('#rec_nro_fac').val();
                            poliza=$('#rec_poliza').val();
                            tice=$('#rec_ice').val();
                            excento=$('#rec_excento').val();
                            ineto=$('#rec_imp_neto').val();
                            iva=$('#rec_iva').val();
                        } else if(tipo_nota==='retencion'){
                            tipo_ret=$('#rec_tipo_ret option:selected').val();
                            ret_it=$('#rec_it').val();;
                            ret_iue=$('#rec_iue').val();
                            ret_res=$('#rec_ret_res').val();
                        }
                        var data={};
                        data.tipo_nota=tipo_nota;
                        data.moneda=moneda;
                        data.fecha=fecha;
                        data.proveedor=proveedor;
                        data.importe=importe;
                        data.glosa=glosa;
                        data.gastos=gastos;
                        data.unegocios=unegocios;
                        data.pagos=pagos;
                        data.dif_cambio=dif_cambio;
                        
                        data.com_tipo=com_tipo;
                        data.nit=nit;
                        data.aut=aut;
                        data.control=control;
                        data.nro_fac=nro_fac;
                        data.poliza=poliza;
                        data.ice=tice;
                        data.excento=excento;
                        data.imp_neto=ineto;
                        data.iva=iva;

                        data.tipo_ret=tipo_ret;
                        data.it=ret_it;
                        data.iue=ret_iue;
                        data.ret_res=ret_res;
                        
                        data.num_fil=$('#num_fil').val();
                        
                        window.opener.cargar_detalle_compra(data);
//                        console.log(data);
                        self.close();
                        return true;
                    });

                    $('#btn_cancelar').click(function(){
                        self.close();
                        return false;
                    });

                    function validar_dato(input){
                        if($(input)!==undefined){
                            var valor=trim($(input).val());
                            if(valor!==''){
                                return true;
                            }else{
                                return false;
                            }
                        }else{
                            return false;
                        }
                    }

                    function validar_campos(){
                        $('.msError').children().remove();
                        $('.msError').slideUp();
                        var sw=true;
                        var tipo_nota=$('#tipo_nota').val();
                        if(tipo_nota===''){
                            $('#tipo_nota').addClass('box-error');
                            sw=false;
                        }
                        var moneda=$('#rec_moneda option:selected').val();
                        if(moneda===''){
                            $('#rec_moneda').addClass('box-error');
                            sw=false;
                        }
                        if(!validar_dato('#rec_fecha')){
                            $('#rec_fecha').addClass('box-error');
                            sw=false;
                        }
                        if(!validar_dato('#rec_proveedor')){
                            $('#rec_proveedor').addClass('box-error');
                            sw=false;
                        }
                        if(!validar_dato('#rec_importe')){
                            $('#rec_importe').addClass('box-error');
                            sw=false;
                        }
                        if(!validar_dato('#rec_glosa')){
                            $('#rec_glosa').addClass('box-error');
                            sw=false;
                        }
                        var tab_filas= $('#tab_cuentas_act_disp tbody tr');
                        if(tab_filas.size()===0){
                            $('.box_lista_cuenta').addClass('box-error');
                            sw=false;
                        }else{
                            
                            var gast_montos=$('.gast_monto');
                            var suma=0;
                            for (var i=0;i<gast_montos.size();i++){
                                var gmonto=$(gast_montos[i]).val()*1;
                                if(gmonto===0){
                                    $('.msError').append('<p>- La suma de los <b>Gastos</b> debe ser igual al importe <b>('+importe+')</b></p>')
                                    sw=false;
                                    break;
                                }
                                suma+=gmonto;
                            }
                            suma=suma.toFixed(2)*1;
                            var importe=$('#rec_importe').val()*1;
                            if(importe!==suma){
                                $('.msError').append('<p>- La suma de los <b>Gastos</b> debe ser igual al importe <b>('+importe+')</b></p>')
                                sw=false;
                            }
                        }

                        var une_porcs=$('.gastos_une_porc');
                        var suma=0;
                        
                        for (var i=0;i<une_porcs.size();i++){
                            var une_porc=$(une_porcs[i]).val()*1;
                            suma+=une_porc;
                        }

                        suma=suma.toFixed(2)*1;
                        if(suma!==100){
                            $('.msError').append('<p>- La suma de los porcentajes de las <b>U. Negocios</b> debe ser el 100%</p>');
                            sw=false;
                        }
                        var tcambios=JSON.parse($('#cambios').val());
                        if(!validar_fpag_montos(tcambios)){
                            $('.msError').append('<p>- El <b>monto a pagar</b> no cocuerda con los pagos realizados</p>');
                            sw=false;
                        }
                        
                        if(tipo_nota==='factura'){
                            if(!validar_dato('#rec_com_tipo')){
                                $('#rec_com_tipo').addClass('box-error');sw=false;
                            }
                            if(!validar_dato('#rec_nit')){
                                $('#rec_nit').addClass('box-error');sw=false;
                            }
                            if(!validar_dato('#rec_aut')){
                                $('#rec_aut').addClass('box-error');sw=false;
                            }
                            if(!validar_dato('#rec_control')){
                                $('#rec_control').addClass('box-error');sw=false;
                            }
                            if(!validar_dato('#rec_nro_fac')){
                                $('#rec_nro_fac').addClass('box-error');sw=false;
                            }
                            if(!validar_dato('#rec_poliza')){
                                $('#rec_poliza').addClass('box-error');sw=false;
                            }
                        }else if (tipo_nota==='retencion'){
                            var tipo_ret=$('#rec_tipo_ret option:selected').val();
                            if(tipo_ret===''){
                                $('#rec_poliza').addClass('box-error');sw=false;
                            }
                        }
                        
                        
                        if(!sw){
                            $('.msError').append('<p>- Ingrese correctamente los campos requeridos</p>')
                            
                        }
                        return sw;
                    }

                    function cargar_nota_compra(){
                        var tnota=$('#tipo_nota').val();
                    }

                    $('#rec_fecha').mask('99/99/9999');
//                    var projects = JSON.parse('<?php // echo FUNCIONES::cuentas_json("and (cue_codigo like '6.%' or cue_codigo like '1.%')");?>')
                    var projects = JSON.parse(trim($('#lista_cuenta_gastos').text()));
                    function complete_cuenta(input){
                        autocomplete_ui(
                            {
                                input:'#txt_cuentas_act_disp',
                                bus_info:true,
                                lista:projects,
                                select:function(obj){
                                    agregar_cuenta(obj,input);
                                    return false;
                                }

                            }
                        );
                    }
                    function agregar_cuenta(cuenta,input) {
                        var gast_montos= $('.gast_monto');
                        var monto='';
                        console.log(gast_montos.size());
                        if(gast_montos.size()===0){
                            monto=$('#rec_importe').val();
                        }else{
                            for(var i=0;i<gast_montos.size();i++){
                                $(gast_montos[i]).val('');
                            }
                        }
                        if (!existe_en_lista(cuenta.info,input)) {
                            var fila = '<tr data-id="' + cuenta.id + '">';
                            fila += '<td>'+cuenta.info+' | ' + cuenta.value + '</td>';
                            fila += '<td><input type="text" class="gast_monto" value="'+monto+'" size="5"></td>';
                            fila += '<td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                            fila += '</tr>';
                            $("#tab_"+input).append(fila);
                        }
                        $("#txt_"+input).val("");
                    }

                    function existe_en_lista(id_cuenta,input) {
                        var lista = $("#tab_"+input+" tr");
                        for (var i = 0; i < lista.size(); i++) {
                            var cuenta = lista[i];
                            var id = $(cuenta).attr("data-id");
//                            console.log(id+" - "+id_cuenta);
                            if (id === id_cuenta) {
                                return true;
                            }
                        }
                        return false;
                    }
                    $(".img_del_cuenta").live('click', function() {
                        $(this).parent().parent().remove();
                    });
                    complete_cuenta("cuentas_act_disp");
                    mask_decimal('#rec_importe',null);
                    mask_decimal('.gastos_une_porc',null);
                    $('#rec_fecha').focus();
                    
                    var str_data=trim($('#data_objeto').val());
                    if(str_data!==''){
//                        console.log(str_data);
                        llenar_datos_formulario(JSON.parse(str_data));
                    }
                    function llenar_datos_formulario(data){
                        $('#rec_moneda option[value="'+data.moneda+'"]').attr('selected','true');
                        $('#rec_fecha').val(data.fecha);
                        $('#rec_com_tipo').val(data.com_tipo);
                        $('#rec_nit').val(data.nit);
                        $('#rec_aut').val(data.aut);
                        $('#rec_control').val(data.control);
                        $('#rec_nro_fac').val(data.nro_fac);
                        $('#rec_poliza').val(data.poliza);
                        $('#rec_proveedor').val(data.proveedor);
                        $('#rec_importe').val(data.importe);
                        $('#rec_ice').val(data.ice);
                        $('#rec_excento').val(data.excento);
                        $('#rec_imp_neto').val(data.imp_neto);
                        $('#rec_iva').val(data.iva);
                        
                        $('#rec_it').val(data.it);
                        $('#rec_iue').val(data.iue);
                        $('#rec_ret_res').val(data.ret_res);
//                        tipo_ret
                        $('#rec_tipo_ret option[value='+data.tipo_ret+']').attr('selected','true');
                        
                        $('#rec_glosa').val(data.glosa);
                        
                        var gastos=data.gastos;
                        var filas='';
                        for(var i=0;i<gastos.length;i++){
                            var cuenta=gastos[i];
                            filas += '<tr data-id="' + cuenta.cue_id + '">';
                            filas += '<td>'+cuenta.cue_desc +'</td>';
                            filas += '<td><input type="text" class="gast_monto" value="'+cuenta.monto+'" size="5"></td>';
                            filas += '<td width="8%"><img class="img_del_cuenta" src="images/retener.png"/></td>';
                            filas += '</tr>';
                        }
                        $("#tab_cuentas_act_disp").append(filas);
                        
                        var unegocios=data.unegocios;
                        var filas='';
                        for(var i=0;i<unegocios.length;i++){
                            var une=unegocios[i];
                            $('#une_porc_'+une.une_id).val(une.une_porc);
                        }
                        var pagos=data.pagos;
                        var pag_filas='';

                        for(var i=0;i<pagos.length;i++){
                            var pag=pagos[i];
                            var mon_id=pag.mon_id * 1;
                            var mon_txt='';
                            if(mon_id===1){
                                mon_txt='Bolivianos';
                            }else if(mon_id===2){
                                mon_txt='Dolares';
                            }
                            pag_filas+='<tr>';
                            pag_filas+='     <td><input type="hidden" class="a_fpag_cue_id" name="a_fpag_cue_id[]" value="'+pag.cue_id+'">'+pag.cue_desc+'</td>';
                            pag_filas+='     <td><input type="hidden" class="a_fpag_monto" name="a_fpag_monto[]" value="'+pag.monto+'">'+pag.monto+'</td>';
                            pag_filas+='     <td><input type="hidden" class="a_fpag_mon_id" name="a_fpag_mon_id[]" value="'+pag.mon_id+'">'+mon_txt+'</td>';
                            pag_filas+='     <td><input type="hidden" class="a_fpag_forma_pago" name="a_fpag_forma_pago[]" value="'+pag.fpago+'">'+pag.fpago+'</td>';                    
                            pag_filas+='     <td><input type="hidden" class="a_fpag_ban_nombre" name="a_fpag_ban_nombre[]" value="'+pag.ban_nombre+'">'+pag.ban_nombre+'</td>';                    
                            pag_filas+='     <td><input type="hidden" class="a_fpag_ban_nro" name="a_fpag_ban_nro[]" value="'+pag.ban_nro+'">'+pag.ban_nro+'</td>';                    
                            pag_filas+='     <td><input type="hidden" class="a_fpag_descripcion" name="a_fpag_descripcion[]" value="'+pag.descripcion+'">'+pag.descripcion+'</td>';
                            pag_filas+='     <td><img class="img-del-fpag" src="images/b_drop.png"></td>';
                            pag_filas+='</tr>';
                        }
                        $('#lista-pagos').append(pag_filas);
                        $('#fpag_dif_cambio').val(data.dif_cambio);
                    }
                </script>
            <!--</div>-->
            <?php
        }
}

?>