<?php
class Mesa
{
    public $id;
    public $codigo;
    public $estado;
    
    public function GetCodigo() {
        return $this->codigo;
    }
    public function GetEstado() {
        return $this->estado;
    }

    public function SetCodigo($value) {
        $this->codigo = $value;
    }
    public function SetEstado($value) {
        $this->estado = $value;
    }

    public function InsertarMesa() {
        $nuevoCodigo = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 5);
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into mesas (codigo,estado)values('$nuevoCodigo','$this->estado')");
        $consulta->execute();
        return $nuevoCodigo;
    }

    public function BorrarMesa() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            delete
            from mesas
            WHERE id=$this->id");
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function ModificarMesa() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->RetornarConsulta("
            update mesas 
            set estado='$this->estado'
            WHERE id=$this->id");
        return $consulta->execute();
    }

    public static function TraerMesa($id) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from mesas where codigo = '$id'");
        $consulta->execute();
        $mesaResultado= $consulta->fetchObject('Mesa');
        return $mesaResultado;
    }

    public static function TraerMesas() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from mesas");
        $consulta->execute();
        $mesas = $consulta->fetchAll(PDO::FETCH_CLASS, "Mesa");
        return $mesas;
    }
    
    public function CerrarMesa() {
        $this->estado = "cerrada";
        $this->ModificarMesa();
    }

    public static function CerrarMesasSinComanda() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
        update mesas 
        set estado = 'cerrada'
        WHERE codigo not in (SELECT idMesa FROM comandas)");
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function toString() {
        return "Mesa:".$this->codigo."  ".$this->estado;
    }
}