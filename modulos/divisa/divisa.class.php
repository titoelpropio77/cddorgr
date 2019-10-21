<?php

class DIVISA extends BUSQUEDA
{
	var $formulario;
	var $mensaje;

	function DIVISA()
	{
		//permisos
		$this->ele_id=153;

		$this->busqueda();

		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos

		$this->num_registros=14;

		$this->coneccion= new ADO();

		/*
		$this->arreglo_campos[0]["nombre"]="gru_descripcion";
		$this->arreglo_campos[0]["texto"]="Grupo";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=25;
		*/

		$this->arreglo_campos[0]["nombre"]="int_nombre";
		$this->arreglo_campos[0]["texto"]="Nombre";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=25;

		$this->arreglo_campos[1]["nombre"]="int_apellido";
		$this->arreglo_campos[1]["texto"]="Apellido";
		$this->arreglo_campos[1]["tipo"]="cadena";
		$this->arreglo_campos[1]["tamanio"]=25;
		
		$this->arreglo_campos[2]["nombre"]="cvd_tipo";
		$this->arreglo_campos[2]["texto"]="Tipo";
		$this->arreglo_campos[2]["tipo"]="comboarray";
		$this->arreglo_campos[2]["valores"]="compra,venta:Compra,Venta";

		/*
		$this->arreglo_campos[3]["nombre"]="usu_id";
		$this->arreglo_campos[3]["texto"]="Usuario";
		$this->arreglo_campos[3]["tipo"]="cadena";
		$this->arreglo_campos[3]["tamanio"]=25;
		*/

		$this->link='gestor.php';

		$this->modulo='divisa';

		$this->formulario = new FORMULARIO();

		$this->formulario->set_titulo('COMRA / VENTA DE DIVISAS');
		
		$this->usu=new USUARIO;
	}

	function dibujar_busqueda()
	{
		?>
        
		<script>
		function ejecutar_script(id,tarea){
			if (tarea == 'RETENER') {
				var txt = 'Esta seguro de retener este credito prendario?';
			}else{
				if(tarea=='DESEMBOLSO'){
					var txt = 'Esta seguro de realizar el Desembolso?';
				}else{
					var txt = 'Esta seguro de anular este credito prendario?';
				}
			}
				$.prompt(txt,{
					buttons:{Aceptar:true, Cancelar:false},
					callback: function(v,m,f){

						if(v){
							location.href='gestor.php?mod=divisa&tarea='+tarea+'&id='+id;

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

		if($this->verificar_permisos('VER'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='VER';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_search.png';
			$this->arreglo_opciones[$nun]["nombre"]='VER';
			$nun++;
		}

		if($this->verificar_permisos('MODIFICAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='MODIFICAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_edit.png';
			$this->arreglo_opciones[$nun]["nombre"]='MODIFICAR';
			$nun++;
		}

		if($this->verificar_permisos('ELIMINAR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='ELIMINAR';
			$this->arreglo_opciones[$nun]["imagen"]='images/b_drop.png';
			$this->arreglo_opciones[$nun]["nombre"]='ELIMINAR';
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
		
		if($this->verificar_permisos('IMPRIMIR'))
		{
			$this->arreglo_opciones[$nun]["tarea"]='IMPRIMIR';
			$this->arreglo_opciones[$nun]["imagen"]='images/printer.png';
			$this->arreglo_opciones[$nun]["nombre"]='IMPRIMIR';
			$nun++;
		}

	}

	function dibujar_listado()
	{
		$sql="SELECT * FROM compra_venta_divisa";
		
		$this->set_sql($sql,' order by cvd_id desc');
		
		$this->set_opciones();
		
		$this->dibujar();
	}

	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Nro</th>
				<th>Persona</th>
				<th>Fecha</th>
				<th>Tipo</th>
                <th>Monto</th>
                <th>Tipo de Cambio</th>
                <th>Particular</th>
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
						echo $objeto->cvd_id;
					echo "</td>";
					echo "<td>";
						echo $this->nombre_persona($objeto->cvd_int_id);
					echo "</td>";
					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->cvd_fecha);
					echo "</td>";
					echo "<td>";
						
						if($objeto->cvd_tipo=='compra')
							echo 'Compra';
						if($objeto->cvd_tipo=='venta')
							echo 'Venta';
					
					echo "</td>";
					echo "<td>";
						echo $objeto->cvd_monto;
					echo "</td>";
					echo "<td>";
						echo $objeto->cvd_tipo_cambio;
					echo "</td>";
					echo "<td>";
						echo $objeto->cvd_particular;
					echo "</td>";
					echo "<td>";
						echo $objeto->cvd_estado;
					echo "</td>";
					echo "<td>";
						echo $this->get_opciones($objeto->cvd_id);
					echo "</td>";
				echo "</tr>";

				$this->coneccion->siguiente();
			}
	}

	function cargar_datos()
	{
		$conec=new ADO();

		$sql="select * from compra_venta_divisa
				where cvd_id = '".$_GET['id']."'";

		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();

		$_POST['cvd_id']=$objeto->cvd_id;
		$_POST['cvd_fecha']=$objeto->cvd_fecha;
		$_POST['cvd_hora']=$objeto->cvd_hora;
		$_POST['cvd_tipo']=$objeto->cvd_tipo;
		$_POST['cvd_monto']=$objeto->cvd_monto;
		$_POST['cvd_tipo_cambio']=$objeto->cvd_tipo_cambio;
		$_POST['cvd_int_id']=$objeto->cvd_int_id;
		$_POST['cvd_particular']=$objeto->cvd_particular;

		$fun=NEW FUNCIONES;

		$_POST['cvd_nombre_persona']=$fun->nombre($objeto->cvd_int_id);
	}

	function datos()
	{
		if($_POST)
		{
			//texto,  numero,  real,  fecha,  mail.
			$num=0;
			$valores[$num]["etiqueta"]="Persona";
			$valores[$num]["valor"]=$_POST['cvd_int_id'];
			$valores[$num]["tipo"]="numero";
			$valores[$num]["requerido"]=false;
			$num++;
			$valores[$num]["etiqueta"]="Tipo";
			$valores[$num]["valor"]=$_POST['cvd_tipo'];
			$valores[$num]["tipo"]="texto";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Monto";
			$valores[$num]["valor"]=$_POST['cvd_monto'];
			$valores[$num]["tipo"]="real";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Tipo de Cambio";
			$valores[$num]["valor"]=$_POST['cvd_tipo_cambio'];
			$valores[$num]["tipo"]="real";
			$valores[$num]["requerido"]=true;

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
		$page="'gestor.php?mod=usuario&tarea=AGREGAR&acc=Emergente'";
		$extpage="'persona'";
		$features="'left=325,width=600,top=200,height=420'";

		$this->formulario->dibujar_tarea('USUARIO');

		if($this->mensaje<>"")
		{
			$this->formulario->dibujar_mensaje($this->mensaje);
		}
			?>
        <!--AutoSuggest-->
		<script type="text/javascript" src="js/bsn.AutoSuggest_c_2.0.js"></script>
		<link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />
		<!--AutoSuggest-->
        <script>
        function obtener_tipo_cambio(tipo)
		{	
			var	valores="tarea=tipo_cambio_cv&tipo="+tipo;		

			ejecutar_ajax('ajax.php','tipo_cambio',valores,'POST');									
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
			<form id="frm_usuario" name="frm_usuario" action="<?php echo $url;?>" method="POST" enctype="multipart/form-data">
				<div id="FormSent">

					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Persona</div>
							   <div id="CajaInput">
								    <input name="cvd_int_id" id="cvd_int_id" readonly="readonly" type="hidden" class="caja_texto" value="<?php echo $_POST['cvd_int_id']?>" size="2">
									<input name="cvd_nombre_persona" <?php if($_GET['tarea']!='AGREGAR') echo "readonly";  ?> id="cvd_nombre_persona" type="text" class="caja_texto" value="<?php echo $_POST['cvd_nombre_persona']?>" size="40">
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
		<script type="text/javascript" >
        var options2 = {
					script:"sueltos/persona.php?json=true&",
					varname:"input",
					minchars:3,
					timeout:10000,
					noresults:"No se encontro ninguna persona",
					json:true,
					callback: function (obj) { document.getElementById('cvd_int_id').value = obj.id; }
				};
		var as_json2 = new _bsn.AutoSuggest('cvd_nombre_persona', options2);
		</script>
		<?php
	}

	function emergente()
	{
		$this->formulario->dibujar_cabecera();

		$valor=trim($_POST['valor']);
	?>

			<script>
				function poner(id,valor)
				{
					opener.document.frm_usuario.usu_per_id.value=id;
					opener.document.frm_usuario.usu_nombre_persona.value=valor;
					window.close();
				}
			</script>
			<br><center><form name="form" id="form" method="POST" action="gestor.php?mod=usuario&tarea=AGREGAR&acc=Emergente">
				<table align="center">
					<tr>
						<td class="txt_contenido" colspan="2" align="center">
							<input name="valor" type="text" class="caja_texto" size="30" value="<?php echo $valor;?>">
							<input name="Submit" type="submit" class="boton" value="Buscar">
						</td>
					</tr>
				</table>
			</form><center>
			<?php

			$conec= new ADO();

			if($valor<>"")
			{
				$sql="select int_id,int_nombre,int_apellido from interno where int_nombre like '%$valor%' or int_apellido like '%$valor%'";
			}
			else
			{
				$sql="select int_id,int_nombre,int_apellido from interno";
			}

			$conec->ejecutar($sql);

			$num=$conec->get_num_registros();

			echo '<table class="tablaLista" cellpadding="0" cellspacing="0">
					<thead>
					<tr>
						<th>
							Nombre
						</th>
						<th>
							Apellido
						</th>
						<th width="80" class="tOpciones">
							Seleccionar
						</th>
				</tr>
				</thead>
				<tbody>
			';

			for($i=0;$i<$num;$i++)
			{
				$objeto=$conec->get_objeto();

				echo '<tr>
						 <td>'.$objeto->int_nombre.'</td>
						 <td>'.$objeto->int_apellido.'</td>
						 <td><a href="javascript:poner('."'".$objeto->int_id."'".','."'".$objeto->int_nombre.' '.$objeto->int_apellido."'".');"><center><img src="images/select.png" border="0" width="20px" height="20px"></center></a></td>
					   </tr>
				';

				$conec->siguiente();
			}

			?>
			</tbody></table>
			<?php
	}

	function insertar_tcp()
	{
		$conec= new ADO();
		
		$verificar=NEW VERIFICAR;

		$sql="select cja_cue_id from cajero where cja_usu_id = '".$this->usu->get_id()."' and cja_estado='1'";

		$conec->ejecutar($sql);

		$nume=$conec->get_num_registros();
		
		if($nume > 0)
		{
			$obj = $conec->get_objeto();
			
			$caja=$obj->cja_cue_id;
			/*
		$parametros[0]=array('usu_id');
		$parametros[1]=array($_POST['usu_id']);
		$parametros[2]=array('ad_usuario');

		if($verificar->validar($parametros))
		{
			*/
			
			if($_POST['cvd_int_id']<>"")
			{
				$par="";
			}
			else
			{
				if(trim($_POST['cvd_particular'])<>"")
				{
					$par=$_POST['cvd_particular'];
				}
				else
				{
					$par="Persona Particular";
				}
			}
			
			
			

			$sql="insert into compra_venta_divisa (cvd_fecha,cvd_hora,cvd_tipo,cvd_monto,cvd_tipo_cambio,cvd_int_id,cvd_particular,cvd_usu_id,cvd_estado) values ('".date('Y-m-d')."','".date('H:i:s')."','".$_POST['cvd_tipo']."','".$_POST['cvd_monto']."','".$_POST['cvd_tipo_cambio']."','".$_POST['cvd_int_id']."','".$par."','".$this->usu->get_id()."','Habilitado')";

			$conec->ejecutar($sql,false);
			
			$cvd_id = mysql_insert_id();
			
			
			if($_POST['cvd_tipo']=='compra')
			{
				$sql="select par_cuenta_ingreso_compra,par_cuenta_egreso_compra,par_cc_compra_divisa from ad_parametro";

				$conec->ejecutar($sql);
				
				 $objeto=$conec->get_objeto();
				 
				 $cue_ing=$objeto->par_cuenta_ingreso_compra;
				 $cue_egre=$objeto->par_cuenta_egreso_compra;
				 $cc=$objeto->par_cc_compra_divisa;
				 $concepto_ingreso='Ingreso por Compra de Divisas';
				 $concepto_egreso='Egreso por Compra de Divisas';
			}
			else
			{
				if($_POST['cvd_tipo']=='venta')
				{
					$sql="select par_cuenta_ingreso_venta,par_cuenta_egreso_venta,par_cc_venta_divisa from ad_parametro";

				 	$conec->ejecutar($sql);
				
				 	$objeto=$conec->get_objeto();
				 
				 	$cue_ing=$objeto->par_cuenta_ingreso_venta;
				 	$cue_egre=$objeto->par_cuenta_egreso_venta;
				 	$cc=$objeto->par_cc_venta_divisa;
					$concepto_ingreso='Ingreso por Venta de Divisas';
				 	$concepto_egreso='Egreso por Venta de Divisas';
				}
			}
			
			include_once("clases/registrar_comprobantes.class.php");

	   		$comp = new COMPROBANTES();
			
			
			
			$monto = $_POST['cvd_monto'];
			
			$tipo_cambio=$_POST['cvd_tipo_cambio'];
			
			if($_POST['cvd_tipo']=='venta')
			{
			
				$cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$_POST['cvd_tc'],2,'',$_POST['cvd_int_id'],$this->usu->get_id(),1,1,'compra_venta_divisa',$cvd_id);
			
				$comp->ingresar_detalle($cmp_id,($monto*$tipo_cambio),$caja,0);
				$comp->ingresar_detalle($cmp_id,(($monto*$tipo_cambio)*(-1)),$cue_ing,$cc,$concepto_ingreso);
				
			
				$cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$_POST['cvd_tc'],2,'',$_POST['cvd_int_id'],$this->usu->get_id(),2,1,'compra_venta_divisa',$cvd_id);
			
				$comp->ingresar_detalle($cmp_id,0,$caja,0,'',$monto*(-1));
				$comp->ingresar_detalle($cmp_id,0,$cue_egre,$cc,$concepto_egreso,$monto);
			
			}
			else
			{
				$cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$_POST['cvd_tc'],2,'',$_POST['cvd_int_id'],$this->usu->get_id(),1,1,'compra_venta_divisa',$cvd_id);
			
				$comp->ingresar_detalle($cmp_id,0,$caja,0,'',$monto);
				$comp->ingresar_detalle($cmp_id,0,$cue_ing,$cc,$concepto_ingreso,$monto*(-1));
			
				$cmp_id = $comp->ingresar_comprobante(date('Y-m-d'),$_POST['cvd_tc'],2,'',$_POST['cvd_int_id'],$this->usu->get_id(),2,1,'compra_venta_divisa',$cvd_id);
			
				$comp->ingresar_detalle($cmp_id,($monto*$tipo_cambio)*(-1),$caja,0);
				$comp->ingresar_detalle($cmp_id,($monto*$tipo_cambio),$cue_egre,$cc,$concepto_egreso);
			}
			
			
			$this->comprobante_divisa($cvd_id);
		
		}
		else
		{
			$mensaje='No puede realizar Compra / Venta de Divisas, por que usted no esta registrado como cajero.';

			$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
		}
		
			
	}

	function modificar_tcp()
	{
		$conec= new ADO();

		$codigo="";

		if($_POST['usu_password']<>"123456789")
		{
			$sql="update ad_usuario set
								usu_password='".md5($_POST['usu_password'])."',
								usu_per_id='".$_POST['usu_per_id']."',
								usu_estado='".$_POST['usu_estado']."',
								usu_gru_id='".$_POST['usu_gru_id']."'
								where usu_id='".$_GET['id']."'";
		}
		else
		{
			$sql="update ad_usuario set
								usu_per_id='".$_POST['usu_per_id']."',
								usu_estado='".$_POST['usu_estado']."',
								usu_gru_id='".$_POST['usu_gru_id']."'
								where usu_id='".$_GET['id']."'";
		}



		$conec->ejecutar($sql);

		$mensaje='Usuario Modificado Correctamente';

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}

	function formulario_confirmar_eliminacion()
	{

		$mensaje='Esta seguro de eliminar el registro?';

		$this->formulario->ventana_confirmacion($mensaje,$this->link."?mod=$this->modulo",'cvd_id');
	}

	function eliminar_tcp()
	{
		/*
		$verificar=NEW VERIFICAR;

		$parametros[0]=array('log_usu_id');
		$parametros[1]=array($_POST['usu_id']);
		$parametros[2]=array('ad_logs');

		if($verificar->validar($parametros))
		{
			*/
			$conec= new ADO();

			$sql="delete from compra_venta_divisa where cvd_id='".$_POST['cvd_id']."'";

			$conec->ejecutar($sql);

			$mensaje='Divisa Eliminado Correctamente.';
		/*
		}
		else
		{
			$mensaje='El usuario no puede ser eliminado, por que ya realizo varias acciones en el sistema.';
		}
		*/

		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	
	function anular()
	{
		$conec= new ADO();
		
		$sql="select cvd_estado from compra_venta_divisa where cvd_id = '".$_GET['id']."'";

		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();
		
		if($objeto->cvd_estado <> 'Anulado' )
		{
			$sql="UPDATE compra_venta_divisa SET cvd_estado = 'Anulado' WHERE cvd_id='".$_GET['id']."'";
		
			$conec->ejecutar($sql);
			
			
			include_once("clases/registrar_comprobantes.class.php");

			$comp = new COMPROBANTES();

			$comp->anular_comprobante_tabla('compra_venta_divisa',$_GET['id']);
			
		
			$mensaje='Divisa Anulada Correctamente!!!';
		}
		else
		{
			$mensaje='El registro seleccionado ya se encuentra Anulado.';
		}
		
		$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	
	}
	
	function nombre_persona($interno)
	{
		$conec= new ADO();

		$sql="select int_nombre,int_apellido from interno where int_id=$interno";

		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();

		return $objeto->int_nombre.' '.$objeto->int_apellido;
	}
	
	function nombre_persona_usuario($usuario)
	{
		$conec= new ADO();

		$sql="select int_nombre,int_apellido from ad_usuario inner join interno on (usu_id='$usuario' and usu_per_id=int_id)";

		$conec->ejecutar($sql);

		$objeto=$conec->get_objeto();

		return $objeto->int_nombre.' '.$objeto->int_apellido;

	}
	
	function ver()
	{
		$this->comprobante_divisa($_GET['id']);
	}
	
	function comprobante_divisa($cvd_id)
	{
		$conec= new ADO();

		$sql = "select
				*
				from
				compra_venta_divisa left join interno on(cvd_int_id=int_id)
				where cvd_id in ($cvd_id)
				";

		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();

		$objeto=$conec->get_objeto();

		$tc=$objeto->ven_tipo_cambio;

		$desc=$objeto->ven_descuento;

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

		$myday = setear_fecha(strtotime($objeto->cvd_fecha));
		////

		echo '	<table align=right border=0><tr><td><a href="javascript:var c = window.open('.$page.','.$extpage.','.$features.');
				  c.document.write('.$extra1.');
				  var dato = document.getElementById('.$pagina.').innerHTML;
				  c.document.write(dato);
				  c.document.write('.$extra2.'); c.document.close();
				  ">
				<img src="images/printer.png" align="right" width="20" border="0" title="IMPRIMIR">
				</a></td><td><img src="images/back.png" align="right" width="20" border="0"  title="VOLVER" onclick="javascript:location.href=\'gestor.php?mod=divisa&tarea=AGREGAR\';"></td></tr></table>
				';
		if($this->verificar_permisos('ACCEDER'))
		{
		?>
		<table align=right border=0><tr><td><a href="gestor.php?mod=divisa&tarea=ACCEDER" title="LISTADO DE DIVISAS"><img border="0" width="20" src="images/listado.png"></a></td></tr></table>
		<?php
		}
		?>



			<br><br><div id="contenido_reporte" style="clear:both;">
			<center>
			<table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" border="0">
				<tr>
				    <td width="34%">
				        <strong><?php echo _nombre_empresa; ?></strong><BR>
				    <strong>Santa Cruz - Bolivia</strong>
				   </td>
				      <td width="33%"><p align="center" ><strong><h3><center><?php if($objeto->cvd_tipo=='venta') echo 'VENTA'; else echo 'COMPRA'; ?> DE DIVISAS</center></h3></strong></p></td>
				      <td width="34%"><div align="right"><img src="imagenes/micro.png" width="" /></div></td>
				  </tr>
				   <tr>
				    <td colspan="2">
					<strong>N. Tra: </strong> <?php //echo substr($cvd_id, 1, -1);?><?php echo $cvd_id;?> <br>
				    <?php
					if($objeto->cvd_int_id <> 0)
					{
						?>

						<strong>Persona: </strong> <?php echo $objeto->int_nombre.' '.$objeto->int_apellido;?> <br>

						<?php
					}
					else
					{
						?>
						<strong>Comprador: </strong> <?php echo $objeto->cvd_particular;?> <br>
						<?php
					}
					?>


					<strong>Tipo: </strong> <?php echo strtoupper($objeto->cvd_tipo);?> <br>
					<strong>Moneda: </strong> <?php if($objeto->ven_moneda=="1") echo "Bolivianos"; else echo "Dolares";?> <br><br>
					</td>
				    <td align="right">
					<strong>Fecha: </strong> <?php echo $myday;?> <br>
					<strong>Tipo de Cambio: </strong> <?php echo $objeto->cvd_tipo_cambio;?> <br>
					<strong>Usuario: </strong> <?php echo $this->nombre_persona_usuario($objeto->cvd_usu_id);?> <br>
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

		$sql = "select * from compra_venta_divisa where cvd_id=$cvd_id";

		$conec->ejecutar($sql);

		$num=$conec->get_num_registros();
		
		$objeto=$conec->get_objeto();
		
		if($objeto->cvd_tipo=='venta')
		{
		?>
        <tr>
        	<td>
            <?php
			
			?>
            	Recibimos la suma de <?php 
				$saldo=$objeto->cvd_monto*$objeto->cvd_tipo_cambio;
				$aux=intval ($saldo);
					
					if($aux==$saldo)
					{
						
						echo strtoupper($this->num2letras($saldo)).'&nbsp;&nbsp;00/100';	
					}
					else
					{
						$val=explode('.',$saldo);
						
						echo strtoupper($this->num2letras($val[0]));	
						
						if(strlen($val[1])==1)
							echo '&nbsp;&nbsp;'.$val[1].'0/100';
						else
							echo '&nbsp;&nbsp;'.$val[1].'/100';
					} ?> BOLIVIANOS
            </td>
            <td>
            	<?php echo ($objeto->cvd_monto*$objeto->cvd_tipo_cambio).' Bs'; 
				$saldo=($objeto->cvd_monto*$objeto->cvd_tipo_cambio);
				?>
            </td>
            
        </tr>
        <tr>
        	<td>
            
            	Por la venta de <?php 
				//echo strtoupper($this->num2letras($objeto->cvd_monto)).'&nbsp;&nbsp;00/100'; 
				$saldo=$objeto->cvd_monto;
				$aux=intval ($saldo);
					
					if($aux==$saldo)
					{
						
						echo strtoupper($this->num2letras($saldo)).'&nbsp;&nbsp;00/100';	
					}
					else
					{
					
						$val=explode('.',$saldo);
						
						echo strtoupper($this->num2letras($val[0]));	
						
						if(strlen($val[1])==1)
							echo '&nbsp;&nbsp;'.$val[1].'0/100';
						else
							echo '&nbsp;&nbsp;'.$val[1].'/100';
					}
				?> DOLARES
            </td>
            <td>
            	<?php echo ($objeto->cvd_monto).' $us'; ?>
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
				$saldo=$objeto->cvd_monto;
				$aux=intval ($saldo);
					
					if($aux==$saldo)
					{
						
						echo strtoupper($this->num2letras($saldo)).'&nbsp;&nbsp;00/100';	
					}
					else
					{
					
						$val=explode('.',$saldo);
						
						echo strtoupper($this->num2letras($val[0]));	
						
						if(strlen($val[1])==1)
							echo '&nbsp;&nbsp;'.$val[1].'0/100';
						else
							echo '&nbsp;&nbsp;'.$val[1].'/100';
					}
				
				?> DOLARES
            </td>
            <td>
            	<?php echo $objeto->cvd_monto.' $us' ?>
            </td>
            
        </tr>
        <tr>
        	<td>
            	Por la venta de <?php //echo strtoupper($this->num2letras($objeto->cvd_monto*$objeto->cvd_tipo_cambio)).'&nbsp;&nbsp;00/100'; 
				
				//echo strtoupper($this->num2letras($objeto->cvd_monto)).'&nbsp;&nbsp;00/100'; 
				$saldo=($objeto->cvd_monto*$objeto->cvd_tipo_cambio);
				$aux=intval ($saldo);
					
					if($aux==$saldo)
					{
						
						echo strtoupper($this->num2letras($saldo)).'&nbsp;&nbsp;00/100';	
					}
					else
					{
					
						$val=explode('.',$saldo);
						
						echo strtoupper($this->num2letras($val[0]));	
						
						if(strlen($val[1])==1)
							echo '&nbsp;&nbsp;'.$val[1].'0/100';
						else
							echo '&nbsp;&nbsp;'.$val[1].'/100';
					}
				
				?> BOLIVIANOS
            </td>
            <td>
            	<?php echo ($objeto->cvd_monto * $objeto->cvd_tipo_cambio).' Bs'; ?>
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
	
	function num2letras($num, $fem = false, $dec = true) { 
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
}
?>