<?php
class TNodo 
{
  private $vector;
  public $eventos;
  
  public $id;
  public $caption;
  public $url;
  public $target;
  public $isOpen;
  public $isChecked;
  public $onClickCaption;
  public $radioButtonSelected;
  public $imageDir;
  public $icon;
  
  function __construct()
  {  
      $this->vector              = NULL;
	  $this->eventos             = NULL;
      $this->id;
	  $this->caption;
	  $this->url;
	  $this->target              = "principal9";
	  $this->isOpen              = false;
	  $this->isChecked           = 0;
	  $this->onClickCaption;
	  $this->radioButtonSelected;
	  $this->imageDir            = NULL;
	  $this->icon                = NULL;       
  }
  
  function addNodo($nodo)
  {
    if ( !$this->vector )
    $this->vector=array();
	$this->vector[sizeof($this->vector)]= $nodo;
  }

   
  function getVectorHijos()
  {
    return $this->vector;
  }
  
  function cantHijos()
  {
     if ( !$this->vector )
	 return 0;
	 
	return sizeof($this->vector);
  }
 
  function addEvento_onClickCaption($comando)
  {
    if ( !$this->eventos )
      $this->eventos=array();	  
    $this->eventos[sizeof($this->eventos)]="['onClickCaption']= \"".$comando."\";";
  }
  
  function addEvento_onBeforeOpen($comando)
  {
    if ( !$this->eventos )
      $this->eventos=array();	  
    $this->eventos[sizeof($this->eventos)]="['onBeforeOpen']= \"".$comando."\";";
  }

  function addEvento_onAfterOpen($comando)
  {
    if ( !$this->eventos )
      $this->eventos=array();	  
    $this->eventos[sizeof($this->eventos)]="['onAfterOpen']= \"".$comando."\";";
  }
  
  function addEvento_onAfterClose($comando)
  {
    if ( !$this->eventos )
      $this->eventos=array();	  
    $this->eventos[sizeof($this->eventos)]="['onAfterClose']= \"".$comando."\";";
  }
  
  
  
  
  function addEvento_onBeforeClose($comando)
  {
    if ( !$this->eventos )
      $this->eventos=array();	  
    $this->eventos[sizeof($this->eventos)]="['onBeforeClose']= \"".$comando."\";";
  }

  
  function toString($antecesor="")
  {
	$res='';
	if ($this->id)
	   $res.=$antecesor."['id']=".$this->id."; <br>";
	
	if ($this->caption)
	   $res.=$antecesor."['caption']="."'".$this->caption."'; <br>";


	if ($this->url)
	{
	   $res.=$antecesor."['url']="."'".$this->url."'; <br>";
	   $res.=$antecesor."['target']="."'".$this->target."'; <br>";
	}   

	if ($this->isOpen)
	 $bool='true';
	else 
	 $bool='false';
	 
	$res.=$antecesor."['isOpen']= ".$bool."; <br>";
	   
	$res.=$antecesor."['isChecked']= ".$this->isChecked."; <br>";

	if ( isset($this->radioButtonSelected) )
	{
	  if ($this->radioButtonSelected)
	     $bool="true";
	  else
         $bool="false";	  
      $res.=$antecesor."['radioButtonSelected']= $bool; <br>";
	}
	
    if ($this->icon && strlen($this->icon)>0)
	   $res.=$antecesor."['icon']= \"$this->icon\"; <br>" ;

    if ($this->imageDir && strlen($this->imageDir)>0)
	   $res.=$antecesor."['imageDir']= \"$this->imageDir\"; <br>" ;
	
	
	if ($this->onClickCaption)
	  $res.=$antecesor."['onClickCaption']= '$this->onClickCaption';  <br>";

	
	//muestro los eventos (si tiene alguno asociado)
	if ( $this->eventos && sizeof($this->eventos)>0 ) 
  	  for ($i=0; $i < sizeof($this->eventos); $i++)
	     $res.=$antecesor."['events']".$this->eventos[$i]."<br>";
	
	
    if ($this->vector && sizeof($this->vector)>0 )
	 for ($i=0; $i < sizeof($this->vector); $i++)
	    $res.=$this->vector[$i]->toString($antecesor."['children'][$i]"); 
	
	return $res;
  } 

   
  function toJavascript($antecesor="")
  {
	$res='';
	if ($this->id)
	   $res.=$antecesor."['id']=".$this->id."; ";
	
	if ($this->caption)
	   $res.=$antecesor."['caption']="."'".$this->caption."'; ";


	if ($this->url)
	{
	  $res.=$antecesor."['url']="."'".$this->url."'; ";
	  $res.=$antecesor."['target']="."'".$this->target."'; ";
	}
	   

	if ($this->isOpen)
	 $bool='true';
	else 
	 $bool='false';
	 
	$res.=$antecesor."['isOpen']= ".$bool."; ";
	   
	$res.=$antecesor."['isChecked']= ".$this->isChecked."; ";

	if ( isset($this->radioButtonSelected) )
	{
	  if ($this->radioButtonSelected)
	     $bool="true";
	  else
         $bool="false";	  
      $res.=$antecesor."['radioButtonSelected']= $bool; ";
	}
	

	if ($this->icon && strlen($this->icon)>0)
	   $res.=$antecesor."['icon']= \"$this->icon\"; " ;

    if ($this->imageDir && strlen($this->imageDir)>0)
	   $res.=$antecesor."['imageDir']= \"$this->imageDir\"; " ;
	   
	
	if ($this->onClickCaption)
	  $res.=$antecesor."['onClickCaption']= '$this->onClickCaption';  ";

	
	//muestro los eventos (si tiene alguno asociado)
	if ( $this->eventos && sizeof($this->eventos)>0 ) 
	{
  	  $res.=$antecesor."['events']= new Array; ";
	  for ($i=0; $i < sizeof($this->eventos); $i++)
		 $res.=$antecesor."['events']".$this->eventos[$i];
	} 
		
    if ($this->vector && sizeof($this->vector)>0 )
	{
	   $res.="$antecesor ['children']=new Array; ";
	   for ($i=0; $i < sizeof($this->vector); $i++)
	   {
	      $res.=$antecesor."['children'][$i]= new Array;";
		  $res.=$this->vector[$i]->toJavascript($antecesor."['children'][$i]"); 
	   }
    }	 
	
	return $res;
  }
  
}  
  
?>
