<?php

class generar_control {

    function generar($factura, $nit_cli, $fecha, $monto, $dosificacion, $autorizacion) {//        
        $monto=  round($monto);
        $fecha=str_replace('/', '', $fecha);
        $digitos = $this->paso1($factura, $nit_cli, $fecha, $monto);
        $cad_concat = $this->paso2($factura, $nit_cli, $fecha, $monto, $dosificacion, $autorizacion, $digitos);        
        $arc4 = $this->paso3($digitos, $cad_concat, $dosificacion);                
        $total = $this->paso4($arc4, $digitos);
        $mensaje = $this->big_base_convert($total);
        $cc=$this->allegedrc4($mensaje, $dosificacion . $digitos);
        $i=0;
        $codigo="";
        while ($i<  strlen($cc)){
            if($i!=0){
                $codigo.='-';
            }
            $codigo.=$cc[$i].$cc[$i+1];
            $i+=2;
        }
        return $codigo;
    }

    function paso1(&$factura, &$nit_cli, &$fecha, &$monto) {
        $vhf = new algverhoeff();
        $factura = $vhf->verhoeff_add_recursive($factura, 2);
        $nit_cli = $vhf->verhoeff_add_recursive($nit_cli, 2);
        $fecha = $vhf->verhoeff_add_recursive($fecha, 2);
        $monto = $vhf->verhoeff_add_recursive($monto, 2);

//        $suma = bcadd(bcadd(bcadd($factura, $nit_cli), $fecha), $monto);        
        $suma = $factura + $nit_cli + $fecha + $monto;
        $suma = $vhf->verhoeff_add_recursive($suma, 5);
        $digitos = "" . substr($suma, -5);
        return $digitos;
    }

    function paso2($factura, $nit_cli, $fecha, $monto, $dosificacion, $autorizacion, $digitos) {
        $nd = array($digitos[0] + 1, $digitos[1] + 1, $digitos[2] + 1, $digitos[3] + 1, $digitos[4] + 1);
        $cad = array(
            '1' => substr($dosificacion, 0, $nd[0]),
            '2' => substr($dosificacion, $nd[0], $nd[1]),
            '3' => substr($dosificacion, $nd[0] + $nd[1], $nd[2]),
            '4' => substr($dosificacion, $nd[0] + $nd[1] + $nd[2], $nd[3]),
            '5' => substr($dosificacion, $nd[0] + $nd[1] + $nd[2] + $nd[3], $nd[4]),
        );
        $objeto = array(
            'autorizacion' => $autorizacion . $cad[1],
            'factura' => $factura . $cad[2],
            'nit_cli' => $nit_cli . $cad[3],
            'fecha' => $fecha . $cad[4],
            'monto' => $monto . $cad[5]
        );        
        return implode('', $objeto);
    }

    function paso3($digitos, $cad_concat, $dosificacion) {        
        $allegedrc4=  $this->allegedrc4($cad_concat, $dosificacion.$digitos);
        return $allegedrc4;
    }
    
    function paso4($arc4,$digitos){        
        $suma_total = 0;
        $sumas = array_fill(0, 5, 0);
        $strlen_arc4 = strlen($arc4);
        for ($i = 0; $i < $strlen_arc4; $i++) {
            $x = ord($arc4[$i]);
            $sumas[$i % 5] += $x;
            $suma_total += $x;
        }
        $total = "0";
        foreach ($sumas as $i => $sp) {
            $total +=  (int)(($suma_total*$sp)/($digitos[$i]+1));
        }
        return $total;
        
    }
    function allegedrc4($mensaje, $llaverc4) {
        $state = array();
        $x = 0;
        $y = 0;
        $index1 = 0;
        $index2 = 0;
        $nmen = 0;
        $i = 0;
        $cifrado = "";

        $state = range(0, 255);

        $strlen_llave = strlen($llaverc4);
        $strlen_mensaje = strlen($mensaje);
        for ($i = 0; $i < 256; $i++) {
            $index2 = ( ord($llaverc4[$index1]) + $state[$i] + $index2 ) % 256;
            list($state[$i], $state[$index2]) = array($state[$index2], $state[$i]);
            $index1 = ($index1 + 1) % $strlen_llave;
        }
        for ($i = 0; $i < $strlen_mensaje; $i++) {
            $x = ($x + 1) % 256;
            $y = ($state[$x] + $y) % 256;
            list($state[$x], $state[$y]) = array($state[$y], $state[$x]);
            // ^ = XOR function
            $nmen = ord($mensaje[$i]) ^ $state[( $state[$x] + $state[$y] ) % 256];
            $cifrado .= substr("0" . $this->big_base_convert($nmen, "16"), -2);
        }
        return $cifrado;
    }
    private function big_base_convert($numero, $base = "64") {
        $dic = array(
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
            'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd',
            'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',
            'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
            'y', 'z', '+', '/');
        $cociente = "1";
        $resto = "";
        $palabra = "";
		$c = 0;
        while ($cociente > 0) {
            $cociente = intval($numero / $base);
            $resto = $numero % $base;
            $palabra = $dic[0 + $resto] . $palabra;
            $numero = "" . $cociente;
        }
        
//        while (bccomp($cociente, "0")) {
//            $cociente = bcdiv($numero, $base);
//            $resto = bcmod($numero, $base);
//            $palabra = $dic[0 + $resto] . $palabra;
//            $numero = "" . $cociente;
//        }
        return $palabra;
    }

}

class algverhoeff {

    var $table_d = array(
        array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
        array(1, 2, 3, 4, 0, 6, 7, 8, 9, 5),
        array(2, 3, 4, 0, 1, 7, 8, 9, 5, 6),
        array(3, 4, 0, 1, 2, 8, 9, 5, 6, 7),
        array(4, 0, 1, 2, 3, 9, 5, 6, 7, 8),
        array(5, 9, 8, 7, 6, 0, 4, 3, 2, 1),
        array(6, 5, 9, 8, 7, 1, 0, 4, 3, 2),
        array(7, 6, 5, 9, 8, 2, 1, 0, 4, 3),
        array(8, 7, 6, 5, 9, 3, 2, 1, 0, 4),
        array(9, 8, 7, 6, 5, 4, 3, 2, 1, 0),
    );
    var $table_p = array(
        array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
        array(1, 5, 7, 6, 2, 8, 3, 0, 9, 4),
        array(5, 8, 0, 3, 7, 9, 6, 1, 4, 2),
        array(8, 9, 1, 6, 0, 4, 3, 5, 2, 7),
        array(9, 4, 5, 3, 1, 2, 6, 8, 7, 0),
        array(4, 2, 8, 6, 5, 7, 3, 9, 0, 1),
        array(2, 7, 9, 3, 8, 0, 6, 4, 1, 5),
        array(7, 0, 4, 6, 9, 1, 3, 2, 5, 8),
    );
    var $table_inv = array(0, 4, 3, 2, 1, 5, 6, 7, 8, 9);

    function calcsum($number) {
        $c = 0;
        $n = strrev($number);

        $len = strlen($n);
        for ($i = 0; $i < $len; $i++) {
            $c = $this->table_d[$c][$this->table_p[($i + 1) % 8][$n[$i]]];
        }

        return $this->table_inv[$c];
    }

    function verhoeff_add_recursive($number, $digits) {
        $temp = $number;
        while ($digits > 0) {
            $temp .= $this->calcsum($temp);
            $digits--;
        }
        return $temp;
    }

}

class algbase64 {
    
}

?>
