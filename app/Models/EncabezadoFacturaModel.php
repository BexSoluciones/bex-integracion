<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;

class EncabezadoFacturaModel extends Model
{
    protected $connection = 'mysql';

    protected $table="tbldmovencfac";

    protected $fillable = [
        'CODMOVENC',
        'centro_operacion',
        'CODTIPODOC',
        'tipo_documento',
        'numero_factura',
        'cedula_vendedor',
        'NUMVISITA' ,
        'nit',
        'CODPRECIO',
        'CODDESCUENTO',
        'CODMOTVIS',
        'FECHORINIVISITA',
        'FECHORFINVISITA',
        'EXTRARUTAVISITA',
        'fecha_factura',
        'CODVEHICULO',
        'MOTENTREGA',
        'FECHORENTREGAMOV',
        'CODFPAGOVTA',
        'NUMCIERRE',
        'FECHORCIERRE',
        'CODGRACIERRE',
        'NUMCARGUE',
        'FECHORCARGUE',
        'DIARUTERO',
        'NUMLIQUIDACION',
        'FECHORLIQUIDACION',
        'ORDENCARGUEMOV',
        'observaciones_factura',
        'JAVAID',
        'FECCAP',
        'NUMMOVALT',
        'FECHORENTREGACLI',
        'FECNOVEDAD',
        'AUTORIZACION',
        'CODGRAAUTORIZACION',
        'DCTOGLOBAL',
        'NUMCIERREREC',
        'FECHORCIERREREC',
        'CODGRACIERREREC',
        'PROYECTO',
        'EXPORTADO',
        'MENSAJEADIC',
        'CONSCAMPANAOK',
        'CODVENDEDORTRANS',
        'EMAILB2B',
        'ORIGEN',
        'ORDENDECOMPRA',
        'direntrega',
        'tipoentrega',
        'nummovtr',
        'prefmovtr',
        'backorder',
        'prospecto',
        'puntosenvio',
        'estadoenviows',
        'udid',
        'os',
        'ip',
        'tipo_cliente',
        'bodega',
        'sucursal_cliente',
        'nombre_vendedor',
        'medio_pago',
        'valor_medio_pago',
        'referencia_medio_pago',
        'fecha_consignacion_cheque',
        'tipo_documento_remision',
        'numero_documento_remision',
        'fecha_venc',
    ];
    public $timestamps=false;
    //eloquent
    // public function obtenerPedidoEncabezado($estadoPedido){
    //     $datos=$this->where('estadoenviows','=',$estadoPedido)
    //                 ->get();
    //     // dump($datos);
    //     if(count($datos)>0){
    //         return $datos;
    //     }else{
    //         return null;
    //     }
    // }

    //DB
    public function obtenerFacturaEncabezado($estado){
        $sql="select *, DATE_ADD(fecha_factura, INTERVAL 3 DAY) AS fecha_venc from ".$this->table." where estadoenviows='".$estado."'";          
        $resultadoSql = DB::select($sql);
        // log::info($resultadoSql);     
        return json_decode(json_encode($resultadoSql),true);
    }
}
