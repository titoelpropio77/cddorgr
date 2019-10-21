<?php

class convertir

{

	function get_fecha_latina($fecha)
	{

    	ereg( "([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $fecha, $mifecha);

    	$lafecha=$mifecha[3]."/".$mifecha[2]."/".$mifecha[1];

    	return $lafecha;

	} 

	function restar_fechas($startDate,$endDate)
	{ 
		list($year, $month, $day) = explode('-', $startDate);  
		$startDate = mktime(0, 0, 0, $month, $day, $year);  
		list($year, $month, $day) = explode('-', $endDate);  
		$endDate = mktime(0, 0, 0, $month, $day, $year);  
		$totalDays = ($endDate - $startDate)/(60 * 60 * 24) ;  
		return $totalDays; 	
	}

	function get_fecha_latina_baja($fecha)

	{

    	ereg( "([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $fecha, $mifecha);

    	

    	if($mifecha[3]<10)

    	{

    		$mifecha[3]=$mifecha[3]*1;

    	}

    	

    	if($mifecha[2]<10)

    	{

    		$mifecha[2]=$mifecha[2]*1;

    	}

    	

    	$lafecha=$mifecha[3]."/".$mifecha[2]."/".$mifecha[1];

    	return $lafecha;

	}

	

	function get_fecha_mysql($fecha)

	{

    	ereg( "([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $fecha, $mifecha);

    	$lafecha=$mifecha[3]."-".$mifecha[2]."-".$mifecha[1];

    	return $lafecha;

	} 

	

	function get_fecha_larga($data, $tipus=1)

	{

		  if ($data != '' && $tipus == 0 || $tipus == 1)

		  {

			   $setmana = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');

			   $mes = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'); 

			

			   if ($tipus == 1)

			   {

			      ereg('([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})', $data, $data);

			      $data = mktime(0,0,0,$data[2],$data[1],$data[3]);

			   } 

			

			   return $setmana[date('w', $data)].', '.date('d', $data).' de '.$mes[date('m',$data)-1].' del '.date('Y', $data);

		  }

		  else

		  {

		   	 return 0;

		  }

	}

	

	function get_mes($mes)

	{

		switch ($mes)

		{

			case '01':{return 'ENERO';break;}

			case '02':{return 'FEBRERO';break;}

			case '03':{return 'MARZO';break;}

			case '04':{return 'ABRIL';break;}

			case '05':{return 'MAYO';break;}

			case '06':{return 'JUNIO';break;}

			case '07':{return 'JULIO';break;}

			case '08':{return 'AGOSTO';break;}

			case '09':{return 'SEPTIEMBRE';break;}

			case '10':{return 'OCTUBRE';break;}

			case '11':{return 'NOVIEMBRE';break;}

			case '12':{return 'DICIEMBRE';break;}

			

		}

	}
	
	function validar_fecha($input,$format="")
    {
        $separator_type= array(
           /* 
		    "/",
            "-",
            "."
			*/
			"/"
        );
        foreach ($separator_type as $separator) {
            $find= stripos($input,$separator);
            if($find<>false){
                $separator_used= $separator;
            }
        }
        $input_array= explode($separator_used,$input);
        if ($format=="mdy") {
            return checkdate($input_array[0],$input_array[1],$input_array[2]);
        } elseif ($format=="ymd") {
            return checkdate($input_array[1],$input_array[2],$input_array[0]);
        } else {
            return checkdate($input_array[1],$input_array[0],$input_array[2]);
        }
        $input_array=array();
    }


}

?>