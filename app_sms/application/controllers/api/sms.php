<?php 

date_default_timezone_set("America/La_Paz");

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'/libraries/REST_Controller.php';

class Sms extends REST_Controller {
	
	//=========== Bandeja GET ===========//
	function bandeja_get(){
		$limit = 30;
		$max = 0;
		$min = 0;
		
		$this->_bandeja_mensajes_estado();
		
		$sql = "select ban_id as nro_envio, ban_cel as celular, ban_contenido as contenido,
		ban_men_id as id from bandeja where ban_estado='LISTO' and ban_fecha_cre='".date('Y-m-d')."' order by ban_id asc limit 0,".$limit; 
		
		$query = $this->db->query($sql);
		$num = $query->num_rows();
		if ($num>0) {
			foreach ($query->result() as $i=>$obj)  
			{
				if ($i == 0) {
					$min = $obj->nro_envio;
				}
				if ($i == ($num - 1)) {
					$max = $obj->nro_envio;
				}
			}
			$jsonEstructura = array(
				'mensaje' => "Hay ".$query->num_rows()." mensajes para enviar",
				'accion' => "correcto",
				'opciones' => array(
									'max'=>$max,
									'min'=>$min
									),
				'datos' => $query->result_array()
			); 
        } else {
			$jsonEstructura = array(
				'mensaje' => "No hay mensajes para enviar",
				'accion' => "error",
				'opciones' => "",
				'datos' => array() 
			);
		} 
		$this->_bandeja_estado_bandeja($max, $min);
		$this->response($jsonEstructura, 200);
	}
	
	function _bandeja_mensajes_estado()
    {		
		$sql = "select est_max_id as max,est_min_id as min from estado_bandeja where est_id='0'";
		$query = $this->db->query($sql);
		$num = $query->num_rows();
		if ($num > 0) {
			$o = $query->row();
			if ($o->max != 0 && $o->min != 0) {
				$fecha = date("Y-m-d");
				$hora = date("H:i:s");
				$sql2 = "update bandeja set ban_estado='ENVIADO',ban_fecha_env='$fecha',ban_hora_env='$hora' where ban_id >= '$o->min' and ban_id <= '$o->max'";
				$query = $this->db->query($sql2);
			}
		}
	}
	
	function _bandeja_estado_bandeja($max, $min)
    {
		 if ($max != null && $min != null) {
			$fecha = date("Y-m-d");
			$hora = date("H:i:s");
			$sql1 = "update bandeja set ban_estado='DESPACHADO',ban_fecha_desp='$fecha',ban_hora_desp='$hora' where ban_id >= '$min' and ban_id <= '$max'";
			$query = $this->db->query($sql1);

			$sql2 = "update estado_bandeja set est_max_id='$max',est_min_id='$min' where est_id='0'";    
			$query = $this->db->query($sql2);
		}
	}
	//=========== Bandeja GET FIN ===========//
}