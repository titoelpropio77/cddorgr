<script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../js/funciones.js"></script>
<link href="../css/estilos.css" rel="stylesheet" type="text/css">
<link href="../css/examples.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../js/jquery.maskedinput-1.3.min.js"></script> 
<script type="text/javascript" src="../js/jquery-impromptu.2.7.min.js"></script> 
<script>
jQuery(function($){
   $("#int_fecha_ingreso").mask("99/99/9999");
   $("#int_fecha_nacimiento").mask("99/99/9999");   
});
</script>
 <!--AutoSuggest-->
<script type="text/javascript" src="../js/bsn.AutoSuggest_c_2.0.js"></script>
<link rel="stylesheet" href="../css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
<!--AutoSuggest-->

<script>
function obtener_tipo_cambio(tipo)
{	
	var	valores="tarea=tipo_cambio_cv&tipo="+tipo;		

	ejecutar_ajax('../ajax.php','tipo_cambio',valores,'POST');									
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

<style>
#hiddenemp{display:none;}
</style>
<script >
	//$("#frm_agregar").bind("submit", function() {

	    //$.fancybox.showActivity();

		//$.ajax({
			//type	: "POST",
			//cache	: false,
			//url		: "/admin/sueltos/agregarpersona.php",
			//data	: $(this).serializeArray(),
			//success: function(data) {
				//$.fancybox(data);

				//$('#hiddenemp2').css({opacity:0}).remove();
				//$('#hiddenemp').css({display:'block'});
				
			//}
		//});

		//return false;
	//});
</script>
<script>
function enviar_formulario_persona()
{
	var cvd_int_id = document.frm_agregar.cvd_int_id.value;
	var cvd_particular = document.frm_agregar.cvd_particular.value;
	var cvd_tipo = document.frm_agregar.cvd_tipo.value;
	var cvd_monto = document.frm_agregar.cvd_monto.value;
	var cvd_tipo_cambio = document.frm_agregar.cvd_tipo_cambio.value;
	//document.frm_agregar.submit();
	if(cvd_tipo!='' && cvd_monto!='' && cvd_tipo_cambio!='' && (cvd_int_id!='' || cvd_particular!=''))
	{
		document.frm_agregar.submit();
	}
	else
	{
		$.prompt('Los campos Tipo, Monto, Tipo de Cambio, Persona o Particular son requeridos.',{ opacity: 0.8 });
	}
}
</script>

<?php
require_once('mysql.php');
require("../clases/mytime_int.php");
require("../clases/conversiones.class.php");
require('../clases/session.class.php');
require('../config/constantes.php');

?>
<!--<a href="javascript:window.parent.print();"><img src="images/printer.png" border="0" width="16"></a>-->
<? 
$red="gestor.php?mod=venta&tarea=AGREGAR";

/* ================================================ */
	function dibujar_mensaje($mensaje)
	{
		?>
        <link href="../css/estilos.css" rel="stylesheet" type="text/css">
        <div>
		<div class="fila_formulario_cabecera">VOLVER</div>
		<div>
		<br>	
		<table class="tablaLista" cellpadding="0" cellspacing="0">
			<thead>
				<tr bgcolor="#DEDEDE">
					<th >
					</th>
				</tr>
			</thead>
	    
			<tbody>
			<tr>
				<td>

					<center><h1><?php echo $mensaje; ?></h1></center>
					
				</td>
			</tr>
			</tbody>
		</table>
		<br><center>
		<form id="form_eliminacion" action="<?php echo "../gestor.php?mod=venta&tarea=AGREGAR"; ?>" method="POST" enctype="multipart/form-data">
						<input type="button" value="Volver" class="boton" onclick="cierra();">
		</form>
        <!--<a href="#" onclick="cierra();"> Cerrar</a>-->
        <script>
        function cierra()
		{
			parent.$.fn.fancybox.close();
			return false
		}
        </script>
		</center>
		</div>			
	</div>
        <?php
	}
	
	function revisar_registro()
	{
		$query= new QUERY();
		
		$sql = "SELECT * FROM interno WHERE int_nombre='".trim($_POST['int_nombre'])."' AND int_apellido='".trim($_POST['int_apellido'])."'"; 

		$query->consulta($sql);

		$num=$query->num_registros();

		if($num>0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	function insertar_tcp()
	{
			$query= new QUERY();

			$conversor = new convertir();
			
			$session=new SESSION;
			
			$sql="insert into compra_venta_divisa (cvd_fecha,cvd_hora,cvd_tipo,cvd_monto,cvd_tipo_cambio,cvd_int_id,cvd_particular,cvd_usu_id,cvd_estado) values ('".date('Y-m-d')."','".date('H:i:s')."','".$_POST['cvd_tipo']."','".$_POST['cvd_monto']."','".$_POST['cvd_tipo_cambio']."','".$_POST['cvd_int_id']."','".$par."','".$session->get('id')."','Habilitado')";
			
			$query->consulta($sql);
			
			$cvd_id = mysql_insert_id();
			
			if($_POST['cvd_tipo']=='compra')
			{
				 $sql="select par_cuenta_ingreso_compra,par_cuenta_egreso_compra,par_cc_compra_divisa from ad_parametro";

				 $query->consulta($sql);
				
				 list($cue_ing,$cue_egre,$cc)=$query->valores_fila();
				 
				 //$cue_ing=$objeto->par_cuenta_ingreso_compra;
				 //$cue_egre=$objeto->par_cuenta_egreso_compra;
				 //$cc=$objeto->par_cc_compra_divisa;
				 $concepto_ingreso='Ingreso por Compra de Divisas';
				 $concepto_egreso='Egreso por Compra de Divisas';
			}
			else
			{
				if($_POST['cvd_tipo']=='venta')
				{
					$sql="select par_cuenta_ingreso_venta,par_cuenta_egreso_venta,par_cc_venta_divisa from ad_parametro";

				 	$query->consulta($sql);
				
				    list($cue_ing,$cue_egre,$cc)=$query->valores_fila();
				 
				 	//$cue_ing=$objeto->par_cuenta_ingreso_venta;
				 	//$cue_egre=$objeto->par_cuenta_egreso_venta;
				 	//$cc=$objeto->par_cc_venta_divisa;
					$concepto_ingreso='Ingreso por Venta de Divisas';
				 	$concepto_egreso='Egreso por Venta de Divisas';
				}
			}
			
			
			
			
			
			$monto = $_POST['cvd_monto'];
			
			$tipo_cambio=$_POST['cvd_tipo_cambio'];
			
			if($_POST['cvd_tipo']=='venta')
			{
			
				$cmp_id = ingresar_comprobante(date('Y-m-d'),$_POST['cvd_tc'],2,'',$_POST['cvd_int_id'],$session->get('id'),1,1,'compra_venta_divisa',$cvd_id);
			
				ingresar_detalle($cmp_id,($monto*$tipo_cambio),$caja,0);
				ingresar_detalle($cmp_id,(($monto*$tipo_cambio)*(-1)),$cue_ing,$cc,$concepto_ingreso);
				
			
				$cmp_id = ingresar_comprobante(date('Y-m-d'),$_POST['cvd_tc'],2,'',$_POST['cvd_int_id'],$session->get('id'),2,1,'compra_venta_divisa',$cvd_id);
			
				ingresar_detalle($cmp_id,0,$caja,0,'',$monto*(-1));
				ingresar_detalle($cmp_id,0,$cue_egre,$cc,$concepto_egreso,$monto);
			
			}
			else
			{
				$cmp_id = ingresar_comprobante(date('Y-m-d'),$_POST['cvd_tc'],2,'',$_POST['cvd_int_id'],$session->get('id'),1,1,'compra_venta_divisa',$cvd_id);
			
				ingresar_detalle($cmp_id,0,$caja,0,'',$monto);
				ingresar_detalle($cmp_id,0,$cue_ing,$cc,$concepto_ingreso,$monto*(-1));
			
				$cmp_id = ingresar_comprobante(date('Y-m-d'),$_POST['cvd_tc'],2,'',$_POST['cvd_int_id'],$session->get('id'),2,1,'compra_venta_divisa',$cvd_id);
			
				ingresar_detalle($cmp_id,($monto*$tipo_cambio)*(-1),$caja,0);
				ingresar_detalle($cmp_id,($monto*$tipo_cambio),$cue_egre,$cc,$concepto_egreso);
			}
			
			comprobante_divisa($cvd_id);				
	}
	
	function validar_formulario()
	{
		if($_POST['cvd_tipo']!="" && $_POST['cvd_monto']!="")
		{
			return true;
		}
		else
		{
			return false;
		}
	}
/* ================================================ */

if($_POST['oculto1']==1)
{
	if(validar_formulario())
	{
		//if(revisar_registro())
		//{
			insertar_tcp();
			//dibujar_mensaje('PERSONA AGREGADA CORRECTAMENTE');
		//}
		//else
		//{
			//dibujar_mensaje('LA PERSONA YA ESTA REGISTRADA');
		//}	
	}
	else
	{
		dibujar_mensaje('LOS CAMPOS "NOMBRE" Y "APELLIDO" SON REQUERIDOS ');
	}
}
else
{

?>
	<script>
    function cierra()
    {
        parent.$.fn.fancybox.close();
        return false
    }
    </script>
	<div id="hiddenemp2" >
		<div class="fila_formulario_cabecera" style="background:#94CBE6;"> <?PHP echo "COMPRA / VENTA DE DIVISAS"; ?></div>
		<div id="Contenedor_NuevaSentencia" style="width:98%;">		
			<form id="frm_agregar" name="frm_agregar" action="<? echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">  
				
                <div id="FormSent">

					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
							   <div id="CajaInput">
								    <input name="cvd_int_id" id="cvd_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['cvd_int_id']?>" size="2">
									<input name="cvd_nombre_persona" id="cvd_nombre_persona" type="text" class="caja_texto" value="<?php echo $_POST['cvd_nombre_persona']?>" size="40">
							   </div>
							</div>
							<!--Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" >Particular</div>
							   <div id="CajaInput">
								    <input name="cvd_particular" id="cvd_particular" type="texto" class="caja_texto" value="<?php echo $_POST['cvd_particular']?>" size="40">
							   </div>
							</div>
							<!--Fin-->
                            <!--Inicio
                            <div id="ContenedorDiv">
                               <div class="Etiqueta" >Fecha</div>
                                 <div id="CajaInput">
                                    <input readonly="readonly" class="caja_texto" name="cvd_fecha" id="cvd_fecha" size="12" value="<?php echo $_POST['cvd_fecha'];?>" type="text">
                                        <input name="but_fecha_pago2" id="but_fecha_pago2" class="boton_fecha" value="..." type="button">
                                        <script type="text/javascript">
                                                        Calendar.setup({inputField     : "cvd_fecha"
                                                                        ,ifFormat     :     "%Y-%m-%d",
                                                                        button     :    "but_fecha_pago2"
                                                                        });
                                        </script>
                                </div>		
                            </div>
                            Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Tipo</div>
							   <div id="CajaInput">
								    <select name="cvd_tipo" id="cvd_tipo" onchange="javascript:obtener_tipo_cambio(this.value);">
                                    	<option value="">Seleccione</option>
                                    	<option value="compra" <?php if($_POST['cvd_tipo']=='compra') {?> selected="selected" <?php } ?>>Compra</option>
                                        <option value="venta" <?php if($_POST['cvd_tipo']=='venta') {?> selected="selected" <?php } ?>>Venta</option>
                                    </select>
							   </div>
							</div>
							<!--Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto</div>
							   <div id="CajaInput">
								    <input name="cvd_monto" id="cvd_monto" type="texto" class="caja_texto" value="<?php echo $_POST['cvd_monto']?>" size="10">
							   </div><span>&nbsp;$us</span>
							</div>
							<!--Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Tipo de Cambio</div>
							   <div id="CajaInput">
                               		<div id="tipo_cambio">
								    <input name="cvd_tipo_cambio" id="cvd_tipo_cambio" readonly="readonly" type="texto" class="caja_texto" value="<?php echo $_POST['cvd_tipo_cambio']?>" size="10" onKeyPress="return ValidarNumero(event);">
                                    </div>
							   </div>
							</div>
							<!--Fin-->
                            
						</div>

						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
									<input type="button" class="boton" name="" value="Guardar" onclick="javascript:enviar_formulario_persona();">
									<input type="button" class="boton" name="" value="Volver" onclick="cierra();">
                                    
									<input name="oculto1" id="oculto1" readonly="readonly" type="hidden" class="caja_texto" value="1" size="2">									
								</center>								
                                
						   </div>
						</div>
				</div>
			</form>
		</div>
        <script type="text/javascript" >
        var options2 = {
					script:"persona.php?json=true&",
					varname:"input",
					minchars:3,
					timeout:10000,
					noresults:"No se encontro ninguna persona",
					json:true,
					callback: function (obj) { document.getElementById('cvd_int_id').value = obj.id; }
				};
		var as_json2 = new _bsn.AutoSuggest('cvd_nombre_persona', options2);
		</script>
	</div>	
	<div id="hiddenemp">
		<div class="fila_formulario_cabecera">VOLVER A VENTA</div>
		<div>
		<br>	
		<table class="tablaLista" cellpadding="0" cellspacing="0">
			<thead>
				<tr bgcolor="#DEDEDE">
					<th >
					</th>
				</tr>
			</thead>
	    
			<tbody>
			<tr>
				<td>

					<center><h1>PERSONA AGREGADA CORRECTAMENTE</h1></center>
					
				</td>
			</tr>
			</tbody>
		</table>
		<br><center>
		<form id="form_eliminacion" action="<?php echo $red; ?>" method="POST" enctype="multipart/form-data">
						<input type="submit" value="Volver" class="boton">
		</form>
		</center>
		</div>			
	</div>			
<? 
}

function comprobante_divisa($cvd_id)
{
	$query= new QUERY();

	$sql = "select
			cvd_id,cvd_fecha,cvd_hora,cvd_tipo,cvd_monto,cvd_tipo_cambio,cvd_int_id,cvd_particular,cvd_usu_id,cvd_estado,int_nombre,int_apellido
			from
			compra_venta_divisa left join interno on(cvd_int_id=int_id)
			where cvd_id in ($cvd_id)
			";

	$query->consulta($sql);

	list($cvd_id,$cvd_fecha,$cvd_hora,$cvd_tipo,$cvd_monto,$cvd_tipo_cambio,$cvd_int_id,$cvd_particular,$cvd_usu_id,$cvd_estado,$int_nombre,$int_apellido)=$query->valores_fila();
	
	//$num=$conec->get_num_registros();

	//$objeto=$conec->get_objeto();

	//$tc=$objeto->ven_tipo_cambio;

	//$desc=$objeto->ven_descuento;

	////
	$pagina="'contenido_reporte'";

	$page="'about:blank'";

	$extpage="'reportes'";

	$features="'left=100,width=800,height=500,top=0,scrollbars=yes'";

	$extra1="'<html><head><title>Vista Previa</title><head>
			<link href=../css/estilos.css rel=stylesheet type=text/css />
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

	$myday = setear_fecha(strtotime($cvd_fecha));
	////

	echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
			  c.document.write('.$extra1.');
			  var dato = document.getElementById('.$pagina.').innerHTML;
			  c.document.write(dato);
			  c.document.write('.$extra2.'); c.document.close();
			  ">
			<img src="../images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
			</a></td><td><img src="../images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=divisa&tarea=AGREGAR\';"></td></tr></table>
			';
	
	?>
		<br><br><div id="contenido_reporte" style="clear:both;">
		<center>
		<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" border="0">
			<tr>
				<td width="34%">
					<strong><?php echo _nombre_empresa; ?></strong><BR>
				<strong>Santa Cruz - Bolivia</strong>
			   </td>
				  <td width="33%"><p align="center" ><strong><h3><center><?php if($cvd_tipo=='venta') echo 'VENTA'; else echo 'COMPRA'; ?> DE DIVISAS</center></h3></strong></p></td>
				  <td width="34%"><div align="right"><img src="../imagenes/micro.png" width="" /></div></td>
			  </tr>
			   <tr>
				<td colspan="2">
				<strong>N. Tra: </strong> <?php //echo substr($cvd_id, 1, -1);?><?php echo $cvd_id;?> <br>
				<?php
				if($cvd_int_id <> 0)
				{
					?>

					<strong>Persona: </strong> <?php echo $int_nombre.' '.$int_apellido;?> <br>

					<?php
				}
				else
				{
					?>
					<strong>Comprador: </strong> <?php echo $cvd_particular;?> <br>
					<?php
				}
				?>


				<strong>Tipo: </strong> <?php echo strtoupper($cvd_tipo);?> <br>
				<strong>Moneda: </strong> <?php if($objeto->ven_moneda=="1") echo "Bolivianos"; else echo "Dolares";?> <br><br>
				</td>
				<td align="right">
				<strong>Fecha: </strong> <?php echo $myday;?> <br>
				<strong>Tipo de Cambio: </strong> <?php echo $cvd_tipo_cambio;?> <br>
				<strong>Usuario: </strong> <?php echo nombre_persona($cvd_usu_id);?> <br>
				</td>
			  </tr>

		</table>
		
		<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th>Concepto</th>
				<th>Monto</th>
				<!--
				<th class="tOpciones" >Bs</th>
				<th class="tOpciones" >$us</th>
				-->
			</tr>
			</thead>
			<tbody>
			
	<?php
	
	$query= new QUERY();

	$sql = "select * from compra_venta_divisa where cvd_id=$cvd_id";

	$query->consulta($sql);

	list($cvd_id,$cvd_fecha,$cvd_hora,$cvd_tipo,$cvd_monto,$cvd_tipo_cambio,$cvd_int_id,$cvd_particular,$cvd_usu_id,$cvd_estado)=$query->valores_fila();

	
	if($cvd_tipo=='venta')
	{
	?>
	<tr>
		<td>
		<?php
		
		?>
			Recibimos la suma de <?php 
			$saldo=$ocvd_monto*$cvd_tipo_cambio;
			$aux=intval ($saldo);
				
				if($aux==$saldo)
				{
					
					echo strtoupper(num2letras($saldo)).'&nbsp;&nbsp;00/100';	
				}
				else
				{
					$val=explode('.',$saldo);
					
					echo strtoupper(num2letras($val[0]));	
					
					if(strlen($val[1])==1)
						echo '&nbsp;&nbsp;'.$val[1].'0/100';
					else
						echo '&nbsp;&nbsp;'.$val[1].'/100';
				} ?> BOLIVIANOS
		</td>
		<td>
			<?php echo ($cvd_monto*$cvd_tipo_cambio).' Bs'; 
			$saldo=($cvd_monto*$cvd_tipo_cambio);
			?>
		</td>
		
	</tr>
	<tr>
		<td>
		
			Por la venta de <?php 
			//echo strtoupper($this->num2letras($objeto->cvd_monto)).'&nbsp;&nbsp;00/100'; 
			$saldo=$cvd_monto;
			$aux=intval ($saldo);
				
				if($aux==$saldo)
				{
					
					echo strtoupper(num2letras($saldo)).'&nbsp;&nbsp;00/100';	
				}
				else
				{
				
					$val=explode('.',$saldo);
					
					echo strtoupper(num2letras($val[0]));	
					
					if(strlen($val[1])==1)
						echo '&nbsp;&nbsp;'.$val[1].'0/100';
					else
						echo '&nbsp;&nbsp;'.$val[1].'/100';
				}
			?> DOLARES
		</td>
		<td>
			<?php echo ($cvd_monto).' $us'; ?>
		</td>
	</tr>
	<?php
	}
	else
	{
	?>
	<tr>
		<td>
			Recibimos la suma de <?php //echo strtoupper($this->num2letras($objeto->cvd_monto)).'&nbsp;&nbsp;00/100'; 
			
			//echo strtoupper($this->num2letras($objeto->cvd_monto)).'&nbsp;&nbsp;00/100'; 
			$saldo=$cvd_monto;
			$aux=intval ($saldo);
				
				if($aux==$saldo)
				{
					
					echo strtoupper(num2letras($saldo)).'&nbsp;&nbsp;00/100';	
				}
				else
				{
				
					$val=explode('.',$saldo);
					
					echo strtoupper(num2letras($val[0]));	
					
					if(strlen($val[1])==1)
						echo '&nbsp;&nbsp;'.$val[1].'0/100';
					else
						echo '&nbsp;&nbsp;'.$val[1].'/100';
				}
			
			?> DOLARES
		</td>
		<td>
			<?php echo $cvd_monto.' $us' ?>
		</td>
		
	</tr>
	<tr>
		<td>
			Por la venta de <?php //echo strtoupper($this->num2letras($objeto->cvd_monto*$objeto->cvd_tipo_cambio)).'&nbsp;&nbsp;00/100'; 
			
			//echo strtoupper($this->num2letras($objeto->cvd_monto)).'&nbsp;&nbsp;00/100'; 
			$saldo=($cvd_monto*$cvd_tipo_cambio);
			$aux=intval ($saldo);
				
				if($aux==$saldo)
				{
					
					echo strtoupper(num2letras($saldo)).'&nbsp;&nbsp;00/100';	
				}
				else
				{
				
					$val=explode('.',$saldo);
					
					echo strtoupper(num2letras($val[0]));	
					
					if(strlen($val[1])==1)
						echo '&nbsp;&nbsp;'.$val[1].'0/100';
					else
						echo '&nbsp;&nbsp;'.$val[1].'/100';
				}
			
			?> BOLIVIANOS
		</td>
		<td>
			<?php echo (($cvd_monto * $cvd_tipo_cambio)).' Bs'; ?>
		</td>
	</tr>
	<?php
	}
	?>
	</tbody>
	</table>
	
	</br>
	<table border="0"  width="70%" style="font-size:12px;">
	
		</br></br></br>
		
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
    
	<!--<br><br><br><br>
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

	</center>-->
	<br><table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
	</div><br>
	<?php
}

function num2letras($num, $fem = false, $dec = true) 
{ 
/*! 
  @function num2letras () 
  @abstract Dado un n?mero lo devuelve escrito. 
  @param $num number - N?mero a convertir. 
  @param $fem bool - Forma femenina (true) o no (false). 
  @param $dec bool - Con decimales (true) o no (false). 
  @result string - Devuelve el n?mero escrito en letra. 

*/ 
//if (strlen($num) > 14) die("El n?mero introducido es demasiado grande"); 
	   $matuni[2]  = "dos"; 
	   $matuni[3]  = "tres"; 
	   $matuni[4]  = "cuatro"; 
	   $matuni[5]  = "cinco"; 
	   $matuni[6]  = "seis"; 
	   $matuni[7]  = "siete"; 
	   $matuni[8]  = "ocho"; 
	   $matuni[9]  = "nueve"; 
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
	   $matsub[3]  = 'mill'; 
	   $matsub[5]  = 'bill'; 
	   $matsub[7]  = 'mill'; 
	   $matsub[9]  = 'trill'; 
	   $matsub[11] = 'mill'; 
	   $matsub[13] = 'bill'; 
	   $matsub[15] = 'mill'; 
	   $matmil[4]  = 'millones'; 
	   $matmil[6]  = 'billones'; 
	   $matmil[7]  = 'de billones'; 
	   $matmil[8]  = 'millones de billones'; 
	   $matmil[10] = 'trillones'; 
	   $matmil[11] = 'de trillones'; 
	   $matmil[12] = 'millones de trillones'; 
	   $matmil[13] = 'de trillones'; 
	   $matmil[14] = 'billones de trillones'; 
	   $matmil[15] = 'de billones de trillones'; 
	   $matmil[16] = 'millones de billones de trillones'; 

	   $num = trim((string)@$num); 
	   if ($num[0] == '-') { 
	      $neg = 'menos '; 
	      $num = substr($num, 1); 
	   }else 
	      $neg = ''; 
	   while ($num[0] == '0') $num = substr($num, 1); 
	   if ($num[0] < '1' or $num[0] > 9) $num = '0' . $num; 
	   $zeros = true; 
	   $punt = false; 
	   $ent = ''; 
	   $fra = ''; 
	   for ($c = 0; $c < strlen($num); $c++) { 
	      $n = $num[$c]; 
	      if (! (strpos(".,'''", $n) === false)) { 
	         if ($punt) break; 
	         else{ 
	            $punt = true; 
	            continue; 
	         } 

	      }elseif (! (strpos('0123456789', $n) === false)) { 
	         if ($punt) { 
	            if ($n != '0') $zeros = false; 
	            $fra .= $n; 
	         }else 

	            $ent .= $n; 
	      }else 

	         break; 

	   } 
	   $ent = '     ' . $ent; 
	   if ($dec and $fra and ! $zeros) { 
	      $fin = ' coma'; 
	      for ($n = 0; $n < strlen($fra); $n++) { 
	         if (($s = $fra[$n]) == '0') 
	            $fin .= ' cero'; 
	         elseif ($s == '1') 
	            $fin .= $fem ? ' una' : ' un'; 
	         else 
	            $fin .= ' ' . $matuni[$s]; 
	      } 
	   }else 
	      $fin = ''; 
	   if ((int)$ent === 0) return 'Cero ' . $fin; 
	   $tex = ''; 
	   $sub = 0; 
	   $mils = 0; 
	   $neutro = false; 
	   while ( ($num = substr($ent, -3)) != '   ') { 
	      $ent = substr($ent, 0, -3); 
	      if (++$sub < 3 and $fem) { 
	         $matuni[1] = 'una'; 
	         $subcent = 'as'; 
	      }else{ 
	         $matuni[1] = $neutro ? 'un' : 'uno'; 
	         $subcent = 'os'; 
	      } 
	      $t = ''; 
	      $n2 = substr($num, 1); 
	      if ($n2 == '00') { 
	      }elseif ($n2 < 21) 
	         $t = ' ' . $matuni[(int)$n2]; 
	      elseif ($n2 < 30) { 
	         $n3 = $num[2]; 
	         if ($n3 != 0) $t = 'i' . $matuni[$n3]; 
	         $n2 = $num[1]; 
	         $t = ' ' . $matdec[$n2] . $t; 
	      }else{ 
	         $n3 = $num[2]; 
	         if ($n3 != 0) $t = ' y ' . $matuni[$n3]; 
	         $n2 = $num[1]; 
	         $t = ' ' . $matdec[$n2] . $t; 
	      } 
	      $n = $num[0]; 
	      if ($n == 1) { 
	         $t = ' ciento' . $t; 
	      }elseif ($n == 5){ 
	         $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t; 
	      }elseif ($n != 0){ 
	         $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t; 
	      } 
	      if ($sub == 1) { 
	      }elseif (! isset($matsub[$sub])) { 
	         if ($num == 1) { 
	            $t = ' mil'; 
	         }elseif ($num > 1){ 
	            $t .= ' mil'; 
	         } 
	      }elseif ($num == 1) { 
	         $t .= ' ' . $matsub[$sub] . '?n'; 
	      }elseif ($num > 1){ 
	         $t .= ' ' . $matsub[$sub] . 'ones'; 
	      }   
	      if ($num == '000') $mils ++; 
	      elseif ($mils != 0) { 
	         if (isset($matmil[$sub])) $t .= ' ' . $matmil[$sub]; 
	         $mils = 0; 
	      } 
	      $neutro = true; 
	      $tex = $t . $tex; 
	   } 
	   $tex = $neg . substr($tex, 1) . $fin; 
	   return ucfirst($tex); 
	}

function nombre_persona($usuario)
{
	$query= new QUERY();
	
	$sql="select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";
	
	$query->consulta($sql);

	list($int_nombre,$int_apellido)=$query->valores_fila();
	
	return $int_nombre.' '.$int_apellido; 
		
}	

function ingresar_comprobante($fecha,$tc,$mon,$interesado,$interno,$usuario,$tipocomprobante,$tipopago,$tabla,$tabla_id,$cmp_obs='')
{
	$query= new QUERY();

	 $sql="insert into comprobante(cmp_fecha,cmp_estado,cmp_tc,cmp_interesado,cmp_fecha_creacion,cmp_fecha_modificacion,cmp_tco_id,cmp_usu_id,cmp_int_id,cmp_tpa_id,cmp_mon_id,cmp_tabla,cmp_tabla_id,cmp_obs)
		   values ('$fecha','1','$tc','$interesado','".date('Y-m-d')."','".date('Y-m-d')."','$tipocomprobante','$usuario','$interno','$tipopago','$mon','$tabla','$tabla_id','$cmp_obs')";

	  $query->consulta($sql);

	  return mysql_insert_id();
}

function ingresar_detalle($cmp_id,$monto,$cuenta,$centrocosto,$detalle='',$monto_sus=0)
{
	 $query= new QUERY();

	 $sql="insert into comprobante_detalle(cde_monto,cde_cue_id,cde_cco_id,cde_cmp_id,cde_glosa,cde_monto_sus)
		   values ('$monto','$cuenta','$centrocosto','$cmp_id','$detalle','$monto_sus')";

	  $query->consulta($sql);
}	
?>

