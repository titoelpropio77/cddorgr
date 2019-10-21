<?php

    require_once('mysql.php');
	
	class Dplan {
			
		  public $db;
		  public function Dplan()
		  {
			  $this->db = new QUERY();
		  }			  
	  
		  public function insert()
		  {
			  $table = $_POST['tableplan'];
			  $auxTable = $_POST['auxtable'];
			  $sql = "select count(*)as 'total' FROM $table WHERE ".$auxTable."_eliminado='No' and ".$auxTable."_codigo='".$_POST['codigo']."' 
			  and ".$auxTable."_ges_id='".$_POST['ges_id']."';";
			  $cuenta = mysql_fetch_array($this->db->consulta($sql));
			  if ($cuenta['total'] > 0){
			      $state = 1;
			  } else {
				  $state = 0;
				  $code = explode(".", $_POST['codigo']);
				  $type = $this->typeAccount($_POST['codigo']);
				  $level = $type[1];
				  $type = $type[0];
				  $idcuentapadre = $_POST['idplanpadre'];
				  $sql = "insert into $table(".$auxTable."_ges_id,".$auxTable."_codigo
				   ,".$auxTable."_descripcion,".$auxTable."_tipo,".$auxTable."_padre_id,".$auxTable."_eliminado,".$auxTable."_mon_id
				   ,".$auxTable."_tree_left,".$auxTable."_tree_right,".$auxTable."_tree_position,".$auxTable."_tree_level)
				   values('".$_POST['ges_id']."','".$_POST['codigo']."','".$_POST['cuenta']."','$type',
				   '$idcuentapadre','No','".$_POST['moneda']."','','','','$level');";
				  $this->db->consulta($sql);  
			  }
			  echo json_encode(array("state" => $state));
		  }	
		  
		  public function typeAccount($code)
		  {
			   $type = "Movimiento";
			   $code = explode(".", $code);
			   $level = 0;
			   for ($i = 0; $i < count($code); $i++){
				   if ((int)$code[$i] == 0) {
					   $type = "Titulo";
				   } else {
					   $level++;   
				   }
			   }
			   return array($type, $level);
		  }
		  	  
		  
		  public function update()
		  {
			  $table = $_POST['tableplan'];
			  $auxTable = $_POST['auxtable'];
			  $state = 0;
			  $code = explode(".", $_POST['codigo']);
			  $idcuentapadre = $_POST['idplanpadre'];
			  $sql = "update $table set ".$auxTable."_descripcion='".$_POST['cuenta']."',
			   ".$auxTable."_mon_id='".$_POST['moneda']."' where ".$auxTable."_id='$idcuentapadre';";
			  $this->db->consulta($sql); 
			  echo json_encode(array("state" => $state)); 
		  }		  
		  
		  public function anular()
		  {	
		      $table = $_POST['tableplan'];
			  $auxTable = $_POST['auxtable'];	   
			  $sql = "select count(*)as 'total' FROM $table WHERE ".$auxTable."_eliminado='No' and ".$auxTable."_padre_id='".$_POST['idplanpadre']."';";
			  $cuenta = mysql_fetch_array($this->db->consulta($sql));
			  if ($cuenta['total'] > 0){				  
				  $state = 1;
			  } else {
				  $sql = "select count(*)as 'total' FROM con_comprobante_detalle WHERE cde_{$auxTable}_id='".$_POST['idplanpadre']."';";
				  $cuenta = mysql_fetch_array($this->db->consulta($sql));
				  if ($cuenta['total'] > 0){				  
					  $state = 2;
				  } else {
					  $sql = "delete from $table where ".$auxTable."_id='".$_POST['idplanpadre']."';";
					  $this->db->consulta($sql);
					  $state = 0;
				  }
			  }
			  echo json_encode(array("state" => $state));
		  }
				
	}

    $data = new Dplan();
	switch($_POST['transaccion']) {
		case "insert":
		    $data->insert();
		break;
		case "update":
		    $data->update();
		break;
		case "anular":
		    $data->anular();
		break;
	}

?>