<?php
class Comanda
{
    public $id;
    public $nombreCliente;
    public $codigo;
    public $importe;
    public $idMesa;
    public $foto;
    
    public function GetNombreCliente() {
        return $this->nombreCliente;
    }
    public function GetCodigo() {
        return $this->codigo;
    }
    public function GetImporte() {
        return $this->importe;
    }
    public function GetIdMesa() {
        return $this->idMesa;
    }
    public function GetFoto() {
        return $this->foto;
    }
    public function SetNombreCliente($value) {
        $this->nombreCliente = $value;
    }
    public function SetCodigo($value) {
        $this->codigo = $value;
    }
    public function SetImporte($value) {
        $this->importe = (float)$value;
    }
    public function SetIdMesa($value) {
        $this->idMesa = $value;
    }
    public function SetFoto($value) {
        $this->foto = $value;
    }

    public function __construct(){}
    
    public function InsertarComanda($nuevoCodigo) {
        $mesa = Mesa::TraerMesa($this->idMesa);
        
        if ($mesa && $mesa->GetEstado() == 'cerrada') {
            $mesa->SetEstado('esperando');
            $mesa->ModificarMesa();
            $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
            $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into comandas (nombreCliente,codigo,idMesa,foto)
                values(
                '$this->nombreCliente',
                '$nuevoCodigo',
                '$this->idMesa',
                '$this->foto'
                );");
            $consulta->execute();
            return $nuevoCodigo;
        } else {
            return NULL;
        }
    }

    public function BorrarComanda() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            delete
            from comandas
            WHERE id=$this->id;");
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function ModificarComanda() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

        if($this->foto == null) {
            $consulta = $objetoAccesoDato->RetornarConsulta("
                update comandas 
                set nombreCliente='$this->nombreCliente',
                importe='$this->importe',
                idMesa='$this->idMesa'
                WHERE id=$this->id;");
        } 
        else {
            $consulta = $objetoAccesoDato->RetornarConsulta("
                update comandas 
                set nombreCliente='$this->nombreCliente',
                importe='$this->importe',
                idMesa='$this->idMesa',
                foto='$this->foto'
                WHERE id=$this->id;");
        }

        return $consulta->execute();
    }

    public function AgregarFoto($archivos, $codComanda) {
        $destino = "./fotos/";
        $nombreAnterior = $archivos['foto']->getClientFilename();
        $extension = explode(".", $nombreAnterior);
        $extension = array_reverse($extension);
        $archivos['foto']->moveTo($destino.$codComanda.".".$extension[0]);
        $this->foto = $codComanda.".".$extension[0];
    }
    
    public static function TraerComanda($codigoComanda) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("select * from comandas where codigo = '$codigoComanda';");
        $consulta->execute();
        $comandaResultado = $consulta->fetchObject('Comanda');
        return $comandaResultado;
    }

    public static function TraerComandas() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("select * from comandas;");
        $consulta->execute();
        $comandas = $consulta->fetchAll(PDO::FETCH_CLASS, "Comanda");
        return $comandas;
    }

    public function CobrarComanda($importe) {
        $mesa = Mesa::TraerMesa($this->idMesa);
        if ($mesa) {
            if ($mesa->estado == 'comiendo') {
                $mesa->SetEstado('pagando');
                $mesa->ModificarMesa();
                $this->SetImporte($importe);
                $this->ModificarComanda();
                return 'OK';
            } else if ($mesa->estado == 'pagando') {
                return 'Estos clientes ya estan pagando';
            } else if ($mesa->estado == 'esperando') {
                return 'Estos clientes aún están esperando pedido/s';
            } else {
                return 'Esta comanda ya ha sido cerrada';
            }
        }
        return 'Error encontrando la mesa de su comanda';
    }
}