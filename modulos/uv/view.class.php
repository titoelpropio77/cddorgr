<?php

class VIEWPLANO {

    public $config;
    public $acceso = FALSE;

    function VIEWPLANO() {
        $result = $this->urbanizacion_fases();
        if ($result->estado) {
            // Iniciar Configuracion
            $this->config = $this->plano_config($result->pla_urb_id, "fase");
        } else {
            $this->config = $this->plano_config($_GET['u'], "");
        }
        // Verificar si el usuario esta habilitado en el sistema 
        $this->acceso = $this->acceso_verificar();
    }

    function acceso_verificar() {
        if (isset($_GET['mapa'])) {
            $usu_id = $this->safe_b64decode($_GET['mapa']);
            $sql = "SELECT * FROM `ad_usuario` WHERE usu_estado='1' AND usu_id='" . $usu_id . "'";
            $result = mysql_query($sql);
            $num = mysql_num_rows($result);
            if ($num > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    function iniciar() {

        $datos = new stdClass();
        // Mapa por defecto
        $defaultMapId;
        if (isset($_GET['u'])) {
            $defaultMapId = $_GET['u'];
        } else {
            $defaultMapId = $this->urbanizacion_activo();
        }

        // Panel abierto planos
        $panelOpened = 1;
        if ($this->config->pla_panel_left == "Automatico") {
            // Detectar si es mobil
            $detect = new Mobile_Detect();
            if ($detect->isMobile()) {
                $panelOpened = 0;
            } else {
                $panelOpened = 1;
            }
        } else {
            if ($this->config->pla_panel_left == "Mostrar") {
                $panelOpened = 1;
            } else {
                $panelOpened = 0;
            }
        }

        // tabs
        $disableViewTab = 0;
        if ($this->config->pla_tab_plano == "Mostrar") {
            $disableViewTab = 0;
        } else {
            $disableViewTab = 1;
        }


        $datos->data = array(
            'settings' => array(
                'panelOpened' => $panelOpened,
                'disableViewTab' => $disableViewTab,
                'defaultMarkerType' => null
            ),
            'tree' => array(
                $this->urbanizacion()
            ),
            'markerTypes' => array(
                array(
                    'id' => 'Disponible',
                    'name' => 'Disponible'
                ),
                array(
                    'id' => 'Vendido',
                    'name' => 'Vendido'
                ),
                array(
                    'id' => 'Reservado',
                    'name' => 'Reservado'
                ),
                array(
                    'id' => 'Bloqueado',
                    'name' => 'Bloqueado'
                )
            ),
            'defaultMapId' => $defaultMapId
        );
        return $datos->data;
    }

    function buscar() {

        $datos = new stdClass();
        $and = "";
        $error = "";
        $busqueda = "";
        $htmlResul = "";
        $totalResult = 1;


        // DATOS POST
        $superficieIni = $this->clean_input($_POST['superficieIni']);
        $superficieFin = $this->clean_input($_POST['superficieFin']);

        $precioIni = $this->clean_input($_POST['precioIni']);
        $precioFin = $this->clean_input($_POST['precioFin']);

        $lot_estado = $this->clean_input($_POST['markerTypeId']);

        if (($lot_estado == "") && ($superficieIni == "") && ($superficieFin == "") && ($precioIni == "") && ($precioFin == "")) {
            $error .= "Por favor defina un criterio de b&uacute;squeda <br/><b>Estado, Superficie o Rango de precio</b>.<br/>";
        }

        if ($lot_estado != '') {
            $and .= " and lot_estado='" . $lot_estado . "' ";
        }

        if (($superficieIni != '') || ($superficieFin != '')) {
            if (is_numeric($superficieIni) && is_numeric($superficieFin)) {
                if ($superficieIni > $superficieFin) {
                    $error .= "Superficie m&iacute;nimo debe ser menor que el m&aacute;ximo<br/>";
                } else {
                    $and = " and lot_superficie >='" . $superficieIni . "' and lot_superficie <='" . $superficieFin . "' ";
                }
            } else {
                $error .= "Superficie m&iacute;nimo y m&aacute;ximo debe ser n&uacute;mero, (No utilice comas ni puntos)<br/>";
            }
        }

        if (($precioIni != '') || ($precioFin != '')) {
            if (is_numeric($precioIni) && is_numeric($precioFin)) {
                if ($precioIni > $precioFin) {
                    $error .= "El precio m&iacute;nimo debe ser menor que el m&aacute;ximo<br/>";
                } else {
                    $and .= " and TRUNCATE(lot_superficie*zon_precio,0) >='" . $precioIni . "' and TRUNCATE(lot_superficie*zon_precio,0) <='" . $precioFin . "' ";
                }
            } else {
                $error .= "Los precios m&iacute;nimo y m&aacute;ximo debe ser n&uacute;mero, (No utilice comas ni puntos)<br/>";
            }
        }

        //lot_superficie*zon_precio TRUNCATE 


        if ($error == "") {
            $sql = "SELECT lot_id,lot_nro,man_nro,lot_superficie, zon_precio, (lot_superficie*zon_precio) as precio, zon_moneda FROM lote 
					inner join manzano ON(lot_man_id=man_id)
					inner join zona on(lot_zon_id=zon_id)
					WHERE man_urb_id='" . $_GET['u'] . "' " . $and;

            //echo $sql;

            $result = mysql_query($sql);
            $totalResult = mysql_num_rows($result);
            if ($totalResult > 0) {
                for ($i = 0; $i < $totalResult; $i++) {
                    $objeto = mysql_fetch_object($result);
                    $busqueda .= '<a href="#/?view=marker&id=' . $objeto->lot_id . '" class="">Manz ' . $objeto->man_nro . ' Lot: ' . $objeto->lot_nro . '</a> ';
                }
            }

            $htmlResul = $busqueda;
        } else {
            $htmlResul = '<div class="alert alert-error">' . $error . '</div>';
        }


        $datos->data = array(
            'count' => $totalResult,
            'result' => $htmlResul
        );

        return $datos->data;
    }

    function plano_config($urb_id, $fase) {

        $sqlAnd = "";
        $sqlJoin = "";

        if ($fase == "fase") {
            $sqlJoin = "INNER JOIN urbanizacion_plano_config ON (pla_fases=urb_id)";
            $sqlAnd = " pla_fases='" . $_GET['u'] . "' and pla_uv_id='" . $_GET['uv'] . "' ";
        } else {
            $sqlJoin = "INNER JOIN urbanizacion_plano_config ON (pla_urb_id=urb_id)";
            $sqlAnd = " urb_id='" . $urb_id . "' ";
        }

        $sql = "SELECT pla_urb_id,pla_tab_plano,pla_panel_left,pla_zoom,pla_marker,pla_img_width,pla_img_height,pla_imagen,urb_id,urb_nombre FROM urbanizacion 
			" . $sqlJoin . "
		WHERE " . $sqlAnd;

        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            return mysql_fetch_object($result);
        } else {
            return "";
        }
    }

    function plano() {

        $urb_id = $_POST['id'];
        $datos = new stdClass();
        $datos->data = array(
            'map' => array(
                //'id' => $this->config->urb_id,
                'id' => $urb_id,
                'name' => $this->config->urb_nombre,
                'enabled' => 1,
                'showLegend' => 0,
                'zoom' => $this->config->pla_zoom, //default
                'mapImage' => array(
                    'width' => $this->config->pla_img_width,
                    'height' => $this->config->pla_img_height
                )
            ),
            'regions' => $this->manzano_coordenadas($urb_id),
            'labels' => array(
            /*
              array(
              'id' => 1,
              'x' => 200,
              'y' => 200,
              'clickable' => true,
              'html' => 'isaac'
              )
             */
            ),
            'markers' => $this->lote_coordenadas($urb_id),
            'viewHtml' => array(
                'breadcrumb' => '<ul class="breadcrumb"><li><a href="#"data-cfm-map-link="' . $urb_id . '">' . $this->config->urb_nombre . '</a></li></ul>',
                'legend' => ''
            ),
            'leyenda' => array(
                array(
                    'label' => "Disponible",
                    'color' => "#7bd148",
                    'cssClass' => "Disponible"
                ),
                array(
                    'label' => "Vendido",
                    'color' => "#f83a22",
                    'cssClass' => "Vendido"
                ),
                array(
                    'label' => "Reservado",
                    'color' => "#4986e7",
                    'cssClass' => "Reservado"
                ),
                array(
                    'label' => "Bloqueado",
                    'color' => "#c2c2c2",
                    'cssClass' => "Bloqueado"
                )
            )
        );
        return $datos->data;
    }

    function manzano_coordenadas($urb_id) {
        $sqlAnd = "";
        if (isset($_GET['uv'])) {
            $sqlAnd .=" and uv_id='" . $_GET['uv'] . "' ";
        }

        $arr = array();
        $sql = "SELECT DISTINCT man_id,man_nro, man_img_x, man_img_y, CAST(man_nro as signed) as numero  FROM manzano 
		inner join lote on (lot_man_id=man_id)
		inner join uv on (lot_uv_id = uv_id)
		WHERE man_urb_id='" . $urb_id . "' and man_img_x !='' && man_img_y !='' " . $sqlAnd . " ORDER BY numero ASC"; //ORDER BY numero ASC
        //echo $sql;

        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $objArray = array(
                    'id' => $objeto->man_id,
                    'name' => utf8_encode($objeto->man_nro),
                    'mapId' => $urb_id,
                    'x' => $objeto->man_img_x,
                    'y' => $objeto->man_img_y,
                    'zoom' => '0'
                );
                array_push($arr, $objArray);
            }
        }
        return $arr;
    }

    function loteInfo() {
        $lot_id = $_POST['id'];
        $result = "";
        //inner join uv_zona_precio on (uvz_uv_id = lot_uv_id and uvz_zon_id=lot_zon_id)
        $sql = "
			select lot_nro,lot_superficie,lot_estado,man_nro,urb_nombre, 
                        zon_precio, uv_nombre, zon_moneda,zon_nombre,lot_id,urb_anio_base
			from lote 
			inner join manzano on (lot_man_id=man_id)
			inner join urbanizacion on (man_urb_id=urb_id)
			inner join zona on (lot_zon_id = zon_id)
			inner join uv on (lot_uv_id = uv_id)
			
			where lot_id='" . $lot_id . "'
		";

        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        $objeto = mysql_fetch_object($result);

        $precio = (($objeto->lot_superficie) * ($objeto->zon_precio));
        $solicitante = "";
        $sufijo = "";
        if ($objeto->zon_moneda == 2) {
            $sufijo = '$us.';
        } else {
            $sufijo = 'Bs.';
        }

        if ($objeto->lot_estado == 'Vendido') {
            $sql2 = "
			select 
			*
			from venta 
			inner join interno on (ven_lot_id='" . $lot_id . "' and (ven_estado='Pendiente' or ven_estado='Pagado') and ven_int_id=int_id) 
			";

            $result2 = mysql_query($sql2);
            $objeto2 = mysql_fetch_object($result2);

            $solicitante = utf8_encode($objeto2->int_nombre . ' ' . $objeto2->int_apellido);
            $precio = $objeto2->ven_monto;
            if ($objeto2->ven_co_propietario * 1 > 0) {
                $sql_2 = "select * from interno where int_id= $objeto2->ven_co_propietario";

                $result_2 = mysql_query($sql_2);
                $objeto_2 = mysql_fetch_object($result_2);
                $solicitante.=" / $objeto_2->int_nombre $objeto_2->int_apellido";
            }
        } else {
            if ($objeto->lot_estado == 'Reservado') {
                $sql3 = "
				select 
				res_vdo_id,int_nombre,int_apellido
				from reserva_terreno 
				
				inner join interno on (res_lot_id='" . $lot_id . "' and res_estado in ('Pendiente','Habilitado') and res_int_id=int_id)";

                $result3 = mysql_query($sql3);
                $objeto3 = mysql_fetch_object($result3);

                $solicitante = 'Vendedor: ' . utf8_encode($this->nombre_persona_vendedor($objeto3->res_vdo_id)) . ', Cliente: ' . utf8_encode($objeto3->int_nombre . ' ' . $objeto3->int_apellido);
            } else {

                $sql3 = " select * from bloquear_terreno where bloq_lot_id=$lot_id and bloq_estado='Habilitado'";

                $result3 = mysql_query($sql3);
                $objeto3 = mysql_fetch_object($result3);

                if (trim($objeto3->bloq_nota) == 'VENDIDO GUAPOMO') {
                    $bloq_estado = trim($objeto3->bloq_nota);
                } else {
                    $bloq_estado = 'Bloqueado';
                }
                if ($objeto->lot_estado == 'Bloqueado') {
                    $solicitante = '';
                }
            }
        }
        
//        if ($this->acceso) {
        if (TRUE) {
            if ($objeto->lot_estado == 'Disponible') {
                $sql_mlm = "select * from lote_multinivel where lm_lot_id=$objeto->lot_id";
                $result_mlm = mysql_query($sql_mlm);
                $datos_mlm = mysql_fetch_object($result_mlm);

                if ($datos_mlm) {
                    if ($datos_mlm->lm_anticipo_tipo == 'porc') {
                        $ci = $datos_mlm->lm_anticipo_min . "%";
                    } else {
                        $ci = $datos_mlm->lm_anticipo_min . " " . $sufijo;
                    }
                    $s_ci .= "<b>Cuota Inicial: </b> " . $ci . "<br/>";
                    $cm = ($precio - $ci) / ($objeto->urb_anio_base * 12);
                }
            }
        }

        $html = "<div class='loteInfo'>";
//        $html .= "<b>" . $objeto->urb_nombre . "</b><br/>";
//        $html .= "<b>Uv.: </b> " . $objeto->uv_nombre . "<br/>";
//        $html .= "<b>Manzano: </b>" . $objeto->man_nro . "<br/>";
        $html .= "<b>Superficie: </b> " . $objeto->lot_superficie . " M2.<br/>";
        $html .= "<b>Precio M2: </b> " . number_format($objeto->zon_precio, 2, ".", ",")  . " " . $sufijo . "<br/>";
        
        
        

//        if ($this->acceso) {
        if (true) {    
            $html .= "<b>Precio Lote: </b> " . number_format($precio, 2, ".", ",") . " " . $sufijo . "<br/>";
        }
//        $html .= "<b>Estado: </b> " . $objeto->lot_estado . "<br/>";
        $html .= $s_ci;
        $html .= "<b>Cuota Mensual: </b> " . number_format($cm, 2, ".", ",")  . " " . $sufijo . "<br/>";
        if ($this->acceso) {
            if ($solicitante <> "") {
                $html .= "<b>Cliente: </b>" . $solicitante . "<br/>";
            }
        }

        $html .= "</div>";

        // resultado json
        $datos = new stdClass();
        $datos->data = array(
            'info' => $html
        );
        return $datos->data;
    }

    function lote_coordenadas($urb_id) {

        //filtro por fase 
        $sqlJoin = "";
        $sqlWhere = "";
        if (isset($_GET['uv'])) {
            $sqlJoin = " INNER JOIN uv ON(uv_id=lot_uv_id) ";
            $sqlWhere = " AND uv_id='" . $_GET['uv'] . "' ";
        }

        $arr = array();
        $sql = "SELECT * FROM lote_cordenada_imagen 
						INNER JOIN lote ON(cor_lot_id=lot_id) 
						INNER JOIN manzano ON (lot_man_id=man_id)
						" . $sqlJoin . "
						WHERE cor_urb_id='" . $urb_id . "' " . $sqlWhere;

        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                $objArray = array(
                    'id' => $objeto->lot_id,
                    'x' => (($objeto->cor_lot_coorX) + ($this->config->pla_marker / 2)),
                    'y' => $objeto->cor_lot_coorY,
                    'typeCssName' => $objeto->lot_estado,
                    'html' => '<div class="cfm-inner" data-id="' . $objeto->lot_id . '" data-nro="' . $objeto->lot_nro . '" data-manz="' . $objeto->man_nro . '"><div class="cfm-icon-nuevo" style="width:' . $this->config->pla_marker . 'px;height:' . $this->config->pla_marker . 'px"></div></div>'
                );
                array_push($arr, $objArray);
            }
        }
        return $arr;
    }

    function urbanizacion() {
        $arr = array();
        $sql = "SELECT urb_id,urb_nombre FROM urbanizacion INNER JOIN urbanizacion_plano_config ON (pla_urb_id=urb_id) order by urb_nombre asc";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            for ($i = 0; $i < $num; $i++) {
                $objeto = mysql_fetch_object($result);
                if ($objeto->urb_id == $_GET['u']) {
                    $objArray = array(
                        'data' => utf8_encode($objeto->urb_nombre),
                        'metadata' => array(
                            'id' => $objeto->urb_id,
                            'enabled' => 1,
                            'noMap' => false
                        ),
                        'attr' => ''
                    );
                    array_push($arr, $objArray);
                }
            }
        }
        return $arr;
    }

    function precio_min_max() {
        $datos = new stdClass();
        $datos->min = 0;
        $datos->max = 10000;

        $sql = "SELECT  TRUNCATE(min(lot_superficie*zon_precio),0) as min, TRUNCATE(max(lot_superficie*zon_precio),0) as max  FROM lote 
		inner join manzano ON(lot_man_id=man_id)
		inner join zona on(lot_zon_id=zon_id)
		WHERE man_urb_id='" . $_GET['u'] . "' ";

        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            $objeto = mysql_fetch_object($result);

            $datos->min = $objeto->min;
            $datos->max = $objeto->max;
        }
        return $datos;
    }

    function superficie_min_max() {
        $datos = new stdClass();
        $datos->min = 0;
        $datos->max = 10000;

        $sql = "SELECT TRUNCATE(min(lot_superficie),0) as min, TRUNCATE(max(lot_superficie),0) as max FROM lote 
				INNER JOIN manzano ON (lot_man_id=man_id)
						WHERE man_urb_id='" . $_GET['u'] . "'; ";

        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            $objeto = mysql_fetch_object($result);

            $datos->min = $objeto->min;
            $datos->max = $objeto->max;
        }
        return $datos;
    }

    function urbanizacion_activo() {
        $sql = "SELECT urb_id,urb_nombre FROM urbanizacion order by urb_nombre asc limit 0,1";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {
            $objeto = mysql_fetch_object($result);
            return $objeto->urb_id;
        } else {
            return 0;
        }
    }

    function nombre_persona_vendedor($vdo_id) {
        $sql = "SELECT int_nombre,int_apellido FROM interno
		inner join vendedor on (vdo_int_id=int_id) 
		WHERE vdo_id='$vdo_id'";
        $result = mysql_query($sql);
        $objeto = mysql_fetch_object($result);
        return $objeto->int_nombre . ' ' . $objeto->int_apellido;
    }

    function urbanizacion_fases() {
        $respuesta = new stdClass();
        $sql = "SELECT * FROM urbanizacion_plano_config WHERE pla_fases='" . $_GET['u'] . "'";
        $result = mysql_query($sql);
        $num = mysql_num_rows($result);
        if ($num > 0) {

            $sql = "SELECT * FROM urbanizacion_plano_config WHERE pla_fases='" . $_GET['u'] . "' and pla_uv_id='" . $_GET['uv'] . "' ";
            $result2 = mysql_query($sql);
            //$num2 = mysql_num_rows($result2);
            $objeto2 = mysql_fetch_object($result2);

            $respuesta->pla_urb_id = $objeto2->pla_urb_id;
            $respuesta->estado = true;
        } else {
            $respuesta->estado = false;
        }
        return $respuesta;
    }

    // Otras funciones
    function clean_input($string) {
        $string = str_replace(" ", "-", $string);
        $string = preg_replace('/[^A-Za-z0-9_\-]/', '', $string);
        return preg_replace('/-+/', '-', $string);
    }

    function safe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    function safe_b64decode($string) {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

}