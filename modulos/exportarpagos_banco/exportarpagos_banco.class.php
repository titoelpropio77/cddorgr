<?php

class EXPORTARPAGOS_BANCO extends BUSQUEDA 
{
	var $formulario;
	var $mensaje;

	function EXPORTARPAGOS_BANCO()
	{
		//permisos
		$this->ele_id=156;
		
		$this->busqueda();
		
		if(!($this->verificar_permisos('AGREGAR')))
		{
			$this->ban_agregar=false;
		}
		//fin permisos
		
		$this->num_registros=14;
		
		$this->coneccion= new ADO();
		
		$this->arreglo_campos[0]["nombre"]="expban_titulo";
		$this->arreglo_campos[0]["texto"]="Titulo";
		$this->arreglo_campos[0]["tipo"]="cadena";
		$this->arreglo_campos[0]["tamanio"]=25;
		
		$this->busqueda();
		
		$this->link='gestor.php';
		
		$this->modulo='exportarpagos_banco';
		
		$this->formulario = new FORMULARIO();
		
		$this->formulario->set_titulo('EXPORTAR CUOTAS - BANCO');
		
		$this->usu=new USUARIO;
		
	}
	
	function dibujar_busqueda()
	{
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
	}
	
	function dibujar_listado()
	{
		$sql="SELECT 
				* 
			  FROM 
				exportacion_banco";
		
		$this->set_sql($sql,' order by expban_id desc ');
		
		$this->set_opciones();
		
		$this->dibujar();
		
	}
	
	function dibujar_encabezado()
	{
		?>
			<tr>
	        	<th>Titulo</th>
                <th>Ventas</th>
                <th>Cuotas</th>
				<th>Fecha</th>
				<!--<th>Archivo</th>-->
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
						echo $objeto->expban_titulo;
					echo "</td>";
					echo "<td>";
						echo $objeto->expban_ventas;
					echo "</td>";
					echo "<td>";
						echo $objeto->expban_cuotas;
					echo "</td>";
					echo "<td>";
						echo $conversor->get_fecha_latina($objeto->expban_fecha);
					echo "</td>";
					
					/*
					echo "<td>";
					?>
						<a href="imagenes/exportacion_banco/<?php echo $objeto->expban_archivo; ?>">Archivo</a>
					<?php
                    echo "</td>";
					*/
					
					echo "<td>";
						//echo $this->get_opciones($objeto->expban_id);
					echo "</td>";
				echo "</tr>";
				
				$this->coneccion->siguiente();
			}
	}

	function datos()
	{
		
		if($_POST)
		{
			$conversor= new convertir();
			
			$num=0;	
			$valores[$num]["etiqueta"]="Titulo";
			$valores[$num]["valor"]=$_POST['expban_titulo'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=true;
			$num++;
			$valores[$num]["etiqueta"]="Ventas";
			$valores[$num]["valor"]=$_POST['expban_venta'];
			$valores[$num]["tipo"]="todo";
			$valores[$num]["requerido"]=false;
			$num++;
			$valores[$num]["etiqueta"]="Cuotas";
			$valores[$num]["valor"]=$_POST['expban_cuota'];
			$valores[$num]["tipo"]="todo";
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
			
		
	}
	
	function formulario_tcp($tipo)
	{
		$conec= new ADO();
			
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
		$features="'left=325,width=600,top=200,height=420,scrollbars=yes'";
		
		$this->formulario->dibujar_tarea('USUARIO');
		
		if($this->mensaje<>"")
		{
			$this->formulario->mensaje('Error',$this->mensaje);
		}
		
			?>
            <!--MaskedInput-->
			<script type="text/javascript" src="js/jquery.maskedinput-1.3.min.js"></script>
            <!--MaskedInput-->
            <script> 
			<!--
			function soloNumeros(evt)
			{
				var charCode = (evt.which) ? evt.which : event.keyCode
			
				if (charCode > 31 && (charCode < 48 || charCode > 57))
					return false;
				return true;
			}
			//-->

			</script>
            <div id="Contenedor_NuevaSentencia">
			<form id="frm_sentencia" name="frm_sentencia" action="<?php echo $url;?>" method="POST">  
				<div id="FormSent">
				  
					<div class="Subtitulo">Datos</div>
						<div id="ContenedorSeleccion">
							
							<!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Titulo</div>
							   <div id="CajaInput">
									<input  name="expban_titulo" type="text" id="expban_titulo" />
							   </div>
							</div>
							<!--Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Ventas</div>
							   <div id="CajaInput">
									<input  name="expban_venta" type="text" id="expban_venta" />
							   </div>
                               <div id="CajaInput">
								  &nbsp;&nbsp;&nbsp;<input type="checkbox"  name="ventas_todas" id="ventas_todas" value="Si">&nbsp;&nbsp;&nbsp;Todas&nbsp;&nbsp;&nbsp;
							   </div>
                               
							</div>
							<!--Fin-->
                            <!--Inicio-->
							<div id="ContenedorDiv">
							   <div class="Etiqueta" ><span class="flechas1">*</span>Cuotas</div>
							   <div id="CajaInput">
									<input  name="expban_cuota" type="text" id="expban_cuota" onkeypress="return soloNumeros(event)" />
							   </div>
                               
                               <div id="CajaInput">
								  &nbsp;&nbsp;&nbsp;<input type="checkbox"  name="cuotas_todas" id="cuotas_todas" value="Si">&nbsp;&nbsp;&nbsp;Todas&nbsp;&nbsp;&nbsp;
							   </div>
							</div>
							<!--Fin-->	
						</div>
						<div id="ContenedorDiv">
						   <div id="CajaBotones">
								<center>
									<input type="submit" class="boton" name="" value="Exportar Cuotas">
								</center>
						   </div>
						</div>
				</div>
			</form>
		</div>
   		<script>
			/*
			jQuery(function($){
			   $("#ven_fecha").mask("99/99/9999");
			});
			*/
		</script>
		<?php
	}
	
	function vista_previa()
	{
		$titulo=$_POST['expban_titulo'];
		
		$ventas=$_POST['expban_venta'];
		
		$check_ventas=$_POST['ventas_todas'];
		
		$cuotas=$_POST['expban_cuota'];
		
		$check_cuotas=$_POST['cuotas_todas'];
		
		?>
        <form id="frm_sentencia" name="frm_sentencia" action="sueltos/exportar_pagos_banco.php" method="POST" enctype="multipart/form-data">
        
        <input type="submit" class="boton" value="Exportar" />
        
		<br><br><center><h2>VISTA PREVIA EXPORTACION</h2>
        <table width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
		        	<th>Codigo</th>
                    <th>Nombre Depositante</th>
                    <th>Fecha Vencimiento</th>
					<th>Importe</th>
					<th>Mora</th>
					<th>Aviso</th>
					<th>Aviso</th>					
				</tr>	
				</thead>
				<tbody>
		<?php
		$conec= new ADO();
		$conec2= new ADO();
		
		if($_POST['ventas_todas']=='Si')
		{
			$sql="SELECT *
			FROM 
			venta 
			where ven_estado='Pendiente'";
		}
		else
		{
			$sql="SELECT *
			FROM 
			venta 
			where ven_id in ($ventas) and ven_estado='Pendiente'";
		}
		
		
				
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$conversor = new convertir();
		
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();

			if($_POST['cuotas_todas']=='Si')
			{
				$sql2="SELECT *
				FROM 
				interno_deuda
				inner join interno on (ind_int_id=int_id) 
				where ind_estado='Pendiente' and ind_tabla='venta' and ind_tabla_id=".$objeto->ven_id;
			}
			else
			{
				//$ultimo_exportado=$this->obtener_ultimo_exportado($objeto->ven_id);
				/*		
				if($ultimo_exportado<>0)
				{
					$ultimo_exportado=$ultimo_exportado-1;
				}
				*/
				
				$sql2="SELECT *
				FROM 
				interno_deuda
				inner join interno on (ind_int_id=int_id) 
				where ind_estado='Pendiente' and ind_tabla='venta' and ind_tabla_id=".$objeto->ven_id." LIMIT 0,$cuotas";
			}
			
					
			$conec2->ejecutar($sql2);
			
			$num2=$conec2->get_num_registros();
			
			for($j=0;$j<$num2;$j++)
			{
				$objeto2=$conec2->get_objeto();
			
				echo '<tr>';
										
					echo "<td>";
						echo $this->generar_codigo($objeto->ven_lot_id);
					echo "&nbsp;</td>";
					echo "<td>";
						
						if(strlen(trim($objeto2->int_nombre.' '.$objeto2->int_apellido))>40)
						{
							echo substr(utf8_encode(trim($objeto2->int_nombre.' '.$objeto2->int_apellido)), 0,40);
						}
						else
						{
							echo utf8_encode(trim($objeto2->int_nombre.' '.$objeto2->int_apellido));
						}
						
						//echo strtoupper($objeto2->int_nombre.' '.$objeto2->int_apellido);
						//if($objeto->ven_co_propietario<>0)
							//echo ' / '.strtoupper($this->nombre_interno($objeto->ven_co_propietario));
					echo "&nbsp;</td>";
					echo "<td>";
						echo substr($objeto2->ind_fecha_programada,0,4).substr($objeto2->ind_fecha_programada,5,2).substr($objeto2->ind_fecha_programada,8,2);
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto2->ind_monto;
					echo "&nbsp;</td>";
					echo "<td>";
						//echo $objeto2->int_nombre;
					echo "&nbsp;</td>";
					echo "<td>";
						echo '0';
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto2->ind_num_correlativo;
					echo "&nbsp;</td>";

				echo "</tr>";
					
				$conec2->siguiente();
			}
				
			$conec->siguiente();
		}
		?>
		</tbody>
		</table>
        
        </center>
		
        <br />
        
        <input type="hidden" readonly="readonly" name="titulo" id="titulo" value="<?php echo $titulo; ?>" />
        <input type="hidden" readonly="readonly" name="venta" id="venta" value="<?php echo $ventas; ?>" />
        <input type="hidden" readonly="readonly" name="cuota" id="cuota" value="<?php echo $cuotas; ?>" />
        <input type="hidden" readonly="readonly" name="check_cuotas" id="check_cuotas" value="<?php echo $check_cuotas; ?>" />
        <input type="hidden" readonly="readonly" name="check_ventas" id="check_ventas" value="<?php echo $check_ventas; ?>" />
        
        <input type="submit" class="boton" value="Exportar" />
        
        </form>
		<?php
	}

	function insertar_tcp()
	{
		$conversor= new convertir();
		
		/*
		$verificar=NEW VERIFICAR;
		
		$parametros[0]=array('usu_id');
		$parametros[1]=array($_POST['usu_id']);
		$parametros[2]=array('ad_usuario');
		
		if($verificar->validar($parametros))
		{
		*/
			$conec= new ADO();
		
			$sql="insert into exportacion_banco (expban_titulo,expban_venta,expban_cuota,expban_fecha) values ('".$_POST['expban_titulo']."','".$_POST['expban_venta']."','".$_POST['expban_cuota']."','".date('Y-m-d')."')";
			
			$conec->ejecutar($sql,false);
			
			$llave=mysql_insert_id();
			
			//$this->importar($llave);
			
			$_GET['id']=$llave;
			
			?>
            <script>
            location.href='gestor.php?mod=importarexcel&tarea=VER&id='+<?php echo $_GET['id']; ?>;
            </script>
			<?php
		/*
		}
		else
		{
			$mensaje='El usuario no puede ser agregado, por que ya existe una persona con ese nombre de usuario.';
		}
		*/
		
		//$this->formulario->ventana_volver($mensaje,$this->link.'?mod='.$this->modulo);
	}
	
	
	
	function ver()
	{
		$this->listado_cuentas();
	}
	
	function importar($llave)
	{
			if($_GET['n_archivo']<>"")
			{	
				$result="";
				$nombre_archivo=$_GET['n_archivo'];
			}	
			else
				$result=$this->subir_archivo($nombre_archivo,$_FILES['archivo']['name'],$_FILES['archivo']['tmp_name']);
			
			
			if(trim($result)<>'')
			{
				
				$this->formulario->ventana_volver($result,$this->link.'?mod='.$this->modulo.'&tarea=ACCEDER');
			}
			else 
			{
			
				/////
				
				require_once 'excel/reader.php';

				$data = new Spreadsheet_Excel_Reader();

				$data->setOutputEncoding('CP1251');
				
				 

				if($_GET['n_archivo']<>"")
					$data->read("imagenes/importacion/".$_GET['n_archivo']);
				else
					$data->read("imagenes/importacion/$nombre_archivo");
				
				if($_POST['invalidos']<>"")
					$invalidos=$_POST['invalidos'];
				else
					$invalidos="<ul>";
				
				if($_POST['existen']<>"")			
					$existen=$_POST['existen'];
				else
					$existen="<ul>";
				
				
				$conec= new ADO();
				
				
				if($_POST["categoria"])
					$categoria=$_POST["categoria"];
				else
					$categoria=array();
				
				$num_filas=$data->sheets[0]['numRows'];
				
				
				$num_enviar=50;
				
				if($_GET['puntero']<>"")
					$ini=$_GET['puntero'];
				else
					$ini=2;
					
				$fin=$ini + $num_enviar;
				
				if($fin > $num_filas)
				{	
					$fin=$num_filas;
				}
				else
				{
					?>
					<center><br/><img src="imagenes/carga.gif" border="0" /><br/>Importando</center>
					<?php
				}
					
					
				for ($i = $ini; $i <= $fin; $i++) 
				{
					list($nombre,$ci,$telefono,$codigo,$uv,$manzano,$lote,$superficie,$plazo,$cuota_inicial,$valor_total,$comision,$monto_cuota)=array($data->sheets[0]['cells'][$i][1],$data->sheets[0]['cells'][$i][2],$data->sheets[0]['cells'][$i][3],$data->sheets[0]['cells'][$i][4],$data->sheets[0]['cells'][$i][5],$data->sheets[0]['cells'][$i][6],$data->sheets[0]['cells'][$i][7],$data->sheets[0]['cells'][$i][8],$data->sheets[0]['cells'][$i][9],$data->sheets[0]['cells'][$i][10],$data->sheets[0]['cells'][$i][11],$data->sheets[0]['cells'][$i][12],$data->sheets[0]['cells'][$i][13]);
					
					if($nombre<>'')
					{
						if($this->existe_slash($nombre))
						{
							$this->obtener_nombres($nombre,$propietario1,$propietario2);
							
							$this->obtener_ci($ci,$ci1,$ci2);
							
							$nombre1=$propietario1;
							$ci1=$ci1;
						
							$nombre2=$propietario2;
							$ci2=$ci2;
								
							if($this->existe_persona($nombre1,$id_interno))
							{
								$id_interno1=$id_interno;
							}
							else
							{
								$id_interno1=$this->insertar_persona($nombre1,$ci,$telefono);
							}
							
							if($this->existe_persona($nombre2,$id_interno))
							{
								$id_interno2=$id_interno;
							}
							else
							{
								$id_interno2=$this->insertar_persona($nombre2,$ci,$telefono);
							}
							
							$_POST['nombre']=$nombre;
							$_POST['ci']=$ci;
							$_POST['llave']=$llave;
							$this->insertar_venta($id_interno1,$ci1,$telefono,$codigo,$uv,$manzano,$lote,$superficie,$plazo,$cuota_inicial,$valor_total,$comision,$id_interno2,$monto_cuota);
						}
						else
						{
							if($this->existe_persona($nombre,$id_interno))
							{
								$id_interno=$id_interno;
							}
							else
							{
								$id_interno=$this->insertar_persona($nombre,$ci,$telefono);
							}
							$_POST['nombre']=$nombre;
							$_POST['ci']=$ci;
							$_POST['llave']=$llave;
							$this->insertar_venta($id_interno,$ci1,$telefono,$codigo,$uv,$manzano,$lote,$superficie,$plazo,$cuota_inicial,$valor_total,$comision,0,$monto_cuota);
						}
					}
				}
				
				$conec= new ADO();
		
        		$sql="update importacion set imp_archivo='$nombre_archivo' where imp_id=".$llave;
				
				$conec->ejecutar($sql);
				
            }	
	}
	
	function listado_cuentas()
	{
		?>
		<script>		
		function pagar_comision(id){
				var txt = 'Estas seguro que realizaras el pago de la comisión?';
				
				$.prompt(txt,{ 
					buttons:{Pagar:true, Cancelar:false},
					callback: function(v,m,f){
						
						if(v){
								location.href='gestor.php?mod=vendedor&tarea=CUENTAS&acc=comision&id='+id;
						}
												
					}
				});
			}
			
		function cambiar_comision(id){
			var txt = 'Estas seguro que cambiara la comisión a otro vendedor?';
			
			$.prompt(txt,{ 
				buttons:{Cambiar:true, Cancelar:false},
				callback: function(v,m,f){
					
					if(v){
							location.href='gestor.php?mod=vendedor&tarea=CUENTAS&acc=frm_cambiar&id=<?php echo $_GET['id']; ?>&com='+id;
					}
											
				}
			});
		}	

		</script>
        <?php
		$conec= new ADO();
		
        $sql="select * from vendedor
		inner join interno on (int_id=vdo_int_id) where vdo_id=".$_GET['id'];
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();

		$objeto=$conec->get_objeto();
		?>
        <table style="font-size:12px;" width="100%"  cellpadding="5" cellspacing="0" >
        	<tr>
        		<td width="40%" ></td>
        		<td  width="20%" ><p align="center" ><strong><h3><center></center></h3></strong></p></td>
        		<td  width="40%" ><div align="right"></div></td>
      		</tr>
       		<tr>
        		<td colspan="2">
                	<br />
        			<strong>Vendedor: </strong> <?php echo $objeto->int_nombre.' '.$objeto->int_apellido;?>
        		</td>
        		<td align="right">
        		</td>
      		</tr> 
        </table>
		<br><br><center><h2>VENTAS IMPORTADAS</h2><table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
		        	<th>Cliente</th>
                    <th>CI</th>
                    <th>Telefono</th>
					<th>Codigo</th>
					<th>Uv</th>
					<th>Manzano</th>					
					<th>Lote</th>
					<th>Superficie</th>
                    <th>Plazo</th>
                    <th>Cuota Inicial</th>
					<th>Valor Total</th>
                    <th>Comision</th>
                    <th>Comentario</th>
				</tr>	
				</thead>
				<tbody>
		<?php
		$conec= new ADO();
		
		$sql="SELECT *
		FROM 
		importacion_log 
		where
		implog_importado='si' and implog_imp_id='".$_GET['id']."'
		order by implog_id asc";
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$conversor = new convertir();
		$totalbs=0;
		$totalsus=0;
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();

			echo '<tr>';
									
					echo "<td>";
						echo $objeto->implog_nombre;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->implog_ci;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->implog_telefono;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->implog_codigo;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->implog_uv;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->implog_manzano;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->implog_lote;
					echo "&nbsp;</td>";
					
					
					echo "<td>";
						echo $objeto->implog_superficie;
					echo "&nbsp;</td>";
						
						
					echo "<td>";
						echo $objeto->implog_plazo.' años';
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->implog_cuota_inicial;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->implog_monto;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->implog_comision;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->implog_comentario;
					echo "&nbsp;</td>";
					
					
				echo "</tr>";
			
			$conec->siguiente();
		}
		?>
		</tbody>
		<!--
        <tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
                <td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>					
				<td>&nbsp;</td>
				<td><?php echo $totalbs.' Bs'; ?></td>
				<td><?php echo $totalsus.' $us'; ?></td>
				<td>&nbsp;</td>
			</tr>	
		</tfoot>
        -->
		</table></center>
		
		<br><br><center><h2>VENTAS NO IMPORTADAS</h2><table   width="100%"  class="tablaReporte" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
		        	<th>Cliente</th>
                    <th>CI</th>
                    <th>Telefono</th>
					<th>Codigo</th>
					<th>Uv</th>
					<th>Manzano</th>					
					<th>Lote</th>
					<th>Superficie</th>
                    <th>Plazo</th>
                    <th>Cuota Inicial</th>
					<th>Valor Total</th>
                    <th>Comision</th>
                    <th>Comentario</th>
				</tr>
				</thead>
				<tbody>
		<?php
		$conec= new ADO();
		
		$sql="SELECT *
		FROM 
		importacion_log 
		where
		implog_importado='no' and implog_imp_id='".$_GET['id']."'
		order by implog_id asc";
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		$conversor = new convertir();
		$totalbs=0;
		$totalsus=0;
		for($i=0;$i<$num;$i++)
		{
			$objeto=$conec->get_objeto();

			echo '<tr>';
									
					echo "<td>";
						echo $objeto->implog_nombre;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->implog_ci;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->implog_telefono;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->implog_codigo;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->implog_uv;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->implog_manzano;
					echo "&nbsp;</td>";
					echo "<td>";
						echo $objeto->implog_lote;
					echo "&nbsp;</td>";
					
					
					echo "<td>";
						echo $objeto->implog_superficie;
					echo "&nbsp;</td>";
						
						
					echo "<td>";
						echo $objeto->implog_plazo.' años';
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->implog_cuota_inicial;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->implog_monto;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->implog_comision;
					echo "&nbsp;</td>";
					
					echo "<td>";
						echo $objeto->implog_comentario;
					echo "&nbsp;</td>";
					
					
				echo "</tr>";
			
			$conec->siguiente();
		}
		?>
		</tbody>
        <!--
		<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
                <td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>					
				<td>&nbsp;</td>
				<td><?php echo $tot; ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>	
		</tfoot>
        -->
		</table></center>
		<?php
	}
	
	
	//----------------------------- Funciones Extras ---------------------------//

	function generar_codigo($id_lote)
	{
		$conec= new ADO();
				
		$sql="select urb_nombre,man_nro,lot_nro,zon_nombre,uv_nombre 
		from 
		lote
		inner join zona on (lot_id='".$id_lote."' and lot_zon_id=zon_id)
		inner join uv on (lot_uv_id=uv_id)	
		inner join manzano on (lot_man_id=man_id)
		inner join urbanizacion on(man_urb_id=urb_id)";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		$man_nro=$objeto->man_nro;
		
		if(strlen($man_nro)==1)
			$man_nro='0'.$man_nro;
		
		$lot_nro=$objeto->lot_nro;
		
		if(strlen($lot_nro)==1)
			$lot_nro='0'.$lot_nro;
		
		return 'A'.trim($man_nro).trim($lot_nro);
	}
	
	function nombre_interno($int_id)
	{
		$conec= new ADO();
				
		$sql="select int_nombre,int_apellido from interno where int_id=$int_id";
		
		$conec->ejecutar($sql);
		
		$objeto=$conec->get_objeto();
		
		return $objeto->int_nombre.' '.$objeto->int_apellido;
	}
	
	function obtener_ultimo_exportado($venta)
	{
		$conec= new ADO();
				
		$sql="select ind_id,ind_expbanco from interno_deuda where ind_tabla='venta' and ind_expbanco='Si' ORDER BY ind_id DESC LIMIT 0,1";
		
		$conec->ejecutar($sql);
		
		$num=$conec->get_num_registros();
		
		if($num>0)
		{
			$objeto=$conec->get_objeto();
		
			return $objeto->ind_id;
		}
		else
		{
			return 0;
		}	
	}
}
?>
