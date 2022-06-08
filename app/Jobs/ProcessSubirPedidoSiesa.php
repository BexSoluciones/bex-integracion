<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Custom\PedidoCore;
use App\Custom\PedidoCoreWms;
use Log;

class ProcessSubirPedidoSiesa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pedido;
    
    protected $detallePedido;

    protected $installer;
    
    public function __construct($pedido,$detallePedido,$installer)
    {
        $this->pedido= $pedido;
        $this->detallePedido= $detallePedido;
        $this->installer = $installer;
    }

    
    public function handle()
    {
        // Log::info('=========imprimiendo datos recibidos al job=====');
        // Log::info($this->pedido);
        // Log::info($this->detallePedido);
        if ($this->installer == 'prooriente') {
            $objPedidoCore=new PedidoCoreWms();
            $objPedidoCore->crearPedidoWms($this->pedido,$this->detallePedido);
        }else{
            $objPedidoCore=new PedidoCore();
            $objPedidoCore->subirPedidoSiesa($this->pedido,$this->detallePedido);
        }
        

    }
}
