<?php

class ARQUEO extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;
	var $usu;
	var $tipo_cambio;
	function ARQUEO()
	{
		//permisos
		$this->ele_id=109;		
		$this->busqueda();
		$this->tipo_cambio = $this->tc;
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();		
		
		$this->arreglo_campos[1]["nombre"]="ges_fecha_ini";
		$this->arreglo_campos[1]["texto"]="Fecha Inicio";
		$this->arreglo_campos[1]["tipo"]="fecha";
		$this->arreglo_campos[1]["tamanio"]=12;
		
		$this->arreglo_campos[2]["nombre"]="ges_fecha_fin";
		$this->arreglo_campos[2]["texto"]="Fecha Fin";
		$this->arreglo_campos[2]["tipo"]="fecha";
		$this->arreglo_campos[2]["tamanio"]=12;
		
		
		$this->link='gestor.php';
		
		$this->modulo='arqueo';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('TRASPASO');
		$this->usu=new USUARIO;
		
	}
		
	function dibujar_busqueda()
	{	
	    ?>
		<script>		
		function ejecutar_script(id,tarea){
				var txt = 'Esta seguro de anular el Traspaso de Caja?';
				
				$.prompt(txt,{ 
					buttons:{Anular:true, Cancelar:false},
					callback: function(v,m,f){
						
						if(v){
								location.href='gestor.php?mod=arqueo&tarea='+tarea+'&id='+id;
						}
												
					}
				});
			}

		</script>
		<?php
		$this->formulario->dibujar_cabecera();		
		$this->dibujar_listado();
	}
	
		
	function set_opciones()
	{				
		$nun=0;		
		if($this->verificar_permisos('IMPRIMIR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='IMPRIMIR';
			$this->arreglo_opciones[$nun]["imagen"]='images/imprimir.png';
			$this->arreglo_opciones[$nun]["nombre"]='IMPRIMIR';
			$nun++;
		}		
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
			$this->arreglo_opciones[$nun]["script"]="ok";
			$nun++;
		}
	}
	
	function dibujar_listado()
	{
		$sql="SELECT arq_id,arq_tc,arq_mon_id,arq_fecha_ini,arq_fecha_fin,o.cue_descripcion as origen,d.cue_descripcion as destino,arq_monto,arq_monto_sus,arq_observacion
              FROM arqueo a, cuenta o, cuenta d      
              where a.arq_cue_origen = o.cue_id and a.arq_cue_destino = d.cue_id and arq_estado = 1";
		
		$this->set_sql($sql);
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
                <th>Descripción</th>			
	            <th>Fecha Inicio</th>
				<th>Fecha Fin</th>
				<th>Caja Origen</th>
				<th>Caja Destino</th>
				<th>Monto Bs</th>
                <th>Monto $us</th>	
                <th>Tipo Cambio</th>					
				<th>Moneda</th>					
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
						echo $objeto->arq_observacion;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->arq_fecha_ini);
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->arq_fecha_fin);
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->origen;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->destino;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->arq_monto;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->arq_monto_sus;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->arq_tc;
					echo "&nbsp;</td>";
					echo "<td>";
						if($objeto->arq_mon_id=='1') echo 'Bolivianos'; else echo 'Dolares';
					echo "&nbsp;</td>";
					echo "<td>";
						echo $this->get_opciones($objeto->arq_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}
	
	function cargar_datos()
	{
		$conec=new ADO();
		
		$sql="select * from arqueo
				where arq_id = '".$_GET['id']."'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$_POST['arq_fecha_ini']=$objeto->arq_fecha_ini;		
		$_POST['arq_fecha_fin']=$objeto->arq_fecha_fin;
		$_POST['arq_cue_origen']=$objeto->arq_cue_origen;
		$_POST['arq_cue_destino']=$objeto->arq_cue_destino;
		$_POST['arq_monto_bs']=$objeto->arq_monto;
		$_POST['arq_monto_sus']=$objeto->arq_monto_sus;
		$_POST['arq_observacion']=$objeto->arq_observacion;
		$_POST['arq_tc']=$objeto->arq_tc;
		$_POST['arq_mon_id']=$objeto->arq_mon_id;
	}
	
	function imprimir()
	{
		$conec=new ADO();
		
		$sql="select * from arqueo
				where arq_id = '".$_GET['id']."'";
		
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
			  <body onload=window.print();>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
		$extra1.=" <a href=\'javascript:window.print();\'>Imprimir</a> 
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
		$extra2="'</center></body></html>'"; 
		
		$myday = setear_fecha(strtotime($objeto->arq_fecha));
		
		$conversor = new convertir();
		////
		
		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=arqueo&tarea=ACCEDER\';"></td></tr></table>
				';
		
		?>
						
				 

			<br><br><div id="contenido_reporte" style="clear:both;">
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="35%">
					<strong><?php echo _nombre_empresa; ?></strong><BR>
					<strong>Santa Cruz - Bolivia</strong>
					</td>
				    <td width="30%"><p align="center" ><strong><h3><center>TRASPASO DE DINERO</center></h3></strong></p></td>
				    <td width="35%"><div align="right"><img src="imagenes/micro.png"  /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
					<strong>Caja Origen: </strong> <?php  echo $this->nombre_cuenta($objeto->arq_cue_origen); ?> <br>
					<strong>Caja Destino: </strong> <?php echo $this->nombre_cuenta($objeto->arq_cue_destino); ?> <br>
					</td>
				    <td align="right">
					<strong>Fecha: </strong> <?php echo $myday;?> <br>
					<strong>Tipo de Cambio: </strong> <?php echo $objeto->arq_tc;?> <br>
					<strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->arq_usu_id);?> <br><br>
					</td>
				  </tr>
				  
			</table>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Fecha Inicio</th>
					<th>Fecha Fin</th>
					<th>Descripción</th>
					<th>Moneda</th>
					<th>Monto</th>
				</tr>		
				</thead>
				<tbody>
				<tr>
					<td class="">
						<?php echo $conversor->get_fecha_latina($objeto->arq_fecha_ini); ?>
					</td>
					<td class="">
						<?php echo $conversor->get_fecha_latina($objeto->arq_fecha_fin); ?>
					</td>
					<td class="">
						<?php echo $objeto->arq_observacion; ?>
					</td>
					<td class="">
						<?php if($objeto->arq_mon_id=="1") echo "Bolivianos"; else echo "Dolares"; ?>
					</td>
					<td class="">
						<?php echo $objeto->arq_monto; ?>
					</td>
					
				</tr>
			
		
		</tbody></table>
		
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
		<br>
		<table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
		</div><br>
		<?php		
	}
	
	function nombre_cuenta($id)
	{
		$conec= new ADO();
		
		$sql="select cue_descripcion as nombre from cuenta  where cue_id ='$id'";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->nombre;
		
	}
	
	function nombre_persona($usuario)
	{
		$conec= new ADO();
		
		$sql="select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";
		
		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		return $objeto->int_nombre.' '.$objeto->int_apellido; 
			
	}
	
	function datos()
	{
		$conversor = new convertir();
		
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;			
			$valores[$num]["etiqueta"]="Fecha Inicio";
			$valores[$num]["valor"]=$conversor->get_fecha_mysql($_POST['arq_fecha_ini']);
			$valores[$num]["tipo"]="fecha";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Fecha Fin";
			$valores[$num]["valor"]=$conversor->get_fecha_mysql($_POST['arq_fecha_fin']);
			$valores[$num]["tipo"]="fecha";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Caja Origen";
			$valores[$num]["valor"]=$_POST['arq_cue_origen'];
			$valores[$num]["tipo"]="numero";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Caja Destino";
			$valores[$num]["valor"]=$_POST['arq_cue_destino'];
			$valores[$num]["tipo"]="numero";
			$valores[$num]["requerido"]=true;
			$num++;
            $valores[$num]["etiqueta"]="monto";
			$valores[$num]["valor"]=$_POST['arq_monto'];
			$valores[$num]["tipo"]="real";
			$valores[$num]["requerido"]=false;
			$num++;
			$valores[$num]["etiqueta"]="moneda";
			$valores[$num]["valor"]=$_POST['arq_mon_id'];
			$valores[$num]["tipo"]="numero";
			$valores[$num]["requerido"]=false;
			$num++;
			
			$val=NEW VALIDADOR;
			
			$this->mensaje="";
			
			if($val->validar($valores))
			{
				return true;
			}
				
			else
			{
				$this->mensaje=$val->mensaje;
				return false;
			}
		}
			return false;
	}
	
	function formulario_tcp($tipo)
	{
				switch ($tipo)
				{
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
				
				if(!($ver))
				{
					$url.="&tarea=".$_GET['tarea'];
				}
				
				if($cargar)
				{
					$url.='&id='.$_GET['id'];
				}

		
		    $this->formulario->dibujar_tarea("ARQUEO DE CAJA");
		
			if($this->mensaje<>"")
			{
				$this->formulario->mensaje('Error',$this->mensaje);
			}
			?>
        <script>
			jQuery(function($){
			   $("#arq_fecha_ini").mask("99/99/9999"); 
			   $("#arq_fecha_fin").mask("99/99/9999");  
			});
		</script>
        <!--MaskedInput-->
        <script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
        <!--MaskedInput-->
        <!--FancyBox-->
        <link rel="stylesheet" type="text/css" href="jquery.fancybox/jquery.fancybox.css" media="screen" />
		<script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
		<script type="text/javascript" src="jquery.fancybox/jquery.fancybox-1.2.1.pack.js"></script>
        <script type="text/javascript" src="jquery.fancybox/jquery.easing.1.3.js"></script>
        <!--FancyBox-->
        <table align=right border=0><tr><td><a style="float:left; margin:0 0 0 7px;" class="group" href="sueltos/llamada.php?accion=agregar_divisa"><img border="0" src="images/compra_venta_divisa.png"></a></td></tr></table>
        
		<div id="Contenedor_NuevaSentencia">
        	
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">						
							<!--Inicio
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Fecha Inicio</div>
								 <div id="CajaInput">
									<input readonly="readonly" class="caja_texto" name="arq_fecha_ini" id="arq_fecha_ini" size="12" value="<?php echo $_POST['arq_fecha_ini'];?>" type="text">
										<input name="but_fecha_pagos" id="but_fecha_pagos" class="boton_fecha" value="..." type="button">
										<script type="text/javascript">
														Calendar.setup({inputField     : "arq_fecha_ini"
																		,ifFormat     :     "%Y-%m-%d",
																		button     :    "but_fecha_pagos"
																		});
										</script>
										Tipo de Cambio
										<input readonly="readonly" class="caja_texto" name="arq_tc" id="arq_tc" size="12" value="<?php echo $this->tipo_cambio;?>" type="text">									 
								</div>								
							</div>
							Fin-->
							<!--Inicio
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Fecha Fin</div>
								 <div id="CajaInput">
									<input readonly="readonly" class="caja_texto" name="arq_fecha_fin" id="arq_fecha_fin" size="12" value="<?php echo $_POST['arq_fecha_fin'];?>" type="text">
										<input name="but_fecha_pago" id="but_fecha_pago" class="boton_fecha" value="..." type="button">
										<script type="text/javascript">
														Calendar.setup({inputField     : "arq_fecha_fin"
																		,ifFormat     :     "%Y-%m-%d",
																		button     :    "but_fecha_pago"
																		});
										</script>
								</div>		
							</div>
							Fin-->
                            <?php $conversor = new convertir(); ?>
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Fecha Inicio</div>
								 <div id="CajaInput">
									<input class="caja_texto" name="arq_fecha_ini" id="arq_fecha_ini" size="12" value="<?php if ($_POST['arq_fecha_ini']!=''){echo $conversor->get_fecha_latina($_POST['arq_fecha_ini']);} else { echo date("d/m/Y"); }?>" type="text">
								<span class="flechas1">(DD/MM/AAAA)</span>
                                
								</div>&nbsp;&nbsp;&nbsp;&nbsp;	
                                Tipo de Cambio
										<input readonly="readonly" class="caja_texto" name="arq_tc" id="arq_tc" size="12" value="<?php echo $this->tipo_cambio;?>" type="text">	
							</div>
							<!--Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Fecha Fin</div>
								 <div id="CajaInput">
									<input class="caja_texto" name="arq_fecha_fin" id="arq_fecha_fin" size="12" value="<?php if ($_POST['arq_fecha_fin']!=''){echo $conversor->get_fecha_latina($_POST['arq_fecha_fin']);} else { echo date("d/m/Y"); }?>" type="text">
								<span class="flechas1">(DD/MM/AAAA)</span>
								</div>		
							</div>
							<!--Fin-->
                            
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Caja Origen</div>
							     <div id="CajaInput">
							   <select name="arq_cue_origen" class="caja_texto">
							   <option value="">Seleccione</option>
							   <?php 		
								$fun=NEW FUNCIONES;		
								$fun->combo("select cue_id as id,cue_descripcion as nombre from cuenta  where cue_padre_id = 4 or cue_padre_id = 69 order by cue_id asc",$_POST['arq_cue_origen']);		
								?>
							   </select>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">* </span>Caja Destino</div>
							     <div id="CajaInput">
							   <select name="arq_cue_destino" class="caja_texto">
							   <option value="">Seleccione</option>
							   <?php 		
								$fun=NEW FUNCIONES;		
								$fun->combo("select cue_id as id,cue_descripcion as nombre from cuenta  where cue_padre_id = 4 or cue_padre_id = 69 order by cue_id asc",$_POST['arq_cue_destino']);		
								?>
							   </select>
							   </div>
							</div>
							<!--Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto Bs</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="arq_monto_bs" id="arq_monto_bs" size="20" maxlength="250" value="<?php echo $_POST['arq_monto_bs'];?>">
							   </div>
							</div>
							<!--Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Monto $us</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="arq_monto_sus" id="arq_monto_sus" size="20" maxlength="250" value="<?php echo $_POST['arq_monto_sus'];?>">
							   </div>
							</div>
							<!--Fin-->
							
							<!--Inicio
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Moneda</div>
							   <div id="CajaInput">
							   <select name="arq_mon_id" class="caja_texto">
							   <option value="">Seleccione</option>
							   <?php 		
								$fun=NEW FUNCIONES;	
                                 
								$fun->combo("select mon_id as id,mon_descripcion as nombre from con_moneda order by mon_id asc",$_POST['arq_mon_id']);		
								?>
							   </select>
							   </div>
							</div>
							Fin-->
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Descripción</div>
							   <div id="CajaInput">
							   <input type="text" class="caja_texto" name="arq_observacion" id="arq_observacion" size="60" maxlength="250" value="<?php echo $_POST['arq_observacion'];?>">
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
									<input type="submit" class="boton" name="" value="Guardar">
									<input type="reset" class="boton" name="" value="Cancelar">
									<input type="button" class="boton" name="" value="Volver" onclick="javascript:location.href='<?php echo $red;?>';">
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
	}
	
	function insertar_tcp()
	{
	    $conversor = new convertir();
		
		include_once("clases/registrar_comprobantes.class.php");
		 
		$comp = new COMPROBANTES();	
	    
		$conec= new ADO();
		
		//$_POST['arq_mon_id'] == 1;
		
		//if ($_POST['arq_mon_id'] == 1)
		//{
			$sql="insert into arqueo(arq_fecha,arq_fecha_ini,arq_fecha_fin,arq_fecha_modif,arq_usu_id,arq_cue_origen,arq_cue_destino,arq_monto,arq_estado,arq_observacion,arq_tc,arq_mon_id,arq_monto_sus)
				 values ('".date('Y-m-d')."','".$conversor->get_fecha_mysql($_POST['arq_fecha_ini'])."','".$conversor->get_fecha_mysql($_POST['arq_fecha_fin'])."','".date('Y-m-d')."','".$this->usu->get_id()."','".$_POST['arq_cue_origen']."','".$_POST['arq_cue_destino']."','".$_POST['arq_monto_bs']."','1','".$_POST['arq_observacion']."','".$_POST['arq_tc']."','".$_POST['arq_mon_id']."','".$_POST['arq_monto_sus']."')";
		/*
		}
		else
		{
			$sql="insert into arqueo(arq_fecha,arq_fecha_ini,arq_fecha_fin,arq_fecha_modif,arq_usu_id,arq_cue_origen,arq_cue_destino,arq_monto,arq_estado,arq_observacion,arq_tc,arq_mon_id,arq_monto_sus)
				 values ('".date('Y-m-d')."','".$conversor->get_fecha_mysql($_POST['arq_fecha_ini'])."','".$conversor->get_fecha_mysql($_POST['arq_fecha_fin'])."','".date('Y-m-d')."','".$this->usu->get_id()."','".$_POST['arq_cue_origen']."','".$_POST['arq_cue_destino']."',0,'1','".$_POST['arq_observacion']."','".$_POST['arq_tc']."','".$_POST['arq_mon_id']."','".$_POST['arq_monto']."')";
		}
		*/
		
		/*		
		$sql="insert into arqueo(arq_fecha,arq_fecha_ini,arq_fecha_fin,arq_fecha_modif,arq_usu_id,arq_cue_origen,arq_cue_destino,arq_monto,arq_estado,arq_observacion,arq_tc,arq_mon_id)
		     values ('".date('Y-m-d')."','".$conversor->get_fecha_mysql($_POST['arq_fecha_ini'])."','".$conversor->get_fecha_mysql($_POST['arq_fecha_fin'])."','".date('Y-m-d')."','".$this->usu->get_id()."','".$_POST['arq_cue_origen']."','".$_POST['arq_cue_destino']."','".$_POST['arq_monto']."','1','".$_POST['arq_observacion']."','".$_POST['arq_tc']."','".$_POST['arq_mon_id']."')";
		*/
		
		$conec->ejecutar($sql,false);
		
		$arq_id = mysql_insert_id();
	
        $cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$_POST['arq_tc'],$_POST['arq_mon_id'],$this->usu->get_id(),'',$this->usu->get_id(),3,1,'arqueo',$arq_id);			   
		
		$montobs = $_POST['arq_monto_bs'];
		
		$montosus = $_POST['arq_monto_sus'];
		/*
		if ($_POST['arq_mon_id'] == 2)
		{
		   $montobs = $_POST['arq_monto'] * $_POST['arq_tc'];
		}
		*/
		
		//if ($_POST['arq_mon_id'] == 1)
		//{
			$comp->ingresar_detalle($cmp_id,($montobs*-1),$_POST['arq_cue_origen'],0,'',($montosus*-1));
			$comp->ingresar_detalle($cmp_id,$montobs,$_POST['arq_cue_destino'],0,'',$montosus);
		//}
		//else
		//{
			//$comp->ingresar_detalle($cmp_id,0,$_POST['arq_cue_origen'],0,'',($montobs*-1));
			//$comp->ingresar_detalle($cmp_id,0,$_POST['arq_cue_destino'],0,'',$montobs);
		//}
		
		$this->nota_comprobante($arq_id);
		
		//$comp->ingresar_detalle($cmp_id,($montobs*-1),$_POST['arq_cue_origen'],0);
		//$comp->ingresar_detalle($cmp_id,$montobs,$_POST['arq_cue_destino'],0);
		
		//$mensaje='Traspaso Agregada Correctamente!!!';

		//$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function modificar_tcp()
	{
		$conec= new ADO();
		
		$sql="update arqueo set 
							ges_descripcion='".$_POST['ges_descripcion']."',
							ges_fecha_ini='".$_POST['ges_fecha_ini']."',
							ges_fecha_fin='".$_POST['ges_fecha_fin']."'
							where ges_id = '".$_GET['id']."'";

		$conec->ejecutar($sql);

		$mensaje='Gestion Modificada Correctamente!!!';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		
	}
	
	function formulario_confirmar_eliminacion()
	{
		
		$mensaje='Esta seguro de eliminar la Gestión?';
		
		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'ges_id');
	}
	
	function anular()
	{
	    include_once("clases/registrar_comprobantes.class.php");
		 
		$comp = new COMPROBANTES();	
	    $conec = new ADO();
		$sql = "update arqueo SET arq_estado = 0, arq_fecha_modif = '".date('Y-m-d')."' Where arq_id =".$_GET['id'];
	    $conec->ejecutar($sql);
	
	    $comp->anular_comprobante_tabla('arqueo',$_GET['id']);
		
		$mensaje='Traspaso de Caja Anulado Correctamente!!!';
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	
	function nota_comprobante($cmp_id)
	{

		$conec= new ADO();
		$conversor = new convertir();

		$sql = "select arq_id ,arq_fecha,arq_fecha_modif,arq_fecha_ini ,arq_fecha_fin ,arq_usu_id,arq_cue_origen,arq_cue_destino,arq_monto,arq_estado ,arq_observacion,arq_tc,arq_mon_id,arq_monto_sus
			from arqueo where arq_id = '".$cmp_id."' ";

		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();

		$objeto=$conec->get_objeto();

		$tc=$objeto->arq_tc;

		//$cmp_mon=$objeto->arq_mon_id;
		
		//if($cmp_mon=='1')
			$monto_bs=$objeto->arq_monto;
		//if($cmp_mon=='2')
			$monto_sus=$objeto->arq_monto_sus;

		$pagina="'contenido_reporte'";

		$page="'about:blank'";

		$extpage="'reportes'";

		$features="'left=100,width=800,height=500,top=0,scrollbars=yes'";

		$extra1="'<html><head><title>Vista Previa</title><head>
				<link href=css/estilos.css rel=stylesheet type=text/css />
			  </head>
			  <body onload=window.print();>
			  <div id=imprimir>
			  <div id=status>
			  <p>";
		$extra1.=" <a href=\'javascript:window.print();\'>Imprimir</a>
				  <a href=javascript:self.close();>Cerrar</a></td>
				  </p>
				  </div>
				  </div>
				  <center>'";
		$extra2="'</center></body></html>'";

		$myday = setear_fecha(strtotime($objeto->arq_fecha));
		////

		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=comprobante&tarea=AGREGAR\';"></td></tr></table>
				';
		if($this->verificar_permisos('ACCEDER'))
		{
		?>
		<table align=right border=0><tr><td><a href="gestor.php?mod=comprobante&tarea=ACCEDER" title="LISTADO DE COMPROBANTES"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
		<?php
		}
		?>

			<br><br><div id="contenido_reporte" style="clear:both;">
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
				<tr>
				    <td width="40%">
					<strong><?php echo _nombre_empresa; ?></strong><br/>
					<strong>Santa Cruz - Bolivia</strong>
				    <td><p align="center" ><strong><h3>COMPROBANTE DE TRASPASO</h3><br/>
				    		de <? echo $this->get_nombre_caja($objeto->arq_cue_origen); ?> a <? echo $this->get_nombre_caja($objeto->arq_cue_destino); ?> </strong></p></td>
				    <td><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
					<strong>Moneda: </strong> <?php if($cmp_mon=="1") echo "Bolivianos"; else echo "Dolares";?> <br/>
					<strong>Fecha Inicio: </strong><?php echo $conversor->get_fecha_latina($objeto->arq_fecha_ini); ?><br/>

					<strong>Fecha Fin: </strong><?php echo $conversor->get_fecha_latina($objeto->arq_fecha_fin); ?>
					</td>
				    <td align="right">
					<strong>Fecha: </strong> <?php echo $myday;?> <br/>
					<strong>Tipo de Cambio: </strong> <?php echo $tc;?> <br/>
					<strong>Usuario: </strong> <?php echo $this->nombre_persona($objeto->arq_usu_id);?> <br>
					<strong>Nro. Comprobante: </strong> <?php echo $objeto->arq_id;?> <br>
					</td>
				  </tr>

			</table>
			<table   width="70%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>Descripción</th>
					<th class="tOpciones" >Bs</th>
					<th class="tOpciones" >$us</th>
				</tr>
				</thead>
				<tbody>
		<?php


			echo '<tr>';

					if($cmp_mon == '1'){
						$moneda='Bs ';
						$montobs = $monto;
						$montous = round(($monto/$tc),2);
					}else{
						$moneda='$us ';
						$montous = $monto;
						$montobs = round(($montous * $tc),2) ;
					}
					echo "<td>";
						echo $objeto->arq_observacion;
					echo "</td>";

					echo "<td>";
						echo abs($monto_bs);
					echo "</td>";

					echo "<td>";
						echo abs($monto_sus);
					echo "</td>";

				echo "</tr>";
		?>
		<tr>
					<td class="">&nbsp;

					</td>
					<td class="">
						<b><?php echo round(($monto_bs),2);?></b>
					</td>
					<td class="">
						<b><?php echo round(($monto_sus),2);?></b>
					</td>
				</tr>


		</tbody></table>

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
		<br>
		<table align="right" border="0" style="font-size:12px;"><tr><td><b>Impreso: </b><?php echo setear_fecha(strtotime(date('Y-m-d'))).' '.date('H:i');?></td></tr></table>
		</div><br>
		<?php


	}
	
	function get_nombre_caja($caja){
        $conec=new ADO();
        $sql="select cue_descripcion as nombre from cuenta
                where cue_id = '".$caja."'";

        $conec->ejecutar($sql);

        $objeto=$conec->get_objeto();
        return  $objeto->nombre;
	}
}
?>