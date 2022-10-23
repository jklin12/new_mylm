<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use \RouterOS\Client;
use \RouterOS\Query;



class MikrotikController extends Controller
{

    public function cekOlt(Request $request)
    {
        $cust_number = $request->input('cust_number');

        if ($cust_number) {
            $response = Http::get('http://202.169.224.46:8080/index.php/onu/detailCust/' . $cust_number);

            $response = $response->object();
            if ($response->status) {
                return response()
                    ->json($response->data);
            }
        }
    }

    public function cekStatus(Request $request)
    {
        // Initiate client with config object
        $client = new Client([
            'host' => '202.169.224.19',
            'user' => 'faris123',
            'pass' => 'faris123',
            'port' => 9778,
        ]);

        $cust_number = $request->input('cust_number');
        $status = false;
        $data = [];
        if ($cust_number) {
            $query =
                (new Query('/ppp/secret/print'))
                ->where('name', $cust_number);

            // Send query and read response from RouterOS
            $response = $client->query($query)->read();

            //print_r($response);
            $status = true;
            $data = [];
            foreach ($response as $key => $value) {
                $data['name'] = $value['name'];
                $data['password'] = $value['password'];
                $data['profile'] = $value['profile'];
                $data['remote-address'] = $value['remote-address'];
                $data['last-logged-out'] = $value['last-logged-out'];
                $data['disabled'] = $value['disabled'];
                $data['comment'] = isset($value['comment']) ? $value['comment'] : '';
            }
        }


        return response()
            ->json($data);
    }

    public function test()
    {
        $client = new Client([
            'host' => '202.169.224.19',
            'user' => 'faris123',
            'pass' => 'faris123',
            'port' => 9778,
        ]);


        $status = false;
        $data = [];
        $query =
            (new Query('/ppp/secret/print'))
            ->where('profile', 'new-pppoe-ngp');

        $response = $client->query($query)->read();
        $ipList = [];
        foreach ($response as $key => $value) {
            $ipList[] = $value['remote-address'];
        }
        //print_r($ipList);
        foreach ($ipList as $key => $value) $susunIp[$key] = ip2long($value);
        sort($susunIp);
        foreach ($susunIp as $key => $value) $susunIp[$key] = long2ip($value);

        $lastip = "";
        $lastipIndex = 0;

        foreach ($susunIp as $key => $value) {
            if (!array_key_last($susunIp)) {
                $nowIp = explode('.', $value);
                $nexIp = explode('.', $susunIp[$key + 1]);

                if ($nowIp[2] != $nexIp[2]) {

                    if ($nowIp[3] < 255) {
                        $lastipIndex = $key;
                        $lastip = $value;
                        break;
                    }
                }
            }else{
                $lastipIndex = $key;
                $lastip = $value;
            }
        }

        $ip5after = [];
        for ($i = $lastipIndex - 5; $i < $lastipIndex; $i++) {
            if (isset($susunIp[$i])) {
                $ip5after[] =  $susunIp[$i];
            }
        }
        for ($i = $lastipIndex; $i < $lastipIndex + 5; $i++) {
            if (isset($susunIp[$i])) {
                $ip5after[] =  $susunIp[$i];
            }
        }



        $lastip = explode('.', $lastip);
        $indexIp3 = 1;
        $indexIp2 = $lastip[2];
        if ($lastip[3] < 255) {
            $indexIp3 = $lastip[3] + 1;
        } else {
            $indexIp2 = $lastip[2] + 1;
        }

        $newIp = $lastip[0] . '.' . $lastip[1] . '.' . $indexIp2 . '.' . $indexIp3;

        //echo $newIp;
        //print_r($ip5after);
        $load['new_ip'] = $newIp;
        $load['ip_terdekat'] = $ip5after;

        return $load;
    }
}
