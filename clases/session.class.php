<?PHP
class SESSION
{
	function SESSION()
	{
		@session_start();
	}
	
	function set($nombre,$valor)
	{
		$_SESSION[$nombre]=$valor;
	}
	
	function get($nombre)
	{
		return $_SESSION[$nombre];
	}
	
	function cerrar()
	{
		session_destroy();
	}
}	
?>