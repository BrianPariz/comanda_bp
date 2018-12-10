<?php

class EmpleadoApi extends Empleado implements IApiUsable
{
    public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		
		$miempleado = new Empleado();
		$miempleado->usuario = $ArrayDeParametros['usuario'];
		$miempleado->clave = $ArrayDeParametros['clave'];
		$miempleado->sector = $ArrayDeParametros['sector'];
		$miempleado->sueldo = $ArrayDeParametros['sueldo'];
		$miempleado->estado = $ArrayDeParametros['estado'];
        $miempleado->InsertarEmpleado();
		//Cargo el log
		if ($request->getAttribute('empleado')) {
			$new_log = new Log();
			$new_log->idEmpleado = $request->getAttribute('empleado')->id;
			$new_log->accion = "Cargar empleado";
			$new_log->InsertarLog();
		}
		//--
		$objDelaRespuesta = new stdclass();
		$objDelaRespuesta->respuesta = "Se ha ingresado el empleado";
		return $response->withJson($objDelaRespuesta, 200);
    }

	public function BorrarUno($request, $response, $args) {
		$id=$args['id'];
		$empleado= new Empleado();
		$empleado->id = $id;
		$cantidadDeBorrados = $empleado->BorrarEmpleado();
		$objDelaRespuesta = new stdclass();

		if($cantidadDeBorrados > 0) {
			//Cargo el log
			if ($request->getAttribute('empleado')) {
				$new_log = new Log();
				$new_log->idEmpleado = $request->getAttribute('empleado')->id;
				$new_log->accion = "Borrar empleado";
				$new_log->InsertarLog();
			}
			//--
			$objDelaRespuesta->respuesta="Empleado eliminado";
			return $response->withJson($objDelaRespuesta, 200);
		} else {
			$objDelaRespuesta->respuesta="Error al eliminar el empleado";
			return $response->withJson($objDelaRespuesta, 400);
		}
	}
		
	public function ModificarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$miempleado = new Empleado();
		$miempleado->id = $args['id'];
		$miempleado->usuario = $ArrayDeParametros['usuario'];
		$miempleado->clave = $ArrayDeParametros['clave'];
		$miempleado->sector = $ArrayDeParametros['sector'];
		$miempleado->sueldo = $ArrayDeParametros['sueldo'];
		$miempleado->estado = $ArrayDeParametros['estado'];
		
		$miempleado->ModificarEmpleado();
		//Cargo el log
		if ($request->getAttribute('empleado')) {
			$new_log = new Log();
			$new_log->idEmpleado = $request->getAttribute('empleado')->id;
			$new_log->accion = "Modificar empleados";
			$new_log->InsertarLog();
		}
		//--
		
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="Empleado modificado";
		return $response->withJson($objDelaRespuesta, 200);	
	}
	
	public function TraerUno($request, $response, $args) {
		$id = $args['id'];
		$empleadoObj = Empleado::TraerEmpleado($id);
		$newResponse = $response->withJson($empleadoObj, 200);  
		return $newResponse;
	}

	public function TraerTodos($request, $response, $args) {
		$empleados = Empleado::TraerEmpleados();
		$newResponse = $response->withJson($empleados, 200);  
		return $newResponse;
	}
	
	public function TomarUnPedido($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$empleado = $request->getAttribute('empleado');
		if ($empleado && $ArrayDeParametros['idPedido'] && $ArrayDeParametros['estimacion']) {
			$empleadoObj = Empleado::TraerEmpleado($empleado->id);
			if($empleadoObj->estado == 'habilitado') {
				$respuesta = $empleadoObj->TomarPedido($ArrayDeParametros['idPedido'], $ArrayDeParametros['estimacion']);
				//Cargo el log
				if ($request->getAttribute('empleado')) {
					$new_log = new Log();
					$new_log->idEmpleado = $request->getAttribute('empleado')->id;
					$new_log->accion = "Tomar un pedido";
					$new_log->InsertarLog();
				}
				//--
				$objDelaRespuesta= new stdclass();
				$objDelaRespuesta->respuesta = $respuesta;
				return $response->withJson($objDelaRespuesta, 200);
			} else {
				$objDelaRespuesta= new stdclass();
				$objDelaRespuesta->respuesta = "No puede tomar un pedido en estado ocupado o deshabilitado";
				return $response->withJson($objDelaRespuesta, 401);
			}
		}
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="Error, campos faltantes";
		return $response->withJson($objDelaRespuesta, 401);
	}

	public function EntregarUnPedido($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		if ($ArrayDeParametros['idPedido']) {
			$respuesta = Empleado::EntregarPedido($ArrayDeParametros['idPedido']);
			//Cargo el log
			if ($request->getAttribute('empleado')) {
				$new_log = new Log();	
				$new_log->idEmpleado = $request->getAttribute('empleado')->id;
				$new_log->accion = "Entregar pedido listo para servir";
				$new_log->InsertarLog();
			}
			//--
			$objDelaRespuesta = new stdclass();
			$objDelaRespuesta->respuesta = $respuesta;
			return $response->withJson($objDelaRespuesta, 200);
		}
		$objDelaRespuesta = new stdclass();
		$objDelaRespuesta->respuesta = "Debe ingresar el numero del pedido";
		return $response->withJson($objDelaRespuesta, 401);
	}

	public function HabilitarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$empleado = Empleado::TraerEmpleado($ArrayDeParametros['idEmpleado']);
		if ($empleado) {
			if ($empleado->GetEstado() == 'deshabilitado') {
				$empleado->HabilitarEmpleado();
				//Cargo el log
				if ($request->getAttribute('empleado')) {
					$idEmpleado = $request->getAttribute('empleado')->id;
					$new_log = new Log();
					$new_log->idEmpleado = $request->getAttribute('empleado')->id;
					$new_log->accion = "Habilitar empleado $empleado->usuario";
					$new_log->InsertarLog();
				}
				//--
				
				$objDelaRespuesta= new stdclass();
				$objDelaRespuesta->respuesta="Empleado habilitado!";
				return $response->withJson($objDelaRespuesta, 200);
			} else {
				$objDelaRespuesta= new stdclass();
				$objDelaRespuesta->respuesta="Este empleado ya esta habilitado.";
				return $response->withJson($objDelaRespuesta, 401);
			}
		}
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="Empleado inexistente";
		return $response->withJson($objDelaRespuesta, 401);
	}

	public function DeshabilitarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$empleado = Empleado::TraerEmpleado($ArrayDeParametros['idEmpleado']);
		$idEmpleado = $request->getAttribute('empleado')->id;
		if ($empleado) {
			if ($empleado->id != $idEmpleado) {
				if ($empleado->estado == "habilitado") {
					$empleado->DeshabilitarEmpleado();
					//Cargo el log
					if ($request->getAttribute('empleado')) {
						$new_log = new Log();
						$new_log->idEmpleado = $request->getAttribute('empleado')->id;
						$new_log->accion = "Deshabilitar empleado $empleado->usuario";
						$new_log->InsertarLog();
					}
					//--
					
					$objDelaRespuesta = new stdclass();
					$objDelaRespuesta->respuesta = "Empleado deshabilitado";
					return $response->withJson($objDelaRespuesta, 200);
				} else if ($empleado->estado == "ocupado") {
					$objDelaRespuesta= new stdclass();
					$objDelaRespuesta->respuesta="Este empleado esta ocupado en este momento.";
					return $response->withJson($objDelaRespuesta, 401);
				} else {
					$objDelaRespuesta = new stdclass();
					$objDelaRespuesta->respuesta="Este empleado ya esta deshabilitado.";
					return $response->withJson($objDelaRespuesta, 401);
				}
			} else {
				$objDelaRespuesta = new stdclass();
				$objDelaRespuesta->respuesta = "No puede deshabilitarse a usted mismo.";
				return $response->withJson($objDelaRespuesta, 401);
			}
		}
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="Empleado inexistente";
		return $response->withJson($objDelaRespuesta, 401);
	}
	
	public function TraerMetricas($request, $response, $args) {
		$metricas = Empleado::Analytics();
		$newResponse = $response->withJson($metricas, 200);  
		return $newResponse;
	}
}

?>