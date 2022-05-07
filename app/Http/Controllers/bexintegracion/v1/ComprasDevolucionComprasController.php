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
        $dataCompraDevCompra = $this->getWmsComprasDevolucioCompras($conexion);

        return $dataCompraDevCompra;
    }
    public function getAccesTokenWms()
    {
        $conexion = ConexionesModel::where('id_conexion','2')->get()[0];
        $access_token = $this->getWmsAccesTokenWms($conexion);

        return $access_token;
    }
}