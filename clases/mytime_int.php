  <?php
function setear_fecha($fecha_noticia)
{
/***************************************
**  Various Date & Time Formats INTERNATIONAL VERSION
** Copyright 2006 John Reed / www.johnhouston.net/
***************************************/


/****************************************
*  Server Date and Time
*  To be on the "safe side", it is a wise idea
*  To know what the server's clock is set at.
*  To find out:  Call the "servertime.php" file with
*  Your browser.  Then, make the necessary adjustments
*  And format choices below.
*****************************************/

   $servertime = date("h:i:s A");
   
/***************** EDIT INSTRUCTION 1 ***********************
*  Adjust "$houroffset = "??" (BELOW this block) for server offset: In -+hours
*
*  If there is a time difference between "Server Time" and your
*  Local Time, you should adjust the $houroffset number below.
*
*  If your LOCAL TIME is BEHIND the server time, the number below
*  should be a NEGATIVE number e.g.: -2
*
*  If your LOCAL TIME is AHEAD of the server time, the number below
*  should be a Positive number e.g.: +2
*
*  If your LOCAL TIME is the SAME as the server time, the number below
*  should be set at "0".. No Offset needed
*
*****************************************/

    $houroffset = "0"; //<<< INSERT HOUR OFFSET HERE

/***************** EDIT INSTRUCTION 2 ***********************
*  Which display format?
*  Enter Format Style Number at $fs = "??" BELOW this block of information
*  The Formats are:
*  Format Style No.: 1 = This format >> Monday, 21 October, 2006 8:15:45 pm
*  Format Style No.: 2 = This format >> Monday, 21 October, 2006 8:15 pm
*  Format Style No.: 3 = This format >> Monday, 21 October, 2006
*  Format Style No.: 4 = This format >> Monday, October 21, 2006
*  Format Style No.: 5 = This format >> Monday, October 21, 2006 8:15 pm
*  Format Style No.: 6 = This format >> October, 21 2006 8:15 pm
*  Format Style No.: 7 = This format >> October, 21 2006
*****************************************/

    $fs = "3"; // <<<< INSERT FORMAT STYLE NUMBER
    
/***************** EDIT INSTRUCTION 3 ***********************
*  Select the language the time/day/date should display and EDIT the
*  $language = "1" BELOW (English is Default)
*  1 = English :: 2 = French :: 3 = German :: 4 = Spanish 
*  5 = Dutch :: 6 = Italian :: 7 = Portuguese
*****************************************/
    $language = "4"; // <<<< EDIT Language Here

/****************** DO NOT EDIT BELOW THIS POINT ************************/
/****************** DO NOT EDIT BELOW THIS POINT ************************/
/**** Day Name Translations *****/
  $day = '';
  $month = '';
  /******** English ********/
  if($language == "1"):
  $day = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
  $month = array('','January','February','March','April','May','June','July','August','September','October','November','December');
  
  /******** FRENCH ********/
  elseif($language == "2"):
  $day = array('Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi');
  $month = array('','Janvier','F&eacute;vrier','Mars','Avril','Mai','Juin','Juillet','Ao&ucirc;t','Septembre','Octobre','Novembre','D&eacute;cembre');
  
  /******** German ********/
  elseif($language == "3"):
  $day = array('Sonntag', 'Montag', 'Dienstag','Mittwoch', 'Donnerstag', 'Freitag','Samstag');
  $month = array('','Januar','Februar','M&auml;rz','Avril', 'Mai','Juni','Juli','August','September','Oktober','November','Dezember');
  
  /******** Spanish ********/
  elseif($language == "4"):
  $day = array('Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado');
  $month = array('',' de Enero',' de Febrero', 'de Marzo','de Abril', 'de Mayo','de Junio', 'de Julio','de Agosto','de Septiembre', 'de Octubre',' de Noviembre',' de Diciembre');
  
  /******** Dutch ********/
  elseif($language == "5"):
  $day = array('Zondag','Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag');
  $month = array('','Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','September','Oktober','November','December');
  
  /******** Italian ********/
  elseif($language == "6"):
  $day = array('Domenica','Luned&igrave;','Marted&igrave;','Mercoled&igrave;','Gioved&igrave;','Venerd&igrave;','Sabato');
  $month = array('','Gennaio','Febbraio','Marzo','Aprile', 'Maggio','Giugno', 'Luglio','Agosto','Settembre', 'Ottobre','Novembre','Dicembre');
  
  /******** Portuguese ********/
  elseif($language == "7"):
  $day = array('Domingo','Segunda-feira','Ter&ccedil;a-feira', 'Quarta-feira','Quinta-feira','Sexta-feira','S&aacute;bado');
  $month = array('','Janeiro','Fevereiro','Mar&ccedil;o','Abril', 'Pode','Junho', 'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
  
  endif;
  
/************  Put it all together  ************/
    $offset = ($houroffset*60*60);
	
	$fecha_aux = $fecha_noticia;	
    $myday = '';
    $newday = '';
    $newmonth = '';
  $dn = array(date('w',$fecha_aux+$offset));
    list($dn) = $dn;
  $mn = array(date('n',$fecha_aux+$offset));
    list($mn) = $mn;
  
  $day = $day[$dn];
  $month = $month[$mn];
  $daynum = date("j",$fecha_aux+$offset);
  $year = date("Y",$fecha_aux+$offset);

/************ Set the time formats  ************/
    $df = '';
    $dateform = '';
    if($fs == '1'):
    $df = "h:i:s a";
    elseif($fs == '2'):
    $df = "h:i a";
    elseif($fs == '3'):
    $df = "";
    elseif($fs == '4'):
    $df = "";
    elseif($fs == '5'):
    $df = "h:i a";
    elseif($fs == '6'):
    $df = "h:i a";
    elseif($fs == '7'):
    $df = "";
    endif;

  $ctime = date($df,time()+$offset); 
 
  
    if($fs == '1'):
	$dateform = $day .',&nbsp;&nbsp;' . $daynum . '&nbsp;' . $month . ',&nbsp;' . $year . '&nbsp;&nbsp;&nbsp;' . $ctime;
	elseif($fs == '2'):
	$dateform = $day .',&nbsp;&nbsp;' . $daynum . '&nbsp;' . $month . ',&nbsp;' . $year . '&nbsp;&nbsp;&nbsp;' . $ctime;
	elseif($fs == '3'):
	$dateform = $day .',&nbsp;&nbsp;' . $daynum . '&nbsp;' . $month . ',&nbsp;' . $year . $ctime;
	elseif($fs == '4'):
	$dateform = $day .',&nbsp;&nbsp; ' . $month . '&nbsp;' . $daynum . ',&nbsp;' . $year . $ctime;
	elseif($fs == '5'):
	$dateform = $day .',&nbsp;&nbsp;' . $month . '&nbsp;' . $daynum . ',&nbsp;' . $year . '&nbsp;&nbsp;&nbsp;' . $ctime;
	elseif($fs == '6'):
	$dateform = $month . '&nbsp;' . $daynum . ',&nbsp;' . $year . '&nbsp;&nbsp;&nbsp;' . $ctime;
	elseif($fs == '7'):
	$dateform = $month . '&nbsp;' . $daynum . ',&nbsp;' . $year . '&nbsp;&nbsp;&nbsp;' . $ctime;
	endif;
	
	return $dateform;
 
 } 
?>
