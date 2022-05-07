<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;


trait TraitApiWms
{
    public function getWmsComprasDevolucioCompras($request)
    {
        // Log::info($request);
        // return $request->siesa_url.'consulta_compras';
        $compra = HTTP::post($request->siesa_url.'consulta_compras',[
            'url_rpc' => $request->siesa_id_consulta,
            'db_rpc' => $request->siesa_conexion,
            'email_rpc' =>  $request->siesa_usuario,
            'token_rpc' =>  $request->siesa_clave,
        ]);
        $compraArray = $compra->json();

        $devCompra = HTTP::post($request->siesa_url.'consulta_ventas',[
            'url_rpc' => $request->siesa_id_consulta,
            'db_rpc' => $request->siesa_conexion,
            'email_rpc' =>  $request->siesa_usuario,
            'token_rpc' =>  $request->siesa_clave,
        ]);
        $devcompraArray = $devCompra->json();
        $compraDevCompraArray = $compraArray + $devcompraArray;
        // return $compraArray;
        return $compraDevCompraArray;
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
}
