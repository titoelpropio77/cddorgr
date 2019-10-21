<?php
class SISTEMA {
	
	public static function obj_post($obj) {
		//Convierte objeto en POST
		foreach ($obj as $key => $value) {
			$_POST[$key] = $value;
		}
	}
	
    public static function send_get($url, $data = array()) {
		//Datos por defecto tipo de aplicacion
		if(_aplicacion){
			$url = $url."&aplicacion="._aplicacion;
		}
		
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);
        $response = trim($response);
        $response = str_replace('\"', '"', trim($response));
        $response = str_replace('"{', '{', trim($response));
        $response = str_replace('}"', '}', trim($response));

        //echo $response;
        if ($response) {
            //return json_decode($response);
            return $response;
        } else {
            return null;
        }
    }
	
    public static function send_post($url, $data = array()) {
		//Datos por defecto tipo de aplicacion
		if(_aplicacion){
			$url = $url."&aplicacion="._aplicacion;
		}
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);
        $response = str_replace('\"', '"', trim($response));
        $response = str_replace('"{', '{', trim($response));
        $response = str_replace('}"', '}', trim($response));
        //echo "-------> $response <br>";
        if ($response) {
            //return json_decode($response); 
            return $response;
        } else {
            return null;
        }
    }

}
