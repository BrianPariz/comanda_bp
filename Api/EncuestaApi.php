<?php

class EncuestaApi extends Encuesta implements IApiUsable
{
	public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$comanda = Comanda::TraerComanda($ArrayDeParametros['idComanda']);
		if ($comanda) {
			$miencuesta = new Encuesta();
			$miencuesta->idComanda = $ArrayDeParametros['idComanda'];
			$miencuesta->puntosMozo = $ArrayDeParametros['puntosMozo'];
			$miencuesta->puntosMesa = $ArrayDeParametros['puntosMesa'];
			$miencuesta->puntosRestaurante = $ArrayDeParametros['puntosRestaurante'];
			$miencuesta->puntosCocinero = $ArrayDeParametros['puntosCocinero'];
			$miencuesta->comentario = $ArrayDeParametros['comentario'];
            $miencuesta->InsertarEncuesta();
			//Cargo el log
			if ($request->getAttribute('empleado')) {
				$new_log = new Log();
				$new_log->idEmpleado = $request->getAttribute('empleado')->id;
				$new_log->accion = "Realizar encuesta";
				$new_log->InsertarLog();
			}
			//--
			$objDelaRespuesta= new stdclass();
			$objDelaRespuesta->respuesta = "Gracias por realizar nuestra encuestra!";
			return $response->withJson($objDelaRespuesta, 200);		
		} else {
			$objDelaRespuesta= new stdclass();
			$objDelaRespuesta->respuesta = "Codigo de comanda inexistente!";
			return $response->withJson($objDelaRespuesta, 400);
		}
    }
    
	public function TraerTodos($request, $response, $args) {
		$encuestas = Encuesta::TraerEncuestas();
		$newResponse = $response->withJson($encuestas, 200);  
		return $newResponse;
    }
    
	public function TraerUno($request, $response, $args) {
		$id = $args['id'];
		$encuestaObj = Encuesta::TraerEncuesta($id);
		$newResponse = $response->withJson($encuestaObj, 200);  
		return $newResponse;
    }
    
    public function BorrarUno($request,$response,$args) {}
    public function ModificarUno($request,$response,$args) {}  
}