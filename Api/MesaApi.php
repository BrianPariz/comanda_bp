<?php

class MesaApi extends Mesa implements IApiUsable
{
    public function CargarUno($request,$response,$args)
    {
        $ArrayDeParametros = $request->getParsedBody();
		$mimesa = new Mesa();
		$mimesa->SetEstado('cerrada');
		$codigo = $mimesa->InsertarMesa();
		//Cargo el log
		if ($request->getAttribute('empleado')) {
			$new_log = new Log();
			$new_log->idEmpleado = $request->getAttribute('empleado')->id;
			$new_log->accion = "Cargar mesa";
			$new_log->InsertarLog();
		}
		//--
		$objDelaRespuesta = new stdclass();
        $objDelaRespuesta->respuesta = "Se ha ingresado la mesa #$codigo";
        
		return $response->withJson($objDelaRespuesta, 200);
    }  

    public function BorrarUno($request, $response, $args) {
		$id = $args['id'];
		$mesa = new Mesa();
		$mesa->id = $id;
		$cantidadDeBorrados=$mesa->BorrarMesa();
		//Cargo el log
		if ($request->getAttribute('empleado')) {
			$new_log = new Log();
			$new_log->idEmpleado = $request->getAttribute('empleado')->id;
			$new_log->accion = "Borrar mesa";
			$new_log->InsertarLog();
		}
		//--
		$objDelaRespuesta = new stdclass();
		if($cantidadDeBorrados > 0) {
			$objDelaRespuesta->respuesta = "Mesa eliminada";
			return $response->withJson($objDelaRespuesta, 200);
		} else {
			$objDelaRespuesta->respuesta = "Error eliminando la mesa";
			return $response->withJson($objDelaRespuesta, 400);
		}
	}  

    public function ModificarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$mimesa = new Mesa();
		$mimesa->id = $args['id'];
		$mimesa->estado = $ArrayDeParametros['estado'];
        $mimesa->ModificarMesa();
		//Cargo el log
		if ($request->getAttribute('empleado')) {
			$new_log = new Log();
			$new_log->idEmpleado = $request->getAttribute('empleado')->id;
			$new_log->accion = "Modificar mesa";
			$new_log->InsertarLog();
		}
		//--
        
        return $response->withJson($mimesa, 200);	
	}

    public function TraerUno($request, $response, $args) {
		$id = $args['id'];
		$mesaObj = Mesa::TraerMesa($id);
		$newResponse = $response->withJson($mesaObj, 200);  
		return $newResponse;
	}

	public function TraerTodos($request, $response, $args) {
		$mesas = Mesa::TraerMesas();
		$newResponse = $response->withJson($mesas, 200);  
		return $newResponse;
	}

	public function CerrarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$mesa = Mesa::TraerMesa($ArrayDeParametros['codigoMesa']);
		if ($mesa) {
			if ($mesa->estado == "pagando") {
				$mesa->CerrarMesa();
				//Cargo el log
				if ($request->getAttribute('empleado')) {
					$new_log = new Log();
					$new_log->idEmpleado = $request->getAttribute('empleado')->id;
					$new_log->accion = "Cerrar mesa";
					$new_log->InsertarLog();
				}
				//--
				
				$objDelaRespuesta = new stdclass();
				$objDelaRespuesta->respuesta = "Mesa cerrada";
				return $response->withJson($objDelaRespuesta, 200);
			} else if ($mesa->estado == "cerrada") {
				$objDelaRespuesta= new stdclass();
				$objDelaRespuesta->respuesta = "Esta mesa ya esta cerrada";
				return $response->withJson($objDelaRespuesta, 401);
			} else {
				$objDelaRespuesta = new stdclass();
				$objDelaRespuesta->respuesta = "Esta mesa tiene comensales";
				return $response->withJson($objDelaRespuesta, 401);
			}
		}
		$objDelaRespuesta = new stdclass();
		$objDelaRespuesta->respuesta = "Error buscando la mesa seleccionada";
		return $response->withJson($objDelaRespuesta, 401);
	}
}

?>