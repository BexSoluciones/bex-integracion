<?php

namespace App\Http\Controllers\bexintegracion\v1;

use App\Custom\ClienteCore;
use App\Http\Controllers\Controller;
use App\Traits\TraitApiWms;
use App\Traits\TraitHerramientas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    use TraitApiWms;
    use TraitHerramientas;
    public function saveCliente(Request $request){

        $respValidacion = $this->validarEstructuraJson($request);

        if ($respValidacion['valid'] == false) {

            return response()->json([
                'created' => false,
                'code' => 412,
                'errors' => $respValidacion['errors'],
            ], 412);

        }

        $datosCliente=$this->convertirArrayMayuscula($this->decodificarArray($request->input('data.0')));

        $crearCliente = new ClienteCore;
        $respCrearCliente = $crearCliente->crearCliente($datosCliente);

        return $respCrearCliente;

        // if($respCrearCliente['created']===false){
        //     return response()->json([
        //         'created' => false,
        //         'code' => 412,
        //         'errors' =>$respCrearCliente['errors'] ,
        //     ], 412);
        // }

        // return response()->json([
        //     'created' => true,
        //     'code' => 201,
        //     'errors' =>0,
        // ], 201);

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

        $datos=$this->decodificarArray($request->input('data.0'));
          
        //--------Validando tipo identificacion
        $rules = [
            'tipo_identificacion' => [
                'required',
                Rule::in(['C', 'N']),
            ]
        ];
        
        $validator = Validator::make($datos, $rules);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors(),
            ];
        }

        $rules = [
            'tipo_identificacion' => [
                'required',
                Rule::in(['C', 'N']),
            ],
            'nit' => 'required|max:15',
            'sucursal' => 'required|digits_between:1,3',
            'nombre_establecimiento' => 'required|max:50',
            'direccion' => 'required|max:50',
            'nombre_contacto' => 'required|max:50',
            'codigo_ciudad' => 'required|size:5',
            'ciudad' => 'required',
            'departamento' => 'required',
            'barrio' => 'required|max:40',
            'telefono' => 'required|max:20',
            'correo' => 'required'
        ];

        if($datos['tipo_identificacion'] =='C'){
            $rules['nombres']='required|max:20';
            $rules['apellido_1']='required|max:15';
            $rules['apellido_2']='max:15';
        }elseif($datos['tipo_identificacion'] =='N'){
            $rules['razon_social']='required|max:50';
        }       
        
        //--------Validacion final
        
        $validator = Validator::make($datos, $rules);

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
}
