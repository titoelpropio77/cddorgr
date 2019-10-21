<?php

class MIRED {

    var $usuDatos;
    var $encript;
    var $vdo_id;

    function MIRED() {
        $this->encript = new Encryption();
        $this->usuDatos = explode("|", $this->encript->decode($_GET['token']));
        //$this->vdo_id = 1;
        $this->vdo_id = $this->vendedor_id();
    }

    function listar() {
        return $this->tree_vendedores($this->vdo_id, 7);
    }

    function get_comisiones($interno_id, $vendedor_id) {
        $sql_comision = "select com_porcentaje from comision
			inner join venta on (ven_id=com_ven_id)
			where ven_int_id='$interno_id' and com_vdo_id ='$vendedor_id' and com_estado !='Anulado'";
        $result = mysql_query($sql_comision);
        $registros = mysql_num_rows($result);
        $datos = array();
        for ($i = 0; $i < $registros; $i++) {
            $comision = mysql_fetch_object($result);
            $datos[] = ($comision->com_porcentaje * 1);
        }
        return implode('%, ', $datos);
    }

    function tree_vendedores($padre_id, $nivel_limite = 0, $nivel = 0) {
        if ($padre_id > 0) {
            $vendedor = FUNCIONES::objeto_bd_sql("select int_nombre, 
                                                        int_apellido, int_id, 
                                                        vendedor.* from vendedor, interno where vdo_id='$padre_id' and vdo_int_id=int_id");
            // $nombre=  explode(' ', $vendedor->int_nombre);
            // $apellido=  explode(' ', $vendedor->int_apellido);
            $nombre = $vendedor->int_nombre;
            $apellido = $vendedor->int_apellido;
            $porcentaje = $this->get_comisiones($vendedor->int_id, $this->vdo_id);
            $txt_porc = '';
            if ($porcentaje) {
                $txt_porc = " ($porcentaje%)";
            }
            // $name= $this->limpiar_cadena($nombre[0]).' '.$this->limpiar_cadena($apellido[0]) . $txt_porc;
            $name = $this->limpiar_cadena($nombre) . ' ' . $this->limpiar_cadena($apellido) . "($vendedor->vdo_nivel-$vendedor->vdo_id)";
        } else {
            $vendedor = null;
            $name = 'Raiz';
        }
        $tree = new stdClass();
        $tree->name = utf8_encode($name);

        $hijosSql = "select * from vendedor where vdo_vendedor_id='$padre_id'";
        $result = mysql_query($hijosSql);
        $registros = mysql_num_rows($result);
        if ($registros > 0) {
            $children = array();
            for ($i = 0; $i < $registros; $i++) {
                $_vendedor = mysql_fetch_object($result);
                if ($nivel_limite == 0 || $nivel < $nivel_limite) {
                    $children[] = $this->tree_vendedores($_vendedor->vdo_id, $nivel_limite, $nivel + 1);
                }
            }
            $tree->children = $children;
        } else {

            $tree->size = 0;
        }
        return $tree;
    }

    /*function objeto_bd_sql($sql) {
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            return mysql_fetch_object($result);
        } else {
            return null;
        }
    }*/

    function hijos($linea) {
                        
        $vdo_id = $this->vendedor_id();
        
        if ($vdo_id <= 0) {
            return false;
        }
        
        $sql = "select vdo_nivel as campo from vendedor where vdo_id='$vdo_id'";
//        echo $sql;
        $nivel_raiz = FUNCIONES::atributo_bd_sql($sql);
        $nivel = 0;
        $hijos = $this->obtener_red($vdo_id, $nivel, FALSE, $nivel_raiz + 7);
        
        $hijos1 = array();
        $hijos2 = array();
        $hijos3 = array();
        $hijos4 = array();
        $hijos5 = array();
        $hijos6 = array();
        $hijos7 = array();
                
        $todos = new stdClass();
        for ($i = 0; $i < count($hijos); $i++) {
            
            $hijo = $hijos[$i];
            $ele = FUNCIONES::objeto_bd_sql("select convert(concat(int_nombre,' ',int_apellido)using utf8)as nombre,
                int_telefono as telefono,int_celular as celular,
                int_fecha_nacimiento as fecha_nac,
                int_email as email,usu_id as login,vdo_venta_inicial as codigo_venta 
                from interno 
                inner join vendedor on (int_id=vdo_int_id)
                inner join ad_usuario on (usu_per_id=vdo_int_id)
                where int_id='$hijo->vdo_int_id' and vdo_id='$hijo->vdo_id'
                and usu_gru_id='AFILIADOS' and vdo_estado='Habilitado'");            
            
            $elem = new stdClass();
            $elem->nombre = html_encode($ele->nombre);
            $elem->telefono = $ele->telefono;
            $elem->celular = $ele->celular;
            $elem->fecha_nac = $ele->fecha_nac;
            $elem->email = html_encode($ele->email);
            $elem->login = html_encode($ele->login);
            $elem->codigo_venta = $ele->codigo_venta;
            
            if (($hijo->vdo_nivel - $nivel_raiz) == 1) {
                $hijos1[] = $elem;
            } else if (($hijo->vdo_nivel - $nivel_raiz) == 2) {
                $hijos2[] = $elem;
            } else if (($hijo->vdo_nivel - $nivel_raiz) == 3) {
                $hijos3[] = $elem;
            } else if (($hijo->vdo_nivel - $nivel_raiz) == 4) {
                $hijos4[] = $elem;
            } else if (($hijo->vdo_nivel - $nivel_raiz) == 5) {
                $hijos5[] = $elem;
            } else if (($hijo->vdo_nivel - $nivel_raiz) == 6) {
                $hijos6[] = $elem;
            } else if (($hijo->vdo_nivel - $nivel_raiz) == 7) {
                $hijos7[] = $elem;
            }
            
        }
        
//        return $hijos;
        $todos->hijos1 = $hijos1;
        $todos->hijos2 = $hijos2;
        $todos->hijos3 = $hijos3;
        $todos->hijos4 = $hijos4;
        $todos->hijos5 = $hijos5;
        $todos->hijos6 = $hijos6;
        $todos->hijos7 = $hijos7;
        
        return $todos;
    }
    
    function hijos_($linea) {
                        
        $vdo_id = $this->vendedor_id();
        
        if ($vdo_id <= 0) {
            return false;
        }
                        
        $todos = new stdClass();
//        $todos->hijos = array();
        for ($i = 1; $i <= 7; $i++) {            
            $todos->hijos{$i} = array();
            $todos->hijos{$i} = $this->hijos_linea($vdo_id, $i);


//            $ar = $this->hijos_linea($vdo_id, $i);
//            $c = count($ar);
//            
//            for ($j = 0; $j < $c; $j++) {
//                $todos->hijos{$i}[] = $ar[$j];
//            }

        }
//        echo "<pre>";
//        print_r($todos);
//        echo "</pre>";
        return $todos;
    }
    
    function hijos_linea($vdo_id, $linea){
                
        if ($vdo_id <= 0) {
            return false;
        }
        
        $sql = "select vdo_nivel as campo from vendedor where vdo_id='$vdo_id'";
//        echo $sql;
        $nivel_raiz = FUNCIONES::atributo_bd_sql($sql);
        $nivel = 0;
        $hijos = $this->obtener_red($vdo_id, $nivel, FALSE, $nivel_raiz + $linea, '', '=');
        
        $hijos1 = array();
                
        for ($i = 0; $i < count($hijos); $i++) {
            
            $hijo = $hijos[$i];
            $elem = FUNCIONES::objeto_bd_sql("select concat(int_nombre,' ',int_apellido)as nombre,
                int_telefono as telefono,int_celular as celular,
                int_fecha_nacimiento as fecha_nac,
                int_email as email,usu_id as login,vdo_venta_inicial as codigo_venta 
                from interno 
                inner join vendedor on (int_id=vdo_int_id)
                inner join ad_usuario on (usu_per_id=vdo_int_id)
                where int_id='$hijo->vdo_int_id' and vdo_id='$hijo->vdo_id'
                and usu_gru_id='AFILIADOS' and vdo_estado='Habilitado'");            
            
            if (($hijo->vdo_nivel - $nivel_raiz) == $linea) {
                $hijos1[] = $elem;
            }
        }
        
        return $hijos1;                
    }

    function obtener_red($vdo_id, &$nivel, $self = false, $profundidad = 0, $this_not = '', $signo = '<=') {

        $arr_vendedores = array();
        if ($self) {
            //if ($this_not != $vdo_id) {                                    
            $arr_vendedores[] = FUNCIONES::objeto_bd_sql("select * from vendedor where vdo_id='$vdo_id'");
//            $arr_vendedores[] = $vdo_id;
            //}//esto es para incluirlo si es diferente de $this_not
        }

        $sql = "select vdo_id from vendedor 
            inner join venta on (vdo_venta_inicial=ven_id)
            where vdo_vendedor_id='$vdo_id' and ven_estado in ('Pendiente','Pagado')
            and vdo_venta_inicial>0 and vdo_nivel $signo $profundidad";
//            echo "$sql<br/>";
        $vendedores = FUNCIONES::objetos_bd_sql($sql);
        $num = $vendedores->get_num_registros();

        if ($profundidad > 0) {

            for ($i = 0; $i < $num; $i++) {

                $obj = $vendedores->get_objeto();

                $arr_aux = $this->obtener_red($obj->vdo_id, $nivel, true, $profundidad, $this_not, $signo);
                for ($j = 0; $j < count($arr_aux); $j++) {

                    if ($this_not != $arr_aux[$j]) {//esto es para incluirlo si es diferente de $this_not                                    
                        $arr_vendedores[] = $arr_aux[$j];
                    }//esto es para incluirlo si es diferente de $this_not
                }

                $vendedores->siguiente();
            }
        } else {
            return $arr_aux = array();
        }

        return $arr_vendedores;
    }

    function vendedor_id() {
        $sql = "select vdo_id from vendedor where vdo_int_id='" . $this->usuDatos[2] . "';";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            $objeto = mysql_fetch_object($result);
            return $objeto->vdo_id;
        } else {
            return 0;
        }
    }

    function limpiar_cadena($cadena) {
        $cadena = str_replace('á', 'a', $cadena);
        $cadena = str_replace('é', 'e', $cadena);
        $cadena = str_replace('í', 'i', $cadena);
        $cadena = str_replace('ó', 'o', $cadena);
        $cadena = str_replace('ú', 'u', $cadena);
        $cadena = str_replace('�?', 'A', $cadena);
        $cadena = str_replace('É', 'E', $cadena);
        $cadena = str_replace('�?', 'I', $cadena);
        $cadena = str_replace('Ó', 'O', $cadena);
        $cadena = str_replace('Ú', 'U', $cadena);
        $cadena = str_replace('ñ', 'n', $cadena);
        $cadena = str_replace('Ñ', 'N', $cadena);
        return $cadena;
    }

}
