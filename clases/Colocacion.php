<?php 
require_once "../clases/db.php";
require_once "../clases/Trampa.php";

class Colocacion extends Trampa{
	public $idColocacion;
	public $lat;
	public $lon;
	public $tempMin;
	public $tempMax;
	public $humMin;
	public $humMax;
    public $tempProm;
    public $humProm;
	public $fechaInicio;
	public $fechaFin;
	public $usuario;
	

	public function __construct($lat = 0, $lon = 0, $trampa = 0, $usuario = 0) {
		parent::__construct( $trampa );
		$this->lat = $lat;
		$this->lon = $lon;
		$this->usuario = $usuario;
		$this->fechaInicio = date_create(NULL, timezone_open("America/Montevideo"))->format('Y-m-d H:i:s');
	}


	function colocarTrampa(){
		$sql = DB::conexion()->prepare("INSERT INTO colocacion (lat, lon, fechaInicio, trampa, usuario) VALUES(?,?,?,?,?)");
		if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ddsii", $this->getLat(), $this->getLon(), $this->getFechaInicio(), $this->getId(),$this->getUsuario());
        if($sql->execute()){
            return $sql->insert_id;
        }else{
            return false;
        }
    }

    function extraerTrampa(){
        $sql = DB::conexion()->prepare("UPDATE colocacion SET tempMin=?, tempMax=?, humMin=?, humMax=?, tempProm=?, humProm=?, fechaFin=? WHERE trampa=? AND fechaFin IS NULL");
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ddddddsi", $this->getTempMin(), $this->getTempMax(), $this->getHumMin(), $this->getHumMax(),$this->getTempProm(), $this->getHumProm() ,$this->getFechaFin(),$this->getId());
        
        return $sql->execute();
    }

    public function obtenerColocacionesActivas(){
        //$sql = DB::conexion()->prepare("SELECT * FROM `colocacion` WHERE fechaFin IS NULL");
        $sql = DB::conexion()->prepare("SELECT c.idColocacion, c.lat, c.lon, c.tempMin, c.tempMax, c.humMin, c.humMax, c.tempProm, c.humProm, c.fechaInicio, c.fechaFin, c.usuario, c.trampa, t.nombre, t.mac FROM `colocacion` c INNER JOIN `trampa` t ON c.trampa = t.id WHERE t.activa = 1 AND c.fechaFin IS NULL");
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->execute();
        $resultado=$sql->get_result();
        while ($fila=$resultado->fetch_object()) {
         $trampa = new Trampa($fila->trampa, $fila->nombre, $fila->mac);
         $fila->trampa = $trampa;
         unset( $fila->nombre );
         unset( $fila->mac );
         $colocaciones[] = $fila;
        }
         return $colocaciones;
    }


    public function obtenerColocacionesTrampa( $idTrampa ){
        $sql = DB::conexion()->prepare("SELECT * FROM colocacion WHERE trampa =".$idTrampa. " ORDER BY idColocacion DESC");
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->execute();

        $resultado=$sql->get_result();
        while ($fila=$resultado->fetch_object()) {
            $fila->trampa = null;
            $colocaciones[] = $fila;
        }
        return $colocaciones;
    }

   /*public function obtenerUltimaColocacion($id){
       $sql = DB::conexion()->prepare("SELECT * FROM `colocacion` c WHERE c.trampa = ? AND c.fechaFin IS NULL");
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("i",$id);
        $sql->execute();
        $resultado=$sql->get_result();

        if($resultado->num_rows == 0){
            $sql2 = DB::conexion()->prepare("SELECT * FROM `colocacion` c WHERE c.trampa = ? ORDER BY fechaFin DESC LIMIT 1");  
            if($sql2 == null)
                throw new Exception('Error de conexion con la BD.');
            $sql2->bind_param("i",$id);
            $sql2->execute();
            $resultado = $sql2->get_result();
        }

        while ($fila=$resultado->fetch_object()) {
          $colocaciones[] = $fila;
        }
     return $colocaciones;
 }*/

    public function getIdColocacion(){
    	return $this->idColocacion;
    }

    public function setIdColocacion($idColocacion){
    	$this->idColocacion = $idColocacion;
    	return $this;
    }

    public function getLat(){
    	return $this->lat;
    }

    
    public function setLat($lat){
    	$this->lat = $lat;
    	return $this;
    }

  
    public function getLon(){
    	return $this->lon;
    }

   
    public function setLon($lon){
    	$this->lon = $lon;
    	return $this;
    }

    public function getTempMin(){
    	return $this->tempMin;
    }

 
    public function setTempMin($tempMin){
    	$this->tempMin = $tempMin;
    	return $this;
    }

    public function getTempMax(){
    	return $this->tempMax;
    }

    public function setTempMax($tempMax){
    	$this->tempMax = $tempMax;
    	return $this;
    }

    public function getHumMin(){
    	return $this->humMin;
    }


    public function setHumMin($humMin){
    	$this->humMin = $humMin;
    	return $this;
    }

    public function getHumMax(){
    	return $this->humMax;
    }

    public function setHumMax($humMax){
    	$this->humMax = $humMax;
    	return $this;
    }

    public function getFechaInicio(){
    	return $this->fechaInicio;
    }

    public function setFechaInicio($fechaInicio){
    	$this->fechaInicio = $fechaInicio;
    	return $this;
    }

    public function getFechaFin(){
    	return $this->fechaFin;
    }

  
    public function setFechaFin($fechaFin){
    	$this->fechaFin = $fechaFin;
    	return $this;
    }

    public function getUsuario(){
    	return $this->usuario;
    }

    public function setUsuario($usuario){
    	$this->usuario = $usuario;
    	return $this;
    }
 
    public function getTempProm(){
        return $this->tempProm;
    }

    public function setTempProm($tempProm){
        $this->tempProm = $tempProm;
        return $this;
    }

    public function getHumProm(){
        return $this->humProm;
    }

    public function setHumProm($humProm){
        $this->humProm = $humProm;
        return $this;
    }
}

?> 

