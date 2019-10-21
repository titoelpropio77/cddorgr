<?php
class _db {
    public static $dbh=null;

    public static function open() {
        $dns = 'mysql:host=localhost;dbname=sistema_com_bo_pruebas9;charset=utf8';
        try {
            _db::$dbh = new PDO($dns, 'sistema.com.bo', 'wfnuxEHbH5', array(PDO::ATTR_PERSISTENT => true));
        } catch (PDOException $Exception) {            
            switch ($Exception->getCode()) {
                case 1045:                   
                    echo 'ACCESO DENEGADO: El sistema no puede establecer conexi&ocute;n con la base de datos. Por favor vuelva a intentarlo nuevamente y si el problema persiste contacte con soporte t&eacute;cnico.';
                    throw new Exception();
                    break;
                default:
                    echo 'ACCESO DESCONOCIDO: '.$Exception->getMessage() . ' [' . $Exception->getCode() . '].';
                    throw new Exception();
            }
        }
    }

    
    

    public static function close() {
        try {
            unset(_db::$dbh);
        } catch (PDOException $Exception) {
            echo 'CIERRE DE CONEXI&Oacute;N FALLIDA: '.$Exception->getMessage() . ' [' . $Exception->getCode() . '].';
            throw new Exception();
        }
    }

    public static function execute($sql) {
//        echo "-- $sql;<br>";
        try {
            $sth = _db::$dbh->prepare($sql);
            if ($sth->execute()) {
                $filasProcesadas = $sth->rowCount();
                unset($sth);
                return ($filasProcesadas > 0 ? true : false);                
            }else{
                echo $sql.'<br>'; 
                echo 'TRANSACCI&Oacute;N FALLIDA: El sistema no puede procesar la transacci&oacute;n. Por favor vuelva a intentarlo nuevamente y si el problema persiste contacte con soporte t&eacute;cnico.'; 
                throw new Exception();
            }
            
        } catch (PDOException $Exception) {
            
            echo 'TRANSACCI&Oacute;N FALLIDA: '. $Exception->getMessage() . ' [' . $Exception->getCode() . '].';
            throw new Exception();
        }
    }

    
    public static function query($sql) {
        try {
            $sth = _db::$dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            if ($sth->execute()) {
                $filas_obtenidas = array();
                while ($fila = $sth->fetchObject()) {
                    $filas_obtenidas[] = $fila;
                }
                unset($sth);
                return $filas_obtenidas;
            } else {
                echo "$sql<br>";
                echo 'CONSULTA FALLIDA: El sistema no puede procesar la consulta. Por favor vuelva a intentarlo nuevamente y si el problema persiste contacte con soporte t&eacute;cnico.';

                throw new Exception();                
            }            
        } catch (PDOException $Exception) {
            echo 'CONSULTA FALLIDA: '.$Exception->getMessage() . ' [' . $Exception->getCode() . '].';
            throw new Exception();
        }
    }
    
    public static function objeto_sql($sql) {
        $lista=  _db::query($sql);
        if(count($lista)>0){
            return $lista[0];
        }else{
            return null;
        }
    }
    public static function objetos_sql($sql) {
        return _db::query($sql);
    }
    public static function atributo_sql($sql) {
        $lista=  _db::query($sql);
        if(count($lista)>0){
            return $lista[0]->campo;
        }else{
            return '';
        }
    }

}
