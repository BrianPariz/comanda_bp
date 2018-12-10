<?php
class MWVerificacion
{
	public function VerificarToken($request, $response, $next) {
        
		$objDelaRespuesta = new stdclass();
		$objDelaRespuesta->respuesta = "";
		$arrayConToken = $request->getHeader('token');
		$token=$arrayConToken[0];
		$objDelaRespuesta->esValido = true;
		
		try {
			AutentificadorJWT::verificarToken($token);
			$objDelaRespuesta->esValido = true;
		} catch (Exception $e) {
			$objDelaRespuesta->excepcion = $e->getMessage();
			$objDelaRespuesta->esValido = false;
		}
		
		if($objDelaRespuesta->esValido) {
			$payload = AutentificadorJWT::ObtenerData($token);
			$request = $request->withAttribute('empleado', $payload);
			$response = $next($request, $response);
		} else {
			$objDelaRespuesta->respuesta = "Por favor logueese para realizar esta accion";
			$objDelaRespuesta->elToken = $token;
		}
        
        if($objDelaRespuesta->respuesta != "") {
			$nueva = $response->withJson($objDelaRespuesta, 401);
			return $nueva;
        }

        return $response;
	}

	public function VerificarAdmin($request, $response, $next) {
		$objDelaRespuesta = new stdclass();
		$objDelaRespuesta->respuesta = "";
		$sector = $request->getAttribute('empleado')->sector;
		$estado = $request->getAttribute('empleado')->estado;
		if($sector == "admin") {
			$response = $next($request, $response);
		}
		else
		{
			$objDelaRespuesta->respuesta = "Solo socios";
		}
        
        if($objDelaRespuesta->respuesta != "") {
			$nueva = $response->withJson($objDelaRespuesta, 401);
			return $nueva;
        }

        return $response;
	}

	public function VerificarEmpleado($request, $response, $next) {
		$objDelaRespuesta = new stdclass();
		$objDelaRespuesta->respuesta = "";
		$sector = $request->getAttribute('empleado')->sector;
		if($sector == "barra" || $sector == "cerveza" || $sector == "cocina" || $sector == "candy" || $sector == "admin") {
			$response = $next($request, $response);
		}
		else
		{
			$objDelaRespuesta->respuesta="Solo empleados";
		}
        
        if($objDelaRespuesta->respuesta != "") {
			$nueva = $response->withJson($objDelaRespuesta, 401);
			return $nueva;
        }

        return $response;
	}

	public function VerificarMozo($request, $response, $next) {
		$objDelaRespuesta = new stdclass();
		$objDelaRespuesta->respuesta="";
		$sector = $request->getAttribute('empleado')->sector;
		if($sector == "mozo" || $sector == "admin") {
			$response = $next($request, $response);
		}
		else
		{
			$objDelaRespuesta->respuesta = "Solo mozos";
		}
        
        if($objDelaRespuesta->respuesta != "") {
			$nueva = $response->withJson($objDelaRespuesta, 401);
			return $nueva;
        }

        return $response;
	}

	public function FiltrarPedidos($request, $response, $next) {
		$objDelaRespuesta = new stdclass();
		$objDelaRespuesta->respuesta = "";
		$usuarioEmpleado = $request->getAttribute('empleado')->usuario;
		$sector = $request->getAttribute('empleado')->sector;
		if($sector == "barra" || $sector == "cerveza" || $sector == "cocina" || $sector == "candy") {
			$response = $next($request, $response);
			$pedidos = json_decode($response->getBody()->__toString());
			if (is_array($pedidos)) {
				foreach ($pedidos as $key => $pedido) {
					if (!($pedido->sector == $sector && ($pedido->estado == 'pendiente' || ($pedido->estado == 'preparandose' && $pedido->idEmpleado == $usuarioEmpleado)))) {
						unset($pedidos[$key]);
					}
				}
			} else {
				if ($pedidos->sector != $sector) {
					$pedidos = [];
				}
			}
			$nueva = $response->withJson($pedidos, 200);
			return $nueva;
		} else if($sector == "mozo") {
			$response = $next($request, $response);
			$pedidos = json_decode($response->getBody()->__toString());
			if (is_array($pedidos)) {
				foreach ($pedidos as $key => $pedido) {
					if ($pedido->estado != 'listo') {
						unset($pedidos[$key]);
					}
				}
			} else {
				if ($pedidos->sector != $sector) {
					$pedidos = [];
				}
			}
			$nueva = $response->withJson($pedidos, 200);
			return $nueva;
			return $response;
		} else if($sector == "admin") {
			$response = $next($request, $response);
			return $response;
		} else {
			$objDelaRespuesta->respuesta = "Solo usuarios";
		}
        
        if($objDelaRespuesta->respuesta != "") {
			$nueva = $response->withJson($objDelaRespuesta, 401);
			return $nueva;
        }

        return $response;
	}
}

?>