<?php

namespace App\Custom;
use App\Models\ConexionesModel;
use App\Traits\TraitApiWms;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClienteCore
{
    use TraitApiWms;
    public function crearCliente($data)
    {
        // Log::info($data);
        $conexion = ConexionesModel::where('id_conexion','3')->get()[0];

        $arrayCliente = [
            'url_rpc' => $conexion->siesa_id_consulta,
            'db_rpc' => $conexion->siesa_conexion,
            'email_rpc' => $conexion->siesa_usuario,
            'token_rpc' => $conexion->siesa_clave,
        ];
        foreach ($data as $key => $value) {
            $arrayCliente['clientes'] = array([
                'vat' => $data['nit'],
                'tipo_identificacion' => $data['tipo_identificacion'] == 'N' ? 'NIT' : 'Cédula de ciudadanía',
                'nombre_completo' => $data['tipo_identificacion'] == 'N' ? $data['razon_social'] : $data['nombres']." ".$data['apellido_1']." ".$data['apellido_2'],
                'correo' => $data['correo'],
                'telefono' => $data['telefono'],
                'celular' => '',
                'direccion' => $data['direccion'],
                'zona' => $data['barrio'],
                'codigo_postal' => $data['codigo_ciudad'],
                'ciudad' => $data['ciudad'],
                'departamento' => $data['departamento'],
                'pais' => 'Colombia',
            ]);
        }
        $collectionClient = collect(['cliente' => $data]);
        Storage::disk('local')->put('/public/clientes/' . $data['nit'].'.json', $collectionClient);
        
        $crearCliente = $this->postCrearClientes($conexion,$arrayCliente);
        Log::info($crearCliente);
        return $crearCliente;
    }

    public function postCrearClientes($conexion,$arrayCliente)
    {
        $conexionToken = ConexionesModel::where('id_conexion','2')->get()[0];
        $access_token = $this->getWmsAccesTokenWms($conexionToken);
        $response = $this->postWmsCrearClientes($access_token,$conexion->siesa_url,$arrayCliente); 
        return $response;
    }
}
