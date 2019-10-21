<?php

class Ticket {

//    static $ticketera;

    function Ticket() {
        
    }

    public static function pedirTicket() {
        $i = microtime();
        $id = 'admin';
        $momento = date('YmdHis');
        $ms=  substr($i, 2, 6);
        return $id . $momento. $ms;
    }

    public static function fueAtendido($ticket) {

        $b = false;

        if (isset($_SESSION['tickets'])) {
            $ticketera = $_SESSION['tickets'];
        } else {
            $ticketera = array();
        }


        if (in_array($ticket, $ticketera)) {
            $b = true;
        } else {
            $ticketera[] = $ticket;
            $_SESSION['tickets'] = $ticketera;
        }

        return $b;
    }

}

?>
