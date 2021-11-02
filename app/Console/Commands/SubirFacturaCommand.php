<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EncabezadoFacturaModel;
use App\Models\DetalleFacturaModel;
use App\Jobs\ProcessSubirFacturaSiesa;


class SubirFacturaCommand extends Command
{
    protected $signature = 'ecom:subir-factura';

    protected $description = 'sube facturas';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $facturas=$this->obtenerFacturas();
        if(!empty($facturas)){
            foreach ($facturas as $key => $factura) {
                $objFacturaDetalle = new DetalleFacturaModel();
                $detalleFactura = $objFacturaDetalle->obtenerDetalleFactura($factura['numero_factura'],$factura['centro_operacion'],$factura['tipo_documento'],$factura['bodega']);
                ProcessSubirFacturaSiesa::dispatch($factura,$detalleFactura);
            }
        }

        $facturasSinConexion=$this->obtenerFacturasErrorWs();
        if(!empty($facturasSinConexion)){
            foreach ($facturasSinConexion as $key => $factura) {
                $objFacturaDetalle = new DetalleFacturaModel();
                $detalleFactura = $objFacturaDetalle->obtenerDetalleFactura($factura['numero_factura'],$factura['centro_operacion'],$factura['tipo_documento'],$factura['bodega']);
                ProcessSubirFacturaSiesa::dispatch($factura,$detalleFactura);
            }
        }
    }

    public function obtenerFacturas()
    {
        $estado="0";
        $objFacturaEncabezado = new EncabezadoFacturaModel();
        return $objFacturaEncabezado->obtenerFacturaEncabezado($estado);
    }

    public function obtenerFacturasErrorWs()
    {
        $estado="4";
        $objFacturaEncabezado = new EncabezadoFacturaModel();
        return $objFacturaEncabezado->obtenerFacturaEncabezado($estado);
    }

}
