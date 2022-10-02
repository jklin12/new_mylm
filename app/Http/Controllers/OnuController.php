<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FreeDSx\Snmp\SnmpClient;

class OnuController extends Controller
{
    public function index()
    {
        $snmp = new SnmpClient([
            'host' => '192.168.99.77',
            'version' => 2,
            'community' => 'bravojmn',
        ]);

        # Get a specific OID value as a string...
        echo $snmp->getValue('.1.3.6.1.4.1.3902.1012.3.28.2.1.4') . PHP_EOL;

        $session = new SNMP(SNMP::VERSION_2c, '192.168.99.77', 'bravojmn');
        var_dump($session->walk(".1.3.6.1.4.1.3902.1012.3.28.1.1.3"));
        var_dump($session->getError());
    }
}
