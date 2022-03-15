<?php

namespace App\Http\Controllers\integracionjuandhoyosweb;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EncabezadoPedidoModel;
use App\Models\EncabezadoFacturaModel;
use App\Models\LogErrorImportacionModel;
use Log;

class ReenvioPedidoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // dump($request->all());
        $buscar =$request->input('buscar');
        $registrosXpagina=100;
        if(!empty($buscar)){
            $pedidosError = EncabezadoPedidoModel::where('estadoenviows', '=', "3")->where('numero_pedido','=',$buscar)->paginate($registrosXpagina);
        }else{
            $pedidosError = EncabezadoPedidoModel::where('estadoenviows', '=', "3")->paginate($registrosXpagina);
        }
        
        return view('reenviopedido.index',compact(['pedidosError','buscar']));
    }

    public function indexfac(Request $request)
    {
        $buscar =$request->input('buscar');
        $registrosXpagina=100;
        if(!empty($buscar)){
            $facturasError = EncabezadoFacturaModel::where('estadoenviows', '=', "3")->where('numero_factura','=',$buscar)->paginate($registrosXpagina);
        }else{
            $facturasError = EncabezadoFacturaModel::where('estadoenviows', '=', "3")->paginate($registrosXpagina);
        }
        
        return view('reenviofacturas.index',compact(['facturasError','buscar']));
    }

    public function reenviarPedido(Request $request)
    {        
        try {
            $pedido= $request->input('pedido');
            $arrayLlave= explode("|",$pedido);
    
            // Log::info($arrayLlave);
            $centroOperacion= $arrayLlave[2];
            $numeroPedido   = $arrayLlave[0];
            $bodega= $arrayLlave[3];
            $tipoDocumento= $arrayLlave[1];
            $estado="0";
    
            $updatePedido= new LogErrorImportacionModel();
            $updatePedido->actualizarEstadoDocumento("",$estado,$centroOperacion,$bodega,$tipoDocumento,$numeroPedido);
    
            return response()->json([
                'mensaje'=>'Pedido actualizado',
                'renviado' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje'=>'Error al actualizar pedido',
                'renviado' => false
            ], 200);
        }
    }

    public function reenviarFactura(Request $request)
    {
        try {
            $factura= $request->input('factura');
            $arrayLlave= explode("|",$factura);
    
            // Log::info($arrayLlave);
            $centroOperacion= $arrayLlave[2];
            $numeroFactura   = $arrayLlave[0];
            $bodega= $arrayLlave[3];
            $tipoDocumento= $arrayLlave[1];
            $estado="0";
    
            $updateFactura= new LogErrorImportacionModel();
            $updateFactura->actualizarEstadoDocumentoFac("",$estado,$centroOperacion,$bodega,$tipoDocumento,$numeroFactura);
    
            return response()->json([
                'mensaje'=>'Factura actualizada',
                'renviado' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje'=>'Error al actualizar factura',
                'renviado' => false
            ], 200);
        }  
    }

    public function getEncabezadoPedidoModel(){
        return new EncabezadoPedidoModel();
    }
}
