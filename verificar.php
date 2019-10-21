<?PHP

class VERIFICAR 
{
	function validar($valores="")
	{		
		$numero= count($valores[0]);
		
		$query=new ADO();
		
		$sql=" select * from ".$valores[2][0]." where ".$this->formar_where($valores).$valores[3][0];
		
		$query->ejecutar($sql);
		
		if($query->get_num_registros()>0)
		{
			return false;
		}
		else
		{
			return true;
		}
		
	} 
	
	function formar_where($valores="")
	{
		$numero= count($valores[0]);
		$cadena="";
		for($i=0;$i<$numero;$i++)
		{
			if($i==0)
			{
				$cadena.=$valores[0][$i]."='".$valores[1][$i]."' ";
			}
			else
			{
				$cadena.="and ".$valores[0][$i]."='".$valores[1][$i]."' ";
			}
				
		}
		return $cadena;
	}
};
?>
