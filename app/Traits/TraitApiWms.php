<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait TraitApiWms
{
    public function getWmsTransacciones($request, $transc)
    {
        $transac = HTTP::post($request->siesa_url.$transc,[
            'url_rpc' => $request->siesa_id_consulta,
            'db_rpc' => $request->siesa_conexion,
            'email_rpc' =>  $request->siesa_usuario,
            'token_rpc' =>  $request->siesa_clave,
        ]);
        return $transac->json();
    }

    public function getWmsDevolucioCompras($request)
    {
        $devCompra = HTTP::post($request->siesa_url.'consulta_devolucion_mercancia',[
            'url_rpc' => $request->siesa_id_consulta,
            'db_rpc' => $request->siesa_conexion,
            'email_rpc' =>  $request->siesa_usuario,
            'token_rpc' =>  $request->siesa_clave,
        ]);

        return $devCompra->json();
    }

    public function getWmsAccesTokenWms($request)
    {
        $token = HTTP::post($request->siesa_url,[
            'parameter' =>
                [
                    'data' =>
                        [
                            'username' => $request->siesa_usuario,
                            'password' => $request->siesa_clave,
                        ]
                ]
           
        ]);
        $tokenArray = $token->json();
        foreach ($tokenArray as $tok => $value) {
            if($tok == 'result')
            {
                foreach ($value as $key => $values) {
                    if ($key == 'access_token') 
                    {
                        $access_token = $values;
                    }
                }   
            }
                
        }
        return $access_token;
    }

    public function postWmsCrearClientes($access_token,$conexion,$request)
    {
        $crearCliente = HTTP::withToken($access_token)->post($conexion.'clientes', $request);
        return  $crearCliente->json();
    }

    public function postWmsCrearPedidos($access_token,$conexion,$request)
    {
        // Log::info($access_token);
        // Log::info($conexion);
        // Log::info($request);
        $crearPedidos = HTTP::withToken($access_token)->post($conexion.'creacion_ventas', $request);

        return  $crearPedidos->json();
    }
}
