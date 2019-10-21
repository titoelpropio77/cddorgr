<?php

class CON_CUENTASX extends BUSQUEDA
{
	var $formulario;
	var $mensaje;
	var $usu;

	function CON_CUENTASX(){
            //permisos
            $this->ele_id=164;
            $this->busqueda();
            if(!($this->verificar_permisos('AGREGAR'))){
                    $this->ban_agregar=false;
            }
            //fin permisos
            $this->num_registros=14;
            $this->coneccion= new ADO();
            $this->arreglo_campos[0]["nombre"]="int_nombre";
            $this->arreglo_campos[0]["texto"]="Nombre";
            $this->arreglo_campos[0]["tipo"]="cadena";
            $this->arreglo_campos[0]["tamanio"]=40;

            $this->arreglo_campos[1]["nombre"]="int_apellido";
            $this->arreglo_campos[1]["texto"]="Apellido";
            $this->arreglo_campos[1]["tipo"]="cadena";
            $this->arreglo_campos[1]["tamanio"]=40;

            $this->arreglo_campos[2]["nombre"]="cux_tipo";
            $this->arreglo_campos[2]["texto"]="Tipo";
            $this->arreglo_campos[2]["tipo"]="comboarray";
            $this->arreglo_campos[2]["valores"]="1,2:X Cobrar,X Pagar";

            $this->arreglo_campos[3]["nombre"]="cux_estado";
            $this->arreglo_campos[3]["texto"]="Estado";
            $this->arreglo_campos[3]["tipo"]="comboarray";
            $this->arreglo_campos[3]["valores"]="Pendiente,Pagado:Pendiente,Pagado";

            $this->link='gestor.php';
            $this->modulo='con_cuentasx';
            $this->formulario = new FORMULARIO();
            $this->formulario->set_titulo('CUENTAS X COBRAR / X PAGAR');
            $this->usu=new USUARIO;
	}

	function cuentas(){
            $this->formulario->dibujar_tarea();
            $this->xpagar();
	}



    function xpagar() {
        if ($_POST['cux_id'] <> "") {
            $conec = new ADO();
//            $sql = "select cja_cue_id from cajero where cja_usu_id = '" . $this->usu->get_id() . "'";
//            $conec->ejecutar($sql);
//            $nume = $conec->get_num_registros();
//            if ($nume > 0) {
//                $obj = $conec->get_objeto();
//                $caja = $obj->cja_cue_id;
                $sql="select * from con_cuentasx where cux_id='".$_POST['cux_id']."'";
                $cuentax=  FUNCIONES::objeto_bd_sql($sql);
                $persona = $this->obtener_persona_cuentasx($_POST['cux_id']);
                $sql = "select sum(ind_capital) as capital from interno_deuda where ind_tabla='con_cuentasx' and ind_tabla_id=" . $_POST['cux_id'] . " and ind_estado='Pagado' order by ind_id";
                $conec->ejecutar($sql);
                $nume = $conec->get_num_registros();
                $objeto = $conec->get_objeto();
                $capital_pagado = $objeto->capital . ' ';
                $monto_cux = $this->obtener_monto_cuentasx($_POST['cux_id']);
                $saldo = $monto_cux - $capital_pagado;
                $interes = $_POST['interes'];
                $capital = $_POST['monto'];
                $cuota = $interes + $capital;
                $saldo = $saldo - $capital;
                $fecha_prog=  FUNCIONES::get_fecha_mysql($_POST['pag_fecha']);
                $sql="select count(*) as campo from interno_deuda where ind_tabla='con_cuentasx' and ind_tabla_id='".$_POST['cux_id']."' and ind_estado<>'Anulado'";
                $num_correlativo=  FUNCIONES::atributo_bd_sql($sql)*1+1;
                $sql = "insert into interno_deuda(ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_cue_id,ind_cco_id,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_fecha_pago,ind_estado,ind_interes,ind_capital,ind_saldo,ind_num_correlativo)
                                                    values('" . $persona . "','" . $cuota . "','" . $_POST['monedacuenta'] . "','" . $_POST['observacion'] . "','" . $cuentax->cux_fecha . "','" . $this->usu->get_id() . "','0','$cuentax->cux_cco_id','con_cuentasx','" . $_GET['id'] . "','" . $fecha_prog. "','" . $fecha_prog. "','Pagado','$interes','$capital','$saldo','$num_correlativo')";
                $conec->ejecutar($sql, false);
                $llave = mysql_insert_id();

    //----------- C O M P R O B A N T E S ----------
                
                $int=FUNCIONES::objeto_bd_sql("select * from interno where int_id='$persona'");
                $_tipo=$cuentax->cux_tipo=='Pagar'?'Pago':'Cobro';
                $glosa = "$_tipo de cuota Nro. $num_correlativo por cuenta X $cuentax->cux_tipo a " . $int->int_nombre." ".$int->int_apellido. ": ". $_POST['observacion'];//$_POST['nombre_persona'];
                include_once 'clases/registrar_comprobantes.class.php';
                
    //            $ges_id = $_SESSION['ges_id'];
                $cc = $cuentax->cux_cco_id;
                $comprobante = new stdClass();
                $comprobante->tipo =$cuentax->cux_tipo=='Pagar'?'Egreso':'Ingreso';
                $comprobante->mon_id = $_POST['monedacuenta'];
                $comprobante->nro_documento = date("Ydm");
                $comprobante->fecha = $fecha_prog;
                $comprobante->ges_id = $_SESSION['ges_id'];
                $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha_prog);
                $comprobante->forma_pago = "Efectivo";
                $comprobante->ban_id = 0;
                $comprobante->ban_char = '';
                $comprobante->ban_nro = '';
                $comprobante->glosa = $glosa;
                $comprobante->referido = $int->int_nombre." ".$int->int_apellido;
                $comprobante->tabla = "interno_deuda";
                $comprobante->tabla_id = $llave;
                
                $cuen_1=0;
                $cuen_2=0;
                if($cuentax->cux_tipo=='Pagar'){                    
                    $cuen_1=$cuentax->cux_cue_egr;
                    $cuen_2=$_POST['cta_act_disp'];
                }elseif($cuentax->cux_tipo=='Cobrar'){                    
                    $cuen_1=$_POST['cta_act_disp'];
                    $cuen_2=$cuentax->cux_cue_ing;
                }
                $comprobante->detalles = array(
                    array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $cuen_1,
                        "glosa" => $glosa, "debe" => $capital, "haber" => ""
                    ),
                    array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $cuen_2,
                        "glosa" => $glosa, "debe" => "", "haber" => $capital
                    )
                );
                if($interes*1>0){
                    $cuen_1=0;
                    $cuen_2=0;
                    if($cuentax->cux_tipo=='Pagar'){                    
                        $cuen_1=$cuentax->cux_cue_egr_int;
                        $cuen_2=$_POST['cta_act_disp'];
                    }elseif($cuentax->cux_tipo=='Cobrar'){                    
                        $cuen_1=$_POST['cta_act_disp'];
                        $cuen_2=$cuentax->cux_cue_ing_int;
                    }
                    $comprobante->detalles[]= array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $cuen_1,
                                                "glosa" => $glosa, "debe" => $interes, "haber" => "");
                         
                    $comprobante->detalles[]=array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $cuen_2,
                                                "glosa" => $glosa, "debe" => "", "haber" => $interes);                    
                }
                COMPROBANTES::registrar_comprobante($comprobante);            
                //----------- C O M P R O B A N T E S ----------

                //Reviso saldo
                $sql = "select ind_saldo,ind_interes,ind_capital from interno_deuda where ind_tabla='cuentasx' and ind_tabla_id=" . $_POST['cux_id'] . " and ind_estado='Pagado' order by ind_id DESC LIMIT 0,1";
                $conec->ejecutar($sql);
                $nume = $conec->get_num_registros();
                $objeto = $conec->get_objeto();
                if ($nume > 0) {
                    $saldo = $objeto->ind_saldo;
                } else {
                    $saldo = $this->obtener_monto_cuentasx($_POST['cux_id']);
                }

                if ($saldo == 0) {
                    $sql = "update con_cuentasx set cux_estado='Pagado' where cux_id='" . $_POST['cux_id'] . "'";
                    $conec->ejecutar($sql, false);
                }

                //$this->nota_de_pago($llave);
                $this->imprimir_pago($llave);
//            } else {
//                $mensaje = 'No puede realizar ningun cobro, por que usted no esta registrado como cajero.';
//
//                $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
//            }
        } else {
            $estado_cux = $this->obtener_estado_cux($_GET['id']);

            if ($estado_cux <> 'Anulado') {
                $url = $this->link . '?mod=' . $this->modulo . '&tarea=CUENTAS&id=' . $_GET['id'];
                $tipocambio = $this->tc;
                $this->datos_cuenta($_GET['id'], $monto, $pagado, $moneda, $des);
                $conec = new ADO();
                $sql = "select sum(ind_capital) as capital_pagado from interno_deuda where ind_tabla='con_cuentasx' and ind_tabla_id=" . $_GET['id'] . " and ind_estado='Pagado' order by ind_id";
                $conec->ejecutar($sql);
                $nume = $conec->get_num_registros();
                $objeto = $conec->get_objeto();
                $capital_pagado = $objeto->capital_pagado;
                $monto_cux = $this->obtener_monto_cuentasx($_GET['id']);
                $saldo = $monto_cux - $capital_pagado;
                //Interes y capital Pagado (Sumatoria)
                $sql = "select sum(ind_capital) as capital_pagado,sum(ind_interes) as interes_pagado from interno_deuda where ind_tabla='con_cuentasx' and ind_tabla_id=" . $_GET['id'] . " and ind_estado='Pagado'";
                $conec->ejecutar($sql);
                $nume = $conec->get_num_registros();
                $objeto = $conec->get_objeto();
                if ($nume > 0) {
                    $interes_pagado = $objeto->interes_pagado;
                    $capital_pagado = $objeto->capital_pagado;
                }
                if ($moneda == '1') {
                    $mon = "Bs";
                } else {
                    $mon = '$us';
                }
                
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
                

                <table align=right border=0><tr><td><a href="gestor.php?mod=cuentasx" title="VOLVER"><img border="0" width="20" src="images/back.png"></a></td></tr></table>

                <table align=right border=0><tr><td><a style="float:left; margin:0 0 0 7px;" class="group" href="sueltos/llamada.php?accion=agregar_divisa"><img border="0" src="images/compra_venta_divisa.png"></a></td></tr></table>
                <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
                <!--FancyBox-->
                <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
                <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
                <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
                <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
                <!--FancyBox-->
                <div id="Contenedor_NuevaSentencia">
                    <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">
                        <div id="FormSent">

                            <div class="Subtitulo">Pago de Cuenta: <?php echo $des; ?></div>
                            <div id="ContenedorSeleccion">
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Monto</div>
                                    <div id="CajaInput">
                                        <input type="text" name="montototal" id="montototal" size="10" value="<?php echo $monto; ?>" readonly="readonly"><?php echo $mon; ?>
                                    </div>
                                    <div id="CajaInput">

                                        <input type="hidden" name="cux_id" id="cux_id" value="<?php echo $_GET['id']; ?>">
                                        <input type="hidden" name="monedacuenta" id="monedacuenta" value="<?php echo $moneda; ?>">
                                    </div>
                                </div>
                                <!--Fin-->								
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Interes Pagado hasta la Fecha</div>
                                    <div id="CajaInput">
                                        <input type="text" name="interes_pagado" id="interes_pagado" size="10" value="<?php echo $interes_pagado; ?>" readonly="readonly"><?php echo $mon; ?>
                                    </div>
                                </div>
                                <!--Fin-->
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Capital Pagado hasta la Fecha</div>
                                    <div id="CajaInput">
                                        <input type="text" name="capital_pagado" id="capital_pagado" size="10" value="<?php echo $capital_pagado; ?>" readonly="readonly"><?php echo $mon; ?>
                                    </div>
                                </div>
                                <!--Fin-->
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Saldo</div>
                                    <div id="CajaInput">
                                        <input type="text" name="saldo" id="saldo" size="10" value="<?php echo $saldo; ?>" readonly="readonly"><?php echo $mon; ?>&nbsp;&nbsp;&nbsp;
                                    </div>
                                </div>
                                <!--Fin-->
                                <div id="ContenedorDiv">==================================================================================</div>
                                <!--Inicio-->
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Fecha:</div>
                                    <div id="CajaInput">                                    
                                        <input class="caja_texto" name="pag_fecha" id="pag_fecha" size="12" value="<?php if (isset($_POST['pag_fecha'])) echo $_POST['pag_fecha'];
                                        else echo date("d/m/Y"); ?>" type="text">
                                    </div>
                                </div>
                                <script>
                                    jQuery(function($) {
                                        $("#pag_fecha").mask("99/99/9999");                                        
                                    });
                                </script>
                                <!--Fin-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" >Cta. Act. Disponible:</div>
                                    <div id="CajaInput">
                                        <select name="cta_act_disp" id="cta_act_disp" style="min-width: 150px">
                                            <option value="0">Seleccione</option>
                                            <?php foreach($cuentas_act_disp as $cta){ ?>
                                            <option value="<?=$cta->id?>"><?=$cta->descripcion?></option>
                                            <?php }?>
                                        </select>
                                        &nbsp;<span style="color: #ff0000;" hidden="" id="msj_ret_ctas_act_disp">&nbsp;</span>
                                    </div>
                                </div>
                                <!--Fin-->
                                
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Capital a Pagar</div>
                                    <div id="CajaInput">
                                        <input type="text" name="monto" id="monto" size="10" value="" autocomplete="off"><?php echo $mon; ?>&nbsp;&nbsp;&nbsp;Nota:<font color="#FF0000">( Al Realizar el ultimo pago debe ser identico al Saldo)</font>
                                    </div>
                                </div>
                                <!--Fin-->
                                <?php
                                //$monto_cux = $this->obtener_monto_cuentasx($_GET['id']);
                                $cuentax= FUNCIONES::objeto_bd_sql("select * from con_cuentasx where cux_id='".$_GET['id']."'");
                                $hidden="";
                                if(!($cuentax->cux_interes)){
                                    $hidden='hidden=""';
                                }    
                                ?>
                                <!--Inicio-->
                                <div id="ContenedorDiv" <?php echo $hidden;?>>
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Interes a Pagar</div>
                                    <div id="CajaInput">
                                        <input type="text" name="interes" id="interes" size="10" value="<?php echo ($this->obtener_porc_interes($_GET['id']) / 100) * $saldo; ?>" ><?php echo $mon; ?>&nbsp;<?php echo $this->obtener_porc_interes($_GET['id']) . '%'; ?>
                                    </div>
                                </div>
                                <!--Fin-->
                                
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Monto a Pagar</div>
                                    <div id="CajaInput">
                                        <input type="text" name="monto_a_pagar" id="monto_a_pagar" size="10" value="" readonly="">
                                    </div>
                                </div>
                                <!--Fin-->
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" ><span class="flechas1">*</span>Observación</div>
                                    <div id="CajaInput">
                                        <input type="text" name="observacion" id="observacion" size="30" value="">
                                    </div>
                                </div>
                                <!--Fin-->
                            </div>

                            <div id="ContenedorDiv">
                                <div id="CajaBotones">
                                    <center>
                <?php
                if (!($ver)) {
                    ?>
                                            <input type="button" class="boton" name="" value="Pagar Cuenta" onclick="javascript:enviar_formulario();">
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
                </div>
                <script type="text/javascript">
                    $(document).ready(function() {
                        $("a.group").fancybox({
                            'hideOnContentClick': false,
                            'overlayShow': true,
                            'zoomOpacity': true,
                            'zoomSpeedIn': 300,
                            'zoomSpeedOut': 200,
                            'overlayOpacity': 0.5,
                            'frameWidth': 700,
                            'frameHeight': 350,
                            'type': 'iframe'
                        });

                        $('a.close').click(function() {
                            $(this).fancybox.close();
                        });

                    });
                
                    function enviar_formulario() {
                        
                        var pag_fecha = $('#pag_fecha').val();
                        if (pag_fecha !== "") {
                            $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: pag_fecha}, function(respuesta) {
                                var dato = JSON.parse(respuesta);
                                if (dato.response === "ok") {
                                    var act_disp=$("#cta_act_disp option:selected").val();
                                    if(act_disp==='0'){
                                        $.prompt('Seleccione una cuenta de activo disponible.', {opacity: 0.8});
                                        return false;
                                    }
                                    var monto = parseFloat(document.frm_sentencia.monto.value);
                                    var interes = parseFloat(document.frm_sentencia.interes.value);
                                    var saldo = parseFloat(document.frm_sentencia.saldo.value);
                                    if (monto >= 0 && interes >= 0){
                                        if (monto < saldo){
                                            document.frm_sentencia.submit();
                                        }
                                        else{                                            
                                            $.prompt('El Monto debe ser Menor o Igual que el saldo y no Mayor.', {opacity: 0.8});
                                        }
                                    }else{
                                        $.prompt('Para registrar el pago debe introducir el monto.', {opacity: 0.8});
                                    }
                                } else if (dato.response === "error") {
                                    $.prompt(dato.mensaje);                                    
                                }
                            });
                        }else{
                            $.prompt('La fecha del pago de la cuota no debe ser vacio.');
                            
                        }
                        return false;
                    }
                    

                    function ejecutar_script(id, tarea, cux_id){
                        
                        var txt = 'Esta seguro de anular este pago ?';
                        $.prompt(txt, {
                            buttons: {Aceptar: true, Cancelar: false},
                            callback: function(v, m, f) {

                                if (v) {
//                                    alert('gestor.php?mod=con_cuentasx&tarea=' + tarea + '&acc=anular&cup_id=' + id + '&id=' + cux_id);
//                                    return false;
                                    location.href = 'gestor.php?mod=con_cuentasx&tarea=' + tarea + '&acc=anular&cup_id=' + id + '&id=' + cux_id;
                                }

                            }

                        });
                    }

                    function calcular_monto(e)
                    {
                        var interes = parseFloat(document.frm_sentencia.interes.value);
                        var capital = parseFloat(document.frm_sentencia.monto.value);
                        document.frm_sentencia.monto_a_pagar.value = (capital + interes);

                    }
                    $("#monto,#interes").live('keypress', function(e) {
                        if (e.keyCode !== 13 && e.keyCode !== 8 && e.keyCode !== 46 && e.keyCode !== 37 && e.keyCode !== 39 
                                && e.keyCode !== 35 && e.keyCode !== 36 && e.keyCode !== 9 && !(e.keyCode>=112 && e.keyCode<=123)) {
                            var valor = $(this).val();
                            var char = String.fromCharCode(e.which);
                            valor = valor + char;                        
                            if (!/^([0-9])*[.]?[0-9]*$/.test(valor)) {
                                return false;
                            }
                        }
                    });
                    $("#monto,#interes").live('keyup', function(e) {
                        var monto=$("#monto").val();
                        var interes=$("#interes").val();
                        $("#monto_a_pagar").val(monto*1+interes*1);
                    });
                </script>

                <?php
                $this->dibujar_encabezado_pagos();
                $this->mostrar_busqueda_pagos();
            } else {
                $mensaje = 'No puede realizar Cobros/Pagos por que la Cuenta x Cobrar/Pagar se encuentra en estado <b>Anulado</b>.';
                $tipo = 'Error';
                $this->formulario->dibujar_mensaje($mensaje, $this->link . '?mod=' . $this->modulo, '', $tipo);
            }
        }
    }

	
	function obtener_estado_cux($cux_id){
		$conec= new ADO();
		$sql="select cux_estado from con_cuentasx where cux_id='$cux_id'";		
		$conec->ejecutar($sql);
		$num=$conec->get_num_registros();		
		$objeto=$conec->get_objeto();				
		return $objeto->cux_estado;
	}

	function obtener_persona_cuentasx($cux_id)
	{
		$conec= new ADO();

		$sql="select cux_int_id from con_cuentasx where cux_id=$cux_id";

		$conec->ejecutar($sql);

		$nume=$conec->get_num_registros();

		if($nume > 0)
		{
			$objeto = $conec->get_objeto();
			
			return $objeto->cux_int_id;
		}
	}
	
	function obtener_monto_cuentasx($cux_id)
	{
		$conec= new ADO();

		$sql="select cux_monto from con_cuentasx where cux_id=$cux_id";

		$conec->ejecutar($sql);

		$nume=$conec->get_num_registros();

		if($nume > 0)
		{
			$objeto = $conec->get_objeto();
			
			return $objeto->cux_monto;
		}
	}

	function dibujar_encabezado_pagos()
	{

		?><div style="clear:both;"></div><center>
		<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
			<tr>

				<td><center><strong><h3>LISTADO DE PAGOS</h3></strong></center></p></td>

			</tr>

		</table>
        <br />
		<table class="tablaLista" cellpadding="0" cellspacing="0" width="60%">
			<thead>
				<tr>
					<th >
						Nro.
					</th>
					<th >
						Persona
					</th>
					<th >
						Concepto
					</th>
					<th >
						Moneda
					</th>
                                        <th >
						Interes
					</th>
                                        <th >
						Capital
					</th>
					<th >
						Monto
					</th>
                                        <th >
						Estado
					</th>
					<th class="tOpciones" width="100px">
						Opciones
					</th>

				</tr>
			</thead>
			<tbody>
		<?PHP

	}

	function mostrar_busqueda_pagos(){
            $conversor = new convertir();		
            $conec=new ADO();		
            $sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_monto_parcial,int_nombre,int_apellido,ind_fecha_programada,ind_fecha_pago,ind_interes,ind_capital,ind_saldo 
            FROM 
            interno_deuda 
            inner join interno on (ind_tabla='con_cuentasx' and(ind_estado='Pendiente' or ind_estado='Pagado') and ind_tabla_id='".$_GET['id']."' and ind_int_id=int_id) order by ind_id ASC";

            $conec->ejecutar($sql);

            $num=$conec->get_num_registros();
            $sumatorio=0;
            for($i=0;$i<$num;$i++)
            {
                    $objeto=$conec->get_objeto();
                    $sumatorio+=$objeto->cup_monto;
                    echo '<tr class="busqueda_campos">';

                    ?>

                            <td align="left">
                                    <center>
                                    <?php echo $i+1;?>
                                    </center>
                            </td>

            <td align="left">
                                    <center>
                                    <?php echo $objeto->int_nombre.' '.$objeto->int_apellido;?>
                                    </center>
                            </td>

            <td align="left">
                                    <center>
                                    <?php echo $objeto->ind_concepto; ?>
                                    </center>
                            </td>

            <td align="left">
                                    <center>
                                    <?php if($objeto->ind_moneda==1){echo "Bolivianos"; } else { echo "Dolares"; }?>
                                    </center>
                            </td>

                            <td align="left">
                                    <center>
                                    <?php echo $objeto->ind_interes;?>

                                    </center>
                            </td>

            <td align="left">
                                    <center>
                                    <?php echo $objeto->ind_capital;?>					
                                    </center>
                            </td>

                            <td align="left">
                                    <center>
                                    <?php echo $objeto->ind_monto;?>

                                    </center>
                            </td>

            <td align="left">
                                    <center>
                                    <?php echo $objeto->ind_estado;?>

                                    </center>
                            </td>

            <!--
            <td align="left">
                                    <center>
                                    <?php echo $objeto->ind_saldo;?>

                                    </center>
                            </td>
            -->


                            <td>
                                <center>
                                    <table>
                                        <tr>
                                            <td>
                                                <a href="gestor.php?mod=con_cuentasx&tarea=CUENTAS&acc=verpago&id=<?php echo $objeto->ind_id;?>"><img src="images/ver.png" alt="VER" title="VER" border="0"></a>
                                            </td>
                                            <td>
                                                <?php if($i==$num-1){?>
                                                <a href="javascript:void(0);" onclick="ejecutar_script('<?php echo $objeto->ind_id;?>','CUENTAS','<?php echo $_GET['id'];?>');" target="contenido" ><img src="images/anular.png" alt="ANULAR" title="ANULAR" border="0"></a>
                                                <?php }?>
                                            </td>
                                        </tr>
                                    </table>
                                </center>
                            </td>

                    <?php

                    echo "</tr>";

                    $conec->siguiente();

            }
            echo "</tbody>";
                    ?>
                    <tfoot>
                            <tr>
                            <td colspan="5" >&nbsp;</td>
            <td><b>Total</b></td>
                            <td><? echo $sumatorio; ?></td>
            <td><? echo $sumatorio; ?></td>
            <td></td>
            </tr>

                    </tfoot>
                    <?

            echo "</table></center><br>";
	}

	function datos_cuenta_historial($cuenta,$fecha,&$monto,&$pagado,&$moneda,&$des="")
	{
		$conec= new ADO();

		$sql = "select cux_monto,cux_moneda,cux_concepto from cuentasx where cux_id='$cuenta'";

		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();

		$moneda=$objeto->cux_moneda;

		$monto=$objeto->cux_monto;

		$des=$objeto->cux_concepto;

		$sql = "select sum(cup_monto) as pagado from cuentax_pago where cup_cux_id='$cuenta' AND cup_fecha <= '$fecha' AND cup_estado='Pagado'";

		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();

		$pagado=$objeto->pagado;
	}

	function anular_pago_anulado($id){
            
            include_once 'clases/registrar_comprobantes.class.php';
            $bool=COMPROBANTES::anular_comprobante('interno_deuda', $id);
            if(!$bool){
                $mensaje="El registro no puede ser Anulado por que el periodo en el que fue realizado el pago fue cerrado.";
                $tipo='Error';			
                $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo,'',$tipo);
                return;
            }
            $conec= new ADO();

            $sql="update interno_deuda set
                    ind_estado='Anulado'
                    where ind_id = '$id'";

            $conec->ejecutar($sql);

            $mensaje='Pago Anulado Correctamente!!!';

            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	
	function anular_pago_pendiente($id){
            include_once 'clases/registrar_comprobantes.class.php';
            $bool=COMPROBANTES::anular_comprobante('interno_deuda', $id);
//            echo $id;
            if(!$bool){
                $mensaje="El registro no puede ser Anulado por que el periodo en el que fue realizado el pago fue cerrado.";
                $tipo='Error';			
                $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo,'',$tipo);
                return;
            }
            $conec= new ADO();
            $sql="update interno_deuda set
                    ind_estado='Pendiente'
                    where ind_id = '$id'";
            $conec->ejecutar($sql);
            $mensaje='Pago Anulado Correctamente!!!';
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}

	function mostrar_nota_pago_historial($pago,$fecha){
            $conec= new ADO();
            $sql = "SELECT
            cup_cux_id,cup_monto,cup_moneda,cup_tipo_cambio,cup_fecha,cup_usu_id,cux_concepto,cux_tipo
            FROM
            cuentax_pago INNER JOIN cuentasx ON (cup_cux_id=cux_id)
            WHERE cup_id='$pago' AND cup_fecha <= '$fecha'";
            echo $sql;
            $conec->ejecutar($sql);

            $num=$conec->get_num_registros();

            $objeto=$conec->get_objeto();

            $this->datos_cuenta_historial($objeto->cup_cux_id,$objeto->cup_fecha,$monto,$pagado,$moneda);

            if($moneda=='1')
                    $moneda="Bs";
            else
                    $moneda='$us';

            ////
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

            $myday = setear_fecha(strtotime($objeto->cup_fecha));
            ////

            echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
                              c.document.write('.$extra1.');
                              var dato = document.getElementById('.$pagina.').innerHTML;
                              c.document.write(dato);
                              c.document.write('.$extra2.'); c.document.close();
                              ">
                            <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                            </a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=CUENTAS&id='.$_POST['cux_id'].'\';"></td></tr></table>
                            ';
            ?>
            <br><br><div id="contenido_reporte" style="clear:both;";>
                    <center>
                    <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                            <tr>
                                <td  width="40%">
                                <strong><?php echo _nombre_empresa; ?></strong><BR>
                                    <strong>Santa Cruz - Bolivia</strong>
                               </td>
                                <td><center><strong><h3>NOTA DE <?php if($objeto->cux_tipo==1) echo 'COBRO'; else echo 'PAGO'; ?></h3></strong></center></p></td>
                                <td  width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
                              </tr>
                               <tr>
                                <td colspan="2">
                                <strong>Fecha: </strong> <?php echo $myday;?> <br>
                                    <strong>Tipo de Cambio: </strong> <?php echo $objeto->cup_tipo_cambio;?> <br>
                                    <strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cup_usu_id);?> <br><br>
                                    </td>
                                <td align="right">

                                    </td>
                              </tr>

                    </table>
                    <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                            <thead>
                            <tr>
                                    <th>Cuenta</th>
                                    <th>Monto</th>
                                    <th>Moneda</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                    <td><?php echo $objeto->cux_concepto;?></td>
                                    <td><?php echo $objeto->cup_monto;?></td>
                                    <td><?php if($objeto->cup_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
                            </tr>
                            </tbody></table>
                            <br>
                            <table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >

                               <tr>
                                <td colspan="2">
                                <strong>Monto: </strong> <?php echo $monto.' '.$moneda;?> <br>
                                    <strong>Pagado: </strong> <?php echo $pagado.' '.$moneda;;?> <br>
                                    <strong>Saldo: </strong> <?php echo ($monto-$pagado).' '.$moneda;;?> <br><br>
                                    </td>
                              </tr>

                    </table>

                    <br><br><br><br>
                            <table border="0"  width="90%" style="font-size:12px;">
                            <tr>
                                    <td width="50%" align ="center">-------------------------------------</td>
                                    <td width="50%" align ="center">-------------------------------------</td>
                            </tr>
                            <tr>
                                    <td align ="center"><strong>Recibi Conforme</strong></td>
                                    <td align ="center"><strong>Entregue Conforme</strong></td>
                            </tr>
                            </table>

                    </center></div><br>

            <?php

	}

	function mostrar_resumen(){

		$conec= new ADO();
		$conversor = new convertir();
		
		
		$sql="select
		*
		from
		cuentasx
		inner join interno on (int_id=cux_int_id)
		where cux_id=".$_GET['id'];

		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		$persona=$objeto->cux_int_id;
		
		$tipo=$objeto->cux_tipo;
		
		
		////
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

		// $myday = setear_fecha(strtotime($objeto->cup_fecha));
		$myday = setear_fecha(strtotime(date('Y-m-d')));
		////

		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=ACCEDER\';"></td></tr></table>
				';
		?>
		<br><br><div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td  width="40%">
					    <strong><?php echo _nombre_empresa; ?></strong><br/>
						<strong>Santa Cruz - Bolivia</strong>
				    </td>
				    <td><center><strong>
				    	<h3>RESUMEN</h3>
 				    	<h3><?php if($tipo=='1') echo 'CUENTA X COBRAR'; else echo 'CUENTA X PAGAR'; ?></h3> 
				    </strong></center></p></td>
				    <td  width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				</tr>
 			    <tr>
				    <td colspan="2">
				    <strong>Fecha: </strong> <?php echo $myday;?> <br>
 					<strong>Persona: </strong> <?php echo $objeto->int_nombre.' '.$objeto->int_apellido;?> <br><br> 
					</td>
				    <td align="right">

					</td>
				</tr>
			</table>

			<table   width="70%"  class="" cellpadding="0" cellspacing="0">
				<tr>
					<td> <center><h3><?php if($tipo=='1') echo 'COBROS'; else echo 'PAGOS'; ?> DE CAPITAL</h3></center></td>
				</tr>
			</table>

			<br>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Fecha Programada</th>
                    <th>Fecha Pago</th>
                    <th>Concepto</th>
					<th>Monto Capital</th>
					<th>Moneda</th>
                    <th>Estado</th>
				</tr>
				</thead>
					<tbody>
						<?php
						$sql="select
						*
						from
						interno_deuda
						where ind_estado='Pagado' and ind_tabla='cuentasx' and ind_tabla_id=".$_GET['id']." order by ind_id asc";
				
						$conec->ejecutar($sql);
				
						$num=$conec->get_num_registros();
						
						$sumcapital=0;
				
						for($i=0;$i<$num;$i++)
						{
							$objeto=$conec->get_objeto();
                        ?>
                        <tr>
                            <td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);?></td>
                            <td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_pago);?></td>
                            <td><?php echo $objeto->ind_concepto;?>&nbsp;</td>
                            <td><?php echo number_format($objeto->ind_capital,2,',','.'); $sumcapital+=$objeto->ind_capital;?></td>
                            <td><?php if($objeto->ind_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
                            <td><?php echo $objeto->ind_estado;?></td>
                        </tr>
						<?
							$conec->siguiente();
						}
                        ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td align="right"><b>Total:</b>&nbsp;</td>
                            <td><b><?php echo number_format($sumcapital,2,',','.');?></b></td>
                            <td><b><?php if($objeto->ind_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></b></td>
                            <td>&nbsp;</td>
                        </tr>
					</tbody>
                </table>
				<br>
                <br /><br />

			<table   width="70%"  class="" cellpadding="0" cellspacing="0">
				<tr>
					<td> <center><h3><?php if($tipo=='1') echo 'COBROS'; else echo 'PAGOS'; ?> DE INTERESES</h3></center></td>
				</tr>
			</table>
			<br>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Fecha Programada</th>
                    <th>Fecha Pago</th>
                    <th>Concepto</th>
					<th>Monto Interes</th>
					<th>Moneda</th>
                    <th>Estado</th>
				</tr>
				</thead>
				<tbody>

					<?php
					$sql="select
					*
					from
					interno_deuda
					where ind_estado='Pagado' and ind_tabla='cuentasx' and ind_tabla_id=".$_GET['id']." order by ind_id asc";
			
					$conec->ejecutar($sql);
			
					$num=$conec->get_num_registros();
					
					$suminteres=0;
			
					for($i=0;$i<$num;$i++)
					{
			
						$objeto=$conec->get_objeto();
                    ?>
                    <tr>
                        <td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);?></td>
                        <td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_pago);?></td>
                        <td><?php echo $objeto->ind_concepto;?>&nbsp;</td>
                        <td><?php echo number_format($objeto->ind_interes,2,',','.');$suminteres+=$objeto->ind_interes;?></td>
                        <td><?php if($objeto->ind_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
                        <td><?php echo $objeto->ind_estado;?></td>
                    </tr>
					<?
						$conec->siguiente();
					}
                    ?>
					<tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="right"><b>Total:</b>&nbsp;</td>
                        <td><b><?php echo number_format($suminteres,2,',','.');?></b></td>
                        <td><b><?php if($objeto->ind_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></b></td>
                        <td>&nbsp;</td>
                    </tr>

				</tbody>
     		</table>
				<br>
			</center></div><br/>

		<?php
	}

	function nota_de_pago($pago)
	{
		$conec= new ADO();

		$sql = "select
		cup_cux_id,cup_monto,cup_moneda,cup_tipo_cambio,cup_fecha,cup_usu_id,cux_concepto,cux_tipo
		from
		cuentax_pago inner join cuentasx on (cup_cux_id=cux_id)
		where cup_id='$pago'";

		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();

		$objeto=$conec->get_objeto();

		$this->datos_cuenta($objeto->cup_cux_id,$monto,$pagado,$moneda);

		if($moneda=='1')
			$moneda="Bs";
		else
			$moneda='$us';

		////
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

		$myday = setear_fecha(strtotime($objeto->cup_fecha));
		////

		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=CUENTAS&id='.$_POST['cux_id'].'\';"></td></tr></table>
				';
		?>
		<br><br><div id="contenido_reporte" style="clear:both;">
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td  width="40%">
				    <strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong>
				   </td>
				    <td><center><strong><h3>NOTA DE <?php if($objeto->cux_tipo==1) echo 'COBRO'; else echo 'PAGO'; ?></h3></strong></center></p></td>
				    <td  width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
				    <strong>Fecha: </strong> <?php echo $myday;?> <br>
					<strong>Tipo de Cambio: </strong> <?php echo $objeto->cup_tipo_cambio;?> <br>
					<strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cup_usu_id);?> <br><br>
					</td>
				    <td align="right">

					</td>
				  </tr>

			</table>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Concepto</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><?php echo $objeto->cux_concepto;?></td>
					<td><?php echo $objeto->cup_monto;?></td>
					<td><?php if($objeto->cup_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>
				</tbody></table>
				<br>
				<table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >

				   <tr>
				    <td colspan="2">
				    <strong>Monto: </strong> <?php echo $monto.' '.$moneda;?> <br>
					<strong>Pagado: </strong> <?php echo $pagado.' '.$moneda;;?> <br>
					<strong>Saldo: </strong> <?php echo ($monto-$pagado).' '.$moneda;;?> <br><br>
					</td>
				  </tr>

			</table>

			<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>

			</center></div><br>

		<?php

	}

	function nombre_interno($id_interno)
	{
		$conec= new ADO();

		$sql="select int_nombre,int_apellido from interno where int_id=$id_interno";

		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();

		return $objeto->int_nombre.' '.$objeto->int_apellido;

	}
	
	function nombre_persona($usuario)
	{
		$conec= new ADO();

		$sql="select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";

		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();

		return $objeto->int_nombre.' '.$objeto->int_apellido;

	}

	function datos_cuenta($cuenta,&$monto,&$pagado,&$moneda,&$des=""){
		$conec= new ADO();

		$sql = "select cux_monto,cux_moneda,cux_concepto from con_cuentasx where cux_id='$cuenta'";

		$conec->ejecutar($sql);
		$objeto=$conec->get_objeto();
		$moneda=$objeto->cux_moneda;
		$monto=$objeto->cux_monto;
		$des=$objeto->cux_concepto;
		$sql = "select sum(ind_monto) as pagado from interno_deuda where ind_tabla='con_cuentasx' and ind_tabla_id='$cuenta' AND ind_estado='Pagado'";
		$conec->ejecutar($sql);
		$objeto=$conec->get_objeto();
		$pagado=$objeto->pagado*1;                
        }

	function dibujar_busqueda(){
		?>
		<script>
		function ejecutar_script(id,tarea){
				var txt = 'Esta seguro de anular la Deuda de la Persona?';

				$.prompt(txt,{
					buttons:{Anular:true, Cancelar:false},
					callback: function(v,m,f){

						if(v){
								location.href='gestor.php?mod=con_cuentasx&tarea='+tarea+'&id='+id;
						}

					}
				});
			}

		</script>
		<?php
		$this->formulario->dibujar_cabecera();

		$this->dibujar_listado();
	}


	function set_opciones(){

		$nun=0;

		if($this->verificar_permisos('VER'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='VER';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_search.png';
			$this->arreglo_opciones[$nun]["nombre"]='VER';
			$nun++;
		}

		if($this->verificar_permisos('ANULAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='ANULAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/anular.png';
			$this->arreglo_opciones[$nun]["nombre"]='ANULAR';
			$this->arreglo_opciones[$nun]["script"]='ok';
			$nun++;
		}

		if($this->verificar_permisos('CUENTAS'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='CUENTAS';
			$this->arreglo_opciones[$nun]["imagen"]='images/cuenta.png';
			$this->arreglo_opciones[$nun]["nombre"]='PAGOS';
			$nun++;
		}

		if($this->verificar_permisos('PAGO_INTERES_CUENTASX'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='PAGO_INTERES_CUENTASX';
			$this->arreglo_opciones[$nun]["imagen"]='images/pago_interes.png';
			$this->arreglo_opciones[$nun]["nombre"]='PAGO DE INTERESES';
			$nun++;
		}
		if($this->verificar_permisos('RESUMEN'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='RESUMEN';
			$this->arreglo_opciones[$nun]["imagen"]='images/historial.jpg';
			$this->arreglo_opciones[$nun]["nombre"]='RESUMEN';
			$nun++;
		}

	}

	function dibujar_listado(){
		$sql="SELECT cux_id,cux_monto,cux_moneda,cux_concepto,cux_fecha,cux_estado,int_nombre,int_apellido,cux_tipo,cux_tipo_plan,cco_descripcion
                    FROM con_cuentasx, interno, con_cuenta_cc where cux_cco_id=cco_id and cux_int_id=int_id
		";
		$this->set_sql($sql,' order by cux_id desc');
		$this->set_opciones();
		$this->dibujar();

	}

	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Persona</th>
				<th>Centro Costo</th>
				<th>Tipo</th>
				<th>Tipo de Plan</th>
				<th>Observación</th>
				<th>Monto</th>
				<th>Pagado</th>
				<th>Saldo</th>
				<th>Fecha</th>
				<th>Estado</th>
	            <th class="tOpciones" width="100px">Opciones</th>
			</tr>

		<?PHP
	}

	function mostrar_busqueda()
	{
		$conversor = new convertir();

		for($i=0;$i<$this->numero;$i++)
			{

				$objeto=$this->coneccion->get_objeto();
				echo '<tr>';

					echo "<td>";
                                            echo $objeto->int_nombre.' '.$objeto->int_apellido;
					echo "&nbsp;</td>";
					echo "<td>";
                                            echo $objeto->cco_descripcion;
					echo "&nbsp;</td>";
					echo "<td>";
                                            echo "X $objeto->cux_tipo";
					echo "&nbsp;</td>";
					echo "<td>";
                                            echo  $objeto->cux_tipo_plan;
					echo "&nbsp;</td>";
					echo "<td>";
                                            echo $objeto->cux_concepto;
					echo "&nbsp;</td>";
					$this->datos_cuenta($objeto->cux_id,$monto,$pagado,$moneda);
					
					echo "<td>";
                                            echo $monto; if($objeto->cux_moneda=='1') echo ' Bs'; else echo ' $us';
					echo "&nbsp;</td>";
					
					
					echo "<td>";
                                            $monto_pag=$this->montopagado($objeto->cux_id);
                                            if($monto_pag==0) echo ''; else echo $monto_pag;
					echo "&nbsp;</td>";
					echo "<td>";
                                            echo $this->saldo_capital($objeto->cux_id);
					echo "&nbsp;</td>";
					
					/*
					echo "<td>";
						echo $pagado; if($objeto->cux_moneda=='1') echo ' Bs'; else echo ' $us';
					echo "&nbsp;</td>";

					echo "<td>";
						echo $monto-$pagado; if($objeto->cux_moneda=='1') echo ' Bs'; else echo ' $us';
					echo "&nbsp;</td>";
					*/



					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->cux_fecha);
					echo "&nbsp;</td>";

					echo "<td>";
						echo $objeto->cux_estado;
					echo "&nbsp;</td>";

					echo "<td>";
						echo $this->get_opciones($objeto->cux_id);
					echo "</td>";
				echo "</tr>";

				$this->coneccion->siguiente();
			}
	}
	
	
	
	function anular()
	{
		$conec= new ADO();
		
		$sql = "select cux_tipo,cux_estado from cuentasx where cux_id='".$_GET['id']."'"; 
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		if($objeto->cux_estado <> 'Anulado')
		{
			$sql = "select ind_id from interno_deuda where ind_tabla='cuentasx' and ind_tabla_id='".$_GET['id']."' and ind_estado='pagado' "; 
		
			$conec->ejecutar($sql);
			
			$num=$conec->get_num_registros();
			
			if($num > 0)
			{
				$mensaje='La Cuenta x Cobrar/Pagar no puede ser anulado, por que ya tiene pagos registrados!!!';
				
				$tipo='Error';
			}
			else
			{
				$sql="update interno_deuda set 
							ind_estado='Anulado'
							where 
							ind_tabla='cuentasx' and
							ind_tabla_id = '".$_GET['id']."'";
		
		
				$conec->ejecutar($sql);
				
				$this->anular_ok($conec);
				
				$mensaje='Cuenta x Cobrar/Pagar Anulado Correctamente!!!';
				
				$tipo='Correcto';
			}
				
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo,'',$tipo);
		}
		else
		{
			$mensaje='La Cuenta x Cobrar/Pagar no puede ser Anulada por que ya fue Anulada.';
					
			$tipo='Error';
			
			$this->formulario->dibujar_mensaje($mensaje,$this->link.'?mod='.$this->modulo,'',$tipo);
		}
	}
	
	function anular_ok($conec)
	{
		$sql="update cuentasx set 
							cux_estado='Anulado'
							where cux_id = '".$_GET['id']."'";
		
		
		$conec->ejecutar($sql);
						
		include_once("clases/registrar_comprobantes.class.php");
			 
		$comp = new COMPROBANTES();	
		
		$comp->anular_comprobante_tabla('cuentasx',$_GET['id']);
		
	}
	
	function montopagado($id)
	{
		$conec=new ADO();
		
		//Sumatoria del monto de los registros que estan en estado 'Pagados'
		$sql="select sum(ind_monto) as monto from interno_deuda where ind_tabla='cuentasx' and ind_estado='Pagado' and ind_tabla_id='$id'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$monto_totales=$objeto->monto;
		
		
		//Sumatoria de los montos Parciales que estan en estado 'Pendiente'
		$sql="select sum(ind_monto_parcial) as monto_parcial from interno_deuda where ind_tabla='cuentasx' and ind_estado='Pendiente' and ind_tabla_id='$id'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$monto_parciales=$objeto->monto_parcial;
		
		
		return ($monto_totales + $monto_parciales);
	}
	
	function saldo_capital($id)
	{
		$conec=new ADO();
		
		$sql="select ind_saldo from interno_deuda where ind_tabla='cuentasx' and ind_estado='Pagado' and ind_tabla_id='$id' order by ind_id desc";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->ind_saldo;
	}

	function datos()
	{
		if($_POST){
                    return true;
		}
		else{
                    return false;
                }
	}

	function formulario_tcp($tipo) {
            $conec = new ADO();
            $sql = "select * from interno";
            $conec->ejecutar($sql);
            $nume = $conec->get_num_registros();
            $personas = 0;
            if ($nume > 0) {
                $personas = 1;
            }
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
            $this->formulario->dibujar_tarea();
            $tipocambio = $this->tc;

            if ($this->mensaje <> "") {
                $this->formulario->dibujar_mensaje($this->mensaje);
            }
            ?>
            <script>
                function generar_pagos(){
                    var tipo = document.frm_cuentax.cux_tipo_plan.options[document.frm_cuentax.cux_tipo_plan.selectedIndex].value;
                    if (tipo === 'Normal'){
                        var cux_meses_plazo = document.frm_cuentax.cux_meses_plazo.value;
                        var monto = parseFloat(document.frm_cuentax.monto.value);                        
                        var fecha = document.frm_cuentax.ven_fecha.value;
                        var moneda = document.frm_cuentax.moneda.value;
                        var interes_mensual = document.frm_cuentax.interes_mensual.value;
                        var observacion = document.frm_cuentax.ind_concepto.value;
                        if ((cux_meses_plazo > 0) && monto > +0 && fecha !== '' && moneda !== '' && interes_mensual >= 0){
                            var valores = "tarea=plan_pagos_cuentasx&monto=" + monto + "&cux_meses_plazo=" + cux_meses_plazo +"&moneda=" + moneda + "&fecha=" + fecha + "&interes_mensual=" + interes_mensual + "&observacion=" + observacion;
                            ejecutar_ajax('ajax.php', 'plan_de_pagos', valores, 'POST');
                            $("#norm_mtotal").val(monto);
                        }else{
                            $('#tprueba tbody').remove();
                            $.prompt('-La Fecha no debe estar vacia.</br>-El valor del monto debe ser mayor a cero.</br>-El Nro de Cuotas debe ser mayor a cero.', {opacity: 0.8});
                        }
                    }else{
                        $.prompt('El tipo de Plan de Pago no es de Tipo "Normal", no necesita generar un plan de pagos.', {opacity: 0.8});
                    }
                }

                function ValidarMoneda(e){
                    evt = e ? e : event;
                    tcl = (window.Event) ? evt.which : evt.keyCode;
                    if ((tcl < 48 || tcl > 57) && (tcl !== 8 && tcl !== 0 && tcl !== 46)){
                        return false;
                    }
                    return true;
                }
                function validar_cuentasx(){
                    var persona = document.frm_cuentax.interno.value;
                    var fecha = document.frm_cuentax.ven_fecha.value;
                    var tipo = document.frm_cuentax.tipo.options[document.frm_cuentax.tipo.selectedIndex].value;                    
                    var interes = document.frm_cuentax.cux_interes.options[document.frm_cuentax.cux_interes.selectedIndex].value;                    
                    var cux_tipo_plan = document.frm_cuentax.cux_tipo_plan.options[document.frm_cuentax.cux_tipo_plan.selectedIndex].value;                    
                    var cco_id = document.frm_cuentax.cco_id.options[document.frm_cuentax.cco_id.selectedIndex].value;
                    var id_cuenta_ing =document.frm_cuentax.id_cuenta_ing.value;
                    var id_cuenta_egr =document.frm_cuentax.id_cuenta_egr.value;
                    var ind_concepto =document.frm_cuentax.ind_concepto.value;
                    var moneda = document.frm_cuentax.moneda.options[document.frm_cuentax.moneda.selectedIndex].value;
                    var monto = document.frm_cuentax.monto.value;
                    var mensaje="";
                    if(persona===""){
                        mensaje+="- Seleccione una <b>Persona</b> Correctamente <br>";
                    }
                    if(fecha===""){
                        mensaje+="- La <b>Fecha</b> no debe estar vacia <br>";
                    }
                    if(tipo===""){
                        mensaje+="- Seleccione un <b>Tipo</b> <br>";
                    }
                    if(cux_tipo_plan===""){
                        mensaje+="- Seleccione un <b>Tipo Plan de Pagos</b> <br>";
                    }
                    if(cco_id===""){
                        mensaje+="- Seleccione un <b>Centro de Costos</b> <br>";
                    }                    
                    if(id_cuenta_ing===""){
                        mensaje+="- Ingrese una <b>cuenta de Debe (Capital)</b> <br>";
                    }
                    if(id_cuenta_egr===""){
                        mensaje+="- Ingrese una <b>cuenta de Haber (Capital)</b> <br>";
                    }                    
                    if(interes==='1'){
                        var id_cuenta_ing_int =document.frm_cuentax.id_cuenta_ing_int.value;
                        var id_cuenta_egr_int =document.frm_cuentax.id_cuenta_egr_int.value;
                        if(id_cuenta_ing_int===''||id_cuenta_ing_int==='0'){
                            mensaje+="- Ingrese una <b>cuenta de Debe (Inter&eacute;s)</b> <br>";
                        }
                        if(id_cuenta_egr_int===''||id_cuenta_egr_int==='0'){
                            mensaje+="- Ingrese una <b>cuenta de Haber (Inter&eacute;s)</b> <br>";
                        }
                    }
                    if(ind_concepto===""){
                        mensaje+="- Ingrese un <b>Concepto</b> <br>";
                    }
                    if(moneda===""){
                        mensaje+="- Seleccione una  <b>Moneda</b> <br>";
                    }
                    if(monto===""){
                        mensaje+="- Ingrese un <b>Monto</b> <br>";
                    }                    
                    if(cux_tipo_plan==='Normal'){
                        var interes_mensual =document.frm_cuentax.interes_mensual.value;
                        var cux_meses_plazo =document.frm_cuentax.cux_meses_plazo.value;
                        if(interes_mensual===""){
                            mensaje+="- Ingrese el <b>Interes mensual</b> <br>";
                        }
                        if(cux_meses_plazo===""){
                            mensaje+="- Ingrese el <b>Nro. de cuotas</b> <br>";
                        }
                    }
                    if(cux_tipo_plan==='Especial'){
                        var c_total=$("#c_total").val();
                        if(c_total*1!==monto*1){
                            mensaje+="- El capital programado es diferente del monto Total a programar <br>";
                        }
                    }
                    return mensaje;                    
                }
                function guardar_cuentax_cp(frm){
                    var msj=validar_cuentasx();
                    if(msj!==''){
                        $.prompt(msj);
                        return false;
                    }
                    frm.submit();
                }

                function reset_interno(){
                    document.frm_cuentax.interno.value = "";
                    document.frm_cuentax.nombre_persona.value = "";
                }

                function remove(row){
                    var cant = $(row).parent().parent().parent().children().length;
                    if (cant > 1)
                        $(row).parent().parent().parent().remove();
                }

                function addTableRow(id, valor) {
                    $(id).append(valor);
                }

                function limpiar(){
                    $("#interes_especial").val("");
                    $("#capital_especial").val("");
                }

                function f_tipo(){
                    var tipo = document.frm_cuentax.cux_tipo_plan.options[document.frm_cuentax.cux_tipo_plan.selectedIndex].value;
                    var interes = document.frm_cuentax.cux_interes.options[document.frm_cuentax.cux_interes.selectedIndex].value;
                    if (tipo === 'Especial'){                        
                        $(".tplan_normal").hide();
                        $(".tplan_especial").show();                        
                        if(interes==='1'){
                            $(".int_especial").show();
                            $("#interes_especial").val('');
                        }else{
                            $(".int_especial").hide();
                            $("#interes_especial").val(0);
                        }                        
                    }
                    else if (tipo === 'Normal'){
                        $(".tplan_normal").show();
                        $(".tplan_especial").hide();
                        document.frm_cuentax.cux_meses_plazo.value = "";
                        if(interes==='1'){
                            $(".int_mensual").show();
                            $("#interes_mensual").val('');
                        }else{
                            $(".int_mensual").hide();
                            $("#interes_mensual").val(0);
                        }
                    }else if (tipo === 'Sin Programar' ||tipo === ''){
                        $(".tplan_normal").hide();
                        $(".tplan_especial").hide();
                    }
                    if(interes==='1'){
                        $(".ie_interes").show();                        
                    }else{
                        $(".ie_interes").hide();
                        $("#id_cuenta_ing_int").val('0');
                        $("#id_cuenta_egr_int").val('0');
                        $("#txt_cuenta_ing_int").val('');
                        $("#txt_cuenta_egr_int").val('');
                    } 
                }
            </script>
            <!--MaskedInput-->
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <!--MaskedInput-->
            <!--AutoSuggest-->
            <script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
            <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
            <!--AutoSuggest-->
            <!--FancyBox-->
            <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
            <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
            <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
            <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
            <!--FancyBox-->
            <table align=right border=0><tr><td><a style="float:left; margin:0 0 0 7px;" class="group" href="sueltos/llamada.php?accion=agregar_divisa"><img border="0" src="images/compra_venta_divisa.png"></a></td></tr></table>
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_cuentax" name="frm_cuentax" action="<?php echo $url; ?>" method="POST" enctype="multipart/form-data">
                    <div id="FormSent" style="width:800px;">

                        <div class="Subtitulo">Datos</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
                                <div id="CajaInput">
                                <?php
                                if ($personas <> 0) {
                                ?>
                                    <input name="interno" id="interno" type="hidden" readonly="readonly" class="caja_texto" value="<?php echo $_POST['interno'] ?>" size="2">
                                    <input name="nombre_persona" <? if ($_GET['change'] == "ok") { ?>readonly="readonly" <? } ?> id="nombre_persona"  type="text" class="caja_texto" value="<?php echo $_POST['nombre_persona'] ?>" size="35">

                                    <a class="group-popup" style="float:left; margin:0 0 0 7px;float:right;"  data-url="gestor.php?mod=interno&tarea=AGREGAR" href="javascript:void(0)">
                                        <img src="images/add_user.png" border="0" title="AGREGAR" alt="AGREGAR">
                                    </a>
                                    <a style="float:left; margin:0 0 0 7px;float:right;" href="#" onclick="reset_interno();">
                                        <img src="images/borrar.png" border="0" title="BORRAR" alt="BORRAR">
                                    </a>
                                <?php
                                } else {
                                    echo 'No se le asigno ningúna personas, para poder cargar las personas.';
                                }
                                ?>
                                </div>
                                    <?php $conversor = new convertir(); ?>
                                <div id="CajaInput">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha&nbsp;                                    
                                    <input class="caja_texto" name="ven_fecha" id="ven_fecha" size="12" value="<?php if (isset($_POST['ven_fecha'])) echo $_POST['ven_fecha'];
                                        else echo date("d-m-Y"); ?>" type="text">
                                </div>
                                
                            </div>
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Tipo</div>
                                <div id="CajaInput">
                                    <select name="tipo" class="caja_texto" >
                                        <option value="">Seleccione</option>
                                        <option value="Cobrar">X Cobrar</option>
                                        <option value="Pagar">X Pagar</option>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Interes:</div>
                                <div id="CajaInput">
                                    <select name="cux_interes" id="cux_interes" class="caja_texto" onchange="javascript:f_tipo();" >
                                        <option value="">Seleccione</option>
                                        <option value="1">Si</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Tipo Plan de Pagos</div>
                                <div id="CajaInput">
                                    <select name="cux_tipo_plan" id="cux_tipo_plan" onchange="javascript:f_tipo();" class="caja_texto">
                                        <option value="">Seleccione</option>
                                        <option value="Normal">Normal</option>
                                        <option value="Especial">Especial</option>
                                        <option value="Sin Programar">Sin Programar</option>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Centro Costo</div>
                                <div id="CajaInput">
                                    <select name="cco_id" class="caja_texto">
                                        <option value="">Seleccione</option>
                                        <?php
                                        $ges_id=$_SESSION['ges_id'];
                                        $fun = NEW FUNCIONES;                                        
                                        $fun->combo("select cco_id as id, cco_descripcion as nombre from con_cuenta_cc where cco_ges_id='$ges_id' and cco_tipo='Movimiento' order by cco_codigo asc", $_POST['cco_id']);                                        
                                        ?>
                                    </select>
                                    <?php 
//                                    echo "select cco_id as id, cco_descripcion as nombre from con_cuenta_cc where cco_ges_id='$ges_id' and cco_tipo='Movimiento' order by cco_codigo asc";
                                    ?>
                                </div>
                            </div>
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" id="titulo_etiqueta1">Debe (Capital)</div>
                                <div id="CajaInput">
                                    <div id="cuenta">
                                        <input name="id_cuenta_ing" id="id_cuenta_ing" type="hidden" readonly="readonly" class="caja_texto" value="<?php ?>" size="2">
                                        <input name="txt_cuenta_ing" id="txt_cuenta_ing"  type="text" class="caja_texto" value="<?php ?>" size="35">
                                    </div>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" id="titulo_etiqueta1">Haber (Capital)</div>
                                <div id="CajaInput">
                                    <div id="cuenta">
                                        <input name="id_cuenta_egr" id="id_cuenta_egr" type="hidden" readonly="readonly" class="caja_texto" value="<?php ?>" size="2">
                                        <input name="txt_cuenta_egr" id="txt_cuenta_egr"  type="text" class="caja_texto" value="<?php ?>" size="35">
                                    </div>
                                </div>
                            </div>
                            <!--Fin-->
                            <div class="ie_interes">
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" id="titulo_etiqueta1">Debe (Inter&eacute;s)</div>
                                    <div id="CajaInput">
                                        <div id="cuenta">
                                            <input name="id_cuenta_ing_int" id="id_cuenta_ing_int" type="hidden" readonly="readonly" class="caja_texto" value="<?php ?>" size="2">
                                            <input name="txt_cuenta_ing_int" id="txt_cuenta_ing_int"  type="text" class="caja_texto" value="<?php ?>" size="35">
                                        </div>
                                    </div>
                                </div>
                                <!--Fin-->
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta" id="titulo_etiqueta1">Haber (Inter&eacute;s)</div>
                                    <div id="CajaInput">
                                        <div id="cuenta">
                                            <input name="id_cuenta_egr_int" id="id_cuenta_egr_int" type="hidden" readonly="readonly" class="caja_texto" value="<?php ?>" size="2">
                                            <input name="txt_cuenta_egr_int" id="txt_cuenta_egr_int"  type="text" class="caja_texto" value="<?php ?>" size="35">
                                        </div>
                                    </div>
                                </div>
                                <!--Fin-->
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
                                        $("#id_"+input).val(obj.id);
                                    }
                                };
                                var as_json = new _bsn.AutoSuggest('txt_'+input, options);
                            }
                            complete_cuenta('cuenta_ing');
                            complete_cuenta('cuenta_egr');
                            complete_cuenta('cuenta_ing_int');
                            complete_cuenta('cuenta_egr_int');
                            </script>

                            <!--Fin-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Observación</div>
                                <div id="CajaInput">
                                    <input type="text" class="caja_texto" name="ind_concepto" id="ind_concepto" size="40" maxlength="250" value="<?php echo $_POST['ind_concepto']; ?>">
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Moneda</div>
                                <div id="CajaInput">
                                    <select name="moneda" class="caja_texto">
                                        <option value="" >Seleccione</option>
                                        <option value="1" <?php if ($_POST['moneda'] == '1') echo 'selected="selected"'; ?>>Bolivianos</option>
                                        <option value="2" <?php if ($_POST['moneda'] == '2') echo 'selected="selected"'; ?>>Dolares</option>
                                    </select>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">* </span>Monto</div>
                                <div id="CajaInput">
                                    <input type="text" name="monto" id="monto" size="5" value="<?php echo $_POST['monto'] ?>" onkeypress="return ValidarMoneda(event);">
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div class="tplan_normal">
                                <div id="ContenedorDiv" class="int_mensual">
                                    <div class="Etiqueta" ><span class="flechas1">* </span>Interes Mensual</div>
                                    <div id="CajaInput">
                                        <input type="text" name="interes_mensual" id="interes_mensual" size="5" value="0" onkeypress="return ValidarMoneda(event);">&nbsp;%
                                    </div>
                                </div>
                                <div id="ContenedorDiv">
                                    <div class="Etiqueta"><span class="flechas1">*</span>Nro de Cuotas</div>
                                    <div id="CajaInput">
                                        <input type="text" name="cux_meses_plazo" id="cux_meses_plazo" size="5" value="">
                                        <input type="hidden" name="norm_mtotal" id="norm_mtotal" size="5" value="">
                                    </div> 
                                </div>
                                <div id="ContenedorDiv">
                                    <div id="CajaInput">
                                        <img src="imagenes/generar.png" style='margin:0px 0px 0px 10px' onclick="javascript:generar_pagos();">
                                    </div>
                                </div>
                                <!--Inicio-->
                                <div id="ContenedorDiv">
                                    <div id="plan_de_pagos">
                                    </div>
                                </div>
                                <!--Fin-->
                            </div>
                            <!--Fin-->                            
                            <div class="tplan_especial" style="display:none;">
                                <div class="Subtitulo">Plan de Pagos Especial</div>
                                <div id="ContenedorSeleccion">
                                    <!--Inicio-->
                                    <div id="ContenedorDiv">
                                        <div id="CajaInput">
                                            &nbsp;&nbsp;&nbsp;Fecha:&nbsp;<input class="caja_texto" name="fecha_especial" id="fecha_especial" size="10" value="<?php echo isset($_POST['ven_fecha'])?$_POST['fecha_especial']:date("d-m-Y");?>" type="text">
                                        </div>
                                        <div id="CajaInput" class="int_especial">
                                            &nbsp;&nbsp;&nbsp;Interes:&nbsp;<input name="interes_especial" id="interes_especial"  type="text" class="caja_texto" value="<?php echo $_POST['interes_especial'] ?>" size="8">
                                        </div>
                                        <div id="CajaInput">		
                                            &nbsp;&nbsp;&nbsp;Capital:&nbsp;<input name="capital_especial" id="capital_especial"  type="text" class="caja_texto" value="<?php echo $_POST['capital_especial'] ?>" size="8">
                                        </div>
                                        <input type="hidden" name="capital_hidden" id="capital_hidden" value="" /> 
                                        <div id="CajaInput">
                                            &nbsp;&nbsp;&nbsp;<input type="button" class="boton" name="" value="Agregar" onclick="javascript:datos_fila();">
                                        </div>
                                    </div>
                                    <!--Fin-->
                                    <style>
                                        .del_fespecial_h{
                                            display: none;
                                        }
                                        .del_fespecial{
                                            cursor: pointer;                                            
                                        }
                                    </style>
                                    <script>
                                        function datos_fila(){
                                            var moneda;                    
                                            var monto = parseFloat(document.frm_cuentax.monto.value);
                                            var moneda = document.frm_cuentax.moneda.value;                    
                                            var fecha_especial = document.frm_cuentax.fecha_especial.value;

                                            if (document.frm_cuentax.interes_especial.value !== '')
                                                var interes_especial = parseFloat(document.frm_cuentax.interes_especial.value);
                                            else
                                                interes_especial = '';

                                            if (document.frm_cuentax.capital_especial.value !== '')
                                                var capital_especial = parseFloat(document.frm_cuentax.capital_especial.value);
                                            else
                                                capital_especial = '';
                                            //tipo=document.frm_sentencia.ven_tipo.options[document.frm_sentencia.ven_tipo.selectedIndex].value;
                                            if ((monto !== '' && parseInt(monto) > 0) && moneda !== '' && fecha_especial !== '' && (interes_especial !== '' || parseInt(interes_especial) >= 0) && (capital_especial !== '' || parseInt(capital_especial) >= 0)){
                                                //var valor=document.frm_sentencia.vde_pro_id.value;
                                                var c_total=$("#c_total").val()*1;                                                
                                                var saldo_hidden=monto-(c_total+capital_especial*1);
                                                if(saldo_hidden>=0){
                                                    var montopagar = (interes_especial + capital_especial);
                                                    var nro=$('#tprueba_especial tbody tr').size();
                                                    $(".del_fespecial").attr('class','del_fespecial_h');                       
                                                    var txt_fila='';
                                                    txt_fila+='<tr>';
                                                    txt_fila+='     <td>';
                                                    txt_fila+='         <input name="cuentax_especial[]" type="hidden" value="' + fecha_especial + 'Ø' + interes_especial + 'Ø' + capital_especial + 'Ø' + saldo_hidden + '">' ;
                                                    txt_fila+=          (nro*1+1 )+ '&nbsp;';
                                                    txt_fila+='     </td>';                        
                                                    txt_fila+='     <td>' + fecha_especial + '</td>';
                                                    txt_fila+='     <td>' + interes_especial+ '</td>';
                                                    txt_fila+='     <td>' + capital_especial + '</td>';
                                                    txt_fila+='     <td>' + montopagar + '</td>';
                                                    txt_fila+='     <td>' + saldo_hidden + '</td>';
                                                    txt_fila+='     <td><img src="images/b_drop.png" class="del_fespecial"></td>';
                                                    txt_fila+='</tr>';                        
                                                    $('#tprueba_especial tbody').append(txt_fila);
                                                    calcular_monto_capital();
                                                    $("#m_total").val(monto);
                                                    
                                                    limpiar();
                                                }else{
                                                    $.prompt('- El capital a ingresar es sobrepasa el monto acordado', {opacity: 0.8});
                                                }
                                                
                                            }else{
                                                $.prompt('- Ingrese Fecha <br>-Ingrese Interes a Pagar<br>-Ingrese Capital a Pagar', {opacity: 0.8});
                                            }
                                        }
                                        $(".del_fespecial").live('click',function (){
                                            $(this).parent().parent().remove();
                                            var filas =$("#tprueba_especial tbody tr");
                                            $(filas[filas.size()-1]).find('img').attr('class','del_fespecial');
                                            calcular_monto_capital();
                                        });
                                        
                                        function calcular_monto_capital(){
                                            var filas =$("#tprueba_especial tbody tr");
                                            var tmonto=0;
                                            var tmontopagar=0;
                                            for(var i=0;i<filas.size();i++){
                                                var cols=$(filas[i]).children();
                                                var capital=$(cols[3]).text();                                                
                                                var monto_pagar=$(cols[4]).text();                                                
                                                tmonto+=capital*1;
                                                tmontopagar+=monto_pagar*1;
                                            }
                                            $("#c_total").val(tmonto);
                                            $("#pag_total").val(tmontopagar);
                                        }
                                        
                                        $("#monto,#interes_mensual,#cux_meses_plazo").live('keypress', function(e) {
                                            if (e.keyCode !== 13 && e.keyCode !== 8 && e.keyCode !== 46 && e.keyCode !== 37 && e.keyCode !== 39 
                                                    && e.keyCode !== 35 && e.keyCode !== 36 && e.keyCode !== 9 && !(e.keyCode>=112 && e.keyCode<=123)) {
                                                var valor = $(this).val();
                                                var char = String.fromCharCode(e.which);
                                                valor = valor + char;                        
                                                if (!/^([0-9])*[.]?[0-9]*$/.test(valor)) {
                                                    return false;
                                                }
                                            }
                                        });
                                        $("#monto,#interes_mensual,#cux_meses_plazo").live('keyup', function(e) {
                                            var tipo=$("#cux_tipo_plan option:selected").val();
                                            if(tipo==='Especial'){
                                                var valor=$(this).val()*1;
                                                var m_total=$("#m_total").val()*1;
                                                if(valor!==m_total){
                                                    $("#tprueba_especial tbody tr").remove();
                                                    $("#c_total").val(0);
                                                    $("#m_total").val(0);
                                                    $("#pag_total").val(0);
                                                    calcular_monto_capital();
                                                }
                                            }else if(tipo==='Normal'){
                                                var valor=$(this).val()*1;
                                                var mtotal=$("#norm_mtotal").val()*1;
                                                if(valor!==mtotal){
                                                    $("#tprueba tbody tr").remove();
                                                }
                                            }
                                        });
                                        
                                    </script>
                                    <div>
                                        <br>
                                        <table width="96%"   class="tablaReporte" id="tprueba_especial" cellpadding="0" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Cuota</th>
                                                    <th>Fecha de Pago</th>                                                   
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
                                                    <td>
                                                        <input type="hidden" id="c_total" value="0">
                                                        <input type="hidden" id="m_total" value="0">
                                                        <input type="hidden" id="pag_total" value="0">
                                                    </td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                            </tfoot>
                                        </table>

                                    </div>

                                </div>
                            </div>
                        </div>
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="hidden" class="boton" name="persona" value="ok">
                                    <input type="button" class="boton" name="" value="Generar Deuda" onClick="guardar_cuentax_cp(this.form);">
                                </center>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <script>
                var options1 = {
                    script: "sueltos/persona.php?json=true&",
                    varname: "input",
                    minchars: 1,
                    timeout: 10000,
                    noresults: "No se encontro ninguna persona",
                    json: true,
                    callback: function(obj) {
                        document.getElementById('interno').value = obj.id;
//                        f_particular();
                    }
                };
                var as_json1 = new _bsn.AutoSuggest('nombre_persona', options1);
            </script>
            <script type="text/javascript">
                $(document).ready(function() {
                    $("a.group").fancybox({
                        'hideOnContentClick': false,
                        'overlayShow': true,
                        'zoomOpacity': true,
                        'zoomSpeedIn': 300,
                        'zoomSpeedOut': 200,
                        'overlayOpacity': 0.5,
                        'frameWidth': 700,
                        'frameHeight': 350,
                        'type': 'iframe'
                    });

                    $('a.close').click(function() {
                        $(this).fancybox.close();
                    });

                });
            </script>
            <script>
                jQuery(function($) {
                    $("#ven_fecha").mask("99/99/9999");
                    $("#fecha_especial").mask("99/99/9999");
                    $("#cux_tipo_plan").trigger('change');
                });
            </script>
            <?php
        }


	function insertar_tcp(){
            $conversor = new convertir();		
            $conec= new ADO();
            $persona = $_POST['interno'];
            $fecha = FUNCIONES::get_fecha_mysql($_POST['ven_fecha']);
            $tipo = $_POST['tipo'];                    
            $cux_interes = $_POST['cux_interes'];                    
            $cux_tipo_plan = $_POST['cux_tipo_plan'];                    
            $cco_id = $_POST['cco_id'];
            $id_cuenta_ing =$_POST['id_cuenta_ing'];
            $id_cuenta_egr =$_POST['id_cuenta_egr'];
            $ind_concepto =$_POST['ind_concepto'];
            $moneda = $_POST['moneda'];
            $monto = $_POST['monto'];
            $llave=0;
            $monto_interes=0;
            $id_cuenta_ing_int =$_POST['id_cuenta_ing_int'];
            $id_cuenta_egr_int =$_POST['id_cuenta_egr_int'];
            if($_POST['cux_tipo_plan']=="Sin Programar"){
                $sql="insert into con_cuentasx(cux_int_id,cux_fecha,cux_tipo,cux_interes,cux_tipo_plan,cux_cco_id,cux_cue_ing,cux_cue_egr,cux_cue_ing_int,cux_cue_egr_int,cux_concepto,cux_moneda,cux_monto,cux_interes_mensual,cux_meses_plazo,cux_usu_id,cux_estado,cux_eliminado)
                        values('$persona','$fecha','$tipo','$cux_interes','$cux_tipo_plan','$cco_id','$id_cuenta_ing','$id_cuenta_egr','$id_cuenta_ing_int','$id_cuenta_egr_int','$ind_concepto','$moneda','$monto','null','null','".$this->usu->get_id()."','Pendiente','No')";
                $conec->ejecutar($sql,false);
                $llave=mysql_insert_id();
                	
            }elseif($_POST['cux_tipo_plan']=="Normal"){
                
                $interes_mensual =$_POST['interes_mensual'];
                $cux_meses_plazo =$_POST['cux_meses_plazo'];
                
                $sql="insert into con_cuentasx(cux_int_id,cux_fecha,cux_tipo,cux_interes,cux_tipo_plan,cux_cco_id,cux_cue_ing,cux_cue_egr,cux_cue_ing_int,cux_cue_egr_int,cux_concepto,cux_moneda,cux_monto,cux_interes_mensual,cux_meses_plazo,cux_usu_id,cux_estado,cux_eliminado)
                        values('$persona','$fecha','$tipo','$cux_interes','$cux_tipo_plan','$cco_id','$id_cuenta_ing','$id_cuenta_egr','$id_cuenta_ing_int','$id_cuenta_egr_int','$ind_concepto','$moneda','$monto','$interes_mensual','$cux_meses_plazo','".$this->usu->get_id()."','Pendiente','No')";
                $conec->ejecutar($sql,false);
                $llave=mysql_insert_id();               
                $fechap=$conversor->get_fecha_mysql($_POST['ven_fecha']);
                $saldo=$monto;				
                $interes_anual=($_POST['interes_mensual']*12);					
                $interes_mensual=(($interes_anual/12)/100)*$cux_interes;
                $cuota=$this->cuota($saldo,$interes_anual,$cux_meses_plazo);
                $tpagar=0;
                for($i=1;$i <= $_POST['cux_meses_plazo'];$i++){
                    $fechap=$this->calcular_fecha(+1,$fechap);
                    $interes=$saldo*$interes_mensual;
                    $capital=$cuota-$interes;
                    $saldo=$saldo-$capital;
                    $tpagar+=$cuota;
                    $sql = "insert into interno_deuda(ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_cue_id,ind_cco_id,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,ind_capital,ind_saldo,ind_valor_form,ind_num_correlativo)
                            values('" . $persona . "','" . $cuota . "','" . $moneda . "','Cuota Nro: " . $i . " - $ind_concepto ','" . $fecha . "','" . $this->usu->get_id() . "','" . 0 . "','" . $cco_id . "','con_cuentasx','" . $llave . "','$fechap','$interes','$capital','$saldo','" . 0 . "','$i')";
                    $conec->ejecutar($sql);
                }
                $monto_interes=$tpagar-$monto;                
            }elseif($_POST['cux_tipo_plan']=="Especial"){                
                $sql="insert into con_cuentasx(cux_int_id,cux_fecha,cux_tipo,cux_interes,cux_tipo_plan,cux_cco_id,cux_cue_ing,cux_cue_egr,cux_cue_ing_int,cux_cue_egr_int,cux_concepto,cux_moneda,cux_monto,cux_interes_mensual,cux_meses_plazo,cux_usu_id,cux_estado,cux_eliminado)
                    values('$persona','$fecha','$tipo','$cux_interes','$cux_tipo_plan','$cco_id','$id_cuenta_ing','$id_cuenta_egr','$id_cuenta_ing_int','$id_cuenta_egr_int','$ind_concepto','$moneda','$monto','null','null','".$this->usu->get_id()."','Pendiente','No')";
                $conec->ejecutar($sql,false);				
                $llave=mysql_insert_id();

                $detalle=$_POST['cuentax_especial'];
                $tpagar=0;
                for($i=0;$i<  count($detalle);$i++){
                    $valor=split( "Ø", $detalle[$i]);							
                    $cuota=$valor[1]+$valor[2];
                    $fecha_programada=  FUNCIONES::get_fecha_mysql($valor[0]);
                    $interes=$valor[1]*$cux_interes;
                    $capital=$valor[2];
                    $saldo=$valor[4];
                    $nro_cuota=$i+1;
                    $tpagar+=$cuota;
                    $sql = "insert into interno_deuda(ind_int_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_usu_id,ind_cue_id,ind_cco_id,ind_tabla,ind_tabla_id,ind_fecha_programada,ind_interes,ind_capital,ind_saldo,ind_valor_form,ind_num_correlativo)
                            values('" . $persona . "','" . $cuota . "','" . $moneda . "','Cuota Nro: " . $nro_cuota . " - $ind_concepto ','" . $fecha . "','" . $this->usu->get_id() . "','" . 0 . "','" . $cco_id . "','con_cuentasx','" . $llave . "','$fecha_programada','$interes','$capital','$saldo','" . 0 . "','$nro_cuota')";
                    $conec->ejecutar($sql);
                }
                $monto_interes=$tpagar-$monto;                
            }
            //----------- C O M P R O B A N T E S ----------
            $glosa = "cuenta X $tipo a " . $_POST['nombre_persona'].": ".$ind_concepto;
            include_once 'clases/registrar_comprobantes.class.php';
//            $ges_id = $_SESSION['ges_id'];
            
            $cc = $cco_id;
            $comprobante = new stdClass();
            $comprobante->tipo = $tipo=='Pagar'?"Ingreso":'Egreso';
            $comprobante->mon_id = $_POST['moneda'];
            $comprobante->nro_documento = date("Ydm");
            $comprobante->fecha = $fecha;
            $comprobante->ges_id = $_SESSION['ges_id'];
            $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha);
            $comprobante->forma_pago = "Efectivo";
            $comprobante->ban_id = 0;
            $comprobante->ban_char = '';
            $comprobante->ban_nro = '';
            $comprobante->glosa = $glosa;
            $comprobante->referido = $_POST['nombre_persona'];
            $comprobante->tabla = "con_cuentasx";
            $comprobante->tabla_id = $llave;
            $comprobante->detalles = array(
                array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $id_cuenta_ing,
                    "glosa" => $glosa, "debe" => $monto, "haber" => 0
                ),
                array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $id_cuenta_egr,
                    "glosa" => $glosa, "debe" => 0, "haber" => $monto
                )
            );
            if($cux_interes=='1' && $cux_tipo_plan!='Sin Programar'){
                $comprobante->detalles[] = array("ca" => "", "cc" => $cc, "cf" => "", "cuen" =>$id_cuenta_ing_int ,
                    "glosa" => $glosa, "debe" => $monto_interes, "haber" => 0
                );
                $comprobante->detalles[] = array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $id_cuenta_egr_int,
                    "glosa" => $glosa, "debe" => 0, "haber" => $monto_interes
                );
            }
            _PRINT::pre($comprobante);
            COMPROBANTES::registrar_comprobante($comprobante);            
            //----------- C O M P R O B A N T E S ----------
            
            if($cux_tipo_plan==='Normal'||$cux_tipo_plan==='Especial'){
                $this->nota_de_cuentax($llave);
            }  elseif ($cux_tipo_plan=="Sin Programar") {
                $this->comprobante_cuenta_x($llave);
            }
	}
	
	function cuota($monto,$interes,$meses){
            if($interes > 0){
                $interes /= 100; 
                $interes /= 12;
                $power = pow(1 + $interes, $meses);
                return ($monto * $interes * $power) / ($power - 1);
            }else{
                return round($monto/$meses,2);
            }
	}
	
	function calcular_fecha($meses,$fecha){  	     
            $fechaComparacion = strtotime($fecha);
            $calculo= strtotime("$meses month", $fechaComparacion);		  
            return date("Y-m-d", $calculo);
        }
	
	function nota_de_cuentax($cux_id){
            $conversor=new convertir();		
            $conec= new ADO();		
            $sql = "SELECT * FROM con_cuentasx 
                    inner join interno on (cux_int_id=int_id)
                    WHERE cux_id=$cux_id"; 		
            $conec->ejecutar($sql);		
            $num=$conec->get_num_registros();		
            $objeto=$conec->get_objeto();		
//		$tc=$objeto->cux_tipo_cambio;		
            $tipo=$objeto->cux_tipo;
            ////
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

            $myday = setear_fecha(strtotime($objeto->cux_fecha));
            ////

            echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
                              c.document.write('.$extra1.');
                              var dato = document.getElementById('.$pagina.').innerHTML;
                              c.document.write(dato);
                              c.document.write('.$extra2.'); c.document.close();
                              ">
                            <img src="images/printer.png" align="right" width="16" height="16" border="0" title="IMPRIMIR">
                            </a></td><td><img src="images/back.png" align="right" width="16" height="16" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=venta&tarea=AGREGAR\';"></td></tr></table>
                            ';
            if($this->verificar_permisos('ACCEDER'))
            {	
            ?>
            <table align=right border=0><tr><td><a href="gestor.php?mod=con_cuentasx&tarea=ACCEDER" title="LISTADO DE CUENTAS"><img border="0" width="16" height="16" src="images/listado.png"></a></td></tr></table>
            <?php
            }
            ?>

    <?php
    if($this->verificar_permisos('CUENTAS'))
            {	
            ?>
            <table align=right border=0><tr><td><a href="gestor.php?mod=con_cuentas&tarea=CUENTAS&id=<?php echo $venta; ?>" title="PAGOS"><img border="0" src="images/cuenta.png"></a></td></tr></table>
            <?php
            }
            ?>



                    <br><br><div id="contenido_reporte" style="clear:both;">
                    <center>
                    <table border='0' style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                            <tr>
                                <td width="33%">
                                    <strong><?php echo FUNCIONES::parametro("razon_social"); ?></strong><br>
                               </td>
                               <td width="34%"><p align="center" ><strong><h3>COMPROBANTE DE CUENTA POR <?php echo strtoupper($objeto->cux_tipo);?></h3></strong></p></td>                               
                                <td><div align="right"><img src="imagenes/micro.png"  /></div></td>
                              </tr>
                               <tr>
                                <td colspan="2">                                    				    	
                                        <strong>Fecha: </strong> <?php echo $myday;?> <br>						
                                        <strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cux_usu_id);?> <br><br>
				
                                </td>
                                <td align="right">
                                    <strong>Persona: </strong> <?php echo $this->nombre_interno($objeto->cux_int_id);?> <br>						
                                    <strong>Centro de Costo: </strong> <?php echo $objeto->cco_descripcion;?> <br><br>
                                </td>
                              </tr>

                    </table>
                    <table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                            <thead>
                            <tr>
                                    <th>Persona</th>
                                    <th>Tipo</th>
                                    <th>Observacion</th>
                                    <th>Monto</th>
                                    <th>Moneda</th>
                                    <th>Fecha</th>
                <th>Meses Plazo</th>
                            </tr>		
                            </thead>
                            <tbody>
                            <tr>
                                    <td><?php echo $objeto->int_nombre.' '.$objeto->int_apellido; ?></td>
                                    <td><?php echo $objeto->cux_tipo;?></td>
                                    <td><?php echo $objeto->cux_concepto; ?></td>
                                    <td><?php echo $objeto->cux_monto; ?></td>
                                    <td><?php if($objeto->cux_moneda=="1") echo "Bolivianos"; else echo "Dolares"; ?></td>
                                    <td><?php echo $conversor->get_fecha_latina($objeto->cux_fecha); ?></td>
                <td><?php echo $objeto->cux_meses_plazo; ?></td>
                            </tr>	
                            </tbody>
                    </table>
                    <?php
                    $sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha_programada,int_nombre,int_apellido,ind_estado
                    FROM 
                    interno_deuda 
                    inner join interno on (ind_int_id=int_id)
                    where
                    (ind_estado='Pendiente' or ind_estado='Pagado') and
                    ind_tabla='con_cuentasx' and
                    ind_tabla_id='$cux_id' order by ind_id ASC";

                    $conec->ejecutar($sql);

                    $num=$conec->get_num_registros();

                    if($num > 0)
                    {
                    ?>

                    <br/><h3>PLAN DE PAGO</h3>
                    <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                            <thead>
                            <tr>
                                    <th>Persona</th>
                                    <th>Concepto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                            </tr>		
                            </thead>
                            <tbody>
                            <tr>
                                    <?php


                                    $conversor = new convertir();

                                    for($i=0;$i<$num;$i++)
                                    {
                                            $objeto=$conec->get_objeto();

                                            echo '<tr>';

                                                            echo "<td>";
                                                                    echo $objeto->int_nombre.' '.$objeto->int_apellido;
                                                            echo "&nbsp;</td>";


                                                            echo "<td>";
                                                                    echo $objeto->ind_concepto;
                                                            echo "&nbsp;</td>";
                                                            echo "<td>";
                                                                    echo $objeto->ind_estado;
                                                            echo "&nbsp;</td>";
                                                            echo "<td>";
                                                                    if($objeto->ind_fecha_programada <> '0000-00-00') echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);
                                                            echo "&nbsp;</td>";

                                                            echo "<td>";
                                                                    echo $objeto->ind_monto; if($objeto->ind_moneda=='1') echo ' Bs'; else echo ' $us';
                                                            echo "&nbsp;</td>";



                                                    echo "</tr>";

                                            $conec->siguiente();
                                    }
                                    ?>	
                            </tbody>
                    </table>
            <?php
            }
            ?>
            </br>	
            <table border="0"  width="70%" style="font-size:12px;">
            <?php 
            if($tipo=='Credito') 
            {
            ?>
            <tr>
                    <td colspan="2" width="50%" align ="center">
                    <b>NOTA</b>.- El Sr. <?php echo $objeto->int_nombre.' '.$objeto->int_apellido;?> (Cliente) dejo <?php echo $cuota_ini; ?> <?php if($moneda=="1") echo "Bolivianos"; else echo "Dolares";?>  (importe de la cuota inicial) por el terreno (<?php echo $terreno; ?>). <br/>En caso de no pagar dos cuotas pierde los depósitos y el lote sin necesidad de Orden Judicial.</br></br></br></br></br>
                    </td>
            </tr>
            <?php
            }
            else
            {
                    ?>
                    </br></br></br>
                    <?php
            }
            ?>
            <tr>
                    <td width="50%" align ="center">-------------------------------------</td>
                    <td width="50%" align ="center">-------------------------------------</td>
            </tr>
            <tr>
                    <td align ="center"><strong>Recibi Conforme</strong></td>
                    <td align ="center"><strong>Entregue Conforme</strong></td>
            </tr>
            </table>

            </center>
            <br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
            </div><br>
            <?php	
	}
	
	function pagos() {
            $this->formulario->dibujar_tarea();
            if ($_GET['acc'] == "deuda") {
                if($_GET['op'] == "pagar"){
                    $this->pagardeuda();                    
                }else{
                    $this->frm_pagar_deuda();
                }
                
            } else {
                if ($_GET['acc'] == "especial") {
                    if ($_GET['op'] == "cobrar")
                        $this->cobrar_especial();
                    else
                        $this->frm_pagar_especial();
                }
                else {
                    if ($_GET['acc'] == "verpago") {
                        $this->imprimir_pago($_GET['id']);
                    } else {
                        if ($_GET['acc'] == "multa") {
                            $this->pagarmulta($_GET['id']);
                        } else {
                            if ($_GET['acc'] == "imprimir_plan_pagos") {
                                $this->imprimir_plan_pagos($_GET['id_venta']);
                            } else {
                                if ($_GET['acc'] == "anular") {
                                    $this->anular_pago_pendiente($_GET['id']);
                                } else {
                                    if ($_GET['am'] == 'ok')
                                        $this->amortizar_capital();

                                    $this->listado_pagos();
                                }
                            }
                        }
                    }
                }
            }
        }
        function frm_pagar_deuda(){
            $url = $this->link . '?mod=' . $this->modulo;
            if (!($ver)) {
                $url.="&tarea=" . $_GET['tarea'];
            }
            $url.="&acc=deuda&op=pagar&id=".$_GET['id']."&fecha_ultima_cuota_pagada=".$_GET['fecha_ultima_cuota_pagada'];//."&fecha_comprobante=".$_GET['fecha_comprobante'];
            //&id='+id+'&fecha_ultima_cuota_pagada='+fecha_ultima_cuota_pagada+'&fecha_comprobante='+'';
            //$this->formulario->dibujar_tarea('USUARIO');
            if ($this->mensaje <> "") {
                $this->formulario->mensaje('Error', $this->mensaje);
            }
            $ind=  FUNCIONES::objeto_bd_sql("select * from interno_deuda where ind_id='".$_GET['id']."'");
            $idventa=$ind->ind_tabla_id;
            ?>
            <!--MaskedInput-->
            <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <!--MaskedInput-->
            <div id="Contenedor_NuevaSentencia">
                <form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url?>" method="POST" enctype="multipart/form-data">  
                    <div id="FormSent">

                        <div class="Subtitulo">Datos</div>
                        <div id="ContenedorSeleccion">
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Fecha Pago</div>
                                <div id="CajaInput">                                    
                                    <!--<input type="hidden" id="monto_mora_dia" value="<?php //echo FUNCIONES::atributo_bd("urbanizacion", "urb_id=1", "concat(urb_mora_dia, '-',urb_mora_moneda)");?>">-->
                                    <input class="caja_texto" name="pag_fecha" id="pag_fecha" size="12" value="<?php if (isset($_POST['pag_fecha'])) echo $_POST['pag_fecha'];else echo date("d/m/Y"); ?>" type="text" >
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Fin-->
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
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" >Cta. Act. Disponible:</div>
                                <div id="CajaInput">
                                    <select name="cta_act_disp" id="cta_act_disp" style="min-width: 150px">
                                        <option value="0">Seleccione</option>
                                        <?php foreach($cuentas_act_disp as $cta){ ?>
                                        <option value="<?=$cta->id?>"><?=$cta->descripcion?></option>
                                        <?php }?>
                                    </select>
                                    &nbsp;<span style="color: #ff0000;" hidden="" id="msj_ret_ctas_act_disp">&nbsp;</span>
                                </div>
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Capital a pagar</div>
                                <div id="CajaInput">                                                                            
                                    <input type="text" name="ind_capital" id="ind_capital" value="<?php echo $ind->ind_capital;?>" readonly="true">                                        
                                </div>                                    
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Interes a pagar</div>
                                <div id="CajaInput">                                                                            
                                    <input type="text" name="ind_interes_pagado" id="ind_interes_pagado" value="<?php echo $ind->ind_interes;?>" readonly="true">                                        
                                </div>                                    
                            </div>
                            <!--Fin-->
                            <!--Inicio-->
                            <div id="ContenedorDiv">
                                <div class="Etiqueta" ><span class="flechas1">*</span>Monto a pagar</div>
                                <div id="CajaInput">                                                                            
                                    <input type="text" name="ind_monto_pagado" id="ind_monto_pagado" value="<?php echo $ind->ind_monto;?>" readonly="true">                                        
                                </div>                                    
                            </div>
                            <!--Fin-->
                            
                        </div>
                        <div id="ContenedorDiv">
                            <div id="CajaBotones">
                                <center>
                                    <input type="button" class="boton" name="" onclick="enviar_frm_pago();" value="Guardar">
                                    <input type="button" class="boton" name="" value="Volver" onclick="javascript:window.history.back();">
                                </center>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <script>
                jQuery(function($) {                    
                    $("#pag_fecha").mask("99/99/9999");
//                    $("#val_fecha").mask("99/99/9999");
                });
                
                function validar_fecha_valor(){
                    var fecha=fecha_mysql($("#val_fecha").val());
                    var fecha_interes=$("#fecha_interes").val();
                    if(fecha_interes===""){
                        var prog_fecha=$("#prog_fecha").val();
                        fecha_interes=restar_dias(prog_fecha,30);
                    }                    
                    if(fecha<fecha_interes){
                        fecha=fecha_interes;
                        $("#val_fecha").val(fecha_latina(fecha));
                    }
                    fecha=fecha_latina(fecha);
                    var expreg = ("^([0-9]{1,2})([/])([0-9]{1,2})([/])(19|20)+([0-9]{2})$");
//                    if (!expreg.test(fecha)){
                    if (fecha.search(expreg)!==0){                        
                        $("#val_fecha").val($("#fecha_hoy"));
                    }
//                    console.log($(this).val());
                    setTimeout("calcular_interes();",100);
                }
               
                function enviar_frm_pago(){ //$("#frm_sentencia").submit(function (){
                    var pag_fecha = $('#pag_fecha').val();
                    if (pag_fecha !== "") {
                        $.get('AjaxRequest.php', {peticion: 'idPeriodo', fecha: pag_fecha}, function(respuesta) {
                            var dato = JSON.parse(respuesta);
                            if (dato.response === "ok") {
                                var fecha=$("#cta_act_disp option:selected").val();
                                if(fecha!=='0'){
                                    document.frm_sentencia.submit();
                                }else{
                                    $.prompt('Seleccion una cuenta de activo disponible.');
                                }
                            } else if (dato.response === "error") {
                                $.prompt(dato.mensaje);
                                return false;
                            }
                        });
                    }else{
                        $.prompt('La fecha del pago de la cuota no debe ser vacio.');
                        return false;
                    }

                }
                

            </script>
            <?php
        }
	
	function listado_pagos(){
            if($this->verificar_permisos('ACCEDER'))
            {	
            ?>		
            <table align=right border=0><tr><td><a href="gestor.php?mod=cuentasx&tarea=ACCEDER" title="LISTADO DE CUENTAS"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
            <table align=right border=0><tr><td><a href="gestor.php?mod=cuentasx&tarea=CUENTAS&acc=imprimir_plan_pagos&id_venta=<?php echo $_GET['id']; ?>" title="IMPRIMIR PLAN DE PAGOS"><img border="0" width="20" src="images/printer.png"></a></td></tr></table>
		<?php
		}
		?>
		<script>		
                    function pagar_deuda(id, fecha_ultima_cuota_pagada) {
                        location.href = 'gestor.php?mod=con_cuentasx&tarea=CUENTAS&acc=deuda&id=' + id + '&fecha_ultima_cuota_pagada=' + fecha_ultima_cuota_pagada;
                    }

                    function pagar_menos(id, fecha_ultima_cuota_pagada) {
                        var txt = 'Esta seguro que cobrara menos?<br />Ingrese Fecha de Pago<br /><input type="text" id="fecha_comprobante" name="fecha_comprobante" value="<?php echo date('d/m/Y'); ?>" />&nbsp;<span>dd/mm/aaaa</span>';
                        $.prompt(txt, {
                            buttons: {Si: true, Cancelar: false},
                            callback: function(v, m, f) {
                                if (v) {
                                    location.href = 'gestor.php?mod=cuentasx&tarea=CUENTAS&acc=especial&id=' + id + '&fecha_ultima_cuota_pagada=' + fecha_ultima_cuota_pagada + '&fecha_comprobante=' + f.fecha_comprobante;
                                }
                            }
                        });
                    }

                    function anular_pago(id) {
                        var txt = 'Esta seguro de anular el cobro de la cuota?';
                        $.prompt(txt, {
                            buttons: {Aceptar: true, Cancelar: false},
                            callback: function(v, m, f) {
                                if (v) {
                                    location.href = 'gestor.php?mod=con_cuentasx&tarea=CUENTAS&acc=anular&id=' + id;
                                }
                            }
                        });
                    }

		</script>
        
        <?php
		$conversor=new convertir();		
		$conec= new ADO();		
		$sql = "SELECT * FROM con_cuentasx 
				inner join interno on (cux_int_id=int_id)
				WHERE cux_id=".$_GET['id']; 		
		$conec->ejecutar($sql);		
		$num=$conec->get_num_registros();		
		$objeto=$conec->get_objeto();
		$tipo=$objeto->cux_tipo;		
		$myday = setear_fecha(strtotime($objeto->cux_fecha));
		?>
        <table border='0' style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="33%">
				        <strong><?php echo _nombre_empresa; ?></strong><BR>
				    
				   </td>
				    <td width="33%"><center><p align="center" ><strong><h3>NOTA DE <?php if($tipo==1) echo 'CUENTA X COBRAR'; if($tipo==2) echo 'CUENTA X PAGAR'; ?> </h3></strong></p></center></td>
				    <td><div align="right"><img src="imagenes/micro.png"  /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
					<strong><br/>Nro de Cuenta x <?php if($tipo==1) echo 'Cobrar'; if($tipo==2) echo 'Pagar'; ?>: </strong> <?php echo $_GET['id'];?><br>
				    <?php
					if($objeto->cux_int_id <> 0)
					{
						$persona=$objeto->int_nombre.' '.$objeto->int_apellido;
						
						?>
						
						<strong>Persona: </strong> <?php echo $persona;?> <br>
						
						<?php
					}
					//if($objeto->ven_observacion<>'')
					//{
					?>
					<!--<strong>Observación: </strong> <?php echo $objeto->ven_observacion;?> <br><br>-->
					<?php
					//}
					//else
					//{
					?>
					<br><br>
					<?php
					//}
					?>
					</td>
				    <td align="right">
					<strong>Fecha: </strong> <?php echo $myday;?> <br>
					
					<strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cux_usu_id);?> <br>
					</td>
				  </tr>
				 
			</table>
			<table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Persona</th>
					<th>Tipo</th>
					<th>Observacion</th>
					<th>Monto</th>
					<th>Moneda</th>
					<th>Fecha</th>
                    <th>Meses Plazo</th>
				</tr>		
				</thead>
				<tbody>
				<tr>
					<td><?php echo $objeto->int_nombre.' '.$objeto->int_apellido; ?></td>
					<td><?php if($objeto->cux_tipo==1)echo 'X Cobrar'; if($objeto->cux_tipo==2)echo 'X Pagar'; ?></td>
					<td><?php echo $objeto->cux_concepto; ?></td>
					<td><?php echo $objeto->cux_monto; ?></td>
					<td><?php if($objeto->cux_moneda=="1") echo "Bolivianos"; else echo "Dolares"; ?></td>
					<td><?php echo $conversor->get_fecha_latina($objeto->cux_fecha); ?></td>
                    <td><?php echo $objeto->cux_meses_plazo; ?></td>
				</tr>	
				</tbody>
			</table>
        
        
		<br><br><center><h2>CUOTAS</h2><table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
		        	<th>Persona</th>
					<th>Observación</th>
					<th>Moneda</th>
					<th>Interes</th>
					<th>Capital</th>
					<th>Monto a Pagar</th>
					<th>Saldo</th>
					<th>F Programada</th>
					<th>F Pagada</th>
					<th>Estado</th>
		            <th class="tOpciones" width="40px">OP</th>
				</tr>	
				</thead>
				<tbody>
		<?php
		
		$fecha_ultima_cuota_pagada_bloquear=$this->fecha_ultima_cuota_pagada_bloquear($_GET['id']);		
		$hay_cuotas_atrasadas=$this->cuotas_atrasadas_rango($fecha_ultima_cuota_pagada_bloquear,date('Y-m-d'),$_GET['id']);		
		//$esta_desbloqueado=$this->verificar_desbloqueo($_GET['id'],date('Y-m-d'));		
		$conec= new ADO();		
		$sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_monto_parcial,int_nombre,int_apellido,ind_fecha_programada,ind_fecha_pago,ind_interes,ind_capital,ind_saldo 
		FROM 
		interno_deuda inner join interno on (ind_tabla='con_cuentasx' and(ind_estado='Pendiente' or ind_estado='Pagado') and ind_tabla_id='".$_GET['id']."' and ind_int_id=int_id)
		order by ind_id asc";
		$conec->ejecutar($sql);
		$num=$conec->get_num_registros();
		$conversor = new convertir();
		$pendiente=1;
		for($i=0;$i<$num;$i++){
                    $saldo=0;			
                    $objeto=$conec->get_objeto();
                    echo '<tr>';

                            echo "<td>";
                                    echo $objeto->int_nombre.' '.$objeto->int_apellido;
                            echo "&nbsp;</td>";


                            echo "<td>";
                                    echo $objeto->ind_concepto;
                            echo "&nbsp;</td>";



                            echo "<td>";
                                    if($objeto->ind_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';
                            echo "&nbsp;</td>";
                            echo "<td>";
                                    if($objeto->ind_monto_parcial >= $objeto->ind_interes)
                                    {
                                            echo 0;
                                            $saldo=$objeto->ind_monto_parcial - $objeto->ind_interes;
                                    }
                                    else
                                            echo $objeto->ind_interes - $objeto->ind_monto_parcial;
                                    //echo $objeto->ind_interes;
                            echo "&nbsp;</td>";
                            echo "<td>";
                                    if($saldo > 0)
                                    {
                                            if($saldo >= $objeto->ind_capital)
                                            {
                                                    echo 0;
                                            }
                                            else
                                                    echo $objeto->ind_capital - $saldo;
                                    }
                                    else
                                            echo $objeto->ind_capital;
                                    //echo $objeto->ind_capital;
                            echo "&nbsp;</td>";
                            echo "<td>";
                            if($objeto->ind_monto_parcial>0)
                                    echo ($objeto->ind_monto - $objeto->ind_monto_parcial);
                            else
                                    echo $objeto->ind_monto;
                            echo "&nbsp;</td>";
                            echo "<td>";
                                    echo $objeto->ind_saldo;
                            echo "&nbsp;</td>";
                            $color="#000000";

                            if($objeto->ind_fecha_programada < date('Y-m-d') && $objeto->ind_estado=='Pendiente')
                                    $color="#FB0404;";

                            echo '<td style="color:'.$color.'">';
                                    if($objeto->ind_fecha_programada <> '0000-00-00') echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);
                            echo "&nbsp;</td>";

                            echo "<td>";
                                    if($objeto->ind_fecha_pago <> '0000-00-00') echo $conversor->get_fecha_latina($objeto->ind_fecha_pago);
                            echo "&nbsp;</td>";

                            $color="#000000";

                            if($objeto->ind_estado=='Pendiente')
                                    $color="#FB0404;";

                            if($objeto->ind_estado=='Pagado')
                                    $color="#05A720;";

                            echo '<td style="color:'.$color.'">';
                                    echo $objeto->ind_estado;
                            echo "&nbsp;</td>";


                            echo "<td>&nbsp;";
                                if($objeto->ind_estado=='Pendiente' && $pendiente==1){
                                    $pendiente++;
                                    $registro_ultimo_pago = $this->fecha_ultima_cuota_pagada($objeto->ind_id);
                                ?>
                                <center>
                                    <table border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                    <td>
                                                            <?php
                                                            if($hay_cuotas_atrasadas==true){
                                                                    ?>	
                                                                <a class="linkOpciones" href="javascript:pagar_deuda('<?php echo $objeto->ind_id;?>','<?php echo $registro_ultimo_pago;?>');">
                                                                    <img src="images/pagar.png" border="0" title="COBRAR CUOTA" alt="Pagar">
                                                                </a>                                    		
                                                                    <?php
                                                            }else{
                                                            ?>
                                                                <a class="linkOpciones" href="javascript:pagar_deuda('<?php echo $objeto->ind_id;?>','<?php echo $registro_ultimo_pago;?>');">
                                                                    <img src="images/pagar.png" border="0" title="COBRAR CUOTA" alt="Pagar">
                                                                </a>
                                                            <?php
                                                            }
                                                            ?>

                                                    </td>								
                                            </tr>
                                    </table>

                                    </center>
                                    <?php
                                    }                                    
                                    if($objeto->ind_estado=='Pagado'){
                                        ?>
                                        <center>                                        
                                        <a class="linkOpciones" href="gestor.php?mod=con_cuentasx&tarea=CUENTAS&acc=verpago&id=<?php echo $objeto->ind_id;?>">
                                                <img src="images/b_search.png" border="0" title="VER COMPROBANTE" alt="Ver">
                                        </a>
                                        <?php if($fecha_ultima_cuota_pagada_bloquear==$objeto->ind_fecha_programada){  ?>
                                        <a class="linkOpciones" href="javascript:anular_pago('<?php echo $objeto->ind_id;?>');">
                                            <img src="images/anular.png" border="0" title="ANULAR PAGO" alt="Anular">
                                        </a>
                                        <?php }?>
                                        </center>
                                        <?php
                                    }
                            echo "</td>";
                    echo "</tr>";
                    $conec->siguiente();
		}
		?>
		</tbody></table></center>
		<?php
		
	}
	
	
	
	function fecha_ultima_cuota_pagada($ind_id)
	{
		$conec= new ADO();
		
		$sql="SELECT * FROM interno_deuda where ind_id=$ind_id";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		$id_venta = $objeto->ind_tabla_id;
		
		
		$sql="SELECT * FROM interno_deuda where ind_tabla='cuentasx' and ind_tabla_id=$id_venta AND ind_estado='Pagado' ORDER BY ind_id DESC LIMIT 0,1";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		$ind_id = $objeto->ind_id;
		
		if($ind_id=='')
		{
			return $ind_id = $this->primera_cuota($id_venta);
		}
		else
			return $ind_id;		
	}
	
	function primera_cuota($id_venta)
	{
		$conec= new ADO();
		
		$sql="SELECT * FROM interno_deuda where ind_tabla='cuentasx' and ind_tabla_id=$id_venta ORDER BY ind_id ASC LIMIT 0,1";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		$ind_id = $objeto->ind_id;
			
		return $ind_id;
	}
	
	function primera_cuota_fecha($cux_id)
	{
		$conec= new ADO();
		
		$sql="SELECT * FROM interno_deuda where ind_tabla='cuentasx' and ind_tabla_id=$cux_id ORDER BY ind_id ASC LIMIT 0,1";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		$ind_fecha_programada = $objeto->ind_fecha_programada;
			
		return $ind_fecha_programada;
	}
	
	function obtener_fecha_programada($ind_id)
	{
		$conec= new ADO();
		
		$sql="SELECT * FROM interno_deuda where ind_id=$ind_id";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->ind_id;
			
	}
	
	function obtener_venta($ind_id)
	{
		$conec= new ADO();
		
		$sql="SELECT * FROM interno_deuda where ind_id=$ind_id AND ind_tabla='con_cuentasx'";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->ind_tabla_id;		
	}
	
	function no_existe_pagos($id_venta){
		$conec= new ADO();		
		$sql="SELECT * FROM interno_deuda where ind_tabla='con_cuentasx' and ind_tabla_id=$id_venta AND ind_estado='Pagado' ORDER BY ind_id DESC LIMIT 0,1";
		$conec->ejecutar($sql);
		$objeto=$conec->get_objeto();
		$nume=$conec->get_num_registros();
		if($nume == 0)
                    return true;
		else
                    return false;
			
	}
	
	function fecha_ultima_cuota_pagada_bloquear($id_venta){
            $conec= new ADO();		
            $sql="SELECT * FROM interno_deuda where ind_tabla='con_cuentasx' and ind_tabla_id=$id_venta AND ind_estado='Pagado' ORDER BY ind_id DESC LIMIT 0,1";
            $conec->ejecutar($sql);
            $objeto=$conec->get_objeto();
            $fecha_programada = $objeto->ind_fecha_programada;
            if($fecha_programada==''){
                return $fecha_programada = $this->primera_cuota_fecha($id_venta);
            }else{
                return $fecha_programada;
            }
	}
	
	function cuotas_atrasadas_rango($fecha_inicio,$fecha_fin,$id_venta)
	{
		$array_cuotas='';
		
		$conec= new ADO();
		
		if($fecha_inicio == $this->primera_cuota_fecha($id_venta))
		{
			$sql="SELECT * FROM `interno_deuda` 
			WHERE ind_fecha_programada >= '".$fecha_inicio."' 
			AND ind_fecha_programada < '".$fecha_fin."' 
			AND ind_tabla='cuentasx' 
			AND ind_tabla_id=$id_venta 
			AND ind_estado='Pendiente'";
		}
		else
		{
			$sql="SELECT * FROM `interno_deuda` 
			WHERE ind_fecha_programada > '".$fecha_inicio."' 
			AND ind_fecha_programada < '".$fecha_fin."' 
			AND ind_tabla='cuentasx' 
			AND ind_tabla_id=$id_venta 
			AND ind_estado='Pendiente'";
		}
		
		$conec->ejecutar($sql);

		//$objeto=$conec->get_objeto();
		
		//Parametro - Cantidad de Cuotas a Bloquear - Campo(par_bloq_cuota)
		$cuotas_bloqueadas_parametro=$this->cantbloq_meses_parametro();
		
		$nume=$conec->get_num_registros();
		
		if($cuotas_bloqueadas_parametro > 0)
		{
			if($nume>=$cuotas_bloqueadas_parametro)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
		
	}
	
	function cantbloq_meses_parametro()
	{
		$conec= new ADO();
		
		$sql="SELECT par_bloq_cuota FROM ad_parametro";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->par_bloq_cuota;
	}
	
	function obtener_diferencia_meses($fech_ini,$fech_fin,$id_venta){
		$conec= new ADO();		
		$sql="SELECT * FROM `interno_deuda` 
                WHERE ind_id > '".$fech_ini."' 
                AND ind_id <= '".$fech_fin."' 
                AND ind_tabla='con_cuentasx' 
                AND ind_tabla_id=$id_venta 
                AND ind_estado='Pendiente'";		
		$conec->ejecutar($sql);
		$objeto=$conec->get_objeto();		
		$nume=$conec->get_num_registros();		
		return $nume;
        }
	
	function pagardeuda() {
            $id_venta = $this->obtener_venta($_GET['id']);
            $fecha_programada_actual = $this->obtener_fecha_programada($_GET['id']);
            $fecha_ultima_cuota_pagada = $_GET['fecha_ultima_cuota_pagada'];
            if ($this->obtener_diferencia_meses($fecha_ultima_cuota_pagada, $fecha_programada_actual, $id_venta) > 1) {
                $mensaje = 'No se puede realizar el Cobro, por que existen Cuotas anteriores en el Plan de Pagos.';
                $this->mensaje = $mensaje;
                if ($this->mensaje <> "") {
                    $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=CUENTAS&id=' . $id_venta, "", "Error");
                }
            } else {
//                if ($this->obtener_diferencia_meses($fecha_ultima_cuota_pagada, $fecha_programada_actual, $id_venta) == 1 && $this->no_existe_pagos($id_venta) == true) {
//                    $mensaje = 'No se puede realizar el Cobro, por que existen Cuotas anteriores en el Plan de Pagos.';
//                    $this->mensaje = $mensaje;
//                    if ($this->mensaje <> "") {
//                        $this->formulario->dibujar_mensaje($this->mensaje, $this->link . '?mod=' . $this->modulo . '&tarea=CUENTAS&id=' . $id_venta, "", "Error");
//                    }
//                } else {
                    $conec = new ADO();
                    $conec2 = new ADO();
//                    $sql = "select cja_cue_id from cajero where cja_usu_id = '" . $this->usu->get_id() . "'";
//                    $conec->ejecutar($sql);
//                    $nume = $conec->get_num_registros();
//                    if ($nume > 0) {
//                        $obj = $conec->get_objeto();
//                        $caja = $obj->cja_cue_id;
                        $sql = "SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_int_id,ind_cue_id,ind_cco_id,ind_tabla_id,ind_monto_parcial 
					FROM interno_deuda
					where ind_id='" . $_GET['id'] . "'";

                        $conec->ejecutar($sql);
                        $objeto = $conec->get_objeto();
                        $fecha=  FUNCIONES::get_fecha_mysql($_POST['pag_fecha']);
                        if ($objeto->ind_monto_parcial > 0) {
                            $cadena = $objeto->ind_concepto;
                            $ind_concepto = explode("(", $cadena);
                            $sql = "update interno_deuda set 
                                            ind_estado='Pagado',
                                            ind_fecha_pago='" . $fecha . "',
                                            ind_concepto='" . $ind_concepto[0] . "'
                                            where ind_id = '" . $_GET['id'] . "'";
                        } else {
                            $sql = "update interno_deuda set 
                                            ind_estado='Pagado',
                                            ind_fecha_pago='" . $fecha . "'
                                            where ind_id = '" . $_GET['id'] . "'";
                        }

                        $conec->ejecutar($sql);
                        /*                         * REFLEJO EN LAS CUENTAS* *///
                        $sql = "SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_num_correlativo,ind_fecha,ind_estado,ind_int_id,ind_interes,ind_capital,ind_cue_id,ind_cco_id,ind_tabla,ind_tabla_id,ind_monto_parcial,ind_valor_form 
					FROM interno_deuda
					where ind_id='" . $_GET['id'] . "'";
                        $conec->ejecutar($sql);
                        $objeto = $conec->get_objeto();
                        ////////////   -- //////////
                        if ($objeto->ind_monto_parcial > 0) {
                            if ($objeto->ind_monto_parcial >= $objeto->ind_interes) {
                                $ind_interes = 0;
                                $saldo = $objeto->ind_monto_parcial - $objeto->ind_interes;
                                $ind_capital = $objeto->ind_capital - $saldo;
                                $ind_valor_form = $objeto->ind_valor_form;
                            } else {
                                $ind_interes = $objeto->ind_interes - $objeto->ind_monto_parcial;
                                $ind_capital = $objeto->ind_capital;
                                $ind_valor_form = $objeto->ind_valor_form;
                            }
                        } else {
                            $ind_interes = $objeto->ind_interes;
                            $ind_capital = $objeto->ind_capital;
                            $ind_valor_form = $objeto->ind_valor_form;
                        }
                        ////////////  --  //////////
                        $sql="select * from con_cuentasx where cux_id='".$objeto->ind_tabla_id."'";
                        $cuentax=  FUNCIONES::objeto_bd_sql($sql);
                        $tabla_id = $objeto->ind_id;
//                        $tabla = $objeto->ind_tabla;
                        //----------- C O M P R O B A N T E S ----------
                        $int=FUNCIONES::objeto_bd_sql("select * from interno where int_id='$objeto->ind_int_id'");
                        $_tipo=$cuentax->cux_tipo=='Pagar'?'Pago':'Cobro';
                        $glosa = "$_tipo de cuota Nro $objeto->ind_num_correlativo por cuenta X $cuentax->cux_tipo a " . $int->int_nombre." ".$int->int_apellido. ": ". $cuentax->cux_concepto;//$_POST['nombre_persona'];
                        include_once 'clases/registrar_comprobantes.class.php';

            //            $ges_id = $_SESSION['ges_id'];
                        $cc = $cuentax->cux_cco_id;
                        $comprobante = new stdClass();
                        $comprobante->tipo =$cuentax->cux_tipo=='Pagar'?'Egreso':'Ingreso';
                        $comprobante->mon_id = $objeto->ind_moneda;
                        $comprobante->nro_documento = date("Ydm");
                        $comprobante->fecha = $fecha;
                        $comprobante->ges_id = $_SESSION['ges_id'];
                        $comprobante->peri_id = FUNCIONES::obtener_periodo($fecha);
                        $comprobante->forma_pago = "Efectivo";
                        $comprobante->ban_id = 0;
                        $comprobante->ban_char = '';
                        $comprobante->ban_nro = '';
                        $comprobante->glosa = $glosa;
                        $comprobante->referido = $int->int_nombre." ".$int->int_apellido;
                        $comprobante->tabla = "interno_deuda";
                        $comprobante->tabla_id = $tabla_id;

                        $cuen_1=0;
                        $cuen_2=0;
                        if($cuentax->cux_tipo=='Pagar'){                    
                            $cuen_1=$cuentax->cux_cue_egr;
                            $cuen_2=$_POST['cta_act_disp'];
                        }elseif($cuentax->cux_tipo=='Cobrar'){                    
                            $cuen_1=$_POST['cta_act_disp'];
                            $cuen_2=$cuentax->cux_cue_ing;
                        }
                        $comprobante->detalles = array(
                            array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $cuen_1,
                                "glosa" => $glosa, "debe" => $ind_capital, "haber" => ""
                            ),
                            array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $cuen_2,
                                "glosa" => $glosa, "debe" => "", "haber" => $ind_capital
                            )
                        );
                        if($ind_interes*1>0){
                            $cuen_1=0;
                            $cuen_2=0;
                            if($cuentax->cux_tipo=='Pagar'){                    
                                $cuen_1=$cuentax->cux_cue_egr_int;
                                $cuen_2=$_POST['cta_act_disp'];
                            }elseif($cuentax->cux_tipo=='Cobrar'){                    
                                $cuen_1=$_POST['cta_act_disp'];
                                $cuen_2=$cuentax->cux_cue_ing_int;
                            }
                            $comprobante->detalles[]= array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $cuen_1,
                                                        "glosa" => $glosa, "debe" => $ind_interes, "haber" => "");

                            $comprobante->detalles[]=array("ca" => "", "cc" => $cc, "cf" => "", "cuen" => $cuen_2,
                                                        "glosa" => $glosa, "debe" => "", "haber" => $ind_interes);                    
                        }
                        COMPROBANTES::registrar_comprobante($comprobante); 
                        //----------- C O M P R O B A N T E S ----------
                        
                        ///Estado de la venta
                        $sql = "SELECT ind_id 
					FROM interno_deuda
					where 
					ind_tabla='con_cuentasx' and
					ind_tabla_id='$tabla_id' and
					ind_estado='Pendiente'
					";

                        $conec->ejecutar($sql);
                        $num = $conec->get_num_registros();
                        if ($num == 0) {
                            $sql = "update cuentasx set 
                                        cux_estado='Pagado'
                                        where cux_id = '$tabla_id'";

                            $conec->ejecutar($sql);
                        }
                        ///
                        $this->imprimir_pago($_GET['id']);
                        $sql = "update interno_deuda set 
                                        ind_monto_parcial=0
                                        where ind_id = '" . $_GET['id'] . "'";

                        $conec->ejecutar($sql);
//                    }  
//                    $mensaje = 'No puede realizar ninguna cobro, por que usted no esta registrado como cajero.';
//                    $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
//                    }else {
//                        $mensaje = 'No puede realizar ninguna cobro, por que usted no esta registrado como cajero.';
//                        $this->formulario->ventana_volver($mensaje, $this->link . '?mod=' . $this->modulo);
//                    }
                }
            }

	
	function imprimir_pago($deuda)
	{		
		$conec= new ADO();
		
		$sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_tabla,ind_tabla_id,int_id,int_nombre,int_apellido,ind_fecha_pago,ind_fecha_programada,ind_monto_parcial,ind_valor_form 
		FROM 
		interno_deuda inner join interno on (ind_id='".$deuda."' and ind_int_id=int_id)";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		////
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
		
		if($this->verificar_permisos('AMORTIZAR'))
		{
			$link_amortizar='<td><a href="#"><img src="images/reformular.png" align="right"  border="0"  title="AMORTIZAR" onclick="javascript:location.href=\'gestor.php?mod=venta&tarea=AMORTIZAR&id='.$objeto->ind_tabla_id.'\';"></a></td>';
		}
		
			echo '<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();"><img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR"></a></td>'.$link_amortizar.'
				<td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=con_cuentasx&tarea=CUENTAS&id='.$objeto->ind_tabla_id.'\';"></td></tr></table>
				';
		$conversor = new convertir();
		$id_pago=$objeto->ind_tabla_id;
		?>
		<br><br><div id="contenido_reporte" style="clear:both;";>
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="40%" >
				    <strong><?php echo _nombre_empresa; ?></strong><BR>
					
					</td>
				    <td  width="20%" ><p align="center" ><strong><h3><center>COMPROBANTE DE PAGO</center></h3></strong></p></td>
				    <td  width="40%" ><div align="right"><img src="imagenes/micro.png" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
				    <strong>Persona: </strong> <?php echo $objeto->int_nombre.' '.$objeto->int_apellido;?> <br/><br/>
					
	
					</td>
				    <td align="right">
					
					</td>
				  </tr>
				 
			</table>
			<table   width="90%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>F Programada</th>
					<th>F Pago</th>
					
					<th>Observación</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>			
				</thead>
				<tbody>
				<tr>
					<td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);?></td>
					<td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_pago);?></td>
					
					<td><?php echo $objeto->ind_concepto;?></td>
					<td><?php if($objeto->ind_monto_parcial>0) { echo ($objeto->ind_monto - $objeto->ind_monto_parcial); } else { echo $objeto->ind_monto; }?></td>
					<td><?php if($objeto->ind_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>
                <?php if($objeto->ind_valor_form > 0){ ?>
                <tr>
					<td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);?></td>
					<td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_pago);?></td>
					
					<td><?php echo $objeto->ind_concepto.' (Cobro Formulario)';?></td>
					<td><?php echo $objeto->ind_valor_form;?></td>
					<td><?php if($objeto->ind_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>
                <?php } ?>
                <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					
					<td style="text-align:right"><b>Total</b></td>
					<td><b><?php echo ($objeto->ind_monto - $objeto->ind_monto_parcial) + $objeto->ind_valor_form;?></b></td>
					<td><b><?php if($objeto->ind_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></b></td>
				</tr>	
				</tbody>
				</table>
				
				<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>
				
				</center>
				<br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
				</div>
				<?php 
				if($objeto->ind_fecha_pago > $objeto->ind_fecha_programada)
				{
					$tabla=$objeto->ind_tabla;
					$tabla_id=$objeto->ind_tabla_id;
					
					//$urb_id=$this->obtener_urbanizacion_venta($tabla_id);
					
					//$this->cuenta_urbanizacion_multa($urb_id,$cue_urb_multa,$cco_urb_multa);
				?>
				<script type="text/javascript">
							function enviar(frm)
							{
							   var multa = parseFloat(document.frm_sentencia.multa.value);
							   if (multa > 0 )
							   {
		
									document.frm_sentencia.submit();
								 
							   }
							   else
								 $.prompt('Ingrese el monto de la multa que cobrara.',{ opacity: 0.8 });
							}	
						</script>
						
				<div style="clear:both;">
					<div id="Contenedor_NuevaSentencia">
						<form id="frm_sentencia" name="frm_sentencia" action="gestor.php?mod=venta&tarea=PAGOS&acc=multa&id=<? echo $id_pago; ?>" method="POST" enctype="multipart/form-data">  
									<div id="FormSent">
										<div class="Subtitulo">Este pago esta en mora</div>
					                    <div id="ContenedorSeleccion">
											<div id="ContenedorDiv">
					                           <div class="Etiqueta" ><span class="flechas1">*</span>Monto Multa <?php if($objeto->ind_moneda=='1') echo '(Bs)'; else echo '($us)'; ?></div>
					                             <div id="CajaInput">
													<input class="caja_texto" name="multa" id="multa" size="12" value="" type="text">
													<input class="caja_texto" name="interno" id="interno" size="12" value="<?php echo $objeto->int_id;?>" type="hidden">
													<input class="caja_texto" name="concepto" id="concepto" size="12" value="Multa por retraso en la <?php echo $objeto->ind_concepto;?>" type="hidden">
                                                    
                                                    <input class="caja_texto" name="cue_urb_multa" id="cue_urb_multa" size="12" value="<?php echo $cue_urb_multa; ?>" type="hidden">
                                                    <input class="caja_texto" name="cco_urb_multa" id="cco_urb_multa" size="12" value="<?php echo $cco_urb_multa; ?>" type="hidden">
                                                    <input class="caja_texto" name="moneda_multa" id="moneda_multa" size="12" value="<?php echo $objeto->ind_moneda; ?>" type="hidden">
												</div>		
					                        </div>
										</div>
										
										<div id="ContenedorDiv">
					                           <div id="CajaBotones">
													<center>
													<input type="hidden" class="boton" name="formu" value="ok">
													<input type="button" class="boton" name="" value="Cobrar Multa" onClick="enviar();">
													</center>
											   </div>
					                    </div>
									</div>
						</form>	
						</div>
				</div>
				<?php
				}
				?>	
		<?php	
	}
	
	function imprimir_pago_especial($deuda,$monto,$moneda)
	{		
		$conec= new ADO();
		
		$sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_tabla_id,int_id,int_nombre,int_apellido,ind_fecha_pago,ind_fecha_programada,ind_usu_id,ind_monto_parcial 
		FROM 
		interno_deuda inner join interno on (ind_id='".$deuda."' and ind_int_id=int_id)
		";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		////
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
		
		$myday = setear_fecha(strtotime($objeto->ind_fecha));
		
		if($this->verificar_permisos('AMORTIZAR'))
		{
			$link_amortizar='<td><a href="#"><img src="images/reformular.png" align="right"  border="0"  title="AMORTIZAR" onclick="javascript:location.href=\'gestor.php?mod=venta&tarea=AMORTIZAR&id='.$objeto->ind_tabla_id.'\';"></a></td>';
		}
		
			echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td>'.$link_amortizar.'<td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=venta&tarea=PAGOS&id='.$objeto->ind_tabla_id.'\';"></td></tr></table>
				';
		$conversor = new convertir();
		$id_pago=$objeto->ind_tabla_id;
		?>
		<br/><br/><div id="contenido_reporte" style="clear:both;";>
			<center>
            <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" border="0">
				<tr>
				    <td width="34%">
				        <strong><?php echo _nombre_empresa; ?></strong><BR>
				    <strong>Santa Cruz - Bolivia</strong>
				   </td>
				      <td width="33%"><p align="center" ><strong><h3><center>COMPROBANTE DE PAGO DE CUOTA</center></h3></strong></p></td>
				      <td width="34%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
					<strong>Nro de Venta: </strong> <?php echo $objeto->ind_tabla_id;?> <br>
				    
					<strong>Persona: </strong> <?php echo $objeto->int_nombre.' '.$objeto->int_apellido;?> <br>
						
					<!--<strong>Tipo: </strong> <?php echo $objeto->ven_tipo;?> <br>-->
					<!--<strong>Moneda: </strong> <?php if($objeto->ind_moneda=="1") echo "Bolivianos"; else echo "Dolares";?>-->
                    
					</td>
				    <td align="right">
					<br/><strong>Fecha: </strong> <?php echo $myday;?> <br>
					<strong>Tipo de Cambio: </strong> <?php echo $this->tc;?> <br>
					<strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->ind_usu_id);?>
					</td>
				  </tr>
				 
			</table>
            <br/>
			<table   width="90%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>F Programada</th>
					<th>F Pago</th>
					<th>Observación</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>			
				</thead>
				<tbody>
				<tr>
					<td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_programada);?></td>
					<td><?php echo $conversor->get_fecha_latina($objeto->ind_fecha_pago);?></td>
					
					<td><?php echo $objeto->ind_concepto;?></td>
					<td><?php echo $monto; ?></td>
					<td><?php if($moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>	
				</tbody>
				</table>
				
				<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>
				
				</center>
				<br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
				</div>	
		<?php		
		
	}
	
	function frm_pagar_especial()
	{
		
		$id_venta=$this->obtener_venta($_GET['id']);
		
		$fecha_programada_actual=$this->obtener_fecha_programada($_GET['id']);
		
		$fecha_ultima_cuota_pagada= $_GET['fecha_ultima_cuota_pagada'];
		
		
		if($this->obtener_diferencia_meses($fecha_ultima_cuota_pagada,$fecha_programada_actual,$id_venta) > 1)
		{
			$mensaje='No se puede realizar el Cobro, por que existen Cuotas anteriores en el Plan de Pagos.';
			
			$this->mensaje=$mensaje;
			
			if($this->mensaje<>"")
			{
				$this->formulario->dibujar_mensaje($this->mensaje,$this->link.'?mod='.$this->modulo.'&tarea=CUENTAS&id='.$id_venta,"","Error");
			}
		}
		else
		{
			// Si no existe Pagos Y que la diferencia de Fechas sea = 1 ( Cuota Inicial ,  Primera Cuota )
			if($this->obtener_diferencia_meses($fecha_ultima_cuota_pagada,$fecha_programada_actual,$id_venta)==1 && $this->no_existe_pagos($id_venta)==true)
			{
				$mensaje='No se puede realizar el Cobro, por que existen Cuotas anteriores en el Plan de Pagos.';
				
				$this->mensaje=$mensaje;
				
				if($this->mensaje<>"")
				{
					$this->formulario->dibujar_mensaje($this->mensaje,$this->link.'?mod='.$this->modulo.'&tarea=CUENTAS&id='.$id_venta,"","Error");
				}
			}
			else
			{
				
		
				$conec= new ADO();
				
				$sql="SELECT ind_id,ind_monto,ind_moneda,ind_monto_parcial,ind_valor_form 
					FROM interno_deuda
					where ind_id='".$_GET['id']."'
					";
					
				$conec->ejecutar($sql);
				
				$objeto=$conec->get_objeto();
				
				?>
				<script type="text/javascript">
					function validar_pagar_especial(frm)
					{
					  
					   var monto = parseFloat(document.frm_pagar_especial.monto.value);
					   var pagara = parseFloat(document.frm_pagar_especial.pagara.value);
					   
					   if (pagara > 0 && monto > pagara)
					   {
							  document.frm_pagar_especial.submit();
					   }
					   else
						 $.prompt('Si desea realizar la Cancelacion Total del Pago de Cuota. Realizeló con la Opcion(COBRAR CUOTA) del Listado de Pagos.',{ opacity: 0.8 });
					}
					
					function calcular_saldo()
					{
						document.frm_pagar_especial.debera.value=document.frm_pagar_especial.monto.value - document.frm_pagar_especial.pagara.value;
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
					
				</script>
				<div id="Contenedor_NuevaSentencia">
					<form id="frm_pagar_especial" name="frm_pagar_especial" action="gestor.php?mod=cuentasx&tarea=CUENTAS&acc=especial&op=cobrar&id=<?php echo $_GET['id']; ?>" method="POST" enctype="multipart/form-data">  
						<div id="FormSent">
						  
							<div class="Subtitulo">Datos</div>
								<div id="ContenedorSeleccion">
									
									<!--Inicio-->
									<div id="ContenedorDiv">
									   <div class="Etiqueta" ><span class="flechas1">* </span>Monto de la Cuota</div>
									   <div id="CajaInput">
									   <input type="text" class="caja_texto" readonly="readonly" name="monto" id="monto" size="20" maxlength="250" value="<?php if($objeto->ind_monto_parcial>0){ echo (($objeto->ind_monto - $objeto->ind_monto_parcial) + $objeto->ind_valor_form);} else { echo ($objeto->ind_monto + $objeto->ind_valor_form); }?>"><?php if($objeto->ind_moneda=='1') echo 'Bs'; else echo '$us';?>
									   </div>
									</div>
									<!--Fin-->
									<!--Inicio-->
									<div id="ContenedorDiv">
									   <div class="Etiqueta" ><span class="flechas1">* </span>Monto que Pagara</div>
									   <div id="CajaInput">
									   <input type="text" class="caja_texto" onKeyUp="calcular_saldo();" onKeyPress="return ValidarNumero(event);" name="pagara" id="pagara" size="20" maxlength="250" value="0"><?php if($objeto->ind_moneda=='1') echo 'Bs'; else echo '$us';?>
									   </div>
									</div>
									<!--Fin-->
									<!--Inicio-->
									<div id="ContenedorDiv">
									   <div class="Etiqueta" ><span class="flechas1">* </span>Monto que Debera</div>
									   <div id="CajaInput">
									   <input type="text" readonly="readonly" class="caja_texto" name="debera" id="debera" size="20" maxlength="250" value="0"><?php if($objeto->ind_moneda=='1') echo 'Bs'; else echo '$us';?>
									   </div>
									</div>
									<!--Fin-->
									<!--Inicio-->
									<input type="hidden" readonly="readonly" class="caja_texto" name="moneda_cobro_especial" id="moneda_cobro_especial" size="20" maxlength="10" value="<?php if($objeto->ind_moneda=='1') echo 'Bs'; else echo '$us';?>">
									<!--Fin-->
								</div>
							
								<div id="ContenedorDiv">
								   <div id="CajaBotones">
										<center>
										
											<input type="button" onclick="javascript:validar_pagar_especial();" class="boton" name="" value="Guardar">
											<input type="button" class="boton" name="" value="Volver" onclick="javascript:history.back();">
											
										</center>
								   </div>
								</div>
						</div>
					</form>
				</div>
				<?php
			
			}
		}
		
	}

	
	function cobrar_especial()
	{
		
		$conec= new ADO();
		
		$sql="select cja_cue_id from cajero where cja_usu_id = '".$this->usu->get_id()."'";
			
		$conec->ejecutar($sql);		

		$nume=$conec->get_num_registros();

		if($nume > 0)
		{
			$obj = $conec->get_objeto();
			
			$caja=$obj->cja_cue_id;
			
			if($this->existe_monto_parcial($_GET['id'],$monto_parcial))
			{
				$sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_monto_parcial,ind_interes,ind_capital,ind_int_id,ind_cue_id,ind_cco_id,ind_tabla_id 
			FROM interno_deuda
			where ind_id='".$_GET['id']."'";
			
				$conec->ejecutar($sql);
				
				$objeto=$conec->get_objeto();
				
				
				$monto_parcial=$objeto->ind_monto_parcial;
				
				
				$cadena=$objeto->ind_concepto;
				
				$ind_concepto=explode("(",$cadena);
				
				if($objeto->ind_moneda=='1')
					$moneda='Bs';
				else
					$moneda='$us';
					
				
				$sql="update interno_deuda set 
							ind_estado='Pendiente',
							ind_fecha_pago='".date('Y-m-d')."',
							ind_monto_parcial='".($_POST['pagara'] + $monto_parcial)."',
							ind_concepto='".$ind_concepto[0]." <b>(Solo pago ".($_POST['pagara'] + $monto_parcial).' '.$moneda.")</b>'
							where ind_id = '".$_GET['id']."'";
			}
			else
			{
			
				$sql="update interno_deuda set 
							ind_estado='Pendiente',
							ind_fecha_pago='".date('Y-m-d')."',
							ind_monto_parcial='".$_POST['pagara']."',
							ind_concepto=concat(ind_concepto,' ','<b>(Solo pago ".$_POST['pagara'].' '.$_POST['moneda_cobro_especial'].")</b>')
							where ind_id = '".$_GET['id']."'";
		
			}
			$conec->ejecutar($sql);
			
			/**REFLEJO EN LAS CUENTAS**///
			
			$sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_monto_parcial,ind_interes,ind_capital,ind_int_id,ind_cue_id,ind_cco_id,ind_tabla_id 
			FROM interno_deuda
			where ind_id='".$_GET['id']."'
			";
			
			$conec->ejecutar($sql);
			
			$objeto=$conec->get_objeto();
			
			$tabla_id=$objeto->ind_tabla_id;
			
			//$urb_id=$this->obtener_urbanizacion_venta($tabla_id);
			
			
			
			include_once("clases/registrar_comprobantes.class.php");
			 
			$comp = new COMPROBANTES();	
			
			$cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$this->tc,$objeto->ind_moneda,'',$objeto->ind_int_id,$this->usu->get_id(),'1','1','interno_deuda',$_GET['id']);			   
			
			
			$mde=$_POST['pagara'];
			
			
			
			if($monto_parcial==0)
				$monto_parcial=0;
			else
				$monto_parcial=$monto_parcial;
			
			
			$ind_interes = $objeto->ind_interes - $monto_parcial;
				
			if($ind_interes<0)
			{
				$ind_interes=0;
			}
			
			
			if($ind_interes >= $_POST['pagara'])
			{
				
				$mde=$_POST['pagara'];
				
				
				$this->cuenta_interes($tabla_id,$cue_interes,$cco_interes);
				
				if($objeto->ind_moneda=='1')
				{
					$comp->ingresar_detalle($cmp_id,$mde,$caja,0);
			
					$comp->ingresar_detalle($cmp_id,($mde*(-1)),$cue_interes,$cco_interes,$objeto->ind_concepto);
				}
				else
				{
					$comp->ingresar_detalle($cmp_id,0,$caja,0,'',$mde);
			
					$comp->ingresar_detalle($cmp_id,0,$cue_interes,$cco_interes,$objeto->ind_concepto,($mde*(-1)));
				}
			}
			else
			{
				if(($ind_interes < $_POST['pagara']) && $ind_interes!=0)
				{	
					$this->cuenta_interes($tabla_id,$cue_interes,$cco_interes);
					
					$this->cuenta_capital($tabla_id,$cue_capital,$cco_capital);
					
					if($objeto->ind_moneda=='1')
						$comp->ingresar_detalle($cmp_id,$mde,$caja,0);
					else
						$comp->ingresar_detalle($cmp_id,0,$caja,0,'',$mde);
						
					
						$mde=$ind_interes;
					
					
					if($objeto->ind_moneda=='1')
						$comp->ingresar_detalle($cmp_id,($mde*(-1)),$cue_interes,$cco_interes,$objeto->ind_concepto);
					else
						$comp->ingresar_detalle($cmp_id,0,$cue_interes,$cco_interes,$objeto->ind_concepto,($mde*(-1)));
						
					$saldo= $_POST['pagara'] - $ind_interes;
					
					
						$mde_saldo=$saldo;
					
					
					if($objeto->ind_moneda=='1')
						$comp->ingresar_detalle($cmp_id,($mde_saldo*(-1)),$cue_capital,$cco_capital,$objeto->ind_concepto);
					else
						$comp->ingresar_detalle($cmp_id,0,$cue_capital,$cco_capital,$objeto->ind_concepto,($mde_saldo*(-1)));
				}
				else
				{
					if($ind_interes==0)
					{
						
						$this->cuenta_capital($tabla_id,$cue_capital,$cco_capital);
						
						if($objeto->ind_moneda=='1')
							$comp->ingresar_detalle($cmp_id,$mde,$caja,0);
						else
							$comp->ingresar_detalle($cmp_id,0,$caja,0,'',$mde);
							
						
						$mde=$_POST['pagara'];
						
						
						if($objeto->ind_moneda=='1')
							$comp->ingresar_detalle($cmp_id,($mde*(-1)),$cue_capital,$cco_capital,$objeto->ind_concepto);
						else
							$comp->ingresar_detalle($cmp_id,0,$cue_capital,$cco_capital,$objeto->ind_concepto,($mde*(-1)));
					}
				}
			}
			
			///Estado de la venta
			$sql="SELECT ind_id 
			FROM interno_deuda
			where 
			ind_tabla='cuentasx' and
			ind_tabla_id='$tabla_id' and
			ind_estado='Pendiente'
			";
			
			$conec->ejecutar($sql);

			$num=$conec->get_num_registros();

			if($num==0)
			{
				$sql="update cuentasx set 
							cux_estado='Pagado'
							where cux_id = '$tabla_id'";
		
			
				$conec->ejecutar($sql);
			}	
			///
			
			//$this->imprimir_pago($_GET['id']);
			$this->imprimir_pago_especial($_GET['id'], $_POST['pagara'],$objeto->ind_moneda);
			
		}
		else
		{
			$mensaje='No puede realizar ninguna cobro, por que usted no esta registrado como cajero.';
			
			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		}
	}
	
	function obtener_tipo_plan($cux_id)
	{		
		$conec= new ADO();

		$sql="select cux_tipo_plan from con_cuentasx where cux_id='$cux_id'";
		
		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();
		
		$objeto=$conec->get_objeto();
				
		return $objeto->cux_tipo_plan;
	}
	
	function existe_monto_parcial($cuota,&$monto_parcial)
	{
		$conec= new ADO();
		
		$sql="SELECT ind_id,ind_monto,ind_moneda,ind_concepto,ind_fecha,ind_estado,ind_monto_parcial,ind_int_id,ind_cue_id,ind_cco_id,ind_tabla_id 
			FROM interno_deuda
			where ind_id='".$cuota."'
			";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();	 
		
		$monto_parcial=$objeto->ind_monto_parcial;
		
		if($monto_parcial>0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function cuenta_interes($cux_id,&$cue_interes,&$cco_interes)
	{
		$conec= new ADO();
		
		$sql="select cux_cue_cpinteres,cux_cco_id from cuentasx where cux_id='".$cux_id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$cue_interes=$objeto->cux_cue_cpinteres;
		
		$cco_interes=$objeto->cux_cco_id;
	}
	
	function cuenta_capital($cux_id,&$cue_capital,&$cco_capital)
	{
		$conec= new ADO();
		
		$sql="select cux_cue_cp,cux_cco_id from cuentasx where cux_id='".$cux_id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$cue_capital=$objeto->cux_cue_cp;
		
		$cco_capital=$objeto->cux_cco_id;
	}
	
	function cuenta_urbanizacion_inicial($urb_id,&$cue_urb_inicial,&$cco_urb_inicial)
	{
		$conec= new ADO();
		
		$sql="select urb_cue_cuotainicial,urb_cco_id from urbanizacion where urb_id='".$urb_id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$cue_urb_inicial=$objeto->urb_cue_cuotainicial;
		
		$cco_urb_inicial=$objeto->urb_cco_id;
	}
	
	function cuenta_urbanizacion_formulario($urb_id,&$cue_urb_form,&$cco_urb_form,&$valor_form)
	{
		$conec= new ADO();
		
		$sql="select urb_cue_form,urb_cco_id,urb_valor_form from urbanizacion where urb_id='".$urb_id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$cue_urb_form=$objeto->urb_cue_form;
		
		$cco_urb_form=$objeto->urb_cco_id;
		
		$valor_form=$objeto->urb_valor_form;
	}
	
	function cuenta_urbanizacion_multa($urb_id,&$cue_urb_multa,&$cco_urb_multa)
	{
		$conec= new ADO();
		
		$sql="select urb_cue_cobromulta,urb_cco_id from urbanizacion where urb_id='".$urb_id."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$cue_urb_multa=$objeto->urb_cue_cobromulta;
		
		$cco_urb_multa=$objeto->urb_cco_id;
	}

	function nombre_cuenta($id)
	{
		$conec= new ADO();

		$sql = "select cue_descripcion from cuenta where cue_id='$id'";

		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();

		return $objeto->cue_descripcion;
	}

	function comprobante_cuenta_x($llave)
	{
		$conec= new ADO();

		$sql = "select cux_id,cux_int_id,cux_monto,cux_moneda,cux_concepto,cux_fecha,cux_usu_id,cux_cco_id,cux_estado,cux_tipo, cco_descripcion  
                        from con_cuentasx, con_cuenta_cc
                        where cux_cco_id=cco_id and cux_id='$llave'";
//                echo $sql;
		$conec->ejecutar($sql);
		$num=$conec->get_num_registros();
		$objeto=$conec->get_objeto();

		$this->datos_cuenta($objeto->cux_id,$monto,$pagado,$moneda);

		if($moneda=='1')
			$moneda="Bs";
		else
			$moneda='$us';
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

		$myday = setear_fecha(strtotime($objeto->cux_fecha));
		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=CUENTAS&id='.$_POST['cux_id'].'\';"></td></tr></table>
				';
		?>
		<br><br><div id="contenido_reporte" style="clear:both;">
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td  width="33%">
                                        <strong><?php echo FUNCIONES::parametro("razon_social"); ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong>
				   </td>
                                   <td width="34%"><p align="center" ><strong><h3>COMPROBANTE DE CUENTA POR <?php echo strtoupper($objeto->cux_tipo);?></h3></strong></p></td>
				    <td  width="33%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
                                        <strong>Fecha: </strong> <?php echo $myday;?> <br>						
                                        <strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cux_usu_id);?> <br><br>
					</td>
				    <td align="right">                        
                                        <strong>Persona: </strong> <?php echo $this->nombre_interno($objeto->cux_int_id);?> <br>						
                                        <strong>Centro de Costo: </strong> <?php echo $objeto->cco_descripcion;?> <br><br>
                                    </td>
				  </tr>

			</table>
			<table   width="50%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Concepto</th>
					<th>Monto</th>
					<th>Moneda</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><?php echo $objeto->cux_concepto;?></td>
					<td><?php echo $objeto->cux_monto;?></td>
					<td><?php if($objeto->cux_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
				</tr>
				</tbody></table>
				<br>
				<table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >

				   <tr>
				    <td colspan="2">
				    <strong>Monto: </strong> <?php echo $monto.' '.$moneda;?> <br>
					<strong>Pagado: </strong> <?php echo $pagado.' '.$moneda;?> <br>
					<strong>Saldo: </strong> <?php echo ($monto-$pagado).' '.$moneda;?> <br><br>
					</td>
				  </tr>

			</table>

			<br><br><br><br>
				<table border="0"  width="90%" style="font-size:12px;">
				<tr>
					<td width="50%" align ="center">-------------------------------------</td>
					<td width="50%" align ="center">-------------------------------------</td>
				</tr>
				<tr>
					<td align ="center"><strong>Recibi Conforme</strong></td>
					<td align ="center"><strong>Entregue Conforme</strong></td>
				</tr>
				</table>

			</center></div><br>

		<?php

	}

/////////////////////////////////////////////////////////--  PAGOS INTERESES --///////////////////////////////////////////////////////
	function cuentas_intereses(){
		$this->formulario->dibujar_tarea();
		$this->xpagar_intereses();
	}

	function xpagar_intereses(){
		if($_POST['cux_id']<>"")
		{
			$conec= new ADO();

			$sql="select cja_cue_id from cajero where cja_usu_id = '".$this->usu->get_id()."'";

			$conec->ejecutar($sql);

			$nume=$conec->get_num_registros();

			if($nume > 0)
			{
				$obj = $conec->get_objeto();

				$caja=$obj->cja_cue_id;

				$sql = "insert into cuentax_pago_interes (cupi_cux_id,cupi_monto,cupi_moneda,cupi_tipo_cambio,cupi_fecha,cupi_usu_id,cupi_estado,cupi_monto_interes)
					values('".$_POST['cux_id']."','".$_POST['monto']."','".$_POST['monedacuenta']."','".$_POST['tca']."','".date('Y-m-d')."','".$this->usu->get_id()."','Pagado','".$_POST['interes']."')";

				$conec->ejecutar($sql,false);

				$llave=mysql_insert_id();

				///**REFLEJO EN LAS CUENTAS**///

				include_once("clases/registrar_comprobantes.class.php");

				$comp = new COMPROBANTES();
				///INTERNO
				$sql="select
				cux_id,cux_cue_cp,cux_int_id,cux_moneda,cux_concepto,cux_tipo,cux_cco_id
				from
				cuentasx
				where
				cux_id = '".$_POST['cux_id']."'";

				$conec->ejecutar($sql);

				$obj = $conec->get_objeto();

				//if($_POST['monedacuenta']=='1')
				//{
					$subtotal=$_POST['interes'];
				//}
				//else
				//{
					//$subtotal=$_POST['interes']*$_POST['tca'];
				//}


				$cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$_POST['tca'],$_POST['monedacuenta'],'',$obj->cux_int_id,$this->usu->get_id(),$obj->cux_tipo,'1','cuentax_pago_interes',$llave);



				if($obj->cux_tipo==1)
				{
					if($_POST['monedacuenta']=='1')
					{
						//$this->cuenta_cco_interes_xcobrar($_POST['cux_id'],$cue,$cco);

						$comp->ingresar_detalle($cmp_id,$subtotal,$caja,0);

						$comp->ingresar_detalle($cmp_id,($subtotal * (-1)),$cue,$cco,'Pago de: '.$obj->cux_concepto);
					}
					else
					{
						//$this->cuenta_cco_interes_xcobrar($_POST['cux_id'],$cue,$cco);

						$comp->ingresar_detalle($cmp_id,0,$caja,0,'',$subtotal);

						$comp->ingresar_detalle($cmp_id,0,$cue,$cco,'Pago de: '.$obj->cux_concepto,($subtotal * (-1)));
					}
				}
				else
				{
					if($_POST['monedacuenta']=='1')
					{
						$this->cuenta_cco_interes_xpagar($_POST['cux_id'],$cue,$cco);

						$comp->ingresar_detalle($cmp_id,($subtotal * (-1)),$caja,0);

						$comp->ingresar_detalle($cmp_id,$subtotal,$cue,$cco,'Pago de: '.$obj->cux_concepto);
					}
					else
					{
						$this->cuenta_cco_interes_xpagar($_POST['cux_id'],$cue,$cco);

						$comp->ingresar_detalle($cmp_id,0,$caja,0,'',($subtotal * (-1)));

						$comp->ingresar_detalle($cmp_id,0,$cue,$cco,'Pago de: '.$obj->cux_concepto,$subtotal);
					}

				}

				///**FIN REFLEJO**///

				/*if($_POST['monto']==$_POST['saldo'])
				{
					$sql = "update cuentasx set cux_estado='Pagado' where cux_id='".$_POST['cux_id']."'";

					$conec->ejecutar($sql,false);
				}*/

				$this->nota_de_pago_interes($llave);

			}
			else
			{
				$mensaje='No puede realizar ningun cobro, por que usted no esta registrado como cajero.';

				$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
			}

		}
		else
		{
			$url=$this->link.'?mod='.$this->modulo.'&tarea=PAGO_INTERES_CUENTASX';

			$tipocambio=$this->tc;

			$this->datos_cuenta_interes($_GET['id'],$monto,$pagado,$moneda,$des);

			$saldo=$monto-$pagado;

			if($moneda=='1')
			{
				$mon="Bs";

			}
			else
			{
				$mon='$us';

			}

			?>
			<script>
			function enviar_formulario_interes(){

					var interes=parseFloat(document.frm_sentencia.interes.value);
					var monedacuenta=document.frm_sentencia.monedacuenta.value;
					var saldo=parseFloat(document.frm_sentencia.saldo.value);

					if(interes > 0)
					{
						if(interes > saldo)
						{
							$.prompt('El Monto debe ser Menor o Igual que el saldo y no Mayor.',{ opacity: 0.8 });
						}
						else
						{
							document.frm_sentencia.submit();
						}
					}
					else
					{
						$.prompt('Para registrar el pago debe introducir el monto.',{ opacity: 0.8 });
					}
				}
			function anular_pago_interes(id,tarea){
                            var txt='Esta seguro de anular este pago de Cuota?';
                            $.prompt(txt,{
                                    buttons:{Aceptar:true, Cancelar:false},
                                    callback: function(v,m,f){

                                            if(v){
                                                            location.href='gestor.php?mod=cuentasx&tarea='+tarea+'&acc=anular&cupi_id='+id;
                                            }

                                    }

                            });
                        }
			</script>

			<table align=right border=0><tr><td><a href="gestor.php?mod=cuentasx" title="VOLVER"><img border="0" width="20" src="images/back.png"></a></td></tr></table>

            <table align=right border=0><tr><td><a style="float:left; margin:0 0 0 7px;" class="group" href="sueltos/llamada.php?accion=agregar_divisa"><img border="0" src="images/compra_venta_divisa.png"></a></td></tr></table>

            <!--FancyBox-->
            <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
            <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
            <script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
            <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
            <!--FancyBox-->
            <div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">
				<div id="FormSent">

					<div class="Subtitulo">Pago de Cuenta: <?php echo $des; ?></div>
						<div id="ContenedorSeleccion">


							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto</div>
							   <div id="CajaInput">
							  <input type="text" name="montototal" id="montototal" size="10" value="<?php echo $monto;?>" readonly="readonly"><?php echo $mon;?>
							   </div>
							   <div id="CajaInput">
							   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

							   Tipo de Cambio&nbsp;<input type="text" name="tca" id="tca" size="5" value="<?php echo $tipocambio;?>" readonly="readonly">
							   <input type="hidden" name="cux_id" id="cux_id" value="<?php echo $_GET['id'];?>">
							   <input type="hidden" name="monedacuenta" id="monedacuenta" value="<?php echo $moneda;?>">
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Pagado</div>
							   <div id="CajaInput">
							   <input type="text" name="pagado" id="pagado" size="10" value="<?php echo $pagado;?>" readonly="readonly"><?php echo $mon;?>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Saldo</div>
							   <div id="CajaInput">
							   <input type="text" name="saldo" id="saldo" size="10" value="<?php echo $saldo;?>" readonly="readonly"><?php echo $mon;?>&nbsp;&nbsp;&nbsp;
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto a Pagar</div>
							   <div id="CajaInput">
							   <input type="text" name="monto" id="monto" size="10" value=""><?php echo $mon;?>&nbsp;&nbsp;&nbsp;Nota:<font color="#FF0000">( Al Realizar el ultimo pago debe ser identico al Saldo)</font>
							   </div>
							</div>
							Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto Interes (Saldo)</div>
							   <div id="CajaInput">
							   <input type="text" name="interes" id="interes" size="10" value="<?php echo ($this->obtener_porc_interes($_GET['id'])/100) * $saldo; ?>"><?php echo $mon;?>&nbsp;<?php echo $this->obtener_porc_interes($_GET['id']).'%'; ?>
							   </div>
							</div>
							<!--Fin-->
						</div>

						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
								<?php
								if(!($ver))
								{
									?>
									<input type="button" class="boton" name="" value="Pagar Cuenta" onclick="javascript:enviar_formulario_interes()">
									<?php
								}
								else
								{
									?>
									<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
									<?php
								}
								?>
								</center>
						   </div>
						</div>
				</div>
			</form>
		</div>
        <script type="text/javascript">
			$(document).ready(function(){
				$("a.group").fancybox({
					'hideOnContentClick': false,
					'overlayShow'	: true,
					'zoomOpacity'	: true,
					'zoomSpeedIn'	: 300,
					'zoomSpeedOut'	: 200,
					'overlayOpacity':0.5,

					'frameWidth'	:700,
					'frameHeight'	:350,
					'type'			:'iframe'
				});

				$('a.close').click(function(){
				 $(this).fancybox.close();
				});

			});
		</script>
        <?php

		$this->dibujar_encabezado_pagos_interes();

		$this->mostrar_busqueda_pagos_interes();

		?>

		<?php
		}
	}

	function datos_cuenta_interes($cuenta,&$monto,&$pagado,&$moneda,&$des=""){
            $conec= new ADO();
            $sql = "select cux_monto,cux_moneda,cux_concepto from cuentasx where cux_id='$cuenta'";
            $conec->ejecutar($sql);
            $objeto=$conec->get_objeto();
            $moneda=$objeto->cux_moneda;
            $monto=$objeto->cux_monto;
            $des=$objeto->cux_concepto;
            $sql = "select sum(cup_monto) as pagado from cuentax_pago where cup_cux_id='$cuenta' AND cup_estado='Pagado'";
            $conec->ejecutar($sql);
            $objeto=$conec->get_objeto();
            $pagado=$objeto->pagado;
	}

	function obtener_porc_interes($id){
            $conec= new ADO();
            $sql = "select cux_id,cux_interes_mensual from con_cuentasx where cux_id='$id'";
            $conec->ejecutar($sql);
            $objeto=$conec->get_objeto();
            return $objeto->cux_interes_mensual;
	}

	function cuenta_cco_interes_xcobrar($id,&$cue,&$cco){
            $conec= new ADO();
            $sql="select cux_cue_cpinteres,cux_cco_id from cuentasx where cux_id = $id";
            $conec->ejecutar($sql);
            $objeto=$conec->get_objeto();
            $cue=$objeto->cux_cue_cpinteres;
            $cco=$objeto->cux_cco_id;
	}

	function cuenta_cco_interes_xpagar($id,&$cue,&$cco){
            $conec= new ADO();
            $sql="select cux_cue_cpinteres,cux_cco_id from cuentasx where cux_id = $id";
            $conec->ejecutar($sql);
            $objeto=$conec->get_objeto();
            $cue=$objeto->cux_cue_cpinteres;
            $cco=$objeto->cux_cco_id;
	}

	function nota_de_pago_interes($pago){
            $conec= new ADO();
            $sql = "select	cupi_cux_id,cupi_monto,cupi_moneda,cupi_tipo_cambio,cupi_fecha,cupi_usu_id,cupi_monto_interes,cux_concepto,cux_tipo,cux_cue_cpinteres
            from cuentax_pago_interes 
            inner join cuentasx on (cupi_cux_id=cux_id)
            where cupi_id='$pago'";
            $conec->ejecutar($sql);
            $num=$conec->get_num_registros();
            $objeto=$conec->get_objeto();
            $this->datos_cuenta_interes($objeto->cupi_cux_id,$monto,$pagado,$moneda);
            if($moneda=='1')
                    $moneda="Bs";
            else
                    $moneda='$us';
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

            $myday = setear_fecha(strtotime($objeto->cupi_fecha));
            ////

            echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
                              c.document.write('.$extra1.');
                              var dato = document.getElementById('.$pagina.').innerHTML;
                              c.document.write(dato);
                              c.document.write('.$extra2.'); c.document.close();
                              ">
                            <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                            </a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=PAGO_INTERES_CUENTASX&id='.$_POST['cux_id'].'\';"></td></tr></table>
                            ';
            ?>
            <br><br><div id="contenido_reporte" style="clear:both;";>
                    <center>
                    <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                            <tr>
                                <td  width="40%">
                                <strong><?php echo _nombre_empresa; ?></strong><BR>
                                    <strong>Santa Cruz - Bolivia</strong>
                               </td>
                                <td><center><strong><h3>NOTA DE <?php if($objeto->cux_tipo==1) echo 'COBRO '; else echo 'PAGO '; ?>DE INTERES</h3></strong></center></p></td>
                                <td  width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
                              </tr>
                               <tr>
                                <td colspan="2">
                                <strong>Fecha: </strong> <?php echo $myday;?> <br>
                                    <strong>Tipo de Cambio: </strong> <?php echo $objeto->cupi_tipo_cambio;?> <br>
                                    <strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cupi_usu_id);?> <br><br>
                                    </td>
                                <td align="right">

                                    </td>
                              </tr>

                    </table>
                    <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                            <thead>
                            <tr>
                <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Moneda</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                    <td><?php echo $objeto->cux_concepto;?></td>
                                    <td><?php echo $objeto->cupi_monto_interes;?></td>
                                    <td><?php if($objeto->cupi_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
                            </tr>
                            </tbody></table>
                            <br>
                            <!--<table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >

                               <tr>
                                <td colspan="2">
                                <strong>Monto: </strong> <?php echo $monto.' '.$moneda;?> <br>
                                    <strong>Pagado: </strong> <?php echo $pagado.' '.$moneda;;?> <br>
                                    <strong>Saldo: </strong> <?php echo ($monto-$pagado).' '.$moneda;;?> <br><br>
                                    </td>
                              </tr>

                    </table>-->

                    <br><br><br><br>
                            <table border="0"  width="90%" style="font-size:12px;">
                            <tr>
                                    <td width="50%" align ="center">-------------------------------------</td>
                                    <td width="50%" align ="center">-------------------------------------</td>
                            </tr>
                            <tr>
                                    <td align ="center"><strong>Recibi Conforme</strong></td>
                                    <td align ="center"><strong>Entregue Conforme</strong></td>
                            </tr>
                            </table>

                    </center></div><br>

            <?php

	}

	function dibujar_encabezado_pagos_interes()
	{

		?><div style="clear:both;"></div><center>
		<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
			<tr>

				<td><center><strong><h3>LISTADO DE PAGOS</h3></strong></center></p></td>

			</tr>

		</table>
        <br />
		<table class="tablaLista" cellpadding="0" cellspacing="0" width="60%">
			<thead>
				<tr>
					<th >
                                            Nro.
					</th>
					<th >
                                            Monto
					</th>
					<th >
                                            Moneda
					</th>
					<th >
                                            Tipo Cambio
					</th>
					<th >
                                            Fecha
					</th>
					<th class="tOpciones" width="100px">
                                            Opciones
					</th>
				</tr>
			</thead>
			<tbody>
		<?PHP

	}



	function mostrar_busqueda_pagos_interes(){
            $conversor = new convertir();
            $conec=new ADO();
            $sql="SELECT
            cupi_id,cupi_cux_id,cupi_monto,cupi_moneda,cupi_tipo_cambio,cupi_fecha,cupi_hora,cupi_usu_id,cupi_estado,cupi_monto_interes
            FROM
            cuentax_pago_interes
            WHERE
            cupi_cux_id='".$_GET['id']."' AND cupi_estado='Pagado'
            ORDER BY
            cupi_id ASC";
            $conec->ejecutar($sql);
            $num=$conec->get_num_registros();
            $sumatorio=0;
            for($i=0;$i<$num;$i++){
                $objeto=$conec->get_objeto();
                $sumatorio+=$objeto->cupi_monto_interes;
                echo '<tr class="busqueda_campos">';
                ?>
                        <td align="left">
                            <center>
                            <?php echo $i+1; ?>
                            </center>
                        </td>
                        <td align="left">
                            <center>
                            <?php echo $objeto->cupi_monto_interes;?>
                            </center>
                        </td>
                        <td align="left">
                            <center>
                            <?php if($objeto->cupi_moneda==1){echo "Bolivianos"; } else { echo "Dolares"; }?>
                            </center>
                        </td>
                        <td align="left">
                            <center>
                            <?php echo $objeto->cupi_tipo_cambio;?>
                            </center>
                        </td>
                        <td align="left">
                            <center>
                            <?php echo $conversor->get_fecha_latina($objeto->cupi_fecha);?>
                            </center>
                        </td>
                        <td>
                            <center>
                            <a href="gestor.php?mod=cuentasx&tarea=PAGO_INTERES_CUENTASX&acc=ver&cupi_id=<?php echo $objeto->cupi_id;?>&cupi_fecha=<?php echo $objeto->cupi_fecha;?>"><img src="images/ver.png" alt="VER" title="VER" border="0"></a>
                    <!--	gestor.php?mod=credito_prendario&tarea=INTERES&acc=anular&cre_id=<?php //echo $objeto->cpi_id;?> -->
                            <a href="javascript:anular_pago_interes('<?php echo $objeto->cupi_id;?>','PAGO_INTERES_CUENTASX');" target="contenido" ><img src="images/anular.png" alt="ANULAR" title="ANULAR" border="0"></a>
                            </center>
                        </td>

                <?php

                echo "</tr>";

                $conec->siguiente();

            }
            echo "</tbody>";
                    ?>
                    <tfoot>
                            <tr>
                            <td><b>Total</b></td>
                            <td><? echo $sumatorio; ?></td>
                            <td colspan="4" >&nbsp;</td></tr>
                    </tfoot>
                    <?

            echo "</table></center><br>";
	}

	function anular_pago_interes($id){
            $conec= new ADO();
            $sql="update cuentax_pago_interes set
                                                    cupi_estado='Anulado'
                                                    where cupi_id = '$id'";
            $conec->ejecutar($sql);
            include_once("clases/registrar_comprobantes.class.php");
            $comp = new COMPROBANTES();
            $comp->anular_comprobante_tabla('cuentax_pago_interes',$id);
            $mensaje='Pago Anulado Correctamente!!!';
            $this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}

	function mostrar_nota_pago_historial_interes($pago,$fecha){
            $conec= new ADO();
            $sql = "SELECT
            cupi_cux_id,cupi_monto,cupi_moneda,cupi_tipo_cambio,cupi_fecha,cupi_usu_id,cupi_monto_interes,cux_concepto,cux_tipo
            FROM
            cuentax_pago_interes INNER JOIN cuentasx ON (cupi_cux_id=cux_id)
            WHERE cupi_id='$pago' AND cupi_fecha <= '$fecha'";
            $conec->ejecutar($sql);
            $num=$conec->get_num_registros();
            $objeto=$conec->get_objeto();
            $this->datos_cuenta_historial_interes($objeto->cupi_cux_id,$objeto->cupi_fecha,$monto,$pagado,$moneda);
            if($moneda=='1')
                $moneda="Bs";
            else
                $moneda='$us';
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
            $myday = setear_fecha(strtotime($objeto->cup_fecha));
            echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
                              c.document.write('.$extra1.');
                              var dato = document.getElementById('.$pagina.').innerHTML;
                              c.document.write(dato);
                              c.document.write('.$extra2.'); c.document.close();
                              ">
                            <img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
                            </a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=cuentasx&tarea=CUENTAS&id='.$_POST['cux_id'].'\';"></td></tr></table>
                            ';
            ?>
            <br><br><div id="contenido_reporte" style="clear:both;";>
                    <center>
                    <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
                            <tr>
                                <td  width="40%">
                                <strong><?php echo _nombre_empresa; ?></strong><BR>
                                    <strong>Santa Cruz - Bolivia</strong>
                               </td>
                                <td><center><strong><h3>NOTA DE <?php if($objeto->cux_tipo==1) echo 'COBRO'; else echo 'PAGO'; ?></h3></strong></center></p></td>
                                <td  width="40%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
                              </tr>
                               <tr>
                                <td colspan="2">
                                <strong>Fecha: </strong> <?php echo $myday;?> <br>
                                    <strong>Tipo de Cambio: </strong> <?php echo $objeto->cupi_tipo_cambio;?> <br>
                                    <strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->cupi_usu_id);?> <br><br>
                                    </td>
                                <td align="right">

                                    </td>
                              </tr>

                    </table>
                    <table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
                            <thead>
                            <tr>
                                    <th>Cuenta</th>
                                    <th>Monto</th>
                                    <th>Moneda</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                    <td><?php echo $objeto->cux_concepto;?></td>
                                    <td><?php echo $objeto->cupi_monto_interes;?></td>
                                    <td><?php if($objeto->cupi_moneda=='1') echo 'Bolivianos'; else echo 'Dolares';?></td>
                            </tr>
                            </tbody></table>
                            <br>
                            <!--<table style="font-size:12px;" width="50%"  cellpadding="0" cellspacing="0" >

                               <tr>
                                <td colspan="2">
                                <strong>Monto: </strong> <?php echo $monto.' '.$moneda;?> <br>
                                    <strong>Pagado: </strong> <?php echo $pagado.' '.$moneda;;?> <br>
                                    <strong>Saldo: </strong> <?php echo ($monto-$pagado).' '.$moneda;;?> <br><br>
                                    </td>
                              </tr>

                    </table>-->

                    <br><br><br><br>
                            <table border="0"  width="90%" style="font-size:12px;">
                            <tr>
                                    <td width="50%" align ="center">-------------------------------------</td>
                                    <td width="50%" align ="center">-------------------------------------</td>
                            </tr>
                            <tr>
                                    <td align ="center"><strong>Recibi Conforme</strong></td>
                                    <td align ="center"><strong>Entregue Conforme</strong></td>
                            </tr>
                            </table>
                    </center></div><br>
            <?php
	}

	function datos_cuenta_historial_interes($cuenta,$fecha,&$monto,&$pagado,&$moneda,&$des=""){
            $conec= new ADO();
            $sql = "select cux_monto,cux_moneda,cux_concepto from cuentasx where cux_id='$cuenta'";
            $conec->ejecutar($sql);
            $objeto=$conec->get_objeto();
            $moneda=$objeto->cux_moneda;
            $monto=$objeto->cux_monto;
            $des=$objeto->cux_concepto;
            $sql = "select sum(cupi_monto_interes) as pagado from cuentax_pago_interes where cupi_cux_id='$cuenta' AND cupi_fecha <= '$fecha' AND cupi_estado='Pagado'";
            $conec->ejecutar($sql);
            $objeto=$conec->get_objeto();
            $pagado=$objeto->pagado;
	}
}
?>