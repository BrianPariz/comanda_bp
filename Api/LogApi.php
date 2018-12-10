<?php

class logApi extends Log implements IApiUsable
{
	public function TraerUno($request, $response, $args) {
		$id = $args['id'];
		$logObj = Log::TraerLog($id);
		$newResponse = $response->withJson($logObj, 200);  
		return $newResponse;
	}

	public function TraerTodos($request, $response, $args) {
		$logs = Log::TraerLogs();
		$newResponse = $response->withJson($logs, 200);  
		return $newResponse;
	}
	
	public function ExportarTodosExcel($request, $response, $args) {
		header("Location:ExcelExport.php");
	}

    
    public function CargarUno($request,$response,$args) {}  
    public function BorrarUno($request,$response,$args) {}
    public function ModificarUno($request,$response,$args) {}  
}