<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Custom\FacturaCore;

class ProcessSubirFacturaSiesa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $factura;
    protected $detallefactura;

    public function __construct($factura, $detallefactura)
    {
        $this->factura = $factura;
        $this->detallefactura = $detallefactura;
    }

    public function handle()
    {
        $objFacturaCore=new FacturaCore();
        $objFacturaCore->subirFacturaSiesa($this->factura,$this->detallefactura);
    }
}
