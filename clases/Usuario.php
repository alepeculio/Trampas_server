<?php 

require_once "../clases/db.php";

class Usuario{
	private $id;
	private $correo;
	private $nombre;
	private $apellido;
	private $contrasenia;
	private $admin;
	
	function __construct($id = 0, $correo = "", $nombre = "", $apellido = "", $contrasenia = "", $admin = false){

		$this->id = $id;
		$this->correo = $correo;
		$this->nombre = $nombre;
		$this->apellido = $apellido;
		$this->contrasenia = $contrasenia;
		$this->admin = $admin;
	}

	public static function agregar($correo, $nombre, $apellido, $contrasenia, $admin){
		$sql = DB::conexion()->prepare("INSERT INTO trampas_usuario (correo, nombre, apellido, contrasenia, admin) VALUES(?,?,?,?,?)");
		
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ssssi", $correo, $nombre, $apellido, $contrasenia, $admin);

        return $sql->execute();
	}

     public static function eliminar($id){
        $activo = 0;
        $sql = DB::conexion()->prepare("UPDATE trampas_usuario SET activo=? WHERE id=?");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ii", $activo, $id);
        
        return $sql->execute();
    }

    public static function login($correo, $contrasenia){
        $sql = DB::conexion()->prepare("SELECT * FROM trampas_usuario  WHERE correo = ? AND contrasenia = ? AND activo=1");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param ("ss", $correo, $contrasenia);
        $sql->execute();

        $resultado =  $sql->get_result();

        if($resultado -> num_rows == 1){
            $usuario =  $resultado -> fetch_object();
            return $usuario;
        }else{
            return 0;
        }
    }

    public static function obtenerUsuarios(){
        $sql = DB::conexion()->prepare("SELECT * FROM `trampas_usuario` WHERE activo=1 AND admin != 3");

        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->execute();

        $resultado=$sql->get_result();
        while ($fila=$resultado->fetch_object()) {
            $usuarios[] = $fila;
        }
        return $usuarios;
    }

    public static function actualizarPrivilegios($id, $admin){
        $sql = DB::conexion()->prepare("UPDATE trampas_usuario SET admin=? WHERE id=?");
        
        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("ii", $admin, $id);
        
        return $sql->execute();
    }


    public static function cambiarContrasenia($id, $actual, $nueva){
        $sql = DB::conexion()->prepare("SELECT * FROM trampas_usuario  WHERE id = ? AND contrasenia = ? AND activo=1");

        if($sql == null)
            throw new Exception('Error de conexion con la BD.');

        $sql->bind_param("is", $id, $actual);
        $sql->execute();

        $resultado = $sql->get_result();

        if($resultado->num_rows == 1){ 
           $sql2 = DB::conexion()->prepare("UPDATE trampas_usuario SET contrasenia = ? WHERE id=?");

            if($sql2 == null)
                throw new Exception('Error de conexion con la BD.');

            $sql2->bind_param("si", $nueva, $id);
            if($sql2->execute())
                return 1;
            else
                return 0;
        }else{
            return 2;
        }
    }


    //geters y setters.
    public function getId(){
    	return $this->id;
    }

    public function setId($id){
    	$this->id = $id;
    	return $this;
    }

    public function getCorreo(){
    	return $this->correo;
    }

    public function setCorreo($correo){
    	$this->correo = $correo;
    	return $this;
    }

    public function getNombre(){
    	return $this->nombre;
    }

    public function setNombre($nombre){
    	$this->nombre = $nombre;
    	return $this;
    }

    public function getApellido(){
    	return $this->apellido;
    }

    public function setApellido($apellido){
    	$this->apellido = $apellido;
    	return $this;
    }

    public function getFnac(){
    	return $this->fnac;
    }

    public function setFnac($fnac){
    	$this->fnac = $fnac;
    	return $this;
    }

    public function getContrasenia(){
    	return $this->contrasenia;
    }

    public function setContrasenia($contrasenia){
    	$this->contrasenia = $contrasenia;
    	return $this;
    }

    public function getAdmin(){
    	return $this->admin;
    }

    public function setAdmin($admin){
    	$this->admin = $admin;
    	return $this;
    }
}

?>