<?PHP

class VALIDADOR 
{
	var $mensaje;

	function VALIDADOR()
	{
		$this->mensaje="";
	}
	
	function validar($valores="")
	{		
		$numero= count($valores);
		
		$ban=true;
		
		for($i=0;$i<$numero;$i++)
		{
			if($valores[$i]['requerido'])
			{
				if(trim($valores[$i]['valor'])<>"")
				{
					if(!($this->verificar($valores[$i]['valor'],$valores[$i]['tipo'])))
					{
						$this->mensaje.="El valor ingresado en el campo <b>".$valores[$i]['etiqueta']."</b> no es valido<br>";
						
						$ban=false;
					}
				}
				else
				{
					$this->mensaje.="El campo <b>".$valores[$i]['etiqueta']."</b> es requerido<br>";
					
					$ban=false;
				}
			}
			else
			{
				if(trim($valores[$i]['valor'])<>"")
				{
					if(!($this->verificar($valores[$i]['valor'],$valores[$i]['tipo'])))
					{
						$this->mensaje.="El valor ingresado en el campo <b>".$valores[$i]['etiqueta']."</b> no es valido<br>";
						
						$ban=false;
					}
				}
			}
		}
		
		return $ban;
	} 
	
	function verificar($valor,$tipo)
	{
		$exp=$this->expresion_regular($tipo);
		
		if(trim($exp)<>"")
		{
			if(ereg($exp,$valor))
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
			return true;	
		}
	}
	
	function expresion_regular($tipo)
	{
		switch($tipo)
		{
			//solo texto
			case 'texto':{
					$exp="^[A-Za-z'][a-zA-Z .'ñÑ]*$";
					break;
					}
			//solo numeros
			case 'numero':{
					$exp='^[0-9][0-9]*$';
					break;
					}
			//solo reales
			case 'real':{
					$exp='^[0-9]+([.]{0,1})[0-9]*$';
					break;
					}
			//solo fecha
			case 'fecha':{
					$exp='^[0-9][0-9][0-9][0-9]-([0][1-9]|[1][0-2])-[0-3][0-9]$';
					break;
					}		
			//solo mail's
			case 'mail':{
					$exp='^([a-zA-Z0-9_.-])+@(([a-z0-9_]|-)+.)+[a-z]{2,4}$';
					break;
					}
					
			default:{
					$exp='';
					break;
					}
		}
		return $exp;
	}
};
?>
