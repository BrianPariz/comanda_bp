<?php

class Empleado
{
    public $id;
    public $usuario;
    public $clave;
    public $sector;
    public $estado;
    public $sueldo;
    
    public function GetUsuario() {
        return $this->usuario;
    }
    public function GetClave() {
        return $this->clave;
    }
    public function GetSector() {
        return $this->sector;
    }
    public function GetEstado() {
        return $this->estado;
    }
    public function GetSueldo() {
        return $this->sueldo;
    }

    public function SetUsuario($value) {
        $this->usuario = $value;
    }
    public function SetClave($value) {
        $this->clave = $value;
    }
    public function SetSector($value) {
        $this->sector = $value;
    }

    public function SetEstado($value) {
        $this->estado = $value;
    }

    public function SetSueldo($value) {
        $this->sueldo = $value;
    }

    public static function ValidarEmpleado($usuario, $clave) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("select * from empleados where usuario='$usuario' and clave='$clave'");
        $consulta->execute();
        $empleadoResultado= $consulta->fetchObject('Empleado');
        return $empleadoResultado;
    }

    public function InsertarEmpleado() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into 
        empleados (usuario,clave,sector,estado,sueldo)
        values('$this->usuario','$this->clave','$this->sector','$this->estado','$this->sueldo')");
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function BorrarEmpleado() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->RetornarConsulta("
            delete
            from empleados
            WHERE id=$this->id");
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function ModificarEmpleado() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->RetornarConsulta("
            update empleados 
            set usuario='$this->usuario',
            clave='$this->clave',
            sector='$this->sector',
            estado='$this->estado',
            sueldo='$this->sueldo'
            WHERE id='$this->id'");
        return $consulta->execute();
    }

    public static function TraerEmpleado($id) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("select * from empleados where id = $id");
        $consulta->execute();
        $empleadoResultado = $consulta->fetchObject('Empleado');
        return $empleadoResultado;
    }
    
    public static function TraerEmpleados() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("select * from empleados");
        $consulta->execute();
        $empleados = $consulta->fetchAll(PDO::FETCH_CLASS, "Empleado");
        return $empleados;
    }

    public function TomarPedido($pedido, $tiempo) {
        $pedido = Pedido::TraerPedido($pedido);
        if($pedido->estado == 'pendiente') {
            $this->estado = 'ocupado';
            $this->ModificarEmpleado();
            $pedido->SetEstimacion($tiempo);
            $pedido->idEmpleado = $this->id;
            $pedido->estado = 'preparandose';
            $pedido->ModificarPedido();
            return "Se le ha asignado el pedido para la comanda #".$pedido->GetIdComanda().
            " Detalles del pedido: ".$pedido->GetDescripcion();
        }
        else {
            return "El pedido no esta pendiente";
        }
    }

    public static function EntregarPedido($id) {
        $pedido = Pedido::TraerPedido($id);
        $pedido->estado = 'listo';
        $pedido->fechaEntregado = date("Y-m-d H:i:s");
        $pedido->ModificarPedido();
        $empleado = Empleado::TraerEmpleado($pedido->idEmpleado);
        $empleado->estado = 'habilitado';
        $empleado->ModificarEmpleado();
        return "Pedido #$id entregado.";
    }

    public function HabilitarEmpleado() {
        $this->estado = "habilitado";
        $this->ModificarEmpleado();
    }

    public function DeshabilitarEmpleado() {
        $this->estado = "deshabilitado";
        $this->ModificarEmpleado();
    }

    public function toString() {
        return "Metodo mostrar:".$this->usuario."  ".$this->clave."  ".$this->sector;
    }

    
    public static function Analytics() {
        $listaAnalytics= array();
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

        //7b
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT COUNT(l.id) as cantidad, e.sector as sector FROM logs l INNER JOIN empleados e ON l.idEmpleado=e.id GROUP BY e.sector"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $rows = array();
            foreach($resultado as $row) {
                $rowObj = new stdclass();
                $rowObj->cantidad = $row['cantidad'];
                $rowObj->sector = $row['sector'];
                array_push($rows, $rowObj);
            }
            $listaAnalytics['7b-logs_por_sector'] = $rows;
        }

        //8a
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT COUNT(id) as cantidad, descripcion as pedido FROM pedidos GROUP BY pedido ORDER BY cantidad DESC LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->pedido = $resultado[0]['pedido'];
            $result->cantidad = $resultado[0]['cantidad'];
            $listaAnalytics['8a-más_pedido'] = $result;
        }

        //8b
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT COUNT(id) as cantidad, descripcion as pedido FROM pedidos GROUP BY pedido ORDER BY cantidad LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->pedido = $resultado[0]['pedido'];
            $result->cantidad = $resultado[0]['cantidad'];
            $listaAnalytics['8b-menos_pedido'] = $result;
        }

        //8c
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT id, TIMESTAMPDIFF(MINUTE, estimacion, fechaEntregado) AS diff FROM pedidos WHERE TIMESTAMPDIFF(MINUTE, estimacion, fechaEntregado) > 0"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $rows = array();
            foreach($resultado as $row) {
                $rowObj = new stdclass();
                $rowObj->id = $row['id'];
                $rowObj->demora = $row['diff'] . " minutos";
                array_push($rows, $rowObj);
            }
            $listaAnalytics['8c-pedidos_demorados'] = $rows;
        }

        //8d
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT id, descripcion FROM pedidos WHERE estado = 'cancelado'"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $rows = array();
            foreach($resultado as $row) {
                $rowObj = new stdclass();
                $rowObj->id = $row['id'];
                $rowObj->descripcion = $row['descripcion'];
                array_push($rows, $rowObj);
            }
            $listaAnalytics['8d-pedidos_cancelados'] = $rows;
        }

        //9a
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT COUNT(id) as cantidad, idMesa as mesa FROM comandas GROUP BY idMesa ORDER BY cantidad DESC LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->cantidad = $resultado[0]['cantidad'];
            $listaAnalytics['9a-mesa_mas_usada'] = $result;
        }

        //9b
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT COUNT(id) as cantidad, idMesa as mesa FROM comandas GROUP BY idMesa ORDER BY cantidad LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->cantidad = $resultado[0]['cantidad'];
            $listaAnalytics['9b-mesa_menos_usada'] = $result;
        }

        //9c
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT SUM(importe) as importe, idMesa as mesa FROM comandas WHERE importe is not Null GROUP BY idMesa ORDER BY importe DESC LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->importe = $resultado[0]['importe'];
            $listaAnalytics['9c-mesa_mas_paga'] = $result;
        }

        //9d
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT SUM(importe) as importe, idMesa as mesa FROM comandas WHERE importe is not Null GROUP BY idMesa ORDER BY importe LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->importe = $resultado[0]['importe'];
            $listaAnalytics['9d-mesa_menos_paga'] = $result;
        }

        //9e
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT importe, idMesa as mesa FROM comandas ORDER BY importe DESC LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->importe = $resultado[0]['importe'];
            $listaAnalytics['9e-mesa_importe_mas_alto'] = $result;
        }

        //9f
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT importe, idMesa as mesa FROM comandas WHERE importe is not Null ORDER BY importe LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->importe = $resultado[0]['importe'];
            $listaAnalytics['9f-mesa_importe_mas_bajo'] = $result;
        }

        //9g
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT SUM(importe) as importe, idMesa as mesa
            FROM comandas WHERE importe is not Null
            AND codigo IN (SELECT idComanda FROM pedidos WHERE fechaIngresado >= '2018-06-25' AND fechaIngresado <= '2018-07-10')
            AND idMesa = 'g8sve' GROUP BY idMesa ORDER BY importe"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->importe = $resultado[0]['importe'];
            $listaAnalytics['9g-recaudacion_mesa_entre_fechas'] = $result;
        }
        
        //9h
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT id, (AVG(puntosMozo)+AVG(puntosMesa)+AVG(puntosRestaurante)+AVG(puntosCocinero))/4 as promedio, comentario
            FROM encuestas GROUP BY id HAVING promedio > 5"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $rows = array();
            foreach($resultado as $row) {
                $rowObj = new stdclass();
                $rowObj->id = $row['id'];
                $rowObj->comentario = $row['comentario'];
                array_push($rows, $rowObj);
            }
            $listaAnalytics['9h-mejores_comentarios'] = $rows;
        }

        //9i
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT id, (AVG(puntosMozo)+AVG(puntosMesa)+AVG(puntosRestaurante)+AVG(puntosCocinero))/4 as promedio, comentario
            FROM encuestas GROUP BY id HAVING promedio <= 5"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $rows = array();
            foreach($resultado as $row) {
                $rowObj = new stdclass();
                $rowObj->id = $row['id'];
                $rowObj->comentario = $row['comentario'];
                array_push($rows, $rowObj);
            }
            $listaAnalytics['9i-peores_comentarios'] = $rows;
        }
        return $listaAnalytics;
    }
}

?>