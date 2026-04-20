<?php
namespace App\Traits;

use Exception;
use App\Models\Connection;

use Illuminate\Support\Facades\DB;

trait ConnectionDBTrait {

    public function connectionDB($database, $cbd){

        if($database == "globoland"){
            $host = 'server05.bexsoluciones.com';
            $username = 'platafor_sys';
            $password = 'lnRRfaen';
        }

        if($database == "verdeazul"){
            $host = 'server07.bexsoluciones.com';
            $username = 'platafor_sys';
            $password = 'lnRRfaen';
        }

        if($database == "bycsasap"){
            $host = '34.45.220.88';
            $username = 'app_integraciones';
            $password = 'zuFG4X,GShG]Mkz';
        }

        try {
            // Database configuration
            config([
                'database.connections.dynamic_connection' => [
                    'driver'    => 'mysql',
                    'host'      => $host,
                    'database'  => 'platafor_pi'.$cbd,
                    'username'  => $username,
                    'password'  => $password,
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix'    => '',
                    'strict' => true,
                ],
            ]);
        } catch (\Exception $e) {
            $this->error('Error al configurar la conexión: ' . $e->getMessage());
        }
    }
}
