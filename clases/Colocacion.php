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
    public $flebotomos;
    public $habitantes;
    public $observaciones;
    public $perrosExitentes;
    public $perrosMuestreados;
    public $perrosPositivos;
    public $perrosProcedencia;
    public $perrosEutanasiados;
    public $otrasAcciones;
	public $usuario;
  
	

	public function __construct($lat = 0, $lon = 0, $trampa = 0, $usuario = 0) {
		parent::__construct( $trampa );
		$this->lat = $lat;
		$this->lon = $lon;
		$this->usuario = $usuario;
		$this->fechaInicio = date_create(NULL, timezone_open("America/Montevideo"))->format('Y-m-d H:i:s');
	}


    public static function colocarTrampa($lat, $lon, $idTrampa, $idUsuario){
        $sql = DB::conexion()->prepare("
            SELECT COUNT(p.id) AS cantidad,
                   p.id 
            FROM trampas_periodo AS p 
            LEFT JOIN trampas_colocacion AS c ON p.colocacion = c.idColocacion
            WHERE c.trampa = ?
            GROUP BY p.id
            ORDER BY p.id DESC
            ");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("i", $idTrampa);
        $sql->execute();
        $resultado = $sql->get_result();

        if($resultado->num_rows != null){
            while ($fila = $resultado -> fetch_object()) {
                if( $fila->cantidad < 3){
                    return Colocacion::insertarTrampa($fila->id, $lat, $lon, $idTrampa, $idUsuario);
                }else{
                    return Colocacion::insertarTrampa(0, $lat, $lon, $idTrampa, $idUsuario);
                }
            }
        }else{
           return Colocacion::insertarTrampa(0, $lat, $lon, $idTrampa, $idUsuario);
        }
    }

    public function insertarTrampa($idPeriodo, $lat, $lon, $idTrampa, $idUsuario){
        $fechaInicio = date_create(NULL, timezone_open("America/Montevideo"))->format('Y-m-d H:i:s');

        $sql = DB::conexion()->prepare("INSERT INTO trampas_colocacion (lat, lon, fechaInicio, trampa, usuario) VALUES(?,?,?,?,?)");
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ddsii", $lat, $lon, $fechaInicio, $idTrampa, $idUsuario);
        if($sql->execute()){
            $idColocacion = $sql->insert_id;

            $sql2 = DB::conexion()->prepare("INSERT INTO trampas_periodo (id, colocacion) VALUES(?,?)");
            if($sql2 == null)
                throw new Exception('Error de conexion con la BD.');
            
            $sql2->bind_param("ii", $idPeriodo, $idColocacion);
            
            if($sql2->execute()){
                return $idColocacion;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public static function extraerTrampa($tMin, $tMax, $hMin, $hMax, $tProm, $hProm, $idTrampa){
        $fechaFin = date_create(NULL, timezone_open("America/Montevideo"))->format('Y-m-d H:i:s');
        $sql = DB::conexion()->prepare("
            UPDATE trampas_colocacion 
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

        $sql->bind_param("ddddddsi", $tMin, $tMax, $hMin, $hMax, $tProm, $hProm ,$fechaFin , $idTrampa);
        
        return $sql->execute();
    }

    public static function obtenerColocaciones(){
        $sql = DB::conexion()->prepare("
            SELECT  c.idColocacion,
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
                    c.flebotomos,
                    c.habitantes,
                    c.observaciones,
                    c.perrosExistentes,
                    c.perrosMuestreados,
                    c.perrosPositivos,
                    c.perrosProcedencia,
                    c.perrosEutanasiados,
                    c.otrasAcciones,
                    c.usuario,
                    p.id AS periodo,
                    c.trampa,
                    t.nombre,
                    t.mac
            FROM trampas_colocacion c 
            INNER JOIN trampas_trampa t ON c.trampa = t.id 
            INNER JOIN trampas_periodo p ON c.idColocacion = p.colocacion
            WHERE t.activa = 1 
            ORDER BY c.fechaInicio DESC
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


    public static function obtenerColocacionesTrampa( $idTrampa ){
        $sql = DB::conexion()->prepare("
            SELECT  c.idColocacion,
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
                    c.flebotomos,
                    c.habitantes,
                    c.observaciones,
                    c.perrosExistentes,
                    c.perrosMuestreados,
                    c.perrosPositivos,
                    c.perrosProcedencia,
                    c.perrosEutanasiados,
                    c.otrasAcciones,
                    c.usuario,
                    p.id AS periodo
            FROM trampas_colocacion c 
            INNER JOIN trampas_periodo p ON c.idColocacion = p.colocacion
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

    public static function actualizarUbicacion($id, $lat, $lon){
        $sql = DB::conexion()->prepare("UPDATE trampas_colocacion SET lat=?, lon=? WHERE idColocacion=?");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ddi",$lat, $lon, $id);
        
        return $sql->execute();
    }

    public static function actualizar(
            $id, 
            $lat, 
            $lon, 
            $fechaInicio,
            $fechaFin,
            $tMin,
            $tMax,
            $tProm,
            $hMin,
            $hMax,
            $hProm,
            $leishmaniasis,
            $flebotomos,
            $habitantes,
            $observaciones,
            $perrosExistentes,
            $perrosMuestreados,
            $perrosPositivos,
            $perrosProcedencia,
            $perrosEutanasiados,
            $otrasAcciones
        ){

        $sql = DB::conexion()->prepare("
            UPDATE trampas_colocacion
            SET lat=?,
                lon=?,
                fechaInicio=?,
                fechaFin=?,
                tempMin=?,
                tempMax=?,
                tempProm=?,
                humMin=?,
                humMax=?,
                humProm=?,
                leishmaniasis=?,
                flebotomos=?,
                habitantes=?,
                observaciones=?,
                perrosExistentes=?,
                perrosMuestreados=?,
                perrosPositivos=?,
                perrosProcedencia=?,
                perrosEutanasiados=?,
                otrasAcciones=?
            WHERE idColocacion=?
            ");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ddssssssssiiisiiisisi", 
            $lat, 
            $lon, 
            $fechaInicio,
            $fechaFin,
            $tMin,
            $tMax,
            $tProm,
            $hMin,
            $hMax,
            $hProm,
            $leishmaniasis,
            $flebotomos,
            $habitantes,
            $observaciones,
            $perrosExistentes,
            $perrosMuestreados,
            $perrosPositivos,
            $perrosProcedencia,
            $perrosEutanasiados,
            $otrasAcciones, 
            $id
        );
        
        return $sql->execute();
    }

    public static function obtenerColocacion($id){
        $sql = DB::conexion()->prepare("SELECT * FROM trampas_colocacion WHERE idColocacion=?");
        
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

    public static function obtenerColocacionesGrafica($idPeriodo){
         $sql = DB::conexion()->prepare("
            SELECT c.tempProm,
                   c.humProm
            FROM trampas_periodo AS p 
            RIGHT JOIN trampas_colocacion AS c ON p.colocacion = c.idColocacion 
            WHERE p.id = ?
            ");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("i", $idPeriodo);
        $sql->execute();
        
        $resultado = $sql->get_result();

        $colocaciones = [];
        while ($fila = $resultado->fetch_object()) {
          $colocaciones[] = $fila;
        }
        return $colocaciones;
    }

    public static function enviarCorreoCSV($correo, $desde = '', $hasta = '') {
        $multipartSep = '-----'.md5(time()).'-----';

        $headers = array(
            "From: trampas.paysandu@gmail.com",
            "Reply-To: trampas.paysandu@gmail.com",
            "Content-Type: multipart/mixed; boundary=".$multipartSep
        );

        $csv = Colocacion::generarCSV($desde, $hasta);
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

    public static function generarCSV($desde, $hasta){
         $consulta = 
         "SELECT    p.id AS periodo,
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
                    c.flebotomos,
                    c.habitantes,
                    c.observaciones,
                    c.perrosExistentes,
                    c.perrosMuestreados,
                    c.perrosPositivos,
                    c.perrosProcedencia,
                    c.perrosEutanasiados,
                    c.otrasAcciones,
                    u.nombre,
                    u.apellido, 
                    u.correo
            FROM trampas_colocacion AS c
            INNER JOIN trampas_trampa AS t ON c.trampa = t.id
            INNER JOIN trampas_usuario AS u ON c.usuario = u.id
            INNER JOIN trampas_periodo AS p ON c.idColocacion = p.colocacion";

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
                    'Cant flebótomos',
                    'Habitantes vivienda',
                    'Observaciones',
                    'Perros existentes',
                    'Perros muestreados',
                    'Perros positivos',
                    'Procedencia de perros positivos',
                    'Perros eutanasiados',
                    'Otras acciones',
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


    //geters y setters.
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

