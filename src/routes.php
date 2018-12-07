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
		$usuarioLogueado = Usuario::login($correo, $contrasenia);
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
		$resultado = Trampa::agregar($nombre, $mac);

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
		$resultado = Trampa::eliminar($id);

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
		$resultado = Trampa::obtenerTrampas();
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
		$resultado = Trampa::obtenerTrampas(true);
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
		$resultado = Trampa::obtenerTrampasNoColocadas();
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
		$resultado = Trampa::obtenerTrampasColocadas();
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
		$resultado = Colocacion::colocarTrampa($lat, $lon, $idTrampa, $idUsuario);
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
		$resultado = Colocacion::extraerTrampa($tMin, $tMax, $hMin, $hMax, $tProm, $hProm, $idTrampa);
		if($resultado == false){
			$codigo = 0;
			$mensaje = "Error al extraer la trampa.";
		}else{
			$codigo = 1;
			$mensaje = "Trampa extraída.";
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
		$resultado = Colocacion::actualizarUbicacion($id, $lat, $lon);

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
	$fechaInicio = $params['fInicio'];
	$fechaFin = $params['fFin'];
	$tMin = (float)$params['tMin'];
	$tMax = (float)$params['tMax'];
	$tProm = (float)$params['tProm'];
	$hMin = (float)$params['hMin'];
	$hMax = (float)$params['hMax'];
	$hProm = (float)$params['hProm'];
	$leishmaniasis = (boolean)$params['leishmaniasis'];
	$flebotomos = (int)$params['flebotomos'];
	$habitantes = (int)$params['habitantes'];
	$observaciones = $params['observaciones'];
	$perrosExistentes = (int)$params['perrosExistentes'];
	$perrosMuestreados = (int)$params['perrosMuestreados'];
	$perrosPositivos = (int)$params['perrosPositivos'];
	$perrosProcedencia = $params['perrosProcedencia'];
	$perrosEutanasiados = (int)$params['perrosEutanasiados'];
	$otrasAcciones = $params['otrasAcciones'];

	try{
		$resultado = Colocacion::actualizar(
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
		);

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
		$resultado = Colocacion::obtenerColocaciones();
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
		$resultado = Colocacion::obtenerColocacionesTrampa($id);
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
		$resultado = Colocacion::obtenerColocacionesGrafica($idPeriodo);
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

//Devuelve la colocación con 'idColocacion' igual a 'id'.
$app->post('/obtenerColocacion', function ($req, $res) {
	$params = $req->getParams();
	$id = (int)$params['id'];

	try{
		$resultado = Colocacion::obtenerColocacion($id);
		$res = $res->
		withStatus(200)->
		withHeader('Content-type', 'application/json;charset=utf-8')->
		write(json_encode(['codigo' => 1, 'mensaje' => 'Colocación obtenida.', 'colocacion' => $resultado]));

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
		$resultado = Usuario::agregar($correo, $nombre, $apellido, $contrasenia, $admin);
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
		$resultado = Usuario::obtenerUsuarios();
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
		$resultado = Usuario::actualizarPrivilegios($id , $admin);

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
		$resultado = Usuario::eliminar($id);

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
		$resultado = Usuario::cambiarContrasenia($id, $contraseniaActual, $contraseniaNueva);

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
		$resultado = (int)Colocacion::enviarCorreoCSV($correo, $desde, $hasta);

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