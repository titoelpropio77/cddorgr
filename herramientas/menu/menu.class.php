<?class MENU {    var $usuario;    function MENU($usu) {        $this->set_usuario($usu);    }    function dibujar_menu() {        $this->cargar_padres();    }    function set_usuario($usu) {        $this->usuario = $usu;    }    function get_usuario() {        return $this->usuario;    }    function cargar_padres() {        $conec = new ADO();        $sql = "SELECT distinct  ele_id,ele_padre,ele_nombre,ele_titulo,ele_tipo,ele_icono					FROM ad_elemento,ad_permiso,ad_usuario					WHERE usu_id= '" . $this->get_usuario() . "'					AND usu_gru_id = pmo_gru_id					AND pmo_ele_id=ele_id					AND ele_padre='0'					AND ele_estado = 'H'					ORDER BY ele_orden";        $conec->ejecutar($sql);        $num = $conec->get_num_registros();        if ($num <> 0) {            for ($i = 0; $i < $num; $i++) {                $objeto = $conec->get_objeto();                $imagen = "";                ?>                <li class="mNivel1">                    <a href="#Cat2" class="mLink1" ><img class="iconMas" src="imagenes/<?php echo $objeto->ele_icono; ?>"/> <span><?php echo $objeto->ele_titulo; ?></span></a>                    <ul class="menuNivel1">                <?php                $this->cargar_hijos($objeto->ele_id, $i);                ?>                       </ul>                </li>                <?php                $conec->siguiente();            }        }    }    function cargar_hijos($padre, $num) {        $conec = new ADO();        $sql = "SELECT distinct  ele_id,ele_padre,ele_nombre,ele_titulo,ele_tipo,ele_tarea,ele_icono					FROM ad_elemento,ad_permiso,ad_usuario					WHERE usu_id= '" . $this->get_usuario() . "'					AND usu_gru_id = pmo_gru_id					AND pmo_ele_id=ele_id					AND ele_padre='$padre'					AND ele_estado = 'H'					ORDER BY ele_orden";        $conec->ejecutar($sql);        $num = $conec->get_num_registros();        if ($num <> 0) {            for ($i = 0; $i < $num; $i++) {                $objeto = $conec->get_objeto();                $ruta = "gestor.php?mod=" . $objeto->ele_nombre . '&tarea=' . $objeto->ele_tarea;                				if($objeto->ele_icono=="reporte.png")				{					?>					<li><a class="mLinkSubCatRep" href="<?php echo $ruta; ?>" target="contenido" ><?php echo $objeto->ele_titulo;?></a></li>             					<?php				}				else				{					?>					<li><a class="mLinkSubCat" href="<?php echo $ruta; ?>" target="contenido" ><?php echo $objeto->ele_titulo;?></a></li>             					<?php				}                $conec->siguiente();            }        }    }}?>