<?php

namespace App\Console\Commands;

use Carbon\Carbon;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class Globoland extends Command {
    
    protected $signature   = 'command:globoland {cbd?} {tipodocori?} {rango?}';
    protected $description = 'Ejecucion Goboland';
    
    public function handle(){
        $cbd        = $this->argument('cbd')        ?? "275";
        $tipodocori = $this->argument('tipodocori') ?? "4";
        $rango      = $this->argument('rango')      ?? "";

        if ($tipodocori == '4') {
            $tipodoc = '4';
        }
        
        try {

            //Obtener token consumo de API
            $apiUrl = 'https://sl1cp.boyala.com:50000/b1s/v1/Login';
    
            $requestData = [
                "CompanyDB" => "REPRESENTACIONES_GLOBOLAND",
                "Password" => "Panama123",
                "UserName" => "BOYALA\\globoind"
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->post($apiUrl, $requestData);

            $data = json_decode($response->getBody());
            $token = $data->SessionId;
            
            //$this->info('Token: ' . $token);

            //Conexion server 5
            $dbConfig = config('database.connections.globoland');
            $dbConfig['database'] = 'platafor_pi' . $cbd;

            // Establecer la nueva configuración en la conexión de la base de datos
            config(['database.connections.globoland' => $dbConfig]);
            $customConnection = DB::connection('globoland');

            //Pedidos en estado '1' para pasarlos a estado '0'
            $pedidos = $customConnection->table('tbldmovenc')
                ->where('CODTIPODOC', $tipodoc)
                ->whereNotNull('NUMCIERRE')
                ->whereNotNull('FECHORCIERRE')
                ->where('estadoenviows', '1')
                ->where('fechamovws', '<', Carbon::now()->subHour(1)) 
                ->update([
                    'estadoenviows' => '0',
                    'fechamovws' => Carbon::now(),
                ]);
            $formattedResults = json_encode($pedidos, JSON_PRETTY_PRINT);
            $this->info('total pedidos convertidos a estado cero: '.$formattedResults);

            
            //Pedidos estado '0'
            $pedidosEstadoCero = $customConnection->table('tbldmovenc')
                ->select('tbldmovenc.CODMOVENC', 'tbldmovenc.CODEMPRESA', 'tbldmovenc.CODTIPODOC', 'tbldmovenc.PREFMOV', 
                    'tbldmovenc.NUMMOV', 'tbldmovenc.CODVENDEDOR', 'tbldmovenc.NUMVISITA', 'tbldmovenc.CODCLIENTE', 
                    'tbldmovenc.CODPRECIO', 'tbldmovenc.CODDESCUENTO', 'tbldmovenc.CODMOTVIS', 'tbldmovenc.FECHORINIVISITA', 
                    'tbldmovenc.FECHORFINVISITA', 'tbldmovenc.EXTRARUTAVISITA', 'tbldmovenc.FECMOV', 'tbldmovenc.CODVEHICULO',
                    'tbldmovenc.MOTENTREGA', 'tbldmovenc.FECHORENTREGAMOV', 'tbldmovenc.CODFPAGOVTA', 'tbldmovenc.NUMCIERRE',
                    'tbldmovenc.FECHORCIERRE', 'tbldmovenc.CODGRACIERRE', 'tbldmovenc.NUMCARGUE', 'tbldmovenc.FECHORCARGUE', 
                    'tbldmovenc.DIARUTERO', 'tbldmovenc.NUMLIQUIDACION', 'tbldmovenc.FECHORLIQUIDACION', 
                    'tbldmovenc.ORDENCARGUEMOV', 'tbldmovenc.MENSAJEMOV', 'tbldmovenc.JAVAID', 'tbldmovenc.FECCAP', 
                    'tbldmovenc.NUMMOVALT', 'tbldmovenc.FECNOVEDAD', 'tbldmovenc.autorizacion', 'tbldmovenc.fechorentregacli', 
                    'tbldmovenc.CODGRAAUTORIZACION', 'tbldmovenc.DCTOGLOBAL', 'tbldmovenc.NUMCIERREREC',
                    'tbldmovenc.FECHORCIERREREC', 'tbldmovenc.CODGRACIERREREC', 'tbldmovenc.PROYECTO', 'tbldmovenc.EXPORTADO', 
                    'tbldmovenc.MENSAJEADIC', 'tbldmovenc.CONSCAMPANAOK', 'tbldmovenc.CODVENDEDORTRANS', 'tbldmovenc.EMAILB2B', 
                    'tbldmovenc.ORIGEN', 'tbldmovenc.ORDENDECOMPRA', 'tbldmovenc.direntrega', 'tbldmovenc.tipoentrega', 
                    'tbldmovenc.nummovtr', 'tbldmovenc.prefmovtr', 'tbldmovenc.backorder', 'tbldmovenc.prospecto', 
                    'tbldmovenc.puntosenvio', 'tbldmovenc.estadoenviows', 'tbldmovenc.fechamovws', 'tbldmovenc.msmovws',
                    'tbldmovenc.udid', 'tbldmovenc.os', 'tbldmovenc.ip', 'tbldmovenc.tipofactura', 'tbldmovenc.adjunto1', 
                    'tbldmovenc.adjunto2', 'tbldmovenc.adjunto3', 'tblmvendedor.tercvendedor', 'tblmcliente.nitcliente')
                ->join('tblmvendedor', 'tbldmovenc.CODVENDEDOR', '=', 'tblmvendedor.CODVENDEDOR')
                ->join('tblmcliente', 'tbldmovenc.CODCLIENTE', '=', 'tblmcliente.codcliente')
                ->whereNotNull('NUMCIERRE')
                ->whereNotNull('FECHORCIERRE')
                ->where('estadoenviows', '0')
                ->limit(20)
                ->get();

            $this->info('total pedidos en estado cero: ' . $pedidosEstadoCero->count());
            
                
            //Recorre los pedidos para enviar por api
            foreach ($pedidosEstadoCero as $pedido) {
                $updatePedido = $customConnection->table('tbldmovenc')
                    ->where('CODMOVENC', $pedido->CODMOVENC)
                    ->update([
                        'estadoenviows' => '1'
                    ]);

                $formattedResults = json_encode($updatePedido, JSON_PRETTY_PRINT);
                $this->info('convirtiendo pedido '.$pedido->CODMOVENC.' a estado 1: ' . (($formattedResults == 1) ? 'actualizado' : 'error'));
                
                // Consulta para obtener los detalles del pedido
                $pedidosdets = $customConnection->table('tbldmovdet')
                    ->select('tbldmovdet.CODMOVDET', 'tbldmovdet.CODMOVENC', 'tbldmovdet.CODEMPRESA', 'tbldmovdet.CODTIPODOC', 
                        'tbldmovdet.PREFMOV', 'tbldmovdet.NUMMOV', 'tbldmovdet.CODBODEGA', 'tbldmovdet.CODPRODUCTO', 'tbldmovdet.CANTIDADMOV',
                        'tbldmovdet.PRECIOMOV', 'tbldmovdet.IVAMOV', 'tblmproducto.CCOSTOS', 'tbldmovdet.CODMOTDEV', 'tbldmovdet.BONENTREGAPRODUCTO',
                        'tbldmovdet.DCTO1MOV', 'tbldmovdet.DCTO2MOV', 'tbldmovdet.DCTO3MOV', 'tbldmovdet.DCTO4MOV', 'tbldmovdet.motentrega',
                        'tbldmovdet.javaid', 'tbldmovdet.DCTONC', 'tbldmovdet.DCTOPIEFACAUT', 'tbldmovdet.FACTOR', 'tbldmovdet.BONIFICADO',
                        'tbldmovdet.PREPACK', 'tbldmovdet.AUTORIZACION','tbldmovdet.CANTID1', 'tbldmovdet.UNIDAD01','tbldmovdet.UNIDAD02',
                        'tbldmovdet.OBSEQUIO1', 'tbldmovdet.OBSEQUIO2', 'tbldmovdet.CANTID2', 'tbldmovdet.IDLISPRE', 'tbldmovdet.PRODUCTOPADRE',
                        'tbldmovdet.dctovalor', 'tbldmovdet.autovalor', 'tbldmovdet.ocultorowid', 'tbldmovdet.ocultoporcval', 'tbldmovdet.id_ofertasenc',
                        'tbldmovdet.tipo_oferta', 'tbldmovdet.grupo_oferta', 'tbldmovdet.rowid', 'tbldmovdet.cantidadpines', 'tbldmovdet.codmotpines', 
                        'tbldmovdet.impconsumo', 'tbldmovdet.codaprobpines', 'tbldmovdet.lote', 'tbldmovdet.peso', 'tbldmovdet.fletesimple')
                    ->join('tblmproducto', 'tblmproducto.CODPRODUCTO', '=', 'tbldmovdet.CODPRODUCTO')
                    ->where('NUMMOV', $pedido->NUMMOV)
                    ->where('CODTIPODOC', $pedido->CODTIPODOC)
                    ->get(); 
                    
                $this->info('Detalles de los pedidos: ' . $pedidosdets->count());
                 
                $DATADET = [];
                foreach ($pedidosdets as $pedidosdet) {
                    $DATADET[] = [
                        'ItemCode' => $pedidosdet->CODPRODUCTO,
                        'Quantity' => floatval($pedidosdet->CANTIDADMOV),
                        'TaxCode' => $pedidosdet->CCOSTOS,
                        'UnitPrice' => floatval($pedidosdet->PRECIOMOV)
                    ];
                }
                
                $DATA = [
                    'CardCode' => $pedido->nitcliente,
                    'DocDate' => $pedido->FECNOVEDAD,
                    'DocDueDate' => $pedido->fechorentregacli,
                    'DocumentLines' => $DATADET,
                ];
                
                $DATAjson = json_encode($DATA);
                
                $this->info("Ejecutando Movimiento: {$pedido->NUMMOV} - {$pedido->CODTIPODOC}");
                $this->info($DATAjson);
                
                $url = 'https://sl1cp.boyala.com:50000/b1s/v1/Orders';
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Cookie' => "B1SESSION={$token}; ROUTEID=.node2",
                ])->withoutVerifying()->post($url, $DATA);
                
                if ($response['Confirmed'] == "tYES") {
                    $erroresms = "<strong> Pedido enviado #{$pedido->NUMMOV} exitosamente!, Con la referencia {$response['Reference1']} </strong>";
                    $this->info($erroresms);

                    $customConnection->table('tbldmovenc')
                        ->where('CODMOVENC', $pedido->CODMOVENC)
                        ->update([
                            'estadoenviows' => '2',
                            'fechamovws' => Carbon::now(),
                            'msmovws' => $erroresms,
                        ]);

                    $error = false;
                } else {
                    $errorMessage = $response['Message'] ?? '';
                    if (isset($errorMessage['Message']) && strpos($errorMessage['Message'], "Error El Pedido: {$pedido->NUMMOV} Ya Existe") !== false) {
                        $erroresms = "El pedido ya existe. Se marca como enviado.";
                        $this->info($erroresms);

                        $customConnection->table('tbldmovenc')
                            ->where('CODMOVENC', $pedido->CODMOVENC)
                            ->update([
                                'estadoenviows' => '2',
                                'fechamovws' => Carbon::now(),
                                'msmovws' => $erroresms,
                            ]);
                    } else {
                        $erroresms = "Pedido con errores. Error: {$response->status()} - Mensaje: {$response->body()}";
                        $this->info($erroresms);

                        $customConnection->table('tbldmovenc')
                            ->where('CODMOVENC', $pedido->CODMOVENC)
                            ->update([
                                'estadoenviows' => '3',
                                'fechamovws' => Carbon::now(),
                                'msmovws' => $erroresms,
                            ]);
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Ocurrió un error durante la conexión: " . $e->getMessage());
        }
        
    }
}

