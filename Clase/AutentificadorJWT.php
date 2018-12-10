<?php

use \Firebase\JWT\JWT;

class AutentificadorJWT {

    private static $clave = "ContraToken1234";
    private static $tipoEncriptacion = ['HS256'];
    
    public static function CrearToken($datos)
    {
        $ahora = time();
        
        $payload = array(
        	'iat'=>$ahora,
            'exp' => $ahora + (60*60),
            'data' => $datos,
            self::$tipoEncriptacion
        );
     
        return JWT::encode($payload, self::$clave);
    }

    public static function ObtenerData($token)
    {
        return JWT::decode(
            $token,
            self::$clave,
            self::$tipoEncriptacion
        )->data;
    }

    public static function VerificarToken($token)
    {
       
        if(empty($token) || $token == "")
        {
            throw new Exception("El token esta vacio.");
        } 
        // las siguientes lineas lanzan una excepcion, de no ser correcto o de haberse terminado el tiempo       
        try {
            $decodificado = JWT::decode(
            $token,
            self::$clave,
            self::$tipoEncriptacion
            );
        } catch (ExpiredException $e) {
           throw new Exception("Clave fuera de tiempo");
        }
    }
}