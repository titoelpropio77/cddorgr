<?php
include_once ('TNodo.php');

class TArbol
{
  public $vector;
  public $nombreVariable;
  
  function __construct()
  {
    $this->vector = NULL;
    $this->nombreVariable = "a";
  }
  
  function cantHijos()
  {
    
	if (!$this->vector) return 0;
	return sizeof($this->vector);
  }
  
  
  function addNodo($nodo)
  {
     if (!$this->vector)
	   $this->vector=array();
	 $this->vector[sizeof($this->vector)]=$nodo;
  }
  
  function toString()
  { 
	 //si est� vac�o el �rbol, devuelvo solamente el vector
	 $res='';
	 if (!$this->vector)
	   return  $this->nombreVariable."[]";
	 
	 //no est� vac�o el �rbol, debo concatenar todos los vectores
	 $res="";
	 for ( $i=0; $i < sizeof($this->vector); $i++ )
	    $res.=$this->vector[$i]->toString($this->nombreVariable."[$i]")."<br>";
	 
	 return $res; 
  }
  

  function toJavaScript()
  { 
	 //declaraci�n de un array en javascript	 
	 $res= "var $this->nombreVariable = new Array;";

	 //si est� vac�o el �rbol, devuelvo solamente el vector	 
	 if (!$this->vector)
	   return $res;
	 
	 //no est� vac�o el �rbol, debo concatenar todos los vectores
	 for ( $i=0; $i < sizeof($this->vector); $i++ )
	 {
	    $res.=$this->nombreVariable."[$i] = new Array;";
		$res.=$this->vector[$i]->toJavascript($this->nombreVariable."[$i]");
	 }		 
	 return $res; 
  }
}
?>