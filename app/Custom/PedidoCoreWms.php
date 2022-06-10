<?php

namespace App\Custom;

use App\Models\BodegasTiposDocModel;
use App\Models\ConexionesModel;
use App\Models\LogErrorImportacionModel;
use App\Traits\TraitApiWms;
use App\Traits\TraitHerramientas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PedidoCoreWms
{
    use TraitApiWms;
    

    public function crearPedidoWms($pedido, $detallesPedido)
    {
        // Log::info($pedido);
        // Log::info($detallesPedido);
        if (count($detallesPedido) > 0) {
            $importar = true;
            
            $conexion = ConexionesModel::where('id_conexion', '3')->get()[0];
            
            $arrayPedido = [
                'url_rpc' => $conexion->siesa_id_consulta,
                'db_rpc' => $conexion->siesa_conexion,
                'email_rpc' => $conexion->siesa_usuario,
                'token_rpc' => $conexion->siesa_clave,
                'name' => $pedido['tipo_documento'].'-'.$pedido['numero_pedido'],
                'partner_id' => (string) $pedido['nit_cliente'],
                'date_order' => substr($pedido['fecha_pedido'], 0, 10),
            ];
            $detProd = array();
            $countDet = 0;
            foreach ($detallesPedido as $key => $detallePedido) {
                
                $detProd[$countDet] = [
                    'product_id' => $detallePedido['codigo_producto'],
                    'product_uom_qty' => $detallePedido['cantidad'],
                    'tax_id' => $detallePedido['IVAMOV'],
                    'price_unit' => $detallePedido['precio_unitario'],
                    'discount' => is_null($detallePedido['DCTO1MOV']) ? '0' : $detallePedido['DCTO1MOV'],
                ];
                $countDet++;
            }
            $arrayPedido['detalle'] = $detProd;
        }else{
            $error = 'El pedido no tiene productos asignados';
            $estado = "3";
            $this->logErrorImportarPedido($error, $estado, $pedido['centro_operacion'], $pedido['bodega'], $pedido['tipo_documento'], $pedido['numero_pedido']);    
        }

        $collectionClient = collect(['pedido' => $arrayPedido]);
        Storage::disk('local')->put('/public/pedidos/' . $pedido['tipo_documento'].'-'.str_pad($pedido['numero_pedido'], 10, "0", STR_PAD_LEFT).'.json', $collectionClient);
        $response = $this->postPedidoWms($conexion,$arrayPedido,$pedido);
        // Log::info($response);
        return 'pedido recibido';
    }

    public function postPedidoWms($conexion,$arrayPedido,$pedido)
    {
        $conexionToken = ConexionesModel::where('id_conexion', '2')->get()[0];
        $access_token = $this->getWmsAccesTokenWms($conexionToken);
        $response = $this->postWmsCrearPedidos($access_token,$conexion->siesa_url,$arrayPedido);
        // Log::info($response);
        if (isset($response['detail']['error_rpc'])) {
            foreach ($response as $key => $valuea) {
                foreach ($valuea as $key => $valueb) {
                    foreach ($valueb as $key => $valuec) {
                        $apiResp = $valuec;
                    }
                    
                }
            }
            if($apiResp['code'] == 400) {
                $error = $apiResp['msg'];
                $estado = "2";
                $this->logErrorImportarPedido($error, $estado, $pedido['centro_operacion'], $pedido['bodega'], $pedido['tipo_documento'], $pedido['numero_pedido']);
            }else{
                $error = $apiResp['msg'];
                $estado = "3";
                $this->logErrorImportarPedido($error, $estado, $pedido['centro_operacion'], $pedido['bodega'], $pedido['tipo_documento'], $pedido['numero_pedido']);
            }
        }else{
            if(isset($response['detail']['name'])){
                $error = 'Ok';
                $estado = "2";
                $this->logErrorImportarPedido($error, $estado, $pedido['centro_operacion'], $pedido['bodega'], $pedido['tipo_documento'], $pedido['numero_pedido']);
            }
        }
        return $response;
    }

    public function logErrorImportarPedido($mensaje, $estado, $centroOperacion, $bodega, $tipoDocumento, $numeroPedido)
    {
        $objErrorImpPed = new LogErrorImportacionModel();
        $result = $objErrorImpPed->actualizarEstadoDocumento($mensaje, $estado, $centroOperacion, $bodega, $tipoDocumento, $numeroPedido);
    }    
}
