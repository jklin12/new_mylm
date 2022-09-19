<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \RouterOS\Client;
use \RouterOS\Query;



class MikrotikController extends Controller
{

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
                $data['comment'] = $value['comment'];
            }
        }
        
        
        return response()
            ->json($data);
    }
}
