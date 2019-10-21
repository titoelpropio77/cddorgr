<?php

 
  function llenarNivel(&$nodo,$vector,$CodGrupo,$vector_aux)

  {	

	//$consulta = "select id,nombre,id_padre from categoria where id_padre = '".$CodGrupo."'";	

	

	//$rs = $cnx->consulta($consulta);   

        $hijos = obtener_hijos($vector,$CodGrupo);

		$cantidad = count($hijos);		$i = 0;

		while ($i < $cantidad)

		{ 

		  $fila = obtener_fila($hijos,$i) ;

		  $aux_nodo=new TNodo(); 

	      if (is_array($vector_aux))

          {

		       if (existe($fila["id"],$vector_aux))

			   {

			      $aux_nodo->isChecked= 1;		   

			   }	  

		  }		

		 // $aux_nodo->isChecked=1;

		  $aux_nodo->caption=$fila['nombre'];	

		  //$var ="'£".$fila['CodOpcion']."Ø".$fila['Descripcion']."Ø".$fila['CodPadre']."£'";

		  $aux_nodo->id = $fila["id"];

		 // $aux_nodo->addEvento_onClickCaption("ColocarDatos($aux_nodo->id);");			  	  		  

		  llenarNivel($aux_nodo,$vector,$fila['id'],$vector_aux);	  

		  $nodo->addNodo($aux_nodo);

		   $i++;

		}	

	

  }

  

  

  

  function llenarPrincipal(&$arbol,&$cnx,$vector_aux)

  {     

   // $ss = obtener_frases($id);

    $vector = cargar_vector($cnx);

	$padres = obtener_hijos($vector,0);

	

	$nodoGrupo=new TNodo();      

	$nodoGrupo->caption="CATEGORIAS";

	$nodoGrupo->id = "'0'";

	
	$nodoGrupo->isChecked= 0;
	
	
	
	$nodoGrupo->isOpen = "true";

	//$nodoGrupo->icon = 'grupos.gif'; 

//	$nodoGrupo->addEvento_onClickCaption("ColocarDatos($nodoGrupo->id);");	



        $cantidad = count($padres);		$i = 0;

		while ($i < $cantidad)

		{  

		  $fila = obtener_fila($padres,$i) ;

		  $nodo=new TNodo();  

		  $nodo->caption=$fila['nombre'];

          if (is_array($vector_aux))

          {

		       if (existe($fila["id"],$vector_aux))

			   {

			      $nodo->isChecked= 1;		   

			   }	  

		  }		  

         		  

		  //$nodo->isChecked= 1;

		  //$var ="'£".$fila['CodOpcion']."Ø".$fila['Descripcion']."Ø".$fila['CodPadre']."£'";

		  $nodo->id= $fila["id"];	  		  

  		  //$nodo->addEvento_onClickCaption("ColocarDatos($nodo->id);");  							 

		  llenarNivel($nodo,$vector, $fila["id"],$vector_aux);//llena los hijos que tiene ese nodo

		  $nodoGrupo->addNodo($nodo);    

		  $i++;

		}

	  $arbol->addNodo($nodoGrupo);

    //  retornar_consulta($Matriz);	

  }  

  

  

  

  function obtener_hijos($vector,$id)

  {

      $vector_padre=array();

	  $c = 0;

	  for ($i=0; $i< count($vector); $i++)

	  {

	      if ($vector[$i]['id_padre'] == $id)

		  {

	         $vector_padre[$c]['id'] = $vector[$i]['id'];

		     $vector_padre[$c]['nombre']=$vector[$i]['nombre'];

		     $vector_padre[$c]['id_padre']=$vector[$i]['id_padre'];	

             $c++;			 

		  }

      } 

	  return $vector_padre; 	 

  } 



  

  

  function obtener_fila($vector,$fila)

  {

      return $vector[$fila];

  

  }

  

  function obtener_tabla($id,&$conec, $tabla, $campo,$seleccion)
  {

      $vector=array();

      $consulta = "select $seleccion as elemento from $tabla where $campo='".$id."'";	

	  $conec->ejecutar($consulta);

      $numero = $conec->get_num_registros();   

	   for($i=0;$i<$numero;$i++)
		{

	      $objeto=$conec->get_objeto();
		  
		  $vector[$i] = $objeto->elemento;		  

		$conec->siguiente();

      } 

	  return $vector;   

  }
  
  function cargar_vector(&$conec)

  {

      $vector=array();

      $consulta = "select cat_id,cat_nombre,cat_padre from cm_categoria";	

	  $conec->ejecutar($consulta);

      $numero = $conec->get_num_registros();  

	  for($i=0;$i<$numero;$i++)
		{
			
			$objeto=$conec->get_objeto();
			
			$vector[$i]['id'] = $objeto->cat_id;

		    $vector[$i]['nombre'] = $objeto->cat_nombre;

		    $vector[$i]['id_padre'] = $objeto->cat_padre;	
			
			$conec->siguiente();
		}
	  
	 

	  return $vector; 

  }

  

  function existe($id,$vector)

  {

      $sw = false;

      for ($i = 0; $i < count($vector) ; $i++)

	  {

	       if ($vector[$i] == $id)

		   {

		      $sw = true;

		   }

	  }

     return $sw;  

  }

  

  

//***************************************************************************************************************    

?>