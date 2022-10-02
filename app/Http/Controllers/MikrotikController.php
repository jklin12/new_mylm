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
}
