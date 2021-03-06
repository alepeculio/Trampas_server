<?php 
require_once "../clases/db.php";
require_once "../clases/Colocacion.php";

class Trampa {
	public $id;
	public $nombre;
	public $mac;

	public function __construct($id = 0, $nombre = "Trampa", $mac = "") {
		$this->id = $id;
		$this->nombre = $nombre;
		$this->mac = $mac;
	}

	public static function agregar($nombre, $mac){
		$sql = DB::conexion()->prepare("INSERT INTO trampas_trampa (nombre, mac) VALUES(?, ?)");
		
		if($sql == null)
			throw new Exception('Error de conexion con la BD.');

		$sql->bind_param("ss",$nombre, $mac);
		
		if ($sql->execute()){
			return $sql->insert_id;
		}else{
			return false;
		}
	}

	public static function eliminar($id){
		$activa = 0;
		$sql = DB::conexion()->prepare("UPDATE trampas_trampa SET activa=? WHERE id=?");
		
		if($sql == null)
			throw new Exception('Error de conexion con la BD.');

		$sql->bind_param("ii", $activa, $id);
		
		return $sql->execute();
	}

	public static function obtenerTrampas($leishmaniasis = false){
		$query;
		if($leishmaniasis)
			$query = "SELECT DISTINCT t.id,
									  t.nombre,
									  t.mac
					  FROM trampas_trampa AS t
					  INNER JOIN trampas_colocacion AS c
					  WHERE t.id = c.trampa
					  		AND t.activa=1
					  		AND c.leishmaniasis=1
					  ORDER BY t.id DESC";
		 else 
			$query = "SELECT * FROM trampas_trampa WHERE activa=1 ORDER BY id DESC";
		

		$sql = DB::conexion()->prepare($query);
		if($sql == null)
			throw new Exception('Error de conexion con la BD.');
		$sql->execute();
		$resTrampas=$sql->get_result();

		while ($filaTrampas=$resTrampas->fetch_object()) {
			$idTrampa = $filaTrampas->id;

			$sql2 = DB::conexion()->prepare("SELECT * FROM trampas_colocacion c WHERE c.trampa = ? AND c.fechaFin IS NULL");
			if($sql2 == null)
				throw new Exception('Error de conexion con la BD.');

			$sql2->bind_param("i", $idTrampa);
			$sql2->execute();
			$resColocacion=$sql2->get_result();

			if($resColocacion->num_rows == 0){
				$sql3 = DB::conexion()->prepare("SELECT * FROM trampas_colocacion c WHERE c.trampa = ? ORDER BY fechaFin DESC LIMIT 1");  
				if($sql2 == null)
					throw new Exception('Error de conexion con la BD.');
				$sql3->bind_param("i", $idTrampa);
				$sql3->execute();
				$resColocacion = $sql3->get_result();
			}

			if($resColocacion->num_rows != 0){
				$c = $resColocacion->fetch_object();
				$colocacion = new Colocacion();
				$colocacion->setIdColocacion($c->idColocacion);
				$colocacion->setLat($c->lat);
				$colocacion->setLon($c->lon);
				$colocacion->setTempProm($c->tempProm);
				$colocacion->setHumProm($c->humProm);
				$colocacion->setTempMin($c->tempMin);
				$colocacion->setTempMax($c->tempMax);
				$colocacion->setHumMin($c->humMin);
				$colocacion->setHumMax($c->humMax);
				$colocacion->setFechaInicio($c->fechaInicio);
				$colocacion->setFechaFin($c->fechaFin);
				$colocacion->setUsuario($c->usuario);
				unset( $colocacion->nombre );
				unset( $colocacion->id );
				unset( $colocacion->mac );
				$filaTrampas->colocacion = $colocacion;
			}

			$trampas[] = $filaTrampas;
		}
		return $trampas;
	}

	public static function obtenerTrampasNoColocadas(){
		$sql = DB::conexion()->prepare("
			SELECT *
			FROM trampas_trampa 
			WHERE activa=1 
				  AND id NOT IN (SELECT trampa 
				  				 FROM trampas_colocacion
				  				 WHERE fechaFin IS NULL)
			");

		if($sql == null)
			throw new Exception('Error de conexion con la BD.');

		$sql->execute();

		$resultado=$sql->get_result();
		while ($fila=$resultado->fetch_object()) {
			$trampas[] = $fila;
		}
		return $trampas;
	}

	public static function obtenerTrampasColocadas(){
		$sql = DB::conexion()->prepare("
			SELECT *
			FROM trampas_trampa
			WHERE activa=1 
				  AND id IN (SELECT trampa
				  			 FROM trampas_colocacion
				  			 WHERE fechaFin IS NULL)
			");

		if($sql == null)
			throw new Exception('Error de conexion con la BD.');

		$sql->execute();

		$resultado=$sql->get_result();
		while ($fila=$resultado->fetch_object()) {
			$trampas[] = $fila;
		}
		
		return $trampas;
	}


	//geters y setters.
	public function getNombre(){
		return $this->nombre;
	}

	public function getId(){
		return $this->id;
	}

	public function setNombre($nombre){
		$this->nombre = $nombre;
		return $this;
	}

	public function setId($id){
		$this->id = $id;
		return $this;
	}

	public function getMac(){
		return $this->mac;
	}

	public function setMac($mac){
		$this->mac = $mac;
		return $this;
	}

}

?>