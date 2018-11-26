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
    public $leishmaniasis;
    public $flevotomo;
    public $perros;
	public $usuario;
  
	

	public function __construct($lat = 0, $lon = 0, $trampa = 0, $usuario = 0) {
		parent::__construct( $trampa );
		$this->lat = $lat;
		$this->lon = $lon;
		$this->usuario = $usuario;
		$this->fechaInicio = date_create(NULL, timezone_open("America/Montevideo"))->format('Y-m-d H:i:s');
	}


	/*function colocarTrampa(){
		$sql = DB::conexion()->prepare("INSERT INTO colocacion (lat, lon, fechaInicio, trampa, usuario) VALUES(?,?,?,?,?)");
		if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ddsii", $this->getLat(), $this->getLon(), $this->getFechaInicio(), $this->getId(),$this->getUsuario());
        if($sql->execute()){
            return $sql->insert_id;
        }else{
            return false;
        }
    }*/

    public function colocarTrampa(){
        //Obtener periodos de la trampa de la colocacion actual
        $sql = DB::conexion()->prepare("
            SELECT COUNT(p.id) AS cantidad,
                   p.id 
            FROM periodo AS p 
            LEFT JOIN colocacion AS c ON p.colocacion = c.idColocacion
            WHERE c.trampa = ?
            GROUP BY p.id
            ORDER BY p.id DESC
            ");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("i", $this->getId());
        $sql->execute();
        $resultado = $sql->get_result();

        if($resultado->num_rows != null){
            while ($fila = $resultado -> fetch_object()) {
                $coso[] = $fila;
                if( $fila->cantidad < 3){
                    return $this->insertarTrampa($fila->id);
                }else{
                    return $this->insertarTrampa();
                }
            }
            return $coso;
        }else{
           return $this->insertarTrampa();
        }
    }

    public function insertarTrampa($id = 0){
        $sql = DB::conexion()->prepare("INSERT INTO colocacion (lat, lon, fechaInicio, trampa, usuario) VALUES(?,?,?,?,?)");
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ddsii", $this->getLat(), $this->getLon(), $this->getFechaInicio(), $this->getId(),$this->getUsuario());
        if($sql->execute()){
            $idColocacion = $sql->insert_id;

            $sql2 = DB::conexion()->prepare("INSERT INTO periodo (id, colocacion) VALUES(?,?)");
            if($sql2 == null)
                throw new Exception('Error de conexion con la BD.');
            
            $sql2->bind_param("ii", $id, $idColocacion);
            
            if($sql2->execute()){
                return $idColocacion;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function extraerTrampa(){
        $sql = DB::conexion()->prepare("
            UPDATE colocacion 
            SET tempMin=?,
                tempMax=?,
                humMin=?,
                humMax=?,
                tempProm=?,
                humProm=?,
                fechaFin=?
            WHERE trampa=? AND fechaFin IS NULL
            ");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ddddddsi", $this->getTempMin(), $this->getTempMax(), $this->getHumMin(), $this->getHumMax(),$this->getTempProm(), $this->getHumProm() ,$this->getFechaFin(),$this->getId());
        
        return $sql->execute();
    }

    public function obtenerColocacionesActivas(){
        //$sql = DB::conexion()->prepare("SELECT * FROM `colocacion` WHERE fechaFin IS NULL");
        $sql = DB::conexion()->prepare("
            SELECT c.idColocacion,
                   c.lat, c.lon,
                   c.tempMin,
                   c.tempMax,
                   c.humMin,
                   c.humMax,
                   c.tempProm,
                   c.humProm,
                   c.fechaInicio,
                   c.fechaFin,
                   c.leishmaniasis,
                   c.flevotomo,
                   c.perros,
                   c.usuario,
                   p.id AS periodo,
                   c.trampa,
                   t.nombre,
                   t.mac
            FROM colocacion c 
            INNER JOIN trampa t ON c.trampa = t.id 
            INNER JOIN periodo p ON c.idColocacion = p.colocacion
            WHERE t.activa = 1 
            ORDER BY c.fechaInicio DESC/* AND c.fechaFin IS NULL*/
            ");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->execute();
        $resultado=$sql->get_result();
        while ($fila=$resultado->fetch_object()) {
         $trampa = new Trampa($fila->trampa, $fila->nombre, $fila->mac);
         $fila->trampa = $trampa;
         unset( $fila->nombre );
         unset( $fila->mac );
         $fila->leishmaniasis = (boolean)$fila->leishmaniasis;
         $colocaciones[] = $fila;
        }
         return $colocaciones;
    }


    public function obtenerColocacionesTrampa( $idTrampa ){
        $sql = DB::conexion()->prepare("
            SELECT c.idColocacion,
                   c.lat, c.lon,
                   c.tempMin,
                   c.tempMax,
                   c.humMin,
                   c.humMax,
                   c.tempProm,
                   c.humProm,
                   c.fechaInicio,
                   c.fechaFin,
                   c.leishmaniasis,
                   c.flevotomo,
                   c.perros,
                   c.usuario,
                   p.id AS periodo
            FROM colocacion c 
            INNER JOIN periodo p ON c.idColocacion = p.colocacion
            WHERE c.trampa = ?
            ORDER BY c.fechaInicio DESC
            ");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("i",$idTrampa);

        $sql->execute();

        $resultado=$sql->get_result();
        while ($fila=$resultado->fetch_object()) {
            $fila->leishmaniasis = (boolean)$fila->leishmaniasis;
            $colocaciones[] = $fila;
        }
        return $colocaciones;
    }

    public function actualizarUbicacion($id){
        $lat = $this->getLat();
        $lon = $this->getLon();
        $sql = DB::conexion()->prepare("UPDATE colocacion SET lat=?, lon=? WHERE idColocacion=?");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ddi",$lat, $lon, $id);
        
        return $sql->execute();
    }

    public function actualizar($id){
        $lat = $this->getLat();
        $lon = $this->getLon();
        $tempMin = $this->getTempMin();
        $tempMax = $this->getTempMax();
        $tempProm = $this->getTempProm();
        $humMin = $this->getHumMin();
        $humMax = $this->getHumMax();
        $humProm = $this->getHumProm();
        $fechaInicio = $this->getFechaInicio();
        $fechaFin = $this->getFechaFin();
        $leishmaniasis = $this->getLeishmaniasis();
        $flevotomo = $this->getFlevotomo();
        $perros = $this->getPerros();

        $sql = DB::conexion()->prepare("
            UPDATE colocacion
            SET lat=?,
                lon=?,
                tempMin=?,
                tempMax=?,
                humMin=?,
                humMax=?,
                tempProm=?,
                humProm=?,
                fechaInicio=?,
                fechaFin=?,
                leishmaniasis=?,
                flevotomo=?,
                perros=?
            WHERE idColocacion=?
            ");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ddssssssssiiii",$lat, $lon, $tempMin, $tempMax, $humMin, $humMax, $tempProm, $humProm, $fechaInicio, $fechaFin, $leishmaniasis, $flevotomo, $perros, $id);
        
        return $sql->execute();
    }

    public function obtenerColocacion($id){
        $sql = DB::conexion()->prepare("SELECT * FROM colocacion WHERE idColocacion=?");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("i", $id);
        $sql->execute();
       
        $colocacion;

        $resultado = $sql->get_result();

        while ($c = $resultado->fetch_object()) {
           $c->leishmaniasis = (boolean)$c->leishmaniasis;
            $colocacion[] = $c;
        }
        return $colocacion;
    }

    public function obtenerColocacionesGrafica($idPeriodo){
         $sql = DB::conexion()->prepare("
            SELECT c.idColocacion,
                   c.lat, 
                   c.lon,
                   c.tempMin,
                   c.tempMax,
                   c.humMin,
                   c.humMax,
                   c.tempProm,
                   c.humProm,
                   c.fechaInicio,
                   c.fechaFin,
                   c.leishmaniasis,
                   c.usuario,
                   p.id AS periodo
            FROM periodo AS p 
            RIGHT JOIN colocacion AS c ON p.colocacion = c.idColocacion 
            WHERE p.id = ?
            ");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("i", $idPeriodo);
        $sql->execute();
        
        $resultado = $sql->get_result();

        $colocaciones = [];
        while ($fila = $resultado->fetch_object()) {
          $fila->trampa = null;
          $fila->leishmaniasis = (boolean)$fila->leishmaniasis;
          $colocaciones[] = $fila;
        }
        return $colocaciones;
    }

    public function enviarCorreoCSV($correo, $desde, $hasta) {
        // This will provide plenty adequate entropy
        $multipartSep = '-----'.md5(time()).'-----';

        $headers = array(
            "From: trampas.paysandu@gmail.com", //hospitalwebuy@gmail.com;
            "Reply-To: trampas.paysandu@gmail.com",
            "Content-Type: multipart/mixed; boundary=".$multipartSep
        );

        $csv = $this->generarCSV($desde, $hasta);
        if($csv == -1){
            return $csv;
        }else{
            $attachment = chunk_split(base64_encode($csv));
        }

        
        $body =
            "--$multipartSep\r\n"
            . "Content-Type: text/plain; charset=ISO-8859-1; format=flowed\r\n"
            . "Content-Transfer-Encoding: 7bit\r\n"
            . "\r\n"
            . "$body\r\n"
            . "--$multipartSep\r\n"
            . "Content-Type: text/csv\r\n"
            . "Content-Transfer-Encoding: base64\r\n"
            . "Content-Disposition: attachment; filename=datos.csv\r\n"
            . "\r\n"
            . "$attachment\r\n"
            . "--$multipartSep--";

        return @mail($correo, 'Trampas - Datos exportados', $body, implode("\r\n", $headers));
    }

    public function generarCSV($desde, $hasta){
         $consulta = 
         "SELECT p.id AS periodo,
                   c.lat,
                   c.lon,
                   c.tempMin,
                   c.tempMax,
                   c.tempProm,
                   c.humMin,
                   c.humMax,
                   c.humProm,
                   c.fechaInicio,
                   c.fechaFin,
                   c.leishmaniasis,
                   c.flevotomo,
                   c.perros,
                   u.nombre,
                   u.apellido, 
                   u.correo
            FROM colocacion AS c
            INNER JOIN trampa AS t ON c.trampa = t.id
            INNER JOIN usuario AS u ON c.usuario = u.id
            INNER JOIN periodo AS p ON c.idColocacion = p.colocacion";

       if($desde != '' && $hasta != ''){
            if((strcmp($desde, $hasta) == 0) == 1){
                $consulta .= " WHERE c.fechaInicio >= ?";
            }else{
                $consulta .= " WHERE c.fechaInicio BETWEEN ? AND ?";
            }
        }

        $sql = DB::conexion()->prepare($consulta);
       
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');
        
        if($desde != '' && $hasta != ''){
            if((strcmp($desde, $hasta) == 0) == 1){
                $sql->bind_param('s', $desde);
            }else{
                $sql->bind_param('ss', $desde, $hasta);
            }
        }

        $sql->execute();
        
        $resultado = $sql->get_result();

        if($resultado->num_rows > 0){

            if (!$fp = fopen('php://temp', 'w+')) return FALSE;
      
            fputcsv($fp, array(
                    'Periodo',
                    'Lat',
                    'Lon',
                    'Temp min',
                    'Temp max',
                    'Temp prom',
                    'Hum min',
                    'Hum max',
                    'Hum prom',
                    'Fecha inicio',
                    'Fecha fin',
                    'Leishmaniasis',
                    'Flevotomo',
                    'Perros',
                    'Nombre',
                    'Apellido',
                    'Correo'
            ));

            $colocaciones = [];
            while ($fila = $resultado->fetch_assoc()) {
                   $fila->leishmaniasis = (boolean)$fila->leishmaniasis;
                   fputcsv($fp, $fila);
            }

            rewind($fp);
            return stream_get_contents($fp);

        }else{
            return -1;
        }
    }

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

    public function getLeishmaniasis(){
        return $this->leishmaniasis;
    }

    public function setLeishmaniasis($leishmaniasis){
        $this->leishmaniasis = $leishmaniasis;
        return $this;
    }


    public function getFlevotomo(){
        return $this->flevotomo;
    }

    public function setFlevotomo($flevotomo){
        $this->flevotomo = $flevotomo;
    }

    public function getPerros(){
        return $this->perros;
    }

    public function setPerros($perros){
        $this->perros = $perros;
    }

}

?> 

