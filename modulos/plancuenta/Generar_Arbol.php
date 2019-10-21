<?php
  include_once('clases/TNodo.php');
  include_once('clases/TArbol.php');  
 
  
    function Es_Padre($Id)
	{
	    $con=new ADO();		
		$sql="SELECT cue_id FROM cuenta WHERE cue_padre_id=".$Id;					 	 	 	 	
		$con->ejecutar($sql);	
		$numero = $con->get_num_registros();
	 
	  if ($numero>0)
	   { 		   
		   return true;
	   }
	   else
	   {		  
		  return false;		   
	   } 	  
	}
  
  function llenarNivel(&$nodo,$idarbol,$NivelConfig)
  {	
    $con=new ADO();		
	$sql="SELECT cue_id,cue_numero,cue_nivel, cue_descripcion,cue_padre_id,cue_tcu_id FROM cuenta WHERE cue_padre_id=".$idarbol." ORDER by cue_numero";					 	 	 	 	
	$con->ejecutar($sql);	
	$num = $con->get_num_registros();

    for($i=0;$i<$num;$i++)
	{ 
		$objeto=$con->get_objeto();	
		
			  $aux_nodo=new TNodo();  
		      $aux_nodo->caption=$objeto->cue_numero." ".$objeto->cue_descripcion;	// el que pone los capcion a los hijos
			  $var ="'".$objeto->cue_id."Ø".$objeto->cue_numero."Ø".$objeto->cue_descripcion."Ø".$objeto->cue_padre_id."Ø".$objeto->cue_nivel."Ø".$objeto->cue_tcu_id."'";
			  $aux_nodo->id = $var;
			//  $aux_nodo->isOpen = "true";
			  $aux_nodo->addEvento_onClickCaption("ColocarDatos($aux_nodo->id);");	  //EL QUE PONE A LOS EDITS
			  
			   if  (($objeto->cue_nivel < $NivelConfig)  && !(Es_Padre($objeto->cue_id)))	        
			       $aux_nodo->icon = '_folder.gif'; 
			  
			  
			  
			  llenarNivel($aux_nodo,$objeto->cue_id,$NivelConfig);	  
			
		      $nodo->addNodo($aux_nodo);
		$con->siguiente();
	}
  }
  
  
  function llenarPrincipal(&$arbol,$NivelConfig)
  {
    $nodoGrupo=new TNodo();      
	$nodoGrupo->caption="PLAN DE CUENTAS";
	$nodoGrupo->id = "'0'";
	$nodoGrupo->isOpen = "true";
	$nodoGrupo->icon = 'grupos.gif'; 
	$nodoGrupo->addEvento_onClickCaption("ColocarPadre($nodoGrupo->id);");
  
     $con=new ADO();		
	 $sql="SELECT cue_id,cue_numero,cue_nivel, cue_descripcion,cue_padre_id,cue_tcu_id FROM cuenta WHERE cue_padre_id=0"." ORDER by cue_numero";;					 	 	 	 	
	 $con->ejecutar($sql);	
	 
	 $num = $con->get_num_registros(); 
   
    
	 
	 
      	 
	 for($i=0;$i<$num;$i++)
	 { 
		$objeto=$con->get_objeto();				
		$nodo=new TNodo();  
	    $nodo->caption=$objeto->cue_numero." ".$objeto->cue_descripcion;	// el que pone los capcion a los hijos
	    $var ="'".$objeto->cue_id."Ø".$objeto->cue_numero."Ø".$objeto->cue_descripcion."Ø".$objeto->cue_padre_id."Ø".$objeto->cue_nivel."Ø".$objeto->cue_tcu_id."'";
	    $nodo->id = $var;
		//$nodo->isOpen = "true";
		//echo $nodo->id."<br>";
	    $nodo->addEvento_onClickCaption("ColocarDatos($nodo->id);");	  //EL QUE PONE A LOS EDITS

		if  (($objeto->cue_nivel < $NivelConfig)  && !(Es_Padre($objeto->cue_id)))	        
			       $nodo->icon = '_folder.gif'; 
		
		llenarNivel($nodo,$objeto->cue_id,$NivelConfig);
		$nodoGrupo->addNodo($nodo);
        $con->siguiente();
     }
  	$arbol->addNodo($nodoGrupo);
	
  }  
//***************************************************************************************************************  
  
 
?>