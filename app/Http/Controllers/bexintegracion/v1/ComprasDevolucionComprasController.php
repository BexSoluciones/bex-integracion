<?php

namespace App\Http\Controllers\bexintegracion\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ConexionesModel;
use App\Traits\TraitApiWms;

class ComprasDevolucionComprasController extends Controller
{
    use TraitApiWms;

    public function getComprasDevolucionCompras()
    {
        $conexion = ConexionesModel::where('id_conexion','1')->get()[0];
        $dataCompras = $this->getWmsTransacciones($conexion,'consulta_compras');
        $dataDevCompras = $this->getWmsTransacciones($conexion,'consulta_devolucion_mercancia');
        if (!empty($dataCompras['detail']) || !empty($dataDevCompras['detail'])) {
            $jsonResponse = $this->createJson($dataCompras,$dataDevCompras);   
            return response()->json([
                'code' => 200,
                'data' => $jsonResponse
            ], 200);
        }else{
            return response()->json([
                'code' => 404,
                'errors' => 'No se encontraron registros'
            ], 404);
        }        
    }
    
    public function createJson($compras = null, $devCompras = null)
    {
        $count = '0';
        if (!empty($compras)) {
            foreach ($compras as $compra => $value) {
                foreach ($value as $key => $datos) {
                    $nit = $datos['partner_id'];
                    $razonSoc = $datos['partner_id'] = '860002518' ? 'UNILEVER ANDINA COLOMBIA LTDA' : 'PROORIENTE';
                    $suc = $datos['partner_id'] = '860002518' ? '001' : '002';
                    $bod = $datos['almacen'];
                    $data = [
                        'consec_doc' => (string) $datos['name'],
                        'cia' => '1',
                        'centro_operacion' => '001',
                        'tipo_doc' => 'EN',
                        'fecha_doc' => $datos['date_approve'],
                        'periodo_doc'=> substr($datos['date_approve'], 0, 4).substr($datos['date_approve'], 5, 2).substr($datos['date_approve'], 8, 2),
                        'nit' => $nit,
                        'razon_social' => $razonSoc,
                        'sucursal_terc' => $suc,
                        'valor_base_gravable' => '0.0000',
                        'observacion' => '',
                    ];

                    $countDet = '0';
                    foreach ($datos['detalle'] as $keyb => $det) {
                        $valTotal = $det['price_unit'] * $det['product_qty'];
                        $valorImp =  $valTotal * ($det['taxes_id']/100);

                        $detalle = [
                            'item' => $det['product_id'],
                            'Bodega' => $bod, // ES MEJOR QUE ME ENVIÉ EL CODIGO DE LA BOD
                            'unid_medida' => 'UNID',
                            'cantidad' => (string) $det['product_qty'],
                            'costo_prom_unit' => (string) $det['price_unit'],
                            'costo_prom_total' => (string) $valTotal,
                            'precio_unit' => (string) $det['price_unit'],
                            'valor_bruto' => (string) $det['price_unit'],
                            'valor_impuesto' => (string) $valorImp,
                            'valor_neto' => (string) $valTotal,
                            'descuento' => (string) '',
                            'impuesto' => (string) $det['taxes_id'],
                        ];
                        $arrayDet[$countDet] = $detalle;
                        $countDet++;
                    }
                    $arrayCompDevComp[$count] = $data;
                    $arrayCompDevComp[$count]['detalle'] = $arrayDet;
               
                    $count++;
                }
            }
        }
        if (!empty($devCompras)) {
            foreach ($devCompras as $compra => $value) {
                foreach ($value as $key => $datos) {
                    $nit = $datos['partner_id'];
                    $razonSoc = $datos['partner_id'] = '860002518' ? 'UNILEVER ANDINA COLOMBIA LTDA' : 'PROORIENTE';
                    $suc = $datos['partner_id'] = '860002518' ? '001' : '002';
                    // $bod = $datos['almacen']; AGREGAR BODEGA EN LA DEVOLUCIÓN
                    $data = [
                        'consec_doc' => (string) $datos['name'],
                        'cia' => '1',
                        'centro_operacion' => '001',
                        'tipo_doc' => 'SA',
                        'fecha_doc' => $datos['date_done'],
                        'periodo_doc'=> substr($datos['date_done'], 0, 4).substr($datos['date_done'], 5, 2).substr($datos['date_done'], 8, 2),
                        'nit' => $nit,
                        'razon_social' => $razonSoc,
                        'sucursal_terc' => $suc,
                        'valor_base_gravable' => '0.0000',
                        'observacion' => $datos['observation'],
                    ];

                    $countDet = '0';
                    foreach ($datos['detalle'] as $keyb => $det) {
                        // $valTotal = $det['price_unit'] * $det['product_qty'];
                        // $valorImp =  $valTotal * ($det['taxes_id']/100);

                        $detalle = [
                            'item' => $det['product_id'],
                            'Bodega' => 'PROORIENTE: Receipts', // ES MEJOR QUE ME ENVIÉ EL CODIGO DE LA BOD
                            'unid_medida' => 'UNID',
                            'cantidad' => (string) $det['qty_done'],
                            // 'costo_prom_unit' => (string) $det['price_unit'],
                            // 'costo_prom_total' => (string) $valTotal,
                            // 'precio_unit' => (string) $det['price_unit'],
                            // 'valor_bruto' => (string) $det['price_unit'],
                            // 'valor_impuesto' => (string) $valorImp,
                            // 'valor_neto' => (string) $valTotal,
                            // 'descuento' => (string) '',
                            // 'impuesto' => (string) $det['taxes_id'],
                        ];
                        $arrayDet[$countDet] = $detalle;
                        $countDet++;
                    }
                    $arrayCompDevComp[$count] = $data;
                    $arrayCompDevComp[$count]['detalle'] = $arrayDet;
               
                    $count++;
                }
            }
        }
        return $arrayCompDevComp;
    }

    public function validateDocType($productoEcom)
    {
        $resp = strpos($productoEcom, 'P');

        if ($resp === 0) {
            return 'EN';
        } else {
            return 'SA';
        }
    }


}