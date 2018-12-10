<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require './Clase/Referencias.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$app = new \Slim\App(["settings" => $config]);

$app->post('/login/', \Login::class . ':UserLogin');

$app->group('/api', function () use ($app) {

    $this->group('/comanda', function () use ($app) {
        $this->post('/', \ComandaApi::class . ':CargarUno')->add(\MWVerificacion::class . ':VerificarMozo')->add(\MWVerificacion::class . ':VerificarToken');
        $this->delete('/{id}', \ComandaApi::class . ':BorrarUno')->add(\MWVerificacion::class . ':VerificarMozo')->add(\MWVerificacion::class . ':VerificarToken');
        //se utiliza post al modificar para subir la foto
        $this->post('/{id}', \ComandaApi::class . ':ModificarUno')->add(\MWVerificacion::class . ':VerificarMozo')->add(\MWVerificacion::class . ':VerificarToken');
        $this->get('/{codigoMesa}/{codigoComanda}', \ComandaApi::class . ':TraerUno');
        $this->get('/', \ComandaApi::class . ':TraerTodos')->add(\MWVerificacion::class . ':VerificarMozo')->add(\MWVerificacion::class . ':VerificarToken');
        $this->post('/cobrar/', \ComandaApi::class . ':CobrarUno')->add(\MWVerificacion::class . ':VerificarMozo')->add(\MWVerificacion::class . ':VerificarToken');
    });

    $app->group('/empleado', function () use ($app) {
        $this->post('/', \EmpleadoApi::class . ':CargarUno')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->delete('/{id}', \EmpleadoApi::class . ':BorrarUno')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->put('/{id}', \EmpleadoApi::class . ':ModificarUno')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->get('/{id}', \EmpleadoApi::class . ':TraerUno')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->get('/', \EmpleadoApi::class . ':TraerTodos')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->post('/tomar_pedido', \EmpleadoApi::class . ':TomarUnPedido')->add(\MWVerificacion::class . ':VerificarEmpleado');
        $this->post('/entregar_pedido', \EmpleadoApi::class . ':EntregarUnPedido')->add(\MWVerificacion::class . ':VerificarEmpleado');
        $this->post('/habilitar_empleado', \EmpleadoApi::class . ':HabilitarUno')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->post('/deshabilitar_empleado', \EmpleadoApi::class . ':DeshabilitarUno')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->get('/metricas/', \EmpleadoApi::class . ':TraerMetricas')->add(\MWVerificacion::class . ':VerificarAdmin');
    })->add(\MWVerificacion::class . ':VerificarToken');

    $app->group('/mesa', function () use ($app) {
        $this->post('/', \MesaApi::class . ':CargarUno')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->delete('/{id}', \MesaApi::class . ':BorrarUno')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->put('/{id}', \MesaApi::class . ':ModificarUno')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->get('/{id}', \MesaApi::class . ':TraerUno')->add(\MWVerificacion::class . ':VerificarMozo');
        $this->get('/', \MesaApi::class . ':TraerTodos')->add(\MWVerificacion::class . ':VerificarMozo');
        $this->post('/cerrar', \MesaApi::class . ':CerrarUno')->add(\MWVerificacion::class . ':VerificarMozo');
    })->add(\MWVerificacion::class . ':VerificarToken');

    $app->group('/pedido', function () use ($app) {
        $this->post('/', \PedidoApi::class . ':CargarUno')->add(\MWVerificacion::class . ':VerificarMozo');
        $this->delete('/{id}', \PedidoApi::class . ':BorrarUno')->add(\MWVerificacion::class . ':VerificarMozo');
        $this->put('/{id}', \PedidoApi::class . ':ModificarUno')->add(\MWVerificacion::class . ':VerificarMozo');
        $this->get('/{id}', \PedidoApi::class . ':TraerUno')->add(\MWVerificacion::class . ':FiltrarPedidos');
        $this->get('/', \PedidoApi::class . ':TraerTodos')->add(\MWVerificacion::class . ':FiltrarPedidos');
        $this->post('/entregar_pedido', \PedidoApi::class . ':EntregarACliente')->add(\MWVerificacion::class . ':VerificarMozo');
        $this->post('/cancelar_pedido', \PedidoApi::class . ':CancelarUno')->add(\MWVerificacion::class . ':VerificarMozo');
    })->add(\MWVerificacion::class . ':VerificarToken');

    $app->group('/encuesta', function () use ($app) {
        $this->post('/', \EncuestaApi::class . ':CargarUno');
        $this->get('/', \EncuestaApi::class . ':TraerTodos');
        $this->get('/{id}', \EncuestaApi::class . ':TraerUno');
    });

    $app->group('/log', function () use ($app) {
        $this->get('/', \LogApi::class . ':TraerTodos')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->get('/{id}', \LogApi::class . ':TraerUno')->add(\MWVerificacion::class . ':VerificarAdmin');
        $this->get('/exportar/', \LogApi::class . ':ExportarTodosExcel')->add(\MWVerificacion::class . ':VerificarAdmin');
    })->add(\MWVerificacion::class . ':VerificarToken');
});

$app->run();