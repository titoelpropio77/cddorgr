<?php

class _tree_struct {
	// Structure table and fields
	protected $table	= "";
	protected $fields	= array(
			"can_id"		=> false,
			"can_padre_id"	=> false,
			"can_tree_position"	=> false,
			"can_tree_left"		=> false,
			"can_tree_right"		=> false,
			"can_tree_level"		=> false
		);
		
	// Constructor
	function __construct($table = "con_cuenta_ca", $fields = array()) {
		$this->table = $table;
		if(!count($fields)) {
			foreach($this->fields as $k => &$v) { $v = $k; }
		}
		else {
			foreach($fields as $key => $field) {
				switch($key) {
					case "can_id":
					case "can_padre_id":
					case "can_tree_position":
					case "can_tree_left":
					case "can_tree_right":
					case "can_tree_level":
						$this->fields[$key] = $field;
						break;
				}
			}
		}
		// Database
		$this->db = new _database;
	}

	function _get_node($can_id) {
		$this->db->query("SELECT `".implode("` , `", $this->fields)."`, `can_codigo` FROM `".$this->table."` WHERE can_ges_id='".$_POST["can_ges_id"]."' AND `".$this->fields["can_id"]."` = ".(int) $can_id);
		$this->db->nextr();
		return $this->db->nf() === 0 ? false : $this->db->get_row("assoc");
	}
	function _get_children($can_id, $recursive = false) {
	
		$children = array();
		if($recursive) {
			$node = $this->_get_node($can_id);
			$this->db->query("SELECT `".implode("` , `", $this->fields)."`, `can_codigo` FROM `".$this->table."` WHERE  can_ges_id='".$_GET["can_ges_id"]."' AND `".$this->fields["can_tree_left"]."` >= ".(int) $node[$this->fields["can_tree_left"]]." AND `".$this->fields["can_tree_right"]."` <= ".(int) $node[$this->fields["can_tree_right"]]." ORDER BY `".$this->fields["can_tree_left"]."` DESC");
		}
		else {
			$this->db->query("SELECT `".implode("` , `", $this->fields)."`, `can_codigo` FROM `".$this->table."` WHERE can_ges_id='".$_GET["can_ges_id"]."' AND `".$this->fields["can_padre_id"]."` = ".(int) $can_id." ORDER BY `".$this->fields["can_tree_position"]."` DESC");
		}
		while($this->db->nextr()) $children[$this->db->f($this->fields["can_id"])] = $this->db->get_row("assoc");
		return $children;
	}
	function _get_path($can_id) {
	
		$node = $this->_get_node($can_id);
		$path = array();
		if(!$node === false) return false;
		$this->db->query("SELECT `".implode("` , `", $this->fields)."`, `can_codigo` FROM `".$this->table."` WHERE `".$this->fields["can_tree_left"]."` <= ".(int) $node[$this->fields["can_tree_left"]]." AND `".$this->fields["can_tree_right"]."` >= ".(int) $node[$this->fields["can_tree_right"]]);
		while($this->db->nextr()) $path[$this->db->f($this->fields["can_id"])] = $this->db->get_row("assoc");
		return $path;
	}

	function _create($parent, $can_tree_position) {
		return $this->_move(0, $parent, $can_tree_position);
	}
	
	function _remove($can_id) {
		
		$can_ges_id = $_POST["can_ges_id"];
		
		if((int)$can_id === 1) { return false; }
		$data = $this->_get_node($can_id);
		$lft = (int)$data[$this->fields["can_tree_left"]];
		$rgt = (int)$data[$this->fields["can_tree_right"]];
		$dif = $rgt - $lft + 1;

		// deleting node and its children
		$this->db->query("".
			"DELETE FROM `".$this->table."` " . 
			"WHERE can_ges_id='".$can_ges_id."' AND `".$this->fields["can_tree_left"]."` >= ".$lft." AND `".$this->fields["can_tree_right"]."` <= ".$rgt
		);
		// shift can_tree_left indexes of nodes can_tree_right of the node
		$this->db->query("".
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["can_tree_left"]."` = `".$this->fields["can_tree_left"]."` - ".$dif." " . 
			"WHERE  can_ges_id='".$can_ges_id."' AND `".$this->fields["can_tree_left"]."` > ".$rgt
		);
		// shift can_tree_right indexes of nodes can_tree_right of the node and the node's parents
		$this->db->query("" . 
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["can_tree_right"]."` = `".$this->fields["can_tree_right"]."` - ".$dif." " . 
			"WHERE  can_ges_id='".$can_ges_id."' AND  `".$this->fields["can_tree_right"]."` > ".$lft
		);

		$pid = (int)$data[$this->fields["can_padre_id"]];
		$pos = (int)$data[$this->fields["can_tree_position"]];

		// Update can_tree_position of siblings below the deleted node
		$this->db->query("" . 
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["can_tree_position"]."` = `".$this->fields["can_tree_position"]."` - 1 " . 
			"WHERE  can_ges_id='".$can_ges_id."' AND `".$this->fields["can_padre_id"]."` = ".$pid." AND `".$this->fields["can_tree_position"]."` > ".$pos
		);
		return true;
	}
	
	function _move($can_id, $ref_id, $can_tree_position = 0, $is_copy = false)
	{
	
		$can_codigo = $_POST["can_codigo"];
		$can_ges_id = $_POST["can_ges_id"];
		
		if((int)$ref_id === 0 || (int)$can_id === 1) { return false; }
		$sql		= array();						// Queries executed at the end
		$node		= $this->_get_node($can_id);		// Node data
		$nchildren	= $this->_get_children($can_id);	// Node children
		$ref_node	= $this->_get_node($ref_id);	// Ref node data
		$rchildren	= $this->_get_children($ref_id);// Ref node children

		$ndif = 2;
		$node_ids = array(-1);
		if($node !== false) {
			$node_ids = array_keys($this->_get_children($can_id, true));
			// TODO: should be !$is_copy && , but if copied to self - screws some can_tree_right indexes
			if(in_array($ref_id, $node_ids)) return false;
			$ndif = $node[$this->fields["can_tree_right"]] - $node[$this->fields["can_tree_left"]] + 1;
		}
		if($can_tree_position >= count($rchildren)) {
			$can_tree_position = count($rchildren);
		}

		// Not creating or copying - old parent is cleaned
		if($node !== false && $is_copy == false) {
			$sql[] = "" . 
				"UPDATE `".$this->table."` " . 
					"SET `".$this->fields["can_tree_position"]."` = `".$this->fields["can_tree_position"]."` - 1 " . 
				"WHERE can_ges_id='".$can_ges_id."' AND " . 
					"`".$this->fields["can_padre_id"]."` = ".$node[$this->fields["can_padre_id"]]." AND " . 
					"`".$this->fields["can_tree_position"]."` > ".$node[$this->fields["can_tree_position"]];
			$sql[] = "" . 
				"UPDATE `".$this->table."` " . 
					"SET `".$this->fields["can_tree_left"]."` = `".$this->fields["can_tree_left"]."` - ".$ndif." " . 
				"WHERE can_ges_id='".$can_ges_id."' AND `".$this->fields["can_tree_left"]."` > ".$node[$this->fields["can_tree_right"]];
			$sql[] = "" . 
				"UPDATE `".$this->table."` " . 
					"SET `".$this->fields["can_tree_right"]."` = `".$this->fields["can_tree_right"]."` - ".$ndif." " . 
				"WHERE  can_ges_id='".$can_ges_id."' AND " . 
					"`".$this->fields["can_tree_right"]."` > ".$node[$this->fields["can_tree_left"]]." AND " . 
					"`".$this->fields["can_id"]."` NOT IN (".implode(",", $node_ids).") ";
		}
		// Preparing new parent
		$sql[] = "" . 
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["can_tree_position"]."` = `".$this->fields["can_tree_position"]."` + 1 " . 
			"WHERE  can_ges_id='".$can_ges_id."' AND " . 
				"`".$this->fields["can_padre_id"]."` = ".$ref_id." AND " . 
				"`".$this->fields["can_tree_position"]."` >= ".$can_tree_position." " . 
				( $is_copy ? "" : " AND `".$this->fields["can_id"]."` NOT IN (".implode(",", $node_ids).") ");

		$ref_ind = $ref_id === 0 ? (int)$rchildren[count($rchildren) - 1][$this->fields["can_tree_right"]] + 1 : (int)$ref_node[$this->fields["can_tree_right"]];
		$ref_ind = max($ref_ind, 1);

		$self = ($node !== false && !$is_copy && (int)$node[$this->fields["can_padre_id"]] == $ref_id && $can_tree_position > $node[$this->fields["can_tree_position"]]) ? 1 : 0;
		foreach($rchildren as $k => $v) {
			if($v[$this->fields["can_tree_position"]] - $self == $can_tree_position) {
				$ref_ind = (int)$v[$this->fields["can_tree_left"]];
				break;
			}
		}
		if($node !== false && !$is_copy && $node[$this->fields["can_tree_left"]] < $ref_ind) {
			$ref_ind -= $ndif;
		}

		$sql[] = "" . 
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["can_tree_left"]."` = `".$this->fields["can_tree_left"]."` + ".$ndif." " . 
			"WHERE  can_ges_id='".$can_ges_id."' AND " . 
				"`".$this->fields["can_tree_left"]."` >= ".$ref_ind." " . 
				( $is_copy ? "" : " AND `".$this->fields["can_id"]."` NOT IN (".implode(",", $node_ids).") ");
		$sql[] = "" . 
			"UPDATE `".$this->table."` " . 
				"SET `".$this->fields["can_tree_right"]."` = `".$this->fields["can_tree_right"]."` + ".$ndif." " . 
			"WHERE  can_ges_id='".$can_ges_id."' AND " . 
				"`".$this->fields["can_tree_right"]."` >= ".$ref_ind." " . 
				( $is_copy ? "" : " AND `".$this->fields["can_id"]."` NOT IN (".implode(",", $node_ids).") ");

		$ldif = $ref_id == 0 ? 0 : $ref_node[$this->fields["can_tree_level"]] + 1;
		$idif = $ref_ind;
		if($node !== false) {
			$ldif = $node[$this->fields["can_tree_level"]] - ($ref_node[$this->fields["can_tree_level"]] + 1);
			$idif = $node[$this->fields["can_tree_left"]] - $ref_ind;
			if($is_copy) {
				$sql[] = "" . 
					"INSERT INTO `".$this->table."` (" . 
						"`".$this->fields["can_padre_id"]."`, " . 
						"`".$this->fields["can_tree_position"]."`, " . 
						"`".$this->fields["can_tree_left"]."`, " . 
						"`".$this->fields["can_tree_right"]."`, " . 
						"`".$this->fields["can_tree_level"]."`" .		
					") " . 
						"SELECT " . 
							"".$ref_id.", " . 
							"`".$this->fields["can_tree_position"]."`, " . 
							"`".$this->fields["can_tree_left"]."` - (".($idif + ($node[$this->fields["can_tree_left"]] >= $ref_ind ? $ndif : 0))."), " . 
							"`".$this->fields["can_tree_right"]."` - (".($idif + ($node[$this->fields["can_tree_left"]] >= $ref_ind ? $ndif : 0))."), " . 
							"`".$this->fields["can_tree_level"]."` - (".$ldif.") " . 
						"FROM `".$this->table."` " . 
						"WHERE " . 
							"`".$this->fields["can_id"]."` IN (".implode(",", $node_ids).") " . 
						"ORDER BY `".$this->fields["can_tree_level"]."` ASC";
			}
			else {
				$sql[] = "" . 
					"UPDATE `".$this->table."` SET " . 
						"`".$this->fields["can_padre_id"]."` = ".$ref_id.", " . 
						"`".$this->fields["can_tree_position"]."` = ".$can_tree_position." " . 
					"WHERE  can_ges_id='".$can_ges_id."' AND " . 
						"`".$this->fields["can_id"]."` = ".$can_id;
				$sql[] = "" . 
					"UPDATE `".$this->table."` SET " . 
						"`".$this->fields["can_tree_left"]."` = `".$this->fields["can_tree_left"]."` - (".$idif."), " . 
						"`".$this->fields["can_tree_right"]."` = `".$this->fields["can_tree_right"]."` - (".$idif."), " . 
						"`".$this->fields["can_tree_level"]."` = `".$this->fields["can_tree_level"]."` - (".$ldif.") " . 
					"WHERE  can_ges_id='".$can_ges_id."' AND " . 
						"`".$this->fields["can_id"]."` IN (".implode(",", $node_ids).") ";
			}
		}
		else {
		
			$sql[] = "" . 
				"INSERT INTO `".$this->table."` (" . 
					"`".$this->fields["can_padre_id"]."`, " . 
					"`".$this->fields["can_tree_position"]."`, " . 
					"`".$this->fields["can_tree_left"]."`, " . 
					"`".$this->fields["can_tree_right"]."`, " . 
					"`".$this->fields["can_tree_level"]."`, " . 
					"`can_codigo`, " .
					"`can_ges_id`" .	
					") " . 
				"VALUES (" . 
					$ref_id.", " . 
					$can_tree_position.", " . 
					$idif.", " . 
					($idif + 1).", " . 
					$ldif.", '" .
					$can_codigo."', '".
					$can_ges_id."'".
					")";
					
		}
		foreach($sql as $q) { $this->db->query($q); }
		$ind = $this->db->insert_id();
		if($is_copy) $this->_fix_copy($ind, $can_tree_position);
		return $node === false || $is_copy ? $ind : true;
	}
	function _fix_copy($can_id, $can_tree_position) {
		$node = $this->_get_node($can_id);
		$children = $this->_get_children($can_id, true);

		$map = array();
		for($i = $node[$this->fields["can_tree_left"]] + 1; $i < $node[$this->fields["can_tree_right"]]; $i++) {
			$map[$i] = $can_id;
		}
		foreach($children as $cid => $child) {
			if((int)$cid == (int)$can_id) {
				$this->db->query("UPDATE `".$this->table."` SET `".$this->fields["can_tree_position"]."` = ".$can_tree_position." WHERE `".$this->fields["can_id"]."` = ".$cid);
				continue;
			}
			$this->db->query("UPDATE `".$this->table."` SET `".$this->fields["can_padre_id"]."` = ".$map[(int)$child[$this->fields["can_tree_left"]]]." WHERE `".$this->fields["can_id"]."` = ".$cid);
			for($i = $child[$this->fields["can_tree_left"]] + 1; $i < $child[$this->fields["can_tree_right"]]; $i++) {
				$map[$i] = $cid;
			}
		}
	}
}

class json_tree extends _tree_struct { 
	function __construct($table = "con_cuenta_ca", $fields = array(), $add_fields = array("can_descripcion" => "can_descripcion", "can_tipo" => "can_tipo")) {
            parent::__construct($table, $fields);
            $this->fields = array_merge($this->fields, $add_fields);
            $this->add_fields = $add_fields;
	}

	function create_node($data) {
            $can_id = parent::_create((int)$data[$this->fields["can_id"]], (int)$data[$this->fields["can_tree_position"]]);
            if($can_id) {
                    $data["can_id"] = $can_id;
                    $this->set_data($data);
                    return  "{ \"status\" : 1, \"id\" : ".(int)$can_id." }";
            }
            return "{ \"status\" : 0 }";
	}
	function set_data($data) {
		if(count($this->add_fields) == 0) { return "{ \"status\" : 1 }"; }
		$s = "UPDATE `".$this->table."` SET `".$this->fields["can_id"]."` = `".$this->fields["can_id"]."` "; 
		foreach($this->add_fields as $k => $v) {
			if(isset($data[$k]))	$s .= ", `".$this->fields[$v]."` = \"".$this->db->escape($data[$k])."\" ";
			else					$s .= ", `".$this->fields[$v]."` = `".$this->fields[$v]."` ";
		}
		$s .= ", can_codigo = '".$_POST['can_codigo']."' WHERE `".$this->fields["can_id"]."` = ".(int)$data["can_id"];
		
		$this->db->query($s);
		return "{ \"status\" : 1 }";
	}
	function rename_node($data) { return $this->set_data($data); }

	function move_node($data) { 
		$can_id = parent::_move((int)$data["can_id"], (int)$data["ref"], (int)$data["can_tree_position"], (int)$data["copy"]);
		if(!$can_id) return "{ \"status\" : 0 }";
		if((int)$data["copy"] && count($this->add_fields)) {
			$ids	= array_keys($this->_get_children($can_id, true));
			$data	= $this->_get_children((int)$data["can_id"], true);
			
			$i = 0;
			foreach($data as $dk => $dv) {
				$s = "UPDATE `".$this->table."` SET `".$this->fields["can_id"]."` = `".$this->fields["can_id"]."` "; 
				foreach($this->add_fields as $k => $v) {
					if(isset($dv[$k]))	$s .= ", `".$this->fields[$v]."` = \"".$this->db->escape($dv[$k])."\" ";
					else				$s .= ", `".$this->fields[$v]."` = `".$this->fields[$v]."` ";
				}
				$s .= "WHERE `".$this->fields["can_id"]."` = ".$ids[$i];
				$this->db->query($s);
				$i++;
			}
		}
		return "{ \"status\" : 1, \"id\" : ".$can_id." }";
	}
	function remove_node($data) {
		$can_id = parent::_remove((int)$data["can_id"]);
		return "{ \"status\" : 1 }";
	}
	function get_children($data) {
	
		$tmp = $this->_get_children((int)$data["can_id"]);
		if((int)$data["can_id"] === 1 && count($tmp) === 0) {
			$tmp = $this->_get_children((int)$data["can_id"]);
		}
		$result = array();
		if((int)$data["can_id"] === 0) return json_encode($result);
		foreach($tmp as $k => $v) {
			$result[] = array(
				"attr" => array("id" => "node_".$k, "data-posicion" => $v['can_tree_position'], "rel" => $v[$this->fields["can_tipo"]],"level"=>$v[$this->fields["can_tree_level"]]), //"data-posicion" => $v['can_tree_position'],
				"data" => $v['can_codigo']." | ".$v[$this->fields["can_descripcion"]],
				"state" => ((int)$v[$this->fields["can_tree_right"]] - (int)$v[$this->fields["can_tree_left"]] > 1) ? "closed" : ""
			);
		}
		
		return json_encode($result);
	}
	function search($data) {
		$this->db->query("SELECT `".$this->fields["can_tree_left"]."`, `".$this->fields["can_tree_right"]."` FROM `".$this->table."` WHERE `".$this->fields["can_descripcion"]."` LIKE '%".$this->db->escape($data["search_str"])."%'");
		if($this->db->nf() === 0) return "[]";
		$q = "SELECT DISTINCT `".$this->fields["can_id"]."` FROM `".$this->table."` WHERE 0 ";
		while($this->db->nextr()) {
			$q .= " OR (`".$this->fields["can_tree_left"]."` < ".(int)$this->db->f(0)." AND `".$this->fields["can_tree_right"]."` > ".(int)$this->db->f(1).") ";
		}
		$result = array();
		$this->db->query($q);
		while($this->db->nextr()) { $result[] = "#node_".$this->db->f(0); }
		return json_encode($result);
	}
}

?>