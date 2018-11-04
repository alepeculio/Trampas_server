<?php 
require_once ("../config/config.php");
class DB{
	public static function conexion(){
       static $conexion = null;
       if (null === $conexion) {
          $conexion=new mysqli(DB_HOST, DB_USR, DB_PASS, DB_DB)
          or die ("No se puede conectar con el servidor");
      }

      $conexion->set_charset("utf8");
      return $conexion;
  }
}
?>