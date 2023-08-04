<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'Auth\AuthController@login');
    Route::post('signup', 'Auth\AuthController@signUp');

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'Auth\AuthController@logout');
        Route::get('integracionjuandhoyos/v1/pedidos', 'integracionjuandhoyos\v1\PedidoController@getPedidoSiesa');
        //Route::post('integracionjuandhoyos/v1/pedidos', 'integracionjuandhoyos\v1\PedidoController@subirPedidoSiesa'); 
        Route::post('integracionjuandhoyos/v1/pedidos', 'integracionjuandhoyos\v1\IngresoPedidoController@recibirPedidoJson');
        
        Route::get('integracionjuandhoyos/v1/log-pedidos', 'integracionjuandhoyos\v1\LogPedidoController@getLogPedido');
        Route::get('integracionjuandhoyos/v1/compras-devolucion-compras', 'integracionjuandhoyos\v1\CompraDevolucionCompraController@getComprasDevolucionesCompra');
        Route::get('integracionjuandhoyos/v1/inventarios', 'integracionjuandhoyos\v1\InventarioController@getInventario');
        Route::post('integracionjuandhoyos/v1/clientes', 'integracionjuandhoyos\v1\ClienteController@saveCliente');
        // Route::post('integracionjuandhoyos/v1/facturas', 'integracionjuandhoyos\v1\FacturaController@subirFacturaSiesa');
        Route::post('integracionjuandhoyos/v1/facturas', 'integracionjuandhoyos\v1\IngresoFacturaController@recibirFacturaJson');
        

        Route::post('bexintegracion/v1/pedidos', 'bexintegracion\v1\IngresoPedidoController@recibirPedidoJson');
        Route::get('bexintegracion/v1/pedidos', 'bexintegracion\v1\PedidoController@getPedidoWms');

        Route::post('bexintegracion/v1/clientes', 'bexintegracion\v1\ClienteController@saveCliente');

        Route::get('bexintegracion/v1/compras-devolucion-compras', 'bexintegracion\v1\ComprasDevolucionComprasController@getComprasDevolucionCompras');
        Route::get('bexintegracion/v1/inventarios', 'bexintegracion\v1\InventarioController@getInventario');

    });
});
