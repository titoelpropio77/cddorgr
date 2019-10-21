<?PHP

require_once('coneccion.class.php');
require_once('bitacora.class.php');
require_once('session.class.php');

class USUARIO {

    var $datos_usuario;
    var $session;

    function USUARIO() {
        $this->session = new SESSION;
    }

    function get_nombre_completo() {

        return $this->session->get('nombre_completo');
    }

    function get_foto() {

        return $this->session->get('foto');
    }

    function get_aut() {

        return $this->session->get('aut');
    }

    function get_usu_per_id() {

        return $this->session->get('usu_per_id');
    }

    function get_gru_id() {

        return $this->session->get('usu_gru_id');
    }

    function get_id() {
        return $this->session->get('id');
    }

    function sesion_iniciada() {
        if (trim($this->get_id()) <> '') {
            return true;
        } else {
            return false;
        }
    }

    function iniciar_sesion() {

        $this->session->set('id', $this->datos_usuario->usu_id);
        $this->session->set('usu_per_id', $this->datos_usuario->usu_per_id);
        $this->session->set('usu_gru_id', $this->datos_usuario->usu_gru_id);
        $this->session->set('nombre', $this->datos_usuario->usu_id);
        $this->session->set('foto', $this->datos_usuario->int_foto);
        $this->session->set('nombre_completo', $this->datos_usuario->int_nombre . " " . $this->datos_usuario->int_apellido);
        $this->session->set('ges_id', $_POST['ges_id']);
        $this->session->set('suc_id', $this->datos_usuario->usu_suc_id);
        $this->session->set('usu_loged', $this->datos_usuario->usu_id);
        $this->session->set('aut', _sesion);

        $this->registrar_inicio();
    }

    function validar_usuario() {
        $coneccion = NEW ADO;

        $sql = "select usu_id,int_nombre,int_apellido,usu_per_id,int_foto,usu_gru_id,usu_suc_id
				from ad_usuario inner join interno on(usu_per_id=int_id)
			 where md5(usu_id) = '" . md5(trim($_POST['myusername'])) . "' and usu_password = '" . (md5($_POST['mypassword'])) . "' and usu_estado='1'";
//        echo "$sql";
        $coneccion->ejecutar($sql);

        $cantidad = $coneccion->get_num_registros();

        if ($cantidad == 1) {
            $this->datos_usuario = $coneccion->get_objeto();
            if($this->datos_usuario->usu_suc_id>0){
                return true;
            }else{
                $GLOBALS[msj_inicio]="Usted no tiene una sucursal asignada";
                return false;    
            }
        } else {
            return false;
        }
    }

    function registrar_inicio() {
        $bitacora = new BITACORA;

        $datos = array();
        $datos['tipo_accion'] = 'INICIO';
        $datos['usuario'] = $this->get_id();
        $datos['accion'] = 'Ingreso al Sistema';

        $bitacora->agregar_accion($datos);
    }

    function registrar_cierre() {
        $bitacora = new BITACORA;

        $datos = array();
        $datos['tipo_accion'] = 'INICIO';
        $datos['usuario'] = $this->get_id();
        $datos['accion'] = 'Cierre del Sistema';

        $bitacora->agregar_accion($datos);
    }

    function get_num_inicios() {
        $bitacora = new BITACORA;

        return $bitacora->get_num_accion($this->get_id(), date('Y-m-d'), 'INICIO');
    }

    function cerrar_sesion() {
        $_POST['usuario'] = "";

        $_POST['codigo'] = "";

        $this->registrar_cierre();

        $this->session->cerrar();

        // $this->session->liberar_variable('id');
        // $this->session->liberar_variable('usu_per_id');
        // $this->session->liberar_variable('nombre');
        // $this->session->liberar_variable('foto');
        // $this->session->liberar_variable('nombre_completo');
        // $this->session->liberar_variable('aut');
    }

}

?>