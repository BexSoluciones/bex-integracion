<?php

namespace App\Http\Controllers\bexintegracion\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetallePedidoModel;
use App\Models\EncabezadoPedidoModel;
use App\Traits\TraitHerramientas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IngresoPedidoController extends Controller
{
    use TraitHerramientas;

    const CAMPOS_AUTORIZADOS_ENCABEZADO = [
        ['campo' => 'numero_pedido'],
        ['campo' => 'fecha_pedido'],
        ['campo' => 'tipo_documento'],
        ['campo' => 'bodega'],
        ['campo' => 'centro_operacion'],
        ['campo' => 'tipo_cliente'],
        ['campo' => 'nit_cliente'],
        ['campo' => 'sucursal_cliente'],
        ['campo' => 'cedula_vendedor'],
        ['campo' => 'vendedor'],
        ['campo' => 'observaciones_pedido'],
        ['campo' => 'detalle_pedido'],
    ];

    const CAMPOS_AUTORIZADOS_DETALLE = [
        ['campo' => 'codigo_producto'],
        ['campo' => 'lista_precio'],
        ['campo' => 'cantidad'],
        ['campo' => 'precio_unitario'],
    ];

    public function recibirPedidoJson(Request $request)
    {
        $respValidacion = $this->validarEstructuraJson($request);

        if ($respValidacion['valid'] == false) {

            return response()->json([
                'created' => false,
                'code' => 412,
                'errors' => $respValidacion['errors'],
            ], 412);
        }
        $pedidosResp = [];
        $pedidosRecib = $request->input('data');

        foreach ($pedidosRecib as $key => $value) {
            $pedidosResp[$key]['tipo_documento'] = $value['tipo_documento'];
            $pedidosResp[$key]['numero_pedido'] = $value['numero_pedido'];
        }

        try {
            $this->guardarEncabezadoPedido($request);
            $this->guardarDetallePedido($request);

            return response()->json([
                'created' => true,
                'code' => 201,
                'errors' => 0,
                'pedidos_recibidos' => $pedidosResp
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error al guardar pedidos. Detalle error: {$e->getCode()},revisar linea: {$e->getLine()},{$e->getMessage()}");
            return response()->json([
                'created' => false,
                'code' => 500,
                'errors' => "Error de servidor por favor contactarse con el administrador",
            ], 500);
        }
    }

    public function guardarDetallePedido($request)
    {
        $pedidos = $request->input('data');
        $contadorItem = 0;
        $pedidoItem = [];

        foreach ($pedidos as $keya => $pedido) {
            $numeroPedido = $pedido['numero_pedido'];
            $centroOperacion = $pedido['centro_operacion'];
            $tipoDoc = $pedido['tipo_documento'];
            $bodega = $pedido['bodega'];
            $detallePedido = $pedido['detalle_pedido'];

            foreach ($detallePedido as $keyb => $item) {
                $item['centro_operacion'] = $centroOperacion;
                $item['tipo_documento'] = $tipoDoc;
                $item['numero_pedido'] = $numeroPedido;
                $item['bodega'] = $bodega;
                $pedidoItem[$contadorItem] = $item;
                $contadorItem++;
            }
        }

        DetallePedidoModel::insertOrIgnore($pedidoItem);
    }

    public function guardarEncabezadoPedido($request)
    {
        $pedidos = $request->input('data');
        $encabezadosPedidos = [];
        $contadorPedido = 0;

        foreach ($pedidos as $key => $value) {
            $nuevoArray = [];
            $value['ip'] = $this->getIpCliente();
            foreach ($value as $campo => $valor) {
                if ($campo != 'detalle_pedido') {
                    $nuevoArray[$campo] = $valor;
                }
            }

            $encabezadosPedidos[$contadorPedido] = $nuevoArray;
            $contadorPedido++;
        }

        EncabezadoPedidoModel::insertOrIgnore($encabezadosPedidos);
    }

    public function armarTablaInsertSql($tablaDestino)
    {
        $datos = $this->ejecutarConsulta();

        if (!empty($datos)) {

            //----se arma la tabla
            $campos = array_keys((array) $datos[0]);

            $nuevoArrayCampos = [];
            foreach ($campos as $key => $campo) {
                $nuevoArrayCampos[$key] = $campo . ' text';
            }
            $camposTabla = implode(',', $nuevoArrayCampos);

            $sqlTabla = "CREATE TABLE $tablaDestino  ( $camposTabla ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; ";

            //-----se arma los insert
            $camposInsertSql = implode(',', $campos);
            $sqlInsert = "INSERT INTO $tablaDestino ($camposInsertSql) values ";
            $arrayValues = [];
            $acumValues = 0;

            foreach ($datos as $key => $value) {
                $arrayValuesRow = [];
                foreach ($value as $keyb => $valores) {
                    if ($keyb == 'password') {
                        $arrayValuesRow[$keyb] = "'" . password_hash($valores, 1) . "'";
                    } elseif ($keyb == 'secure_key') {
                        $arrayValuesRow[$keyb] = "'" . md5(uniqid(mt_rand(0, mt_getrandmax()), true)) . "'";
                    } else {
                        $arrayValuesRow[$keyb] = "'" . $this->eliminarNumeroCadena(trim($valores)) . "'";
                    }
                }
                $valueInsert = implode(',', $arrayValuesRow);
                $arrayValues[$acumValues] = "($valueInsert)";
                $acumValues++;
            }

            $valuesInsert = implode(',', $arrayValues);

            $resp = [
                'sqlDropTable' => "DROP TABLE IF EXISTS $tablaDestino; ",
                'sqlCreateTable' => $sqlTabla,
                'sqlInsert' => $sqlInsert .= " $valuesInsert;",
            ];
            return $resp;
        } else {
            Log::error("$this->cliente : error en la funcion " . __FUNCTION__ . " parametro datos vacío.");
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

        //--------Valido que exista detalle pedido
        $erroresTotal = [];
        $erroresEncabezado = [];
        $contEE = 0;
        $erroresDetalleNoDefinido = [];
        $contED = 0;

        foreach ($this->data as $key => $data) {

            $datosEnc = $data;
            unset($datosEnc['detalle_pedido']);
            $datosEnc = $this->depurarCamposAutorizados(self::CAMPOS_AUTORIZADOS_ENCABEZADO, $datosEnc);

            $respValidarEncabezado = $this->validarEncabezadoPedido($datosEnc);
            if ($respValidarEncabezado['valid'] == false) {

                $erroresEncabezado[$contEE] = $respValidarEncabezado['errors'];
                $erroresTotal['registro_' . ($key + 1)]['tipo_documento'] = $data['tipo_documento'];
                $erroresTotal['registro_' . ($key + 1)]['numero_pedido'] = $data['numero_pedido'];
                $erroresTotal['registro_' . ($key + 1)]['error_encabezado_pedido'] = $erroresEncabezado;
                $contEE++;
            }

            $formatoValido = false;
            $formatoValido = $request->input('data.' . $key . '.detalle_pedido') ?? false;

            if (!$formatoValido) {
                $erroresTotal['registro_' . ($key + 1)]['error_detalle_pedido'] = "Formato json no válido, detalle pedido " . $data['numero_pedido'] . " no está definido";
                $contED++;
            } elseif ($formatoValido) {

                $item = 1;
                $erroresDetallePedido = [];
                foreach ($data['detalle_pedido'] as $keyb => $detallePedido) {

                    $detallePedido = $this->depurarCamposAutorizados(self::CAMPOS_AUTORIZADOS_DETALLE, $detallePedido);
                    $respValidacion = $this->validarDetallePedido($detallePedido);
                    if ($respValidacion['valid'] == false) {
                        $erroresDetallePedido['item_' . $item] = $respValidacion['errors'];
                    }

                    $item++;
                }
                if (count($erroresDetallePedido) > 0) {
                    $erroresTotal['registro_' . ($key + 1)]['tipo_documento'] = $data['tipo_documento'];
                    $erroresTotal['registro_' . ($key + 1)]['numero_pedido'] = $data['numero_pedido'];
                    $erroresTotal['registro_' . ($key + 1)]['error_detalle_pedido'] = $erroresDetallePedido;
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

    public function validarEncabezadoPedido($datosEncPedido)
    {
        //------Elimino detalle pedido el cual no esta dentro de esta validación
        $datosEncPedido = $this->decodificarArray($datosEncPedido);

        $rules = [
            'tipo_documento' => 'required|max:5',
            'bodega' => 'required|max:10',
            'numero_pedido' => 'required|max:8',
            'tipo_cliente' => 'required|digits:4',
            'fecha_pedido' => 'required|date_format:"Ymd"',
            'nit_cliente' => 'required|digits_between:1,15',
            'sucursal_cliente' => 'required|digits_between:1,15',
            'centro_operacion' => 'required|digits_between:1,15',
            'cedula_vendedor' => 'required|min:4|max:100|digits_between:1,15',
            'vendedor' => 'required|max:255',
            'observaciones_pedido' => 'max:2000',
        ];

        $validator = Validator::make($datosEncPedido, $rules);

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

    public function validarDetallePedido($datosDetallePedido)
    {
        $rules = [
            'codigo_producto' => 'required',
            'lista_precio' => 'required|size:3',
            'cantidad' => 'required|digits_between:1,15',
            'precio_unitario' => 'required|regex:/^[0-9]+(\.[0-9]{1,4})?$/',
        ];
        $validator = Validator::make($datosDetallePedido, $rules);

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
}
