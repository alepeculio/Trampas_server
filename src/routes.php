<?php
require_once "../clases/Trampa.php";
require_once "../clases/Colocacion.php";
require_once "../clases/Usuario.php";
error_reporting(E_ERROR);
ini_set("display_errors", 1);

set_include_path(dirname(__FILE__) . '/../../');

$app->post('/login', function($req, $res) {
	$params=$req->getParams();
	$correo=$params["correo"];
	$contrasenia=$params["contrasenia"];
	
	$this->logger->addInfo("INFO: Login: Usuario".$correo); 

	try{
		$usuario = new Usuario(0,$correo, NULL, NULL, NULL, $contrasenia, 0);
		$usuarioLogueado = $usuario-> login();
		if($usuarioLogueado != 0){
			$codigo = 1;
			$mensaje = "Login correcto";
		}else{
			$codigo = 0;
			$mensaje = "Correo o/y contraseña incorrectos";
			$usuarioLogueado = null;
		}
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => $codigo, 'mensaje' => $mensaje, 'usuario' => $usuarioLogueado ]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage(), 'usuario' => null]));
	}
	return $res;
});

$app->post('/agregarTrampa', function ($req, $res) {
	$params = $req->getParams();
	$nombre = $params['nombre'];
	$mac = $params['mac'];
	try{
		$trampa = new Trampa(0, $nombre, $mac);
		$resultado = $trampa->agregar();

		if($resultado == false){
			$mensaje = "Nombre ya en uso.";
			$codigo = -1;
		}else{
			$mensaje = "Trampa agregada exitosamente.";
			$codigo = $resultado;
		}

		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => $codigo, 'mensaje' => $mensaje ]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -2, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Devuelve todas las trampas de la tabla Trampa y la ultima colocacion de cada una.
$app->get('/obtenerTrampas', function ($req, $res) {
	$params = $req->getParams();
	try{
		$trampa = new Trampa();
		$resultado = $trampa->obtenerTrampas();
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto', 'trampas' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Devuelve todas las trampas que no tienen un colocacion activa.
$app->get('/obtenerTrampasNoColocadas', function ($req, $res) {
	$params = $req->getParams();
	try{
		$trampa = new Trampa();
		$resultado = $trampa->obtenerTrampasNoColocadas();
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto', 'trampas' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

$app->get('/obtenerTrampasColocadas', function ($req, $res) {
	$params = $req->getParams();
	try{
		$trampa = new Trampa();
		$resultado = $trampa->obtenerTrampasColocadas();
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto', 'trampas' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

/*
//Devuelve la ultima colocacion
$app->get('/obtenerUltimaColocacion', function ($req, $res) {
	$params = $req->getParams();
	$id = $params['id'];

	try{
		$colocacion = new Colocacion();
		$resultado = $colocacion->obtenerUltimaColocacion($id);
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto', 'colocaciones' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});*/

//Agrega una colocacion activa a la trabla Colocacion.
$app->post('/colocarTrampa', function ($req, $res) {	
	$params = $req->getParams();
	$lat = (double)$params['lat'];
	$lon = (double)$params['lon'];
	$idTrampa = (int)$params['id_trampa'];
	$idUsuario = (int)$params['id_usuario'];

	try{
		$colocacion = new Colocacion($lat, $lon, $idTrampa, $idUsuario);
		$resultado = $colocacion -> colocarTrampa();
		if($resultado == false){
			$codigo = 0;
			$mensaje = "Error al colocar la trampa";
		}else{
			$codigo = $resultado;
			$mensaje = "Trampa colocada exitosamente";
		}
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => $codigo, 'mensaje' => $mensaje]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

$app->post('/extraerTrampa', function ($req, $res) {	
	$params = $req->getParams();
	$tMin = (float)$params['tmin'];
	$tMax = (float)$params['tmax'];
	$tProm = (float)$params['tprom'];
	$hMin = (float)$params['hmin'];
	$hMax = (float)$params['hmax'];
	$hProm = (float)$params['hprom'];
	$idTrampa = (int)$params['id_trampa'];

	try{
		$fechaFin = date_create(NULL, timezone_open("America/Montevideo"))->format('Y-m-d H:i:s');
		$colocacion = new Colocacion(0, 0, $idTrampa,0);
		$colocacion->setTempMin($tMin);
		$colocacion->setTempMax($tMax);
		$colocacion->setHumMin($hMin);
		$colocacion->setHumMax($hMax);
		$colocacion->setFechaFin($fechaFin);
		$colocacion->setTempProm($tProm);
		$colocacion->setHumProm($hProm);
		$resultado = $colocacion -> extraerTrampa();
		if($resultado == false){
			$codigo = 0;
			$mensaje = "Error al extraer la trampa";
		}else{
			$codigo = 1;
			$mensaje = "Trampa extraida exitosamente";
		}
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => $codigo, 'mensaje' => $mensaje]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Devuelve todas las colocaciones cuya fecha de fin no es null.
$app->get('/obtenerColocacionesActivas', function ($req, $res) {
	$params = $req->getParams();
	try{
		$colocacion = new Colocacion();
		$resultado = $colocacion->obtenerColocacionesActivas();
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto', 'colocaciones' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Devuelve todas las colocaciones de una Trampa.
$app->post('/obtenerColocacionesTrampa', function ($req, $res) {
	$params = $req->getParams();
	$id = (int)$params['id'];

	try{
		$colocacion = new Colocacion();
		$resultado = $colocacion->obtenerColocacionesTrampa($id);
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto', 'colocaciones' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

$app->get('/agregarUsuario', function ($req, $res) {	
	$params = $req->getParams();
	$correo = $params['correo'];
	$nombre = $params['nombre'];
	$apellido = $params['apellido'];
	$fnac = $params['fnac'];
	$contrasenia = $params['contrasenia'];
	$admin = (bool)$params['admin'];
	
	try{
		$usuario = new Usuario(0,$correo, $nombre, $apellido, $fnac, $contrasenia, $admin);
		$resultado = $usuario-> agregar();
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['resultado' => $resultado ]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});