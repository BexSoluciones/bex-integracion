<?php

namespace App\Http\Controllers\integracionjuandhoyos\v1;

use App\Http\Controllers\Controller;
use App\Models\DetalleFacturaModel;
use App\Models\EncabezadoFacturaModel;
use App\Traits\TraitHerramientas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IngresoFacturaController extends Controller
{
    use TraitHerramientas;

    const CAMPOS_AUTORIZADOS_ENCABEZADO = [
        ['campo' => 'tipo_documento'],
        ['campo' => 'numero_factura'],
        ['campo' => 'fecha_factura'],
        ['campo' => 'nit'],
        ['campo' => 'sucursal_cliente'],
        ['campo' => 'cedula_vendedor'],
        ['campo' => 'nombre_vendedor'],
        ['campo' => 'medio_pago'],
        ['campo' => 'valor_medio_pago'],
        ['campo' => 'referencia_medio_pago'],
        ['campo' => 'fecha_consignacion_cheque'],
        ['campo' => 'tipo_documento_remision'],
        ['campo' => 'numero_documento_remision'],
        ['campo' => 'bodega'],
        ['campo' => 'centro_operacion'],
        ['campo' => 'observaciones_factura'],
        ['campo' => 'detalle_factura'],
    ];

    const CAMPOS_AUTORIZADOS_DETALLE = [
        ['campo' => 'codigo_producto'],
        ['campo' => 'lista_precio'],
        ['campo' => 'cantidad'],
        ['campo' => 'valor_bruto'],
    ];

    public function recibirFacturaJson(Request $request)
    {
        $respValidacion = $this->validarEstructuraJson($request);

        if ($respValidacion['valid'] == false) {

            return response()->json([
                'created' => false,
                'code' => 412,
                'errors' => $respValidacion['errors'],
            ], 412);
        }
        
        $facturasResp=[];
        $facturasRecib = $request->input('data');
        foreach ($facturasRecib as $key => $value) {
            $facturasResp[$key]['tipo_documento']=$value['tipo_documento'];
            $facturasResp[$key]['numero_factura']=$value['numero_factura'];
        }

        try {

            $this->guardarEncabezadoFactura($request);
            $this->guardarDetalleFactura($request);

            return response()->json([
                'created' => true,
                'code' => 201,
                'errors' =>0 ,
                'facturas_recibidas'=>$facturasResp
            ], 201);

        } catch (\Exception $e) {
            Log::error("Error al guardar la factura. Detalle error: {$e->getCode()},revisar linea: {$e->getLine()},{$e->getMessage()}");
            return response()->json([
                'created' => false,
                'code' => 500,
                'errors' =>"Error de servidor por favor contactarse con el administrador",
            ], 500);
        }
    }
    
    public function validarEstructuraJson($request)
    {
        //--------Valido que exista data
        $formatoValido = false;
        $formatoValido = $request->input('data') ?? false;

        if (!$formatoValido) {
            return [
                'valid' => false,
                'errors' => "Formato json no válido, data no está definido",
            ];
        }

        //--------Defino data
        $this->data = $request->input('data');

        //--------Valido que exista detalle factura
        $erroresTotal = [];
        $erroresEncabezado = [];
        $contEE = 0;
        $erroresDetalleNoDefinido = [];
        $contED = 0;
        foreach ($this->data as $key => $data) {
            $datosEnc = $data;
            unset($datosEnc['detalle_factura']);
            $datosEnc = $this->depurarCamposAutorizados(self::CAMPOS_AUTORIZADOS_ENCABEZADO, $datosEnc);

            $respValidarEncabezado = $this->validarEncabezadoFactura($datosEnc);
            if ($respValidarEncabezado['valid'] == false) {
                $erroresEncabezado[$contEE] = $respValidarEncabezado['errors'];
                $erroresTotal ['registro_'.($key+1)]['tipo_documento'] = $data['tipo_documento'];
                $erroresTotal ['registro_'.($key+1)]['numero_factura'] = $data['numero_factura'];
                $erroresTotal['registro_'.($key+1)]['error_encabezado_factura'] = $erroresEncabezado;
                $contEE++;
            }

            $formatoValido = false;
            $formatoValido = $request->input('data.' . $key . '.detalle_factura') ?? false;
            
            if (!$formatoValido) {
                $erroresTotal['registro_'.($key+1)]['error_detalle_factura'] = "Formato json no válido, detalle factura " . $data['numero_factura'] . " no está definido";
                $contED++;
            } elseif($formatoValido) {
                $item = 1;
                $erroresDetalleFactura = [];
                foreach ($data['detalle_factura'] as $keyb => $detalleFactura) {
                    $detalleFactura=$this->depurarCamposAutorizados(self::CAMPOS_AUTORIZADOS_DETALLE, $detalleFactura);
                    $respValidacion = $this->validarDetalleFactura($detalleFactura);
                    if ($respValidacion['valid'] == false) {
                        $erroresDetalleFactura['item_' . $item] = $respValidacion['errors'];
                    }
                    $item++;
                }
                
                if(count($erroresDetalleFactura)>0){
                    $erroresTotal ['registro_'.($key+1)]['tipo_documento'] = $data['tipo_documento'];
                    $erroresTotal ['registro_'.($key+1)]['numero_factura'] = $data['numero_factura'];
                    $erroresTotal['registro_'.($key+1)]['error_detalle_factura'] = $erroresDetalleFactura;
                    $contED++;
                }
            }
        }

        if (count($erroresTotal) > 0) {
            return [
                'valid' => false,
                'errors' => $erroresTotal,
            ];
        } else {
            return [
                'valid' => true,
                'errors' => 0,
            ];
        }
    }

    public function validarEncabezadoFactura($datosEncFactura)
    {
        //------Elimino detalle factura el cual no esta dentro de esta validación
        $datosEncFactura = $this->decodificarArray($datosEncFactura);
        log::info($datosEncFactura);
        $rules = [
            'tipo_documento' => 'required',
            'numero_factura' => 'required',
            'fecha_factura' => 'required|date_format:"Ymd"',
            'nit' => 'required|max:15',
            'sucursal_cliente' => 'required|digits_between:1,3',
            'cedula_vendedor' => 'required',
            'nombre_vendedor' => 'required',
            'medio_pago' => [
                'required',
                Rule::in(['CG1', 'CHD','CHE','CHP','EFE','TC','TD']),
            ],
            'valor_medio_pago' => 'required|regex:/^[0-9]+(\.[0-9]{1,4})?$/',
            'tipo_documento_remision' => 'required',
            'numero_documento_remision' => 'required',
            'bodega' => 'required',
            'centro_operacion' => 'required|digits_between:1,3',
            'observaciones_factura' => 'max:2000',
        ];
        
        if($datosEncFactura['medio_pago'] =='CG1'){
            $rules['referencia_medio_pago']='required';
            $rules['fecha_consignacion_cheque']='required|date_format:"Ymd"';
        }

        $validator = Validator::make($datosEncFactura, $rules);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors(),
            ];
        } else {
            return [
                'valid' => true,
                'errors' => 0,
            ];
        }
    }

    public function validarDetalleFactura($datosDetalleFactura)
    {
        
        $rules = [
            'codigo_producto' => 'required',
            'cantidad' => 'required|digits_between:1,15',
            'valor_bruto' => 'required|regex:/^[0-9]+(\.[0-9]{1,4})?$/',
            'lista_precio'=>'required|max:3'
        ];

        $validator = Validator::make($datosDetalleFactura, $rules);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors(),
            ];
        } else {
            return [
                'valid' => true,
                'errors' => 0,
            ];
        }
    }

    public function depurarCamposAutorizados($camposAutorizados, $data)
    {
        $nuevoArray = [];
        foreach ($data as $campo => $valor) {

            foreach ($camposAutorizados as $key => $value) {

                if ($value['campo'] === $campo) {
                    $nuevoArray[$campo] = $valor;
                }
            }

        }

        return $nuevoArray;
    }

    public function guardarEncabezadoFactura($request)
    {

        $facturas = $request->input('data');
        $encabezadosFacturas = [];
        $contadorFactura = 0;
        foreach ($facturas as $key => $value) {
            $nuevoArray = [];
            $value['ip']=$this->getIpCliente();
            foreach ($value as $campo => $valor) {                
                if ($campo != 'detalle_factura') {
                    $nuevoArray[$campo] = $valor;
                }
            }
            $encabezadosFacturas[$contadorFactura] = $nuevoArray;
            $contadorFactura++;
        }
        EncabezadoFacturaModel::insertOrIgnore($encabezadosFacturas);
    }

    public function guardarDetalleFactura($request)
    {
        $facturas = $request->input('data');
        $contadorItem = 0;
        $facturaItem = [];
        foreach ($facturas as $keya => $factura) {
            $numeroFactura = $factura['numero_factura'];
            $centroOperacion = $factura['centro_operacion'];
            $tipoDoc = $factura['tipo_documento'];
            $bodega = $factura['bodega'];
            $detalleFactura = $factura['detalle_factura'];

            foreach ($detalleFactura as $keyb => $item) {
                $item['centro_operacion'] = $centroOperacion;
                $item['tipo_documento'] = $tipoDoc;
                $item['numero_factura'] = $numeroFactura;
                $item['bodega'] = $bodega;
                $item['CODTIPODOC'] = '5';
                $facturaItem[$contadorItem] = $item;
                $contadorItem++;
            }
        }
        DetalleFacturaModel::insertOrIgnore($facturaItem);
    }
}
