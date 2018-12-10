<?php

class pedidoApi extends Pedido implements IApiUsable
{
	public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$comanda = Comanda::TraerComanda($ArrayDeParametros['idComanda']);
		if ($comanda) {
			$pedido_nuevo = new Pedido();
			$pedido_nuevo->sector = $ArrayDeParametros['sector'];
			$pedido_nuevo->estado = 'pendiente';
			$pedido_nuevo->idComanda = $ArrayDeParametros['idComanda'];
			$pedido_nuevo->descripcion = $ArrayDeParametros['descripcion'];
            $pedido_nuevo->InsertarPedido();
			//Cargo el log
			if ($request->getAttribute('empleado')) {
				$new_log = new Log();
				$new_log->idEmpleado = $request->getAttribute('empleado')->id;
				$new_log->accion = "Cargar pedido";
				$new_log->InsertarLog();
			}
			//--
			$objDelaRespuesta = new stdclass();
			$objDelaRespuesta->respuesta = 'Se guardo el pedido';
			return $response->withJson($objDelaRespuesta, 200);
		} else {
			$objDelaRespuesta = new stdclass();
			$objDelaRespuesta->respuesta = 'Código de comanda inexistente';
			return $response->withJson($objDelaRespuesta, 401);
		}
	}

	public function BorrarUno($request, $response, $args) {
        $pedido = Pedido::TraerPedido($args['id']);
		$cantidadDeBorrados=$pedido->BorrarPedido();
		
		$objDelaRespuesta = new stdclass();
		if($cantidadDeBorrados > 0) {
			//Cargo el log
			if ($request->getAttribute('empleado')) {
				$new_log = new Log();
				$new_log->idEmpleado = $request->getAttribute('empleado')->id;
				$new_log->accion = "Borrar pedido";
				$new_log->InsertarLog();
			}
			//--
			$objDelaRespuesta->respuesta = "Pedido eliminado";
			return $response->withJson($objDelaRespuesta, 200);
		} else {
			$objDelaRespuesta->respuesta = "Error eliminando el pedido";
			return $response->withJson($objDelaRespuesta, 400);
		}
	}

	public function ModificarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        $mipedido = Pedido::TraerPedido($args['id']);
        
		if ($mipedido) {
			$mipedido->idComanda = $ArrayDeParametros['idComanda'];
			$mipedido->sector = $ArrayDeParametros['sector'];
			$mipedido->descripcion = $ArrayDeParametros['descripcion'];
			$mipedido->estado = $ArrayDeParametros['estado'];
            $mipedido->ModificarPedido();
			//Cargo el log
			if ($request->getAttribute('empleado')) {
				$new_log = new Log();
				$new_log->idEmpleado = $request->getAttribute('empleado')->id;
				$new_log->accion = "Modificar pedido";
				$new_log->InsertarLog();
			}
			//--
			$objDelaRespuesta = new stdclass();
			$objDelaRespuesta->respuesta = 'Pedido modificado';
			return $response->withJson($objDelaRespuesta, 200);
		} else {
			$objDelaRespuesta = new stdclass();
			$objDelaRespuesta->respuesta = 'Codigo de pedido inexistente';
			return $response->withJson($objDelaRespuesta, 401);
		}
    }

    public function TraerUno($request, $response, $args) {
		$id = $args['id'];
		$pedidoObj = Pedido::TraerPedido($id);
		$newResponse = $response->withJson($pedidoObj, 200);  
		return $newResponse;
	}

	public function TraerTodos($request, $response, $args) {
		$pedidos = Pedido::TraerPedidos();
		$newResponse = $response->withJson($pedidos, 200);  
		return $newResponse;
	}
	
	public function EntregarACliente($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		if ($ArrayDeParametros['idPedido']) {
			$respuesta = Pedido::EntregarPedido($ArrayDeParametros['idPedido']);
			//Cargo el log
			if ($request->getAttribute('empleado')) {
				$new_log = new Log();
				$new_log->idEmpleado = $request->getAttribute('empleado')->id;
				$new_log->accion = "Entregar pedido a cliente";
				$new_log->InsertarLog();
			}
			//--
			$objDelaRespuesta = new stdclass();
			$objDelaRespuesta->respuesta=$respuesta;
			return $response->withJson($objDelaRespuesta, 200);
		}
		$objDelaRespuesta = new stdclass();
		$objDelaRespuesta->respuesta = 'Debe ingresar el numero del pedido';
		return $response->withJson($objDelaRespuesta, 401);
	}

	public function CancelarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		if ($ArrayDeParametros['idPedido']) {
			$pedidoObj = Pedido::TraerPedido($ArrayDeParametros['idPedido']);
			$respuesta = $pedidoObj->Cancelar();
			//Cargo el log
			if ($request->getAttribute('empleado')) {
				$new_log = new Log();
				$new_log->idEmpleado = $request->getAttribute('empleado')->id;
				$new_log->accion = "Cancelar pedido";
				$new_log->InsertarLog();
			}
			//--
			$objDelaRespuesta= new stdclass();
			$objDelaRespuesta->respuesta=$respuesta;
			return $response->withJson($objDelaRespuesta, 200);
		}
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta='Debe ingresar el numero del pedido';
		return $response->withJson($objDelaRespuesta, 401);
	}
}