<?php
class convertir {
    function fecha_sumar_minutos($FechaStr, $MinASumar) {
        // $FechaStr = '2014-06-12 09:00:00';
        // MinASumar = 20; // 120, 180
        $FechaStr = str_replace("-", " ", $FechaStr);
        $FechaStr = str_replace(":", " ", $FechaStr);
        $FechaOrigen = explode(" ", $FechaStr);

        $Dia = $FechaOrigen[2];
        $Mes = $FechaOrigen[1];
        $Ano = $FechaOrigen[0];

        $Horas = $FechaOrigen[3];
        $Minutos = $FechaOrigen[4];
        $Segundos = $FechaOrigen[5];

        // Sumo los minutos
        $Minutos = ((int) $Minutos) - ((int) $MinASumar);

        // Asigno la fecha modificada a una nueva variable
        $FechaNueva = date("Y-m-d H:i:s", mktime($Horas, $Minutos, $Segundos, $Mes, $Dia, $Ano));

        return $FechaNueva;
    }

    function restar_fechas($startDate, $endDate) {
        list($year, $month, $day) = explode('-', $startDate);
        $startDate = mktime(0, 0, 0, $month, $day, $year);
        list($year, $month, $day) = explode('-', $endDate);
        $endDate = mktime(0, 0, 0, $month, $day, $year);
        $totalDays = ($endDate - $startDate) / (60 * 60 * 24);
        return $totalDays;
    }

    function get_fecha_latina($fecha) {
        preg_match('/' . "([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})" . '/i', $fecha, $mifecha);
        $lafecha = $mifecha[3] . "/" . $mifecha[2] . "/" . $mifecha[1];
        return $lafecha;
    }

    function get_fecha_latina_baja($fecha) {
        ereg("([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $fecha, $mifecha);

        if ($mifecha[3] < 10) {
            $mifecha[3] = $mifecha[3] * 1;
        }

        if ($mifecha[2] < 10) {
            $mifecha[2] = $mifecha[2] * 1;
        }

        $lafecha = $mifecha[3] . "/" . $mifecha[2] . "/" . $mifecha[1];
        return $lafecha;
    }

    function get_fecha_mysql($fecha) {
        ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $fecha, $mifecha);
        $lafecha = $mifecha[3] . "-" . $mifecha[2] . "-" . $mifecha[1];
        return $lafecha;
    }

    function calcular_edad($fechanacimiento) {
        list($ano, $mes, $dia) = explode("-", $fechanacimiento);
        $ano_diferencia = date("Y") - $ano;
        $mes_diferencia = date("m") - $mes;
        $dia_diferencia = date("d") - $dia;
        if ($dia_diferencia < 0 || $mes_diferencia < 0)
            $ano_diferencia--;
        return $ano_diferencia;
    }

    function tiempo_pasado($from, $to = null) {
        $to = (( $to === null ) ? ( time()) : ( $to ));
        $to = (( is_int($to)) ? ( $to ) : ( strtotime($to)));
        $from = (( is_int($from)) ? ( $from ) : ( strtotime($from)));

        $units = array
            (
            "a�o" => 29030400, // seconds in a year (12 months)
            "mes" => 2419200, // seconds in a month (4 weeks)
            "semana" => 604800, // seconds in a week (7 days)
            "dia" => 86400, // seconds in a day (24 hours)
            "hora" => 3600, // seconds in an hour (60 minutes)
            "minuto" => 60, // seconds in a minute (60 seconds)
            "segundo" => 1 // 1 second
        );

        $diff = abs($from - $to);
        $suffix = (( $from > $to ) ? ( "" ) : ( "" ));

        foreach ($units as $unit => $mult)
            if ($diff >= $mult) {
                $and = (( $mult != 1 ) ? ( "" ) : ( " " ));
                $output .= ", " . $and . intval($diff / $mult) . " " . $unit . (( intval($diff / $mult) == 1 ) ? ( "" ) : ( "s" ));
                $diff -= intval($diff / $mult) * $mult;
            }
        $output .= " " . $suffix;

        $output = substr($output, strlen(", "));

        $marray = explode(',', $output);

        $cad = $marray[0] . ', ' . $marray[1];

        return ereg_replace(" mess", " meses", $cad);
    }

    function tiempo_transcurrido($fecha) {
        if (empty($fecha)) {
            return "No hay fecha";
        }
        $intervalos = array("segundo", "minuto", "hora", "d�a", "semana", "mes", "a�o");
        $duraciones = array("60", "60", "24", "7", "4.35", "12");

        $ahora = time();
        $Fecha_Unix = strtotime($fecha);

        if (empty($Fecha_Unix)) {
            return "Fecha incorracta";
        }
        if ($ahora > $Fecha_Unix) {
            $diferencia = $ahora - $Fecha_Unix;
            $tiempo = "Hace";
        } else {
            $diferencia = $Fecha_Unix - $ahora;
            $tiempo = "Hace";
        }
        for ($j = 0; $diferencia >= $duraciones[$j] && $j < count($duraciones) - 1; $j++) {
            $diferencia /= $duraciones[$j];
        }

        $diferencia = round($diferencia);

        if ($diferencia != 1) {
            $intervalos[5].="e"; //MESES
            $intervalos[$j].= "s";
        }

        return "$tiempo $diferencia $intervalos[$j]";
    }

    function fecha_literal() {
        $week_days = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado");
        $months = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $year_now = date("Y");
        $month_now = date("n");
        $day_now = date("j");
        $week_day_now = date("w");
        $date = $week_days[$week_day_now] . ", " . $day_now . " de " . $months[$month_now] . " de " . $year_now;
        return $date;
    }
            
    function get_fecha_larga($auxFecha, $tipus = 1) {
        
        $data = strtotime($auxFecha);
        if ($data != '' && $tipus == 0 || $tipus == 1) { 
            $setmana = array('Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'); 
            $mes = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

            if ($tipus == 1) {
                ereg('([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})', $data, $data);
                $data = mktime(0, 0, 0, $data[2], $data[1], $data[3]);
            }

            return $setmana[date('w', $data)] . ', ' . date('d', $data) . ' de ' . $mes[date('m', $data) - 1] . ' del ' . date('Y', $data);
        } else {
            return 0;
        }
    }

    function get_mes($mes) {
        switch ($mes) {
            case '01': {
                    return 'ENERO';
                    break;
                }
            case '02': {
                    return 'FEBRERO';
                    break;
                }
            case '03': {
                    return 'MARZO';
                    break;
                }
            case '04': {
                    return 'ABRIL';
                    break;
                }
            case '05': {
                    return 'MAYO';
                    break;
                }
            case '06': {
                    return 'JUNIO';
                    break;
                }
            case '07': {
                    return 'JULIO';
                    break;
                }
            case '08': {
                    return 'AGOSTO';
                    break;
                }
            case '09': {
                    return 'SEPTIEMBRE';
                    break;
                }
            case '10': {
                    return 'OCTUBRE';
                    break;
                }
            case '11': {
                    return 'NOVIEMBRE';
                    break;
                }
            case '12': {
                    return 'DICIEMBRE';
                    break;
                }
        }
    }

    function sumar_restar_fechas($tipo, $dias, $fecha_actual) { //$fecha_actual -> formato YYYY-MM-DD
        $signo = '';
        if ($tipo == 'suma')
            $signo = '+';
        if ($tipo == 'resta')
            $signo = '-';

        $fecha = $fecha_actual;
        $nuevafecha = strtotime($signo . $dias . ' day', strtotime($fecha));
        $nuevafecha = date('Y-m-d', $nuevafecha);
        return $nuevafecha;
    }
	
    function setear_fecha($fecha) {
        $fecha_noticia = strtotime($fecha); 
        $servertime = date("h:i:s A");
        $houroffset = "0"; //<<< INSERT HOUR OFFSET HERE
        $fs = "3"; // <<<< INSERT FORMAT STYLE NUMBER
        $language = "4"; // <<<< EDIT Language Here

        /*         * ** Day Name Translations **** */
        $day = '';
        $month = '';
        /*         * ****** English ******* */
        if ($language == "1"):
            $day = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
            $month = array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

        /*         * ****** FRENCH ******* */
        elseif ($language == "2"):
            $day = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
            $month = array('', 'Janvier', 'F&eacute;vrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Ao&ucirc;t', 'Septembre', 'Octobre', 'Novembre', 'D&eacute;cembre');

        /*         * ****** German ******* */
        elseif ($language == "3"):
            $day = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');
            $month = array('', 'Januar', 'Februar', 'M&auml;rz', 'Avril', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');

        /*         * ****** Spanish ******* */
        elseif ($language == "4"):
            $day = array('Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado');
            $month = array('', ' de Enero', ' de Febrero', 'de Marzo', 'de Abril', 'de Mayo', 'de Junio', 'de Julio', 'de Agosto', 'de Septiembre', 'de Octubre', ' de Noviembre', ' de Diciembre');

        /*         * ****** Dutch ******* */
        elseif ($language == "5"):
            $day = array('Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag');
            $month = array('', 'Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December');

        /*         * ****** Italian ******* */
        elseif ($language == "6"):
            $day = array('Domenica', 'Luned&igrave;', 'Marted&igrave;', 'Mercoled&igrave;', 'Gioved&igrave;', 'Venerd&igrave;', 'Sabato');
            $month = array('', 'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');

        /*         * ****** Portuguese ******* */
        elseif ($language == "7"):
            $day = array('Domingo', 'Segunda-feira', 'Ter&ccedil;a-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'S&aacute;bado');
            $month = array('', 'Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Pode', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

        endif;

        /*         * **********  Put it all together  *********** */
        $offset = ($houroffset * 60 * 60);

        $fecha_aux = $fecha_noticia;
        $myday = '';
        $newday = '';
        $newmonth = '';
        $dn = array(date('w', $fecha_aux + $offset));
        list($dn) = $dn;
        $mn = array(date('n', $fecha_aux + $offset));
        list($mn) = $mn;

        $day = $day[$dn];
        $month = $month[$mn];
        $daynum = date("j", $fecha_aux + $offset);
        $year = date("Y", $fecha_aux + $offset);

        /*         * ********** Set the time formats  *********** */
        $df = '';
        $dateform = '';
        if ($fs == '1'):
            $df = "h:i:s a";
        elseif ($fs == '2'):
            $df = "h:i a";
        elseif ($fs == '3'):
            $df = "";
        elseif ($fs == '4'):
            $df = "";
        elseif ($fs == '5'):
            $df = "h:i a";
        elseif ($fs == '6'):
            $df = "h:i a";
        elseif ($fs == '7'):
            $df = "";
        endif;

        $ctime = date($df, time() + $offset);


        if ($fs == '1'): 
            $dateform = $day . ',&nbsp;&nbsp;' . $daynum . '&nbsp;' . $month . ',&nbsp;' . $year . '&nbsp;&nbsp;&nbsp;' . $ctime;
        elseif ($fs == '2'):
            $dateform = $day . ',&nbsp;&nbsp;' . $daynum . '&nbsp;' . $month . ',&nbsp;' . $year . '&nbsp;&nbsp;&nbsp;' . $ctime;
        elseif ($fs == '3'):
            $dateform = $day . ',&nbsp;&nbsp;' . $daynum . '&nbsp;' . $month . ',&nbsp;' . $year . $ctime;
        elseif ($fs == '4'):
            $dateform = $day . ',&nbsp;&nbsp; ' . $month . '&nbsp;' . $daynum . ',&nbsp;' . $year . $ctime;
        elseif ($fs == '5'):
            $dateform = $day . ',&nbsp;&nbsp;' . $month . '&nbsp;' . $daynum . ',&nbsp;' . $year . '&nbsp;&nbsp;&nbsp;' . $ctime;
        elseif ($fs == '6'):
            $dateform = $month . '&nbsp;' . $daynum . ',&nbsp;' . $year . '&nbsp;&nbsp;&nbsp;' . $ctime;
        elseif ($fs == '7'):
            $dateform = $month . '&nbsp;' . $daynum . ',&nbsp;' . $year . '&nbsp;&nbsp;&nbsp;' . $ctime;
        endif;

        return $dateform;
    }
}

?>