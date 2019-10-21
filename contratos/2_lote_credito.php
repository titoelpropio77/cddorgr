<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Documento sin t&iacute;tulo</title>
<style type="text/css">
<!--
p.MsoNormal {
margin:0cm;
margin-bottom:.0001pt;
font-size:10.0pt;
font-family:"Times New Roman","serif";
}
p.MsoBodyText {
margin:0cm;
margin-bottom:.0001pt;
font-size:12.0pt;
font-family:"Times New Roman","serif";
}
table.MsoNormalTable {
font-size:10.0pt;
font-family:"Times New Roman","serif";
}
-->
</style>
</head>
<?php
$arDeps = array('Santa Cruz' => 'SC', 
            'La Paz' => 'LP', 
            'Oruro' => 'OR', 
            'Potosi' => 'PT',
            'Pando' => 'PA',
            'Beni' => 'BN',
            'Chuquisaca' => 'CH',
            'Cochabamba' => 'CB',
            'Tarija' => 'TJ');
			
function datos_copropietario($int_id, $arDeps){
		 	$conec= new ADO();  
	    
		$sql="select int_nombre,int_apellido,int_direccion,int_estado_civil,int_ci,int_ci_exp from interno where int_id=$int_id";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$resp = new stdClass();
		$resp->nombre = "$objeto->int_nombre $objeto->int_apellido";
		$resp->direccion = $objeto->int_direccion;
		$resp->ci = $objeto->int_ci;
		$resp->ci_exp = array_search($objeto->int_ci_exp.'',$arDeps);
		$resp->estado_civil = $objeto->int_estado_civil;		
		
		return $resp;
}			
 function obtener_nombre_copropietario($id)
 {
	 	$conec= new ADO();  
	    
		$sql="select int_nombre,int_apellido from interno where int_id=$id";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->int_nombre.' '.$objeto->int_apellido;
 }
 function obtener_ci_copropietario($id)
 {
	 	$conec= new ADO();  
	    
		$sql="select int_ci from interno where int_id=$id";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->int_ci;
 }
 

 
 function fecha_cobranza($ven_id){
 	$sql = "select right(ind_fecha_programada,2)as campo 
			from interno_deuda 
			where ind_tabla='venta' 
			and ind_tabla_id='$ven_id'
			and ind_estado in ('Pendiente','Pagado')
			and ind_num_correlativo = 1";
	$fecha = FUNCIONES::atributo_bd_sql($sql);
	
	return $fecha;
 }
 
 function obtener_ciexp_copropietario($id)
 {
	 	$conec= new ADO();  
	    
		$sql="select int_ci_exp from interno where int_id=$id";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->int_ci_exp;
 }
 
 function info_plan($ven_id){
 	$sql = "select * from interno_deuda where ind_tabla_id=$ven_id
			and ind_tabla='venta'
			and ind_num_correlativo > 0
			and ind_estado in ('Pendiente','Pagado')
			order by ind_num_correlativo desc limit 0,2";
	$cuotas = FUNCIONES::objetos_bd_sql($sql);
	
	$ultima = $cuotas->get_objeto()->ind_monto;
	$cuotas->siguiente();
	$normal = $cuotas->get_objeto()->ind_monto;
	$datos = new stdClass();
	$datos->nro_cuotas = FUNCIONES::atributo_bd_sql("select count(ind_id)as campo from interno_deuda where ind_tabla_id=$ven_id
			and ind_tabla='venta'
			and ind_num_correlativo > 0
			and ind_estado in ('Pendiente','Pagado')")*1;
		
	if ($ultima > $normal) {
		$datos->mayor = 1;
		$datos->cuota_1 = $normal;
		$datos->cuota_n = $ultima;
//		echo "Es mayor la ultima $ultima $normal $datos->nro_cuotas";
		
	} else {
		$datos->mayor = 0;
		$datos->cuota_1 = $normal;
//		echo "Es menor o igual la ultima $ultima $normal";
	}
	
	return $datos;
	
 }
 
 function obtener_mes($mes)
 {
	 if($mes=='01')
			$mes_literal='Enero';
		if($mes=='02')
			$mes_literal='Febrero';
		if($mes=='03')
			$mes_literal='Marzo';
		if($mes=='04')
			$mes_literal='Abril';
		if($mes=='05')
			$mes_literal='Mayo';
		if($mes=='06')
			$mes_literal='Junio';
		if($mes=='07')
			$mes_literal='Julio';
		if($mes=='08')
			$mes_literal='Agosto';
		if($mes=='09')
			$mes_literal='Septiembre';
		if($mes=='10')
			$mes_literal='Octubre';
		if($mes=='11')
			$mes_literal='Noviembre';
		if($mes=='12')
			$mes_literal='Diciembre';
	
	return $mes_literal;
 }
 
   function objeto_bd_sql($sql)
 {
	 	$conec= new ADO();  
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto;
 }
 ?>
<?php
$conec= new ADO();
info_plan($_GET[id]);
		
		$sql = "select 
				ven.*,inte.*,inte2.int_nombre as co_nombre,inte2.int_apellido as co_apellido,inte2.int_ci as co_ci,inte2.int_ci_exp as co_ci_exp,inte2.int_direccion as co_direccion,inte2.int_estado_civil as co_estado_civil,
				man.man_nro,man.man_matricula,man.man_superficie,man.man_lote_ini,man.man_lote_fin,u.uv_nombre,lot.lot_nro,lot.lot_col_norte,lot.lot_col_sur,lot.lot_col_este,lot.lot_col_oeste,lot.lot_codigo,ven.ven_co_propietario
				from 
				venta ven
				inner join interno inte on(ven.ven_id = '$_GET[id]' and ven.ven_int_id=inte.int_id)
				left join interno inte2 on(ven.ven_id = '$_GET[id]' and ven.ven_co_propietario=inte2.int_id)
				inner join lote lot on(ven.ven_lot_id=lot.lot_id)
				inner join zona zon on(lot.lot_zon_id=zon.zon_id)
				inner join uv u on(lot.lot_uv_id=u.uv_id)
				inner join manzano man on (lot.lot_man_id=man.man_id)
			
				"; 
//		echo $sql;
		//$urb_id=$this->obtener_urbanizacion_venta($_GET['id']);
		//$nombre_urbanizacion=$this->obtener_nombre_urbanizacion($urb_id);
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$objeto=$conec->get_objeto();
		
		//$objeto->int_ci_exp = array_search($objeto->int_ci_exp, $arDeps);
		//$objeto->co_ci_exp = array_search($objeto->co_ci_exp, $arDeps);

		
//		echo "<pre>";
//                print_r($objeto);
//		echo "</pre>";
		
		$propietario = "$objeto->int_nombre $objeto->int_apellido";
		$ci = $objeto->int_ci;
		$ci_expedicion = array_search($objeto->int_ci_exp, $arDeps);
		$direccion = $objeto->int_direccion;
		$estado_civil = $objeto->int_estado_civil;
		$lote = $objeto->lot_nro;
		$precio_lote = $objeto->ven_monto;
		$fecha_cobro = FUNCIONES::atributo_bd_sql("select ind_fecha_programada as campo from interno_deuda 
		where ind_estado in('Pendiente','Pagado') and ind_tabla='venta' 
		and ind_tabla_id='$_GET[id]' and ind_num_correlativo>0");
		$fecha_cobro = explode('-', $fecha_cobro);
		
		$dia_cobro = $fecha_cobro[2];
		
		$dec = $objeto->ven_monto;
		$int = round(($dec - intval($dec))*100);
		if (ceil($int) == 0) {
			$int .= '0';
		}		
		$precio_lote_literal = strtoupper($this->num2letras(intval($objeto->ven_monto)));
		$cuota_inicial = $objeto->ven_res_anticipo;
		
		$dec2 = $objeto->ven_res_anticipo;
		$int_ci = round(($dec2 - intval($dec2))*100);
		if (ceil($int_ci) == 0) {
			$int_ci .= '0';
		}
				
		$cuota_inicial_literal = strtoupper($this->num2letras(intval($objeto->ven_res_anticipo)));
		
		$saldo = round($precio_lote - $cuota_inicial, 2);
		$dec3 = $saldo;
		$int_saldo = round(($dec3 - intval($dec3))*100);
		if (ceil($int_saldo) == 0) {
			$int_saldo .= '0';
		}
		$saldo_literal = strtoupper($this->num2letras(intval($saldo)));		
		
		$plazo = FUNCIONES::atributo_bd_sql("select count(ind_id)as campo from interno_deuda 
		where ind_estado in('Pendiente','Pagado') and ind_tabla='venta' 
		and ind_tabla_id='$_GET[id]' and ind_num_correlativo>0") * 1;
		
		$tasa_interes = round($objeto->ven_val_interes, 2);
		$manzano = $objeto->man_nro;
		
		$this->formulario->dibujar_cabecera();	
		
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
		////
				
		?>		
				<?php echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:window.history.back();"></td></tr></table><br><br>
				';?>
<body>
<div id="contenido_reporte">
<p class="MsoNormal" align="center" style="text-align:center;"><strong><u><span style="letter-spacing:-.1pt; font-family:'Arial','sans-serif'; ">CONTRATO PRIVADO DE VENTA DE UN LOTE  DE TERRENO A PLAZOS CON RESERVA DEL DERECHO DE PROPIEDAD DENTRO DE LA URB.  CIUDAD DE DIOS</span></u></strong></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif';">Conste por el  contenido y tenor del presente contrato privado sobre la venta al cr&eacute;dito  directo con prestaciones sucesivas&nbsp; y  reserva de derecho de propiedad de acuerdo al Art. 585 del C&oacute;digo Civil, que se  suscribe entre partes al tenor de las siguientes clausulas y condiciones:</span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CL&Aacute;USULA PRIMERA: DE LAS PARTES QUE INTERVIENEN.-</span></u></strong><span style="font-family:'Arial','sans-serif'; "> Intervienen en la suscripci&oacute;n del presente contrato, por una parte la se&ntilde;ora </span><strong><span style="font-family:'Arial','sans-serif'; ">Margoth Masan&eacute;s de Campos</span></strong><span style="font-family:'Arial','sans-serif'; ">, con c&eacute;dula de identidad N&deg; 3217944 </span><span style="letter-spacing:-.15pt; font-family:'Arial','sans-serif'; ">expedida  en Santa Cruz</span><span style="font-family:'Arial','sans-serif'; ">,  mayor de edad, domiciliada en esta ciudad y h&aacute;bil por derecho, representada en este  acto por el se&ntilde;or <strong>ABD&Oacute;N GILBERTO ZURITA  VARGAS</strong>, con c&eacute;dula de identidad N&deg; 3127465 expedida en Cochabamba, <strong>quien act&uacute;a en m&eacute;rito al poder de  representaci&oacute;n N&deg; 91/2016 </strong></span><strong><span style="font-family:'Arial','sans-serif'; ">de fecha 29 de enero de 2016, protocolizado por ante la Notar&iacute;a de Fe  P&uacute;blica N&deg; 54 de este Distrito Judicial, a cargo de la Dra. Martha G&oacute;mez S.  Vda. de Colosia</span></strong><strong><span style="font-family:'Arial','sans-serif'; "> de fecha </span></strong><span style="font-family:'Arial','sans-serif'; ">quien en adelante, y para  los fines de este contrato se denominar&aacute; <strong>EL VENDEDOR</strong>.</span></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">Y por otra, el (la)  Se&ntilde;or (a) <strong><?php echo $propietario;?></strong> mayor de edad, y h&aacute;bil por ley,  quien en adelante para los fines de este contrato se denominar&aacute; <strong>el (la)  COMPRADOR (A).</strong></span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CL&Aacute;USULA SEGUNDA: DEL DERECHO PROPIETARIO.-</span></u></strong><u><span style="font-family:'Arial','sans-serif'; "> </span></u><span style="font-family:'Arial','sans-serif'; ">La  Se&ntilde;ora Margoth Masan&eacute;s de Campos, legalmente representada por el Se&ntilde;or Abd&oacute;n  Gilberto Zurita Vargas, declara que es &uacute;nica y legitima propietaria</span><span style="font-family:'Arial','sans-serif'; "> de  un inmueble de mayor extensi&oacute;n, ubicado en departamento de Santa Cruz,  provincia Andr&eacute;s Ib&aacute;&ntilde;ez, en el municipio de Cotoca, U.V. 209, denominado  Manzano N&ordm; <?php echo $manzano;?>, con  una superficie total seg&uacute;n t&iacute;tulo de <?php echo $objeto->man_superficie;?>.Mts2., (Lotes  del <?php echo $objeto->man_lote_ini;?> al <?php echo $objeto->man_lote_fin;?>) &nbsp;con derecho  propietario debidamente inscrito en el Registro de Derechos Reales bajo la  Matr&iacute;cula <strong><?php echo $objeto->man_matricula;?></strong> asiento A1 en fecha  09/07/2013, inmueble que forma parte de la Urbanizaci&oacute;n &ldquo;Ciudad de Dios&rdquo;,  aprobada en merito a la Resoluci&oacute;n Administrativa N&deg; 089/2.012, homologada  mediante la ORDENANZA MUNICIPAL N&deg; 181/2.012, del HONORABLE CONCEJO MUNICIPAL  DE COTOCA.</span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CLAUSULA  TERCERA: DE LA VENTA CON RESERVA DEL DERECHO DE PROPIEDAD Y FORMA DE PAGO.-</span></u></strong><span style="font-family:'Arial','sans-serif'; "> De nuestra libre y </span><span style="font-family:'Arial','sans-serif'; ">espont&aacute;nea  voluntad y por as&iacute; convenir a los intereses de ambas partes, sin que exista  vicio alguno de nulidad o de consentimiento u otro vicio legal que pueda tachar  el presente acto jur&iacute;dico, el VENDEDOR con las facultades que le asiste, otorga  bajo la modalidad de <strong>CONTRATO PRIVADO DE</strong> <strong>VENTA CON RESERVA DE DERECHO PROPIETARIO</strong> previsto en el Art. 585 del C&oacute;digo Civil y 839 del C&oacute;digo de Comercio, cede a  favor del (a) COMPRADOR (A) un lote de terreno dentro de la superficie de mayor  extensi&oacute;n descrita en la cl&aacute;usula anterior y que en un futura se conocer&aacute; como  urbanizaci&oacute;n &ldquo;CIUDAD DE DIOS&rdquo; <strong>lote de  terreno dentro de la U.V. 209, &nbsp;MZA <?php echo $manzano;?>  Lote No <?php echo $lote;?> con una superficie de <?php echo $objeto->ven_superficie;?> Mts2.</strong> venta que se realiza bajo la  modalidad de cr&eacute;dito directo de manera libre, voluntaria, de com&uacute;n y mutuo  acuerdo entre VENDEDOR y COMPRADOR (A) y se conviene y acepta el precio (Art.  611 del C&oacute;digo Civil) de </span><strong><span style="font-family:'Arial','sans-serif'; ">$us <?php echo number_format($precio_lote, 2, '.', ',');?>  (<?php echo $precio_lote_literal;?> <?php echo $int;?>/100 DOLARES AMERICANOS)</span></strong><span style="font-family:'Arial','sans-serif'; ">, monto a ser  pagado necesaria y &uacute;nicamente con dinero en efectivo de la siguiente forma:</span></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">EL VENDEDOR  declara recibir del (a) COMPRADOR (A) a la firma y suscripci&oacute;n del presente la  suma de $us <?php echo number_format($cuota_inicial, 2, '.', ',');?> (<?php echo $cuota_inicial_literal;?> <?php echo $int_ci;?>/100 DOLARES AMERICANOS) como cuota inicial a su  entera satisfacci&oacute;n y conformidad y sin lugar a reclamos posteriores.</span></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">El saldo de </span><strong><span style="font-family:'Arial','sans-serif'; ">$us <?php echo number_format($saldo, 2, '.', ',');?> (<?php echo $saldo_literal . " " . $int_saldo;?>/100  DOLARES AMERICANOS)</span></strong><span style="font-family:'Arial','sans-serif'; "> </span><span style="font-family:'Arial','sans-serif'; ">ser&aacute;n  pagados por el (la) COMPRADOR (A) en el plazo perentorio e improrrogable de  <?php echo $plazo;?> <strong> (<?php echo $this->num2letras($plazo);?>) MESES</strong> calendario, es  decir <?php echo $plazo;?> CUOTAS y sobre el saldo deudor conforme el Art. 637 del C&oacute;digo Civil,  al (a) COMPRADOR (A) le correr&aacute; un inter&eacute;s libre y plenamente acordado entre  partes del <strong><?php echo $this->num2letras($tasa_interes);?> (<?php echo $tasa_interes;?> %) POR CIENTO ANUAL. </strong>Los</span><span style="font-family:'Arial','sans-serif'; "> pagos mensuales se realizaran conforme al Plan de Pagos que como Anexo 1, se  firma y adjunta como parte inseparable e indisoluble del presente contrato, el  mismo que podr&aacute; ser modificado en funci&oacute;n a amortizaciones realizadas por el  (la) COMPRADOR (A) y/o otras circunstancias admitidas por el VENDEDOR.&nbsp; </span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif';">CL&Aacute;USULA CUARTA: </span></u></strong><strong><u><span style="font-family:'Arial','sans-serif'; ">DE  LA </span></u></strong><strong><u><span style="font-family:'Arial','sans-serif'; ">EVICCI&Oacute;N Y SANEAMIENTO</span></u></strong><strong><span style="font-family:'Arial','sans-serif'; font-size:10.0pt; ">.-</span></strong><span style="font-family:'Arial','sans-serif';"> Sobre la  Urbanizaci&oacute;n CIUDAD DE DIOS no pesa ning&uacute;n tipo de gravamen o restricci&oacute;n de  alguna naturaleza, dejando claro que El VENDEDOR act&uacute;a de buena fe y garantiza  la evicci&oacute;n y saneamiento de ley en la mejor forma de derecho en favor del (a)  COMPRADOR (A).</span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CLAUSULA QUINTA: DEL RECONOCIMIENTO DE PROPIEDAD.-</span></u></strong><strong><span style="font-family:'Arial','sans-serif'; "> La se&ntilde;ora Margoth  Masanes de Campos</span></strong><span style="font-family:'Arial','sans-serif'; "> declara y  reconoce que ya no tiene la posesi&oacute;n ni disposici&oacute;n alguna sobre las  superficies de terreno descritos en la cl&aacute;usula segunda, siendo el &uacute;nico  responsable legal y autorizado para la comercializaci&oacute;n y venta de los lotes de  terreno de la futura Urbanizaci&oacute;n &ldquo;CIUDAD DE DIOS&rdquo; el se&ntilde;or </span><strong><span style="font-family:'Arial','sans-serif'; ">ABD&Oacute;N GILBERTO ZURITA VARGAS </span></strong><span style="font-family:'Arial','sans-serif';">para que  este realice todo tipo de acciones y gestiones comerciales, administre las  financieras y realice actividades administrativas para la venta de dichos  terrenos, estando a su cargo la cobranza, ejecuci&oacute;n y reversi&oacute;n de los mismos  sin limitaci&oacute;n alguna, estando conforme al mandato que le fuera conferido y en  cualquier momento que se requiera y para los fines legales pertinentes se podr&aacute;  ampliar las facultades del mismo.&nbsp; </p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CL&Aacute;USULA SEXTA:</span></u></strong><u><span style="font-family:'Arial','sans-serif'; "> <strong>DEL LUGAR DE PAGO</strong></span></u><span style="font-family:'Arial','sans-serif'; ">.<strong>-</strong> De com&uacute;n y mutuo acuerdo, ambas partes convienen que tanto la  primera cuota como pago inicial, como el resto de cuotas </span><span style="font-family:'Arial','sans-serif'; ">mensuales</span><span style="font-family:'Arial','sans-serif'; "> y todo otro  pago, deben ser efectuadas y/o depositadas en dinero en efectivo a nombre y a  favor del VENDEDOR y/o la Urbanizaci&oacute;n &ldquo;CIUDAD DE DIOS&rdquo;, en las cuentas  bancarias proporcionadas por el Vendedor, las cuales el VENDEDOR tiene  expresamente autorizada para este fin y/o en oficinas autorizadas de Ciudad De  Dios bienes Ra&iacute;ces SRL. En consecuencia no ser&aacute; reconocido ning&uacute;n tipo de pago  que no sea certificado por las entidades financieras que se indique y/o la  empresa Ciudad De Dios. De igual manera, al (a) comprador (a) le queda  terminantemente prohibido hacer entrega de dinero en efectivo a cualquier  persona o funcionario a cuenta de alg&uacute;n pago, bajo la &uacute;nica y absoluta  responsabilidad del (a) comprador (a) toda vez que TODOS los pagos se deben  hacer mediante instituci&oacute;n financiera autorizada y/o caja de Ciudad de Dios  Bienes Ra&iacute;ces SRL.<strong><u> </u></strong></span></p>
<!--<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">&nbsp;</span></p>-->
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CL&Aacute;USULA SEPTIMA: DEL ATRASO EN EL PAGO DE UN MES</span></u></strong><span style="font-family:'Arial','sans-serif'; ">.<strong>-</strong> El (la) COMPRADOR (A) libre y  voluntariamente conoce y acepta que en el retraso del pago de una las cuotas  mensuales detalladas en las fechas de vencimiento establecidas en el Anexo 1  del presente contrato, conlleva al cobro del inter&eacute;s por los d&iacute;as atrasados  como lo dispone el Art. 637 del C&oacute;digo Civil.</span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CLAUSULA OCTAVA: DE LA RESOLUCI&Oacute;N DE CONTRATO Y REVERSI&Oacute;N DEL LOTE</span></u></strong><strong><span style="font-family:'Arial','sans-serif'; ">.- </span></strong><span style="font-family:'Arial','sans-serif'; ">Sin perjuicio de lo establecido  en la cl&aacute;usula anterior, el atraso en el pago de dos (2) cuotas </span><span style="font-family:'Arial','sans-serif'; ">mensuales</span><span style="font-family:'Arial','sans-serif'; "> consecutivas  y/o el retraso de sesenta (60) d&iacute;as&nbsp;  conforme al plan de pagos ser&aacute; motivo y/o causal &uacute;nica y suficiente de  resoluci&oacute;n de pleno derecho y de hecho del presente contrato sin la necesidad  de aviso previo, intervenci&oacute;n judicial, procedimiento, formalidad o tr&aacute;mite  alguno como lo disponen los Arts. 569 y 639 del C&oacute;digo Civil y Arts. 805 y 841  del C&oacute;digo de Comercio, al darse la resoluci&oacute;n del presente, se revierte el  lote de terreno descrito en la cl&aacute;usula tercera y se otorga nuevamente la  posesi&oacute;n y la propiedad al vendedor en el estado en que se encuentre,  consolid&aacute;ndose a favor del VENDEDOR el pago inicial y todas las cuotas </span><span style="font-family:'Arial','sans-serif'; ">mensuales</span><span style="font-family:'Arial','sans-serif'; "> que hubiese  cancelado el (la) COMPRADOR (A) en resarcimiento a los da&ntilde;os y perjuicios  ocasionados por el incumplimiento, estando autorizado el VENDEDOR a ocupar,  tomar posesi&oacute;n y disponer del lote revertido, sin perjuicio de las acciones  legales que considere necesarias en caso de resistencia del (a) COMPRADOR (A).</span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CL&Aacute;USULA NOVENA: DE LA FACULTAD PARA LA CESI&Oacute;N DEL CREDITO</span></u></strong><strong><span style="font-family:'Arial','sans-serif'; ">.- </span></strong><span style="font-family:'Arial','sans-serif'; ">El (la) COMPRADOR (A) faculta de  manera expresa e irrevocablemente al VENDEDOR </span><span style="font-family:'Arial','sans-serif'; ">para que gestione en caso de ser  necesario la CESION DEL CREDITO generado a trav&eacute;s del presente, pudiendo en  este caso EL VENDEDOR si lo estima o ve conveniente a sus intereses, el  transferir o ceder a tercero el cr&eacute;dito objeto de &eacute;ste contrato, o el derecho a  cobrar las cuotas pactadas o los dem&aacute;s derechos, para que el comprador del  cr&eacute;dito tenga las m&aacute;s amplias y absolutas facultades de cobro, ejecuci&oacute;n, dar  en prenda, pignorar, negociar la acreencia y cualesquier derecho emanado y  emergente del mismo a su favor, cuya cesi&oacute;n o transferencia no deber&aacute;  contradecir ni modificar las condiciones pactadas en &eacute;ste contrato o las  garant&iacute;as constituidas que se establecieron en su elaboraci&oacute;n. De darse esta  situaci&oacute;n, el COMPRADOR/DEUDOR estar&aacute; obligado de ipso facto, con la sola  notificaci&oacute;n de la cesi&oacute;n de cr&eacute;dito a responder ante el ente adjudicatario de  la deuda</span><span style="font-family:'Arial','sans-serif'; ">, de conformidad, aplicaci&oacute;n y adecuaci&oacute;n a los  art&iacute;culos 324 y 384 del C&oacute;digo Civil, asimismo, el (la) COMPRADOR (A) autoriza  a el VENDEDOR y/o cesionario, para que de manera directa o a trav&eacute;s de  terceros, consulte y/o pida informes confidenciales de la central de  informaci&oacute;n de riesgo de la </span><span style="font-family:'Arial','sans-serif'; ">Autoridad  de Supervisi&oacute;n del Sistema Financiero de Bolivia</span><span style="font-family:'Arial','sans-serif'; "> (ASFI), as&iacute;  como informes emitidos por cualquier entidad legalmente constituida y  reconocida y/o ante cualquier otra instituci&oacute;n p&uacute;blica o privada.</span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CL&Aacute;USULA DECIMA</span></u></strong><u><span style="font-family:'Arial','sans-serif'; ">: </span></u><strong><u><span style="font-family:'Arial','sans-serif'; ">DE LA  PROHIBICION DE DEVOLUCIONES Y TRANSFERENCIAS</span></u></strong><strong><u><span style="font-family:'Arial','sans-serif'; "> A FAVOR DE TERCEROS</span></u></strong><span style="font-family:'Arial','sans-serif'; ">.<strong>-</strong> De com&uacute;n acuerdo, las partes establecen que, una vez pagada la  cuota inicial, se entiende trabada la relaci&oacute;n contractual, por tanto se  considerar&aacute; como incumplimiento del (de la) COMPRADOR (A) la devoluci&oacute;n del  terreno, por lo que no habr&aacute; lugar a devoluci&oacute;n de dinero bajo ning&uacute;n concepto. </span><span style="font-family:'Arial','sans-serif'; ">As&iacute; mismo se  deja claramente establecido que al COMPRADOR le queda terminantemente prohibido  transferir el lote de terreno objeto del presente bajo ninguna modalidad y/o  t&iacute;tulo a favor de terceros, hasta que no haya pagado la &uacute;ltima cuota  establecida en el plan de pagos o el precio total por el lote de terreno. </span><span style="font-family:'Arial','sans-serif'; ">Los  suscribientes acuerdan que en caso de EL (A) COMPRADOR (A) por razones de  fuerza mayor como ser: grave enfermedad o fallecimiento, sus herederos de l&iacute;nea  directa o indirecta hasta un tercer grado de consanguineidad podr&aacute;n subrogarse  la deuda al momento de la causal de fuerza mayor para que el presente contrato  contin&uacute;e y EL (A) COMPRADOR (A) no pierda las cuotas canceladas, previa  presentaci&oacute;n de la declaratoria de herederos para que proceda dicha  subrogaci&oacute;n. <strong><u>&nbsp;</u></strong></span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif';">CL&Aacute;USULA D&Eacute;CIMA PRIMERA: DE LA TRANSFERENCIA  DEFINITIVA</span></u></strong><strong><span style="font-family:'Arial','sans-serif';">.- </span></strong><span style="font-family:'Arial','sans-serif';">Una vez pagada la &uacute;ltima cuota y/o precio total del  lote de terreno objeto del presente contrato, El (LA) COMPRADOR (A)  no podr&aacute; referirse a la existencia del presente contrato y la minuta de  referencia como si fuera una doble venta de un mismo bien por ser distintos  actos jur&iacute;dicos, ya que ambos documentos son complementarios entre s&iacute;. Por acuerdo entre partes se establece que el (a) COMPRADOR (A) correr&aacute;  con los gastos que signifiquen el tramitar y gestionar la titulaci&oacute;n del lote  de terreno objeto del presente, como ser el reconocimiento de firma, plano de  ubicaci&oacute;n, certificado catastral, gastos notariales, registro en Derechos  Reales del lote que adquiere, hasta consolidar su derecho propietario, conforme  a lo establecido en el Articulo 589 del C&oacute;digo Civil.</span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">DECIMA SEGUNDA: </span></u></strong><strong><u><span style="font-family:'Arial','sans-serif'; ">DE  LA DETENTACION TOLERADA Y LA AUTORIZACION PARA CONSTRUIR.- </span></u></strong><span style="font-family:'Arial','sans-serif'; ">Se  deja claramente establecido que a partir de la firma del presente, AL (A)  COMPRADOR (A) se le reconoce la posesi&oacute;n del lote de terreno, es decir mientras  no cancele la totalidad del valor de terreno, ser&aacute; un (a) detentador (a)  Tolerado (a), en esta condici&oacute;n, no podr&aacute; introducir mejoras considerables y/o  edificaci&oacute;n alguna <strong><u>SIN LA  AUTORIZACION ESCRITA DEL VENDEDOR</u></strong>, dicha Autorizaci&oacute;n deber&aacute; realizarse <u>cuando EL (A) COMPRADOR (A) haya&nbsp;  cancelado</u> <u>como m&iacute;nimo un 25% del valor total del lote de terreno  establecido del plan de pago que se tiene como anexo.</u> En caso de que EL (A)  COMPRADOR (A) haya realizado mejoras considerables y/o edificaci&oacute;n alguna  previo cumplimiento del requisito previsto y m&aacute;s adelante cayera en mora y se  procediera a la reversi&oacute;n del terreno y se proceda como lo establece la  cl&aacute;usula novena del presente, EL VENDEDOR NO reconocer&aacute; ning&uacute;n costo por  mejoras o servicios b&aacute;sicos introducidos en el lote de terreno, debiendo el  detentador tolerado o comprador si desea hacerlo, retirar dichas mejoras y  servicios a su costo y riesgo.</span></p>
<!--<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">&nbsp;</span></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">&nbsp;</span></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">&nbsp;</span></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">&nbsp;</span></p>-->
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CL&Aacute;USULA D&Eacute;CIMA TERCERA: DE LAS OBLIGACIONES DEL (DE LA) COMPRADOR (A)</span></u></strong><strong><span style="font-family:'Arial','sans-serif'; ">.- </span></strong><span style="font-family:'Arial','sans-serif'; ">El (la) COMPRADOR (A) al  suscribir el presente contrato, libre y espont&aacute;neamente, en forma expresa e  irrevocable, asume y se obliga:</span></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">- A partir de  la entrega f&iacute;sica del terreno mediante acta suscrita entre partes, el cuidado,  limpieza y mantenimiento del lote ser&aacute; de exclusiva responsabilidad del (de la)  COMPRADOR (A) el cual deber&aacute; asumir los gastos que correspondan al mismo. </span></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">-  En caso de la que el (a) comprador (a) no hiciera la limpieza y mantenimiento  de su terreno adquirido de manera continua, &eacute;ste AUTORIZA para que EL VENDEDOR  realice la limpieza cada SEIS (6) MESES aceptando t&aacute;citamente que en caso de  que EL VENDEDOR haga dicha limpieza y mantenimiento, los gastos que signifiquen  los mismos sean recargados en su plan de pago que se tiene como anexo.</span><span style="font-family:'Arial','sans-serif'; "> </span></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif';">- </span><span style="font-family:'Arial','sans-serif';">Es de conocimiento y aceptaci&oacute;n del (de la) COMPRADOR (A) que el pago de  impuestos anuales del terreno objeto del presente contrato correr&aacute;n por su  cuenta y cargo a partir de la fecha de pago de la cuota inicial, para lo cual  el (la) COMPRADOR (A) manifiesta expresamente su conformidad.</span></p>
<p class="MsoNormal" style="text-align:justify;"><em><span style="font-family:'Arial','sans-serif'; ">Para que a la culminaci&oacute;n del cr&eacute;dito directo objeto  del presente el (a) COMPRADOR (A) al momento de realizar los tr&aacute;mites  respectivos de traslaci&oacute;n de dominio no se encuentre con gestiones de impuestos  de su terreno adeudadas y con multas e intereses, &eacute;ste (a) abonara al VENDEDOR  una suma ANUAL de Bs. 80.- (OCHENTA 00/100 BOLIVIANOS) por concepto de pago de  impuestos anuales referente a su terreno por la gesti&oacute;n correspondiente, para  que culminado el plazo del cr&eacute;dito directo para la compra del lote de terreno  descrito en la cl&aacute;usula tercera del presente, NO tenga deuda tributaria  alguna.&nbsp;&nbsp;&nbsp; </span></em></p>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif';">- Cualquier responsabilidad civil y penal por el  almacenamiento de sustancias prohibidas y/o controladas por la ley 1008,  dep&oacute;sito de mercader&iacute;as de contrabando o de dudosa procedencia ser&aacute; bajo la  responsabilidad legal de la COMPRADORA quedando exento de cualquier  responsabilidad El Propietario/Vendedor. <strong><u>&nbsp;</u></strong></span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CL&Aacute;USULA D&Eacute;CIMA CUARTA</span></u></strong><u><span style="font-family:'Arial','sans-serif'; ">:<strong> DE LA CLAUSULA ACLARATIVA  COMPLEMENTARIA.-</strong></span></u><strong><span style="font-family:'Arial','sans-serif'; "> </span></strong><span style="font-family:'Arial','sans-serif'; ">&nbsp;</span><span style="font-family:'Arial','sans-serif'; ">Se deja expresamente establecido que y  es de conocimiento del (a) comprador (a) la morfolog&iacute;a irregular actual del  terreno adquirido y que pudieran ser desniveles, quebradas, arroyos, cursos  naturales de aguas, accidentes naturales del terreno, as&iacute; como cualquier otro  accidente natural que existiere en el mismo, por lo que no dar&aacute; lugar en lo  posterior a reclamos u observaciones por lo antes mencionado y cualquier  correcci&oacute;n a la morfolog&iacute;a del terreno estar&aacute; a cargo del (a) comprador (a). <strong><u></u></strong></span></p>
<p class="MsoNormal" style="text-align:justify;"><strong><u><span style="font-family:'Arial','sans-serif'; ">CLAUSULA DECIMA QUINTA:</span></u></strong><u><span style="font-family:'Arial','sans-serif'; "> <strong>DE LA</strong> <strong>ACEPTACI&Oacute;N Y CONFORMIDAD</strong></span></u><strong><span style="font-family:'Arial','sans-serif'; ">.-</span></strong><span style="font-family:'Arial','sans-serif'; "> Ambas Partes declaramos nuestra total conformidad y  aceptaci&oacute;n con todas y cada una de las cl&aacute;usulas del presente contrato,</span><span style="font-family:'Arial','sans-serif'; "> haciendo constar  expresamente que este contrato tiene las caracter&iacute;sticas y la fuerza de  documento privado y es ley entre las partes, de acuerdo al Art. 519 del C&oacute;digo  Civil, mientras se realice el reconocimiento de firmas</span><span style="font-family:'Arial','sans-serif'; "> y en  consecuencia lo suscribimos en tres ejemplares de un mismo tenor e id&eacute;ntico  efecto jur&iacute;dico</span></p>
<?php
$datos_fecha = explode('-',date('Y-m-d'));
$dia = $datos_fecha[2];
$mes = obtener_mes($datos_fecha[1]);
$anio = $datos_fecha[0];
?>
<p class="MsoNormal" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; ">Santa Cruz  <?php echo $dia;?> de <?php echo $mes;?> de <?php echo $anio;?></span></p>
<p class="MsoBodyText" style="text-align:justify;"><strong><span style="font-family:'Arial','sans-serif'; font-size:10.0pt; ">&nbsp;</span></strong></p>
<p class="MsoBodyText" style="text-align:justify;"><strong><span style="font-family:'Arial','sans-serif'; font-size:10.0pt; ">&nbsp;</span></strong></p>
<p class="MsoBodyText" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; font-size:10.0pt; ">&nbsp;</span></p>
<p class="MsoBodyText" style="text-align:justify;"><span style="font-family:'Arial','sans-serif'; font-size:10.0pt; ">&nbsp;</span></p>
<p class="MsoNormal"><span style="font-family:'Arial','sans-serif'; ">&nbsp;</span></p>
<table class="MsoNormalTable" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
  <tr>
    <td width="337" valign="top" style="width:252.85pt;padding:0cm 5.4pt 0cm 5.4pt;"><p class="MsoNormal"><strong><span style="font-family:'Arial','sans-serif'; ">ABD&Oacute;N G.    ZURITA VARGAS </span></strong></p>
        <p class="MsoNormal"><strong><span style="font-family:'Arial','sans-serif'; ">&nbsp; APODERADO/VENDEDOR</span></strong></p></td>
    <td width="337" valign="top" style="width:252.85pt;padding:0cm 5.4pt 0cm 5.4pt;"><p class="MsoNormal" align="center" style="text-align:center;"><strong><span style="font-family:'Arial','sans-serif'; "><?php echo $propietario;?></span></strong></p>
        <p class="MsoNormal" align="center" style="text-align:center;"><strong><span style="font-family:'Arial','sans-serif'; ">COMPRADOR (A)</span></strong></p></td>
  </tr>
</table>
</div>
</body>
</html>
