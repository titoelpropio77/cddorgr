<?PHP

class USUARIO {

    var $datos_usuario;
    var $session;

    function USUARIO() {
        $this->session = new SESSION;
    }

    function get_nombre_completo() {
        return $this->session->get('afil_nombre_completo');
    }

    function get_foto() {

        return $this->session->get('afil_foto');
    }

    function get_aut() {

        return $this->session->get('afil_aut');
    }

    function get_usu_per_id() {

        return $this->session->get('afil_usu_per_id');
    }
    
    function get_vdo_id() {

        return $this->session->get('vdo_id');
    }

    function get_id() {
        return $this->session->get('afil_id');
    }

    function sesion_iniciada() {
		
		if ($this->session->get('sesion_num') != _sesion) {
			// echo "son distintas";
			return false;
		} else {
			// echo "son iguales";
		}
	
        if (trim($this->get_id()) <> '') {
            return true;
        } else {
            return false;
        }
    }

    function get_token() {
        return $this->session->get('token');
    }

    function iniciar_sesion($jsonResp) {
        $this->session->set('afil_id', $jsonResp->datos->i);
        $this->session->set('afil_usu_per_id', $jsonResp->datos->p);
        $this->session->set('vdo_id', $jsonResp->datos->v);
        $this->session->set('afil_usu_gru_id', $jsonResp->datos->g);
        $this->session->set('afil_nombre', $jsonResp->datos->i);
		//$this->session->set('afil_rango', $jsonResp->datos->r);
        $this->session->set('afil_foto', $jsonResp->datos->f);
        $this->session->set('afil_nombre_completo', $jsonResp->datos->n . " " . $jsonResp->datos->a);
        $this->session->set('token', $jsonResp->datos->t);
        $this->session->set('afil_aut', "positron");
		$this->session->set('sesion_num', _sesion);
		
    }

    function cerrar_sesion() {
        //$this->registrar_cierre();
        $this->session->cerrar();
    }

}

?>
