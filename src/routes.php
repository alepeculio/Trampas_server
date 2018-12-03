<?php
require_once "../clases/Trampa.php";
require_once "../clases/Colocacion.php";
require_once "../clases/Usuario.php";
error_reporting(E_ERROR);
ini_set("display_errors", 1);

set_include_path(dirname(__FILE__) . '/../../');

//Comprueba que existe un usuario con 'correo' y 'contrasenia' en la tabla 'usuario'.
$app->post('/login', function($req, $res) {
	$params=$req->getParams();
	$correo=$params['correo'];
	$contrasenia=$params['contrasenia'];
	
	$this->logger->addInfo("Login: ".$correo); 

	try{
		$usuario = new Usuario(0,$correo, NULL, NULL, $contrasenia, 0);
		$usuarioLogueado = $usuario-> login();
		if($usuarioLogueado != 0){
			$codigo = 1;
			$mensaje = "Login correcto.";
		}else{
			$codigo = 0;
			$mensaje = "Correo o/y contraseña incorrectos.";
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

//Inserta una fila en la tabla 'trampa'.
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
			$mensaje = "Trampa agregada.";
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

//Setea el campo 'activa' de la trampa con el id recido a false.
$app->post('/eliminarTrampa', function ($req, $res) {
	$params = $req->getParams();
	$id = (int)$params['id'];
	try{
		$trampa = new Trampa($id);
		$resultado = $trampa->eliminar();

		if($resultado == false){
			$mensaje = "No se pudo eliminar la trampa.";
			$codigo = -1;
		}else{
			$mensaje = "Trampa eliminada.";
			$codigo = 1;
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

//Devuelve todas las trampas de la tabla 'trampa' y la última colocación de cada una.
$app->get('/obtenerTrampas', function ($req, $res) {
	$params = $req->getParams();
	try{
		$trampa = new Trampa();
		$resultado = $trampa->obtenerTrampas();
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto.', 'trampas' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Devuelve las trampas de la tabla 'trampa' que tienen alguna colocación con leishmaniasis y la última colocación de cada una.
$app->get('/obtenerTrampasLeishmaniasis', function ($req, $res) {
	$params = $req->getParams();
	try{
		$trampa = new Trampa();
		$resultado = $trampa->obtenerTrampas(true);
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto.', 'trampas' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Devuelve todas las trampas que no están colocadas actualmente (ninguna colocación con fechaFin null).
$app->get('/obtenerTrampasNoColocadas', function ($req, $res) {
	$params = $req->getParams();
	try{
		$trampa = new Trampa();
		$resultado = $trampa->obtenerTrampasNoColocadas();
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto.', 'trampas' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Devuelve todas las trampas que están colocadas actualmente (alguna colocación con fechaFin null).
$app->get('/obtenerTrampasColocadas', function ($req, $res) {
	$params = $req->getParams();
	try{
		$trampa = new Trampa();
		$resultado = $trampa->obtenerTrampasColocadas();
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto.', 'trampas' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

/*Inserta una fila en la tabla 'colocacion' y en 'periodo' 
(si hay algún periodo con menos de tres filas de esa trampa se completa, sino se crea uno nuevo). */
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
			$mensaje = "Error al colocar la trampa.";
		}else{
			$codigo = $resultado;
			$mensaje = "Trampa colocada.";
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

//Se actualiza la información de la última colocación de la trampa con id igual a 'id_trampa'.
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
			$mensaje = "Error al extraer la trampa.";
		}else{
			$codigo = 1;
			$mensaje = "Trampa extraida.";
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

//Se actualiza la ubicación de la colocación con idColocacion igual a 'id'.
$app->post('/actualizarUbicacionColocacion', function ($req, $res) {
	$params = $req->getParams();
	$id = (int)$params['id'];
	$lat = (double)$params['lat'];
	$lon = (double)$params['lon'];
	try{
		$colocacion = new Colocacion($lat,$lon);
		$resultado = $colocacion->actualizarUbicacion($id);

		if($resultado == false){
			$mensaje = "No se pudo guardar los cambios.";
			$codigo = -1;
		}else{
			$mensaje = "Cambios guardados";
			$codigo = 1;
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

//Se actualizan todos los datos de la colocación con idColocacion igual a 'id'.
$app->post('/actualizarColocacion', function ($req, $res) {
	$params = $req->getParams();
	$id = (int)$params['id'];
	$lat = (double)$params['lat'];
	$lon = (double)$params['lon'];
	$fechaInicio = $params['finicio'];
	$fechaFin = $params['ffin'];
	$tMin = (float)$params['tmin'];
	$tMax = (float)$params['tmax'];
	$tProm = (float)$params['tprom'];
	$hMin = (float)$params['hmin'];
	$hMax = (float)$params['hmax'];
	$hProm = (float)$params['hprom'];
	$leishmaniasis = (boolean)$params['leishmaniasis'];
	$flevotomo = (int)$params['flevotomo'];
	$perros = (int)$params['perros'];

	try{
		$colocacion = new Colocacion($lat, $lon);
		$colocacion->setFechaInicio($fechaInicio);
		$colocacion->setFechaFin($fechaFin);
		$colocacion->setTempMin($tMin);
		$colocacion->setTempMax($tMax);
		$colocacion->setHumMin($hMin);
		$colocacion->setHumMax($hMax);
		$colocacion->setTempProm($tProm);
		$colocacion->setHumProm($hProm);
		$colocacion->setLeishmaniasis($leishmaniasis);
		$colocacion->setFlevotomo($flevotomo);
		$colocacion->setPerros($perros);
		$resultado = $colocacion->actualizar($id);

		if($resultado == false){
			$mensaje = "No se pudo actualizar colocación.";
			$codigo = "-1";
		}else{
			$mensaje = "Colocación actualizada.";
			$codigo = "1";
		}

		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => $codigo, 'mensaje' => $mensaje ]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => "-2", 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Devuelve todas las colocaciones exitentes.
$app->get('/obtenerColocaciones', function ($req, $res) {
	$params = $req->getParams();
	try{
		$colocacion = new Colocacion();
		$resultado = $colocacion->obtenerColocaciones();
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto.', 'colocaciones' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Devuelve todas las colocaciones de la trampa con 'id' igual a 'id'.
$app->post('/obtenerColocacionesTrampa', function ($req, $res) {
	$params = $req->getParams();
	$id = (int)$params['id'];

	try{
		$colocacion = new Colocacion();
		$resultado = $colocacion->obtenerColocacionesTrampa($id);
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto.', 'colocaciones' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Devuelve todas las colocaciones del periodo con 'id' igual a 'id_periodo'.
$app->post('/obtenerColocacionesGrafica', function ($req, $res) {
	$params = $req->getParams();
	$idPeriodo = (int)$params['id_periodo'];

	try{
		$colocacion = new Colocacion();
		$resultado = $colocacion->obtenerColocacionesGrafica($idPeriodo);
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto.', 'colocaciones' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Desvuelve la colocación con 'idColocacion' igual a 'id'.
$app->get('/obtenerColocacion', function ($req, $res) {
	$params = $req->getParams();
	$id = (int)$params['id'];

	try{
		$colocacion = new Colocacion();
		$resultado = $colocacion->obtenerColocacion($id);
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Colocación obtenida correctamente.', 'colocacion' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Inserta una fila en la tabla 'usuario'.
$app->post('/agregarUsuario', function ($req, $res) {	
	$params = $req->getParams();
	$correo = $params['correo'];
	$nombre = $params['nombre'];
	$apellido = $params['apellido'];
	$contrasenia = $params['contrasenia'];
	$admin = (int)$params['admin'];
	
	try{
		$usuario = new Usuario(0,$correo, $nombre, $apellido, $contrasenia, $admin);
		$resultado = $usuario-> agregar();
		$codigo;
		if($resultado){
			$codigo = 1;
			$mensaje = 'Registrado correctamente.';
		}else{
			$codigo = 0;
			$mensaje = 'Correo ya en uso.';
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

//Devuelve todos los usuarios existentes.
$app->get('/obtenerUsuarios', function ($req, $res) {
	$params = $req->getParams();
	try{
		$usuario = new Usuario();
		$resultado = $usuario->obtenerUsuarios();
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Listado correcto.', 'usuarios' => $resultado]));

	}catch(Exception $e){
		$res = $res->
		withStatus(400)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => -1, 'mensaje' => $e->getMessage()]));
	}
	return $res;
});

//Actualiza el 'admin' del usuario con 'id' igual a 'id'.
$app->post('/actualizarPrivilegios', function ($req, $res) {
	$params = $req->getParams();
	$id = (int)$params['id'];
	$admin = (int)$params['admin'];
	try{
		$usuario = new Usuario($id, null, null, null, null, $admin);
		$resultado = $usuario->actualizarPrivilegios();

		if($resultado == false){
			$mensaje = "No se pudo actualizar los privilegios.";
			$codigo = -1;
		}else{
			$mensaje = "Privilegios actualizados.";
			$codigo = 1;
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


//Actualiza el campo 'activo' del usuario a false.
$app->post('/eliminarUsuario', function ($req, $res) {
	$params = $req->getParams();
	$id = (int)$params['id'];

	try{
		$usuario = new Usuario($id);
		$resultado = $usuario->eliminar();

		if($resultado == false){
			$mensaje = "No se pudo eliminar el usuario.";
			$codigo = -1;
		}else{
			$mensaje = "Usuario eliminado.";
			$codigo = 1;
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

//Comprueba que la contraseña actual sea correcta y si es asi la actualiza por 'contrasenia_nueva'.
$app->post('/cambiarContrasenia', function ($req, $res) {
	$params = $req->getParams();
	$id = (int)$params['id'];
	$contraseniaActual = $params['contrasenia_actual'];
	$contraseniaNueva = $params['contrasenia_nueva'];

	try{
		$usuario = new Usuario($id);
		$resultado = $usuario->cambiarContrasenia($contraseniaActual, $contraseniaNueva);

		if($resultado == 2){
			$mensaje = "Contraseña actual incorrecta.";
			$codigo = 2;
		}else if($resultado == 1){
			$mensaje = "Contraseña cambiada.";
			$codigo = 1;
		}else {
			$mensaje = "No se pudo cambiar la contraseña.";
			$codigo = 0;			
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

//Envia un correo con los datos en formato csv entre las fechas 'desde' y 'hasta'.
$app->post('/exportarDatos', function ($req, $res) {
	$params = $req->getParams();
	$correo = $params['correo'];
	$desde = $params['desde'];
	$hasta = $params['hasta'];

	try{
		$c = new Colocacion();
		$resultado = (int)$c->enviarCorreoCSV($correo, $desde, $hasta);

		if($resultado == -1){
			$mensaje = "No hay datos entre las fechas seleccionadas.";
			$codigo = 1;
		}else if($resultado == 1){
			$mensaje = "Datos exportados a su correo.";
			$codigo = 0;
		}else{
			$mensaje = "No se pudo exportar los datos.";
			$codigo = -1;
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