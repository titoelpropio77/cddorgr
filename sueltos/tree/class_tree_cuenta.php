<?php
class _tree_struct {
	// Structure table and fields
	protected $table	= "";
	protected $fields	= array(
			"cue_id"		=> false,
			"cue_mon_id"	=> false,
			"cue_padre_id"	=> false,
			"cue_tree_position"	=> false,
			"cue_tree_left"		=> false,
			"cue_tree_right"		=> false,
			"cue_tree_level"		=> false
		);
		
	// Constructor
	function __construct($table = "con_cuenta", $fields = array()) {
		$this->table = $table;
		if(!count($fields)) {
			foreach($this->fields as $k => &$v) { $v = $k; }
		}
		else {
			foreach($fields as $key => $field) {
				switch($key) {
					case "cue_id":
					case "cue_mon_id":
					case "cue_padre_id":
					case "cue_tree_position":
					case "cue_tree_left":
					case "cue_tree_right":
					case "cue_tree_level":
						$this->fields[$key] = $field;
						break;
				}
			}
		}
		// Database
		$this->db = new _database;
	}

	function _get_node($cue_id) {
		$this->db->query("SELECT `".implode("` , `", $this->fields)."`, `cue_codigo`, `cue_ges_id`, `cue_mon_id` FROM `".$this->table."` WHERE cue_ges_id='".$_POST["cue_ges_id"]."' AND `".$this->fields["cue_id"]."` = ".(int) $cue_id);
		$this->db->nextr();
		return $this->db->nf() === 0 ? false : $this->db->get_row("assoc");
	}
	function _get_children($cue_id, $recursive = false) {
	
		$children = array();
		if($recursive) {
			$node = $this->_get_node($cue_id);
			$this->db->query("SELECT `".implode("` , `", $this->fields)."`, `cue_codigo`, `cue_ges_id`, `cue_mon_id` FROM `".$this->table."` WHERE  cue_ges_id='".$_GET["cue_ges_id"]."' AND `".$this->fields["cue_tree_left"]."` >= ".(int) $node[$this->fields["cue_tree_left"]]." AND `".$this->fields["cue_tree_right"]."` <= ".(int) $node[$this->fields["cue_tree_right"]]." ORDER BY `".$this->fields["cue_tree_left"]."` DESC");
		}
		else {
			$this->db->query("SELECT `".implode("` , `", $this->fields)."`, `cue_codigo` FROM `".$this->table."` WHERE cue_ges_id='".$_GET["cue_ges_id"]."' AND `".$this->fields["cue_padre_id"]."` = ".(int) $cue_id." ORDER BY `".$this->fields["cue_tree_position"]."` DESC");
		}
		while($this->db->nextr()) $children[$this->db->f($this->fields["cue_id"])] = $this->db->get_row("assoc");
		return $children;
	}
	function _get_path($cue_id) {
	
		$node = $this->_get_node($cue_id);
		$path = array();
		if(!$node === false) return false;
		$this->db->query("SELECT `".implode("` , `", $this->fields)."`, `cue_codigo`, `cue_ges_id`, `cue_mon_id` FROM `".$this->table."` WHERE `".$this->fields["cue_tree_left"]."` <= ".(int) $node[$this->fields["cue_tree_left"]]." AND `".$this->fields["cue_tree_right"]."` >= ".(int) $node[$this->fields["cue_tree_right"]]);
		while($this->db->nextr()) $path[$this->db->f($this->fields["cue_id"])] = $this->db->get_row("assoc");
		return $path;
	}

	function _create($parent, $cue_tree_position) {
		return $this->_move(0, $parent, $cue_tree_position);
	}
	
	function _remove($cue_id) {
		
		$cue_ges_id = $_POST["cue_ges_id"];
		
		if((int)$cue_id === 1) { return false; }
		$data = $this->_get_node($cue_id);
		$lft = (int)$data[$this->fields["cue_tree_left"]];
		$rgt = (int)$data[$this->fields["cue_tree_right"]];
		$dif = $rgt - $lft + 1;

		// deleting node and its children
		$this->db->query("".
			"DELETE FROM `".$this->table."` " . 
			"WHERE cue_ges_id='".$cue_ges_id."' AND `".$this->fields["cue_tree_left"]."` >= ".$lft." AND `".$this->fields["cue_tree_right"]."` <= ".$rgt
		);
		// shift cue_tree_left indexes of nodes cue_tree_right of the node
		$this->db->query("".
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["cue_tree_left"]."` = `".$this->fields["cue_tree_left"]."` - ".$dif." " . 
			"WHERE  cue_ges_id='".$cue_ges_id."' AND `".$this->fields["cue_tree_left"]."` > ".$rgt
		);
		// shift cue_tree_right indexes of nodes cue_tree_right of the node and the node's parents
		$this->db->query("" . 
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["cue_tree_right"]."` = `".$this->fields["cue_tree_right"]."` - ".$dif." " . 
			"WHERE  cue_ges_id='".$cue_ges_id."' AND  `".$this->fields["cue_tree_right"]."` > ".$lft
		);

		$pid = (int)$data[$this->fields["cue_padre_id"]];
		$pos = (int)$data[$this->fields["cue_tree_position"]];

		// Update cue_tree_position of siblings below the deleted node
		$this->db->query("" . 
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["cue_tree_position"]."` = `".$this->fields["cue_tree_position"]."` - 1 " . 
			"WHERE  cue_ges_id='".$cue_ges_id."' AND `".$this->fields["cue_padre_id"]."` = ".$pid." AND `".$this->fields["cue_tree_position"]."` > ".$pos
		);
		return true;
	}
	
	function _move($cue_id, $ref_id, $cue_tree_position = 0, $is_copy = false)
	{
	
		$cue_codigo = $_POST["cue_codigo"];
		$cue_ges_id = $_POST["cue_ges_id"];
		$cue_mon_id = $_POST["cue_mon_id"];
		
		if((int)$ref_id === 0 || (int)$cue_id === 1) { return false; }
		$sql		= array();						// Queries executed at the end
		$node		= $this->_get_node($cue_id);		// Node data
		$nchildren	= $this->_get_children($cue_id);	// Node children
		$ref_node	= $this->_get_node($ref_id);	// Ref node data
		$rchildren	= $this->_get_children($ref_id);// Ref node children

		$ndif = 2;
		$node_ids = array(-1);
		if($node !== false) {
			$node_ids = array_keys($this->_get_children($cue_id, true));
			// TODO: should be !$is_copy && , but if copied to self - screws some cue_tree_right indexes
			if(in_array($ref_id, $node_ids)) return false;
			$ndif = $node[$this->fields["cue_tree_right"]] - $node[$this->fields["cue_tree_left"]] + 1;
		}
		if($cue_tree_position >= count($rchildren)) {
			$cue_tree_position = count($rchildren);
		}

		// Not creating or copying - old parent is cleaned
		if($node !== false && $is_copy == false) {
			$sql[] = "" . 
				"UPDATE `".$this->table."` " . 
					"SET `".$this->fields["cue_tree_position"]."` = `".$this->fields["cue_tree_position"]."` - 1 " . 
				"WHERE cue_ges_id='".$cue_ges_id."' AND " . 
					"`".$this->fields["cue_padre_id"]."` = ".$node[$this->fields["cue_padre_id"]]." AND " . 
					"`".$this->fields["cue_tree_position"]."` > ".$node[$this->fields["cue_tree_position"]];
			$sql[] = "" . 
				"UPDATE `".$this->table."` " . 
					"SET `".$this->fields["cue_tree_left"]."` = `".$this->fields["cue_tree_left"]."` - ".$ndif." " . 
				"WHERE cue_ges_id='".$cue_ges_id."' AND `".$this->fields["cue_tree_left"]."` > ".$node[$this->fields["cue_tree_right"]];
			$sql[] = "" . 
				"UPDATE `".$this->table."` " . 
					"SET `".$this->fields["cue_tree_right"]."` = `".$this->fields["cue_tree_right"]."` - ".$ndif." " . 
				"WHERE  cue_ges_id='".$cue_ges_id."' AND " . 
					"`".$this->fields["cue_tree_right"]."` > ".$node[$this->fields["cue_tree_left"]]." AND " . 
					"`".$this->fields["cue_id"]."` NOT IN (".implode(",", $node_ids).") ";
		}
		// Preparing new parent
		$sql[] = "" . 
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["cue_tree_position"]."` = `".$this->fields["cue_tree_position"]."` + 1 " . 
			"WHERE  cue_ges_id='".$cue_ges_id."' AND " . 
				"`".$this->fields["cue_padre_id"]."` = ".$ref_id." AND " . 
				"`".$this->fields["cue_tree_position"]."` >= ".$cue_tree_position." " . 
				( $is_copy ? "" : " AND `".$this->fields["cue_id"]."` NOT IN (".implode(",", $node_ids).") ");

		$ref_ind = $ref_id === 0 ? (int)$rchildren[count($rchildren) - 1][$this->fields["cue_tree_right"]] + 1 : (int)$ref_node[$this->fields["cue_tree_right"]];
		$ref_ind = max($ref_ind, 1);

		$self = ($node !== false && !$is_copy && (int)$node[$this->fields["cue_padre_id"]] == $ref_id && $cue_tree_position > $node[$this->fields["cue_tree_position"]]) ? 1 : 0;
		foreach($rchildren as $k => $v) {
			if($v[$this->fields["cue_tree_position"]] - $self == $cue_tree_position) {
				$ref_ind = (int)$v[$this->fields["cue_tree_left"]];
				break;
			}
		}
		if($node !== false && !$is_copy && $node[$this->fields["cue_tree_left"]] < $ref_ind) {
			$ref_ind -= $ndif;
		}

		$sql[] = "" . 
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["cue_tree_left"]."` = `".$this->fields["cue_tree_left"]."` + ".$ndif." " . 
			"WHERE  cue_ges_id='".$cue_ges_id."' AND " . 
				"`".$this->fields["cue_tree_left"]."` >= ".$ref_ind." " . 
				( $is_copy ? "" : " AND `".$this->fields["cue_id"]."` NOT IN (".implode(",", $node_ids).") ");
		$sql[] = "" . 
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["cue_tree_right"]."` = `".$this->fields["cue_tree_right"]."` + ".$ndif." " . 
			"WHERE  cue_ges_id='".$cue_ges_id."' AND " . 
				"`".$this->fields["cue_tree_right"]."` >= ".$ref_ind." " . 
				( $is_copy ? "" : " AND `".$this->fields["cue_id"]."` NOT IN (".implode(",", $node_ids).") ");

		$ldif = $ref_id == 0 ? 0 : $ref_node[$this->fields["cue_tree_level"]] + 1;
		$idif = $ref_ind;
		if($node !== false) {
			$ldif = $node[$this->fields["cue_tree_level"]] - ($ref_node[$this->fields["cue_tree_level"]] + 1);
			$idif = $node[$this->fields["cue_tree_left"]] - $ref_ind;
			if($is_copy) {
				$sql[] = "" . 
					"INSERT INTO `".$this->table."` (" . 
						"`".$this->fields["cue_padre_id"]."`, " . 
						"`".$this->fields["cue_tree_position"]."`, " . 
						"`".$this->fields["cue_tree_left"]."`, " . 
						"`".$this->fields["cue_tree_right"]."`, " . 
						"`".$this->fields["cue_tree_level"]."`" .		
					") " . 
						"SELECT " . 
							"".$ref_id.", " . 
							"`".$this->fields["cue_tree_position"]."`, " . 
							"`".$this->fields["cue_tree_left"]."` - (".($idif + ($node[$this->fields["cue_tree_left"]] >= $ref_ind ? $ndif : 0))."), " . 
							"`".$this->fields["cue_tree_right"]."` - (".($idif + ($node[$this->fields["cue_tree_left"]] >= $ref_ind ? $ndif : 0))."), " . 
							"`".$this->fields["cue_tree_level"]."` - (".$ldif.") " . 
						"FROM `".$this->table."` " . 
						"WHERE " . 
							"`".$this->fields["cue_id"]."` IN (".implode(",", $node_ids).") " . 
						"ORDER BY `".$this->fields["cue_tree_level"]."` ASC";
			}
			else {
				$sql[] = "" . 
					"UPDATE `".$this->table."` SET " . 
						"`".$this->fields["cue_padre_id"]."` = ".$ref_id.", " . 
						"`".$this->fields["cue_tree_position"]."` = ".$cue_tree_position." " . 
					"WHERE  cue_ges_id='".$cue_ges_id."' AND " . 
						"`".$this->fields["cue_id"]."` = ".$cue_id;
				$sql[] = "" . 
					"UPDATE `".$this->table."` SET " . 
						"`".$this->fields["cue_tree_left"]."` = `".$this->fields["cue_tree_left"]."` - (".$idif."), " . 
						"`".$this->fields["cue_tree_right"]."` = `".$this->fields["cue_tree_right"]."` - (".$idif."), " . 
						"`".$this->fields["cue_tree_level"]."` = `".$this->fields["cue_tree_level"]."` - (".$ldif.") " . 
					"WHERE  cue_ges_id='".$cue_ges_id."' AND " . 
						"`".$this->fields["cue_id"]."` IN (".implode(",", $node_ids).") ";
			}
		}
		else {
		
			$sql[] = "" . 
				"INSERT INTO `".$this->table."` (" . 
					"`".$this->fields["cue_padre_id"]."`, " . 
					"`".$this->fields["cue_tree_position"]."`, " . 
					"`".$this->fields["cue_tree_left"]."`, " . 
					"`".$this->fields["cue_tree_right"]."`, " . 
					"`".$this->fields["cue_tree_level"]."`, " . 
					"`cue_codigo`, " . 
					"`cue_ges_id`, " .
					"`cue_mon_id`" .
					") " . 
				"VALUES (" . 
					$ref_id.", " . 
					$cue_tree_position.", " . 
					$idif.", " . 
					($idif + 1).", " . 
					$ldif.", '" .
					$cue_codigo."', '".
					$cue_ges_id."', '".
					$cue_mon_id."' ".
					")";
					
		}
		foreach($sql as $q) { $this->db->query($q); }
		$ind = $this->db->insert_id();
		if($is_copy) $this->_fix_copy($ind, $cue_tree_position);
		return $node === false || $is_copy ? $ind : true;
	}
	function _fix_copy($cue_id, $cue_tree_position) {
		$node = $this->_get_node($cue_id);
		$children = $this->_get_children($cue_id, true);

		$map = array();
		for($i = $node[$this->fields["cue_tree_left"]] + 1; $i < $node[$this->fields["cue_tree_right"]]; $i++) {
			$map[$i] = $cue_id;
		}
		foreach($children as $cid => $child) {
			if((int)$cid == (int)$cue_id) {
				$this->db->query("UPDATE `".$this->table."` SET `".$this->fields["cue_tree_position"]."` = ".$cue_tree_position." WHERE `".$this->fields["cue_id"]."` = ".$cid);
				continue;
			}
			$this->db->query("UPDATE `".$this->table."` SET `".$this->fields["cue_padre_id"]."` = ".$map[(int)$child[$this->fields["cue_tree_left"]]]." WHERE `".$this->fields["cue_id"]."` = ".$cid);
			for($i = $child[$this->fields["cue_tree_left"]] + 1; $i < $child[$this->fields["cue_tree_right"]]; $i++) {
				$map[$i] = $cid;
			}
		}
	}
}

class json_tree extends _tree_struct { 
	function __construct($table = "con_cuenta", $fields = array(), $add_fields = array("cue_descripcion" => "cue_descripcion", "cue_tipo" => "cue_tipo")) {
		parent::__construct($table, $fields);
		$this->fields = array_merge($this->fields, $add_fields);
		$this->add_fields = $add_fields;
	}

	function create_node($data) {
                $sql_cons="select * from con_cuenta where cue_codigo='$_POST[cue_codigo]' and cue_ges_id='$_POST[cue_ges_id]'";
                $this->db->query($sql_cons);
                if($this->db->nf()>0){
                    return "{ \"status\" : 0, \"msj\":\"ya existe una cuenta con el mismo codigo\" }";
                }
		$cue_id = parent::_create((int)$data[$this->fields["cue_id"]], (int)$data[$this->fields["cue_tree_position"]]);
		if($cue_id) {
			$data["cue_id"] = $cue_id;
			$this->set_data($data);
			return  "{ \"status\" : 1, \"id\" : ".(int)$cue_id." }";
		}
		return "{ \"status\" : 0 }";
	}
	function set_data($data) {
                
		if(count($this->add_fields) == 0) { return "{ \"status\" : 1 }"; }
		$s = "UPDATE `".$this->table."` SET `".$this->fields["cue_id"]."` = `".$this->fields["cue_id"]."` "; 
		foreach($this->add_fields as $k => $v) {
			if(isset($data[$k]))	
                            $s .= ", `".$this->fields[$v]."` = \"".$this->db->escape($data[$k])."\" ";
			else					
                            $s .= ", `".$this->fields[$v]."` = `".$this->fields[$v]."` ";
		}
		$s .= ", cue_codigo = '".$_POST['cue_codigo']."',  cue_mon_id = '".$_POST['cue_mon_id']."' WHERE `".$this->fields["cue_id"]."` = ".(int)$data["cue_id"];
		
		$this->db->query($s);
		return "{ \"status\" : 1 }";
	}
	function rename_node($data) { 
            $sql_cons="select * from con_cuenta where cue_codigo='$_POST[cue_codigo]' and cue_ges_id='$_POST[cue_ges_id]' and cue_id!='$_POST[cue_id]'";
            $this->db->query($sql_cons);
            if($this->db->nf()>0){
                return "{ \"status\" : 0, \"msj\":\"ya existe una cuenta con el mismo codigo\" }";
            }
            return $this->set_data($data); 
            
        }

	function move_node($data) { 
            
		$cue_id = parent::_move((int)$data["cue_id"], (int)$data["ref"], (int)$data["cue_tree_position"], (int)$data["copy"]);
		if(!$cue_id) return "{ \"status\" : 0 }";
		if((int)$data["copy"] && count($this->add_fields)) {
			$ids	= array_keys($this->_get_children($cue_id, true));
			$data	= $this->_get_children((int)$data["cue_id"], true);
			
			$i = 0;
			foreach($data as $dk => $dv) {
				$s = "UPDATE `".$this->table."` SET `".$this->fields["cue_id"]."` = `".$this->fields["cue_id"]."` "; 
				foreach($this->add_fields as $k => $v) {
					if(isset($dv[$k]))	$s .= ", `".$this->fields[$v]."` = \"".$this->db->escape($dv[$k])."\" ";
					else				$s .= ", `".$this->fields[$v]."` = `".$this->fields[$v]."` ";
				}
				$s .= "WHERE `".$this->fields["cue_id"]."` = ".$ids[$i];
				$this->db->query($s);
				$i++;
			}
		}
		return "{ \"status\" : 1, \"id\" : ".$cue_id." }";
	}
	function remove_node($data) {
		$cue_id = parent::_remove((int)$data["cue_id"]);
		return "{ \"status\" : 1 }";
	}
	function get_children($data) {
	
		$tmp = $this->_get_children((int)$data["cue_id"]);
		if((int)$data["cue_id"] === 1 && count($tmp) === 0) {
			$tmp = $this->_get_children((int)$data["cue_id"]);
		}
		$result = array();
		if((int)$data["cue_id"] === 0) return json_encode($result);
		foreach($tmp as $k => $v) {
			$result[] = array(
				"attr" => array("id" => "node_".$k, "data-moneda" => $v["cue_mon_id"], "rel" => $v[$this->fields["cue_tipo"]],"level"=>$v[$this->fields["cue_tree_level"]]), //"data-posicion" => $v['cue_tree_position'],
				"data" => $v['cue_codigo']." | ".$v[$this->fields["cue_descripcion"]],
				"state" => ((int)$v[$this->fields["cue_tree_right"]] - (int)$v[$this->fields["cue_tree_left"]] > 1) ? "closed" : ""
			);
		}
		
		return json_encode($result);
	}
	function search($data) {
		$this->db->query("SELECT `".$this->fields["cue_tree_left"]."`, `".$this->fields["cue_tree_right"]."` FROM `".$this->table."` WHERE `".$this->fields["cue_descripcion"]."` LIKE '%".$this->db->escape($data["search_str"])."%'");
		if($this->db->nf() === 0) return "[]";
		$q = "SELECT DISTINCT `".$this->fields["cue_id"]."` FROM `".$this->table."` WHERE 0 ";
		while($this->db->nextr()) {
			$q .= " OR (`".$this->fields["cue_tree_left"]."` < ".(int)$this->db->f(0)." AND `".$this->fields["cue_tree_right"]."` > ".(int)$this->db->f(1).") ";
		}
		$result = array();
		$this->db->query($q);
		while($this->db->nextr()) { $result[] = "#node_".$this->db->f(0); }
		return json_encode($result);
	}
}

?>