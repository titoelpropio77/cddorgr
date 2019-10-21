<?PHP 

define('_mes_1','Enero');
define('_mes_2','Febrero');
define('_mes_3','Marzo');
define('_mes_4','Abril');
define('_mes_5','Mayo');
define('_mes_6','Junio');
define('_mes_7','Julio');
define('_mes_8','Agosto');
define('_mes_9','Septiembre');
define('_mes_10','Octubre');
define('_mes_11','Noviembre');
define('_mes_12','Diciembre');
define('_ano_inicio','2008');
define('_ano_fin','2009');

require_once("clases/ventana.php");
//require_once("comun_mysql.php");
//require_once("../parametros/es.php");

class ALMANAQUE
{
	var $dia;
	var $mes;
	var $anio;
	var $num_meses;
	var $titulo;
	var $nombre;
	var $query;
	var $mensaje;
	var $hay_actividad;
	var $fraternos;
	
	var $ventana_invisible;
	
	function ALMANAQUE($titulo='Almanaque',$nombre='mes')
	{
		if(isset($_GET['dia']))
		{
			$this->dia=$_GET['dia'];
		}
		else
		{
			$this->dia=date("d");
		}
		
		if($this->dia<10)
		{
			$this->dia='0'.$this->dia;
		}
		
		if(isset($_GET['mes']))
		{
			$this->mes=$_GET['mes'];
			
		}
		else
		{
			$this->mes=date("m");
		}
		
		if(isset($_GET['anio']))
		{
			$this->anio=$_GET['anio'];
		}
		else
		{
			$this->anio=date("Y");
		}

		$this->num_meses=1;
		
		$this->titulo=$titulo;
		
		$this->nombre=$nombre;
		//$this->query=new QUERY;
		$this->crear_flotante();
		$this->mensaje="";
	}
	
	function crear_flotante()
	{
		$this->ventana_invisible=new VENTANA2;
		
		$parametros['izquierda']='15%';
		$parametros['arriba']='98px';
		$parametros['ancho']='220px';
		$parametros['alto']='100px';
		$parametros['titulo']='flotante';
		$this->ventana_invisible->add_css_param ('border-right','1px #000000 solid');
		$this->ventana_invisible->add_css_param ('border-left','1px #000000 solid');
		$this->ventana_invisible->add_css_param ('border-top','1px #000000 solid');
		$this->ventana_invisible->add_css_param ('border-bottom','1px #000000 solid');
		$this->ventana_invisible->add_css_param ('display','none');
		$this->ventana_invisible->add_css_param ('overflow','auto');
			
		$this->ventana_invisible->set_ventana($parametros);
		$this->ventana_invisible->generar_css();
		$this->ventana_invisible->abrir();
			 /*echo '<center>
				
					<center><a href="#" onclick="document.getElementById(\'flotante\').style.display=\'none\';">'._men_exit.'</a></center>
				
			      </center>';*/
			  echo '<style type="text/css">';
			  	echo '#flotante_int{overflow:auto;
			  			border-right:2px #DDDDDD solid;
			  			border-left:2px #DDDDDD solid;
			  			border-top:2px #DDDDDD solid;
			  			border-bottom:2px #DDDDDD solid;
						background-image:url(images/fondo.jpg)}';
						
			  	echo '#fecha_flot{
			  			border-right:2px #DDDDDD solid;
			  			border-left:2px #DDDDDD solid;
			  			border-top:2px #DDDDDD solid;
			  			border-bottom:2px #DDDDDD solid;
			  			background-image:url(images/fondo.jpg)}';
			  		
			  echo '</style>';
			
			  echo '<div id="fecha_flot"></div>';
			  	
				
			echo '<div id="flotante_int">';
				echo '<br><br>';
			echo '</div>';
			
			echo '<center>
				<table  background=../images/fondo.jpg>
				<tr>
				<td >
					<a href="#" onclick="document.getElementById(\'flotante\').style.display=\'none\';"><img src='._imagen_eliminar2.' border=0 ></a>
				</td>
				</tr>
			   	</table>
			      </center>';
			
		$this->ventana_invisible->cerrar();
	}
	
	function funcion_posicion()
	{
		echo '<script>';
			echo 'function posiciones(e,corr_x,corr_y)
				{
				    var posx = 0;
    				    var posy = 0;
    				    if (!e) var e = window.event;
    					if (e.pageX || e.pageY)
    					{
        					posx = e.pageX-360;
        					posy = e.pageY-60;
    					}
    					else if (e.clientX || e.clientY)
    					{
        					posx = e.clientX-360;
        					posy = e.clientY-60;
    					}

					document.getElementById(\'flotante\').style.left = posx;
					
					document.getElementById(\'flotante\').style.top = posy;
				
				}';
		echo '</script>';
	}
	
	function fecha()
	{
		
		$this->funcion_posicion();
		
	}
	
	function mes_scroll()
	{
		$mes_texto='<div id="mes_scroll">';
			if($this->mes>1)
			{
				$anterior=$this->mes-1;
				$mes_texto.='<a class="mes" href="gestor.php?mod=repcumpleano&dia=01&mes='.$anterior.'&anio='.$this->anio.'">'.constant('_mes_'.$anterior).'</a>';
			}
		$actual=$this->mes+0;
		$mes_texto.='<font style="font-size:11px; font-family: Tahoma, "Franklin Gothic Medium";"><b>&nbsp;&nbsp;'.constant('_mes_'.$actual).'&nbsp;&nbsp;</b></font>';
			if($this->mes<12)
			{
				$anterior=$this->mes+1;
				$mes_texto.='<a class="mes" href="gestor.php?mod=repcumpleano&dia=01&mes='.$anterior.'&anio='.$this->anio.'">'.constant('_mes_'.$anterior).'</a>';
			}
		$mes_texto.='</div>';
		
		return $mes_texto;
	}
	
	function dibujar()
	{
		
		
		echo '<script>
			function tratarFecha(dia,mes,ano)
			{';
				echo 'document.location ="gestor.php?mod=repcumpleano&dia="+dia+"&mes="+mes+"&anio=" + ano;';
			echo '}
		     </script>';
	
		if(trim($this->mes)<>"")
			{
				switch ($this->mes)
				{
					case 1:{$mes_nombre=_mes_1;break;}
					case 2:{$mes_nombre=_mes_2;break;}
					case 3:{$mes_nombre=_mes_3;break;}
					case 4:{$mes_nombre=_mes_4;break;}
					case 5:{$mes_nombre=_mes_5;break;}
					case 6:{$mes_nombre=_mes_6;break;}
					case 7:{$mes_nombre=_mes_7;break;}
					case 8:{$mes_nombre=_mes_8;break;}
					case 9:{$mes_nombre=_mes_9;break;}
					case 10:{$mes_nombre=_mes_10;break;}
					case 11:{$mes_nombre=_mes_11;break;}
					case 12:{$mes_nombre=_mes_12;break;}
				}
			}
			
			$tabla_calendario='<table  width="100%"    cellspading="0" cellspacing="1"   align="left">';
			
			$tabla_calendario.='<tr  ><td colspan="7">'.$this->mes_scroll().'</td></tr>';
				
				$fecha = mktime(0,0,0,$this->mes,$this->dia,$this->anio);
				$fechaInicioMes = mktime(0,0,0,$this->mes,1,$this->anio);
				$fechaInicioMes = date("w",$fechaInicioMes);		
				
						//datos para la tabla25
						$diasSem = Array ('L','M','Mi','J','V','S','D');
						$ultimoDia = date('t',$fecha);
						//echo $ultimoDia;
						$numMes = 0;
						for ($fila = 0; $fila < 7; $fila++)
						{
						  $corr_y='0';
						  if($fila>3)
						  {
						  	$corr_y='-180';
						  }
						  $tabla_calendario.="      <tr style='font-size:10px'>\n";
						  for ($coln = 0; $coln < 7; $coln++)
						  {
						  	$corr_x='0';
						   	if($coln>4)
						   	{
						   	 $corr_x='-220';
						   	}
						   	
						    $posicion = Array (1,2,3,4,5,6,0);
							$tabla_calendario.= '<td  height="53px" width="14%" ';	    
								
								//$descripcion=$this->contenido_fecha();
								$sw1=true;
								
								$this->mensaje='<center><table cellspacing=0 cellspading=0 width=90% background=images/fondo.jpg>';
								$this->hay_actividad=false;
								
								if($this->actividades($numMes+1))
								{
									$bgcolor=' bgcolor="#64CE64" valign="top"';
								}
								
								
								if(!$this->hay_actividad)
								{
									if($this->dia-1 == $numMes)
									{
										$bgcolor= ' bgcolor="#FFCC00" valign="top"';
									
									}
									else
									{
										$bgcolor=' bgcolor="#DBE9F0" valign="top"';
									}
								}
									
								$this->mensaje.="</table></center>";
								
								 if($fila == 0)
								{
									$bgcolor=' bgcolor="#006699"';
									$tabla_calendario.= $bgcolor." align=\"center\">";
								}
								else
								{
									$tabla_calendario.= $bgcolor." align=\"left\">";
								}

						    
						    		$tabla_calendario.='';
						    
							if($fila == 0)
							{
								$tabla_calendario.= '<font color="#FFCC00"><strong>'.$diasSem[$coln].'</strong>';
								
							}
						    	elseif(($numMes && $numMes < $ultimoDia) || (!$numMes && $posicion[$coln] == $fechaInicioMes))
							{	
								$midia=$numMes+1;
								if($midia<10)
								{	$midia="0".$midia;
								}
								$mimes=$this->mes;
								if($mimes<10)
								{	$mimes=$mimes;
								}
								$mifecha=$midia.'/'.$mimes.'/'.$this->anio;
								
								$content_header='<center><table width=90% border=0 cellspacing=0 cellspading=0><tr><td><b>&nbsp;&nbsp;Fecha :</b>'.$mifecha.'</tr></td></table></center>';
								
								$tabla_calendario.='';
								
								if($this->hay_actividad)
								{
									 $tabla_calendario.= '<a class="mes" href="#">';
									++$numMes;
								}
								else
								{
									$tabla_calendario.= '<a class="mes" href="#">';
									++$numMes;
								}
								
								
								$this->mensaje="";
								
								
							
								
								if($this->dia == $numMes)
								{
									$tabla_calendario.= '<font color="#006699">';
								
								}
								$m=$this->mes;
								$d=$numMes;
								$val=$numMes;
								$a=$this->anio;
								//if($m<10){$m="0".$m;}
								if($d<10){$d="0".$d;}
								$f=$a."-".$m."-".$d;
								if($val<10){$val="0".$val;}	
								$tabla_calendario.=($val);
								$tabla_calendario.='<br>'.$this->fraternos;
								
						   	 }
							    $tabla_calendario.= "</td>\n";
						  }
						  $tabla_calendario.= "      </tr>\n";
						}
				$tabla_calendario.='</table>';
				
				echo $tabla_calendario;
					
	}
	
	function get_valor()
	{
		
	}
	
	function contenido_fecha($dia,$mes,$anio)
	{
		if($dia<10)
		{
			$dia+=0;18;
			$dia='0'.$dia;
		}
	
		$contenido=$dia.'--'.$mes.'--'.$anio;
		
		return $contenido;
	}
	
	function mostrar_ventana_flotante($dia)
	{
		if($dia<10)
		{
		  $dia=$dia+0;
		  $dia='0'.$dia;
		}
		
		$fecha_buscar=$this->anio.'-'.$this->mes.'-'.$dia;
		
		
	}
	
	function set_valor($dia,$mes,$anio)
	{
		$this->dia=date("d",$dia);
		$this->mes=date("m",$mes);
		$this->anio=date("d",$anio);
	}
	
	function actividades($dia)
	{
		$this->fraternos="";
		
		if($dia<10)
		{
		  $dia=$dia+0;
		  $dia='0'.$dia;
		}
		
		if($this->mes<10)
		{
		  $mes_d=$this->mes+0;
		  $mes_d='0'.$mes_d;
		  
		}
		else
		{
			$mes_d=$this->mes+0;
		}
		
		$fecha_buscar='-'.$mes_d.'-'.$dia;
		
		$es_fecha=false;
		
		$mens="";
		
		$check_prt2 ="select
				per_id,per_nombre,per_apellido
			from 
				gr_persona inner join socio on (per_id=soc_per_id)
			where
				per_fecha_nacimiento like'%".$fecha_buscar."%'
			";	
		  
		$qry_prt2 = mysql_query($check_prt2) or die ("No se puede seleccionar la consulta <p>$check_prt2<p> porque ".mysql_error());
		
		$num_rows = mysql_num_rows($qry_prt2);

		if($num_rows > 0)
		{
			$this->hay_actividad=true;
			$es_fecha=true;
			
			while ($qry_r_prt2=mysql_fetch_array($qry_prt2)) 
			{	
					
				$per=$qry_r_prt2['per_nombre'].' '.$qry_r_prt2['per_apellido'];
				
				if(strlen(trim($per))>20)
				{
					$per=substr(trim($per), 0,20)."..";
				}
				
				$this->fraternos.='* <a class="group" href="sueltos/detalle_cumpleano.php?&id='.$qry_r_prt2['per_id'].'">'.$per.'</a><br>';
				
			}
			
		}
		else
		{
			$es_fecha=false;
		}
		
			return $es_fecha;
		
	}
};
	
?>
