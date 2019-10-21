<?php
ini_set('display_errors', 'On');
class UTIL {

    public static $_FOTOS = 100;
    public static $_DOCUMENTOS_RESERVA = 101;
    public static $_DOCUMENTOS_VENTA = 102;

    public static $const_dir = array(
        100 => 'imagenes/persona/',
        101 => 'docs/reserva/',
        102 => 'docs/venta/'
    );
    
    public static function nombre_archivo($name, $tipo_carga, $tabla_id = 0, $desc = ''){
        
        $nombre_archivo = "";        
        $datos_archivo = explode('.', $name);
        $extension = $datos_archivo[count($datos_archivo) - 1];
        
        if ($tipo_carga == UTIL::$_FOTOS) {
            
            $nn = date('d_m_Y_H_i_s_') . rand();                        
            $nombre_archivo = $nn . "." .$extension;
            
        } else {
            $desc = str_replace(' ', '_', $desc);
            $nombre_archivo = "$tabla_id-$desc.$extension";
        }
        
        return $nombre_archivo;
    }

    public static function subir_archivo($name, $tmp, $tipo_carga, $tabla_id = 0, $desc = '') {

        $result = new stdClass();

        require_once('clases/upload.class.php');
        
//        $nn = date('d_m_Y_H_i_s_') . rand();

        $upload_class = new Upload_Files();

        $upload_class->temp_file_name = trim($tmp);
        
//        $datos_archivo = explode('.', $name);
//        $extension = $datos_archivo[count($datos_archivo) - 1];
        
//        $upload_class->file_name = $nn . substr(trim($name), -4, 4);
//        $upload_class->file_name = $nn . "." .$extension;
        $upload_class->file_name = UTIL::nombre_archivo($name, $tipo_carga, $tabla_id, $desc);

        $result->nombre_archivo = $upload_class->file_name;

        $directorio = _sistema_root .'/'. self::$const_dir[$tipo_carga];
        $result->directorio = $directorio;

        $upload_class->upload_dir = $directorio;

        $upload_class->upload_log_dir = $directorio . "upload_logs/";

        $upload_class->max_file_size = 1048576*4;

        $upload_class->ext_array = array(".jpg", ".gif", ".png", ".xls", ".pdf", ".sql", ".txt", ".docx", ".xlsx", ".jpeg");

        $upload_class->crear_thumbnail = false;

        $valid_ext = $upload_class->validate_extension();

        $valid_size = $upload_class->validate_size();

        $valid_user = $upload_class->validate_user();

        $max_size = $upload_class->get_max_size();

        $file_size = $upload_class->get_file_size();

        $file_exists = $upload_class->existing_file();

        if (!$valid_ext) {
            $result->exito = 'no';
            $result->mensaje = "La Extension de este Archivo es invalida, Intente nuevamente por favor!";
        } elseif (!$valid_size) {
            $result->exito = 'no';
            $result->mensaje = "El Tamaño de este archivo es invalido, El maximo tamaño permitido es: $max_size y su archivo pesa: $file_size";
        } elseif ($file_exists) {
            $result->exito = 'no';
            $result->mensaje = "El Archivo Existe en el Servidor, Intente nuevamente por favor.";
        } else {
            $upload_file = $upload_class->upload_file_with_validation();
//            $upload_file = $upload_class->upload_file_no_validation();

            if (!$upload_file) {
                $result->exito = 'no';
                $result->mensaje = "Su archivo no se subio correctamente al Servidor.(" . $directorio . $upload_class->file_name .")";
            } else {
                $result->exito = "si";

                require_once('clases/class.upload.php');

//                $mifile = 'imagenes/persona/' . $upload_class->file_name;
                $mifile = $directorio . $upload_class->file_name;
                $result->mi_file = $mifile;

                $handle = new upload($mifile);

                if ($handle->uploaded) {
                    
//                    $handle->image_resize = true;
//                    $handle->image_ratio = true;
//                    $handle->image_y = 50;
//                    $handle->image_x = 50;
//                    $handle->process($directorio . 'chica/');
//
//                    if (!($handle->processed)) {
//                        echo 'error : ' . $handle->error;
//                    }
                }
            }
        }

        return $result;
    }

}

?>